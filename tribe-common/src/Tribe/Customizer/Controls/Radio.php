<?php
/**
 * Models a Customizer radio.
 *
 * @since   4.13.3
 *
 * @package Tribe\Customizer\Controls
 */

namespace Tribe\Customizer\Controls;

use Tribe\Customizer\Control;

/**
 * Class Radio
 *
 * @since   4.13.3
 *
 * @package Tribe\Customizer\Controls
 */
class Radio extends Control {
	/**
	 * Anyone able to set theme options will be able to see the header.
	 *
	 * @since 4.13.3
	 *
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Render the control's content
	 *
	 * @since 4.13.3
	 */
	public function render_content() {
		if ( empty( $this->choices ) ) {
			return;
		}

		$input_id         = '_customize-input-' . $this->id;
		$description_id   = '_customize-description-' . $this->id;
		$describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
		$name             = '_customize-radio-' . $this->id;
		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $this->description ) ) : ?>
			<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description">
				<?php echo wp_kses_post( $this->description ); ?>
			</span>
		<?php endif; ?>

		<?php foreach ( $this->choices as $value => $label ) : ?>
			<span class="customize-inside-control-row">
				<input
					id="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>"
					type="radio"
					<?php echo $describedby_attr; ?>
					value="<?php echo esc_attr( $value ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					<?php $this->link(); ?>
					<?php checked( $this->value(), $value ); ?>
					/>
				<label for="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>">
					<?php echo wp_kses_post( $label ); ?>
				</label>
			</span>
		<?php endforeach; ?>
		<?php
	}
}
