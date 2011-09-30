<?php
// just saving some time, for demo's sake
global $i;
// name has been skipped for the time being

//$pod = $this->api->load_pod('event');
//$fields = $pod->fields;
$fields = array(
                array(
                    'label'     => 'Name',
                    'name'      => 'name',
                    'type'      => 'text',
                    'comment'   => ''
                ),
                array(
                    'label'     => 'Featured',
                    'name'      => 'featured',
                    'type'      => 'boolean',
                    'comment'   => 'Is this event featured?'
                ),
                array(
                    'label'     => 'Category',
                    'name'      => 'category',
                    'type'      => 'relationship to <strong>pod</strong> event_categories',
                    'comment'   => ''
                ),
                array(
                    'label'     => 'Date',
                    'name'      => 'date',
                    'type'      => 'date',
                    'comment'   => ''
                ),/* no groups for 2.0 unless we can fit it into the schedule
                array(
                    'label'     => 'Images',
                    'name'      => 'images',
                    'type'      => 'group',
                    'comment'   => '',
                    'children'  => array(
                                        array(
                                            'label'     => 'Image',
                                            'name'      => 'image',
                                            'type'      => 'file',
                                            'comment'   => ''
                                        ),
                                        array(
                                            'label'     => 'URL',
                                            'name'      => 'url',
                                            'type'      => 'text',
                                            'comment'   => ''
                                        )
                                    ),
                ),*/
                array(
                    'label'     => 'Registration Form',
                    'name'      => 'reg_form',
                    'type'      => 'text',
                    'comment'   => 'Choose from the dropdown'
                ),
                array(
                    'label'     => 'Flyer',
                    'name'      => 'flyer',
                    'type'      => 'file upload',
                    'comment'   => 'Must be PDF'
                ),
                array(
                    'label'     => 'Contact Name',
                    'name'      => 'contact_name',
                    'type'      => 'text',
                    'comment'   => ''
                ),
                array(
                    'label'     => 'Contact Email',
                    'name'      => 'contact_email',
                    'type'      => 'text',
                    'comment'   => ''
                ),
                array(
                    'label'     => 'Details',
                    'name'      => 'details',
                    'type'      => 'paragraph',
                    'comment'   => ''
                ),
                array(
                    'label'     => 'Slug',
                    'name'      => 'slug',
                    'type'      => 'slug',
                    'comment'   => ''
                ),
                array(
                    'label'     => '',
                    'name'      => '_pods_empty',
                    'type'      => '',
                    'comment'   => '',
                )
);
?>

