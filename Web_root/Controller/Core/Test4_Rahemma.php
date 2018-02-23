<?php


set_time_limit(1800);

$time_zone="Asia/Karachi";
if(function_exists('date_default_timezone_set'))date_default_timezone_set($time_zone);

///////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// GLOBAL VARIABLES ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////

//----- Paths for resources such as scripts, recordings -----
$base_dir  				= "http://127.0.0.1/Web_Root/";
$base_dir_absolute      = "D:/xampp/htdocs/Web_Root/";
$scripts_dir 			= $base_dir."Controller/helpers/Scripts/PollyGame/";
$database_dir           = $base_dir . "Controller/helpers/DBScripts/PollyGame/";
$prompts_dir 	        = $base_dir_absolute."Audio_Resources/PollyGame/prompts/";
$praat_dir 			    = $bases_dir_absolute."Audio_Resources/PollyGame/Praat/";
$friend_name_dir        = $praat_dir . "FriendNames/";
$sender_name_dir        = $praat_dir . "UserNames/";
$feedback_dir      	    = $praat_dir."Feedback/";
$voice_recordings_dir   = $praat_dir."Recordings/";

$test4_scripts  		= $base_dir."Controller/helpers/Scripts/Raahemaa_test4/API/";
$test4_recs  		    = $base_dir_absolute."Audio_Resources/Raahemaa_test4/Recordings/";
$test4_prompts  		= $base_dir_absolute."Audio_Resources/Raahemaa_test4/Prompts/";
$test4_fb_dir  			= $base_dir_absolute."Audio_Resources/Raahemaa_test4/Feedbacks/";
$test4_user_comments	= $base_dir_absolute."Audio_Resources/Raahemaa_test4/Comments/";

$polly_prompts_dir  	= "";
$log_file_path          = "D:\\xampp\\htdocs\\Web_Root\\Logs\\Raahemaa_test4\\";

//----- Call-Session Info variables-----
$callid 	   = "";
$thisCallStatus 	  = "Answered";    // Temporary assignment
$currentStatus 		  = "";
$destinationAppId     = "";
$useridUnCond 		  = "";
$useridUnEnc 		  = "";
$systemLanguage 	  ="Urdu";         // Language in which prompts will be played to the user
$countryCode 		  = "";
$sipProvider 	      = "WateenE1";

//----- Call Flow control -----
$phDirEnabled = "true";
$forwardedTo = array();    // Array of (EncPhNo-EffectNo, Number of times in this call)
$term = "#";
$atWhatAgeDoJobsKickIn = 0;
$atWhatAgeDoesFBKickIn = 0;
$atWhatAgeDoesClearVoiceKickIn = 0;

//----- Call Logging -----
$fh 		   = "";    // temprary variable to act as a place holder for file handle
$logEntry 	   = "";

//----- Global Voice Prompts---------
$ask_for_forwarding = "";
$what_to_do         = "";

//----- User ID's used for Testing rah-e-maa tests-----
$testers_user_ids = array(
    "3566",
    "26573",
    "1776",
    "142566",
    "142562",
    "147650"
);

// Cut-off limits, based on days, of previous calls and requests in Database
$callTableCutoff = getcallTableCutoff(5);
$reqTableCutoff  = getreqTableCutoff(5);

// Inbound ESL connection to FreeSwitch Server
$password = "Conakry2014";
$port 	  = "8021";
$host 	  = "127.0.0.1";
$fp 	  = event_socket_create($host, $port, $password); // Connection handle to FreeSwitch Server
$uuid 	  = ""; // Randomly generated value used by Freeswitch to identify one session (object for interaction with Call-legs) from another
if(isset($_REQUEST["uuid"])){  // session-id is passed from the Freeswitch
	$uuid = $_REQUEST["uuid"];
}

if(isset($_REQUEST["calltype"])){	// This is not incoming call as $calltype is set

	///////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////// OUTGOING CALLS ///////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////

	$calltype 	 = $_REQUEST["calltype"];
 	$testcall 	 = $_REQUEST["testcall"];
    $sipProvider = $_REQUEST["ch"];
	$userid 	 = $_REQUEST["phno"];
 	$oreqid 	 = $_REQUEST["oreqid"];
 	$recIDtoPlay = $_REQUEST["recIDtoPlay"];
 	$effectno 	 = $_REQUEST["effectno"];
 	$ocallid 	 = $_REQUEST["ocallid"];
 	$ouserid 	 = $_REQUEST["ouserid"];
 	$app 		 = $_REQUEST["app"];
 	$From 		 = $_REQUEST["From"];

    $currentStatus = 'InProgress';
	$useridUnEnc   = KeyToPh($userid);
	$countryCode   = getCountryCode($useridUnEnc);
	$temp 			 = getPreferredLangs($oreqid);
	$Langs 			 = explode(",", $temp);
	$systemLanguage  = $Langs[0];
	$ouserid = KeyToPh($ouserid);
	$callid  = makeNewCall($oreqid, $userid, $currentStatus, $calltype, $sipProvider);	// Create a row in the call table to store info related to current call

 	if(isset($_REQUEST["error"])){
 		$error= $_REQUEST["error"];
 		switch ($error) {
 			case 'USER_BUSY':
 			case 'CALL_REJECTED':
 				busyFCN($callid);
 				break;
 			case 'ALLOTTED_TIMEOUT':
 			case 'NO_ANSWER':
 			case 'RECOVERY_ON_TIMER_EXPIRE':
 				timeOutFCN($callid);
 				break;
 			case 'NO_ROUTE_DESTINATION':
 			case 'INCOMPATIBLE_DESTINATION':
 			case 'UNALLOCATED_NUMBER':
 				callFailureFCN($callid);
 				break;
 			default:
 				errorFCN($callid);
 				break;
 		}
 		exit(1);
 	}

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "App: ".$app.", Call Type: ".$calltype.", Phone Number: ".$userid.", Originating Request ID: ".$oreqid.", Call ID: ".$callid.", Country: PK, ouserid: ".$ouserid.", Country Code: " . $countryCode);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "Call Table cutoff found at:".$callTableCutoff);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "Req Table cutoff found at:".$reqTableCutoff);

	$polly_prompts_dir = $prompts_dir.$systemLanguage."/Polly/";

	sayInt($polly_prompts_dir. "sil1500.wav ".$polly_prompts_dir. "sil1500.wav ");

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "PGame prompts directory set to: ". $polly_prompts_dir);

	selectDelPrompt();
	selectMainPrompt("FALSE", 1);

	if($sipProvider == "WateenE1"){
	    if($calltype=="Call-me-back"){
    		StartUpFn();
    		PollyGameAnswerCall($callid,"FALSE");
    	}else if($calltype=="Delivery"){
    		StartUpFn();
    		PollyGameMsgDelivery($callid,"FALSE");
    	}else{
	    	Prehangup();
	    }
	}
}
else{

	///////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////// INCOMING CALLS ///////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////

	$oreqid 		= "0";
	$currentStatus  = 'InProgress';

	$PollyNumber 			= "0428333112";

	$destinationAppId = calledID(); 	//which number was called in the current call?
	$destinationAppId = trim(preg_replace('/\s\s+/', ' ', $destinationAppId));

	$userid  = getCallerID();
	$userid  = trim(preg_replace('/\s\s+/', ' ', $userid));

	$ouserid = $userid;

	if(strpos($destinationAppId, $PollyNumber) !== FALSE) {

		$testcall 	 = "FALSE";
		$requestType = "Call-me-back";
		$calltype = 'Missed_Call';
		$app = 'PollyGame';
	}
	else {

		$testcall = "FALSE";
		$requestType = "";
		$calltype = 'Unknown';
		$app = 'Alien';
		Prehangupunknown();
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$useridUnCond 	= $userid;
	$useridUnEnc 	= conditionPhNo($userid, $calltype);
	$useridUnEnc	= trim(preg_replace('/\s\s+/', ' ', $useridUnEnc));
	$userid 		= PhToKeyAndStore($useridUnEnc, 0);
	$countryCode 	= getCountryCode($useridUnEnc);

	$callid = makeNewCall($oreqid, $userid, $currentStatus, $calltype, 'WateenE1');	// Create a row in the call table to store this incoming call

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "PhToKeyAndStore returned :" . $userid);

	phoneNumBeforeAndAfterConditioning($useridUnCond, $useridUnEnc, $calltype, "");

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "App: ".$app.", Call Type: ".$calltype.", Phone Number: ".$userid.", Originating Request ID: ".$oreqid.", Call ID: ".$callid.", Country: PK, ouserid: ".$ouserid.", Country Code: " . $countryCode);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "Call Table cutoff found at:".$callTableCutoff);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "Req Table cutoff found at:".$reqTableCutoff);

	$polly_prompts_dir = $prompts_dir.$systemLanguage."/Polly/";

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "PGame prompts directory set to: ". $polly_prompts_dir);

	selectDelPrompt();
	selectMainPrompt("FALSE", 1);

	// If its a missed call then reject() will generate 2 retries from the Cisco equipment. This is to ignore those. OR If its a self loop call... ignore it. &&$$** Added < '1000' in place of == '04238333111'
	if(searchCalls($userid)>1 || $userid < '1000') {

		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Ignoring Call as it passed: searchCalls($userid)>1 || $userid < '1000' check."); 	 //&&$$** Added < '1000' in place of == '04238333111'
		rejectCall($app);
		exit(0);
	}

	if(searchCallsReq($userid) <= 0) { // Is there a pending request from this guy already which has never been retried?

		updateCallsReq($userid); // Upgrade all retry type Pending requests from this guy, if any.
		$reqid = createMissedCall('0', '0', $callid, $requestType, $userid, "Pending", $systemLanguage, "Urdu", "WateenE1", $destinationAppId);
	}
	else{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Not making a request because there are already first try Call-me-back requests of status Pending from this phone number.");
	}

	$thisCallStatus = "Complete";
	$status = "Complete";
	updateCallStatus($callid, $status);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Call Complete. Now exiting.");
	markCallEndTime($callid);
	rejectCall($app);
	exit(0);
}

///////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// Polly Game /////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////

function PollyGameMsgDelivery(){  //  start PollyGameMsgDelivery()
	// PHP requires all globals to be called like this from within all functions. Not using this was creating access problems.
	global $praat_dir;
	global $polly_prompts_dir;
	global $what_to_do;
	global $userid;
	global $ouserid;
	global $recIDtoPlay;
	global $effectno;
	global $ocallid;

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing greetings for message delivery.");

	$Name = $praat_dir."UserNames/".getFilePath($ocallid.".wav", "TRUE")."UserName-".$ocallid.".wav";
	$promptType = "PGame Delivery Greetings";
	$breakLoop = "FALSE";
	while($breakLoop == "FALSE"){
		$breakLoop = "TRUE";
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "About to listen to $promptType.");
		$result = sayInt($polly_prompts_dir."Greetings2.wav"."\n".$Name."\n".$polly_prompts_dir."Hereitis.wav"."\n");
		$breakLoop = bargeInToChangeLang($result, $breakLoop);
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Finished listening to $promptType.");
	}

	//Say out the message:
	$Path = $praat_dir."/ModifiedRecordings/".getFilePath($recIDtoPlay.".wav", "TRUE").$effectno."-s-".$recIDtoPlay.".wav";
	$repeat = "TRUE";
	$iter = 0;
	$playMsg = "TRUE";

	while($repeat == "TRUE"){
		if($playMsg == "TRUE"){
			if(!file_exists($Path)){
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "File cannot be found. Playing the prompt to wait.");
				sayInt($polly_prompts_dir."Processingplzwait.wav");//"Processing. Please wait!";

				$reps = 0;
				while(!file_exists($Path) && $reps<20){
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing the clock as the user waits.");
					sayInt($polly_prompts_dir."Processingplzwait.wav");//"Processing. Please wait!";
					sayInt($praat_dir."clock_fast.wav");
					$reps = $reps+1;
				}
			}
			else{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now playing the clock sound.");
				sayInt($praat_dir."clock_fast.wav");
			}

			$iter = $iter+1;

			$presult = sayInt($Path . "\n" . $polly_prompts_dir. "sil500.wav");
			if ($presult->name == 'choice')
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$presult->value." to skip ".$Path.".");
			}

			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Played the message.");
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now playing message related options.");
			$repeat = "FALSE";	// If no action taken then loop will break
		}
		$result = gatherInput($what_to_do,
		array(
				"choices" => "[1 DIGITS], *",//Using the [1 DIGITS] to allow tracking wrong keys"rpt(1,rpt), fwd(2, fwd), cont(3,cont), feedback(8, feedback), quit(9, quit)",
				"mode" => 'dtmf',
				"bargein" => true,
				"repeat" => 2,
				"timeout"=> 10,
				"onBadChoice" => "keysbadChoiceFCN",
				"onTimeout" => "keystimeOutFCN",
				"onHangup" => create_function("$event", "Prehangup()")
			)
		);

		if ($result->name == 'choice')// If User respond, then $result->name must be setted to choice
		{
			if ($result->value == 9)//'Sender's Phone Number'
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Sender's Phone number).");
				sayInt($polly_prompts_dir."senderPh.wav");	// The sender's phone number is:
				$occ = getCountryCode($ouserid);			// Sender's country code
				$oPhnoWoCC = substr($ouserid, strlen($occ));	// Sender's number wo country code

				$num12 = str_split($ouserid);//str_split($oPhnoWoCC);
				for($index1 = 0; $index1 < count($num12); $index1+=1)
				{
					$fileName = $num12[$index1].'.wav';
					$numpath = $polly_prompts_dir.$fileName;
					sayInt($numpath);
				}
				$repeat = "TRUE";
				$playMsg = "FALSE";
			}
			else if ($result->value == 1)//'rpt'
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Repeat).");
				$repeat = "TRUE";
				$playMsg = "TRUE";
			}
			else if ($result->value==2)//'fwd'
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Forward).");
				$repeat = "TRUE";
				PollyGameScheduleMsgDelivery($callid, $recIDtoPlay, $userid, $effectno, $Path);
				$playMsg = "TRUE";
			}
			else if($result->value==3)//'reply'
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Reply).");
				$repeat = "TRUE";
				$reply = "TRUE";
				PollyGameAnswerCall($callid, $reply);
				$playMsg = "TRUE";
			}
			else if($result->value==4)//'new'
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (New Recording).");

				$repeat = "TRUE";
				$reply = "FALSE";
				PollyGameAnswerCall($callid, $reply);
				$playMsg = "TRUE";
			}
			else{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (wrong key).");
				$repeat = "TRUE";
				sayInt($polly_prompts_dir."Wrongbutton.wav");
				$playMsg = "FALSE";
			}
		}
		else{
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User did not press any key.");
			sayInt($polly_prompts_dir."Nobutton.wav");
			$repeat = "FALSE";
			$playMsg = "FALSE";
		}
	}// end of main while loop while($repeat == "TRUE")

	sayInt($polly_prompts_dir."ContactDetails.wav");
	sayInt($polly_prompts_dir."Bye.wav");//"Thanks for calling. Good Bye."
	Prehangup();
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Hanging up.");
}// end PollyGameMsgDelivery()

