<?php

/**
 * Name: Advanced Templates
 *
 * Description: Creates advanced view templates for displaying Pod data on the front end via a Shortcode.
 *
 * Version: <strong>PROTOTYPE</strong>
 *
 * Category: Advanced
 *
 * Developer Mode: on
 *
 * Menu Page: edit.php?post_type=_pods_adv_template
 * Menu Add Page: post-new.php?post_type=_pods_adv_template
 * @package Pods\Components
 * @subpackage Advanced Templates
 */

/**
 * ==Disclaimer==
 * I have not worked on Pods before, nor seen any of the code,
 * so please go easy on me.
 * I still dont know the api or your coding standards so I'm just winging it for now.
 * I do hope that my thought patterns are not to far off others.
 *
 *
 *
 * ==SOME NOTES==
 *
 * Comments:
 * I still struggle to comment my work effectivly. I will try to be more consistant.
 *
 * The UI:
 * I know some people are very much against custom UI's in WordPress.
 * I did try with the WordPress UI, but space became an issue and somethings
 * just wouldn't work.
 * I did try model the Editor UI within Wordpress so it looked at least like it belonged.
 * Please let me know if it will be an issue.
 *
 * What it does:
 * This aim of this component is to allow you build wrapper elements around a pod,
 * and display it on a page via a shortcode or as a widget.
 *
 * I'm not sure of the name "Advanced Templates" but I'll leave it for now.
 * Maybe something like Pod Elements or what ever.
 *
 * It current just works on a loop. I'm going to add options to set the template up
 * for single view, list, parameters setting, things like that...
 *
 * Anyway, this is just a prototype component to help me learn and understand pods better
 * and hey, It might be useful.
 *
 * ~ David
 *
 */
class Pods_Advanced_Templates extends PodsComponent {

    /**
     * object type
     *
     * @since 0.1
     */
    private $object_type = '_pods_adv_template';
    /**
     * component name
     *
     * @since 0.1
     */
    private $component_name = 'Advanced Templates';
    /**
     * menu name for switching between post type and custom UI
     *
     * @since 0.1
     */
    private $pods_menu = 'pods';
    /**
     * template buffer
     *
     * @since 0.1
     */
    private $template_data = array();
    /**
     * burrered list of pods
     *
     * @since 0.1
     */
    private $pods_list = array();
    /**
     * footer scripts buffer
     *
     * @since 0.1
     */
    private $template_foot;
    /**
     * shortcodes used buffer
     *
     * @since 0.1
     */
    private $shortcodes_used;
    /**
     * header buffer
     *
     * @since 0.1
     */
    private $template_head;

