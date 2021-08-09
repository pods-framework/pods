<div class="wrap pods-admin pods-admin--flex">
	<div class="pods-admin__content-container">
		<div id="icon-pods" class="icon32"><br /></div>
		<h2><?php _e( 'Pods Help', 'pods' ); ?></h2>
		<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

		<h3><?php _e( 'Pods Support' , 'pods'); ?></h3>

		<p><?php _e( 'There are many resources available to help you learn to use Pods on our site, <a href="https://pods.io/" target="_blank" rel="noopener noreferrer">Pods.io</a>:', 'pods'); ?></p>
		<ul class="ul-disc">
			<li><?php _e('To learn more about using Pods, see our <a href="https://pods.io/docs/" target="_blank" rel="noopener noreferrer">documentation</a> and subscribe to our <a href="https://www.youtube.com/user/podsframework/" target="_blank" rel="noopener noreferrer">YouTube Channel</a>', 'pods'); ?>.

			<li><?php _e( 'To get help with a specific issue, you can ask in our <a href="https://wordpress.org/support/plugin/pods" target="_blank" rel="noopener noreferrer">support forums</a>, or you can join <a href="https://pods.io/chat/" target="_blank" rel="noopener noreferrer">our Slack chat</a>', 'pods'); ?>.

			<li><?php _e('To report <strong>bugs or request features</strong>, go to our <a href="https://github.com/pods-framework/pods/issues?sort=updated&direction=desc&state=open" target="_blank" rel="noopener noreferrer">GitHub</a>.', 'pods' ); ?></li>

			<li><?php _e( "Pods is open source, so if you want to get into the code and submit your own fixes or features, go at it, we'd love to have you contribute on our project! With GitHub, it's really easy to contribute back, so why not give it a try?", 'pods'); ?></li>
		</ul>

		<hr />

		<?php
			include_once( ABSPATH . WPINC . '/feed.php' );

			$feed = fetch_feed( 'https://wordpress.org/support/plugin/pods/feed/' );

			if ( !is_wp_error( $feed ) ) {
				$max_items = $feed->get_item_quantity( 10 );
				$rss_items = $feed->get_items( 0, $max_items );

				if ( 0 < $max_items ) {
		?>
			<h3><?php _e( 'Latest Forum Posts at <a href="https://wordpress.org/support/plugin/pods" target="_blank" rel="noopener noreferrer">wordpress.org</a>', 'pods'); ?></h3>

			<ul class="ul-disc">
				<?php
					foreach ( $rss_items as $item ) {
						$authors = $item->get_authors();

						$author_text = '';

						foreach ( $authors as $author ) {
							$author_text = '<br /> by ';

							if ( !empty( $author->link ) )
								$author_text .= '<a href="' . $author->link . '" target="_blank" rel="noopener noreferrer">';

							$author_text .= $author->name;

							if ( !empty( $author->link ) )
								$author_text .= '</a>';
						}
				?>
					<li>
						<a href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo html_entity_decode( esc_html( $item->get_title() ) ); ?></a>
						<?php echo $author_text; ?>
						<br />
						on <?php echo $item->get_date( 'm/d/Y' ); ?>
					</li>
				<?php
					}
				?>
			</ul>

			<hr />
		<?php
				}
			}

			$feed = fetch_feed( 'https://github.com/pods-framework/pods/commits/2.x.atom' );

			if ( !is_wp_error( $feed ) ) {
				// Silence warnings due to a bug in SimplePie's date parser with PHP 7.1+
				// See: https://github.com/pods-framework/pods/issues/4363
				$max_items = @$feed->get_item_quantity( 10 );
				$rss_items = $feed->get_items( 0, $max_items );

				if ( 0 < $max_items ) {
		?>
			<h3><?php _e( 'Latest Activity on <a href="http://github.com/pods-framework/pods" target="_blank" rel="noopener noreferrer">GitHub</a>', 'pods' ); ?></h3>

			<ul class="ul-disc">
				<?php
					foreach ( $rss_items as $item ) {
						$authors = $item->get_authors();

						$author_text = '';

						foreach ( $authors as $author ) {
							$author_text = '<br /> by ';

							if ( !empty( $author->link ) )
								$author_text .= '<a href="' . $author->link . '" target="_blank" rel="noopener noreferrer">';

							$author_text .= $author->name;

							if ( !empty( $author->link ) )
								$author_text .= '</a>';
						}
				?>
					<li>
						<a href="<?php echo esc_url( $item->get_permalink() ); ?>">Commit</a>: <?php echo esc_html( $item->get_title() ); ?>
						<?php echo $author_text; ?>
						<br />
						on <?php echo $item->get_date( 'm/d/Y' ); ?>
					</li>
				<?php
					}
				?>
			</ul>
		<?php
				}
			}
		?>
	</div>

	<?php
	/**
	 * Allow additional output after the container area of the Pods help screen.
	 *
	 * @since 2.7.17
	 */
	do_action( 'pods_admin_after_help' );
	?>
</div>
