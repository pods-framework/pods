<?php

namespace Tribe\Widget;

use Tribe__Utils__Array as Arr;
use Tribe__Template;

/**
 * The abstract base without Views that all widgets should implement.
 *
 * @since   4.12.12
 *
 * @package Tribe\Widget
 */
abstract class Widget_Abstract extends \WP_Widget implements Widget_Interface {
	/**
	 * Prefix for WordPress registration of the widget.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const PREFIX = 'tribe-widget-';

	/**
	 * Slug of the current widget.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	protected static $widget_slug;

	/**
	 * If this Widget was rendered on the screen, often useful for Assets.
	 *
	 * Every widget needs this to be defined internally otherwise it wont work.
	 *
	 * @since 4.13.0
	 *
	 * @var boolean
	 */
	protected static $widget_in_use;

	/**
	 * An instance of template.
	 *
	 * @since 4.12.14
	 *
	 * @var Tribe__Template
	 */
	protected $admin_template;

	/**
	 * Default arguments to be merged into final arguments of the widget.
	 *
	 * @since 4.12.12
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [];

	/**
	 * Sidebar arguments passed to the widget.
	 *
	 * @since 4.13.0
	 *
	 * @var array<string,mixed>
	 */
	protected $sidebar_arguments = [];

	/**
	 * Array map allowing aliased widget arguments.
	 *
	 * The array keys are aliases of the array values (i.e. the "real" widget attributes to parse).
	 * Example array: [ 'alias' => 'canonical', 'from' => 'to', 'that' => 'becomes_this' ]
	 * Example widget usage: [some_tag alias=17 to='Fred'] will be parsed as [some_tag canonical=17 to='Fred']
	 *
	 * @since 4.12.12
	 *
	 * @var array<string,string>
	 */
	protected $aliased_arguments = [];

	/**
	 * Array of callbacks for validation of arguments.
	 *
	 * @since 4.12.12
	 *
	 * @var array<string,callable>
	 */
	protected $validate_arguments_map = [];

	/**
	 * Arguments of the current widget.
	 *
	 * @since 4.12.12
	 *
	 * @var array<string,mixed>
	 */
	protected $arguments = [];

	/**
	 * Current set of Admin Fields used on the admin form.
	 *
	 * @since 4.13.0
	 *
	 * @var array<string,mixed>
	 */
	protected $admin_fields = [];

	/**
	 * HTML content of the current widget.
	 *
	 * @since 4.12.12
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * {@inheritDoc}
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = [], $control_options = [] ) {
		/**
		 * For backwards compatibility purposes alone.
		 * @todo remove after 2021-08-01
		 */
		$this->slug = static::get_widget_slug();

