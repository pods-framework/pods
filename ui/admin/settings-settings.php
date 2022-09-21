<?php
wp_enqueue_script( 'pods' );
pods_form_enqueue_style( 'pods-form' );

/**
 * Allow filtering the list of fields for the settings page.
 *
 * @since 2.8.0
 *
 * @param array $fields List of fields for the settings page.
 */
$fields = apply_filters( 'pods_admin_settings_fields', array() );

if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'pods-settings' ) ) {
	if ( isset( $_POST['pods_cache_flush'] ) ) {
		// Handle clearing cache.
		$api = pods_api();

		$api->cache_flush_pods();

		if ( defined( 'PODS_PRELOAD_CONFIG_AFTER_FLUSH' ) && PODS_PRELOAD_CONFIG_AFTER_FLUSH ) {
			$api->load_pods( array( 'bypass_cache' => true ) );
		}

		pods_redirect( pods_query_arg( array( 'pods_cache_flushed' => 1 ), array( 'page', 'tab' ) ) );
	} else {
		// Handle saving settings.
		$action = __( 'saved', 'pods' );

		$params = pods_unslash( (array) $_POST );

		$settings_to_save = [];

		$layout_field_types = PodsForm::layout_field_types();

		foreach ( $fields as $key => $field ) {
			// Auto set the field name.
			if ( ! isset( $field['name'] ) ) {
				$field['name'] = $key;
			}

			// Skip layout field types.
			if ( isset( $field['type'] ) && in_array( $field['type'], $layout_field_types, true ) ) {
				continue;
			}

			$value = '';

			if ( isset( $params[ 'pods_field_' . $field['name'] ] ) ) {
				$value = $params[ 'pods_field_' . $field['name'] ];
			} elseif ( 'boolean' === $field['type'] ) {
				$value = '0';
			}

			$sanitize_callback = pods_v( 'sanitize_callback', $field, 'sanitize_text_field', true );

			// Sanitize value if needed.
			if ( is_callable( $sanitize_callback ) ) {
				$value = $sanitize_callback( $value );
			}

			$settings_to_save[ $field['name'] ] = $value;
		}

		if ( $settings_to_save ) {
			pods_update_settings( $settings_to_save );

			$message = sprintf( __( '<strong>Success!</strong> %1$s %2$s successfully.', 'pods' ), __( 'Settings', 'pods' ), $action );

			pods_message( $message );
		} else {
			$error = sprintf( __( '<strong>Error:</strong> %1$s %2$s successfully.', 'pods' ), __( 'Settings', 'pods' ), $action );

			pods_message( $error, 'error' );
		}
	}
} elseif ( 1 === (int) pods_v( 'pods_cache_flushed' ) ) {
	pods_message( __( 'Pods transients and cache have been cleared.', 'pods' ) );
}

$do = 'save';
?>

<h3><?php _e( 'Clear Pods Cache', 'pods' ); ?></h3>

<p><?php esc_html_e( 'This will clear all of the transients and object cache that are used by Pods and your site.', 'pods' ); ?></p>

<p class="submit">
	<input type="submit" class="button button-primary" name="pods_cache_flush" value="<?php esc_attr_e( 'Clear Pods Cache', 'pods' ); ?>" />
</p>

<hr />

<div class="pods-submittable-fields pods-dependency">
	<?php echo PodsForm::field( 'do', $do, 'hidden' ); ?>

	<?php
	foreach ( $fields as $key => $field ) {
		// Auto set the field name.
		if ( ! isset( $field['name'] ) ) {
			$fields[ $key ]['name'] = $key;
		}

		// Skip if not hidden.
		if ( 'hidden' !== $field['type'] ) {
			continue;
		}

		// Output hidden field at top.
		echo PodsForm::field( 'pods_field_' . $field['name'], pods_get_setting( $field['name'], pods_v( 'default', $field ) ), 'hidden' );

		// Remove from list of fields to render below.
		unset( $fields[ $key ] );
	}
	?>
	<table class="form-table pods-manage-field">
		<?php
		$field_prefix      = 'pods_field_';
		$field_row_classes = '';
		$id                = '';
		$value_callback    = static function( $field_name, $id, $field, $pod ) {
			return pods_get_setting( $field_name, pods_v( 'default', $field ) );
		};

		pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );
	?>
	</table>

	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'pods' ); ?>">
		<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
	</p>
</div>

<script type="text/javascript">
	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'dependency', true );
		$( document ).Pods( 'qtip', '.pods-submittable' );
	} );
</script>
