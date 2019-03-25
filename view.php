<?php

require_once('../../config.php');
require_once("lib.php");

$id = required_param('id', PARAM_INT);  // Course Module ID

// check permissions and retrieve context
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

$miquizurl = get_config('mod_miquiz', 'instanceurl');
$context = context_module::instance($cm->id);
$is_manager =  has_capability('moodle/course:manageactivities', $context);  //https://docs.moodle.org/dev/Roles

if ($is_manager && isset($_GET['download'])) {
    // perform export
    header('Content-Type: text/csv');
    header('Content-disposition: filename="export_'.time() .'.csv"');
    echo miquiz::api_get("api/categories/download?categories=".$miquiz->miquizcategoryid, ['return_raw' => true]);
    die();
}

if ($is_manager && isset($_GET['queue_adhoc_task'])) {
    header('Content-Type: application/json');
    $taskToQueue = $_GET['queue_adhoc_task'];
    if ($taskToQueue === 'sync_questions') {
        $task = new \mod_miquiz\task\sync_questions();
        $task->set_custom_data(['activities' => [$miquiz->id]]);
    }

    if (!is_null($task)) {
        // \core\task\manager::queue_adhoc_task($task);
        try {
            $task->execute();
            $result = ['success' => true, 'queued_task' => $taskToQueue];
        } catch (\Exception $e) {
            $result = ['success' => false, 'queued_task' => $taskToQueue, 'error' => $e->getMessage()];
        }
    } else {
        $result = ['success' => false, 'queued_task' => $taskToQueue, 'error' => 'No valid task provided'];
    }

    echo json_encode($result);
    die();
}

echo $OUTPUT->header();

$categories_dto = array();
$reports = [];
$resp = miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/reports");
foreach ($resp as $report) {
    if (!isset($reports[$report["questionId"]])) {
        $reports[$report["questionId"]] = [];
    }
    $reports[$report["questionId"]][] = $report;
}

$quiz_questions = $DB->get_records('miquiz_questions', array('quizid' => $miquiz->id));
if (count($quiz_questions) > 0) {
    $question_ids = "";
    foreach ($quiz_questions as $quiz_question) {
        if ($question_ids != "") {
            $question_ids .= " OR ";
        }
        $question_ids .="id=".$quiz_question->questionid;
    }
    $questions = $DB->get_records_sql('SELECT * FROM {question} q WHERE '. $question_ids);

    $questionsbycategory = array();
    foreach ($questions as $question) {
        $category = $DB->get_record('question_categories', array('id' => $question->category));
        if(!array_key_exists($category->name, $questionsbycategory))
            $questionsbycategory[$category->name] = [$question];
        else
            array_push($questionsbycategory[$category->name], $question);
    }
    asort($questionsbycategory);
    foreach ($questionsbycategory as $catname => $questions) {
        if (count($questions) == 0)
            continue;

        $category = $DB->get_record('question_categories', array('id' => $questions[0]->category));
        $questions_dto = array();
        foreach ($questions as $question) {
            $miquiz_question = $DB->get_record_sql('SELECT miquizquestionid FROM {miquiz_questions} WHERE questionid='. $question->id.' AND quizid='.$miquiz->id);
            $reports_dto = array();
            if (isset($reports[$miquiz_question->miquizquestionid])) {
                foreach ($reports[$miquiz_question->miquizquestionid] as $report) {
                    array_push($reports_dto, array(
                        'report_category' =>$report['category'],
                        'report_message' =>$report['message'],
                        'report_author' => $report['author']
                    ));
                }
            }
            array_push($questions_dto, array(
                'question_id' =>$question->id,
                'question_name' =>$question->name,
                'reports' => $reports_dto
            ));
        }
        array_push($categories_dto, array(
            'category_name' => $category->name,
            'questions' => $questions_dto
        ));
    }
}

