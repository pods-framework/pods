<?php

namespace Tribe\Widget;

/**
 * Interface Widget_Interface
 *
 * @since   4.12.12
 *
 * @package Tribe\Widget
 *
 */
interface Widget_Interface {
	/**
	 * Constructor for V2 Widgets.
	 *
	 * @since 4.12.12
	 *
	 * @param string              $id_base         Optional. Base ID for the widget, lowercase. If left empty,
	 *                                             a portion of the widget's class name will be used. Has to be unique.
	 * @param string              $name            Name for the widget displayed on the configuration page.
	 * @param array<string,mixed> $widget_options  Optional. Widget options. See wp_register_sidebar_widget() for
	 *                                             information on accepted arguments. Default empty array.
	 * @param array<string,mixed> $control_options Optional. Widget control options. See wp_register_widget_control() for
	 *                                             information on accepted arguments. Default empty array.
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = [], $control_options = [] );

	/**
	 * Returns the widget slug that allows the widget to be built via the widget class using that slug.
	 *
	 * @since 4.13.0
	 *
	 * @return string The widget slug.
	 */
	public static function get_widget_slug();

	/**
	 * Returns if the widget is in use on this current page.
	 *
	 * @since 4.13.0
	 *
	 * @return bool If this widget is in use.
	 */
	public static function is_widget_in_use();

	/**
	 * Sets if this widget is in use on the current page.
	 *
	 * @since 4.13.0
	 *
	 * @see tribe_is_truthy()
	 *
	 * @param bool $toggle Toggle true or false if a widget is in use.
	 */
	public static function widget_in_use( $toggle = true );

	/**
	 * Echoes the widget content.
	 *
	 * @since 4.12.12
	 *
	 * @param array<string,mixed> $args     Display arguments including 'before_title', 'after_title',
	 *                                      'before_widget', and 'after_widget'.
	 * @param array<string,mixed> $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance );

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @since 4.12.12
	 *
	 * @param array<string,mixed> $new_instance New settings for this instance as input by the user via
	 *                                          WP_Widget::form().
	 * @param array<string,mixed> $old_instance Old settings for this instance.
	 *
	 * @return array<string,mixed> Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance );

	/**
	 * Outputs the settings update form.
	 *
	 * @since 4.12.12
	 *
	 * @param array<string,mixed> $instance Current settings.
	 *
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance );

	/**
	 * Sets the aliased arguments array.
	 *
	 * @see Tribe__Utils__Array::parse_associative_array_alias() The expected format.
	 *
	 * @since 4.12.12
	 *
	 * @param array<string,mixed> $alias_map An associative array of aliases: key as alias, value as mapped canonical.
	 *                         Example: [ 'alias' => 'canonical', 'from' => 'to', 'that' => 'becomes_this' ]
	 */
	public function set_aliased_arguments( array $alias_map );

	/**
	 * Gets the aliased arguments array.
	 *
	 * @since 4.12.12
	 *
	 * @return array<string,string> The associative array map of aliases and their canonical arguments.
	 */
	public function get_aliased_arguments();

	/**
	 * Returns the arguments for the widget parsed correctly with defaults applied.
	 *
	 * @since 4.12.12
	 *
	 * @param array $arguments Set of arguments passed to the widget at hand.
	 *
	 * @return array<string,mixed> The parsed widget arguments map.
	 */
	public function parse_arguments( array $arguments );

	/**
	 * Returns the array of arguments for this widget after applying the validation callbacks.
	 *
	 * @since 4.12.12
	 *
	 * @param array $arguments Set of arguments passed to the widget at hand.
	 *
	 * @return array<string,mixed> The validated widget arguments map.
	 */
	public function validate_arguments( array $arguments );

	/**
	 * Returns the array of callbacks for this widget's arguments.
	 *
	 * @since 4.12.12
	 *
	 * @return array<string,callable> A map of the widget arguments that have survived validation.
	 */
	public function get_validated_arguments_map();

	/**
	 * Returns the array of callbacks for this widget's arguments.
	 *
	 * @since 4.12.12
	 *
	 * @param array<string,callable> $validate_arguments_map Array of callbacks for this widget's arguments.
	 *
	 * @return array<string,callable> A map of the widget arguments that have survived validation.
	 */
	public function filter_validated_arguments_map( $validate_arguments_map = [] );

	/**
	 * Returns an array of admin fields for the widget.
	 *
	 * @since 4.12.14
	 *
	 * @return array<string,mixed> The array of widget admin fields.
	 */
	public function get_admin_fields();

