<?php
/**
 * @package Pods
 */
class PodsUI {

	/**
	 * @var null Nonce for security
	 */
	private $_nonce = null;

    // internal
    /**
     * @var bool|PodsData
     */
    private $pods_data = false;

    /**
     * @var array
     */
    private $actions = array(
        'manage',
        'add',
        'edit',
        'duplicate',
        'save',
        //'view',
        'delete',
        'reorder',
        'export'
    );

    /**
     * @var array
     */
    private $ui_page = array();

    /**
     * @var bool
     */
    private $unique_identifier = false;

    // base
    public $x = array();

    /**
     * @var array|bool|mixed|null|Pods
     */
    public $pod = false;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $num = ''; // allows multiple co-existing PodsUI instances with separate functionality in URL

    /**
     * @var array
     */
    static $excluded = array(
        'do',
        'id',
        'pg',
        'search',
        'filter_*',
        'orderby',
        'orderby_dir',
        'limit',
        'action',
        'action_bulk',
        'action_bulk_ids',
        '_wpnonce',
        'view',
        'export',
        'export_type',
        'export_delimiter',
        'remove_export',
        'updated',
        'duplicate',
		'message'
    ); // used in var_update

    static $allowed = array(
        'page',
        'post_type'
    );

    // ui
    /**
     * @var bool
     */
    public $item = false; // to be set with localized string
    /**
     * @var bool
     */
    public $items = false; // to be set with localized string
    /**
     * @var bool
     */
    public $heading = false; // to be set with localized string array
    /**
     * @var bool
     */
    public $header = false; // to be set with localized string array
    /**
     * @var bool
     */
    public $label = false; // to be set with localized string array
    /**
     * @var bool
     */
    public $icon = false;

    /**
     * @var bool
     */
    public $css = false; // set to a URL of stylesheet to include
    /**
     * @var bool
     */
    public $wpcss = false; // set to true to include WP Admin stylesheets
    /**
     * @var array
     */
    public $fields = array(
        'manage' => array(),
        'search' => array(),
        'form' => array(),
        'add' => array(),
        'edit' => array(),
        'duplicate' => array(),
        'view' => array(),
        'reorder' => array(),
        'export' => array()
    );

    /**
     * @var bool
     */
    public $searchable = true;

    /**
     * @var bool
     */
    public $sortable = true;

    /**
     * @var bool
     */
    public $pagination = true;

    /**
     * @var bool
     */
    public $pagination_total = true;

    /**
     * @var array
     */
    public $export = array(
        'on' => false,
        'formats' => array(
            'csv' => ',',
            'tsv' => "\t",
            'xml' => false,
            'json' => false
        ),
        'url' => false,
        'type' => false
    );

    /**
     * @var array
     */
    public $reorder = array(
        'on' => false,
        'limit' => 250,
        'orderby' => false,
        'orderby_dir' => 'ASC',
        'sql' => null
    );

    /**
     * @var array
     */
    public $screen_options = array(); // set to 'page' => 'Text'; false hides link
    /**
     * @var array
     */
    public $help = array(); // set to 'page' => 'Text'; 'page' => array('link' => 'yourhelplink'); false hides link

    // data
    /**
     * @var bool
     */
    public $search = false;

    /**
     * @var bool
     */
    public $filters_enhanced = false;

    /**
     * @var array
     */
    public $filters = array();

    /**
     * @var string
     */
    public $view = false;

    /**
     * @var array
     */
    public $views = array();

    /**
     * @var bool
     */
    public $search_across = true;

    /**
     * @var bool
     */
    public $search_across_picks = false;

    /**
     * @var bool
     */
    public $default_none = false;

    /**
     * @var array
     */
    public $where = array(
        'manage' => null,
        /*'edit' => null,
        'duplicate' => null,
        'delete' => null,*/
        'reorder' => null
    );

    /**
     * @var bool
     */
    public $orderby = false;

    /**
     * @var string
     */
    public $orderby_dir = 'DESC';

    /**
     * @var int
     */
    public $limit = 25;

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var int
     */
    public $total_found = 0;

    /**
     * @var array
     */
    public $session = array(
        'search',
        'filters'
    ); // allowed: search, filters, show_per_page, orderby (priority over usermeta)
    /**
     * @var array
     */
    public $user = array(
        'show_per_page',
        'orderby'
    ); // allowed: search, filters, show_per_page, orderby (priority under session)

    // advanced data
    /**
     * @var array
     */
    public $sql = array(
        'table' => null,
        'field_id' => 'id',
        'field_index' => 'name',
        'select' => null,
        'sql' => null
    );

    /**
     * @var array
     */
    public $params = array();

    /**
     * @var bool|array
     */
    public $data = false;

    /**
     * @var bool|array
     */
    public $data_full = false;

    /**
     * @var array
     */
    public $data_keys = array();

    /**
     * @var array
     */
    public $row = array();

    // actions
    /**
     * @var string
     */
    public $action = 'manage';

    /**
     * @var string
     */
    public $action_bulk = false;

    /**
     * @var array
     */
    public $bulk = array();

    /**
     * @var array
     */
    public $action_after = array(
        'add' => 'edit',
        'edit' => 'edit',
        'duplicate' => 'edit'
    ); // set action to 'manage'
    /**
     * @var bool
     */
    public $do = false;

    /**
     * @var array
     */
    public $action_links = array(
		'manage' => null,
        'add' => null,
        'edit' => null,
        'duplicate' => null,
        'view' => null,
        'delete' => null,
        'reorder' => null
    ); // custom links (ex. /my-link/{@id}/

    /**
     * @var array
     */
    public $actions_disabled = array(
        'view',
        'export'
    ); // disable actions

    /**
     * @var array
     */
    public $actions_hidden = array(); // hide actions to not show them but allow them

    /**
     * @var array
     */
    public $actions_custom = array(); // overwrite existing actions or add your own

    /**
     * @var array
     */
    public $actions_bulk = array(); // enabled bulk actions

    /**
     * @var array
     */
    public $restrict = array(
        'manage' => null,
        'edit' => null,
        'duplicate' => null,
        'delete' => null,
        'reorder' => null,
        'author_restrict' => null
    );

    /**
     * @var array
     */
    public $extra = array(
        'total' => null
    );

    /**
     * @var string
     */
    public $style = 'post_type';

    /**
     * @var bool
     */
    public $save = false; // Allow custom save handling for tables that aren't Pod-based

    /**
     * Generate UI for Data Management
     *
     * @param mixed $options Object, Array, or String containing Pod or Options to be used
     * @param bool $deprecated Set to true to support old options array from Pods UI plugin
     *
     * @return \PodsUI
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0
     */
    public function __construct ( $options, $deprecated = false ) {

		$this->_nonce = pods_v( '_wpnonce', 'request' );

        $object = null;

        if ( is_object( $options ) ) {
            $object = $options;
            $options = array();

            if ( isset( $object->ui ) ) {
                $options = (array) $object->ui;

                unset( $object->ui );
            }

            if ( is_object( $object ) && ( 'Pods' == get_class( $object ) || 'Pod' == get_class( $object ) ) )
                $this->pod =& $object;
        }

        if ( !is_array( $options ) ) {
            // @todo need to come back to this and allow for multi-dimensional strings
            // like: option=value&option2=value2&option3=key[val],key2[val2]&option4=this,that,another
            if ( false !== strpos( $options, '=' ) || false !== strpos( $options, '&' ) )
                parse_str( $options, $options );
            else
                $options = array( 'pod' => $options );
        }

        if ( !is_object( $object ) && isset( $options[ 'pod' ] ) ) {
            if ( is_object( $options[ 'pod' ] ) )
                $this->pod = $options[ 'pod' ];
            elseif ( isset( $options[ 'id' ] ) )
                $this->pod = pods( $options[ 'pod' ], $options[ 'id' ] );
            else
                $this->pod = pods( $options[ 'pod' ] );

            unset( $options[ 'pod' ] );
        }
        elseif ( is_object( $object ) )
            $this->pod = $object;

        if ( false !== $deprecated || ( is_object( $this->pod ) && 'Pod' == get_class( $this->pod ) ) )
            $options = $this->setup_deprecated( $options );

        if ( is_object( $this->pod ) && 'Pod' == get_class( $this->pod ) && is_object( $this->pod->_data ) )
            $this->pods_data =& $this->pod->_data;
        elseif ( is_object( $this->pod ) && 'Pods' == get_class( $this->pod ) && is_object( $this->pod->data ) )
            $this->pods_data =& $this->pod->data;
        elseif ( is_object( $this->pod ) )
            $this->pods_data = pods_data( $this->pod->pod );
        elseif ( !is_object( $this->pod ) )
            $this->pods_data = pods_data( $this->pod );

        $options = $this->do_hook( 'pre_init', $options );
        $this->setup( $options );

        if ( is_object( $this->pods_data ) && is_object( $this->pod ) && 0 < $this->id ) {
            if ( $this->id != $this->pods_data->id )
                $this->row = $this->pods_data->fetch( $this->id );
            else
                $this->row = $this->pods_data->row;
        }

        if ( ( !is_object( $this->pod ) || 'Pods' != get_class( $this->pod ) ) && false === $this->sql[ 'table' ] && false === $this->data ) {
            echo $this->error( __( '<strong>Error:</strong> Pods UI needs a Pods object or a Table definition to run from, see the User Guide for more information.', 'pods' ) );

            return false;
        }

        $this->go();
    }

