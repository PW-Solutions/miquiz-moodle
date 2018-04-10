<?php

namespace mod_miquiz\task;
require_once($CFG->dirroot.'/mod/miquiz/miquiz_api.php');

class sync_users extends \core\task\scheduled_task
{
    public function get_name()
    {
        return get_string('task_sync_users_name', 'miquiz');
    }

    public function execute()
    {
        global $DB;
        $miquizs = $DB->get_records("miquiz");
        if (defined('CLI_SCRIPT') && CLI_SCRIPT === true) cli_heading('MIQUIZ usersync');
        else echo 'MIQUIZ usersync<br>';
        $currentTime = time();
        foreach($miquizs as $miquiz){
            // Check if sync is necessary
            if ( $miquiz->assesstimefinish <= $currentTime ||
                $miquiz->assesstimestart > ($currentTime + 60 * 10)
            ) {
                continue;
            }
            try {
               if (defined('CLI_SCRIPT') && CLI_SCRIPT === true) cli_write("syncing $miquiz->short_name (course $miquiz->course)\n");
               else echo "syncing $miquiz->short_name (course $miquiz->course)<br>";
                \miquiz::sync_users($miquiz);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
