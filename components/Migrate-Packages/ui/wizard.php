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
								<h3><label for="link_name"><?php _e( 'Paste the Package Code', 'pods' ); ?></label></h3>

								<div class="inside pods-manage-field pods-dependency">
									<div class="pods-field-option">
										<?php
										echo PodsForm::field( 'import_package', pods_var_raw( 'import_package', 'post' ), 'paragraph', [
											'attributes'  => [
												'style' => 'width: 100%; max-width: 100%; height: 300px;',
											],
											'disable_dfv' => true,
										] );
										?>
									</div>
									<div class="pods-field-option">
										<label><?php esc_html_e( 'Or select a pods-package.json file to load: ', 'pods' ); ?> <input type="file" id="import_package_file" accept=".json" /></label>
									</div>
								</div>
							</div>
						</div>

						<div class="pods-wizard-option-content" id="pods-wizard-export">
							<div class="pods-wizard-content">
								<p><?php _e( 'Packages allow you to import/export your Pods, Groups, Fields, and other settings between any Pods sites.', 'pods' ); ?></p>
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

						<div class="stuffbox hidden" id="import-export-results">
							<h3><label for="link_name"><?php _e( 'Package / Results', 'pods' ); ?></label></h3>

							<div class="inside pods-manage-field pods-dependency">
							</div>

							<p align="right">
								<button id="pods-wizard-export-copy" class="button button-secondary hidden"><?php esc_html_e( 'Copy the Package JSON', 'pods' ); ?></button>
								<button id="pods-wizard-export-download" class="button button-secondary hidden"><?php esc_html_e( 'Download pods-package.json', 'pods' ); ?></button>
							</p>
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
	var pods_admin_wizard_callback = function ( step, completed ) {
		console.log( step );
		console.log( completed );

		if ( 2 == step || !step ) {
			jQuery( '#pods-wizard-panel-2 div#import-export-results' ).slideUp( 'fast', function () {
				jQuery( '#pods-wizard-panel-2 div#import-export-results div.inside' ).html( '' );
			} );
		}

		return true;
	};

	var pods_admin_submit_callback = function ( id ) {
		jQuery( '#pods-wizard-panel-2 div#import-export-results div.inside' ).html( id );
		jQuery( '#pods-wizard-panel-2 div#import-export-results' ).slideDown( 'fast' );

		jQuery( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
		jQuery( '#pods-wizard-next' ).prop( 'disabled', false );
		jQuery( '#pods-wizard-next' ).text( jQuery( '#pods-wizard-next' ).data( 'again' ) );

		window.location.hash = 'import-export';

		if ( 'export' === jQuery( '#pods-form-ui-import-export' ).val() ) {
			jQuery( '#pods-wizard-export-copy' ).show().removeClass( 'hidden' );
			jQuery( '#pods-wizard-export-download' ).show().removeClass( 'hidden' );
		} else {
			jQuery( '#pods-wizard-export-copy' ).hide().addClass( 'hidden' );
			jQuery( '#pods-wizard-export-download' ).hide().addClass( 'hidden' );
		}

		return false;
	};

	var pods_admin_option_select_callback = function ( $opt ) {
		jQuery( '#pods-form-ui-import-export' ).val( $opt.data( 'opt' ) );
		jQuery( '#pods-wizard-next' ).show().removeClass( 'hidden' );
	};

	var pods_admin_wizard_startover_callback = function () {
		jQuery( '#pods-wizard-panel-2 div#import-export-results' ).hide();
		jQuery( '#pods-wizard-panel-2 div#import-export-results div.inside' ).html( '' );
	};

	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'wizard' );
		$( document ).Pods( 'dependency' );
		$( document ).Pods( 'advanced' );
		$( document ).Pods( 'confirm' );
		$( document ).Pods( 'sluggable' );

		var toggle_all = {};

		$( '.pods-wizard-toggle-all' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( 'undefined' == typeof toggle_all[$( this ).data( 'toggle' )] ) {
				toggle_all[$( this ).data( 'toggle' )] = true;
			}

			$( this ).closest( '.pods-field-option-group' ).find( '.pods-field.pods-boolean input[type="checkbox"]' ).prop( 'checked', (!toggle_all[$( this ).data( 'toggle' )]) );

			toggle_all[$( this ).data( 'toggle' )] = (!toggle_all[$( this ).data( 'toggle' )]);
		} );

		$( '#import_package_file' ).on( 'change', function( e ) {
			if ( ! e.target.files[0] ) {
				return;
			}

			const reader = new FileReader();

			reader.onload = function( reader_event ) {
				const fileContents = reader_event.target.result;

				$( '#pods-form-ui-import-package' ).val( fileContents );
			};

			reader.readAsText( e.target.files[0] );

			$( this ).val( '' );
		} );

		$( '#pods-wizard-export-copy' ).on( 'click', function( e ) {
			e.preventDefault();

			const packageData = jQuery( '#pods-wizard-panel-2 div#import-export-results div.inside textarea' );

			$( '#pods-wizard-panel-2 div#import-export-results div.inside textarea' ).select();

			document.execCommand( 'copy' );
		} );

		$( '#pods-wizard-export-download' ).on( 'click', function( e ) {
			e.preventDefault();

			const packageData = jQuery( '#pods-wizard-panel-2 div#import-export-results div.inside textarea' ).val();
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
