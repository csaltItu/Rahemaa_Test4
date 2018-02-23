<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Answer extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->library('form_validation');
        $this->load->model('Answer_model');
        $this->load->model('Question_model');
    }

    //Getting answer of a question
    public function getAnswer()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["question_id"]) && isset($_GET["user_id"]))
            {
                $question_id = $this->input->get('question_id');
                $user_id = $this->input->get('user_id');

                $question_data = array('question_id' => $question_id, 'user_id' => $user_id);
                $question = array();

                $this->form_validation->set_data($question_data);
                $this->form_validation->set_rules('question_id', 'QUESTION ID', 'required');
                $this->form_validation->set_rules('user_id', 'USER ID', 'required');

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //Getting answer of question
                $question_answer = $this->Answer_model->getAnswer($question_id);

                $question['question_id'] = $question_id;

                //Getting answer id
                if($question_answer)
                {
                    $question['answer_id'] = $question_answer['answer_id'];
                }
                else
                {
                    $question['answer_id'] = null;
                }
                //Checking whether user has listened to this question before
                if($this->Question_model->checkUserQuestion($user_id, $question_id))
                {
                    $question['listen_before'] = true;
                }
                else
                {
                    $question['listen_before'] = false;
                }
                //Checking whether user recorded any response against question
                $response = $this->Question_model->checkUserResponse($user_id, $question_id);
                if($response)
                {
                    $response = $response['response'];
                    if($response == "like")
                    {
                        $question['like'] = true;
                        $question['dislike'] = false;
                        $question['report'] = false;
                    }
                    elseif($response == "dislike")
                    {
                        $question['like'] = false;
                        $question['dislike'] = true;
                        $question['report'] = false;
                    }
                    elseif($response == "report")
                    {
                        $question['like'] = false;
                        $question['dislike'] = false;
                        $question['report'] = true;
                    }
                }
                else
                {
                    $question['like'] = false;
                    $question['dislike'] = false;
                    $question['report'] = false;
                }

                echo json_encode(array('result' => array('error' => false, 'qa' => $question)));
                return;

            }
            else
            {
                echo json_encode(array('result' => array('error' => true, 'message' => 'wrong params')));
            }
        }
    }

    //Inserting answer by system
    public function setAnswerSystem()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["question_id"]))
            {
                //Getting question
                $answer_data = array(
                    'question_id'=> $this->input->get('question_id'),
                    'flag' => 0
                );

                $this->form_validation->set_data($answer_data);
                $this->form_validation->set_rules('question_id', 'QUESTION ID', 'required');

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                $answer_question = $this->Answer_model->insertAnswer($answer_data);

                //if case option inserted successfully
                if($answer_question)
                {
                    //Updating answer_check of question
                    $this->Question_model->updateIsAnswered($answer_data['question_id']);
                    echo json_encode(array('result' => array('error' => false, 'answer_question'=> $answer_question)));
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

    //Storing answer of a question - text
    public function saveAnswerText()
    {
        if($this->input->server('REQUEST_METHOD') == 'POST')
        {
            $data = json_decode(file_get_contents("php://input"));
            $question_id = $data->question_id;
            $question_answer = $data->question_answer;

            $answer_data = array(
                'question_id' => $question_id,
                'answer_text' => $question_answer,
                'flag' => 1

            );


            //Inserting answer of a question
            if ($this->Answer_model->insertAnswer($answer_data))
            {
                //Updating answer_check of question
                $this->Question_model->updateIsAnswered($question_id);
                echo json_encode(array('status' => "success"));
                return;
            }
            echo json_encode(array('status' => "error"));
            return;

        }

    }

    //Storing answer of a question - audio
    public function saveAnswerAudio()
    {
        if($this->input->server('REQUEST_METHOD') == 'POST')
        {
            $data = json_decode(file_get_contents("php://input"));
            $question_id = $data->question_id;
            $answer_audio = $data->answer_audio;

            if (file_put_contents("D:/xampp/htdocs/wa/ReMT4/Recordings/A" . $question_id . ".wav", base64_decode($answer_audio)))
            {
                $answer_data = array(
                    'question_id' => $question_id,
                    'flag' => 1
                );

                if ($this->Answer_model->insertAnswer($answer_data))
                {
                    //Updating answer_check of question
                    $this->Question_model->updateIsAnswered($question_id);
                    echo json_encode(array('status' => "success"));
                    return;
                }
            }

        }
    }

}