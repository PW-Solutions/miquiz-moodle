<?php

require_once('../../config.php');
require_once("lib.php");

$id = required_param('id', PARAM_INT);  // Course Module ID

$url = new moodle_url('/mod/miquiz/view.php', array('id'=>$id));
$PAGE->set_url($url);

if (!$cm = get_coursemodule_from_id('miquiz', $id)) {
    print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}
if (!$miquiz = $DB->get_record('miquiz', array('id'=> $cm->instance))) {
    print_error('course module is incorrect'); // NOTE As above
}
require_login($course, false, $cm);

$url = get_string('modulebaseurl', 'miquiz');
echo $OUTPUT->header();

echo '<br/><b>'.get_string('miquiz_view_overview', 'miquiz').'</b><br/>';
echo '<b>'.$miquiz->intro.'</b>';
echo get_string('miquiz_view_shortname', 'miquiz').': '.$miquiz->short_name.'<br/>';
echo get_string('miquiz_view_assesstimestart', 'miquiz').': '.gmdate("m.d.Y H:i", $miquiz->assesstimestart).'<br/>';
echo get_string('miquiz_view_timeuntilproductive', 'miquiz').': '.gmdate("m.d.Y H:i", $miquiz->timeuntilproductive).'<br/>';
echo get_string('miquiz_view_assesstimefinish', 'miquiz').': '.gmdate("m.d.Y H:i", $miquiz->assesstimefinish).'<br/>';
echo get_string('miquiz_view_scoremode', 'miquiz').': '.get_string('miquiz_create_scoremode_'.$miquiz->scoremode, 'miquiz').'<br/>';

echo '<br/><b>'.get_string('miquiz_view_questions', 'miquiz').'</b><br/>';
$quiz_questions = $DB->get_records('miquiz_questions', array('quizid' => $miquiz->id));
if(count($quiz_questions) > 0){
    $question_ids = "";
    foreach($quiz_questions as $quiz_question){
        if($question_ids != ""){
            $question_ids .= " OR ";
        }
        $question_ids .="id=".$quiz_question->questionid;
    }
    $questions = $DB->get_records_sql('SELECT * FROM {question} q WHERE '. $question_ids);
    foreach($questions as $question){
        $category = $DB->get_record('question_categories', array('id' => $question->category));
        echo $question->name.' ('.$category->name.')<br/>';
    }
}

echo '<br/><b>'.get_string('miquiz_view_user', 'miquiz').'</b><br/>';

$enrolled = miquiz::sync_users($miquiz);
foreach($enrolled as $user){
    echo $user->username.'<br/>';
}

echo '<br/><b>'.get_string('miquiz_view_statistics', 'miquiz').'</b><br/>';
//TODO get statistiken
echo get_string('miquiz_view_score', 'miquiz').': <br/>';

//teacher area
$context = context_course::instance($miquiz->course);
//https://docs.moodle.org/dev/Roles
if (has_capability('moodle/course:manageactivities', $context)) {
    echo get_string('miquiz_view_statisticsans_answeredquestions', 'miquiz').': <br/>';
    echo get_string('miquiz_view_statisticsans_totalscore', 'miquiz').': <br/>';
}

echo '<br/><h3><a href=\''.$url.'\' >'.get_string('miquiz_view_openlink', 'miquiz').'</a></h3>';

miquiz::sync_feedback($miquiz);

//print_r($miquiz);

echo $OUTPUT->footer();
