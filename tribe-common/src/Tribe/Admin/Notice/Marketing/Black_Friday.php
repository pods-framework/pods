<?php
/**
 * Notice for the Black Friday Sale
 *
 * @since 4.14.2
 */

namespace Tribe\Admin\Notice\Marketing;

use Tribe__Date_Utils as Dates;

/**
 * Class Black_Friday
 *
 * @since 4.14.2
 *
 * @package Tribe\Admin\Notice\Marketing
 */
class Black_Friday extends \Tribe\Admin\Notice\Date_Based {
	/**
	 * {@inheritDoc}
	 */
	public $slug = 'black-friday';

	/**
	 * {@inheritDoc}
	 */
	public $start_date = 'fourth Thursday of November';

	/**
	 * {@inheritDoc}
	 */
	public $end_date = 'November 29';

	/**
	 * {@inheritDoc}
	 */
	public $end_time = 23;

	/**
	 * {@inheritDoc}
	 */
	public function display_notice() {
		\Tribe__Assets::instance()->enqueue( [ 'tribe-common-admin' ] );

		// Set up template variables.
		$template_args = [
			'icon_url' => \Tribe__Main::instance()->plugin_url . 'src/resources/images/icons/sale-burst.svg',
			'cta_url'  => 'https://evnt.is/1aqi',
			'end_date' => $this->get_end_time()->format_i18n( 'F jS' ),
		];

		// Get the Black Friday notice content.
		$content = $this->get_template()->template( 'notices/tribe-bf-general', $template_args, false );

		return $content;
	}

	/**
	 * Unix time for notice start.
	 *
	 * @since 4.14.2
	 *
	 * @return int $end_time The date & time the notice should start displaying, as a Unix timestamp.
	 */
	public function get_start_time() {
		$date = parent::get_start_time();
		$date = $date->modify( '-3 days' );

		return $date;
	}
}
