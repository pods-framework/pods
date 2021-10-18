<?php
/**
 * Admin View: Widget Component Fieldset field.
 *
 * This component is different in that it calls other components!
 *
 * Administration Views cannot be overwritten by default from your theme.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 4.12.18
 *
 * @var string              $label       Title for the fieldset.
 * @var string              $description Description for the fieldset.
 * @var string              $classes     Classes to add to the fieldset.
 * @var string              $dependency  Dependency attribute for the fieldset.
 * @var array<string,mixed> $children    Child elements for the fieldset.
 */

use Tribe__Utils__Array as Arr;

$fieldset_classes = array_merge( [ 'tribe-widget-form-control', 'tribe-widget-form-control--fieldset' ], Arr::list_to_array( $classes, ' ' ) );

?>
<fieldset
	<?php tribe_classes( $fieldset_classes ); ?>
	<?php
	// Not escaped - contains html (data-attr="value").
	echo $dependency; // phpcs:ignore
	?>
>
	<?php if ( ! empty( $label ) ) : ?>
		<legend class="tribe-widget-form-control__legend"><?php echo esc_html( $label ); ?></legend>
	<?php endif; ?>

	<?php $this->template( "widgets/components/fields", [ 'fields' => $children ] );  ?>
</fieldset>
