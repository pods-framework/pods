<?php
ob_start();
require_once(preg_replace("/wp-content.*/","wp-load.php",__FILE__));
ob_end_clean();

if (false === headers_sent()) {
    if ('' == session_id())
        @session_start();
    header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
}

// Sanitize input
$params = stripslashes_deep($_POST);
if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) {
    foreach ($params as $key => $val) {
        $params[$key] = pods_sanitize(trim($val));
    }
}

$methods = array(
    'save_pod' => array('priv' => 'manage_pods', 'format' => 'json'),
    'save_column' => array('priv' => 'manage_pods'),
    'save_template' => array('priv' => 'manage_templates'),
    'save_page' => array('priv' => 'manage_pod_pages'),
    'save_helper' => array('priv' => 'manage_helpers'),
    'save_roles' => array('priv' => 'manage_roles'),
    'save_pod_item' => array('processor' => 'process_save_pod_item'),
    'reorder_pod_item' => array('access_pod_specific' => true),
    'drop_pod' => array('priv' => 'manage_pods'),
    'drop_column' => array('priv' => 'manage_pods'),
    'drop_template' => array('priv' => 'manage_templates'),
    'drop_page' => array('priv' => 'manage_pod_pages'),
    'drop_helper' => array('priv' => 'manage_helpers'),
    'drop_pod_item' => array('access_pod_specific' => true),
    'load_pod' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_column' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_template' => array('priv' => 'manage_templates', 'format' => 'json'),
    'load_page' => array('priv' => 'manage_pod_pages', 'format' => 'json'),
    'load_helper' => array('priv' => 'manage_helpers', 'format' => 'json'),
    'load_sister_fields' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_pod_item' => array(),
    'load_files' => array(),
    'export_package' => array('priv' => 'manage_packages', 'format' => 'json', 'safe' => true),
    'import_package' => array('priv' => 'manage_packages'),
    'validate_package' => array('priv' => 'manage_packages'),
    'replace_package' => array('priv' => 'manage_packages'),
    'security_settings' => array('priv' => 'manage_settings'),
    'pod_page_settings' => array('priv' => 'manage_settings'),
    'fix_wp_pod' => array('priv' => 'manage_settings')
);

$api = new PodAPI();
$params = (object) $params;
$action = $params->action;

if (isset($methods[$action])) {
    $priv = isset($methods[$action]['priv']) ? $methods[$action]['priv'] : null;
    $format = isset($methods[$action]['format']) ? $methods[$action]['format'] : null;
    $processor = isset($methods[$action]['processor']) ? (string) $methods[$action]['processor'] : null;
    $safe = isset($methods[$action]['safe']) ? $methods[$action]['safe'] : null;
    $access_pod_specific = isset($methods[$action]['access_pod_specific']) ? $methods[$action]['access_pod_specific'] : null;

    if ('save_pod_item' == $action) {
        if (isset($params->_wpnonce) && false === wp_verify_nonce($params->_wpnonce, 'pods-' . $action))
            die('<e>Access denied');
    }
    elseif ((!isset($params->_wpnonce) || (false === wp_verify_nonce($params->_wpnonce, 'pods-' . $action) && false === wp_verify_nonce($params->_wpnonce, 'pods-multi'))))
        die('<e>Access denied');

    if ($access_pod_specific === true) {
        if (isset($params->datatype))
            $priv_val = 'pod_' . $params->datatype;
        else {
            if (isset($params->tbl_row_id)) {
                if (isset($params->datatype_id)) {
                    $select_dt = "p.datatype = '$params->datatype_id'";
                }
                else {
                    $select_dt = "t.name = '$params->datatype'";
                }
                $sql = "
                SELECT
                    p.id AS pod_id, p.tbl_row_id, t.id, t.name AS datatype, t.id AS datatype_id
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
                    p.id AS pod_id, p.tbl_row_id, t.id, t.name AS datatype, t.id AS datatype_id
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
            $priv_val = 'pod_' . $row['datatype'];
            $params->datatype = $row['datatype'];
            $params->datatype_id = $row['datatype_id'];
        }
        if (!pods_access($priv_val) && !pods_access('manage_content'))
            die('<e>Access denied');
    }

    // Check permissions (convert to array to support multiple)
    if (!empty($priv)) {
        foreach ((array) $priv as $priv_val) {
            if (!pods_access($priv_val))
                die('<e>Access denied');
        }
    }

    // Call any processors
    if (null !== $processor && 0 < strlen($processor) && function_exists($processor))
        $params = $processor($params, $api);

    $params = apply_filters('pods_api_'.$action, $params);
    $output = '';

    if ('security_settings' == $action) {
        delete_option('pods_disable_file_browser');
        add_option('pods_disable_file_browser', (isset($params->disable_file_browser) ? $params->disable_file_browser : 0));

        delete_option('pods_files_require_login');
        add_option('pods_files_require_login', (isset($params->files_require_login) ? $params->files_require_login : 0));

        delete_option('pods_files_require_login_cap');
        add_option('pods_files_require_login_cap', (isset($params->files_require_login_cap) ? $params->files_require_login_cap : ''));

        delete_option('pods_disable_file_upload');
        add_option('pods_disable_file_upload', (isset($params->disable_file_upload) ? $params->disable_file_upload : 0));

        delete_option('pods_upload_require_login');
        add_option('pods_upload_require_login', (isset($params->upload_require_login) ? $params->upload_require_login : 0));

        delete_option('pods_upload_require_login_cap');
        add_option('pods_upload_require_login_cap', (isset($params->upload_require_login_cap) ? $params->upload_require_login_cap : ''));
    }
    elseif ('pod_page_settings' == $action) {
        delete_option('pods_page_precode_timing');
        add_option('pods_page_precode_timing', (isset($params->pods_page_precode_timing) ? (int) $params->pods_page_precode_timing : 0));
    }
    else {
        // Dynamically call the API method
        $params = (array) $params;
        $output = $api->$action($params);
    }

    // Output in PHP or JSON format
    if ('json' == $format && false !== $output)
        $output = json_encode($output);

    // If output for on-page to go into a textarea
    if (true === $safe)
        $output = esc_textarea($output);

    if (!is_bool($output))
        echo $output;
}

function process_save_pod_item($params, $api) {
    $params = (object) $params;

    $columns = pods_validate_key($params->token, $params->datatype, $params->uri_hash, null, $params->form_count);
    if (false === $columns)
        die("<e>This form has expired. Please reload the page and ensure your session is still active.");

    if (is_array($columns)) {
        foreach ($columns as $key => $val) {
            $column = is_array($val) ? $key : $val;
            if (!isset($params->$column))
                unset($columns[$column]);
            else
                $columns[$column] = $params->$column;
        }
    }
    else {
        $tmp = $api->load_pod(array('name' => $params->datatype));
        $columns = array();
        foreach ($tmp['fields'] as $field_data) {
            $column = $field_data['name'];
            if (!isset($params->$column))
                continue;
            $columns[$column] = $params->$column;
        }
    }
    $params->columns = $columns;
    return $params;
}