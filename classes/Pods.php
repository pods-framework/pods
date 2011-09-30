<?php
class Pods
{
    private $api;
    public $display_errors = false;

    public $column_id = 'id';
    public $column_index = 'name';

    public $pod_data;
    public $pod;
    public $pod_id;
    public $fields;
    public $detail_page;

    public $id;

    public $results = array();
    public $data;
    public $sql;
    public $calc_found_rows = true;
    public $count_found_rows = false;
    private $row_number = -1;
    private $zebra = false;
    private $total = 0;
    private $total_found = 0;

    public $limit = 15;
    public $page_var = 'pg';
    public $page = 1;
    public $pagination = true;
    public $search = true;
    public $search_var = 'search';
    public $search_mode = 'int'; // int | text | text_like
    private $search_where = '';
    private $search_prepare = array();

    public $traverse = array();
    public $rabit_hole = array();

    /**
     * Constructor - Pods CMS core
     *
     * @param string $pod The pod name
     * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find' immediately
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.0.0
     */
    public function __construct ($pod = null, $id = null) {
        $this->api = pods_api();
        $this->api->display_errors &= $this->display_errors;

        if (defined('PODS_STRICT_MODE') && PODS_STRICT_MODE) {
            $this->page = 1;
            $this->pagination = false;
            $this->search = false;
        }
        else {
            // Get the page variable
            $this->page = pods_var($this->page_var, 'get');
            $this->page = (empty($this->page) ? 1 : max(pods_absint($this->page), 1));
            if (defined('PODS_GLOBAL_POD_PAGINATION') && !PODS_GLOBAL_POD_PAGINATION) {
                $this->page = 1;
                $this->pagination = false;
            }

            if (defined('PODS_GLOBAL_POD_SEARCH') && !PODS_GLOBAL_POD_SEARCH)
                $this->search = false;
            if (defined('PODS_GLOBAL_POD_SEARCH_MODE') && in_array(PODS_GLOBAL_POD_SEARCH_MODE, array('int', 'text', 'text_like')))
                $this->search_mode = PODS_GLOBAL_POD_SEARCH_MODE;
        }

        if (null !== $pod) {
            $this->pod_data = $this->api->load_pod(array('name' => $pod));
            if(false === $this->pod_data)
                return pods_error('Pod not found', $this);
            $this->pod_id = $this->datatype_id = $pod['id'];
            $this->pod = $this->datatype = $pod['name'];
            $this->fields = $pod['fields'];
            $this->detail_page = $pod['detail_page'];

            switch ($pod['type']) {
                case 'pod':
                    $this->column_id = 'id';
                    $this->column_name = 'name';
                    break;
                case 'post_type':
                    $this->column_id = 'ID';
                    $this->column_name = 'post_title';
                    break;
                case 'taxonomy':
                    $this->column_id = 'term_id';
                    $this->column_name = 'name';
                    break;
                case 'user':
                    $this->column_id = 'ID';
                    $this->column_name = 'display_name';
                    break;
                case 'comment':
                    $this->column_id = 'comment_ID';
                    $this->column_name = 'comment_date';
                    break;
                case 'table':
                    $this->column_id = 'id';
                    $this->column_name = 'name';
                    break;
            }

            if (null !== $id) {
                if (is_array($id) || is_object($id))
                    $this->find($id);
                else {
                    $this->fetch($id);
                    if (!empty($this->data))
                        $this->id = $this->field($this->column_id);
                }
            }
        }
    }

    /**
     * Return a field's value(s)
     *
     * @param array $params An associative array of parameters (OR the Column name)
     * @param string $orderby (optional) The orderby string, for PICK fields
     * @since 2.0.0
     */
    public function field ($params, $orderby = null) {
        $value = null;

        $default = array('name' => $params, 'orderby' => $orderby);
        if (is_array($params))
            $params = array_merge($default, $params);
        else
            $params = $default;
        $name = $params->name;
        $orderby = $params->orderby;

        if (isset($this->data[$params->name]))
            $value = $this->data[$params->name];
        elseif ('detail_url' == $name)
            $value = $this->do_magic_tags($params->name);
        else {
            // Dot-traversal
            $ids = $this->data[$this->column_id];

            $traverse = (false !== strpos($params->name, '.') ? explode('.', $params->name) : array($params->name));
            $traverse_fields = implode("', '", $traverse);

            $all_fields = array();
            if (!empty($traverse_fields)) {
                // Get columns matching traversal names
                $sql = "SELECT * FROM `@wp_pods_fields` WHERE `name` IN ('$traverse_fields')";
                $result = pods_query($sql, $this);
                if (!empty($result)) {
                    foreach ($result as $row) {
                        if (!isset($all_fields[$row->pod]))
                            $all_fields[$row->pod] = array();
                        $all_fields[$row->pod][$row->name] = get_object_vars($row);
                    }
                }
                // No matching columns
                else
                    $value = false;
            }
            else
                $value = false;

            if (null === $value) {
                // Loop through each traversal level
                $total_traverse = count($traverse) - 1;
                $last_type = $last_pick_object = $last_pick_val = '';
                $pod_id = $this->pod_id;
                foreach ($traverse as $key => $column_name) {
                    $last_loop = false;
                    if ($total_traverse == $key)
                        $last_loop = true;
                    $column_exists = (isset($all_fields[$pod_id]) && isset($all_fields[$pod_id][$column_name]));

                    if ($column_exists) {
                        $column = $all_fields[$pod_id][$column_name];
                        $pod_id = 0;
                        $field_id = $column['id'];
                        $type = $column['type'];
                        $pick_object = $column['pick_object'];
                        $pick_val = $column['pick_val'];

                        if ('pick' == $type || 'file' == $type) {
                            $last_type = $type;
                            $last_pick_object = $pick_object;
                            $last_pick_val = $pick_val;
                            $ids = $this->lookup_row_ids($field_id, $pod_id, $ids);
                            if (false === $ids)
                                return false;

                            // Get Pod ID for Pod PICK columns
                            if (!empty($pick_val) && !in_array($pick_val, array('wp_taxonomy',
                                                                                'wp_post',
                                                                                'wp_page',
                                                                                'wp_user'))) {
                                $where = 'name';
                                if ('pod' != $pick_object)
                                    $where = 'object';
                                $sql = "SELECT `id` FROM `@wp_pods` WHERE `type` = %s AND `{$where}` = %s LIMIT 1";
                                $sql = array($sql, array($pick_object, $pick_val));
                                $result = pods_query($sql, $this);
                                if (!empty($result))
                                    $pod_id = $result[0]->id;
                            }
                        }
                        else
                            $last_loop = true;
                    }
                    // Assume last iteration
                    else
                        $last_loop = true;

                    if ($last_loop && !empty($last_pick_object) && !empty($last_pick_val)) {
                        $table = '';
                        if ('file' == $last_type)
                            $table = '@wp_posts';
                        else {
                            switch ($last_pick_object) {
                                case 'pod':
                                    $table = "@wp_pods_tbl_{$last_pick_val}";
                                    break;
                                case 'post_type':
                                    $table = '@wp_posts';
                                    break;
                                case 'taxonomy':
                                    $table = '@wp_terms';
                                    break;
                                case 'user':
                                    $table = '@wp_users';
                                    break;
                                case 'comment':
                                    $table = '@wp_comments';
                                    break;
                                case 'table':
                                    $table = "{$last_pick_val}";
                                    break;
                            }
                        }

                        if (!empty($table))
                            $data = $this->lookup(array('ids' => $ids, 'table' => $table, 'orderby' => $orderby));
                        $results = $value;

                        if (empty($data))
                            $results = false;
                        // Return entire array
                        elseif (false !== $column_exists && ('pick' == $type || 'file' == $type))
                            $results = $data;
                        // Return a single column value
                        elseif (1 == count($data)) {
                $data = current($data);
                if (isset($data[$column_name]))
                                $results = $data[$column_name];
                }
                        // Return an array of single column values
                        else {
                            foreach ($data as $k => $v) {
                    if (isset($v[$column_name]))
                                    $results[$k] = $v[$column_name];
                            }
                        }
                        $value = $results;
                    }
                }
            }
        }
        $value = apply_filters('pods_field', $value, $params, $this);
        $value = apply_filters("pods_field_{$this->pod}", $value, $params, $this);
        return $value;
    }

