<?php

namespace mod_miquiz\task;

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
        cli_heading('MIQUIZ usersync');
        $currentTime = time();
        foreach($miquizs as $miquiz){
            // Check if sync is necessary
            if ( $miquiz->assesstimefinish <= $currentTime ||
                $miquiz->assesstimestart > ($currentTime + 60 * 10)
            ) {
                continue;
            }
            try {
                cli_write("syncing $miquiz->short_name (course $miquiz->course)\n");
                miquiz::sync_users($miquiz);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }        
    }
}
