<div class="wrap pods-admin">
    <div id="icon-pods" class="icon32"><br /></div>
    <h2><?php _e( 'Pods Help', 'pods' ); ?></h2>
    <img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

    <h3><?php _e( 'Pods Support' , 'pods'); ?></h3>

    <p><?php _e( 'There are many resources available to help you learn to use Pods on our site, <a href="http://pods.io/" target="_blank">Pods.io</a>:', 'pods'); ?></p>
    <ul class="ul-disc">
        <li><?php _e('To learn more about using Pods, see the <a href="http://pods.io/docs/" target="_blank">documentation</a>, or our <a href="http://pods.io/tutorials/" target="_blank">tutorials section</a>', 'pods'); ?>.

        <li><?php _e( 'To get help with a specific issue, you can ask in our <a href="http://pods.io/forums/" target="_blank">support forums</a>, or in <a href="http://pods.io/forums/chat/" target="_blank">our chat</a>', 'pods'); ?>.

        <li><?php _e('To report <strong>bugs or request features</strong>, go to our <a href="https://github.com/pods-framework/pods/issues?sort=updated&direction=desc&state=open" target="_blank">GitHub</a>.', 'pods' ); ?></li>

        <li><?php _e( "Pods is open source, so if you want to get into the code and submit your own fixes or features, go at it, we'd love to have you contribute on our project! With GitHub, it's really easy to contribute back, so why not give it a try?", 'pods'); ?></li>
    </ul>

    <hr />

    <?php
        include_once( ABSPATH . WPINC . '/feed.php' );

        $feed = fetch_feed( 'http://pods.io/forums/forum/pods-2-x/feed/' );

        if ( !is_wp_error( $feed ) ) {
            $max_items = $feed->get_item_quantity( 6 );
            $rss_items = $feed->get_items( 0, $max_items );

            if ( 0 < $max_items ) {
    ?>
        <h3><?php _e( 'Latest Forum Posts at <a href="http://pods.io/forums/forum/general-discussion/pods-2-x/" target="_blank">pods.io</a>', 'pods'); ?></h3>

        <ul class="ul-disc">
            <?php
                foreach ( $rss_items as $item ) {
                    $authors = $item->get_authors();

                    $author_text = '';

                    foreach ( $authors as $author ) {
                        $author_text = '<br /> by ';

                        if ( !empty( $author->link ) )
                            $author_text .= '<a href="' . $author->link . '" target="_blank">';

                        $author_text .= $author->name;

                        if ( !empty( $author->link ) )
                            $author_text .= '</a>';
                    }
            ?>
                <li>
                    <a href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo esc_html( $item->get_title() ); ?></a>
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

        $feed = fetch_feed( 'https://github.com/pods-framework/pods/commits/' . PODS_GITHUB_BRANCH . '.atom' );

        if ( !is_wp_error( $feed ) ) {
            $max_items = $feed->get_item_quantity( 6 );
            $rss_items = $feed->get_items( 0, $max_items );

            if ( 0 < $max_items ) {
    ?>
        <h3><?php _e( 'Latest Activity on <a href="http://github.com/pods-framework/pods" target="_blank">GitHub</a>', 'pods' ); ?></h3>

        <ul class="ul-disc">
            <?php
                foreach ( $rss_items as $item ) {
                    $authors = $item->get_authors();

                    $author_text = '';

                    foreach ( $authors as $author ) {
                        $author_text = '<br /> by ';

                        if ( !empty( $author->link ) )
                            $author_text .= '<a href="' . $author->link . '" target="_blank">';

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
