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

                    if ( $tab == pods_var( 'tab', 'get', $default ) ) {
                        $class = ' nav-tab-active';

                        $label = 'Pods ' . $label;
                    }

                    $url = pods_var_update( array( 'tab' => $tab ), array( 'page' ) );
            ?>
                <a href="<?php echo $url; ?>" class="nav-tab<?php echo $class; ?>">
                    <?php echo $label; ?>
                </a>
            <?php
                }
            ?>
        </h2>
        <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

        <?php
            $tab = pods_var( 'tab', 'get', $default );
            $tab = sanitize_title( $tab );

            echo pods_view( PODS_DIR . 'ui/admin/settings-' . $tab . '.php' );
        ?>
    </form>
</div>
