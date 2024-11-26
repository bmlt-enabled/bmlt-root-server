/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.6.2-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: rootserver
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `na_comdef_changes`
--

DROP TABLE IF EXISTS `na_comdef_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_changes` (
  `id_bigint` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id_bigint` bigint(20) unsigned DEFAULT NULL,
  `service_body_id_bigint` bigint(20) unsigned NOT NULL,
  `lang_enum` varchar(7) NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `object_class_string` varchar(64) NOT NULL,
  `change_name_string` varchar(255) DEFAULT NULL,
  `change_description_text` text DEFAULT NULL,
  `before_id_bigint` bigint(20) unsigned DEFAULT NULL,
  `before_lang_enum` varchar(7) DEFAULT NULL,
  `after_id_bigint` bigint(20) unsigned DEFAULT NULL,
  `after_lang_enum` varchar(7) DEFAULT NULL,
  `change_type_enum` varchar(32) NOT NULL,
  `before_object` blob DEFAULT NULL,
  `after_object` blob DEFAULT NULL,
  PRIMARY KEY (`id_bigint`),
  KEY `user_id_bigint` (`user_id_bigint`),
  KEY `service_body_id_bigint` (`service_body_id_bigint`),
  KEY `lang_enum` (`lang_enum`),
  KEY `change_type_enum` (`change_type_enum`),
  KEY `change_date` (`change_date`),
  KEY `before_id_bigint` (`before_id_bigint`),
  KEY `after_id_bigint` (`after_id_bigint`),
  KEY `before_lang_enum` (`before_lang_enum`),
  KEY `after_lang_enum` (`after_lang_enum`),
  KEY `object_class_string` (`object_class_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_changes`
--

LOCK TABLES `na_comdef_changes` WRITE;
/*!40000 ALTER TABLE `na_comdef_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_comdef_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_db_version`
--

DROP TABLE IF EXISTS `na_comdef_db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_db_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_db_version`
--

LOCK TABLES `na_comdef_db_version` WRITE;
/*!40000 ALTER TABLE `na_comdef_db_version` DISABLE KEYS */;
INSERT INTO `na_comdef_db_version` VALUES
(1,21);
/*!40000 ALTER TABLE `na_comdef_db_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_format_types`
--

DROP TABLE IF EXISTS `na_comdef_format_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_format_types` (
  `key_string` varchar(10) NOT NULL,
  `api_enum` varchar(256) NOT NULL,
  `position_int` int(11) NOT NULL,
  UNIQUE KEY `na_comdef_format_types_key_string_unique` (`key_string`),
  UNIQUE KEY `na_comdef_format_types_api_enum_unique` (`api_enum`),
  KEY `na_comdef_format_types_key_string_index` (`key_string`),
  KEY `na_comdef_format_types_api_enum_index` (`api_enum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_format_types`
--

LOCK TABLES `na_comdef_format_types` WRITE;
/*!40000 ALTER TABLE `na_comdef_format_types` DISABLE KEYS */;
INSERT INTO `na_comdef_format_types` VALUES
('FC1','MEETING_FORMAT',1),
('FC2','LOCATION',2),
('FC3','COMMON_NEEDS_OR_RESTRICTION',3),
('LANG','LANGUAGE',5),
('O','OPEN_OR_CLOSED',4);
/*!40000 ALTER TABLE `na_comdef_format_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_formats`
--

DROP TABLE IF EXISTS `na_comdef_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_formats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `shared_id_bigint` bigint(20) unsigned NOT NULL,
  `root_server_id` bigint(20) unsigned DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `key_string` varchar(255) DEFAULT NULL,
  `icon_blob` blob DEFAULT NULL,
  `worldid_mixed` varchar(255) DEFAULT NULL,
  `lang_enum` varchar(7) NOT NULL DEFAULT 'en',
  `name_string` varchar(255) DEFAULT NULL,
  `description_string` text DEFAULT NULL,
  `format_type_enum` varchar(7) DEFAULT 'FC1',
  PRIMARY KEY (`id`),
  KEY `shared_id_bigint` (`shared_id_bigint`),
  KEY `worldid_mixed` (`worldid_mixed`),
  KEY `format_type_enum` (`format_type_enum`),
  KEY `lang_enum` (`lang_enum`),
  KEY `key_string` (`key_string`),
  KEY `root_server_id_source_id` (`root_server_id`,`source_id`),
  CONSTRAINT `na_comdef_formats_root_server_id_foreign` FOREIGN KEY (`root_server_id`) REFERENCES `na_root_servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=500 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_formats`
--

LOCK TABLES `na_comdef_formats` WRITE;
/*!40000 ALTER TABLE `na_comdef_formats` DISABLE KEYS */;
INSERT INTO `na_comdef_formats` VALUES
(1,1,NULL,NULL,'B',NULL,'BEG','de','Beginners','This meeting is focused on the needs of new members of NA.','FC3'),
(2,2,NULL,NULL,'BL',NULL,'LANG','de','Bi-Lingual','This Meeting can be attended by speakers of English and another language.','LANG'),
(3,3,NULL,NULL,'BT',NULL,'BT','de','Basic Text','This meeting is focused on discussion of the Basic Text of Narcotics Anonymous.','FC1'),
(4,4,NULL,NULL,'C',NULL,'CLOSED','de','Closed','This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.','O'),
(5,5,NULL,NULL,'CH',NULL,'CH','de','Closed Holidays','This meeting gathers in a facility that is usually closed on holidays.','FC3'),
(6,6,NULL,NULL,'CL',NULL,'CAN','de','Candlelight','This meeting is held by candlelight.','FC2'),
(7,7,NULL,NULL,'CS',NULL,'','de','Children under Supervision','Well-behaved, supervised children are welcome.','FC3'),
(8,8,NULL,NULL,'D',NULL,'DISC','de','Discussion','This meeting invites participation by all attendees.','FC1'),
(9,9,NULL,NULL,'ES',NULL,'LANG','de','Español','This meeting is conducted in Spanish.','LANG'),
(10,10,NULL,NULL,'GL',NULL,'GL','de','Gay/Lesbian/Transgender','This meeting is focused on the needs of gay, lesbian and transgender members of NA.','FC3'),
(11,11,NULL,NULL,'IL',NULL,NULL,'de','Illness','This meeting is focused on the needs of NA members with chronic illness.','FC1'),
(12,12,NULL,NULL,'IP',NULL,'IP','de','Informational Pamphlet','This meeting is focused on discussion of one or more Informational Pamphlets.','FC1'),
(13,13,NULL,NULL,'IW',NULL,'IW','de','It Works -How and Why','This meeting is focused on discussion of the It Works -How and Why text.','FC1'),
(14,14,NULL,NULL,'JT',NULL,'JFT','de','Just for Today','This meeting is focused on discussion of the Just For Today text.','FC1'),
(15,15,NULL,NULL,'M',NULL,'M','de','Men','This meeting is meant to be attended by men only.','FC3'),
(16,16,NULL,NULL,'NC',NULL,'NC','de','No Children','Please do not bring children to this meeting.','FC3'),
(17,17,NULL,NULL,'O',NULL,'OPEN','de','Open','This meeting is open to addicts and non-addicts alike. All are welcome.','O'),
(18,18,NULL,NULL,'Pi',NULL,NULL,'de','Pitch','This meeting has a format that consists of each person who shares picking the next person.','FC1'),
(19,19,NULL,NULL,'RF',NULL,'VAR','de','Rotating Format','This meeting has a format that changes for each meeting.','FC1'),
(20,20,NULL,NULL,'Rr',NULL,NULL,'de','Round Robin','This meeting has a fixed sharing order (usually a circle.)','FC1'),
(21,21,NULL,NULL,'SC',NULL,NULL,'de','Security Cameras','This meeting is held in a facility that has security cameras.','FC2'),
(22,22,NULL,NULL,'SD',NULL,'S-D','de','Speaker/Discussion','This meeting is lead by a speaker, then opened for participation by attendees.','FC1'),
(23,23,NULL,NULL,'SG',NULL,'SWG','de','Step Working Guide','This meeting is focused on discussion of the Step Working Guide text.','FC1'),
(24,24,NULL,NULL,'SL',NULL,NULL,'de','ASL','This meeting provides an American Sign Language (ASL) interpreter for the deaf.','FC2'),
(25,26,NULL,NULL,'So',NULL,'SPK','de','Speaker Only','This meeting is a speaker-only meeting. Other attendees do not participate in the discussion.','FC1'),
(26,27,NULL,NULL,'St',NULL,'STEP','de','Step','This meeting is focused on discussion of the Twelve Steps of NA.','FC1'),
(27,28,NULL,NULL,'Ti',NULL,NULL,'de','Timer','This meeting has sharing time limited by a timer.','FC1'),
(28,29,NULL,NULL,'To',NULL,'TOP','de','Topic','This meeting is based upon a topic chosen by a speaker or by group conscience.','FC1'),
(29,30,NULL,NULL,'Tr',NULL,'TRAD','de','Tradition','This meeting is focused on discussion of the Twelve Traditions of NA.','FC1'),
(30,31,NULL,NULL,'TW',NULL,'TRAD','de','Traditions Workshop','This meeting engages in detailed discussion of one or more of the Twelve Traditions of N.A.','FC1'),
(31,32,NULL,NULL,'W',NULL,'W','de','Women','This meeting is meant to be attended by women only.','FC3'),
(32,33,NULL,NULL,'WC',NULL,'WCHR','de','Wheelchair','This meeting is wheelchair accessible.','FC2'),
(33,34,NULL,NULL,'YP',NULL,'Y','de','Young People','This meeting is focused on the needs of younger members of NA.','FC3'),
(34,35,NULL,NULL,'OE',NULL,NULL,'de','Open-Ended','No fixed duration. The meeting continues until everyone present has had a chance to share.','FC1'),
(35,36,NULL,NULL,'BK',NULL,'LIT','de','Book Study','Approved N.A. Books','FC1'),
(36,37,NULL,NULL,'NS',NULL,'NS','de','No Smoking','Smoking is not allowed at this meeting.','FC1'),
(37,38,NULL,NULL,'Ag',NULL,NULL,'de','Agnostic','Intended for people with varying degrees of Faith.','FC1'),
(38,39,NULL,NULL,'FD',NULL,NULL,'de','Five and Dime','Discussion of the Fifth Step and the Tenth Step','FC1'),
(39,40,NULL,NULL,'AB',NULL,'QA','de','Ask-It-Basket','A topic is chosen from suggestions placed into a basket.','FC1'),
(40,41,NULL,NULL,'ME',NULL,'MED','de','Meditation','This meeting encourages its participants to engage in quiet meditation.','FC1'),
(41,42,NULL,NULL,'RA',NULL,'RA','de','Restricted Attendance','This facility places restrictions on attendees.','FC3'),
(42,43,NULL,NULL,'QA',NULL,'QA','de','Question and Answer','Attendees may ask questions and expect answers from Group members.','FC1'),
(43,44,NULL,NULL,'CW',NULL,'CW','de','Children Welcome','Children are welcome at this meeting.','FC3'),
(44,45,NULL,NULL,'CP',NULL,'CPT','de','Concepts','This meeting is focused on discussion of the twelve concepts of NA.','FC1'),
(45,46,NULL,NULL,'FIN',NULL,'LANG','de','Finnish','Finnish speaking meeting','LANG'),
(46,47,NULL,NULL,'ENG',NULL,'LANG','de','English speaking','This Meeting can be attended by speakers of English.','LANG'),
(47,48,NULL,NULL,'PER',NULL,'LANG','de','Persian','Persian speaking meeting','LANG'),
(48,49,NULL,NULL,'L/R',NULL,'LANG','de','Lithuanian/Russian','Lithuanian/Russian Speaking Meeting','LANG'),
(49,51,NULL,NULL,'LC',NULL,'LC','de','Living Clean','This is a discussion of the NA book Living Clean -The Journey Continues.','FC1'),
(50,52,NULL,NULL,'GP',NULL,'GP','de','Guiding Principles','This is a discussion of the NA book Guiding Principles - The Spirit of Our Traditions.','FC1'),
(51,54,NULL,NULL,'VM',NULL,'VM','de','Virtual Meeting','Meets Virtually','FC2'),
(52,55,NULL,NULL,'TC',NULL,'TC','de','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(53,56,NULL,NULL,'HY',NULL,'HYBR','de','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(54,57,NULL,NULL,'SPAD',NULL,'SPAD','de','Ein spirituelles Prinzip pro Tag','Lesen aus dem Buch Ein spirituelles Prinzip pro Tag','FC1'),
(55,1,NULL,NULL,'B',NULL,'BEG','dk','Beginners','This meeting is focused on the needs of new members of NA.','FC3'),
(56,2,NULL,NULL,'BL',NULL,'LANG','dk','Bi-Lingual','This Meeting can be attended by speakers of English and another language.','LANG'),
(57,3,NULL,NULL,'BT',NULL,'BT','dk','Basic Text','This meeting is focused on discussion of the Basic Text of Narcotics Anonymous.','FC1'),
(58,4,NULL,NULL,'C',NULL,'CLOSED','dk','Closed','This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.','O'),
(59,5,NULL,NULL,'CH',NULL,'CH','dk','Closed Holidays','This meeting gathers in a facility that is usually closed on holidays.','FC3'),
(60,6,NULL,NULL,'CL',NULL,'CAN','dk','Candlelight','This meeting is held by candlelight.','FC2'),
(61,7,NULL,NULL,'CS',NULL,'','dk','Children under Supervision','Well-behaved, supervised children are welcome.','FC3'),
(62,8,NULL,NULL,'D',NULL,'DISC','dk','Discussion','This meeting invites participation by all attendees.','FC1'),
(63,9,NULL,NULL,'ES',NULL,'LANG','dk','Español','This meeting is conducted in Spanish.','LANG'),
(64,10,NULL,NULL,'GL',NULL,'GL','dk','Gay/Lesbian/Transgender','This meeting is focused on the needs of gay, lesbian and transgender members of NA.','FC3'),
(65,11,NULL,NULL,'IL',NULL,NULL,'dk','Illness','This meeting is focused on the needs of NA members with chronic illness.','FC1'),
(66,12,NULL,NULL,'IP',NULL,'IP','dk','Informational Pamphlet','This meeting is focused on discussion of one or more Informational Pamphlets.','FC1'),
(67,13,NULL,NULL,'IW',NULL,'IW','dk','It Works -How and Why','This meeting is focused on discussion of the It Works -How and Why text.','FC1'),
(68,14,NULL,NULL,'JT',NULL,'JFT','dk','Just for Today','This meeting is focused on discussion of the Just For Today text.','FC1'),
(69,15,NULL,NULL,'M',NULL,'M','dk','Men','This meeting is meant to be attended by men only.','FC3'),
(70,16,NULL,NULL,'NC',NULL,'NC','dk','No Children','Please do not bring children to this meeting.','FC3'),
(71,17,NULL,NULL,'O',NULL,'OPEN','dk','Open','This meeting is open to addicts and non-addicts alike. All are welcome.','O'),
(72,18,NULL,NULL,'Pi',NULL,NULL,'dk','Pitch','This meeting has a format that consists of each person who shares picking the next person.','FC1'),
(73,19,NULL,NULL,'RF',NULL,'VAR','dk','Rotating Format','This meeting has a format that changes for each meeting.','FC1'),
(74,20,NULL,NULL,'Rr',NULL,NULL,'dk','Round Robin','This meeting has a fixed sharing order (usually a circle.)','FC1'),
(75,21,NULL,NULL,'SC',NULL,NULL,'dk','Security Cameras','This meeting is held in a facility that has security cameras.','FC2'),
(76,22,NULL,NULL,'SD',NULL,'S-D','dk','Speaker/Discussion','This meeting is lead by a speaker, then opened for participation by attendees.','FC1'),
(77,23,NULL,NULL,'SG',NULL,'SWG','dk','Step Working Guide','This meeting is focused on discussion of the Step Working Guide text.','FC1'),
(78,24,NULL,NULL,'SL',NULL,NULL,'dk','ASL','This meeting provides an American Sign Language (ASL) interpreter for the deaf.','FC2'),
(79,26,NULL,NULL,'So',NULL,'SPK','dk','Speaker Only','This meeting is a speaker-only meeting. Other attendees do not participate in the discussion.','FC1'),
(80,27,NULL,NULL,'St',NULL,'STEP','dk','Step','This meeting is focused on discussion of the Twelve Steps of NA.','FC1'),
(81,28,NULL,NULL,'Ti',NULL,NULL,'dk','Timer','This meeting has sharing time limited by a timer.','FC1'),
(82,29,NULL,NULL,'To',NULL,'TOP','dk','Topic','This meeting is based upon a topic chosen by a speaker or by group conscience.','FC1'),
(83,30,NULL,NULL,'Tr',NULL,'TRAD','dk','Tradition','This meeting is focused on discussion of the Twelve Traditions of NA.','FC1'),
(84,31,NULL,NULL,'TW',NULL,'TRAD','dk','Traditions Workshop','This meeting engages in detailed discussion of one or more of the Twelve Traditions of N.A.','FC1'),
(85,32,NULL,NULL,'W',NULL,'W','dk','Women','This meeting is meant to be attended by women only.','FC3'),
(86,33,NULL,NULL,'WC',NULL,'WCHR','dk','Wheelchair','This meeting is wheelchair accessible.','FC2'),
(87,34,NULL,NULL,'YP',NULL,'Y','dk','Young People','This meeting is focused on the needs of younger members of NA.','FC3'),
(88,35,NULL,NULL,'OE',NULL,NULL,'dk','Open-Ended','No fixed duration. The meeting continues until everyone present has had a chance to share.','FC1'),
(89,36,NULL,NULL,'BK',NULL,'LIT','dk','Book Study','Approved N.A. Books','FC1'),
(90,37,NULL,NULL,'NS',NULL,'NS','dk','No Smoking','Smoking is not allowed at this meeting.','FC1'),
(91,38,NULL,NULL,'Ag',NULL,NULL,'dk','Agnostic','Intended for people with varying degrees of Faith.','FC1'),
(92,39,NULL,NULL,'FD',NULL,NULL,'dk','Five and Dime','Discussion of the Fifth Step and the Tenth Step','FC1'),
(93,40,NULL,NULL,'AB',NULL,'QA','dk','Ask-It-Basket','A topic is chosen from suggestions placed into a basket.','FC1'),
(94,41,NULL,NULL,'ME',NULL,'MED','dk','Meditation','This meeting encourages its participants to engage in quiet meditation.','FC1'),
(95,42,NULL,NULL,'RA',NULL,'RA','dk','Restricted Attendance','This facility places restrictions on attendees.','FC3'),
(96,43,NULL,NULL,'QA',NULL,'QA','dk','Question and Answer','Attendees may ask questions and expect answers from Group members.','FC1'),
(97,44,NULL,NULL,'CW',NULL,'CW','dk','Children Welcome','Children are welcome at this meeting.','FC3'),
(98,45,NULL,NULL,'CP',NULL,'CPT','dk','Concepts','This meeting is focused on discussion of the twelve concepts of NA.','FC1'),
(99,46,NULL,NULL,'FIN',NULL,'LANG','dk','Finnish','Finnish speaking meeting','LANG'),
(100,47,NULL,NULL,'ENG',NULL,'LANG','dk','English speaking','This Meeting can be attended by speakers of English.','LANG'),
(101,48,NULL,NULL,'PER',NULL,'LANG','dk','Persian','Persian speaking meeting','LANG'),
(102,49,NULL,NULL,'L/R',NULL,'LANG','dk','Lithuanian/Russian','Lithuanian/Russian Speaking Meeting','LANG'),
(103,51,NULL,NULL,'LC',NULL,'LC','dk','Living Clean','This is a discussion of the NA book Living Clean -The Journey Continues.','FC1'),
(104,52,NULL,NULL,'GP',NULL,'GP','dk','Guiding Principles','This is a discussion of the NA book Guiding Principles - The Spirit of Our Traditions.','FC1'),
(105,54,NULL,NULL,'VM',NULL,'VM','dk','Virtual Meeting','Meets Virtually','FC2'),
(106,55,NULL,NULL,'TC',NULL,'TC','dk','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(107,56,NULL,NULL,'HY',NULL,'HYBR','dk','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(108,1,NULL,NULL,'B',NULL,'BEG','en','Beginners','This meeting is focused on the needs of new members of NA.','FC3'),
(109,2,NULL,NULL,'BL',NULL,'LANG','en','Bi-Lingual','This Meeting can be attended by speakers of English and another language.','LANG'),
(110,3,NULL,NULL,'BT',NULL,'BT','en','Basic Text','This meeting is focused on discussion of the Basic Text of Narcotics Anonymous.','FC1'),
(111,4,NULL,NULL,'C',NULL,'CLOSED','en','Closed','This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.','O'),
(112,5,NULL,NULL,'CH',NULL,'CH','en','Closed Holidays','This meeting gathers in a facility that is usually closed on holidays.','FC3'),
(113,6,NULL,NULL,'CL',NULL,'CAN','en','Candlelight','This meeting is held by candlelight.','FC2'),
(114,7,NULL,NULL,'CS',NULL,'','en','Children under Supervision','Well-behaved, supervised children are welcome.','FC3'),
(115,8,NULL,NULL,'D',NULL,'DISC','en','Discussion','This meeting invites participation by all attendees.','FC1'),
(116,9,NULL,NULL,'ES',NULL,'LANG','en','Español','This meeting is conducted in Spanish.','LANG'),
(117,10,NULL,NULL,'GL',NULL,'GL','en','Gay/Lesbian/Transgender','This meeting is focused on the needs of gay, lesbian and transgender members of NA.','FC3'),
(118,11,NULL,NULL,'IL',NULL,NULL,'en','Illness','This meeting is focused on the needs of NA members with chronic illness.','FC1'),
(119,12,NULL,NULL,'IP',NULL,'IP','en','Informational Pamphlet','This meeting is focused on discussion of one or more Informational Pamphlets.','FC1'),
(120,13,NULL,NULL,'IW',NULL,'IW','en','It Works -How and Why','This meeting is focused on discussion of the It Works -How and Why text.','FC1'),
(121,14,NULL,NULL,'JT',NULL,'JFT','en','Just for Today','This meeting is focused on discussion of the Just For Today text.','FC1'),
(122,15,NULL,NULL,'M',NULL,'M','en','Men','This meeting is meant to be attended by men only.','FC3'),
(123,16,NULL,NULL,'NC',NULL,'NC','en','No Children','Please do not bring children to this meeting.','FC3'),
(124,17,NULL,NULL,'O',NULL,'OPEN','en','Open','This meeting is open to addicts and non-addicts alike. All are welcome.','O'),
(125,18,NULL,NULL,'Pi',NULL,NULL,'en','Pitch','This meeting has a format that consists of each person who shares picking the next person.','FC1'),
(126,19,NULL,NULL,'RF',NULL,'VAR','en','Rotating Format','This meeting has a format that changes for each meeting.','FC1'),
(127,20,NULL,NULL,'Rr',NULL,NULL,'en','Round Robin','This meeting has a fixed sharing order (usually a circle.)','FC1'),
(128,21,NULL,NULL,'SC',NULL,NULL,'en','Security Cameras','This meeting is held in a facility that has security cameras.','FC2'),
(129,22,NULL,NULL,'SD',NULL,'S-D','en','Speaker/Discussion','This meeting is lead by a speaker, then opened for participation by attendees.','FC1'),
(130,23,NULL,NULL,'SG',NULL,'SWG','en','Step Working Guide','This meeting is focused on discussion of the Step Working Guide text.','FC1'),
(131,24,NULL,NULL,'SL',NULL,NULL,'en','ASL','This meeting provides an American Sign Language (ASL) interpreter for the deaf.','FC2'),
(132,26,NULL,NULL,'So',NULL,'SPK','en','Speaker Only','This meeting is a speaker-only meeting. Other attendees do not participate in the discussion.','FC1'),
(133,27,NULL,NULL,'St',NULL,'STEP','en','Step','This meeting is focused on discussion of the Twelve Steps of NA.','FC1'),
(134,28,NULL,NULL,'Ti',NULL,NULL,'en','Timer','This meeting has sharing time limited by a timer.','FC1'),
(135,29,NULL,NULL,'To',NULL,'TOP','en','Topic','This meeting is based upon a topic chosen by a speaker or by group conscience.','FC1'),
(136,30,NULL,NULL,'Tr',NULL,'TRAD','en','Tradition','This meeting is focused on discussion of the Twelve Traditions of NA.','FC1'),
(137,31,NULL,NULL,'TW',NULL,'TRAD','en','Traditions Workshop','This meeting engages in detailed discussion of one or more of the Twelve Traditions of N.A.','FC1'),
(138,32,NULL,NULL,'W',NULL,'W','en','Women','This meeting is meant to be attended by women only.','FC3'),
(139,33,NULL,NULL,'WC',NULL,'WCHR','en','Wheelchair','This meeting is wheelchair accessible.','FC2'),
(140,34,NULL,NULL,'YP',NULL,'Y','en','Young People','This meeting is focused on the needs of younger members of NA.','FC3'),
(141,35,NULL,NULL,'OE',NULL,NULL,'en','Open-Ended','No fixed duration. The meeting continues until everyone present has had a chance to share.','FC1'),
(142,36,NULL,NULL,'BK',NULL,'LIT','en','Book Study','Approved N.A. Books','FC1'),
(143,37,NULL,NULL,'NS',NULL,'NS','en','No Smoking','Smoking is not allowed at this meeting.','FC1'),
(144,38,NULL,NULL,'Ag',NULL,NULL,'en','Agnostic','Intended for people with varying degrees of Faith.','FC1'),
(145,39,NULL,NULL,'FD',NULL,NULL,'en','Five and Dime','Discussion of the Fifth Step and the Tenth Step','FC1'),
(146,40,NULL,NULL,'AB',NULL,'QA','en','Ask-It-Basket','A topic is chosen from suggestions placed into a basket.','FC1'),
(147,41,NULL,NULL,'ME',NULL,'MED','en','Meditation','This meeting encourages its participants to engage in quiet meditation.','FC1'),
(148,42,NULL,NULL,'RA',NULL,'RA','en','Restricted Attendance','This facility places restrictions on attendees.','FC3'),
(149,43,NULL,NULL,'QA',NULL,'QA','en','Question and Answer','Attendees may ask questions and expect answers from Group members.','FC1'),
(150,44,NULL,NULL,'CW',NULL,'CW','en','Children Welcome','Children are welcome at this meeting.','FC3'),
(151,45,NULL,NULL,'CP',NULL,'CPT','en','Concepts','This meeting is focused on discussion of the twelve concepts of NA.','FC1'),
(152,46,NULL,NULL,'FIN',NULL,'LANG','en','Finnish','Finnish speaking meeting','LANG'),
(153,47,NULL,NULL,'ENG',NULL,'LANG','en','English speaking','This Meeting can be attended by speakers of English.','LANG'),
(154,48,NULL,NULL,'PER',NULL,'LANG','en','Persian','Persian speaking meeting','LANG'),
(155,49,NULL,NULL,'L/R',NULL,'LANG','en','Lithuanian/Russian','Lithuanian/Russian Speaking Meeting','LANG'),
(156,51,NULL,NULL,'LC',NULL,'LC','en','Living Clean','This is a discussion of the NA book Living Clean -The Journey Continues.','FC1'),
(157,52,NULL,NULL,'GP',NULL,'GP','en','Guiding Principles','This is a discussion of the NA book Guiding Principles - The Spirit of Our Traditions.','FC1'),
(158,54,NULL,NULL,'VM',NULL,'VM','en','Virtual Meeting','Meets Virtually','FC2'),
(159,55,NULL,NULL,'TC',NULL,'TC','en','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(160,56,NULL,NULL,'HY',NULL,'HYBR','en','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(161,57,NULL,NULL,'SPAD',NULL,'SPAD','en','A Spiritual Principle a Day','This meeting is focused on discussion of the book A Spiritual Principle a Day.','FC1'),
(162,1,NULL,NULL,'B',NULL,'BEG','es','Para el recién llegado','Esta reunión se centra en las necesidades de los nuevos miembros de NA.','FC3'),
(163,2,NULL,NULL,'BL',NULL,'LANG','es','Bilingüe','Esta reunión se pueden asistir personas de que hablen inglés y otro idioma.','LANG'),
(164,3,NULL,NULL,'BT',NULL,'BT','es','Texto Básico','Esta reunión se centra en la discusión del texto básico de Narcóticos Anónimos.','FC1'),
(165,4,NULL,NULL,'C',NULL,'CLOSED','es','Cerrado','Esta reunión está cerrada a los no adictos. Usted debe asistir solamente si cree que puede tener un problema con abuso de drogas.','O'),
(166,5,NULL,NULL,'CH',NULL,NULL,'es','Cerrado en Días de fiesta','Esta reunión tiene lugar en una localidad que esta generalmente cerrada los días de fiesta.','FC3'),
(167,6,NULL,NULL,'CL',NULL,'CAN','es','Luz de vela','Esta reunión se celebra a luz de vela.','FC2'),
(168,7,NULL,NULL,'CS',NULL,'','es','Niños bajo Supervisión','Los niños de buen comportamiento y supervisados son bienvenidos.','FC3'),
(169,8,NULL,NULL,'D',NULL,'DISC','es','Discusión','Esta reunión invita la participación de todos los asistentes.','FC1'),
(170,10,NULL,NULL,'GL',NULL,'GL','es','Gay/Lesbiana','Esta reunión se centra en las necesidades de miembros gay y lesbianas de NA.','FC3'),
(171,11,NULL,NULL,'IL',NULL,NULL,'es','Enfermedad','Esta reunión se centra en las necesidades de los miembros de NA con enfermedades crónicas.','FC1'),
(172,12,NULL,NULL,'IP',NULL,'IP','es','Folleto Informativo','Esta reunión se centra en la discusión de unos o más folletos informativos.','FC1'),
(173,13,NULL,NULL,'IW',NULL,'IW','es','Functiona - Cómo y Porqué','Esta reunión se centra en la discusión del texto Funciona - Cómo y Porqué.','FC1'),
(174,14,NULL,NULL,'JT',NULL,'JFT','es','Solo por Hoy','Esta reunión se centra en la discusión del texto Solo por Hoy.','FC1'),
(175,15,NULL,NULL,'M',NULL,'M','es','Hombres','A esta reunión se supone que aistan hombres solamente.','FC3'),
(176,16,NULL,NULL,'NC',NULL,NULL,'es','No niños','Por favor no traer niños a esta reunión.','FC3'),
(177,17,NULL,NULL,'O',NULL,'OPEN','es','Abierta','Esta reunión está abierta a los adictos y a los no adictos por igual. Todos son bienvenidos.','O'),
(178,18,NULL,NULL,'Pi',NULL,NULL,'es','Echada','Esta reunión tiene un formato que consiste en que cada persona que comparta escoja a la persona siguiente.','FC1'),
(179,19,NULL,NULL,'RF',NULL,'VAR','es','Formato que Rota','Esta reunión tiene un formato que cambia para cada reunión.','FC1'),
(180,20,NULL,NULL,'Rr',NULL,NULL,'es','Round Robin','Esta reunión tiene un orden fijo de compartir (generalmente un círculo).','FC1'),
(181,21,NULL,NULL,'SC',NULL,NULL,'es','Cámaras de Vigilancia','Esta reunión se celebra en una localidad que tenga cámaras de vigilancia.','FC2'),
(182,22,NULL,NULL,'SD',NULL,'S-D','es','Orador/Discusión','Esta reunión es conducida por un orador, después es abierta para la participación de los asistentes.','FC1'),
(183,23,NULL,NULL,'SG',NULL,'SWG','es','Guia Para Trabajar los Pasos','Esta reunión se centra en la discusión del texto Guia Para Trabajar los Pasos.','FC1'),
(184,24,NULL,NULL,'SL',NULL,NULL,'es','ASL','Esta reunión proporciona intérprete (ASL) para los sordos.','FC2'),
(185,26,NULL,NULL,'So',NULL,'SPK','es','Solamente Orador','Esta reunión es de orador solamente. Otros asistentes no participan en la discusión.','FC1'),
(186,27,NULL,NULL,'St',NULL,'STEP','es','Paso','Esta reunión se centra en la discusión de los doce pasos de NA.','FC1'),
(187,28,NULL,NULL,'Ti',NULL,NULL,'es','Contador de Tiempo','Esta reunión tiene el tiempo de compartir limitado por un contador de tiempo.','FC1'),
(188,29,NULL,NULL,'To',NULL,'TOP','es','Tema','Esta reunión se basa en un tema elegido por el orador o por la conciencia del grupo.','FC1'),
(189,30,NULL,NULL,'Tr',NULL,'TRAD','es','Tradición','Esta reunión se centra en la discusión de las Doce Tradiciones de NA.','FC1'),
(190,31,NULL,NULL,'TW',NULL,'TRAD','es','Taller de las Tradiciones','Esta reunión consiste en la discusión detallada de una o más de las Doce Tradiciones de N.A.','FC1'),
(191,32,NULL,NULL,'W',NULL,'W','es','Mujeres','A esta reunión se supone que asistan mujeres solamente.','FC3'),
(192,33,NULL,NULL,'WC',NULL,'WCHR','es','Silla de Ruedas','Esta reunión es accesible por silla de ruedas.','FC2'),
(193,34,NULL,NULL,'YP',NULL,'Y','es','Jovenes','Esta reunión se centra en las necesidades de los miembros más jóvenes de NA.','FC3'),
(194,35,NULL,NULL,'OE',NULL,NULL,'es','Sin Tiempo Fijo','No tiene tiempo fijo. Esta reunión continua hasta que cada miembro haya tenido la oportunidad de compartir.','FC1'),
(195,54,NULL,NULL,'VM',NULL,'VM','es','Virtual Meeting','Meets Virtually','FC2'),
(196,55,NULL,NULL,'TC',NULL,'TC','es','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(197,56,NULL,NULL,'HY',NULL,'HYBR','es','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(198,54,NULL,NULL,'VM',NULL,'VM','fa','Virtual Meeting','Meets Virtually','FC2'),
(199,55,NULL,NULL,'TC',NULL,'TC','fa','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(200,56,NULL,NULL,'HY',NULL,'HYBR','fa','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(201,1,NULL,NULL,'B',NULL,'BEG','fr','Débutants','Cette réunion est axée sur les besoins des nouveaux membres de NA.','FC3'),
(202,2,NULL,NULL,'BL',NULL,'LANG','fr','bilingue','Cette réunion peut aider les personnes qui parlent l\'anglais et une autre langue.','LANG'),
(203,3,NULL,NULL,'BT',NULL,'BT','fr','Texte de Base','Cette réunion est axée sur la discussion du texte de base de Narcotiques Anonymes.','FC1'),
(204,4,NULL,NULL,'C',NULL,'CLOSED','fr','Fermée','Cette réunion est fermée aux non-toxicomanes. Vous pouvez y assister que si vous pensez que vous pouvez avoir un problème avec l\'abus de drogues.','O'),
(205,5,NULL,NULL,'CH',NULL,NULL,'fr','Fermé durant les jours fériés.','Cette réunion a lieu dans une local qui est généralement fermé durant les jours fériés.','FC3'),
(206,6,NULL,NULL,'CL',NULL,'CAN','fr','Chandelle','Cette réunion se déroule à la chandelle.','FC2'),
(207,7,NULL,NULL,'CS',NULL,'','fr','Enfants sous Supervision','Les enfants bien élevés sont les bienvenus et supervisés.','FC3'),
(208,8,NULL,NULL,'D',NULL,'DISC','fr','Discussion','Cette réunion invite tous les participants à la discussion.','FC1'),
(209,10,NULL,NULL,'GL',NULL,'GL','fr','Gais, lesbiennes, transsexuel(le)s, bisexuel(le)s','Cette réunion est axée sur les besoins des membres gais, lesbiennes, transsexuel(le)s et bisexuel(le)s de NA.','FC3'),
(210,11,NULL,NULL,'IL',NULL,NULL,'fr','Chroniques','Cette réunion est axée sur les besoins des membres de NA comportant des troubles de maladies chroniques.','FC1'),
(211,12,NULL,NULL,'IP',NULL,'IP','fr','Brochures','Cette réunion est axée sur la discussion d\'une ou plusieurs brochures.','FC1'),
(212,13,NULL,NULL,'IW',NULL,'IW','fr','Ça marche, Comment et Pourquoi','Cette session met l\'accent sur la discussion de texte Ça marche, Comment et Pourquoi.','FC1'),
(213,14,NULL,NULL,'JT',NULL,'JFT','fr','Juste pour aujourd\'hui','Cette session met l\'accent sur la discussion du texte Juste pour aujourd\'hui.','FC1'),
(214,15,NULL,NULL,'M',NULL,'M','fr','Hommes','Cette réunion est destinée à être assisté par seulement que des hommes.','FC3'),
(215,16,NULL,NULL,'NC',NULL,NULL,'fr','Pas d\'enfant','S\'il vous plaît, ne pas amener les enfants à cette réunion.','FC3'),
(216,17,NULL,NULL,'O',NULL,'OPEN','fr','Ouvert','Cette réunion est ouverte aux toxicomanes et non-toxicomanes de même. Tous sont les bienvenus.','O'),
(217,18,NULL,NULL,'Pi',NULL,NULL,'fr','À la pige','Cette réunion a un format de discussion est que chaque personne qui discute invite la personne suivante à discuter.','FC1'),
(218,19,NULL,NULL,'RF',NULL,'VAR','fr','Format varié','Cette réunion a un format qui varie à toutes les réunions.','FC1'),
(219,20,NULL,NULL,'Rr',NULL,NULL,'fr','À la ronde','Cette réunion a un ordre de partage fixe (généralement un cercle).','FC1'),
(220,21,NULL,NULL,'SC',NULL,NULL,'fr','Caméra de surveillance','Cette réunion se tient dans un emplacement qui a des caméras de surveillance.','FC2'),
(221,22,NULL,NULL,'SD',NULL,'S-D','fr','Partage et ouvert','Cette réunion a un conférencier, puis ouvert au public.','FC1'),
(222,23,NULL,NULL,'SG',NULL,'SWG','fr','Guides des Étapes','Cette réunion est axée sur la discussion sur le Guide des Étapes.','FC1'),
(223,24,NULL,NULL,'SL',NULL,NULL,'fr','Malentendants','Cette rencontre permet l\'interprète pour les personnes malentendantes.','FC2'),
(224,26,NULL,NULL,'So',NULL,'SPK','fr','Partage seulement','Cette réunion a seulement un conférencier. Les autres participants ne participent pas à la discussion.','FC1'),
(225,27,NULL,NULL,'St',NULL,'STEP','fr','Étapes NA','Cette réunion est axée sur la discussion des Douze Étapes de NA.','FC1'),
(226,28,NULL,NULL,'Ti',NULL,NULL,'fr','Discussion chronométrée','Cette réunion a une durée de discussion  limitée par une minuterie pour chaque personne.','FC1'),
(227,29,NULL,NULL,'To',NULL,'TOP','fr','Thématique','Cette réunion est basée sur un thème choisi par la personne qui anime ou la conscience de groupe.','FC1'),
(228,30,NULL,NULL,'Tr',NULL,'TRAD','fr','Traditions','Cette réunion est axée sur la discussion des Douze Traditions de NA.','FC1'),
(229,31,NULL,NULL,'TW',NULL,'TRAD','fr','Atelier sur les traditions','Cette réunion est une discussion détaillée d\'une ou de plusieurs des Douze Traditions de NA','FC1'),
(230,32,NULL,NULL,'W',NULL,'W','fr','Femmes','Seulement les femmes sont admises.','FC3'),
(231,33,NULL,NULL,'WC',NULL,'WCHR','fr','Fauteuil Roulant','Cette réunion est accessible en fauteuil roulant.','FC2'),
(232,34,NULL,NULL,'YP',NULL,'Y','fr','Jeunes','Cette réunion est axée sur les besoins des plus jeunes membres de NA.','FC3'),
(233,35,NULL,NULL,'OE',NULL,NULL,'fr','Marathon','Il n\'y a pas de durée fixe. Cette réunion se poursuit jusqu\'à ce que chaque membre a eu l\'occasion de partager.','FC1'),
(234,36,NULL,NULL,'BK',NULL,'LIT','fr','Études de livres NA','Livres  N.A. Approuvés','FC1'),
(235,37,NULL,NULL,'NS',NULL,'NS','fr','Non-fumeurs','Fumer n\'est pas permis à cette réunion.','FC1'),
(236,38,NULL,NULL,'Ag',NULL,NULL,'fr','Agnostique','Destiné aux personnes ayant divers degrés de la foi.','FC1'),
(237,39,NULL,NULL,'FD',NULL,NULL,'fr','Cinq et dix','Discussion de la cinquième étape et la dixième étape.','FC1'),
(238,40,NULL,NULL,'AB',NULL,'QA','fr','Panier','Un sujet est choisi parmi les suggestions placées dans un panier.','FC1'),
(239,41,NULL,NULL,'ME',NULL,'MED','fr','Méditation','Cette réunion encourage ses participants à s\'engager dans la méditation tranquille.','FC1'),
(240,42,NULL,NULL,'RA',NULL,'RA','fr','Accés limités','Cet emplacement impose des restrictions sur les participants.','FC3'),
(241,43,NULL,NULL,'QA',NULL,'QA','fr','Questions et Réponses','Les participants peuvent poser des questions et attendre des réponses des membres du groupe.','FC1'),
(242,44,NULL,NULL,'CW',NULL,'CW','fr','Enfants bienvenus','Les enfants sont les bienvenus à cette réunion.','FC3'),
(243,45,NULL,NULL,'CP',NULL,'CPT','fr','Concepts','Cette réunion est axée sur la discussion des douze concepts de NA.','FC1'),
(244,46,NULL,NULL,'Finlandais',NULL,NULL,'fr','Finlandais','Cette réunion se déroule en langue finlandaisè','FC3'),
(245,47,NULL,NULL,'ENG',NULL,NULL,'fr','Anglais','Cette réunion se déroule de langues anglais.','FC3'),
(246,54,NULL,NULL,'VM',NULL,'VM','fr','Virtual Meeting','Meets Virtually','FC2'),
(247,55,NULL,NULL,'TC',NULL,'TC','fr','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(248,56,NULL,NULL,'HY',NULL,'HYBR','fr','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(249,1,NULL,NULL,'NV',NULL,NULL,'it','Nuovi venuti','Riunione concentrata principalmente sulle necessità dei nuovi membri di NA.','FC3'),
(250,2,NULL,NULL,'BL',NULL,'LANG','it','Bilingue','Questa riunione può essere frequentata da membri che parlano italiano e/o inglese.','LANG'),
(251,3,NULL,NULL,'TB',NULL,NULL,'it','Testo base','Riunione concentrata sulla discussione del testo base di NA.','FC1'),
(252,4,NULL,NULL,'Ch.',NULL,NULL,'it','Chiusa','Riunione chiusa ai non dipendenti. Dovrebbe frequentarla soltanto chi crede di avere un problema con le sostanze d\'abuso.','O'),
(253,5,NULL,NULL,'SF',NULL,NULL,'it','Sospesa nei giorni festivi','Questa riunione si tiene in locali che di solito sono chiusi nei giorni festivi e di vacanza.','FC3'),
(254,6,NULL,NULL,'LC',NULL,NULL,'it','Lume di candela','Questa riunione si tiene a lume di candela per favorire la meditazione.','FC2'),
(255,7,NULL,NULL,'BS',NULL,NULL,'it','Bambini sotto supervisione','Sono ammessi bambini senza problemi di comportamento e sotto supervisione.','FC3'),
(256,8,NULL,NULL,'Disc.',NULL,NULL,'it','Discussione','Tutti i partecipanti sono invitati a condividere.','FC1'),
(257,9,NULL,NULL,'ES',NULL,'LANG','it','Spagnolo','Riunione in lingua spagnolo.','FC3'),
(258,14,NULL,NULL,'SPO',NULL,NULL,'it','Solo per oggi','Riunione in cui si discutono i temi delle meditazioni quotidiane del libro \"Solo per oggi\".','FC1'),
(259,15,NULL,NULL,'U',NULL,NULL,'it','Uomini','Riunioni per soli uomini.','FC3'),
(260,17,NULL,NULL,'Ap.',NULL,NULL,'it','Aperta','Riunione aperta ai non dipendenti. Parenti, amici, professionisti e altri membri della società, sono benvenuti.','O'),
(261,23,NULL,NULL,'GLP',NULL,NULL,'it','Guida al lavoro sui passi','Riunione basata sulla discussione della Guida al lavoro sui Dodici passi di NA.','FC1'),
(262,28,NULL,NULL,'Temp.',NULL,NULL,'it','Condivisioni temporizzate','In queste riunioni il tempo di condivisione è limitato da un cronometro.','FC1'),
(263,27,NULL,NULL,'P',NULL,NULL,'it','Passi','Riunione di discussione sui Dodici passi.','FC1'),
(264,29,NULL,NULL,'Arg.',NULL,NULL,'it','Riunioni a tema','Queste riunioni si basano su un argomento prescelto.','FC1'),
(265,30,NULL,NULL,'T',NULL,NULL,'it','Tradizioni','Riunione di discussione sulle Dodici tradizioni.','FC1'),
(266,31,NULL,NULL,'TW',NULL,NULL,'it','Workshop sulle Dodici tradizioni','Riunioni in cui si discute dettagliatamente su una o più delle Dodici tradizioni.','FC1'),
(267,32,NULL,NULL,'D',NULL,NULL,'it','Donne','Riunione solo donne.','FC3'),
(268,33,NULL,NULL,'SR',NULL,NULL,'it','Sedia a rotelle','Riunione accessibile per chi ha la sedia a rotelle.','FC2'),
(269,35,NULL,NULL,'M',NULL,NULL,'it','Maratona','Durata non prestabilita. La riunione prosegue finché tutti i presenti hanno da condividere.','FC1'),
(270,37,NULL,NULL,'NF',NULL,NULL,'it','Non fumatori','In queste riunioni non è consentito fumare.','FC1'),
(271,40,NULL,NULL,'TS',NULL,NULL,'it','Tema a sorpresa','L\'argomento su cui condividere è scritto su un biglietto o altro supporto (es. un bastoncino di legno) ed estratto a caso da ciascun membro.','FC1'),
(272,42,NULL,NULL,'M',NULL,NULL,'it','Meditazione','In questa riunione sono poste restrizioni alle modalità di partecipazione.','FC3'),
(273,43,NULL,NULL,'D/R',NULL,NULL,'it','Domande e risposteq','I partecipanti possono fare domande e attenderne la risposta dagli altri membri del gruppo.','FC1'),
(274,44,NULL,NULL,'Ba',NULL,NULL,'it','Bambini','I bambini sono benvenuti in queste riunioni.','FC3'),
(275,45,NULL,NULL,'C',NULL,NULL,'it','Concetti di servizio','Riunioni basate sulla discussione dei Dodici concetti per il servizio in NA.','FC1'),
(276,51,NULL,NULL,'VP',NULL,NULL,'it','Vivere puliti','Riunioni di discussione sul libro \"Vivere puliti - Il viaggio continua\".','FC1'),
(277,54,NULL,NULL,'VM',NULL,'VM','it','Virtual Meeting','Meets Virtually','FC2'),
(278,55,NULL,NULL,'TC',NULL,'TC','it','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(279,56,NULL,NULL,'HY',NULL,'HYBR','it','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(280,1,NULL,NULL,'B',NULL,'BEG','pl','Nowoprzybyli','Mityng koncentruje się na potrzebach nowyh członków NA.','FC3'),
(281,2,NULL,NULL,'BL',NULL,'LANG','pl','Wielojęzykowość','Na tym mityngu mogą uczęszczać osoby posługujące się językiem angielskim i innymi.','LANG'),
(282,3,NULL,NULL,'BT',NULL,'BT','pl','Tekst Podstawowy','Mityng koncentruje się na dyskusjach o Tekście Podstawowym Anonimowych Narkomanów.','FC1'),
(283,4,NULL,NULL,'C',NULL,'CLOSED','pl','Mityng zamknięty','Mityng zamknięty. Wyłącznie dla osób uzależnionych i tych, które chcą przestać brać.','O'),
(284,5,NULL,NULL,'CH',NULL,'CH','pl','Zamknięty w święta','Mityng odbywa się w miejscu, które zwykle jest zamknięte w dni wolne od pracy/wakacje.','FC3'),
(285,6,NULL,NULL,'CL',NULL,'CAN','pl','Świeczka','Ten mityng odbywa się przy blasku świecy.','FC2'),
(286,7,NULL,NULL,'CS',NULL,'','pl','Dzieci pod opieką','Dzieci uzależnionych mile widziane pod warunkiem odpowiedniego zachowania.','FC3'),
(287,8,NULL,NULL,'D',NULL,'DISC','pl','Dyskusja','Mityng dla wszystkich chętnych.','FC1'),
(288,9,NULL,NULL,'ES',NULL,'LANG','pl','Hiszpański','Mityng odbywa się w języku hiszpańskim.','LANG'),
(289,10,NULL,NULL,'GL',NULL,'GL','pl','LGBTQ','Mityng koncentruje się na członkach wspólnoty należących do społeczności LGBT.','FC3'),
(290,11,NULL,NULL,'IL',NULL,NULL,'pl','Choroba','Mityng koncentruje się na potrzebach przewlekle chorych członków NA.','FC1'),
(291,12,NULL,NULL,'IP',NULL,'IP','pl','Broszura Informacyjna','Mityng koncentruje się na dyskusji nad jedną z Broszur Międzynarodowych.','FC1'),
(292,13,NULL,NULL,'IW',NULL,'IW','pl','To Działa - Jak i Dlaczego','Mityng koncentruje się na dyskusji nad tekstem z \"To Działa - Jak i Dlaczego\".','FC1'),
(293,14,NULL,NULL,'JT',NULL,'JFT','pl','Właśnie Dzisiaj','Mityng koncentruje się na dyskusji nad tekstem z \"Właśnie dzisiaj\".','FC1'),
(294,15,NULL,NULL,'M',NULL,'M','pl','Mężczyźni','Mityng wyłącznie dla mężczyzn.','FC3'),
(295,16,NULL,NULL,'NC',NULL,'NC','pl','Bez Dzieci','Prosimy, by nie przyprowadzać dzieci na ten mityng.','FC3'),
(296,17,NULL,NULL,'O',NULL,'OPEN','pl','Otwarty','Mityng otwarty dla uzależnionych i nieuzależnionych. Wszyscy są mile widziani.','O'),
(297,18,NULL,NULL,'Pi',NULL,NULL,'pl','Pitch','Na tym mityngu obowiązuje format, w którym osoba, dzieląca się doświadczeniem, wybiera kolejną osobę.','FC1'),
(298,19,NULL,NULL,'RF',NULL,'VAR','pl','Zmienny format','Format tego mityngu zmienia się co mityng.','FC1'),
(299,20,NULL,NULL,'Rr',NULL,NULL,'pl','Round Robin','Na tym mityngu jest ustalona kolejność dzielenia się doświadczeniem (zwykle w koło)','FC1'),
(300,21,NULL,NULL,'SC',NULL,NULL,'pl','Kamery bezpieczeństwa','Mityng odbywa się w miejscu, w którym zamontowane są kamery bezpieczeństwa.','FC2'),
(301,22,NULL,NULL,'SD',NULL,'S-D','pl','Spikerka/dyskusja','Mityng rozpoczynany jest wypowiedzią spikera, a następnie jest otwarty do dzielenia się przez resztę uczestników.','FC1'),
(302,23,NULL,NULL,'SG',NULL,'SWG','pl','Przewodnik pracy nad Krokami','Mityng koncentruje się na dyskusji nad tekstem z \"Przewodnika do pracy nad Krokami\".','FC1'),
(303,24,NULL,NULL,'SL',NULL,NULL,'pl','ASL','W tym mityngu bierze udział tłumacz języka migowego dla osób niesłyszących.','FC2'),
(304,26,NULL,NULL,'So',NULL,'SPK','pl','Tylko spikerka','Mityng składa się tylko z wypowiedzi spikera. Inni uczestnicy nie dzielą się doświadczeniem.','FC1'),
(305,27,NULL,NULL,'St',NULL,'STEP','pl','Kroki','Mityng koncentruje się na dyskusji nad Dwunastoma Krokami Anonimowych Narkomanów.','FC1'),
(306,28,NULL,NULL,'Ti',NULL,NULL,'pl','Licznik czasu','Na tym mitngu czas wypowiedzi jest kontrolowany przez licznik czasu.','FC1'),
(307,29,NULL,NULL,'To',NULL,'TOP','pl','Dowolny temat','Temat tego mityngu jest wybierany przez spikera lub przez sumienie grupy.','FC1'),
(308,30,NULL,NULL,'Tr',NULL,'TRAD','pl','Tradycje','Mityng koncentruje się na dyskusji nad Dwunastoma Tradycjami NA.','FC1'),
(309,31,NULL,NULL,'TW',NULL,'TRAD','pl','Warsztaty z tradycji','Mityng koncentruje się na wnikliwej analizje jednej lub wielu z Dwunastu Tradycji Anonimowych Narkomanów','FC1'),
(310,32,NULL,NULL,'W',NULL,'W','pl','Kobiety','Mityng przeznaczony jedynie dla kobiet.','FC3'),
(311,33,NULL,NULL,'WC',NULL,'WCHR','pl','Wózki inwalidzkie','Mityng wyposażony w łatwy dostęp dla wózków inwalidzkich.','FC2'),
(312,34,NULL,NULL,'YP',NULL,'Y','pl','Młodzi ludzie','Mityng koncentruje się na dyskusjach nad potrzebami najmłodszych członków NA.','FC3'),
(313,35,NULL,NULL,'OE',NULL,NULL,'pl','Bez końca','Mityng bez ustalonej długości. Trwa tak długo, jak długo są na nim uczestnicy.','FC1'),
(314,36,NULL,NULL,'BK',NULL,'LIT','pl','Analiza książek','Analiza oficjalnych książek Anonimowych Narkomanów','FC1'),
(315,37,NULL,NULL,'NS',NULL,'NS','pl','Zakac palenia','Palenie w trakcie tego mityngu jest zabronione.','FC1'),
(316,38,NULL,NULL,'Ag',NULL,NULL,'pl','Agnostycy','Mityng dla ludzi o zróżnicowanych stopniach wiary.','FC1'),
(317,39,NULL,NULL,'FD',NULL,NULL,'pl','Piąty i dziesiąty krok','Dyskusja nad piątym i dziesiątym krokiem Anonimowych Narkomanów','FC1'),
(318,40,NULL,NULL,'AB',NULL,'QA','pl','Temat z koszyka','Temat mityngu wybierany jest spośród zaproponowanych niejawnie przez grupę.','FC1'),
(319,41,NULL,NULL,'ME',NULL,'MED','pl','Medytacja','Uczestnicy tego mityngu zachęcani są do wzięcia udziału w cichej medytacji.','FC1'),
(320,42,NULL,NULL,'RA',NULL,'RA','pl','Ograniczone uczestnictwo','Miejsce odbywania się mityngu nakłada ograniczenia na to, kto może wziąć udział w mityngu.','FC3'),
(321,43,NULL,NULL,'QA',NULL,'QA','pl','Pytania i odpowiedzi','Uczestnicy mogą zadawać pytania i oczekiwać odpowiedzi od innych uczestników.','FC1'),
(322,44,NULL,NULL,'CW',NULL,'CW','pl','Dzieci mile widziane','Dzieci są mile widziane.','FC3'),
(323,45,NULL,NULL,'CP',NULL,'CPT','pl','Koncepcje','Mityng koncentruje się na dyskusji nad Dwunastoma Koncepcjami Anonimowych Narkomanów.','FC1'),
(324,46,NULL,NULL,'FIN',NULL,'LANG','pl','Fiński','Mityng odbywa się w języku fińskim','LANG'),
(325,47,NULL,NULL,'ENG',NULL,'LANG','pl','Anglojęzyczny','Mityng odbywa się w języku angielskim.','LANG'),
(326,48,NULL,NULL,'PER',NULL,'LANG','pl','Perski','Mityng odbywa się w języku perskim','LANG'),
(327,49,NULL,NULL,'L/R',NULL,'LANG','pl','Litewski/rosyjski','Mityng odbywa się w języku litewskim/rosyjskim','LANG'),
(328,51,NULL,NULL,'LC',NULL,'LC','pl','Życie w czystości','Mityng koncentruje się na dyskusji nad tekstem z \"Życie w czystości: Podróż trwa nadal\".','FC1'),
(329,52,NULL,NULL,'GP',NULL,'GP','pl','Guiding Principles','Mityng koncentruje się na dyskusji nad tekstem z \"Guiding Principles - The Spirit of Our Traditions\".','FC1'),
(330,54,NULL,NULL,'VM',NULL,'VM','pl','Virtual Meeting','Meets Virtually','FC2'),
(331,55,NULL,NULL,'TC',NULL,'TC','pl','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(332,56,NULL,NULL,'HY',NULL,'HYBR','pl','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(333,1,NULL,NULL,'RC',NULL,'BEG','pt','Recém-chegados','Esta reunião tem foco nas necessidades de novos membros em NA.','FC3'),
(334,2,NULL,NULL,'BL',NULL,'LANG','pt','Bilíngue','Reunião pode acontecer em duas línguas além de Português.','LANG'),
(335,3,NULL,NULL,'TB',NULL,'BT','pt','Texto Básico','Esta reunião tem foco no debate sobre o Texto Básico de Narcóticos Anônimos.','FC1'),
(336,4,NULL,NULL,'F',NULL,'CLOSED','pt','Fechada','Esta reunião fechada para não adictos. Você deve ir apenas se acredita ter problemas com abuso de substâncias.','O'),
(337,5,NULL,NULL,'FF',NULL,'CH','pt','Fechada em feriados','Esta reunião acontece em local que geralmente é fechado em feirados.','FC3'),
(338,6,NULL,NULL,'VL',NULL,'CAN','pt','Luz de velas','Esta reunião acontece à luz de velas.','FC2'),
(339,7,NULL,NULL,'CA',NULL,'','pt','Criança sob supervisão','Bem-comportadas, crianças sob supervisão são bem-vindas.','FC3'),
(340,8,NULL,NULL,'D',NULL,'DISC','pt','Discussão','Esta reunião convida a participação de todos.','FC1'),
(341,9,NULL,NULL,'ES',NULL,'LANG','pt','Espanhol','Esta reunião acontece em Espanhol.','LANG'),
(342,10,NULL,NULL,'LGBT',NULL,'GL','pt','LGBTQ+','Reunião de interesse LGBTQ+ em NA.','FC3'),
(343,11,NULL,NULL,'DC',NULL,NULL,'pt','Doença Crônica','Esta reunião tem foco nos interesses especiais de pessoas sofrendo de doenças crônicas.','FC1'),
(344,12,NULL,NULL,'IP',NULL,'PI','pt','Panfleto Informativo','Esta reunião tem foco na discussão sobre um ou mais IPs ou Panfletos Informativos.','FC1'),
(345,13,NULL,NULL,'FUN',NULL,'IW','pt','Funciona - Como e Por quê','Esta reunião tem foco na discussão do texto do livro Funciona - Como e Por quê.','FC1'),
(346,14,NULL,NULL,'SPH',NULL,'JFT','pt','Só Por Hoje','Esta reunião tem foco na discussão do texto do livro Só Por Hoje.','FC1'),
(347,15,NULL,NULL,'H',NULL,'M','pt','Homens','Reunião de interesse masculino em NA','FC3'),
(348,16,NULL,NULL,'PC',NULL,'NC','pt','Proibido crianças','Por gentileza não trazer crianças a essa reunião.','FC3'),
(349,17,NULL,NULL,'A',NULL,'OPEN','pt','Aberta','Esta reunião é aberta para adictos e não-adictos. Todos são bem-vindos.','O'),
(350,18,NULL,NULL,'Ind',NULL,NULL,'pt','Indicação','Esta reunião tem um formato que consiste que cada pessoa que partilha escolhe a próxima pessoa a partilhar.','FC1'),
(351,19,NULL,NULL,'FR',NULL,'VAR','pt','Formato Rotativo','Esta reunião muda seu formato a cada reunião.','FC1'),
(352,20,NULL,NULL,'Rr',NULL,NULL,'pt','Round Robin','Esta reunião tem um formato fixo de partilha (geralmente em círculo.)','FC1'),
(353,21,NULL,NULL,'CV',NULL,NULL,'pt','Câmera de vigilância','Esta reunião acontece em ambiente que tem câmeras de vigilância.','FC2'),
(354,22,NULL,NULL,'TD',NULL,'S-D','pt','Temática/Discussão','Esta reunião tem um orador, em seguida é aberta a participação dos membros','FC1'),
(355,23,NULL,NULL,'EP',NULL,'SWG','pt','Estudo de Passos','Esta reunião é de estudo dos passos através do Guia Para Trabalhar os Passos de NA.','FC1'),
(356,24,NULL,NULL,'LS',NULL,NULL,'pt','LSB','Esta reunião acontece com ajuda de intérprete de LIBRAS (Língua Brasileira de Sinais).','FC2'),
(357,26,NULL,NULL,'TM',NULL,'SPK','pt','Temática','Esta reunião é do tipo temática. Não há participação dos membros na discussão.','FC1'),
(358,27,NULL,NULL,'PS',NULL,'STEP','pt','Passos','Esta reunião é de discussão dos 12 Passos de NA.','FC1'),
(359,28,NULL,NULL,'TP',NULL,NULL,'pt','Tempo de Partilha','Esta reunião tem seu tempo de partilha controlado por relógio.','FC1'),
(360,29,NULL,NULL,'To',NULL,'TOP','pt','Tópico','Esta reunião é baseada em tópico escolhida por um orador ou por consciência de grupo.','FC1'),
(361,30,NULL,NULL,'Tr',NULL,'TRAD','pt','Tradições','Esta reunião tem foco em discussão das 12 Tradições de NA.','FC1'),
(362,31,NULL,NULL,'TW',NULL,'TRAD','pt','Workshop de Tradições','Esta reunião envolve uma discussão mais detalhada de uma ou mais das Tradições de N.A.','FC1'),
(363,32,NULL,NULL,'M',NULL,'W','pt','Mulheres','Reunião de interesse feminino em NA.','FC3'),
(364,33,NULL,NULL,'CadT',NULL,'WCHR','pt','Cadeirante Total','Esta reunião tem acesso total a cadeirantes.','FC2'),
(365,34,NULL,NULL,'Jv',NULL,'Y','pt','Jovens','Esta reunião tem foco nos interesses de membros jovens em NA.','FC3'),
(366,35,NULL,NULL,'UP',NULL,NULL,'pt','Último Partilhar','Sem duração fixa. A reunião continua até todos os presentes partilharem.','FC1'),
(367,36,NULL,NULL,'EL',NULL,'LIT','pt','Estudo de Literatura','Reunião de estudo de literaturas aprovadas de NA','FC1'),
(368,37,NULL,NULL,'NF',NULL,'NS','pt','Proibido Fumar','Não é permitido fumar nessa reunião.','FC1'),
(369,38,NULL,NULL,'Ag',NULL,NULL,'pt','Agnóstico','Destinada a pessoas com diferentes graus de fé.','FC1'),
(370,39,NULL,NULL,'QD',NULL,NULL,'pt','Quinto e Décimo','Reunião de discussão sobre o Quinto e Décimo Passos','FC1'),
(371,40,NULL,NULL,'ST',NULL,'QA','pt','Sorteio de Tópico','Um tópico é escolhido através de sugestões sorteadas.','FC1'),
(372,41,NULL,NULL,'ME',NULL,'MED','pt','Meditação','Esta reunião incentiva seus participantes a se envolverem em meditação silenciosa.','FC1'),
(373,42,NULL,NULL,'AR',NULL,'RA','pt','Acesso Restrito','Esta reunião esta em local que impõe restrição de acesso às pessoas.','FC3'),
(374,43,NULL,NULL,'PR',NULL,'QA','pt','Perguntas e Respostas','Os participantes podem fazer perguntas e esperar respostas dos membros do grupo.','FC1'),
(375,44,NULL,NULL,'PC',NULL,'CW','pt','Permitido Crianças','Crianças são bem-vindas a essa reunião.','FC3'),
(376,45,NULL,NULL,'Con',NULL,'CPT','pt','Conceitos','Esta reunião tem foco na discussão dos Doze Conceitos de NA.','FC1'),
(377,46,NULL,NULL,'FIN',NULL,'LANG','pt','Filandês','Reunião em língua filandesa','LANG'),
(378,47,NULL,NULL,'ENG',NULL,'LANG','pt','Inglês','Reunião em língua inglesa.','LANG'),
(379,48,NULL,NULL,'PER',NULL,'LANG','pt','Persa','Reunião em língua persa','LANG'),
(380,49,NULL,NULL,'L/R',NULL,'LANG','pt','Lituano/Russo','Reunião em Lituano/Russo','LANG'),
(381,51,NULL,NULL,'VL',NULL,'LC','pt','Vivendo Limpo','Esta é uma reunião de discussão do livro Vivendo Limpo-A Jornada Continua.','FC1'),
(382,52,NULL,NULL,'GP',NULL,'GP','pt','Guia de Princípios','Esta é uma reunião baseada no livro Guia de Princípios - O Espírito das Nossas Tradições .','FC1'),
(383,53,NULL,NULL,'CadP',NULL,'WCHR','pt','Cadeirante Parcial','Esta reunião tem acesso parcial a cadeirante.','FC2'),
(384,54,NULL,NULL,'VM',NULL,'VM','pt','Virtual Meeting','Meets Virtually','FC2'),
(385,55,NULL,NULL,'TC',NULL,'TC','pt','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(386,56,NULL,NULL,'HY',NULL,'HYBR','pt','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(387,1,NULL,NULL,'B',NULL,'BEG','ru','Начинающие','Эта встреча посвящена потребностям новых членов NA.','FC3'),
(388,2,NULL,NULL,'BL',NULL,'LANG','ru','Двуязычное','На этом совещании могут присутствов Базового Текста Анонимных Наркоманов','LANG'),
(389,4,NULL,NULL,'C',NULL,'CLOSED','ru','Закрытая','Эта встреча закрыта для не наркоманов. Вам следует присутствовать только в том случае, если вы считаете, что у вас могут быть проблемы со злоупотреблением психоактивными веществами.','O'),
(390,5,NULL,NULL,'CH',NULL,'CH','ru','Закрыто по праздникам','Эта встреча собирается в учреждении, которое обычно закрыто в праздничные дни.','FC3'),
(391,6,NULL,NULL,'CL',NULL,'CAN','ru','Искусственное освещение','Эта встреча проводится при свечах.','FC2'),
(392,7,NULL,NULL,'CS',NULL,'','ru','Дети под присмотром','Добро пожаловать, хорошо воспитанные дети приветствуются.','FC3'),
(393,8,NULL,NULL,'D',NULL,'DISC','ru','Обсуждение','Эта встреча приглашает к участию всех участников.','FC1'),
(394,9,NULL,NULL,'ES',NULL,'LANG','ru','Испанский','Эта встреча проводится на испанском языке.','LANG'),
(395,10,NULL,NULL,'GL',NULL,'GL','ru','Геи / Лесбиянки / трансгендеры','Эта встреча посвящена потребностям геев, лесбиянок и транссексуальных членов АН.','FC3'),
(396,11,NULL,NULL,'IL',NULL,NULL,'ru','Болезнь','Эта встреча посвящена потребностям членов АН с хроническим заболеванием.','FC1'),
(397,12,NULL,NULL,'IP',NULL,'IP','ru','Информационная брошюра','Эта встреча посвящена обсуждению одной или нескольких информационных брошюр.','FC1'),
(398,13,NULL,NULL,'IW',NULL,'IW','ru','Это работает - как и почему','Эта встреча посвящена обсуждению текста «Как это работает - как и почему».','FC1'),
(399,14,NULL,NULL,'JT',NULL,'JFT','ru','Только сегодня','Эта встреча посвящена обсуждению текста \"Только Сегодня\"','FC1'),
(400,15,NULL,NULL,'M',NULL,'M','ru','Мужчины','Эта встреча предназначена только для мужчин.','FC3'),
(401,16,NULL,NULL,'NC',NULL,'NC','ru','Без детей','Пожалуйста, не приводите детей на эту встречу.','FC3'),
(402,17,NULL,NULL,'O',NULL,'OPEN','ru','Открытая','Эта встреча открыта как для наркоманов, так и для не наркоманов. Все приветствуются.','O'),
(403,18,NULL,NULL,'Pi',NULL,NULL,'ru','Питч','Эта встреча имеет формат, который состоит из каждого участника, который разделяет выбор следующего участника.','FC1'),
(404,19,NULL,NULL,'RF',NULL,'VAR','ru','Ротация','Эта встреча имеет формат, который изменяется для каждой встречи.','FC1'),
(405,20,NULL,NULL,'Rr',NULL,NULL,'ru','Говорим по кругу','Эта встреча имеет фиксированный порядок обмена опытом (высказывания по кругу.)','FC1'),
(406,21,NULL,NULL,'SC',NULL,NULL,'ru','Камеры наблюдения','Эта встреча проводится в учреждении с камерами наблюдения.','FC2'),
(407,22,NULL,NULL,'SD',NULL,'S-D','ru','Спикерская / Обсуждение','Это спикерская, а затем время для обсуждений.','FC1'),
(408,23,NULL,NULL,'SG',NULL,'SWG','ru','Руководство по Шагам АН','Эта встреча посвящена обсуждению текста руководства по шагам АН.','FC1'),
(409,24,NULL,NULL,'SL',NULL,NULL,'ru','Для глухих','Эта встреча предоставляет переводчика американского языка жестов (ASL) для глухих.','FC2'),
(410,26,NULL,NULL,'So',NULL,'SPK','ru','Только спикерская','Только спикерская. Другие участники не участвуют в обсуждении.','FC1'),
(411,27,NULL,NULL,'St',NULL,'STEP','ru','Шаги','Эта встреча посвящена обсуждению Двенадцати Шагов АН.','FC1'),
(412,28,NULL,NULL,'Ti',NULL,NULL,'ru','Таймер','Время этой встречи ограничено таймером.','FC1'),
(413,29,NULL,NULL,'To',NULL,'TOP','ru','Тема','Эта встреча основана на теме, выбранной ведущим или групповым.','FC1'),
(414,30,NULL,NULL,'Tr',NULL,'TRAD','ru','Традиции','Эта встреча посвящена обсуждению Двенадцати Традиций АН.','FC1'),
(415,31,NULL,NULL,'TW',NULL,'TRAD','ru','Мастерская Традиций','Эта встреча включает в себя подробное обсуждение одной или нескольких из двенадцати традиций А.Н.','FC1'),
(416,32,NULL,NULL,'W',NULL,'W','ru','Женская','Эта встреча предназначена для участия только женщин.','FC3'),
(417,33,NULL,NULL,'WC',NULL,'WCHR','ru','Инвалидное кресло','Эта встреча доступна для инвалидов.','FC2'),
(418,34,NULL,NULL,'YP',NULL,'Y','ru','Молодые люди','Эта встреча ориентирована на потребности молодых членов АН.','FC3'),
(419,35,NULL,NULL,'OE',NULL,NULL,'ru','Неограниченная','Нет фиксированной продолжительности. Встреча продолжается до тех пор, пока все присутствующие не смогут поделиться опытом.','FC1'),
(420,36,NULL,NULL,'BK',NULL,'LIT','ru','Книжное обучение','Утвержденные книги А.Н.','FC1'),
(421,37,NULL,NULL,'NS',NULL,'NS','ru','Не курить','Курение запрещено на этой встрече.','FC1'),
(422,38,NULL,NULL,'Ag',NULL,NULL,'ru','Агностики','Предназначен для людей с разной степенью веры.','FC1'),
(423,39,NULL,NULL,'FD',NULL,NULL,'ru','Пятый и Десятый','Обсуждение пятого шага и десятого шага','FC1'),
(424,40,NULL,NULL,'AB',NULL,'QA','ru','Коробочка','Тема выбирается из предложений, помещенных в коробочку.','FC1'),
(425,41,NULL,NULL,'ME',NULL,'MED','ru','Медитация','Эта встреча поощряет ее участников заниматься тихой медитацией.','FC1'),
(426,42,NULL,NULL,'RA',NULL,'RA','ru','Ограниченная Посещаемость','Эта встреча накладывает ограничения на посетителей.','FC3'),
(427,43,NULL,NULL,'QA',NULL,'QA','ru','Вопрос и ответ','Участники могут задавать вопросы и ожидать ответов от членов группы.','FC1'),
(428,44,NULL,NULL,'CW',NULL,'CW','ru','Дети - добро пожаловать','Дети приветствуются на этой встрече.','FC3'),
(429,45,NULL,NULL,'CP',NULL,'CPT','ru','Концепции','Эта встреча посвящена обсуждению двенадцати концепций А.Н.','FC1'),
(430,46,NULL,NULL,'FIN',NULL,'LANG','ru','Финский','финноязычная встреча','LANG'),
(431,47,NULL,NULL,'ENG',NULL,'LANG','ru','Англогоязычный','На его собрании могут присутствовать носители английского языка.','LANG'),
(432,48,NULL,NULL,'PER',NULL,'LANG','ru','Персидский','Собрание проводится на Персидском языке','LANG'),
(433,49,NULL,NULL,'L/R',NULL,'LANG','ru','Русский\\литовский','Русскоговорящие собрания АН','LANG'),
(434,51,NULL,NULL,'LC',NULL,'LC','ru','Жить Чистыми','Это обсуждение книги АН «Живи чисто - путешествие продолжается».','FC1'),
(435,52,NULL,NULL,'GP',NULL,'GP','ru','Руководящие принципы','Это обсуждение книги АН «Руководящие принципы - дух наших традиций».','FC1'),
(436,54,NULL,NULL,'VM',NULL,'VM','ru','Виртуальная встреча','Собираемся онлайн','FC2'),
(437,55,NULL,NULL,'TC',NULL,'TC','ru','Временно закрыто','Объект временно закрыт','FC2'),
(438,56,NULL,NULL,'HY',NULL,'HYBR','ru','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(439,4,NULL,NULL,'S',NULL,'CLOSED','sv','Slutet möte','Ett slutet NA möte är för de individer som identifierar sig som beroende eller för de som är osäkra och tror att de kanske har drogproblem.','FC3'),
(440,15,NULL,NULL,'M',NULL,'M','sv','Mansmöte','Detta möte är endast öppet för män.','FC3'),
(441,17,NULL,NULL,'Ö',NULL,'OPEN','sv','Öppet möte','Ett öppet möte är ett NA-möte där vem som helst som är intresserad av hur vi har funnit tillfrisknande från beroendesjukdomen kan närvara.','FC3'),
(442,47,NULL,NULL,'ENG',NULL,NULL,'sv','Engelska','Engelsktalande möte','FC3'),
(443,48,NULL,NULL,'PER',NULL,NULL,'sv','Persiskt','Persiskt möte','FC1'),
(444,32,NULL,NULL,'K',NULL,'W','sv','Kvinnomöte','Detta möte är endast öppet för kvinnor.','FC3'),
(445,33,NULL,NULL,'RL',NULL,'WCHR','sv','Rullstolsvänlig lokal','Detta möte är tillgängligt för rullstolsbundna.','FC2'),
(446,47,NULL,NULL,'ENG',NULL,NULL,'sv','Engelska','Engelsktalande möte','FC3'),
(447,54,NULL,NULL,'VM',NULL,'VM','sv','Virtual Meeting','Meets Virtually','FC2'),
(448,55,NULL,NULL,'TC',NULL,'TC','sv','Temporarily Closed Facility','Facility is Temporarily Closed','FC2'),
(449,56,NULL,NULL,'HY',NULL,'HYBR','sv','Hybrid Meeting','Meets Virtually and In-person','FC2'),
(450,1,NULL,NULL,'B',NULL,'BEG','fa','تازه واردان','این جلسه بر روی نیازهای تازه واردان در معتادان گمنام متمرکز میباشد','FC3'),
(451,2,NULL,NULL,'BL',NULL,'LANG','fa','دو زبانه','این جلسه پذیرای شرکت کنندگان انگلیسی زبان و دیگر زبان ها میباشد','LANG'),
(452,3,NULL,NULL,'BT',NULL,'BT','fa','کتاب پایه','این جلسه متمرکز بر روی بحث درباره کتاب پایه معتادان گمنام میباشد','FC1'),
(453,4,NULL,NULL,'C',NULL,'CLOSED','fa','بسته','این جلسه برای افراد غیر معتاد بسته میباشد. شما تنها اگر فکر میکنید با مواد خدر مشکل دارید میتوانید شرکت کنید','O'),
(454,5,NULL,NULL,'CH',NULL,'CH','fa','بسته در روزهای تعطیل','این جلسات در روزهای تعطیل برگزار نمیگردد','FC3'),
(455,6,NULL,NULL,'CL',NULL,'CAN','fa','شمع روشن','این جلسه بهمراه شمع روشن برگزار میگردد','FC2'),
(456,7,NULL,NULL,'CS',NULL,'','fa','کودکان بی سرپرست','خوش رفتاری','FC3'),
(457,8,NULL,NULL,'D',NULL,'DISC','fa','بحث و گفتگو','این جلسه از تمامی شرکت کنندگان دعوت به بحث میکند','FC1'),
(458,9,NULL,NULL,'ES',NULL,'LANG','fa','اسپانیایی','این جلسه به زبان اسپانیایی برگزار میگردد','LANG'),
(459,10,NULL,NULL,'GL',NULL,'GL','fa','مردان همجنس باز/زنان همجنس باز/تغییر جنسیتی ها','این جلسه به نیازهای همجنس بازان/همجنس خواهان میپردازد','FC3'),
(460,11,NULL,NULL,'IL',NULL,NULL,'fa','بیماران','این جلسه به نیازهای اعضا با بیماری های مزمن متمرکز میباشد','FC1'),
(461,12,NULL,NULL,'IP',NULL,'IP','fa','پمفلت های اطلاعاتی','این جلسه به بررسی و بحث در مورد یک یا چند پمفلت متمرکز میباشد','FC1'),
(462,13,NULL,NULL,'IW',NULL,'IW','fa','چگونگی عملکرد','این جلسه با موضوع بحث در مورد کتاب چگونگی عملکرد برگزار میگردد','FC1'),
(463,14,NULL,NULL,'JT',NULL,'JFT','fa','فقط برای امروز','این جلسه با موضوع بحث درمورد کتاب فقط برای امروز متمرکز میباشد','FC1'),
(464,15,NULL,NULL,'M',NULL,'M','fa','مردان','این جلسه فقط مخصوص آقایان مباشد','FC3'),
(465,16,NULL,NULL,'NC',NULL,'NC','fa','ممنوعیت ورود کودکان','لطفاً کودکان را به این جلسه نیاورید','FC3'),
(466,17,NULL,NULL,'O',NULL,'OPEN','fa','باز','این جلسه برای کلیه اعضا معتاد و همچنین غیر معتادان باز میباشد','O'),
(467,18,NULL,NULL,'Pi',NULL,NULL,'fa','انتخابی','فورمت این جلسه بصورتیست که هر مشارکت کننده میتواند نفر بعدی را جهت مشارکت انتخاب نماید','FC1'),
(468,19,NULL,NULL,'RF',NULL,'VAR','fa','فورمت چرخشی','فورمت این جلسه در هر جلسه متغیر میباشد','FC1'),
(469,20,NULL,NULL,'Rr',NULL,NULL,'fa','مشارکت موضوع دار','این جلسه دارای یکسری موضوعات خاص میباشد (معمولاً بصورت چرخشی)','FC1'),
(470,21,NULL,NULL,'SC',NULL,NULL,'fa','دوربین مداربسته','این جلسه در مکانهای مجهز به دوربین مدار بسته برگزار میگردد','FC2'),
(471,22,NULL,NULL,'SD',NULL,'S-D','fa','سخنرانی/بحث','این جلسه توسط یک سخنران گردانندگی میگردد','FC1'),
(472,23,NULL,NULL,'SG',NULL,'SWG','fa','راهنمای کارکرد قدم','این جلسه با موضوع بررسی و بحث در مورد کتاب راهنمای کاکرد قدم برگزار میگردد','FC1'),
(473,24,NULL,NULL,'SL',NULL,NULL,'fa','تفسیر به زبان انگلیسی برای ناشنوایان','این جلسه بهمراه مفسر انگلیسی برای ناشنوایان برگزار میگردد','FC2'),
(474,26,NULL,NULL,'So',NULL,'SPK','fa','فقط سخنرانی','این جلسه فقط یک سخنران دارد. دیگر شرکت کنندگان حق مشارکت ندارند','FC1'),
(475,27,NULL,NULL,'St',NULL,'STEP','fa','قدم','این جلسه با موضوع بحث درمورد قدم های دوازده گانه معتادان گمنام برگزار میگردد','FC1'),
(476,28,NULL,NULL,'Ti',NULL,NULL,'fa','زمان سنج','در این جلسه زمان مشارکت توسط زمان سنج محاسبه و کنترل میگردد','FC1'),
(477,29,NULL,NULL,'To',NULL,'TOP','fa','موضوع','این جلسه برپایه موضوع انتخابی توسط یک سخنران یا وجدان گروهی برگزار میگردد','FC1'),
(478,30,NULL,NULL,'Tr',NULL,'TRAD','fa','سنت ها','این جلسه با موضوع بحث درمورد سنت های دوازده گانه معتادان گمنام برگزار میگردد','FC1'),
(479,31,NULL,NULL,'TW',NULL,'TRAD','fa','کارگاه سنت ها','این جلسه با موضوع بررسی جزئیاتی یک یاچند سنت معتادان گمنام برگزار میگردد','FC1'),
(480,32,NULL,NULL,'W',NULL,'W','fa','بانوان','این جلسه فقط مخصوص خانم ها مباشد','FC3'),
(481,33,NULL,NULL,'WC',NULL,'WCHR','fa','ویلچر','در این جلسه ویلچر در دسترس میباشد','FC2'),
(482,34,NULL,NULL,'YP',NULL,'Y','fa','جوانان','این جلسه بر روی نیازهای اعضا جوان متمرکز میباشد','FC3'),
(483,35,NULL,NULL,'OE',NULL,NULL,'fa','بی پایان','بدون مدت زمان ثابت. این جلسه تا زمانی که تمامی اعضا درخواست کننده مشارکت، مشارکت نکرده باشند به اتمام نمیرسد','FC1'),
(484,36,NULL,NULL,'BK',NULL,'LIT','fa','کتاب خوانی','کتابخوانی نشریات معتادان گمنام','FC1'),
(485,37,NULL,NULL,'NS',NULL,'NS','fa','مصرف دخانیات ممنوع','مصرف دخانیات در این جلسه ممنوع میباشد','FC1'),
(486,38,NULL,NULL,'Ag',NULL,NULL,'fa','بی اعتقادان','جلسه مخصوص اعضا باهر میزان درجه از اعتقاد','FC1'),
(487,39,NULL,NULL,'FD',NULL,NULL,'fa','پنج و ده','جلسه بحث و بررسی قدم های پنج و ده','FC1'),
(488,40,NULL,NULL,'AB',NULL,'QA','fa','انتخاب موضوع از سبد','انتخاب یک موضوع توسط پیشنهادات ارائه شده در سبد','FC1'),
(489,41,NULL,NULL,'ME',NULL,'MED','fa','مراقبه','این جلسه اعضا شرکت کننده را به مراقبه کامل تشویق مینماید','FC1'),
(490,42,NULL,NULL,'RA',NULL,'RA','fa','محدودیت شرکت کننده','این جلسه دارای محدودیت شرکت کنندگان میباشد','FC3'),
(491,43,NULL,NULL,'QA',NULL,'QA','fa','پرسش و پاسخ','اعضا میتوانند سوالات خود را مطرح نموده و منتظر دریافت پاسخ از دیگر اعضا باشند','FC1'),
(492,44,NULL,NULL,'CW',NULL,'CW','fa','با حضور کودکان','حضور کودکان در این جلسه بلامانع میباشد','FC3'),
(493,45,NULL,NULL,'CP',NULL,'CPT','fa','مفاهیم','این جلسه با موضوع بحث درمورد مفاهیم دوازده گانه معتادان گمنام برگزار میگردد','FC1'),
(494,46,NULL,NULL,'FIN',NULL,'LANG','fa','فنلاندی','جلسه به زبان فنلاندی','LANG'),
(495,47,NULL,NULL,'ENG',NULL,'LANG','fa','انگلیسی','این جلسه میتواند با حضور اعضا انگلیسی زبان نیز برگزار گردد','LANG'),
(496,48,NULL,NULL,'PER',NULL,'LANG','fa','فارسی','جلسه به زبان فارسی','LANG'),
(497,49,NULL,NULL,'L/R',NULL,'LANG','fa','لیتوانیایی/روسی','جلسه به زبان های لیتوانیایی/روسی','LANG'),
(498,51,NULL,NULL,'LC',NULL,'LC','fa','پاک زیستن','این جلسه با موضوع بررسی و بحث در مورد کتاب پاک زیستن - سفر ادامه دارد، برگزار میگردد','FC1'),
(499,52,NULL,NULL,'GP',NULL,'GP','fa','روح سنت ها','این جلسه با موضوع بررسی و بحث در مورد کتاب روح سنت ها برگزار میگردد','FC1');
/*!40000 ALTER TABLE `na_comdef_formats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_meetings_data`
--

DROP TABLE IF EXISTS `na_comdef_meetings_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_meetings_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meetingid_bigint` bigint(20) unsigned NOT NULL,
  `key` varchar(32) NOT NULL,
  `field_prompt` varchar(255) DEFAULT NULL,
  `lang_enum` varchar(7) DEFAULT NULL,
  `visibility` int(11) DEFAULT NULL,
  `data_string` varchar(255) DEFAULT NULL,
  `data_bigint` bigint(20) DEFAULT NULL,
  `data_double` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_bigint` (`data_bigint`),
  KEY `data_double` (`data_double`),
  KEY `meetingid_bigint` (`meetingid_bigint`),
  KEY `lang_enum` (`lang_enum`),
  KEY `key` (`key`),
  KEY `visibility` (`visibility`),
  FULLTEXT KEY `na_comdef_meetings_data_data_string_fulltext` (`data_string`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_meetings_data`
--

LOCK TABLES `na_comdef_meetings_data` WRITE;
/*!40000 ALTER TABLE `na_comdef_meetings_data` DISABLE KEYS */;
INSERT INTO `na_comdef_meetings_data` VALUES
(1,0,'meeting_name','Meeting Name','en',0,'Meeting Name',NULL,NULL),
(2,0,'location_text','Location Name','en',0,'Location Name',NULL,NULL),
(3,0,'location_info','Additional Location Information','en',0,'Additional Location Information',NULL,NULL),
(4,0,'location_street','Street Address','en',0,'Street Address',NULL,NULL),
(5,0,'location_city_subsection','Borough','en',0,'Borough',NULL,NULL),
(6,0,'location_neighborhood','Neighborhood','en',0,'Neighborhood',NULL,NULL),
(7,0,'location_municipality','Town','en',0,'Town',NULL,NULL),
(8,0,'location_sub_province','County','en',0,'County',NULL,NULL),
(9,0,'location_province','State','en',0,'State',NULL,NULL),
(10,0,'location_postal_code_1','Zip Code','en',0,NULL,0,NULL),
(11,0,'location_nation','Nation','en',0,'Nation',NULL,NULL),
(12,0,'comments','Comments','en',0,'Comments',NULL,NULL),
(13,0,'train_lines','Train Lines','en',0,NULL,NULL,NULL),
(14,0,'bus_lines','Bus Lines','en',0,NULL,NULL,NULL),
(15,0,'contact_phone_2','Contact 2 Phone','en',1,'Contact 2 Phone',NULL,NULL),
(16,0,'contact_email_2','Contact 2 Email','en',1,'Contact 2 Email',NULL,NULL),
(17,0,'contact_name_2','Contact 2 Name','en',1,'Contact 2 Name',NULL,NULL),
(18,0,'contact_phone_1','Contact 1 Phone','en',1,'Contact 1 Phone',NULL,NULL),
(19,0,'contact_email_1','Contact 1 Email','en',1,'Contact 1 Email',NULL,NULL),
(20,0,'contact_name_1','Contact 1 Name','en',1,'Contact 1 Name',NULL,NULL),
(21,0,'phone_meeting_number','Phone Meeting Dial-in Number','en',0,'Phone Meeting Dial-in Number',NULL,NULL),
(22,0,'virtual_meeting_link','Virtual Meeting Link','en',0,'Virtual Meeting Link',NULL,NULL),
(23,0,'virtual_meeting_additional_info','Virtual Meeting Additional Info','en',0,'Virtual Meeting Additional Info',NULL,NULL);
/*!40000 ALTER TABLE `na_comdef_meetings_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_meetings_longdata`
--

DROP TABLE IF EXISTS `na_comdef_meetings_longdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_meetings_longdata` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meetingid_bigint` bigint(20) unsigned NOT NULL,
  `key` varchar(32) NOT NULL,
  `field_prompt` varchar(255) DEFAULT NULL,
  `lang_enum` varchar(7) DEFAULT NULL,
  `visibility` int(11) DEFAULT NULL,
  `data_blob` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meetingid_bigint` (`meetingid_bigint`),
  KEY `lang_enum` (`lang_enum`),
  KEY `field_prompt` (`field_prompt`),
  KEY `key` (`key`),
  KEY `visibility` (`visibility`),
  FULLTEXT KEY `na_comdef_meetings_longdata_data_blob_fulltext` (`data_blob`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_meetings_longdata`
--

LOCK TABLES `na_comdef_meetings_longdata` WRITE;
/*!40000 ALTER TABLE `na_comdef_meetings_longdata` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_comdef_meetings_longdata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_meetings_main`
--

DROP TABLE IF EXISTS `na_comdef_meetings_main`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_meetings_main` (
  `id_bigint` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `root_server_id` bigint(20) unsigned DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `worldid_mixed` varchar(255) DEFAULT NULL,
  `shared_group_id_bigint` bigint(20) DEFAULT NULL,
  `service_body_bigint` bigint(20) unsigned NOT NULL,
  `weekday_tinyint` tinyint(3) unsigned DEFAULT NULL,
  `venue_type` tinyint(3) unsigned DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `duration_time` time DEFAULT NULL,
  `time_zone` varchar(40) DEFAULT NULL,
  `formats` varchar(255) DEFAULT NULL,
  `lang_enum` varchar(7) DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `published` tinyint(4) NOT NULL DEFAULT 0,
  `email_contact` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_bigint`),
  KEY `weekday_tinyint` (`weekday_tinyint`),
  KEY `venue_type` (`venue_type`),
  KEY `service_body_bigint` (`service_body_bigint`),
  KEY `start_time` (`start_time`),
  KEY `duration_time` (`duration_time`),
  KEY `time_zone` (`time_zone`),
  KEY `formats` (`formats`),
  KEY `lang_enum` (`lang_enum`),
  KEY `worldid_mixed` (`worldid_mixed`),
  KEY `shared_group_id_bigint` (`shared_group_id_bigint`),
  KEY `longitude` (`longitude`),
  KEY `latitude` (`latitude`),
  KEY `published` (`published`),
  KEY `email_contact` (`email_contact`),
  KEY `root_server_id_source_id` (`root_server_id`,`source_id`),
  CONSTRAINT `na_comdef_meetings_main_root_server_id_foreign` FOREIGN KEY (`root_server_id`) REFERENCES `na_root_servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_meetings_main`
--

LOCK TABLES `na_comdef_meetings_main` WRITE;
/*!40000 ALTER TABLE `na_comdef_meetings_main` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_comdef_meetings_main` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_service_bodies`
--

DROP TABLE IF EXISTS `na_comdef_service_bodies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_service_bodies` (
  `id_bigint` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `root_server_id` bigint(20) unsigned DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `name_string` varchar(255) NOT NULL,
  `description_string` text NOT NULL,
  `lang_enum` varchar(7) NOT NULL DEFAULT 'en',
  `worldid_mixed` varchar(255) DEFAULT NULL,
  `kml_file_uri_string` varchar(255) DEFAULT NULL,
  `principal_user_bigint` bigint(20) unsigned DEFAULT NULL,
  `editors_string` varchar(255) DEFAULT NULL,
  `uri_string` varchar(255) DEFAULT NULL,
  `sb_type` varchar(32) DEFAULT NULL,
  `sb_owner` bigint(20) unsigned DEFAULT NULL,
  `sb_owner_2` bigint(20) unsigned DEFAULT NULL,
  `sb_meeting_email` varchar(255) NOT NULL,
  PRIMARY KEY (`id_bigint`),
  KEY `worldid_mixed` (`worldid_mixed`),
  KEY `kml_file_uri_string` (`kml_file_uri_string`),
  KEY `principal_user_bigint` (`principal_user_bigint`),
  KEY `editors_string` (`editors_string`),
  KEY `lang_enum` (`lang_enum`),
  KEY `uri_string` (`uri_string`),
  KEY `sb_type` (`sb_type`),
  KEY `sb_owner` (`sb_owner`),
  KEY `sb_owner_2` (`sb_owner_2`),
  KEY `sb_meeting_email` (`sb_meeting_email`),
  KEY `root_server_id_source_id` (`root_server_id`,`source_id`),
  CONSTRAINT `na_comdef_service_bodies_root_server_id_foreign` FOREIGN KEY (`root_server_id`) REFERENCES `na_root_servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_service_bodies`
--

LOCK TABLES `na_comdef_service_bodies` WRITE;
/*!40000 ALTER TABLE `na_comdef_service_bodies` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_comdef_service_bodies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_comdef_users`
--

DROP TABLE IF EXISTS `na_comdef_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_comdef_users` (
  `id_bigint` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_level_tinyint` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `name_string` varchar(255) NOT NULL,
  `description_string` text NOT NULL,
  `email_address_string` varchar(255) NOT NULL,
  `login_string` varchar(255) NOT NULL,
  `password_string` varchar(255) NOT NULL,
  `last_access_datetime` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lang_enum` varchar(7) NOT NULL DEFAULT 'en',
  `owner_id_bigint` bigint(20) NOT NULL DEFAULT -1,
  PRIMARY KEY (`id_bigint`),
  UNIQUE KEY `login_string` (`login_string`),
  KEY `user_level_tinyint` (`user_level_tinyint`),
  KEY `email_address_string` (`email_address_string`),
  KEY `last_access_datetime` (`last_access_datetime`),
  KEY `lang_enum` (`lang_enum`),
  KEY `owner_id_bigint` (`owner_id_bigint`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_comdef_users`
--

LOCK TABLES `na_comdef_users` WRITE;
/*!40000 ALTER TABLE `na_comdef_users` DISABLE KEYS */;
INSERT INTO `na_comdef_users` VALUES
(1,1,'Server Administrator','Main Server Administrator','','serveradmin','$2y$10$wwu/pk1IUY3X3ppzoJvDJ.EWqIgZ1A4qyZHCrFNaN0r9RLHagTZGG','1970-01-01 00:00:00','en',-1);
/*!40000 ALTER TABLE `na_comdef_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_migrations`
--

DROP TABLE IF EXISTS `na_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_migrations`
--

LOCK TABLES `na_migrations` WRITE;
/*!40000 ALTER TABLE `na_migrations` DISABLE KEYS */;
INSERT INTO `na_migrations` VALUES
(1,'1900_01_01_000000_create_sessions_table',1),
(2,'1901_01_01_000000_legacy_migrations',1),
(3,'1902_01_01_000000_create_initial_schema',1),
(4,'1903_01_01_000000_innodb_db_version',1),
(5,'1904_01_01_000000_innodb_meetings_data',1),
(6,'1905_01_01_000000_innodb_meetings_longdata',1),
(7,'1906_01_01_000000_innodb_formats',1),
(8,'1907_01_01_000000_innodb_meetings_main',1),
(9,'1908_01_01_000000_innodb_service_bodies',1),
(10,'1909_01_01_000000_innodb_users',1),
(11,'1910_01_01_000000_innodb_changes',1),
(12,'1911_01_01_000000_trim_whitespace',1),
(13,'2019_12_14_000001_create_personal_access_tokens_table',1),
(14,'2023_05_16_223943_format_types',1),
(15,'2024_06_12_164303_fix_meeting_lang_enum',1),
(16,'2024_07_20_203802_fix_admin_user_owners',1);
/*!40000 ALTER TABLE `na_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_personal_access_tokens`
--

DROP TABLE IF EXISTS `na_personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `na_personal_access_tokens_token_unique` (`token`),
  KEY `na_personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_personal_access_tokens`
--

LOCK TABLES `na_personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `na_personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_root_server_statistics`
--

DROP TABLE IF EXISTS `na_root_server_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_root_server_statistics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `root_server_id` bigint(20) unsigned NOT NULL,
  `num_zones` int(10) unsigned NOT NULL,
  `num_regions` int(10) unsigned NOT NULL,
  `num_areas` int(10) unsigned NOT NULL,
  `num_groups` int(10) unsigned NOT NULL,
  `num_total_meetings` int(10) unsigned NOT NULL,
  `num_in_person_meetings` int(10) unsigned NOT NULL,
  `num_virtual_meetings` int(10) unsigned NOT NULL,
  `num_hybrid_meetings` int(10) unsigned NOT NULL,
  `num_unknown_meetings` int(10) unsigned NOT NULL,
  `is_latest` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `na_root_server_statistics_root_server_id_foreign` (`root_server_id`),
  KEY `is_latest` (`is_latest`),
  KEY `is_latest_root_server_id` (`is_latest`,`root_server_id`),
  CONSTRAINT `na_root_server_statistics_root_server_id_foreign` FOREIGN KEY (`root_server_id`) REFERENCES `na_root_servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_root_server_statistics`
--

LOCK TABLES `na_root_server_statistics` WRITE;
/*!40000 ALTER TABLE `na_root_server_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_root_server_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_root_servers`
--

DROP TABLE IF EXISTS `na_root_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_root_servers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `server_info` text DEFAULT NULL,
  `last_successful_import` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_root_servers`
--

LOCK TABLES `na_root_servers` WRITE;
/*!40000 ALTER TABLE `na_root_servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_root_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_sessions`
--

DROP TABLE IF EXISTS `na_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `na_sessions_user_id_index` (`user_id`),
  KEY `na_sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_sessions`
--

LOCK TABLES `na_sessions` WRITE;
/*!40000 ALTER TABLE `na_sessions` DISABLE KEYS */;
INSERT INTO `na_sessions` VALUES
('4lISohRRKEYSvJpxRYMw94j06dw6WNagcbJB9Gmo',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Safari/605.1.15','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVk5Lc1BCUG9pNjJENXUxU0YwQXljYjFZbUJJdzlEajJFZTdNZzhxRyI7czo5OiJsYW5nX2VudW0iO3M6MjoiZW4iO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbWFpbl9zZXJ2ZXIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1732564880);
/*!40000 ALTER TABLE `na_sessions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2024-11-25 20:02:57
