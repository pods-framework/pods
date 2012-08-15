<?php
    if ( !empty( $_POST ) ) {
        if ( isset( $_POST[ 'reset' ] ) ) {
            global $pods_init;

            $pods_init->reset();
            $pods_init->setup();

            die( '<script type="text/javascript">document.location = "?page=' . urlencode( $_GET[ 'page' ] ) . '&reset=1";</script>' );
        }
        elseif ( isset( $_POST[ 'reset_deactivate' ] ) ) {
            global $pods_init;

            $pods_init->reset();
            deactivate_plugins( PODS_DIR . '/pods.php' );

            die( '<script type="text/javascript">document.location = "index.php";</script>' );
        }
        elseif ( isset( $_POST[ 'cleanup' ] ) ) {
            require_once( PODS_DIR . 'sql/PodsUpgrade.php' );

            $upgrade = new PodsUpgrade_2_0();
            $upgrade->cleanup();

            die( '<script type="text/javascript">document.location = "?page=' . urlencode( $_GET[ 'page' ] ) . '&cleanup=1";</script>' );
        }
    }

    if ( isset( $_GET[ 'reset' ] ) )
        pods_ui_message( 'Pods 2.0 settings and data have been reset.' );
    elseif ( isset( $_GET[ 'cleanup' ] ) )
        pods_ui_message( 'Pods 1.x data has been deleted.' );
?>

<div class="wrap pods-admin">
    <div id="icon-pods" class="icon32"><br /></div>
    <h2><?php _e( 'Pods Help', 'pods' ); ?></h2>

    <p>
        This plugin is an <strong>beta</strong> version, it is not meant for use on production/live sites and is not 100% complete with functionality yet.
        This plugin has been released for developers and users so they can help test, review the new UI, and provide feedback regarding Pods 2.0 and let us know of any bugs they come across.
    </p>
    <p>To report bugs or request features, go to our <a href="https://github.com/pods-framework/pods/issues?milestone=1&sort=created&direction=desc&state=open" target="_blank">GitHub</a>.</p>
    <p>It's open source, so if you want to get into the code and submit your own fixes or features, go at it, we'd love to have you contribute on our project! With GitHub, it's really easy to contribute back, so why not give it a try?</p>

    <hr />

    <h2><?php _e( 'Pods Developer Tools', 'pods' ); ?></h2>

    <p>These items below will exist here only until our new Settings area is up and going.</p>

    <h3>Force an update of this beta from GitHub</h3>
    <p>This tool lets you update your Pods 2.0 beta installation to the latest, usually only when you've been instructed to do so.</p>

    <?php
        $update = admin_url( 'update-core.php?pods_force_refresh=1' );

        if ( is_multisite() )
            $update = network_admin_url( 'update-core.php?pods_force_refresh=1' );
    ?>

    <p class="submit">
        <a href="<?php echo $update; ?>" class="button button-primary">Force Plugin Refresh/Update from GitHub</a>
    </p>

    <form action="" method="post">
        <h3>Reset Pods 2.0</h3>
        <p>This tool does not delete any Pods 1.x data, it simply resets the Pods 2.0 settings and removes all of the 2.0 data.</p>

        <p class="submit">
            <input type="submit" class="button button-primary" name="reset" value="Reset Pods 2.0 settings and data" onclick="return confirm( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.0, resetting it to a clean first install.' );" />
        </p>

        <?php
            $old_version = get_option( 'pods_version' );

            if ( !empty( $old_version ) ) {
        ?>
            <h3>Delete Pods 1.x data</h3>
            <p>This tool will delete all of your Pods 1.x data, it's only recommended if you've verified your data has been properly migrated into Pods 2.0.</p>

            <p class="submit">
                <input type="submit" class="button button-primary" name="cleanup" value="Delete Pods 1.x settings and data" onclick="return confirm( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 1.x, resetting it to a clean first install.' );" />
            </p>
        <?php
            }
        ?>

        <h3>Deactivate and Delete Pods 2.0 Data</h3>

        <p>This tool will delete Pods 2.0 settings, data, and deactivate itself once done. Your database will be as if Pods 2.0 never existed.</p>

        <p class="submit">
            <input type="submit" class="button button-primary" name="reset_deactivate" value="Deactivate and Delete Pods 2.0 Data" onclick="return confirm( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.0 with no turning back.' );" />
        </p>
    </form>

</div>