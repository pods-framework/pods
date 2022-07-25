<?php


/**
 * Class Tribe__Utils__Coordinates_Provider
 *
 * Provided latitude and longitude coordinates for a location.
 */
class Tribe__Utils__Coordinates_Provider {

	/**
	 * @var string
	 */
	public static $google_api_base = 'https://maps.googleapis.com/maps/api/geocode/';

	/**
	 * @var string
	 */
	public static $google_api_json_format = 'json';

	/**
	 * @var string
	 */
	public static $transient_name = 'tribe_resolved_address_coordinates';

	/**
	 * @var bool
	 */
	protected $transient = false;

	/**
	 * @var WP_Http
	 */
	private $http;

	/**
	 * @var Tribe__Utils__Coordinates_Provider
	 */
	protected static $instance;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Utils__Coordinates_Provider
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Tribe__Utils__Coordinates_Provider constructor.
	 *
	 * @param WP_Http|null $https
	 */
	public function __construct( WP_Http $https = null ) {
		$this->http = ! empty( $https ) ? $https : _wp_http_get_object();
	}

	/**
	 * @param string|array $address
	 */
	public function provide_coordinates_for_address( $address ) {

		if ( is_array( $address ) ) {
			$address = implode( ', ', array_filter( array_map( 'trim', $address ) ) );
		}

		$address = trim( $address );

		if ( $location = $this->get_resolved( $address ) ) {
			return $location;
		}

		$base_request_url = trailingslashit( $this->get_google_api_base() ) . $this->get_google_api_json_format();
		$url              = esc_url( add_query_arg( [ 'address' => $address ], $base_request_url ) );
		$response         = $this->http->get( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$decoded = json_decode( $response['body'], true );

		if ( empty( $decoded['status'] ) || 'OK' !== $decoded['status'] ) {
			return false;
		}

		if ( empty( $decoded['results'][0]['place_id'] ) || empty( $decoded['results'][0]['geometry']['location']['lat'] ) || empty( $decoded['results'][0]['geometry']['location']['lng'] ) ) {
			return false;
		}

		$location = $decoded['results'][0]['geometry']['location'];

		$updated_transient = array_merge( $this->get_transient(), [ $address => $location ] );
		set_transient( self::$transient_name, $updated_transient );
		$this->transient = $updated_transient;

		return $location;
	}

	/**
	 * @return null|WP_Http
	 */
	public function get_http() {
		return $this->http;
	}

	protected function get_google_api_base() {
		return self::$google_api_base;
	}

	protected function get_google_api_json_format() {
		return self::$google_api_json_format;
	}

	protected function get_transient() {
		if ( ! is_array( $this->transient ) ) {
			$transient       = get_transient( self::$transient_name );
			$this->transient = is_array( $transient ) ? $transient : [];
		}

		return $this->transient;
	}

	protected function get_resolved( $address ) {
		$transient = $this->get_transient();

		return isset( $transient[ $address ] ) ? $transient[ $address ] : false;
	}
}
