<?php

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'MI-Quiz';
$string['modulenameplural'] = 'MI-Quizzes';

$instance_name = get_config('mod_miquiz', 'instancename');
$string['modulename_help'] = 'Challenge your students in a ' . $instance_name . ' duel.';
$additional_info = get_config('mod_miquiz', 'additional_info');
if (!empty($additional_info)) {
    $string['modulename_help'] .= $additional_info;
}

//strings for Administration->Manage roles page
$string['miquiz:overview'] = 'Display ' . $instance_name . ' activity overview';
$string['miquiz:addinstance'] = 'Challenge your students in a ' . $instance_name . ' activity';

$string['miquiz_index_title_overview'] = $instance_name .' Overview';
$string['miquiz_index_title'] = $instance_name .' Course Overview';
$string['miquiz_index_table_status'] = 'Status';
$string['miquiz_index_reports'] = 'Number of reports'; //Anzahl Reports
$string['miquiz_index_download'] = 'Download statistics';
$string['miquiz_index_noquizselected'] = 'Please select at least one quiz!';
$string['miquiz_index_overview'] = 'Display all activities';

$string['miquiz_setting_instanceurl_title'] = 'URL for quiz instance';
$string['miquiz_setting_instanceurl_helper'] = 'Please add the full url to the quiz instance';
$string['miquiz_setting_apikey_title'] = 'API key for quiz instance';
$string['miquiz_setting_apikey_helper'] = 'The API key is necessary to make a connection to the quiz instance.';
$string['miquiz_setting_instancename_title'] = 'Instance name';
$string['miquiz_setting_instancename_helper'] = 'Name of the quiz instance how it is used in Moodle';
$string['miquiz_setting_questiontimetag_title'] = 'Tag name for time to answer';
$string['miquiz_setting_questiontimetag_helper'] = 'First part of the question tag that determines the time to answer. The complete tag is structered like this: [tagname]:[time_to_answer]';
$string['miquiz_setting_questiondefaulttime_title'] = 'Default time to answer (in s)';
$string['miquiz_setting_questiondefaulttime_helper'] = 'In case no specific time to answer is set, this default time (in seconds) will be used.';
$string['miquiz_setting_info_title'] = 'Additional info';
$string['miquiz_setting_info_helper'] = 'Visible when creating a new instance.';

$string['miquiz_create_name'] = 'Quiz Name';
$string['miquiz_create_short_name'] = 'Short Name';
$string['miquiz_create_timeuntilproductive'] = 'Training Phase Ending Time';
$string['miquiz_create_scoremode'] = 'Rating Mode';
$string['miquiz_create_scoremode_0'] = 'no rating';
$string['miquiz_create_scoremode_1'] = 'simple rating without demerit';
$string['miquiz_create_scoremode_2'] = 'simple rating with demerit';
$string['miquiz_create_scoremode_3'] = 'relative rating without demerit';
$string['miquiz_create_scoremode_4'] = 'relative rating with demerit';
$string['miquiz_create_scoremode_help'] = 'Rating during the productive phase.
  Can <strong>not</strong> be changed later!
  <br><br>
  <strong>Simple rating</strong>: 1 point, if all correct possibilities are selected. Otherwise 0 points ("without demerit") or -1 points ("with demerit")
  <br><br>
  <strong>Relative rating</strong>: Calculated with <code>(x / n) - (y / m)</code> with values below 0 only possible "with demerit".
  <br>
  <i>x</i> = selected correct possibilities<br>
  <i>n</i> = number of correct possibilities<br>
  <i>y</i> = selected wrong possibilities<br>
  <i>m</i> = number of wrong possibilities<br>
';
$string['miquiz_create_game_modes'] = 'Game Mode';
$string['miquiz_create_game_modes_help'] = 'Playable game modes during the productive phase.
  Can be changed later.
  <br><br>
  <strong>Solo game</strong>: A game without an opponent.<br>
  <strong>Random duel</strong>: A random opponent is selected.<br>
  <strong>Picked duel</strong>: A specifc opponent can be selected.<br>
  <br>
  Regardless of the game mode, there is only one game at the time possible with the <strong>same</strong> opponent.
