SET FOREIGN_KEY_CHECKS = 0;
 
CREATE TABLE `apisettings` (
  `id` int(9) NOT NULL auto_increment,
  `userid` int(9) NOT NULL,
  `fburl` varchar(100) NOT NULL default '',
  `fbtoken` varchar(100) NOT NULL default '',
  `tickurl` varchar(255) NOT NULL,
  `tickemail` varchar(100) NOT NULL default '',
  `tickpassword` varchar(100) NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
 
 
CREATE TABLE `entries` (
  `id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL,
  `ts_entry_id` int(10) NOT NULL,
  `fb_invoice_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=366 DEFAULT CHARSET=latin1;
 
 
CREATE TABLE `password_reset` (
  `id` int(10) NOT NULL auto_increment,
  `email` varchar(100) NOT NULL,
  `hash` varchar(200) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
 
 
CREATE TABLE `users` (
  `id` int(9) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(256) NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
 
 
SET FOREIGN_KEY_CHECKS = 1;
