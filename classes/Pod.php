<?php
class Pod
{
    var $id;
    var $sql;
    var $raw_sql;
    var $data;
    var $row_number = -1;
    var $zebra = false;
    var $result;
    var $datatype;
    var $datatype_id;
    var $calc_found_rows = true;
    var $count_found_rows = false;
    var $total;
    var $total_rows;
    var $detail_page;
    var $rpp = 15;
    var $page_var = 'pg';
    var $page = 1;
    var $pagination = true;
    var $search = true;
    var $search_var = 'search';
    var $search_where = '';
    var $search_mode = 'int'; // int | text | text_like

    var $traverse = array();
    var $rabit_hole = array();

    /**
     * Constructor - Pods CMS core
     *
     * @param string $datatype The pod name
     * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run findRecords immediately
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.0.0
     */
    function __construct($datatype = null, $id = null) {
        $datatype = pods_sanitize($datatype);

        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE) {
            $this->page = 1;
            $this->pagination = false;
            $this->search = false;
        }
        else {
            // Get the page variable
            $this->page = pods_var($this->page_var, 'get');
            $this->page = empty($this->page) ? 1 : max((int) $this->page, 1);
            if (defined('PODS_GLOBAL_POD_PAGINATION') && !PODS_GLOBAL_POD_PAGINATION) {
                $this->page = 1;
                $this->pagination = false;
            }

            if (defined('PODS_GLOBAL_POD_SEARCH') && !PODS_GLOBAL_POD_SEARCH)
                $this->search = false;
            if (defined('PODS_GLOBAL_POD_SEARCH_MODE') && in_array(PODS_GLOBAL_POD_SEARCH_MODE, array('int', 'text', 'text_like')))
                $this->search_mode = PODS_GLOBAL_POD_SEARCH_MODE;
        }

