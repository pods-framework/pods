<?php


class Tribe__REST__Headers__Supported extends Tribe__REST__Headers__Base_Header implements Tribe__REST__Headers__Headers_Interface {

	/**
	 * @var Tribe__Events__Main
	 */
	protected $main;

	public function __construct( Tribe__REST__Headers__Base_Interface $base, Tribe__REST__Main $main ) {
		parent::__construct( $base );
		$this->main = $main;
	}

	/**
	 * Prints TEC REST API related meta on the site.
	 */
	public function add_header() {
		$api_root = $this->base->get_rest_url();

		if ( empty( $api_root ) ) {
			return;
		}

		printf( '<meta name="%s" content="%s">', esc_attr( $this->base->get_api_version_meta_name() ), esc_attr( $this->main->get_version() ) );
		printf( '<meta name="%s" content="%s">', esc_attr( $this->base->get_api_origin_meta_name() ), esc_url( $this->base->get_rest_origin_url() ) );
		printf( '<link rel="%s" href="%s" />', esc_attr( $this->main->get_reference_url() ), esc_url( $api_root ) );
	}

	/**
	 * Sends TEC REST API related headers.
	 */
	public function send_header() {
		if ( headers_sent() ) {
			return;
		}

		$api_root = $this->base->get_rest_url();

		if ( empty( $api_root ) ) {
			return;
		}

		header( $this->base->get_api_version_header() . ': ' . $this->main->get_version() );
		header( $this->base->get_api_root_header() . ': ' . esc_url_raw( $api_root ) );
		header( $this->base->get_api_origin_header() . ': ' . esc_url_raw( $this->base->get_rest_origin_url() ) );
	}
}