function PollyGameAnswerCall($callid, $reply)//	start PollyGameAnswerCall()
	// PHP requires all globals to be called like this from within all functions. Not using this was creating access problems.
	global $scripts_dir;
	global $praat_dir;
	global $polly_prompts_dir;
	global $playInformedConsent;
	global $ask_for_forwarding;
	global $calltype;
	global $callid;
	global $userid;
	global $TreatmentGroup;
	global $Q;
	global $useridUnEnc;
	global $checkForQuota;
	global $recID;
	global $PGameCMBAge;
	global $atWhatAgeDoesClearVoiceKickIn;

	$firstEnc = "False";
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Inside PollyGameAnswerCall(). Calltype: ".$calltype);

	if($calltype != 'Delivery'){
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing Greetings");
			$promptType = "PGame Greetings";
			$breakLoop = "FALSE";
			while($breakLoop == "FALSE"){
				$breakLoop = "TRUE";
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "About to listen to $promptType.");
				$result = sayInt($polly_prompts_dir."Salaam.wav"." ".$polly_prompts_dir."sil1000.wav ".$polly_prompts_dir."Greetings.wav");
				$breakLoop = bargeInToChangeLang($result, $breakLoop);
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Finished listening to $promptType.");
			}
	}

	selectMainPrompt($reply, 1);
	if($calltype != 'SystemMessage' && $playInformedConsent == "TRUE" ){
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now playing Informed consent.");
		sayInt($polly_prompts_dir."InformedConsent.wav");
		$playInformedConsent = "FALSE";							// Play informed consent only once in each call
	}

	$recid = makeNewRec($callid);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Assigned Recording ID ".$recid);

	// For doing a rerecording
	$rerecordAllowed = 1; // Rerecord allowed in this call
    $rerecord = "TRUE";
	while($rerecord == "TRUE"){
		$rerecord = "FALSE";

		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Prompting the user to speak");
		if($PGameCMBAge < 0){
			$promptTheUserToSpeak = $polly_prompts_dir."Polly-intro-2.wav\n" . $polly_prompts_dir."sil500.wav\n" . $polly_prompts_dir."Ready-here-goes.wav\n";
		}
		else{
			$promptTheUserToSpeak = $polly_prompts_dir."Promptforspeaking.wav";
		}

		$result = recordAudio($promptTheUserToSpeak,		//"Just say something after the beep and Press # when done."
					array(
						"beep" => true, "timeout" => 600, "silenceTimeout" => 3, "maxTime" => 30, "bargein" => false, "terminator" => $term,
						"recordURI" => $scripts_dir."process_recording.php?recid=$recid",
						"format" => "audio/wav",
						"onHangup" => create_function("$event", "Prehangup()")
						)
					);

		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Recording Complete. Result: ".$result);

		$filePath = $praat_dir;
		$fileNames[0] = "99-s";
		$fileNames[1] = "0-s";
		$fileNames[2] = "1-s";
		$fileNames[3] = "2-s";
		$fileNames[4] = "3-s";
		$fileNames[5] = "4-s";
		$fileNames[6] = "5-s";
		$fileNames[7] = "6-s";
		$fileNames[8] = "7-s";
		$fileNames[9] = "HIs";
		$fileNames[10] = "LOs";
		$fileNames[11] = "RVSs";
		$fileNames[12] = "BKMUZWHs";

		$Path = "";
		$repeat = "TRUE";

		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Prompting the user to get ready for effects.");
		sayInt($polly_prompts_dir."Readyforeffects.wav");//"Now get ready for the effects... Here it goes!!!"

		$NumOfEffects = 7;
		for($count = 0; $count <= $NumOfEffects && $repeat == "TRUE"; $count+=1)
		{
			$audioFileName = $fileNames[$count]."-".$recid.".wav";
			$Path = $filePath . "ModifiedRecordings/" . getFilePath($recid.".wav", "TRUE") . $audioFileName;

			$reps = 0;

			while((doesFileExist($audioFileName)=="0")){
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing the clock as the user waits.");
				sayInt($praat_dir."clock_fast.wav");
				$reps = $reps+1;
			}
			sayInt($praat_dir."clock_fast.wav");

			$presult = sayInt($Path . "\n" . $polly_prompts_dir. "sil500.wav");
			if ($presult->name == 'choice')
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$presult->value." to skip ".$Path.".");
			}

			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Played effect number: ".$count);
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now playing effect related options.");
			$repeat = "FALSE";	// If not action taken then loop will break
			// "To Repeat, press one. To send to a friend, press two. To try another effect, press three",
			// Using the [1 DIGITS], * to allow tracking wrong keys"rpt(1,rpt), fwd(2, fwd), cont(3,cont), feedback(8, feedback), quit(9, quit)",

			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing main menu prompt to the user: ".$Ask_for_forwarding2);

			$result = gatherInput($ask_for_forwarding, array(
					"choices" => "[1 DIGITS], *",
					"mode" => 'dtmf',
					"bargein" => true,
					"repeat" => 2,
					"timeout"=> 10,
					"onBadChoice" => "keysbadChoiceFCN",
					"onTimeout" => "keystimeOutFCN",
					"onHangup" => create_function("$event", "Prehangup()")
				)
			);
			if ($result->name == 'choice'){
				if ($result->value == 1)//'rpt')
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Repeat).");
					$repeat = "TRUE";
					$count = $count - 1;
				}
				else if ($result->value==2)//'fwd')
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Forward).");
					$rerecordAllowed = 0;	// Can't rerecord in this call anymore
					selectMainPrompt($reply, 0);
					if($reply == "FALSE"){
						$repeat = "TRUE";
						PollyGameScheduleMsgDelivery($callid,$recid, $userid, $count, $Path);
					}
					else if($reply == "TRUE"){
						$repeat = "FALSE";
						PollyGameScheduleMsgReply($callid,$recid, $userid, $count, $Path);
					}
				}
				else if($result->value==3)//'cont')
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Next).");
					$repeat = "TRUE";
					if($count == $NumOfEffects){
						$count = -1;
					}
					continue;
				}
				else if ($result->value==4 && ($PGameCMBAge >= $atWhatAgeDoesClearVoiceKickIn))//'fwd' clear voice)
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Forward using unmodified voice).");
					$rerecordAllowed = 0;	// Can't rerecord in this call anymore
					selectMainPrompt($reply, 0);
					if($reply == "FALSE"){
						$repeat = "TRUE";
						PollyGameScheduleMsgDelivery($callid,$recid, $userid, 99, $Path);	// number 4 is the unmodified voice
					}
					else if($reply == "TRUE"){
						$repeat = "FALSE";
						PollyGameScheduleMsgReply($callid,$recid, $userid, 99, $Path);	// number 4 is the unmodified voice
					}
				}
				else if($result->value==5)//'ReM Hook')
				{
					{
						Test4Menu();
						$repeat = "TRUE";
					}
				}
				else if($result->value==8)//'feedback')
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Feedback).");
					$fbtype = "UInit";
					$fbid = makeNewFB($fbtype, $callid);
					$repeat = "TRUE";
					$feedBack = recordAudio($polly_prompts_dir."Recordyourfeedback.wav",
							array(
								"beep" => true, "timeout" => 600.0, "silenceTimeout" => 4.0, "maxTime" => 60, "bargein" => false, "terminator" => "#",
								"recordURI" => $scripts_dir."process_feedback.php?fbid=$fbid&fbtype=$fbtype",
								"format" => "audio/wav",
								"onHangup" => create_function("$event", "Prehangup()")
								)
							);
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Feedback Recording Complete. Result: ".$feedBack);
					sayInt($polly_prompts_dir."ThanksforFeedback.wav");
					$count = $count - 1;
				}
				else if ($result->value==0 && $rerecordAllowed == 1)//'rerecord')
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (Rerecord).");
					$repeat = "FALSE";
					$rerecord = "TRUE";
				}
				else
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$result->value." (wrong key).");
					$repeat = "TRUE";
					sayInt($polly_prompts_dir."Wrongbutton.wav");
					$count = $count - 1;// Dealing with bad choices as repeats.
				}
			}
			else
			{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User did not press any key.");
				sayInt($polly_prompts_dir."Nobutton.wav");
				$repeat = "FALSE";
			}
			if($count == $NumOfEffects)
			{
				$count = -1;
			}
		}//Continue in for loop: play next effect
	}// Only loop this loop if the user want to rerecord
	if($reply != "TRUE"){// If this function was being used to record a reply then don't hangup from here
		sayInt($polly_prompts_dir."Bye.wav");//"Thanks for calling. Good Bye."
		Prehangup();
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Hanging up.");
	}
}// end PollyGameAnswerCall() function

