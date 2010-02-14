<?php
// Include the MySQL connection
require_once(realpath('../../../../wp-load.php'));

if (!pods_access('manage_packages'))
{
    die('Error: Authentication failed');
}

foreach ($_POST as $key => $val)
{
    ${$key} = $val;
}

/*
==================================================
Package Manager: Export
==================================================
*/
if ('export' == $action)
{
    $export = array(
        'meta' => array(
            'version' => get_option('pods_version'),
            'build' => date('U'),
        )
    );

    $pod_ids = $pod;
    $template_ids = $template;
    $podpage_ids = $podpage;
    $helper_ids = $helper;

    // Get pods
    if (!empty($pod_ids))
    {
        $result = pod_query("SELECT * FROM @wp_pod_types WHERE id IN ($pod_ids)");
        while ($row = mysql_fetch_assoc($result))
        {
            $dt = $row['id'];
            unset($row['id']);
            $export['pods'][$dt] = $row;
        }

        // Get pod fields
        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE datatype IN ($pod_ids)");
        while ($row = mysql_fetch_assoc($result))
        {
            unset($row['id']);
            $dt = $row['datatype'];
            unset($row['datatype']);
            $export['pods'][$dt]['fields'][] = $row;
        }
    }

    // Get templates
    if (!empty($template_ids))
    {
        $result = pod_query("SELECT * FROM @wp_pod_templates WHERE id IN ($template_ids)");
        while ($row = mysql_fetch_assoc($result))
        {
            unset($row['id']);
            $export['templates'][] = $row;
        }
    }

    // Get pod pages
    if (!empty($podpage_ids))
    {
        $result = pod_query("SELECT * FROM @wp_pod_pages WHERE id IN ($podpage_ids)");
        while ($row = mysql_fetch_assoc($result))
        {
            unset($row['id']);
            $export['pod_pages'][] = $row;
        }
    }

    // Get helpers
    if (!empty($helper_ids))
    {
        $result = pod_query("SELECT * FROM @wp_pod_helpers WHERE id IN ($helper_ids)");
        while ($row = mysql_fetch_assoc($result))
        {
            unset($row['id']);
            $export['helpers'][] = $row;
        }
    }

    echo htmlspecialchars(json_encode($export));
}
/*
==================================================
Package Manager: Import Finalize
==================================================
*/
elseif ('finalize' == $action)
{
    $data = get_option('pods_package');
    $data = json_decode(stripslashes($data), true);

    $dbtypes = array(
        'bool' => 'bool default 0',
        'date' => 'datetime',
        'num' => 'decimal(9,2)',
        'txt' => 'varchar(128)',
        'slug' => 'varchar(128)',
        'file' => 'varchar(128)',
        'code' => 'mediumtext',
        'desc' => 'mediumtext'
    );

    if (isset($data['pods']))
    {
        $pod_columns = '';
        foreach ($data['pods'] as $key => $val)
        {
            $table_columns = array();
            $pod_fields = $val['fields'];
            unset($val['fields']);

            // Escape the values
            foreach ($val as $k => $v)
            {
                $val[$k] = pods_sanitize($v);
            }

            if (empty($pod_columns))
            {
                $pod_columns = implode("`,`", array_keys($val));
            }
            // Backward-compatibility (before/after helpers)
            $pod_columns = str_replace('before_helpers', 'pre_save_helpers', $pod_columns);
            $pod_columns = str_replace('after_helpers', 'post_save_helpers', $pod_columns);

            $values = implode("','", $val);
            $dt = pod_query("INSERT INTO @wp_pod_types (`$pod_columns`) VALUES ('$values')");

            $tupples = array();
            $field_columns = '';
            foreach ($pod_fields as $key => $fieldval)
            {
                // Escape the values
                foreach ($fieldval as $k => $v)
                {
                    $fieldval[$k] = empty($v) ? 'null' : pods_sanitize($v);
                }

                // Store all table columns
                if ('pick' != $fieldval['coltype'])
                {
                    $table_columns[$fieldval['name']] = $fieldval['coltype'];
                }
                
                $fieldval['datatype'] = $dt;
                if (empty($field_columns))
                {
                    $field_columns = implode("`,`", array_keys($fieldval));
                }
                $tupples[] = implode("','", $fieldval);
            }
            $tupples = implode("'),('", $tupples);
            $tupples = str_replace("'null'", 'null', $tupples);
            pod_query("INSERT INTO @wp_pod_fields (`$field_columns`) VALUES ('$tupples')");

            // Create the actual table with any non-PICK columns
            $definitions = array("id INT unsigned auto_increment primary key");
            foreach ($table_columns as $colname => $coltype)
            {
                $definitions[] = "`$colname` {$dbtypes[$coltype]}";
            }
            $definitions = implode(',', $definitions);
            pod_query("CREATE TABLE @wp_pod_tbl_{$val['name']} ($definitions)");
        }
    }

    if (isset($data['templates']))
    {
        $columns = '';
        $tupples = array();
        foreach ($data['templates'] as $key => $val)
        {
            // Escape the values
            foreach ($val as $k => $v)
            {
                $val[$k] = pods_sanitize($v);
            }

            if (empty($columns))
            {
                $columns = implode("`,`", array_keys($val));
            }
            $tupples[] = implode("','", $val);
        }
        $tupples = implode("'),('", $tupples);
        pod_query("INSERT INTO @wp_pod_templates (`$columns`) VALUES ('$tupples')");
    }

    if (isset($data['pod_pages']))
    {
        $columns = '';
        $tupples = array();
        foreach ($data['pod_pages'] as $key => $val)
        {
            // Escape the values
            foreach ($val as $k => $v)
            {
                $val[$k] = pods_sanitize($v);
            }

            if (empty($columns))
            {
                $columns = implode("`,`", array_keys($val));
            }
            $tupples[] = implode("','", $val);
        }
        $tupples = implode("'),('", $tupples);
        pod_query("INSERT INTO @wp_pod_pages (`$columns`) VALUES ('$tupples')");
    }

    if (isset($data['helpers']))
    {
        $columns = '';
        $tupples = array();
        foreach ($data['helpers'] as $key => $val)
        {
            // Escape the values
            foreach ($val as $k => $v)
            {
                // Backward-compatibility (before/after helpers)
                if ('helper_type' == $k)
                {
                    $v = ('before' == $v) ? 'pre_save' : $v;
                    $v = ('after' == $v) ? 'post_save' : $v;
                }
                $val[$k] = pods_sanitize($v);
            }

            if (empty($columns))
            {
                $columns = implode("`,`", array_keys($val));
            }
            $tupples[] = implode("','", $val);
        }
        $tupples = implode("'),('", $tupples);
        pod_query("INSERT INTO @wp_pod_helpers (`$columns`) VALUES ('$tupples')");
    }

    echo '<p>All done!</p>';
}
/*
==================================================
Package Manager: Import Confirmation
==================================================
*/
elseif ('import' == $action)
{
    $warnings = array();

    update_option('pods_package', $data);

    $data = json_decode(stripslashes($data), true);

    if (isset($data['pods']))
    {
        foreach ($data['pods'] as $id => $val)
        {
            $pod_name = $val['name'];
            $result = pod_query("SELECT id FROM @wp_pod_types WHERE name = '$pod_name' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $warnings[] = "The pod <b>$pod_name</b> already exists!";
            }
        }
    }

    if (isset($data['pod_pages']))
    {
        foreach ($data['pod_pages'] as $id => $val)
        {
            $uri = $val['uri'];
            $result = pod_query("SELECT id FROM @wp_pod_pages WHERE uri = '$uri' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $warnings[] = "The pod page <b>$uri</b> already exists!";
            }
        }
    }

    if (isset($data['helpers']))
    {
        foreach ($data['helpers'] as $id => $val)
        {
            $helper_name = $val['name'];
            $result = pod_query("SELECT id FROM @wp_pod_helpers WHERE name = '$helper_name' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $warnings[] = "The helper <b>$helper_name</b> already exists!";
            }
        }
    }
    if (0 < count($warnings))
    {
        // Display any warnings
        echo '<p class="red">The import cannot continue because of the following warnings:</p>';
        echo '<p>' . implode('</p><p>', $warnings) . '</p>';
    }
    else
    {
        // Show the "Finalize Import" button
        echo '<p><input type="button" class="button" onclick="podsImport(true)" value="Looking good. Finalize!" />';
    }
}

