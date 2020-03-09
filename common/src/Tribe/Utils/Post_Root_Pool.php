<?php


class Tribe__Utils__Post_Root_Pool {

	/**
	 * @var string
	 */
	protected $pool_transient_name = 'tribe_ticket_prefix_pool';

	/**
	 * @var array|bool
	 */
	protected static $prefix_pool = false;

	/**
	 * @var string
	 */
	protected $root_separator = '-';

	/**
	 * @var array
	 */
	protected $postfix = 1;

	/**
	 * @var WP_Post
	 */
	protected $current_post = null;

	/**
	 * Generates a unique root for a post using its post_name.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function generate_unique_root( WP_Post $post ) {
		$post_name = $post->post_name;

		// A lot fo these get urlencoded, so let's try to fix that first
		$post_name = tribe_maybe_urldecode( $post_name );

		$this->current_post = $post;
		$flipped_pool       = array_flip( $this->fetch_pool() );

		if ( isset( $flipped_pool[ $this->current_post->ID ] ) ) {
			return $flipped_pool[ $this->current_post->ID ] . $this->root_separator;
		}

		$root = $this->build_root_from( $post_name );

		return $root . $this->root_separator;
	}

	/**
	 * @param string $post_name
	 *
	 * @param string $postfix
	 *
	 * @return string
	 */
	protected function build_root_from( $post_name, $postfix = '' ) {
		$candidate = $this->build_root_candidate( $post_name, $postfix );

		$initial_candidate = $candidate;

		while ( $this->is_in_pool( $candidate ) ) {
			$postfix   = $this->postfix;
			$candidate = $initial_candidate . '-' . $postfix;
			$this->postfix ++;
		}

		$this->postfix = 1;

		$this->insert_root_in_pool( $candidate );

		return $candidate;
	}

	/**
	 * @return string
	 */
	public function get_pool_transient_name() {
		return $this->pool_transient_name;
	}

	/**
	 * @param $string
	 *
	 * @return string
	 * @deprecated 4.7.18
	 */
	protected function uc_first_letter( $string ) {
		_deprecated_function( __METHOD__, '4.7.18', 'tribe_uc_first_letter' );

		return is_numeric( $string ) ? $string : tribe_uc_first_letter( $string );
	}

	/**
	 * @param $string
	 *
	 * @return string
	 * @deprecated 4.7.18
	 */
	protected function safe_strtoupper( $string ) {
		_deprecated_function( __METHOD__, '4.7.18', 'tribe_strtoupper' );

		return is_numeric( $string ) ? $string : tribe_strtoupper( $string );
	}

	/**
	 * @param string $candidate
	 */
	protected function is_in_pool( $candidate ) {
		$pool = $this->fetch_pool();

		return isset( $pool[ $candidate ] );
	}

	/**
	 * @return array
	 */
	protected function fetch_pool() {
		if ( false === self::$prefix_pool ) {
			$this->maybe_init_pool();
		}

		return self::$prefix_pool;
	}

	protected function maybe_init_pool() {
		self::$prefix_pool = get_transient( $this->pool_transient_name );
		if ( self::$prefix_pool === false ) {
			self::$prefix_pool = array();
			set_transient( $this->pool_transient_name, array() );
		}
	}

	/**
	 * @param string $unique_root
	 */
	protected function insert_root_in_pool( $unique_root ) {
		$prefix_pool                 = $this->fetch_pool();
		$prefix_pool[ $unique_root ] = $this->current_post->ID;
		self::$prefix_pool           = $prefix_pool;
		set_transient( $this->pool_transient_name, $prefix_pool );
	}

	public static function reset_pool() {
		self::$prefix_pool = false;
	}

	/**
	 * @param $post_name
	 * @param $postfix
	 *
	 * @return string
	 */
	protected function build_root_candidate( $post_name, $postfix ) {
		$frags = explode( '-', $post_name );

		$candidate = implode( '', array_map( 'strtoupper', $frags ) );

		if ( strlen( $candidate ) > 9 ) {
			$frags     = array_filter( $frags );
			$candidate = implode( '', array_map( 'tribe_uc_first_letter', $frags ) );
		}

		$candidate = $candidate . $postfix;

		return $candidate;
	}

	/**
	 * Primes the post pool.
	 *
	 * @param array $pool
	 * @param bool  $override_transient If `true` the transient too will be overwritten.
	 */
	public function set_pool( array $pool, $override_transient = false ) {
		self::$prefix_pool = $pool;
		if ( $override_transient ) {
			set_transient( $this->pool_transient_name, $pool );
		}
	}

	/**
	 * Whether the pool transient has been primed or not.
	 *
	 * @return bool
	 */
	public function is_primed() {
		return get_transient( $this->pool_transient_name ) !== false;
	}

	/**
	 * @return array
	 */
	public function get_pool() {
		return $this->fetch_pool();
	}
}
