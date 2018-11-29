<?php
/**
 * Name: Helpers
 *
 * Description: A holdover from Pods 1.x for backwards compatibility purposes, you most likely don't need these and we
 * recommend you use our WP filters and actions instead.
 *
 * Version: 2.3
 *
 * Category: Advanced
 *
 * Menu Page: edit.php?post_type=_pods_helper
 * Menu Add Page: post-new.php?post_type=_pods_helper
 *
 * External: pods-helpers/pods-helpers.php
 *
 * @package    Pods\Components
 * @subpackage Helpers
 */

if ( class_exists( 'Pods_Helpers' ) ) {
	return;
}

class Pods_Helpers extends PodsComponent {

	/**
	 * Pods object
	 *
	 * @var object
	 *
	 * @since 2.0.0
	 */
	static $obj = null;

	/**
	 * Object type
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	private $object_type = '_pods_helper';

	/**
	 * {@inheritdoc}
	 */
	public function init() {

		$args = array(
			'label'        => 'Pod Helpers',
			'labels'       => array( 'singular_name' => 'Pod Helper' ),
			'public'       => false,
			'can_export'   => false,
			'show_ui'      => true,
			'show_in_menu' => false,
			'query_var'    => false,
			'rewrite'      => false,
			'has_archive'  => false,
			'hierarchical' => false,
			'supports'     => array( 'title', 'author', 'revisions' ),
			'menu_icon'    => 'dashicons-pods',
		);

		if ( ! pods_is_admin() ) {
			$args['capability_type'] = 'pods_helper';
		}

		$args = PodsInit::object_label_fix( $args, 'post_type' );

		register_post_type( $this->object_type, apply_filters( 'pods_internal_register_post_type_object_helper', $args ) );

		if ( is_admin() ) {
			add_filter( 'post_updated_messages', array( $this, 'setup_updated_messages' ), 10, 1 );

			add_action( 'dbx_post_advanced', array( $this, 'edit_page_form' ) );

			add_action( 'pods_meta_groups', array( $this, 'add_meta_boxes' ) );
			add_filter( 'get_post_metadata', array( $this, 'get_meta' ), 10, 4 );
			add_filter( 'update_post_metadata', array( $this, 'save_meta' ), 10, 4 );

			add_action( 'pods_meta_save_pre_post__pods_helper', array( $this, 'fix_filters' ), 10, 5 );
			add_action( 'post_updated', array( $this, 'clear_cache' ), 10, 3 );
			add_action( 'delete_post', array( $this, 'clear_cache' ), 10, 1 );
			add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-' . $this->object_type, array( $this, 'remove_bulk_actions' ) );

			add_filter( 'builder_layout_filter_non_layout_post_types', array( $this, 'disable_builder_layout' ) );
		}

	}

	public function disable_builder_layout( $post_types ) {

		$post_types[] = $this->object_type;

		return $post_types;
	}

