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
