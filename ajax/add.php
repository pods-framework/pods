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

$name = pods_clean_name($name);

/*
==================================================
Add new datatype
==================================================
*/
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
        pod_query("INSERT INTO {$table_prefix}pod_fields (datatype, name, coltype, required, weight) VALUES ($pod_id, 'name', 'txt', 1, 0),($pod_id, 'body', 'desc', 0, 1)", 'Cannot add name and body columns');

        die("$pod_id"); // return as string
    }
    die('Error: Enter a pod name!');
}

/*
==================================================
Add new page
==================================================
*/
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

/*
==================================================
Add new helper
==================================================
*/
elseif ('helper' == $type)
{
    if (!empty($name))
    {
        $sql = "SELECT id FROM {$table_prefix}pod_helpers WHERE name = '$name' LIMIT 1";
        pod_query($sql, 'Cannot get helpers', 'helper by this name already exists');
        $helper_id = pod_query("INSERT INTO {$table_prefix}pod_helpers (name, phpcode) VALUES ('$name', '$phpcode')", 'Cannot add new helper');

        die("$helper_id"); // return as string
    }
    die('Error: Enter a helper name');
}

/*
==================================================
Add new menu item
==================================================
*/
elseif ('menu' == $type)
{
    // get the "rgt" value of the parent
    $result = pod_query("SELECT rgt FROM {$table_prefix}pod_menu WHERE id = $parent_menu_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    $rgt = $row['rgt'];

    // Increase all "lft" values by 2 if > "rgt"
    pod_query("UPDATE {$table_prefix}pod_menu SET lft = lft + 2 WHERE lft > $rgt");

    // Increase all "rgt" values by 2 if >= "rgt"
    pod_query("UPDATE {$table_prefix}pod_menu SET rgt = rgt + 2 WHERE rgt >= $rgt");

    // Add new item: "lft" = rgt, "rgt" = rgt + 1
    $lft = $rgt;
    $rgt = ($rgt + 1);
    $menu_id = pod_query("INSERT INTO {$table_prefix}pod_menu (uri, title, lft, rgt) VALUES ('$menu_uri', '$menu_title', $lft, $rgt)");

    die("$menu_id"); // return as string
}

/*
==================================================
Add new column
==================================================
*/
else
{
    if ('id' == $name || 'name' == $name || 'type' == $name)
    {
        die("Error: $name is a reserved name");
    }

    $sql = "SELECT id FROM {$table_prefix}pod_fields WHERE datatype = $datatype AND name = '$name' LIMIT 1";
    pod_query($sql, 'Cannot get fields', 'Column by this name already exists');

    // Sink the new column to the bottom of the list
    $weight = 0;
    $result = pod_query("SELECT weight FROM {$table_prefix}pod_fields WHERE datatype = $datatype ORDER BY weight DESC LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        $row = mysql_fetch_assoc($result);
        $weight = intval($row['weight']) + 1;
    }

    $sister_field_id = ('null' == $sister_field_id) ? 'NULL' : "'$sister_field_id'";
    $field_id = pod_query("INSERT INTO {$table_prefix}pod_fields (datatype, name, label, coltype, pickval, sister_field_id, required, weight) VALUES ('$datatype', '$name', '$label', '$coltype', '$pickval', $sister_field_id, '$required', '$weight')", 'Cannot add new field');

    if (empty($pickval))
    {
        $dbtypes = array(
            'bool' => 'bool',
            'date' => 'datetime',
            'num' => 'decimal(9,2)',
            'txt' => 'varchar(128)',
            'file' => 'varchar(128)',
            'code' => 'mediumtext',
            'desc' => 'mediumtext'
        );
        $dbtype = $dbtypes[$coltype];
        pod_query("ALTER TABLE {$table_prefix}pod_tbl_$dtname ADD COLUMN $name $dbtype", 'Cannot create new column');
    }
    else
    {
        pod_query("UPDATE {$table_prefix}pod_fields SET sister_field_id = '$field_id' WHERE id = '$sister_field_id' LIMIT 1", 'Cannot update sister field');
    }
}

