<?php
wp_enqueue_style( 'pods-form', false, array(), false, true );

if ( !wp_script_is( 'pods', 'done' ) ) {
	wp_print_scripts( 'pods' );
}

/**
 * @var array $fields
 * @var string $label
 */
// unset fields
foreach ( $fields as $k => $field ) {
	if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) )
		unset( $fields[ $k ] );
	elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $pod->id() ) ) {
		if ( pods_v_sanitized( 'hidden', $field[ 'options' ], false ) )
			$fields[ $k ][ 'type' ] = 'hidden';
		elseif ( pods_v_sanitized( 'read_only', $field[ 'options' ], false ) )
			$fields[ $k ][ 'readonly' ] = true;
		else
			unset( $fields[ $k ] );
	}
	elseif ( !pods_has_permissions( $field[ 'options' ] ) ) {
		if ( pods_v_sanitized( 'hidden', $field[ 'options' ], false ) )
			$fields[ $k ][ 'type' ] = 'hidden';
		elseif ( pods_v_sanitized( 'read_only', $field[ 'options' ], false ) )
			$fields[ $k ][ 'readonly' ] = true;
	}
}
?>

<div class="pods-submittable-fields">
	<?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
	<?php echo PodsForm::field( 'method', 'process_form', 'hidden' ); ?>
	<?php echo PodsForm::field( 'do', ( 0 < $pod->id() ? 'save' : 'create' ), 'hidden' ); ?>
	<?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
	<?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
	<?php echo PodsForm::field( '_pods_id', $pod->id(), 'hidden' ); ?>
	<?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
	<?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $fields ) ), 'hidden' ); ?>
	<?php echo PodsForm::field( '_pods_location', $_SERVER[ 'REQUEST_URI' ], 'hidden' ); ?>

	<ul class="pods-form-fields">
		<?php
		foreach ( $fields as $field ) {
			if ( 'hidden' == $field[ 'type' ] )
				continue;

			do_action( 'pods_form_pre_field', $field, $fields, $pod );
			?>
			<li class="pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Podsform::clean( $field[ 'name' ], true ); ?>">
				<div class="pods-field-label">
					<?php echo PodsForm::label( 'pods_field_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?>
				</div>

				<div class="pods-field-input">
					<?php echo PodsForm::field( 'pods_field_' . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), $field[ 'type' ], $field, $pod, $pod->id() ); ?>

					<?php echo PodsForm::comment( 'pods_field_' . $field[ 'name' ], null, $field ); ?>
				</div>
			</li>
		<?php
		}
		?>
	</ul>

	<?php
	foreach ( $fields as $field ) {
		if ( 'hidden' != $field[ 'type' ] )
			continue;

		echo PodsForm::field( 'pods_field_' . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), 'hidden' );
	}
	?>

	<p class="pods-submit">
		<img class="waiting" src="<?php echo admin_url() . '/images/wpspin_light.gif' ?>" alt="">
		<input type="submit" value=" <?php echo esc_attr( $label ); ?> " class="pods-submit-button" />

		<?php do_action( 'pods_form_after_submit', $pod, $fields ); ?>
	</p>
</div>
