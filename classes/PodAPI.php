<?php
class PodAPI
{
    var $snap = false;
    var $dt;
    var $dtname;
    var $format;
    var $fields;
    var $use_pod_id = false; // set to true for save_pod_item to operate off of pod_id (for backwards compatibility with functions using pod_ids)

    /**
     * Store and retrieve data programatically
     *
     * @param string $dtname (optional) The pod name
     * @param string $format (optional) Format for import/export, "php" or "csv"
     * @since 1.7.1
     */
    function __construct($dtname = null, $format = 'php') {
        $this->dtname = pods_sanitize(trim($dtname));
        $this->format = pods_sanitize(trim($format));

        if (!empty($this->dtname)) {
            $pod = $this->load_pod(array('name' => $this->dtname));
            if (is_array($pod)) {
                $this->dt = $pod['id'];
                $this->fields = $pod['fields'];
            }
        }
    }

    /**
     * Throw an error or die (cake or death?)
     *
     * @param string $error Error message
     * @since 1.9.0
     */
    function oh_snap($error) {
        if (false!==$this->snap) {
            throw new Exception($error);
            return false;
        }
        die($error);
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
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_pod($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Set defaults
        $params = (object) array_merge(array('id' => '',
                                             'name' => '',
                                             'label' => '',
                                             'is_toplevel' => '',
                                             'detail_page' => '',
                                             'pre_save_helpers' => '',
                                             'pre_drop_helpers' => '',
                                             'post_save_helpers' => '',
                                             'post_drop_helpers' => '',
                                             'order' => ''),
                                       (array) $params);
        if (isset($params->action))
            unset($params->action);
        if (isset($params->_wpnonce))
            unset($params->_wpnonce);

        // Add new pod
        if (empty($params->id)) {
            $params->name = pods_clean_name($params->name);
            if (empty($params->name)) {
                return $this->oh_snap('<e>Enter a pod name');
            }
            $sql = "SELECT id FROM @wp_pod_types WHERE name = '{$params->name}' LIMIT 1";
            pod_query($sql, 'Duplicate pod name', 'Pod name already exists');

            $set = array();
            $columns = array();
            foreach ($params as $column => $value) {
                if (in_array($column, array('id', 'order', 'return_pod')))
                    continue;
                $columns[] = "`{$column}`";
                $set[] = "'{$value}'";
            }
            $columns = implode(', ', $columns);
            $set = implode(', ', $set);
            $pod_id = pod_query("INSERT INTO @wp_pod_types ({$columns}) VALUES ({$set})", 'Cannot add new pod');
            pod_query("CREATE TABLE `@wp_pod_tbl_{$params->name}` (id int unsigned auto_increment primary key, name varchar(128), slug varchar(128)) DEFAULT CHARSET utf8", 'Cannot add pod database table');
            pod_query("INSERT INTO @wp_pod_fields (datatype, name, label, comment, coltype, required, weight) VALUES ({$pod_id}, 'name', 'Name', '', 'txt', 1, 0),({$pod_id}, 'slug', 'Permalink', 'Leave blank to auto-generate', 'slug', 0, 1)");
            if (!isset($params->return_pod) || false === $params->return_pod)
                return $pod_id;
        }
        // Edit existing pod
        else {
            $pod_id = $params->id;
            $set = array();
            foreach ($params as $column => $value) {
                if (in_array($column, array('id', 'name', 'order', 'return_pod')))
                    continue;
                $set[] = "`{$column}` = '{$value}'";
            }
            if (!empty($set)) {
                $set = implode(', ', $set);
                $sql = "
                UPDATE
                    `@wp_pod_types`
                SET
                    {$set}
                WHERE
                    `id` = {$pod_id}
                LIMIT
                    1
                ";
                pod_query($sql, 'Cannot change Pod settings');
            }

            $weight = 0;
            $order = (false !== strpos($params->order, ',')) ? explode(',', $params->order) : array($params->order);
            foreach ($order as $field_id) {
                pod_query("UPDATE `@wp_pod_fields` SET `weight` = '{$weight}' WHERE `id` = '{$field_id}' LIMIT 1", 'Cannot change column order');
                $weight++;
            }
            if (!isset($params->return_pod) || false === $params->return_pod)
                return $pod_id;
        }
        return $this->load_pod(array('id' => $pod_id));
    }

    /**
     * Add or edit a column within a content type
     *
     * $params['id'] int The field ID
     * $params['name'] string The field name
     * $params['datatype'] int The datatype ID
     * $params['dtname'] string The datatype name
     * $params['coltype'] string The column type ("txt", "desc", "pick", etc)
     * $params['sister_field_id'] int (optional) The related field ID
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
    function save_column($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Set defaults
        $params = (object) array_merge(array('id' => '',
                                             'name' => '',
                                             'datatype' => '',
                                             'dtname' => '',
                                             'coltype' => 'txt',
                                             'sister_field_id' => 0,
                                             'pickval' => '',
                                             'label' => '',
                                             'comment' => '',
                                             'display_helper' => '',
                                             'input_helper' => '',
                                             'pick_filter' => '',
                                             'pick_orderby' => '',
                                             'required' => 0,
                                             'unique' => 0,
                                             'multiple' => 0),
                                       (array) $params);

        // Force Types
        $params->id = absint($params->id);
        $params->sister_field_id = absint($params->sister_field_id);
        $params->required = absint($params->required);
        $params->unique = absint($params->unique);
        $params->multiple = absint($params->multiple);

        $dbtypes = array(
            'bool' => 'bool default 0',
            'date' => 'datetime',
            'num' => 'decimal(12,2)',
            'txt' => 'varchar(128)',
            'slug' => 'varchar(128)',
            'code' => 'longtext',
            'desc' => 'longtext'
        );
        $dbtypes = apply_filters('pods_column_dbtypes', $dbtypes);

        // Add new column
        if (empty($params->id)) {
            $params->name = pods_clean_name($params->name);
            if (empty($params->name)) {
                return $this->oh_snap('<e>Enter a column name');
            }
            elseif (in_array($params->name, array('id', 'name', 'type', 'created', 'modified', 'p', 't'))) {
                return $this->oh_snap("<e>$params->name is a reserved name");
            }
            $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $params->datatype AND name = '$params->name' LIMIT 1";
            pod_query($sql, 'Cannot get fields', 'Column by this name already exists');

            if ('slug' == $params->coltype) {
                $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $params->datatype AND coltype = 'slug' LIMIT 1";
                pod_query($sql, 'Too many permalinks', 'This pod already has a permalink column');
            }

            // Sink the new column to the bottom of the list
            $weight = 0;
            $result = pod_query("SELECT weight FROM @wp_pod_fields WHERE datatype = $params->datatype ORDER BY weight DESC LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $row = mysql_fetch_assoc($result);
                $weight = (int) $row['weight'] + 1;
            }

            if ('pick' != $params->coltype) {
                $params->pickval = '';
                $params->pick_filter = '';
                $params->pick_orderby = '';
                $params->sister_field_id = 0;
                $params->multiple = 0;
            }

            $field_id = pod_query("INSERT INTO @wp_pod_fields (datatype, name, label, comment, display_helper, input_helper, coltype, pickval, pick_filter, pick_orderby, sister_field_id, required, `unique`, `multiple`, weight) VALUES ('$params->datatype', '$params->name', '$params->label', '$params->comment', '$params->display_helper', '$params->input_helper', '$params->coltype', '$params->pickval', '$params->pick_filter', '$params->pick_orderby', '$params->sister_field_id', '$params->required', '$params->unique', '$params->multiple', '$weight')", 'Cannot add new field');

            if ('pick' != $params->coltype && 'file' != $params->coltype) {
                $dbtype = $dbtypes[$params->coltype];
                pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` ADD COLUMN `$params->name` $dbtype", 'Cannot create new column');
            }
            else {
                pod_query("UPDATE @wp_pod_fields SET sister_field_id = '{$field_id}' WHERE id = '{$params->sister_field_id}' LIMIT 1", 'Cannot update sister field');
            }
        }
        // Edit existing column
        else {
            if ('id' == $params->name) {
                return $this->oh_snap("<e>$params->name is not editable.");
            }

            $sql = "SELECT id FROM @wp_pod_fields WHERE datatype = $params->datatype AND id != $params->id AND name = '$params->name' LIMIT 1";
            pod_query($sql, 'Column already exists', "$params->name already exists.");

            $sql = "SELECT name, coltype FROM @wp_pod_fields WHERE id = $params->id LIMIT 1";
            $result = pod_query($sql);

            if (0 < mysql_num_rows($result)) {
                $row = mysql_fetch_assoc($result);
                $old_coltype = $row['coltype'];
                $old_name = $row['name'];

                $dbtype = $dbtypes[$params->coltype];
                $pickval = ('pick' != $params->coltype || empty($params->pickval)) ? '' : "$params->pickval";

                if ($params->coltype != $old_coltype) {
                    if ('pick' == $params->coltype || 'file' == $params->coltype) {
                        if ('pick' != $old_coltype && 'file' != $old_coltype) {
                            pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` DROP COLUMN `$old_name`");
                        }
                    }
                    elseif ('pick' == $old_coltype || 'file' == $old_coltype) {
                        pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` ADD COLUMN `$params->name` $dbtype", 'Cannot create column');
                        pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id = $params->id");
                        pod_query("DELETE FROM @wp_pod_rel WHERE field_id = $params->id");
                    }
                    else {
                        pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` CHANGE `$old_name` `$params->name` $dbtype");
                    }
                }
                elseif ($params->name != $old_name && 'pick' != $params->coltype && 'file' != $params->coltype) {
                    pod_query("ALTER TABLE `@wp_pod_tbl_$params->dtname` CHANGE `$old_name` `$params->name` $dbtype");
                }

                if ('pick' != $params->coltype) {
                    $params->pickval = '';
                    $params->pick_filter = '';
                    $params->pick_orderby = '';
                    $params->sister_field_id = 0;
                    $params->multiple = 0;
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
    function save_template($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Set defaults
        $params = (object) array_merge(array('id' => '',
                                             'name' => '',
                                             'code' => ''),
                                       (array) $params);

        // Force Types
        $params->id = absint($params->id);

        // Add new template
        if (empty($params->id)) {
            if (empty($params->name)) {
                return $this->oh_snap('<e>Enter a template name');
            }

            $sql = "SELECT id FROM @wp_pod_templates WHERE name = '$params->name' LIMIT 1";
            pod_query($sql, 'Cannot get Templates', 'Template by this name already exists');
            $template_id = pod_query("INSERT INTO @wp_pod_templates (name, code) VALUES ('$params->name', '$params->code')", 'Cannot add new template');

            return $template_id; // return
        }
        // Edit existing template
        else {
            $maybename = '';
            if (!empty($params->name))
                $maybename = "name = '$params->name',";
            pod_query("UPDATE @wp_pod_templates SET $maybename code = '$params->code' WHERE id = $params->id LIMIT 1");
        }
    }

    /**
     * Add or edit a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     * $params['title'] string The page title
     * $params['page_template'] string The page template
     * $params['phpcode'] string The page code
     * $params['precode'] string The page pre code
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_page($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Set defaults
        $params = (object) array_merge(array('id' => '',
                                             'uri' => '',
                                             'page_title' => '',
                                             'page_template' => '',
                                             'phpcode' => '',
                                             'precode' => ''),
                                       (array) $params);

        // Force Types
        $params->id = absint($params->id);

        // Add new page
        if (empty($params->id)) {
            if (empty($params->uri)) {
                return $this->oh_snap('<e>Enter a page URI');
            }
            // normalize URI (remove outside /
            $params->uri = trim($params->uri,'/');
            $sql = "SELECT id FROM @wp_pod_pages WHERE uri = '$params->uri' LIMIT 1";
            pod_query($sql, 'Cannot get Pod Pages', 'Page by this URI already exists');
            $page_id = pod_query("INSERT INTO @wp_pod_pages (uri, title, page_template, phpcode, precode) VALUES ('$params->uri', '$params->page_title', '$params->page_template', '$params->phpcode', '$params->precode')", 'Cannot add new page');
            return $page_id; // return
        }
        // Edit existing page
        else {
            $maybename = '';
            if (!empty($params->uri))
                $maybename = "uri = '$params->uri',";
            pod_query("UPDATE @wp_pod_pages SET $maybename title = '$params->page_title', page_template = '$params->page_template', phpcode = '$params->phpcode', precode = '$params->precode' WHERE id = $params->id LIMIT 1");
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
    function save_helper($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Set defaults
        $params = (object) array_merge(array('id' => '',
                                             'name' => '',
                                             //'helper_type' => 'display',
                                             'phpcode' => ''),
                                       (array) $params);

        // Force Types
        $params->id = absint($params->id);

        // Add new helper
        if (empty($params->id)) {
            if (empty($params->name)) {
                return $this->oh_snap('<e>Enter a helper name');
            }
            if (!isset($params->helper_type) || empty($params->helper_type))
                $params->helper_type = 'display';

            $sql = "SELECT id FROM @wp_pod_helpers WHERE name = '$params->name' LIMIT 1";
            pod_query($sql, 'Cannot get helpers', 'helper by this name already exists');
            $helper_id = pod_query("INSERT INTO @wp_pod_helpers (name, helper_type, phpcode) VALUES ('$params->name', '$params->helper_type', '$params->phpcode')", 'Cannot add new helper');
            return $helper_id; // return
        }
        // Edit existing helper
        else {
            $maybename = '';
            if (isset($params->name) && !empty($params->name))
                $maybename = "name = '$params->name',";
            $maybetype = '';
            if (isset($params->helper_type) && !empty($params->helper_type))
                $maybetype = "helper_type = '$params->helper_type',";
            pod_query("UPDATE @wp_pod_helpers SET {$maybename} {$maybetype} phpcode = '$params->phpcode' WHERE id = $params->id LIMIT 1");
        }
    }

    /**
     * Save the entire role structure
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_roles($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $roles = array();
        foreach ($params as $key => $val) {
            if ('action' != $key) {
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
    function get_table_data($params) {
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
        if (0 < mysql_num_rows($result)) {
            while ($row = mysql_fetch_assoc($result)) {
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
     * $params['data'] array (optional) Associative array of a set of associative arrays of column names + values (for bulk operations)
     * $params['pod_id'] int The item's ID from the wp_pod table (or alternatively use the tbl_row_id parameter instead)
     * $params['tbl_row_id'] int The item's ID from the wp_pod_tbl_* table (or alternatively use the pod_id parameter instead)
     *
     * @param array $params An associative array of parameters
     * @return int The table row ID
     * @since 1.7.9
     */
    function save_pod_item($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) str_replace('@wp_', '{prefix}', $params);

        // Support for multiple save_pod_item operations at the same time
        if (isset($params->data) && !empty($params->data) && is_array($params->data)) {
            $ids = array();
            $new_params = $params;
            unset($new_params->data);
            foreach ($params->data as $columns){
                $new_params->columns = $columns;
                $ids[] = $this->save_pod_item($new_params);
            }
            return $ids;
        }

        // Support for bulk edit
        if (isset($params->tbl_row_id) && !empty($params->tbl_row_id) && is_array($params->tbl_row_id)) {
            $ids = array();
            $new_params = $params;
            foreach ($params->tbl_row_id as $tbl_row_id){
                $new_params->tbl_row_id = $tbl_row_id;
                $ids[] = $this->save_pod_item($new_params);
            }
            return $ids;
        }

        // Allow Helpers to bypass subsequent helpers in recursive save_pod_item calls
        $bypass_helpers = false;
        if (isset($params->bypass_helpers) && true === (boolean) $params->bypass_helpers) {
        	$bypass_helpers = true;
        }

        // Get array of datatypes
        $datatypes = $this->get_table_data(array('array_key' => 'name', 'columns' => 'id, name'));
        $params->datatype_id = (int) $datatypes[$params->datatype]['id'];

        // Get the datatype fields
        $opts = array('table' => 'fields', 'where' => "datatype = {$params->datatype_id}", 'orderby' => 'weight', 'array_key' => 'name');
        $columns = $this->get_table_data($opts);

        // Find the active columns (loop through $params->columns to retain order)
        if (!empty($params->columns) && is_array($params->columns)) {
            foreach ($params->columns as $column_name => $column_val) {
                // Support for Pre Key/Value Parameters in previous Pods versions
                if (isset($params->name)&&isset($params->$column_val)) {
                    $column_name = $column_val;
                    $column_val = $params->$column_name;
                }
                if (isset($columns[$column_name])) {
                    $columns[$column_name]['value'] = $column_val;
                    $active_columns[] = $column_name;
                }
            }
            unset($params->columns);
        }

        // Load all helpers
        $result = pod_query("SELECT pre_save_helpers, post_save_helpers FROM @wp_pod_types WHERE id = {$params->datatype_id}");
        $row = mysql_fetch_assoc($result);
        $params->pre_save_helpers = explode(',', $row['pre_save_helpers']);
        $params->post_save_helpers = explode(',', $row['post_save_helpers']);

        // Allow Helpers to know what's going on, are we adding or saving?
        $is_new_item = false;
        if (!empty($params->tbl_row_id)) {
            $result = pod_query("SELECT p.id FROM @wp_pod p INNER JOIN @wp_pod_types t ON t.id = p.datatype WHERE p.tbl_row_id = $params->tbl_row_id AND t.name = '$params->datatype' LIMIT 1",'Pod item not found',null,'Pod item not found');
            $params->pod_id = mysql_result($result, 0);
        }
        elseif (!empty($params->pod_id)) {
            $result = pod_query("SELECT tbl_row_id FROM @wp_pod WHERE id = $params->pod_id LIMIT 1",'Item not found',null,'Item not found');
            $params->tbl_row_id = mysql_result($result, 0);
        }
        else
            $is_new_item = true;

        // Plugin hook
        do_action('pods_pre_save_pod_item', $params, $columns);

        // Call any pre-save helpers (if not bypassed)
        if(!$bypass_helpers && !empty($params->pre_save_helpers)) {
            foreach ($params->pre_save_helpers as $helper) {
                $function_or_file = $helper;
                $check_function = $function_or_file;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FUNCTIONS') || !PODS_HELPER_FUNCTIONS))
                    $check_function = false;
                $check_file = null;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FILES') || !PODS_HELPER_FILES))
                    $check_file = false;
                if (false !== $check_function && false !== $check_file)
                    $function_or_file = pods_function_or_file($function_or_file, $check_function, 'helper', $check_file);
                else
                    $function_or_file = false;

                $content = false;
                if (!$function_or_file) {
                    $api = new PodAPI();
                    $params_helper = array('name' => $helper, 'type' => 'pre_save');
                    if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                        $params_helper = pods_sanitize($params_helper);
                    $content = $api->load_helper($params_helper);
                    if (false !== $content && 0 < strlen(trim($content['phpcode'])))
                        $content = $content['phpcode'];
                    else
                        $content = false;
                }

                if (false === $content && false !== $function_or_file && isset($function_or_file['function']))
                    $function_or_file['function']($params, $columns, $this);
                elseif (false === $content && false !== $function_or_file && isset($function_or_file['file']))
                    locate_template($function_or_file['file'], true, true);
                elseif (false !== $content) {
                    if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                        eval("?>$content");
                    else
                        echo $content;
                }
            }
        }

        // Loop through each active column, validating and preparing the table data
        foreach ($active_columns as $key) {
            $val = $columns[$key]['value'];
            $type = $columns[$key]['coltype'];
            $label = $columns[$key]['label'];
            $label = empty($label) ? $key : $label;

            // Verify required fields
            if (1 == $columns[$key]['required']) {
                if ('' == $val || null == $val) {
                    return $this->oh_snap("<e>$label is empty.");
                }
                elseif ('num' == $type && !is_numeric($val)) {
                    return $this->oh_snap("<e>$label is not numeric.");
                }
            }
            // Verify unique fields
            if (1 == $columns[$key]['unique'] && !in_array($type, array('pick', 'file'))) {
                $exclude = '';
                if (!empty($params->pod_id)) {
                    $result = pod_query("SELECT tbl_row_id FROM @wp_pod WHERE id = '$params->pod_id' AND datatype = '{$params->datatype_id}' LIMIT 1");
                    if (0 < mysql_num_rows($result)) {
                        $exclude = 'AND id != ' . mysql_result($result, 0);
                    }
                }

                // Trigger an error if not unique
                $sql = "SELECT id FROM `@wp_pod_tbl_$params->datatype` WHERE `$key` = '$val' $exclude LIMIT 1";
                pod_query($sql, 'Not unique', "$label needs to be unique.");
            }
            // Verify slug columns
            if ('slug' == $type) {
                $slug_val = empty($val) ? $columns['name']['value'] : $val;
                $val = pods_unique_slug($slug_val, $key, $params);
            }

            // Prepare all table (non-relational) data
            if (!in_array($type, array('pick', 'file'))) {
                if ('num' == $type)
                    $val = floatval($val);
                elseif ('bool' == $type)
                    $val = min(absint($val), 1);
                if ('num' != $type)
                    $val = "'$val'";
                $table_data[] = "`$key` = $val";
            }
            // Store relational column data to be looped through later
            else {
                $rel_columns[$type][$key] = $val;
            }
        }

        // Create the pod_id if it doesn't exist
        if (empty($params->pod_id)&&empty($params->tbl_row_id)) {
            $current_time = current_time('mysql');
            $user = 0;
            if (is_user_logged_in()) {
                global $user_ID;
                get_currentuserinfo();
                $user = $user_ID;
            }
            $name = $params->datatype;
            if (isset($params->name))
                $name = $params->name;
            if (isset($params->columns) && is_array($params->columns) && isset($params->columns['name']))
                $name = $params->columns['name'];
            $sql = "INSERT INTO @wp_pod (datatype, name, created, modified, author_id) VALUES ('{$params->datatype_id}', '$name', '$current_time', '$current_time', '$user')";
            $params->pod_id = pod_query($sql, 'Cannot add new content');
            $params->tbl_row_id = pod_query("INSERT INTO `@wp_pod_tbl_$params->datatype` (name) VALUES (NULL)", 'Cannot add new table row');
        }

        // Save the table row
        if (isset($table_data)) {
            $table_data = implode(',', $table_data);
            pod_query("UPDATE `@wp_pod_tbl_$params->datatype` SET $table_data WHERE id = $params->tbl_row_id LIMIT 1");
        }

        // Update wp_pod
        $item_name = isset($columns['name']['value']) ? ", name = '" . $columns['name']['value'] . "'" : '';
        pod_query("UPDATE @wp_pod SET tbl_row_id = $params->tbl_row_id, datatype = $params->datatype_id, modified = '" . current_time('mysql') . "' $item_name WHERE id = $params->pod_id LIMIT 1");

        // Save relational column data
        if (isset($rel_columns)) {
            // E.g. $rel_columns['pick']['related_events'] = '3,15';
            foreach ($rel_columns as $rel_type => $rel_data) {
                foreach ($rel_data as $rel_name => $rel_values) {
                    $field_id = $columns[$rel_name]['id'];

                    // Convert values from a comma-separated string into an array
                    if (empty($rel_values))
                        $rel_values = array();
                    elseif (!is_array($rel_values))
                        $rel_values = explode(',', $rel_values);

                    // Remove existing relationships
                    pod_query("DELETE FROM @wp_pod_rel WHERE pod_id = $params->pod_id AND field_id = $field_id");

                    // File relationships
                    if ('file' == $rel_type) {
                        $rel_weight = 0;
                        foreach ($rel_values as $related_id) {
                            $related_id = absint($related_id);
                            if (empty($related_id))
                                continue;
                            pod_query("INSERT INTO @wp_pod_rel (pod_id, field_id, tbl_row_id, weight) VALUES ($params->pod_id, $field_id, $related_id, $rel_weight)");
                            $rel_weight++;
                        }
                    }
                    // Pick relationships
                    elseif ('pick' == $rel_type) {
                        $pickval = $columns[$rel_name]['pickval'];
                        $sister_datatype_id = 0;
                        $sister_field_id = 0;
                        if (!in_array($pickval, array('wp_taxonomy', 'wp_post', 'wp_page', 'wp_user')) && isset($datatypes[$pickval])) {
                            $sister_datatype_id = $datatypes[$pickval]['id'];
                            if (!empty($columns[$rel_name]['sister_field_id']))
                                $sister_field_id = $columns[$rel_name]['sister_field_id'];
                        }
                        $sister_pod_ids = array();

                        // Delete parent and sister rels
                        if (!empty($sister_field_id)) {
                            // Get sister pod IDs (a sister pod's sister pod is the parent pod)
                            $result = pod_query("SELECT pod_id FROM @wp_pod_rel WHERE sister_pod_id = $params->pod_id");
                            if (0 < mysql_num_rows($result)) {
                                while ($row = mysql_fetch_assoc($result)) {
                                    $sister_pod_ids[] = $row['pod_id'];
                                }
                                $sister_pod_ids = implode(',', $sister_pod_ids);

                                // Delete the sister pod relationship
                                pod_query("DELETE FROM @wp_pod_rel WHERE pod_id IN ($sister_pod_ids) AND sister_pod_id = $params->pod_id AND field_id = $sister_field_id");
                            }
                        }

                        // Add rel values
                        $rel_weight = 0;
                        foreach ($rel_values as $related_id) {
                            $related_id = absint($related_id);
                            if (empty($related_id))
                                continue;
                            $sister_pod_id = 0;
                            if (!empty($sister_field_id) && !empty($sister_datatype_id)) {
                                $result = pod_query("SELECT id FROM @wp_pod WHERE datatype = $sister_datatype_id AND tbl_row_id = $related_id LIMIT 1");
                                if (0 < mysql_num_rows($result)) {
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
        do_action('pods_post_save_pod_item', $params, $columns);

        // Call any post-save helpers (if not bypassed)
        if(!$bypass_helpers && !empty($params->post_save_helpers)) {
            foreach ($params->post_save_helpers as $helper) {
                $function_or_file = $helper;
                $check_function = $function_or_file;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FUNCTIONS') || !PODS_HELPER_FUNCTIONS))
                    $check_function = false;
                $check_file = null;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FILES') || !PODS_HELPER_FILES))
                    $check_file = false;
                if (false !== $check_function && false !== $check_file)
                    $function_or_file = pods_function_or_file($function_or_file, $check_function, 'helper', $check_file);
                else
                    $function_or_file = false;

                $content = false;
                if (!$function_or_file) {
                    $api = new PodAPI();
                    $params_helper = array('name' => $helper, 'type' => 'post_save');
                    if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                        $params_helper = pods_sanitize($params_helper);
                    $content = $api->load_helper($params_helper);
                    if (false !== $content && 0 < strlen(trim($content['phpcode'])))
                        $content = $content['phpcode'];
                    else
                        $content = false;
                }

                if (false === $content && false !== $function_or_file && isset($function_or_file['function']))
                    $function_or_file['function']($params, $columns, $this);
                elseif (false === $content && false !== $function_or_file && isset($function_or_file['file']))
                    locate_template($function_or_file['file'], true, true);
                elseif (false !== $content) {
                    if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                        eval("?>$content");
                    else
                        echo $content;
                }
            }
        }

        // Success! Return the id
        if (false===$this->use_pod_id) {
            return $params->tbl_row_id;
        }
        return $params->pod_id;
    }

    /**
     * Duplicate a pod item
     *
     * $params['datatype'] string The datatype name
     * $params['tbl_row_id'] int The item's ID from the wp_pod_tbl_* table
     *
     * @param array $params An associative array of parameters
     * @return int The table row ID
     * @since 1.12
     */
    function duplicate_pod_item($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        $id = false;
        $columns = $this->fields;
        if (empty($columns) || $this->dtname != $params->datatype) {
            $pod = $this->load_pod(array('name' => $params->datatype));
            $columns = $pod['fields'];
            if (null === $this->dtname) {
                $this->dtname = $pod['name'];
                $this->dt = $pod['id'];
                $this->fields = $pod['fields'];
            }
        }
        $pod = new Pod($params->datatype, $params->tbl_row_id);
        if (!empty($pod->data)) {
            $params = array('datatype' => $params->datatype,
                            'columns' => array());
            foreach ($columns as $column) {
                $field = $column['name'];
                if ('pick' == $column['coltype']) {
                    $field = $column . '.id';
                    if ('wp_taxonomy' == $column['pickval'])
                        $field = $column . '.term_id';
                }
                if ('file' == $column['coltype'])
                    $field = $column . '.ID';
                $value = $pod->get_field($field);
                if (0 < strlen($value))
                    $params['columns'][$column['name']] = $value;
            }
            $params = apply_filters('duplicate_pod_item', $params, $pod->datatype, $pod->get_field('id'));
            $id = $this->save_pod_item(pods_sanitize($params));
        }
        return $id;
    }

    /**
     * Export a pod item
     *
     * $params['datatype'] string The datatype name
     * $params['tbl_row_id'] int The item's ID from the wp_pod_tbl_* table
     *
     * @param array $params An associative array of parameters
     * @return int The table row ID
     * @since 1.12
     */
    function export_pod_item($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        $data = false;
        $columns = $this->fields;
        if (empty($columns) || $this->dtname != $params->datatype) {
            $pod = $this->load_pod(array('name' => $params->datatype));
            $columns = $pod['fields'];
            if (null === $this->dtname) {
                $this->dtname = $pod['name'];
                $this->dt = $pod['id'];
                $this->fields = $pod['fields'];
            }
        }
        $pod = new Pod($params->datatype, $params->tbl_row_id);
        if (!empty($pod->data)) {
            $data = array();
            foreach ($columns as $column) {
                $value = $pod->get_field($column['name']);
                if (0 < strlen($value))
                    $data[$column['name']] = $value;
            }
            $data = apply_filters('export_pod_item', $data, $pod->datatype, $pod->get_field('id'));
        }
        return $data;
    }

    /**
     * Reorder a Pod
     *
     * $params['datatype'] string The datatype name
     * $params['field'] string The column name of the field to reorder
     * $params['order'] array The key=>value array of items to reorder (key should be an integer)
     *
     * @param array $params An associative array of parameters
     * @since 1.9.0
     */
    function reorder_pod_item($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        if (!is_array($params->order)) {
            $params->order = explode(',', $params->order);
        }
        foreach ($params->order as $order => $id) {
            pod_query("UPDATE @wp_pod_tbl_{$params->datatype} SET `{$params->field}`={$order} WHERE id={$id}");
        }
    }

    /**
     * Delete all content for a content type
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @param array $params An associative array of parameters
     * @since 1.9.0
     */
    function reset_pod($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        $pod = $this->load_pod($params);
        if (false === $pod)
            return false;

        $params->id = $pod['id'];
        $params->name = $pod['name'];

        $fields = array();
        foreach ($pod['fields'] as $field) {
            $fields[] = $field['id'];
        }
        $fields = implode(',',$fields);
        if (!empty($fields))
            pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($fields)");

        $sql = "DELETE FROM p, r
        USING @wp_pod_types AS t
        LEFT JOIN @wp_pod AS p ON p.datatype = t.id
        LEFT JOIN @wp_pod_fields AS f ON f.datatype = t.id
        LEFT JOIN @wp_pod_rel AS r ON r.field_id = f.id
        WHERE t.name = '$params->name'";

        pod_query($sql);

        $sql = "DELETE FROM r
        USING @wp_pod_fields AS f
        INNER JOIN @wp_pod_rel AS r ON r.field_id = f.id
        WHERE f.pickval = '$params->name'";

        pod_query($sql);
        pod_query("TRUNCATE `@wp_pod_tbl_$params->name`");
    }

    /**
     * Drop a content type and all its content
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_pod($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        $pod = $this->load_pod($params);
        if (false === $pod)
            return false;

        $params->id = $pod['id'];
        $params->name = $pod['name'];

        pod_query("DELETE FROM @wp_pod_types WHERE id = $params->id LIMIT 1");

        $fields = array();
        foreach ($pod['fields'] as $field) {
            $fields[] = $field['id'];
        }
        $fields = implode(',',$fields);
        if (!empty($fields))
            pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($fields)");

        $sql = "DELETE FROM @wp_pod,@wp_pod_rel
        USING @wp_pod_fields
        INNER JOIN @wp_pod_rel ON @wp_pod_rel.field_id = @wp_pod_fields.id
        INNER JOIN @wp_pod ON @wp_pod.datatype = @wp_pod_fields.datatype
        WHERE @wp_pod_fields.datatype = $params->id";

        pod_query($sql);
        pod_query("DELETE FROM @wp_pod_fields WHERE datatype = $params->id");
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
    function drop_column($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        $result = pod_query("SELECT name, coltype FROM @wp_pod_fields WHERE id = $params->id LIMIT 1");
        list($field_name, $coltype) = mysql_fetch_array($result);

        if ('pick' == $coltype) {
            // Remove any orphans
            $result = pod_query("SELECT id FROM @wp_pod_fields WHERE sister_field_id = $params->id");
            if (0 < mysql_num_rows($result)) {
                while ($row = mysql_fetch_assoc($result)) {
                    $related_fields[] = $row['id'];
                }
                $related_fields = implode(',', $related_fields);
                pod_query("DELETE FROM @wp_pod_rel WHERE field_id IN ($related_fields)");
                pod_query("UPDATE @wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($related_fields)");
            }
        }
        elseif ('file' != $coltype) {
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
    function drop_template($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
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
    function drop_page($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
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
    function drop_helper($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        $where = empty($params->id) ? "name = '$params->name'" : "id = $params->id";
        pod_query("DELETE FROM @wp_pod_helpers WHERE $where LIMIT 1");
    }

    /**
     * Drop a single pod item
     *
     * $params['pod_id'] int The item's ID from the wp_pod table
     * $params['tbl_row_id'] int (optional) The item's ID from the wp_pod_tbl_* table (used with datatype parameter)
     * $params['datatype'] string (optional) The datatype name (used with tbl_row_id parameter)
     * $params['datatype_id'] int (optional) The datatype ID (used with tbl_row_id parameter)
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_pod_item($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        if (isset($params->tbl_row_id)) {
            if (!empty($params->tbl_row_id) && is_array($params->tbl_row_id)) {
                $new_params = $params;
                foreach ($params->tbl_row_id as $tbl_row_id) {
                    $new_params->tbl_row_id = $tbl_row_id;
                    $this->drop_pod_item($new_params);
                }
                return;
            }
            if (isset($params->datatype_id)) {
                $select_dt = "p.datatype = '$params->datatype_id'";
            }
            else {
                $select_dt = "t.name = '$params->datatype'";
            }
            $sql = "
            SELECT
                p.id AS pod_id, p.tbl_row_id, t.id, t.name
            FROM
                @wp_pod p
            INNER JOIN
                @wp_pod_types t ON t.id = p.datatype
            WHERE
                p.tbl_row_id = $params->tbl_row_id AND
                $select_dt
            LIMIT
                1
            ";
        }
        else {
            $sql = "
            SELECT
                p.id AS pod_id, p.tbl_row_id, t.id, t.name
            FROM
                @wp_pod p
            INNER JOIN
                @wp_pod_types t ON t.id = p.datatype
            WHERE
                p.id = $params->pod_id
            LIMIT
                1
            ";
        }

        $result = pod_query($sql);
        $row = mysql_fetch_assoc($result);
        $params->datatype_id = $row['id'];
        $params->datatype = $row['name'];
        $params->pod_id = $row['pod_id'];
        $params->tbl_row_id = $row['tbl_row_id'];

        // Get helper code
        $result = pod_query("SELECT pre_drop_helpers, post_drop_helpers FROM @wp_pod_types WHERE id = $params->datatype_id");
        $row = mysql_fetch_assoc($result);
        $params->pre_drop_helpers = explode(',', $row['pre_drop_helpers']);
        $params->post_drop_helpers = explode(',', $row['post_drop_helpers']);

        // Plugin hook
        do_action('pods_pre_drop_pod_item', $params);

        // Pre-drop helpers
        if (0 < count($params->pre_drop_helpers)) {
            foreach ($params->pre_drop_helpers as $helper) {
                $function_or_file = $helper;
                $check_function = $function_or_file;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FUNCTIONS') || !PODS_HELPER_FUNCTIONS))
                    $check_function = false;
                $check_file = null;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FILES') || !PODS_HELPER_FILES))
                    $check_file = false;
                if (false !== $check_function && false !== $check_file)
                    $function_or_file = pods_function_or_file($function_or_file, $check_function, 'helper', $check_file);
                else
                    $function_or_file = false;

                $content = false;
                if (!$function_or_file) {
                    $api = new PodAPI();
                    $params_helper = array('name' => $helper, 'type' => 'pre_drop');
                    if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                        $params_helper = pods_sanitize($params_helper);
                    $content = $api->load_helper($params_helper);
                    if (false !== $content && 0 < strlen(trim($content['phpcode'])))
                        $content = $content['phpcode'];
                    else
                        $content = false;
                }

                if (false === $content && false !== $function_or_file && isset($function_or_file['function']))
                    $function_or_file['function']($params, $this);
                elseif (false === $content && false !== $function_or_file && isset($function_or_file['file']))
                    locate_template($function_or_file['file'], true, true);
                elseif (false !== $content) {
                    if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                        eval("?>$content");
                    else
                        echo $content;
                }
            }
        }

        pod_query("DELETE FROM `@wp_pod_tbl_$params->datatype` WHERE id = $params->tbl_row_id LIMIT 1");
        pod_query("UPDATE @wp_pod_rel SET sister_pod_id = NULL WHERE sister_pod_id = $params->pod_id");
        pod_query("DELETE FROM @wp_pod WHERE id = $params->pod_id LIMIT 1");
        pod_query("DELETE FROM @wp_pod_rel WHERE pod_id = $params->pod_id");

        // Plugin hook
        do_action('pods_post_drop_pod_item', $params);

        // Post-drop helpers
        if (0 < count($params->post_drop_helpers)) {
            foreach ($params->post_drop_helpers as $helper) {
                $function_or_file = $helper;
                $check_function = $function_or_file;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FUNCTIONS') || !PODS_HELPER_FUNCTIONS))
                    $check_function = false;
                $check_file = null;
                if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FILES') || !PODS_HELPER_FILES))
                    $check_file = false;
                if (false !== $check_function && false !== $check_file)
                    $function_or_file = pods_function_or_file($function_or_file, $check_function, 'helper', $check_file);
                else
                    $function_or_file = false;

                $content = false;
                if (!$function_or_file) {
                    $api = new PodAPI();
                    $params_helper = array('name' => $helper, 'type' => 'post_drop');
                    if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                        $params_helper = pods_sanitize($params_helper);
                    $content = $api->load_helper($params_helper);
                    if (false !== $content && 0 < strlen(trim($content['phpcode'])))
                        $content = $content['phpcode'];
                    else
                        $content = false;
                }

                if (false === $content && false !== $function_or_file && isset($function_or_file['function']))
                    $function_or_file['function']($params, $this);
                elseif (false === $content && false !== $function_or_file && isset($function_or_file['file']))
                    locate_template($function_or_file['file'], true, true);
                elseif (false !== $content) {
                    if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                        eval("?>$content");
                    else
                        echo $content;
                }
            }
        }
    }

    /**
     * Check if a Pod exists
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @param array $params An associative array of parameters
     * @since 1.12
     */
    function pod_exists($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        if (!empty($params->id) || !empty($params->name)) {
            $where = empty($params->id) ? "name = '{$params->name}'" : "id = {$params->id}";
            $result = pod_query("SELECT id, name FROM @wp_pod_types WHERE {$where} LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $pod = mysql_fetch_assoc($result);
                return $pod;
            }
        }
        return false;
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
    function load_pod($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        if (!empty($params->id) || !empty($params->name)) {
            $where = empty($params->id) ? "name = '$params->name'" : "id = $params->id";
            $result = pod_query("SELECT * FROM @wp_pod_types WHERE $where LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $pod = mysql_fetch_assoc($result);
                $pod['fields'] = array();
                $result = pod_query("SELECT id, name, coltype, pickval, required, weight FROM @wp_pod_fields WHERE datatype = {$pod['id']} ORDER BY weight");
                while ($row = mysql_fetch_assoc($result)) {
                    $pod['fields'][$row['name']] = $row;
                }

                return $pod;
            }
        }
        return false;
    }

    /**
     * Load a column
     *
     * $params['id'] int The field ID
     * $params['name'] string The field name
     * $params['datatype'] int The Pod ID
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_column($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        if (isset($params->id))
            $params->id = absint($params->id);
        $where = empty($params->id) ? "`name` = '{$params->name}' AND `datatype` = {$params->datatype}" : "`id` = {$params->id}";
        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE {$where} LIMIT 1");
        return @mysql_fetch_assoc($result);
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
    function load_template($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        if (isset($params->id))
            $params->id = absint($params->id);
        $where = empty($params->id) ? "`name` = '{$params->name}'" : "`id` = {$params->id}";
        $result = pod_query("SELECT * FROM @wp_pod_templates WHERE {$where} LIMIT 1");
        return @mysql_fetch_assoc($result);
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
    function load_page($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        if (isset($params->id))
            $params->id = absint($params->id);
        $where = empty($params->id) ? "`uri` = '{$params->uri}'" : "`id` = {$params->id}";
        $result = pod_query("SELECT * FROM @wp_pod_pages WHERE {$where} LIMIT 1");
        return @mysql_fetch_assoc($result);
    }

    /**
     * Load a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     * $params['type'] string The helper type
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_helper($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        if (isset($params->id))
            $params->id = absint($params->id);
        $where = empty($params->id) ? "`name` = '{$params->name}'" : "`id` = {$params->id}";
        if (isset($params->type) && !empty($params->type))
            $where .= " AND `helper_type` = '{$params->type}'";
        $result = pod_query("SELECT * FROM @wp_pod_helpers WHERE {$where} LIMIT 1");
        return @mysql_fetch_assoc($result);
    }

    /**
     * Load the input form for a pod item
     *
     * $params['datatype'] string The datatype name
     * $params['pod_id'] int The item's pod ID
     * $params['tbl_row_id'] int (optional) The item's ID
     * $params['public_columns'] array An associative array of columns
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_pod_item($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        $params->tbl_row_id = (int) (isset($params->tbl_row_id)?$params->tbl_row_id:null);
        $params->pod_id = (int) (isset($params->pod_id)?$params->pod_id:null);
        if (empty($params->tbl_row_id)) {
            $params->tbl_row_id = null;
            if (!empty($params->pod_id)) {
                $result = pod_query("SELECT tbl_row_id FROM @wp_pod WHERE id = $params->pod_id LIMIT 1",'Item not found',null,'Item not found');
                $params->tbl_row_id = mysql_result($result, 0);
            }
        }
        $obj = new Pod($params->datatype,$params->tbl_row_id);
        $pod_id = 0;
        if (!empty($params->tbl_row_id) && !empty($obj->data)) {
            $pod_id = $obj->get_pod_id();
        }
        return $obj->showform($pod_id, $params->public_columns = null);
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
    function load_sister_fields($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;

        if (!empty($params->pickval) && is_string($params->pickval)) {
            $result = pod_query("SELECT id FROM @wp_pod_types WHERE name = '$params->pickval' LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $sister_datatype = mysql_result($result, 0);

                $result = pod_query("SELECT name FROM @wp_pod_types WHERE id = $params->datatype LIMIT 1");
                if (0 < mysql_num_rows($result)) {
                    $datatype_name = mysql_result($result, 0);

                    $result = pod_query("SELECT id, name FROM @wp_pod_fields WHERE datatype = $sister_datatype AND pickval = '$datatype_name'");
                    if (0 < mysql_num_rows($result)) {
                        while ($row = mysql_fetch_assoc($result)) {
                            $sister_fields[] = $row;
                        }
                        return $sister_fields;
                    }
                }
            }
        }
    }

    /**
     * Export a package
     *
     * $params['pod'] string Pod Type IDs to export
     * $params['template'] string Template IDs to export
     * $params['podpage'] string Pod Page IDs to export
     * $params['helper'] string Helper IDs to export
     *
     * @param array $params An associative array of parameters
     * @since 1.9.0
     */
    function export_package($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $export = array(
            'meta' => array(
                'version' => PODS_VERSION,
                'build' => date('U'),
            )
        );

        $pod_ids = $params['pod'];
        $template_ids = $params['template'];
        $pod_page_ids = $params['podpage'];
        $helper_ids = $params['helper'];

        // Get pods
        if (!empty($pod_ids)) {
            $result = pod_query("SELECT * FROM @wp_pod_types WHERE id IN ($pod_ids)");
            while ($row = mysql_fetch_assoc($result)) {
                $dt = $row['id'];
                unset($row['id']);
                $export['pods'][$dt] = $row;
            }

            // Get pod fields
            $result = pod_query("SELECT * FROM @wp_pod_fields WHERE datatype IN ($pod_ids)");
            while ($row = mysql_fetch_assoc($result)) {
                unset($row['id']);
                $dt = $row['datatype'];
                unset($row['datatype']);
                unset($row['sister_field_id']); // impossible to reference this correctly until all pods / fields have been added
                $export['pods'][$dt]['fields'][] = $row;
            }
        }

        // Get templates
        if (!empty($template_ids)) {
            $result = pod_query("SELECT * FROM @wp_pod_templates WHERE id IN ($template_ids)");
            while ($row = mysql_fetch_assoc($result)) {
                unset($row['id']);
                $export['templates'][] = $row;
            }
        }

        // Get pod pages
        if (!empty($pod_page_ids)) {
            $result = pod_query("SELECT * FROM @wp_pod_pages WHERE id IN ($pod_page_ids)");
            while ($row = mysql_fetch_assoc($result)) {
                unset($row['id']);
                $export['pod_pages'][] = $row;
            }
        }

        // Get helpers
        if (!empty($helper_ids)) {
            $result = pod_query("SELECT * FROM @wp_pod_helpers WHERE id IN ($helper_ids)");
            while ($row = mysql_fetch_assoc($result)) {
                unset($row['id']);
                $export['helpers'][] = $row;
            }
        }

        if (1 == count($export))
            return false;

        return $export;
    }

    /**
     * Replace an existing package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     * @since 1.9.8
     */
    function replace_package($data = false) {
        return $this->import_package($data, true);
    }

    /**
     * Import a package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     * @param bool $replace (optional) Replace existing items when found
     * @since 1.9.0
     */
    function import_package($data = false, $replace = false) {
        $output = false;
        if (false===$data || isset($data['action'])) {
            $data = get_option('pods_package');
            $output = true;
        }
        if (!is_array($data)) {
            $json_data = @json_decode($data, true);
            if (!is_array($json_data))
                $json_data = @json_decode(stripslashes($data), true);
            $data = $json_data;
        }
        if (!is_array($data) || empty($data)) {
            return false;
        }

        $dbtypes = array(
            'bool' => 'bool default 0',
            'date' => 'datetime',
            'num' => 'decimal(9,2)',
            'txt' => 'varchar(128)',
            'slug' => 'varchar(128)',
            'code' => 'mediumtext',
            'desc' => 'mediumtext'
        );
        $dbtypes = apply_filters('pods_column_dbtypes', $dbtypes, $this);

        $found = array();

        if (isset($data['pods'])) {
            $pod_columns = '';
            foreach ($data['pods'] as $pod) {
                $pod = pods_sanitize($pod);

                $table_columns = array();
                $pod_fields = $pod['fields'];
                unset($pod['fields']);

                if (false !== $replace) {
                    $existing = $this->load_pod(array('name' => $pod['name']));
                    if (is_array($existing))
                        $this->drop_pod(array('id' => $existing['id']));
                }

                if (empty($pod_columns))
                    $pod_columns = implode("`,`", array_keys($pod));
                // Backward-compatibility (before/after helpers)
                $pod_columns = str_replace('before_helpers', 'pre_save_helpers', $pod_columns);
                $pod_columns = str_replace('after_helpers', 'post_save_helpers', $pod_columns);

                $values = implode("','", $pod);
                $dt = pod_query("INSERT INTO @wp_pod_types (`$pod_columns`) VALUES ('$values')");

                $tupples = array();
                $field_columns = '';
                foreach ($pod_fields as $fieldval) {
                    // Escape the values
                    foreach ($fieldval as $k => $v) {
                        if (empty($v))
                            $v = 'null';
                        else
                            $v = pods_sanitize($v);
                        $fieldval[$k] = $v;
                    }

                    // Store all table columns
                    if ('pick' != $fieldval['coltype'] && 'file' != $fieldval['coltype'])
                        $table_columns[$fieldval['name']] = $fieldval['coltype'];

                    $fieldval['datatype'] = $dt;
                    if (empty($field_columns))
                        $field_columns = implode("`,`", array_keys($fieldval));
                    $tupples[] = implode("','", $fieldval);
                }
                $tupples = implode("'),('", $tupples);
                $tupples = str_replace("'null'", 'null', $tupples);
                pod_query("INSERT INTO @wp_pod_fields (`$field_columns`) VALUES ('$tupples')");

                // Create the actual table with any non-PICK columns
                $definitions = array("id INT unsigned auto_increment primary key");
                foreach ($table_columns as $colname => $coltype) {
                    $definitions[] = "`$colname` {$dbtypes[$coltype]}";
                }
                $definitions = implode(',', $definitions);
                pod_query("CREATE TABLE @wp_pod_tbl_{$pod['name']} ($definitions)");
                if (!isset($found['pods']))
                    $found['pods'] = array();
                $found['pods'][] = esc_textarea($pod['name']);
            }
        }

        if (isset($data['templates'])) {
            foreach ($data['templates'] as $template) {
                $defaults = array('name' => '', 'code' => '');
                $params = array_merge($defaults, $template);
                if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                    $params = pods_sanitize($params);
                if (false !== $replace) {
                    $existing = $this->load_template(array('name' => $params['name']));
                    if (is_array($existing))
                        $params['id'] = $existing['id'];
                }
                $this->save_template($params);
                if (!isset($found['templates']))
                    $found['templates'] = array();
                $found['templates'][] = esc_textarea($params['name']);
            }
        }

        if (isset($data['pod_pages'])) {
            foreach ($data['pod_pages'] as $pod_page) {
                $defaults = array('uri' => '', 'title' => '', 'phpcode' => '', 'precode' => '', 'page_template' => '');
                $params = array_merge($defaults, $pod_page);
                if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                    $params = pods_sanitize($params);
                if (false !== $replace) {
                    $existing = $this->load_page(array('uri' => $params['uri']));
                    if (is_array($existing))
                        $params['id'] = $existing['id'];
                }
                $this->save_page($params);
                if (!isset($found['pod_pages']))
                    $found['pod_pages'] = array();
                $found['pod_pages'][] = esc_textarea($params['uri']);
            }
        }

        if (isset($data['helpers'])) {
            foreach ($data['helpers'] as $helper) {
                // backwards compatibility
                if (isset($helper['helper_type'])) {
                    if ('before' == $helper['helper_type'])
                        $helper['helper_type'] = 'pre_save';
                    if ('after' == $helper['helper_type'])
                        $helper['helper_type'] = 'post_save';
                }
                $defaults = array('name' => '', 'helper_type' => 'display', 'phpcode' => '');
                $params = array_merge($defaults, $helper);
                if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                    $params = pods_sanitize($params);
                if (false !== $replace) {
                    $existing = $this->load_helper(array('name' => $params['name']));
                    if (is_array($existing))
                        $params['id'] = $existing['id'];
                }
                $this->save_helper($params);
                if (!isset($found['helpers']))
                    $found['helpers'] = array();
                $found['helpers'][] = esc_textarea($params['name']);
            }
        }
        if (true===$output) {
            if (!empty($found)) {
                echo '<br /><div id="message" class="updated fade">';
                echo '<h3 style="margin-top:10px;">Package Imported:</h3>';
                if (isset($found['pods'])) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['pods']) . '</li></ul>';
                }
                if (isset($found['templates'])) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['templates']) . '</li></ul>';
                }
                if (isset($found['pod_pages'])) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['pod_pages']) . '</li></ul>';
                }
                if (isset($found['helpers'])) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['helpers']) . '</li></ul>';
                }
                echo '</div>';
            }
            else
                echo '<e><br /><div id="message" class="error fade"><p>Error: Package not imported, try again.</p></div></e>';
        }
        if (!empty($found))
            return true;
        return false;
    }

