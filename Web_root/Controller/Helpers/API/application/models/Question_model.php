<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Question_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    //--------SELECT--------------------

    //Getting all user attempted questions
    public function getQuestions($user_id, $action_id, $attempted, $oq) {

        $log_query = "SELECT DISTINCT file_id FROM `logs` WHERE user_id = $user_id AND action_id = action_id";

        $this->db->from('question');
        $this->db->select('question_id, answer_check, user_id');
        $this->db->order_by('answer_check', 'DESC');
        $this->db->where('approved', 1);

        if   ($oq)         $this->db->where('user_id', $user_id);
        if   ($attempted)  $this->db->where("question_id IN ($log_query)");
        else               $this->db->where("question_id NOT IN ($log_query)");

        $query = $this->db->get();

        return $query->result_array();
    }

    //Getting all user questions
    public function getUserQuestions($user_id) {

        $log_query =    "SELECT DISTINCT file_id FROM `logs` WHERE user_id = $user_id AND action_id = 2";

        $query     =    $this->db
                        ->from('question')
                        ->select('question_id, answer_check')
                        ->where('user_id', $user_id)
                        ->where('is_recorded',1)
                        ->where("question_id NOT IN ($log_query)")
                        ->order_by('answer_check', 'DESC')
                        ->get();

        return $query->result_array();
    }

    //Getting all user attempted questions
    public function getAllAttemptedQuestions($user_id, $action_id, $attempted) {

        $log_query = "SELECT DISTINCT file_id FROM `logs` WHERE user_id = $user_id AND action_id = action_id";

        $this->db->from('question');
        $this->db->select('question_id, answer_check');
        $this->db->order_by('answer_check', 'DESC');

        if      ($attempted == "true")  $this->db->where("question_id IN ($log_query)");
        else if ($attempted == "false") $this->db->where("question_id NOT IN ($log_query)");
        else return false;

        $query = $this->db->get();

        return $query->result_array();
    }

    //Getting delviery params of question in passed forward id
    public function getDeliveryParams($id)
    {
        $query = $this->db
            ->from('forward')
            ->select('file_id, user_id as fuid')
            ->where('forward_id', $id)
            ->get();
        return $query->row();

    }

    //Getting all questions on basis of likes
    public function getQuestionWithLikes()
    {
        $query = $this->db->query('Select a.question_id, COUNT(a.question_id) as total_likes from question_user_response a JOIN question ON question.question_id = a.question_id where question.approved = 1 AND a.response = "like" GROUP BY a.question_id ORDER BY question.answer_check DESC, total_likes DESC');
        return $query->result_array();

    }

    //Getting all questions with no likes
    public function getQuestionWithNoLikes()
    {
        $query = $this->db->query('Select question_id from question where approved = 1 AND question_id NOT IN(Select question_id from question_user_response WHERE response = "like") Order by answer_check DESC');
        return $query->result_array();

    }

    //Getting all questions with no likes
    public function getQuestionWSLikes()
    {
        $query = $this->db->query('SELECT 
                                    q.question_id, SUM(CASE WHEN qur.response = "like" THEN 1 ELSE 0 END) as likes
                                FROM
                                    question q
                                    LEFT JOIN 
                                    question_user_response qur 
                                        ON q.question_id = qur.question_id 
                                WHERE 
                                    q.approved = 1 #AND qur.response = "like" 
                                GROUP BY 
                                    q.question_id 
                                ORDER BY 
                                    q.answer_check DESC, q.question_id DESC, likes DESC');
        return $query->result_array();

    }

    //Getting all user attempted questions
    // public function getUserUnattemptedQuestions($user_id, $action_id)
    // {
    //     $query = $this->db->query("SELECT question_id, answer_check 
    //               FROM question WHERE question_id NOT IN (
    //                     SELECT DISTINCT file_id 
    //                     FROM `logs` 
    //                     WHERE user_id = $user_id AND action_id = $action_id 
    //               )"
    //             );
    //     return $query->result_array();

    // }



   /* //Getting question which user asked
    public function getQuestionUser($user_id,$call_id)
    {
        $query = $this->db->query('SELECT question_id, answer_check from question where is_recorded=1 and user_id='.$user_id.' AND question_id NOT IN(Select file_id from logs where call_id='.$call_id.' AND action_id=3) order by answer_check DESC, timestamp ASC LIMIT 1');
        return $query->row_array();
    }

    //Getting user question from logs
    public function getQuestionUserLogs($call_id, $total_user_question)
    {
        $query = $this->db->query('SELECT * FROM logs  where call_id='. $call_id.' AND action_id=3 order by timestamp DESC Limit '. $total_user_question);
        $row = $query->last_row();
        return $row;
    }*/

    /*//Getting question with most likes
    public function getQuestionMaxLikes($call_id)
    {
        $query = $this->db->query('Select a.question_id, COUNT(a.question_id) as total_likes from question_user_response a JOIN question ON question.question_id = a.question_id where question.approved = 1 AND a.response = "like" AND a.question_id NOT IN(Select file_id from logs where call_id='.$call_id.' AND action_id =1) GROUP BY a.question_id ORDER BY question.answer_check DESC, total_likes DESC Limit 1');
        return $query->row_array();
    }

    //Getting question with no like from question table
    public function getQuestionNoLike($call_id)
    {
        $query = $this->db->query('Select question_id from question where approved = 1 AND question_id NOT IN(Select file_id from logs where call_id='.$call_id .' and action_id=1) Order by answer_check DESC, timestamp ASC Limit 1');
        return $query->row_array();
    }

    //Getting question from logs
    public function getQuestionFromLogs($call_id,$total_question_count)
    {
        $query = $this->db->query('SELECT * FROM logs  where call_id='. $call_id.' AND action_id=1 order by timestamp DESC Limit '. $total_question_count);
        $row = $query->last_row();
        return $row;
    }

    //Total count of all questions of a user
    public function getTotalUserQuestion($user_id)
    {
        $this->db->from('question');
        $this->db->where('is_recorded', 1);
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results();
    }

    //Total count of all questions from logs - for a single
    public function getTotalQuestionfromLogs($call_id)
    {
        $query = $this->db->query('Select COUNT(*) from (Select DISTINCT file_id from logs where action_id=1 and call_id='.$call_id.') as total');
        return $query->row_array();
    }*/

    //Getting the comment (feedback) in descending order
    public function getQuestionFeedbacks($question_id)
    {
        $query = $this->db->query('Select q_feedback_id from question_feedback where approved=1 AND question_id='.$question_id.' order by timestamp DESC');
        return $query->result_array();
    }


    //Checking whether user has listened to question before
    public function checkUserQuestion($user_id, $question_id)
    {
        $query = $this->db
            ->from('logs')
            ->where('user_id', $user_id)
            ->where('action_id', 1)
            ->where('file_id', $question_id)
            ->get();

        if($query->num_rows() > 0)
        {
            return true;
        }
        else
            return false;

    }

    //Checking whether user has responded response against question
    public function checkUserResponse($user_id, $question_id)
    {
        $query = $this->db
            ->from('question_user_response')
            ->select('response')
            ->where('user_id', $user_id)
            ->where('question_id', $question_id)
            ->get();

        return $query->row_array();

    }




    //-----------------------------------------------------------ANDROID---------------------------------------------
    //Total count of all questions
    public function getTotalQuestionCount()
    {
        $this->db->from('question');
        $this->db->where('approved', 1);
        return $this->db->count_all_results();
    }

    //Total count of all un-answered questions
    public function getUnansweredCount()
    {
        $this->db->from('question');
        $this->db->where('answer_check', 0);
        $this->db->where('approved', 1);
        return $this->db->count_all_results();
    }

    //Total count of all answered questions
    public function getAnsweredCount()
    {
        $this->db->from('question');
        $this->db->where('answer_check', 1);
        $this->db->where('approved', 1);
        return $this->db->count_all_results();
    }

    //Getting all unanswered questions
    public function getUnansweredQuestions()
    {
        $query = $this->db
            ->from('question')
            ->where('answer_check', 0)
            ->where('approved', 1)
            ->get();

        return $query->result_array();

    }

    //Getting all the tokens
    public function getTokens()
    {
        $query = $this->db
            ->select('token')
            ->get('token');

        return $query->result_array();
    }

    //-----------------------------------------------------------ANDROID---------------------------------------------

    //-------------------INSERT---------------------
    public function insertQuestion($question_data)
    {
        if($this->db->insert('question', $question_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    public function insertQuestionFeedback($question_data)
    {
        if($this->db->insert('question_feedback', $question_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    public function insertForward($forward_data)
    {
        if($this->db->insert('forward', $forward_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    public function insertResponse($response_data)
    {
        if($this->db->insert('question_user_response', $response_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    public function insertToken($token_data)
    {
        if($this->db->insert('token', $token_data))
        {
            return true;
        }
    }

    //----------------UPDATE----------------------

    public function updateQuestion($question_id)
    {
        $this->db->query('UPDATE question SET is_recorded=1 WHERE question_id='.$question_id);
        return ($this->db->affected_rows() > 0);

    }

    public function updateQuestionFeedback($q_feedback_id)
    {
        $this->db->query('UPDATE question_feedback SET is_recorded=1 WHERE q_feedback_id='.$q_feedback_id);
        return ($this->db->affected_rows() > 0);

    }

    //Updating answer_check of a question
    public function updateIsAnswered($question_id)
    {
        if($this->db->query('UPDATE question SET answer_check = 1 WHERE question_id ='.$question_id))
        {
            return true;
        }
        return false;
    }

    //Updating approve of a question
    public function updateApprove($question_id)
    {
        if($this->db->query('UPDATE question SET approved = 0 WHERE question_id ='.$question_id))
        {

            return true;
        }
        return false;
    }


    //-----------------VALIDATION RULES--------------------
    public function setQuestion()
    {
        $config = array(
            array(
                'field' => 'user_id',
                'label' => 'USER ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'call_id',
                'label' => 'CALL ID',
                'rules' => 'required'
            )
        );

        return $config;
    }

    public function setQuestionFeedback()
    {
        $config = array(
            array(
                'field' => 'user_id',
                'label' => 'USER ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'call_id',
                'label' => 'CALL ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'question_id',
                'label' => 'QUESTION ID',
                'rules' => 'required'
            )
        );

        return $config;
    }

    public function setForward()
    {
        $config = array(
            array(
                'field' => 'user_id',
                'label' => 'USER ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'call_id',
                'label' => 'CALL ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'destination',
                'label' => 'DESTINATION NUMBER',
                'rules' => 'required'
            ),
            array(
                'field' => 'file_id',
                'label' => 'FILE ID',
                'rules' => 'required'
            )
        );

        return $config;
    }

    public function setResponse()
    {
        $config = array(
            array(
                'field' => 'user_id',
                'label' => 'USER ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'call_id',
                'label' => 'CALL ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'question_id',
                'label' => 'Question ID',
                'rules' => 'required'
            ),
            array(
                'field' => 'response',
                'label' => 'Response',
                'rules' => 'required'
            )
        );

        return $config;
    }
}