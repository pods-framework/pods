<?php
class PodsAdmin {

    private $api;
    private $data;

    /**
     * Setup and Handle Admin functionality
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct() {
        $this->api = pods_api();
        $this->data = pods_data();

        add_action('admin_enqueue_scripts', array($this, 'admin_head'));
        add_action('admin_menu', array($this, 'admin_menu'), 99);
        add_action('admin_init', array($this, 'admin_init'), 9);
        if (is_admin()) {
            add_action('wp_ajax_pods_admin', array($this, 'admin_ajax'));
            add_action('wp_ajax_nopriv_pods_admin', array($this, 'admin_ajax'));
        }
    }

    public function admin_init() {
        // Fix for plugins that *don't do it right* so we don't cause issues for users
        if (defined('DOING_AJAX') && !empty($_POST) && ('pods_admin' == pods_var('action', 'get') || 'pods_admin' == pods_var('action', 'post'))) {
            foreach ($_POST as $key => $value) {
                if ('action' == $key)
                    continue;
                unset($_POST[$key]);
                $_POST['_podsfix_' . $key] = $value;
            }
        }
    }

    public function admin_head() {
        wp_register_style('pods-admin', PODS_URL . 'ui/css/pods-admin.css', array(), PODS_VERSION);
        if (!wp_style_is('jquery-ui', 'registered'))
            wp_register_style('jquery-ui', PODS_URL . 'ui/css/smoothness/jquery-ui-1.8.16.custom.css', array(), '1.8.16');
        wp_register_script('pods-floatmenu', PODS_URL . 'ui/js/floatmenu.js', array(), PODS_VERSION);
        wp_register_script('pods-cleditor-min', PODS_URL . 'ui/js/jquery.cleditor.min.js', array(), PODS_VERSION);
        wp_register_style('pods-cleditor', PODS_URL . 'ui/css/jquery.cleditor.css', array(), PODS_VERSION);
        wp_register_script('pods-admin-importer', PODS_URL . 'ui/js/admin-importer.js', array(), PODS_VERSION);
        if (!wp_script_is('pods-qtip', 'registered'))
            wp_register_script('pods-qtip', PODS_URL . 'ui/js/jquery.qtip.min.js', array('jquery'), '2.0-2011-10-02');
        if (!wp_style_is('pods-qtip', 'registered'))
            wp_register_style('pods-qtip', PODS_URL . 'ui/css/jquery.qtip.min.css', array(), '2.0-2011-10-02');
        if (!wp_script_is('pods-qtip-init', 'registered'))
            wp_register_script('pods-qtip-init', PODS_URL . 'ui/js/qtip.js', array('jquery', 'pods-qtip'), PODS_VERSION);
        wp_register_script('jquery-pods-admin', PODS_URL . 'ui/js/jquery.pods.admin.js', array('jquery'), PODS_VERSION);
        wp_register_style('pods-manage', PODS_URL . 'ui/css/pods-manage.css', array(), PODS_VERSION);
        wp_register_script('pods-forms', PODS_URL . 'ui/js/forms.js', array(), PODS_VERSION);
        if (!wp_script_is('jquery-ui-timepicker', 'registered'))
            wp_register_script('jquery-ui-timepicker', PODS_URL . 'ui/js/jquery.ui.timepicker.min.js', array('jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider'), '0.9.7');
        if (!wp_style_is('jquery-ui-timepicker', 'registered'))
            wp_register_style('jquery-ui-timepicker', PODS_URL . 'ui/css/jquery.ui.timepicker.css', array(), '0.9.7');
        wp_register_script('pods-file-attach', PODS_URL . 'ui/js/file-attach.js', array(), PODS_VERSION);
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            if ('pods' == $page || (false !== strpos($page, 'pods-') && 0 === strpos($page, 'pods-'))) {
                ?>
                <script type="text/javascript">
                    var PODS_URL = "<?php echo PODS_URL; ?>";
                </script>
                <?php
                wp_enqueue_script('jquery');
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-sortable');

                wp_enqueue_style('jquery-ui');

                wp_enqueue_script('pods-floatmenu');

                if (false !== strpos($page, 'pods-import-')) {
                    wp_enqueue_script('pods-admin-importer');
                    wp_enqueue_script('jquery-ui-effects-fade', PODS_URL . 'ui/js/jquery-ui/jquery.effects.fade.js', array('jquery'), '1.8.8');
                }

                wp_enqueue_script('pods-qtip');
                wp_enqueue_style('pods-qtip');

                wp_enqueue_script('pods-qtip-init');

                wp_enqueue_script('jquery-pods-admin');

                if (false !== strpos($page, 'pods-manage-') && 0 === strpos($page, 'pods-manage-')) {
                    wp_enqueue_style('pods-manage');

                    // Just for demo
                    wp_enqueue_script('pods-forms');

                    wp_enqueue_style('pods-cleditor');

                    wp_enqueue_script('jquery-ui-slider');
                    wp_enqueue_script('jquery-ui-button');
                    wp_enqueue_script('jquery-ui-autocomplete');
                    wp_enqueue_script('pods-cleditor-min');
                    // Date
                    wp_enqueue_script('jquery-ui-datepicker');

                    // Date + Time
                    wp_enqueue_script('jquery-ui-timepicker');
                    wp_enqueue_style('jquery-ui-timepicker');

                    // File Upload
                    wp_enqueue_script('thickbox');
                    wp_enqueue_style('thickbox');
                    wp_enqueue_script('pods-file-attach');
                }
                else
                    wp_enqueue_style('pods-admin');
                if ('pods-advanced' == $page) {
                    wp_register_style('pods-parts', PODS_URL . 'ui/css/pods-parts.css', array(), '1.0');
                    wp_enqueue_style('pods-parts');

                    wp_enqueue_script('jquery-ui-effects-core', PODS_URL . 'ui/js/jquery-ui/jquery.effects.core.js', array('jquery'), '1.8.8');
                    wp_enqueue_script('jquery-ui-effects-fade', PODS_URL . 'ui/js/jquery-ui/jquery.effects.fade.js', array('jquery'), '1.8.8');
                    wp_enqueue_script('jquery-ui-dialog');

                    wp_register_script('parts', PODS_URL . 'ui/js/parts.js', array(), PODS_VERSION);
                    wp_enqueue_script('parts');
                }
            }
        }
    }

    public function admin_menu() {
        $submenu = array();
        $results = $this->api->load_pods(array(//'options' => array('disable_manage' => 0),
            'orderby' => '`weight`, `name`', 'type' => 'pod'));
        $can_manage = pods_access('manage_content');
        if ( !defined( 'PODS_DEVELOPER' ) )
            $results = false; // not yet!
        if (false !== $results) {
            foreach ((array) $results as $item) {
                if (!pods_access('pod_' . $item['name']) && !$can_manage)
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
                        add_object_page('Pods', 'Pods', 'read', $parent, null, PODS_URL . '/ui/images/icon16.png');
                    }
                    add_submenu_page($parent, "Manage {$item['options']['label']}", "Manage {$item['options']['label']}", 'read', $page, array($this, 'admin_content'));
                }
            }
        }

        $admin_menus = array('pods' => array('label' => 'Setup',
                                             'function' => array($this, 'admin_setup'),
                                             'access' => 'manage_pods'),
                             'pods-help' => array('label' => 'Help',
                                                  'function' => array($this, 'admin_help')));
        if ( defined( 'PODS_DEVELOPER' ) ) {
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
        }
        $admin_menus = apply_filters('pods_admin_menu', $admin_menus);

        $parent = false;
        foreach ($admin_menus as $page => $menu_item) {
            if (isset($menu_item['access']) && !pods_access($menu_item['access']))
                continue;
            if (!isset($menu_item['label']))
                $menu_item['label'] = $page;
            if (false === $parent) {
                $parent = $page;
                add_menu_page('Pods Admin', 'Pods Admin', 'read', $parent, null, PODS_URL . '/ui/images/icon16.png');
            }
            add_submenu_page($parent, $menu_item['label'], $menu_item['label'], 'read', $page, $menu_item['function']);
            if ('pods-components' == $page)
                $this->admin_components_menu($parent);
        }

        if ( defined( 'PODS_DEVELOPER' ) ) {
            add_submenu_page('pods', 'x Import - Table', 'x Import - Table', 'manage_options', 'pods-import-table', array($this, 'pods_import_table'));
            add_submenu_page('pods', 'x Media Upload - Test', 'x Media Upload - Test', 'manage_options', 'media-upload-test', array($this, 'media_upload_test'));
			add_submenu_page('pods', 'x Form Test', 'x Form Test', 'manage_options', 'pods-manage-form-test', array($this, 'admin_content_form'));
        }
    }

    private function admin_components_menu($parent = 'pods') {
        $components = $this->api->load_components();
        foreach ($components as $component => $component_data) {
            if (!empty($component_data['HideMenu']))
                continue;
            add_submenu_page($parent, strip_tags($component_data['Name']), '- ' . strip_tags($component_data['ShortName']), 'read', 'pods-component-' . $component_data['ID'], array($this, 'admin_components_handler'));
        }
    }

    public function admin_content() {
        $pod = str_replace('pods-manage-', '', $_GET['page']);
        $ui = pods_ui(array('pod' => $pod,
                            'actions_custom' => array('form' => array( $this, 'admin_content_form' ))));
    }

    public function admin_content_form() {
        require_once PODS_DIR . 'ui/admin/form.php';
    }

    public function media_upload_test() {
        wp_enqueue_script('plupload-all');
        require_once PODS_DIR . 'ui/admin/media_upload_test.php';
    }

    public function pods_import_table() {
        require_once PODS_DIR . 'ui/admin/pods_import_table.php';
    }

    public function pods_import_convert_fields() {
        require_once PODS_DIR . 'ui/admin/pods_import_convert_fields.php';
    }

    public function pods_import_create_pod() {
        require_once PODS_DIR . 'ui/admin/pods_import_create_pod.php';
    }

	public function pods_form_test() {
		require_once PODS_DIR . 'ui/admin/pods_form_test.php';
	}

    public function admin_setup() {
        pods_ui(array('sql' => array('table' => '@wp_pods',
                                     'select' => 'name, type, "0" AS field_count'),
                      'icon' => PODS_URL .'ui/images/icon32.png',
                      'items' => 'Pods',
                      'item' => 'Pod',
                      'orderby' => 'name',
                      //'reorder' => array('on' => 'weight'),
                      'fields' => array('manage' => array('name', 'type')),
                      'actions_disabled' => array('duplicate', 'view', 'export'),
                      'actions_custom' => array('add' => array($this, 'admin_setup_add'),
                                                'edit' => array($this, 'admin_setup_edit'),
                                                'delete' => array($this, 'admin_setup_delete'))));
    }

    public function admin_setup_add($obj) {
        require_once PODS_DIR . 'ui/admin/setup_add_pod.php';
    }

    public function admin_setup_edit($duplicate, $obj) {
        require_once PODS_DIR . 'ui/admin/setup_edit_pod.php';
    }

    public function admin_setup_delete($id, $obj) {
        $this->api->drop_pod(array('id' => $id));
        $obj->message('Pod deleted succesfully.');
    }

    public function admin_advanced() {
        require_once PODS_DIR . 'ui/admin/setup_edit_part.php';
    }

    public function admin_settings() {

    }

    public function admin_packages() {
        pods_ui(array('sql' => array('table' => '@wp_pods_objects'),
                      'icon' => PODS_URL .'ui/images/icon32.png',
                      'items' => 'Packages',
                      'item' => 'Package',
                      'orderby' => 'name',
                      'where' => 'type="package"',
                      'fields' => array('manage' => array('name')),
                      'actions_disabled' => array('edit', 'duplicate', 'view', 'export'),
                      'actions_custom' => array('add' => array($this, 'admin_packages_add'))));
    }

    public function admin_components() {
        $components = $this->api->load_components();
        var_dump($components);
    }

    public function admin_components_handler() {
        $components = $this->api->load_components();
        var_dump($components);
    }

    public function admin_help() {
        require_once PODS_DIR . 'ui/admin/help.php';
    }

    public function admin_ajax() {
        if (false === headers_sent()) {
            if ('' == session_id())
                @session_start();
            header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
        }

        // Sanitize input
        $params = stripslashes_deep((array) $_POST);
        foreach ($params as $key => $value) {
            if ('action' == $key)
                continue;
            unset($params[$key]);
            $params[str_replace('_podsfix_', '', $key)] = $value;
        }
        if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
            $params = pods_sanitize($params);

        $params = (object) $params;

        $methods = array('add_pod' => array('priv' => 'manage_pods'),
                         'save_pod' => array('priv' => 'manage_pods'),
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

        $methods = apply_filters('pods_admin_ajax_methods', $methods, $this);

        if (!isset($params->method) || !isset($methods[$params->method]))
            pods_error('Invalid AJAX request', $this);

        if (!method_exists($this->api, $params->method))
            pods_error('API method does not exist', $this);

        if (!isset($params->_wpnonce) || false === wp_verify_nonce($params->_wpnonce, 'pods-' . $params->method))
            pods_error('Unauthorized request', $this);

        $defaults = array('priv' => null,
                          'format' => null,
                          'safe' => null,
                          'access_pod_specific' => null,
                          'name' => $params->method);

        $method = (object) array_merge($defaults, (array) $methods[$params->method]);

        // Cleaning up $params
        unset($params->action);
        unset($params->method);
        unset($params->_wpnonce);

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

        if ('save_pod_item' == $method->name) {
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

        $params = apply_filters('pods_api_' . $method->name, $params, $method);

        if ('security_settings' == $method->name) {
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
        } else {
            // Dynamically call the API method
            $params = (array) $params;
            $output = $this->api->{$method->name}($params);
        }

        // Output in PHP or JSON format
        if ('json' == $method->format && false !== $output)
            $output = json_encode($output);

        // If output for on-page to go into a textarea
        if (true === $method->safe)
            $output = esc_textarea($output);

        if (!is_bool($output))
            echo $output;

        die(); // KBAI!
    }

}
