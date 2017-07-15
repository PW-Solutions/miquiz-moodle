<?php

require_once('../../config.php');
require_once("lib.php");

$id = required_param('id', PARAM_INT);    // Course Module ID

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

/*
* show all students
* show questions and kategories
* übersicht lehrende
*/
$url = get_string('modulebaseurl', 'miquiz');
/*TODO
wie lange offen
*/
echo $OUTPUT->header();

echo '<b>'.$miquiz->intro.'</b>';
echo 'Produktiv nach: '.gmdate("m.d.Y H:i", $miquiz->timeuntilproductive).'<br/>';
echo 'Bewertungsmodus: '.get_string('miquiz_create_scoremode_'.$miquiz->scoremode, 'miquiz').'<br/>';

echo '<br/><b>Questions</b><br/>';
$quiz_questions = $DB->get_records('miquiz_questions', array('quizid' => $miquiz->id));
if(count($quiz_questions)>0){
    $question_ids = "";
    foreach($quiz_questions as $quiz_question){
        if($question_ids != ""){
            $question_ids .= " OR ";
        }
        $question_ids .="id=".$quiz_question->id;
    }
    $questions = $DB->get_records('question', array(), $question_ids);
    foreach($questions as $question){
        $category = $DB->get_record('question_categories', array('id' => $question->category));
        echo $question->name.' ('.$category->name.')<br/>';
    }
} else{
    echo 'This quiz has no associated questions<br/>';
}

echo '<br/><b>Users</b><br/>';
$context = context_course::instance($miquiz->course);
$enrolled = get_enrolled_users($context);
foreach($enrolled as $user){
    echo $user->username.'<br/>';
}
echo '<br/><h3><a href=\''.$url.'\' >Jump to MI-Quiz</a></h3>';

print_r($miquiz);

echo $OUTPUT->footer();
