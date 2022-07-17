<?php
/**
 * @var string      $form_type
 * @var PodsUI      $obj
 * @var Pods        $pod
 * @var null|string $thank_you
 * @var null|string $thank_you_alt
 * @var null|string $label
 */

use Pods\Static_Cache;

pods_form_enqueue_script( 'pods' );
pods_form_enqueue_style( 'pods-form' );

$is_settings_pod = 'settings' === $pod->pod_data['type'];

if ( empty( $fields ) || ! is_array( $fields ) ) {
	$fields = $obj->pod->fields;
}

if ( ! isset( $duplicate ) || $is_settings_pod ) {
	$duplicate = false;
} else {
	$duplicate = (boolean) $duplicate;
}

$groups = PodsInit::$meta->groups_get( $pod->pod_data['type'], $pod->pod_data['name'], $fields );

if ( 1 === count( $groups ) ) {
	$first_group = current( $groups );

	if ( 0 === count( $first_group['fields'] ) ) {
		$groups = [];
	}
}

$pod_name    = $pod->pod_data['name'];
$pod_options = $pod->pod_data['options'];
$pod_options = apply_filters( "pods_advanced_content_type_pod_data_{$pod_name}", $pod_options, $pod->pod_data['name'] );
$pod_options = apply_filters( 'pods_advanced_content_type_pod_data', $pod_options, $pod->pod_data['name'] );

$group_fields       = array();
$submittable_fields = array();

foreach ( $groups as $g => $group ) {
	// unset fields
	foreach ( $group['fields'] as $k => $field ) {
		if ( in_array( $field['name'], array( 'created', 'modified' ), true ) ) {
			unset( $group['fields'][ $k ] );

			continue;
		} elseif ( ! pods_permission( $field ) ) {
			if ( (boolean) pods_v( 'hidden', $field['options'], false ) ) {
				$group['fields'][ $k ]['type'] = 'hidden';
			} elseif ( (boolean) pods_v( 'read_only', $field['options'], false ) ) {
				$group['fields'][ $k ]['readonly'] = true;
			} else {
				unset( $group['fields'][ $k ] );

				continue;
			}
		} elseif ( ! pods_has_permissions( $field ) ) {
			if ( (boolean) pods_v( 'hidden', $field['options'], false ) ) {
				$group['fields'][ $k ]['type'] = 'hidden';
			} elseif ( (boolean) pods_v( 'read_only', $field['options'], false ) ) {
				$group['fields'][ $k ]['readonly'] = true;
			}
		}//end if

		if ( ! pods_v_sanitized( 'readonly', $field, false ) ) {
			$submittable_fields[ $field['name'] ] = $group['fields'][ $k ];
		}

		$group_fields[ $field['name'] ] = $group['fields'][ $k ];
	}//end foreach
	$groups[ $g ] = $group;
}//end foreach

if ( ! isset( $thank_you_alt ) ) {
	$thank_you_alt = $thank_you;
}

$uri_hash   = wp_create_nonce( 'pods_uri_' . pods_current_path() );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

if ( is_user_logged_in() ) {
	$uid = 'user_' . get_current_user_id();
} else {
	$uid = pods_session_id();
}

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . ( $duplicate ? 0 : $pod->id() ) . '_' . $uri_hash . '_' . $field_hash );

$submit_result = null;

if ( isset( $_POST['_pods_nonce'] ) ) {
	try {
		$params = pods_unslash( (array) $_POST );
		$id     = $pod->api->process_form( $params, $pod, $submittable_fields, $thank_you );

		$submit_result = 0 < $id;
	} catch ( Exception $e ) {
		echo $obj->error( $e->getMessage() );
	}
} elseif ( isset( $_GET['do'] ) ) {
	$submit_result = 0 < $pod->id();
}

