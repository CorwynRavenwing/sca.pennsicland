CREATE TABLE `data_var` (
  `data_var_id` int(11) NOT NULL auto_increment,
  `registration_open_date` date NOT NULL,
  `registration_close_date` date NOT NULL,
  `pennsic_open_date` date NOT NULL,
  `pennsic_close_date` date NOT NULL,
  `current_mode` varchar(30) NOT NULL,
  `current_year` varchar(10) NOT NULL,
  `pennsic_number` varchar(10) NOT NULL,
  PRIMARY KEY  (`data_var_id`)
)
