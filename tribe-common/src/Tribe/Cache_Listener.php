<?php

/**
 * Listen for events and update their timestamps
 */
class Tribe__Cache_Listener {

	/**
	 * The name of the trigger that will be fired when rewrite rules are generated.
	 */
	const TRIGGER_GENERATE_REWRITE_RULES = 'generate_rewrite_rules';

	/**
	 * The name of the trigger that will be fired when a post is saved.
	 */
	const TRIGGER_SAVE_POST = 'save_post';

	/**
	 * The name of the trigger that will be fired when an option is updated
	 */
	const TRIGGER_UPDATED_OPTION = 'updated_option';

	/**
	 * The singleton instance of the class.
	 *
	 * @var Tribe__Cache_Listener|null
	 */
	private static $instance;

	/**
	 * An instance of the cache object.
	 *
	 * @var Tribe__Cache|null
	 */
	private $cache;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->cache = new Tribe__Cache();
	}

	/**
	 * Run the init functionality (like add_hooks).
	 *
	 * @return void
	 */
	public function init() {
		$this->add_hooks();
	}

	/**
	 * Add the hooks necessary.
	 *
	 * @return void
	 */
	private function add_hooks() {
		add_action( 'save_post', [ $this, 'save_post' ], 0, 2 );
		add_action( 'updated_option', [ $this, 'update_last_updated_option' ], 10, 3 );
		add_action( 'updated_option', [ $this, 'update_last_save_post' ], 10, 3 );
		add_action( 'generate_rewrite_rules', [ $this, 'generate_rewrite_rules' ] );
		add_action( 'clean_post_cache', [ $this, 'save_post' ], 0, 2 );
	}

	/**
	 * Run the caching functionality that is executed on save post.
	 *
	 * @param int     $post_id The post_id.
	 * @param WP_Post $post    The current post object being saved.w
	 */
	public function save_post( $post_id, $post ) {
		if ( in_array( $post->post_type, Tribe__Main::get_post_types() ) ) {
			$this->cache->set_last_occurrence( self::TRIGGER_SAVE_POST );
		}
	}

	/**
	 * Run the caching functionality that is executed on saving tribe calendar options.
	 *
	 * @see 'updated_option'
	 *
	 * @param string $option_name Name of the updated option.
	 * @param mixed  $old_value   The old option value.
	 * @param mixed  $value       The new option value.
	 */
	public function update_last_save_post( $option_name, $old_value, $value ) {
		$triggers = [
			'tribe_events_calendar_options' => true,
			'permalink_structure'           => true,
			'rewrite_rules'                 => true,
			'start_of_week'                 => true,
		];

		$triggers = $this->filter_action_last_occurrence_triggers( $triggers, static::TRIGGER_SAVE_POST, func_get_args() );

		if ( ! empty( $triggers[ $option_name ] ) ) {
			$this->cache->set_last_occurrence( self::TRIGGER_SAVE_POST );
		}
	}

	/**
	 * Run the caching functionality that is executed on saving tribe calendar options.
	 *
	 * @see   'updated_option'
	 *
	 * @since 4.11.0
	 *
	 * @param string $option_name Name of the updated option.
	 * @param mixed  $old_value   The old option value.
	 * @param mixed  $value       The new option value.
	 */
	public function update_last_updated_option( $option_name, $old_value, $value ) {
		$triggers = [
			'active_plugins'                => true,
			'tribe_events_calendar_options' => true,
			'permalink_structure'           => true,
			'rewrite_rules'                 => true,
			'start_of_week'                 => true,
			'sidebars_widgets'              => true,
			'stylesheet'                    => true,
			'template'                      => true,
			'WPLANG'                        => true,
		];

		$triggers = $this->filter_action_last_occurrence_triggers( $triggers, static::TRIGGER_UPDATED_OPTION, func_get_args() );

		if ( ! empty( $triggers[ $option_name ] ) ) {
			$this->cache->set_last_occurrence( self::TRIGGER_UPDATED_OPTION );
		}
	}

	/**
	 * Filtering for last occurrence triggers.
	 *
	 * @since 4.13.2
	 *
	 * @param array<string,bool> $triggers Which options will trigger this given action last occurrence.
	 * @param string             $action   Which action this trigger will set.
	 * @param array              $args     Which arguments from the updated option method.
	 *
	 * @return array
	 */
	public function filter_action_last_occurrence_triggers( array $triggers = [], $action = '', array $args = [] ) {

		/**
		 * Filters the contents of which options will trigger expiring a given actions cache.
		 *
		 * @since 4.13.2
		 *
		 * @param array<string,bool> $triggers Which options will trigger this given action last occurrence.
		 * @param string             $action   Which action this trigger will set.
		 * @param array              $args     Which arguments from the updated option method.
		 */
		$triggers = apply_filters( 'tribe_cache_last_occurrence_option_triggers', $triggers, $action, $args );

		/**
		 * Filters the contents of which options will trigger expiring a given actions cache.
		 * Allows filtering a specific action.
		 *
		 * @since 4.13.2
		 *
		 * @param array<string,bool> $triggers Which options will trigger this given action last occurrence.
		 * @param string             $action   Which action this trigger will set.
		 * @param array              $args     Which arguments from the updated option method.
		 */
		return (array) apply_filters( "tribe_cache_last_occurrence_option_triggers:{$action}", $triggers, $action, $args );
	}

	/**
	 * For any hook that doesn't need any additional filtering
	 *
	 * @param $method
	 * @param $args
	 */
	public function __call( $method, $args ) {
		$this->cache->set_last_occurrence( $method );
	}

	/**
	 * Instance method of the cache listener.
	 *
	 * @return Tribe__Cache_Listener
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = self::create_listener();
		}

		return self::$instance;
	}

	/**
	 * Create a cache listener.
	 *
	 * @return Tribe__Cache_Listener
	 */
	private static function create_listener() {
		$listener = new self();
		$listener->init();

		return $listener;
	}

	/**
	 * Run the caching functionality that is executed when rewrite rules are generated.
	 *
	 * @since 4.9.11
	 */
	public function generate_rewrite_rules() {
		$this->cache->set_last_occurrence( self::TRIGGER_GENERATE_REWRITE_RULES );
	}
}
