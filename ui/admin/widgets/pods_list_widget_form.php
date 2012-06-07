<style type="text/css">
    ol.pods_list_widget_form {
        list-style: none;
        padding-left: 0;
        margin-left: 0;
    }
    
    ol.pods_list_widget_form label {
        display: block;
    }
</style>

<ol class="pods_list_widget_form">
    <li>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
		<input class="widefat" type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
    </li>

    <li>
        <?php
        $api = new PodsAPI();
        $all_pods = $api->load_pods(array());
        ?>
        <label for="<?php echo $this->get_field_id('pod_type'); ?>">
            Pod Type
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
            <strong class="red">None Found</strong>
        <?php endif; ?>
    </li>

    <li>
        <?php
        $all_templates = $api->load_templates(array());
        ?>
        <label for="<?php echo $this->get_field_id('template'); ?>">
            Template
        </label>
        <?php if (0 < count($all_templates)): ?>
            <select name="<?php echo $this->get_field_name('template'); ?>" id="<?php echo $this->get_field_id('template'); ?>">
                <?php foreach($all_templates as $tpl): ?>
                    <?php $selected = ($tpl['name'] == $template) ? 'selected' : ''; ?>
                    <option value="<?php echo $tpl['name']; ?>" <?php echo $selected; ?>>
                        <?php echo $tpl['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <strong class="red">None Found</strong>
        <?php endif; ?>
    </li>

	<li>
		<label for="<?php echo $this->get_field_id('limit'); ?>">Limit</label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $limit; ?>" />
	</li>

	<li>
		<label for="<?php echo $this->get_field_id('orderby'); ?>">Order By</label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>" value="<?php echo $orderby; ?>" />
	</li>

	<li>
		<label for="<?php echo $this->get_field_id('direction'); ?>">Sort Direction</label>
		<select id="<?php echo $this->get_field_id('direction'); ?>" name="<?php echo $this->get_field_name('direction'); ?>">
			<option value=""></option>
			<option value="ASC" <?php echo ($direction == 'ASC') ? 'selected' : ''; ?>>Ascending</option>
			<option value="DESC" <?php echo ($direction == 'DESC') ? 'selected' : ''; ?>>Descending</option>
		</select>
	</li>

	<li>
		<label for="<?php echo $this->get_field_id('where'); ?>">Filter</label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('where'); ?>" name="<?php echo $this->get_field_name('where'); ?>" value="<?php echo $where; ?>" />
	</li>

    <li>
        <?php
        $all_helpers = $api->load_helpers(array());
        ?>
        <label for="<?php echo $this->get_field_id('helper'); ?>">
            Helper
        </label>
        
        <select name="<?php echo $this->get_field_name('helper'); ?>" id="<?php echo $this->get_field_id('helper'); ?>">
            <option value=""></option>
            <?php foreach ($all_helpers as $hlp): ?>
                <?php $selected = ($hlp['name'] == $helper) ? 'selected' : ''; ?>
                <option value="<?php echo $hlp['name']; ?>" <?php echo $selected; ?>>
                    <?php echo $hlp['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </li>
</ol>
