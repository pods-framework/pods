<?php
// Include the MySQL connection
require_once(realpath(dirname(__FILE__) . '/../../../../wp-config.php'));
require_once(realpath(dirname(__FILE__) . '/../core/Pod.class.php'));

$pod_id = (int) $_POST['pod_id'];
$uri_hash = $_POST['uri_hash'];
$datatype = $_POST['datatype'];
$token = $_POST['token'];

// Save the form
if (!empty($token))
{
    if (!pods_validate_key($token, $uri_hash, $datatype))
    {
        die("Error: The form has expired.");
    }

    if ($datatype)
    {
        // Get array of datatypes
        $result = pod_query("SELECT id, name FROM @wp_pod_types");
        while ($row = mysql_fetch_assoc($result))
        {
            $datatypes[$row['name']] = $row['id'];
        }

        // Get the datatype ID
        $datatype_id = $datatypes[$datatype];

        // Add data from a public form
        $where = '';
        if (empty($pod_id))
        {
            $public_columns = unserialize($_SESSION[$uri_hash]['columns']);

            if (!empty($public_columns))
            {
                foreach ($public_columns as $key => $val)
                {
                    $column_key = is_array($public_columns[$key]) ? $key : $val;
                    $active_columns[] = $column_key;
                    $where[] = $column_key;
                }
                $where = "AND name IN ('" . implode("','", $where) . "')";
            }
        }

        // Get the datatype fields
        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE datatype = $datatype_id $where ORDER BY weight", 'Cannot get datatype fields');
        while ($row = mysql_fetch_assoc($result))
        {
            if (empty($public_columns))
            {
                $active_columns[] = $row['name'];
            }
            $fields[$row['name']] = $row;
        }

        $before = array();
        $after = array();
        $pick_columns = array();
        $table_columns = array();

        /*
        ==================================================
        Loop through $active_columns, separating table data from PICK data
        ==================================================
        */
        foreach ($active_columns as $key)
        {
            $type = $fields[$key]['coltype'];
            $val = mysql_real_escape_string(stripslashes(trim($_POST[$key])));

            // Verify required fields
            if (1 == $fields[$key]['required'])
            {
                if (empty($val))
                {
                    die("Error: The $key column is empty.");
                }
                elseif ('date' == $type && false === preg_match("/^(\d{4})-([01][0-9])-([0-3][0-9]) ([0-2][0-9]:[0-5][0-9]:[0-5][0-9])$/", $val))
                {
                    die("Error: The $key column is an invalid date.");
                }
                elseif ('num' == $type && !is_numeric($val))
                {
                    die("Error: The $key column is an invalid number.");
                }
            }

            // Verify unique fields
            if (1 == $fields[$key]['unique'] && 'pick' != $type)
            {
                $exclude = '';
                if (!empty($pod_id))
                {
                    $result = pod_query("SELECT tbl_row_id FROM @wp_pod WHERE id = $pod_id LIMIT 1");
                    $exclude = 'AND id != ' . mysql_result($result, 0);
                }
                $sql = "SELECT id FROM `@wp_pod_tbl_$datatype` WHERE `$key` = '$val' $exclude LIMIT 1";
                pod_query($sql, 'Not a unique value', "The $key value needs to be unique.");
            }

            // Verify slug columns
            if ('slug' == $type)
            {
                $slug_val = empty($_POST[$key]) ? $_POST['name'] : $_POST[$key];
                $val = pods_unique_slug($slug_val, $key, $datatype, $datatype_id, $pod_id);
            }

            if ('pick' == $type)
            {
                $pick_columns[$key] = empty($val) ? array() : explode(',', $val);
            }
            else
            {
                $table_columns[$key] = $val;
            }
        }

        // Get helper code
        $result = pod_query("SELECT before_helpers, after_helpers FROM @wp_pod_types WHERE id = $datatype_id");
        $row = mysql_fetch_assoc($result);
        $before = str_replace(',', "','", $row['before_helpers']);
        $after = str_replace(',', "','", $row['after_helpers']);

        // Call any "before" helpers
        $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$before')");
        while ($row = mysql_fetch_assoc($result))
        {
            eval('?>' . $row['phpcode']);
        }

        // Make sure the pod_id exists
        if (empty($pod_id))
        {
            $sql = "INSERT INTO @wp_pod (datatype, name, created, modified) VALUES ('$datatype_id', '$name', NOW(), NOW())";
            $pod_id = pod_query($sql, 'Cannot add new content');
            $tbl_row_id = pod_query("INSERT INTO `@wp_pod_tbl_$datatype` (name) VALUES (NULL)", 'Cannot add new table row');
        }
        else
        {
            $result = pod_query("SELECT tbl_row_id FROM @wp_pod WHERE id = $pod_id LIMIT 1");
            $tbl_row_id = mysql_result($result, 0);
        }

        /*
        ==================================================
        Save table row data
        ==================================================
        */
        foreach ($table_columns as $key => $val)
        {
            $set_data[] = "`$key` = '$val'";
        }
        $set_data = implode(',', $set_data);

        // Get the item name
        $name = stripslashes($_POST['name']);
        $name = mysql_real_escape_string(trim($name));

        // Insert table row
        pod_query("UPDATE `@wp_pod_tbl_$datatype` SET $set_data WHERE id = $tbl_row_id LIMIT 1");

        // Update wp_pod
        pod_query("UPDATE @wp_pod SET tbl_row_id = $tbl_row_id, datatype = $datatype_id, name = '$name', modified = NOW() WHERE id = $pod_id LIMIT 1", 'Cannot modify datatype row');

        /*
        ==================================================
        Save PICK relationship data
        ==================================================
        */
        foreach ($pick_columns as $key => $rel_ids)
        {
            $field_id = $fields[$key]['id'];
            $pickval = $fields[$key]['pickval'];
            $sister_datatype_id = $datatypes[$pickval];
            $sister_field_id = $fields[$key]['sister_field_id'];
            $sister_field_id = empty($sister_field_id) ? 0 : $sister_field_id;
            $sister_pod_ids = array();

            /*
            ==================================================
            Delete parent & sister rels
            ==================================================
            */
            if (!empty($sister_field_id))
            {
                // Get sister pod IDs (a sister pod's sister pod is the parent pod)
                $result = pod_query("SELECT pod_id FROM @wp_pod_rel WHERE sister_pod_id = $pod_id");
                if (0 < mysql_num_rows($result))
                {
                    while ($row = mysql_fetch_assoc($result))
                    {
                        $sister_pod_ids[] = $row['pod_id'];
                    }
                    $sister_pod_ids = implode(',', $sister_pod_ids);

                    // Delete the sister pod relationship
                    pod_query("DELETE FROM @wp_pod_rel WHERE pod_id IN ($sister_pod_ids) AND sister_pod_id = $pod_id AND field_id = $sister_field_id", 'Cannot drop sister relationships');
                }
            }
            pod_query("DELETE FROM @wp_pod_rel WHERE pod_id = $pod_id AND field_id = $field_id", 'Cannot drop relationships');

            /*
            ==================================================
            Add rel values
            ==================================================
            */
            foreach ($rel_ids as $rel_id)
            {
                $sister_pod_id = 0;
                if (!empty($sister_field_id) && !empty($sister_datatype_id))
                {
                    $result = pod_query("SELECT id FROM @wp_pod WHERE datatype = $sister_datatype_id AND tbl_row_id = $rel_id LIMIT 1");
                    if (0 < mysql_num_rows($result))
                    {
                        $sister_pod_id = mysql_result($result, 0);
                        pod_query("INSERT INTO @wp_pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id) VALUES ($sister_pod_id, $pod_id, $sister_field_id, $tbl_row_id)", 'Cannot add sister relationships');
                    }
                }
                pod_query("INSERT INTO @wp_pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id) VALUES ($pod_id, $sister_pod_id, $field_id, $rel_id)", 'Cannot add relationships');
            }
        }

        // Call any "after" helpers
        $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$after')");
        while ($row = mysql_fetch_assoc($result))
        {
            eval('?>' . $row['phpcode']);
        }
    }
    else
    {
        die('Error: no datatype selected');
    }
}
else
{
    // Show the input form
    $obj = new Pod($datatype);
    echo $obj->showform($pod_id, $public_columns);
}
