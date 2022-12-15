<?php
/**
 * Models a Number input.
 * This really only overrides the Core control object
 * to ensure that the input is wrapped in <div class="customize-control-content">
 * so that it matches our other controls stylistically (spacing, etc).
 *
 * @since   4.12.13
 *
 * @package Tribe\Customizer\Controls
 */

namespace Tribe\Customizer\Controls;

use Tribe\Customizer\Control;

/**
 * Class Number
 *
 * @since   4.12.13
 *
 * @package Tribe\Customizer\Controls
 */
class Number extends Control {

	/**
	 * Control's Type.
	 *
	 * @since 4.12.13
	 *
	 * @var string
	 */
	public $type = 'number';

	/**
	 * Anyone able to set theme options will be able to see the input.
	 *
	 * @since 4.12.13
	 *
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Render the control's content
	 *
	 * @since 4.12.13
	 */
	public function render_content() {
		$input_id         = '_customize-input-' . $this->id;
		$description_id   = '_customize-description-' . $this->id;
		$describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
		$name             = '_customize-number-' . $this->id;

		if ( ! empty( $this->label ) ) : ?>
			<label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
		<?php endif; ?>
		<?php if ( ! empty( $this->description ) ) : ?>
			<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description">
				<?php echo wp_kses_post( $this->description ); ?>
			</span>
		<?php endif; ?>
		<div class="customize-control-content">
			<input
				id="<?php echo esc_attr( $input_id ); ?>"
				type="<?php echo esc_attr( $this->type ); ?>"
				<?php echo $describedby_attr; ?>
				<?php $this->input_attrs(); ?>
				<?php if ( ! isset( $this->input_attrs['value'] ) ) : ?>
					value="<?php echo esc_attr( $this->value() ); ?>"
				<?php endif; ?>
				<?php $this->link(); ?>
			/>
		</div>
	<?php
	}

}