    /**
     * @param $deprecated_options
     *
     * @return array
     */
    public function setup_deprecated ( $deprecated_options ) {
        $options = array();

        if ( isset( $deprecated_options[ 'id' ] ) )
            $options[ 'id' ] = $deprecated_options[ 'id' ];
        if ( isset( $deprecated_options[ 'action' ] ) )
            $options[ 'action' ] = $deprecated_options[ 'action' ];
        if ( isset( $deprecated_options[ 'num' ] ) )
            $options[ 'num' ] = $deprecated_options[ 'num' ];

        if ( isset( $deprecated_options[ 'title' ] ) )
            $options[ 'items' ] = $deprecated_options[ 'title' ];
        if ( isset( $deprecated_options[ 'item' ] ) )
            $options[ 'item' ] = $deprecated_options[ 'item' ];

        if ( isset( $deprecated_options[ 'label' ] ) )
            $options[ 'label' ] = array(
                'add' => $deprecated_options[ 'label' ],
                'edit' => $deprecated_options[ 'label' ],
                'duplicate' => $deprecated_options[ 'label' ]
            );
        if ( isset( $deprecated_options[ 'label_add' ] ) ) {
            if ( isset( $options[ 'label' ] ) )
                $options[ 'label' ][ 'add' ] = $deprecated_options[ 'label_add' ];
            else
                $options[ 'label' ] = array( 'add' => $deprecated_options[ 'label_add' ] );
        }
        if ( isset( $deprecated_options[ 'label_edit' ] ) ) {
            if ( isset( $options[ 'label' ] ) )
                $options[ 'label' ][ 'edit' ] = $deprecated_options[ 'label_edit' ];
            else
                $options[ 'label' ] = array( 'edit' => $deprecated_options[ 'label_edit' ] );
        }
        if ( isset( $deprecated_options[ 'label_duplicate' ] ) ) {
            if ( isset( $options[ 'label' ] ) )
                $options[ 'label' ][ 'duplicate' ] = $deprecated_options[ 'label_duplicate' ];
            else
                $options[ 'label' ] = array( 'duplicate' => $deprecated_options[ 'label_duplicate' ] );
        }

        if ( isset( $deprecated_options[ 'icon' ] ) )
            $options[ 'icon' ] = $deprecated_options[ 'icon' ];

        if ( isset( $deprecated_options[ 'columns' ] ) )
            $options[ 'fields' ] = array( 'manage' => $deprecated_options[ 'columns' ] );
        if ( isset( $deprecated_options[ 'reorder_columns' ] ) ) {
            if ( isset( $options[ 'fields' ] ) )
                $options[ 'fields' ][ 'reorder' ] = $deprecated_options[ 'reorder_columns' ];
            else
                $options[ 'fields' ] = array( 'reorder' => $deprecated_options[ 'reorder_columns' ] );
        }
        if ( isset( $deprecated_options[ 'add_fields' ] ) ) {
            if ( isset( $options[ 'fields' ] ) ) {
                if ( !isset( $options[ 'fields' ][ 'add' ] ) )
                    $options[ 'fields' ][ 'add' ] = $deprecated_options[ 'add_fields' ];
                if ( !isset( $options[ 'fields' ][ 'edit' ] ) )
                    $options[ 'fields' ][ 'edit' ] = $deprecated_options[ 'add_fields' ];
                if ( !isset( $options[ 'fields' ][ 'duplicate' ] ) )
                    $options[ 'fields' ][ 'duplicate' ] = $deprecated_options[ 'add_fields' ];
            }
            else
                $options[ 'fields' ] = array(
                    'add' => $deprecated_options[ 'add_fields' ],
                    'edit' => $deprecated_options[ 'add_fields' ],
                    'duplicate' => $deprecated_options[ 'add_fields' ]
                );
        }
        if ( isset( $deprecated_options[ 'edit_fields' ] ) ) {
            if ( isset( $options[ 'fields' ] ) ) {
                if ( !isset( $options[ 'fields' ][ 'add' ] ) )
                    $options[ 'fields' ][ 'add' ] = $deprecated_options[ 'edit_fields' ];
                if ( !isset( $options[ 'fields' ][ 'edit' ] ) )
                    $options[ 'fields' ][ 'edit' ] = $deprecated_options[ 'edit_fields' ];
                if ( !isset( $options[ 'fields' ][ 'duplicate' ] ) )
                    $options[ 'fields' ][ 'duplicate' ] = $deprecated_options[ 'edit_fields' ];
            }
            else
                $options[ 'fields' ] = array(
                    'add' => $deprecated_options[ 'edit_fields' ],
                    'edit' => $deprecated_options[ 'edit_fields' ],
                    'duplicate' => $deprecated_options[ 'edit_fields' ]
                );
        }
        if ( isset( $deprecated_options[ 'duplicate_fields' ] ) ) {
            if ( isset( $options[ 'fields' ] ) )
                $options[ 'fields' ][ 'duplicate' ] = $deprecated_options[ 'duplicate_fields' ];
            else
                $options[ 'fields' ] = array( 'duplicate' => $deprecated_options[ 'duplicate_fields' ] );
        }

        if ( isset( $deprecated_options[ 'session_filters' ] ) && false === $deprecated_options[ 'session_filters' ] )
            $options[ 'session' ] = false;
        if ( isset( $deprecated_options[ 'user_per_page' ] ) ) {
            if ( isset( $options[ 'user' ] ) && !empty( $options[ 'user' ] ) )
                $options[ 'user' ] = array( 'orderby' );
            else
                $options[ 'user' ] = false;
        }
        if ( isset( $deprecated_options[ 'user_sort' ] ) ) {
            if ( isset( $options[ 'user' ] ) && !empty( $options[ 'user' ] ) )
                $options[ 'user' ] = array( 'show_per_page' );
            else
                $options[ 'user' ] = false;
        }

        if ( isset( $deprecated_options[ 'custom_list' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'manage' ] = $deprecated_options[ 'custom_list' ];
            else
                $options[ 'actions_custom' ] = array( 'manage' => $deprecated_options[ 'custom_list' ] );
        }
        if ( isset( $deprecated_options[ 'custom_reorder' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'reorder' ] = $deprecated_options[ 'custom_reorder' ];
            else
                $options[ 'actions_custom' ] = array( 'reorder' => $deprecated_options[ 'custom_reorder' ] );
        }
        if ( isset( $deprecated_options[ 'custom_add' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'add' ] = $deprecated_options[ 'custom_add' ];
            else
                $options[ 'actions_custom' ] = array( 'add' => $deprecated_options[ 'custom_add' ] );
        }
        if ( isset( $deprecated_options[ 'custom_edit' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'edit' ] = $deprecated_options[ 'custom_edit' ];
            else
                $options[ 'actions_custom' ] = array( 'edit' => $deprecated_options[ 'custom_edit' ] );
        }
        if ( isset( $deprecated_options[ 'custom_duplicate' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'duplicate' ] = $deprecated_options[ 'custom_duplicate' ];
            else
                $options[ 'actions_custom' ] = array( 'duplicate' => $deprecated_options[ 'custom_duplicate' ] );
        }
        if ( isset( $deprecated_options[ 'custom_delete' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'delete' ] = $deprecated_options[ 'custom_delete' ];
            else
                $options[ 'actions_custom' ] = array( 'delete' => $deprecated_options[ 'custom_delete' ] );
        }
        if ( isset( $deprecated_options[ 'custom_save' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'save' ] = $deprecated_options[ 'custom_save' ];
            else
                $options[ 'actions_custom' ] = array( 'save' => $deprecated_options[ 'custom_save' ] );
        }

        if ( isset( $deprecated_options[ 'custom_actions' ] ) )
            $options[ 'actions_custom' ] = $deprecated_options[ 'custom_actions' ];
        if ( isset( $deprecated_options[ 'action_after_save' ] ) )
            $options[ 'action_after' ] = array(
                'add' => $deprecated_options[ 'action_after_save' ],
                'edit' => $deprecated_options[ 'action_after_save' ],
                'duplicate' => $deprecated_options[ 'action_after_save' ]
            );
        if ( isset( $deprecated_options[ 'edit_link' ] ) ) {
            if ( isset( $options[ 'action_links' ] ) )
                $options[ 'action_links' ][ 'edit' ] = $deprecated_options[ 'edit_link' ];
            else
                $options[ 'action_links' ] = array( 'edit' => $deprecated_options[ 'edit_link' ] );
        }
        if ( isset( $deprecated_options[ 'view_link' ] ) ) {
            if ( isset( $options[ 'action_links' ] ) )
                $options[ 'action_links' ][ 'view' ] = $deprecated_options[ 'view_link' ];
            else
                $options[ 'action_links' ] = array( 'view' => $deprecated_options[ 'view_link' ] );
        }
        if ( isset( $deprecated_options[ 'duplicate_link' ] ) ) {
            if ( isset( $options[ 'action_links' ] ) )
                $options[ 'action_links' ][ 'duplicate' ] = $deprecated_options[ 'duplicate_link' ];
            else
                $options[ 'action_links' ] = array( 'duplicate' => $deprecated_options[ 'duplicate_link' ] );
        }

        if ( isset( $deprecated_options[ 'reorder' ] ) )
            $options[ 'reorder' ] = array(
                'on' => $deprecated_options[ 'reorder' ],
                'orderby' => $deprecated_options[ 'reorder' ]
            );
        if ( isset( $deprecated_options[ 'reorder_sort' ] ) && isset( $options[ 'reorder' ] ) )
            $options[ 'reorder' ][ 'orderby' ] = $deprecated_options[ 'reorder_sort' ];
        if ( isset( $deprecated_options[ 'reorder_limit' ] ) && isset( $options[ 'reorder' ] ) )
            $options[ 'reorder' ][ 'limit' ] = $deprecated_options[ 'reorder_limit' ];
        if ( isset( $deprecated_options[ 'reorder_sql' ] ) && isset( $options[ 'reorder' ] ) )
            $options[ 'reorder' ][ 'sql' ] = $deprecated_options[ 'reorder_sql' ];

        if ( isset( $deprecated_options[ 'sort' ] ) )
            $options[ 'orderby' ] = $deprecated_options[ 'sort' ];
        if ( isset( $deprecated_options[ 'sortable' ] ) )
            $options[ 'sortable' ] = $deprecated_options[ 'sortable' ];
        if ( isset( $deprecated_options[ 'limit' ] ) )
            $options[ 'limit' ] = $deprecated_options[ 'limit' ];

        if ( isset( $deprecated_options[ 'where' ] ) ) {
            if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'manage' ] = $deprecated_options[ 'where' ];
            else
                $options[ 'where' ] = array( 'manage' => $deprecated_options[ 'where' ] );
        }
        if ( isset( $deprecated_options[ 'edit_where' ] ) ) {
            /*if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'edit' ] = $deprecated_options[ 'edit_where' ];
            else
                $options[ 'where' ] = array( 'edit' => $deprecated_options[ 'edit_where' ] );*/

            if ( isset( $options[ 'restrict' ] ) )
                $options[ 'restrict' ][ 'edit' ] = (array) $deprecated_options[ 'edit_where' ];
            else
                $options[ 'restrict' ] = array( 'edit' => (array) $deprecated_options[ 'edit_where' ] );
        }
        if ( isset( $deprecated_options[ 'duplicate_where' ] ) ) {
            /*if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'duplicate' ] = $deprecated_options[ 'duplicate_where' ];
            else
                $options[ 'where' ] = array( 'duplicate' => $deprecated_options[ 'duplicate_where' ] );*/

            if ( isset( $options[ 'restrict' ] ) )
                $options[ 'restrict' ][ 'duplicate' ] = (array) $deprecated_options[ 'duplicate_where' ];
            else
                $options[ 'restrict' ] = array( 'duplicate' => (array) $deprecated_options[ 'duplicate_where' ] );
        }
        if ( isset( $deprecated_options[ 'delete_where' ] ) ) {
            /*if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'delete' ] = $deprecated_options[ 'delete_where' ];
            else
                $options[ 'where' ] = array( 'delete' => $deprecated_options[ 'delete_where' ] );*/

            if ( isset( $options[ 'restrict' ] ) )
                $options[ 'restrict' ][ 'delete' ] = (array) $deprecated_options[ 'delete_where' ];
            else
                $options[ 'restrict' ] = array( 'delete' => (array) $deprecated_options[ 'delete_where' ] );
        }
        if ( isset( $deprecated_options[ 'reorder_where' ] ) ) {
            if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'reorder' ] = $deprecated_options[ 'reorder_where' ];
            else
                $options[ 'where' ] = array( 'reorder' => $deprecated_options[ 'reorder_where' ] );
        }

        if ( isset( $deprecated_options[ 'sql' ] ) )
            $options[ 'sql' ] = array( 'sql' => $deprecated_options[ 'sql' ] );

        if ( isset( $deprecated_options[ 'search' ] ) )
            $options[ 'searchable' ] = $deprecated_options[ 'search' ];
        if ( isset( $deprecated_options[ 'search_across' ] ) )
            $options[ 'search_across' ] = $deprecated_options[ 'search_across' ];
        if ( isset( $deprecated_options[ 'search_across_picks' ] ) )
            $options[ 'search_across_picks' ] = $deprecated_options[ 'search_across_picks' ];
        if ( isset( $deprecated_options[ 'filters' ] ) )
            $options[ 'filters' ] = $deprecated_options[ 'filters' ];
        if ( isset( $deprecated_options[ 'custom_filters' ] ) ) {
            if ( is_callable( $deprecated_options[ 'custom_filters' ] ) )
                add_filter( 'pods_ui_filters', $deprecated_options[ 'custom_filters' ] );
            else {
                global $pods_ui_custom_filters;
                $pods_ui_custom_filters = $deprecated_options[ 'custom_filters' ];
                add_filter( 'pods_ui_filters', array( $this, 'deprecated_filters' ) );
            }
        }

        if ( isset( $deprecated_options[ 'disable_actions' ] ) )
            $options[ 'actions_disabled' ] = $deprecated_options[ 'disable_actions' ];
        if ( isset( $deprecated_options[ 'hide_actions' ] ) )
            $options[ 'actions_hidden' ] = $deprecated_options[ 'hide_actions' ];

        if ( isset( $deprecated_options[ 'wpcss' ] ) )
            $options[ 'wpcss' ] = $deprecated_options[ 'wpcss' ];

        $remaining_options = array_diff_assoc( $options, $deprecated_options );

        foreach ( $remaining_options as $option => $value ) {
            if ( isset( $deprecated_options[ $option ] ) && isset( $this->$option ) )
                $options[ $option ] = $value;
        }

        return $options;
    }

    /**
     *
     */
    public function deprecated_filters () {
        global $pods_ui_custom_filters;
        echo $pods_ui_custom_filters;
    }

    /**
     * @param $options
     *
     * @return array|bool|mixed|null|PodsArray
     */
    public function setup ( $options ) {
        $options = pods_array( $options );

        $options->validate( 'num', '', 'absint' );

        if ( empty( $options->num ) )
            $options->num = '';

        $options->validate( 'id', pods_var( 'id' . $options->num, 'get', $this->id ) );

        $options->validate( 'do', pods_var( 'do' . $options->num, 'get', $this->do ), 'in_array', array(
            'save',
            'create'
        ) );

        $options->validate( 'excluded', self::$excluded, 'array_merge' );

        $options->validate( 'action', pods_var( 'action' . $options->num, 'get', $this->action, null, true ), 'in_array', $this->actions );
        $options->validate( 'actions_bulk', $this->actions_bulk, 'array_merge' );
        $options->validate( 'action_bulk', pods_var( 'action_bulk' . $options->num, 'get', $this->action_bulk, null, true ), 'isset', $this->actions_bulk );

        $bulk = pods_var( 'action_bulk_ids' . $options->num, 'get', array(), null, true );

        if ( !empty( $bulk ) )
            $bulk = (array) pods_var( 'action_bulk_ids' . $options->num, 'get', array(), null, true );
        else
            $bulk = array();

        $options->validate( 'bulk', $bulk, 'array_merge', $this->bulk );

        $options->validate( 'views', $this->views, 'array' );
        $options->validate( 'view', pods_var( 'view' . $options->num, 'get', $this->view, null, true ), 'isset', $this->views );

        $options->validate( 'searchable', $this->searchable, 'boolean' );
        $options->validate( 'search', pods_var( 'search' . $options->num, 'get' ) );
        $options->validate( 'search_across', $this->search_across, 'boolean' );
        $options->validate( 'search_across_picks', $this->search_across_picks, 'boolean' );
        $options->validate( 'filters', $this->filters, 'array' );
        $options->validate( 'filters_enhanced', $this->filters_enhanced, 'boolean' );
        $options->validate( 'where', $this->where, 'array_merge' );

        $options->validate( 'pagination', $this->pagination, 'boolean' );
        $options->validate( 'page', pods_var( 'pg' . $options->num, 'get', $this->page ), 'absint' );
        $options->validate( 'limit', pods_var( 'limit' . $options->num, 'get', $this->limit ), 'int' );

        if ( isset( $this->pods_data ) && is_object( $this->pods_data ) ) {
            $this->sql = array(
                'table' => $this->pods_data->table,
                'field_id' => $this->pods_data->field_id,
                'field_index' => $this->pods_data->field_index
            );
        }
        $options->validate( 'sql', $this->sql, 'array_merge' );

        $options->validate( 'orderby_dir', strtoupper( pods_v( 'orderby_dir' . $options[ 'num' ], 'get', $this->orderby_dir, true ) ), 'in_array', array( 'ASC', 'DESC' ) );

	    $orderby = $this->orderby;

	    // Enforce strict DB column name usage
	    if ( ! empty( $_GET[ 'orderby' . $options->num ] ) ) {
		    $orderby = pods_clean_name( $_GET[ 'orderby' . $options->num ], true, false );
	    }

        if ( !empty( $orderby ) ) {
            $orderby = array(
                'default' => $orderby
            );
        }
        else
            $orderby = array();

        $options->validate( 'orderby', $orderby, 'array_merge' );
        $options->validate( 'sortable', $this->sortable, 'boolean' );

        $options->validate( 'params', $this->params, 'array' );

        $options->validate( 'restrict', $this->restrict, 'array_merge' );

        // handle author restrictions
        if ( !empty( $options[ 'restrict' ][ 'author_restrict' ] ) ) {
            $restrict = $options[ 'restrict' ];

            if ( !is_array( $restrict[ 'author_restrict' ] ) )
                $restrict[ 'author_restrict' ] = array( $restrict[ 'author_restrict' ] => get_current_user_id() );

            if ( null === $restrict[ 'edit' ] )
                $restrict[ 'edit' ] = $restrict[ 'author_restrict' ];

            $options->restrict = $restrict;
        }

        if ( null !== $options[ 'restrict' ][ 'edit' ] ) {
            $restrict = $options[ 'restrict' ];

            if ( null === $restrict[ 'duplicate' ] )
                $restrict[ 'duplicate' ] = $restrict[ 'edit' ];

            if ( null === $restrict[ 'delete' ] )
                $restrict[ 'delete' ] = $restrict[ 'edit' ];

            if ( null === $restrict[ 'manage' ] )
                $restrict[ 'manage' ] = $restrict[ 'edit' ];

            if ( null === $restrict[ 'reorder' ] )
                $restrict[ 'reorder' ] = $restrict[ 'edit' ];

            $options->restrict = $restrict;
        }

        $item = __( 'Item', 'pods' );
        $items = __( 'Items', 'pods' );

        if ( is_object( $this->pod ) ) {
            $item = pods_var_raw( 'label_singular', $this->pod->pod_data[ 'options' ], pods_var_raw( 'label', $this->pod->pod_data, $item, null, true ), null, true );
            $items = pods_var_raw( 'label', $this->pod->pod_data, $items, null, true );
        }

        $options->validate( 'item', $item );
        $options->validate( 'items', $items );

        $options->validate( 'heading', array(
            'manage' => __( 'Manage', 'pods' ),
            'add' => __( 'Add New', 'pods' ),
            'edit' => __( 'Edit', 'pods' ),
            'duplicate' => __( 'Duplicate', 'pods' ),
            'view' => __( 'View', 'pods' ),
            'reorder' => __( 'Reorder', 'pods' ),
            'search' => __( 'Search', 'pods' ),
            'views' => __( 'View', 'pods' )
        ), 'array_merge' );

        $options->validate( 'header', array(
            'manage' => sprintf( __( 'Manage %s', 'pods' ), $options->items ),
            'add' => sprintf( __( 'Add New %s', 'pods' ), $options->item ),
            'edit' => sprintf( __( 'Edit %s', 'pods' ), $options->item ),
            'duplicate' => sprintf( __( 'Duplicate %s', 'pods' ), $options->item ),
            'view' => sprintf( __( 'View %s', 'pods' ), $options->item ),
            'reorder' => sprintf( __( 'Reorder %s', 'pods' ), $options->items ),
            'search' => sprintf( __( 'Search %s', 'pods' ), $options->items )
        ), 'array_merge' );

        $options->validate( 'label', array(
            'add' => sprintf( __( 'Save New %s', 'pods' ), $options->item ),
            'add_new' => __( 'Add New', 'pods' ),
            'edit' => sprintf( __( 'Save %s', 'pods' ), $options->item ),
            'duplicate' => sprintf( __( 'Save New %s', 'pods' ), $options->item ),
            'delete' => sprintf( __( 'Delete this %s', 'pods' ), $options->item ),
            'view' => sprintf( __( 'View %s', 'pods' ), $options->item ),
            'reorder' => sprintf( __( 'Reorder %s', 'pods' ), $options->items )
        ), 'array_merge' );

        $options->validate( 'fields', array(
            'manage' => array(
                $options->sql[ 'field_index' ] => array( 'label' => __( 'Name', 'pods' ) )
            )
        ), 'array' );

        $options->validate( 'export', $this->export, 'array_merge' );
        $options->validate( 'reorder', $this->reorder, 'array_merge' );
        $options->validate( 'screen_options', $this->screen_options, 'array_merge' );

        $options->validate( 'session', $this->session, 'in_array', array(
            'search',
            'filters',
            'show_per_page',
            'orderby'
        ) );
        $options->validate( 'user', $this->user, 'in_array', array(
            'search',
            'filters',
            'show_per_page',
            'orderby'
        ) );

        $options->validate( 'action_after', $this->action_after, 'array_merge' );
        $options->validate( 'action_links', $this->action_links, 'array_merge' );
        $options->validate( 'actions_disabled', $this->actions_disabled, 'array' );
        $options->validate( 'actions_hidden', $this->actions_hidden, 'array_merge' );
        $options->validate( 'actions_custom', $this->actions_custom, 'array_merge' );

		if ( !empty( $options->actions_disabled ) ) {
			if ( !empty( $options->actions_bulk ) ) {
				$actions_bulk = $options->actions_bulk;

				foreach ( $actions_bulk as $action => $action_opt ) {
					if ( in_array( $action, $options->actions_disabled ) ) {
						unset( $actions_bulk[ $action ] );
					}
				}

				$options->actions_bulk = $actions_bulk;
			}

			if ( !empty( $options->actions_custom ) ) {
				$actions_custom = $options->actions_custom;

				foreach ( $actions_custom as $action => $action_opt ) {
					if ( in_array( $action, $options->actions_disabled ) ) {
						unset( $actions_custom[ $action ] );
					}
				}

				$options->actions_custom = $actions_custom;
			}
		}

        $options->validate( 'extra', $this->extra, 'array_merge' );

        $options->validate( 'style', $this->style );
        $options->validate( 'icon', $this->icon );
        $options->validate( 'css', $this->css );
        $options->validate( 'wpcss', $this->wpcss, 'boolean' );

        if ( true === $options[ 'wpcss' ] ) {
            global $user_ID;
            wp_get_current_user();

            $color = get_user_meta( $user_ID, 'admin_color', true );
            if ( strlen( $color ) < 1 )
                $color = 'fresh';

            $this->wpcss = "colors-{$color}";
        }

        $options = $options->dump();

        if ( is_object( $this->pod ) )
            $options = $this->do_hook( $this->pod->pod . '_setup_options', $options );

        $options = $this->do_hook( 'setup_options', $options );

        if ( false !== $options && !empty( $options ) ) {
            foreach ( $options as $option => $value ) {
                if ( isset( $this->{$option} ) )
                    $this->{$option} = $value;
                else
                    $this->x[ $option ] = $value;
            }
        }

        $unique_identifier = pods_var( 'page', 'get' ); // wp-admin page
        if ( is_object( $this->pod ) && isset( $this->pod->pod ) )
            $unique_identifier = '_' . $this->pod->pod;
        elseif ( 0 < strlen( $this->sql[ 'table' ] ) )
            $unique_identifier = '_' . $this->sql[ 'table' ];

        $unique_identifier .= '_' . $this->page;
        if ( 0 < strlen( $this->num ) )
            $unique_identifier .= '_' . $this->num;

        $this->unique_identifier = 'pods_ui_' . md5( $unique_identifier );

        $this->setup_fields();

        return $options;
    }

    /**
     * @param null $fields
     * @param string $which
     *
     * @return array|bool|mixed|null
     */
    public function setup_fields ( $fields = null, $which = 'fields' ) {
        $init = false;
        if ( null === $fields ) {
            if ( isset( $this->fields[ $which ] ) )
                $fields = (array) $this->fields[ $which ];
            elseif ( isset( $this->fields[ 'manage' ] ) )
                $fields = (array) $this->fields[ 'manage' ];
            else
                $fields = array();
            if ( 'fields' == $which )
                $init = true;
        }
        if ( !empty( $fields ) ) {
            // Available Attributes
            // type = field type
            // type = date (data validation as date)
            // type = time (data validation as time)
            // type = datetime (data validation as datetime)
            // date_touch = use current timestamp when saving (even if readonly, if type is date-related)
            // date_touch_on_create = use current timestamp when saving ONLY on create (even if readonly, if type is date-related)
            // date_ongoing = use this additional field to search between as if the first is the "start" and the date_ongoing is the "end" for filter
            // type = text / other (single line text box)
            // type = desc (textarea)
            // type = number (data validation as int float)
            // type = decimal (data validation as decimal)
            // type = password (single line password box)
            // type = bool (checkbox)
            // type = related (select box)
            // related = table to relate to (if type=related) OR custom array of (key => label or comma separated values) items
            // related_field = field name on table to show (if type=related) - default "name"
            // related_multiple = true (ability to select multiple values if type=related)
            // related_sql = custom where / order by SQL (if type=related)
            // readonly = true (shows as text)
            // display = false (doesn't show on form, but can be saved)
            // search = this field is searchable
            // filter = this field will be independently searchable (by default, searchable fields are searched by the primary search box)
            // comments = comments to show for field
            // comments_top = true (shows comments above field instead of below)
            // real_name = the real name of the field (if using an alias for 'name')
            // group_related = true (uses HAVING instead of WHERE for filtering field)
            $new_fields = array();
            $filterable = false;
            if ( empty( $this->filters ) && ( empty( $this->fields[ 'search' ] ) || 'search' == $which ) && false !== $this->searchable ) {
                $filterable = true;
                $this->filters = array();
            }

            foreach ( $fields as $field => $attributes ) {
                if ( !is_array( $attributes ) ) {
                    if ( is_int( $field ) ) {
                        $field = $attributes;
                        $attributes = array();
                    }
                    else
                        $attributes = array( 'label' => $attributes );
                }

                if ( !isset( $attributes[ 'real_name' ] ) )
                    $attributes[ 'real_name' ] = pods_var( 'name', $attributes, $field );

                if ( is_object( $this->pod ) && isset( $this->pod->fields ) && isset( $this->pod->fields[ $attributes[ 'real_name' ] ] ) )
                    $attributes = array_merge( $this->pod->fields[ $attributes[ 'real_name' ] ], $attributes );

                if ( !isset( $attributes[ 'options' ] ) )
                    $attributes[ 'options' ] = array();
                if ( !isset( $attributes[ 'id' ] ) )
                    $attributes[ 'id' ] = '';
                if ( !isset( $attributes[ 'label' ] ) )
                    $attributes[ 'label' ] = ucwords( str_replace( '_', ' ', $field ) );
                if ( !isset( $attributes[ 'type' ] ) )
                    $attributes[ 'type' ] = 'text';
                if ( !isset( $attributes[ 'options' ][ 'date_format_type' ] ) )
                    $attributes[ 'options' ][ 'date_format_type' ] = 'date';
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related' ] ) )
                    $attributes[ 'related' ] = false;
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_id' ] ) )
                    $attributes[ 'related_id' ] = 'id';
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_field' ] ) )
                    $attributes[ 'related_field' ] = 'name';
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_multiple' ] ) )
                    $attributes[ 'related_multiple' ] = false;
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_sql' ] ) )
                    $attributes[ 'related_sql' ] = false;
                if ( 'related' == $attributes[ 'type' ] && ( is_array( $attributes[ 'related' ] ) || strpos( $attributes[ 'related' ], ',' ) ) ) {
                    if ( !is_array( $attributes[ 'related' ] ) ) {
                        $attributes[ 'related' ] = @explode( ',', $attributes[ 'related' ] );
                        $related_items = array();
                        foreach ( $attributes[ 'related' ] as $key => $label ) {
                            if ( is_numeric( $key ) ) {
                                $key = $label;
                                $label = ucwords( str_replace( '_', ' ', $label ) );
                            }
                            $related_items[ $key ] = $label;
                        }
                        $attributes[ 'related' ] = $related_items;
                    }
                    if ( empty( $attributes[ 'related' ] ) )
                        $attributes[ 'related' ] = false;
                }
                if ( !isset( $attributes[ 'readonly' ] ) )
                    $attributes[ 'readonly' ] = false;
                if ( !isset( $attributes[ 'date_touch' ] ) || 'date' != $attributes[ 'type' ] )
                    $attributes[ 'date_touch' ] = false;
                if ( !isset( $attributes[ 'date_touch_on_create' ] ) || 'date' != $attributes[ 'type' ] )
                    $attributes[ 'date_touch_on_create' ] = false;
                if ( !isset( $attributes[ 'display' ] ) )
                    $attributes[ 'display' ] = true;
                if ( !isset( $attributes[ 'hidden' ] ) )
                    $attributes[ 'hidden' ] = false;
                if ( !isset( $attributes[ 'sortable' ] ) || false === $this->sortable )
                    $attributes[ 'sortable' ] = $this->sortable;
                if ( !isset( $attributes[ 'options' ][ 'search' ] ) || false === $this->searchable )
                    $attributes[ 'options' ][ 'search' ] = $this->searchable;
                if ( !isset( $attributes[ 'options' ][ 'filter' ] ) || false === $this->searchable )
                    $attributes[ 'options' ][ 'filter' ] = $this->searchable;
                /*if ( false !== $attributes[ 'options' ][ 'filter' ] && false !== $filterable )
                    $this->filters[] = $field;*/
                if ( false === $attributes[ 'options' ][ 'filter' ] || !isset( $attributes[ 'filter_label' ] ) || !in_array( $field, $this->filters ) )
                    $attributes[ 'filter_label' ] = $attributes[ 'label' ];
                if ( false === $attributes[ 'options' ][ 'filter' ] || !isset( $attributes[ 'filter_default' ] ) || !in_array( $field, $this->filters ) )
                    $attributes[ 'filter_default' ] = false;
                if ( false === $attributes[ 'options' ][ 'filter' ] || !isset( $attributes[ 'date_ongoing' ] ) || 'date' != $attributes[ 'type' ] || !in_array( $field, $this->filters ) )
                    $attributes[ 'date_ongoing' ] = false;
                if ( false === $attributes[ 'options' ][ 'filter' ] || !isset( $attributes[ 'date_ongoing' ] ) || 'date' != $attributes[ 'type' ] || !isset( $attributes[ 'date_ongoing_default' ] ) || !in_array( $field, $this->filters ) )
                    $attributes[ 'date_ongoing_default' ] = false;
                if ( !isset( $attributes[ 'export' ] ) )
                    $attributes[ 'export' ] = true;
                if ( !isset( $attributes[ 'group_related' ] ) )
                    $attributes[ 'group_related' ] = false;
                if ( !isset( $attributes[ 'comments' ] ) )
                    $attributes[ 'comments' ] = '';
                if ( !isset( $attributes[ 'comments_top' ] ) )
                    $attributes[ 'comments_top' ] = false;
                if ( !isset( $attributes[ 'custom_view' ] ) )
                    $attributes[ 'custom_view' ] = false;
                if ( !isset( $attributes[ 'custom_input' ] ) )
                    $attributes[ 'custom_input' ] = false;
                if ( isset( $attributes[ 'display_helper' ] ) ) // pods ui backward compatibility
                    $attributes[ 'custom_display' ] = $attributes[ 'display_helper' ];
                if ( !isset( $attributes[ 'custom_display' ] ) )
                    $attributes[ 'custom_display' ] = false;
                if ( !isset( $attributes[ 'custom_relate' ] ) )
                    $attributes[ 'custom_relate' ] = false;
                if ( !isset( $attributes[ 'custom_form_display' ] ) )
                    $attributes[ 'custom_form_display' ] = false;
                if ( !isset( $attributes[ 'css_values' ] ) )
                    $attributes[ 'css_values' ] = true;
                if ( 'search_columns' == $which && !$attributes[ 'options' ][ 'search' ] )
                    continue;

                $attributes = PodsForm::field_setup( $attributes, null, $attributes[ 'type' ] );

                $new_fields[ $field ] = $attributes;
            }
            $fields = $new_fields;
        }
        if ( false !== $init ) {
            if ( 'fields' != $which && !empty( $this->fields ) )
                $this->fields = $this->setup_fields( $this->fields, 'fields' );
            else
                $this->fields[ 'manage' ] = $fields;

            if ( !in_array( 'add', $this->actions_disabled ) || !in_array( 'edit', $this->actions_disabled ) || !in_array( 'duplicate', $this->actions_disabled ) ) {
                if ( 'form' != $which && isset( $this->fields[ 'form' ] ) && is_array( $this->fields[ 'form' ] ) )
                    $this->fields[ 'form' ] = $this->setup_fields( $this->fields[ 'form' ], 'form' );
                else
                    $this->fields[ 'form' ] = $fields;

                if ( !in_array( 'add', $this->actions_disabled ) ) {
                    if ( 'add' != $which && isset( $this->fields[ 'add' ] ) && is_array( $this->fields[ 'add' ] ) )
                        $this->fields[ 'add' ] = $this->setup_fields( $this->fields[ 'add' ], 'add' );
                }
                if ( !in_array( 'edit', $this->actions_disabled ) ) {
                    if ( 'edit' != $which && isset( $this->fields[ 'edit' ] ) && is_array( $this->fields[ 'edit' ] ) )
                        $this->fields[ 'edit' ] = $this->setup_fields( $this->fields[ 'edit' ], 'edit' );
                }
                if ( !in_array( 'duplicate', $this->actions_disabled ) ) {
                    if ( 'duplicate' != $which && isset( $this->fields[ 'duplicate' ] ) && is_array( $this->fields[ 'duplicate' ] ) )
                        $this->fields[ 'duplicate' ] = $this->setup_fields( $this->fields[ 'duplicate' ], 'duplicate' );
                }
            }

            if ( false !== $this->searchable ) {
                if ( 'search' != $which && isset( $this->fields[ 'search' ] ) &&!empty( $this->fields[ 'search' ] ) )
                    $this->fields[ 'search' ] = $this->setup_fields( $this->fields[ 'search' ], 'search' );
                else
                    $this->fields[ 'search' ] = $fields;
            }
            else
                $this->fields[ 'search' ] = false;

            if ( !in_array( 'export', $this->actions_disabled ) ) {
                if ( 'export' != $which && isset( $this->fields[ 'export' ] ) &&!empty( $this->fields[ 'export' ] ) )
                    $this->fields[ 'export' ] = $this->setup_fields( $this->fields[ 'export' ], 'export' );
            }

            if ( !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
                if ( 'reorder' != $which && isset( $this->fields[ 'reorder' ] ) &&!empty( $this->fields[ 'reorder' ] ) )
                    $this->fields[ 'reorder' ] = $this->setup_fields( $this->fields[ 'reorder' ], 'reorder' );
                else
                    $this->fields[ 'reorder' ] = $fields;
            }
        }
        return $this->do_hook( 'setup_fields', $fields, $which, $init );
    }

    /**
     * @param $msg
     * @param bool $error
     */
    public function message ( $msg, $error = false ) {
        $msg = $this->do_hook( ( $error ) ? 'error' : 'message', $msg );
        ?>
    <div id="message" class="<?php echo esc_attr( ( $error ) ? 'error' : 'updated' ); ?> fade"><p><?php echo $msg; ?></p></div>
    <?php
    }

    /**
     * @param $msg
     *
     * @return bool
     */
    public function error ( $msg ) {
        $this->message( $msg, true );

        return false;
    }

    /**
     * @return mixed
     */
    public function go () {
        $this->do_hook( 'go' );
        $_GET = pods_unsanitize( $_GET ); // fix wp sanitization
        $_POST = pods_unsanitize( $_POST ); // fix wp sanitization

        if ( false !== $this->css ) {
            ?>
        <link type="text/css" rel="stylesheet" href="<?php echo esc_url( $this->css ); ?>" />
        <?php
        }
        if ( false !== $this->wpcss ) {
            $stylesheets = array( 'global', 'wp-admin', $this->wpcss );
            foreach ( $stylesheets as $style ) {
                if ( !wp_style_is( $style, 'queue' ) && !wp_style_is( $style, 'to_do' ) && !wp_style_is( $style, 'done' ) )
                    wp_enqueue_style( $style );
            }
        }

        $this->ui_page = array( $this->action );
        if ( 'add' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) {
            $this->ui_page[] = 'form';
            if ( 'create' == $this->do && $this->save && !in_array( $this->do, $this->actions_disabled ) && !empty( $_POST ) ) {
                $this->ui_page[] = $this->do;
                $this->save( true );
                $this->manage();
            }
            else
                $this->add();
        }
        elseif ( ( 'edit' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) || ( 'duplicate' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) ) {
            $this->ui_page[] = 'form';
            if ( 'save' == $this->do && $this->save && !empty( $_POST ) )
                $this->save();
            $this->edit( ( 'duplicate' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) ? true : false );
        }
        elseif ( 'delete' == $this->action && !in_array( $this->action, $this->actions_disabled ) && false !== wp_verify_nonce( $this->_nonce, 'pods-ui-action-delete' ) ) {
            $this->delete( $this->id );
            $this->manage();
        }
        elseif ( 'reorder' == $this->action && !in_array( $this->action, $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
            if ( 'save' == $this->do ) {
                $this->ui_page[] = $this->do;
                $this->reorder();
            }
            $this->manage( true );
        }
        elseif ( 'save' == $this->do && $this->save && !in_array( $this->do, $this->actions_disabled ) && !empty( $_POST ) ) {
            $this->ui_page[] = $this->do;
            $this->save();
            $this->manage();
        }
        elseif ( 'create' == $this->do && $this->save && !in_array( $this->do, $this->actions_disabled ) && !empty( $_POST ) ) {
            $this->ui_page[] = $this->do;
            $this->save( true );
            $this->manage();
        }
        elseif ( 'view' == $this->action && !in_array( $this->action, $this->actions_disabled ) )
            $this->view();
		else {
			if ( isset( $this->actions_custom[ $this->action ] ) ) {
				$more_args = false;

				if ( is_array( $this->actions_custom[ $this->action ] ) && isset( $this->actions_custom[ $this->action ][ 'more_args' ] ) ) {
					$more_args = $this->actions_custom[ $this->action ][ 'more_args' ];
				}

				$row = $this->row;

				if ( empty( $row ) ) {
					$row = $this->get_row();
				}

				if ( $this->restricted( $this->action, $row ) || ( $more_args && ! empty( $more_args[ 'nonce' ] ) && false === wp_verify_nonce( $this->_nonce, 'pods-ui-action-' . $this->action ) ) ) {
					return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );
				}
				elseif ( $more_args && false !== $this->callback_action( true, $this->action, $this->id, $row ) ) {
					return null;
				}
				elseif ( false !== $this->callback_action( true, $this->action, $this->id ) ) {
					return null;
				}
			}

			if ( !in_array( 'manage', $this->actions_disabled ) ) {
				// handle session / user persistent settings for show_per_page, orderby, search, and filters
				$methods = array( 'session', 'user' );

				// @todo fix this to set ($this) AND save (setting)
				foreach ( $methods as $method ) {
					foreach ( $this->$method as $setting ) {
						if ( 'show_per_page' == $setting )
							$value = $this->limit;
						elseif ( 'orderby' == $setting ) {
							if ( empty( $this->orderby ) )
								$value = '';
							// save this if we have a default index set
							elseif ( isset( $this->orderby[ 'default' ] ) ) {
								$value = $this->orderby[ 'default' ] . ' '
										 . ( false === strpos( $this->orderby[ 'default' ], ' ' ) ? $this->orderby_dir : '' );
							}
							else
								$value = '';
						}
						else
							$value = $this->$setting;

						pods_v_set( $value, $setting, $method );
					}
				}

				$this->manage();
			}
		}
    }

    /**
     * @return mixed
     */
    public function add () {
		if ( false !== $this->callback_action( 'add' ) ) {
			return null;
		}
        ?>
    <div class="wrap pods-ui">
        <div id="icon-edit-pages" class="icon32"<?php if ( false !== $this->icon ) { ?> style="background-position:0 0;background-size:100%;background-image:url(<?php echo esc_url( $this->icon ); ?>);"<?php } ?>><br /></div>
        <h2>
            <?php
            echo $this->header[ 'add' ];

			$link = pods_query_arg( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), self::$allowed, $this->exclusion() );

			if ( !empty( $this->action_links[ 'manage' ] ) ) {
				$link = $this->action_links[ 'manage' ];
			}
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading[ 'manage' ] ); ?></a>
        </h2>

        <?php $this->form( true ); ?>
    </div>
    <?php
    }

    /**
     * @param bool $duplicate
     *
     * @return mixed
     */
    public function edit ( $duplicate = false ) {
        if ( in_array( 'duplicate', $this->actions_disabled ) )
            $duplicate = false;

        if ( empty( $this->row ) )
            $this->get_row();

		if ( $duplicate && false !== $this->callback_action( 'duplicate' ) ) {
			return null;
		}
		elseif ( false !== $this->callback_action( 'edit', $duplicate ) ) {
			return null;
		}
        ?>
    <div class="wrap pods-ui">
        <div id="icon-edit-pages" class="icon32"<?php if ( false !== $this->icon ) { ?> style="background-position:0 0;background-size:100%;background-image:url(<?php echo esc_url( $this->icon ); ?>);"<?php } ?>><br /></div>
        <h2>
            <?php
            echo $this->do_template( $duplicate ? $this->header[ 'duplicate' ] : $this->header[ 'edit' ] );

            if ( !in_array( 'add', $this->actions_disabled ) && !in_array( 'add', $this->actions_hidden ) ) {
                $link = pods_query_arg( array( 'action' . $this->num => 'add', 'id' . $this->num => '', 'do' . $this->num = '' ), self::$allowed, $this->exclusion() );

                if ( !empty( $this->action_links[ 'add' ] ) )
                    $link = $this->action_links[ 'add' ];
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo $this->heading[ 'add' ]; ?></a>
                <?php
            }
            elseif ( !in_array( 'manage', $this->actions_disabled ) && !in_array( 'manage', $this->actions_hidden ) ) {
                $link = pods_query_arg( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), self::$allowed, $this->exclusion() );

                if ( !empty( $this->action_links[ 'manage' ] ) )
                    $link = $this->action_links[ 'manage' ];
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading[ 'manage' ] ); ?></a>
                <?php
            }
            ?>
        </h2>

        <?php $this->form( false, $duplicate ); ?>
    </div>
    <?php
    }

    /**
     * @param bool $create
     * @param bool $duplicate
     *
     * @return bool|mixed
     */
    public function form ( $create = false, $duplicate = false ) {
        if ( in_array( 'duplicate', $this->actions_disabled ) )
            $duplicate = false;

		if ( false !== $this->callback( 'form' ) ) {
			return null;
		}

        $label = $this->label[ 'add' ];
        $id = null;
        $vars = array(
            'action' . $this->num => $this->action_after[ 'add' ],
            'do' . $this->num => 'create',
            'id' . $this->num => 'X_ID_X'
        );

        $alt_vars = $vars;
        $alt_vars[ 'action' ] = 'manage';
        unset( $alt_vars[ 'id' ] );

        if ( false === $create ) {
            if ( empty( $this->row ) )
                $this->get_row();

            if ( empty( $this->row ) && ( !is_object( $this->pod ) || 'settings' != $this->pod->pod_data[ 'type' ] ) )
                return $this->error( sprintf( __( '<strong>Error:</strong> %s not found.', 'pods' ), $this->item ) );

            if ( $this->restricted( $this->action, $this->row ) )
                return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );

            $label = $this->do_template( $this->label[ 'edit' ] );
            $id = $this->row[ $this->sql[ 'field_id' ] ];
            $vars = array(
                'action' . $this->num => $this->action_after[ 'edit' ],
                'do' . $this->num => 'save',
                'id' . $this->num => $id
            );

            $alt_vars = $vars;
            $alt_vars[ 'action' ] = 'manage';
            unset( $alt_vars[ 'id' ] );

            if ( $duplicate ) {
                $label = $this->do_template( $this->label[ 'duplicate' ] );
                $id = null;
                $vars = array(
                    'action' . $this->num => $this->action_after[ 'duplicate' ],
                    'do' . $this->num => 'create',
                    'id' . $this->num => 'X_ID_X'
                );

                $alt_vars = $vars;
                $alt_vars[ 'action' ] = 'manage';
                unset( $alt_vars[ 'id' ] );
            }
        }

		$fields = array();

        if ( isset( $this->fields[ $this->action ] ) )
            $fields = $this->fields[ $this->action ];

        if ( is_object( $this->pod ) ) {
            $object_fields = (array) pods_var_raw( 'object_fields', $this->pod->pod_data, array(), null, true );

            if ( empty( $object_fields ) && in_array( $this->pod->pod_data[ 'type' ], array( 'post_type', 'taxonomy', 'media', 'user', 'comment' ) ) )
                $object_fields = $this->pod->api->get_wp_object_fields( $this->pod->pod_data[ 'type' ], $this->pod->pod_data );

            if ( empty( $fields ) ) {
                // Add core object fields if $fields is empty
                $fields = array_merge( $object_fields, $this->pod->fields );
            }
        }

        $form_fields = $fields; // Temporary

        $fields = array();

        foreach ( $form_fields as $k => $field ) {
            $name = $k;

            $defaults = array(
                'name' => $name
            );

            if ( !is_array( $field ) ) {
                $name = $field;

                $field = array(
                    'name' => $name
                );
            }

            $field = array_merge( $defaults, $field );

            $field[ 'name' ] = trim( $field[ 'name' ] );

            $default_value = pods_var_raw( 'default', $field );
            $value = pods_var_raw( 'value', $field );

            if ( empty( $field[ 'name' ] ) )
                $field[ 'name' ] = trim( $name );

            if ( isset( $object_fields[ $field[ 'name' ] ] ) )
                $field = array_merge( $field, $object_fields[ $field[ 'name' ] ] );
            elseif ( isset( $this->pod->fields[ $field[ 'name' ] ] ) )
                $field = array_merge( $this->pod->fields[ $field[ 'name' ] ], $field );

            if ( pods_var_raw( 'hidden', $field, false, null, true ) )
                $field[ 'type' ] = 'hidden';

			$fields[ $field[ 'name' ] ] = $field;

            if ( empty( $this->id ) && null !== $default_value ) {
                $this->pod->row_override[ $field[ 'name' ] ] = $default_value;
			}
			elseif ( !empty( $this->id ) && null !== $value ) {
                $this->pod->row[ $field[ 'name' ] ] = $value;
			}
        }

        unset( $form_fields ); // Cleanup

        $fields = $this->do_hook( 'form_fields', $fields, $this->pod );

        $pod =& $this->pod;
        $thank_you = pods_query_arg( $vars, self::$allowed, $this->exclusion() );
        $thank_you_alt = pods_query_arg( $alt_vars, self::$allowed, $this->exclusion() );
        $obj =& $this;
        $singular_label = $this->item;
        $plural_label = $this->items;

        if ( is_object( $this->pod ) && 'settings' == $this->pod->pod_data[ 'type' ] && 'settings' == $this->style )
            pods_view( PODS_DIR . 'ui/admin/form-settings.php', compact( array_keys( get_defined_vars() ) ) );
        else
            pods_view( PODS_DIR . 'ui/admin/form.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * @return bool|mixed
	 * @since 2.3.10
     */
    public function view () {

		if ( false !== $this->callback_action( 'view' ) ) {
            return null;
		}

        if ( empty( $this->row ) ) {
            $this->get_row();
		}

        if ( empty( $this->row ) ) {
            return $this->error( sprintf( __( '<strong>Error:</strong> %s not found.', 'pods' ), $this->item ) );
		}

		$pod =& $this->pod;
		$obj =& $this;

		$fields = array();

		if ( isset( $this->fields[ $this->action ] ) ) {
			$fields = $this->fields[ $this->action ];
		}

		if ( is_object( $this->pod ) ) {
			$object_fields = (array) pods_var_raw( 'object_fields', $this->pod->pod_data, array(), null, true );

			$object_field_objects = array(
				'post_type',
				'taxonomy',
				'media',
				'user',
				'comment'
			);

			if ( empty( $object_fields ) && in_array( $this->pod->pod_data[ 'type' ], $object_field_objects ) ) {
				$object_fields = $this->pod->api->get_wp_object_fields( $this->pod->pod_data[ 'type' ], $this->pod->pod_data );
			}

			if ( empty( $fields ) ) {
				// Add core object fields if $fields is empty
				$fields = array_merge( $object_fields, $this->pod->fields );
			}
		}

		$view_fields = $fields; // Temporary

		$fields = array();

		foreach ( $view_fields as $k => $field ) {
			$name = $k;

			$defaults = array(
				'name' => $name,
				'type' => 'text',
				'options' => 'text'
			);

			if ( !is_array( $field ) ) {
				$name = $field;

				$field = array(
					'name' => $name
				);
			}

			$field = array_merge( $defaults, $field );

			$field[ 'name' ] = trim( $field[ 'name' ] );

			$value = pods_var_raw( 'default', $field );

			if ( empty( $field[ 'name' ] ) ) {
				$field[ 'name' ] = trim( $name );
			}

			if ( isset( $object_fields[ $field[ 'name' ] ] ) ) {
				$field = array_merge( $field, $object_fields[ $field[ 'name' ] ] );
			}
			elseif ( isset( $this->pod->fields[ $field[ 'name' ] ] ) ) {
				$field = array_merge( $this->pod->fields[ $field[ 'name' ] ], $field );
			}

			if ( pods_v( 'hidden', $field, false, null, true ) || 'hidden' == $field[ 'type' ] ) {
				continue;
			}
			elseif ( !PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $pod->id() ) ) {
				continue;
			}

			$fields[ $field[ 'name' ] ] = $field;

			if ( empty( $this->id ) && null !== $value ) {
				$this->pod->row_override[ $field[ 'name' ] ] = $value;
			}
		}

		unset( $view_fields ); // Cleanup
		?>
		<div class="wrap pods-ui">
			<div id="icon-edit-pages" class="icon32"<?php if ( false !== $this->icon ) { ?> style="background-position:0 0;background-size:100%;background-image:url(<?php echo esc_url( $this->icon ); ?>);"<?php } ?>><br /></div>
			<h2>
				<?php
					echo $this->do_template( $this->header[ 'view' ] );

					if ( !in_array( 'add', $this->actions_disabled ) && !in_array( 'add', $this->actions_hidden ) ) {
						$link = pods_query_arg( array( 'action' . $this->num => 'add', 'id' . $this->num => '', 'do' . $this->num = '' ), self::$allowed, $this->exclusion() );

						if ( !empty( $this->action_links[ 'add' ] ) ) {
							$link = $this->action_links[ 'add' ];
						}
				?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo $this->heading[ 'add' ]; ?></a>
				<?php
					}
					elseif ( !in_array( 'manage', $this->actions_disabled ) && !in_array( 'manage', $this->actions_hidden ) ) {
						$link = pods_query_arg( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), self::$allowed, $this->exclusion() );

						if ( !empty( $this->action_links[ 'manage' ] ) ) {
							$link = $this->action_links[ 'manage' ];
						}
				?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading[ 'manage' ] ); ?></a>
				<?php
					}

