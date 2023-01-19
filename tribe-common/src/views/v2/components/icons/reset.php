<?php
/**
 * View: Reset Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/reset.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--reset' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg"><path d="M12.268 7.5a5.633 5.633 0 11-.886-3.033M11.4 1v3.467H7.934" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
