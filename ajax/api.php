<?php
ob_start();
require_once(realpath('../../../../wp-load.php'));
ob_end_clean();

// Sanitize input
foreach ($_POST as $key => $val)
{
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
    'drop_pod_item' => array('priv' => 'manage_content'),
    'load_pod' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_column' => array('priv' => 'manage_pods', 'format' => 'json'),
    'load_template' => array('priv' => 'manage_templates', 'format' => 'json'),
    'load_page' => array('priv' => 'manage_pod_pages', 'format' => 'json'),
    'load_helper' => array('priv' => 'manage_helpers', 'format' => 'json'),
    'load_menu_item' => array('priv' => 'manage_menu', 'format' => 'json'),
    'load_sister_fields' => array('format' => 'json'),
    'load_pod_item' => array(),
    'load_files' => array()
);

$api = new PodAPI();
$action = $params['action'];

if (isset($methods[$action]))
{
    $priv = isset($methods[$action]['priv']) ? $methods[$action]['priv'] : null;
    $format = isset($methods[$action]['format']) ? $methods[$action]['format'] : null;
    $processor = isset($methods[$action]['processor']) ? $methods[$action]['processor'] : null;

    // Check permissions (convert to array to support multiple)
    if (!empty($priv))
    {
        foreach ((array) $priv as $priv_val)
        {
            if (!pods_access($priv_val))
            {
                die('Error: Access denied');
            }
        }
    }

    // Call any processors
    if (!empty($processor) && function_exists($processor))
    {
        $params = $processor($params, $api);
    }

    // Dynamically call the API method
    $output = $api->$action($params);

    // Output in PHP or JSON format
    if ('json' == $format)
    {
        $output = json_encode($output);
    }
    echo $output;
}

function process_save_pod_item($params, $api)
{
    if (!pods_validate_key($params['token'], $params['uri_hash'], $params['datatype'], $params['form_count']))
    {
        die("Error: The form has expired.");
    }

    if ($tmp = $_SESSION[$params['uri_hash']][$params['form_count']]['columns'])
    {
        foreach ($tmp as $key => $val)
        {
            $column_name = is_array($val) ? $key : $val;
            $columns[$column_name] = $params[$column_name];
        }
    }
    else
    {
        $tmp = $api->load_pod(array('name' => $params['datatype']));
        foreach ($tmp['fields'] as $key => $field_data)
        {
            $column_name = $field_data['name'];
            $columns[$column_name] = $params[$column_name];
        }
    }
    $params['columns'] = $columns;
    return $params;
}