					pods_view( PODS_DIR . 'ui/admin/view.php', compact( array_keys( get_defined_vars() ) ) );
				?>

			</h2>
		</div>
	<?php
	}


    /**
     * Reorder data
     */
    public function reorder () {
        // loop through order
        $order = (array) pods_var_raw( 'order', 'post', array(), null, true );

        $params = array(
            'pod' => $this->pod->pod,
            'field' => $this->reorder[ 'on' ],
            'order' => $order
        );

        $reorder = pods_api()->reorder_pod_item( $params );

        if ( $reorder )
            $this->message( sprintf( __( "<strong>Success!</strong> %s reordered successfully.", 'pods' ), $this->items ) );
        else
            $this->error( sprintf( __( "<strong>Error:</strong> %s has not been reordered.", 'pods' ), $this->items ) );
    }

    /**
     * @param bool $insert
     *
     * @return mixed
     */
    public function save ( $insert = false ) {
        $this->do_hook( 'pre_save', $insert );

        if ( $this->callback( 'save', $insert ) ) {
			return null;
		}

        global $wpdb;
        $action = __( 'saved', 'pods' );
        if ( true === $insert )
            $action = __( 'created', 'pods' );
        $field_sql = array();
        $values = array();
        $data = array();
        foreach ( $this->fields[ 'form' ] as $field => $attributes ) {
            $vartype = '%s';
            if ( 'bool' == $attributes[ 'type' ] )
                $selected = ( 1 == pods_var( $field, 'post', 0 ) ) ? 1 : 0;
            elseif ( '' == pods_var( $field, 'post', '' ) )
                continue;
            if ( false === $attributes[ 'display' ] || false !== $attributes[ 'readonly' ] ) {
                if ( !in_array( $attributes[ 'type' ], array( 'date', 'time', 'datetime' ) ) )
                    continue;
                if ( false === $attributes[ 'date_touch' ] && ( false === $attributes[ 'date_touch_on_create' ] || false === $insert || 0 < $this->id ) )
                    continue;
            }
            if ( in_array( $attributes[ 'type' ], array( 'date', 'time', 'datetime' ) ) ) {
                $format = "Y-m-d H:i:s";
                if ( 'date' == $attributes[ 'type' ] )
                    $format = "Y-m-d";
                if ( 'time' == $attributes[ 'type' ] )
                    $format = "H:i:s";
                if ( false !== $attributes[ 'date_touch' ] || ( false !== $attributes[ 'date_touch_on_create' ] && true === $insert && $this->id < 1 ) )
                    $value = date_i18n( $format );
                else
                    $value = date_i18n( $format, strtotime( ( 'time' == $attributes[ 'type' ] ) ? date_i18n( 'Y-m-d ' ) : pods_var( $field, 'post', '' ) ) );
            }
            else {
                if ( 'bool' == $attributes[ 'type' ] ) {
                    $vartype = '%d';
                    $value = 0;
                    if ( '' != pods_var( $field, 'post', '' ) )
                        $value = 1;
                }
                elseif ( 'number' == $attributes[ 'type' ] ) {
                    $vartype = '%d';
                    $value = number_format( pods_var( $field, 'post', 0 ), 0, '', '' );
                }
                elseif ( 'decimal' == $attributes[ 'type' ] ) {
                    $vartype = '%d';
                    $value = number_format( pods_var( $field, 'post', 0 ), 2, '.', '' );
                }
                elseif ( 'related' == $attributes[ 'type' ] ) {
                    if ( is_array( pods_var( $field, 'post', '' ) ) )
                        $value = implode( ',', pods_var( $field, 'post', '' ) );
                    else
                        $value = pods_var( $field, 'post', '' );
                }
                else
                    $value = pods_var( $field, 'post', '' );
            }

            if ( isset( $attributes[ 'custom_save' ] ) && false !== $attributes[ 'custom_save' ] && is_callable( $attributes[ 'custom_save' ] ) )
                $value = call_user_func_array( $attributes[ 'custom_save' ], array( $value, $field, $attributes, &$this ) );

            $field_sql[] = "`$field`=$vartype";
            $values[] = $value;
            $data[ $field ] = $value;
        }
        $field_sql = implode( ',', $field_sql );
        if ( false === $insert && 0 < $this->id ) {
            $this->insert_id = $this->id;
            $values[] = $this->id;
            $check = $wpdb->query( $wpdb->prepare( "UPDATE $this->sql['table'] SET $field_sql WHERE id=%d", $values ) );
        }
        else
            $check = $wpdb->query( $wpdb->prepare( "INSERT INTO $this->sql['table'] SET $field_sql", $values ) );
        if ( $check ) {
            if ( 0 == $this->insert_id )
                $this->insert_id = $wpdb->insert_id;
            $this->message( sprintf( __( "<strong>Success!</strong> %s %s successfully.", 'pods' ), $this->item, $action ) );
        }
        else
            $this->error( sprintf( __( "<strong>Error:</strong> %s has not been %s.", 'pods' ), $this->item, $action ) );
        $this->do_hook( 'post_save', $this->insert_id, $data, $insert );
    }

    /**
     * @param null $id
     *
     * @return bool|mixed
     */
    public function delete ( $id = null ) {
        $this->do_hook( 'pre_delete', $id );

		if ( false !== $this->callback_action( 'delete', $id ) ) {
			return null;
		}

        $id = pods_absint( $id );

        if ( empty( $id ) )
            $id = pods_absint( $this->id );

        if ( $id < 1 )
            return $this->error( __( '<strong>Error:</strong> Invalid Configuration - Missing "id" definition.', 'pods' ) );

        if ( false === $id )
            $id = $this->id;

        if ( is_object( $this->pod ) )
            $check = $this->pod->delete( $id );
        else
            $check = $this->pods_data->delete( $this->sql[ 'table' ], array( $this->sql[ 'field_id' ] => $id ) );

        if ( $check )
            $this->message( sprintf( __( "<strong>Deleted:</strong> %s has been deleted.", 'pods' ), $this->item ) );
        else
            $this->error( sprintf( __( "<strong>Error:</strong> %s has not been deleted.", 'pods' ), $this->item ) );

        $this->do_hook( 'post_delete', $id );
    }

    /**
     * @param null $id
     *
     * @return bool|mixed
     */
    public function delete_bulk () {
        $this->do_hook( 'pre_delete_bulk' );

        if ( 1 != pods_var( 'deleted_bulk', 'get', 0 ) ) {
            $ids = $this->bulk;

            $success = false;

            if ( !empty( $ids ) ) {
                $ids = (array) $ids;

                foreach ( $ids as $id ) {
                    $id = pods_absint( $id );

                    if ( empty( $id ) )
                        continue;

					if ( $callback = $this->callback( 'delete', $id ) ) {
						$check = $callback;
					}
                    elseif ( is_object( $this->pod ) )
                        $check = $this->pod->delete( $id );
                    else
                        $check = $this->pods_data->delete( $this->sql[ 'table' ], array( $this->sql[ 'field_id' ] => $id ) );

                    if ( $check )
                        $success = true;
                }
            }

            if ( $success )
                pods_redirect( pods_query_arg( array( 'action_bulk' => 'delete', 'deleted_bulk' => 1 ), array( 'page', 'lang', 'action', 'id' ) ) );
            else
                $this->error( sprintf( __( "<strong>Error:</strong> %s has not been deleted.", 'pods' ), $this->item ) );
        }
        else {
            $this->message( sprintf( __( "<strong>Deleted:</strong> %s have been deleted.", 'pods' ), $this->items ) );

            unset( $_GET[ 'deleted_bulk' ] );
        }

        $this->action_bulk = false;
        unset( $_GET[ 'action_bulk' ] );

        $this->do_hook( 'post_delete_bulk' );

        $this->manage();
    }

    public function export () {
        $export_type = pods_var( 'export_type', 'get', 'csv' );

        $type = 'sv'; // covers csv + tsv

        if ( in_array( $export_type, array( 'xml', 'json' ) ) )
            $type = $export_type;

        $delimiter = ',';

        if ( 'tsv' == $export_type )
            $delimiter = "\t";

        $columns = array();

        if ( empty( $this->fields[ 'export' ] ) ) {
            $this->fields[ 'export' ] = $this->pod->fields;

            $columns = array(
                $this->pod->pod_data[ 'field_id' ] => 'ID'
            );
        }

        foreach ( $this->fields[ 'export' ] as $field ) {
            $columns[ $field[ 'name' ] ] = $field[ 'label' ];
        }

        $params = array(
            'full' => true,
            'flatten' => true,
            'fields' => array_keys( $columns ),
            'type' => $type,
            'delimiter' => $delimiter,
            'columns' => $columns
        );

        $items = $this->get_data( $params );

        $data = array(
            'columns' => $columns,
            'items' => $items,
            'fields' => $this->fields[ 'export' ]
        );

        $migrate = pods_migrate( $type, $delimiter, $data );

        $migrate->export();

        $export_file = $migrate->save();

        $this->message( sprintf( __( '<strong>Success:</strong> Your export is ready, you can download it <a href="%s" target="_blank">here</a>', 'pods' ), $export_file ) );

        //echo '<script type="text/javascript">window.open("' . esc_js( $export_file ) . '");</script>';

        $this->get_data();
    }

    /**
     * @param $field
     *
     * @return array|bool|mixed|null
     */
    public function get_field ( $field ) {
        $value = null;

        // use PodsData to get field

		if ( $callback = $this->callback( 'get_field', $field ) ) {
			return $callback;
		}

        if ( isset( $this->row[ $field ] ) ) {
            $value = $this->row[ $field ];
		}
        elseif ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
            if ( 'Pod' == get_class( $this->pod ) )
                $value = $this->pod->get_field( $field );
            else
                $value = $this->pod->field( $field );
        }

        return $this->do_hook( 'get_field', $value, $field );
    }

    /**
     * Get find() params based on current UI action
     *
     * @param null|array $params
     * @param null|string $action
     */
    public function get_params( $params = null, $action = null ) {

        if ( null === $action ) {
            $action = $this->action;
        }

        $defaults = array(
            'full' => false,
            'flatten' => true,
            'fields' => null,
            'type' => ''
        );

        if ( !empty( $params ) && is_array( $params ) )
            $params = (object) array_merge( $defaults, $params );
        else
            $params = (object) $defaults;

        if ( !in_array( $action, array( 'manage', 'reorder' ) ) )
            $action = 'manage';

        $params_override = false;

        $orderby = array();

        $limit = $this->limit;

        $sql = null;

	    if ( 'reorder' == $this->action ) {
		    if ( ! empty( $this->reorder[ 'orderby' ] ) ) {
			    $orderby[ $this->reorder[ 'orderby' ] ] = $this->reorder[ 'orderby_dir' ];
		    } else {
			    $orderby[ $this->reorder[ 'on' ] ] = $this->reorder[ 'orderby_dir' ];
		    }

		    if ( ! empty( $this->reorder[ 'limit' ] ) ) {
			    $limit = $this->reorder[ 'limit' ];
		    }

		    if ( ! empty( $this->reorder[ 'sql' ] ) ) {
			    $sql = $this->reorder[ 'sql' ];
		    }
	    }

        if ( !empty( $this->orderby ) ) {
            $this->orderby = (array) $this->orderby;

            foreach ( $this->orderby as $order ) {
                if ( false !== strpos( $order, ' ' ) ) {
                    $orderby[] = $order;
                }
                elseif ( !isset( $orderby[ $order ] ) ) {
                    $orderby[ $order ] = $this->orderby_dir;
                }
            }
        }

        if ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
            $find_params = array(
                'where' => pods_v( $action, $this->where, null, true ),
                'orderby' => $orderby,
                'page' => (int) $this->page,
                'pagination' => true,
                'limit' => (int) $limit,
                'search' => $this->searchable,
                'search_query' => $this->search,
                'search_across' => $this->search_across,
                'search_across_picks' => $this->search_across_picks,
                'filters' => $this->filters,
                'sql' => $sql
            );

            $params_override = true;
        }
        else {
            $find_params = array(
                'table' => $this->sql[ 'table' ],
                'id' => $this->sql[ 'field_id' ],
                'index' => $this->sql[ 'field_index' ],
                'where' => pods_v( $action, $this->where, null, true ),
                'orderby' => $orderby,
                'page' => (int) $this->page,
                'pagination' => true,
                'limit' => (int) $limit,
                'search' => $this->searchable,
                'search_query' => $this->search,
                'fields' => $this->fields[ 'search' ],
                'sql' => $sql
            );

		if ( ! empty( $this->sql['select'] ) ) {
			$find_params['select'] = $this->sql['select'];
		}
        }

        if ( empty( $find_params[ 'where' ] ) && $this->restricted( $this->action ) )
            $find_params[ 'where' ] = $this->pods_data->query_fields( $this->restrict[ $this->action ], ( is_object( $this->pod ) ? $this->pod->pod_data : null ) );

        if ( $params_override ) {
            $find_params = array_merge( $find_params, (array) $this->params );
        }

        if ( $params->full )
            $find_params[ 'limit' ] = -1;

        // Debug purposes
        if ( 1 == pods_v( 'pods_debug_params', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) )
            pods_debug( $find_params );

        return $find_params;
    }

    /**
     * @param bool $full Whether to get ALL data or use pagination
     *
     * @return bool
     */
    public function get_data ( $params = null ) {
        $action = $this->action;

        $defaults = array(
            'full' => false,
            'flatten' => true,
            'fields' => null,
            'type' => ''
        );

        if ( !empty( $params ) && is_array( $params ) )
            $params = (object) array_merge( $defaults, $params );
        else
            $params = (object) $defaults;

        if ( !in_array( $action, array( 'manage', 'reorder' ) ) )
            $action = 'manage';

        $find_params = $this->get_params( $params );

        if ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
            $this->pod->find( $find_params );

            if ( !$params->full ) {
                $data = $this->pod->data();

                $this->data = $data;

                if ( !empty( $this->data ) ) {
                    $this->data_keys = array_keys( $this->data );
                }

                $this->total = $this->pod->total();
                $this->total_found = $this->pod->total_found();
            }
            else {
                $this->data_full = array();

                $export_params = array(
                    'fields' => $params->fields,
                    'flatten' => true
                );

                if ( in_array( $params->type, array( 'json', 'xml' ) ) )
                    $export_params[ 'flatten' ] = false;

                $export_params = $this->do_hook( 'export_options', $export_params, $params );

                while ( $this->pod->fetch() ) {
                    $this->data_full[ $this->pod->id() ] = $this->pod->export( $export_params );
                }

                $this->pod->reset();

                return $this->data_full;
            }
        }
        else {
            if ( !empty( $this->data ) )
                return $this->data;

            if ( empty( $this->sql[ 'table' ] ) )
                return $this->data;

            $this->pods_data->select( $find_params );

            if ( !$params->full ) {
                $this->data = $this->pods_data->data;

                if ( !empty( $this->data ) ) {
					$this->data_keys = array_keys( $this->data );
				}

                $this->total = $this->pods_data->total();
                $this->total_found = $this->pods_data->total_found();
            }
            else {
                $this->data_full = $this->pods_data->data;

                if ( !empty( $this->data_full ) ) {
					$this->data_keys = array_keys( $this->data_full );
				}

                return $this->data_full;
            }
        }

        return $this->data;
    }

    /**
     * Sort out data alphabetically by a key
     */
    public function sort_data () {
        // only do this if we have a default orderby
        if ( isset( $this->orderby[ 'default' ] ) ) {
            $orderby = $this->orderby[ 'default' ];
            foreach ( $this->data as $k => $v ) {
                $sorter[ $k ] = strtolower( $v[ $orderby ] );
            }
            if ( $this->orderby_dir == 'ASC' )
                asort( $sorter );
            else
                arsort( $sorter );
            foreach ( $sorter as $key => $val ) {
                $intermediary[] = $this->data[ $key ];
            }
            if ( isset( $intermediary ) ) {
                $this->data = $intermediary;
                $this->data_keys = array_keys( $this->data );
            }
        }
    }

    /**
     * @return array
     */
    public function get_row ( &$counter = 0, $method = null ) {
        if ( !empty( $this->row ) && 0 < (int) $this->id && 'table' != $method )
            return $this->row;

        if ( is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) )
            $this->row = $this->pod->fetch();
        else {
            $this->row = false;

            if ( !empty( $this->data ) ) {
                if ( empty( $this->data_keys ) || count( $this->data ) != count( $this->data_keys ) ) {
                    $this->data_keys = array_keys( $this->data );
				}

                if ( count( $this->data ) == $this->total && isset( $this->data_keys[ $counter ] ) && isset( $this->data[ $this->data_keys[ $counter ] ] ) ) {
                    $this->row = $this->data[ $this->data_keys[ $counter ] ];

                    $counter++;
                }
            }

            if ( false === $this->row && 0 < (int) $this->id && !empty( $this->sql[ 'table' ] ) ) {
                $this->pods_data->select(
                    array(
                        'table' => $this->sql[ 'table' ],
                        'where' => '`' . $this->sql[ 'field_id' ] . '` = ' . (int) $this->id,
                        'limit' => 1
                    )
                );

                $this->row = $this->pods_data->fetch();
            }
        }

        return $this->row;
    }

    /**
     * @param bool $reorder
     *
     * @return mixed|null
     */
    public function manage ( $reorder = false ) {
		if ( false !== $this->callback_action( 'manage', $reorder ) ) {
			return null;
		}

        if ( !empty( $this->action_bulk ) && !empty( $this->actions_bulk ) && isset( $this->actions_bulk[ $this->action_bulk ] ) && !in_array( $this->action_bulk, $this->actions_disabled ) && !empty( $this->bulk ) ) {
	        if ( empty( $_REQUEST[ '_wpnonce' . $this->num ] ) || false === wp_verify_nonce( $_REQUEST[ '_wpnonce' . $this->num ], 'pods-ui-action-bulk' ) ) {
		        pods_message( __( 'Invalid bulk request, please try again.', 'pods' ) );
	        }
	        elseif ( false !== $this->callback_bulk( $this->action_bulk, $this->bulk ) ) {
				return null;
			}
            elseif ( 'delete' == $this->action_bulk ) {
                return $this->delete_bulk();
			}
        }

        $this->screen_meta();

        if ( true === $reorder )
            wp_enqueue_script( 'jquery-ui-sortable' );
        ?>
    <div class="wrap pods-admin pods-ui">
        <div id="icon-edit-pages" class="icon32"<?php if ( false !== $this->icon ) { ?> style="background-position:0 0;background-size:100%;background-image:url(<?php echo esc_url( $this->icon ); ?>);"<?php } ?>><br /></div>
        <h2>
            <?php
            if ( true === $reorder ) {
                echo $this->header[ 'reorder' ];

				$link = pods_query_arg( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), self::$allowed, $this->exclusion() );

                if ( !empty( $this->action_links[ 'manage' ] ) )
                    $link = $this->action_links[ 'manage' ];
                ?>
                <small>(<a href="<?php echo esc_url( $link ); ?>">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading[ 'manage' ] ); ?></a>)</small>
                <?php
            }
            else
                echo $this->header[ 'manage' ];

            if ( !in_array( 'add', $this->actions_disabled ) && !in_array( 'add', $this->actions_hidden ) ) {
                $link = pods_query_arg( array( 'action' . $this->num => 'add', 'id' . $this->num => '', 'do' . $this->num => '' ), self::$allowed, $this->exclusion() );

                if ( !empty( $this->action_links[ 'add' ] ) )
                    $link = $this->action_links[ 'add' ];
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo $this->label[ 'add_new' ]; ?></a>
                <?php
            }
            if ( !in_array( 'reorder', $this->actions_disabled ) && !in_array( 'reorder', $this->actions_hidden ) && false !== $this->reorder[ 'on' ] ) {
                $link = pods_query_arg( array( 'action' . $this->num => 'reorder' ), self::$allowed, $this->exclusion() );

                if ( !empty( $this->action_links[ 'reorder' ] ) )
                    $link = $this->action_links[ 'reorder' ];
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo $this->label[ 'reorder' ]; ?></a>
                <?php
            }
            ?>
        </h2>

		<form id="posts-filter" action="" method="get">
        	<?php
                $excluded_filters = array(
                    'search' . $this->num,
                    'pg' . $this->num,
                    'action' . $this->num,
                    'action_bulk' . $this->num,
                    'action_bulk_ids' . $this->num,
	                '_wpnonce' . $this->num
                );

                $filters = $this->filters;

                foreach ( $filters as $k => $filter ) {
					if ( isset( $this->pod->fields[ $filter ] ) ) {
						$filter_field = $this->pod->fields[ $filter ];
					}
					elseif ( isset( $this->fields[ 'manage' ][ $filter ] ) ) {
						$filter_field = $this->fields[ 'manage' ][ $filter ];
					}
					else {
						unset( $filters[ $k ] );
						continue;
					}

                    if ( in_array( $filter_field[ 'type' ], array( 'date', 'datetime', 'time' ) ) ) {
                        if ( '' == pods_var_raw( 'filter_' . $filter . '_start', 'get', '', null, true ) && '' == pods_var_raw( 'filter_' . $filter . '_end', 'get', '', null, true ) ) {
                            unset( $filters[ $k ] );
                            continue;
                        }
                    }
                    elseif ( '' === pods_var_raw( 'filter_' . $filter, 'get', '' ) ) {
                        unset( $filters[ $k ] );
                        continue;
                    }

                    $excluded_filters[] = 'filter_' . $filter . '_start';
                    $excluded_filters[] = 'filter_' . $filter . '_end';
                    $excluded_filters[] = 'filter_' . $filter;
                }

                $get = $_GET;

                foreach ( $get as $k => $v ) {
                    if ( is_array( $v ) || in_array( $k, $excluded_filters ) || strlen( $v ) < 1 )
                        continue;
			?>
				<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
			<?php
                }

			if ( false !== $this->callback( 'header', $reorder ) ) {
				return null;
			}

            if ( false === $this->data )
                $this->get_data();
            elseif ( $this->sortable ) // we have the data already as an array
                $this->sort_data();

            if ( !in_array( 'export', $this->actions_disabled ) && 'export' == $this->action )
                $this->export();

            if ( ( !empty( $this->data ) || false !== $this->search || ( $this->filters_enhanced && !empty( $this->views ) ) ) && ( ( $this->filters_enhanced && !empty( $this->views ) ) || false !== $this->searchable ) ) {
                if ( $this->filters_enhanced )
                    $this->filters();
                else {
                ?>
                <p class="search-box" align="right">
                    <?php
                    $excluded_filters = array( 'search' . $this->num, 'pg' . $this->num );

                    foreach ( $this->filters as $filter ) {
                        $excluded_filters[] = 'filter_' . $filter . '_start';
                        $excluded_filters[] = 'filter_' . $filter . '_end';
                        $excluded_filters[] = 'filter_' . $filter;
                    }

                    $this->hidden_vars( $excluded_filters );

                    foreach ( $this->filters as $filter ) {
						if ( isset( $this->pod->fields[ $filter ] ) ) {
							$filter_field = $this->pod->fields[ $filter ];
						}
						elseif ( isset( $this->fields[ 'manage' ][ $filter ] ) ) {
							$filter_field = $this->fields[ 'manage' ][ $filter ];
						}
						else {
							continue;
						}

                        if ( in_array( $filter_field[ 'type' ], array( 'date', 'datetime', 'time' ) ) ) {
                            $start = pods_var_raw( 'filter_' . $filter . '_start', 'get', pods_var_raw( 'filter_default', $filter_field, '', null, true ), null, true );
                            $end = pods_var_raw( 'filter_' . $filter . '_end', 'get', pods_var_raw( 'filter_ongoing_default', $filter_field, '', null, true ), null, true );

                            // override default value
                            $filter_field[ 'options' ][ 'default_value' ] = '';
                            $filter_field[ 'options' ][ $filter_field[ 'type' ] . '_allow_empty' ] = 1;


                            if ( !empty( $start ) && !in_array( $start, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
                                $start = PodsForm::field_method( $filter_field[ 'type' ], 'convert_date', $start, 'n/j/Y' );

                            if ( !empty( $end ) && !in_array( $end, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
                                $end = PodsForm::field_method( $filter_field[ 'type' ], 'convert_date', $end, 'n/j/Y' );
                    ?>
                        <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
                            <?php echo $filter_field[ 'label' ]; ?>
                        </label>
                        <?php echo PodsForm::field( 'filter_' . $filter . '_start', $start, $filter_field[ 'type' ], $filter_field ); ?>

                        <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">
                            to
                        </label>
                    <?php
                            echo PodsForm::field( 'filter_' . $filter . '_end', $end, $filter_field[ 'type' ], $filter_field );
                        }
                        elseif ( 'pick' == $filter_field[ 'type' ] ) {
                            $value = pods_var_raw( 'filter_' . $filter, 'get' );

                            if ( strlen( $value ) < 1 )
                                $value = pods_var_raw( 'filter_default', $filter_field );

                            // override default value
                            $filter_field[ 'options' ][ 'default_value' ] = '';

                            $filter_field[ 'options' ][ 'pick_format_type' ] = 'single';
                            $filter_field[ 'options' ][ 'pick_format_single' ] = 'dropdown';

                            $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields[ 'search' ], array(), null, true ), array(), null, true ), '', null, true );
                            $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', $filter_field[ 'options' ], $filter_field[ 'options' ][ 'input_helper' ], null, true );

                            $options = array_merge( $filter_field, $filter_field[ 'options' ] );
                    ?>
                        <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
                            <?php echo $filter_field[ 'label' ]; ?>
                        </label>
                    <?php
                            echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options );
                        }
                        elseif ( 'boolean' == $filter_field[ 'type' ] ) {
                            $value = pods_var_raw( 'filter_' . $filter, 'get', '' );

                            if ( strlen( $value ) < 1 )
                                $value = pods_var_raw( 'filter_default', $filter_field );

                            // override default value
                            $filter_field[ 'options' ][ 'default_value' ] = '';

                            $filter_field[ 'options' ][ 'pick_format_type' ] = 'single';
                            $filter_field[ 'options' ][ 'pick_format_single' ] = 'dropdown';

                            $filter_field[ 'options' ][ 'pick_object' ] = 'custom-simple';
                            $filter_field[ 'options' ][ 'pick_custom' ] = array(
                                '1' => pods_var_raw( 'boolean_yes_label', $filter_field[ 'options' ], __( 'Yes', 'pods' ), null, true ),
                                '0' => pods_var_raw( 'boolean_no_label', $filter_field[ 'options' ], __( 'No', 'pods' ), null, true )
                            );

                            $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields[ 'search' ], array(), null, true ), array(), null, true ), '', null, true );
                            $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', $filter_field[ 'options' ], $filter_field[ 'options' ][ 'input_helper' ], null, true );

                            $options = array_merge( $filter_field, $filter_field[ 'options' ] );
                    ?>
                        <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
                            <?php echo $filter_field[ 'label' ]; ?>
                        </label>
                    <?php
                            echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options );
                        }
                        else {
                            $value = pods_var_raw( 'filter_' . $filter, 'get' );

                            if ( strlen( $value ) < 1 )
                                $value = pods_var_raw( 'filter_default', $filter_field );

                            // override default value
                            $filter_field[ 'options' ][ 'default_value' ] = '';

                            $options = array();
                            $options[ 'input_helper' ] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields[ 'search' ], array(), null, true ), array(), null, true ), '', null, true );
                            $options[ 'input_helper' ] = pods_var_raw( 'ui_input_helper', $options, $options[ 'input_helper' ], null, true );
                    ?>
                        <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
                            <?php echo $filter_field[ 'label' ]; ?>
                        </label>
                    <?php
                            echo PodsForm::field( 'filter_' . $filter, $value, 'text', $options );
                        }
                    }

					if ( false !== $this->do_hook( 'filters_show_search', true ) ) {
					?>
						&nbsp;&nbsp; <label<?php echo ( empty( $this->filters ) ) ? ' class="screen-reader-text"' : ''; ?> for="page-search<?php echo esc_attr( $this->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>:</label>
						<?php echo PodsForm::field( 'search' . $this->num, $this->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $this->num . '-input' ) ) ); ?>
					<?php
					}
					else {
						echo PodsForm::field( 'search' . $this->num, '', 'hidden' );
					}
                    ?>
                    <?php echo PodsForm::submit_button( $this->header[ 'search' ], 'button', false, false, array('id' => 'search' . $this->num . '-submit') ); ?>
                    <?php
                    if ( 0 < strlen( $this->search ) ) {
                        $clear_filters = array(
							'search' . $this->num => false
						);

                        foreach ( $this->filters as $filter ) {
                            $clear_filters[ 'filter_' . $filter . '_start' ] = false;
                            $clear_filters[ 'filter_' . $filter . '_end' ] = false;
                            $clear_filters[ 'filter_' . $filter ] = false;
                        }
                        ?>
                        <br class="clear" />
                        <small>[<a href="<?php echo esc_url( pods_query_arg( $clear_filters, array( 'orderby' . $this->num, 'orderby_dir' . $this->num, 'limit' . $this->num, 'page' ), $this->exclusion() ) ); ?>"><?php _e( 'Reset Filters', 'pods' ); ?></a>]</small>
                        <br class="clear" />
                        <?php
                    }
                    ?>
                </p>
                <?php
            }
            }
            else {
                ?>
                <br class="clear" />
                <?php
            }

            if ( !empty( $this->data ) && ( false !== $this->pagination_total || false !== $this->pagination || true === $reorder ) || ( !in_array( 'export', $this->actions_disabled ) && !in_array( 'export', $this->actions_hidden ) ) || !empty( $this->actions_disabled ) ) {
                ?>
                <div class="tablenav">
                    <?php
                    if ( !empty( $this->data ) && !empty( $this->actions_bulk ) ) {
                ?>
                    <div class="alignleft actions">
	                    <?php wp_nonce_field( 'pods-ui-action-bulk', '_wpnonce' . $this->num, false ); ?>

                        <select name="action_bulk<?php echo esc_attr( $this->num ); ?>">
                            <option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'pods' ); ?></option>

                            <?php
                                foreach ( $this->actions_bulk as $action => $action_data ) {
                                    if ( in_array( $action, $this->actions_hidden ) || in_array( $action, $this->actions_hidden ) )
                                        continue;

                                    if ( !isset( $action_data[ 'label' ] ) )
                                        $action_data[ 'label' ] = ucwords( str_replace( '_', ' ', $action ) );
                            ?>
                                <option value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $action_data[ 'label' ] ); ?></option>
                            <?php
                                }
                            ?>
                        </select>

                        <input type="submit" id="doaction_bulk<?php echo esc_attr( $this->num ); ?>" class="button-secondary action" value="<?php esc_attr_e( 'Apply', 'pods' ); ?>">
                    </div>
                <?php
                    }

                    if ( true !== $reorder && ( false !== $this->pagination_total || false !== $this->pagination ) ) {
                        ?>
                        <div class="tablenav-pages<?php echo esc_attr( ( $this->limit < $this->total_found || 1 < $this->page ) ? '' : ' one-page' ); ?>">
                            <?php $this->pagination( 1 ); ?>
                        </div>
                        <?php
                    }

                    if ( true === $reorder ) {
						$link = pods_query_arg( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), self::$allowed, $this->exclusion() );

						if ( !empty( $this->action_links[ 'manage' ] ) ) {
							$link = $this->action_links[ 'manage' ];
						}
                        ?>
                        <input type="button" value="<?php esc_attr_e( 'Update Order', 'pods' ); ?>" class="button" onclick="jQuery('form.admin_ui_reorder_form').submit();" />
                        <input type="button" value="<?php esc_attr_e( 'Cancel', 'pods' ); ?>" class="button" onclick="document.location='<?php echo esc_js( $link ); ?>';" />
                    </form>
				<?php
                    }
                    elseif ( !in_array( 'export', $this->actions_disabled ) && !in_array( 'export', $this->actions_hidden ) ) {
                        ?>
                        <div class="alignleft actions">
                            <strong><?php _e( 'Export', 'pods' ); ?>:</strong>
                            <?php
                            foreach ( $this->export[ 'formats' ] as $format => $separator ) {
                                ?>
                                <input type="button" value=" <?php echo esc_attr( strtoupper( $format ) ); ?> " class="button" onclick="document.location='<?php echo pods_slash( pods_query_arg( array( 'action' . $this->num => 'export', 'export_type' . $this->num => $format, '_wpnonce' => wp_create_nonce( 'pods-ui-action-export' ) ), self::$allowed, $this->exclusion() ) ); ?>';" />
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
            }
            else {
                ?>
                <br class="clear" />
                <?php
            }
            ?>
            <div class="clear"></div>
            <?php
            if ( empty( $this->data ) && false !== $this->default_none && false === $this->search ) {
                ?>
                <p><?php _e( 'Please use the search filter(s) above to display data', 'pods' ); ?><?php if ( $this->export ) { ?>, <?php _e( 'or click on an Export to download a full copy of the data', 'pods' ); ?><?php } ?>.</p>
                <?php
            }
            else
                $this->table( $reorder );
            if ( !empty( $this->data ) ) {
                if ( true !== $reorder && ( false !== $this->pagination_total || false !== $this->pagination ) ) {
                    ?>
                    <div class="tablenav">
                        <div class="tablenav-pages<?php echo esc_attr( ( $this->limit < $this->total_found || 1 < $this->page ) ? '' : ' one-page' ); ?>">
                            <?php $this->pagination( 0 ); ?>
                            <br class="clear" />
                        </div>
                    </div>
                    <?php
                }
            }

            ?>
        </form>
    </div>
    <?php
        if ( $this->filters_enhanced )
            $this->filters_popup();
    }

    public function filters () {
		include_once ABSPATH . 'wp-admin/includes/template.php';

        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'pods-ui-list-table', PODS_URL . 'ui/css/pods-ui-list-table.css', array( 'thickbox' ), PODS_VERSION );

        $filters = $this->filters;

        foreach ( $filters as $k => $filter ) {
			if ( isset( $this->pod->fields[ $filter ] ) ) {
				$filter_field = $this->pod->fields[ $filter ];
			}
			elseif ( isset( $this->fields[ 'manage' ][ $filter ] ) ) {
				$filter_field = $this->fields[ 'manage' ][ $filter ];
			}
			else {
				continue;
			}

            if ( isset( $filter_field ) && in_array( $filter_field[ 'type' ], array( 'date', 'datetime', 'time' ) ) ) {
                if ( '' == pods_var_raw( 'filter_' . $filter . '_start', 'get', '', null, true ) && '' == pods_var_raw( 'filter_' . $filter . '_end', 'get', '', null, true ) )
                    unset( $filters[ $k ] );
            }
            elseif ( '' === pods_var_raw( 'filter_' . $filter, 'get', '' ) )
                unset( $filters[ $k ] );
        }

        $filtered = false;

        if ( !empty( $filters ) )
            $filtered = true;
