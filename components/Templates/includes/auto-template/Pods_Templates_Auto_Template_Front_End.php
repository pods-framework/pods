<?php
if ( class_exists( 'Pods_PFAT_Frontend' ) ) {
	return;
}

/**
 * Class Pods_Templates_Auto_Template_Front_End
 *
 * Replaces Pods_PFAT_Frontend
 */
class Pods_Templates_Auto_Template_Front_End {

	/**
	 * Currently filtered content functions.
	 *
	 * @var array
	 *
	 * @since 2.7.16
	 */
	private $filtered_content = array();

	/**
	 * List of auto pods.
	 *
	 * @var array
	 *
	 * @since 2.7.25
	 */
	private $auto_pods = array();

	/**
	 * Pods_Templates_Auto_Template_Front_End constructor.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			add_action( 'wp', array( $this, 'set_frontier_style_script' ) );

			// Load autopods.
			$this->auto_pods();
		}

		// Setup initial hooks.
		add_action( 'template_redirect', array( $this, 'hook_content' ) );
	}

	/**
	 * Add hooks for output.
	 *
	 * @since 2.6.6
	 */
	public function hook_content() {
		$possible_pods = $this->auto_pods();

		// Always register archive hooks.
		foreach ( $possible_pods as $pod_name => $pod ) {
			$filter = pods_v( 'archive_filter', $pod, '', true );
			if ( $filter ) {
				$this->filtered_content[ $filter ] = 10.5;
			}
		}

		// Optionally register single hook for current object.
		$obj = get_queried_object();
		$pod = null;
		switch ( true ) {
			case $obj instanceof WP_Post:
				$pod = $obj->post_type;
				break;
			case $obj instanceof WP_Term:
				$pod = $obj->taxonomy;
				break;
			case $obj instanceof WP_User:
				$pod = 'user';
				break;
		}

		if ( ! empty( $possible_pods[ $pod ] ) ) {
			// No need to check for default hooks, this is already done in auto_pods().
			$filter = pods_v( 'single_filter', $possible_pods[ $pod ], '', true );
			if ( $filter ) {
				$this->filtered_content[ $filter ] = 10.5;
			}
		}

		$this->install_hooks();
	}

	/**
	 * Install the hooks specified by the filtered_content member.
	 *
	 * @since 2.7.16
	 */
	public function install_hooks() {

		foreach ( $this->filtered_content as $filter => $priority ) {
			add_filter( $filter, array( $this, 'front' ), $priority, 2 );
		}
	}

	/**
	 * Remove the hooks specified by the filtered_content member.
	 *
	 * @since 2.7.16
	 */
	public function remove_hooks() {

		foreach ( $this->filtered_content as $filter => $priority ) {
			remove_filter( $filter, array( $this, 'front' ) );
		}
	}

	/**
	 * Get all post type and taxonomy Pods.
	 *
	 * @since 2.4.5
	 *
	 * @return array Of Pod names.
	 */
	public function the_pods() {

		// use the cached results.
		$key      = 'pods_pfat_the_pods';
		$the_pods = pods_transient_get( $key );

		// check if we already have the results cached & use it if we can.
		if ( ! is_array( $the_pods ) ) {
			// get all post type pods.
			$the_pods = pods_api()->load_pods(
				array(
					'type'       => array(
						'post_type',
						'taxonomy',
						'comment',
						'user',
					),
					'labels'     => true,
					'fields'     => false,
					'table_info' => false,
				)
			);

			// cache the results.
			pods_transient_set( $key, $the_pods, WEEK_IN_SECONDS );

		}

		return $the_pods;

	}

