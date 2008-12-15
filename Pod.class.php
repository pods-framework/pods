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
        $this->page = empty($_GET['p']) ? 1 : $_GET['p'];

        if (null != $datatype)
        {
            $this->datatype = trim($datatype);
            $result = mysql_query("SELECT id FROM {$this->prefix}pod_types WHERE name = '$datatype' LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $this->datatype_id = $row['id'];

            if (null != $id && is_numeric($id))
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

            $result = mysql_query("SELECT id, pickval FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$name' LIMIT 1") or die(mysql_error());
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
                $val = is_numeric($datatype) ? $val['name'] : "<a href='/detail/?type=$datatype&id=$key'>" . $val['name'] . '</a>';
                $out .= "<span class='{$first}list list_$datatype'>$val</span>";
                $first = '';
            }
            $data = $out;
        }
        return $data;
    }

    /*
    ==================================================
    Get the post id
    ==================================================
    */
    function get_post_id()
    {
        if (empty($this->data['post_id']))
        {
            $this->data['post_id'] = -1;

            $dt = $this->datatype_id;
            $row_id = $this->print_field('id');
            $result = mysql_query("SELECT post_id FROM {$this->prefix}pod WHERE datatype = $dt AND row_id = '$row_id' LIMIT 1");
            if (0 < mysql_num_rows($result))
            {
                $row = mysql_fetch_assoc($result);
                $this->data['post_id'] = $row['post_id'];
            }
        }
        return $this->data['post_id'];
    }

    /*
    ==================================================
    Get pod or category dropdown values
    ==================================================
    */
    function get_dropdown_values($table = null, $field_name = null, $term_ids = null)
    {
        // Category dropdown
        if (is_numeric($table))
        {
            $sql = "
            SELECT
                t.term_id AS id, t.name
            FROM
                {$this->prefix}term_taxonomy tx
            INNER JOIN
                {$this->prefix}terms t ON t.term_id = tx.term_id
            WHERE
                tx.parent = $table AND tx.taxonomy = 'category'
            ";
        }
        // Pod table dropdown
        else
        {
            $sql = "SELECT id, name FROM {$this->prefix}tbl_$table ORDER BY name ASC";
        }

        $result = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($result))
        {
            if (!empty($term_ids))
            {
                $row['active'] = in_array($row['id'], $term_ids);
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
        $post_id = $this->get_post_id();
        $row_id = $this->data['id'];

        $result = mysql_query("SELECT term_id FROM {$this->prefix}pod_rel WHERE post_id = $post_id AND field_id = $field_id") or die(mysql_error());

        // Find all related IDs
        if (0 < mysql_num_rows($result))
        {
            $term_ids = array();
            while ($row = mysql_fetch_assoc($result))
            {
                $term_ids[] = $row['term_id'];
            }
            $term_ids = implode(', ', $term_ids);
        }
        else
        {
            return false;
        }

        // The default table is wp_posts
        if (is_numeric($table))
        {
            $result = mysql_query("SELECT term_id AS id, name FROM {$this->prefix}terms WHERE term_id IN ($term_ids)");
        }
        else
        {
            $result = mysql_query("SELECT * FROM {$this->prefix}tbl_$table WHERE id IN ($term_ids)");
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
            $result = mysql_query("SELECT * FROM {$this->prefix}tbl_$datatype WHERE id = $id LIMIT 1");
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
    function findRecords($orderby = 'id DESC', $rpp = null)
    {
        $page = $this->page;
        $datatype = $this->datatype;
        $datatype_id = $this->datatype_id;
        $this->rpp = is_numeric($rpp) ? $rpp : $this->rpp;
        $rows_per_page = $this->rpp;
        $limit = ($rows_per_page * ($page - 1)) . ', ' . $rows_per_page;

        // Get this datatype's fields
        $result = mysql_query("SELECT name FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id");
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
                        $search = "AND t.name LIKE '%$search%'";
                    }
                }
                elseif (in_array($key, $fields))
                {
                    $join .= "
                    INNER JOIN
                        {$this->prefix}pod_rel r$i ON r$i.field_id = (SELECT id FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$key') AND r$i.term_id = $val AND r$i.post_id = p.post_id
                    ";
                    $i++;
                }
            }
        }

        $sql = "
        SELECT
            SQL_CALC_FOUND_ROWS DISTINCT t.*
        FROM
            {$this->prefix}pod p
        $join
        INNER JOIN
            {$this->prefix}tbl_$datatype t ON t.id = p.row_id
        WHERE
            p.datatype = $datatype_id
            $search
        ORDER BY
            t.$orderby
        LIMIT
            $limit
        ";
        $this->result = mysql_query($sql) or die(mysql_error());
        $this->total_rows = mysql_query("SELECT FOUND_ROWS()");
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
            if ('p' != $key && 'type' != $key && !empty($val))
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
    <a href="<?php echo $request_uri; ?>p=1" class="pageNum firstPage">1</a>
<?php
        }
        if (1 < ($page - 100))
        {
?>
    <a href="<?php echo $request_uri; ?>p=<?= ($page - 100) ?>" class="pageNum"><?= ($page - 100) ?></a>
<?php
        }
        if (1 < ($page - 10))
        {
?>
    <a href="<?php echo $request_uri; ?>p=<?= ($page - 10) ?>" class="pageNum"><?= ($page - 10) ?></a>
<?php
        }
        for ($i = 2; $i > 0; $i--)
        {
            if (1 < ($page - $i))
            {
?>
    <a href="<?php echo $request_uri; ?>p=<?= ($page - $i) ?>" class="pageNum"><?= ($page - $i) ?></a>
<?php
            }
        }
