<?php
/**
 * @package  Pods
 * @category Admin
 */

wp_enqueue_script( 'post', false, array(), false, true );

global $pods_i;

$api = pods_api();

$pod = $api->load_pod( array( 'id' => $obj->id ) );

if ( 'taxonomy' == $pod[ 'type' ] && 'none' == $pod[ 'storage' ] && 1 == pods_v( 'enable_extra_fields' ) ) {
	$api->save_pod( array( 'id' => $obj->id, 'storage' => 'table' ) );

	$pod = $api->load_pod( array( 'id' => $obj->id ) );

	unset( $_GET[ 'enable_extra_fields' ] );

	pods_message( __( 'Extra fields were successfully enabled for this Custom Taxonomy.', 'pods' ) );
}

$field_types = Pods_Form::field_types();

$field_types_select = array();

foreach ( $field_types as $type => $field_type_data ) {
	/**
	 * @var $field_type Pods_Field
	 */
	$field_type = Pods_Form::field_loader( $type, $field_type_data[ 'file' ] );

	$field_type_vars = get_class_vars( get_class( $field_type ) );

	if ( ! isset( $field_type_vars[ 'pod_types' ] ) ) {
		$field_type_vars[ 'pod_types' ] = true;
	}

	// Only show supported field types
	if ( true !== $field_type_vars[ 'pod_types' ] ) {
		if ( empty( $field_type_vars[ 'pod_types' ] ) ) {
			continue;
		} elseif ( is_array( $field_type_vars[ 'pod_types' ] ) && ! in_array( pods_v( 'type', $pod ), $field_type_vars[ 'pod_types' ] ) ) {
			continue;
		} elseif ( ! is_array( $field_type_vars[ 'pod_types' ] ) && pods_v( 'type', $pod ) != $field_type_vars[ 'pod_types' ] ) {
			continue;
		}
	}

	if ( ! empty( Pods_Form::$field_group ) ) {
		if ( ! isset( $field_types_select[ Pods_Form::$field_group ] ) ) {
			$field_types_select[ Pods_Form::$field_group ] = array();
		}

		$field_types_select[ Pods_Form::$field_group ][ $type ] = $field_type_data[ 'label' ];
	} else {
		if ( ! isset( $field_types_select[ __( 'Other', 'pods' ) ] ) ) {
			$field_types_select[ __( 'Other', 'pods' ) ] = array();
		}

		$field_types_select[ __( 'Other', 'pods' ) ][ $type ] = $field_type_data[ 'label' ];
	}
}

$field_defaults = array(
	'name'        => 'new_field',
	'label'       => 'New Field',
	'description' => '',
	'type'        => 'text',
	'pick_object' => '',
	'sister_id'   => '',
	'required'    => 0,
	'unique'      => 0,
);

$pick_object = Pods_Form::field_method( 'pick', 'related_objects', true );

$tableless_field_types = Pods_Form::tableless_field_types();
$simple_tableless_objects = Pods_Form::simple_tableless_objects();
$bidirectional_objects = Pods_Form::field_method( 'pick', 'bidirectional_objects' );

$field_defaults = apply_filters( 'pods_field_defaults', apply_filters( 'pods_field_defaults_' . $pod[ 'name' ], $field_defaults, $pod ) );

$pick_table = pods_transient_get( 'pods_tables' );

if ( empty( $pick_table ) ) {
	$pick_table = array(
		'' => __( '-- Select Table --', 'pods' )
	);

	global $wpdb;

	$tables = $wpdb->get_results( "SHOW TABLES", ARRAY_N );

	if ( ! empty( $tables ) ) {
		foreach ( $tables as $table ) {
			$pick_table[ $table[ 0 ] ] = $table[ 0 ];
		}
	}

	pods_transient_set( 'pods_tables', $pick_table );
}

$field_settings = array(
	'field_types_select' => $field_types_select,
	'field_defaults'     => $field_defaults,
	'pick_object'        => $pick_object,
	'pick_table'         => $pick_table,
	'sister_id'          => array( '' => __( 'No Related Fields Found', 'pods' ) )
);

$field_settings = apply_filters( 'pods_field_settings', apply_filters( 'pods_field_settings_' . $pod[ 'name' ], $field_settings, $pod ) );

