<?php
/**
 * Notice for the Stellar Sale
 *
 * @since 4.14.2
 */

namespace Tribe\Admin\Notice\Marketing;

/**
 * Class Stellar_Sale
 *
 * @since 4.14.2
 *
 * @package Tribe\Admin\Notice\Marketing
 */
class Stellar_Sale extends \Tribe\Admin\Notice\Date_Based {
	/**
	 * {@inheritDoc}
	 */
	public $slug = 'stellar-sale';

	/**
	 * {@inheritDoc}
	 */
	public $start_date = 'July 25th, 2022';

	/**
	 * {@inheritDoc}
	 *
	 * 7am UTC is midnight PDT (-7) and 3am EDT (-4)
	 */
	public $start_time = 19;

	/**
	 * {@inheritDoc}
	 */
	public $end_date = 'July 31st, 2022';

	/**
	 * {@inheritDoc}
	 *
	 * 7am UTC is midnight PDT (-7) and 3am EDT (-4)
	 */
	public $end_time = 19;

	/**
	 * {@inheritDoc}
	 */
	public $extension_date = 'August 2nd, 2022';

	/**
	 * {@inheritDoc}
	 *
	 * 7am UTC is midnight PDT (-7) and 3am EDT (-4)
	 */
	public $extension_time = 19;

	/**
	 * {@inheritDoc}
	 */
	public function display_notice() {
		\Tribe__Assets::instance()->enqueue( [ 'tribe-common-admin' ] );

		// Used in the template.
		$cta_url      = 'https://evnt.is/1aqi';
		$icon_url     = \Tribe__Main::instance()->plugin_url . 'src/resources/images/marketing/circles.svg';
		$icon_classes = [ 'tribe-common-c-svgicon--circles' ];
		$end_date     = $this->get_end_time();

		ob_start();

		include \Tribe__Main::instance()->plugin_path . 'src/admin-views/notices/tribe-stellar-sale.php';

		return ob_get_clean();
	}
}
