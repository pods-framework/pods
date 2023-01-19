<?php
/**
 * View: Messages Not Found Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/messages-not-found.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *
 * @version 4.12.14
 *
 */
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--messages-not-found' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 21 23" xmlns="http://www.w3.org/2000/svg"><g fill-rule="evenodd"><path d="M.5 2.5h20v20H.5z"/><path stroke-linecap="round" d="M7.583 11.583l5.834 5.834m0-5.834l-5.834 5.834" class="tribe-common-c-svgicon__svg-stroke"/><path stroke-linecap="round" d="M4.5.5v4m12-4v4"/><path stroke-linecap="square" d="M.5 7.5h20"/></g></svg>
