<?php
/**
 * View: Calendar Export Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/cal-export.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 4.15.1
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *
 * @version 4.15.1
 *
 */
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--cal-export' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 23 17" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" clip-rule="evenodd" d="M.128.896V16.13c0 .211.145.383.323.383h15.354c.179 0 .323-.172.323-.383V.896c0-.212-.144-.383-.323-.383H.451C.273.513.128.684.128.896Zm16 6.742h-.901V4.679H1.009v10.729h14.218v-3.336h.901V7.638ZM1.01 1.614h14.218v2.058H1.009V1.614Z" />
  <path d="M20.5 9.846H8.312M18.524 6.953l2.89 2.909-2.855 2.855" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
