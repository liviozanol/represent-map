CREATE TABLE IF NOT EXISTS `courses_instances` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `courseid` int(9) NOT NULL,
  `placeid` int(9) NOT NULL,
  `data_begin` DATE,
  `data_end` DATE,
  `data_endof_inscription` DATE,
  `shift` varchar(100),
  `duration` int(9),
  `class` int(9),
  `capacity` int(9),
  `enrollments` int(9),
  PRIMARY KEY (id),
  FOREIGN KEY (courseid) REFERENCES courses(id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (placeid) REFERENCES places(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 