<?php

/**
 * Events Gutenberg Utils
 *
 * @since 4.8
 */
class Tribe__Editor__Utils {

	/**
	 * Adds the required prefix of a tribe block with the wp: prefix as well and escaped.
	 *
	 * @since 4.8
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function to_tribe_block_name( $name = '' ) {
		return 'wp:tribe\/' . $name;
	}

	/**
	 * Remove all invalid characters in string that are used to set the name of a block
	 *
	 * @since 4.8
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function to_block_name( $name = '' ) {
		return preg_replace( '/[^a-zA-Z0-9-]/', '', $name );
	}

	/**
	 * Replaces the content of a post where a block is located, removes the space before and after on the same line where
	 * the block is located, it replaces the content of the block with an empty string
	 *
	 * @since 4.8
	 *
	 * @param        $post_id
	 * @param string $block_name
	 * @param string $replacement
	 *
	 * @return bool
	 */
	public function remove_block( $post_id, $block_name = '', $replacement = '' ) {
		$patttern = '/^\s*<!-- ' . $block_name . '.*\/-->\s*$/im';
		return $this->update_post_content( $post_id, $patttern, $replacement );
	}

	/**
	 * Function used to remove the inner blocks and the parent block as well inside of a post_content
	 *
	 * @since 4.8.2
	 *
	 * @param        $post_id
	 * @param        $block_name The name of the block
	 * @param string $replacement The string used to replace the value of the searched block
	 *
	 * @return bool
	 */
	public function remove_inner_blocks( $post_id, $block_name, $replacement = '' ) {
		$pattern = '/^\s*<!-- ' . $block_name . '.*-->\s.*<!-- \/' . $block_name . ' -->/ims';
		return $this->update_post_content( $post_id, $pattern, $replacement );
	}

	/**
	 * Update the content of a post using a pattern to search a specifc string, with a custom
	 * replacement
	 *
	 * @since 4.8.2
	 *
	 * @param        $post_id
	 * @param        $pattern
	 * @param string $replacement The string used to replace the value of the searched block
	 *
	 * @return bool
	 */
	public function update_post_content( $post_id, $pattern, $replacement = '' ) {
		$content = get_post_field( 'post_content', $post_id );

		if ( empty( $content ) ) {
			return false;
		}

		$next_content = preg_replace( $pattern, $replacement, $content );

		/**
		 * Don't update post content if preg_replace fails or content is the update_content
		 * is same as current content on the post to avoid a DB operation.
		 */
		if ( $next_content === null || $next_content === $content ) {
			return false;
		}

		return wp_update_post( [
			'ID'           => $post_id,
			'post_content' => $next_content,
		] );
	}

	/**
	 * Strip the dynamic blocks of the content
	 *
	 * @since 4.8.5
	 *
	 * @param string $content The event content
	 *
	 * @return string
	 */
	public function strip_dynamic_blocks( $content = '' ) {

		if ( ! function_exists( 'strip_dynamic_blocks' ) ) {
			return $content;
		}

		return strip_dynamic_blocks( $content );

	}

	/**
	 * Return the content without the tribe blocks
	 *
	 * @since 4.8.5
	 *
	 * @param string $content The event content
	 *
	 * @return string
	 */
	public function exclude_tribe_blocks( $content = '' ) {

		$match_blocks_exp = '/\<\!\-\- \/?wp\:tribe.*\/?-->/i';

		if ( ! preg_match( $match_blocks_exp, $content ) ) {
			return $content;
		}

		return preg_replace( $match_blocks_exp, '', $content );
	}
}