?>
    <div class="pods-ui-filter-bar">
        <div class="pods-ui-filter-bar-primary">
            <?php
                if ( !empty( $this->views ) ) {
            ?>
                <ul class="subsubsub">
                    <li class="pods-ui-filter-view-label"><strong><?php echo $this->heading[ 'views' ]; ?></strong></li>

                    <?php
                        foreach ( $this->views as $view => $label ) {
                            if ( false === strpos( $label, '<a' ) ) {
                                $link = pods_query_arg( array( 'view' . $this->num => $view, 'pg' . $this->num => '' ), self::$allowed, $this->exclusion() );

                                if ( $this->view == $view )
                                    $label = '<a href="' . esc_url( $link ) . '" class="current">' . $label . '</a>';
                                else
                                    $label = '<a href="' . esc_url( $link ) . '">' . $label . '</a>';
                            }
                    ?>
                        <li class="<?php echo esc_attr( $view ); ?>"><?php echo $label; ?></li>
                    <?php
                        }
                    ?>
                </ul>
            <?php
                }
            ?>

            <?php
                if ( false !== $this->search && false !== $this->searchable ) {
            ?>
                <p class="search-box">
                    <?php
                        if ( $filtered || '' != pods_var_raw( 'search' . $this->num, 'get', '', null, true ) ) {
                            $clear_filters = array(
								'search' . $this->num => false
							);

                            foreach ( $this->filters as $filter ) {
                                $clear_filters[ 'filter_' . $filter . '_start' ] = false;
                                $clear_filters[ 'filter_' . $filter . '_end' ] = false;
                                $clear_filters[ 'filter_' . $filter ] = false;
                            }
                    ?>
                        <a href="<?php echo esc_url( pods_query_arg( $clear_filters, array( 'orderby' . $this->num, 'orderby_dir' . $this->num, 'limit' . $this->num, 'page' ), $this->exclusion() ) ); ?>" class="pods-ui-filter-reset">[<?php _e( 'Reset', 'pods' ); ?>]</a>
                    <?php
                        }

						if ( false !== $this->do_hook( 'filters_show_search', true ) ) {
					?>
						&nbsp;&nbsp; <label class="screen-reader-text" for="page-search<?php echo esc_attr( $this->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>:</label>
						<?php echo PodsForm::field( 'search' . $this->num, $this->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $this->num . '-input' ) ) ); ?>
					<?php
						}
						else {
							echo PodsForm::field( 'search' . $this->num, '', 'hidden' );
						}
					?>

                    <?php echo PodsForm::submit_button( $this->header[ 'search' ], 'button', false, false, array('id' => 'search' . $this->num . '-submit') ); ?>
                </p>
            <?php
                }
            ?>
        </div>

        <?php
            if ( !empty( $this->filters ) ) {
        ?>
            <div class="pods-ui-filter-bar-secondary">
                <ul class="subsubsub">
                    <?php
                        if ( !$filtered ) {
                    ?>
                        <li class="pods-ui-filter-bar-add-filter">
                            <a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox" title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
                                <?php _e( 'Advanced Filters', 'pods' ); ?>
                            </a>
                        </li>
                    <?php
                        }
                        else {
                    ?>
                        <li class="pods-ui-filter-bar-add-filter">
                            <a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox" title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
                                + <?php _e( 'Add Filter', 'pods' ); ?>
                            </a>
                        </li>
                    <?php
                        }

                        foreach ( $filters as $filter ) {
                            $value = pods_var_raw( 'filter_' . $filter, 'get' );

							if ( isset( $this->pod->fields[ $filter ] ) ) {
								$filter_field = $this->pod->fields[ $filter ];
							}
							elseif ( isset( $this->fields[ 'manage' ][ $filter ] ) ) {
								$filter_field = $this->fields[ 'manage' ][ $filter ];
							}
							else {
								continue;
							}

                            $data_filter = 'filter_' . $filter;

                            $start = $end = $value_label = '';

                            if ( in_array( $filter_field[ 'type' ], array( 'date', 'datetime', 'time' ) ) ) {
                                $start = pods_var_raw( 'filter_' . $filter . '_start', 'get', '', null, true );
                                $end = pods_var_raw( 'filter_' . $filter . '_end', 'get', '', null, true );

                                if ( !empty( $start ) && !in_array( $start, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
                                    $start = PodsForm::field_method( $filter_field[ 'type' ], 'convert_date', $start, 'n/j/Y' );

                                if ( !empty( $end ) && !in_array( $end, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
                                    $end = PodsForm::field_method( $filter_field[ 'type' ], 'convert_date', $end, 'n/j/Y' );

                                $value = trim( $start . ' - ' . $end, ' -' );

                                $data_filter = 'filter_' . $filter . '_start';
                            }
                            elseif ( 'pick' == $filter_field[ 'type' ] )
                                $value_label = trim( PodsForm::field_method( 'pick', 'value_to_label', $filter, $value, $filter_field, $this->pod->pod_data, null ) );
                            elseif ( 'boolean' == $filter_field[ 'type' ] ) {
                                $yesno_options = array(
                                    '1' => pods_var_raw( 'boolean_yes_label', $filter_field[ 'options' ], __( 'Yes', 'pods' ), null, true ),
                                    '0' => pods_var_raw( 'boolean_no_label', $filter_field[ 'options' ], __( 'No', 'pods' ), null, true )
                                );

                                if ( isset( $yesno_options[ (string) $value ] ) )
                                    $value_label = $yesno_options[ (string) $value ];
                            }

                            if ( strlen( $value_label ) < 1 )
                                $value_label = $value;
                    ?>
                        <li class="pods-ui-filter-bar-filter" data-filter="<?php echo esc_attr( $data_filter ); ?>">
                            <a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox" title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
                                <strong><?php echo $filter_field[ 'label' ]; ?>:</strong>
                                <?php echo esc_html( $value_label ); ?>
                            </a>

                            <a href="#remove-filter" class="remove-filter" title="<?php esc_attr_e( 'Remove Filter', 'pods' ); ?>">x</a>

                            <?php
                                if ( in_array( $filter_field[ 'type' ], array( 'date', 'datetime', 'time' ) ) ) {
                                    echo PodsForm::field( 'filter_' . $filter . '_start', $start, 'hidden' );
                                    echo PodsForm::field( 'filter_' . $filter . '_end', $end, 'hidden' );
                                }
                                else
                                    echo PodsForm::field( $data_filter, $value, 'hidden' );
                            ?>
                        </li>
                    <?php
                        }
                    ?>
                </ul>
            </div>
        <?php
            }
        ?>
    </div>

    <script type="text/javascript">
        jQuery( function() {
            jQuery( '.pods-ui-filter-bar-secondary' ).on( 'click', '.remove-filter', function ( e ) {
                jQuery( '.pods-ui-filter-popup #' + jQuery( this ).parent().data( 'filter' ) ).remove();

                jQuery( this ).parent().find( 'input' ).each( function () {
                    jQuery( this ).remove();
                } );

                jQuery( 'form#posts-filter [name="pg<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );
                jQuery( 'form#posts-filter [name="action<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );
                jQuery( 'form#posts-filter [name="action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );
	            jQuery( 'form#posts-filter [name="_wpnonce<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );

                jQuery( 'form#posts-filter' ).submit();

                e.preventDefault();
            } );
        } );
    </script>
<?php
    }

    public function filters_popup () {
        $filters = $this->filters;
?>
    <div id="pods-ui-posts-filter-popup" class="hidden">
        <form action="" method="get" class="pods-ui-posts-filter-popup">
            <h2><?php _e( 'Advanced Filters', 'pods' ); ?></h2>

            <div class="pods-ui-posts-filters">
                <?php
                    $excluded_filters = array(
                        'search' . $this->num,
                        'pg' . $this->num,
                        'action' . $this->num,
                        'action_bulk' . $this->num,
                        'action_bulk_ids' . $this->num,
                        '_wpnonce' . $this->num
                    );

                    foreach ( $filters as $filter ) {
                        $excluded_filters[] = 'filters_relation';
                        $excluded_filters[] = 'filters_compare_' . $filter;
                        $excluded_filters[] = 'filter_' . $filter . '_start';
                        $excluded_filters[] = 'filter_' . $filter . '_end';
                        $excluded_filters[] = 'filter_' . $filter;
                    }

                    $get = $_GET;

                    foreach ( $get as $k => $v ) {
                        if ( in_array( $k, $excluded_filters ) || strlen( $v ) < 1 )
                            continue;
                ?>
                    <input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
                <?php
                    }

                    $zebra = true;

                    foreach ( $filters as $filter ) {
                        if ( empty( $filter ) )
                            continue;

						if ( isset( $this->pod->fields[ $filter ] ) ) {
							$filter_field = $this->pod->fields[ $filter ];
						}
						elseif ( isset( $this->fields[ 'manage' ][ $filter ] ) ) {
							$filter_field = $this->fields[ 'manage' ][ $filter ];
						}
						else {
							continue;
						}
                ?>
                    <p class="pods-ui-posts-filter-toggled pods-ui-posts-filter-<?php echo esc_attr( $filter . ( $zebra ? ' clear' : '' ) ); ?>">
                        <?php
                            if ( in_array( $filter_field[ 'type' ], array( 'date', 'datetime', 'time' ) ) ) {
                                $start = pods_var_raw( 'filter_' . $filter . '_start', 'get', pods_var_raw( 'filter_default', $filter_field, '', null, true ), null, true );
                                $end = pods_var_raw( 'filter_' . $filter . '_end', 'get', pods_var_raw( 'filter_ongoing_default', $filter_field, '', null, true ), null, true );

                                // override default value
                                $filter_field[ 'options' ][ 'default_value' ] = '';
                                $filter_field[ 'options' ][ $filter_field[ 'type' ] . '_allow_empty' ] = 1;

                                if ( !empty( $start ) && !in_array( $start, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
                                    $start = PodsForm::field_method( $filter_field[ 'type' ], 'convert_date', $start, 'n/j/Y' );

                                if ( !empty( $end ) && !in_array( $end, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
                                    $end = PodsForm::field_method( $filter_field[ 'type' ], 'convert_date', $end, 'n/j/Y' );
                        ?>
                            <span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( ( empty( $start ) && empty( $end ) ) ? '' : ' hidden' ); ?>">+</span>
                            <span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( ( empty( $start ) && empty( $end ) ) ? ' hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

                            <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
                                <?php echo $filter_field[ 'label' ]; ?>
                            </label>

                            <span class="pods-ui-posts-filter<?php echo esc_attr( ( empty( $start ) && empty( $end ) ) ? ' hidden' : '' ); ?>">
                                <?php echo PodsForm::field( 'filter_' . $filter . '_start', $start, $filter_field[ 'type' ], $filter_field ); ?>

                                <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">to</label>
                                <?php echo PodsForm::field( 'filter_' . $filter . '_end', $end, $filter_field[ 'type' ], $filter_field ); ?>
                            </span>
                        <?php
                            }
                            elseif ( 'pick' == $filter_field[ 'type' ] ) {
                                $value = pods_var_raw( 'filter_' . $filter, 'get', '' );

                                if ( strlen( $value ) < 1 )
                                    $value = pods_var_raw( 'filter_default', $filter_field );

                                // override default value
                                $filter_field[ 'options' ][ 'default_value' ] = '';

                                $filter_field[ 'options' ][ 'pick_format_type' ] = 'single';
                                $filter_field[ 'options' ][ 'pick_format_single' ] = 'dropdown';

                                $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields[ 'search' ], array(), null, true ), array(), null, true ), '', null, true );
                                $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', $filter_field[ 'options' ], $filter_field[ 'options' ][ 'input_helper' ], null, true );

                                $options = array_merge( $filter_field, $filter_field[ 'options' ] );
                        ?>
                            <span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( empty( $value ) ? '' : ' hidden' ); ?>">+</span>
                            <span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( empty( $value ) ? ' hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

                            <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
                                <?php echo $filter_field[ 'label' ]; ?>
                            </label>

                            <span class="pods-ui-posts-filter<?php echo esc_attr( strlen( $value ) < 1 ? ' hidden' : '' ); ?>">
                                <?php echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options ); ?>
                            </span>
                        <?php
                            }
                            elseif ( 'boolean' == $filter_field[ 'type' ] ) {
                                $value = pods_var_raw( 'filter_' . $filter, 'get', '' );

                                if ( strlen( $value ) < 1 )
                                    $value = pods_var_raw( 'filter_default', $filter_field );

                                // override default value
                                $filter_field[ 'options' ][ 'default_value' ] = '';

                                $filter_field[ 'options' ][ 'pick_format_type' ] = 'single';
                                $filter_field[ 'options' ][ 'pick_format_single' ] = 'dropdown';

                                $filter_field[ 'options' ][ 'pick_object' ] = 'custom-simple';
                                $filter_field[ 'options' ][ 'pick_custom' ] = array(
                                    '1' => pods_var_raw( 'boolean_yes_label', $filter_field[ 'options' ], __( 'Yes', 'pods' ), null, true ),
                                    '0' => pods_var_raw( 'boolean_no_label', $filter_field[ 'options' ], __( 'No', 'pods' ), null, true )
                                );

                                $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields[ 'search' ], array(), null, true ), array(), null, true ), '', null, true );
                                $filter_field[ 'options' ][ 'input_helper' ] = pods_var_raw( 'ui_input_helper', $filter_field[ 'options' ], $filter_field[ 'options' ][ 'input_helper' ], null, true );

                                $options = array_merge( $filter_field, $filter_field[ 'options' ] );
                        ?>
                            <span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( empty( $value ) ? '' : ' hidden' ); ?>">+</span>
                            <span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( empty( $value ) ? ' hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

                            <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
                                <?php echo $filter_field[ 'label' ]; ?>
                            </label>

                            <span class="pods-ui-posts-filter<?php echo esc_attr( strlen( $value ) < 1 ? ' hidden' : '' ); ?>">
                                <?php echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options ); ?>
                            </span>
                        <?php
                            }
                            else {
                                $value = pods_var_raw( 'filter_' . $filter, 'get' );

                                if ( strlen( $value ) < 1 )
                                    $value = pods_var_raw( 'filter_default', $filter_field );

                                $options = array(
                                    'input_helper' => pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields[ 'search' ], array(), null, true ), array(), null, true ), '', null, true )
                                );

                                if ( empty( $options[ 'input_helper' ] ) && isset( $filter_field[ 'options' ] ) && isset( $filter_field[ 'options' ][ 'input_helper' ] ) )
                                    $options[ 'input_helper' ] = $filter_field[ 'options' ][ 'input_helper' ];
                        ?>
                            <span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( empty( $value ) ? '' : ' hidden' ); ?>">+</span>
                            <span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( empty( $value ) ? ' hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

                            <label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
                                <?php echo $filter_field[ 'label' ]; ?>
                            </label>

                            <span class="pods-ui-posts-filter<?php echo esc_attr( empty( $value ) ? ' hidden' : '' ); ?>">
                                <?php echo PodsForm::field( 'filter_' . $filter, $value, 'text', $options ); ?>
                            </span>
                        <?php
                            }
                        ?>
                    </p>
                <?php
                        $zebra = empty( $zebra );
                    }
                ?>

                <p class="pods-ui-posts-filter-toggled pods-ui-posts-filter-search<?php echo esc_attr( $zebra ? ' clear' : '' ); ?>">
                    <label for="pods-form-ui-search<?php echo esc_attr( $this->num ); ?>"><?php _e( 'Search Text', 'pods' ); ?></label>
                    <?php echo PodsForm::field( 'search' . $this->num, pods_var_raw( 'search' . $this->num, 'get' ), 'text' ); ?>
                </p>

                <?php $zebra = empty( $zebra ); ?>
            </div>

            <p class="submit<?php echo esc_attr( $zebra ? ' clear' : '' ); ?>"><input type="submit" value="<?php echo esc_attr( $this->header[ 'search' ] ); ?>" class="button button-primary" /></p>
        </form>
    </div>

    <script type="text/javascript">
        jQuery( function () {
            jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggle.toggle-on', function ( e ) {
                jQuery( this ).parent().find( '.pods-ui-posts-filter' ).removeClass( 'hidden' );

                jQuery( this ).hide();
                jQuery( this ).parent().find( '.toggle-off' ).show();
            } );

            jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggle.toggle-off', function ( e ) {
                jQuery( this ).parent().find( '.pods-ui-posts-filter' ).addClass( 'hidden' );
                jQuery( this ).parent().find( 'select, input' ).val( '' );

                jQuery( this ).hide();
                jQuery( this ).parent().find( '.toggle-on' ).show();
            } );

            jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggled label', function ( e ) {
                if ( jQuery( this ).parent().find( '.pods-ui-posts-filter' ).hasClass( 'hidden' ) ) {
                    jQuery( this ).parent().find( '.pods-ui-posts-filter' ).removeClass( 'hidden' );

                    jQuery( this ).parent().find( '.toggle-on' ).hide();
                    jQuery( this ).parent().find( '.toggle-off' ).show();
                }
                else {
                    jQuery( this ).parent().find( '.pods-ui-posts-filter' ).addClass( 'hidden' );
                    jQuery( this ).parent().find( 'select, input' ).val( '' );

                    jQuery( this ).parent().find( '.toggle-on' ).show();
                    jQuery( this ).parent().find( '.toggle-off' ).hide();
                }
            } );
        } );
    </script>
