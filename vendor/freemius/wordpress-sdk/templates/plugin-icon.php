<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.1.4
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @var array $VARS
	 */
	$fs   = freemius( $VARS['id'] );
?>
<div class="fs-plugin-icon">
	<img src="<?php echo $fs->get_local_icon_url() ?>" width="80" height="80"/>
</div>