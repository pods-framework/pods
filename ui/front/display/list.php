<?php
/**
 * @var string                $list_type
 * @var string                $tag_name
 * @var string                $sub_tag_name
 * @var \Pods\Whatsit\Field[] $fields
 * @var Pods                  $obj
 */
?>

<<?php echo $tag_name; ?>
	class="pods-all-fields pods-all-fields-<?php echo esc_attr( $list_type ); ?>">
	<?php foreach ( $fields as $field_name => $field ) : ?>
		<?php $field_label = $field->get_label(); ?>
		<<?php echo $sub_tag_name; ?>
			class="pods-all-fields-row pods-all-fields-row-name-<?php echo esc_attr( PodsForm::clean( $field_label, true ) ); ?>">
			<strong>
				<?php echo $field_label; // @codingStandardsIgnoreLine ?>:
			</strong>

			<?php echo $obj->display( $field_name ); // @codingStandardsIgnoreLine ?>
		</<?php echo sanitize_key( $sub_tag_name ); ?>>
	<?php endforeach; ?>
</<?php echo $tag_name; ?>>
