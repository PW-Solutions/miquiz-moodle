<?php

require('../../config.php');
require_once("lib.php");


$id = required_param('id', PARAM_INT);           // Course ID

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

$url = new moodle_url('/mod/miquiz/index.php', array('id'=>$id));
$PAGE->set_url($url);

require_login($course);

//check if user has permissions to administrate course
$context = context_course::instance($id);
$is_manager =  has_capability('moodle/course:manageactivities', $context);
if(!$is_manager) {
    $red_url = new moodle_url('/course/view.php', array('id'=>$course->id));
    header("Location: ".$red_url);
    die();
}

// retrieve all quiz cm's from db
$sql = "select cm.id, m.name, cm.instance from {modules} m inner join {course_modules} cm on (cm.module=m.id) where name='miquiz' and cm.course = '$id'";
$res = $DB->get_records_sql($sql);

$miquizzes = [];
foreach($res as $a_cm_entry){
    $a_cm = get_coursemodule_from_id('miquiz', $a_cm_entry->id, 0, false, MUST_EXIST);
    $miquiz = $DB->get_record('miquiz', array('id'=> $a_cm->instance));

    $reports = [];
    $resp = miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/reports");

    $now = time();
    $currentState = miquiz::getCurrentStateForQuiz($miquiz);
    $status = get_string('miquiz_status_' . $currentState, 'miquiz');

    $resp = miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/stats");
    $answeredQuestions_training_total = $resp["answeredQuestions"]["training"]["total"];
    $answeredQuestions_training_correct = $resp["answeredQuestions"]["training"]["correct"];
    $answeredQuestions_training_wrong = $answeredQuestions_training_total - $answeredQuestions_training_correct;
    $answeredQuestions_duel_total = $resp["answeredQuestions"]["duel"]["total"];
    $answeredQuestions_duel_correct = $resp["answeredQuestions"]["duel"]["correct"];
    $answeredQuestions_duel_wrong = $answeredQuestions_duel_total - $answeredQuestions_duel_correct;

    $answeredQuestions_total = number_format($answeredQuestions_training_total + $answeredQuestions_duel_total, 0);
    $answeredQuestions_correct = number_format($answeredQuestions_training_correct + $answeredQuestions_duel_correct, 0);
    $answeredQuestions_wrong = number_format($answeredQuestions_training_wrong + $answeredQuestions_duel_wrong, 0);

    $miquizzes[] = [
        'id' => $a_cm->id,
        'name' => $miquiz->name,
        'assesstimestart' => $miquiz->assesstimestart,
        'assesstimefinish' => $miquiz->assesstimefinish,
        'num_questions' => count($DB->get_records('miquiz_questions', array('quizid' => $miquiz->id))),
        'num_questions_with_reports' => count(miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/reports")),
        'status' => $status,
        'answeredQuestions_total' => "$answeredQuestions_total ($answeredQuestions_training_total / $answeredQuestions_duel_total)",
        'answeredQuestions_correct' => "$answeredQuestions_correct ($answeredQuestions_training_correct / $answeredQuestions_duel_correct)",
        'answeredQuestions_wrong' => "$answeredQuestions_wrong ($answeredQuestions_training_wrong / $answeredQuestions_duel_wrong)",
        'miquizcategoryid' => $miquiz->miquizcategoryid,
    ];
}

if (isset($_GET['download_categories'])) {
    // perform export
    $data = $_GET['download_categories'];
    foreach (explode(",", $data) as $miquizcategoryid) {
        $found = False;
        foreach($miquizzes as $row) {
            if($row['miquizcategoryid'] == $miquizcategoryid){
                $found = true;
            }
        }
        if(!$found)
            die();
    }
    header('Content-Type: text/csv');
    header('Content-disposition: filename="export_'.time() .'.csv"');
    echo miquiz::api_get("api/categories/download?categories=".$data, ['return_raw' => true]);
    die();
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('miquiz_index_title', 'miquiz')." (".$course->fullname.")");

$quiz_table_headings = [['name' => '<i class="icon fa fa-download fa-fw " aria-hidden="true" aria-label=""></i>'],
                        ['name' => get_string('miquiz_view_name', 'miquiz')],
                        ['name' => get_string('miquiz_create_assesstimestart', 'miquiz')],
                        ['name' => get_string('miquiz_create_assesstimefinish', 'miquiz')],
                        ['name' => get_string('miquiz_view_numquestions', 'miquiz')],
                        ['name' => get_string('miquiz_index_reports', 'miquiz')],
                        ['name' => get_string('miquiz_index_table_status', 'miquiz')],
                        ['name' => get_string('miquiz_view_numquestions', 'miquiz')],
                        ['name' => get_string('miquiz_cockpit_correct', 'miquiz')],
                        ['name' => get_string('miquiz_cockpit_incorrect', 'miquiz')]];

$quiz_table_body = [];
foreach($miquizzes as $row) {
    array_push($quiz_table_body, array(
        "miquizcategoryid" => $row['miquizcategoryid'],
        "id" => $row['id'],
        "name" => $row['name'],
        "assesstimestart" => date("d.m.Y, H:i", $row['assesstimestart']),
        "assesstimefinish" => date("d.m.Y, H:i", $row['assesstimefinish']),
        "num_questions" => $row['num_questions'],
        "num_questions_with_reports" => $row['num_questions_with_reports'],
        "status" => $row['status'],
        "answeredQuestions_total" => $row['answeredQuestions_total'],
        "answeredQuestions_correct" => $row['answeredQuestions_correct'],
        "answeredQuestions_wrong" => $row['answeredQuestions_wrong']
    ));
}

echo $PAGE->get_renderer('mod_miquiz')->render_from_template('miquiz/index', array(
    'quiz_table_headings' => $quiz_table_headings,
    'quiz_table_body' => $quiz_table_body,
    'i18n_miquiz_index_download' => get_string('miquiz_index_download', 'miquiz')));
$PAGE->requires->js_amd_inline('$y(document).ready(function() {$y("#datatable").DataTable();});');
$downloadjs = 'generateAndFollowDownloadLink = function(){
    var downloadids = Array();
    $("input:checkbox[name=add2download]:checked").each(function(){
        downloadids.push($(this).val());
    });
    if(downloadids.length == 0){
        alert("'.get_string('miquiz_index_noquizselected', 'miquiz').'");
        return;
    }
    window.location = "'.$url.'&download_categories="+downloadids;
};';
$PAGE->requires->js_amd_inline($downloadjs);
echo $OUTPUT->footer();