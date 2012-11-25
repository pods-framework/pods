<?php
wp_enqueue_script( 'pods' );
wp_enqueue_style( 'pods-form' );

if ( empty( $fields ) || !is_array( $fields ) )
    $fields = $obj->pod->fields;

if ( !isset( $duplicate ) )
    $duplicate = false;
else
    $duplicate = (boolean) $duplicate;

// unset fields
foreach ( $fields as $k => $field ) {
    if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) )
        unset( $fields[ $k ] );
    elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field['options'], $fields, $pod, $pod->id() ) )
        unset( $fields[ $k ] );
}

if ( !isset( $thank_you_alt ) )
    $thank_you_alt = $thank_you;

$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $fields ) ) );

$uid = @session_id();

if ( is_user_logged_in() )
    $uid = 'user_' . get_current_user_id();

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . ( $duplicate ? 0 : $pod->id() ) . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST[ '_pods_nonce' ] ) ) {
    $action = __( 'saved', 'pods' );

    if ( 'create' == pods_var_raw( 'do', 'post', 'save' ) )
        $action = __( 'created', 'pods' );
    elseif ( 'duplicate' == pods_var_raw( 'do', 'get', 'save' ) )
        $action = __( 'duplicated', 'pods' );

    try {
        $params = stripslashes_deep( (array) $_POST );
        $id = $pod->api->process_form( $params, $pod, $fields, $thank_you );

        $message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );
        $error = sprintf( __( '<strong>Error:</strong> %s %s successfully.', 'pods' ), $obj->item, $action );

        if ( 0 < $id )
            echo $obj->message( $message );
        else
            echo $obj->error( $error );
    }
    catch ( Exception $e ) {
        echo $obj->error( $e->getMessage() );
    }
}
elseif ( isset( $_GET[ 'do' ] ) ) {
    $action = __( 'saved', 'pods' );

    if ( 'create' == pods_var_raw( 'do', 'get', 'save' ) )
        $action = __( 'created', 'pods' );
    elseif ( 'duplicate' == pods_var_raw( 'do', 'get', 'save' ) )
        $action = __( 'duplicated', 'pods' );

    $message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );
    $error = sprintf( __( '<strong>Error:</strong> %s not %s.', 'pods' ), $obj->item, $action );

    if ( 0 < $pod->id() )
        echo $obj->message( $message );
    else
        echo $obj->error( $error );
}

if ( !isset( $label ) )
    $label = _e( 'Save', 'pods' );

$do = 'create';

if ( 0 < $pod->id() ) {
    if ( $duplicate )
        $do = 'duplicate';
    else
        $do = 'save';
}
?>

