<?php
/**
 * Admin View: Widget Component Checkbox field
 *
 * Administration Views cannot be overwritten by default from your theme.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 4.12.18
 *
 * @var string $label      Label for the checkbox.
 * @var string $value      Value for the checkbox.
 * @var string $id         ID of the checkbox.
 * @var string $name       Name attribute for the checkbox.
 * @var string $dependency The dependency attributes for the control wrapper.
 */

?>
<div
	class="tribe-widget-form-control tribe-widget-form-control--checkbox"
	<?php
	// Not escaped - contains html (data-attr="value").
	echo $dependency; // phpcs:ignore
	?>
>
	<input
		class="tribe-widget-form-control__input"
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="checkbox"
		value="1"
		<?php checked( tribe_is_truthy( $value ), true ); ?>
	/>
	<label
		class="tribe-widget-form-control__label"
		for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
</div>
