<?php

use Pods\Tools\Reset;

/** @var $pods_init PodsInit */
global $pods_init, $wpdb;

$relationship_table = $wpdb->prefix . 'podsrel';

$excluded_pod_types_from_reset = [
	'user',
	'media',
];

$pods_api = pods_api();

if ( isset( $_POST['_wpnonce'] ) && false !== wp_verify_nonce( $_POST['_wpnonce'], 'pods-settings' ) ) {
	if ( isset( $_POST['pods_cleanup_1x'] ) ) {
		pods_upgrade( '2.0.0' )->cleanup();

		pods_redirect( pods_query_arg( array( 'pods_cleanup_1x_success' => 1 ), array( 'page', 'tab' ) ) );
	} elseif ( isset( $_POST['pods_reset_pod'] ) || isset( $_POST['pods_reset_pod_preview'] ) ) {
		$pod_name = pods_v( 'pods_field_reset_pod', 'post' );

		if ( is_array( $pod_name ) ) {
			$pod_name = $pod_name[0];
		}

		$pod_name = sanitize_text_field( $pod_name );

		if ( empty( $pod_name ) ) {
			pods_message( __( 'No Pod specified.', 'pods' ), 'error' );
		} else {
			$pod = $pods_api->load_pod( [ 'name' => $pod_name ], false );

			if ( empty( $pod ) ) {
				pods_message( __( 'Pod not found.', 'pods' ), 'error' );
			} else {
				$tool = pods_container( Reset::class );

				$mode = 'full';

				if ( ! empty( $_POST['pods_reset_pod_preview'] ) ) {
					$mode = 'preview';
				}

				$results = $tool->delete_all_content_for_pod( $pod, $mode );

				pods_message( $results['message_html'] );
			}
		}
	} elseif ( isset( $_POST['pods_reset'] ) ) {
		$pods_init->reset();
		$pods_init->setup();

		pods_redirect( pods_query_arg( array( 'pods_reset_success' => 1 ), array( 'page', 'tab' ) ) );
	} elseif ( isset( $_POST['pods_reset_deactivate'] ) ) {
		$pods_init->reset();

		deactivate_plugins( PODS_DIR . 'init.php' );

		pods_redirect( 'index.php' );
	}
} elseif ( 1 === (int) pods_v( 'pods_reset_success' ) ) {
	pods_message( 'Pods settings and data have been reset.' );
} elseif ( 1 === (int) pods_v( 'pods_cleanup_1x_success' ) ) {
	pods_message( 'Pods 1.x data has been deleted.' );
}

// Monday Mode
$monday_mode = pods_v( 'pods_monday_mode', 'get', 0, true );

if ( pods_v_sanitized( 'pods_reset_weekend', 'post', pods_v_sanitized( 'pods_reset_weekend', 'get', 0, null, true ), null, true ) ) {
	if ( $monday_mode ) {
		$html = '<br /><br /><iframe width="480" height="360" src="https://www.youtube-nocookie.com/embed/QH2-TGUlwu4?autoplay=1" frameborder="0" allowfullscreen></iframe>';
		pods_message( 'The weekend has been reset and you have been sent back to Friday night. Unfortunately due to a tear in the fabric of time, you slipped back to Monday. We took video of the whole process and you can see it below..' . $html );
	} else {
		$html = '<br /><br /><iframe width="480" height="360" src="https://www.youtube-nocookie.com/embed/QH2-TGUlwu4?autoplay=1" frameborder="0" allowfullscreen></iframe>';
		pods_message( 'Oops, sorry! You can only reset the weekend on a Monday before the end of the work day. Somebody call the Waaambulance!' . $html, 'error' );
	}
}

// Please Note:
$please_note = __( 'Please Note:' );

$all_pods = $pods_api->load_pods();
?>
<h3><?php esc_html_e( 'Delete all content for a Pod', 'pods' ); ?></h3>

<p><?php esc_html_e( 'This will delete ALL stored content in the database for your Pod.', 'pods' ); ?></p>

<h4><?php esc_html_e( 'What you can expect', 'pods' ); ?></h4>

