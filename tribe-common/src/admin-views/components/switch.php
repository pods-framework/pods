<?php
/**
 * View: Switch
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/components/switch.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 4.13.5
 *
 * @var string               $label         Label for the switch input.
 * @var string               $id            ID of the switch input.
 * @var array<string,string> $classes_wrap  An array of classes for the switch wrap.
 * @var array<string,string> $classes_input An array of classes for the switch input.
 * @var array<string,string> $classes_label An array of classes for the switch label.
 * @var string               $name          Name attribute for the switch input.
 * @var string|int           $value         The value of the switch.
 * @var string|int           $checked       Whether the switch is enabled or not.
 * @var array<string,string> $attrs         Associative array of attributes of the switch.
 */
$switch_wrap_classes = [ 'tribe-common-control', 'tribe-common-control--switch' ];
if ( ! empty( $classes_wrap ) ) {
	$switch_wrap_classes = array_merge( $switch_wrap_classes, $classes_wrap );
}

$switch_input_classes = [ 'tribe-common-switch__input' ];
if ( ! empty( $classes_input ) ) {
	$switch_input_classes = array_merge( $switch_input_classes, $classes_input );
}

$switch_label_classes = [ 'tribe-common-switch__label' ];
if ( ! empty( $classes_label ) ) {
	$switch_label_classes = array_merge( $switch_label_classes, $classes_label );
}
?>
<div
	<?php tribe_classes( $switch_wrap_classes ); ?>
>
	<input
		<?php tribe_classes( $switch_input_classes ); ?>
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="checkbox"
		value="<?php echo esc_attr( $value ); ?>"
		<?php checked( true, tribe_is_truthy( $checked ) ); ?>
		<?php tribe_attributes( $attrs ) ?>
	/>

	<label <?php tribe_classes( $switch_label_classes ); ?> for="<?php echo esc_attr( $id ); ?>">
		<span class="screen-reader-text">
			<?php echo esc_html( $label ); ?>
		</span>
	</label>
</div>
