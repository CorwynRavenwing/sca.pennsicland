CREATE TABLE `user_mail` (
  `user_mail_id` bigint(20) unsigned NOT NULL auto_increment,
  `recipient_user_id` bigint(20) unsigned NOT NULL default '0',
  `recipient_group_id` bigint(20) unsigned NOT NULL default '0',
  `sender_user_id` bigint(20) unsigned NOT NULL default '0',
  `sender_group_id` bigint(20) unsigned NOT NULL default '0',
  `time_sent` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `body` text NOT NULL,
  `subject` varchar(200) NOT NULL default '',
  `mail_error` varchar(25) default NULL,
  PRIMARY KEY  (`user_mail_id`)
)
