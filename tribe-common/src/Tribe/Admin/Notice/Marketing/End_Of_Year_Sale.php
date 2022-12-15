<?php
/**
 * Notice for the end of year Sale
 *
 * @since 4.14.9
 */

namespace Tribe\Admin\Notice\Marketing;

use Tribe__Date_Utils as Dates;

/**
 * Class End_Of_Year_Sale
 *
 * @since 4.14.9
 *
 * @package Tribe\Admin\Notice\Marketing
 */
class End_Of_Year_Sale extends \Tribe\Admin\Notice\Date_Based {
	/**
	 * {@inheritDoc}
	 */
	public $slug = 'end-of-year-sale';

	/**
	 * {@inheritDoc}
	 */
	public $start_date = 'December 23';

	/**
	 * {@inheritDoc}
	 */
	public $end_date = 'December 31';

	/**
	 * {@inheritDoc}
	 */
	public function display_notice() {
		tribe_asset_enqueue( [ 'tribe-common-admin' ] );

		// Set up template variables.
		$template_args = [
			'icon_url' => \Tribe__Main::instance()->plugin_url . 'src/resources/images/marketing/eoy-sale-2021.svg',
			'cta_url'  => 'https://evnt.is/1a-x',
		];

		// Get the sale notice content.
		$content = $this->get_template()->template( 'notices/end-of-year-sale', $template_args, false );

		return $content;
	}

	/**
	 * Unix time for notice end.
	 *
	 * @since 4.14.9
	 *
	 * @return int $end_time The date & time the notice should stop displaying, as a Unix timestamp.
	 */
	public function get_end_time() {
		$date = parent::get_end_time();
		$date = $date->setTime( 23, 59 );

		return $date;
	}
}
