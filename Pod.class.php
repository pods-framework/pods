<?php
/*
==================================================
Pod.class.php

This class generates the display values.

USAGE:
$Record = new Pod('news');
$Record->findRecords('id DESC', 10);

echo $Record->getFilters();
echo $Record->getPagination();
echo $Record->showTemplate('list');
==================================================
*/
class Pod
{
    var $data;
    var $result;
    var $datatype;
    var $datatype_id;
    var $total_rows;
    var $rel_table;
    var $rpp = 15;
    var $prefix;
    var $page;

    function Pod($datatype = null, $id = null)
    {
        global $table_prefix;
        $this->prefix = $table_prefix;
        $this->page = empty($_GET['pg']) ? 1 : $_GET['pg'];

        if (null != $datatype)
        {
            $this->datatype = trim($datatype);
            $result = pod_query("SELECT id FROM {$this->prefix}pod_types WHERE name = '$datatype' LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $this->datatype_id = $row['id'];

            if (null != $id)
            {
                return $this->getRecordById($id);
            }
        }
    }

    /*
    ==================================================
    Output the SQL resultset
    ==================================================
    */
    function fetchRecord()
    {
        if ($this->data = mysql_fetch_assoc($this->result))
        {
            return $this->data;
        }
        return false;
    }

    /*
    ==================================================
    Return the value of a single field (return arrays)
    ==================================================
    */
    function get_field($name)
    {
        if (false === isset($this->data[$name]))
        {
            $datatype = $this->datatype;
            $datatype_id = $this->datatype_id;

            $result = pod_query("SELECT id, pickval FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$name' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $row = mysql_fetch_assoc($result);
                $this->rel_table = $row['pickval'];
                $this->data[$name] = $this->rel_lookup($row['id'], $this->rel_table);
                return $this->data[$name];
            }
            return false;
        }
        else
        {
            return $this->data[$name];
        }
    }

    /*
    ==================================================
    Return the value of a single field (implode arrays)
    ==================================================
    */
    function print_field($name)
    {
        $data = $this->get_field($name);
        if (is_array($data))
        {
            $first = 'first ';
            $datatype = $this->rel_table;
            foreach ($data as $key => $val)
            {
                $detail_url = get_bloginfo('url') . "/detail/?type=$datatype&id=$key";
                $val = is_numeric($datatype) ? $val['name'] : '<a href="' . $detail_url . '">' . $val['name'] . '</a>';
                $out .= "<span class='{$first}list list_$datatype'>$val</span>";
                $first = '';
            }
            $data = $out;
        }
        return $data;
    }

    /*
    ==================================================
    Store user-generated data
    ==================================================
    */
    function set_field($name, $data)
    {
        return $this->data[$name] = $data;
    }

    /*
    ==================================================
    Run a helper within a PodPage or WP template
    ==================================================
    */
    function pod_helper($helper, $value = null, $name = null)
    {
        $helper = mysql_real_escape_string(trim($helper));
        $result = pod_query("SELECT phpcode FROM {$this->prefix}pod_helpers WHERE name = '$helper' LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            $phpcode = mysql_result($result, 0);

            ob_start();
            eval("?>$phpcode");
            return ob_get_clean();
        }
    }

    /*
    ==================================================
    Get the post id
    ==================================================
    */
    function get_pod_id()
    {
        if (empty($this->data['pod_id']))
        {
            $this->data['pod_id'] = -1;

            $dt = $this->datatype_id;
            $tbl_row_id = $this->print_field('id');
            $result = pod_query("SELECT id FROM {$this->prefix}pod WHERE datatype = $dt AND tbl_row_id = '$tbl_row_id' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $row = mysql_fetch_assoc($result);
                $this->data['pod_id'] = $row['id'];
            }
        }
        return $this->data['pod_id'];
    }

    /*
    ==================================================
    Get pod or category dropdown values
    ==================================================
    */
    function get_dropdown_values($table = null, $field_name = null, $tbl_row_ids = null, $unique_vals = false)
    {
        // Category dropdown
        if (is_numeric($table))
        {
            $where = (false !== $unique_vals) ? "AND id NOT IN ($unique_vals)" : '';
            $sql = "
            SELECT
                t.term_id AS id, t.name
            FROM
                {$this->prefix}term_taxonomy tx
            INNER JOIN
                {$this->prefix}terms t ON t.term_id = tx.term_id
            WHERE
                tx.parent = $table AND tx.taxonomy = 'category' $where
            ";
        }
        // WP page dropdown
        elseif ('wp_page' == $table)
        {
            $where = (false !== $unique_vals) ? "AND id NOT IN ($unique_vals)" : '';
            $sql = "SELECT ID as id, post_title AS name FROM {$this->prefix}posts WHERE post_type = 'page' $where ORDER BY name ASC";
        }
        // WP post dropdown
        elseif ('wp_post' == $table)
        {
            $where = (false !== $unique_vals) ? "AND id NOT IN ($unique_vals)" : '';
            $sql = "SELECT ID as id, post_title AS name FROM {$this->prefix}posts WHERE post_type = 'post' $where ORDER BY name ASC";
        }
        // WP user dropdown
        elseif ('wp_user' == $table)
        {
            $where = (false !== $unique_vals) ? "WHERE id NOT IN ($unique_vals)" : '';
            $sql = "SELECT ID as id, display_name AS name FROM {$this->prefix}users $where ORDER BY name ASC";
        }
        // Pod table dropdown
        else
        {
            $where = (false !== $unique_vals) ? "WHERE id NOT IN ($unique_vals)" : '';
            $sql = "SELECT id, name FROM {$this->prefix}pod_tbl_$table $where ORDER BY name ASC";
        }

        $result = pod_query($sql);
        while ($row = mysql_fetch_assoc($result))
        {
            if (!empty($tbl_row_ids))
            {
                $row['active'] = in_array($row['id'], $tbl_row_ids);
            }
            else
            {
                $row['active'] = ($row['id'] == $_GET[$field_name]) ? true : false;
            }
            $val[] = $row;
        }
        return $val;
    }

    /*
    ==================================================
    Lookup values from a single relationship field
    ==================================================
    */
    function rel_lookup($field_id, $table = null)
    {
        $datatype_id = $this->datatype_id;
        $pod_id = $this->get_pod_id();
        $row_id = $this->data['id'];

        $result = pod_query("SELECT tbl_row_id FROM {$this->prefix}pod_rel WHERE pod_id = $pod_id AND field_id = $field_id");

        // Find all related IDs
        if (0 < mysql_num_rows($result))
        {
            $term_ids = array();
            while ($row = mysql_fetch_assoc($result))
            {
                $term_ids[] = $row['tbl_row_id'];
            }
            $term_ids = implode(', ', $term_ids);
        }
        else
        {
            return false;
        }

        // WP category
        if (is_numeric($table))
        {
            $result = pod_query("SELECT term_id AS id, name FROM {$this->prefix}terms WHERE term_id IN ($term_ids)");
        }
        // WP page or post
        elseif ('wp_page' == $table || 'wp_post' == $table)
        {
            $result = pod_query("SELECT ID as id, post_title AS name FROM {$this->prefix}posts WHERE ID IN ($term_ids)");
        }
        // WP user
        elseif ('wp_user' == $table)
        {
            $result = pod_query("SELECT ID as id, display_name AS name FROM {$this->prefix}users WHERE ID IN ($term_ids)");
        }
        // Pod table
        else
        {
            $result = pod_query("SELECT * FROM {$this->prefix}pod_tbl_$table WHERE id IN ($term_ids)");
        }

        // Put all related items into an array
        while ($row = mysql_fetch_assoc($result))
        {
            $data[$row['id']] = $row;
        }
        return $data;
    }
    /*
    ==================================================
    Return a single record
    ==================================================
    */
    function getRecordById($id)
    {
        $datatype = $this->datatype;
        if (!empty($datatype))
        {
            if (is_numeric($id))
            {
                $result = pod_query("SELECT * FROM {$this->prefix}pod_tbl_$datatype WHERE id = $id LIMIT 1");
            }
            else
            {
                // Get the slug column
                $result = pod_query("SELECT name FROM {$this->prefix}pod_fields WHERE coltype = 'slug' LIMIT 1");
                if (0 < mysql_num_rows($result))
                {
                    $field_name = mysql_result($result, 0);
                    $result = pod_query("SELECT * FROM {$this->prefix}pod_tbl_$datatype WHERE $field_name = '$id' LIMIT 1");
                }
            }

            if (0 < mysql_num_rows($result))
            {
                $row = mysql_fetch_assoc($result);
                $this->data = $row;
                return $row;
            }
            $this->data = false;
        }
        else
        {
            die('Error: Datatype not set');
        }
    }

    /*
    ==================================================
    Search and filter records
    ==================================================
    */
    function findRecords($orderby = 'id DESC', $rpp = null, $where = null, $sql = null)
    {
        $page = $this->page;
        $datatype = $this->datatype;
        $datatype_id = $this->datatype_id;
        $this->rpp = is_numeric($rpp) ? $rpp : $this->rpp;
        $rows_per_page = $this->rpp;
        $limit = ($rows_per_page * ($page - 1)) . ', ' . $rows_per_page;
        $where = empty($where) ? null : "AND t.$where";

        // Get this datatype's fields
        $result = pod_query("SELECT name FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id");
        while ($row = mysql_fetch_assoc($result))
        {
            $fields[] = $row['name'];
        }

        $i = 1;
        foreach ($_GET as $key => $val)
        {
            $val = mysql_real_escape_string(trim($val));
            if (!empty($val))
            {
                ${$key} = $val;
                if ('search' == $key)
                {
                    if (!empty($search))
                    {
                        $search = "AND (t.name LIKE '%$search%')";
                    }
                }
                elseif (in_array($key, $fields))
                {
                    $join .= "
                    INNER JOIN
                        {$this->prefix}pod_rel r$i ON r$i.field_id = (SELECT id FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$key') AND r$i.tbl_row_id = $val AND r$i.pod_id = p.id
                    ";
                    $i++;
                }
            }
        }

        if (empty($sql))
        {
            $sql = "
            SELECT
                SQL_CALC_FOUND_ROWS DISTINCT t.*
            FROM
                {$this->prefix}pod p
            $join
            INNER JOIN
                {$this->prefix}pod_tbl_$datatype t ON t.id = p.tbl_row_id
            WHERE
                p.datatype = $datatype_id
                $search
                $where
            ORDER BY
                t.$orderby
            LIMIT
                $limit
            ";
        }
        $this->result = pod_query($sql);
        $this->total_rows = pod_query("SELECT FOUND_ROWS()");
    }

    /*
    ==================================================
    Fetch the total row count
    ==================================================
    */
    function getTotalRows()
    {
        if (false === is_numeric($this->total_rows))
        {
            if ($row = mysql_fetch_array($this->total_rows))
            {
                $this->total_rows = $row[0];
            }
        }
        return $this->total_rows;
    }

    /*
    ==================================================
    Display the pagination controls
    ==================================================
    */
    function getPagination($label = 'Go to page:')
    {
        $page = $this->page;
        $rows_per_page = $this->rpp;
        $total_rows = $this->getTotalRows();
        $total_pages = ceil($total_rows / $rows_per_page);
        $type = $this->datatype;
        ob_start();

        $request_uri = "?type=$type&";
        foreach ($_GET as $key => $val)
        {
            if ('pg' != $key && 'type' != $key && !empty($val))
            {
                $request_uri .= $key . '=' . urlencode($val) . '&';
            }
        }
?>
    <span class="pager"><?php echo $label; ?>
<?php
        if (1 < $page)
        {
?>
    <a href="<?php echo $request_uri; ?>pg=1" class="pageNum firstPage">1</a>
<?php
        }
        if (1 < ($page - 100))
        {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page - 100); ?>" class="pageNum"><?php echo ($page - 100); ?></a>
<?php
        }
        if (1 < ($page - 10))
        {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page - 10); ?>" class="pageNum"><?php echo ($page - 10); ?></a>
<?php
        }
        for ($i = 2; $i > 0; $i--)
        {
            if (1 < ($page - $i))
            {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page - $i); ?>" class="pageNum"><?php echo ($page - $i); ?></a>
<?php
            }
        }
?>
    <span class="pageNum currentPage"><?php echo $page; ?></span>
<?php
        for ($i = 1; $i < 3; $i++)
        {
            if ($total_pages > ($page + $i))
            {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page + $i); ?>" class="pageNum"><?php echo ($page + $i); ?></a>
<?php
            }
        }
        if ($total_pages > ($page + 10))
        {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page + 10); ?>" class="pageNum"><?php echo ($page + 10); ?></a>
