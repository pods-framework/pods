<?php
global $pods_init;

if ( isset( $_POST['_wpnonce'] ) && false !== wp_verify_nonce( $_POST['_wpnonce'], 'pods-settings' ) ) {
	if ( isset( $_POST['pods_cleanup_1x'] ) ) {
		pods_upgrade( '2.0.0' )->cleanup();

		pods_redirect( pods_query_arg( array( 'pods_cleanup_1x_success' => 1 ), array( 'page', 'tab' ) ) );
	} elseif ( isset( $_POST['pods_reset'] ) ) {
		$pods_init->reset();
		$pods_init->setup();

		pods_redirect( pods_query_arg( array( 'pods_reset_success' => 1 ), array( 'page', 'tab' ) ) );
	} elseif ( isset( $_POST['pods_reset_deactivate'] ) ) {
		$pods_init->reset();

		deactivate_plugins( PODS_DIR . 'init.php' );

		pods_redirect( 'index.php' );
	} elseif ( 1 == pods_v( 'pods_reset_success' ) ) {
		pods_message( 'Pods 2.x settings and data have been reset.' );
	} elseif ( 1 == pods_v( 'pods_cleanup_1x_success' ) ) {
		pods_message( 'Pods 1.x data has been deleted.' );
	}
}//end if

// Monday Mode
$monday_mode = pods_v( 'pods_monday_mode', 'get', 0, true );

if ( pods_v_sanitized( 'pods_reset_weekend', 'post', pods_v_sanitized( 'pods_reset_weekend', 'get', 0, null, true ), null, true ) ) {
	if ( $monday_mode ) {
		$html = '<br /><br /><iframe width="480" height="360" src="http://www.youtube-nocookie.com/embed/QH2-TGUlwu4?autoplay=1" frameborder="0" allowfullscreen></iframe>';
		pods_message( 'The weekend has been reset and you have been sent back to Friday night. Unfortunately due to a tear in the fabric of time, you slipped back to Monday. We took video of the whole process and you can see it below..' . $html );
	} else {
		$html = '<br /><br /><iframe width="480" height="360" src="http://www.youtube-nocookie.com/embed/xhrBDcQq2DM?autoplay=1" frameborder="0" allowfullscreen></iframe>';
		pods_message( 'Oops, sorry! You can only reset the weekend on a Monday before the end of the work day. Somebody call the Waaambulance!' . $html, 'error' );
	}
}

// Please Note:
$please_note = __( 'Please Note:' );

$old_version = get_option( 'pods_version' );

if ( ! empty( $old_version ) ) {
	?>
	<h3><?php _e( 'Delete Pods 1.x data', 'pods' ); ?></h3>

	<p><?php _e( 'This will delete all of your Pods 1.x data, it\'s only recommended if you\'ve verified your data has been properly migrated into Pods 2.x.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 1.x, resetting it to a clean first install.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_cleanup_1x" value=" <?php esc_attr_e( 'Delete Pods 1.x settings and data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>

	<hr />

	<h3><?php _e( 'Reset Pods 2.x', 'pods' ); ?></h3>

	<p><?php _e( 'This does not delete any Pods 1.x data, it simply resets the Pods 2.x settings, removes all of it\'s data, and performs a fresh install.', 'pods' ); ?></p>
	<p><?php echo sprintf( '<strong>%1$s</strong>', $please_note ) . __( 'This does not remove any items from any Post Types, Taxonomies, Media, Users, or Comments data you have added/modified. Any custom fields stored using the table storage component, content in Advanced Content Types, and relationships between posts will be lost.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.x, resetting it to a clean first install.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset" value=" <?php esc_attr_e( 'Reset Pods 2.x settings and data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>

	<hr />

	<h3><?php _e( 'Deactivate and Delete Pods 2.x data', 'pods' ); ?></h3>

	<p><?php _e( 'This will delete Pods 2.x settings, data, and deactivate itself once done. Your database will be as if Pods 2.x never existed.', 'pods' ); ?></p>
	<p><?php _e( '<strong>Please Note:</strong> This does not remove any items from any Post Types, Taxonomies, Media, Users, or Comments data you have added/modified.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.x with no turning back.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset_deactivate" value=" <?php esc_attr_e( 'Deactivate and Delete Pods 2.x data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>
	<?php
} else {
	?>
	<h3><?php _e( 'Reset Pods', 'pods' ); ?></h3>

	<p><?php _e( 'This will reset Pods settings, removes all of it\'s data, and performs a fresh install.', 'pods' ); ?></p>
	<p><?php _e( '<strong>Please Note:</strong> This does not remove any items from any Post Types, Taxonomies, Media, Users, or Comments data you have added/modified.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds Pods, resetting it to a clean, first install.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset" value="<?php esc_attr_e( 'Reset Pods settings and data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>

	<hr />

	<h3><?php _e( 'Deactivate and Delete Pods data', 'pods' ); ?></h3>

	<p><?php _e( 'This will delete Pods settings, data, and deactivate itself once done. Your database will be as if Pods never existed.', 'pods' ); ?></p>
	<p><?php _e( '<strong>Please Note:</strong> This does not remove any items from any Post Types, Taxonomies, Media, Users, or Comments data you have added/modified.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds with no turning back.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset_deactivate" value=" <?php esc_attr_e( 'Deactivate and Delete Pods data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>
	<?php
}//end if

if ( $monday_mode ) {
	?>
	<hr />

	<h3><?php _e( 'Reset Weekend', 'pods' ); ?></h3>

	<p><?php _e( 'This feature has been exclusively built for Pods to help developers suffering from "Monday", and allows them to reset the weekend.', 'pods' ); ?></p>
	<p><?php _e( "By resetting the weekend, you will be sent back to Friday night and the weekend you've just spent will be erased. You will retain all of your memories of the weekend, and be able to relive it in any way you wish.", 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to Reset your Weekend?\n\nThere is no going back, you cannot reclaim anything you've gained throughout your weekend.\n\nYou are about to be groundhoggin' it", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset_weekend" value=" reset_weekend( '<?php echo esc_js( date_i18n( 'Y-m-d', strtotime( '-3 days' ) ) ); ?> 19:00:00' ); " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>
	<?php
}
?>
