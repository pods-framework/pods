<?php
/**
 * Admin View: Widget Component Text field
 *
 * Administration Views cannot be overwritten by default from your theme.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 4.12.18
 *
 * @var string $label      Label for the text input.
 * @var string $value      Value for the text input.
 * @var string $id         ID of the text input.
 * @var string $name       Name attribute for the text input.
 * @var string $dependency The dependency attributes for the control wrapper.
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
		type="text"
		value="<?php echo esc_attr( $value ); ?>"
	/>
</div>
