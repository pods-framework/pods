<?php
/**
 * @var Pods                $pod
 * @var mixed               $id
 * @var string              $field_prefix
 * @var \Pods\Whatsit\Field $field
 * @var string              $row_classes
 * @var mixed               $value
 */
?>
<div class="pods-field pods-field-option" style="<?php echo esc_attr( 'hidden' == $field['type'] ? 'display:none;' : '' ); ?>">
	<?php if ( 'heading' === $field['type'] ) : ?>
		<<?php echo esc_html( pods_v( 'heading_tag', $field, 'h2', true ) ); ?>>
			<?php echo esc_html( $field['label'] ); ?>
		</<?php echo esc_html( pods_v( 'heading_tag', $field, 'h2', true ) ); ?>>
		<?php echo PodsForm::comment( $field_prefix . $field['name'], $field['description'], $field ); ?>
	<?php elseif ( 'html' === $field['type'] ) : ?>
		<?php echo PodsForm::field( $field_prefix . $field['name'], $value, $field['type'], $field, $pod, $id ); ?>
	<?php else : ?>
		<p<?php if ( ! empty( $row_classes ) ) : ?>
			class="<?php echo esc_attr( $row_classes ); ?>
					<?php endif; ?>>
					<?php
			echo PodsForm::label( $field_prefix . $field['name'], $field['label'], $field['help'], $field );
			echo PodsForm::field( $field_prefix . $field['name'], $value, $field['type'], $field, $pod, $id );
			echo PodsForm::comment( $field_prefix . $field['name'], $field['description'], $field );
			?>
		</p>
	<?php endif; ?>
</div>
