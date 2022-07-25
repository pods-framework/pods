<?php
/**
 * @var array   $fields
 * @var boolean $fields_only
 * @var Pods    $pod
 * @var array   $params
 * @var string  $label
 * @var string  $thank_you
 * @var string  $output_type
 */
pods_form_enqueue_style( 'pods-form' );
pods_form_enqueue_script( 'pods' );

$pod_name = $pod->pod;
$id       = $pod->id();

// Set up fields.
foreach ( $fields as $k => $field ) {
	// Make sure all required array keys exist.
	if ( ! $field instanceof \Pods\Whatsit\Field ) {
		$field = wp_parse_args( $field, [
			'name'    => '',
			'type'    => '',
			'label'   => '',
			'help'    => '',
		] );

		$fields[ $k ] = $field;
	}

	if ( in_array( $field['name'], [ 'created', 'modified' ], true ) ) {
		unset( $fields[ $k ] );
	} elseif ( ! pods_permission( $field ) ) {
		if ( pods_v( 'hidden', $field, false ) ) {
			$fields[ $k ]['type'] = 'hidden';
		} elseif ( pods_v( 'read_only', $field, false ) ) {
			$fields[ $k ]['readonly'] = true;
		} else {
			unset( $fields[ $k ] );
		}
	} elseif ( ! pods_has_permissions( $field ) ) {
		if ( pods_v( 'hidden', $field, false ) ) {
			$fields[ $k ]['type'] = 'hidden';
		} elseif ( pods_v( 'read_only', $field, false ) ) {
			$fields[ $k ]['readonly'] = true;
		}
	}
}

$submittable_fields = $fields;

foreach ( $submittable_fields as $k => $field ) {
	if ( ! pods_v( 'readonly', $field, false ) ) {
		continue;
	}

	unset( $submittable_fields[ $k ] );
}

$uri_hash   = wp_create_nonce( 'pods_uri_' . $_SERVER['REQUEST_URI'] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

$uid = pods_session_id();

if ( is_user_logged_in() ) {
	$uid = 'user_' . get_current_user_id();
}

$nonce = wp_create_nonce( 'pods_form_' . $pod_name . '_' . $uid . '_' . $id . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST['_pods_nonce'] ) ) {
	try {
		$id = $pod->api->process_form( $_POST, $pod, $submittable_fields, $thank_you );
	} catch ( Exception $e ) {
		echo '<div class="pods-message pods-message-error">' . $e->getMessage() . '</div>';
	}
}

$field_prefix = '';
?>

<?php if ( ! $fields_only ) : ?>
<?php $field_prefix = 'pods_field_'; ?>
<form action="" method="post" class="pods-submittable pods-form pods-form-front pods-form-pod-<?php echo esc_attr( $pod_name ); ?> pods-submittable-ajax" data-location="<?php echo esc_attr( $thank_you ); ?>">
	<div class="pods-submittable-fields">
		<?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
		<?php echo PodsForm::field( 'method', 'process_form', 'hidden' ); ?>
		<?php echo PodsForm::field( 'do', ( ! empty( $id ) ? 'save' : 'create' ), 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_pod', $pod_name, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_id', $id, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $submittable_fields ) ), 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_location', $_SERVER['REQUEST_URI'], 'hidden' ); ?>
		<?php endif; ?>

		<?php
		/**
		 * Runs before fields are outputted.
		 *
		 * @params array $fields Fields of the form.
		 * @params object $pod The current Pod object.
		 * @params array $params The form's parameters.
		 *
		 * @since  2.3.19
		 */
		do_action( 'pods_form_pre_fields', $fields, $pod, $params );

		$field_row_classes = 'pods-field-html-class';
		$heading_tag       = 'h3';

		$pre_callback = static function ( $field_name, $id, $field, $pod ) use ( $fields, $params ) {
			/**
			 * Runs before a field is output.
			 *
			 * @since 2.3.19
			 *
			 * @param array  $field  The current field.
			 * @param array  $fields All fields of the form.
			 * @param object $pod    The current Pod object.
			 * @param array  $params The form's parameters.
			 */
			do_action( 'pods_form_pre_field', $field, $fields, $pod, $params );
		};

		$post_callback = static function ( $field_name, $id, $field, $pod ) use ( $fields, $params ) {
			/**
			 * Runs after a field is output.
			 *
			 * @since  2.3.19
			 *
			 * @params array  $field  The current field.
			 * @params array  $fields All fields of the form.
			 * @params object $pod    The current Pod object.
			 * @params array  $params The form's parameters.
			 */
			do_action( 'pods_form_after_field', $field, $fields, $pod, $params );
		};

		$template        = 'ui/forms/list-rows.php';
		$template_before = '';
		$template_after  = '';

		if ( 'div' === $output_type ) {
			$template = 'ui/forms/div-rows.php';
		} elseif ( 'p' === $output_type ) {
			$template = 'ui/forms/p-rows.php';
		} elseif ( 'table' === $output_type ) {
			$template        = 'ui/forms/table-rows.php';
			$template_before = '<table>';
			$template_after  = '</table>';
		}

		echo $template_before;

		pods_view( PODS_DIR . $template, compact( array_keys( get_defined_vars() ) ) );

		echo $template_after;

		/**
		 * Runs after all fields are outputted.
		 *
		 * @params array $fields Fields of the form
		 * @params object $pod The current Pod object
		 * @params array $params The form's parameters.
		 *
		 * @since  2.3.19
		 */
		do_action( 'pods_form_after_fields', $fields, $pod, $params );
		?>

		<?php if ( ! $fields_only ) : ?>
		<p class="pods-submit">
			<img class="waiting" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>" alt="<?php esc_attr_e( 'Submitting...', 'pods' ); ?>">
			<input type="submit" value=" <?php echo esc_attr( $label ); ?> " class="pods-submit-button" />

			<?php do_action( 'pods_form_after_submit', $pod, $fields, $params ); ?>
		</p>
	</div>
</form>

	<script type="text/javascript">
		if ( 'undefined' === typeof pods_form_init ) {
			var pods_form_init = true;

			document.addEventListener( "DOMContentLoaded", function () {
				if ( 'undefined' !== typeof jQuery( document ).Pods ) {

					if ( 'undefined' === typeof ajaxurl ) {
						window.ajaxurl = '<?php echo pods_slash( admin_url( 'admin-ajax.php' ) ); ?>';
					}

					jQuery( document ).Pods( 'validate' );
					jQuery( document ).Pods( 'submit' );
					jQuery( document ).Pods( 'dependency', true ); // Pass `true` to trigger init.
				}
			}, false );
		}
	</script>
<?php endif; ?>
