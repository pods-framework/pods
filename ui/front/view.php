<?php
wp_enqueue_style( 'pods-form', false, array(), false, true );

/**
 * @var array $fields
 * @var Pods $pod
 */
?>
<div class="pods-submittable-fields">
	<ul class="pods-form-fields">
		<?php
			foreach ( $fields as $field ) {
				if ( isset( $field[ 'custom_display' ] ) && is_callable( $field[ 'custom_display' ] ) ) {
					$value = call_user_func_array( $field[ 'custom_display' ], array( $pod->row(), $pod, $pod->field( $field[ 'name' ] ), $field[ 'name' ], $field ) );
				}
				else {
					$value = $pod->display( $field[ 'name' ] );
				}
		?>
			<li class="pods-field <?php echo esc_attr( 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $field[ 'name' ], true ) ); ?>">
				<div class="pods-field-label">
					<strong><?php echo $field[ 'label' ]; ?></strong>
				</div>

				<div class="pods-field-input">
					<?php echo $value; ?>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>
