CREATE TABLE IF NOT EXISTS `places` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `approved` int(1) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `inep` varchar(100),
  `cnpj` varchar(100),
  `lat` float NOT NULL,
  `lng` float NOT NULL,
  `address_street` varchar(200),
  `address_number` varchar(200),
  `address_neighborhood` varchar(200),
  `address_city` varchar(200) NOT NULL,
  `address_state` varchar(200) NOT NULL,
  `address_postal_code` varchar(200),
  `address_telephone` varchar(200),
  `uri` varchar(200),
  `description` varchar(255) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `owner_email` varchar(100) NOT NULL,  
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 

