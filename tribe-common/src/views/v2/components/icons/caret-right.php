<?php
/**
 * View: Caret Right Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/caret-right.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *
 * @version 4.12.10
 *
 */
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--caret-right' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 16"><path d="M.3 1.6L1.8.1 9.7 8l-7.9 7.9-1.5-1.5L6.7 8 .3 1.6z"/></svg>
