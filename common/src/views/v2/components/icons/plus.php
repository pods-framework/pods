<?php
/**
 * View: Plus Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/plus.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--plus' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg"><path d="M11 5.881H1M5.88 1v10" stroke-width="2" stroke-linecap="square"/></svg>
