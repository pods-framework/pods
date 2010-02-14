<?php
ob_start();
require_once(realpath('../../../../wp-load.php'));
ob_end_clean();

// Sanitize input
foreach ($_POST as $key => $val)
{
    $params[$key] = mysql_real_escape_string(stripslashes(trim($val)));
}

$api = new PodAPI();
$action = $params['action'];

/*
==================================================
Actions: Add / Edit
==================================================
*/
if ('save_pod' == $action && pods_access('manage_pods'))
{
    echo $api->save_pod($params);
}
elseif ('save_column' == $action && pods_access('manage_pods'))
{
    echo $api->save_column($params);
}
elseif ('save_template' == $action && pods_access('manage_templates'))
{
    echo $api->save_template($params);
}
elseif ('save_page' == $action && pods_access('manage_pod_pages'))
{
    echo $api->save_page($params);
}
elseif ('save_helper' == $action && pods_access('manage_helpers'))
{
    echo $api->save_helper($params);
}
elseif ('save_roles' == $action && pods_access('manage_roles'))
{
    echo $api->save_roles($params);
}
elseif ('save_menu' == $action && pods_access('manage_menu'))
{
    echo $api->save_menu($params);
}
elseif ('save_menu_item' == $action && pods_access('manage_menu'))
{
    echo $api->save_menu_item($params);
}
elseif ('save_pod_item' == $action)
{
    echo $api->save_pod_item($params);
}

/*
==================================================
Actions: Drop
==================================================
*/
elseif ('drop_pod' == $action && pods_access('manage_pods'))
{
    $api->drop_pod($params);
}
elseif ('drop_column' == $action && pods_access('manage_pods'))
{
    $api->drop_column($params);
}
elseif ('drop_template' == $action && pods_access('manage_templates'))
{
    $api->drop_template($params);
}
elseif ('drop_page' == $action && pods_access('manage_pod_pages'))
{
    $api->drop_page($params);
}
elseif ('drop_helper' == $action && pods_access('manage_helpers'))
{
    $api->drop_helper($params);
}
elseif ('drop_menu_item' == $action && pods_access('manage_menu'))
{
    $api->drop_menu_item($params);
}
elseif ('drop_pod_item' == $action)
{
    $api->drop_pod_item($params);
}

/*
==================================================
Actions: Load
==================================================
*/
elseif ('load_pod' == $action && pods_access('manage_pods'))
{
    $out = $api->load_pod($params);
    echo json_encode($out);
}
elseif ('load_column' == $action && pods_access('manage_pods'))
{
    $out = $api->load_column($params);
    echo json_encode($out);
}
elseif ('load_template' == $action && pods_access('manage_templates'))
{
    $out = $api->load_template($params);
    echo json_encode($out);
}
elseif ('load_page' == $action && pods_access('manage_pod_pages'))
{
    $out = $api->load_page($params);
    echo json_encode($out);
}
elseif ('load_helper' == $action && pods_access('manage_helpers'))
{
    $out = $api->load_helper($params);
    echo json_encode($out);
}
elseif ('load_menu_item' == $action && pods_access('manage_menu'))
{
    $out = $api->load_menu_item($params);
    echo json_encode($out);
}
elseif ('load_pod_item' == $action)
{
    echo $api->load_pod_item($params);
}
elseif ('load_files' == $action)
{
    echo $api->load_files($params);
}
elseif ('load_sister_fields' == $action)
{
    echo $api->load_sister_fields($params);
}
else
{
    echo 'Error: Access denied';
}
