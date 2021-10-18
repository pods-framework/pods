<?php
/**
 * @var array         $fields
 * @var Pods          $pod
 * @var mixed         $id
 * @var string        $field_prefix
 * @var string        $field_row_classes
 * @var callable|null $value_callback
 * @var callable|null $pre_callback
 * @var callable|null $post_callback
 */

$pod               = isset( $pod ) ? $pod : null;
$id                = isset( $id ) ? $id : 0;
$field_row_classes = isset( $field_row_classes ) ? $field_row_classes : '';
$field_prefix      = isset( $field_prefix ) ? $field_prefix : '';
$value_callback    = isset( $value_callback ) ? $value_callback : null;
$pre_callback      = isset( $pre_callback ) ? $pre_callback : null;
$post_callback     = isset( $post_callback ) ? $post_callback : null;

foreach ( $fields as $field ) {
	$hidden_field = (boolean) pods_v( 'hidden', $field, false ) || 'hidden' === $field['type'];

	if (
		! pods_permission( $field )
		|| ( ! pods_has_permissions( $field ) && $hidden_field )
	) {
		if ( ! $hidden_field ) {
			continue;
		}

		$field['type'] = 'hidden';
	}

	$value = '';

	if ( isset( $field['value_override'] ) && $field['value_override'] !== $value ) {
		$value = $field['value_override'];
	} elseif ( is_callable( $value_callback ) ) {
		$value = $value_callback( $field['name'], $id, $field, $pod );
	} elseif ( ! empty( $pod ) ) {
		$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
	}

	$row_classes = $field_row_classes . ' pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . PodsForm::clean( $field['name'], true );

	/**
	 * Filter the html class used on form field list item element.
	 *
	 * @since 2.7.2
	 *
	 * @param string $html_class The HTML class.
	 * @param array  $field      The current field.
	 */
	$row_classes = apply_filters( 'pods_form_html_class', $row_classes );

	$row_classes = trim( $row_classes );

	if ( ! empty( $pre_callback ) && is_callable( $pre_callback ) ) {
		$pre_callback( $field['name'], $id, $field, $pod );
	}

	pods_view( PODS_DIR . 'ui/forms/list-row.php', compact( array_keys( get_defined_vars() ) ) );

	if ( ! empty( $post_callback ) && is_callable( $post_callback ) ) {
		$post_callback( $field['name'], $id, $field, $pod );
	}
}