        if (!empty($datatype)) {
            $result = pod_query("SELECT id, name, detail_page FROM @wp_pod_types WHERE name = '$datatype' LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $row = mysql_fetch_assoc($result);
                $this->datatype = $row['name'];
                $this->datatype_id = $row['id'];
                $this->detail_page = $row['detail_page'];

                if (null !== $id) {
                    if (is_array($id) || is_object($id))
                        $this->findRecords($id);
                    else {
                        $this->getRecordById($id);
                        if (!empty($this->data))
                            $this->id = $this->get_field('id');
                    }
                }
            }
            else
                echo "<e>Error: Pod '{$datatype}' does not exist</e>";
        }
    }

    /**
     * Fetch a row of results from the DB
     *
     * @since 1.2.0
     */
    function fetchRecord() {
        if ($this->data = mysql_fetch_assoc($this->result)) {
            $this->row_number++;
            if (true === $this->zebra)
                $this->zebra = false;
            else
                $this->zebra = true;
            return $this->data;
        }
        return false;
    }

    /**
     * Return a field's value(s)
     *
     * @param string $name The field name
     * @param string $orderby (optional) The orderby string, for PICK fields
     * @since 1.2.0
     */
    function get_field($name, $orderby = null) {
        if (empty($this->data) && empty($this->datatype_id)) {
            echo "<e>Error: Pod name invalid, no data available</e>";
            return null;
        }
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        elseif ('created' == $name || 'modified' == $name) {
            $pod_id = (int) $this->get_pod_id();
            if (!empty($pod_id))
                $result = pod_query("SELECT id, created, modified FROM @wp_pod WHERE id = {$pod_id} LIMIT 1");
            else {
                $tbl_row_id = (int) $this->get_field('id');
                if (!empty($tbl_row_id))
                    $result = pod_query("SELECT id, created, modified FROM @wp_pod WHERE tbl_row_id = {$tbl_row_id} LIMIT 1");
                else
                    return;
            }
            $row = mysql_fetch_assoc($result);
            $this->data['pod_id'] = $row['pod_id'];
            $this->data['created'] = $row['created'];
            $this->data['modified'] = $row['modified'];
            return $this->data[$name];
        }
        elseif ('detail_url' == $name) {
            return $this->parse_magic_tags(array('', '', 'detail_url'));
        }
        else {
            // Dot-traversal
            $last_loop = false;
            $datatype_id = $this->datatype_id;
            $tbl_row_ids = $this->data['id'];

            $traverse = (false !== strpos($name, '.')) ? explode('.', $name) : array($name);
            $traverse_fields = implode("','", $traverse);

            // Get columns matching traversal names
            $result = pod_query("SELECT id, datatype, name, coltype, pickval FROM @wp_pod_fields WHERE name IN ('$traverse_fields')");
            if (0 < mysql_num_rows($result)) {
                while ($row = mysql_fetch_assoc($result)) {
                    $all_fields[$row['datatype']][$row['name']] = $row;
                }
            }
            // No matching columns
            else {
                return false;
            }

            $last_coltype = $last_pickval = '';
            // Loop through each traversal level
            foreach ($traverse as $key => $column_name) {
                $last_loop = (1 < count($traverse) - $key) ? false : true;
                $column_exists = isset($all_fields[$datatype_id][$column_name]);

                if ($column_exists) {
                    $col = $all_fields[$datatype_id][$column_name];
                    $field_id = $col['id'];
                    $coltype = $col['coltype'];
                    $pickval = $col['pickval'];

                    if ('pick' == $coltype || 'file' == $coltype) {
                        $last_coltype = $coltype;
                        $last_pickval = $pickval;
                        $tbl_row_ids = $this->lookup_row_ids($field_id, $datatype_id, $tbl_row_ids);

                        if (false === $tbl_row_ids) {
                            return false;
                        }

                        // Get datatype ID for non-WP PICK columns
                        if (
                            !empty($pickval) &&
                            !in_array($pickval, array('wp_taxonomy', 'wp_post', 'wp_page', 'wp_user')))
                        {
                            $result = pod_query("SELECT id FROM @wp_pod_types WHERE name = '$pickval' LIMIT 1");
                            $datatype_id = mysql_result($result, 0);
                        }
                    }
                    else {
                        $last_loop = true;
                    }
                }
                // Assume last iteration
                else {
                    // Invalid column name
                    if (0 == $key) {
                        return false;
                    }
                    $last_loop = true;
                }

                if ($last_loop) {
                    $table = ('file' == $last_coltype) ? 'file' : $last_pickval;

                    if (!empty($table)) {
                        $data = $this->rel_lookup($tbl_row_ids, $table, $orderby);
                    }

                    if (empty($data)) {
                        $results = false;
                    }
                    // Return entire array
                    elseif (false !== $column_exists && ('pick' == $coltype || 'file' == $coltype)) {
                        $results = $data;
                    }
                    // Return a single column value
                    elseif (1 == count($data)) {
                        $results = $data[0][$column_name];
                    }
                    // Return an array of single column values
                    else {
                        foreach ($data as $key => $val) {
                            $results[] = $val[$column_name];
                        }
                    }
                    return $results;
                }
            }
        }
    }

    /**
     * Find items related to a parent field
     *
     * @param int $field_id The field ID
     * @param int $datatype_id The datatype ID
     * @param mixed $tbl_row_ids A comma-separated string (or array) of row IDs
     * @since 1.2.0
     */
    function lookup_row_ids($field_id, $datatype_id, $tbl_row_ids) {
        if (empty($tbl_row_ids))
            $tbl_row_ids = 0;
        else {
            if (!is_array($tbl_row_ids))
                $tbl_row_ids = explode(',', $tbl_row_ids);
            foreach ($tbl_row_ids as $key => $id) {
                $tbl_row_ids[$key] = (int) $id;
            }
            $tbl_row_ids = implode(',', $tbl_row_ids);
        }

        $field_id = (int) $field_id;
        $datatype_id = (int) $datatype_id;

        $sql = "
        SELECT
            `r`.`tbl_row_id`
        FROM
            `@wp_pod` AS `p`
        INNER JOIN
            `@wp_pod_rel` AS `r` ON `r`.`field_id` = {$field_id} AND `r`.`pod_id` = `p`.`id`
        WHERE
            `p`.`datatype` = {$datatype_id} AND `p`.`tbl_row_id` IN ($tbl_row_ids)
        ORDER BY
            `r`.`weight`
        ";
        $result = pod_query($sql);
        if (0 < mysql_num_rows($result)) {
            while ($row = mysql_fetch_assoc($result)) {
                $out[] = $row['tbl_row_id'];
            }
            return implode(',', $out);
        }
        return false;
    }

    /**
     * Lookup values from a single relationship field
     *
     * @param mixed $tbl_row_ids A comma-separated string of row IDs
     * @param string $table The database table alias
     * @param string $orderby (optional) MySQL "ORDER BY" clause
     * @return array $data Associative array of table column values
     * @since 1.2.0
     */
    function rel_lookup($tbl_row_ids, $table, $orderby = null) {
        global $wpdb;
        $orderby = empty($orderby) ? '' : "ORDER BY $orderby";

        // WP taxonomy item
        if ('wp_taxonomy' == $table) {
            $result = pod_query("SELECT * FROM `$wpdb->terms` WHERE `term_id` IN ($tbl_row_ids) $orderby");
        }
        // WP page, post, or attachment
        elseif ('wp_page' == $table || 'wp_post' == $table || 'file' == $table) {
            $result = pod_query("SELECT * FROM `$wpdb->posts` WHERE `ID` IN ($tbl_row_ids) $orderby");
        }
        // WP user
        elseif ('wp_user' == $table) {
            $result = pod_query("SELECT * FROM `$wpdb->users` WHERE `ID` IN ($tbl_row_ids) $orderby");
        }
        // Pod table
        else {
            $result = pod_query("SELECT * FROM `@wp_pod_tbl_$table` WHERE `id` IN ($tbl_row_ids) $orderby");
        }

        //override with custom relational query
        $result = apply_filters('pods_rel_lookup', $result, $tbl_row_ids, $table, $orderby, $this);

        // Put all related items into an array
        if (!is_array($result)) {
	    while ($row = mysql_fetch_assoc($result)) {
                $data[] = $row;
	    }
        }
	else {
	    $data = $result;
	}

        return $data;
    }

    /**
     * Get the current item's pod ID from its datatype ID and tbl_row_id
     *
     * @todo pod_id should NEVER be needed by users - fix code so tbl_row_id is used instead
     * @return int The ID from the wp_pod table
     * @since 1.2.0
     */
    function get_pod_id() {
        if (empty($this->data) && empty($this->datatype_id)) {
            echo "<e>Error: Pod name invalid, no data available</e>";
            return null;
        }
        elseif (empty($this->data))
            return 0;
        elseif ((!isset($this->data['pod_id']) ||empty($this->data['pod_id'])) && isset($this->data['id']) && 0 < $this->data['id']) {
            $this->data['pod_id'] = 0;
            $tbl_row_id = (isset($this->data['id']) ? (int) $this->data['id'] : 0);
            $result = pod_query("SELECT `id` FROM `@wp_pod` WHERE `datatype` = '$this->datatype_id' AND `tbl_row_id` = '$tbl_row_id' LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $this->data['pod_id'] = mysql_result($result, 0);
            }
        }
        return $this->data['pod_id'];
    }

    /**
     * Set a custom data value (no database changes)
     *
     * @param string $name The field name
     * @param mixed $data The value to set
     * @return mixed The value of $data
     * @since 1.2.0
     */
    function set_field($name, $data) {
        return $this->data[$name] = $data;
    }

    /**
     * Run a helper within a Pod Page or WP Template or Magic Tags
     *
     * @param string $helper The helper name
     * @return mixed Anything returned by the helper
     * @since 1.2.0
     */
    function pod_helper($helper, $value = null, $name = null) {
        ob_start();

        do_action('pods_pre_pod_helper', $helper, $value, $name, $this);
        do_action("pods_pre_pod_helper_{$helper}", $helper, $value, $name, $this);

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
            $params = array('name' => $helper, 'type' => 'display');
            if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                $params = pods_sanitize($params);
            $content = $api->load_helper($params);
            if (false !== $content && 0 < strlen(trim($content['phpcode'])))
                $content = $content['phpcode'];
            else
                $content = false;
        }

        if (false === $content && false !== $function_or_file && isset($function_or_file['function']))
            echo $function_or_file['function']($value, $name, $this);
        elseif (false === $content && false !== $function_or_file && isset($function_or_file['file']))
            locate_template($function_or_file['file'], true, true);
        elseif (false !== $content) {
            if (!defined('PODS_DISABLE_EVAL') || !PODS_DISABLE_EVAL)
                eval("?>$content");
            else
                echo $content;
        }

        //post-helper hooks
        do_action('pods_post_pod_helper', $helper, $value, $name, $this);
        do_action("pods_post_pod_helper_{$helper}", $helper, $value, $name, $this);

        return apply_filters('pods_helper', ob_get_clean(), $helper, $value, $this);
    }

    /**
     * Get pod or category dropdown values
     */
    function get_dropdown_values($params) {
        global $wpdb;

        $params = (object) $params;

        $params->orderby = empty($params->pick_orderby) ? '`name` ASC' : $params->pick_orderby;

        // WP taxonomy dropdown
        if ('wp_taxonomy' == $params->table) {
            $where = (false !== $params->unique_vals) ? "WHERE `t`.term_id NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= (empty($where) ? ' WHERE ' : ' AND ') . $params->pick_filter;
            }

            $sql = "SELECT `t`.term_id AS id, `t`.`name` FROM `{$wpdb->term_taxonomy}` AS `tx` INNER JOIN `{$wpdb->terms}` AS `t` ON `t`.`term_id` = `tx`.`term_id` {$where} ORDER BY {$params->orderby}";
        }
        // WP page or post dropdown
        elseif ('wp_page' == $params->table || 'wp_post' == $params->table) {
            $post_type = substr($params->table, 3);
            $where = (false !== $params->unique_vals) ? "AND `id` NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= " AND {$params->pick_filter}";
            }

            $sql = "SELECT `t`.ID AS id, `t`.post_title AS `name` FROM `{$wpdb->posts}` AS `t` WHERE `t`.`post_type` = '{$post_type}' {$where} ORDER BY {$params->orderby}";
        }
        // WP user dropdown
        elseif ('wp_user' == $params->table) {
            $where = (false !== $params->unique_vals) ? "WHERE `id` NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= (empty($where) ? ' WHERE ' : ' AND ') . $params->pick_filter;
            }

            $sql = "SELECT `t`.`ID` AS `id`, `t`.`display_name` AS `name` FROM `{$wpdb->users}` AS `t` {$where} ORDER BY {$params->orderby}";
        }
        // Pod table dropdown
        else {
            $where = (false !== $params->unique_vals) ? "WHERE id NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= (empty($where) ? ' WHERE ' : ' AND ') . $params->pick_filter;
            }

            $sql = "SELECT * FROM `@wp_pod_tbl_{$params->table}` AS `t` {$where} ORDER BY {$params->orderby}";
        }

        //override with custom dropdown values
        $sql = apply_filters('pods_get_dropdown_values', $sql, $params, $this);

        $val = array();
        $result = pod_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $row['active'] = false;
            if (!empty($params->tbl_row_ids)) {
                $row['active'] = in_array($row['id'], $params->tbl_row_ids);
            }
            elseif (isset($_GET[$params->field_name])) {
                if ('int' == $this->search_mode && $row['id'] == (int) $_GET[$params->field_name])
                    $row['active'] = true;
                elseif ('text' == $this->search_mode && $row['name'] == $_GET[$params->field_name])
                    $row['active'] = true;
            }
            $val[] = $row;
        }
        return $val;
    }

    /**
     * Setup fields for rabit hole
     */
    function feed_rabit ($fields) {
        $feed = array();
        foreach ($fields as $field => $data) {
            if (!is_array($data))
                $field = $data;
            if (isset($_GET[$field]))
                $feed['traverse_' . $field] = array($field);
        }
        return $feed;
    }

    /**
     * Recursively join tables based on fields
     */
    function recurse_rabit_hole ($pod, $fields, $joined = 't', $depth = 0) {
        global $wpdb;

        if (!isset($this->rabit_hole[$pod]))
            $this->rabit_hole[$pod] = array();
        $api = new PodAPI($pod);
        $pod_id = (int) $api->dt;
        foreach ($api->fields as $field) {
            if (!in_array($field['coltype'], array('pick', 'file')) && !isset($this->rabit_hole[$pod][$field['name']]))
                continue;
            $the_pod = null;
            $table = $field['pickval'];
            $on = 'id';
            $name = 'name';
            $recurse = true;
            if ('file' == $field['coltype'] || 'wp_page' == $table || 'wp_post' == $table) {
                $table = $wpdb->posts;
                $on = 'ID';
                $name = 'post_title';
                $recurse = false;
            }
            elseif ('wp_taxonomy' == $table) {
                $table = $wpdb->terms;
                $on = 'term_id';
                $recurse = false;
            }
            elseif ('wp_user' == $table) {
                $table = $wpdb->users;
                $on = 'ID';
                $name = 'display_name';
                $recurse = false;
            }
            elseif (!empty($table)) {
                $the_pod = $table;
                $table = '@wp_pod_tbl_' . $table;
            }
            $rabit_hole = array_merge($field,
                                      array('table' => $table,
                                            'pod' => $the_pod,
                                            'on' => $on,
                                            'name' => $name,
                                            'recurse' => $recurse));
            if (isset($this->rabit_hole[$pod][$field['name']]))
                $rabit_hole = array_merge($rabit_hole, (array) $this->rabit_hole[$pod][$field['name']]);
            $this->rabit_hole[$pod][$field['name']] = apply_filters('pods_rabit_hole', $rabit_hole, $pod, $fields, $joined, $depth);
        }
        unset($api);

        $joins = array();
        if (!isset($fields[$depth]) || empty($fields[$depth]))
            return $joins;
        $field = $fields[$depth];
        if (!isset($this->rabit_hole[$pod][$field]))
            return $joins;
        $this->rabit_hole[$pod][$field] = array_merge(array('table' => null,
                                                            'pod' => null,
                                                            'on' => 'id',
                                                            'name' => 'name',
                                                            'recurse' => false,
                                                            'id' => 0,
                                                            'coltype' => null),
                                                      $this->rabit_hole[$pod][$field]);
        $this->rabit_hole[$pod][$field]['id'] = (int) $this->rabit_hole[$pod][$field]['id'];
        $field_joined = $field;
        if (0 < $depth && 't' != $joined)
            $field_joined = $joined . '_' . $field;
        if (false !== $this->search) {
            if (0 < strlen(pods_var($field_joined, 'get'))) {
                $val = absint(pods_var($field_joined, 'get'));
                $on = $this->rabit_hole[$pod][$field]['on'];
                $search = "`{$field_joined}`.`{$on}` = {$val}";
                if ('text' == $this->search_mode) {
                    $val = pods_var($field_joined, 'get');
                    $on = $this->rabit_hole[$pod][$field]['name'];
                    $search = "`{$field_joined}`.`{$on}` = '{$val}'";
                }
                elseif ('text_like' == $this->search_mode) {
                    $val = pods_sanitize(like_escape($_GET[$field_joined]));
                    $on = $this->rabit_hole[$pod][$field]['name'];
                    $search = "`{$field_joined}`.`{$on}` LIKE '%{$val}%'";
                }
                $this->search_where .= " AND {$search} ";
            }
        }
        $p_alias = 'p';
        $p_join = '';
        if (0 < $depth && 't' != $joined) {
            $p_alias = 'p_' . $joined;
            $p_join = "
            LEFT JOIN `@wp_pod` AS `{$p_alias}` ON `{$p_alias}`.`datatype` = {$pod_id} AND `{$p_alias}`.`tbl_row_id` = `{$joined}`.`id`";
        }
        $rel_alias = 'rel_' . $field_joined;
        $the_join = "{$p_join}
            LEFT JOIN `@wp_pod_rel` AS `{$rel_alias}` ON `{$rel_alias}`.`field_id` = {$this->rabit_hole[$pod][$field]['id']} AND `{$rel_alias}`.`pod_id` = `{$p_alias}`.id
            LEFT JOIN `{$this->rabit_hole[$pod][$field]['table']}` AS `{$field_joined}` ON `{$field_joined}`.`{$this->rabit_hole[$pod][$field]['on']}` = `{$rel_alias}`.`tbl_row_id`
        ";
        if (!in_array($this->rabit_hole[$pod][$field]['coltype'], array('pick', 'file'))) {
            $the_join = "
            LEFT JOIN `{$this->rabit_hole[$pod][$field]['table']}` AS `{$field_joined}` ON `{$field_joined}`.`{$this->rabit_hole[$pod][$field]['on']}` = CONVERT(`{$joined}`.`{$field_joined}`, SIGNED)
            ";
        }
        $joins[$pod . '_' . $depth . '_' . $this->rabit_hole[$pod][$field]['id']] = apply_filters('pods_rabit_hole_the_join', $the_join, $pod, $fields, $joined, $depth, $this);
        if (($depth + 1) < count($fields) && null !== $this->rabit_hole[$pod][$field]['pod'] && false !== $this->rabit_hole[$pod][$field]['recurse'])
            $joins = array_merge($joins, $this->recurse_rabit_hole($this->rabit_hole[$pod][$field]['pod'], $fields, $field_joined, ($depth + 1)));
        return $joins;
    }

    /**
     * Recursively join tables based on fields
     */
    function rabit_hole ($pod, $fields = null) {
        $joins = array();
        if (null === $fields) {
            $api = new PodAPI($pod);
            $fields = $this->feed_rabit($api->fields);
        }
        foreach ((array) $fields as $field_group) {
            if (is_array($field_group))
                $joins = array_merge($joins, $this->recurse_rabit_hole($pod, $field_group));
            else {
                $joins = array_merge($joins, $this->recurse_rabit_hole($pod, $fields));
                $joins = array_filter($joins);
                return $joins;
            }
        }
        $joins = array_filter($joins);
        return $joins;
    }

    /**
     * Return a single record
     */
    function getRecordById($id) {
        if (empty($this->datatype_id)) {
            echo "<e>Error: Pod name invalid</e>";
            return null;
        }
        global $wpdb;
        $datatype = $this->datatype;
        $datatype_id = $this->datatype_id;
        if ($this->is_val($datatype)) {
            $this->result = null;
            if (is_numeric($id)) {
                $sql = "
                SELECT
                    DISTINCT `t`.*, `p`.`id` AS `pod_id`, `p`.`created`, `p`.`modified`
                FROM
                    `@wp_pod` AS `p`
                INNER JOIN
                    `@wp_pod_tbl_{$datatype}` AS `t` ON `t`.`id` = `p`.`tbl_row_id`
                WHERE
                    `p`.`datatype` = {$datatype_id}
                    AND `t`.`id` = {$id}
                LIMIT 1
                ";
                $this->raw_sql = $sql;
                $this->sql = str_replace('@wp_', $wpdb->prefix, $sql);
                $this->result = pod_query($sql);
                $this->row_number = 0;
                $this->zebra = false;
                $this->total = $this->total_rows = 1;
            }
            else {
                // Get the slug column
                $fields_result = pod_query("SELECT name FROM @wp_pod_fields WHERE coltype = 'slug' AND datatype = $this->datatype_id LIMIT 1");
                if (0 < mysql_num_rows($fields_result)) {
                    $field_name = mysql_result($fields_result, 0);
                    $sql = "
                    SELECT
                        DISTINCT `t`.*, `p`.`id` AS `pod_id`, `p`.`created`, `p`.`modified`
                    FROM
                        `@wp_pod` AS `p`
                    INNER JOIN
                        `@wp_pod_tbl_{$datatype}` AS `t` ON `t`.`id` = `p`.`tbl_row_id`
                    WHERE
                        `p`.`datatype` = {$datatype_id}
                        AND `t`.`{$field_name}` = '{$id}'
                    LIMIT 1
                    ";
                    $this->raw_sql = $sql;
                    $this->sql = str_replace('@wp_', $wpdb->prefix, $sql);
                    $this->result = pod_query($sql);
                    $this->row_number = 0;
                    $this->zebra = false;
                    $this->total = $this->total_rows = 1;
                }
            }

            if (is_resource($this->result) && 0 < mysql_num_rows($this->result)) {
                $this->data = mysql_fetch_assoc($this->result);
                $this->data['type'] = $datatype;
                return $this->data;
            }
            $this->data = false;
        }
        else
            die('<e>Datatype not set');
    }

    /**
     * Search and filter records
     */
    function findRecords($orderby = '`t`.`id` DESC', $rows_per_page = 15, $where = null, $sql = null) {
        if (empty($this->datatype_id)) {
            echo "<e>Error: Pod name invalid</e>";
            return null;
        }
        global $wpdb;
        $join = $groupby = $having = '';
        $params = null;
        $select = '`t`.*, `p`.`id` AS `pod_id`, `p`.`created`, `p`.`modified`';
        $this->traverse = array();

        $defaults = array('select' => $select,
                          'join' => $join,
                          'where' => $where,
                          'groupby' => $groupby,
                          'having' => $having,
                          'orderby' => (is_array($orderby) ? '`t`.`id` DESC' : $orderby),
                          'limit' => $rows_per_page,
                          'search' => $this->search,
                          'search_var' => $this->search_var,
                          'search_mode' => $this->search_mode,
                          'traverse' => $this->traverse,
                          'page' => $this->page,
                          'pagination' => $this->pagination,
                          'calc_found_rows' => $this->calc_found_rows,
                          'count_found_rows' => $this->count_found_rows,
                          'sql' => $sql);
        $defaults = (array) apply_filters('pods_findrecords_defaults', $defaults, $orderby, $this);
        $params = (object) $defaults;
        if (is_array($orderby) && !empty($orderby))
            $params = (object) array_merge($defaults, $orderby);

        if (0 < strlen($params->select))
            $select = $params->select;
        $join = $params->join;
        $this->search = (boolean) $params->search;
        $this->search_var = $params->search_var;
        $this->search_mode = (in_array($params->search_mode, array('int', 'text')) ? $params->search_mode : 'int');
        $this->traverse = (array) $params->traverse;
        $where = $params->where;
        $groupby = $params->groupby;
        $having = $params->having;
        $orderby = $params->orderby;
        $rows_per_page = (int) $params->limit;
        $this->page = (int) $params->page;
        $this->pagination = (bool) $params->pagination;
        $this->calc_found_rows = (boolean) $params->calc_found_rows;
        $this->count_found_rows = (boolean) $params->count_found_rows;
        $sql = $params->sql;
        if (true === $this->count_found_rows && empty($sql))
            $this->calc_found_rows = false;
        $page = (int) $this->page;
        if ($rows_per_page < 0 || false === $this->pagination)
            $page = $this->page = 1;
        $datatype = $this->datatype;
        $datatype_id = (int) $this->datatype_id;
        $this->rpp = (int) $rows_per_page;

        $sql_builder = false;
        if (empty($sql)) {
            $sql_builder = true;
            $limit = $this->search_where = '';

            // ctype_digit expects a string, or it returns FALSE
            if (ctype_digit("$rows_per_page") && 0 <= $rows_per_page) {
                $limit = ($rows_per_page * ($page - 1)) . ',' . $rows_per_page;
            }
            elseif (false !== strpos($rows_per_page, ',')) {
                // Custom offset
                $limit = $rows_per_page;
            }
            $where = empty($where) ? '' : " AND ( $where )";

            if (false !== $this->search) {
                // Handle search
                if (0 < strlen(pods_var($this->search_var, 'get'))) {
                    $val = pods_sanitize(like_escape($_GET[$this->search_var]));
                    $this->search_where = " AND (`t`.`name` LIKE '%{$val}%') ";
                }
            }

            // Add "`t`." prefix to $orderby if needed
            if (!empty($orderby) && false === strpos($orderby, ',') && false === strpos($orderby, '(') && false === strpos($orderby, '.')) {
                if (false !== strpos($orderby, ' ASC'))
                    $orderby = '`t`.`' . trim(str_replace(array('`', ' ASC'), '', $orderby)) . '` ASC';
                else
                    $orderby = '`t`.`' . trim(str_replace(array('`', ' DESC'), '', $orderby)) . '` DESC';
            }

            $haystack = str_replace(array('(', ')'), '', preg_replace('/\s/', ' ', "$select $where $groupby $having $orderby"));

            preg_match_all('/`?[\w]+`?(?:\\.`?[\w]+`?)+(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $haystack, $found, PREG_PATTERN_ORDER);

            $found = (array) @current($found);
            $find = $replace = array();
            foreach ($found as $key => $value) {
                $value = str_replace('`', '', $value);
                $value = explode('.', $value);
                $dot = array_pop($value);
                if (in_array('/\b' . trim($found[$key], '`') . '\b(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $find)) {
                    unset($found[$key]);
                    continue;
                }
                $find[$key] = '/\b' . trim($found[$key], '`') . '\b(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/';
                $esc_start = $esc_end = '`';
                if (strlen(ltrim($found[$key], '`')) < strlen($found[$key]))
                    $esc_start = '';
                if (strlen(rtrim($found[$key], '`')) < strlen($found[$key]))
                    $esc_end = '';
                if ('*' != $dot)
                    $dot = '`' . $dot . $esc_end;
                $replace[$key] = $esc_start . implode('_', $value) . '`.' . $dot;
                if (in_array($value[0], array('t', 'p'))) {
                    unset($found[$key]);
                    continue;
                }
                unset($found[$key]);
                if (!in_array($value, $found))
                    $found[$key] = $value;
            }

            if (!empty($this->traverse)) {
                foreach ((array) $this->traverse as $key => $traverse) {
                    $traverse = str_replace('`', '', $traverse);
                    $already_found = false;
                    foreach ($found as $traversal) {
                        if (is_array($traversal))
                            $traversal = implode('.', $traversal);
                        if ($traversal == $traverse) {
                            $already_found = true;
                            break;
                        }
                    }
                    if (!$already_found)
                        $found['traverse_' . $key] = explode('.', $traverse);
                }
            }

            $joins = array();
            if (!empty($find)) {
                $select = preg_replace($find, $replace, $select);
                $where = preg_replace($find, $replace, $where);
                $groupby = preg_replace($find, $replace, $groupby);
                $having = preg_replace($find, $replace, $having);
                $orderby = preg_replace($find, $replace, $orderby);

                if (!empty($found))
                    $joins = $this->rabit_hole($this->datatype, $found);
                elseif (false !== $this->search)
                    $joins = $this->rabit_hole($this->datatype);
            }

            if (0 < strlen($join)) {
                $joins[] = "
                    {$join}
                ";
            }
            $join = apply_filters('pods_findrecords_join', implode(' ', $joins), $params, $this);

            $groupby = trim($groupby);
            $orderby = trim($orderby);
            $limit = trim($limit);

            $calc_found_rows = '';
            if (false !== $this->calc_found_rows)
                $calc_found_rows = 'SQL_CALC_FOUND_ROWS';
            $sql = "
            SELECT
                {$calc_found_rows} DISTINCT {$select}
            FROM
                `@wp_pod` AS `p`
            INNER JOIN
                `@wp_pod_tbl_{$datatype}` AS `t` ON `t`.`id` = `p`.`tbl_row_id`
            {$join}
            WHERE
                `p`.`datatype` = {$datatype_id}";
            if (!empty($this->search_where)) {
                $sql .= "
                {$this->search_where}";
            }
            if (!empty($where)) {
                $sql .= "
                {$where}";
            }
            if (!empty($groupby)) {
                $sql .= "
            GROUP BY {$groupby}";
            }
            if (!empty($having)) {
                $sql .= "
            HAVING {$having}";
            }
            if (!empty($orderby)) {
                $sql .= "
            ORDER BY {$orderby}";
            }
            if (!empty($limit)) {
                $sql .= "
            LIMIT {$limit}";
            }
        }
        $this->raw_sql = $sql;
        $this->sql = str_replace('@wp_', $wpdb->prefix, $sql);
        $this->result = pod_query($sql);
        $this->row_number = -1;
        $this->zebra = false;
        $this->total = absint(@mysql_num_rows($this->result));
        if (false !== $this->calc_found_rows) {
            $this->total_rows = pod_query("SELECT FOUND_ROWS()");
            $this->getTotalRows();
        }
        elseif (false !== $this->count_found_rows && false !== $sql_builder) {
            $sql = "
            SELECT
                COUNT(*) AS `found_rows`
            FROM
                `@wp_pod` AS `p`
            INNER JOIN
                `@wp_pod_tbl_{$datatype}` AS `t` ON `t`.`id` = `p`.`tbl_row_id`
            {$join}
            WHERE
                `p`.`datatype` = {$datatype_id}";
            if (!empty($this->search_where)) {
                $sql .= "
                {$this->search_where}";
            }
            if (!empty($where)) {
                $sql .= "
                {$where}";
            }
            if (!empty($groupby)) {
                $sql .= "
            GROUP BY {$groupby}";
            }
            if (!empty($having)) {
                $sql .= "
            HAVING {$having}";
            }
            $this->total_rows = pod_query($sql);
            $this->getTotalRows();
        }
    }

    /**
     * Fetch the total row count
     */
    function getTotalRows() {
        if (false === is_numeric($this->total_rows)) {
            if (is_resource($this->total_rows) && $row = mysql_fetch_array($this->total_rows))
                $this->total_rows = $row[0];
            else
                $this->total_rows = 0;
        }
        return (int) $this->total_rows;
    }

    /**
     * Fetch the current row number
     */
    function getRowNumber() {
        return (int) $this->row_number;
    }

    /**
     * Fetch the current row number
     */
    function getZebra() {
        return (boolean) $this->zebra;
    }

    /**
     * (Re)set the MySQL result pointer
     */
    function resetPointer($row_number = 0) {
        $row_number = absint($row_number);
        if (0 < mysql_num_rows($this->result)) {
            $this->row_number = $row_number;
            $this->zebra = false;
            return mysql_data_seek($this->result, $row_number);
        }
        return false;
    }

    /**
     * Display HTML for all datatype fields
     */
    function showform($pod_id = null, $public_columns = null, $label = 'Save changes') {
        if (empty($this->datatype_id)) {
            echo "<e>Error: Pod name invalid</e>";
            return null;
        }
        $pod_id = absint($pod_id);
        $cache = PodCache::instance();

        $datatype = $this->datatype;
        $datatype_id = (int) $this->datatype_id;
        $this->data['pod_id'] = $pod_id = (int) $pod_id;

        $where = '';
        if (!empty($public_columns)) {
            foreach ($public_columns as $key => $val) {
                if (is_array($public_columns[$key])) {
                    $where[] = $key;
                    $attributes[$key] = $val;
                }
                else {
                    $where[] = $val;
                    $attributes[$val] = array();
                }
            }
            $where = "AND name IN ('" . implode("','", $where) . "')";
        }

        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE datatype = $datatype_id $where ORDER BY weight ASC");
        $public_columns = array();
        while ($row = mysql_fetch_assoc($result)) {
            $fields[$row['name']] = $row;
            $public_columns[] = $row['name'];
        }

        // Re-order the fields if a public form
        if (!empty($attributes)) {
            $tmp = $fields;
            $fields = array();
            foreach ($attributes as $key => $val) {
                if (isset($tmp[$key]))
                    $fields[$key] = $tmp[$key];
            }
            unset($tmp);
        }

        // Edit an existing item
        if (!empty($pod_id)) {
            $sql = "
            SELECT
                `t`.*
            FROM
                @wp_pod `p`
            INNER JOIN
                `@wp_pod_tbl_{$datatype}` AS `t` ON `t`.`id` = `p`.`tbl_row_id`
            WHERE
                `p`.`id` = {$pod_id}
            LIMIT
                1
            ";
            $result = pod_query($sql);
            if (0 < mysql_num_rows($result)) {
                $tbl_cols = mysql_fetch_assoc($result);
            }
        }
        $uri_hash = wp_hash($_SERVER['REQUEST_URI']);

        do_action('pods_showform_pre', $pod_id, $public_columns, $label, $this);

        foreach ($fields as $key => $field) {
            // Replace field attributes with public form attributes
            if (!empty($attributes) && is_array($attributes[$key])) {
                $field = array_merge($field, $attributes[$key]);
            }

            if (empty($field['label'])) {
                $field['label'] = ucwords($key);
            }

            if (1 == $field['required']) {
                $field['label'] .= ' <span class="red">*</span>';
            }

            if (!empty($field['pickval'])) {
                $val = array();
                $tbl_row_ids = array();
                $table = $field['pickval'];

                $result = pod_query("SELECT `id` FROM `@wp_pod_fields` WHERE `datatype` = {$datatype_id} AND `name` = '$key' LIMIT 1");
                $field_id = (int) mysql_result($result, 0);

                $result = pod_query("SELECT `tbl_row_id` FROM `@wp_pod_rel` WHERE `field_id` = {$field_id} AND `pod_id` = {$pod_id}");
                while ($row = mysql_fetch_assoc($result)) {
                    $tbl_row_ids[] = (int) $row['tbl_row_id'];
                }

                // Use default values for public forms
                if (empty($tbl_row_ids) && !empty($field['default'])) {
                    $tbl_row_ids = $field['default'];
                    if (!is_array($field['default'])) {
                        $tbl_row_ids = explode(',', $tbl_row_ids);
                        foreach ($tbl_row_ids as $row_key => $row_val) {
                            $tbl_row_ids[$row_key] = (int) trim($row_val);
                        }
                    }
                }

                // If the PICK column is unique, get values already chosen
                $unique_vals = false;
                if (1 == $field['unique']) {
                    $exclude = empty($pod_id) ? '' : "AND `pod_id` != {$pod_id}";
                    $result = pod_query("SELECT `tbl_row_id` FROM `@wp_pod_rel` WHERE `field_id` = {$field_id} {$exclude}");
                    if (0 < mysql_num_rows($result)) {
                        $unique_vals = array();
                        while ($row = mysql_fetch_assoc($result)) {
                            $unique_vals[] = (int) $row['tbl_row_id'];
                        }
                        $unique_vals = implode(',', $unique_vals);
                    }
                }

                $params = array(
                    'table' => $table,
                    'field_name' => null,
                    'tbl_row_ids' => $tbl_row_ids,
                    'unique_vals' => $unique_vals,
                    'pick_filter' => $field['pick_filter'],
                    'pick_orderby' => $field['pick_orderby']
                );
                $this->data[$key] = $this->get_dropdown_values($params);
            }
            else {
                // Set a default value if no value is entered
                if (!isset($this->data[$key]) && !empty($field['default'])) {
                    $this->data[$key] = $field['default'];
                }
                else {
                    $this->data[$key] = isset($tbl_cols[$key]) && $this->is_val($tbl_cols[$key]) ? $tbl_cols[$key] : null;
                }
            }
            $this->build_field_html($field);
        }
        $uri_hash = wp_hash($_SERVER['REQUEST_URI']);

        $save_button_atts = array(
        	'type' => 'button',
        	'class' => 'button btn_save',
        	'value' => $label,
        	'onclick' => "saveForm($cache->form_count)"
        );
        $save_button_atts = apply_filters('pods_showform_save_button_atts', $save_button_atts, $this);
        $atts = '';
        foreach ($save_button_atts as $att => $value) {
        	$atts .= $att.'="'.$value.'" ';
        }
        $save_button = '<input '.$atts.'/>';
?>
    <div>
    <input type="hidden" class="form num pod_id" value="<?php echo $pod_id; ?>" />
    <input type="hidden" class="form num tbl_row_id" value="<?php echo (!empty($tbl_cols) ? $tbl_cols['id'] : 0); ?>" />
    <input type="hidden" class="form txt datatype" value="<?php echo $datatype; ?>" />
    <input type="hidden" class="form txt form_count" value="<?php echo $cache->form_count; ?>" />
    <input type="hidden" class="form txt token" value="<?php echo pods_generate_key($datatype, $uri_hash, $public_columns, $cache->form_count); ?>" />
    <input type="hidden" class="form txt uri_hash" value="<?php echo $uri_hash; ?>" />
	<?php echo apply_filters('pods_showform_save_button', $save_button, $save_button_atts, $this); ?>
    </div>
<?php
        do_action('pods_showform_post', $pod_id, $public_columns, $label, $this);
    }

    /**
     * Does the field have a value? (incl. 0)
     */
    function is_val($val) {
        return (null != $val && false !== $val) ? true : false;
    }

    /**
     * Display the pagination controls
     */
    function getPagination($label = 'Go to page:') {
        if ($this->rpp < $this->getTotalRows() && 0 < $this->rpp && false !== $this->pagination) {
            include PODS_DIR . '/ui/pagination.php';
        }
    }

    /**
     * Display the list filters
     */
    function getFilters($filters = null, $label = 'Filter', $action = '') {
        include PODS_DIR . '/ui/list_filters.php';
    }

    /**
     * Build public input form
     */
    function publicForm($public_columns = null, $label = 'Save changes', $thankyou_url = null) {
        include PODS_DIR . '/ui/input_form.php';
    }

    /**
     * Build HTML for a single field
     */
    function build_field_html($field) {
        include PODS_DIR . '/ui/input_fields.php';
    }

    /**
     * Display the page template
     */
    function showTemplate($template, $code = null) {
        ob_start();

        //pre-template hooks
        do_action('pods_pre_showtemplate', $template, $code, $this);
        do_action("pods_pre_showtemplate_$template", $template, $code, $this);

        if (!empty($code))
            $function_or_file = false;
        else {
            $function_or_file = $template;
            $check_function = false;
            $check_file = null;
            if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_TEMPLATE_FILES') || !PODS_TEMPLATE_FILES))
                $check_file = false;
            if (false !== $check_function && false !== $check_file)
                $function_or_file = pods_function_or_file($function_or_file, $check_function, 'template', $check_file);
            else
                $function_or_file = false;

            if (!$function_or_file) {
                $api = new PodAPI();
                $params = array('name' => $template);
                if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
                    $params = pods_sanitize($params);
                $code = $api->load_template($params);
                if (false !== $code && 0 < strlen(trim($code['code'])))
                    $code = $code['code'];
                else
                    $code = false;
            }
        }

        if (empty($code) && false !== $function_or_file && isset($function_or_file['file'])) {
            // Only detail templates need $this->id
            if (empty($this->id)) {
                while ($this->fetchRecord()) {
                    locate_template($function_or_file['file'], true, true);
                }
            }
            else
                locate_template($function_or_file['file'], true, true);
        }
        elseif (!empty($code)) {
            // Only detail templates need $this->id
            if (empty($this->id)) {
                while ($this->fetchRecord()) {
                    echo $this->parse_template_string($code);
                }
            }
            else
                echo $this->parse_template_string($code);
        }

        //post-template hooks
        do_action('pods_post_showtemplate', $template, $code, $this);
        do_action("pods_post_showtemplate_$template", $template, $code, $this);

        return apply_filters('pods_showtemplate', ob_get_clean(), $template, $code, $this);
    }

    /**
     * Parse a template string
     *
     * @param string $in The template string to parse
     * @since 1.8.5
     */
    function parse_template_string($in) {
        ob_start();
        if (!defined('PODS_DISABLE_EVAL') || !PODS_DISABLE_EVAL)
            eval("?>$in");
        else
            echo $in;
        $out = ob_get_clean();
        $out = preg_replace_callback("/({@(.*?)})/m", array($this, "parse_magic_tags"), $out);
        return apply_filters('pods_parse_template_string', $out, $in, $this);
    }

    /**
     * Replace magic tags with their values
     */
    function parse_magic_tags($in) {
        $name = $in[2];
        $before = $after = $helper = '';
        if (false !== strpos($name, ',')) {
            @list($name, $helper, $before, $after) = explode(',', $name);
            $name = trim($name);
            $helper = trim($helper);
            $before = trim($before);
            $after = trim($after);
        }

        if ('type' == $name)
            $value = $this->datatype;
        elseif ('detail_url' == $name)
            $value = get_bloginfo('url') . '/' . $this->parse_template_string($this->detail_page);
        else
            $value = $this->get_field($name);

        // Use helper if necessary
        if (!empty($helper))
            $value = $this->pod_helper($helper, $value, $name);

        // Clean out PHP in case it exists
        $value = str_replace(array('<' . '?php', '<' . '?', '?' .'>'), array('&lt;?php', '&lt;?', '?&gt;'), $value);

        $value = apply_filters('pods_parse_magic_tags', $value, $name, $helper, $before, $after);
        if (null != $value && false !== $value)
            return $before . $value . $after;
    }
}