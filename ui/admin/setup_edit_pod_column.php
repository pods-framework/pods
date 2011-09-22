<?php
$alternate = $i % 2 ? 'alternate ': '';
$level = 0;
if (null !== $group)
    $level = 1;
?>
        <tr id="field-<?php echo $i; ?>" class="<?php echo $alternate;?> pods-field pods-field-<?php echo esc_attr($field['name']); ?> pods-level-<?php echo intval($level); ?><?php if ('group' == $field['type']) { ?> pods-field-group pods-field-group-<?php echo esc_attr($field['name']); ?><?php } elseif (null !== $group) { ?> pods-child-of-<?php echo esc_attr($group['name']); } ?>" valign="top">
            <th scope="row" class="check-field">
                <img src="<?php echo PODS_URL; ?>/ui/images/handle.gif" alt="Move" class="pods-sort-handles" />
            </th>
            <td class="label field-label">
                <strong>
                    <?php if ('group' != $field['type']) { ?>
                        <a class="row-label pods-edit-field-specifics" title="Edit Field" href="#edit-field">
                            <?php 
                                if(!empty($level)) {
                                    for ($level_count = 0; $level_count < intval($level); $level_count++) {
                                        echo "&mdash; ";
                                    }
                                }
                                echo esc_html($field['label']);
                            ?>
                        </a>
                    <?php } else { ?>
                        <a class="row-label pods-edit-field-children" title="Edit Children" href="#edit-children">
                            <?php echo esc_html($field['label']); ?>
                        </a>
                    <?php } ?>
                </strong> 
                <div class="row-actions">
                    <?php if ('group' != $field['type']) { ?>
                        <span class="edit">
                            <a title="Edit Field" class="pods-edit-field-specifics" href="#edit-field">Edit</a>
                             | 
                        </span>
                    <?php } ?>
                    <span class="delete">
                         <a class="submitdelete" title="Delete this Field" href="#delete-field">Delete</a>
                    </span>
                </div> 
                <?php if ('group' != $field['type']) { ?>
                <div class="pods-manage-field-wrapper" id="pods-manage-field-<?php echo $i; ?>">
                        <div class="pods-manage-field">

                            <div class="pods-main-field-attributes">

                                <div class="pods-field-detail pods-textfield" id="field-pods-field-machine-name-<?php echo $i; ?>">
                                    <label for="pods-field-machine-name-<?php echo $i; ?>">Name</label>
                                    <input name="pods-field-machine-name-<?php echo $i; ?>" id="pods-field-machine-name-<?php echo $i; ?>" type="text" value="<?php echo esc_attr($field['name']); ?>" />
                                </div>

                                <div class="pods-field-detail pods-textfield" id="field-pods-field-label-<?php echo $i; ?>">
                                    <label for="pods-field-label-<?php echo $i; ?>">Label</label>
                                    <input name="pods-field-label-<?php echo $i; ?>" id="pods-field-label-<?php echo $i; ?>" type="text" value="<?php echo esc_attr($field['label']); ?>" />
                                </div>

                                <div class="pods-field-detail pods-textfield" id="field-pods-field-comment-<?php echo $i; ?>">
                                    <label for="pods-field-comment-<?php echo $i; ?>">Comment</label>
                                    <input name="pods-field-comment-<?php echo $i; ?>" id="pods-field-comment-<?php echo $i; ?>" type="text" value="<?php echo esc_attr($field['comment']); ?>" />
                                </div>

                                <div class="pods-field-detail pods-select" id="field-pods-field-type-<?php echo $i; ?>">
                                    <label for="pods-field-type-<?php echo $i; ?>">Field Type</label>
                                    <select class="pods-field-type" name="pods-field-type-<?php echo $i; ?>" id="pods-field-type-<?php echo $i; ?>">
                                        <option value="">--Select--</option>
                                        <option value="date">Date</option>
                                        <option value="num">Number</option>
                                        <option value="bool">Boolean</option>
                                        <option value="txt">Single Line Text</option>
                                        <option value="code">Paragraph/Code</option>
                                        <option value="file">File Upload</option>
                                        <option value="slug">Permalink</option>
                                        <option value="pick">Relationship (pick)</option>
                                    </select>
                                </div>

                                <div class="pods-field-detail pods-pick pods-group">
                                    <p class="pods-pick-label">
                                        Pod Details
                                    </p>
                                    <div class="pods-pick-values">
                                        <div class="pods-checkbox" id="field-pods-field-required-<?php echo $i; ?>">
                                            <input name="pods-field-required-<?php echo $i; ?>" id="pods-field-required-<?php echo $i; ?>" type="checkbox" />
                                            <label for="pods-field-required-<?php echo $i; ?>">Required</label>
                                        </div>
                                        <div class="pods-checkbox" id="field-pods-field-unique-<?php echo $i; ?>">
                                            <input name="pods-field-unique-<?php echo $i; ?>" id="pods-field-unique-<?php echo $i; ?>" type="checkbox" />
                                            <label for="pods-field-unique-<?php echo $i; ?>">Unique</label>
                                        </div>
                                        <div class="pods-checkbox" id="field-pods-field-duplication-<?php echo $i; ?>">
                                            <input name="pods-field-duplication" id="pods-field-duplication-<?php echo $i; ?>" type="checkbox" />
                                            <label for="pods-field-duplication-<?php echo $i; ?>">Duplication</label>
                                        </div>
                                    </div>
                                    <!-- /.pods-pick-values -->
                                </div>
                                <!-- /.pods-field-detail -->

                            </div>
                            <!-- /.pods-main-field-attributes -->

                            <div class="pods-field-specific-children">
                                <!-- placeholder for all children -->
                            </div>

                            <div class="pods-field-specific">
                                <!-- placeholder for field specific -->
                            </div>

                            <div id="pods-advanced-<?php echo $i; ?>" class="pods-advanced">

                                <div class="pods-field-specific-advanced-children">
                                    <!-- placeholder for specific advanced -->
                                </div>

                                <div class="pods-field-specific-advanced">
                                    <!-- placeholder for specific advanced -->
                                </div>

                                <div class="pods-select pods-helper" id="field-pods-field-display-helper-<?php echo $i; ?>">
                                    <label for="pods-field-display-helper-<?php echo $i; ?>">Display Helper</label>
                                    <select name="pods-field-display-helper-<?php echo $i; ?>" id="pods-field-display-helper-<?php echo $i; ?>">
                                        <option value="0">--Select--</option>
                                    </select>
                                </div>
                                <div class="pods-select pods-helper" id="field-pods-field-input-helper-<?php echo $i; ?>">
                                    <label for="pods-field-input-helper-<?php echo $i; ?>">Input Helper</label>
                                    <select name="pods-field-input-helper-<?php echo $i; ?>" id="pods-field-input-helper-<?php echo $i; ?>">
                                        <option value="0">--Select--</option>
                                    </select>
                                </div>
                                <div class="pods-textfield" id="field-pods-field-max-length-<?php echo $i; ?>">
                                    <label for="pods-field-max-length-<?php echo $i; ?>">Maximum Length</label>
                                    <input name="pods-field-max-length-<?php echo $i; ?>" id="pods-field-max-length-<?php echo $i; ?>" type="text" />
                                </div>
                            </div>
                            <!-- /pods-advanced -->

                            <div class="pods-field-actions pods-group">
                                <p class="pods-advanced-toggle"><a href="#pods-advanced-<?php echo $i; ?>">Advanced</a></p>
                                <p class="pods-save-field">
                                    <a class="cancel" href="#cancel-edit-field">Cancel</a> &nbsp;&nbsp;
                                    <a href="#save-field" class="button-primary button">Save Field</a>
                                </p>
                            </div>
                            <!-- /.pods-field-actions -->

                        </div>
                    </div><!-- /pods-manage-field-wrapper -->
                    <?php } ?>
            </td>
            <td class="machine-name field-machine-name">
                <?php if ('group' != $field['type']) { ?>
                    <a title="Edit Field" class="pods-edit-field-specifics" href="#edit-field"><?php echo esc_html($field['name']); ?></a>
                <?php } ?>
            </td> 
            <td class="field-type field-field-type">
                <code><?php echo esc_html($field['type']); ?></code>
            </td> 
            <td class="comment field-comment">
                <?php echo esc_html($field['comment']); ?>
            </td>
        </tr>