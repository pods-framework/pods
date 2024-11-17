<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>

	<form action="" method="post" class="pods-submittable">
		<div class="pods-submittable-fields">
			<?php echo PodsForm::field( 'action', 'pods_admin_components', 'hidden' ); ?>
			<?php echo PodsForm::field( 'component', $component, 'hidden' ); ?>
			<?php echo PodsForm::field( 'method', $method, 'hidden' ); ?>
			<?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-component-' . $component . '-' . $method ), 'hidden' ); ?>
			<?php echo PodsForm::field( 'cleanup', 0, 'hidden', array( 'attributes' => array( 'id' => 'pods_cleanup' ) ) ); ?>

			<h2 class="italicized"><?php esc_html_e( 'Migrate: Pod Page and Pod Template PHP into File-based templates', 'pods' ); ?></h2>

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
							<p><?php esc_html_e( 'PHP in Pods is deprecated and will no longer be supported. You can use this tool to migrate your Pod Pages and Pod Templates into files within your current theme.', 'pods' ); ?></p>

							<?php if ( ! $has_objects_to_migrate ) : ?>
								<p>✅ <?php esc_html_e( 'No Pod Pages or Pod Templates have been found that contain PHP.', 'pods' ); ?></p>
							<?php endif; ?>
						</div>

						<?php if ( $has_objects_to_migrate ) : ?>
							<div id="pods-wizard-options">
								<div class="pods-wizard-options-list">
									<div class="pods-wizard-option">
										<a href="#pods-wizard-run" data-opt="0">
											<div>
												<h2><?php esc_html_e( 'Migrate PHP to files', 'pods' ); ?></h2>

												<p><?php esc_html_e( 'This will migrate Pod Pages and Pod Templates that have PHP in them into files in your theme folder.', 'pods' ); ?></p>
											</div>
											<span>&#10095;</span>
										</a>
									</div>
									<div class="pods-wizard-option">
										<a href="#pods-wizard-run-clean" data-opt="1">
											<div>
												<h2><?php esc_html_e( 'Migrate PHP to files and clear the content in the DB', 'pods' ); ?></h2>

												<p><?php esc_html_e( 'This will migrate Pod Pages and Pod Templates that have PHP in them into files in your theme folder, and then clear the content in the DB for those.', 'pods' ); ?></p>
												<p><?php esc_html_e( 'Please be sure to backup your database before you run this tool.', 'pods' ); ?></p>
											</div>
											<span>&#10095;</span>
										</a>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<div id="pods-wizard-panel-2" class="pods-wizard-panel pods-wizard-option-content">
						<div class="pods-wizard-content">
							<p><?php esc_html_e( 'The objects listed have been determined to currently contain PHP that needs to be migrated. Choose below which Pod Page(s) and/or Pod Template(s) you want to migrate into theme files', 'pods' ); ?></p>
							<p><?php esc_html_e( 'If you have chosen to clear the content in this migration, when you refresh this migration screen the migrated objects will no longer be shown.', 'pods' ); ?></p>

							<p>
								<a href="#toggle" class="button pods-wizard-toggle-all"
									data-toggle="all"><?php esc_html_e( 'Toggle everything on / off', 'pods' ); ?></a>
							</p>
						</div>

						<?php if ( ! empty( $pod_templates ) ) : ?>
							<?php
							$data      = $pod_templates;
							$data_name = 'templates';
							?>
							<div class="stuffbox pods-migrate-php-group">
								<h3>
									<label
										for="link_name"><?php esc_html_e( 'Choose which Pod Templates to migrate', 'pods' ); ?></label>
								</h3>

								<div class="inside pods-manage-field pods-dependency">
									<div class="pods-field-option-group">
										<div class="pods-pick-values pods-pick-checkbox pods-zebra">
											<p>
												<a href="#toggle" class="button pods-wizard-toggle-all"
													data-toggle="<?php echo esc_attr( $data_name ); ?>"><?php esc_html_e( 'Toggle all on / off', 'pods' ); ?></a>
											</p>

											<ul>
												<?php
												$zebra = false;

												foreach ( $data as $item ) {
													$checked = true;

													$class = ( $zebra ? 'even' : 'odd' );

													$zebra = ( ! $zebra );
													?>
													<li class="pods-zebra-<?php echo esc_attr( $class ); ?>">
														<?php
														echo PodsForm::field( $data_name . '[' . $item['id'] . ']', $checked, 'boolean', [
															'boolean_yes_label' => $item['name'] . ( ! empty( $item['label'] ) ? ' (' . $item['label'] . ')' : '' ),
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
								</div>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $pod_pages ) ) : ?>
							<?php
							$data      = $pod_pages;
							$data_name = 'pages';
							?>
							<div class="stuffbox pods-migrate-php-group">
								<h3>
									<label
										for="link_name"><?php esc_html_e( 'Choose which Pod Pages to migrate', 'pods' ); ?></label>
								</h3>

								<div class="inside pods-manage-field pods-dependency">
									<div class="pods-field-option-group">
										<div class="pods-pick-values pods-pick-checkbox pods-zebra">
											<p>
												<a href="#toggle" class="button pods-wizard-toggle-all"
													data-toggle="<?php echo esc_attr( $data_name ); ?>"><?php esc_html_e( 'Toggle all on / off', 'pods' ); ?></a>
											</p>

											<ul>
												<?php
												$zebra = false;

												foreach ( $data as $item ) {
													$checked = true;

													$class = ( $zebra ? 'even' : 'odd' );

													$zebra = ( ! $zebra );
													?>
													<li class="pods-zebra-<?php echo esc_attr( $class ); ?>">
														<?php
														echo PodsForm::field( $data_name . '[' . $item['id'] . ']', $checked, 'boolean', [
															'boolean_yes_label' => $item['name'] . ( ! empty( $item['label'] ) ? ' (' . $item['label'] . ')' : '' ),
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
								</div>
							</div>
						<?php endif; ?>

						<span id="pods-wizard-result"></span>

						<div class="stuffbox hidden" id="pods-wizard-results">
							<h3><?php esc_html_e( 'Migration Results', 'pods' ); ?></h3>

							<div class="inside pods-manage-field pods-dependency">
								<div class="pods-wizard-results"></div>
							</div>
						</div>
					</div>

					<div id="pods-wizard-actions" class="pods-wizard-button-interface">
						<div id="pods-wizard-toolbar">
							<button id="pods-wizard-start" class="button button-secondary"><?php esc_html_e( 'Start Over', 'pods' ); ?></button>
							<button id="pods-wizard-next" class="button button-primary" data-again="<?php esc_attr_e( 'Process Again', 'pods' ); ?>" data-next="<?php esc_attr_e( 'Continue', 'pods' ); ?>" data-finished="<?php esc_attr_e( 'Finished', 'pods' ); ?>" data-processing="<?php esc_attr_e( 'Processing', 'pods' ); ?>.."><?php esc_html_e( 'Continue', 'pods' ); ?></button>
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
	const $pods_admin_wizard_results = jQuery( '#pods-wizard-results' );

	var pods_admin_wizard_callback = function ( step, completed ) {
		if ( 2 == step || !step ) {
			$pods_admin_wizard_results.slideUp( 'fast', function () {
				jQuery( 'div.pods-wizard-results', $pods_admin_wizard_results ).html( '' );
			} );
		}

		return true;
	};

	var pods_admin_option_select_callback = function ( $opt ) {
		jQuery( '#pods_cleanup' ).val( $opt.data( 'opt' ) );
	};

	var pods_admin_submit_callback = function ( id ) {
		jQuery( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
		jQuery( '#pods-wizard-next' ).prop( 'disabled', false );
		jQuery( '#pods-wizard-next' ).text( jQuery( '#pods-wizard-next' ).data( 'again' ) );

		window.location.hash = 'pods-wizard-result';

		jQuery( 'div.pods-wizard-results', $pods_admin_wizard_results ).html( id );
		$pods_admin_wizard_results.slideDown( 'fast' );

		return false;
	};

	var pods_admin_wizard_startover_callback = function () {
		$pods_admin_wizard_results.hide();
		jQuery( 'div.pods-wizard-results', $pods_admin_wizard_results ).html( '' );
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
