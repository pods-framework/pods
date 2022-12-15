<?php

/**
 * Upsell notice class.
 *
 * @since 4.14.17
 *
 * @package Tribe\Admin\Upsell_Notice
 */

namespace Tribe\Admin\Upsell_Notice;

class Main {

	/**
	 * Stores the instance of the template engine that we will use for rendering the page.
	 *
	 * @since 4.14.17
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Get template object.
	 *
	 * @since 4.14.17
	 *
	 * @return \Tribe__Template
	 */
	private function get_template() {
		if ( empty( self::$template ) ) {
			$this->template = new \Tribe__Template();
			$this->template->set_template_origin( \Tribe__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views/notices/upsell' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}

	/**
	 * Checks if upsell should be rendered.
	 * 
	 * @since 4.14.17
	 *
	 * @return boolean
	 */
	private function should_render() {
		if ( function_exists( 'tec_should_hide_upsell' ) ) {
			return ! tec_should_hide_upsell();
		}
		if ( defined( 'TRIBE_HIDE_UPSELL' ) ) {
			return ! tribe_is_truthy( TRIBE_HIDE_UPSELL );
		}
		return true;
	}

	 /**
	  * Render upsell notice.
	  * 
	  * @since 4.14.17
	  *
	  * @param array  $args Array of arguments that will ultimately be sent to the template.
	  * @param bool   $echo Whether or not to echo the HTML. Defaults to true.
	  *
	  * @return string HTML of upsell notice.
	  */
	public function render( $args, $echo = true ) {
		// Check if upsell should be rendered.
		if ( ! $this->should_render() ) {
			return;
		}

		// Default args for the container.
		$args = wp_parse_args( $args, [
			'classes'     => [],
			'text'        => '',
			'link_target' => '_blank',
			'icon_url'    => tribe_resource_url( 'images/icons/circle-bolt.svg', false, null, \Tribe__Main::instance() ),
			'link'    => [],
		] );

		// Default args for the link.
		$args['link'] = wp_parse_args( $args['link'], [
			'classes' => [],
			'text'    => '',
			'url'     => '',
			'target'  => '_blank',
			'rel'     => 'noopener noreferrer',
		] );

		$template = $this->get_template();
		return $template->template( 'main', $args, $echo );
	}
}