<?php
/**
 * View: Phone Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/phone.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--phone' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M11.682 10.882l-1.304 1.629a13.762 13.762 0 01-4.89-4.888l1.63-1.304c.393-.315.525-.855.32-1.315L5.953 1.66a1.108 1.108 0 00-1.292-.623l-2.828.733c-.547.143-.9.672-.822 1.231A16.435 16.435 0 0015 16.99a1.114 1.114 0 001.23-.822l.734-2.83a1.109 1.109 0 00-.622-1.29l-3.346-1.486c-.46-.205-1-.073-1.314.32z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="tribe-common-c-svgicon__svg-stroke"/></svg>
