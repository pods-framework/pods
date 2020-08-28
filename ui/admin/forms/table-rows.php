<?php
/**
 * @var array         $fields
 * @var Pods          $pod
 * @var mixed         $id
 * @var string        $field_prefix
 * @var string        $field_row_classes
 * @var string        $th_scope
 * @var callable|null $value_callback
 * @var callable|null $pre_callback
 * @var callable|null $post_callback
 */

$depends_on = false;

foreach ( $fields as $field ) {
	$hidden_field = (boolean) pods_v( 'hidden', $field['options'], false );

	if (
		! PodsForm::permission( $field['type'], $field['name'], $field, $fields, $pod, $id )
		|| ( ! pods_has_permissions( $field['options'] ) && $hidden_field )
	) {
		if ( ! $hidden_field ) {
			continue;
		}

		$field['type'] = 'hidden';
	}

	$value = '';

	if ( ! empty( $value_callback ) && is_callable( $value_callback ) ) {
		$value = $value_callback( $field['name'], $id, $field, $pod );
	} elseif ( ! empty( $pod ) ) {
		$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
	}

	$dep_options = PodsForm::dependencies( $field );
	$dep_classes = $dep_options['classes'];
	$dep_data    = $dep_options['data'];

	if ( ( ! empty( $depends_on ) || ! empty( $dep_classes ) ) && $depends_on !== $dep_classes ) {
		if ( ! empty( $depends_on ) ) {
			?>
			</tbody>
			<?php
		}

		if ( ! empty( $dep_classes ) ) {
			?>
			<tbody class="pods-field-option-container <?php echo esc_attr( $dep_classes ); ?>" <?php PodsForm::data( $dep_data ); ?>>
			<?php
		}
	}

	$row_classes = $field_row_classes . ' pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . PodsForm::clean( $field['name'], true );
	$row_classes = trim( $row_classes );

	if ( ! empty( $pre_callback ) && is_callable( $pre_callback ) ) {
		$pre_callback( $field['name'], $id, $field, $pod );
	}

	pods_view( PODS_DIR . 'ui/admin/forms/table-row.php', compact( array_keys( get_defined_vars() ) ) );

	if ( ! empty( $post_callback ) && is_callable( $post_callback ) ) {
		$post_callback( $field['name'], $id, $field, $pod );
	}

	if ( false !== $depends_on || ! empty( $dep_classes ) ) {
		$depends_on = $dep_classes;
	}
}

if ( ! empty( $depends_on ) ) {
	?>
	</tbody>
	<?php
}