<?php

$model = new waModel();
$model->exec("CREATE TABLE IF NOT EXISTS shop_referrals_activity (
  id int(11) NOT NULL AUTO_INCREMENT,
  create_datetime datetime NOT NULL,
  `code` int(11) NOT NULL,
  contact_id int(11) NOT NULL,
  referer varchar(255) NOT NULL,
  referer_host varchar(255) NOT NULL,
  PRIMARY KEY (id),
  KEY contact_id (contact_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");