function PollyGameScheduleMsgDelivery($callid, $recid, $telNumber, $count, $songpath){
	// PHP requires all globals to be called like this from within all functions. Not using this was creating access problems.
	global $scripts_dir;
	global $praat_dir;
	global $countryCode;
	global $systemLanguage;
	global $polly_prompts_dir;
	global $term;
	global $dlvRequestType;
	global $AlreadygivenFeedback;
	global $userid;
	global $callerPaidDel;
	global $PGameCMBAge;
	global $phDirEnabled;
	global $sipProvider;
	global $hasTheUserRecordedAName;
	global $oreqid;
	global $WaitWhileTheUserSearchesForPhNo;

	$numOfDigs = "[11-12 DIGITS]";

	//Prompt for friends' numbers
	$FriendsNumber = 'true';
	$numNewRequests = 1;
	$retryNumEntry = "false";
	while($FriendsNumber != 'false')
	{
		$phoneNumChosen = "None";
		$phoneNumber = "";
		$NoOfEntriesInDir = 0;
		$NewNumberEntered = "true";
		if($phDirEnabled == "true" && $retryNumEntry == "false"){	// Is Phone directory feature enabled? AND this is not a retry of number entry?
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Phone Directory feature is enabled.");
			$PhDir = getPhDir();
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Current Phone Directory: ".$PhDir);
			$entries = explode("-", $PhDir);
			$NoOfEntriesInDir = count($entries)-1;
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Number of directory entries: ".$NoOfEntriesInDir);
			if($NoOfEntriesInDir > 0){	// there are entries in the Phone Directory for this user
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now Giving option to select a directory entry.");

				// This is the Phone Number Directory
				// Create the prompt here
				$DirPrompt = "";
				for($j=1; $j < ($NoOfEntriesInDir+1); $j++){

					$DirPrompt = $DirPrompt . "\n" . $praat_dir . "FriendNames/".getFilePath($userid.".wav", "TRUE") . $userid . "-" . $entries[$j] . ".wav";
					$DirPrompt = $DirPrompt . "\n" . $polly_prompts_dir . "For.wav";
					$DirPrompt = $DirPrompt . "\n" . $polly_prompts_dir . "SendTo" . $j . ".wav";
				}

				$DirPrompt = $DirPrompt . "\n" . $polly_prompts_dir."NewNumber.wav";
				// Give the choice to enter a new number (0) or choose a number from the list
				$Choice = gatherInput($DirPrompt,
				array(
					"choices"=> "[1 DIGITS], *",
					"mode" => 'dtmf',
					"bargein" => true,
					"attempts" => 2,
					"onBadChoice" => "keysbadChoiceFCN",
					"onTimeout" => "keystimeOutFCN",
					"timeout"=> 7,
					"onHangup" => create_function("$event", "Prehangup()")
					)
				);
				if($Choice->name == 'choice'){	// otherwise the user will be asked to enter a number.
					if($Choice->value == 0){	// Enter new number
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$Choice->value." (Enter a number manually).");
						// do nothing, as $NewNumberEntered is already true, so the user will be asked to enter a number
					}
					else if($NoOfEntriesInDir >= $Choice->value){
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$Choice->value." (".$entries[$Choice->value].").");
						$phoneNumber = $entries[$Choice->value];
						$NewNumberEntered = "false";
						$phoneNumChosen = "choice";
						updatePhDir($phoneNumber);
					}
					else{
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$Choice->value." (wrong key).");
						sayInt($polly_prompts_dir."Wrongbutton.wav");
					}
				}
				else{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User did not press any key.");
				}
			}
		}

		if($NoOfEntriesInDir == 0 || $NewNumberEntered == "true" || $retryNumEntry == "true"){
			$breakLoop = "TRUE";
			if($WaitWhileTheUserSearchesForPhNo == "TRUE"){
				$breakLoop = "FALSE";
			}
			$loopTries = 0;
			$op1 = "TRUE";
			$op2 = "FALSE";
			while($breakLoop == "FALSE" && $loopTries < 3){
				if($op1 == "TRUE"){
					$action = gatherInput($polly_prompts_dir."EHL-fwd-2.wav", array("choices" => "[1 DIGITS], *", "mode" => 'dtmf', "bargein" => true, "repeat" => 1, "timeout"=> 15, "onHangup" => create_function("$event", "Prehangup()")));//"Whenever you are ready to enter the phone number, press 1."
				}
				else if($op2 == "TRUE"){
					$action = gatherInput($polly_prompts_dir."EHL-fwd-2.wav\n".$polly_prompts_dir."EHL-fwd-4-nopress.wav\n".$polly_prompts_dir."SendTo2.wav\n", array("choices" => "[1 DIGITS], *", "mode" => 'dtmf', "bargein" => true, "repeat" => 9, "timeout"=> 20, "onHangup" => create_function("$event", "Prehangup()")));//"Whenever you are ready to enter the phone number, press 1."
				}
				if($action->name != 'choice'){
					if($op1 == "TRUE"){
						$op1 = "FALSE";
						$op2 = "TRUE";
					}
					else if($op2 == "TRUE"){
						$breakLoop = "TRUE";
						return ($numNewRequests-1);
					}
				}
				else if($action->value == 1){
					$breakLoop = "TRUE";
				}
				else if($action->value == 2){
					$breakLoop = "TRUE";
					return "FALSE";
				}
				else if($loopTries < 2){
				}
				else{
					return ($numNewRequests-1);
				}
				$loopTries++;
			}
			$retryNumEntry = "false";
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now getting friend's phone number for request number".$numNewRequests.".");
			$NumberList = gatherInput($polly_prompts_dir."FriendnopromptWOHash.wav",//"Please enter the phone number of your friend followed by the pound key",
				array(
					"choices"=>$numOfDigs,
					"mode" => 'dtmf',
					"bargein" => true,
					"attempts" => 3,
					"timeout"=> 30,
					"interdigitTimeout"=> 20,
					"onBadChoice" => "keysbadChoiceFCN",
					"onTimeout" => "keystimeOutFCN",
					"terminator" => $term,
					"onHangup" => create_function("$event", "Prehangup()")
					)
				);
			if($NumberList->name == 'choice'){
				sayInt($polly_prompts_dir."Friendnorepeat.wav");				//here is the number you entered
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Friend's phone number entered: ".$NumberList->value.". Now playing it.");

				$num12 = str_split($NumberList->value);
				for($index1 = 0; $index1 < count($num12); $index1+=1)
				{
					if($index1 == 0){
						$fileName = $num12[$index1].'.wav';
						$numpath = $polly_prompts_dir.$fileName;
					}
					else{
						$fileName = $num12[$index1].'.wav';
						$numpath = $numpath . "\n" . $polly_prompts_dir.$fileName;
					}
				}
				$presult = sayInt($numpath);
				if ($presult->name == 'choice')
				{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$presult->value." to skip ".$numpath.".");
				}
			}
			else{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Timed out. No number entered. Now hanging up.");
					sayInt($polly_prompts_dir."Bye.wav");//"Thanks for calling. Good Bye."
					$FriendsNumber = 'false';
					Prehangup();
			}

			if($FriendsNumber != 'false'){
				// Number Confirmation
				$WrongButtonPressed = 'TRUE';
				while($WrongButtonPressed == 'TRUE'){
					$WrongButtonPressed = 'FALSE';
					$NumCorrect = gatherInput($polly_prompts_dir."Numberconfirm.wav",//"If this is correct, press one, otherwise, press two",
						array(
							"choices"=> "[1 DIGITS], *",
							"mode" => 'dtmf',
							"bargein" => true,
							"attempts" => 2,
							"onBadChoice" => "keysbadChoiceFCN",
							"onTimeout" => "keystimeOutFCN",
							"timeout"=> 10,
							"onHangup" => create_function("$event", "Prehangup()")
							)
						);
					if($NumCorrect->name == 'choice' && $NumCorrect->value != 1 && $NumCorrect->value != 2){	// Wrong button
						$WrongButtonPressed = 'TRUE';
						sayInt($polly_prompts_dir."Wrongbutton.wav");
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed a wrong key: ".$NumCorrect->value);
						// Repeat the phone number
						sayInt($polly_prompts_dir."Friendnorepeat.wav");				//here is the number you entered
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Friend's phone number entered: ".$NumberList->value.". Now playing it again.");

						$num12 = str_split($NumberList->value);
						for($index1 = 0; $index1 < count($num12); $index1+=1)
						{
							if($index1 == 0){
								$fileName = $num12[$index1].'.wav';
								$numpath = $polly_prompts_dir.$fileName;
							}
							else{
								$fileName = $num12[$index1].'.wav';
								$numpath = $numpath . "\n" . $polly_prompts_dir.$fileName;
							}
						}
						$presult = sayInt($numpath);
						if ($presult->name == 'choice')
						{
							writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$presult->value." to skip ".$numpath.".");
						}
					}
				}

				if($NumCorrect->name == 'choice'){
					if($NumCorrect->value == 1){//correct
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User Confirmed the number by pressing:".$NumCorrect->value);
						$phoneNumChosen = $NumberList->name;
						$phoneNumber = $NumberList->value;
						$NewNumberEntered = "true";

					}
					else if($NumCorrect->value == 2){ //If number entered is not correct
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User wants to enter the number again by pressing: ".$NumCorrect->value);
						sayInt($polly_prompts_dir."Tryagain.wav");//"Please try again"
						$retryNumEntry = "true";
					}
				}
				else{
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Timed out. User did not press any key. Now hanging up.");
					$FriendsNumber = 'false';
					sayInt($polly_prompts_dir."Bye.wav");//"Thanks for calling. Good Bye."
					Prehangup();
				}
			}
		}// if($NoOfEntriesInDir == 0 || $NewNumberEntered == "true")
		if($phoneNumChosen == 'choice'){

			if($hasTheUserRecordedAName == "FALSE"){	// Only record the name once per call
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now recording user's name.");
				// Prompt the user for his/her name
				$ownName = recordAudio($polly_prompts_dir."Recordyourname.wav",//"Please record your name, so that your friend can send you a message back",
							array(
								"beep" => true, "timeout" => 600.0, "silenceTimeout" => 2.0, "maxTime" => 4, "bargein" => false, "terminator" => $term,
								"recordURI" => $scripts_dir."process_UserNamerecording.php?callid=$callid",
								"format" => "audio/wav",
								"onHangup" => create_function("$event", "Prehangup()")
								)
							);

				$hasTheUserRecordedAName = "TRUE";
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Recording of user's name complete.".$ownName);
			}
			// Create a new Request here
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now formatting the number (if required).");		// added
			$frndNoEnc = "";
			if($NewNumberEntered == "true"){
				$formattedPhNo = conditionPhNo($phoneNumber, "Delivery");
				$frndNoEnc = PhToKeyAndStore($formattedPhNo, $userid);	// added
			}
			else{
				$frndNoEnc = $phoneNumber;	// chosen from existing directory
			}
			//----->
			if(isForwardingAllowedToThisPhNo($frndNoEnc, $recid, 'msg', $count) == 'yes'){
				$reqsipProvider = whatWasThesipProviderOfTheOriginalRequest($oreqid);
				$reqid = makeNewReq($recid, $count, $callid, $dlvRequestType, $frndNoEnc, "WPending", $systemLanguage, "Urdu", $reqsipProvider);

				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Assigned request ID: ".$reqid);
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now recording user's friend's name");

				if($NewNumberEntered == "true" && $phDirEnabled == "true"){
							$FName = gatherInput($polly_prompts_dir."SaveNumber.wav",
							array(
								"choices"=> "[1 DIGITS], *",
								"mode" => 'dtmf',
								"bargein" => true,
								"attempts" => 1,
								"onBadChoice" => "keysbadChoiceFCN",
								"onTimeout" => "keystimeOutFCN",
								"timeout"=> 7,
								"onHangup" => create_function("$event", "Prehangup()")
								)
							);
							if($FName->name == 'choice'){
								if($FName->value == 1){
									writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$FName->value." (Record Friend's name).");

									$friendsName = recordAudio($polly_prompts_dir."NameOfNumber.wav",//"Okay! After the beep please record your friend's name",
										array(
											"beep" => true, "timeout" => 600.0, "silenceTimeout" => 2.0, "maxTime" => 4, "bargein" => false, "terminator" => $term,
											"recordURI" => $scripts_dir."process_FriendNamerecording.php?reqid=".$userid."-".$frndNoEnc,
											"format" => "audio/wav",
											"onError" => create_function("$event", 'sayInt("Wrong Input");'),
											"onTimeout" => create_function("$event", 'sayInt("No Input");'),
											"onHangup" => create_function("$event", "Prehangup()")
											)
										);
									writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User's friend's name recording complete.".$friendsName);
									updatePhDir($frndNoEnc);
							}
							else if($FName->value == 2){
								writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$FName->value." (Do not record Friend name).");
							}
							else{
								writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$FName->value." (Wrong key).");
							}
						}
						else{
							writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User did not press any key.");
						}
					}
					sayInt($polly_prompts_dir."Forward_confirmation2.wav");		// Thanks your message would be sent soon.
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User is thanked and informed that the message would be sent soon.");
				}

				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Asking the user if he wants to record another name.");
				$WrongButtonPressed = 'TRUE';
				while($WrongButtonPressed == 'TRUE'){
					$WrongButtonPressed = 'FALSE';
					$MoreNumbers = gatherInput($polly_prompts_dir."Anotherfriend-otherwise.wav",//"To add another number, press one, or if you are done, press two",
						array(
							"choices"=> "[1 DIGITS], *",
							"mode" => 'dtmf',
							"bargein" => true,
							"attempts" => 2,
							"onBadChoice" => "keysbadChoiceFCN",
							"onTimeout" => "keystimeOutFCN",
							"timeout"=> 10,
							"onHangup" => create_function("$event", "Prehangup()")
							)
						);
					if($MoreNumbers->name == 'choice' && $MoreNumbers->value != 1 && $MoreNumbers->value != 2){	// Wrong button
						$WrongButtonPressed = 'TRUE';
						sayInt($polly_prompts_dir."Wrongbutton.wav");
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed a wrong key: ".$MoreNumbers->value);
					}
				}

				if($MoreNumbers->name == 'choice'){
					if($MoreNumbers->value == 2){
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User presses ".$MoreNumbers->value." to say that he is done");
						$FriendsNumber = 'false';
					}
					else if($MoreNumbers->value == 1){
						writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User presses ".$MoreNumbers->value." to say that he wants to record another number.");
						$numNewRequests++;
					}
				}
				else{	// No key was pressed so, assume that he does not want to add another number
					writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Timed out. User did not press any key. Now proceeding.");
					$FriendsNumber = 'false';
				}
			}
			else if($retryNumEntry == "true"){
				// continue
			}
			else{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Timed out. No number entered. Now hanging up.");
				sayInt($polly_prompts_dir."Bye.wav");//"Thanks for calling. Good Bye."
				$FriendsNumber = 'false';
				Prehangup();
			}
	}//End of while($Friendsnumber != false)

	// Requesting feedback
	$previousFeedBack = gaveFeedBack($telNumber);	// Did he ever give feedback before?
	if(((($PGameCMBAge > 5 && $previousFeedBack == 0) || $PGameCMBAge % 20 == 0) && $AlreadygivenFeedback == "FALSE") && $PGameCMBAge != 0){

		$AlreadygivenFeedback = "TRUE";
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Requesting System prompted feedback ".$PGameCMBAge." ".$previousFeedBack." ".$telNumber.".");
		$fbtype = $dlvRequestType . "-SPrompt";
		$fbid = makeNewFB($fbtype, $callid);

		$feedBack = recordAudio($polly_prompts_dir."Recordyourfeedback.wav",//
				array(
					"beep" => true, "timeout" => 600.0, "silenceTimeout" => 4.0, "maxTime" => 30, "bargein" => false, "terminator" => $term,
					"recordURI" => $scripts_dir."process_feedback.php?fbid=$fbid&fbtype=$fbtype",
					"format" => "audio/wav",
					"onHangup" => create_function("$event", "Prehangup()")
					)
				);
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Feedback Recording Complete. Result: ".$feedBack);
		sayInt($polly_prompts_dir."ThanksforFeedback.wav");
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "System prompted feedback recording complete.");
	}

	return $numNewRequests;
}// PollyGameScheduleMsgDelivery(

function PollyGameScheduleMsgReply($callid, $recid, $telNumber, $count, $songpath){
	// PHP requires all globals to be called like this from within all functions. Not using this was creating access problems.
	global $scripts_dir;
	global $praat_dir;
	global $systemLanguage;
	global $polly_prompts_dir;
	global $term;
	global $dlvRequestType;
	global $sipProvider;
	global $hasTheUserRecordedAName;
	global $oreqid;

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Now recording user's name.");
	// Prompt the user for his/her name
	if($hasTheUserRecordedAName == "FALSE"){
		$ownName = recordAudio($polly_prompts_dir."Recordyourname.wav",//"Please record your name, so that your friend can send you a message back",
					array(
						"beep" => true, "timeout" => 600.0, "silenceTimeout" => 2.0, "maxTime" => 4, "bargein" => false, "terminator" => $term,
						"recordURI" => $scripts_dir."process_UserNamerecording.php?callid=$callid",
						"format" => "audio/wav",
						"onHangup" => create_function("$event", "Prehangup()")
						)
					);
		$hasTheUserRecordedAName = "TRUE";
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Recording of user's name complete.".$ownName);
	}
	// Create a new Request here
	$reqsipProvider = whatWasThesipProviderOfTheOriginalRequest($oreqid);
	$reqid = makeNewReq($recid, $count, $callid, $dlvRequestType, getPhNo(), "WPending", $systemLanguage, "Urdu", $reqsipProvider);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Assigned request ID: ".$reqid);

	sayInt($polly_prompts_dir."Forward_confirmation2.wav");		// Thanks your message would be sent soon.
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User is thanked and informed that the message would be sent soon.");
}// end PollyGameScheduleMsgReply()

function selectMainPrompt($reply, $rerecordAllowed){// start selectMainPrompt()
	global $PGameCMBAge;
	global $atWhatAgeDoesFBKickIn;
	global $atWhatAgeDoesClearVoiceKickIn;
	global $ask_for_forwarding;
	global $polly_prompts_dir;
	global $test4_prompts;

	$ask_for_forwarding = "";
	if($rerecordAllowed == 1){
		$ask_for_forwarding = $ask_for_forwarding . "\n" . $polly_prompts_dir."12.wav"  . "\n" . $polly_prompts_dir."sil250.wav";		// to rerecord, press 0
	}

	$ask_for_forwarding = $ask_for_forwarding . "\n" . $polly_prompts_dir."Hear-your-recording-nopress.wav" . "\n" . $polly_prompts_dir."SendTo1.wav";		// to relisten, press 1

	if($reply == "TRUE"){
		$ask_for_forwarding = $ask_for_forwarding . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."15.wav";		// to reply using this voice, press 2
	}
	else{
		$ask_for_forwarding = $ask_for_forwarding . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."14.wav";		// to send to friends, press 2
	}

	$ask_for_forwarding = $ask_for_forwarding . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."16.wav";		// to go to the next effect, press 3

	if($PGameCMBAge >= $atWhatAgeDoesClearVoiceKickIn){
		$ask_for_forwarding = $ask_for_forwarding . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."26.wav" . "\n" . $polly_prompts_dir."SendTo4.wav";		// to send message in your unmodified voice, press 4
	}

	$ask_for_forwarding = $ask_for_forwarding." ". $test4_prompts."Superabbu.wav ";

	if($PGameCMBAge >= $atWhatAgeDoesFBKickIn){
		$ask_for_forwarding = $ask_for_forwarding . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."19.wav";		// for feedback, press 8
	}
}// end selectMainPrompt()


function selectDelPrompt(){ // start selectDelPrompt()
	global $what_to_do;
	global $polly_prompts_dir;

	$what_to_do = "";
	$what_to_do = $what_to_do . "\n" . $polly_prompts_dir."21.wav";												// to relisten, press 1
	$what_to_do = $what_to_do . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."22.wav";		// to send to friends, press 2
	$what_to_do = $what_to_do . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."23.wav";		// to reply, press 3
	$what_to_do = $what_to_do . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."24.wav";		// to record a new message, press 4
	$what_to_do = $what_to_do . "\n" . $polly_prompts_dir."sil250.wav" . "\n" . $polly_prompts_dir."20.wav";		// to listen to the phone number of the sender, press 0
}// end selectDelPrompt()


/*>>>>*********************************************************************************************************<<<<*/
/*>>>>***********************************************  Test 4  ************************************************<<<<*/
/*>>>>*********************************************************************************************************<<<<*/

/*******************************************************************************************************************/
/************************************************  Model Functions  ************************************************/
/*******************************************************************************************************************/

function getActionID($type){

	switch ($type) {
		case "menu_all_questions":
			return 1;
		case "listen_oq":
			return 2;
		case "menu_user_question":
			return 3;
		case "menu_ask_question":
			return 4;
		case "listen_case":
			return 5;
		case "menu_case":
			return 6;
		case "main_feedback":
			return 7;
		case "listen_aq":
			return 8;
		case "main_return":
			return 9;
		default:
			return 0;
	}

}

