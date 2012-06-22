<style type="text/css">
    h3.popup-header {
        font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", sans-serif;
        font-weight: normal;
        color: #5a5a5a;
        font-size: 1.8em;
        background: url(<?php echo PODS_URL; ?>/ui/images/icon32.png) top left no-repeat;
        padding: 8px 0 5px 36px;
        margin-top: 0;
    }

    div.pods-section, div.pods-select, div.pods-header {
        padding: 15px 15px 0 15px;
    }

    div.pods-section.hide {
        display: none;
    }

    .pods-section label {
        display: inline-block;
        width: 120px;
        font-weight: bold;
    }

    a#pods_insert_shortcode {
        color: white !important;
    }

    strong.red {
        color: red;
    }
</style>

<script type="text/javascript">
    jQuery( function ( $ ) {
        var $useCaseSelector = $( '#pods-use-case-selector' ),
            $form = $( '#pods_shortcode_form_element' ),
            $podSelector = $( '#pod_select' ),
            pods_ajaxurl = "<?php echo admin_url( 'admin-ajax.php?pods_ajax=1' ); ?>",
            nonce = "<?php echo wp_create_nonce( 'pods-load_pod' ); ?>";

        $useCaseSelector.change( function ( evt ) {
            var val = $( this ).val();

            $( '.section' ).addClass( 'hide' );

            switch ( val ) {
                case 'single':
                    $( '#pod_select, #pod_slug, #pod_template, #pod_helper, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'list':
                    $( '#pod_select, #pod_orderby, #pod_sort_direction, #pod_limit, #pod_template, #pod_helper, #pod_where, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'column':
                    $( '#pod_select, #pod_slug, #pod_helper, #pod_column, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'form':
                    $( '#pod_select, #pod_slug, #pod_columns, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
            }
        } );

        $( '#pod_select' ).change( function () {
            var pod = $( this ).val();
            var jax = $.ajax( pods_ajaxurl, {
                type : 'POST',
                dataType : 'json',
                data : {
                    action : 'pods_admin',
                    method : 'load_pod',
                    name : pod,
                    _wpnonce : nonce
                }
            } );
            jax.success( function ( json ) {
                var $orderby = $( '#pod_orderby' ),
                    $column = $( '#pod_column' );

                $orderby.find( 'option' ).remove();
                $orderby.append( '<option value=""></option>' );

                $column.find( 'option' ).remove();
                $column.append( '<option value=""></option>' );

                $.each( json.fields, function ( key, val ) {
                    $orderby.append( '<option value="' + val.name + '">' + val.label + '</option>' );
                    $column.append( '<option value="' + val.name + '">' + val.label + '</option>' );
                } );
            } );
        } );

        $( '#pod_select' ).trigger( 'change' );
    } );
</script>


<div id="pods_shortcode_form" style="display: none;">
    <div class="wrap">
        <div>
            <div class="pods-header">
                <h3 class="popup-header">Pods &raquo; Embed</h3>
            </div>

            <form id="pods_shortcode_form_element">
                <div class="pods-select">
                    <label for="pods-use-case-selector">What would you like to do?</label>
                    <select id="pods-use-case-selector">
                        <option value="">---</option>
                        <option value="single">Display a single Pod item</option>
                        <option value="list">List multiple Pod items</option>
                        <option value="column">Display a column from a single Pod item</option>
                    </select>
                </div>
                <div class="pods-section hide">
                    <?php
                        $api = new PodsAPI();
                        $all_pods = $api->load_pods( array(
                            'orderby' => 'name ASC',
                        ) );
                        $pod_count = count( $all_pods );
                    ?>
                    <label for="pod_select">Choose a Pod</label>
                    <?php if ( $pod_count > 0 ) { ?>
                        <select id="pod_select" name="pod_select">
                            <?php foreach ( $all_pods as $pod => $data ) { ?>
                                <option value="<?php echo $pod; ?>">
                                    <?php echo $pod; ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <strong class="red" id="pod_select">None Found</strong>
                    <?php } ?>
                </div>
                <div class="pods-section hide">
                    <?php
                        $templates = $api->load_templates( array(
                            'orderby' => 'name ASC',
                        ) );
                        $template_count = count( $templates );
                    ?>
                    <label for="pod_template">Template</label>
                    <select id="pod_template" name="pod_template">
                        <option value="">- Custom Template -</option>
                        <?php foreach ( $templates as $tmpl => $data ) { ?>
                            <option value="<?php echo $tmpl; ?>">
                                <?php echo $tmpl; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="pods-section hide">
                    <label for="pod_slug">ID or Slug</label> <input type="text" id="pod_slug" name="pod_slug" />
                </div>
                <div class="pods-section hide">
                    <label for="pod_limit">Limit</label> <input type="text" id="pod_limit" name="pod_limit" />
                </div>
                <div class="pods-section hide">
                    <label for="pod_orderby">Order By</label> <select name="pod_orderby" id="pod_orderby"> </select>
                </div>
                <div class="pods-section hide">
                    <label for="pod_sort_direction">Order Direction</label>
                    <select id="pod_sort_direction" name="pod_sort_direction">
                        <option value="ASC">
                            Ascending
                        </option>
                        <option value="DESC">
                            Descending
                        </option>
                    </select>
                </div>
                <div class="pods-section hide">
                    <label for="pod_where">Where</label> <input type="text" name="pod_where" id="pod_where" />
                </div>
                <div class="pods-section hide">
                    <label for="pod_column">Column</label> <select id="pod_column" name="pod_column"> </select>
                </div>
                <div class="pods-section hide">
                    <?php
                        $helpers = $api->load_helpers( array(
                            'orderby' => 'name ASC',
                        ) );
                        $helper_count = count( $helpers );
                    ?>
                    <label for="pod_helper">Helper</label>
                    <select id="pod_helper" name="pod_helper">
                        <option value="">- Helper -</option>
                        <?php foreach ( $helpers as $helper => $data ) { ?>
                            <option value="<?php echo $helper; ?>">
                                <?php echo $helper; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="pods-section hide" style="text-align: right;">
                    <a class="button-primary" id="pods_insert_shortcode" href="#">Insert</a>
                </div>
            </form>
        </div>
    </div>
</div>