<?php

use Pods\Whatsit\Field;

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
		// 'view',
		'delete',
		'reorder',
		'export',
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
	 * The prefix used for all URL parameters used by PodsUI.
	 *
	 * @since 2.7.28
	 *
	 * @var string
	 */
	public $num_prefix = '';

	/**
	 * Allows multiple co-existing PodsUI instances with separate functionality in URL.
	 *
	 * @var string
	 */
	public $num = '';

	/**
	 * @var array
	 */
	public static $excluded = array(
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
		'message',
	);
	// used in var_update
	public static $allowed = array(
		'page',
		'post_type',
	);

	// ui
	/**
	 * @var bool
	 */
	public $item = false;
	// to be set with localized string
	/**
	 * @var bool
	 */
	public $items = false;
	// to be set with localized string
	/**
	 * @var bool
	 */
	public $heading = false;
	// to be set with localized string array
	/**
	 * @var bool
	 */
	public $header = false;
	// to be set with localized string array
	/**
	 * @var bool
	 */
	public $label = false;
	// to be set with localized string array
	/**
	 * @var bool
	 */
	public $icon = false;

	/**
	 * @var bool
	 */
	public $css = false;
	// set to a URL of stylesheet to include
	/**
	 * @var bool
	 */
	public $wpcss = false;
	// set to true to include WP Admin stylesheets
	/**
	 * @var array
	 */
	public $fields = array(
		'manage'    => array(),
		'search'    => array(),
		'sort'      => array(),
		'form'      => array(),
		'add'       => array(),
		'edit'      => array(),
		'duplicate' => array(),
		'view'      => array(),
		'reorder'   => array(),
		'export'    => array(),
	);

	/**
	 * Which field sets haven set up.
	 *
	 * @since 2.7.28
	 *
	 * @var array
	 */
	public $fields_setup = array();

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
	 * The type of pagination to use.
	 *
	 * @since 2.7.28
	 *
	 * @var string
	 */
	public $pagination_type = 'table';

	/**
	 * The location of where to show pagination.
	 *
	 * @since 2.7.28
	 *
	 * @var string
	 */
	public $pagination_location = 'both';

	/**
	 * @var bool
	 */
	public $pagination_total = true;

	/**
	 * @var array
	 */
	public $export = array(
		'on'      => false,
		'formats' => array(
			'csv'  => ',',
			'tsv'  => "\t",
			'xml'  => false,
			'json' => false,
		),
		'url'     => false,
		'type'    => false,
	);

	/**
	 * @var array
	 */
	public $reorder = array(
		'on'          => false,
		'limit'       => 250,
		'orderby'     => false,
		'orderby_dir' => 'ASC',
		'sql'         => null,
	);

	/**
	 * @var array
	 */
	public $screen_options = array();
	// set to 'page' => 'Text'; false hides link
	/**
	 * @var array
	 */
	public $help = array();
	// set to 'page' => 'Text'; 'page' => array('link' => 'yourhelplink'); false hides link
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
		'manage'  => null,
		/*
		'edit' => null,
        'duplicate' => null,
        'delete' => null,*/
		'reorder' => null,
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
		'filters',
	);
	// allowed: search, filters, show_per_page, orderby (priority over usermeta)
	/**
	 * @var array
	 */
	public $user = array(
		'show_per_page',
		'orderby',
	);
	// allowed: search, filters, show_per_page, orderby (priority under session)
	// advanced data
	/**
	 * @var array
	 */
	public $sql = array(
		'table'       => null,
		'field_id'    => 'id',
		'field_index' => 'name',
		'select'      => null,
		'sql'         => null,
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

	/**
	 * @var array
	 */
	public $temp_row = array();

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
		'add'       => 'edit',
		'edit'      => 'edit',
		'duplicate' => 'edit',
	);
	// set action to 'manage'
	/**
	 * @var bool
	 */
	public $do = false;

	/**
	 * @var array
	 */
	public $action_links = array(
		'manage'    => null,
		'add'       => null,
		'edit'      => null,
		'duplicate' => null,
		'view'      => null,
		'delete'    => null,
		'reorder'   => null,
	);
	// custom links (ex. /my-link/{@id}/
	/**
	 * @var array
	 */
	public $actions_disabled = array(
		'view',
		'export',
	);
	// disable actions
	/**
	 * @var array
	 */
	public $actions_hidden = array();
	// hide actions to not show them but allow them
	/**
	 * @var array
	 */
	public $actions_custom = array();
	// overwrite existing actions or add your own
	/**
	 * @var array
	 */
	public $actions_bulk = array();
	// enabled bulk actions
	/**
	 * @var array
	 */
	public $restrict = array(
		'manage'          => null,
		'edit'            => null,
		'duplicate'       => null,
		'delete'          => null,
		'reorder'         => null,
		'author_restrict' => null,
	);

	/**
	 * @var array
	 */
	public $extra = array(
		'total' => null,
	);

	/**
	 * @var string
	 */
	public $style = 'post_type';

	/**
	 * Allow custom save handling for tables that aren't Pod-based.
	 *
	 * @var bool
	 */
	public $save = false;

	/**
	 * Generate UI for Data Management
	 *
	 * @param mixed $options Object, Array, or String containing Pod or Options to be used
	 *
	 * @return \PodsUI
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct( $options ) {

		$this->_nonce = pods_v( $this->num_prefix . '_wpnonce' . $this->num, 'request' );

		$object = null;

		if ( is_object( $options ) ) {
			$object  = $options;
			$options = array();

			if ( isset( $object->ui ) ) {
				$options = (array) $object->ui;

				unset( $object->ui );
			}

			if ( is_object( $object ) && ( 'Pods' == get_class( $object ) || 'Pod' == get_class( $object ) ) ) {
				$this->pod =& $object;
			}
		}

		if ( ! is_array( $options ) ) {
			// @todo need to come back to this and allow for multi-dimensional strings
			// like: option=value&option2=value2&option3=key[val],key2[val2]&option4=this,that,another
			if ( false !== strpos( $options, '=' ) || false !== strpos( $options, '&' ) ) {
				parse_str( $options, $options );
			} else {
				$options = array( 'pod' => $options );
			}
		}

		if ( ! is_object( $object ) && isset( $options['pod'] ) ) {
			if ( is_object( $options['pod'] ) ) {
				$this->pod = $options['pod'];
			} elseif ( isset( $options['id'] ) ) {
				$this->pod = pods_get_instance( $options['pod'], $options['id'] );
			} else {
				$this->pod = pods_get_instance( $options['pod'] );
			}

			unset( $options['pod'] );
		} elseif ( is_object( $object ) ) {
			$this->pod = $object;
		}

		if ( is_object( $this->pod ) && 'Pod' == get_class( $this->pod ) && is_object( $this->pod->_data ) ) {
			$this->pods_data =& $this->pod->_data;
		} elseif ( is_object( $this->pod ) && 'Pods' == get_class( $this->pod ) && is_object( $this->pod->data ) ) {
			$this->pods_data =& $this->pod->data;
		} elseif ( is_object( $this->pod ) ) {
			$this->pods_data = pods_data( $this->pod->pod );
		} elseif ( ! is_object( $this->pod ) ) {
			$this->pods_data = pods_data( $this->pod );
		}

		$options = $this->do_hook( 'pre_init', $options );

		$this->setup( $options );

		if ( is_object( $this->pods_data ) && is_object( $this->pod ) && 0 < $this->id ) {
			if ( $this->id != $this->pods_data->id ) {
				$this->row = $this->pods_data->fetch( $this->id );
			} else {
				$this->row = $this->pods_data->row;
			}
		}

		if ( ( ! is_object( $this->pod ) || 'Pods' != get_class( $this->pod ) ) && false === $this->sql['table'] && false === $this->data ) {
			echo $this->error( __( '<strong>Error:</strong> Pods UI needs a Pods object or a Table definition to run from, see the User Guide for more information.', 'pods' ) );

			return null;
		}

		// Assign pod labels
		// @todo This is also done in setup(), maybe a better / more central way?
		if ( is_object( $this->pod ) && ! empty( $this->pod->pod_data ) ) {
			$pod_data = $this->pod->pod_data;
			$pod_name = $this->pod->pod_data['name'];
			$pod_data = apply_filters( "pods_advanced_content_type_pod_data_{$pod_name}", $pod_data, $this->pod->pod_data['name'] );
			$pod_data = apply_filters( 'pods_advanced_content_type_pod_data', $pod_data, $this->pod->pod_data['name'] );

			$this->label = array_merge( $this->label, $pod_data['options'] );
		}

		$this->go();
	}

	/**
	 * @param $options
	 *
	 * @return array|bool|mixed|null|PodsArray
	 */
	public function setup( $options ) {

		$options = pods_array( $options );

		$options->validate( 'num_prefix', '' );
		$options->validate( 'num', '', 'absint' );

		if ( empty( $options->num_prefix ) ) {
			$options->num_prefix = '';
		}

		if ( empty( $options->num ) ) {
			$options->num = '';
		}

		$options->validate( 'id', pods_v( $options->num_prefix . 'id' . $options->num, 'get', $this->id ) );

		$options->validate(
			'do', pods_v( $options->num_prefix . 'do' . $options->num, 'get', $this->do ), 'in_array', array(
				'save',
				'create',
			)
		);

		$options->validate( 'excluded', self::$excluded, 'array_merge' );

		$options->validate( 'action', pods_v( $options->num_prefix . 'action' . $options->num, 'get', $this->action, true ), 'in_array', $this->actions );
		$options->validate( 'actions_bulk', $this->actions_bulk, 'array_merge' );
		$options->validate( 'action_bulk', pods_v( $options->num_prefix . 'action_bulk' . $options->num, 'get', $this->action_bulk, true ), 'isset', $this->actions_bulk );

		$bulk = pods_v( $options->num_prefix . 'action_bulk_ids' . $options->num, 'get', array(), true );

		if ( ! empty( $bulk ) ) {
			$bulk = (array) pods_v( $options->num_prefix . 'action_bulk_ids' . $options->num, 'get', array(), true );
		} else {
			$bulk = array();
		}

		$options->validate( 'bulk', $bulk, 'array_merge', $this->bulk );

		$options->validate( 'views', $this->views, 'array' );
		$options->validate( 'view', pods_v( $options->num_prefix . 'view' . $options->num, 'get', $this->view, true ), 'isset', $this->views );

		$options->validate( 'searchable', $this->searchable, 'boolean' );
		$options->validate( 'search', pods_v( $options->num_prefix . 'search' . $options->num ) );
		$options->validate( 'search_across', $this->search_across, 'boolean' );
		$options->validate( 'search_across_picks', $this->search_across_picks, 'boolean' );
		$options->validate( 'filters', $this->filters, 'array' );
		$options->validate( 'filters_enhanced', $this->filters_enhanced, 'boolean' );
		$options->validate( 'where', $this->where, 'array_merge' );

		$options->validate( 'pagination', $this->pagination, 'boolean' );
		$options->validate( 'pagination_type', $this->pagination_type );
		$options->validate( 'pagination_location', $this->pagination_location );
		$options->validate( 'page', pods_v( $options->num_prefix . 'pg' . $options->num, 'get', $this->page ), 'absint' );
		$options->validate( 'limit', pods_v( $options->num_prefix . 'limit' . $options->num, 'get', $this->limit ), 'int' );

		if ( isset( $this->pods_data ) && is_object( $this->pods_data ) ) {
			$this->sql = array_merge( $this->sql, array_filter( [
				'table'       => $this->pods_data->table,
				'field_id'    => $this->pods_data->field_id,
				'field_index' => $this->pods_data->field_index,
			] ) );
		}

		$options->validate( 'sql', $this->sql, 'array_merge' );

		$options->validate(
			'orderby_dir', strtoupper( pods_v( $options->num_prefix . 'orderby_dir' . $options->num, 'get', $this->orderby_dir, true ) ), 'in_array', array(
				'ASC',
				'DESC',
			)
		);

		$options->validate( 'sortable', $this->sortable, 'boolean' );

		$orderby = $this->orderby;

		$provided_orderby = sanitize_text_field( pods_v( $options->num_prefix . 'orderby' . $options->num, 'get', '' ) );

		// Enforce strict DB column name usage
		if ( ! empty( $options->sortable ) && ! empty( $provided_orderby ) ) {
			$orderby = pods_clean_name( $provided_orderby );
		}

		if ( ! empty( $orderby ) ) {
			$orderby = array(
				'default' => $orderby,
			);
		} else {
			$orderby = array();
		}

		$options->validate( 'orderby', $orderby, 'array_merge' );

		$options->validate( 'params', $this->params, 'array' );

		$options->validate( 'restrict', $this->restrict, 'array_merge' );

		// handle author restrictions
		if ( ! empty( $options['restrict']['author_restrict'] ) ) {
			$restrict = $options['restrict'];

			if ( ! is_array( $restrict['author_restrict'] ) ) {
				$restrict['author_restrict'] = array( $restrict['author_restrict'] => get_current_user_id() );
			}

			if ( null === $restrict['edit'] ) {
				$restrict['edit'] = $restrict['author_restrict'];
			}

			$options->restrict = $restrict;
		}

		if ( null !== $options['restrict']['edit'] ) {
			$restrict = $options['restrict'];

			if ( null === $restrict['duplicate'] ) {
				$restrict['duplicate'] = $restrict['edit'];
			}

			if ( null === $restrict['delete'] ) {
				$restrict['delete'] = $restrict['edit'];
			}

			if ( null === $restrict['manage'] ) {
				$restrict['manage'] = $restrict['edit'];
			}

			if ( null === $restrict['reorder'] ) {
				$restrict['reorder'] = $restrict['edit'];
			}

			$options->restrict = $restrict;
		}//end if

		$item  = __( 'Item', 'pods' );
		$items = __( 'Items', 'pods' );

		if ( ! is_array( $this->label ) ) {
			$this->label = (array) $this->label;
		}

		if ( is_object( $this->pod ) ) {
			$pod_data = $this->pod->pod_data;
			$pod_name = $this->pod->pod_data['name'];
			$pod_data = apply_filters( "pods_advanced_content_type_pod_data_{$pod_name}", $pod_data, $this->pod->pod_data['name'] );
			$pod_data = apply_filters( 'pods_advanced_content_type_pod_data', $pod_data, $this->pod->pod_data['name'] );

			$this->label = array_merge( $this->label, $pod_data['options'] );

			$item  = pods_v( 'label_singular', $pod_data, pods_v( 'label', $pod_data, $item, true ), true );
			$items = pods_v( 'label', $pod_data, $items, true );
		}

		$options->validate( 'item', $item );
		$options->validate( 'items', $items );

		$options->validate(
			'heading', array(
				'manage'    => pods_v( 'label_manage', $this->label, __( 'Manage', 'pods' ) ),
				'add'       => pods_v( 'label_add_new', $this->label, __( 'Add New', 'pods' ) ),
				'edit'      => pods_v( 'label_edit', $this->label, __( 'Edit', 'pods' ) ),
				'duplicate' => pods_v( 'label_duplicate', $this->label, __( 'Duplicate', 'pods' ) ),
				'view'      => pods_v( 'label_view', $this->label, __( 'View', 'pods' ) ),
				'reorder'   => pods_v( 'label_reorder', $this->label, __( 'Reorder', 'pods' ) ),
				'search'    => pods_v( 'label_search', $this->label, __( 'Search', 'pods' ) ),
				'views'     => pods_v( 'label_view', $this->label, __( 'View', 'pods' ) ),
			), 'array_merge'
		);

		$options->validate(
			'header', array(
				'manage'    => pods_v( 'label_manage_items', $this->label, sprintf( __( 'Manage %s', 'pods' ), $options->items ) ),
				'add'       => pods_v( 'label_add_new_item', $this->label, sprintf( __( 'Add New %s', 'pods' ), $options->item ) ),
				'edit'      => pods_v( 'label_edit_item', $this->label, sprintf( __( 'Edit %s', 'pods' ), $options->item ) ),
				'duplicate' => pods_v( 'label_duplicate_item', $this->label, sprintf( __( 'Duplicate %s', 'pods' ), $options->item ) ),
				'view'      => pods_v( 'label_view_item', $this->label, sprintf( __( 'View %s', 'pods' ), $options->item ) ),
				'reorder'   => pods_v( 'label_reorder_items', $this->label, sprintf( __( 'Reorder %s', 'pods' ), $options->items ) ),
				'search'    => pods_v( 'label_search_items', $this->label, sprintf( __( 'Search %s', 'pods' ), $options->items ) ),
			), 'array_merge'
		);

		$options->validate(
			'label', array(
				'add'       => pods_v( 'label_add_new_item', $this->label, sprintf( __( 'Add New %s', 'pods' ), $options->item ) ),
				'add_new'   => pods_v( 'label_add_new', $this->label, __( 'Add New', 'pods' ) ),
				'edit'      => pods_v( 'label_update_item', $this->label, sprintf( __( 'Update %s', 'pods' ), $options->item ) ),
				'duplicate' => pods_v( 'label_duplicate_item', $this->label, sprintf( __( 'Duplicate %s', 'pods' ), $options->item ) ),
				'delete'    => pods_v( 'label_delete_item', $this->label, sprintf( __( 'Delete this %s', 'pods' ), $options->item ) ),
				'view'      => pods_v( 'label_view_item', $this->label, sprintf( __( 'View %s', 'pods' ), $options->item ) ),
				'reorder'   => pods_v( 'label_reorder_items', $this->label, sprintf( __( 'Reorder %s', 'pods' ), $options->items ) ),
			), 'array_merge'
		);

		$options->validate( 'fields', $this->fields, 'array_merge' );

		// Set up default manage field.
		if ( empty( $options->fields['manage'] ) ) {
			// Make a local copy of the fields.
			$fields = $options->fields;

			// Change the info.
			$fields['manage'][ $options->sql['field_index'] ] = [
					'label' => __( 'Name', 'pods' ),
			];

			// Set the object fields property.
			$options->fields = $fields;
		}

		$options->validate( 'export', $this->export, 'array_merge' );
		$options->validate( 'reorder', $this->reorder, 'array_merge' );
		$options->validate( 'screen_options', $this->screen_options, 'array_merge' );

		$options->validate(
			'session', $this->session, 'in_array', array(
				'search',
				'filters',
				'show_per_page',
				'orderby',
			)
		);
		$options->validate(
			'user', $this->user, 'in_array', array(
				'search',
				'filters',
				'show_per_page',
				'orderby',
			)
		);

		$options->validate( 'action_after', $this->action_after, 'array_merge' );
		$options->validate( 'action_links', $this->action_links, 'array_merge' );
		$options->validate( 'actions_disabled', $this->actions_disabled, 'array' );
		$options->validate( 'actions_hidden', $this->actions_hidden, 'array_merge' );
		$options->validate( 'actions_custom', $this->actions_custom, 'array_merge' );

		if ( ! empty( $options->actions_disabled ) ) {
			if ( ! empty( $options->actions_bulk ) ) {
				$actions_bulk = $options->actions_bulk;

				foreach ( $actions_bulk as $action => $action_opt ) {
					if ( in_array( $action, $options->actions_disabled ) ) {
						unset( $actions_bulk[ $action ] );
					}
				}

				$options->actions_bulk = $actions_bulk;
			}

			if ( ! empty( $options->actions_custom ) ) {
				$actions_custom = $options->actions_custom;

				foreach ( $actions_custom as $action => $action_opt ) {
					if ( in_array( $action, $options->actions_disabled ) ) {
						unset( $actions_custom[ $action ] );
					}
				}

				$options->actions_custom = $actions_custom;
			}
		}//end if

		$options->validate( 'extra', $this->extra, 'array_merge' );

		$options->validate( 'style', $this->style );
		$options->validate( 'icon', $this->icon );
		$options->validate( 'css', $this->css );
		$options->validate( 'wpcss', $this->wpcss, 'boolean' );

		if ( true === $options['wpcss'] ) {
			global $user_ID;
			wp_get_current_user();

			$color = (string) get_user_meta( $user_ID, 'admin_color', true );
			if ( ! $color || strlen( $color ) < 1 ) {
				$color = 'fresh';
			}

			$this->wpcss = "colors-{$color}";
		}

		$options = $options->dump();

		if ( is_object( $this->pod ) ) {
			$options = $this->do_hook( $this->pod->pod . '_setup_options', $options );
		}

		$options = $this->do_hook( 'setup_options', $options );

		if ( false !== $options && ! empty( $options ) ) {
			foreach ( $options as $option => $value ) {
				if ( isset( $this->{$option} ) ) {
					$this->{$option} = $value;
				} else {
					$this->x[ $option ] = $value;
				}
			}
		}

		$unique_identifier = pods_v( 'page' );
		// wp-admin page
		if ( is_object( $this->pod ) && isset( $this->pod->pod ) ) {
			$unique_identifier = '_' . $this->pod->pod;
		} elseif ( is_string( $this->sql['table'] ) && 0 < strlen( $this->sql['table'] ) ) {
			$unique_identifier = '_' . $this->sql['table'];
		}

		$unique_identifier .= '_' . $this->page;
		if ( $this->num && 0 < strlen( $this->num ) ) {
			$unique_identifier .= '_' . $this->num_prefix . $this->num;
		}

		$this->unique_identifier = 'pods_ui_' . md5( $unique_identifier );

		$this->setup_fields();

		return $options;
	}

	/**
	 * @param null   $fields
	 * @param string $which
	 *
	 * @return array|bool|mixed|null
	 */
	public function setup_fields( $fields = null, $which = 'fields' ) {

		$init = false;
		if ( null === $fields ) {
			if ( isset( $this->fields[ $which ] ) ) {
				$fields = (array) $this->fields[ $which ];
			} elseif ( isset( $this->fields['manage'] ) ) {
				$fields = (array) $this->fields['manage'];
			} else {
				$fields = array();
			}
			if ( 'fields' === $which ) {
				$init = true;
			}
		}
		if ( ! empty( $fields ) ) {
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
			if ( empty( $this->filters ) && ( empty( $this->fields['search'] ) || 'search' === $which ) && false !== $this->searchable ) {
				$filterable    = true;
				$this->filters = array();
			}

			foreach ( $fields as $field => $attributes ) {
				if ( ! is_array( $attributes ) ) {
					if ( is_int( $field ) ) {
						$field      = $attributes;
						$attributes = array();
					} elseif ( $attributes instanceof Field ) {
						$attributes = $attributes->get_args();
					} else {
						$attributes = array( 'label' => $attributes );
					}
				}

				if ( ! isset( $attributes['name'] ) ) {
					$attributes['name'] = $field;
				}

				$field_name = pods_v( 'real_name', $attributes, pods_v( 'name', $attributes, $field ) );

				if ( is_object( $this->pod ) ) {
					$field_attributes = $this->pod->fields( $field_name );

					if ( $field_attributes ) {
						$attributes = pods_config_merge_data( $field_attributes, $attributes );
					}
				}

				if ( ! empty( $attributes['options'] ) && is_array( $attributes['options'] ) ) {
					$attributes = pods_config_merge_data( $attributes['options'], $attributes );
				}

				if ( ! isset( $attributes['id'] ) ) {
					$attributes['id'] = '';
				}
				if ( ! isset( $attributes['label'] ) ) {
					$attributes['label'] = ucwords( str_replace( '_', ' ', $field ) );
				}
				if ( ! isset( $attributes['type'] ) ) {
					$attributes['type'] = 'text';
				}
				if ( ! isset( $attributes['date_format_type'] ) ) {
					$attributes['date_format_type'] = 'date';
				}
				if ( 'related' !== $attributes['type'] || ! isset( $attributes['related'] ) ) {
					$attributes['related'] = false;
				}
				if ( 'related' !== $attributes['type'] || ! isset( $attributes['related_id'] ) ) {
					$attributes['related_id'] = 'id';
				}
				if ( 'related' !== $attributes['type'] || ! isset( $attributes['related_field'] ) ) {
					$attributes['related_field'] = 'name';
				}
				if ( 'related' !== $attributes['type'] || ! isset( $attributes['related_multiple'] ) ) {
					$attributes['related_multiple'] = false;
				}
				if ( 'related' !== $attributes['type'] || ! isset( $attributes['related_sql'] ) ) {
					$attributes['related_sql'] = false;
				}
				if ( 'related' === $attributes['type'] && ( is_array( $attributes['related'] ) || strpos( $attributes['related'], ',' ) ) ) {
					if ( ! is_array( $attributes['related'] ) ) {
						$attributes['related'] = @explode( ',', $attributes['related'] );
						$related_items         = array();
						foreach ( $attributes['related'] as $key => $label ) {
							if ( is_numeric( $key ) ) {
								$key   = $label;
								$label = ucwords( str_replace( '_', ' ', $label ) );
							}
							$related_items[ $key ] = $label;
						}
						$attributes['related'] = $related_items;
					}
					if ( empty( $attributes['related'] ) ) {
						$attributes['related'] = false;
					}
				}
				if ( ! isset( $attributes['readonly'] ) ) {
					$attributes['readonly'] = false;
				}
				if ( ! isset( $attributes['date_touch'] ) || 'date' !== $attributes['type'] ) {
					$attributes['date_touch'] = false;
				}
				if ( ! isset( $attributes['date_touch_on_create'] ) || 'date' !== $attributes['type'] ) {
					$attributes['date_touch_on_create'] = false;
				}
				if ( ! isset( $attributes['display'] ) ) {
					$attributes['display'] = true;
				}
				if ( ! isset( $attributes['hidden'] ) ) {
					$attributes['hidden'] = false;
				}
				if ( ! isset( $attributes['sortable'] ) || false === $this->sortable ) {
					$attributes['sortable'] = $this->sortable;
				}
				if ( ! isset( $attributes['search'] ) || false === $this->searchable ) {
					$attributes['search'] = $this->searchable;
				}
				if ( ! isset( $attributes['filter'] ) || false === $this->searchable ) {
					$attributes['filter'] = $this->searchable;
				}
				/*
				if ( false !== $attributes[ 'options' ][ 'filter' ] && false !== $filterable )
                    $this->filters[] = $field;*/
				if ( false === $attributes['filter'] || ! isset( $attributes['filter_label'] ) || ! in_array( $field, $this->filters ) ) {
					$attributes['filter_label'] = $attributes['label'];
				}
				if ( false === $attributes['filter'] || ! isset( $attributes['filter_default'] ) || ! in_array( $field, $this->filters ) ) {
					$attributes['filter_default'] = false;
				}
				if ( false === $attributes['filter'] || ! isset( $attributes['date_ongoing'] ) || 'date' !== $attributes['type'] || ! in_array( $field, $this->filters ) ) {
					$attributes['date_ongoing'] = false;
				}
				if ( false === $attributes['filter'] || ! isset( $attributes['date_ongoing'] ) || 'date' !== $attributes['type'] || ! isset( $attributes['date_ongoing_default'] ) || ! in_array( $field, $this->filters ) ) {
					$attributes['date_ongoing_default'] = false;
				}
				if ( ! isset( $attributes['export'] ) ) {
					$attributes['export'] = true;
				}
				if ( ! isset( $attributes['group_related'] ) ) {
					$attributes['group_related'] = false;
				}
				if ( ! isset( $attributes['comments'] ) ) {
					$attributes['comments'] = '';
				}
				if ( ! isset( $attributes['comments_top'] ) ) {
					$attributes['comments_top'] = false;
				}
				if ( ! isset( $attributes['custom_view'] ) ) {
					$attributes['custom_view'] = false;
				}
				if ( ! isset( $attributes['custom_input'] ) ) {
					$attributes['custom_input'] = false;
				}
				if ( isset( $attributes['display_helper'] ) ) {
					// pods ui backward compatibility
					$attributes['custom_display'] = $attributes['display_helper'];
				}
				if ( ! isset( $attributes['custom_display'] ) ) {
					$attributes['custom_display'] = false;
				}
				if ( ! isset( $attributes['custom_relate'] ) ) {
					$attributes['custom_relate'] = false;
				}
				if ( ! isset( $attributes['custom_form_display'] ) ) {
					$attributes['custom_form_display'] = false;
				}
				if ( ! isset( $attributes['css_values'] ) ) {
					$attributes['css_values'] = true;
				}
				if ( 'search_columns' === $which && ! $attributes['search'] ) {
					continue;
				}

				$attributes = PodsForm::field_setup( $attributes, null, $attributes['type'] );

				$new_fields[ $field ] = $attributes;
			}//end foreach
			$fields = $new_fields;
		}//end if

		if ( false !== $init ) {
			if ( empty( $this->fields_setup['fields'] ) ) {
				if ( 'fields' !== $which && ! empty( $this->fields ) ) {
					$this->fields = $this->setup_fields( $this->fields, 'fields' );
				} else {
					$this->fields['manage'] = $fields;
				}

				$this->fields_setup['fields'] = true;
			}

			if ( ! in_array( 'add', $this->actions_disabled ) || ! in_array( 'edit', $this->actions_disabled ) || ! in_array( 'duplicate', $this->actions_disabled ) ) {
				if ( empty( $this->fields_setup['form'] ) ) {
					if ( 'form' !== $which && isset( $this->fields['form'] ) && is_array( $this->fields['form'] ) ) {
						$this->fields['form'] = $this->setup_fields( $this->fields['form'], 'form' );
					} else {
						$this->fields['form'] = $fields;
					}

					$this->fields_setup['form'] = true;
				}

				if ( empty( $this->fields_setup['add'] ) ) {
					if ( ! in_array( 'add', $this->actions_disabled ) ) {
						if ( 'add' !== $which && isset( $this->fields['add'] ) && is_array( $this->fields['add'] ) ) {
							$this->fields['add'] = $this->setup_fields( $this->fields['add'], 'add' );
						}
					}

					$this->fields_setup['add'] = true;
				}

				if ( empty( $this->fields_setup['edit'] ) ) {
					if ( ! in_array( 'edit', $this->actions_disabled ) ) {
						if ( 'edit' !== $which && isset( $this->fields['edit'] ) && is_array( $this->fields['edit'] ) ) {
							$this->fields['edit'] = $this->setup_fields( $this->fields['edit'], 'edit' );
						}
					}

					$this->fields_setup['edit'] = true;
				}

				if ( empty( $this->fields_setup['duplicate'] ) ) {
					if ( ! in_array( 'duplicate', $this->actions_disabled ) ) {
						if ( 'duplicate' !== $which && isset( $this->fields['duplicate'] ) && is_array( $this->fields['duplicate'] ) ) {
							$this->fields['duplicate'] = $this->setup_fields( $this->fields['duplicate'], 'duplicate' );
						}
					}

					$this->fields_setup['duplicate'] = true;
				}
			}//end if

			if ( empty( $this->fields_setup['search'] ) ) {
				if ( false !== $this->searchable ) {
					if ( 'search' !== $which && isset( $this->fields['search'] ) && ! empty( $this->fields['search'] ) ) {
						$this->fields['search'] = $this->setup_fields( $this->fields['search'], 'search' );
					}
				} else {
					$this->fields['search'] = false;
				}

				$this->fields_setup['search'] = true;
			}

			if ( empty( $this->fields_setup['sort'] ) ) {
				if ( false !== $this->sortable ) {
					if ( 'sort' !== $which ) {
						if ( isset( $this->fields['sort'] ) && ! empty( $this->fields['sort'] ) ) {
							$this->fields['sort'] = $this->setup_fields( $this->fields['sort'], 'sort' );
						} else {
							$this->fields['sort'] = $fields;
						}
					}
				} else {
					$this->fields['sort'] = false;
				}

				$this->fields_setup['sort'] = true;
			}

			if ( empty( $this->fields_setup['export'] ) ) {
				if ( ! in_array( 'export', $this->actions_disabled, true ) ) {
					if ( 'export' !== $which && isset( $this->fields['export'] ) && ! empty( $this->fields['export'] ) ) {
						$this->fields['export'] = $this->setup_fields( $this->fields['export'], 'export' );
					}
				}

				$this->fields_setup['export'] = true;
			}

			if ( empty( $this->fields_setup['reorder'] ) ) {
				if ( ! in_array( 'reorder', $this->actions_disabled, true ) && false !== $this->reorder['on'] ) {
					if ( 'reorder' !== $which && isset( $this->fields['reorder'] ) && ! empty( $this->fields['reorder'] ) ) {
						$this->fields['reorder'] = $this->setup_fields( $this->fields['reorder'], 'reorder' );
					}
				}

				$this->fields_setup['reorder'] = true;
			}
		}//end if

		return $this->do_hook( 'setup_fields', $fields, $which, $init );
	}

	/**
	 * @param      $msg
	 * @param bool $error
	 */
	public function message( $msg, $error = false ) {

		$class = 'updated';
		$hook  = 'message';

		if ( $error ) {
			$class = 'error';
			$hook  = 'error';
		}

		$msg = $this->do_hook( $hook, $msg );

		if ( empty( $msg ) ) {
			return;
		}
		?>
		<div id="message" class="<?php echo esc_attr( $class ); ?> fade">
			<p><?php echo $msg; ?></p>
		</div>
		<?php
	}

	/**
	 * @param $msg
	 *
	 * @return bool
	 */
	public function error( $msg ) {

		$this->message( $msg, true );

		return false;
	}

	/**
	 * @return mixed
	 */
	public function go() {

		$this->do_hook( 'go' );
		$_GET = pods_unsanitize( $_GET );
		// fix wp sanitization
		$_POST = pods_unsanitize( $_POST );
		// fix wp sanitization
		if ( false !== $this->css ) {
			?>
			<link type="text/css" rel="stylesheet" href="<?php echo esc_url( $this->css ); ?>" />
			<?php
		}
		if ( false !== $this->wpcss ) {
			$stylesheets = array( 'global', 'wp-admin', $this->wpcss );
			foreach ( $stylesheets as $style ) {
				if ( ! wp_style_is( $style, 'queue' ) && ! wp_style_is( $style, 'to_do' ) && ! wp_style_is( $style, 'done' ) ) {
					wp_enqueue_style( $style );
				}
			}
		}

		$this->ui_page = array( $this->action );
		if ( 'add' === $this->action && ! in_array( $this->action, $this->actions_disabled ) ) {
			$this->ui_page[] = 'form';
			if ( 'create' === $this->do && $this->save && ! in_array( $this->do, $this->actions_disabled ) && ! empty( $_POST ) ) {
				$this->ui_page[] = $this->do;
				$this->save( true );
				$this->manage();
			} else {
				$this->add();
			}
		} elseif ( ( 'edit' === $this->action && ! in_array( $this->action, $this->actions_disabled ) ) || ( 'duplicate' === $this->action && ! in_array( $this->action, $this->actions_disabled ) ) ) {
			$this->ui_page[] = 'form';
			if ( 'save' === $this->do && $this->save && ! empty( $_POST ) ) {
				$this->save();
			}
			$this->edit( ( 'duplicate' === $this->action && ! in_array( $this->action, $this->actions_disabled ) ) ? true : false );
		} elseif ( 'delete' === $this->action && ! in_array( $this->action, $this->actions_disabled ) && false !== wp_verify_nonce( $this->_nonce, 'pods-ui-action-delete' ) ) {
			$this->delete( $this->id );
			$this->manage();
		} elseif ( 'reorder' === $this->action && ! in_array( $this->action, $this->actions_disabled ) && false !== $this->reorder['on'] ) {
			if ( 'save' === $this->do ) {
				$this->ui_page[] = $this->do;
				$this->reorder();
			}
			$this->manage( true );
		} elseif ( 'save' === $this->do && $this->save && ! in_array( $this->do, $this->actions_disabled ) && ! empty( $_POST ) ) {
			$this->ui_page[] = $this->do;
			$this->save();
			$this->manage();
		} elseif ( 'create' === $this->do && $this->save && ! in_array( $this->do, $this->actions_disabled ) && ! empty( $_POST ) ) {
			$this->ui_page[] = $this->do;
			$this->save( true );
			$this->manage();
		} elseif ( 'view' === $this->action && ! in_array( $this->action, $this->actions_disabled ) ) {
			$this->view();
		} else {
			if ( isset( $this->actions_custom[ $this->action ] ) ) {
				$use_nonce = false;

				if ( is_array( $this->actions_custom[ $this->action ] ) ) {
					$more_args = [];

					if ( ! empty( $this->actions_custom[ $this->action ]['more_args'] ) ) {
						$more_args = $this->actions_custom[ $this->action ]['more_args'];
					}

					$use_nonce = ! empty( $this->actions_custom[ $this->action ]['nonce'] ) || ! empty( $more_args['nonce'] );
				}

				$row = $this->row;

				if ( empty( $row ) ) {
					$row = $this->get_row();
				}

				if (
					$this->restricted( $this->action, $row )
					|| (
						$use_nonce
						&& false === wp_verify_nonce( $this->_nonce, 'pods-ui-action-' . $this->action )
					)
				) {
					return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );
				} elseif ( $more_args && false !== $this->callback_action( true, $this->action, $this->id, $row ) ) {
					return null;
				} elseif ( false !== $this->callback_action( true, $this->action, $this->id ) ) {
					return null;
				}
			}//end if

			if ( ! in_array( 'manage', $this->actions_disabled, true ) ) {
				// handle session / user persistent settings for show_per_page, orderby, search, and filters
				$methods = array( 'session', 'user' );

				// @todo fix this to set ($this) AND save (setting)
				foreach ( $methods as $method ) {
					foreach ( $this->$method as $setting ) {
						if ( 'show_per_page' === $setting ) {
							$value = $this->limit;
						} elseif ( 'orderby' === $setting ) {
							if ( empty( $this->orderby ) ) {
								$value = '';
							} elseif ( isset( $this->orderby['default'] ) ) {
								// save this if we have a default index set
								$value = $this->orderby['default'] . ' ' . ( false === strpos( $this->orderby['default'], ' ' ) ? $this->orderby_dir : '' );
							} else {
								$value = '';
							}
						} else {
							$value = $this->$setting;
						}

						$current_value = pods_v( $setting, $method );

						if ( ! is_array( $current_value ) && ! is_array( $value ) ) {
							if ( (string) $current_value !== (string) $value ) {
								pods_v_set( $value, $setting, $method );
							}
						} elseif ( $current_value !== $value ) {
							pods_v_set( $value, $setting, $method );
						}
					}
				}

				$this->manage();
			}//end if
		}//end if
	}

	/**
	 * @return mixed
	 */
	public function add() {

		if ( false !== $this->callback_action( 'add' ) ) {
			return null;
		}

		if ( $this->restricted( $this->action ) ) {
			return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );
		}

		$icon_style = '';
		if ( false !== $this->icon ) {
			$icon_style = ' style="background-position:0 0;background-size:100%;background-image:url(' . esc_url( $this->icon ) . ');"';
		}
		?>
		<div class="wrap pods-ui">
			<div id="icon-edit-pages" class="icon32"<?php echo $icon_style; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>>
				<br />
			</div>
			<h2>
				<?php
				echo wp_kses_post( $this->header['add'] );

				if ( ! in_array( 'manage', $this->actions_disabled ) && ! in_array( 'manage', $this->actions_hidden ) && ! $this->restricted( 'manage' ) ) {
					$link = pods_query_arg(
						array(
							$this->num_prefix . 'action' . $this->num => 'manage',
							$this->num_prefix . 'id' . $this->num     => '',
						), self::$allowed, $this->exclusion()
					);

					if ( ! empty( $this->action_links['manage'] ) ) {
						$link = $this->action_links['manage'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading['manage'] ); ?></a>
				<?php } ?>
			</h2>

			<?php $this->form( true ); ?>
		</div>
		<?php
	}

	/**
	 * @param bool $duplicate
	 *
	 * @return bool|mixed
	 */
	public function edit( $duplicate = false ) {

		if ( in_array( 'duplicate', $this->actions_disabled ) ) {
			$duplicate = false;
		}

		if ( empty( $this->row ) ) {
			$this->get_row();
		}

		if ( $duplicate && false !== $this->callback_action( 'duplicate' ) ) {
			return null;
		} elseif ( false !== $this->callback_action( $this->action, $duplicate ) ) {
			return null;
		}

		if ( $this->restricted( $this->action ) ) {
			return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );
		}

		$icon_style = '';
		if ( false !== $this->icon ) {
			$icon_style = ' style="background-position:0 0;background-size:100%;background-image:url(' . esc_url( $this->icon ) . ');"';
		}
		?>
		<div class="wrap pods-ui">
			<div id="icon-edit-pages" class="icon32"<?php echo $icon_style; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>>
				<br />
			</div>
			<h2>
				<?php
				echo wp_kses_post( $this->do_template( $duplicate ? $this->header['duplicate'] : $this->header['edit'] ) );

				if ( ! in_array( 'add', $this->actions_disabled ) && ! in_array( 'add', $this->actions_hidden ) && ! $this->restricted( 'add' ) ) {
					$link = pods_query_arg(
						array(
							$this->num_prefix . 'action' . $this->num => 'add',
							$this->num_prefix . 'id' . $this->num     => '',
							$this->num_prefix . 'do' . $this->num     => '',
						), self::$allowed, $this->exclusion()
					);

					if ( ! empty( $this->action_links['add'] ) ) {
						$link = $this->action_links['add'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo wp_kses_post( $this->heading['add'] ); ?></a>
					<?php
				} elseif ( ! in_array( 'manage', $this->actions_disabled ) && ! in_array( 'manage', $this->actions_hidden ) && ! $this->restricted( 'manage' ) ) {
					$link = pods_query_arg(
						array(
							$this->num_prefix . 'action' . $this->num => 'manage',
							$this->num_prefix . 'id' . $this->num     => '',
						), self::$allowed, $this->exclusion()
					);

					if ( ! empty( $this->action_links['manage'] ) ) {
						$link = $this->action_links['manage'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading['manage'] ); ?></a>
					<?php
				}//end if
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
	public function form( $create = false, $duplicate = false ) {

		if ( in_array( 'duplicate', $this->actions_disabled ) ) {
			$duplicate = false;
		}

		if ( false !== $this->callback( 'form' ) ) {
			return null;
		}

		$label = $this->label['add'];
		$id    = null;
		$vars  = array(
			$this->num_prefix . 'action' . $this->num => $this->action_after['add'],
			$this->num_prefix . 'do' . $this->num     => 'create',
			$this->num_prefix . 'id' . $this->num     => 'X_ID_X',
		);

		$alt_vars           = $vars;
		$alt_vars['action'] = 'manage';
		unset( $alt_vars['id'] );

		if ( false === $create ) {
			if ( empty( $this->row ) ) {
				$this->get_row();
			}

			if ( empty( $this->row ) && ( ! is_object( $this->pod ) || 'settings' !== $this->pod->pod_data['type'] ) ) {
				return $this->error( sprintf( __( '<strong>Error:</strong> %s not found.', 'pods' ), $this->item ) );
			}

			if ( $this->restricted( $this->action, $this->row ) ) {
				return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );
			}

			$label = $this->do_template( $this->label['edit'] );
			$id    = pods_v( $this->sql['field_id'], $this->row );
			$vars  = array(
				$this->num_prefix . 'action' . $this->num => $this->action_after['edit'],
				$this->num_prefix . 'do' . $this->num     => 'save',
				$this->num_prefix . 'id' . $this->num     => $id,
			);

			$alt_vars           = $vars;
			$alt_vars['action'] = 'manage';
			unset( $alt_vars['id'] );

			if ( $duplicate ) {
				$label = $this->do_template( $this->label['duplicate'] );
				$id    = null;
				$vars  = array(
					$this->num_prefix . 'action' . $this->num => $this->action_after['duplicate'],
					$this->num_prefix . 'do' . $this->num     => 'create',
					$this->num_prefix . 'id' . $this->num     => 'X_ID_X',
				);

				$alt_vars           = $vars;
				$alt_vars['action'] = 'manage';
				unset( $alt_vars['id'] );
			}
		} elseif ( $this->restricted( $this->action, $this->row ) ) {
			return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );
		}//end if

		$fields = array();

		if ( isset( $this->fields[ $this->action ] ) ) {
			$fields = $this->fields[ $this->action ];
		}

		if ( is_object( $this->pod ) ) {
			$object_fields = $this->pod->pod_data->get_object_fields();

			if ( empty( $fields ) ) {
				// Add core object fields if $fields is empty
				$fields = $this->pod->pod_data->get_all_fields();
			}
		}

		$form_fields = $fields;

		// Temporary
		$fields = array();

		foreach ( $form_fields as $k => $field ) {
			$name = $k;

			$is_field_object = $field instanceof Field;

			if ( ! is_array( $field ) && ! $is_field_object ) {
				$name = $field;

				$field = [
					'name' => $name,
				];
			}

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			$default_value = pods_v( 'default', $field );
			$value         = pods_v( 'value', $field );

			if ( ! $field instanceof Field ) {
				if ( isset( $object_fields[ $field['name'] ] ) ) {
					$field_attributes = $object_fields[ $field['name'] ];

					$field = pods_config_merge_data( $field, $field_attributes );
				} else {
					$field_attributes = $this->pod->pod_data->get_field( $field['name'] );

					if ( $field_attributes ) {
						$field = pods_config_merge_data( $field_attributes, $field );
					}
				}
			}

			if ( pods_v( 'hidden', $field, false ) ) {
				$field['type'] = 'hidden';
			}

			$fields[ $field['name'] ] = $field;

			if ( empty( $this->id ) && null !== $default_value ) {
				$this->pod->row_override[ $field['name'] ] = $default_value;
			} elseif ( ! empty( $this->id ) && null !== $value ) {
				$this->pod->row[ $field['name'] ] = $value;
			}
		}//end foreach

		unset( $form_fields );
		// Cleanup
		$fields = $this->do_hook( 'form_fields', $fields, $this->pod );

		$pod            =& $this->pod;
		$thank_you      = pods_query_arg( $vars, self::$allowed, $this->exclusion() );
		$thank_you_alt  = pods_query_arg( $alt_vars, self::$allowed, $this->exclusion() );
		$obj            =& $this;
		$singular_label = $this->item;
		$plural_label   = $this->items;

		$is_settings_pod = is_object( $this->pod ) && 'settings' === $this->pod->pod_data['type'];

		$form_type = 'post';

		if ( 'settings' === $this->style || $is_settings_pod ) {
			$form_type = 'settings';
		}

		pods_view( PODS_DIR . 'ui/forms/form.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * @return bool|mixed
	 * @since 2.3.10
	 */
	public function view() {

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
			$object_fields = $this->pod->pod_data->get_object_fields();

			$object_field_objects = array(
				'post_type',
				'taxonomy',
				'media',
				'user',
				'comment',
			);

			if ( empty( $object_fields ) && in_array( $this->pod->pod_data['type'], $object_field_objects, true ) ) {
				$object_fields = $this->pod->api->get_wp_object_fields( $this->pod->pod_data['type'], $this->pod->pod_data );
			}

			if ( empty( $fields ) ) {
				// Add core object fields if $fields is empty
				$fields = $this->pod->pod_data->get_all_fields();
			}
		}

		$view_fields = $fields;
		// Temporary
		$fields = array();

		foreach ( $view_fields as $k => $field ) {
			$name = $k;

			$defaults = array(
				'name'    => $name,
				'type'    => 'text',
				'options' => 'text',
			);

			$is_field_object = $field instanceof Field;

			if ( ! is_array( $field ) && ! $is_field_object ) {
				$name = $field;

				$field = array(
					'name' => $name,
				);
			}

			$field = pods_config_merge_data( $defaults, $field );

			$field['name'] = trim( $field['name'] );

			$value = pods_v( 'default', $field );

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			$found_field = pods_config_get_field_from_all_fields( $field['name'], $this->pod );

			if ( $found_field ) {
				$field = pods_config_merge_data( $field, $found_field );
			}

			if ( pods_v( 'hidden', $field, false, null, true ) || 'hidden' === $field['type'] ) {
				continue;
			} elseif ( ! pods_permission( $field ) ) {
				continue;
			}

			$fields[ $field['name'] ] = $field;

			if ( empty( $this->id ) && null !== $value ) {
				$this->pod->row_override[ $field['name'] ] = $value;
			}
		}//end foreach

		unset( $view_fields );
		// Cleanup

		$icon_style = '';
		if ( false !== $this->icon ) {
			$icon_style = ' style="background-position:0 0;background-size:100%;background-image:url(' . esc_url( $this->icon ) . ');"';
		}
		?>
		<div class="wrap pods-ui">
			<div id="icon-edit-pages" class="icon32"<?php echo $icon_style; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>>
				<br />
			</div>
			<h2>
				<?php
				echo wp_kses_post( $this->do_template( $this->header['view'] ) );

				if ( ! in_array( 'add', $this->actions_disabled ) && ! in_array( 'add', $this->actions_hidden ) && ! $this->restricted( 'add' ) ) {
					$link = pods_query_arg(
						array(
							$this->num_prefix . 'action' . $this->num => 'add',
							$this->num_prefix . 'id' . $this->num     => '',
							$this->num_prefix . 'do' . $this->num     => '',
						), self::$allowed, $this->exclusion()
					);

					if ( ! empty( $this->action_links['add'] ) ) {
						$link = $this->action_links['add'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo wp_kses_post( $this->heading['add'] ); ?></a>
					<?php
				} elseif ( ! in_array( 'manage', $this->actions_disabled ) && ! in_array( 'manage', $this->actions_hidden ) && ! $this->restricted( 'manage' ) ) {
					$link = pods_query_arg(
						array(
							$this->num_prefix . 'action' . $this->num => 'manage',
							$this->num_prefix . 'id' . $this->num     => '',
						), self::$allowed, $this->exclusion()
					);

					if ( ! empty( $this->action_links['manage'] ) ) {
						$link = $this->action_links['manage'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading['manage'] ); ?></a>
					<?php
				}//end if

				pods_view( PODS_DIR . 'ui/admin/view.php', compact( array_keys( get_defined_vars() ) ) );
				?>

			</h2>
		</div>
		<?php
	}

	/**
	 * Reorder data
	 */
	public function reorder() {

		// loop through order
		$order = (array) pods_v( 'order', 'post', array(), true );

		$params = array(
			'pod'   => $this->pod->pod,
			'field' => $this->reorder['on'],
			'order' => $order,
		);

		$reorder = pods_api()->reorder_pod_item( $params );

		if ( $reorder ) {
			$this->message( sprintf( __( '<strong>Success!</strong> %s reordered successfully.', 'pods' ), $this->items ) );
		} else {
			$this->error( sprintf( __( '<strong>Error:</strong> %s has not been reordered.', 'pods' ), $this->items ) );
		}
	}

	/**
	 * @param bool $insert
	 *
	 * @return mixed
	 */
	public function save( $insert = false ) {

		$this->do_hook( 'pre_save', $insert );

		if ( $this->callback( 'save', $insert ) ) {
			return null;
		}

		global $wpdb;
		$action = __( 'saved', 'pods' );
		if ( true === $insert ) {
			$action = __( 'created', 'pods' );
		}
		$field_sql = array();
		$values    = array();
		$data      = array();
		foreach ( $this->fields['form'] as $field => $attributes ) {
			$vartype = '%s';
			if ( 'bool' === $attributes['type'] ) {
				$selected = ( 1 == pods_v( $field, 'post', 0 ) ) ? 1 : 0;
			} elseif ( '' == pods_v( $field, 'post', '' ) ) {
				continue;
			}
			if ( false === $attributes['display'] || false !== $attributes['readonly'] ) {
				if ( ! in_array( $attributes['type'], array( 'date', 'time', 'datetime' ) ) ) {
					continue;
				}
				if ( false === $attributes['date_touch'] && ( false === $attributes['date_touch_on_create'] || false === $insert || 0 < $this->id ) ) {
					continue;
				}
			}
			if ( in_array( $attributes['type'], array( 'date', 'time', 'datetime' ) ) ) {
				$format = 'Y-m-d H:i:s';
				if ( 'date' === $attributes['type'] ) {
					$format = 'Y-m-d';
				}
				if ( 'time' === $attributes['type'] ) {
					$format = 'H:i:s';
				}
				if ( false !== $attributes['date_touch'] || ( false !== $attributes['date_touch_on_create'] && true === $insert && $this->id < 1 ) ) {
					$value = date_i18n( $format );
				} else {
					$value = date_i18n( $format, strtotime( ( 'time' === $attributes['type'] ) ? date_i18n( 'Y-m-d ' ) : pods_v( $field, 'post', '' ) ) );
				}
			} else {
				if ( 'bool' === $attributes['type'] ) {
					$vartype = '%d';
					$value   = 0;
					if ( '' != pods_v( $field, 'post', '' ) ) {
						$value = 1;
					}
				} elseif ( 'number' === $attributes['type'] ) {
					$vartype = '%d';
					$value   = number_format( pods_v( $field, 'post', 0 ), 0, '', '' );
				} elseif ( 'decimal' === $attributes['type'] ) {
					$vartype = '%d';
					$value   = number_format( pods_v( $field, 'post', 0 ), 2, '.', '' );
				} elseif ( 'related' === $attributes['type'] ) {
					if ( is_array( pods_v( $field, 'post', '' ) ) ) {
						$value = implode( ',', pods_v( $field, 'post', '' ) );
					} else {
						$value = pods_v( $field, 'post', '' );
					}
				} else {
					$value = pods_v( $field, 'post', '' );
				}//end if
			}//end if

			if ( isset( $attributes['custom_save'] ) && false !== $attributes['custom_save'] && is_callable( $attributes['custom_save'] ) ) {
				$value = call_user_func_array(
					$attributes['custom_save'], array(
						$value,
						$field,
						$attributes,
						&$this,
					)
				);
			}

			$field_sql[]    = "`$field`=$vartype";
			$values[]       = $value;
			$data[ $field ] = $value;
		}//end foreach

		if ( $this->pod instanceof Pods ) {
			if ( false === $insert && 0 < $this->id ) {
				$this->insert_id = $this->pod->save( $data );
			} else {
				$this->insert_id = $this->pod->add( $data );
			}

			$check = 0 < $this->insert_id;
		} else {
			$field_sql = implode( ',', $field_sql );

			if ( false === $insert && 0 < $this->id ) {
				$this->insert_id = $this->id;
				$values[]        = $this->id;
				$check           = $wpdb->query( $wpdb->prepare( "UPDATE $this->sql['table'] SET $field_sql WHERE id=%d", $values ) );
			} else {
				$check = $wpdb->query( $wpdb->prepare( "INSERT INTO $this->sql['table'] SET $field_sql", $values ) );
			}
		}

		if ( $check ) {
			if ( empty( $this->insert_id ) ) {
				$this->insert_id = $wpdb->insert_id;
			}
			$this->message( sprintf( __( '<strong>Success!</strong> %1\$s %2\$s successfully.', 'pods' ), $this->item, $action ) );
		} else {
			$this->error( sprintf( __( '<strong>Error:</strong> %1\$s has not been %2\$s.', 'pods' ), $this->item, $action ) );
		}

		$this->do_hook( 'post_save', $this->insert_id, $data, $insert );
	}

	/**
	 * @param null $id
	 *
	 * @return bool|mixed
	 */
	public function delete( $id = null ) {

		$this->do_hook( 'pre_delete', $id );

		if ( false !== $this->callback_action( 'delete', $id ) ) {
			return null;
		}

		$id = pods_absint( $id );

		if ( empty( $id ) ) {
			$id = pods_absint( $this->id );
		}

		if ( $id < 1 ) {
			return $this->error( __( '<strong>Error:</strong> Invalid Configuration - Missing "id" definition.', 'pods' ) );
		}

		if ( false === $id ) {
			$id = $this->id;
		}

		if ( is_object( $this->pod ) ) {
			$check = $this->pod->delete( $id );
		} else {
			$check = $this->pods_data->delete( $this->sql['table'], array( $this->sql['field_id'] => $id ) );
		}

		if ( $check ) {
			$this->message( sprintf( __( '<strong>Deleted:</strong> %s has been deleted.', 'pods' ), $this->item ) );
		} else {
			$this->error( sprintf( __( '<strong>Error:</strong> %s has not been deleted.', 'pods' ), $this->item ) );
		}

		$this->do_hook( 'post_delete', $id );
	}

	/**
	 * Callback for deleting items in bulk
	 */
	public function delete_bulk() {

		$this->do_hook( 'pre_delete_bulk' );

		if ( 1 != pods_v( 'deleted_bulk', 'get', 0 ) ) {
			$ids = $this->bulk;

			$success = false;

			if ( ! empty( $ids ) ) {
				$ids = (array) $ids;

				foreach ( $ids as $id ) {
					$id = pods_absint( $id );

					if ( empty( $id ) ) {
						continue;
					}

					$callback = $this->callback( 'delete', $id );
					if ( $callback ) {
						$check = $callback;
					} elseif ( is_object( $this->pod ) ) {
						$check = $this->pod->delete( $id );
					} else {
						$check = $this->pods_data->delete( $this->sql['table'], array( $this->sql['field_id'] => $id ) );
					}

					if ( $check ) {
						$success = true;
					}
				}
			}//end if

			if ( $success ) {
				pods_redirect(
					pods_query_arg(
						array(
							'action_bulk'  => 'delete',
							'deleted_bulk' => 1,
						), array(
							'page',
							'lang',
							'action',
							'id',
						)
					)
				);
			} else {
				$this->error( sprintf( __( '<strong>Error:</strong> %s has not been deleted.', 'pods' ), $this->item ) );
			}
		} else {
			$this->message( sprintf( __( '<strong>Deleted:</strong> %s have been deleted.', 'pods' ), $this->items ) );

			unset( $_GET['deleted_bulk'] );
		}//end if

		$this->action_bulk = false;
		unset( $_GET['action_bulk'] );

		$this->do_hook( 'post_delete_bulk' );

		$this->manage();
	}

	/**
	 * Callback for exporting items in bulk
	 */
	public function export_bulk() {

		if ( ! empty( $_POST['bulk_export_type'] ) ) {
			if ( ! empty( $_POST['bulk_export_fields'] ) ) {
				$export_fields = $_POST['bulk_export_fields'];

				$this->fields['export'] = array();

				if ( $this->pod ) {
					$fields = $this->pod->fields();

					foreach ( $fields as $field ) {
						if ( in_array( $field['name'], $export_fields ) ) {
							$this->fields['export'][] = $field;
						}
					}
				}
			}

			// Set up where clause so that export function finds it
			if ( ! empty( $_POST['action_bulk_ids'] ) ) {
				$ids = (array) explode( ',', $_POST['action_bulk_ids'] );
				$ids = array_map( 'absint', $ids );
				$ids = array_filter( $ids );

				if ( ! empty( $ids ) ) {
					$ids = implode( ', ', $ids );

					$this->where = array(
						'manage' => '`' . pods_sanitize( $this->sql['field_id'] ) . '` IN ( ' . $ids . ' )',
					);
				}
			}

			$this->export( $_POST['bulk_export_type'] );

			// Cleanup since export function calls get_data before returning
			$this->action_bulk = '';
			$this->where       = array();
			$this->data        = false;

			$_GET['action_bulk_ids'] = '';

			$this->manage();
		} else {
			$this->export_fields_form();
		}//end if

	}

	/**
	 * Select the pods fields to be exported
	 */
	public function export_fields_form() {

		?>
		<div class="wrap pods-admin pods-ui">
			<h2><?php echo __( 'Choose Export Fields', 'pods' ); ?></h2>

			<form method="post" id="pods_admin_ui_export_form">
				<?php
				// Avoid a bunch of inputs if there's a lot selected
				if ( ! empty( $_REQUEST['action_bulk_ids'] ) ) {
					$_GET['action_bulk_ids'] = implode( ',', (array) $_REQUEST['action_bulk_ids'] );
				}

				$this->hidden_vars();
				?>

				<ul>
					<?php foreach ( $this->pod->fields() as $field_name => $field ) { ?>
						<li>
							<label for="bulk_export_fields_<?php echo esc_attr( $field['name'] ); ?>">
								<input type="checkbox" name="bulk_export_fields[]" id="bulk_export_fields_<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $field['name'] ); ?>" />
								<?php esc_html_e( $field['label'] ); ?>
							</label>
						</li>
					<?php } ?>
				</ul>

				<p class="submit">
					<?php _e( 'Export as:', 'pods' ); ?>&nbsp;&nbsp;
					<?php foreach ( $this->export['formats'] as $format => $separator ) { ?>
						<input type="submit" id="export_type_<?php esc_attr_e( strtoupper( $format ) ); ?>" value=" <?php esc_attr_e( strtoupper( $format ) ); ?> " name="bulk_export_type" class="button-primary" />
					<?php } ?>
				</p>
			</form>
		</div>
		<?php

	}

	/**
	 * @param null $export_type
	 */
	public function export( $export_type = null ) {

		if ( empty( $export_type ) ) {
			$export_type = pods_v( 'export_type', 'get', 'csv' );
		}

		$export_type = trim( strtolower( $export_type ) );

		$type = $export_type;

		$delimiter = ',';

		if ( ! empty( $this->export['formats'][ $export_type ] ) ) {
			$delimiter = $this->export['formats'][ $export_type ];
		}

		$columns = array(
			$this->sql['field_id'] => 'ID',
		);

		if ( empty( $this->fields['export'] ) && $this->pod && ! empty( $this->pod->fields ) ) {
			$this->fields['export'] = $this->pod->fields;
		}

		if ( ! empty( $this->fields['export'] ) ) {
			foreach ( $this->fields['export'] as $field ) {
				$columns[ $field['name'] ] = $field['label'];
			}
		}

		$params = array(
			'full'      => true,
			'flatten'   => true,
			'fields'    => array_keys( $columns ),
			'type'      => $type,
			'delimiter' => $delimiter,
			'columns'   => $columns,
		);

		$items = $this->get_data( $params );

		$data = array(
			'columns' => $columns,
			'items'   => $items,
			'fields'  => $this->fields['export'],
		);

		$migrate = pods_migrate( $type, $delimiter, $data );

		$migrate->export();

		$save_params = array(
			'attach' => true,
		);

		$export_file = $migrate->save( $save_params );

		$this->message( sprintf( __( '<strong>Success:</strong> Your export is ready, you can download it <a href="%s" target="_blank" rel="noopener noreferrer">here</a>', 'pods' ), $export_file ) );

		// echo '<script type="text/javascript">window.open("' . esc_js( $export_file ) . '");</script>';
		$this->get_data();
	}

	/**
	 * @param $field
	 *
	 * @return array|bool|mixed|null
	 */
	public function get_field( $field ) {

		$value = null;

		// use PodsData to get field
		$callback = $this->callback( 'get_field', $field );
		if ( $callback ) {
			return $callback;
		}

		if ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
			if ( 'Pod' == get_class( $this->pod ) ) {
				$value = $this->pod->get_field( $field );
			} else {
				$value = $this->pod->field( $field );
			}
		} elseif ( isset( $this->row[ $field ] ) ) {
			$value = $this->row[ $field ];
		}

		return $this->do_hook( 'get_field', $value, $field );
	}

	/**
	 * Get find() params based on current UI action
	 *
	 * @param null|array  $params
	 * @param null|string $action
	 *
	 * @return array|mixed|void
	 */
	public function get_params( $params = null, $action = null ) {

		if ( null === $action ) {
			$action = $this->action;
		}

		$params = (array) $params;

		$defaults = array(
			'full'    => false,
			'flatten' => true,
			'fields'  => null,
			'type'    => '',
		);

		if ( ! empty( $params ) ) {
			if ( is_object( $params ) ) {
				$params = get_object_vars( $params );
			}

			$params = (object) array_merge( $defaults, $params );
		} else {
			$params = (object) $defaults;
		}

		if ( ! in_array( $action, array( 'manage', 'reorder' ) ) ) {
			$action = 'manage';
		}

		$params_override = false;

		$orderby = array();

		$limit = $this->limit;

		$sql = null;

		if ( 'reorder' === $this->action ) {
			if ( ! empty( $this->reorder['orderby'] ) ) {
				$orderby[ $this->reorder['orderby'] ] = $this->reorder['orderby_dir'];
			} else {
				$orderby[ $this->reorder['on'] ] = $this->reorder['orderby_dir'];
			}

			if ( ! empty( $this->reorder['limit'] ) ) {
				$limit = $this->reorder['limit'];
			}

			if ( ! empty( $this->reorder['sql'] ) ) {
				$sql = $this->reorder['sql'];
			}
		}

		if ( ! empty( $this->orderby ) ) {
			$this->orderby = (array) $this->orderby;

			foreach ( $this->orderby as $order ) {
				if ( false !== strpos( $order, ' ' ) ) {
					$orderby[] = $order;
				} elseif ( ! isset( $orderby[ $order ] ) ) {
					$orderby[ $order ] = $this->orderby_dir;
				}
			}
		}

		if ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
			$find_params = array(
				'where'               => pods_v( $action, $this->where, null, true ),
				'orderby'             => $orderby,
				'page'                => (int) $this->page,
				'pagination'          => true,
				'limit'               => (int) $limit,
				'search'              => $this->searchable,
				'search_query'        => $this->search,
				'search_across'       => $this->search_across,
				'search_across_picks' => $this->search_across_picks,
				'fields'              => $this->fields['search'],
				'filters'             => $this->filters,
				'sql'                 => $sql,
			);

			$params_override = true;
		} else {
			$find_params = array(
				'table'        => $this->sql['table'],
				'id'           => $this->sql['field_id'],
				'index'        => $this->sql['field_index'],
				'where'        => pods_v( $action, $this->where, null, true ),
				'orderby'      => $orderby,
				'page'         => (int) $this->page,
				'pagination'   => true,
				'limit'        => (int) $limit,
				'search'       => $this->searchable,
				'search_query' => $this->search,
				'fields'       => $this->fields['search'],
				'sql'          => $sql,
			);

			if ( ! empty( $this->sql['select'] ) ) {
				$find_params['select'] = $this->sql['select'];
			}
		}//end if

		if ( empty( $find_params['where'] ) && $this->restricted( $this->action ) ) {
			$find_params['where'] = $this->pods_data->query_fields( $this->restrict[ $this->action ], ( is_object( $this->pod ) ? $this->pod->pod_data : null ) );
		}

		if ( $params_override ) {
			$find_params = array_merge( $find_params, (array) $this->params );
		}

		if ( $params->full ) {
			$find_params['limit'] = - 1;
		}

		$find_params = apply_filters( 'pods_ui_get_params', $find_params, ( is_object( $this->pod ) ? $this->pod->pod : null ), $this );

		/**
		 * Filter Pods::find() parameters to make it more easily extended by plugins and developers.
		 *
		 * @param array  $find_params Parameters used with Pods::find()
		 * @param string $action      Current action
		 * @param PodsUI $this        PodsUI instance
		 *
		 * @since 2.6.8
		 */
		$find_params = apply_filters( 'pods_ui_get_find_params', $find_params, $action, $this );

		// Debug purposes
		if ( 1 == pods_v( 'pods_debug_params', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) ) {
			pods_debug( $find_params );
		}

		return $find_params;
	}

	/**
	 * @param null $params
	 *
	 * @return bool
	 * @internal param bool $full Whether to get ALL data or use pagination
	 */
	public function get_data( $params = null ) {

		$action = $this->action;

		$defaults = array(
			'full'    => false,
			'flatten' => true,
			'fields'  => null,
			'type'    => '',
		);

		if ( ! empty( $params ) ) {
			if ( is_object( $params ) ) {
				$params = get_object_vars( $params );
			}

			$params = (object) array_merge( $defaults, $params );
		} else {
			$params = (object) $defaults;
		}

		if ( ! in_array( $action, array( 'manage', 'reorder' ) ) ) {
			$action = 'manage';
		}

		$find_params = $this->get_params( $params, $action );

		if ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
			$this->pod->find( $find_params );

			if ( ! $params->full ) {
				$data = $this->pod->data();

				$this->data = $data;

				if ( ! empty( $this->data ) ) {
					$this->data_keys = array_keys( $this->data );
				}

				$this->total       = $this->pod->total();
				$this->total_found = $this->pod->total_found();
			} else {
				$this->data_full = array();

				$export_params = array(
					'fields'  => $params->fields,
					'flatten' => true,
				);

				if ( in_array( $params->type, array( 'json', 'xml' ) ) ) {
					$export_params['flatten'] = false;
				}

				$export_params = $this->do_hook( 'export_options', $export_params, $params );

				while ( $this->pod->fetch() ) {
					$this->data_full[ $this->pod->id() ] = $this->pod->export( $export_params );
				}

				$this->pod->reset();

				return $this->data_full;
			}//end if
		} else {
			if ( ! empty( $this->data ) ) {
				return $this->data;
			}

			if ( empty( $this->sql['table'] ) ) {
				return $this->data;
			}

			$this->pods_data->select( $find_params );

			if ( ! $params->full ) {
				$this->data = $this->pods_data->rows;

				if ( ! empty( $this->data ) ) {
					$this->data_keys = array_keys( $this->data );
				}

				$this->total       = $this->pods_data->total();
				$this->total_found = $this->pods_data->total_found();
			} else {
				$this->data_full = $this->pods_data->rows;

				if ( ! empty( $this->data_full ) ) {
					$this->data_keys = array_keys( $this->data_full );
				}

				return $this->data_full;
			}
		}//end if

		return $this->data;
	}

	/**
	 * Sort out data alphabetically by a key
	 */
	public function sort_data() {

		// only do this if we have a default orderby
		if ( isset( $this->orderby['default'] ) ) {
			$orderby = $this->orderby['default'];
			foreach ( $this->data as $k => $v ) {
				$sorter[ $k ] = strtolower( $v[ $orderby ] );
			}
			if ( $this->orderby_dir == 'ASC' ) {
				asort( $sorter );
			} else {
				arsort( $sorter );
			}
			foreach ( $sorter as $key => $val ) {
				$intermediary[] = $this->data[ $key ];
			}
			if ( isset( $intermediary ) ) {
				$this->data      = $intermediary;
				$this->data_keys = array_keys( $this->data );
			}
		}
	}

	/**
	 * @param int  $counter
	 * @param null $method
	 *
	 * @return array|false
	 */
	public function get_row( &$counter = 0, $method = null ) {

		if ( ! empty( $this->row ) && 0 < (int) $this->id && 'table' !== $method ) {
			return $this->row;
		}

		if ( is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
			$this->row = $this->pod->fetch();
		} else {
			$this->row = false;

			if ( ! empty( $this->data ) ) {
				if ( empty( $this->data_keys ) || count( $this->data ) != count( $this->data_keys ) ) {
					$this->data_keys = array_keys( $this->data );
				}

				if ( count( $this->data ) == $this->total && isset( $this->data_keys[ $counter ] ) && isset( $this->data[ $this->data_keys[ $counter ] ] ) ) {
					$this->row = $this->data[ $this->data_keys[ $counter ] ];

					$counter ++;
				}
			}

			if ( false === $this->row && 0 < (int) $this->id && ! empty( $this->sql['table'] ) ) {
				$this->pods_data->select(
					array(
						'table' => $this->sql['table'],
						'where' => '`' . pods_sanitize( $this->sql['field_id'] ) . '` = ' . (int) $this->id,
						'limit' => 1,
					)
				);

				$this->row = $this->pods_data->fetch();
			}
		}//end if

		return $this->row;
	}

	/**
	 * @param bool $reorder
	 *
	 * @return mixed|null
	 */
	public function manage( $reorder = false ) {
		if ( false !== $this->callback_action( 'manage', $reorder ) ) {
			return null;
		}

		if ( ! empty( $this->action_bulk ) && ! empty( $this->actions_bulk ) && isset( $this->actions_bulk[ $this->action_bulk ] ) && ! in_array( $this->action_bulk, $this->actions_disabled ) && ( ! empty( $this->bulk ) || 'export' === $this->action_bulk ) ) {
			if ( empty( $_REQUEST[ $this->num_prefix . '_wpnonce' . $this->num ] ) || false === wp_verify_nonce( $_REQUEST[ $this->num_prefix . '_wpnonce' . $this->num ], 'pods-ui-action-bulk' ) ) {
				pods_message( __( 'Invalid bulk request, please try again.', 'pods' ) );
			} elseif ( false !== $this->callback_bulk( $this->action_bulk, $this->bulk ) ) {
				return null;
			} elseif ( 'delete' === $this->action_bulk ) {
				$this->delete_bulk();

				return;
			} elseif ( 'export' === $this->action_bulk ) {
				$this->export_bulk();

				return;
			}
		}

		$this->screen_meta();

		wp_enqueue_script( 'jquery' );

		if ( true === $reorder ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		$icon_style = '';
		if ( false !== $this->icon ) {
			$icon_style = ' style="background-position:0 0;background-size:100%;background-image:url(' . esc_url( $this->icon ) . ');"';
		}

		/**
		 * Allow adding custom CSS classes to the Pods::manage() container.
		 *
		 * @since 2.6.8
		 *
		 * @param array  $custom_container_classes List of custom classes to use.
		 * @param PodsUI $this                     PodsUI instance.
		 */
		$custom_container_classes = apply_filters( 'pods_ui_manage_custom_container_classes', array() );

		if ( is_admin() ) {
			array_unshift( $custom_container_classes, 'wrap' );
		}

		array_unshift( $custom_container_classes, 'pods-admin' );
		array_unshift( $custom_container_classes, 'pods-ui' );

		$custom_container_classes = array_map( 'sanitize_html_class', $custom_container_classes );
		$custom_container_classes = implode( ' ', $custom_container_classes );
		?>
	<div class="<?php echo esc_attr( $custom_container_classes ); ?>">
		<div class="pods-admin-container">
			<?php if ( ! in_array( 'manage_header', $this->actions_disabled, true ) && ! in_array( 'manage_header', $this->actions_hidden, true ) ) : ?>
			<div id="icon-edit-pages" class="icon32"<?php echo $icon_style; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>>
				<br />
			</div>
			<h2>
				<?php
				if ( true === $reorder ) {
					echo wp_kses_post( $this->header['reorder'] );

					if ( ! in_array( 'manage', $this->actions_disabled ) && ! in_array( 'manage', $this->actions_hidden ) && ! $this->restricted( 'manage' ) ) {
						$link = pods_query_arg(
							array(
								$this->num_prefix . 'action' . $this->num => 'manage',
								$this->num_prefix . 'id' . $this->num     => '',
							),
							self::$allowed, $this->exclusion()
						);

						if ( ! empty( $this->action_links['manage'] ) ) {
							$link = $this->action_links['manage'];
						}
					?>
					<small>(<a href="<?php echo esc_url( $link ); ?>">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $this->heading['manage'] ); ?></a>)</small>
					<?php
					}
				} else {
					echo wp_kses_post( $this->header['manage'] );
				}

				if ( ! in_array( 'add', $this->actions_disabled ) && ! in_array( 'add', $this->actions_hidden ) && ! $this->restricted( 'add' ) ) {
					$link = pods_query_arg(
						array(
							$this->num_prefix . 'action' . $this->num => 'add',
							$this->num_prefix . 'id' . $this->num     => '',
							$this->num_prefix . 'do' . $this->num     => '',
						),
						self::$allowed, $this->exclusion()
					);

					if ( ! empty( $this->action_links['add'] ) ) {
						$link = $this->action_links['add'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo wp_kses_post( $this->label['add_new'] ); ?></a>
					<?php
				}
				if ( ! in_array( 'reorder', $this->actions_disabled ) && ! in_array( 'reorder', $this->actions_hidden ) && false !== $this->reorder['on'] && ! $this->restricted( 'reorder' ) ) {
					$link = pods_query_arg( array( $this->num_prefix . 'action' . $this->num => 'reorder' ), self::$allowed, $this->exclusion() );

					if ( ! empty( $this->action_links['reorder'] ) ) {
						$link = $this->action_links['reorder'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo wp_kses_post( $this->label['reorder'] ); ?></a>
					<?php
				}
				?>
			</h2>
			<?php endif; ?>

			<form id="posts-filter" action="" method="get">
				<?php
				$excluded_filters = array(
					$this->num_prefix . 'search' . $this->num,
					$this->num_prefix . 'pg' . $this->num,
					$this->num_prefix . 'action' . $this->num,
					$this->num_prefix . 'action_bulk' . $this->num,
					$this->num_prefix . 'action_bulk_ids' . $this->num,
					$this->num_prefix . '_wpnonce' . $this->num,
				);

				$filters = $this->filters;

				foreach ( $filters as $k => $filter ) {
					if ( isset( $this->pod->fields[ $filter ] ) ) {
						$filter_field = $this->pod->fields[ $filter ];

						if ( isset( $this->fields['manage'][ $filter ] ) && is_array( $this->fields['manage'][ $filter ] ) ) {
							$filter_field = pods_config_merge_data( $filter_field, $this->fields['manage'][ $filter ] );
						}
					} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
						$filter_field = $this->fields['manage'][ $filter ];
					} else {
						unset( $filters[ $k ] );
						continue;
					}

					if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
						if ( '' == pods_v( 'filter_' . $filter . '_start', 'get', '', true ) && '' == pods_v( 'filter_' . $filter . '_end', 'get', '', true ) ) {
							unset( $filters[ $k ] );
							continue;
						}
					} elseif ( '' === pods_v( 'filter_' . $filter, 'get', '' ) ) {
						unset( $filters[ $k ] );
						continue;
					}

					$excluded_filters[] = 'filter_' . $filter . '_start';
					$excluded_filters[] = 'filter_' . $filter . '_end';
					$excluded_filters[] = 'filter_' . $filter;
				}//end foreach

				$this->hidden_vars( $excluded_filters );

				if ( false !== $this->callback( 'header', $reorder ) ) {
					return null;
				}

				if ( false === $this->data ) {
					$this->get_data();
				} elseif ( $this->sortable ) {
					// we have the data already as an array
					$this->sort_data();
				}

					if ( 'export' === $this->action && ! in_array( 'export', $this->actions_disabled, true ) ) {
						$this->export();
					}

					if ( ( ! empty( $this->data ) || false !== $this->search || ( $this->filters_enhanced && ! empty( $this->views ) ) ) && ( ( $this->filters_enhanced && ! empty( $this->views ) ) || false !== $this->searchable ) ) {
						pods_form_enqueue_style( 'pods-styles' );

						if ( $this->filters_enhanced ) {
							$this->filters();
						} else {
							?>
							<p class="search-box" align="right">
							<?php
							foreach ( $this->filters as $filter ) {
								if ( isset( $this->pod->fields[ $filter ] ) ) {
									$filter_field = $this->pod->fields[ $filter ];

									if ( isset( $this->fields['manage'][ $filter ] ) ) {
										$filter_field = pods_config_merge_data( $filter_field, $this->fields['manage'][ $filter ] );
									}
								} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
									$filter_field = $this->fields['manage'][ $filter ];
								} else {
									continue;
								}

								$filter_field[ 'disable_dfv' ] = true;
								?>
								<span class="pods-form-ui-filter-container pods-form-ui-filter-container-<?php echo esc_attr( $filter ); ?>">
								<?php
								if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
									$start = pods_v( 'filter_' . $filter . '_start', 'get', pods_v( 'filter_default', $filter_field, '', true ), true );
									$end   = pods_v( 'filter_' . $filter . '_end', 'get', pods_v( 'filter_ongoing_default', $filter_field, '', true ), true );

									// override default value
									$filter_field['default_value']                          = '';
									$filter_field[ $filter_field['type'] . '_allow_empty' ] = 1;

									if ( ! empty( $start ) && ! in_array( $start, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
										$start = PodsForm::field_method( $filter_field['type'], 'convert_date', $start, 'n/j/Y' );
									}

									if ( ! empty( $end ) && ! in_array( $end, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
										$end = PodsForm::field_method( $filter_field['type'], 'convert_date', $end, 'n/j/Y' );
									}
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
										<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
									<?php
										// Prevent p div issues.
										echo str_replace(
											array(
												'<div',
												'</div>',
											),
											array(
												'<span',
												'</span>',
											),
											PodsForm::field( 'filter_' . $filter . '_start', $start, $filter_field['type'], $filter_field )
										);
									?>

									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">
										to
									</label>
								<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter . '_end', $end, $filter_field['type'], $filter_field )
									);
								} elseif ( 'pick' === $filter_field['type'] ) {
									$value = pods_v( 'filter_' . $filter );

									if ( '' === $value ) {
										$value = pods_v( 'filter_default', $filter_field );}

									// override default value
									$filter_field['default_value'] = '';

									$filter_field['pick_format_type']   = 'single';
									$filter_field['pick_format_single'] = 'dropdown';
									$filter_field['pick_allow_add_new'] = 0;

									$filter_field['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $this->fields['search'] ?: $this->fields['manage'], array(), true ), '', true );
									$filter_field['input_helper'] = pods_v( 'ui_input_helper', $filter_field, $filter_field['input_helper'], true );

												$options = $filter_field;
								?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
										<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
								<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter, $value, 'pick', $options )
									);
								} elseif ( 'boolean' === $filter_field['type'] ) {
									$value = pods_v( 'filter_' . $filter, 'get', '' );

									if ( '' === $value ) {
										$value = pods_v( 'filter_default', $filter_field );}

									// override default value
									$filter_field['default_value'] = '';

									$filter_field['pick_format_type']   = 'single';
									$filter_field['pick_format_single'] = 'dropdown';
									$filter_field['pick_allow_add_new'] = 0;

									$filter_field['pick_object'] = 'custom-simple';
									$filter_field['pick_custom'] = array(
										'1' => pods_v( 'boolean_yes_label', $filter_field, __( 'Yes', 'pods' ), true ),
										'0' => pods_v( 'boolean_no_label', $filter_field, __( 'No', 'pods' ), true ),
									);

									$filter_field['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $this->fields['search'] ?: $this->fields['manage'], array(), true ), '', true );
									$filter_field['input_helper'] = pods_v( 'ui_input_helper', $filter_field, $filter_field['input_helper'], true );

												$options = $filter_field;
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter, $value, 'pick', $options )
									);
								} else {
									$value = pods_v( 'filter_' . $filter );

									if ( '' === $value ) {
										$value = pods_v( 'filter_default', $filter_field );
									}

									// override default value
									$filter_field['default_value'] = '';

									$options                 = array();
									$options['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $this->fields['search'] ?: $this->fields['manage'], array(), true ), '', true );
									$options['input_helper'] = pods_v( 'ui_input_helper', $options, $options['input_helper'], true );
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
										<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter, $value, 'text', $options )
									);
								}//end if
								?>
									</span>
								<?php
							}//end foreach

							if ( false !== $this->do_hook( 'filters_show_search', true ) ) {
							?>
								<span class="pods-form-ui-filter-container pods-form-ui-filter-container-search">
									<label<?php echo ( empty( $this->filters ) ) ? ' class="screen-reader-text"' : ''; ?> for="<?php echo esc_attr( $this->num_prefix ); ?>page-search<?php echo esc_attr( $this->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>:</label>
									<?php echo PodsForm::field( $this->num_prefix . 'search' . $this->num, $this->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $this->num . '-input' ), 'disable_dfv' => true ) ); ?>
								</span>
							<?php
							} else {
								echo PodsForm::field( $this->num_prefix . 'search' . $this->num, '', 'hidden' );
							}

							echo PodsForm::submit_button( $this->header['search'], 'button', false, false, array( 'id' => $this->num_prefix . 'search' . $this->num . '-submit' ) );

							if ( 0 < strlen( $this->search ) ) {
								$clear_filters = array(
									$this->num_prefix . 'search' . $this->num => false,
								);

								foreach ( $this->filters as $filter ) {
									$clear_filters[ 'filter_' . $filter . '_start' ] = false;
									$clear_filters[ 'filter_' . $filter . '_end' ]   = false;
									$clear_filters[ 'filter_' . $filter ]            = false;
								}
								?>
								<br class="clear" />
								<small>[<a href="<?php echo esc_url( pods_query_arg( $clear_filters, array( $this->num_prefix . 'orderby' . $this->num, $this->num_prefix . 'orderby_dir' . $this->num, $this->num_prefix . 'limit' . $this->num, 'page' ), $this->exclusion() ) ); ?>"><?php _e( 'Reset Filters', 'pods' ); ?></a>]</small>
								<br class="clear" />
								<?php
							}
							?>
							</p>
							<?php
						}//end if
					} else {
						?>
						<br class="clear" />
						<?php
					}//end if

					if ( ! empty( $this->data ) && ( false !== $this->pagination_total || false !== $this->pagination || true === $reorder ) || ( ! in_array( 'export', $this->actions_disabled ) && ! in_array( 'export', $this->actions_hidden ) ) || ! empty( $this->actions_disabled ) ) {
						?>
						<div class="tablenav">
						<?php
						if ( ! empty( $this->data ) && ! empty( $this->actions_bulk ) ) {
						?>
						<div class="alignleft actions">
							<?php wp_nonce_field( 'pods-ui-action-bulk', $this->num_prefix . '_wpnonce' . $this->num, false ); ?>

							<select name="<?php echo esc_attr( $this->num_prefix ); ?>action_bulk<?php echo esc_attr( $this->num ); ?>">
								<option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'pods' ); ?></option>

								<?php
								foreach ( $this->actions_bulk as $action => $action_data ) {
									if ( in_array( $action, $this->actions_hidden ) || in_array( $action, $this->actions_hidden ) ) {
										continue;}

									if ( ! isset( $action_data['label'] ) ) {
										$action_data['label'] = ucwords( str_replace( '_', ' ', $action ) );}
								?>
								<option value="<?php echo esc_attr( $action ); ?>"><?php esc_html_e( $action_data['label'] ); ?></option>
								<?php
								}
								?>
							</select>

							<input type="submit" id="<?php echo esc_attr( $this->num_prefix ); ?>doaction_bulk<?php echo esc_attr( $this->num ); ?>" class="button-secondary action" value="<?php esc_attr_e( 'Apply', 'pods' ); ?>">
						</div>
					<?php
						}//end if

						if ( true !== $reorder && ( false !== $this->pagination_total || false !== $this->pagination ) ) {
							?>
							<div class="tablenav-pages<?php esc_attr_e( ( $this->limit < $this->total_found || 1 < $this->page ) ? '' : ' one-page' ); ?>">
								<?php $this->pagination( true ); ?>
							</div>
							<?php
						}

						if ( true === $reorder ) {
							$link = pods_query_arg(
								array(
									$this->num_prefix . 'action' . $this->num => 'manage',
									$this->num_prefix . 'id' . $this->num     => '',
								), self::$allowed, $this->exclusion()
							);

							if ( ! empty( $this->action_links['manage'] ) ) {
								$link = $this->action_links['manage'];
							}
							?>
							<input type="button" value="<?php esc_attr_e( 'Update Order', 'pods' ); ?>" class="button" onclick="jQuery('form.admin_ui_reorder_form').submit();" />
							<input type="button" value="<?php esc_attr_e( 'Cancel', 'pods' ); ?>" class="button" onclick="document.location='<?php echo esc_js( $link ); ?>';" />
						</form>
					<?php
						} elseif ( ! in_array( 'export', $this->actions_disabled ) && ! in_array( 'export', $this->actions_hidden ) ) {
							$export_document_location = pods_slash(
								pods_query_arg(
									array(
										$this->num_prefix . 'action_bulk' . $this->num => 'export',
										$this->num_prefix . '_wpnonce' . $this->num => wp_create_nonce( 'pods-ui-action-bulk' ),
									), self::$allowed, $this->exclusion()
								)
							);
							?>
							<div class="alignleft actions">
								<input type="button" value="<?php esc_attr_e( sprintf( __( 'Export all %s', 'pods' ), $this->items ) ); ?>" class="button" onclick="document.location='<?php echo $export_document_location; ?>';" />
							</div>
							<?php
						}//end if
						?>
						<br class="clear" />
						</div>
						<?php
					} else {
						?>
						<br class="clear" />
						<?php
					}//end if
				?>
				<div class="clear"></div>
				<?php
				if ( empty( $this->data ) && false !== $this->default_none && false === $this->search ) {
					?>
					<p><?php _e( 'Please use the search filter(s) above to display data', 'pods' ); ?>
					<?php
					if ( $this->export ) {
					?>
					, <?php _e( 'or click on an Export to download a full copy of the data', 'pods' ); ?><?php } ?>.</p>
					<?php
				} else {
					$this->table( $reorder );}
				if ( ! empty( $this->data ) ) {
					if ( true !== $reorder && ( false !== $this->pagination_total || false !== $this->pagination ) ) {
						?>
						<div class="tablenav">
							<div class="tablenav-pages<?php esc_attr_e( ( $this->limit < $this->total_found || 1 < $this->page ) ? '' : ' one-page' ); ?>">
								<?php $this->pagination(); ?>
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
		/**
		 * Allow additional output after the container area of the Pods UI manage screen.
		 *
		 * @since 2.7.17
		 */
		do_action( 'pods_ui_manage_after_container' );
		?>
	</div>
	<?php
		if ( $this->filters_enhanced ) {
			$this->filters_popup();
		}
	}

	public function filters() {

		include_once ABSPATH . 'wp-admin/includes/template.php';

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		$filters = $this->filters;

		foreach ( $filters as $k => $filter ) {
			if ( isset( $this->pod->fields[ $filter ] ) ) {
				$filter_field = $this->pod->fields[ $filter ];

				if ( isset( $this->fields['manage'][ $filter ] ) ) {
					$filter_field = pods_config_merge_data( $filter_field, $this->fields['manage'][ $filter ] );
				}
			} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
				$filter_field = $this->fields['manage'][ $filter ];
			} else {
				continue;
			}

			if ( isset( $filter_field ) && in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
				if ( '' == pods_v( 'filter_' . $filter . '_start', 'get', '', true ) && '' == pods_v( 'filter_' . $filter . '_end', 'get', '', true ) ) {
					unset( $filters[ $k ] );
				}
			} elseif ( '' === pods_v( 'filter_' . $filter, 'get', '' ) ) {
				unset( $filters[ $k ] );
			}
		}

		$filtered = false;

		if ( ! empty( $filters ) ) {
			$filtered = true;
		}
		?>
		<div class="pods-ui-filter-bar">
			<div class="pods-ui-filter-bar-primary">
				<?php
				if ( ! empty( $this->views ) ) {
					?>
					<ul class="subsubsub">
						<li class="pods-ui-filter-view-label">
							<strong><?php echo wp_kses_post( $this->heading['views'] ); ?></strong></li>

						<?php
						foreach ( $this->views as $view => $label ) {
							if ( false === strpos( $label, '<a' ) ) {
								$link = pods_query_arg(
									array(
										$this->num_prefix . 'view' . $this->num => $view,
										$this->num_prefix . 'pg' . $this->num => '',
									), self::$allowed, $this->exclusion()
								);

								if ( $this->view == $view ) {
									$label = '<a href="' . esc_url( $link ) . '" class="current">' . esc_html( $label ) . '</a>';
								} else {
									$label = '<a href="' . esc_url( $link ) . '">' . esc_html( $label ) . '</a>';
								}
							} else {
								$label = wp_kses_post( $label );
							}
							?>
							<li class="<?php echo esc_attr( $view ); ?>">
								<?php
								/* Escaped above to support links */
								echo $label;
								?>
							</li>
							<?php
						}//end foreach
						?>
					</ul>
					<?php
				}//end if
				?>

				<?php
				if ( false !== $this->search && false !== $this->searchable ) {
					?>
					<p class="search-box">
						<?php
						if ( $filtered || '' != pods_v( $this->num_prefix . 'search' . $this->num, 'get', '', true ) ) {
							$clear_filters = array(
								$this->num_prefix . 'search' . $this->num => false,
							);

							foreach ( $this->filters as $filter ) {
								$clear_filters[ 'filter_' . $filter . '_start' ] = false;
								$clear_filters[ 'filter_' . $filter . '_end' ]   = false;
								$clear_filters[ 'filter_' . $filter ]            = false;
							}
							?>
							<a href="
							<?php
							echo esc_url(
								pods_query_arg(
									$clear_filters, array(
										$this->num_prefix . 'orderby' . $this->num,
										$this->num_prefix . 'orderby_dir' . $this->num,
										$this->num_prefix . 'limit' . $this->num,
										'page',
									), $this->exclusion()
								)
							);
							?>
							" class="pods-ui-filter-reset">[<?php _e( 'Reset', 'pods' ); ?>]</a>
							<?php
						}//end if

						if ( false !== $this->do_hook( 'filters_show_search', true ) ) {
							?>
							&nbsp;&nbsp;
							<label class="screen-reader-text" for="<?php echo esc_attr( $this->num_prefix ); ?>page-search<?php echo esc_attr( $this->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>:</label>
							<?php echo PodsForm::field( $this->num_prefix . 'search' . $this->num, $this->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $this->num . '-input' ), 'disable_dfv' => true ) ); ?>
							<?php
						} else {
							echo PodsForm::field( $this->num_prefix . 'search' . $this->num, '', 'hidden' );
						}
						?>

						<?php echo PodsForm::submit_button( $this->header['search'], 'button', false, false, array( 'id' => $this->num_prefix . 'search' . $this->num . '-submit' ) ); ?>
					</p>
					<?php
				}//end if
				?>
			</div>

			<?php
			if ( ! empty( $this->filters ) ) {
				?>
				<div class="pods-ui-filter-bar-secondary">
					<ul class="subsubsub">
						<?php
						if ( ! $filtered ) {
							?>
							<li class="pods-ui-filter-bar-add-filter">
								<a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox" title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
									<?php _e( 'Advanced Filters', 'pods' ); ?>
								</a>
							</li>
							<?php
						} else {
							?>
							<li class="pods-ui-filter-bar-add-filter">
								<a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox" title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>"> + <?php _e( 'Add Filter', 'pods' ); ?>
								</a>
							</li>
							<?php
						}

						foreach ( $filters as $filter ) {
							$value = pods_v( 'filter_' . $filter );

							if ( isset( $this->pod->fields[ $filter ] ) ) {
								$filter_field = $this->pod->fields[ $filter ];

								if ( isset( $this->fields['manage'][ $filter ] ) ) {
									$filter_field = pods_config_merge_data( $filter_field, $this->fields['manage'][ $filter ] );
								}
							} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
								$filter_field = $this->fields['manage'][ $filter ];
							} else {
								continue;
							}

							$data_filter = 'filter_' . $filter;

							$start       = '';
							$end         = '';
							$value_label = '';

							if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
								$start = pods_v( 'filter_' . $filter . '_start', 'get', '', true );
								$end   = pods_v( 'filter_' . $filter . '_end', 'get', '', true );

								if ( ! empty( $start ) && ! in_array(
									$start, array(
										'0000-00-00',
										'0000-00-00 00:00:00',
										'00:00:00',
									)
								) ) {
									$start = PodsForm::field_method( $filter_field['type'], 'convert_date', $start, 'n/j/Y' );
								}

								if ( ! empty( $end ) && ! in_array(
									$end, array(
										'0000-00-00',
										'0000-00-00 00:00:00',
										'00:00:00',
									)
								) ) {
									$end = PodsForm::field_method( $filter_field['type'], 'convert_date', $end, 'n/j/Y' );
								}

								$value = trim( $start . ' - ' . $end, ' -' );

								$data_filter = 'filter_' . $filter . '_start';
							} elseif ( 'pick' === $filter_field['type'] ) {
								$value_label = trim( (string) PodsForm::field_method( 'pick', 'value_to_label', $filter, $value, $filter_field, $this->pod->pod_data, null ) );
							} elseif ( 'boolean' === $filter_field['type'] ) {
								$yesno_options = array(
									'1' => pods_v( 'boolean_yes_label', $filter_field, __( 'Yes', 'pods' ), true ),
									'0' => pods_v( 'boolean_no_label', $filter_field, __( 'No', 'pods' ), true ),
								);

								if ( isset( $yesno_options[ (string) $value ] ) ) {
									$value_label = $yesno_options[ (string) $value ];
								}
							}//end if

							if ( strlen( $value_label ) < 1 ) {
								$value_label = $value;
							}
							?>
							<li class="pods-ui-filter-bar-filter" data-filter="<?php echo esc_attr( $data_filter ); ?>">
								<a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox" title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
									<strong><?php esc_html_e( $filter_field['label'] ); ?>:</strong>
									<?php esc_html_e( $value_label ); ?>
								</a>

								<a href="#remove-filter" class="remove-filter" title="<?php esc_attr_e( 'Remove Filter', 'pods' ); ?>">x</a>

								<?php
								if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
									echo PodsForm::field( 'filter_' . $filter . '_start', $start, 'hidden' );
									echo PodsForm::field( 'filter_' . $filter . '_end', $end, 'hidden' );
								} else {
									echo PodsForm::field( $data_filter, $value, 'hidden' );
								}
								?>
							</li>
							<?php
						}//end foreach
						?>
					</ul>
				</div>
				<?php
			}//end if
			?>
		</div>

		<script type="text/javascript">
			document.addEventListener( 'DOMContentLoaded', function( event ) {
				jQuery( '.pods-ui-filter-bar-secondary' ).on( 'click', '.remove-filter', function ( e ) {
					jQuery( '.pods-ui-filter-popup #' + jQuery( this ).parent().data( 'filter' ) ).remove();

					jQuery( this ).parent().find( 'input' ).each( function () {
						jQuery( this ).remove();
					} );

					jQuery( 'form#posts-filter [name="<?php echo esc_attr( $this->num_prefix ); ?>pg<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );
					jQuery( 'form#posts-filter [name="<?php echo esc_attr( $this->num_prefix ); ?>action<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );
					jQuery( 'form#posts-filter [name="<?php echo esc_attr( $this->num_prefix ); ?>action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );
					jQuery( 'form#posts-filter [name="<?php echo esc_attr( $this->num_prefix ); ?>_wpnonce<?php echo esc_attr( $this->num ); ?>"]' ).prop( 'disabled', true );

					jQuery( 'form#posts-filter' ).submit();

					e.preventDefault();
				} );
			} );
		</script>
		<?php
	}

	public function filters_popup() {

		$filters = $this->filters;
		?>
		<div id="pods-ui-posts-filter-popup" class="pods-hidden">
			<form action="" method="get" class="pods-ui-posts-filter-popup">
				<h2><?php _e( 'Advanced Filters', 'pods' ); ?></h2>

				<div class="pods-ui-posts-filters">
					<?php
					$excluded_filters = array(
						$this->num_prefix . 'search' . $this->num,
						$this->num_prefix . 'pg' . $this->num,
						$this->num_prefix . 'action' . $this->num,
						$this->num_prefix . 'action_bulk' . $this->num,
						$this->num_prefix . 'action_bulk_ids' . $this->num,
						$this->num_prefix . '_wpnonce' . $this->num,
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
						if ( in_array( $k, $excluded_filters ) || strlen( $v ) < 1 ) {
							continue;
						}
						?>
						<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
						<?php
					}

					$zebra = true;

					foreach ( $filters as $filter ) {
						if ( empty( $filter ) ) {
							continue;
						}

						if ( isset( $this->pod->fields[ $filter ] ) ) {
							$filter_field = $this->pod->fields[ $filter ];

							if ( isset( $this->fields['manage'][ $filter ] ) ) {
								$filter_field = pods_config_merge_data( $filter_field, $this->fields['manage'][ $filter ] );
							}
						} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
							$filter_field = $this->fields['manage'][ $filter ];
						} else {
							continue;
						}

						$filter_field[ 'disable_dfv' ] = true;
						?>
						<p class="pods-ui-posts-filter-toggled pods-ui-posts-filter-<?php echo esc_attr( $filter . ( $zebra ? ' clear' : '' ) ); ?>">
							<?php
							if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
								$start = pods_v( 'filter_' . $filter . '_start', 'get', pods_v( 'filter_default', $filter_field, '', true ), true );
								$end   = pods_v( 'filter_' . $filter . '_end', 'get', pods_v( 'filter_ongoing_default', $filter_field, '', true ), true );

								// override default value
								$filter_field['default_value']                          = '';
								$filter_field[ $filter_field['type'] . '_allow_empty' ] = 1;

								if ( ! empty( $start ) && ! in_array(
									$start, array(
										'0000-00-00',
										'0000-00-00 00:00:00',
										'00:00:00',
									)
								) ) {
									$start = PodsForm::field_method( $filter_field['type'], 'convert_date', $start, 'n/j/Y' );
								}

								if ( ! empty( $end ) && ! in_array(
									$end, array(
										'0000-00-00',
										'0000-00-00 00:00:00',
										'00:00:00',
									)
								) ) {
									$end = PodsForm::field_method( $filter_field['type'], 'convert_date', $end, 'n/j/Y' );
								}
								?>
								<span class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( ( empty( $start ) && empty( $end ) ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( ( empty( $start ) && empty( $end ) ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
									<?php esc_html_e( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php esc_attr_e( ( empty( $start ) && empty( $end ) ) ? ' pods-hidden' : '' ); ?>">
								<?php
								// Prevent p div issues.
								echo str_replace(
									array(
										'<div',
										'</div>',
									),
									array(
										'<span',
										'</span>',
									),
									PodsForm::field( 'filter_' . $filter . '_start', $start, $filter_field['type'], $filter_field )
								);
								?>

									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">to</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter . '_end', $end, $filter_field['type'], $filter_field )
									);
									?>
							</span>
								<?php
							} elseif ( 'pick' === $filter_field['type'] ) {
								$value = pods_v( 'filter_' . $filter, 'get', '', true );

								if ( '' === $value ) {
									$value = pods_v( 'filter_default', $filter_field, '', true );
								}

								// override default value
								$filter_field['default_value'] = '';

								$filter_field['pick_format_type']   = 'single';
								$filter_field['pick_format_single'] = 'dropdown';
								$filter_field['pick_allow_add_new'] = 0;

								$filter_field['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $this->fields['search'] ?: $this->fields['manage'], array(), true ), '', true );
								$filter_field['input_helper'] = pods_v( 'ui_input_helper', $filter_field, $filter_field['input_helper'], true );

								$options = $filter_field;
								?>
								<span class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php esc_html_e( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php esc_attr_e( '' === $value ? ' pods-hidden' : '' ); ?>">
								<?php
								// Prevent p div issues.
								echo str_replace(
									array(
										'<div',
										'</div>',
									),
									array(
										'<span',
										'</span>',
									),
									PodsForm::field( 'filter_' . $filter, $value, 'pick', $options )
								);
								?>
							</span>
								<?php
							} elseif ( 'boolean' === $filter_field['type'] ) {
								$value = pods_v( 'filter_' . $filter, 'get', '', true );

								if ( '' === $value ) {
									$value = pods_v( 'filter_default', $filter_field, '', true );
								}

								// override default value
								$filter_field['default_value'] = '';

								$filter_field['pick_format_type']   = 'single';
								$filter_field['pick_format_single'] = 'dropdown';
								$filter_field['pick_allow_add_new'] = 0;

								$filter_field['pick_object'] = 'custom-simple';
								$filter_field['pick_custom'] = array(
									'1' => pods_v( 'boolean_yes_label', $filter_field, __( 'Yes', 'pods' ), true ),
									'0' => pods_v( 'boolean_no_label', $filter_field, __( 'No', 'pods' ), true ),
								);

								$filter_field['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $this->fields['search'] ?: $this->fields['manage'], array(), true ), '', true );
								$filter_field['input_helper'] = pods_v( 'ui_input_helper', $filter_field, $filter_field['input_helper'], true );

								$options = $filter_field;
								?>
								<span class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php esc_html_e( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php esc_attr_e( '' === $value ? ' pods-hidden' : '' ); ?>">
								<?php
								// Prevent p div issues.
								echo str_replace(
									array(
										'<div',
										'</div>',
									),
									array(
										'<span',
										'</span>',
									),
									PodsForm::field( 'filter_' . $filter, $value, 'pick', $options )
								);
								?>
							</span>
								<?php
							} else {
								$value = pods_v( 'filter_' . $filter, 'get', '', true );

								if ( '' === $value ) {
									$value = pods_v( 'filter_default', $filter_field, '', true );
								}

								$options = array(
									'input_helper' => pods_v( 'ui_input_helper', pods_v( 'options', pods_v( $filter, $this->fields['search'], array(), true ), array(), true ), '', true ),
								);

								if ( empty( $options['input_helper'] ) && isset( $filter_field['input_helper'] ) ) {
									$options['input_helper'] = $filter_field['input_helper'];
								}
								?>
								<span class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php esc_html_e( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>">
								<?php
								// Prevent p div issues.
								echo str_replace(
									array(
										'<div',
										'</div>',
									),
									array(
										'<span',
										'</span>',
									),
									PodsForm::field( 'filter_' . $filter, $value, 'text', $options )
								);
								?>
							</span>
								<?php
							}//end if
							?>
						</p>
						<?php
						$zebra = empty( $zebra );
					}//end foreach
					?>

					<p class="pods-ui-posts-filter-toggled pods-ui-posts-filter-search<?php echo esc_attr( $zebra ? ' clear' : '' ); ?>">
						<label for="<?php echo esc_attr( $this->num_prefix ); ?>pods-form-ui-search<?php echo esc_attr( $this->num ); ?>"><?php _e( 'Search Text', 'pods' ); ?></label>
						<?php echo PodsForm::field( $this->num_prefix . 'search' . $this->num, pods_v( $this->num_prefix . 'search' . $this->num ), 'text', [ 'disable_dfv' => true ] ); ?>
					</p>

					<?php $zebra = empty( $zebra ); ?>
				</div>

				<p class="submit<?php echo esc_attr( $zebra ? ' clear' : '' ); ?>">
					<input type="submit" value="<?php echo esc_attr( $this->header['search'] ); ?>" class="button button-primary" />
				</p>
			</form>
		</div>

		<script type="text/javascript">
			document.addEventListener( 'DOMContentLoaded', function( event ) {
				jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggle.toggle-on', function ( e ) {
					jQuery( this ).parent().find( '.pods-ui-posts-filter' ).removeClass( 'pods-hidden' );

					jQuery( this ).hide();
					jQuery( this ).parent().find( '.toggle-off' ).show();
				} );

				jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggle.toggle-off', function ( e ) {
					jQuery( this ).parent().find( '.pods-ui-posts-filter' ).addClass( 'pods-hidden' );
					jQuery( this ).parent().find( 'select, input' ).val( '' );

					jQuery( this ).hide();
					jQuery( this ).parent().find( '.toggle-on' ).show();
				} );

				jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggled label', function ( e ) {
					if ( jQuery( this ).parent().find( '.pods-ui-posts-filter' ).hasClass( 'pods-hidden' ) ) {
						jQuery( this ).parent().find( '.pods-ui-posts-filter' ).removeClass( 'pods-hidden' );

						jQuery( this ).parent().find( '.toggle-on' ).hide();
						jQuery( this ).parent().find( '.toggle-off' ).show();
					}
					else {
						jQuery( this ).parent().find( '.pods-ui-posts-filter' ).addClass( 'pods-hidden' );
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
	public function table( $reorder = false ) {

		if ( false !== $this->callback( 'table', $reorder ) ) {
			return null;
		}

		if ( empty( $this->data ) ) {
			?>
			<p><?php echo pods_v( 'label_no_items_found', $this->label, sprintf( __( 'No %s found', 'pods' ), $this->items ) ); ?></p>
			<?php
			return false;
		}

		$tableless_field_types = PodsForm::tableless_field_types();

		if ( true === $reorder && ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] ) {

			?>
			<style type="text/css">
			table.widefat.fixed tbody.reorderable tr {
				height: 50px;
			}

			.dragme {
				background:          url(<?php echo esc_url( PODS_URL ); ?>/ui/images/handle.gif) no-repeat;
				background-position: 8px 8px;
				cursor:              pointer;
			}

			.dragme strong {
				margin-left: 30px;
			}
			</style>
			<form action="
			<?php
			echo esc_url(
				pods_query_arg(
					array(
						$this->num_prefix . 'action' . $this->num => 'reorder',
						$this->num_prefix . 'do' . $this->num     => 'save',
						'page'                => pods_v( 'page' ),
					), self::$allowed, $this->exclusion()
				)
			);
		?>
		" method="post" class="admin_ui_reorder_form">
					<?php
		}//end if
			$table_fields = $this->fields['manage'];
		if ( true === $reorder && ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] && ! empty( $this->fields['reorder'] ) ) {
			$table_fields = $this->fields['reorder'];
		}
		if ( false === $table_fields || empty( $table_fields ) ) {
			return $this->error( __( '<strong>Error:</strong> Invalid Configuration - Missing "fields" definition.', 'pods' ) );
		}
			?>
			<table class="widefat page fixed wp-list-table" cellspacing="0"<?php echo ( 1 == $reorder && $this->reorder ) ? ' id="admin_ui_reorder"' : ''; ?>>
				<thead>
				<tr>
					<?php
					if ( ! empty( $this->actions_bulk ) ) {
						?>
						<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" />
						</th>
						<?php
					}

					$name_field = false;
					$fields     = array();
					if ( ! empty( $table_fields ) ) {
						foreach ( $table_fields as $field => $attributes ) {
							if ( false === $attributes['display'] ) {
								continue;
							}
							if ( false === $name_field ) {
								$id = 'title';
							} else {
								$id = '';
							}
							if ( 'other' === $attributes['type'] ) {
								$id = '';
							}
							if ( in_array( $attributes['type'], array( 'date', 'datetime', 'time' ) ) ) {
								$id = 'date';
							}
							if ( false === $name_field && 'title' === $id ) {
								$name_field = true;
							}
							$fields[ $field ]             = $attributes;
							$fields[ $field ]['field_id'] = $id;
							$dir                          = 'DESC';
							$current_sort                 = ' asc';
							if ( isset( $this->orderby['default'] ) && $field == $this->orderby['default'] ) {
								if ( 'DESC' === $this->orderby_dir ) {
									$dir          = 'ASC';
									$current_sort = ' desc';
								}
							}

							$att_id = '';
							if ( ! empty( $id ) ) {
								$att_id = ' id="' . esc_attr( $id ) . '"';
							}

							$width = '';

							$column_classes = array(
								'manage-column',
								'column-' . $id,
							);

							// Merge with the classes taken from the UI call
							if ( ! empty( $attributes['classes'] ) && is_array( $attributes['classes'] ) ) {
								$column_classes = array_merge( $column_classes, $attributes['classes'] );
							}
							if ( $id == 'title' ) {
								$column_classes[] = 'column-primary';
							}

							if ( isset( $attributes['width'] ) && ! empty( $attributes['width'] ) ) {
								$width = ' style="width: ' . esc_attr( $attributes['width'] ) . '"';
							}

							if ( $this->is_field_sortable( $attributes ) ) {
								$column_classes[] = 'sortable' . $current_sort;
								?>
								<th scope="col"<?php echo $att_id; ?> class="<?php esc_attr_e( implode( ' ', $column_classes ) ); ?>"<?php echo $width; ?>>
									<a href="
									<?php
									echo esc_url_raw(
										pods_query_arg(
											array(
												$this->num_prefix . 'orderby' . $this->num => $field,
												$this->num_prefix . 'orderby_dir' . $this->num => $dir,
											),
											array(
												$this->num_prefix . 'limit' . $this->num,
												$this->num_prefix . 'search' . $this->num,
												$this->num_prefix . 'pg' . $this->num,
												'page',
												'post_type',
												'taxonomy',
											),
											$this->exclusion()
										)
									);
									?>
									">
										<span><?php esc_html_e( $attributes['label'] ); ?></span>
										<span class="sorting-indicator"></span> </a>
								</th>
								<?php
							} else {
								?>
								<th scope="col"<?php echo $att_id; ?> class="<?php esc_attr_e( implode( ' ', $column_classes ) ); ?>"<?php echo $width; ?>><?php esc_html_e( $attributes['label'] ); ?></th>
								<?php
							}//end if
						}//end foreach
					}//end if
					?>
				</tr>
				</thead>
				<?php
				if ( 6 < $this->total_found ) {
					?>
					<tfoot>
					<tr>
						<?php
						if ( ! empty( $this->actions_bulk ) ) {
							?>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<?php
						}

						if ( ! empty( $fields ) ) {
							foreach ( $fields as $field => $attributes ) {
								$dir = 'ASC';
								if ( $field == $this->orderby ) {
									$current_sort = 'desc';
									if ( 'ASC' === $this->orderby_dir ) {
										$dir          = 'DESC';
										$current_sort = 'asc';
									}
								}

								$width = '';

								if ( isset( $attributes['width'] ) && ! empty( $attributes['width'] ) ) {
									$width = ' style="width: ' . esc_attr( $attributes['width'] ) . '"';
								}

								if ( $this->is_field_sortable( $attributes ) ) {
									?>
									<th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?> sortable <?php echo esc_attr( $current_sort ); ?>"<?php echo $width; ?>>
										<a href="
										<?php
										echo esc_url_raw(
											pods_query_arg(
												array(
													$this->num_prefix . 'orderby' . $this->num     => $field,
													$this->num_prefix . 'orderby_dir' . $this->num => $dir,
												), array(
													$this->num_prefix . 'limit' . $this->num,
													$this->num_prefix . 'search' . $this->num,
													$this->num_prefix . 'pg' . $this->num,
													'page',
												), $this->exclusion()
											)
										);
										?>
										"><span><?php esc_html_e( $attributes['label'] ); ?></span><span class="sorting-indicator"></span></a>
									</th>
									<?php
								} else {
									?>
									<th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?>"<?php echo $width; ?>><?php esc_html_e( $attributes['label'] ); ?></th>
									<?php
								}//end if
							}//end foreach
						}//end if
						?>
					</tr>
					</tfoot>
					<?php
				}//end if
				?>
				<tbody id="the-list"<?php echo ( true === $reorder && ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] ) ? ' class="reorderable"' : ''; ?>>
				<?php
				if ( ! empty( $this->data ) && is_array( $this->data ) ) {
					$counter = 0;

					while ( $row = $this->get_row( $counter, 'table' ) ) {
						if ( is_object( $row ) ) {
							$row = get_object_vars( (object) $row );
						}

						$toggle_class = '';

						$field_id = '';

						if ( ! empty( $row[ $this->sql['field_id'] ] ) ) {
							$field_id = $row[ $this->sql['field_id'] ];
						}

						if ( is_array( $this->actions_custom ) && isset( $this->actions_custom['toggle'] ) ) {
							$toggle_class = ' pods-toggled-on';

							if ( ! isset( $row['toggle'] ) || empty( $row['toggle'] ) ) {
								$toggle_class = ' pods-toggled-off';
							}
						}
						?>
						<tr id="item-<?php echo esc_attr( $field_id ); ?>" class="iedit<?php echo esc_attr( $toggle_class ); ?>">
							<?php
							if ( ! empty( $this->actions_bulk ) ) {
								?>
								<th scope="row" class="check-column">
									<input type="checkbox" name="<?php echo esc_attr( $this->num_prefix ); ?>action_bulk_ids<?php echo esc_attr( $this->num ); ?>[]" value="<?php echo esc_attr( $field_id ); ?>">
								</th>
								<?php
							}
							// Boolean for the first field to output after the check-column
							// will be set to false at the end of the first loop
							$first_field = true;
							foreach ( $fields as $field => $attributes ) {
								if ( false === $attributes['display'] ) {
									continue;
								}

								$row[ $field ] = $this->get_field( $field );

								$row_value = $row[ $field ];

								if ( ! empty( $attributes['custom_display'] ) ) {
									if ( is_callable( $attributes['custom_display'] ) ) {
										$row_value = call_user_func_array(
											$attributes['custom_display'], array(
												$row,
												&$this,
												$row_value,
												$field,
												$attributes,
											)
										);
									}
								} else {
									$row_value_is_array = is_array( $row_value );
									$row_values = (array) $row_value;

									if (
										$row_values
										&& ! isset( $row_values[0] )
										&& in_array( $attributes['type'], $tableless_field_types, true )
									) {
										$row_values = [
											$row_values,
										];
									}

									foreach ( $row_values as $row_value_key => $row_value_item ) {
										ob_start();

										$field_value = PodsForm::field_method( $attributes['type'], 'ui', $this->id, $row_value_item, $field, $attributes, $fields, $this->pod );

										$field_output = trim( (string) ob_get_clean() );

										if ( false === $field_value ) {
											$row_values[ $row_value_key ] = '';
										} elseif ( $field_value && 0 < strlen( trim( (string) $field_value ) ) ) {
											$row_values[ $row_value_key ] = trim( (string) $field_value );
										} elseif ( $field_output && 0 < strlen( $field_output ) ) {
											$row_values[ $row_value_key ] = $field_output;
										}
									}

									$row_value = $row_values;

									if ( ! $row_value_is_array ) {
										$row_value = $row_value ? current( $row_value ) : null;
									}
								}

								if ( false !== $attributes['custom_relate'] ) {
									global $wpdb;
									$table = $attributes['custom_relate'];
									$on    = $this->sql['field_id'];
									$is    = $field_id;
									$what  = array( 'name' );
									if ( is_array( $table ) ) {
										if ( isset( $table['on'] ) ) {
											$on = pods_sanitize( $table['on'] );
										}
										if ( isset( $table['is'] ) && isset( $row[ $table['is'] ] ) ) {
											$is = pods_sanitize( $row[ $table['is'] ] );
										}
										if ( isset( $table['what'] ) ) {
											$what = array();
											if ( is_array( $table['what'] ) ) {
												foreach ( $table['what'] as $wha ) {
													$what[] = pods_sanitize( $wha );
												}
											} else {
												$what[] = pods_sanitize( $table['what'] );
											}
										}
										if ( isset( $table['table'] ) ) {
											$table = $table['table'];
										}
									}//end if
									$table = pods_sanitize( $table );
									$wha   = implode( ',', $what );
									$sql   = "SELECT {$wha} FROM {$table} WHERE `{$on}`='{$is}'";
									$value = @current( $wpdb->get_results( $sql, ARRAY_A ) );
									if ( ! empty( $value ) ) {
										$val = array();
										foreach ( $what as $wha ) {
											if ( isset( $value[ $wha ] ) ) {
												$val[] = $value[ $wha ];
											}
										}
										if ( ! empty( $val ) ) {
											$row_value = implode( ' ', $val );
										}
									}
								}//end if

								$css_classes = array(
									'pods-ui-col-field-' . sanitize_title( $field ),
								);

								// Merge with the classes taken from the UI call
								if ( ! empty( $attributes['classes'] ) && is_array( $attributes['classes'] ) ) {
									$css_classes = array_merge( $css_classes, $attributes['classes'] );
								}

								if ( $attributes['css_values'] ) {
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

													$css_classes[] = 'pods-ui-css-value-' . sanitize_title(
														str_replace(
															array(
																"\n",
																"\r",
															), ' ', strip_tags( (string) $css_field_v )
														)
													);
												}
											} else {
												$css_classes[] = ' pods-ui-css-value-' . sanitize_title(
													str_replace(
														array(
															"\n",
															"\r",
														), ' ', strip_tags( (string) $css_field_val )
													)
												);
											}//end if
										}//end foreach
									} else {
										$css_classes[] = ' pods-ui-css-value-' . sanitize_title(
											str_replace(
												array(
													"\n",
													"\r",
												), ' ', strip_tags( (string) $css_field_value )
											)
										);
									}//end if
								}//end if

								if ( is_object( $this->pod ) ) {
									$row_value = $this->do_hook( $this->pod->pod . '_field_value', $row_value, $field, $attributes, $row );
								}

								$row_value = $this->do_hook( 'field_value', $row_value, $field, $attributes, $row );

								if ( ! empty( $attributes['custom_display_formatted'] ) && is_callable( $attributes['custom_display_formatted'] ) ) {
									$row_value = call_user_func_array(
										$attributes['custom_display_formatted'], array(
											$row,
											&$this,
											$row_value,
											$field,
											$attributes,
										)
									);
								}

								if ( 'title' === $attributes['field_id'] ) {
									$default_action = $this->do_hook( 'default_action', 'edit', $row );

									if ( $first_field ) {
										$css_classes[] = 'column-primary';
									}

									if ( is_admin() ) {
										$css_classes[] = 'post-title';
										$css_classes[] = 'page-title';
									}

									$css_classes[] = 'column-title';

									if ( 'raw' !== $attributes['type'] ) {
										// Deal with unexpected array values.
										if ( is_array( $row_value ) ) {
											if ( empty( $row_value ) ) {
												$row_value = '';
											} else {
												$row_value = pods_serial_comma( $row_value, $attributes );
											}
										}

										$row_value = wp_kses_post( $row_value );
									}

									/**
									 * Allow filtering the display value used for a field in the table column.
									 *
									 * @since 2.7.29
									 *
									 * @param string $row_value  The display value for the field.
									 * @param string $field      The field name.
									 * @param array  $attributes The field attributes.
									 * @param array  $row        The other values for the row.
									 * @param PodsUI $obj        The PodsUI object.
									 */
									$row_value = apply_filters( 'pods_ui_field_display_value', $row_value, $field, $attributes, $row, $this );

									if (
										'edit' === $default_action
										&& ! in_array( 'edit', $this->actions_disabled, true )
										&& ! in_array( 'edit', $this->actions_hidden, true )
										&& (
											false === $reorder
											|| in_array( 'reorder', $this->actions_disabled, true )
											|| false === $this->reorder['on']
										)
										&& ! $this->restricted( 'edit', $row )
									) {
										$link = pods_query_arg(
											array(
												$this->num_prefix . 'action' . $this->num => 'edit',
												$this->num_prefix . 'id' . $this->num     => $field_id,
											), self::$allowed, $this->exclusion()
										);

										if ( ! empty( $this->action_links['edit'] ) ) {
											$link = $this->do_template( $this->action_links['edit'], $row );
										}
										?>
										<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>">
										<strong><a class="row-title" href="<?php echo esc_url_raw( $link ); ?>" title="<?php esc_attr_e( 'Edit this item', 'pods' ); ?>">
																						<?php
																						/* Escaped above for non-HTML types */
																						echo $row_value;
												?>
												</a></strong>
										<?php
									} elseif ( ! in_array( 'view', $this->actions_disabled ) && ! in_array( 'view', $this->actions_hidden ) && ( false === $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder['on'] ) && 'view' === $default_action ) {
										$link = pods_query_arg(
											array(
												$this->num_prefix . 'action' . $this->num => 'view',
												$this->num_prefix . 'id' . $this->num     => $field_id,
											), self::$allowed, $this->exclusion()
										);

										if ( ! empty( $this->action_links['view'] ) ) {
											$link = $this->do_template( $this->action_links['view'], $row );
										}
										?>
										<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>">
										<strong><a class="row-title" href="<?php echo esc_url_raw( $link ); ?>" title="<?php esc_attr_e( 'View this item', 'pods' ); ?>">
																						<?php
																						/* Escaped above for non-HTML types */
																						echo $row_value;
												?>
												</a></strong>
										<?php
									} else {
										if ( 1 == $reorder && $this->reorder ) {
											$css_classes[] = 'dragme';
										}
										?>
										<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>">
										<strong>
										<?php
										/* Escaped above for non-HTML types */
											echo $row_value;
											?>
											</strong>
										<?php
									}//end if

									if ( true !== $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder['on'] ) {
										$toggle = false;

										$actions = $this->get_actions( $row );
										$actions = $this->do_hook( 'row_actions', $actions, $field_id );

										if ( ! empty( $actions ) ) {
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
									} else {
										?>
										<input type="hidden" name="order[]" value="<?php echo esc_attr( $field_id ); ?>" />
										<?php
									}//end if

									if ( ! in_array( 'toggle_details', $this->actions_disabled, true ) ) {
									?>
										<button type="button" class="toggle-row">
											<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
										</button>
									<?php } ?>
									</td>
									<?php
								} elseif ( 'date' === $attributes['type'] ) {
									if ( $first_field ) {
										$css_classes[] = 'column-primary';
									}
									$css_classes[] = 'date';
									$css_classes[] = 'column-date';
									?>
									<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>" data-colname="<?php echo esc_attr( $attributes['label'] ); ?>">
										<abbr title="<?php echo esc_attr( $row_value ); ?>"><?php echo wp_kses_post( $row_value ); ?></abbr>
										<?php if ( $first_field && ! in_array( 'toggle_details', $this->actions_disabled, true ) ) { ?>
											<button type="button" class="toggle-row">
												<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
											</button>
										<?php } ?>
									</td>
									<?php
								} else {
									if ( $first_field ) {
										$css_classes[] = 'column-primary';
									}

									$css_classes[] = 'author';

									if ( 'raw' !== $attributes['type'] ) {
										// Deal with unexpected array values.
										if ( is_array( $row_value ) ) {
											if ( empty( $row_value ) ) {
												$row_value = '';
											} else {
												$row_value = pods_serial_comma( $row_value, $attributes );
											}
										}

										$row_value = wp_kses_post( $row_value );
									}
									?>
									<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>" data-colname="<?php echo esc_attr( $attributes['label'] ); ?>">
										<span>
										<?php
										/* Escaped above for non-HTML types */
											echo $row_value;
											?>
											</span>
										<?php if ( $first_field && ! in_array( 'toggle_details', $this->actions_disabled, true ) ) { ?>
											<button type="button" class="toggle-row">
												<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
											</button>
										<?php } ?>
									</td>
									<?php
								}//end if
								$first_field = false;
							}//end foreach
							?>
						</tr>
						<?php
					}//end while
				}//end if
				?>
				</tbody>
			</table>
			<?php
			if ( true === $reorder && ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] ) {

			?>
		</form>
		<?php
			}
		?>
		<script type="text/javascript">
			document.addEventListener( 'DOMContentLoaded', function( event ) {
				jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
				<?php
				if ( true === $reorder && ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] ) {
				?>
					jQuery( ".reorderable" ).sortable( {axis : "y", handle : ".dragme"} );
					jQuery( ".reorderable" ).bind( 'sortupdate', function ( event, ui ) {
						jQuery( 'table.widefat tbody tr' ).removeClass( 'alternate' );
						jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
					} );
				<?php
				}
				?>
			} );
		</script>
		<?php
	}

	/**
	 * Get actions for row.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function get_actions( $row ) {

		$field_id = '';

		if ( ! empty( $row[ $this->sql['field_id'] ] ) ) {
			$field_id = $row[ $this->sql['field_id'] ];
		}

		$actions = array();

		if ( ! in_array( 'view', $this->actions_disabled ) && ! in_array( 'view', $this->actions_hidden ) ) {
			$link = pods_query_arg(
				array(
					$this->num_prefix . 'action' . $this->num => 'view',
					$this->num_prefix . 'id' . $this->num     => $field_id,
				), self::$allowed, $this->exclusion()
			);

			if ( ! empty( $this->action_links['view'] ) ) {
				$link = $this->do_template( $this->action_links['view'], $row );
			}

			$actions['view'] = '<span class="view"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'View this item', 'pods' ) . '">' . __( 'View', 'pods' ) . '</a></span>';
		}

		if ( ! in_array( 'edit', $this->actions_disabled ) && ! in_array( 'edit', $this->actions_hidden ) && ! $this->restricted( 'edit', $row ) ) {
			$link = pods_query_arg(
				array(
					$this->num_prefix . 'action' . $this->num => 'edit',
					$this->num_prefix . 'id' . $this->num     => $field_id,
				), self::$allowed, $this->exclusion()
			);

			if ( ! empty( $this->action_links['edit'] ) ) {
				$link = $this->do_template( $this->action_links['edit'], $row );
			}

			$actions['edit'] = '<span class="edit"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'Edit this item', 'pods' ) . '">' . __( 'Edit', 'pods' ) . '</a></span>';
		}

		if ( ! in_array( 'duplicate', $this->actions_disabled ) && ! in_array( 'duplicate', $this->actions_hidden ) && ! $this->restricted( 'edit', $row ) ) {
			$link = pods_query_arg(
				array(
					$this->num_prefix . 'action' . $this->num => 'duplicate',
					$this->num_prefix . 'id' . $this->num     => $field_id,
				), self::$allowed, $this->exclusion()
			);

			if ( ! empty( $this->action_links['duplicate'] ) ) {
				$link = $this->do_template( $this->action_links['duplicate'], $row );
			}

			$actions['duplicate'] = '<span class="edit"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'Duplicate this item', 'pods' ) . '">' . __( 'Duplicate', 'pods' ) . '</a></span>';
		}

		if ( ! in_array( 'delete', $this->actions_disabled ) && ! in_array( 'delete', $this->actions_hidden ) && ! $this->restricted( 'delete', $row ) ) {
			$link = pods_query_arg(
				array(
					$this->num_prefix . 'action' . $this->num   => 'delete',
					$this->num_prefix . 'id' . $this->num       => $field_id,
					$this->num_prefix . '_wpnonce' . $this->num => wp_create_nonce( 'pods-ui-action-delete' ),
				), self::$allowed, $this->exclusion()
			);

			if ( ! empty( $this->action_links['delete'] ) ) {
				$link = add_query_arg( array( $this->num_prefix . '_wpnonce' . $this->num => wp_create_nonce( 'pods-ui-action-delete' ) ), $this->do_template( $this->action_links['delete'], $row ) );
			}

			$actions['delete'] = '<span class="delete"><a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'Delete this item', 'pods' ) . '" class="submitdelete" onclick="if(confirm(\'' . esc_attr__( 'You are about to permanently delete this item\n Choose \\\'Cancel\\\' to stop, \\\'OK\\\' to delete.', 'pods' ) . '\')){return true;}return false;">' . __( 'Delete', 'pods' ) . '</a></span>';
		}

		if ( is_array( $this->actions_custom ) ) {
			foreach ( $this->actions_custom as $custom_action => $custom_data ) {
				if ( 'add' !== $custom_action && is_array( $custom_data ) && ( isset( $custom_data['link'] ) || isset( $custom_data['callback'] ) ) && ! in_array( $custom_action, $this->actions_disabled ) && ! in_array( $custom_action, $this->actions_hidden ) ) {
					if ( ! in_array(
						$custom_action, array(
							'add',
							'view',
							'edit',
							'duplicate',
							'delete',
							'save',
							'export',
							'reorder',
							'manage',
							'table',
						)
					) ) {
						if ( 'toggle' === $custom_action ) {
							$toggle        = true;
							$toggle_labels = array(
								__( 'Enable', 'pods' ),
								__( 'Disable', 'pods' ),
							);

							$custom_data['label'] = ( $row['toggle'] ? $toggle_labels[1] : $toggle_labels[0] );
						}

						if ( ! isset( $custom_data['label'] ) ) {
							$custom_data['label'] = ucwords( str_replace( '_', ' ', $custom_action ) );
						}

						if ( ! isset( $custom_data['link'] ) ) {
							$vars = array(
								$this->num_prefix . 'action' . $this->num   => $custom_action,
								$this->num_prefix . 'id' . $this->num       => $field_id,
								$this->num_prefix . '_wpnonce' . $this->num => wp_create_nonce( 'pods-ui-action-' . $custom_action ),
							);

							if ( 'toggle' === $custom_action ) {
								$vars[ $this->num_prefix . 'toggle' . $this->num ]  = (int) ( ! $row['toggle'] );
								$vars[ $this->num_prefix . 'toggled' . $this->num ] = 1;
							}

							$custom_data['link'] = pods_query_arg( $vars, self::$allowed, $this->exclusion() );

							if ( isset( $this->action_links[ $custom_action ] ) && ! empty( $this->action_links[ $custom_action ] ) ) {
								$custom_data['link'] = add_query_arg( array( $this->num_prefix . '_wpnonce' . $this->num => wp_create_nonce( 'pods-ui-action-' . $custom_action ) ), $this->do_template( $this->action_links[ $custom_action ], $row ) );
							}
						}

						$confirm = '';

						if ( isset( $custom_data['confirm'] ) ) {
							$confirm = ' onclick="if(confirm(\'' . esc_js( $custom_data['confirm'] ) . '\')){return true;}return false;"';
						}

						if ( $this->restricted( $custom_action, $row ) ) {
							continue;
						}

						$span_class = 'edit';

						if ( isset( $custom_data['span_class'] ) ) {
							$span_class = $custom_data['span_class'];
						}

						$span_class .= ' action-' . $custom_action;

						$actions[ $custom_action ] = '<span class="' . esc_attr( $span_class ) . '"><a href="' . esc_url( $this->do_template( $custom_data['link'], $row ) ) . '" title="' . esc_attr( $custom_data['label'] ) . ' this item"' . $confirm . '>' . $custom_data['label'] . '</a></span>';
					}//end if
				}//end if
			}//end foreach
		}//end if

		return $actions;

	}

	/**
	 *
	 */
	public function screen_meta() {

		$screen_html = '';
		$help_html   = '';
		$screen_link = '';
		$help_link   = '';
		if ( ! empty( $this->screen_options ) && ! empty( $this->help ) ) {
			foreach ( $this->ui_page as $page ) {
				if ( isset( $this->screen_options[ $page ] ) ) {
					if ( is_array( $this->screen_options[ $page ] ) ) {
						if ( isset( $this->screen_options[ $page ]['link'] ) ) {
							$screen_link = $this->screen_options[ $page ]['link'];
							break;
						}
					} else {
						$screen_html = $this->screen_options[ $page ];
						break;
					}
				}
			}
			foreach ( $this->ui_page as $page ) {
				if ( isset( $this->help[ $page ] ) ) {
					if ( is_array( $this->help[ $page ] ) ) {
						if ( isset( $this->help[ $page ]['link'] ) ) {
							$help_link = $this->help[ $page ]['link'];
							break;
						}
					} else {
						$help_html = $this->help[ $page ];
						break;
					}
				}
			}
		}//end if
		$screen_html = $this->do_hook( 'screen_meta_screen_html', $screen_html );
		$screen_link = $this->do_hook( 'screen_meta_screen_link', $screen_link );
		$help_html   = $this->do_hook( 'screen_meta_help_html', $help_html );
		$help_link   = $this->do_hook( 'screen_meta_help_link', $help_link );
		if ( 0 < strlen( $screen_html ) || 0 < strlen( $screen_link ) || 0 < strlen( $help_html ) || 0 < strlen( $help_link ) ) {
			?>
			<div id="screen-meta">
				<?php
				$this->do_hook( 'screen_meta_pre' );
				if ( 0 < strlen( $screen_html ) ) {
					?>
					<div id="screen-options-wrap" class="pods-hidden">
						<form id="adv-settings" action="" method="post">
							<?php
							echo $screen_html;
							$fields = array();
							foreach ( $this->ui_page as $page ) {
								if ( isset( $this->fields[ $page ] ) && ! empty( $this->fields[ $page ] ) ) {
									$fields = $this->fields[ $page ];
								}
							}
							if ( ! empty( $fields ) || true === $this->pagination ) {
								?>
								<h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
								<?php
								if ( ! empty( $fields ) ) {
									?>
									<div class="metabox-prefs">
										<?php
										$this->do_hook( 'screen_meta_screen_options' );
										foreach ( $fields as $field => $attributes ) {
											if ( false === $attributes['display'] || true === $attributes['hidden'] ) {
												continue;
											}
											?>
											<label for="<?php echo esc_attr( $field ); ?>-hide">
												<input class="hide-column-tog" name="<?php echo esc_attr( $this->unique_identifier ); ?>_<?php echo esc_attr( $field ); ?>-hide" type="checkbox" id="<?php echo esc_attr( $field ); ?>-hide" value="<?php echo esc_attr( $field ); ?>" checked="checked"><?php esc_html_e( $attributes['label'] ); ?>
											</label>
											<?php
										}
										?>
										<br class="clear">
									</div>
									<h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
									<?php
								}//end if
								?>
								<div class="screen-options">
									<?php
									if ( true === $this->pagination ) {
										?>
										<input type="text" class="screen-per-page" name="wp_screen_options[value]" id="<?php echo esc_attr( $this->unique_identifier ); ?>_per_page" maxlength="3" value="20">
										<label for="<?php echo esc_attr( $this->unique_identifier ); ?>_per_page"><?php esc_html_e( sprintf( __( '%s per page', 'pods' ), $this->items ) ); ?></label>
										<?php
									}
									$this->do_hook( 'screen_meta_screen_submit' );
									?>
									<input type="submit" name="screen-options-apply" id="screen-options-apply" class="button" value="<?php esc_attr_e( 'Apply', 'pods' ); ?>">
									<input type="hidden" name="wp_screen_options[option]" value="<?php echo esc_attr( $this->unique_identifier ); ?>_per_page">
									<?php wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ); ?>
								</div>
								<?php
							}//end if
							?>
						</form>
					</div>
					<?php
				}//end if
				if ( 0 < strlen( $help_html ) ) {
					?>
					<div id="contextual-help-wrap" class="pods-hidden">
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
							} else {
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
							} else {
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
		}//end if
	}

	/**
	 * @param bool $header
	 *
	 * @return mixed
	 */
	public function pagination( $header = false ) {

		if ( false !== $this->callback( 'pagination', $header ) ) {
			return null;
		}

		// Check if we should show the pagination in this location.
		if ( $header && 'after' === $this->pagination_location ) {
			return null;
		} elseif ( ! $header && 'before' === $this->pagination_location ) {
			return null;
		}

		$allowed_query_args = array(
			$this->num_prefix . 'limit' . $this->num,
			$this->num_prefix . 'orderby' . $this->num,
			$this->num_prefix . 'orderby_dir' . $this->num,
			$this->num_prefix . 'search' . $this->num,
			'filter_*',
			$this->num_prefix . 'view' . $this->num,
			$this->num_prefix . 'pg' . $this->num,
			'page',
			'post_type',
			'taxonomy',
			$this->num_prefix . 'action' . $this->num,
		);

		$total_pages = ceil( $this->total_found / $this->limit );
		$request_uri = pods_query_arg(
			array( $this->num_prefix . 'pg' . $this->num => '' ), $allowed_query_args, $this->exclusion()
		);

		$append = false;

		if ( false !== strpos( $request_uri, '?' ) ) {
			$append = true;
		}

		if ( false !== $this->pagination_total && ( $header || 1 != $this->total_found ) ) {
			$singular_label = strtolower( $this->item );
			$plural_label   = strtolower( $this->items );
			?>
			<span class="displaying-num"><?php echo number_format_i18n( $this->total_found ) . ' ' . _n( $singular_label, $plural_label, $this->total_found, 'pods' ) . $this->extra['total']; ?></span>
			<?php
		}

		// Check if we need to output a different pagination type.
		if ( 'table' !== $this->pagination_type ) {
			// This pagination type requires a Pod object.
			if ( ! $this->pod instanceof Pods ) {
				return null;
			}

			$pagination_params = array(
				'type'     => $this->pagination_type,
				'page_var' => $this->num_prefix . 'pg' . $this->num,
				'format'   => "{$this->num_prefix}pg{$this->num}=%#%",
			);

			// Get global query args.
			global $pods_query_args;

			// Store current args for reference.
			$old_query_args = isset( $pods_query_args ) ? $pods_query_args : null;

			// Tell the pagination links what to include/exclude.
			$pods_query_args = [
				'allowed'  => $allowed_query_args,
				'excluded' => $this->exclusion(),
			];

			echo $this->pod->pagination( $pagination_params );

			// Reset global query args.
			$pods_query_args = $old_query_args;

			return;
		}

		if ( false !== $this->pagination ) {
			if ( 1 < $total_pages ) {
				$first_link = esc_url( $request_uri . ( $append ? '&' : '?' ) . $this->num_prefix . 'pg' . $this->num . '=1' );
				$prev_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . $this->num_prefix . 'pg' . $this->num . '=' . max( $this->page - 1, 1 ) );
				$next_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . $this->num_prefix . 'pg' . $this->num . '=' . min( $this->page + 1, $total_pages ) );
				$last_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . $this->num_prefix . 'pg' . $this->num . '=' . $total_pages );

				$classes = '';
				if ( 1 >= $this->page ) {
					$classes .= ' disabled';
				}
				if ( is_admin() ) {
					$classes .= ' button';
				}
				?>
				<a class="first-page<?php echo esc_attr( $classes ); ?>" title="<?php esc_attr_e( 'Go to the first page', 'pods' ); ?>" href="<?php echo $first_link; ?>">&laquo;</a>
				<a class="prev-page<?php echo esc_attr( $classes ); ?>" title="<?php esc_attr_e( 'Go to the previous page', 'pods' ); ?>" href="<?php echo $prev_link; ?>">&lsaquo;</a>
				<?php
				if ( true == $header ) {
					?>
					<span class="paging-input"><input class="current-page" title="<?php esc_attr_e( 'Current page', 'pods' ); ?>" type="text" name="<?php echo esc_attr( $this->num_prefix ); ?>pg<?php echo esc_attr( $this->num ); ?>" value="<?php esc_attr_e( absint( $this->page ) ); ?>" size="<?php esc_attr_e( strlen( $total_pages ) ); ?>"> <?php _e( 'of', 'pods' ); ?>
						<span class="total-pages"><?php echo absint( $total_pages ); ?></span></span>
					<script type="text/javascript">
						document.addEventListener( 'DOMContentLoaded', function( event ) {
							var pageInput = jQuery( 'input.current-page' );
							var currentPage = pageInput.val();
							pageInput.closest( 'form' ).submit( function ( e ) {
								if ( ( 1 > jQuery( 'select[name="<?php echo esc_attr( $this->num_prefix ); ?>action<?php echo esc_attr( $this->num ); ?>"]' ).length || jQuery( 'select[name="<?php echo esc_attr( $this->num_prefix ); ?>action<?php echo esc_attr( $this->num ); ?>"]' ).val() == -1 ) && ( 1 > jQuery( 'select[name="<?php echo esc_attr( $this->num_prefix ); ?>action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).length || jQuery( 'select[name="<?php echo esc_attr( $this->num_prefix ); ?>action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).val() == -1 ) && pageInput.val() == currentPage ) {
									pageInput.val( '1' );
								}
							} );
						} );
					</script>
					<?php
				} else {
					?>
					<span class="paging-input"><?php echo absint( $this->page ); ?> <?php _e( 'of', 'pods' ); ?>
						<span class="total-pages"><?php echo number_format_i18n( $total_pages ); ?></span></span>
					<?php
				}//end if
				$classes = '';
				if ( $this->page >= $total_pages ) {
					$classes .= ' disabled';
				}
				if ( is_admin() ) {
					$classes .= ' button';
				}
				?>
				<a class="next-page<?php echo esc_attr( $classes ); ?>" title="<?php esc_attr_e( 'Go to the next page', 'pods' ); ?>" href="<?php echo $next_link; ?>">&rsaquo;</a>
				<a class="last-page<?php echo esc_attr( $classes ); ?>" title="<?php esc_attr_e( 'Go to the last page', 'pods' ); ?>" href="<?php echo $last_link; ?>">&raquo</a>
				<?php
			}//end if
		}//end if
	}

	/**
	 * @param bool $options
	 *
	 * @return mixed
	 */
	public function limit( $options = false ) {

		if ( false !== $this->callback( 'limit', $options ) ) {
			return null;
		}

		if ( false === $options || ! is_array( $options ) || empty( $options ) ) {
			$options = array( 10, 25, 50, 100, 200 );
		}

		if ( ! in_array( $this->limit, $options ) && - 1 != $this->limit ) {
			$this->limit = $options[1];
		}

		foreach ( $options as $option ) {
			if ( $option == $this->limit ) {
				echo ' <span class="page-numbers current">' . esc_html( $option ) . '</span>';
			} else {
				echo ' <a href="' . esc_url(
					pods_query_arg(
						array( 'limit' => $option ), array(
							$this->num_prefix . 'orderby' . $this->num,
							$this->num_prefix . 'orderby_dir' . $this->num,
							$this->num_prefix . 'search' . $this->num,
							'filter_*',
							'page',
						), $this->exclusion()
					)
				) . '">' . esc_html( $option ) . '</a>';
			}
		}
	}

	/**
	 * @param            $code
	 * @param bool|array $row
	 *
	 * @return mixed
	 */
	public function do_template( $code, $row = false ) {

		if ( is_object( $this->pod ) && 1 == 0 && 0 < $this->pod->id() ) {
			return $this->pod->do_magic_tags( $code );
		} else {
			if ( false !== $row ) {
				$this->temp_row = $this->row;
				$this->row      = $row;
			}

			$code = preg_replace_callback( '/({@(.*?)})/m', array( $this, 'do_magic_tags' ), $code );

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
	public function do_magic_tags( $tag ) {

		if ( is_array( $tag ) ) {
			if ( ! isset( $tag[2] ) && strlen( trim( $tag[2] ) ) < 1 ) {
				return '';
			}

			$tag = $tag[2];
		}

		$tag = trim( $tag, ' {@}' );
		$tag = explode( ',', $tag );

		if ( empty( $tag ) || ! isset( $tag[0] ) || strlen( trim( $tag[0] ) ) < 1 ) {
			return null;
		}

		foreach ( $tag as $k => $v ) {
			$tag[ $k ] = trim( $v );
		}

		$field_name = $tag[0];

		$value = $this->get_field( $field_name );

		if ( isset( $tag[1] ) && ! empty( $tag[1] ) && is_callable( $tag[1] ) ) {
			$value = call_user_func_array( $tag[1], array( $value, $field_name, $this->row, &$this ) );
		}

		$before = '';
		$after  = '';

		if ( isset( $tag[2] ) && ! empty( $tag[2] ) ) {
			$before = $tag[2];
		}

		if ( isset( $tag[3] ) && ! empty( $tag[3] ) ) {
			$after = $tag[3];
		}

		if ( 0 < strlen( $value ) ) {
			return $before . $value . $after;
		}

		return null;
	}

	/**
	 * @param bool|array $exclude
	 * @param bool|array $array
	 */
	public function hidden_vars( $exclude = false, $array = false ) {

		$exclude = $this->do_hook( 'hidden_vars', $exclude, $array );
		if ( false === $exclude ) {
			$exclude = array();
		}
		if ( ! is_array( $exclude ) ) {
			$exclude = explode( ',', $exclude );
		}
		$get = $_GET;
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $val ) {
				if ( 0 < strlen( $val ) ) {
					$get[ $key ] = $val;
				} else {
					unset( $get[ $key ] );
				}
			}
		}
		foreach ( $get as $k => $v ) {
			if ( in_array( $k, $exclude, true ) ) {
				continue;
			}

			if ( is_array( $v ) ) {
				foreach ( $v as $vk => $vv ) {
					?>
					<input type="hidden" name="<?php echo esc_attr( $k ); ?>[<?php echo esc_attr( $vk ); ?>]" value="<?php echo esc_attr( $vv ); ?>" />
					<?php
				}
			} else {
				?>
				<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
				<?php
			}
		}
	}

	/**
	 * @return array
	 */
	public function exclusion() {

		$exclusion = self::$excluded;

		foreach ( $exclusion as $k => $exclude ) {
			$exclusion[ $k ] = $this->num_prefix . $exclude . $this->num;
		}

		return $exclusion;
	}

	/**
	 * @param string $action
	 * @param null   $row
	 *
	 * @return bool
	 */
	public function restricted( $action = 'edit', $row = null ) {

		$restricted = false;

		$restrict = array();

		if ( isset( $this->restrict[ $action ] ) ) {
			$restrict = (array) $this->restrict[ $action ];
		}

		// @todo Build 'edit', 'duplicate', 'delete' action support for 'where' which runs another find() query
		/*
		if ( !in_array( $action, array( 'manage', 'reorder' ) ) ) {
            $where = pods_v( $action, $this->where, null, true );

            if ( ! empty( $where ) ) {
                $restricted = true;

                $old_where = $this->where[ $action ];

                $id = $this->row[ $this->sql[ 'field_id' ] ];

                if ( is_array( $where ) ) {
                    if ( 'OR' == pods_v( 'relation', $where ) ) {
                        $where = array( $where );
					}

                    $where[] = "`t`.`" . $this->sql['field_id'] . "` = " . (int) $id;
                } else {
                    $where = "( {$where} ) AND `t`.`" . $this->sql['field_id'] . "` = " . (int) $id;
				}

                $this->where[ $action ] = $where;

                $data = false;

                //$data = $this->get_data();

                $this->where[ $action ] = $old_where;

                if ( empty( $data ) ) {
                    $restricted = true;
				}
            }
        }*/

		$author_restrict = false;

		if ( ! empty( $this->restrict['author_restrict'] ) && $restrict === $this->restrict['author_restrict'] ) {
			$restricted      = false;
			$author_restrict = true;

			if ( is_object( $this->pod ) ) {
				$restricted = true;

				if ( 'settings' === $this->pod->pod_data['type'] && 'add' === $action ) {
					$action = 'edit';
				}

				if ( pods_is_admin( array( 'pods', 'pods_content' ) ) ) {
					$restricted = false;
				} else {
					// Disable legacy check.
					$author_restrict = false;

					$pod = $this->pod;
					if ( ! $pod->id() && $row ) {
						$pod->fetch( $row );
					}

					// Check if the current user is the author of this item.
					$author    = $pod->field( 'author', true );
					$is_author = false;
					if ( $author && (int) wp_get_current_user()->ID === (int) pods_v( 'ID', $author, 0 ) ) {
						$is_author = true;
					}

					$cap_actions = array( $action );
					if ( 'manage' === $action || 'reorder' === $action ) {
						if ( ! in_array( 'edit', $this->actions_disabled, true ) ) {
							$cap_actions[] = 'edit';
						}
						if ( ! in_array( 'delete', $this->actions_disabled, true ) ) {
							$cap_actions[] = 'delete';
						}
					}

					foreach ( $cap_actions as $cap ) {
						if ( $is_author ) {
							// Only need regular capability.
							if ( current_user_can( 'pods_' . $cap . '_' . $this->pod->pod ) ) {
								$restricted = false;
								break;
							}
						} else {
							// This item is created by another user so the "others" capability is required as well.
							if (
								current_user_can( 'pods_' . $cap . '_' . $this->pod->pod ) &&
								current_user_can( 'pods_' . $cap . '_others_' . $this->pod->pod )
							) {
								$restricted = false;
								break;
							}
						}
					}
				}

			}//end if

		}//end if

		if ( $restricted && ! empty( $restrict ) ) {
			$relation = strtoupper( trim( (string) pods_v( 'relation', $restrict, 'AND', null, true ) ) );

			if ( 'AND' !== $relation ) {
				$relation = 'OR';
			}

			$okay = true;

			foreach ( $restrict as $field => $match ) {
				if ( 'relation' === $field ) {
					continue;
				}

				if ( is_array( $match ) ) {
					$match_okay = true;

					$match_relation = strtoupper( trim( (string) pods_v( 'relation', $match, 'OR', null, true ) ) );

					if ( 'AND' !== $match_relation ) {
						$match_relation = 'OR';
					}

					foreach ( $match as $the_field => $the_match ) {
						if ( 'relation' === $the_field ) {
							continue;
						}

						$value = null;

						if ( is_object( $this->pod ) ) {
							$value = $this->pod->field( $the_match, true );
						} else {
							if ( empty( $row ) ) {
								$row = $this->row;
							}

							if ( isset( $row[ $the_match ] ) ) {
								if ( is_array( $row[ $the_match ] ) ) {
									if ( false !== strpos( $the_match, '.' ) ) {
										$the_matches = explode( '.', $the_match );

										$value = $row[ $the_match ];

										foreach ( $the_matches as $m ) {
											if ( is_array( $value ) && isset( $value[ $m ] ) ) {
												$value = $value[ $m ];
											} else {
												$value = null;

												break;
											}
										}
									}
								} else {
									$value = $row[ $the_match ];
								}
							}//end if
						}//end if

						if ( is_array( $value ) ) {
							if ( ! in_array( $the_match, $value ) ) {
								$match_okay = false;
							} elseif ( 'OR' === $match_relation ) {
								$match_okay = true;

								break;
							}
						} elseif ( $value == $the_match ) {
							$match_okay = false;
						} elseif ( 'OR' === $match_relation ) {
							$match_okay = true;

							break;
						}
					}//end foreach

					if ( ! $match_okay ) {
						$okay = false;
					}
					if ( 'OR' === $relation ) {
						$okay = true;

						break;
					}
				} else {
					$value = null;

					if ( is_object( $this->pod ) ) {
						$value = $this->pod->field( $match, true );
					} else {
						if ( empty( $row ) ) {
							$row = $this->row;
						}

						if ( isset( $row[ $match ] ) ) {
							if ( is_array( $row[ $match ] ) ) {
								if ( false !== strpos( $match, '.' ) ) {
									$matches = explode( '.', $match );

									$value = $row[ $match ];

									foreach ( $matches as $m ) {
										if ( is_array( $value ) && isset( $value[ $m ] ) ) {
											$value = $value[ $m ];
										} else {
											$value = null;

											break;
										}
									}
								}
							} else {
								$value = $row[ $match ];
							}
						}//end if
					}//end if

					if ( is_array( $value ) ) {
						if ( ! in_array( $match, $value ) ) {
							$okay = false;
						} elseif ( 'OR' === $relation ) {
							$okay = true;

							break;
						}
					} elseif ( $value != $match ) {
						$okay = false;
					} elseif ( 'OR' === $relation ) {
						$okay = true;

						break;
					}
				}//end if
			}//end foreach

			// Legacy author restrict check.
			if ( $author_restrict && ! $okay && ! empty( $row ) ) {
				foreach ( $this->restrict['author_restrict'] as $key => $val ) {
					$author_restricted = $this->get_field( $key );

					if ( ! empty( $author_restricted ) ) {
						if ( ! is_array( $author_restricted ) ) {
							$author_restricted = (array) $author_restricted;
						}
						$author_restricted = array_map( 'intval', $author_restricted );

						if ( is_array( $val ) ) {
							foreach ( $val as $v ) {
								if ( in_array( (int) $v, $author_restricted, true ) ) {
									$restricted = false;
								}
							}
						} elseif ( in_array( (int) $val, $author_restricted, true ) ) {
							$restricted = false;
						}
					}
				}//end foreach
			}//end if

			if ( $okay ) {
				$restricted = false;
			}
		}//end if

		if (
			isset( $this->actions_custom[ $action ]['restrict_callback'] )
			&& is_callable( $this->actions_custom[ $action ]['restrict_callback'] )
		) {
			$restricted = call_user_func( $this->actions_custom[ $action ]['restrict_callback'], $restricted, $restrict, $action, $row, $this );
		}

		$restricted = $this->do_hook( 'restricted_' . $action, $restricted, $restrict, $action, $row );

		return $restricted;
	}

	/**
	 * Normalize and get the field data from a field.
	 *
	 * @since 2.7.28
	 *
	 * @param string|array|Field $field The field data.
	 *
	 * @return array|Field|false The field data or false if invalid / not found.
	 */
	public function get_field_data( $field, $which = 'manage' ) {
		$field_data = $field;

		if ( ! is_array( $field_data ) && ! $field_data instanceof Field ) {
			// Field is not set.
			if ( ! isset( $this->fields[ $which ][ $field ] ) ) {
				return false;
			}

			$field_data = $this->fields[ $which ][ $field ];
		} elseif ( ! isset( $field_data['name'] ) ) {
			// Field name is required.
			return false;
		}

		return $field_data;
	}

	/**
	 * Determine whether a field is searchable.
	 *
	 * @since 2.7.28
	 *
	 * @param string|array|Field $field The field data.
	 *
	 * @return bool Whether a field is searchable.
	 */
	public function is_field_searchable( $field ) {
		$field_data = $this->get_field_data( $field );

		// Field not valid.
		if ( ! $field_data ) {
			return false;
		}

		$field = $field_data['name'];

		// Provided as a search field.
		if ( isset( $this->fields['search'][ $field ] ) ) {
			return true;
		}

		// Search is turned on individually and we don't have a list of search-only fields.
		return ! empty( $field_data['search'] ) && empty( $this->fields['search'] );
	}

	/**
	 * Determine whether a field is sortable.
	 *
	 * @since 2.7.28
	 *
	 * @param string|array|Field $field The field data.
	 *
	 * @return bool Whether a field is sortable.
	 */
	public function is_field_sortable( $field ) {
		$field_data = $this->get_field_data( $field );

		// Field not valid.
		if ( ! $field_data ) {
			return false;
		}

		$field = $field_data['name'];

		// Provided as a sort field.
		if ( isset( $this->fields['sort'][ $field ] ) ) {
			return true;
		}

		// Sort is turned on individually and we don't have a list of sort-only fields.
		return ! empty( $field_data['sortable'] ) && empty( $this->fields['sort'] );
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
			if ( is_array( $this->actions_custom[ $action ] ) && isset( $this->actions_custom[ $action ]['callback'] ) && is_callable( $this->actions_custom[ $action ]['callback'] ) ) {
				$callback = call_user_func_array( $this->actions_custom[ $action ]['callback'], $args );
			} elseif ( is_callable( $this->actions_custom[ $action ] ) ) {
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
			if ( is_array( $this->actions_custom[ $action ] ) && isset( $this->actions_custom[ $action ]['callback'] ) && is_callable( $this->actions_custom[ $action ]['callback'] ) ) {
				$callback = call_user_func_array( $this->actions_custom[ $action ]['callback'], $args );
			} elseif ( is_callable( $this->actions_custom[ $action ] ) ) {
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
			if ( is_array( $this->actions_bulk[ $action ] ) && isset( $this->actions_bulk[ $action ]['callback'] ) && is_callable( $this->actions_bulk[ $action ]['callback'] ) ) {
				$callback = call_user_func_array( $this->actions_bulk[ $action ]['callback'], $args );
			} elseif ( is_callable( $this->actions_bulk[ $action ] ) ) {
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

		return pods_do_hook( 'ui', $name, $args, $this );

	}
}