function setLog($action, $fileid)
{

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $test4_scripts;
	global $userid, $callid;

	$result = doCurl($test4_scripts."api/add/log?user_id=".$userid."&action_id=".$action."&call_id=".$callid."&file_id=".$fileid);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);


	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;
	}
	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");

		return true;
	}

}

function addQuestion()
{

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/add/question_user?user_id=".$userid."&call_id=".$callid);              //api hit

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);


	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;

	}
	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");

		if(!isset($result['result']['question_id']))
    	{
    	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true, message = api object error ");
    	}

		return $result['result']["question_id"];
	}

}

function getUserQuestions() {

	writeToLog($GLOBALS['call_id'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/questions_user?user_id=".$userid);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);


	if ($result['result']["error"])  {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");
	} else {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false ");
    	if(!isset($result['result']['user_questions']))
    		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true, message = api object error ");
    	else
			return $result['result']['user_questions'];
	}
	return false;
}

function getUserAttemptedQuestions($action_id, $attempted = true) {

	writeToLog($GLOBALS['call_id'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/questions_user_attempted?user_id=".$userid."&action_id=".$action_id."&attempted=".$attempted);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);


	if ($result['result']["error"])  {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");
	} else {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false ");
    	if(!isset($result['result']['questions']))
    		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true, message = api object error ");
    	else if(sizeof($result['result']['questions']) > 0)
			return $result['result']['questions'];
	}
	return false;
}

function addFeedback()
{

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/add/main_feedback?user_id=".$userid."&call_id=".$callid);              //api hit

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);


	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;

	}

	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");

		return $result['result']["feedback_id"];
	}
}

function getCaseQuestions()  {

    writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/cases_questions");

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);

	if ($result['result']["error"]) {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");
	}
	else {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");
		if(!isset($result['result']['case_questions']))
    		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true, message = api object error ");
    	else
			return $result['result']['case_questions'];
	}
	return false;
}

function setCaseAnswer($case_id,$answer) {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/add/case_answer?user_id=".$userid."&call_id=".$callid."&case_id=".$case_id."&user_answer=".$answer);              //api hit

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);


	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;

	}

	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");

		return true;
	}
}

function getQuestions($action_id, $oq, $attempted) {

	writeToLog($GLOBALS['call_id'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/get_questions?user_id=$userid&action_id=$action_id&attempted=$attempted&oq=$oq");    //api hit

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=".$result['result']["error"]);

	if ($result['result']["error"])
		return false;
	else
		return $result['result']['questions'];
}

function createComment($question_id) {

	writeToLog($GLOBALS['call_id'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/add/question_feedback?question_id=".$question_id."&user_id=".$userid."&call_id=".$callid);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");

	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;

	}

	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");

	    return $result['result']['q_feedback_id'] ;
    }
}

function createForward($question_id, $fuid) {

	writeToLog($GLOBALS['call_id'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/forward/question?file_id=".$question_id."&user_id=".$userid."&call_id=".$callid."&dest=".$fuid);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");

	if ($result['result']["error"]){

		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");
		return false;
	}

	else{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");
	    return $result['result']['forward_id'] ;
    }
}

function gettest4_user_comments($question_id)
{
	writeToLog($GLOBALS['call_id'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/question_feedback?question_id=".$question_id);              //api hit

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");

	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;

	}

	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");

	    return $result['result']['question_feedback'] ;
    }
}

function setResponse( $question_id, $response)
{
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/add/response?user_id=".$userid."&call_id=".$callid."&question_id=".$question_id."&response=".$response);              //api hit

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");

	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;

	}

	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");


		return true;
	}
}

function T4_GetDeliveryParams($del_id){

	writeToLog($GLOBALS['call_id'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $userid, $callid;

	global $test4_scripts;

	$result = doCurl($test4_scripts."api/delivery_params?id=".$del_id);              //api hit

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " JSON received: ".$result);

	$result = json_decode($result, true);

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");

	if ($result['result']["error"])
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=true.");

		return false;

	}

	else
	{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning, error=false.");
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " ".implode($result['result']['qa']));

	    return $result['result']['qa'] ;
    }

}

/*******************************************************************************************************************/
/**************************************************  Controllers   *************************************************/
/*******************************************************************************************************************/

function Test4Menu() {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $test4_prompts;
	global $test4_recordings;
	global $test4_scripts;

	sayInt($test4_prompts."Mainprompt1.wav ");

	$loop = true;

	while ($loop) {

		$prompt =   $test4_prompts."Mainprompt2.wav";

		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " prompt: ".$prompt);

		$result = gatherInput($prompt, array(
						"choices" => "[1 DIGITS]",
						"mode" => 'dtmf',
						"bargein" => false,
						"repeat" => 2,
						"timeout"=> 10,
						"onBadChoice" => "keysbadChoiceFCN",
						"onTimeout" => "ReMkeystimeOutFCN",
						"onHangup" => create_function("$event", "Prehangup()")
					)
				);

		if ($result->value == "1"){ 			//ask question
			setLog(getActionID("menu_ask_question"), "0");
			askQuestion();															//its arguments
		}

		else if ($result->value == "2"){		//user question
			setLog( getActionID("menu_all_questions") , "0");
			allQuestions();
		}

		else if ($result->value == "3"){   // all question
			setLog( getActionID("menu_user_question"), "0");
			listenOwnQuestion();
		}

		else if ($result->value == "4"){		//case
			setLog( getActionID("menu_case"), "0");
			caseQuiz();
		}

		else if ($result->value == "5"){		//main feedback
			setLog( getActionID("main_feedback"), "0");
			feedback();
		}else if ($result->value == "6"){		//back to polly
			setLog( getActionID("main_return"), "0");
			$loop = false;
		}
		else{
			sayInt($test4_prompts."Wrongbutton.wav");
			$loop = true;
		}
	}

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");
}

function askQuestion() {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $callid;
	global $userid;
	global $test4_prompts;
	global $test4_recordings;
	global $test4_scripts;

	sayInt($test4_prompts."Disclaimer1.wav");

	$question_id = addQuestion();

	if (!$question_id) {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Something bad happened in ".__FUNCTION__);
		return;
	}

	$loop	     = true;
	$rerecord 	 = true;

	while ($question_id && $loop) {

		if ($rerecord) {
			recordAudio($test4_prompts."Record1.wav", array(
		       "beep"=>true,
		       "timeout"=>300,
		       "bargein" => false,
		       "silenceTimeout"=>4,
		       "maxTime"=>120,
		       "terminator" => "#",
		      // "recordFormat" => "audio/wav",
		       "format" => "audio/wav",
		       "recordURI" => $test4_scripts."api/add/question_user?question_id=".$question_id."&user_id=".$userid."&call_id=".$callid,
		        )
		    );
		    $rerecord = false;
		}

	    sayInt($test4_prompts."Record2.wav");

	   	$path = $test4_recordings."Q".$question_id.".wav";
		sayInt($path);

	    $result = gatherInput( $test4_prompts."Record3.wav", array(
									"choices" => "[1 DIGITS]",
									"mode" => 'dtmf',
									"bargein" => true,
									"repeat" => 2,
									"timeout"=> 10,
									"onBadChoice" => "keysbadChoiceFCN",
									"onTimeout" => "ReMkeystimeOutFCN",
									"onHangup" => create_function("$event", "Prehangup()")
								)
							);

    	if ($result->value == "1") {
    		sayInt($test4_prompts."Record4.wav");
	    	$loop = false;
	    }

	    else if ($result->value == "2") {
    		$loop = true;
    		$rerecord = true;
    	}

    	else {
    		sayInt($test4_prompts."Wrongbutton.wav");
    		$loop = true;
    	}
    }

    writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");

}

function feedback() {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $callid;
	global $userid;
	global $test4_prompts;
	global $test4_recordings;
	global $test4_fb_dir;
	global $test4_scripts;

	$feedback_id = addFeedback();

	if (!$feedback_id) {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Something bad happened in ".__FUNCTION__);
		return;
	}

	$loop 	  = true;
	$rerecord = true;
	$replay   = true;

	while ($loop) {

		if ($rerecord) {
			recordAudio($test4_prompts."Mainfeedback.wav", array(
		       "beep"=>true,
		       "timeout"=>300,
		       "bargein" => false,
		       "silenceTimeout"=>4,
		       "maxTime"=>120,
		       "terminator" => "#",
		      // "recordFormat" => "audio/wav",
		       "format" => "audio/wav",
		       "recordURI" => $test4_scripts."api/add/main_feedback?feedback_id=".$feedback_id."&call_id=".$callid."&user_id=".$userid,
		        )
		    );
		    $rerecord = false;
		    $replay   = true;
		}

		if ($replay) {
			sayInt($test4_prompts."Replay.wav");
			sayInt($test4_fb_dir.$feedback_id.".wav");
			$replay = false;
		}

	    $result = gatherInput( $test4_prompts."Record3.wav", array(
									"choices" => "[1 DIGITS]",
									"mode" => 'dtmf',
									"bargein" => true,
									"repeat" => 2,
									"timeout"=> 10,
									"onBadChoice" => "keysbadChoiceFCN",
									"onTimeout" => "ReMkeystimeOutFCN",
									"onHangup" => create_function("$event", "Prehangup()")
								)
							);

    	if ($result->value == "1") {

    		sayInt($test4_prompts."Shukriya1.wav");

    		$more_loop = true;

    		while ($more_loop) {

    			$result = gatherInput( $test4_prompts."Morefeedback.wav",  array(
										"choices" => "[1 DIGITS]",
										"mode" => 'dtmf',
										"bargein" => true,
										"repeat" => 2,
										"timeout"=> 10,
										"onBadChoice" => "keysbadChoiceFCN",
										"onTimeout" => "keystimeOutFCN",
										"onHangup" => create_function("$event", "Prehangup()")
									)
								);

			   	if ($result->value == "1") {
		    		$feedback_id = addFeedback();
		    		$more_loop= false;
		    		$loop 	  = true;
		    		$rerecord = true;
		    	}
		    	else if ($result->value == "2") {
		    		$more_loop= false;
		    		$loop 	  = false;
		        }else{
		        	sayInt($test4_prompts."Wrongbutton.wav");
		        	$more_loop= true;
		        }
    		}
    	}

    	else if ($result->value == "2") {
    		$loop 	  = true;
    		$rerecord = true;
    	}
    	else {
    		sayInt($test4_prompts."Wrongbutton.wav");
    		$loop 	  =  true;
    		$rerecord = false;
    	}
	}

    writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");
}

function caseQuiz(){

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");


	global $test4_prompts;
	global $test4_recordings;
	global $test4_scripts;

	$case_questions = getCaseQuestions();

	if (!$case_questions) {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Something bad happened in ".__FUNCTION__);
		return;
	}

    sayInt($test4_prompts."Caseprompt1.wav ");

	$innerloop = true; $case_id = 1;

	while($innerloop && $case_id <= sizeof($case_questions)) {

		$case_question_id 	 = $case_questions[$case_id-1]['case_question_id'];
		$correct_answer   	 = $case_questions[$case_id-1]['correct_answer'];
		$options	   		 = $case_questions[$case_id-1]['options'];

		setLog(getActionID("listen_case"), $case_id);

		sayInt($test4_recordings."Case".$case_id.".wav");

		$cqprompt = $test4_recordings."Case".$case_id."Question".".wav ".$test4_recordings."Case".$case_id."option1".".wav ".$test4_recordings."Case".$case_id."option2".".wav ".$test4_recordings."Case".$case_id."option3".".wav " ;

		$result = gatherInput( $cqprompt, array(
								"choices" => "[1 DIGITS]",
								"mode" => 'dtmf',
								"bargein" => true,
								"repeat" => 2,
								"timeout"=> 10,
								"onBadChoice" => "keysbadChoiceFCN",
								"onTimeout" => "ReMkeystimeOutFCN",
								"onHangup" => create_function("$event", "Prehangup()")
							)
						);

		$opt_arr = array();

		for ($i = 1 ; $i <= intval($options); $i++) {
			$opt_arr[] = $i;
		}

		if (in_array(intval($result->value), $opt_arr)) {

			setCaseAnswer($case_id,$result->value);

			if (intval($result->value) == intval($correct_answer))  {
				sayInt($test4_prompts."applause_effect.wav");
	    	} else {				//wrong answer
	    		sayInt($test4_prompts."wrong_effect.wav");
	    		sayInt($test4_prompts."Sahi.wav");
			}

			$more_info_loop = true;
			while ($more_info_loop) {

				sayInt($test4_recordings."Case".$case_id."True".".wav");

				$result = gatherInput( $test4_prompts."Caseprompt3.wav", array(
										"choices" => "[1 DIGITS]",
										"mode" => 'dtmf',
										"bargein" => true,
										"repeat" => 2,
										"timeout"=> 10,
										"onBadChoice" => "keysbadChoiceFCN",
										"onTimeout" => "ReMkeystimeOutFCN",
										"onHangup" => create_function("$event", "Prehangup()")
									)
								);

				if ($result->value == "1") 						{
	    			$more_info_loop = true;
	    		} elseif ($result->value == "2")  { //next

	    			$case_id++;

	    			if ($case_id <= sizeof($case_questions)) {
	    				sayInt($test4_prompts."Aglacase.wav");
	    				$more_info_loop = false;
	    				$innerloop		= true;
	    			}else{
	    				$more_info_loop = false;
	    				$innerloop		= false;
	    				$loop 			= true;
	    				sayInt($test4_prompts."Nomorequestion.wav");
	    			}
	    		} elseif ($result->value == "3") {							//back to main prompt2
					$more_info_loop = false;
    				$innerloop		= false;
    				$loop 			= false;
	    		} else {
	    			sayInt($test4_prompts."Wrongbutton.wav");
					$more_info_loop = true;
				}
			}

		} else  {
			sayInt($test4_prompts."Wrongbutton.wav");
			$innerloop = true;
		}
	}

	 writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");
}

