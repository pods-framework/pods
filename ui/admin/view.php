<?php
wp_enqueue_script( 'pods' );
wp_enqueue_style( 'pods-form' );

/**
 * @var array $fields
 * @var PodsUI $obj
 */
if ( empty( $fields ) || !is_array( $fields ) ) {
	$fields = $obj->pod->fields;
}

// unset fields
foreach ( $fields as $k => $field ) {
	if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) ) {
		unset( $fields[ $k ] );
	}
	elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $pod->id() ) ) {
		if ( pods_v_sanitized( 'hidden', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		}
		elseif ( pods_v_sanitized( 'read_only', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		}
		else {
			unset( $fields[ $k ] );
		}
	}
	elseif ( !pods_has_permissions( $field[ 'options' ] ) ) {
		if ( pods_v_sanitized( 'hidden', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'type' ] = 'hidden';
		}
		elseif ( pods_v_sanitized( 'read_only', $field[ 'options' ], false ) ) {
			$fields[ $k ][ 'readonly' ] = true;
		}
	}
}

if ( !isset( $label ) ) {
	$label = __( 'Save', 'pods' );
}
?>

<div class="pods-submittable-fields">
<?php
foreach ( $fields as $field ) {
	if ( 'hidden' != $field[ 'type' ] )
		continue;
}
?>
<div id="poststuff" class="metabox-holder has-right-sidebar"> <!-- class "has-right-sidebar" preps for a sidebar... always present? -->
<div id="side-info-column" class="inner-sidebar">
	<div id="side-sortables" class="meta-box-sortables ui-sortable">
		<!-- BEGIN PUBLISH DIV -->
		<div id="submitdiv" class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class="hndle"><span><?php _e( 'Manage', 'pods' ); ?></span></h3>

			<div class="inside">
				<div class="submitbox" id="submitpost">
					<?php
					if ( 0 < $pod->id() && ( isset( $pod->pod_data[ 'fields' ][ 'created' ] ) || isset( $pod->pod_data[ 'fields' ][ 'modified' ] ) || 0 < strlen( pods_v_sanitized( 'detail_url', $pod->pod_data[ 'options' ] ) ) ) ) {
						?>
						<div id="minor-publishing">
							<?php
							if ( 0 < strlen( pods_v_sanitized( 'detail_url', $pod->pod_data[ 'options' ] ) ) ) {
								?>
								<div id="minor-publishing-actions">
									<div id="preview-action">
										<a class="button" href="<?php echo $pod->field( 'detail_url' ); ?>" target="_blank"><?php echo sprintf( __( 'View %s', 'pods' ), $obj->item ); ?></a>
									</div>
									<div class="clear"></div>
								</div>
							<?php
							}

							if ( isset( $pod->pod_data[ 'fields' ][ 'created' ] ) || isset( $pod->pod_data[ 'fields' ][ 'modified' ] ) ) {
								?>
								<div id="misc-publishing-actions">
									<?php
									$datef = __( 'M j, Y @ G:i' );

									if ( isset( $pod->pod_data[ 'fields' ][ 'created' ] ) ) {
										$date = date_i18n( $datef, strtotime( $pod->field( 'created' ) ) );
										?>
										<div class="misc-pub-section curtime">
											<span id="timestamp"><?php _e( 'Created on', 'pods' ); ?>: <b><?php echo $date; ?></b></span>
										</div>
									<?php
									}

									if ( isset( $pod->pod_data[ 'fields' ][ 'modified' ] ) && $pod->display( 'created' ) != $pod->display( 'modified' ) ) {
										$date = date_i18n( $datef, strtotime( $pod->field( 'modified' ) ) );
										?>
										<div class="misc-pub-section curtime">
											<span id="timestamp"><?php _e( 'Last Modified', 'pods' ); ?>: <b><?php echo $date; ?></b></span>
										</div>
									<?php
									}
									?>
								</div>
							<?php
							}
							?>
						</div>
						<!-- /#minor-publishing -->
					<?php
					}
					?>

					<div id="major-publishing-actions">
						<?php
						if ( pods_is_admin( array( 'pods', 'pods_delete_' . $pod->pod ) ) && null !== $pod->id() && !$duplicate && !in_array( 'delete', $obj->actions_disabled ) && !in_array( 'delete', $obj->actions_hidden ) ) {
							?>
							<div id="delete-action">
								<a class="submitdelete deletion" href="<?php echo pods_query_arg( array( 'action' => 'delete' ) ) ?>" onclick="return confirm('You are about to permanently delete this item\n Choose \'Cancel\' to stop, \'OK\' to delete.');"><?php _e( 'Delete', 'pods' ); ?></a>
							</div>
							<!-- /#delete-action -->
						<?php } ?>

						<div id="publishing-action">
							<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
							<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php echo esc_attr( $label ); ?>" accesskey="p" />
						</div>
						<!-- /#publishing-action -->

						<div class="clear"></div>
					</div>
					<!-- /#major-publishing-actions -->
				</div>
				<!-- /#submitpost -->
			</div>
			<!-- /.inside -->
		</div>
		<!-- /#submitdiv --><!-- END PUBLISH DIV --><!-- TODO: minor column fields -->
		<?php
		if ( pods_v( 'action' ) == 'edit' && !$duplicate && !in_array( 'navigate', $obj->actions_disabled ) && !in_array( 'navigate', $obj->actions_hidden ) ) {
			if ( !isset( $singular_label ) )
				$singular_label = ucwords( str_replace( '_', ' ', $pod->pod_data[ 'name' ] ) );

			$singular_label = pods_v( 'label', $pod->pod_data[ 'options' ], $singular_label, true );
			$singular_label = pods_v( 'label_singular', $pod->pod_data[ 'options' ], $singular_label, true );

			$prev = $pod->prev_id();
			$next = $pod->next_id();

			if ( 0 < $prev || 0 < $next ) {
				?>
				<div id="navigatediv" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e( 'Navigate', 'pods' ); ?></span></h3>

					<div class="inside">
						<div class="pods-admin" id="navigatebox">
							<div id="navigation-actions">
								<?php
								if ( 0 < $prev ) {
									?>
									<a class="previous-item" href="<?php echo pods_query_arg( array( 'id' => $prev ), null, 'do' ); ?>">
										<span>&laquo;</span>
										<?php echo sprintf( __( 'Previous %s', 'pods' ), $singular_label ); ?>
									</a>
								<?php
								}

								if ( 0 < $next ) {
									?>
									<a class="next-item" href="<?php echo pods_query_arg( array( 'id' => $next ), null, 'do' ); ?>">
										<?php echo sprintf( __( 'Next %s', 'pods' ), $singular_label ); ?>
										<span>&raquo;</span>
									</a>
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
				</div> <!-- /#navigatediv -->
			<?php
			}
		}
		?>
	</div>
	<!-- /#side-sortables -->
</div>
<!-- /#side-info-column -->

<div id="post-body">
	<div id="post-body-content">
		<?php
		$more = false;

		if ( $pod->pod_data[ 'field_index' ] != $pod->pod_data[ 'field_id' ] ) {
			foreach ( $fields as $k => $field ) {
				if ( $pod->pod_data[ 'field_index' ] != $field[ 'name' ] || 'text' != $field[ 'type' ] )
					continue;

				$more = true;
				$extra = '';

				$max_length = (int) pods_v_sanitized( 'maxlength', $field[ 'options' ], pods_v_sanitized( $field[ 'type' ] . '_max_length', $field[ 'options' ], 0 ), true );

				if ( 0 < $max_length )
					$extra .= ' maxlength="' . $max_length . '"';
				?>
				<div id="titlediv">
					<div id="titlewrap">
						<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php echo apply_filters( 'pods_enter_name_here', __( 'Enter name here', 'pods' ), $pod, $fields ); ?></label>
						<input type="text" name="pods_field_<?php echo $pod->pod_data[ 'field_index' ]; ?>" data-name-clean="pods-field-<?php echo $pod->pod_data[ 'field_index' ]; ?>" id="title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $pod->index() ) ); ?>" class="pods-form-ui-field-name-pods-field-<?php echo $pod->pod_data[ 'field_index' ]; ?>" autocomplete="off"<?php echo $extra; ?> />
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
				unset( $fields[ $k ] );
			}
		}

		if ( 0 < count( $fields ) ) {
			?>

			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				<div id="pods-meta-box" class="postbox" style="">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle">
                                <span>
                                    <?php
									if ( $more )
										$title = __( 'More Fields', 'pods' );
									else
										$title = __( 'Fields', 'pods' );

									echo apply_filters( 'pods_meta_default_box_title', $title, $pod, $fields );
									?>
                                </span>
					</h3>

					<div class="inside">
						<table class="form-table pods-metabox">
							<?php
							foreach ( $fields as $field ) {
								if ( 'hidden' == $field[ 'type' ] )
									continue;
								?>
								<tr class="form-field pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Podsform::clean( $field[ 'name' ], true ); ?>">
									<th scope="row" valign="top"><?php echo PodsForm::label( 'pods_field_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?></th>
									<td>
										<?php echo PodsForm::field( 'pods_field_' . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), $field[ 'type' ], $field, $pod, $pod->id() ); ?>
										<?php echo PodsForm::comment( 'pods_field_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
									</td>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
					<!-- /.inside -->
				</div>
				<!-- /#pods-meta-box -->
			</div>
			<!-- /#normal-sortables -->

		<?php } ?>

		<!--<div id="advanced-sortables" class="meta-box-sortables ui-sortable">
		</div>
		 /#advanced-sortables -->

	</div>
	<!-- /#post-body-content -->

	<br class="clear" />
</div>
<!-- /#post-body -->

<br class="clear" />
</div>
<!-- /#poststuff -->
</div>
<!-- /#pods-record -->
