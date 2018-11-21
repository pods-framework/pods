/*
 Navicat MySQL Data Transfer

 Source Server         : test.pods.dev
 Source Server Type    : MySQL
 Source Server Version : 50634
 Source Host           : 192.168.95.100
 Source Database       : test

 Target Server Type    : MySQL
 Target Server Version : 50634
 File Encoding         : utf-8

 Date: 11/20/2018 09:26:52 AM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `test_commentmeta`
-- ----------------------------
DROP TABLE IF EXISTS `test_commentmeta`;
CREATE TABLE `test_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_comments`
-- ----------------------------
DROP TABLE IF EXISTS `test_comments`;
CREATE TABLE `test_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `comment_author` tinytext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT '0',
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_links`
-- ----------------------------
DROP TABLE IF EXISTS `test_links`;
CREATE TABLE `test_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT '1',
  `link_rating` int(11) NOT NULL DEFAULT '0',
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_options`
-- ----------------------------
DROP TABLE IF EXISTS `test_options`;
CREATE TABLE `test_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB AUTO_INCREMENT=565 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Records of `test_options`
-- ----------------------------
BEGIN;
INSERT INTO `test_options` VALUES ('2', 'siteurl', 'http://test.pods.dev', 'yes'), ('3', 'home', 'http://test.pods.dev', 'yes'), ('4', 'blogname', 'Pods Tests', 'yes'), ('5', 'blogdescription', 'Just another WordPress site', 'yes'), ('6', 'users_can_register', '0', 'yes'), ('7', 'admin_email', 'admin@test.pods.dev', 'yes'), ('8', 'start_of_week', '1', 'yes'), ('9', 'use_balanceTags', '0', 'yes'), ('10', 'use_smilies', '1', 'yes'), ('11', 'require_name_email', '1', 'yes'), ('12', 'comments_notify', '1', 'yes'), ('13', 'posts_per_rss', '10', 'yes'), ('14', 'rss_use_excerpt', '0', 'yes'), ('15', 'mailserver_url', 'mail.example.com', 'yes'), ('16', 'mailserver_login', 'login@example.com', 'yes'), ('17', 'mailserver_pass', 'password', 'yes'), ('18', 'mailserver_port', '110', 'yes'), ('19', 'default_category', '1', 'yes'), ('20', 'default_comment_status', 'open', 'yes'), ('21', 'default_ping_status', 'open', 'yes'), ('22', 'default_pingback_flag', '1', 'yes'), ('23', 'posts_per_page', '10', 'yes'), ('24', 'date_format', 'F j, Y', 'yes'), ('25', 'time_format', 'g:i a', 'yes'), ('26', 'links_updated_date_format', 'F j, Y g:i a', 'yes'), ('27', 'comment_moderation', '0', 'yes'), ('28', 'moderation_notify', '1', 'yes'), ('29', 'rewrite_rules', '', 'yes'), ('30', 'hack_file', '0', 'yes'), ('31', 'blog_charset', 'UTF-8', 'yes'), ('32', 'moderation_keys', '', 'no'), ('33', 'active_plugins', 'a:1:{i:0;s:13:\"pods/init.php\";}', 'yes'), ('34', 'category_base', '', 'yes'), ('35', 'ping_sites', 'http://rpc.pingomatic.com/', 'yes'), ('36', 'comment_max_links', '2', 'yes'), ('37', 'gmt_offset', '0', 'yes'), ('38', 'default_email_category', '1', 'yes'), ('39', 'recently_edited', '', 'no'), ('40', 'template', 'twentyseventeen', 'yes'), ('41', 'stylesheet', 'twentyseventeen', 'yes'), ('42', 'comment_whitelist', '1', 'yes'), ('43', 'blacklist_keys', '', 'no'), ('44', 'comment_registration', '0', 'yes'), ('45', 'html_type', 'text/html', 'yes'), ('46', 'use_trackback', '0', 'yes'), ('47', 'default_role', 'subscriber', 'yes'), ('48', 'db_version', '38590', 'yes'), ('49', 'uploads_use_yearmonth_folders', '1', 'yes'), ('50', 'upload_path', '', 'yes'), ('51', 'blog_public', '1', 'yes'), ('52', 'default_link_category', '2', 'yes'), ('53', 'show_on_front', 'posts', 'yes'), ('54', 'tag_base', '', 'yes'), ('55', 'show_avatars', '1', 'yes'), ('56', 'avatar_rating', 'G', 'yes'), ('57', 'upload_url_path', '', 'yes'), ('58', 'thumbnail_size_w', '150', 'yes'), ('59', 'thumbnail_size_h', '150', 'yes'), ('60', 'thumbnail_crop', '1', 'yes'), ('61', 'medium_size_w', '300', 'yes'), ('62', 'medium_size_h', '300', 'yes'), ('63', 'avatar_default', 'mystery', 'yes'), ('64', 'large_size_w', '1024', 'yes'), ('65', 'large_size_h', '1024', 'yes'), ('66', 'image_default_link_type', 'none', 'yes'), ('67', 'image_default_size', '', 'yes'), ('68', 'image_default_align', '', 'yes'), ('69', 'close_comments_for_old_posts', '0', 'yes'), ('70', 'close_comments_days_old', '14', 'yes'), ('71', 'thread_comments', '1', 'yes'), ('72', 'thread_comments_depth', '5', 'yes'), ('73', 'page_comments', '0', 'yes'), ('74', 'comments_per_page', '50', 'yes'), ('75', 'default_comments_page', 'newest', 'yes'), ('76', 'comment_order', 'asc', 'yes'), ('77', 'sticky_posts', 'a:0:{}', 'yes'), ('78', 'widget_categories', 'a:2:{i:2;a:4:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:12:\"hierarchical\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}', 'yes'), ('79', 'widget_text', 'a:0:{}', 'yes'), ('80', 'widget_rss', 'a:0:{}', 'yes'), ('81', 'uninstall_plugins', 'a:0:{}', 'no'), ('82', 'timezone_string', '', 'yes'), ('83', 'page_for_posts', '0', 'yes'), ('84', 'page_on_front', '0', 'yes'), ('85', 'default_post_format', '0', 'yes'), ('86', 'link_manager_enabled', '0', 'yes'), ('87', 'finished_splitting_shared_terms', '1', 'yes'), ('88', 'site_icon', '0', 'yes'), ('89', 'medium_large_size_w', '768', 'yes'), ('90', 'medium_large_size_h', '0', 'yes'), ('91', 'wp_page_for_privacy_policy', '3', 'yes'), ('92', 'show_comments_cookies_opt_in', '0', 'yes'), ('93', 'initial_db_version', '38590', 'yes'), ('94', 'test_user_roles', 'a:5:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:61:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:34:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}}', 'yes'), ('95', 'fresh_site', '0', 'yes'), ('96', 'widget_search', 'a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}', 'yes'), ('97', 'widget_recent-posts', 'a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}', 'yes'), ('98', 'widget_recent-comments', 'a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}', 'yes'), ('99', 'widget_archives', 'a:2:{i:2;a:3:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}', 'yes'), ('100', 'widget_meta', 'a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}', 'yes'), ('101', 'sidebars_widgets', 'a:5:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:6:{i:0;s:8:\"search-2\";i:1;s:14:\"recent-posts-2\";i:2;s:17:\"recent-comments-2\";i:3;s:10:\"archives-2\";i:4;s:12:\"categories-2\";i:5;s:6:\"meta-2\";}s:9:\"sidebar-2\";a:0:{}s:9:\"sidebar-3\";a:0:{}s:13:\"array_version\";i:3;}', 'yes'), ('105', 'pods_component_settings', '{\"components\":{\"templates\":[]}}', 'yes'), ('106', 'pods_framework_version', '2.8.0-a-1', 'yes'), ('107', 'pods_framework_db_version', '2.3.5', 'yes'), ('112', 'widget_pages', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('113', 'widget_calendar', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('114', 'widget_media_audio', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('115', 'widget_media_image', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('116', 'widget_media_gallery', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('117', 'widget_media_video', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('118', 'nonce_key', 'vfH+7$H*HXJUE49)3k~s)gdo$o)X8/~~t0Sn{. XI>GXXr2hn)@K$= cm,h(8G<P', 'no'), ('119', 'nonce_salt', 's{[cc/eQj)VMBom&j1f6xe$mhfT[o;ko3R1{C2oIi%P!I2%=Cl}E-*`7.?UE-3o`', 'no'), ('120', 'widget_tag_cloud', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('121', 'widget_nav_menu', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('122', 'widget_custom_html', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('123', 'widget_pods_widget_single', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('124', 'widget_pods_widget_list', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('125', 'widget_pods_widget_field', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('126', 'widget_pods_widget_form', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('127', 'widget_pods_widget_view', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'), ('128', 'cron', 'a:3:{i:1542727064;a:4:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1542727066;a:1:{s:8:\"do_pings\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:2:{s:8:\"schedule\";b:0;s:4:\"args\";a:0:{}}}}s:7:\"version\";i:2;}', 'yes');
COMMIT;

-- ----------------------------
--  Table structure for `test_podsrel`
-- ----------------------------
DROP TABLE IF EXISTS `test_podsrel`;
CREATE TABLE `test_podsrel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pod_id` int(10) unsigned DEFAULT NULL,
  `field_id` int(10) unsigned DEFAULT NULL,
  `item_id` bigint(20) unsigned DEFAULT NULL,
  `related_pod_id` int(10) unsigned DEFAULT NULL,
  `related_field_id` int(10) unsigned DEFAULT NULL,
  `related_item_id` bigint(20) unsigned DEFAULT NULL,
  `weight` smallint(5) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_item_idx` (`field_id`,`item_id`),
  KEY `rel_field_rel_item_idx` (`related_field_id`,`related_item_id`),
  KEY `field_rel_item_idx` (`field_id`,`related_item_id`),
  KEY `rel_field_item_idx` (`related_field_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_postmeta`
-- ----------------------------
DROP TABLE IF EXISTS `test_postmeta`;
CREATE TABLE `test_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_posts`
-- ----------------------------
DROP TABLE IF EXISTS `test_posts`;
CREATE TABLE `test_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_term_relationships`
-- ----------------------------
DROP TABLE IF EXISTS `test_term_relationships`;
CREATE TABLE `test_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_term_taxonomy`
-- ----------------------------
DROP TABLE IF EXISTS `test_term_taxonomy`;
CREATE TABLE `test_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_termmeta`
-- ----------------------------
DROP TABLE IF EXISTS `test_termmeta`;
CREATE TABLE `test_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_terms`
-- ----------------------------
DROP TABLE IF EXISTS `test_terms`;
CREATE TABLE `test_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Table structure for `test_usermeta`
-- ----------------------------
DROP TABLE IF EXISTS `test_usermeta`;
CREATE TABLE `test_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Records of `test_usermeta`
-- ----------------------------
BEGIN;
INSERT INTO `test_usermeta` VALUES ('1', '1', 'nickname', 'admin'), ('2', '1', 'first_name', ''), ('3', '1', 'last_name', ''), ('4', '1', 'description', ''), ('5', '1', 'rich_editing', 'true'), ('6', '1', 'syntax_highlighting', 'true'), ('7', '1', 'comment_shortcuts', 'false'), ('8', '1', 'admin_color', 'fresh'), ('9', '1', 'use_ssl', '0'), ('10', '1', 'show_admin_bar_front', 'true'), ('11', '1', 'locale', ''), ('12', '1', 'test_capabilities', 'a:1:{s:13:\"administrator\";b:1;}'), ('13', '1', 'test_user_level', '10'), ('14', '1', 'dismissed_wp_pointers', 'wp496_privacy'), ('15', '1', 'show_welcome_panel', '1');
COMMIT;

-- ----------------------------
--  Table structure for `test_users`
-- ----------------------------
DROP TABLE IF EXISTS `test_users`;
CREATE TABLE `test_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT '0',
  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ----------------------------
--  Records of `test_users`
-- ----------------------------
BEGIN;
INSERT INTO `test_users` VALUES ('1', 'admin', '$P$BtRfPE97Qjj5c32yLM7Y7Q/0AOQgMe1', 'admin', 'admin@test.pods.dev', '', '2018-11-20 15:17:44', '', '0', 'admin');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
