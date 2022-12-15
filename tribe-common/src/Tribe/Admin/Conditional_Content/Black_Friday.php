<?php
namespace Tribe\Admin\Conditional_Content;

use Tribe__Date_Utils as Dates;

/**
 * Set up for Black Friday promo.
 *
 * @since 4.14.7
 */
class Black_Friday extends Datetime_Conditional_Abstract {
	/**
	 * Promo slug.
	 *
	 * @since 4.14.7
	 */
	protected $slug = 'black_friday';

	/**
	 * Start Date.
	 *
	 * @since 4.14.7
	 */
	protected $start_date = 'fourth Thursday of November';

	/**
	 * End Date.
	 *
	 * @since 4.14.7
	 */
	protected $end_date = 'November 30th';

	/**
	 * Register actions and filters.
	 *
	 * @since 4.14.7
	 * @return void
	 */
	public function hook() {
		add_action( 'tribe_general_settings_tab_fields', [ $this, 'add_conditional_content' ] );
	}

	/**
	 * Start the Monday before Thanksgiving.
	 *
	 * @since 4.14.7
	 * @return int - Unix timestamp
	 */
	protected function get_start_time() {
		$date = parent::get_start_time();
		$date = $date->modify( '-3 days' );

		return $date;
	}

	/**
	 * Replace the opening markup for the general settings info box.
	 *
	 * @since 4.14.7
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
			'background_image' => $images_dir . 'marketing/bf-promo.png',
			'button_link' => 'https://evnt.is/1aqi',
		];

		// Get the Black Friday promo content.
		$content = $this->get_template()->template( 'conditional_content/black-friday', $template_args, false );

		// Replace starting info box markup.
		$fields['info-start']['html'] .=  $content;

		return $fields;
	}
}
