<?php
class Pods
{
    private $api;
    private $data;
    private $ui;
    private $deprecated;
    public $display_errors = false;

    public $table;
    public $field_id = 'id';
    public $field_index = 'name';
    public $pod_data;
    public $pod;
    public $pod_id;
    public $fields;
    public $detail_page;
    
    public $id;

    public $limit = 15;
    public $page_var = 'pg';
    public $page = 1;
    public $pagination = true;
    public $search = true;
    public $search_var = 'search';
    public $search_mode = 'int'; // int | text | text_like

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
                    $this->field_id = 'id';
                    $this->field_name = 'name';
                    break;
                case 'post_type':
                    $this->table = '@wp_posts';
                    $this->field_id = 'ID';
                    $this->field_name = 'post_title';
                    break;
                case 'taxonomy':
                    $this->table = '@wp_taxonomy';
                    $this->field_id = 'term_id';
                    $this->field_name = 'name';
                    break;
                case 'user':
                    $this->table = '@wp_users';
                    $this->field_id = 'ID';
                    $this->field_name = 'display_name';
                    break;
                case 'comment':
                    $this->table = '@wp_comments';
                    $this->field_id = 'comment_ID';
                    $this->field_name = 'comment_date';
                    break;
                case 'table':
                    $this->field_id = 'id';
                    $this->field_name = 'name';
                    break;
            }

            if (null !== $id) {
                if (is_array($id) || is_object($id))
                    $this->find($id);
                else {
                    $this->fetch($id);
                    if (!empty($this->data))
                        $this->id = $this->field($this->field_id);
                }
            }
        }
    }

    /**
     * Return a field's value(s)
     *
     * @param array $params An associative array of parameters (OR the Field name)
     * @param string $orderby (optional) The orderby string, for PICK fields
     * @since 2.0.0
     */
    public function field ($params, $orderby = null) {
        // PodsData
    }

    /**
     * Search and filter records
     *
     * @param array $params An associative array of parameters
     * @since 2.0.0
     */
    public function find ($params, $limit = 15, $where = null, $sql = null) {
        // PodsData
    }

    /**
     * Fetch a row of results from the DB
     *
     * @since 2.0.0
     */
    public function fetch ($id = null) {
        // PodsData
    }

    /**
     * (Re)set the MySQL result pointer
     *
     * @since 2.0.0
     */
    public function reset ($row = 0) {
        // PodsData
    }

    /**
     * Fetch the total row count returned
     *
     * @return int Number of rows returned by find()
     * @since 2.0.0
     */
    public function total () {
        // PodsData
    }

    /**
     * Fetch the total row count total
     *
     * @return int Number of rows found by find()
     * @since 2.0.0
     */
    public function total_found () {
        // PodsData
    }

    /**
     * Fetch the zebra switch
     *
     * @return bool Zebra state
     * @since 1.12
     */
    public function zebra () {
        // PodsData
    }

    /**
     * Display the pagination controls
     *
     * @since 2.0.0
     */
    public function pagination ($params = null) {
        // PodsUI
    }

    /**
     * Display the list filters
     *
     * @since 2.0.0
     */
    public function filters ($params = null) {
        // PodsUI
    }

    /**
     * Run a helper within a Pod Page or WP Template
     *
     * $params['helper'] string Helper name
     * $params['value'] string Value to run Helper on
     * $params['name'] string Field name
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

        $this->do_hook('pre_pod_helper', $params);
        $this->do_hook("pre_pod_helper_{$params->helper}", $params);

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

        $this->do_hook('post_pod_helper', $params);
        $this->do_hook("post_pod_helper_{$params->helper}", $params);

        return $this->do_hook('helper', ob_get_clean(), $params);
    }

    /**
     * Display the page template
     *
     * @since 2.0.0
     */
    public function template ($template, $code = null) {
        ob_start();

        $this->do_hook('pre_template', $template, $code);
        $this->do_hook("pre_template_{$template}", $template, $code);

        if (empty($code)) {
            $template = $this->api->load_template(array('name' => $template));
            if (!empty($template) && !empty($template['code']))
                $code = $template['code'];
            elseif (function_exists("{$template}"))
                $code = $template($this);
        }

        $code = $this->do_hook('template', $code, $template);
        $code = $this->do_hook("template_{$template}", $code, $template);

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

        $this->do_hook('post_template', $template, $code);
        $this->do_hook("post_template_{$template}", $template, $code);

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
        return $this->do_hook('do_template', $out, $code);
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

        $value = $this->do_hook('do_magic_tags', $value, $field_name, $helper_name, $before, $after);
        if (null !== $value && false !== $value)
            return $before . $value . $after;
        return;

    }

    /**
     * Handle filters / actions for the class
     *
     * @since 2.0.0
     */
    private function do_hook () {
        $args = func_get_args();
        if (empty($args))
            return false;
        $name = array_shift($args);
        return pods_do_hook('pods', $name, $args, $this);
    }

    /**
     * Handle methods that have been deprecated
     *
     * @since 2.0.0
     */
    public function __call ($name, $args) {
        $name = (string) $name;
        if (!isset($this->deprecated)) {
            require_once(PODS_DIR . 'deprecated/classes/Pods.php');
            $this->deprecated = new Pods_Deprecated($this);
            if (method_exists($this->deprecated, $name)) {
                $arg_count = count($args);
                if (0 == $arg_count)
                    $this->deprecated->{$name}();
                elseif (1 == $arg_count)
                    $this->deprecated->{$name}($args[0]);
                elseif (2 == $arg_count)
                    $this->deprecated->{$name}($args[0], $args[1]);
                elseif (3 == $arg_count)
                    $this->deprecated->{$name}($args[0], $args[1], $args[2]);
                else
                    $this->deprecated->{$name}($args[0], $args[1], $args[2], $args[3]);
            }
            else
                pods_deprecated("Pods::{$name}", '2.0.0');
        }
    }
}