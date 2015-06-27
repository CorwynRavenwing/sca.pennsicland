CREATE TABLE `user_information` (
`user_id` int(10) unsigned NOT NULL auto_increment
`user_name` varchar(20) NOT NULL default ''
`legal_name` varchar(100) NOT NULL default ''
`alias` varchar(100) NOT NULL default ''
`active_account` enum('F','T') default NULL
`temporary_account` enum('F','T') default NULL
`password` varchar(32) default NULL
`password_salt` varchar(32) default NULL
`password_hint` varchar(100) default NULL
`password_answer` varchar(100) default NULL
`last_update` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
`time_created` timestamp NOT NULL default '0000-00-00 00:00:00'
`street_1` varchar(100) NOT NULL default ''
`street_2` varchar(100) NOT NULL default ''
`city` varchar(45) NOT NULL default ''
`state` varchar(45) NOT NULL default ''
`postal_code` varchar(20) NOT NULL default ''
`country` varchar(45) NOT NULL default 'United States'
`email_address` varchar(100) NOT NULL default ''
`format` enum('text','html') NOT NULL default 'text'
`phone_number` varchar(12) NOT NULL default ''
`extension` varchar(5) NOT NULL default ''
PRIMARY KEY  (`user_id`)
UNIQUE KEY `idx_user_name` (`user_name`)
)