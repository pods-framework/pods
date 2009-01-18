<?php
// Include the MySQL connection
require_once(realpath(dirname(__FILE__) . '/../../../../wp-config.php'));
require_once(realpath(dirname(__FILE__) . '/../Pod.class.php'));

$save = (int) $_POST['save'];
$post_id = (int) $_POST['post_id'];
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
        if (empty($post_id))
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
        $result = pod_query("SELECT id, label, name, coltype, pickval, sister_field_id, required FROM {$table_prefix}pod_fields WHERE datatype = $datatype_id $where", 'Cannot get datatype fields');
        while ($row = mysql_fetch_assoc($result))
        {
            if (1 == $row['required'])
            {
                $req[$row['name']] = $row['coltype'];
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

        // Add or edit the post
        $post_title = mysql_real_escape_string(trim($_POST['name']));
        $post_title = stripslashes($post_title);

        if (empty($post_id))
        {
            $sql = "
            INSERT INTO
                {$table_prefix}posts (post_date, post_date_gmt, post_modified, post_modified_gmt, post_type, post_title)
            VALUES
                (NOW(), UTC_TIMESTAMP(), NOW(), UTC_TIMESTAMP(), '$datatype', '$post_title')
            ";
            $post_id = pod_query($sql, 'Cannot add new content');
        }
        else
        {
            $sql = "
            UPDATE
                {$table_prefix}posts
            SET
                post_modified = NOW(), post_modified_gmt = UTC_TIMESTAMP(), post_title = '$post_title'
            WHERE
                ID = $post_id
            LIMIT
                1
            ";
            pod_query($sql, 'Cannot edit posts table');
        }

        // See if this post_ID already has a module (removing previous module data)
        $result = pod_query("SELECT row_id FROM {$table_prefix}pod WHERE post_id = $post_id LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            $row = mysql_fetch_assoc($result);
            $table_row_id = $row['row_id'];
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
                $sister_post_ids = array();

                /*
                ==================================================
                Delete all rels (parent and sister)
                ==================================================
                */
                if (!empty($sister_field_id))
                {
                    // Get sister post IDs (a sister post's sister post is the parent post)
                    $result = pod_query("SELECT post_id FROM {$table_prefix}pod_rel WHERE sister_post_id = $post_id");
                    if (0 < mysql_num_rows($result))
                    {
                        while ($row = mysql_fetch_assoc($result))
                        {
                            $sister_post_ids[] = $row['post_id'];
                        }
                        $sister_post_ids = implode(',', $sister_post_ids);

                        // Delete the sister post relationship
                        pod_query("DELETE FROM {$table_prefix}pod_rel WHERE post_id IN ($sister_post_ids) AND sister_post_id = $post_id AND field_id = $sister_field_id", 'Cannot drop sister relationships');
                    }
                }
                pod_query("DELETE FROM {$table_prefix}pod_rel WHERE post_id = $post_id AND field_id = $field_id", 'Cannot drop relationships');
                /*
                ==================================================
                Add relationship values
                ==================================================
                */
                foreach ($term_ids as $term_id)
                {
                    $sister_post_id = 0;
                    if (!empty($sister_field_id) && !empty($sister_datatype_id))
                    {
                        $result = pod_query("SELECT post_id FROM {$table_prefix}pod WHERE datatype = $sister_datatype_id AND row_id = $term_id LIMIT 1");
                        if (0 < mysql_num_rows($result))
                        {
                            $row = mysql_fetch_assoc($result);
                            $sister_post_id = $row['post_id'];
                            pod_query("INSERT INTO {$table_prefix}pod_rel (post_id, sister_post_id, field_id, term_id) VALUES ($sister_post_id, $post_id, $sister_field_id, $table_row_id)", 'Cannot add sister relationships');
                        }
                    }
                    pod_query("INSERT INTO {$table_prefix}pod_rel (post_id, sister_post_id, field_id, term_id) VALUES ($post_id, $sister_post_id, $field_id, $term_id)", 'Cannot add relationships');
                }
            }
            elseif ('datatype' != $key && 'post_id' != $key && 'columns' != $key && 'public' != $key && 'save' != $key)
            {
                if (isset($table_row_id))
                {
                    // Update existing row
                    pod_query("UPDATE {$table_prefix}pod_tbl_$datatype SET $key = '$val' WHERE id = $table_row_id LIMIT 1");
                }
                else
                {
                    // Insert new row to data table
                    $table_row_id = pod_query("INSERT INTO {$table_prefix}pod_tbl_$datatype ($key) VALUES ('$val')", 'Cannot add new table row');

                    // Insert new row to wp_pod table
                    pod_query("INSERT INTO {$table_prefix}pod (row_id, post_id, datatype) VALUES ('$table_row_id', '$post_id', '$datatype_id')", 'Cannot add new Pod row');
                }
                $post_content .= $val;
            }
        }
        // Update wp_pod datatype
        pod_query("UPDATE {$table_prefix}pod SET datatype = $datatype_id WHERE row_id = $table_row_id AND post_id = $post_id LIMIT 1", 'Cannot modify datatype row');

        // Update "post_content", which is used for pod searching
        //$sql = "UPDATE {$table_prefix}posts SET post_content = '$post_content' WHERE ID = $post_id LIMIT 1";
        //pod_query($sql) or die('Error: Could not save post_content data');
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
    echo $obj->showform($post_id, $is_public, $public_columns);
}
