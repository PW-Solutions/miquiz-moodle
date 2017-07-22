<?php

defined('MOODLE_INTERNAL') || die();

class miquiz {
    function get($endpoint) {
        $url = get_string('modulebaseurl', 'miquiz') . "/" . $endpoint;
        $accesstoken = get_string('miquizapikey', 'miquiz');
        $headr = array();
        $headr[] = 'Accept: application/json';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: Bearer '.$accesstoken;

        $crl = curl_init();
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_HTTPGET, true);
        $reply = curl_exec($crl);

        if ($reply === false) {
            throw new Exception('Curl error: ' . curl_error($crl));
        //    print_r('Curl error: ' . curl_error($crl));
        }
        curl_close($crl);

        return json_decode($reply, true);
    }

    function post($endpoint, $data=array()) {
        $url = get_string('modulebaseurl', 'miquiz') . "/" . $endpoint;
        $accesstoken = get_string('miquizapikey', 'miquiz');
        $headr = array();
        $headr[] = 'Accept: application/json';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: Bearer '.$accesstoken;
        $data_string = json_encode($data);

        $crl = curl_init();
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($crl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        $reply = curl_exec($crl);

        if ($reply === false) {
            throw new Exception('Curl error: ' . curl_error($crl));
        //    print_r('Curl error: ' . curl_error($crl));
        }
        curl_close($crl);

        return json_decode($reply, true);
    }

    function create($miquiz){
        global $DB;

        $moduleid = miquiz::get_module_id();


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
        $resp = miquiz::post("api/categories", array("parent" => $moduleid,
                                                     "scoreStrategy" => $scoremode,
                                                     "fullName" => $miquiz->name,
                                                     "name" => $miquiz->short_name));
        $catid = (int)$resp['id'];

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

        foreach($questions as $question){
            $possibilities = $DB->get_records('question_answers', array('question' => $question->id));
            $json_possibilities = [];
            foreach($possibilities as $possibility)
                $json_possibilities[] = ["description" => $possibility->answer, "isCorrect" => ((float)$possibility->fraction) > 0];

            miquiz::post("api/questions", ["description" => ["text" => $question->questiontext],
                                           "possibilities" => $json_possibilities,
                                           "comment" => ["text" => $question->generalfeedback],
                                           "timeToAnswer" => get_question_answeringtime($question->id),
                                           "categories" => [["id" => $catid]]]);
        }

        return $catid;
    }

    function update($miquiz){
        global $DB;
        /*
        assesstimestart
        assesstimefinish
        timeuntilproductive
        */
        return True; //TODO success
    }

    function delete($miquiz){
        $resp = miquiz::post("api/categories/" . $miquiz->$miquizcategoryid, array("active" => False));
        return True;
    }

    function get_module_id(){
        $resp = miquiz::get("api/modules");
        foreach($resp as $cat){
            if($cat["name"] == get_string('miquizcategorygroup', 'miquiz'))
                return (int)$cat['id'];
        }
        return -1;
    }

    function sync_feedback($miquiz){
        global $DB;

        //TODO sync feeback with moodle
    }

    function sync_users($miquiz){
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

        //create users not existing in mi-quiz
        $miquiz_user = miquiz::get("api/users");
        foreach($enrolled as $a_user){
            $found = False;
            foreach($miquiz_user as $a_miquiz_user){
                if($a_miquiz_user["externalLogin"] == $a_user->username){
                    $found = True;
                    break;
                }
            }
            if(!$found){
                $resp = miquiz::post("api/users", array("login" => $a_user->username,
                                                        "role" => "standard",
                                                        "externalProvider" => get_string('miquizloginprovider', 'miquiz'),
                                                        "externalLogin" => $a_user->username));
            }
        }

        //create non existing user links
        foreach($enrolled as $a_user){
            $found = False;
            foreach($activity_users as $b_user){
                if($a_user->id == $b_user->userid)
                    $found = True;
                    break;
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
                if($a_user->id == $b_user->userid)
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
            $a_user_id = 0;
            foreach($miquiz_user as $a_miquiz_user) {
                if($a_miquiz_user["login"] == $a_user->username){
                    $a_user_id = $a_miquiz_user["id"];
                    break;
                }
            }
            $user_patch[] = ["type" => "users", "id" => $a_user_id];
        }
        $resp = miquiz::post("/api/categories/" . $miquiz->$miquizcategoryid . "/relationships/players",
                              array("data" => $user_patch));

        return $enrolled;
    }
}
