CREATE TABLE IF NOT EXISTS `wp_podsrel` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `pod` VARCHAR(200) NULL DEFAULT NULL,
    `field` VARCHAR(200) NULL DEFAULT NULL,
    `item_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    `related_pod` VARCHAR(200) NULL DEFAULT NULL,
    `related_field` VARCHAR(200) NULL DEFAULT NULL,
    `related_item_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    `weight` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    INDEX `field_item_idx` (`field`, `item_id`),
    INDEX `rel_field_rel_item_idx` (`related_field`, `related_item_id`),
    INDEX `field_rel_item_idx` (`field`, `related_item_id`),
    INDEX `rel_field_item_idx` (`related_field`, `item_id`)
) DEFAULT CHARSET utf8;