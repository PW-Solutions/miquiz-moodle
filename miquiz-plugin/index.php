<?php

require_once('../../config.php');
require_once("lib.php");

$id = required_param('id', PARAM_INT);           // Course ID

$PAGE->set_url('/mod/miquiz/index.php', array('id'=>$id));

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

echo $OUTPUT->header();
echo "TODO";
echo $OUTPUT->footer();
