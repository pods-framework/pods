<?php
class PodsData
{
    // base
    private $prefix = 'pods_';
    private $field_types = array();
    public $display_errors = true;

    // pods
    public $pod = null;
    public $pod_data = null;
    public $id = 0;

    // data
    public $row_number = -1;
    public $data;
    public $row;
    public $insert_id;
    public $total;

    /**
     * Data Abstraction Class for Pods
     *
     * @param string $pod Pod name
     * @param integer $id Pod Item ID
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct ($pod = null, $id = 0) {
        if (0 < strlen($pod)) {
            $pod_id = pods_absint($pod);
            $where = $this->prepare('name = %s', $pod);
            if (0 < $pod_id)
                $where = $this->prepare('id = %d', $pod_id);
            $pod_data = $this->select(array('table' => '@wp_pods',
                                            'where' => $where,
                                            'limit' => 1));
            if (is_array($pod_data) && !empty($pod_data)) {
                $this->pod_data = $pod_data[0];
                $this->pod_data->options = @json_decode($this->pod_data->options, true);
                if (empty($this->pod_data->options))
                    $this->pod_data->options = array();
                $field_data = $this->select(array('table' => '@wp_pods_fields',
                                                  'where' => "pod_id = {$this->data->id}",
                                                  'limit' => 1));
                $fields = array();
                if (is_array($field_data) && !empty($field_data)) {
                    foreach ($field_data as $field) {
                        $fields[$field->name] = $field;
                        $fields[$field->name]->options = @json_decode($fields[$field->name]->options, true);
                        if (empty($fields[$field->name]->options))
                            $fields[$field->name]->options = array();
                    }
                }
                $this->pod_data->fields = $fields;
                $this->pod = $this->pod_data->name;
                if ('pod' == $this->pod_data->type)
                    $this->pod_data->table = 'tbl_' . $this->pod;
                $id = pods_absint($id);
                if (0 < $id)
                    $this->id = $id;
            }
        }
    }

    public function insert ($table, $data, $format = null) {
        global $wpdb;
        if (strlen($table) < 1 || empty($data) || !is_array($data))
            return false;
        if (empty($format)) {
            $format = array();
            foreach ($data as $field) {
                if (isset($this->field_types[$field]))
                    $format[] = $this->field_types[$field];
                elseif (isset($wpdb->field_types[$field]))
                    $format[] = $wpdb->field_types[$field];
                else
                    break;
            }
        }
        list($table, $data, $format) = $this->do_hook('insert', array($table, $data, $format));
        return $wpdb->insert($table, $data, $format);
    }

    public function update ($table, $data, $where, $format = null, $where_format = null) {
        global $wpdb;
        if (strlen($table) < 1 || empty($data) || !is_array($data))
            return false;
        if (empty($format)) {
            $format = array();
            foreach ($data as $field) {
                if (isset($this->field_types[$field]))
                    $form = $this->field_types[$field];
                elseif (isset($wpdb->field_types[$field]))
                    $form = $wpdb->field_types[$field];
                else
                    $form = '%s';
                $format[] = $form;
            }
        }
        if (empty($where_format)) {
            $where_format = array();
            foreach ((array) array_keys($where) as $field) {
                if (isset($this->field_types[$field]))
                    $form = $this->field_types[$field];
                elseif (isset($wpdb->field_types[$field]))
                    $form = $wpdb->field_types[$field];
                else
                    $form = '%s';
                $where_format[] = $form;
            }
        }
        list($table, $data, $where, $format, $where_format) = $this->do_hook('update', array($table, $data, $where, $format, $where_format));
        return $wpdb->update($table, $data, $where, $format, $where_format);
    }

    public function delete ($table, $where, $where_format = null) {
        global $wpdb;
        if (strlen($table) < 1 || empty($where) || !is_array($where))
            return false;
        $wheres = array();
        $where_formats = $where_format = (array) $where_format;
        foreach ((array) array_keys($where) as $field) {
            if (!empty($where_format))
                $form = ($form = array_shift($where_formats)) ? $form : $where_format[0];
            elseif (isset($this->field_types[$field]))
                $form = $this->field_types[$field];
            elseif (isset($wpdb->field_types[$field]))
                $form = $wpdb->field_types[$field];
            else
                $form = '%s';
            $wheres[] = "`{$field}` = {$form}";
        }
        $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $wheres);
        list($sql, $where) = $this->do_hook('insert', array($sql, array_values($where)), $table, $where, $where_format, $wheres);
        return $this->query($this->prepare($sql, $where));
    }

    public function select ($params) {
        /*
         * sql params:
         *
         * select
         * table
         * join
         * where
         * groupby
         * having
         * orderby
         * limit
         *
         * build params:
         *
         * page
         * search
         * filters
         * sort
         *
         * return:
         *
         * array of objects
         */
        $defaults = array('select' => '*',
                          'table' => null,
                          'join' => null,
                          'where' => null,
                          'groupby' => null,
                          'having' => null,
                          'orderby' => null,
                          'limit' => -1,
                          'identifier' => 'id',
                          'index' => 'name',
                          'page' => 1,
                          'search' => null,
                          'filters' => null,
                          'sort' => null,
                          'fields' => null,
                          'sql' => null);
        $params = (object) array_merge($defaults, (array) $params);
        $params->page = pods_absint($params->page);
        if (0 == $params->page)
            $params->page = 1;
        $params->limit = pods_absint($params->limit);
        if (0 == $params->limit)
            $params->limit = -1;
        if (empty($params->fields) || !is_array($params->fields) && isset($this->pod_data->fields) && !empty($this->pod_data->fields))
            $params->fields = $this->pod_data->fields;

