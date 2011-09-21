<?php
class PodAdminUI
{
    /**
     * Setup and Handle Admin UI
     * 
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    function __construct () {
        add_action('admin_menu', array($this, 'admin_menu'), 99);
    }

    function admin_menu () {
        $submenu = array();
        $api = pods_api();
        $result = $api->load_pods(array('options' => array('disable_manage' => 0),
                                    'orderby' => '`weight`,`name`'));
        $can_manage = pods_access('manage_content');
        foreach ($result as $item) {
            if (!pods_access('pod_'.$item['name']) && !$can_manage)
                continue;
            $item['options']['label'] = (!empty($item['options']['label'])) ? $item['options']['label'] : ucwords(str_replace('_', ' ', $item['name']));
            $item['options']['label'] = apply_filters('pods_admin_menu_label', $item['options']['label'], $item);
            if (1 == $row['options']['is_toplevel']) {
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
        $parent = false;
        $admin_menus = array('pods' => array('label' => 'Setup',
                                            'function' => array($this, 'admin_setup'),
                                            'access' => 'manage_pods'),
                            'pods-advanced' => array('label' => 'Advanced',
                                                    'function' => array($this, 'admin_advanced'),
                                                    'access' => array('manage_templates','manage_pod_pages','manage_helpers','manage_roles')),
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
                add_object_page('Pods Admin', 'Pods Admin', 'read', $parent, null, PODS_URL.'/ui/images/icon16.png');
            }
            add_submenu_page($parent, $menu_item['label'], $menu_item['label'], 'read', $page, array($this, 'admin_setup'));
        }
    }
    
    function admin_content () {
        
    }
    
    function admin_setup () {
        
    }
    
    function admin_advanced () {
        
    }
    
    function admin_settings () {
        
    }
    
    function admin_packages () {
        
    }
    
    function admin_help () {
        
    }
}