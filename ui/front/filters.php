<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * @var Pods $pod
 * @var array<string,\Pods\Whatsit\Field> $fields
 * @var string $action
 * @var string $search
 * @var string $label
 */
?>
<form method="get" class="pods-form-filters pods-form-filters-<?php echo esc_attr( $pod->pod ); ?>" action="<?php echo esc_attr( $action ); ?>">
	<?php
	foreach ( $fields as $name => $field ) {
		if ( in_array( $field['type'], array( 'pick', 'taxonomy' ), true ) && 'pick-custom' !== $field['pick_object'] && ! empty( $field['pick_object'] ) ) {
			$filter_name  = $pod->filter_var . '_' . $name;
			$filter_label = sprintf( __( 'Filter by %s', 'pods' ), $field['label'] );

			$field['pick_format_type']       = 'single';
			$field['pick_format_single']     = 'dropdown';
			$field['pick_show_select_text']  = 0;

			$filter = sanitize_text_field( pods_v( $filter_name, 'get', '' ) );

			// @todo Support other field types.
			$field['type'] = 'pick';

			PodsForm::output_label( $filter_name, $filter_label, '', $field );
			PodsForm::output_field( $filter_name, $filter, $field['type'], $field, $pod->pod, $pod->id() );
		}
	}

	$search_id = 'pods-form-filters-search-' . sanitize_key( $pod->pod );
	?>

	<label for="<?php echo esc_attr( $search_id ); ?>" class="pods-form-filters-label"><?php esc_html_e( 'Search', 'pods' ); ?></label>
	<input type="text" id="<?php echo esc_attr( $search_id ); ?>" class="pods-form-filters-search" name="<?php echo esc_attr( $pod->search_var ); ?>" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'pods' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'pods' ); ?>" />

	<input type="submit" class="pods-form-filters-submit" value="<?php echo esc_attr( $label ); ?>" />
</form>
