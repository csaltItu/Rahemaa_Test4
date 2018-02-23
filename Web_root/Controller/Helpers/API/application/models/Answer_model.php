<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Answer_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    //----------SELECT------------------
    public function getAnswer($question_id)
    {
        $query = $this->db
            ->from('question_answer')
            ->where('question_id', $question_id)
            ->get();
        return $query->row_array();

    }

    public function insertAnswer($answer_data)
    {
        if($this->db->insert('question_answer', $answer_data))
        {
            return true;
        }
        return false;
    }
}