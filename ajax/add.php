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
    if (empty($name))
    {
        die('Error: Enter a pod name');
    }

    $sql = "SELECT id FROM @wp_pod_types WHERE name = '$name' LIMIT 1";
    pod_query($sql, 'Cannot get pod type', 'Pod by this name already exists');

    $pod_id = pod_query("INSERT INTO @wp_pod_types (name) VALUES ('$name')", 'Cannot add new pod');
    pod_query("CREATE TABLE `@wp_pod_tbl_$name` (id int unsigned auto_increment primary key, name varchar(128), body text)", 'Cannot add pod database table');
    pod_query("INSERT INTO @wp_pod_fields (datatype, name, coltype, required, weight) VALUES ($pod_id, 'name', 'txt', 1, 0),($pod_id, 'body', 'desc', 0, 1)", 'Cannot add name and body columns');

    die("$pod_id"); // return as string
}

/*
==================================================
Add new template
==================================================
*/
elseif ('template' == $type)
{
    if (empty($name))
    {
        die('Error: Enter a template name');
    }

    $sql = "SELECT id FROM @wp_pod_templates WHERE name = '$name' LIMIT 1";
    pod_query($sql, 'Cannot get Templates', 'Template by this name already exists');
    $template_id = pod_query("INSERT INTO @wp_pod_templates (name, code) VALUES ('$name', '$code')", 'Cannot add new template');

    die("$template_id"); // return as string
}

/*
==================================================
Add new page
==================================================
*/
elseif ('page' == $type)
{
    if (empty($uri))
    {
        die('Error: Enter a page URI');
    }

    $sql = "SELECT id FROM @wp_pod_pages WHERE uri = '$uri' LIMIT 1";
    pod_query($sql, 'Cannot get Pod Pages', 'Page by this URI already exists');
    $page_id = pod_query("INSERT INTO @wp_pod_pages (uri, phpcode) VALUES ('$uri', '$phpcode')", 'Cannot add new page');

    die("$page_id"); // return as string
}

/*
==================================================
Add new helper
==================================================
*/
elseif ('helper' == $type)
{
    if (empty($name))
    {
        die('Error: Enter a helper name');
    }

    $sql = "SELECT id FROM @wp_pod_helpers WHERE name = '$name' LIMIT 1";
    pod_query($sql, 'Cannot get helpers', 'helper by this name already exists');
    $helper_id = pod_query("INSERT INTO @wp_pod_helpers (name, helper_type, phpcode) VALUES ('$name', '$helper_type', '$phpcode')", 'Cannot add new helper');

    die("$helper_id"); // return as string
}

/*
==================================================
Add new menu item
==================================================
*/
elseif ('menu' == $type)
{
    // get the "rgt" value of the parent
    $result = pod_query("SELECT rgt FROM @wp_pod_menu WHERE id = $parent_menu_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    $rgt = $row['rgt'];

    // Increase all "lft" values by 2 if > "rgt"
    pod_query("UPDATE @wp_pod_menu SET lft = lft + 2 WHERE lft > $rgt");

    // Increase all "rgt" values by 2 if >= "rgt"
    pod_query("UPDATE @wp_pod_menu SET rgt = rgt + 2 WHERE rgt >= $rgt");

    // Add new item: "lft" = rgt, "rgt" = rgt + 1
    $lft = $rgt;
    $rgt = ($rgt + 1);
    $menu_id = pod_query("INSERT INTO @wp_pod_menu (uri, title, lft, rgt) VALUES ('$menu_uri', '$menu_title', $lft, $rgt)");

    die("$menu_id"); // return as string
}

/*
==================================================
Add new column
==================================================
*/
else
{
    if (empty($name))
    {
        die('Error: Enter a column name');
    }
    elseif (in_array($name, array('id', 'name', 'type', 'created', 'modified')))
    {
        die("Error: $name is a reserved name");
    }

    $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $datatype AND name = '$name' LIMIT 1";
    pod_query($sql, 'Cannot get fields', 'Column by this name already exists');

    if ('slug' == $coltype)
    {
        $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $datatype AND coltype = 'slug' LIMIT 1";
        pod_query($sql, 'Too many permalinks', 'This pod already has a permalink column');
    }

    // Sink the new column to the bottom of the list
    $weight = 0;
    $result = pod_query("SELECT weight FROM @wp_pod_fields WHERE datatype = $datatype ORDER BY weight DESC LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        $row = mysql_fetch_assoc($result);
        $weight = (int) $row['weight'] + 1;
    }

    $sister_field_id = (int) $sister_field_id;
    $field_id = pod_query("INSERT INTO @wp_pod_fields (datatype, name, label, comment, display_helper, input_helper, coltype, pickval, pick_filter, pick_orderby, sister_field_id, required, `unique`, `multiple`, weight) VALUES ('$datatype', '$name', '$label', '$comment', '$display_helper', '$input_helper', '$coltype', '$pickval', '$pick_filter', '$pick_orderby', '$sister_field_id', '$required', '$unique', '$multiple', '$weight')", 'Cannot add new field');

    if ('pick' != $coltype && 'file' != $coltype)
    {
        $dbtypes = array(
            'bool' => 'bool default 0',
            'date' => 'datetime',
            'num' => 'decimal(9,2)',
            'txt' => 'varchar(128)',
            'slug' => 'varchar(128)',
            'code' => 'longtext',
            'desc' => 'longtext'
        );
        $dbtype = $dbtypes[$coltype];
        pod_query("ALTER TABLE `@wp_pod_tbl_$dtname` ADD COLUMN `$name` $dbtype", 'Cannot create new column');
    }
    else
    {
        pod_query("UPDATE @wp_pod_fields SET sister_field_id = '$field_id' WHERE id = $sister_field_id LIMIT 1", 'Cannot update sister field');
    }
}
