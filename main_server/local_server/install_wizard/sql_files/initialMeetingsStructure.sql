CREATE TABLE `%%PREFIX%%_comdef_meetings_main` (
  `id_bigint` bigint(20) unsigned NOT NULL auto_increment,
  `worldid_mixed` varchar(255) default NULL,
  `shared_group_id_bigint` bigint(20) default NULL,
  `service_body_bigint` bigint(20) unsigned NOT NULL,
  `weekday_tinyint` tinyint(4) unsigned default NULL,
  `start_time` time default NULL,
  `duration_time` time default NULL,
  `time_zone` varchar(40) default NULL,
  `formats` varchar(255) default NULL,
  `lang_enum` varchar(7) default NULL,
  `longitude` double default NULL,
  `latitude` double default NULL,
  `published` tinyint(4) NOT NULL default '0',
  `email_contact` varchar(255) default NULL,
  PRIMARY KEY  (`id_bigint`),
  KEY `weekday_tinyint` (`weekday_tinyint`),
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
  KEY `email_contact` (`email_contact`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
CREATE TABLE `%%PREFIX%%_comdef_meetings_data` (
  `meetingid_bigint` bigint(20) unsigned NOT NULL,
  `key` varchar(32) NOT NULL,
  `field_prompt` tinytext,
  `lang_enum` varchar(7) default NULL,
  `visibility` int(1) default NULL,
  `data_string` tinytext,
  `data_bigint` bigint(20) default NULL,
  `data_double` double default NULL,
  KEY `data_bigint` (`data_bigint`),
  KEY `data_double` (`data_double`),
  KEY `meetingid_bigint` (`meetingid_bigint`),
  KEY `lang_enum` (`lang_enum`),
  KEY `key` (`key`),
  KEY `visibility` (`visibility`)
) DEFAULT CHARSET=utf8;
CREATE TABLE `%%PREFIX%%_comdef_meetings_longdata` (
  `meetingid_bigint` bigint(20) unsigned NOT NULL,
  `key` varchar(32) NOT NULL,
  `field_prompt` varchar(255) default NULL,
  `lang_enum` varchar(7) default NULL,
  `visibility` int(1) default NULL,
  `data_longtext` text,
  `data_blob` blob,
  KEY `meetingid_bigint` (`meetingid_bigint`),
  KEY `lang_enum` (`lang_enum`),
  KEY `field_prompt` (`field_prompt`),
  KEY `key` (`key`),
  KEY `visibility` (`visibility`)
) DEFAULT CHARSET=utf8;
