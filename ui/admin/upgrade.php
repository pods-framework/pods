<?php
    global $wpdb;
?>
<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo PODS_URL; ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <h2 class="italicized"><?php _e( 'Upgrade Pods', 'pods' ); ?></h2>

    <div id="pods-wizard-box" class="pods-wizard-steps-3">
        <div id="pods-wizard-heading">
            <ul>
                <li class="pods-wizard-menu-current"><i></i><span>1</span> <?php _e( 'Getting Started', 'pods' ); ?><em></em></li>
                <li><i></i><span>2</span> <?php _e( 'Prepare', 'pods' ); ?><em></em></li>
                <li><i></i><span>3</span> <?php _e( 'Migrate', 'pods' ); ?></li>
            </ul>
        </div>
        <div id="pods-wizard-main">

            <!-- Getting Started Panel -->
            <div id="pods-wizard-panel-1" class="pods-wizard-panel">
                <div class="pods-wizard-content pods-wizard-grey">
                    <p><?php _e( '', 'pods' ); ?></p>
                </div>
                <p><em><?php _e( '', 'pods' ); ?></em></p>
                <div id="pods-wizard-options">
                    <div class="pods-wizard-option">
                        <a href="#">
                            <img src="<?php echo PODS_URL; ?>/ui/images/logo_vaultpress.png" alt="Vaultpress" />
                            <p><?php _e( '1 free month', 'pods' ); ?></p>
                            <p><?php _e( 'Coupon Code', 'pods' ); ?>: <strong>ABC123</strong></p>
                        </a>
                        <p><em><?php _e( 'A service that provides realtime continuous backups, restores, and security scanning.', 'pods' ); ?></em></p>
                    </div>
                    <div class="pods-wizard-option">
                        <a href="#">
                            <img src="<?php echo PODS_URL; ?>/ui/images/logo_backupbuddy.png" alt="Backup Buddy" />
                            <p><?php _e( 'Receive 25% off', 'pods' ); ?></p>
                            <p><?php _e( 'Coupon Code', 'pods' ); ?>: <strong>ABC123</strong></p>
                        </a>
                        <p><em><?php _e( 'The all-in-one WordPress backup plugin to easily backup, restore, and migrate to any number of local or external locations.', 'pods' ); ?></em></p>
                    </div>
                </div>
            </div>
            <!-- // Getting Started Panel -->

            <!-- Prepare Panel -->
            <div id="pods-wizard-panel-2" class="pods-wizard-panel">
                <div class="pods-wizard-content">
                    <p><?php _e( '', 'pods' ); ?></p>
                </div>
                <table cellpadding="0" cellspacing="0">
                    <col style="width: 70px">
                    <col style="width: 110px">
                    <col style="width: 580px">
                    <thead>
                        <tr>
                            <th colspan="3"><?php _e( 'Your Content', 'pods' ); ?></th>
                        </tr>
                    </thead>
                    <tbody><!-- complete|pending|active <i></i> -->
                        <?php
                            $pods = $wpdb->get_results( "SELECT `name`, `label` FROM `{$wpdb->prefix}pod_types` ORDER BY `name`" );
                        ?>
                        <tr class="pods-wizard-table-complete" data-upgrade="pods">
                            <td class="pods-wizard-right pods-wizard-status">
                                <i><img src="<?php echo PODS_URL; ?>/ui/images/spinner.gif" alt="Loading..." /></i>
                            </td>
                            <td class="pods-wizard-right pods-wizard-count"><?php echo count( $pods ); ?></td>
                            <td class="pods-wizard-name">
                                <?php _e( 'Pods', 'pods' ); ?>
                                <span class="pods-wizard-info"></span>
                            </td>
                        </tr>
                        <tr class="pods-wizard-table-active" data-upgrade="fields">
                            <td class="pods-wizard-right pods-wizard-status">
                                <i><img src="<?php echo PODS_URL; ?>/ui/images/spinner.gif" alt="Loading..." /></i>
                            </td>
                            <td class="pods-wizard-right pods-wizard-count">&mdash;</td>
                            <td class="pods-wizard-name">
                                <?php _e( 'Fields', 'pods' ); ?>
                                <span class="pods-wizard-info"></span>
                            </td>
                        </tr>
                        <tr class="pods-wizard-table-pending" data-upgrade="relationships">
                            <td class="pods-wizard-right pods-wizard-status">
                                <i><img src="<?php echo PODS_URL; ?>/ui/images/spinner.gif" alt="Loading..." /></i>
                            </td>
                            <td class="pods-wizard-right pods-wizard-count">&mdash;</td>
                            <td class="pods-wizard-name">
                                <?php _e( 'Relationships', 'pods' ); ?>
                                <span class="pods-wizard-info"></span>
                            </td>
                        </tr>
                        <tr class="pods-wizard-table-pending" data-upgrade="templates">
                            <td class="pods-wizard-right pods-wizard-status">
                                <i><img src="<?php echo PODS_URL; ?>/ui/images/spinner.gif" alt="Loading..." /></i>
                            </td>
                            <td class="pods-wizard-right pods-wizard-count">&mdash;</td>
                            <td class="pods-wizard-name">
                                <?php _e( 'Templates', 'pods' ); ?>
                                <span class="pods-wizard-info"></span>
                            </td>
                        </tr>
                        <tr class="pods-wizard-table-pending" data-upgrade="pages">
                            <td class="pods-wizard-right pods-wizard-status">
                                <i><img src="<?php echo PODS_URL; ?>/ui/images/spinner.gif" alt="Loading..." /></i>
                            </td>
                            <td class="pods-wizard-right pods-wizard-count">&mdash;</td>
                            <td class="pods-wizard-name">
                                <?php _e( 'Pod Pages', 'pods' ); ?>
                                <span class="pods-wizard-info"></span>
                            </td>
                        </tr>
                        <tr class="pods-wizard-table-pending" data-upgrade="helpers">
                            <td class="pods-wizard-right pods-wizard-status">
                                <i><img src="<?php echo PODS_URL; ?>/ui/images/spinner.gif" alt="Loading..." /></i>
                            </td>
                            <td class="pods-wizard-right pods-wizard-count">&mdash;</td>
                            <td class="pods-wizard-name">
                                <?php _e( 'Helpers', 'pods' ); ?>
                                <span class="pods-wizard-info"></span>
                            </td>
                        </tr>
                        <?php
                            foreach ( $pods as $pod ) {
                        ?>
                            <tr class="pods-wizard-table-pending" data-upgrade="pod" data-pod="<?php echo $pod->name; ?>">
                                <td class="pods-wizard-right pods-wizard-status">
                                    <img src="<?php echo PODS_URL; ?>/ui/images/spinner.gif" alt="Loading..." />
                                </td>
                                <td class="pods-wizard-right pods-wizard-count">&mdash;</td>
                                <td class="pods-wizard-name">
                                    <?php
                                        echo __( 'Content', 'pods' ) . ': ' . $pod->name;

                                        if ( 0 < strlen( $pod->label ) )
                                            echo '<br /><em>' . $pod->label . '</em>';
                                    ?>
                                    <span class="pods-wizard-info"></span>
                                </td>
                            </tr>
                        <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- // Prepare Panel -->

            <!-- Migrate Panel -->
            <div id="pods-wizard-panel-3" class="pods-wizard-panel">
                <div class="pods-wizard-content">
                    <p><?php _e( '', 'pods' ); ?></p>
                </div>
                <table cellpadding="0" cellspacing="0" width="100%">
                    <col style="width: 70px">
                    <col style="width: 110px">
                    <col style="width: 580px">
                    <thead>
                        <tr>
                            <th colspan="3">Your Content</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="pods-wizard-table-complete">
                            <td class="pods-wizard-right"><i></i></td>
                            <td class="pods-wizard-right">4</td>
                            <td class="pods-wizard-name">Pods</td>
                            <td class="pods-wizard-info"></td>
                        </tr>
                        <tr class="pods-wizard-table-warning">
                            <td class="pods-wizard-right"><i></i></td>
                            <td class="pods-wizard-right">158</td>
                            <td class="pods-wizard-name">Content: Author</td>
                            <td class="pods-wizard-info">
                                Birthday field has been manually changed in the database,
                                you will need to manually modify it again after migration.
                            </td>
                        </tr>
                        <tr class="pods-wizard-table-error">
                            <td class="pods-wizard-right"><i></i></td>
                            <td class="pods-wizard-right">0</td>
                            <td class="pods-wizard-name">Content: Bunny</td>
                            <td class="pods-wizard-info">Table not found</td>
                        </tr>
                        <tr class="pods-wizard-table-complete">
                            <td class="pods-wizard-right"><i></i></td>
                            <td class="pods-wizard-right">552</td>
                            <td class="pods-wizard-name"><?php _e( '', 'pods' ); ?></td>
                            <td class="pods-wizard-info"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- // Mirate Panel -->

        </div>
        <div id="pods-wizard-actions">
            <div id="pods-wizard-toolbar">
                <a href="#start" id="pods-wizard-start" class="button button-secondary">Start Over</a>
                <a href="#next" id="pods-wizard-next" class="button button-primary">Next Step</a>
            </div>
            <div id="pods-wizard-finished">
                MIGRATION COMPLETE
            </div>
        </div>
    </div>
</div>

<script>
    jQuery( function ( $ ) {
        $( '#pods-wizard-box' ).Pods( 'wizard' );
    } );
</script>