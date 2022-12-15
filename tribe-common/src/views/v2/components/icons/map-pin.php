<?php
/**
 * View: Map Pin Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/map-pin.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--map-pin' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M12.932 6.992C12.932 11.034 6.966 17 6.966 17S1 11.034 1 6.992C1 3.2 4.083 1 6.966 1s5.966 2.2 5.966 5.992z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="tribe-common-c-svgicon__svg-stroke"/><path clip-rule="evenodd" d="M6.966 9.136a2.17 2.17 0 100-4.34 2.17 2.17 0 000 4.34z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="tribe-common-c-svgicon__svg-stroke"/></svg>
