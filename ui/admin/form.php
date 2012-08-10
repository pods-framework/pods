<?php
    $uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
    $field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $fields ) ) );

    $nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . session_id() . '_' . $pod->id() . '_' . $uri_hash . '_' . $field_hash );

    if ( isset( $_POST[ '_pods_nonce' ] ) ) {
        try {
            $id = $pod->api->process_form( $pod, $fields, $thank_you );
        }
        catch ( Exception $e ) {
            echo '<div class="pods-message pods-message-error">' . $e->getMessage() . '</div>';
        }
    }
?>

<form action="<?php echo pods_var_update( array( '_p_submitted' => 1 ) ); ?>" method="post" class="pods-submittable pods-form pods-form-pod-<?php echo $pod->pod; ?>">
    <div class="pods-submittable-fields">
        <?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_id', $pod->id(), 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $fields ) ), 'hidden' ); ?>

        <div id="poststuff" class="metabox-holder has-right-sidebar"> <!-- class "has-right-sidebar" preps for a sidebar... always present? -->
            <div id="side-info-column" class="inner-sidebar">
                <div id="side-sortables" class="meta-box-sortables ui-sortable">
                    <!-- BEGIN PUBLISH DIV -->
                    <div id="submitdiv" class="postbox">
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        <h3 class="hndle"><span><?php _e( 'Manage', 'pods' ); ?></span></h3>

                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="minor-publishing">
                                    <div id="major-publishing-actions">
                                        <?php
                                            if ( is_super_admin() || current_user_can( 'pods_delete_' . $pod->pod ) ) {
                                        ?>
                                            <div id="delete-action">
                                                <a class="submitdelete deletion" href="#"><?php _e( 'Delete', 'pods' ); ?></a>
                                            </div>
                                            <!-- /#delete-action -->
                                        <?php } ?>

                                        <div id="publishing-action">
                                            <input type="submit" name="publish" id="publish" class="button-primary" value="<?php _e( 'Save' ); ?>" accesskey="p" />
                                        </div>
                                        <!-- /#publishing-action -->

                                        <div class="clear"></div>
                                    </div>
                                    <!-- /#major-publishing-actions -->
                                </div>
                                <!-- /#minor-publishing -->
                            </div>
                            <!-- /#submitpost -->
                        </div>
                        <!-- /.inside -->
                    </div>
                    <!-- /#submitdiv --><!-- END PUBLISH DIV --><!-- TODO: minor column fields -->
                </div>
                <!-- /#side-sortables -->
            </div>
            <!-- /#side-info-column -->

            <div id="post-body">
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label class="hide-if-no-js" style="" id="title-prompt-text" for="title"><?php _e( 'Name', 'pods' ); ?></label>
                            <input type="text" name="pods_field_<?php echo $pod->pod_data[ 'field_index' ]; ?>" data-name-clean="pods-field-<?php echo $pod->pod_data[ 'field_index' ]; ?>" id="title" size="30" tabindex="1" value="<?php echo esc_attr( $pod->index() ); ?>" class="pods-form-ui-field-name-pods-field-<?php echo $pod->pod_data[ 'field_index' ]; ?>" autocomplete="off" />
                        </div>
                        <!-- /#titlewrap -->

                        <div class="inside">
                            <div id="edit-slug-box">
                            </div>
                            <!-- /#edit-slug-box -->
                        </div>
                        <!-- /.inside -->
                    </div>
                    <!-- /#titlediv -->

                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <div id="pods-meta-box" class="postbox" style="">
                            <div class="handlediv" title="Click to toggle"><br /></div>
                            <h3 class="hndle"><span><?php _e( 'More Fields', 'pods' ); ?></span></h3>

                            <div class="inside">
                                <ul class="form-fields">
                                    <?php
                                        foreach ( $fields as $field ) {
                                            if ( $pod->pod_data[ 'field_index' ] == $field[ 'name' ] )
                                                continue;
                                    ?>
                                        <li class="pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Podsform::clean( $field[ 'name' ], true ); ?>">
                                            <?php echo PodsForm::row( 'pods_field_' . $field[ 'name' ], $pod->field( $field[ 'name' ] ), $field[ 'type' ], $field, $pod, $pod->id() ); ?>
                                        </li>
                                    <?php
                                        }
                                    ?>
                                </ul>
                            </div>
                            <!-- /.inside -->
                        </div>
                        <!-- /#pods-meta-box -->
                    </div>
                    <!-- /#normal-sortables -->

                    <div id="advanced-sortables" class="meta-box-sortables ui-sortable">
                    </div>
                    <!-- /#advanced-sortables -->

                </div>
                <!-- /#post-body-content -->

                <br class="clear" />
            </div>
            <!-- /#post-body -->

            <br class="clear" />
        </div>
        <!-- /#poststuff -->
    </div>
</form>
<!-- /#pods-record -->

<script type="text/javascript">
    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
    } );

    pods_admin_submit_callback = function ( id ) {
        document.location = '<?php echo pods_var_update( array( 'action' . $obj->num => 'manage', 'id' . $obj->num => '' ) ); ?>';
    }
</script>