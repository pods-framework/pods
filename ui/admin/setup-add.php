<?php
$pods_meta = pods_meta();

$ignore = [];

// Only add support for built-in taxonomy "link_category" if link manager is enabled.
$link_manager_enabled = (int) get_option( 'link_manager_enabled', 0 );

if ( 0 === $link_manager_enabled ) {
	$ignore['link_category'] = true;
}

$all_pods = pods_api()->load_pods( [ 'key_names' => true ] );

$quick_actions = [];

if ( ! pods_is_types_only() ) {
	if ( ! isset( $all_pods['post'] ) ) {
		$quick_actions['post_type-post'] = [
			'label'         => __( 'Add custom fields to Posts', 'pods' ),
			'create_extend' => 'extend',
			'type'          => 'post_type',
			'object'        => 'post',
		];
	}

	if ( ! isset( $all_pods['page'] ) ) {
		$quick_actions['post_type-page'] = [
			'label'         => __( 'Add custom fields to Pages', 'pods' ),
			'create_extend' => 'extend',
			'type'          => 'post_type',
			'object'        => 'page',
		];
	}

	if ( ! isset( $all_pods['category'] ) ) {
		$quick_actions['taxonomy-category'] = [
			'label'         => __( 'Add custom fields to Categories', 'pods' ),
			'create_extend' => 'extend',
			'type'          => 'taxonomy',
			'object'        => 'category',
		];
	}

	if ( ! isset( $all_pods['user'] ) ) {
		$quick_actions['user'] = [
			'label'         => __( 'Add custom fields to Users', 'pods' ),
			'create_extend' => 'extend',
			'type'          => 'user',
			'object'        => 'user',
		];
	}
}

$extend_post_type_linked = pods_v( 'pods_extend_post_type' );
$extend_post_type_nonce  = pods_v( 'pods_extend_post_type_nonce' );

$submit_from_linked = false;

if ( $extend_post_type_linked && wp_verify_nonce( $extend_post_type_nonce, 'pods_extend_post_type_' . $extend_post_type_linked ) ) {
	$submit_from_linked = 'post_type-' . $extend_post_type_linked;

	$quick_actions[ $submit_from_linked ] = [
		// Translators: %s is the post type name (not label).
		'label'         => sprintf( __( 'Extend Post Type: %s', 'pods' ), $extend_post_type_linked ),
		'create_extend' => 'extend',
		'type'          => 'post_type',
		'object'        => $extend_post_type_linked,
	];
}

/**
 * Allow filtering the list of quick actions.
 *
 * @since 2.8.0
 *
 * @param array $quick_actions List of quick actions with the following info: label, create_extend, type, object.
 * @param array $all_pods      List of pods, keyed by name.
 */
$quick_actions = apply_filters( 'pods_admin_setup_add_quick_actions', $quick_actions, $all_pods );
?>

