CREATE TABLE `land_variable` (
`variable_id` int(11) NOT NULL auto_increment
`variable_name` varchar(50) NOT NULL default ''
`description` varchar(255) NOT NULL default ''
`value` varchar(255) NOT NULL default ''
`updated` int(11) NOT NULL default '0'
`delay` int(11) NOT NULL default '0'
`queued` int(11) NOT NULL default '0'
`modified_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
PRIMARY KEY  (`variable_id`)
KEY `VarName` (`variable_name`)
)