        $where = (array) $params->where;
        if (empty($where))
            $where = array();
        $having = (array) $params->having;
        if (empty($having))
            $having = array();
        if (false !== $this->search_query && 0 < strlen($this->search_query)) {
            foreach ($params->fields as $key => $field) {
                $attributes = $field;
                if (!is_array($attributes))
                    $attributes = array();
                if (false === $attributes['search'])
                    continue;
                if (in_array($attributes['type'], array('date', 'time', 'datetime')))
                    continue;
                if (is_array($field))
                    $field = $key;
                if (!isset($this->filters[$field]))
                    continue;
                $fieldfield = '`' . $field . '`';
                if (isset($selects[$field]))
                    $fieldfield = '`' . $selects[$field] . '`';
                if (false !== $attributes['real_name'])
                    $fieldfield = $attributes['real_name'];
                if (false !== $attributes['group_related'])
                    $having[] = "$fieldfield LIKE '%" . $this->sanitize($this->search_query) . "%'";
                else
                    $where[] = "$fieldfield LIKE '%" . $this->sanitize($this->search_query) . "%'";
            }
            if (!empty($where)) {
                $where = array('(' . implode(' OR ', $where) . ')');
            }
            if (!empty($having)) {
                $having = array('(' . implode(' OR ', $having) . ')');
            }
        }
        foreach ($this->filters as $filter) {
            if (!isset($this->search_fields[$filter]))
                continue;
            $filterfield = '`' . $filter . '`';
            if (isset($selects[$filter]))
                $filterfield = '`' . $selects[$filter] . '`';
            if (false !== $this->search_fields[$filter]['real_name'])
                $filterfield = $this->search_fields[$filter]['real_name'];
            if (in_array($this->search_fields[$filter]['type'], array('date', 'datetime'))) {
                $start = date('Y-m-d') . ('datetime' == $this->search_fields[$filter]['type']) ? ' 00:00:00' : '';
                $end = date('Y-m-d') . ('datetime' == $this->search_fields[$filter]['type']) ? ' 23:59:59' : '';
                if (strlen($this->get_var('filter_' . $filter . '_start', false)) < 1 && strlen($this->get_var('filter_' . $filter . '_end', false)) < 1)
                    continue;
                if (0 < strlen($this->get_var('filter_' . $filter . '_start', false)))
                    $start = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_start', false))) . ('datetime' == $this->search_fields[$filter]['type']) ? ' 00:00:00' : '';
                if (0 < strlen($this->get_var('filter_' . $filter . '_end', false)))
                    $end = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_end', false))) . ('datetime' == $this->search_fields[$filter]['type']) ? ' 23:59:59' : '';
                if (false !== $this->search_fields[$filter]['date_ongoing']) {
                    $date_ongoing = $this->search_fields[$filter]['date_ongoing'];
                    if (isset($selects[$date_ongoing]))
                        $date_ongoing = $selects[$date_ongoing];
                    if (false !== $this->search_fields[$filter]['group_related'])
                        $having[] = "(($filterfield <= '$start' OR ($filterfield >= '$start' AND $filterfield <= '$end')) AND ($date_ongoing >= '$start' OR ($date_ongoing >= '$start' AND $date_ongoing <= '$end')))";
                    else
                        $where[] = "(($filterfield <= '$start' OR ($filterfield >= '$start' AND $filterfield <= '$end')) AND ($date_ongoing >= '$start' OR ($date_ongoing >= '$start' AND $date_ongoing <= '$end')))";
                }
                else {
                    if (false !== $this->search_fields[$filter]['group_related'])
                        $having[] = "($filterfield BETWEEN '$start' AND '$end')";
                    else
                        $where[] = "($filterfield BETWEEN '$start' AND '$end')";
                }
            }
            elseif (0 < strlen($this->get_var('filter_' . $filter, false))) {
                if (false !== $this->search_fields[$filter]['group_related'])
                    $having[] = "$filterfield LIKE '%" . PodsUI::sanitize($this->get_var('filter_' . $filter, false)) . "%'";
                else
                    $where[] = "$filterfield LIKE '%" . PodsUI::sanitize($this->get_var('filter_' . $filter, false)) . "%'";
            }
        }
        if (null !== $params->sql)
            $params->sql = $this->build($params);
        else {

        }
    }

    public function build ($params) {
        $defaults = array('select' => '*',
                          'join' => null,
                          'where' => null,
                          'groupby' => null,
                          'having' => null,
                          'orderby' => null,
                          'limit' => -1,
                          'page' => 1,
                          'sql' => null);
        $params = (object) array_merge($defaults, (array) $params);
        $params->page = pods_absint($params->page);
        if (0 == $params->page)
            $params->page = 1;
        $params->limit = pods_absint($params->limit);
        if (0 == $params->limit)
            $params->limit = -1;
        if (empty($params->sql))
            return false;

        // build SQL based off of sql query
        global $wpdb;
        if (false === $this->sql) {
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM $this->table";
            if (false !== $this->search && !empty($this->search_fields)) {
                $whered = false;
                if (false !== $this->search_query && 0 < strlen($this->search_query)) {
                    $sql .= " WHERE ";
                    $whered = true;
                    $where_sql = array();
                    foreach ($this->search_fields as $key => $field) {
                        if (false === $field['search'])
                            continue;
                        if (in_array($field['type'], array('date', 'time', 'datetime')))
                            continue;
                        if (is_array($field))
                            $field = $key;
                        $where_sql[] = "`$field` LIKE '%" . $this->sanitize($this->search_query) . "%'";
                    }
                    if (!empty($where_sql))
                        $sql .= '(' . implode(' OR ', $where_sql) . ')';
                }
                $where_sql = array();
                foreach ($this->filters as $filter) {
                    if (!isset($this->search_fields[$filter]))
                        continue;
                    if (in_array($this->search_fields[$filter]['type'], array('date', 'datetime'))) {
                        $start = date('Y-m-d') . ' 00:00:00';
                        $end = date('Y-m-d') . ' 23:59:59';
                        if (strlen($this->get_var('filter_' . $filter . '_start', false)) < 1 && strlen($this->get_var('filter_' . $filter . '_end', false)) < 1)
                            continue;
                        if (0 < strlen($this->get_var('filter_' . $filter . '_start', false)))
                            $start = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_start', false))) . ' 00:00:00';
                        if (0 < strlen($this->get_var('filter_' . $filter . '_end', false)))
                            $end = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_end', false))) . ' 23:59:59';
                        if (false !== $this->search_fields[$filter]['date_ongoing'])
                            $where_sql[] = "((`$filter` <= '$start' OR `$filter` <= '$end') AND (`" . $this->search_fields[$filter]['date_ongoing'] . "` >= '$end' OR `" . $this->search_fields[$filter]['date_ongoing'] . "` >= '$start'))";
                        else
                            $where_sql[] = "(`$filter` BETWEEN '$start' AND '$end')";
                    }
                    elseif (0 < strlen($this->get_var('filter_' . $filter, false)))
                        $where_sql[] = "`$filter` LIKE '%" . $this->sanitize($this->get_var('filter_' . $filter, false)) . "%'";
                }
                if (!empty($where_sql)) {
                    if (false === $whered)
                        $sql .= " WHERE ";
                    else
                        $sql .= " AND ";
                    $sql .= implode(' AND ', $where_sql);
                }
            }
            $sql .= ' ORDER BY ';
            if (false !== $this->sort && (false === $this->reorder || 'reorder' != $this->action))
                $sql .= $this->sort . ' ' . $this->sort_dir;
            elseif (false !== $this->reorder && 'reorder' == $this->action)
                $sql .= $this->reorder_sort . ' ' . $this->reorder_sort_dir;
            else
                $sql .= $this->identifier;
            if (false !== $this->pagination) {
                $start = ($this->page - 1) * $this->limit;
                $end = ($this->page - 1) * $this->limit + $this->limit;
                $sql .= " LIMIT $start, $end";
            }
        }
        else {
            $sql = str_replace(array("\n", "\r"), ' ', ' ' . $this->sql);
            $sql = str_ireplace(' SELECT ', ' SELECT SQL_CALC_FOUND_ROWS ', str_ireplace(' SELECT SQL_CALC_FOUND_ROWS ', ' SELECT ', $sql));
            $wheresql = $havingsql = $ordersql = $limitsql = '';
            $where_sql = $having_sql = array();
            preg_match('/SELECT SQL_CALC_FOUND_ROWS (.*) FROM/i', $sql, $selectmatches);
            $selects = array();
            if (isset($selectmatches[1]) && !empty($selectmatches[1]) && false !== stripos($selectmatches[1], ' AS ')) {
                $theselects = explode(', ', $selectmatches[1]);
                if (empty($theselects))
                    $theselects = explode(',', $selectmatches[1]);
                foreach ($theselects as $selected) {
                    $selectfield = explode(' AS ', $selected);
                    if (2 == count($selectfield)) {
                        $field = trim(trim($selectfield[1]), '`');
                        $real_field = trim(trim($selectfield[0]), '`');
                        $selects[$field] = $real_field;
                    }
                }
            }
            if (false !== $this->search && !empty($this->search_fields)) {
                if (false !== $this->search_query && 0 < strlen($this->search_query)) {
                    foreach ($this->search_fields as $key => $field) {
                        $attributes = $field;
                        if (!is_array($attributes))
                            $attributes = array();
                        if (false === $attributes['search'])
                            continue;
                        if (in_array($attributes['type'], array('date', 'time', 'datetime')))
                            continue;
                        if (is_array($field))
                            $field = $key;
                        if (!isset($this->filters[$field]))
                            continue;
                        $fieldfield = '`' . $field . '`';
                        if (isset($selects[$field]))
                            $fieldfield = '`' . $selects[$field] . '`';
                        if (false !== $attributes['real_name'])
                            $fieldfield = $attributes['real_name'];
                        if (false !== $attributes['group_related'])
                            $having_sql[] = "$fieldfield LIKE '%" . $this->sanitize($this->search_query) . "%'";
                        else
                            $where_sql[] = "$fieldfield LIKE '%" . $this->sanitize($this->search_query) . "%'";
                    }
                    if (!empty($where_sql)) {
                        $where_sql = array('(' . implode(' OR ', $where_sql) . ')');
                    }
                    if (!empty($having_sql)) {
                        $having_sql = array('(' . implode(' OR ', $having_sql) . ')');
                    }
                }
                foreach ($this->filters as $filter) {
                    if (!isset($this->search_fields[$filter]))
                        continue;
                    $filterfield = '`' . $filter . '`';
                    if (isset($selects[$filter]))
                        $filterfield = '`' . $selects[$filter] . '`';
                    if (false !== $this->search_fields[$filter]['real_name'])
                        $filterfield = $this->search_fields[$filter]['real_name'];
                    if (in_array($this->search_fields[$filter]['type'], array('date', 'datetime'))) {
                        $start = date('Y-m-d') . ('datetime' == $this->search_fields[$filter]['type']) ? ' 00:00:00' : '';
                        $end = date('Y-m-d') . ('datetime' == $this->search_fields[$filter]['type']) ? ' 23:59:59' : '';
                        if (strlen($this->get_var('filter_' . $filter . '_start', false)) < 1 && strlen($this->get_var('filter_' . $filter . '_end', false)) < 1)
                            continue;
                        if (0 < strlen($this->get_var('filter_' . $filter . '_start', false)))
                            $start = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_start', false))) . ('datetime' == $this->search_fields[$filter]['type']) ? ' 00:00:00' : '';
                        if (0 < strlen($this->get_var('filter_' . $filter . '_end', false)))
                            $end = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_end', false))) . ('datetime' == $this->search_fields[$filter]['type']) ? ' 23:59:59' : '';
                        if (false !== $this->search_fields[$filter]['date_ongoing']) {
                            $date_ongoing = $this->search_fields[$filter]['date_ongoing'];
                            if (isset($selects[$date_ongoing]))
                                $date_ongoing = $selects[$date_ongoing];
                            if (false !== $this->search_fields[$filter]['group_related'])
                                $having_sql[] = "(($filterfield <= '$start' OR ($filterfield >= '$start' AND $filterfield <= '$end')) AND ($date_ongoing >= '$start' OR ($date_ongoing >= '$start' AND $date_ongoing <= '$end')))";
                            else
                                $where_sql[] = "(($filterfield <= '$start' OR ($filterfield >= '$start' AND $filterfield <= '$end')) AND ($date_ongoing >= '$start' OR ($date_ongoing >= '$start' AND $date_ongoing <= '$end')))";
                        }
                        else {
                            if (false !== $this->search_fields[$filter]['group_related'])
                                $having_sql[] = "($filterfield BETWEEN '$start' AND '$end')";
                            else
                                $where_sql[] = "($filterfield BETWEEN '$start' AND '$end')";
                        }
                    }
                    elseif (0 < strlen($this->get_var('filter_' . $filter, false))) {
                        if (false !== $this->search_fields[$filter]['group_related'])
                            $having_sql[] = "$filterfield LIKE '%" . PodsUI::sanitize($this->get_var('filter_' . $filter, false)) . "%'";
                        else
                            $where_sql[] = "$filterfield LIKE '%" . PodsUI::sanitize($this->get_var('filter_' . $filter, false)) . "%'";
                    }
                }
                if (!empty($where_sql)) {
                    if (false === stripos($sql, ' WHERE '))
                        $wheresql .= ' WHERE (' . implode(' AND ', $where_sql) . ')';
                    elseif (empty($wheresql))
                        $wheresql .= ' AND (' . implode(' AND ', $where_sql) . ')';
                    else
                        $wheresql .= '(' . implode(' AND ', $where_sql) . ') AND ';
                }
                if (!empty($having_sql)) {
                    if (false === stripos($sql, ' HAVING '))
                        $havingsql .= ' HAVING (' . implode(' AND ', $having_sql) . ')';
                    elseif (empty($havingsql))
                        $havingsql .= ' AND (' . implode(' AND ', $having_sql) . ')';
                    else
                        $havingsql .= '(' . implode(' AND ', $having_sql) . ') AND ';
                }
            }
            if (false !== $this->sort && (false === $this->reorder || 'reorder' != $this->action))
                $ordersql = trim($this->sort . ' ' . $this->sort_dir);
            elseif (false !== $this->reorder && 'reorder' == $this->action)
                $ordersql = trim($this->reorder_sort . ' ' . $this->reorder_sort_dir);
            elseif (false === stripos($sql, ' ORDER BY '))
                $ordersql = trim($this->identifier);
            if (!empty($ordersql)) {
                if (false === stripos($sql, ' ORDER BY '))
                    $ordersql = ' ORDER BY ' . $ordersql;
                else
                    $ordersql = $ordersql . ', ';
            }
            if (false !== $this->pagination && false === stripos($sql, ' LIMIT ')) {
                $start = ($this->page - 1) * $this->limit;
                $end = ($this->page - 1) * $this->limit + $this->limit;
                $limitsql .= " LIMIT $start, $end";
            }
            $sql = str_replace(' WHERE ', ' WHERE %%WHERE%% ', $sql);
            $sql = str_replace(' HAVING ', ' HAVING %%HAVING%% ', $sql);
            $sql = str_replace(' ORDER BY ', ' ORDER BY %%ORDERBY%% ', $sql);
            if (false === stripos($sql, '%%WHERE%%') && false === stripos($sql, ' WHERE ')) {
                if (false !== stripos($sql, ' GROUP BY '))
                    $sql = str_replace(' GROUP BY ', ' %%WHERE%% GROUP BY ', $sql);
                elseif (false !== stripos($sql, ' ORDER BY '))
                    $sql = str_replace(' ORDER BY ', ' %%WHERE%% ORDER BY ', $sql);
                elseif (false !== stripos($sql, ' LIMIT '))
                    $sql = str_replace(' LIMIT ', ' %%WHERE%% LIMIT ', $sql);
                else
                    $sql .= ' %%WHERE%% ';
            }
            if (false === stripos($sql, '%%HAVING%%') && false === stripos($sql, ' HAVING ')) {
                if (false !== stripos($sql, ' ORDER BY '))
                    $sql = str_replace(' ORDER BY ', ' %%HAVING%% ORDER BY ', $sql);
                elseif (false !== stripos($sql, ' LIMIT '))
                    $sql = str_replace(' LIMIT ', ' %%HAVING%% LIMIT ', $sql);
                else
                    $sql .= ' %%HAVING%% ';
            }
            if (false === stripos($sql, '%%ORDERBY%%') && false === stripos($sql, ' ORDER BY ')) {
                if (false !== stripos($sql, ' LIMIT '))
                    $sql = str_replace(' LIMIT ', ' %%ORDERBY%% LIMIT ', $sql);
                else
                    $sql .= ' %%ORDERBY%% ';
            }
            if (false === stripos($sql, '%%LIMIT%%') && false === stripos($sql, ' LIMIT '))
                $sql .= ' %%LIMIT%%';
            $sql = str_replace('%%WHERE%%', $wheresql, $sql);
            $sql = str_replace('%%HAVING%%', $havingsql, $sql);
            $sql = str_replace('%%ORDERBY%%', $ordersql, $sql);
            $sql = str_replace('%%LIMIT%%', $limitsql, $sql);
            $sql = str_replace('``', '`', $sql);
            $sql = str_replace('  ', ' ', $sql);
            //echo "<textarea cols='130' rows='30'>$sql</textarea>";
        }
        $results = $wpdb->get_results($sql, array_A);
        $results = $this->do_hook('get_data', $results);
        $this->data = $results;
        if (empty($this->fields) && !empty($this->data)) {
            $data = current($this->data);
            foreach ($data as $data_key => $data_value) {
                $this->fields[$data_key] = array('label' => ucwords(str_replace('-', ' ', str_replace('_', ' ', $data_key))));
            }
            $this->export_fields = $this->fields;
        }
        $total = @current($wpdb->get_col("SELECT FOUND_ROWS()"));
        $total = $this->do_hook('get_data_total', $total);
        if (is_numeric($total))
            $this->total = $total;
        return $results;
    }

    public static function table_create ($table, $fields, $if_not_exists = false) {
        global $wpdb;
        $sql = "CREATE TABLE";
        if (true === $if_not_exists)
            $sql .= " IF NOT EXISTS";
        $sql .= " `{$wpdb->prefix}" . self::prefix . "{$table}` ({$fields})";
        if (!empty($wpdb->charset))
            $sql .= " DEFAULT CHARACTER SET {$wpdb->charset}";
        if (!empty($wpdb->collate))
            $sql .= " COLLATE {$wpdb->collate}";
        return self::query($sql);
    }

    public static function table_alter ($table, $changes) {
        global $wpdb;
        $sql = "ALTER TABLE `{$wpdb->prefix}" . self::prefix . "{$table}` {$changes}";
        return self::query($sql);
    }

    public static function table_truncate ($table) {
        global $wpdb;
        $sql = "TRUNCATE TABLE `{$wpdb->prefix}" . self::prefix . "{$table}`";
        return self::query($sql);
    }

    public static function table_drop ($table) {
        global $wpdb;
        $sql = "DROP TABLE `{$wpdb->prefix}" . self::prefix . "{$table}`";
        return self::query($sql);
    }

    public function reorder ($table, $weight_field, $id_field, $ids) {
        $success = false;
        $ids = (array) $ids;
        list($table, $weight_field, $id_field, $ids) = $this->do_hook('reorder', array($table, $weight_field, $id_field, $ids));
        if (!empty($ids)) {
            $success = true;
            foreach ($ids as $weight => $id) {
                $updated = $this->update($table, array($weight_field => $weight), array($id_field => $id), array('%d'), array('%d'));
                if (false === $updated)
                    $success = false;
            }
        }
        return $success;
    }

    function get_row ($id = false) {
        if (isset($this->actions_custom['row']) && function_exists("{$this->actions_custom['row']}"))
            return $this->actions_custom['row']($id, $this);
        if (false !== $this->ui['pod'] && is_object($this->ui['pod'])) {
            if (false === $id) {
                $this->ui['pod']->fetch();
                $row = $this->ui['pod']->data;
                $row = $this->do_hook('get_row', $row, $id);
                $this->row = $row;
                return $row;
            }
            else {
                foreach ($this->ui['pod']->result as $row) {
                    if (!empty($row) && $id === $row[$this->identified]) {
                        $row = $this->do_hook('get_row', $row, $id);
                        $this->row = $row;
                        return $row;
                    }
                }
            }
        }
        if (false === $this->table && false === $this->sql)
            return $this->error(__('<strong>Error:</strong> Invalid Configuration - Missing "table" definition.', 'pods'));
        if (false === $this->id && false === $id)
            return $this->error(__('<strong>Error:</strong> Invalid Configuration - Missing "id" definition.', 'pods'));
        if (false === $id)
            $id = $this->id;
        global $wpdb;
        $sql = "SELECT * FROM `{$this->table}` WHERE `id` = " . $this->sanitize($id);
        $row = @current($wpdb->get_results($sql, array_A));
        $row = $this->do_hook('get_row', $row, $id);
        if (!empty($row))
            $this->row = $row;
        return $row;
    }

    function get_data ($full = false) {
        if (isset($this->actions_custom['data']) && function_exists("{$this->actions_custom['data']}"))
            return $this->actions_custom['data']($full, $this);
        if (false !== $this->ui['pod'] && is_object($this->ui['pod'])) {
            if (false !== $this->sort && (false === $this->reorder || 'reorder' != $this->action))
                $sort = $this->sort . ' ' . $this->sort_dir;
            elseif (false !== $this->reorder && 'reorder' == $this->action)
                $sort = $this->reorder_sort . ' ' . $this->reorder_sort_dir;
            $params = array('limit' => $this->limit, 'orderby' => $sort, 'search_var' => 'search_query' . $this->num, 'page_var' => 'pg' . $this->num);
            if (false !== $full)
                $params['limit'] = -1;
            $this->ui['pod']->find($params);
            $results = $this->ui['pod']->results;
            $results = $this->do_hook('get_data', $results, $full);
            return $results;
        }
        if (false === $this->table && false === $this->sql)
            return $this->error(__('<strong>Error:</strong> Invalid Configuration - Missing "table" definition.', 'pods'));
        global $wpdb;
        if (false === $this->sql) {
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM $this->table";
            if (false !== $this->search && !empty($this->search_fields)) {
                $whered = false;
                if (false !== $this->search_query && 0 < strlen($this->search_query)) {
                    $sql .= " WHERE ";
                    $whered = true;
                    $where_sql = array();
                    foreach ($this->search_fields as $key => $field) {
                        if (false === $field['search'])
                            continue;
                        if (in_array($field['type'], array('date', 'time', 'datetime')))
                            continue;
                        if (is_array($field))
                            $field = $key;
                        $where_sql[] = "`$field` LIKE '%" . $this->sanitize($this->search_query) . "%'";
                    }
                    if (!empty($where_sql))
                        $sql .= '(' . implode(' OR ', $where_sql) . ')';
                }
                $where_sql = array();
                foreach ($this->filters as $filter) {
                    if (!isset($this->search_fields[$filter]))
                        continue;
                    if (in_array($this->search_fields[$filter]['type'], array('date', 'datetime'))) {
                        $start = date('Y-m-d') . ' 00:00:00';
                        $end = date('Y-m-d') . ' 23:59:59';
                        if (strlen($this->get_var('filter_' . $filter . '_start', false)) < 1 && strlen($this->get_var('filter_' . $filter . '_end', false)) < 1)
                            continue;
                        if (0 < strlen($this->get_var('filter_' . $filter . '_start', false)))
                            $start = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_start', false))) . ' 00:00:00';
                        if (0 < strlen($this->get_var('filter_' . $filter . '_end', false)))
                            $end = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_end', false))) . ' 23:59:59';
                        if (false !== $this->search_fields[$filter]['date_ongoing'])
                            $where_sql[] = "((`$filter` <= '$start' OR `$filter` <= '$end') AND (`" . $this->search_fields[$filter]['date_ongoing'] . "` >= '$end' OR `" . $this->search_fields[$filter]['date_ongoing'] . "` >= '$start'))";
                        else
                            $where_sql[] = "(`$filter` BETWEEN '$start' AND '$end')";
                    }
                    elseif (0 < strlen($this->get_var('filter_' . $filter, false)))
                        $where_sql[] = "`$filter` LIKE '%" . $this->sanitize($this->get_var('filter_' . $filter, false)) . "%'";
                }
                if (!empty($where_sql)) {
                    if (false === $whered)
                        $sql .= " WHERE ";
                    else
                        $sql .= " AND ";
                    $sql .= implode(' AND ', $where_sql);
                }
            }
            $sql .= ' ORDER BY ';
            if (false !== $this->sort && (false === $this->reorder || 'reorder' != $this->action))
                $sql .= $this->sort . ' ' . $this->sort_dir;
            elseif (false !== $this->reorder && 'reorder' == $this->action)
                $sql .= $this->reorder_sort . ' ' . $this->reorder_sort_dir;
            else
                $sql .= $this->identifier;
            if (false !== $this->pagination && !$full) {
                $start = ($this->page - 1) * $this->limit;
                $end = ($this->page - 1) * $this->limit + $this->limit;
                $sql .= " LIMIT $start, $end";
            }
        }
        else {
            $sql = str_replace(array("\n", "\r"), ' ', ' ' . $this->sql);
            $sql = str_ireplace(' SELECT ', ' SELECT SQL_CALC_FOUND_ROWS ', str_ireplace(' SELECT SQL_CALC_FOUND_ROWS ', ' SELECT ', $sql));
            $wheresql = $havingsql = $ordersql = $limitsql = '';
            $where_sql = $having_sql = array();
            preg_match('/SELECT SQL_CALC_FOUND_ROWS (.*) FROM/i', $sql, $selectmatches);
            $selects = array();
            if (isset($selectmatches[1]) && !empty($selectmatches[1]) && false !== stripos($selectmatches[1], ' AS ')) {
                $theselects = explode(', ', $selectmatches[1]);
                if (empty($theselects))
                    $theselects = explode(',', $selectmatches[1]);
                foreach ($theselects as $selected) {
                    $selectfield = explode(' AS ', $selected);
                    if (2 == count($selectfield)) {
                        $field = trim(trim($selectfield[1]), '`');
                        $real_field = trim(trim($selectfield[0]), '`');
                        $selects[$field] = $real_field;
                    }
                }
            }
            if (false !== $this->search && !empty($this->search_fields)) {
                if (false !== $this->search_query && 0 < strlen($this->search_query)) {
                    foreach ($this->search_fields as $key => $field) {
                        $attributes = $field;
                        if (!is_array($attributes))
                            $attributes = array();
                        if (false === $attributes['search'])
                            continue;
                        if (in_array($attributes['type'], array('date', 'time', 'datetime')))
                            continue;
                        if (is_array($field))
                            $field = $key;
                        if (!isset($this->filters[$field]))
                            continue;
                        $fieldfield = '`' . $field . '`';
                        if (isset($selects[$field]))
                            $fieldfield = '`' . $selects[$field] . '`';
                        if (false !== $attributes['real_name'])
                            $fieldfield = $attributes['real_name'];
                        if (false !== $attributes['group_related'])
                            $having_sql[] = "$fieldfield LIKE '%" . $this->sanitize($this->search_query) . "%'";
                        else
                            $where_sql[] = "$fieldfield LIKE '%" . $this->sanitize($this->search_query) . "%'";
                    }
                    if (!empty($where_sql)) {
                        $where_sql = array('(' . implode(' OR ', $where_sql) . ')');
                    }
                    if (!empty($having_sql)) {
                        $having_sql = array('(' . implode(' OR ', $having_sql) . ')');
                    }
                }
                foreach ($this->filters as $filter) {
                    if (!isset($this->search_fields[$filter]))
                        continue;
                    $filterfield = '`' . $filter . '`';
                    if (isset($selects[$filter]))
                        $filterfield = '`' . $selects[$filter] . '`';
                    if (false !== $this->search_fields[$filter]['real_name'])
                        $filterfield = $this->search_fields[$filter]['real_name'];
                    if (in_array($this->search_fields[$filter]['type'], array('date', 'datetime'))) {
                        $start = date('Y-m-d') . ('datetime' == $this->search_fields[$filter]['type']) ? ' 00:00:00' : '';
                        $end = date('Y-m-d') . ('datetime' == $this->search_fields[$filter]['type']) ? ' 23:59:59' : '';
                        if (strlen($this->get_var('filter_' . $filter . '_start', false)) < 1 && strlen($this->get_var('filter_' . $filter . '_end', false)) < 1)
                            continue;
                        if (0 < strlen($this->get_var('filter_' . $filter . '_start', false)))
                            $start = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_start', false))) . ('datetime' == $this->search_fields[$filter]['type']) ? ' 00:00:00' : '';
                        if (0 < strlen($this->get_var('filter_' . $filter . '_end', false)))
                            $end = date('Y-m-d', strtotime($this->get_var('filter_' . $filter . '_end', false))) . ('datetime' == $this->search_fields[$filter]['type']) ? ' 23:59:59' : '';
                        if (false !== $this->search_fields[$filter]['date_ongoing']) {
                            $date_ongoing = $this->search_fields[$filter]['date_ongoing'];
                            if (isset($selects[$date_ongoing]))
                                $date_ongoing = $selects[$date_ongoing];
                            if (false !== $this->search_fields[$filter]['group_related'])
                                $having_sql[] = "(($filterfield <= '$start' OR ($filterfield >= '$start' AND $filterfield <= '$end')) AND ($date_ongoing >= '$start' OR ($date_ongoing >= '$start' AND $date_ongoing <= '$end')))";
                            else
                                $where_sql[] = "(($filterfield <= '$start' OR ($filterfield >= '$start' AND $filterfield <= '$end')) AND ($date_ongoing >= '$start' OR ($date_ongoing >= '$start' AND $date_ongoing <= '$end')))";
                        }
                        else {
                            if (false !== $this->search_fields[$filter]['group_related'])
                                $having_sql[] = "($filterfield BETWEEN '$start' AND '$end')";
                            else
                                $where_sql[] = "($filterfield BETWEEN '$start' AND '$end')";
                        }
                    }
                    elseif (0 < strlen($this->get_var('filter_' . $filter, false))) {
                        if (false !== $this->search_fields[$filter]['group_related'])
                            $having_sql[] = "$filterfield LIKE '%" . PodsUI::sanitize($this->get_var('filter_' . $filter, false)) . "%'";
                        else
                            $where_sql[] = "$filterfield LIKE '%" . PodsUI::sanitize($this->get_var('filter_' . $filter, false)) . "%'";
                    }
                }
                if (!empty($where_sql)) {
                    if (false === stripos($sql, ' WHERE '))
                        $wheresql .= ' WHERE (' . implode(' AND ', $where_sql) . ')';
                    elseif (empty($wheresql))
                        $wheresql .= ' AND (' . implode(' AND ', $where_sql) . ')';
                    else
                        $wheresql .= '(' . implode(' AND ', $where_sql) . ') AND ';
                }
                if (!empty($having_sql)) {
                    if (false === stripos($sql, ' HAVING '))
                        $havingsql .= ' HAVING (' . implode(' AND ', $having_sql) . ')';
                    elseif (empty($havingsql))
                        $havingsql .= ' AND (' . implode(' AND ', $having_sql) . ')';
                    else
                        $havingsql .= '(' . implode(' AND ', $having_sql) . ') AND ';
                }
            }
            if (false !== $this->sort && (false === $this->reorder || 'reorder' != $this->action))
                $ordersql = trim($this->sort . ' ' . $this->sort_dir);
            elseif (false !== $this->reorder && 'reorder' == $this->action)
                $ordersql = trim($this->reorder_sort . ' ' . $this->reorder_sort_dir);
            elseif (false === stripos($sql, ' ORDER BY '))
                $ordersql = trim($this->identifier);
            if (!empty($ordersql)) {
                if (false === stripos($sql, ' ORDER BY '))
                    $ordersql = ' ORDER BY ' . $ordersql;
                else
                    $ordersql = $ordersql . ', ';
            }
            if (false !== $this->pagination && !$full && false === stripos($sql, ' LIMIT ')) {
                $start = ($this->page - 1) * $this->limit;
                $end = ($this->page - 1) * $this->limit + $this->limit;
                $limitsql .= " LIMIT $start, $end";
            }
            $sql = str_replace(' WHERE ', ' WHERE %%WHERE%% ', $sql);
            $sql = str_replace(' HAVING ', ' HAVING %%HAVING%% ', $sql);
            $sql = str_replace(' ORDER BY ', ' ORDER BY %%ORDERBY%% ', $sql);
            if (false === stripos($sql, '%%WHERE%%') && false === stripos($sql, ' WHERE ')) {
                if (false !== stripos($sql, ' GROUP BY '))
                    $sql = str_replace(' GROUP BY ', ' %%WHERE%% GROUP BY ', $sql);
                elseif (false !== stripos($sql, ' ORDER BY '))
                    $sql = str_replace(' ORDER BY ', ' %%WHERE%% ORDER BY ', $sql);
                elseif (false !== stripos($sql, ' LIMIT '))
                    $sql = str_replace(' LIMIT ', ' %%WHERE%% LIMIT ', $sql);
                else
                    $sql .= ' %%WHERE%% ';
            }
            if (false === stripos($sql, '%%HAVING%%') && false === stripos($sql, ' HAVING ')) {
                if (false !== stripos($sql, ' ORDER BY '))
                    $sql = str_replace(' ORDER BY ', ' %%HAVING%% ORDER BY ', $sql);
                elseif (false !== stripos($sql, ' LIMIT '))
                    $sql = str_replace(' LIMIT ', ' %%HAVING%% LIMIT ', $sql);
                else
                    $sql .= ' %%HAVING%% ';
            }
            if (false === stripos($sql, '%%ORDERBY%%') && false === stripos($sql, ' ORDER BY ')) {
                if (false !== stripos($sql, ' LIMIT '))
                    $sql = str_replace(' LIMIT ', ' %%ORDERBY%% LIMIT ', $sql);
                else
                    $sql .= ' %%ORDERBY%% ';
            }
            if (false === stripos($sql, '%%LIMIT%%') && false === stripos($sql, ' LIMIT '))
                $sql .= ' %%LIMIT%%';
            $sql = str_replace('%%WHERE%%', $wheresql, $sql);
            $sql = str_replace('%%HAVING%%', $havingsql, $sql);
            $sql = str_replace('%%ORDERBY%%', $ordersql, $sql);
            $sql = str_replace('%%LIMIT%%', $limitsql, $sql);
            $sql = str_replace('``', '`', $sql);
            $sql = str_replace('  ', ' ', $sql);
            //echo "<textarea cols='130' rows='30'>$sql</textarea>";
        }
        $results = $wpdb->get_results($sql, array_A);
        $results = $this->do_hook('get_data', $results, $full);
        if ($full)
            $this->full_data = $results;
        else
            $this->data = $results;
        if ($full) {
            if (empty($this->fields) && !empty($this->full_data)) {
                $data = current($this->full_data);
                foreach ($data as $data_key => $data_value) {
                    $this->fields[$data_key] = array('label' => ucwords(str_replace('-', ' ', str_replace('_', ' ', $data_key))));
                }
                $this->export_fields = $this->fields;
            }
            return;
        }
        else {
            if (empty($this->fields) && !empty($this->data)) {
                $data = current($this->data);
                foreach ($data as $data_key => $data_value) {
                    $this->fields[$data_key] = array('label' => ucwords(str_replace('-', ' ', str_replace('_', ' ', $data_key))));
                }
                $this->export_fields = $this->fields;
            }
        }
        $total = @current($wpdb->get_col("SELECT FOUND_ROWS()"));
        $total = $this->do_hook('get_data_total', $total, $full);
        if (is_numeric($total))
            $this->total = $total;
        return $results;
    }

    public static function query ($sql, $error = 'Database Error', $results_error = null, $no_results_error = null) {
        global $wpdb;

        if ($wpdb->show_errors)
            self::$display_errors = true;

        $display_errors = self::$display_errors;
        if (is_object($error)) {
            if (isset($error->display_errors) && false === $error->display_errors) {
                $display_errors = false;
            }
            $error = 'Database Error';
        }
        elseif (is_bool($error)) {
            $display_errors = $error;
            $error = 'Database Error';
        }

        $params = (object) array('sql' => $sql,
                                 'error' => $error,
                                 'results_error' => $results_error,
                                 'no_results_error' => $no_results_error,
                                 'display_errors' => $display_errors);

        if (is_array($sql)) {
            if (isset($sql[0]) && 1 < count($sql)) {
                if (2 == count($sql))
                    $params->sql = self::prepare($sql[0], $sql[1]);
                elseif (3 == count($sql))
                    $params->sql = self::prepare($sql[0], $sql[1], $sql[2]);
                else
                    $params->sql = self::prepare($sql[0], $sql[1], $sql[2], $sql[3]);
            }
            else
                $params = array_merge($params, $sql);
        }

        $params->sql = trim($params->sql);
        $params->sql = str_replace('@wp_users', $wpdb->users, $params->sql);
        $params->sql = str_replace('@wp_', $wpdb->prefix, $params->sql);
        $params->sql = str_replace('{prefix}', '@wp_', $params->sql);

        // Run Query
        $params->sql = self::do_hook('query', $params->sql, $params);
        $result = $wpdb->query($sql);
        $result = self::do_hook('query_result', $result, $params);

        if (false === $result && !empty($params->error) && !empty($wpdb->last_error))
            return pods_error("{$params->error}; SQL: {$params->sql}; Response: {$wpdb->last_error}", $params->display_errors);
        if ('INSERT' == substr($params->sql, 0, 6))
            $result = $wpdb->insert_id;
        elseif ('SELECT' == substr($params->sql, 0, 6)) {
            $result = (array) $wpdb->last_result;
            if (!empty($result) && !empty($params->results_error))
                return pods_error("{$params->results_error}", $params->display_errors);
            elseif (empty($result) && !empty($params->no_results_error))
                return pods_error("{$params->no_results_error}", $params->display_errors);
        }
        return $result;
    }

    public static function prepare ($sql, $data) {
        global $wpdb;
        list($sql, $data) = self::do_hook('prepare', array($sql, $data));
        return $wpdb->prepare($sql, $data);
    }

    public static function switch_site ($new_blog, $validate = true) {
        $new_blog = self::do_hook('switch_site', $new_blog, $validate);
        $new_blog = pods_absint($new_blog);
        if (0 < $new_blog)
            return switch_to_blog($new_blog, $validate);
        return false;
    }

    public static function restore_site () {
        self::do_hook('restore_site');
        return restore_current_blog();
    }

    private function do_hook () {
        $args = func_get_args();
        if (empty($args))
            return false;
        $name = array_shift($args);
        return pods_do_hook("data", $name, $args, $this);
    }
}