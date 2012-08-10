<form action="" method="post" class="pods-manage pod-event">
<div id="poststuff" class="metabox-holder has-right-sidebar"> <!-- class "has-right-sidebar" preps for a sidebar... always present? -->
    <div id="side-info-column" class="inner-sidebar">
        <div id="side-sortables" class="meta-box-sortables ui-sortable">
            <!-- BEGIN PUBLISH DIV -->
            <div id="submitdiv" class="postbox">
                <div class="handlediv" title="Click to toggle"><br /></div>
                <h3 class="hndle"><span><?php _e( 'Manage', 'pods' ); ?></span></h3>

                <div class="inside">
                    <div class="submitbox" id="submitpost">
                        <div id="minor-publishing"><!--
                                    <div id="minor-publishing-actions">
                                        <div id="save-action">
                                            <input type="submit" name="save" id="save-post" value="Save" class="button button-highlighted" />
                                        </div>
                                        --><!-- /#save-action --><!--
                                        <div id="preview-action">
                                            <a class="preview button" href="#" target="pods-preview" id="pod-preview">Preview</a>
                                            <input type="hidden" name="pods-preview" id="pods-preview" value="" />
                                        </div>
                                        <!-- /#preview-action --><!--
                                        <div class="clear"></div>
                                    </div>
                                    <!-- /#minor-publishing-actions -->
                            <div id="major-publishing-actions">
                                <div id="delete-action">
                                    <a class="submitdelete deletion" href="#"><?php _e( 'Move to Trash', 'pods' ); ?></a>
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
<ol class="form-fields">
<li class="pods-field pods-textfield">
    <?php
    echo PodsForm::label( 'singlelinetext1', __( 'Single Line Text', 'pods' ) );
    echo PodsForm::field( 'singlelinetext1', NULL, 'text', array( 'class' => 'pods-text-field' ) );
    ?>
</li>

<li class="pods-field pods-textfield">
    <?php
    echo PodsForm::label( 'singlelinetext2', __( 'Single Line Text with Comment', 'pods' ) );
    echo PodsForm::field( 'singlelinetext2', NULL, 'text', array( 'class' => 'pods-text-field' ) );
    echo PodsForm::comment( 'Please fill out the field' );
    ?>
</li>
<li class="pods-field pods-textarea">
    <?php
    echo PodsForm::label( 'code1', __( 'Code Field', 'pods' ) );
    echo PodsForm::field( 'code1', NULL, 'textarea', array(
        'class' => 'pods-code-field',
        'attributes' => array( 'rows' => '7', 'cols' => '70' )
    ) );
    ?>
</li>
<li class="pods-field pods-textarea">
    <?php
    echo PodsForm::label( 'code2', __( 'Code Field w/ Comment', 'pods' ) );
    echo PodsForm::field( 'code2', NULL, 'textarea', array(
        'class' => 'pods-code-field',
        'attributes' => array( 'rows' => '7', 'cols' => '70' )
    ) );
    echo PodsForm::comment( 'Enter some code' );
    ?>
</li>
<li class="pods-field pods-textarea">
    <?php
    echo PodsForm::label( 'code3', __( 'WYSIWYG (cleditor)', 'pods' ) );
    echo PodsForm::field( 'code3', NULL, 'textarea', array(
        'attributes' => array(
            'rows' => '7',
            'cols' => '70',
            'data-width' => '100%'
        )
    ) );
    ?>
</li>

<li class="pods-field pods-textarea">
    <?php
    echo PodsForm::label( 'code4', __( 'WYSIWYG (tinymce)', 'pods' ) );
    echo PodsForm::field( 'code4', "Yay! It's TinyMCE!", 'tinymce' );
    //wp_editor("<strong>Yay, it's TinyMCE!</strong>", 'pods-field-code4');
    ?>
</li>