	/**
	 * Filter a widget's admin fields.
	 *
	 * @since 4.12.14
	 *
	 * @param array<string,mixed> $admin_fields The array of widget admin fields.
	 *
	 * @return array<string,mixed> The array of widget admin fields.
	 */
	public function filter_admin_fields( $admin_fields );

	/**
	 * Filters a widget's updated instance.
	 *
	 * @since 4.12.14
	 *
	 * @param array<string,mixed> $updated_instance The updated instance of the widget.
	 * @param array<string,mixed> $new_instance The new values for the widget instance.
	 *
	 * @return array<string,mixed> The updated instance to be saved for the widget.
	 */
	public function filter_updated_instance( $updated_instance, $new_instance );

	/**
	 * Returns a widget arguments after been parsed.
	 *
	 * @since 4.12.12
	 * @since 4.13.0 Deprecated the instance method as that is passed only to setup_arguments method.
	 *
	 * @param array<string,mixed> $_deprecated Saved values for the widget instance.
	 *
	 * @return array<string,mixed> The widget arguments, as set by the user in the widget string.
	 */
	public function get_arguments( array $_deprecated = [] );

	/**
	 * Filter a widget's arguments after they have been been parsed.
	 *
	 * @since 4.12.12
	 *
	 * @param array<string,mixed> $arguments Current set of arguments.
	 *
	 * @return array<string,mixed> The widget arguments, as set by the user in the widget string.
	 */
	public function filter_arguments( $arguments );

	/**
	 * Get a single widget argument after it has been parsed and filtered.
	 *
	 * @since 4.12.12
	 *
	 * @param string|int   $index   Which index we intend to fetch from the arguments.
	 * @param array|mixed  $default Default value if it doesn't exist.
	 *
	 * @uses  Tribe__Utils__Array::get For index fetching and Default.
	 *
	 * @return mixed Value for the Index passed as the first argument.
	 */
	public function get_argument( $index, $default = null );

	/**
	 * Filter a widget argument.
	 *
	 * @since 4.12.12
	 *
	 * @param mixed       $argument The argument value.
	 * @param string|int  $index    Which index we intend to fetch from the arguments.
	 * @param array|mixed $default  Default value if it doesn't exist.
	 *
	 * @uses  Tribe__Utils__Array::get For index fetching and Default.
	 *
	 * @return mixed Value for the Index passed as the first argument.
	 */
	public function filter_argument( $argument, $index, $default = null );

	/**
	 * Get default arguments for a widget.
	 *
	 * @since 4.12.12
	 *
	 * @return array<string,mixed> The map of widget default arguments.
	 */
	public function get_default_arguments();

	/**
	 * Filter a widget's default arguments.
	 *
	 * @since 4.12.12
	 *
	 * @param array<string,mixed> $default_arguments Current set of default arguments.
	 *
	 * @return array<string,mixed> The map of widget default arguments.
	 */
	public function filter_default_arguments( array $default_arguments = [] );


	/**
	 * Filter a widget's arguments before they are passed to the context.
	 *
	 * @since 4.13.0
	 *
	 * @param array<string,mixed>  $alterations Current set of alterations for the context.
	 * @param array<string,mixed>  $arguments   Current set of arguments in the widget.
	 *
	 * @return array<string,mixed> The map of arguments after filtering.
	 */
	public function filter_args_to_context( array $alterations = [], array $arguments = [] );

	/**
	 * Returns a widget's HTML.
	 *
	 * @since 4.12.12
	 *
	 * @return string The rendered widget's HTML code.
	 */
	public function get_html();

	/**
	 * Sets the sidebar arguments sent by the theme.
	 *
	 * @since 4.13.0
	 *
	 * @param array<string,mixed> $arguments Arguments passed by the theme.
	 */
	public function setup_sidebar_arguments( $arguments );

	/**
	 * Sets the sidebar arguments sent by the theme.
	 *
	 * @since 4.13.0
	 *
	 * @return array<string, mixed> Arguments sent by the theme and stored in this class.
	 */
	public function get_sidebar_arguments();

	/**
	 * Sets the sidebar arguments sent by the theme.
	 *
	 * @since 4.13.0
	 *
	 * @param array<string,mixed> $arguments Arguments passed by the theme.
	 *
	 * @return array<string, mixed> Arguments sent by the theme and stored in this class.
	 */
	public function filter_sidebar_arguments( $arguments );

	/**********************
	 * Deprecated Methods *
	 **********************/

	/**
	 * Returns the widget slug that allows the widget to be built via the widget class using that slug.
	 *
	 * @since 4.12.12
	 *
	 * @deprecated 4.13.0 In favor of static::get_widget_slug()
	 * @todo remove after 2021-08-01
	 *
	 * @return string The widget slug.
	 */
	public function get_registration_slug();

}