	/**
	 * Get all Pods with auto template enable and its settings.
	 *
	 * @return array With info about auto template settings per post type.
	 *
	 * @since 2.4.5
	 */
	public function auto_pods() {
		if ( ! empty( $this->auto_pods ) ) {
			return $this->auto_pods;
		}

		/**
		 * Filter to override all settings for which templates are used.
		 *
		 * Note: If this filter does not return null, all back-end settings are ignored. To add to settings with a filter, use 'pods_pfat_auto_pods';
		 *
		 * @param array $auto_pods Array of parameters to use instead of those from settings.
		 *
		 * @return array Settings arrays for each post type.
		 *
		 * @since 2.4.5
		 */
		$auto_pods = apply_filters( 'pods_pfat_auto_pods_override', null );
		if ( ! is_null( $auto_pods ) ) {
			return $auto_pods;
		}

		// try to get cached results of this method.
		$key       = 'pods_pfat_auto_pods';
		$auto_pods = pods_transient_get( $key );

		// check if we already have the results cached & use it if we can.
		if ( ! is_array( $auto_pods ) ) {
			$default_hooks = array(
				'post_type' => 'the_content',
				'taxonomy'  => 'get_the_archive_description',
				'user'      => 'get_the_author_description',
				'comment'   => 'comment_text',
			);

			// get possible pods
			$the_pods = $this->the_pods();

			// start output array empty.
			$auto_pods = array();

			// get pods api class.
			$api = pods_api();

			// loop through each to see if auto templates is enabled.
			foreach ( $the_pods as $the_pod => $the_pod_label ) {
				// get this Pods' data.
				$pod_data = $api->load_pod( array( 'name' => $the_pod, 'fields' => false, 'table_info' => false ) );
				$options  = pods_v( 'options', $pod_data, array() );

				// if auto template is enabled add info about Pod to array.
				if ( 1 === (int) pods_v( 'pfat_enable', $options, 0 ) ) {
					$type = pods_v( 'type', $pod_data, false, true );

					// Get default hook.
					$default_hook = pods_v( $type, $default_hooks, '' );

					// check if pfat_single and pfat_archive are set.
					$single           = pods_v( 'pfat_single', $options, false, true );
					$archive          = pods_v( 'pfat_archive', $options, false, true );
					$single_append    = pods_v( 'pfat_append_single', $options, true, true );
					$archive_append   = pods_v( 'pfat_append_archive', $options, true, true );
					$single_filter    = pods_v( 'pfat_filter_single', $options, $default_hook, true );
					$archive_filter   = pods_v( 'pfat_filter_archive', $options, $default_hook, true );
					$run_outside_loop = pods_v( 'pfat_run_outside_loop', $options, false, true );

					if ( 'true' === $single ) {
						$single = '';
					}

					if ( 'true' === $archive ) {
						$archive = '';
					}

					if ( 'custom' === $single_filter ) {
						$single_filter = pods_v( 'pfat_filter_single_custom', $options, $default_hook, true );
					}

					if ( 'custom' === $archive_filter ) {
						$archive_filter = pods_v( 'pfat_filter_archive_custom', $options, $default_hook, true );
					}

					if ( 'taxonomy' === $type ) {
						// We are treating taxonomy post archive as a taxonomy singular so disable the filter.
						$archive_filter = '';
					}

					// Check if it's a type that supports archive templates. This will be used for admin checks.
					if ( 'post_type' === $type ) {
						if ( ! $pod_data->is_extended() ) {
							// Custom post types.
							$has_archive = pods_v( 'has_archive', $options, false, true );
						} else {
							// Extended post types (use what they have).
							// $has_archive = get_post_type_object( $the_pod )->has_archive;

							// Force archive as true since we don't allow overriding the option in the admin.
							$has_archive = true;
						}
					} elseif ( 'taxonomy' === $type ) {
						// We are treating taxonomy post archive as a taxonomy singular.
						$has_archive = false;
					} else {
						// All other types have singular and archive views.
						$has_archive = true;
					}

					// Build output array.
					$auto_pods[ $the_pod ] = array(
						'name'             => $the_pod,
						'label'            => $the_pod_label,
						'single'           => $single,
						'archive'          => $archive,
						'single_append'    => $single_append,
						'archive_append'   => $archive_append,
						'has_archive'      => $has_archive,
						'single_filter'    => $single_filter,
						'archive_filter'   => $archive_filter,
						'run_outside_loop' => $run_outside_loop,
						'type'             => $type,
					);
				}//end if
			}//end foreach

			// cache the results.
			pods_transient_set( $key, $auto_pods, WEEK_IN_SECONDS );
		}//end if

		/**
		 * Add to or change settings.
		 *
		 * Use this filter to change or add to the settings set in the back-end for this plugin. Has no effect if 'pods_pfat_auto_pods_override' filter is being used.
		 *
		 * @param array $auto_pods Array of parameters to use instead of those from settings.
		 *
		 * @return array Settings arrays for each post type.
		 *
		 * @since 2.4.5
		 */
		$this->auto_pods = apply_filters( 'pods_pfat_auto_pods', $auto_pods );

		return $this->auto_pods;
	}

