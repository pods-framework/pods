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
        add_action('admin_init', array($this, 'admin_init'));
    }

    public function admin_init () {
        wp_register_script('pods-floatmenu', PODS_URL . 'ui/js/floatmenu.js', array('jquery'), PODS_VERSION);
        wp_register_style('pods-form', PODS_URL . 'ui/css/pods-form.css', array(), PODS_VERSION);
    }

    public function admin_menu () {
        $submenu = array();
        $this->api = pods_api();
        $results = $this->api->load_pods(array('options' => array('disable_manage' => 0),
                                               'orderby' => '`weight`,`name`'));
        $can_manage = pods_access('manage_content');
        if (false !== $results) {
            foreach ($results as $item) {
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

    }

    public function admin_setup () {
        if (!wp_script_is('jquery-ui-core', 'queue') && !wp_script_is('jquery-ui-core', 'to_do') && !wp_script_is('jquery-ui-core', 'done'))
            wp_print_scripts('jquery-ui-core');
        if (!wp_script_is('jquery-ui-sortable', 'queue') && !wp_script_is('jquery-ui-sortable', 'to_do') && !wp_script_is('jquery-ui-sortable', 'done'))
            wp_print_scripts('jquery-ui-sortable');
        if (!wp_script_is('pods-floatmenu', 'queue') && !wp_script_is('pods-floatmenu', 'to_do') && !wp_script_is('pods-floatmenu', 'done'))
            wp_print_scripts('pods-floatmenu');
        /*if (!wp_script_is('postbox', 'queue') && !wp_script_is('postbox', 'to_do') && !wp_script_is('postbox', 'done'))
            wp_print_scripts('postbox');*/
        if (!wp_style_is('pods-form', 'queue') && !wp_style_is('pods-form', 'to_do') && !wp_style_is('pods-form', 'done'))
            wp_print_styles('pods-form');

        // testing frontend
        if (!isset($_GET['preview']) || 'edit' == $_GET['preview'])
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

    }

    public function admin_components_handler () {
        $components = $this->api->load_components();
    }

    public function admin_help () {

    }
}