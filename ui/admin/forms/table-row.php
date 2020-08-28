<?php
/**
 * @var Pods                $pod
 * @var mixed               $id
 * @var string              $field_prefix
 * @var string              $th_scope
 * @var \Pods\Whatsit\Field $field
 * @var string              $row_classes
 * @var mixed               $value
 */
?>
<tr valign="top" class="pods-field pods-field-option <?php echo esc_attr( $row_classes ); ?>"
	style="<?php echo esc_attr( 'hidden' == $field['type'] ? 'display:none;' : '' ); ?>">
	<?php if ( 'heading' === $field['type'] ) : ?>
		<td colspan="2">
			<<?php echo esc_html( pods_v( 'heading_tag', $field, 'h2', true ) ); ?>>
				<?php echo esc_html( $field['label'] ); ?>
			</<?php echo esc_html( pods_v( 'heading_tag', $field, 'h2', true ) ); ?>>
			<?php echo PodsForm::comment( $field_prefix . $field['name'], $field['description'], $field ); ?>
		</td>
	<?php elseif ( 'html' === $field['type'] ) : ?>
		<td colspan="2">
			<?php echo PodsForm::field( $field_prefix . $field['name'], $value, $field['type'], $field, $pod, $id ); ?>
		</td>
	<?php else : ?>
		<th<?php if ( ! empty( $th_scope ) ) : ?>
			scope="<?php echo esc_attr( $th_scope ); ?>"
		<?php endif; ?>>
			<?php echo PodsForm::label( $field_prefix . $field['name'], $field['label'], $field['help'], $field ); ?>
		</th>
		<td>
			<div class="pods-submittable-fields">
				<?php
				echo PodsForm::field( $field_prefix . $field['name'], $value, $field['type'], $field, $pod, $id );
				echo PodsForm::comment( $field_prefix . $field['name'], $field['description'], $field );
				?>
			</div>
		</td>
	<?php endif; ?>
</tr>
