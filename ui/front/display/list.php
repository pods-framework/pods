<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * @var string                $list_type
 * @var string                $tag_name
 * @var string                $sub_tag_name
 * @var \Pods\Whatsit\Field[] $display_fields
 * @var Pods                  $obj
 * @var boolean               $bypass_map_field_values
 */
?>

<<?php echo esc_html( sanitize_key( $tag_name ) ); ?> class="pods-all-fields pods-all-fields-<?php echo esc_attr( $list_type ); ?>">
	<?php foreach ( $display_fields as $field_path => $field ) : ?>
		<?php $field_label = $field->get_label(); ?>
		<<?php echo esc_html( sanitize_key( $sub_tag_name ) ); ?> class="pods-all-fields-row pods-all-fields-row-name-<?php echo esc_attr( PodsForm::clean( $field_path, true ) ); ?>">
			<strong>
				<?php echo esc_html( $field_label ); ?>:
			</strong>

			<?php echo $obj->display( [ 'name' => $field_path, 'bypass_map_field_values' => $bypass_map_field_values ] ); // @codingStandardsIgnoreLine ?>
		</<?php echo esc_html( sanitize_key( $sub_tag_name ) ); ?>>
	<?php endforeach; ?>
</<?php echo esc_html( sanitize_key( $tag_name ) ); ?>>
