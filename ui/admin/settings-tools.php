<?php

use Pods\Tools\Recover;

global $wpdb;

$pods_api = pods_api();

$all_pods = $pods_api->load_pods();

if ( isset( $_POST['_wpnonce'] ) && false !== wp_verify_nonce( $_POST['_wpnonce'], 'pods-settings' ) ) {
	if ( isset( $_POST['pods_recover_pod'] ) ) {
		$pod_name = pods_v( 'pods_field_recover_pod', 'post' );

		if ( is_array( $pod_name ) ) {
			$pod_name = $pod_name[0];
		}

		$pod_name = sanitize_text_field( $pod_name );

		if ( empty( $pod_name ) ) {
			pods_message( __( 'No Pod selected.', 'pods' ), 'error' );
		} else {
			if ( '__all_pods' === $pod_name ) {
				$pods_to_recover = wp_list_pluck( $all_pods, 'name' );
			} else {
				$pods_to_recover = [ $pod_name ];
			}

			foreach ( $pods_to_recover as $pod_to_recover ) {
				$pod = $pods_api->load_pod( [ 'name' => $pod_to_recover ], false );

				if ( empty( $pod ) ) {
					pods_message( __( 'Pod not found.', 'pods' ) . ' (' . $pod_to_recover . ')', 'error' );
				} else {
					$recover = pods_container( Recover::class );

					$results = $recover->recover_groups_and_fields_for_pod( $pod, 'full' );

					pods_message( $results['message_html'] );
				}
			}
		}
	} elseif ( isset( $_POST['pods_recreate_tables'] ) ) {
		pods_upgrade()->delta_tables();

		pods_redirect( pods_query_arg( array( 'pods_recreate_tables_success' => 1 ), array( 'page', 'tab' ) ) );
	}
} elseif ( 1 === (int) pods_v( 'pods_recreate_tables_success' ) ) {
	pods_message( 'Pods tables have been recreated.' );
}
?>

<h3><?php esc_html_e( 'Recover Groups and Fields', 'pods' ); ?></h3>

<p><?php esc_html_e( 'This tool will attempt to recover Groups and Fields that do not appear when editing a Pod. After selecting the Pod, you will be redirected to the Edit Pod screen to see the full list of Groups and Fields available after the recovery process.', 'pods' ); ?></p>

<h4><?php esc_html_e( 'What you can expect', 'pods' ); ?></h4>

<ul class="ul-disc">
	<li><?php esc_html_e( 'All conflicted Groups will be auto-renamed to prevent conflict with other registered Groups', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All conflicted Fields will be auto-renamed to prevent conflict with other registered Fields', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All orphaned Fields that do not belong to a Group will be auto-assigned to the first available Group', 'pods' ); ?></li>
</ul>

<?php
$recover_pods = [
	'__all_pods' => '-- ' . __( 'Run Recovery for All Pods', 'pods' ) . ' --',
];

foreach ( $all_pods as $pod ) {
	if ( 'post_type' !== $pod->get_object_storage_type() ) {
		continue;
	}

	$recover_pods[ $pod->get_name() ] = sprintf(
		'%1$s (%2$s)',
		$pod->get_label(),
		$pod->get_name()
	);
}

asort( $recover_pods );
?>

<table class="form-table pods-manage-field">
	<?php
	$fields = [
		'recover_pod' => [
			'name'               => 'recover_pod',
			'label'              => __( 'Pod', 'pods' ),
			'type'               => 'pick',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'autocomplete',
			'data'               => $recover_pods,
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
	<input type="submit" class="button button-primary" name="pods_recover_pod"
		value="<?php esc_attr_e( 'Recover Groups and Fields', 'pods' ); ?> " />
</p>

<hr />

<?php
$relationship_table = $wpdb->prefix . 'podsrel';
?>

<h3><?php esc_html_e( 'Recreate missing tables', 'pods' ); ?></h3>

<p><?php esc_html_e( 'This will recreate missing tables if there are any that do not exist.', 'pods' ); ?></p>
<h4><?php esc_html_e( 'What you can expect', 'pods' ); ?></h4>
<ul>
	<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'KEEP', 'pods' ); ?>:</strong> <?php
		// translators: %s is the name of the wp_podsrel table in the database.
		printf( esc_html__( '%s will remain untouched if it already exists', 'pods' ), esc_html( $relationship_table ) );
		?></li>
	<li>🆗 &nbsp;&nbsp;<strong><?php esc_html_e( 'CREATE', 'pods' ); ?>:</strong> <?php
		// translators: %s is the name of the wp_podsrel table in the database.
		printf( esc_html__( '%s will be created if it does not exist', 'pods' ), esc_html( $relationship_table ) );
		?></li>
</ul>

<p class="submit">
	<?php $confirm = __( 'Are you sure you want to do this?', 'pods' ); ?>
	<input type="submit" class="button button-primary" name="pods_recreate_tables" value=" <?php esc_attr_e( 'Recreate missing tables', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
</p>
