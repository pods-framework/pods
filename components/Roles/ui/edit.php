<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>

	<form action="" method="post" class="pods-submittable pods-form">
		<div class="pods-submittable-fields">
			<?php echo PodsForm::field( 'action', 'pods_admin_components', 'hidden' ); ?>
			<?php echo PodsForm::field( 'component', $component, 'hidden' ); ?>
			<?php echo PodsForm::field( 'method', $method, 'hidden' ); ?>
			<?php echo PodsForm::field( 'id', $id, 'hidden' ); ?>
			<?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-component-' . $component . '-' . $method ), 'hidden' ); ?>

			<h2 class="italicized"><?php _e( 'Roles &amp; Capabilities: Edit Role', 'pods' ); ?></h2>

			<?php
			if ( isset( $_GET['do'] ) ) {
				$action = __( 'saved', 'pods' );

				if ( 'create' == pods_var( 'do', 'get', 'save' ) ) {
					$action = __( 'created', 'pods' );
				}

				$message = sprintf( __( '<strong>Success!</strong> %1$s %2$s successfully.', 'pods' ), $obj->item, $action );

				echo $obj->message( $message );
			}
			?>

			<p><?php _e( 'Choose below which Capabilities you would like this existing user role to have.', 'pods' ); ?></p>

			<div id="poststuff" class="poststuff">
				<div id="post-body" class="metabox-holder columns-2">

					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<!-- BEGIN PUBLISH DIV -->
							<div id="submitdiv" class="postbox">
								<div class="handlediv" title="Click to toggle"><br /></div>
								<h3 class="hndle"><span><?php _e( 'Manage', 'pods' ); ?></span></h3>

								<div class="inside">
									<div class="submitbox" id="submitpost">
										<div id="minor-publishing">
											<div id="major-publishing-actions">
												<div id="publishing-action">
													<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
													<input type="submit" name="publish" id="publish" class="button-primary" value="<?php _e( 'Save', 'pods' ); ?>" accesskey="p" />
												</div>
												<!-- /#publishing-action -->

												<div class="clear"></div>
											</div>
											<!-- /#major-publishing-actions -->
										</div>
										<!-- /#minor-publishing -->
									</div>
									<!-- /#submitpost -->
								</div>
								<!-- /.inside -->
							</div>
							<!-- /#submitdiv --><!-- END PUBLISH DIV --><!-- TODO: minor column fields -->
						</div>
						<!-- /#side-sortables -->
					</div>
					<!-- /#side-info-column -->

					<div id="postbox-container-2" class="postbox-container">
						<div id="post-body-content">
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
								<div id="pods-meta-box" class="postbox" style="">
									<div class="handlediv" title="Click to toggle"><br /></div>
									<h3 class="hndle">
										<span>
											<?php _e( 'Assign the Capabilities for', 'pods' ); ?>
											<strong><?php echo $role_label; ?></strong>
										</span>
									</h3>

									<div class="inside pods-manage-field pods-dependency">
										<div class="pods-field-option-group">
											<div class="pods-pick-values pods-pick-checkbox pods-zebra">
												<p>
													<a href="#toggle" class="button" id="toggle-all"><?php _e( 'Toggle All Capabilities on / off', 'pods' ); ?></a>
												</p>

												<ul>
													<?php
													$zebra = false;

													foreach ( $capabilities as $capability ) {
														$checked = false;

														if ( true === (boolean) pods_var( $capability, $role_capabilities, false ) ) {
															$checked = true;
														}

														$class = ( $zebra ? 'even' : 'odd' );

														$zebra = ( ! $zebra );
														?>
														<li class="pods-zebra-<?php echo esc_attr( $class ); ?>" data-capability="<?php echo esc_attr( $capability ); ?>">
															<?php
															echo PodsForm::field( 'capabilities[' . $capability . ']', pods_var_raw( 'capabilities[' . $capability . ']', 'post', $checked ), 'boolean', [
																'boolean_yes_label' => $capability,
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

										<div class="pods-field-option-group">
											<p class="pods-field-option-group-label">
												<?php
												echo PodsForm::label( 'custom_capabilities[0]', __( 'Custom Capabilities', 'pods' ), __( 'These capabilities will automatically be created and assigned to this role', 'pods' ) );
												?>
											</p>

											<div class="pods-pick-values pods-pick-checkbox">
												<ul id="custom-capabilities">
													<li class="pods-repeater hidden">
														<?php
														echo PodsForm::field( 'custom_capabilities[--1]', '', 'text', [
															'disable_dfv' => true,
														] );
														?>
													</li>
													<li>
														<?php
														echo PodsForm::field( 'custom_capabilities[0]', '', 'text', [
															'disable_dfv' => true,
														] );
														?>
													</li>
												</ul>

												<p>
													<a href="#add-capability" id="add-capability" class="button"><?php _e( 'Add Another Custom Capability', 'pods' ); ?></a>
												</p>
											</div>
										</div>
									</div>
									<!-- /.inside -->
								</div>
								<!-- /#pods-meta-box -->
							</div>
							<!-- /#normal-sortables -->

							<!-- <div id="advanced-sortables" class="meta-box-sortables ui-sortable"></div> -->
							<!-- /#advanced-sortables -->

						</div>
						<!-- /#post-body-content -->

						<br class="clear" />
					</div>
					<!-- /#post-body -->

					<br class="clear" />

				</div>
			</div>
			<!-- /#poststuff -->
		</div>
	</form>
	<!-- /#pods-record -->
</div>

<script type="text/javascript">
	var pods_admin_submit_callback = function ( id ) {
		id = parseInt( id );
		document.location = 'admin.php?page=pods-component-<?php echo esc_js( $component ); ?>&action=edit&id=<?php echo esc_js( $id ); ?>&do=save';
	};

	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'wizard' );
		$( document ).Pods( 'dependency' );
		$( document ).Pods( 'advanced' );
		$( document ).Pods( 'confirm' );
		$( document ).Pods( 'sluggable' );

		var toggle_all = true;

		$( '#toggle-all' ).on( 'click', function ( e ) {
			e.preventDefault();

			$( '.pods-field.pods-boolean input[type="checkbox"]' ).prop( 'checked', toggle_all );

			toggle_all = (!toggle_all);
		} );

		$( '#add-capability' ).on( 'click', function ( e ) {
			e.preventDefault();

			var new_id = $( 'ul#custom-capabilities li' ).length;
			var html = $( 'ul#custom-capabilities li.pods-repeater' ).html().replace( /\-\-1/g, new_id );

			$( 'ul#custom-capabilities' ).append( '<li id="capability-' + new_id + '">' + html + '</li>' );
			$( 'li#capability-' + new_id + ' input' ).focus();
		} );
	} );
</script>
