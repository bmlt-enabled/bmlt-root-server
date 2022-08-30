CREATE TABLE `%%PREFIX%%_comdef_users` (
  `id_bigint` bigint(20) unsigned NOT NULL auto_increment,
  `user_level_tinyint` tinyint(4) unsigned NOT NULL default '0',
  `name_string` tinytext NOT NULL,
  `description_string` text NOT NULL,
  `email_address_string` varchar(255) NOT NULL,
  `login_string` varchar(255) NOT NULL,
  `password_string` varchar(255) NOT NULL,
  `last_access_datetime` datetime NOT NULL default '1970-01-01 00:00:00',
  `lang_enum` varchar(7) NOT NULL default 'en',
  `owner_id_bigint` BIGINT(20) NOT NULL default -1,
  PRIMARY KEY  (`id_bigint`),
  UNIQUE KEY `login_string` (`login_string`),
  KEY `user_level_tinyint` (`user_level_tinyint`),
  KEY `email_address_string` (`email_address_string`),
  KEY `last_access_datetime` (`last_access_datetime`),
  KEY `lang_enum` (`lang_enum`),
  KEY `owner_id_bigint` (`owner_id_bigint`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
