<?php

/**
 * @package Pods
 */
class PodsAdmin {

	/**
	 * @var PodsAdmin
	 */
	public static $instance = null;

	/**
	 * Singleton handling for a basic pods_admin() request
	 *
	 * @return \PodsAdmin
	 *
	 * @since 2.3.5
	 */
	public static function init() {

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new PodsAdmin();
		}

		return self::$instance;
	}

	/**
	 * Setup and Handle Admin functionality
	 *
	 * @return \PodsAdmin
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct() {

		// Scripts / Stylesheets
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ), 20 );

		// AJAX $_POST fix
		add_action( 'admin_init', array( $this, 'admin_init' ), 9 );

		// Menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );

		// AJAX for Admin
		add_action( 'wp_ajax_pods_admin', array( $this, 'admin_ajax' ) );
		add_action( 'wp_ajax_nopriv_pods_admin', array( $this, 'admin_ajax' ) );

		// Add Media Bar button for Shortcode
		add_action( 'media_buttons', array( $this, 'media_button' ), 12 );

		// Add the Pods capabilities
		add_filter( 'members_get_capabilities', array( $this, 'admin_capabilities' ) );

		add_action( 'admin_head-media-upload-popup', array( $this, 'register_media_assets' ) );

		// Add our debug to Site Info.
		add_filter( 'debug_information', array( $this, 'add_debug_information' ) );

		$this->rest_admin();

	}

	/**
	 * Init the admin area
	 *
	 * @since 2.0.0
	 */
	public function admin_init() {

		// Fix for plugins that *don't do it right* so we don't cause issues for users
		// @codingStandardsIgnoreLine
		if ( defined( 'DOING_AJAX' ) && ! empty( $_POST ) ) {
			$pods_admin_ajax_actions = array(
				'pods_admin',
				'pods_relationship',
				'pods_upload',
				'pods_admin_components',
			);

			/**
			 * Admin AJAX Callbacks
			 *
			 * @since unknown
			 *
			 * @param array $pods_admin_ajax_actions Array of actions to handle.
			 */
			$pods_admin_ajax_actions = apply_filters( 'pods_admin_ajax_actions', $pods_admin_ajax_actions );

			if ( in_array( pods_v( 'action' ), $pods_admin_ajax_actions, true ) || in_array( pods_v( 'action', 'post' ), $pods_admin_ajax_actions, true ) ) {
				// @codingStandardsIgnoreLine
				foreach ( $_POST as $key => $value ) {
					if ( 'action' === $key || 0 === strpos( $key, '_podsfix_' ) ) {
						continue;
					}

					// @codingStandardsIgnoreLine
					unset( $_POST[ $key ] );

					// @codingStandardsIgnoreLine
					$_POST[ '_podsfix_' . $key ] = $value;
				}
			}
		}//end if
	}

	/**
	 * Attach requirements to admin header
	 *
	 * @since 2.0.0
	 */
	public function admin_head() {

		wp_register_script( 'pods-floatmenu', PODS_URL . 'ui/js/floatmenu.js', array(), PODS_VERSION );

		wp_register_script( 'pods-admin-importer', PODS_URL . 'ui/js/admin-importer.js', array(), PODS_VERSION );

		wp_register_script( 'pods-upgrade', PODS_URL . 'ui/js/jquery.pods.upgrade.js', array(), PODS_VERSION );

		wp_register_script( 'pods-migrate', PODS_URL . 'ui/js/jquery.pods.migrate.js', array(), PODS_VERSION );

		// @codingStandardsIgnoreLine
		if ( isset( $_GET['page'] ) ) {
			// @codingStandardsIgnoreLine
			$page = $_GET['page'];
			if ( 'pods' === $page || ( false !== strpos( $page, 'pods-' ) && 0 === strpos( $page, 'pods-' ) ) ) {
				?>
				<script type="text/javascript">
					var PODS_URL = "<?php echo esc_js( PODS_URL ); ?>";
				</script>
				<?php
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-sortable' );

				wp_enqueue_script( 'pods-floatmenu' );

				wp_enqueue_script( 'jquery-qtip2' );
				wp_enqueue_script( 'pods-qtip-init' );

				wp_enqueue_script( 'pods' );

				if ( 0 === strpos( $page, 'pods-manage-' ) || 0 === strpos( $page, 'pods-add-new-' ) ) {
					wp_enqueue_script( 'post' );
				} elseif ( 0 === strpos( $page, 'pods-settings-' ) ) {
					wp_enqueue_script( 'post' );
				}

				if ( 'pods-advanced' === $page ) {
					wp_register_script( 'pods-advanced', PODS_URL . 'ui/js/advanced.js', array(), PODS_VERSION );
					wp_enqueue_script( 'jquery-ui-effects-core', PODS_URL . 'ui/js/jquery-ui/jquery.effects.core.js', array( 'jquery' ), '1.8.8' );
					wp_enqueue_script( 'jquery-ui-effects-fade', PODS_URL . 'ui/js/jquery-ui/jquery.effects.fade.js', array( 'jquery' ), '1.8.8' );
					wp_enqueue_script( 'jquery-ui-dialog' );
					wp_enqueue_script( 'pods-advanced' );
				} elseif ( 'pods-packages' === $page ) {
					wp_enqueue_style( 'pods-wizard' );
				} elseif ( 'pods-wizard' === $page || 'pods-upgrade' === $page || ( in_array(
					$page, array(
						'pods',
						'pods-add-new',
					), true
				) && in_array(
					pods_v( 'action', 'get', 'manage' ), array(
						'add',
						'manage',
					), true
				) ) ) {
					wp_enqueue_style( 'pods-wizard' );

					if ( 'pods-upgrade' === $page ) {
						wp_enqueue_script( 'pods-upgrade' );
					}
				}//end if
			}//end if
		}//end if

		/**
		 * Filter to disable default loading of the DFV script. By default, Pods
		 * will always enqueue the DFV script if is_admin()
		 *
		 * Example: add_filter( 'pods_default_enqueue_dfv', '__return_false');
		 *
		 * @param bool Whether or not to enqueue by default
		 *
		 * @since 2.7.10
		 */
		if ( apply_filters( 'pods_default_enqueue_dfv', true ) ) {
			wp_enqueue_script( 'pods-dfv' );
		}

		// New Styles Enqueue
		wp_enqueue_style( 'pods-styles' );
	}

	/**
	 * Build the admin menus
	 *
	 * @since 2.0.0
	 */
	public function admin_menu() {

		$advanced_content_types = PodsMeta::$advanced_content_types;
		$taxonomies             = PodsMeta::$taxonomies;
		$settings               = PodsMeta::$settings;

		$all_pods = pods_api()->load_pods( array( 'count' => true ) );

		if ( ! PodsInit::$upgrade_needed || ( pods_is_admin() && 1 === (int) pods_v( 'pods_upgrade_bypass' ) ) ) {
			$submenu_items = array();

			if ( ! empty( $advanced_content_types ) ) {
				$submenu = array();

				$pods_pages = 0;

				foreach ( (array) $advanced_content_types as $pod ) {
					if ( ! isset( $pod['name'] ) || ! isset( $pod['options'] ) || empty( $pod['fields'] ) ) {
						continue;
					} elseif ( ! pods_is_admin(
						array(
							'pods',
							'pods_content',
							'pods_add_' . $pod['name'],
							'pods_edit_' . $pod['name'],
							'pods_delete_' . $pod['name'],
						)
					) ) {
						continue;
					}

					$pod_name = $pod['name'];

					$pod = apply_filters( "pods_advanced_content_type_pod_data_{$pod_name}", $pod, $pod['name'] );
					$pod = apply_filters( 'pods_advanced_content_type_pod_data', $pod, $pod['name'] );

					if ( 1 === (int) pods_v( 'show_in_menu', $pod['options'], 0 ) ) {
						$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );
						$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

						$menu_label = pods_v( 'menu_name', $pod['options'], $page_title, true );
						$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

						$singular_label = pods_v( 'label_singular', $pod['options'], pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true ), true );
						$plural_label   = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );

						$menu_location        = pods_v( 'menu_location', $pod['options'], 'objects' );
						$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );

						$menu_position = pods_v( 'menu_position', $pod['options'], '', true );
						$menu_icon     = pods_evaluate_tags( pods_v( 'menu_icon', $pod['options'], '', true ), true );

						if ( empty( $menu_position ) ) {
							$menu_position = null;
						}

						$parent_page = null;

						if ( pods_is_admin(
							array(
								'pods',
								'pods_content',
								'pods_edit_' . $pod['name'],
								'pods_delete_' . $pod['name'],
							)
						) ) {
							if ( ! empty( $menu_location_custom ) ) {
								if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
									$submenu_items[ $menu_location_custom ] = array();
								}

								$submenu_items[ $menu_location_custom ][] = array(
									$menu_location_custom,
									$page_title,
									$menu_label,
									'read',
									'pods-manage-' . $pod['name'],
									array( $this, 'admin_content' ),
								);

								continue;
							} else {
								$pods_pages ++;

								$page        = 'pods-manage-' . $pod['name'];
								$parent_page = $page;

								if ( empty( $menu_position ) ) {
									$menu_position = null;
								}
								add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );

								$all_title = $plural_label;
								$all_label = pods_v( 'label_all_items', $pod['options'], __( 'All', 'pods' ) . ' ' . $plural_label );

								if ( pods_v( 'page' ) === $page ) {
									if ( 'edit' === pods_v( 'action', 'get', 'manage' ) ) {
										$all_title = pods_v( 'label_edit_item', $pod['options'], __( 'Edit', 'pods' ) . ' ' . $singular_label );
									} elseif ( 'add' === pods_v( 'action', 'get', 'manage' ) ) {
										$all_title = pods_v( 'label_add_new_item', $pod['options'], __( 'Add New', 'pods' ) . ' ' . $singular_label );
									}
								}

								add_submenu_page(
									$parent_page, $all_title, $all_label, 'read', $page, array(
										$this,
										'admin_content',
									)
								);
							}//end if
						}//end if

						if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod['name'] ) ) ) {
							$page = 'pods-add-new-' . $pod['name'];

							if ( null === $parent_page ) {
								$pods_pages ++;

								$parent_page = $page;

								if ( empty( $menu_position ) ) {
									$menu_position = null;
								}
								add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );
							}

							$add_title = pods_v( 'label_add_new_item', $pod['options'], __( 'Add New', 'pods' ) . ' ' . $singular_label );
							$add_label = pods_v( 'label_add_new', $pod['options'], __( 'Add New', 'pods' ) );

							add_submenu_page(
								$parent_page, $add_title, $add_label, 'read', $page, array(
									$this,
									'admin_content',
								)
							);
						}//end if
					} else {
						$submenu[] = $pod;
					}//end if
				}//end foreach

				$submenu = apply_filters( 'pods_admin_menu_secondary_content', $submenu );

				if ( ! empty( $submenu ) && ( ! defined( 'PODS_DISABLE_CONTENT_MENU' ) || ! PODS_DISABLE_CONTENT_MENU ) ) {
					$parent_page = null;

					foreach ( $submenu as $item ) {
						$singular_label = pods_v( 'label_singular', $item['options'], pods_v( 'label', $item, ucwords( str_replace( '_', ' ', $item['name'] ) ), true ), true );
						$plural_label   = pods_v( 'label', $item, ucwords( str_replace( '_', ' ', $item['name'] ) ), true );

						if ( pods_is_admin(
							array(
								'pods',
								'pods_content',
								'pods_edit_' . $item['name'],
								'pods_delete_' . $item['name'],
							)
						) ) {
							$page = 'pods-manage-' . $item['name'];

							if ( null === $parent_page ) {
								$parent_page = $page;

								add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, 'dashicons-pods', '58.5' );
							}

							$all_title = $plural_label;
							$all_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

							if ( pods_v( 'page' ) === $page ) {
								if ( 'edit' === pods_v( 'action', 'get', 'manage' ) ) {
									$all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
								} elseif ( 'add' === pods_v( 'action', 'get', 'manage' ) ) {
									$all_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
								}
							}

							add_submenu_page(
								$parent_page, $all_title, $all_label, 'read', $page, array(
									$this,
									'admin_content',
								)
							);
						} elseif ( current_user_can( 'pods_add_' . $item['name'] ) ) {
							$page = 'pods-add-new-' . $item['name'];

							if ( null === $parent_page ) {
								$parent_page = $page;

								add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, 'dashicons-pods', '58.5' );
							}

							$add_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
							$add_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

							add_submenu_page(
								$parent_page, $add_title, $add_label, 'read', $page, array(
									$this,
									'admin_content',
								)
							);
						}//end if
					}//end foreach
				}//end if
			}//end if

			if ( ! empty( $taxonomies ) ) {
				foreach ( (array) $taxonomies as $pod ) {
					// Default taxonomy capability
					$capability = 'manage_categories';

					if ( ! empty( $pod['options']['capability_type'] ) ) {
						if ( 'custom' === $pod['options']['capability_type'] && ! empty( $pod['options']['capability_type_custom'] ) ) {
							$capability = 'manage_' . (string) $pod['options']['capability_type_custom'] . '_terms';
						}
					}

					// Check capabilities.
					if ( ! pods_is_admin(
						array(
							'pods',
							'pods_content',
							'pods_edit_' . $pod['name'],
							$capability,
						)
					) ) {
						continue;
					}

					// Check UI settings
					if ( 1 !== (int) pods_v( 'show_ui', $pod['options'], 0 ) || 1 !== (int) pods_v( 'show_in_menu', $pod['options'], 0 ) ) {
						continue;
					}

					$menu_location = pods_v( 'menu_location', $pod['options'], 'default' );

					if ( 'default' === $menu_location ) {
						continue;
					}

					$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );
					$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

					$menu_label = pods_v( 'menu_name', $pod['options'], $page_title, true );
					$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

					$menu_icon            = pods_evaluate_tags( pods_v( 'menu_icon', $pod['options'], '', true ), true );
					$menu_slug            = 'edit-tags.php?taxonomy=' . $pod['name'];
					$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );

					$menu_position = pods_v( 'menu_position', $pod['options'], '', true );
					if ( empty( $menu_position ) ) {
						$menu_position = null;
					}

					$taxonomy_data = get_taxonomy( $pod['name'] );

					foreach ( (array) $taxonomy_data->object_type as $post_type ) {
						if ( 'post' === $post_type ) {
							remove_submenu_page( 'edit.php', $menu_slug );
						} elseif ( 'attachment' === $post_type ) {
							remove_submenu_page( 'upload.php', $menu_slug . '&amp;post_type=' . $post_type );
						} else {
							remove_submenu_page( 'edit.php?post_type=' . $post_type, $menu_slug . '&amp;post_type=' . $post_type );
						}
					}

					if ( 'settings' === $menu_location ) {
						add_options_page( $page_title, $menu_label, 'read', $menu_slug );
					} elseif ( 'appearances' === $menu_location ) {
						add_theme_page( $page_title, $menu_label, 'read', $menu_slug );
					} elseif ( 'objects' === $menu_location || 'top' === $menu_location ) {
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, '', $menu_icon, $menu_position );
					} elseif ( 'submenu' === $menu_location && ! empty( $menu_location_custom ) ) {
						if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
							$submenu_items[ $menu_location_custom ] = array();
						}

						$submenu_items[ $menu_location_custom ][] = array(
							$menu_location_custom,
							$page_title,
							$menu_label,
							'read',
							$menu_slug,
							'',
						);
					}//end if
				}//end foreach
			}//end if

			if ( ! empty( $settings ) ) {
				foreach ( (array) $settings as $pod ) {
					if ( ! pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod['name'] ) ) ) {
						continue;
					}

					$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );
					$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

					$menu_label = pods_v( 'menu_name', $pod['options'], $page_title, true );
					$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

					$menu_icon = pods_evaluate_tags( pods_v( 'menu_icon', $pod['options'], '', true ), true );

					$menu_position = pods_v( 'menu_position', $pod['options'], '', true );
					if ( empty( $menu_position ) ) {
						$menu_position = null;
					}

					$menu_slug            = 'pods-settings-' . $pod['name'];
					$menu_location        = pods_v( 'menu_location', $pod['options'], 'settings' );
					$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );
					$menu_callback        = array( $this, 'admin_content_settings' );

					if ( 'settings' === $menu_location ) {
						add_options_page( $page_title, $menu_label, 'read', $menu_slug, $menu_callback, $menu_position );
					} elseif ( 'appearances' === $menu_location ) {
						add_theme_page( $page_title, $menu_label, 'read', $menu_slug, $menu_callback, $menu_position );
					} elseif ( 'objects' === $menu_location || 'top' === $menu_location ) {
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, $menu_callback, $menu_icon, $menu_position );
					} elseif ( 'submenu' === $menu_location && ! empty( $menu_location_custom ) ) {
						if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
							$submenu_items[ $menu_location_custom ] = array();
						}

						$submenu_items[ $menu_location_custom ][] = array(
							$menu_location_custom,
							$page_title,
							$menu_label,
							'read',
							$menu_slug,
							$menu_callback,
							$menu_position,
						);
					}//end if
				}//end foreach
			}//end if

			foreach ( $submenu_items as $items ) {
				foreach ( $items as $item ) {
					call_user_func_array( 'add_submenu_page', $item );
				}
			}

			$admin_menus = array(
				'pods'            => array(
					'label'    => __( 'Edit Pods', 'pods' ),
					'function' => array( $this, 'admin_setup' ),
					'access'   => 'pods',
				),
				'pods-add-new'    => array(
					'label'    => __( 'Add New', 'pods' ),
					'function' => array( $this, 'admin_setup' ),
					'access'   => 'pods',
				),
				'pods-components' => array(
					'label'    => __( 'Components', 'pods' ),
					'function' => array( $this, 'admin_components' ),
					'access'   => 'pods_components',
				),
				'pods-settings'   => array(
					'label'    => __( 'Settings', 'pods' ),
					'function' => array( $this, 'admin_settings' ),
					'access'   => 'pods_settings',
				),
				'pods-help'       => array(
					'label'    => __( 'Help', 'pods' ),
					'function' => array( $this, 'admin_help' ),
				),
			);

			if ( empty( $all_pods ) ) {
				unset( $admin_menus['pods'] );
			}

			add_filter( 'parent_file', array( $this, 'parent_file' ) );
		} else {
			$admin_menus = array(
				'pods-upgrade'  => array(
					'label'    => __( 'Upgrade', 'pods' ),
					'function' => array( $this, 'admin_upgrade' ),
					'access'   => 'manage_options',
				),
				'pods-settings' => array(
					'label'    => __( 'Settings', 'pods' ),
					'function' => array( $this, 'admin_settings' ),
					'access'   => 'pods_settings',
				),
				'pods-help'     => array(
					'label'    => __( 'Help', 'pods' ),
					'function' => array( $this, 'admin_help' ),
				),
			);

			add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
		}//end if

		/**
		 * Add or change Pods Admin menu items
		 *
		 * @param array $admin_menus The submenu items in Pods Admin menu.
		 *
		 * @since  unknown
		 */
		$admin_menus = apply_filters( 'pods_admin_menu', $admin_menus );

		$parent = false;

		// PODS_LIGHT disables all Pods components so remove the components menu
		if ( pods_light() ) {
			unset( $admin_menus['pods-components'] );
		}

		if ( ! empty( $admin_menus ) && ( ! defined( 'PODS_DISABLE_ADMIN_MENU' ) || ! PODS_DISABLE_ADMIN_MENU ) ) {
			foreach ( $admin_menus as $page => $menu_item ) {
				if ( ! pods_is_admin( pods_v( 'access', $menu_item ) ) ) {
					continue;
				}

				// Don't just show the help page
				if ( false === $parent && 'pods-help' === $page ) {
					continue;
				}

				if ( ! isset( $menu_item['label'] ) ) {
					$menu_item['label'] = $page;
				}

				if ( false === $parent ) {
					$parent = $page;

					$menu = __( 'Pods Admin', 'pods' );

					if ( 'pods-upgrade' === $parent ) {
						$menu = __( 'Pods Upgrade', 'pods' );
					}

					add_menu_page( $menu, $menu, 'read', $parent, null, 'dashicons-pods' );
				}

				add_submenu_page( $parent, $menu_item['label'], $menu_item['label'], 'read', $page, $menu_item['function'] );

				if ( 'pods-components' === $page && is_object( PodsInit::$components ) ) {
					PodsInit::$components->menu( $parent );
				}
			}//end foreach
		}//end if
	}

	/**
	 * Set the correct parent_file to highlight the correct top level menu
	 *
	 * @param string $parent_file The parent file.
	 *
	 * @return mixed|string
	 *
	 * @since unknown
	 */
	public function parent_file( $parent_file ) {

		global $current_screen;

		if ( isset( $current_screen ) && ! empty( $current_screen->taxonomy ) ) {
			$taxonomies = PodsMeta::$taxonomies;
			if ( ! empty( $taxonomies ) ) {
				foreach ( (array) $taxonomies as $pod ) {
					if ( $current_screen->taxonomy !== $pod['name'] ) {
						continue;
					}

					$menu_slug            = 'edit-tags.php?taxonomy=' . $pod['name'];
					$menu_location        = pods_v( 'menu_location', $pod['options'], 'default' );
					$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );

					if ( 'settings' === $menu_location ) {
						$parent_file = 'options-general.php';
					} elseif ( 'appearances' === $menu_location ) {
						$parent_file = 'themes.php';
					} elseif ( 'objects' === $menu_location ) {
						$parent_file = $menu_slug;
					} elseif ( 'top' === $menu_location ) {
						$parent_file = $menu_slug;
					} elseif ( 'submenu' === $menu_location && ! empty( $menu_location_custom ) ) {
						$parent_file = $menu_location_custom;
					}

					break;
				}//end foreach
			}//end if
		}//end if

		if ( isset( $current_screen ) && ! empty( $current_screen->post_type ) && is_object( PodsInit::$components ) ) {
			global $submenu_file;

			$components = PodsInit::$components->components;

			foreach ( $components as $component => $component_data ) {
				if ( ! empty( $component_data['MenuPage'] ) && $parent_file === $component_data['MenuPage'] ) {
					$parent_file = 'pods';

					// @codingStandardsIgnoreLine
					$submenu_file = $component_data['MenuPage'];
				}
			}
		}

		return $parent_file;
	}

	/**
	 * Show upgrade notice.
	 */
	public function upgrade_notice() {

		echo '<div class="error fade"><p>';
		// @codingStandardsIgnoreLine
		echo sprintf( __( '<strong>NOTICE:</strong> Pods %1$s requires your action to complete the upgrade. Please run the <a href="%2$s">Upgrade Wizard</a>.', 'pods' ), esc_html( PODS_VERSION ), esc_url( admin_url( 'admin.php?page=pods-upgrade' ) ) );
		echo '</p></div>';
	}

	/**
	 * Create PodsUI content for the administration pages
	 */
	public function admin_content() {

		// @codingStandardsIgnoreLine
		$pod_name = str_replace( array( 'pods-manage-', 'pods-add-new-' ), '', $_GET['page'] );

		$pod = pods( $pod_name, pods_v( 'id', 'get', null, true ) );

		// @codingStandardsIgnoreLine
		if ( false !== strpos( $_GET['page'], 'pods-add-new-' ) ) {
			// @codingStandardsIgnoreLine
			$_GET['action'] = pods_v( 'action', 'get', 'add' );
		}

		$pod->ui();
	}

	/**
	 * Create PodsUI content for the settings administration pages
	 */
	public function admin_content_settings() {

		// @codingStandardsIgnoreLine
		$pod_name = str_replace( 'pods-settings-', '', $_GET['page'] );

		$pod = pods( $pod_name );

		if ( 'custom' !== pods_v( 'ui_style', $pod->pod_data['options'], 'settings', true ) ) {
			$actions_disabled = array(
				'manage'    => 'manage',
				'add'       => 'add',
				'delete'    => 'delete',
				'duplicate' => 'duplicate',
				'view'      => 'view',
				'export'    => 'export',
			);

			// @codingStandardsIgnoreLine
			$_GET['action'] = 'edit';

			$page_title = pods_v( 'label', $pod->pod_data, ucwords( str_replace( '_', ' ', $pod->pod_data['name'] ) ), true );
			$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod->pod_data );

			$ui = array(
				'pod'              => $pod,
				'fields'           => array(
					'edit' => $pod->pod_data['fields'],
				),
				'header'           => array(
					'edit' => $page_title,
				),
				'label'            => array(
					'edit' => __( 'Save Changes', 'pods' ),
				),
				'style'            => pods_v( 'ui_style', $pod->pod_data['options'], 'settings', true ),
				'icon'             => pods_evaluate_tags( pods_v( 'menu_icon', $pod->pod_data['options'] ), true ),
				'actions_disabled' => $actions_disabled,
			);

			$pod_pod_name = $pod->pod;

			$ui = apply_filters( "pods_admin_ui_{$pod_pod_name}", apply_filters( 'pods_admin_ui', $ui, $pod->pod, $pod ), $pod->pod, $pod );

			// Force disabled actions, do not pass go, do not collect $two_hundred
			$ui['actions_disabled'] = $actions_disabled;

			pods_ui( $ui );
		} else {
			$pod_pod_name = $pod->pod;
			do_action( 'pods_admin_ui_custom', $pod );
			do_action( "pods_admin_ui_custom_{$pod_pod_name}", $pod );
		}//end if
	}

	/**
	 * Add media button for Pods shortcode
	 *
	 * @param string $context Media button context.
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
		 * @param bool   $show_button Set to false to block the shortcode button from appearing.
		 * @param string $context     Media button context.
		 *
		 * @since 2.3.19
		 */
		if ( ! apply_filters( 'pods_admin_media_button', true, $context ) ) {
			return '';
		}

		$current_page = basename( $_SERVER['PHP_SELF'] );
		$current_page = explode( '?', $current_page );
		$current_page = explode( '#', $current_page[0] );
		$current_page = $current_page[0];

		// Only show the button on post type pages
		if ( ! in_array(
			$current_page, array(
				'post-new.php',
				'post.php',
			), true
		) ) {
			return '';
		}

		add_action( 'admin_footer', array( $this, 'mce_popup' ) );

		echo '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox button" id="add_pod_button" title="Pods Shortcode"><img style="padding: 0px 6px 0px 0px; margin: -3px 0px 0px;" src="' . esc_url( PODS_URL . 'ui/images/icon16.png' ) . '" alt="' . esc_attr__( 'Pods Shortcode', 'pods' ) . '" />' . esc_html__( 'Pods Shortcode', 'pods' ) . '</a>';
	}

	/**
	 * Enqueue assets for Media Library Popup
	 */
	public function register_media_assets() {

		if ( 'pods_media_attachment' === pods_v( 'inlineId' ) ) {
			wp_enqueue_style( 'pods-styles' );
		}
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

		$pods = pods_api()->load_pods( array( 'fields' => false ) );

		$view = pods_v( 'view', 'get', 'all', true );

		// @codingStandardsIgnoreLine
		if ( empty( $pods ) && ! isset( $_GET['action'] ) ) {
			// @codingStandardsIgnoreLine
			$_GET['action'] = 'add';
		}

		// @codingStandardsIgnoreLine
		if ( 'pods-add-new' === $_GET['page'] ) {
			// @codingStandardsIgnoreLine
			if ( isset( $_GET['action'] ) && 'add' !== $_GET['action'] ) {
				pods_redirect(
					pods_query_arg(
						array(
							'page'   => 'pods',
							// @codingStandardsIgnoreLine
							'action' => $_GET['action'],
						)
					)
				);
			} else {
				// @codingStandardsIgnoreLine
				$_GET['action'] = 'add';
			}
			// @codingStandardsIgnoreLine
		} elseif ( isset( $_GET['action'] ) && 'add' === $_GET['action'] ) {
			pods_redirect(
				pods_query_arg(
					array(
						'page'   => 'pods-add-new',
						'action' => '',
					)
				)
			);
		}//end if

		$types = array(
			'post_type' => __( 'Post Type (extended)', 'pods' ),
			'taxonomy'  => __( 'Taxonomy (extended)', 'pods' ),
			'cpt'       => __( 'Custom Post Type', 'pods' ),
			'ct'        => __( 'Custom Taxonomy', 'pods' ),
			'user'      => __( 'User (extended)', 'pods' ),
			'media'     => __( 'Media (extended)', 'pods' ),
			'comment'   => __( 'Comments (extended)', 'pods' ),
			'pod'       => __( 'Advanced Content Type', 'pods' ),
			'settings'  => __( 'Custom Settings Page', 'pods' ),
		);

		$row = false;

		$pod_types_found = array();

		$fields = array(
			'label'       => array( 'label' => __( 'Label', 'pods' ) ),
			'name'        => array( 'label' => __( 'Name', 'pods' ) ),
			'type'        => array( 'label' => __( 'Type', 'pods' ) ),
			'storage'     => array(
				'label' => __( 'Storage Type', 'pods' ),
				'width' => '10%',
			),
			'field_count' => array(
				'label' => __( 'Number of Fields', 'pods' ),
				'width' => '8%',
			),
		);

		$total_fields = 0;

		foreach ( $pods as $k => $pod ) {
			if ( isset( $types[ $pod['type'] ] ) ) {
				if ( in_array(
					$pod['type'], array(
						'post_type',
						'taxonomy',
					), true
				) ) {
					if ( empty( $pod['object'] ) ) {
						if ( 'post_type' === $pod['type'] ) {
							$pod['type'] = 'cpt';
						} else {
							$pod['type'] = 'ct';
						}
					}
				}

				if ( ! isset( $pod_types_found[ $pod['type'] ] ) ) {
					$pod_types_found[ $pod['type'] ] = 1;
				} else {
					$pod_types_found[ $pod['type'] ] ++;
				}

				if ( 'all' !== $view && $view !== $pod['type'] ) {
					unset( $pods[ $k ] );

					continue;
				}

				$pod['real_type'] = $pod['type'];
				$pod['type']      = $types[ $pod['type'] ];
			} elseif ( 'all' !== $view ) {
				continue;
			}//end if

			$pod['storage'] = ucwords( $pod['storage'] );

			// @codingStandardsIgnoreLine
			if ( $pod['id'] == pods_v( 'id' ) && 'delete' !== pods_v( 'action' ) ) {
				$row = $pod;
			}

			$pod = array(
				'id'          => $pod['id'],
				'label'       => pods_v( 'label', $pod ),
				'name'        => pods_v( 'name', $pod ),
				'object'      => pods_v( 'object', $pod ),
				'type'        => pods_v( 'type', $pod ),
				'real_type'   => pods_v( 'real_type', $pod ),
				'storage'     => pods_v( 'storage', $pod ),
				'field_count' => count( $pod['fields'] ),
			);

			$total_fields += $pod['field_count'];

			$pods[ $k ] = $pod;
		}//end foreach

		if ( false === $row && 0 < pods_v( 'id' ) && 'delete' !== pods_v( 'action' ) ) {
			pods_message( 'Pod not found', 'error' );

			// @codingStandardsIgnoreLine
			unset( $_GET['id'], $_GET['action'] );
		}

		$ui = array(
			'data'             => $pods,
			'row'              => $row,
			'total'            => count( $pods ),
			'total_found'      => count( $pods ),
			'items'            => 'Pods',
			'item'             => 'Pod',
			'fields'           => array(
				'manage' => $fields,
			),
			'actions_disabled' => array( 'view', 'export' ),
			'actions_custom'   => array(
				'add'       => array( $this, 'admin_setup_add' ),
				'edit'      => array( $this, 'admin_setup_edit' ),
				'duplicate' => array(
					'callback'          => array( $this, 'admin_setup_duplicate' ),
					'restrict_callback' => array( $this, 'admin_setup_duplicate_restrict' ),
				),
				'reset'     => array(
					'label'             => __( 'Delete All Items', 'pods' ),
					'confirm'           => __( 'Are you sure you want to delete all items from this Pod? If this is an extended Pod, it will remove the original items extended too.', 'pods' ),
					'callback'          => array( $this, 'admin_setup_reset' ),
					'restrict_callback' => array( $this, 'admin_setup_reset_restrict' ),
					'nonce'             => true,
				),
				'delete'    => array( $this, 'admin_setup_delete' ),
			),
			'action_links'     => array(
				'add' => pods_query_arg(
					array(
						'page'   => 'pods-add-new',
						'action' => '',
						'id'     => '',
						'do'     => '',
					)
				),
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => true,
			'pagination'       => false,
			'extra'            => array(
				'total' => ', ' . number_format_i18n( $total_fields ) . ' ' . _n( 'field', 'fields', $total_fields, 'pods' ),
			),
		);

		if ( 1 < count( $pod_types_found ) ) {
			$ui['views']            = array( 'all' => __( 'All', 'pods' ) );
			$ui['view']             = $view;
			$ui['heading']          = array( 'views' => __( 'Type', 'pods' ) );
			$ui['filters_enhanced'] = true;

			foreach ( $pod_types_found as $pod_type => $number_found ) {
				$ui['views'][ $pod_type ] = $types[ $pod_type ];
			}
		}

		// Add our custom callouts.
		$this->handle_callouts_updates();

		add_filter( 'pods_ui_manage_custom_container_classes', array( $this, 'admin_manage_container_class' ) );
		add_action( 'pods_ui_manage_after_container', array( $this, 'admin_manage_callouts' ) );

		pods_ui( $ui );
	}

	/**
	 * Get list of callouts to show.
	 *
	 * @since 2.7.17
	 *
	 * @return array List of callouts.
	 */
	public function get_callouts() {
		$force_callouts = false;

		$page = pods_v( 'page' );

		if ( in_array( $page, array( 'pods-settings', 'pods-help' ), true ) ) {
			$force_callouts = true;
		}

		$callouts = get_option( 'pods_callouts' );

		if ( ! $callouts ) {
			$callouts = array(
				'friends_2020' => 1,
			);
		}

		// Handle Friends of Pods 2020 callout logic.
		$callouts['friends_2020'] = ! isset( $callouts['friends_2020'] ) || $callouts['friends_2020'] || $force_callouts ? 1 : 0;

		/**
		 * Allow hooking into whether or not the specific callouts should show.
		 *
		 * @since 2.7.17
		 *
		 * @param array List of callouts to enable.
		 */
		$callouts = apply_filters( 'pods_admin_callouts', $callouts );

		return $callouts;
	}

	/**
	 * Handle callouts update logic.
	 *
	 * @since 2.7.17
	 */
	public function handle_callouts_updates() {
		$callouts = get_option( 'pods_callouts' );

		if ( ! $callouts ) {
			$callouts = array();
		}

		$disable_pods = pods_v( 'pods_callout_dismiss' );

		// Disable Friends of Pods 2020 callout.
		if ( 'friends_2020' === $disable_pods ) {
			$callouts['friends_2020'] = 0;

			update_option( 'pods_callouts', $callouts );
		} elseif ( 'reset' === $disable_pods ) {
			$callouts = array();

			update_option( 'pods_callouts', $callouts );
		}
	}

	/**
	 * Add class to container if we have callouts to show.
	 *
	 * @since 2.7.17
	 *
	 * @param array $classes List of classes to use.
	 *
	 * @return array List of classes to use.
	 */
	public function admin_manage_container_class( $classes ) {
		$callouts = $this->get_callouts();

		// Only get enabled callouts.
		$callouts = array_filter( $callouts );

		if ( ! empty( $callouts ) ) {
			$classes[] = 'pods-admin--flex';
		}

		return $classes;
	}

	/**
	 * Add callouts to let admins know about certain things.
	 *
	 * @since 2.7.17
	 */
	public function admin_manage_callouts() {
		$force_callouts = false;

		$page = pods_v( 'page' );

		if ( in_array( $page, array( 'pods-settings', 'pods-help' ), true ) ) {
			$force_callouts = true;
		}

		$callouts = $this->get_callouts();

		if ( ! empty( $callouts['friends_2020'] ) ) {
?>
		<div class="pods-admin_friends-callout_container">
			<?php if ( ! $force_callouts ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'pods_callout_dismiss', 'friends_2020' ) ); ?>" class="pods-admin_friends-callout_close">
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 125" enable-background="new 0 0 100 100" xml:space="preserve"><polygon points="95,17 83,5 50,38 17,5 5,17 38,50 5,83 17,95 50,62 83,95 95,83 62,50 "/></svg>
				</a>
			<?php endif; ?>
			<div class="pods-admin_friends-callout_logo-container">
				<svg version="1.1" viewBox="0 0 305 111" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>Friends of Pods Logo</title> <defs> <path id="a" d="m0.14762 49.116c0 27.103 21.919 49.075 48.956 49.075 19.888 0 37.007-11.888 44.669-28.962-2.1342-6.538-3.9812-18.041 3.3854-29.538-0.3152-1.624-0.71019-3.219-1.1807-4.781-22.589 22.49-40.827 24.596-54.558 24.229-0.12701-4e-3 -0.58883-0.079-0.71152-0.35667 0 0-0.20016-0.89933 0.38502-0.89933 26.307 0 41.29-15.518 53.531-26.865-0.66763-1.687-1.4264-3.3273-2.2695-4.9167-13.196-3.6393-18.267-14.475-20.067-20.221-6.9007-3.7267-14.796-5.8413-23.184-5.8413-27.037 0-48.956 21.972-48.956 49.076zm62.283-34.287s18.69-2.039 24.194 21.114c-20.424-1.412-24.194-21.114-24.194-21.114zm-7.4779 9.7423s16.57-0.55467 20.342 20.219c-17.938-2.602-20.342-20.219-20.342-20.219zm-9.1018 8.256s14.912-0.97567 18.728 17.626c-16.209-1.8273-18.728-17.626-18.728-17.626zm40.774 3.116c0.021279 0.0013333 0.040563 0.0026667 0.061842 0.0043333l-0.044886 0.067667c-0.0056523-0.024-0.011304-0.048-0.016957-0.072zm-48.709 3.445s13.103-0.859 16.456 15.486c-14.243-1.6047-16.456-15.486-16.456-15.486zm-10.498 1.6027s12.587-0.82333 15.808 14.877c-13.68-1.5417-15.808-14.877-15.808-14.877zm-10.391 0.72233s11.423-0.74667 14.347 13.502c-12.417-1.3997-14.347-13.502-14.347-13.502zm-11.298 2.0857s9.5832-2.526 13.712 9.0523c-0.44919 0.036667-0.88474 0.054333-1.3073 0.054333-9.6148-3.333e-4 -12.405-9.1067-12.405-9.1067zm81.565 0.964c-0.0073147-0.021333-0.014962-0.043667-0.021612-0.064667l0.066497 0.018333c-0.014962 0.015333-0.029924 0.030667-0.044886 0.046333 7.1338 21.557-5.5721 33.29-5.5721 33.29s-8.9279-18.395 5.5721-33.29zm-11.999 0.027333c0.017622 0.0023333 0.035576 5e-3 0.053198 0.0076667l-0.041561 0.056c-0.0036573-0.021667-0.0073147-0.042-0.011637-0.063667zm-10.716 5.6627c0.015959 2e-3 0.032916 0.0036666 0.048543 0.0056666l-0.036573 0.051c-0.0039899-0.019667-0.0076472-0.037333-0.01197-0.056667zm12.212 1.8693c-0.0023274-0.021-0.0046548-0.043333-0.0066497-0.064667l0.056523 0.035667c-0.016624 0.01-0.032916 0.019333-0.049873 0.029 2.2323 21.678-11.682 28.793-11.682 28.793s-4.4962-19.303 11.682-28.793zm-57.318 0.52633-0.018619 0.037333c-0.0039899-0.011667-0.0086447-0.023667-0.012302-0.034667 0.010307-1e-3 0.020614-0.0016667 0.030921-0.0026667zm34.9 2.0257c0.013964 0.0013333 0.027264 3e-3 0.041561 0.0043333l-0.031254 0.046333c-0.0036573-0.017-0.0069822-0.034-0.010307-0.050667zm-22.998 0.34067c0.012302 0.0016666 0.025269 3e-3 0.037571 0.0043333l-0.028594 0.039c-0.0029924-0.014667-0.0056523-0.028667-0.0089771-0.043333zm-16.9 3.542s8.0594-7.693 18.254 0.88467c-3.1137 2.6673-6.0605 3.5853-8.642 3.5853-5.6965 0-9.6125-4.47-9.6125-4.47zm28.752-2.8893c0.013632 0.0013334 0.027264 3e-3 0.040896 0.0043334l-0.030589 0.045c-0.0033249-0.016-0.0069822-0.033333-0.010307-0.049333zm21.183 2.4917c3.325e-4 -0.019 3.325e-4 -0.039333 6.65e-4 -0.058333l0.046548 0.039667c-0.015959 0.0063334-0.031254 0.012333-0.047213 0.018667-0.19118 19.436-13.172 23.733-13.172 23.733s-2.0415-17.662 13.172-23.733zm-31.651 1.257v0.05c-0.0099746-0.0083333-0.019617-0.016333-0.029259-0.024667 0.0093096-0.0086666 0.019284-0.017 0.029259-0.025333zm-6.6234 10.543s4.296-11.202 16.634-9.0197c-3.5273 8.062-9.0759 9.5947-12.782 9.5947-2.2719 0-3.8515-0.575-3.8515-0.575zm16.651-9.0593 0.018287 0.046c-0.01197-2e-3 -0.023606-0.0043334-0.035576-0.0063334 0.0059848-0.013333 0.011637-0.026333 0.017289-0.039667zm10.12 0.685c0.0029923-0.016333 0.0059847-0.033333 0.0089771-0.049667l0.034911 0.043c-0.014629 0.0023333-0.029259 0.0046667-0.043888 0.0066667-2.9518 16.71-14.755 17.762-14.755 17.762s0.77369-15.639 14.755-17.762z"/> </defs> <g fill="none" fill-rule="evenodd"> <g transform="translate(6.3172 6.3333)"> <mask id="b" fill="white"> <use xlink:href="#a"/> </mask> <polygon points="-3.1772 -3.2937 100.48 -3.2937 100.48 101.52 -3.1772 101.52" fill="#fff" mask="url(#b)"/> </g> <path d="m55.303 3.569c-28.538 0-51.754 23.273-51.754 51.88 0 28.607 23.216 51.88 51.754 51.88 28.538 0 51.754-23.273 51.754-51.88 0-28.607-23.217-51.88-51.754-51.88m0 107.18c-30.417 0-55.163-24.807-55.163-55.298 0-30.492 24.746-55.298 55.163-55.298 30.417 0 55.164 24.807 55.164 55.298 0 30.491-24.747 55.298-55.164 55.298" fill="#fff"/> <path d="m137.42 77.263-2.7699 22.725h-14.958l8.2174-67.434h22.252c4.001 0 7.4249 0.48597 10.272 1.4579 2.8469 0.97195 5.1859 2.3141 7.0171 4.0266 1.8312 1.7125 3.1777 3.7335 4.0395 6.0631 0.86176 2.3296 1.2926 4.8366 1.2926 7.521 0 3.6718-0.56167 7.0736-1.685 10.205-1.1234 3.1318-2.8392 5.8394-5.1474 8.1227s-5.232 4.0729-8.7714 5.3688-7.725 1.9439-12.557 1.9439h-7.2018zm4.1549-33.463-2.7238 22.123h7.248c2.1544 0 3.9625-0.31626 5.4244-0.9488 1.4619-0.63254 2.6468-1.5273 3.5547-2.6844 0.90792-1.1571 1.5619-2.5378 1.962-4.1423 0.4001-1.6045 0.60015-3.3632 0.60015-5.2763 0-1.3268-0.17696-2.5456-0.5309-3.6564-0.35394-1.1108-0.90022-2.0673-1.6389-2.8695-0.73865-0.80224-1.6696-1.4271-2.793-1.8745-1.1234-0.4474-2.4544-0.6711-3.9933-0.6711h-7.1095zm48.956 46.283c1.6004 0 3.0315-0.48597 4.2934-1.4579 1.2619-0.97195 2.3313-2.2987 3.2085-3.9803 0.87714-1.6816 1.5542-3.6409 2.0313-5.8779 0.47704-2.237 0.71556-4.6206 0.71556-7.1507 0-3.4867-0.46934-5.9782-1.408-7.4747-0.9387-1.4965-2.4698-2.2447-4.5934-2.2447-1.6004 0-3.0315 0.48597-4.2934 1.4579-1.2619 0.97195-2.3313 2.291-3.2085 3.9572s-1.5542 3.6255-2.0313 5.8779c-0.47704 2.2525-0.71556 4.6437-0.71556 7.1739 0 3.425 0.46934 5.9011 1.408 7.4284 0.9387 1.5273 2.4698 2.291 4.5934 2.291zm-1.2926 10.645c-2.6776 0-5.1782-0.4474-7.5019-1.3422-2.3237-0.89481-4.3395-2.1984-6.0477-3.9109-1.7081-1.7125-3.0469-3.8261-4.0164-6.3408-0.96948-2.5147-1.4542-5.4074-1.4542-8.6781 0-4.2581 0.70017-8.1689 2.1005-11.733 1.4004-3.5638 3.2854-6.6416 5.6552-9.2335s5.1166-4.6129 8.2405-6.0631 6.4093-2.1753 9.8563-2.1753c2.6776 0 5.1705 0.4474 7.4788 1.3422 2.3083 0.89481 4.3164 2.1984 6.0246 3.9109 1.7081 1.7125 3.0546 3.8261 4.0395 6.3408 0.98486 2.5147 1.4773 5.4074 1.4773 8.6781 0 4.1963-0.70017 8.0764-2.1005 11.64-1.4004 3.5638-3.2854 6.6493-5.6552 9.2566-2.3698 2.6073-5.1166 4.6437-8.2405 6.1094-3.1239 1.4656-6.4093 2.1984-9.8563 2.1984zm62.6-0.74053c-0.89253 0-1.6389-0.11571-2.239-0.34712-0.60015-0.23142-1.0772-0.54768-1.4311-0.9488s-0.60015-0.87937-0.73864-1.4348c-0.1385-0.5554-0.20774-1.1571-0.20774-1.805v-4.0266c-2.1852 2.9004-4.6011 5.176-7.248 6.8267-2.6468 1.6508-5.4629 2.4761-8.4482 2.4761-1.9697 0-3.7702-0.34712-5.4013-1.0414-1.6312-0.69425-3.0392-1.7819-4.2241-3.263s-2.1082-3.3709-2.7699-5.6697c-0.66171-2.2987-0.99255-5.0525-0.99255-8.2615 0-2.777 0.29238-5.4845 0.87714-8.1227 0.58476-2.6381 1.4003-5.1143 2.4468-7.4284s2.2929-4.4354 3.7394-6.3639c1.4465-1.9285 3.0392-3.5792 4.7781-4.9523 1.7389-1.3731 3.5778-2.4453 5.5168-3.2167s3.9394-1.1571 6.0015-1.1571c1.9697 0 3.7471 0.33169 5.3321 0.99508 1.585 0.66339 2.9777 1.535 4.178 2.615l3.0469-24.16h14.034l-8.5867 69.286h-7.6634zm-14.588-10.182c0.98486 0 2.0082-0.37026 3.07-1.1108s2.0928-1.7587 3.0931-3.0547 1.9312-2.8155 2.793-4.5589c0.86176-1.7433 1.6004-3.6178 2.2159-5.6234l1.4773-11.663c-0.89253-0.70968-1.8851-1.2188-2.9777-1.5273s-2.1313-0.46283-3.1162-0.46283c-1.662 0-3.1931 0.55539-4.5934 1.6662s-2.6083 2.5687-3.624 4.3737c-1.0156 1.805-1.8081 3.8646-2.3775 6.1788s-0.85406 4.6746-0.85406 7.0813c0 3.0547 0.44626 5.2685 1.3388 6.6416 0.89253 1.3731 2.0774 2.0596 3.5547 2.0596zm63.465-27.724c-0.43088 0.5554-0.83097 0.95651-1.2003 1.2034s-0.86175 0.37026-1.4773 0.37026-1.2234-0.13885-1.8235-0.41655-1.2465-0.59396-1.9389-0.9488c-0.69248-0.35484-1.4773-0.6711-2.3544-0.9488-0.87714-0.2777-1.8851-0.41655-3.0238-0.41655-2.1236 0-3.6394 0.40883-4.5473 1.2265-0.90792 0.81767-1.3619 1.8745-1.3619 3.1704 0 0.8331 0.25391 1.5428 0.76173 2.129 0.50782 0.58625 1.1772 1.1108 2.0082 1.5736 0.83098 0.46283 1.7774 0.88709 2.8392 1.2728 1.0618 0.38569 2.1467 0.81766 3.2547 1.2959 1.108 0.47826 2.1928 1.0259 3.2547 1.643s2.0082 1.3731 2.8392 2.2679c0.83098 0.89481 1.5004 1.9593 2.0082 3.1935 0.50782 1.2342 0.76173 2.6998 0.76173 4.3969 0 2.4067-0.47704 4.6823-1.4311 6.8267-0.95409 2.1445-2.3236 4.0112-4.1087 5.6002-1.7851 1.5891-3.9471 2.8541-6.4862 3.7952-2.5391 0.94109-5.3936 1.4116-8.5637 1.4116-1.5081 0-3.0007-0.15428-4.478-0.46283-1.4773-0.30856-2.8699-0.7251-4.178-1.2496s-2.5006-1.1494-3.5778-1.8745c-1.0772-0.7251-1.9543-1.5042-2.6314-2.3373l3.5086-5.2763c0.43088-0.61711 0.931-1.1031 1.5004-1.4579 0.56938-0.35484 1.2695-0.53225 2.1005-0.53225 0.76942 0 1.4311 0.18513 1.9851 0.5554 0.55399 0.37027 1.1541 0.77138 1.8004 1.2034s1.4157 0.83309 2.3083 1.2034c0.89253 0.37027 2.062 0.5554 3.5086 0.5554 2.0313 0 3.5393-0.45511 4.5242-1.3653 0.98486-0.91024 1.4773-1.9824 1.4773-3.2167 0-0.95652-0.25391-1.7433-0.76173-2.3604-0.50782-0.61711-1.1772-1.1494-2.0082-1.5968-0.83098-0.4474-1.7697-0.84852-2.8161-1.2034-1.0464-0.35484-2.1236-0.74824-3.2316-1.1802s-2.1852-0.93337-3.2316-1.5042c-1.0464-0.57083-1.9851-1.2882-2.8161-2.1522-0.83098-0.86395-1.5004-1.9285-2.0082-3.1935-0.50782-1.2651-0.76173-2.7924-0.76173-4.582 0-2.2216 0.40779-4.3814 1.2234-6.4796s2.0313-3.9572 3.6471-5.5771c1.6158-1.6199 3.624-2.9235 6.0246-3.9109s5.2013-1.4811 8.4021-1.4811c3.2008 0 6.0553 0.55539 8.5637 1.6662 2.5083 1.1108 4.578 2.4684 6.2092 4.0729l-3.6932 5.0911z" fill="#fff"/> <g transform="translate(128.86 3.5426)" fill="#fff"> <path d="m14.411 3.7958h-8.3873l-0.77821 6.399h7.0759l-0.3891 3.0104h-7.0471l-1.052 8.6095h-3.7613l2.5652-21.029h12.149l-0.37469 3.0104zm-0.59086 18.019 1.8014-14.936h1.8014c0.34587 0 0.62929 0.077562 0.85026 0.23269 0.22097 0.15513 0.33146 0.40236 0.33146 0.7417v0.050901c0 0.033934-0.0048037 0.13331-0.014411 0.29813s-0.019215 0.42417-0.028822 0.77806c-0.0096075 0.35388-0.028822 0.85077-0.057645 1.4907 0.60527-1.1732 1.2682-2.0797 1.9887-2.7196 0.72056-0.6399 1.4699-0.95985 2.2482-0.95985 0.39391 0 0.79262 0.087258 1.1961 0.26178l-0.6485 3.4322c-0.48038-0.2036-0.93673-0.30541-1.3691-0.30541-0.85507 0-1.6044 0.41205-2.2482 1.2362-0.6437 0.82411-1.1721 2.1136-1.5852 3.8685l-0.77821 6.5299h-3.4875zm16.083-14.921-1.7726 14.921h-3.5163l1.7726-14.921h3.5163zm0.8935-4.3484c0 0.31025-0.06485 0.60111-0.19455 0.87259-0.1297 0.27147-0.29783 0.50901-0.50439 0.71261-0.20656 0.2036-0.44434 0.366-0.71336 0.48719-0.26901 0.12119-0.54762 0.18179-0.83585 0.18179-0.27862 0-0.55003-0.060596-0.81423-0.18179-0.26421-0.12119-0.49719-0.28359-0.69894-0.48719s-0.36268-0.44114-0.48278-0.71261c-0.12009-0.27147-0.18014-0.56233-0.18014-0.87259s0.062448-0.60596 0.18735-0.88713 0.28822-0.52597 0.48998-0.73443c0.20176-0.20845 0.43474-0.37327 0.69894-0.49447 0.26421-0.12119 0.53562-0.18179 0.81423-0.18179 0.28823 0 0.56684 0.060596 0.83585 0.18179 0.26901 0.12119 0.50679 0.28359 0.71336 0.48719 0.20656 0.2036 0.37229 0.44599 0.49719 0.72716s0.18735 0.58172 0.18735 0.90167zm13.402 7.8097c0 0.66899-0.1321 1.2798-0.39631 1.8324-0.26421 0.55264-0.73737 1.0471-1.4195 1.4834-0.68213 0.4363-1.6068 0.81199-2.7742 1.1271-1.1673 0.3151-2.6541 0.56476-4.4603 0.74897v0.18906c0 2.3463 0.98476 3.5194 2.9543 3.5194 0.42273 0 0.79742-0.041205 1.1241-0.12362s0.61248-0.18179 0.85747-0.29813 0.46596-0.24723 0.66292-0.39266c0.19695-0.14543 0.37949-0.27632 0.54763-0.39266 0.16813-0.11635 0.33386-0.21572 0.49719-0.29813 0.16333-0.082411 0.34106-0.12362 0.53322-0.12362 0.11529 0 0.23058 0.026662 0.34587 0.079987 0.11529 0.053325 0.21136 0.13331 0.28822 0.23996l0.90791 1.1053c-0.5092 0.51386-1.0088 0.95984-1.4988 1.338-0.48998 0.37812-0.98957 0.68837-1.4988 0.93076-0.5092 0.24239-1.0472 0.42175-1.6141 0.5381-0.56684 0.11635-1.1865 0.17452-1.859 0.17452-0.86468 0-1.6477-0.14785-2.349-0.44356-0.70135-0.29571-1.3018-0.71261-1.8014-1.2507-0.49959-0.5381-0.88629-1.1877-1.1601-1.9488-0.27381-0.76109-0.41072-1.6119-0.41072-2.5523 0-0.78533 0.084065-1.5561 0.2522-2.3124 0.16813-0.75625 0.41072-1.4737 0.72777-2.1524 0.31705-0.67868 0.70615-1.304 1.1673-1.8761 0.46116-0.57203 0.98236-1.0665 1.5636-1.4834 0.58126-0.4169 1.2201-0.7417 1.9167-0.97439 0.69655-0.23269 1.4435-0.34903 2.2409-0.34903 0.77821 0 1.4579 0.1115 2.0392 0.33449 0.58126 0.223 1.0664 0.51143 1.4555 0.86532 0.3891 0.35388 0.67973 0.74897 0.87188 1.1853 0.19215 0.4363 0.28822 0.86289 0.28822 1.2798zm-4.8566-1.1634c-0.48038 0-0.92712 0.099377-1.3402 0.29813-0.41312 0.19876-0.78541 0.4775-1.1169 0.83623-0.33146 0.35873-0.61968 0.78775-0.86467 1.2871-0.24499 0.49932-0.43954 1.0447-0.58365 1.6361 1.1913-0.16482 2.1497-0.34419 2.875-0.5381 0.72537-0.19391 1.2874-0.40721 1.6861-0.6399 0.39871-0.23269 0.66292-0.47992 0.79262-0.7417 0.1297-0.26178 0.19455-0.54294 0.19455-0.8435 0-0.14543-0.031224-0.29571-0.093673-0.45084-0.062449-0.15513-0.15852-0.29329-0.28822-0.41448-0.1297-0.12119-0.29783-0.22299-0.50439-0.30541-0.20656-0.082411-0.45876-0.12362-0.75659-0.12362zm6.0095 12.623 1.7726-14.936h1.8158c0.37469 0 0.66532 0.092105 0.87188 0.27632 0.20656 0.18421 0.30984 0.47992 0.30984 0.88713l-0.10088 1.9342c0.74939-1.115 1.5804-1.9464 2.4931-2.4941 0.91272-0.54779 1.8542-0.82169 2.8246-0.82169 0.54763 0 1.0448 0.099377 1.4916 0.29813s0.82864 0.48962 1.1457 0.87259 0.56204 0.85319 0.73497 1.4107c0.17294 0.55749 0.2594 1.1998 0.2594 1.927 0 0.18421-0.0072055 0.37085-0.021617 0.55991s-0.031224 0.38539-0.050439 0.589l-1.1097 9.4967h-3.5596c0.19215-1.6385 0.35548-3.0274 0.48998-4.1666 0.13451-1.1392 0.24499-2.0869 0.33146-2.8432 0.086468-0.75625 0.15372-1.3501 0.20176-1.7815 0.048038-0.43145 0.084065-0.75866 0.10808-0.98166 0.024019-0.223 0.03843-0.37085 0.043234-0.44356s0.0072056-0.13331 0.0072056-0.18179c0-0.6399-0.11529-1.1029-0.34587-1.3889s-0.59086-0.42902-1.0808-0.42902c-0.39391 0-0.80222 0.11634-1.225 0.34903-0.42273 0.23269-0.82624 0.56233-1.2105 0.98893-0.3843 0.4266-0.73497 0.94045-1.052 1.5416-0.31705 0.60112-0.57645 1.2701-0.77821 2.0069l-0.80703 7.3297h-3.5596zm25.738 0c-0.43234 0-0.73497-0.1018-0.90791-0.30541-0.17294-0.2036-0.2594-0.46053-0.2594-0.77078l0.11529-2.1815c-0.71096 1.0665-1.5084 1.9124-2.3923 2.5378-0.88389 0.62536-1.8254 0.93803-2.8246 0.93803-0.61488 0-1.1745-0.11392-1.6789-0.34176-0.5044-0.22784-0.93433-0.5696-1.2898-1.0253s-0.62929-1.0326-0.82144-1.7306c-0.19215-0.69807-0.28822-1.5222-0.28822-2.4723 0-0.8435 0.086466-1.67 0.2594-2.4796 0.17294-0.80957 0.41792-1.5779 0.73497-2.3051 0.31705-0.72716 0.69414-1.3961 1.1313-2.0069 0.43714-0.61081 0.92472-1.1392 1.4627-1.5852 0.53802-0.44599 1.1121-0.79502 1.7221-1.0471s1.2418-0.37812 1.8951-0.37812c0.66292 0 1.2634 0.12119 1.8014 0.36358 0.53802 0.24239 1.0088 0.57203 1.4123 0.98893l0.96555-7.8097h3.4875l-2.6373 21.611h-1.8879zm-4.8854-2.6032c-0.60527 0-1.076-0.2545-1.4123-0.76351-0.33626-0.50901-0.50439-1.2871-0.50439-2.3342 0-0.80472 0.10088-1.607 0.30264-2.4069 0.20176-0.79988 0.48758-1.5198 0.85747-2.1597 0.36989-0.6399 0.81423-1.1586 1.333-1.5561 0.51881-0.39751 1.0952-0.59627 1.7293-0.59627 0.40352 0 0.81663 0.072715 1.2394 0.21815s0.79742 0.39751 1.1241 0.75624l-0.44675 3.6212c-0.23058 0.73685-0.50679 1.4228-0.82865 2.0579-0.32185 0.63505-0.67012 1.1877-1.0448 1.6579-0.37469 0.47023-0.76379 0.83865-1.1673 1.1053s-0.79742 0.39994-1.1817 0.39994zm19.549-9.5612c-0.10501 0.15274-0.20525 0.26014-0.30071 0.32219-0.095464 0.062051-0.21956 0.093077-0.37231 0.093077-0.16229 0-0.32935-0.047731-0.50118-0.14319-0.17183-0.095464-0.36992-0.20286-0.59426-0.32219-0.22434-0.11933-0.48209-0.22672-0.77325-0.32219s-0.64199-0.14319-1.0525-0.14319c-0.75416 0-1.3317 0.16945-1.7327 0.50834-0.40095 0.3389-0.60142 0.76132-0.60142 1.2673 0 0.29594 0.083529 0.54653 0.25059 0.75177s0.38424 0.38424 0.65154 0.53698c0.2673 0.15274 0.57278 0.29355 0.91645 0.42242s0.69688 0.2673 1.0596 0.41526 0.71597 0.31503 1.0596 0.50118c0.34367 0.18615 0.64915 0.41288 0.91645 0.68017 0.2673 0.2673 0.48447 0.58948 0.65154 0.96656s0.25059 0.82814 0.25059 1.3532c0 0.70643-0.14081 1.3794-0.42242 2.019-0.28162 0.63961-0.68495 1.2004-1.21 1.6825-0.52505 0.48209-1.1599 0.86394-1.9045 1.1456-0.74462 0.28162-1.5751 0.42242-2.4916 0.42242-0.46777 0-0.91883-0.045344-1.3532-0.13603-0.43436-0.09069-0.84246-0.21718-1.2243-0.37947-0.38185-0.16229-0.7279-0.35321-1.0382-0.57278-0.31026-0.21957-0.57516-0.45822-0.79473-0.71597l0.85917-1.346c0.10501-0.16229 0.2315-0.28639 0.37947-0.37231 0.14797-0.085917 0.32219-0.12888 0.52266-0.12888s0.38185 0.06205 0.54414 0.18615c0.16229 0.1241 0.35321 0.26014 0.57278 0.4081s0.48447 0.284 0.79473 0.4081 0.70881 0.18615 1.1957 0.18615c0.3914 0 0.73745-0.052504 1.0382-0.15751 0.30071-0.10501 0.55369-0.2482 0.75893-0.42958 0.20525-0.18138 0.36037-0.3914 0.46538-0.63006 0.10501-0.23866 0.15751-0.49163 0.15751-0.75893 0-0.32458-0.081143-0.59426-0.24343-0.80905-0.16229-0.21479-0.37946-0.40094-0.65154-0.55846-0.27207-0.15751-0.57994-0.29594-0.92361-0.41526s-0.69449-0.24582-1.0525-0.37947c-0.35799-0.13365-0.70643-0.28639-1.0453-0.45822s-0.64437-0.38901-0.91645-0.65154c-0.27207-0.26252-0.48925-0.58232-0.65154-0.9594-0.16229-0.37708-0.24343-0.83769-0.24343-1.3818 0-0.64915 0.1241-1.2816 0.37231-1.8973 0.24821-0.61574 0.61335-1.1599 1.0954-1.6324 0.48209-0.47254 1.074-0.85201 1.7756-1.1384 0.70166-0.28639 1.5059-0.42958 2.4128-0.42958 0.93554 0 1.7637 0.1599 2.4844 0.4797 0.72075 0.3198 1.3293 0.7279 1.8257 1.2243l-0.91645 1.2888z"/> <path d="m57.693 40.297c0.49639 0 0.94473-0.14341 1.345-0.43023 0.40031-0.28682 0.74258-0.6706 1.0268-1.1513 0.28422-0.48073 0.50239-1.0402 0.65451-1.6785 0.15212-0.63828 0.22818-1.3089 0.22818-2.0118 0-1.0099-0.17013-1.7533-0.5104-2.2299-0.34027-0.47669-0.85466-0.71504-1.5432-0.71504-0.49639 0-0.94473 0.14139-1.345 0.42417s-0.74058 0.66656-1.0208 1.1513c-0.28022 0.48477-0.49639 1.0463-0.6485 1.6846-0.15212 0.63828-0.22818 1.3129-0.22818 2.0239 0 1.0099 0.16813 1.7512 0.50439 2.2239s0.84866 0.70898 1.5372 0.70898zm-0.26421 2.3027c-0.68053 0-1.311-0.11513-1.8915-0.3454s-1.0808-0.56758-1.5012-1.012-0.75058-0.98974-0.99077-1.6361c-0.24019-0.64636-0.36028-1.3856-0.36028-2.2178 0-1.0746 0.17013-2.0764 0.5104-3.0056 0.34027-0.92915 0.80462-1.7331 1.3931-2.4117 0.58846-0.67868 1.275-1.2119 2.0596-1.5997 0.78461-0.38782 1.6213-0.58172 2.51-0.58172 0.68053 0 1.311 0.11513 1.8915 0.3454s1.0828 0.56758 1.5072 1.012c0.42433 0.44438 0.75659 0.98974 0.99678 1.6361 0.24019 0.64636 0.36028 1.3856 0.36028 2.2178 0 1.0665-0.17213 2.0623-0.5164 2.9874-0.34427 0.92511-0.81063 1.729-1.3991 2.4117-0.58846 0.68272-1.275 1.22-2.0596 1.6119-0.78461 0.39186-1.6213 0.58778-2.51 0.58778zm12.177-10.374-1.1649 9.6833-0.61248 2.2784c-0.11209 0.3959-0.28622 0.69888-0.52241 0.90894-0.23619 0.21007-0.57044 0.3151-1.0028 0.3151h-1.1889l1.5732-13.186-0.94874-0.16967c-0.17614-0.032318-0.32025-0.098973-0.43234-0.19997-0.11209-0.10099-0.16813-0.2444-0.16813-0.43023 0-0.0080796 0.0020015-0.024238 0.0060047-0.048477 0.0040031-0.024239 0.010008-0.078775 0.018014-0.16361 0.0080063-0.084835 0.022017-0.21411 0.042033-0.38782 0.020016-0.17371 0.050039-0.41811 0.09007-0.73322h1.6573l0.10808-0.92106c0.088069-0.711 0.27421-1.3493 0.55843-1.9148 0.28422-0.56557 0.6445-1.0463 1.0808-1.4422 0.43634-0.3959 0.93873-0.69888 1.5072-0.90894 0.56845-0.21007 1.1809-0.3151 1.8374-0.3151 0.5124 0 0.98476 0.076755 1.4171 0.23027l-0.26421 1.5513c-0.016013 0.088875-0.054042 0.15957-0.11409 0.21209-0.060047 0.052517-0.1341 0.090894-0.22217 0.11513s-0.18614 0.040398-0.29423 0.048477c-0.10808 0.0080796-0.21817 0.012119-0.33026 0.012119-0.32025 0-0.61448 0.044437-0.88269 0.13331s-0.50439 0.23228-0.70855 0.43023c-0.20416 0.19795-0.37229 0.45649-0.50439 0.77563-0.1321 0.31914-0.22618 0.70897-0.28222 1.1695l-0.096075 0.82411h2.7622l-0.27622 2.133h-2.6421z"/> </g> </g> </svg>
			</div>
			<div class="pods-admin_friends-callout_content-container">
				<h2 class="pods-admin_friends-callout_headline"><?php printf( esc_html__( 'We need %1$sYOU%2$s in 2020 and beyond', 'pods' ), '<span class="pods-admin_friends-you">', '</span>' ); ?></h2>
				<p class="pods-admin_friends-callout_text"><?php esc_html_e( 'Things are changing for Pods and we want you to be a part of it! Our goal is to be fully funded by users like you. Help us reach our goal of 200 recurring donors in 2020.', 'pods' ); ?></p>
				<div class="pods-admin_friends-callout_button-group">
					<a href="https://friends.pods.io/?utm_source=pods_plugin_callout&utm_medium=link&utm_campaign=friends_of_pods_2020" class="pods-admin_friends-callout_button"><?php esc_html_e( 'Learn More', 'pods' ); ?></a>
					<a href="https://friends.pods.io/membership-levels/?utm_source=pods_plugin_callout&utm_medium=link&utm_campaign=friends_of_pods_2020" class="pods-admin_friends-callout_button--join"><?php esc_html_e( 'Join Now', 'pods' ); ?></a>
				</div>
			</div>
		</div>
<?php
		}
	}

	/**
	 * Get the add page of an object
	 *
	 * @param PodsUI $obj PodsUI object.
	 */
	public function admin_setup_add( $obj ) {

		pods_view( PODS_DIR . 'ui/admin/setup-add.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get the edit page of an object
	 *
	 * @param boolean $duplicate Whether the screen is for duplicating.
	 * @param PodsUI  $obj       PodsUI object.
	 */
	public function admin_setup_edit( $duplicate, $obj ) {

		pods_view( PODS_DIR . 'ui/admin/setup-edit.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get list of Pod option tabs
	 *
	 * @param array $pod Pod options.
	 *
	 * @return array
	 */
	public function admin_setup_edit_tabs( $pod ) {

		$fields   = true;
		$labels   = false;
		$admin_ui = false;
		$advanced = false;

		if ( 'post_type' === pods_v( 'type', $pod ) && '' === pods_v( 'object', $pod ) ) {
			$labels   = true;
			$admin_ui = true;
			$advanced = true;
		} elseif ( 'taxonomy' === pods_v( 'type', $pod ) && '' === pods_v( 'object', $pod ) ) {
			$labels   = true;
			$admin_ui = true;
			$advanced = true;
		} elseif ( 'pod' === pods_v( 'type', $pod ) ) {
			$labels   = true;
			$admin_ui = true;
			$advanced = true;
		} elseif ( 'settings' === pods_v( 'type', $pod ) ) {
			$labels   = true;
			$admin_ui = true;
		}

		if ( ! function_exists( 'get_term_meta' ) && 'none' === pods_v( 'storage', $pod, 'none', true ) && 'taxonomy' === pods_v( 'type', $pod ) ) {
			$fields = false;
		}

		$tabs = array();

		if ( $fields ) {
			$tabs['manage-fields'] = __( 'Manage Fields', 'pods' );
		}

		if ( $labels ) {
			$tabs['labels'] = __( 'Labels', 'pods' );
		}

		if ( $admin_ui ) {
			$tabs['admin-ui'] = __( 'Admin UI', 'pods' );
		}

		if ( $advanced ) {
			$tabs['advanced'] = __( 'Advanced Options', 'pods' );
		}

		if ( 'taxonomy' === pods_v( 'type', $pod ) && ! $fields ) {
			$tabs['extra-fields'] = __( 'Extra Fields', 'pods' );
		}

		$addtl_args = compact( array( 'fields', 'labels', 'admin_ui', 'advanced' ) );

		$pod_type = $pod['type'];
		$pod_name = $pod['name'];

		/**
		 * Add or modify tabs in Pods editor for a specific Pod
		 *
		 * @param array  $tabs       Tabs to set.
		 * @param object $pod        Current Pods object.
		 * @param array  $addtl_args Additional args.
		 *
		 * @since  unknown
		 */
		$tabs = apply_filters( "pods_admin_setup_edit_tabs_{$pod_type}_{$pod_name}", $tabs, $pod, $addtl_args );

		/**
		 * Add or modify tabs for any Pod in Pods editor of a specific post type.
		 */
		$tabs = apply_filters( "pods_admin_setup_edit_tabs_{$pod_type}", $tabs, $pod, $addtl_args );

		/**
		 * Add or modify tabs in Pods editor for all pods.
		 */
		$tabs = apply_filters( 'pods_admin_setup_edit_tabs', $tabs, $pod, $addtl_args );

		return $tabs;
	}

	/**
	 * Get list of Pod options
	 *
	 * @param array $pod Pod options.
	 *
	 * @return array
	 */
	public function admin_setup_edit_options( $pod ) {

		$options = array();

		if ( '' === pods_v( 'object', $pod ) && 'settings' !== pods_v( 'type', $pod ) ) {
			$labels = array(
				'label'                            => array(
					'label'           => __( 'Label', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => str_replace( '_', ' ', pods_v( 'name', $pod ) ),
					'text_max_length' => 30,
				),
				'label_singular'                   => array(
					'label'           => __( 'Singular Label', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', pods_v( 'name', $pod ) ) ) ),
					'text_max_length' => 30,
				),
				'label_add_new'                    => array(
					'label'       => __( 'Add New', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type', 'pod' ),
				),
				'label_add_new_item'               => array(
					'label'   => __( 'Add new <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'label_new_item'                   => array(
					'label'       => __( 'New <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type', 'pod' ),
				),
				'label_new_item_name'              => array(
					'label'       => __( 'New <span class="pods-slugged" data-sluggable="label_singular">Item</span> Name', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy' ),
				),
				'label_edit'                       => array(
					'label'       => __( 'Edit', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_edit_item'                  => array(
					'label'   => __( 'Edit <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'label_update_item'                => array(
					'label'       => __( 'Update <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy', 'pod' ),
				),
				'label_duplicate'                  => array(
					'label'       => __( 'Duplicate', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_duplicate_item'             => array(
					'label'       => __( 'Duplicate <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_delete_item'                => array(
					'label'       => __( 'Delete <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_view'                       => array(
					'label'       => __( 'View', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_view_item'                  => array(
					'label'   => __( 'View <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'label_view_items'                 => array(
					'label'       => __( 'View <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_back_to_manage'             => array(
					'label'       => __( 'Back to Manage', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_manage'                     => array(
					'label'       => __( 'Manage', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_manage_items'               => array(
					'label'       => __( 'Manage <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_reorder'                    => array(
					'label'       => __( 'Reorder', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_reorder_items'              => array(
					'label'       => __( 'Reorder <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_all_items'                  => array(
					'label'   => __( 'All <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'label_search'                     => array(
					'label'       => __( 'Search', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_search_items'               => array(
					'label'   => __( 'Search <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'label_popular_items'              => array(
					'label'       => __( 'Popular <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy' ),
				),
				// @todo Why was label_parent added previously? Can't find it in WP
				'label_parent'                     => array(
					'label'       => __( 'Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type', 'pod' ),
				),
				'label_parent_item'                => array(
					'label'       => __( 'Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy' ),
				),
				'label_parent_item_colon'          => array(
					'label'   => __( 'Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>:', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'label_not_found'                  => array(
					'label'   => __( 'Not Found', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'label_no_items_found'             => array(
					'label'       => __( 'No <span class="pods-slugged" data-sluggable="label_singular">Item</span> Found', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'pod' ),
				),
				'label_not_found_in_trash'         => array(
					'label'       => __( 'Not Found in Trash', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type', 'pod' ),
				),
				'label_archives'                   => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label_singular">Item</span> Archives', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_attributes'                 => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label_singular">Item</span> Attributes', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_insert_into_item'           => array(
					'label'       => __( 'Insert into <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_uploaded_to_this_item'      => array(
					'label'       => __( 'Uploaded to this <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_featured_image'             => array(
					'label'       => __( 'Featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => array( 'post_type' ),
				),
				'label_set_featured_image'         => array(
					'label'       => __( 'Set featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => array( 'post_type' ),
				),
				'label_remove_featured_image'      => array(
					'label'       => __( 'Remove featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => array( 'post_type' ),
				),
				'label_use_featured_image'         => array(
					'label'       => __( 'Use as featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => array( 'post_type' ),
				),
				'label_filter_items_list'          => array(
					'label'       => __( 'Filter <span class="pods-slugged" data-sluggable="label">Items</span> lists', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_items_list_navigation'      => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label">Items</span> list navigation', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type', 'taxonomy' ),
				),
				'label_items_list'                 => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label">Items</span> list', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type', 'taxonomy' ),
				),
				'label_separate_items_with_commas' => array(
					'label'       => __( 'Separate <span class="pods-slugged-lower" data-sluggable="label">items</span> with commas', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy' ),
				),
				'label_add_or_remove_items'        => array(
					'label'       => __( 'Add or remove <span class="pods-slugged-lower" data-sluggable="label">items</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy' ),
				),
				'label_choose_from_the_most_used'  => array(
					'label'       => __( 'Choose from the most used <span class="pods-slugged-lower" data-sluggable="label">items</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy' ),
				),
				'label_no_terms'                   => array(
					'label'       => __( 'No <span class="pods-slugged-lower" data-sluggable="label">items</span>', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'taxonomy' ),
				),
				'label_item_published'             => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label_singular">Item</span> Published.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_item_published_privately'   => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label_singular">Item</span> published privately.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_item_reverted_to_draft'     => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label_singular">Item</span> reverted to draft.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_item_scheduled'             => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label_singular">Item</span> scheduled.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
				'label_item_updated'               => array(
					'label'       => __( '<span class="pods-slugged" data-sluggable="label_singular">Item</span> updated.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => array( 'post_type' ),
				),
			);

			$options['labels'] = array();

			/**
			 * Filter through all labels if they have an object_type set and match it against the current object type
			 */
			foreach ( $labels as $label => $labeldata ) {
				if ( array_key_exists( 'object_type', $labeldata ) ) {
					if ( in_array( pods_v( 'type', $pod ), $labeldata['object_type'], true ) ) {
						// Do not add the object_type to the actual label data
						unset( $labeldata['object_type'] );
						$options['labels'][ $label ] = $labeldata;
					}
				} else {
					$options['labels'][ $label ] = $labeldata;
				}
			}
		} elseif ( 'settings' === pods_v( 'type', $pod ) ) {

			$options['labels'] = array(
				'label'     => array(
					'label'           => __( 'Page Title', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => str_replace( '_', ' ', pods_v( 'name', $pod ) ),
					'text_max_length' => 30,
				),
				'menu_name' => array(
					'label'           => __( 'Menu Name', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', pods_v( 'name', $pod ) ) ) ),
					'text_max_length' => 30,
				),
			);
		}//end if

		if ( 'post_type' === $pod['type'] ) {
			$options['admin-ui'] = array(
				'description'          => array(
					'label'   => __( 'Post Type Description', 'pods' ),
					'help'    => __( 'A short descriptive summary of what the post type is.', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'show_ui'              => array(
					'label'             => __( 'Show Admin UI', 'pods' ),
					'help'              => __( 'Whether to generate a default UI for managing this post type in the admin.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				),
				'show_in_menu'         => array(
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'Whether to show the post type in the admin menu.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'menu_location_custom' => array(
					'label'      => __( 'Parent Menu ID (optional)', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'show_in_menu' => true ),
				),
				'menu_name'            => array(
					'label'      => __( 'Menu Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_in_menu' => true ),
				),
				'menu_position'        => array(
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'help', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => array( 'show_in_menu' => true ),
				),
				'menu_icon'            => array(
					'label'      => __( 'Menu Icon', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="https://pods.io/docs/build/special-magic-tags/#site-tags" target="_blank" rel="noopener noreferrer">site tag</a> type <a href="https://pods.io/docs/build/special-magic-tags/" target="_blank" rel="noopener noreferrer">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener noreferrer">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_in_menu' => true ),
				),
				'show_in_nav_menus'    => array(
					'label'             => __( 'Show in Navigation Menus', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				),
				'show_in_admin_bar'    => array(
					'label'             => __( 'Show in Admin Bar "New" Menu', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'name_admin_bar'       => array(
					'label'      => __( 'Admin bar name', 'pods' ),
					'help'       => __( 'Defaults to singular name', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_in_admin_bar' => true ),
				),
			);

			$post_type_name = pods_v( 'name', $pod, 'post_type', true );

			$options['advanced'] = array(
				'public'                  => array(
					'label'             => __( 'Public', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				),
				'publicly_queryable'      => array(
					'label'             => __( 'Publicly Queryable', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				),
				'exclude_from_search'     => array(
					'label'             => __( 'Exclude from Search', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => ! pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				),
				'capability_type'         => array(
					'label'      => __( 'User Capability', 'pods' ),
					'help'       => __( 'Uses these capabilties for access to this post type: edit_{capability}, read_{capability}, and delete_{capability}', 'pods' ),
					'type'       => 'pick',
					'default'    => 'post',
					'data'       => array(
						'post'   => 'post',
						'page'   => 'page',
						'custom' => __( 'Custom Capability', 'pods' ),
					),
					'dependency' => true,
				),
				'capability_type_custom'  => array(
					'label'      => __( 'Custom User Capability', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => pods_v( 'name', $pod ),
					'depends-on' => array( 'capability_type' => 'custom' ),
				),
				'capability_type_extra'   => array(
					'label'             => __( 'Additional User Capabilities', 'pods' ),
					'help'              => __( 'Enables additional capabilities for this Post Type including: delete_{capability}s, delete_private_{capability}s, delete_published_{capability}s, delete_others_{capability}s, edit_private_{capability}s, and edit_published_{capability}s', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				),
				'has_archive'             => array(
					'label'             => __( 'Enable Archive Page', 'pods' ),
					'help'              => __( 'If enabled, creates an archive page with list of of items in this custom post type. Functions like a category page for posts. Can be controlled with a template in your theme called "archive-{$post-type}.php".', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'has_archive_slug'        => array(
					'label'      => __( 'Archive Page Slug Override', 'pods' ),
					'help'       => __( 'If archive page is enabled, you can override the slug used by WordPress, which defaults to the name of the post type.', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'has_archive' => true ),
				),
				'hierarchical'            => array(
					'label'             => __( 'Hierarchical', 'pods' ),
					'help'              => __( 'Allows for parent/ child relationships between items, just like with Pages. Note: To edit relationships in the post editor, you must enable "Page Attributes" in the "Supports" section below.', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'label_parent_item_colon' => array(
					'label'      => __( '<strong>Label: </strong> Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'hierarchical' => true ),
				),
				'label_parent'            => array(
					'label'      => __( '<strong>Label: </strong> Parent', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'hierarchical' => true ),
				),
				'rewrite'                 => array(
					'label'             => __( 'Rewrite', 'pods' ),
					'help'              => __( 'Allows you to use pretty permalinks, if set in WordPress Settings->Permalinks. If not enabled, your links will be in the form of "example.com/?pod_name=post_slug" regardless of your permalink settings.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'rewrite_custom_slug'     => array(
					'label'      => __( 'Custom Rewrite Slug', 'pods' ),
					'help'       => __( 'Changes the first segment of the URL, which by default is the name of the Pod. For example, if your Pod is called "foo", if this field is left blank, your link will be "example.com/foo/post_slug", but if you were to enter "bar" your link will be "example.com/bar/post_slug".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'rewrite' => true ),
				),
				'rewrite_with_front'      => array(
					'label'             => __( 'Rewrite with Front', 'pods' ),
					'help'              => __( 'Allows permalinks to be prepended with your front base (example: if your permalink structure is /blog/, then your links will be: Unchecked->/news/, Checked->/blog/news/)', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'depends-on'        => array( 'rewrite' => true ),
					'boolean_yes_label' => '',
				),
				'rewrite_feeds'           => array(
					'label'             => __( 'Rewrite Feeds', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'depends-on'        => array( 'rewrite' => true ),
					'boolean_yes_label' => '',
				),
				'rewrite_pages'           => array(
					'label'             => __( 'Rewrite Pages', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'depends-on'        => array( 'rewrite' => true ),
					'boolean_yes_label' => '',
				),
				'query_var'               => array(
					'label'             => __( 'Query Var', 'pods' ),
					'help'              => __( 'The Query Var is used in the URL and underneath the WordPress Rewrite API to tell WordPress what page or post type you are on. For a list of reserved Query Vars, read <a href="http://codex.wordpress.org/WordPress_Query_Vars">WordPress Query Vars</a> from the WordPress Codex.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				),
				'can_export'              => array(
					'label'             => __( 'Exportable', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				),
				'default_status'          => array(
					'label'       => __( 'Default Status', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'pick',
					'pick_object' => 'post-status',
					'default'     => apply_filters( "pods_api_default_status_{$post_type_name}", 'draft', $pod ),
				),
			);
		} elseif ( 'taxonomy' === $pod['type'] ) {
			$options['admin-ui'] = array(
				'description'           => array(
					'label'   => __( 'Taxonomy Description', 'pods' ),
					'help'    => __( 'A short descriptive summary of what the taxonomy is.', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'show_ui'               => array(
					'label'             => __( 'Show Admin UI', 'pods' ),
					'help'              => __( 'Whether to generate a default UI for managing this taxonomy.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'show_in_menu'          => array(
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'Whether to show the taxonomy in the admin menu.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'dependency'        => true,
					'depends-on'        => array( 'show_ui' => true ),
					'boolean_yes_label' => '',
				),
				'menu_name'             => array(
					'label'      => __( 'Menu Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_ui' => true ),
				),
				'menu_location'         => array(
					'label'      => __( 'Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'default',
					'depends-on' => array( 'show_ui' => true ),
					'data'       => array(
						'default'     => __( 'Default - Add to associated Post Type(s) menus', 'pods' ),
						'settings'    => __( 'Add a submenu item to Settings menu', 'pods' ),
						'appearances' => __( 'Add a submenu item to Appearances menu', 'pods' ),
						'submenu'     => __( 'Add a submenu item to another menu', 'pods' ),
						'objects'     => __( 'Make a new menu item', 'pods' ),
						'top'         => __( 'Make a new menu item below Settings', 'pods' ),
					),
					'dependency' => true,
				),
				'menu_location_custom'  => array(
					'label'      => __( 'Custom Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'menu_location' => 'submenu' ),
				),
				'menu_position'         => array(
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'help', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => array( 'menu_location' => array( 'objects', 'top' ) ),
				),
				'menu_icon'             => array(
					'label'      => __( 'Menu Icon URL', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'menu_location' => array( 'objects', 'top' ) ),
				),
				'show_in_nav_menus'     => array(
					'label'             => __( 'Show in Navigation Menus', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				),
				'show_tagcloud'         => array(
					'label'             => __( 'Allow in Tagcloud Widget', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'show_ui', $pod, pods_v( 'public', $pod, true ) ),
					'boolean_yes_label' => '',
				),
				// @todo check https://core.trac.wordpress.org/ticket/36964
				'show_tagcloud_in_edit' => array(
					'label'             => __( 'Allow Tagcloud on term edit pages', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'show_ui', $pod, pods_v( 'show_tagcloud', $pod, true ) ),
					'boolean_yes_label' => '',
				),
				'show_in_quick_edit'    => array(
					'label'             => __( 'Allow in quick/bulk edit panel', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'show_ui', $pod, pods_v( 'public', $pod, true ) ),
					'boolean_yes_label' => '',
				),
			);

			$options['admin-ui']['show_admin_column'] = array(
				'label'             => __( 'Show Taxonomy column on Post Types', 'pods' ),
				'help'              => __( 'Whether to add a column for this taxonomy on the associated post types manage screens', 'pods' ),
				'type'              => 'boolean',
				'default'           => false,
				'boolean_yes_label' => '',
			);

			// Integration for Single Value Taxonomy UI
			if ( function_exists( 'tax_single_value_meta_box' ) ) {
				$options['admin-ui']['single_value'] = array(
					'label'             => __( 'Single Value Taxonomy', 'pods' ),
					'help'              => __( 'Use a drop-down for the input instead of the WordPress default', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				);

				$options['admin-ui']['single_value_required'] = array(
					'label'             => __( 'Single Value Taxonomy - Required', 'pods' ),
					'help'              => __( 'A term will be selected by default in the Post Editor, not optional', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				);
			}

			$options['advanced'] = array(
				'public'                  => array(
					'label'             => __( 'Public', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				),
				'hierarchical'            => array(
					'label'             => __( 'Hierarchical', 'pods' ),
					'help'              => __( 'Hierarchical taxonomies will have a list with checkboxes to select an existing category in the taxonomy admin box on the post edit page (like default post categories). Non-hierarchical taxonomies will just have an empty text field to type-in taxonomy terms to associate with the post (like default post tags).', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'label_parent_item_colon' => array(
					'label'      => __( '<strong>Label: </strong> Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'hierarchical' => true ),
				),
				'label_parent'            => array(
					'label'      => __( '<strong>Label: </strong> Parent', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'hierarchical' => true ),
				),
				'label_no_terms'          => array(
					'label'      => __( '<strong>Label: </strong> No <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'hierarchical' => true ),
				),
				'rewrite'                 => array(
					'label'             => __( 'Rewrite', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'rewrite_custom_slug'     => array(
					'label'      => __( 'Custom Rewrite Slug', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'rewrite' => true ),
				),
				'rewrite_with_front'      => array(
					'label'             => __( 'Rewrite with Front', 'pods' ),
					'help'              => __( 'Allows permalinks to be prepended with your front base (example: if your permalink structure is /blog/, then your links will be: Unchecked->/news/, Checked->/blog/news/)', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
					'depends-on'        => array( 'rewrite' => true ),
				),
				'rewrite_hierarchical'    => array(
					'label'             => __( 'Hierarchical Permalinks', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
					'depends-on'        => array( 'rewrite' => true ),
				),
				'capability_type'         => array(
					'label'      => __( 'User Capability', 'pods' ),
					'help'       => __( 'Uses WordPress term capabilities by default', 'pods' ),
					'type'       => 'pick',
					'default'    => 'default',
					'data'       => array(
						'default' => 'Default',
						'custom'  => __( 'Custom Capability', 'pods' ),
					),
					'dependency' => true,
				),
				'capability_type_custom'  => array(
					'label'      => __( 'Custom User Capability', 'pods' ),
					'help'       => __( 'Enables additional capabilities for this Taxonomy including: manage_{capability}_terms, edit_{capability}_terms, assign_{capability}_terms, and delete_{capability}_terms', 'pods' ),
					'type'       => 'text',
					'default'    => pods_v( 'name', $pod ),
					'depends-on' => array( 'capability_type' => 'custom' ),
				),
				'query_var'               => array(
					'label'             => __( 'Query Var', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				),
				'query_var'               => array(
					'label'             => __( 'Query Var', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => '',
				),
				'query_var_string'        => array(
					'label'      => __( 'Custom Query Var Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'query_var' => true ),
				),
				'sort'                    => array(
					'label'             => __( 'Remember order saved on Post Types', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				),
				'update_count_callback'   => array(
					'label'   => __( 'Function to call when updating counts', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
			);
		} elseif ( 'settings' === $pod['type'] ) {
			$options['admin-ui'] = array(
				'ui_style'             => array(
					'label'      => __( 'Admin UI Style', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => array(
						'settings'  => __( 'Normal Settings Form', 'pods' ),
						'post_type' => __( 'Post Type UI', 'pods' ),
						'custom'    => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' ),
					),
					'dependency' => true,
				),
				'menu_location'        => array(
					'label'      => __( 'Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => array(
						'settings'    => __( 'Add a submenu item to Settings menu', 'pods' ),
						'appearances' => __( 'Add a submenu item to Appearances menu', 'pods' ),
						'submenu'     => __( 'Add a submenu item to another menu', 'pods' ),
						'top'         => __( 'Make a new menu item below Settings', 'pods' ),
					),
					'dependency' => true,
				),
				'menu_location_custom' => array(
					'label'      => __( 'Custom Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'menu_location' => 'submenu' ),
				),
				'menu_position'        => array(
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'help', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => array( 'menu_location' => 'top' ),
				),
				'menu_icon'            => array(
					'label'      => __( 'Menu Icon URL', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'menu_location' => 'top' ),
				),
			);

			// @todo fill this in
			$options['advanced'] = array(
				'temporary' => 'This type has the fields hardcoded',
			// :(
			);
		} elseif ( 'pod' === $pod['type'] ) {
			$actions_enabled = array(
				'add',
				'edit',
				'duplicate',
				'delete',
			);

			if ( 1 === (int) pods_v( 'ui_export', $pod ) ) {
				$actions_enabled = array(
					'add',
					'edit',
					'duplicate',
					'delete',
					'export',
				);
			}

			$options['admin-ui'] = array(
				'ui_style'             => array(
					'label'      => __( 'Admin UI Style', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => array(
						'post_type' => __( 'Normal (Looks like the Post Type UI)', 'pods' ),
						'custom'    => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' ),
					),
					'dependency' => true,
				),
				'show_in_menu'         => array(
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
					'dependency'        => true,
				),
				'menu_location_custom' => array(
					'label'      => __( 'Parent Menu ID (optional)', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'show_in_menu' => true ),
				),
				'menu_position'        => array(
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'help', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => array( 'show_in_menu' => true ),
				),
				'menu_icon'            => array(
					'label'      => __( 'Menu Icon URL', 'pods' ),
					'help'       => __( 'This is the icon shown to the left of the menu text for this content type.', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_in_menu' => true ),
				),
				'ui_icon'              => array(
					'label'           => __( 'Header Icon', 'pods' ),
					'help'            => __( 'This is the icon shown to the left of the heading text at the top of the manage pages for this content type.', 'pods' ),
					'type'            => 'file',
					'default'         => '',
					'file_edit_title' => 0,
					'depends-on'      => array( 'show_in_menu' => true ),
				),
				'ui_actions_enabled'   => array(
					'label'            => __( 'Actions Available', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => $actions_enabled,
					'data'             => array(
						'add'       => __( 'Add New', 'pods' ),
						'edit'      => __( 'Edit', 'pods' ),
						'duplicate' => __( 'Duplicate', 'pods' ),
						'delete'    => __( 'Delete', 'pods' ),
						'reorder'   => __( 'Reorder', 'pods' ),
						'export'    => __( 'Export', 'pods' ),
					),
					'pick_format_type' => 'multi',
					'dependency'       => true,
				),
				'ui_reorder_field'     => array(
					'label'      => __( 'Reorder Field', 'pods' ),
					'help'       => __( 'This is the field that will be reordered on, it should be numeric.', 'pods' ),
					'type'       => 'text',
					'default'    => 'menu_order',
					'depends-on' => array( 'ui_actions_enabled' => 'reorder' ),
				),
				'ui_fields_manage'     => array(
					'label'            => __( 'Admin Table Columns', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => array(),
					'data'             => array(),
					'pick_format_type' => 'multi',
				),
				'ui_filters'           => array(
					'label'            => __( 'Search Filters', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => array(),
					'data'             => array(),
					'pick_format_type' => 'multi',
				),
			);

			if ( ! empty( $pod['fields'] ) ) {
				if ( isset( $pod['fields'][ pods_v( 'pod_index', $pod, 'name' ) ] ) ) {
					$options['admin-ui']['ui_fields_manage']['default'][] = pods_v( 'pod_index', $pod, 'name' );
				}

				if ( isset( $pod['fields']['modified'] ) ) {
					$options['admin-ui']['ui_fields_manage']['default'][] = 'modified';
				}

				foreach ( $pod['fields'] as $field ) {
					$type = '';

					if ( isset( $field_types[ $field['type'] ] ) ) {
						$type = ' <small>(' . $field_types[ $field['type'] ]['label'] . ')</small>';
					}

					$options['admin-ui']['ui_fields_manage']['data'][ $field['name'] ] = $field['label'] . $type;
					$options['admin-ui']['ui_filters']['data'][ $field['name'] ]       = $field['label'] . $type;
				}

				$options['admin-ui']['ui_fields_manage']['data']['id'] = 'ID';
			} else {
				unset( $options['admin-ui']['ui_fields_manage'] );
				unset( $options['admin-ui']['ui_filters'] );
			}//end if

			// @todo fill this in
			$options['advanced'] = array(
				'temporary' => 'This type has the fields hardcoded',
			// :(
			);
		}//end if

		$pod_type = $pod['type'];
		$pod_name = $pod['name'];

		/**
		 * Add admin fields to the Pods editor for a specific Pod
		 *
		 * @param array  $options The Options fields.
		 * @param object $pod     Current Pods object.
		 *
		 * @since  unkown
		 */
		$options = apply_filters( "pods_admin_setup_edit_options_{$pod_type}_{$pod_name}", $options, $pod );

		/**
		 * Add admin fields to the Pods editor for any Pod of a specific content type.
		 *
		 * @param array  $options The Options fields.
		 * @param object $pod     Current Pods object.
		 */
		$options = apply_filters( "pods_admin_setup_edit_options_{$pod_type}", $options, $pod );

		/**
		 * Add admin fields to the Pods editor for all Pods
		 *
		 * @param array  $options The Options fields.
		 * @param object $pod     Current Pods object.
		 */
		$options = apply_filters( 'pods_admin_setup_edit_options', $options, $pod );

		return $options;
	}

	/**
	 * Get list of Pod field option tabs
	 *
	 * @param array $pod Pod options.
	 *
	 * @return array
	 */
	public function admin_setup_edit_field_tabs( $pod ) {

		$core_tabs = array(
			'basic'            => __( 'Basic', 'pods' ),
			'additional-field' => __( 'Additional Field Options', 'pods' ),
			'advanced'         => __( 'Advanced', 'pods' ),
		);

		/**
		 * Field option tabs
		 *
		 * Use to add new tabs, default tabs are added after this filter (IE you can't remove/modify them with this, kthanksbye).
		 *
		 * @since unknown
		 *
		 * @param array      $tabs Tabs to add, starts empty.
		 * @param object|Pod $pod  Current Pods object.
		 */
		$tabs = apply_filters( 'pods_admin_setup_edit_field_tabs', array(), $pod );

		$tabs = array_merge( $core_tabs, $tabs );

		return $tabs;
	}

	/**
	 * Get list of Pod field options
	 *
	 * @param array $pod Pod options.
	 *
	 * @return array
	 */
	public function admin_setup_edit_field_options( $pod ) {

		$options = array();

		$options['additional-field'] = array();

		$field_types = PodsForm::field_types();

		foreach ( $field_types as $type => $field_type_data ) {
			/**
			 * @var $field_type PodsField
			 */
			$field_type = PodsForm::field_loader( $type, $field_type_data['file'] );

			$field_type_vars = get_class_vars( get_class( $field_type ) );

			if ( ! isset( $field_type_vars['pod_types'] ) ) {
				$field_type_vars['pod_types'] = true;
			}

			$options['additional-field'][ $type ] = array();

			// Only show supported field types
			if ( true !== $field_type_vars['pod_types'] ) {
				if ( empty( $field_type_vars['pod_types'] ) ) {
					continue;
				} elseif ( is_array( $field_type_vars['pod_types'] ) && ! in_array( pods_v( 'type', $pod ), $field_type_vars['pod_types'], true ) ) {
					continue;
				} elseif ( ! is_array( $field_type_vars['pod_types'] ) && pods_v( 'type', $pod ) !== $field_type_vars['pod_types'] ) {
					continue;
				}
			}

			$options['additional-field'][ $type ] = PodsForm::ui_options( $type );

			/**
			 * Modify Additional Field Options tab
			 *
			 * @since 2.7.0
			 *
			 * @param array       $options Additional field type options,
			 * @param string      $type    Field type,
			 * @param array       $options Tabs, indexed by label,
			 * @param object|Pods $pod     Pods object for the Pod this UI is for.
			 */
			$options['additional-field'][ $type ] = apply_filters( "pods_admin_setup_edit_{$type}_additional_field_options", $options['additional-field'][ $type ], $type, $options, $pod );
			$options['additional-field'][ $type ] = apply_filters( 'pods_admin_setup_edit_additional_field_options', $options['additional-field'][ $type ], $type, $options, $pod );
		}//end foreach

		$input_helpers = array(
			'' => '-- Select --',
		);

		if ( class_exists( 'Pods_Helpers' ) ) {
			$helpers = pods_api()->load_helpers( array( 'options' => array( 'helper_type' => 'input' ) ) );

			foreach ( $helpers as $helper ) {
				$input_helpers[ $helper['name'] ] = $helper['name'];
			}
		}

		$options['advanced'] = array(
			__( 'Visual', 'pods' )     => array(
				'class'        => array(
					'name'    => 'class',
					'label'   => __( 'Additional CSS Classes', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
				'input_helper' => array(
					'name'    => 'input_helper',
					'label'   => __( 'Input Helper', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'pick',
					'default' => '',
					'data'    => $input_helpers,
				),
			),
			__( 'Values', 'pods' )     => array(
				'default_value'           => array(
					'name'    => 'default_value',
					'label'   => __( 'Default Value', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
					'options' => array(
						'text_max_length' => - 1,
					),
				),
				'default_value_parameter' => array(
					'name'    => 'default_value_parameter',
					'label'   => __( 'Set Default Value via Parameter', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				),
			),
			__( 'Visibility', 'pods' ) => array(
				'restrict_access'    => array(
					'name'  => 'restrict_access',
					'label' => __( 'Restrict Access', 'pods' ),
					'group' => array(
						'admin_only'          => array(
							'name'       => 'admin_only',
							'label'      => __( 'Restrict access to Admins?', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'dependency' => true,
							'help'       => __( 'This field will only be able to be edited by users with the ability to manage_options or delete_users, or super admins of a WordPress Multisite network', 'pods' ),
						),
						'restrict_role'       => array(
							'name'       => 'restrict_role',
							'label'      => __( 'Restrict access by Role?', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'dependency' => true,
						),
						'restrict_capability' => array(
							'name'       => 'restrict_capability',
							'label'      => __( 'Restrict access by Capability?', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'dependency' => true,
						),
						'hidden'              => array(
							'name'    => 'hidden',
							'label'   => __( 'Hide field from UI', 'pods' ),
							'default' => 0,
							'type'    => 'boolean',
							'help'    => __( 'This option is overriden by access restrictions. If the user does not have access to edit this field, it will be hidden. If no access restrictions are set, this field will always be hidden.', 'pods' ),
						),
						'read_only'           => array(
							'name'       => 'read_only',
							'label'      => __( 'Make field "Read Only" in UI', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'help'       => __( 'This option is overriden by access restrictions. If the user does not have access to edit this field, it will be read only. If no access restrictions are set, this field will always be read only.', 'pods' ),
							'depends-on' => array(
								'type' => array(
									'boolean',
									'color',
									'currency',
									'date',
									'datetime',
									'email',
									'number',
									'paragraph',
									'password',
									'phone',
									'slug',
									'text',
									'time',
									'website',
								),
							),
						),
					),
				),
				'roles_allowed'      => array(
					'name'             => 'roles_allowed',
					'label'            => __( 'Role(s) Allowed', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'pick_object'      => 'role',
					'pick_format_type' => 'multi',
					'default'          => 'administrator',
					'depends-on'       => array(
						'restrict_role' => true,
					),
				),
				'capability_allowed' => array(
					'name'       => 'capability_allowed',
					'label'      => __( 'Capability Allowed', 'pods' ),
					'help'       => __( 'Comma-separated list of cababilities, for example add_podname_item, please see the Roles and Capabilities component for the complete list and a way to add your own.', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array(
						'restrict_capability' => true,
					),
				),
			),
		);

		/*
		$options['advanced'][ __( 'Visibility', 'pods' ) ]['search'] = array(
			'label'   => __( 'Include in searches', 'pods' ),
			'help'    => __( 'help', 'pods' ),
			'default' => 1,
			'type'    => 'boolean',
		);
		*/

		/*
		$options['advanced'][ __( 'Validation', 'pods' ) ] = array(
			'regex_validation' => array(
				'label'   => __( 'RegEx Validation', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			),
			'message_regex'    => array(
				'label'   => __( 'Message if field does not pass RegEx', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			),
			'message_required' => array(
				'label'      => __( 'Message if field is blank', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'type'       => 'text',
				'default'    => '',
				'depends-on' => array( 'required' => true ),
			),
			'message_unique'   => array(
				'label'      => __( 'Message if field is not unique', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'type'       => 'text',
				'default'    => '',
				'depends-on' => array( 'unique' => true ),
			),
		);
		*/

		if ( ! class_exists( 'Pods_Helpers' ) ) {
			unset( $options['advanced']['input_helper'] );
		}

		/**
		 * Modify tabs and their contents for field options
		 *
		 * @since unknown
		 *
		 * @param array       $options Tabs, indexed by label.
		 * @param object|Pods $pod     Pods object for the Pod this UI is for.
		 */
		$options = apply_filters( 'pods_admin_setup_edit_field_options', $options, $pod );

		return $options;
	}

	/**
	 * Duplicate a pod
	 *
	 * @param PodsUI $obj PodsUI object.
	 */
	public function admin_setup_duplicate( $obj ) {

		$new_id = pods_api()->duplicate_pod( array( 'id' => $obj->id ) );

		if ( 0 < $new_id ) {
			pods_redirect(
				pods_query_arg(
					array(
						'action' => 'edit',
						'id'     => $new_id,
						'do'     => 'duplicate',
					)
				)
			);
		}
	}

	/**
	 * Restrict Duplicate action to custom types, not extended
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @since 2.3.10
	 *
	 * @return bool
	 */
	public function admin_setup_duplicate_restrict( $restricted, $restrict, $action, $row, $obj ) {

		if ( in_array(
			$row['real_type'], array(
				'user',
				'media',
				'comment',
			), true
		) ) {
			$restricted = true;
		}

		return $restricted;

	}

	/**
	 * Reset a pod
	 *
	 * @param PodsUI     $obj PodsUI object.
	 * @param int|string $id  Item ID.
	 *
	 * @return mixed
	 */
	public function admin_setup_reset( $obj, $id ) {

		$pod = pods_api()->load_pod( array( 'id' => $id ), false );

		if ( empty( $pod ) ) {
			return $obj->error( __( 'Pod not found.', 'pods' ) );
		}

		pods_api()->reset_pod( array( 'id' => $id ) );

		$obj->message( __( 'Pod reset successfully.', 'pods' ) );

		$obj->manage();
	}

	/**
	 * Restrict Reset action from users and media
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @since 2.3.10
	 */
	public function admin_setup_reset_restrict( $restricted, $restrict, $action, $row, $obj ) {

		if ( in_array(
			$row['real_type'], array(
				'user',
				'media',
			), true
		) ) {
			$restricted = true;
		}

		return $restricted;

	}

	/**
	 * Delete a pod
	 *
	 * @param int|string $id  Item ID.
	 * @param PodsUI     $obj PodsUI object.
	 *
	 * @return mixed
	 */
	public function admin_setup_delete( $id, $obj ) {

		$pod = pods_api()->load_pod( array( 'id' => $id ), false );

		if ( empty( $pod ) ) {
			return $obj->error( __( 'Pod not found.', 'pods' ) );
		}

		pods_api()->delete_pod( array( 'id' => $id ) );

		unset( $obj->data[ $pod['id'] ] );

		$obj->total       = count( $obj->data );
		$obj->total_found = count( $obj->data );

		$obj->message( __( 'Pod deleted successfully.', 'pods' ) );
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

		// Add our custom callouts.
		add_action( 'pods_admin_after_settings', array( $this, 'admin_manage_callouts' ) );

		pods_view( PODS_DIR . 'ui/admin/settings.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get components administration UI
	 */
	public function admin_components() {

		if ( ! is_object( PodsInit::$components ) ) {
			return;
		}

		$components = PodsInit::$components->components;

		$view = pods_v( 'view', 'get', 'all', true );

		$recommended = array(
			'advanced-relationships',
			'advanced-content-types',
			'migrate-packages',
			'roles-and-capabilities',
			'pages',
			'table-storage',
			'templates',
		);

		foreach ( $components as $component => &$component_data ) {
			if ( ! in_array(
				$view, array(
					'all',
					'recommended',
					'dev',
				), true
			) && ( ! isset( $component_data['Category'] ) || sanitize_title( $component_data['Category'] ) !== $view ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( 'recommended' === $view && ! in_array( $component_data['ID'], $recommended, true ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( 'dev' === $view && pods_developer() && ! pods_v( 'DeveloperMode', $component_data, false ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( pods_v( 'DeveloperMode', $component_data, false ) && ! pods_developer() ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( ! pods_v( 'TablelessMode', $component_data, false ) && pods_tableless() ) {
				unset( $components[ $component ] );

				continue;
			}//end if

			$component_data['Name'] = strip_tags( $component_data['Name'] );

			if ( pods_v( 'DeveloperMode', $component_data, false ) ) {
				$component_data['Name'] .= ' <em style="font-weight: normal; color:#333;">(Developer Preview)</em>';
			}

			$meta = array();

			if ( ! empty( $component_data['Version'] ) ) {
				$meta[] = sprintf( __( 'Version %s', 'pods' ), $component_data['Version'] );
			}

			if ( empty( $component_data['Author'] ) ) {
				$component_data['Author']    = 'Pods Framework Team';
				$component_data['AuthorURI'] = 'https://pods.io/';
			}

			if ( ! empty( $component_data['AuthorURI'] ) ) {
				$component_data['Author'] = '<a href="' . $component_data['AuthorURI'] . '">' . $component_data['Author'] . '</a>';
			}

			$meta[] = sprintf( __( 'by %s', 'pods' ), $component_data['Author'] );

			if ( ! empty( $component_data['URI'] ) ) {
				$meta[] = '<a href="' . $component_data['URI'] . '">' . __( 'Visit component site', 'pods' ) . '</a>';
			}

			$component_data['Description'] = wpautop( trim( make_clickable( strip_tags( $component_data['Description'], 'em,strong' ) ) ) );

			if ( ! empty( $meta ) ) {
				$description_style = '';

				if ( ! empty( $component_data['Description'] ) ) {
					$description_style = ' style="padding:8px 0 4px;"';
				}

				$component_data['Description'] .= '<div class="pods-component-meta" ' . $description_style . '>' . implode( '&nbsp;&nbsp;|&nbsp;&nbsp;', $meta ) . '</div>';
			}

			$component_data = array(
				'id'          => $component_data['ID'],
				'name'        => $component_data['Name'],
				'category'    => $component_data['Category'],
				'version'     => '',
				'description' => $component_data['Description'],
				'mustuse'     => pods_v( 'MustUse', $component_data, false ),
				'toggle'      => 0,
			);

			if ( ! empty( $component_data['category'] ) ) {
				$category_url = pods_query_arg(
					array(
						'view' => sanitize_title( $component_data['category'] ),
						'pg'   => '',
						// @codingStandardsIgnoreLine
						'page' => $_GET['page'],
					)
				);

				$component_data['category'] = '<a href="' . esc_url( $category_url ) . '">' . $component_data['category'] . '</a>';
			}

			if ( isset( PodsInit::$components->settings['components'][ $component_data['id'] ] ) && 0 !== PodsInit::$components->settings['components'][ $component_data['id'] ] ) {
				$component_data['toggle'] = 1;
			} elseif ( $component_data['mustuse'] ) {
				$component_data['toggle'] = 1;
			}
		}//end foreach

		$ui = array(
			'data'             => $components,
			'total'            => count( $components ),
			'total_found'      => count( $components ),
			'items'            => __( 'Components', 'pods' ),
			'item'             => __( 'Component', 'pods' ),
			'fields'           => array(
				'manage' => array(
					'name'        => array(
						'label'   => __( 'Name', 'pods' ),
						'width'   => '30%',
						'type'    => 'text',
						'options' => array(
							'text_allow_html' => true,
						),
					),
					'category'    => array(
						'label'   => __( 'Category', 'pods' ),
						'width'   => '10%',
						'type'    => 'text',
						'options' => array(
							'text_allow_html' => true,
						),
					),
					'description' => array(
						'label'   => __( 'Description', 'pods' ),
						'width'   => '60%',
						'type'    => 'text',
						'options' => array(
							'text_allow_html'        => true,
							'text_allowed_html_tags' => 'strong em a ul ol li b i br div',
						),
					),
				),
			),
			'actions_disabled' => array( 'duplicate', 'view', 'export', 'add', 'edit', 'delete' ),
			'actions_custom'   => array(
				'toggle' => array(
					'callback' => array( $this, 'admin_components_toggle' ),
					'nonce'    => true,
				),
			),
			'filters_enhanced' => true,
			'views'            => array(
				'all'         => __( 'All', 'pods' ),
				// 'recommended' => __( 'Recommended', 'pods' ),
				'field-types' => __( 'Field Types', 'pods' ),
				'tools'       => __( 'Tools', 'pods' ),
				'integration' => __( 'Integration', 'pods' ),
				'migration'   => __( 'Migration', 'pods' ),
				'advanced'    => __( 'Advanced', 'pods' ),
			),
			'view'             => $view,
			'heading'          => array(
				'views' => __( 'Category', 'pods' ),
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => false,
			'pagination'       => false,
		);

		if ( pods_developer() ) {
			$ui['views']['dev'] = __( 'Developer Preview', 'pods' );
		}

		// Add our custom callouts.
		$this->handle_callouts_updates();

		add_filter( 'pods_ui_manage_custom_container_classes', array( $this, 'admin_manage_container_class' ) );
		add_action( 'pods_ui_manage_after_container', array( $this, 'admin_manage_callouts' ) );

		pods_ui( $ui );
	}

	/**
	 * Toggle a component on or off
	 *
	 * @param PodsUI $ui PodsUI object.
	 *
	 * @return bool
	 */
	public function admin_components_toggle( $ui ) {

		// @codingStandardsIgnoreLine
		$component = $_GET['id'];

		if ( ! empty( PodsInit::$components->components[ $component ]['PluginDependency'] ) ) {
			$dependency = explode( '|', PodsInit::$components->components[ $component ]['PluginDependency'] );

			if ( ! pods_is_plugin_active( $dependency[1] ) ) {
				$website = 'http://wordpress.org/extend/plugins/' . dirname( $dependency[1] ) . '/';

				if ( isset( $dependency[2] ) ) {
					$website = $dependency[2];
				}

				if ( ! empty( $website ) ) {
					$website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $website . '" target="_blank" rel="noopener noreferrer">' . $website . '</a>' );
				}

				$message = sprintf( __( 'The %1$s component requires that you have the <strong>%2$s</strong> plugin installed and activated.', 'pods' ), PodsInit::$components->components[ $component ]['Name'], $dependency[0] ) . $website;

				$ui->error( $message );

				$ui->manage();

				return;
			}
		}//end if

		if ( ! empty( PodsInit::$components->components[ $component ]['ThemeDependency'] ) ) {
			$dependency = explode( '|', PodsInit::$components->components[ $component ]['ThemeDependency'] );

			$check = strtolower( $dependency[1] );

			if ( strtolower( get_template() ) !== $check && strtolower( get_stylesheet() ) !== $check ) {
				$website = '';

				if ( isset( $dependency[2] ) ) {
					$website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $dependency[2] . '" target="_blank" rel="noopener noreferrer">' . $dependency[2] . '</a>' );
				}

				$message = sprintf( __( 'The %1$s component requires that you have the <strong>%2$s</strong> theme installed and activated.', 'pods' ), PodsInit::$components->components[ $component ]['Name'], $dependency[0] ) . $website;

				$ui->error( $message );

				$ui->manage();

				return;
			}
		}//end if

		if ( ! empty( PodsInit::$components->components[ $component ]['MustUse'] ) ) {
			$message = sprintf( __( 'The %s component can not be disabled from here. You must deactivate the plugin or theme that added it.', 'pods' ), PodsInit::$components->components[ $component ]['Name'] );

			$ui->error( $message );

			$ui->manage();

			return;
		}

		if ( 1 === (int) pods_v( 'toggled' ) ) {
			$toggle = PodsInit::$components->toggle( $component );

			if ( true === $toggle ) {
				$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component enabled', 'pods' ) );
			} elseif ( false === $toggle ) {
				$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component disabled', 'pods' ) );
			}

			$components = PodsInit::$components->components;

			foreach ( $components as $component => &$component_data ) {
				$toggle = 0;

				if ( isset( PodsInit::$components->settings['components'][ $component_data['ID'] ] ) ) {
					if ( 0 !== PodsInit::$components->settings['components'][ $component_data['ID'] ] ) {
						$toggle = 1;
					}
				}
				if ( true === $component_data['DeveloperMode'] ) {
					if ( ! pods_developer() ) {
						unset( $components[ $component ] );
						continue;
					}
				}

				$component_data = array(
					'id'          => $component_data['ID'],
					'name'        => $component_data['Name'],
					'description' => make_clickable( $component_data['Description'] ),
					'version'     => $component_data['Version'],
					'author'      => $component_data['Author'],
					'toggle'      => $toggle,
				);
			}//end foreach

			$ui->data = $components;

			pods_transient_clear( 'pods_components' );

			$url = pods_query_arg( array( 'toggled' => null ) );

			pods_redirect( $url );
		} elseif ( 1 === (int) pods_v( 'toggle' ) ) {
			$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component enabled', 'pods' ) );
		} else {
			$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component disabled', 'pods' ) );
		}//end if

		$ui->manage();
	}

	/**
	 * Get the admin upgrade page
	 */
	public function admin_upgrade() {

		foreach ( PodsInit::$upgrades as $old_version => $new_version ) {
			if ( version_compare( $old_version, PodsInit::$version_last, '<=' ) && version_compare( PodsInit::$version_last, $new_version, '<' ) ) {
				$new_version = str_replace( '.', '_', $new_version );

				pods_view( PODS_DIR . 'ui/admin/upgrade/upgrade_' . $new_version . '.php', compact( array_keys( get_defined_vars() ) ) );

				break;
			}
		}
	}

	/**
	 * Get the admin help page
	 */
	public function admin_help() {

		// Add our custom callouts.
		$this->handle_callouts_updates();

		add_action( 'pods_admin_after_help', array( $this, 'admin_manage_callouts' ) );

		pods_view( PODS_DIR . 'ui/admin/help.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Add pods specific capabilities.
	 *
	 * @param array $capabilities List of extra capabilities to add.
	 *
	 * @return array
	 */
	public function admin_capabilities( $capabilities ) {

		$pods = pods_api()->load_pods(
			array(
				'type'       => array(
					'settings',
					'post_type',
					'taxonomy',
				),
				'fields'     => false,
				'table_info' => false,
			)
		);

		$other_pods = pods_api()->load_pods(
			array(
				'type'       => array(
					'pod',
					'table',
				),
				'table_info' => false,
			)
		);

		$pods = array_merge( $pods, $other_pods );

		$capabilities[] = 'pods';
		$capabilities[] = 'pods_content';
		$capabilities[] = 'pods_settings';
		$capabilities[] = 'pods_components';

		foreach ( $pods as $pod ) {
			if ( 'settings' === $pod['type'] ) {
				$capabilities[] = 'pods_edit_' . $pod['name'];
			} elseif ( 'post_type' === $pod['type'] ) {
				$capability_type = pods_v_sanitized( 'capability_type_custom', $pod['options'], pods_v( 'name', $pod ) );

				if ( 'custom' === pods_v( 'capability_type', $pod['options'] ) && 0 < strlen( $capability_type ) ) {
					$capabilities[] = 'read_' . $capability_type;
					$capabilities[] = 'edit_' . $capability_type;
					$capabilities[] = 'delete_' . $capability_type;

					if ( 1 === (int) pods_v( 'capability_type_extra', $pod['options'], 1 ) ) {
						$capability_type_plural = $capability_type . 's';

						$capabilities[] = 'read_private_' . $capability_type_plural;
						$capabilities[] = 'edit_' . $capability_type_plural;
						$capabilities[] = 'edit_others_' . $capability_type_plural;
						$capabilities[] = 'edit_private_' . $capability_type_plural;
						$capabilities[] = 'edit_published_' . $capability_type_plural;
						$capabilities[] = 'publish_' . $capability_type_plural;
						$capabilities[] = 'delete_' . $capability_type_plural;
						$capabilities[] = 'delete_private_' . $capability_type_plural;
						$capabilities[] = 'delete_published_' . $capability_type_plural;
						$capabilities[] = 'delete_others_' . $capability_type_plural;
					}
				}
			} elseif ( 'taxonomy' === $pod['type'] ) {
				if ( 'custom' === pods_v( 'capability_type', $pod['options'], 'terms' ) ) {
					$capability_type = pods_v_sanitized( 'capability_type_custom', $pod['options'], pods_v( 'name', $pod ) . 's' );

					$capability_type       .= '_term';
					$capability_type_plural = $capability_type . 's';

					// Singular
					$capabilities[] = 'edit_' . $capability_type;
					$capabilities[] = 'delete_' . $capability_type;
					$capabilities[] = 'assign_' . $capability_type;
					// Plural
					$capabilities[] = 'manage_' . $capability_type_plural;
					$capabilities[] = 'edit_' . $capability_type_plural;
					$capabilities[] = 'delete_' . $capability_type_plural;
					$capabilities[] = 'assign_' . $capability_type_plural;
				}
			} else {
				$capabilities[] = 'pods_add_' . $pod['name'];
				$capabilities[] = 'pods_edit_' . $pod['name'];

				if ( isset( $pod['fields']['author'] ) && 'pick' === $pod['fields']['author']['type'] && 'user' === $pod['fields']['author']['pick_object'] ) {
					$capabilities[] = 'pods_edit_others_' . $pod['name'];
				}

				$capabilities[] = 'pods_delete_' . $pod['name'];

				if ( isset( $pod['fields']['author'] ) && 'pick' === $pod['fields']['author']['type'] && 'user' === $pod['fields']['author']['pick_object'] ) {
					$capabilities[] = 'pods_delete_others_' . $pod['name'];
				}

				$actions_enabled = pods_v( 'ui_actions_enabled', $pod['options'] );

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
					'export',
				);

				if ( ! empty( $actions_enabled ) ) {
					$actions_disabled = array(
						'view' => 'view',
					);

					foreach ( $available_actions as $action ) {
						if ( ! in_array( $action, $actions_enabled, true ) ) {
							$actions_disabled[ $action ] = $action;
						}
					}

					if ( ! in_array( 'export', $actions_disabled, true ) ) {
						$capabilities[] = 'pods_export_' . $pod['name'];
					}

					if ( ! in_array( 'reorder', $actions_disabled, true ) ) {
						$capabilities[] = 'pods_reorder_' . $pod['name'];
					}
				} elseif ( 1 === (int) pods_v( 'ui_export', $pod['options'], 0 ) ) {
					$capabilities[] = 'pods_export_' . $pod['name'];
				}//end if
			}//end if
		}//end foreach

		return $capabilities;
	}

	/**
	 * Handle ajax calls for the administration
	 */
	public function admin_ajax() {

		if ( false === headers_sent() ) {
			pods_session_start();

			header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		}

		// Sanitize input
		// @codingStandardsIgnoreLine
		$params = pods_unslash( (array) $_POST );

		foreach ( $params as $key => $value ) {
			if ( 'action' === $key ) {
				continue;
			}

			// Fixup $_POST data @codingStandardsIgnoreLine
			$_POST[ str_replace( '_podsfix_', '', $key ) ] = $_POST[ $key ];

			// Fixup $params with unslashed data
			$params[ str_replace( '_podsfix_', '', $key ) ] = $value;

			// Unset the _podsfix_* keys
			unset( $params[ $key ] );
		}

		$params = (object) $params;

		$methods = array(
			'add_pod'                 => array( 'priv' => true ),
			'save_pod'                => array( 'priv' => true ),
			'load_sister_fields'      => array( 'priv' => true ),
			'process_form'            => array( 'custom_nonce' => true ),
			// priv handled through nonce
							'upgrade' => array( 'priv' => true ),
			'migrate'                 => array( 'priv' => true ),
		);

		/**
		 * AJAX Callbacks in field editor
		 *
		 * @since unknown
		 *
		 * @param array     $methods Callback methods.
		 * @param PodsAdmin $obj     PodsAdmin object.
		 */
		$methods = apply_filters( 'pods_admin_ajax_methods', $methods, $this );

		if ( ! isset( $params->method ) || ! isset( $methods[ $params->method ] ) ) {
			pods_error( __( 'Invalid AJAX request', 'pods' ), $this );
		}

		$defaults = array(
			'priv'         => null,
			'name'         => $params->method,
			'custom_nonce' => null,
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

		$method_name = $method->name;

		$params = apply_filters( "pods_api_{$method_name}", $params, $method );

		$api = pods_api();

		$api->display_errors = false;

		if ( 'upgrade' === $method->name ) {
			$output = (string) pods_upgrade( $params->version )->ajax( $params );
		} elseif ( 'migrate' === $method->name ) {
			$output = (string) apply_filters( 'pods_api_migrate_run', $params );
		} else {
			if ( ! method_exists( $api, $method->name ) ) {
				pods_error( __( 'API method does not exist', 'pods' ), $this );
			} elseif ( 'save_pod' === $method->name ) {
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
		}//end if

		// Output in json format
		if ( false !== $output ) {

			/**
			 * Pods Admin AJAX request was successful
			 *
			 * @since  2.6.8
			 *
			 * @param array               $params AJAX parameters.
			 * @param array|object|string $output Output for AJAX request.
			 */
			do_action( "pods_admin_ajax_success_{$method->name}", $params, $output );

			if ( is_array( $output ) || is_object( $output ) ) {
				wp_send_json( $output );
			} else {
				// @codingStandardsIgnoreLine
				echo $output;
			}
		} else {
			pods_error( __( 'There was a problem with your request.', 'pods' ) );
		}//end if

		die();
		// KBAI!
	}

	/**
	 * Profiles the Pods configuration
	 *
	 * @param null|string|array $pod             Which Pod(s) to get configuration for. Can be a the name
	 *                                           of one Pod, or an array of names of Pods, or null, which is the
	 *                                           default, to profile all Pods.
	 * @param bool              $full_field_info If true all info about each field is returned. If false,
	 *                                           which is the default only name and type, will be returned.
	 *
	 * @return array
	 *
	 * @since 2.7.0
	 */
	public function configuration( $pod = null, $full_field_info = false ) {

		$api = pods_api();

		if ( null === $pod ) {
			$the_pods = $api->load_pods();
		} elseif ( is_array( $pod ) ) {
			foreach ( $pod as $p ) {
				$the_pods[] = $api->load_pod( $p );
			}
		} else {
			$the_pods[] = $api->load_pod( $pod );
		}

		foreach ( $the_pods as $the_pod ) {
			$configuration[ $the_pod['name'] ] = array(
				'name'    => $the_pod['name'],
				'ID'      => $the_pod['id'],
				'storage' => $the_pod['storage'],
				'fields'  => $the_pod['fields'],
			);
		}

		if ( ! $full_field_info ) {
			foreach ( $the_pods as $the_pod ) {
				$fields = $configuration[ $the_pod['name'] ]['fields'];

				unset( $configuration[ $the_pod['name'] ]['fields'] );

				foreach ( $fields as $field ) {
					$info = array(
						'name' => $field['name'],
						'type' => $field['type'],
					);

					if ( 'pick' === $info['type'] ) {
						$info['pick_object'] = $field['pick_object'];

						if ( isset( $field['pick_val'] ) && '' !== $field['pick_val'] ) {
							$info['pick_val'] = $field['pick_val'];
						}
					}

					if ( is_array( $info ) ) {
						$configuration[ $the_pod['name'] ]['fields'][ $field['name'] ] = $info;
					}

					unset( $info );

				}//end foreach
			}//end foreach
		}//end if

		if ( is_array( $configuration ) ) {
			return $configuration;
		}

	}

	/**
	 * Build UI for extending REST API, if makes sense to do so.
	 *
	 * @since  2.6.0
	 *
	 * @access protected
	 */
	protected function rest_admin() {

		if ( function_exists( 'register_rest_field' ) ) {
			add_filter(
				'pods_admin_setup_edit_field_options', array(
					$this,
					'add_rest_fields_to_field_editor',
				), 12, 2
			);
			add_filter( 'pods_admin_setup_edit_field_tabs', array( $this, 'add_rest_field_tab' ), 12 );
		}

		add_filter( 'pods_admin_setup_edit_tabs', array( $this, 'add_rest_settings_tab' ), 12, 2 );
		add_filter( 'pods_admin_setup_edit_options', array( $this, 'add_rest_settings_tab_fields' ), 12, 2 );

	}

	/**
	 * Check if Pod type <em>could</em> extend core REST API response
	 *
	 * @since  2.5.6
	 *
	 * @access protected
	 *
	 * @param array $pod Pod options.
	 *
	 * @return bool
	 */
	protected function restable_pod( $pod ) {

		$type = $pod['type'];

		$restable_types = array(
			'post_type',
			'user',
			'taxonomy',
			'media',
		);

		return in_array( $type, $restable_types, true );

	}

	/**
	 * Add a rest api tab.
	 *
	 * @since 2.6.0
	 *
	 * @param array $tabs Tab array.
	 * @param array $pod  Pod options.
	 *
	 * @return array
	 */
	public function add_rest_settings_tab( $tabs, $pod ) {

		$tabs['rest-api'] = __( 'REST API', 'pods' );

		return $tabs;

	}

	/**
	 * Populate REST API tab.
	 *
	 * @since 0.1.0
	 *
	 * @param array $options Tab options.
	 * @param array $pod     Pod options.
	 *
	 * @return array
	 */
	public function add_rest_settings_tab_fields( $options, $pod ) {

		if ( ! function_exists( 'register_rest_field' ) ) {
			$options['rest-api'] = array(
				'no_dependencies' => array(
					'label' => sprintf( __( 'Pods REST API support requires WordPress 4.3.1 or later and the %s or later.', 'pods' ), '<a href="https://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/" target="_blank" rel="noopener noreferrer">WordPress REST API 2.0-beta9</a>' ),
					'help'  => sprintf( __( 'See %s for more information.', 'pods' ), '<a href="https://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/" target="_blank" rel="noopener noreferrer">https://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/</a>' ),
					'type'  => 'html',
				),
			);
		} elseif ( $this->restable_pod( $pod ) ) {
			$options['rest-api'] = array(
				'rest_enable' => array(
					'label'      => __( 'Enable', 'pods' ),
					'help'       => __( 'Add REST API support for this Pod.', 'pods' ),
					'type'       => 'boolean',
					'default'    => '',
					'dependency' => true,
				),
				'rest_base'   => array(
					'label'      => __( 'REST Base (if any)', 'pods' ),
					'help'       => __( 'This will form the url for the route. Default / empty value here will use the pod name.', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'rest_enable' => true ),
				),
				'read_all'    => array(
					'label'      => __( 'Show All Fields (read-only)', 'pods' ),
					'help'       => __( 'Show all fields in REST API. If unchecked fields must be enabled on a field by field basis.', 'pods' ),
					'type'       => 'boolean',
					'default'    => '',
					'depends-on' => array( 'rest_enable' => true ),
				),
				'write_all'   => array(
					'label'             => __( 'Allow All Fields To Be Updated', 'pods' ),
					'help'              => __( 'Allow all fields to be updated via the REST API. If unchecked fields must be enabled on a field by field basis.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'name', $pod ),
					'boolean_yes_label' => '',
					'depends-on'        => array( 'rest_enable' => true ),
				),

			);

		} else {
			$options['rest-api'] = array(
				'not_restable' => array(
					'label' => __( 'Pods REST API support covers post type, taxonomy and user Pods.', 'pods' ),
					'help'  => sprintf( __( 'See %s for more information.', 'pods' ), '<a href="https://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/" target="_blank" rel="noopener noreferrer">https://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/"</a>' ),
					'type'  => 'html',
				),
			);

		}//end if

		return $options;

	}

	/**
	 * Add a REST API section to advanced tab of field editor.
	 *
	 * @since 2.5.6
	 *
	 * @param array $options Tab options.
	 * @param array $pod     Pod options.
	 *
	 * @return array
	 */
	public function add_rest_fields_to_field_editor( $options, $pod ) {

		if ( $this->restable_pod( $pod ) ) {
			$options['rest'][ __( 'Read/ Write', 'pods' ) ]                = array(
				'rest_read'  => array(
					'label'   => __( 'Read via REST API?', 'pods' ),
					'help'    => __( 'Should this field be readable via the REST API? You must enable REST API support for this Pod.', 'pods' ),
					'type'    => 'boolean',
					'default' => '',
				),
				'rest_write' => array(
					'label'   => __( 'Write via REST API?', 'pods' ),
					'help'    => __( 'Should this field be writeable via the REST API? You must enable REST API support for this Pod.', 'pods' ),
					'type'    => 'boolean',
					'default' => '',
				),
			);
			$options['rest'][ __( 'Relationship Field Options', 'pods' ) ] = array(
				'rest_pick_response' => array(
					'label'      => __( 'Response Type', 'pods' ),
					'help'       => __( 'This will determine what amount of data for the related items will be returned.', 'pods' ),
					'type'       => 'pick',
					'default'    => 'array',
					'depends-on' => array( 'type' => 'pick' ),
					'dependency' => true,
					'data'       => array(
						'array' => __( 'Full', 'pods' ),
						'id'    => __( 'ID only', 'pods' ),
						'name'  => __( 'Name', 'pods' ),
					),
				),
				'rest_pick_depth'    => array(
					'label'      => __( 'Depth', 'pods' ),
					'help'       => __( 'How far to traverse relationships in response', 'pods' ),
					'type'       => 'number',
					'default'    => '2',
					'depends-on' => array(
						'type'               => 'pick',
						'rest_pick_response' => 'array',
					),
				),

			);

		}//end if

		return $options;

	}

	/**
	 * Add REST field tab
	 *
	 * @since 2.5.6
	 *
	 * @param array $tabs Tab list.
	 *
	 * @return array
	 */
	public function add_rest_field_tab( $tabs ) {

		$tabs['rest'] = __( 'REST API', 'pods' );

		return $tabs;
	}

	/**
	 * Add Pods-specific debug info to Site Info debug area.
	 *
	 * @since 2.7.13
	 *
	 * @param array $info Debug info.
	 *
	 * @return array Debug info with Pods-specific debug info added.
	 */
	public function add_debug_information( $info ) {
		$info['pods'] = array(
			'label'       => 'Pods',
			'description' => __( 'Debug information for Pods installations.', 'pods' ),
			'fields'      => array(
				'pods-server-software'               => array(
					'label' => __( 'Server Software', 'pods' ),
					'value' => ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A',
				),
				'pods-user-agent'                    => array(
					'label' => __( 'Your User Agent', 'pods' ),
					'value' => ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A',
				),
				'pods-session-save-path'             => array(
					'label' => __( 'Session Save Path', 'pods' ),
					'value' => session_save_path(),
				),
				'pods-session-save-path-exists'      => array(
					'label' => __( 'Session Save Path Exists', 'pods' ),
					'value' => file_exists( session_save_path() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-session-save-path-writable'    => array(
					'label' => __( 'Session Save Path Writeable', 'pods' ),
					'value' => is_writable( session_save_path() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-session-max-lifetime'          => array(
					'label' => __( 'Session Max Lifetime', 'pods' ),
					'value' => ini_get( 'session.gc_maxlifetime' ),
				),
				'pods-opcode-cache-apc'              => array(
					'label' => __( 'Opcode Cache: Apc', 'pods' ),
					'value' => function_exists( 'apc_cache_info' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-opcode-cache-memcached'        => array(
					'label' => __( 'Opcode Cache: Memcached', 'pods' ),
					'value' => class_exists( 'eaccelerator_put' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-opcode-cache-opcache'          => array(
					'label' => __( 'Opcode Cache: OPcache', 'pods' ),
					'value' => function_exists( 'opcache_get_status' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-opcode-cache-redis'            => array(
					'label' => __( 'Opcode Cache: Redis', 'pods' ),
					'value' => class_exists( 'xcache_set' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-object-cache-apc'              => array(
					'label' => __( 'Object Cache: APC', 'pods' ),
					'value' => function_exists( 'apc_cache_info' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-object-cache-apcu'             => array(
					'label' => __( 'Object Cache: APCu', 'pods' ),
					'value' => function_exists( 'apcu_cache_info' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-object-cache-memcache'         => array(
					'label' => __( 'Object Cache: Memcache', 'pods' ),
					'value' => class_exists( 'Memcache' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-object-cache-memcached'        => array(
					'label' => __( 'Object Cache: Memcached', 'pods' ),
					'value' => class_exists( 'Memcached' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-object-cache-redis'            => array(
					'label' => __( 'Object Cache: Redis', 'pods' ),
					'value' => class_exists( 'Redis' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-memory-current-usage'          => array(
					'label' => __( 'Current Memory Usage', 'pods' ),
					'value' => number_format_i18n( memory_get_usage() / 1024 / 1024, 3 ) . 'M',
				),
				'pods-memory-current-usage-real'     => array(
					'label' => __( 'Current Memory Usage (real)', 'pods' ),
					'value' => number_format_i18n( memory_get_usage( true ) / 1024 / 1024, 3 ) . 'M',
				),
				'pods-network-wide'                  => array(
					'label' => __( 'Pods Network-Wide Activated', 'pods' ),
					'value' => is_plugin_active_for_network( basename( PODS_DIR ) . '/init.php' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-install-location'              => array(
					'label' => __( 'Pods Install Location', 'pods' ),
					'value' => PODS_DIR,
				),
				'pods-developer'                     => array(
					'label' => __( 'Pods Developer Activated' ),
					'value' => ( pods_developer() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-tableless-mode'                => array(
					'label' => __( 'Pods Tableless Mode Activated', 'pods' ),
					'value' => ( pods_tableless() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-light-mode'                    => array(
					'label' => __( 'Pods Light Mode Activated', 'pods' ),
					'value' => ( pods_light() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-strict'                        => array(
					'label' => __( 'Pods Strict Activated' ),
					'value' => ( pods_strict() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-allow-deprecated'              => array(
					'label' => __( 'Pods Allow Deprecated' ),
					'value' => ( pods_allow_deprecated() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-api-cache'                     => array(
					'label' => __( 'Pods API Cache Activated' ),
					'value' => ( pods_api_cache() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
				'pods-shortcode-allow-evaluate-tags' => array(
					'label' => __( 'Pods Shortcode Allow Evaluate Tags' ),
					'value' => ( pods_shortcode_allow_evaluate_tags() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				),
			),
		);

		return $info;
	}

}
