<?php

namespace TEC\Common\Editor\Full_Site;

/**
 * Class Template_Utils.
 *
 * @since   4.14.18
 *
 * @package TEC\Common\Editor\Full_Site
 */
class Template_Utils {
	/**
	 * Returns an array containing the references of the passed blocks and their inner blocks.
	 *
	 * When we return we are replacing/overwriting $blocks with $all_blocks so we pass-by-reference.
	 * If we don't pass-by-reference the non-event blocks get lost (ex: header and footer)
	 *
	 * @since 4.14.18
	 *
	 * @param array<array<string,mixed>> $blocks Array of parsed block objects.
	 *
	 * @return array<array<string,mixed>> Block references to the passed blocks and their inner blocks.
	 */
	public static function flatten_blocks( &$blocks ) {
		$all_blocks = [];
		$queue      = [];

		foreach ( $blocks as &$block ) {
			$queue[] = &$block;
		}

		$queue_count = count( $queue );

		while ( $queue_count > 0 ) {
			$block = &$queue[0];
			array_shift( $queue );
			$all_blocks[] = &$block;

			if ( ! empty( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as &$inner_block ) {
					$queue[] = &$inner_block;
				}
			}

			$queue_count = count( $queue );
		}

		return $all_blocks;
	}

	/**
	 * Parses wp_template content and injects the current theme's stylesheet as a theme attribute into
	 * each wp_template_part.
	 *
	 * @since 4.14.18
	 *
	 * @param string $template_content serialized wp_template content.
	 *
	 * @return string Updated wp_template content.
	 */
	public static function inject_theme_attribute_in_content( $template_content ) {
		$has_updated_content = false;
		$new_content         = '';
		$template_blocks     = parse_blocks( $template_content );

		$blocks = static::flatten_blocks( $template_blocks );
		foreach ( $blocks as &$block ) {
			if (
				'core/template-part' === $block['blockName'] &&
				! isset( $block['attrs']['theme'] )
			) {
				$block['attrs']['theme'] = wp_get_theme()->get_stylesheet();
				$has_updated_content     = true;
			}
		}

		if ( $has_updated_content ) {
			foreach ( $template_blocks as &$block ) {
				$new_content .= serialize_block( $block );
			}

			return $new_content;
		}

		return $template_content;
	}
}
