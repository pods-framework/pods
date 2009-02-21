<?php
// Include the MySQL connection
require_once(realpath(dirname(__FILE__) . '/../../../../wp-config.php'));
require_once(realpath(dirname(__FILE__) . '/../Pod.class.php'));

$save = (int) $_POST['save'];
$pod_id = (int) $_POST['pod_id'];
$datatype = $_POST['datatype'];

// Determine whether the form is public
$is_public = (int) $_POST['public'];

if ($save)
{
    if ($datatype)
    {
        // Get array of datatypes
        $result = pod_query("SELECT id, name FROM {$table_prefix}pod_types");
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
            $public_columns = unserialize(stripslashes($_POST['columns']));

            if (!empty($public_columns))
            {
                foreach ($public_columns as $key => $val)
                {
                    $where[] = is_array($public_columns[$key]) ? $key : $val;
                }
                $where = "AND name IN ('" . implode("','", $where) . "')";
            }
        }

        // Get the datatype fields
        $result = pod_query("SELECT id, label, name, coltype, pickval, sister_field_id, required, `unique`, `multiple` FROM {$table_prefix}pod_fields WHERE datatype = $datatype_id $where", 'Cannot get datatype fields');
        while ($row = mysql_fetch_assoc($result))
        {
            if (1 == $row['required'])
            {
                $req[$row['name']] = $row['coltype'];
            }
            if (1 == $row['unique'])
            {
                $unique[] = $row['name'];
            }
            if ('slug' == $row['coltype'])
            {
                $slug_column = $row['name'];
                $slug_value = empty($_POST[$slug_column]) ? $_POST['name'] : $_POST[$slug_column];
                $slug_value = sanitize_title($slug_value);
                $_POST[$slug_column] = $slug_value;
            }
            $fields[$row['name']] = $row;
        }

        // Verify all required fields
        foreach ($req as $name => $type)
        {
            $val = $_POST[$name];

            if (empty($val))
            {
                die("Error: The $name column is empty.");
            }
            elseif ('date' == $type && false === preg_match("/^(\d{4})-([01][0-9])-([0-3][0-9]) ([0-2][0-9]:[0-5][0-9]:[0-5][0-9])$/", $val))
            {
                die("Error: The $name column is an invalid date.");
            }
            elseif ('num' == $type && !is_numeric($val))
            {
                die("Error: The $name column is an invalid number.");
            }
        }

        // Ensure unique values
        if (isset($unique))
        {
            foreach ($unique as $key => $name)
            {
                $val = $_POST[$name];

                $exclude = '';
                if (!empty($pod_id))
                {
                    $result = pod_query("SELECT tbl_row_id FROM {$table_prefix}pod WHERE id = $pod_id LIMIT 1");
                    $tbl_row_id = mysql_result($result, 0);
                    $exclude = "AND id != $tbl_row_id";
                }
                $sql = "SELECT id FROM {$table_prefix}pod_tbl_$datatype WHERE $name = '$val' $exclude LIMIT 1";
                pod_query($sql, 'Not a unique value', "The $name value needs to be unique.");
            }
        }

        // Add or edit the pod
        $name = mysql_real_escape_string(trim($_POST['name']));
        $name = stripslashes($name);

        if (empty($pod_id))
        {
            $sql = "INSERT INTO {$table_prefix}pod (datatype, name, created, modified) VALUES ('$datatype_id', '$name', NOW(), NOW())";
            $pod_id = pod_query($sql, 'Cannot add new content');
        }

        // See if this pod_id already has a module (removing previous module data)
        $result = pod_query("SELECT tbl_row_id FROM {$table_prefix}pod WHERE id = $pod_id LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            $tbl_row_id = mysql_result($result, 0);
        }

        // Cleanse the $_POST variables
        foreach ($_POST as $key => $val)
        {
            $val = mysql_real_escape_string(stripslashes(trim($val)));
            if ('pick' == $fields[$key]['coltype'])
            {
                // Add rel table entry for each value
                $term_ids = trim($val);
                if (!empty($term_ids))
                {
                    $term_ids = explode(',', $val);
                }
                $field_id = $fields[$key]['id'];
                $pickval = $fields[$key]['pickval'];
                $sister_datatype_id = $datatypes[$pickval];
                $sister_field_id = $fields[$key]['sister_field_id'];
                $sister_field_id = empty($sister_field_id) ? 0 : $sister_field_id;
                $sister_pod_ids = array();

                /*
                ==================================================
                Delete all rels (parent and sister)
                ==================================================
                */
                if (!empty($sister_field_id))
                {
                    // Get sister pod IDs (a sister pod's sister pod is the parent pod)
                    $result = pod_query("SELECT pod_id FROM {$table_prefix}pod_rel WHERE sister_pod_id = $pod_id");
                    if (0 < mysql_num_rows($result))
                    {
                        while ($row = mysql_fetch_assoc($result))
                        {
                            $sister_pod_ids[] = $row['pod_id'];
                        }
                        $sister_pod_ids = implode(',', $sister_pod_ids);

                        // Delete the sister pod relationship
                        pod_query("DELETE FROM {$table_prefix}pod_rel WHERE pod_id IN ($sister_pod_ids) AND sister_pod_id = $pod_id AND field_id = $sister_field_id", 'Cannot drop sister relationships');
                    }
                }
                pod_query("DELETE FROM {$table_prefix}pod_rel WHERE pod_id = $pod_id AND field_id = $field_id", 'Cannot drop relationships');
                /*
                ==================================================
                Add relationship values
                ==================================================
                */
                foreach ($term_ids as $term_id)
                {
                    $sister_pod_id = 0;
                    if (!empty($sister_field_id) && !empty($sister_datatype_id))
                    {
                        $result = pod_query("SELECT id FROM {$table_prefix}pod WHERE datatype = $sister_datatype_id AND tbl_row_id = $term_id LIMIT 1");
                        if (0 < mysql_num_rows($result))
                        {
                            $sister_pod_id = mysql_result($result, 0);
                            pod_query("INSERT INTO {$table_prefix}pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id) VALUES ($sister_pod_id, $pod_id, $sister_field_id, $tbl_row_id)", 'Cannot add sister relationships');
                        }
                    }
                    pod_query("INSERT INTO {$table_prefix}pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id) VALUES ($pod_id, $sister_pod_id, $field_id, $term_id)", 'Cannot add relationships');
                }
            }
            elseif ('datatype' != $key && 'pod_id' != $key && 'columns' != $key && 'public' != $key && 'save' != $key)
            {
                if (isset($tbl_row_id))
                {
                    // Update existing row
                    pod_query("UPDATE {$table_prefix}pod_tbl_$datatype SET $key = '$val' WHERE id = $tbl_row_id LIMIT 1");
                }
                else
                {
                    // Insert new row to data table
                    $tbl_row_id = pod_query("INSERT INTO {$table_prefix}pod_tbl_$datatype ($key) VALUES ('$val')", 'Cannot add new table row');
                }
            }
        }
        // Update wp_pod datatype
        pod_query("UPDATE {$table_prefix}pod SET tbl_row_id = $tbl_row_id, datatype = $datatype_id, name = '$name', modified = NOW() WHERE id = $pod_id LIMIT 1", 'Cannot modify datatype row');

        // Insert "after helpers"
        
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
    echo $obj->showform($pod_id, $is_public, $public_columns);
}

