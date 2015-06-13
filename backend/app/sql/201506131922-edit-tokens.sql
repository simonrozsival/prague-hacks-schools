CREATE TABLE `edit_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(255) NOT NULL COLLATE 'utf8_czech_ci',
  `school_id` INT(11) NOT NULL,
  `email` VARCHAR(255) NOT NULL COLLATE 'utf8_czech_ci',
  `valid_until` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
)
  COLLATE='utf8_czech_ci'
  ENGINE=InnoDB
;
