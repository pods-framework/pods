<?php
class PodAPI
{
    var $dt;
    var $dtname;
    var $format;
    var $fields;

    /**
     * Store and retrieve data programatically
     *
     * @param string $dtname (optional) The pod name
     * @param string $format (optional) Format for import/export, "php" or "csv"
     * @since 1.7.1
     */
    function __construct($dtname = null, $format = 'php')
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

    /**
     * Add or edit a content type
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     * $params['label'] string The datatype label
     * $params['is_toplevel'] bool Display the pod as a top-level admin menu
     * $params['detail_page'] string The URI for single pod items
     * $params['pre_save_helpers'] string Comma-separated list of helper names
     * $params['pre_drop_helpers'] string Comma-separated list of helper names
     * $params['post_save_helpers'] string Comma-separated list of helper names
     * $params['post_drop_helpers'] string Comma-separated list of helper names
     * $params['order'] string Comma-separated list of field IDs
     *
     * @todo Ability to edit a single DB column (e.g. "detail_page")
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_pod($params)
    {
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Add new pod
        if (empty($params->id))
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
        // Edit existing pod
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
                id = $params->id
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

    /**
     * Add or edit a column within a content type
     *
     * $params['id'] int The field ID
     * $params['name'] string The field name
     * $params['datatype'] int The datatype ID
     * $params['coltype'] string The column type ("txt", "desc", "pick", etc)
     * $params['sister_field_id'] int (optional) The related field ID
     * $params['dtname'] string The datatype name
     * $params['pickval'] string The related PICK pod name
     * $params['label'] string The field label
     * $params['comment'] string The field comment
     * $params['display_helper'] string (optional) The display helper name
     * $params['input_helper'] string (optional) The input helper name
     * $params['pick_filter'] string (optional) WHERE clause for PICK fields
     * $params['pick_orderby'] string (optional) ORDER BY clause for PICK fields
     * $params['required'] bool Is the field required?
     * $params['unique'] bool Is the field unique?
     * $params['multiple'] bool Is the PICK dropdown a multi-select?
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_column($params)
    {
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        $dbtypes = array(
            'bool' => 'bool default 0',
            'date' => 'datetime',
            'num' => 'decimal(12,2)',
            'txt' => 'varchar(128)',
            'slug' => 'varchar(128)',
            'code' => 'longtext',
            'desc' => 'longtext'
        );

        // Add new column
        if (empty($params->id))
        {
            $params->name = pods_clean_name($params->name);
            if (empty($params->name))
            {
                die('Error: Enter a column name');
            }
            elseif (in_array($params->name, array('id', 'name', 'type', 'created', 'modified')))
            {
                die("Error: $params->name is a reserved name");
            }
            $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $params->datatype AND name = '$params->name' LIMIT 1";
            pod_query($sql, 'Cannot get fields', 'Column by this name already exists');

            if ('slug' == $params->coltype)
            {
                $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $params->datatype AND coltype = 'slug' LIMIT 1";
                pod_query($sql, 'Too many permalinks', 'This pod already has a permalink column');
            }

            // Sink the new column to the bottom of the list
            $weight = 0;
            $result = pod_query("SELECT weight FROM @wp_pod_fields WHERE datatype = $params->datatype ORDER BY weight DESC LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $row = mysql_fetch_assoc($result);
                $weight = (int) $row['weight'] + 1;
            }

            $params->sister_field_id = (int) $params->sister_field_id;
            $field_id = pod_query("INSERT INTO @wp_pod_fields (datatype, name, label, comment, display_helper, input_helper, coltype, pickval, pick_filter, pick_orderby, sister_field_id, required, `unique`, `multiple`, weight) VALUES ('$params->datatype', '$params->name', '$params->label', '$params->comment', '$params->display_helper', '$params->input_helper', '$params->coltype', '$params->pickval', '$params->pick_filter', '$params->pick_orderby', '$params->sister_field_id', '$params->required', '$params->unique', '$params->multiple', '$weight')", 'Cannot add new field');

            if ('pick' != $params->coltype && 'file' != $params->coltype)
            {
                $dbtype = $dbtypes[$params->coltype];
                pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` ADD COLUMN `$params->name` $dbtype", 'Cannot create new column');
            }
            else
            {
                pod_query("UPDATE @wp_pod_fields SET sister_field_id = '$field_id' WHERE id = $params->sister_field_id LIMIT 1", 'Cannot update sister field');
            }
        }
        // Edit existing column
        else
        {
            if ('id' == $params->name)
            {
                die("Error: $params->name is not editable.");
            }

            $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $params->datatype AND id != $params->id AND name = '$params->name' LIMIT 1";
            pod_query($sql, 'Column already exists', "$params->name already exists.");

            $sql = "SELECT name, coltype FROM @wp_pod_fields WHERE id = $params->id LIMIT 1";
            $result = pod_query($sql);

            if (0 < mysql_num_rows($result))
            {
                $row = mysql_fetch_assoc($result);
                $old_coltype = $row['coltype'];
                $old_name = $row['name'];

                $dbtype = $dbtypes[$params->coltype];
                $pickval = ('pick' != $params->coltype || empty($params->pickval)) ? '' : "$params->pickval";
                $params->sister_field_id = (int) $params->sister_field_id;

                if ($params->coltype != $old_coltype)
                {
                    if ('pick' == $params->coltype || 'file' == $params->coltype)
                    {
                        if ('pick' != $old_coltype && 'file' != $old_coltype)
                        {
                            pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` DROP COLUMN `$old_name`");
                        }
                    }
                    elseif ('pick' == $old_coltype || 'file' == $old_coltype)
                    {
                        pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` ADD COLUMN `$params->name` $dbtype", 'Cannot create column');
                        pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id = $params->id");
                        pod_query("DELETE FROM @wp_pod_rel WHERE field_id = $params->id");
                    }
                    else
                    {
                        pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` CHANGE `$old_name` `$params->name` $dbtype");
                    }
                }
                elseif ($params->name != $old_name && 'pick' != $params->coltype && 'file' != $params->coltype)
                {
                    pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` CHANGE `$old_name` `$params->name` $dbtype");
                }

                $sql = "
                UPDATE
                    @wp_pod_fields
                SET
                    name = '$params->name',
                    label = '$params->label',
                    comment = '$params->comment',
                    coltype = '$params->coltype',
                    pickval = '$params->pickval',
                    display_helper = '$params->display_helper',
                    input_helper = '$params->input_helper',
                    pick_filter = '$params->pick_filter',
                    pick_orderby = '$params->pick_orderby',
                    sister_field_id = '$params->sister_field_id',
                    required = '$params->required',
                    `unique` = '$params->unique',
                    `multiple` = '$params->multiple'
                WHERE
                    id = $params->id
                LIMIT
                    1
                ";
                pod_query($sql, 'Cannot edit column');
            }
        }
    }

    /**
     * Add or edit a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     * $params['code'] string The template code
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_template($params)
    {
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Add new template
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
        // Edit existing template
        else
        {
            pod_query("UPDATE @wp_pod_templates SET code = '$params->code' WHERE id = $params->id LIMIT 1");
        }
    }

    /**
     * Add or edit a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     * $params['phpcode'] string The page code
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_page($params)
    {
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Add new page
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
        // Edit existing page
        else
        {
            pod_query("UPDATE @wp_pod_pages SET title = '$params->page_title', page_template = '$params->page_template', phpcode = '$params->phpcode', precode = '$params->precode' WHERE id = $params->id LIMIT 1");
        }
    }

    /**
     * Add or edit a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     * $params['helper_type'] string The helper type ("pre_save", "display", etc)
     * $params['phpcode'] string The helper code
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_helper($params)
    {
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Add new helper
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
        // Edit existing helper
        else
        {
            pod_query("UPDATE @wp_pod_helpers SET phpcode = '$params->phpcode' WHERE id = $params->id LIMIT 1");
        }
    }

    /**
     * Add or edit a single menu item
     *
     * $params['id'] int The menu ID
     * $params['parent_menu_id'] int The parent menu ID
     * $params['menu_uri'] string The menu URI
     * $params['menu_title'] string The menu title
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_menu_item($params)
    {
        $params = (object) $params;

        // Add new menu item
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
        // Edit existing menu item
        else
        {
            pod_query("UPDATE @wp_pod_menu SET uri = '$params->menu_uri', title = '$params->menu_title' WHERE id = $params->id LIMIT 1");
        }
    }

    /**
     * Save the entire role structure
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
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

    /**
     * Retrieve an associative array of table values
     *
     * $params['table'] string The table name (default: "types")
     * $params['columns'] string Comma-separated string of columns (default: "*")
     * $params['orderby'] string MySQL ORDER BY clause (default: "id ASC")
     * $params['where'] string MySQL WHERE clause (default: 1)
     * $params['array_key'] string The key column for the returned associative array (default: "id")
     *
     * @param array $params An associative array of parameters
     * @return array The table data array
     * @since 1.8.5
     */
    function get_table_data($params)
    {
        $params = is_array($params) ? $params : array();
        $defaults = array(
            'table' => 'types',
            'columns' => '*',
            'orderby' => 'id ASC',
            'where' => 1,
            'array_key' => 'id'
        );
        $params = (object) array_merge($defaults, $params);
        $result = pod_query("SELECT $params->columns FROM @wp_pod_$params->table WHERE $params->where ORDER BY $params->orderby");
        if (0 < mysql_num_rows($result))
        {
            while ($row = mysql_fetch_assoc($result))
            {
                $data[$row[$params->array_key]] = $row;
            }
            return $data;
        }
    }

