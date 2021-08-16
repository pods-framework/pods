<?php
class Tribe__Utils__Global_ID {

	/**
	 * Type of the ID
	 * @var string|bool
	 */
	protected $type = false;

	/**
	 * Origin of this Instance of ID
	 * @var string|bool
	 */
	protected $origin = false;


	/**
	 * Don't allow creation of Global IDs for other types of source
	 * @var array
	 */
	protected $valid_types = [
		'url',
		'meetup',
		'facebook',
		'eventbrite',
	];

	/**
	 * For some types of ID we have a predefined Origin
	 * @var array
	 */
	protected $type_origins = [
		'meetup'     => 'meetup.com',
		'facebook'   => 'facebook.com',
		'eventbrite' => 'eventbrite.com',
	];

	/**
	 * Tribe__Utils__Global_ID constructor.
	 */
	public function __construct() {

		/**
		 * Filters the registered origin types for Global IDs.
		 *
		 * @since 4.7.21
		 *
		 * @param array $type_origins List of origin types.
		 */
		$this->valid_types = apply_filters( 'tribe_global_id_valid_types', $this->valid_types );

		/**
		 * Filters the registered origin URLs for Global IDs.
		 *
		 * @since 4.7.21
		 *
		 * @param array $type_origins List of origin URLs.
		 */
		$this->type_origins = apply_filters( 'tribe_global_id_type_origins', $this->type_origins );

	}

	/**
	 * A setter and getter for the Type of ID
	 *
	 * @param  string|null  $name  When null is passed it will return the current Type
	 * @return mixed               Will return False on invalid type or the Type in String
	 */
	public function type( $name = null ) {
		if ( is_null( $name ) ) {
			return $this->type;
		}

		$name = strtolower( $name );

		if ( ! in_array( $name, $this->valid_types ) ) {
			return false;
		}

		$this->type = $name;

		return $this->type;
	}

	/**
	 * A setter and getter for the origin on this ID
	 *
	 * @param  string|null  $name  When null is passed it will return the current Origin
	 * @return mixed               Will return False on invalid origin or the Origin in String
	 */
	public function origin( $url = null ) {
		if ( ! empty( $this->type_origins[ $this->type ] ) ) {
			$this->origin = $this->type_origins[ $this->type ];
		}

		if ( is_null( $url ) ) {
			return $this->origin;
		}

		$parts = wp_parse_url( $url );

		if ( ! $parts ) {
			return false;
		}

		$this->origin = $parts['host'];

		if ( ! empty( $parts['path'] ) ) {
			$this->origin .= $parts['path'];
		}

		if ( ! empty( $parts['query'] ) ) {
			$this->origin .= '?' . $parts['query'];
		}

		return $this->origin;
	}

	/**
	 * A very simple Generation of IDs
	 *
	 * @param  array  $args Which query arguments will be added to the Origin
	 *
	 * @return string
	 */
	public function generate( array $args = [] ) {
		// We can't do this without type or origin
		if ( ! $this->type() || ! $this->origin() ) {
			return false;
		}

		return add_query_arg( $args, $this->origin() );
	}

	/**
	 * Parse the Global ID string.
	 *
	 * @param string $global_id The previously generated global ID string.
	 *
	 * @return array The parsed $args information built by self::generate()
	 *
	 * @since 4.7.15
	 */
	public function parse( $global_id ) {
		$parsed_global_id = null;

		if ( $global_id ) {
			$global_id = html_entity_decode( $global_id ); // &amp; characters replaced as expected

			$parsed = wp_parse_url( 'http://' . $global_id );

			if ( ! empty( $parsed['query'] ) ) {
				$parsed_query = [];

				wp_parse_str( $parsed['query'], $parsed_query );

				if ( ! empty( $parsed_query ) ) {
					$parsed_global_id = $parsed_query;
				}
			}
		}

		return $parsed_global_id;
	}
}
