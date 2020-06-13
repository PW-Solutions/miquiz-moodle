<?php

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'MI-Quiz';
$string['modulenameplural'] = 'MI-Quizze';

//$instance_name ='';
$instance_name = get_config('mod_miquiz', 'instancename');
$string['modulename_help'] = 'Erstelle eine ' . $instance_name . ' Aktivit&auml;t.';
$additional_info = get_config('mod_miquiz', 'additional_info');
if (!empty($additional_info)) {
    $string['modulename_help'] .= $additional_info;
}

//strings for Administration->Manage roles page
$string['miquiz:overview'] = 'Globale ' . $instance_name . ' Aktivit&auml;ts&uuml;bersicht';
$string['miquiz:addinstance'] = 'Erstelle eine ' . $instance_name . ' Aktivit&auml;t';

$string['miquiz_index_title_overview'] = $instance_name .' &Uuml;bersicht';
$string['miquiz_index_title'] = $instance_name .' Kurs &Uuml;bersicht';
$string['miquiz_index_reports'] = 'Anzahl Reports';
$string['miquiz_index_download'] = 'Statistiken herunterladen';
$string['miquiz_index_noquizselected'] = 'Bitte w&auml;hlen sie mindestens ein Quiz aus!';
$string['miquiz_index_overview'] = 'Zur globalen &Uuml;bersicht';

$string['miquiz_setting_info_title'] = 'Zus&auml;tzliche Info';
$string['miquiz_setting_info_helper'] = 'Erscheint beim Erstellen der Aktivit&auml;t';
$string['miquiz_setting_instanceurl_title'] = 'URL zur Quiz-Instanz';
$string['miquiz_setting_instanceurl_helper'] = 'Bitte die komplette URL zur Quiz-Instanz angeben, mit der dieses Plugin arbeiten soll';
$string['miquiz_setting_apikey_title'] = 'API-Key zur Quiz-Instanz';
$string['miquiz_setting_apikey_helper'] = 'Der API-Key ist notwendig, um eine Verbindung zu der Quiz-Instanz aufbauen zu k&ouml;nnen.';
$string['miquiz_setting_instancename_title'] = 'Instanzname';
$string['miquiz_setting_instancename_helper'] = 'Name der Quiz-Instanz, wie er in Moodle verwendet wird.';
$string['miquiz_setting_questiontimetag_title'] = 'Tagname f&uuml;r Beantwortungszeit';
$string['miquiz_setting_questiontimetag_helper'] = 'Name des Fragetags, dass die Beantwortungszeit festlegt. Der Tag setzt sich folgendermaßen zusammen: [tagname]:[beantwortungszeit]';
$string['miquiz_setting_questiondefaulttime_title'] = 'Default-Beantwortungszeit (in s)';
$string['miquiz_setting_questiondefaulttime_helper'] = 'Falls f&uuml;r eine Frage keine Beantwortungszeit festgelegt wurde, wird diese Zeit in Sekunden verwendet.';

$string['miquiz_create_name'] = 'Quiz-Name';
$string['miquiz_create_short_name'] = 'Abk&uuml;rzung';
$string['miquiz_create_timeuntilproductive'] = 'Ende der Trainingsphase';
$string['miquiz_create_scoremode'] = 'Bewertungsmodus';
$string['miquiz_create_scoremode_0'] = 'keine Punktvergabe';
$string['miquiz_create_scoremode_1'] = 'einfache Punktvergabe ohne Minuspunkte';
$string['miquiz_create_scoremode_2'] = 'einfache Punktvergabe mit Minuspunkten';
$string['miquiz_create_scoremode_3'] = 'relative Punktvergabe ohne Minuspunkte';
$string['miquiz_create_scoremode_4'] = 'relative Punktvergabe mit Minuspunkten';
$string['miquiz_create_scoremode_help'] = 'Punktvergabe w&auml;hrend der Produktivphase.
  Kann nachtr&auml;glich <strong>nicht</strong> ge&auml;ndert werden!
  <br><br>
  <strong>Einfache Punktvergabe</strong>: 1 Punkt, falls alle korrekten Antwortm&ouml;glichkeiten ausgew&auml;hlt wurden. Anderenfalls 0 Punkte ("ohne Minuspunkte") oder -1 Punkt ("mit Minuspunkten")
  <br><br>
  <strong>Relative Punktvergabe</strong>: Berechnung aus <code>(x / n) - (y / m)</code>, wobei der Wert bei "ohne Minuspunkte" nicht unter 0 Punkte sinken kann.
  <br>
  <i>x</i> = ausgew&auml;hlte korrekte Antwortm&ouml;glichkeiten<br>
  <i>n</i> = Anzahl korrekter Antwortm&ouml;glichkeiten<br>
  <i>y</i> = ausgew&auml;hlte falsche Antwortm&ouml;glichkeiten<br>
  <i>m</i> = Anzahl falscher Antwortm&ouml;glichkeiten<br>
