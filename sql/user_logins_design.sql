CREATE TABLE `user_logins` (
  `login_id` int(12) NOT NULL auto_increment,
  `access_type` varchar(20) NOT NULL default '',
  `http_refferer` varchar(255) NOT NULL default '',
  `http_user_agent` varchar(255) NOT NULL default '',
  `remote_addr` varchar(20) NOT NULL default '',
  `remote_host` varchar(255) NOT NULL default '',
  `request_method` varchar(10) NOT NULL default '',
  `login_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `user_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`login_id`)
) ENGINE=MyISAM AUTO_INCREMENT=130518 DEFAULT CHARSET=latin1