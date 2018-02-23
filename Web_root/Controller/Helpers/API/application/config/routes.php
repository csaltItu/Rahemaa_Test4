<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//------------Question------------------
$route['api/add/question_user'] = 'Question/setQuestionUser'; //Setting question
$route['api/add/question_system'] = 'Question/setQuestionSystem'; //Setting question
$route['api/add/question_feedback'] = 'Question/setQuestionFeedback'; //Setting question feedback
$route['api/question'] = 'Question/getQuestionMaxLikes'; //Getting question with max. likes
$route['api/questions'] = 'Question/getAllQuestions'; //Getting all questions
$route['api/delivery_params'] = 'Question/getDeliveryParams';

$route['api/question_feedback'] = 'Question/getQuestionFeedback'; //Getting question feedback in descending order
$route['api/question_user'] = 'Question/getUserQuestion'; //Getting question which user asked
$route['api/questions_user'] = 'Question/getUserQuestions'; //Getting all questions which user asked
$route['api/questions_user_attempted'] = 'Question/getUserAttemptedQuestions'; //Getting all questions which user asked

$route['api/get_questions'] = 'Question/getQuestions'; //Getting all questions

//------------ANSWER----------------------
$route['api/add/answer_system'] = 'Answer/setAnswerSystem'; //Setting answer - system
$route['api/answer_question'] = 'Answer/getAnswer'; //getting answer

//-------------TOKEN---------------------
$route['api/token'] = 'Question/saveToken'; //getting answer

$route['api/username'] = 'Question/setUserNameDummy'; //getting answer




//--------------Case-----------------------------
$route['api/add/case'] = 'Cases/setCase'; //Setting Case
$route['api/case'] = 'Cases/getCase'; //Getting Case
$route['api/add/case_more_info'] = 'Cases/setCaseMoreInfo'; //Setting Case More Info
$route['api/case_more_info'] = 'Cases/getCaseMoreInfo'; //Getting Case More Info

$route['api/add/case_question'] = 'Cases/setCaseQuestion'; //Setting Question of a case
$route['api/case_question'] = 'Cases/getCaseQuestion'; //Getting Question of a case
$route['api/cases_questions'] = 'Cases/getCasesQuestions'; //Getting questions of all cases

$route['api/add/case_question_answer'] = 'Cases/setCaseCorrectAnswer'; //Setting correct answer of a case question



$route['api/add/case_option'] = 'Cases/setCaseOption'; //Setting Options of a Question Case
$route['api/case_options'] = 'Cases/getCaseOptions'; //Getting all options of a case
$route['api/add/case_answer'] = 'Cases/setCaseAnswerUser'; //Setting case Answer


//-------------MAIN FEEDBACK------------------------
$route['api/add/main_feedback'] = 'MainFeedback/setMainFeedback'; //Setting Main Feedback


//------------RESPONSE------------------------
$route['api/add/response'] = 'Question/setResponseQuestion'; //Setting Main Feedback


//------------NOTIFICATION------------------------
$route['api/notification'] = 'Question/notificationQuestion'; //Setting Main Feedback

//-------------LOG------------------------
$route['api/add/log'] = 'Log/setLog'; //Setting Main Feedback

//-------------FORWARD------------------------
$route['api/forward/question'] = 'Question/forwardQuestion'; //Setting Main Feedback




//------------Question------------------
$route['api/questions/count'] = 'Question/getTotalCountQuestions'; //Getting count of questions
$route['api/unanswered_questions'] = 'Question/getUnansweredQuestions'; //Getting count of questions
$route['api/answer/text'] = 'Answer/saveAnswerText'; //Saving answer text
$route['api/answer/audio'] = 'Answer/saveAnswerAudio'; //Saving answer audio
$route['api/question/disapprove'] = 'Question/changeApprovedStatus'; //Changing approved status
