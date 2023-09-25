<?php

use Pods\Tools\Repair;

global $wpdb;

$pods_api = pods_api();

$all_pods = $pods_api->load_pods();

if ( isset( $_POST['_wpnonce'] ) && false !== wp_verify_nonce( $_POST['_wpnonce'], 'pods-settings' ) ) {
	if ( isset( $_POST['pods_repair_pod'] ) || isset( $_POST['pods_repair_pod_preview'] ) ) {
		$pod_name = pods_v( 'pods_field_repair_pod', 'post' );

		if ( is_array( $pod_name ) ) {
			$pod_name = $pod_name[0];
		}

		$pod_name = sanitize_text_field( $pod_name );

		if ( empty( $pod_name ) ) {
			pods_message( __( 'No Pod selected.', 'pods' ), 'error' );
		} else {
			if ( '__all_pods' === $pod_name ) {
				$pods_to_repair = [];

				foreach ( $all_pods as $pod ) {
					if ( 'post_type' !== $pod->get_object_storage_type() ) {
						continue;
					}

					$pods_to_repair[] = $pod->get_name();
				}
			} else {
				$pods_to_repair = [ $pod_name ];
			}

			foreach ( $pods_to_repair as $pod_to_repair ) {
				$pod = $pods_api->load_pod( [ 'name' => $pod_to_repair ], false );

				if ( empty( $pod ) ) {
					pods_message( __( 'Pod not found.', 'pods' ) . ' (' . $pod_to_repair . ')', 'error' );
				} else {
					$tool = pods_container( Repair::class );

					$mode = 'full';

					if ( ! empty( $_POST['pods_repair_pod_preview'] ) ) {
						$mode = 'preview';
					}

					$results = $tool->repair_groups_and_fields_for_pod( $pod, $mode );

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

<h3><?php esc_html_e( 'Repair Pod, Groups, and Fields', 'pods' ); ?></h3>

<p><?php esc_html_e( 'This tool will attempt to repair a Pod, Groups, and Fields. When the tool runs you will be provided with a complete list of repairs made.', 'pods' ); ?></p>

<h4><?php esc_html_e( 'What you can expect', 'pods' ); ?></h4>

<ul class="ul-disc">
	<li><?php esc_html_e( 'If the Pod has Fields but no Groups yet, the first Group will be automatically created', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All Groups with conflicting names will be renamed to prevent conflicts with other registered Groups', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All Fields with conflicting names will be renamed to prevent conflicts with other registered Fields', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All orphaned Fields that belong to a Group that no longer exists will be auto-assigned to the first available Group', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All orphaned Fields that do not belong to a Group will be auto-assigned to the first available Group', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All Fields that have an invalid Field type set will be changed to Plain Text', 'pods' ); ?></li>
	<li><?php esc_html_e( 'All Fields that have invalid arguments set will have those arguments removed (conditional logic saved with serialized data and other arguments not intended for the DB used by early Pods 2.x releases)', 'pods' ); ?></li>
</ul>

<?php
$repair_pods = [];

foreach ( $all_pods as $pod ) {
	if ( 'post_type' !== $pod->get_object_storage_type() ) {
		continue;
	}

	$repair_pods[ $pod->get_name() ] = sprintf(
		'%1$s (%2$s)',
		$pod->get_label(),
		$pod->get_name()
	);
}

asort( $repair_pods );

$repair_pods['__all_pods'] = '-- ' . __( 'Run Repair for All Pods', 'pods' ) . ' (' . __( 'Warning: This may be slow on large configurations', 'pods' ) . ') --';
?>

<?php if ( 1 < count( $repair_pods ) ) : ?>
	<table class="form-table pods-manage-field">
		<?php
		$fields = [
			'repair_pod' => [
				'name'               => 'repair_pod',
				'label'              => __( 'Pod', 'pods' ),
				'type'               => 'pick',
				'pick_format_type'   => 'single',
				'pick_format_single' => 'autocomplete',
				'data'               => $repair_pods,
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
			 . "\n\n" . esc_html__( 'We will be making a few repairs to your configuration that should not be destructive to your data but you should be always have a backup just in case.', 'pods' );
		?>
		<input type="submit" class="button button-primary" name="pods_repair_pod"
			value=" <?php esc_attr_e( 'Repair Pod, Groups, and Fields', 'pods' ); ?> " onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
		<input type="submit" class="button button-secondary" name="pods_repair_pod_preview"
			value=" <?php esc_attr_e( 'Preview (no changes will be made)', 'pods' ); ?> " />
	</p>
<?php else : ?>
	<p><em><?php esc_html_e( 'No Pods available to repair.', 'pods' ); ?></em></p>
<?php endif; ?>

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
