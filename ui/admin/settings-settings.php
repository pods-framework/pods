<p><?php _e( 'The following are settings provided for advanced site configurations.', 'pods' ); ?></p>

<?php
wp_enqueue_script( 'pods' );
wp_enqueue_style( 'pods-form' );

/**
 * Allow filtering the list of fields for the settings page.
 *
 * @since TBD
 *
 * @param array $fields List of fields for the settings page.
 */
$fields = apply_filters( 'pods_admin_settings_fields', array() );

$nonce = wp_create_nonce( 'pods_settings_form' );

if ( isset( $_POST['_pods_nonce'] ) && wp_verify_nonce( $_POST['_pods_nonce'], 'pods_settings_form' ) ) {
	$action = __( 'saved', 'pods' );

	$params = pods_unslash( (array) $_POST );

	foreach ( $fields as $key => $field ) {
		// Auto set the field name.
		if ( ! isset( $field['name'] ) ) {
			$field['name'] = $key;
		}

		$value = '';

		if ( isset( $params[ 'pods_field_' . $field['name'] ] ) ) {
			$value = $params[ 'pods_field_' . $field['name'] ];
		} elseif ( 'boolean' === $field['type'] ) {
			$value = '0';
		}

		pods_update_setting( $field['name'], $value );
	}

	$saved = true;

	$message = sprintf( __( '<strong>Success!</strong> %1$s %2$s successfully.', 'pods' ), __( 'Settings', 'pods' ), $action );
	$error   = sprintf( __( '<strong>Error:</strong> %1$s %2$s successfully.', 'pods' ), __( 'Settings', 'pods' ), $action );

	if ( $saved ) {
		pods_message( $message );
	} else {
		pods_message( $error, 'error' );
	}
}

$do = 'save';
?>

<form action="" method="post" class="pods-submittable pods-form pods-form-settings">
	<div class="pods-submittable-fields pods-dependency">
		<?php echo PodsForm::field( 'do', $do, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>

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
</form>

<script type="text/javascript">
	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'dependency', true );
	} );
</script>