    public function __construct() {

        #populate the pods list
        $api = pods_api();
        $this->pods_list = $api->load_pods();

        # register out templates post type
        $args = array(
            'label' => 'Advanced Templates',
            'labels' => array('singular_name' => 'Pod Advanced Template'),
            'public' => false,
            'can_export' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => false,
            'rewrite' => false,
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title', 'author', 'revisions'),
            'menu_icon' => PODS_URL . 'ui/images/icon16.png'
        );
        if (!is_super_admin())
            $args['capability_type'] = 'pods_adv_template';

        $args = PodsInit::object_label_fix($args, 'post_type');
        register_post_type($this->object_type, apply_filters('pods_internal_register_post_type_object_adv_template', $args));


        if (is_admin()) {
            # on save finds and caches used shortcodes so we dont need to check on each page load
            add_action('post_updated', array($this, 'build_cache'), 10, 3);
            # Clear the cache of used shortcodes on delete
            add_action('delete_post', array($this, 'clear_cache'), 10, 1);

            add_filter('post_row_actions', array($this, 'remove_row_actions'), 10, 2);
            add_filter('bulk_actions-edit-' . $this->object_type, array($this, 'remove_bulk_actions'));

            #custom list view
            add_filter('manage_' . $this->object_type . '_posts_columns', array($this, 'edit_columns'));
            add_action('manage_' . $this->object_type . '_posts_custom_column', array($this, 'column_content'), 10, 2);
        } else {

            # Prefetch page ID for hooking in early so we can the PHP early.
            # This allows a template to be able to add_actions and filters
            add_action('init', array($this, 'pre_process'), 1);
        }


        #early saving from the custom UI screen
        if (isset($_POST['_wpnonce'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'pat-edit-template')) {
                unset($_POST['_wpnonce']);
                unset($_POST['_wp_http_referer']);
                $data = stripslashes_deep($_POST['data']);

                $shortcodes = get_transient('pods_advtemplates');
                # Manually build a custom post for a template
                $templateArgs = array(
                    'post_name' => $data['slug'],
                    'post_title' => $data['name'],
                    'post_type' => '_pods_adv_template'
                );
                if (!empty($data['pod']))
                    $templateArgs['post_status'] = 'publish';
                else
                    # if there is no pod attached, draft it.
                    $templateArgs['post_status'] = 'draft';

                # Create or insert a template
                if (!empty($data['ID'])) {
                    $templateArgs['ID'] = $data['ID'];
                    $lastPost = wp_update_post($templateArgs);
                } else {
                    $lastPost = wp_insert_post($templateArgs);
                }
                # Save template settings as a post meta.
                update_post_meta($lastPost, '_pods_adv_template', $data);

                # Add shortcode to shortcode list.
                $shortcodes[$lastPost] = $data['slug'];
                set_transient('pods_advtemplates', $shortcodes);

                # Send back to post type manager
                wp_redirect('edit.php?post_type='.$this->object_type.'&last=' . $lastPost);
                die;
            }
        }

        # Grab the default edit page - direct to UI
        # Editing: redirect with edit=ID
        if (isset($_GET['post']) && isset($_GET['action'])) {
            # Check if its our post type
            if ($this->object_type == get_post_type($_GET['post']) && $_GET['action'] == 'edit') {
                wp_redirect('admin.php?page=pods-component-advanced-templates&edit=' . $_GET['post']);
                die;
            }
        }
        # Editing: new template
        if (isset($_GET['post_type'])) {
            # if its a new post - send to the ui as a new tempalte
            if ($this->object_type == $_GET['post_type'] && 'post-new.php' == basename($_SERVER['SCRIPT_NAME'])) {
                wp_redirect('admin.php?page=pods-component-advanced-templates&edit');
                die;
            }
        }
        # Admin: Send to admin UI
        if (!empty($_GET['page'])) {
            if ('pods-component-advanced-templates' == $_GET['page'] && !isset($_GET['edit'])) {
                wp_redirect('edit.php?post_type='.$this->object_type);
                die;
            } elseif ('pods-component-advanced-templates' == $_GET['page'] && isset($_GET['edit'])) {
                # Switch menu type from post type to admin ui.
                # This overides the post type to a page
                add_action('admin_menu', array($this, 'admin_menu_switch'), 101);
            }
        }
    }

