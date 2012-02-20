<?php
/*
Plugin Name: Pods CMS Framework
Plugin URI: http://podsframework.org/
Description: Pods is a CMS framework for creating, managing, and deploying customized content types.
Version: 1.12.3
Author: The Pods CMS Team
Author URI: http://podsframework.org/about/

(c) Copyright 2009-2012  The Pods CMS Team  (email : contact@podsframework.org)

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

define('PODS_VERSION', '1.12.3');
if (!defined('PODS_WP_VERSION_MINIMUM'))
    define('PODS_WP_VERSION_MINIMUM', '3.1');
if (!defined('PODS_PHP_VERSION_MINIMUM'))
    define('PODS_PHP_VERSION_MINIMUM', '5.2.4');
if (!defined('PODS_MYSQL_VERSION_MINIMUM'))
    define('PODS_MYSQL_VERSION_MINIMUM', '5.0');

define('PODS_URL', rtrim(plugin_dir_url(__FILE__), '/')); // non-trailing slash being deprecated in 2.0
define('PODS_DIR', rtrim(plugin_dir_path(__FILE__), '/')); // non-trailing slash being deprecated in 2.0
define('WP_INC_URL', rtrim(includes_url(), '/')); // non-trailing slash being deprecated in 2.0

require_once(PODS_DIR . '/functions.php');

require_once(PODS_DIR . '/classes/PodInit.php');

require_once(PODS_DIR . '/classes/Pod.php');
require_once(PODS_DIR . '/classes/PodAPI.php');

require_once(PODS_DIR . '/classes/PodCache.php');

require_once(PODS_DIR . '/pods-ui.php');

global $pods_cache, $cache, $pods_init;
if (false !== pods_compatible() && (!defined('SHORTINIT') || !SHORTINIT)) {
    // JSON support
    if (!function_exists('json_encode')) {
        require_once(ABSPATH . '/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');

        function json_encode($str) {
            $json = new Moxiecode_JSON();
            return $json->encode($str);
        }

        function json_decode($str) {
            $json = new Moxiecode_JSON();
            return $json->decode($str);
        }
    }

    $pods_cache = PodCache::instance();
    $cache = &$pods_cache; // DEPRECATED IN 2.0
    $pods_init = new PodInit();
}