	/**
	 * Fetches the current Pod name.
	 *
	 * @return string Pod name.
	 *
	 * @since 2.4.5
	 */
	public function get_pod_name() {

		// If we are in the loop, fair game to use the post itself to help determine the current post type.
		if ( in_the_loop() ) {
			return get_post_type();
		}

		// Start by getting current post or stdClass object.
		$obj = get_queried_object();

		// See if we are on a post type and if so, set $current_post_type to post type.
		if ( $obj instanceof WP_Post ) {
			$pod_name = $obj->post_type;
		} elseif ( $obj instanceof WP_Term ) {
			$pod_name = $obj->taxonomy;
		} elseif ( $obj instanceof WP_User ) {
			$pod_name = 'user';
		} elseif ( isset( $obj->name ) ) {
			$pod_name = $obj->name;
		} elseif ( is_home() ) {
			$pod_name = 'post';
		} else {
			$pod_name = false;
		}

		return $pod_name;
	}

	/**
	 * Outputs templates after the content as needed.
	 *
	 * @param string $content Post content.
	 * @param mixed  $obj     Object context.
	 *
	 * @uses  'the_content' filter
	 *
	 * @return string Post content with the template appended if appropriate.
	 *
	 * @since 2.4.5
	 */
	public function front( $content, $obj = null ) {

		static $running = false;

		if ( $running ) {
			return $content;
		}

		$running = true;

		// Now use other methods in class to build array to search in/ use.
		$possible_pods = $this->auto_pods();

		$current_filter = current_filter();
		$in_the_loop    = in_the_loop();

		if ( null === $obj && $in_the_loop ) {
			$obj = get_post();
		}

		if ( null !== $obj ) {
			$pod_info = $this->get_pod_info( $obj );

			if ( empty( $pod_info['pod_type'] ) ) {
				$obj = null;

				$pod_info = $this->get_pod_info();
			}
		} else {
			$pod_info = $this->get_pod_info();
		}

		$pod_id   = $pod_info['pod_id'];
		$pod_name = $pod_info['pod_name'];
		$pod_type = $pod_info['pod_type'];

		// @todo Media?

		// Check if $current_post_type is the key of the array of possible pods.
		if ( $pod_type && isset( $possible_pods[ $pod_name ] ) ) {
			$pod_name_and_id = array( $pod_name, $pod_id );

			/**
			 * Change which pod and item to run the template against. The
			 * default pod is the the post type of the post about to be
			 * displayed, the default item is the post about to be displayed,
			 * except outside the loop in a taxonomy archive, in which case it
			 * is the term the archive is for.
			 *
			 * @since 2.7.17
			 *
			 * @param string          $pod_name_and_id An array of the name of the pod to run the template against and the ID of the item in that pod to use.
			 * @param string          $pod_name        The name of the pod from which the template was selected.
			 * @param WP_post|WP_Term $obj             The object that is about to be displayed.
			 */
			$pod_name_and_id = apply_filters( 'pods_auto_template_pod_name_and_id', $pod_name_and_id, $pod_name, $obj );

			$pod_name = $pod_name_and_id[0];
			$pod_id   = $pod_name_and_id[1];

			if ( empty( $possible_pods[ $pod_name ] ) ) {
				$running = false;

				return $content;
			}

			// Get array for the current post type.
			$auto_pod = $possible_pods[ $pod_name ];

			if ( 'post' === $pod_type ) {
				if ( ! $in_the_loop && ! pods_v( 'run_outside_loop', $auto_pod, false ) ) {
					// If outside of the loop, exit quickly.
					$running = false;

					return $content;
				}
			}

			$pod = pods( $pod_name, $pod_id );

			// Heuristically decide if this is single or archive.
			$type        = 'archive';
			$type_filter = 'archive_filter';
			$type_append = 'archive_append';

			$is_single = true;

			if ( null !== $obj ) {
				$pod_info_check = $this->get_pod_info();

				if ( $pod_info !== $pod_info_check ) {
					$is_single = false;
				}
			}

			if ( $is_single && ( ! $in_the_loop || is_singular( $pod_name ) ) ) {
				$type        = 'single';
				$type_filter = 'single_filter';
				$type_append = 'single_append';
			}

			if ( ! empty( $auto_pod[ $type ] ) && $current_filter === $auto_pod[ $type_filter ] ) {
				// Load the template.
				$content = $this->load_template( $auto_pod[ $type ], $content, $pod, $auto_pod[ $type_append ] );
			}
		}//end if

		$running = false;

		return $content;

	}

