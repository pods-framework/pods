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

            <h2 class="italicized"><?php _e('Add New Pod'); ?></h2>

            <div id="poststuff" class="pods-wizard">
                <img src="<?php echo PODS_URL; ?>/ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />
                <p>Pods are content types that you can customize and define fields for based on your needs. You can choose to create a Custom Post Type, Custom Taxonomy, or a Custom Pod which operate completely seperate from normal WordPress Objects. You can also extend existing content types like WP Objects such as Post Types, Taxonomies, Users, or Comments</p>
                <hr />
                <div class="pods-manage-field pods-dependency">
                    <input type="hidden" name="create_extend" value="" class="pods-wizard-current-step" />
                    <div class="pods-wizard-step" id="pods-wizard-choose">
                        <p class="submit">
                            <a class="button-primary" href="#pods-wizard-create" title="Create a New Content Type">Create a New Content Type</a>
                            &nbsp;&nbsp;&nbsp;
                            <a class="button-secondary" href="#pods-wizard-extend" title="Extend an Existing Content Type">Extend an Existing Content Type</a>
                        </p>
                    </div>
                    <div class="pods-wizard-step" id="pods-wizard-create">
                        <h2>Creating a New Content Type</h2>
                        <hr />
                        <div class="pods-field-option">
                            <?php echo PodsForm::label('create_pod_type', __('Content Type', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('create_pod_type', pods_var('create_pod_type', 'post'), 'pick', array('data' => array('post_type' => 'Custom Post Type (like Posts or Pages)', 'taxonomy' => 'Custom Taxonomy (like Categories or Tags)', 'pod' => 'Custom Content Type'), 'class' => 'pods-dependent-toggle')); ?>
                        </div>
                        <div class="pods-field-option">
                            <?php echo PodsForm::label('create_name', __('Name', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('create_name', pods_var('create_name', 'post'), 'text', array('class' => 'pods-validate pods-validate-required')); ?>
                        </div>
                        <p><a href="#pods-advanced" class="pods-advanced-toggle">Advanced</a></p>
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
                            <a class="button-secondary" href="#pods-wizard-choose" title="Go Back">Go Back</a>
                        </p>
                    </div>
                    <div class="pods-wizard-step" id="pods-wizard-extend">
                        <h2>Extend an Existing Content Type</h2>
                        <hr />
                        <div class="pods-field-option">
                            <?php echo PodsForm::label('extend_pod_type', __('Content Type', 'pods'), __('help', 'pods')); ?>
                            <?php echo PodsForm::field('extend_pod_type', pods_var('extend_pod_type', 'post'), 'pick', array('data' => array('post_type' => 'Post Types (Posts, Pages, etc..)', 'taxonomy' => 'Taxonomies (Categories, Tags, etc..)', 'media' => 'Media', 'user' => 'Users', 'comment' => 'Comments'), 'class' => 'pods-dependent-toggle')); ?>
                        </div>
                        <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post_type">
<?php
$post_types = get_post_types();
$ignore = array('attachment', 'revision', 'nav_menu_item');
foreach ($post_types as $post_type => $label) {
    if (in_array($post_type, $ignore)) {
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
                            <p><a href="#pods-advanced" class="pods-advanced-toggle">Advanced</a></p>
                            <div class="pods-advanced">
                                <div class="pods-field-option">
                                    <?php echo PodsForm::label('extend_storage', __('Storage Type', 'pods'), __('Table based storage will operate in a way where each field in your content type becomes a field in a table, where as Meta based relies upon WordPress\' meta storage table for all field data.')); ?>
                                    <?php echo PodsForm::field('extend_storage', pods_var('extend_storage', 'post'), 'pick', array('data' => array('meta' => 'Meta Based (WP Default)', 'table' => 'Table Based'))); ?>
                                </div>
                            </div>
                        </div>
                        <p class="submit">
                            <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                            <button type="submit" class="button-primary"><?php _e('Continue'); ?></button>
                            &nbsp;&nbsp;&nbsp;
                            <a class="button-secondary" href="#pods-wizard-choose" title="Go Back">Go Back</a>
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
        $( document ).Pods( 'wizard' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'advanced' );
        $( document ).Pods( 'confirm' );
    } );

    pods_admin_submit_callback = function ( id ) {
        document.location = 'admin.php?page=pods&action=edit&id=' + id;
    }
</script>