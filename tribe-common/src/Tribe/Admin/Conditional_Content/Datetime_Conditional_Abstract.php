<?php
namespace Tribe\Admin\Conditional_Content;

use Tribe__Date_Utils as Dates;

/**
 * Abstract class for conditional content.
 *
 * @since 4.14.7
 */
abstract class Datetime_Conditional_Abstract {
	/**
	 * Item slug.
	 *
	 * @since 4.14.7
	 */
	protected $slug = '';

	/**
	 * Start date.
	 *
	 * @since 4.14.7
	 */
	protected $start_date;

	/**
	 * Start time.
	 *
	 * @since 4.14.7
	 */
	protected $start_time;

	/**
	 * End date.
	 *
	 * @since 4.14.7
	 */
	protected $end_date;

	/**
	 * End time.
	 *
	 * @since 4.14.7
	 */
	protected $end_time;

	/**
	 * Stores the instance of the template engine that we will use for rendering the page.
	 *
	 * @since 4.14.7
	 *
	 * @var \Tribe__Template
	 */
	protected $template;

	/**
	 * Register actions and filters.
	 *
	 * @since 4.14.7
	 * @return void
	 */
	abstract function hook();

	/**
	 * Unix datetime for content start.
	 *
	 * @since 4.14.7
	 * @return int - Unix timestamp
	 */
	protected function get_start_time() {
		$date = Dates::build_date_object( $this->start_date, 'UTC' );
		$date = $date->setTime( $this->start_time, 0 );

		/**
		 * Allow filtering of the start date for testing.
		 *
		 * @since 4.14.7
		 * @param \DateTime $date - Unix timestamp for start date
		 * @param object $this
		 */
		$date = apply_filters( "tec_admin_conditional_content_{$this->slug}_start_date", $date, $this );

		return $date;
	}

	/**
	 * Unix datetime for content end.
	 *
	 * @since 4.14.7
	 * @return int - Unix timestamp
	 */
	protected function get_end_time() {
		$date = Dates::build_date_object( $this->end_date, 'UTC' );
		$date = $date->setTime( $this->end_time, 0 );

		/**
		 * Allow filtering of the end date for testing.
		 *
		 * @since 4.14.7
		 * @param \DateTime $date - Unix timestamp for end date
		 * @param object $this
		 */
		$date = apply_filters( "tec_admin_conditional_content_{$this->slug}_end_date", $date, $this );

		return $date;
	}

	/**
	 * Whether the content should display.
	 *
	 * @since 4.14.7
	 * @return boolean - Whether the content should display
	 */
	protected function should_display() {
		$now          = Dates::build_date_object( 'now', 'UTC' );
		$notice_start = $this->get_start_time();
		$notice_end   = $this->get_end_time();
		$display      = $notice_start <= $now && $now < $notice_end;

		/**
		 * Allow filtering whether the content should display.
		 *
		 * @since 4.14.7
		 * @param bool $should_display - whether the content should display
		 * @param object $this - the conditional content object
		 */
		$should_display = apply_filters( "tec_admin_conditional_content_{$this->slug}_should_display", $display, $this );

		return $should_display;
	}

	/**
	 * Gets the template instance used to setup the rendering of the page.
	 *
	 * @since 4.14.7
	 *
	 * @return \Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new \Tribe__Template();
			$this->template->set_template_origin( \Tribe__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}
}
