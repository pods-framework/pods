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
 * @var string        $th_scope
 */

$pod               = isset( $pod ) ? $pod : null;
$id                = isset( $id ) ? $id : 0;
$field_row_classes = isset( $field_row_classes ) ? $field_row_classes : '';
$field_prefix      = isset( $field_prefix ) ? $field_prefix : '';
$value_callback    = isset( $value_callback ) ? $value_callback : null;
$pre_callback      = isset( $pre_callback ) ? $pre_callback : null;
$post_callback     = isset( $post_callback ) ? $post_callback : null;
$th_scope          = isset( $th_scope ) ? $th_scope : '';

foreach ( $fields as $field ) {
	$hidden_field = 'hidden' === $field['type'] || filter_var( pods_v( 'hidden', $field, false ), FILTER_VALIDATE_BOOLEAN );

	if (
		! pods_permission( $field )
		|| ( ! pods_has_permissions( $field ) && $hidden_field )
	) {
		if ( ! $hidden_field ) {
			continue;
		}

		if ( $field instanceof \Pods\Whatsit\Field ) {
			$field = clone $field;
		}

		$field['type'] = 'hidden';
	}

	$value = '';

	if ( isset( $field['value_override'] ) && $field['value_override'] !== $value ) {
		$value = $field['value_override'];
	} elseif ( ! empty( $value_callback ) && is_callable( $value_callback ) ) {
		$value = $value_callback( $field['name'], $id, $field, $pod );
	} elseif ( ! empty( $pod ) ) {
		$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
	}

	$row_classes = $field_row_classes . ' pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . PodsForm::clean( $field['name'], true );
	$row_classes = trim( $row_classes );

	if ( ! empty( $pre_callback ) && is_callable( $pre_callback ) ) {
		$pre_callback( $field['name'], $id, $field, $pod );
	}

	pods_view( PODS_DIR . 'ui/forms/table-row.php', compact( array_keys( get_defined_vars() ) ) );

	if ( ! empty( $post_callback ) && is_callable( $post_callback ) ) {
		$post_callback( $field['name'], $id, $field, $pod );
	}
}
