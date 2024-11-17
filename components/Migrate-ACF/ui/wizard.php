<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>

	<form action="" method="post" class="pods-submittable">
		<div class="pods-submittable-fields">
			<?php echo PodsForm::field( 'action', 'pods_admin_components', 'hidden' ); ?>
			<?php echo PodsForm::field( 'component', $component, 'hidden' ); ?>
			<?php echo PodsForm::field( 'method', $method, 'hidden' ); ?>
			<?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-component-' . $component . '-' . $method ), 'hidden' ); ?>
			<?php echo PodsForm::field( 'cleanup', 0, 'hidden', array( 'attributes' => array( 'id' => 'pods_cleanup' ) ) ); ?>

			<h2 class="italicized"><?php esc_html_e( 'Migrate: Import from Advanced Custom Fields', 'pods' ); ?></h2>

			<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

			<div id="pods-wizard-box" class="pods-wizard-steps-2 pods-wizard-hide-first">
				<div id="pods-wizard-heading">
					<ul>
						<li class="pods-wizard-menu-current" data-step="1">
							<i></i> <span>1</span> <?php esc_html_e( 'Step 1: Setup', 'pods' ); ?>
							<em></em>
						</li>
						<li data-step="2">
							<i></i> <span>2</span> <?php esc_html_e( 'Step 2: Migrate', 'pods' ); ?>
							<em></em>
						</li>
					</ul>
				</div>
				<div id="pods-wizard-main">
					<div id="pods-wizard-panel-1" class="pods-wizard-panel">
						<div class="pods-wizard-content">
							<p><?php esc_html_e( 'Advanced Custom Fields provides an interface to create Custom Post Types and Custom Taxonomies. You can import these and their settings directly into Pods', 'pods' ); ?></p>
						</div>

						<div id="pods-wizard-options">
							<div class="pods-wizard-options-list">
								<div class="pods-wizard-option">
									<a href="#pods-wizard-import" data-opt="0">
										<div>
											<h2><?php esc_html_e( 'Import Only', 'pods' ); ?></h2>

											<p><?php esc_html_e( 'This will import your Custom Post Types and Taxonomies.', 'pods' ); ?></p>
											<p><?php esc_html_e( 'Until you remove them from Advanced Custom Fields, these will be treated by Pods as extended content types.', 'pods' ); ?></p>
										</div>
										<span>&#10095;</span>
									</a>
								</div>
								<div class="pods-wizard-option">
									<a href="#pods-wizard-import-clean" data-opt="1">
										<div>
											<h2><?php esc_html_e( 'Import and Clean Up', 'pods' ); ?></h2>

											<p><?php esc_html_e( 'This will import your Custom Post Types and Taxonomies, and then remove them from Advanced Custom Fields.', 'pods' ); ?></p>
										</div>
										<span>&#10095;</span>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div id="pods-wizard-panel-2" class="pods-wizard-panel pods-wizard-option-content">
						<div class="pods-wizard-content">
							<p><?php esc_html_e( 'Choose below which Custom Post Types and Taxonomies you want to import into Pods', 'pods' ); ?></p>

							<p>
								<a href="#toggle" class="button pods-wizard-toggle-all"
									data-toggle="all"><?php esc_html_e( 'Toggle everything on / off', 'pods' ); ?></a>
							</p>
						</div>

						<div class="stuffbox">
							<h3><label for="link_name"><?php esc_html_e( 'Choose Post Types to Import', 'pods' ); ?></label>
							</h3>

							<div class="inside pods-manage-field pods-dependency">
								<?php
								if ( ! empty( $post_types ) ) {
									?>
									<div class="pods-field-option-group">
										<p class="pods-field-option-group-label">
											<?php esc_html_e( 'Available Post Types', 'pods' ); ?>
										</p>

										<div class="pods-pick-values pods-pick-checkbox">
											<p>
												<a href="#toggle" class="button pods-wizard-toggle-all"
													data-toggle="post_type"><?php esc_html_e( 'Toggle all on / off', 'pods' ); ?></a>
											</p>

											<ul>
												<?php
												foreach ( $post_types as $post_type ) {
													$post_type_name  = pods_v( 'post_type', $post_type );
													$post_type_label = pods_v( 'title', $post_type, ucwords( str_replace( '_', ' ', $post_type_name ) ) );
													?>
													<li>
														<div class="pods-field pods-boolean">
															<?php
															echo PodsForm::field( 'post_type[' . $post_type_name . ']', pods_v( 'post_type[' . $post_type_name . ']', 'post', true ), 'boolean', [
																'boolean_yes_label' => $post_type_label . ' (' . $post_type_name . ')',
																'disable_dfv'       => true,
															] );
															?>
														</div>
													</li>
													<?php
												}
												?>
											</ul>
										</div>
									</div>
									<?php
								} else {
									?>
									<p class="padded"><?php esc_html_e( 'No Post Types were found.', 'pods' ); ?></p>
									<?php
								}//end if
								?>
							</div>
						</div>

						<div class="stuffbox">
							<h3><label for="link_name"><?php esc_html_e( 'Choose Taxonomies to Import', 'pods' ); ?></label>
							</h3>

							<div class="inside pods-manage-field pods-dependency">
								<?php
								if ( ! empty( $taxonomies ) ) {
									?>
									<div class="pods-field-option-group">
										<p class="pods-field-option-group-label">
											<?php esc_html_e( 'Available Taxonomies', 'pods' ); ?>
										</p>

										<div class="pods-pick-values pods-pick-checkbox">
											<p>
												<a href="#toggle" class="button pods-wizard-toggle-all"
													data-toggle="taxonomy"><?php esc_html_e( 'Toggle all on / off', 'pods' ); ?></a>
											</p>

											<ul>
												<?php
												foreach ( $taxonomies as $taxonomy ) {
													$taxonomy_name  = pods_v( 'taxonomy', $taxonomy );
													$taxonomy_label = pods_v( 'title', $taxonomy, ucwords( str_replace( '_', ' ', $taxonomy_name ) ) );
													?>
													<li>
														<?php
														echo PodsForm::field( 'taxonomy[' . $taxonomy_name . ']', pods_v( 'taxonomy[' . $taxonomy_name . ']', 'post', true ), 'boolean', [
															'boolean_yes_label' => $taxonomy_label . ' (' . $taxonomy_name . ')',
															'disable_dfv'       => true,
														] );
														?>
													</li>
													<?php
												}
												?>
											</ul>
										</div>
									</div>
									<?php
								} else {
									?>
									<p class="padded"><?php esc_html_e( 'No Taxonomies were found.', 'pods' ); ?></p>
									<?php
								}//end if
								?>
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
		document.location = 'admin.php?page=pods&do=create';
	};

	var pods_admin_option_select_callback = function ( $opt ) {
		jQuery( '#pods_cleanup' ).val( $opt.data( 'opt' ) );
	};

	const toggle_all = {
		all: true
	};

	jQuery( function ( $ ) {
		$( '.pods-wizard-toggle-all' ).on( 'click', function ( e ) {
			e.preventDefault();

			const toggleData = $( this ).data( 'toggle' );

			if ( 'undefined' == typeof toggle_all[toggleData] ) {
				toggle_all[toggleData] = true;
			}

			let $parent;

			if ( 'all' !== toggleData ) {
				$parent = $( this ).closest( '.pods-field-option-group' );
			} else {
				$parent = $( this ).closest( '.pods-wizard-option-content' );
			}

			$parent.find( '.pods-field.pods-boolean input[type="checkbox"]' ).prop( 'checked', (!toggle_all[toggleData]) );

			toggle_all[toggleData] = (!toggle_all[toggleData]);
		} );

		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'wizard' );
		$( document ).Pods( 'dependency' );
		$( document ).Pods( 'advanced' );
		$( document ).Pods( 'confirm' );
	} );
</script>
