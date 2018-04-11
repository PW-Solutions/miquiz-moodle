<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/question/editlib.php');
require_once($CFG->dirroot.'/question/category_class.php');
require_once("lib.php");

class mod_miquiz_mod_form extends moodleform_mod {

    protected $course = null;

    public function __construct($current, $section, $cm, $course) {
        $this->course = $course;
        parent::__construct($current, $section, $cm, $course);
    }

    function definition() {
        global $CFG, $COURSE, $DB, $OUTPUT;

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

        if( $this->_instance == ''){
            $options=array(); //use string keys as keys since conversion to numbers more complicated
            $options[0]  = get_string('miquiz_create_scoremode_0', 'miquiz');
            $options[1]  = get_string('miquiz_create_scoremode_1', 'miquiz');
            $options[2]  = get_string('miquiz_create_scoremode_2', 'miquiz');
            $options[3]  = get_string('miquiz_create_scoremode_3', 'miquiz');
            $options[4]  = get_string('miquiz_create_scoremode_4', 'miquiz');
            $mform->addElement('select', 'scoremode', get_string('miquiz_create_scoremode', 'miquiz'), $options);
        }

        $mform->addElement('date_time_selector', 'assesstimestart', get_string('miquiz_create_assesstimestart', 'miquiz'));
        $mform->addElement('date_time_selector', 'timeuntilproductive', get_string('miquiz_create_timeuntilproductive', 'miquiz'));
        $mform->addElement('date_time_selector', 'assesstimefinish', get_string('miquiz_create_assesstimefinish', 'miquiz'));

        $this->standard_intro_elements(get_string('description', 'miquiz'));

        if( $this->_instance == ''){
            // https://docs.moodle.org/dev/Question_database_structure
            $context = context_course::instance($COURSE->id);
            $categories = $DB->get_records('question_categories', array('contextid' => $context->id));
            $question_choice = array();
            foreach ($categories as $category){
                $questions = $DB->get_records('question', array('category' => $category->id));
                foreach ($questions as $question){
                    if($question->qtype =='multichoice')
                        $question_choice[$question->id] = $question->name.' ('.$category->name.')';
                }
            }
            $select = $mform->addElement('select', 'questions', get_string("miquiz_create_questions", "miquiz"), $question_choice);
            $select->setMultiple(true);
            $mform->addRule('questions', null, 'required', null, 'client');
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
    public function data_preprocessing(&$defaultvalues) {
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
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        # check categories in moodle
        $same_names = $DB->get_records("miquiz", array("short_name" => $data["short_name"]));

        # check categories in miquiz
        $moduleid = miquiz::get_module_id();
        $all_categories = miquiz::api_get("api/categories");
        $exists_in_miquiz = False;
        foreach($all_categories as $category){
            if($category["name"] == $data["short_name"]){
                $exists_in_miquiz = True;
                break;
            }
        }

        if($data['assesstimestart'] >= $data['assesstimefinish'])
            $errors['assesstimefinish'] = get_string('miquiz_create_error_endbeforestart', 'miquiz');

        if($data['timeuntilproductive'] >= $data['assesstimefinish'] ||
           $data['timeuntilproductive'] < $data['assesstimestart'])
            $errors['timeuntilproductive'] = get_string('miquiz_create_error_betweenendstart', 'miquiz');

        if(count($same_names) > 0 || $exists_in_miquiz)
            $errors['short_name'] = get_string('miquiz_create_error_unique', 'miquiz');

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

        return $errors;
    }

    /**
     * Display module-specific activity completion rules.
     * Part of the API defined by moodleform_mod
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform = $this->_form;

        $mform->addElement('checkbox', 'completionendreached', get_string('completionendreached', 'lesson'),
                get_string('completionendreached_desc', 'lesson'));
        // Enable this completion rule by default.
        $mform->setDefault('completionendreached', 1);

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completiontimespentenabled', '',
                get_string('completiontimespent', 'lesson'));
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
    public function completion_rule_enabled($data) {
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
    public function data_postprocessing($data) {
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
