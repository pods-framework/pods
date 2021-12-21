<?php

namespace Pods\Blocks\Types;

use Pods\Whatsit\Store;
use Tribe__Editor__Blocks__Abstract;
use WP_Block;

/**
 * Field block functionality class.
 *
 * @since 2.8.0
 */
abstract class Base extends Tribe__Editor__Blocks__Abstract {

	/**
	 * Set the default attributes of this block.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of attributes.
	 */
	public function default_attributes() {
		$fields = $this->fields();

		$defaults = [];

		foreach ( $fields as $field ) {
			$defaults[ $field['name'] ] = $this->default_attribute( $field );
		}

		return $defaults;
	}

	/**
	 * Get the default attribute for a field.
	 *
	 * @since 2.8.0
	 *
	 * @param array $field The field to get the default attribute for.
	 *
	 * @return mixed The default attribute for a field.
	 */
	public function default_attribute( $field ) {
		$default_value = isset( $field['default'] ) ? $field['default'] : '';

		if ( 'pick' === $field['type'] && isset( $field['data'] ) ) {
			foreach ( $field['data'] as $key => $value ) {
				if ( ! is_array( $value ) ) {
					$value = [
						'label' => $value,
						'value' => $key,
					];
				}

				if ( $default_value === $value['value'] ) {
					$default_value = $value;

					break;
				}
			}
		}

		return $default_value;
	}

	/**
	 * Get list of Field configurations to register with Pods for the block.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of Field configurations.
	 */
	public function fields() {
		return [];
	}

	/**
	 * Register the block with Pods.
	 *
	 * @since 2.8.0
	 */
	public function register_with_pods() {
		$block = $this->block();

		if ( empty( $block ) ) {
			return;
		}

		$block['name'] = $this->slug();

		$this->assets();
		$this->hook();

		pods_register_block_type( $block, $this->fields() );
	}

	/**
	 * Get block configuration to register with Pods.
	 *
	 * @since 2.8.0
	 *
	 * @return array Block configuration.
	 */
	public function block() {
		return [];
	}

	/*
	 * {@inheritDoc}
	 *
	 * @since 2.8.0
	*/
	public function attributes( $params = [] ) {
		// Convert any potential array values for pick/boolean.
		foreach ( $params as $param => $value ) {
			if ( is_array( $value ) ) {
				if ( isset( $value['label'], $value['value'] ) ) {
					$params[ $param ] = $value['value'];
				} elseif ( isset( $value[0]['label'], $value[0]['value'] ) ) {
					$params[ $param ] = array_values( wp_list_pluck( $value, 'value' ) );
				}
			}
		}

		return parent::attributes( $params );
	}

	/**
	 * Render content for block with placeholder template.
	 *
	 * @since 2.8.0
	 *
	 * @param string $heading The heading text.
	 * @param string $content The content text.
	 * @param null|string $image The image content or null if not set.
	 *
	 * @return string The content to render.
	 */
	public function render_placeholder( $heading, $content, $image = null ) {
		ob_start();
		?>
		<div class="pods-block-placeholder_container">
			<div class="pods-block-placeholder_content-container">
				<img src="<?php echo esc_url( PODS_URL . 'ui/images/pods-logo-green.svg' ); ?>" alt="<?php esc_attr_e( 'Pods logo', 'pods' ); ?>" class="pods-logo">
				<div class="pods-block-placeholder_content">
					<h2 class="pods-block-placeholder_title"><?php echo wp_kses_post( $heading ); ?></h2>
					<p><?php echo wp_kses_post( $content ); ?></p>
				</div>
			</div>
			<?php if ( $image ) : ?>
				<?php echo wp_kses_post( $image ); ?>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Determine whether we are preloading a block.
	 *
	 * @since 2.8.8
	 *
	 * @return bool Whether we are preloading a block.
	 */
	public function is_preloading_block() {
		return is_admin() && 'edit' === pods_v( 'action' ) && 0 < (int) pods_v( 'post' );
	}

	/**
	 * Determine whether to preload the block.
	 *
	 * @since 2.8.8
	 *
	 * @param array         $attributes           The block attributes used.
	 * @param WP_Block|null $block                The WP_Block object or null if not provided.
	 *
	 * @return bool Whether to preload the block.
	 */
	public function should_preload_block( $attributes = [], $block = null ) {
		/**
		 * Allow filtering whether to preload the block.
		 *
		 * @since 2.8.8
		 *
		 * @param bool          $should_preload_block Whether to preload the block.
		 * @param array         $attributes           The block attributes used.
		 * @param WP_Block|null $block                The WP_Block object or null if not provided.
		 * @param Base          $block_type           The block type object (not WP_Block).
		 */
		return (bool) apply_filters( 'pods_blocks_types_preload_block', true, $this );
	}
}
