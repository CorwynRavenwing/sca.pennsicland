CREATE TABLE `numbers` (
`n` int(11) NOT NULL auto_increment
`divisors` int(11) NOT NULL default '0'
`checked` tinyint(1) NOT NULL default '0'
`prime` tinyint(1) NOT NULL default '0'
`triangle` tinyint(1) NOT NULL default '0'
PRIMARY KEY  (`n`)
)