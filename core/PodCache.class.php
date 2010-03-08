<?php
/*
==================================================
PodCache.class.php

http://podscms.org/codex/
==================================================
*/
class PodCache
{
    var $types;
    var $fields;
    var $helpers;
    var $pod_pages;
    var $templates;
    var $cache_enabled = true;
    var $form_count = 0;
    var $results;

    public static function Instance()
    {
        static $instance = null;
        if (null == $instance)
        {
            $instance = new PodCache();
        }
        return $instance;
    }

    private function __construct()
    {
        // Silence is golden.
    }

    function get_type($param)
    {
        if (isset($this->types[$param]) && $this->cache_enabled)
        {
            // If $param is numeric, $this->types[$param] is the type name
            $param = is_numeric($param) ? $this->types[$param] : $param;
            return $this->types[$param];
        }
        else
        {
            $where = is_numeric($param) ? "id = $param" : "name = '$param'";
            $result = pod_query("SELECT * FROM @wp_pod_types WHERE $where LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $this->types[$row['name']] = $row;
            $this->types[$row['id']] = $row['name'];
            return $row;
        }
    }

    function get_field($param)
    {
        if (isset($this->fields[$param]) && $this->cache_enabled)
        {
            // If $param is numeric, $this->fields[$param] is the field name
            $param = is_numeric($param) ? $this->fields[$param] : $param;
            return $this->fields[$param];
        }
        else
        {
            $where = is_numeric($param) ? "id = $param" : "name = '$param'";
            $result = pod_query("SELECT * FROM @wp_pod_fields WHERE $where LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $this->fields[$row['name']] = $row;
            $this->fields[$row['id']] = $row['name'];
            return $row;
        }
    }

    function get_helper($param)
    {
        if (isset($this->helpers[$param]) && $this->cache_enabled)
        {
            // If $param is numeric, $this->helpers[$param] is the helper name
            $param = is_numeric($param) ? $this->helpers[$param] : $param;
            return $this->helpers[$param];
        }
        else
        {
            $where = is_numeric($param) ? "id = $param" : "name = '$param'";
            $result = pod_query("SELECT * FROM @wp_pod_helpers WHERE $where LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $this->helpers[$row['name']] = $row;
            $this->helpers[$row['id']] = $row['name'];
            return $row;
        }
    }

    function get_pod_page($param)
    {
        if (isset($this->pod_pages[$param]) && $this->cache_enabled)
        {
            // If $param is numeric, $this->pod_pages[$param] is the pod page uri
            $param = is_numeric($param) ? $this->types[$param] : $param;
            return $this->types[$param];
        }
        else
        {
            $where = is_numeric($param) ? "id = $param" : "uri = '$param'";
            $result = pod_query("SELECT * FROM @wp_pod_pages WHERE $where LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $this->pod_pages[$row['uri']] = $row;
            $this->pod_pages[$row['id']] = $row['uri'];
            return $row;
        }
    }

    function get_template($param)
    {
        if (isset($this->templates[$param]) && $this->cache_enabled)
        {
            // If $param is numeric, $this->templates[$param] is the template name
            $param = is_numeric($param) ? $this->templates[$param] : $param;
            return $this->templates[$param];
        }
        else
        {
            $where = is_numeric($param) ? "id = $param" : "name = '$param'";
            $result = pod_query("SELECT * FROM @wp_pod_templates WHERE $where LIMIT 1");
            $row = mysql_fetch_assoc($result);
            $this->templates[$row['name']] = $row;
            $this->templates[$row['id']] = $row['name'];
            return $row;
        }
    }
}
