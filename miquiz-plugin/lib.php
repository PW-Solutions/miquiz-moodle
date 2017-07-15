<?php

defined('MOODLE_INTERNAL') || die();

require_once("miquiz_api.php");

function miquiz_add_instance($miquiz) {
    global $DB, $CFG;

    $miquiz->timemodified = time();
    $questions = $miquiz->questions;

    $miquiz->miquizcategoryid = miquiz::create($miquiz);

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

    miquiz::update($miquiz);

    return $DB->update_record('miquiz', $miquiz);
}


function miquiz_delete_instance($id) {
    global $DB;

    $miquiz = $DB->get_record("miquiz", array("id"=>"$id"));
    miquiz::delete($miquiz);

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
    foreach($tags as $tag){
        if(startsWith($tag->rawname, get_string('miquiz_question_timetag', 'miquiz')))
            return (int)(trim(str_replace(get_string('miquiz_question_timetag', 'miquiz'), "", $tag->rawname)));
    }
    return (int)get_string('miquiz_question_defaulttimetag', 'miquiz');
}
