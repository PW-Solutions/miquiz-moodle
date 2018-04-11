<?php

defined('MOODLE_INTERNAL') || die();

require_once("miquiz_api.php");

function miquiz_add_instance($miquiz) {
    global $DB, $CFG;

    $miquiz->timemodified = time();
    try {
        $miquiz_ids = miquiz::create($miquiz);
    } catch (Exception $e) {
        echo $e->getMessage();
        return;
    }
    $miquiz->miquizcategoryid = $miquiz_ids["catid"];
    $miquiz->id = $DB->insert_record("miquiz", $miquiz);

    foreach($miquiz->questions as $question){
        $added_question = array(
            'quizid' => $miquiz->id,
            'questionid' => $question,
            'miquizquestionid' => $miquiz_ids["qids"][$question],
            'timecreated' => time()
        );
        $DB->insert_record("miquiz_questions", $added_question);
    }
    return $miquiz->id;
}


function miquiz_update_instance($miquiz) {
    global $DB, $CFG;

    $miquiz->id = $miquiz->instance;
    $miquiz->timemodified = time();

    // get a fresh copy of the miquiz object, since the received obj just contains the changes
    $miquiz_fresh = $DB->get_record("miquiz", array("id"=>$miquiz->id));
    $miquiz_fresh->assesstimestart = $miquiz->assesstimestart;
    $miquiz_fresh->assesstimefinish = $miquiz->assesstimefinish;
    $miquiz_fresh->timeuntilproductive = $miquiz->timeuntilproductive;
    $miquiz_fresh->name = $miquiz->name;
    try {
        miquiz::update($miquiz_fresh);
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    return $DB->update_record('miquiz', $miquiz);
}


function miquiz_delete_instance($id) {
    global $DB;

    $miquiz = $DB->get_record("miquiz", array("id"=>"$id"));
    try {
        miquiz::delete($miquiz);
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    $DB->delete_records('miquiz_questions', array("quizid" => $id));
    $DB->delete_records('miquiz_users', array("quizid" => $id));
    $DB->delete_records('miquiz', array('id' => $id));
    return True;
}


function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}


function get_question_answeringtime($question_id){
    global $DB;

    $tags = $DB->get_records_sql('SELECT t.rawname FROM {tag} t JOIN {tag_instance} ti on t.id=ti.tagid JOIN {question} q on q.id=ti.itemid WHERE ti.itemtype=\'question\' AND q.id=?', array($question_id));
    foreach ($tags as $tag) {
        if (startsWith($tag->rawname, get_config('mod_miquiz', 'questiontimetag'))) {
            $timeToAnswer = (int)(trim(str_replace(get_config('mod_miquiz', 'questiontimetag') . ':', "", $tag->rawname)));
            break;
        }
    }
    if (empty($timeToAnswer)) {
        $timeToAnswer = (int) get_config('mod_miquiz', 'questiondefaulttime');
    }
    if ($timeToAnswer < 15) {
        $timeToAnswer = 15;
    }
    return $timeToAnswer;
}
