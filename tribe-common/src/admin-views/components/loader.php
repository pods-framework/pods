<?php
/**
 * View: Loader
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/components/loader.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 4.14.9
 *
 * @version 4.14.9
 *
 */
if ( empty( $text ) ) {
	$text = $this->get( 'text' ) ?: __( 'Loading...', 'tribe-common' );
}

if ( empty( $loader_classes ) ) {
	$loader_classes = $this->get( 'classes' ) ?: [];
}

$spinner_classes = [
	'tribe-events-loader__dots',
	'tribe-common-c-loader',
	'tribe-common-a11y-hidden',
];

if ( ! empty( $loader_classes ) ) {
	$spinner_classes = array_merge( $spinner_classes, (array) $loader_classes );
}

?>
<div class="tribe-events-virtual-loader-wrap">
	<div class="tribe-common">
		<div <?php tribe_classes( $spinner_classes ); ?> >
			<?php $this->template( '/components/icons/dot', [ 'classes' => [ 'tribe-common-c-loader__dot', 'tribe-common-c-loader__dot--first' ] ] ); ?>
			<?php $this->template( '/components/icons/dot', [ 'classes' => [ 'tribe-common-c-loader__dot', 'tribe-common-c-loader__dot--second' ] ] ); ?>
			<?php $this->template( '/components/icons/dot', [ 'classes' => [ 'tribe-common-c-loader__dot', 'tribe-common-c-loader__dot--third' ] ] ); ?>
		</div>
	</div>
</div>