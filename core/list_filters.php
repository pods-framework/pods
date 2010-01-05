<?php
$datatype = $this->datatype;
$datatype_id = $this->datatype_id;
?>
    <form method="get" class="filterbox filterbox_<?php echo $datatype; ?>" action="<?php echo $action; ?>">
        <input type="hidden" name="type" value="<?php echo $datatype; ?>" />
<?php
if (empty($filters))
{
    $result = pod_query("SELECT list_filters FROM @wp_pod_types WHERE id = $datatype_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    $filters = $row['list_filters'];
}

if (!empty($filters))
{
    $filters = explode(',', $filters);
    foreach ($filters as $key => $val)
    {
        $field_name = trim($val);
        $result = pod_query("SELECT pickval FROM @wp_pod_fields WHERE datatype = $datatype_id AND name = '$field_name' LIMIT 1");
        $row = mysql_fetch_assoc($result);
        if (!empty($row['pickval']))
        {
            $params = array('table' => $row['pickval'], 'field_name' => $field_name, 'unique_vals' => false);
            $data = $this->get_dropdown_values($params);
?>
    <select name="<?php echo $field_name; ?>" class="filter <?php echo $field_name; ?>">
        <option value="">-- <?php echo ucwords(str_replace('_', ' ', $field_name)); ?> --</option>
<?php
            foreach ($data as $key => $val)
            {
                $active = empty($val['active']) ? '' : ' selected';
?>
        <option value="<?php echo $val['id']; ?>"<?php echo $active; ?>><?php echo $val['name']; ?></option>
<?php
            }
?>
    </select>
<?php
        }
    }
}
// Display the search box and submit button
$search = empty($_GET['search']) ? '' : $_GET['search'];
?>
        <input type="text" class="pod_search" name="search" value="<?php echo $search; ?>" />
        <input type="submit" class="pod_submit" value="<?php echo $label; ?>" />
    </form>
