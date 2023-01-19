<?php
namespace Tribe\Admin\Conditional_Content;

use Tribe__Date_Utils as Dates;

/**
 * Set up for end of year sale promo.
 *
 * @since 4.14.9
 */
class End_Of_Year_Sale extends Datetime_Conditional_Abstract {
	/**
	 * Promo slug.
	 *
	 * @since 4.14.9
	 */
	protected $slug = 'end_of_year_sale';

	/**
	 * Start Date.
	 *
	 * @since 4.14.9
	 */
	protected $start_date = 'December 23';

	/**
	 * End Date.
	 *
	 * @since 4.14.9
	 */
	protected $end_date = 'December 31';

	/**
	 * Register actions and filters.
	 *
	 * @since 4.14.9
	 * @return void
	 */
	public function hook() {
		add_action( 'tribe_general_settings_tab_fields', [ $this, 'add_conditional_content' ] );
	}

	/**
	 * Replace the opening markup for the general settings info box.
	 *
	 * @since 4.14.9
	 * @return void
	 */
	public function add_conditional_content( $fields ) {
		// Check if the content should currently be displayed.
		if( ! $this->should_display() ) {
			return $fields;
		}

		// Set up template variables.
		$images_dir =  \Tribe__Main::instance()->plugin_url . 'src/resources/images/';
		$template_args = [
			'branding_logo' => $images_dir . 'logo/tec-brand.svg',
			'background_image' => $images_dir . 'marketing/eoy-sale-promo.png',
			'button_link' => 'https://evnt.is/1a-x',
		];

		// Get the Black Friday promo content.
		$content = $this->get_template()->template( 'conditional_content/end-of-year-sale', $template_args, false );

		// Replace starting info box markup.
		$fields['info-start']['html'] .= $content;

		return $fields;
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
