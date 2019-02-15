<?php

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'MI-Quiz';
$string['modulenameplural'] = 'MI-Quizze';

//$instance_name ='';
$instance_name = get_config('mod_miquiz', 'instancename');
$string['modulename_help'] = 'Erstelle ein ' . $instance_name . ' Duell.';

$string['miquiz_index_title'] = $instance_name .' Kurs &Uuml;bersicht';
$string['miquiz_index_reports'] = 'Anzahl Reports';

$string['miquiz_setting_instanceurl_title'] = 'URL zur Quiz-Instanz';
$string['miquiz_setting_instanceurl_helper'] = 'Bitte die komplette URL zur Quiz-Instanz angeben, mit der dieses Plugin arbeiten soll';
$string['miquiz_setting_apikey_title'] = 'API-Key zur  Quiz-Instanz';
$string['miquiz_setting_apikey_helper'] = 'Der API-Key ist notwendig, um eine Verbindung zu der Quiz-Instanz aufbauen zu können.';
$string['miquiz_setting_loginprovider_title'] = 'Login-Provider';
$string['miquiz_setting_loginprovider_helper'] = 'Der Login-Provider legt fest, wie die User sich in der Quiz-Instanz einloggen.';
$string['miquiz_setting_modulename_title'] = 'Modulname';
$string['miquiz_setting_modulename_helper'] = 'Name des Modules in der Quiz-Instanz, in der alle Moodle-Inhalte gruppiert werden.';
$string['miquiz_setting_instancename_title'] = 'Instanzname';
$string['miquiz_setting_instancename_helper'] = 'Name der Quiz-Instanz, wie er in Moodle verwendet wird.';
$string['miquiz_setting_questiontimetag_title'] = 'Tagname für Beantwortungszeit';
$string['miquiz_setting_questiontimetag_helper'] = 'Name des Fragetags, dass die Beantwortungszeit festlegt. Der Tag setzt sich folgendermaßen zusammen: [tagname]:[beantwortungszeit]';
$string['miquiz_setting_questiondefaulttime_title'] = 'Default-Beantwortungszeit (in s)';
$string['miquiz_setting_questiondefaulttime_helper'] = 'Falls für eine Frage keine Beantwortungszeit festgelegt wurde, wird diese Zeit in Sekunden verwendet.';

$string['miquiz_create_name'] = 'Quiz Name';
$string['miquiz_create_short_name'] = 'Abk&uuml;rzung';
$string['miquiz_create_timeuntilproductive'] = 'Ende der Testphase';
$string['miquiz_create_scoremode'] = 'Bewertungsmodus';
$string['miquiz_create_scoremode_0'] = 'keine Punktvergabe';
$string['miquiz_create_scoremode_1'] = 'einfache Punktvergabe ohne Minuspunkte';
$string['miquiz_create_scoremode_2'] = 'einfache Punktvergabe mit Minuspunkten';
$string['miquiz_create_scoremode_3'] = 'relative Punktvergabe ohne Minuspunkte';
$string['miquiz_create_scoremode_4'] = 'relative Punktvergabe mit Minuspunkten';
$string['miquiz_create_questions'] = 'Fragen';
$string['miquiz_create_assesstimestart'] = 'Quiz Startdatum';
$string['miquiz_create_assesstimefinish'] = 'Quiz Enddatum';
$string['miquiz_create_error_unique'] = 'Diese Abk&uuml;rzung muss einmalig sein.';
$string['miquiz_create_error_endbeforestart'] = 'Das Quiz Enddatum muss später als das Quiz Startdatum sein.';
$string['miquiz_create_error_betweenendstart'] = 'Das Enddatum der Testphase muss zwischen dem Quiz Start- und Enddatum sein.';

$string['miquiz_view_overview'] = '&Uuml;bersicht';
$string['miquiz_view_shortname'] = 'Abk&uuml;rzung';
$string['miquiz_view_scoremode'] = 'Bewertungsmodus';
$string['miquiz_view_questions'] = 'Fragen';
$string['miquiz_view_numquestions'] = 'Anzahl Fragen';
$string['miquiz_view_user'] = 'Mitspieler';
$string['miquiz_view_statistics_user'] = 'Statistiken (Mitspieler)';
$string['miquiz_view_score'] = 'Deine Punke';
$string['miquiz_view_statistics_answeredquestions'] = 'Beantwortete Fragen absolut(gesamt/richtig/falsch), relativ(gesamt/richtig/falsch)';
$string['miquiz_view_statistics_totalscore'] = 'Punkte (erreicht/gesamt erreichbar)';
$string['miquiz_view_score'] = 'Deine Punke Training(Erreicht/M&ouml;glich), Duel(Erreicht/M&ouml;glich)';
$string['miquiz_view_openlink'] =  $instance_name . ' &ouml;ffnen';
$string['miquiz_view_nodata'] =  'Es sind noch keine Daten vorhanden.';

$string['miquiz_status_inactive'] = 'Inaktiv';
$string['miquiz_status_training'] = 'Training';
$string['miquiz_status_productive'] = 'Produktiv';
$string['miquiz_status_finished'] = 'Beendet';

$string['miquiz_cockpit_total'] = 'Insgesamt';
$string['miquiz_cockpit_correct'] = 'Richtig';
$string['miquiz_cockpit_incorrect'] = 'Falsch';

$string['description'] = 'Beschreibung';
$string['task_sync_users_name'] = 'Synchronisiere Quiz-User mit Instanz';

$string['pluginadministration'] = 'MI-Quiz Plugin administration';
