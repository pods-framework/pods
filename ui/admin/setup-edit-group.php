<?php
/**
 * @package  Pods
 * @category Admin
 */

wp_enqueue_script( 'post', false, array(), false, true );

global $pods_i;

$api = pods_api();

$pod = pods_object_pod( null, Pods_Admin::$admin_row[ 'id' ] );

if ( ! $pod->is_valid() ) {
	return pods_error( __( 'Pod not found', 'pods' ) );
}

if ( ! empty( $obj->id ) ) {
	$group = pods_object_group( null, $obj->id, false, $pod[ 'id' ] );

	if ( ! $group->is_valid() ) {
		return pods_error( __( 'Field Group not found', 'pods' ) );
	} elseif ( (int) $pod[ 'id' ] !== (int) $group[ 'parent_id' ] ) {
		pods_redirect( add_query_arg( array( 'id' => (int) $group[ 'parent_id' ] ) ) );
	}
} else {
	$group = pods_object_group( array( 'parent_id' => $pod[ 'id' ] ) );
}

$group_label = $group[ 'label' ];

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

$tabs = $group->admin_tabs();
$tab_options = $group->admin_options();

$field_tabs = $group->admin_field_tabs();
$field_tab_options = $group->admin_field_options();

$no_additional = array();

foreach ( $field_tab_options[ 'additional-field' ] as $field_type => $field_type_fields ) {
	if ( empty( $field_type_fields ) ) {
		$no_additional[] = $field_type;
	}
}
?>
<div class="wrap pods-admin">
<div id="icon-pods" class="icon32"><br /></div>
<form action="" method="post" class="pods-submittable pods-nav-tabbed">
<div class="pods-submittable-fields">
	<input type="hidden" name="action" value="pods_admin" />
	<input type="hidden" name="method" value="save_pod_group" />
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'pods-save_pod_group' ); ?>" />
	<input type="hidden" name="id" value="<?php echo esc_attr( (int) $group[ 'id' ] ); ?>" />
	<input type="hidden" name="pod_id" value="<?php echo esc_attr( (int) $pod[ 'id' ] ); ?>" />
	<input type="hidden" name="old_name" value="<?php echo esc_attr( $group[ 'name' ] ); ?>" />

	<h2>
		<?php
		if ( ! $group->is_custom() ) {
			_e( 'Edit Field Group in Pod', 'pods' );
		} else {
			_e( 'Add New Field Group to Pod', 'pods' );
		}

		echo ':';
		?>
		<em><?php echo esc_html( $pod[ 'name' ] ); ?></em> <a href="<?php echo esc_url( remove_query_arg( array(
					'action_group',
					'do_group',
					'id_group'
				) ) ); ?>" class="add-new-h2">&laquo; <?php _e( 'Back to Edit Pod', 'pods' ); ?></a>
	</h2>
</div>

