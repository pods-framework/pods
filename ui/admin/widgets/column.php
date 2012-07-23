<style type="text/css">
    ol.pods_column_widget_form {
        list-style: none;
        padding-left: 0;
        margin-left: 0;
    }

    ol.pods_column_widget_form label {
        display: block;
    }
</style>

<ol class="pods_column_widget_form">
    <li>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'pods'); ?></label>
        <input class="widefat" type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
    </li>

    <li>
        <?php
        $api = new PodsAPI();
        $all_pods = $api->load_pods(array());
        ?>
        <label for="<?php echo $this->get_field_id('pod_type'); ?>">
            <?php _e('Pod Type', 'pods'); ?>
        </label>
        <?php if (0 < count($all_pods)): ?>
            <select id="<?php $this->get_field_id('pod_type'); ?>" name="<?php echo $this->get_field_name('pod_type'); ?>">
                <?php foreach ($all_pods as $pod): ?>
                    <?php $selected = ($pod['name'] == $pod_type) ? 'selected' : ''; ?>
                    <option value="<?php echo $pod['name']; ?>" <?php echo $selected; ?>>
                        <?php echo $pod['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <strong class="red"><?php _e('None Found', 'pods'); ?></strong>
        <?php endif; ?>
    </li>

    <li>
        <label for="<?php echo $this->get_field_id('slug'); ?>">
            <?php _e('Slug or ID', 'pods'); ?>
        </label>
        <input class="widefat" type="text" id="<?php echo $this->get_field_id('slug'); ?>" name="<?php echo $this->get_field_name('slug'); ?>" value="<?php echo $slug; ?>" />
    </li>

    <li>
        <label for="<?php $this->get_field_id('column'); ?>"><?php _e('Column', 'pods'); ?></label>
        <input class="widefat" type="text" name="<?php echo $this->get_field_name('column'); ?>" id="<?php echo $this->get_field_id('column'); ?>" value="<?php echo $column; ?>" />
    </li>

    <li>
        <?php
        $all_helpers = $api->load_helpers(array());
        ?>
        <label for="<?php echo $this->get_field_id('helper'); ?>">
            <?php _e('Helper', 'pods'); ?>
        </label>

        <select name="<?php echo $this->get_field_name('helper'); ?>" id="<?php echo $this->get_field_id('helper'); ?>">
            <option value="">- <?php _e('Helper', 'pods'); ?> -</option>
            <?php foreach ($all_helpers as $hlp): ?>
                <?php $selected = ($hlp['name'] == $helper) ? 'selected' : ''; ?>
                <option value="<?php echo $hlp['name']; ?>" <?php echo $selected; ?>>
                    <?php echo $hlp['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </li>
</ol>
