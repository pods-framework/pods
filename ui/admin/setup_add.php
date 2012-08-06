<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo PODS_URL; ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <input type="hidden" name="action" value="pods_admin" />
            <input type="hidden" name="method" value="add_pod" />
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('pods-add_pod'); ?>" />

            <h2 class="italicized"><?php _e('Add New Pod', 'pods'); ?></h2>

            <div id="poststuff" class="pods-wizard">
                <img src="<?php echo PODS_URL; ?>/ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />
                <p><?php _e('Pods are content types that you can customize and define fields for based on your needs. You can choose to create a Custom Post Type, Custom Taxonomy, or a Custom Pod which operate completely seperate from normal WordPress Objects. You can also extend existing content types like WP Objects such as Post Types, Taxonomies, Users, or Comments', 'pods'); ?></p>
                <hr />
                <div class="pods-manage-field pods-dependency">
                    <input type="hidden" name="create_extend" value="" class="pods-wizard-current-step" />
                    <div class="pods-wizard-step" id="pods-wizard-choose">
                        <p class="submit">
                            <a class="button-primary" href="#pods-wizard-create" title="Create a New Content Type"><?php _e( 'Create a New Content Type', 'pods' ); ?></a>
                            &nbsp;&nbsp;&nbsp;
                            <a class="button-secondary" href="#pods-wizard-extend" title="Extend an Existing Content Type"><?php _e( 'Extend an Existing Content Type', 'pods' ); ?></a>
                        </p>
                    </div>
                    <div class="pods-wizard-step" id="pods-wizard-create">
                        <h2><?php _e( 'Creating a New Content Type', 'pods' ); ?></h2>
                        <hr />
                        <div class="pods-field-option">
                            <?php echo PodsForm::label('create_pod_type', __('Content Type', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('create_pod_type', pods_var('create_pod_type', 'post'), 'pick', array('data' => array('post_type' => __('Custom Post Type (like Posts or Pages)', 'pods'), 'taxonomy' => __('Custom Taxonomy (like Categories or Tags)', 'pods'), 'pod' => __('Custom Content Type', 'pods')), 'class' => 'pods-dependent-toggle')); ?>
                        </div>
                        <div class="pods-field-option">
                            <?php echo PodsForm::label('create_name', __('Name', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('create_name', pods_var('create_name', 'post'), 'text', array('class' => 'pods-validate pods-validate-required')); ?>
                        </div>
                        <p><a href="#pods-advanced" class="pods-advanced-toggle"><?php _e('Advanced', 'pods'); ?></a></p>
                        <div class="pods-advanced">
                            <div class="pods-field-option">
                                <?php echo PodsForm::label('create_label_plural', __('Plural Label', 'pods'), __('help', 'pods')); ?>
                                <?php echo PodsForm::field('create_label_plural', pods_var('create_label_plural', 'post'), 'text'); ?>
                            </div>
                            <div class="pods-field-option">
                                <?php echo PodsForm::label('create_label_singular', __('Singular Label', 'pods'), __('help', 'pods')); ?>
                                <?php echo PodsForm::field('create_label_singular', pods_var('create_label_singular', 'post'), 'text'); ?>
                            </div>
                            <div class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-post_type">
                                <?php echo PodsForm::label('create_storage', __('Storage Type', 'pods'), __('Table based storage will operate in a way where each field in your content type becomes a field in a table, where as Meta based relies upon WordPress\' meta storage table for all field data.')); ?>
                                <?php echo PodsForm::field('create_storage', pods_var('create_storage', 'post'), 'pick', array('data' => array('meta' => 'Meta Based (WP Default)', 'table' => 'Table Based'))); ?>
                            </div>
                        </div>
                        <p class="submit">
                            <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                            <button type="submit" class="button-primary"><?php _e('Continue'); ?></button>
                            &nbsp;&nbsp;&nbsp;
                            <a class="button-secondary" href="#pods-wizard-choose" title="Go Back"><?php _e('Go Back', 'pods'); ?></a>
                        </p>
                    </div>
                    <div class="pods-wizard-step" id="pods-wizard-extend">
                        <h2><?php _e('Extend an Existing Content Type', 'pods'); ?></h2>
                        <hr />
                        <div class="pods-field-option">
                            <?php echo PodsForm::label('extend_pod_type', __('Content Type', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('extend_pod_type', pods_var('extend_pod_type', 'post'), 'pick', array('data' => array('post_type' => __('Post Types (Posts, Pages, etc..)', 'pods'), 'taxonomy' => __('Taxonomies (Categories, Tags, etc..)', 'pods'), 'media' => __('Media', 'pods'), 'user' => __('Users', 'pods'), 'comment' => __('Comments', 'pods')), 'class' => 'pods-dependent-toggle')); ?>
                        </div>
                        <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post_type">
<?php
$post_types = get_post_types();
$ignore = array('attachment', 'revision', 'nav_menu_item');
foreach ($post_types as $post_type => $label) {
    if ( in_array( $post_type, $ignore ) || empty( $post_type ) || 0 === strpos( $post_type, '_pods_' ) ) {
        unset($post_types[$post_type]);
        continue;
    }
    $post_type = get_post_type_object($post_type);
    $post_types[$post_type->name] = $post_type->label;
}
?>
                            <?php echo PodsForm::label('extend_post_type', __('Post Type', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('extend_post_type', pods_var('extend_post_type', 'post'), 'pick', array('data' => $post_types)); ?>
                        </div>
                        <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-taxonomy">
<?php
$taxonomies = get_taxonomies();
$ignore = array('nav_menu', 'link_category', 'post_format');
foreach ($taxonomies as $taxonomy => $label) {
    if (in_array($taxonomy, $ignore)) {
        unset($taxonomies[$taxonomy]);
        continue;
    }
    $taxonomy = get_taxonomy($taxonomy);
    $taxonomies[$taxonomy->name] = $taxonomy->label;
}
?>
                            <?php echo PodsForm::label('extend_taxonomy', __('Taxonomy', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('extend_taxonomy', pods_var('extend_taxonomy', 'post'), 'pick', array('data' => $taxonomies)); ?>
                        </div>
                        <div class="pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post_type pods-depends-on-extend-pod-type-user pods-depends-on-extend-pod-type-comment">
                            <p><a href="#pods-advanced" class="pods-advanced-toggle"><?php _e('Advanced', 'pods'); ?></a></p>
                            <div class="pods-advanced">
                                <div class="pods-field-option">
                                    <?php echo PodsForm::label('extend_storage', __('Storage Type', 'pods'), __('Table based storage will operate in a way where each field in your content type becomes a field in a table, where as Meta based relies upon WordPress\' meta storage table for all field data.')); ?>
                                    <?php echo PodsForm::field('extend_storage', pods_var('extend_storage', 'post'), 'pick', array('data' => array('meta' => __('Meta Based (WP Default)', 'pods'), 'table' => 'Table Based'))); ?>
                                </div>
                            </div>
                        </div>
                        <p class="submit">
                            <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                            <button type="submit" class="button-primary"><?php _e('Continue'); ?></button>
                            &nbsp;&nbsp;&nbsp;
                            <a class="button-secondary" href="#pods-wizard-choose" title="Go Back"><?php _e('Go Back', 'pods'); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        //$( document ).Pods( 'wizard' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'advanced' );
        $( document ).Pods( 'confirm' );

        $( '.pods-wizard .pods-wizard-step' ).on( 'click', 'a.button-primary, a.button-secondary', function ( e ) {
            $( this ).css( 'cursor', 'default' );
            $( this ).prop( 'disabled', true );

            var $wizard = $( this ).closest( '.pods-wizard' );

            var wizard_hash = this.hash;
            if ( null === wizard_hash || '' == wizard_hash )
                return true;

            $wizard.find( '.pods-wizard-step' ).not( wizard_hash ).slideUp( 400, function () {
                $wizard.find( '.pods-wizard-step' ).filter( wizard_hash ).slideDown();
                $wizard.find( 'input.pods-wizard-current-step' ).val( wizard_hash.replace( /\#pods\-wizard\-/gi, '' ) );
            } );

            $( this ).css( 'cursor', 'pointer' );
            $( this ).prop( 'disabled', false );

            e.preventDefault();
        } );
        $( '.pods-wizard .pods-wizard-step' ).hide();
        $( '.pods-wizard .pods-wizard-step:first' ).show();
    } );

    pods_admin_submit_callback = function ( id ) {
        document.location = 'admin.php?page=pods&action=edit&id=' + id;
    }
</script>