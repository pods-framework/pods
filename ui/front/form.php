<?php
wp_enqueue_style( 'pods-form' );
wp_enqueue_script( 'pods' );

// unset fields
foreach ( $fields as $k => $field ) {

	// Make sure all required array keys exist.
	$field = wp_parse_args( $field, array(
		'name' => '',
		'type' => '',
		'label' => '',
		'help' => '',
		'options' => array(),
	) );
	$fields[ $k ] = $field;

	if ( in_array( $field[ 'name' ], array( 'created', 'modified' ), true ) ) {
		unset( $fields[ $k ] );
	}
	elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $pod->id() ) ) {
		if ( pods_var( 'hidden', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		}
		elseif ( pods_var( 'read_only', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		}
		else {
			unset( $fields[ $k ] );
		}
	}
	elseif ( !pods_has_permissions( $field[ 'options' ] ) ) {
		if ( pods_var( 'hidden', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		}
		elseif ( pods_var( 'read_only', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		}
	}
}

$submittable_fields = $fields;

foreach ( $submittable_fields as $k => $field ) {
	if ( pods_var( 'readonly', $field, false ) ) {
		unset( $submittable_fields[ $k ] );
	}
}

$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

$uid = @session_id();

if ( is_user_logged_in() ) {
	$uid = 'user_' . get_current_user_id();
}

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . $pod->id() . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST[ '_pods_nonce' ] ) ) {
    try {
        $id = $pod->api->process_form( $_POST, $pod, $submittable_fields, $thank_you );
    }
    catch ( Exception $e ) {
        echo '<div class="pods-message pods-message-error">' . $e->getMessage() . '</div>';
    }
}

$field_prefix = '';

if ( !$fields_only ) {
	$field_prefix = 'pods_field_';
?>
	<form action="" method="post" class="pods-submittable pods-form pods-form-front pods-form-pod-<?php echo esc_attr( $pod->pod ); ?> pods-submittable-ajax" data-location="<?php echo esc_attr( $thank_you ); ?>">
		<div class="pods-submittable-fields">
			<?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
			<?php echo PodsForm::field( 'method', 'process_form', 'hidden' ); ?>
			<?php echo PodsForm::field( 'do', ( 0 < $pod->id() ? 'save' : 'create' ), 'hidden' ); ?>
			<?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
			<?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
			<?php echo PodsForm::field( '_pods_id', $pod->id(), 'hidden' ); ?>
			<?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
			<?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $submittable_fields ) ), 'hidden' ); ?>
			<?php echo PodsForm::field( '_pods_location', $_SERVER[ 'REQUEST_URI' ], 'hidden' ); ?>
<?php
}

/**
 * Runs before fields are outputted.
 *
 * @params array $fields Fields of the form.
 * @params object $pod The current Pod object.
 * @params array $params The form's parameters.
 *
 * @since 2.3.19
 */
do_action( 'pods_form_pre_fields', $fields, $pod, $params );
?>

			<ul class="pods-form-fields pods-dependency">
				<?php
					foreach ( $fields as $field ) {
						if ( 'hidden' == $field[ 'type' ] ) {
							continue;
						}

						/**
						 * Runs before a field is outputted.
						 *
						 * @param array $field The current field.
						 * @param array $fields All fields of the form.
						 * @param object $pod The current Pod object.
						 * @param array $params The form's parameters.
						 *
						 * @since 2.3.19
						 */
						do_action( 'pods_form_pre_field', $field, $fields, $pod, $params );

						$default_class = ' pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $field[ 'name' ] );

						// Setup field conditionals.
						$dependencies = PodsForm::dependencies( $field, 'pods-field-' );
						if ( ! empty( $dependencies['classes'] ) ) {
							$default_class .= ' ' . $dependencies['classes'];
						}
						$dep_data = $dependencies['data'];

						/**
						 * Filter the html class used on form field list item element.
						 *
						 * @param string $html_class The HTML class.
						 * @param array  $field      The current field.
						 *
						 * @since 2.7.2
						 */
						$html_class = apply_filters( 'pods_form_html_class', 'pods-field-html-class', $field ) . $default_class;
				?>
					<li class="pods-field <?php echo esc_attr( $html_class, true ); ?>" <?php PodsForm::data( $dep_data ); ?>>
						<div class="pods-field-label">
							<?php echo PodsForm::label( $field_prefix . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?>
						</div>

						<div class="pods-field-input">
							<?php echo PodsForm::field( $field_prefix . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), $field[ 'type' ], $field, $pod, $pod->id() ); ?>

							<?php echo PodsForm::comment( $field_prefix . $field[ 'name' ], null, $field ); ?>
						</div>
					</li>
				<?php
						/**
						 * Runs after a field is outputted.
						 *
						 * @params array $field The current field.
						 * @params array $fields All fields of the form.
						 * @params object $pod The current Pod object.
						 * @params array $params The form's parameters.
						 *
						 * @since 2.3.19
						 */
						do_action( 'pods_form_after_field', $field, $fields, $pod, $params );
					}
				?>
			</ul>

			<?php
				foreach ( $fields as $field ) {
					if ( 'hidden' != $field[ 'type' ] ) {
						continue;
					}

					echo PodsForm::field( $field_prefix . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), 'hidden' );
				}

				/**
				 * Runs after all fields are outputted.
				 *
				 * @params array $fields Fields of the form
				 * @params object $pod The current Pod object
				 * @params array $params The form's parameters.
				 *
				 * @since 2.3.19
				 */
				do_action( 'pods_form_after_fields', $fields, $pod, $params );
			?>

<?php
if ( !$fields_only ) {
?>
        <p class="pods-submit">
            <img class="waiting" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>" alt="">
            <input type="submit" value=" <?php echo esc_attr( $label ); ?> " class="pods-submit-button" />

            <?php do_action( 'pods_form_after_submit', $pod, $fields, $params ); ?>
        </p>
    </div>
</form>

<script type="text/javascript">
	if ( 'undefined' === typeof pods_form_init ) {
		var pods_form_init = true;

		document.addEventListener( "DOMContentLoaded", function() {
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
<?php
}