function listenOwnQuestion() {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

    global $callid;
	global $userid;
	global $test4_prompts;
	global $test4_scripts;
	global $test4_recordings;

	if ( $questions = getQuestions(getActionID("listen_oq"), 1, 1) ) {

		$result = gatherInput( $test4_prompts."Return1.wav", array(
				"choices" => "[1 DIGITS]",
				"mode" => 'dtmf',
				"bargein" => true,
				"repeat" => 3,
				"timeout"=> 10,
				"onBadChoice" => "keysbadChoiceFCN",
				"onTimeout" => "ReMkeystimeOutFCN",
				"onHangup" => create_function("$event", "Prehangup()")
			)
		);

		if ($result->value == "1") {
			// pass
		} else if ($result->value == "2") {
			$questions = getQuestions(getActionID("listen_oq"), 1, 0);
		}
	}else
		$questions = getQuestions(getActionID("listen_oq"), 1, 0);

	if (!$questions) {
		sayInt($test4_prompts."Noquestion.wav");
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Something bad happened in ".__FUNCTION__);
		return;
	}

	$qid  = 0;
	$loop = true;

	$play = true;

	while($loop) {

		if ($qid >= sizeof($questions)) {
			sayInt($test4_prompts."Nomorequestion.wav");
			break;
		}

		$question_id = $questions[$qid]['question_id'];
		$answer_id   = $questions[$qid]['answer_id'];

		if ($play) {

			if( !isset($question_id) || (isset($question_id) && !$question_id) ) {

				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "QID not set, check API!");
				sayInt($test4_prompts."Question2.wav");
				$loop = false;

			} else if( isset($answer_id) && isset($question_id) && $answer_id && $question_id ) {

				setLog(getActionID("listen_oq"), $question_id);

				sayInt($test4_prompts."Yourquestion.wav ".$test4_recordings."Q".$question_id.".wav ".$test4_prompts."Jawaab.wav ".$test4_recordings."A".$answer_id.".wav");

			} else if( !isset($answer_id) || (isset($answer_id) && !$answer_id) ) {

				setLog(getActionID("listen_oq"), $question_id);

				sayInt($test4_prompts."Yourquestion.wav ".$test4_recordings."Q".$question_id.".wav ".$test4_prompts."Question3.wav");
				sayInt($test4_prompts."Question3.wav");
			}

			$play = false;
		}

		$result = gatherInput( $test4_prompts."Answer2.wav", array(
				"choices" => "[1 DIGITS]",
				"mode" => 'dtmf',
				"bargein" => true,
				"repeat" => 3,
				"timeout"=> 10,
				"onBadChoice" => "keysbadChoiceFCN",
				"onTimeout" => "ReMkeystimeOutFCN",
				"onHangup" => create_function("$event", "Prehangup()")
			)
		);

		if ($result->value == "1") { //repeat
			$loop = true;
			$play = true;
		}

		else if ($result->value == "2") { 	// next
			$qid++;
			$loop = true;
			$play = true;
		}

		else if ($result->value == "5")  {      //back to main prompt
			$loop  = false;
		}
		else if ($result->value == "4") {
			forwardQuestion($question_id);
			$loop  = true;
			$play = false;
		}
		else if ($result->value == "3") { // report
			setResponse($question_id, 5);
		}
		else {
			sayInt($test4_prompts."Wrongbutton.wav");
			$loop = true;
		}
	}

    writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");

}

function allQuestions() {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

    global $callid;
	global $userid;
	global $test4_prompts , $test4_recordings;
	global $test4_scripts;

	if ( $questions = getQuestions(getActionID("listen_aq"), 0, 1) ) {

		$result = gatherInput( $test4_prompts."Return1.wav", array(
				"choices" => "[1 DIGITS]",
				"mode" => 'dtmf',
				"bargein" => true,
				"repeat" => 3,
				"timeout"=> 10,
				"onBadChoice" => "keysbadChoiceFCN",
				"onTimeout" => "ReMkeystimeOutFCN",
				"onHangup" => create_function("$event", "Prehangup()")
			)
		);

		if ($result->value == "1") {
			// pass
		} else if ($result->value == "2") {
			$questions = getQuestions(getActionID("listen_aq"), 0, 0);
		}
	}else
		$questions = getQuestions(getActionID("listen_aq"), 0, 0);

	if (!$questions) {
		sayInt($test4_prompts."Noquestion.wav");
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Something bad happened in ".__FUNCTION__);
		return;
	}

	sayInt($test4_prompts."Answer1.wav");

	$qid  = 0;
	$loop = true;
	$play = true;

    while ($loop) 	{

		if ($qid >= sizeof($questions)) {
			sayInt($test4_prompts."Nomorequestion.wav");
			break;
		}

		$question_id 	 = $questions[$qid]['question_id'];
		$answer_id       = $questions[$qid]['answer_id'];
		$listen_before   = $questions[$qid]['listen_before'];
		$like            = $questions[$qid]['like'];
		$dislike         = $questions[$qid]['dislike'];
		$report          = $questions[$qid]['report'];

		if(isset($question_id) && !$question_id){
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__." - Question ID is null! Iter: ".$qid);
			break;
		} else if(isset($question_id) && isset($answer_id) && $answer_id && $question_id) {

			setLog(getActionID("listen_aq"), $question_id);

			if ($play) {
				sayInt($test4_prompts."Sawaal.wav ".$test4_recordings."Q".$question_id.".wav ".$test4_prompts."Jawaab.wav ".$test4_recordings."A".$answer_id.".wav");
		 		$play = false;
			}

	 		$result = gatherInput( $test4_prompts."Answer2.wav", array(
									"choices" => "[1 DIGITS]",
									"mode" => 'dtmf',
									"bargein" => true,
									"repeat" => 2,
									"timeout"=> 10,
									"onBadChoice" => "keysbadChoiceFCN",
									"onTimeout" => "ReMkeystimeOutFCN",
									"onHangup" => create_function("$event", "Prehangup()")
								)
							);

		    if ($result->value == "1") {
		    	$loop = true;
		    	$play = true;
		    } else if ($result->value == "2") {
	    		$qid++;
	    		$loop = true;
	    		$play = true;
	    		continue;
	    	}
	    	/* else if (in_array($result->value, array("3","4","5"))) {
	    		if ($like) {
	    			sayInt($test4_prompts."Alreadyliked.wav");
	    		} else if ($dislike) {
	    			sayInt($test4_prompts."Alreadydisliked.wav");
	    		} else if ($report) {
	    			sayInt($test4_prompts."Alreadyreported.wav");
	    		} else {
	    			if ($result->value == "3") {
			    		sayInt($test4_prompts."Answer3.wav");
			    		$like = $questions[$qid]['like'] = true;
		    		} else if ($result->value == "4") {
			    		sayInt($test4_prompts."Answer4.wav");
			    		$dislike = $questions[$qid]['dislike'] = true;
		    		} else if ($result->value == "5") {
			    		sayInt($test4_prompts."Answer4.wav");
			    		$report = $questions[$qid]['report'] = true;
		    		}
			    	setResponse($question_id, $result->value);
		    		$loop = true;
	    		}
	    	} */
	    	else if($result->value == "4") {
	    		forwardQuestion($question_id);
	    		$loop = true;
	    		$play = false;
	    	} else if($result->value == "3") {
	    		setResponse($question_id, 5);
	    		sayInt($test4_prompts."Answer4.wav");
	    		//user_comment($question_id);
	    	} else if($result->value == "5") {
	    		$loop = false;
	    	} else {
	    		sayInt($test4_prompts."Wrongbutton.wav");
	    		$loop = true;
	    	}
	 	} else {
	 		$qid++;
			$loop = true;
			continue;
		}
	}

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");
}

function playComments($question_id){

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

    global $callid;
	global $userid;
	global $test4_prompts , $test4_recordings, $test4_user_comments;
	global $test4_scripts;

	$comments = gettest4_user_comments($question_id);

	if (!$comments) {
		sayInt($test4_prompts."Nocomment.wav");
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Something bad happened in ".__FUNCTION__);
		return;
	}

	$i    = 0;
	$loop = true;
	$play = true;

	while($loop) {

		if ($i >= sizeof($comments)) {
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "No more comments in ".__FUNCTION__);
			sayInt($test4_prompts."Nomorecomment.wav");
			break;
		}

		$cid = $comments[$i]["q_feedback_id"];

		if ($play) {
			sayInt($test4_user_comments.$cid.".wav");
			$play = false;
		}

		$result = gatherInput( $test4_prompts."Answer5.wav", array(
							"choices" => "[1 DIGITS]",
							"mode" => 'dtmf',
							"bargein" => true,
							"repeat" => 2,
							"timeout"=> 10,
							"onBadChoice" => "keysbadChoiceFCN",
							"onTimeout" => "ReMkeystimeOutFCN",
							"onHangup" => create_function("$event", "Prehangup()")
						)
					);
		if ($result->value == "1") {
			$play  = true;
		} else if ($result->value == "2") {
			user_comment($question_id);
		} else if ($result->value == "3") {
			$i++;
		} else if ($result->value == "4") {
			$loop = false;
		}
	}

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");
}

function user_comment($question_id) {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

    global $callid;
	global $userid;
	global $test4_prompts , $test4_recordings, $test4_user_comments;
	global $test4_scripts;

	$comment_id = createComment($question_id);

	if (!$comment_id) {
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Something bad happened in ".__FUNCTION__);
		return;
	}

	$loop 	= true;
	$record = true;
	$replay = true;

	while ($loop)  {

		if ($record) {

			recordAudio($test4_prompts."Comment.wav", array(
			       "beep"=>true,
			       "timeout"=>300,
			       "bargein" => false,
			       "silenceTimeout"=>4,
			       "maxTime"=>30,
			       "terminator" => "#",
			      // "recordFormat" => "audio/wav",
			       "format" => "audio/wav",
			       "recordURI" => $test4_scripts."api/add/question_feedback?q_feedback_id=".$comment_id."&question_id=".$question_id."&user_id=".$userid."&call_id=".$callid,
	        	)
	    	);
			$record = false;
	    	$replay = true;
		}

		if ($replay) {
			sayInt($test4_prompts."Record2.wav");
			$path = $test4_user_comments.$comment_id.".wav";
			sayInt($path);
			$replay = false;
		}


		$result = gatherInput( $test4_prompts."Record3.wav ", array(
					"choices" => "[1 DIGITS]",
					"mode" => 'dtmf',
					"bargein" => true,
					"repeat" => 2,
					"timeout"=> 10,
					"onBadChoice" => "keysbadChoiceFCN",
					"onTimeout" => "ReMkeystimeOutFCN",
					"onHangup" => create_function("$event", "Prehangup()")
				)
			);

		if ($result->value == "1") {
			sayInt($test4_prompts."Shukriya1.wav");
			$loop = false;
		} else if ($result->value == "2") {
			$record = true;
			$loop 	= true;
		} else {
			sayInt($test4_prompts."Wrongbutton.wav");
			$loop = true;
		}
	}
}

function user_name($question_id) {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

    global $callid;
	global $userid;
	global $test4_prompts , $test4_recordings, $test4_user_comments, $SA_prompts;
	global $test4_scripts;

	$loop 	= true;
	$record = true;
	$replay = true;

	while ($loop)  {

		if ($record) {

			recordAudio($SA_prompts."Forward3.wav ", array(
			       "beep"=>true,
			       "timeout"=>10,
			       "bargein" => false,
			       "silenceTimeout"=>3,
			       "maxTime"=>10,
			       "terminator" => "#",
			      // "recordFormat" => "audio/wav",
			       "format" => "audio/wav",
			       "recordURI" => $test4_scripts."api/username",
	        	)
	    	);
			$record = false;
	    	$replay = true;
		}

		if ($replay) {
			sayInt($test4_prompts."Record2.wav");
			$path = $test4_recordings."U".$userid.".wav";
			sayInt($path);
			$replay = false;
		}


		$result = gatherInput( $test4_prompts."Record3.wav ", array(
					"choices" => "[1 DIGITS]",
					"mode" => 'dtmf',
					"bargein" => true,
					"repeat" => 2,
					"timeout"=> 10,
					"onBadChoice" => "keysbadChoiceFCN",
					"onTimeout" => "ReMkeystimeOutFCN",
					"onHangup" => create_function("$event", "Prehangup()")
				)
			);

		if ($result->value == "1") {
			$loop = false;
		} else if ($result->value == "2") {
			$record = true;
			$loop 	= true;
		} else {
			sayInt($test4_prompts."Wrongbutton.wav");
			$loop = true;
		}
	}
}

function forwardQuestion($question_id) {

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $SA_prompts;
	global $ReM_prompts;
	global $userid;
	global $callid;

	$prompt = $SA_prompts."Forward1.wav";

	$loop = true;

	$number = "";

	while($loop){
		$NumberList = gatherInput($prompt, array(
				        "choices" => "[11 DIGITS]",
						"mode" => 'dtmf',
						"bargein" => true,
						"repeat" => 3,
						"timeout"=> 30,
						"interdigitTimeout"=> 20,
						"onBadChoice" => "keysbadChoiceFCN",
						"onTimeout" => "ReMkeystimeOutFCN",
						"terminator" => "#",
						"onHangup" => create_function("$event", "Prehangup()")
				    )
				);

		if($NumberList->name == 'choice'){

			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Friend's phone number entered: ".$NumberList->value.". Now playing it.");

			$num12 = str_split($NumberList->value);

			for($index1 = 0; $index1 < count($num12); $index1 += 1){
				if($index1 == 0){
					$fileName = $num12[$index1].'.wav';
					$numpath = $ReM_prompts.$fileName;
				}
				else{
					$fileName = $num12[$index1].'.wav';
					$numpath = $numpath . "\n" . $ReM_prompts.$fileName;
				}
			}

			sayInt($SA_prompts."Forward8.wav ");
			$presult = sayInt($numpath);

			if ($presult->name == 'choice'){
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "User pressed ".$presult->value." to skip ".$numpath.".");
			}

			$choice = gatherInput(
					$SA_prompts."Forward2.wav ", array(
			        "choices" => "[1 DIGITS],*,#",
					"mode" => 'dtmf',
					"bargein" => true,
					"repeat" => 3,
					"timeout"=> 5,
					"onBadChoice" => "keysbadChoiceFCN",
					"onTimeout" => "ReMkeystimeOutFCN",
					"onHangup" => create_function("$event", "Prehangup()")
		        )
		    );

			if($choice->value=="1"){
				$number = $NumberList->value;
				$forward_id = createForward($question_id, PhToKeyAndStore($number, $userid));
				if ($forward_id) {
					user_name($question_id);
					if ($userid == 3566) {
						if(makeNewReq($forward_id, 999888, $callid, "REM-DEL", PhToKeyAndStore($number, $userid), "Pending", $GLOBALS["SystemLanguage"], $GLOBALS["MessageLanguage"], $GLOBALS["channel"])){
							sayInt($SA_prompts."Forward6.wav ");
							$loop   = false;
						}else
							$loop   = true;
					}else{
						if(makeNewReq($forward_id, 999888, $callid, "REM-DEL", PhToKeyAndStore($number, $userid), "Pending", $SystemLanguage, $MessageLanguage, $channel)){
							sayInt($SA_prompts."Forward6.wav ");
							$loop   = false;
						}else
							$loop   = true;
					}
				}else
					$loop   = true;
				$loop = false;
			}else if($choice->value=="2"){
				$loop = true;
			}
		}
		else{
			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Timed out. No number entered. Now hanging up.");
			$FriendsNumber = 'false';
			//Prehangup();
		}
	}
}