    /**
     * Add or edit a single pod item
     *
     * $params['datatype'] string The datatype name
     * $params['columns'] array (optional) Associative array of column names + values
     * $params['pod_id'] int The item's ID from the wp_pod table
     * $params['tbl_row_id'] int The item's ID from the wp_pod_tbl_* table
     *
     * @param array $params An associative array of parameters
     * @return int The table row ID
     * @since 1.7.9
     */
    function save_pod_item($params)
    {
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Get array of datatypes
        $datatypes = $this->get_table_data(array('array_key' => 'name', 'columns' => 'id, name'));
        $datatype_id = $datatypes[$params->datatype]['id'];

        // Get the datatype fields
        $opts = array('table' => 'fields', 'where' => "datatype = $datatype_id", 'orderby' => 'weight', 'array_key' => 'name');
        $columns = $this->get_table_data($opts);

        // Find the active columns (loop through $params->columns to retain order)
        if (!empty($params->columns) && is_array($params->columns))
        {
            foreach ($params->columns as $column_name => $column_val)
            {
                if (isset($columns[$column_name]))
                {
                    $columns[$column_name]['value'] = $column_val;
                    $active_columns[] = $column_name;
                }
            }
            unset($params->columns);
        }

        // Load all helpers
        $result = pod_query("SELECT pre_save_helpers, post_save_helpers FROM @wp_pod_types WHERE id = $datatype_id");
        $row = mysql_fetch_assoc($result);
        $pre_save_helpers = str_replace(',', "','", $row['pre_save_helpers']);
        $post_save_helpers = str_replace(',', "','", $row['post_save_helpers']);

        // Plugin hook
        do_action('pods_pre_save_pod_item');

        // Call any pre-save helpers
        $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$pre_save_helpers')");
        while ($row = mysql_fetch_assoc($result))
        {
            eval('?>' . $row['phpcode']);
        }

        // Loop through each active column, validating and preparing the table data
        foreach ($active_columns as $key)
        {
            $val = $columns[$key]['value'];
            $type = $columns[$key]['coltype'];
            $label = $columns[$key]['label'];
            $label = empty($label) ? $key : $label;

            // Verify required fields
            if (1 == $columns[$key]['required'])
            {
                if ('' == $val || null == $val)
                {
                    die("Error: $label is empty.");
                }
                elseif ('num' == $type && !is_numeric($val))
                {
                    die("Error: $label is not numeric.");
                }
            }
            // Verify unique fields
            if (1 == $columns[$key]['unique'] && !in_array($type, array('pick', 'file')))
            {
                $exclude = '';
                if (!empty($params->tbl_row_id))
                {
                    $exclude = "AND id != $params->tbl_row_id";
                }
                $sql = "SELECT id FROM `@wp_pod_tbl_$params->datatype` WHERE `$key` = '$val' $exclude LIMIT 1";
                pod_query($sql, 'Not unique', "$label needs to be unique.");
            }
            // Verify slug columns
            if ('slug' == $type)
            {
                $slug_val = empty($val) ? $columns['name']['value'] : $val;
                $val = pods_unique_slug($slug_val, $key, $params->datatype, $datatype_id, $params->pod_id);
            }

            // Prepare all table (non-relational) data
            if (!in_array($type, array('pick', 'file')))
            {
                $table_data[] = "`$key` = '$val'";
            }
            // Store relational column data to be looped through later
            else
            {
                $rel_columns[$type][$key] = $val;
            }
        }

        // Create the pod_id if it doesn't exist
        if (empty($params->pod_id))
        {
            $current_time = current_time('mysql');
            $sql = "INSERT INTO @wp_pod (datatype, name, created, modified) VALUES ('$datatype_id', '$params->name', '$current_time', '$current_time')";
            $params->pod_id = pod_query($sql, 'Cannot add new content');
            $params->tbl_row_id = pod_query("INSERT INTO `@wp_pod_tbl_$params->datatype` (name) VALUES (NULL)", 'Cannot add new table row');
        }
        else
        {
            $result = pod_query("SELECT tbl_row_id FROM @wp_pod WHERE id = $params->pod_id LIMIT 1");
            $params->tbl_row_id = mysql_result($result, 0);
        }

        // Save the table row
        if (isset($table_data))
        {
            $table_data = implode(',', $table_data);
            pod_query("UPDATE `@wp_pod_tbl_$params->datatype` SET $table_data WHERE id = $params->tbl_row_id LIMIT 1");
        }

        // Update wp_pod
        $item_name = isset($columns['name']['value']) ? ", name = '" . $columns['name']['value'] . "'" : '';
        pod_query("UPDATE @wp_pod SET tbl_row_id = $params->tbl_row_id, datatype = $datatype_id, modified = '" . current_time('mysql') . "' $item_name WHERE id = $params->pod_id LIMIT 1");

        // Save relational column data
        if (isset($rel_columns))
        {
            // E.g. $rel_columns['pick']['related_events'] = '3,15';
            foreach ($rel_columns as $rel_type => $rel_data)
            {
                foreach ($rel_data as $rel_name => $rel_values)
                {
                    $field_id = $columns[$rel_name]['id'];

                    // Convert values from a comma-separated string into an array
                    $rel_values = empty($rel_values) ? array() : explode(',', $rel_values);

                    // Remove existing relationships
                    pod_query("DELETE FROM @wp_pod_rel WHERE pod_id = $params->pod_id AND field_id = $field_id");

                    // File relationships
                    if ('file' == $rel_type)
                    {
                        $rel_weight = 0;
                        foreach ($rel_values as $related_id)
                        {
                            pod_query("INSERT INTO @wp_pod_rel (pod_id, field_id, tbl_row_id, weight) VALUES ($params->pod_id, $field_id, $related_id, $rel_weight)");
                            $rel_weight++;
                        }
                    }
                    // Pick relationships
                    elseif ('pick' == $rel_type)
                    {
                        $pickval = $columns[$key]['pickval'];
                        $sister_datatype_id = $datatypes[$pickval]['id'];
                        $sister_field_id = $columns[$key]['sister_field_id'];
                        $sister_field_id = empty($sister_field_id) ? 0 : $sister_field_id;
                        $sister_pod_ids = array();

                        // Delete parent and sister rels
                        if (!empty($sister_field_id))
                        {
                            // Get sister pod IDs (a sister pod's sister pod is the parent pod)
                            $result = pod_query("SELECT pod_id FROM @wp_pod_rel WHERE sister_pod_id = $params->pod_id");
                            if (0 < mysql_num_rows($result))
                            {
                                while ($row = mysql_fetch_assoc($result))
                                {
                                    $sister_pod_ids[] = $row['pod_id'];
                                }
                                $sister_pod_ids = implode(',', $sister_pod_ids);
        
                                // Delete the sister pod relationship
                                pod_query("DELETE FROM @wp_pod_rel WHERE pod_id IN ($sister_pod_ids) AND sister_pod_id = $params->pod_id AND field_id = $sister_field_id");
                            }
                        }

                        // Add rel values
                        $rel_weight = 0;
                        foreach ($rel_values as $related_id)
                        {
                            $sister_pod_id = 0;
                            if (!empty($sister_field_id) && !empty($sister_datatype_id))
                            {
                                $result = pod_query("SELECT id FROM @wp_pod WHERE datatype = $sister_datatype_id AND tbl_row_id = $related_id LIMIT 1");
                                if (0 < mysql_num_rows($result))
                                {
                                    $sister_pod_id = mysql_result($result, 0);
                                    pod_query("INSERT INTO @wp_pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id, weight) VALUES ($sister_pod_id, $params->pod_id, $sister_field_id, $params->tbl_row_id, $rel_weight)", 'Cannot add sister relationships');
                                }
                            }
                            pod_query("INSERT INTO @wp_pod_rel (pod_id, sister_pod_id, field_id, tbl_row_id, weight) VALUES ($params->pod_id, $sister_pod_id, $field_id, $related_id, $rel_weight)", 'Cannot add relationships');
                            $rel_weight++;
                        }
                    }
                }
            }
        }

        // Plugin hook
        do_action('pods_post_save_pod_item');

        // Call any post-save helpers
        $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name IN ('$post_save_helpers')");
        while ($row = mysql_fetch_assoc($result))
        {
            eval('?>' . $row['phpcode']);
        }

        // Success! Return the tbl_row_id
        return $params->tbl_row_id;
    }