<?php
        }
        if ($total_pages > ($page + 100))
        {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page + 100); ?>" class="pageNum"><?php echo ($page + 100); ?></a>
<?php
        }
        if ($page < $total_pages)
        {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo $total_pages; ?>" class="pageNum lastPage"><?php echo $total_pages; ?></a>
<?php
        }
?>
    </span>
<?php
        $output = ob_get_clean();
        return $output;
    }

    /*
    ==================================================
    Display the list filters
    ==================================================
    */
    function getFilters($filters = null)
    {
        $datatype = $this->datatype;
        $datatype_id = $this->datatype_id;
?>
    <form method="get" action="">
        <input type="hidden" name="type" value="<?php echo $datatype; ?>" />
<?php
        if (empty($filters))
        {
            $result = pod_query("SELECT list_filters FROM {$this->prefix}pod_types WHERE id = $datatype_id LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $filters = $row['list_filters'];
        }

        if (!empty($filters))
        {
            $filters = explode(',', $filters);
            foreach ($filters as $key => $val)
            {
                $field_name = trim($val);
                $result = pod_query("SELECT pickval FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$field_name' LIMIT 1");
                $row = mysql_fetch_assoc($result);
                if (!empty($row['pickval']))
                {
                    $rel_table = $row['pickval'];
                    $data = $this->get_dropdown_values($rel_table, $field_name);
?>
    <select name="<?php echo $field_name; ?>" class="filter <?php echo $field_name; ?>" style="width:180px">
        <option value="">-- <?php echo ucwords(str_replace('_', ' ', $field_name)); ?> --</option>
<?php
                    foreach ($data as $key => $val)
                    {
                        $active = empty($val['active']) ? '' : ' selected';
?>
        <option value="<?php echo $val['id']; ?>"<?php echo $active; ?>><?php echo $val['name']; ?></option>
<?php
                    }
?>
    </select>
<?php
                }
            }
        }
        // Display the search box and submit button
?>
        <input type="text" name="search" value="<?php echo empty($_GET['search']) ? '' : $_GET['search']; ?>" style="width:120px" />
        <input type="submit" value="Filter" />
    </form>
<?php
    }

    /*
    ==================================================
    Display HTML for all datatype fields
    ==================================================
    */
    function showform($pod_id = null, $is_public = false, $public_columns = null)
    {
        $datatype = $this->datatype;
        $datatype_id = $this->datatype_id;
        $this->data['pod_id'] = $pod_id;

        $where = '';
        if (!empty($public_columns))
        {
            foreach ($public_columns as $key => $val)
            {
                if (is_array($public_columns[$key]))
                {
                    $where[] = $key;
                    $attribute[$key] = $val;
                }
                else
                {
                    $where[] = $val;
                }
            }
            $where = "AND f.name IN ('" . implode("','", $where) . "')";
        }

        $sql = "
        SELECT
            f.name, f.label, f.comment, f.coltype, f.pickval, f.required, f.`unique`, f.`multiple`
        FROM
            {$this->prefix}pod_types t
        INNER JOIN
            {$this->prefix}pod_fields f ON f.datatype = t.id
        WHERE
            t.name = '$datatype'
            $where
        ORDER BY
            f.weight ASC
        ";
        $result = pod_query($sql);
        while ($row = mysql_fetch_assoc($result))
        {
            $fields[$row['name']] = $row;
        }

        $sql = "
        SELECT
            t.*
        FROM
            {$this->prefix}pod p
        INNER JOIN
            {$this->prefix}pod_tbl_$datatype t ON t.id = p.tbl_row_id
        WHERE
            p.id = $pod_id
        LIMIT
            1
        ";
        $result = pod_query($sql);
        $tbl_cols = mysql_fetch_assoc($result);
?>
    <div><input type="hidden" class="form num pod_id" value="<?php echo $pod_id; ?>" /></div>
<?php
        foreach ($fields as $key => $field_array)
        {
            $label = $field_array['label'];
            $label = empty($label) ? ucwords($key) : $label;
            $comment = $field_array['comment'];
            $coltype = $field_array['coltype'];
            $pickval = $field_array['pickval'];
            $attr = $attribute[$key];
            $attr['required'] = $field_array['required'];
            $attr['unique'] = $field_array['unique'];
            $attr['multiple'] = $field_array['multiple'];

            if (1 == $field_array['required'])
            {
                $label .= ' <span class="red">*</span>';
            }

            if (!empty($pickval))
            {
                $val = array();
                $tbl_row_ids = array();
                $table = $pickval;

                $result = pod_query("SELECT id FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$key' LIMIT 1");
                $row = mysql_fetch_assoc($result);
                $field_id = $row['id'];

                $result = pod_query("SELECT tbl_row_id FROM {$this->prefix}pod_rel WHERE pod_id = $pod_id AND field_id = $field_id");
                while ($row = mysql_fetch_assoc($result))
                {
                    $tbl_row_ids[] = $row['tbl_row_id'];
                }

                // Use default values for public forms
                if (empty($tbl_row_ids) && !empty($attr['default']))
                {
                    $tbl_row_ids = $attr['default'];
                    if (!is_array($default))
                    {
                        $tbl_row_ids = explode(',', $tbl_row_ids);
                        foreach ($tbl_row_ids as $row_key => $row_val)
                        {
                            $tbl_row_ids[$row_key] = trim($row_val);
                        }
                    }
                }

                // If the PICK column is unique, get values already chosen
                $unique_vals = false;
                if (1 == $attr['unique'])
                {
                    $exclude = empty($pod_id) ? '' : "pod_id != $pod_id AND";
                    $result = pod_query("SELECT tbl_row_id FROM {$this->prefix}pod_rel WHERE $exclude field_id = $field_id");
                    if (0 < mysql_num_rows($result))
                    {
                        $unique_vals = array();
                        while ($row = mysql_fetch_assoc($result))
                        {
                            $unique_vals[] = $row['tbl_row_id'];
                        }
                        $unique_vals = implode(',', $unique_vals);
                    }
                }
                $this->data[$key] = $this->get_dropdown_values($table, null, $tbl_row_ids, $unique_vals);
            }
            else
            {
                if (empty($this->data[$key]) && !empty($attr['default']))
                {
                    $this->data[$key] = $attr['default'];
                }
                else
                {
                    $this->data[$key] = $tbl_cols[$key];
                }
                $this->get_field($key);
            }

            if ('id' != $key || -1 == $this->get_pod_id())
            {
                $this->build_field_html($key, $label, $comment, $coltype, $attr);
            }
        }
?>
    <div><input type="button" class="button" value="Save changes" onclick="saveForm()" /></div>
<?php
    }

    /*
    ==================================================
    Build public input form
    ==================================================
    */
    function publicForm($public_columns = null)
    {
        include realpath(dirname(__FILE__) . '/form.php');
    }

    /*
    ==================================================
    Build HTML for a single field
    ==================================================
    */
    function build_field_html($name, $label, $comment, $coltype, $attr)
    {
        $data = is_array($this->data[$name]) ? $this->data[$name] : stripslashes($this->data[$name]);
        $hidden = empty($attr['hidden']) ? '' : ' hidden';
?>
    <div class="leftside<?php echo $hidden; ?>">
        <?php echo $label; ?>
<?php
        if (!empty($comment))
        {
?>
        <div class="comment"><?php echo $comment; ?></div>
<?php
        }
?>
    </div>
    <div class="rightside<?php echo $hidden; ?>">
<?php
        // Boolean checkbox
        if ('bool' == $coltype)
        {
            $data = empty($data) ? '' : ' checked';
?>
    <input type="checkbox" class="form bool <?php echo $name; ?>"<?php echo $data; ?> />
<?php
        }
        elseif ('date' == $coltype)
        {
            $data = empty($data) ? date("Y-m-d H:i:s") : $data;
?>
    <input type="text" class="form date <?php echo $name; ?>" value="<?php echo $data; ?>" />
<?php
        }
        // File upload box
        elseif ('file' == $coltype)
        {
?>
    <input type="text" class="form file <?php echo $name; ?>" value="<?php echo $data; ?>" />
    <a href="javascript:;" onclick="active_file = '<?php echo $name; ?>'; jQuery('#dialog').jqmShow()">select</a> after
    <a href="media-upload.php" target="_blank">uploading</a>
<?php
        }
        // Standard text box
        elseif ('num' == $coltype || 'txt' == $coltype || 'slug' == $coltype)
        {
?>
    <input type="text" class="form <?php echo $coltype . ' ' . $name; ?>" value="<?php echo str_replace('"', '&quot;', $data); ?>" />
<?php
        }
        // Textarea box
        elseif ('desc' == $coltype)
        {
?>
    <textarea class="form desc <?php echo $name; ?>" id="desc-<?php echo $name; ?>"><?php echo $data; ?></textarea>
<?php
        }
        // Textarea box (without WYSIWYG)
        elseif ('code' == $coltype)
        {
?>
    <textarea class="form code <?php echo $name; ?>" id="code-<?php echo $name; ?>"><?php echo $data; ?></textarea>
<?php
        }
        else
        {
            // Multi-select list
            if (1 == $attr['multiple'])
            {
?>
    <div class="form pick <?php echo $name; ?>">
<?php
                if (!empty($data))
                {
                    foreach ($data as $key => $val)
                    {
                        $active = empty($val['active']) ? '' : ' active';
?>
        <div class="option<?php echo $active; ?>" value="<?php echo $val['id']; ?>"><?php echo $val['name']; ?></div>
<?php
                    }
                }
?>
    </div>
<?php
            }
            // Single-select list
            else
            {
?>
    <select class="form pick1 <?php echo $name; ?>">
        <option value="">-- Select one --</option>
<?php
                if (!empty($data))
                {
                    foreach ($data as $key => $val)
                    {
                        $selected = empty($val['active']) ? '' : ' selected';
?>
        <option value="<?php echo $val['id']; ?>"<?php echo $selected; ?>><?php echo $val['name']; ?></option>
<?php
                    }
                }
?>
    </select>
<?php
            }
        }
?>
    </div>
    <div class="clear"></div>
<?php
    }

    /*
    ==================================================
    Display the page template
    ==================================================
    */
    function showTemplate($tpl, $code = null)
    {
        if ('list' == $tpl || 'detail' == $tpl)
        {
            if (empty($code))
            {
                $result = pod_query("SELECT tpl_$tpl AS template FROM {$this->prefix}pod_types WHERE name = '{$this->datatype}' LIMIT 1");
                $row = mysql_fetch_assoc($result);
                $code = $row['template'];
            }

            if ('list' == $tpl)
            {
                while ($this->fetchRecord())
                {
                    echo preg_replace_callback("/({@(.*?)})/m", array($this, "magic_swap"), $code);
                }
            }
            elseif ('detail' == $tpl)
            {
                echo preg_replace_callback("/({@(.*?)})/m", array($this, "magic_swap"), $code);
            }
        }
    }

    /*
    ==================================================
    Replace magic tags with their values
    ==================================================
    */
    function magic_swap($in)
    {
        $name = $in[2];
        $before = $after = '';
        if (false !== strpos($name, ','))
        {
            list($name, $helper, $before, $after) = explode(',', $name);
        }
        if ('detail_url' == $name)
        {
            return get_bloginfo('url') . '/detail/?type=' . $this->datatype . '&id=' . $this->print_field('id');
        }
        else
        {
            $value = $this->print_field($name);
            if (!empty($value) || 0 === $value)
            {
                // Use helper if necessary
                if (!empty($helper))
                {
                    $value = $this->pod_helper($helper, $this->get_field($name), $name);
                }
                return $before . $value . $after;
            }
        }
    }
}

