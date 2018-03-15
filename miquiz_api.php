<?php

defined('MOODLE_INTERNAL') || die();

class miquiz {
    static function api_get_base_crl($endpoint) {
        global $CFG;

        $url = $CFG->miquiz_baseurl . $endpoint;
        $headr = array();
        $headr[] = 'Accept: application/json';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: Bearer '.$CFG->miquiz_apikey;

        $crl = curl_init();
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        return $crl;
    }

    static function api_send($endpoint, $crl) {
        $reply = curl_exec($crl);

        if ($reply === false) {
            throw new Exception('Curl error: ' . curl_error($crl));
        }
        $info = curl_getinfo($crl);
        if($info['http_code'] != 200 && $info['http_code'] != 201 && $info['http_code'] != 204){
            $error_ob = [
                "url" => $info['url'],
                'http_code' => $info['http_code'],
            ];
            if($info['http_code'] == 422)  # print response if api was not used properly
                $error_ob['reply'] = $reply;
            throw new Exception('mi-quiz api error: ' . json_encode($error_ob, True) . "\n");
        }

        curl_close($crl);
        return json_decode($reply, true);
    }

    static function api_get($endpoint) {
        $crl = miquiz::api_get_base_crl($endpoint);
        curl_setopt($crl, CURLOPT_HTTPGET, true);
        return miquiz::api_send($endpoint, $crl);
    }