$score_training = 0;
$score_duel = 0;
$score_training_correct = 0;
$score_duel_correct = 0;
$user_stats = miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/user-stats");
$resp = miquiz::api_get("api/categories/" . $miquiz->miquizcategoryid . "/stats");
$answeredQuestions_training_total = $resp["answeredQuestions"]["training"]["total"];
$answeredQuestions_training_correct = $resp["answeredQuestions"]["training"]["correct"];
$answeredQuestions_duel_total = $resp["answeredQuestions"]["duel"]["total"];
$answeredQuestions_duel_correct = $resp["answeredQuestions"]["duel"]["correct"];
$answeredQuestions_total = number_format($answeredQuestions_training_total+$answeredQuestions_duel_total, 0);
$answeredQuestions_correct = number_format($answeredQuestions_training_correct+$answeredQuestions_duel_correct, 0);
$answeredQuestions_wrong = number_format($answeredQuestions_total-$answeredQuestions_correct, 0);
$eps = pow(10000000, -1);
$rel_answeredQuestions_total = number_format($answeredQuestions_total/($answeredQuestions_total+$eps), 2);
$rel_answeredQuestions_correct = number_format($answeredQuestions_correct/($answeredQuestions_total+$eps), 2);
$rel_answeredQuestions_wrong = number_format($answeredQuestions_wrong/($answeredQuestions_total+$eps), 2);
$answered_abs = "(".$answeredQuestions_total."/".$answeredQuestions_correct."/".$answeredQuestions_wrong.")";
$answered_rel = "(".$rel_answeredQuestions_total."/".$rel_answeredQuestions_correct."/".$rel_answeredQuestions_wrong.")";

$userdata = [];
$miquiz_users = miquiz::api_get("api/users?fields[users]=id,externalLogin");
foreach ($user_stats as $user_score) {
    $a_data = [
        "score_training" => $user_score["score"]["training"]["total"],
        "score_duel" => $user_score["score"]["duel"]["total"],
        "score_training_possible" => $user_score["score"]["training"]["possible"],
        "score_duel_possible" => $user_score["score"]["duel"]["possible"],
        "answeredQuestions_training_total" => $user_score["answeredQuestions"]["training"]["total"],
        "answeredQuestions_training_correct" => $user_score["answeredQuestions"]["training"]["correct"],
        "answeredQuestions_duel_total" => $user_score["answeredQuestions"]["duel"]["total"],
        "answeredQuestions_duel_correct" => $user_score["answeredQuestions"]["duel"]["correct"],
        "score"=> $score_training + $score_duel,
        "score_possible" => $user_score["score"]["training"]["possible"] + $user_score["score"]["duel"]["possible"],
        "answeredQuestions_total" => number_format($answeredQuestions_training_total+$answeredQuestions_duel_total, 0),
        "answeredQuestions_correct" => number_format($answeredQuestions_training_correct+$answeredQuestions_duel_correct, 0),
        "answeredQuestions_wrong" => number_format($answeredQuestions_total-$answeredQuestions_correct, 0),
        "rel_answeredQuestions_total" => number_format($answeredQuestions_total/($answeredQuestions_total+$eps), 2),
        "rel_answeredQuestions_correct" => number_format($answeredQuestions_correct/($answeredQuestions_total+$eps), 2),
        "rel_answeredQuestions_wrong" => number_format($answeredQuestions_wrong/($answeredQuestions_total+$eps), 2),
    ];
    $username = miquiz::get_username($user_score["userId"], $miquiz_users);
    $userdata[$username] = $a_data;
}
asort($userdata);
$user_stats_dto = array();
foreach ($userdata as $username => $a_data) {
    $answered_abs = "(".$a_data['answeredQuestions_total']."/".$a_data['answeredQuestions_correct']."/".$a_data['answeredQuestions_wrong'].")";
    $answered_rel = "(".$a_data['rel_answeredQuestions_total']."/".$a_data['rel_answeredQuestions_correct']."/".$a_data['rel_answeredQuestions_wrong'].")";
    array_push($user_stats_dto, array(
        "username" => $username,
        "answered_abs" => $answered_abs,
        "answered_rel" => $answered_rel,
        "score" => $a_data['score'],
        "score_possible" => $a_data['score_possible'],

    ));
}
$now = time();

$currentState = miquiz::getCurrentStateForQuiz($miquiz);
$is_notyetstarted = $currentState === 'not_started';
$is_training = $currentState === 'training';
$is_productive = $currentState === 'productive';
$is_finished = $currentState === 'finished';


