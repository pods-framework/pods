<?php
/**
 * @var array         $fields
 * @var Pods          $pod
 * @var mixed         $id
 * @var string        $field_prefix
 * @var string        $field_row_classes
 * @var callable|null $value_callback
 * @var callable|null $pre_callback
 * @var callable|null $post_callback
 * @var string        $th_scope
 * @var PodsUI        $obj
 */

/**
 * Action that runs before the meta boxes for an Advanced Content Type
 *
 * Occurs at the top of #poststuff
 *
 * @param Pods   $pod Current Pods object.
 * @param PodsUI $obj Current PodsUI object.
 *
 * @since 2.5.0
 */
do_action( 'pods_meta_box_pre', $pod, $obj );
?>
<div id="poststuff" class="poststuff metabox-holder has-right-sidebar"> <!-- class "has-right-sidebar" preps for a sidebar... always present? -->
	<div id="side-info-column" class="inner-sidebar">
		<?php
		/**
		 * Action that runs before the sidebar of the editor for an Advanced Content Type
		 *
		 * Occurs at the top of #side-info-column
		 *
		 * @param Pods   $pod Current Pods object.
		 * @param PodsUI $obj Current PodsUI object.
		 *
		 * @since 2.4.1
		 */
		do_action( 'pods_act_editor_before_sidebar', $pod, $obj );
		?>
		<div id="side-sortables" class="meta-box-sortables ui-sortable">
			<!-- BEGIN PUBLISH DIV -->
			<div id="submitdiv" class="postbox">
				<?php PodsForm::render_postbox_header( pods_v( 'label_manage', $pod_options, __( 'Manage', 'pods' ) ) ); ?>

				<div class="inside">
					<div class="submitbox" id="submitpost">
						<?php
						if ( 0 < $pod->id() && ( isset( $pod->pod_data['fields']['created'] ) || isset( $pod->pod_data['fields']['modified'] ) || 0 < strlen( (string) pods_v_sanitized( 'detail_url', $pod_options ) ) ) ) {
							?>
							<div id="minor-publishing">
								<?php
								if ( 0 < strlen( (string) pods_v_sanitized( 'detail_url', $pod_options ) ) ) {
									?>
									<div id="minor-publishing-actions">
										<div id="preview-action">
											<a class="button" href="<?php echo esc_url( $pod->field( 'detail_url' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo sprintf( __( 'View %s', 'pods' ), $obj->item ); ?></a>
										</div>
										<div class="clear"></div>
									</div>
									<?php
								}

								if ( isset( $pod->pod_data['fields']['created'] ) || isset( $pod->pod_data['fields']['modified'] ) ) {
									?>
									<div id="misc-publishing-actions">
										<?php
										$datef = __( 'M j, Y @ G:i' );

										if ( isset( $pod->pod_data['fields']['created'] ) ) {
											$date = date_i18n( $datef, strtotime( $pod->field( 'created' ) ) );
											?>
											<div class="misc-pub-section curtime">
												<span id="timestamp"><?php _e( 'Created on', 'pods' ); ?>: <b><?php echo $date; ?></b></span>
											</div>
											<?php
										}

										if ( isset( $pod->pod_data['fields']['modified'] ) && $pod->display( 'created' ) != $pod->display( 'modified' ) ) {
											$date = date_i18n( $datef, strtotime( $pod->field( 'modified' ) ) );
											?>
											<div class="misc-pub-section curtime">
												<span id="timestamp"><?php _e( 'Last Modified', 'pods' ); ?>: <b><?php echo $date; ?></b></span>
											</div>
											<?php
										}
										?>

										<?php
										/**
										 * Action that runs after the misc publish actions area for an Advanced Content Type
										 *
										 * Occurs at the end of #misc-publishing-actions
										 *
										 * @param Pods   $pod Current Pods object.
										 * @param PodsUI $obj Current PodsUI object.
										 *
										 * @since 2.5.0
										 */
										do_action( 'pods_ui_form_misc_pub_actions', $pod, $obj );
										?>
									</div>
									<?php
								}//end if
								?>
							</div>
							<!-- /#minor-publishing -->
							<?php
						}//end if
						?>

						<div id="major-publishing-actions">
							<?php
							if ( pods_is_admin(
								array(
									'pods',
									'pods_delete_' . $pod->pod,
								)
							) && null !== $pod->id() && ! $duplicate && ! in_array( 'delete', (array) $obj->actions_disabled, true ) && ! in_array( 'delete', (array) $obj->actions_hidden, true ) ) {
								$link = pods_query_arg(
									array(
										'action'   => 'delete',
										'_wpnonce' => wp_create_nonce( 'pods-ui-action-delete' ),
									)
								);
								?>
								<div id="delete-action">
									<a class="submitdelete deletion" href="<?php echo esc_url( $link ); ?>" onclick="return confirm('<?php _e( 'You are about to permanently delete this item. Choose CANCEL to stop, OK to delete.', 'pods' ); ?>');"><?php _e( 'Delete', 'pods' ); ?></a>
								</div>
								<!-- /#delete-action -->
							<?php } ?>

							<div id="publishing-action">
								<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
								<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php echo esc_attr( $label ); ?>" accesskey="p" />
								<?php
								/**
								 * Action that runs after the publish button for an Advanced Content Type
								 *
								 * Occurs at the end of #publishing-action
								 *
								 * @param Pods   $pod Current Pods object.
								 * @param PodsUI $obj Current PodsUI object.
								 *
								 * @since 2.5.0
								 */
								do_action( 'pods_ui_form_submit_area', $pod, $obj );
								?>
							</div>
							<!-- /#publishing-action -->

							<div class="clear"></div>
						</div>

						<?php
						/**
						 * Action that runs after the publish area for an Advanced Content Type
						 *
						 * Occurs at the end of #submitpost
						 *
						 * @param Pods   $pod Current Pods object.
						 * @param PodsUI $obj Current PodsUI object.
						 *
						 * @since 2.5.0
						 */
						do_action( 'pods_ui_form_publish_area', $pod, $obj );
						?>
						<!-- /#major-publishing-actions -->
					</div>
					<!-- /#submitpost -->
				</div>
				<!-- /.inside -->
			</div>
			<!-- /#submitdiv --><!-- END PUBLISH DIV --><!-- TODO: minor column fields -->
			<?php
			if ( pods_v( 'action' ) == 'edit' && ! $duplicate && ! in_array( 'navigate', (array) $obj->actions_disabled, true ) && ! in_array( 'navigate', (array) $obj->actions_hidden, true ) ) {
				if ( ! isset( $singular_label ) ) {
					$singular_label = ucwords( str_replace( '_', ' ', $pod->pod_data['name'] ) );
				}

				$singular_label = pods_v( 'label', $pod_options, $singular_label, null, true );
				$singular_label = pods_v( 'label_singular', $pod_options, $singular_label, null, true );

				$pod->params = $obj->get_params( null, 'manage' );

				$prev_next = apply_filters( 'pods_ui_prev_next_ids', array(), $pod, $obj );

				if ( empty( $prev_next ) ) {
					$prev_next = array(
						'prev' => $pod->prev_id(),
						'next' => $pod->next_id(),
					);
				}

				$prev = $prev_next['prev'];
				$next = $prev_next['next'];

				if ( 0 < $prev || 0 < $next ) {
					?>
					<div id="navigatediv" class="postbox">
						<?php
						/**
						 * Action that runs before the post navagiation in the editor for an Advanced Content Type
						 *
						 * Occurs at the top of #navigatediv
						 *
						 * @param Pods   $pod Current Pods object.
						 * @param PodsUI $obj Current PodsUI object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_before_navigation', $pod, $obj );

						PodsForm::render_postbox_header( __( 'Navigate', 'pods' ) );
						?>
						<div class="inside">
							<div class="pods-admin" id="navigatebox">
								<div id="navigation-actions">
									<?php
									if ( 0 < $prev ) {
										?>
										<a class="previous-item" href="<?php echo esc_url( pods_query_arg( array( 'id' => $prev ), null, 'do' ) ); ?>">
											<span>&laquo;</span>
											<?php echo sprintf( __( 'Previous %s', 'pods' ), $singular_label ); ?>
										</a>
										<?php
									}

									if ( 0 < $next ) {
										?>
										<a class="next-item" href="<?php echo esc_url( pods_query_arg( array( 'id' => $next ), null, 'do' ) ); ?>">
											<?php echo sprintf( __( 'Next %s', 'pods' ), $singular_label ); ?>
											<span>&raquo;</span> </a>
										<?php
									}
									?>

									<div class="clear"></div>
								</div>
								<!-- /#navigation-actions -->
							</div>
							<!-- /#navigatebox -->
						</div>
						<!-- /.inside -->
						<?php
						/**
						 * Action that runs after the post navagiation in the editor for an Advanced Content Type
						 *
						 * Occurs at the bottom of #navigatediv
						 *
						 * @param Pods   $pod Current Pods object.
						 * @param PodsUI $obj Current PodsUI object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_after_navigation', $pod, $obj );
						?>
					</div> <!-- /#navigatediv -->
					<?php
				}//end if
			}//end if
			?>
		</div>
		<!-- /#side-sortables -->
		<?php
		/**
		 * Action that runs after the sidebar of the editor for an Advanced Content Type
		 *
		 * Occurs at the bottom of #side-info-column
		 *
		 * @param Pods   $pod Current Pods object.
		 * @param PodsUI $obj Current PodsUI object.
		 *
		 * @since 2.4.1
		 */
		do_action( 'pods_act_editor_after_sidebar', $pod, $obj );
		?>
	</div>
	<!-- /#side-info-column -->

	<div id="post-body">
		<div id="post-body-content">
			<?php
			$more = false;

			if ( $pod->pod_data['field_index'] != $pod->pod_data['field_id'] ) {
				foreach ( $group_fields as $field ) {
					if ( $pod->pod_data['field_index'] != $field['name'] || 'text' !== $field['type'] ) {
						continue;
					}

					$more  = true;
					$extra = '';

					$max_length = (int) pods_v_sanitized( 'maxlength', $field['options'], pods_v_sanitized( $field['type'] . '_max_length', $field['options'], 0 ), null, true );

					if ( 0 < $max_length ) {
						$extra .= ' maxlength="' . esc_attr( $max_length ) . '"';
					}

					/**
					 * Filter that lets you make the title field readonly
					 *
					 * @param Pods   $pod Current Pods object.
					 * @param PodsUI $obj Current PodsUI object.
					 *
					 * @since 2.5.0
					 */
					if ( pods_v( 'readonly', $field['options'], pods_v( 'readonly', $field, false ) ) || apply_filters( 'pods_ui_form_title_readonly', false, $pod, $obj ) ) {
						?>
						<div id="titlediv">
							<div id="titlewrap">
								<h3><?php echo esc_html( $pod->index() ); ?></h3>
								<input type="hidden" name="pods_field_<?php echo esc_attr( $pod->pod_data['field_index'] ); ?>" data-name-clean="pods-field-<?php echo esc_attr( $pod->pod_data['field_index'] ); ?>" id="title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $pod->index() ) ); ?>" class="pods-form-ui-field-name-pods-field-<?php echo esc_attr( $pod->pod_data['field_index'] ); ?>" autocomplete="off"<?php echo $extra; ?> />
							</div>
							<!-- /#titlewrap -->
						</div>
						<!-- /#titlediv -->
						<?php
					} else {
						?>
						<div id="titlediv">
							<?php
							/**
							 * Action that runs before the title field of the editor for an Advanced Content Type
							 *
							 * Occurs at the top of #titlediv
							 *
							 * @param Pods   $pod Current Pods object.
							 * @param PodsUI $obj Current PodsUI object.
							 *
							 * @since 2.4.1
							 */
							do_action( 'pods_act_editor_before_title', $pod, $obj );
							?>
							<div id="titlewrap">
								<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo apply_filters( 'pods_enter_name_here', __( 'Enter name here', 'pods' ), $pod, $fields ); ?></label>
								<input type="text" name="pods_field_<?php echo esc_attr( $pod->pod_data['field_index'] ); ?>" data-name-clean="pods-field-<?php echo esc_attr( $pod->pod_data['field_index'] ); ?>" id="title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $pod->index() ) ); ?>" class="pods-form-ui-field-name-pods-field-<?php echo esc_attr( $pod->pod_data['field_index'] ); ?>" autocomplete="off"<?php echo $extra; ?> />
								<?php
								/**
								 * Action that runs after the title field of the editor for an Advanced Content Type
								 *
								 * Occurs at the bottom of #titlediv
								 *
								 * @param Pods   $pod Current Pods object.
								 * @param PodsUI $obj Current PodsUI object.
								 *
								 * @since 2.4.1
								 */
								do_action( 'pods_act_editor_after_title', $pod, $obj );
								?>
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
					}//end if

					unset( $group_fields[ $field['name'] ] );
				}//end foreach
			}//end if

			if ( 0 < count( $groups ) ) {
				if ( $more && 1 == count( $groups ) ) {
					$first_group = current( $groups );

					if ( 1 == count( $first_group['fields'] ) && isset( $first_group['fields'][ $pod->pod_data['field_index'] ] ) ) {
						$groups = array();
					}
				}

				if ( 0 < count( $groups ) ) {
					?>
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<?php
						foreach ( $groups as $group ) {
							if ( empty( $group['fields'] ) ) {
								continue;
							}

							/**
							 * Action that runs before the main fields metabox in the editor for an Advanced Content Type
							 *
							 * Occurs at the top of #normal-sortables
							 *
							 * @param Pods $pod Current Pods object.
							 *
							 * @since 2.4.1
							 */
							do_action( 'pods_act_editor_before_metabox', $pod );

							if ( ! $more && 1 === count( $groups ) ) {
								$title = __( 'Fields', 'pods' );
							} else {
								$title = $group['label'];
							}

							/** This filter is documented in classes/PodsMeta.php */
							$title = apply_filters( 'pods_meta_default_box_title', $title, $pod, $fields, $pod->pod_data['type'], $pod->pod );
							?>
							<div id="pods-meta-box-<?php echo esc_attr( sanitize_title( $group['label'] ) ); ?>" class="postbox">
								<?php PodsForm::render_postbox_header( $title ); ?>

								<div class="inside">
									<?php
									if ( false === apply_filters( 'pods_meta_box_override', false, $pod, $group, $obj ) ) {
										?>
										<table class="form-table pods-metabox">
											<?php
											$fields            = [];
											$field_prefix      = 'pods_field_';
											$field_row_classes = 'form-field pods-field-input';
											$id                = $pod->id();

											foreach ( $group['fields'] as $field ) {
												if ( ! isset( $group_fields[ $field['name'] ] ) ) {
													continue;
												}

												$fields[ $field['name'] ] = $field;
											}

											pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );
											?>
										</table>
										<?php
									}//end if
									?>
								</div>
								<!-- /.inside -->
							</div>
							<!-- /#pods-meta-box -->
							<?php
						}//end foreach

						/**
						 * Action that runs after the main fields metabox in the editor for an Advanced Content Type
						 *
						 * Occurs at the bottom of #normal-sortables
						 *
						 * @param Pods   $pod Current Pods object.
						 * @param PodsUI $obj Current PodsUI object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_after_metabox', $pod, $obj );
						?>
					</div>
					<!-- /#normal-sortables -->

					<?php
				}//end if
			}//end if
			?>

			<!-- <div id="advanced-sortables" class="meta-box-sortables ui-sortable"></div> -->
			<!-- /#advanced-sortables -->

		</div>
		<!-- /#post-body-content -->

		<br class="clear" />
	</div>
	<!-- /#post-body -->

	<br class="clear" />
</div>
<!-- /#poststuff -->
