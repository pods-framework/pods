CREATE TABLE IF NOT EXISTS `wp_podsrel` (
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
