<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    //-------------------INSERT---------------------
    public function insertLog($log_data)
    {
        if($this->db->insert('logs', $log_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }


    //-----------------VALIDATION RULES--------------------
    public function setLog()
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
                'field' => 'action_id',
                'label' => 'ACTION ID',
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
}