if ( null !== $submit_result ) {
	$messages = [
		'success'            => __( 'Success', 'pods' ),
		'error'              => __( 'Error', 'pods' ),
		// translators: %s: The singular item label.
		'view_item'          => __( 'View %s', 'pods' ),
		// translators: %s: The singular item label.
		'success_saved'      => _x( '%s saved successfully', 'The success message shown after saving form', 'pods' ),
		// translators: %s: The singular item label.
		'success_created'    => _x( '%s created successfully', 'The success message shown after saving form', 'pods' ),
		// translators: %s: The singular item label.
		'success_duplicated' => _x( '%s duplicated successfully', 'The success message shown after saving form', 'pods' ),
		// translators: %s: The singular item label.
		'error_saved'        => _x( '%s not saved', 'The error message shown after saving form', 'pods' ),
		// translators: %s: The singular item label.
		'error_created'      => _x( '%s not created', 'The error message shown after saving form', 'pods' ),
		// translators: %s: The singular item label.
		'error_duplicated'   => _x( '%s not duplicated', 'The error message shown after saving form', 'pods' ),
	];

	$success_message = sprintf( '<strong>%1$s:</strong> %2$s.', $messages['success'], $messages['success_saved'] );
	$error_message   = sprintf( '<strong>%1$s:</strong> %2$s.', $messages['error'], $messages['error_saved'] );

	if ( ! $is_settings_pod ) {
		if ( 'create' === pods_v( 'do', 'post', pods_v( 'do', 'get', 'save' ) ) ) {
			$success_message = sprintf( '<strong>%1$s:</strong> %2$s.', $messages['success'], $messages['success_created'] );
			$error_message   = sprintf( '<strong>%1$s:</strong> %2$s.', $messages['error'], $messages['error_created'] );
		} elseif ( 'duplicate' === pods_v( 'do', 'get', 'save' ) ) {
			$success_message = sprintf( '<strong>%1$s:</strong> %2$s.', $messages['success'], $messages['success_duplicated'] );
			$error_message   = sprintf( '<strong>%1$s:</strong> %2$s.', $messages['error'], $messages['error_duplicated'] );
		}
	}

	if ( $submit_result ) {
		$message = sprintf( $success_message, $obj->item );

		if ( ! $is_settings_pod && ! empty( $pod_options['detail_url'] ) ) {
			$message_view =
			$message .= sprintf(
				' <a target="_blank" rel="noopener noreferrer" href="%1$s">%2$s</a>',
				esc_url( $pod->field( 'detail_url' ) ),
				esc_html( sprintf( $messages['view_item'], $obj->item ) )
			);
		}

		echo $obj->message( $message );
	} else {
		$error = sprintf( $error_message, $obj->item );

		echo $obj->error( $error );
	}
}

if ( ! isset( $label ) ) {
	$label = __( 'Save', 'pods' );
}

$do = 'create';

if ( 0 < $pod->id() ) {
	if ( $duplicate ) {
		$do = 'duplicate';
	} else {
		$do = 'save';
	}
}

$counter = (int) pods_static_cache_get( $pod->pod . '-counter', 'pods-forms' );

$counter ++;

pods_static_cache_set( $pod->pod . '-counter', $counter, 'pods-forms' );
?>

<form action="" method="post"
	class="pods-submittable pods-form pods-form-pod-<?php echo esc_attr( $pod->pod ); ?> pods-submittable-ajax"
	id="pods-form-<?php echo esc_attr( $pod->pod . '-' . $counter ); ?>"
>
	<div class="pods-submittable-fields">
		<?php
		echo PodsForm::field( 'action', 'pods_admin', 'hidden' );
		echo PodsForm::field( 'method', 'process_form', 'hidden' );
		echo PodsForm::field( 'do', $do, 'hidden' );
		echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' );
		echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' );
		echo PodsForm::field( '_pods_id', ( $duplicate ? 0 : $pod->id() ), 'hidden' );
		echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' );
		echo PodsForm::field( '_pods_form', implode( ',', array_keys( $submittable_fields ) ), 'hidden' );
		echo PodsForm::field( '_pods_location', $_SERVER['REQUEST_URI'], 'hidden' );

		pods_view( PODS_DIR . 'ui/forms/type/' . sanitize_title( $form_type ) . '.php', compact( array_keys( get_defined_vars() ) ) );
		?>
	</div>
</form>

<script type="text/javascript">
	if ( 'undefined' == typeof ajaxurl ) {
		var ajaxurl = '<?php echo pods_slash( admin_url( 'admin-ajax.php' ) ); ?>';
	}

	if ( 'undefined' == typeof pods_form_thank_you ) {
		var pods_form_thank_you = null;
	}

	<?php if ( $is_settings_pod ) : ?>
		var pods_admin_submit_callback = function ( id ) {
			document.location = '<?php echo pods_slash( pods_query_arg( array( 'do' => $do ) ) ); ?>';
		}
	<?php else : ?>
		var pods_admin_submit_callback = function ( id ) {

			id = parseInt( id, 10 );
			var thank_you = '<?php echo esc_url_raw( $thank_you ); ?>';
			var thank_you_alt = '<?php echo esc_url_raw( $thank_you_alt ); ?>';

			if ( 'undefined' != typeof pods_form_thank_you && null !== pods_form_thank_you ) {
				thank_you = pods_form_thank_you;
			}

			if ( isNaN( id ) ) {
				document.location = thank_you_alt.replace( 'X_ID_X', String( 0 ) );
			}
			else {
				document.location = thank_you.replace( 'X_ID_X', String( id ) );
			}
		}
	<?php endif; ?>

	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'dependency', true );
		$( document ).Pods( 'confirm' );
		$( document ).Pods( 'exit_confirm' );
	} );
</script>
