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
$context = context_module::instance($cm->id);
$enrolled = miquiz::sync_users($miquiz);

echo $OUTPUT->header();

echo '<br/><b>'.get_string('miquiz_view_overview', 'miquiz').'</b><br/>';
echo '<b>'.$miquiz->intro.'</b>';
echo get_string('miquiz_view_shortname', 'miquiz').': '.$miquiz->short_name.'<br/>';
echo get_string('miquiz_view_assesstimestart', 'miquiz').': '.gmdate("m.d.Y H:i", $miquiz->assesstimestart).'<br/>';
echo get_string('miquiz_view_timeuntilproductive', 'miquiz').': '.gmdate("m.d.Y H:i", $miquiz->timeuntilproductive).'<br/>';
echo get_string('miquiz_view_assesstimefinish', 'miquiz').': '.gmdate("m.d.Y H:i", $miquiz->assesstimefinish).'<br/>';
echo get_string('miquiz_view_scoremode', 'miquiz').': '.get_string('miquiz_create_scoremode_'.$miquiz->scoremode, 'miquiz').'<br/>';

if (has_capability('moodle/course:manageactivities', $context)) {
    echo '<br/><b>'.get_string('miquiz_view_questions', 'miquiz').'</b><br/>';

    $reports = [];
    if (has_capability('moodle/course:manageactivities', $context)) {
        $resp = miquiz::api_get("/api/categories/" . $miquiz->miquizcategoryid . "/reports");
        foreach($resp as $report){
            if(!isset($reports[$resp["questionId"]]))
                $reports[$resp["questionId"]] = [];
            $reports[$resp["questionId"]][] = $report;
        }
    }

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
            echo $question->name.' ('.$category->name.')<ul>';
            foreach($reports[$question->miquizquestionid] as $report){
                echo '<li>['.$report['category'].']['.$report['author'].'] '.$report['message'].'</li>';
            }
            echo "</ul>";
        }
    }

    /*
    echo '<br/><b>'.get_string('miquiz_view_user', 'miquiz').'</b><br/>';

    $current_user_is_enrolled = False;
    foreach($enrolled as $user){
        echo $user->username.'<br/>';
        if($user->username == $USER->username)
            $current_user_is_enrolled = True;
    }
    */
}

