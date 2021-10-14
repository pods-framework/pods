<?php
/**
 * View: Mail Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/mail.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--mail' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M1.405 1.405l7.87 7.043 7.313-7.051" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="tribe-common-c-svgicon__svg-stroke"/><path clip-rule="evenodd" d="M1 2.38v10.482c0 .762.618 1.38 1.38 1.38h13.24a1.38 1.38 0 001.38-1.38V2.38A1.38 1.38 0 0015.62 1H2.38A1.38 1.38 0 001 2.38z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="tribe-common-c-svgicon__svg-stroke"/></svg>
