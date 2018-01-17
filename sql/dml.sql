CREATE TABLE `augustova`.`errors` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `message` MEDIUMTEXT NULL DEFAULT NULL,
  `stack` MEDIUMTEXT NULL,
  `date` DATETIME NULL,
  PRIMARY KEY (`id`, `name`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC))
  DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

ALTER TABLE `augustova`.`goods`
ADD COLUMN `category` VARCHAR(45) NOT NULL AFTER `image_path`;

ALTER TABLE `augustova`.`user_order`
DROP FOREIGN KEY `fk_good_id`;
ALTER TABLE `augustova`.`user_order`
ADD CONSTRAINT `fk_good_id`
  FOREIGN KEY (`good_id`)
  REFERENCES `augustova`.`goods` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


ALTER TABLE `augustova`.`goods`
ADD COLUMN `version` VARCHAR(45) NOT NULL DEFAULT 0 AFTER `category`;
