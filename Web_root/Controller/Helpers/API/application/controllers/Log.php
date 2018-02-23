<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->library('form_validation');
        $this->load->model('Log_model');
    }


    //insert a new log
    public function setLog()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["call_id"]) && isset($_GET["user_id"]) && isset($_GET["action_id"]) && isset($_GET["file_id"]))
            {
                $log_data = array(
                    'call_id' => $this->input->get('call_id'),
                    'user_id' => $this->input->get('user_id'),
                    'action_id' => $this->input->get('action_id'),
                    'file_id' => $this->input->get('file_id')

                );

                $this->form_validation->set_data($log_data);
                $this->form_validation->set_rules($this->Log_model->setLog());

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //insert new log

                $log= $this->Log_model->insertLog($log_data);

                //If new log inserted successfully
                if($log)
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

}