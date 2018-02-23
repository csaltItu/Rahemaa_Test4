<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Question extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->library('form_validation');
        $this->load->model('Question_model');
        $this->load->model('Answer_model');
    }


    //-----------------------------------IVR API-----------------------------------

    //-------------------QUESTION-----------------

    //Create new Question - by User
    public function setQuestionUser()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["call_id"]) && isset($_GET["user_id"]) )
            {
                $question_data = array(
                    'call_id' => $this->input->get('call_id'),
                    'user_id' => $this->input->get('user_id'),
                    'flag' => 1,
                    'is_recorded' => 0
                );

                $this->form_validation->set_data($question_data);
                $this->form_validation->set_rules($this->Question_model->setQuestion());

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //insert new question
                if (!(isset($_GET["question_id"])))
                {
                    $question_id = $this->Question_model->insertQuestion($question_data);

                    //If new question inserted successfully
                    if($question_id)
                    {
                        echo json_encode(array('result' => array('error' => false, 'question_id'=> $question_id)));
                        return;
                    }
                    echo json_encode(array('result' => array('error' => true)));
                    return;

                }


                //update question - is_recorded
                else if(isset($_GET["question_id"]))
                {
                    $question_id = $this->input->get('question_id');

                    //If question updated successfully
                    if($this->Question_model->updateQuestion($question_id))
                    {
                        echo json_encode(array('result' => array('error' => false, 'message' => 'updated success')));
                        return;
                    }
                    echo json_encode(array('result' => array('error' => true)));
                    return;
                }

            }

            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
                return;
            }
        }
    }

    //Create new Question - by System
    public function setQuestionSystem()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            $question_data = array(
                'is_recorded' => 1,
                'flag' => 0
            );
            $question_id = $this->Question_model->insertQuestion($question_data);

            //If new question inserted successfully
            if($question_id)
            {
                echo json_encode(array('result' => array('error' => false, 'question_id'=> $question_id)));
                return;
            }
            echo json_encode(array('result' => array('error' => true)));
            return;

        }
    }

    //Create new Question - by System
    public function setUserNameDummy()
    {
        echo json_encode(array('result' => array('error' => false)));
        return;
    }

    //Getting all questions/answers of a user in ascending order
    public function getUserQuestions()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["user_id"]))
            {
                $user_id = $this->input->get('user_id');
                $user_question_data = array('user_id' => $user_id);

                $this->form_validation->set_data($user_question_data );
                $this->form_validation->set_rules('user_id', 'USER ID', 'required');

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //Getting all user/questions in ascending order
                $user_questions = $this->Question_model->getUserQuestions($user_id);

                if($user_questions)
                {
                    //Getting answer of each question
                    foreach ($user_questions as $key => $user_question)
                    {
                        //getting answer of each question
                        if ($user_question['answer_check'] == 1)
                        {
                            $answer_question = $this->Answer_model->getAnswer($user_question['question_id']);
                            if($answer_question)
                            {
                                $user_questions[$key]['answer_id'] = $answer_question['answer_id'];
                            }
                            else
                            {
                                $user_questions[$key]['answer_id'] = null;
                            }

                        }
                        else
                        {
                            $user_questions[$key]['answer_id'] = null;
                        }
                        unset($user_questions[$key]['answer_check']);
                    }

                    echo json_encode(array('result' => array('error' => false, 'user_questions' => $user_questions)));
                    return;
                }
                echo json_encode(array('result' => array('error' => true, 'message' => 'DB Error')));
                return;
            }
            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
            }
        }
    }

    //Getting all questions - in order of likes
    public function getAllQuestions()
    {
        if (isset($_GET["user_id"]))
        {
            $user_id = $this->input->get('user_id');
            $user_question_data = array('user_id' => $user_id);

            $this->form_validation->set_data($user_question_data);
            $this->form_validation->set_rules('user_id', 'USER ID', 'required');

            //On Wrong Validation
            if ($this->form_validation->run() == FALSE)
            {
                echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                return;
            }

            $questions_likes = $this->Question_model->getQuestionWithLikes();
            $questions_no_likes = $this->Question_model->getQuestionWithNoLikes();


           if($questions_likes && $questions_no_likes)
           {
               $questions = array_merge($questions_likes,$questions_no_likes);
           }
           elseif ($questions_likes && !($questions_no_likes))
           {
               $questions = $questions_likes;
           }
           elseif (!($questions_likes) && $questions_no_likes)
           {
               $questions = $questions_no_likes;
           }
           else{
               echo json_encode(array('result' => array('error' => true, 'message' => 'DB Error')));
               return;
           }

            foreach ($questions as $key => $question)
            {
                //Removing total_Likes
                if(isset($question['total_likes']))
                {
                    unset($questions[$key]['total_likes']);
                }

                //getting answer of question
                $answer_question = $this->Answer_model->getAnswer($question['question_id']);
                if($answer_question)
                {
                    $questions[$key]['answer_id'] = $answer_question['answer_id'];

                }
                else
                {
                    $questions[$key]['answer_id'] = null;
                }

                if ($user_id == "142566") {

                    $questions[$key]['answer_id'] = 18;
                   
                } 

                //Checking whether user has listened to this question before
                if($this->Question_model->checkUserQuestion($user_id, $question['question_id']))
                {
                    $questions[$key]['listen_before'] = true;
                }
                else
                {
                    $questions[$key]['listen_before'] = false;
                }
                //Checking whether user recorded any response against question
                $response = $this->Question_model->checkUserResponse($user_id, $question['question_id']);
                if($response)
                {
                    $response = $response['response'];
                    if($response == "like")
                    {
                        $questions[$key]['like'] = true;
                        $questions[$key]['dislike'] = false;
                        $questions[$key]['report'] = false;
                        $questions[$key]['pref'] = true;
                    }
                    elseif($response == "dislike")
                    {
                        $questions[$key]['like'] = false;
                        $questions[$key]['dislike'] = true;
                        $questions[$key]['report'] = false;
                        $questions[$key]['pref'] = true;
                    }
                    elseif($response == "report")
                    {
                        $questions[$key]['like'] = false;
                        $questions[$key]['dislike'] = false;
                        $questions[$key]['report'] = true;
                        $questions[$key]['pref'] = true;
                    }
                }
                else
                {
                    $questions[$key]['like'] = false;
                    $questions[$key]['dislike'] = false;
                    $questions[$key]['report'] = false;
                    $questions[$key]['pref'] = false;
                }

            }
            echo json_encode(array('result' => array('error' => false, 'questions' => $questions)));
            return;

        }
        else
        {
            echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
        }

    }

    //Getting all questions - in order of likes
    public function getQuestions()
    {
        if (isset($_GET["user_id"]) && isset($_GET["action_id"]) && isset($_GET["attempted"]) && isset($_GET["oq"] ) ) {

            $user_id   = $this->input->get('user_id');
            $action_id = $this->input->get('action_id');
            $attempted = $this->input->get('attempted');
            $oq        = $this->input->get('oq');

            $questions = $this->Question_model->getQuestions($user_id, $action_id, $attempted, $oq);

            //var_dump($questions);

            foreach ($questions as $key => $question) {

                $questions[$key]['answer_id'] = null;
                
                if($answer_question = $this->Answer_model->getAnswer($question['question_id'])){
                    if ($answer_question['isRecorded'] == 1) {
                        $questions[$key]['answer_id'] = $answer_question['answer_id'];
                    }
                }

                if ($user_id == "142566") $questions[$key]['answer_id'] = 18;

                $questions[$key]['listen_before'] = false;
                if($this->Question_model->checkUserQuestion($user_id, $question['question_id']))
                    $questions[$key]['listen_before'] = true;
                
                $questions[$key]['like']    = false;
                $questions[$key]['dislike'] = false;
                $questions[$key]['report']  = false;
                $questions[$key]['pref']    = false;
                
                if($response = $this->Question_model->checkUserResponse($user_id, $question['question_id'])) {
                    $questions[$key]['pref'] = true;
                    $questions[$key][$response['response']] = false;
                }
            }
            echo json_encode(array('result' => array('error' => false, 'length' => sizeof($questions), 'questions' => $questions)));
            return;
        }
        else echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
    }

    //-------------------FEEDBACK-----------------

    //Set Question Feedback
    public function setQuestionFeedback()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["call_id"]) && isset($_GET["user_id"])  && isset($_GET["question_id"]) )
            {
                $question_feedback_data = array(
                    'call_id' => $this->input->get('call_id'),
                    'user_id' => $this->input->get('user_id'),
                    'question_id' => $this->input->get('question_id'),
                    'is_recorded' => 0
                );

                $this->form_validation->set_data($question_feedback_data);
                $this->form_validation->set_rules($this->Question_model->setQuestionFeedback());

                //Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //insert new question feedback
                if (!(isset($_GET["q_feedback_id"])))
                {
                    $q_feedback_id = $this->Question_model->insertQuestionFeedback($question_feedback_data);

                    //On successful insert
                    if($q_feedback_id)
                    {
                        echo json_encode(array('result' => array('error' => false, 'q_feedback_id'=> $q_feedback_id)));
                        return;
                    }

                    echo json_encode(array('result' => array('error' => true)));
                    return;

                }


                //update question_feedback  - is_recorded
                else if(isset($_GET["q_feedback_id"]))
                {
                    $q_feedback_id = $this->input->get('q_feedback_id');
                    if($this->Question_model->updateQuestionFeedback($q_feedback_id))
                    {
                        echo json_encode(array('result' => array('error' => false, 'message' => 'update success')));
                        return;
                    }
                    echo json_encode(array('result' => array('error' => true)));
                    return;
                }
            }

            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
            }
        }
    }

    //Getting the comments in descending order
    public function getQuestionFeedback()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["question_id"]))
            {
                $question_id = $this->input->get('question_id');
                $call_data = array('question_id' => $question_id);
                $this->form_validation->set_data($call_data);
                $this->form_validation->set_rules('question_id', 'QUESTION ID', 'required');

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //Getting feedback in descending order
                $question_feedbacks = $this->Question_model->getQuestionFeedbacks($question_id);
                if($question_feedbacks)
                {
                    echo json_encode(array('result' => array('error' => false, 'question_feedback' => $question_feedbacks)));
                   // $result = json_decode($result, true);
                   // print_r($result['result']['question_feedback'][0]);
                    return;
                }
                
                echo json_encode(array('result' => array('error' => true, 'message' => 'DB Error')));
                return;
            }
            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
            }
        }
    }


    //---------------------FORWARD---------------
    //Forward a question
    public function forwardQuestion()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["call_id"]) && isset($_GET["user_id"]) && isset($_GET["dest"]) && isset($_GET["file_id"]))
            {
                $forward_data = array(
                    'call_id' => $this->input->get('call_id'),
                    'user_id' => $this->input->get('user_id'),
                    'destination' => $this->input->get('dest'),
                    'file_id' => $this->input->get('file_id')
                );

                $this->form_validation->set_data($forward_data);
                $this->form_validation->set_rules($this->Question_model->setForward());

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //Insert Forward Data
                $forward_id= $this->Question_model->insertForward($forward_data);
                if($forward_id)
                {
                    echo json_encode(array('result' => array('error' => false, 'forward_id' => $forward_id)));
                    return;
                }
                echo json_encode(array('result' => array('error' => true)));
                return;
            }

            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
                return;
            }
        }
    }


    //---------------------RESPONSE---------------
    //Recording response of a question
    public function setResponseQuestion()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["call_id"]) && isset($_GET["user_id"]) && isset($_GET["question_id"]) && isset($_GET["response"]))
            {
                $response_data = array(
                    'call_id' => $this->input->get('call_id'),
                    'user_id' => $this->input->get('user_id'),
                    'question_id' => $this->input->get('question_id'),
                    'response' => $this->input->get('response')
                );

                $this->form_validation->set_data($response_data);
                $this->form_validation->set_rules($this->Question_model->setResponse());

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                if($response_data['response'] == 3)
                {
                    $response_data['response'] = "like";
                }
                elseif($response_data['response'] == 4)
                {
                    $response_data['response'] = "dislike";
                }
                elseif($response_data['response'] == 5)
                {
                    $response_data['response'] = "report";
                }
                else
                {
                    $response_data['response'] = "invalid";
                }
                //Insert Response Data
                $response_id= $this->Question_model->insertResponse($response_data);
                if($response_id)
                {
                    echo json_encode(array('result' => array('error' => false)));
                    return;
                }
                echo json_encode(array('result' => array('error' => true)));
                return;


            }

            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
                return;
            }
        }
    }


    //-------------------------------ANDROID APPLICATION API--------------------------------------

    //Getting total count of questions - unanswered/answered one
    public function getTotalCountQuestions()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET')
        {
            $total_questions = $this->Question_model->getTotalQuestionCount();
            $unanswered_questions = $this->Question_model->getUnansweredCount();
            $answered_questions = $this->Question_model->getAnsweredCount();

            echo json_encode(array('status' => "success", 'total_questions' => $total_questions, 'unanswered_questions' => $unanswered_questions,
                'answered_questions' => $answered_questions));
            return;
        }
    }

    //Getting all unanswered questions
    public function getUnansweredQuestions()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET')
        {
            $unanswered_questions = $this->Question_model->getUnansweredQuestions();
            echo json_encode(array('status' => "success", 'unanswered_questions' => $unanswered_questions));
            return;
        }
    }

    //Saving app token
    public function saveToken()
    {
        if($this->input->server('REQUEST_METHOD') == 'POST')
        {
            $data = json_decode(file_get_contents("php://input"));
            $token = $data->token;

            $token_data = array('token' => $token);


            //Inserting token
            if ($this->Question_model->insertToken($token_data))
            {
                echo json_encode(array('status' => "success"));
                return;
            }
            echo json_encode(array('status' => "error"));
            return;
        }
    }

    public function getDeliveryParams(){

        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["id"]))
            {
                $id = $this->input->get('id');

                //Getting user question
                $user_question = $this->Question_model->getDeliveryParams($id);
                if($user_question)
                {
                    $ans = $this->Answer_model->getAnswer($id);
                    $user_question->ans_id = $ans["answer_id"];
                    echo json_encode(array('result' => array('error' => false, 'qa' => $user_question)));
                    return;
                }

                echo json_encode(array('result' => array('error' => true, 'message' => 'DB Error')));
                return;

            }
            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
            }
        }

    }

    //Sample notification for Question
    public function notificationQuestion()
    {

        $unanswered_questions_count = $this->Question_model->getUnansweredCount();
        $tokens = $this->Question_model->getTokens();

        foreach ($tokens as $token)
        {
            //$token = "dzD9G0LpT7o:APA91bE5EGEGOTs_gB8uWvalTy05LY2B6CoVddeEg2qZiiZDuzEwJ-5U_8Oj-zPSZe44dXlY5m1aZ3ZCAW7jM8vZ5V2vM1vMLQgF5l9S8Q68ibK0lL4MhXQxuVjgb9A7ZRKm7PdUMlA3";
            $url = 'https://fcm.googleapis.com/fcm/send';
            $headers = array('Content-Type' => 'application/json', 'authorization' => 'key= AIzaSyDJnLqtnXqSI5vF6N8IJCRgHLF58pQ7xYI');
            $fields = array (
                'data' => array(
                    "unanswered_questions_count" => $unanswered_questions_count
                ),
                'to' => $token['token']
            );

            $response = (Requests::post($url, $headers, json_encode($fields)));
        }
    }

}