';
$string['miquiz_create_game_modes'] = 'Spielmodus';
$string['miquiz_create_game_modes_help'] = 'Spielbare Formate w&auml;hrend der Produktivphase.
  Kann jederzeit ge&auml;ndert werden.
  <br><br>
  <strong>Zufallsduell</strong>: Ein/e zuf&auml;llige/r Gegner/in wird ausgew&auml;hlt.<br>
  <strong>Gezieltes Duell</strong>: Gegner/in kann ausgew&auml;hlt werden.<br>
  <br>
  Unabh&auml;ngig vom Spielmodus kann immer nur <strong>ein</strong> Spiel mit der/m gleichen Gegner/in gespielt werden.
';
$string['miquiz_create_game_mode_random_fight'] = 'Zufallsduell';
$string['miquiz_create_game_mode_picked_fight'] = 'Gezieltes Duell';
$string['miquiz_create_game_mode_solo_fight'] = 'Einzelspieler';
$string['miquiz_create_questions'] = 'Fragen';
$string['miquiz_create_questions_error'] = 'Ein Quiz ben&ouml;tigt mindestens drei Fragen!';
$string['miquiz_create_questions_selected'] = '${numquestions} Fragen ausgew&auml;lt';
$string['miquiz_create_questions_search'] = 'Suche';
$string['miquiz_create_questions_no_questions'] = 'Keine Fragen in diesem Kurs vorhanden.';
$string['miquiz_create_questions_create_questions'] = 'Bitte mindestens drei Fragen in diesem Kurs erstellen.';
$string['miquiz_create_assesstimestart'] = 'Quiz-Startdatum';
$string['miquiz_create_assesstimefinish'] = 'Quiz-Enddatum';
$string['miquiz_create_error_endbeforestart'] = 'Das Quiz-Enddatum muss nach dem Quiz-Startdatum liegen.';
$string['miquiz_create_error_betweenendstart'] = 'Das Enddatum der Trainingsphase muss zwischen dem Quiz-Start- und -Enddatum liegen.';
$string['miquiz_create_error_game_modes'] = 'Es muss mindestens ein Spielmodus ausgew&auml;hlt werden.';
$string['miquiz_create_statsonlyforfinishedgames'] = 'Nur beendete Spiele in Statistiken ber&uuml;cksichtigen.';
$string['miquiz_create_statsonlyforfinishedgames_help'] = 'Wenn diese Option aktiv ist werden in den Statistiken nur beendete Spiele ber&uuml;cksichtigt. Dies umfasst unter Anderem die Punktvergabe und die Berechnung der beantworten Fragen.';
$string['miquiz_create_activate_training_phase'] = 'Trainingsphase aktivieren';
$string['miquiz_create_activate_training_phase_help'] = 'W&auml;hrend der Trainingsphase sind nur Einzelspiele m&ouml;glich (Training) und es werden keine Punkte vergeben.';

$string['miquiz_view_overview'] = '&Uuml;bersicht';
$string['miquiz_view_coursename'] = 'Kurs';
$string['miquiz_view_shortname'] = 'Abk&uuml;rzung';
$string['miquiz_view_scoremode'] = 'Bewertungsmodus (wenn produktiv)';
$string['miquiz_view_questions'] = 'Fragen';
$string['miquiz_view_numquestions'] = 'Anzahl Fragen';
$string['miquiz_view_user'] = 'Mitspieler';
$string['miquiz_view_statistics_user'] = 'Statistiken (Mitspieler)';
$string['miquiz_view_statistics_username'] = 'Nutzername';
$string['miquiz_view_statistics_answeredquestionsabs'] = 'Beantwortete Fragen absolut (gesamt / richtig / falsch)';
$string['miquiz_view_statistics_answeredquestionsrel'] = 'Beantwortete Fragen relativ (richtig / falsch)';
$string['miquiz_view_statistics_totalscore'] = 'Punkte (erreicht/gesamt erreichbar)';
$string['miquiz_view_score'] = 'Deine Punkte: Training (Erreicht / M&ouml;glich), Duell (Erreicht / M&ouml;glich)';
$string['miquiz_view_answeredquestions'] = 'Beantwortete Fragen';
$string['miquiz_view_statsonlyforfinishedgames'] = 'Nur beendete Spiele';
$string['miquiz_view_nodata'] = 'Keine Daten vorhanden';
$string['miquiz_view_game_modes'] = 'Spielmodus (wenn produktiv)';

$string['miquiz_view_openlink'] =  $instance_name . ' &ouml;ffnen';
$string['miquiz_view_sync_questions'] = 'Fragen synchronisieren';

$string['miquiz_status_not_started'] = 'Inaktiv';
$string['miquiz_status_training'] = 'Training';
$string['miquiz_status_productive'] = 'Produktiv';
$string['miquiz_status_finished'] = 'Beendet';

$string['miquiz_cockpit_total'] = 'Insgesamt';
$string['miquiz_cockpit_correct'] = 'Richtig';
$string['miquiz_cockpit_incorrect'] = 'Falsch';
$string['miquiz_cockpit_with_reports'] = 'Mit R&uuml;ckmeldungen';

$string['description'] = 'Beschreibung';
$string['task_sync_users_name'] = 'Synchronisiere Quiz-User mit Instanz';

$string['pluginadministration'] = 'MI-Quiz Plugin administration';
