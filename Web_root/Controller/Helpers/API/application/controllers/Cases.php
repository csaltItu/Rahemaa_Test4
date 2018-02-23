<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cases extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->library('form_validation');
        $this->load->model('Cases_model');
    }


    //--------------CASE--------------------------

    //Inserting new case
    public function setCase()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            $case_data = array(
                'timestamp' => date('Y-m-d H:i:d')
            );

            $case = $this->Cases_model->insertCase($case_data);

            //if case inserted successfully
            if($case)
            {
                echo json_encode(array('result' => array('error' => false, 'case_id' => $case)));
                return;

            }
            echo json_encode(array('result' => array('error' => true, 'message' => 'DB Error')));
            return;
        }

    }

    //Getting a case
   /* public function getCase()
    {
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            if (isset($_GET["call_id"]))
            {
                $case_data = array(
                    'call_id' => $this->input->get('call_id')
                );

                $this->form_validation->set_data($case_data);
                $this->form_validation->set_rules('call_id', 'CALL ID', 'required');

                //On Wrong Validation
                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                //Returning a case if set
                if (isset($_GET["case_id"]))
                {
                    $case_id = $this->input->get('case_id');
                    echo json_encode(array('result' => array('error' => false, 'case_id'=> $case_id)));
                    return;
                }

                //If case id not set
                else if(!(isset($_GET["case_id"])))
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
    }*/


    //--------------CASE QUESTION--------------------------

    //Inserting new case question
    public function setCaseQuestion()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_id"]) && isset($_GET["correct_answer"]) && isset($_GET["options"]))
            {
                //Getting file id, case_id
                $case_question_data = array(
                    'case_id'=> $this->input->get('case_id'),
                    'correct_answer'=> $this->input->get('correct_answer'),
                    'options'=> $this->input->get('options')
                );


                $this->form_validation->set_data($case_question_data);
                $this->form_validation->set_rules('case_id', 'CASE ID', 'required');
                $this->form_validation->set_rules('correct_answer', 'Correct Answer', 'required');
                $this->form_validation->set_rules('options', 'Options', 'required');

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                $case_question = $this->Cases_model->insertCaseQuestion($case_question_data);

                //if case question inserted successfully - return case_question id
                if($case_question)
                {
                    echo json_encode(array('result' => array('error' => false, 'case_question' => $case_question)));
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

    //Getting case Questions for all cases
    public function getCasesQuestions()
    {
        $case_questions = $this->Cases_model->getCasesQuestions();

        //if case question inserted successfully - return case_question id
        if($case_questions)
        {
            echo json_encode(array('result' => array('error' => false, 'case_questions' => $case_questions)));
            return;

        }
        echo json_encode(array('result' => array('error' => true, 'message' => 'DB Error')));
        return;
    }

    /*//Getting case question
    public function getCaseQuestion()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_id"]))
            {
                //Getting file id, case_id
                $case_question_data = array(
                    'case_id'=> $this->input->get('case_id')
                );


                $this->form_validation->set_data($case_question_data);
                $this->form_validation->set_rules('case_id', 'CASE ID', 'required');

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                $case_question = $this->Cases_model->getCaseQuestion($case_question_data['case_id']);


                //if case question inserted successfully - return case_question id
                if($case_question)
                {
                    echo json_encode(array('result' => array('error' => false, 'case_question' => $case_question)));
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

    }*/



    //--------------CASE MORE INFO--------------------------

   /* //Inserting new case more info
    public function setCaseMoreInfo()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_id"]))
            {
                //Getting file id, case_id
                $case_info_data = array(
                    'case_id'=> $this->input->get('case_id')
                );

                $this->form_validation->set_data($case_info_data);
                $this->form_validation->set_rules($this->Cases_model->setCaseMoreInfo());

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                $more_info = $this->Cases_model->insertCaseInfo($case_info_data);

                //if case inserted successfully
                if($more_info)
                {
                    echo json_encode(array('result' => array('error' => false )));
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

    //Getting more info of a case
    public function getCaseMoreInfo()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_id"]))
            {
                $case_id = $this->input->get('case_id');
                $case_array=array('case_id'=> $case_id);
                $this->form_validation->set_data($case_array);
                $this->form_validation->set_rules('case_id','CASE ID','required');

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }
                $more_info = $this->Cases_model->getCaseMoreInfo($case_id);

                //Getting file id of more case info
                if($more_info)
                {
                    echo json_encode(array('result' => array('error' => false, 'more_info_id' =>$more_info['more_info_id'] )));
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

    }*/


    //-------------CASE OPTION-----------------------------

/*    //Inserting option of a case question
    public function setCaseOption()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_question_id"]))
            {
                //Getting file id, case_id
                $case_option_data = array(
                    'case_question_id'=> $this->input->get('case_question_id')
                );

                $this->form_validation->set_data($case_option_data);
                $this->form_validation->set_rules($this->Cases_model->setCaseOption());

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                $case_option = $this->Cases_model->insertCaseOption($case_option_data);

                //if case option inserted successfully
                if($case_option)
                {
                    echo json_encode(array('result' => array('error' => false, 'case_option'=>$case_option)));
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

    //Getting all options of a case
    public function getCaseOptions()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_question_id"]))
            {
                $case_question_id = $this->input->get('case_question_id');
                $case_array=array('case_question_id'=> $case_question_id);
                $this->form_validation->set_data($case_array);
                $this->form_validation->set_rules('case_question_id','CASE QUESTION ID','required');

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }
                $options = $this->Cases_model->getCaseOptions($case_question_id);

                //All options of a case
                if($options)
                {
                    echo json_encode(array('result' => array('error' => false, 'options' => $options)));
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

    }*/

    //-------------CASE ANSWER----------------------------

    //Inserting correct answer of a case_question
    public function setCaseCorrectAnswer()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_id"]) && isset($_GET["correct_answer"]))
            {
                //Getting file id, case_id
                $case_answer_data = array(
                    'case_id'=> $this->input->get('case_id'),
                    'correct_answer'=> $this->input->get('correct_answer')
                );


                $this->form_validation->set_data($case_answer_data);
                $this->form_validation->set_rules($this->Cases_model->setCaseCorrectAnswer());

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }


                //if case question inserted successfully - return case_question id
                if($this->Cases_model->updateCaseAnswer($case_answer_data['case_id'],$case_answer_data['correct_answer']))
                {
                    echo json_encode(array('result' => array('error' => false)));
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

    //Inserting user answer of a case
    public function setCaseAnswerUser()
    {
        if($this->input->server('REQUEST_METHOD')== "GET")
        {
            if (isset($_GET["case_id"]) && isset($_GET["user_id"]) && isset($_GET["call_id"]) && isset($_GET["user_answer"]))
            {
                //Getting file id, case_id
                $case_answer_data = array(
                    'case_id'=> $this->input->get('case_id'),
                    'user_id'=> $this->input->get('user_id'),
                    'call_id'=> $this->input->get('call_id'),
                    'user_answer'=> $this->input->get('user_answer'),
                );

                $this->form_validation->set_data($case_answer_data);
                $this->form_validation->set_rules($this->Cases_model->setCaseAnswer());

                if($this->form_validation->run() == FALSE)
                {
                    echo json_encode(array('result' => array('error' => true, 'message' => validation_errors())));
                    return;
                }

                $case_answer = $this->Cases_model->insertCaseAnswer($case_answer_data);

                //if case option inserted successfully
                if($case_answer)
                {
                    echo json_encode(array('result' => array('error' => false )));
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
}