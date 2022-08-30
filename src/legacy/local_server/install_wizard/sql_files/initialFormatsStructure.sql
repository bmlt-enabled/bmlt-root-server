CREATE TABLE `%%PREFIX%%_comdef_formats` (
  `shared_id_bigint` bigint(20) unsigned NOT NULL,
  `key_string` varchar(255) default NULL,
  `icon_blob` longblob,
  `worldid_mixed` varchar(255) default NULL,
  `lang_enum` varchar(7) NOT NULL default 'en',
  `name_string` tinytext,
  `description_string` text,
  `format_type_enum` varchar(7) default 'FC1',
  KEY `shared_id_bigint` (`shared_id_bigint`),
  KEY `worldid_mixed` (`worldid_mixed`),
  KEY `format_type_enum` (`format_type_enum`),
  KEY `lang_enum` (`lang_enum`),
  KEY `key_string` (`key_string`)
) DEFAULT CHARSET=utf8;