    /**
     * Validate a package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     * @since 1.9.0
     */
    function validate_package($data = false, $output = false) {
        if (is_array($data)&&isset($data['data'])) {
            $data = $data['data'];
            $output = true;
        }
        if (is_array($data))
            $data = esc_textarea(json_encode($data));

        $found = array();
        $warnings = array();

        update_option('pods_package', $data);

        $json_data = @json_decode($data, true);
        if (!is_array($json_data))
            $json_data = @json_decode(stripslashes($data), true);

        if (!is_array($json_data) || empty($json_data)) {
            $warnings[] = "This is not a valid package. Please try again.";
            if (true===$output) {
                echo '<e><br /><div id="message" class="error fade"><p>This is not a valid package. Please try again.</p></div></e>';
                return false;
            }
            else
                return $warnings;
        }
        $data = $json_data;

        if (0 < strlen($data['meta']['version']) && false === strpos($data['meta']['version'], '.') && (int) $data['meta']['version'] < 1000) { // older style
            $data['meta']['version'] = implode('.', str_split($data['meta']['version']));
        }
        elseif (0 < strlen($data['meta']['version']) && false === strpos($data['meta']['version'], '.')) { // old style
            $data['meta']['version'] = pods_version_to_point($data['meta']['version']);
        }

        if (isset($data['meta']['compatible_from'])) {
            if (0 < strlen($data['meta']['compatible_from']) && false === strpos($data['meta']['compatible_from'], '.')) { // old style
                $data['meta']['compatible_from'] = pods_version_to_point($data['meta']['compatible_from']);
            }
            if (version_compare(PODS_VERSION, $data['meta']['compatible_from'], '<')) {
                $compatible_from = explode('.', $data['meta']['compatible_from']);
                $compatible_from = $compatible_from[0] . '.' . $compatible_from[1];
                $pods_version = explode('.', PODS_VERSION);
                $pods_version = $pods_version[0] . '.' . $pods_version[1];
                if (version_compare($pods_version, $compatible_from, '<'))
                    $warnings['version'] = 'This package may only compatible with the newer <strong>Pods ' . pods_version_to_point($data['meta']['compatible_from']) . '+</strong>, but you are currently running the older <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }
        if (isset($data['meta']['compatible_to'])) {
            if (0 < strlen($data['meta']['compatible_to']) && false === strpos($data['meta']['compatible_to'], '.')) { // old style
                $data['meta']['compatible_to'] = pods_version_to_point($data['meta']['compatible_to']);
            }
            if (version_compare($data['meta']['compatible_to'], PODS_VERSION, '<')) {
                $compatible_to = explode('.', $data['meta']['compatible_to']);
                $compatible_to = $compatible_to[0] . '.' . $compatible_to[1];
                $pods_version = explode('.', PODS_VERSION);
                $pods_version = $pods_version[0] . '.' . $pods_version[1];
                if (version_compare($compatible_to, $pods_version, '<'))
                    $warnings['version'] = 'This package may only compatible with the older <strong>Pods ' . $data['meta']['compatible_to'] . '</strong>, but you are currently running the newer <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }
        if (!isset($data['meta']['compatible_from']) && !isset($data['meta']['compatible_to'])) {
            if (version_compare(PODS_VERSION, $data['meta']['version'], '<')) {
                $compatible_from = explode('.', $data['meta']['version']);
                $compatible_from = $compatible_from[0] . '.' . $compatible_from[1];
                $pods_version = explode('.', PODS_VERSION);
                $pods_version = $pods_version[0] . '.' . $pods_version[1];
                if (version_compare($pods_version, $compatible_from, '<'))
                    $warnings['version'] = 'This package was built using the newer <strong>Pods ' . $data['meta']['version'] . '</strong>, but you are currently running the older <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
            elseif (version_compare($data['meta']['version'], PODS_VERSION, '<')) {
                $compatible_to = explode('.', $data['meta']['version']);
                $compatible_to = $compatible_to[0] . '.' . $compatible_to[1];
                $pods_version = explode('.', PODS_VERSION);
                $pods_version = $pods_version[0] . '.' . $pods_version[1];
                if (version_compare($compatible_to, $pods_version, '<'))
                    $warnings['version'] = 'This package was built using the older <strong>Pods ' . $data['meta']['version'] . '</strong>, but you are currently running the newer <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }

        if (isset($data['pods'])) {
            foreach ($data['pods'] as $pod) {
                $pod = pods_sanitize($pod);
                $existing = $this->load_pod(array('name' => $pod['name']));
                if (is_array($existing)) {
                    if (!isset($warnings['pods']))
                        $warnings['pods'] = array();
                    $warnings['pods'][] = esc_textarea($pod['name']);
                }
                if (!isset($found['pods']))
                    $found['pods'] = array();
                $found['pods'][] = esc_textarea($pod['name']);
            }
        }

        if (isset($data['templates'])) {
            foreach ($data['templates'] as $template) {
                $template = pods_sanitize($template);
                $existing = $this->load_template(array('name' => $template['name']));
                if (is_array($existing)) {
                    if (!isset($warnings['templates']))
                        $warnings['templates'] = array();
                    $warnings['templates'][] = esc_textarea($template['name']);
                }
                if (!isset($found['templates']))
                    $found['templates'] = array();
                $found['templates'][] = esc_textarea($template['name']);
            }
        }

        if (isset($data['pod_pages'])) {
            foreach ($data['pod_pages'] as $pod_page) {
                $pod_page = pods_sanitize($pod_page);
                $existing = $this->load_page(array('uri' => $pod_page['uri']));
                if (is_array($existing)) {
                    if (!isset($warnings['pod_pages']))
                        $warnings['pod_pages'] = array();
                    $warnings['pod_pages'][] = esc_textarea($pod_page['uri']);
                }
                if (!isset($found['pod_pages']))
                    $found['pod_pages'] = array();
                $found['pod_pages'][] = esc_textarea($pod_page['uri']);
            }
        }

        if (isset($data['helpers'])) {
            foreach ($data['helpers'] as $helper) {
                $helper = pods_sanitize($helper);
                $existing = $this->load_helper(array('name' => $helper['name']));
                if (is_array($existing)) {
                    if (!isset($warnings['helpers']))
                        $warnings['helpers'] = array();
                    $warnings['helpers'][] = esc_textarea($helper['name']);
                }
                if (!isset($found['helpers']))
                    $found['helpers'] = array();
                $found['helpers'][] = esc_textarea($helper['name']);
            }
        }

        if (true===$output) {
            if (!empty($found)) {
                echo '<hr />';
                echo '<h3>Package Contents:</h3>';
                if (isset($warnings['version']))
                    echo '<p><em><strong>NOTICE:</strong> ' . $warnings['version'] . '</em></p>';
                if (isset($found['pods'])) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['pods']) . '</li></ul>';
                }
                if (isset($found['templates'])) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['templates']) . '</li></ul>';
                }
                if (isset($found['pod_pages'])) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['pod_pages']) . '</li></ul>';
                }
                if (isset($found['helpers'])) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $found['helpers']) . '</li></ul>';
                }
            }
            if (0 < count($warnings) && (!isset($warnings['version']) || 1 < count($warnings))) {
                echo '<hr />';
                echo '<h3 class="red">WARNING: There are portions of this package that already exist</h3>';
                if (isset($warnings['pods'])) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $warnings['pods']) . '</li></ul>';
                }
                if (isset($warnings['templates'])) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $warnings['templates']) . '</li></ul>';
                }
                if (isset($warnings['pod_pages'])) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $warnings['pod_pages']) . '</li></ul>';
                }
                if (isset($warnings['helpers'])) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode('</li><li>', $warnings['helpers']) . '</li></ul>';
                }
                echo '<p><input type="button" class="button-primary" style="background:#f39400;border-color:#d56500;" onclick="podsImport(\'replace_package\')" value=" Overwrite the existing package (Step 2 of 2) " />&nbsp;&nbsp;&nbsp;<input type="button" class="button-secondary" onclick="podsImportCancel()" value=" Cancel " /></p>';
                return false;
            }
            elseif (!empty($found)) {
                echo '<p><input type="button" class="button-primary" onclick="podsImport(\'import_package\')" value=" Import Package (Step 2 of 2) " />&nbsp;&nbsp;&nbsp;<input type="button" class="button-secondary" onclick="podsImportCancel()" value=" Cancel " /></p>';
                return false;
            }
            echo '<e><br /><div id="message" class="error fade"><p>Error: This package is empty, there is nothing to import.</p></div></e>';
            return false;
        }
        if (0 < count($warnings))
            return $warnings;
        elseif (!empty($found))
            return true;
        return false;
    }

    /**
     * Import data
     *
     * @param mixed $data PHP associative array or CSV input
     * @param bool $numeric_mode Use IDs instead of the name field when matching
     * @since 1.7.1
     */
    function import($data, $numeric_mode = false) {
        global $wpdb;
        if ('csv' == $this->format) {
            $data = $this->csv_to_php($data);
        }

        pod_query("SET NAMES utf8");
        pod_query("SET CHARACTER SET utf8");

        // Get the id/name pairs of all associated pick/file tables
        $pick_values = $file_values = array();
        foreach ($this->fields as $field_name => $field_data) {
            $pickval = $field_data['pickval'];
            if ('file' == $field_data['coltype']) {
                $res = pod_query("SELECT ID as id, guid as name FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY id");
                while ($item = mysql_fetch_assoc($res)) {
                    $file_url = str_replace(get_bloginfo('url'), '', $item['name']);
                    $file_values[$field_name][$file_url] = $item['id'];
                    $file_values[$field_name][$item['name']] = $item['id'];
                }
            }
            elseif ('pick' == $field_data['coltype']) {
                if ('wp_taxonomy' == $pickval) {
                    $res = pod_query("SELECT term_id AS id, name FROM $wpdb->terms ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
                elseif ('wp_page' == $pickval || 'wp_post' == $pickval) {
                    $pickval = str_replace('wp_', '', $pickval);
                    $res = pod_query("SELECT ID as id, post_title as name FROM $wpdb->posts WHERE post_type = '$pickval' ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
                elseif ('wp_user' == $pickval) {
                    $res = pod_query("SELECT ID as id, display_name as name FROM $wpdb->users ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
                else {
                    $res = pod_query("SELECT id, name FROM @wp_pod_tbl_{$pickval} ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
                        $pick_values[$field_name][$item['name']] = $item['id'];
                    }
                }
            }
        }

        // Loop through the array of items
        $ids = array();

        // Test to see if it's an array of arrays
        foreach ($data as $key => $data_row) {
            if(!is_array($data_row)){
                $data = array($data);
            }
            break;
        }
        foreach ($data as $key => $data_row) {
            $columns = array();

            // Loop through each field (use $this->fields so only valid columns get parsed)
            foreach ($this->fields as $field_name => $field_data) {
                $field_id = $field_data['id'];
                $coltype = $field_data['coltype'];
                $pickval = $field_data['pickval'];
                $field_value = $data_row[$field_name];

                if (null != $field_value && false !== $field_value) {
                    if ('pick' == $coltype || 'file' == $coltype) {
                        $field_values = is_array($field_value) ? $field_value : array($field_value);
                        $pick_value = array();
                        foreach ($field_values as $key => $pick_title) {
                            if (is_int($pick_title) && false !== $numeric_mode) {
                                $pick_value[] = $pick_title;
                            }
                            elseif (!empty($pick_values[$field_name][$pick_title])) {
                                $pick_value[] = $pick_values[$field_name][$pick_title];
                            }
                        }
                        $field_value = implode(',',$pick_value);
                    }
                    $columns[$field_name] = esc_sql(trim($field_value));
                }
            }
            if (!empty($columns)) {
                $params = array('datatype'=>$this->dtname,'columns'=>$columns);
                $ids[] = $this->save_pod_item($params);
            }
        }
        return $ids;
    }

    /**
     * Export data
     *
     * @since 1.7.1
     */
    function export() {
        global $wpdb;
        $data = array();
        $fields = array();
        $pick_values = array();

        // Find all pick/file fields
        $result = pod_query("SELECT id, name, coltype, pickval FROM @wp_pod_fields WHERE datatype = {$this->dt} ORDER BY weight");
        while ($row = mysql_fetch_assoc($result)) {
            $field_id = $row['id'];
            $field_name = $row['name'];
            $coltype = $row['coltype'];
            $pickval = $row['pickval'];

            // Store all pick/file values into an array
            if ('file' == $coltype) {
                $res = pod_query("SELECT ID AS id, guid AS name FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY id");
                while ($item = mysql_fetch_assoc($res)) {
                    $pick_values[$field_name][$item['id']] = $item['name'];
                }
            }
            elseif ('pick' == $coltype) {
                if ('wp_taxonomy' == $pickval) {
                    $res = pod_query("SELECT term_id AS id, name FROM $wpdb->terms ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
                elseif ('wp_page' == $pickval || 'wp_post' == $pickval) {
                    $pickval = str_replace('wp_', '', $pickval);
                    $res = pod_query("SELECT ID as id, post_title as name FROM $wpdb->posts WHERE post_type = '$pickval' ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
                elseif ('wp_user' == $pickval) {
                    $res = pod_query("SELECT ID as id, display_name as name FROM $wpdb->users ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
                        $pick_values[$field_name][$item['id']] = $item['name'];
                    }
                }
                else {
                    $res = pod_query("SELECT id, name FROM @wp_pod_tbl_{$pickval} ORDER BY id");
                    while ($item = mysql_fetch_assoc($res)) {
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
        while ($row = mysql_fetch_assoc($result)) {
            $item_id = $row['item_id'];
            $tbl_row_id = $row['tbl_row_id'];
            $field_name = $fields[$row['field_id']];
            $pick_array[$field_name][$tbl_row_id][] = $pick_values[$field_name][$item_id];
        }

        // Access the current datatype
        $result = pod_query("SELECT * FROM @wp_pod_tbl_{$this->dtname} ORDER BY id");
        while ($row = mysql_fetch_assoc($result)) {
            $tmp = array();
            $row_id = $row['id'];

            foreach ($fields as $junk => $fname) {
                if (isset($pick_array[$fname][$row_id])) {
                    $tmp[$fname] = $pick_array[$fname][$row_id];
                }
                else {
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
    function csv_to_php($data) {
        $delimiter = ",";
        $expr = "/$delimiter(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
        $data = str_replace("\r\n", "\n", $data);
        $data = str_replace("\r", "\n", $data);
        $lines = explode("\n", $data);
        $field_names = explode($delimiter, array_shift($lines));
        $field_names = preg_replace("/^\"(.*)\"$/s", "$1", $field_names);
        foreach ($lines as $line) {
            // Skip the empty line
            if (empty($line)) continue;
            $fields = preg_split($expr, trim($line));
            $fields = preg_replace("/^\"(.*)\"$/s", "$1", $fields);
            foreach ($field_names as $key => $field) {
                $tmp[$field] = $fields[$key];
            }
            $out[] = $tmp;
        }
        return $out;
    }

    /**
     * Resync wp_pod and wp_pod_tbl_* tables
     *
     * wp_pod_tbl_* is assumed the primary source
     * (if not found there, it'll get deleted from wp_pod)
     *
     * This might take a bit!
     *
     * @since 1.10.1
     */
    function fix_wp_pod() {
        $result = pod_query("SELECT id, name FROM @wp_pod_types ORDER BY name");
        while ($row = mysql_fetch_array($result)) {
            $id = (int) $row['id'];
            $name = pods_sanitize($row['name']);
            pod_query("DELETE p FROM `@wp_pod` AS p LEFT JOIN `@wp_pod_tbl_{$name}` AS t ON t.id = p.tbl_row_id WHERE p.datatype = {$id} AND t.id IS NULL");
            pod_query("INSERT INTO `@wp_pod` (tbl_row_id, name, datatype, created, modified, author_id) SELECT t.id AS tbl_row_id, t.name AS name, {$id} AS datatype, '" . current_time('mysql') . "' AS created, '" . current_time('mysql') . "' AS modified, 0 AS author_id FROM `@wp_pod_tbl_{$name}` AS t LEFT JOIN `@wp_pod` AS p ON p.datatype = {$id} AND p.tbl_row_id = t.id WHERE p.id IS NULL");
        }
    }
}