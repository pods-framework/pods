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
		?>
			<li class="pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Podsform::clean( $field[ 'name' ], true ); ?>">
				<div class="pods-field-label">
					<strong><?php echo $field[ 'label' ]; ?></strong>
				</div>

				<div class="pods-field-input">
					<?php echo $pod->display( $field[ 'name' ] ); ?>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>
