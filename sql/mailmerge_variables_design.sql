CREATE TABLE `mailmerge_variables` (
  `mailmerge_variable_id` bigint(20) NOT NULL auto_increment,
  `variable_name` varchar(45) default NULL,
  `mailmerge_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mailmerge_variable_id`)
)