    static function api_post($endpoint, $data=array()) {
        $crl = miquiz::api_get_base_crl($endpoint);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($data));
        return miquiz::api_send($endpoint, $crl);
    }

    static function api_delete($endpoint) {
        $crl = miquiz::api_get_base_crl($endpoint);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "DELETE");
        return miquiz::api_send($endpoint, $crl);
    }

    function create($miquiz){
        global $DB;

        $moduleid = miquiz::get_module_id();
        $resp = miquiz::api_post("api/categories", array("parent" => $moduleid,
                                                         "active" => False,
                                                         "fullName" => $miquiz->name,
                                                         "name" => $miquiz->short_name));
        $catid = (int)$resp['id'];
        $miquiz->miquizcategoryid = $catid;

        //get questions from moodle
        $question_ids = $miquiz->questions;
        $query_question_ids = "";
        foreach($question_ids as $question_id){
            if($query_question_ids != ""){
                $query_question_ids .= " OR ";
            }
            $query_question_ids .="id=".$question_id;
        }
        $questions = $DB->get_records_sql('SELECT * FROM {question} q WHERE '. $query_question_ids);

        $miquiz_qids = [];
        foreach($questions as $question){
            $possibilities = $DB->get_records('question_answers', array('question' => $question->id));
            $json_possibilities = [];
            foreach($possibilities as $possibility)
                $json_possibilities[] = ["description" => $possibility->answer, "isCorrect" => ((float)$possibility->fraction) > 0];

            $resp = miquiz::api_post("api/questions", ["description" => ["text" => $question->questiontext],
                                                   "possibilities" => $json_possibilities,
                                                   "comment" => ["text" => $question->generalfeedback],
                                                   "status" => "active",
                                                   "timeToAnswer" => get_question_answeringtime($question->id),
                                                   "categories" => [["id" => $catid]]]);
            $miquiz_qids[$question->id] = (int)$resp["question"]["id"];
        }

        miquiz::scheduleTasks($miquiz);

        return ['catid' => $catid, 'qids' => $miquiz_qids];
    }

    function update($miquiz){
        global $DB;
        miquiz::scheduleTasks($miquiz);
        return True;
    }

    function delete($miquiz){
        miquiz::deleteTasks($miquiz);
        $resp = miquiz::api_post("api/categories/" . $miquiz->miquizcategoryid, array("active" => False));
        return True;
    }

    static function deleteTasks($miquiz){
        $oldTasks = miquiz::api_get("api/tasks?filter[resource]=categories&filter[resourceId]=".$miquiz->miquizcategoryid);
        foreach($oldTasks['data'] as $a_task){
            miquiz::api_delete("api/tasks/".$a_task["id"]);
        }
    }

    function scheduleTasks($miquiz){
        miquiz::deleteTasks($miquiz);

        $currentTime = time();

        switch ($miquiz->scoremode) {
            case 1:
                $scoremode = "rating_without_demerit";
                break;
            case 2:
                $scoremode = "rating_with_demerit";
                break;
            case 3:
                $scoremode = "relative_rating_without_demerit";
                break;
            case 4:
                $scoremode = "relative_rating_with_demerit";
                break;
            default:
                $scoremode = "no_rating";
        }

        //set category to current status
        if($miquiz->assesstimestart>$currentTime){
            $is_active = False;
            $score_Strategy = "no_rating";
            $enabled_game_modes = 'training';
        }
        else if($miquiz->assesstimestart<=$currentTime &&
                $miquiz->assesstimefinish>$currentTime &&
                $miquiz->timeuntilproductive>$currentTime){
            $is_active = True;
            $score_Strategy = "no_rating";
            $enabled_game_modes = 'training';
        }
        else if($miquiz->assesstimestart<=$currentTime &&
                $miquiz->assesstimefinish>$currentTime &&
                $miquiz->timeuntilproductive<=$currentTime){
            $is_active = True;
            $score_Strategy = $scoremode;
            $enabled_game_modes = "random-fight";
        }
        else if($miquiz->assesstimefinish<=$currentTime){
            $is_active = False;
        }

        $data = [];
        if (isset($is_active)) {
            $data['active'] = $is_active;
        }
        if (isset($score_Strategy)) {
            $data['scoreStrategy'] = $score_Strategy;
        }
        if (isset($enabled_game_modes)) {
            $data['enabledModes'] = $enabled_game_modes;
        }

        $task = [
            "type" => "tasks",
            "attributes" => [
                  "trigger" => [
                    "type" => "timestamp",
                    "operator" => ">=",
                    "value" => (string)($currentTime-1)
                  ],
                  "resourceType" => "categories",
                  "resourceId" => (string)$miquiz->miquizcategoryid,
                  "action" => "update",
                  "data" => $data,
            ]
        ];
        miquiz::api_post("api/tasks", array("data" => $task));
        //change status in the future
        if($miquiz->assesstimestart>$currentTime){
            $task = [
                "type" => "tasks",
                "attributes" => [
                      "trigger" => [
                        "type" => "timestamp",
                        "operator" => ">=",
                        "value" => (string)$miquiz->assesstimestart
                      ],
                      "resourceType" => "categories",
                      "resourceId" => (string)$miquiz->miquizcategoryid,
                      "action" => "update",
                      "data" => [
                          "active" => True,
                          "scoreStrategy" => "no_rating",
                          "enabledModes" => "training"
                      ]
                ]
            ];
            miquiz::api_post("api/tasks", array("data" => $task));
        }

        if($miquiz->assesstimefinish>$currentTime){
            $task = [
                "type" => "tasks",
                "attributes" => [
                      "trigger" => [
                        "type" => "timestamp",
                        "operator" => ">=",
                        "value" => (string)$miquiz->assesstimefinish
                      ],
                      "resourceType" => "categories",
                      "resourceId" => (string)$miquiz->miquizcategoryid,
                      "action" => "update",
                      "data" => [
                          "active" => False
                      ]
                ]
            ];
            miquiz::api_post("api/tasks", array("data" => $task));
        }

        if($miquiz->timeuntilproductive>$currentTime){
            $task = [
                "type" => "tasks",
                "attributes" => [
                      "trigger" => [
                        "type" => "timestamp",
                        "operator" => ">=",
                        "value" => (string)$miquiz->timeuntilproductive
                      ],
                      "resourceType" => "categories",
                      "resourceId" => (string)$miquiz->miquizcategoryid,
                      "action" => "update",
                      "data" => [
                          "scoreStrategy" => $scoremode,
                          "enabledModes" => "random-fight"
                      ]
                ]
            ];
            miquiz::api_post("api/tasks", array("data" => $task));
        }

        //set/update full-name
        $task = [
            "type" => "tasks",
            "attributes" => [
                  "trigger" => [
                    "type" => "timestamp",
                    "operator" => ">=",
                    "value" => (string)(time()-1)
                  ],
                  "resourceType" => "categories",
                  "resourceId" => (string)$miquiz->miquizcategoryid,
                  "action" => "update",
                  "data" => [
                      "fullName" => $miquiz->name
                  ]
            ]
        ];
        miquiz::api_post("api/tasks", array("data" => $task));
    }

    function get_module_id() {
        global $CFG;

        $resp = miquiz::api_get("api/modules");
        foreach($resp as $cat){
            if($cat["name"] == $CFG->miquiz_categorygroup)
                return (int)$cat['id'];
        }
        return -1;
    }

    static function sync_users($miquiz){
        global $DB, $CFG;

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

        //create users not existing in mi-quiz
        $miquiz_user = miquiz::api_get("api/users");
        foreach($enrolled as $a_user){
            $found = False;
            foreach($miquiz_user as $a_miquiz_user){
                if($a_miquiz_user["externalLogin"] == $a_user->username &&
                        $a_miquiz_user["externalProvider"] == $CFG->miquiz_loginprovider) {
                    $found = True;
                    break;
                }
            }
            if(!$found){
                try {
                    $resp = miquiz::api_post("api/users", array("login" => $CFG->miquiz_loginprovider.'_'.$a_user->username,
                                                            "role" => "standard",
                                                            "externalProvider" => $CFG->miquiz_loginprovider,
                                                            "externalLogin" => $a_user->username));
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        //create non existing user links
        foreach($enrolled as $a_user){
            $found = False;
            foreach($activity_users as $b_user){
                if($a_user->id == $b_user->userid){
                    $found = True;
                    break;
                }
            }
            if(!$found){
                $added_user = array(
                    'quizid' => $miquiz->id,
                    'userid' => $a_user->id,
                    'timecreated' => time()
                );
                $DB->insert_record("miquiz_users", $added_user);
            }
        }

        //detete not any longer existing user links
        foreach($activity_users as $a_user){
            $found = False;
            foreach($enrolled as $b_user){
                if($a_user->id == $b_user->id)
                    $found = True;
                    break;
            }
            if(!$found){
                $DB->delete_records("miquiz_users", array("quizid" => $miquiz->id, "userid" => $a_user->id));
            }
        }

        // send patch to miquiz to update user links
        $user_patch = array();
        foreach($enrolled as $a_user){
            $a_user_id = miquiz::get_user_id($a_user->username, $miquiz_user);
            $user_patch[] = ["type" => "users", "id" => (string)$a_user_id];
        }
        $resp = miquiz::api_post("api/categories/" . $miquiz->miquizcategoryid . "/relationships/players",
                      array("data" => $user_patch));
        return $enrolled;
    }

    static function get_user_id($username, $user_obj=null) {
        global $CFG;

        if(is_null($user_obj))
            $user_obj = miquiz::api_get("api/users");
        foreach($user_obj as $a_miquiz_user) {
            if($a_miquiz_user["externalLogin"] == $username &&
                    $a_miquiz_user["externalProvider"] == $CFG->miquiz_loginprovider)
                return $a_miquiz_user["id"];
        }
        return -1;
    }

    static function get_username($id, $user_obj=null){
        if(is_null($user_obj))
            $user_obj = miquiz::api_get("api/users");

        foreach($user_obj as $a_miquiz_user) {
            if($a_miquiz_user["id"] == $id)
                return $a_miquiz_user["externalLogin"];
        }
        return "";
    }
}
