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
	function __construct() {

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
		$filter = 'the_content';

		// get the current post type
		$current_post_type = $this->current_post_type();

		//now use other methods in class to build array to search in/ use
		$possible_pods = $this->auto_pods();


		//check if $current_post_type is the key of the array of possible pods
		if ( isset( $possible_pods[ $current_post_type ] ) ) {

			$this_pod = $possible_pods[ $current_post_type ];

			if ( is_singular( $current_post_type ) ) {
				$filter =  $this_pod[ 'single_filter' ];
			} elseif ( is_post_type_archive( $current_post_type ) ) {
				$filter =  $this_pod[ 'archive_filter' ];

			} elseif ( is_home() && $current_post_type === 'post'  ) {
				$filter =  $this_pod[ 'archive_filter' ];
			} elseif ( is_tax( $current_post_type )  ) {
				$filter =  $this_pod[ 'archive_filter' ];

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


		add_filter( $filter, array( $this, 'front' ), 10.5 );

		if (  PFAT_USE_ON_EXCERPT  ) {
			add_filter( 'the_excerpt', array ( $this, 'front' ) );
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
					$type = pods_v( 'type', $pod_data, false, true );
					//check if it's a post type that has an arhive
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

			//build Pods object for current item
			global $post;
			$pods = pods( $current_post_type, $post->ID );

			//get array for the current post type
			$this_pod = $possible_pods[ $current_post_type ];


			if ( $this_pod[ 'single' ] && is_singular( $current_post_type ) ) {
				//load the template
				$content = $this->load_template( $this_pod[ 'single' ], $content , $pods, $this_pod[ 'single_append' ] );
			}
			//if pfat_archive was set try to use that template
			//check if we are on an archive of the post type
			elseif ( $this_pod[ 'archive' ] && is_post_type_archive( $current_post_type ) ) {
				//load the template
				$content = $this->load_template( $this_pod[ 'archive' ], $content , $pods, $this_pod[ 'archive_append' ] );

			}
			//if pfat_archive was set and we're in the blog index, try to append template
			elseif ( is_home() && $this_pod[ 'archive' ] && $current_post_type === 'post'  ) {
				//append the template
				$content = $this->load_template( $this_pod[ 'archive' ], $content , $pods, $this_pod[ 'archive_append' ] );

			}
			//if is taxonomy archive of the selected taxonomy
			elseif ( is_tax( $current_post_type )  ) {
				//if pfat_single was set try to use that template
				if ( $this_pod[ 'archive' ] ) {
					//append the template
					$content = $this->load_template( $this_pod[ 'archive' ], $content , $pods, $this_pod[ 'archive_append' ] );
				}

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
		remove_filter( 'the_content', array( $this, 'front' ) );

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
		add_filter( 'the_content', array( $this, 'front' ) );

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
