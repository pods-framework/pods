<?php
class PodsAPI
{
    public $display_errors = false;

    public $pod;
    public $pod_id;
    public $pod_data;
    public $fields;
    
    public $format = 'php';

    /**
     * Store and retrieve data programatically
     *
     * @param string $dtname (optional) The pod name
     * @param string $format (optional) Format for import/export, "php" or "csv"
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.7.1
     */
    function __construct ($pod = null, $format = 'php') {
        if (!empty($pod)) {
            $this->format = $format;
            $this->pod_data = $this->load_pod(array('name' => $pod));
            if (false !== $this->pod_data && is_array($this->pod_data)) {
                $this->pod = $this->pod_data['name'];
                $this->pod_id = $this->pod_data['id'];
                $this->fields = $this->pod_data['fields'];
            }
        else
                return false;
            return true;
        }
    }

    /**
     * Add or edit a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     * $params['type'] string The Pod type
     * $params['object'] string Object name
     * $params['options'] array Options
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_pod ($params) {
        $pod = $this->load_pod($params);
    $params = (object) pods_sanitize($params);
        if (!empty($pod)) {
            $params->id = $pod['id'];
            $params->name = $pod['name'];
        }

        // Add new pod
        if (empty($params->id)) {
            $params->name = pods_clean_name($params->name);
            if (strlen($params->name) < 1)
                return pods_error('Pod name cannot be empty', $this);

            $check = pods_query("SELECT `id` FROM `@wp_pods` WHERE `name` = '{$params->name}' LIMIT 1", $this);
            if (!empty($check))
                return pods_error('Duplicate Pod name', $this);

            $columns = array('name' => $params->name, 'options' => '');
            if (isset($params->type) && 0 < strlen($params->type))
                $columns['type'] = $params->type;
            if (isset($params->object) && 0 < strlen($params->object))
                $columns['object'] = $params->object;
            if (!isset($params->options) || empty($params->options)) {
                $options = get_object_vars($params);
                $exclude = array('id', 'name', 'type', 'object', 'options');
                foreach ($exclude as $exclude_field) {
                    if (isset($options[$exclude_field]))
                        unset($options[$exclude_field]);
                }
                $params->options = '';
                if (!empty($options))
                    $params->options = $options;
            }
            if (!empty($params->options)) {
                $options['columns'] = str_replace('@wp_', '{prefix}', json_encode($params->options));
            }
            $params->id = pods_query("INSERT INTO `@wp_pods` (`" . implode('`,`', array_keys($columns)) . "`) VALUES ('" . implode("','",pods_sanitize($columns)) . "')", $this);
            if (false === $params->id)
                return pods_error('Cannot add entry for new Pod', $this);

            $field_columns = array('pod_id' => $params->id,
                                'name' => '',
                                'label' => '',
                                'type' => 'txt',
                                'pick_object' => '',
                                'pick_val' => '',
                                'sister_field_id' => 0,
                                'weight' => 0,
                                'options' => '');
            $fields = array(
                            array('name' => 'name',
                                'label' => 'Name',
                                'type' => 'txt',
                                'weight' => '0',
                                'options' => array('required' => '1')),
                            array('name' => 'slug',
                                'label' => 'Permalink',
                                'type' => 'slug',
                                'weight' => '1',
                                'options' => array('comment' => 'Leave blank to auto-generate from Name'))
                            );
            if (isset($params->fields) && is_array($params->fields) && !empty($params->fields))
                $fields = $params->fields;
            $rows = array();
            $definitions = array("`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY");
            foreach ($fields as $field) {
                $row = array();
                foreach ($field_columns as $column => $default) {
                    $row[$column] = $default;
                    if (isset($field[$column]) && !empty($field[$column]))
                        $row[$column] = $field[$column];
                    if (!isset($row['options']) && !isset($field['options']))
                        $row['options'] = $field;
                    if (!empty($row['options'])) {
                        if (is_array($row['options'])) {
                            $options = $row['options'];
                            $exclude = array('id','pod_id','name','type','pick_object','pick_val','sister_field_id','weight','options');
                            foreach ($exclude as $exclude_field) {
                                if (isset($options[$exclude_field]))
                                    unset($options[$exclude_field]);
                            }
                            $row['options'] = '';
                            if (!empty($options))
                                $row['options'] = str_replace('@wp_', '{prefix}', json_encode($options));
                        }
                    }
                    $row = pods_sanitize($row);
                }
                $rows[] = implode("','", $row);
                if (!in_array($row['type'], array('pick','file')))
                    $definitions[] = "`{$row['name']}` " . $this->get_column_definition($row['type']);
            }
            $result = pods_query("CREATE TABLE `@wp_pods_tbl_{$params->name}` ({$definitions}) DEFAULT CHARSET utf8", $this);
            if (empty($result))
                return pods_error('Cannot add Database Table for new Pod');
            $result = pods_query("INSERT INTO `@wp_pods_fields` (`" . implode('`,`', array_keys($field_columns)) . "`) VALUES ('" . implode('),(', $rows) . "')", $this);
            if (empty($result))
                return pods_error('Cannot add fields for new Pod');
        }
        // Edit existing pod
        else {
            if (!isset($params->options) || empty($params->options)) {
                $options = get_object_vars($params);
                $exclude = array('id','name','type','object','options');
                foreach ($exclude as $exclude_field) {
                    if (isset($options[$exclude_field]))
                        unset($options[$exclude_field]);
                }
                $params->options = '';
                if (!empty($options))
                    $params->options = $options;
            }
            if (!empty($params->options)) {
                $params->options = str_replace('@wp_', '{prefix}', json_encode($params->options));
            }
            $params = pods_sanitize($params);
            $result = pods_query("UPDATE `@wp_pods` SET `options` = '{$params->options}' WHERE `id` = {$params->id}", $this);
            if (empty($result))
                return pods_error('Cannot edit Pod');
        }
        return $params->id;
    }

    /**
     * Add or edit a column within a Pod
     *
     * $params['id'] int The field ID
     * $params['pod_id'] int The Pod ID
     * $params['name'] string The field name
     * $params['label'] string The field label
     * $params['type'] string The column type ("txt", "desc", "pick", etc)
     * $params['pick_object'] string The related PICK object name
     * $params['pick_val'] string The related PICK object value
     * $params['sister_field_id'] int (optional) The related field ID
     * $params['weight'] int The field weight
     * $params['options'] array The field options
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_column ($params) {
        $params = (object) $params;

        $params->pod_id = pods_absint($params->pod_id);
        if (empty($params->pod_id))
            return pods_error('Pod ID is required', $this);

        $params->name = trim(str_replace('-', '_', pods_clean_name(strtolower($params->name))), ' _');
        if (empty($params->name))
            return pods_error('Pod Column name is required', $this);

        // Add new column
        if (!isset($params->id) || empty($params->id)) {
            if (in_array($params->name, array('p'))) // there are more, let's add them as we find them
                return pods_error("$params->name is reserved for internal WordPress usage, please try a different name", $this);
            if (in_array($params->name, array('id', 'created', 'modified', 'author')))
                return pods_error("$params->name is reserved for internal Pods usage, please try a different name", $this);

            $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `name` = '{$params->name}' LIMIT 1";
            $result = pods_query($sql, $this);
            if (!empty($result))
                return pods_error("Pod Column {$params->name} already exists", $this);

            if ('slug' == $params->type) {
                $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `type` = 'slug' LIMIT 1";
                $result = pods_query($sql, $this);
                if (!empty($result))
                    return pods_error('This pod already has a permalink column', $this);
            }

            // Sink the new column to the bottom of the list
            if (!isset($params->weight)) {
                $params->weight = 0;
                $result = pods_query("SELECT `weight` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} ORDER BY `weight` DESC LIMIT 1", $this);
                if (!empty($result))
                    $params->weight = pods_absint($result[0]->weight) + 1;
            }

            $params->sister_field_id = pods_absint($params->sister_field_id);
            if (!isset($params->options) || empty($params->options)) {
                $options = $params->options;
                $exclude = array('id','pod_id','name','type','pick_object','pick_val','sister_field_id','weight','options');
                foreach ($exclude as $exclude_field) {
                    if (isset($options[$exclude_field]))
                        unset($options[$exclude_field]);
                }
                $params->options = '';
                if (!empty($options))
                    $params->options = $options;
            }
            if (!empty($params->options)) {
                $params->options = str_replace('@wp_', '{prefix}', json_encode($params->options));
            }
            $field_id = pods_query("INSERT INTO `@wp_pods_fields` (`pod_id`, `name`, `label`, `type`, `pick_object`, `pick_val`, `sister_field_id`, `weight`, `options`) VALUES ('{$params->pod_id}', '{$params->name}', '{$params->label}', '{$params->type}', '{$params->pick_object}', '{$params->pick_val}', {$params->sister_field_id}, {$params->weight}, '{$params->options}')", 'Cannot add new field');

            if (!in_array($params->type, array('pick','file'))) {
                $dbtype = $this->get_column_definition($params->type);
                pods_query("ALTER TABLE `@wp_pods_tbl_{$params->pod}` ADD COLUMN `{$params->name}` {$dbtype}", 'Cannot create new column');
            }
            elseif (0 < $params->sister_field_id) {
                pods_query("UPDATE `@wp_pods_fields` SET `sister_field_id` = '{$field_id}' WHERE `id` = {$params->sister_field_id} LIMIT 1", 'Cannot update sister field');
            }
        }
        // Edit existing column
        else {
            $params->id = pods_absint($params->id);
            if ('id' == $params->name) {
                return pods_error("$params->name is not editable", $this);
            }

            $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `id` != {$params->id} AND name = '{$params->name}' LIMIT 1";
            $check = pods_query($sql, $this);
            if (!empty($check))
                return pods_error("Column {$params->name} already exists", $this);

            $sql = "SELECT * FROM `@wp_pods_fields` WHERE `id` = {$params->id} LIMIT 1";
            $result = pods_query($sql, $this);
            if (!empty($result))
                return pods_error("Column {$params->name} not found, cannot edit", $this);

            $old_type = $result[0]->type;
            $old_name = $result[0]->name;

            $dbtype = $this->get_column_definition($params->type);
            $pickval = ('pick' != $params->type || empty($params->pickval)) ? '' : "$params->pickval";
            $params->sister_field_id = pods_absint($params->sister_field_id);

            if ($params->type != $old_type) {
                if ('pick' == $params->type || 'file' == $params->type) {
                    if ('pick' != $old_type && 'file' != $old_type) {
                        pods_query("ALTER TABLE `@wp_pods_tbl_$params->dtname` DROP COLUMN `$old_name`");
                    }
                }
                elseif ('pick' == $old_type || 'file' == $old_type) {
                    pods_query("ALTER TABLE `@wp_pods_tbl_$params->dtname` ADD COLUMN `$params->name` $dbtype", 'Cannot create column');
                    pods_query("UPDATE @wp_pods_fields SET sister_field_id = NULL WHERE sister_field_id = $params->id");
                    pods_query("DELETE FROM @wp_pods_rel WHERE field_id = $params->id");
                }
                else {
                    pods_query("ALTER TABLE `@wp_pods_tbl_$params->dtname` CHANGE `$old_name` `$params->name` $dbtype");
                }
            }
            elseif ($params->name != $old_name && 'pick' != $params->type && 'file' != $params->type) {
                pods_query("ALTER TABLE `@wp_pods_tbl_$params->dtname` CHANGE `$old_name` `$params->name` $dbtype");
            }
            if (!isset($params->options) || empty($params->options)) {
                $options = $params->options;
                $exclude = array('id','pod_id','name','type','pick_object','pick_val','sister_field_id','weight','options');
                foreach ($exclude as $exclude_field) {
                    if (isset($options[$exclude_field]))
                        unset($options[$exclude_field]);
                }
                $params->options = '';
                if (!empty($options))
                    $params->options = $options;
            }
            if (!empty($params->options)) {
                $params->options = str_replace('@wp_', '{prefix}', json_encode($params->options));
            }
            pods_query("UPDATE `@wp_pods_fields` SET `name` = '{$params->name}', `label` = '{$params->label}', `type` = '{$params->type}', `pick_object` = '$params->pick_object}', `pick_val` = '{$params->pick_val}', `sister_field_id` = {$params->sister_field_id}, `options` = '{$params->options}' WHERE `id` = {$params->id} LIMIT 1", 'Cannot edit column');
        }
    }

    /**
     * Add or Edit a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     * $params['options'] Associative array of Object options
     *
     * @param array $params An associative array of parameters
     * @since 2.0.0
     */
    function save_object ($params) {
        $params = (object) $params;
        if (!isset($params->name) || empty($params->name))
            return pods_error('Name must be given to save an Object', $this);
        if (!isset($params->type) || empty($params->type))
            return pods_error('Type must be given to save an Object', $this);
        if (!isset($params->options) || empty($params->options)) {
            $options = get_object_vars($params);
            $exclude = array('id','name','type','options');
            foreach ($exclude as $exclude_field) {
                if (isset($options[$exclude_field]))
                    unset($options[$exclude_field]);
            }
            $params->options = '';
            if (!empty($options))
                $params->options = $options;
        }
        if (!empty($params->options)) {
            $params->options = str_replace('@wp_', '{prefix}', json_encode($params->options));
        }
        $params = pods_sanitize($params);
        if (isset($params->id) && !empty($params->id)) {
            $params->id = pods_absint($params->id);
            $result = pods_query("UPDATE `@wp_pods_objects` SET `name` = '{$params->name}', `type` = '{$params->type}', `options` = '{$params->options}' WHERE `id` = " . pods_absint($params->id));
            if (empty($result))
                return pods_error(ucwords($params->type).' Object not saved', $this);
            return $params->id;
        }
        else {
            $sql = "SELECT id FROM `@wp_pods_objects` WHERE `name` = '{$params->name}' LIMIT 1";
            $check = pods_query($sql, $this);
            if (!empty($check))
                return pods_error(ucwords($params->type) . " Object {$params->name} already exists", $this);
            $object_id = pods_query("INSERT INTO `@wp_pods_objects` (`name`, `type`, `options`) VALUES ('{$params->name}', '{$params->type}', '{$params->options}')");
            if (empty($object_id))
                return pods_error(ucwords($params->type).' Object not saved', $this);
            return $object_id;
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
    function save_template ($params) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->save_object($params);
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
    function save_page ($params) {
        $params = (object) $params;
        if (!isset($params->name)) {
            $params->name = $params->uri;
            unset($params->uri);
        }
        $params->name = trim($params->name, '/');
        $params->type = 'page';
        return $this->save_object($params);
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
    function save_helper ($params) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->save_object($params);
    }

    /**
     * Save the entire role structure
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function save_roles ($params) {
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
    function get_table_data ($params) {
        $params = is_array($params) ? $params : array();
        $defaults = array(
            'table' => 'types',
            'columns' => '*',
            'orderby' => '`id` ASC',
            'where' => 1,
            'array_key' => 'id');
        $params = (object) array_merge($defaults, $params);
        $result = pods_query("SELECT $params->columns FROM `@wp_pods_$params->table` WHERE $params->where ORDER BY $params->orderby", $this);
        $data = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                $data[$row->{$params->array_key}] = get_object_vars($row);
            }
        }
        return $data;
    }

    /**
     * Add or edit a single pod item
     *
     * $params['pod'] string The Pod name
     * $params['pod_id'] string The Pod name
     * $params['columns'] array (optional) Associative array of column names + values
     * $params['data'] array (optional) Associative array of a set of associative arrays of column names + values (for bulk operations)
     * $params['id'] int The item's ID from the wp_pod_tbl_* table (or alternatively use the pod_id parameter instead)
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     * @return int The item ID
     * @since 1.7.9
     */
    function save_pod_item ($params) {
        $params = (object) str_replace('@wp_', '{prefix}', pods_sanitize($params));

    // deprecated in 2.0
        if (isset($params->datatype)) {
            $params->pod = $params->datatype;
            if (isset($params->pod_id)) {
                $params->id = $params->pod_id;
                unset($params->pod_id);
            }
            if (isset($params->tbl_row_id)) {
                $params->id = $params->tbl_row_id;
                unset($params->tbl_row_id);
            }
        }

        if (!isset($params->pod))
            $params->pod = false;
        if (isset($params->pod_id))
            $params->pod_id = pods_absint($params->pod_id);
        else
            $params->pod_id = 0;

        if (isset($params->id))
            $params->id = pods_absint($params->id);
        else
            $params->id = 0;

        // support for multiple save_pod_item operations at the same time
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
        if (isset($params->id) && !empty($params->id) && is_array($params->id)) {
            $ids = array();
            $new_params = $params;
            foreach ($params->id as $id){
                $new_params->id = $id;
                $ids[] = $this->save_pod_item($new_params);
            }
            return $ids;
        }

        // Allow Helpers to know what's going on, are we adding or saving?
        $is_new_item = false;
        if (empty($params->id)) {
            $is_new_item = true;
        }

        // Allow Helpers to bypass subsequent helpers in recursive save_pod_item calls
        $bypass_helpers = false;
        if (isset($params->bypass_helpers) && false !== $params->bypass_helpers) {
            $bypass_helpers = true;
        }

        // Get array of Pods
        if (empty($this->pod_data) || ($this->pod != $params->pod && $this->pod_id != $params->pod_id))
            $this->pod_data = $this->load_pod(array('id' => $params->pod_id, 'name' => $params->pod));
        if (false === $this->pod_data)
            return pods_error("Pod not found", $this);
        $this->pod = $params->pod = $this->pod_data['name'];
        $this->pod_id = $params->pod_id = $this->pod_data['id'];
        $this->fields = $this->pod_data['fields'];
        $columns = $this->fields;
        $columns_active = array();

        // Find the active columns (loop through $params->columns to retain order)
        if (!empty($params->columns) && is_array($params->columns)) {
            foreach ($params->columns as $column => $value) {
                if (isset($columns[$column])) {
                    $columns[$column]['value'] = $value;
                    $columns_active[] = $column;
                }
            }
            unset($params->columns);
        }
    $active_columns =& $columns_active; // deprecated as of 2.0

        $pre_save_helpers = $post_save_helpers = array();
        $pre_create_helpers = $post_create_helpers = array();
        $pre_edit_helpers = $post_edit_helpers = array();

        if (!empty($this->pod_data['options']) && is_array($this->pod_data['options'])) {
            $helpers = array('pre_save_helpers', 'post_save_helpers',
                            'pre_create_helpers', 'post_create_helpers',
                            'pre_edit_helpers', 'post_edit_helpers');
            foreach ($helpers as $helper) {
                if (isset($this->pod_data['options'][$helper]) && !empty($this->pod_data['options'][$helper]))
                    ${$helper} = explode(',', $this->pod_data['options'][$helper]);
            }
        }

        // Plugin hook
        do_action('pods_pre_save_pod_item', $params, $columns, $this);
        do_action("pods_pre_save_pod_item_{$params->pod}", $params, $this);
        if (false !== $is_new_item) {
            do_action('pods_pre_create_pod_item', $params, $this);
            do_action("pods_pre_create_pod_item_{$params->pod}", $params, $this);
        }
        else {
            do_action('pods_pre_edit_pod_item', $params, $this);
            do_action("pods_pre_edit_pod_item_{$params->pod}", $params, $this);
        }

        // Call any pre-save helpers (if not bypassed)
        if (false === $bypass_helpers) {
            if (!empty($pre_save_helpers)) {
                foreach ($pre_save_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && !defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                        echo eval('?>' . $helper['code']);
                }
            }
            if (false !== $is_new_item && !empty($pre_create_helpers)) {
                foreach ($pre_create_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && !defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                        echo eval('?>' . $helper['code']);
                }
            }
            elseif (false === $is_new_item && !empty($pre_edit_helpers)) {
                foreach ($pre_edit_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && !defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                        echo eval('?>' . $helper['code']);
                }
            }
        }

        $table_data = $rel_columns = $rel_field_ids = array();

        // Loop through each active column, validating and preparing the table data
        foreach ($columns_active as $column) {
            $value = $columns[$column]['value'];
            $type = $columns[$column]['type'];
            $label = $columns[$column]['label'];
            $label = empty($label) ? $column : $label;

            // Validate value
            $value = $this->handle_column_validation($value, $column, $columns, $params);
            if (false === $value)
                return false;

            // Prepare all table (non-relational) data
            if (!in_array($type, array('pick', 'file')))
                $table_data[] = "`$column` = '$value'";
            // Store relational column data to be looped through later
            else {
                $rel_columns[$type][$column] = $value;
                $rel_field_ids[] = $columns[$column]['id'];
            }
        }

        // @to-do: Use REPLACE INTO instead and set defaults on created / modified / author
        // (if not submitted) but check if the fields exist on Pod first
        if (false !== $is_new_item) {
            $current_time = current_time('mysql');
            $author = 0;
            if (is_user_logged_in()) {
                global $user_ID;
                get_currentuserinfo();
                $author = pods_absint($user_ID);
            }
            $params->id = pods_query("INSERT INTO `@wp_pods_tbl_{$params->pod}` (`created`, `modified`, `author`) VALUES ('{$current_time}', '{$current_time}', {$author})", 'Cannot add new table row');
        }

        // Save the table row
        if (!empty($table_data)) {
            $table_data = implode(', ', $table_data);
            pods_query("UPDATE `@wp_pods_tbl_{$params->pod}` SET {$table_data} WHERE `id` = {$params->id} LIMIT 1");
        }

        // Save relational column data
        if (!empty($rel_columns)) {
            // E.g. $rel_columns['pick']['related_events'] = '3,15';
            foreach ($rel_columns as $type => $data) {
                foreach ($data as $column => $values) {
                    $field_id = pods_absint($columns[$column]['id']);

                    // Remove existing relationships
                    pods_query("DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->pod_id} AND `field_id` = {$field_id} AND `item_id` = {$params->id}", $this);

                    // Convert values from a comma-separated string into an array
                    if (!is_array($values))
                        $values = explode(',', $values);

                    // File relationships
                    if ('file' == $type) {
                        if (empty($values))
                            continue;
                        $weight = 0;
                        foreach ($values as $id) {
                            $id = pods_absint($id);
                            pods_query("INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_item_id`, `weight`) VALUES ({$params->pod_id}, {$field_id}, {$params->id}, {$id}, {$weight})");
                            $weight++;
                        }
                    }
                    // Pick relationships
                    elseif ('pick' == $type) {
                        $pick_object = $columns[$column]['pick_object']; // pod, post_type, taxonomy, etc..
                        $pick_val = $columns[$column]['pick_val']; // pod name, post type name, taxonomy name, etc..
                        $related_pod_id = $related_field_id = 0;
                        if ('pod' == $pick_object) {
                            $related_pod = $this->load_pod(array('name' => $pick_val));
                            if (false !== $related_pod)
                                $related_pod_id = $related_pod['id'];
                            if (0 < $columns[$column]['sister_field_id']) {
                                foreach ($related_pod['fields'] as $field) {
                                    if ('pick' == $field['type'] && $columns[$column]['sister_field_id'] == $field['id']) {
                                        $related_field_id = $field['id'];
                                        break;
                                    }
                                }
                            }
                        }

                        // Delete existing sister relationships
                        if (!empty($related_field_id) && !empty($related_pod_id) && in_array($related_field_id, $rel_field_ids)) {
                            pods_query("DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$related_pod_id} AND `field_id` = {$related_field_id} AND `related_pod_id` = {$params->pod_id} AND `related_field_id` = {$field_id} AND `related_item_id` = {$params->id}", $this);
                        }

                        if (empty($values))
                            continue;

                        // Add relationship values
                        $weight = 0;
                        foreach ($values as $id) {
                            if (!empty($related_pod_id) && !empty($related_field_id)) {
                                $related_weight = 0;
                                $result = pods_query("SELECT `weight` FROM `@wp_pods_rel` WHERE `pod_id` = {$related_pod_id} AND `field_id` = {$related_field_id} ORDER BY `weight` DESC LIMIT 1", $this);
                                if (!empty($result))
                                    $related_weight = pods_absint($result[0]->weight) + 1;
                                pods_query("INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES ({$related_pod_id}, {$related_field_id}, {$id}, {$params->pod_id}, {$field_id}, {$params->id}, {$related_weight}", 'Cannot add sister relationship');
                            }
                            pods_query("INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES ({$params->pod_id}, {$field_id}, {$params->id}, {$related_pod_id}, {$related_field_id}, {$id}, {$weight})", 'Cannot add relationship');
                            $weight++;
                        }
                    }
                }
            }
        }

        // Plugin hook
        do_action('pods_post_save_pod_item', $params, $columns, $this);
        do_action("pods_post_save_pod_item_{$params->pod}", $params, $columns, $this);
        if (false !== $is_new_item) {
            do_action('pods_post_create_pod_item', $params, $columns, $this);
            do_action("pods_post_create_pod_item_{$params->pod}", $params, $columns, $this);
        }
        else {
            do_action('pods_post_edit_pod_item', $params, $columns, $this);
            do_action("pods_post_edit_pod_item_{$params->pod}", $params, $columns, $this);
        }

        // Call any post-save helpers (if not bypassed)
        if (false === $bypass_helpers) {
            if (!empty($post_save_helpers)) {
                foreach ($post_save_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL))
                        echo eval('?>' . $helper['code']);
                }
            }
            if (false !== $is_new_item && !empty($post_create_helpers)) {
                foreach ($post_create_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL))
                        echo eval('?>' . $helper['code']);
                }
            }
            elseif (false === $is_new_item && !empty($post_edit_helpers)) {
                foreach ($post_edit_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL))
                        echo eval('?>' . $helper['code']);
                }
            }
        }

        // Success! Return the id
        return $params->id;
    }

    /**
     * Duplicate a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_tbl_* table
     *
     * @param array $params An associative array of parameters
     * @return int The table row ID
     * @since 1.12
     */
    function duplicate_pod_item ($params) {
        $params = (object) pods_sanitize($params);

        $id = false;
        $columns = $this->fields;
        if (empty($columns) || $this->pod != $params->pod) {
            $pod = $this->load_pod(array('name' => $params->pod));
            $columns = $pod['fields'];
            if (null === $this->pod) {
                $this->pod = $pod['name'];
                $this->pod_id = $pod['id'];
                $this->fields = $pod['fields'];
            }
        }
        $pod = pods($params->pod, $params->id);
        if (!empty($pod->data)) {
            $params = array('pod' => $params->pod,
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
                $value = $pod->field($field);
                if (0 < strlen($value))
                    $params['columns'][$column['name']] = $value;
            }
            $params = apply_filters('duplicate_pod_item', $params, $pod->pod, $pod->field('id'));
            $id = $this->save_pod_item($params);
        }
        return $id;
    }

    /**
     * Export a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_tbl_* table
     *
     * @param array $params An associative array of parameters
     * @return int The table row ID
     * @since 1.12
     */
    function export_pod_item ($params) {
        $params = (object) pods_sanitize($params);

        $data = false;
        $columns = $this->fields;
        if (empty($columns) || $this->pod != $params->pod) {
            $pod = $this->load_pod(array('name' => $params->pod));
            $columns = $pod['fields'];
            if (null === $this->pod) {
                $this->pod = $pod['name'];
                $this->pod_id = $pod['id'];
                $this->fields = $pod['fields'];
            }
        }
        $pod = pods($params->pod, $params->id);
        if (!empty($pod->data)) {
            $data = array();
            foreach ($columns as $column) {
                $value = $pod->field($column['name']);
                if (0 < strlen($value))
                    $data[$column['name']] = $value;
            }
            $data = apply_filters('export_pod_item', $data, $pod->pod, $pod->field('id'));
        }
        return $data;
    }

    /**
     * Reorder a Pod
     *
     * $params['pod'] string The Pod name
     * $params['field'] string The column name of the field to reorder
     * $params['order'] array The key => value array of items to reorder (key should be an integer)
     *
     * @param array $params An associative array of parameters
     * @since 1.9.0
     */
    function reorder_pod_item ($params) {
        $params = (object) pods_sanitize($params);

    // deprecated in 2.0
        if (isset($params->datatype)) {
            $params->pod = $params->datatype;
            unset($params->datatype);
        }

        if (!is_array($params->order))
            $params->order = explode(',', $params->order);
        foreach ($params->order as $order => $id) {
            pods_query("UPDATE `@wp_pods_tbl_{$params->pod}` SET `{$params->field}` = " . pods_absint($order) . " WHERE `id` = " . pods_absint($id) . " LIMIT 1");
        }
    }

    /**
     * Delete all content for a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     * @since 1.9.0
     */
    function reset_pod ($params) {
        $params = (object) pods_sanitize($params);

        $pod = $this->load_pod($params);
        if (false === $pod)
            return pods_error('Pod not found', $this);

        $params->id = $pod['id'];
        $params->name = $pod['name'];

        $field_ids = array();
        foreach ($pod['fields'] as $field) {
            $field_ids[] = $field['id'];
        }
        if (!empty($field_ids))
            pods_query("UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` IN (" . implode(',', $field_ids) . ")");

        if ('pod' == $pod['type']) {
            pods_query("TRUNCATE `@wp_pods_tbl_{$params->name}`");
        }
        pods_query("DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}");
    }

    /**
     * Drop a Pod and all its content
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_pod ($params) {
        $params = (object) pods_sanitize($params);

        $pod = $this->load_pod($params);
        if (false === $pod)
            return pods_error('Pod not found', $this);

        $params->id = $pod['id'];
        $params->name = $pod['name'];

        $field_ids = array();
        foreach ($pod['fields'] as $field) {
            $field_ids[] = $field['id'];
        }
        if (!empty($field_ids))
            pods_query("UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` IN (" . implode(',', $field_ids) . ")");

        if ('pod' == $pod['type']) {
            pods_query("DROP TABLE `@wp_pods_tbl_{$params->name}`");
            pods_query("UPDATE `@wp_pods_fields` SET `pick_val` = '' WHERE `pick_object` = 'pod' AND `pick_val` = '{$params->name}'");
        }
        pods_query("DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}");
        pods_query("DELETE FROM `@wp_pods_fields` WHERE `pod_id` = {$params->id}");
        pods_query("DELETE FROM `@wp_pods` WHERE `id` = {$params->id} LIMIT 1");
    }

    /**
     * Drop a column within a Pod
     *
     * $params['id'] int The column ID
     * $params['name'] int The column name
     * $params['pod'] string The Pod name
     * $params['pod_id'] string The Pod name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_column ($params) {
        $params = (object) pods_sanitize($params);

        if (!isset($params->pod))
            $params->pod = '';
        if (!isset($params->pod_id))
            $params->pod_id = 0;
        $pod = $this->load_pod(array('name' => $params->pod, 'id' => $params->pod_id));
        if (false === $pod)
            return pods_error('Pod not found', $this);

        $params->pod_id = $pod['id'];
        $params->pod = $pod['name'];

        if (!isset($params->name))
            $params->name = '';
        if (!isset($params->id))
            $params->id = 0;
        $column = $this->load_column(array('name' => $params->name, 'id' => $params->id));
        if (false === $column)
            return pods_error('Column not found', $this);

        $params->id = $column['id'];
        $params->name = $column['name'];

        if ('pod' == $pod['type'] && !in_array($column['type'], array('file', 'pick'))) {
            pods_query("ALTER TABLE `@wp_pods_tbl_{$params->pod}` DROP COLUMN `{$params->name}`");
        }

        pods_query("DELETE FROM `@wp_pods_rel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})");
        pods_query("DELETE FROM `@wp_pods_fields` WHERE `id` = {$params->id} LIMIT 1");
        pods_query("UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` = {$params->id}");
    }

    /**
     * Drop a Pod Object
     *
     * $params['id'] int The object ID
     * $params['name'] string The object name
     * $params['type'] string The object type
     *
     * @param array $params An associative array of parameters
     * @since 2.0.0
     */
    function drop_object ($params) {
        $params = (object) pods_sanitize($params);
        if (!isset($params->id) || empty($params->id)) {
            if (!isset($params->name) || empty($params->name))
                return pods_error('Name OR ID must be given to load an Object', $this);
            $where = "`name` = '{$params->name}'";
        }
        else
            $where = '`id` = ' . pods_absint($params->id);
        if (!isset($params->type) || empty($params->type))
            return pods_error('Type must be given to load an Object', $this);
        $result = pods_query("DELETE FROM `@wp_pods_objects` WHERE $where AND `type` = '{$params->type}' LIMIT 1", $this);
        if (empty($result))
            return pods_error(ucwords($params->type).' Object not found', $this);
        return true;
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
    function drop_template ($params) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->drop_object($params);
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
    function drop_page ($params) {
        $params = (object) $params;
        if (!isset($params->name)) {
            $params->name = $params->uri;
            unset($params->uri);
        }
        $params->name = trim($params->name, '/');
        $params->type = 'page';
        return $this->drop_object($params);
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
    function drop_helper ($params) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->drop_object($params);
    }

    /**
     * Drop a single pod item
     *
     * $params['id'] int (optional) The item's ID from the wp_pod_tbl_* table (used with datatype parameter)
     * $params['pod'] string (optional) The datatype name (used with id parameter)
     * $params['pod_id'] int (optional) The datatype ID (used with id parameter)
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function drop_pod_item ($params) {
        $params = (object) pods_sanitize($params);

        // deprecated in 2.0
        if (isset($params->datatype_id) || isset($params->datatype)) {
            if (isset($params->tbl_row_id)) {
                $params->id = $params->tbl_row_id;
                unset($params->tbl_row_id);
            }
            if (isset($params->pod_id)) {
                $params->id = $params->pod_id;
                unset($params->pod_id);
            }
            if (isset($params->dataype_id)) {
                $params->pod_id = $params->dataype_id;
                unset($params->dataype_id);
            }
            if (isset($params->datatype)) {
                $params->pod = $params->datatype;
                unset($params->datatype);
            }
        }

        $params->id = pods_absint($params->id);

        if (!isset($params->pod))
            $params->pod = '';
        if (!isset($params->pod_id))
            $params->pod_id = 0;
        $pod = $this->load_pod(array('name' => $params->pod, 'id' => $params->pod_id));
        if (false === $pod)
            return pods_error('Pod not found', $this);

        $params->pod_id = $pod['id'];
        $params->pod = $pod['name'];

        // Allow Helpers to bypass subsequent helpers in recursive drop_pod_item calls
        $bypass_helpers = false;
        if (isset($params->bypass_helpers) && false !== $params->bypass_helpers) {
            $bypass_helpers = true;
        }

        $pre_drop_helpers = $post_drop_helpers = array();

        if (!empty($pod['options']) && is_array($pod['options'])) {
            $helpers = array('pre_drop_helpers','post_drop_helpers');
            foreach ($helpers as $helper) {
                if (isset($pod['options'][$helper]) && !empty($pod['options'][$helper]))
                    ${$helper} = explode(',', $pod['options'][$helper]);
            }
        }

        // Plugin hook
        do_action('pods_pre_drop_pod_item', $params, $this);
        do_action("pods_pre_drop_pod_item_{$params->pod}", $params, $this);

        // Call any pre-save helpers (if not bypassed)
        if (false === $bypass_helpers) {
            if (!empty($pre_drop_helpers)) {
                foreach ($pre_drop_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL))
                        echo eval('?>' . $helper['code']);
                }
            }
        }

        if ('pod' == $pod['type'])
            pods_query("DELETE FROM `@wp_pods_tbl_{$params->datatype}` WHERE `id` = {$params->id} LIMIT 1");
        pods_query("DELETE FROM `@wp_pods_rel` WHERE (`pod_id` = {$params->pod_id} AND `item_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_item_id` = {$params->id})");

        // Plugin hook
        do_action('pods_post_drop_pod_item', $params, $this);
        do_action("pods_post_drop_pod_item_{$params->pod}", $params, $this);

        // Call any post-save helpers (if not bypassed)
        if (false === $bypass_helpers) {
            if (!empty($post_drop_helpers)) {
                foreach ($post_drop_helpers as $helper) {
                    $helper = $this->load_helper(array('name' => $helper));
                    if (false !== $helper && (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL))
                        echo eval('?>' . $helper['code']);
                }
            }
        }
    }

    function handle_options ($options, $pod) {
        // setup default array
        $default = array('is_toplevel' => 0, 'label' => '');
        $options = array_merge($default, $options);
        return apply_filters('pods_api_pod_options', $options, $pod);
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
    function pod_exists ($params) {
        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $params = (object) $params;
        if (!empty($params->id) || !empty($params->name)) {
            $where = empty($params->id) ? "name = '{$params->name}'" : "id = {$params->id}";
            $result = pods_query("SELECT id, name FROM @wp_pod_types WHERE {$where} LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $pod = mysql_fetch_assoc($result);
                return $pod;
            }
        }
        return false;
    }

    /**
     * Load a Pod and all of its fields
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_pod ($params) {
        $params = (object) pods_sanitize($params);
        if ((!isset($params->id) || empty($params->id)) && (!isset($params->name) || empty($params->name)))
            return pods_error('Either Pod ID or Name are required', $this);
        $where = empty($params->id) ? "`name` = '$params->name'" : "`id` = " . pods_absint($params->id);
        $result = pods_query("SELECT * FROM `@wp_pods` WHERE {$where} LIMIT 1", $this);
        if (empty($result))
            return pods_error('Pod not found', $this);
        $pod = get_object_vars($result[0]);
        if (!empty($pod['options']))
            $pod['options'] = @json_decode($pod['options'],true);
        $pod['options'] = $this->handle_options($pod['options'], $pod);
        $pod['fields'] = array();
        $result = pods_query("SELECT * FROM `@wp_pods_fields` WHERE pod_id = {$pod['id']} ORDER BY weight");
        if (!empty($result)) {
            foreach ($result as $row) {
                $pod['fields'][$row->name] = get_object_vars($row);
            }
        }
        return $pod;
    }

    /**
     * Load Pods and filter by options
     *
     * $params['type'] string/array Pod Type(s) to filter by
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of Pods to return
     *
     * @param array $params An associative array of parameters
     * @since 2.0.0
     */
    function load_pods ($params) {
        $params = (object) pods_sanitize($params);
        $where = $orderby = $limit = '';
        if (isset($params->type) && !empty($params->type)) {
            if (!is_array($params->type))
                $params->type = array($params->type);
            $where .= " `type` IN (" . implode("','", $params->type) . ") ";
        }
        if (isset($params->options) && !empty($params->options) && is_array($params->options)) {
            $options = array();
            foreach ($params->options as $option => $value) {
                $options[] = pods_sanitize(trim(json_encode(array($option => $value)), '{} []'));
            }
            if (!empty($options))
                $where .= ' (`options` LIKE "%' . implode('%" AND `options` LIKE "%', $options) . '%")';
        }
        if (!empty($where))
            $where = " WHERE {$where} ";
        if (isset($params->orderby) && !empty($params->orderby))
            $orderby = " ORDER BY {$params->orderby} ";
        if (isset($params->limit) && !empty($params->limit)) {
            $params->limit = pods_absint($params->limit);
            $limit = " LIMIT {$params->limit} ";
        }
        $result = pods_query("SELECT * FROM `@wp_pods` {$where} {$orderby} {$limit}", $this);
        if (empty($result))
            return false;
        $the_pods = array();
        foreach ($result as $row) {
            $pod = get_object_vars($row);
            if (!empty($pod['options']))
                $pod['options'] = @json_decode($pod['options'],true);
            $pod['options'] = $this->handle_options($pod['options'], $pod);
            $the_pods = $pod;
        }
        return $the_pods;
    }

    /**
     * Load a column
     *
     * $params['pod_id'] int The Pod ID
     * $params['id'] int The field ID
     * $params['name'] string The field name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_column ($params) {
        $params = (object) pods_sanitize($params);
        if (empty($params->id)) {
            if (empty($params->name))
                return pods_error('Column name is required', $this);
            if (empty($params->pod_id))
                return pods_error('Pod ID is required', $this);
            $where = "`pod_id` = " . pods_absint($params->pod_id) . " AND `name` = '" . $params->name . "'";
        }
        else {
            $where = '`id` = ' . pods_absint($params->id);
        }
        $result = pods_query("SELECT * FROM `@wp_pods_fields` WHERE {$where} LIMIT 1", $this);
        if (empty($result))
            return pods_error('Column not found', $this);
        $column = get_object_vars($result[0]);
        if (!empty($column['options']))
            $column['options'] = @json_decode($column['options'],true);
        return $column;
    }

    /**
     * Load a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     *
     * @param array $params An associative array of parameters
     * @since 2.0.0
     */
    function load_object ($params) {
        $params = (object) pods_sanitize($params);
        if (!isset($params->id) || empty($params->id)) {
            if (!isset($params->name) || empty($params->name))
                return pods_error('Name OR ID must be given to load an Object', $this);
            $where = "name = '{$params->name}'";
        }
        else
            $where = 'id = ' . pods_absint($params->id);
        if (!isset($params->type) || empty($params->type))
            return pods_error('Type must be given to load an Object', $this);
        $result = pods_query("SELECT * FROM `@wp_pods_objects` WHERE $where `type` = '{$params->type}' LIMIT 1", $this);
        if (empty($result))
            return pods_error(ucwords($params->type).' Object not found', $this);
        return get_object_vars($result[0]);
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
    function load_template ($params) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->load_object($params);
    }

    /**
     * Load a Pod Page
     *
     * $params['id'] int The page ID
     * $params['name'] string The page URI
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_page ($params) {
        $params = (object) $params;
        if (!isset($params->name) && isset($params->uri)) {
            $params->name = $params->uri;
            unset($params->uri);
        }
        $params->type = 'page';
        return $this->load_object($params);
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
    function load_helper ($params) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->load_object($params);
    }

    /**
     * Load the pod item object
     *
     * $params['pod'] string The datatype name
     * $params['id'] int (optional) The item's ID
     *
     * @param array $params An associative array of parameters
     * @since 2.0.0
     */
    function load_pod_item ($params) {
        $params = (object) pods_sanitize($params);

        if (!isset($params->pod) || empty($params->pod))
            return pods_error('Pod name required', $this);
        if (!isset($params->id) || empty($params->id))
            return pods_error('Item ID required', $this);

        return new Pod($params->datatype, $params->id);
    }

    /**
     * Load a bi-directional (sister) column
     *
     * $params['pod'] int The Pod name
     * $params['related_pod'] string The related Pod name
     *
     * @param array $params An associative array of parameters
     * @since 1.7.9
     */
    function load_sister_fields ($params) {
        $params = (object) pods_sanitize($params);

        $pod = $this->load_pod(array('name' => $params->pod));
        if (false === $pod)
            return pods_error('Pod not found', $this);

        $params->pod_id = $pod['id'];
        $params->pod = $pod['name'];

        $related_pod = $this->load_pod(array('name' => $params->related_pod));
        if (false === $pod)
            return pods_error('Related Pod not found', $this);

        $params->related_pod_id = $related_pod['id'];
        $params->related_pod = $related_pod['name'];

        if ('pod' == $related_pod['type']) {
            $sister_fields = array();
            foreach ($related_pod['fields'] as $column) {
                if ('pick' == $column['type'] && $params->pod == $column['pick_val']) {
                    $sister_fields[] = $column;
                }
            }
            return $sister_fields;
        }
        return false;
    }

    function load_column_types () {
        $columns = array('bool' => 'BOOL DEFAULT 0',
                        'date' => "DATETIME NOT NULL default '0000-00-00 00:00:00'",
                        'num' => 'DECIMAL(12,2)',
                        'txt' => 'VARCHAR(255)',
                        'slug' => 'VARCHAR(200)',
                        'code' => 'LONGTEXT',
                        'desc' => 'LONGTEXT');
        $columns = apply_filters('pods_column_dbtypes', $columns, $this);
        return $columns;
    }

    function get_column_definition ($type, $options = null) {
        $column_types = $this->load_column_types();
        $definition = $columns[$type];
        if (!empty($options) && is_array($options)) {
            // handle options and change definition where needed
        }
        $definition = apply_filters('pods_column_definition', $definition, $column_types, $type, $options, $this);
        return $definition;
    }

    function handle_column_validation ($value, $column, $columns, $params) {
        $type = $columns[$column]['type'];

        // Verify slug columns
        if ('slug' == $type) {
            if (empty($value) && isset($columns['name']['value']))
                $value = $columns['name']['value'];
            if (!empty($value))
                $value = pods_unique_slug($value, $column, $params->pod, $params->pod_id, $params->id, $this);
        }
        // Verify required fields
        if (1 == $columns[$column]['required']) {
            if ('' == $value || null == $value)
                return pods_error("{$label} is empty", $this);
            elseif ('num' == $type && !is_numeric($value))
                return pods_error("{$label} is not numeric", $this);
        }
        // Verify unique fields
        if (1 == $columns[$column]['unique']) {
            if (!in_array($type, array('pick', 'file'))) {
                $exclude = '';
                if (!empty($params->id))
                    $exclude = "AND `id` != {$params->id}";

                // Trigger an error if not unique
                $check = pods_query("SELECT `id` FROM `@wp_pods_tbl_{$params->pod}` WHERE `{$column}` = '{$value}' {$exclude} LIMIT 1", $this);
                if (!empty($check))
                    return pods_error("$label needs to be unique", $this);
            }
            else {
                // handle rel check
            }
        }
        $value = apply_filters('pods_column_validation', $value, $column, $columns, $this);
        return $value;
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
    function export_package ($params) {
        $export = array(
            'meta' => array(
                'version' => PODS_VERSION,
                'build' => date('U'),
            )
        );

        $pod_ids = $params['pods'];
        $template_ids = $params['templates'];
        $page_ids = $params['pages'];
        $helper_ids = $params['helpers'];

        if (!empty($pod_ids)) {
            $pod_ids = explode(',', $pod_ids);
            foreach ($pod_ids as $pod_id) {
                $export['pods'][$pod_id] = $this->load_pod(array('id' => $pod_id));
            }
        }
        if (!empty($template_ids)) {
            $template_ids = explode(',', $template_ids);
            foreach ($template_ids as $template_id) {
                $export['templates'][$template_id] = $this->load_template(array('id' => $template_id));
            }
        }
        if (!empty($page_ids)) {
            $page_ids = explode(',', $page_ids);
            foreach ($page_ids as $page_id) {
                $export['pod_pages'][$page_id] = $this->load_page(array('id' => $page_id));
            }
        }
        if (!empty($helper_ids)) {
            $helper_ids = explode(',', $helper_ids);
            foreach ($helper_ids as $helper_id) {
                $export['helpers'][$helper_id] = $this->load_helper(array('id' => $helper_id));
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
    function replace_package ($data = false) {
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
    function import_package ($data = false, $replace = false) {
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
                $dt = pods_query("INSERT INTO @wp_pod_types (`$pod_columns`) VALUES ('$values')");

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
                pods_query("INSERT INTO @wp_pod_fields (`$field_columns`) VALUES ('$tupples')");

                // Create the actual table with any non-PICK columns
                $definitions = array("id INT unsigned auto_increment primary key");
                foreach ($table_columns as $colname => $coltype) {
                    $definitions[] = "`$colname` {$dbtypes[$coltype]}";
                }
                $definitions = implode(',', $definitions);
                pods_query("CREATE TABLE @wp_pod_tbl_{$pod['name']} ($definitions)");
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
    function validate_package ($data = false, $output = false) {
        if (is_array($data) && isset($data['data'])) {
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
    function import ($data, $numeric_mode = false) {
        global $wpdb;
        if ('csv' == $this->format) {
            $data = $this->csv_to_php($data);
        }

        pods_query("SET NAMES utf8");
        pods_query("SET CHARACTER SET utf8");

        // Loop through the array of items
        $ids = array();

        // Test to see if it's an array of arrays
        if (!is_array(@current($data)))
            $data = array($data);

        foreach ($data as $key => $data_row) {
            $columns = array();

            // Loop through each field (use $this->fields so only valid columns get parsed)
            foreach ($this->fields as $field_name => $field_data) {
                $field_id = $field_data['id'];
                $type = $field_data['type'];
                $pickval = $field_data['pickval'];
                $field_value = $data_row[$field_name];

                if (null != $field_value && false !== $field_value) {
                    if ('pick' == $type || 'file' == $type) {
                        $field_values = is_array($field_value) ? $field_value : array($field_value);
                        $pick_values = array();
                        foreach ($field_values as $pick_value) {
                            if ('file' == $type) {
                                $where = "`guid` = '" . pods_sanitize($pick_value) . "'";
                                if (0 < pods_absint($pick_value) && false !== $numeric_mode)
                                    $where = "`ID` = " . pods_absint($pick_value);
                                $result = pods_query("SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = 'attachment' AND {$where} ORDER BY `ID`", $this);
                                if (!empty($result))
                                    $pick_values[$field_name] = $result['id'];
                            }
                            elseif ('pick' == $type) {
                                if ('wp_taxonomy' == $pickval) {
                                    $where = "`name` = '" . pods_sanitize($pick_value) . "'";
                                    if (0 < pods_absint($pick_value) && false !== $numeric_mode)
                                        $where = "`term_id` = " . pods_absint($pick_value);
                                    $result = pods_query("SELECT `term_id` AS `id` FROM `{$wpdb->terms}` WHERE {$where} ORDER BY `term_id`", $this);
                                    if (!empty($result))
                                        $pick_values[$field_name] = $result['id'];
                                }
                                elseif ('wp_page' == $pickval || 'wp_post' == $pickval) {
                                    $pickval = str_replace('wp_', '', $pickval);
                                    $where = "`post_title` = '" . pods_sanitize($pick_value) . "'";
                                    if (0 < pods_absint($pick_value) && false !== $numeric_mode)
                                        $where = "`ID` = " . pods_absint($pick_value);
                                    $result = pods_query("SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = '$pickval' AND {$where} ORDER BY `ID`", $this);
                                    if (!empty($result))
                                        $pick_values[$field_name] = $result['id'];
                                }
                                elseif ('wp_user' == $pickval) {
                                    $where = "`display_name` = '" . pods_sanitize($pick_value) . "'";
                                    if (0 < pods_absint($pick_value) && false !== $numeric_mode)
                                        $where = "`ID` = " . pods_absint($pick_value);
                                    $result = pods_query("SELECT `ID` AS `id` FROM `{$wpdb->users}` WHERE {$where} ORDER BY `ID`", $this);
                                    if (!empty($result))
                                        $pick_values[$field_name] = $result['id'];
                                }
                                else {
                                    $where = "`name` = '" . pods_sanitize($pick_value) . "'";
                                    if (0 < pods_absint($pick_value) && false !== $numeric_mode)
                                        $where = "`id` = " . pods_absint($pick_value);
                                    $result = pods_query("SELECT `id` FROM `@wp_pods_tbl_{$pickval}` WHERE {$where} ORDER BY `id`", $this);
                                    if (!empty($result))
                                        $pick_values[$field_name] = $result['id'];
                                }
                            }
                        }
                        $field_value = implode(',', $pick_values);
                    }
                    $columns[$field_name] = pods_sanitize($field_value);
                }
            }
            if (!empty($columns)) {
                $params = array('pod' => $this->pod,
                                'columns' => $columns);
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
    function export () {
        global $wpdb;
        $data = array();
        $fields = array();
        $pick_values = array();

        $pod = pods($this->pod, array('limit' => -1,'search' => false,'pagination' => false));
        while($pod->fetch()) {
            $data[$pod->field('id')] = $this->export_pod_item($pod->field('id'));
        }
        return $data;
    }

    /**
     * Convert CSV to a PHP array
     *
     * @param string $data The CSV input
     * @since 1.7.1
     */
    function csv_to_php ($data) {
        $delimiter = ",";
        $expr = "/{$delimiter}(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
        $data = str_replace("\r\n", "\n", $data);
        $data = str_replace("\r", "\n", $data);
        $lines = explode("\n", $data);
        $field_names = explode($delimiter, array_shift($lines));
        $field_names = preg_replace("/^\"(.*)\"$/s", "$1", $field_names);
        $out = array();
        foreach ($lines as $line) {
            // Skip the empty line
            if (empty($line))
                continue;
            $row = array();
            $fields = preg_split($expr, trim($line));
            $fields = preg_replace("/^\"(.*)\"$/s", "$1", $fields);
            foreach ($field_names as $key => $field) {
                $row[$field] = $fields[$key];
            }
            $out[] = $row;
        }
        return $out;
    }
}