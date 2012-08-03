<div id="pods-wizard-box" class="pods-wizard-steps-2 pods-wizard-hide-first">
    <div id="pods-wizard-heading">
        <ul>
            <li class="pods-wizard-menu-current">
                <i></i><span>1</span> Create or Extend<em></em>
            </li>
            <li>
                <i></i><span>2</span> Configure Content Type<em></em>
            </li>
        </ul>
    </div>
    <div id="pods-wizard-main">
        <div id="pods-wizard-panel-1" class="pods-wizard-panel">
            <div class="pods-wizard-content">
                <p>
                    Pods are content types that you can customize and define fields for based on your needs. You can choose to create a Custom Post Type, Custom Taxonomy, or a Custom Pod which operate completely seperate from normal WordPress Objects. You can also extend existing content types like WP Objects such as Post Types, Taxonomies, Users, or Comments
                </p>
            </div>
            <div id="pods-wizard-options">
                <div class="pods-wizard-option">
                    <a href="#pods-wizard-create">
                        <h2>Create New</h2>
                        <p>Create a new custom content type, blah blah bliggedy bloo.</p>
                        <span class="pods-wizard-option-selected">Option selected, please move to next step.</span>
                    </a>
                    <p><br /></p>
                </div>
                <div class="pods-wizard-option">
                    <a href="#pods-wizard-extend">
                        <h2>Extend Existing</h2>
                        <p>Extend an existing content type, blah blah bliggedy blum.</p>
                        <span class="pods-wizard-option-selected">Option selected, please move to next step.</span>
                    </a>
                    <p><br /></p>
                </div>
            </div>
        </div>
        <div id="pods-wizard-panel-2" class="pods-wizard-panel">
            <div class="pods-wizard-content">
                <p>
                    Some info about creating and extending new content types
                </p>
            </div>
            <div class="stuffbox" id="pods-wizard-create">
                <h3><label for="link_name">Create</label></h3>
                <div class="inside pods-manage-field pods-dependency">
                    <div class="pods-field-option">
                        <label class="pods-form-ui-label pods-form-ui-label-create-pod-type" for="pods-form-ui-create-pod-type"> Content Type</label>
                        <select name="create_pod_type" data-name-clean="create-pod-type" id="pods-form-ui-create-pod-type" class="pods-form-ui-field-type-pick pods-form-ui-field-name-create-pod-type pods-dependent-toggle">
                            <option value="post_type">Custom Post Type (like Posts or Pages)</option>
                            <option value="taxonomy">Custom Taxonomy (like Categories or Tags)</option>
                            <option value="pod">Custom Content Type</option>
                        </select>
                    </div>
                    <div class="pods-field-option">
                        <label class="pods-form-ui-label pods-form-ui-label-create-name" for="pods-form-ui-create-name"> Name</label>
                        <input name="create_name" data-name-clean="create-name" id="pods-form-ui-create-name" class="pods-form-ui-field-type-text pods-form-ui-field-name-create-name pods-validate pods-validate-required" type="text">
                    </div>
                    <p>
                        <a href="#pods-advanced" class="pods-advanced-toggle">+ Advanced</a>
                    </p>
                    <div style="display: none;" class="pods-advanced">
                        <div class="pods-field-option">
                            <label class="pods-form-ui-label pods-form-ui-label-create-label-plural" for="pods-form-ui-create-label-plural"> Plural Label</label>
                            <input name="create_label_plural" data-name-clean="create-label-plural" id="pods-form-ui-create-label-plural" class="pods-form-ui-field-type-text pods-form-ui-field-name-create-label-plural" type="text">
                        </div>
                        <div class="pods-field-option">
                            <label class="pods-form-ui-label pods-form-ui-label-create-label-singular" for="pods-form-ui-create-label-singular"> Singular Label</label>
                            <input name="create_label_singular" data-name-clean="create-label-singular" id="pods-form-ui-create-label-singular" class="pods-form-ui-field-type-text pods-form-ui-field-name-create-label-singular" type="text">
                        </div>
                        <div style="display: block;" class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-post_type pods-dependent-visible">
                            <label class="pods-form-ui-label pods-form-ui-label-create-storage" for="pods-form-ui-create-storage"> Storage Type<img src="<?php echo PODS_URL; ?>/ui/images/help.png" alt="Table based storage will operate in a way where each field in your content type becomes a field in a table, where as Meta based relies upon WordPress' meta storage table for all field data." class="pods-icon pods-qtip"></label>
                            <select name="create_storage" data-name-clean="create-storage" id="pods-form-ui-create-storage" class="pods-form-ui-field-type-text pods-form-ui-field-name-create-storage">
                                <option value="meta">Meta Based (WP Default)</option>
                                <option value="table">Table Based</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stuffbox" id="pods-wizard-extend">
                <h3><label for="link_name">Extend</label></h3>
                <div class="inside pods-manage-field pods-dependency">
                    <div class="pods-field-option">
                        <label class="pods-form-ui-label pods-form-ui-label-extend-pod-type" for="pods-form-ui-extend-pod-type"> Content Type</label>
                        <select name="extend_pod_type" data-name-clean="extend-pod-type" id="pods-form-ui-extend-pod-type" class="pods-form-ui-field-type-text pods-form-ui-field-name-extend-pod-type pods-dependent-toggle">
                            <option value="post_type">Post Types (Posts, Pages, etc..)</option>
                            <option value="taxonomy">Taxonomies (Categories, Tags, etc..)</option>
                            <option value="media">Media</option>
                            <option value="user">Users</option>
                            <option value="comment">Comments</option>
                        </select>
                    </div>
                    <div style="display: block;" class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post_type pods-dependent-visible">
                        <label class="pods-form-ui-label pods-form-ui-label-extend-post-type" for="pods-form-ui-extend-post-type"> Post Type</label>
                        <select name="extend_post_type" data-name-clean="extend-post-type" id="pods-form-ui-extend-post-type" class="pods-form-ui-field-type-text pods-form-ui-field-name-extend-post-type">
                            <option value="post">Posts</option>
                            <option value="page">Pages</option>
                        </select>
                    </div>
                    <div style="display: none;" class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-taxonomy">
                        <label class="pods-form-ui-label pods-form-ui-label-extend-taxonomy" for="pods-form-ui-extend-taxonomy"> Taxonomy</label>
                        <select name="extend_taxonomy" data-name-clean="extend-taxonomy" id="pods-form-ui-extend-taxonomy" class="pods-form-ui-field-type-text pods-form-ui-field-name-extend-taxonomy">
                            <option value="category">Categories</option>
                            <option value="post_tag">Tags</option>
                        </select>
                    </div>
                    <div style="display: block;" class="pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post_type pods-depends-on-extend-pod-type-user pods-depends-on-extend-pod-type-comment pods-dependent-visible">
                        <p>
                            <a href="#pods-advanced" class="pods-advanced-toggle">+ Advanced</a>
                        </p>
                        <div style="display: none;" class="pods-advanced">
                            <div class="pods-field-option">
                                <label class="pods-form-ui-label pods-form-ui-label-extend-storage" for="pods-form-ui-extend-storage"> Storage Type<img src="<?php echo PODS_URL; ?>/ui/images/help.png" alt="Table based storage will operate in a way where each field in your content type becomes a field in a table, where as Meta based relies upon WordPress' meta storage table for all field data." class="pods-icon pods-qtip"></label>
                                <select name="extend_storage" data-name-clean="extend-storage" id="pods-form-ui-extend-storage" class="pods-form-ui-field-type-text pods-form-ui-field-name-extend-storage">
                                    <option value="meta">Meta Based (WP Default)</option>
                                    <option value="table">Table Based</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="pods-wizard-actions">
        <div id="pods-wizard-toolbar">
            <a href="#start" id="pods-wizard-start" class="button button-secondary">Start Over</a>
            <a href="#next" id="pods-wizard-next" class="button button-primary">Next Step</a>
        </div>
        <div id="pods-wizard-finished">
            POD CREATION COMPLETE
        </div>
    </div>
</div>
<script>
    jQuery( function ( $ ) {
        $( '#pods-wizard-box' ).Pods( 'wizard' );
    } );
</script>