	/**
	 * Get list of pod information based on an object or the detected current context.
	 *
	 * @since 2.7.25
	 *
	 * @param null|object $obj The object to get pod information from or null to detect from current context.
	 *
	 * @return array List of pod information.
	 */
	public function get_pod_info( $obj = null ) {
		$pod_id   = null;
		$pod_type = '';
		$pod_name = '';

		if ( null !== $obj ) {
			if ( $obj instanceof WP_Post ) {
				$pod_type = 'post';
				$pod_name = $obj->post_type;
				$pod_id   = (int) $obj->ID;
			} elseif ( $obj instanceof WP_Term ) {
				$pod_type = 'taxonomy';
				$pod_name = $obj->taxonomy;
				$pod_id   = (int) $obj->term_id;
			} elseif ( $obj instanceof WP_Comment ) {
				$pod_type = 'comment';
				$pod_name = 'comment';
				$pod_id   = (int) $obj->comment_ID;
			} elseif ( $obj instanceof WP_User ) {
				$pod_type = 'user';
				$pod_name = 'user';
				$pod_id   = (int) $obj->ID;
			} elseif ( is_numeric( $obj ) ) {
				$current_filter = current_filter();

				$possible_pods = $this->auto_pods();

				foreach ( $possible_pods as $possible_pod => $possible_pod_data ) {
					if (
						$current_filter === $possible_pod_data['single_filter'] ||
						$current_filter === $possible_pod_data['archive_filter']
					) {
						$pod_id   = (int) $obj;
						$pod_name = $possible_pod;
						$pod_type = $possible_pod_data['type'];

						break;
					}
				}
			}

			return compact( 'pod_id', 'pod_type', 'pod_name' );
		}

		// Build Pods object for current item.
		$obj      = get_queried_object();
		$pod_id   = (int) get_queried_object_id();
		$pod_type = '';
		$pod_name = '';

		if ( is_author() ) {
			$pod_type = 'user';
			$pod_name = 'user';
		} elseif ( is_singular() || in_the_loop() ) {
			if ( null === $obj ) {
				$obj = get_post();
			}

			return $this->get_pod_info( $obj );
		} elseif ( $obj && ( is_tax() || is_category() || is_tag() ) ) {
			return $this->get_pod_info( $obj );
		} else {
			// Backwards compatibility.
			$post = get_post();

			if ( $post ) {
				return $this->get_pod_info( $post );
			}
		}

		return compact( 'pod_id', 'pod_type', 'pod_name' );
	}

