CREATE TABLE `land_group_history` (
  `group_history_id` int(11) unsigned NOT NULL auto_increment,
  `group_id` int(11) unsigned NOT NULL default '0',
  `year` int(11) unsigned NOT NULL default '0',
  `block_id` int(11) unsigned NOT NULL default '0',
  `attendance` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_history_id`),
  KEY `ind_group_id` (`group_id`),
  KEY `ind_block_id` (`block_id`),
  KEY `ind_year` (`year`)
)