function T4Delivery($del_id){

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");

	global $test4_prompts, $test4_recordings, $test4_scripts, $callid, $userid, $SA_prompts;

	if ($del_id) {
		$params = T4_GetDeliveryParams($del_id);
	}else if(isset($_REQUEST["recIDtoPlay"])) {
		$del_id = $_REQUEST["recIDtoPlay"];
		$params = T4_GetDeliveryParams($del_id);
	}else{
		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " -- no way to fetch delivery details!");
		return;
	}

	//ob_start();
	//var_dump($params);
	//$vardump = ob_get_clean();

	//writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . "DUMP! ".$vardump);

	//sayInt($test4_prompts."Messagereceived1.wav");

	if ($params) {

		//writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " inside!");

		$id     = $params["file_id"];
		$fuid   = $params["fuid"];
		$ans_id = $params['ans_id'];

		//writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " Params: id: ".$id." = fuid: ".$fuid." = ans: ".$ans_id );

		sayInt($test4_prompts."Messagereceived1.wav");
		sayInt($test4_recordings."U".$fuid.".wav");
		sayInt($test4_prompts."Messagereceived2.wav");

		$loop = true;


		while($loop){

			sayInt($test4_prompts."Sawaal.wav ".$test4_recordings."Q".$id.".wav ".$test4_prompts."Jawaab.wav ".$test4_recordings."A".$ans_id.".wav");

			$result = gatherInput($test4_prompts."Messageoptions2.wav ", array(
					"choices" => "[1 DIGITS]",
					"mode" => 'dtmf',
					"bargein" => false,
					"repeat" => 2,
					"timeout"=> 10,
					"onBadChoice" => "keysbadChoiceFCN",
					"onTimeout" => "ReMkeystimeOutFCN",
					"onHangup" => create_function("$event", "Prehangup()")
				)
			);

			if($result->value == "1"){
				continue;
			}

			else if($result->value == "2"){
				forwardQuestion($id);
			}

			else if($result->value == "3"){
				Test4Menu();
			}
		}
	}

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " returning.");
}

/*>>>>***********************************************************************************************<<<<*/
/*>>>>****************************************  Test 4 END  *****************************************<<<<*/
/*>>>>***********************************************************************************************<<<<*/



///////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// Error Handlers /////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////

function callFailureFCN($callid){
	global $fh;
	global $oreqid;
	global $callid;
	global $thisCallStatus;

	$thisCallStatus = "Failed";
	$status = "unfulfilled";
	updateRequestStatus($oreqid, $status);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Call Failed.");

	Prehangup();
}

function errorFCN($callid){
	global $fh;
	global $oreqid;
	global $callid;
	global $thisCallStatus;

	$thisCallStatus = "Error";
	$status = "unfulfilled";
	updateRequestStatus($oreqid, $status);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Call Error.");

	Prehangup();
}

function timeOutFCN($callid){
	global $fh;
	global $oreqid;
	global $callid;
	global $thisCallStatus;

	$thisCallStatus = "TimedOut";
	$status = "unfulfilled";
	updateRequestStatus($oreqid, $status);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Call Timed Out.");

	Prehangup();
}

function busyFCN($callid){
	global $fh;
	global $oreqid;
	global $callid;
	global $thisCallStatus;

	$thisCallStatus = "Busy";
	$status = "unfulfilled";
	updateRequestStatus($oreqid, $status);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "Destination number is busy.");

	Prehangup();
}

function keystimeOutFCN($event){
	global $polly_prompts_dir;

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing the timed-out prompt.");
	sayInt($polly_prompts_dir."Nobutton.wav");
}

function ReMkeystimeOutFCN($event){
	global $test1_prompts;

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing the timed-out prompt.");
	sayInt($test1_prompts."Nobutton.wav");
}

function keysbadChoiceFCN($event){
	global $polly_prompts_dir;

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Playing the invalid key prompt.");
	sayInt($polly_prompts_dir."Wrongbutton.wav");
}

///////////////////////////////////////////////////////////////////////////////////////
/////////////////// Call-Session processing/logging Functions /////////////////////////
///////////////////////////////////////////////////////////////////////////////////////

function sendLogs(){
	global $database_dir;
	global $callid;
	global $logEntry;
	global $tester;//change testing

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", __FUNCTION__ . " called.");

	$LogScript = $database_dir."createLogs.php";

	$arr = explode('^', chunk_split($logEntry, 1000, '^'));		// Send logs in chunks of 1000 characters
	$i=0;
	$len = count($arr);
	while($i < $len){
		$datatopost = array (
			"callid" => $callid,
			"data" => $arr[$i]
		);

		$ch = curl_init ($LogScript);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$returndata = curl_exec ($ch);

		$i++;
	}
}

function createLogFile($id){
	global $log_file_path;

	if(!file_exists($log_file_path."\\".date('Y'))){
		mkdir($log_file_path."\\".date('Y'));
	}
	if(!file_exists($log_file_path."\\".date('Y')."\\".date('m'))){
		mkdir($log_file_path."\\".date('Y')."\\".date('m'));
	}
	if(!file_exists($log_file_path."\\".date('Y')."\\".date('m')."\\".date('d'))){
		mkdir($log_file_path."\\".date('Y')."\\".date('m')."\\".date('d'));
	}

	$now = new DateTime;
	$timestamp = $now->format('Y-m-d_H-i-s');
	$logFile = $log_file_path."\\".$now->format('Y')."\\".$now->format('m')."\\".$now->format('d')."\\log_".$timestamp."_".$id.".txt"; // Give a caller ID based name

	$handle = fopen($logFile, 'a');
	/*echo "HAH!:";
	var_dump($handle);*/
	return $handle;
}

function writeToLog($id, $handle, $tag, $str){
	global $deployment;
	global $logEntry;
	global $tester;
	global $userid;


	$writeToTropoLogs = "true";
	$spch1 = "%%";
	$spch2 = "$$";
	$del = "~";
	$colons = ":::";
	// From Apr 01, 2015: tag could be L0: System level, L1: Mixed interest, L2: User Experience
	if($tag!= 'L0' && $tag!= 'L1' && $tag!= 'L2'){
		$tag = 'L1';
	}
	if($id == "" || $id == 0){
		$id = 'UnAssigned';
	}


	//$string = $spch1 . $spch2 . $del . $id . $del . $del . date('D_Y-m-d_H-i-s') . $del . $tag . $colons . $del . $str . $spch2 . $spch1;
    // replaced the above with the following to overcome the date bug in tropo cloud. Details in email. Dec 18, 2013
    $now = new DateTime;
	$actualLogLine = $deployment . $del . $id . $del . $del . $now->format('D_Y-m-d_H-i-s') . $del . $tag . $colons . $del . $str;
    $string = $spch1 . $spch2  . $del . $actualLogLine . $spch2 . $spch1;

	$logEntry = $logEntry . $actualLogLine . $spch1 . $spch2;
}

function StartUpFn(){
	global $userid;
	global $oreqid;
	global $scripts_dir;

	$status = "InProgress";
	updateCallStatus($GLOBALS['callid'], $status);
	$status = "fulfilled";
	updateRequestStatus($oreqid, $status);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Call Answered.");
}

function Prehangup(){
	global $callid;
	global $fh;
	global $thisCallStatus;
	global $currentCall;
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " was called.");
	/////////////////////
	updateWaitingDlvRequests($callid);
	updateCallStatus($callid, $thisCallStatus);

	$sessID = getSessID();
	$calledID = calledID();
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "calledID: $calledID , SessionID: $sessID");
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Hanging Up. Call ended for callid: ".$callid);
	/////////////////////
	markCallEndTime($callid);
	sendLogs();
	//fclose($fh);
	hangupFT();
	exit(0);
}

function Prehangupunknown(){
	global $callid;
	global $fh;
	global $thisCallStatus;
	global $currentCall;
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " was called.");

	$sessID = getSessID();
	$calledID = calledID();
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", "calledID: $calledID , SessionID: $sessID");
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Hanging Up. Call ended for callid: ".$callid);

	sendLogs();
	hangupFT();
	exit(0);
}

function isThisCallActive(){
	global $uuid;
	global $fp;
	global $callid;
	global $thisCallStatus;
	global $fh;

	$cmd = "api lua isActive.lua ".$uuid;
	$response = event_socket_request($fp, $cmd);
	$retVal =  trim($response);
	writeToLog($callid, $fh, "isActive", "Is the current call active? ".$retVal.". Hanging up if the call is not active.");
	if(strcmp($retVal,"false") == 0 ){
		Prehangup();
	}
	return $retVal;
}

function calledID(){
	global $fp;
	global $uuid;

	$cmd = "api lua getCalledID.lua ".$uuid;
	$response = event_socket_request($fp, $cmd);
	return $response;
}

function getSessID(){
	global $uuid;
	return $uuid;
}

function getCallerID(){
	global $uuid;
	global $fp;
	global $fh;

	$cmd = "api lua getCallerID.lua ".$uuid;
	$response = event_socket_request($fp, $cmd);
	return $response;
}

function answerCall(){
	global $uuid;
	global $fp;
	global $fh;

	$cmd = "api lua answer.lua ".$uuid;//first character is a null (0)
	$response = event_socket_request($fp, $cmd);
	return $response;
}

function rejectIfCallInactive(){
	if(isThisCallActive()=="true")
		return;
	Prehangup();
}

function rejectCall($app) {
	global $uuid;
	global $fp;
	global $fh;

    $cmd = "api lua reject.lua ".$uuid;
	$response = event_socket_request($fp, $cmd);
	Prehangup();
}

//////////////////////////////////////////////////////////////////////////////////////
///////////////////////////// DB Access Functions ////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

function makeNewRec($callid){
	global $database_dir;
	$url = $database_dir."New_Rec.php?callid=$callid";
	$result = doCurl($url);
	return $result;
}

function callRecURI($url){
	$result = doCurl($url);
	return $result;
}

function makeNewCall($reqid, $phno, $status, $calltype, $chan){
	global $database_dir;
	$url = $database_dir."New_Call.php?reqid=$reqid&phno=$phno&calltype=$calltype&status=$status&ch=$chan";
	$result = doCurl($url);
	return $result;
}

function makeNewSess($reqid, $type, $ph){
	global $database_dir;
	global $calltype;
	$url = $database_dir."New_Sess.php?calltype=$type&reqid=$reqid&ph=$ph";
	$result = doCurl($url);
	return $result;
}

function makeNewReq($recid, $effect, $callid, $reqtype, $phno, $status, $syslang, $msglang, $ch){
	global $database_dir;
	global $userid;
	global $testcall;
	$url = $database_dir."New_Req.php?recid=$recid&effect=$effect&callid=$callid&reqtype=$reqtype&from=$userid&phno=$phno&status=$status&syslang=$syslang&msglang=$msglang&testcall=$testcall&ch=$ch";
	$result = doCurl($url);
	return $result;
}

function createMissedCall($recid, $effect, $callid, $reqtype, $phno, $status, $syslang, $msglang, $ch, $destinationAppId){
	global $database_dir;
	global $userid;
	global $testcall;
	global $tester;
	$url = $database_dir."New_Req.php?recid=$recid&effect=$effect&callid=$callid&reqtype=$reqtype&from=$destinationAppId&phno=$phno&status=$status&syslang=$syslang&msglang=$msglang&testcall=$testcall&ch=$ch";
	$result = doCurl($url);
	return $result;
}

function makeNewFB($fbtype, $callid){
	global $database_dir;
	$url = $database_dir."New_FB.php?fbtype=$fbtype&callid=$callid";
	$result = doCurl($url);
	return $result;
}

function markCallEndTime($callid){
	global $database_dir;
	$url = $database_dir."Update_Call_Endtime.php?callid=$callid";
	$result = doCurl($url);
	return $result;
}

function updateCallStatus($callid, $status){
	global $database_dir;
	$url = $database_dir."Update_Call_Status.php?callid=$callid&status=$status";
	$result = doCurl($url);
	return $result;
}

function updateRequestStatus($reqid, $status){
	global $database_dir;
	global $sipProvider;
	$url = $database_dir."Update_Request_Status.php?reqid=$reqid&status=$status&ch=$sipProvider";
	$result = doCurl($url);
	return $result;
}

function updateWaitingDlvRequests($id){
	global $database_dir;
	$url = $database_dir."Update_Waiting_DLV_Reqs.php?rcallid=$id";
	$result = doCurl($url);
	return $result;
}

function gaveFeedBack($ph){
	global $database_dir;
	global $callTableCutoff;
	$url = $database_dir."gave_feedback.php?ph=$ph&cutoff=$callTableCutoff";
	$result = doCurl($url);
	return $result;
}

function getPhNo(){
	global $database_dir;
	global $ocallid;
	$url = $database_dir."GetPhNo.php?callID=$ocallid";
	$result = doCurl($url);
	return $result;
}

function getPreferredLangs($id){
	global $database_dir;
	$url = $database_dir."GetPreferredLangs.php?id=$id";
	$result = doCurl($url);
	return $result;
}

function getCountryCode($ph){
	global $database_dir;
	$url = $database_dir."CountryCode.php?ph=$ph";
	$result = doCurl($url);
	if($result != "Not found."){
		return explode(" - ", $result)[0];
	}
	return "";
}

function phoneNumBeforeAndAfterConditioning($before, $after, $type, $sender){
	global $callid;
	global $database_dir;
	$url = $database_dir."Update_Conditioned_PhNo.php?callid=$callid&uncond=$before&cond=$after&type=$type&sender=$sender";
	$result = doCurl($url);
	return $result;
}

function doesFileExist($fname){
	global $scripts_dir;
	$url = $scripts_dir."doesFileExist.php?fname=$fname";
	$result = doCurl($url);
	return $result;
}

function whatWasThesipProviderOfTheOriginalRequest($OrigReqID){
	global $database_dir;
	$url = $database_dir."getReqsipProvider.php?id=$OrigReqID";
	$result = doCurl($url);
	return $result;
}

//////////////////////////////////////////////////////////////////////////////////////
///////////////////////////// Cutoff based Functions /////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

function searchCalls($ph){
	global $database_dir;
	global $calltype;
	global $callTableCutoff;
	$url = $database_dir."search_calls.php?ph=$ph&type=$calltype&cutoff=$callTableCutoff";
	$result = doCurl($url);
	return $result;
}

function searchPh($ph, $application){
	global $database_dir;
	global $callTableCutoff;
	$url = $database_dir."search_phno.php?ph=$ph&app=$application&cutoff=$callTableCutoff";
	$result = doCurl($url);
	return $result;
}

function searchCallsReq($ph){
	global $database_dir;
	global $calltype;
	global $reqTableCutoff;
	$url = $database_dir."search_calls_hist.php?ph=$ph&type=$calltype&cutoff=$reqTableCutoff";
	$result = doCurl($url);
	return $result;
}

function updateCallsReq($ph){
	global $database_dir;
	global $reqTableCutoff;
	$url = $database_dir."update_calls_hist.php?ph=$ph&cutoff=$reqTableCutoff";
	$result = doCurl($url);
	return $result;
}

function getcallTableCutoff($days){
	global $database_dir;
	$url = $database_dir."getcallTableCutoff.php?days=$days";
	$result = doCurl($url);
	return $result;
}

function getreqTableCutoff($days){
	global $database_dir;
	global $userid;
	$url = $database_dir."getreqTableCutoff.php?days=$days";
	$result = doCurl($url);
	return $result;
}

