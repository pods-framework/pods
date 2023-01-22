<?php
/**
 * Models a Customizer heading, a Control just in name, it does not control any setting.
 *
 * @since   4.12.14
 *
 * @package Tribe\Customizer\Controls
 */

namespace Tribe\Customizer\Controls;

use Tribe\Customizer\Control;

/**
 * Class Heading
 *
 * @since   4.12.14
 *
 * @package Tribe\Customizer\Controls
 */
class Heading extends Control {
	/**
	 * Anyone able to set theme options will be able to see the header.
	 *
	 * @since 4.12.14
	 *
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * The heading does not control any setting.
	 *
	 * @since 4.12.14
	 *
	 * @var array<string,mixed>
	 */
	public $settings = [];

	/**
	 * Render the control's content
	 *
	 * @since 4.12.14
	 */
	public function render_content() {
		?>
		<h4 style="font-size: 20px; font-weight: normal; line-height: 1.75; margin-top: 0; margin-bottom: 0px;">
			<?php echo esc_html( $this->label ); ?>
		</h4>
		<?php
	}
}