    /**
     * Drop a content type and all its content
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @todo Only require the ID or dtname (not both!)
     * @param array $params An associative array of parameters
     * @since 1.7.9
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
        pod_query("DROP TABLE `@wp_pod_tbl_$params->name`");
    }

    /**
     * Drop a column within a content type
     *
     * $params['id'] int The column ID
     * $params['dtname'] string The datatype name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
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

    /**
     * Drop a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_template($params)
    {
        $params = (object) $params;
        $where = empty($params->id) ? "name = '$params->name'" : "id = $params->id";
        pod_query("DELETE FROM @wp_pod_templates WHERE $where LIMIT 1");
    }

    /**
     * Drop a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_page($params)
    {
        $params = (object) $params;
        $where = empty($params->id) ? "uri = '$params->uri'" : "id = $params->id";
        pod_query("DELETE FROM @wp_pod_pages WHERE $where LIMIT 1");
    }

    /**
     * Drop a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_helper($params)
    {
        $params = (object) $params;
        $where = empty($params->id) ? "name = '$params->name'" : "id = $params->id";
        pod_query("DELETE FROM @wp_pod_helpers WHERE $where LIMIT 1");
    }

    /**
     * Drop a menu item and all its children
     *
     * $params['id'] int The menu ID
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
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

    /**
     * Drop a single pod item
     *
     * $params['pod_id'] int The item's ID from the wp_pod table
     *
     * @todo Use the tbl_row_id (id) instead of the pod_id
     * @param array $params An associative array of parameters
     * @since 1.7.9
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

    /**
     * Load a content type and all of its fields
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_pod($params)
    {
        $params = (object) $params;
        $where = empty($params->id) ? "name = '$params->name'" : "id = $params->id";
        $result = pod_query("SELECT * FROM @wp_pod_types WHERE $where LIMIT 1");
        $module = mysql_fetch_assoc($result);

        $sql = "
            SELECT
                id, name, coltype, pickval, required, weight
            FROM
                @wp_pod_fields
            WHERE
                datatype = {$module['id']}
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

    /**
     * Load a column
     *
     * $params['id'] int The field ID
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_column($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE id = $params->id LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /**
     * Load a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_template($params)
    {
        $params = (object) $params;
        $where = empty($params->id) ? "name = '$params->name'" : "id = $params->id";
        $result = pod_query("SELECT * FROM @wp_pod_templates WHERE $where LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /**
     * Load a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_page($params)
    {
        $params = (object) $params;
        $where = empty($params->id) ? "uri = '$params->uri'" : "id = $params->id";
        $result = pod_query("SELECT * FROM @wp_pod_pages WHERE $where LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /**
     * Load a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_helper($params)
    {
        $params = (object) $params;
        $where = empty($params->id) ? "name = '$params->name'" : "id = $params->id";
        $result = pod_query("SELECT * FROM @wp_pod_helpers WHERE $where LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /**
     * Load a single menu item
     *
     * $params['id'] int The menu ID
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_menu_item($params)
    {
        $params = (object) $params;
        $result = pod_query("SELECT * FROM @wp_pod_menu WHERE id = $params->id LIMIT 1");
        return mysql_fetch_assoc($result);
    }

    /**
     * Load the input form for a pod item
     *
     * $params['datatype'] string The datatype name
     * $params['pod_id'] int The item's pod ID
     * $params['public_columns'] array An associative array of columns
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_pod_item($params)
    {
        $params = (object) $params;
        $obj = new Pod($params->datatype);
        return $obj->showform((int) $params->pod_id, $params->public_columns);
    }

    /**
     * Load a bi-directional (sister) column
     *
     * $params['pickval'] string The related PICK pod name
     * $params['datatype'] int The datatype ID
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
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
                        return $sister_fields;
                    }
                }
            }
        }
    }

    /**
     * Import data
     *
     * @param mixed $data PHP associative array or CSV input
     * @param bool $numeric_mode Use IDs instead of the name field when matching
     * @since 1.7.1
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

                if (null != $field_value && false !== $field_value)
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
            $current_time = current_time('mysql');
            $pod_name = mysql_real_escape_string(trim($data_row['name']));
            $pod_id = pod_query("INSERT INTO @wp_pod (tbl_row_id, datatype, name, created, modified) VALUES ('$tbl_row_id', '{$this->dt}', '$pod_name', '$current_time', '$current_time')");
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

    /**
     * Export data
     * 
     * @since 1.7.1
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
        return $data;
    }

    /**
     * Convert CSV to a PHP array
     *
     * @param string $data The CSV input
     * @since 1.7.1
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
            $fields = preg_replace("/^\"(.*)\"$/s", "$1", $fields);
            foreach ($field_names as $key => $field)
            {
                $tmp[$field] = $fields[$key];
            }
            $out[] = $tmp;
        }
        return $out;
    }
}