';
$string['miquiz_create_game_mode_random_fight'] = 'Random duel';
$string['miquiz_create_game_mode_picked_fight'] = 'Picked duel';
$string['miquiz_create_game_mode_solo_fight'] = 'Solo';
$string['miquiz_create_questions'] = 'Questions';
$string['miquiz_create_questions_error'] = 'A quiz requires at least three questions!';
$string['miquiz_create_questions_selected'] = '${numquestions} questions selected';
$string['miquiz_create_questions_search'] = 'Search';
$string['miquiz_create_questions_no_questions'] = 'No questions in this course available.';
$string['miquiz_create_questions_create_questions'] = 'Please first create at least three questions in this course.';
$string['miquiz_create_assesstimestart'] = 'Quiz Start Date';
$string['miquiz_create_assesstimefinish'] = 'Quiz Stop Date';
$string['miquiz_create_error_endbeforestart'] = 'Quiz end date date has to be later than quiz start date.';
$string['miquiz_create_error_betweenendstart'] = 'Training phase ending time has to be between quiz start and end date.';
$string['miquiz_create_error_game_modes'] = 'Select at least one game mode!';
$string['miquiz_create_statsonlyforfinishedgames'] = 'Consider only finished games in statistics.';
$string['miquiz_create_statsonlyforfinishedgames_help'] = 'If this flag is set, only finished games are considered in the statistics. This includes the rating and calculation of answered questions.';
$string['miquiz_create_activate_training_phase'] = 'Enable training phase';
$string['miquiz_create_activate_training_phase_help'] = 'During the training phase only single player games are available (training) and no points are given.';
$string['miquiz_create_show_always_in_production'] = 'Always include activity during production phase';
$string['miquiz_create_show_always_in_production_help'] = 'If activated, the category will be <b>always</b> included in the category selection during the production phase.';

$string['miquiz_view_overview'] = 'Overview'; //&Uuml;bersicht
$string['miquiz_view_coursename'] = 'Course';
$string['miquiz_view_name'] = 'Name';
$string['miquiz_view_shortname'] = 'Short name'; //Abk&uuml;rzung
$string['miquiz_view_scoremode'] = 'Rating mode (if productive)'; //Bewertungsmodus
$string['miquiz_view_questions'] = 'Questions'; //Fragen
$string['miquiz_view_numquestions'] = 'Number of questions'; //Anzahl Fragen
$string['miquiz_view_user'] = 'Player'; //Mitspieler
$string['miquiz_view_statistics_user'] = 'Statistics (Players)';
$string['miquiz_view_statistics_answeredquestionsabs'] = 'Answered questions absolute (total / correct / wrong)'; //Beantwortete Fragen (gesamt/richtig/falsch, absolut/relativ)
$string['miquiz_view_statistics_answeredquestionsrel'] = 'Answered questions relative (correct / wrong)'; //Beantwortete Fragen (gesamt/richtig/falsch, absolut/relativ)
$string['miquiz_view_statistics_username'] = 'Username'; //Nutzername
$string['miquiz_view_statistics_totalscore'] = 'Score (achieved / maximum)'; //Punkte (erreicht/gesamt erreichbar)
$string['miquiz_view_score'] = 'Your score: training (achieved / maximum), duel (achieved / maximum)'; //Deine Punke
$string['miquiz_view_answeredquestions'] = 'Answered questions'; //Beantwortete Fragen
$string['miquiz_view_statsonlyforfinishedgames'] = 'Finished games only'; //Nur beendete Spiele
$string['miquiz_view_nodata'] =  'No data available'; // Es sind noch keine Daten vorhanden
$string['miquiz_view_game_modes'] = 'Game modes (if productive)';

$string['miquiz_view_openlink'] = 'Open ' . $instance_name; //&ouml;ffnen
$string['miquiz_view_sync_questions'] = 'Sync questions';

$string['description'] = 'Description';
$string['task_sync_users_name'] = 'Sync quiz users with instance';

$string['miquiz_status_not_started'] = 'Inactive';
$string['miquiz_status_training'] = 'Training';
$string['miquiz_status_productive'] = 'Productive';
$string['miquiz_status_finished'] = 'Finished';

$string['miquiz_cockpit_total'] = 'Total';
$string['miquiz_cockpit_correct'] = 'Correct';
$string['miquiz_cockpit_incorrect'] = 'Incorrect';
$string['miquiz_cockpit_with_reports'] = 'With reports';

$string['pluginname'] = 'MI-Quiz';
$string['pluginadministration'] = 'MI-Quiz Plugin administration';
