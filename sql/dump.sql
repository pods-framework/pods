DROP TABLE IF EXISTS wp_pods;
DROP TABLE IF EXISTS wp_pods_fields;
DROP TABLE IF EXISTS wp_pods_objects;
DROP TABLE IF EXISTS wp_pods_rel;

CREATE TABLE `wp_pods` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NULL DEFAULT NULL,
    `type` VARCHAR(15) NULL DEFAULT 'pod',
    `object` VARCHAR(255) NULL DEFAULT NULL,
    `storage` VARCHAR(255) NULL DEFAULT 'table',
    `alias` VARCHAR(255) NULL DEFAULT NULL,
    `weight` INT(10) UNSIGNED NULL DEFAULT '0',
    `options` LONGTEXT NULL,
    PRIMARY KEY (`id`),
    INDEX `name_x` (`name`)
) DEFAULT CHARSET utf8;

CREATE TABLE `wp_pods_fields` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `pod_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(64) NULL DEFAULT NULL,
    `label` VARCHAR(255) NULL DEFAULT NULL,
    `type` VARCHAR(32) NULL DEFAULT NULL,
    `pick_object` VARCHAR(32) NULL DEFAULT NULL,
    `pick_val` VARCHAR(32) NULL DEFAULT NULL,
    `sister_field_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `weight` INT(10) UNSIGNED NULL DEFAULT '0',
    `options` LONGTEXT NULL,
    PRIMARY KEY (`id`),
    INDEX `pod_idx` (`pod_id`)
) DEFAULT CHARSET utf8;

CREATE TABLE `wp_pods_objects` (
    `id` INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NULL DEFAULT NULL,
    `type` VARCHAR(20) NULL DEFAULT NULL,
    `code` LONGTEXT NULL,
    `options` LONGTEXT NULL,
    PRIMARY KEY (`id`),
    INDEX `name_idx` (`name`)
) DEFAULT CHARSET utf8;

CREATE TABLE `wp_pods_rel` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `pod_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `field_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `item_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    `related_pod_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `related_field_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `related_item_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    `weight` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    INDEX `field_item_idx` (`field_id`, `item_id`),
    INDEX `rel_field_rel_item_idx` (`related_field_id`, `related_item_id`),
    INDEX `field_rel_item_idx` (`field_id`, `related_item_id`),
    INDEX `rel_field_item_idx` (`related_field_id`, `item_id`)
) DEFAULT CHARSET utf8;