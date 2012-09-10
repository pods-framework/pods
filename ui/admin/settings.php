<?php
if ( !empty( $_POST ) ) {
    if ( isset( $_POST[ 'clearcache' ] ) ) {
        pods_api()->cache_flush_pods();

        die( '<script type="text/javascript">document.location = "?page=' . urlencode( $_GET[ 'page' ] ) . '&pods_clearcache=1";</script>' );
    }
    elseif ( isset( $_POST[ 'reset' ] ) ) {
        global $pods_init;

        $pods_init->reset();
        $pods_init->setup();

        die( '<script type="text/javascript">document.location = "?page=' . urlencode( $_GET[ 'page' ] ) . '&pods_reset=1";</script>' );
    }
    elseif ( isset( $_POST[ 'reset_deactivate' ] ) ) {
        global $pods_init;

        $pods_init->reset();
        deactivate_plugins( PODS_DIR . 'pods.php' );

        die( '<script type="text/javascript">document.location = "index.php";</script>' );
    }
    elseif ( isset( $_POST[ 'cleanup' ] ) ) {
        require_once( PODS_DIR . 'sql/PodsUpgrade.php' );

        $upgrade = new PodsUpgrade_2_0();
        $upgrade->cleanup();

        die( '<script type="text/javascript">document.location = "?page=' . urlencode( $_GET[ 'page' ] ) . '&pods_cleanup=1";</script>' );
    }
}

if ( isset( $_GET[ 'pods_reset' ] ) )
    pods_ui_message( 'Pods 2.0 settings and data have been reset.' );
elseif ( isset( $_GET[ 'pods_cleanup' ] ) )
    pods_ui_message( 'Pods 1.x data has been deleted.' );
elseif ( isset( $_GET[ 'pods_clearcache' ] ) )
    pods_ui_message( 'Pods 2.0 transients and cache have been cleared.' );
?>

<div class="wrap pods-admin">
    <?php if ( defined( 'PODS_DEVELOPER' ) && PODS_DEVELOPER ) { ?>
    <div id="icon-pods" class="icon32"><br /></div>
    <h2><?php _e( 'Pods Settings', 'pods' ); ?></h2>
    <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

    <p><?php _e( 'The following are settings provided for advanced site configurations.', 'pods' ); ?></p>

    <hr />
    <?php } ?>

    <form action="" method="post">

        <div id="icon-pods" class="icon32"><br /></div>
        <h2><?php _e( 'Pods Developer Tools', 'pods' ); ?></h2>

        <p><?php _e( 'The tools below are for advanced users or users who have been directed to use them. Use at your own risk, and backup your database whenever possible.', 'pods' ); ?></p>

        <hr />

        <h3><?php _e( 'Force an update of this beta from GitHub', 'pods' ); ?></h3>

        <p><?php _e( 'This tool lets you update your Pods 2.0 beta installation to the latest, usually only when you\'ve been instructed to do so.', 'pods' ); ?></p>

        <?php
        $update = admin_url( 'update-core.php?pods_force_refresh=1' );

        if ( is_multisite() )
            $update = network_admin_url( 'update-core.php?pods_force_refresh=1' );
        ?>

        <p class="submit">
            <a href="<?php echo $update; ?>" class="button button-primary"><?php esc_html_e( 'Force Plugin Refresh/Update from GitHub', 'pods' ); ?></a>
        </p>

        <hr />

        <h3><?php _e( 'Clear Pods 2.0 Cache', 'pods' ); ?></h3>

        <p><?php _e( 'This tool will clear all of the transients/cache that are used by Pods 2.0. ', 'pods' ); ?></p>

        <p class="submit">
            <input type="submit" class="button button-primary" name="clearcache" value="<?php esc_attr_e( 'Clear Pods 2.0 Cache', 'pods' ); ?>" />
        </p>

        <hr />

        <h3><?php _e( 'Reset Pods 2.0', 'pods' ); ?></h3>

        <p><?php _e( 'This tool does not delete any Pods 1.x data, it simply resets the Pods 2.0 settings, removes all of it\'s data, and performs a fresh install.', 'pods' ); ?></p>

        <p class="submit">
            <?php $confirm = __( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.0, resetting it to a clean first install.', 'pods' ); ?>
            <input type="submit" class="button button-primary" name="reset" value="<?php esc_attr_e( 'Reset Pods 2.0 settings and data', 'pods' ); ?>" onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
        </p>

        <?php
        $old_version = get_option( 'pods_version' );

        if ( !empty( $old_version ) ) {
            ?>
            <hr />

            <h3><?php _e( 'Delete Pods 1.x data', 'pods' ); ?></h3>
            <p><?php _e( 'This tool will delete all of your Pods 1.x data, it\'s only recommended if you\'ve verified your data has been properly migrated into Pods 2.0.', 'pods' ); ?></p>

            <p class="submit">
                <?php $confirm = __( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 1.x, resetting it to a clean first install.', 'pods' ); ?>
                <input type="submit" class="button button-primary" name="cleanup" value="<?php esc_attr_e( 'Delete Pods 1.x settings and data', 'pods' ); ?>" onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
            </p>
            <?php
        }
        ?>

        <hr />

        <h3><?php _e( 'Deactivate and Delete Pods 2.0 data', 'pods' ); ?></h3>

        <p><?php _e( 'This tool will delete Pods 2.0 settings, data, and deactivate itself once done. Your database will be as if Pods 2.0 never existed.', 'pods' ); ?></p>

        <p class="submit">
            <?php $confirm = __( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.0 with no turning back.', 'pods' ); ?>
            <input type="submit" class="button button-primary" name="reset_deactivate" value=" <?php esc_attr_e( 'Deactivate and Delete Pods 2.0 data', 'pods' ); ?>" onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
        </p>

        <hr />

        <h3><?php _e( 'Debug Information', 'pods' ); ?></h3>

        <?php
        global $wp_version, $wpdb;

        $wp = $wp_version;
        $php = phpversion();
        $mysql = $wpdb->db_version();
        $plugins = array();

        $all_plugins = get_plugins();

        foreach ( $all_plugins as $plugin_file => $plugin_data ) {
            if ( is_plugin_active( $plugin_file ) )
                $plugins[ $plugin_data[ 'Name' ] ] = $plugin_data[ 'Version' ];
        }

        $versions = array(
            'WordPress Version' => $wp,
            'PHP Version' => $php,
            'MySQL Version' => $mysql,
            'Server Software' => $_SERVER[ 'SERVER_SOFTWARE' ],
            'Your User Agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
            'Currently Active Plugins' => $plugins
        );

        foreach ( $versions as $what => $version ) {
            echo '<p><strong>' . $what . '</strong>: ';

            if ( is_array( $version ) ) {
                echo '</p><ul class="ul-disc">';

                foreach ( $version as $what_v => $v ) {
                    echo '<li><strong>' . $what_v . '</strong>: ' . $v . '</li>';
                }

                echo '</ul>';
            }
            else
                echo $version . '</p>';
        }
        ?>
    </form>
</div>
