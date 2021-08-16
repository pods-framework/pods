<?php
/**
 * View: Website Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/website.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--website' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 16 18" xmlns="http://www.w3.org/2000/svg"><path d="M14.531 1.5l-7.5 8M7.969 1.5h6.562v7M4.219 1.5H1.406c-.517 0-.937.448-.937 1v13c0 .552.42 1 .937 1h12.188c.517 0 .937-.448.937-1v-3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="tribe-common-c-svgicon__svg-stroke"/></svg>