	/**
	 * Update Post Type messages
	 *
	 * @param array $messages
	 *
	 * @return array
	 * @since 2.0.2
	 */
	public function setup_updated_messages( $messages ) {

		global $post, $post_ID;

		$post_type = get_post_type_object( $this->object_type );

		$labels = $post_type->labels;

		$messages[ $post_type->name ] = array(
			1  => sprintf( __( '%1$s updated. <a href="%2$s">%3$s</a>', 'pods' ), $labels->singular_name, esc_url( get_permalink( $post_ID ) ), $labels->view_item ),
			2  => __( 'Custom field updated.', 'pods' ),
			3  => __( 'Custom field deleted.', 'pods' ),
			4  => sprintf( __( '%s updated.', 'pods' ), $labels->singular_name ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'pods' ), $labels->singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( '%1$s published. <a href="%2$s">%3$s</a>', 'pods' ), $labels->singular_name, esc_url( get_permalink( $post_ID ) ), $labels->view_item ),
			7  => sprintf( __( '%s saved.', 'pods' ), $labels->singular_name ),
			8  => sprintf( __( '%1$s submitted. <a target="_blank" href="%2$s">Preview %3$s</a>', 'pods' ), $labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels->singular_name ),
			9  => sprintf(
				__( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %4$s</a>', 'pods' ), $labels->singular_name,
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $labels->singular_name
			),
			10 => sprintf( __( '%1$s draft updated. <a target="_blank" href="%2$s">Preview %3$s</a>', 'pods' ), $labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels->singular_name ),
		);

		if ( false === (boolean) $post_type->public ) {
			$messages[ $post_type->name ][1] = sprintf( __( '%s updated.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][6] = sprintf( __( '%s published.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][8] = sprintf( __( '%s submitted.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][9] = sprintf(
				__( '%1$s scheduled for: <strong>%2$s</strong>.', 'pods' ), $labels->singular_name,
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
			);
			$messages[ $post_type->name ][10] = sprintf( __( '%s draft updated.', 'pods' ), $labels->singular_name );
		}

		return $messages;
	}

	/**
	 * Enqueue styles
	 *
	 * @since 2.0.0
	 */
	public function admin_assets() {

		wp_enqueue_style( 'pods-styles' );
	}

	/**
	 * Fix filters, specifically removing balanceTags
	 *
	 * @since 2.0.1
	 */
	public function fix_filters( $data, $pod = null, $id = null, $groups = null, $post = null ) {

		remove_filter( 'content_save_pre', 'balanceTags', 50 );
	}

	/**
	 * Remove unused row actions
	 *
	 * @since 2.0.5
	 */
	public function remove_row_actions( $actions, $post ) {

		global $current_screen;

		if ( ! is_object( $current_screen ) || $this->object_type != $current_screen->post_type ) {
			return $actions;
		}

		if ( isset( $actions['view'] ) ) {
			unset( $actions['view'] );
		}

		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		// W3 Total Cache
		if ( isset( $actions['pgcache_purge'] ) ) {
			unset( $actions['pgcache_purge'] );
		}

		return $actions;
	}

	/**
	 * Remove unused bulk actions
	 *
	 * @since 2.0.5
	 */
	public function remove_bulk_actions( $actions ) {

		if ( isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		return $actions;
	}

	/**
	 * Clear cache on save
	 *
	 * @since 2.0.0
	 */
	public function clear_cache( $data, $pod = null, $id = null, $groups = null, $post = null ) {

		$old_post = $id;

		if ( ! is_object( $id ) ) {
			$old_post = null;
		}

		if ( is_object( $post ) && $this->object_type != $post->post_type ) {
			return;
		}

		if ( ! is_array( $data ) && 0 < $data ) {
			$post = $data;
			$post = get_post( $post );
		}

		if ( $this->object_type == $post->object_type ) {
			pods_transient_clear( 'pods_object_helpers' );
		}
	}

	/**
	 * Change post title placeholder text
	 *
	 * @since 2.0.0
	 */
	public function set_title_text( $text, $post ) {

		return __( 'Enter helper name here', 'pods' );
	}

	/**
	 * Edit page form
	 *
	 * @since 2.0.0
	 */
	public function edit_page_form() {

		global $post_type;

		if ( $this->object_type != $post_type ) {
			return;
		}

		add_filter( 'enter_title_here', array( $this, 'set_title_text' ), 10, 2 );
	}

	/**
	 * Add meta boxes to the page
	 *
	 * @since 2.0.0
	 */
	public function add_meta_boxes() {

		$pod = array(
			'name' => $this->object_type,
			'type' => 'post_type',
		);

		if ( isset( PodsMeta::$post_types[ $pod['name'] ] ) ) {
			return;
		}

		$fields = array(
			array(
				'name'    => 'helper_type',
				'label'   => __( 'Helper Type', 'pods' ),
				'type'    => 'pick',
				'default' => 'display',
				'data'    => array(
					'input'       => 'Input (change form fields)',
					'display'     => 'Display (change field output when using magic tags)',
					'pre_save'    => 'Pre-Save (change form fields before saving)',
					'post_save'   => 'Post-Save',
					'pre_delete'  => 'Pre-Delete',
					'post_delete' => 'Post-Delete',
				),
			),
			array(
				'name'  => 'code',
				'label' => __( 'Code', 'pods' ),
				'type'  => 'code',
			),
		);

		pods_group_add( $pod, __( 'Helper', 'pods' ), $fields, 'normal', 'high' );
	}

	/**
	 * Get the fields
	 *
	 * @param null $_null
	 * @param null $post_ID
	 * @param null $meta_key
	 * @param bool $single
	 *
	 * @return array|bool|int|mixed|null|string|void
	 */
	public function get_meta( $_null, $post_ID = null, $meta_key = null, $single = false ) {

		if ( 'code' == $meta_key ) {
			$post = get_post( $post_ID );

			if ( is_object( $post ) && $this->object_type == $post->post_type ) {
				return $post->post_content;
			}
		}

		return $_null;
	}

	/**
	 * Save the fields
	 *
	 * @param        $_null
	 * @param int    $post_ID
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool|int|null
	 */
	public function save_meta( $_null, $post_ID = null, $meta_key = null, $meta_value = null ) {

		if ( 'code' == $meta_key ) {
			$post = get_post( $post_ID );

			if ( is_object( $post ) && $this->object_type == $post->post_type ) {
				$postdata = array(
					'ID'           => $post_ID,
					'post_content' => $meta_value,
				);

				remove_filter( current_filter(), array( $this, __FUNCTION__ ) );

				$revisions = false;

				if ( has_action( 'pre_post_update', 'wp_save_post_revision' ) ) {
					remove_action( 'pre_post_update', 'wp_save_post_revision' );

					$revisions = true;
				}

				wp_update_post( (object) $postdata );
				// objects will be automatically sanitized
				if ( $revisions ) {
					add_action( 'pre_post_update', 'wp_save_post_revision' );
				}

				return true;
			}//end if
		}//end if

		return $_null;
	}

	/**
	 * @static
	 *
	 * Run a helper within a Pod Page or WP Template
	 *
	 * $params['helper'] string Helper name
	 * $params['value'] string Value to run Helper on
	 * $params['name'] string Field name
	 *
	 * @param array $params An associative array of parameters
	 * @param null  $obj
	 *
	 * @return mixed Anything returned by the helper
	 * @since 2.0.0
	 */
	public static function helper( $params, $obj = null ) {

		/**
		 * @var $obj Pods
		 */
		if ( ! empty( $obj ) ) {
			self::$obj =& $obj;
		} else {
			$obj =& self::$obj;
		}

		if ( empty( $obj ) || ! is_object( $obj ) ) {
			return '';
		}

		$defaults = array(
			'helper'     => '',
			'value'      => '',
			'name'       => '',
			'deprecated' => false,
		);

		if ( is_array( $params ) ) {
			$params = array_merge( $defaults, $params );
		} else {
			$params = $defaults;
		}

		$params = (object) $params;

		if ( empty( $params->helper ) ) {
			return pods_error( 'Helper name required', $obj );
		} elseif ( ! is_array( $params->helper ) ) {
			$params->helper = trim( $params->helper );
		}

		if ( ! isset( $params->value ) ) {
			$params->value = null;
		}

		if ( true === $params->deprecated && is_array( $params->value ) && ! empty( $params->value ) && ! isset( $params->value[0] ) ) {
			$params->value = array( $params->value );
		}

		if ( ! isset( $params->name ) ) {
			$params->name = null;
		}

		$helper = $obj->api->load_helper( array( 'name' => $params->helper ) );

		ob_start();

		if ( ! empty( $helper ) && ! empty( $helper['code'] ) ) {
			$code = $helper['code'];

			$code = str_replace( '$this->', '$obj->', $code );

			$value =& $params->value;
			$name  =& $params->name;

			$_safe_params = $params;

			if ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) {
				eval( "?>{$code}" );
			} else {
				echo $code;
			}

			$params = $_safe_params;
		} elseif ( is_callable( (string) $params->helper ) ) {
			$params->helper = (string) $params->helper;

			$disallowed = array(
				'system',
				'exec',
				'popen',
				'eval',
				'preg_replace',
				'create_function',
				'include',
				'include_once',
				'require',
				'require_once',
			);

			$allowed = array();

			/**
			 * Allows adjusting the disallowed callbacks as needed.
			 *
			 * @param array $disallowed List of callbacks not allowed.
			 * @param array $params     Parameters used by Pods::helper() method.
			 *
			 * @since 2.7.0
			 */
			$disallowed = apply_filters( 'pods_helper_disallowed_callbacks', $disallowed, get_object_vars( $params ) );

			/**
			 * Allows adjusting the allowed allowed callbacks as needed.
			 *
			 * @param array $allowed List of callbacks explicitly allowed.
			 * @param array $params  Parameters used by Pods::helper() method.
			 *
			 * @since 2.7.0
			 */
			$allowed = apply_filters( 'pods_helper_allowed_callbacks', $allowed, get_object_vars( $params ) );

			// Clean up helper callback (if string)
			$params->helper = strip_tags( str_replace( array( '`', chr( 96 ) ), "'", $params->helper ) );

			$is_allowed = false;

			if ( ! empty( $allowed ) ) {
				if ( in_array( $params->helper, $allowed, true ) ) {
					$is_allowed = true;
				}
			} elseif ( ! in_array( $params->helper, $disallowed, true ) ) {
				$is_allowed = true;
			}

			if ( $is_allowed ) {
				echo call_user_func( $params->helper, $params->value, $params->name, $params, $obj );
			}
		}//end if

		$slug = $helper['slug'];

		$out = ob_get_clean();

		$out = apply_filters( 'pods_helpers_post_helper', $out, $params, $helper );
		$out = apply_filters( "pods_helpers_post_helper_{$slug}", $out, $params, $helper );

		return $out;
	}
}