		parent::__construct(
			$this->parse_id_base( $id_base ),
			$this->parse_name( $name ),
			$this->parse_widget_options( $widget_options ),
			$this->parse_control_options( $control_options )
		);
	}

	/**
	 * Parse the ID base sent to the __construct method.
	 *
	 * @since 4.13.0
	 *
	 * @param string $id_base The ID base that we will use for this Widget instance.
	 *
	 * @return string|null    Parsed value given by the __construct.
	 */
	protected function parse_id_base( $id_base = null ) {
		// When empty use the one default to the widget.
		if ( empty( $id_base ) ) {
			$id_base = static::PREFIX . static::get_widget_slug();
		}

		return $id_base;
	}

	/**
	 * Parse the ID base sent to the __construct method.
	 *
	 * @since 4.13.0
	 *
	 * @param string $name The ID base that we will use for this Widget instance.
	 *
	 * @return string      Parsed value given by the __construct.
	 */
	protected function parse_name( $name = null ) {
		// When empty use the one default to the widget.
		if ( empty( $name ) ) {
			$name = static::get_default_widget_name();
		}

		return $name;
	}

	/**
	 * Sets up the Widget name,
	 *
	 * @since 4.13.0
	 *
	 * @return string Returns the default widget name.
	 */
	public static function get_default_widget_name() {
		return __( 'Widget', 'tribe-common' );
	}

	/**
	 * Parse the widget options base sent to the __construct method.
	 *
	 * @since 4.13.0
	 *
	 * @param array $widget_options The widget options base that we will use for this Widget instance.
	 *
	 * @return array                Widget options that will be passed to the __construct.
	 */
	protected function parse_widget_options( $widget_options = [] ) {
		// When empty use the one default to the widget.
		if ( empty( $widget_options ) ) {
			$widget_options = static::get_default_widget_options();
		}

		return $widget_options;
	}

	/**
	 * Gets the default widget options.
	 *
	 * @since 4.13.0
	 *
	 * @return array Default widget options.
	 */
	public static function get_default_widget_options() {
		return [];
	}

	/**
	 * Parse the control options base sent to the __construct method.
	 *
	 * @since 4.13.0
	 *
	 * @param array $control_options The base control options passed to the construct method.
	 *
	 * @return array Parsed value given by the __construct.
	 */
	protected function parse_control_options( $control_options = [] ) {
		// When empty use the one default to the widget.
		if ( empty( $control_options ) ) {
			$control_options = static::get_default_control_options();
		}

		return $control_options;
	}

	/**
	 * Gets the default control options.
	 *
	 * @since 4.13.0
	 *
	 * @return array Default control options.
	 */
	public static function get_default_control_options() {
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_widget_slug() {
		return static::$widget_slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function is_widget_in_use() {
		return static::$widget_in_use;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function widget_in_use( $toggle = true ) {
		static::$widget_in_use = tribe_is_truthy( $toggle );
	}

	/**
	 * Setup the widget.
	 *
	 * @since  5.2.1
	 * @since 4.13.0 include $args and $instance params.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 *
	 * @return mixed
	 */
	abstract public function setup( $args = [], $instance = [] );

	/**
	 * {@inheritDoc}
	 */
	public function form( $instance ) {
		$this->setup( [], $instance );

		// Specifically on the admin we force the admin fields into the arguments.
		$this->arguments['admin_fields'] = $this->get_admin_fields();

		$this->toggle_hooks( true, 'form' );

		$html = $this->get_admin_html( $this->get_arguments() );

		$this->toggle_hooks( false, 'form' );
		return $html;
	}

	/**
	 * {@inheritDoc}
	 */
	public function widget( $args, $instance ) {
		// Once the widget is rendered we trigger that it is in use.
		static::widget_in_use( true );

		$this->setup( $args, $instance );

		$this->toggle_hooks( true, 'display' );

		$html = $this->get_html();

		$this->toggle_hooks( false, 'display' );

		echo $html;

		return $html;
	}

	/**
	 * Returns the rendered View HTML code.
	 *
	 * @since 4.12.12
	 *
	 * @return string
	 */
	abstract public function get_html();

	/**
	 * {@inheritDoc}
	 */
	public function set_aliased_arguments( array $alias_map ) {
		$this->aliased_arguments = Arr::filter_to_flat_scalar_associative_array( (array) $alias_map );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_aliased_arguments() {
		return $this->aliased_arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function parse_arguments( array $arguments ) {
		$arguments = Arr::parse_associative_array_alias( (array) $arguments, (array) $this->get_aliased_arguments() );

		return $this->validate_arguments( $arguments );
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate_arguments( array $arguments ) {
		$validate_arguments_map = $this->filter_validated_arguments_map( $this->get_validated_arguments_map() );

		// Only overwrite methods that have a validation, the rest stay as-is.
		foreach ( $validate_arguments_map as $key => $callback ) {
			$arguments[ $key ] = $callback( Arr::get( $arguments, $key, null ) );
		}

		return $arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_validated_arguments_map() {
		return $this->validate_arguments_map;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_validated_arguments_map( $validate_arguments_map = [] ) {
		/**
		 * Applies a filter to the validation map for instance arguments.
		 *
		 * @since 4.12.12
		 *
		 * @param array<string,callable> $validate_arguments_map Current set of callbacks for arguments.
		 * @param static                 $instance               The widget instance we are dealing with.
		 */
		$validate_arguments_map = apply_filters( 'tribe_widget_validate_arguments_map', $validate_arguments_map, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to the validation map for instance arguments for a specific widget. Based on the widget slug of the widget
		 *
		 * @since 4.12.12
		 *
		 * @param array<string,callable> $validate_arguments_map Current set of callbacks for arguments.
		 * @param static                 $instance               The widget instance we are dealing with.
		 */
		$validate_arguments_map = apply_filters( "tribe_widget_{$widget_slug}_validate_arguments_map", $validate_arguments_map, $this );

		return $validate_arguments_map;
	}

	/**
	 * Sets up the widgets default admin fields.
	 *
	 * @since 4.12.14
	 *
	 * @return array<string,mixed> The array of widget admin fields.
	 */
	abstract protected function setup_admin_fields();

	/**
	 * {@inheritDoc}
	 */
	public function get_admin_fields() {
		$fields    = $this->setup_admin_fields();
		$arguments = $this->get_arguments();
		$fields    = $this->filter_admin_fields( $fields );

		foreach ( $fields as $field_name => $field ) {
			$fields[ $field_name ] = $this->get_admin_data( $arguments, $field_name, $field );
		}

		return $fields;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_admin_fields( $admin_fields ) {
		/**
		 * Applies a filter to a widget's admin fields.
		 *
		 * @since 4.12.14
		 *
		 * @param array<string,mixed> $admin_fields The array of widget admin fields.
		 * @param static              $instance     The widget instance we are dealing with.
		 */
		$admin_fields = apply_filters( 'tribe_widget_admin_fields', $admin_fields, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to a widget's admin fields based on the widget slug of the widget.
		 *
		 * @since TBE
		 *
		 * @param array<string,mixed> $admin_fields The array of widget admin fields.
		 * @param static              $instance     The widget instance we are dealing with.
		 */
		$admin_fields = apply_filters( "tribe_widget_{$widget_slug}_admin_fields", $admin_fields, $this );

		return $admin_fields;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_updated_instance( $updated_instance, $new_instance ) {
		/**
		 * Applies a filter to updated instance of a widget.
		 *
		 * @since 4.12.14
		 *
		 * @param array<string,mixed> $updated_instance The updated instance of the widget.
		 * @param array<string,mixed> $new_instance The new values for the widget instance.
		 * @param static              $instance  The widget instance we are dealing with.
		 */
		$updated_instance = apply_filters( 'tribe_widget_updated_instance', $updated_instance, $new_instance, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to updated instance of a widget arguments based on the widget slug of the widget.
		 *
		 * @since 4.12.14
		 *
		 * @param array<string,mixed> $updated_instance The updated instance of the widget.
		 * @param array<string,mixed> $new_instance The new values for the widget instance.
		 * @param static              $instance  The widget instance we are dealing with.
		 */
		$updated_instance = apply_filters( "tribe_widget_{$widget_slug}_updated_instance", $updated_instance, $new_instance, $this );

		return $updated_instance;
	}

	/**
	 * Sets up the widgets arguments, using saved values.
	 *
	 * @since 4.12.14
	 *
	 * @param array<string,mixed> $instance Saved values for the widget instance.
	 *
	 * @return array<string,mixed> The widget arguments, as set by the user in the widget string.
	 */
	protected function setup_arguments( array $instance = [] ) {
		// First Setup the Defaults to make sure dynamic values are present.
		$this->setup_default_arguments();

		// Now merge instance into the arguments then to the defaults.
		$this->arguments = array_merge(
			$this->get_default_arguments(),
			$this->arguments,
			$instance
		);

		// Parse these arguments to avoid problems.
		$this->arguments = $this->parse_arguments( $this->arguments );

		return $this->arguments;
	}

	/**
	 * Handles gathering the data for admin fields.
	 *
	 * @since 5.3.0
	 * @since 4.13.0 Move into common from Events Abstract
	 *
	 * @param array<string,mixed> $arguments   Current set of arguments.
	 * @param int                 $field_name  The ID of the field.
	 * @param array<string,mixed> $field       The field info.
	 *
	 * @return array<string,mixed> $data The assembled field data.
	 */
	public function get_admin_data( $arguments, $field_name, $field ) {
		$data = [
			'classes'     => Arr::get( $field, 'classes', '' ),
			'dependency'  => $this->format_dependency( $field ),
			'id'          => $this->get_field_id( $field_name ),
			'label'       => Arr::get( $field, 'label', '' ),
			'name'        => $this->get_field_name( $field_name ),
			'options'     => Arr::get( $field, 'options', [] ),
			'placeholder' => Arr::get( $field, 'placeholder', '' ),
			'value'       => Arr::get( $arguments, $field_name ),
		];

		$children = Arr::get( $field, 'children', [] );

		if ( ! empty( $children ) ) {
			foreach ( $children as $child_name => $child ) {
				$input_name =  ( 'radio' === $child['type'] ) ? $field_name : $child_name;

				$child_data = $this->get_admin_data(
					$arguments,
					$input_name,
					$child
				);

				$data['children'][ $child_name ] = $child_data;
			}
		}

		$data = array_merge( $field, $data );

		// @todo properly filter this.
		return apply_filters( 'tribe_widget_field_data', $data, $field_name, $this );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_arguments( array $_deprecated = [] ) {
		return $this->filter_arguments( $this->arguments );
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_arguments( $arguments ) {
		/**
		 * Applies a filter to instance arguments.
		 *
		 * @since 4.12.12
		 *
		 * @param array<string,mixed> $arguments Current set of arguments.
		 * @param static              $instance  The widget instance we are dealing with.
		 */
		$arguments = apply_filters( 'tribe_widget_arguments', $arguments, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to instance arguments based on the widget slug of the widget.
		 *
		 * @since 4.12.12
		 *
		 * @param array<string,mixed> $arguments Current set of arguments.
		 * @param static              $instance  The widget instance we are dealing with.
		 */
		$arguments = apply_filters( "tribe_widget_{$widget_slug}_arguments", $arguments, $this );

		return $arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_argument( $index, $default = null ) {
		$argument  = Arr::get( $this->get_arguments(), $index, $default );

		return $this->filter_argument( $argument, $index, $default );
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_argument( $argument, $index, $default = null ) {
		/**
		 * Applies a filter to a specific widget argument, catch all for all widgets.
		 *
		 * @since 4.12.12
		 *
		 * @param mixed               $argument The argument.
		 * @param string|int          $index    Which index we intend to fetch from the arguments.
		 * @param array<string,mixed> $default  Default value if it doesn't exist.
		 * @param static              $instance The widget instance we are dealing with.
		 */
		$argument = apply_filters( 'tribe_widget_argument', $argument, $index, $default, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to a specific widget argument, to a particular widget slug.
		 *
		 * @since 4.12.12
		 *
		 * @param mixed      $argument The argument value.
		 * @param string|int $index    Which index we intend to fetch from the arguments.
		 * @param mixed      $default  Default value if it doesn't exist.
		 * @param static     $instance The widget instance we are dealing with.
		 */
		$argument = apply_filters( "tribe_widget_{$widget_slug}_argument", $argument, $index, $default, $this );

		return $argument;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_sidebar_arguments( $arguments ) {
		$this->sidebar_arguments = $arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_sidebar_arguments() {
		return $this->filter_sidebar_arguments( $this->sidebar_arguments );
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_sidebar_arguments( $arguments ) {
		/**
		 * Applies a filter to the widget sidebar arguments, catch all for all widgets.
		 *
		 * @since 4.13.0
		 *
		 * @param mixed   $arguments The argument.
		 * @param static  $instance  The widget instance we are dealing with.
		 */
		$arguments = apply_filters( 'tribe_widget_sidebar_arguments', $arguments, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to the widget sidebar arguments, to a particular widget slug.
		 *
		 * @since 4.13.0
		 *
		 * @param mixed   $arguments The argument.
		 * @param static  $instance  The widget instance we are dealing with.
		 */
		$arguments = apply_filters( "tribe_widget_{$widget_slug}_sidebar_arguments", $arguments, $this );

		return $arguments;
	}

	/**
	 * Sets up the widgets default arguments.
	 *
	 * @since 4.12.14
	 *
	 * @return array<string,mixed> The default widget arguments.
	 */
	protected function setup_default_arguments() {
		// Setup admin fields.
		$this->default_arguments['admin_fields'] = $this->get_admin_fields();

		// Add the Widget to the arguments to pass to the admin template.
		$this->default_arguments['widget_obj'] = $this;

		return $this->default_arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_default_arguments() {
		return $this->filter_default_arguments( $this->default_arguments );
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_default_arguments( array $default_arguments = [] ) {
		/**
		 * Applies a filter to default instance arguments.
		 *
		 * @since 4.12.12
		 *
		 * @param array<string,mixed>  $default_arguments Current set of default arguments.
		 * @param static               $instance          The widget instance we are dealing with.
		 */
		$default_arguments = apply_filters( 'tribe_widget_default_arguments', $default_arguments, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to default instance arguments based on the widget slug of the widget.
		 *
		 * @since 4.12.12
		 *
		 * @param array<string,mixed>  $default_arguments Current set of default arguments.
		 * @param static               $instance          The widget instance we are dealing with.
		 */
		return apply_filters( "tribe_widget_{$widget_slug}_default_arguments", $default_arguments, $this );
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_args_to_context( array $alterations = [], array $arguments = [] ) {
		/**
		 * Applies a filter to arguments before they get turned into context.
		 *
		 * @since 4.13.0
		 *
		 * @param array<string,mixed>  $alterations Current set of alterations for the context.
		 * @param array<string,mixed>  $arguments   Current set of arguments in the widget.
		 * @param static               $instance    The widget instance we are dealing with.
		 */
		$alterations = apply_filters( 'tribe_widget_args_to_context', $alterations, $arguments, $this );

		$widget_slug = static::get_widget_slug();

		/**
		 * Applies a filter to arguments before they get turned into context based on the widget slug of the widget.
		 *
		 * @since 4.13.0
		 *
		 * @param array<string,mixed>  $alterations Current set of alterations for the context.
		 * @param array<string,mixed>  $arguments   Current set of arguments in the widget.
		 * @param static               $instance    The widget instance we are dealing with.
		 */
		return apply_filters( "tribe_widget_{$widget_slug}_args_to_context", $alterations, $arguments, $this );
	}

	/**
	 * Sets the admin template.
	 *
	 * @since 4.12.14
	 *
	 * @param \Tribe__Template $template The admin template to use.
	 */
	public function set_admin_template( \Tribe__Template $template ) {
		$this->admin_template = $template;
	}

	/**
	 * Returns the current admin template.
	 *
	 * @since 4.12.14
	 *
	 * @return \Tribe__Template The current admin template.
	 */
	public function get_admin_template() {
		return $this->admin_template;
	}

	/**
	 * Get the admin html for the widget form.
	 *
	 * @since 4.12.14
	 *
	 * @param array<string,mixed> $arguments Current set of arguments.
	 *
	 * @return string  HTML for the admin fields.
	 */
	public function get_admin_html( $arguments ) {
		return $this->get_admin_template()->template( [ 'widgets', static::get_widget_slug() ], $arguments );
	}

	/**
	 * Toggles hooks for the widget, will be deactivated after the rendering has happened.
	 *
	 * @since 4.13.0
	 *
	 * @param bool   $toggle Whether to turn the hooks on or off.
	 * @param string $location If we are doing the form (admin) or the display (front end)
	 *
	 * @return void
	 */
	public function toggle_hooks( $toggle, $location = 'display' ) {
		$slug = static::get_widget_slug();

		if ( $toggle ) {
			do_action( 'tec_start_widget_' . $location, $slug );
			$this->add_hooks();
		} else {
			$this->remove_hooks();
			do_action( 'tec_end_widget_' . $location, $slug );
		}

		/**
		 * Fires after widget was setup while rendering a widget.
		 *
		 * @since 4.13.0
		 *
		 * @param bool   $toggle Whether the hooks should be turned on or off. This value is `true` before a widget
		 *                       HTML is rendered and `false` after the widget HTML rendered.
		 * @param static $this   The widget object that is toggling the hooks.
		 */
		do_action( 'tribe_shortcode_toggle_hooks', $toggle, $this );
	}

	/**
	 * Toggles off portions of the template based on widget params.
	 * This runs on the `tribe_shortcode_toggle_hooks` hook when the toggle is true.
	 *
	 * @since 4.13.0
	 */
	protected function add_hooks() {

	}

	/**
	 * Toggles on portions of the template that were modified in `add_template_mods()` above.
	 * This runs on the `tribe_shortcode_toggle_hooks` hook when the toggle is false.
	 * Thus encapsulating our control of these shared pieces to only when the widget is rendering.
	 *
	 * @since 4.13.0
	 */
	protected function remove_hooks() {

	}

	/**********************
	 * Deprecated Methods *
	 **********************/

	/**
	 * Slug of the current widget.
	 *
	 * @since 4.12.12
	 *
	 * @deprecated 4.13.0 Moved into using static::$widget_slug
	 * @todo remove after 2021-08-01
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The slug of the admin widget view.
	 *
	 * @since 4.12.14
	 *
	 * @deprecated 4.13.0 Moved into using static::$widget_slug
	 * @todo remove after 2021-08-01
	 *
	 * @var string
	 */
	protected $view_admin_slug;

	/**
	 * {@inheritDoc}
	 * @deprecated 4.13.0 Moved into using static::get_widget_slug
	 */
	public function get_registration_slug() {
		return static::get_widget_slug();
	}
}
