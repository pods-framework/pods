<?php

/**
 * @package Pods
 */
class Pods_Metadata {
	
	/**
	 * @var bool
	 */ 
	public static $in_field = false;
	
	/**
	 * @var array
	 */ 
	public static $cmb2_types = array();

	public function __construct() {
		
		add_filter( 'cmb2_meta_boxes', array( $this, 'metaboxes' ) );
		add_filter( 'cmb2_show_on', array( $this, 'group_show_on' ), 10, 3 );

		// @todo Handle field sanitization
		// @todo Figure out what to do about pre/post save hooks and CMB2 hooks
		// @todo Handle required / custom validation
		// @todo Utilize CMB2 on all other form area / saves for other object types (with pods_developer check)

	}

	/**
	 * Define the metabox and field configurations.
	 *
	 * @param array $meta_boxes
	 *
	 * @return array
	 */
	public function metaboxes( array $meta_boxes ) {

		self::$cmb2_types = array(
			'avatar'    => 'file',
			'boolean'   => 'checkbox',
			'code'      => 'textarea_code',
			'color'     => 'colorpicker',
			'currency'  => 'text_money',
			'date'      => 'text_date',
			'datetime'  => 'text_datetime',
			'email'     => 'text_email',
			'file'      => 'file',
			'heading'   => 'title',
			'html'      => 'title',
			'loop'      => 'group',
			'number'    => 'text_small',
			'paragraph' => 'textarea',
			'password'  => 'text_medium',
			'phone'     => 'text_medium',
			'pick'      => 'select',
			'slug'      => 'text_medium',
			'taxonomy'  => 'taxonomy_select',
			'text'      => 'text',
			'time'      => 'text_datetime',
			'website'   => 'text_url',
			'wysiwyg'   => 'wysiwyg'
		);

		$pods_groups = array(
			'post_type' => Pods_Init::$meta->groups_get( 'post_type', true ),

			// @todo CMB2 Taxonomy support
			'taxonomy'  => Pods_Init::$meta->groups_get( 'taxonomy', true ),

			// @todo CMB2 Media support
			'media'     => Pods_Init::$meta->groups_get( 'media', true ),

			// @todo CMB2 Comment support
			'comment'   => Pods_Init::$meta->groups_get( 'comment', true ),

			'user'      => Pods_Init::$meta->groups_get( 'user', true ),

			// @todo Hook into settings pages to output CMB2
			'settings'  => Pods_Init::$meta->groups_get( 'settings', true ),

			// @todo CMB2 Pods ACT support
			'pod'       => Pods_Init::$meta->groups_get( 'pod', true )
		);

		foreach ( $pods_groups as $type => $object_groups ) {
			$pods_meta_box = array(
				'id'           => 'pods_cmb2_' . $type,
				'title'        => '',
				'object_types' => array(),
				'show_on'      => array(),
				'context'      => 'normal',
				'priority'     => 'default',
				'show_names'   => true,
				'fields'       => array()
			);

			if ( 'user' == $type ) {
				$pods_meta_box[ 'object_types' ][ ] = 'user';
			}

			foreach ( $object_groups as $name => $groups ) {
				$pods_group_meta_box = $pods_meta_box;

				$pods_group_meta_box[ 'id' ] .= '_' . $name;

				if ( 'post_type' == $type ) {
					$pods_group_meta_box[ 'object_types' ][ ] = $name;
				} elseif ( 'user' != $type ) {
					$pods_group_meta_box[ 'show_on' ] = array(
						'key'   => 'options-page',
						'value' => array( 'unknown' )
					);
				}

				foreach ( $groups as $group ) {
					$meta_box = $pods_group_meta_box;

					$meta_box[ 'id' ] .= '_' . $group[ 'name' ];
					$meta_box[ 'title' ]  = $group[ 'label' ];
					$meta_box[ 'fields' ] = $this->setup_fields( $meta_box[ 'id' ], $group[ 'fields' ], $group );
					$meta_box[ '_pods_group' ] = $group;

					$meta_boxes[ $meta_box[ 'id' ] ] = $meta_box;
				}
			}
		}

		return $meta_boxes;

	}