<?php
    }

    /**
     * @param bool $reorder
     *
     * @return bool|mixed
     */
    public function table ( $reorder = false ) {
		if ( false !== $this->callback( 'table', $reorder ) ) {
			return null;
		}

        if ( empty( $this->data ) ) {
            ?>
        <p><?php echo sprintf( __( 'No %s found', 'pods' ), $this->items ); ?></p>
        <?php
            return false;
        }
        if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
            ?>
        <style type="text/css">
            table.widefat.fixed tbody.reorderable tr {
                height: 50px;
            }

            .dragme {
                background: url(<?php echo esc_url( PODS_URL ); ?>/ui/images/handle.gif) no-repeat;
                background-position: 8px 8px;
                cursor: pointer;
            }

            .dragme strong {
                margin-left: 30px;
            }
        </style>
<form action="<?php echo esc_url( pods_query_arg( array( 'action' . $this->num => 'reorder', 'do' . $this->num => 'save', 'page' => pods_var_raw( 'page' ) ), self::$allowed, $this->exclusion() ) ); ?>" method="post" class="admin_ui_reorder_form">
<?php
        }
        $table_fields = $this->fields[ 'manage' ];
        if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] )
            $table_fields = $this->fields[ 'reorder' ];
        if ( false === $table_fields || empty( $table_fields ) )
            return $this->error( __( '<strong>Error:</strong> Invalid Configuration - Missing "fields" definition.', 'pods' ) );
        ?>
        <table class="widefat page fixed wp-list-table" cellspacing="0"<?php echo ( 1 == $reorder && $this->reorder ) ? ' id="admin_ui_reorder"' : ''; ?>>
            <thead>
                <tr>
                    <?php
                        if ( !empty( $this->actions_bulk ) ) {
            ?>
                            <th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
            <?php
                        }

                    $name_field = false;
                    $fields = array();
                    if ( !empty( $table_fields ) ) {
                        foreach ( $table_fields as $field => $attributes ) {
                            if ( false === $attributes[ 'display' ] )
                                continue;
                            if ( false === $name_field )
                                $id = 'title';
                            else
                                $id = '';
                            if ( 'other' == $attributes[ 'type' ] )
                                $id = '';
                            if ( in_array( $attributes[ 'type' ], array( 'date', 'datetime', 'time' ) ) )
                                $id = 'date';
                            if ( false === $name_field && 'title' == $id )
                                $name_field = true;
                            $fields[ $field ] = $attributes;
                            $fields[ $field ][ 'field_id' ] = $id;
                            $dir = 'DESC';
                            $current_sort = ' asc';
                            if ( isset( $this->orderby[ 'default' ] ) && $field == $this->orderby[ 'default' ] ) {
                                if ( 'DESC' == $this->orderby_dir ) {
                                    $dir = 'ASC';
                                    $current_sort = ' desc';
                                }
                            }

                            $att_id = '';
                            if ( !empty( $id ) )
                                $att_id = ' id="' . esc_attr( $id ) . '"';

                            $width = '';

                            if ( isset( $attributes[ 'width' ] ) && !empty( $attributes[ 'width' ] ) )
                                $width = ' style="width: ' . esc_attr( $attributes[ 'width' ] ) . '"';

                            if ( $fields[ $field ][ 'sortable' ] ) {
                                ?>
                                <th scope="col"<?php echo $att_id; ?> class="manage-column column-<?php echo esc_attr( $id ); ?> sortable<?php echo esc_attr( $current_sort ); ?>"<?php echo $width; ?>>
                                    <a href="<?php echo esc_url_raw( pods_query_arg( array( 'orderby' . $this->num => $field, 'orderby_dir' . $this->num => $dir ), array( 'limit' . $this->num, 'search' . $this->num, 'pg' . $this->num, 'page' ), $this->exclusion() ) ); ?>"> <span><?php echo $attributes[ 'label' ]; ?></span> <span class="sorting-indicator"></span> </a>
                                </th>
                                <?php
                            }
                            else {
                                ?>
                                <th scope="col"<?php echo $att_id; ?> class="manage-column column-<?php echo esc_attr( $id ); ?>"<?php echo $width; ?>><?php echo $attributes[ 'label' ]; ?></th>
                                <?php
                            }
                        }
                    }
                    ?>
                </tr>
            </thead>
            <?php
            if ( 6 < $this->total_found ) {
                ?>
                <tfoot>
                    <tr>
                        <?php
                            if ( !empty( $this->actions_bulk ) ) {
            ?>
                                <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
            <?php
                            }

                        if ( !empty( $fields ) ) {
                            foreach ( $fields as $field => $attributes ) {
                                $dir = 'ASC';
                                if ( $field == $this->orderby ) {
                                    $current_sort = 'desc';
                                    if ( 'ASC' == $this->orderby_dir ) {
                                        $dir = 'DESC';
                                        $current_sort = 'asc';
                                    }
                                }

                                $width = '';

                                if ( isset( $attributes[ 'width' ] ) && !empty( $attributes[ 'width' ] ) )
                                    $width = ' style="width: ' . esc_attr( $attributes[ 'width' ] ) . '"';

                                if ( $fields[ $field ][ 'sortable' ] ) {
                                    ?>
                                    <th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?> sortable <?php echo esc_attr( $current_sort ); ?>"<?php echo $width; ?>><a href="<?php echo esc_url_raw( pods_query_arg( array( 'orderby' . $this->num => $field, 'orderby_dir' . $this->num => $dir ), array( 'limit' . $this->num, 'search' . $this->num, 'pg' . $this->num, 'page' ), $this->exclusion() ) ); ?>"><span><?php echo $attributes[ 'label' ]; ?></span><span class="sorting-indicator"></span></a></th>
                                    <?php
                                }
                                else {
                                    ?>
                                    <th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?>"<?php echo $width; ?>><?php echo $attributes[ 'label' ]; ?></th>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </tr>
                </tfoot>
                <?php
            }
            ?>
            <tbody id="the-list"<?php echo ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) ? ' class="reorderable"' : ''; ?>>
                <?php
                if ( !empty( $this->data ) && is_array( $this->data ) ) {
                    $counter = 0;

                    while ( $row = $this->get_row( $counter, 'table' ) ) {
                        if ( is_object( $row ) )
                            $row = get_object_vars( (object) $row );

                        $toggle_class = '';

                        if ( is_array( $this->actions_custom ) && isset( $this->actions_custom[ 'toggle' ] ) ) {
                            $toggle_class = ' pods-toggled-on';

                            if ( !isset( $row[ 'toggle' ] ) || empty( $row[ 'toggle' ] ) )
                                $toggle_class = ' pods-toggled-off';
                        }
                        ?>
                        <tr id="item-<?php echo esc_attr( $row[ $this->sql[ 'field_id' ] ] ); ?>" class="iedit<?php echo esc_attr( $toggle_class ); ?>">
                            <?php
                                if ( !empty( $this->actions_bulk ) ) {
            ?>
                                <th scope="row" class="check-column"><input type="checkbox" name="action_bulk_ids<?php echo esc_attr( $this->num ); ?>[]" value="<?php echo esc_attr( $row[$this->sql['field_id']] ); ?>"></th>
            <?php
                                }

                            foreach ( $fields as $field => $attributes ) {
                                if ( false === $attributes[ 'display' ] )
                                    continue;

                                if ( !isset( $row[ $field ] ) ) {
                                    $row[ $field ] = $this->get_field( $field );
								}

								$row_value = $row[ $field ];

                                if ( !empty( $attributes[ 'custom_display' ] ) ) {
                                    if ( is_callable( $attributes[ 'custom_display' ] ) )
                                        $row_value = call_user_func_array( $attributes[ 'custom_display' ], array( $row, &$this, $row_value, $field, $attributes ) );
                                    elseif ( is_object( $this->pod ) && class_exists( 'Pods_Helpers' ) )
                                        $row_value = $this->pod->helper( $attributes[ 'custom_display' ], $row_value, $field );
                                }
                                else {
                                    ob_start();

                                    $field_value = PodsForm::field_method( $attributes[ 'type' ], 'ui', $this->id, $row_value, $field, array_merge( $attributes, pods_var_raw( 'options', $attributes, array(), null, true ) ), $fields, $this->pod );

                                    $field_output = trim( (string) ob_get_clean() );

                                    if ( false === $field_value )
                                        $row_value = '';
                                    elseif ( 0 < strlen( trim( (string) $field_value ) ) )
                                        $row_value = trim( (string) $field_value );
                                    elseif ( 0 < strlen( $field_output ) )
                                        $row_value = $field_output;
                                }

                                if ( false !== $attributes[ 'custom_relate' ] ) {
                                    global $wpdb;
                                    $table = $attributes[ 'custom_relate' ];
                                    $on = $this->sql[ 'field_id' ];
                                    $is = $row[ $this->sql[ 'field_id' ] ];
                                    $what = array( 'name' );
                                    if ( is_array( $table ) ) {
                                        if ( isset( $table[ 'on' ] ) )
                                            $on = pods_sanitize( $table[ 'on' ] );
                                        if ( isset( $table[ 'is' ] ) && isset( $row[ $table[ 'is' ] ] ) )
                                            $is = pods_sanitize( $row[ $table[ 'is' ] ] );
                                        if ( isset( $table[ 'what' ] ) ) {
                                            $what = array();
                                            if ( is_array( $table[ 'what' ] ) ) {
                                                foreach ( $table[ 'what' ] as $wha ) {
                                                    $what[] = pods_sanitize( $wha );
                                                }
                                            }
                                            else
                                                $what[] = pods_sanitize( $table[ 'what' ] );
                                        }
                                        if ( isset( $table[ 'table' ] ) )
                                            $table = $table[ 'table' ];
                                    }
                                    $table = pods_sanitize( $table );
                                    $wha = implode( ',', $what );
                                    $sql = "SELECT {$wha} FROM {$table} WHERE `{$on}`='{$is}'";
                                    $value = @current( $wpdb->get_results( $sql, ARRAY_A ) );
                                    if ( !empty( $value ) ) {
                                        $val = array();
                                        foreach ( $what as $wha ) {
                                            if ( isset( $value[ $wha ] ) )
                                                $val[] = $value[ $wha ];
                                        }
                                        if ( !empty( $val ) )
                                            $row_value = implode( ' ', $val );
                                    }
                                }

								$css_classes = ' pods-ui-col-field-' . sanitize_title( $field );

								if ( $attributes[ 'css_values' ] ) {
									$css_field_value = $row[ $field ];

									if ( is_object( $css_field_value ) ) {
										$css_field_value = get_object_vars( $css_field_value );
									}

									if ( is_array( $css_field_value ) ) {
										foreach ( $css_field_value as $css_field_val ) {
											if ( is_object( $css_field_val ) ) {
												$css_field_val = get_object_vars( $css_field_val );
											}

											if ( is_array( $css_field_val ) ) {
												foreach ( $css_field_val as $css_field_v ) {
													if ( is_object( $css_field_v ) ) {
														$css_field_v = get_object_vars( $css_field_v );
													}

													$css_classes .= ' pods-ui-css-value-' . sanitize_title( str_replace( array( "\n", "\r" ), ' ', strip_tags( (string) $css_field_v ) ) );
												}
											}
											else {
												$css_classes .= ' pods-ui-css-value-' . sanitize_title( str_replace( array( "\n", "\r" ), ' ', strip_tags( (string) $css_field_val ) ) );
											}
										}
									}
									else {
										$css_classes .= ' pods-ui-css-value-' . sanitize_title( str_replace( array( "\n", "\r" ), ' ', strip_tags( (string) $css_field_value ) ) );
									}
								}

                                if ( is_object( $this->pod ) )
                                    $row_value = $this->do_hook( $this->pod->pod . '_field_value', $row_value, $field, $attributes, $row );

                                $row_value = $this->do_hook( 'field_value', $row_value, $field, $attributes, $row );

                                if ( 'title' == $attributes[ 'field_id' ] ) {
									$default_action = $this->do_hook( 'default_action', 'edit', $row );

                                    if ( !in_array( 'edit', $this->actions_disabled ) && !in_array( 'edit', $this->actions_hidden ) && ( false === $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder[ 'on' ] ) && 'edit' == $default_action ) {
                                        $link = pods_query_arg( array( 'action' . $this->num => 'edit', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), self::$allowed, $this->exclusion() );

                                        if ( !empty( $this->action_links[ 'edit' ] ) )
                                            $link = $this->do_template( $this->action_links[ 'edit' ], $row );
                                        ?>
                <td class="post-title page-title column-title<?php echo esc_attr( $css_classes ); ?>"><strong><a class="row-title" href="<?php echo esc_url_raw( $link ); ?>" title="<?php esc_attr_e( 'Edit this item', 'pods' ); ?>"><?php echo $row_value; ?></a></strong>
                                        <?php
                                    }
                                    elseif ( !in_array( 'view', $this->actions_disabled ) && !in_array( 'view', $this->actions_hidden ) && ( false === $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder[ 'on' ] ) && 'view' == $default_action ) {
                                        $link = pods_query_arg( array( 'action' . $this->num => 'view', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), self::$allowed, $this->exclusion() );

                                        if ( !empty( $this->action_links[ 'view' ] ) )
                                            $link = $this->do_template( $this->action_links[ 'view' ], $row );
                                        ?>
                <td class="post-title page-title column-title<?php echo esc_attr( $css_classes ); ?>"><strong><a class="row-title" href="<?php echo esc_url_raw( $link ); ?>" title="<?php esc_attr_e( 'View this item', 'pods' ); ?>"><?php echo $row_value; ?></a></strong>
                                        <?php
                                    }
                                    else {
                                        ?>
                <td class="post-title page-title column-title<?php echo esc_attr( $css_classes ); ?><?php echo esc_attr( ( 1 == $reorder && $this->reorder ) ? ' dragme' : '' ); ?>"><strong><?php echo $row_value; ?></strong>
                                        <?php
                                    }

                                    if ( true !== $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder[ 'on' ] ) {
                                        $toggle = false;
                                        $actions = array();

                                        if ( !in_array( 'view', $this->actions_disabled ) && !in_array( 'view', $this->actions_hidden ) ) {
                                            $link = pods_query_arg( array( 'action' . $this->num => 'view', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), self::$allowed, $this->exclusion() );

                                            if ( !empty( $this->action_links[ 'view' ] ) )
                                                $link = $this->do_template( $this->action_links[ 'view' ], $row );

                                            $actions[ 'view' ] = '<span class="view"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'View this item', 'pods' ) . '">' . __( 'View', 'pods' ) . '</a></span>';
                                        }

                                        if ( !in_array( 'edit', $this->actions_disabled ) && !in_array( 'edit', $this->actions_hidden ) && !$this->restricted( 'edit', $row ) ) {
                                            $link = pods_query_arg( array( 'action' . $this->num => 'edit', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), self::$allowed, $this->exclusion() );

                                            if ( !empty( $this->action_links[ 'edit' ] ) )
                                                $link = $this->do_template( $this->action_links[ 'edit' ], $row );

                                            $actions[ 'edit' ] = '<span class="edit"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'Edit this item', 'pods' ) . '">' . __( 'Edit', 'pods' ) . '</a></span>';
                                        }

                                        if ( !in_array( 'duplicate', $this->actions_disabled ) && !in_array( 'duplicate', $this->actions_hidden ) && !$this->restricted( 'edit', $row ) ) {
                                            $link = pods_query_arg( array( 'action' . $this->num => 'duplicate', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), self::$allowed, $this->exclusion() );

                                            if ( !empty( $this->action_links[ 'duplicate' ] ) )
                                                $link = $this->do_template( $this->action_links[ 'duplicate' ], $row );

                                            $actions[ 'duplicate' ] = '<span class="edit"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'Duplicate this item', 'pods' ) . '">' . __( 'Duplicate', 'pods' ) . '</a></span>';
                                        }

                                        if ( !in_array( 'delete', $this->actions_disabled ) && !in_array( 'delete', $this->actions_hidden ) && !$this->restricted( 'delete', $row ) ) {
                                            $link = pods_query_arg( array( 'action' . $this->num => 'delete', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ], '_wpnonce' => wp_create_nonce( 'pods-ui-action-delete' ) ), self::$allowed, $this->exclusion() );

                                            if ( !empty( $this->action_links[ 'delete' ] ) ) {
	                                            $link = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'pods-ui-action-delete' ) ), $this->do_template( $this->action_links[ 'delete' ], $row ) );
                                            }

                                            $actions[ 'delete' ] = '<span class="delete"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'Delete this item', 'pods' ) . '" class="submitdelete" onclick="if(confirm(\'' . esc_attr__( 'You are about to permanently delete this item\n Choose \\\'Cancel\\\' to stop, \\\'OK\\\' to delete.', 'pods' ) . '\')){return true;}return false;">' . __( 'Delete', 'pods' ) . '</a></span>';
                                        }

                                        if ( is_array( $this->actions_custom ) ) {
                                            foreach ( $this->actions_custom as $custom_action => $custom_data ) {
												if ( 'add' != $custom_action && is_array( $custom_data ) && ( isset( $custom_data[ 'link' ] ) || isset( $custom_data[ 'callback' ] ) ) && !in_array( $custom_action, $this->actions_disabled ) && !in_array( $custom_action, $this->actions_hidden ) ) {
                                                    if ( !in_array( $custom_action, array( 'add', 'view', 'edit', 'duplicate', 'delete', 'save', 'export', 'reorder', 'manage', 'table' ) ) ) {
                                                        if ( 'toggle' == $custom_action ) {
                                                            $toggle = true;
                                                            $toggle_labels = array(
                                                                __( 'Enable', 'pods' ),
                                                                __( 'Disable', 'pods' )
                                                            );

                                                            $custom_data[ 'label' ] = ( $row[ 'toggle' ] ? $toggle_labels[ 1 ] : $toggle_labels[ 0 ] );
                                                        }

                                                        if ( !isset( $custom_data[ 'label' ] ) )
                                                            $custom_data[ 'label' ] = ucwords( str_replace( '_', ' ', $custom_action ) );

                                                        if ( !isset( $custom_data[ 'link' ] ) ) {
                                                            $vars = array(
                                                                'action' => $custom_action,
                                                                'id' => $row[ $this->sql[ 'field_id' ] ],
                                                                '_wpnonce' => wp_create_nonce( 'pods-ui-action-' . $custom_action )
                                                            );

                                                            if ( 'toggle' == $custom_action ) {
                                                                $vars[ 'toggle' ] = (int) ( !$row[ 'toggle' ] );
                                                                $vars[ 'toggled' ] = 1;
                                                            }

                                                            $custom_data[ 'link' ] = pods_query_arg( $vars, self::$allowed, $this->exclusion() );

                                                            if ( isset( $this->action_links[ $custom_action ] ) && !empty( $this->action_links[ $custom_action ] ) ) {
	                                                            $custom_data[ 'link' ] = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'pods-ui-action-' . $custom_action ) ), $this->do_template( $this->action_links[ $custom_action ], $row ) );
                                                            }
                                                        }

                                                        $confirm = '';

                                                        if ( isset( $custom_data[ 'confirm' ] ) )
                                                            $confirm = ' onclick="if(confirm(\'' . esc_js( $custom_data[ 'confirm' ] ) . '\')){return true;}return false;"';

                                                        if ( $this->restricted( $custom_action, $row ) ) {
                                                            continue;
														}

                                                        $actions[ $custom_action ] = '<span class="edit action-' . esc_attr( $custom_action ) . '"><a href="' . esc_url( $this->do_template( $custom_data[ 'link' ], $row ) ) . '" title="' . esc_attr( $custom_data[ 'label' ] ) . ' this item"' . $confirm . '>' . $custom_data[ 'label' ] . '</a></span>';
                                                    }
                                                }
                                            }
                                        }

                                        $actions = $this->do_hook( 'row_actions', $actions, $row[ $this->sql[ 'field_id' ] ] );

                                        if ( !empty( $actions ) ) {
                                            ?>
                                            <div class="row-actions<?php echo esc_attr( $toggle ? ' row-actions-toggle' : '' ); ?>">
                                                <?php
													$this->callback( 'actions_start', $row, $actions );

													echo implode( ' | ', $actions );

													$this->callback( 'actions_end', $row, $actions );
                                                ?>
                                            </div>
                                            <?php
                                        }
                                    }
                                    else {
                                        ?>
                                        <input type="hidden" name="order[]" value="<?php echo esc_attr( $row[ $this->sql[ 'field_id' ] ] ); ?>" />
                                        <?php
                                    }
                                    ?>
                </td>
<?php
                                }
                                elseif ( 'date' == $attributes[ 'type' ] ) {
                                    ?>
                                    <td class="date column-date<?php echo esc_attr( $css_classes ); ?>"><abbr title="<?php echo esc_attr( $row_value ); ?>"><?php echo $row_value; ?></abbr></td>
                                    <?php
                                }
                                else {
                                    ?>
                                    <td class="author<?php echo esc_attr( $css_classes ); ?>"><span><?php echo $row_value; ?></span></td>
                                    <?php
                                }
                            }
                            ?>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
        if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
            ?>