echo $PAGE->get_renderer('mod_miquiz')->render_from_template('miquiz/view', array(
    'is_manager' => $is_manager,
    'name' => $miquiz->name,
    'short_name' => $miquiz->short_name,
    'description' => $miquiz->intro,
    'miquizurl' => $miquizurl,
    'instance_name' => get_config('mod_miquiz', 'instancename'),
    'i18n_miquiz_view_openlink' => get_string('miquiz_view_openlink', 'miquiz'),
    'i18n_miquiz_sync_questions' => get_string('miquiz_view_sync_questions', 'miquiz'),
    'i18n_miquiz_view_shortname' => get_string('miquiz_view_shortname', 'miquiz'),
    'i18n_miquiz_status_training' => get_string('miquiz_status_training', 'miquiz'),
    'i18n_miquiz_status_productive' => get_string('miquiz_status_productive', 'miquiz'),
    'assesstimestart' => $miquiz->assesstimestart,
    'timeuntilproductive' => $miquiz->timeuntilproductive,
    'assesstimefinish' => $miquiz->assesstimefinish,
    'is_notyetstarted' => $is_notyetstarted,
    'is_training' => $is_training,
    'is_productive' =>  $is_productive,
    'is_finished' => $is_finished,
    'i18n_miquiz_view_scoremode' => get_string('miquiz_view_scoremode', 'miquiz'),
    'i18n_miquiz_create_scoremode' => get_string('miquiz_create_scoremode_'.$miquiz->scoremode, 'miquiz'),
    'statsonlyforfinishedgames' => $miquiz->statsonlyforfinishedgames,
    'i18n_miquiz_view_statsonlyforfinishedgames' => get_string('miquiz_view_statsonlyforfinishedgames', 'miquiz'),
    'i18n_miquiz_view_answeredquestions' => get_string('miquiz_view_answeredquestions', 'miquiz'),
    'i18n_miquiz_view_nodata' => get_string('miquiz_view_nodata', 'miquiz'),
    'i18n_miquiz_view_numquestions' => get_string('miquiz_view_numquestions', 'miquiz'),
    'i18n_miquiz_view_numquestions' => get_string('miquiz_view_numquestions', 'miquiz'),
    'numquestions' => count($DB->get_records('miquiz_questions', array('quizid' => $miquiz->id))),
    'numquestions_with_reports' => count($reports),
    'url' => $url,
    'i18n_miquiz_index_download' => get_string('miquiz_index_download', 'miquiz'),
    'i18n_miquiz_cockpit_with_reports' => get_string('miquiz_cockpit_with_reports', 'miquiz'),
    'i18n_miquiz_cockpit_total' => get_string('miquiz_cockpit_total', 'miquiz'),
    'i18n_miquiz_cockpit_correct' => get_string('miquiz_cockpit_correct', 'miquiz'),
    'i18n_miquiz_cockpit_incorrect' => get_string('miquiz_cockpit_incorrect', 'miquiz'),
    'answeredQuestions_total' => $answeredQuestions_total,
    'answeredQuestions_correct' => $answeredQuestions_correct,
    'answeredQuestions_wrong' => $answeredQuestions_wrong,
    'i18n_miquiz_view_questions' => get_string('miquiz_view_questions', 'miquiz'),
    'categories' => $categories_dto,
    'i18n_miquiz_view_statistics_user' => get_string('miquiz_view_statistics_user', 'miquiz'),
    'i18n_miquiz_view_statistics_username' => get_string('miquiz_view_statistics_username', 'miquiz'),
    'i18n_miquiz_view_statistics_answeredquestionsabs' => get_string('miquiz_view_statistics_answeredquestionsabs', 'miquiz'),
    'i18n_miquiz_view_statistics_answeredquestionsrel' => get_string('miquiz_view_statistics_answeredquestionsrel', 'miquiz'),
    'i18n_miquiz_view_statistics_totalscore' => get_string('miquiz_view_statistics_totalscore', 'miquiz'),
    'user_stats' => $user_stats_dto
));

if ($is_manager) {
    echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
    echo '<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>';
    $PAGE->requires->js_amd_inline('$("#userdatatable").DataTable();');
}

echo $OUTPUT->footer();