//$pod[ 'fields' ] = apply_filters( 'pods_fields_edit', apply_filters( 'pods_fields_edit_' . $pod[ 'name' ], $pod[ 'fields' ], $pod ) );

global $wpdb;
$max_length_name = 64;
$max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
$max_length_name -= strlen( $wpdb->prefix . 'pods_' );

$tabs = $pod->admin_tabs();
$tab_options = $pod->admin_options();
?>
<div class="wrap pods-admin">
<div id="icon-pods" class="icon32"><br /></div>
<form action="" method="post" class="pods-submittable pods-nav-tabbed">
<div class="pods-submittable-fields">
	<input type="hidden" name="action" value="pods_admin" /> <input type="hidden" name="method" value="save_pod" />
	<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'pods-save_pod' ) ); ?>" />
	<input type="hidden" name="id" value="<?php echo esc_attr( (int) $pod[ 'id' ] ); ?>" />
	<input type="hidden" name="old_name" value="<?php echo esc_attr( $pod[ 'name' ] ); ?>" />

	<h2>
		<?php
		echo __( 'Edit Pod', 'pods' ) . ':';

		if ( ( in_array( $pod[ 'type' ], array(
						'post_type',
						'taxonomy'
					) ) && ! empty( $pod[ 'object' ] ) ) || in_array( $pod[ 'type' ], array(
					'media',
					'user',
					'comment'
				) )
		) {
			?>
			<em><?php echo esc_html( $pod[ 'name' ] ); ?></em>
		<?php
		} else {
			?>
			<span class="pods-sluggable">
                <span class="pods-slug">
                    <em><?php echo esc_html( $pod[ 'name' ] ); ?></em>
                    <input type="button" class="edit-slug-button button" value="<?php esc_attr_e( 'Edit', 'pods' ); ?>" />
                </span>
                <span class="pods-slug-edit">
                    <?php echo Pods_Form::field( 'name',
	                    pods_v( 'name', $pod ),
	                    'db',
	                    array(
		                    'attributes' => array(
			                    'maxlength' => $max_length_name,
			                    'size'      => 25
		                    ),
		                    'class'      => 'pods-validate pods-validate-required'
	                    ) ); ?>
	                <input type="button" class="save-button button" value="<?php esc_attr_e( 'OK', 'pods' ); ?>" /> <a class="cancel" href="#cancel-edit"><?php _e( 'Cancel', 'pods' ); ?></a>
                </span>
            </span>
		<?php
		}
		?>
	</h2>

	<?php
	if ( ! empty( $tabs ) ) {
		?>

		<h2 class="nav-tab-wrapper pods-nav-tabs">
			<?php
			$default = sanitize_title( pods_v( 'tab', 'get', 'manage-groups', true ) );

			if ( ! isset( $tabs[ $default ] ) ) {
				$tab_keys = array_keys( $tabs );

				$default = current( $tab_keys );
			}

			foreach ( $tabs as $tab => $label ) {
				if ( ! in_array( $tab, array(
							'manage-groups',
							'labels',
							'extra-fields'
						) ) && ( ! isset( $tab_options[ $tab ] ) || empty( $tab_options[ $tab ] ) )
				) {
					continue;
				}

				$class = '';

				$tab = sanitize_title( $tab );

				if ( $tab == $default ) {
					$class = ' nav-tab-active';
				}
				?>
				<a href="#pods-<?php echo esc_attr( $tab ); ?>" class="nav-tab<?php echo esc_attr( $class ); ?> pods-nav-tab-link">
					<?php echo $label; ?>
				</a>
			<?php
			}
			?>
		</h2>
	<?php
	}
	?>
</div>

<?php
if ( isset( $_GET[ 'do' ] ) ) {
	$action = __( 'saved', 'pods' );

	if ( 'create' == pods_v( 'do', 'get', 'save' ) ) {
		$action = __( 'created', 'pods' );
	} elseif ( 'duplicate' == pods_v( 'do', 'get', 'save' ) ) {
		$action = __( 'duplicated', 'pods' );
	}

	$message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );

	echo $obj->message( $message );
}
?>

<div id="poststuff">
<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />
<!-- /inner-sidebar -->
<div id="post-body" class="meta-box-holder columns-2">
<div id="post-body-content" class="pods-nav-tab-group">

