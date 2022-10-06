<?php
/**
 * @var string                $list_type
 * @var \Pods\Whatsit\Field[] $display_fields
 * @var Pods                  $obj
 * @var boolean               $bypass_map_field_values
 */
?>

<dl class="pods-all-fields pods-all-fields-<?php echo esc_attr( $list_type ); ?>">
	<?php foreach ( $display_fields as $field_path => $field ) : ?>
		<?php $field_label = $field->get_label(); ?>
		<dt class="pods-all-fields-row-label pods-all-fields-row-label-<?php echo esc_attr( PodsForm::clean( $field_path, true ) ); ?>">
			<strong>
				<?php echo $field_label; // @codingStandardsIgnoreLine ?>
			</strong>
		</dt>
		<dd class="pods-all-fields-row-value pods-all-fields-row-value-<?php echo esc_attr( PodsForm::clean( $field_path, true ) ); ?>">
			<?php echo $obj->display( [ 'name' => $field_path, 'bypass_map_field_values' => $bypass_map_field_values ] ); // @codingStandardsIgnoreLine ?>
		</dd>
	<?php endforeach; ?>
</dl>
