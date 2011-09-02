<?php
ob_start();
require_once(preg_replace("/wp-content.*/","wp-load.php",__FILE__));
ob_end_clean();

// Sanitize input
$params = array();
foreach ($_POST as $key => $val) {
    $params[$key] = mysql_real_escape_string(stripslashes(trim($val)));
}

$methods = array(
    'save_pod' => array('priv' => 'manage_pods'),
    'save_column' => array('priv' => 'manage_pods'),
    'save_template' => array('priv' => 'manage_templates'),
    'save_page' => array('priv' => 'manage_pod_pages'),
    'save_helper' => array('priv' => 'manage_helpers'),
    'save_roles' => array('priv' => 'manage_roles'),
    'save_menu_item' => array('priv' => 'manage_menu'),
    'save_pod_item' => array('processor' => 'process_save_pod_item'),
    'drop_pod' => array('priv' => 'manage_pods'),
    'drop_column' => array('priv' => 'manage_pods'),
    'drop_template' => array('priv' => 'manage_templates'),
    'drop_page' => array('priv' => 'manage_pod_pages'),
    'drop_helper' => array('priv' => 'manage_helpers'),
    'drop_menu_item' => array('priv' => 'manage_menu'),
    'drop_pod_item' => array('access_pod_specific' => true),
    'load_pod' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_column' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_template' => array('priv' => 'manage_templates', 'format' => 'json'),
    'load_page' => array('priv' => 'manage_pod_pages', 'format' => 'json'),
    'load_helper' => array('priv' => 'manage_helpers', 'format' => 'json'),
    'load_menu_item' => array('priv' => 'manage_menu', 'format' => 'json'),
    'load_sister_fields' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_pod_item' => array(),
    'load_files' => array(),
    'export_package' => array('priv' => 'manage_packages', 'format' => 'json', 'safe' => true),
    'import_package' => array('priv' => 'manage_packages'),
    'validate_package' => array('priv' => 'manage_packages')
);

$api = new PodAPI();
$params = (object) $params;
$action = $params->action;

if (isset($methods[$action])) {
    $priv = isset($methods[$action]['priv']) ? $methods[$action]['priv'] : null;
    $format = isset($methods[$action]['format']) ? $methods[$action]['format'] : null;
    $processor = isset($methods[$action]['processor']) ? $methods[$action]['processor'] : null;
    $safe = isset($methods[$action]['safe']) ? $methods[$action]['safe'] : null;
    $access_pod_specific = isset($methods[$action]['access_pod_specific']) ? $methods[$action]['access_pod_specific'] : null;

    if($access_pod_specific === true) {
        if (isset($params->tbl_row_id)) {
            if (isset($params->datatype_id)) {
                $select_dt = "p.datatype = '$params->datatype_id'";
            }
            else {
                $select_dt = "t.name = '$params->datatype'";
            }
            $sql = "
            SELECT
                p.id AS pod_id, p.tbl_row_id, t.id, t.name AS datatype
            FROM
                @wp_pod p
            INNER JOIN
                @wp_pod_types t ON t.id = p.datatype
            WHERE
                p.tbl_row_id = $params->tbl_row_id AND
                $select_dt
            LIMIT
                1
            ";
        }
        else {
            $sql = "
            SELECT
                p.id AS pod_id, p.tbl_row_id, t.id, t.name AS datatype
            FROM
                @wp_pod p
            INNER JOIN
                @wp_pod_types t ON t.id = p.datatype
            WHERE
                p.id = $params->pod_id
            LIMIT
                1
            ";
        }
        $result = pod_query($sql);
        $row = mysql_fetch_assoc($result);
        $priv_val = 'pod_'.$row['datatype'];
        if (!pods_access($priv_val) && !pods_access('manage_content')) {
            die('<e>Access denied');
        }
    }

    // Check permissions (convert to array to support multiple)
    if (!empty($priv)) {
        foreach ((array) $priv as $priv_val) {
            if (!pods_access($priv_val)) {
                die('<e>Access denied');
            }
        }
    }

    // Call any processors
    if (!empty($processor) && function_exists($processor)) {
        $params = $processor($params, $api);
    }

    $params = apply_filters('pods_api_'.$action,$params);

    // Dynamically call the API method
    $params = (array) $params;
    $output = $api->$action($params);

    // Output in PHP or JSON format
    if ('json' == $format) {
        $output = json_encode($output);
    }

    // If output for on-page to go into a textarea
    if (true === $safe) {
        $output = htmlspecialchars($output);
    }

    if (!is_bool($output)) {
        echo $output;
    }
}

function process_save_pod_item($params, $api) {
    if (!pods_validate_key($params->token, $params->uri_hash, $params->datatype, $params->form_count)) {
        die("<e>The form has expired. Please reload the page and ensure your session is still active.");
    }

    if ($tmp = $_SESSION[$params->uri_hash][$params->form_count]['columns']) {
        foreach ($tmp as $key => $val) {
            $column_name = is_array($val) ? $key : $val;
            $columns[$column_name] = $params->$column_name;
        }
    }
    else {
        $tmp = $api->load_pod(array('name' => $params->datatype));
        foreach ($tmp['fields'] as $key => $field_data) {
            $column_name = $field_data['name'];
            $columns[$column_name] = $params->$column_name;
        }
    }
    $params->columns = $columns;
    return $params;
}
