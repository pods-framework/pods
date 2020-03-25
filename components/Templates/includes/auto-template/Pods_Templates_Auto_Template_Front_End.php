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
	 * Pods_Templates_Auto_Template_Front_End constructor.
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			add_action( 'wp', array( $this, 'set_frontier_style_script' ) );
		}

		// Setup initial hooks.
		add_action( 'template_redirect', array( $this, 'hook_content' ) );
		// Setup hooks after each new post in the loop.
		add_action( 'the_post', array( $this, 'hook_content' ) );

	}

	/**
	 * Get the filter used for a Pod.
	 * @since  1.7.2
	 * @param  string  $current_post_type
	 * @param  array   $possible_pods
	 * @return string
	 */
	public function get_pod_filter( $current_post_type = '', $possible_pods = array() ) {
		$filter = 'the_content';

		if ( ! $current_post_type ) {
			// get the current post type.
			$current_post_type = $this->current_post_type();
		}

		if ( ! $possible_pods ) {
			// now use other methods in class to build array to search in/ use.
			$possible_pods = $this->auto_pods();
		}

		// check if $current_post_type is the key of the array of possible pods.
		if ( isset( $possible_pods[ $current_post_type ] ) ) {
			$this_pod = $possible_pods[ $current_post_type ];

			if ( in_the_loop() ) {
				$filter = pods_v( 'archive_filter', $this_pod, $filter, true );
			} else {
				$filter = pods_v( 'single_filter', $this_pod, $filter, true );
			}
		}

		return $filter;
	}

	/**
	 * Add hooks for output.
	 *
	 * @since 2.6.6
	 */
	public function hook_content(){
		$filter = $this->get_pod_filter();

		/**
		 * Allows plugin to append/replace the_excerpt.
		 *
		 * Default is false, set to true to enable.
		 */
		if ( ! defined( 'PFAT_USE_ON_EXCERPT' ) ) {
			define( 'PFAT_USE_ON_EXCERPT', false );
		}

		$this->filtered_content[ $filter ] = 10.5;

		if ( PFAT_USE_ON_EXCERPT ) {
			$this->filtered_content['the_excerpt'] = 10;
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
			add_filter( $filter, array( $this, 'front' ), $priority );
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
		$key      = '_pods_pfat_the_pods';
		$the_pods = pods_transient_get( $key );

		// check if we already have the results cached & use it if we can.
		if ( false === $the_pods ) {
			// get all post type pods.
			$the_pods = pods_api()->load_pods(
				array(
					'type'  => array(
						'taxonomy',
						'post_type',
					),
					'names' => true,
				)
			);

			// cache the results.
			pods_transient_set( $key, $the_pods );

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
		$key       = '_pods_pfat_auto_pods';
		$auto_pods = pods_transient_get( $key );

		// check if we already have the results cached & use it if we can.
		if ( $auto_pods === false ) {
			// get possible pods
			$the_pods = $this->the_pods();

			// start output array empty.
			$auto_pods = array();

			// get pods api class.
			$api = pods_api();

			// loop through each to see if auto templates is enabled.
			foreach ( $the_pods as $the_pod => $the_pod_label ) {
				// get this Pods' data.
				$pod_data = $api->load_pod( array( 'name' => $the_pod ) );

				// if auto template is enabled add info about Pod to array.
				if ( 1 == pods_v( 'pfat_enable', $pod_data['options'] ) ) {
					// check if pfat_single and pfat_archive are set.
					$single           = pods_v( 'pfat_single', $pod_data['options'], false, true );
					$archive          = pods_v( 'pfat_archive', $pod_data['options'], false, true );
					$single_append    = pods_v( 'pfat_append_single', $pod_data['options'], true, true );
					$archive_append   = pods_v( 'pfat_append_archive', $pod_data['options'], true, true );
					$single_filter    = pods_v( 'pfat_filter_single', $pod_data['options'], 'the_content', true );
					$archive_filter   = pods_v( 'pfat_filter_archive', $pod_data['options'], 'the_content', true );
					$run_outside_loop = pods_v( 'pfat_run_outside_loop', $pod_data['options'], false, true );
					$type             = pods_v( 'type', $pod_data, false, true );
					// check if it's a post type that has an archive.
					if ( $type === 'post_type' && $the_pod !== 'post' || $the_pod !== 'page' ) {
						$has_archive = pods_v( 'has_archive', $pod_data['options'], false, true );
					} else {
						$has_archive = true;
					}

					// build output array.
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
			pods_transient_set( $key, $auto_pods );
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
		$auto_pods = apply_filters( 'pods_pfat_auto_pods', $auto_pods );

		return $auto_pods;

	}

	/**
	 * Fetches the current post type.
	 *
	 * @return string current post type.
	 *
	 * @since 2.4.5
	 */
	public function current_post_type() {

		// start by getting current post or stdClass object.
		global $wp_query;
		$obj = $wp_query->get_queried_object();

		// see if we are on a post type and if so, set $current_post_type to post type.
		if ( isset( $obj->post_type ) ) {
			$current_post_type = $obj->post_type;

		} elseif ( isset( $obj->taxonomy ) ) {
			$current_post_type = $obj->taxonomy;
		} elseif ( isset( $obj->name ) ) {
			$current_post_type = $obj->name;
		} elseif ( is_home() ) {
			$current_post_type = 'post';
		} else {
			$current_post_type = false;
		}

		// Once we are in the loop, fair game to use the post itself to help determine the current post type.
		if ( ( ! $current_post_type || is_array( $current_post_type ) ) && in_the_loop() ) {
			$current_post_type = get_post_type();
		}

		return $current_post_type;
	}

	/**
	 * Outputs templates after the content as needed.
	 *
	 * @param string $content Post content
	 *
	 * @uses  'the_content' filter
	 *
	 * @return string Post content with the template appended if appropriate.
	 *
	 * @since 2.4.5
	 */
	public function front( $content ) {

		// get the current post type.
		$current_post_type = $this->current_post_type();

		// now use other methods in class to build array to search in/ use.
		$possible_pods = $this->auto_pods();

		// build Pods object for current item.
		global $post;
		$pod_name = $current_post_type;
		$pod_item = $post->ID;
		if ( in_the_loop() ) {
			$pod_name = $post->post_type;
		} else {
			// Outside the loop in a taxonomy, we want the term.
			if ( is_tax() ) {
				$obj      = get_queried_object();
				$pod_name = $obj->slug;
				$pod_item = $obj->term_id;
			}
		}

		// check if $current_post_type is the key of the array of possible pods.
		if ( isset( $possible_pods[ $pod_name ] ) ) {
			// get array for the current post type.
			$this_pod = $possible_pods[ $pod_name ];

			$filter = $this->get_pod_filter( $pod_name, $possible_pods );

			if ( current_filter() !== $filter ) {
				return $content;
			}

			if ( ! in_the_loop() && ! pods_v( 'run_outside_loop', $this_pod, false ) ) {
				// If outside of the loop, exit quickly.
				return $content;
			}

			$pod_name_and_item = array( $pod_name, $pod_item );
			/**
			 * Change which pod and item to run the template against. The
			 * default pod is the the post type of the post about to be
			 * displayed, the default item is the post about to be displayed,
			 * except outside the loop in a taxonomy archive, in which case it
			 * is the term the archive is for.
			 *
			 * @since 2.7.16
			 *
			 * @param string $pod_name_and_item An array of the name of the pod to run the template against and the item (ID or slug) of the item in that pod to use.
			 * @param string $template_source   The name of the pod from which the template was selected.
			 * @param Post   $post              The Post object that is about to be displayed.
			 */
			$pod_name_and_item = apply_filters( 'pods_auto_template_pod_name_and_item', $pod_name_and_item, $current_post_type, $post );
			$pod               = pods( $pod_name_and_item[0], $pod_name_and_item[1] );

			// Heuristically decide if this is single or archive.
			$s_or_a        = 'archive';
			$s_or_a_filter = 'archive_filter';
			$s_or_a_append = 'archive_append';
			if ( ! in_the_loop() || is_singular() ) {
				$s_or_a        = 'single';
				$s_or_a_filter = 'single_filter';
				$s_or_a_append = 'single_append';
			}

			if ( ! empty( $this_pod[ $s_or_a ] ) && current_filter() == $this_pod[ $s_or_a_filter ] ) {
				// load the template.
				$content = $this->load_template( $this_pod[ $s_or_a ], $content, $pod, $this_pod[ $s_or_a_append ] );
			}
		}//end if

		return $content;

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

		// prevent infinite loops caused by this method acting on post_content.
		$this->remove_hooks();

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

		// check if we have a valid template.
		if ( ! is_null( $template ) ) {
			// if so append it to content or replace content.
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

		// cet the current post type.
		$current_post_type = $this->current_post_type();

		// now use other methods in class to build array to search in/ use.
		$possible_pods = $this->auto_pods();

		if ( isset( $possible_pods[ $current_post_type ] ) ) {
			$this_pod = $possible_pods[ $current_post_type ];

			if ( $this_pod['single'] && is_singular( $current_post_type ) ) {
				$template = $this_pod['single'];

			} elseif ( $this_pod['archive'] && is_post_type_archive( $current_post_type ) ) {
				// if pfat_archive was set try to use that template.
				// check if we are on an archive of the post type.
				$template = $this_pod['archive'];

			} elseif ( is_home() && $this_pod['archive'] && $current_post_type === 'post' ) {
				// if pfat_archive was set and we're in the blog index, try to append template.
				$template = $this_pod['archive'];

			} elseif ( is_tax( $current_post_type ) ) {
				// if is taxonomy archive of the selected taxonomy.
				// if pfat_single was set try to use that template.
				if ( $this_pod['archive'] ) {
					$template = $this_pod['archive'];
				}
			}//end if

			if ( isset( $template ) ) {
				global $frontier_styles, $frontier_scripts;

				$template_post = pods()->api->load_template( array( 'name' => $template ) );

				if ( ! empty( $template_post['id'] ) ) {
					// got a template - check for styles & scripts.
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
