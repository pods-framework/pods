<?php
wp_enqueue_style( 'pods-form', false, array(), false, true );

/**
 * @var array $fields
 * @var Pods $pod
 */
// unset fields
foreach ( $fields as $k => $field ) {
	if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) ) {
		unset( $fields[ $k ] );
	}
	elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $pod->id() ) ) {
		if ( pods_v_sanitized( 'hidden', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		}
		elseif ( pods_v_sanitized( 'read_only', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		}
		else {
			unset( $fields[ $k ] );
		}
	}
	elseif ( !pods_has_permissions( $field[ 'options' ] ) ) {
		if ( pods_v_sanitized( 'hidden', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		}
		elseif ( pods_v_sanitized( 'read_only', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		}
	}
}
?>
<div class="pods-submittable-fields">
	<ul class="pods-form-fields">
		<?php
		foreach ( $fields as $field ) {
			if ( 'hidden' == $field[ 'type' ] )
				continue;

			//do_action( 'pods_form_pre_field', $field, $fields, $pod );
			?>
			<li class="pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Podsform::clean( $field[ 'name' ], true ); ?>">
				<strong><?php echo $field[ 'label' ]; ?></strong>
				<?php echo $pod->display( $field[ 'name' ] ); ?>
			</li>
		<?php } ?>
	</ul>
</div>