    /**
     *  Pickup prescan the content to find shortcodes being used,
     *  then push headers .
     *  This needs to be done on init action to allow the use of adding actions and filters
     *  in the PHP tab of a template.
     *
     */
    function pre_process() {

        /**
         * Not the best way to do it, does not support custom types.
         * I'm basically guessing my way through this.
         * Any suggestions?
         */

        # Convert request URL to post ID
        $postID = url_to_postid($_SERVER['REQUEST_URI']);
        if (empty($postID)) {
            # It may be a front page item
            $frontPage = get_option('page_on_front');
            if (!empty($frontPage)) {
                # Get the front page
                $posts[] = get_post($frontPage);
            } else {
                # Blog page, get the posts being shown
                $args = array(
                    'numberposts' => get_option('posts_per_page')
                );
                $posts = get_posts($args);
            }
        } else {
            # Found the ID, load it up.
            $posts[] = get_post($postID);
        }

        # Cant find anything, just get out.
        if (empty($posts))
            return;

        # Load up the cache of shortcode tempaltes used on this page
        $usedCache = get_transient('pods_adv_template_used');

        #loop through the posts found
        foreach ($posts as $post) {
            # If there are shortcode templates used on this post process it.
            if (!empty($usedCache[$post->ID])) {
                # Get the template used
                foreach ($usedCache[$post->ID] as $template => $code) {
                    #load up the template or use it if its been used already
                    if(!isset($this->shortcodes_used[$code])){
                        $templateData = get_post_meta($template, '_pods_adv_template', true);
                        $this->shortcodes_used[$code] = $templateData;
                    }else{
                        $templateData = $this->shortcodes_used[$code];
                    }
                    # Dynamically add the shortcode so we dont need to register everything.
                    add_shortcode($code, array($this, 'render_shortcode'));

                    #Execute the PHP code of the template.
                    # Any add_filter or add_action for after init will now work
                    # Any php function should be available
                    if (!empty($templateData['phpCode'])) {
                        eval($templateData['phpCode']);
                    }

                    # enqueue external script libraries correctly.
                    if (!empty($templateData['jsLib'])) {
                        foreach ($templateData['jsLib'] as $key => $url) {
                            $in_footer = 0;
                            if (2 == (int) $templateData['jsLibLoc'][$key]) {
                                $in_footer = 1;
                            }
                            wp_enqueue_script('adv-' . $key, $url, array(), false, $in_footer);
                        }
                    }
                    # enqueue external CSS libraries correctly.
                    if (!empty($templateData['cssLib'])) {
                        foreach ($templateData['cssLib'] as $key => $url) {
                            wp_enqueue_style('adv-' . $key, $url);
                        }
                    }

                    # Process CSS and Build in the Cache file.
                    # PHP is available to make it more dynamic.
                    ob_start();
                    eval(' ?>' . $templateData['cssCode']);
                    $Css = ob_get_clean();

                    # build a temp file for this and register its existance
                    $cssCache = get_transient('_pods_adv_template_css_cache');
                    $cachePath = wp_upload_dir();
                    $cssHash = md5($Css);
                    if (!file_exists($cachePath['basedir'] . '/cache')) {
                        mkdir($cachePath['basedir'] . '/cache');
                    }
                    # check cache and clear exired items
                    if (empty($cssCache)) {
                        $cssCache = array();
                    } else {
                        # cleanout expired stuff
                        foreach ($cssCache as $cachefile => $expire) {
                            if (mktime() - $expire > 0) {
                                unlink($cachePath['basedir'] . '/cache/' . $cachefile . '.css');
                                unset($cssCache[$cachefile]);
                            }
                        }
                    }
                    # make new cache file
                    $tempfile = $cachePath['basedir'] . '/cache/' . $cssHash . '.css';
                    $cached = true;
                    if (!file_exists($tempfile)) {
                        # write new file
                        $cached = false;
                        $fp = @fopen($tempfile, 'w+');
                        if ($fp) {
                            $cached = true;
                            fwrite($fp, $Css);
                            fclose($fp);
                            $cssCache[$cssHash] = strtotime('+5 days');
                            set_transient('_pods_adv_template_css_cache', $cssCache);
                        }
                    }
                    if ($cached) {
                        # enqueue cache file correctly
                        wp_enqueue_style($cssHash, $cachePath['baseurl'] . '/cache/' . $cssHash . '.css');
                    } else {
                        # resort to header placement if cant write the file
                        $this->template_head .= "<style type=\"text/css\">\n" . $Css . "\n</stlyle>\n";
                    }
                    # Buffer the javascript tab content for the footer.
                    if (!empty($templateData['javascriptCode'])) {
                        $this->template_foot .= "\n" . $templateData['javascriptCode'];
                        add_action('wp_footer', array($this, 'footer_scripts'));
                    }
                }
            }
        }
    }