<li class="pods-field pods-textfield pods-date">
    <?php
    echo PodsForm::label( 'date1', __( 'Date', 'pods' ) );
    echo PodsForm::field( 'date1', NULL, 'text', array( 'class' => 'pods-date-field' ) );
    ?>
    <script>
        jQuery( function () {
            jQuery( '#pods-form-ui-date1' ).datepicker();
        } );
    </script>
</li>

<li class="pods-field pods-textfield pods-date">
    <?php
    echo PodsForm::label( 'date2', __( 'Date with Comment', 'pods' ) );
    echo PodsForm::field( 'date2', NULL, 'text', array( 'class' => 'pods-date-field' ) );
    echo PodsForm::comment( 'Please select a date' );
    ?>
    <script>
        jQuery( function () {
            jQuery( '#pods-form-ui-date2' ).datepicker();
        } );
    </script>
</li>

<li class="pods-field pods-textfield pods-date">
    <?php
    echo PodsForm::label( 'date3', __( 'Date with Time', 'pods' ) );
    echo PodsForm::field( 'date3', NULL, 'text', array( 'class' => 'pods-date-field' ) );
    ?>
    <script>
        jQuery( function () {
            jQuery( '#pods-form-ui-date3' ).datetimepicker();
        } );
    </script>
</li>

<li class="pods-field pods-textfield">
    <?php
    echo PodsForm::label( 'number1', __( 'Number Field', 'pods' ) );
    echo PodsForm::field( 'number1', NULL, 'number' );
    ?>
</li>

<li class="pods-field pods-textfield">
    <?php
    echo PodsForm::label( 'number2', __( 'Number with Comment', 'pods' ) );
    echo PodsForm::field( 'number2', NULL, 'number', array( 'decimals' => 1 ) );
    echo PodsForm::comment( 'Please fill out the field' );
    ?>
</li>

<li class="pods-field pods-textfield pods-slider">
    <?php
    echo PodsForm::label( 'slider1', __( 'Slider Default', 'pods' ) );
    ?>
    <div class="pods-slider-field" id="pods-field-slider1"></div>
    <div id="pods-field-slider1-amount-display" class="pods-slider-field-display"></div>
    <?php echo PodsForm::field( 'slider1', NULL, 'hidden' ); ?>
    <script>
        jQuery( function ( $ ) {
            $( '#pods-field-slider1' ).slider( {
                                                   range : false,
                                                   value : 0,
                                                   orientation : 'horizontal',
                                                   min : 0,
                                                   max : 100,
                                                   step : 1,
                                                   slide : function ( evt, ui ) {
                                                       $( '#pods-form-ui-slider1' ).val( ui.value );
                                                       $( '#pods-field-slider1-amount-display' ).html( ui.value );
                                                   }
                                               } );
            $( '#pods-form-ui-slider1' ).val( $( '#pods-field-slider1' ).slider( 'value' ) );
            $( '#pods-field-slider1-amount-display' ).html( $( '#pods-field-slider1' ).slider( 'value' ) );
        } );
    </script>
</li>

<li class="pods-field pods-boolean">
    <?php
    echo PodsForm::label( 'boolean1', __( 'Boolean', 'pods' ) );
    echo PodsForm::field( 'boolean1', NULL, 'boolean' );
    ?>
</li>

<li class="pods-field pods-boolean">
    <?php
    echo PodsForm::label( 'boolean2', __( 'Boolean with Comment', 'pods' ) );
    echo PodsForm::field( 'boolean2', NULL, 'boolean' );
    echo PodsForm::comment( 'Please check this field' );
    ?>
</li>

<li class="pods-field pods-file pods-plupload-context" id="field-pods-field-file9">
    <?php echo PodsForm::label( 'file9', __( 'File via Form API', 'pods' ) ); ?>
    <?php
    echo PodsForm::field( 'file9', null, 'file', array(
        'file_format_type' => 'multiple',
        'file_uploader' => 'plupload',
        'file_limit' => 2,
        'file_type' => 'images'
    ) );
    ?>
