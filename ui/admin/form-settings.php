<?php
/**
 * @package  Pods
 * @category Admin
 */

wp_enqueue_script( 'pods' );
wp_enqueue_style( 'pods-form' );

$id = $pod->id();

if ( empty( $fields ) || ! is_array( $fields ) ) {
	$fields = $obj->pod->fields;
}

if ( ! isset( $duplicate ) ) {
	$duplicate = false;
} else {
	$duplicate = (boolean) $duplicate;
}

$block_field_types = Pods_Form::block_field_types();

// unset fields
foreach ( $fields as $k => $field ) {
	if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) ) {
		unset( $fields[ $k ] );
	} elseif ( false === Pods_Form::permission( $field[ 'type' ], $field[ 'name' ], $field, $fields, $pod, $id ) ) {
		if ( pods_v( 'hidden', $field, false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		} elseif ( pods_v( 'read_only', $field, false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		} else {
			unset( $fields[ $k ] );
		}
	} elseif ( ! pods_has_permissions( $field ) ) {
		if ( pods_v( 'hidden', $field, false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		} elseif ( pods_v( 'read_only', $field, false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		}
	}
}

$submittable_fields = $fields;

foreach ( $submittable_fields as $k => $field ) {
	if ( pods_v( 'readonly', $field, false ) ) {
		unset( $submittable_fields[ $k ] );
	}
}

if ( ! isset( $thank_you_alt ) ) {
	$thank_you_alt = $thank_you;
}

$uri_hash   = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

$uid = @session_id();

if ( is_user_logged_in() ) {
	$uid = 'user_' . get_current_user_id();
}

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . ( $duplicate ? 0 : $id ) . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST[ '_pods_nonce' ] ) ) {
	$action = __( 'saved', 'pods' );

	try {
		$params = pods_unslash( (array) $_POST );
		$id     = pods_api()->process_form( $params, $pod, $submittable_fields, $thank_you );

		$message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );
		$error   = sprintf( __( '<strong>Error:</strong> %s %s successfully.', 'pods' ), $obj->item, $action );

		if ( 0 < $id ) {
			echo $obj->message( $message );
		} else {
			echo $obj->error( $error );
		}
	}
	catch ( Exception $e ) {
		echo $obj->error( $e->getMessage() );
	}
} elseif ( isset( $_GET[ 'do' ] ) ) {
	$action = __( 'saved', 'pods' );

	$message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );
	$error   = sprintf( __( '<strong>Error:</strong> %s not %s.', 'pods' ), $obj->item, $action );

	if ( 0 < $pod->id() ) {
		echo $obj->message( $message );
	} else {
		echo $obj->error( $error );
	}
}

if ( ! isset( $label ) ) {
	$label = __( 'Save', 'pods' );
}

$do = 'save';
?>
<form action="" method="post" class="pods-submittable pods-form pods-form-pod-<?php echo esc_attr( $pod->pod ); ?> pods-submittable-ajax">
	<div class="pods-submittable-fields">
		<?php echo Pods_Form::field( 'action', 'pods_admin', 'hidden' ); ?>
		<?php echo Pods_Form::field( 'method', 'process_form', 'hidden' ); ?>
		<?php echo Pods_Form::field( 'do', $do, 'hidden' ); ?>
		<?php echo Pods_Form::field( '_pods_nonce', $nonce, 'hidden' ); ?>
		<?php echo Pods_Form::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
		<?php echo Pods_Form::field( '_pods_id', ( $duplicate ? 0 : $id ), 'hidden' ); ?>
		<?php echo Pods_Form::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
		<?php echo Pods_Form::field( '_pods_form', implode( ',', array_keys( $submittable_fields ) ), 'hidden' ); ?>
		<?php echo Pods_Form::field( '_pods_location', $_SERVER[ 'REQUEST_URI' ], 'hidden' ); ?>

		<?php
		$groups = Pods_Init::$meta->groups_get( $pod->pod_data[ 'type' ], $pod->pod_data[ 'name' ] );

		if ( 0 < count( $groups ) ) {
			foreach ( $groups as $group ) {
				if ( empty( $group[ 'fields' ] ) ) {
					continue;
				}

				$hidden_fields = array();
				?>
				<h3 class="title"><?php echo $group[ 'label' ]; ?></h3>

				<?php echo Pods_Form::field( 'pods_meta', wp_create_nonce( 'pods_meta_settings' ), 'hidden' ); ?>

				<table class="form-table pods-manage-field">
					<?php
					$depends_on = false;

					foreach ( $group[ 'fields' ] as $field ) {
						if ( false === Pods_Form::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
							if ( pods_v( 'hidden', $field, false ) ) {
								$field[ 'type' ] = 'hidden';
							} else {
								continue;
							}
						} elseif ( ! pods_has_permissions( $field ) && pods_v( 'hidden', $field, false ) ) {
							$field[ 'type' ] = 'hidden';
						}

						$value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );

						if ( 'hidden' == $field[ 'type' ] ) {
							$hidden_fields[] = array(
								'field' => $field,
								'value' => $value
							);

							continue;
						}

						$depends = Pods_Form::dependencies( $field );

						if ( ( ! empty( $depends_on ) || ! empty( $depends ) ) && $depends_on != $depends ) {
							if ( ! empty( $depends_on ) ) {
								?>
								</tbody>
								<?php
							}

							if ( ! empty( $depends ) ) {
								?>
								<tbody class="pods-field-option-container <?php echo $depends; ?>">
								<?php
							}
						}

						if ( in_array( $field[ 'type' ], $block_field_types ) ) {
							?>
							<tr valign="top" class="pods-field-option pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Pods_Form::clean( $field[ 'name' ], true ); ?>">
								<td colspan="2">
									<?php echo Pods_Form::field( 'pods_field_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
								</td>
							</tr>
						<?php
						} else {
							?>
							<tr valign="top" class="pods-field-option pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Pods_Form::clean( $field[ 'name' ], true ); ?>">
								<th>
									<?php echo Pods_Form::label( 'pods_field_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?>
								</th>
								<td>
									<?php echo Pods_Form::field( 'pods_field_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
									<?php echo Pods_Form::comment( 'pods_field_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
								</td>
							</tr>
						<?php
						}

						if ( false !== $depends_on || ! empty( $depends ) ) {
							$depends_on = $depends;
						}
					}

					if ( ! empty( $depends_on ) ) {
						?>
						</tbody>
					<?php
					}
				?>
				</table>
				<?php
				foreach ( $hidden_fields as $hidden_field ) {
					$field = $hidden_field[ 'field' ];

					echo Pods_Form::field( 'pods_meta_' . $field[ 'name' ], $hidden_field[ 'value' ], 'hidden' );
				}
			}
		}
		?>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr( $obj->label[ 'edit' ] ); ?>">
			<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
		</p>
	</div>
</form>

<script type="text/javascript">
	if ( 'undefined' == typeof ajaxurl ) {
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
	}

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