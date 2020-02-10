<?php
wp_enqueue_script( 'pods' );
wp_enqueue_style( 'pods-form' );

if ( empty( $fields ) || ! is_array( $fields ) ) {
	$fields = $obj->pod->fields;
}

if ( ! isset( $duplicate ) ) {
	$duplicate = false;
} else {
	$duplicate = (boolean) $duplicate;
}

// unset fields
foreach ( $fields as $k => $field ) {
	if ( in_array( $field['name'], array( 'created', 'modified' ), true ) ) {
		unset( $fields[ $k ] );
	} elseif ( false === PodsForm::permission( $field['type'], $field['name'], $field['options'], $fields, $pod, $pod->id() ) ) {
		if ( pods_v_sanitized( 'hidden', $field['options'], false ) ) {
			$fields[ $k ]['type'] = 'hidden';
		} else {
			unset( $fields[ $k ] );
		}
	} elseif ( ! pods_has_permissions( $field['options'] ) && pods_v_sanitized( 'hidden', $field['options'], false ) ) {
		$fields[ $k ]['type'] = 'hidden';
	}
}

$submittable_fields = $fields;

foreach ( $submittable_fields as $k => $field ) {
	if ( pods_v_sanitized( 'readonly', $field, false ) ) {
		unset( $submittable_fields[ $k ] );
	}
}

if ( ! isset( $thank_you_alt ) ) {
	$thank_you_alt = $thank_you;
}

$uri_hash   = wp_create_nonce( 'pods_uri_' . $_SERVER['REQUEST_URI'] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

$uid = @session_id();

if ( is_user_logged_in() ) {
	$uid = 'user_' . get_current_user_id();
}

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . ( $duplicate ? 0 : $pod->id() ) . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST['_pods_nonce'] ) ) {
	$action = __( 'saved', 'pods' );

	try {
		$params = pods_unslash( (array) $_POST );
		$id     = $pod->api->process_form( $params, $pod, $submittable_fields, $thank_you );

		$message = sprintf( __( '<strong>Success!</strong> %1$s %2$s successfully.', 'pods' ), $obj->item, $action );
		$error   = sprintf( __( '<strong>Error:</strong> %1$s %2$s successfully.', 'pods' ), $obj->item, $action );

		if ( 0 < $id ) {
			echo $obj->message( $message );
		} else {
			echo $obj->error( $error );
		}
	} catch ( Exception $e ) {
		echo $obj->error( $e->getMessage() );
	}
} elseif ( isset( $_GET['do'] ) ) {
	$action = __( 'saved', 'pods' );

	$message = sprintf( __( '<strong>Success!</strong> %1$s %2$s successfully.', 'pods' ), $obj->item, $action );
	$error   = sprintf( __( '<strong>Error:</strong> %1$s not %2$s.', 'pods' ), $obj->item, $action );

	if ( 0 < $pod->id() ) {
		echo $obj->message( $message );
	} else {
		echo $obj->error( $error );
	}
}//end if

if ( ! isset( $label ) ) {
	$label = __( 'Save', 'pods' );
}

$do = 'save';
?>

<form action="" method="post" class="pods-submittable pods-form pods-form-pod-<?php echo esc_attr( $pod->pod ); ?>">
	<div class="pods-submittable-fields">
		<?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
		<?php echo PodsForm::field( 'method', 'process_form', 'hidden' ); ?>
		<?php echo PodsForm::field( 'do', $do, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_id', $pod->id(), 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $submittable_fields ) ), 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_location', $_SERVER['REQUEST_URI'], 'hidden' ); ?>

		<?php
		foreach ( $fields as $field ) {
			if ( 'hidden' !== $field['type'] ) {
				continue;
			}

			echo PodsForm::field(
				'pods_field_' . $field['name'], $pod->field(
					array(
						'name'    => $field['name'],
						'in_form' => true,
					)
				), 'hidden'
			);
		}
		?>
		<table class="form-table pods-manage-field">
			<?php
			$depends_on = false;

			foreach ( $fields

			as $field ) {
				if ( 'hidden' === $field['type'] ) {
					continue;
				}

				$dep_options = PodsForm::dependencies( $field );
				$dep_classes = $dep_options['classes'];
				$dep_data    = $dep_options['data'];

				if ( ( ! empty( $depends_on ) || ! empty( $dep_classes ) ) && $depends_on != $dep_classes ) {
					if ( ! empty( $depends_on ) ) {
						?>
						</tbody>
						<?php
					}

					if ( ! empty( $dep_classes ) ) {
						?>
						<tbody class="pods-field-option-container <?php echo esc_attr( $dep_classes ); ?>" <?php PodsForm::data( $dep_data ); ?>>
				<?php
					}
				}
			?>
			<tr valign="top" class="pods-field-option pods-field <?php echo esc_attr( 'pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . PodsForm::clean( $field['name'], true ) ); ?>">
				<th>
					<?php echo PodsForm::label( 'pods_field_' . $field['name'], $field['label'], $field['help'], $field ); ?>
				</th>
				<td>
					<?php
					echo PodsForm::field(
						'pods_field_' . $field['name'], $pod->field(
							array(
								'name'    => $field['name'],
								'in_form' => true,
							)
						), $field['type'], $field, $pod, $pod->id()
					);
					?>
					<?php echo PodsForm::comment( 'pods_field_' . $field['name'], $field['description'], $field ); ?>
				</td>
			</tr>
			<?php
			if ( false !== $depends_on || ! empty( $dep_classes ) ) {
				$depends_on = $dep_classes;
			}
			}//end foreach

			if ( ! empty( $depends_on ) ) {
			?>
			</tbody>
		<?php
			}
		?>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr( $obj->label['edit'] ); ?>">
			<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
		</p>
	</div>
</form>

<script type="text/javascript">
	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'dependency' );
		$( document ).Pods( 'confirm' );
		$( document ).Pods( 'exit_confirm' );
	} );

	var pods_admin_submit_callback = function ( id ) {
		document.location = '<?php echo pods_slash( pods_query_arg( array( 'do' => $do ) ) ); ?>';
	}
</script>