	/**
	 * Attach Pods Template to $content.
	 *
	 * @param string      $template_name The name of a Pods Template to load.
	 * @param string      $content       Post content.
	 * @param Pods        $pod           Current Pods object.
	 * @param bool|string $append        Optional. Whether to append, prepend or replace content. Defaults to true,
	 *                                   which appends, if false, content is replaced, if 'prepend' content is
	 *                                   prepended.
	 *
	 * @return string $content with Pods Template appended if template exists.
	 *
	 * @since 2.4.5
	 */
	public function load_template( $template_name, $content, $pod, $append = true ) {

		// Allow magic tags for content type related templates.
		$template_name = trim( $pod->do_magic_tags( $template_name ) );

		/**
		 * Change which template -- by name -- to be used.
		 *
		 * @since 2.5.6
		 *
		 * @param string      $template_name The name of a Pods Template to load.
		 * @param Pods        $pod           Current Pods object.
		 * @param bool|string $append        Whether Template will be appended (true), prepended ("prepend") or replaced (false).
		 */
		$template_name = apply_filters( 'pods_auto_template_template_name', $template_name, $pod, $append );

		$template = $pod->template( $template_name );

		// Restore the hooks for subsequent posts.
		$this->install_hooks();

		// Check if we have a valid template.
		if ( ! is_null( $template ) ) {
			// If so append it to content or replace content.
			if ( $append === 'replace' ) {
				$content = $template;
			} elseif ( $append === 'prepend' ) {
				$content = $template . $content;
			} elseif ( $append || $append === 'append' ) {
				$content .= $template;
			} else {
				$content = $template;
			}
		}

		return $content;
	}

	/**
	 * Sets Styles and Scripts from the Frontier template addons.
	 *
	 * @since 2.4.5
	 */
	public function set_frontier_style_script() {

		if ( ! class_exists( 'Pods_Frontier' ) ) {
			return;
		}

		// Get the current post type.
		$pod_name = $this->get_pod_name();

		// Now use other methods in class to build array to search in/ use.
		$possible_pods = $this->auto_pods();

		if ( isset( $possible_pods[ $pod_name ] ) ) {
			$this_pod = $possible_pods[ $pod_name ];

			if ( $this_pod['single'] && is_singular( $pod_name ) ) {
				$template = $this_pod['single'];

			} elseif ( $this_pod['archive'] && is_post_type_archive( $pod_name ) ) {
				// If pfat_archive was set try to use that template.
				// Check if we are on an archive of the post type.
				$template = $this_pod['archive'];

			} elseif ( is_home() && $this_pod['archive'] && 'post' === $pod_name ) {
				// If pfat_archive was set and we're in the blog index, try to append template.
				$template = $this_pod['archive'];

			} elseif ( is_tax( $pod_name ) ) {
				// If is taxonomy archive of the selected taxonomy.
				// If pfat_single was set try to use that template.
				if ( $this_pod['archive'] ) {
					$template = $this_pod['archive'];
				}
			}//end if

			if ( isset( $template ) ) {
				global $frontier_styles, $frontier_scripts;

				$template_post = pods()->api->load_template( array( 'name' => $template ) );

				if ( ! empty( $template_post['id'] ) ) {
					// Got a template - check for styles & scripts.
					$meta = get_post_meta( $template_post['id'], 'view_template', true );

					$frontier = new Pods_Frontier();

					if ( ! empty( $meta['css'] ) ) {
						$frontier_styles .= $meta['css'];
					}

					if ( ! empty( $meta['js'] ) ) {
						$frontier_scripts .= $meta['js'];
					}
				}
			}
		}//end if
	}
}
