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