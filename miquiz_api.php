<?php

defined('MOODLE_INTERNAL') || die();

class miquiz
{
    public static function api_get_base_crl($endpoint, $asJson = true)
    {
        $url = get_config('mod_miquiz', 'instanceurl') . "/" . $endpoint;
        $accesstoken = get_config('mod_miquiz', 'apikey');
        $headr = array();
        if ($asJson) {
            $headr[] = 'Content-type: application/json';
        }
        $headr[] = 'Accept: application/json';
        $headr[] = 'Authorization: Bearer ' . $accesstoken;

        $crl = curl_init();
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        return $crl;
    }

    public static function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }

    public static function api_send($endpoint, $crl, $config=array())
    {
        $reply = curl_exec($crl);

        if ($reply === false) {
            throw new Exception('Curl error: ' . curl_error($crl));
        }
        $info = curl_getinfo($crl);
        if (!in_array($info['http_code'], [200, 201, 204])) {
            $error_ob = [
                "url" => $info['url'],
                'http_code' => $info['http_code'],
            ];
            if ($info['http_code'] == 422) {  # print response if api was not used properly
                $error_ob['reply'] = $reply;
            }
            if ($info['http_code'] == 401) {
                $error_ob['reply'] = $reply;
                $error_ob['info'] = 'Please check the provided API key in settings';
            }
            if ($info['http_code'] == 403) {
                $error_ob['reply'] = $reply;
            }
            throw new Exception('MI-Quiz API Error: ' . json_encode($error_ob, true) . "\n");
        }

        curl_close($crl);

        if (!empty($config['return_raw']))
            return $reply;
        return json_decode($reply, true);
    }

    public static function api_get($endpoint, $config=array())
    {
        $crl = miquiz::api_get_base_crl($endpoint);
        curl_setopt($crl, CURLOPT_HTTPGET, true);
        $response = miquiz::api_send($endpoint, $crl, $config);
        return $response;
    }

    public static function api_post($endpoint, $data=array(), $config=array())
    {
        $crl = miquiz::api_get_base_crl($endpoint);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($data));
        return miquiz::api_send($endpoint, $crl, $config);
    }

    public static function api_put($endpoint, $data=array(), $config=array())
    {
        $crl = miquiz::api_get_base_crl($endpoint);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($data));
        return miquiz::api_send($endpoint, $crl, $config);
    }

    public static function api_delete($endpoint, $data = [], $config=array())
    {
        $crl = miquiz::api_get_base_crl($endpoint);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "DELETE");
        if (!empty($data)) {
            curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        return miquiz::api_send($endpoint, $crl, $config);
    }

    public static function create($miquiz)
    {
        $categoryObject = [
            'active' => false,
            'fullName' => $miquiz->name,
            'name' => $miquiz->short_name,
            'stats_only_for_finished_games' => $miquiz->statsonlyforfinishedgames,
        ];
        $resp = miquiz::api_post('api/categories', $categoryObject);
        $catid = (int)$resp['id'];
        $miquiz->miquizcategoryid = $catid;
        $questionIds = explode(',', $miquiz->questions);
        $questions = miquiz::getQuestionsById($questionIds);

        $miquiz_qids = [];
        foreach ($questions as $question) {
            $miquiz_qids[$question->id] = miquiz::createOrUpdateMiQuizQuestion($question, $catid);
        }

        miquiz::scheduleTasks($miquiz);

        return ['catid' => $catid, 'qids' => $miquiz_qids];
    }

    public static function getQuestionsById($questionIds)
    {
        global $DB;
        $filteredQuestionIds = array_filter($questionIds, function ($questionId) {
            return !empty($questionId) && is_numeric($questionId);
        });
        if (empty($filteredQuestionIds)) {
            return [];
        }
        return $DB->get_records_list('question', 'id', $filteredQuestionIds);
    }

    public static function createOrUpdateMiQuizQuestion($question, $miQuizCategoryId)
    {
        $miQuizQuestionId = miquiz::getMiQuizQuestionId($question->id);
        if (is_null($miQuizQuestionId) || !miquiz::miQuizQuestionExistsInMiQuiz($miQuizQuestionId)) {
            $questionData = miquiz::getQuestionData($question, $miQuizCategoryId);
            $miQuizQuestion = miquiz::createMiQuizQuestion($questionData);
            $miQuizQuestionId = (int) $miQuizQuestion['id'];
        } else {
            $questionUpdateTimestamp = $question->timemodified;
            if ($questionUpdateTimestamp > miquiz::getMiQuizQuestionRelationTimestamp($question->id)) {
                $questionData = miquiz::getQuestionData($question, $miQuizCategoryId);
                // Attention: this will overwrite the question in miquiz. Maybe we should add check and if question is different, we create it as new
                // Difficulty: images are included in moodle question and not in mi quiz question -> comparison complex. Maybe timestamps?
                miquiz::updateMiQuizQuestion($questionData, $miQuizQuestionId);
                miquiz::updateMiQuizQuestionRelationTimestamp($question->id);
            }
        }
        return $miQuizQuestionId;
    }

    private static function getMiQuizQuestionId($questionId, $activityId = null)
    {
        $miQuizQuestionIds = miquiz::getMiQuizQuestionIds([$questionId], $activityId);
        if (empty($miQuizQuestionIds)) {
            return;
        }
        return array_values($miQuizQuestionIds)[0];
    }

    private static function getMiQuizQuestionIds($questionIds, $activityId = null)
    {
        global $DB;
        if (empty($questionIds)) {
            return [];
        }
        $select = 'questionid IN (' . implode(',', $questionIds) . ')';
        if (!is_null($activityId)) {
            $select .= ' AND quizid = ' . $activityId;
        }
        $existingQuestions = $DB->get_records_select('miquiz_questions', $select);

        if (empty($existingQuestions)) {
            return [];
        }
        return array_map(function ($question) {
            return (int) $question->miquizquestionid;
        }, $existingQuestions);
    }

    private static function getMiQuizQuestionRelationTimestamp($questionId)
    {
        global $DB;
        if (is_null($questionId)) {
            return;
        }
        $select = 'questionid = ' . $questionId;
        $existingQuestions = $DB->get_records_select('miquiz_questions', $select);
        if (empty($existingQuestions)) {
            return;
        }
        // Return only for the first, because the creation timestamp should be roughly the same for all
        return array_values($existingQuestions)[0]->timecreated;
    }

    private static function updateMiQuizQuestionRelationTimestamp($questionId)
    {
        global $DB;
        if (is_null($questionId)) {
            return;
        }
        $query = 'update {miquiz_questions} set timecreated = ' . time() . ' where questionid = ' . $questionId;
        $result = $DB->execute($query);
        return $result;
    }

    private static function getQuestionData($question, $miQuizCategoryId)
    {
        global $DB, $COURSE;
        $context = context_course::instance($COURSE->id);

        $possibilities = $DB->get_records('question_answers', ['question' => $question->id]);
        $json_possibilities = [];
        foreach ($possibilities as $possibility) {
            $possibilityDescription = miquiz::addImage($possibility->answer, $context->id, 'question', 'answer', $possibility->id);
            $json_possibilities[] = [
                'description' => $possibilityDescription,
                'isCorrect' => ((float) $possibility->fraction) > 0,
            ];
        }

        $questionDescription = miquiz::addImage($question->questiontext, $context->id, 'question', 'questiontext', $question->id);

        return [
            'description' => ['text' => $questionDescription],
            'possibilities' => $json_possibilities,
            'comment' => ['text' => $question->generalfeedback],
            'status' => 'active',
            'timeToAnswer' => get_question_answeringtime($question->id),
            'categories' => [['id' => $miQuizCategoryId]],
        ];
    }

    private static function createMiQuizQuestion($questionData)
    {
        $response = miquiz::api_post('api/questions', $questionData);
        return $response['question'];
    }

    private static function updateMiQuizQuestion($questionData, $miQuizQuestionId)
    {
        $response = miquiz::api_put('api/questions/' . $miQuizQuestionId, $questionData);
        return $response['question'];
    }

    private static function miQuizQuestionExistsInMiQuiz($miQuizQuestionId)
    {
        return !is_null(miquiz::getMiQuizQuestion($miQuizQuestionId));
    }

    private static function getMiQuizQuestion($miQuizQuestionId)
    {
        try {
            $miQuizQuestion = miquiz::api_get('api/questions/' . $miQuizQuestionId);
        } catch (Exception $e) {
            return;
        }
        return $miQuizQuestion;
    }

    public static function addImage($string, $contextId, $component, $filearea, $objectId)
    {
        global $CFG;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextId, $component, $filearea, $objectId);
        if (empty($files) || strpos($string, '@@PLUGINFILE@@') === false) {
            return $string;
        }
        foreach ($files as $file) {
            if (!in_array($file->get_mimetype(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
                continue;
            }
            $hash = $file->get_contenthash();
            $filePath = implode('/', [
                $CFG->dataroot,
                'filedir',
                substr($hash, 0, 2),
                substr($hash, 2, 2),
                $hash
            ]);
            if (!file_exists($filePath)) {
                error_log('miquiz: could not find file: ' . $filePath . ' (MIME: ' . $file->get_mimetype() . ')');
                continue;
            }
            $fileUrl = self::uploadFile($filePath, $file->get_filename(), $file->get_mimetype());
            if (!empty($fileUrl)) {
                $string = str_replace('@@PLUGINFILE@@/' . rawurlencode($file->get_filename()), $fileUrl, $string);
            }
        }
        return $string;
    }

    private static function uploadFile($filepath, $filename, $mimetype)
    {
        $endpoint = 'api/upload';
        $fileData = [
            'file' => new CURLFile($filepath, $mimetype, $filename),
        ];
        $crl = miquiz::api_get_base_crl($endpoint, false);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($crl, CURLOPT_POSTFIELDS, $fileData);
        $response = miquiz::api_send($endpoint, $crl);
        if (!isset($response['success']) || !$response['success']) {
            error_log('miquiz: could not upload file ' . $filename);
            return null;
        }
        return $response['src'];
    }

    public static function update($miquiz)
    {
        $miQuizCategoryId = $miquiz->miquizcategoryid;

        $existingQuestionIds = miquiz::getQuestionIdsForMiQuizId($miquiz->id);
        $newQuestionIds = array_map(function ($id) { return (int) $id; }, explode(',', $miquiz->questions));

        $questionIdsToAdd = array_diff($newQuestionIds, $existingQuestionIds);
        $questionsToAdd = miquiz::getQuestionsById($questionIdsToAdd);
        $mappedMiQuizQuestionIds = [];
        foreach ($questionsToAdd as $question) {
            $mappedMiQuizQuestionIds[$question->id] = miquiz::createOrUpdateMiQuizQuestion($question, $miQuizCategoryId);
        }

        $questionIdsToRemove = array_diff($existingQuestionIds, $newQuestionIds);
        $miQuestionIdsToRemove = miquiz::getMiQuizQuestionIds($questionIdsToRemove, $miquiz->id);
        if (!empty($miQuestionIdsToRemove)) {
            $removeRelationshipPayload = [
                'data' => array_map(function ($questionId) {
                    return [
                        'type' => 'questions',
                        'id' => $questionId,
                    ];
                }, $miQuestionIdsToRemove),
            ];
            $response = miquiz::api_delete('api/categories/' . $miQuizCategoryId . '/relationships/questions', $removeRelationshipPayload);
        }

        miquiz::scheduleTasks($miquiz);

        return [
            'addedQuestionIds' => $questionIdsToAdd,
            'removedQuestionIds' => $questionIdsToRemove,
            'miQuizQuestionIds' => $mappedMiQuizQuestionIds,
        ];
    }

    private static function getQuestionsForMiQuizId($miquizId)
    {
        global $DB;

        return $DB->get_records('miquiz_questions', ['quizid' => $miquizId]);
    }

    public static function getQuestionIdsForMiQuizId($miquizId)
    {
        $questions = miquiz::getQuestionsForMiQuizId($miquizId);
        $questionIds = array_map(function ($question) {
            return (int) $question->questionid;
        }, $questions);
        return $questionIds;
    }

    // Soft delete a category
    public static function delete($miquiz)
    {
        miquiz::deleteTasks($miquiz);
        $resp = miquiz::api_put("api/categories/" . $miquiz->miquizcategoryid, array("active" => false));
        return true;
    }

    public static function forceDelete($miquiz)
    {
        if (empty($miquiz) || empty($miquiz->miquizcategoryid)) {
            error_log('miquiz: forceDelete failed. no categoryId found');
            return false;
        }
        $categoryId = $miquiz->miquizcategoryid;
        $resp = miquiz::api_delete('api/categories/' . $categoryId);
        if (empty($resp) || !isset($resp['success'])) {
            error_log("miquiz: forceDelelte failed (category: $categoryId)");
            return false;
        }

        if (!$resp['success']) {
            $error = $resp['error'];
            error_log("miquiz: forceDelete failed (category: $categoryId): $error");
            return false;
        }

        return true;
    }

    public static function deleteTasks($miquiz)
    {
        $oldTasks = miquiz::api_get("api/tasks?filter[resource]=categories&filter[resourceId]=".$miquiz->miquizcategoryid);
        foreach ($oldTasks['data'] as $a_task) {
            miquiz::api_delete("api/tasks/".$a_task["id"]);
        }
    }

    public static function getCurrentStateForQuiz($miquiz)
    {
        $stateTimestamps = self::getStateTimestampsForQuiz($miquiz);
        $currentTime = time();
        return self::getStateAtTimestamp($stateTimestamps, $currentTime);
    }

    private static function getStateTimestampsForQuiz($miquiz)
    {
        return [
            'not_started' => 0,
            'training' => $miquiz->assesstimestart,
            'productive' => $miquiz->timeuntilproductive,
            'finished' => $miquiz->assesstimefinish,
        ];
    }

    public static function scheduleTasks($miquiz)
    {
        $currentTime = time();
        $categoryId = (string) $miquiz->miquizcategoryid;

        $stateTimestamps = self::getStateTimestampsForQuiz($miquiz);

        $scoreModes = [
            1 => 'rating_without_demerit',
            2 => 'rating_with_demerit',
            3 => 'relative_rating_without_demerit',
            4 => 'relative_rating_with_demerit',
        ];
        $scoremode = 'no_rating';
        if (!is_null($miquiz->scoremode) && isset($scoreModes[$miquiz->scoremode])) {
            $scoremode = $scoreModes[$miquiz->scoremode];
        }

        // Delete old tasks for this category
        miquiz::deleteTasks($miquiz);

        // Configure current state
        $currentState = self::getStateAtTimestamp($stateTimestamps, $currentTime);
        $currentStateConfig = self::getConfigForState($currentState, $scoremode, $miquiz->duelmode_in_productive);
        self::scheduleTaskForCategory($categoryId, $stateTimestamps[$currentState], $currentStateConfig);

        // Configure future states
        $futureStates = self::getStatesAfterTimestamp($stateTimestamps, $currentTime);
        foreach ($futureStates as $state) {
            $stateConfig = self::getConfigForState($state, $scoremode, $miquiz->duelmode_in_productive);
            self::scheduleTaskForCategory($categoryId, $stateTimestamps[$state], $stateConfig);
        }

        $stateIndependentConfig = [
            'fullName' => $miquiz->name,
            'name' => $miquiz->short_name,
            'stats_only_for_finished_games' => $miquiz->statsonlyforfinishedgames,
        ];
        self::scheduleTaskForCategory($categoryId, $currentTime - 1, $stateIndependentConfig);
    }

    private static function getStateAtTimestamp($stateTimestamps, $timestamp)
    {
        foreach (array_reverse($stateTimestamps) as $state => $stateTimestamp) {
            if ($stateTimestamp <= $timestamp) {
                return $state;
            }
        }
    }

    private static function getStatesAfterTimestamp($stateTimestamps, $timestamp)
    {
        return array_keys(
            array_filter($stateTimestamps, function ($stateTimestamp) use ($timestamp) {
                return $stateTimestamp > $timestamp;
            })
        );
    }

    private static function getConfigForState($state, $scoreMode, $duelmode_in_productive)
    {
        $active = in_array($state, ['training', 'productive']);
        $scoreStrategy = $state === 'productive' ? $scoreMode : 'no_rating';
        if($state === 'productive') {
            $enabledModes = 'random-fight';
            if($duelmode_in_productive)
                $enabledModes .= ',duel';
        } else {
            $enabledModes = 'training';
        }

        return [
            'active' => $active,
            'scoreStrategy' => $scoreStrategy,
            'enabledModes' => $enabledModes,
        ];
    }

    private static function scheduleTaskForCategory($id, $timestamp, $data)
    {
        $task = [
            "type" => "tasks",
            "attributes" => [
                "trigger" => [
                    "type" => "timestamp",
                    "operator" => ">=",
                    "value" => (string) $timestamp,
                ],
                "resourceType" => "categories",
                "resourceId" => $id,
                "action" => "update",
                "data" => $data,
            ]
        ];
        miquiz::api_post("api/tasks", ["data" => $task]);
    }

    public static function sync_users($miquiz)
    {
        global $DB;

        // get users which can access miquiz
        $context = context_course::instance($miquiz->course);
        $enrolled = get_enrolled_users($context);
        $course = $DB->get_record('course', array('id'=> $miquiz->course));
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($miquiz->coursemodule);
        $info = new core_availability\info_module($cm);
        $enrolled = $info->filter_user_list($enrolled);

        //sync with db
        $activity_users = $DB->get_records('miquiz_users', array('quizid' => $miquiz->id));

        // Create users not existing in mi-quiz
        $miquiz_users = miquiz::api_get('api/users?fields[users]=id,externalLogin');
        $miquiz_users_dirty = false;
        foreach ($enrolled as $user) {
            $username = $user->username;
            if (defined('CLI_SCRIPT') && CLI_SCRIPT === true) {
                cli_write("    $username");
            } else {
                echo " $username";
            }
            $found = false;
            foreach ($miquiz_users as $miquiz_user) {
                if ($miquiz_user['externalLogin'] === $username) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                try {
                    $resp = miquiz::api_post('api/users', ['externalLogin' => $username]);
                    if (defined('CLI_SCRIPT') && CLI_SCRIPT === true) {
                        cli_write(" synced\n");
                    } else {
                        echo " synced<br>";
                    }
                    $miquiz_users_dirty = true;
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            } else {
                if (defined('CLI_SCRIPT') && CLI_SCRIPT === true) {
                    cli_write(" OK\n");
                } else {
                    echo " OK<br>";
                }
            }
        }

        // call again to get ids for new users
        if ($miquiz_users_dirty) {
            $miquiz_users = miquiz::api_get("api/users?fields[users]=id,externalLogin");
        }

        //create non existing user links
        foreach ($enrolled as $a_user) {
            $found = false;
            foreach ($activity_users as $b_user) {
                if ($a_user->id == $b_user->userid) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $added_user = array(
                    'quizid' => $miquiz->id,
                    'userid' => $a_user->id,
                    'timecreated' => time()
                );
                $DB->insert_record("miquiz_users", $added_user);
            }
        }

        //detete not any longer existing user links
        foreach ($activity_users as $a_user) {
            $found = false;
            foreach ($enrolled as $b_user) {
                if ($a_user->id == $b_user->id) {
                    $found = true;
                }
                break;
            }
            if (!$found) {
                $DB->delete_records("miquiz_users", array("quizid" => $miquiz->id, "userid" => $a_user->id));
            }
        }

        // send patch to miquiz to update user links
        $user_patch = [];
        foreach ($enrolled as $a_user) {
            $a_user_id = miquiz::get_user_id($a_user->username, $miquiz_users);
            if (is_null($a_user_id)) {
                continue;
            }
            $user_patch[] = [
                'type' => 'users',
                'id' => (string)$a_user_id,
            ];
        }
        $resp = miquiz::api_post('api/categories/' . $miquiz->miquizcategoryid . '/relationships/players', ['data' => $user_patch]);
        return $enrolled;
    }

    public static function get_user_id($username, $miquiz_users = null)
    {
        if (is_null($miquiz_users)) {
            $miquiz_users = miquiz::api_get("api/users?fields[users]=id,externalLogin&filter[externalLogin]=$username");
        }
        if (count($miquiz_users) > 0) {
            foreach ($miquiz_users as $miquiz_user) {
                if ($miquiz_user['externalLogin'] === $username) {
                    return $miquiz_user['id'];
                }
            }
        }
        return;
    }

    public static function get_username($id, $miquiz_users = null)
    {
        if (is_null($miquiz_users)) {
            $miquiz_users = miquiz::api_get("api/users?fields[users]=id,externalLogin&filter[id]=$id");
        }
        if (count($miquiz_users) > 0) {
            foreach ($miquiz_users as $miquiz_user) {
                if ($miquiz_user['id'] == $id) {
                    return $miquiz_user['externalLogin'];
                }
            }
        }
        return;
    }
}
