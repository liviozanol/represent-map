CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `active` int(1) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `min_grade` varchar(100),
  `min_duration` varchar(100),
  `description` varchar(500),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 