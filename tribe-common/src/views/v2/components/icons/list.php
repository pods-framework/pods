<?php
/**
 * View: List Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *                             If not empty, the first is used for the clip path IDs.
 *
 * @version 4.12.14
 * @since   4.14.7     Prevent duplicate IDs.
 *
 */
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--list' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
	// If we have classes, use the first for the id_prefix for target specificity.
	$id_prefix   = array_shift( $classes );
} else {
	// If we don't have classes, use a generated ID.
	$id_prefix = 'tribe-common-c-svgicon__list-clip-path-';
	$id_prefix .= tribe_get_next_cached_increment( $id_prefix );
}

$clip0 = $id_prefix . '-0';
$clip1 = $id_prefix . '-1';
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 19 19" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M.451.432V17.6c0 .238.163.432.364.432H18.12c.2 0 .364-.194.364-.432V.432c0-.239-.163-.432-.364-.432H.815c-.2 0-.364.193-.364.432zm.993.81h16.024V3.56H1.444V1.24zM17.468 3.56H1.444v13.227h16.024V3.56z" class="tribe-common-c-svgicon__svg-fill"/><g clip-path="url(#<?php echo $clip0; ?>)" class="tribe-common-c-svgicon__svg-fill"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.831 4.912v1.825c0 .504.409.913.913.913h1.825a.913.913 0 00.912-.913V4.912A.913.913 0 0014.57 4h-1.825a.912.912 0 00-.913.912z"/><path d="M8.028 7.66a.449.449 0 00.446-.448v-.364c0-.246-.2-.448-.446-.448h-4.13a.449.449 0 00-.447.448v.364c0 .246.201.448.447.448h4.13zM9.797 5.26a.449.449 0 00.447-.448v-.364c0-.246-.201-.448-.447-.448h-5.9a.449.449 0 00-.446.448v.364c0 .246.201.448.447.448h5.9z"/></g><g clip-path="url(#<?php echo $clip1; ?>)" class="tribe-common-c-svgicon__svg-fill"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.831 10.912v1.825c0 .505.409.913.913.913h1.825a.913.913 0 00.912-.912v-1.825A.913.913 0 0014.57 10h-1.825a.912.912 0 00-.913.912z"/><path d="M8.028 13.66a.449.449 0 00.446-.448v-.364c0-.246-.2-.448-.446-.448h-4.13a.449.449 0 00-.447.448v.364c0 .246.201.448.447.448h4.13zM9.797 11.26a.449.449 0 00.447-.448v-.364c0-.246-.201-.448-.447-.448h-5.9a.449.449 0 00-.446.448v.364c0 .246.201.448.447.448h5.9z"/></g><defs><clipPath id="<?php echo $clip0; ?>"><path transform="translate(3.451 4)" d="M0 0h13v4H0z"/></clipPath><clipPath id="<?php echo $clip1; ?>"><path transform="translate(3.451 10)" d="M0 0h13v4H0z"/></clipPath></defs></svg>