</li>

<li class="pods-field pods-file pods-plupload-context" id="field-pods-field-file10">
    <?php echo PodsForm::label( 'file10', __( 'Single File via Form API', 'pods' ) ); ?>
    <br />
    <?php
    echo PodsForm::field( 'file10', null, 'file', array(
        'file_format_type' => 'single',
        'file_uploader' => 'plupload',
        'file_limit' => 1,
        'file_type' => 'images'
    ) );
    ?>
</li>

<li class="pods-field pods-file pods-plupload-context" id="field-pods-field-file9">
    <?php echo PodsForm::label( 'file11', __( 'Attachment via Form API', 'pods' ) ); ?>
    <?php
    echo PodsForm::field( 'file11', null, 'file', array(
        'file_format_type' => 'multiple',
        'file_uploader' => 'attachment',
        'file_limit' => 2,
        'file_type' => 'images'
    ) );
    ?>
</li>

<li class="pods-field pods-file pods-plupload-context" id="field-pods-field-file10">
    <?php echo PodsForm::label( 'file12', __( 'Single Attachment via Form API', 'pods' ) ); ?>
    <br />
    <?php
    echo PodsForm::field( 'file12', null, 'file', array(
        'file_format_type' => 'single',
        'file_uploader' => 'attachment',
        'file_limit' => 1,
        'file_type' => 'images'
    ) );
    ?>
</li>

<!-- Pods Pick Field -->
<li class="pods-field pods-pick" id="field-pods-field-pick1">
    <?php
    echo PodsForm::label( 'pick1', __( 'Pick', 'pods' ) );
    echo PodsForm::field( 'pick1', NULL, 'pick', array(
        'data' => array(
            '' => '-- Select One --',
            'option_1' => 'Choice 1',
            'option_2' => 'Choice 2',
            'option_3' => 'Choice 3'
        )
    ) );
    ?>
</li>
<!-- /.pods-field.pods-pick -->

<li class="pods-field pods-pick" id="field-pods-field-pick2">
    <?php
    echo PodsForm::label( 'pick2', __( 'Pick with Comment', 'pods' ) );
    echo PodsForm::field( 'pick2', NULL, 'pick', array(
        'data' => array(
            '' => '-- Select One --',
            'option_1' => 'Choice 1',
            'option_2' => 'Choice 2',
            'option_3' => 'Choice 3'
        )
    ) );
    echo PodsForm::comment( 'Please select one' );
    ?>
</li>
<!-- /.pods-field.pods-pick -->

<?php // FIXME: Figure out why 'multiple' doesn't get through the attributes merge ?>
<li class="pods-field pods-pick" id="field-pods-field-pick3">
    <?php
    echo PodsForm::label( 'pick3', __( 'Pick Multiple', 'pods' ) );
    echo PodsForm::field( 'pick3', NULL, 'pick', array(
        'data' => array(
            'option_1' => 'Choice 1',
            'option_2' => 'Choice 2',
            'option_3' => 'Choice 3',
            'option_4' => 'Choice 4',
            'option_5' => 'Choice 5'
        ),
        'attributes' => array(
            'multiple' => true
        )
    ) );
    ?>
</li>
<!-- /.pods-field.pods-pick -->

<li class="pods-field pods-pick" id="field-pods-field-pick4">
    <?php
    echo PodsForm::label( 'pick4', __( 'Pick - Checkboxes', 'pods' ) );
    $pick_opts = array(
        'option_1' => 'Choice 1',
        'option_2' => 'Choice 2',
        'option_3' => 'Choice 3'
    );
    $i = 0;
    ?>
    <div class="pods-pick-values pods-pick-checkbox">
        <ul>
            <?php foreach ( $pick_opts as $opt => $label ): ?>
            <?php $i++; ?>
            <li>
                <div class="pods-field pods-boolean">
                    <?php
                    echo PodsForm::field( 'pick4-' . $i, $opt, 'pick_checkbox' );
                    echo PodsForm::label( 'pick4-' . $i, $label );
                    ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</li>
