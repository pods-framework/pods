<?php
/**
 * Dialog Button Template
 * The button template for Tribe Dialog trigger.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe/dialogs/button.php
 *
 * @since 4.10.0
 * @since 4.12.15 Add data attributes to the button.
 * @since 4.12.15 Don't render template if `$button_display` is set to false.
 * @since 4.12.17 Allow having basic HTMl within the button content so we can add elements with texts for a11y.
 *
 * @package Tribe
 * @version 4.12.17
 */

if ( empty( $button_display ) ) {
	return;
}

$classes    = $button_classes ?: 'tribe-button';
$classes    = implode( ' ', (array) $classes );
$attributes = $button_attributes ?: [];

?>
<button
	class="<?php echo esc_attr( $classes ); ?>"
	<?php tribe_attributes( $attributes ); ?>
	data-content="<?php echo esc_attr( 'dialog-content-' . $id ); ?>"
	data-js="<?php echo esc_attr( 'trigger-dialog-' . $id ); ?>"
	<?php if ( ! empty( $button_id ) ) : ?>
		id="<?php echo esc_attr( $button_id ); ?>"
	<?php endif; ?>
	<?php if ( ! empty( $button_name ) ) : ?>
		name="<?php echo esc_attr( $button_name ); ?>"
	<?php endif; ?>
	<?php if ( ! empty( $button_type ) ) : ?>
		type="<?php echo esc_attr( $button_type ); ?>"
	<?php endif; ?>
	<?php if ( ! empty( $button_value ) && 0 !== absint( $button_value ) ) : ?>
		value="<?php echo esc_attr( $button_value ); ?>"
	<?php endif; ?>
	<?php if ( ! empty( $button_disabled ) && tribe_is_truthy( $button_disabled ) ) : ?>
		<?php tribe_disabled( true ); ?>
	<?php endif; ?>
><?php echo wp_kses_post( $button_text ); ?></button>
