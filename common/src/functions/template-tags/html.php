<?php
/**
 * HTML functions (template-tags) for use in WordPress templates.
 */
use Tribe\Utils\Element_Classes;

/**
 * Parse input values into a valid array of classes to be used in the templates.
 *
 * @since  4.9.13
 *
 * @param  mixed $classes,... unlimited Any amount of params to be rendered as classes.
 *
 * @return array
 */
function tribe_get_classes() {
	$element_classes = new Element_Classes( func_get_args() );
	return $element_classes->get_classes();
}

/**
 * Parses input values into a valid class html attribute to be used in the templates.
 *
 * @since  4.9.13
 *
 * @param  mixed $classes,... unlimited Any amount of params to be rendered as classes.
 *
 * @return string
 */
function tribe_classes() {
	$element_classes = new Element_Classes( func_get_args() );
	echo $element_classes->get_attribute();
}