<!-- /.pods-field.pods-pick -->

<li class="pods-field pods-pick" id="field-pods-field-pick5">
    <?php
    echo PodsForm::label( 'pick5', __( 'Pick - jQuery Chosen Autocomplete', 'pick' ) );
    echo PodsForm::field( 'pick5', NULL, 'pick', array(
        'class' => 'chosen',
        'data' => array(
            'option_1' => 'Choice 1',
            'option_2' => 'Choice 2',
            'option_3' => 'Choice 3',
            'option_4' => 'Choice 4',
            'option_5' => 'Choice 5'
        )
    ) );
    ?>
</li>
<!-- /#field-pods-field-pick5 -->

<!-- Pick - Radio -->
<li class="pods-field pods-pick" id="field-pods-field-pick6">
    <?php
    echo PodsForm::label( 'pick6', __( 'Pick - Radio Buttons', 'pods' ) );
    ?>
    <div class="pods-pick-values pods-pick-radio">
        <ul>
            <?php $i = 0; ?>
            <?php foreach ( $pick_opts as $opt => $label ): ?>
            <?php $i++; ?>
            <li>
                <div class="pods-field pods-boolean" id="field-pods-field-pick6-<?php echo $i; ?>">
                    <input type="radio" name="pods-field-pick6" id="pods-form-ui-pick6-<?php echo $i; ?>" value="<?php echo $opt; ?>" />
                    <?php echo PodsForm::label( 'pick6-' . $i, $label ); ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</li>
<!-- /#field-pods-field-pick6 -->

<!-- WP Auto Complete Categories -->
<li class="pods-field pods-textfield" id="field-pods-field-wpcategories">
    <?php
    echo PodsForm::label( 'wpcategories', __( 'WordPress Auto Complete Categories', 'pods' ) );
    echo PodsForm::field( 'wpcategories', NULL, 'text' );
    ?>
    <script>
        jQuery( function ( $ ) {
            var availableTags = [
                "ActionScript",
                "AppleScript",
                "Asp",
                "BASIC",
                "C",
                "C++",
                "Clojure",
                "COBOL",
                "ColdFusion",
                "Erlang",
                "Fortran",
                "Groovy",
                "Haskell",
                "Java",
                "JavaScript",
                "Lisp",
                "Perl",
                "PHP",
                "Python",
                "Ruby",
                "Scala",
                "Scheme"
            ];
            $( '#pods-form-ui-wpcategories' ).autocomplete( {
                                                                source : availableTags
                                                            } );
        } );
    </script>
</li>
<!-- /#field-pods-field-wpcategories -->

<!-- Pick Field: Select2 -->
<li>
    <?php echo PodsForm::label( 'pick7', __( 'Autocomplete: Select2', 'pods' ) ); ?>
    <?php echo PodsForm::field( 'pick7', null, 'pick', array(
    'pick_format_type' => 'single',
    'pick_format_single' => 'autocomplete'
) ); ?>
</li>

<?php

// TODO: Add slider-configured field once we write a PodsForm::field_slider method
// slider configured
//$args = array( 'type' => 'number', 'name' => 'slider2', 'label' => 'Slider Configured (stepped)', 'options' => array ('slider' => true, 'value' => 100, 'minnumber' => 0, 'maxnumber' => 500, 'step' => 50), 'comment' => 'Demonstrates Configured Values' );
//pods_field( $args );

// TODO: Make attributes pass multiple="true" to pick fields
//$args = array( 'type' => 'pick', 'name' => 'pick3', 'label' => 'Pick - Multi', 'options' => array( 'type' => 'multi' ) );
//pods_field( $args );
?>
</ol>
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