<?php
/**
 * Admin View: Widget Component Radio field
 *
 * Administration Views cannot be overwritten by default from your theme.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 4.12.18
 *
 * @var string $label        Label for the radio group.
 * @var string $value        Value for the radio group.
 * @var string $button_value Value for the individual button.
 * @var string $name         Name attribute for the radio.
 * @var string $id           ID attribute for the radio.
 * @var string $dependency   The dependency attributes for the control wrapper.
 */

?>
<div
	class="tribe-widget-form-control tribe-widget-form-control--radio"
	<?php
	// Not escaped - contains html (data-attr="value").
	echo $dependency; // phpcs:ignore
	?>
>
	<input
		class="tribe-widget-form-control__input"
		id="<?php echo esc_attr( $id . '-' . strtolower( $button_value ) ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="radio"
		value="<?php echo esc_attr( $button_value ); ?>"
		<?php checked( $button_value, $value ); ?>
	/>
	<label
		class="tribe-widget-form-control__label"
		for="<?php echo esc_attr( $id . '-' . strtolower( $button_value ) ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
</div>
