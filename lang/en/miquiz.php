<?php

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'MI-Quiz';
$string['modulenameplural'] = 'MI-Quizzes';

$instance_name = get_config('mod_miquiz', 'instancename');
$string['modulename_help'] = 'Challenge your students in a ' . $instance_name . ' duel.';

$string['miquiz_index_title'] = $instance_name .' Course Overview';
$string['miquiz_index_table_status'] = 'Status';
$string['miquiz_index_reports'] = 'Number of reports'; //Anzahl Reports
$string['miquiz_index_download'] = 'Download';
$string['miquiz_index_noquizselected'] = 'Please select at least one quiz!';

$string['miquiz_setting_instanceurl_title'] = 'URL for quiz instance';
$string['miquiz_setting_instanceurl_helper'] = 'Please add the full url to the quiz instance';
$string['miquiz_setting_apikey_title'] = 'API key for quiz instance';
$string['miquiz_setting_apikey_helper'] = 'The API key is necessary to make a connection to the quiz instance.';
$string['miquiz_setting_loginprovider_title'] = 'Login provider';
$string['miquiz_setting_loginprovider_helper'] = 'The login provider determines, how the user logs into the quiz instance.';
$string['miquiz_setting_modulename_title'] = 'Module name';
$string['miquiz_setting_modulename_helper'] = 'Name of the module in the quiz instance that groups all the Moodle content.';
$string['miquiz_setting_instancename_title'] = 'Instance name';
$string['miquiz_setting_instancename_helper'] = 'Name of the quiz instance how it is used in Moodle';
$string['miquiz_setting_questiontimetag_title'] = 'Tag name for time to answer';
$string['miquiz_setting_questiontimetag_helper'] = 'First part of the question tag that determines the time to answer. The complete tag is structered like this: [tagname]:[time_to_answer]';
$string['miquiz_setting_questiondefaulttime_title'] = 'Default time to answer (in s)';
$string['miquiz_setting_questiondefaulttime_helper'] = 'In case no specific time to answer is set, this default time (in seconds) will be used.';

$string['miquiz_create_name'] = 'Quiz Name';
$string['miquiz_create_short_name'] = 'Short Name';
$string['miquiz_create_timeuntilproductive'] = 'Test Phase Ending Time';
$string['miquiz_create_scoremode'] = 'Rating Mode';
$string['miquiz_create_scoremode_0'] = 'no rating';
$string['miquiz_create_scoremode_1'] = 'simple rating without demerit';
$string['miquiz_create_scoremode_2'] = 'simple rating with demerit';
$string['miquiz_create_scoremode_3'] = 'relative rating without demerit';
$string['miquiz_create_scoremode_4'] = 'relative rating with demerit';
$string['miquiz_create_questions'] = 'Questions';
$string['miquiz_create_questions_error'] = 'A quiz requires at least one question!';
$string['miquiz_create_questions_selected'] = '${numquestions} questions selected';
$string['miquiz_create_questions_search'] = 'Search';
$string['miquiz_create_assesstimestart'] = 'Quiz Start Date';
$string['miquiz_create_assesstimefinish'] = 'Quiz Stop Date';
$string['miquiz_create_error_unique'] = 'Has to be unique.';
$string['miquiz_create_error_endbeforestart'] = 'Quiz end date date has to be later than quiz start date.';
$string['miquiz_create_error_betweenendstart'] = 'Test phase ending time has to be between quiz start and end date.';
$string['miquiz_create_statsonlyforfinishedgames'] = 'Consider only finished games in statistics.';
$string['miquiz_create_statsonlyforfinishedgames_help'] = 'If this flag is set, only finished games are considered in the statistics. This includes the rating and calculation of answered questions.';

$string['miquiz_view_overview'] = 'Overview'; //&Uuml;bersicht
$string['miquiz_view_name'] = 'Name';
$string['miquiz_view_shortname'] = 'Short name'; //Abk&uuml;rzung
$string['miquiz_view_scoremode'] = 'Rating mode'; //Bewertungsmodus
$string['miquiz_view_questions'] = 'Questions'; //Fragen
$string['miquiz_view_numquestions'] = 'Number of questions'; //Anzahl Fragen
$string['miquiz_view_user'] = 'Player'; //Mitspieler
$string['miquiz_view_statistics_user'] = 'Statistics (Players)';
$string['miquiz_view_score'] = 'Your score'; //Deine Punke
$string['miquiz_view_statistics_answeredquestionsabs'] = 'Answered questions absolute(total/correct/wrong)'; //Beantwortete Fragen (gesamt/richtig/falsch, absolut/relativ)
$string['miquiz_view_statistics_answeredquestionsrel'] = 'Answered questions relative(total/correct/wrong)'; //Beantwortete Fragen (gesamt/richtig/falsch, absolut/relativ)
$string['miquiz_view_statistics_username'] = 'Username'; //Nutzername
$string['miquiz_view_statistics_totalscore'] = 'Score (achieved/maximum)'; //Punkte (erreicht/gesamt erreichbar)
$string['miquiz_view_score'] = 'Your score training(achieved/maximum), duel(achieved/maximum)'; //Deine Punke
$string['miquiz_view_answeredquestions'] = 'Answered questions'; //Beantwortete Fragen
$string['miquiz_view_statsonlyforfinishedgames'] = 'Finished games only'; //Nur beendete Spiele

$string['miquiz_view_openlink'] = 'Open ' . $instance_name; //$string['modulename'] &ouml;ffnen

$string['description'] = 'Description';
$string['task_sync_users_name'] = 'Sync quiz users with instance';

$string['miquiz_status_inactive'] = 'Inactive';
$string['miquiz_status_training'] = 'Training';
$string['miquiz_status_productive'] = 'Productive';
$string['miquiz_status_finished'] = 'Finished';

$string['miquiz_cockpit_total'] = 'Total';
$string['miquiz_cockpit_correct'] = 'Correct';
$string['miquiz_cockpit_incorrect'] = 'Incorrect';

$string['pluginname'] = 'MI-Quiz';
$string['pluginadministration'] = 'MI-Quiz Plugin administration';
