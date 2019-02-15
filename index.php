<?php

require('../../config.php');
require_once("lib.php");
 

$id = required_param('id', PARAM_INT);           // Course Module ID
 
// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

// retrieve all quiz cm's from db
$sql = "select cm.id, m.name, cm.instance from {modules} m, {course_modules} cm where name='miquiz' and cm.module=m.id and cm.course = '".$id."' and cm.id in (SELECT cs.sequence FROM {course_sections} cs where cs.course = '".$id."')";
$res = $DB->get_records_sql($sql);

require_course_login($course, true, get_coursemodule_from_id('miquiz',$res[array_keys($res)[0]]->id, 0, false, MUST_EXIST));

$miquizzes = [];
foreach($res as $a_cm_entry){
    $a_cm = get_coursemodule_from_id('miquiz', $a_cm_entry->id, 0, false, MUST_EXIST);
    $miquiz = $DB->get_record('miquiz', array('id'=> $a_cm->instance));

    $reports = [];
    $resp = miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/reports");

    $now = new DateTime("now");
    if($miquiz->assesstimestart < $now){           
        $status = get_string('miquiz_status_inactive', 'miquiz');
    } elseif($miquiz->timeuntilproductive < $now){
        $status = get_string('miquiz_status_training', 'miquiz');
    } elseif($miquiz->assesstimefinish < $now){
        $status = get_string('miquiz_status_productive', 'miquiz');
    } else{
        $status = get_string('miquiz_status_finished', 'miquiz');
    }

    $resp = miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/stats");
    $answeredQuestions_training_total = $resp["answeredQuestions"]["training"]["total"];
    $answeredQuestions_training_correct = $resp["answeredQuestions"]["training"]["correct"];
    $answeredQuestions_duel_total = $resp["answeredQuestions"]["duel"]["total"];
    $answeredQuestions_duel_correct = $resp["answeredQuestions"]["duel"]["correct"];
    
    $answeredQuestions_total = number_format($answeredQuestions_training_total+$answeredQuestions_duel_total, 0);
    $answeredQuestions_correct = number_format($answeredQuestions_training_correct+$answeredQuestions_duel_correct, 0);
    $answeredQuestions_wrong = number_format($answeredQuestions_total-$answeredQuestions_correct, 0);

    $miquizzes[] = [
        'id' => $a_cm->id,
        'name' => $miquiz->name,
        'assesstimestart' => $miquiz->assesstimestart,
        'assesstimefinish' => $miquiz->assesstimefinish,
        'num_questions' => count($DB->get_records('miquiz_questions', array('quizid' => $miquiz->id))),
        'num_questions_with_reports' => count(miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/reports")),
        'status' => $status,
        'answeredQuestions_total' => $answeredQuestions_total,
        'answeredQuestions_correct' => $answeredQuestions_correct,
        'answeredQuestions_wrong' => $answeredQuestions_wrong,

    ];
}

$url = new moodle_url('/mod/miquiz/index.php', array('id'=>$id));
$PAGE->set_url($url);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('miquiz_index_title', 'miquiz'));

echo '<table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">';
echo '<thead><tr>';
$rows = [get_string('miquiz_view_name', 'miquiz'), get_string('miquiz_create_assesstimestart', 'miquiz'), get_string('miquiz_create_assesstimefinish', 'miquiz'), 
        get_string('miquiz_view_numquestions', 'miquiz'), get_string('miquiz_index_reports', 'miquiz'), get_string('miquiz_index_table_status', 'miquiz'), 
        get_string('miquiz_view_numquestions', 'miquiz'), get_string('miquiz_cockpit_correct', 'miquiz'), get_string('miquiz_cockpit_incorrect', 'miquiz')];
foreach($rows as $row)
    echo '<th class="th-sm">'.$row.'</th>';
echo '</thead><tbody>';

foreach($miquizzes as $row) {
    echo '<tr>';
    echo '<td>'.$row['name'].'</td>';
    echo '<td>'.gmdate("Y.m.d H:i:s", $row['assesstimestart']).'</td>';
    echo '<td>'.gmdate("Y.m.d H:i:s", $row['assesstimefinish']).'</td>';
    echo '<td>'.$row['num_questions'].'</td>';
    echo '<td>'.$row['num_questions_with_reports'].'</td>';
    echo '<td>'.$row['status'].'</td>';
    echo '<td>'.$row['answeredQuestions_total'].'</td>';
    echo '<td>'.$row['answeredQuestions_correct'].'</td>';
    echo '<td>'.$row['answeredQuestions_wrong'].'</td>';
    echo '</tr>';
}
echo '</tbody></table>';
echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
echo '<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>';
$PAGE->requires->js_amd_inline('$("#datatable").DataTable();');
echo $OUTPUT->footer();