	/**
	 * Setup fields from group for use with CMB2
	 *
	 * @param string            $group_id Group Input ID
	 * @param array             $fields   Pod Fields array
	 * @param Pods_Object_Group $group    Pod Group
	 *
	 * @return array Field arrays of args
	 */
	public function setup_fields( $group_id, $fields, $group ) {

		$group_fields = array();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field => $field_data ) {
				$field_id = $group_id . '_' . $field;

				if ( false !== Pods_Form::permission( $field_data[ 'type' ], $field_data[ 'name' ], $field_data, $fields ) ) {
					$group_fields[ $field_id ] = $this->setup_field( $field_id, $field, $field_data, $group );
				}
			}
		}

		return $group_fields;

	}

	/**
	 * Setup field for use with CMB2
	 *
	 * @param string            $field_id   Field Input ID
	 * @param string            $field      Field Name
	 * @param Pods_Object_Field $field_data Pod Field
	 * @param Pods_Object_Group $group      Pod Group
	 *
	 * @return array Field arg array
	 */
	public function setup_field( $field_id, $field, $field_data, $group ) {

		$meta_box_field = array(
			'id'           => $field_id,
			'name'         => $field_data[ 'label' ],
			'desc'         => $field_data[ 'description' ],
			'type'         => self::$cmb2_types[ $field_data[ 'type' ] ],
			'default'      => Pods_Form::default_value( pods_v( $field_id, 'post' ), $field_data[ 'type' ], $field, $field_data, $group[ 'pod' ], 0 ),
			'repeatable'   => (boolean) $field_data[ $field_data[ 'type' ] . '_repeatable' ],
			'sortable'     => (boolean) $field_data[ $field_data[ 'type' ] . '_repeatable' ],
			'show_on_cb'   => array( $this, 'field_show_on' ),
			'before_field' => array( $this, 'render_before_field' ),
			'after_field'  => array( $this, 'render_after_field' ),
			'_pods_field'   => $field_data
		);

		if ( 'loop' == $field_data[ 'type' ] ) {
			$meta_box_field[ 'fields' ] = $this->setup_fields( $field_id, $field_data[ 'fields' ], $group );

			$meta_box_field[ 'options' ] = array(
				'group_title'   => __( 'Entry {#}', 'pods' ), // {#} gets replaced by row number
				'add_button'    => __( 'Add Another Entry', 'pods' ),
				'remove_button' => __( 'Remove Entry', 'pods' ),
				'sortable'      => true
			);
		}

		return $meta_box_field;

	}

	/**
	 * Conditionally displays a group
	 *
	 * @param boolean $show_on Whether group is currently set to show
	 * @param array $cmb2_meta_box CMB2 Meta Box args
	 * @param CMB2 $cmb2 CMB2 instance
	 *
	 * @return bool True if metabox should show
	 */
	public function group_show_on( $show_on, $cmb2_meta_box, $cmb2 ) {

		$group = false;

		if ( isset( $cmb2_meta_box[ '_pods_group' ] ) ) {
			$group = $cmb2_meta_box[ '_pods_group' ];
		}

		if ( ! empty( $group ) ) {
			if ( ! pods_v( 'hidden', $group, false ) ) {
				$show_on = false;
			}
		}

		return apply_filters( 'pods_cmb2_group_show_on', $show_on, $group, $cmb2_meta_box );

	}

	/**
	 * Conditionally displays a field when used as a callback in the 'show_on_cb' field parameter
	 *
	 * @param CMB2_Field $cmb2_field Field object
	 *
	 * @return bool True if metabox should show
	 */
	public function field_show_on( $cmb2_field ) {

		$field = $cmb2_field->args( '_pods_field' );

		$show_on = true;

		if ( ! empty( $field ) && false === Pods_Form::permission( $field[ 'type' ], $field[ 'name' ], $field, array(), $field[ 'pod' ], $cmb2_field->object_id ) ) {
			if ( ! pods_v( 'hidden', $field, false ) ) {
				$show_on = false;
			}
		}

		return apply_filters( 'pods_cmb2_show_on', $show_on, $cmb2_field->args(), $cmb2_field );

	}

	/**
	 * Override field input
	 *
	 * @param array|null $args       Arguments array
	 * @param CMB2_Field $cmb2_field Field object
	 *
	 * @return bool True if metabox should show
	 */
	public function render_before_field( $args, $cmb2_field ) {

		if ( isset( $args[ '_pods_field' ] ) ) {
			ob_start();
		}

	}

	/**
	 * Override field input
	 *
	 * @param array|null $args       Arguments array
	 * @param CMB2_Field $cmb2_field Field object
	 *
	 * @return bool True if metabox should show
	 */
	public function render_after_field( $args, $cmb2_field ) {

		$field = $cmb2_field->args( '_pods_field' );

		if ( ! empty( $field ) ) {
			$void = ob_get_clean();

			self::$in_field = true;

			var_dump( $cmb2_field->value() );

			echo Pods_Form::field( $args[ 'id' ], $cmb2_field->value(), $field[ 'type' ], $field, $field[ 'pod' ], $cmb2_field->object_id );

			self::$in_field = false;
		}

	}

}
