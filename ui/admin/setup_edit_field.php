<?php
$field = array_merge($field_settings['field_defaults'], $field);
?>
        <tr id="row-<?php echo $i; ?>" class="pods-manage-row pods-field-<?php echo esc_attr(pods_var('name', $field)); ?>" valign="top" data-row="<?php echo $i; ?>">
            <th scope="row" class="check-field pods-manage-sort">
                <img src="<?php echo PODS_URL; ?>/ui/images/handle.gif" alt="Move" />
            </th>
            <td class="pods-manage-row-label">
                <strong>
                    <a class="pods-manage-row-edit row-label" title="Edit this field" href="#edit-field">
                        <?php echo esc_html(pods_var('label', $field)); ?>
                    </a>
                    <abbr title="required" class="required<?php echo (1 == pods_var('required', $field) ? '' : ' hidden'); ?>">*</abbr>
                </strong>
<?php
if ('__1' != pods_var('id', $field)) {
?>
                <span class="pods-manage-row-more">
                    [id: <?php echo esc_html(pods_var('id', $field)); ?>]
                </span>
<?php
}
?>
                <div class="row-actions">
                    <span class="edit">
                        <a title="Edit this field" class="pods-manage-row-edit" href="#edit-field"><?php _e('Edit'); ?></a> |
                    </span>
                    <span class="pods-manage-row-delete">
                        <a class="submitdelete" title="Delete this field" href="#delete-field"><?php _e('Delete'); ?></a>
                    </span>
                </div>
                <div class="pods-manage-row-wrapper" id="pods-manage-field-<?php echo $i; ?>"><?php