    /**
     * Render Shortcode
     * Still got work to do here!
     */
    function render_shortcode($content, $args, $code) {

        # get the pod
        # Will do sorting, finding and stuff like that later. for now, its a test with the data as a whole.
        $pods = pods($this->pods_list[$this->shortcodes_used[$code]['pod']]['name']);
        # Just get everything for now.
        $pods->find();

        # find the loop statment to get the loopable area for each pod record.
        $pattern = '\[(\[?)(loop)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
        preg_match_all('/' . $pattern . '/s', $this->shortcodes_used[$code]['htmlCode'], $loops);
        if (!empty($loops)) {
            foreach ($loops[0] as $loopKey => $loopcode) {
                if (!empty($loops[2][$loopKey]) && !empty($loops[5][$loopKey])) {
                    $this->shortcodes_used[$code]['htmlCode'] = str_replace($loopcode, Pods_Templates::template(null, $loops[5][$loopKey], $pods), $this->shortcodes_used[$code]['htmlCode']);
                }
            }
            ob_start();
            eval('?> ' . $this->shortcodes_used[$code]['htmlCode']);
            $return = ob_get_clean();

        }else{
            # Push the full template as a loop
            # Process php in the template
            ob_start();
            eval('?> ' . $this->shortcodes_used[$code]['htmlCode']);
            $this->shortcodes_used[$code]['htmlCode'] = ob_get_clean();

            vardump($pods);

            # Push it to the template renderer
            $return = Pods_Templates::template(null, $this->shortcodes_used[$code]['htmlCode'], $pods);
        }

        # Do it again for embeded shortcodes in the templates.
        return do_shortcode($return);
    }

    /**
     * Get list of used Shortcodes in the content provided
     * Recursive to find any shortcode templates within templates
     * This allows header libs and php etc. to load correctly in embeded codes
     */
    function get_used($content, $codes) {

        /**
         * I may want to cache this process.
         */
        $codesList = array();
        foreach ($codes as $id => $code) {
            $codesList[$code] = $id;
        }


        $tagregexp = join('|', array_map('preg_quote', $codes));

        # Just pulled in the code from WP get_regex and customizzed it to only
        # look for shortcodes of templates we've made.
        $regex =
                '\\['                              # Opening bracket
                . '(\\[?)'                           # 1: Optional second opening bracket for escaping shortcodes: [[tag]]
                . "($tagregexp)"                     # 2: Shortcode name
                . '\\b'                              # Word boundary
                . '('                                # 3: Unroll the loop: Inside the opening shortcode tag
                . '[^\\]\\/]*'                   # Not a closing bracket or forward slash
                . '(?:'
                . '\\/(?!\\])'               # A forward slash not followed by a closing bracket
                . '[^\\]\\/]*'               # Not a closing bracket or forward slash
                . ')*?'
                . ')'
                . '(?:'
                . '(\\/)'                        # 4: Self closing tag ...
                . '\\]'                          # ... and closing bracket
                . '|'
                . '\\]'                          # Closing bracket
                . '(?:'
                . '('                        # 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
                . '[^\\[]*+'             # Not an opening bracket
                . '(?:'
                . '\\[(?!\\/\\2\\])' # An opening bracket not followed by the closing shortcode tag
                . '[^\\[]*+'         # Not an opening bracket
                . ')*+'
                . ')'
                . '\\[\\/\\2\\]'             # Closing shortcode tag
                . ')?'
                . ')'
                . '(\\]?)';                          # 6: Optional second closing brocket for escaping shortcodes: [[tag]]

        preg_match_all('/' . $regex . '/s', $content, $found);
        $foundList = array();
        # If found any shortcodes process them
        if (!empty($found[2])) {
            foreach ($found[2] as $foundCode) {
                if (!isset($foundList[$codesList[$foundCode]])) {
                    # register the template code as not to use it again
                    $foundList[$codesList[$foundCode]] = $foundCode;
                    # Fetch the template data
                    $foundTemplate = get_post_meta($codesList[$foundCode], '_pods_adv_template', true);
                    # if there is content in the html tab, process it to find
                    # embeded shortcodes.
                    # And yes you can create a endless loop by placing its own
                    # shortcode in its html tab. Don't do it!
                    if (!empty($foundTemplate['htmlCode']))
                        $foundList = $foundList + $this->get_used($foundTemplate['htmlCode'], $codes);
                }
            }
        }
        # Send back the list of codes used
        return $foundList;
    }

