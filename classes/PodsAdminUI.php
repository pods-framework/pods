<?php
class PodsAdminUI
{
    private $api;

    /**
     * Setup and Handle Admin UI
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct () {
        add_action('admin_menu', array($this, 'admin_menu'), 99);
        add_action('admin_enqueue_scripts', array($this, 'admin_head'));
        add_action('wp_ajax_pods_admin', array($this, 'admin_ajax'));
    }

    public function admin_head () {
        $page = $_GET['page'];
        if ('pods' == $page || (false !== strpos($page, 'pods-') && 0 === strpos($page, 'pods-'))) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');

            wp_register_script('pods-floatmenu', PODS_URL . 'ui/js/floatmenu.js', array(), PODS_VERSION);
            wp_enqueue_script('pods-floatmenu');
            
            wp_register_style('pods-form', PODS_URL . 'ui/css/pods-form.css', array(), PODS_VERSION);
            wp_enqueue_style('pods-form');
        }
    }

    public function admin_menu () {
        $submenu = array();
        $this->api = pods_api();
        $results = $this->api->load_pods(array(//'options' => array('disable_manage' => 0),
                                               'orderby' => '`weight`, `name`'));
        $can_manage = pods_access('manage_content');
        if (false !== $results) {
            foreach ((array) $results as $item) {
                if (!pods_access('pod_'.$item['name']) && !$can_manage)
                    continue;
                $item['options']['label'] = (!empty($item['options']['label'])) ? $item['options']['label'] : ucwords(str_replace('_', ' ', $item['name']));
                $item['options']['label'] = apply_filters('pods_admin_menu_label', $item['options']['label'], $item);
                if (1 == $item['options']['is_toplevel']) {
                    add_object_page($item['options']['label'], $item['options']['label'], 'read', "pods-manage-{$item['name']}");
                    add_submenu_page("pods-manage-{$item['name']}", 'Edit', 'Edit', 'read', "pods-manage-{$item['name']}", array($this, 'admin_content'));
                    add_submenu_page("pods-manage-{$item['name']}", 'Add New', 'Add New', 'read', "pods-add-new-{$item['name']}", array($this, 'admin_content'));
                }
                else
                    $submenu[] = $item;
            }
            if (!empty($submenu)) {
                $parent = false;
                foreach ($submenu as $item) {
                    $page = "pods-manage-{$item['name']}";
                    if (false === $parent) {
                        $parent = $page;
                        add_object_page('Pods', 'Pods', 'read', $parent, null, PODS_URL.'/ui/images/icon16.png');
                    }
                    add_submenu_page($parent, "Manage {$item['options']['label']}", "Manage {$item['options']['label']}", 'read', $page, array($this, 'admin_content'));
                }
            }
        }
        $parent = false;
        $admin_menus = array('pods' => array('label' => 'Setup',
                                             'function' => array($this, 'admin_setup'),
                                             'access' => 'manage_pods'),
                            'pods-advanced' => array('label' => 'Advanced',
                                                     'function' => array($this, 'admin_advanced'),
                                                     'access' => array('manage_templates',
                                                                       'manage_pod_pages',
                                                                       'manage_helpers',
                                                                       'manage_roles')),
                            'pods-settings' => array('label' => 'Settings',
                                                    'function' => array($this, 'admin_settings'),
                                                    'access' => 'manage_settings'),
                            'pods-packages' => array('label' => 'Packages',
                                                     'function' => array($this, 'admin_packages'),
                                                     'access' => 'manage_packages'),
                            'pods-components' => array('label' => 'Components',
                                                       'function' => array($this, 'admin_components'),
                                                       'access' => 'manage_components'),
                            'pods-help' => array('label' => 'Help',
                                                 'function' => array($this, 'admin_help')));
        $admin_menus = apply_filters('pods_admin_menu', $admin_menus);
        foreach ($admin_menus as $page => $menu_item) {
            if (isset($menu_item['access']) && !pods_access($menu_item['access']))
                continue;
            if (!isset($menu_item['label']))
                $menu_item['label'] = $page;
            if (false === $parent) {
                $parent = $page;
                add_menu_page('Pods Admin', 'Pods Admin', 'read', $parent, null, PODS_URL.'/ui/images/icon16.png');
            }
            add_submenu_page($parent, $menu_item['label'], $menu_item['label'], 'read', $page, $menu_item['function']);
            if ('pods-components' == $page)
                $this->admin_components_menu($parent);
        }
    }

    private function admin_components_menu ($parent = 'pods') {
        $components = $this->api->load_components();
        foreach ($components as $component => $component_data) {
            if (!empty($component_data['HideMenu']))
                continue;
            add_submenu_page($parent, '- ' . strip_tags($component_data['ShortName']), strip_tags($component_data['Name']), 'read', 'pods-components&component=' . urlencode($component), array($this, 'admin_components_handler'));
        }
    }

    public function admin_content () {
        $pod = str_replace('pods-manage-', '', $_GET['page']);
        $ui = pods_ui(array('pod' => $pod));
    }

    public function admin_setup () {
        if (!isset($_GET['preview'])) {
            $ui = pods_ui(array('sql' => array('table' => 'wp_pods',
                                               'select' => 'name, type'),
                                'fields' => array('manage' => array('name',
                                                                    'type'))));
            $ui->go();
        }
        elseif ('edit' == $_GET['preview'])
            require_once PODS_DIR . 'ui/admin/setup.php';
        elseif ('edit-pod' == $_GET['preview'])
            require_once PODS_DIR . 'ui/admin/setup_edit_pod.php';
    }

    public function admin_advanced () {

    }

    public function admin_settings () {

    }

    public function admin_packages () {

    }

    public function admin_components () {
        $components = $this->api->load_components();
        var_dump($components);
    }

    public function admin_components_handler () {
        $components = $this->api->load_components();
        var_dump($components);
    }

    public function admin_help () {

    }

    public function admin_ajax () {
        if (false === headers_sent()) {
            if ('' == session_id())
                @session_start();
            header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
        }
        
        // Sanitize input
        $params = stripslashes_deep((array) $_POST);
        if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) {
            foreach ($params as $key => $val) {
                $params[$key] = pods_sanitize(trim($val));
            }
        }
        $params = (object) $params;
        
        $methods = array('save_pod' => array('priv' => 'manage_pods',
                                             'format' => 'json'),
                         'save_column' => array('priv' => 'manage_pods'),
                         'save_template' => array('priv' => 'manage_templates'),
                         'save_page' => array('priv' => 'manage_pod_pages'),
                         'save_helper' => array('priv' => 'manage_helpers'),
                         'save_roles' => array('priv' => 'manage_roles'),
                         'save_pod_item' => array(),
                         'reorder_pod_item' => array('access_pod_specific' => true),
                         'drop_pod' => array('priv' => 'manage_pods'),
                         'drop_column' => array('priv' => 'manage_pods'),
                         'drop_template' => array('priv' => 'manage_templates'),
                         'drop_page' => array('priv' => 'manage_pod_pages'),
                         'drop_helper' => array('priv' => 'manage_helpers'),
                         'drop_pod_item' => array('access_pod_specific' => true),
                         'load_pod' => array('priv' => 'manage_pods',
                                             'format' => 'json'),
                         'load_column' => array('priv' => 'manage_pods',
                                                'format' => 'json'),
                         'load_template' => array('priv' => 'manage_templates',
                                                  'format' => 'json'),
                         'load_page' => array('priv' => 'manage_pod_pages',
                                              'format' => 'json'),
                         'load_helper' => array('priv' => 'manage_helpers',
                                                'format' => 'json'),
                         'load_sister_fields' => array('priv' => 'manage_pods',
                                                       'format' => 'json'),
                         'load_pod_item' => array('access_pod_specific' => true),
                         'load_files' => array('priv' => 'upload_files'),
                         'export_package' => array('priv' => 'manage_packages',
                                                   'format' => 'json',
                                                   'safe' => true),
                         'import_package' => array('priv' => 'manage_packages'),
                         'validate_package' => array('priv' => 'manage_packages'),
                         'replace_package' => array('priv' => 'manage_packages'),
                         'security_settings' => array('priv' => 'manage_settings'));

        if (!isset($params->method) || !isset($methods[$params->method]))
            pods_error('Invalid AJAX request', $this);

        if (!isset($params->_wpnonce) || false === wp_verify_nonce($params->_wpnonce, 'pods-' . $params->method))
            pods_error('Unauthorized request', $this);

        $defaults = array('priv' => null,
                          'format' => null,
                          'safe' => null,
                          'access_pod_specific' => null);
        $method = (object) array_merge($defaults, (array) $methods[$params->method]);

        if (true === $method->access_pod_specific) {
            $priv_val = false;
            if (isset($params->pod))
                $priv_val = 'pod_' . $params->pod;
            if (false === $priv_val || (!pods_access($priv_val) && !pods_access('manage_content')))
                pods_error('Access denied', $this);
        }

        // Check permissions (convert to array to support multiple)
        if (!empty($method->priv)) {
            foreach ((array) $method->priv as $priv_val) {
                if (!pods_access($priv_val))
                    pods_error('Access denied', $this);
            }
        }

        if ('save_pod_item' == $params->method) {
            $columns = pods_validate_key($params->token, $params->datatype, $params->uri_hash, null, $params->form_count);
            if (false === $columns)
                pods_error('This form has expired. Please reload the page and ensure your session is still active.', $this);

            if (is_array($columns)) {
                foreach ($columns as $key => $val) {
                    $column = is_array($val) ? $key : $val;
                    if (!isset($params->$column))
                        unset($columns[$column]);
                    else
                        $columns[$column] = $params->$column;
                }
            }
            else {
                $tmp = $this->api->load_pod(array('name' => $params->datatype));
                $columns = array();
                foreach ($tmp['fields'] as $field_data) {
                    $column = $field_data['name'];
                    if (!isset($params->$column))
                        continue;
                    $columns[$column] = $params->$column;
                }
            }
            $params->columns = $columns;
        }

        $params = apply_filters('pods_api_' . $params->method, $params, $method);

        if ('security_settings' == $params->method) {
            delete_option('pods_disable_file_browser');
            add_option('pods_disable_file_browser', (isset($params->disable_file_browser) ? $params->disable_file_browser : 0));

            delete_option('pods_files_require_login');
            add_option('pods_files_require_login', (isset($params->files_require_login) ? $params->files_require_login : 0));

            delete_option('pods_files_require_login_cap');
            add_option('pods_files_require_login_cap', (isset($params->files_require_login_cap) ? $params->files_require_login_cap : ''));

            delete_option('pods_disable_file_upload');
            add_option('pods_disable_file_upload', (isset($params->disable_file_upload) ? $params->disable_file_upload : 0));

            delete_option('pods_upload_require_login');
            add_option('pods_upload_require_login', (isset($params->upload_require_login) ? $params->upload_require_login : 0));

            delete_option('pods_upload_require_login_cap');
            add_option('pods_upload_require_login_cap', (isset($params->upload_require_login_cap) ? $params->upload_require_login_cap : ''));
        }
        else {
            // Dynamically call the API method
            $params = (array) $params;
            $output = $this->api->{$params->method}($params);
        }

        // Output in PHP or JSON format
        if ('json' == $method->format && false !== $output)
            $output = json_encode($output);

        // If output for on-page to go into a textarea
        if (true === $method->safe)
            $output = esc_textarea($output);

        if (!is_bool($output))
            echo $output;
    }
}