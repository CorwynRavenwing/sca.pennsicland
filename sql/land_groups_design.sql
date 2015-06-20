CREATE TABLE `land_groups` (
`group_id` int(11) NOT NULL auto_increment
`group_name` varchar(100) default NULL
`first_block_choice` varchar(12) NOT NULL default ''
`second_block_choice` varchar(12) NOT NULL default ''
`third_block_choice` varchar(12) NOT NULL default ''
`fourth_block_choice` varchar(12) NOT NULL default ''
`staff_group` int(1) default NULL
`new_group` int(1) default NULL
`time_registered` int(12) unsigned NOT NULL default '0'
`bonus_footage` int(11) NOT NULL default '0'
`bonus_reason` varchar(255) NOT NULL default ''
`registration_complete` int(1) default NULL
`other_group_information` text NOT NULL
`other_admin_information` text NOT NULL
`final_block_location` varchar(12) NOT NULL default ''
`compression_percentage` int(11) NOT NULL default '0'
`on_site_representative` varchar(100) NOT NULL default ''
`user_id` int(11) NOT NULL default '0'
`exact_land_amount` int(11) NOT NULL default '0'
`reserved_group` int(1) default NULL
`pre_registration_count` int(11) NOT NULL default '0'
`block_pointer` varchar(10) NOT NULL default ''
`calculated_compression` int(10) NOT NULL default '0'
`used_space` int(11) NOT NULL
`group_data` varchar(255) NOT NULL
`used_space_save` int(11) NOT NULL
`has_changed` tinyint(1) NOT NULL
`block_choices_valid` tinyint(1) NOT NULL default '0'
`status` int(1) NOT NULL default '2'
`group_name_base` varchar(255) NOT NULL
`group_metaphone` varchar(255) NOT NULL
`group_soundex` varchar(255) NOT NULL
PRIMARY KEY  (`group_id`)
KEY `GroupNameLookup` (`group_name`)
KEY `UserNameLookup` (`user_id`)
)