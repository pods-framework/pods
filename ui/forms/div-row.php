<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @var Pods                $pod
 * @var mixed               $id
 * @var string              $field_prefix
 * @var \Pods\Whatsit\Field $field
 * @var string              $row_classes
 * @var mixed               $value
 * @var string|null         $heading_tag
 */
?>
<div class="pods-field__container pods-field-option <?php echo esc_attr( $row_classes ); ?>"
	style="<?php echo esc_attr( 'hidden' == $field['type'] ? 'display:none;' : '' ); ?>">
	<?php if ( 'heading' === $field['type'] ) : ?>
		<?php $heading_tag = PodsField_Heading::get_heading_tag( $field, 'h2' ); ?>
		<<?php echo esc_html( sanitize_key( $heading_tag ) ); ?>
			class="pods-form-ui-heading pods-form-ui-heading-<?php echo esc_attr( $field['name'] ); ?>"
			id="heading-<?php echo esc_attr( $field['name'] ); ?>">
			<?php echo esc_html( $field['label'] ); ?>
		</<?php echo esc_html( sanitize_key( $heading_tag ) ); ?>>
		<?php echo PodsForm::comment( $field['name'], pods_v( 'description', $field ), $field ); ?>
	<?php elseif ( 'html' === $field['type'] && 1 === (int) $field['html_no_label'] ) : ?>
		<?php echo PodsForm::field( $field['name'], $value, $field['type'], $field, $pod, $id ); ?>
	<?php else : ?>
		<?php
		echo PodsForm::label( $field['name'], $field['label'], pods_v( 'help', $field ), $field );
		echo PodsForm::field( $field['name'], $value, $field['type'], $field, $pod, $id );
		echo PodsForm::comment( $field['name'], pods_v( 'description', $field ), $field );
		?>
	<?php endif; ?>
</div>
