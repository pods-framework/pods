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
	 * Currently filtered content functions
	 *
	 * @since 2.7.2
	 *
	 * @var array of strings
	 */
	private $filtered_content;

	function __construct() {

        $this->filtered_content = array();
		if( !is_admin() ){
			add_action( 'wp', array( $this, 'set_frontier_style_script' ) );
		}

		add_action( 'template_redirect', array( $this, 'hook_content' ) );


	}

	/**
	 * Add hooks for output
	 *
	 * @since 2.6.6
	 */
	public function hook_content(){
        $this->remove_hooks();
        $this->filtered_content = array();

		// get the current post type
		$current_post_type = $this->current_post_type();

        if ( !$current_post_type || is_array($current_post_type) ) {
            // If we're outside the loop, provide a chance to recompute
            // the current_post_type when we are in the loop
            if (!in_the_loop()) {
                add_action( 'the_post', array( $this, 'hook_content' ) );
            }
        }

		//now use other methods in class to build array to search in/ use
		$possible_pods = $this->auto_pods();


		//check if $current_post_type is the key of the array of possible pods
		if ( isset( $possible_pods[ $current_post_type ] ) ) {

			$this_pod = $possible_pods[ $current_post_type ];

            if ( !empty( $this_pod[ 'single_filter' ] ) ) {
                $this->filtered_content[ $this_pod [ 'single_filter' ] ] = 10.5;
            }
            if ( !empty( $this_pod[ 'archive_filter' ] ) ) {
                $this->filtered_content[ $this_pod [ 'archive_filter' ] ] = 10.5;
            }
		}
		/**
		 * Allows plugin to append/replace the_excerpt
		 *
		 * Default is false, set to true to enable.
		 */
		if ( !defined( 'PFAT_USE_ON_EXCERPT' ) ) {
			define( 'PFAT_USE_ON_EXCERPT', false );
		}

        if (  PFAT_USE_ON_EXCERPT  ) {
            $this->filtered_content['the_excerpt'] = 10;
		}

        $this->install_hooks();
	}

    /**
     * Install the hooks specified by the filtered_content member
     *
     * @since 2.7.2
     *
     */
    function install_hooks() {
        foreach ( $this->filtered_content as $filter => $priority ) {
            add_filter( $filter, array( $this, 'front' ), $priority );
        }
    }

    /**
     * Remove the hooks specified by the filtered_content member
     *
     * @since 2.7.2
     *
     */
    function remove_hooks() {
        foreach ( $this->filtered_content as $filter => $priority) {
            remove_filter( $filter, array( $this, 'front' ) );
        }
    }

	/**
	 * Get all post type and taxonomy Pods
	 *
	 * @since 2.4.5
	 *
	 * @return array Of Pod names.
	 */
	function the_pods() {

		//use the cached results
		$key = '_pods_pfat_the_pods';
		$the_pods = pods_transient_get( $key  );

		//check if we already have the results cached & use it if we can.
		if ( false === $the_pods ) {
			//get all post type pods
			$the_pods = pods_api()->load_pods( array(
					'type' => array(
						'taxonomy',
						'post_type'
					),
					'names' => true )
			);

			//cache the results
			pods_transient_set( $key, $the_pods );

		}

		return $the_pods;

	}

	/**
	 * Get all Pods with auto template enable and its settings
	 *
	 * @return array With info about auto template settings per post type
	 *
	 * @since 2.4.5
	 */
	function auto_pods() {
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
		if ( !is_null( $auto_pods ) ) {
			return $auto_pods;
		}

		//try to get cached results of this method
		$key = '_pods_pfat_auto_pods';
		$auto_pods = pods_transient_get( $key );
	

		//check if we already have the results cached & use it if we can.
		if ( $auto_pods === false  ) {
			//get possible pods
			$the_pods = $this->the_pods();

			//start output array empty
			$auto_pods = array();

			//get pods api class
			$api = pods_api();

			//loop through each to see if auto templates is enabled
			foreach ( $the_pods as $the_pod => $the_pod_label ) {
				//get this Pods' data.
				$pod_data = $api->load_pod( array( 'name' => $the_pod ) );

				//if auto template is enabled add info about Pod to array
				if ( 1 == pods_v( 'pfat_enable', $pod_data[ 'options' ] ) ) {
					//check if pfat_single and pfat_archive are set
					$single = pods_v( 'pfat_single', $pod_data[ 'options' ], false, true );
					$archive = pods_v( 'pfat_archive', $pod_data[ 'options' ], false, true );
					$single_append = pods_v( 'pfat_append_single', $pod_data[ 'options' ], true, true );
					$archive_append = pods_v( 'pfat_append_archive', $pod_data[ 'options' ], true, true );
					$single_filter = pods_v( 'pfat_filter_single', $pod_data[ 'options' ], 'the_content', true );
					$archive_filter = pods_v( 'pfat_filter_archive', $pod_data[ 'options' ], 'the_content', true );
					$run_outside_loop = pods_v( 'pfat_run_outside_loop', $pod_data[ 'options' ], false, true );
					$type = pods_v( 'type', $pod_data, false, true );
					//check if it's a post type that has an archive
					if ( $type === 'post_type' && $the_pod !== 'post' || $the_pod !== 'page' ) {
						$has_archive = pods_v( 'has_archive', $pod_data['options'], false, true );
					}
					else {
						$has_archive = true;
					}

					if( empty( $single_filter ) ){
						$single_filter = 'the_content';
					}

					if( empty( $archive_filter  ) ){
						$archive_filter = 'the_content';
					}

					//build output array
					$auto_pods[ $the_pod ] = array(
						'name' => $the_pod,
						'label'	=> $the_pod_label,
						'single' => $single,
						'archive' => $archive,
						'single_append' => $single_append,
						'archive_append' => $archive_append,
						'has_archive'	=> $has_archive,
						'single_filter' => $single_filter,
						'archive_filter' => $archive_filter,
						'run_outside_loop' => $run_outside_loop,
						'type' => $type,
					);
				}

			} //endforeach

			//cache the results
			pods_transient_set( $key, $auto_pods );
		}

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
	function current_post_type() {
		//start by getting current post or stdClass object
		global $wp_query;
		$obj = $wp_query->get_queried_object();

		//see if we are on a post type and if so, set $current_post_type to post type
		if ( isset( $obj->post_type ) ) {
			$current_post_type = $obj->post_type;

		}
		elseif ( isset( $obj->taxonomy ) ) {
			$current_post_type = $obj->taxonomy;
		}
		elseif ( isset ( $obj->name ) ) {
			$current_post_type = $obj->name;
		}
		elseif ( is_home() ) {
			$current_post_type = 'post';
		}
		else {
			$current_post_type = false;
		}

        // Once we are in the loop, fair game to use the post itself to
        // help determine the current post type
        if ( ( !$current_post_type || is_array($current_post_type) )
             && in_the_loop() ) {
            global $post;
            $current_post_type = $post->post_type;
        }

		return $current_post_type;
	}

	/**
	 * Outputs templates after the content as needed.
	 *
	 * @param string $content Post content
	 *
	 * @uses 'the_content' filter
	 *
	 * @return string Post content with the template appended if appropriate.
	 *
	 * @since 2.4.5
	 */
	function front( $content ) {

		// get the current post type
		$current_post_type = $this->current_post_type();

		//now use other methods in class to build array to search in/ use
		$possible_pods = $this->auto_pods();


		//check if $current_post_type is the key of the array of possible pods
		if ( isset( $possible_pods[ $current_post_type ] ) ) {
			//get array for the current post type
			$this_pod = $possible_pods[ $current_post_type ];

			if ( !in_the_loop() && !pods_v( 'run_outside_loop', $this_pod, false ) ) {
				// If outside of the loop, exit quickly
				return $content;
			}

			//build Pods object for current item
			global $post;
            $pod_name = get_post_type($post->ID);
            /**
             * Change which pod -- by name -- to run the template against. The
             * default pod is the the post type of the post about to be
             * displayed.
             *
             * @since 2.7.2
             *
             * @param string  $pod_name         The name of the pod to run the template against.
             * @param string  $template_source  The name of the pod from which the template was selected.
             * @param Post    $post             The Post object that is about to be displayed.
             */
            $pod_name = apply_filters('pods_auto_template_pod_name', $pod_name, $current_post_type, $post);
			$pods = pods( $pod_name, $post->ID );

            // Heuristically decide if this is single or archive
            $s_or_a = 'archive';
            $s_or_a_append = 'archive_append';
            if ( !in_the_loop() || is_singular() ) {
                $s_or_a = 'single';
                $s_or_a_append = 'single_append';
            }

			if ( $this_pod[ $s_or_a ] ) {
				//load the template
				$content = $this->load_template( $this_pod[ $s_or_a ], $content , $pods, $this_pod[ $s_or_a_append ] );
			}
		}

		return $content;

	}

	/**
	 * Attach Pods Template to $content
	 *
	 * @param string        $template_name  The name of a Pods Template to load.
	 * @param string        $content        Post content
	 * @param Pods          $pods           Current Pods object.
	 * @param bool|string   $append         Optional. Whether to append, prepend or replace content. Defaults to true, which appends, if false, content is replaced, if 'prepend' content is prepended.
	 *
	 * @return string $content with Pods Template appended if template exists
	 *
	 * @since 2.4.5
	 */
	function load_template( $template_name, $content, $pods, $append = true  ) {

		//prevent infinite loops caused by this method acting on post_content
		$this->remove_hooks();

        // Allow template chosen to depend on post type or content via magic
        // tags
        $template_name = $pods->do_magic_tags($template_name);
		/**
		 * Change which template -- by name -- to be used.
		 *
		 * @since 2.5.6
		 *
		 * @param string        $template_name  The name of a Pods Template to load.
		 * @param Pods          $pods           Current Pods object.
		 * @param bool|string   $append         Whether Template will be appended (true), prepended ("prepend") or replaced (false).
		 */
		$template_name = apply_filters( 'pods_auto_template_template_name', $template_name, $pods, $append );

		$template = $pods->template( $template_name );
        // Restore the hooks for subsequent posts
		$this->install_hooks();

		//check if we have a valid template
		if ( !is_null( $template ) ) {
			//if so append it to content or replace content.

			if ( $append === 'replace' ) {
				$content = $template;
			}
			elseif ( $append === 'prepend' ) {
				$content = $template . $content;
			}
			elseif ( $append || $append === 'append' ) {
				$content = $content . $template;
			}
			else {
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
	function set_frontier_style_script(){

		if( ! class_exists( 'Pods_Frontier' ) ) {
			return;
		}

		// cet the current post type
		$current_post_type = $this->current_post_type();

		//now use other methods in class to build array to search in/ use
		$possible_pods = $this->auto_pods();

		if ( isset( $possible_pods[ $current_post_type ] ) ) {

			$this_pod = $possible_pods[ $current_post_type ];

			if ( $this_pod[ 'single' ] && is_singular( $current_post_type ) ) {
				//set template
				$template = $this_pod[ 'single' ];

			}
			//if pfat_archive was set try to use that template
			//check if we are on an archive of the post type
			elseif ( $this_pod[ 'archive' ] && is_post_type_archive( $current_post_type ) ) {
				//set template
				$template = $this_pod[ 'archive' ];

			}
			//if pfat_archive was set and we're in the blog index, try to append template
			elseif ( is_home() && $this_pod[ 'archive' ] && $current_post_type === 'post'  ) {
				//set template
				$template = $this_pod[ 'archive' ];

			}
			//if is taxonomy archive of the selected taxonomy
			elseif ( is_tax( $current_post_type )  ) {
				//if pfat_single was set try to use that template
				if ( $this_pod[ 'archive' ] ) {
					//set template
					$template = $this_pod[ 'archive' ];
				}

			}

			if( isset( $template ) ){
				global $frontier_styles, $frontier_scripts;

				$template_post = pods()->api->load_template( array('name' => $template ) );

				if( !empty( $template_post['id'] ) ){
					// got a template - check for styles & scripts
					$meta = get_post_meta($template_post['id'], 'view_template', true);

					$frontier = new Pods_Frontier;
					if(!empty($meta['css'])){
						$frontier_styles .= $meta['css'];
					}

					if(!empty($meta['js'])){
						$frontier_scripts .= $meta['js'];
					}
				}
			}

		}
	}
}