if ('__1' != pods_var('id', $field)) {
?>
                        <input type="hidden" name="field_data[<?php echo $i; ?>][id]" value="<?php echo pods_var('id', $field); ?>" />
<?php
}
?>
                        <div class="pods-manage-field pods-dependency">
                            <div class="pods-tabbed">
                                <ul class="pods-tabs">
                                    <li class="pods-tab"><a href="#pods-basic-options-<?php echo $i; ?>" class="selected"><?php _e('Basic'); ?></a></li>
                                    <li class="pods-tab"><a href="#pods-additional-field-options-<?php echo $i; ?>"><?php _e('Additional Field Options'); ?></a></li>
                                    <li class="pods-tab"><a href="#pods-advanced-options-<?php echo $i; ?>"><?php _e('Advanced'); ?></a></li>
                                </ul>
                                <div class="pods-tab-group">
                                    <div id="pods-basic-options-<?php echo $i; ?>" class="pods-tab pods-basic-options">
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][name]', __('Name', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][name]', pods_var('name', $field), 'db'); ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][label]', __('Label', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][label]', pods_var('label', $field), 'text'); ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][description]', __('Description', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][description]', pods_var('description', $field), 'text'); ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][type]', __('Field Type', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][type]', pods_var('type', $field), 'pick', array('data' => pods_var('field_types', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                        </div>
                                        <div class="pods-field-option-container pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-pick">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_object]', __('Related To', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_object]', pods_var('pick_object', $field), 'pick', array('data' => pods_var('pick_object', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-custom-simple">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_custom]', __('Custom Defined Options', 'pods'), __('One option per line, use value|Label for separate values and labels')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_custom]', pods_var('pick_custom', $field), 'textarea'); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-pod-event">
                                                <?php echo PodsForm::label('field_data[' . $i . '][sister_field_id]', __('Bi-Directional Related', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][sister_field_id]', pods_var('sister_field_id', $field), 'pick', array('data' => pods_var('sister_field_id', $field_settings))); ?>
                                            </div>
                                        </div>
                                        <div class="pods-field-option-group">
                                            <p class="pods-field-option-group-label">
                                                <?php _e('Options'); ?>
                                            </p>
                                            <div class="pods-field-option-group-values">
                                                <div class="pods-field-option-group-value">
                                                    <?php echo PodsForm::field('field_data[' . $i . '][required]', pods_var('required', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                    <?php echo PodsForm::label('field_data[' . $i . '][required]', __('Required', 'pods'), __('help', 'pods')); ?>
                                                </div>
                                                <div class="pods-field-option-group-value">
                                                    <?php echo PodsForm::field('field_data[' . $i . '][unique]', pods_var('unique', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                    <?php echo PodsForm::label('field_data[' . $i . '][unique]', __('Unique', 'pods'), __('help', 'pods')); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="pods-additional-field-options-<?php echo $i; ?>" class="pods-tab pods-additional-field-options">
                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-date">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][date_format_type]', __('Format Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][date_format_type]', pods_var('date_format_type', $field), 'pick', array('data' => pods_var('date_format_type', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-date-format-type pods-depends-on-field-data-date-format-type-date pods-depends-on-field-data-date-format-type-datetime">
                                                <?php echo PodsForm::label('field_data[' . $i . '][date_format_date]', __('Date Format', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][date_format_date]', pods_var('date_format', $field), 'pick', array('data' => pods_var('date_format', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-date-format-type pods-depends-on-field-data-date-format-type-datetime pods-depends-on-field-data-date-format-type-time">
                                                <?php echo PodsForm::label('field_data[' . $i . '][date_time_type]', __('Time Format Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][date_time_type]', pods_var('date_time_type', $field), 'pick', array('data' => pods_var('date_time_type', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-date-format-type pods-depends-on-field-data-date-format-type-datetime pods-depends-on-field-data-date-format-type-time pods-depends-on-field-data-date-time-type pods-depends-on-field-data-date-time-type-12">
                                                <?php echo PodsForm::label('field_data[' . $i . '][date_format_time]', __('Time Format', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][date_format_time]', pods_var('date_time_format', $field), 'pick', array('data' => pods_var('date_time_format', $field_settings))); ?>
                                            </div>
                                        </div>

                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-text">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][text_format_type]', __('Format Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][text_format_type]', pods_var('text_format_type', $field), 'pick', array('data' => pods_var('text_format_type', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-text-format-type pods-depends-on-field-data-text-format-type-website">
                                                <?php echo PodsForm::label('field_data[' . $i . '][text_format_website]', __('Format', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][text_format_website]', pods_var('text_format_website', $field), 'pick', array('data' => pods_var('text_format_website', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-field-data-text-format-type pods-depends-on-field-data-text-format-type-phone">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('field_data[' . $i . '][text_format_phone]', __('Format', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('field_data[' . $i . '][text_format_phone]', pods_var('text_format_phone', $field), 'pick', array('data' => pods_var('text_format_phone', $field_settings))); ?>
                                                </div>
                                                <div class="pods-field-option-group">
                                                    <p class="pods-field-option-group-label">
                                                        <?php _e('Phone Options'); ?>
                                                    </p>
                                                    <div class="pods-field-option-group-values">
                                                        <div class="pods-field-option-group-value">
                                                            <?php echo PodsForm::field('field_data[' . $i . '][text_enable_phone_extension]', pods_var('text_enable_phone_extension', $field), 'boolean'); ?>
                                                            <?php echo PodsForm::label('field_data[' . $i . '][text_enable_phone_extension]', __('Enable Phone Extension?', 'pods'), __('help', 'pods')); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pods-field-option-group">
                                                <p class="pods-field-option-group-label">
                                                    <?php _e('Output Options'); ?>
                                                </p>
                                                <div class="pods-field-option-group-values">
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('field_data[' . $i . '][text_allow_html]', pods_var('text_allow_html', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                        <?php echo PodsForm::label('field_data[' . $i . '][text_allow_html]', __('Allow HTML?', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('field_data[' . $i . '][text_allow_shortcode]', pods_var('text_allow_shortcode', $field), 'boolean'); ?>
                                                        <?php echo PodsForm::label('field_data[' . $i . '][text_allow_shortcode]', __('Allow Shortcodes?', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-text-allow-html">
                                                <?php echo PodsForm::label('field_data[' . $i . '][text_allowed_html_tags]', __('Allowed HTML Tags', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][text_allowed_html_tags]', pods_var('text_allowed_html_tags', $field), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][text_max_length]', __('Max Length', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][text_max_length]', pods_var('text_max_length', $field), 'number'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][text_size]', __('Field Size', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][text_size]', pods_var('text_size', $field), 'pick', array('data' => pods_var('text_size', $field_settings))); ?>
                                            </div>
                                        </div>

                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-paragraph">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][paragraph_editor]', __('Text Editor', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][paragraph_editor]', pods_var('paragraph_editor', $field), 'pick', array('data' => pods_var('paragraph_editor', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option-group">
                                                <p class="pods-field-option-group-label">
                                                    <?php _e('Output Options'); ?>
                                                </p>
                                                <div class="pods-field-option-group-values">
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('field_data[' . $i . '][paragraph_allow_html]', pods_var('paragraph_allow_html', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                        <?php echo PodsForm::label('field_data[' . $i . '][paragraph_allow_html]', __('Allow HTML?', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('field_data[' . $i . '][paragraph_allow_markdown]', pods_var('paragraph_allow_markdown', $field), 'boolean'); ?>
                                                        <?php echo PodsForm::label('field_data[' . $i . '][paragraph_allow_markdown]', __('Allow Markdown Syntax?', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('field_data[' . $i . '][paragraph_allow_shortcode]', pods_var('paragraph_allow_shortcode', $field), 'boolean'); ?>
                                                        <?php echo PodsForm::label('field_data[' . $i . '][paragraph_allow_shortcode]', __('Allow Shortcodes?', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-paragraph-allow-html">
                                                <?php echo PodsForm::label('field_data[' . $i . '][paragraph_allowed_html_tags]', __('Allowed HTML Tags', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][paragraph_allowed_html_tags]', pods_var('paragraph_allowed_html_tags', $field), 'text'); ?>
                                            </div>
                                        </div>

                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-number">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][number_format_type]', __('Format Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][number_format_type]', pods_var('number_format_type', $field), 'pick', array('data' => pods_var('number_format_type', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-field-data-number-format-type pods-depends-on-field-data-number-format-type-currency">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('field_data[' . $i . '][number_format_currency_sign]', __('Currency Sign', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('field_data[' . $i . '][number_format_currency_sign]', pods_var('number_format_currency_sign', $field), 'pick', array('data' => pods_var('number_format_currency_sign', $field_settings))); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('field_data[' . $i . '][number_format_currency_placement]', __('Currency Placement', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('field_data[' . $i . '][number_format_currency_placement]', pods_var('number_format_currency_placement', $field), 'pick', array('data' => pods_var('number_format_currency_placement', $field_settings))); ?>
                                                </div>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][number_format]', __('Format', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][number_format]', pods_var('number_format', $field), 'pick', array('data' => pods_var('number_format', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][number_decimals]', __('Decimals', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][number_decimals]', pods_var('number_decimals', $field), 'number'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][number_max_length]', __('Max Length', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][number_max_length]', pods_var('number_max_length', $field), 'number'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][number_size]', __('Field Size', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][number_size]', pods_var('number_size', $field), 'pick', array('data' => pods_var('number_size', $field_settings))); ?>
                                            </div>
                                        </div>

                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-file">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][file_type]', __('File Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][file_type]', pods_var('file_type', $field), 'pick', array('data' => pods_var('file_type', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][file_uploader]', __('File Uploader', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][file_uploader]', pods_var('file_uploader', $field), 'pick', array('data' => pods_var('file_uploader', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-file-type pods-depends-on-field-data-file-type-multi-limited">
                                                <?php echo PodsForm::label('field_data[' . $i . '][file_limit]', __('File Limit', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][file_limit]', pods_var('file_limit', $field), 'number'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][file_restrict_filesize]', __('Restrict File Size (MB)', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][file_restrict_filesize]', pods_var('file_restrict_filesize', $field), 'number'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][file_restrict_filetypes]', __('Restrict Filetypes', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][file_restrict_filetypes]', pods_var('file_restrict_filetypes', $field), 'text'); ?>
                                            </div>
                                        </div>

                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-boolean">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][boolean_format_type]', __('Format Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][boolean_format_type]', pods_var('boolean_format_type', $field), 'pick', array('data' => pods_var('boolean_format_type', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][boolean_yes_label]', __('Yes Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][boolean_yes_label]', pods_var('boolean_yes_label', $field), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][boolean_no_label]', __('No Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][boolean_no_label]', pods_var('boolean_no_label', $field), 'text'); ?>
                                            </div>
                                        </div>

                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-permalink">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][permalink_behavior]', __('Behavior', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][permalink_behavior]', pods_var('permalink_behavior', $field), 'pick', array('data' => pods_var('permalink_behavior', $field_settings))); ?>
                                            </div>
                                        </div>

                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-pick">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_type]', __('Selection Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_type]', pods_var('pick_type', $field), 'pick', array('data' => pods_var('pick_type', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-type pods-depends-on-field-data-pick-type-single">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_format_single]', __('Format', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_format_single]', pods_var('pick_format_single', $field), 'pick', array('data' => pods_var('pick_format_single', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-type pods-depends-on-field-data-pick-type-multi-limited pods-depends-on-field-data-pick-type-multi-unlimited">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_format_multi]', __('Format', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_format_multi]', pods_var('pick_format_multi', $field), 'pick', array('data' => pods_var('pick_format_multi', $field_settings))); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-type pods-depends-on-field-data-pick-type-multi-limited">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_limit]', __('Select Limit', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_limit]', pods_var('pick_limit', $field), 'number'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_filter]', __('Filter Items by', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_filter]', pods_var('pick_filter', $field), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_orderby]', __('Order Items by', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_orderby]', pods_var('pick_orderby', $field), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_groupby]', __('Group Items by', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_groupby]', pods_var('pick_groupby', $field), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][pick_size]', __('Field Size', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][pick_size]', pods_var('pick_size', $field), 'pick', array('data' => pods_var('pick_size', $field_settings))); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="pods-advanced-options-<?php echo $i; ?>" class="pods-tab pods-advanced-options">
                                        <h4><?php _e('Visual'); ?></h4>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][css_class_name]', __('CSS Class Name', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][css_class_name]', pods_var('css_class_name', $field), 'text'); ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][input_helper]', __('Input Helper', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][input_helper]', pods_var('input_helper', $field), 'pick', array('data' => pods_var('input_helper', $field_settings))); ?>
                                        </div>
                                        <h4><?php _e('Values'); ?></h4>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][default_value]', __('Default Value', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][default_value]', pods_var('default_value', $field), 'text'); ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][default_value_parameter]', __('Set Default Value via Parameter', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][default_value_parameter]', pods_var('default_value_parameter', $field), 'text'); ?>
                                        </div>
                                        <h4><?php _e('Visibility'); ?></h4>
                                            <div class="pods-field-option-group">
                                                <p class="pods-field-option-group-label">
                                                    <?php _e('Restrict Access'); ?>
                                                </p>
                                                <div class="pods-field-option-group-values">
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('field_data[' . $i . '][admin_only]', pods_var('admin_only', $field), 'boolean'); ?>
                                                        <?php echo PodsForm::label('field_data[' . $i . '][admin_only]', __('Show to Admins Only?', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('field_data[' . $i . '][restrict_capability]', pods_var('restrict_capability', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                        <?php echo PodsForm::label('field_data[' . $i . '][restrict_capability]', __('Restrict access by Capability?', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="pods-depends-on pods-depends-on-field-data-restrict-capability">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][capability_allowed]', __('Capability Allowed', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][capability_allowed]', pods_var('capability_allowed', $field), 'text'); ?>
                                            </div>
                                        </div>
                                        <h4><?php _e('Validation'); ?></h4>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][regex_validation]', __('RegEx Validation', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][regex_validation]', pods_var('regex_validation', $field), 'text'); ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][message_regex]', __("Message if field doesn't pass RegEx"), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][message_regex]', pods_var('message_regex', $field), 'text'); ?>
                                        </div>
                                        <div class="pods-depends-on pods-depends-on-field-data-required">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][message_required]', __('Message if field is blank', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][message_required]', pods_var('message_required', $field), 'text'); ?>
                                            </div>
                                        </div>
                                        <div class="pods-depends-on pods-depends-on-field-data-unique">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('field_data[' . $i . '][message_unique]', __('Message if field is not unique', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('field_data[' . $i . '][message_unique]', pods_var('message_required', $field), 'text'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pods-manage-row-actions submitbox">
                                    <div class="pods-manage-row-delete">
                                        <a class="submitdelete deletion" href="#delete-field"><?php _e('Delete Field'); ?></a>
                                    </div>
                                    <p class="pods-manage-row-save">
                                        <a class="pods-manage-row-cancel" href="#cancel-edit-field"><?php _e('Cancel'); ?></a> &nbsp;&nbsp;
                                        <a href="#save-field" class="button-primary"><?php _e('Update Field'); ?></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
            </td>
            <td class="pods-manage-row-name">
                <a title="Edit this field" class="pods-manage-row-edit row-name" href="#edit-field"><?php echo esc_html(pods_var('name', $field)); ?></a>
            </td>
            <td class="pods-manage-row-type">
                <?php echo esc_html((isset($field_types[pods_var('type', $field)]) ? $field_types[pods_var('type', $field)] : 'Unknown')) . ' <span class="pods-manage-row-more">[type: ' . pods_var('type', $field) . ']</span>'; ?>
<?php
if ('pick' == pods_var('type', $field) && '' != pods_var('pick_object', $field, '')) {
    $pick_object = null;
    foreach ($field_settings['pick_object'] as $object => $object_label) {
        if (null !== $pick_object)
            break;
        if ('-- Select --' == $object_label)
            continue;
        if (is_array($object_label)) {
            foreach ($object_label as $sub_object => $sub_object_label) {
                if (pods_var('pick_object', $field) == $sub_object) {
                    $object = rtrim($object, 's');
                    if (false !== strpos($object, 'ies'))
                        $object = str_replace('ies', 'y', $object);
                    $sub_object_label = preg_replace('/(\s\([\w\d\s]*\))/', '', $sub_object_label);
                    $pick_object = esc_html($sub_object_label) . ' <small>(' . esc_html($object) . ')</small>';
                    break;
                }
            }
        }
        elseif (pods_var('pick_object', $field) == $object) {
            $pick_object = $object_label;
            break;
        }
    }
    if (null === $pick_object)
        $pick_object = pods_var('pick_object', $field);
?>
                <br /><span class="pods-manage-field-type-desc">&rsaquo; <?php echo $pick_object; ?></span>
<?php
}
?>
            </td>
        </tr>