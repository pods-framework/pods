<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>

	<form action="" method="post" class="pods-submittable">
		<div class="pods-submittable-fields">
			<?php echo PodsForm::field( 'action', 'pods_admin_components', 'hidden' ); ?>
			<?php echo PodsForm::field( 'component', $component, 'hidden' ); ?>
			<?php echo PodsForm::field( 'method', $method, 'hidden' ); ?>
			<?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-component-' . $component . '-' . $method ), 'hidden' ); ?>
			<?php echo PodsForm::field( 'import_export', 'export', 'hidden' ); ?>

			<h2 class="italicized"><?php _e( 'Migrate: Packages', 'pods' ); ?></h2>

			<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

			<div id="pods-wizard-box" class="pods-wizard-steps-2" data-step-process="1">
				<div id="pods-wizard-heading">
					<ul>
						<li class="pods-wizard-menu-current" data-step="1">
							<i></i> <span>1</span> <?php _e( 'Choose', 'pods' ); ?> <em></em>
						</li>
						<li data-step="2">
							<i></i> <span>2</span> <?php _e( 'Import / Export', 'pods' ); ?> <em></em>
						</li>
					</ul>
				</div>

				<div id="pods-wizard-main">
					<?php
					$api = pods_api();

					$pods          = $api->load_pods( array( 'fields' => false ) );
					$pod_templates = $api->load_templates();
					$pod_pages     = $api->load_pages();
					$pod_helpers   = $api->load_helpers();

					$export = true;

					if ( empty( $pods ) && empty( $pod_templates ) && empty( $pod_pages ) && empty( $pod_helpers ) ) {
						$export = false;
					}
					?>

					<div id="pods-wizard-panel-1" class="pods-wizard-panel">
						<div class="pods-wizard-content">
							<p><?php _e( 'Packages allow you to import/export your Pods, Groups, Fields, and other settings between any Pods sites.', 'pods' ); ?></p>
						</div>

						<div id="pods-wizard-options">
							<div class="pods-wizard-option">
								<a href="#pods-wizard-import" data-opt="import">
									<h2><?php _e( 'Import', 'pods' ); ?></h2>

									<p><?php _e( 'Import a package of Pods, Groups, Fields, and other settings from another site.', 'pods' ); ?></p>
								</a>

								<p><br /></p>
							</div>

							<?php
							if ( $export ) {
								?>
								<div class="pods-wizard-option">
									<a href="#pods-wizard-export" data-opt="export">
										<h2><?php _e( 'Export', 'pods' ); ?></h2>

										<p><?php _e( 'Choose which Pods, Groups, Fields, and other settings to export into a package.', 'pods' ); ?></p>
									</a>

									<p><br /></p>
								</div>
								<?php
							}
							?>
						</div>
					</div>

					<div id="pods-wizard-panel-2" class="pods-wizard-panel">
						<div class="pods-wizard-option-content" id="pods-wizard-import">
							<div class="pods-wizard-content">
								<p><?php _e( 'Packages allow you to import/export your Pods, Groups, Fields, and other settings between any Pods sites.', 'pods' ); ?></p>
							</div>

							<div class="stuffbox">
								<h3><?php esc_html_e( 'Import your Package', 'pods' ); ?></h3>

								<div class="inside pods-manage-field pods-dependency">
									<div class="pods-field__container pods-field-option">
										<?php
										echo PodsForm::label( 'import_package_file', __( 'Upload your pods-package.json', 'pods' ) );
										?>
										<input type="file" name="import_package_file" id="pods-form-ui-import-package-file" accept=".json" />
										<button type="button"
											id="pods-form-ui-import-package-file-reset"
											class="button button-secondary button-small hidden"
											aria-hidden="true">
											<?php esc_html_e( 'Clear file', 'pods' ); ?>
										</button>
									</div>
									<div class="pods-field__container pods-field-option">
										<?php
										echo PodsForm::label( 'import_package', __( 'Or paste the Package code', 'pods' ), __( 'If you paste the code, you may encounter issues on certain hosts where mod_security will block the submission. If you encounter an error message on submit, contact your host and let them know that you believe you are seeing a mod_security issue with /wp-admin/admin-ajax.php and they can look through your error logs to help solve it.', 'pods' ) );
										echo PodsForm::field( 'import_package', pods_v( 'import_package', 'post' ), 'paragraph', [
											'attributes'  => [
												'style' => 'width: 100%; max-width: 100%; height: 250px;',
											],
											'disable_dfv' => true,
										] );
										?>
									</div>
								</div>
							</div>
						</div>

						<div class="pods-wizard-option-content" id="pods-wizard-export">
							<div class="pods-wizard-content">
								<p><?php _e( 'Packages allow you to import/export your Pods, Groups, Fields, and other settings between any Pods sites.', 'pods' ); ?></p>

								<p>
									<a href="#toggle" class="button pods-wizard-toggle-all" data-toggle="all"><?php _e( 'Toggle everything on / off', 'pods' ); ?></a>
								</p>
							</div>

							<div class="stuffbox pods-package-import-group">
								<h3>
									<label for="link_name"><?php _e( 'Choose whether to export Settings', 'pods' ); ?></label>
								</h3>

								<div class="inside pods-manage-field pods-dependency">
									<div class="pods-field-option-group">
										<div class="pods-pick-values pods-pick-checkbox pods-zebra">
											<ul>
												<?php
												$data_name = 'settings';
												$data = [
													'all' => __( 'All Settings', 'pods' ),
												];

												$zebra = false;

												foreach ( $data as $key => $label ) {
													$checked = true;

													$class = ( $zebra ? 'even' : 'odd' );

													$zebra = ( ! $zebra );
													?>
													<li class="pods-zebra-<?php echo esc_attr( $class ); ?>">
														<?php
														echo PodsForm::field( $data_name . '[' . $key . ']', true, 'boolean', [
															'boolean_yes_label' => $label,
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

							<?php
							if ( ! empty( $pods ) ) {
								$data      = $pods;
								$data_name = 'pods';
								?>
								<div class="stuffbox pods-package-import-group">
									<h3>
										<label for="link_name"><?php _e( 'Choose which Pods to export', 'pods' ); ?></label>
									</h3>

									<div class="inside pods-manage-field pods-dependency">
										<div class="pods-field-option-group">
											<div class="pods-pick-values pods-pick-checkbox pods-zebra">
												<p>
													<a href="#toggle" class="button pods-wizard-toggle-all" data-toggle="<?php echo esc_attr( $data_name ); ?>"><?php _e( 'Toggle all on / off', 'pods' ); ?></a>
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
								<?php
							}//end if

							if ( ! empty( $pod_templates ) ) {
								$data      = $pod_templates;
								$data_name = 'templates';
								?>
								<div class="stuffbox pods-package-import-group">
									<h3>
										<label for="link_name"><?php _e( 'Choose which Pod Templates to export', 'pods' ); ?></label>
									</h3>

									<div class="inside pods-manage-field pods-dependency">
										<div class="pods-field-option-group">
											<div class="pods-pick-values pods-pick-checkbox pods-zebra">
												<p>
													<a href="#toggle" class="button pods-wizard-toggle-all" data-toggle="<?php echo esc_attr( $data_name ); ?>"><?php _e( 'Toggle all on / off', 'pods' ); ?></a>
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
								<?php
							}//end if

							if ( ! empty( $pod_pages ) ) {
								$data      = $pod_pages;
								$data_name = 'pages';
								?>
								<div class="stuffbox pods-package-import-group">
									<h3>
										<label for="link_name"><?php _e( 'Choose which Pod Pages to export', 'pods' ); ?></label>
									</h3>

									<div class="inside pods-manage-field pods-dependency">
										<div class="pods-field-option-group">
											<div class="pods-pick-values pods-pick-checkbox pods-zebra">
												<p>
													<a href="#toggle" class="button pods-wizard-toggle-all" data-toggle="<?php echo esc_attr( $data_name ); ?>"><?php _e( 'Toggle all on / off', 'pods' ); ?></a>
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
								<?php
							}//end if

							if ( ! empty( $pod_helpers ) ) {
								$data      = $pod_helpers;
								$data_name = 'helpers';
								?>
								<div class="stuffbox pods-package-import-group">
									<h3>
										<label for="link_name"><?php _e( 'Choose which Pod Helpers to export', 'pods' ); ?></label>
									</h3>

									<div class="inside pods-manage-field pods-dependency">
										<div class="pods-field-option-group">
											<div class="pods-pick-values pods-pick-checkbox pods-zebra">
												<p>
													<a href="#toggle" class="button pods-wizard-toggle-all" data-toggle="<?php echo esc_attr( $data_name ); ?>"><?php _e( 'Toggle all on / off', 'pods' ); ?></a>
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
								<?php
							}//end if

							do_action( 'pods_packages_export_options', $pods, $pod_templates, $pod_pages, $pod_helpers );
							?>
						</div>

						<span id="import-export"></span>

						<div class="stuffbox hidden" id="pods-import-results">
							<h3><?php _e( 'Imported Package', 'pods' ); ?></h3>

							<div class="inside pods-manage-field pods-dependency">
								<div class="pods-wizard-results"></div>
							</div>
						</div>

						<div class="stuffbox hidden" id="pods-export-results">
							<h3><?php _e( 'Exported Package', 'pods' ); ?></h3>

							<div class="inside pods-manage-field pods-dependency">
								<p>
									<button id="pods-wizard-export-download" class="button button-secondary"><?php esc_html_e( 'Download pods-package.json', 'pods' ); ?></button>
									<button id="pods-wizard-export-copy" class="button button-secondary"><?php esc_html_e( 'Copy the Package JSON', 'pods' ); ?></button>
								</p>

								<div class="pods-wizard-results"></div>
							</div>
						</div>
					</div>

					<div id="pods-wizard-actions" class="pods-wizard-button-interface">
						<div id="pods-wizard-toolbar">
							<button id="pods-wizard-start" class="button button-secondary hidden"><?php esc_html_e( 'Start Over', 'pods' ); ?></button>
							<button id="pods-wizard-next" class="button button-primary hidden" data-again="<?php esc_attr_e( 'Process Again', 'pods' ); ?>" data-next="<?php esc_attr_e( 'Continue', 'pods' ); ?>" data-finished="<?php esc_attr_e( 'Finished', 'pods' ); ?>" data-processing="<?php esc_attr_e( 'Processing', 'pods' ); ?>.."><?php esc_html_e( 'Continue', 'pods' ); ?></button>
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
	const $pods_admin_package_import_export  = jQuery( '#pods-form-ui-import-export' );
	const $pods_admin_package_import_results = jQuery( '#pods-import-results' );
	const $pods_admin_package_export_results = jQuery( '#pods-export-results' );

	var pods_admin_wizard_callback = function ( step, completed ) {
		if ( 2 == step || !step ) {
			$pods_admin_package_import_results.slideUp( 'fast', function () {
				jQuery( 'div.pods-wizard-results', $pods_admin_package_import_results ).html( '' );
			} );

			$pods_admin_package_export_results.slideUp( 'fast', function () {
				jQuery( 'div.pods-wizard-results', $pods_admin_package_export_results ).html( '' );
			} );
		}

		return true;
	};

	var pods_admin_submit_callback = function ( id ) {
		jQuery( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
		jQuery( '#pods-wizard-next' ).prop( 'disabled', false );
		jQuery( '#pods-wizard-next' ).text( jQuery( '#pods-wizard-next' ).data( 'again' ) );

		window.location.hash = 'import-export';

		if ( 'export' === $pods_admin_package_import_export.val() ) {
			jQuery( 'div.pods-wizard-results', $pods_admin_package_export_results ).html( id );
			$pods_admin_package_export_results.slideDown( 'fast' );
		} else {
			jQuery( 'div.pods-wizard-results', $pods_admin_package_import_results ).html( id );
			$pods_admin_package_import_results.slideDown( 'fast' );
		}

		return false;
	};

	var pods_admin_option_select_callback = function ( $opt ) {
		$pods_admin_package_import_export.val( $opt.data( 'opt' ) );
		jQuery( '#pods-wizard-next' ).show().removeClass( 'hidden' );
	};

	var pods_admin_wizard_startover_callback = function () {
		$pods_admin_package_import_results.hide();
		jQuery( 'div.pods-wizard-results', $pods_admin_package_import_results ).html( '' );

		$pods_admin_package_export_results.hide();
		jQuery( 'div.pods-wizard-results', $pods_admin_package_export_results ).html( '' );
	};

	const $pods_admin_package_import_package_code = jQuery( '#pods-form-ui-import-package' );
	const $pods_admin_package_import_package_file = jQuery( '#pods-form-ui-import-package-file' );

	let pods_admin_submit_validation = function ( valid_form ) {
		if ( ! valid_form || 'import' !== $pods_admin_package_import_export.val() ) {
			return valid_form;
		}

		// Check for at least one of these.
		return (
			'' !== $pods_admin_package_import_package_code.val()
			|| '' !== $pods_admin_package_import_package_file.val()
		);
	};

	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'wizard' );
		$( document ).Pods( 'dependency' );
		$( document ).Pods( 'advanced' );
		$( document ).Pods( 'confirm' );
		$( document ).Pods( 'sluggable' );

		const toggle_all = {
			all: true
		};

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

		const $import_package_reset = $( '#pods-form-ui-import-package-file-reset' );
		const $import_package_code_parent = $pods_admin_package_import_package_code.parent();

		$pods_admin_package_import_package_file.on( 'change', function( e ) {
			if ( ! e.target.files[0] ) {
				$pods_admin_package_import_package_code.prop( 'readonly', false );
				$pods_admin_package_import_package_code.prop( 'disabled', false );
				$import_package_reset.prop( 'aria-hidden', true );
				$import_package_reset.addClass( 'hidden' );
				$import_package_code_parent.show();

				return;
			}

			$pods_admin_package_import_package_code.val( '' );
			$pods_admin_package_import_package_code.prop( 'readonly', true );
			$pods_admin_package_import_package_code.prop( 'disabled', true );
			$import_package_reset.prop( 'aria-hidden', false );
			$import_package_reset.removeClass( 'hidden' );
			$import_package_code_parent.hide();
		} );

		$import_package_reset.on( 'click', function() {
			$pods_admin_package_import_package_file.val( '' );
			$pods_admin_package_import_package_file.change();
		} );

		const $export_results = $( 'div.pods-wizard-results', $pods_admin_package_export_results );

		$( '#pods-wizard-export-copy' ).on( 'click', function( e ) {
			e.preventDefault();

			const $export_textarea = $( 'textarea', $export_results );
			const packageData = $export_textarea;

			$export_textarea.select();

			document.execCommand( 'copy' );
		} );

		$( '#pods-wizard-export-download' ).on( 'click', function( e ) {
			e.preventDefault();

			const packageData = $( 'textarea', $export_results ).val();
			const fileName = 'pods-package-' + new Date().toISOString().split( 'T' )[0] + '.json';
			const fileType = 'application/json;charset=utf-8';
			const fileContent = new Blob( [ packageData ], { type: fileType } );

			try {
				saveAs( fileContent, fileName );
			} catch( e ) {
				window.open( 'data:' + fileType + ',' + encodeURIComponent( fileContent ), '_blank', '' );
			}
		} );
	} );
</script>
