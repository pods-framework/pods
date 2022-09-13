<?php
/**
 * @var \Pods\Whatsit\Field[] $fields
 * @var Pods                  $obj
 */
?>

<table class="form-table pods-all-fields pods-all-fields-table">
	<tbody>
	<?php foreach ( $fields as $field_name => $field ) : ?>
		<?php $field_label = $field->get_label(); ?>
		<tr class="pods-all-fields-row pods-all-fields-row-name-<?php echo esc_attr( PodsForm::clean( $field_label, true ) ); ?>">
			<th scope="row">
				<strong>
					<?php echo $field_label; // @codingStandardsIgnoreLine ?>
				</strong>
			</th>
			<td>
				<?php echo $obj->display( $field_name ); // @codingStandardsIgnoreLine ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
