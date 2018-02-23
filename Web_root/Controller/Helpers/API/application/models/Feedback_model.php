<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    //-------------------INSERT---------------------
    public function insertFeedback($feedback_data)
    {
        if($this->db->insert('main_feedback', $feedback_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    //----------------UPDATE----------------------

    public function updateFeedback($feedback_id)
    {
        $this->db->query('UPDATE main_feedback SET is_recorded=1 WHERE feedback_id='.$feedback_id);
        return ($this->db->affected_rows() > 0);

    }

    //-----------------VALIDATION RULES--------------------
    public function setFeedback()
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
}