<?php

    function pods_field( $params = array() )
    {

        $defaults = array(
                'type' => 'text',
                'name' => 'textfield',
                'label' => 'Single Line Text',
                'comment' => '',
                'options' => array()
            );

        $params = array_merge( $defaults, $params );

        switch ($params['type']) {
            case 'text':
                ?>
                    <div class="pods-field pods-textfield" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" type="text" value="" />
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-textfield -->
                <?php
                break;

            case 'code':
                ?>
                    <div class="pods-field pods-textarea" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <textarea name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" rows="7" cols="70"></textarea>
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-textfield -->
                <?php
                break;

            case 'date':
                ?>
                    <div class="pods-field pods-textfield pods-date" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" type="text" class="pods-date-field" value="" />
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-textfield.pods-date -->
                    <script>
                    jQuery(function() {
                        <?php if( !empty( $params['options']['time'] ) ) : ?>
                            jQuery( "input#pods-field-<?php echo $params['name']; ?>" ).datetimepicker();
                        <?php else: ?>
                            jQuery( "input#pods-field-<?php echo $params['name']; ?>" ).datepicker();
                        <?php endif; ?>
                    });
                    </script>
                <?php
                break;

            case 'number':
                ?>
                    <div class="pods-field pods-textfield pods-number" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" type="text" value="" />
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-number -->
                <?php
                break;

            case 'boolean':
                ?>
                    <div class="pods-field pods-boolean" id="field-pods-field-<?php echo $params['name']; ?>">
                        <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" type="checkbox" value="1" />
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-boolean -->
                <?php
                break;

            case 'file':
                ?>
                    <div class="pods-field pods-file" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>

                        <ul class="pods-files">
                            <?php for ($i=0; $i < 3; $i++) : ?>
                                <li class="media-item"> <!-- required for WP styles (.pinkynail) -->
                                    <span class="pods-file-reorder"><img src="<?php echo PODS_URL . 'ui/images/handle.gif'; ?>" alt="Drag to reorder" /></span>
                                    <span class="pods-file-thumb">
                                        <span>
                                            <img class="pinkynail" src="<?php echo PODS_URL . 'ui/images/icon32.png'; ?>" alt="Thumbnail" /> <!-- URL to Media thumbnail, .pinkynail forces max 40px wide, max 32px tall -->
                                            <input name="pods-field-<?php echo $params['name']; ?>[files]" type="hidden" value="" /> <!-- for ID storage -->
                                        </span>
                                    </span>
                                    <span class="pods-file-name">Sample Image</span>
                                    <span class="pods-file-remove"><img class="pods-icon-minus" src="<?php echo PODS_URL . 'ui/images/del.png'; ?>" alt="Remove" /></span>
                                </li>
                            <?php endfor; ?>
                        </ul>
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                        <p class="pods-add-file">
                            <a class="button" href="media-upload.php?type=image&amp;TB_iframe=1&amp;width=640&amp;height=1500">Add New</a>
                        </p>
                    </div>
                    <!-- /.pods-file -->
                <?php
                break;

            case 'pick':
                ?>
                    <div class="pods-field pods-pick" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <?php if( !isset( $params['options']['type'] ) || $params['options']['type'] == 'default' ) : ?>
                            <select name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>">
                                <option value="">-- Select One --</option>
                                <?php for ($i=1; $i < 6; $i++) : ?>
                                    <option value="<?php echo $i; ?>">Choice <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        <?php else:
                            switch($params['options']['type']){
                                case 'multi': ?>
                                    <select class="pods-pick-multi" multiple="multiple" size="7" name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>">
                                        <?php for ($i=1; $i < 6; $i++) : ?>
                                            <option value="<?php echo $i; ?>">Choice <?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <?php break;
                                case 'checkbox': ?>
                                    <div class="pods-pick-values pods-pick-checkbox">
                                        <ul>
                                            <?php for ($i=1; $i < 16; $i++) : ?>
                                                <li>
                                                    <div class="pods-field pods-boolean" id="field-pods-field-<?php echo $params['name']; ?>-<?php echo $i; ?>">
                                                        <input name="pods-field-<?php echo $params['name']; ?>-<?php echo $i; ?>" id="pods-field-<?php echo $params['name']; ?>-<?php echo $i; ?>" type="checkbox" value="<?php echo $i; ?>" />
                                                        <label for="pods-field-<?php echo $params['name']; ?>-<?php echo $i; ?>">Option <?php echo $i; ?></label>
                                                    </div>
                                                    <!-- /.pods-boolean -->
                                                </li>
                                            <?php endfor; ?>
                                        </ul>
                                    </div>
                                    <?php break;
                                case 'radio': ?>
                                    <div class="pods-pick-values pods-pick-radio">
                                        <ul>
                                            <?php for ($i=1; $i < 13; $i++) : ?>
                                                <li>
                                                    <div class="pods-field pods-boolean" id="field-pods-field-<?php echo $params['name']; ?>-<?php echo $i; ?>">
                                                        <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>-<?php echo $i; ?>" type="radio" value="<?php echo $i; ?>" />
                                                        <label for="pods-field-<?php echo $params['name']; ?>-<?php echo $i; ?>">Option <?php echo $i; ?></label>
                                                    </div>
                                                    <!-- /.pods-boolean -->
                                                </li>
                                            <?php endfor; ?>
                                        </ul>
                                    </div>
                                    <?php break;
                            }
                        endif; ?>
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-pick -->
                <?php
                break;

            default:
                # code...
                break;
        }

        ?>

    <?php }

?>

