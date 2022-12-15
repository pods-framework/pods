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
	 * Control's Type.
	 *
	 * @since 4.13.3
	 *
	 * @var string
	 */
	public $type = 'heading';

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
	public function render_content() { ?>

		<h4 class="customize-control-heading">
			<?php echo esc_html( $this->label ); ?>
		</h4>

		<?php if ( ! empty( $this->description ) ) : ?>

			<p class="customize-control-heading-description">
				<?php echo wp_kses_post( $this->description ); ?>
			</p>

		<?php endif;
	}
}
