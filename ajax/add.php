<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

if ($_POST['auth'] != md5(AUTH_KEY))
{
    die('Error: Authentication failed');
}

foreach ($_POST as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

$name = strtolower(str_replace(' ', '_', $name));

// Add new datatype
if ('pod' == $type)
{
    if (!empty($name))
    {
        $sql = "SELECT id FROM {$table_prefix}pod_types WHERE name = '$name' LIMIT 1";
        pod_query($sql, 'Cannot get pod type', 'Pod by this name already exists');

        // Add list and detail template presets
        $tpl_list = '<p><a href="{@detail_url}">{@name}</a></p>';
        $tpl_detail = "<h2>{@name}</h2>\n{@body}";

        $pod_id = pod_query("INSERT INTO {$table_prefix}pod_types (name, tpl_list, tpl_detail) VALUES ('$name', '$tpl_list', '$tpl_detail')", 'Cannot add new pod');
        pod_query("CREATE TABLE {$table_prefix}pod_tbl_$name (id int unsigned auto_increment primary key, name varchar(128), body text)", 'Cannot add pod database table');
        pod_query("INSERT INTO {$table_prefix}pod_fields (datatype, name, coltype, required) VALUES ($pod_id, 'name', 'txt', 1),($pod_id, 'body', 'desc', 0)", 'Cannot add name and body columns');

        die("$pod_id"); // return as string
    }
    die('Error: Enter a pod name!');
}
// Add new page
elseif ('page' == $type)
{
    if (!empty($uri))
    {
        $sql = "SELECT id FROM {$table_prefix}pod_pages WHERE uri = '$uri' LIMIT 1";
        pod_query($sql, 'Cannot get PodPages', 'Page by this URI already exists');
        $page_id = pod_query("INSERT INTO {$table_prefix}pod_pages (uri, phpcode) VALUES ('$uri', '$phpcode')", 'Cannot add new page');

        die("$page_id"); // return as string
    }
    die('Error: Enter a page URI');
}
// Add new widget
elseif ('widget' == $type)
{
    if (!empty($name))
    {
        $sql = "SELECT id FROM {$table_prefix}pod_widgets WHERE name = '$name' LIMIT 1";
        pod_query($sql, 'Cannot get Widgets', 'Widget by this name already exists');
        $widget_id = pod_query("INSERT INTO {$table_prefix}pod_widgets (name, phpcode) VALUES ('$name', '$phpcode')", 'Cannot add new widget');

        die("$widget_id"); // return as string
    }
    die('Error: Enter a widget name');
}
// Add new column
else
{
    if ('id' == $name || 'name' == $name || 'body' == $name || 'type' == $name)
    {
        die("Error: $name is a reserved name");
    }

    $sql = "SELECT id FROM {$table_prefix}pod_fields WHERE datatype = $datatype AND name = '$name' LIMIT 1";
    pod_query($sql, 'Cannot get fields', 'Column by this name already exists');
    $field_id = pod_query("INSERT INTO {$table_prefix}pod_fields (datatype, name, label, coltype, pickval, sister_field_id, required) VALUES ('$datatype', '$name', '$label', '$coltype', '$pickval', '$sister_field_id', '$required')", 'Cannot add new field');

    if (empty($pickval))
    {
        $dbtypes = array(
            'bool' => 'bool',
            'date' => 'datetime',
            'num' => 'decimal(9,2)',
            'txt' => 'varchar(128)',
            'file' => 'varchar(128)',
            'code' => 'text',
            'desc' => 'text'
        );
        $dbtype = $dbtypes[$coltype];
        pod_query("ALTER TABLE {$table_prefix}pod_tbl_$dtname ADD COLUMN $name $dbtype", 'Cannot create new column');
    }
    else
    {
        pod_query("UPDATE {$table_prefix}pod_fields SET sister_field_id = '$field_id' WHERE id = '$sister_field_id' LIMIT 1", 'Cannot update sister field');
    }
}