<?php
if ( isset( $_GET[ 'do' . $obj->num ] ) ) {
	$action = __( 'saved', 'pods' );

	if ( 'create' == pods_v( 'do' . $obj->num, 'get', 'save' ) ) {
		$action = __( 'created', 'pods' );
	} elseif ( 'duplicate' == pods_v( 'do' . $obj->num, 'get', 'save' ) ) {
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

	<div id="titlediv" class="pods-submittable-fields">
		<div id="titlewrap">
			<label class="hide-if-no-js screen-reader-text" id="title-prompt-text" for="title"><?php _e( 'Enter Group title', 'pods' ); ?></label>
			<input type="text" name="label" data-name-clean="label" id="title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $group_label ) ); ?>" class="pods-form-ui-field-name-pods-field-label" autocomplete="off" />
		</div>
		<!-- /#titlewrap -->

		<div class="inside">
			<div id="edit-slug-box">
			</div>
			<!-- /#edit-slug-box -->
		</div>
		<!-- /.inside -->
	</div>
	<!-- /#titlediv -->

	<?php
	if ( ! empty( $tabs ) ) {
		?>
		<h2 class="nav-tab-wrapper pods-nav-tabs">
			<?php
			$default = sanitize_title( pods_v( 'tab', 'get', 'manage-fields', true ) );

			if ( ! isset( $tabs[ $default ] ) ) {
				$tab_keys = array_keys( $tabs );

				$default = current( $tab_keys );
			}

			foreach ( $tabs as $tab => $label ) {
				if ( ! in_array( $tab, array( 'manage-fields' ) ) && ( ! isset( $tab_options[ $tab ] ) || empty( $tab_options[ $tab ] ) ) ) {
					continue;
				}

				$class = '';

				$tab = sanitize_title( $tab );

				if ( $tab == $default ) {
					$class = ' nav-tab-active';
				}
				?>
				<a href="#pods-<?php echo esc_url( $tab ); ?>" class="nav-tab<?php echo esc_attr( $class ); ?> pods-nav-tab-link">
					<?php echo $label; ?>
				</a>
			<?php
			}
			?>
		</h2>
	<?php
	}
	?>

	<?php
	if ( isset( $tabs[ 'manage-fields' ] ) ) {
		?>
		<div id="pods-manage-fields" class="pods-nav-tab">
			<p class="pods-manage-row-add pods-float-right">
				<a href="#add-field" class="button-primary"><?php _e( 'Add Field', 'pods' ); ?></a>
			</p>

			<?php
			if ( ! empty( $tabs ) ) {
				echo '<h2>' . __( 'Manage Fields', 'pods' ) . '</h2>';
			}

			do_action( 'pods_admin_ui_setup_edit_groups_fields', $pod, $obj );
			?>

			<!-- pods table -->
			<table class="widefat fixed pages" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column field-cb check-column">
							<span>&nbsp;</span>
						</th>
						<th scope="col" id="label" class="manage-column field-label">
							<span>Label<?php pods_help( __( "<h6>Label</h6>The label is the descriptive name to identify the Pod field.", 'pods' ) ); ?></span>
						</th>
						<th scope="col" id="machine-name" class="manage-column field-machine-name">
							<span>Name<?php pods_help( __( "<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically.", 'pods' ) ); ?></span>
						</th>
						<th scope="col" id="field-type" class="manage-column field-field-type">
							<span>Field Type<?php pods_help( __( "<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc.", 'pods' ) ); ?></span>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" class="manage-column field-cb check-column">
							<span>&nbsp;</span>
						</th>
						<th scope="col" class="manage-column field-label">
							<span>Label<?php pods_help( __( "<h6>Label</h6>The label is the descriptive name to identify the Pod field.", 'pods' ) ); ?></span>
						</th>
						<th scope="col" class="manage-column field-machine-name">
							<span>Name<?php pods_help( __( "<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically.", 'pods' ) ); ?></span>
						</th>
						<th scope="col" class="manage-column field-field-type">
							<span>Field Type<?php pods_help( __( "<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc.", 'pods' ) ); ?></span>
						</th>
					</tr>
				</tfoot>
				<tbody class="pods-manage-list">
					<?php
					// Empty Row for Flexible functionality
					$pods_i = '--1';

					$field = array(
						'id'    => '__1',
						'name'  => '',
						'label' => '',
						'type'  => 'text'
					);

					include PODS_DIR . 'ui/admin/setup-edit-field-fluid.php';

					$pods_i = 1;

					$fields = $group[ 'fields' ];

					foreach ( $fields as $field ) {
						include PODS_DIR . 'ui/admin/setup-edit-field.php';

						$pods_i ++;
					}
					?>
					<tr class="no-items<?php echo( 1 < $pods_i ? ' hidden' : '' ); ?>">
						<td class="colspanchange" colspan="4">No fields have been added yet</td>
					</tr>
				</tbody>
			</table>
			<!-- /pods table -->
			<p class="pods-manage-row-add">
				<a href="#add-field" class="button-primary"><?php _e( 'Add Field', 'pods' ); ?></a>
			</p>
		</div>
	<?php
	}

	$pods_tab_form = true;

	foreach ( $tabs as $tab => $tab_label ) {
		$tab = sanitize_title( $tab );

		if ( in_array( $tab, array( 'manage-fields' ) ) || ! isset( $tab_options[ $tab ] ) || empty( $tab_options[ $tab ] ) ) {
			continue;
		}
		?>
		<div id="pods-<?php echo esc_attr( $tab ); ?>" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
			<?php
			$fields = $tab_options[ $tab ];
			$field_options = Pods_Form::fields_setup( $fields );
			$field = $group;

			include PODS_DIR . 'ui/admin/field-option.php';
			?>
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
				<h3><span>Manage <small>(<a href="<?php echo esc_attr( remove_query_arg( array(
										'action_group',
										'do_group',
										'id_group'
									) ) ); ?>">&laquo; <?php _e( 'Back to Edit Pod', 'pods' ); ?></a>)
						</small>
						</small></span></h3>
				<div class="inside">
					<div class="submitbox" id="submitpost">
						<div id="minor-publishing">
							<div id="misc-publishing-actions">
								<div class="misc-pub-section">
									<span><?php _e( 'Name', 'pods' ); ?>: <b><?php echo $group[ 'name' ]; ?></b></span>
								</div>

								<div class="misc-pub-section">
									<span><?php _e( 'ID', 'pods' ); ?>: <b><?php echo $group[ 'id' ]; ?></b></span>
								</div>
								<div class="misc-pub-section">
									<span><?php _e( 'Pod Name', 'pods' ); ?>: <b><?php echo $pod[ 'name' ]; ?></b></span>
								</div>

								<div class="misc-pub-section">
									<span><?php _e( 'Pod ID', 'pods' ); ?>: <b><?php echo $pod[ 'id' ]; ?></b></span>
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
									<span><?php _e( 'Pod Type', 'pods' ); ?>: <b><?php echo $type; ?></b></span>
								</div>

								<div class="misc-pub-section">
									<span><?php _e( 'Pod Storage Type', 'pods' ); ?>: <b><?php echo ucwords( $pod[ 'storage' ] ); ?></b></span>
								</div>
							</div>
						</div>
						<!-- /#minor-publishing -->

						<div id="major-publishing-actions">
							<div id="delete-action">
								<a href="<?php echo pods_var_update( array( 'action' . $obj->num => 'delete' ) ); ?>" class="submitdelete deletion pods-confirm" data-confirm="<?php esc_attr_e( 'Are you sure you want to delete this Pod? All fields and data will be removed.', 'pods' ); ?>"> Delete Pod </a>
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
		elseif ( '-- Select --' != $object_label )
			{$pods_pick_objects[] = "'" . esc_js( $object ) . "' : '" . esc_js( $object_label ) . "'";
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

		var thank_you = '<?php echo pods_slash( add_query_arg( array( 'action_group' => 'edit', 'id_group' => 'X_ID_X', 'do_group' => 'save' ) ) ); ?>';

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
			_wpnonce : '<?php echo wp_create_nonce( 'pods-load_sister_fields' ); ?>',
			pod : '<?php echo pods_v( 'name', $pod ); ?>',
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

					if ( 'object' != typeof json || jQuery.isEmptyObject( json ) ) {
						if ( window.console ) {
							console.log( d );
						}
						if ( window.console ) {
							console.log( json );
						}

						select_container += '<option value=""><?php esc_attr_e( 'There was a server error with your AJAX request.', 'pods' ); ?></option>';
					}
					else {
						select_container += '<option value=""><?php esc_attr_e( '-- Select Related Field --', 'pods' ); ?></option>';

						for ( var field_id in json ) {
							var field_name = json[field_id];

							select_container += '<option value="' + field_id + '">' + field_name + '</option>';
						}
					}

					select_container += '</select>';

					$el.find( '.pods-sister-field' ).html( select_container );

					jQuery( '#pods-form-ui-field-data-' + id + '-sister-id' ).val( selected_value );

					pods_sister_field_going[id + '_' + $el.prop( 'id' )] = false;
				}
				else {
					// None found
					$el.find( '.pods-sister-field' ).html( default_select );

					pods_sister_field_going[id + '_' + $el.prop( 'id' )] = false;
				}
			},
			error : function () {
				// None found
				$el.find( '.pods-sister-field' ).html( default_select );

				pods_sister_field_going[id + '_' + $el.prop( 'id' )] = false;
			}
		} );
	}
</script>