<form action="" method="post" class="pods-submittable pods-form pods-form-pod-<?php echo $pod->pod; ?>">
    <div class="pods-submittable-fields">
        <?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
        <?php echo PodsForm::field( 'method', 'process_form', 'hidden' ); ?>
        <?php echo PodsForm::field( 'do', $do, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_id', ( $duplicate ? 0 : $pod->id() ), 'hidden' ); ?>
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
                                            if ( ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods_delete_' . $pod->pod ) ) && null !== $pod->id() && !$duplicate && !in_array( 'delete', $obj->actions_disabled ) && !in_array( 'delete', $obj->actions_hidden ) ) {
                                        ?>
                                            <div id="delete-action">
                                                <a class="submitdelete deletion" href="<?php echo pods_var_update( array( 'action' => 'delete' ) ) ?>" onclick="return confirm('You are about to permanently delete this item\n Choose \'Cancel\' to stop, \'OK\' to delete.');"><?php _e( 'Delete', 'pods' ); ?></a>
                                            </div>
                                            <!-- /#delete-action -->
                                        <?php } ?>

                                        <div id="publishing-action">
                                            <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                                            <input type="submit" name="publish" id="publish" class="button-primary" value="<?php echo esc_attr( $label ); ?>" accesskey="p" />
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
                    <?php
                        if ( pods_var_raw( 'action' ) == 'edit' && !$duplicate && !in_array( 'navigate', $obj->actions_disabled ) && !in_array( 'navigate', $obj->actions_hidden ) ) {
                            if ( !isset( $singular_label ) )
                                $singular_label = ucwords( str_replace( '_', ' ', $pod->pod_data[ 'name' ] ) );

                            $singular_label = pods_var_raw( 'label', $pod->pod_data[ 'options' ], $singular_label, null, true );
                            $singular_label = pods_var_raw( 'label_singular', $pod->pod_data[ 'options' ], $singular_label, null, true );

                            $prev = $pod->prev_id();
                            $next = $pod->next_id();

                            if ( 0 < $prev || 0 < $next ) {
                    ?>
                    <div id="navigatediv" class="postbox">
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        <h3 class="hndle"><span><?php _e( 'Navigate', 'pods' ); ?></span></h3>

                        <div class="inside">
                            <div class="pods-admin" id="navigatebox">
                                <div id="navigation-actions">
                                    <?php
                                        if ( 0 < $prev ) {
                                    ?>
                                        <a class="previous-item" href="<?php echo pods_var_update( array( 'id' => $prev ), null, 'do' ); ?>">
                                            <span>&laquo;</span>
                                            <?php
                                                _e( 'Previous', 'pods' );
                                                echo ' ' . $singular_label;
                                            ?>
                                        </a>
                                    <?php
                                        }

                                        if ( 0 < $next ) {
                                    ?>
                                        <a class="next-item" href="<?php echo pods_var_update( array( 'id' => $next ), null, 'do' ); ?>">
                                            <?php
                                                _e( 'Next', 'pods' );
                                                echo ' ' . $singular_label;
                                            ?>
                                            <span>&raquo;</span>
                                        </a>
                                    <?php
                                        }
                                    ?>

                                    <div class="clear"></div>
                                </div>
                                <!-- /#navigation-actions -->
                            </div>
                            <!-- /#navigatebox -->
                        </div>
                        <!-- /.inside -->
                    </div> <!-- /#navigatediv -->
                    <?php
                            }
                        }
                    ?>
                </div>
                <!-- /#side-sortables -->
            </div>
            <!-- /#side-info-column -->

            <div id="post-body">
                <div id="post-body-content">
                    <?php
                    $more = false;

                    if ( $pod->pod_data[ 'field_index' ] != $pod->pod_data[ 'field_id' ] ) {
                        foreach ( $fields as $k => $field ) {
                            if ( $pod->pod_data[ 'field_index' ] != $field[ 'name' ] || 'text' != $field[ 'type' ] )
                                continue;

                            $more = true;
                            ?>
                            <div id="titlediv">
                                <div id="titlewrap">
                                    <label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php echo apply_filters( 'pods_enter_name_here', __( 'Enter name here', 'pods' ), $pod, $fields ); ?></label>
                                    <input type="text" name="pods_field_<?php echo $pod->pod_data[ 'field_index' ]; ?>" data-name-clean="pods-field-<?php echo $pod->pod_data[ 'field_index' ]; ?>" id="title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $pod->index() ) ); ?>" class="pods-form-ui-field-name-pods-field-<?php echo $pod->pod_data[ 'field_index' ]; ?>" autocomplete="off" />
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
                            <?php
                            unset( $fields[ $k ] );
                        }
                    }
                    ?>

                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <div id="pods-meta-box" class="postbox" style="">
                            <div class="handlediv" title="Click to toggle"><br /></div>
                            <h3 class="hndle">
                                <span>
                                    <?php
                                    if ( $more )
                                        $title = __( 'More Fields', 'pods' );
                                    else
                                        $title = __( 'Fields', 'pods' );

                                    echo apply_filters( 'pods_meta_default_box_title', $title, $pod, $fields );
                                    ?>
                                </span>
                            </h3>

                            <div class="inside">
                                <table class="form-table pods-metabox">
                                    <?php
                                    foreach ( $fields as $field ) {
                                        ?>
                                        <tr class="form-field pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Podsform::clean( $field[ 'name' ], true ); ?>">
                                            <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_field_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?></th>
                                            <td>
                                                <?php echo PodsForm::field( 'pods_field_' . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), $field[ 'type' ], $field, $pod, $pod->id() ); ?>
                                                <?php echo PodsForm::comment( 'pods_field_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                            <!-- /.inside -->
                        </div>
                        <!-- /#pods-meta-box -->
                    </div>
                    <!-- /#normal-sortables -->

                    <!--<div id="advanced-sortables" class="meta-box-sortables ui-sortable">
                    </div>
                     /#advanced-sortables -->

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
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'confirm' );
        $( document ).Pods( 'exit_confirm' );
    } );

    var pods_admin_submit_callback = function ( id ) {
        id = parseInt( id );
        var thank_you = '<?php echo addslashes( $thank_you ); ?>';
        var thank_you_alt = '<?php echo addslashes( $thank_you_alt ); ?>';

        if ( 'NaN' == id )
            document.location = thank_you_alt.replace( 'X_ID_X', 0 );
        else
            document.location = thank_you.replace( 'X_ID_X', id );
    }
</script>
