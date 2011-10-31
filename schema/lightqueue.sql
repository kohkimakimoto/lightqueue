-- -----------------------------------------------------
-- Create database
-- -----------------------------------------------------
CREATE DATABASE lightqueue CHARACTER SET utf8;

-- -----------------------------------------------------
-- Table `queue`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `queue` ;

CREATE  TABLE IF NOT EXISTS `queue` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `kind` VARCHAR(30) NOT NULL ,
  `task` LONGTEXT NOT NULL ,
  `locked_until` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
