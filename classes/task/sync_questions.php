<?php

namespace mod_miquiz\task;

require_once($CFG->dirroot.'/mod/miquiz/miquiz_api.php');

class sync_questions extends \core\task\adhoc_task
{

  private $activities;

  public function get_name()
  {
    return get_string('task_sync_questions', 'miquiz');
  }

  public function execute()
  {
    global $DB;

    $customData = $this->get_custom_data();
    if (empty($customData->activities)) {
      $allActivities = $DB->get_records('miquiz');

      $currentTime = time();
      $activities = array_filter($allActivities, function ($miquiz) use ($currentTime) {
        return $currentTime > ($miquiz->assesstimestart - 60 * 10) && $currentTime <= $miquiz->assesstimefinish;
      });
    } else {
      $activities = $DB->get_records_list('miquiz', 'id', $customData->activities);
    }

    // Better: get all questions where miquiz->id in all ids AND
    foreach ($activities as $miquiz) {
      $questionIds = \miquiz::getQuestionIdsForMiQuizId($miquiz->id);
      $questionsToSync = \miquiz::getQuestionsById($questionIds);
      foreach ($questionsToSync as $question) {
        \miquiz::createOrUpdateMiQuizQuestion($question, $miquiz->miquizcategoryid);
      }
    }
  }
}
