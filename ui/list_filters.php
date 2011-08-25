<?php
$datatype = $this->datatype;
$datatype_id = $this->datatype_id;
?>
    <form method="get" class="filterbox filterbox_<?php echo esc_attr($datatype); ?>" action="<?php echo esc_attr($action); ?>">
        <input type="hidden" name="type" value="<?php echo esc_attr($datatype); ?>" />
<?php
if (empty($filters)) {
    $result = pod_query("SELECT list_filters FROM @wp_pod_types WHERE id = $datatype_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    $filters = $row['list_filters'];
}

if (!empty($filters)) {
    $filters = explode(',', $filters);
    foreach ($filters as $key => $val) {
        $field_name = trim($val);
        $result = pod_query("SELECT label, pickval, coltype FROM @wp_pod_fields WHERE datatype = $datatype_id AND name = '$field_name' LIMIT 1");
        $row = mysql_fetch_assoc($result);
        if ('pick' == $row['coltype'] && !empty($row['pickval'])) {
            $pick_params = array('table' => $row['pickval'], 'field_name' => $field_name, 'unique_vals' => false);
            $field_data = $this->get_dropdown_values($pick_params);
            $field_label = ucwords(str_replace('_', ' ', $field_name));
            if (0 < strlen($row['label']))
                $field_label = $row['label'];
?>
    <select name="<?php echo esc_attr($field_name); ?>" id="filter_<?php echo esc_attr($field_name); ?>" class="filter <?php echo esc_attr($field_name); ?>">
        <option value="">-- <?php echo esc_attr($field_label); ?> --</option>
<?php
            foreach ($field_data as $key => $val) {
                $active = empty($val['active']) ? '' : ' selected';
                $value = $val['id'];
                if ('text' == $this->search_mode)
                    $value = $val['name'];
?>
        <option value="<?php echo esc_attr($value); ?>"<?php echo $active; ?>><?php echo esc_html($val['name']); ?></option>
<?php
            }
?>
    </select>
<?php
        }/* findRecords doesn't handle non-pick fields yet
        elseif ('bool' == $row['coltype']) {
            $field_label = ucwords(str_replace('_', ' ', $field_name));
            if (0 < strlen($row['label']))
                $field_label = $row['label'];
?>
    <label for="filter_<?php echo esc_attr($field_name); ?>" class="filter <?php echo esc_attr($field_name); ?>">
        <input type="checkbox" name="<?php echo esc_attr($field_name); ?>" id="filter_<?php echo esc_attr($field_name); ?>" value="1"<?php echo ((isset($_GET[$field_name]) && 1 == $_GET[$field_name]) ? ' CHEKED' : ''); ?>> <?php echo esc_html($field_label); ?>
    </label>
<?php
        }
        elseif ('file' != $row['coltype']) {
            $field_label = ucwords(str_replace('_', ' ', $field_name));
            if (0 < strlen($row['label']))
                $field_label = $row['label'];
?>
    <label for="filter_<?php echo esc_attr($field_name); ?>" class="filter <?php echo esc_attr($field_name); ?>">
        <input type="text" name="<?php echo esc_attr($field_name); ?>" id="filter_<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr((isset($_GET[$field_name]) ? $_GET[$field_name] : '')); ?>"> <?php echo esc_html($field_label); ?>
    </label>
<?php
        }*/
    }
}
// Display the search box and submit button
$search = empty($_GET[$this->search_var]) ? '' : stripslashes($_GET[$this->search_var]);
?>
        <input type="text" class="pod_search" name="<?php echo esc_attr($this->search_var); ?>" value="<?php echo esc_attr($search); ?>" />
        <input type="submit" class="pod_submit" value="<?php echo esc_attr($label); ?>" />
    </form>