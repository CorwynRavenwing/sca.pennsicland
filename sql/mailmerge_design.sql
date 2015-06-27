CREATE TABLE `mailmerge` (
`mailmerge_id` bigint(20) NOT NULL auto_increment
`owner` varchar(255) default NULL
`from_email` varchar(45) NOT NULL
`letter_subject` varchar(255) NOT NULL
`letter_body` text NOT NULL
`modified_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
PRIMARY KEY  (`mailmerge_id`)
)