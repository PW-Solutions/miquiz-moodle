<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/question/editlib.php');
require_once($CFG->dirroot.'/question/category_class.php');
require_once("lib.php");

class mod_miquiz_mod_form extends moodleform_mod
{
    protected $course = null;

    public function __construct($current, $section, $cm, $course)
    {
        $this->course = $course;
        parent::__construct($current, $section, $cm, $course);
    }

    public function definition()
    {
        global $CFG, $COURSE, $DB, $OUTPUT, $PAGE;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('miquiz_create_name', 'miquiz'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 50), 'maxlength', 50, 'client');

        $mform->addElement('text', 'short_name', get_string('miquiz_create_short_name', 'miquiz'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('short_name', PARAM_TEXT);
        } else {
            $mform->setType('short_name', PARAM_CLEANHTML);
        }
        $mform->addRule('short_name', null, 'required', null, 'client');
        $mform->addRule('short_name', get_string('maximumchars', '', 10), 'maxlength', 10, 'client');

        if ($this->_instance == '') {
            $options=array(); //use string keys as keys since conversion to numbers more complicated
            $options[0]  = get_string('miquiz_create_scoremode_0', 'miquiz');
            $options[1]  = get_string('miquiz_create_scoremode_1', 'miquiz');
            $options[2]  = get_string('miquiz_create_scoremode_2', 'miquiz');
            $options[3]  = get_string('miquiz_create_scoremode_3', 'miquiz');
            $options[4]  = get_string('miquiz_create_scoremode_4', 'miquiz');
            $mform->addElement('select', 'scoremode', get_string('miquiz_create_scoremode', 'miquiz'), $options);
            $mform->addElement('advcheckbox', 'statsonlyforfinishedgames', get_string('miquiz_create_statsonlyforfinishedgames', 'miquiz'));
            $mform->addHelpButton('statsonlyforfinishedgames', 'miquiz_create_statsonlyforfinishedgames', 'miquiz');
        }

        $mform->addElement('date_time_selector', 'assesstimestart', get_string('miquiz_create_assesstimestart', 'miquiz'));
        $mform->addElement('date_time_selector', 'timeuntilproductive', get_string('miquiz_create_timeuntilproductive', 'miquiz'));
        $mform->addElement('date_time_selector', 'assesstimefinish', get_string('miquiz_create_assesstimefinish', 'miquiz'));

        $this->standard_intro_elements(get_string('description', 'miquiz'));

        if ($this->_instance == '') {
            $mform->addElement('header', 'modstandardelshdr', get_string("miquiz_create_questions", "miquiz").'<i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i>');
            $mform->setExpanded('modstandardelshdr');

            // https://docs.moodle.org/dev/Question_database_structure
            $context = context_course::instance($COURSE->id);
            $categories = $DB->get_records('question_categories', array('contextid' => $context->id));
            $questionchooser_categories = array();
            foreach ($categories as $category) {
                $questions = $DB->get_records('question', array('category' => $category->id));
                $question_dtos = array();
                foreach ($questions as $question) {
                    if ($question->qtype =='multichoice') {
                        array_push($question_dtos, array(
                            "question_id" => $question->id,
                            "question_name" => $question->name
                        ));
                    }
                }
                $cat_dto = array(
                    "category_id" => $category->id,
                    "category_name" => $category->name,
                    "questions" => $question_dtos
                );
            }

            $customel_rendered = $PAGE->get_renderer('mod_miquiz')->render_from_template('miquiz/questionchooser', array(
                $questionchooser_categories,
                "i18n_miquiz_create_questions_search" => get_string("miquiz_create_questions_search", "miquiz"),
                "i18n_miquiz_create_questions_selected" => get_string("miquiz_create_questions_selected", "miquiz"),
                "categories" => $cat_dto
            ));
            $fields = array(
                $mform->createElement('hidden', 'questions', ''),            
                $mform->createElement('html', $customel_rendered));
            $mform->addGroup($fields, 'questiong', '', '', false);
            $mform->setType('questions', PARAM_NOTAGS);
        }

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }


