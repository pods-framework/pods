<div class="wrap pods-admin">
    <form action="" method="post">

        <div id="icon-pods" class="icon32"><br /></div>

        <?php
            $default = 'tools';

            $tabs = array(
                //'settings' => __( 'Settings', 'pods' ),
                'tools' => __( 'Tools', 'pods' ),
                'reset' => __( 'Cleanup &amp; Reset', 'pods' )
            );
        ?>

        <h2 class="nav-tab-wrapper">
            <?php
                foreach ( $tabs as $tab => $label ) {
                    $class = '';

                    if ( $tab == pods_v_sanitized( 'tab', 'get', $default ) ) {
                        $class = ' nav-tab-active';

                        $label = 'Pods ' . $label;
                    }

                    $url = pods_query_arg( array( 'tab' => $tab ), array( 'page' ) );
            ?>
                <a href="<?php echo esc_url( $url ); ?>" class="nav-tab<?php echo esc_attr( $class ); ?>">
                    <?php echo $label; ?>
                </a>
            <?php
                }
            ?>
        </h2>
        <img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

        <?php
            wp_nonce_field( 'pods-settings' );

            $tab = pods_v_sanitized( 'tab', 'get', $default );
            $tab = sanitize_title( $tab );

            echo pods_view( PODS_DIR . 'ui/admin/settings-' . $tab . '.php' );
        ?>
    </form>
</div>
