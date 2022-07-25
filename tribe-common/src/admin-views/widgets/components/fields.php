<?php
/**
 * Admin View: Widget Component Fields Container
 *
 * Administration Views cannot be overwritten by default from your theme.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \Tribe__Template     $this                      Instance of the template including this file.
 * @var array<mixed>         $fields_container_classes  (optional) HTML classes used for the form element
 * @var Widget_Abstract      $widget_obj                An instance of the widget abstract.
 * @var array<array,mixed>   $fields                    An array of admin fields to display in the widget form.
 *
 * @version 4.12.18
 */

use  Tribe\Widget\Widget_Abstract;

$default_classes = [
	'tribe-widget-fields'
];

$classes = array_merge( $default_classes, $this->get( 'fields_container_classes', [] ) );
?>

<div
	<?php tribe_classes( $classes ); ?>
>
	<?php
	foreach ( $fields as $field ) {
		// Try to load the component for this field type.
		$this->template( "widgets/components/fields/{$field['type']}", $field );

		// Sets the current field for possible usage inside of the entry point.
		$this->set_values( [ 'field' => $field ] );

		// Allow fields that were not registered as components to have something loaded.
		$this->do_entry_point( "field-{$field['type']}" );
	}
	?>
</div>
