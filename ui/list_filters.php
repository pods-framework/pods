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
        $result = pod_query("SELECT label, pickval FROM @wp_pod_fields WHERE datatype = $datatype_id AND name = '$field_name' LIMIT 1");
        $row = mysql_fetch_assoc($result);
        if (!empty($row['pickval'])) {
            $params = array('table' => $row['pickval'], 'field_name' => $field_name, 'unique_vals' => false);
            $data = $this->get_dropdown_values($params);
            $label = ucwords(str_replace('_', ' ', $field_name));
            if (0 < strlen($row['label']))
                $label = $row['label'];
?>
    <select name="<?php echo esc_attr($field_name); ?>" class="filter <?php echo esc_attr($field_name); ?>">
        <option value="">-- <?php echo esc_attr($label); ?> --</option>
<?php
            foreach ($data as $key => $val) {
                $active = empty($val['active']) ? '' : ' selected';
?>
        <option value="<?php echo esc_attr($val['id']); ?>"<?php echo $active; ?>><?php echo esc_attr($val['name']); ?></option>
<?php
            }
?>
    </select>
<?php
        }
    }
}
// Display the search box and submit button
$search = empty($_GET[$this->search_var]) ? '' : $_GET[$this->search_var];
?>
        <input type="text" class="pod_search" name="<?php echo esc_attr($this->search_var); ?>" value="<?php echo esc_attr($search); ?>" />
        <input type="submit" class="pod_submit" value="<?php echo esc_attr($label); ?>" />
    </form>
