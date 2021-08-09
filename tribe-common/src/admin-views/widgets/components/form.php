<?php
/**
 * Admin View: Widget Component Form
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/form.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \Tribe__Template    $this          Instance of the template including this file.
 * @var array<mixed>        $form_classes  (optional) HTML classes used for the form element
 * @var array<string,mixed> $admin_fields  Fields to be rendered.
 *
 * @version 4.12.18
 */

$default_classes = [
	'tribe-widget-form'
];

$classes = array_merge( $default_classes, $this->get( 'form_classes', [] ) );
?>

<div
	<?php tribe_classes( $classes ); ?>
>
	<?php $this->template( 'widgets/components/fields', [ 'fields' => $admin_fields ] ); ?>
</div>

