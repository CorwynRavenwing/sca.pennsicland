CREATE TABLE `cooper_data` (
  `cooper_data_id` int(11) NOT NULL auto_increment,
  `group_name` varchar(100) NOT NULL,
  `penn_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `sca_name` varchar(100) NOT NULL,
  `previous_group` varchar(100) NOT NULL,
  `garbage_collect` tinyint(1) NOT NULL,
  `modified_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`cooper_data_id`),
  KEY `group_name` (`group_name`),
  KEY `penn_number` (`penn_number`)
) ENGINE=MyISAM AUTO_INCREMENT=94079 DEFAULT CHARSET=latin1