    /**
     * Set a custom data value (no database changes)
     *
     * @param string $name The field name
     * @param mixed $data The value to set
     * @return mixed The value of $data
     * @since 1.2.0
     */
    public function set_field ($name, $data = null) {
        $this->data[$name] = $data;
        return $this->data[$name];
    }

    /**
     * Setup fields for rabit hole
     */
    private function feed_rabit ($fields) {
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
    private function recurse_rabit_hole ($pod, $fields, $joined = 't', $depth = 0) {
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
                $search = "`{$field_joined}`.`{$on}` = %d";
                if ('text' == $this->search_mode) {
                    $val = pods_unsanitize(pods_var($field_joined, 'get'));
                    $on = $this->rabit_hole[$pod][$field]['name'];
                    $search = "`{$field_joined}`.`{$on}` = %s";
                }
                elseif ('text_like' == $this->search_mode) {
                    $val = '%' . like_escape(pods_unsanitize(pods_var($field_joined, 'get'))) . '%';
                    $on = $this->rabit_hole[$pod][$field]['name'];
                    $search = "`{$field_joined}`.`{$on}` LIKE %s";
                }
                $this->search_where .= " AND {$search} ";
                $this->search_prepare[] = $val;
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
    private function rabit_hole ($pod, $fields = null) {
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
     * Search and filter records
     *
     * @param array $params An associative array of parameters
     * @since 2.0.0
     */
    public function find ($params, $limit = 15, $where = null, $sql = null) {
        if (empty($this->pod) || empty($this->pod_id))
            return pods_error('Pod not found', $this);

        global $wpdb;
        $this->traverse = array();
        $defaults = array(// query-related
                          'select' => '`t`.*',
                          'join' => '',
                          'where' => null,
                          'groupby' => '',
                          'having' => '',
                          'orderby' => "`t`.`{$this->column_id}` DESC",
                          'limit' => $this->limit,
                          'sql' => null,

                          // functionality-related
                          'search' => $this->search,
                          'search_across' => true,
                          'search_across_picks' => false,
                          'search_var' => $this->search_var,
                          'search_mode' => $this->search_mode,
                          'traverse' => $this->traverse,
                          'page' => $this->page,
                          'pagination' => $this->pagination,
                          'calc_found_rows' => $this->calc_found_rows,
                          'count_found_rows' => $this->count_found_rows);

        if (!is_array($params) && !is_object($params)) {
            $params = array('orderby' => $params,
                            'limit' => $limit,
                            'where' => $where,
                            'sql' => $sql);
        }

        $params = (object) array_merge($defaults, (array) $params);

        $this->search = $params->search = (boolean) $params->search;
        $this->search_var = $params->search_var;
        $this->search_mode = $params->search_mode = (in_array($params->search_mode, array('int', 'text', 'test_like')) ? $params->search_mode : 'int');
        $this->traverse = $params->traverse = (array) $params->traverse;

        $this->page = $params->page = max(pods_absint($params->page), 1);
        $this->pagination = $params->pagination = (bool) $params->pagination;
        if (false === $this->pagination)
            $this->page = $params->page = 1;

        $this->calc_found_rows = $params->calc_found_rows = (boolean) $params->calc_found_rows;
        $this->count_found_rows = $params->count_found_rows = (boolean) $params->count_found_rows;
        $sql = $params->sql;
        if (true === $this->count_found_rows && empty($sql))
            $this->calc_found_rows = $params->calc_found_rows = false;

        $params->where = str_replace('%', '%%', $params->where);

        $this->limit = $params->limit = pods_absint($params->limit, false);

        $sql_builder = false;
        $this->search_where = '';
        if (empty($sql)) {
            $sql_builder = true;

            if (0 < $params->limit)
                $params->limit = ($params->limit * ($this->page - 1)) . ',' . $params->limit;

            if (false !== $this->search) {
                $this->search_where = array();

                $search = '%' . like_escape($_GET[$this->search_var]) . '%';
                if (0 < strlen($search)) {
                    $search_where = array();
                    if (false === $params->search_across)
                        $search_where[] = "`t`.`{$this->column_index}` LIKE %s";
                    else
                        $search_where[] = "`t`.* LIKE %s";
                    $this->search_prepare[] = $search;
                    foreach ((array) $this->fields as $column) {
                        if (false !== $params->search_across_picks) {
                            if('pod' == $column['pick_object']) {
                                $name_field = $this->api->load_column(array('name' => 'name'));
                                if (false !== $name_field)
                                    $search_where[] = "`{$column['name']}`.`name` LIKE %s";
                                else
                                    $search_where[] = "`{$column['name']}`.* LIKE %s";
                                $this->search_prepare[] = $search;
                            }
                            elseif('post_type' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`post_title` LIKE %s";
                                $search_where[] = "`{$column['name']}`.`post_content` LIKE %s";
                                $this->search_prepare[] = $search;
                                $this->search_prepare[] = $search;
                            }
                            elseif('taxonomy' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`name` LIKE %s";
                                $this->search_prepare[] = $search;
                            }
                            elseif('user' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`display_name` LIKE %s";
                                $search_where[] = "`{$column['name']}`.`user_email` LIKE %s";
                                $search_where[] = "`{$column['name']}`.`user_login` LIKE %s";
                                $this->search_prepare[] = $search;
                                $this->search_prepare[] = $search;
                                $this->search_prepare[] = $search;
                            }
                            elseif('comment' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`comment_author` LIKE %s";
                                $search_where[] = "`{$column['name']}`.`comment_content` LIKE %s";
                                $this->search_prepare[] = $search;
                                $this->search_prepare[] = $search;
                            }
                            elseif('table' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`name` = LIKE %s";
                                $this->search_prepare[] = $search;
                            }
                        }
                        if (isset($_GET['filter_'.$column['name']]) || isset($_GET[$column['name']])) {
                            if (isset($_GET['filter_'.$column['name']]))
                                $id = pods_absint(pods_var('filter_' . $column['name'], 'get'));
                            else
                                $id = pods_absint(pods_var($column['name'], 'get')); // deprecated
                            if (empty($id))
                                continue;
                            if('pod' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`id` = %d";
                                $this->search_prepare[] = $id;
                            }
                            elseif('post_type' == $column['pick_object'] || 'user' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`ID` = %d";
                                $this->search_prepare[] = $id;
                            }
                            elseif('taxonomy' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`term_id` = %d";
                                $this->search_prepare[] = $id;
                            }
                            elseif('comment' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`comment_ID` = %d";
                                $this->search_prepare[] = $id;
                            }
                            elseif('table' == $column['pick_object']) {
                                $search_where[] = "`{$column['name']}`.`id` = %d";
                                $this->search_prepare[] = $id;
                            }
                        }
                    }
                    $search_where = implode(' OR ', $search_where);
                    $this->search_where[] = "({$search_where})";
                }
            }
            $this->search_where = implode(' AND ', $this->search_where);

            // Add "`t`." prefix to $orderby if needed
            if (!empty($params->orderby) && false === strpos($params->orderby, ',') && false === strpos($params->orderby, '(') && false === strpos($params->orderby, '.')) {
                if (false !== strpos($params->orderby, ' ASC'))
                    $params->orderby = '`t`.`' . str_replace(array('`', ' ASC'), '', $params->orderby) . '` ASC';
                elseif (false !== strpos($params->orderby, ' ASC'))
                    $params->orderby = '`t`.`' . str_replace(array('`', ' DESC'), '', $params->orderby) . '` DESC';
            }

            $haystack = str_replace(array('(', ')'), ' ', preg_replace('/\s/', ' ', "{$params->select} {$this->search_where} {$params->where} {$params->groupby} {$params->having} {$params->orderby}"));

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
                $params->select = preg_replace($find, $replace, $params->select);
                $this->search_where = preg_replace($find, $replace, $this->search_where);
                $params->where = preg_replace($find, $replace, $params->where);
                $params->groupby = preg_replace($find, $replace, $params->groupby);
                $params->having = preg_replace($find, $replace, $params->having);
                $params->orderby = preg_replace($find, $replace, $params->orderby);

                if (!empty($found))
                    $joins = $this->rabit_hole($this->datatype, $found);
                elseif (false !== $this->search)
                    $joins = $this->rabit_hole($this->pod);
            }
            if (0 < strlen($params->join)) {
                $joins[] = "
            {$params->join}";
            }
            $params->join = apply_filters('pods_find_join', implode(' ', $joins), $params, $this);

            $params->table = false;
            switch ($this->pod_data['type']) {
                case 'pod':
                    $params->table = "@wp_pods_tbl_{$this->pod}";
                    break;
                case 'post_type':
                    $params->table = '@wp_posts';
                    $params->where = "`t`.`post_type` = %s
        {$params->where}";
                    $this->search_prepare[] = $this->pod_data['object'];
                    break;
                case 'taxonomy':
                    $params->table = '@wp_terms';
                    $params->join = "`@wp_term_taxonomy` AS `tx` ON `tx`.`term_id` = `t`.`term_id`
        {$params->join}";
                    $params->where = "`t`.`taxonomy` = %s
        {$params->where}";
                    $this->search_prepare[] = $this->pod_data['object'];
                    break;
                case 'user':
                    $params->table = '@wp_users';
                    break;
                case 'comment':
                    $params->table = '@wp_comments';
                    $params->where = "`t`.`comment_type` = %s
        {$params->where}";
                    $this->search_prepare[] = $this->pod_data['object'];
                    break;
                case 'table':
                    $params->table = "{$this->pod_data['object']}";
                    break;
                default:
                    return false;
                    break;
            }

            $calc_found_rows = '';
            if (false !== $this->calc_found_rows)
                $calc_found_rows = 'SQL_CALC_FOUND_ROWS';
            $sql = "
            SELECT
                {$calc_found_rows} DISTINCT {$params->select}
            FROM
                `{$params->table}` AS `t`";
            if (!empty($params->join)) {
                $sql .= "
            {$params->join}";
            }
            if (!empty($this->search_where) || !empty($params->where)) {
                $sql .= "
            WHERE";
                if (!empty($this->search_where)) {
                    $sql .= "
                {$this->search_where}";
                    if (!empty($this->where))
                        $sql .= " AND";
                }
                if (!empty($params->where)) {
                    $sql .= "
                {$params->where}";
                }
            }
            if (!empty($params->groupby)) {
                $sql .= "
            GROUP BY {$params->groupby}";
            }
            if (!empty($params->having)) {
                $sql .= "
            HAVING
                {$params->having}";
            }
            if (!empty($params->orderby)) {
                $sql .= "
            ORDER BY {$params->orderby}";
            }
            if (!empty($params->limit)) {
                $sql .= "
            LIMIT {$params->limit}";
            }
            $sql = array($sql, $this->search_prepare);
        }
/*
        // Get this pod's fields
        $i = 0;
        foreach ($this->fields as $column) {
            if (!in_array($column['type'], array('file', 'pick')))
                continue;

            $field_id = $column['id'];
            $field_name = $column['name'];
            $field_type = $column['type'];

            // Performance improvement - only use PICK columns mentioned in ($select, $where, $orderby)
            $haystack = "$select $where $orderby";
            if (false === strpos($haystack, $field_name . '.') && false === strpos($haystack, "`{$field_name}`."))
                continue;

            $i++;

            $pick_object = $column['pick_object'];
            $pick_val = $column['pick_val'];
            if ('file' == $field_type) {
                $pick_object = 'post_type';
                $pick_val = 'attachment';
            }

            $pick_table = $pick_join = $pick_where = '';
            $pick_column_id = 'id';
            switch ($pick_object) {
                case 'pod':
                    $pick_table = "@wp_pods_tbl_{$this->pod}";
                    $pick_column_id = 'id';
                    break;
                case 'post_type':
                    $pick_table = '@wp_posts';
                    $pick_column_id = 'ID';
                    $pick_where = " AND t.`post_type` = '{$this->pod_data['object']}'";
                    break;
                case 'taxonomy':
                    $pick_table = '@wp_terms';
                    $pick_column_id = 'term_id';
                    $pick_join = "`@wp_term_taxonomy` AS tx{$i} ON tx{$i}.`term_id` = r{$i}.`related_item_id` AND tx{$i}.`taxonomy` = '{$this->pod_data['object']}'";
                    $pick_where = " AND `{$field_name}`.`term_id` = tx{$i}.`term_id`";
                    break;
                case 'user':
                    $pick_table = '@wp_users';
                    $pick_column_id = 'ID';
                    break;
                case 'comment':
                    $pick_table = '@wp_comments';
                    $pick_column_id = 'comment_ID';
                    $pick_where = " AND t.`comment_type` = '{$this->pod_data['object']}'";
                    break;
                case 'table':
                    $pick_table = "{$this->pod_data['object']}";
                    $pick_column_id = 'id';
                    break;
            }
            $the_join = "LEFT JOIN `@wp_pod_rel` AS r{$i} ON r{$i}.`field_id` = `{$field_id}` AND r{$i}.`item_id` = t.`{$this->column_id}`
                        {$pick_join} LEFT JOIN `{$pick_table}` AS `{$field_name}` ON `{$field_name}`.`{$pick_column_id}` = r{$i}.`related_item_id` {$pick_where}";
            $join .= ' '.apply_filters('pods_find_the_join', $the_join, $i, $column, $pick_table, $pick_join, $pick_where, $params, $this).' ';
        }
        if (!empty($params->join))
            $join .= " {$params->join} ";
        $join = apply_filters('pods_find_join', $join, $params, $this);
*/

        $this->sql = apply_filters('pods_find', $sql, $params, $this);
        $this->results = pods_query($this->sql, $this);
        $this->row_number = -1;
        $this->zebra = false;
        $this->id = null;
        $this->total = $this->total_found = pods_absint(count($this->results));
        if (false !== $this->calc_found_rows) {
            $found_rows = pods_query("SELECT FOUND_ROWS() AS `found_rows`", $this);
            $found_rows = $found_rows[0]->found_rows;
            $this->total_found = pods_absint($found_rows);
        }
        elseif (false !== $this->count_found_rows && false !== $sql_builder) {
            $sql = "
            SELECT
                COUNT(*) AS `found_rows`
            FROM
                {$params->table} AS t";
            if (!empty($params->join)) {
                $sql .= "
            {$params->join}";
            }
            if (!empty($this->search_where) || !empty($params->where)) {
                $sql .= "
            WHERE";
                if (!empty($this->search_where)) {
                    $sql .= "
                {$this->search_where}";
                    if (!empty($this->where))
                        $sql .= " AND";
                }
                if (!empty($params->where)) {
                    $sql .= "
                {$params->where}";
                }
            }
            if (!empty($params->groupby)) {
                $sql .= "
            GROUP BY {$params->groupby}";
            }
            if (!empty($params->having)) {
                $sql .= "
            HAVING
                {$params->having}";
            }
            $sql = array($sql, $this->search_prepare);
            $this->total_found = pods_absint(pods_query($sql, $this));
        }
    }

    /**
     * Fetch a row of results from the DB
     *
     * @since 2.0.0
     */
    public function fetch ($id = null) {
        if (null !== $id) {
            if (null === $this->pod || null === $this->pod_id)
                return pods_error('Pod is required');

            $id = pods_sanitize($id);
            $check = pods_absint($id);
            if (0 < $check) {
                $sql = "SELECT * FROM `@wp_pods_tbl_{$this->pod}` WHERE `{$this->column_id}` = %d LIMIT 1";
                $sql = array($sql, array($check));
                $result = pods_query($sql, $this);
            }
            if (empty($check) || empty($result)) {
                // Get the slug column
                $sql = "SELECT `name` FROM `@wp_pods_fields` WHERE `pod_id` = %d AND `type` = 'slug' LIMIT 1";
                $sql = array($sql, array($this->pod_id));
                $slug_field = pods_query($sql, $this);
                if (!empty($slug_field)) {
                    $field_name = pods_sanitize($slug_field[0]->name);
                    $sql = "SELECT * FROM `@wp_pods_tbl_{$this->pod}` WHERE `{$field_name}` = %s LIMIT 1";
                    $sql = array($sql, array($id));
                    $result = pods_query($sql, $this);
                }
            }

            $this->data = false;
            if (!empty($result)) {
                $this->data = get_object_vars($result[0]);
                $this->data['type'] = $this->pod;
                $this->data['pod_id'] = $this->data[$this->column_id]; // deprecated
                $this->row_number = 0;
                $this->zebra = false;
                $this->total = $this->total_rows = 1;
            }
            return $this->data;
    }
        if (!isset($this->results[$this->row_number + 1]))
            return false;
        $this->row_number++;
        $this->data = $this->results[$this->row_number];
        if (true === $this->zebra)
            $this->zebra = false;
        else
            $this->zebra = true;
        return $this->data;
    }

    /**
     * (Re)set the MySQL result pointer
     *
     * @since 2.0.0
     */
    public function reset ($row = 0) {
        if (isset($this->results[$row]))
            $this->data = $this->results[$row];
        else
            $this->data = false;
        $this->zebra = false;
        return $this->data;
    }

    /**
     * Fetch the total row count returned
     *
     * @return int Number of rows returned by find()
     * @since 2.0.0
     */
    public function total () {
        return (int) $this->total;
    }

    /**
     * Fetch the total row count total
     *
     * @return int Number of rows found by find()
     * @since 2.0.0
     */
    public function total_found () {
        return (int) $this->total_found;
    }

    /**
     * Fetch the zebra switch
     *
     * @return bool Zebra state
     * @since 1.12
     */
    public function zebra () {
        return (boolean) $this->zebra;
    }

    /**
     * Lookup values
     *
     * $params['ids'] A comma-separated string or array of item IDs to include
     * $params['exclude'] A comma-separated string or array of item IDs to exclude
     * $params['selected_ids'] A comma-separated string or array of item IDs (for 'active' index)
     * $params['table'] The database table alias
     * $params['column'] (optional) The name of the primary ID column
     * $params['column_name'] (optional) The name of the column (for 'active' index)
     * $params['select'] (optional) The columns to select
     * $params['join'] (optional) MySQL "JOIN" clause
     * $params['orderby'] (optional) MySQL "ORDER BY" clause
     * $params['where'] (optional) MySQL "WHERE" clause
     * $params['sql'] (optional) Custom MySQL SQL Query
     *
     * @param array $params An associative array of parameters
     * @return array $data Associative array of table column values
     * @since 2.0.0
     */
    private function lookup ($params) {
        $params = (object) $params;

        $ids = '';
        if (!empty($params->ids))
            $ids = $params->ids;
        if (!empty($ids)) {
            if (!is_array($ids))
                $ids = explode(',', $ids);
            foreach ($ids as $k => $id) {
                $ids[$k] = pods_absint($id);
            }
        }
        $ids = implode(', ', $ids);

        $exclude = '';
        if (!empty($params->exclude))
            $exclude = $params->exclude;
        if (!empty($exclude)) {
            if (!is_array($exclude))
                $exclude = explode(',', $exclude);
            foreach ($exclude as $k => $id) {
                $exclude[$k] = pods_absint($id);
            }
        }
        $exclude = implode(', ', $exclude);

        $table = '';
        if (isset($params->table) && !empty($params->table))
            $table = $params->table;

        $column = 'id';
        switch ($table) {
            case '@wp_posts':
            case '@wp_users':
                $column = 'ID';
            case '@wp_terms':
                $column = '`t`.`term_id`';
            case '@wp_comments':
                $column = 'comment_ID';
        }
        if (isset($params->column) && !empty($params->column))
            $column = pods_sanitize($params->column);
        if (false === strpos($column, '`') && false === strpos($column, '.'))
            $column = "`{$column}`";
        
        if (isset($params->select))
            $select = $params->select;
        if (empty($select)) {
            $select = 'name';
            switch ($table) {
                case '@wp_posts':
                    $select = 'post_title';
                case '@wp_terms':
                    $select = 't`.`name';
                case '@wp_users':
                    $select = 'display_name';
                case '@wp_comments':
                    $select = 'comment_date';
            }
        }
        if (false === strpos($select, '`') && false === strpos($select, '.') && false === strpos($select, '*'))
            $select = "`{$select}`";

        $join = '';
        if (isset($params->join) && !empty($params->join))
            $join = $params->join;
        if ('@wp_terms' == $table)
            $join .= ' `t` INNER JOIN `{@wp_term_taxonomy}` `tx` ON `tx`.`term_id` = `t`.`term_id`';

        $orderby = "{$column} ASC";
        if ('@wp_posts' == $table)
            $orderby = "`menu_order` ASC, {$orderby}";
        if (isset($params->orderby))
            $orderby = $params->orderby;
        if (!empty($orderby))
            $orderby = " ORDER BY {$orderby} ";

        $where = array();
        if (!empty($ids))
            $where[] = "{$column} IN ({$ids})";
        if (!empty($exclude))
            $where[] = "{$column} NOT IN ({$exclude})";
        if (isset($params->where))
            $where = $params->where;
        if (is_array($where))
            $where = implode(' AND ', $where);
        if (!empty($where))
            $where = " WHERE {$where} ";
        else
            $where = '';

        $sql = "SELECT {$select} FROM `{$table}` {$join} {$where} {$orderby}";
        if (isset($params->sql) && !empty($params->sql))
            $sql = $params->sql;
        elseif (empty($table))
            return pods_error('Table or SQL param required', $this);
        $result = pods_query($sql, $this);

        $data = array();
        // Put all related items into an array
        if (!empty($result)) {
            $column = explode('.', $column);
            if (isset($column[1]))
                $column = $column[1];
            else
                $column = $column[0];
            $column = trim($column, ' `');
            $select = explode('.', $select);
            if (isset($select[1]))
                $select = $select[1];
            else
                $select = $select[0];
            $select = trim($select, ' `');
            foreach ($result as $row) {
                $data[$row->{$column}] = get_object_vars($row);
                if (isset($params->column_name)) {
                    $data[$row->{$column}]['active'] = false;
                    if(isset($_GET['filter_'.$params->column_name]) && $row->{$column} == $_GET['filter_'.$params->column_name])
                        $data[$row->{$column}]['active'] = true;
                    if(isset($_GET[$params->column_name])) { // deprecated
                        if ('int' == $this->search_mode && isset($row->{$column}) && $row->{$column} == $_GET[$params->column_name])
                            $data[$row->{$column}]['active'] = true;
                        elseif ('text' == $this->search_mode && isset($row->{$select}) && $row->{$select} == $_GET[$params->column_name])
                            $data[$row->{$column}]['active'] = true;
                    }
                }
            }
        }

        $data = apply_filters('pods_lookup', $data, $params, $this);
        $data = apply_filters("pods_lookup_{$this->pod}", $data, $params, $this);

        return $data;
    }

    /**
     * Find items related to a parent field
     *
     * $params['field_id'] The field ID
     * $params['pod_id'] The Pod ID
     * $params['ids'] A comma-separated string or array of item IDs
     *
     * @param array $params An associative array of parameters
     * @since 1.2.0
     */
    private function lookup_row_ids ($params) {
        $params = (object) pods_sanitize($params);

        $field_id = pods_absint($params->field_id);

        $pod_id = $this->pod_id;
        if (isset($params->pod_id))
            $pod_id = pods_absint($params->pod_id);

        $ids = 0;
        if (isset($params->ids))
            $ids = $params->ids;
        if (!is_array($ids))
            $ids = explode(',', $ids);
        foreach ($ids as $k => $id) {
            $ids[$k] = pods_absint($id);
        }
        $ids = implode(', ', $ids);

        $sql = "SELECT `related_item_id` FROM `@wp_pods_rel` WHERE `pod_id` = %d AND `field_id` = %d AND `item_id` IN ({$ids}) ORDER BY `weight`";
        $sql = array($sql, array($pod_id, $field_id));
        $result = pods_query($sql, $this);
        if (!empty($result)) {
            $data = array();
            foreach ($result as $row) {
                $data[] = (int) $row->related_item_id;
            }
            return implode(',', $data);
        }
        return false;
    }

    /**
     * Get pod or category dropdown values
     *
     * $params['ids'] A comma-separated string or array of item IDs to include
     * $params['exclude'] A comma-separated string or array of item IDs to exclude
     * $params['selected_ids'] A comma-separated string or array of item IDs (for 'active' index)
     * $params['table'] The database table alias
     * $params['column'] (optional) The name of the primary ID column
     * $params['column_name'] (optional) The name of the column (for 'active' index)
     * $params['select'] (optional) The columns to select
     * $params['join'] (optional) MySQL "JOIN" clause
     * $params['orderby'] (optional) MySQL "ORDER BY" clause
     * $params['where'] (optional) MySQL "WHERE" clause
     * $params['sql'] (optional) Custom MySQL SQL Query
     *
     * @param array $params An associative array of parameters
     * @return mixed Anything returned by the helper
     * @since 1.x
     */
    private function get_dropdown_values ($params) {
        $params = (object) $params;

        $values = $this->lookup($params);

        $values = apply_filters('pods_get_dropdown_values', $values, $params, $this);
        $values = apply_filters("pods_get_dropdown_values_{$this->pod}", $values, $params, $this);

        return $values;
    }

    /**
     * Display the pagination controls
     *
     * @since 2.0.0
     */
    public function pagination ($params = null) {
        $defaults = array('label' => 'Go to page:');
        if (!empty($params) && is_array($params))
            $params = array_merge($defaults, $params);
        else
            $params = $defaults;
        $params = (object) $params;

        $output = '';
        if (0 < $this->rpp && $this->rpp < $this->total_found() && false !== $this->pagination) {
            ob_start();
            include PODS_DIR . 'ui/pagination.php';
            $output = ob_get_clean();
        }

        $output = apply_filters('pods_pagination', $output, $params, $this);
        $output = apply_filters("pods_pagination_{$this->pod}", $output, $params, $this);

        return $output;
    }

    /**
     * Display the list filters
     *
     * @since 2.0.0
     */
    public function filters ($params = null) {
        $defaults = array('filters' => null,
                            'label' => 'Filter',
                            'action' => '',
                            'show_textbox' => true);
        if (!empty($params) && is_array($params))
            $params = array_merge($defaults, $params);
        else
            $params = $defaults;
        $params = (object) $params;

        ob_start();
        include PODS_DIR . 'ui/list_filters.php';
        $output = ob_get_clean();

        $output = apply_filters('pods_filters', $output, $params, $this);
        $output = apply_filters("pods_filters_{$this->pod}", $output, $params, $this);

        return $output;
    }

    /**
     * Run a helper within a Pod Page or WP Template
     *
     * $params['helper'] string Helper name
     * $params['value'] string Value to run Helper on
     * $params['name'] string Column name
     *
     * @param array $params An associative array of parameters
     * @return mixed Anything returned by the helper
     * @since 2.0.0
     */
    public function helper ($helper, $value = null, $name = null) {
        $params = array('helper' => $helper,
                        'value' => $value,
                        'name' => $name);
        if (is_array($helper))
            $params = array_merge($params, $helper);
        $params = (object) $params;

        if (empty($params->helper))
            return pods_error('Helper name required', $this);

        if (!isset($params->value))
            $params->value = null;
        if (!isset($params->name))
            $params->name = null;

        ob_start();

        do_action('pods_pre_pod_helper', $params, $this);
        do_action("pods_pre_pod_helper_{$params->helper}", $params, $this);

        $helper = $this->api->load_helper(array('name' => $params->helper));
        if (!empty($helper) && !empty($helper['code'])) {
            if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                eval("?>{$helper['code']}");
            else
                echo $helper['code'];
        }
        elseif (function_exists("{$params->helper}")) {
            $function_name = (string) $params->helper;
            echo $function_name($params->value, $params->name, $params, $this);
        }

        do_action('pods_post_pod_helper', $params, $this);
        do_action("pods_post_pod_helper_{$params->helper}", $params, $this);

        return apply_filters('pods_helper', ob_get_clean(), $params, $this);
    }

    /**
     * Display the page template
     *
     * @since 2.0.0
     */
    public function template ($template_name, $code = null) {
        ob_start();

        do_action('pods_pre_template', $template_name, $code, $this);
        do_action("pods_pre_template_{$template_name}", $template_name, $code, $this);

        if (empty($code)) {
            $template = $this->api->load_template(array('name' => $template_name));
            if (!empty($template) && !empty($template['code']))
                $code = $template['code'];
            elseif (function_exists("{$template_name}"))
                $code = $template_name($this);
        }

        $code = apply_filters('pods_template', $code, $template_name, $this);
        $code = apply_filters("pods_template_{$template_name}", $code, $template_name, $this);

        if (!empty($code)) {
            // Only detail templates need $this->id
            if (empty($this->id)) {
                while ($this->fetch()) {
                    echo $this->do_template($code);
                }
            }
            else
                echo $this->do_template($code);
        }

        do_action('pods_post_template', $template_name, $code, $this);
        do_action("pods_post_template_{$template_name}", $template_name, $code, $this);

        return ob_get_clean();
    }

    /**
     * Parse a template string
     *
     * @param string $code The template string to parse
     * @since 1.8.5
     */
    public function do_template ($code) {
        ob_start();
        if ((!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL))
            eval("?>$code");
        else
            echo $code;
        $out = ob_get_clean();
        $out = preg_replace_callback("/({@(.*?)})/m", array($this, "do_magic_tags"), $out);
        return apply_filters('pods_do_template', $out, $code, $this);
    }

    /**
     * Replace magic tags with their values
     * @param string $tag The magic tag to evaluate
     * @since 1.x
     */
    private function do_magic_tags ($tag) {
        $tag = trim($tag, ' {@}');
        $tag = explode(',', $tag);
        if (empty($tag) || !isset($tag[0]) || 0 < strlen(trim($tag[0])))
            return;
        foreach ($tag as $k => $v) {
            $tag[$k] = trim($v);
        }
        $field_name = $tag[0];
        if ('detail_url' == $field_name)
            $value = get_bloginfo('url') . '/' . $this->do_template($this->detail_page);
        elseif ('type' == $field_name)
            $value = $this->pod;
        else
            $value = $this->field($field_name);
        $helper_name = $before = $after = '';
        if (isset($tag[1]) && !empty($tag[1])) {
            $helper_name = $tag[1];
            $value = $this->helper($helper_name, $value, $field_name);
        }
        if (isset($tag[2]) && !empty($tag[2]))
            $before = $tag[2];
        if (isset($tag[3]) && !empty($tag[3]))
            $after = $tag[3];

        $value = apply_filters('pods_do_magic_tags', $value, $field_name, $helper_name, $before, $after);
        if (null !== $value && false !== $value)
            return $before . $value . $after;
        return;
    }

    //
    // DEPRECATED FUNCTIONS IN 2.0.0
    //

    /**
     * Display HTML for all datatype fields
     *
     * @deprecated deprecated since 2.0.0
     */
    public function showform ($id = null, $public_columns = null, $label = 'Save changes') {
        pods_deprecated('Pods::showform', '2.0.0');
        $pods_cache = PodCache::instance();

        $pod = $this->pod;
        $pod_id = $this->pod_id;
        $this->type_counter = array();

        $where = '';
        if (!empty($public_columns)) {
            $attributes = array();
            foreach ($public_columns as $key => $value) {
                if (is_array($public_columns[$key]))
                    $attributes[$key] = $value;
                else
                    $attributes[$value] = array();
            }
        }

        $fields = $this->fields;

        // Re-order the fields if a public form
        if (!empty($attributes)) {
            $fields = array();
            foreach ($attributes as $key => $value) {
                if (isset($this->fields[$key]))
                    $fields[$key] = $this->fields[$key];
            }
        }

        do_action('pods_showform_pre', $pod_id, $public_columns, $label, $this);

        foreach ($fields as $key => $field) {
            if (!is_array($field))
                continue;

            // Pass options so they can be manipulated via form
            $field = array_merge($field['options'], $field);

            // Replace field attributes with public form attributes
            if (!empty($attributes) && is_array($attributes[$key]))
                $field = array_merge($field, $attributes[$key]);

            // Replace the input helper name with the helper code
            if (!empty($field['input_helper'])) {
                $helper = $this->api->load_helper(array('name' => $field['input_helper']));
                $field['input_helper'] = '';
                if (!empty($helper))
                    $field['input_helper'] = $helper['code'];
            }

            if (empty($field['label']))
                $field['label'] = ucwords($key);

            if (1 == $field['required'])
                $field['label'] .= ' <span class="red">*</span>';

            if (!empty($field['pick_val'])) {
                $selected_ids = array();
                $pick_object = $field['pick_object'];
                $pick_val = $field['pick_val'];
                if ('pod' == $pick_object) {
                    $pick_pod = $this->api->load_pod(array('name' => $pick_val));
                    $pick_object = $pick_pod['type'];
                    $pick_val = $pick_pod['object'];
                }
                $pick_table = $pick_join = $pick_where = '';
                $pick_column_id = 'id';
                switch ($pick_object) {
                    case 'pod':
                        $pick_table = "@wp_pods_tbl_{$pick_val}";
                        $pick_column_id = 'id';
                        break;
                    case 'post_type':
                        $pick_table = '@wp_posts';
                        $pick_column_id = 'ID';
                        $pick_where = "t.`post_type` = '{$pick_val}'";
                        break;
                    case 'taxonomy':
                        $pick_table = '@wp_terms';
                        $pick_column_id = 'term_id';
                        $pick_join = "`@wp_term_taxonomy` AS tx ON tx.`term_id` = t.`term_id";
                        $pick_where = "tx.`taxonomy` = '{$pick_val}' AND tx.`taxonomy` IS NOT NULL";
                        break;
                    case 'user':
                        $pick_table = '@wp_users';
                        $pick_column_id = 'ID';
                        break;
                    case 'comment':
                        $pick_table = '@wp_comments';
                        $pick_column_id = 'comment_ID';
                        $pick_where = "t.`comment_type` = '{$pick_val}'";
                        break;
                    case 'table':
                        $pick_table = "{$pick_val}";
                        $pick_column_id = 'id';
                        break;
                }

                $sql = "SELECT `related_item_id` FROM `@wp_pods_rel` WHERE `item_id` = %d AND `field_id` = %d";
                $sql = array($sql, array($id, $field['id']));
                $result = pods_query($sql, $this);
                foreach ($result as $row) {
                    $selected_ids[] = $row->related_item_id;
                }

                // Use default values for public forms
                if (empty($selected_ids) && !empty($field['default'])) {
                    $default_ids = $field['default'];
                    if (!is_array($field['default']))
                        $default_ids = explode(',', $default_ids);
                    foreach ($default_ids as $default_id) {
                        $default_id = pods_absint($default_id);
                        if (0 < $default_id)
                            $selected_ids[] = $default_id;
                    }
                }

                // If the PICK column is unique, get values already chosen
                $exclude = false;
                if (1 == $field['unique']) {
                    $unique_where = (empty($id)) ? '' : " AND `item_id` != %d";
                    $sql = "SELECT `related_item_id` FROM `@wp_pods_rel` WHERE `field_id` = %d {$unique_where}";
                    $sql = array($sql, array($field['id']));
                    if (!empty($id))
                        $sql[1][] = $id;
                    $result = pods_query($sql, $this);
                    if (!empty($result)) {
                        $exclude = array();
                        foreach ($result as $row) {
                            $exclude[] = (int) $row->related_item_id;
                        }
                        $exclude = implode(',', $exclude);
                    }
                }

                if (!empty($field['options']['pick_filter']))
                    $pick_where .= ' AND ' . $field['options']['pick_filter'];

                $params = array(
                    'exclude' => $exclude,
                    'selected_ids' => $selected_ids,
                    'table' => $pick_table,
                    'column' => $pick_column_id,
                    'join' => $pick_join,
                    'orderby' => $field['options']['pick_orderby'],
                    'where' => $pick_where
                );
                $this->data[$key] = $this->get_dropdown_values($params);
            }
            else {
                // Set a default value if no value is entered
                if (!isset($this->data[$key]) || (null === $this->data[$key] || false === $this->data[$key])) {
                    if (!empty($field['default']))
                        $this->data[$key] = $field['default'];
                    else
                        $this->data[$key] = null;
                }
            }
            $this->build_field_html($field);
        }

        $uri_hash = wp_hash($_SERVER['REQUEST_URI']);

        $save_button_atts = array(
            'type' => 'button',
            'class' => 'button btn_save',
            'value' => $label,
            'onclick' => "saveForm({$pods_cache->form_count})"
        );
        $save_button_atts = apply_filters('pods_showform_save_button_atts', $save_button_atts, $this);
        $atts = '';
        foreach ($save_button_atts as $att => $value) {
            $atts .= ' ' . esc_attr($att) . '="' . esc_attr($value) . '"';
        }
        $save_button = '<input ' . $atts . '/>';
?>
    <div>
    <input type="hidden" class="form num id" value="<?php echo $id; ?>" />
    <input type="hidden" class="form txt pod" value="<?php echo $pod; ?>" />
    <input type="hidden" class="form txt pod_id" value="<?php echo $pod_id; ?>" />
    <input type="hidden" class="form txt form_count" value="<?php echo $pods_cache->form_count; ?>" />
    <input type="hidden" class="form txt token" value="<?php echo pods_generate_key($pod, $uri_hash, $public_columns, $pods_cache->form_count); ?>" />
    <input type="hidden" class="form txt uri_hash" value="<?php echo $uri_hash; ?>" />
    <?php echo apply_filters('pods_showform_save_button', $save_button, $save_button_atts, $this); ?>
    </div>
<?php
        do_action('pods_showform_post', $pod_id, $public_columns, $label, $this);
    }

    /**
     * Build public input form
     *
     * @deprecated deprecated since 2.0.0
     */
    public function publicForm ($public_columns = null, $label = 'Save Changes', $thankyou_url = null) {
        pods_deprecated('Pods::publicForm', '2.0.0', 'Pods::form');
        include PODS_DIR . 'ui/input_form.php';
    }

    /**
     * Build HTML for a single field
     *
     * @deprecated deprecated since 2.0.0
     */
    public function build_field_html ($field) {
        pods_deprecated('Pods::build_field_html', '2.0.0');
        include PODS_DIR . 'ui/input_fields.php';
    }

    /**
     * Fetch a row of results from the DB
     *
     * @since 1.2.0
     * @deprecated deprecated since 2.0.0
     */
    public function fetchRecord () {
        pods_deprecated('Pods::fetchRecord', '2.0.0', 'Pods::fetch');
        return $this->fetch();
    }

    /**
     * Return a field's value(s)
     *
     * @param string $name The field name
     * @param string $orderby (optional) The orderby string, for PICK fields
     * @since 1.2.0
     * @deprecated deprecated since version 2.0.0
     */
    public function get_field ($name, $orderby = null) {
        pods_deprecated('Pods::get_field', '2.0.0', 'Pods::field');
        return $this->field(array('name' => $name, 'orderby' => $orderby));
    }

    /**
     * Get the current item's pod ID from its datatype ID and tbl_row_id
     *
     * @todo pod_id should NEVER be needed by users - fix code so tbl_row_id is used instead
     * @return int The ID from the wp_pod table
     * @since 1.2.0
     * @deprecated deprecated since version 2.0.0
     */
    public function get_pod_id () {
        pods_deprecated('Pods::get_pod_id', '2.0.0');
        if (!empty($this->data))
            return $this->data[$this->column_id];
        return false;
    }

    /**
     * Search and filter records
     *
     * @since 1.x
     * @deprecated deprecated since version 2.0.0
     */
    public function findRecords ($orderby = null, $rows_per_page = 15, $where = null, $sql = null) {
        pods_deprecated('Pods::findRecords', '2.0.0', 'Pods::find');
        if (null == $orderby)
            $orderby = "t.`{$this->column_id}` DESC";
        $params = array('select' => 't.*',
                            'join' => '',
                            'where' => $where,
                            'orderby' => $orderby,
                            'limit' => $rows_per_page,
                            'page' => $this->page,
                            'search' => $this->search,
                            'search_across' => true,
                            'search_across_picks' => false,
                            'sql' => $sql);
        if (is_array($orderby)) {
            $params = (object) array_merge($params, $orderby);
            $this->rpp = $params->limit;
            $this->page = $params->page;
            $this->search = $params->search;
        }
        return $this->find($params);
    }

    /**
     * Return a single record
     *
     * @since 1.x
     * @deprecated deprecated since version 2.0.0
     */
    public function getRecordById ($id) {
        pods_deprecated('Pods::getRecordById', '2.0.0', 'Pods::fetch_item');
        return $this->fetch_item($id);
    }

    /**
     * Fetch the total row count
     *
     * @deprecated deprecated since version 2.0.0
     */
    public function getTotalRows () {
        pods_deprecated('Pods::getTotalRows', '2.0.0', 'Pods::total_found');
        return $this->total_found();
    }

    /**
     * (Re)set the MySQL result pointer
     *
     * @deprecated deprecated since version 2.0.0
     */
    public function resetPointer ($row_number = 0) {
        pods_deprecated('Pods::resetPointer', '2.0.0', 'Pods::reset');
        return $this->reset($row_number);
    }

    /**
     * Display the pagination controls
     *
     * @deprecated deprecated since 2.0.0
     */
    public function getPagination ($label = 'Go to page:') {
        pods_deprecated('Pods::getPagination', '2.0.0', 'Pods::pagination');
        echo $this->pagination(array('label' => $label));
    }

    /**
     * Display the list filters
     *
     * @deprecated deprecated since 2.0.0
     */
    public function getFilters ($filters = null, $label = 'Filter', $action = '') {
        pods_deprecated('Pods::getFilters', '2.0.0', 'Pods::filters');
        $params = array('filters' => $filters,
                            'label' => $label,
                            'action' => $action,
                            'show_textbox' => true);
        if(is_array($filters))
            $params = array_merge($params, $filters);
        echo $this->filters($params);
    }

    /**
     * Run a helper within a Pod Page or WP Template
     *
     * @param string $helper The helper name
     * @return mixed Anything returned by the helper
     * @since 1.2.0
     * @deprecated deprecated since version 2.0.0
     */
    public function pod_helper ($helper_name, $value = null, $name = null) {
        pods_deprecated('Pods::pod_helper', '2.0.0', 'Pods::helper');
        return $this->helper(array('helper' => $helper_name, 'value' => $value, 'name' => $name));
    }

    /**
     * Display the page template
     *
     * @deprecated deprecated since version 2.0.0
     */
    public function showTemplate ($template_name, $code = null) {
        pods_deprecated('Pods::showTemplate', '2.0.0', 'Pods::template');
        return $this->template($template_name, $code);
    }

    private function do_hook () {
        $args = func_get_args();
        if (empty($args))
            return false;
        $name = array_shift($args);
        return pods_do_hook("pods", $name, $args, $this);
    }
}