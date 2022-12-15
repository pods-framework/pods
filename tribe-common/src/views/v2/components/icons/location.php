<?php
/**
 * View: Location Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/location.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--location' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 10 16" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.682 1.548a5.166 5.166 0 00-7.375 0C-3.073 5.984 4.959 15.36 4.994 15.36c.051-.001 8.092-9.35 3.688-13.812zM4.994 2.833c1.27 0 2.301 1.043 2.301 2.331 0 1.287-1.03 2.33-2.301 2.33-1.272 0-2.3-1.043-2.3-2.33 0-1.287 1.028-2.331 2.3-2.331z"/></svg>