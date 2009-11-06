<?php
// Include the MySQL connection
require_once(realpath('../../../../wp-config.php'));

if ($_POST['auth'] != md5(AUTH_KEY))
{
    die('Error: Authentication failed');
}

foreach ($_POST as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

$name = pods_clean_name($name);

$dbtypes = array(
    'bool' => 'bool default 0',
    'date' => 'datetime',
    'num' => 'decimal(9,2)',
    'txt' => 'varchar(128)',
    'slug' => 'varchar(128)',
    'code' => 'longtext',
    'desc' => 'longtext'
);

/*
==================================================
Edit a column
==================================================
*/
if ('edit' == $action)
{
    if ('id' == $name || 'type' == $name)
    {
        die("Error: $name is not editable.");
    }

    $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $datatype AND id != $field_id AND name = '$name' LIMIT 1";
    pod_query($sql, 'Column already exists', "The $name column already exists.");

    $sql = "SELECT name, coltype FROM @wp_pod_fields WHERE id = $field_id LIMIT 1";
    $result = pod_query($sql) or die(mysql_error());

    if (0 < mysql_num_rows($result))
    {
        $row = mysql_fetch_assoc($result);
        $old_coltype = $row['coltype'];
        $old_name = $row['name'];

        $dbtype = $dbtypes[$coltype];
        $pickval = ('pick' != $coltype || empty($pickval)) ? 'NULL' : "'$pickval'";
        $sister_field_id = (int) $sister_field_id;

        if ($coltype != $old_coltype)
        {
            if ('pick' == $coltype || 'file' == $coltype)
            {
                if ('pick' != $old_coltype && 'file' != $old_coltype)
                {
                    pod_query("ALTER TABLE `@wp_pod_tbl_$dtname` DROP COLUMN `$old_name`");
                }
            }
            elseif ('pick' == $old_coltype || 'file' == $old_coltype)
            {
                pod_query("ALTER TABLE `@wp_pod_tbl_$dtname` ADD COLUMN `$name` $dbtype", 'Cannot create column');
                pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id = $field_id");
                pod_query("DELETE FROM @wp_pod_rel WHERE field_id = $field_id");
            }
            else
            {
                pod_query("ALTER TABLE `@wp_pod_tbl_$dtname` CHANGE `$old_name` `$name` $dbtype");
            }
        }
        elseif ($name != $old_name && 'pick' != $coltype && 'file' != $coltype)
        {
            pod_query("ALTER TABLE `@wp_pod_tbl_$dtname` CHANGE `$old_name` `$name` $dbtype");
        }

        $sql = "
        UPDATE
            @wp_pod_fields
        SET
            name = '$name',
            label = '$label',
            comment = '$comment',
            coltype = '$coltype',
            pickval = $pickval,
            display_helper = '$display_helper',
            input_helper = '$input_helper',
            pick_filter = '$pick_filter',
            pick_orderby = '$pick_orderby',
            sister_field_id = '$sister_field_id',
            required = '$required',
            `unique` = '$unique',
            `multiple` = '$multiple'
        WHERE
            id = $field_id
        LIMIT
            1
        ";
        pod_query($sql, 'Cannot edit column');
    }
}

/*
==================================================
Edit a template
==================================================
*/
elseif ('edittemplate' == $action)
{
    pod_query("UPDATE @wp_pod_templates SET code = '$code' WHERE id = $template_id LIMIT 1");
}

/*
==================================================
Edit a page
==================================================
*/
elseif ('editpage' == $action)
{
    pod_query("UPDATE @wp_pod_pages SET title = '$page_title', page_template = '$page_template', phpcode = '$phpcode', precode = '$precode' WHERE id = $page_id LIMIT 1");
}

/*
==================================================
Edit a menu item
==================================================
*/
elseif ('editmenu' == $action)
{
    pod_query("UPDATE @wp_pod_menu SET uri = '$menu_uri', title = '$menu_title' WHERE id = $menu_id LIMIT 1");
}

/*
==================================================
Edit a helper
==================================================
*/
elseif ('edithelper' == $action)
{
    pod_query("UPDATE @wp_pod_helpers SET phpcode = '$phpcode' WHERE id = $helper_id LIMIT 1");
}

/*
==================================================
Edit roles
==================================================
*/
elseif ('editroles' == $action)
{
    $roles = array();
    foreach ($_POST as $key => $val)
    {
        if ('action' != $key && 'auth' != $key)
        {
            $tmp = empty($val) ? array() : explode(',', $val);
            $roles[$key] = $tmp;
        }
    }
    $roles = serialize($roles);
    delete_option('pods_roles');
    add_option('pods_roles', $roles);
}

/*
==================================================
Edit a pod
==================================================
*/
else
{
    $sql = "
    UPDATE
        @wp_pod_types
    SET
        label = '$label',
        is_toplevel = '$is_toplevel',
        detail_page = '$detail_page',
        before_helpers = '$before_helpers',
        after_helpers = '$after_helpers'
    WHERE
        id = $datatype
    LIMIT
        1
    ";
    pod_query($sql, 'Cannot change Pod settings');

    $weight = 0;
    $order = (false !== strpos($order, ',')) ? explode(',', $order) : array($order);
    foreach ($order as $key => $field_id)
    {
        pod_query("UPDATE @wp_pod_fields SET weight = '$weight' WHERE id = '$field_id' LIMIT 1", 'Cannot change column order');
        $weight++;
    }
}