<ul>
	<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'Your Pod configuration will remain', 'pods' ); ?></li>
	<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'Your Group configurations will remain', 'pods' ); ?></li>
	<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'Your Field configurations will remain', 'pods' ); ?></li>
	<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'Previously uploaded media will remain in the Media Library but will no longer be attached to their corresponding posts', 'pods' ); ?></li>
	<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'All items for this Pod stored will be deleted from the database', 'pods' ); ?></li>
	<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'All custom fields for the items stored will be deleted from the database', 'pods' ); ?></li>
	<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'All files and relationships for the items stored will be deleted from the database', 'pods' ); ?></li>
</ul>

<?php
$reset_pods = [];

foreach ( $all_pods as $pod ) {
	if ( in_array( $pod->get_type(), $excluded_pod_types_from_reset, true ) ) {
		continue;
	}

	$pod_name = $pod->get_name();

	$reset_pods[ $pod_name ] = sprintf(
		'%1$s (%2$s)',
		$pod->get_label(),
		$pod_name
	);
}

asort( $reset_pods );
?>

<?php if ( ! empty( $reset_pods ) ) : ?>
	<table class="form-table pods-manage-field">
		<?php
		$fields = [
			'reset_pod' => [
				'name'               => 'reset_pod',
				'label'              => __( 'Pod', 'pods' ),
				'type'               => 'pick',
				'pick_format_type'   => 'single',
				'pick_format_single' => 'autocomplete',
				'data'               => $reset_pods,
			],
		];

		$field_prefix      = 'pods_field_';
		$field_row_classes = '';
		$id                = '';
		$value_callback    = static function( $field_name, $id, $field, $pod ) use ( $field_prefix ) {
			return pods_v( $field_prefix . $field_name, 'post', '' );
		};

		pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );
	?>
	</table>

	<p class="submit">
		<?php
		$confirm = esc_html__( 'Are you sure you want to do this?', 'pods' )
				   . "\n\n" . esc_html__( 'This is a good time to make sure you have a backup.', 'pods' )
				   . "\n\n" . esc_html__( 'We will delete ALL of the content for the Pod you selected.', 'pods' );
		?>
		<input type="submit" class="button button-primary" name="pods_reset_pod"
			value=" <?php esc_attr_e( 'Delete Pod Content', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
		<input type="submit" class="button button-secondary" name="pods_reset_pod_preview"
			value=" <?php esc_attr_e( 'Preview (no changes will be made)', 'pods' ); ?> " />
	</p>
<?php else : ?>
	<p><em><?php esc_html_e( 'No Pods available to reset.', 'pods' ); ?></em></p>
<?php endif; ?>

<hr />

<?php
$old_version = get_option( 'pods_version' );

