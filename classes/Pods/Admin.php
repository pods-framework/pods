<?php
/**
 * @package Pods
 * @category Admin
 */
class Pods_Admin {

	/**
	 * @var Pods_Admin
	 */
	static $instance = null;

	/**
	 * @var bool|Pods_Object_Pod
	 */
	static $admin_row = false;

	/**
	 * Singleton handling for a basic pods_admin() request
	 *
	 * @return \Pods_Admin
	 *
	 * @since 2.3.5
	 */
	public static function init() {

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new Pods_Admin();
		}

		return self::$instance;

	}

	/**
	 * Setup and Handle Admin functionality
	 *
	 * @return \Pods_Admin
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since   2.0
	 */
	public function __construct() {

		// Scripts / Stylesheets
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ), 20 );

		// AJAX $_POST fix
		add_action( 'admin_init', array( $this, 'admin_init' ), 9 );

		// Menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );

		// AJAX for Admin
		add_action( 'wp_ajax_pods_admin', array( $this, 'admin_ajax' ) );
		add_action( 'wp_ajax_nopriv_pods_admin', array( $this, 'admin_ajax' ) );

		// Add Media Bar button for Shortcode
		add_action( 'media_buttons', array( $this, 'media_button' ), 12 );

		// Add the Pods capabilities
		add_filter( 'members_get_capabilities', array( $this, 'admin_capabilities' ) );

	}

	/**
	 * Init the admin area
	 *
	 * @since 2.0
	 */
	public function admin_init() {

		// Fix for plugins that *don't do it right* so we don't cause issues for users
		if ( defined( 'DOING_AJAX' ) && ! empty( $_POST ) ) {
			$pods_admin_ajax_actions = array(
				'pods_admin',
				'pods_relationship',
				'pods_upload',
				'pods_admin_components'
			);

			/**
			 * Fire off the Pods Admin Ajax
			 *
			 * @param array $pods_admin_ajax_actions The Ajax actions that will occur in the WordPress Admin
			 *
			 * @since 2.0
			 */
			$pods_admin_ajax_actions = apply_filters( 'pods_admin_ajax_actions', $pods_admin_ajax_actions );

			if ( in_array( pods_v( 'action' ), $pods_admin_ajax_actions ) || in_array( pods_v( 'action', 'post' ), $pods_admin_ajax_actions ) ) {
				foreach ( $_POST as $key => $value ) {
					if ( 'action' == $key || 0 === strpos( $key, '_podsfix_' ) ) {
						continue;
					}

					unset( $_POST[ $key ] );

					$_POST[ '_podsfix_' . $key ] = $value;
				}
			}
		}

	}

	/**
	 * Attach requirements to admin header
	 *
	 * @since 2.0
	 */
	public function admin_head() {

		wp_register_style( 'pods-admin', PODS_URL . 'ui/css/pods-admin.css', array(), PODS_VERSION );

		wp_register_style( 'pods-font', PODS_URL . 'ui/css/pods-font.css', array(), PODS_VERSION );

		wp_register_script( 'pods-floatmenu', PODS_URL . 'ui/js/pods-floatmenu.js', array(), PODS_VERSION );

		wp_register_style( 'pods-manage', PODS_URL . 'ui/css/pods-manage.css', array(), PODS_VERSION );

		wp_register_style( 'pods-wizard', PODS_URL . 'ui/css/pods-wizard.css', array(), PODS_VERSION );

		wp_register_script( 'pods-upgrade', PODS_URL . 'ui/js/jquery-pods-upgrade.js', array(), PODS_VERSION );

		wp_register_script( 'pods-migrate', PODS_URL . 'ui/js/jquery-pods-migrate.js', array(), PODS_VERSION );

		if ( isset( $_GET[ 'page' ] ) ) {
			$page = $_GET[ 'page' ];
			if ( 'pods' == $page || ( false !== strpos( $page, 'pods-' ) && 0 === strpos( $page, 'pods-' ) ) ) {
				?>
				<script type="text/javascript">
					var PODS_URL = "<?php echo esc_js( PODS_URL ); ?>";
				</script>
				<?php
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-sortable' );

				wp_enqueue_style( 'jquery-ui' );

				wp_enqueue_script( 'pods-floatmenu' );

				wp_enqueue_style( 'jquery-qtip2' );
				wp_enqueue_script( 'jquery-qtip2' );
				wp_enqueue_script( 'pods-qtip-init' );

				wp_enqueue_script( 'pods' );

				if ( 0 === strpos( $page, 'pods-manage-' ) || 0 === strpos( $page, 'pods-add-new-' ) ) {
					wp_enqueue_script( 'post' );
				} elseif ( 0 === strpos( $page, 'pods-settings-' ) ) {
					wp_enqueue_script( 'post' );
					wp_enqueue_style( 'pods-admin' );
				} else {
					wp_enqueue_style( 'pods-admin' );
				}

				if ( 'pods-packages' == $page ) {
					wp_enqueue_style( 'pods-wizard' );
				} elseif ( 'pods-wizard' == $page || 'pods-upgrade' == $page || ( in_array( $page, array(
							'pods',
							'pods-add-new'
						) ) && in_array( pods_v( 'action', 'get', 'manage' ), array( 'add', 'manage' ) ) )
				) {
					wp_enqueue_style( 'pods-wizard' );

					if ( 'pods-upgrade' == $page ) {
						wp_enqueue_script( 'pods-upgrade' );
					}
				}
			}
		}

		wp_enqueue_style( 'pods-font' );
	}

	/**
	 * Build the admin menus
	 *
	 * @since 2.0
	 */
	public function admin_menu() {

		// @todo Fix usage per #2586
		$advanced_content_types = Pods_Meta::$advanced_content_types;
		$taxonomies             = Pods_Meta::$taxonomies;
		$settings               = Pods_Meta::$settings;

		$all_pods = pods_api()->load_pods( array( 'count' => true ) );

		if ( ! Pods_Init::$upgrade_needed || ( pods_is_admin() && 1 == pods_v( 'pods_upgrade_bypass' ) ) ) {
			$submenu_items = array();

			if ( ! empty( $advanced_content_types ) ) {
				$submenu = array();

				$pods_pages = 0;

				foreach ( (array) $advanced_content_types as $pod ) {
					if ( ! pods_is_admin( array(
						'pods',
						'pods_content',
						'pods_add_' . $pod[ 'name' ],
						'pods_edit_' . $pod[ 'name' ],
						'pods_delete_' . $pod[ 'name' ]
					) )
					) {
						continue;
					}

					if ( 1 == pods_v( 'show_in_menu', $pod, 0 ) ) {
						$page_title = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );

						// @todo Needs hook doc
						$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

						$menu_label = pods_v( 'menu_name', $pod, '', true );

						// @todo Needs hook doc
						$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

						$singular_label = pods_v( 'label_singular', $pod, pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), true ), true );
						$plural_label   = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), true );

						$menu_location        = pods_v( 'menu_location', $pod, 'objects' );
						$menu_location_custom = pods_v( 'menu_location_custom', $pod, '' );

						$menu_position = pods_v( 'menu_position', $pod, '', true );
						$menu_icon     = pods_evaluate_tags( pods_v( 'menu_icon', $pod, '', true ), true );

						if ( empty( $menu_position ) ) {
							$menu_position = null;
						}

						$parent_page = null;

						if ( pods_is_admin( array(
							'pods',
							'pods_content',
							'pods_edit_' . $pod[ 'name' ],
							'pods_delete_' . $pod[ 'name' ]
						) ) ) {
							if ( ! empty( $menu_location_custom ) ) {
								if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
									$submenu_items[ $menu_location_custom ] = array();
								}

								$submenu_items[ $menu_location_custom ][] = array(
									$menu_location_custom,
									$page_title,
									$menu_label,
									'read',
									'pods-manage-' . $pod[ 'name' ],
									array( $this, 'admin_content' )
								);

								continue;
							} else {
								$pods_pages ++;

								$parent_page = $page = 'pods-manage-' . $pod[ 'name' ];

								if ( empty( $menu_position ) ) {
									$menu_position = null;
								}
								add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );

								$all_title = $plural_label;
								$all_label = __( 'All', 'pods' ) . ' ' . $plural_label;

								if ( $page == pods_v( 'page', 'get' ) ) {
									if ( 'edit' == pods_v( 'action', 'get', 'manage' ) ) {
										$all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
									} elseif ( 'add' == pods_v( 'action', 'get', 'manage' ) ) {
										$all_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
									}
								}

								add_submenu_page( $parent_page, $all_title, $all_label, 'read', $page, array(
									$this,
									'admin_content'
								) );
							}
						}

						if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod[ 'name' ] ) ) ) {
							$page = 'pods-add-new-' . $pod[ 'name' ];

							if ( null === $parent_page ) {
								$pods_pages ++;

								$parent_page = $page;

								if ( empty( $menu_position ) ) {
									$menu_position = null;
								}
								add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );
							}

							$add_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
							$add_label = __( 'Add New', 'pods' );

							add_submenu_page( $parent_page, $add_title, $add_label, 'read', $page, array(
								$this,
								'admin_content'
							) );
						}
					} else {
						$submenu[] = $pod;
					}
				}

				$submenu = apply_filters( 'pods_admin_menu_secondary_content', $submenu );

				if ( ! empty( $submenu ) && ( ! defined( 'PODS_DISABLE_CONTENT_MENU' ) || ! PODS_DISABLE_CONTENT_MENU ) ) {
					$parent_page = null;

					foreach ( $submenu as $item ) {
						$singular_label = pods_v( 'label_singular', $item, pods_v( 'label', $item, ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), true ), true );
						$plural_label   = pods_v( 'label', $item, ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), true );

						if ( pods_is_admin( array(
							'pods',
							'pods_content',
							'pods_edit_' . $item[ 'name' ],
							'pods_delete_' . $item[ 'name' ]
						) ) ) {
							$page = 'pods-manage-' . $item[ 'name' ];

							if ( null === $parent_page ) {
								$parent_page = $page;

								add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, 'dashicons-pods', '58.5' );
							}

							$all_title = $plural_label;
							$all_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

							if ( $page == pods_v( 'page', 'get' ) ) {
								if ( 'edit' == pods_v( 'action', 'get', 'manage' ) ) {
									$all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
								} elseif ( 'add' == pods_v( 'action', 'get', 'manage' ) ) {
									$all_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
								}
							}

							add_submenu_page( $parent_page, $all_title, $all_label, 'read', $page, array(
								$this,
								'admin_content'
							) );
						} elseif ( current_user_can( 'pods_add_' . $item[ 'name' ] ) ) {
							$page = 'pods-add-new-' . $item[ 'name' ];

							if ( null === $parent_page ) {
								$parent_page = $page;

								add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, 'dashicons-pods', '58.5' );
							}

							$add_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
							$add_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

							add_submenu_page( $parent_page, $add_title, $add_label, 'read', $page, array(
								$this,
								'admin_content'
							) );
						}
					}
				}
			}

			if ( ! empty( $taxonomies ) ) {
				foreach ( (array) $taxonomies as $pod ) {
					if ( ! pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod[ 'name' ] ) ) ) {
						continue;
					}

					$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), true );

					// @todo Needs hook doc
					$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

					$menu_label = pods_v( 'menu_name', $pod, '', true );

					// @todo Needs hook doc
					$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

					$menu_position = pods_v( 'menu_position', $pod, '', true );
					$menu_icon     = pods_evaluate_tags( pods_v( 'menu_icon', $pod, '', true ), true );

					if ( empty( $menu_position ) ) {
						$menu_position = null;
					}

					$menu_slug            = 'edit-tags.php?taxonomy=' . $pod[ 'name' ];
					$menu_location        = pods_v( 'menu_location', $pod, 'default' );
					$menu_location_custom = pods_v( 'menu_location_custom', $pod, '' );

					if ( 'default' == $menu_location ) {
						continue;
					}

					$taxonomy_data = get_taxonomy( $pod[ 'name' ] );

					foreach ( (array) $taxonomy_data->object_type as $post_type ) {
						if ( 'post' == $post_type ) {
							remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=' . $pod[ 'name' ] );
						} elseif ( 'attachment' == $post_type ) {
							remove_submenu_page( 'upload.php', 'edit-tags.php?taxonomy=' . $pod[ 'name' ] . '&amp;post_type=' . $post_type );
						} else {
							remove_submenu_page( 'edit.php?post_type=' . $post_type, 'edit-tags.php?taxonomy=' . $pod[ 'name' ] . '&amp;post_type=' . $post_type );
						}
					}

					if ( 'settings' == $menu_location ) {
						add_options_page( $page_title, $menu_label, 'read', $menu_slug );
					} elseif ( 'appearances' == $menu_location ) {
						add_theme_page( $page_title, $menu_label, 'read', $menu_slug );
					} elseif ( 'objects' == $menu_location ) {
						if ( empty( $menu_position ) ) {
							$menu_position = null;
						}
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, '', $menu_icon, $menu_position );
					} elseif ( 'top' == $menu_location ) {
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, '', $menu_icon, $menu_position );
					} elseif ( 'submenu' == $menu_location && ! empty( $menu_location_custom ) ) {
						if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
							$submenu_items[ $menu_location_custom ] = array();
						}

						$submenu_items[ $menu_location_custom ][] = array(
							$menu_location_custom,
							$page_title,
							$menu_label,
							'read',
							$menu_slug,
							''
						);
					}
				}
			}

			if ( ! empty( $settings ) ) {
				foreach ( (array) $settings as $pod ) {
					if ( ! pods_is_admin( array(
							'pods',
							'pods_content',
							'pods_edit_' . $pod[ 'name' ]
						) ) || ! pods_permission( $pod )
					) {
						continue;
					}

					$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), true );

					// @todo Needs hook doc
					$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

					$menu_label = pods_v( 'menu_name', $pod, $page_title, true );

					// @todo Needs hook doc
					$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

					$menu_position = pods_v( 'menu_position', $pod, '', true );
					$menu_icon     = pods_evaluate_tags( pods_v( 'menu_icon', $pod, '', true ), true );

					if ( empty( $menu_position ) ) {
						$menu_position = null;
					}

					$menu_slug            = 'pods-settings-' . $pod[ 'name' ];
					$menu_location        = pods_v( 'menu_location', $pod, 'settings' );
					$menu_location_custom = pods_v( 'menu_location_custom', $pod, '' );

					if ( 'settings' == $menu_location ) {
						add_options_page( $page_title, $menu_label, 'read', $menu_slug, array(
							$this,
							'admin_content_settings'
						) );
					} elseif ( 'appearances' == $menu_location ) {
						add_theme_page( $page_title, $menu_label, 'read', $menu_slug, array(
							$this,
							'admin_content_settings'
						) );
					} elseif ( 'objects' == $menu_location ) {
						if ( empty( $menu_position ) ) {
							$menu_position = null;
						}
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, array(
							$this,
							'admin_content_settings'
						), $menu_icon, $menu_position );
					} elseif ( 'top' == $menu_location ) {
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, array(
							$this,
							'admin_content_settings'
						), $menu_icon, $menu_position );
					} elseif ( 'submenu' == $menu_location && ! empty( $menu_location_custom ) ) {
						if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
							$submenu_items[ $menu_location_custom ] = array();
						}

						$submenu_items[ $menu_location_custom ][] = array(
							$menu_location_custom,
							$page_title,
							$menu_label,
							'read',
							$menu_slug,
							array( $this, 'admin_content_settings' )
						);
					}
				}
			}

			foreach ( $submenu_items as $items ) {
				foreach ( $items as $item ) {
					call_user_func_array( 'add_submenu_page', $item );
				}
			}

			$admin_menus = array(
				'pods'            => array(
					'label'    => __( 'Edit Pods', 'pods' ),
					'function' => array( $this, 'admin_setup' ),
					'access'   => 'pods'
				),
				'pods-add-new'    => array(
					'label'    => __( 'Add New', 'pods' ),
					'function' => array( $this, 'admin_setup' ),
					'access'   => 'pods'
				),
				'pods-components' => array(
					'label'    => __( 'Components', 'pods' ),
					'function' => array( $this, 'admin_components' ),
					'access'   => 'pods_components'
				),
				'pods-settings'   => array(
					'label'    => __( 'Settings', 'pods' ),
					'function' => array( $this, 'admin_settings' ),
					'access'   => 'pods_settings'
				),
				'pods-help'       => array(
					'label'    => __( 'Help', 'pods' ),
					'function' => array( $this, 'admin_help' )
				)
			);

			if ( empty( $all_pods ) ) {
				unset( $admin_menus[ 'pods' ] );
			} elseif ( 'pods' == pods_v( 'page' ) ) {
				$admin_menus[ 'pods' ][ 'title' ] = __( 'Edit Pod', 'pods' );

				if ( 'add' == pods_v( 'action_group' ) ) {
					$admin_menus[ 'pods' ][ 'title' ] = __( 'Add Field Group', 'pods' );
				} elseif ( 'edit' == pods_v( 'action_group' ) ) {
					$admin_menus[ 'pods' ][ 'title' ] = __( 'Edit Field Group', 'pods' );
				}
			}

			add_filter( 'parent_file', array( $this, 'parent_file' ) );
		} else {
			$admin_menus = array(
				'pods-upgrade'  => array(
					'label'    => __( 'Upgrade', 'pods' ),
					'function' => array( $this, 'admin_upgrade' ),
					'access'   => 'manage_options'
				),
				'pods-settings' => array(
					'label'    => __( 'Settings', 'pods' ),
					'function' => array( $this, 'admin_settings' ),
					'access'   => 'pods_settings'
				),
				'pods-help'     => array(
					'label'    => __( 'Help', 'pods' ),
					'function' => array( $this, 'admin_help' )
				)
			);

			add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
		}

		/**
		 * Add or change Pods Admin menu items
		 *
		 * @params array $admin_menus The submenu items in Pods Admin menu.
		 *
		 * @since  unknown
		 */
		$admin_menus = apply_filters( 'pods_admin_menu', $admin_menus );

		$parent = false;

		if ( ! empty( $admin_menus ) && ( ! defined( 'PODS_DISABLE_ADMIN_MENU' ) || ! PODS_DISABLE_ADMIN_MENU ) ) {
			foreach ( $admin_menus as $page => $menu_item ) {
				if ( ! pods_is_admin( pods_v( 'access', $menu_item ) ) ) {
					continue;
				}

				// Don't just show the help page
				if ( false === $parent && 'pods-help' == $page ) {
					continue;
				}

				if ( ! isset( $menu_item[ 'label' ] ) ) {
					$menu_item[ 'label' ] = $page;
				}

				if ( false === $parent ) {
					$parent = $page;

					$menu_title = __( 'Pods Admin', 'pods' );

					if ( 'pods-upgrade' == $parent ) {
						$menu_title = __( 'Pods Upgrade', 'pods' );
					}

					add_menu_page( $menu_title, $menu_title, 'read', $parent, null, 'dashicons-pods' );
				}

				$menu_title = $page_title = $menu_item[ 'label' ];

				if ( isset( $menu_item[ 'title' ] ) ) {
					$page_title = $menu_item[ 'title' ];
				}

				add_submenu_page( $parent, $page_title, $menu_title, 'read', $page, $menu_item[ 'function' ] );

				if ( 'pods-components' == $page ) {
					Pods_Init::$components->menu( $parent );
				}
			}
		}

	}

	/**
	 * Set the correct parent_file to highlight the correct top level menu
	 *
	 * @param string $parent_file The parent file
	 *
	 * @return mixed|string
	 *
	 * @since unknown
	 */
	public function parent_file( $parent_file ) {

		global $current_screen;

		if ( isset( $current_screen ) && ! empty( $current_screen->taxonomy ) ) {
			$taxonomies = Pods_Meta::$taxonomies;

			if ( ! empty( $taxonomies ) ) {
				foreach ( (array) $taxonomies as $pod ) {
					if ( $current_screen->taxonomy !== $pod[ 'name' ] ) {
						continue;
					}

					$menu_slug            = 'edit-tags.php?taxonomy=' . $pod[ 'name' ];
					$menu_location        = pods_v( 'menu_location', $pod, 'default' );
					$menu_location_custom = pods_v( 'menu_location_custom', $pod, '' );

					if ( 'settings' == $menu_location ) {
						$parent_file = 'options-general.php';
					} elseif ( 'appearances' == $menu_location ) {
						$parent_file = 'themes.php';
					} elseif ( 'objects' == $menu_location ) {
						$parent_file = $menu_slug;
					} elseif ( 'top' == $menu_location ) {
						$parent_file = $menu_slug;
					} elseif ( 'submenu' == $menu_location && ! empty( $menu_location_custom ) ) {
						$parent_file = $menu_location_custom;
					}

					break;
				}
			}
		}

		if ( isset( $current_screen ) && ! empty( $current_screen->post_type ) ) {
			global $submenu_file;

			$components = Pods_Init::$components->components;

			foreach ( $components as $component => $component_data ) {
				if ( ! empty( $component_data[ 'MenuPage' ] ) && $parent_file === $component_data[ 'MenuPage' ] ) {
					$parent_file  = 'pods';
					$submenu_file = $component_data[ 'MenuPage' ];
				}
			}
		}

		return $parent_file;

	}

	/**
	 * Show upgrade notice
	 */
	public function upgrade_notice() {

		echo '<div class="error fade"><p>';

		echo sprintf(
			__( '<strong>NOTICE:</strong> Pods %s requires your action to complete the upgrade. Please run the <a href="%s">Upgrade Wizard</a>.', 'pods' ),
			esc_html( PODS_VERSION ),
			esc_url( admin_url( 'admin.php?page=pods-upgrade' ) )
		);

		echo '</p></div>';

	}

	/**
	 * Create Pods_UI content for the administration pages
	 */
	public function admin_content() {

		global $pods;

		$pod_name = str_replace( array( 'pods-manage-', 'pods-add-new-' ), '', $_GET[ 'page' ] );

		$pods = pods( $pod_name, pods_v_sanitized( 'id', 'get', null, true ) );

		if ( false !== strpos( $_GET[ 'page' ], 'pods-add-new-' ) ) {
			$_GET[ 'action' ] = pods_v_sanitized( 'action', 'get', 'add' );
		}

		$pods->ui();

	}

	/**
	 * Create Pods_UI content for the settings administration pages
	 */
	public function admin_content_settings() {

		global $pods;

		$pod_name = str_replace( 'pods-settings-', '', $_GET[ 'page' ] );

		$pods = pods( $pod_name );

		if ( 'custom' != pods_v( 'ui_style', $pods->pod_data, 'settings', true ) ) {
			$actions_disabled = array(
				'manage'    => 'manage',
				'add'       => 'add',
				'delete'    => 'delete',
				'duplicate' => 'duplicate',
				'view'      => 'view',
				'export'    => 'export'
			);

			$_GET[ 'action' ] = 'edit';

			$page_title = pods_v( 'label', $pods->pod_data, ucwords( str_replace( '_', ' ', $pods->pod_data[ 'name' ] ) ), true );

			$ui = array(
				'pod'              => $pods,
				'fields'           => array(
					'edit' => $pods->pod_data[ 'fields' ]
				),
				'header'           => array(
					'edit' => $page_title
				),
				'label'            => array(
					'edit' => __( 'Save Changes', 'pods' )
				),
				'style'            => pods_v( 'ui_style', $pods->pod_data, 'settings', true ),
				'icon'             => pods_evaluate_tags( pods_v( 'menu_icon', $pods->pod_data ), true ),
				'actions_disabled' => $actions_disabled
			);

			// @todo Needs hook doc
			$ui = apply_filters( 'pods_admin_ui_' . $pods->pod, apply_filters( 'pods_admin_ui', $ui, $pods->pod, $pods ), $pods->pod, $pods );

			// Force disabled actions, do not pass go, do not collect $two_hundred
			$ui[ 'actions_disabled' ] = $actions_disabled;

			pods_ui( $ui );
		} else {
			// @todo Needs hook doc
			do_action( 'pods_admin_ui_custom', $pods );
			do_action( 'pods_admin_ui_custom_' . $pods->pod, $pods );
		}

	}

	/**
	 * Add media button for Pods shortcode
	 *
	 * @param $context
	 *
	 * @return string
	 */
	public function media_button( $context = null ) {

		// If shortcodes are disabled don't show the button
		if ( defined( 'PODS_DISABLE_SHORTCODE' ) && PODS_DISABLE_SHORTCODE ) {
			return '';
		}

		/**
		 * Filter to remove Pods shortcode button from the post editor.
		 *
		 * @param        bool . Set to false to block the shortcode button from appearing.
		 * @param string $context
		 *
		 * @since 2.3.19
		 */
		if ( ! apply_filters( 'pods_admin_media_button', true, $context ) ) {
			return '';
		}

		$current_page = basename( $_SERVER[ 'PHP_SELF' ] );
		$current_page = explode( '?', $current_page );
		$current_page = explode( '#', $current_page[ 0 ] );
		$current_page = $current_page[ 0 ];

		// Only show the button on post type pages
		if ( ! in_array( $current_page, array( 'post-new.php', 'post.php' ) ) ) {
			return '';
		}

		add_action( 'admin_footer', array( $this, 'mce_popup' ) );

		// @todo Does this need fixing?
		echo '<style>';
		echo '.pod-media-icon { background:url(' . PODS_URL . 'ui/images/icon16.png) no-repeat top left; display: inline-block; height: 16px; margin: 0 2px 0 0; vertical-align: text-top; width: 16px; }
			.wp-core-ui a.pods-media-button { padding-left: 0.4em; }';
		echo '</style>';

		echo '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox button pods-media-button" title="Embed Content"><span class="pod-media-icon"></span> Embed Content</a>';

		return '';

	}

	/**
	 * Output Pods shortcode popup window
	 */
	public function mce_popup() {

		pods_view( PODS_DIR . 'ui/admin/shortcode.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Handle main Pods Setup area for managing Pods and Fields
	 */
	public function admin_setup() {

		$all_pods = pods_api()->load_pods( array( 'output' => OBJECT ) );

		$view = pods_v( 'view', 'get', 'all', true );

		if ( empty( $all_pods ) && ! isset( $_GET[ 'action' ] ) ) {
			$_GET[ 'action' ] = 'add';
		}

		if ( 'pods-add-new' == $_GET[ 'page' ] ) {
			if ( isset( $_GET[ 'action' ] ) && 'add' != $_GET[ 'action' ] ) {
				pods_redirect( pods_query_arg( array( 'page' => 'pods', 'action' => $_GET[ 'action' ] ) ) );
			} else {
				$_GET[ 'action' ] = 'add';
			}
		} elseif ( isset( $_GET[ 'action' ] ) && 'add' == $_GET[ 'action' ] ) {
			pods_redirect( pods_query_arg( array( 'page' => 'pods-add-new', 'action' => '' ) ) );
		}

		$types = array(
			'post_type' => __( 'Post Type (extended)', 'pods' ),
			'taxonomy'  => __( 'Taxonomy (extended)', 'pods' ),
			'cpt'       => __( 'Custom Post Type', 'pods' ),
			'ct'        => __( 'Custom Taxonomy', 'pods' ),
			'user'      => __( 'User (extended)', 'pods' ),
			'media'     => __( 'Media (extended)', 'pods' ),
			'comment'   => __( 'Comments (extended)', 'pods' ),
			'pod'       => __( 'Advanced Content Type', 'pods' ),
			'settings'  => __( 'Custom Settings Page', 'pods' )
		);

		$row = false;

		$pod_types_found = array();

		$fields = array(
			'label'       => array( 'label' => __( 'Label', 'pods' ) ),
			'name'        => array( 'label' => __( 'Name', 'pods' ) ),
			'type'        => array( 'label' => __( 'Type', 'pods' ) ),
			'storage'     => array(
				'label' => __( 'Storage Type', 'pods' ),
				'width' => '10%'
			),
			'field_count' => array(
				'label' => __( 'Number of Fields', 'pods' ),
				'width' => '8%'
			)
		);

		$total_fields = 0;

		$data = array();

		/**
		 * @var $the_pod Pods_Object_Pod
		 */
		foreach ( $all_pods as $the_pod ) {
			$pod = array(
				'id'          => $the_pod->id,
				'label'       => $the_pod->label,
				'name'        => $the_pod->name,
				'object'      => $the_pod->object,
				'type'        => $the_pod->type,
				'real_type'   => $the_pod->type,
				'storage'     => $the_pod->storage,
				'field_count' => $the_pod->field_count()
			);

			if ( isset( $types[ $pod[ 'type' ] ] ) ) {
				if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) ) {
					if ( empty( $pod[ 'object' ] ) ) {
						if ( 'post_type' == $pod[ 'type' ] ) {
							$pod[ 'type' ] = 'cpt';
						} else {
							$pod[ 'type' ] = 'ct';
						}
					}
				}

				if ( ! isset( $pod_types_found[ $pod[ 'type' ] ] ) ) {
					$pod_types_found[ $pod[ 'type' ] ] = 1;
				} else {
					$pod_types_found[ $pod[ 'type' ] ] ++;
				}

				if ( 'all' != $view && $view != $pod[ 'type' ] ) {
					continue;
				}

				$pod[ 'type' ] = $types[ $pod[ 'type' ] ];
			} elseif ( 'all' != $view ) {
				continue;
			}

			$pod[ 'storage' ] = ucwords( $pod[ 'storage' ] );

			if ( $pod[ 'id' ] == pods_v( 'id' ) && 'delete' != pods_v( 'action' ) ) {
				$row = $pod;

				self::$admin_row = $the_pod;
			}

			$total_fields += $pod[ 'field_count' ];

			$data[ $pod[ 'id' ] ] = $pod;
		}

		if ( false === $row && 0 < (int) pods_v( 'id' ) && 'delete' != pods_v( 'action' ) ) {
			pods_message( 'Pod not found', 'error' );

			unset( $_GET[ 'id' ] );
			unset( $_GET[ 'action' ] );
		}

		$action_group = pods_v( 'action_group', 'get', 'manage' );

		if ( false !== $row && ! in_array( $action_group, array( 'manage', 'delete' ) ) ) {
			$this->admin_setup_groups();

			return;
		}

		$ui = array(
			'data'             => $data,
			'row'              => $row,
			'total'            => count( $data ),
			'total_found'      => count( $data ),
			'items'            => 'Pods',
			'item'             => 'Pod',
			'fields'           => array(
				'manage' => $fields
			),
			'actions_disabled' => array( 'view', 'export', 'bulk_delete' ),
			// Should bulk_delete be disabled? Does it not work?
			'actions_custom'   => array(
				'add'       => array( $this, 'admin_setup_add' ),
				'edit'      => array( $this, 'admin_setup_edit' ),
				'duplicate' => array(
					'callback'          => array( $this, 'admin_setup_duplicate' ),
					'restrict_callback' => array( $this, 'admin_setup_duplicate_restrict' )
				),
				'reset'     => array(
					'label'             => __( 'Delete All Items', 'pods' ),
					'confirm'           => __( 'Are you sure you want to delete all items from this Pod? If this is an extended Pod, it will remove the original items extended too.', 'pods' ),
					'callback'          => array( $this, 'admin_setup_reset' ),
					'restrict_callback' => array( $this, 'admin_setup_reset_restrict' ),
					'nonce'             => true
				),
				'delete'    => array( $this, 'admin_setup_delete' )
			),
			'action_links'     => array(
				'add' => pods_query_arg( array( 'page' => 'pods-add-new', 'action' => '', 'id' => '', 'do' => '' ) )
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => true,
			'pagination'       => false,
			'extra'            => array(
				'total' => ', ' . number_format_i18n( $total_fields ) . ' ' . _n( 'field', 'fields', $total_fields, 'pods' )
			)
		);

		if ( 1 < count( $pod_types_found ) ) {
			$ui[ 'views' ]            = array( 'all' => __( 'All', 'pods' ) );
			$ui[ 'view' ]             = $view;
			$ui[ 'heading' ]          = array( 'views' => __( 'Type', 'pods' ) );
			$ui[ 'filters_enhanced' ] = true;

			foreach ( $pod_types_found as $pod_type => $number_found ) {
				$ui[ 'views' ][ $pod_type ] = $types[ $pod_type ];
			}
		}

		pods_ui( $ui );

	}

	/**
	 * Get the add page of an object
	 *
	 * @param Pods_UI $obj
	 */
	public function admin_setup_add( $obj ) {

		pods_view( PODS_DIR . 'ui/admin/setup-add.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Get the edit page of an object
	 *
	 * @param bool $duplicate
	 * @param Pods_UI $obj
	 */
	public function admin_setup_edit( $duplicate, $obj ) {

		$pods_admin =& $this;

		pods_view( PODS_DIR . 'ui/admin/setup-edit.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Duplicate a pod
	 *
	 * @param Pods_UI $obj
	 *
	 * @return mixed
	 */
	public function admin_setup_duplicate( $obj ) {

		$new_id = pods_api()->duplicate_pod( array( 'id' => $obj->id ) );

		if ( 0 < $new_id ) {
			pods_redirect( pods_query_arg( array( 'action' => 'edit', 'id' => $new_id, 'do' => 'duplicate' ) ) );
		} else {
			pods_message( 'Pod could not be duplicated', 'error' );

			$obj->manage();
		}

	}

	/**
	 * Restrict Duplicate action to custom types, not extended
	 *
	 * @param bool $restricted
	 * @param array $restrict
	 * @param string $action
	 * @param array $row
	 * @param Pods_UI $obj
	 *
	 * @return bool
	 *
	 * @since 2.3.10
	 */
	public function admin_setup_duplicate_restrict( $restricted, $restrict, $action, $row, $obj ) {

		if ( in_array( $row[ 'real_type' ], array( 'user', 'media', 'comment' ) ) ) {
			$restricted = true;
		}

		return $restricted;

	}

	/**
	 * Reset a pod
	 *
	 * @param Pods_UI $obj
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function admin_setup_reset( $obj, $id ) {

		$pod = pods_api()->load_pod( array( 'id' => $id, 'output' => OBJECT ), false );

		if ( empty( $pod ) || ! $pod->is_valid() ) {
			return pods_message( __( 'Pod not found.', 'pods' ), 'error' );
		}

		$pod->reset();

		pods_message( __( 'Pod reset successfully.', 'pods' ) );

		$obj->manage();

		return null;

	}

	/**
	 * Restrict Reset action from users and media
	 *
	 * @param bool $restricted
	 * @param array $restrict
	 * @param string $action
	 * @param array $row
	 * @param Pods_UI $obj
	 *
	 * @return bool
	 * @since 2.3.10
	 */
	public function admin_setup_reset_restrict( $restricted, $restrict, $action, $row, $obj ) {

		if ( in_array( $row[ 'real_type' ], array( 'user', 'media' ) ) ) {
			$restricted = true;
		}

		return $restricted;

	}

	/**
	 * Delete a pod
	 *
	 * @param int $id
	 * @param Pods_UI $obj
	 *
	 * @return mixed
	 */
	public function admin_setup_delete( $id, $obj ) {

		$pod = pods_api()->load_pod( array( 'id' => $id, 'output' => OBJECT ), false );

		if ( empty( $pod ) || ! $pod->is_valid() ) {
			return pods_message( __( 'Pod not found.', 'pods' ), 'error' );
		}

		unset( $obj->data[ $pod->id ] );

		$pod->delete();

		$obj->total       = count( $obj->data );
		$obj->total_found = count( $obj->data );

		pods_message( __( 'Pod deleted successfully.', 'pods' ) );

		return null;

	}

	/**
	 * Handle main Pods Setup area for managing Pods and Fields
	 */
	public function admin_setup_groups() {

		$field_groups = pods( '_pods_group' );

		$fields = array(
			'name'        => array(
				'label' => __( 'Group title', 'pods' )
			),
			'rules'       => array(
				'label'          => __( 'Rules', 'pods' ),
				'custom_display' => array( $this, 'admin_setup_groups_field_rules' ),
				'width'          => '40%'
			),
			'field_count' => array(
				'label'          => __( 'Number of Fields', 'pods' ),
				'custom_display' => array( $this, 'admin_setup_groups_field_count' ),
				'width'          => '15%'
			)
		);

		$total_fields = self::$admin_row->field_count();

		$ui = array(
			'num'              => 'group',
			'icon'             => PODS_URL . 'ui/images/icon32.png',
			'items'            => 'Field Groups',
			'item'             => 'Field Group',
			'fields'           => array(
				'manage' => $fields
			),
			'actions_disabled' => array( 'view', 'export', 'bulk_delete', 'duplicate' ),
			'actions_custom'   => array(
				'add'       => array( $this, 'admin_setup_groups_add' ),
				'edit'      => array( $this, 'admin_setup_groups_edit' ),
				'duplicate' => array( $this, 'admin_setup_groups_duplicate' ),
				'delete'    => array( $this, 'admin_setup_groups_delete' )
			),
			'action_links'     => array(
				'add'    => pods_query_arg( array( 'action_group' => 'add' ) ),
				'edit'   => pods_query_arg( array( 'id_group' => '{@id}', 'action_group' => 'edit' ) ),
				'delete' => pods_query_arg( array( 'id_group' => '{@id}', 'action_group' => 'delete' ) )
			),
			'params'           => array(
				'where' => 't.post_parent = ' . (int) self::$admin_row->id
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => true,
			'pagination'       => false,
			'extra'            => array(
				'total' => ', ' . number_format_i18n( $total_fields ) . ' ' . _n( 'field', 'fields', $total_fields, 'pods' )
			)
		);

		$field_groups->ui( $ui, true );

	}

	/**
	 * Custom value handler for 'Rules' in the Groups Pods_UI
	 *
	 * @param array|Pods_Object|Pods_Object_Group $row Row data
	 * @param Pods_UI $obj Pods_UI object
	 * @param mixed $row_value Row value
	 * @param string $field Field name
	 * @param array|Pods_Object|Pods_Object_Field $attributes Field options
	 * @param array $fields Fields
	 *
	 * @return mixed|string|void
	 */
	public function admin_setup_groups_field_rules( $row, $obj, $row_value, $field, $attributes, $fields ) {

		$options = $row->admin_options();

		$rules = array();

		foreach ( $options[ 'rules' ] as $option => $option_data ) {
			if ( 'rules_taxonomy' == $option ) {
				continue;
			}

			$value = $row[ $option ];

			if ( ! empty( $value ) ) {
				$value = Pods_Form::field_method( 'pick', 'value_to_label', $option, $value, $option_data, $obj->pod->pod_data, $obj->id );

				if ( ! empty( $value ) ) {
					$rule_label = $option_data[ 'label' ];
					$rule_label = str_replace( __( 'Show Group based on', 'pods' ) . ' ', '', $rule_label );

					$rules[ $rule_label ] = pods_serial_comma( $value );
				}
			}
		}

		$row_value = __( 'No rules set.', 'pods' );

		if ( ! empty( $rules ) ) {
			$row_value = '<ul>';

			foreach ( $rules as $rule => $value ) {
				$row_value .= '<li><strong>' . esc_html( $rule ) . ':</strong> ' . esc_html( $value );
			}

			$row_value .= '</ul>';
		}

		return $row_value;

	}

	/**
	 * Custom value handler for 'Field Count' in the Groups Pods_UI
	 *
	 * @param array|Pods_Object|Pods_Object_Group $row Row data
	 * @param Pods_UI $obj Pods_UI object
	 * @param mixed $row_value Row value
	 * @param string $field Field name
	 * @param array|Pods_Object|Pods_Object_Field $attributes Field options
	 * @param array $fields Fields
	 *
	 * @return int
	 */
	public function admin_setup_groups_field_count( $row, $obj, $row_value, $field, $attributes, $fields ) {

		$field_count = count( $row->fields() );

		return $field_count;

	}

	/**
	 * Get the add page of an object
	 *
	 * @param Pods_UI $obj
	 */
	public function admin_setup_groups_add( $obj ) {

		pods_view( PODS_DIR . 'ui/admin/setup-edit-group.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Get the edit page of an object
	 *
	 * @param bool $duplicate
	 * @param Pods_UI $obj
	 */
	public function admin_setup_groups_edit( $duplicate, $obj ) {

		pods_view( PODS_DIR . 'ui/admin/setup-edit-group.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Duplicate a pod
	 *
	 * @param Pods_UI $obj
	 *
	 * @return mixed
	 */
	public function admin_setup_groups_duplicate( $obj ) {

		$group = pods_object_group( null, $obj->id );

		if ( ! $group->is_valid() ) {
			return pods_message( __( 'Field Group not found.', 'pods' ), 'error' );
		}

		$new_id = $group->duplicate();

		if ( 0 < $new_id ) {
			pods_redirect( pods_var_update( array(
				'action' . $obj->num => 'edit',
				'id' . $obj->num     => $new_id,
				'do' . $obj->num     => 'duplicate'
			) ) );
		} else {
			pods_message( 'Field Group could not be duplicated', 'error' );

			$obj->manage();
		}

		return null;
	}

	/**
	 * Delete a pod
	 *
	 * @param int $id
	 * @param Pods_UI $obj
	 *
	 * @return mixed
	 */
	public function admin_setup_groups_delete( $id, $obj ) {

		$group = pods_object_group( null, $obj->id );

		if ( ! $group->is_valid() ) {
			return pods_message( __( 'Field Group not found.', 'pods' ), 'error' );
		}

		$group->delete();

		unset( $obj->data[ $obj->id ] );

		$obj->total       = count( $obj->data );
		$obj->total_found = count( $obj->data );

		pods_message( __( 'Field Group deleted successfully.', 'pods' ) );

		return null;
	}

	/**
	 * Get advanced administration view.
	 */
	public function admin_advanced() {

		pods_view( PODS_DIR . 'ui/admin/advanced.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Get settings administration view
	 */
	public function admin_settings() {

		pods_view( PODS_DIR . 'ui/admin/settings.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Get components administration UI
	 */
	public function admin_components() {

		$components = Pods_Init::$components->components;

		$view = pods_v( 'view', 'get', 'all', true );

		$recommended = array(
			'advanced-relationships',
			'advanced-content-types',
			'migrate-packages',
			'roles-and-capabilities',
			'pages',
			'table-storage',
			'templates'
		);

		foreach ( $components as $component => &$component_data ) {
			if ( ! in_array( $view, array(
					'all',
					'recommended',
					'dev'
				) ) && ( ! isset( $component_data[ 'Category' ] ) || $view != sanitize_title( $component_data[ 'Category' ] ) )
			) {
				unset( $components[ $component ] );

				continue;
			} elseif ( 'recommended' == $view && ! in_array( $component_data[ 'ID' ], $recommended ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( 'dev' == $view && pods_developer() && ! pods_v( 'DeveloperMode', $component_data, false ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( pods_v( 'DeveloperMode', $component_data, false ) && ! pods_developer() ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( ! pods_v( 'TablelessMode', $component_data, false ) && pods_tableless() ) {
				unset( $components[ $component ] );

				continue;
			}

			$component_data[ 'Name' ] = strip_tags( $component_data[ 'Name' ] );

			if ( pods_v( 'DeveloperMode', $component_data, false ) ) {
				$component_data[ 'Name' ] .= ' <em style="font-weight: normal; color:#333;">(Developer Preview)</em>';
			}

			$meta = array();

			if ( ! empty( $component_data[ 'Version' ] ) ) {
				$meta[] = 'Version ' . $component_data[ 'Version' ];
			}

			if ( empty( $component_data[ 'Author' ] ) ) {
				$component_data[ 'Author' ]    = 'Pods Framework Team';
				$component_data[ 'AuthorURI' ] = 'http://pods.io/';
			}

			if ( ! empty( $component_data[ 'AuthorURI' ] ) ) {
				$component_data[ 'Author' ] = '<a href="' . $component_data[ 'AuthorURI' ] . '">' . $component_data[ 'Author' ] . '</a>';
			}

			$meta[] = sprintf( __( 'by %s', 'pods' ), $component_data[ 'Author' ] );

			if ( ! empty( $component_data[ 'URI' ] ) ) {
				$meta[] = '<a href="' . $component_data[ 'URI' ] . '">' . __( 'Visit component site', 'pods' ) . '</a>';
			}

			$component_data[ 'Description' ] = wpautop( trim( make_clickable( strip_tags( $component_data[ 'Description' ], 'em,strong' ) ) ) );

			if ( ! empty( $meta ) ) {
				$component_data[ 'Description' ] .= '<div class="pods-component-meta" ' . ( ! empty( $component_data[ 'Description' ] ) ? ' style="padding:8px 0 4px;"' : '' ) . '>' . implode( '&nbsp;&nbsp;|&nbsp;&nbsp;', $meta ) . '</div>';
			}

			$component_data = array(
				'id'          => $component_data[ 'ID' ],
				'name'        => $component_data[ 'Name' ],
				'category'    => $component_data[ 'Category' ],
				'version'     => '',
				'description' => $component_data[ 'Description' ],
				'mustuse'     => pods_v( 'MustUse', $component_data, false ),
				'toggle'      => 0
			);

			if ( ! empty( $component_data[ 'category' ] ) ) {
				$category_url = pods_query_arg( array(
					'view' => sanitize_title( $component_data[ 'category' ] ),
					'pg'   => '',
					'page' => $_GET[ 'page' ]
				) );

				$component_data[ 'category' ] = '<a href="' . esc_url( $category_url ) . '">' . $component_data[ 'category' ] . '</a>';
			}

			if ( isset( Pods_Init::$components->settings[ 'components' ][ $component_data[ 'id' ] ] ) && 0 != Pods_Init::$components->settings[ 'components' ][ $component_data[ 'id' ] ] ) {
				$component_data[ 'toggle' ] = 1;
			} elseif ( $component_data[ 'mustuse' ] ) {
				$component_data[ 'toggle' ] = 1;
			}
		}

		$components = $this->plugins_to_components( $components );

		$ui = array(
			'data'             => $components,
			'total'            => count( $components ),
			'total_found'      => count( $components ),
			'items'            => 'Components',
			'item'             => 'Component',
			'fields'           => array(
				'manage' => array(
					'name'        => array(
						'label'           => __( 'Name', 'pods' ),
						'width'           => '30%',
						'type'            => 'text',
						'text_allow_html' => true
					),
					'category'    => array(
						'label'           => __( 'Category', 'pods' ),
						'width'           => '10%',
						'type'            => 'text',
						'text_allow_html' => true
					),
					'description' => array(
						'label'                  => __( 'Description', 'pods' ),
						'width'                  => '60%',
						'type'                   => 'text',
						'text_allow_html'        => true,
						'text_allowed_html_tags' => 'strong em a ul ol li b i br div'
					)
				)
			),
			'actions_disabled' => array( 'duplicate', 'view', 'export', 'add', 'edit', 'delete' ),
			'actions_custom'   => array(
				'toggle' => array(
					'callback' => array( $this, 'admin_components_toggle' ),
					'nonce'    => true
				)
			),
			'filters_enhanced' => true,
			'views'            => array(
				'all'          => __( 'All', 'pods' ),
				//'recommended' => __( 'Recommended', 'pods' ),
				'field-types'  => __( 'Field Types', 'pods' ),
				'tools'        => __( 'Tools', 'pods' ),
				'integration'  => __( 'Integration', 'pods' ),
				'migration'    => __( 'Migration', 'pods' ),
				'advanced'     => __( 'Advanced', 'pods' ),
				'pods-plugins' => __( 'Pods Plugins', 'pods' ),
			),
			'view'             => $view,
			'heading'          => array(
				'views' => __( 'Category', 'pods' )
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => false,
			'pagination'       => false
		);

		if ( pods_developer() ) {
			$ui[ 'views' ][ 'dev' ] = __( 'Developer Preview', 'pods' );
		}

		pods_ui( $ui );

	}

	/**
	 * Toggle a component on or off
	 *
	 * @param Pods_UI $ui
	 *
	 * @return bool
	 */
	public function admin_components_toggle( Pods_UI $ui ) {

		$component = $_GET[ 'id' ];

		$plugins = $this->pods_plugins();

		if ( array_key_exists( $component, $this->pods_plugins() ) ) {
			$slug = $component;
			$uri  = $plugins[ $slug ];

			$plugin_file = $this->plugin_file( $uri );
			$all_plugins = get_plugins();
			if ( isset( $all_plugins[ $plugin_file ][ 'Name' ] ) ) {
				$name = $all_plugins[ $plugin_file ][ 'Name' ];
			} else {
				$name = $slug;
			}

			$toggle = pods_v( 'toggle' );

			if ( '1' !== $toggle ) {
				$toggle = false;
			} else {
				$toggle = true;
			}

			if ( $toggle ) {
				//plugin is installed, but not active. So Activate
				if ( ! is_plugin_active( $plugin_file ) && $plugin_file ) {
					echo sprintf( '<div id="message" class="error"><p>%s</p></div>',
						sprintf(
							__( 'Activating %1$s', 'pods' ),
							$name )
					);
					pods_redirect( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file ) );

				} //plugin is not active or installed. So install.
				elseif ( ! is_plugin_active( $plugin_file ) && ! $plugin_file ) {
					echo sprintf( '<div id="message" class="error"><p>%s</p></div>',
						sprintf(
							__( 'Installing %1$s', 'pods' ),
							$name )
					);
					pods_redirect( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $component ), 'install-plugin_' . $component ) );
				}
			} else {
				echo sprintf( '<div id="message" class="error"><p>%s</p></div>',
					sprintf(
						__( 'Deactivating %1$s', 'pods' ),
						$name )
				);
				deactivate_plugins( $plugin_file );
				pods_redirect( self_admin_url( 'plugins.php' ) );

			}

		} else {
			if ( ! empty( Pods_Init::$components->components[ $component ][ 'PluginDependency' ] ) ) {
				$dependency = explode( '|', Pods_Init::$components->components[ $component ][ 'PluginDependency' ] );

				if ( ! pods_is_plugin_active( $dependency[ 1 ] ) ) {
					$website = 'http://wordpress.org/extend/plugins/' . dirname( $dependency[ 1 ] ) . '/';

					if ( isset( $dependency[ 2 ] ) ) {
						$website = $dependency[ 2 ];
					}

					if ( ! empty( $website ) ) {
						$website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $website . '" target="_blank">' . $website . '</a>' );
					}

					$message = sprintf( __( 'The %s component requires that you have the <strong>%s</strong> plugin installed and activated.', 'pods' ), Pods_Init::$components->components[ $component ][ 'Name' ], $dependency[ 0 ] ) . $website;

					// ToDo: Deprecated
					$ui->error( $message );

					$ui->manage();

					return;
				}
			}

			if ( ! empty( Pods_Init::$components->components[ $component ][ 'ThemeDependency' ] ) ) {
				$dependency = explode( '|', Pods_Init::$components->components[ $component ][ 'ThemeDependency' ] );

				if ( strtolower( $dependency[ 1 ] ) != strtolower( get_template() ) && strtolower( $dependency[ 1 ] ) != strtolower( get_stylesheet() ) ) {
					$website = '';

					if ( isset( $dependency[ 2 ] ) ) {
						$website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $dependency[ 2 ] . '" target="_blank">' . $dependency[ 2 ] . '</a>' );
					}

					$message = sprintf( __( 'The %s component requires that you have the <strong>%s</strong> theme installed and activated.', 'pods' ), Pods_Init::$components->components[ $component ][ 'Name' ], $dependency[ 0 ] ) . $website;

					// ToDo: Deprecated
					$ui->error( $message );

					$ui->manage();

					return;
				}
			}

			if ( ! empty( Pods_Init::$components->components[ $component ][ 'MustUse' ] ) ) {
				$message = sprintf( __( 'The %s component can not be disabled from here. You must deactivate the plugin or theme that added it.', 'pods' ), Pods_Init::$components->components[ $component ][ 'Name' ] );

				// ToDo: Deprecated
				$ui->error( $message );

				$ui->manage();

				return;
			}

			$toggled = pods_v( 'toggled' );

			if ( '1' === $toggled ) {
				$toggle = Pods_Init::$components->toggle( $component );

				if ( true === $toggle ) {
					// ToDo: Deprecated
					$ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
				} elseif ( false === $toggle ) {
					// ToDo: Deprecated
					$ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );
				}

				$components = Pods_Init::$components->components;

				foreach ( $components as $component => &$component_data ) {
					$toggle = 0;

					if ( isset( Pods_Init::$components->settings[ 'components' ][ $component_data[ 'ID' ] ] ) ) {
						if ( 0 != Pods_Init::$components->settings[ 'components' ][ $component_data[ 'ID' ] ] ) {
							$toggle = 1;
						}
					}

					if ( true === $component_data[ 'DeveloperMode' ] ) {
						if ( ! pods_developer() ) {
							unset( $components[ $component ] );

							continue;
						}
					}

					$component_data = array(
						'id'          => $component_data[ 'ID' ],
						'name'        => $component_data[ 'Name' ],
						'description' => make_clickable( $component_data[ 'Description' ] ),
						'version'     => $component_data[ 'Version' ],
						'author'      => $component_data[ 'Author' ],
						'toggle'      => $toggle
					);
				}

				$ui->data = $components;

				pods_transient_clear( 'pods_components' );

				$url = pods_query_arg( array( 'toggled' => null ) );

				pods_redirect( $url );
			} elseif ( 1 == pods_v( 'toggle' ) ) {
				// ToDo: Deprecated
				$ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
			} else {
				// ToDo: Deprecated
				$ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );
			}

			$ui->manage();
		}

	}

	/**
	 * Get the admin upgrade page
	 */
	public function admin_upgrade() {

		foreach ( Pods_Init::$upgrades as $old_version => $new_version ) {
			if ( version_compare( $old_version, Pods_Init::$version_last, '<=' ) && version_compare( Pods_Init::$version_last, $new_version, '<' ) ) {
				$new_version = str_replace( '.', '_', $new_version );

				pods_view( PODS_DIR . 'ui/admin/upgrade/upgrade_' . $new_version . '.php', compact( array_keys( get_defined_vars() ) ) );

				return;
			}
		}

		pods_message( __( 'No upgrade necessary', 'pods' ) );

	}

	/**
	 * Get the admin help page
	 */
	public function admin_help() {

		pods_view( PODS_DIR . 'ui/admin/help.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Add pods specific capabilities.
	 *
	 * @param array $capabilities List of extra capabilities to add
	 *
	 * @return array
	 */
	public function admin_capabilities( $capabilities ) {

		$pods = pods_api()->load_pods( array(
			'type' => array(
				'pod',
				'table',
				'post_type',
				'taxonomy',
				'settings'
			)
		) );

		$capabilities[] = 'pods';
		$capabilities[] = 'pods_content';
		$capabilities[] = 'pods_settings';
		$capabilities[] = 'pods_components';

		foreach ( $pods as $pod ) {
			if ( 'settings' == $pod[ 'type' ] ) {
				$capabilities[] = 'pods_edit_' . $pod[ 'name' ];
			} elseif ( 'post_type' == $pod[ 'type' ] ) {
				$capability_type = pods_v( 'capability_type_custom', $pod, pods_v( 'name', $pod ) );

				if ( 'custom' == pods_v( 'capability_type', $pod ) && 0 < strlen( $capability_type ) ) {
					$capabilities[] = 'read_' . $capability_type;
					$capabilities[] = 'edit_' . $capability_type;
					$capabilities[] = 'delete_' . $capability_type;

					if ( 1 == pods_v( 'capability_type_extra', $pod, 1 ) ) {
						$capabilities[] = 'read_private_' . $capability_type . 's';
						$capabilities[] = 'edit_' . $capability_type . 's';
						$capabilities[] = 'edit_others_' . $capability_type . 's';
						$capabilities[] = 'edit_private_' . $capability_type . 's';
						$capabilities[] = 'edit_published_' . $capability_type . 's';
						$capabilities[] = 'publish_' . $capability_type . 's';
						$capabilities[] = 'delete_' . $capability_type . 's';
						$capabilities[] = 'delete_private_' . $capability_type . 's';
						$capabilities[] = 'delete_published_' . $capability_type . 's';
						$capabilities[] = 'delete_others_' . $capability_type . 's';
					}
				}
			} elseif ( 'taxonomy' == $pod[ 'type' ] ) {
				if ( 1 == pods_v( 'capabilities', $pod, 0 ) ) {
					$capability_type = pods_var( 'capability_type_custom', $pod, pods_v( 'name', $pod ) . 's' );

					$capabilities[] = 'manage_' . $capability_type;
					$capabilities[] = 'edit_' . $capability_type;
					$capabilities[] = 'delete_' . $capability_type;
					$capabilities[] = 'assign_' . $capability_type;
				}
			} else {
				$capabilities[] = 'pods_add_' . $pod[ 'name' ];
				$capabilities[] = 'pods_edit_' . $pod[ 'name' ];

				if ( isset( $pod[ 'fields' ][ 'author' ] ) && 'pick' == $pod[ 'fields' ][ 'author' ][ 'type' ] && 'user' == $pod[ 'fields' ][ 'author' ][ 'pick_object' ] ) {
					$capabilities[] = 'pods_edit_others_' . $pod[ 'name' ];
				}

				$capabilities[] = 'pods_delete_' . $pod[ 'name' ];

				if ( isset( $pod[ 'fields' ][ 'author' ] ) && 'pick' == $pod[ 'fields' ][ 'author' ][ 'type' ] && 'user' == $pod[ 'fields' ][ 'author' ][ 'pick_object' ] ) {
					$capabilities[] = 'pods_delete_others_' . $pod[ 'name' ];
				}

				$actions_enabled = pods_v( 'ui_actions_enabled', $pod );

				if ( ! empty( $actions_enabled ) ) {
					$actions_enabled = (array) $actions_enabled;
				} else {
					$actions_enabled = array();
				}

				$available_actions = array(
					'add',
					'edit',
					'duplicate',
					'delete',
					'reorder',
					'export'
				);

				if ( ! empty( $actions_enabled ) ) {
					$actions_disabled = array(
						'view' => 'view'
					);

					foreach ( $available_actions as $action ) {
						if ( ! in_array( $action, $actions_enabled ) ) {
							$actions_disabled[ $action ] = $action;
						}
					}

					if ( ! in_array( 'export', $actions_disabled ) ) {
						$capabilities[] = 'pods_export_' . $pod[ 'name' ];
					}

					if ( ! in_array( 'reorder', $actions_disabled ) ) {
						$capabilities[] = 'pods_reorder_' . $pod[ 'name' ];
					}
				} elseif ( 1 == pods_v( 'ui_export', $pod, 0 ) ) {
					$capabilities[] = 'pods_export_' . $pod[ 'name' ];
				}
			}
		}

		return $capabilities;

	}

	/**
	 * Handle ajax calls for the administration
	 *
	 * @todo Use pure JSON responses
	 */
	public function admin_ajax() {

		if ( false === headers_sent() ) {
			pods_session_start();

			header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		}

		// Sanitize input
		$params = pods_unslash( (array) $_POST );

		foreach ( $params as $key => $value ) {
			if ( 'action' == $key ) {
				continue;
			}

			// Fixup $_POST data
			$_POST[ str_replace( '_podsfix_', '', $key ) ] = $_POST[ $key ];

			// Fixup $params with unslashed data
			$params[ str_replace( '_podsfix_', '', $key ) ] = $value;

			// Unset the _podsfix_* keys
			unset( $params[ $key ] );
		}

		$params = (object) $params;

		$methods = array(
			'add_pod'            => array( 'priv' => true ),
			'save_pod'           => array( 'priv' => true ),
			'save_pod_group'     => array( 'priv' => true ),
			'load_sister_fields' => array( 'priv' => true ),
			'process_form'       => array( 'custom_nonce' => true ), // priv handled through nonce
			'upgrade'            => array( 'priv' => true ),
			'migrate'            => array( 'priv' => true )
		);

		// @todo Needs hook doc
		$methods = apply_filters( 'pods_admin_ajax_methods', $methods, $this );

		if ( ! isset( $params->method ) || ! isset( $methods[ $params->method ] ) ) {
			pods_error( 'Invalid AJAX request', $this );
		}

		$defaults = array(
			'priv'         => null,
			'name'         => $params->method,
			'custom_nonce' => null
		);

		$method = (object) array_merge( $defaults, (array) $methods[ $params->method ] );

		if ( true !== $method->custom_nonce && ( ! isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, 'pods-' . $params->method ) ) ) {
			pods_error( __( 'Unauthorized request', 'pods' ), $this );
		}

		// Cleaning up $params
		unset( $params->action );
		unset( $params->method );

		if ( true !== $method->custom_nonce ) {
			unset( $params->_wpnonce );
		}

		// Check permissions (convert to array to support multiple)
		if ( ! empty( $method->priv ) && ! pods_is_admin( array( 'pods' ) ) && true !== $method->priv && ! pods_is_admin( $method->priv ) ) {
			pods_error( __( 'Access denied', 'pods' ), $this );
		}

		$params->method = $method->name;

		// @todo Needs hook doc
		$params = apply_filters( 'pods_api_' . $method->name, $params, $method );

		$api = pods_api();

		$api->display_errors = false;

		if ( 'upgrade' == $method->name ) {
			$output = (string) pods_upgrade( $params->version )->ajax( $params );
		} elseif ( 'migrate' == $method->name ) {
			// @todo Needs hook doc
			$output = (string) apply_filters( 'pods_api_migrate_run', $params );
		} else {
			if ( ! method_exists( $api, $method->name ) ) {
				pods_error( 'API method does not exist', $this );
			} elseif ( 'save_pod_group' == $method->name ) { // @todo Do we need this to be backwards compatible with save_pod too?
				if ( isset( $params->field_data_json ) && is_array( $params->field_data_json ) ) {
					$params->fields = $params->field_data_json;

					unset( $params->field_data_json );

					foreach ( $params->fields as $k => $v ) {
						if ( empty( $v ) ) {
							unset( $params->fields[ $k ] );
						} elseif ( ! is_array( $v ) ) {
							$params->fields[ $k ] = (array) @json_decode( $v, true );
						}
					}
				}
			}

			// Dynamically call the API method
			$params = (array) $params;

			$output = call_user_func( array( $api, $method->name ), $params );
		}

		// Output in json format
		if ( false !== $output ) {
			if ( is_array( $output ) || is_object( $output ) ) {
				wp_send_json( $output );
			} else {
				echo $output;
			}
		} else {
			pods_error( 'There was a problem with your request.' );
		}

		die(); // KBAI!

	}

	/**
	 * Profiles the Pods configuration
	 *
	 * @param null|string|array $pod . Optional. Which Pod(s) to get configuration for. Can be a the name of one Pod, or an array of names of Pods, or null, which is the default, to profile all Pods.
	 * @param bool $full_field_info Optional. If true all info about each field is returned. If false, which is the default only name and type, will be returned.
	 *
	 * @return array
	 *
	 * @since 3.0.0
	 */
	function configuration( $pod = null, $full_field_info = false ) {

		$api = pods_api();

		if ( is_null( $pod ) ) {
			$the_pods = $api->load_pods();
		} elseif ( is_array( $pod ) ) {
			foreach ( $pod as $p ) {
				$the_pods[] = $api->load_pod( $p );
			}
		} else {
			$the_pods[] = $api->load_pod( $pod );
		}

		$configuration = array();

		foreach ( $the_pods as $pod ) {
			$configuration[ $pod[ 'name' ] ] = array(
				'name'    => $pod[ 'name' ],
				'ID'      => $pod[ 'id' ],
				'storage' => $pod[ 'storage' ],
				'fields'  => $pod[ 'fields' ],
			);
		}

		if ( ! $full_field_info ) {
			foreach ( $the_pods as $pod ) {
				$fields = $configuration[ $pod[ 'name' ] ][ 'fields' ];

				unset( $configuration[ $pod[ 'name' ] ][ 'fields' ] );

				foreach ( $fields as $field ) {
					$info = array(
						'name' => $field[ 'name' ],
						'type' => $field[ 'type' ],
					);

					if ( $info[ 'type' ] === 'pick' ) {
						$info[ 'pick_object' ] = $field[ 'pick_object' ];

						if ( isset ( $field[ 'pick_val' ] ) && $field[ 'pick_val' ] !== '' ) {
							$info[ 'pick_val' ] = $field[ 'pick_val' ];
						}
					}

					if ( is_array( $info ) ) {
						$configuration[ $pod[ 'name' ] ][ 'fields' ][ $field[ 'name' ] ] = $info;
					}

					unset( $info );
				}

			}

		}

		return $configuration;

	}

	/**
	 * Debug Information
	 *
	 * @param bool $html
	 *
	 * @return string
	 *
	 * @since 3.0.0
	 */
	function debug_info( $html = true ) {

		global $wp_version, $wpdb;

		$wp      = $wp_version;
		$php     = phpversion();
		$mysql   = $wpdb->db_version();
		$plugins = array();

		$all_plugins = get_plugins();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$plugins[ $plugin_data[ 'Name' ] ] = $plugin_data[ 'Version' ];
			}
		}

		$stylesheet    = get_stylesheet();
		$theme         = wp_get_theme( $stylesheet );
		$theme_name    = $theme->get( 'Name' );
		$theme_version = $theme->get( 'Version' );

		$opcode_cache = array(
			'Apc'       => function_exists( 'apc_cache_info' ) ? 'Yes' : 'No',
			'Memcached' => class_exists( 'eaccelerator_put' ) ? 'Yes' : 'No',
			'Redis'     => class_exists( 'xcache_set' ) ? 'Yes' : 'No',
		);

		$object_cache = array(
			'Apc'       => function_exists( 'apc_cache_info' ) ? 'Yes' : 'No',
			'Apcu'      => function_exists( 'apcu_cache_info' ) ? 'Yes' : 'No',
			'Memcache'  => class_exists( 'Memcache' ) ? 'Yes' : 'No',
			'Memcached' => class_exists( 'Memcached' ) ? 'Yes' : 'No',
			'Redis'     => class_exists( 'Redis' ) ? 'Yes' : 'No',
		);

		$versions = array(
			'WordPress Version'             => $wp,
			'PHP Version'                   => $php,
			'MySQL Version'                 => $mysql,
			'Server Software'               => $_SERVER[ 'SERVER_SOFTWARE' ],
			'Your User Agent'               => $_SERVER[ 'HTTP_USER_AGENT' ],
			'Session Save Path'             => session_save_path(),
			'Session Save Path Exists'      => ( file_exists( session_save_path() ) ? 'Yes' : 'No' ),
			'Session Save Path Writeable'   => ( is_writable( session_save_path() ) ? 'Yes' : 'No' ),
			'Session Max Lifetime'          => ini_get( 'session.gc_maxlifetime' ),
			'Opcode Cache'                  => $opcode_cache,
			'Object Cache'                  => $object_cache,
			'WPDB Prefix'                   => $wpdb->prefix,
			'WP Multisite Mode'             => ( is_multisite() ? 'Yes' : 'No' ),
			'WP Memory Limit'               => WP_MEMORY_LIMIT,
			'Pods Network-Wide Activated'   => ( is_plugin_active_for_network( basename( PODS_DIR ) . '/init.php' ) ? 'Yes' : 'No' ),
			'Pods Install Location'         => PODS_DIR,
			'Pods Tableless Mode Activated' => ( ( pods_tableless() ) ? 'Yes' : 'No' ),
			'Pods Light Mode Activated'     => ( ( defined( 'PODS_LIGHT' ) && PODS_LIGHT ) ? 'Yes' : 'No' ),
			'Currently Active Theme'        => $theme_name . ': ' . $theme_version,
			'Currently Active Plugins'      => $plugins
		);

		if ( $html ) {
			$debug = '';
			foreach ( $versions as $what => $version ) {
				$debug .= '<p><strong>' . $what . '</strong>: ';

				if ( is_array( $version ) ) {
					$debug .= '</p><ul class="ul-disc">';

					foreach ( $version as $what_v => $v ) {
						$debug .= '<li><strong>' . $what_v . '</strong>: ' . $v . '</li>';
					}

					$debug .= '</ul>';
				} else {
					$debug .= $version . '</p>';
				}
			}

			return $debug;

		} else {
			return $versions;

		}

	}

	/**
	 * Formatted array of information prepared to send to support.
	 *
	 * @param bool $kses . Optional. Whether to pass output through wp_kses() or not and only allow <pre>. Defaults to true.
	 *
	 * @return string
	 */
	function send_info( $kses = true ) {

		$info = array(
			'Debug Information'  => $this->debug_info( false ),
			'Pods Configuration' => $this->configuration(),
		);

		$return = '<pre>' . print_r( $info, true ) . '</pre>';

		if ( $kses ) {
			return wp_kses( $return, array( 'pre' => array(), ) );

		} else {
			return $return;
		}

	}

	/**
	 * Sets the settings fields used to create Pods Settings (_pods_settings)
	 *
	 * @return array Pods settings fields
	 *
	 * @since 3.0.0
	 *
	 * @todo Needs i18n
	 */
	function pods_settings() {

		$general = array(
			'default_pagination' => array(
				'name'        => 'default_pagination',
				'label'       => 'Default Pagination',
				'description' => 'Change the default pagination, when pagination type is not set explicitly.',
				'help'        => '',
				'default'     => null,
				'type'        => 'pick',
				'data'        => array(
					'none'     => __( 'None', 'pods' ),
					'simple'   => __( 'Simple', 'pods' ),
					'paginate' => __( 'Paginate', 'pods' ),
					'advanced' => __( 'Advanced', 'pods' )
				),
				'pick_object' => 'custom-simple'
			)
		);

		/**
		 * Change or add to Pods Settings General Group
		 *
		 * @param array $general General Settings
		 *
		 * @return array The settings field arrays.
		 *
		 * @since 3.0.0
		 */
		$general = apply_filters( 'pods_admin_general_settings', $general );

		foreach ( $general as $item ) {
			$item[ 'group' ]   = 1;
			$item[ 'grouped' ] = 1;
		}

		$performance = array(
			'enable_pods_light_mode'            => array(
				'name'                => 'enable_pods_light_mode',
				'label'               => 'Enable Pods Light Mode',
				'description'         => '',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'enable_pods_tableless_mode'        => array(
				'name'                => 'enable_pods_tableless_mode',
				'label'               => 'Enable Pods Tableless Mode',
				'description'         => '',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_pods_api_cache'            => array(
				'name'                => 'disable_pods_api_cache',
				'label'               => 'Disable Pods API Cache',
				'description'         => '',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_pods_deprecated_functions' => array(
				'name'                => 'disable_pods_deprecated_functions',
				'label'               => 'Disable Deprecated Pods Functions',
				'description'         => 'Use with caution. When checked use of deprecated functions will cause fatal errors instead of warnings.',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_session_auto_start'        => array(
				'name'                => 'disable_session_auto_start',
				'label'               => 'Disable Session Auto Start',
				'description'         => '',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			)
		);

		/**
		 * Change or add to Pods Settings Performance Group
		 *
		 * @param array $performance Performance settings
		 *
		 * @return array The settings field arrays.
		 *
		 * @since 3.0.0
		 */
		$performance = apply_filters( 'pods_admin_performance_settings', $performance );

		foreach ( $performance as $item ) {
			$item[ 'group' ]   = 2;
			$item[ 'grouped' ] = 1;
		}

		$access_security = array(
			'disable_pods_menu'       => array(
				'name'                => 'disable_pods_menu',
				'label'               => 'Disable Pods Menu',
				'description'         => 'Hide the Pods admin menu for users of any role.',
				'help'                => 'Since you can set access to the Pods admin menu by user level using the Admin Access Role setting, that is generally a better setting to use.',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_pods_eval'       => array(
				'name'                => 'disable_pods_eval',
				'label'               => 'Disable Pods Eval',
				'description'         => 'Prevents Pods Pages and Templates from executing PHP code.',
				'help'                => 'If you are allowing untrusted users access to your Pods Pages or Pods Template editors, the ability to execute php code via these components is a threat to security and stability of your site.',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_file_upload'     => array(
				'name'                => 'disable_file_upload',
				'label'               => 'Disable File Upload',
				'description'         => '',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_file_browser'    => array(
				'name'                => 'disable_file_browser',
				'label'               => 'Disable File Browser',
				'description'         => '',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'require_login_for_files' => array(
				'name'                => 'require_login_for_files',
				'label'               => 'Require Files For Login',
				'description'         => '',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_shortcode_sql'   => array(
				'name'                => 'disable_shortcode_sql',
				'label'               => 'Disable Shortcode SQL',
				'description'         => 'Prevent shortcodes from passing SQL that could potentially be used to used to compromise site security.',
				'help'                => 'This setting is recommended if you are allowing untrusted users to create or edit posts. When enabled, Pods shortcodes will ignore its "orderby", "where", "having", "groupby" and "select" arguments.',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			// @todo This is going to be wrong, we should base these off of user capabilities
			/*'admin_access_role' => array(
				'name' => 'admin_access_role',
				'label' => 'Admin access role',
				'description' => 'Set the minimum use role to access the Pods Admin.',
				'help' => 'On multisite, default is super admin, otherwise the default is admin.',
				'type' => 'pick',
				'data' => array(
					// This should be a list of capabilities like manage_option etc, I doubt we need many options
				),
				'pick_object' => 'custom-simple',
			)*/
		);

		/*$access_security[ 'admin_access_role' ][ 'default' ] = 'admin';

		if ( is_multisite() ) {
			$access_security[ 'admin_access_role' ][ 'default' ] = 'super_admin';
		}

		global $wp_roles;

		$roles = $wp_roles->get_names();

		foreach ( $roles as $role => $label ) {
			$the_roles[] = $role.'|'.$label."\n";
		}

		$access_security[ 'admin_access_role' ][ 'pick_custom' ] = implode("\n", $the_roles );*/

		/**
		 * Change or add to Pods Settings Access & Security Group
		 *
		 * @param array $access_security Access & Security settings
		 *
		 * @return array The settings field arrays.
		 *
		 * @since 3.0.0
		 */
		$access_security = apply_filters( 'pods_admin_access_security_settings', $access_security );

		foreach ( $access_security as $item ) {
			$item[ 'group' ]   = 3;
			$item[ 'grouped' ] = 1;
		}

		// @todo This needs abstracting into the Pod Pages class, and hook into pods_admin_pods_settings instead

		$pods_pages = array(
			'enable_pods_light_mode'        => array(
				'name'                => 'enable_pages_pods_version_output',
				'label'               => 'Enable output of Pods version in head of Pods Pages.',
				'description'         => 'If enabled, Pods Pages will display, inside the &lt;head&gt; tag the current version of Pods.',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			),
			'disable_pods_pages_page_check' => array(
				'name'                => 'disable_pods_pages_page_check',
				'label'               => 'Disable Pods Pages Page Check',
				'description'         => 'Disables the check that is run before a page loads to see if Pods Pages is being used.',
				'help'                => '',
				'boolean_format_type' => 'checkbox',
				'boolean_yes_label'   => 'Enable',
				'boolean_no_label'    => 'Disable'
			)
		);

		/**
		 * Change or add to Pods Settings Pods Pages Group
		 *
		 * @param array $pods_pages Pods Pages settings
		 *
		 * @return array The settings field arrays.
		 *
		 * @since 3.0.0
		 */
		$pods_pages = apply_filters( 'pods_admin_pods_pages_settings', $pods_pages );

		foreach ( $pods_pages as $item ) {
			$item[ 'group' ]   = 5;
			$item[ 'grouped' ] = 1;
		}

		$pods_settings = array_merge( $general, $performance, $pods_pages );

		/**
		 * Change or add to Pods Settings fields and groups.
		 *
		 * Useful for adding your own group of settings to Pods Settings page.
		 *
		 * @param array $pods_settings Pods settings.
		 *
		 * @return array The settings field arrays.
		 *
		 * @since 3.0.0
		 */
		$pods_settings = apply_filters( 'pods_admin_pods_settings', $pods_settings );

		foreach ( $pods_settings as $setting ) {
			if ( ! isset( $setting[ 'type' ] ) ) {
				$setting[ 'type' ] = 'boolean';
			}
		}

		return $pods_settings;

	}

	/**
	 * Implements settings set in pods settings
	 *
	 * @todo Hook this to something when it's ready to be used.
	 *
	 * @since 3.0.0
	 */
	function pods_settings_callback() {

		$settings = $this->settings_object();

		// General settings
		if ( $settings->field( 'default_pagination' ) != 'none' ) {
			$type = $settings->field( 'default_pagination' );
			// @todo Do something with this
		} else {
			// @todo Disable pagination
		}

		// Performance settings
		if ( ! defined( 'PODS_LIGHT' ) && $settings->field( 'enable_pods_light_mode' ) == 1 ) {
			define( 'PODS_LIGHT', true );
		}

		if ( ! defined( 'PODS_TABLELESS' ) && $settings->field( 'enable_pods_tableless_mode' ) == 1 ) {
			define( 'PODS_TABLELESS', true );
		}

		if ( $settings->field( 'disable_full_meta_integration' ) == 1 ) {
			// @todo disable full meta integration
		}

		if ( ! defined( 'PODS_API_CACHE' ) && $settings->field( 'disable_pods_api_cache' ) == 0 ) {
			define( 'PODS_API_CACHE', false );
		}

		if ( ! defined( 'PODS_DEPRECATED' ) && $settings->field( 'disable_pods_deprecated_functions' ) == 0 ) {
			define( 'PODS_DEPRECATED', false );
		}

		if ( ! defined( 'PODS_SESSION_AUTO_START' ) && $settings->field( 'disable_session_auto_start' ) == 1 ) {
			define( 'PODS_SESSION_AUTO_START', true );
		}

		// Access and security
		if ( ! defined( 'PODS_DISABLE_ADMIN_MENU' ) && $settings->field( 'disable_pods_menu' ) == 1 ) {
			define( 'PODS_DISABLE_ADMIN_MENU', true );
		}

		if ( ! defined( 'PODS_DISABLE_EVAL' ) && $settings->field( 'disable_pods_eval' ) ) {
			define( 'PODS_DISABLE_EVAL', true );
		}

		if ( ! defined( 'PODS_DISABLE_FILE_UPLOAD' ) && $settings->field( 'disable_file_upload' ) == 1 ) {
			define( 'PODS_DISABLE_FILE_UPLOAD', true );
		}

		if ( ! defined( 'PODS_DISABLE_FILE_BROWSER' ) && $settings->field( 'disable_file_browser' ) == 1 ) {
			define( 'PODS_DISABLE_FILE_BROWSER', true );
		}

		if ( ! defined( 'PODS_FILES_REQUIRE_LOGIN' ) && $settings->field( 'require_login_for_files' ) == 1 ) {
			define( 'PODS_FILES_REQUIRE_LOGIN', true );
		}

		if ( ! defined( 'PODS_DISABLE_SHORTCODE_SQL' ) && $settings->field( 'disable_shortcode_sql' ) == 1 ) {
			define( 'PODS_DISABLE_SHORTCODE_SQL', true );
		}

		if ( ( is_multisite() && $settings->field( 'admin_access_role' ) !== 'super_admin' ) || ( ! is_multisite() && $settings->field( 'admin_access_role' ) !== 'admin' ) ) {
			add_filter( 'pods_is_admin', array( $this, 'settings_admin_access' ), 25, 3 );
		}

		// Components
		// @todo This needs abstracting into the Pod Pages class
		if ( ! defined( 'PODS_DISABLE_VERSION_OUTPUT' ) && $settings->field( 'enable_pages_pods_version_output' ) == 1 ) {
			define( 'PODS_DISABLE_VERSION_OUTPUT', true );
		}

		if ( ! defined( 'PODS_DISABLE_POD_PAGE_CHECK' ) && $settings->field( 'disable_pods_pages_page_check' ) == 1 ) {
			define( 'PODS_DISABLE_POD_PAGE_CHECK', true );
		}

		if ( ! defined( 'PODS_DISABLE_META' ) && $settings->field( 'disable_pods_pages_meta' ) == 1 ) {
			define( 'PODS_DISABLE_META', false );
		}

		if ( ! defined( 'PODS_DISABLE_BODY_CLASSES' ) && $settings->field( 'disable_pods_pages_body_class' ) == 1 ) {
			define( 'PODS_DISABLE_BODY_CLASSES', true );
		}

	}

	/**
	 * Implements Pods Admin Menu Access Setting
	 *
	 * Don't use this to set admin access programmatically! Use pods_is_admin filter instead.
	 *
	 * @param $has_access
	 * @param $cap
	 * @param $capability
	 *
	 * @return mixed|null
	 *
	 * @since 3.0.0
	 */
	function settings_admin_access( $has_access, $cap, $capability ) {

		$settings = $this->settings_object();

		$capability = $settings->field( 'admin_access_role' );

		return $capability;

	}

	/**
	 * Returns the Pods settings UI
	 *
	 * @return Pods_UI|void
	 *
	 * @since 3.0.0
	 */
	function pods_settings_ui() {

		$settings = $this->settings_object();

		return $settings->ui();

	}

	/**
	 * Get the settings object
	 *
	 * @todo Make pods( '_pods_settings' ) return the actual settings
	 *
	 * @return bool|Pods
	 *
	 * @since 3.0.0
	 */
	function settings_object() {

		$settings = pods( '_pods_settings', null, true );

		if ( ! $settings || ! is_object( $settings ) || ! $settings->valid() ) {
			return null;
		}

		return $settings;

	}

	/**
	 * Pods add-on plugins available in the plugin installer
	 *
	 * @return array
	 *
	 * @since 3.0.0
	 */
	function pods_plugins() {

		$plugins = array(
			'pods-alternative-cache'      => 'http://pods.io/2014/04/16/introducing-pods-alternative-cache/',
			'pods-frontier-auto-template' => 'http://pods.io/?p=182830',
			'pods-seo'                    => 'http://wordpress.org/plugins/pods-seo/',
			'pods-ajax-views'             => 'https://wordpress.org/plugins/pods-ajax-views/',
			// 'pods-jobs-queue'             => 'https://wordpress.org/plugins/pods-jobs-queue/',
			'csv-importer-for-pods'       => 'https://wordpress.org/plugins/csv-importer-for-pods/',
		);

		/**
		 * Set plugins available in the plugin installer
		 *
		 * @param array $plugins Should be in the form of 'plugin-slug' => 'plugin-uri'
		 *
		 * @returns array
		 *
		 * @since 3.0.0
		 */
		$plugins = apply_filters( 'pods_admin_pods_plugins', $plugins );

		return $plugins;

	}

	/**
	 * Adds Pods Plugins to the array of components used to populate components admin UI.
	 *
	 * @param array $components The prepared array of components.
	 *
	 * @returns array The array of components and plugins.
	 *
	 * @access private
	 *
	 * @since 3.0.0
	 */
	private function plugins_to_components( $components ) {

		$pods_plugins = $this->pods_plugins();

		$all_plugins = get_plugins();
		$plugin_uris = wp_list_pluck( $all_plugins, 'PluginURI' );
		$plugin_uris = array_flip( $plugin_uris );

		foreach ( $pods_plugins as $slug => $uri ) {
			if ( array_key_exists( $uri, $plugin_uris ) ) {
				$file        = $plugin_uris[ $uri ];
				$plugin_info = $all_plugins[ $file ];

				$components[ $slug ][ 'id' ]          = $slug;
				$components[ $slug ][ 'file' ]        = $file;
				$components[ $slug ][ 'name' ]        = $plugin_info[ 'Name' ];
				$components[ $slug ][ 'PluginName' ]  = $plugin_info[ 'Name' ];
				$components[ $slug ][ 'ShortName' ]   = $plugin_info[ 'Name' ];
				$components[ $slug ][ 'PluginName' ]  = $plugin_info[ 'Name' ];
				$components[ $slug ][ 'MenuName' ]    = $plugin_info[ 'Name' ];
				$components[ $slug ][ 'URI' ]         = $uri;
				$components[ $slug ][ 'Version' ]     = $plugin_info[ 'Version' ];
				$components[ $slug ][ 'AuthorURI' ]   = $plugin_info[ 'AuthorURI' ];
				$components[ $slug ][ 'Description' ] = $plugin_info[ 'Description' ];
				$components[ $slug ][ 'category' ]    = 'Pods Plugins';
				$components                           = $this->plugin_component_meta_prepare( $components, $slug );

				if ( pods_is_plugin_active( $file ) ) {
					$components[ $slug ][ 'toggle' ] = 1;
				} else {
					$components[ $slug ][ 'toggle' ] = 0;
				}
			} else {
				$info = $this->plugin_info_via_api( $slug );

				$components[ $slug ][ 'id' ] = $slug;

				foreach ( $info as $key => $value ) {
					if ( $key === 'Name' ) {
						$components[ $slug ][ 'name' ] = $value;
					}

					$components[ $slug ][ $key ] = $value;
				}

				$components[ $slug ][ 'category' ] = 'Pods Plugins';

				$components                      = $this->plugin_component_meta_prepare( $components, $slug );
				$components[ $slug ][ 'toggle' ] = 0;
			}
		}

		return $components;

	}

	/**
	 * Fetch plugin info via WordPress.org plugins API
	 *
	 * @param string $slug The plugin's slug (needs to match http://wordpress.org/plugins/{$slug})
	 *
	 * @return array
	 *
	 * @since 3.0.0
	 */
	function plugin_info_via_api( $slug ) {

		$url = "http://api.wordpress.org/plugins/info/1.0/{$slug}.json";

		$response = wp_remote_post(
			$url,
			array(
				'method'  => 'GET',
				'timeout' => 30,
			)
		);

		$info = array();

		if ( ! is_wp_error( $response ) ) {
			$obj = wp_remote_retrieve_body( $response );

			$obj = json_decode( $obj );

			$info = array(
				'Name'        => $obj->name,
				'Version'     => $obj->version,
				'Author'      => $obj->author,
				'AuthorURI'   => $obj->author_profile,
				'Description' => $obj->short_description,
			);
		}

		return $info;

	}

	/**
	 * Get the base file for any installed plugin.
	 *
	 * @param string $uri The plugins WordPress.org page. IE 'http://wordpress.org/plugins/pods/'
	 *
	 * @return string|bool Either the base file for the plugin, IE 'pods/init.php' or false if plugin isn't installed.
	 *
	 * @since 3.0.0
	 */
	function plugin_file( $uri ) {

		$all_plugins = get_plugins();
		$plugin_uris = wp_list_pluck( $all_plugins, 'PluginURI' );
		$plugin_uris = array_flip( $plugin_uris );

		if ( array_key_exists( $uri, $plugin_uris ) ) {
			return $plugin_uris[ $uri ];
		}

		return false;

	}

	/**
	 * Prepares the meta section of the component admin UI for Pods Plugin in $this->plugins_to_components()
	 *
	 * @param array $components The components array.
	 * @param string $slug The slug of the plugin currently being prepared.
	 *
	 * @return array Updated components array.
	 *
	 * @access private
	 *
	 * @since 3.0.0
	 */
	private function plugin_component_meta_prepare( $components, $slug ) {

		$meta = array();

		if ( ! empty( $components[ $slug ][ 'Version' ] ) ) {
			$meta[] = 'Version ' . $components[ $slug ][ 'Version' ];
		}

		if ( empty( $components[ $slug ][ 'Author' ] ) ) {
			$components[ $slug ][ 'Author' ]    = 'Pods Framework Team';
			$components[ $slug ][ 'AuthorURI' ] = 'http://pods.io/';
		}
		if ( ! empty( $components[ $slug ][ 'AuthorURI' ] ) ) {
			$components[ $slug ][ 'Author' ] = '<a href="' . $components[ $slug ][ 'AuthorURI' ] . '">' . $components[ $slug ][ 'Author' ] . '</a>';
		}

		$meta[] = sprintf( __( 'by %s', 'pods' ), $components[ $slug ][ 'Author' ] );

		if ( ! empty( $components[ $slug ][ 'URI' ] ) ) {
			$meta[] = '<a href="' . $components[ $slug ][ 'URI' ] . '">' . __( 'Visit component site', 'pods' ) . '</a>';
		}

		$components[ $slug ][ 'description' ] = wpautop( trim( make_clickable( strip_tags( $components[ $slug ][ 'Description' ], 'em,strong' ) ) ) );

		if ( ! empty( $meta ) ) {
			$components[ $slug ][ 'description' ] .= '<div class="pods-component-meta" ' . ( ! empty( $components[ $slug ][ 'Description' ] ) ? ' style="padding:8px 0 4px;"' : '' ) . '>' . implode( '&nbsp;&nbsp;|&nbsp;&nbsp;', $meta ) . '</div>';
		}

		if ( ! empty( $components[ $slug ][ 'category' ] ) ) {
			$category_url = pods_query_arg( array(
				'view' => sanitize_title( $components[ $slug ][ 'category' ] ),
				'pg'   => '',
				'page' => $_GET[ 'page' ]
			) );

			$components[ $slug ][ 'category' ] = '<a href="' . $category_url . '">' . $components[ $slug ][ 'category' ] . '</a>';
		}

		return $components;

	}

}
