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
	 * @var string
	 */
	public $num = '';
	// allows multiple co-existing PodsUI instances with separate functionality in URL
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
		'form'      => array(),
		'add'       => array(),
		'edit'      => array(),
		'duplicate' => array(),
		'view'      => array(),
		'reorder'   => array(),
		'export'    => array(),
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
	 * @var bool
	 */
	public $save = false;
	// Allow custom save handling for tables that aren't Pod-based
	/**
	 * Generate UI for Data Management
	 *
	 * @param mixed $options    Object, Array, or String containing Pod or Options to be used
	 * @param bool  $deprecated Set to true to support old options array from Pods UI plugin
	 *
	 * @return \PodsUI
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct( $options, $deprecated = false ) {

		$this->_nonce = pods_v( '_wpnonce', 'request' );

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
				$this->pod = pods( $options['pod'], $options['id'] );
			} else {
				$this->pod = pods( $options['pod'] );
			}

			unset( $options['pod'] );
		} elseif ( is_object( $object ) ) {
			$this->pod = $object;
		}

		if ( false !== $deprecated || ( is_object( $this->pod ) && 'Pod' == get_class( $this->pod ) ) ) {
			$options = $this->setup_deprecated( $options );
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

			return false;
		}

		// Assign pod labels
		// @todo This is also done in setup(), maybe a better / more central way?
		if ( is_object( $this->pod ) && ! empty( $this->pod->pod_data['options'] ) ) {
			$pod_options = $this->pod->pod_data['options'];
			$pod_name    = $this->pod->pod_data['name'];
			$pod_options = apply_filters( "pods_advanced_content_type_pod_data_{$pod_name}", $pod_options, $this->pod->pod_data['name'] );
			$pod_options = apply_filters( 'pods_advanced_content_type_pod_data', $pod_options, $this->pod->pod_data['name'] );

			$this->label = array_merge( $this->label, $pod_options );
		}

		$this->go();
	}

	/**
	 * @param $deprecated_options
	 *
	 * @return array
	 */
	public function setup_deprecated( $deprecated_options ) {

		$options = array();

		if ( isset( $deprecated_options['id'] ) ) {
			$options['id'] = $deprecated_options['id'];
		}
		if ( isset( $deprecated_options['action'] ) ) {
			$options['action'] = $deprecated_options['action'];
		}
		if ( isset( $deprecated_options['num'] ) ) {
			$options['num'] = $deprecated_options['num'];
		}

		if ( isset( $deprecated_options['title'] ) ) {
			$options['items'] = $deprecated_options['title'];
		}
		if ( isset( $deprecated_options['item'] ) ) {
			$options['item'] = $deprecated_options['item'];
		}

		if ( isset( $deprecated_options['label'] ) ) {
			$options['label'] = array(
				'add'       => $deprecated_options['label'],
				'edit'      => $deprecated_options['label'],
				'duplicate' => $deprecated_options['label'],
			);
		}
		if ( isset( $deprecated_options['label_add'] ) ) {
			if ( isset( $options['label'] ) ) {
				$options['label']['add'] = $deprecated_options['label_add'];
			} else {
				$options['label'] = array( 'add' => $deprecated_options['label_add'] );
			}
		}
		if ( isset( $deprecated_options['label_edit'] ) ) {
			if ( isset( $options['label'] ) ) {
				$options['label']['edit'] = $deprecated_options['label_edit'];
			} else {
				$options['label'] = array( 'edit' => $deprecated_options['label_edit'] );
			}
		}
		if ( isset( $deprecated_options['label_duplicate'] ) ) {
			if ( isset( $options['label'] ) ) {
				$options['label']['duplicate'] = $deprecated_options['label_duplicate'];
			} else {
				$options['label'] = array( 'duplicate' => $deprecated_options['label_duplicate'] );
			}
		}

		if ( isset( $deprecated_options['icon'] ) ) {
			$options['icon'] = $deprecated_options['icon'];
		}

		if ( isset( $deprecated_options['columns'] ) ) {
			$options['fields'] = array( 'manage' => $deprecated_options['columns'] );
		}
		if ( isset( $deprecated_options['reorder_columns'] ) ) {
			if ( isset( $options['fields'] ) ) {
				$options['fields']['reorder'] = $deprecated_options['reorder_columns'];
			} else {
				$options['fields'] = array( 'reorder' => $deprecated_options['reorder_columns'] );
			}
		}
		if ( isset( $deprecated_options['add_fields'] ) ) {
			if ( isset( $options['fields'] ) ) {
				if ( ! isset( $options['fields']['add'] ) ) {
					$options['fields']['add'] = $deprecated_options['add_fields'];
				}
				if ( ! isset( $options['fields']['edit'] ) ) {
					$options['fields']['edit'] = $deprecated_options['add_fields'];
				}
				if ( ! isset( $options['fields']['duplicate'] ) ) {
					$options['fields']['duplicate'] = $deprecated_options['add_fields'];
				}
			} else {
				$options['fields'] = array(
					'add'       => $deprecated_options['add_fields'],
					'edit'      => $deprecated_options['add_fields'],
					'duplicate' => $deprecated_options['add_fields'],
				);
			}
		}
		if ( isset( $deprecated_options['edit_fields'] ) ) {
			if ( isset( $options['fields'] ) ) {
				if ( ! isset( $options['fields']['add'] ) ) {
					$options['fields']['add'] = $deprecated_options['edit_fields'];
				}
				if ( ! isset( $options['fields']['edit'] ) ) {
					$options['fields']['edit'] = $deprecated_options['edit_fields'];
				}
				if ( ! isset( $options['fields']['duplicate'] ) ) {
					$options['fields']['duplicate'] = $deprecated_options['edit_fields'];
				}
			} else {
				$options['fields'] = array(
					'add'       => $deprecated_options['edit_fields'],
					'edit'      => $deprecated_options['edit_fields'],
					'duplicate' => $deprecated_options['edit_fields'],
				);
			}
		}
		if ( isset( $deprecated_options['duplicate_fields'] ) ) {
			if ( isset( $options['fields'] ) ) {
				$options['fields']['duplicate'] = $deprecated_options['duplicate_fields'];
			} else {
				$options['fields'] = array( 'duplicate' => $deprecated_options['duplicate_fields'] );
			}
		}

		if ( isset( $deprecated_options['session_filters'] ) && false === $deprecated_options['session_filters'] ) {
			$options['session'] = false;
		}
		if ( isset( $deprecated_options['user_per_page'] ) ) {
			if ( isset( $options['user'] ) && ! empty( $options['user'] ) ) {
				$options['user'] = array( 'orderby' );
			} else {
				$options['user'] = false;
			}
		}
		if ( isset( $deprecated_options['user_sort'] ) ) {
			if ( isset( $options['user'] ) && ! empty( $options['user'] ) ) {
				$options['user'] = array( 'show_per_page' );
			} else {
				$options['user'] = false;
			}
		}

		if ( isset( $deprecated_options['custom_list'] ) ) {
			if ( isset( $options['actions_custom'] ) ) {
				$options['actions_custom']['manage'] = $deprecated_options['custom_list'];
			} else {
				$options['actions_custom'] = array( 'manage' => $deprecated_options['custom_list'] );
			}
		}
		if ( isset( $deprecated_options['custom_reorder'] ) ) {
			if ( isset( $options['actions_custom'] ) ) {
				$options['actions_custom']['reorder'] = $deprecated_options['custom_reorder'];
			} else {
				$options['actions_custom'] = array( 'reorder' => $deprecated_options['custom_reorder'] );
			}
		}
		if ( isset( $deprecated_options['custom_add'] ) ) {
			if ( isset( $options['actions_custom'] ) ) {
				$options['actions_custom']['add'] = $deprecated_options['custom_add'];
			} else {
				$options['actions_custom'] = array( 'add' => $deprecated_options['custom_add'] );
			}
		}
		if ( isset( $deprecated_options['custom_edit'] ) ) {
			if ( isset( $options['actions_custom'] ) ) {
				$options['actions_custom']['edit'] = $deprecated_options['custom_edit'];
			} else {
				$options['actions_custom'] = array( 'edit' => $deprecated_options['custom_edit'] );
			}
		}
		if ( isset( $deprecated_options['custom_duplicate'] ) ) {
			if ( isset( $options['actions_custom'] ) ) {
				$options['actions_custom']['duplicate'] = $deprecated_options['custom_duplicate'];
			} else {
				$options['actions_custom'] = array( 'duplicate' => $deprecated_options['custom_duplicate'] );
			}
		}
		if ( isset( $deprecated_options['custom_delete'] ) ) {
			if ( isset( $options['actions_custom'] ) ) {
				$options['actions_custom']['delete'] = $deprecated_options['custom_delete'];
			} else {
				$options['actions_custom'] = array( 'delete' => $deprecated_options['custom_delete'] );
			}
		}
		if ( isset( $deprecated_options['custom_save'] ) ) {
			if ( isset( $options['actions_custom'] ) ) {
				$options['actions_custom']['save'] = $deprecated_options['custom_save'];
			} else {
				$options['actions_custom'] = array( 'save' => $deprecated_options['custom_save'] );
			}
		}

		if ( isset( $deprecated_options['custom_actions'] ) ) {
			$options['actions_custom'] = $deprecated_options['custom_actions'];
		}
		if ( isset( $deprecated_options['action_after_save'] ) ) {
			$options['action_after'] = array(
				'add'       => $deprecated_options['action_after_save'],
				'edit'      => $deprecated_options['action_after_save'],
				'duplicate' => $deprecated_options['action_after_save'],
			);
		}
		if ( isset( $deprecated_options['edit_link'] ) ) {
			if ( isset( $options['action_links'] ) ) {
				$options['action_links']['edit'] = $deprecated_options['edit_link'];
			} else {
				$options['action_links'] = array( 'edit' => $deprecated_options['edit_link'] );
			}
		}
		if ( isset( $deprecated_options['view_link'] ) ) {
			if ( isset( $options['action_links'] ) ) {
				$options['action_links']['view'] = $deprecated_options['view_link'];
			} else {
				$options['action_links'] = array( 'view' => $deprecated_options['view_link'] );
			}
		}
		if ( isset( $deprecated_options['duplicate_link'] ) ) {
			if ( isset( $options['action_links'] ) ) {
				$options['action_links']['duplicate'] = $deprecated_options['duplicate_link'];
			} else {
				$options['action_links'] = array( 'duplicate' => $deprecated_options['duplicate_link'] );
			}
		}

		if ( isset( $deprecated_options['reorder'] ) ) {
			$options['reorder'] = array(
				'on'      => $deprecated_options['reorder'],
				'orderby' => $deprecated_options['reorder'],
			);
		}
		if ( isset( $deprecated_options['reorder_sort'] ) && isset( $options['reorder'] ) ) {
			$options['reorder']['orderby'] = $deprecated_options['reorder_sort'];
		}
		if ( isset( $deprecated_options['reorder_limit'] ) && isset( $options['reorder'] ) ) {
			$options['reorder']['limit'] = $deprecated_options['reorder_limit'];
		}
		if ( isset( $deprecated_options['reorder_sql'] ) && isset( $options['reorder'] ) ) {
			$options['reorder']['sql'] = $deprecated_options['reorder_sql'];
		}

		if ( isset( $deprecated_options['sort'] ) ) {
			$options['orderby'] = $deprecated_options['sort'];
		}
		if ( isset( $deprecated_options['sortable'] ) ) {
			$options['sortable'] = $deprecated_options['sortable'];
		}
		if ( isset( $deprecated_options['limit'] ) ) {
			$options['limit'] = $deprecated_options['limit'];
		}

		if ( isset( $deprecated_options['where'] ) ) {
			if ( isset( $options['where'] ) ) {
				$options['where']['manage'] = $deprecated_options['where'];
			} else {
				$options['where'] = array( 'manage' => $deprecated_options['where'] );
			}
		}
		if ( isset( $deprecated_options['edit_where'] ) ) {
			/*
			if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'edit' ] = $deprecated_options[ 'edit_where' ];
            else
                $options[ 'where' ] = array( 'edit' => $deprecated_options[ 'edit_where' ] );*/

			if ( isset( $options['restrict'] ) ) {
				$options['restrict']['edit'] = (array) $deprecated_options['edit_where'];
			} else {
				$options['restrict'] = array( 'edit' => (array) $deprecated_options['edit_where'] );
			}
		}
		if ( isset( $deprecated_options['duplicate_where'] ) ) {
			/*
			if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'duplicate' ] = $deprecated_options[ 'duplicate_where' ];
            else
                $options[ 'where' ] = array( 'duplicate' => $deprecated_options[ 'duplicate_where' ] );*/

			if ( isset( $options['restrict'] ) ) {
				$options['restrict']['duplicate'] = (array) $deprecated_options['duplicate_where'];
			} else {
				$options['restrict'] = array( 'duplicate' => (array) $deprecated_options['duplicate_where'] );
			}
		}
		if ( isset( $deprecated_options['delete_where'] ) ) {
			/*
			if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'delete' ] = $deprecated_options[ 'delete_where' ];
            else
                $options[ 'where' ] = array( 'delete' => $deprecated_options[ 'delete_where' ] );*/

			if ( isset( $options['restrict'] ) ) {
				$options['restrict']['delete'] = (array) $deprecated_options['delete_where'];
			} else {
				$options['restrict'] = array( 'delete' => (array) $deprecated_options['delete_where'] );
			}
		}
		if ( isset( $deprecated_options['reorder_where'] ) ) {
			if ( isset( $options['where'] ) ) {
				$options['where']['reorder'] = $deprecated_options['reorder_where'];
			} else {
				$options['where'] = array( 'reorder' => $deprecated_options['reorder_where'] );
			}
		}

		if ( isset( $deprecated_options['sql'] ) ) {
			$options['sql'] = array( 'sql' => $deprecated_options['sql'] );
		}

		if ( isset( $deprecated_options['search'] ) ) {
			$options['searchable'] = $deprecated_options['search'];
		}
		if ( isset( $deprecated_options['search_across'] ) ) {
			$options['search_across'] = $deprecated_options['search_across'];
		}
		if ( isset( $deprecated_options['search_across_picks'] ) ) {
			$options['search_across_picks'] = $deprecated_options['search_across_picks'];
		}
		if ( isset( $deprecated_options['filters'] ) ) {
			$options['filters'] = $deprecated_options['filters'];
		}
		if ( isset( $deprecated_options['custom_filters'] ) ) {
			if ( is_callable( $deprecated_options['custom_filters'] ) ) {
				add_filter( 'pods_ui_filters', $deprecated_options['custom_filters'] );
			} else {
				global $pods_ui_custom_filters;
				$pods_ui_custom_filters = $deprecated_options['custom_filters'];
				add_filter( 'pods_ui_filters', array( $this, 'deprecated_filters' ) );
			}
		}

		if ( isset( $deprecated_options['disable_actions'] ) ) {
			$options['actions_disabled'] = $deprecated_options['disable_actions'];
		}
		if ( isset( $deprecated_options['hide_actions'] ) ) {
			$options['actions_hidden'] = $deprecated_options['hide_actions'];
		}

		if ( isset( $deprecated_options['wpcss'] ) ) {
			$options['wpcss'] = $deprecated_options['wpcss'];
		}

		$remaining_options = array_diff_assoc( $options, $deprecated_options );

		foreach ( $remaining_options as $option => $value ) {
			if ( isset( $deprecated_options[ $option ] ) && isset( $this->$option ) ) {
				$options[ $option ] = $value;
			}
		}

		return $options;
	}

	/**
	 *
	 */
	public function deprecated_filters() {

		global $pods_ui_custom_filters;
		echo $pods_ui_custom_filters;
	}

	/**
	 * @param $options
	 *
	 * @return array|bool|mixed|null|PodsArray
	 */
	public function setup( $options ) {

		$options = pods_array( $options );

		$options->validate( 'num', '', 'absint' );

		if ( empty( $options->num ) ) {
			$options->num = '';
		}

		$options->validate( 'id', pods_var( 'id' . $options->num, 'get', $this->id ) );

		$options->validate(
			'do', pods_var( 'do' . $options->num, 'get', $this->do ), 'in_array', array(
				'save',
				'create',
			)
		);

		$options->validate( 'excluded', self::$excluded, 'array_merge' );

		$options->validate( 'action', pods_var( 'action' . $options->num, 'get', $this->action, null, true ), 'in_array', $this->actions );
		$options->validate( 'actions_bulk', $this->actions_bulk, 'array_merge' );
		$options->validate( 'action_bulk', pods_var( 'action_bulk' . $options->num, 'get', $this->action_bulk, null, true ), 'isset', $this->actions_bulk );

		$bulk = pods_var( 'action_bulk_ids' . $options->num, 'get', array(), null, true );

		if ( ! empty( $bulk ) ) {
			$bulk = (array) pods_var( 'action_bulk_ids' . $options->num, 'get', array(), null, true );
		} else {
			$bulk = array();
		}

		$options->validate( 'bulk', $bulk, 'array_merge', $this->bulk );

		$options->validate( 'views', $this->views, 'array' );
		$options->validate( 'view', pods_var( 'view' . $options->num, 'get', $this->view, null, true ), 'isset', $this->views );

		$options->validate( 'searchable', $this->searchable, 'boolean' );
		$options->validate( 'search', pods_var( 'search' . $options->num ) );
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
				'table'       => $this->pods_data->table,
				'field_id'    => $this->pods_data->field_id,
				'field_index' => $this->pods_data->field_index,
			);
		}
		$options->validate( 'sql', $this->sql, 'array_merge' );

		$options->validate(
			'orderby_dir', strtoupper( pods_v( 'orderby_dir' . $options['num'], 'get', $this->orderby_dir, true ) ), 'in_array', array(
				'ASC',
				'DESC',
			)
		);

		$orderby = $this->orderby;

		// Enforce strict DB column name usage
		if ( ! empty( $_GET[ 'orderby' . $options->num ] ) ) {
			$orderby = pods_clean_name( $_GET[ 'orderby' . $options->num ], true, false );
		}

		if ( ! empty( $orderby ) ) {
			$orderby = array(
				'default' => $orderby,
			);
		} else {
			$orderby = array();
		}

		$options->validate( 'orderby', $orderby, 'array_merge' );
		$options->validate( 'sortable', $this->sortable, 'boolean' );

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

			$item  = pods_v( 'label_singular', $pod_data['options'], pods_v( 'label', $pod_data, $item, true ), true );
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

		$options->validate(
			'fields', array(
				'manage' => array(
					$options->sql['field_index'] => array( 'label' => __( 'Name', 'pods' ) ),
				),
			), 'array'
		);

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

			$color = get_user_meta( $user_ID, 'admin_color', true );
			if ( strlen( $color ) < 1 ) {
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

		$unique_identifier = pods_var( 'page' );
		// wp-admin page
		if ( is_object( $this->pod ) && isset( $this->pod->pod ) ) {
			$unique_identifier = '_' . $this->pod->pod;
		} elseif ( 0 < strlen( $this->sql['table'] ) ) {
			$unique_identifier = '_' . $this->sql['table'];
		}

		$unique_identifier .= '_' . $this->page;
		if ( 0 < strlen( $this->num ) ) {
			$unique_identifier .= '_' . $this->num;
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
					} else {
						$attributes = array( 'label' => $attributes );
					}
				}

				if ( ! isset( $attributes['real_name'] ) ) {
					$attributes['real_name'] = pods_var( 'name', $attributes, $field );
				}

				if ( is_object( $this->pod ) && isset( $this->pod->fields ) && isset( $this->pod->fields[ $attributes['real_name'] ] ) ) {
					$attributes = array_merge( $this->pod->fields[ $attributes['real_name'] ], $attributes );
				}

				if ( ! isset( $attributes['options'] ) ) {
					$attributes['options'] = array();
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
				if ( ! isset( $attributes['options']['date_format_type'] ) ) {
					$attributes['options']['date_format_type'] = 'date';
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
				if ( ! isset( $attributes['options']['search'] ) || false === $this->searchable ) {
					$attributes['options']['search'] = $this->searchable;
				}
				if ( ! isset( $attributes['options']['filter'] ) || false === $this->searchable ) {
					$attributes['options']['filter'] = $this->searchable;
				}
				/*
				if ( false !== $attributes[ 'options' ][ 'filter' ] && false !== $filterable )
                    $this->filters[] = $field;*/
				if ( false === $attributes['options']['filter'] || ! isset( $attributes['filter_label'] ) || ! in_array( $field, $this->filters ) ) {
					$attributes['filter_label'] = $attributes['label'];
				}
				if ( false === $attributes['options']['filter'] || ! isset( $attributes['filter_default'] ) || ! in_array( $field, $this->filters ) ) {
					$attributes['filter_default'] = false;
				}
				if ( false === $attributes['options']['filter'] || ! isset( $attributes['date_ongoing'] ) || 'date' !== $attributes['type'] || ! in_array( $field, $this->filters ) ) {
					$attributes['date_ongoing'] = false;
				}
				if ( false === $attributes['options']['filter'] || ! isset( $attributes['date_ongoing'] ) || 'date' !== $attributes['type'] || ! isset( $attributes['date_ongoing_default'] ) || ! in_array( $field, $this->filters ) ) {
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
				if ( 'search_columns' === $which && ! $attributes['options']['search'] ) {
					continue;
				}

				$attributes = PodsForm::field_setup( $attributes, null, $attributes['type'] );

				$new_fields[ $field ] = $attributes;
			}//end foreach
			$fields = $new_fields;
		}//end if
		if ( false !== $init ) {
			if ( 'fields' !== $which && ! empty( $this->fields ) ) {
				$this->fields = $this->setup_fields( $this->fields, 'fields' );
			} else {
				$this->fields['manage'] = $fields;
			}

			if ( ! in_array( 'add', $this->actions_disabled ) || ! in_array( 'edit', $this->actions_disabled ) || ! in_array( 'duplicate', $this->actions_disabled ) ) {
				if ( 'form' !== $which && isset( $this->fields['form'] ) && is_array( $this->fields['form'] ) ) {
					$this->fields['form'] = $this->setup_fields( $this->fields['form'], 'form' );
				} else {
					$this->fields['form'] = $fields;
				}

				if ( ! in_array( 'add', $this->actions_disabled ) ) {
					if ( 'add' !== $which && isset( $this->fields['add'] ) && is_array( $this->fields['add'] ) ) {
						$this->fields['add'] = $this->setup_fields( $this->fields['add'], 'add' );
					}
				}
				if ( ! in_array( 'edit', $this->actions_disabled ) ) {
					if ( 'edit' !== $which && isset( $this->fields['edit'] ) && is_array( $this->fields['edit'] ) ) {
						$this->fields['edit'] = $this->setup_fields( $this->fields['edit'], 'edit' );
					}
				}
				if ( ! in_array( 'duplicate', $this->actions_disabled ) ) {
					if ( 'duplicate' !== $which && isset( $this->fields['duplicate'] ) && is_array( $this->fields['duplicate'] ) ) {
						$this->fields['duplicate'] = $this->setup_fields( $this->fields['duplicate'], 'duplicate' );
					}
				}
			}//end if

			if ( false !== $this->searchable ) {
				if ( 'search' !== $which && isset( $this->fields['search'] ) && ! empty( $this->fields['search'] ) ) {
					$this->fields['search'] = $this->setup_fields( $this->fields['search'], 'search' );
				} else {
					$this->fields['search'] = $fields;
				}
			} else {
				$this->fields['search'] = false;
			}

			if ( ! in_array( 'export', $this->actions_disabled ) ) {
				if ( 'export' !== $which && isset( $this->fields['export'] ) && ! empty( $this->fields['export'] ) ) {
					$this->fields['export'] = $this->setup_fields( $this->fields['export'], 'export' );
				}
			}

			if ( ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] ) {
				if ( 'reorder' !== $which && isset( $this->fields['reorder'] ) && ! empty( $this->fields['reorder'] ) ) {
					$this->fields['reorder'] = $this->setup_fields( $this->fields['reorder'], 'reorder' );
				} else {
					$this->fields['reorder'] = $fields;
				}
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
				$more_args = false;

				if ( is_array( $this->actions_custom[ $this->action ] ) && isset( $this->actions_custom[ $this->action ]['more_args'] ) ) {
					$more_args = $this->actions_custom[ $this->action ]['more_args'];
				}

				$row = $this->row;

				if ( empty( $row ) ) {
					$row = $this->get_row();
				}

				if ( $this->restricted( $this->action, $row ) || ( $more_args && ! empty( $more_args['nonce'] ) && false === wp_verify_nonce( $this->_nonce, 'pods-ui-action-' . $this->action ) ) ) {
					return $this->error( sprintf( __( '<strong>Error:</strong> You do not have access to this %s.', 'pods' ), $this->item ) );
				} elseif ( $more_args && false !== $this->callback_action( true, $this->action, $this->id, $row ) ) {
					return null;
				} elseif ( false !== $this->callback_action( true, $this->action, $this->id ) ) {
					return null;
				}
			}//end if

			if ( ! in_array( 'manage', $this->actions_disabled ) ) {
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

						pods_v_set( $value, $setting, $method );
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
							'action' . $this->num => 'manage',
							'id' . $this->num     => '',
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
	 * @return mixed
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
		} elseif ( false !== $this->callback_action( 'edit', $duplicate ) ) {
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
							'action' . $this->num => 'add',
							'id' . $this->num     => '',
							'do' . $this->num     => '',
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
							'action' . $this->num => 'manage',
							'id' . $this->num     => '',
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
			'action' . $this->num => $this->action_after['add'],
			'do' . $this->num     => 'create',
			'id' . $this->num     => 'X_ID_X',
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
			$id    = $this->row[ $this->sql['field_id'] ];
			$vars  = array(
				'action' . $this->num => $this->action_after['edit'],
				'do' . $this->num     => 'save',
				'id' . $this->num     => $id,
			);

			$alt_vars           = $vars;
			$alt_vars['action'] = 'manage';
			unset( $alt_vars['id'] );

			if ( $duplicate ) {
				$label = $this->do_template( $this->label['duplicate'] );
				$id    = null;
				$vars  = array(
					'action' . $this->num => $this->action_after['duplicate'],
					'do' . $this->num     => 'create',
					'id' . $this->num     => 'X_ID_X',
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
			$object_fields = (array) pods_var_raw( 'object_fields', $this->pod->pod_data, array(), null, true );

			if ( empty( $object_fields ) && in_array(
				$this->pod->pod_data['type'], array(
					'post_type',
					'taxonomy',
					'media',
					'user',
					'comment',
				)
			) ) {
				$object_fields = $this->pod->api->get_wp_object_fields( $this->pod->pod_data['type'], $this->pod->pod_data );
			}

			if ( empty( $fields ) ) {
				// Add core object fields if $fields is empty
				$fields = array_merge( $object_fields, $this->pod->fields );
			}
		}

		$form_fields = $fields;
		// Temporary
		$fields = array();

		foreach ( $form_fields as $k => $field ) {
			$name = $k;

			$defaults = array(
				'name' => $name,
			);

			if ( ! is_array( $field ) ) {
				$name = $field;

				$field = array(
					'name' => $name,
				);
			}

			$field = array_merge( $defaults, $field );

			$field['name'] = trim( $field['name'] );

			$default_value = pods_var_raw( 'default', $field );
			$value         = pods_var_raw( 'value', $field );

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			if ( isset( $object_fields[ $field['name'] ] ) ) {
				$field = array_merge( $field, $object_fields[ $field['name'] ] );
			} elseif ( isset( $this->pod->fields[ $field['name'] ] ) ) {
				$field = array_merge( $this->pod->fields[ $field['name'] ], $field );
			}

			if ( pods_var_raw( 'hidden', $field, false, null, true ) ) {
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

		if ( is_object( $this->pod ) && 'settings' === $this->pod->pod_data['type'] && 'settings' === $this->style ) {
			pods_view( PODS_DIR . 'ui/admin/form-settings.php', compact( array_keys( get_defined_vars() ) ) );
		} else {
			pods_view( PODS_DIR . 'ui/admin/form.php', compact( array_keys( get_defined_vars() ) ) );
		}
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
			$object_fields = (array) pods_var_raw( 'object_fields', $this->pod->pod_data, array(), null, true );

			$object_field_objects = array(
				'post_type',
				'taxonomy',
				'media',
				'user',
				'comment',
			);

			if ( empty( $object_fields ) && in_array( $this->pod->pod_data['type'], $object_field_objects ) ) {
				$object_fields = $this->pod->api->get_wp_object_fields( $this->pod->pod_data['type'], $this->pod->pod_data );
			}

			if ( empty( $fields ) ) {
				// Add core object fields if $fields is empty
				$fields = array_merge( $object_fields, $this->pod->fields );
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

			if ( ! is_array( $field ) ) {
				$name = $field;

				$field = array(
					'name' => $name,
				);
			}

			$field = array_merge( $defaults, $field );

			$field['name'] = trim( $field['name'] );

			$value = pods_var_raw( 'default', $field );

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			if ( isset( $object_fields[ $field['name'] ] ) ) {
				$field = array_merge( $field, $object_fields[ $field['name'] ] );
			} elseif ( isset( $this->pod->fields[ $field['name'] ] ) ) {
				$field = array_merge( $this->pod->fields[ $field['name'] ], $field );
			}

			if ( pods_v( 'hidden', $field, false, null, true ) || 'hidden' === $field['type'] ) {
				continue;
			} elseif ( ! PodsForm::permission( $field['type'], $field['name'], $field['options'], $fields, $pod, $pod->id() ) ) {
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
							'action' . $this->num => 'add',
							'id' . $this->num     => '',
							'do' . $this->num     => '',
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
							'action' . $this->num => 'manage',
							'id' . $this->num     => '',
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
		$order = (array) pods_var_raw( 'order', 'post', array(), null, true );

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
				$selected = ( 1 == pods_var( $field, 'post', 0 ) ) ? 1 : 0;
			} elseif ( '' == pods_var( $field, 'post', '' ) ) {
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
					$value = date_i18n( $format, strtotime( ( 'time' === $attributes['type'] ) ? date_i18n( 'Y-m-d ' ) : pods_var( $field, 'post', '' ) ) );
				}
			} else {
				if ( 'bool' === $attributes['type'] ) {
					$vartype = '%d';
					$value   = 0;
					if ( '' != pods_var( $field, 'post', '' ) ) {
						$value = 1;
					}
				} elseif ( 'number' === $attributes['type'] ) {
					$vartype = '%d';
					$value   = number_format( pods_var( $field, 'post', 0 ), 0, '', '' );
				} elseif ( 'decimal' === $attributes['type'] ) {
					$vartype = '%d';
					$value   = number_format( pods_var( $field, 'post', 0 ), 2, '.', '' );
				} elseif ( 'related' === $attributes['type'] ) {
					if ( is_array( pods_var( $field, 'post', '' ) ) ) {
						$value = implode( ',', pods_var( $field, 'post', '' ) );
					} else {
						$value = pods_var( $field, 'post', '' );
					}
				} else {
					$value = pods_var( $field, 'post', '' );
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
		$field_sql = implode( ',', $field_sql );
		if ( false === $insert && 0 < $this->id ) {
			$this->insert_id = $this->id;
			$values[]        = $this->id;
			$check           = $wpdb->query( $wpdb->prepare( "UPDATE $this->sql['table'] SET $field_sql WHERE id=%d", $values ) );
		} else {
			$check = $wpdb->query( $wpdb->prepare( "INSERT INTO $this->sql['table'] SET $field_sql", $values ) );
		}
		if ( $check ) {
			if ( 0 == $this->insert_id ) {
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

		if ( 1 != pods_var( 'deleted_bulk', 'get', 0 ) ) {
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

				$this->fields['export '] = array();

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
								<?php echo esc_html( $field['label'] ); ?>
							</label>
						</li>
					<?php } ?>
				</ul>

				<p class="submit">
					<?php _e( 'Export as:', 'pods' ); ?>&nbsp;&nbsp;
					<?php foreach ( $this->export['formats'] as $format => $separator ) { ?>
						<input type="submit" id="export_type_<?php echo esc_attr( strtoupper( $format ) ); ?>" value=" <?php echo esc_attr( strtoupper( $format ) ); ?> " name="bulk_export_type" class="button-primary" />
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
			$export_type = pods_var( 'export_type', 'get', 'csv' );
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

		$this->message( sprintf( __( '<strong>Success:</strong> Your export is ready, you can download it <a href="%s" target="_blank">here</a>', 'pods' ), $export_file ) );

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

		if ( isset( $this->row[ $field ] ) ) {
			$value = $this->row[ $field ];
		} elseif ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
			if ( 'Pod' == get_class( $this->pod ) ) {
				$value = $this->pod->get_field( $field );
			} else {
				$value = $this->pod->field( $field );
			}
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

		$defaults = array(
			'full'    => false,
			'flatten' => true,
			'fields'  => null,
			'type'    => '',
		);

		if ( ! empty( $params ) && is_array( $params ) ) {
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

		if ( ! empty( $params ) && is_array( $params ) ) {
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
				$this->data = $this->pods_data->data;

				if ( ! empty( $this->data ) ) {
					$this->data_keys = array_keys( $this->data );
				}

				$this->total       = $this->pods_data->total();
				$this->total_found = $this->pods_data->total_found();
			} else {
				$this->data_full = $this->pods_data->data;

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
	 * @return array
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
			if ( empty( $_REQUEST[ '_wpnonce' . $this->num ] ) || false === wp_verify_nonce( $_REQUEST[ '_wpnonce' . $this->num ], 'pods-ui-action-bulk' ) ) {
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

		if ( true === $reorder ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		$icon_style = '';
		if ( false !== $this->icon ) {
			$icon_style = ' style="background-position:0 0;background-size:100%;background-image:url(' . esc_url( $this->icon ) . ');"';
		}

		?>
	<div class="wrap pods-admin pods-ui">
		<div class="pods-admin-container">
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
								'action' . $this->num => 'manage',
								'id' . $this->num     => '',
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
							'action' . $this->num => 'add',
							'id' . $this->num     => '',
							'do' . $this->num     => '',
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
					$link = pods_query_arg( array( 'action' . $this->num => 'reorder' ), self::$allowed, $this->exclusion() );

					if ( ! empty( $this->action_links['reorder'] ) ) {
						$link = $this->action_links['reorder'];
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo wp_kses_post( $this->label['reorder'] ); ?></a>
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
						'_wpnonce' . $this->num,
					);

					$filters = $this->filters;

				foreach ( $filters as $k => $filter ) {
					if ( isset( $this->pod->fields[ $filter ] ) ) {
						$filter_field = $this->pod->fields[ $filter ];
					} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
						$filter_field = $this->fields['manage'][ $filter ];
					} else {
						unset( $filters[ $k ] );
						continue;
					}

					if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
						if ( '' == pods_var_raw( 'filter_' . $filter . '_start', 'get', '', null, true ) && '' == pods_var_raw( 'filter_' . $filter . '_end', 'get', '', null, true ) ) {
							unset( $filters[ $k ] );
							continue;
						}
					} elseif ( '' === pods_var_raw( 'filter_' . $filter, 'get', '' ) ) {
						unset( $filters[ $k ] );
						continue;
					}

					$excluded_filters[] = 'filter_' . $filter . '_start';
					$excluded_filters[] = 'filter_' . $filter . '_end';
					$excluded_filters[] = 'filter_' . $filter;
				}//end foreach

					$get = $_GET;

				foreach ( $get as $k => $v ) {
					if ( is_array( $v ) || in_array( $k, $excluded_filters ) || 1 > strlen( $v ) ) {
						continue;
					}
				?>
				<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
				<?php
				}

				if ( false !== $this->callback( 'header', $reorder ) ) {
					return null;
				}

				if ( false === $this->data ) {
					$this->get_data();
				} elseif ( $this->sortable ) {
					// we have the data already as an array
					$this->sort_data();}

					if ( ! in_array( 'export', $this->actions_disabled ) && 'export' === $this->action ) {
						$this->export();}

					if ( ( ! empty( $this->data ) || false !== $this->search || ( $this->filters_enhanced && ! empty( $this->views ) ) ) && ( ( $this->filters_enhanced && ! empty( $this->views ) ) || false !== $this->searchable ) ) {
						if ( $this->filters_enhanced ) {
							$this->filters();
						} else {
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
								} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
									$filter_field = $this->fields['manage'][ $filter ];
								} else {
									continue;
								}

								if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
									$start = pods_var_raw( 'filter_' . $filter . '_start', 'get', pods_var_raw( 'filter_default', $filter_field, '', null, true ), null, true );
									$end   = pods_var_raw( 'filter_' . $filter . '_end', 'get', pods_var_raw( 'filter_ongoing_default', $filter_field, '', null, true ), null, true );

									// override default value
									$filter_field['options']['default_value']                          = '';
									$filter_field['options'][ $filter_field['type'] . '_allow_empty' ] = 1;

									if ( ! empty( $start ) && ! in_array( $start, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
										$start = PodsForm::field_method( $filter_field['type'], 'convert_date', $start, 'n/j/Y' );
									}

									if ( ! empty( $end ) && ! in_array( $end, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
										$end = PodsForm::field_method( $filter_field['type'], 'convert_date', $end, 'n/j/Y' );
									}
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
										<?php echo esc_html( $filter_field['label'] ); ?>
									</label>
									<?php echo PodsForm::field( 'filter_' . $filter . '_start', $start, $filter_field['type'], $filter_field ); ?>

									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">
										to
									</label>
								<?php
									echo PodsForm::field( 'filter_' . $filter . '_end', $end, $filter_field['type'], $filter_field );
								} elseif ( 'pick' === $filter_field['type'] ) {
									$value = pods_var_raw( 'filter_' . $filter );

									if ( strlen( $value ) < 1 ) {
										$value = pods_var_raw( 'filter_default', $filter_field );}

									// override default value
									$filter_field['options']['default_value'] = '';

									$filter_field['options']['pick_format_type']   = 'single';
									$filter_field['options']['pick_format_single'] = 'dropdown';

									$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields['search'], array(), null, true ), array(), null, true ), '', null, true );
									$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', $filter_field['options'], $filter_field['options']['input_helper'], null, true );

									$options = array_merge( $filter_field, $filter_field['options'] );
								?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
										<?php echo esc_html( $filter_field['label'] ); ?>
									</label>
								<?php
									echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options );
								} elseif ( 'boolean' === $filter_field['type'] ) {
									$value = pods_var_raw( 'filter_' . $filter, 'get', '' );

									if ( strlen( $value ) < 1 ) {
										$value = pods_var_raw( 'filter_default', $filter_field );}

									// override default value
									$filter_field['options']['default_value'] = '';

									$filter_field['options']['pick_format_type']   = 'single';
									$filter_field['options']['pick_format_single'] = 'dropdown';

									$filter_field['options']['pick_object'] = 'custom-simple';
									$filter_field['options']['pick_custom'] = array(
										'1' => pods_var_raw( 'boolean_yes_label', $filter_field['options'], __( 'Yes', 'pods' ), null, true ),
										'0' => pods_var_raw( 'boolean_no_label', $filter_field['options'], __( 'No', 'pods' ), null, true ),
									);

									$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields['search'], array(), null, true ), array(), null, true ), '', null, true );
									$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', $filter_field['options'], $filter_field['options']['input_helper'], null, true );

									$options = array_merge( $filter_field, $filter_field['options'] );
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php echo esc_html( $filter_field['label'] ); ?>
									</label>
									<?php
									echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options );
								} else {
									$value = pods_var_raw( 'filter_' . $filter );

									if ( strlen( $value ) < 1 ) {
										$value = pods_var_raw( 'filter_default', $filter_field );}

									// override default value
									$filter_field['options']['default_value'] = '';

									$options                 = array();
									$options['input_helper'] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields['search'], array(), null, true ), array(), null, true ), '', null, true );
									$options['input_helper'] = pods_var_raw( 'ui_input_helper', $options, $options['input_helper'], null, true );
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
										<?php echo esc_html( $filter_field['label'] ); ?>
									</label>
									<?php
									echo PodsForm::field( 'filter_' . $filter, $value, 'text', $options );
								}//end if
							}//end foreach

							if ( false !== $this->do_hook( 'filters_show_search', true ) ) {
							?>
								<label<?php echo ( empty( $this->filters ) ) ? ' class="screen-reader-text"' : ''; ?> for="page-search<?php echo esc_attr( $this->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>:</label>
								<?php echo PodsForm::field( 'search' . $this->num, $this->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $this->num . '-input' ) ) ); ?>
							<?php
							} else {
								echo PodsForm::field( 'search' . $this->num, '', 'hidden' );
							}

							echo PodsForm::submit_button( $this->header['search'], 'button', false, false, array( 'id' => 'search' . $this->num . '-submit' ) );

							if ( 0 < strlen( $this->search ) ) {
								$clear_filters = array(
									'search' . $this->num => false,
								);

								foreach ( $this->filters as $filter ) {
									$clear_filters[ 'filter_' . $filter . '_start' ] = false;
									$clear_filters[ 'filter_' . $filter . '_end' ]   = false;
									$clear_filters[ 'filter_' . $filter ]            = false;
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
							<?php wp_nonce_field( 'pods-ui-action-bulk', '_wpnonce' . $this->num, false ); ?>

							<select name="action_bulk<?php echo esc_attr( $this->num ); ?>">
								<option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'pods' ); ?></option>

								<?php
								foreach ( $this->actions_bulk as $action => $action_data ) {
									if ( in_array( $action, $this->actions_hidden ) || in_array( $action, $this->actions_hidden ) ) {
										continue;}

									if ( ! isset( $action_data['label'] ) ) {
										$action_data['label'] = ucwords( str_replace( '_', ' ', $action ) );}
								?>
								<option value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $action_data['label'] ); ?></option>
								<?php
								}
								?>
							</select>

							<input type="submit" id="doaction_bulk<?php echo esc_attr( $this->num ); ?>" class="button-secondary action" value="<?php esc_attr_e( 'Apply', 'pods' ); ?>">
						</div>
					<?php
						}//end if

						if ( true !== $reorder && ( false !== $this->pagination_total || false !== $this->pagination ) ) {
							?>
							<div class="tablenav-pages<?php echo esc_attr( ( $this->limit < $this->total_found || 1 < $this->page ) ? '' : ' one-page' ); ?>">
								<?php $this->pagination( 1 ); ?>
							</div>
							<?php
						}

						if ( true === $reorder ) {
							$link = pods_query_arg(
								array(
									'action' . $this->num => 'manage',
									'id' . $this->num     => '',
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
										'action_bulk' . $this->num => 'export',
										'_wpnonce' => wp_create_nonce( 'pods-ui-action-bulk' ),
									), self::$allowed, $this->exclusion()
								)
							);
							?>
							<div class="alignleft actions">
								<input type="button" value="<?php echo esc_attr( sprintf( __( 'Export all %s', 'pods' ), $this->items ) ); ?>" class="button" onclick="document.location='<?php echo $export_document_location; ?>';" />
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
		<div class="pods-admin_friends-callout_container">
			<button class="pods-admin_friends-callout_close">
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 125" enable-background="new 0 0 100 100" xml:space="preserve"><polygon points="95,17 83,5 50,38 17,5 5,17 38,50 5,83 17,95 50,62 83,95 95,83 62,50 "/></svg>
			</button>
			<div class="pods-admin_friends-callout_logo-container">
				<svg version="1.1" viewBox="0 0 305 111" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>Friends of Pods Logo</title> <defs> <path id="a" d="m0.14762 49.116c0 27.103 21.919 49.075 48.956 49.075 19.888 0 37.007-11.888 44.669-28.962-2.1342-6.538-3.9812-18.041 3.3854-29.538-0.3152-1.624-0.71019-3.219-1.1807-4.781-22.589 22.49-40.827 24.596-54.558 24.229-0.12701-4e-3 -0.58883-0.079-0.71152-0.35667 0 0-0.20016-0.89933 0.38502-0.89933 26.307 0 41.29-15.518 53.531-26.865-0.66763-1.687-1.4264-3.3273-2.2695-4.9167-13.196-3.6393-18.267-14.475-20.067-20.221-6.9007-3.7267-14.796-5.8413-23.184-5.8413-27.037 0-48.956 21.972-48.956 49.076zm62.283-34.287s18.69-2.039 24.194 21.114c-20.424-1.412-24.194-21.114-24.194-21.114zm-7.4779 9.7423s16.57-0.55467 20.342 20.219c-17.938-2.602-20.342-20.219-20.342-20.219zm-9.1018 8.256s14.912-0.97567 18.728 17.626c-16.209-1.8273-18.728-17.626-18.728-17.626zm40.774 3.116c0.021279 0.0013333 0.040563 0.0026667 0.061842 0.0043333l-0.044886 0.067667c-0.0056523-0.024-0.011304-0.048-0.016957-0.072zm-48.709 3.445s13.103-0.859 16.456 15.486c-14.243-1.6047-16.456-15.486-16.456-15.486zm-10.498 1.6027s12.587-0.82333 15.808 14.877c-13.68-1.5417-15.808-14.877-15.808-14.877zm-10.391 0.72233s11.423-0.74667 14.347 13.502c-12.417-1.3997-14.347-13.502-14.347-13.502zm-11.298 2.0857s9.5832-2.526 13.712 9.0523c-0.44919 0.036667-0.88474 0.054333-1.3073 0.054333-9.6148-3.333e-4 -12.405-9.1067-12.405-9.1067zm81.565 0.964c-0.0073147-0.021333-0.014962-0.043667-0.021612-0.064667l0.066497 0.018333c-0.014962 0.015333-0.029924 0.030667-0.044886 0.046333 7.1338 21.557-5.5721 33.29-5.5721 33.29s-8.9279-18.395 5.5721-33.29zm-11.999 0.027333c0.017622 0.0023333 0.035576 5e-3 0.053198 0.0076667l-0.041561 0.056c-0.0036573-0.021667-0.0073147-0.042-0.011637-0.063667zm-10.716 5.6627c0.015959 2e-3 0.032916 0.0036666 0.048543 0.0056666l-0.036573 0.051c-0.0039899-0.019667-0.0076472-0.037333-0.01197-0.056667zm12.212 1.8693c-0.0023274-0.021-0.0046548-0.043333-0.0066497-0.064667l0.056523 0.035667c-0.016624 0.01-0.032916 0.019333-0.049873 0.029 2.2323 21.678-11.682 28.793-11.682 28.793s-4.4962-19.303 11.682-28.793zm-57.318 0.52633-0.018619 0.037333c-0.0039899-0.011667-0.0086447-0.023667-0.012302-0.034667 0.010307-1e-3 0.020614-0.0016667 0.030921-0.0026667zm34.9 2.0257c0.013964 0.0013333 0.027264 3e-3 0.041561 0.0043333l-0.031254 0.046333c-0.0036573-0.017-0.0069822-0.034-0.010307-0.050667zm-22.998 0.34067c0.012302 0.0016666 0.025269 3e-3 0.037571 0.0043333l-0.028594 0.039c-0.0029924-0.014667-0.0056523-0.028667-0.0089771-0.043333zm-16.9 3.542s8.0594-7.693 18.254 0.88467c-3.1137 2.6673-6.0605 3.5853-8.642 3.5853-5.6965 0-9.6125-4.47-9.6125-4.47zm28.752-2.8893c0.013632 0.0013334 0.027264 3e-3 0.040896 0.0043334l-0.030589 0.045c-0.0033249-0.016-0.0069822-0.033333-0.010307-0.049333zm21.183 2.4917c3.325e-4 -0.019 3.325e-4 -0.039333 6.65e-4 -0.058333l0.046548 0.039667c-0.015959 0.0063334-0.031254 0.012333-0.047213 0.018667-0.19118 19.436-13.172 23.733-13.172 23.733s-2.0415-17.662 13.172-23.733zm-31.651 1.257v0.05c-0.0099746-0.0083333-0.019617-0.016333-0.029259-0.024667 0.0093096-0.0086666 0.019284-0.017 0.029259-0.025333zm-6.6234 10.543s4.296-11.202 16.634-9.0197c-3.5273 8.062-9.0759 9.5947-12.782 9.5947-2.2719 0-3.8515-0.575-3.8515-0.575zm16.651-9.0593 0.018287 0.046c-0.01197-2e-3 -0.023606-0.0043334-0.035576-0.0063334 0.0059848-0.013333 0.011637-0.026333 0.017289-0.039667zm10.12 0.685c0.0029923-0.016333 0.0059847-0.033333 0.0089771-0.049667l0.034911 0.043c-0.014629 0.0023333-0.029259 0.0046667-0.043888 0.0066667-2.9518 16.71-14.755 17.762-14.755 17.762s0.77369-15.639 14.755-17.762z"/> </defs> <g fill="none" fill-rule="evenodd"> <g transform="translate(6.3172 6.3333)"> <mask id="b" fill="white"> <use xlink:href="#a"/> </mask> <polygon points="-3.1772 -3.2937 100.48 -3.2937 100.48 101.52 -3.1772 101.52" fill="#fff" mask="url(#b)"/> </g> <path d="m55.303 3.569c-28.538 0-51.754 23.273-51.754 51.88 0 28.607 23.216 51.88 51.754 51.88 28.538 0 51.754-23.273 51.754-51.88 0-28.607-23.217-51.88-51.754-51.88m0 107.18c-30.417 0-55.163-24.807-55.163-55.298 0-30.492 24.746-55.298 55.163-55.298 30.417 0 55.164 24.807 55.164 55.298 0 30.491-24.747 55.298-55.164 55.298" fill="#fff"/> <path d="m137.42 77.263-2.7699 22.725h-14.958l8.2174-67.434h22.252c4.001 0 7.4249 0.48597 10.272 1.4579 2.8469 0.97195 5.1859 2.3141 7.0171 4.0266 1.8312 1.7125 3.1777 3.7335 4.0395 6.0631 0.86176 2.3296 1.2926 4.8366 1.2926 7.521 0 3.6718-0.56167 7.0736-1.685 10.205-1.1234 3.1318-2.8392 5.8394-5.1474 8.1227s-5.232 4.0729-8.7714 5.3688-7.725 1.9439-12.557 1.9439h-7.2018zm4.1549-33.463-2.7238 22.123h7.248c2.1544 0 3.9625-0.31626 5.4244-0.9488 1.4619-0.63254 2.6468-1.5273 3.5547-2.6844 0.90792-1.1571 1.5619-2.5378 1.962-4.1423 0.4001-1.6045 0.60015-3.3632 0.60015-5.2763 0-1.3268-0.17696-2.5456-0.5309-3.6564-0.35394-1.1108-0.90022-2.0673-1.6389-2.8695-0.73865-0.80224-1.6696-1.4271-2.793-1.8745-1.1234-0.4474-2.4544-0.6711-3.9933-0.6711h-7.1095zm48.956 46.283c1.6004 0 3.0315-0.48597 4.2934-1.4579 1.2619-0.97195 2.3313-2.2987 3.2085-3.9803 0.87714-1.6816 1.5542-3.6409 2.0313-5.8779 0.47704-2.237 0.71556-4.6206 0.71556-7.1507 0-3.4867-0.46934-5.9782-1.408-7.4747-0.9387-1.4965-2.4698-2.2447-4.5934-2.2447-1.6004 0-3.0315 0.48597-4.2934 1.4579-1.2619 0.97195-2.3313 2.291-3.2085 3.9572s-1.5542 3.6255-2.0313 5.8779c-0.47704 2.2525-0.71556 4.6437-0.71556 7.1739 0 3.425 0.46934 5.9011 1.408 7.4284 0.9387 1.5273 2.4698 2.291 4.5934 2.291zm-1.2926 10.645c-2.6776 0-5.1782-0.4474-7.5019-1.3422-2.3237-0.89481-4.3395-2.1984-6.0477-3.9109-1.7081-1.7125-3.0469-3.8261-4.0164-6.3408-0.96948-2.5147-1.4542-5.4074-1.4542-8.6781 0-4.2581 0.70017-8.1689 2.1005-11.733 1.4004-3.5638 3.2854-6.6416 5.6552-9.2335s5.1166-4.6129 8.2405-6.0631 6.4093-2.1753 9.8563-2.1753c2.6776 0 5.1705 0.4474 7.4788 1.3422 2.3083 0.89481 4.3164 2.1984 6.0246 3.9109 1.7081 1.7125 3.0546 3.8261 4.0395 6.3408 0.98486 2.5147 1.4773 5.4074 1.4773 8.6781 0 4.1963-0.70017 8.0764-2.1005 11.64-1.4004 3.5638-3.2854 6.6493-5.6552 9.2566-2.3698 2.6073-5.1166 4.6437-8.2405 6.1094-3.1239 1.4656-6.4093 2.1984-9.8563 2.1984zm62.6-0.74053c-0.89253 0-1.6389-0.11571-2.239-0.34712-0.60015-0.23142-1.0772-0.54768-1.4311-0.9488s-0.60015-0.87937-0.73864-1.4348c-0.1385-0.5554-0.20774-1.1571-0.20774-1.805v-4.0266c-2.1852 2.9004-4.6011 5.176-7.248 6.8267-2.6468 1.6508-5.4629 2.4761-8.4482 2.4761-1.9697 0-3.7702-0.34712-5.4013-1.0414-1.6312-0.69425-3.0392-1.7819-4.2241-3.263s-2.1082-3.3709-2.7699-5.6697c-0.66171-2.2987-0.99255-5.0525-0.99255-8.2615 0-2.777 0.29238-5.4845 0.87714-8.1227 0.58476-2.6381 1.4003-5.1143 2.4468-7.4284s2.2929-4.4354 3.7394-6.3639c1.4465-1.9285 3.0392-3.5792 4.7781-4.9523 1.7389-1.3731 3.5778-2.4453 5.5168-3.2167s3.9394-1.1571 6.0015-1.1571c1.9697 0 3.7471 0.33169 5.3321 0.99508 1.585 0.66339 2.9777 1.535 4.178 2.615l3.0469-24.16h14.034l-8.5867 69.286h-7.6634zm-14.588-10.182c0.98486 0 2.0082-0.37026 3.07-1.1108s2.0928-1.7587 3.0931-3.0547 1.9312-2.8155 2.793-4.5589c0.86176-1.7433 1.6004-3.6178 2.2159-5.6234l1.4773-11.663c-0.89253-0.70968-1.8851-1.2188-2.9777-1.5273s-2.1313-0.46283-3.1162-0.46283c-1.662 0-3.1931 0.55539-4.5934 1.6662s-2.6083 2.5687-3.624 4.3737c-1.0156 1.805-1.8081 3.8646-2.3775 6.1788s-0.85406 4.6746-0.85406 7.0813c0 3.0547 0.44626 5.2685 1.3388 6.6416 0.89253 1.3731 2.0774 2.0596 3.5547 2.0596zm63.465-27.724c-0.43088 0.5554-0.83097 0.95651-1.2003 1.2034s-0.86175 0.37026-1.4773 0.37026-1.2234-0.13885-1.8235-0.41655-1.2465-0.59396-1.9389-0.9488c-0.69248-0.35484-1.4773-0.6711-2.3544-0.9488-0.87714-0.2777-1.8851-0.41655-3.0238-0.41655-2.1236 0-3.6394 0.40883-4.5473 1.2265-0.90792 0.81767-1.3619 1.8745-1.3619 3.1704 0 0.8331 0.25391 1.5428 0.76173 2.129 0.50782 0.58625 1.1772 1.1108 2.0082 1.5736 0.83098 0.46283 1.7774 0.88709 2.8392 1.2728 1.0618 0.38569 2.1467 0.81766 3.2547 1.2959 1.108 0.47826 2.1928 1.0259 3.2547 1.643s2.0082 1.3731 2.8392 2.2679c0.83098 0.89481 1.5004 1.9593 2.0082 3.1935 0.50782 1.2342 0.76173 2.6998 0.76173 4.3969 0 2.4067-0.47704 4.6823-1.4311 6.8267-0.95409 2.1445-2.3236 4.0112-4.1087 5.6002-1.7851 1.5891-3.9471 2.8541-6.4862 3.7952-2.5391 0.94109-5.3936 1.4116-8.5637 1.4116-1.5081 0-3.0007-0.15428-4.478-0.46283-1.4773-0.30856-2.8699-0.7251-4.178-1.2496s-2.5006-1.1494-3.5778-1.8745c-1.0772-0.7251-1.9543-1.5042-2.6314-2.3373l3.5086-5.2763c0.43088-0.61711 0.931-1.1031 1.5004-1.4579 0.56938-0.35484 1.2695-0.53225 2.1005-0.53225 0.76942 0 1.4311 0.18513 1.9851 0.5554 0.55399 0.37027 1.1541 0.77138 1.8004 1.2034s1.4157 0.83309 2.3083 1.2034c0.89253 0.37027 2.062 0.5554 3.5086 0.5554 2.0313 0 3.5393-0.45511 4.5242-1.3653 0.98486-0.91024 1.4773-1.9824 1.4773-3.2167 0-0.95652-0.25391-1.7433-0.76173-2.3604-0.50782-0.61711-1.1772-1.1494-2.0082-1.5968-0.83098-0.4474-1.7697-0.84852-2.8161-1.2034-1.0464-0.35484-2.1236-0.74824-3.2316-1.1802s-2.1852-0.93337-3.2316-1.5042c-1.0464-0.57083-1.9851-1.2882-2.8161-2.1522-0.83098-0.86395-1.5004-1.9285-2.0082-3.1935-0.50782-1.2651-0.76173-2.7924-0.76173-4.582 0-2.2216 0.40779-4.3814 1.2234-6.4796s2.0313-3.9572 3.6471-5.5771c1.6158-1.6199 3.624-2.9235 6.0246-3.9109s5.2013-1.4811 8.4021-1.4811c3.2008 0 6.0553 0.55539 8.5637 1.6662 2.5083 1.1108 4.578 2.4684 6.2092 4.0729l-3.6932 5.0911z" fill="#fff"/> <g transform="translate(128.86 3.5426)" fill="#fff"> <path d="m14.411 3.7958h-8.3873l-0.77821 6.399h7.0759l-0.3891 3.0104h-7.0471l-1.052 8.6095h-3.7613l2.5652-21.029h12.149l-0.37469 3.0104zm-0.59086 18.019 1.8014-14.936h1.8014c0.34587 0 0.62929 0.077562 0.85026 0.23269 0.22097 0.15513 0.33146 0.40236 0.33146 0.7417v0.050901c0 0.033934-0.0048037 0.13331-0.014411 0.29813s-0.019215 0.42417-0.028822 0.77806c-0.0096075 0.35388-0.028822 0.85077-0.057645 1.4907 0.60527-1.1732 1.2682-2.0797 1.9887-2.7196 0.72056-0.6399 1.4699-0.95985 2.2482-0.95985 0.39391 0 0.79262 0.087258 1.1961 0.26178l-0.6485 3.4322c-0.48038-0.2036-0.93673-0.30541-1.3691-0.30541-0.85507 0-1.6044 0.41205-2.2482 1.2362-0.6437 0.82411-1.1721 2.1136-1.5852 3.8685l-0.77821 6.5299h-3.4875zm16.083-14.921-1.7726 14.921h-3.5163l1.7726-14.921h3.5163zm0.8935-4.3484c0 0.31025-0.06485 0.60111-0.19455 0.87259-0.1297 0.27147-0.29783 0.50901-0.50439 0.71261-0.20656 0.2036-0.44434 0.366-0.71336 0.48719-0.26901 0.12119-0.54762 0.18179-0.83585 0.18179-0.27862 0-0.55003-0.060596-0.81423-0.18179-0.26421-0.12119-0.49719-0.28359-0.69894-0.48719s-0.36268-0.44114-0.48278-0.71261c-0.12009-0.27147-0.18014-0.56233-0.18014-0.87259s0.062448-0.60596 0.18735-0.88713 0.28822-0.52597 0.48998-0.73443c0.20176-0.20845 0.43474-0.37327 0.69894-0.49447 0.26421-0.12119 0.53562-0.18179 0.81423-0.18179 0.28823 0 0.56684 0.060596 0.83585 0.18179 0.26901 0.12119 0.50679 0.28359 0.71336 0.48719 0.20656 0.2036 0.37229 0.44599 0.49719 0.72716s0.18735 0.58172 0.18735 0.90167zm13.402 7.8097c0 0.66899-0.1321 1.2798-0.39631 1.8324-0.26421 0.55264-0.73737 1.0471-1.4195 1.4834-0.68213 0.4363-1.6068 0.81199-2.7742 1.1271-1.1673 0.3151-2.6541 0.56476-4.4603 0.74897v0.18906c0 2.3463 0.98476 3.5194 2.9543 3.5194 0.42273 0 0.79742-0.041205 1.1241-0.12362s0.61248-0.18179 0.85747-0.29813 0.46596-0.24723 0.66292-0.39266c0.19695-0.14543 0.37949-0.27632 0.54763-0.39266 0.16813-0.11635 0.33386-0.21572 0.49719-0.29813 0.16333-0.082411 0.34106-0.12362 0.53322-0.12362 0.11529 0 0.23058 0.026662 0.34587 0.079987 0.11529 0.053325 0.21136 0.13331 0.28822 0.23996l0.90791 1.1053c-0.5092 0.51386-1.0088 0.95984-1.4988 1.338-0.48998 0.37812-0.98957 0.68837-1.4988 0.93076-0.5092 0.24239-1.0472 0.42175-1.6141 0.5381-0.56684 0.11635-1.1865 0.17452-1.859 0.17452-0.86468 0-1.6477-0.14785-2.349-0.44356-0.70135-0.29571-1.3018-0.71261-1.8014-1.2507-0.49959-0.5381-0.88629-1.1877-1.1601-1.9488-0.27381-0.76109-0.41072-1.6119-0.41072-2.5523 0-0.78533 0.084065-1.5561 0.2522-2.3124 0.16813-0.75625 0.41072-1.4737 0.72777-2.1524 0.31705-0.67868 0.70615-1.304 1.1673-1.8761 0.46116-0.57203 0.98236-1.0665 1.5636-1.4834 0.58126-0.4169 1.2201-0.7417 1.9167-0.97439 0.69655-0.23269 1.4435-0.34903 2.2409-0.34903 0.77821 0 1.4579 0.1115 2.0392 0.33449 0.58126 0.223 1.0664 0.51143 1.4555 0.86532 0.3891 0.35388 0.67973 0.74897 0.87188 1.1853 0.19215 0.4363 0.28822 0.86289 0.28822 1.2798zm-4.8566-1.1634c-0.48038 0-0.92712 0.099377-1.3402 0.29813-0.41312 0.19876-0.78541 0.4775-1.1169 0.83623-0.33146 0.35873-0.61968 0.78775-0.86467 1.2871-0.24499 0.49932-0.43954 1.0447-0.58365 1.6361 1.1913-0.16482 2.1497-0.34419 2.875-0.5381 0.72537-0.19391 1.2874-0.40721 1.6861-0.6399 0.39871-0.23269 0.66292-0.47992 0.79262-0.7417 0.1297-0.26178 0.19455-0.54294 0.19455-0.8435 0-0.14543-0.031224-0.29571-0.093673-0.45084-0.062449-0.15513-0.15852-0.29329-0.28822-0.41448-0.1297-0.12119-0.29783-0.22299-0.50439-0.30541-0.20656-0.082411-0.45876-0.12362-0.75659-0.12362zm6.0095 12.623 1.7726-14.936h1.8158c0.37469 0 0.66532 0.092105 0.87188 0.27632 0.20656 0.18421 0.30984 0.47992 0.30984 0.88713l-0.10088 1.9342c0.74939-1.115 1.5804-1.9464 2.4931-2.4941 0.91272-0.54779 1.8542-0.82169 2.8246-0.82169 0.54763 0 1.0448 0.099377 1.4916 0.29813s0.82864 0.48962 1.1457 0.87259 0.56204 0.85319 0.73497 1.4107c0.17294 0.55749 0.2594 1.1998 0.2594 1.927 0 0.18421-0.0072055 0.37085-0.021617 0.55991s-0.031224 0.38539-0.050439 0.589l-1.1097 9.4967h-3.5596c0.19215-1.6385 0.35548-3.0274 0.48998-4.1666 0.13451-1.1392 0.24499-2.0869 0.33146-2.8432 0.086468-0.75625 0.15372-1.3501 0.20176-1.7815 0.048038-0.43145 0.084065-0.75866 0.10808-0.98166 0.024019-0.223 0.03843-0.37085 0.043234-0.44356s0.0072056-0.13331 0.0072056-0.18179c0-0.6399-0.11529-1.1029-0.34587-1.3889s-0.59086-0.42902-1.0808-0.42902c-0.39391 0-0.80222 0.11634-1.225 0.34903-0.42273 0.23269-0.82624 0.56233-1.2105 0.98893-0.3843 0.4266-0.73497 0.94045-1.052 1.5416-0.31705 0.60112-0.57645 1.2701-0.77821 2.0069l-0.80703 7.3297h-3.5596zm25.738 0c-0.43234 0-0.73497-0.1018-0.90791-0.30541-0.17294-0.2036-0.2594-0.46053-0.2594-0.77078l0.11529-2.1815c-0.71096 1.0665-1.5084 1.9124-2.3923 2.5378-0.88389 0.62536-1.8254 0.93803-2.8246 0.93803-0.61488 0-1.1745-0.11392-1.6789-0.34176-0.5044-0.22784-0.93433-0.5696-1.2898-1.0253s-0.62929-1.0326-0.82144-1.7306c-0.19215-0.69807-0.28822-1.5222-0.28822-2.4723 0-0.8435 0.086466-1.67 0.2594-2.4796 0.17294-0.80957 0.41792-1.5779 0.73497-2.3051 0.31705-0.72716 0.69414-1.3961 1.1313-2.0069 0.43714-0.61081 0.92472-1.1392 1.4627-1.5852 0.53802-0.44599 1.1121-0.79502 1.7221-1.0471s1.2418-0.37812 1.8951-0.37812c0.66292 0 1.2634 0.12119 1.8014 0.36358 0.53802 0.24239 1.0088 0.57203 1.4123 0.98893l0.96555-7.8097h3.4875l-2.6373 21.611h-1.8879zm-4.8854-2.6032c-0.60527 0-1.076-0.2545-1.4123-0.76351-0.33626-0.50901-0.50439-1.2871-0.50439-2.3342 0-0.80472 0.10088-1.607 0.30264-2.4069 0.20176-0.79988 0.48758-1.5198 0.85747-2.1597 0.36989-0.6399 0.81423-1.1586 1.333-1.5561 0.51881-0.39751 1.0952-0.59627 1.7293-0.59627 0.40352 0 0.81663 0.072715 1.2394 0.21815s0.79742 0.39751 1.1241 0.75624l-0.44675 3.6212c-0.23058 0.73685-0.50679 1.4228-0.82865 2.0579-0.32185 0.63505-0.67012 1.1877-1.0448 1.6579-0.37469 0.47023-0.76379 0.83865-1.1673 1.1053s-0.79742 0.39994-1.1817 0.39994zm19.549-9.5612c-0.10501 0.15274-0.20525 0.26014-0.30071 0.32219-0.095464 0.062051-0.21956 0.093077-0.37231 0.093077-0.16229 0-0.32935-0.047731-0.50118-0.14319-0.17183-0.095464-0.36992-0.20286-0.59426-0.32219-0.22434-0.11933-0.48209-0.22672-0.77325-0.32219s-0.64199-0.14319-1.0525-0.14319c-0.75416 0-1.3317 0.16945-1.7327 0.50834-0.40095 0.3389-0.60142 0.76132-0.60142 1.2673 0 0.29594 0.083529 0.54653 0.25059 0.75177s0.38424 0.38424 0.65154 0.53698c0.2673 0.15274 0.57278 0.29355 0.91645 0.42242s0.69688 0.2673 1.0596 0.41526 0.71597 0.31503 1.0596 0.50118c0.34367 0.18615 0.64915 0.41288 0.91645 0.68017 0.2673 0.2673 0.48447 0.58948 0.65154 0.96656s0.25059 0.82814 0.25059 1.3532c0 0.70643-0.14081 1.3794-0.42242 2.019-0.28162 0.63961-0.68495 1.2004-1.21 1.6825-0.52505 0.48209-1.1599 0.86394-1.9045 1.1456-0.74462 0.28162-1.5751 0.42242-2.4916 0.42242-0.46777 0-0.91883-0.045344-1.3532-0.13603-0.43436-0.09069-0.84246-0.21718-1.2243-0.37947-0.38185-0.16229-0.7279-0.35321-1.0382-0.57278-0.31026-0.21957-0.57516-0.45822-0.79473-0.71597l0.85917-1.346c0.10501-0.16229 0.2315-0.28639 0.37947-0.37231 0.14797-0.085917 0.32219-0.12888 0.52266-0.12888s0.38185 0.06205 0.54414 0.18615c0.16229 0.1241 0.35321 0.26014 0.57278 0.4081s0.48447 0.284 0.79473 0.4081 0.70881 0.18615 1.1957 0.18615c0.3914 0 0.73745-0.052504 1.0382-0.15751 0.30071-0.10501 0.55369-0.2482 0.75893-0.42958 0.20525-0.18138 0.36037-0.3914 0.46538-0.63006 0.10501-0.23866 0.15751-0.49163 0.15751-0.75893 0-0.32458-0.081143-0.59426-0.24343-0.80905-0.16229-0.21479-0.37946-0.40094-0.65154-0.55846-0.27207-0.15751-0.57994-0.29594-0.92361-0.41526s-0.69449-0.24582-1.0525-0.37947c-0.35799-0.13365-0.70643-0.28639-1.0453-0.45822s-0.64437-0.38901-0.91645-0.65154c-0.27207-0.26252-0.48925-0.58232-0.65154-0.9594-0.16229-0.37708-0.24343-0.83769-0.24343-1.3818 0-0.64915 0.1241-1.2816 0.37231-1.8973 0.24821-0.61574 0.61335-1.1599 1.0954-1.6324 0.48209-0.47254 1.074-0.85201 1.7756-1.1384 0.70166-0.28639 1.5059-0.42958 2.4128-0.42958 0.93554 0 1.7637 0.1599 2.4844 0.4797 0.72075 0.3198 1.3293 0.7279 1.8257 1.2243l-0.91645 1.2888z"/> <path d="m57.693 40.297c0.49639 0 0.94473-0.14341 1.345-0.43023 0.40031-0.28682 0.74258-0.6706 1.0268-1.1513 0.28422-0.48073 0.50239-1.0402 0.65451-1.6785 0.15212-0.63828 0.22818-1.3089 0.22818-2.0118 0-1.0099-0.17013-1.7533-0.5104-2.2299-0.34027-0.47669-0.85466-0.71504-1.5432-0.71504-0.49639 0-0.94473 0.14139-1.345 0.42417s-0.74058 0.66656-1.0208 1.1513c-0.28022 0.48477-0.49639 1.0463-0.6485 1.6846-0.15212 0.63828-0.22818 1.3129-0.22818 2.0239 0 1.0099 0.16813 1.7512 0.50439 2.2239s0.84866 0.70898 1.5372 0.70898zm-0.26421 2.3027c-0.68053 0-1.311-0.11513-1.8915-0.3454s-1.0808-0.56758-1.5012-1.012-0.75058-0.98974-0.99077-1.6361c-0.24019-0.64636-0.36028-1.3856-0.36028-2.2178 0-1.0746 0.17013-2.0764 0.5104-3.0056 0.34027-0.92915 0.80462-1.7331 1.3931-2.4117 0.58846-0.67868 1.275-1.2119 2.0596-1.5997 0.78461-0.38782 1.6213-0.58172 2.51-0.58172 0.68053 0 1.311 0.11513 1.8915 0.3454s1.0828 0.56758 1.5072 1.012c0.42433 0.44438 0.75659 0.98974 0.99678 1.6361 0.24019 0.64636 0.36028 1.3856 0.36028 2.2178 0 1.0665-0.17213 2.0623-0.5164 2.9874-0.34427 0.92511-0.81063 1.729-1.3991 2.4117-0.58846 0.68272-1.275 1.22-2.0596 1.6119-0.78461 0.39186-1.6213 0.58778-2.51 0.58778zm12.177-10.374-1.1649 9.6833-0.61248 2.2784c-0.11209 0.3959-0.28622 0.69888-0.52241 0.90894-0.23619 0.21007-0.57044 0.3151-1.0028 0.3151h-1.1889l1.5732-13.186-0.94874-0.16967c-0.17614-0.032318-0.32025-0.098973-0.43234-0.19997-0.11209-0.10099-0.16813-0.2444-0.16813-0.43023 0-0.0080796 0.0020015-0.024238 0.0060047-0.048477 0.0040031-0.024239 0.010008-0.078775 0.018014-0.16361 0.0080063-0.084835 0.022017-0.21411 0.042033-0.38782 0.020016-0.17371 0.050039-0.41811 0.09007-0.73322h1.6573l0.10808-0.92106c0.088069-0.711 0.27421-1.3493 0.55843-1.9148 0.28422-0.56557 0.6445-1.0463 1.0808-1.4422 0.43634-0.3959 0.93873-0.69888 1.5072-0.90894 0.56845-0.21007 1.1809-0.3151 1.8374-0.3151 0.5124 0 0.98476 0.076755 1.4171 0.23027l-0.26421 1.5513c-0.016013 0.088875-0.054042 0.15957-0.11409 0.21209-0.060047 0.052517-0.1341 0.090894-0.22217 0.11513s-0.18614 0.040398-0.29423 0.048477c-0.10808 0.0080796-0.21817 0.012119-0.33026 0.012119-0.32025 0-0.61448 0.044437-0.88269 0.13331s-0.50439 0.23228-0.70855 0.43023c-0.20416 0.19795-0.37229 0.45649-0.50439 0.77563-0.1321 0.31914-0.22618 0.70897-0.28222 1.1695l-0.096075 0.82411h2.7622l-0.27622 2.133h-2.6421z"/> </g> </g> </svg>
			</div>
			<div class="pods-admin_friends-callout_content-container">
				<h2 class="pods-admin_friends-callout_headline"><?php printf( esc_html__( 'We need %1$sYOU%2$s in 2020', 'pods' ), '<span class="pods-admin_friends-you">', '</span>' ); ?></h2>
				<p class="pods-admin_friends-callout_text"><?php esc_html_e( "Things they are a changin' and we want you to be a part of it! Our goal is to be fully funded by users like you. Help us reach our goal of 200 recurring donors in 2020.", 'pods' ); ?></p>
				<div class="pods-admin_friends-callout_button-group">
					<a href="https://friends.pods.io/" class="pods-admin_friends-callout_button"><?php esc_html_e( 'Learn More', 'pods' ); ?></a>
					<a href="https://pods.io/friends-of-pods/membership-levels/" class="pods-admin_friends-callout_button--join"><?php esc_html_e( 'Join Now', 'pods' ); ?></a>
				</div>
			</div>
		</div>
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
		wp_enqueue_style( 'pods-styles' );

		$filters = $this->filters;

		foreach ( $filters as $k => $filter ) {
			if ( isset( $this->pod->fields[ $filter ] ) ) {
				$filter_field = $this->pod->fields[ $filter ];
			} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
				$filter_field = $this->fields['manage'][ $filter ];
			} else {
				continue;
			}

			if ( isset( $filter_field ) && in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
				if ( '' == pods_var_raw( 'filter_' . $filter . '_start', 'get', '', null, true ) && '' == pods_var_raw( 'filter_' . $filter . '_end', 'get', '', null, true ) ) {
					unset( $filters[ $k ] );
				}
			} elseif ( '' === pods_var_raw( 'filter_' . $filter, 'get', '' ) ) {
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
										'view' . $this->num => $view,
										'pg' . $this->num => '',
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
						if ( $filtered || '' != pods_var_raw( 'search' . $this->num, 'get', '', null, true ) ) {
							$clear_filters = array(
								'search' . $this->num => false,
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
										'orderby' . $this->num,
										'orderby_dir' . $this->num,
										'limit' . $this->num,
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
							<label class="screen-reader-text" for="page-search<?php echo esc_attr( $this->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>:</label>
							<?php echo PodsForm::field( 'search' . $this->num, $this->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $this->num . '-input' ) ) ); ?>
							<?php
						} else {
							echo PodsForm::field( 'search' . $this->num, '', 'hidden' );
						}
						?>

						<?php echo PodsForm::submit_button( $this->header['search'], 'button', false, false, array( 'id' => 'search' . $this->num . '-submit' ) ); ?>
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
							$value = pods_var_raw( 'filter_' . $filter );

							if ( isset( $this->pod->fields[ $filter ] ) ) {
								$filter_field = $this->pod->fields[ $filter ];
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
								$start = pods_var_raw( 'filter_' . $filter . '_start', 'get', '', null, true );
								$end   = pods_var_raw( 'filter_' . $filter . '_end', 'get', '', null, true );

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
								$value_label = trim( PodsForm::field_method( 'pick', 'value_to_label', $filter, $value, $filter_field, $this->pod->pod_data, null ) );
							} elseif ( 'boolean' === $filter_field['type'] ) {
								$yesno_options = array(
									'1' => pods_var_raw( 'boolean_yes_label', $filter_field['options'], __( 'Yes', 'pods' ), null, true ),
									'0' => pods_var_raw( 'boolean_no_label', $filter_field['options'], __( 'No', 'pods' ), null, true ),
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
									<strong><?php echo esc_html( $filter_field['label'] ); ?>:</strong>
									<?php echo esc_html( $value_label ); ?>
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
			jQuery( function () {
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

	public function filters_popup() {

		$filters = $this->filters;
		?>
		<div id="pods-ui-posts-filter-popup" class="pods-hidden">
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
						'_wpnonce' . $this->num,
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
						} elseif ( isset( $this->fields['manage'][ $filter ] ) ) {
							$filter_field = $this->fields['manage'][ $filter ];
						} else {
							continue;
						}
						?>
						<p class="pods-ui-posts-filter-toggled pods-ui-posts-filter-<?php echo esc_attr( $filter . ( $zebra ? ' clear' : '' ) ); ?>">
							<?php
							if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
								$start = pods_var_raw( 'filter_' . $filter . '_start', 'get', pods_var_raw( 'filter_default', $filter_field, '', null, true ), null, true );
								$end   = pods_var_raw( 'filter_' . $filter . '_end', 'get', pods_var_raw( 'filter_ongoing_default', $filter_field, '', null, true ), null, true );

								// override default value
								$filter_field['options']['default_value']                          = '';
								$filter_field['options'][ $filter_field['type'] . '_allow_empty' ] = 1;

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
								<span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( ( empty( $start ) && empty( $end ) ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( ( empty( $start ) && empty( $end ) ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
									<?php echo esc_html( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php echo esc_attr( ( empty( $start ) && empty( $end ) ) ? ' pods-hidden' : '' ); ?>">
								<?php echo PodsForm::field( 'filter_' . $filter . '_start', $start, $filter_field['type'], $filter_field ); ?>

									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">to</label>
									<?php echo PodsForm::field( 'filter_' . $filter . '_end', $end, $filter_field['type'], $filter_field ); ?>
							</span>
								<?php
							} elseif ( 'pick' === $filter_field['type'] ) {
								$value = pods_var_raw( 'filter_' . $filter, 'get', '' );

								if ( strlen( $value ) < 1 ) {
									$value = pods_var_raw( 'filter_default', $filter_field );
								}

								// override default value
								$filter_field['options']['default_value'] = '';

								$filter_field['options']['pick_format_type']   = 'single';
								$filter_field['options']['pick_format_single'] = 'dropdown';

								$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields['search'], array(), null, true ), array(), null, true ), '', null, true );
								$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', $filter_field['options'], $filter_field['options']['input_helper'], null, true );

								$options = array_merge( $filter_field, $filter_field['options'] );
								?>
								<span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php echo esc_html( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php echo esc_attr( strlen( $value ) < 1 ? ' pods-hidden' : '' ); ?>">
								<?php echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options ); ?>
							</span>
								<?php
							} elseif ( 'boolean' === $filter_field['type'] ) {
								$value = pods_var_raw( 'filter_' . $filter, 'get', '' );

								if ( strlen( $value ) < 1 ) {
									$value = pods_var_raw( 'filter_default', $filter_field );
								}

								// override default value
								$filter_field['options']['default_value'] = '';

								$filter_field['options']['pick_format_type']   = 'single';
								$filter_field['options']['pick_format_single'] = 'dropdown';

								$filter_field['options']['pick_object'] = 'custom-simple';
								$filter_field['options']['pick_custom'] = array(
									'1' => pods_var_raw( 'boolean_yes_label', $filter_field['options'], __( 'Yes', 'pods' ), null, true ),
									'0' => pods_var_raw( 'boolean_no_label', $filter_field['options'], __( 'No', 'pods' ), null, true ),
								);

								$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields['search'], array(), null, true ), array(), null, true ), '', null, true );
								$filter_field['options']['input_helper'] = pods_var_raw( 'ui_input_helper', $filter_field['options'], $filter_field['options']['input_helper'], null, true );

								$options = array_merge( $filter_field, $filter_field['options'] );
								?>
								<span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php echo esc_html( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php echo esc_attr( strlen( $value ) < 1 ? ' pods-hidden' : '' ); ?>">
								<?php echo PodsForm::field( 'filter_' . $filter, $value, 'pick', $options ); ?>
							</span>
								<?php
							} else {
								$value = pods_var_raw( 'filter_' . $filter );

								if ( strlen( $value ) < 1 ) {
									$value = pods_var_raw( 'filter_default', $filter_field );
								}

								$options = array(
									'input_helper' => pods_var_raw( 'ui_input_helper', pods_var_raw( 'options', pods_var_raw( $filter, $this->fields['search'], array(), null, true ), array(), null, true ), '', null, true ),
								);

								if ( empty( $options['input_helper'] ) && isset( $filter_field['options'] ) && isset( $filter_field['options']['input_helper'] ) ) {
									$options['input_helper'] = $filter_field['options']['input_helper'];
								}
								?>
								<span class="pods-ui-posts-filter-toggle toggle-on<?php echo esc_attr( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
								<span class="pods-ui-posts-filter-toggle toggle-off<?php echo esc_attr( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

								<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php echo esc_html( $filter_field['label'] ); ?>
								</label>

								<span class="pods-ui-posts-filter<?php echo esc_attr( empty( $value ) ? ' pods-hidden' : '' ); ?>">
								<?php echo PodsForm::field( 'filter_' . $filter, $value, 'text', $options ); ?>
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
						<label for="pods-form-ui-search<?php echo esc_attr( $this->num ); ?>"><?php _e( 'Search Text', 'pods' ); ?></label>
						<?php echo PodsForm::field( 'search' . $this->num, pods_var_raw( 'search' . $this->num ), 'text' ); ?>
					</p>

					<?php $zebra = empty( $zebra ); ?>
				</div>

				<p class="submit<?php echo esc_attr( $zebra ? ' clear' : '' ); ?>">
					<input type="submit" value="<?php echo esc_attr( $this->header['search'] ); ?>" class="button button-primary" />
				</p>
			</form>
		</div>

		<script type="text/javascript">
			jQuery( function () {
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
						'action' . $this->num => 'reorder',
						'do' . $this->num     => 'save',
						'page'                => pods_var_raw( 'page' ),
					), self::$allowed, $this->exclusion()
				)
			);
		?>
		" method="post" class="admin_ui_reorder_form">
					<?php
		}//end if
			$table_fields = $this->fields['manage'];
		if ( true === $reorder && ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] ) {
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

							if ( $fields[ $field ]['sortable'] ) {
								$column_classes[] = 'sortable' . $current_sort;
								?>
								<th scope="col"<?php echo $att_id; ?> class="<?php echo esc_attr( implode( ' ', $column_classes ) ); ?>"<?php echo $width; ?>>
									<a href="
									<?php
									echo esc_url_raw(
										pods_query_arg(
											array(
												'orderby' . $this->num => $field,
												'orderby_dir' . $this->num => $dir,
											), array(
												'limit' . $this->num,
												'search' . $this->num,
												'pg' . $this->num,
												'page',
											), $this->exclusion()
										)
									);
									?>
									">
										<span><?php echo esc_html( $attributes['label'] ); ?></span>
										<span class="sorting-indicator"></span> </a>
								</th>
								<?php
							} else {
								?>
								<th scope="col"<?php echo $att_id; ?> class="<?php echo esc_attr( implode( ' ', $column_classes ) ); ?>"<?php echo $width; ?>><?php echo esc_html( $attributes['label'] ); ?></th>
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

								if ( $fields[ $field ]['sortable'] ) {
									?>
									<th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?> sortable <?php echo esc_attr( $current_sort ); ?>"<?php echo $width; ?>>
										<a href="
										<?php
										echo esc_url_raw(
											pods_query_arg(
												array(
													'orderby' . $this->num     => $field,
													'orderby_dir' . $this->num => $dir,
												), array(
													'limit' . $this->num,
													'search' . $this->num,
													'pg' . $this->num,
													'page',
												), $this->exclusion()
											)
										);
										?>
										"><span><?php echo esc_html( $attributes['label'] ); ?></span><span class="sorting-indicator"></span></a>
									</th>
									<?php
								} else {
									?>
									<th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?>"<?php echo $width; ?>><?php echo esc_html( $attributes['label'] ); ?></th>
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

						if ( is_array( $this->actions_custom ) && isset( $this->actions_custom['toggle'] ) ) {
							$toggle_class = ' pods-toggled-on';

							if ( ! isset( $row['toggle'] ) || empty( $row['toggle'] ) ) {
								$toggle_class = ' pods-toggled-off';
							}
						}
						?>
						<tr id="item-<?php echo esc_attr( $row[ $this->sql['field_id'] ] ); ?>" class="iedit<?php echo esc_attr( $toggle_class ); ?>">
							<?php
							if ( ! empty( $this->actions_bulk ) ) {
								?>
								<th scope="row" class="check-column">
									<input type="checkbox" name="action_bulk_ids<?php echo esc_attr( $this->num ); ?>[]" value="<?php echo esc_attr( $row[ $this->sql['field_id'] ] ); ?>">
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

								if ( ! isset( $row[ $field ] ) ) {
									$row[ $field ] = $this->get_field( $field );
								}

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
									} elseif ( is_object( $this->pod ) && class_exists( 'Pods_Helpers' ) ) {
										$row_value = $this->pod->helper( $attributes['custom_display'], $row_value, $field );
									}
								} else {
									ob_start();

									$field_value = PodsForm::field_method( $attributes['type'], 'ui', $this->id, $row_value, $field, array_merge( $attributes, pods_var_raw( 'options', $attributes, array(), null, true ) ), $fields, $this->pod );

									$field_output = trim( (string) ob_get_clean() );

									if ( false === $field_value ) {
										$row_value = '';
									} elseif ( 0 < strlen( trim( (string) $field_value ) ) ) {
										$row_value = trim( (string) $field_value );
									} elseif ( 0 < strlen( $field_output ) ) {
										$row_value = $field_output;
									}
								}//end if

								if ( false !== $attributes['custom_relate'] ) {
									global $wpdb;
									$table = $attributes['custom_relate'];
									$on    = $this->sql['field_id'];
									$is    = $row[ $this->sql['field_id'] ];
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
									$css_classes[] = 'post-title';
									$css_classes[] = 'page-title';
									$css_classes[] = 'column-title';

									if ( 'raw' !== $attributes['type'] ) {
										$row_value = wp_kses_post( $row_value );
									}

									if ( ! in_array( 'edit', $this->actions_disabled ) && ! in_array( 'edit', $this->actions_hidden ) && ( false === $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder['on'] ) && 'edit' === $default_action ) {
										$link = pods_query_arg(
											array(
												'action' . $this->num => 'edit',
												'id' . $this->num     => $row[ $this->sql['field_id'] ],
											), self::$allowed, $this->exclusion()
										);

										if ( ! empty( $this->action_links['edit'] ) ) {
											$link = $this->do_template( $this->action_links['edit'], $row );
										}
										?>
										<td class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
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
												'action' . $this->num => 'view',
												'id' . $this->num     => $row[ $this->sql['field_id'] ],
											), self::$allowed, $this->exclusion()
										);

										if ( ! empty( $this->action_links['view'] ) ) {
											$link = $this->do_template( $this->action_links['view'], $row );
										}
										?>
										<td class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
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
										<td class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
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
										$actions = $this->do_hook( 'row_actions', $actions, $row[ $this->sql['field_id'] ] );

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
										<input type="hidden" name="order[]" value="<?php echo esc_attr( $row[ $this->sql['field_id'] ] ); ?>" />
										<?php
									}//end if
									?>
									<button type="button" class="toggle-row">
										<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
									</button>
									</td>
									<?php
								} elseif ( 'date' === $attributes['type'] ) {
									if ( $first_field ) {
										$css_classes[] = 'column-primary';
									}
									$css_classes[] = 'date';
									$css_classes[] = 'column-date';
									?>
									<td class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" data-colname="<?php echo esc_attr( $attributes['label'] ); ?>">
										<abbr title="<?php echo esc_attr( $row_value ); ?>"><?php echo wp_kses_post( $row_value ); ?></abbr>
										<?php if ( $first_field ) { ?>
											<button type="button" class="toggle-row">
											<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
											</button><?php } ?>
									</td>
									<?php
								} else {
									if ( $first_field ) {
										$css_classes[] = 'column-primary';
									}

									$css_classes[] = 'author';

									if ( 'raw' !== $attributes['type'] ) {
										$row_value = wp_kses_post( $row_value );
									}
									?>
									<td class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" data-colname="<?php echo esc_attr( $attributes['label'] ); ?>">
										<span>
										<?php
										/* Escaped above for non-HTML types */
											echo $row_value;
											?>
											</span>
										<?php if ( $first_field ) { ?>
											<button type="button" class="toggle-row">
											<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
											</button><?php } ?>
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
			jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
			<?php
			if ( true === $reorder && ! in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder['on'] ) {
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
	 * Get actions for row.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function get_actions( $row ) {

		$actions = array();

		if ( ! in_array( 'view', $this->actions_disabled ) && ! in_array( 'view', $this->actions_hidden ) ) {
			$link = pods_query_arg(
				array(
					'action' . $this->num => 'view',
					'id' . $this->num     => $row[ $this->sql['field_id'] ],
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
					'action' . $this->num => 'edit',
					'id' . $this->num     => $row[ $this->sql['field_id'] ],
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
					'action' . $this->num => 'duplicate',
					'id' . $this->num     => $row[ $this->sql['field_id'] ],
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
					'action' . $this->num => 'delete',
					'id' . $this->num     => $row[ $this->sql['field_id'] ],
					'_wpnonce'            => wp_create_nonce( 'pods-ui-action-delete' ),
				), self::$allowed, $this->exclusion()
			);

			if ( ! empty( $this->action_links['delete'] ) ) {
				$link = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'pods-ui-action-delete' ) ), $this->do_template( $this->action_links['delete'], $row ) );
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
								'action'   => $custom_action,
								'id'       => $row[ $this->sql['field_id'] ],
								'_wpnonce' => wp_create_nonce( 'pods-ui-action-' . $custom_action ),
							);

							if ( 'toggle' === $custom_action ) {
								$vars['toggle']  = (int) ( ! $row['toggle'] );
								$vars['toggled'] = 1;
							}

							$custom_data['link'] = pods_query_arg( $vars, self::$allowed, $this->exclusion() );

							if ( isset( $this->action_links[ $custom_action ] ) && ! empty( $this->action_links[ $custom_action ] ) ) {
								$custom_data['link'] = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'pods-ui-action-' . $custom_action ) ), $this->do_template( $this->action_links[ $custom_action ], $row ) );
							}
						}

						$confirm = '';

						if ( isset( $custom_data['confirm'] ) ) {
							$confirm = ' onclick="if(confirm(\'' . esc_js( $custom_data['confirm'] ) . '\')){return true;}return false;"';
						}

						if ( $this->restricted( $custom_action, $row ) ) {
							continue;
						}

						$actions[ $custom_action ] = '<span class="edit action-' . esc_attr( $custom_action ) . '"><a href="' . esc_url( $this->do_template( $custom_data['link'], $row ) ) . '" title="' . esc_attr( $custom_data['label'] ) . ' this item"' . $confirm . '>' . $custom_data['label'] . '</a></span>';
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
												<input class="hide-column-tog" name="<?php echo esc_attr( $this->unique_identifier ); ?>_<?php echo esc_attr( $field ); ?>-hide" type="checkbox" id="<?php echo esc_attr( $field ); ?>-hide" value="<?php echo esc_attr( $field ); ?>" checked="checked"><?php echo esc_html( $attributes['label'] ); ?>
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
										<label for="<?php echo esc_attr( $this->unique_identifier ); ?>_per_page"><?php echo esc_html( sprintf( __( '%s per page', 'pods' ), $this->items ) ); ?></label>
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

		$total_pages = ceil( $this->total_found / $this->limit );
		$request_uri = pods_query_arg(
			array( 'pg' . $this->num => '' ), array(
				'limit' . $this->num,
				'orderby' . $this->num,
				'orderby_dir' . $this->num,
				'search' . $this->num,
				'filter_*',
				'view' . $this->num,
				'page' . $this->num,
				'post_type',
				'taxonomy',
				'action' . $this->num,
			), $this->exclusion()
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

		if ( false !== $this->pagination ) {
			if ( 1 < $total_pages ) {
				$first_link = esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=1' );
				$prev_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=' . max( $this->page - 1, 1 ) );
				$next_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=' . min( $this->page + 1, $total_pages ) );
				$last_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . 'pg' . $this->num . '=' . $total_pages );

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
					<span class="paging-input"><input class="current-page" title="<?php esc_attr_e( 'Current page', 'pods' ); ?>" type="text" name="pg<?php echo esc_attr( $this->num ); ?>" value="<?php echo esc_attr( absint( $this->page ) ); ?>" size="<?php echo esc_attr( strlen( $total_pages ) ); ?>"> <?php _e( 'of', 'pods' ); ?>
						<span class="total-pages"><?php echo absint( $total_pages ); ?></span></span>
					<script>

						jQuery( document ).ready( function ( $ ) {
							var pageInput = $( 'input.current-page' );
							var currentPage = pageInput.val();
							pageInput.closest( 'form' ).submit( function ( e ) {
								if ( ( 1 > $( 'select[name="action<?php echo esc_attr( $this->num ); ?>"]' ).length || $( 'select[name="action<?php echo esc_attr( $this->num ); ?>"]' ).val() == -1 ) && ( 1 > $( 'select[name="action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).length || $( 'select[name="action_bulk<?php echo esc_attr( $this->num ); ?>"]' ).val() == -1 ) && pageInput.val() == currentPage ) {
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
							'orderby' . $this->num,
							'orderby_dir' . $this->num,
							'search' . $this->num,
							'filter_*',
							'page' . $this->num,
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
			if ( in_array( $k, $exclude ) ) {
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
			$exclusion[ $k ] = $exclude . $this->num;
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
			$relation = strtoupper( trim( pods_v( 'relation', $restrict, 'AND', null, true ) ) );

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

					$match_relation = strtoupper( trim( pods_v( 'relation', $match, 'OR', null, true ) ) );

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
