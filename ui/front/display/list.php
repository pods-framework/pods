<?php
/**
 * @var string                $list_type
 * @var string                $tag_name
 * @var string                $sub_tag_name
 * @var \Pods\Whatsit\Field[] $display_fields
 * @var Pods                  $obj
 * @var boolean               $bypass_map_field_values
 */
?>

<<?php echo $tag_name; ?> class="pods-all-fields pods-all-fields-<?php echo esc_attr( $list_type ); ?>">
	<?php foreach ( $display_fields as $field_path => $field ) : ?>
		<?php $field_label = $field->get_label(); ?>
		<<?php echo $sub_tag_name; ?> class="pods-all-fields-row pods-all-fields-row-name-<?php echo esc_attr( PodsForm::clean( $field_path, true ) ); ?>">
			<strong>
				<?php echo $field_label; // @codingStandardsIgnoreLine ?>:
			</strong>

			<?php echo $obj->display( [ 'name' => $field_path, 'bypass_map_field_values' => $bypass_map_field_values ] ); // @codingStandardsIgnoreLine ?>
		</<?php echo sanitize_key( $sub_tag_name ); ?>>
	<?php endforeach; ?>
</<?php echo $tag_name; ?>>
