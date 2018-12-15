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

$cms = [];
foreach($res as $a_cm_entry){
    $cms[] = get_coursemodule_from_id('miquiz', $a_cm_entry->id, 0, false, MUST_EXIST);
}

$url = new moodle_url('/mod/miquiz/index.php', array('id'=>$id));
$PAGE->set_url($url);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('miquiz_index_title', 'miquiz'));
foreach($cms as $a_cm){
    print_r($a_cm);
}
echo $OUTPUT->footer();