<?php
if ( isset( $tabs[ 'manage-groups' ] ) ) {
	?>
	<div id="pods-manage-groups" class="pods-nav-tab">
		<?php
		/**
		 * @var $pods_admin Pods_Admin
		 */
		$pods_admin->admin_setup_groups();
		?>
	</div>
<?php
}

$pods_tab_form = true;

if ( isset( $tabs[ 'labels' ] ) && ! empty( $tab_options[ 'labels' ] ) ) {
	?>
	<div id="pods-labels" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
		<?php
		$fields = $tab_options[ 'labels' ];
		$field_options = Pods_Form::fields_setup( $fields );
		$field = $pod;

		include PODS_DIR . 'ui/admin/field-option.php';
		?>
	</div>
<?php
}

if ( isset( $tabs[ 'advanced' ] ) ) { //&& !empty( $tab_options[ 'advanced' ] ) ) {
	?>
	<div id="pods-advanced" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
	<?php
	if ( 'post_type' == pods_v( 'type', $pod ) && strlen( pods_v( 'object', $pod ) ) < 1 ) {
		$fields = $tab_options[ 'advanced' ];
		$field_options = Pods_Form::fields_setup( $fields );
		$field = $pod;

		include PODS_DIR . 'ui/admin/field-option.php';
		?>
		<div class="pods-field-option-group">
			<p class="pods-field-option-group-label">
				<?php _e( 'Supports', 'pods' ); ?>
			</p>

			<div class="pods-pick-values pods-pick-checkbox">
				<ul>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_title', pods_v( 'supports_title', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Title', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_editor', pods_v( 'supports_editor', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Editor', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_author', pods_v( 'supports_author', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Author', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_thumbnail', pods_v( 'supports_thumbnail', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Featured Image', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_excerpt', pods_v( 'supports_excerpt', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Excerpt', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_trackbacks', pods_v( 'supports_trackbacks', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Trackbacks', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_custom_fields', pods_v( 'supports_custom_fields', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Custom Fields', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_comments', pods_v( 'supports_comments', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Comments', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_revisions', pods_v( 'supports_revisions', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Revisions', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_page_attributes', pods_v( 'supports_page_attributes', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Page Attributes', 'pods' ) ) ); ?>
						</div>
					</li>
					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'supports_post_formats', pods_v( 'supports_post_formats', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Post Formats', 'pods' ) ) ); ?>
						</div>
					</li>

					<?php if ( function_exists( 'genesis' ) ) { ?>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'supports_genesis_seo', pods_v( 'supports_genesis_seo', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: SEO', 'pods' ) ) ); ?>
							</div>
						</li>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'supports_genesis_layouts', pods_v( 'supports_genesis_layouts', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: Layouts', 'pods' ) ) ); ?>
							</div>
						</li>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'supports_genesis_simple_sidebars', pods_v( 'supports_genesis_simple_sidebars', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: Simple Sidebars', 'pods' ) ) ); ?>
							</div>
						</li>
					<?php } ?>

					<?php if ( defined( 'YARPP_VERSION' ) ) { ?>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'supports_yarpp_support', pods_v( 'supports_yarpp_support', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'YARPP Support', 'pods' ) ) ); ?>
							</div>
						</li>
					<?php } ?>

					<?php if ( class_exists( 'Jetpack' ) ) { ?>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'supports_jetpack_publicize', pods_v( 'supports_jetpack_publicize', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Jetpack Publicize Support', 'pods' ) ) ); ?>
							</div>
						</li>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'supports_jetpack_markdown', pods_v( 'supports_jetpack_markdown', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Jetpack Markdown Support', 'pods' ) ) ); ?>
							</div>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'supports_custom', __( 'Advanced Supports', 'pods' ), __( 'Comma-separated list of custom "supports" values to pass to register_post_type.', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'supports_custom', pods_v( 'supports_custom', $pod, '' ), 'text' ); ?>
		</div>
		<div class="pods-field-option-group">
			<p class="pods-field-option-group-label">
				<?php _e( 'Built-in Taxonomies', 'pods' ); ?>
			</p>

			<div class="pods-pick-values pods-pick-checkbox">
				<ul>
					<?php
					foreach ( (array) $field_settings[ 'pick_object' ][ __( 'Taxonomies', 'pods' ) ] as $taxonomy => $label ) {
						$taxonomy = pods_str_replace( 'taxonomy-', '', $taxonomy, 1 );
						?>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'built_in_taxonomies_' . $taxonomy, pods_v( 'built_in_taxonomies_' . $taxonomy, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label . ' <small>(' . $taxonomy . ')</small>' ) ); ?>
							</div>
						</li>
					<?php
					}
					?>
				</ul>
			</div>
		</div>
	<?php
	} elseif ( 'taxonomy' == pods_v( 'type', $pod ) && strlen( pods_v( 'object', $pod ) ) < 1 ) {
		?>
		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'public', __( 'Public', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'public', pods_v( 'public', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
		</div>
		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'hierarchical', __( 'Hierarchical', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'hierarchical', pods_v( 'hierarchical', $pod, false ), 'boolean', array(
					'dependency'        => true,
					'boolean_yes_label' => ''
				) ); ?>
		</div>
		<div class="pods-field-option-container pods-depends-on pods-depends-on-hierarchical">
			<div class="pods-field-option">
				<?php echo Pods_Form::label( 'label_parent_item_colon', __( '<strong>Label: </strong> Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
				<?php echo Pods_Form::field( 'label_parent_item_colon', pods_v( 'label_parent_item_colon', $pod ), 'text' ); ?>
			</div>
			<div class="pods-field-option">
				<?php echo Pods_Form::label( 'label_parent', __( '<strong>Label: </strong> Parent', 'pods' ), __( 'help', 'pods' ) ); ?>
				<?php echo Pods_Form::field( 'label_parent', pods_v( 'label_parent', $pod ), 'text' ); ?>
			</div>
		</div>
		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'rewrite', __( 'Rewrite', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'rewrite', pods_v( 'rewrite', $pod, true ), 'boolean', array(
					'dependency'        => true,
					'boolean_yes_label' => ''
				) ); ?>
		</div>
		<div class="pods-field-option-container pods-depends-on pods-depends-on-rewrite">
			<div class="pods-field-option">
				<?php echo Pods_Form::label( 'rewrite_custom_slug', __( 'Custom Rewrite Slug', 'pods' ), __( 'help', 'pods' ) ); ?>
				<?php echo Pods_Form::field( 'rewrite_custom_slug', pods_v( 'rewrite_custom_slug', $pod ), 'text' ); ?>
			</div>
			<div class="pods-field-option">
				<?php echo Pods_Form::label( 'rewrite_with_front', __( 'Allow Front Prepend', 'pods' ), __( 'Allows permalinks to be prepended with front base (example: if your permalink structure is /blog/, then your links will be: Checked->/news/, Unchecked->/blog/news/)', 'pods' ) ); ?>
				<?php echo Pods_Form::field( 'rewrite_with_front', pods_v( 'rewrite_with_front', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
			</div>
			<div class="pods-field-option">
				<?php echo Pods_Form::label( 'rewrite_hierarchical', __( 'Hierarchical Permalinks', 'pods' ), __( 'help', 'pods' ) ); ?>
				<?php echo Pods_Form::field( 'rewrite_hierarchical', pods_v( 'rewrite_hierarchical', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
			</div>
		</div>
		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'query_var', __( 'Query Var', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'query_var', pods_v( 'query_var', $pod ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
		</div>
		<div class="pods-field-option-container pods-depends-on pods-depends-on-query-var">
			<div class="pods-field-option">
				<?php echo Pods_Form::label( 'query_var_string', __( 'Custom Query Var Name', 'pods' ), __( 'help', 'pods' ) ); ?>
				<?php echo Pods_Form::field( 'query_var_string', pods_v( 'query_var_string', $pod ), 'text' ); ?>
			</div>
		</div>
		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'sort', __( 'Remember order saved on Post Types', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'sort', pods_v( 'sort', $pod ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
		</div>

		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'update_count_callback', __( 'Function to call when updating counts', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'update_count_callback', pods_v( 'update_count_callback', $pod ), 'text' ); ?>
		</div>
		<div class="pods-field-option-group">
			<p class="pods-field-option-group-label">
				<?php _e( 'Associated Post Types', 'pods' ); ?>
			</p>

			<div class="pods-pick-values pods-pick-checkbox">
				<ul>
					<?php
					foreach ( (array) $field_settings[ 'pick_object' ][ __( 'Post Types', 'pods' ) ] as $post_type => $label ) {
						$post_type = pods_str_replace( 'post_type-', '', $post_type, 1 );
						$label = str_replace( array( '(', ')' ), array( '<small>(', ')</small>' ), $label );
						?>
						<li>
							<div class="pods-field pods-boolean">
								<?php echo Pods_Form::field( 'built_in_post_types_' . $post_type, pods_v( 'built_in_post_types_' . $post_type, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label ) ); ?>
							</div>
						</li>
					<?php
					}
					?>

					<li>
						<div class="pods-field pods-boolean">
							<?php echo Pods_Form::field( 'built_in_post_types_attachment', pods_v( 'built_in_post_types_attachment', $pod, false ), 'boolean', array( 'boolean_yes_label' => 'Media <small>(attachment)</small>' ) ); ?>
						</div>
					</li>
				</ul>
			</div>
		</div>
	<?php
	} elseif ( 'pod' == pods_v( 'type', $pod ) ) {
		?>
		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'detail_url', __( 'Detail Page URL', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'detail_url', pods_v( 'detail_url', $pod ), 'text' ); ?>
		</div>

		<?php
		$index_fields = array( 'id' => 'ID' );

		foreach ( $pod[ 'fields' ] as $field ) {
			if ( ! in_array( $field[ 'type' ], $tableless_field_types ) ) {
				$index_fields[ $field[ 'name' ] ] = $field[ 'label' ];
			}
		}
		?>

		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'pod_index', __( 'Title Field', 'pods' ), __( 'If you delete the "name" field, we need to specify the field to use as your primary title field. This field will serve as an index of your content. Most commonly this field represents the name of a person, place, thing, or a summary field.', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'pod_index', pods_v( 'pod_index', $pod, 'name' ), 'pick', array( 'data' => $index_fields ) ); ?>
		</div>

		<div class="pods-field-option">
			<?php echo Pods_Form::label( 'hierarchical', __( 'Hierarchical', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'hierarchical', (int) pods_v( 'hierarchical', $pod, 0 ), 'boolean', array(
					'dependency'        => true,
					'boolean_yes_label' => ''
				) ); ?>
		</div>

		<?php
		$hierarchical_fields = array();

		foreach ( $pod[ 'fields' ] as $field ) {
			if ( 'pick' == $field[ 'type' ] && 'pod' == pods_v( 'pick_object', $field ) && $pod[ 'name' ] == pods_v( 'pick_val', $field ) && 'single' == pods_v( 'pick_format_type', $field ) ) {
				$hierarchical_fields[ $field[ 'name' ] ] = $field[ 'label' ];
			}
		}

		if ( empty( $hierarchical_fields ) ) {
			$hierarchical_fields = array( '' => __( 'No Hierarchical Fields found', 'pods' ) );
		}
		?>

		<div class="pods-field-option pods-depends-on pods-depends-on-hierarchical">
			<?php echo Pods_Form::label( 'pod_parent', __( 'Hierarchical Field', 'pods' ), __( 'help', 'pods' ) ); ?>
			<?php echo Pods_Form::field( 'pod_parent', pods_v( 'pod_parent', $pod, 'name' ), 'pick', array( 'data' => $hierarchical_fields ) ); ?>
		</div>

		<?php
		if ( class_exists( 'Pods_Helpers' ) ) {
			?>

			<div class="pods-field-option">
				<?php
				$pre_save_helpers = array( '' => '-- Select --' );

				$helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'pre_save' ) ) );

				foreach ( $helpers as $helper ) {
					$pre_save_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
				}

				echo Pods_Form::label( 'pre_save_helpers', __( 'Pre-Save Helper(s)', 'pods' ), __( 'help', 'pods' ) );
				echo Pods_Form::field( 'pre_save_helpers', pods_v( 'pre_save_helpers', $pod ), 'pick', array( 'data' => $pre_save_helpers ) );
				?>
			</div>
			<div class="pods-field-option">
				<?php
				$post_save_helpers = array( '' => '-- Select --' );

				$helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'post_save' ) ) );

				foreach ( $helpers as $helper ) {
					$post_save_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
				}

				echo Pods_Form::label( 'post_save_helpers', __( 'Post-Save Helper(s)', 'pods' ), __( 'help', 'pods' ) );
				echo Pods_Form::field( 'post_save_helpers', pods_v( 'post_save_helpers', $pod ), 'pick', array( 'data' => $post_save_helpers ) );
				?>
			</div>
			<div class="pods-field-option">
				<?php
				$pre_delete_helpers = array( '' => '-- Select --' );

				$helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'pre_delete' ) ) );

				foreach ( $helpers as $helper ) {
					$pre_delete_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
				}

				echo Pods_Form::label( 'pre_delete_helpers', __( 'Pre-Delete Helper(s)', 'pods' ), __( 'help', 'pods' ) );
				echo Pods_Form::field( 'pre_delete_helpers', pods_v( 'pre_delete_helpers', $pod ), 'pick', array( 'data' => $pre_delete_helpers ) );
				?>
			</div>
			<div class="pods-field-option">
				<?php
				$post_delete_helpers = array( '' => '-- Select --' );

				$helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'post_delete' ) ) );

				foreach ( $helpers as $helper ) {
					$post_delete_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
				}

				echo Pods_Form::label( 'post_delete_helpers', __( 'Post-Delete Helper(s)', 'pods' ), __( 'help', 'pods' ) );
				echo Pods_Form::field( 'post_delete_helpers', pods_v( 'post_delete_helpers', $pod ), 'pick', array( 'data' => $post_delete_helpers ) );
				?>
			</div>
		<?php
		}
	}
	?>
	</div>
<?php
}

foreach ( $tabs as $tab => $tab_label ) {
	$tab = sanitize_title( $tab );

	if ( in_array( $tab, array(
				'manage-groups',
				'labels',
				'extra-fields'
			) ) || ! isset( $tab_options[ $tab ] ) || empty( $tab_options[ $tab ] ) || isset( $tab_options[ $tab ][ 'temporary' ] )
	) {
		continue;
	}
	?>
	<div id="pods-<?php echo esc_attr( $tab ); ?>" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
		<?php
		$fields = $tab_options[ $tab ];
		$field_options = Pods_Form::fields_setup( $fields );
		$field = $pod;

		include PODS_DIR . 'ui/admin/field-option.php';
		?>
	</div>
<?php
}

if ( isset( $tabs[ 'extra-fields' ] ) ) {
	?>
	<div id="pods-extra-fields" class="pods-nav-tab">
		<p><?php _e( 'Taxonomies do not support extra fields natively, but Pods can add this feature for you easily. Table based storage will operate in a way where each field you create for your content type becomes a field in a table.', 'pods' ); ?></p>

		<p><?php echo sprintf( __( 'Enabling extra fields for this taxonomy will add a custom table into your database as <em>%s</em>.', 'pods' ), $wpdb->prefix . 'pods_' . pods_v( 'name', $pod ) ); ?></p>

		<p>
			<a href="http://pods.io/docs/comparisons/compare-storage-types/" target="_blank"><?php _e( 'Find out more', 'pods' ); ?> &raquo;</a>
		</p>

		<p class="submit">
			<a href="<?php echo esc_url( pods_query_arg( array( 'enable_extra_fields' => 1 ) ) ); ?>" class="button-primary"><?php _e( 'Enable Extra Fields', 'pods' ); ?></a>
		</p>
	</div>
<?php
}
?>
</div>
<!-- /#post-body-content -->

<div id="postbox-container-1" class="postbox-container pods_floatmenu">
	<div id="side-info-field" class="inner-sidebar">
		<div id="side-sortables">
			<div id="submitdiv" class="postbox pods-no-toggle">
				<h3><span>Manage <small>(<a href="<?php echo esc_url( pods_query_arg( array(
					'action' . $obj->num => 'manage',
					'id' . $obj->num     => ''
					) ) ); ?>">&laquo; <?php _e( 'Back to Manage', 'pods' ); ?></a>)
				</small></span></h3>
				<div class="inside">
					<div class="submitbox" id="submitpost">
						<div id="minor-publishing">
							<div id="misc-publishing-actions">
								<div class="misc-pub-section">
									<span><?php _e( 'Name', 'pods' ); ?>: <b><?php echo $pod[ 'name' ]; ?></b></span>
								</div>

								<div class="misc-pub-section">
									<span><?php _e( 'ID', 'pods' ); ?>: <b><?php echo $pod[ 'id' ]; ?></b></span>
								</div>

								<?php
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

								$type = $pod[ 'type' ];

								if ( isset( $types[ $type ] ) ) {
									if ( in_array( $type, array( 'post_type', 'taxonomy' ) ) ) {
										if ( empty( $pod[ 'object' ] ) ) {
											if ( 'post_type' == $type ) {
												$type = 'cpt';
											} else {
												$type = 'ct';
											}
										}
									}

									$type = $types[ $type ];
								}
								?>
								<div class="misc-pub-section">
									<span><?php _e( 'Type', 'pods' ); ?>: <b><?php echo $type; ?></b></span>
								</div>

								<div class="misc-pub-section">
									<span><?php _e( 'Storage Type', 'pods' ); ?>: <b><?php echo ucwords( $pod[ 'storage' ] ); ?></b></span>
								</div>
							</div>
						</div>
						<!-- /#minor-publishing -->

						<div id="major-publishing-actions">
							<div id="delete-action">
								<a href="<?php echo esc_url( pods_query_arg( array( 'action' . $obj->num => 'delete', '_wpnonce' => wp_create_nonce( 'pods-ui-action-delete' ) ) ) ); ?>" class="submitdelete deletion pods-confirm" data-confirm="<?php esc_attr_e( 'Are you sure you want to delete this Pod? All fields and data will be removed.', 'pods' ); ?>"> <?php _e( 'Delete', 'pods' ); ?> Pod </a>
							</div>
							<div id="publishing-action">
								<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
								<button class="button-primary" type="submit">Save Pod</button>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
			<!-- /#submitdiv -->
		</div>
	</div>
</div>
</div>
<!-- /#post-body -->
</div>
<!-- /poststuff -->
</form>
</div>
<script type="text/javascript">
	<?php
	$pods_field_types = array();

	foreach ( $field_types as $field_type => $field_type_data ) {
		$pods_field_types[] = "'" . esc_js( $field_type ) . "' : '" . esc_js( $field_type_data[ 'label' ] ) . "'";
	}

	$pods_pick_objects = array();

	$pick_object_singular = array(
		__( 'Pods', 'pods' ) => __( 'Pod', 'pods' ),
		__( 'Post Types', 'pods' ) => __( 'Post Type', 'pods' ),
		__( 'Taxonomies', 'pods' ) => __( 'Taxonomy', 'pods' )
	);

	foreach ( $field_settings[ 'pick_object' ] as $object => $object_label ) {
		if ( is_array( $object_label ) ) {
			if ( isset( $pick_object_singular[ $object ] ) )
				{$object = ' <small>(' . esc_js( $pick_object_singular[ $object ] ) . ')</small>';
}
			else
				{$object = '';
}

			foreach ( $object_label as $sub_object => $sub_object_label ) {
				$pods_pick_objects[] = "'" . esc_js( $sub_object ) . "' : '" . esc_js( $sub_object_label ) . $object . "'";
			}
		}
		elseif ( '-- Select --' != $object_label ) {
			$pods_pick_objects[] = "'" . esc_js( $object ) . "' : '" . esc_js( $object_label ) . "'";
		}
	}
	?>
	var pods_field_types = {
		<?php echo implode( ",\n        ", $pods_field_types ); ?>
	};
	var pods_pick_objects = {
		<?php echo implode( ",\n        ", $pods_pick_objects ); ?>
	};

	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'sluggable' );
		$( document ).Pods( 'sortable' );
		$( document ).Pods( 'collapsible', $( 'tbody.pods-manage-list tr.flexible-row div.pods-manage-field' ) );
		$( document ).Pods( 'toggled' );
		$( document ).Pods( 'tabbed' );
		$( document ).Pods( 'nav_tabbed' );
		$( document ).Pods( 'dependency' );
		$( document ).Pods( 'flexible', $( 'tbody.pods-manage-list tr.flexible-row' ) );
		$( document ).Pods( 'confirm' );
		$( document ).Pods( 'exit_confirm' );
	} );

	var pods_admin_submit_callback = function ( id ) {
		id = parseInt( id );
		var thank_you = '<?php echo pods_slash( pods_query_arg( array( 'do' => 'save' ) ) ); ?>';

		document.location = thank_you.replace( 'X_ID_X', id );
	}

	var pods_sister_field_going = {};

	var pods_sister_field = function ( $el ) {
		var id = $el.closest( 'tr.pods-manage-row' ).data( 'row' );

		if ( 'undefined' != typeof pods_sister_field_going[id + '_' + $el.prop( 'id' )] && true == pods_sister_field_going[id + '_' + $el.prop( 'id' )] ) {
			return;
		}

		pods_sister_field_going[id + '_' + $el.prop( 'id' )] = true;

		var default_select = '<?php echo pods_slash( str_replace( array( "\n", "\r" ), ' ', Pods_Form::field( 'field_data[--1][sister_id]', '', 'pick', array( 'data' => pods_v( 'sister_id', $field_settings ) ) ) ) ); ?>';
		default_select = default_select.replace( /\-\-1/g, id );

		var related_pod_name = jQuery( '#pods-form-ui-field-data-' + id + '-pick-object' ).val();

		if ( 0 != related_pod_name.indexOf( 'pod-' ) && 0 != related_pod_name.indexOf( 'post_type-' ) && 0 != related_pod_name.indexOf( 'taxonomy-' ) && 0 != related_pod_name.indexOf( 'user' ) && 0 != related_pod_name.indexOf( 'media' ) && 0 != related_pod_name.indexOf( 'comment' ) ) {
			pods_sister_field_going[id + '_' + $el.prop( 'id' )] = false;

			return;
		}

		var selected_value = jQuery( '#pods-form-ui-field-data-' + id + '-sister-id' ).val();

		var select_container = default_select.match( /<select[^<]*>/g );

		$el.find( '.pods-sister-field' ).html( select_container + '<option value=""><?php esc_attr_e( 'Loading available fields..', 'pods' ); ?></option></select>' );

		postdata = {
			action : 'pods_admin',
			method : 'load_sister_fields',
			_wpnonce : '<?php echo esc_js( wp_create_nonce( 'pods-load_sister_fields' ) ); ?>',
			pod : '<?php echo esc_js( pods_v( 'name', $pod ) ); ?>',
			related_pod : related_pod_name
		};

		jQuery.ajax( {
			type : 'POST',
			dataType : 'html',
			url : ajaxurl + '?pods_ajax=1',
			cache : false,
			data : postdata,
			success : function ( d ) {
				if ( -1 == d.indexOf( '<e>' ) && -1 == d.indexOf( '</e>' ) && -1 != d && '[]' != d ) {
					var json = d.match( /{.*}$/ );

					if ( null !== json && 0 < json.length ) {
						json = jQuery.parseJSON( json[0] );
					}
					else {
						json = {};
					}

					var select_container = default_select.match( /<select[^<]*>/g );

					if ( 'object' != typeof json || ! jQuery.isEmptyObject( json ) ) {
						if ( 'object' != typeof json ) {
							if ( window.console ) {
								console.log( d );
							}
							if ( window.console ) {
								console.log( json );
							}

							select_container += '<option value=""><?php esc_attr_e( 'There was a server error with your AJAX request.', 'pods' ); ?></option>';
						}

						select_container += '<option value=""><?php esc_attr_e( '-- Select Related Field --', 'pods' ); ?></option>';

						for ( var field_id in json ) {
							var field_name = json[field_id];

							select_container += '<option value="' + field_id + '">' + field_name + '</option>';
						}

						select_container += '</select>';

						$el.find( '.pods-sister-field' ).html( select_container );

						jQuery( '#pods-form-ui-field-data-' + id + '-sister-id' ).val( selected_value );
					} else {
					// None found
					$el.find( '.pods-sister-field' ).html( default_select );
					}
				}
				else {
					// None found
					$el.find( '.pods-sister-field' ).html( default_select );
				}

				pods_sister_field_going[id + '_' + $el.prop( 'id' )] = false;
			},
			error : function () {
				// None found
				$el.find( '.pods-sister-field' ).html( default_select );

				pods_sister_field_going[id + '_' + $el.prop( 'id' )] = false;
			}
		} );
	}
</script>