?>
    <span class="pageNum currentPage"><?= $page ?></span>
<?php
        for ($i = 1; $i < 3; $i++)
        {
            if ($total_pages > ($page + $i))
            {
?>
    <a href="<?php echo $request_uri; ?>p=<?= ($page + $i) ?>" class="pageNum"><?= ($page + $i) ?></a>
<?php
            }
        }
        if ($total_pages > ($page + 10))
        {
?>
    <a href="<?php echo $request_uri; ?>p=<?= ($page + 10) ?>" class="pageNum"><?= ($page + 10) ?></a>
<?php
        }
        if ($total_pages > ($page + 100))
        {
?>
    <a href="<?php echo $request_uri; ?>p=<?= ($page + 100) ?>" class="pageNum"><?= ($page + 100) ?></a>
<?php
        }
        if ($page < $total_pages)
        {
?>
    <a href="<?php echo $request_uri; ?>p=<?= $total_pages ?>" class="pageNum lastPage"><?= $total_pages ?></a>
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
            $result = mysql_query("SELECT list_filters FROM {$this->prefix}pod_types WHERE id = $datatype_id LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $filters = $row['list_filters'];
        }

        if (!empty($filters))
        {
            $filters = explode(',', $filters);
            foreach ($filters as $key => $val)
            {
                $field_name = trim($val);
                $result = mysql_query("SELECT pickval FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$field_name' LIMIT 1");
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
    function showform($post_id = null, $is_public = false, $public_columns = null)
    {
        $datatype = $this->datatype;
        $datatype_id = $this->datatype_id;
        $this->data['post_id'] = $post_id;

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
            f.name, f.label, f.comment, f.coltype, f.pickval, f.required
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
        $result = mysql_query($sql) or die(mysql_error());
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
            {$this->prefix}tbl_$datatype t ON t.id = p.row_id
        WHERE
            p.post_id = $post_id
        LIMIT
            1
        ";
        $result = mysql_query($sql) or die(mysql_error());
        $tbl_cols = mysql_fetch_assoc($result);
?>
    <div><input type="hidden" class="form num post_id" value="<?php echo $post_id; ?>" /></div>
<?php
        foreach ($fields as $key => $field_array)
        {
            $label = $field_array['label'];
            $label = empty($label) ? ucwords($key) : $label;
            $comment = $field_array['comment'];
            $coltype = $field_array['coltype'];
            $pickval = $field_array['pickval'];
            $attr = $attribute[$key];

            if (1 == $field_array['required'])
            {
                $label .= ' <span class="red">*</span>';
            }

            if (!empty($pickval))
            {
                $val = array();
                $term_ids = array();
                $table = $pickval;

                $result = mysql_query("SELECT id FROM {$this->prefix}pod_fields WHERE datatype = $datatype_id AND name = '$key' LIMIT 1") or die(mysql_error());
                $row = mysql_fetch_assoc($result);
                $field_id = $row['id'];

                $result = mysql_query("SELECT term_id FROM {$this->prefix}pod_rel WHERE post_id = $post_id AND field_id = $field_id");
                while ($row = mysql_fetch_assoc($result))
                {
                    $term_ids[] = $row['term_id'];
                }

                // Use default values for public forms
                if (empty($term_ids) && !empty($attr['default']))
                {
                    $term_ids = $attr['default'];
                    if (!is_array($default))
                    {
                        $term_ids = explode(',', $term_ids);
                        foreach ($term_ids as $term_key => $term_val)
                        {
                            $term_ids[$term_key] = trim($term_val);
                        }
                    }
                }
                $this->data[$key] = $this->get_dropdown_values($table, null, $term_ids);
            }
            else
            {
                $this->data[$key] = $tbl_cols[$key];
                $this->get_field($key);
            }

            if ('id' != $key || -1 == $this->get_post_id())
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
    <a href="media-upload.php" class="thickbox">uploading</a>
<?php
        }
        // Standard text box
        elseif ('num' == $coltype || 'txt' == $coltype)
        {
?>
    <input type="text" class="form <?php echo $coltype . ' ' . $name; ?>" value="<?php echo $data; ?>" />
<?php
        }
        // Textarea box
        elseif ('desc' == $coltype)
        {
?>
    <textarea class="form desc <?php echo $name; ?>"><?php echo $data; ?></textarea>
<?php
        }
        // Multi-select list
        else
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
                $result = mysql_query("SELECT tpl_$tpl AS template FROM {$this->prefix}pod_types WHERE name = '{$this->datatype}' LIMIT 1");
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
            list($name, $widget, $before, $after) = explode(',', $name);
        }
        if ('detail_url' == $name)
        {
            return '/detail/?type=' . $this->datatype . '&id=' . $this->print_field('id');
        }
        elseif ('edit_url' == $name)
        {
            return '/wp-admin/post.php?action=edit&post=' . $this->get_post_id();
        }
        else
        {
            $value = $this->print_field($name);
            if (!empty($value))
            {
                // Use widget if necessary
                if (!empty($widget))
                {
                    $value = $this->get_field($name);
                    $widget = mysql_real_escape_string(trim($widget));
                    $result = mysql_query("SELECT phpcode FROM {$this->prefix}pod_widgets WHERE name = '$widget' LIMIT 1");
                    if (0 < mysql_num_rows($result))
                    {
                        $row = mysql_fetch_assoc($result);
                        $phpcode = $row['phpcode'];

                        ob_start();
                        eval($phpcode);
                        $value = ob_get_clean();
                    }
                }
                return $before . $value . $after;
            }
        }
    }
}

