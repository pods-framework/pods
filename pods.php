<?php
/*
Plugin Name: Pods CMS Framework
Plugin URI: http://podscms.org/
Description: Create / Manage / Develop / Extend content types: Posts, Pages, Custom Post Types, Categories, Tags, Custom Taxonomy, Comments, Users, Custom Content Types, and Custom Tables
Version: 2.0.0 Alpha 1
Author: The Pods CMS Team
Author URI: http://podscms.org/about/

Copyright 2009-2011  The Pods CMS Team  (email : contact@podscms.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define('PODS_VERSION', '2.0.0-a-1');
if (!defined('PODS_WP_VERSION_MINIMUM'))
    define('PODS_WP_VERSION_MINIMUM', '3.1');
if (!defined('PODS_PHP_VERSION_MINIMUM'))
    define('PODS_PHP_VERSION_MINIMUM', '5.2.4');
if (!defined('PODS_MYSQL_VERSION_MINIMUM'))
    define('PODS_MYSQL_VERSION_MINIMUM', '5.0');
define('PODS_URL', plugin_dir_url(__FILE__));
define('PODS_DIR', plugin_dir_path(__FILE__));
if (!defined('WP_INCLUDES_URL'))
    define('WP_INCLUDES_URL', includes_url());

require_once(PODS_DIR . '/functions.php');
require_once(PODS_DIR . '/pods-ui.php');
global $pods, $pods_init, $pods_admin, $pod_page_exists;
if (false !== pods_compatible() && (!defined('SHORTINIT') || !SHORTINIT)) {
    require_once(PODS_DIR . '/deprecated/deprecated.php');
    $pods_init = pods_init();
}