<div class="wrap pods-admin">

    <div id="icon-edit-pages" class="icon32"><br /></div>

    <form action="" method="post" class="pods pods-fields">

    <h2>Edit Pod: <span class="pod-slug"><em>events</em> <input type="button" class="edit-slug-button button" value="Edit" /></span><span class="pod-slug-field"><input type="text" name="pod_name" size="30" tabindex="1" value="events" id="title" /> <input type="button" class="save-button button" value="OK" /> <a class="cancel" href="#cancel-edit">Cancel</a></span></h2>

        <div id="poststuff" class="has-right-sidebar meta-box-sortables">

            <div id="side-info-field" class="inner-sidebar pods_floatmenu">
                <div id="side-sortables">

                    <div id="submitdiv" class="postbox">
                        <h3><span>Manage</span></h3>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="major-publishing-actions">
                                    <div id="delete-action">
                                        <a class="submitdelete deletion" href="#">Delete Pod</a>
                                    </div>
                                    <div id="publishing-action">
                                        <button class="button-primary" type="submit">Save Pod</button>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /#submitdiv -->
                </div>
            </div>
            <!-- /inner-sidebar -->

            <div id="post-body">
                <div id="post-body-content">
                    <h2>Manage Fields</h2>
                    <!-- pods table -->
                    <table class="widefat fixed pages" cellspacing="0"> 
                        <thead> 
                            <tr> 
                                <th scope="col" id="cb" class="manage-column field-cb check-column">
                                    <span>&nbsp;</span>
                                </th>
                                <th scope="col" id="label" class="manage-column field-label">
                                    <span>Label</span>
                                </th>
                                <th scope="col" id="machine-name" class="manage-column field-machine-name">
                                    <span>Name</span>
                                </th>
                                <th scope="col" id="field-type" class="manage-column field-field-type">
                                    <span>Field Type</span>
                                </th>
                                <th scope="col" id="comment" class="manage-column field-comment">
                                    <span>Comment</span>
                                </th>
                            </tr> 
                        </thead> 

                        <tfoot> 
                            <tr> 
                                <th scope="col" id="cb" class="manage-column field-cb check-column">
                                    <span>&nbsp;</span>
                                </th>
                                <th scope="col" id="label" class="manage-column field-label">
                                    <span>Label</span>
                                </th>
                                <th scope="col" id="machine-name" class="manage-column field-machine-name">
                                    <span>Name</span>
                                </th>
                                <th scope="col" id="field-type" class="manage-column field-field-type">
                                    <span>Type</span>
                                </th>
                                <th scope="col" id="comment" class="manage-column field-comment">
                                    <span>Comment</span>
                                </th>
                            </tr>
                        </tfoot> 

                        <tbody id="the-list"> 
                            <?php 
                                $i = 1;
                                foreach ($fields as $field) {
                                    if ('_pods_empty' == $field['name'])
                                        continue;
                                    $group = null;
                                    include PODS_DIR . 'ui/admin/setup_edit_pod_field.php';
                                    $i++;/*
                                    if('group' == $field['type']) {
                                        $children = $field['children'];
                                        $group = $field;
                                        foreach($children as $field) {
                                            include PODS_DIR . 'ui/admin/setup_edit_pod_field.php';
                                            $i++;
                                        }
                                    }*/
                                }
                            ?>

                        </tbody> 
                    </table>
                    <!-- /pods table -->

                    <p class="pods-add-field">
                        <a href="#" class="button-primary">Add Field</a>
                    </p>

                    <div id="pods-pod-advanced-settings" class="postbox closed">
                        <div class="handlediv" title="Click to toggle">
                            <br />
                        </div>
                        <h3><span>Advanced Options</span></h3>
                        <div class="inside pods-form">
                            <div id="pods-manage-settings-wrapper">
                                <div id="pods-manage-settings">

                                    <fieldset>
                                        <legend>Manage Settings</legend>

                                        <div class="pods-group pods-helpers">
                                            <div class="pods-pick-alt pods-pick-checkbox pods-helper" id="field-pods-pod-pre-save-helper">
                                                <p>Pre-save Helpers</p>
                                                <div class="pods-pick-choices">
                                                    <div class="pods-checkbox" id="field-pods-pod-pre-save-helper-1">
                                                        <input name="pods-pod-pre-save-helper-1" id="pods-pod-pre-save-helper-1" type="checkbox" />
                                                        <label for="pods-pod-pre-save-helper-1">Helper 1</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-pre-save-helper-2">
                                                        <input name="pods-pod-pre-save-helper-2" id="pods-pod-pre-save-helper-2" type="checkbox" />
                                                        <label for="pods-pod-pre-save-helper-2">Helper 2</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-pre-save-helper-3">
                                                        <input name="pods-pod-pre-save-helper-3" id="pods-pod-pre-save-helper-3" type="checkbox" />
                                                        <label for="pods-pod-pre-save-helper-3">Helper 3</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pods-pick-alt pods-pick-checkbox pods-helper" id="field-pods-pod-pre-drop-helper">
                                                <p>Pre-drop Helpers</p>
                                                <div class="pods-pick-choices">
                                                    <div class="pods-checkbox" id="field-pods-pod-pre-drop-helper-1">
                                                        <input name="pods-pod-pre-drop-helper-1" id="pods-pod-pre-drop-helper-1" type="checkbox" />
                                                        <label for="pods-pod-pre-drop-helper-1">Helper 1</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-pre-drop-helper-2">
                                                        <input name="pods-pod-pre-drop-helper-2" id="pods-pod-pre-drop-helper-2" type="checkbox" />
                                                        <label for="pods-pod-pre-drop-helper-2">Helper 2</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-pre-drop-helper-3">
                                                        <input name="pods-pod-pre-drop-helper-3" id="pods-pod-pre-drop-helper-3" type="checkbox" />
                                                        <label for="pods-pod-pre-drop-helper-3">Helper 3</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pods-pick-alt pods-pick-checkbox pods-helper" id="field-pods-pod-post-save-helper">
                                                <p>Post-save Helpers</p>
                                                <div class="pods-pick-choices">
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-1">
                                                        <input name="pods-pod-post-save-helper-1" id="pods-pod-post-save-helper-1" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-1">Helper 1</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-2">
                                                        <input name="pods-pod-post-save-helper-2" id="pods-pod-post-save-helper-2" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-2">Helper 2</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-3">
                                                        <input name="pods-pod-post-save-helper-3" id="pods-pod-post-save-helper-3" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-3">Helper 3</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-4">
                                                        <input name="pods-pod-post-save-helper-4" id="pods-pod-post-save-helper-4" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-4">Helper 4</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-5">
                                                        <input name="pods-pod-post-save-helper-5" id="pods-pod-post-save-helper-5" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-5">Helper 5</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-6">
                                                        <input name="pods-pod-post-save-helper-6" id="pods-pod-post-save-helper-6" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-6">Helper 6</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-7">
                                                        <input name="pods-pod-post-save-helper-7" id="pods-pod-post-save-helper-7" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-7">Helper 7</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-8">
                                                        <input name="pods-pod-post-save-helper-8" id="pods-pod-post-save-helper-8" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-8">Helper 8</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-save-helper-9">
                                                        <input name="pods-pod-post-save-helper-9" id="pods-pod-post-save-helper-9" type="checkbox" />
                                                        <label for="pods-pod-post-save-helper-9">Helper 9</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pods-pick-alt pods-pick-checkbox pods-helper" id="field-pods-pod-post-drop-helper">
                                                <p>Post-drop Helpers</p>
                                                <div class="pods-pick-choices">
                                                    <div class="pods-checkbox" id="field-pods-pod-post-drop-helper-1">
                                                        <input name="pods-pod-post-drop-helper-1" id="pods-pod-post-drop-helper-1" type="checkbox" />
                                                        <label for="pods-pod-post-drop-helper-1">Helper 1</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-drop-helper-2">
                                                        <input name="pods-pod-post-drop-helper-2" id="pods-pod-post-drop-helper-2" type="checkbox" />
                                                        <label for="pods-pod-post-drop-helper-2">Helper 2</label>
                                                    </div>
                                                    <div class="pods-checkbox" id="field-pods-pod-post-drop-helper-3">
                                                        <input name="pods-pod-post-drop-helper-3" id="pods-pod-post-drop-helper-3" type="checkbox" />
                                                        <label for="pods-pod-post-drop-helper-3">Helper 3</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.pods-group -->

                                        <div class="pods-textfield" id="field-pods-pod-detail-page">
                                            <label for="pods-pod-detail-page">Detail Page URL (ex. <code>our-team/{@slug}/</code>)</label>
                                            <input name="pods-pod-detail-page" id="pods-pod-detail-page" type="text" />
                                        </div>

                                        <div class="pods-checkbox pods-dependent-toggle pods-related-to-top-level" id="field-pods-pod-top-level">
                                            <input name="pods-pod-top-level" id="pods-pod-top-level" type="checkbox" value="1" />
                                            <label for="pods-pod-top-level">Include this Pod in the Top level menu?</label>
                                        </div>

                                        <div class="pods-group pods-dependent-on pods-requires-top-level-1">
                                            <div class="pods-textfield" id="field-pods-pod-menu-label">
                                                <label for="pods-pod-menu-label">Menu Label</label>
                                                <input name="pods-pod-menu-label" id="pods-pod-menu-label" type="text" />
                                            </div>

                                            <div class="pods-textfield" id="field-pods-pod-menu-icon">
                                                <label for="pods-pod-menu-icon">Menu Icon</label>
                                                <input name="pods-pod-menu-icon" id="pods-pod-menu-icon" type="text" />
                                            </div>
                                        </div>

                                    </fieldset>

                                </div>
                            </div>
                            <!-- /pods-manage-settings-wrapper -->
                        </div>
                        <!-- /inside -->
                    </div>
                    <!-- /pods-pod-advanced-settings -->

                </div>
                <!-- /#post-body-content -->
            </div>
            <!-- /#post-body -->

        </div>
        <!-- /poststuff -->

    </form>
