<?php
    global $pods_init;

    if ( isset( $_POST[ 'cleanup' ] ) ) {
        require_once( PODS_DIR . 'sql/PodsUpgrade.php' );

        $upgrade = new PodsUpgrade_2_0();
        $upgrade->cleanup();

        pods_redirect( pods_var_update( array( 'pods_cleanup' => 1 ), array( 'page', 'tab' ) ) );
    }
    elseif ( isset( $_POST[ 'reset' ] ) ) {
        $pods_init->reset();
        $pods_init->setup();

        pods_redirect( pods_var_update( array( 'pods_reset' => 1 ), array( 'page', 'tab' ) ) );
    }
    elseif ( isset( $_POST[ 'reset_deactivate' ] ) ) {
        $pods_init->reset();

        deactivate_plugins( PODS_DIR . 'init.php' );

        pods_redirect( 'index.php' );
    }
    elseif ( 1 == pods_var( 'pods_reset' ) )
        pods_ui_message( 'Pods 2.0 settings and data have been reset.' );
    elseif ( 1 == pods_var( 'pods_cleanup' ) )
        pods_ui_message( 'Pods 1.x data has been deleted.' );

    $old_version = get_option( 'pods_version' );

    if ( !empty( $old_version ) ) {
?>
    <h3><?php _e( 'Delete Pods 1.x data', 'pods' ); ?></h3>

    <p><?php _e( 'This will delete all of your Pods 1.x data, it\'s only recommended if you\'ve verified your data has been properly migrated into Pods 2.0.', 'pods' ); ?></p>

    <p class="submit">
        <?php $confirm = __( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 1.x, resetting it to a clean first install.', 'pods' ); ?>
        <input type="submit" class="button button-primary" name="cleanup" value="<?php esc_attr_e( 'Delete Pods 1.x settings and data', 'pods' ); ?>" onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
    </p>

    <hr />
<?php
    }
?>

<h3><?php _e( 'Reset Pods 2.0', 'pods' ); ?></h3>

<p><?php _e( 'This does not delete any Pods 1.x data, it simply resets the Pods 2.0 settings, removes all of it\'s data, and performs a fresh install.', 'pods' ); ?></p>

<p class="submit">
    <?php $confirm = __( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.0, resetting it to a clean first install.', 'pods' ); ?>
    <input type="submit" class="button button-primary" name="reset" value="<?php esc_attr_e( 'Reset Pods 2.0 settings and data', 'pods' ); ?>" onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
</p>

<hr />

<h3><?php _e( 'Deactivate and Delete Pods 2.0 data', 'pods' ); ?></h3>

<p><?php _e( 'This will delete Pods 2.0 settings, data, and deactivate itself once done. Your database will be as if Pods 2.0 never existed.', 'pods' ); ?></p>

<p class="submit">
    <?php $confirm = __( 'Are you sure you want to do this?\n\nThis is a good time to make sure you have a backup. We are deleting all of the data that surrounds 2.0 with no turning back.', 'pods' ); ?>
    <input type="submit" class="button button-primary" name="reset_deactivate" value=" <?php esc_attr_e( 'Deactivate and Delete Pods 2.0 data', 'pods' ); ?>" onclick="return confirm( '<?php echo esc_js( $confirm ); ?>' );" />
</p>