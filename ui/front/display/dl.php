<?php
/**
 * @var string                $list_type
 * @var \Pods\Whatsit\Field[] $display_fields
 * @var Pods                  $obj
 */
?>

<dl
	class="pods-all-fields pods-all-fields-<?php echo esc_attr( $list_type ); ?>">
	<?php foreach ( $display_fields as $field_name => $field ) : ?>
		<?php $field_label = $field->get_label(); ?>
		<dt
			class="pods-all-fields-row-label pods-all-fields-row-label-<?php echo esc_attr( PodsForm::clean( $field_label, true ) ); ?>">
			<strong>
				<?php echo $field_label; // @codingStandardsIgnoreLine ?>
			</strong>
		</dt>
		<dd
			class="pods-all-fields-row-value pods-all-fields-row-value-<?php echo esc_attr( PodsForm::clean( $field_label, true ) ); ?>">
			<?php echo $obj->display( $field_name ); // @codingStandardsIgnoreLine ?>
		</dd>
	<?php endforeach; ?>
</dl>