//////////////////////////////////////////////////////////////////////////////////////
/////// functions to encode, decode, store, validate and clean phone numbers /////////
//////////////////////////////////////////////////////////////////////////////////////

function checkIfPhNoValid($phno){

	$phno = rtrim($phno, "*");
	$phno = ltrim($phno, "*");
	$phno = rtrim($phno, "#");

	if(sizeof($phno) < 11 || sizeof($phNo) > 15)
		return false;

	$phno = str_replace("+", "00", $phno);

	if(is_numeric($phno)){
		if ($phno[0] == 0 || $phno[0] == "0") {
			return true;
		}
		return true;
	}
	return false;
}

function conditionPhNo($phno, $type){
	global $useridUnEnc;
	global $countryCode;
	$returnNumber = $phno;
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", __FUNCTION__ . " is called with ph: $phno and type: $type");

	if($type == "Missed_Call" || $type == "EMissed_Call" || $type == "AMissed_Call" || $type == "MMissed_Call" || $type == "BMissed_Call" || $type == "Unsubsidized" || $type == "FCMissed_Call"){
		if(substr($returnNumber, 0, 1) == '1'){										// 1 got prepended by mistake
			$returnNumber = substr($returnNumber, 1, strlen($returnNumber)-1);		// remove it
		}
		else if(strlen($phno) <= 10){						//local US	e.g. 4122677909
			$returnNumber = "1".$phno;						// 1 is to say that missed call is coming in from US
		}
		else{												// International
			$returnNumber = $phno;
		}
	}
	else if($type == "Delivery" || $type == "EDelivery" || $type == "BDelivery" || $type == "ADelivery" || $type == "MDelivery" || $type == "FCDelivery"){
		$returnNumber = $phno;
		if(substr($returnNumber, 0, 2) == '00'){			// A non-US sender entered an Intl. number with country code
			$returnNumber = ltrim($returnNumber, 0);
		}
		else if(substr($returnNumber, 0, 3) == '011'){		// A US sender entered an Intl. number with country code
			$returnNumber = substr($returnNumber, 3, strlen($returnNumber)-3);
		}
		else if(substr($returnNumber, 0, 1) == '0'){					// A non-US sender entered a local number
			$returnNumber = $returnNumber;	//$countryCode . ltrim($returnNumber, 0);		// Prepend the country code of the sender
		}
		else if((strlen($returnNumber)==9) && $countryCode == '224'){	// A Guinean sender entered a local number without a 0 prefix
			$returnNumber = $countryCode . ltrim($returnNumber, 0);		// Prepend the country code of Guinea
		}
		else if((strlen($useridUnEnc) == 11) && (substr($useridUnEnc, 0, 1) == 1) && (strlen($returnNumber)==10)){	// A US sender entered a 10-dig number without country code -> Assmue it is a US number
			$returnNumber = $returnNumber; //"1".$returnNumber;
		}
		phoneNumBeforeAndAfterConditioning($phno, $returnNumber, $type, $useridUnEnc);
	}
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", __FUNCTION__ . " is returning: $returnNumber");
	return $returnNumber;
}

function PhToKeyAndStore($phno, $sender){
	global $database_dir;
	global $tester;
	$url = $database_dir."insertNewPhByMatchingFromOldTable.php?sender=$sender&ph=".$phno;
	$result = doCurl($url);
	return $result;
}

function PhToKey($ph){
	global $database_dir;
	$url = $database_dir."phToKey.php?ph=$ph";
	$result = doCurl($url);
	return $result;
}

function KeyToPh($key){
	global $database_dir;
	$url = $database_dir."keyToPh.php?key=$key";
	$result = doCurl($url);
	return $result;
}

function getPhDir(){
	global $userid;
	global $database_dir;
	$url = $database_dir."getPhDir.php?user=$userid";
	$result = doCurl($url);
	return $result;
}

function updatePhDir($phoneNumber){
	global $userid;
	global $database_dir;
	$url = $database_dir."updatePhDir.php?user=$userid&friend=$phoneNumber";
	$result = doCurl($url);
	return $result;
}

//////////////////////////////////////////////////////////////////////////////////////
///////////////////////////// Misc. Functions ////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
function doCurl($url){
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", __FUNCTION__ . " is called.");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		$result = curl_exec($ch);
		curl_close($ch);
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", __FUNCTION__ . " called with url: $url, is returning: $result");
	return $result;
}

function getFilePath($fileName, $pathOnly = "FALSE"){
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", __FUNCTION__ . " is called.");

	$fname = explode('.', $fileName);

	$FilePath = ($fname[0] - ($fname[0] % 1000));		// rounding down to the nearest 1000

	$File = $FilePath."/".$fileName;
	if($pathOnly == "TRUE"){
		$File = $FilePath . "/";
	}

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L0", __FUNCTION__ . " called with params: $fileName, $pathOnly, is returning: $File");

	return $File;
}

function createFilePath($filePath,$fileName, $pathOnly = "FALSE"){
	global $tester;

	$fname = explode('.', $fileName);
	$FilePathNew = $filePath.($fname[0] - ($fname[0] % 1000));		// rounding down to the nearest 1000
	//////fwrite($tester,$fname[0]." path of file.\n");

	if( is_dir($FilePathNew) === false )
	{
	    mkdir($FilePathNew);
	}
	$File = $FilePathNew."\\".$fileName;
	if($pathOnly == "TRUE"){
		$File = $FilePathNew . "\\";
	}
	return $File;
}
////////////////////////////////////////////// FreeSwitch Event Socket Library - Client Side //////////////////////

function event_socket_create($host, $port, $password) {
	global $fp;

   $fp = fsockopen($host, $port, $errno, $errdesc)
     or die("Connection to $host failed");
   socket_set_blocking($fp,false);


   if ($fp) {
       while (!feof($fp)) {
          $buffer = fgets($fp, 1024);
          usleep(50); //allow time for reponse
          if (trim($buffer) == "Content-Type: auth/request") {
             fputs($fp, "auth $password\n\n");
             break;
          }
       }
       return $fp;
    }
    else {
        return false;
    }
}

function event_socket_request($fp, $cmd) {

    if ($fp) {

        fputs($fp, $cmd."\n\n");
        usleep(50); //allow time for reponse

        $response = "";
        $i = 0;
        $contentlength = 0;
        while (!feof($fp)) {
           $buffer = fgets($fp, 4096);
           if ($contentlength > 0) {
              $response .= $buffer;
           }

           if ($contentlength == 0) { //if contentlenght is already don't process again
               if (strlen(trim($buffer)) > 0) { //run only if buffer has content
                   $temparray = explode(":", trim($buffer));
                   if ($temparray[0] == "Content-Length") {
                      $contentlength = trim($temparray[1]);
                   }
               }
           }

           usleep(50); //allow time for reponse

           //optional because of script timeout //don't let while loop become endless
           if ($i > 100000) { break; }

           if ($contentlength > 0) { //is contentlength set
               //stop reading if all content has been read.
               if (strlen($response) >= $contentlength) {
                  break;
               }
           }
           $i++;
        }

        return $response;
    }
    else {
      echo "no handle";
    }
}

function hangupFT(){
	global $uuid;
	global $fp;

	if(isset($_REQUEST["uuid"])){
		$cmd = "api lua hangup.lua ".$uuid;
		$response = event_socket_request($fp, $cmd);
		fclose($fp);
	}
}

function makechoices($choices){
	//types seen "[1 DIGITS], *" , "[1 DIGITS]" , "sometext(1,sometext)"
	$fchoices = "";
	if($choices[0] == '[')
	{
		//tokenizing all possible choices
		$pchoices = explode(",",$choices);
		$i = 1;

		while($pchoices[0][$i] != ' ' && $i < strlen($pchoices[0]))
		{
			$fchoices .= $pchoices[0][$i];
			 ++$i;
		}

		if(strlen($fchoices ) > 1)
		{
			$fchoices=str_replace("-","",$fchoices);
		}
		$j=1;

		while($j<count($pchoices))
		{
			$fchoices .= trim($pchoices[$j]);
			++$j;
		}

	}
	else
	{
		$i = 0;
		while($i< strlen($choices))
		{
			$j = $i;
			$cchoices = "";//choice in context


			while($choices[$j] != '(')
			{
				$j++;

			}
			$j++;

			while($choices[$j] != ')')
			{
				$cchoices .= $choices[$j];
				$j++;

			}
			$j++;
			$i=$j;
			$pchoices = explode(",",$cchoices);
			$fchoices .= $pchoices[0];


		}

	}

	return $fchoices;
}

function makeValidINput($choices,$fchoices){//to make regex for valid input freeswitch like [1 digits], * => \\d+ or 1234* => [1234]
	if($choices[0] == '[')
	{
		return "d" ;
	}
	else
	{
		$valid = '[';
		$i = 0;
		while($i<strlen($fchoices))
		{
			if($fchoices[$i]!='*' && $fchoices[$i]!='#' )
			{
				$valid .= $fchoices[$i];
			}
			$i++;
		}
		$valid .= ']';
		return $valid;
	}
}

function makeTerminators($fchoices,$terms){//making terms for freeswitch
	$termsin = "";
	$i = 0;
	$b = 0;
	while($i<strlen($fchoices))
	{
		if($fchoices[$i]=='*' || $fchoices[$i]=='#' )
		{
			$b = 1;
			$termsin .= $fchoices[$i];
		}
		$i++;
	}
	if($terms == "@" && $b == 1)
	{
		return $termsin;
	}
	else
	{
		$terms.=$termsin;
		return $terms;
	}
}

function mapChoice($choices,$fchoice){

	if($choices[0] == '[')
	{
		return $fchoice;
	}
	else
	{
		$i = 0;
		while($i< strlen($choices))
		{
			$j = $i;
			$cchoices = "";//choice in context


			while($choices[$j] != '(')
			{
				$j++;

			}
			$j++;

			while($choices[$j] != ')')
			{
				$cchoices .= $choices[$j];
				$j++;

			}
			$j++;
			$i=$j;
			$pchoices = explode(",",$cchoices);
			if($fchoice == $pchoices[0])
			{
				return $pchoices[1];
			}
		}
		return $fchoice;
	}
}

function calculateMaxDigits($choices,$fchoice){
	if($choices[0]=="[")
	{
		$i= strpos($choices,'-');
		if( $i !== false )
		{
			$subchoice = "";//the part 9-14 in [9-14 digits]
			$i=1;

			while($choices[$i]=="-" || is_numeric($choices[$i]))
			{

				$subchoice .= $choices[$i];
				$i++;

			}
			$subchoice = explode("-",$subchoice);
			$max=(int)($subchoice[1]);

		}
		else
		{
			$subchoice = "";//the part 9 in [9 digits]
			$i=1;
			while( is_numeric($choices[$i]))
			{
				$subchoice .= $choices[$i];
				$i++;
			}
			$max=(int)($subchoice);
		}
		return $max;
	}
	else
	{

		return 1;//in these kind of choices like notify(1,notify),donotnotify(2,donotnotify),*(*,*) at a time input numbers require is 1 always
	}
}

function calculateMinDigits($choices,$fchoice){
	if($choices[0]=="[")
	{
		$i= strpos($choices,'-');
		if( $i !== false )
		{
			$subchoice = "";//the part 9-14 in [9-14 digits]
			$i=1;

			while($choices[$i]=="-" || is_numeric($choices[$i]))
			{

				$subchoice .= $choices[$i];
				$i++;

			}
			$subchoice = explode("-",$subchoice);
			$min=(int)($subchoice[0]);

		}
		else
		{
			$subchoice = "";//the part 9 in [9 digits]
			$i=1;
			while( is_numeric($choices[$i]))
			{
				$subchoice .= $choices[$i];
				$i++;
			}
			$min=(int)($subchoice);
		}
		return $min;
	}
	else
	{
		return 1;//in these kind of choices like notify(1,notify),donotnotify(2,donotnotify),*(*,*) at a time input numbers require is 1 always
	}
}

function invalid($onBadCoice){//to handle if invalid key is entered for freeswitch
	global $polly_prompts_dir;

	if ( $onBadCoice == "keysbadChoiceFCN" )
	{
		return $polly_prompts_dir."Wrongbutton.wav";
	}
}

function onTimeOut($onTimeout){//to handle if timeout occured for freeswitch
	global $polly_prompts_dir;
	global $test1_prompts;
	if ( $onTimeout == "keystimeOutFCN" )
	{
		return $polly_prompts_dir."Nobutton.wav";
	}else if ( $onTimeout == "ReMkeystimeOutFCN" )
	{
		return $test1_prompts."Nobutton.wav";
	}
}

function gatherTnputFreeSwitch($toBeSaid,$invalidFS,$mindigitsFS,$maxdigitsFS,$maxattemptsFS,$timeoutFS,$bargein,$termFS,$validInput,$onTimeOutFS,$interdigitTimeout){

	global $uuid;
	global $fp;

	$output = (object) array('name' => 'choice', 'value' => '');
	$repeat = "TRUE";

	$kaho = "file_string://";
		$s=preg_split('/[ \n]/', $toBeSaid);
        for($i=0;$i<count($s);$i++)
        {
        	$j = 0;
        	if($s[0]=="")
        	{
        		$j = 1;
        	}
        	if($i>$j)
        	{
        		$kaho .= "!".$s[$i];
        	}
        	else
        	{
        		$kaho .= $s[$i];
        	}

        }

	while($repeat == "TRUE"){
		$repeat = "FALSE";
		$cmd = "api lua askGather.lua ".$uuid." ".$kaho." ".$invalidFS." ".$mindigitsFS." ".$maxdigitsFS." ".$maxattemptsFS." ".$timeoutFS." ".$termFS." ".$validInput." ".$onTimeOutFS." ".$interdigitTimeout;
		$response = event_socket_request($fp, $cmd);
		if(substr($response, 1)){
			$val = substr($response, 1);
			if($val[0]==' ' || $val[0]=='-'  )
			{
				$output->value= $val[1];
			}
			elseif($val[0]=='_')
			{
				$i = 1;
				while( $i < strlen($val))
				{
					$output->value .= $val[$i];
					$i++;
				}

			}
			else
			{
				$output->name = "not_Good_timeout_or_invalid";
				$output->value= "-";
			}
		}
		else{
			$output->name = "not_Good_timeout_or_invalid";
			$output->value= "-";
		}
		if($output && $output->value == "*"){	// pause the system
			pauseFT();
			$repeat = "TRUE";
		}
	}
	isThisCallActive();
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " complete. Now returning: value: " . ($output->value) . ", name: " . ($output->name) );
	return $output;
}

