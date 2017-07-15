<?php

defined('MOODLE_INTERNAL') || die();

function miquiz_add_instance($miquiz) {
    global $DB, $CFG;

    $miquiz->timemodified = time();
    $questions = $miquiz->questions;

    print_r($miquiz);
    die();

    $miquiz->id = $DB->insert_record("miquiz", $miquiz);

    foreach($questions as $question){
        $added_question = array(
            'quizid' => $miquiz->id,
            'questionid' => $question,
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

    return $DB->update_record('miquiz', $miquiz);
}


function miquiz_delete_instance($id) {
    global $DB;

    $DB->delete_records('miquiz_questions', array("quizid" => $id));

    if (! $miquiz = $DB->get_record("miquiz", array("id"=>"$id"))) {
        return false;
    }
    return true;
}