<?php
    // including the style inline because I'm not sure how you'll want to enqueue it
    // since it'll be used both here and on the site front end (maybe?)
    echo '<style type="text/css">';
    include PODS_DIR . 'ui/css/pods-front.css';
    include PODS_DIR . 'ui/css/custom-theme/jquery-ui-1.8.16.custom.css';   // used for date picker
    include PODS_DIR . 'ui/css/custom-theme/jquery-ui-timepicker-addon.css';   // used for time picker
    echo '</style>';

    // this is a build of jQuery UI that includes everything
    // we need the date picker
    // as with the CSS, this needs to be enqueued based on what ships with WP
    echo '<script type="text/javascript">';
    echo 'var PODS_URL = "';
    echo PODS_URL;
    echo '";';
    include PODS_DIR . 'ui/js/jquery-ui-1.8.16.custom.min.js';
    include PODS_DIR . 'ui/js/jquery-ui-timepicker-addon.js';
    include PODS_DIR . 'ui/js/pods-file-attach.js';
    echo '</script>';
?>

<div class="wrap">

    <div id="icon-edit" class="icon32 icon32-posts-post"><br /></div>
    <h2>Edit</h2>

    <form action="" method="post" id="pods-record">

        <div id="poststuff" class="metabox-holder has-right-sidebar"> <!-- class "has-right-sidebar" preps for a sidebar... always present? -->
            <div id="side-info-column" class="inner-sidebar">
                <div id="side-sortables" class="meta-box-sortables ui-sortable">

                    <!-- BEGIN PUBLISH DIV -->
                    <div id="submitdiv" class="postbox">
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        <h3 class="hndle"><span>Publish</span></h3>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="minor-publishing">
                                    <div id="minor-publishing-actions">
                                        <div id="save-action">
                                            <input type="submit" name="save" id="save-post" value="Save" class="button button-highlighted" />
                                        </div>
                                        <!-- /#save-action -->
                                        <div id="preview-action">
                                            <a class="preview button" href="#" target="pods-preview" id="pod-preview">Preview</a>
                                            <input type="hidden" name="pods-preview" id="pods-preview" value="" />
                                        </div>
                                        <!-- /#preview-action -->
                                        <div class="clear"></div>
                                    </div>
                                    <!-- /#minor-publishing-actions -->
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" href="#">Move to Trash</a>
                                        </div>
                                        <!-- /#delete-action -->
                                        <div id="publishing-action">
                                            <input type="submit" name="publish" id="publish" class="button-primary" value="Publish" accesskey="p" />
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
                    <!-- /#submitdiv -->

                    <!-- END PUBLISH DIV -->


                    <!-- TODO: minor column fields -->

                </div>
                <!-- /#side-sortables -->
            </div>
            <!-- /#side-info-column -->


            <div id="post-body">

                <div id="post-body-content">

                    <div id="titlediv">
                        <div id="titlewrap">
                            <label class="hide-if-no-js" style="" id="title-prompt-text" for="title">Name</label>
                            <input type="text" name="pods_entry_title" size="30" tabindex="1" value="" id="title" autocomplete="off" />
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
                            <h3 class="hndle"><span>Pod Name</span></h3>
                            <div class="inside">

                                <?php

                                    // dummy functions for the sake of being functions

                                    $args = array( 'type' => 'text', 'name' => 'singlelinetext1', 'label' => 'Single Line Text' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'text', 'name' => 'singlelinetext2', 'label' => 'Single Line Text with Comment', 'comment' => 'Please fill out the field' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'code', 'name' => 'code1', 'label' => 'Code' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'code', 'name' => 'code2', 'label' => 'Code with Comment', 'comment' => 'Please fill out the field' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'code', 'name' => 'code3', 'label' => 'WYSIWYG', 'options' => array( 'wysiwyg' => true ) );
                                    pods_field( $args );

                                    $args = array( 'type' => 'date', 'name' => 'date1', 'label' => 'Date' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'date', 'name' => 'date2', 'label' => 'Date with Comment', 'comment' => 'Please fill out the field' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'date', 'name' => 'date3', 'label' => 'Date with time', 'options' => array( 'time' => true ) );
                                    pods_field( $args );

                                    $args = array( 'type' => 'number', 'name' => 'number1', 'label' => 'Number' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'number', 'name' => 'number2', 'label' => 'Number with Comment', 'comment' => 'Please fill out the field' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'boolean', 'name' => 'boolean1', 'label' => 'Boolean' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'boolean', 'name' => 'boolean2', 'label' => 'Boolean with Comment', 'comment' => 'Explain the Boolean' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'file', 'name' => 'file1', 'label' => 'File Upload' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'file', 'name' => 'file2', 'label' => 'File Upload with Comment', 'comment' => 'File upload details' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'pick', 'name' => 'pick1', 'label' => 'Pick' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'pick', 'name' => 'pick2', 'label' => 'Pick with Comment', 'comment' => 'Pick comment' );
                                    pods_field( $args );

                                    $args = array( 'type' => 'pick', 'name' => 'pick3', 'label' => 'Pick - Multi', 'options' => array( 'type' => 'multi' ) );
                                    pods_field( $args );

                                    $args = array( 'type' => 'pick', 'name' => 'pick4', 'label' => 'Pick - Checkboxes', 'options' => array( 'type' => 'checkbox' ) );
                                    pods_field( $args );

                                    $args = array( 'type' => 'pick', 'name' => 'pick5', 'label' => 'Pick - Radio', 'options' => array( 'type' => 'radio' ) );
                                    pods_field( $args );

                                ?>

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

    </form>
    <!-- /#pods-record -->

</div>
<!-- /.wrap -->