if (has_capability('moodle/course:manageactivities', $context)) {
    echo '<br/><b>'.get_string('miquiz_view_statistics', 'miquiz').'</b><br/>';
    $score_training = 0;
    $score_duel = 0;
    $score_training_correct = 0;
    $score_duel_correct = 0;
    $user_stats = miquiz::api_get("/api/categories/" . $miquiz->miquizcategoryid . "/user-stats");
    $user_obj = miquiz::api_get("api/users");
    /*
    if($current_user_is_enrolled){
        $current_user_id = miquiz::get_user_id($USER->username, $user_obj);
        foreach($user_stats as $user_score){
            if($user_score["userId"] == $current_user_id) {
                $score_training = $user_score["score"]["training"]["possible"];
                $score_duel = $user_score["score"]["duel"]["possible"];
                $score_training_correct = $user_score["score"]["training"]["total"];
                $score_duel_correct = $user_score["score"]["duel"]["total"];
                break;
            }
        }
        $score = "(".$score_training."/".$score_training_correct.") (".$score_duel."/".$score_duel_correct.")";
        echo get_string('miquiz_view_score', 'miquiz').': '.$score.'<br/>';
    }
    */

//teacher area
//https://docs.moodle.org/dev/Roles
    $resp = miquiz::api_get("/api/categories/" . $miquiz->miquizcategoryid . "/stats");
    $answeredQuestions_training_total = $resp["answeredQuestions"]["training"]["total"];
    $answeredQuestions_training_correct = $resp["answeredQuestions"]["training"]["correct"];
    $answeredQuestions_duel_total = $resp["answeredQuestions"]["duel"]["total"];
    $answeredQuestions_duel_correct = $resp["answeredQuestions"]["duel"]["correct"];

    $answeredQuestions_total = number_format($answeredQuestions_training_total+$answeredQuestions_duel_total,0);
    $answeredQuestions_correct = number_format($answeredQuestions_training_correct+$answeredQuestions_duel_correct,0);
    $answeredQuestions_wrong = number_format($answeredQuestions_total-$answeredQuestions_correct,0);

    $eps = pow(10000000, -1);
    $rel_answeredQuestions_total = number_format($answeredQuestions_total/($answeredQuestions_total+$eps),2);
    $rel_answeredQuestions_correct = number_format($answeredQuestions_correct/($answeredQuestions_total+$eps),2);
    $rel_answeredQuestions_wrong = number_format($answeredQuestions_wrong/($answeredQuestions_total+$eps),2);

    $answered_abs = "(".$answeredQuestions_total."/".$answeredQuestions_correct."/".$answeredQuestions_wrong.")";
    $answered_rel = "(".$rel_answeredQuestions_total."/".$rel_answeredQuestions_correct."/".$rel_answeredQuestions_wrong.")";

    echo get_string('miquiz_view_statistics_answeredquestions', 'miquiz').': '.$answered_abs.' '.$answered_rel.'<br/>';

    echo '<br/>';
    foreach($user_stats as $user_score){
        $score_training = $user_score["score"]["training"]["total"];
        $score_duel = $user_score["score"]["duel"]["total"];
        $score_training_possible = $user_score["score"]["training"]["possible"];
        $score_duel_possible = $user_score["score"]["duel"]["possible"];
        $answeredQuestions_training_total = $user_score["answeredQuestions"]["training"]["total"];
        $answeredQuestions_training_correct = $user_score["answeredQuestions"]["training"]["correct"];
        $answeredQuestions_duel_total = $user_score["answeredQuestions"]["duel"]["total"];
        $answeredQuestions_duel_correct = $user_score["answeredQuestions"]["duel"]["correct"];

        $score = $score_training+$score_duel;
        $score_possible = $score_training_possible+$score_duel_possible;

        $answeredQuestions_total = number_format($answeredQuestions_training_total+$answeredQuestions_duel_total,0);
        $answeredQuestions_correct = number_format($answeredQuestions_training_correct+$answeredQuestions_duel_correct,0);
        $answeredQuestions_wrong = number_format($answeredQuestions_total-$answeredQuestions_correct,0);
        $rel_answeredQuestions_total = number_format($answeredQuestions_total/($answeredQuestions_total+$eps),2);
        $rel_answeredQuestions_correct = number_format($answeredQuestions_correct/($answeredQuestions_total+$eps),2);
        $rel_answeredQuestions_wrong = number_format($answeredQuestions_wrong/($answeredQuestions_total+$eps),2);

        $answered_abs = "(".$answeredQuestions_total."/".$answeredQuestions_correct."/".$answeredQuestions_wrong.")";
        $answered_rel = "(".$rel_answeredQuestions_total."/".$rel_answeredQuestions_correct."/".$rel_answeredQuestions_wrong.")";

        $username = miquiz::get_username($user_score["userId"], $user_obj);
        echo "<p>".$username."<ul>";
        echo '<li>'.get_string('miquiz_view_statistics_answeredquestions', 'miquiz').': '.$answered_abs.' '.$answered_rel.'</li>';
        echo '<li>'.get_string('miquiz_view_statistics_totalscore', 'miquiz').': '.$score.'/'.$score_possible.'</li>';
        echo "</ul></p>";
    }
}

echo '<br/><form action="'.$url.'"><input class="btn btn-primary" id="id_tomiquizbutton" type="submit" value="'.get_string('miquiz_view_openlink', 'miquiz').'"></form>';

//print_r($miquiz);

echo $OUTPUT->footer();
