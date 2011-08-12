<?php
// Brought in pre-2.0, this is revamped / refactored in 2.0 so this is really just a temporary file
if (function_exists('pods_ui_manage')) {
    add_action('admin_notices', 'pods_ui_notice');
    function pods_ui_notice () {
?>
    <div class="error fade">
        <p><strong>NOTICE:</strong> We've merged Pods and Pods UI! The separate Pods UI plugin will no longer work with Pods since it is now built-in. You should now deactivate and remove the Pods UI plugin. Once deactivated, the new Pods UI included within Pods core will kick in and take over.</p>
    </div>
<?php
    }
}
else {
function pods_ui_manage ($obj)
{
    if(!is_array($obj)&&!is_object($obj)&&0<strlen($obj))
    {
        parse_str($obj,$obj);
    }
    if(is_array($obj)&&isset($obj['pod']))
    {
        $object = new Pod($obj['pod']);
        $object->ui = $obj;
    }
    else
    {
        $object = $obj;
    }
    if(!is_object($object))
    {
        echo '<strong>Error:</strong> Pods UI needs an object to run from, see the User Guide for more information.';
        return false;
    }
    if(!isset($object->ui)||!is_array($object->ui))
    {
        $object->ui = array();
    }
    $object->ui['num'] = (isset($object->ui['num'])&&is_numeric($object->ui['num'])?$object->ui['num']:'');
    $unique_md5 = $object->datatype;
    if(isset($_GET['page']))
    {
        $unique_md5 .= '_'.$_GET['page'];
    }
    if(0<$object->ui['num'])
    {
        $unique_md5 .= '_'.$object->ui['num'];
    }
    $unique_md5 = '_'.md5($unique_md5);
    $object->ui['unique_md5'] = (isset($object->ui['unique_md5'])?'_'.$object->ui['unique_md5']:$unique_md5);
    $object->ui['title'] = (isset($object->ui['title'])?$object->ui['title']:ucwords(str_replace('_',' ',$object->datatype)));
    $object->ui['item'] = (isset($object->ui['item'])?$object->ui['item']:ucwords(str_replace('_',' ',$object->datatype)));
    $object->ui['label'] = (isset($object->ui['label'])?$object->ui['label']:null);
    $object->ui['label_add'] = (isset($object->ui['label_add'])?$object->ui['label_add']:null);
    $object->ui['label_edit'] = (isset($object->ui['label_edit'])?$object->ui['label_edit']:null);
    $object->ui['label_duplicate'] = (isset($object->ui['label_duplicate'])?$object->ui['label_duplicate']:null);
    $object->ui['icon'] = (isset($object->ui['icon'])?$object->ui['icon']:null);
    $object->ui['session_filters'] = (isset($object->ui['session_filters'])?false:null);
    $object->ui['user_per_page'] = (isset($object->ui['user_per_page'])?false:null);
    $object->ui['user_sort'] = (isset($object->ui['user_sort'])?false:null);
    $object->ui['columns'] = (isset($object->ui['columns'])?pods_ui_strtoarray($object->ui['columns']):array('name'=>'Name','created'=>'Date Created','modified'=>'Last Modified'));
    $object->ui['columns'] = apply_filters('pods_ui_columns', $object->ui['columns'], $object);
    $object->ui['columns'] = apply_filters('pods_ui_columns_' . $object->datatype, $object->ui['columns'], $object);
    $object->ui['add_fields'] = (isset($object->ui['add_fields'])?pods_ui_strtoarray($object->ui['add_fields']):null);
    $object->ui['edit_fields'] = (isset($object->ui['edit_fields'])?pods_ui_strtoarray($object->ui['edit_fields']):null);
    $object->ui['duplicate_fields'] = (isset($object->ui['duplicate_fields'])?pods_ui_strtoarray($object->ui['duplicate_fields']):null);
    $object->ui['custom_list'] = (isset($object->ui['custom_list'])?$object->ui['custom_list']:null);
    $object->ui['custom_reorder'] = (isset($object->ui['custom_reorder'])?$object->ui['custom_reorder']:null);
    $object->ui['custom_edit'] = (isset($object->ui['custom_edit'])?$object->ui['custom_edit']:null);
    $object->ui['custom_add'] = (isset($object->ui['custom_add'])?$object->ui['custom_add']:$object->ui['custom_edit']);
    $object->ui['custom_duplicate'] = (isset($object->ui['custom_duplicate'])?$object->ui['custom_duplicate']:$object->ui['custom_edit']);
    $object->ui['custom_delete'] = (isset($object->ui['custom_delete'])?$object->ui['custom_delete']:null);
    $object->ui['custom_save'] = (isset($object->ui['custom_save'])?$object->ui['custom_save']:null);
    $object->ui['custom_actions'] = (isset($object->ui['custom_actions'])?pods_ui_strtoarray($object->ui['custom_actions']):array());
    $object->ui['manage_content'] = (isset($object->ui['manage_content'])?$object->ui['manage_content']:null);
    $object->ui['action_after_save'] = (isset($object->ui['action_after_save'])?$object->ui['action_after_save']:'edit');
    $object->ui['action'] = (isset($object->ui['action'])?$object->ui['action']:pods_ui_var('action'.$object->ui['num'],'get','manage'));
    $object->ui['edit_link'] = (isset($object->ui['edit_link'])&&!empty($object->ui['edit_link'])?$object->ui['edit_link']:null);
    $object->ui['view_link'] = (isset($object->ui['view_link'])&&!empty($object->ui['view_link'])?$object->ui['view_link']:null);
    $object->ui['duplicate_link'] = (isset($object->ui['duplicate_link'])&&!empty($object->ui['duplicate_link'])?$object->ui['duplicate_link']:null);
    $object->ui['reorder'] = (isset($object->ui['reorder'])?$object->ui['reorder']:null);
    $object->ui['reorder_columns'] = (isset($object->ui['reorder_columns'])?pods_ui_strtoarray($object->ui['reorder_columns']):$object->ui['columns']);
    $object->ui['reorder_sort'] = (isset($object->ui['reorder_sort'])?$object->ui['reorder_sort']:'t.'.$object->ui['reorder']);
    $object->ui['sort'] = (isset($object->ui['sort'])?$object->ui['sort']:'t.name');
    $object->ui['sortable'] = (isset($object->ui['sortable'])?false:null);
    if($object->ui['sortable']===null)
    {
        if(pods_ui_var('reset_sort'.$object->ui['num'])!==false)
        {
            pods_ui_var_set('sort'.$object->ui['unique_md5'],'','user');
        }
        if(pods_ui_var('sort'.$object->ui['num'])===false&&$object->ui['user_sort']!==false&&pods_ui_var('sort'.$object->ui['unique_md5'],'user')!==false&&0<strlen(pods_ui_var('sort'.$object->ui['unique_md5'],'user')))
        {
            $object->ui['sort'] = $object->ui['reorder_sort'] = pods_ui_var('sort'.$object->ui['unique_md5'],'user');
        }
        if(pods_ui_var('sort'.$object->ui['num'])!==false)
        {
            $dir = (pods_ui_var('sortdir'.$object->ui['num'])!==false?pods_ui_var('sortdir'.$object->ui['num']):'asc');
            $sort = pods_ui_var('sort'.$object->ui['num']);
            if(in_array($sort,array('created','modified')))
                $sort = 'p.`'.$sort.'`';
            $object->ui['sort'] = trim($sort.' '.strtoupper($dir).','.$object->ui['sort'],' ,');
            if($object->ui['user_per_page']!==false)
            {
                pods_ui_var_set('sort'.$object->ui['unique_md5'],$object->ui['sort'],'user');
            }
        }
    }
    $object->ui['fields'] = ($object->ui['sortable']===null?pods_ui_fields($object->datatype_id):false);
    $object->ui['limit'] = (isset($object->ui['limit'])?$object->ui['limit']:25);
    $object->ui['reorder_limit'] = (isset($object->ui['reorder_limit'])?$object->ui['reorder_limit']:1000);
    if($object->ui['user_per_page']!==false&&pods_ui_var('limit'.$object->ui['unique_md5'],'user')!==false&&0<pods_ui_var('limit'.$object->ui['unique_md5'],'user'))
    {
        $object->ui['limit'] = $object->ui['reorder_limit'] = pods_ui_var('limit'.$object->ui['unique_md5'],'user');
    }
    if(pods_ui_var('limit'.$object->ui['num'])!==false&&0<intval(pods_ui_var('limit'.$object->ui['num'])))
    {
        $object->ui['limit'] = $object->ui['reorder_limit'] = intval(pods_ui_var('limit'.$object->ui['num']));
        if($object->ui['user_per_page']!==false)
        {
            pods_ui_var_set('limit'.$object->ui['unique_md5'],$object->ui['limit'],'user');
        }
    }
    $object->ui['where'] = (isset($object->ui['where'])?$object->ui['where']:'');
    $object->ui['edit_where'] = (isset($object->ui['edit_where'])?$object->ui['edit_where']:null);
    $object->ui['duplicate_where'] = (isset($object->ui['duplicate_where'])?$object->ui['duplicate_where']:$object->ui['edit_where']);
    $object->ui['delete_where'] = (isset($object->ui['delete_where'])?$object->ui['delete_where']:$object->ui['edit_where']);
    $object->ui['reorder_where'] = (isset($object->ui['reorder_where'])?$object->ui['reorder_where']:$object->ui['where']);
    $object->ui['select'] = (isset($object->ui['select'])?$object->ui['select']:null);
    $object->ui['join'] = (isset($object->ui['join'])?$object->ui['join']:null);
    $object->ui['groupby'] = (isset($object->ui['groupby'])?$object->ui['groupby']:null);
    $object->ui['having'] = (isset($object->ui['having'])?$object->ui['having']:null);
    $object->ui['sql'] = (isset($object->ui['sql'])?$object->ui['sql']:null);
    $object->ui['reorder_sql'] = (isset($object->ui['reorder_sql'])?$object->ui['reorder_sql']:$object->ui['sql']);
    $object->ui['search'] = (isset($object->ui['search'])&&is_bool($object->ui['search'])&&$object->ui['search']===false?false:null);
    $object->ui['search_across'] = (isset($object->ui['search_across'])&&is_bool($object->ui['search_across'])&&$object->ui['search_across']===false?$object->ui['search']:null);
    $object->ui['search_across_picks'] = (isset($object->ui['search_across_picks'])&&is_bool($object->ui['search_across_picks'])&&$object->ui['search_across_picks']===false?true:false);
    $object->ui['filters'] = (isset($object->ui['filters'])&&!empty($object->ui['filters'])?pods_ui_strtoarray($object->ui['filters']):null);
    $object->ui['custom_filters'] = (isset($object->ui['custom_filters'])?$object->ui['custom_filters']:null);
    $object->ui['disable_actions'] = (isset($object->ui['disable_actions'])?pods_ui_strtoarray($object->ui['disable_actions']):array());
    if(in_array($object->ui['action'],$object->ui['disable_actions']))
    {
        $object->ui['action'] = 'manage';
    }
    $object->ui['hide_actions'] = (isset($object->ui['hide_actions'])?pods_ui_strtoarray($object->ui['hide_actions']):array());
    $object->ui['wpcss'] = (isset($object->ui['wpcss'])?true:null);
    $object->ui = apply_filters('pods_ui_options', $object->ui, $object);
    if($object->ui['wpcss']!==null)
    {
        wp_print_styles(array('global','wp-admin'));
        global $user_ID;
        get_currentuserinfo();
        $color = get_usermeta($user_ID,'admin_color');
        if(strlen($color)<1)
        {
            $color = 'fresh';
        }
?>
<link rel='stylesheet' id='colors-css'  href='<?php bloginfo('wpurl'); echo '/wp-admin/css/colors-'.$color.'.css?ver='.date_i18n('Ymd'); ?>' type='text/css' media='all' />
<?php
    }
    if($object->ui['action']=='delete')
    {
        $access = $object->ui['delete_where'];
        $continue_delete = true;
        $delete = new Pod($object->datatype,pods_ui_var('id'.$object->ui['num']));
        if($delete->data!==false)
        {
            $check = pods_ui_verify_access($delete,$access,'delete');
            if($check===false)
            {
?>
<h2>Edit <?php echo $object->ui['item']; ?> <small>(<a href="<?php echo pods_ui_var_update(array('action'.$object->ui['num']=>'manage')); ?>">&laquo; Back to Manage</a>)</small></h2>
<?php
                pods_ui_message('<strong>Error:</strong> You do not have the permissions required to delete this '.$object->ui['item'].'.',2);
                $continue_delete = false;
            }
            if($continue_delete)
            {
                $delete->ui = $object->ui;
                if($object->ui['custom_delete']===null||!function_exists($object->ui['custom_delete']))
                {
                    pods_ui_delete($delete);
                }
                else
                {
                    $object->ui['custom_delete']($delete);
                }
            }
        }
        else
        {
            pods_ui_message($object->ui['item'].' <strong>not found</strong>, cannot delete.',2);
        }
    }
    if(!is_numeric($object->ui['num']))
    {
?>
<div class="wrap">
    <div id="icon-edit-pages" class="icon32"<?php if(null!==$object->ui['icon']){ ?> style="background-position:0 0;background-image:url(<?php echo $object->ui['icon']; ?>);"<?php } ?>><br /></div>
<?php
    }
    if($object->ui['action']=='add')
    {
        if($object->ui['custom_add']===null||!function_exists($object->ui['custom_add']))
        {
            pods_ui_form($object,1);
        }
        else
        {
            $object->ui['custom_add']($object);
        }
    }
    elseif($object->ui['action']=='edit')
    {
        $access = $object->ui['edit_where'];
        $continue_edit = true;
        $edit = new Pod($object->datatype,pods_ui_var('id'.$object->ui['num']));
        if($edit->data!==false)
        {
            $check = pods_ui_verify_access($edit,$access,'edit');
            if($check===false)
            {
?>
<h2>Edit <?php echo $object->ui['item']; ?> <small>(<a href="<?php echo pods_ui_var_update(array('action'.$object->ui['num']=>'manage')); ?>">&laquo; Back to Manage</a>)</small></h2>
<?php
                pods_ui_message('<strong>Error:</strong> You do not have the permissions required to edit this '.$object->ui['item'].'.',2);
                $continue_edit = false;
            }
            if($continue_edit)
            {
                $edit->ui = $object->ui;
                if($object->ui['custom_edit']===null||!function_exists($object->ui['custom_edit']))
                {
                    pods_ui_form($edit);
                }
                else
                {
                    $object->ui['custom_edit']($edit);
                }
            }
        }
        else
        {
            pods_ui_message($object->ui['item'].' <strong>not found</strong>, cannot edit.',2);
        }
    }
    elseif($object->ui['action']=='duplicate')
    {
        $access = $object->ui['duplicate_where'];
        $continue_duplicate = true;
        $duplicate = new Pod($object->datatype,pods_ui_var('id'.$object->ui['num']));
        if($duplicate->data!==false)
        {
            $check = pods_ui_verify_access($duplicate,$access,'duplicate');
            if($check===false)
            {
?>
<h2>Edit <?php echo $object->ui['item']; ?> <small>(<a href="<?php echo pods_ui_var_update(array('action'.$object->ui['num']=>'manage')); ?>">&laquo; Back to Manage</a>)</small></h2>
<?php
                pods_ui_message('<strong>Error:</strong> You do not have the permissions required to duplicate this '.$object->ui['item'].'.',2);
                $continue_duplicate = false;
            }
            if($continue_duplicate)
            {
                $duplicate->ui = $object->ui;
                if($object->ui['custom_duplicate']===null||!function_exists($object->ui['custom_duplicate']))
                {
                    pods_ui_form($duplicate,0,1);
                }
                else
                {
                    $object->ui['custom_duplicate']($duplicate);
                }
            }
        }
        else
        {
            pods_ui_message($object->ui['item'].' <strong>not found</strong>, cannot duplicate.',2);
        }
    }
    else
    {
        if($object->ui['action']=='save')
        {
            if($object->ui['custom_save']!==null||function_exists($object->ui['custom_save']))
            {
                $object->ui['custom_save']($object);
            }
        }
        if(!empty($object->ui['custom_actions']))
        {
            $custom = new Pod($object->datatype,pods_ui_var('id'.$object->ui['num']));
            $custom->ui = $object->ui;
            if($custom->data!==false)
            {
                foreach($custom->ui['custom_actions'] as $action=> $function_name)
                {
                    $function_name = (string) $function_name;
                    if (pods_ui_var('action'.$custom->ui['num'])==$action && null !== $function_name && 0 < strlen($function_name) && function_exists($function_name))
                    {
                        $function_name($custom);
                        return;
                    }
                }
            }
        }
        if($object->ui['custom_list']!==null&&function_exists($object->ui['custom_list']))
        {
            $object->ui['custom_list']($object);
        }
        else
        {
            $oldget = $_GET;
            if(false===$object->ui['search'])
            {
                $_GET['search'] = '';
            }
            else
            {
                $search = pods_ui_var('search'.$object->ui['num'],$oldget,false);
                if(false!==$search && $object->ui['session_filters']!==false)
                {
                    $search = pods_ui_var('search'.$object->ui['unique_md5'],'session');
                    if($search!==false)
                    {
                        $_GET['search'] = $search;
                    }
                }
                $search = pods_ui_var('search'.$object->ui['num'],$oldget);
                if($search!==false)
                {
                    $_GET['search'] = $search;
                    if($object->ui['session_filters']!==false)
                    {
                        pods_ui_var_set('search'.$object->ui['unique_md5'],$search,'session');
                    }
                }
            }
            if(pods_ui_var('reset_filters'.$object->ui['num'],$oldget)!==false||pods_ui_var('search')===false)
            {
                $_GET['search'] = '';
                if($object->ui['session_filters']!==false)
                    pods_ui_var_set('search'.$object->ui['unique_md5'],'','session');
            }
            $pg = pods_ui_var('pg'.$object->ui['num'],$oldget);
            if($pg!==false)
            {
                $_GET['pg'] = $pg;
            }
            if(pods_ui_var('pg')===false)
            {
                $_GET['pg'] = '';
            }
            if(false!==$object->ui['search']&&is_array($object->ui['filters']))
            {
                foreach($object->ui['filters'] as $filter)
                {
                    if($object->ui['session_filters']!==false)
                    {
                        $filter_value = pods_ui_var($filter.$object->ui['unique_md5'],'session');
                        if(false!==$filter_value)
                        {
                            $_GET[$filter] = $filter_value;
                        }
                    }
                    $filter_value = pods_ui_var($filter.$object->ui['num'],$oldget);
                    if(false!==$filter_value)
                    {
                        $_GET[$filter] = $filter_value;
                        if($object->ui['session_filters']!==false)
                        {
                            pods_ui_var_set($filter.$object->ui['unique_md5'],$filter_value,'session');
                        }
                    }
                    if(pods_ui_var('reset_filters'.$object->ui['num'],$oldget)!==false||pods_ui_var($filter)===false)
                    {
                        $_GET[$filter] = '';
                        $oldget[$filter] = '';
                        if($object->ui['session_filters']!==false)
                            pods_ui_var_set($filter.$object->ui['unique_md5'],'','session');
                    }
                }
            }
            $all_where = array();
            $the_search = '';
            if(pods_ui_var('reset_filters'.$object->ui['num'])===false&&$object->ui['search_across']!==false&&pods_ui_var('search'.$object->ui['num'])!==false)
            {
                $api = new PodAPI();
                $the_pod = $api->load_pod(array('id'=>$object->datatype_id));
                $the_fields = $the_pod['fields'];
                $the_search = " LIKE '%".pods_ui_var('search'.$object->ui['num'])."%'";
                foreach($the_fields as $the_field)
                {
                    if(in_array($the_field['coltype'],array('file')))
                    {
                        continue;
                    }
                    if($the_field['coltype']=='pick')
                    {
                        if($object->ui['search_across_picks']===false)
                        {
                            continue;
                        }
                        if($the_field['pickval']=='wp_user')
                        {
                            $all_where[] = '`'.$the_field['name'].'`.`display_name`'.$the_search;
                        }
                        if(strpos($the_field['pickval'],'wp_')!==false&&strpos($the_field['pickval'],'wp_')==0)
                        {
                            $all_where[] = '`'.$the_field['name'].'`.`post_title`'.$the_search;
                            $all_where[] = '`'.$the_field['name'].'`.`post_content`'.$the_search;
                        }
                        else
                        {
                            $all_where[] = '`'.$the_field['name'].'`.`name`'.$the_search;
                        }
                    }
                    else
                    {
                        $all_where[] = '`t`.`'.$the_field['name'].'`'.$the_search;
                    }
                }
                if(!empty($all_where))
                {
                    $the_search = $_GET['search'];
                    $_GET['search'] = '';
                    $all_where = '('.implode(' OR ',$all_where).')';
                    if($object->ui['reorder']!==null&&$object->ui['action']=='reorder')
                    {
                        if(!empty($object->ui['reorder_where']))
                        {
                            $object->ui['reorder_where'] .= ' AND '.$all_where;
                        }
                        else
                        {
                            $object->ui['reorder_where'] = $all_where;
                        }
                    }
                    else
                    {
                        if(!empty($object->ui['where']))
                        {
                            $object->ui['where'] .= ' AND '.$all_where;
                        }
                        else
                        {
                            $object->ui['where'] = $all_where;
                        }
                    }
                }
            }
            if($object->ui['reorder']!==null&&$object->ui['action']=='reorder')
            {
                $params = array();
                if (!empty($object->ui['select']))
                    $params['select'] = $object->ui['select'];
                $params['orderby'] = $object->ui['reorder_sort'];
                $params['limit'] = $object->ui['reorder_limit'];
                $params['where'] = $object->ui['reorder_where'];
                $params['join'] = $object->ui['join'];
                $params['groupby'] = $object->ui['groupby'];
                $params['having'] = $object->ui['having'];
                $params['sql'] = $object->ui['reorder_sql'];
                $object->findRecords($params);
            }
            else
            {
                $params = array();
                if (!empty($object->ui['select']))
                    $params['select'] = $object->ui['select'];
                $params['orderby'] = $object->ui['sort'];
                $params['limit'] = $object->ui['limit'];
                $params['where'] = $object->ui['where'];
                $params['join'] = $object->ui['join'];
                $params['groupby'] = $object->ui['groupby'];
                $params['having'] = $object->ui['having'];
                $params['sql'] = $object->ui['sql'];
                $object->findRecords($params);
            }
            $_GET = array_merge($oldget,$_GET);
            if(!empty($all_where))
            {
                $_GET['search'] = $the_search;
            }
            if(pods_ui_var('reset_filters'.$object->ui['num'])!==false)
            {
                unset($_GET['search']);
            }
        }
        if($object->ui['reorder']!==null&&$object->ui['action']=='reorder')
        {
?>
    <h2>Reorder <?php echo $object->ui['title']; ?> <small>(<a href="<?php echo pods_ui_var_update(array('action'.$object->ui['num']=>'manage')); ?>">&laquo; Back to Manage</a>)</small></h2>
<?php
        }
        else
        {
?>
    <h2>Manage <?php echo $object->ui['title']; ?></h2>
<?php
        }
        if(false!==$object->ui['search']||is_array($object->ui['filters'])||null!=$object->ui['custom_filters'])
        {
?>
    <form id="posts-filter" action="" method="get">
        <p class="search-box">
<?php
            $hidden_fields = array('search'.$object->ui['num']=>'','reset_filters'.$object->ui['num']=>'','pg'.$object->ui['num']=>'');
            if(is_array($object->ui['filters']))
            {
                $hidden_filters = array();
                foreach ($object->ui['filters'] as $filter)
                {
                    $hidden_filters[$filter.$object->ui['num']] = '';
                }
                $hidden_fields = array_merge($hidden_filters,$hidden_fields);
            }
            pods_ui_var_get($hidden_fields,'input');
?>
            <label class="screen-reader-text" for="page-search-input">Search:</label>
<?php
            if(false!==pods_ui_var('search'.$object->ui['num'])||(is_array($object->ui['filters'])&&false!==pods_ui_var($object->ui['filters'])))
            {
                $remove_filters = array('search'.$object->ui['num']);
                if(is_array($object->ui['filters']))
                {
                    $remove_filters = array_merge($remove_filters,$object->ui['filters']);
                }
                $reset_filters = array();
                foreach($remove_filters as $filter)
                {
                    $reset_filters[$filter] = '';
                }
                $reset_filters['reset_filters'.$object->ui['num']] = 1;
                $reset_filters = apply_filters('pods_ui_reset_filters', $reset_filters, $object);
?>
            <small>[<a href="<?php echo pods_ui_var_update($reset_filters); ?>">Reset Filters</a>]</small>
<?php
            }
            if ($object->ui['custom_filters']!==null)
            {
                echo $object->ui['custom_filters'];
            }
            if (is_array($object->ui['filters']))
            {
                foreach ($object->ui['filters'] as $key => $value)
                {
                    $field_settings = array('custom_output' => '');
                    if (is_array($value)) {
                        $field_settings = $value;
                        $field_name = trim($key);
                    }
                    else
                        $field_name = trim($value);
                    if (0 < strlen($field_settings['custom_output']))
                        echo $field_settings['custom_output'];
                    else {
                        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE datatype = {$object->datatype_id} AND name = '$field_name' LIMIT 1");
                        if ($row = @mysql_fetch_assoc($result))
                        {
                            $field_settings = array_merge($row, $field_settings);
                            if ('pick' == $field_settings['coltype'] && !empty($field_settings['pickval']))
                            {
                                $params = array(
                                    'table' => $field_settings['pickval'],
                                    'field_name' => $field_name,
                                    'tbl_row_ids' => false,
                                    'unique_vals' => false,
                                    'pick_filter' => $field_settings['pick_filter'],
                                    'pick_orderby' => $field_settings['pick_orderby']
                                );
                                $data = $object->get_dropdown_values($params);
                                if(!empty($data))
                                {
?>
            <select name="<?php echo $field_name.$object->ui['num']; ?>" class="ui_filter <?php echo $field_name; ?>">
                <option value="">-- <?php echo (!empty($field_settings['label'])?$field_settings['label']:ucwords(str_replace('_', ' ', $field_name))); ?> --</option>
<?php
                                    foreach ($data as $k => $v)
                                    {
                                        $active = (false!==pods_ui_var($field_name.$object->ui['num'])&&$v['id']==pods_ui_var($field_name.$object->ui['num'])) ? ' selected' : '';
?>
                <option value="<?php echo $v['id']; ?>"<?php echo $active; ?>><?php echo esc_html($v['name']); ?></option>
<?php
                                    }
?>
            </select>
<?php
                                }
                            }
                            elseif ('bool' == $field_settings['coltype']) {
                                if (isset($field_settings['type']) && 'radio' === $field_settings['type']) {
?>
            <label for="pods_ui_<?php echo $field_name.$object->ui['num']; ?>"><?php echo (!empty($field_settings['label'])?$field_settings['label']:ucwords(str_replace('_', ' ', $field_name))); ?>: <input type="radio" name="<?php echo $field_name.$object->ui['num']; ?>" id="pods_ui_<?php echo $field_name.$object->ui['num']; ?>" class="ui_filter <?php echo $field_name; ?>" value="1"<?php echo (1==pods_ui_var($field_name.$object->ui['num'])?' CHECKED':''); ?> /> Yes | <input type="radio" name="<?php echo $field_name.$object->ui['num']; ?>" class="ui_filter <?php echo $field_name; ?>" value="0"<?php echo (0==pods_ui_var($field_name.$object->ui['num'])?' CHECKED':''); ?> /> No</label>
<?php
                                }
                                else {
?>
            <label for="pods_ui_<?php echo $field_name.$object->ui['num']; ?>"><?php echo (!empty($field_settings['label'])?$field_settings['label']:ucwords(str_replace('_', ' ', $field_name))); ?>: <input type="checkbox" name="<?php echo $field_name.$object->ui['num']; ?>" id="pods_ui_<?php echo $field_name.$object->ui['num']; ?>" class="ui_filter <?php echo $field_name; ?>" value="1"<?php echo (1==pods_ui_var($field_name.$object->ui['num'])?' CHECKED':''); ?> /></label>
<?php
                                }
                            }
                            elseif (in_array($field_settings['coltype'], array('txt', 'num', 'slug', 'desc', 'code'))) {
?>
            <label for="pods_ui_<?php echo $field_name.$object->ui['num']; ?>"><?php echo (!empty($field_settings['label'])?$field_settings['label']:ucwords(str_replace('_', ' ', $field_name))); ?>: <input type="text" name="<?php echo $field_name.$object->ui['num']; ?>" id="pods_ui_<?php echo $field_name.$object->ui['num']; ?>" class="ui_filter <?php echo $field_name; ?>" value="<?php echo esc_attr(pods_ui_var($field_name.$object->ui['num'])); ?>" /></label>
<?php
                            }
                        }
                    }
                }
            }
            if(false!==$object->ui['search'])
            {
?>
            <input type="text" name="search<?php echo $object->ui['num']; ?>" id="page-search-input" value="<?php echo esc_attr(pods_ui_var('search'.$object->ui['num'])); ?>" />
<?php
            }
?>
            <input type="submit" value="Search" class="button" />
        </p>
    </form>
<?php
        }
        if(0<$object->getTotalRows())
        {
?>
    <div class="tablenav">
        <?php do_action('pods_ui_custom_buttons'); ?>
        <div class="tablenav-pages">
<?php
            if($object->ui['sortable']===null&&$object->ui['user_sort']!==false&&pods_ui_var('sort'.$object->ui['unique_md5'],'user')!==false&&0<strlen(pods_ui_var('sort'.$object->ui['unique_md5'],'user')))
            {
?>
            <small>[<a href="<?php echo pods_ui_var_update(array('reset_sort'.$object->ui['num']=>1,'sort'.$object->ui['num']=>''),false,false); ?>">Reset Sort</a>]</small>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php
            }
?>
            Show per page: <?php $ipp = 10; if($object->ui['limit']==$ipp){ ?><span class="page-numbers current"><?php echo $ipp; ?></span><?php } else { ?><a href="<?php echo pods_ui_var_update(array('limit'.$object->ui['num']=>$ipp,'pg'.$object->ui['num']=>''),false,false); ?>"><?php echo $ipp; ?></a><?php } ?> <?php $ipp = 25; if($object->ui['limit']==$ipp){ ?><span class="page-numbers current"><?php echo $ipp; ?></span><?php } else { ?><a href="<?php echo pods_ui_var_update(array('limit'.$object->ui['num']=>$ipp,'pg'.$object->ui['num']=>'')); ?>"><?php echo $ipp; ?></a><?php } ?> <?php $ipp = 50; if($object->ui['limit']==$ipp){ ?><span class="page-numbers current"><?php echo $ipp; ?></span><?php } else { ?><a href="<?php echo pods_ui_var_update(array('limit'.$object->ui['num']=>$ipp,'pg'.$object->ui['num']=>''),false,false); ?>"><?php echo $ipp; ?></a><?php } ?> <?php $ipp = 100; if($object->ui['limit']==$ipp){ ?><span class="page-numbers current"><?php echo $ipp; ?></span><?php } else { ?><a href="<?php echo pods_ui_var_update(array('limit'.$object->ui['num']=>$ipp,'pg'.$object->ui['num']=>''),false,false); ?>"><?php echo $ipp; ?></a><?php } ?> <?php $ipp = 200; if($object->ui['limit']==$ipp){ ?><span class="page-numbers current"><?php echo $ipp; ?></span><?php } else { ?><a href="<?php echo pods_ui_var_update(array('limit'.$object->ui['num']=>$ipp,'pg'.$object->ui['num']=>''),false,false); ?>"><?php echo $ipp; ?></a><?php } ?>&nbsp;&nbsp;|&nbsp;&nbsp;
<?php pods_ui_pagination($object); ?>
        </div>
<?php
            if($object->ui['reorder']!==null&&$object->ui['action']=='reorder')
            {
?>
        <div class="alignleft actions">
            <input type="button" value="Update Order" class="button" onclick="pods_ui_reorder();" />
        </div>
<?php
            }
            elseif(!in_array('add',$object->ui['disable_actions'])||(!in_array('reorder',$object->ui['disable_actions'])&&$object->ui['reorder']!==null))
            {
?>
        <div class="alignleft actions">
<?php
                if(!in_array('add',$object->ui['disable_actions'])) {
?>
            <input type="button" value="Add New <?php echo $object->ui['item']; ?>" class="button" onclick="document.location='<?php echo pods_ui_var_update(array('action'.$object->ui['num']=>'add')); ?>'" />
<?php
                }
                if($object->ui['reorder']!==null&&!in_array('reorder',$object->ui['disable_actions'])) {
?>
            <input type="button" value="Reorder" class="button" onclick="document.location='<?php echo pods_ui_var_update(array('action'.$object->ui['num']=>'reorder')); ?>'" />
<?php
                }
?>
        </div>
<?php
            }
?>
        <br class="clear" />
    </div>
<?php
            if($object->ui['reorder']!==null&&$object->ui['action']=='reorder')
            {
                pods_ui_table_reorder($object);
            }
            else
            {
                pods_ui_table($object);
            }
?>
    <div class="tablenav">
        <div class='tablenav-pages'>
<?php pods_ui_pagination($object); ?>
        </div>
<?php
            if($object->ui['reorder']!==null&&$object->ui['action']=='reorder')
            {
?>
        <div class="alignleft actions">
            <input type="button" value="Update Order" class="button" onclick="pods_ui_reorder();" />
        </div>
<?php
            }
?>
        <br class="clear" />
    </div>
<?php
        }
        else
        {
            if(!in_array('add',$object->ui['disable_actions']))
            {
?>
    <div class="tablenav">
        <div class="alignleft actions">
            <input type="button" value="Add New <?php echo $object->ui['item']; ?>" class="button" onclick="document.location='<?php echo pods_ui_var_update(array('action'.$object->ui['num']=>'add')); ?>'" />
        </div>
        <br class="clear" />
    </div>
<?php
            }
            pods_ui_table(false);
        }
    }
    if(!is_numeric($object->ui['num']))
    {
?>
</div>
<?php
    }
}
function pods_ui_table ($object,$rows=null)
{
    if(!is_object($object)&&!is_array($rows))
    {
?>
<p>No items found</p>
<?php
        return false;
    }
    elseif(is_object($object))
    {
        $i = 0;
        $view_link = $edit_link = $duplicate_link = $delete_link = $rows = $raw_rows = array();
        while($object->fetchRecord())
        {
            $rows[$i] = $raw_rows[$i] = array('id'=>$object->get_field('id'),'name'=>$object->get_field('name'));
            foreach($object->ui['columns'] as $key=>$column)
            {
                if(is_numeric($key))
                {
                    $key = $column;
                    $column = ucwords(str_replace('_',' ',$column));
                }
                $value = $object->get_field($key);
                $raw_rows[$i][$key] = $value;
                if($key=='created'||$key=='modified')
                {
                    $value = '<abbr title="'.date_i18n('Y/m/d g:i:s A',strtotime($value)).'">'.date_i18n('Y/m/d g:i:s A',strtotime($value)).'</abbr>';
                }
                if(is_array($column)&&isset($column['display_helper']))
                {
                    $value = $object->pod_helper($column['display_helper'],$object->get_field($key),$key);
                }
                $rows[$i][$key] = $value;
            }
            $view_link[$i] = $object->get_field('detail_url');
            if($view_link[$i]==get_bloginfo('url').'/')
            {
                $view_link[$i] = false;
            }
            if($object->ui['view_link']!==null)
            {
                $check = $object->pod_helper($object->ui['view_link'],$object->get_field('id'),$view_link[$i]);
                if(!empty($check))
                {
                    $view_link[$i] = $check;
                }
            }
            $check = pods_ui_verify_access($object,$object->ui['edit_where'],'edit');
            if($check===false)
            {
                $edit_link[$i] = false;
            }
            else
            {
                $edit_link[$i] = array('action'.$object->ui['num']=>'edit','id'.$object->ui['num']=>$object->get_field('id'),'updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'','search'.$object->ui['num']=>'');
                if($object->ui['edit_link']!==null)
                {
                    if(is_array($object->ui['edit_link']))
                    {
                        $edit_link[$i] = $object->ui['edit_link'];
                    }
                    else
                    {
                        $link = $object->pod_helper($object->ui['edit_link'],$object->get_field('id'),$edit_link[$i]);
                        $check = @json_decode($link,true);
                        if(is_array($check)&&!empty($check))
                        {
                            $edit_link[$i] = $check;
                        }
                        elseif(!empty($link))
                        {
                            $edit_link[$i] = $link;
                        }
                    }
                }
                if(is_array($edit_link[$i]))
                {
                    $edit_link[$i] = pods_ui_var_update($edit_link[$i]);
                }
            }
            $check = pods_ui_verify_access($object,$object->ui['duplicate_where'],'duplicate');
            if($check===false)
            {
                $duplicate_link[$i] = false;
            }
            else
            {
                $duplicate_link[$i] = array('action'.$object->ui['num']=>'duplicate','id'.$object->ui['num']=>$object->get_field('id'),'updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'','search'.$object->ui['num']=>'');
                if($object->ui['duplicate_link']!==null)
                {
                    if(is_array($object->ui['duplicate_link']))
                    {
                        $duplicate_link[$i] = $object->ui['duplicate_link'];
                    }
                    else
                    {
                        $link = $object->pod_helper($object->ui['duplicate_link'],$object->get_field('id'),$duplicate_link[$i]);
                        $check = @json_decode($link,true);
                        if(is_array($check)&&!empty($check))
                        {
                            $duplicate_link[$i] = $check;
                        }
                        elseif(!empty($link))
                        {
                            $duplicate_link[$i] = $link;
                        }
                    }
                }
                if(is_array($duplicate_link[$i]))
                {
                    $duplicate_link[$i] = pods_ui_var_update($duplicate_link[$i]);
                }
            }
            $check = pods_ui_verify_access($object,$object->ui['delete_where'],'delete');
            if($check===false)
            {
                $delete_link[$i] = false;
            }
            else
            {
                $delete_link[$i] = array('action'.$object->ui['num']=>'delete','id'.$object->ui['num']=>$object->get_field('id'),'updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'','search'.$object->ui['num']=>'');
                if($object->ui['duplicate_link']!==null)
                {
                    if(is_array($object->ui['delete_link']))
                    {
                        $delete_link[$i] = $object->ui['delete_link'];
                    }
                    else
                    {
                        $link = $object->pod_helper($object->ui['delete_link'],$object->get_field('id'),$delete_link[$i]);
                        $check = @json_decode($link,true);
                        if(is_array($check)&&!empty($check))
                        {
                            $delete_link[$i] = $check;
                        }
                        elseif(!empty($link))
                        {
                            $delete_link[$i] = $link;
                        }
                    }
                }
                if(is_array($delete_link[$i]))
                {
                    $delete_link[$i] = pods_ui_var_update($delete_link[$i]);
                }
            }
            $i++;
        }
    }
?>
<div class="clear"></div>
<?php
    if(is_array($rows))
    {
?>
<table class="widefat page fixed" cellspacing="0">
    <thead>
        <tr>
<?php
        $flag=false;
        foreach($object->ui['columns'] as $key=>$column)
        {
            $id = $class = '';
            if(is_numeric($key))
            {
                $key = $column;
                $column = ucwords(str_replace('_',' ',$column));
            }
            if($key=='name')
            {
                $id = 'title';
            }
            elseif($key=='created'||$key=='modified')
            {
                $id = 'date';
            }
            elseif(!$flag)
            {
                $id = 'author'; $flag=true;
            }
            if(0<strlen($id))
            {
                $class = ' column-'.$id; $id = ' id="'.$id.'"';
            }
            $label = $column;
            if(is_array($column)&&isset($column['label']))
            {
                $label = $column['label'];
            }
            $dir = (($object->ui['sortable']===null&&pods_ui_var('sort'.$object->ui['num'])==pods_ui_coltype($key,$object)&&pods_ui_var('sortdir'.$object->ui['num'])=='desc')?'asc':'desc');
            $sort = ($object->ui['sortable']===null?pods_ui_coltype($key,$object):null);
?>
            <th scope="col"<?php echo $id; ?> class="manage-column<?php echo $class; ?>"><?php if($object->ui['sortable']===null&&$sort!==null){ ?><a href="<?php echo pods_ui_var_update(array('sort'.$object->ui['num']=>$sort,'sortdir'.$object->ui['num']=>$dir),false,false); ?>"><?php } echo $label; if($object->ui['sortable']===null){ ?></a><?php } ?></th>
<?php
        }
?>
        </tr>
    </thead>
    <tfoot>
        <tr>
<?php
        $flag=false;
        foreach($object->ui['columns'] as $key=>$column)
        {
            $id = $class = '';
            if(is_numeric($key))
            {
                $key = $column;
                $column = ucwords(str_replace('_',' ',$column));
            }
            if($key=='name')
            {
                $id = 'title';
            }
            elseif($key=='created')
            {
                $id = 'date';
            }
            elseif(!$flag)
            {
                $id = 'author'; $flag=true;
            }
            if(0<strlen($id))
            {
                $class = ' column-'.$id;
            }
            $label = $column;
            if(is_array($column)&&isset($column['label']))
            {
                $label = $column['label'];
            }
?>
            <th scope="col" class="manage-column<?php echo $class; ?>"><?php echo apply_filters('pods_ui_cell_header',$label,$key,$column,$object); ?></th>
<?php
        }
?>
        </tr>
    </tfoot>
    <tbody>
<?php
        foreach($rows as $i=>$row)
        {
?>
        <tr id="item-<?php echo $raw_rows[$i]['id']; ?>" class="iedit">
<?php
            foreach($object->ui['columns'] as $key=>$column)
            {
                $id = $class = '';
                if(is_numeric($key))
                {
                    $key = $column;
                    $column = ucwords(str_replace('_',' ',$column));
                }
                if($key=='name')
                {
                    $default_action = false;
                    $actions = array();
                    $defaults = array('edit','duplicate','delete','view');
                    foreach($defaults as $action)
                    {
                        $link_array = $action.'_link';
                        $link_array = $$link_array;
                        if(!in_array($action,$object->ui['disable_actions'])&&$link_array[$i]!==false)
                        {
                            if($default_action===false&&$action!='delete')
                                $default_action = array('name'=>ucwords($action),'link'=>$link_array[$i]);
                            if($action=='delete')
                                $actions[] = "<span class='delete'><a href='".$link_array[$i]."' title='".ucwords($action)." this item' class='submitdelete' onclick=\"if ( confirm('You are about to delete this item \'".htmlentities($row['name'])."\'\\n \'Cancel\' to stop, \'OK\' to delete.') ) { return true;}return false;\">".ucwords($action)."</a></span>";
                            else
                                $actions[] = "<span class='edit'><a href='".$link_array[$i]."' title='".ucwords($action)." this item'>".ucwords($action)."</a></span>";
                        }
                    }
                    if(!empty($object->ui['custom_actions']))
                    {
                        foreach($object->ui['custom_actions'] as $action=>$function)
                        {
                            if(function_exists($function)&&!in_array($action,$object->ui['disable_actions']))
                            {
                                if($default_action===false)
                                    $default_action = array('name'=>ucwords($action),'link'=>pods_ui_var_update(array('action'.$object->ui['num']=>$action,'id'.$object->ui['num']=>$row['id'])));
                                $actions[] = "<span class='edit'><a href=\"".pods_ui_var_update(array('action'.$object->ui['num']=>$action,'id'.$object->ui['num']=>$row['id']))."\" title=\"".ucwords($action)." this item\">".ucwords($action)."</a>";
                            }
                        }
                    }
?>
            <td class="post-title page-title column-title">
                <strong><?php if(is_array($default_action)){ ?><a class="row-title" href="<?php echo $default_action['link']; ?>" title="<?php echo $default_action['name']; ?> &#8220;<?php echo htmlentities($row['name']); ?>&#8221;"><?php echo apply_filters('pods_ui_title_cell_value',$row['name'],$key,$column,$row,$object); ?></a><?php } else { echo apply_filters('pods_ui_title_cell_value',$row['name'],$key,$column,$row,$object); } ?></strong>
<?php
                    $actions = apply_filters('pods_ui_actions_output_array', $actions, $row, $object);
                    if(!empty($actions))
                    {
?>
                <div class="row-actions"><?php echo implode(' | ', $actions); ?></div>
<?php
                    }
?>
            </td>
<?php
                    continue;
                }
                elseif($key=='created'||$key=='modified')
                {
                    $id = 'date';
                }
                elseif(!$flag)
                {
                    $id = 'author'; $flag=true;
                }
                if(0<strlen($id))
                {
                    $class = ' class="'.$id.' column-'.$id.'"';
                }
                $row_old = false;
                if(is_array($row[$key]))
                {
                    if(!empty($row[$key]) && isset($row[$key][0]))
                    {
                        $row_old = $row[$key];
                        $row[$key] = array();
                        foreach ($row_old as $item) {
                            $row[$key][] = $item[pods_ui_coltype($key,$object,true)];
                        }
                        sort($row[$key]);
                        $row[$key] = implode(', ', $row[$key]);
                    }
                    elseif (isset($row[$key][pods_ui_coltype($key,$object,true)]))
                        $row[$key] = $row[$key][pods_ui_coltype($key,$object,true)];
                    else
                        $row[$key] = '';
                }
                if(is_array($column))
                {
                    if(isset($column['coltype'])&&($column['coltype']=='boolean'||$column['coltype']=='bool'))
                    {
                        if(is_numeric($row[$key])&&$row[$key]==1)
                        {
                            $row[$key] = 'Yes';
                        }
                        elseif(is_numeric($row[$key])&&$row[$key]==0)
                        {
                            $row[$key] = 'No';
                        }
                        elseif(0<strlen($row[$key])||!empty($row[$key]))
                        {
                            $row[$key] = 'Yes';
                        }
                        else
                        {
                            $row[$key] = 'No';
                        }
                    }
                }
?>
            <td<?php echo $class; ?>><?php echo apply_filters('pods_ui_cell_value',$row[$key],$key,$row,$object); ?></td>
<?php
            }
?>
        </tr>
<?php
        }
?>
    </tbody>
</table>
<script type="text/javascript">
jQuery('table.widefat tbody tr:even').addClass('alternate');
</script>
<?php
    }
    else
    {
?>
<p>No items found</p>
<?php
    }
}
/*
* Backwards compatibility for custom solutions
* @deprecated Use pods_ui_table_reorder
*/
function pods_ui_reorder ($object)
{
    pods_ui_manage($object);
}
function pods_ui_table_reorder ($object)
{
    if (!wp_script_is('jquery-ui-core', 'queue') && !wp_script_is('jquery-ui-core', 'to_do') && !wp_script_is('jquery-ui-core', 'done'))
        wp_print_scripts('jquery-ui-core');
    if (!wp_script_is('jquery-ui-sortable', 'queue') && !wp_script_is('jquery-ui-sortable', 'to_do') && !wp_script_is('jquery-ui-sortable', 'done'))
        wp_print_scripts('jquery-ui-sortable');
?>
<script type="text/javascript">
function pods_ui_reorder () {
    var order = "";
    jQuery("table#pods_ui_reorder tbody tr").each(function() {
        order += jQuery(this).attr("id").substr(5) + ",";
    });
    order = order.slice(0, -1);

    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ui/ajax/api.php",
        data: "action=reorder_pod_item&datatype=<?php echo $object->datatype; ?>&field=<?php echo $object->ui['reorder']; ?>&order="+order,
        success: function(msg) {
            if ("<e>" == msg.substr(0, 3)) {
                alert(msg);
            }
            else {
                alert("Success!");
            }
        }
    });
}
</script>
<style type="text/css">
.dragme {
    background: url(<?php echo PODS_URL; ?>/ui/images/move.png) no-repeat;
    background-position:8px 5px;
    cursor:pointer;
}
</style>
<?php
    if(!is_object($object))
    {
?>
<p>No items found</p>
<?php
        return false;
    }
    elseif(is_object($object))
    {
        $i = 0;
        $view_link = $edit_link = $duplicate_link = $delete_link = $rows = $raw_rows = array();
        while($object->fetchRecord())
        {
            $rows[$i] = $raw_rows[$i] = array('id'=>$object->get_field('id'),'name'=>$object->get_field('name'));
            foreach($object->ui['reorder_columns'] as $key=>$column)
            {
                if(is_numeric($key))
                {
                    $key = $column;
                    $column = ucwords(str_replace('_',' ',$column));
                }
                $value = $object->get_field($key);
                $raw_rows[$i][$key] = $value;
                if($key=='created'||$key=='modified')
                {
                    $value = '<abbr title="'.date_i18n('Y/m/d g:i:s A',strtotime($value)).'">'.date_i18n('Y/m/d g:i:s A',strtotime($value)).'</abbr>';
                }
                if(is_array($column)&&isset($column['display_helper']))
                {
                    $value = $object->pod_helper($column['display_helper'],$object->get_field($key),$key);
                }
                $rows[$i][$key] = $value;
            }
            $i++;
        }
    }
?>
<div class="clear"></div>
<?php
    if(is_array($rows))
    {
?>
<table class="widefat page fixed" id="pods_ui_reorder" cellspacing="0">
    <thead>
        <tr>
<?php
        $flag=false;
        foreach($object->ui['reorder_columns'] as $key=>$column)
        {
            $id = $class = '';
            if(is_numeric($key))
            {
                $key = $column;
                $column = ucwords(str_replace('_',' ',$column));
            }
            if($key=='name')
            {
                $id = 'title';
            }
            elseif($key=='created'||$key=='modified')
            {
                $id = 'date';
            }
            elseif(!$flag)
            {
                $id = 'author'; $flag=true;
            }
            if(0<strlen($id))
            {
                $class = ' column-'.$id; $id = ' id="'.$id.'"';
            }
            $label = $column;
            if(is_array($column)&&isset($column['label']))
            {
                $label = $column['label'];
            }
            $dir = (($object->ui['sortable']===null&&pods_ui_var('sort'.$object->ui['num'])==pods_ui_coltype($key,$object)&&pods_ui_var('sortdir'.$object->ui['num'])=='desc')?'asc':'desc');
            $sort = ($object->ui['sortable']===null?pods_ui_coltype($key,$object):null);
?>
            <th scope="col"<?php echo $id; ?> class="manage-column<?php echo $class; ?>"><?php if($object->ui['sortable']===null&&$sort!==null){ ?><a href="<?php echo pods_ui_var_update(array('sort'.$object->ui['num']=>$sort,'sortdir'.$object->ui['num']=>$dir),false,false); ?>"><?php } echo $label; if($object->ui['sortable']===null){ ?></a><?php } ?></th>
<?php
        }
?>
        </tr>
    </thead>
    <tfoot>
        <tr>
<?php
        $flag=false;
        foreach($object->ui['reorder_columns'] as $key=>$column)
        {
            $id = $class = '';
            if(is_numeric($key))
            {
                $key = $column;
                $column = ucwords(str_replace('_',' ',$column));
            }
            if($key=='name')
            {
                $id = 'title';
            }
            elseif($key=='created')
            {
                $id = 'date';
            }
            elseif(!$flag)
            {
                $id = 'author'; $flag=true;
            }
            if(0<strlen($id))
            {
                $class = ' column-'.$id;
            }
            $label = $column;
            if(is_array($column)&&isset($column['label']))
            {
                $label = $column['label'];
            }
?>
            <th scope="col" class="manage-column<?php echo $class; ?>"><?php echo apply_filters('pods_ui_cell_header',$label,$key,$column,$object); ?></th>
<?php
        }
?>
        </tr>
    </tfoot>
    <tbody class="sortable">
<?php
        foreach($rows as $i=>$row)
        {
?>
        <tr id="item-<?php echo $raw_rows[$i]['id']; ?>" class="iedit">
<?php
            foreach($object->ui['reorder_columns'] as $key=>$column)
            {
                $id = $class = '';
                if(is_numeric($key))
                {
                    $key = $column;
                    $column = ucwords(str_replace('_',' ',$column));
                }
                if($key=='name')
                {
?>
            <td class="post-title page-title column-title dragme">
                <strong style="margin-left:30px;"><?php echo apply_filters('pods_ui_title_cell_value',$row['name'],$key,$row,$object); ?></strong>
            </td>
<?php
                    continue;
                }
                elseif($key=='created'||$key=='modified')
                {
                    $id = 'date';
                }
                elseif(!$flag)
                {
                    $id = 'author'; $flag=true;
                }
                if(0<strlen($id))
                {
                    $class = ' class="'.$id.' column-'.$id.'"';
                }
                $row_old = false;
                if(is_array($row[$key]))
                {
                    if(!empty($row[$key]) && isset($row[$key][0]))
                    {
                        $row_old = $row[$key];
                        $row[$key] = array();
                        foreach ($row_old as $item) {
                            $row[$key][] = $item[pods_ui_coltype($key,$object,true)];
                        }
                        sort($row[$key]);
                        $row[$key] = implode(', ', $row[$key]);
                    }
                    elseif (isset($row[$key][pods_ui_coltype($key,$object,true)]))
                        $row[$key] = $row[$key][pods_ui_coltype($key,$object,true)];
                    else
                        $row[$key] = '';
                }
                if(is_array($column))
                {
                    if(isset($column['coltype'])&&($column['coltype']=='boolean'||$column['coltype']=='bool'))
                    {
                        if(is_numeric($row[$key])&&$row[$key]==1)
                        {
                            $row[$key] = 'Yes';
                        }
                        elseif(is_numeric($row[$key])&&$row[$key]==0)
                        {
                            $row[$key] = 'No';
                        }
                        elseif(0<strlen($row[$key])||!empty($row[$key]))
                        {
                            $row[$key] = 'Yes';
                        }
                        else
                        {
                            $row[$key] = 'No';
                        }
                    }
                }
?>
            <td<?php echo $class; ?>><?php echo apply_filters('pods_ui_cell_value',$row[$key],$key,$row,$object); ?></td>
<?php
            }
?>
        </tr>
<?php
        }
?>
    </tbody>
</table>
<script type="text/javascript">
jQuery('table.widefat tbody tr:even').addClass('alternate');
jQuery(document).ready(function(){
    jQuery(".sortable").sortable({axis: "y", handle: ".dragme"});
    jQuery(".sortable").bind('sortupdate', function(event, ui){
        jQuery('table.widefat tbody tr').removeClass('alternate');
        jQuery('table.widefat tbody tr:even').addClass('alternate');
    });
});
</script>
<?php
    }
    else
    {
?>
<p>No items found</p>
<?php
    }
}
function pods_ui_pagination ($object)
{
    $page = $object->page;
    $rows_per_page = $object->rpp;
    $total_rows = $object->getTotalRows();
    $total_pages = ceil($total_rows / $rows_per_page);
    $request_uri = pods_ui_var_update(array('pg'.$object->ui['num']=>''),false,false).'&';
    $begin = ($rows_per_page*$page)-($rows_per_page-1);
    $end = ($total_pages==$page?$total_rows:($rows_per_page*$page));
?>
            <span class="displaying-num">Displaying <?php if($total_rows<1){ echo 0; } else { echo $begin; ?>&#8211;<?php echo $end; } ?> of <?php echo $total_rows; ?></span>
<?php
    if (1 < $page)
    {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo $page-1; ?>" class="prev page-numbers">&laquo;</a>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=1" class="page-numbers">1</a>
<?php
    }
    if (1 < ($page - 100))
    {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo ($page - 100); ?>" class="page-numbers"><?php echo ($page - 100); ?></a>
<?php
    }
    if (1 < ($page - 10))
    {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo ($page - 10); ?>" class="page-numbers"><?php echo ($page - 10); ?></a>
<?php
    }
    for ($i = 2; $i > 0; $i--)
    {
        if (1 < ($page - $i))
        {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo ($page - $i); ?>" class="page-numbers"><?php echo ($page - $i); ?></a>
<?php
       }
}
?>
            <span class="page-numbers current"><?php echo $page; ?></span>
<?php
    for ($i = 1; $i < 3; $i++)
    {
        if ($total_pages > ($page + $i))
        {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo ($page + $i); ?>" class="page-numbers"><?php echo ($page + $i); ?></a>
<?php
        }
    }
    if ($total_pages > ($page + 10))
    {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo ($page + 10); ?>" class="page-numbers"><?php echo ($page + 10); ?></a>
<?php
    }
    if ($total_pages > ($page + 100))
    {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo ($page + 100); ?>" class="page-numbers"><?php echo ($page + 100); ?></a>
<?php
    }
    if ($page < $total_pages)
    {
?>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo $total_pages; ?>" class="page-numbers"><?php echo $total_pages; ?></a>
            <a href="<?php echo $request_uri; ?>pg<?php echo $object->ui['num']; ?>=<?php echo $page+1; ?>" class="next page-numbers">&raquo;</a>
<?php
    }
}
function pods_ui_verify_access  ($object,$access,$what)
{
    if(is_array($access))
    {
        foreach($access as $field=>$match)
        {
            if(is_array($match))
            {
                $okay = false;
                foreach($match as $the_field=>$the_match)
                {
                    if($object->get_field($the_field)==$the_match)
                    {
                        $okay = true;
                    }
                }
                if($okay===false)
                {
                    return false;
                }
            }
            elseif($object->get_field($field)!=$match)
            {
                return false;
            }
        }
    }
    return true;
}
function pods_ui_form ($object,$add=0,$duplicate=0)
{
    if(!is_object($object))
    {
        echo '<strong>Error:</strong> Pods UI needs an object to run from, see the User Guide for more information.';
        return false;
    }
    if($duplicate==1&&$object->id>0)
    {
        $add = 0;
        $fields = ($object->ui['duplicate_fields']!=null?$object->ui['duplicate_fields']:$object->ui['edit_fields']);
        $access = $object->ui['duplicate_where'];
        $what = 'Add New ';
        if($object->ui['label_duplicate']!==null)
        {
            $object->ui['label'] = $object->ui['label_duplicate'];
        }
        if($object->ui['label']===null)
        {
            $object->ui['label'] = 'Add New '.$object->ui['item'];
        }
    }
    elseif($add==0&&$object->id>0)
    {
        $fields = $object->ui['edit_fields'];
        $access = $object->ui['edit_where'];
        $what = 'Edit ';
        if($object->ui['label_edit']!==null)
        {
            $object->ui['label'] = $object->ui['label_edit'];
        }
        if($object->ui['label']===null)
        {
            $object->ui['label'] = 'Save Changes';
        }
    }
    else
    {
        $add = 1;
        $duplicate = 0;
        $fields = $object->ui['add_fields'];
        $access = null;
        $what = 'Add New ';
        if($object->ui['label_add']!==null)
        {
            $object->ui['label'] = $object->ui['label_add'];
        }
        if($object->ui['label']===null)
        {
            $object->ui['label'] = 'Add New '.$object->ui['item'];
        }
    }
?>
<h2><?php echo $what.$object->ui['item']; ?> <small>(<a href="<?php echo pods_ui_var_update(($object->ui['manage_content']!==null?array('page'=>'pods-manage-'.$object->datatype):array('action'.$object->ui['num']=>'manage'))); ?>">&laquo; Back to Manage</a>)</small></h2>
<?php
    $check = pods_ui_verify_access($object,$access,strtolower($what));
    if($check===false)
    {
        pods_ui_message('<strong>Error:</strong> You do not have the permissions required to '.strtolower($what).' this '.$object->ui['item'].'.',2);
        return;
    }
    $viewit = '';
    if($add==0&&$object->get_field('detail_url')!=get_bloginfo('url').'/')
    {
        $viewit = '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$object->get_field('detail_url').'">View '.$object->ui['item'].' &raquo;</a>';
    }
    if(isset($_GET['updated'.$object->ui['num']]))
    {
        pods_ui_message($object->ui['item'].' updated.'.$viewit);
    }
    elseif(isset($_GET['duplicated'.$object->ui['num']]))
    {
        $redirect_array = array('action'.$object->ui['num']=>'add','id'.$object->ui['num']=>'','updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'');
        $redirect_to = pods_ui_var_update($redirect_array,true);
        $redirect_array = array('action'.$object->ui['num']=>'duplicate','id'.$object->ui['num']=>$object->id,'updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'');
        $redirect_to_duplicate = pods_ui_var_update($redirect_array,true);
        pods_ui_message($object->ui['item'].' duplicated successfully. <a href="'.$redirect_to.'">Add another &raquo;</a>'.(!in_array('duplicate',$object->ui['disable_actions'])?'&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$redirect_to_duplicate.'">Add another based on this one &raquo;</a>':'').$viewit);
    }
    elseif(isset($_GET['added'.$object->ui['num']]))
    {
        $redirect_array = array('action'.$object->ui['num']=>'add','id'.$object->ui['num']=>'','updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'');
        $redirect_to = pods_ui_var_update($redirect_array,true);
        $redirect_array = array('action'.$object->ui['num']=>'duplicate','id'.$object->ui['num']=>$object->id,'updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'');
        $redirect_to_duplicate = pods_ui_var_update($redirect_array,true);
        pods_ui_message($object->ui['item'].' added successfully. <a href="'.$redirect_to.'">Add another &raquo;</a>'.(!in_array('duplicate',$object->ui['disable_actions'])?'&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$redirect_to_duplicate.'">Add another based on this one &raquo;</a>':'').$viewit);
    }
    elseif($duplicate==1)
    {
        pods_ui_message('<strong>About Duplicating:</strong> The form below is filled with information based off an existing '.$object->ui['item'].'. By saving this information, you will create a new '.$object->ui['item'].' and nothing will be overwritten.');
    }
    ob_start();
    $object->publicForm($fields,$object->ui['label']);
    $form = ob_get_clean();
    $actionwhat = 'updated';
    if($duplicate==1)
        $actionwhat = 'duplicated';
    elseif($add==1)
        $actionwhat = 'added';
    $redirect_array = array('action'.$object->ui['num']=>'edit','id'.$object->ui['num']=>'','updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'',$actionwhat.$object->ui['num']=>1);
    $redirect_to = pods_ui_var_update($redirect_array,true).'&id="+msg+"';
    if($object->ui['action_after_save']!=null)
    {
        $redirect_array = array('action'.$object->ui['num']=>$object->ui['action_after_save'],'id'.$object->ui['num']=>'','updated'.$object->ui['num']=>'','duplicated'.$object->ui['num']=>'','added'.$object->ui['num']=>'',$actionwhat.$object->ui['num']=>1);
        $redirect_to = pods_ui_var_update($redirect_array,true).($object->ui['action_after_save']=='edit'?'&id="+msg+"':'');
    }
    $form = str_replace('window.location = "'.$_SERVER['REQUEST_URI'].'";','window.location = "'.$redirect_to.'";',$form);
    if(!in_array('delete',$object->ui['disable_actions'])&&$object->id!=null&&$duplicate==0)
        $form = str_replace('<input type="button" class="button btn_save" value="'.$object->ui['label'].'" onclick="saveForm(1)" />','<input type="button" class="button btn_save" value="'.$object->ui['label'].'" onclick="saveForm(1)" />&nbsp;&nbsp;&nbsp;<a style="color:#red;font-weight:normal;text-decoration:underline;font-style:italic;" title="Delete this item" href="'.pods_ui_var_update(array('action'.$object->ui['num']=>'delete','id'.$object->ui['num']=>$object->get_field('id'))).'" onclick="if ( confirm(\'You are about to delete this item \\\''.htmlentities($object->get_field('name')).'\\\'\n \\\'Cancel\\\' to stop, \\\'OK\\\' to delete.\') ) { return true;}return false;">Delete this item</a>',$form);
    if($duplicate==1) {
        $form = str_replace('<input type="hidden" class="form num pod_id" value="'.$object->get_pod_id().'" />','<input type="hidden" class="form num pod_id" value="0" />',$form);
        $form = str_replace('<input type="hidden" class="form num tbl_row_id" value="'.$object->get_field('id').'" />','<input type="hidden" class="form num tbl_row_id" value="0" />',$form);
    }
    echo $form;
}
function pods_ui_delete ($object,$msg=true)
{
    $pod_id = $object;
    $item = 'Item';
    if(is_object($object))
    {
        $pod_id = $object->get_pod_id();
        $item = $object->ui['item'];
    }
    if(!is_bool($msg))
    {
        $item = $msg;
    }
    $api = new PodAPI();
    $api->drop_pod_item(array('pod_id'=>$pod_id));
    if($msg!==false)
    {
        pods_ui_message($item.' <strong>deleted</strong>.');
    }
}
function pods_ui_validate_package ($package) // DEPRECATED
{
    $api = new PodAPI();
    return $api->validate_package($package);
}
function pods_ui_import_package ($package) // DEPRECATED
{
    $api = new PodAPI();
    $validate = $api->validate_package($package);
    if(!is_bool($validate))
    {
        pods_ui_message('Package failed validation: '.$validate,2);
        return false;
    }
    return $api->import_package($package);
}
function pods_ui_message ($msg,$error=false)
{
?>
    <div id="message" class="<?php echo ($error?'error':'updated'); ?> fade"><p><?php echo $msg; ?></p></div>
<?php
}
function pods_ui_var ($var,$method='get',$default=false,$strict=true)
{
    if(is_array($var))
    {
        foreach($var as $key)
        {
            if(false!==pods_ui_var($key,$method,$default))
            {
                return true;
            }
        }
        return false;
    }
    $ret = pods_var($var, $method, $default, null, $strict);
    return $ret;
}
function pods_ui_var_update ($arr=false,$url=false,$strict=true)
{
    if(!isset($_GET))
    {
        $get = array();
    }
    else
    {
        $get = $_GET;
    }
    $query = array();
    if(isset($get['page']))
    {
        $query['page'] = $get['page'];
    }
    if(false===$strict)
    {
        $query = $get;
    }
    if(isset($get['pod']))
    {
        $query['pod'] = $get['pod'];
    }
    if(is_array($arr))
    {
        foreach($arr as $key=>$val)
        {
            if(0<strlen($val))
            {
                $query[$key] = $val;
            }
            elseif(false===$strict&&isset($get[$key]))
            {
                unset($query[$key]);
            }
        }
    }
    if($url===false)
    {
        $url = '';
    }
    else
    {
        $url = explode('?',$_SERVER['REQUEST_URI']);
        $url = explode('#',$url[0]);
        $url = $url[0];
    }
    return $url.'?'.http_build_query($query);
}
function pods_ui_var_get ($arr=false,$format=false)
{
    if(!isset($_GET))
    {
        $get = array();
    }
    else
    {
        $get = $_GET;
    }
    if(is_array($arr))
    {
        foreach($arr as $key=>$val)
        {
            if(0<strlen($val))
            {
                $get[$key] = $val;
            }
            else
            {
                unset($get[$key]);
            }
        }
    }
    if($format!==false&&$format=='input')
    {
        foreach($get as $k=>$v)
        {
?>
<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
<?php
        }
    }
}
function pods_ui_var_set ($var,$value=null,$method='get')
{
    if($method=='get')
    {
        if($value===null&&isset($_GET[$var]))
        {
            unset($_GET[$var]);
            return true;
        }
        $_GET[$var] = $value;
    }
    elseif($method=='post')
    {
        if($value===null&&isset($_POST[$var]))
        {
            unset($_POST[$var]);
            return true;
        }
        $_POST[$var] = $value;
    }
    elseif($method=='session')
    {
        if($value===null&&isset($_SESSION[$var]))
        {
            unset($_SESSION[$var]);
            return true;
        }
        $_SESSION[$var] = $value;
    }
    elseif($method=='user')
    {
        if(!is_user_logged_in())
        {
            return false;
        }
        global $user_ID;
        get_currentuserinfo();
        delete_user_meta($user_ID,'pods_ui_'.$var);
        if($value!==null)
        {
            update_user_meta($user_ID,'pods_ui_'.$var,$value);
        }
    }
    return true;
}
function pods_ui_strtoarray ($var)
{
    if(is_array($var))
    {
        return $var;
    }
    else
    {
        return explode(',',$var);
    }
}
function pods_ui_fields ($datatype_id)
{
    $fields = array();
    $result = pod_query("SELECT * FROM @wp_pod_fields WHERE datatype = $datatype_id ORDER BY weight", 'Cannot get datatype fields');
    while($row = mysql_fetch_assoc($result))
    {
        $fields[$row['name']] = $row;
    }
    return $fields;
}
function pods_ui_coltype ($column,$object,$t=false)
{
    $column = explode('.',$column);
    if(isset($column[1]))
    {
        $t = true;
    }
    $column = $column[0];
    $fields = $object->ui['fields'];
    if($fields===false)
    {
        $fields = pods_ui_fields($object->datatype_id);
    }
    if(empty($fields))
    {
        return ($t?'':'t.').$column;
    }
    elseif(isset($fields[$column]))
    {
        if($fields[$column]['coltype']=='pick')
        {
            if(strpos($fields[$column]['pickval'],'wp_')!==false)
            {
                if($fields[$column]['pickval']=='wp_user')
                {
                    return ($t?'':$column.'.').'display_name';
                }
                else
                {
                    return ($t?'':$column.'.').($t?'post_title':'post_name');
                }
            }
            else
            {
                return ($t?'':$column.'.').'name';
            }
        }
        elseif($fields[$column]['coltype']=='file')
        {
            return ($t?'':$column.'.').($t?'guid':'post_name');
        }
        else
        {
            return ($t?'':'t.').$column;
        }
    }
    elseif($column=='created'||$column=='modified')
    {
        return ($t?'':'p.').$column;
    }
}
}