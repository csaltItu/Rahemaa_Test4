# Host: 127.0.0.1  (Version 5.6.24)
# Date: 2017-07-31 16:44:32
# Generator: MySQL-Front 6.0  (Build 2.20)


#
# Structure for table "action"
#

DROP TABLE IF EXISTS `action`;
CREATE TABLE `action` (
  `action_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_name` varchar(30) NOT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

#
# Structure for table "admin"
#

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

#
# Structure for table "case_answer"
#

DROP TABLE IF EXISTS `case_answer`;
CREATE TABLE `case_answer` (
  `case_answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `user_answer` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`case_answer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=910 DEFAULT CHARSET=latin1;

#
# Structure for table "case_question"
#

DROP TABLE IF EXISTS `case_question`;
CREATE TABLE `case_question` (
  `case_question_id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `correct_answer` int(11) NOT NULL,
  `options` int(11) NOT NULL,
  PRIMARY KEY (`case_question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

#
# Structure for table "cases"
#

DROP TABLE IF EXISTS `cases`;
CREATE TABLE `cases` (
  `case_id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`case_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

#
# Structure for table "forward"
#

DROP TABLE IF EXISTS `forward`;
CREATE TABLE `forward` (
  `forward_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `destination` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`forward_id`)
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=latin1;

#
# Structure for table "logs"
#

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16135 DEFAULT CHARSET=latin1;

#
# Structure for table "main_feedback"
#

DROP TABLE IF EXISTS `main_feedback`;
CREATE TABLE `main_feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `is_recorded` int(11) NOT NULL,
  `approved` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=latin1;

#
# Structure for table "question"
#

DROP TABLE IF EXISTS `question`;
CREATE TABLE `question` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `is_recorded` int(11) NOT NULL,
  `approved` int(11) NOT NULL,
  `answer_check` int(11) NOT NULL,
  `flag` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_by` varchar(20) DEFAULT NULL,
  `approved_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=476 DEFAULT CHARSET=latin1;

#
# Structure for table "question_answer"
#

DROP TABLE IF EXISTS `question_answer`;
CREATE TABLE `question_answer` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `answer_text` text,
  `flag` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isRecorded` int(11) DEFAULT NULL,
  PRIMARY KEY (`answer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=latin1;

#
# Structure for table "question_feedback"
#

DROP TABLE IF EXISTS `question_feedback`;
CREATE TABLE `question_feedback` (
  `q_feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `is_recorded` int(11) NOT NULL,
  `approved` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`q_feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

#
# Structure for table "question_listen_status"
#

DROP TABLE IF EXISTS `question_listen_status`;
CREATE TABLE `question_listen_status` (
  `listen_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `isListened` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`listen_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

#
# Structure for table "question_user_response"
#

DROP TABLE IF EXISTS `question_user_response`;
CREATE TABLE `question_user_response` (
  `response_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `response` varchar(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`response_id`)
) ENGINE=InnoDB AUTO_INCREMENT=339 DEFAULT CHARSET=latin1;

#
# Structure for table "token"
#

DROP TABLE IF EXISTS `token`;
CREATE TABLE `token` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `time_app` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
