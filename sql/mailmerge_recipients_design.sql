CREATE TABLE `mailmerge_recipients` (
`mailmerge_recipient_id` bigint(20) NOT NULL auto_increment
`user_id` bigint(20) NOT NULL default '0'
`group_id` int(11) NOT NULL
`block_id` int(11) NOT NULL
`letter_sent` int(10) unsigned NOT NULL default '0'
`from_email` varchar(45) NOT NULL
`letter_body` text NOT NULL
`letter_subject` varchar(255) NOT NULL default ''
`mailmerge_id` bigint(20) unsigned NOT NULL default '0'
`secondary_id` bigint(20) unsigned NOT NULL default '0'
`display_value` varchar(255) NOT NULL default ''
`selected` enum('0','1') NOT NULL default '0'
`email_address` varchar(45) NOT NULL default ''
PRIMARY KEY  (`mailmerge_recipient_id`)
)