function gatherInput($toBeSaid, $params){

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " was called with prompt: " . $toBeSaid . " and parameters: " . implode(', ', $params));

	global $polly_prompts_dir;
	global $Silence;

	if(isThisCallActive()=="true")
	{
	    //making parameters
	    //parameters that should always be in ask array no matter what..choices...bargein..timeout
	    $choices=$params['choices'];//choices given by user
		$fchoices=makechoices($choices);//fchoices for freeswitch in format 123 or 1234* or 1*#..//fchoices made out of choices given by user,for freeswitch
		$mindigitsFS=calculateMinDigits($choices,$fchoices);
		$maxdigitsFS=calculateMaxDigits($choices,$fchoices);
		$bargein=$params['bargein'];
		$timeoutFS=$params['timeout'] * 1000;//to convert to millisecs as freeswitch timeout is in millisecs
		$validInput=makeValidINput($choices,$fchoices);

		//parameters that should may be in ask array..repeat || attempt...terminator..onBadChoice..onTimeout
		$termFS = "@";//default value menas no terminator
		if(checkifexists('terminator',$params)==true )
			$termFS = $params['terminator'];//intialized with passed parameter terminator
		$termFS= makeTerminators($fchoices,$termFS);//making it for freeswitch like if * is not in term but in choice it will put it in terminator
		$maxattemptsFS=0;//default value
		if(checkifexists('repeat',$params)==true )
			$maxattemptsFS=$params['repeat'] + 1;//intialized with passed parameter repeat
		if(checkifexists('attempts',$params)==true )
			$maxattemptsFS=$params['attempts'];//intialized with passed parameter attempts
		$invalidFS=$polly_prompts_dir.$Silence;//default value that is silence
		if(checkifexists('onBadChoice',$params)==true )
			$invalidFS=invalid($params['onBadChoice']);//intialized with prompt corrosponding to the onBadChoice value
		$onTimeOutFS="-";//default value that is silence
		if(checkifexists('onTimeout',$params)==true )
			$onTimeOutFS=onTimeOut($params['onTimeout']);//intialized with prompt corrosponding to the onTimeout value
		$interdigitTimeout=$timeoutFS;//default value is equal to timeout in freeswitch
		if(checkifexists('interdigitTimeout',$params)==true )
			$interdigitTimeout=$params['interdigitTimeout']*1000;//intialized with passed parameter interdigitTimeout
		return gatherTnputFreeSwitch($toBeSaid,$invalidFS,$mindigitsFS,$maxdigitsFS,$maxattemptsFS,$timeoutFS,$bargein,$termFS,$validInput,$onTimeOutFS,$interdigitTimeout);
	}
}

function checkifexists($parameter,$array){//checking if  parameter exists in array

	if(array_key_exists($parameter, $array)) {
		return true;
	}
	//else return false if parameter doesnt exist in array
	return false;
}

function recordAudio($toBeSaid, $params){

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " entered.");
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " Record Params: ".$params);

    global $uuid;
    global $fp;
    global $feedback_dir;
    global $voice_recordings_dir;
    global $tester;
    global $scripts_dir;
    global $voice_recordings_dir_Baang;
    global $UserIntro_Dir_Baang;
    global $feedback_dir_Baang;
    global $PQRecs;
    global $PQPrompts;
    global $PQScripts;

    $recid = "";
    $result = "";
    $filepathFS= "";
	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " was called with prompt: " . $toBeSaid . " and parameters: " . implode(', ', $params));

	if(isThisCallActive()=="true")
	{
   	 	if(checkifexists('silenceTimeout',$params)==true){
            $silTimeout= $params['silenceTimeout'];

        }
        else{
        	//setting default silence timeout
        	$silTimeout=5;
        }
        if(checkifexists('maxTime',$params)==true){
            $maxTime= $params['maxTime'];

        }
        else{
        	//setting default maximum time
        	$maxTime=30;
        }
        $rec_feed = 0;
        $recordURI = $params['recordURI'];

       	if( strpos($recordURI, 'process_feedback') !== false )
       		{
       			$parameterarray=explode("&", explode("?", $recordURI)[1]);

       			$fbid=explode("=", $parameterarray[0])[1];

	       			$filepathFS=$feedback_dir;
			        $filepathFS = createFilePath($filepathFS,$fbid.".wav",TRUE);
			        $filepathFS .= "Feedback-".$fbid."-";
	       			$i = strpos($recordURI, '=');
			        $i = $i +1;
			        $fbid = "";

				    while( $recordURI[$i] != '&')
			        {
			        	$fbid .= $recordURI[$i];
			        	$i = $i +1;
			        }
			        while( $recordURI[$i] != '=')
			        {
			        	$i = $i +1;
			        }
			        $i = $i +1;
			        while(  $i < strlen($recordURI))
			        {
			        	$filepathFS .= $recordURI[$i];
			        	$i = $i +1;
			        }
		        $filepathFS .= ".wav";
		        $rec_feed = 1;
       		}
        else if( strpos($recordURI, 'process_recording') !== false )
        	{
        		$rec_feed = 0;
       			$parameterarray=explode("&", explode("?", $recordURI)[1]);
       			$recid=explode("=", $parameterarray[0])[1];

       			if(strpos($recordURI, 'name') !== false){
       				$filepathFS=$UserIntro_Dir_Baang;
			        $filepathFS .= "intro-".$recid.".wav";
	        		$rec_feed = 6;
       			}else{
	        		$filepathFS=$voice_recordings_dir;
			        $filepathFS = createFilePath($filepathFS,$recid.".wav",TRUE);
			        $filepathFS .= "s-".$recid.".wav";
	        		$rec_feed = 0;
       			}
        	}
        else if( strpos($recordURI, 'save_recording') !== false )
        	{
        		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " handling save_recording");
       			$parameterarray = explode("&", explode("?", $recordURI)[1]);
       			$type           = explode("=", $parameterarray[0])[1];
       			$uid            = explode("=", $parameterarray[1])[1];
       			$record_id      = explode("=", $parameterarray[2])[1];

   				$rec_feed   = 6;

   				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " type = $type , record_id = $record_id");

		        if($type == "request"){ // QU-5972-3.wav
		        	global $SA_requests;

		        	$filepathFS = $SA_requests. $record_id.".wav";
				}else if($type == "name"){ // U1.wav
					global $SA_recs, $userid;

					$filepathFS = $SA_recs."U".$userid.".wav";
				}else if($type == "story"){ //QU-5972-3-1.wav
					global $SA_recs;

					$filepathFS = $SA_recs.$record_id.".wav";
				}else if($type == "comment"){
					global $SA_comments;

					$filepathFS = $SA_comments."C-".$record_id.".wav";
			   	}else if($type == "fb") {
			   		global $SA_fb_dir;

			        $filepathFS = createFilePath($SA_fb_dir, $record_id.".wav", TRUE);
	   				$rec_feed   = 6;
					$filepathFS .= $record_id . ".wav";
        		}
        		writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " filepathFS = $filepathFS");
        	}
        else if( strpos($recordURI, 'process_FriendNamerecording') !== false )
        	{
       			$parameterarray=explode("&", explode("?", $recordURI)[1]);
       			$reqid=explode("=", $parameterarray[0])[1];
       			$userid=explode("-", $reqid)[0];
					global $friend_name_dir;
					$filepathFS= $friend_name_dir;//"C:/xampp/htdocs/wa/Praat/FriendNames/";
			        $filepathFS = createFilePath($filepathFS,$userid.".wav",TRUE);
       				$rec_feed=6;
					$filepathFS .= $reqid.".wav";
    		}
		else if( strpos($recordURI, 'process_UserNamerecording') !== false )
    		{
   			$parameterarray=explode("&", explode("?", $recordURI)[1]);
   			$callid=explode("=", $parameterarray[0])[1];
				global $sender_name_dir;
				$filepathFS= $sender_name_dir ;//"C:/xampp/htdocs/wa/Praat/UserNames/";
		        $filepathFS = createFilePath($filepathFS,$callid.".wav",TRUE);
   				$rec_feed=6;
				$filepathFS .= "UserName-" . $callid . ".wav";
    		}
    	else if( strpos($recordURI, 'remt1_feedback') !== false )
    		{
    			global $ReMT1_fb_dir;

				$parameterarray = explode("&", explode("?", $recordURI)[1]);
       			$record_id      = explode("=", $parameterarray[0])[1];

		        $filepathFS = createFilePath($ReMT1_fb_dir, $record_id.".wav", TRUE);
   				$rec_feed   = 6;
				$filepathFS .= $record_id . ".wav";
    		}
    	else if( strpos($recordURI, 'remt2_feedback') !== false )
    		{
    			global $ReMT2_fb_dir;

				$parameterarray = explode("&", explode("?", $recordURI)[1]);
       			$record_id      = explode("=", $parameterarray[0])[1];

		        $filepathFS = createFilePath($ReMT2_fb_dir, $record_id.".wav", TRUE);
   				$rec_feed   = 6;
				$filepathFS .= $record_id . ".wav";
    		}
    	else if( strpos($recordURI, 'update_fb') !== false )
    		{
				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " update_fb entered");

    			global $WKB_fb_dir;

				$parameterarray = explode("&", explode("?", $recordURI)[1]);
       			$record_id      = explode("=", $parameterarray[0])[1];

       			writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " -- record_id = $record_id");

   				$rec_feed   = 6;
				$filepathFS = $WKB_fb_dir.$record_id . ".wav";

				writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", __FUNCTION__ . " -- file path = $filepathFS");
    		}

    	else if( strpos($recordURI, 'api/add/main_feedback') !== false )
    		{
    			global $MVP_feedbacks;

				$parameterarray = explode("&", explode("?", $recordURI)[1]);
       			$record_id      = explode("=", $parameterarray[0])[1];
		        $rec_feed   = 6;
				$filepathFS = $MVP_feedbacks.$record_id . ".wav";
    		}
    	else if( strpos($recordURI, 'api/add/question_feedback') !== false )
    		{
    			global $test4_user_comments;

				$parameterarray = explode("&", explode("?", $recordURI)[1]);
       			$record_id      = explode("=", $parameterarray[0])[1];

   				$rec_feed   = 6;
				$filepathFS = $test4_user_comments.$record_id . ".wav";
    		}
    	else if( strpos($recordURI, 'api/add/question_user') !== false )
    		{
    			global $MVP_recordings;

				$parameterarray = explode("&", explode("?", $recordURI)[1]);
       			$record_id      = explode("=", $parameterarray[0])[1];

   				$rec_feed   = 6;
				$filepathFS = $MVP_recordings."Q".$record_id . ".wav";
    		}
    	else if( strpos($recordURI, 'api/username') !== false )
    		{
    			global $MVP_recordings, $userid;

   				$rec_feed   = 6;
				$filepathFS = $MVP_recordings."U".$userid.".wav";
    		}

	        $kaho = "file_string://";
			$s=preg_split('/[ \n]/', $toBeSaid);
	       for($i=0;$i<count($s);$i++)
	        {
	        	$j = 0;
	        	if($s[0]=="")
	        	{
	        		$j = 1;
	        	}
	        	if($i>$j)
	        	{
	        		$kaho .= "!".$s[$i];
	        	}
	        	else
	        	{
	        		$kaho .= $s[$i];
	        	}
	        }
	        $filepathFS=str_replace("\\", "_", $filepathFS);
	        $kaho=rtrim($kaho,"!");
	       	$cmd = "api lua record.lua ".$uuid." ".$kaho." ".$filepathFS." ".$maxTime." ".$silTimeout; // some suggest using 500 as the threshold of silence
	    	$result = event_socket_request($fp, $cmd);
	    	isThisCallActive();
    		$filepathFS=str_replace("_", "\\", $filepathFS);
    		correctWavFT($filepathFS);

	    	if($rec_feed === 0)
	    	{
	    		$res = doCurl($scripts_dir."processAudFile.php?path=s-$recid".".wav");
	    	}else if($rec_feed === 2 || $rec_feed === 5 || $rec_feed === 6){
	    		$res = doCurl($recordURI);
	    	}else if($rec_feed === 1){
	    }
    }

	writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " complete. Now returning.");
    return $result;
}

function correctWavFT($filepath){
	$rep = file_gecontents($filepath);
	$rep[20] = "\x01";
	$rep[21] = "\x00";
	file_put_contents($filepath, $rep);
}

function askFT($toBeSaid, $choices, $mode, $repeat, $bargein, $timeout, $hanguppr, $mindigitsFS, $maxdigitsFS, $maxattemptsFS, $timeoutFS, $termFS, $invalidFS)
{
    global $uuid;
    global $fp;

    writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "In" . __FUNCTION__);

    $output = (object)array(
        'name' => 'choice',
        'value' => ''
    );

    $kaho = "file_string://";
    $s = preg_split('/[ \n]/', $toBeSaid);

    for ($i = 0; $i < count($s); $i++) {
        $j = 0;

        if ($s[0] == "") {
            $j = 1;
        }

        if ($i > $j) {
            $kaho.= "!" . $s[$i];
        }
        else {
            $kaho.= $s[$i];
        }
    }

    $kaho = rtrim($kaho, "!");
    $cmd = "api lua ask.lua " . $uuid . " " . $kaho . " " . $invalidFS . " " . $mindigitsFS . " " . $maxdigitsFS . " " . $maxattemptsFS . " " . $timeoutFS . " " . $termFS;
    writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L1", "FreeSwitch called to execute command : ".$cmd);
    $response = event_socket_request($fp, $cmd); //here

    if (substr($response, 1)) {

        $val = substr($response, 1);

        if ($val[0] == '_' || $val[0] == '+' || $val[0] == ' ') {
            $output->value = $val[1];
            writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", "Response of " . __FUNCTION__ . " : " . $val);
        }
        else {
            $output->name = "not_Good_timeout_or_invalid";
            $output->value = $val[1];
        }
    }
    else {
        $output->name = "not_Good_timeout_or_invalid";
        $output->value = "-";
    }
    isThisCallActive();
    return $output;
}

function sayInt($toBeSaid, $bargein = true)
{
    if (isThisCallActive() == "true") {
        $choices = "[1 DIGITS], *, #";
        $mode = 'dtmf';
        $repeatMode = 0;
        $timeout = 0.1;
        $hanguppr = "Prehangup";
        $mindigitsFS = 1;
        $maxdigitsFS = 1;
        $maxattemptsFS = 1;
        $timeoutFS = 100;
        $termFS = "*#";
        $invalidFS = "nothing";
        $repeat = "TRUE";

        writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " about to play: " . $toBeSaid . " using askFT function");

        while ($repeat == "TRUE") {
            $repeat = "FALSE";
            $result = askFT($toBeSaid, $choices, $mode, $repeatMode, $bargein, $timeout, $hanguppr, $mindigitsFS, $maxdigitsFS, $maxattemptsFS, $timeoutFS, $termFS, $invalidFS);

            if ($result->name == 'choice') {
                writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " prompt " . $toBeSaid . " was barged-in with " . ($result->value));
            }
        }

        writeToLog($GLOBALS['callid'], $GLOBALS['fh'], "L2", __FUNCTION__ . " complete. Now returning: value: " . ($result->value) . ", name: " . ($result->name));
        return $result;
    }
}
//////////////////////////////////////////////////////////////////////////////////////
///////////////////////////// End of Code ////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
?>