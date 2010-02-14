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

    function PodAPI($dtname = null, $format = 'php')
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
    Add/edit a pod
    ==================================================
    */
    function save_pod($params)
    {
        $params = (object) $params;

        if (empty($params->datatype))
        {
            $params->name = pods_clean_name($params->name);
            if (empty($params->name))
            {
                die('Error: Enter a pod name');
            }
            $sql = "SELECT id FROM @wp_pod_types WHERE name = '$params->name' LIMIT 1";
            pod_query($sql, 'Duplicate pod name', 'Pod name already exists');

            $pod_id = pod_query("INSERT INTO @wp_pod_types (name) VALUES ('$params->name')", 'Cannot add new pod');
            pod_query("CREATE TABLE `@wp_pod_tbl_$params->name` (id int unsigned auto_increment primary key, name varchar(128), slug varchar(128)) DEFAULT CHARSET utf8", 'Cannot add pod database table');
            pod_query("INSERT INTO @wp_pod_fields (datatype, name, label, comment, coltype, required, weight) VALUES ($pod_id, 'name', 'Name', '', 'txt', 1, 0),($pod_id, 'slug', 'Permalink', 'Leave blank to auto-generate', 'slug', 0, 1)");
            die("$pod_id"); // return as string
        }
        else
        {
            $sql = "
            UPDATE
                @wp_pod_types
            SET
                label = '$params->label',
                is_toplevel = '$params->is_toplevel',
                detail_page = '$params->detail_page',
                pre_save_helpers = '$params->pre_save_helpers',
                pre_drop_helpers = '$params->pre_drop_helpers',
                post_save_helpers = '$params->post_save_helpers',
                post_drop_helpers = '$params->post_drop_helpers'
            WHERE
                id = $params->datatype
            LIMIT
                1
            ";
            pod_query($sql, 'Cannot change Pod settings');

            $weight = 0;
            $order = (false !== strpos($params->order, ',')) ? explode(',', $params->order) : array($params->order);
            foreach ($order as $key => $field_id)
            {
                pod_query("UPDATE @wp_pod_fields SET weight = '$weight' WHERE id = '$field_id' LIMIT 1", 'Cannot change column order');
                $weight++;
            }
        }
    }

    /*
    ==================================================
    Add/edit a pod column
    ==================================================
    */
    function save_column($params)
    {
        foreach ($params as $key => $val)
        {
            ${$key} = $val;
        }

        $dbtypes = array(
            'bool' => 'bool default 0',
            'date' => 'datetime',
            'num' => 'decimal(12,2)',
            'txt' => 'varchar(128)',
            'slug' => 'varchar(128)',
            'code' => 'longtext',
            'desc' => 'longtext'
        );

        if (empty($id))
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
                $dbtype = $dbtypes[$coltype];
                pod_query("ALTER TABLE `@wp_pod_tbl_$dtname` ADD COLUMN `$name` $dbtype", 'Cannot create new column');
            }
            else
            {
                pod_query("UPDATE @wp_pod_fields SET sister_field_id = '$field_id' WHERE id = $sister_field_id LIMIT 1", 'Cannot update sister field');
            }
        }
        else
        {
            if ('id' == $name)
            {
                die("Error: $name is not editable.");
            }

            $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $datatype AND id != $id AND name = '$name' LIMIT 1";
            pod_query($sql, 'Column already exists', "$name already exists.");

            $sql = "SELECT name, coltype FROM @wp_pod_fields WHERE id = $id LIMIT 1";
            $result = pod_query($sql);

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
                        pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id = $id");
                        pod_query("DELETE FROM @wp_pod_rel WHERE field_id = $id");
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
                    id = $id
                LIMIT
                    1
                ";
                pod_query($sql, 'Cannot edit column');
            }
        }
    }

    /*
    ==================================================
    Add/edit a template
    ==================================================
    */
    function save_template($params)
    {
        $params = (object) $params;

        if (empty($params->id))
        {
            if (empty($params->name))
            {
                die('Error: Enter a template name');
            }

            $sql = "SELECT id FROM @wp_pod_templates WHERE name = '$params->name' LIMIT 1";
            pod_query($sql, 'Cannot get Templates', 'Template by this name already exists');
            $template_id = pod_query("INSERT INTO @wp_pod_templates (name, code) VALUES ('$params->name', '$params->code')", 'Cannot add new template');

            die("$template_id"); // return as string
        }
        else
        {
            pod_query("UPDATE @wp_pod_templates SET code = '$params->code' WHERE id = $params->id LIMIT 1");
        }
    }

    /*
    ==================================================
    Add/edit a pod page
    ==================================================
    */
    function save_page($params)
    {
        $params = (object) $params;

        if (empty($params->id))
        {
            if (empty($params->uri))
            {
                die('Error: Enter a page URI');
            }

            $sql = "SELECT id FROM @wp_pod_pages WHERE uri = '$params->uri' LIMIT 1";
            pod_query($sql, 'Cannot get Pod Pages', 'Page by this URI already exists');
            $page_id = pod_query("INSERT INTO @wp_pod_pages (uri, phpcode) VALUES ('$params->uri', '$params->phpcode')", 'Cannot add new page');
            die("$page_id"); // return as string
        }
        else
        {
            pod_query("UPDATE @wp_pod_pages SET title = '$params->page_title', page_template = '$params->page_template', phpcode = '$params->phpcode', precode = '$params->precode' WHERE id = $params->id LIMIT 1");
        }
    }

    /*
    ==================================================
    Add/edit a helper
    ==================================================
    */
    function save_helper($params)
    {
        $params = (object) $params;

        if (empty($params->id))
        {
            if (empty($params->name))
            {
                die('Error: Enter a helper name');
            }

            $sql = "SELECT id FROM @wp_pod_helpers WHERE name = '$params->name' LIMIT 1";
            pod_query($sql, 'Cannot get helpers', 'helper by this name already exists');
            $helper_id = pod_query("INSERT INTO @wp_pod_helpers (name, helper_type, phpcode) VALUES ('$params->name', '$params->helper_type', '$params->phpcode')", 'Cannot add new helper');
            die("$helper_id"); // return as string
        }
        else
        {
            pod_query("UPDATE @wp_pod_helpers SET phpcode = '$params->phpcode' WHERE id = $params->id LIMIT 1");
        }
    }

    /*
    ==================================================
    Modify the entire menu structure (ordering, etc)
    ==================================================
    */
    function save_menu($params)
    {
        $params = (object) $params;

        
    }

    /*
    ==================================================
    Add/edit a menu item
    ==================================================
    */
    function save_menu_item($params)
    {
        $params = (object) $params;

        if (empty($params->id))
        {
            // get the "rgt" value of the parent
            $result = pod_query("SELECT rgt FROM @wp_pod_menu WHERE id = $params->parent_menu_id LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $rgt = $row['rgt'];

            // Increase all "lft" values by 2 if > "rgt"
            pod_query("UPDATE @wp_pod_menu SET lft = lft + 2 WHERE lft > $rgt");

            // Increase all "rgt" values by 2 if >= "rgt"
            pod_query("UPDATE @wp_pod_menu SET rgt = rgt + 2 WHERE rgt >= $rgt");

            // Add new item: "lft" = rgt, "rgt" = rgt + 1
            $lft = $rgt;
            $rgt = ($rgt + 1);
            $menu_id = pod_query("INSERT INTO @wp_pod_menu (uri, title, lft, rgt) VALUES ('$params->menu_uri', '$params->menu_title', $lft, $rgt)");

            die("$menu_id"); // return as string
        }
        else
        {
            pod_query("UPDATE @wp_pod_menu SET uri = '$params->menu_uri', title = '$params->menu_title' WHERE id = $params->id LIMIT 1");
        }
    }

    /*
    ==================================================
    Save roles
    ==================================================
    */
    function save_roles($params)
    {
        $roles = array();
        foreach ($params as $key => $val)
        {
            if ('action' != $key)
            {
                $tmp = empty($val) ? array() : explode(',', $val);
                $roles[$key] = $tmp;
            }
        }
        delete_option('pods_roles');
        add_option('pods_roles', serialize($roles));
    }

    /*
    ==================================================
    Save a content item
    ==================================================
    */
    function save_pod_item($params)
    {
        foreach ($params as $key => $val)
        {
            ${$key} = $val;
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
            if (false === empty($_SESSION[$uri_hash][$form_count]['columns']))
            {
                $public_columns = unserialize($_SESSION[$uri_hash][$form_count]['columns']);

                if (is_array($public_columns))
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
            $file_columns = array();
            $pick_columns = array();
            $table_columns = array();

            // Loop through $active_columns, separating table data from PICK/file data
            foreach ($active_columns as $key)
            {
                $val = $$key;
                $type = $fields[$key]['coltype'];
                $label = $fields[$key]['label'];
                $label = empty($label) ? $key : $label;

                // Verify required fields
                if (1 == $fields[$key]['required'])
                {
                    if ('' == $val || null == $val)
                    {
                        die("Error: $label is empty.");
                    }
                    elseif ('date' == $type && false === preg_match("/^(\d{4})-([01][0-9])-([0-3][0-9]) ([0-2][0-9]:[0-5][0-9]:[0-5][0-9])$/", $val))
                    {
                        die("Error: $label is an invalid date.");
                    }
                    elseif ('num' == $type && false === is_numeric($val))
                    {
                        die("Error: $label is an invalid number.");
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
                    pod_query($sql, 'Not unique', "$label needs to be unique.");
                }

                // Verify slug columns
                if ('slug' == $type)
                {
                    $slug_val = empty($$key) ? $name : $$key;
                    $val = pods_unique_slug($slug_val, $key, $datatype, $datatype_id, $pod_id);
                }

                if ('pick' == $type)
                {
                    $pick_columns[$key] = empty($val) ? array() : explode(',', $val);
                }
                elseif ('file' == $type)
                {
                    $file_columns[$key] = empty($val) ? array() : explode(',', $val);
                }
                else
                {
                    $table_columns[$key] = $val;
                }
            }

            // Get helper code
            $result = pod_query("SELECT pre_save_helpers, post_save_helpers FROM @wp_pod_types WHERE id = $datatype_id");
            $row = mysql_fetch_assoc($result);
            $pre_save_helpers = str_replace(',', "','", $row['pre_save_helpers']);
            $post_save_helpers = str_replace(',', "','", $row['post_save_helpers']);

            // Plugin hook
            do_action('pods_pre_save_pod_item');

            // Call any "pre-save" helpers
            $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$pre_save_helpers')");
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

            // Save table row data
            foreach ($table_columns as $key => $val)
            {
                $set_data[] = "`$key` = '$val'";
            }
            $set_data = implode(',', $set_data);

            // Get the item name
            $name = mysql_real_escape_string(trim(stripslashes($name)));

            // Insert table row
            pod_query("UPDATE `@wp_pod_tbl_$datatype` SET $set_data WHERE id = $tbl_row_id LIMIT 1");

            // Update wp_pod
            pod_query("UPDATE @wp_pod SET tbl_row_id = $tbl_row_id, datatype = $datatype_id, name = '$name', modified = NOW() WHERE id = $pod_id LIMIT 1", 'Cannot modify datatype row');

            // Save file relationships
            foreach ($file_columns as $key => $attachment_ids)
            {
                // Get the field_id
                $result = pod_query("SELECT id FROM @wp_pod_fields WHERE datatype = $datatype_id AND name = '$key' LIMIT 1");
                $field_id = mysql_result($result, 0);

                // Remove existing relationships
                pod_query("DELETE FROM @wp_pod_rel WHERE pod_id = $pod_id AND field_id = $field_id");

                // Add new relationships
                foreach ($attachment_ids as $attachment_id)
                {
                    pod_query("INSERT INTO @wp_pod_rel (pod_id, field_id, tbl_row_id) VALUES ($pod_id, $field_id, $attachment_id)");
                }
            }

            // Save PICK relationships
            foreach ($pick_columns as $key => $rel_ids)
            {
                $field_id = $fields[$key]['id'];
                $pickval = $fields[$key]['pickval'];
                $sister_datatype_id = $datatypes[$pickval];
                $sister_field_id = $fields[$key]['sister_field_id'];
                $sister_field_id = empty($sister_field_id) ? 0 : $sister_field_id;
                $sister_pod_ids = array();

                // Delete parent & sister rels
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

                // Add rel values
                $rel_weight = 0;
                foreach ($rel_ids as $rel_id)
                {
                    $sister_pod_id = 0;
                    if (!empty($sister_field_id) && !empty($sister_datatype_id))
                    {
                        $result = pod_query("SELECT id FROM @wp_pod WHERE datatype = $sister_datatype_id AND tbl_row_id = $rel_id LIMIT 1");
                        if (0 < mysql_num_rows($result))
                        {
                            $sister_pod_id = mysql_result($result, 0);
                            pod_query("INSERT INTO @wp_pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id, weight) VALUES ($sister_pod_id, $pod_id, $sister_field_id, $tbl_row_id, $rel_weight)", 'Cannot add sister relationships');
                        }
                    }
                    pod_query("INSERT INTO @wp_pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id, weight) VALUES ($pod_id, $sister_pod_id, $field_id, $rel_id, $rel_weight)", 'Cannot add relationships');
                }
                $rel_weight++;
            }

            // Plugin hook
            do_action('pods_post_save_pod_item');

            // Call any "post-save" helpers
            $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$post_save_helpers')");
            while ($row = mysql_fetch_assoc($result))
            {
                eval('?>' . $row['phpcode']);
            }
        }
    }

    /*
    ==================================================
    Drop a pod
    ==================================================
    */
    function drop_pod($params)
    {
        $params = (object) $params;

        $fields = '0';
        pod_query("DELETE FROM @wp_pod_types WHERE id = $params->id LIMIT 1");
        $result = pod_query("SELECT id FROM @wp_pod_fields WHERE datatype = $params->id");
        while ($row = mysql_fetch_assoc($result))
        {
            $fields .= ',' . $row['id'];
        }

        pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($fields)");
        pod_query("DELETE FROM @wp_pod_fields WHERE datatype = $params->id");
        pod_query("DELETE FROM @wp_pod_rel WHERE field_id IN ($fields)");
        pod_query("DELETE FROM @wp_pod WHERE datatype = $params->id");
        pod_query("DROP TABLE `@wp_pod_tbl_$params->dtname`");
    }

    /*
    ==================================================
    Drop a pod column
    ==================================================
    */
    function drop_column($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT name, coltype FROM @wp_pod_fields WHERE id = $params->id LIMIT 1");
        list($field_name, $coltype) = mysql_fetch_array($result);

        if ('pick' == $coltype)
        {
            // Remove any orphans
            $result = pod_query("SELECT id FROM @wp_pod_fields WHERE sister_field_id = $params->id");
            if (0 < mysql_num_rows($result))
            {
                while ($row = mysql_fetch_assoc($result))
                {
                    $related_fields[] = $row['id'];
                }
                $related_fields = implode(',', $related_fields);
                pod_query("DELETE FROM @wp_pod_rel WHERE field_id IN ($related_fields)");
                pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($related_fields)");
            }
        }
        elseif ('file' != $coltype)
        {
            pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` DROP COLUMN `$field_name`");
        }

        pod_query("DELETE FROM @wp_pod_fields WHERE id = $params->id LIMIT 1");
        pod_query("DELETE FROM @wp_pod_rel WHERE field_id = $params->id");
    }

    /*
    ==================================================
    Drop a template
    ==================================================
    */
    function drop_template($params)
    {
        $params = (object) $params;
        pod_query("DELETE FROM @wp_pod_templates WHERE id = $params->id LIMIT 1");
    }

    /*
    ==================================================
    Drop a pod page
    ==================================================
    */
    function drop_page($params)
    {
        $params = (object) $params;
        pod_query("DELETE FROM @wp_pod_pages WHERE id = $params->id LIMIT 1");
    }

    /*
    ==================================================
    Drop a helper
    ==================================================
    */
    function drop_helper($params)
    {
        $params = (object) $params;
        pod_query("DELETE FROM @wp_pod_helpers WHERE id = $params->id LIMIT 1");
    }

    /*
    ==================================================
    Drop a menu item and its children
    ==================================================
    */
    function drop_menu_item($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT lft, rgt, (rgt - lft + 1) AS width FROM @wp_pod_menu WHERE id = $params->id LIMIT 1");
        list($lft, $rgt, $width) = mysql_fetch_array($result);

        pod_query("DELETE from @wp_pod_menu WHERE lft BETWEEN $lft AND $rgt");
        pod_query("UPDATE @wp_pod_menu SET rgt = rgt - $width WHERE rgt > $rgt");
        pod_query("UPDATE @wp_pod_menu SET lft = lft - $width WHERE lft > $rgt");
    }

    /*
    ==================================================
    Drop a menu item and its children
    ==================================================
    */
    function drop_pod_item($params)
    {
        $params = (object) $params;

        $sql = "
        SELECT
            p.tbl_row_id, t.id, t.name
        FROM
            @wp_pod p
        INNER JOIN
            @wp_pod_types t ON t.id = p.datatype
        WHERE
            p.id = $params->pod_id
        LIMIT
            1
        ";
        $result = pod_query($sql);
        $row = mysql_fetch_assoc($result);
        $dt = $row['id'];
        $dtname = $row['name'];
        $tbl_row_id = $row['tbl_row_id'];

        // Get helper code
        $result = pod_query("SELECT pre_drop_helpers, post_drop_helpers FROM @wp_pod_types WHERE id = $dt");
        $row = mysql_fetch_assoc($result);
        $pre_drop_helpers = str_replace(',', "','", $row['pre_drop_helpers']);
        $post_drop_helpers = str_replace(',', "','", $row['post_drop_helpers']);

        // Plugin hook
        do_action('pods_pre_drop_pod_item');

        // Pre-drop helpers
        $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$pre_drop_helpers')");
        while ($row = mysql_fetch_assoc($result))
        {
            eval('?>' . $row['phpcode']);
        }

        pod_query("DELETE FROM `@wp_pod_tbl_$dtname` WHERE id = $tbl_row_id LIMIT 1");
        pod_query("UPDATE @wp_pod_rel SET sister_pod_id = NULL WHERE sister_pod_id = $params->pod_id");
        pod_query("DELETE FROM @wp_pod WHERE id = $params->pod_id LIMIT 1");
        pod_query("DELETE FROM @wp_pod_rel WHERE pod_id = $params->pod_id");

        // Plugin hook
        do_action('pods_post_drop_pod_item');

        // Post-drop helpers
        $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$post_drop_helpers')");
        while ($row = mysql_fetch_assoc($result))
        {
            eval('?>' . $row['phpcode']);
        }
    }

    /*
    ==================================================
    Load a pod
    ==================================================
    */
    function load_pod($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT * FROM @wp_pod_types WHERE id = $params->id LIMIT 1");
        $module = mysql_fetch_assoc($result);

        $sql = "
            SELECT
                id, name, coltype, pickval, required, weight
            FROM
                @wp_pod_fields
            WHERE
                datatype = $params->id
            ORDER BY
                weight
            ";

        $fields = array();
        $result = pod_query($sql);
        while ($row = mysql_fetch_assoc($result))
        {
            $fields[] = $row;
        }

        // Combine the fields into the $module array
        $module['fields'] = $fields;
        return $module;
    }

    /*
    ==================================================
    Load a pod column
    ==================================================
    */
    function load_column($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE id = $params->id LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /*
    ==================================================
    Load a template
    ==================================================
    */
    function load_template($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT * FROM @wp_pod_templates WHERE id = $params->id LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /*
    ==================================================
    Load a pod page
    ==================================================
    */
    function load_page($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT * FROM @wp_pod_pages WHERE id = $params->id LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /*
    ==================================================
    Load a helper item
    ==================================================
    */
    function load_helper($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT * FROM @wp_pod_helpers WHERE id = $params->id LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /*
    ==================================================
    Load a menu item
    ==================================================
    */
    function load_menu_item($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT uri, title FROM @wp_pod_menu WHERE id = $params->id LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /*
    ==================================================
    Load an item's input form
    ==================================================
    */
    function load_pod_item($params)
    {
        $params = (object) $params;
        $obj = new Pod($params->datatype);
        return $obj->showform((int) $params->pod_id, $params->public_columns);
    }

    /*
    ==================================================
    Load a bi-directional (sister) column
    ==================================================
    */
    function load_sister_fields($params)
    {
        $params = (object) $params;

        if (!empty($params->pickval) && is_string($params->pickval))
        {
            $result = pod_query("SELECT id FROM @wp_pod_types WHERE name = '$params->pickval' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $sister_datatype = mysql_result($result, 0);

                $result = pod_query("SELECT name FROM @wp_pod_types WHERE id = $params->datatype LIMIT 1");
                if (0 < mysql_num_rows($result))
                {
                    $datatype_name = mysql_result($result, 0);

                    $result = pod_query("SELECT id, name FROM @wp_pod_fields WHERE datatype = $sister_datatype AND pickval = '$datatype_name'");
                    if (0 < mysql_num_rows($result))
                    {
                        while ($row = mysql_fetch_assoc($result))
                        {
                            $sister_fields[] = $row;
                        }
                        die(json_encode($sister_fields));
                    }
                }
            }
        }
    }

    /*
    ==================================================
    Import data
    ==================================================
    */
    function import($data, $numeric_mode = false)
    {
        if ('csv' == $this->format)
        {
            $data = $this->csv_to_php($data);
        }

        pod_query("SET NAMES utf8");
        pod_query("SET CHARACTER SET utf8");

        // Get the id/name pairs of all associated pick/file tables
        $pick_values = $file_values = array();
        foreach ($this->fields as $field_name => $field_data)
        {
            $pickval = $field_data['pickval'];
            if ('file' == $field_data['coltype'])
            {
                $res = pod_query("SELECT ID as id, guid as name FROM @wp_posts WHERE post_type = 'attachment' ORDER BY id");
                while ($item = mysql_fetch_assoc($res))
                {
                    $file_url = str_replace(get_bloginfo('url'), '', $item['name']);
                    $file_values[$field_name][$file_url] = $item['id'];
                    $file_values[$field_name][$item['name']] = $item['id'];
                }
            }
            elseif ('pick' == $field_data['coltype'])
            {
                if ('wp_taxonomy' == $pickval)
                {
                    $res = pod_query("SELECT term_id AS id, name FROM @wp_terms ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
                elseif ('wp_page' == $pickval || 'wp_post' == $pickval)
                {
                    $pickval = str_replace('wp_', '', $pickval);
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
        $pod_ids = array();
        foreach ($data as $key => $data_row)
        {
            $set_data = array();
            $pick_columns = array();
            $table_columns = array();

            // Loop through each field (use $this->fields so only valid columns get parsed)
            $pod_ids = array();
            foreach ($this->fields as $field_name => $field_data)
            {
                $field_id = $field_data['id'];
                $coltype = $field_data['coltype'];
                $pickval = $field_data['pickval'];
                $field_value = $data_row[$field_name];

                if (!empty($field_value))
                {
                    if ('pick' == $coltype || 'file' == $coltype)
                    {
                        $field_value = is_array($field_value) ? $field_value : array($field_value);
                        foreach ($field_value as $key => $pick_title)
                        {
                            if (is_int($pick_title) && false !== $numeric_mode)
                            {
                                $pick_columns[] = "('POD_ID', '$field_id', '$pick_title')";
                            }
                            elseif (!empty($pick_values[$field_name][$pick_title]))
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
            $pod_ids[] = $pod_id;

            // Insert the relationship (rel) data
            if (!empty($pick_columns))
            {
                $pick_columns = implode(',', $pick_columns);
                $pick_columns = str_replace('POD_ID', $pod_id, $pick_columns);
                pod_query("INSERT INTO @wp_pod_rel (pod_id, field_id, tbl_row_id) VALUES $pick_columns");
            }
        }
        return $pod_ids;
    }

    /*
    ==================================================
    Export data
    ==================================================
    */
    function export()
    {
        $data = array();
        $fields = array();
        $pick_values = array();

        // Find all pick/file fields
        $result = pod_query("SELECT id, name, coltype, pickval FROM @wp_pod_fields WHERE datatype = {$this->dt} ORDER BY weight");
        while ($row = mysql_fetch_assoc($result))
        {
            $field_id = $row['id'];
            $field_name = $row['name'];
            $coltype = $row['coltype'];
            $pickval = $row['pickval'];

            // Store all pick/file values into an array
            if ('file' == $coltype)
            {
                $res = pod_query("SELECT ID AS id, guid AS name FROM @wp_posts WHERE post_type = 'attachment' ORDER BY id");
                while ($item = mysql_fetch_assoc($res))
                {
                    $pick_values[$field_name][$item['id']] = $item['name'];
                }
            }
            elseif ('pick' == $coltype)
            {
                if ('wp_taxonomy' == $pickval)
                {
                    $res = pod_query("SELECT term_id AS id, name FROM @wp_terms ORDER BY id");
                    while ($item = mysql_fetch_assoc($res))
                    {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
                elseif ('wp_page' == $pickval || 'wp_post' == $pickval)
                {
                    $pickval = str_replace('wp_', '', $pickval);
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

        // Get all pick rel values
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
        $data = str_replace("\r\n", "\n", $data);
        $data = str_replace("\r", "\n", $data);
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
