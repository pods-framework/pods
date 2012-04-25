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
                        <?php if ( !empty( $params['options']['autocomplete'] ) && $params['options']['autocomplete'] == true ) : ?>
                            <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" class="pods-text-field" type="text" />
                        <?php else : ?>
                            <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" class="pods-text-field" type="text" value="" />
                        <?php endif; ?>
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ( !empty( $params['options']['autocomplete'] ) ) : ?>
                        <script>
                            jQuery(function($) {
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
                                $( "#pods-field-<?php echo $params['name']; ?>" ).autocomplete({
                                    source: availableTags
                                });
                            });
                        </script>
                    <?php endif; ?>
                    <!-- /.pods-textfield -->
                <?php
                break;
            case 'code':
                ?>
                    <div class="pods-field pods-textarea" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <textarea name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" class="pods-code-field" rows="7" cols="70"></textarea>
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-textfield -->
                <?php
                break;
            case 'html':
                wp_editor("<strong>Yay, it's TinyMCE!</strong>", 'pods-field-'. $params['name']);
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
                if ( !empty( $params['options']['slider'] ) && $params['options']['slider'] == true) : ?>
                    <div class="pods-field pods-textfield pods-slider" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <div id="pods-field-<?php echo $params['name']; ?>" class="pods-slider-field" /></div>
                        <div id="pods-field-<?php echo $params['name']; ?>-amount-display"  class="pods-slider-field-display" /></div>
                        <input name="pods-field-<?php echo $params['name']; ?>-amount-hidden" id="pods-field-<?php echo $params['name']; ?>-amount-hidden" type="hidden" value="" />
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <script>
                        jQuery(function($) {
                            $( "#pods-field-<?php echo $params['name']; ?>" ).slider({
                                <?php if ( !empty( $params['options']['range'] ) ) : ?>
                                    range:<?php echo $params['options']['range']; ?>,
                                <?php else: ?>
                                    range: false,
                                <?php endif; ?>

                                <?php if ( !empty( $params['options']['value'] ) ) : ?>
                                    value:<?php echo $params['options']['value']; ?>,
                                <?php else: ?>
                                    value: 0,
                                <?php endif; ?>

                                <?php if ( !empty( $params['options']['range'] ) ) : ?>
                                    <?php if ( !empty( $params['options']['values'] ) ) : ?>
                                        values: [<?php echo $params['options']['values']; ?>],
                                    <?php else: ?>
                                        values: [0,
                                            <?php if ( !empty( $params['options']['maxnumber'] ) ) : ?>
                                                <?php echo $params['options']['maxnumber']; ?>
                                            <?php else: ?>
                                                100
                                            <?php endif; ?>
                                        ],
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ( !empty( $params['options']['orientation'] ) ): ?>
                                    orientation : "<?php echo $params['options']['orientation']; ?>",
                                <?php else : ?>
                                    orientation : "horizontal",
                                <?php endif; ?>

                                <?php if ( !empty( $params['options']['minnumber'] ) ) : ?>
                                    min:<?php echo $params['options']['minnumber']; ?>,
                                <?php else: ?>
                                    min: 0,
                                <?php endif; ?>

                                <?php if ( !empty( $params['options']['maxnumber'] ) ) : ?>
                                    max:<?php echo $params['options']['maxnumber']; ?>,
                                <?php else: ?>
                                    max: 100,
                                <?php endif; ?>

                                <?php if ( !empty( $params['options']['step'] ) ) : ?>
                                    step:<?php echo $params['options']['step']; ?>,
                                <?php else: ?>
                                    step: 1,
                                <?php endif; ?>

                                slide: function( event, ui ) {
                                <?php if ( !empty( $params['options']['range'] ) ) : ?>
                                    $( "#pods-field-<?php echo $params['name']; ?>-amount-hidden" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
                                    $( "#pods-field-<?php echo $params['name']; ?>-amount-display" ).html( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
                                <?php else : ?>
                                    $( "#pods-field-<?php echo $params['name']; ?>-amount-hidden" ).val( ui.value );
                                    $( "#pods-field-<?php echo $params['name']; ?>-amount-display" ).html( ui.value );
                                <?php endif; ?>
                                }
                            });
                            <?php if ( !empty( $params['options']['range'] ) ) : ?>
                                $( "#pods-field-<?php echo $params['name']; ?>-amount-hidden" ).val( $( "#pods-field-<?php echo $params['name']; ?>" ).slider( "values", 0 ) + " - " + $( "#pods-field-<?php echo $params['name']; ?>" ).slider( "values", 1 ) );
                                $( "#pods-field-<?php echo $params['name']; ?>-amount-display" ).html( $( "#pods-field-<?php echo $params['name']; ?>" ).slider( "values", 0 ) + " - " + $( "#pods-field-<?php echo $params['name']; ?>" ).slider( "values", 1 ) );
                            <?php else : ?>
                                $( "#pods-field-<?php echo $params['name']; ?>-amount-hidden" ).val( $( "#pods-field-<?php echo $params['name']; ?>" ).slider( "value" ) );
                                $( "#pods-field-<?php echo $params['name']; ?>-amount-display" ).html( $( "#pods-field-<?php echo $params['name']; ?>" ).slider( "value" ) );
                            <?php endif; ?>
                        });
                    </script>
                    <!-- /.pods-slider -->
                <?php else : ?>
                    <div class="pods-field pods-textfield pods-number" id="field-pods-field-<?php echo $params['name']; ?>">
                        <label for="pods-field-<?php echo $params['name']; ?>"><?php echo $params['label']; ?></label>
                        <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>" class="pods-number-field" type="text" value="" />
                        <?php if(!empty($params['comment'])) : ?>
                            <p class="pods-field-comment"><?php echo $params['comment']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- /.pods-number -->
                <?php endif;
                break;
            case 'boolean':
                ?>
                    <div class="pods-field pods-boolean" id="field-pods-field-<?php echo $params['name']; ?>">
                        <input name="pods-field-<?php echo $params['name']; ?>" id="pods-field-<?php echo $params['name']; ?>"  class="pods-boolean-field" type="checkbox" value="1" />
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
<form action="" method="post" class="pods-manage pod-event">
    <div id="poststuff" class="metabox-holder has-right-sidebar"> <!-- class "has-right-sidebar" preps for a sidebar... always present? -->
        <div id="side-info-column" class="inner-sidebar">
            <div id="side-sortables" class="meta-box-sortables ui-sortable">
                <!-- BEGIN PUBLISH DIV -->
                <div id="submitdiv" class="postbox">
                    <div class="handlediv" title="Click to toggle"><br /></div>
                    <h3 class="hndle"><span>Manage</span></h3>
                    <div class="inside">
                        <div class="submitbox" id="submitpost">
                            <div id="minor-publishing"><!--
                                <div id="minor-publishing-actions">
                                    <div id="save-action">
                                        <input type="submit" name="save" id="save-post" value="Save" class="button button-highlighted" />
                                    </div>
                                    -->
                                    <!-- /#save-action --><!--
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
                            <ol class="form-fields">
                                <li class="pods-field pods-textfield">
                                    <?php
                                    echo PodsForm::label('singlelinetext1', 'Single Line Text');
                                    echo PodsForm::field('singlelinetext1', NULL, 'text', array('class' => 'pods-text-field'));
                                    ?>
                                </li>

                                <li class="pods-field pods-textfield">
                                    <?php
                                    echo PodsForm::label('singlelinetext2', 'Single Line Text with Comment');
                                    echo PodsForm::field('singlelinetext2', NULL, 'text', array('class' => 'pods-text-field'));
                                    echo PodsForm::comment('Please fill out the field');
                                    ?>
                                </li>
                                <li class="pods-field pods-textarea">
                                    <?php
                                    echo PodsForm::label('code1', 'Code Field');
                                    echo PodsForm::field('code1', NULL, 'textarea', array('class' => 'pods-code-field', 'attributes' => array('rows' => '7', 'cols' => '70')));
                                    ?>
                                </li>
                                <li class="pods-field pods-textarea">
                                    <?php
                                    echo PodsForm::label('code2', 'Code Field w/ Comment');
                                    echo PodsForm::field('code2', NULL, 'textarea', array('class' => 'pods-code-field', 'attributes' => array('rows' => '7', 'cols' => '70')));
                                    echo PodsForm::comment('Enter some code');
                                    ?>
                                </li>
                                <li class="pods-field pods-textarea">
                                    <?php
                                    echo PodsForm::label('code3', 'WYSIWYG (cleditor)');
                                    echo PodsForm::field('code3', NULL, 'textarea', array('class' => 'pods-code-field', 'attributes' => array('rows' => '7', 'cols' => '70', 'data-width' => '100%')));
                                    ?>
                                </li>

                                <li class="pods-field pods-textarea">
                                    <?php
                                    echo PodsForm::label('code4', 'WYSIWYG (tinymce)');
                                    //echo PodsForm::field('code4', "Yay! It's TinyMCE!", 'tinymce', array('class' => 'pods-code-field'));
                                    wp_editor("<strong>Yay, it's TinyMCE!</strong>", 'pods-field-code4');
                                    ?>
                                </li>

                                <li class="pods-field pods-textfield pods-date">
                                    <?php
                                    echo PodsForm::label('date1', 'Date');
                                    echo PodsForm::field('date1', NULL, 'text', array('class' => 'pods-date-field'));
                                    ?>
                                    <script>
                                    jQuery(function() {
                                        jQuery('#pods-form-ui-date1').datepicker();
                                    });
                                    </script>
                                </li>

                                <li class="pods-field pods-textfield pods-date">
                                    <?php
                                    echo PodsForm::label('date2', 'Date with Comment');
                                    echo PodsForm::field('date2', NULL, 'text', array('class' => 'pods-date-field'));
                                    echo PodsForm::comment('Please select a date');
                                    ?>
                                    <script>
                                    jQuery(function() {
                                        jQuery('#pods-form-ui-date2').datepicker();
                                    });
                                    </script>
                                </li>

                                <li class="pods-field pods-textfield pods-date">
                                    <?php
                                    echo PodsForm::label('date3', 'Date with Time');
                                    echo PodsForm::field('date3', NULL, 'text', array('class' => 'pods-date-field'));
                                    ?>
                                    <script>
                                    jQuery(function() {
                                        jQuery('#pods-form-ui-date3').datetimepicker();
                                    });
                                    </script>
                                </li>

                                <li class="pods-field pods-textfield">
                                    <?php
                                    echo PodsForm::label('number1', 'Number Field');
                                    echo PodsForm::field('number1', NULL, 'number');
                                    ?>
                                </li>

                                <li class="pods-field pods-textfield">
                                    <?php
                                    echo PodsForm::label('number2', 'Number with Comment');
                                    echo PodsForm::field('number2', NULL, 'number', array('decimals' => 1));
                                    echo PodsForm::comment('Please fill out the field');
                                    ?>
                                </li>

                                <li class="pods-field pods-textfield pods-slider">
                                    <?php
                                    echo PodsForm::label('slider1', 'Slider Default');
                                    ?>
                                    <div class="pods-slider-field" id="pods-field-slider1"></div>
                                    <div id="pods-field-slider1-amount-display" class="pods-slider-field-display"></div>
                                    <?php echo PodsForm::field('slider1', NULL, 'hidden'); ?>
                                    <script>
                                        jQuery(function($) {
                                            $('#pods-field-slider1').slider({
                                                range: false,
                                                value: 0,
                                                orientation: 'horizontal',
                                                min: 0,
                                                max: 100,
                                                step: 1,
                                                slide: function(evt, ui) {
                                                    $('#pods-form-ui-slider1').val( ui.value );
                                                    $('#pods-field-slider1-amount-display').html( ui.value );
                                                }
                                            });
                                            $('#pods-form-ui-slider1').val( $('#pods-field-slider1').slider('value') );
                                            $('#pods-field-slider1-amount-display').html( $('#pods-field-slider1').slider('value') );
                                        });
                                    </script>
                                </li>

                                <li class="pods-field pods-boolean">
                                    <?php
                                    echo PodsForm::label('boolean1', 'Boolean');
                                    echo PodsForm::field('boolean1', NULL, 'boolean');
                                    ?>
                                </li>

                                <li class="pods-field pods-boolean">
                                    <?php
                                    echo PodsForm::label('boolean2', 'Boolean with Comment');
                                    echo PodsForm::field('boolean2', NULL, 'boolean');
                                    echo PodsForm::comment('Please check this field');
                                    ?>
                                </li>

                                <!-- File Upload Field -->
                                <li class="pods-field pods-file pods-file-context" id="field-pods-field-file1">
                                    <?php echo PodsForm::label('file1', 'File Upload'); ?>
                                    <ul class="pods-files">
                                        <?php for($i=0; $i < 3; $i++): ?>
                                            <li class="media-item">
                                                <span class="pods-file-reorder"><img src="<?php echo PODS_URL . 'ui/images/handle.gif'; ?>" alt="drag to reorder" /></span>
                                                <span class="pods-file-thumb">
                                                    <span>
                                                        <img class="pinkynail" src="<?php echo PODS_URL . 'ui/images/icon32.png'; ?>" alt="Thumbnail" />
                                                        <?php echo PodsForm::field('file1[files]', NULL, 'hidden'); ?>
                                                    </span>
                                                </span>
                                                <span class="pods-file-name">Sample Image</span>
                                                <span class="pods-file-remove">
                                                    <img src="<?php echo PODS_URL . 'ui/images/del.png'; ?>" alt="" class="pods-icon-minus" />
                                                </span>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                    <p class="pods-add-file">
                                        <a href="media-upload.php?type=image&amp;TB_iframe=1&amp;width=640&amp;height=1500" class="button">Add New</a>
                                    </p>
                                </li>

                                <!-- File Upload Field w/ Comment -->
                                <li class="pods-field pods-file pods-file-context" id="field-pods-field-file2">
                                    <?php echo PodsForm::label('file2', 'File Upload with Comment'); ?>
                                    <ul class="pods-files">
                                        <?php for($i=0; $i < 3; $i++): ?>
                                            <li class="media-item">
                                                <span class="pods-file-reorder"><img src="<?php echo PODS_URL . 'ui/images/handle.gif'; ?>" alt="drag to reorder" /></span>
                                                <span class="pods-file-thumb">
                                                    <span>
                                                        <img class="pinkynail" src="<?php echo PODS_URL . 'ui/images/icon32.png'; ?>" alt="Thumbnail" />
                                                        <?php echo PodsForm::field('file2[files]', NULL, 'hidden'); ?>
                                                    </span>
                                                </span>
                                                <span class="pods-file-name">Sample Image</span>
                                                <span class="pods-file-remove">
                                                    <img src="<?php echo PODS_URL . 'ui/images/del.png'; ?>" alt="" class="pods-icon-minus" />
                                                </span>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                    <p class="pods-add-file">
                                        <a href="media-upload.php?type=image&amp;TB_iframe=1&amp;width=640&amp;height=1500" class="button">Add New</a>
                                    </p>
                                    <?php echo PodsForm::comment('File Upload Details'); ?>
                                </li><!-- /#field-pods-field-file2 -->

                                <!-- Pods Pick Field -->
                                <li class="pods-field pods-pick" id="field-pods-field-pick1">
                                    <?php
                                    echo PodsForm::label('pick1', 'Pick');
                                    echo PodsForm::field('pick1', NULL, 'pick', array(
                                        'data' => array(
                                            '' => '-- Select One --',
                                            'option_1' => 'Choice 1',
                                            'option_2' => 'Choice 2',
                                            'option_3' => 'Choice 3'
                                        )
                                    ));
                                    ?>
                                </li><!-- /.pods-field.pods-pick -->

                                <li class="pods-field pods-pick" id="field-pods-field-pick2">
                                    <?php
                                    echo PodsForm::label('pick2', 'Pick with Comment');
                                    echo PodsForm::field('pick2', NULL, 'pick', array(
                                        'data' => array(
                                            '' => '-- Select One --',
                                            'option_1' => 'Choice 1',
                                            'option_2' => 'Choice 2',
                                            'option_3' => 'Choice 3'
                                        )
                                    ));
                                    echo PodsForm::comment('Please select one');
                                    ?>
                                </li><!-- /.pods-field.pods-pick -->

                                <?php // FIXME: Figure out why 'multiple' doesn't get through the attributes merge ?>
                                <li class="pods-field pods-pick" id="field-pods-field-pick3">
                                    <?php
                                    echo PodsForm::label('pick3', 'Pick Multiple');
                                    echo PodsForm::field('pick3', NULL, 'pick', array(
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
                                    ));
                                    ?>
                                </li><!-- /.pods-field.pods-pick -->

                                <li class="pods-field pods-pick" id="field-pods-field-pick4">
                                    <?php
                                    echo PodsForm::label('pick4', 'Pick - Checkboxes');
                                    $pick_opts = array(
                                        'option_1' => 'Choice 1',
                                        'option_2' => 'Choice 2',
                                        'option_3' => 'Choice 3'
                                    );
                                    $i = 0;
                                    ?>
                                    <div class="pods-pick-values pods-pick-checkbox">
                                        <ul>
                                            <?php foreach ($pick_opts as $opt => $label): ?>
                                                <?php $i++; ?>
                                                <li>
                                                    <div class="pods-field pods-boolean">
                                                        <?php
                                                        echo PodsForm::field('pick4-'.$i, $opt, 'pick_checkbox');
                                                        echo PodsForm::label('pick4-'.$i, $label);
                                                        ?>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </li><!-- /.pods-field.pods-pick -->

                                <li class="pods-field pods-pick" id="field-pods-field-pick5">
                                    <?php
                                    echo PodsForm::label('pick5', 'Pick - jQuery Chosen Autocomplete');
                                    echo PodsForm::field('pick5', NULL, 'pick', array(
                                        'class' => 'chosen',
                                        'data' => array(
                                            'option_1' => 'Choice 1',
                                            'option_2' => 'Choice 2',
                                            'option_3' => 'Choice 3',
                                            'option_4' => 'Choice 4',
                                            'option_5' => 'Choice 5'
                                        )
                                    ));
                                    ?>
                                </li><!-- /#field-pods-field-pick5 -->

                                <!-- Pick - Radio -->
                                <li class="pods-field pods-pick" id="field-pods-field-pick6">
                                    <?php
                                    echo PodsForm::label('pick6', 'Pick - Radio Buttons');
                                    ?>
                                    <div class="pods-pick-values pods-pick-radio">
                                        <ul>
                                            <?php $i = 0; ?>
                                            <?php foreach ($pick_opts as $opt => $label): ?>
                                                <?php $i++; ?>
                                                <li>
                                                    <div class="pods-field pods-boolean" id="field-pods-field-pick6-<?php echo $i; ?>">
                                                        <input type="radio" name="pods-field-pick6" id="pods-form-ui-pick6-<?php echo $i; ?>" value="<?php echo $opt; ?>" />
                                                        <?php echo PodsForm::label('pick6-'.$i, $label); ?>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </li><!-- /#field-pods-field-pick6 -->

                                <!-- WP Auto Complete Categories -->
                                <li class="pods-field pods-textfield" id="field-pods-field-wpcategories">
                                    <?php
                                    echo PodsForm::label('wpcategories', 'WordPress Auto Complete Categories');
                                    echo PodsForm::field('wpcategories', NULL, 'text');
                                    ?>
                                    <script>
                                        jQuery(function($) {
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
                                            $('#pods-form-ui-wpcategories').autocomplete({
                                                source: availableTags
                                            });
                                        });
                                    </script>
                                </li><!-- /#field-pods-field-wpcategories -->

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