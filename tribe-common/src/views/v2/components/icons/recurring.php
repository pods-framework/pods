<?php
/**
 * View: Recurring Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/recurring.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--recurring' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9 3.296c0 .039 0 .078-.012.104 0 .013-.012.04-.012.052a.54.54 0 01-.118.222L7.27 5.422a.479.479 0 01-.711 0 .61.61 0 010-.783l.734-.809H4.781c-1.53 0-2.785 1.37-2.785 3.066 0 .821.284 1.591.817 2.165a.61.61 0 010 .782.478.478 0 01-.71 0C1.39 9.061 1 8.017 1 6.91c0-2.296 1.695-4.161 3.78-4.161h2.525l-.735-.809a.61.61 0 010-.782.482.482 0 01.711 0L8.87 2.904c.059.066.094.144.118.222 0 .013.012.04.012.052v.118zM13 7.091c0 2.296-1.695 4.161-3.78 4.161H6.694l.735.809a.582.582 0 010 .783.479.479 0 01-.711 0l-1.577-1.761a.569.569 0 01-.118-.222c0-.013-.012-.04-.012-.052C5 10.769 5 10.743 5 10.704c0-.039 0-.078.012-.104 0-.013.012-.04.012-.052a.54.54 0 01.118-.222L6.73 8.578a.482.482 0 01.711 0 .582.582 0 010 .783l-.734.809H9.23c1.529 0 2.785-1.37 2.785-3.066 0-.821-.284-1.591-.818-2.165a.582.582 0 010-.782.482.482 0 01.712 0C12.609 4.927 13 5.97 13 7.09z" stroke-width=".25"/></svg>