</form>
<?php
        }
        ?>
    <script type="text/javascript">
        jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
            <?php
            if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
                ?>
            jQuery( document ).ready( function () {
                jQuery( ".reorderable" ).sortable( {axis : "y", handle : ".dragme"} );
                jQuery( ".reorderable" ).bind( 'sortupdate', function ( event, ui ) {
                    jQuery( 'table.widefat tbody tr' ).removeClass( 'alternate' );
                    jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
                } );
            } );
                <?php
            }
            ?>
    </script>
    <?php
    }

    /**
     *
     */
    public function screen_meta () {
        $screen_html = $help_html = '';
        $screen_link = $help_link = '';
        if ( !empty( $this->screen_options ) && !empty( $this->help ) ) {
            foreach ( $this->ui_page as $page ) {
                if ( isset( $this->screen_options[ $page ] ) ) {
                    if ( is_array( $this->screen_options[ $page ] ) ) {
                        if ( isset( $this->screen_options[ $page ][ 'link' ] ) ) {
                            $screen_link = $this->screen_options[ $page ][ 'link' ];
                            break;
                        }
                    }
                    else {
                        $screen_html = $this->screen_options[ $page ];
                        break;
                    }
                }
            }
            foreach ( $this->ui_page as $page ) {
                if ( isset( $this->help[ $page ] ) ) {
                    if ( is_array( $this->help[ $page ] ) ) {
                        if ( isset( $this->help[ $page ][ 'link' ] ) ) {
                            $help_link = $this->help[ $page ][ 'link' ];
                            break;
                        }
                    }
                    else {
                        $help_html = $this->help[ $page ];
                        break;
                    }
                }
            }
        }
        $screen_html = $this->do_hook( 'screen_meta_screen_html', $screen_html );
        $screen_link = $this->do_hook( 'screen_meta_screen_link', $screen_link );
        $help_html = $this->do_hook( 'screen_meta_help_html', $help_html );
        $help_link = $this->do_hook( 'screen_meta_help_link', $help_link );
        if ( 0 < strlen( $screen_html ) || 0 < strlen( $screen_link ) || 0 < strlen( $help_html ) || 0 < strlen( $help_link ) ) {
            ?>
        <div id="screen-meta">
            <?php
            $this->do_hook( 'screen_meta_pre' );
            if ( 0 < strlen( $screen_html ) ) {
                ?>
                <div id="screen-options-wrap" class="hidden">
                    <form id="adv-settings" action="" method="post">
                        <?php
                        echo $screen_html;
                        $fields = array();
                        foreach ( $this->ui_page as $page ) {
                            if ( isset( $this->fields[ $page ] ) && !empty( $this->fields[ $page ] ) )
                                $fields = $this->fields[ $page ];
                        }
                        if ( !empty( $fields ) || true === $this->pagination ) {
                            ?>
                            <h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
                            <?php
                            if ( !empty( $fields ) ) {
                                ?>
                                <div class="metabox-prefs">
                                    <?php
                                    $this->do_hook( 'screen_meta_screen_options' );
                                    foreach ( $fields as $field => $attributes ) {
                                        if ( false === $attributes[ 'display' ] || true === $attributes[ 'hidden' ] )
                                            continue;
                                        ?>
                                        <label for="<?php echo esc_attr( $field ); ?>-hide">
                                            <input class="hide-column-tog" name="<?php echo esc_attr( $this->unique_identifier ); ?>_<?php echo esc_attr( $field ); ?>-hide" type="checkbox" id="<?php echo esc_attr( $field ); ?>-hide" value="<?php echo esc_attr( $field ); ?>" checked="checked"><?php echo $attributes[ 'label' ]; ?></label>
                                        <?php
                                    }
                                    ?>
                                    <br class="clear">
                                </div>
                                <h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
                                <?php
                            }
                            ?>
                            <div class="screen-options">
                                <?php
                                if ( true === $this->pagination ) {
                                    ?>
                                    <input type="text" class="screen-per-page" name="wp_screen_options[value]" id="<?php echo esc_attr( $this->unique_identifier ); ?>_per_page" maxlength="3" value="20"> <label for="<?php echo esc_attr( $this->unique_identifier ); ?>_per_page"><?php echo $this->items; ?> per page</label>
                                    <?php
                                }
                                $this->do_hook( 'screen_meta_screen_submit' );
                                ?>
                                <input type="submit" name="screen-options-apply" id="screen-options-apply" class="button" value="<?php esc_attr_e( 'Apply', 'pods' ); ?>">
                                <input type="hidden" name="wp_screen_options[option]" value="<?php echo esc_attr( $this->unique_identifier ); ?>_per_page">
                                <?php wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ); ?>
                            </div>
                            <?php
                        }
                        ?>
                    </form>
                </div>
                <?php
            }
            if ( 0 < strlen( $help_html ) ) {
                ?>
                <div id="contextual-help-wrap" class="hidden">
                    <div class="metabox-prefs">
                        <?php echo $help_html; ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <div id="screen-meta-links">
                <?php
                $this->do_hook( 'screen_meta_links_pre' );
                if ( 0 < strlen( $help_html ) || 0 < strlen( $help_link ) ) {
                    ?>
                    <div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle">
                        <?php
                        if ( 0 < strlen( $help_link ) ) {
                            ?>
                            <a href="<?php echo esc_url( $help_link ); ?>" class="show-settings">Help</a>
                            <?php
                        }
                        else {
                            ?>
                            <a href="#contextual-help" id="contextual-help-link" class="show-settings">Help</a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                if ( 0 < strlen( $screen_html ) || 0 < strlen( $screen_link ) ) {
                    ?>
                    <div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">
                        <?php
                        if ( 0 < strlen( $screen_link ) ) {
                            ?>
                            <a href="<?php echo esc_url( $screen_link ); ?>" class="show-settings">Screen Options</a>
                            <?php
                        }
                        else {
                            ?>
                            <a href="#screen-options" id="show-settings-link" class="show-settings">Screen Options</a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                $this->do_hook( 'screen_meta_links_post' );
                ?>
            </div>
            <?php
            $this->do_hook( 'screen_meta_post' );
            ?>
        </div>
        <?php
        }
    }

    /**
     * @param bool $header
     *
     * @return mixed
     */
    public function pagination ( $header = false ) {
		if ( false !== $this->callback( 'pagination', $header ) ) {
			return null;
		}

        $total_pages = ceil( $this->total_found / $this->limit );
        $request_uri = pods_query_arg( array( 'pg' . $this->num => '' ), array( 'limit' . $this->num, 'orderby' . $this->num, 'orderby_dir' . $this->num, 'search' . $this->num, 'filter_*', 'view' . $this->num, 'page' . $this->num ), $this->exclusion() );

        $append = false;

        if ( false !== strpos( $request_uri, '?' ) )
            $append = true;

        if ( false !== $this->pagination_total && ( $header || 1 != $this->total_found ) ) {
            $singular_label = strtolower( $this->item );
            $plural_label = strtolower( $this->items );
            ?>
        <span class="displaying-num"><?php echo number_format_i18n( $this->total_found ) . ' ' . _n( $singular_label, $plural_label, $this->total_found, 'pods' ) . $this->extra[ 'total' ]; ?></span>
        <?php
        }

        if ( false !== $this->pagination ) {
            if ( 1 < $total_pages ) {
                ?>
            <a class="first-page<?php echo esc_attr( ( 1 < $this->page ) ? '' : ' disabled' ); ?>" title="<?php esc_attr_e( 'Go to the first page', 'pods' ); ?>" href="<?php echo esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=1' ); ?>">&laquo;</a>
            <a class="prev-page<?php echo esc_attr( ( 1 < $this->page ) ? '' : ' disabled' ); ?>" title="<?php esc_attr_e( 'Go to the previous page', 'pods' ); ?>" href="<?php echo esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=' . max( $this->page - 1, 1 ) ); ?>">&lsaquo;</a>
            <?php
                if ( true == $header ) {
                    ?>
                <span class="paging-input"><input class="current-page" title="<?php esc_attr_e( 'Current page', 'pods' ); ?>" type="text" name="pg<?php echo esc_attr( $this->num ); ?>" value="<?php echo esc_attr( $this->page ); ?>" size="<?php echo esc_attr( strlen( $total_pages ) ); ?>"> <?php _e( 'of', 'pods' ); ?> <span class="total-pages"><?php echo $total_pages; ?></span></span>
                <script>

                    jQuery( document ).ready( function ( $ ) {
                        var pageInput = $( 'input.current-page' );
                        var currentPage = pageInput.val();
                        pageInput.closest( 'form' ).submit( function ( e ) {
                            if (
	                            ( 1 > $( 'select[name="action<?php echo esc_attr( $this->num ); ?>"]' ).length
	                              || $( 'select[name="action<?php echo esc_attr( $this->num ); ?>"]' ).val() == -1 )
	                            && ( 1 > $( 'select[name="action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).length
	                                 || $( 'select[name="action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).val() == -1 )
	                            && pageInput.val() == currentPage ) {
                                pageInput.val( '1' );
                            }
                        } );
                    } );
                </script>
                <?php
                }
                else {
                    ?>
                <span class="paging-input"><?php echo $this->page; ?> <?php _e( 'of', 'pods' ); ?> <span class="total-pages"><?php echo number_format_i18n( $total_pages ); ?></span></span>
                <?php
                }
                ?>
            <a class="next-page<?php echo esc_attr( ( $this->page < $total_pages ) ? '' : ' disabled' ); ?>" title="<?php esc_attr_e( 'Go to the next page', 'pods' ); ?>" href="<?php echo esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=' . min( $this->page + 1, $total_pages ) ); ?>">&rsaquo;</a>
            <a class="last-page<?php echo esc_attr( ( $this->page < $total_pages ) ? '' : ' disabled' ); ?>" title="<?php esc_attr_e( 'Go to the last page', 'pods' ); ?>'" href="<?php echo esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=' . $total_pages ); ?>">&raquo;</a>
            <?php
            }
        }
    }

    /**
     * @param bool $options
     *
     * @return mixed
     */
    public function limit ( $options = false ) {
		if ( false !== $this->callback( 'limit', $options ) ) {
			return null;
		}

        if ( false === $options || !is_array( $options ) || empty( $options ) )
            $options = array( 10, 25, 50, 100, 200 );

        if ( !in_array( $this->limit, $options ) && -1 != $this->limit )
            $this->limit = $options[ 1 ];

        foreach ( $options as $option ) {
            if ( $option == $this->limit )
                echo ' <span class="page-numbers current">' . esc_html( $option ) . '</span>';
            else
                echo ' <a href="' . esc_url( pods_query_arg( array( 'limit' => $option ), array( 'orderby' . $this->num, 'orderby_dir' . $this->num, 'search' . $this->num, 'filter_*', 'page' . $this->num ), $this->exclusion() ) ) . '">' . esc_html( $option ) . '</a>';
        }
    }

    /**
     * @param $code
     * @param bool|array $row
     *
     * @return mixed
     */
    public function do_template ( $code, $row = false ) {
        if ( is_object( $this->pod ) && 1 == 0 && 0 < $this->pod->id() )
            return $this->pod->do_magic_tags( $code );
        else {
            if ( false !== $row ) {
                $this->temp_row = $this->row;
                $this->row = $row;
            }

            $code = preg_replace_callback( "/({@(.*?)})/m", array( $this, "do_magic_tags" ), $code );

            if ( false !== $row ) {
                $this->row = $this->temp_row;
                unset( $this->temp_row );
            }
        }

        return $code;
    }

    /**
     * @param $tag
     *
     * @return string
     */
    public function do_magic_tags ( $tag ) {
        if ( is_array( $tag ) ) {
            if ( !isset( $tag[ 2 ] ) && strlen( trim( $tag[ 2 ] ) ) < 1 )
                return '';

            $tag = $tag[ 2 ];
        }

        $tag = trim( $tag, ' {@}' );
        $tag = explode( ',', $tag );

        if ( empty( $tag ) || !isset( $tag[ 0 ] ) || strlen( trim( $tag[ 0 ] ) ) < 1 )
            return null;

        foreach ( $tag as $k => $v ) {
            $tag[ $k ] = trim( $v );
        }

        $field_name = $tag[ 0 ];

        $value = $this->get_field( $field_name );

        if ( isset( $tag[ 1 ] ) && !empty( $tag[ 1 ] ) && is_callable( $tag[ 1 ] ) )
            $value = call_user_func_array( $tag[ 1 ], array( $value, $field_name, $this->row, &$this ) );

        $before = $after = '';

        if ( isset( $tag[ 2 ] ) && !empty( $tag[ 2 ] ) )
            $before = $tag[ 2 ];

        if ( isset( $tag[ 3 ] ) && !empty( $tag[ 3 ] ) )
            $after = $tag[ 3 ];

        if ( 0 < strlen( $value ) )
            return $before . $value . $after;

        return null;
    }

    /**
     * @param bool|array $exclude
     * @param bool|array $array
     */
    public function hidden_vars ( $exclude = false, $array = false ) {
        $exclude = $this->do_hook( 'hidden_vars', $exclude, $array );
        if ( false === $exclude )
            $exclude = array();
        if ( !is_array( $exclude ) )
            $exclude = explode( ',', $exclude );
        $get = $_GET;
        if ( is_array( $array ) ) {
            foreach ( $array as $key => $val ) {
                if ( 0 < strlen( $val ) )
                    $get[ $key ] = $val;
                else
                    unset( $get[ $key ] );
            }
        }
        foreach ( $get as $k => $v ) {
            if ( in_array( $k, $exclude ) )
                continue;

            if ( is_array( $v ) ) {
                foreach ( $v as $vk => $vv ) {
?>
    <input type="hidden" name="<?php echo esc_attr( $k ); ?>[<?php echo esc_attr( $vk ); ?>]" value="<?php echo esc_attr( $vv ); ?>" />
<?php
               }
            }
            else {
?>
    <input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
<?php
            }
        }
    }

    /**
     * @return array
     */
    public function exclusion () {
        $exclusion = self::$excluded;

        foreach ( $exclusion as $k => $exclude ) {
            $exclusion[ $k ] = $exclude . $this->num;
        }

        return $exclusion;
    }

    public function restricted ( $action = 'edit', $row = null ) {
        $restricted = false;

        $restrict = array();

        if ( isset( $this->restrict[ $action ] ) )
            $restrict = (array) $this->restrict[ $action ];

        // @todo Build 'edit', 'duplicate', 'delete' action support for 'where' which runs another find() query
        /*if ( !in_array( $action, array( 'manage', 'reorder' ) ) ) {
            $where = pods_var_raw( $action, $this->where, null, null, true );

            if ( !empty( $where ) ) {
                $restricted = true;

                $old_where = $this->where[ $action ];

                $id = $this->row[ $this->sql[ 'field_id' ] ];

                if ( is_array( $where ) ) {
                    if ( 'OR' == pods_var( 'relation', $where ) )
                        $where = array( $where );

                    $where[] = "`t`.`" . $this->sql[ 'field_id' ] . "` = " . (int) $id;
                }
                else
                    $where = "( {$where} ) AND `t`.`" . $this->sql[ 'field_id' ] . "` = " . (int) $id;

                $this->where[ $action ] = $where;

                $data = false;

                //$data = $this->get_data();

                $this->where[ $action ] = $old_where;

                if ( empty( $data ) )
                    $restricted = true;
            }
        }*/

        $author_restrict = false;

        if ( !empty( $this->restrict[ 'author_restrict' ] ) && $restrict === $this->restrict[ 'author_restrict' ] ) {
            $restricted = false;

            $author_restrict = true;

            if ( is_object( $this->pod ) ) {
                $restricted = true;

                if ( 'settings' == $this->pod->pod_data[ 'type' ] && 'add' == $action )
                    $action = 'edit';

                if ( pods_is_admin( array( 'pods', 'pods_content' ) ) )
                    $restricted = false;
                elseif ( 'manage' == $action ) {
                    if ( !in_array( 'edit', $this->actions_disabled ) && current_user_can( 'pods_edit_' . $this->pod->pod ) && current_user_can( 'pods_edit_others_' . $this->pod->pod ) )
                        $restricted = false;
                    elseif ( !in_array( 'delete', $this->actions_disabled ) && current_user_can( 'pods_delete_' . $this->pod->pod ) && current_user_can( 'pods_delete_others_' . $this->pod->pod ) )
                        $restricted = false;
                    elseif ( current_user_can( 'pods_' . $action . '_' . $this->pod->pod ) && current_user_can( 'pods_' . $action . '_others_' . $this->pod->pod ) )
                        $restricted = false;
                }
                elseif ( current_user_can( 'pods_' . $action . '_' . $this->pod->pod ) && current_user_can( 'pods_' . $action . '_others_' . $this->pod->pod ) )
                    $restricted = false;
            }
            /* @todo determine proper logic for non-pods capabilities
            else {
                $restricted = true;

                if ( pods_is_admin( array( 'pods', 'pods_content' ) ) )
                    $restricted = false;
                elseif ( current_user_can( 'pods_' . $action . '_others_' . $_tbd ) )
                    $restricted = false;
            }*/
        }

        if ( $restricted && !empty( $restrict ) ) {
            $relation = strtoupper( trim( pods_var( 'relation', $restrict, 'AND', null, true ) ) );

            if ( 'AND' != $relation )
                $relation = 'OR';

            $okay = true;

            foreach ( $restrict as $field => $match ) {
                if ( 'relation' == $field )
                    continue;

                if ( is_array( $match ) ) {
                    $match_okay = true;

                    $match_relation = strtoupper( trim( pods_var( 'relation', $match, 'OR', null, true ) ) );

                    if ( 'AND' != $match_relation )
                        $match_relation = 'OR';

                    foreach ( $match as $the_field => $the_match ) {
                        if ( 'relation' == $the_field )
                            continue;

                        $value = null;

                        if ( is_object( $this->pod ) )
                            $value = $this->pod->field( $the_match, true );
                        else {
                            if ( empty( $row ) )
                                $row = $this->row;

                            if ( isset( $row[ $the_match ] ) ) {
                                if ( is_array( $row[ $the_match ] ) ) {
                                    if ( false !== strpos( $the_match, '.' ) ) {
                                        $the_matches = explode( '.', $the_match );

                                        $value = $row[ $the_match ];

                                        foreach ( $the_matches as $m ) {
                                            if ( is_array( $value ) && isset( $value[ $m ] ) )
                                                $value = $value[ $m ];
                                            else {
                                                $value = null;

                                                break;
                                            }
                                        }
                                    }
                                }
                                else
                                    $value = $row[ $the_match ];
                            }
                        }

                        if ( is_array( $value ) ) {
                            if ( !in_array( $the_match, $value ) )
                                $match_okay = false;
                            elseif ( 'OR' == $match_relation ) {
                                $match_okay = true;

                                break;
                            }
                        }
                        elseif ( $value == $the_match )
                            $match_okay = false;
                        elseif ( 'OR' == $match_relation ) {
                            $match_okay = true;

                            break;
                        }
                    }

                    if ( !$match_okay )
                        $okay = false;
                    if ( 'OR' == $relation ) {
                        $okay = true;

                        break;
                    }
                }
                else {
                    $value = null;

                    if ( is_object( $this->pod ) )
                        $value = $this->pod->field( $match, true );
                    else {
                        if ( empty( $row ) )
                            $row = $this->row;

                        if ( isset( $row[ $match ] ) ) {
                            if ( is_array( $row[ $match ] ) ) {
                                if ( false !== strpos( $match, '.' ) ) {
                                    $matches = explode( '.', $match );

                                    $value = $row[ $match ];

                                    foreach ( $matches as $m ) {
                                        if ( is_array( $value ) && isset( $value[ $m ] ) )
                                            $value = $value[ $m ];
                                        else {
                                            $value = null;

                                            break;
                                        }
                                    }
                                }
                            }
                            else
                                $value = $row[ $match ];
                        }
                    }

                    if ( is_array( $value ) ) {
                        if ( !in_array( $match, $value ) )
                            $okay = false;
                        elseif ( 'OR' == $relation ) {
                            $okay = true;

                            break;
                        }
                    }
                    elseif ( $value != $match )
                        $okay = false;
                    elseif ( 'OR' == $relation ) {
                        $okay = true;

                        break;
                    }
                }
            }

            if ( !empty( $author_restrict ) ) {
                if ( is_object( $this->pod ) && 'manage' == $action ) {
                    if ( !in_array( 'edit', $this->actions_disabled ) && !current_user_can( 'pods_edit_' . $this->pod->pod ) && !in_array( 'delete', $this->actions_disabled ) && !current_user_can( 'pods_delete_' . $this->pod->pod ) )
                        $okay = false;
                }
                if ( is_object( $this->pod ) && !current_user_can( 'pods_' . $action . '_' . $this->pod->pod ) )
                    $okay = false;
                /* @todo determine proper logic for non-pods capabilities
                elseif ( !current_user_can( 'pods_' . $action . '_' . $_tbd ) )
                    $okay = false;*/

                if ( !$okay && !empty( $row ) ) {
                    foreach ( $this->restrict[ 'author_restrict' ] as $key => $val ) {
                        $author_restricted = $this->get_field( $key );

                        if ( !empty( $author_restricted ) ) {
                            if ( !is_array( $author_restricted ) )
                                $author_restricted = (array) $author_restricted;

                            if ( is_array( $val ) ) {
                                foreach ( $val as $v ) {
                                    if ( in_array( $v, $author_restricted ) )
                                        $okay = true;
                                }
                            }
                            elseif ( in_array( $val, $author_restricted ) )
                                $okay = true;
                        }
                    }
                }
            }

            if ( $okay )
                $restricted = false;
        }

		if ( isset( $this->actions_custom[ $action ] ) && is_array( $this->actions_custom[ $action ] ) && isset( $this->actions_custom[ $action ][ 'restrict_callback' ] ) && is_callable( $this->actions_custom[ $action ][ 'restrict_callback' ] ) ) {
			$restricted = call_user_func( $this->actions_custom[ $action ][ 'restrict_callback' ], $restricted, $restrict, $action, $row, $this );
		}

        $restricted = $this->do_hook( 'restricted_' . $action, $restricted, $restrict, $action, $row );

        return $restricted;
    }

	/**
	 * Check for a custom action callback and run it
	 *
	 * @return bool|mixed
	 */
	public function callback() {

		$args = func_get_args();

		if ( empty( $args ) ) {
			return false;
		}

		$action = array_shift( $args );

		// Do hook
		$callback_args = $args;
		array_unshift( $callback_args, null );
		array_unshift( $callback_args, $action );

		$callback = call_user_func_array( array( $this, 'do_hook' ), $callback_args );

		if ( null === $callback ) {
			$callback = false;
		}

		$args[] = $this;

		if ( isset( $this->actions_custom[ $action ] ) ) {
			if ( is_array( $this->actions_custom[ $action ] ) && isset( $this->actions_custom[ $action ][ 'callback' ] ) && is_callable( $this->actions_custom[ $action ][ 'callback' ] ) ) {
				$callback = call_user_func_array( $this->actions_custom[ $action ][ 'callback' ], $args );
			}
			elseif ( is_callable( $this->actions_custom[ $action ] ) ) {
				$callback = call_user_func_array( $this->actions_custom[ $action ], $args );
			}
		}

		return $callback;

	}

	/**
	 * Check for a custom action callback and run it (deprecated reverse arg order)
	 *
	 * @return bool|mixed
	 */
	public function callback_action() {

		$args = func_get_args();

		if ( empty( $args ) ) {
			return false;
		}

		$action = array_shift( $args );

		$deprecated = false;

		if ( is_bool( $action ) ) {
			$deprecated = $action;

			$action = array_shift( $args );
		}

		// Do hook
		$callback_args = $args;
		array_unshift( $callback_args, null );
		array_unshift( $callback_args, 'action_' . $action );

		$callback = call_user_func_array( array( $this, 'do_hook' ), $callback_args );

		if ( null === $callback ) {
			$callback = false;
		}

		$args[] = $this;

		// Deprecated reverse arg order
		if ( $deprecated ) {
			$args = array_reverse( $args );
		}

		if ( isset( $this->actions_custom[ $action ] ) ) {
			if ( is_array( $this->actions_custom[ $action ] ) && isset( $this->actions_custom[ $action ][ 'callback' ] ) && is_callable( $this->actions_custom[ $action ][ 'callback' ] ) ) {
				$callback = call_user_func_array( $this->actions_custom[ $action ][ 'callback' ], $args );
			}
			elseif ( is_callable( $this->actions_custom[ $action ] ) ) {
				$callback = call_user_func_array( $this->actions_custom[ $action ], $args );
			}
		}

		return $callback;

	}

	/**
	 * Check for a bulk action callback and run it
	 *
	 * @return bool|mixed Callback result
	 */
	public function callback_bulk() {

		$args = func_get_args();

		if ( empty( $args ) ) {
			return false;
		}

		$action = array_shift( $args );

		$deprecated = false;

		if ( is_bool( $action ) ) {
			$deprecated = $action;

			$action = array_shift( $args );
		}

		// Do hook
		$callback_args = $args;
		array_unshift( $callback_args, null );
		array_unshift( $callback_args, 'bulk_action_' . $action );

		$callback = call_user_func_array( array( $this, 'do_hook' ), $callback_args );

		if ( null === $callback ) {
			$callback = false;
		}

		$args[] = $this;

		// Deprecated reverse arg order
		if ( $deprecated ) {
			$args = array_reverse( $args );
		}

		if ( isset( $this->actions_bulk[ $action ] ) ) {
			if ( is_array( $this->actions_bulk[ $action ] ) && isset( $this->actions_bulk[ $action ][ 'callback' ] ) && is_callable( $this->actions_bulk[ $action ][ 'callback' ] ) ) {
				$callback = call_user_func_array( $this->actions_bulk[ $action ][ 'callback' ], $args );
			}
			elseif ( is_callable( $this->actions_bulk[ $action ] ) ) {
				$callback = call_user_func_array( $this->actions_bulk[ $action ], $args );
			}
		}

		return $callback;

	}

    /*
        // Example code for use with $this->do_hook
        public function my_filter_function ($args, $obj) {
            $obj[0]->item = 'Post';
            $obj[0]->add = true;
            // args are an array (0 => $arg1, 1 => $arg2)
            // may have more than one arg, dependant on filter
            return $args;
        }
        add_filter('pods_ui_post_init', 'my_filter_function', 10, 2);
    */
    /**
     * @return array|bool|mixed|null
     */
	private function do_hook() {

		$args = func_get_args();

		if ( empty( $args ) ) {
			return false;
		}

		$name = array_shift( $args );

		return pods_do_hook( "ui", $name, $args, $this );

	}
}