    /**
     * Output Footer Scripts
     *
     */
    function footer_scripts() {
        # Simply echo out the javascript
        # I may want to make this cached like the CSS and have it enqueue it correctly.
        if (!empty($this->template_foot)) {
            echo "<script type='text/javascript'>\n/* <![CDATA[ */\n";
            echo $this->template_foot;
            echo "/* ]]> */\n</script>\n";
        }
    }

    /**
     * Set the edit list columns
     */
    function edit_columns($columns) {
        # Defining the columns i want
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Template', 'pods'),
            'shortcode' => __('Shortcode', 'pods'),
            'pod' => __('Pod', 'pods'),
            'date' => __('Date', 'pods')
        );

        return $columns;
    }

    /**
     * Column content for list edit
     */
    function column_content($column, $post_id) {

        global $post, $_pod;

        if (empty($this->template_data[$post_id])) {
            $this->template_data[$post_id] = get_post_meta($post_id, '_pods_adv_template', true);
        }
        switch ($column) {

            /* the pod column. */
            case 'pod' :

                if (empty($this->template_data[$post_id]['name']))
                    echo '<em class="description">' . __('not set', 'pods') . '</em>';

                else
                    echo $this->pods_list[$this->template_data[$post_id]['pod']]['name'];

                break;

            /* the shortcode column. */
            case 'shortcode' :

                echo '[' . $this->template_data[$post_id]['slug'] . ']';

                break;

            /* Just break out of the switch statement for everything else. */
            default :
                break;
        }
    }

    /**
     * Manage menu overides for switching between post type and admin page
     *
     */
    public function admin_menu_switch() {
        global $submenu;
        #replace the default registered post type with a custom UI page instead.
        foreach ($submenu[$this->pods_menu] as $menu_key => &$menu) {
            if ($this->component_name == $menu[3]) {
                $menu[2] = 'pods-component-' . sanitize_title($this->component_name);
            }
        }
    }

    /**
     * Enqueue styles
     *
     * @since 2.0.0
     */
    public function admin_assets() {

        if (!isset($_GET['post_type'])) {
            global $submenu;
            # Cleanup my overide.
            unset($submenu['pods'][0]);
        }


        wp_enqueue_style('pat_adminStyle', PODS_URL . 'components/Advanced-Templates/ui/styles/core.css');
        if (isset($_GET['edit'])) {

            /**
             *  I'm loading in an embeded version of codemirror as i needed extra
             *  features thats only available in the newly released V3.0
             *  Maybe we can add it to the core?
             */
            wp_enqueue_style('thickbox');
            wp_enqueue_style('codemirror', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/codemirror.css', false, array(), false);
            wp_enqueue_style('codemirror-simple-hint', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/simple-hint.css', false);
            wp_enqueue_style('codemirror-dialog-css', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/dialog.css', false);
            wp_enqueue_style('codemirror', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/codemirror.css', false);


            wp_enqueue_script("jquery", false, array(), false, false);
            wp_enqueue_script('jquery-ui-core', false, array(), false, false);
            wp_enqueue_script('jquery-ui-sortable', false, array(), false, false);
            wp_enqueue_script('jquery-ui-draggable', false, array(), false, false);
            wp_enqueue_script('jquery-ui-droppable', false, array(), false, false);

            wp_enqueue_script('media-upload', false, array(), false, false);
            wp_enqueue_script('thickbox', false, array(), false, false);

            wp_enqueue_script('codemirror', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/codemirror.js', array(), false, true);
            wp_enqueue_script('codemirror-overlay', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/overlay.js', array(), false, true);
            wp_enqueue_script('codemirror-mode-css', PODS_URL . 'components/Advanced-Templates/ui/codemirror/mode/css/css.js', array(), false, true);
            wp_enqueue_script('codemirror-mode-js', PODS_URL . 'components/Advanced-Templates/ui/codemirror/mode/javascript/javascript.js', array(), false, true);
            wp_enqueue_script('codemirror-mode-xml', PODS_URL . 'components/Advanced-Templates/ui/codemirror/mode/xml/xml.js', array(), false, true);
            wp_enqueue_script('codemirror-mode-clike', PODS_URL . 'components/Advanced-Templates/ui/codemirror/mode/clike/clike.js', array(), false, true);
            wp_enqueue_script('codemirror-mode-php', PODS_URL . 'components/Advanced-Templates/ui/codemirror/mode/php/php.js', array(), false, true);
            wp_enqueue_script('codemirror-mode-htmlmxed', PODS_URL . 'components/Advanced-Templates/ui/codemirror/mode/htmlmixed/htmlmixed.js', array(), false, true);
            wp_enqueue_script('codemirror-simple-hintjs', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/simple-hint.js', array(), false, true);
            wp_enqueue_script('codemirror-close-tag', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/closetag.js', array(), false, true);
            wp_enqueue_script('codemirror-xml-hint', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/xml-hint.js', array(), false, true);
            wp_enqueue_script('codemirror-dialog-js', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/dialog.js', array(), false, true);
            wp_enqueue_script('codemirror-srchcursor-js', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/searchcursor.js', array(), false, true);
            wp_enqueue_script('codemirror-multiplex-js', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/multiplex.js', array(), false, true);
            wp_enqueue_script('codemirror-search-js', PODS_URL . 'components/Advanced-Templates/ui/codemirror/lib/util/search.js', array(), false, true);
            wp_enqueue_script('bootstrap-typeahead', PODS_URL . 'components/Advanced-Templates/ui/libs/js/typeahead.js', array(), false, true);
            wp_enqueue_script('editor-js', PODS_URL . 'components/Advanced-Templates/ui/libs/js/editor.js', array(), false, true);
            wp_enqueue_style('pat_adminecleaner', PODS_URL . 'components/Advanced-Templates/ui/css/editor.css');
        } else {
            wp_enqueue_style('pods-admin');
        }
    }

    /**
     * Remove unused row actions
     *
     * @since 2.0.5
     */
    function remove_row_actions($actions, $post) {
        global $current_screen;

        if ($this->object_type != $current_screen->post_type)
            return $actions;

        if (isset($actions['view']))
            unset($actions['view']);

        if (isset($actions['inline hide-if-no-js']))
            unset($actions['inline hide-if-no-js']);

        # W3 Total Cache
        if (isset($actions['pgcache_purge']))
            unset($actions['pgcache_purge']);

        return $actions;
    }

    /**
     * Remove unused bulk actions
     *
     * @since 2.0.5
     */
    public function remove_bulk_actions($actions) {
        if (isset($actions['edit']))
            unset($actions['edit']);

        return $actions;
    }

    /**
     * build cache on save
     * Registeres a list of tempalte shortcodes used in a post.
     * This is so the we can load up the headers before the do_shortcode to enable us
     * to enqueue then correctly
     */
    public function build_cache($data, $pod = null, $id = null, $groups = null, $post = null) {
        if (!is_array($data) && 0 < $data) {
            $codes = get_transient('pods_advtemplates');
            if (!empty($pod->post_content)) {
                $usedCodes = $this->get_used($pod->post_content, $codes);
                $usedCache = get_transient('pods_adv_template_used');
                $usedCache[$data] = $usedCodes;
                set_transient('pods_adv_template_used', $usedCache);

                $post = $data;
                $post = get_post($post);

                if (is_object($id)) {
                    $old_post = $id;

                    pods_transient_clear('pods_object_adv_template_' . $old_post->post_title);
                }
            }
        }

        if (empty($post))
            return;

        if ($this->object_type != $post->post_type)
            return;

        pods_transient_clear('pods_object_adv_template');
        pods_transient_clear('pods_object_adv_template_' . $post->post_title);
    }

    /**
     * Clear cache on delete
     *Clean up used shortcode list
     * @since 2.0.0
     */
    public function clear_cache($data, $pod = null, $id = null, $groups = null, $post = null) {

        if (!is_array($data) && 0 < $data) {

            $codes = get_transient('pods_adv_template_used');
            if (isset($codes[$data])) {
                unset($codes[$data]);
                set_transient('pods_adv_template_used', $codes);
            }

            $post = $data;
            $post = get_post($post);

            if (is_object($id)) {
                $old_post = $id;

                pods_transient_clear('pods_object_adv_template_' . $old_post->post_title);
            }
        }

        if (empty($post))
            return;

        if ($this->object_type != $post->post_type)
            return;

        pods_transient_clear('pods_object_adv_template');
        pods_transient_clear('pods_object_adv_template_' . $post->post_title);
    }

    /**
     * Build admin area
     *
     * @param $options
     *
     * @since 2.0.0
     */
    public function admin($options, $component) {

        $method = 'template_ajax';
        require_once PODS_DIR . 'components/Advanced-Templates/ui/libs/functions.php';
        pods_view(PODS_DIR . 'components/Advanced-Templates/ui/editor.php', compact(array_keys(get_defined_vars())));
    }

    /**
     * a simple helper for me :)
     */
    function dump($a, $die = true) {
        echo '<pre>';
        print_r($a);
        echo '</pre>';
        if ($die) {
            die;
        }
    }

    /**
     * Handle the Conversion AJAX
     *
     * @param $params
     */
    public function ajax_template_ajax($params) {

        $out['type'] = $params->process;
        switch ($params->process) {
            case 'swap_pod':
                $api = pods_api();
                $_pods = $api->load_pods();
                $podFields = array();
                foreach ($_pods as $pod) {
                    if ((int) $params->pod === (int) $pod['id']) {

                        if (!empty($pod['options']['supports_title']))
                            $podFields[] = "@title";

                        if (!empty($pod['options']['supports_editor']))
                            $podFields[] = "@content";

                        foreach ($pod['fields'] as $podField => $fiedSet) {
                            $podFields[] = "@" . $podField;
                        }
                    }
                }

                $out['data'] = $podFields;
                break;
            case 'apply_changes':
                # Need to confirm things have been saved and updated..
                parse_str(stripslashes($params->data), $predata);
                if (get_magic_quotes_gpc()) {
                    $predata = array_map('stripslashes_deep', $predata);
                }
                $data = $predata['data'];
                $templateArgs = array(
                    'post_name' => $data['slug'],
                    'post_title' => $data['name'],
                    'post_type' => '_pods_adv_template'
                );

                if (!empty($data['pod']))
                    $templateArgs['post_status'] = 'publish';
                else
                    $templateArgs['post_status'] = 'draft';

                if (!empty($data['ID'])) {
                    $templateArgs['ID'] = $data['ID'];
                    $lastPost = wp_update_post($templateArgs);
                } else {
                    $lastPost = wp_insert_post($templateArgs);
                }
                update_post_meta($lastPost, '_pods_adv_template', $data);

                $out['title'] = $data['name'];
                $out['id'] = $lastPost;
                break;
        }

        header("ContentType:application/json charset=utf-8");
        echo json_encode($out);
    }

}

?>