if ( ! empty( $old_version ) ) {
	?>
	<h3><?php esc_html_e( 'Delete Pods 1.x data', 'pods' ); ?></h3>

	<p><?php esc_html_e( 'This will delete all of your Pods 1.x data, it\'s only recommended if you\'ve verified your data has been properly migrated into Pods 2.x.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup.\n\nWe are deleting ALL of the data that surrounds 1.x, resetting it to a clean first install.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_cleanup_1x" value=" <?php esc_attr_e( 'Delete Pods 1.x settings and data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>

	<hr />

	<h3><?php esc_html_e( 'Reset Pods 2.x', 'pods' ); ?></h3>

	<p><?php esc_html_e( 'This does not delete any Pods 1.x data, it simply resets the Pods 2.x settings, removes all of it\'s data, and performs a fresh install.', 'pods' ); ?></p>
	<p><?php echo sprintf( '<strong>%1$s</strong>', $please_note ) . __( 'This does not remove any items from any Post Types, Taxonomies, Media, Users, or Comments data you have added/modified. Any custom fields stored using the table storage component, content in Advanced Content Types, and relationships between posts will be lost.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.x, resetting it to a clean first install.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset" value=" <?php esc_attr_e( 'Reset Pods 2.x settings and data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>

	<hr />

	<h3><?php esc_html_e( 'Deactivate and Delete Pods 2.x data', 'pods' ); ?></h3>

	<p><?php esc_html_e( 'This will delete Pods 2.x settings, data, and deactivate itself once done. Your database will be as if Pods 2.x never existed.', 'pods' ); ?></p>
	<p><?php _e( '<strong>Please Note:</strong> This does not remove any items from any Post Types, Taxonomies, Media, Users, or Comments data you have added/modified.', 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup.\n\nWe are deleting ALL of the data that surrounds 2.x with no turning back.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset_deactivate" value=" <?php esc_attr_e( 'Deactivate and Delete Pods 2.x data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>
	<?php
} else {
	?>
	<h3><?php esc_html_e( 'Reset Pods entirely', 'pods' ); ?></h3>

	<p><?php esc_html_e( 'This will reset Pods settings, remove all of your Pod configurations, and perform a fresh install.', 'pods' ); ?></p>
	<h4><?php esc_html_e( 'What you can expect', 'pods' ); ?></h4>
	<ul>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All posts will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All terms will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All media will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All users will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All custom field values will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'Pods plugin will remain activated', 'pods' ); ?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'Pods settings will be reset to defaults', 'pods' ); ?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'All Pod configurations will be deleted including Custom Post Types, Custom Taxonomies, Custom Settings Pages, and all custom field configurations', 'pods' ); ?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php
			// translators: %s is the name of the wp_podsrel table in the database.
			printf( esc_html__( 'Relationship table (%s) will be deleted (and all of the relationships)', 'pods' ), esc_html( $relationship_table ) );
			?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'Advanced Content Type tables will be deleted (and all of their data)', 'pods' ); ?></li>
	</ul>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup.\n\nWe are deleting ALL of the data that surrounds Pods, resetting it to a clean, first install.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset" value="<?php esc_attr_e( 'Reset Pods settings and data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>

	<hr />

	<h3><?php esc_html_e( 'Deactivate and Delete Pods data', 'pods' ); ?></h3>

	<p><?php esc_html_e( 'This will delete Pods settings, data, and deactivate itself once done.', 'pods' ); ?></p>
	<h4><?php esc_html_e( 'What you can expect', 'pods' ); ?></h4>
	<ul>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All posts will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All terms will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All media will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All users will remain', 'pods' ); ?></li>
		<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php esc_html_e( 'All custom field values will remain', 'pods' ); ?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DEACTIVATE', 'pods' ); ?>:</strong> <?php esc_html_e( 'Pods plugin will be deactivated', 'pods' ); ?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'Pods settings will be reset to defaults', 'pods' ); ?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'All Pod configurations will be deleted including Custom Post Types, Custom Taxonomies, Custom Settings Pages, and all custom field configurations', 'pods' ); ?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php
			// translators: %s is the name of the wp_podsrel table in the database.
			printf( esc_html__( 'Relationship table (%s) will be deleted (and all of the relationships)', 'pods' ), esc_html( $relationship_table ) );
			?></li>
		<li>❌ &nbsp;&nbsp;<strong><?php esc_html_e( 'DELETE', 'pods' ); ?>:</strong> <?php esc_html_e( 'Advanced Content Type tables will be deleted (and all of their data)', 'pods' ); ?></li>
	</ul>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup.\n\nWe are deleting ALL of the data that surrounds with no turning back.", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset_deactivate" value=" <?php esc_attr_e( 'Deactivate and Delete Pods data', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>
	<?php
}//end if

if ( $monday_mode ) {
	?>
	<hr />

	<h3><?php esc_html_e( 'Reset Weekend', 'pods' ); ?></h3>

	<p><?php esc_html_e( 'This feature has been exclusively built for Pods to help developers suffering from "Monday", and allows them to reset the weekend.', 'pods' ); ?></p>
	<p><?php esc_html_e( "By resetting the weekend, you will be sent back to Friday night and the weekend you've just spent will be erased. You will retain all of your memories of the weekend, and be able to relive it in any way you wish.", 'pods' ); ?></p>

	<p class="submit">
		<?php $confirm = __( "Are you sure you want to Reset your Weekend?\n\nThere is no going back, you cannot reclaim anything you've gained throughout your weekend.\n\nYou are about to be groundhoggin' it", 'pods' ); ?>
		<input type="submit" class="button button-primary" name="pods_reset_weekend" value=" reset_weekend( '<?php echo esc_js( date_i18n( 'Y-m-d', strtotime( '-3 days' ) ) ); ?> 19:00:00' ); " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
	</p>
	<?php
}
?>