<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>

	<form action="" method="post" class="pods-submittable">
		<div class="pods-submittable-fields">
			<input type="hidden" name="action" value="pods_admin" />
			<input type="hidden" name="method" value="add_pod" />
			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'pods-add_pod' ) ); ?>" />
			<input type="hidden" name="create_extend" id="pods_create_extend" value="create" />

			<h2 class="italicized">
				<?php
				esc_html_e( 'Add New Pod', 'pods' );

				if ( ! empty( $all_pods ) ) {
					$link = pods_query_arg( [ 'page' => 'pods', 'action' . $obj->num => 'manage' ] );
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php esc_html_e( 'Back to Manage', 'pods' ); ?></a>
					<?php
				}
				?>
			</h2>

			<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

			<div id="pods-wizard-box" class="pods-wizard-steps-2 pods-wizard-hide-first">
				<div id="pods-wizard-heading">
					<ul>
						<li class="pods-wizard-menu-current" data-step="1">
							<i></i> <span>1</span> <?php esc_html_e( 'Create or Extend', 'pods' ); ?>
							<em></em>
						</li>
						<li data-step="2">
							<i></i> <span>2</span> <?php esc_html_e( 'Configure', 'pods' ); ?>
							<em></em>
						</li>
					</ul>
				</div>
				<div id="pods-wizard-main">
					<div id="pods-wizard-panel-1" class="pods-wizard-panel">
						<div class="pods-wizard-content">
							<p>
								<?php esc_html_e( 'Pods are content types that you can customize and define fields for based on your needs. You can choose to create a Custom Post Type, Custom Taxonomy, or Custom Settings Pages for site-specific data. You can also extend existing content types like WP Objects such as Post Types, Taxonomies, Users, or Comments.', 'pods' ); ?>
								<br /><br />
								<?php _e( 'Not sure what content type you should use? Check out our <a href="https://docs.pods.io/creating-editing-pods/compare-content-types/" target="_blank" rel="noopener noreferrer">Content Type Comparison</a> to help you decide.', 'pods' ); ?>
							</p>

							<?php if ( ! empty( $quick_actions ) ) : ?>
								<div id="pods-wizard-quick-actions"<?php echo ( $submit_from_linked ? ' class="hidden"' : '' ); ?>>
									<h3><?php esc_html_e( 'One-Click Quick Actions', 'pods' ); ?></h3>

									<ul class="normal">
										<?php foreach ( $quick_actions as $quick_action_key => $quick_action ) : ?>
											<li>
												<a href="#<?php echo sanitize_title( $quick_action['create_extend'] . '-' . $quick_action['type'] . '-' . $quick_action['object'] ); ?>"
													data-create-extend="<?php echo esc_attr( $quick_action['create_extend'] ); ?>"
													data-object="<?php echo esc_attr( $quick_action['object'] ); ?>"
													data-type="<?php echo esc_attr( $quick_action['type'] ); ?>"
													class="pods-wizard-quick-action"
													id="pods-wizard-quick-action-<?php echo esc_attr( $quick_action_key ); ?>"
												>
													<?php echo esc_html( $quick_action['label'] ); ?>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>

								<div id="pods-wizard-quick-actions-saving-in-progress"<?php echo ( ! $submit_from_linked ? ' class="hidden"' : '' ); ?>>
									<p><span class="pods-dfv-field__loading-indicator" role="progressbar"></span> <?php esc_html_e( 'Creating your Extended Pod', 'pods' ); ?></p>
								</div>
							<?php endif; ?>
						</div>
						<div id="pods-wizard-options">
							<h3><?php esc_html_e( 'Add New Pod Wizard', 'pods' ); ?></h3>

							<div class="pods-wizard-options-list">
								<div class="pods-wizard-option">
									<a href="#pods-wizard-create" data-opt="create">
										<h2><?php esc_html_e( 'Create New', 'pods' ); ?> &raquo;</h2>

										<p><?php _e( 'Create entirely new content types using <strong>Post Types</strong>, <strong>Taxonomies</strong>, or <strong>Custom Settings Pages</strong>.', 'pods' ); ?></p>
									</a>
								</div>

								<div class="pods-wizard-option">
									<a href="#pods-wizard-extend" data-opt="extend">
										<h2><?php esc_html_e( 'Extend Existing', 'pods' ); ?> &raquo;</h2>

										<p><?php _e( 'Extend any existing content type within WordPress, including <strong>Post Types</strong> (Posts, Pages, etc), <strong>Taxonomies</strong> (Categories, Tags, etc), <strong>Media</strong>, <strong>Users</strong>, or <strong>Comments</strong>.', 'pods' ); ?></p>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div id="pods-wizard-panel-2" class="pods-wizard-panel">
						<div class="pods-wizard-option-content" id="pods-wizard-create">
							<div class="pods-wizard-content">
								<p><?php esc_html_e( 'Creating a new Content Type allows you to control exactly what that content type does, how it acts like, the fields it has, and the way you manage it.', 'pods' ); ?></p>
							</div>
							<div class="stuffbox">
								<h3><label for="link_name"><?php esc_html_e( 'Create a New Content Type', 'pods' ); ?></label>
								</h3>

								<div class="inside pods-manage-field pods-dependency">
									<div class="pods-field__container">
										<?php
										echo PodsForm::label( 'create_pod_type', __( 'Content Type', 'pods' ), [
											__( '<h3>Content Types</h3> There are many content types to choose from, we have put together a comparison between them all to help you decide what fits your needs best.', 'pods' ),
											'https://docs.pods.io/creating-editing-pods/compare-content-types/',
										] );

										$data = [
											'post_type' => __( 'Custom Post Type (like Posts or Pages)', 'pods' ),
											'taxonomy'  => __( 'Custom Taxonomy (like Categories or Tags)', 'pods' ),
											'settings'  => __( 'Custom Settings Page', 'pods' ),
											'pod'       => ''
											// component will fill this in if it's enabled (this exists for placement)
										];

										$data = apply_filters( 'pods_admin_setup_add_create_pod_type', $data );

										if ( empty( $data['pod'] ) ) {
											unset( $data['pod'] );
										}

										if ( pods_is_types_only() ) {
											unset( $data['settings'], $data['pod'] );
										}

										echo PodsForm::field( 'create_pod_type', pods_v( 'create_pod_type', 'post', 'post_type', true ), 'pick', [
											'data'       => $data,
											'pick_format_single' => 'dropdown',
											'dependency' => true,
										] );
										?>
									</div>
									<div class="pods-field__container">
										<?php
										echo PodsForm::label( 'create_label_singular', __( 'Singular Label', 'pods' ), __( '<h3>Singular Label</h3> This is the label for 1 item (Singular) that will appear throughout the WordPress admin area for managing the content.', 'pods' ) );
										echo PodsForm::field( 'create_label_singular', pods_v( 'create_label_singular', 'post' ), 'text', [
											'class'           => 'pods-validate pods-validate-required',
											'text_max_length' => 30,
											'excludes-on'     => [
												'create_pod_type' => 'settings',
											],
										] );
										?>
									</div>
									<div class="pods-field__container">
										<?php
										echo PodsForm::label( 'create_label_plural', __( 'Plural Label', 'pods' ), __( '<h3>Plural Label</h3> This is the label for more than 1 item (Plural) that will appear throughout the WordPress admin area for managing the content.', 'pods' ) );
										echo PodsForm::field( 'create_label_plural', pods_v( 'create_label_plural', 'post' ), 'text', [
											'text_max_length' => 30,
											'excludes-on'     => [
												'create_pod_type' => 'settings',
											],
										] );
										?>
									</div>
									<div class="pods-field__container">
										<?php
										echo PodsForm::label( 'create_label_title', __( 'Page Title', 'pods' ), __( '<h3>Page Title</h3> This is the text that will appear at the top of your settings page.', 'pods' ) );
										echo PodsForm::field( 'create_label_title', pods_v( 'create_label_title', 'post' ), 'text', [
											'class'           => 'pods-validate pods-validate-required',
											'text_max_length' => 30,
											'depends-on'      => [
												'create_pod_type' => 'settings',
											],
										] );
										?>
									</div>
									<div class="pods-field__container">
										<?php
										echo PodsForm::label( 'create_label_menu', __( 'Menu Label', 'pods' ), __( '<h3>Menu Label</h3> This is the label that will appear throughout the WordPress admin area for your settings.', 'pods' ) );
										echo PodsForm::field( 'create_label_menu', pods_v( 'create_label_menu', 'post' ), 'text', [
											'text_max_length' => 30,
											'depends-on'      => [
												'create_pod_type' => 'settings',
											],
										] );
										?>
									</div>
									<div class="pods-field__container">
										<?php
										echo PodsForm::label( 'create_menu_location', __( 'Menu Location', 'pods' ), __( '<h3>Menu Location</h3> This is the location where the new settings page will be added in the WordPress Dashboard menu.', 'pods' ) );

										$data = [
											'settings'    => __( 'Add to Settings menu', 'pods' ),
											'appearances' => __( 'Add to Appearances menu', 'pods' ),
											'top'         => __( 'Make a new menu item below Settings', 'pods' ),
										];

										echo PodsForm::field( 'create_menu_location', pods_v( 'create_menu_location', 'post' ), 'pick', [
											'data' => $data,
											'pick_format_single' => 'dropdown',
											'depends-on'      => [
												'create_pod_type' => 'settings',
											],
										] );
										?>
									</div>

									<p>
										<a href="#pods-advanced" class="pods-advanced-toggle"><?php esc_html_e( 'Advanced', 'pods' ); ?> +</a>
									</p>

									<div class="pods-advanced">
										<div class="pods-field__container">
											<?php
											global $wpdb;
											$max_length_name = 64;
											$max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
											$max_length_name -= strlen( $wpdb->prefix . 'pods_' );

											echo PodsForm::label( 'create_name', __( 'Pod Name', 'pods' ), __( '<h3>Pod Identifier</h3> This is different than the labels users will see in the WordPress admin areas, it is the name you will use to programatically reference this object throughout your theme, WordPress, and other PHP.', 'pods' ) );
											echo PodsForm::field( 'create_name', pods_v( 'create_name', 'post' ), 'slug', [
												'attributes'  => [
													'maxlength' => $max_length_name,
													'size'      => 25,
												],
												'excludes-on' => [
													'create_pod_type' => 'settings',
												],
											] );
											?>
										</div>
										<div class="pods-field__container">
											<?php
											global $wpdb;
											$max_length_name = 64;
											$max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
											$max_length_name -= strlen( $wpdb->prefix . 'pods_' );

											echo PodsForm::label( 'create_setting_name', __( 'Pod Name', 'pods' ), __( '<h3>Pod Identifier</h3> This is different than the labels users will see in the WordPress admin areas, it is the name you will use to programatically reference this object throughout your theme, WordPress, and other PHP.', 'pods' ) );
											echo PodsForm::field( 'create_setting_name', pods_v( 'create_setting_name', 'post' ), 'slug', [
												'attributes' => [
													'maxlength' => $max_length_name,
													'size'      => 25,
												],
												'depends-on' => [
													'create_pod_type' => 'settings',
												],
											] );
											?>
										</div>
										<div class="pods-field__container">
											<?php
											echo PodsForm::label( 'create_rest_api', __( 'Enable REST API', 'pods' ), __( 'This option will enable the REST API. For Custom Post Types, this will allow communication with the Block Editor. For Custom Taxonomies, this simply enables the REST API communication that can be taken advantage of by theme integrations.', 'pods' ) );
											echo PodsForm::field( 'create_rest_api', pods_v( 'create_block_editor', 'post' ), 'boolean', [
												'default' => 1,
												'depends-on' => [
													'create_pod_type' => [
														'post_type',
														'taxonomy',
													],
												],
											] );
											?>
										</div>

										<?php
										if ( ! pods_tableless() && apply_filters( 'pods_admin_setup_add_create_storage', false ) ) {
											?>
											<div class="pods-field__container">
												<?php
												echo PodsForm::label( 'create_storage', __( 'Storage Type', 'pods' ), [
													__( '<h3>Storage Types</h3> Table based storage will operate in a way where each field you create for your content type becomes a field in a table. Meta based storage relies upon the WordPress meta storage table for all field data.', 'pods' ),
													'https://docs.pods.io/creating-editing-pods/meta-vs-table-storage/',
												] );

												$data = [
													'meta'  => __( 'Meta Based (WP Default)', 'pods' ),
													'table' => __( 'Table Based', 'pods' ),
												];

												echo PodsForm::field( 'create_storage', pods_v( 'create_storage', 'post' ), 'pick', [
													'data'       => $data,
													'pick_format_single' => 'dropdown',
													'depends-on' => [
														'create_pod_type' => [
															'post_type',
															'taxonomy',
														],
													],
												] );
												?>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
						</div>
						<div class="pods-wizard-option-content" id="pods-wizard-extend">
							<div class="pods-wizard-content">
								<p><?php esc_html_e( 'Extending an existing Content Type allows you to add fields to it and take advantage of the Pods architecture for management and optionally for theming.', 'pods' ); ?></p>
							</div>
							<div class="stuffbox">
								<h3>
									<label for="link_name"><?php esc_html_e( 'Extend an Existing Content Type', 'pods' ); ?></label>
								</h3>

								<div class="inside pods-manage-field pods-dependency">

									<div class="pods-field__container">
										<?php
										echo PodsForm::label( 'extend_pod_type', __( 'Content Type', 'pods' ), [
											__( '<h3>Content Types</h3> There are many content types to choose from, we have put together a comparison between them all to help you decide what fits your needs best.', 'pods' ),
											'https://docs.pods.io/creating-editing-pods/compare-content-types/',
										] );

										$data = [
											'post_type' => __( 'Post Types (Posts, Pages, etc..)', 'pods' ),
											'taxonomy'  => __( 'Taxonomies (Categories, Tags, etc..)', 'pods' ),
											'media'     => __( 'Media', 'pods' ),
											'user'      => __( 'Users', 'pods' ),
											'comment'   => __( 'Comments', 'pods' ),
										];

										if ( isset( $all_pods['media'] ) && 'media' == $all_pods['media']['type'] ) {
											unset( $data['media'] );
										}

										if ( isset( $all_pods['user'] ) && 'user' == $all_pods['user']['type'] ) {
											unset( $data['user'] );
										}

										if ( isset( $all_pods['comment'] ) && 'comment' == $all_pods['comment']['type'] ) {
											unset( $data['comment'] );
										}

										$data = apply_filters( 'pods_admin_setup_add_extend_pod_type', $data );

										echo PodsForm::field( 'extend_pod_type', pods_v( 'extend_pod_type', 'post', 'post_type', true ), 'pick', [
											'data'       => $data,
											'pick_format_single' => 'dropdown',
											'dependency' => true,
										] );
										?>
									</div>
									<div class="pods-field__container">
										<?php
										$post_types = get_post_types();

										foreach ( $post_types as $post_type => $label ) {
											if ( empty( $post_type ) || isset( $ignore[ $post_type ] ) || 0 === strpos( $post_type, '_pods_' ) || ! $pods_meta->is_type_covered( 'post_type', $post_type ) ) {
												// Post type is ignored
												unset( $post_types[ $post_type ] );

												continue;
											} elseif ( isset( $all_pods[ $post_type ] ) && 'post_type' === $all_pods[ $post_type ]['type'] ) {
												unset( $post_types[ $post_type ] );

												continue;
											}

											$post_type                      = get_post_type_object( $post_type );
											$post_types[ $post_type->name ] = $post_type->label . ' (' . $post_type->name . ')';
										}

										/**
										 * Allow filtering the list of post types that can be extended by Pods.
										 *
										 * @since 2.9.17
										 *
										 * @param array<string,string> $post_types The list of post types.
										 */
										$post_types = apply_filters( 'pods_admin_setup_add_extend_post_types', $post_types );

										echo PodsForm::label( 'extend_post_type', __( 'Post Type', 'pods' ), [
											__( '<h3>Post Types</h3> WordPress can hold and display many different types of content. Internally, these are all stored in the same place, in the wp_posts table. These are differentiated by a column called post_type.', 'pods' ),
											'http://codex.wordpress.org/Post_Types',
										] );
										echo PodsForm::field( 'extend_post_type', pods_v( 'extend_post_type', 'post' ), 'pick', [
											'data'       => $post_types,
											'pick_format_single' => 'dropdown',
											'depends-on' => [
												'extend_pod_type' => 'post_type',
											],
										] );
										?>
									</div>
									<div class="pods-field__container">
										<?php
										$taxonomies = get_taxonomies();

										foreach ( $taxonomies as $taxonomy => $label ) {
											if ( empty( $taxonomy ) || isset( $ignore[ $taxonomy ] ) || 0 === strpos( $taxonomy, '_pods_' ) || ! $pods_meta->is_type_covered( 'taxonomy', $taxonomy ) ) {
												// Taxonomy is ignored
												unset( $taxonomies[ $taxonomy ] );

												continue;
											} elseif ( isset( $all_pods[ $taxonomy ] ) && 'taxonomy' == $all_pods[ $taxonomy ]['type'] ) {
												unset( $taxonomies[ $taxonomy ] );
												continue;
											}

											$taxonomy                      = get_taxonomy( $taxonomy );
											$taxonomies[ $taxonomy->name ] = $taxonomy->label . ' (' . $taxonomy->name . ')';
										}

										/**
										 * Allow filtering the list of taxonomies that can be extended by Pods.
										 *
										 * @since 2.9.17
										 *
										 * @param array<string,string> $taxonomies The list of taxonomies.
										 */
										$taxonomies = apply_filters( 'pods_admin_setup_add_extend_taxonomies', $taxonomies );

										echo PodsForm::label( 'extend_taxonomy', __( 'Taxonomy', 'pods' ), [
											__( '<h3>Taxonomies</h3> A taxonomy is a way to group Post Types.', 'pods' ),
											'http://codex.wordpress.org/Taxonomies',
										] );
										echo PodsForm::field( 'extend_taxonomy', pods_v( 'extend_taxonomy', 'post' ), 'pick', [
											'data'       => $taxonomies,
											'pick_format_single' => 'dropdown',
											'depends-on' => [
												'extend_pod_type' => 'taxonomy',
											],
										] );
										?>
									</div>

									<?php
									if ( ! pods_tableless() && apply_filters( 'pods_admin_setup_add_extend_storage', false ) ) {
										?>
										<p>
											<a href="#pods-advanced" class="pods-advanced-toggle"><?php esc_html_e( 'Advanced', 'pods' ); ?> +</a>
										</p>

										<div class="pods-advanced">
											<div class="pods-field__container">
												<?php
												echo PodsForm::label( 'extend_storage', __( 'Storage Type', 'pods' ), [
													__( '<h3>Storage Types</h3> Table based storage will operate in a way where each field you create for your content type becomes a field in a table. Meta based storage relies upon the WordPress meta storage table for all field data.', 'pods' ),
													'https://docs.pods.io/creating-editing-pods/meta-vs-table-storage/',
												] );

												$data = [
													'meta'  => __( 'Meta Based (WP Default)', 'pods' ),
													'table' => __( 'Table Based', 'pods' ),
												];

												echo PodsForm::field( 'extend_storage', pods_v( 'extend_storage', 'post' ), 'pick', [
													'data'       => $data,
													'pick_format_single' => 'dropdown',
													'depends-on' => [
														'extend_pod_type' => [
															'post_type',
															'taxonomy',
															'media',
															'user',
															'comment',
														],
													],
												] );
												?>
											</div>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
					</div>

					<div id="pods-wizard-actions" class="pods-wizard-button-interface">
						<div id="pods-wizard-toolbar">
							<button id="pods-wizard-start" class="button button-secondary"><?php esc_html_e( 'Start Over', 'pods' ); ?></button>
							<button id="pods-wizard-next" class="button button-primary" data-next="<?php esc_attr_e( 'Next Step', 'pods' ); ?>" data-finished="<?php esc_attr_e( 'Finished', 'pods' ); ?>" data-processing="<?php esc_attr_e( 'Processing', 'pods' ); ?>.."><?php esc_html_e( 'Next Step', 'pods' ); ?></button>
						</div>
						<div id="pods-wizard-finished">

						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
	var pods_admin_submit_callback = function ( id ) {
		id = parseInt( id );

		if ( !isNaN( id ) ) {
			document.location = 'admin.php?page=pods&action=edit&id=' + id + '&do=create';
		}
		else {
			document.location = 'admin.php?page=pods&do=create';
		}
	};

	var pods_admin_submit_error_callback = function ( err_msg ) {
		alert( 'Error: ' + err_msg );
		if ( window.console ) console.log( err_msg );

		jQuery( '#pods-wizard-quick-actions-saving-in-progress' ).hide();
	};

	var pods_admin_option_select_callback = function ( $opt ) {
		jQuery( '#pods_create_extend' ).val( $opt.data( 'opt' ) );
	};

	jQuery( function ( $ ) {
		var $document = $( document );

		$document.Pods( 'validate' );
		$document.Pods( 'submit' );
		$document.Pods( 'wizard' );
		$document.Pods( 'advanced' );
		$document.Pods( 'confirm' );
		$document.Pods( 'sluggable' );

		const $quick_actions = $( '.pods-wizard-quick-action' );

		if ( $quick_actions[0] ) {
			$quick_actions.on( 'click', function( e ) {
				e.preventDefault();

				const $action = $( this );
				const createExtend = $action.data( 'create-extend' );
				const objectName = $action.data( 'object' );
				const objectType = $action.data( 'type' );

				jQuery( '#pods_create_extend' ).val( createExtend );
				jQuery( '#pods-form-ui-' + createExtend + '-pod-type' ).val( objectType );
				jQuery( '#pods-form-ui-' + createExtend + '-' + objectType.replace( '_', '-' ) ).val( objectName );

				$action.closest( 'form' ).submit();
			} );

			<?php if ( $submit_from_linked ) : ?>
				jQuery( '#pods-wizard-quick-action-<?php echo esc_attr( $submit_from_linked ); ?>' ).click();

				$quick_actions.off( 'click' );

				jQuery( '#pods-wizard-quick-actions' ).hide();
				jQuery( '#pods-wizard-quick-actions-saving-in-progress' ).show();
			<?php endif; ?>
		}
	} );
</script>
