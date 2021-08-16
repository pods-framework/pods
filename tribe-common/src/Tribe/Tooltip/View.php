<?php

namespace Tribe\Tooltip;

/**
 * Class View
 *
 * @since 4.9.8
 */
class View extends \Tribe__Template {

	/**
	 * Where in the themes we will look for templates
	 *
	 * @since 4.10.2
	 *
	 * @var string
	 */
	public $template_namespace = 'tooltips';

	/**
	 * View constructor.
	 *
	 * @since 4.9.8
	 */
	public function __construct() {
		$this->set_template_origin( \Tribe__Main::instance() );
		$this->set_template_folder( 'src/views/tooltip' );

		// Configures this templating class to extract variables
		$this->set_template_context_extract( true );

		// Uses the public folders
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Public wrapper for build method
	 *
	 * @since 4.9.8
	 *
	 * @param array|string $message Array of messages or single message as string.
	 * @param array $args {
	 *     List of arguments to override tooltip template.
	 *
	 *     @var array  $context      Any additional context data you need to expose to this file (optional).
	 *     @var string $classes      Additional classes for the icon span (optional).
	 *     @var string $direction    Direction the tooltip should be from the trigger (down).
	 *     @var string $icon         dashicon classname to use, without the `dashicon-` (info).
	 *     @var string $wrap_classes Classes for the tooltip wrapper (optional).
	 * }
	 * @return string A string of html for the tooltip.
	 */
	public function render_tooltip( $message, $args = [] ) {
		if ( empty( $message ) ) {
			return;
		}

		/** @var \Tribe__Assets $assets */
		$assets = tribe( 'assets' );
		$assets->enqueue_group( 'tribe-tooltip' );

		$html = $this->build_tooltip( $message, $args );

		return $html;
	}

	/**
	 * Factory method for tooltip HTML
	 *
	 * @since 4.9.8
	 *
	 * @param array|string $message array of messages or single message as string.
	 * @param array $args {
	 *     List of arguments to override tooltip template.
	 *
	 *     @var array  $context      Any additional context data you need to expose to this file (optional).
	 *     @var string $classes      Additional classes for the icon span (optional).
	 *     @var string $direction    Direction the tooltip should be from the trigger (down).
	 *     @var string $icon         dashicon classname to use, without the `dashicon-` (info).
	 *     @var string $wrap_classes Classes for the tooltip wrapper (optional).
	 * }
	 * @return string A string of html for the tooltip.
	 */
	private function build_tooltip( $message, $original_args ) {
		$default_args = [
			'classes'      => '',
			'context'      => '',
			'direction'    => 'down',
			'icon'         => 'info',
			'wrap_classes' => '',
		];

		$args = wp_parse_args( $original_args, $default_args );

		// Check for message to be passed.
		if ( empty( $message ) ) {
			return '';
		}

		// Setup message as an array of messages
		$messages = (array) $message;

		$args['messages'] = $messages;

		ob_start();

		/**
		 * Allow us to filter the tooltip template
		 *
		 * @since  4.9.8
		 *
		 * @param string $template The tooltip template name.
		 * @param array $args Extra arguments, defaults include icon, classes, direction, and context.
		 */
		$template_name = apply_filters( 'tribe_tooltip_template', 'tooltip', $args );

		$template = $this->template( $template_name, $args, false );

		if ( ! empty( $template ) ) {
			 echo $template;
		}

		$html = ob_get_clean();

		/**
		 * Allow us to filter the tooltip output
		 *
		 * @since  4.9.8
		 *
		 * @param string $html The tooltip HTML.
		 * @param array $messages An array of message strings.
		 * @param array $args Extra arguments, defaults include icon, classes, direction, and context.
		 */
		return apply_filters( 'tribe_tooltip_html', $html, $messages, $args );
	}

}
