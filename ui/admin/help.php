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
    <div id="icon-pods" class="icon32"><br /></div>
    <h2><?php _e( 'Pods Help', 'pods' ); ?></h2>
    <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

    <p>
        This plugin is an <strong>beta</strong> version, it is not meant for use on production/live sites and is not 100% complete with functionality yet.
        This plugin has been released for developers and users so they can help test, review the new UI, and provide feedback regarding Pods 2.0 and let us know of any bugs they come across.
    </p>
    <p>To report bugs or request features, go to our <a href="https://github.com/pods-framework/pods/issues?milestone=1&sort=created&direction=desc&state=open" target="_blank">GitHub</a>.</p>
    <p>It's open source, so if you want to get into the code and submit your own fixes or features, go at it, we'd love to have you contribute on our project! With GitHub, it's really easy to contribute back, so why not give it a try?</p>

</div>