    /**
     * Enforce defaults here
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues)
    {
        if (isset($defaultvalues['conditions'])) {
            $conditions = unserialize($defaultvalues['conditions']);
            $defaultvalues['timespent'] = $conditions->timespent;
            $defaultvalues['completed'] = $conditions->completed;
            $defaultvalues['gradebetterthan'] = $conditions->gradebetterthan;
        }

        // Set up the completion checkbox which is not part of standard data.
        $defaultvalues['completiontimespentenabled'] =
            !empty($defaultvalues['completiontimespent']) ? 1 : 0;

        if ($this->current->instance) {
            // Editing existing instance - copy existing files into draft area.
            $draftitemid = file_get_submitted_draft_itemid('mediafile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_lesson', 'mediafile', 0, array('subdirs'=>0, 'maxbytes' => $this->course->maxbytes, 'maxfiles' => 1));
            $defaultvalues['mediafile'] = $draftitemid;
        }
    }

    /**
     * Enforce validation rules here
     *
     * @param object $data Post data to validate
     * @return array
     **/
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        $this->local_validation($errors, $data, $files);
        $this->external_validation($errors, $data, $files);

        if ($data['questions'] == "") {
            $errors['questiong'] = get_string('miquiz_create_questions_error', 'miquiz');
        }

        return $errors;
    }

    private function local_validation(&$errors, $data, $files)
    {
        if ($data['assesstimestart'] >= $data['assesstimefinish']) {
            $errors['assesstimefinish'] = get_string('miquiz_create_error_endbeforestart', 'miquiz');
        }

        if ($data['timeuntilproductive'] >= $data['assesstimefinish'] ||
           $data['timeuntilproductive'] < $data['assesstimestart']) {
            $errors['timeuntilproductive'] = get_string('miquiz_create_error_betweenendstart', 'miquiz');
        }

        // Check open and close times are consistent.
        if (isset($data['available'])) {
            if ($data['available'] != 0 && $data['deadline'] != 0 &&
                    $data['deadline'] < $data['available']) {
                $errors['deadline'] = get_string('closebeforeopen', 'lesson');
            }
        }

        if (!empty($data['usepassword']) && empty($data['password'])) {
            $errors['password'] = get_string('emptypassword', 'lesson');
        }
    }

    private function external_validation(&$errors, $data, $files)
    {
        global $DB;
        if ($this->current->instance) {
            $activities = $DB->get_records("miquiz", array("short_name" => $data["short_name"]));
            if (!empty($activities)) {
                $activity = array_pop($activities);
            }
        }

        # check categories in miquiz
        $categories = miquiz::api_get("api/categories");
        $exists_in_miquiz = false;
        foreach ($categories as $category) {
            if ($category["name"] === $data["short_name"]) {
                $exists_in_miquiz = !isset($activity) || strval($category['id']) !== $activity->miquizcategoryid;
                break;
            }
        }

        if ($exists_in_miquiz) {
            $errors['short_name'] = get_string('miquiz_create_error_unique', 'miquiz');
        }
    }

    /**
     * Display module-specific activity completion rules.
     * Part of the API defined by moodleform_mod
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules()
    {
        $mform = $this->_form;

        $mform->addElement(
            'checkbox',
            'completionendreached',
            get_string('completionendreached', 'lesson'),
                get_string('completionendreached_desc', 'lesson')
        );
        // Enable this completion rule by default.
        $mform->setDefault('completionendreached', 1);

        $group = array();
        $group[] =& $mform->createElement(
            'checkbox',
            'completiontimespentenabled',
            '',
                get_string('completiontimespent', 'lesson')
        );
        $group[] =& $mform->createElement('duration', 'completiontimespent', '', array('optional' => false));
        $mform->addGroup($group, 'completiontimespentgroup', get_string('completiontimespentgroup', 'lesson'), array(' '), false);
        $mform->disabledIf('completiontimespent[number]', 'completiontimespentenabled', 'notchecked');
        $mform->disabledIf('completiontimespent[timeunit]', 'completiontimespentenabled', 'notchecked');

        return array('completionendreached', 'completiontimespentgroup');
    }

    /**
     * Called during validation. Indicates whether a module-specific completion rule is selected.
     *
     * @param array $data Input data (not yet validated)
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data)
    {
        return !empty($data['completionendreached']) || $data['completiontimespent'] > 0;
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data)
    {
        parent::data_postprocessing($data);
        // Turn off completion setting if the checkbox is not ticked.
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiontimespentenabled) || !$autocompletion) {
                $data->completiontimespent = 0;
            }
            if (empty($data->completionendreached) || !$autocompletion) {
                $data->completionendreached = 0;
            }
        }
    }
}