</div>

<script type="text/javascript">
    var field_counter = <?php echo $i; ?>;
<?php
ob_start();
$i = -1;
$field =  array('label'     => 'New Field',
                'name'      => 'new',
                'type'      => 'txt',
                'comment'   => '');
$group = null;
include PODS_DIR . 'ui/admin/setup_edit_pod_field.php';
$new_field_row = ob_get_clean();
?>
    var new_field_row = '<?php echo addslashes(str_replace(array("\n", "\r"), '', $new_field_row)); ?>';

    jQuery(function($){

        $('.pod-slug .edit-slug-button').live('click', function(){
            $('.pod-slug').toggle();
            $('.pod-slug-field').toggle();
        });
        $('.pod-slug-field .save-button').live('click', function(){
            $('.pod-slug em').html($(this).parent().find('input').val());
            $('.pod-slug-field').toggle();
            $('.pod-slug').toggle();
        });
        $('.pod-slug-field a.cancel').live('click', function(){
            $('.pod-slug-field').toggle();
            $('.pod-slug').toggle();
            return false;
        });

        /* expand & collapse field editing */
        $('.pods-manage-field-wrapper').hide();
        $('a.pods-edit-field-specifics').live('click', function(){     // handles 'standard' field editing
            $row = $(this).parents('tr.pods-field');
            $field_cell = $row.find('td.field-label');
            $field = $field_cell.find('div.pods-manage-field-wrapper');
            if($field.is(':visible')) {
                $field.slideUp('', function() {
                    $row.toggleClass('expanded');
                    $field_cell.attr({ colspan: '1'});
                });
            } else {
                $row.toggleClass('expanded');
                $field_cell.attr({ colspan: '4'});
                $field.slideDown();
            }
            return false;
        });

        // add field
        $('p.pods-add-field a').live('click', function(){
            var add_row = new_field_row.replace('--1', '-' + field_counter);
            $('tbody#the-list').append(add_row);
            $('tbody#the-list tr#field-' + field_counter + ' .pods-manage-field-wrapper').hide(0, function() {
                $('tbody#the-list tr#field-' + field_counter + ' a.pods-edit-field-specifics').click();
            });
            field_counter++;
            $('.pods #the-list').sortable('refresh');
            return false;
        });

        // save field editing and collapse
        $('p.pods-save-field a.button-primary').live('click', function(){
            $row = $(this).parents('tr');
            $field_cell = $row.find('td.field-label');
            $field = $field_cell.find('div.pods-manage-field-wrapper');
            var color = $.curCSS($row.get(0), 'backgroundColor');
            $row.css( 'backgroundColor', '#FFFF33').animate( 
                    { backgroundColor: color },
                    { duration: 'slow', complete: function() { $(this).css( 'backgroundColor', '' ); } }
            );
            $field.slideUp('slow', function() {
                $row.toggleClass('expanded');
                $field_cell.attr({ colspan: '1'});
             });
            return false;
        });

        // cancel field editing and collapse
        $('p.pods-save-field a.cancel').live('click', function(){
            $row = $(this).parents('tr');
            $field_cell = $row.find('td.field-label');
            $field = $field_cell.find('div.pods-manage-field-wrapper');
            $field.slideUp('slow', function() {
                $row.toggleClass('expanded');
                $field_cell.attr({ colspan: '1'});
             });
            return false;
        });

        // delete
        $('.row-actions .delete a').live('click', function(){
            if (confirm('Are you sure you want to delete this field?')) {
                $row = $(this).parents('tr');
                if ($row.hasClass('pods-level-0')) {
                    $(this).parents('table tr').each(function() {
                        $child_row = $(this).parents('tr');
                        $child_row.css('backgroundColor', '#B80000');
                        $child_row.fadeOut('slow', function(){
                            $(this).remove();
                        });
                    });
                }
                $row.css('backgroundColor', '#B80000');
                $row.fadeOut('slow', function(){
                    $(this).remove();
                });
                $('.pods #the-list').sortable('refresh');
            }
            return false;
        });

        // advanced
        $('.pods-advanced').hide();
        $('.pods-advanced-toggle').live('click', function(){
            $(this).parent().parent().find('div.pods-advanced').slideToggle();
            return false;
        });

        // collapsable
        $('.handlediv').live('click', function(){
            $(this).parent().find('.inside').slideToggle();
            return false;
        });

        // dependencies
        $('.pods-dependent-on').hide();
        $('.pods-dependent-toggle input').live('change', function(){
            var pods_related_flag;
            pods_related_classes = $(this).parent().attr('class').split(/\s+/);
            $.each( pods_related_classes, function(index, item){
                if (item.substr(0, 16) === 'pods-related-to-') {
                    pods_related_flag = item.substr(16, item.length);
                }
            });
            $('div.pods-requires-'+pods_related_flag).slideToggle();
            $('div.pods-requires-'+pods_related_flag+'-'+$(this).val()).slideToggle();
        });

        // sorting
        var original_items_selector = 'tr:not(.pods-field-parent)';
        $('.pods #the-list').sortable({
            items: original_items_selector,
            axis: 'y',
            handle: 'img.pods-sort-handles',
            start: function(event, ui) {
                $('.pods #the-list tr').removeClass('alternate');
                var items_selector;
                var disabled_selector;
                pods_hide_children = false;
                if($(ui.item).hasClass('pods-level-0')) {
                    items_selector =  'tr.pods-level-0:not(.pods-field-group)';
                    disabled_selector = '.pods #the-list .pods-field-parent, .pods #the-list .pods-level-1';
                    if($(ui.item).hasClass('pods-field-group')) {
                        pods_hide_children = true;
                    }
                } else {
                    items_selector =  'tr.pods-level-1';
                    disabled_selector = '.pods #the-list .pods-level-0';
                }
                $('.pods #the-list').sortable('option', 'items', items_selector);
                $('.pods #the-list').sortable('refresh');
                $(disabled_selector).addClass('disabled');
                // might be easier to just hide the children
                if(pods_hide_children){
                    $('tr.pods-level-1').hide();
                }
            },
            stop: function(event, ui) {
                $('.pods #the-list').sortable('option', 'items', original_items_selector);
                $('.pods #the-list').sortable('refresh');
                // might need to gather our children
                if(ui.item.hasClass('pods-field-group')){
                    // this is a parent so we need to find the field name
                    var pods_group_classes = ui.item.attr('class').split(/\s+/);
                    $.each( pods_group_classes, function(index, item){
                        if (item.substr(0, 18) === 'pods-field-group-') {
                            var pods_group_name = item.substr(18, item.length);
                            pods_group_total_children = $('tr.pods-child-of-'+pods_group_name).length;
                            $($('tr.pods-child-of-'+pods_group_name).get().reverse()).each(function(){
                                // we need to get the reverse because we're using insertAfter
                                $(this).insertAfter($('tr.pods-field-group-'+pods_group_name));
                            });
                            
                        }
                    });
                }
                // we might need to show our hidden children
                if($(ui.item).hasClass('pods-field-group')) {
                    $('tr.pods-level-1').show();
                }
                $('.pods #the-list tr').removeClass('disabled');
                $('.pods #the-list tr').addClass(function(index) {
                        return index % 2 ? 'alternate': '';
                });
            }
        });

    });
</script>