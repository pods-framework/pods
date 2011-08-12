<?php
class Pod
{
    var $id;
    var $sql;
    var $data;
    var $result;
    var $datatype;
    var $datatype_id;
    var $total_rows;
    var $detail_page;
    var $rpp = 15;
    var $page_var = 'pg';
    var $page;
    var $search;
    var $search_var = 'search';
    var $wpdb;

    /**
     * Constructor - Pods CMS core
     *
     * @param string $datatype The pod name
     * @param mixed $id (optional) The ID or slug, to load a single record
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.0.0
     */
    function __construct($datatype = null, $id = null) {
        global $wpdb;

        $this->wpdb = $wpdb;
        $id = pods_sanitize($id);
        $datatype = pods_sanitize($datatype);

        // Get the page variable
        $this->page = pods_url_variable($this->page_var, 'get');
        $this->page = empty($this->page) ? 1 : max((int) $this->page, 1);

        if (!empty($datatype)) {
            $result = pod_query("SELECT id, name, detail_page FROM @wp_pod_types WHERE name = '$datatype' LIMIT 1");
            if (0 < mysql_num_rows($result)) {
                $row = mysql_fetch_assoc($result);
                $this->datatype = $row['name'];
                $this->datatype_id = $row['id'];
                $this->detail_page = $row['detail_page'];

                if (null != $id) {
                    $this->getRecordById($id);
                    if (!empty($this->data))
                        $this->id = $this->get_field('id');
                }
            }
            else
                echo "<e>Error: Pod does not exist</e>";
        }
    }

