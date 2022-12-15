<?php
/**
 * Models a Customizer separator, a Control just in name, it does not control any setting.
 *
 * @since   4.13.3
 *
 * @package Tribe\Customizer\Controls
 */

namespace Tribe\Customizer\Controls;

use Tribe\Customizer\Control;

/**
 * Class Separator
 *
 * @since   4.13.3
 *
 * @package Tribe\Customizer\Controls
 */
class Separator extends Control {

	/**
	 * Control's Type.
	 *
	 * @since 4.13.3
	 *
	 * @var string
	 */
	public $type = 'separator';

	/**
	 * Anyone able to set theme options will be able to see the header.
	 *
	 * @since 4.13.3
	 *
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * The heading does not control any setting.
	 *
	 * @since 4.13.3
	 *
	 * @var array<string,mixed>
	 */
	public $settings = [];

	/**
	 * Render the control's content
	 *
	 * @since 4.13.3
	 */
	public function render_content() {
		?>
		<p>
			<hr>
		</p>
		<?php
	}
}
