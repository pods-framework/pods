<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

$name = strtolower(str_replace(' ', '_', $name));

// Add new datatype
if ('pod' == $type)
{
    if (!empty($name))
    {
        $result = mysql_query("SELECT id FROM {$table_prefix}pod_types WHERE name = '$name' LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            die('Error: Pod by this name already exists!');
        }

        // Add list and detail template presets
        $tpl_list = '<p><a href="{@detail_url}">{@name}</a></p>';
        $tpl_detail = "<h2>{@name}</h2>\n{@body}";

        mysql_query("INSERT INTO {$table_prefix}pod_types (name, tpl_list, tpl_detail) VALUES ('$name', '$tpl_list', '$tpl_detail')") or die('Error: Problem adding new pod.');
        $pod_id = mysql_insert_id();

        mysql_query("CREATE TABLE {$table_prefix}pod_tbl_$name (id int unsigned auto_increment primary key, name varchar(128), body text)") or die('Error: Problem adding pod database table.');
        mysql_query("INSERT INTO {$table_prefix}pod_fields (datatype, name, coltype, required) VALUES ($pod_id, 'name', 'txt', 1),($pod_id, 'body', 'desc', 0)") or die('Error: Problem adding name and body columns.');

        die("$pod_id"); // return as string
    }
    die('Error: Enter a pod name!');
}
// Add new page
elseif ('page' == $type)
{
    if (!empty($uri))
    {
        $result = mysql_query("SELECT id FROM {$table_prefix}pod_pages WHERE uri = '$uri' LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            die('Error: Page by this URI already exists!');
        }
        mysql_query("INSERT INTO {$table_prefix}pod_pages (uri, phpcode) VALUES ('$uri', '$phpcode')") or die('Error: Problem adding new page.');
        $page_id = mysql_insert_id();

        die("$page_id"); // return as string
    }
    die('Error: Enter a page URI!');
}
// Add new widget
elseif ('widget' == $type)
{
    if (!empty($name))
    {
        $result = mysql_query("SELECT id FROM {$table_prefix}pod_widgets WHERE name = '$name' LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            die('Error: Widget by this name already exists!');
        }
        mysql_query("INSERT INTO {$table_prefix}pod_widgets (name, phpcode) VALUES ('$name', '$phpcode')") or die('Error: Problem adding new widget.');
        $widget_id = mysql_insert_id();

        die("$widget_id"); // return as string
    }
    die('Error: Enter a widget name!');
}
// Add new column
else
{
    if ('id' == $name || 'name' == $name || 'body' == $name || 'type' == $name)
    {
        die("Error: $name is a reserved name.");
    }

    $result = mysql_query("SELECT id FROM {$table_prefix}pod_fields WHERE datatype = $datatype AND name = '$name' LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        die('Error: Column by this name already exists!');
    }
    mysql_query("INSERT INTO {$table_prefix}pod_fields (datatype, name, label, coltype, pickval, sister_field_id, required) VALUES ('$datatype', '$name', '$label', '$coltype', '$pickval', '$sister_field_id', '$required')");
    $field_id = mysql_insert_id();

    if (empty($pickval))
    {
        $dbtypes = array(
            'bool' => 'bool',
            'date' => 'datetime',
            'num' => 'decimal(9,2)',
            'txt' => 'varchar(128)',
            'file' => 'varchar(128)',
            'desc' => 'text'
        );
        $dbtype = $dbtypes[$coltype];
        mysql_query("ALTER TABLE {$table_prefix}pod_tbl_$dtname ADD COLUMN $name $dbtype") or die('Error: Could not create column!');
    }
    else
    {
        mysql_query("UPDATE {$table_prefix}pod_fields SET sister_field_id = '$field_id' WHERE id = '$sister_field_id' LIMIT 1");
    }
}

