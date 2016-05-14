CREATE TABLE `land_blocks` (
`block_id` int(11) NOT NULL auto_increment
`block_name` varchar(5) NOT NULL default ''
`reserved` enum('0','1') NOT NULL default '0'
`hide` enum('0','1') NOT NULL default '0'
`total_square_footage` int(11) unsigned NOT NULL default '0'
`campable_square_footage` int(11) unsigned NOT NULL default '0'
`block_use_type` enum('normal','party','family') NOT NULL default 'normal'
`tree_type` enum('none','some','heavy','woods') NOT NULL default 'none'
`ground_type` enum('flat','hilly','slope','swamp') NOT NULL default 'flat'
`description` varchar(255) NOT NULL default ''
`used_space` int(11) NOT NULL default '0'
`free_space` int(11) NOT NULL default '0'
`generate_neighbors` mediumtext NOT NULL
`has_changed` int(3) NOT NULL
`on_gas_line` enum('0','1') NOT NULL default '0'
`map_link` varchar(255) NOT NULL default ''
`auth_link` varchar(255) NOT NULL default ''
`gasline_link` varchar(255) NOT NULL default ''
PRIMARY KEY  (`block_id`)
KEY `BlockName` (`block_name`)
)