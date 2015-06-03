<?php
/**
 * @package  Pods
 * @category Admin
 */

include_once( ABSPATH . WPINC . '/feed.php' );

$feed = fetch_feed( 'http://pods.io/forums/forum/general-discussion/pods-2-x/feed/' );

if ( ! is_wp_error( $feed ) ) {
	$max_items = $feed->get_item_quantity( 6 );
	$rss_items = $feed->get_items( 0, $max_items );

	if ( 0 < $max_items ) {
		?>
		<h3>Latest Forum Posts at <a href="http://pods.io/forums/forum/general-discussion/pods-2-x/" target="_blank">pods.io</a></h3>

		<ul class="ul-disc">
			<?php
			foreach ( $rss_items as $item ) {
				$authors = $item->get_authors();

				$author_text = '';

				foreach ( $authors as $author ) {
					$author_text = '<br /> by ';

					if ( ! empty( $author->link ) ) {
						$author_text .= '<a href="' . $author->link . '" target="_blank">';
					}

					$author_text .= $author->name;

					if ( ! empty( $author->link ) ) {
						$author_text .= '</a>';
					}
				}
				?>
				<li>
					<a href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo esc_html( $item->get_title() ); ?></a>
					<?php echo $author_text; ?>
					<br /> on <?php echo $item->get_date( 'm/d/Y' ); ?>
				</li>
			<?php
			}
			?>
		</ul>

		<hr />
	<?php
	}
}

$feed = fetch_feed( 'https://github.com/pods-framework/pods/commits/' . PODS_GITHUB_BRANCH . '.atom' );

if ( ! is_wp_error( $feed ) ) {
	$max_items = $feed->get_item_quantity( 6 );
	$rss_items = $feed->get_items( 0, $max_items );

	if ( 0 < $max_items ) {
		?>
		<h3>Latest Activity on <a href="http://github.com/pods-framework/pods" target="_blank">GitHub</h3>

		<ul class="ul-disc">
			<?php
			foreach ( $rss_items as $item ) {
				$authors = $item->get_authors();

				$author_text = '';

				foreach ( $authors as $author ) {
					$author_text = '<br /> by ';

					if ( ! empty( $author->link ) ) {
						$author_text .= '<a href="' . $author->link . '" target="_blank">';
					}

					$author_text .= $author->name;

					if ( ! empty( $author->link ) ) {
						$author_text .= '</a>';
					}
				}
				?>
				<li>
					<a href="<?php echo esc_url( $item->get_permalink() ); ?>">Commit</a>: <?php echo esc_html( $item->get_title() ); ?>
					<?php echo $author_text; ?>
					<br /> on <?php echo $item->get_date( 'm/d/Y' ); ?>
				</li>
			<?php
			}
			?>
		</ul>
	<?php
	}
}
?>