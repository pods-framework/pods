<?php
/**
 * Facilitiates live date previews in the Events > Settings > Display admin screen.
 */
class Tribe__Admin__Live_Date_Preview {
	protected $target_fields = array(
		'dateWithYearFormat',
		'dateWithoutYearFormat',
		'monthAndYearFormat',
		'weekDayFormat',
	);

	/**
 	 * Static Singleton Holder
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		return self::$instance ? self::$instance : self::$instance = new self;
	}

	/**
	 * Adds live date previews to the display settings tab (nothing is setup unless
	 * the user is actually on that tab).
	 */
	public function __construct() {
		add_action( 'tribe_settings_after_do_tabs', array( $this, 'listen' ) );
	}

	/**
	 * If the user looking at the Display settings tab, adds live date preview facilities.
	 */
	public function listen() {
		// We are only interested in the "Display" tab
		if ( 'display' !== Tribe__Settings::instance()->currentTab ) {
			return;
		}

		/**
		 * Add or remove fields which should have live date/time preview facilities.
		 *
		 * @var array $target_fields
		 */
		$this->target_fields = (array) apply_filters( 'tribe_settings_date_preview_fields', $this->target_fields );

		add_filter( 'tribe_field_div_end', array( $this, 'setup_date_previews' ), 10, 2 );

		// We are still before `admin_enqueue_scripts` making it safe to use `tribe_asset`
		tribe_asset( Tribe__Main::instance(), 'tribe-date-live-refresh', 'admin-date-preview.js', array( 'jquery' ), 'admin_enqueue_scripts' );
	}

	public function setup_date_previews( $html, $field ) {
		// Not one of the fields we're interested in? Return without modification
		if ( ! in_array( $field->id, $this->target_fields ) ) {
			return $html;
		}

		$preview = esc_html( date_i18n( $field->value ) );
		return " <code class='live-date-preview'> $preview </code> $html";
	}
}
