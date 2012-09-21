<?php
/**
 * @package Pods\Deprecated
 */
ob_start();
require_once(preg_replace("/wp-content.*/","wp-load.php",__FILE__));
ob_end_clean();

if (false === headers_sent()) {
    if ('' == session_id())
        @session_start();
    header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
}

// Sanitize input
$params = stripslashes_deep((array) $_POST);
if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) {
    foreach ($params as $key => $val) {
        $params[$key] = pods_sanitize(trim($val));
    }
}

$methods = array(
    'save_pod_item' => array('processor' => 'process_save_pod_item'),
    'load_pod_item' => array(),
    'load_files' => array(),
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

    if ((!isset($params->_wpnonce) || (false === wp_verify_nonce($params->_wpnonce, 'pods-' . $action) && false === wp_verify_nonce($params->_wpnonce, 'pods-multi'))))
        die('<e>Access denied');

    if ($access_pod_specific === true) {
        $priv_val = false;
        if (isset($params->datatype))
            $priv_val = 'pod_' . $params->datatype;
        if (false === $priv_val || (!pods_access($priv_val) && !pods_access('manage_content')))
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

    $params = apply_filters('pods_api_'.$action,$params);

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

function process_save_pod_item ($params, $api) {
    $params = (object) $params;

    $columns = pods_validate_key($params->token, $params->pod, $params->uri_hash, null, $params->form_count);
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
        $tmp = $api->load_pod(array('name' => $params->pod));
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
