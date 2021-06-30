<?php
/**
 * Admin View: Widget Component Number field
 *
 * Administration Views cannot be overwritten by default from your theme.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 4.13.0
 *
 * @var string $label      Label for the number input.
 * @var string $id         ID of the number input.
 * @var string $name       Name attribute for the number input.
 * @var string $dependency The dependency attributes for the control wrapper.
 * @var string $value      Value for the number input.
 * @var string $min        Value for the min attribute.
 * @var string $max        Value for the max attribute.
 * @var string $step       Value for the step attribute.
 */

?>
<div
	class="tribe-widget-form-control tribe-widget-form-control--text"
	<?php
	// Not escaped - contains html (data-attr="value").
	echo $dependency; // phpcs:ignore
	?>
>
	<label
		class="tribe-common-form-control__label"
		for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
	<input
		class="tribe-common-form-control__input widefat"
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="number"
		min="<?php echo esc_attr( $min ); ?>"
		max="<?php echo esc_attr( $max ); ?>"
		step="<?php echo esc_attr( $step ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
	/>
</div>
