<?php
/**
 * @var \Pods\Whatsit\Field[] $display_fields
 * @var Pods                  $obj
 * @var boolean               $bypass_map_field_values
 */
?>

<table class="form-table pods-all-fields pods-all-fields-table">
	<tbody>
	<?php foreach ( $display_fields as $field_path => $field ) : ?>
		<?php $field_label = $field->get_label(); ?>
		<tr class="pods-all-fields-row pods-all-fields-row-name-<?php echo esc_attr( PodsForm::clean( $field_path, true ) ); ?>">
			<th scope="row">
				<strong>
					<?php echo $field_label; // @codingStandardsIgnoreLine ?>
				</strong>
			</th>
			<td>
				<?php echo $obj->display( [ 'name' => $field_path, 'bypass_map_field_values' => $bypass_map_field_values ] ); // @codingStandardsIgnoreLine ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
