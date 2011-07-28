<?php
/*
Plugin Name: Pods CMS Framework
Plugin URI: http://podscms.org/
Description: Pods is a CMS framework for creating, managing, and deploying customized content types.
Version: 1.10
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

define('PODS_VERSION_FULL', '1.10');
define('PODS_WP_VERSION_MINIMUM', '3.1');
define('PODS_PHP_VERSION_MINIMUM', '5.2.4');
define('PODS_MYSQL_VERSION_MINIMUM', '5.0');

// setup version as a full number for upgrade handling
$pods_version_tmp = explode('.', PODS_VERSION_FULL);
$pods_version_number = '';
for ($pods_x = 0; $pods_x < 3; $pods_x++) { // 3 points max - MAJOR.MINOR.PATCH
    if (!isset($pods_version_tmp[$pods_x]) || strlen($pods_version_tmp[$pods_x]) < 1)
        $pods_version_tmp[$pods_x] = '000';
    $pods_version_temp = str_split($pods_version_tmp[$pods_x]);
    if (3 == count($pods_version_temp))
        $pods_version_number .= $pods_version_tmp[$pods_x];
    elseif (2 == count($pods_version_temp))
        $pods_version_number .= '0' . $pods_version_tmp[$pods_x];
    elseif (1 == count($pods_version_temp))
        $pods_version_number .= '00' . $pods_version_tmp[$pods_x];
}
$pods_version_number = (int) $pods_version_number;
define('PODS_VERSION', $pods_version_number);
unset($pods_version_number, $pods_version_tmp, $pods_x); // cleanup

define('PODS_URL', rtrim(plugin_dir_url(__FILE__), '/')); // non-trailing slash being deprecated in 2.0
define('PODS_DIR', rtrim(plugin_dir_path(__FILE__), '/')); // non-trailing slash being deprecated in 2.0
define('WP_INC_URL', rtrim(includes_url(), '/')); // non-trailing slash being deprecated in 2.0

require_once(PODS_DIR . '/functions.php');

require_once(PODS_DIR . '/classes/PodCache.php');
require_once(PODS_DIR . '/classes/PodInit.php');

require_once(PODS_DIR . '/classes/Pod.php');
require_once(PODS_DIR . '/classes/PodAPI.php');

require_once(PODS_DIR . '/pods-ui.php');

global $pods_cache, $cache, $pods_init;
if (false !== pods_compatible() && (!defined('SHORTINIT') || !SHORTINIT)) {
    require_once(PODS_DIR . '/deprecated.php'); // DEPRECATED IN 2.0

    $pods_cache = PodCache::instance();
    $cache = &$pods_cache; // DEPRECATED IN 2.0
    $pods_init = new PodInit();
}