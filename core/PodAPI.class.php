<?php
/*
==================================================
PodAPI.class.php

http://pods.uproot.us/codex/
==================================================
*/
class PodAPI
{
    var $dt;
    var $dtname;
    var $format;
    var $fields;

    function PodAPI($dtname, $format = 'php')
    {
        $this->dtname = $dtname;
        $this->format = $format;

        if (!empty($dtname))
        {
            $result = pod_query("SELECT id FROM @wp_pod_types WHERE name = '$dtname' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $this->dt = mysql_result($result, 0);
                $result = pod_query("SELECT id, name, coltype, pickval FROM @wp_pod_fields WHERE datatype = {$this->dt} ORDER BY weight");
                if (0 < mysql_num_rows($result))
                {
                    while ($row = mysql_fetch_assoc($result))
                    {
                        $this->fields[$row['name']] = $row;
                    }
                    return true;
                }
            }
            return false;
        }
    }

    /*
    ==================================================
    Import data into a pod
    ==================================================
    */
    function import($data)
    {
        if ('csv' == $this->format)
        {
            $data = $this->csv_to_php($data);
        }

        // Get the id/name pairs of all associated pick tables
        foreach ($this->fields as $field_name => $field_data)
        {
            $pickval = $field_data['pickval'];
            if ('pick' == $field_data['coltype'])
            {
                if (is_numeric($pickval))
                {
                    $res = pod_query("SELECT term_id AS id, name FROM @wp_terms ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
                elseif ('wp_page' == $pickval || 'wp_post' == $pickval)
                {
                    $res = pod_query("SELECT ID as id, post_title as name FROM @wp_posts WHERE post_type = '$pickval' ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
                elseif ('wp_user' == $pickval)
                {
                    $res = pod_query("SELECT ID as id, display_name as name FROM @wp_users ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
                else
                {
                    $res = pod_query("SELECT id, name FROM @wp_pod_tbl_{$pickval} ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
            }
        }

        // Loop through the array of items
        foreach ($data as $key => $data_row)
        {
            $set_data = array();
            $pick_columns = array();
            $table_columns = array();

            // Loop through each field (use $this->fields so only valid columns get parsed)
            foreach ($this->fields as $field_name => $field_data)
            {
                $field_id = $field_data['id'];
                $coltype = $field_data['coltype'];
                $pickval = $field_data['pickval'];
                $field_value = $data_row[$field_name];

                if (!empty($field_value))
                {
                    if ('pick' == $coltype)
                    {
                        $field_value = is_array($field_value) ? $field_value : array($field_value);
                        foreach ($field_value as $key => $pick_title)
                        {
                            if (!empty($pick_values[$field_name][$pick_title]))
                            {
                                $tbl_row_id = $pick_values[$field_name][$pick_title];
                                $pick_columns[] = "('POD_ID', '$field_id', '$tbl_row_id')";
                            }
                        }
                    }
                    else
                    {
                        $table_columns[] = $field_name;
                        $set_data[] = mysql_real_escape_string(trim($field_value));
                    }
                }
            }

            // Insert the row data
            $set_data = implode("','", $set_data);
            $table_columns = implode('`,`', $table_columns);

            // Insert table row
            $tbl_row_id = pod_query("INSERT INTO @wp_pod_tbl_{$this->dtname} (`$table_columns`) VALUES ('$set_data')");

            // Add the new wp_pod item
            $pod_name = mysql_real_escape_string(trim($data_row['name']));
            $pod_id = pod_query("INSERT INTO @wp_pod (tbl_row_id, datatype, name, created, modified) VALUES ('$tbl_row_id', '{$this->dt}', '$pod_name', NOW(), NOW())");

            // Insert the relationship (rel) data
            $pick_columns = implode(',', $pick_columns);
            $pick_columns = str_replace('POD_ID', $pod_id, $pick_columns);
            pod_query("INSERT INTO @wp_pod_rel (pod_id, field_id, tbl_row_id) VALUES $pick_columns");
        }
    }

    /*
    ==================================================
    Export all data from a pod
    ==================================================
    */
    function export()
    {
        $data = array();
        $fields = array();
        $pick_values = array();

        // Find all pick fields
        $result = pod_query("SELECT id, name, coltype, pickval FROM @wp_pod_fields WHERE datatype = {$this->dt} ORDER BY weight");
        while ($row = mysql_fetch_assoc($result))
        {
            $field_id = $row['id'];
            $field_name = $row['name'];
            $coltype = $row['coltype'];
            $pickval = $row['pickval'];

            // Store all pick values into an array
            if ('pick' == $coltype)
            {
                if (is_numeric($pickval))
                {
                    $res = pod_query("SELECT term_id AS id, name FROM @wp_terms ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
                elseif ('wp_page' == $pickval || 'wp_post' == $pickval)
                {
                    $res = pod_query("SELECT ID as id, post_title as name FROM @wp_posts WHERE post_type = '$pickval' ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
                elseif ('wp_user' == $pickval)
                {
                    $res = pod_query("SELECT ID as id, display_name as name FROM @wp_users ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
                else
                {
                    $res = pod_query("SELECT id, name FROM @wp_pod_tbl_{$pickval} ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
            }
            $fields[$field_id] = $field_name;
        }

        // Get all pick (rel) values
        $sql = "
        SELECT
            p.tbl_row_id, r.field_id, r.tbl_row_id AS item_id
        FROM
            @wp_pod_rel r
        INNER JOIN
            @wp_pod p ON p.id = r.pod_id AND p.datatype = {$this->dt}
        ORDER BY
            p.tbl_row_id
        ";
        $result = pod_query($sql);
        while ($row = mysql_fetch_assoc($result))
        {
            $item_id = $row['item_id'];
            $tbl_row_id = $row['tbl_row_id'];
            $field_name = $fields[$row['field_id']];
            $pick_array[$field_name][$tbl_row_id][] = $pick_values[$field_name][$item_id];
        }

        // Access the current datatype
        $result = pod_query("SELECT * FROM @wp_pod_tbl_{$this->dtname} ORDER BY id");
        while ($row = mysql_fetch_assoc($result))
        {
            $tmp = array();
            $row_id = $row['id'];

            foreach ($fields as $junk => $fname)
            {
                if (isset($pick_array[$fname][$row_id]))
                {
                    $tmp[$fname] = $pick_array[$fname][$row_id];
                }
                else
                {
                    $tmp[$fname] = $row[$fname];
                }
            }
            $data[] = $tmp;
        }

        if ('csv' == $this->format)
        {
            $data = $this->php_to_csv($data);
        }
        return $data;
    }

    /*
    ==================================================
    Convert a PHP array to CSV
    ==================================================
    */
    function php_to_csv($data)
    {
        // Work in progress
        return $data;
    }

    /*
    ==================================================
    Convert CSV to a PHP array
    ==================================================
    */
    function csv_to_php($data)
    {
        $delimiter = ",";
        $expr = "/$delimiter(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
        $lines = explode("\n", $data);
        $field_names = explode($delimiter, array_shift($lines));
        foreach ($lines as $line)
        {
            // Skip the empty line
            if (empty($line)) continue;
            $fields = preg_split($expr, trim($line));
            $fields = preg_replace("/^\"(.*)\"$/s","$1",$fields);
            foreach ($field_names as $key => $field)
            {
                $tmp[$field] = $fields[$key];
            }
            $out[] = $tmp;
        }
        return $out;
    }
}