    /**
     * Fetch a row of results from the DB
     *
     * @since 1.2.0
     */
    function fetchRecord() {
        if ($this->data = mysql_fetch_assoc($this->result)) {
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
            $pod_id = $this->get_pod_id();
            $result = pod_query("SELECT created, modified FROM @wp_pod WHERE id = $pod_id LIMIT 1");
            $row = mysql_fetch_assoc($result);
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
            r.tbl_row_id
        FROM
            @wp_pod p
        INNER JOIN
            @wp_pod_rel r ON r.field_id = {$field_id} AND r.pod_id = p.id
        WHERE
            p.datatype = {$datatype_id} AND p.tbl_row_id IN ($tbl_row_ids)
        ORDER BY
            r.weight
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
            $result = pod_query("SELECT * FROM $wpdb->terms WHERE term_id IN ($tbl_row_ids) $orderby");
        }
        // WP page, post, or attachment
        elseif ('wp_page' == $table || 'wp_post' == $table || 'file' == $table) {
            $result = pod_query("SELECT * FROM $wpdb->posts WHERE ID IN ($tbl_row_ids) $orderby");
        }
        // WP user
        elseif ('wp_user' == $table) {
            $result = pod_query("SELECT * FROM $wpdb->users WHERE ID IN ($tbl_row_ids) $orderby");
        }
        // Pod table
        else {
            $result = pod_query("SELECT * FROM `@wp_pod_tbl_$table` WHERE id IN ($tbl_row_ids) $orderby");
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
        if (empty($this->data['pod_id'])) {
            $this->data['pod_id'] = 0;
            $tbl_row_id = (isset($this->data['id'])?$this->data['id']:0);
            $result = pod_query("SELECT id FROM @wp_pod WHERE datatype = '$this->datatype_id' AND tbl_row_id = '$tbl_row_id' LIMIT 1");
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
     * Run a helper within a Pod Page or WP Template
     *
     * @param string $helper The helper name
     * @return mixed Anything returned by the helper
     * @since 1.2.0
     */
    function pod_helper($helper, $value = null, $name = null) {
        $api = new PodAPI();
        $helper = pods_sanitize($helper);
        $content = $api->load_helper(array('name' => $helper));
        $content = $content['phpcode'];

        ob_start();

        //pre-helper hooks
        do_action('pods_pre_pod_helper', $helper, $value, $name, $this);
        do_action("pods_pre_pod_helper_$helper", $helper, $value, $name, $this);

        if (false !== $content) {
            eval("?>$content");
        }

        //post-helper hooks
        do_action('pods_post_pod_helper', $helper, $value, $name, $this);
        do_action("pods_post_pod_helper_$helper", $helper, $value, $name, $this);

        return ob_get_clean();
    }

    /**
     * Get pod or category dropdown values
     */
    function get_dropdown_values($params) {
        global $wpdb;

        $params = (object) $params;

        $params->orderby = empty($params->pick_orderby) ? 'name ASC' : $params->pick_orderby;

        // WP taxonomy dropdown
        if ('wp_taxonomy' == $params->table) {
            $where = (false !== $params->unique_vals) ? "WHERE t.term_id NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= (empty($where) ? ' WHERE ' : ' AND ') . $params->pick_filter;
            }

            $sql = "SELECT t.term_id AS id, t.name FROM {$wpdb->term_taxonomy} AS tx INNER JOIN {$wpdb->terms} AS t ON t.term_id = tx.term_id {$where} ORDER BY {$params->orderby}";
        }
        // WP page or post dropdown
        elseif ('wp_page' == $params->table || 'wp_post' == $params->table) {
            $post_type = substr($params->table, 3);
            $where = (false !== $params->unique_vals) ? "AND id NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= " AND {$params->pick_filter}";
            }

            $sql = "SELECT ID as id, post_title AS name FROM {$wpdb->posts} AS t WHERE post_type = '{$post_type}' {$where} ORDER BY {$params->orderby}";
        }
        // WP user dropdown
        elseif ('wp_user' == $params->table) {
            $where = (false !== $params->unique_vals) ? "WHERE id NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= (empty($where) ? ' WHERE ' : ' AND ') . $params->pick_filter;
            }

            $sql = "SELECT ID as id, display_name AS name FROM {$wpdb->users} AS t {$where} ORDER BY {$params->orderby}";
        }
        // Pod table dropdown
        else {
            $where = (false !== $params->unique_vals) ? "WHERE id NOT IN ({$params->unique_vals})" : '';
            if (!empty($params->pick_filter)) {
                $where .= (empty($where) ? ' WHERE ' : ' AND ') . $params->pick_filter;
            }

            $sql = "SELECT * FROM `@wp_pod_tbl_{$params->table}` AS t {$where} ORDER BY {$params->orderby}";
        }

        //override with custom dropdown values
        $sql = apply_filters('pods_get_dropdown_values', $sql, $params, $this);

        $val = array();
        $result = pod_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            if (!empty($params->tbl_row_ids)) {
                $row['active'] = in_array($row['id'], $params->tbl_row_ids);
            }
            else {
                if (isset($_GET[$params->field_name]) && $row['id'] == $_GET[$params->field_name]) {
                    $row['active'] = true;
                }
                else {
                    $row['active'] = false;
                }
            }
            $val[] = $row;
        }
        return $val;
    }

    /**
     * Return a single record
     */
    function getRecordById($id) {
        if (empty($this->datatype_id)) {
            echo "<e>Error: Pod name invalid</e>";
            return null;
        }
        $datatype = $this->datatype;
        if ($this->is_val($datatype)) {
            if (is_numeric($id)) {
                $result = pod_query("SELECT * FROM `@wp_pod_tbl_$datatype` WHERE id = $id LIMIT 1");
            }
            else {
                // Get the slug column
                $result = pod_query("SELECT name FROM @wp_pod_fields WHERE coltype = 'slug' AND datatype = $this->datatype_id LIMIT 1");
                if (0 < mysql_num_rows($result)) {
                    $field_name = mysql_result($result, 0);
                    $result = pod_query("SELECT * FROM `@wp_pod_tbl_$datatype` WHERE `$field_name` = '$id' LIMIT 1");
                }
            }

            if (0 < mysql_num_rows($result)) {
                $this->data = mysql_fetch_assoc($result);
                $this->data['type'] = $datatype;
                return $this->data;
            }
            $this->data = false;
        }
        else {
            die('<e>Datatype not set');
        }
    }

    /**
     * Search and filter records
     */
    function findRecords($orderby = 't.id DESC', $rows_per_page = 15, $where = null, $sql = null) {
        if (empty($this->datatype_id)) {
            echo "<e>Error: Pod name invalid</e>";
            return null;
        }
        global $wpdb;
        $join = $groupby = $having = '';
        $params = null;
        $select = 't.*, p.id AS pod_id, p.created, p.modified';
        if(is_array($orderby)) {
            $defaults = array('select'=>$select,'join'=>$join,'search'=>$this->search,'where'=>$where,'groupby'=>$groupby,'having'=>$having,'orderby'=>'t.id DESC','limit'=>$rows_per_page,'page'=>$this->page,'sql'=>$sql);
            $params = (object) array_merge($defaults,$orderby);
            if (0 < strlen($params->select))
                $select = $params->select;
            $join = $params->join;
            $this->search = $params->search;
            $where = $params->where;
            $groupby = $params->groupby;
            $having = $params->having;
            $orderby = $params->orderby;
            $rows_per_page = (int) $params->limit;
            $this->page = (int) $params->page;
            $sql = $params->sql;
        }
        $page = (int) $this->page;
        if (-1 == $rows_per_page)
            $page = 1;
        $datatype = $this->datatype;
        $datatype_id = $this->datatype_id;
        $this->rpp = (int) $rows_per_page;

        if (empty($sql)) {
            $limit = $search = '';

            // ctype_digit expects a string, or it returns FALSE
            if (ctype_digit("$rows_per_page") && 0 <= $rows_per_page) {
                $limit = 'LIMIT ' . ($rows_per_page * ($page - 1)) . ',' . $rows_per_page;
            }
            elseif (false !== strpos($rows_per_page, ',')) {
                // Custom offset
                $limit = 'LIMIT ' . $rows_per_page;
            }
            $where = empty($where) ? '' : " AND ( $where )";

            if (false !== $this->search) {
                // Handle search
                if (!empty($_GET[$this->search_var])) {
                    $val = pods_url_variable($this->search_var, 'get');
                    $search = "AND (t.name LIKE '%$val%')";
                }
            }

            // Add "t." prefix to $orderby if needed
            if (false !== strpos($orderby, ',') && false === strpos($orderby, '.') && !empty($orderby)) {
                $orderby = 't.' . $orderby;
            }

            // Get this pod's fields
            $result = pod_query("SELECT id, name, coltype, pickval FROM @wp_pod_fields WHERE datatype = $datatype_id AND (coltype = 'file' OR coltype = 'pick') ORDER BY weight");
            $i = 0;
            while ($row = mysql_fetch_assoc($result)) {
                $i++;
                $field_id = $row['id'];
                $field_name = $row['name'];
                $field_coltype = $row['coltype'];
                $table = $row['pickval'];

                if (false !== $this->search) {
                    // Handle any $_GET variables
                    if (!empty($_GET[$field_name])) {
                        $val = (int) trim($_GET[$field_name]);

                        if ('wp_taxonomy' == $table) {
                            $search .= " AND `$field_name`.term_id = '$val'";
                        }
                        else {
                            $search .= " AND `$field_name`.id = '$val'";
                        }
                    }
                }

                // Performance improvement - only use PICK columns mentioned in ($orderby, $where, $search)
                $haystack = "$select $orderby $where $search $groupby $having";
                if (false === strpos($haystack, $field_name . '.') && false === strpos($haystack, "`$field_name`.")) {
                    continue;
                }

                if ('wp_taxonomy' == $table) {
                    $the_join = "
                    LEFT JOIN
                        @wp_pod_rel r$i ON r$i.field_id = $field_id AND r$i.pod_id = p.id
                    LEFT JOIN
                        $wpdb->terms `$field_name` ON `$field_name`.term_id = r$i.tbl_row_id
                    ";
                }
                elseif ('wp_page' == $table || 'wp_post' == $table || 'file' == $field_coltype) {
                    $the_join = "
                    LEFT JOIN
                        @wp_pod_rel r$i ON r$i.field_id = $field_id AND r$i.pod_id = p.id
                    LEFT JOIN
                        $wpdb->posts `$field_name` ON `$field_name`.ID = r$i.tbl_row_id
                    ";
                }
                elseif ('wp_user' == $table) {
                    $the_join = "
                    LEFT JOIN
                        @wp_pod_rel r$i ON r$i.field_id = $field_id AND r$i.pod_id = p.id
                    LEFT JOIN
                        $wpdb->users `$field_name` ON `$field_name`.ID = r$i.tbl_row_id
                    ";
                }
                else {
                    $the_join = "
                    LEFT JOIN
                        @wp_pod_rel r$i ON r$i.field_id = $field_id AND r$i.pod_id = p.id
                    LEFT JOIN
                        `@wp_pod_tbl_$table` `$field_name` ON `$field_name`.id = r$i.tbl_row_id
                    ";
                }
                //override with custom joins
                $join .= ' '.apply_filters('pods_findrecords_the_join', $the_join, $i, $row, $params, $this).' ';
            }
            //override with custom joins
            $join = apply_filters('pods_findrecords_join', $join, $params, $this);

            $groupby = empty($groupby) ? '' : "GROUP BY $groupby";
            $having = empty($having) ? '' : "HAVING $having";
            $orderby = empty($orderby) ? '' : "ORDER BY $orderby";

            $sql = "
            SELECT
                SQL_CALC_FOUND_ROWS DISTINCT {$select}
            FROM
                @wp_pod p
            INNER JOIN
                `@wp_pod_tbl_{$datatype}` t ON t.id = p.tbl_row_id
            $join
            WHERE
                p.datatype = {$datatype_id}
                {$search}
                {$where}
            {$groupby}
            {$having}
            {$orderby}
            {$limit}
            ";
        }
        $this->sql = $sql;
        $this->result = pod_query($sql);
        $this->total = absint(@mysql_num_rows($this->result));
        $this->total_rows = pod_query("SELECT FOUND_ROWS()");
        if (false === is_numeric($this->total_rows)) {
            if ($row = mysql_fetch_array($this->total_rows)) {
                $this->total_rows = $row[0];
            }
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
        return $this->total_rows;
    }

    /**
     * (Re)set the MySQL result pointer
     */
    function resetPointer($row_number = 0) {
        if (0 < mysql_num_rows($this->result)) {
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
        $cache = PodCache::instance();

        $datatype = $this->datatype;
        $datatype_id = (int) $this->datatype_id;
        $this->coltype_counter = array();
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
                t.*
            FROM
                @wp_pod p
            INNER JOIN
                `@wp_pod_tbl_$datatype` t ON t.id = p.tbl_row_id
            WHERE
                p.id = $pod_id
            LIMIT
                1
            ";
            $result = pod_query($sql);
            if (0 < mysql_num_rows($result)) {
                $tbl_cols = mysql_fetch_assoc($result);
            }
        }
        $uri_hash = md5($_SERVER['REQUEST_URI']);

        do_action('pods_showform_pre', $pod_id, $public_columns, $label, $this);
        
        foreach ($fields as $key => $field) {
            // Replace field attributes with public form attributes
            if (!empty($attributes) && is_array($attributes[$key])) {
                $field = array_merge($field, $attributes[$key]);
            }

            // Replace the input helper name with the helper code
            $input_helper = $field['input_helper'];
            if (!empty($input_helper)) {
                $result = pod_query("SELECT phpcode FROM @wp_pod_helpers WHERE name = '$input_helper' LIMIT 1");
                $field['input_helper'] = mysql_result($result, 0);
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

                $result = pod_query("SELECT id FROM @wp_pod_fields WHERE datatype = $datatype_id AND name = '$key' LIMIT 1");
                $field_id = (int) mysql_result($result, 0);

                $result = pod_query("SELECT tbl_row_id FROM @wp_pod_rel WHERE field_id = $field_id ANd pod_id = $pod_id");
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
                    $exclude = empty($pod_id) ? '' : "AND pod_id != $pod_id";
                    $result = pod_query("SELECT tbl_row_id FROM @wp_pod_rel WHERE field_id = $field_id $exclude");
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
        $uri_hash = md5($_SERVER['REQUEST_URI']);

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
        if ($this->rpp < $this->getTotalRows() && 0 < $this->rpp) {
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
    function showTemplate($tpl, $code = null) {
        ob_start();

        //pre-template hooks
        do_action('pods_pre_showtemplate', $tpl, $code, $this);
        do_action("pods_pre_showtemplate_$tpl", $tpl, $code, $this);

        if (empty($code)) {
            $result = pod_query("SELECT code FROM @wp_pod_templates WHERE name = '$tpl' LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $code = $row['code'];
        }

        if (!empty($code)) {
            // Only detail templates need $this->id
            if (empty($this->id)) {
                while ($this->fetchRecord()) {
                    echo $this->parse_template_string($code);
                }
            }
            else {
                echo $this->parse_template_string($code);
            }
        }

        //post-template hooks
        do_action('pods_post_showtemplate', $tpl, $code, $this);
        do_action("pods_post_showtemplate_$tpl", $tpl, $code, $this);

        return ob_get_clean();
    }

    /**
     * Parse a template string
     *
     * @param string $in The template string to parse
     * @since 1.8.5
     */
    function parse_template_string($in) {
        ob_start();
        $out = preg_replace_callback("/({@(.*?)})/m", array($this, "parse_magic_tags"), $in);
        eval("?>$out");
        return ob_get_clean();
    }

    /**
     * Replace magic tags with their values
     */
    function parse_magic_tags($in) {
        $name = $in[2];
        $before = $after = '';
        if (false !== strpos($name, ',')) {
            @list($name, $helper, $before, $after) = explode(',', $name);
            $name = trim($name);
            $helper = trim($helper);
            $before = trim($before);
            $after = trim($after);
        }
        if ('type' == $name) {
            return $this->datatype;
        }
        elseif ('detail_url' == $name) {
            return get_bloginfo('url') . '/' . $this->parse_template_string($this->detail_page);
        }
        else {
            $value = $this->get_field($name);

            // Use helper if necessary
            if (!empty($helper)) {
                $value = $this->pod_helper($helper, $value, $name);
            }
            if (null != $value && false !== $value) {
                return $before . $value . $after;
            }
        }
    }
}