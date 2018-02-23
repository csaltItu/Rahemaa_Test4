<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MainFeedback extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->library('form_validation');
        $this->load->model('Feedback_model');
    }

    //Insert main feedback by user
    public function setMainFeedback()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["call_id"]) && isset($_GET["user_id"]) )
            {
                $feedback_data = array(
                    'call_id' => $this->input->get('call_id'),
                    'user_id' => $this->input->get('user_id'),
                    'is_recorded' => 0
                );

                $this->form_validation->set_data($feedback_data);
                $this->form_validation->set_rules($this->Feedback_model->setFeedback());

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //insert new feedback
                if (!(isset($_GET["feedback_id"])))
                {
                    $feedback_id = $this->Feedback_model->insertFeedback($feedback_data);

                    //If new feedback inserted successfully
                    if($feedback_id)
                    {
                        echo json_encode(array('result' => array('error' => false, 'feedback_id'=> $feedback_id)));
                        return;
                    }
                    echo json_encode(array('result' => array('error' => true)));
                    return;

                }


                //update feedback - is_recorded
                else if(isset($_GET["feedback_id"]))
                {
                    $feedback_id = $this->input->get('feedback_id');

                    //If feedback updated successfully
                    if($this->Feedback_model->updateFeedback($feedback_id))
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

}