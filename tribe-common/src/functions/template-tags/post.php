<?php

/**
 * Gets the post content. Basically a wrapper around `get_the_content` that will prevent warnings on PHP 7.3
 * and be compatible with WP 5.3
 *
 * @since 4.9.23
 *
 * @global WP_Post  $post          Current post on the loop
 * @global string   $wp_version    Which version of WordPress we are currently dealing with
 *
 * @param string             $more_link_text Optional. Content for when there is more text.
 * @param bool               $strip_teaser   Optional. Strip teaser content before the more text. Default is false.
 * @param WP_Post|object|int $post_id        Optional. WP_Post instance or Post ID/object. Default is null.
 *
 * @return string
 */
function tribe_get_the_content( $more_link_text = null, $strip_teaser = false, $post_id = null ) {
	global $post, $wp_version;

	// Save the global post to be able to restore it later.
	$previous_post = $post;

	$post = get_post( $post_id );

	// Pass in the third param when dealing with WP version 5.2 or higher.
	if ( version_compare( $wp_version, '5.2', '>=' ) ) {
		$content = get_the_content( $more_link_text, $strip_teaser, $post );
	} else {
		$content = get_the_content( $more_link_text, $strip_teaser );
	}

	$has_blocks = function_exists( 'has_blocks' ) && has_blocks( $content );
	// If blocks are present we need to run the content filter.

	if ( $has_blocks || ! doing_filter( 'the_content' ) ) {
		/**
		 * Filters the post content.
		 *
		 * @since 0.71 of WordPress
		 *
		 * @param string $content Content of the current post.
		 */
		$content = apply_filters( 'the_content', $content );
	}

	$content = str_replace( ']]>', ']]&gt;', $content );

	$post = $previous_post;

	return $content;
}

/**
 * Prints the post content.
 *
 * @since 4.9.23
 *
 * @global WP_Post  $post          Current post on the loop
 * @global string   $wp_version    Which version of WordPress we are currently dealing with
 *
 * @param string             $more_link_text Optional. Content for when there is more text.
 * @param bool               $strip_teaser   Optional. Strip teaser content before the more text. Default is false.
 * @param WP_Post|object|int $post_id        Optional. WP_Post instance or Post ID/object. Default is null.
 *
 * @return void
 */
function tribe_the_content( $more_link_text = null, $strip_teaser = false, $post_id = null ) {
	echo tribe_get_the_content( $more_link_text, $strip_teaser, $post_id );
}

/**
 * Wrapper for post_class function that allows us to in-memory cache
 *
 * @since 4.11.0
 *
 * @param string|string[] $class   Space-separated string or array of class names to add to the class list.
 * @param int|WP_Post     $post    Optional. Post ID or post object.
 *
 * @return string[] Array of class names.
 */
function tribe_get_post_class( $class, $post ) {
	static $post_classes = [];

	if ( is_numeric( $post ) ) {
		$post_id = $post;
	} else {
		$post_id = $post->ID;
	}

	if ( ! isset( $post_classes[ $post_id ] ) ) {
		$post_classes[ $post_id ] = get_post_class( [], $post );
	}

	if ( ! is_array( $class ) ) {
		$class = explode( ' ', $class );
	}

	return array_merge( $class, $post_classes[ $post_id ] );
}
