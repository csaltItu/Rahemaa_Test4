<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cases_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    //-------SELECT--------------

    //Getting case more info
   /* public function getCaseMoreInfo($case_id)
    {
        $query = $this->db
            ->select('more_info_id')
            ->where('case_id', $case_id)
            ->get('case_more_info');

        return $query->row_array();
    }*/

    //Getting question case
    public function getCaseQuestion($case_id)
    {
        $query = $this->db
            ->select('case_question_id, correct_answer')
            ->where('case_id', $case_id)
            ->get('case_question');

        return $query->row_array();
    }


    //Getting questions of all case
    public function getCasesQuestions()
    {
        
        $this->db->order_by('case_question_id', 'RANDOM');
        $query = $this->db->get('case_question');
        return $query->result_array();
    }


    /*//Getting all options of a question
    public function getCaseOptions($case_question_id)
    {
        $query = $this->db
            ->select('option_id')
            ->where('case_question_id', $case_question_id)
            ->get('case_question_option');

        return $query->result_array();
    }*/


    //-------------------INSERT---------------------

    //Inserting new case
    public function insertCase($case_data)
    {
        if($this->db->insert('cases', $case_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    //Inserting case more info
  /*  public function insertCaseInfo($case_info_data)
    {
        if($this->db->insert('case_more_info', $case_info_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }*/

    //Inserting case answer
    public function insertCaseQuestion($case_question_data)
    {
        if($this->db->insert('case_question', $case_question_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    //Inserting case option
    /* public function insertCaseOption($case_option_data)
     {
         if($this->db->insert('case_question_option', $case_option_data))
         {
             $insert_id = $this->db->insert_id();
             return $insert_id;
         }
     }*/


    //Inserting case answer
    public function insertCaseAnswer($case_answer_data)
    {
        if($this->db->insert('case_answer', $case_answer_data))
        {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    //--------------------------UPDATE--------------------------
  /*  public function updateCaseAnswer($case_id, $correct_answer)
    {
        $this->db->query('UPDATE case_question SET correct_answer='.$correct_answer.' WHERE case_id='.$case_id);
        return ($this->db->affected_rows() > 0);

    }*/

    //-----------------VALIDATION RULES--------------------
    public function setCaseMoreInfo()
    {
        $config = array(
            array(
                'field' => 'case_id',
                'label' => 'CASE ID',
                'rules' => 'required'
            )
        );

        return $config;
    }

    public function setCaseCorrectAnswer()
    {
        $config = array(
            array(
                'field' => 'case_id',
                'label' => 'CASE ID',
                'rules' => 'required'
            ),array(
                'field' => 'correct_answer',
                'label' => 'Correct Answer',
                'rules' => 'required'
            )
        );

        return $config;
    }

    public function setCaseOption()
    {
        $config = array(
            array(
                'field' => 'case_question_id',
                'label' => 'CASE QUESTION ID',
                'rules' => 'required'
            )
        );

        return $config;
    }

    public function setCaseAnswer()
    {
        $config = array(
            array(
                'field' => 'case_id',
                'label' => 'CASE ID',
                'rules' => 'required'
            ),
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
                'field' => 'user_answer',
                'label' => 'USER ANSWER',
                'rules' => 'required'
            )
        );

        return $config;
    }

}