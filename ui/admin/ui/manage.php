<?php
$ui->screen_meta();

wp_enqueue_script( 'jquery' );

if ( true === $reorder ) {
wp_enqueue_script( 'jquery-ui-sortable' );
}

$icon_style = '';
if ( false !== $ui->icon ) {
$icon_style = ' style="background-position:0 0;background-size:100%;background-image:url(' . esc_url( $ui->icon ) . ');"';
}

/**
* Allow adding custom CSS classes to the Pods::manage() container.
*
* @since 2.6.8
*
* @param array  $custom_container_classes List of custom classes to use.
* @param PodsUI $ui                     PodsUI instance.
*/
$custom_container_classes = apply_filters( 'pods_ui_manage_custom_container_classes', array() );

if ( is_admin() ) {
array_unshift( $custom_container_classes, 'wrap' );
}

array_unshift( $custom_container_classes, 'pods-admin' );
array_unshift( $custom_container_classes, 'pods-ui' );

$custom_container_classes = array_map( 'sanitize_html_class', $custom_container_classes );
$custom_container_classes = implode( ' ', $custom_container_classes );
?>
<div class="<?php echo esc_attr( $custom_container_classes ); ?>">
	<div class="pods-admin-container">
		<?php if ( ! in_array( 'manage_header', $ui->actions_disabled, true ) && ! in_array( 'manage_header', $ui->actions_hidden, true ) ) : ?>
		<div id="icon-edit-pages" class="icon32"<?php echo $icon_style; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>>
			<br />
		</div>
		<h2>
			<?php
			if ( true === $reorder ) {
				echo wp_kses_post( $ui->header['reorder'] );

				if ( ! in_array( 'manage', $ui->actions_disabled ) && ! in_array( 'manage', $ui->actions_hidden ) && ! $ui->restricted( 'manage' ) ) {
					$link = pods_query_arg(
						array(
							$ui->num_prefix . 'action' . $ui->num => 'manage',
							$ui->num_prefix . 'id' . $ui->num     => '',
						),
						$ui::$allowed, $ui->exclusion()
					);

					if ( ! empty( $ui->action_links['manage'] ) ) {
						$link = $ui->action_links['manage'];
					}
					?>
					<small>(<a href="<?php echo esc_url( $link ); ?>">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $ui->heading['manage'] ); ?></a>)</small>
					<?php
				}
			} else {
				echo wp_kses_post( $ui->header['manage'] );
			}

			if ( ! in_array( 'add', $ui->actions_disabled ) && ! in_array( 'add', $ui->actions_hidden ) && ! $ui->restricted( 'add' ) ) {
				$link = pods_query_arg(
					array(
						$ui->num_prefix . 'action' . $ui->num => 'add',
						$ui->num_prefix . 'id' . $ui->num     => '',
						$ui->num_prefix . 'do' . $ui->num     => '',
					),
					$ui::$allowed, $ui->exclusion()
				);

				if ( ! empty( $ui->action_links['add'] ) ) {
					$link = $ui->action_links['add'];
				}
				?>
				<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo wp_kses_post( $ui->label['add_new'] ); ?></a>
				<?php
			}
			if ( ! in_array( 'reorder', $ui->actions_disabled ) && ! in_array( 'reorder', $ui->actions_hidden ) && false !== $ui->reorder['on'] && ! $ui->restricted( 'reorder' ) ) {
				$link = pods_query_arg( array( $ui->num_prefix . 'action' . $ui->num => 'reorder' ), $ui::$allowed, $ui->exclusion() );

				if ( ! empty( $ui->action_links['reorder'] ) ) {
					$link = $ui->action_links['reorder'];
				}
				?>
				<a href="<?php echo esc_url( $link ); ?>" class="add-new-h2"><?php echo wp_kses_post( $ui->label['reorder'] ); ?></a>
				<?php
			}
			?>
		</h2>
		<?php endif; ?>

		<form id="posts-filter" action="" method="get">
			<?php
			$excluded_filters = array(
				$ui->num_prefix . 'search' . $ui->num,
				$ui->num_prefix . 'pg' . $ui->num,
				$ui->num_prefix . 'action' . $ui->num,
				$ui->num_prefix . 'action_bulk' . $ui->num,
				$ui->num_prefix . 'action_bulk_ids' . $ui->num,
				$ui->num_prefix . '_wpnonce' . $ui->num,
			);

			$filters = $ui->filters;

			foreach ( $filters as $k => $filter ) {
				if ( isset( $ui->pod->fields[ $filter ] ) ) {
					$filter_field = $ui->pod->fields[ $filter ];

					if ( isset( $ui->fields['manage'][ $filter ] ) && is_array( $ui->fields['manage'][ $filter ] ) ) {
						$filter_field = pods_config_merge_data( $filter_field, $ui->fields['manage'][ $filter ] );
					}
				} elseif ( isset( $ui->fields['manage'][ $filter ] ) ) {
					$filter_field = $ui->fields['manage'][ $filter ];
				} else {
					unset( $filters[ $k ] );
					continue;
				}

				if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
					if ( '' == pods_v( 'filter_' . $filter . '_start', 'get', '', true ) && '' == pods_v( 'filter_' . $filter . '_end', 'get', '', true ) ) {
						unset( $filters[ $k ] );
						continue;
					}
				} elseif ( '' === pods_v( 'filter_' . $filter, 'get', '' ) ) {
					unset( $filters[ $k ] );
					continue;
				}

				$excluded_filters[] = 'filter_' . $filter . '_start';
				$excluded_filters[] = 'filter_' . $filter . '_end';
				$excluded_filters[] = 'filter_' . $filter;
			}//end foreach

			$ui->hidden_vars( $excluded_filters );

			if ( false !== $ui->callback( 'header', $reorder ) ) {
				return null;
			}

			if ( false === $ui->data ) {
				$ui->get_data();
			} elseif ( $ui->sortable ) {
				// we have the data already as an array
				$ui->sort_data();
			}

			if ( 'export' === $ui->action && ! in_array( 'export', $ui->actions_disabled, true ) ) {
				$ui->export();
			}

			if ( ( ! empty( $ui->data ) || false !== $ui->search || ( $ui->filters_enhanced && ! empty( $ui->views ) ) ) && ( ( $ui->filters_enhanced && ! empty( $ui->views ) ) || false !== $ui->searchable ) ) {
				wp_enqueue_style( 'pods-styles' );

				if ( $ui->filters_enhanced ) {
					$ui->filters();
				} else {
					?>
					<p class="search-box" align="right">
						<?php
						foreach ( $ui->filters as $filter ) {
							if ( isset( $ui->pod->fields[ $filter ] ) ) {
								$filter_field = $ui->pod->fields[ $filter ];

								if ( isset( $ui->fields['manage'][ $filter ] ) ) {
									$filter_field = pods_config_merge_data( $filter_field, $ui->fields['manage'][ $filter ] );
								}
							} elseif ( isset( $ui->fields['manage'][ $filter ] ) ) {
								$filter_field = $ui->fields['manage'][ $filter ];
							} else {
								continue;
							}
							?>
							<span class="pods-form-ui-filter-container pods-form-ui-filter-container-<?php echo esc_attr( $filter ); ?>">
								<?php
								if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
									$start = pods_v( 'filter_' . $filter . '_start', 'get', pods_v( 'filter_default', $filter_field, '', true ), true );
									$end   = pods_v( 'filter_' . $filter . '_end', 'get', pods_v( 'filter_ongoing_default', $filter_field, '', true ), true );

									// override default value
									$filter_field['default_value']                          = '';
									$filter_field[ $filter_field['type'] . '_allow_empty' ] = 1;

									if ( ! empty( $start ) && ! in_array( $start, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
										$start = PodsForm::field_method( $filter_field['type'], 'convert_date', $start, 'n/j/Y' );
									}

									if ( ! empty( $end ) && ! in_array( $end, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
										$end = PodsForm::field_method( $filter_field['type'], 'convert_date', $end, 'n/j/Y' );
									}
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
										<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter . '_start', $start, $filter_field['type'], $filter_field )
									);
									?>

									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">
										to
									</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter . '_end', $end, $filter_field['type'], $filter_field )
									);
								} elseif ( 'pick' === $filter_field['type'] ) {
									$value = pods_v( 'filter_' . $filter );

									if ( '' === $value ) {
										$value = pods_v( 'filter_default', $filter_field );}

									// override default value
									$filter_field['default_value'] = '';

									$filter_field['pick_format_type']   = 'single';
									$filter_field['pick_format_single'] = 'dropdown';
									$filter_field['pick_allow_add_new'] = 0;

									$filter_field['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $ui->fields['search'] ?: $ui->fields['manage'], array(), true ), '', true );
									$filter_field['input_helper'] = pods_v( 'ui_input_helper', $filter_field, $filter_field['input_helper'], true );

									$options = $filter_field;
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
										<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter, $value, 'pick', $options )
									);
								} elseif ( 'boolean' === $filter_field['type'] ) {
									$value = pods_v( 'filter_' . $filter, 'get', '' );

									if ( '' === $value ) {
										$value = pods_v( 'filter_default', $filter_field );}

									// override default value
									$filter_field['default_value'] = '';

									$filter_field['pick_format_type']   = 'single';
									$filter_field['pick_format_single'] = 'dropdown';
									$filter_field['pick_allow_add_new'] = 0;

									$filter_field['pick_object'] = 'custom-simple';
									$filter_field['pick_custom'] = array(
										'1' => pods_v( 'boolean_yes_label', $filter_field, __( 'Yes', 'pods' ), true ),
										'0' => pods_v( 'boolean_no_label', $filter_field, __( 'No', 'pods' ), true ),
									);

									$filter_field['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $ui->fields['search'] ?: $ui->fields['manage'], array(), true ), '', true );
									$filter_field['input_helper'] = pods_v( 'ui_input_helper', $filter_field, $filter_field['input_helper'], true );

									$options = $filter_field;
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
									<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter, $value, 'pick', $options )
									);
								} else {
									$value = pods_v( 'filter_' . $filter );

									if ( '' === $value ) {
										$value = pods_v( 'filter_default', $filter_field );
									}

									// override default value
									$filter_field['default_value'] = '';

									$options                 = array();
									$options['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $ui->fields['search'] ?: $ui->fields['manage'], array(), true ), '', true );
									$options['input_helper'] = pods_v( 'ui_input_helper', $options, $options['input_helper'], true );
									?>
									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
										<?php esc_html_e( $filter_field['label'] ); ?>
									</label>
									<?php
									// Prevent p div issues.
									echo str_replace(
										array(
											'<div',
											'</div>',
										),
										array(
											'<span',
											'</span>',
										),
										PodsForm::field( 'filter_' . $filter, $value, 'text', $options )
									);
								}//end if
								?>
									</span>
							<?php
						}//end foreach

						if ( false !== $ui->do_hook( 'filters_show_search', true ) ) {
							?>
							<span class="pods-form-ui-filter-container pods-form-ui-filter-container-search">
									<label<?php echo ( empty( $ui->filters ) ) ? ' class="screen-reader-text"' : ''; ?> for="<?php echo esc_attr( $ui->num_prefix ); ?>page-search<?php echo esc_attr( $ui->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>:</label>
									<?php echo PodsForm::field( $ui->num_prefix . 'search' . $ui->num, $ui->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $ui->num . '-input' ), 'disable_dfv' => true ) ); ?>
								</span>
							<?php
						} else {
							echo PodsForm::field( $ui->num_prefix . 'search' . $ui->num, '', 'hidden' );
						}

						echo PodsForm::submit_button( $ui->header['search'], 'button', false, false, array( 'id' => $ui->num_prefix . 'search' . $ui->num . '-submit' ) );

						if ( 0 < strlen( $ui->search ) ) {
							$clear_filters = array(
								$ui->num_prefix . 'search' . $ui->num => false,
							);

							foreach ( $ui->filters as $filter ) {
								$clear_filters[ 'filter_' . $filter . '_start' ] = false;
								$clear_filters[ 'filter_' . $filter . '_end' ]   = false;
								$clear_filters[ 'filter_' . $filter ]            = false;
							}
							?>
							<br class="clear" />
							<small>[<a href="<?php echo esc_url( pods_query_arg( $clear_filters, array( $ui->num_prefix . 'orderby' . $ui->num, $ui->num_prefix . 'orderby_dir' . $ui->num, $ui->num_prefix . 'limit' . $ui->num, 'page' ), $ui->exclusion() ) ); ?>"><?php _e( 'Reset Filters', 'pods' ); ?></a>]</small>
							<br class="clear" />
							<?php
						}
						?>
					</p>
					<?php
				}//end if
			} else {
				?>
				<br class="clear" />
				<?php
			}//end if

			if ( ! empty( $ui->data ) && ( false !== $ui->pagination_total || false !== $ui->pagination || true === $reorder ) || ( ! in_array( 'export', $ui->actions_disabled ) && ! in_array( 'export', $ui->actions_hidden ) ) || ! empty( $ui->actions_disabled ) ) {
			?>
			<div class="tablenav">
				<?php
				if ( ! empty( $ui->data ) && ! empty( $ui->actions_bulk ) ) {
					?>
					<div class="alignleft actions">
						<?php wp_nonce_field( 'pods-ui-action-bulk', $ui->num_prefix . '_wpnonce' . $ui->num, false ); ?>

						<select name="<?php echo esc_attr( $ui->num_prefix ); ?>action_bulk<?php echo esc_attr( $ui->num ); ?>">
							<option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'pods' ); ?></option>

							<?php
							foreach ( $ui->actions_bulk as $action => $action_data ) {
								if ( in_array( $action, $ui->actions_hidden ) || in_array( $action, $ui->actions_hidden ) ) {
									continue;}

								if ( ! isset( $action_data['label'] ) ) {
									$action_data['label'] = ucwords( str_replace( '_', ' ', $action ) );}
								?>
								<option value="<?php echo esc_attr( $action ); ?>"><?php esc_html_e( $action_data['label'] ); ?></option>
								<?php
							}
							?>
						</select>

						<input type="submit" id="<?php echo esc_attr( $ui->num_prefix ); ?>doaction_bulk<?php echo esc_attr( $ui->num ); ?>" class="button-secondary action" value="<?php esc_attr_e( 'Apply', 'pods' ); ?>">
					</div>
					<?php
				}//end if

				if ( true !== $reorder && ( false !== $ui->pagination_total || false !== $ui->pagination ) ) {
					?>
					<div class="tablenav-pages<?php esc_attr_e( ( $ui->limit < $ui->total_found || 1 < $ui->page ) ? '' : ' one-page' ); ?>">
						<?php $ui->pagination( true ); ?>
					</div>
					<?php
				}

				if ( true === $reorder ) {
				$link = pods_query_arg(
					array(
						$ui->num_prefix . 'action' . $ui->num => 'manage',
						$ui->num_prefix . 'id' . $ui->num     => '',
					), $ui::$allowed, $ui->exclusion()
				);

				if ( ! empty( $ui->action_links['manage'] ) ) {
					$link = $ui->action_links['manage'];
				}
				?>
				<input type="button" value="<?php esc_attr_e( 'Update Order', 'pods' ); ?>" class="button" onclick="jQuery('form.admin_ui_reorder_form').submit();" />
				<input type="button" value="<?php esc_attr_e( 'Cancel', 'pods' ); ?>" class="button" onclick="document.location='<?php echo esc_js( $link ); ?>';" />
		</form>
		<?php
		} elseif ( ! in_array( 'export', $ui->actions_disabled ) && ! in_array( 'export', $ui->actions_hidden ) ) {
		$export_document_location = pods_slash(
			pods_query_arg(
				array(
					$ui->num_prefix . 'action_bulk' . $ui->num => 'export',
					$ui->num_prefix . '_wpnonce' . $ui->num => wp_create_nonce( 'pods-ui-action-bulk' ),
				), $ui::$allowed, $ui->exclusion()
			)
		);
		?>
		<div class="alignleft actions">
			<input type="button" value="<?php esc_attr_e( sprintf( __( 'Export all %s', 'pods' ), $ui->items ) ); ?>" class="button" onclick="document.location='<?php echo $export_document_location; ?>';" />
		</div>
		<?php
		}//end if
		?>
		<br class="clear" />
	</div>
	<?php
	} else {
		?>
		<br class="clear" />
		<?php
	}//end if
	?>
	<div class="clear"></div>
	<?php
	if ( empty( $ui->data ) && false !== $ui->default_none && false === $ui->search ) {
		?>
		<p><?php _e( 'Please use the search filter(s) above to display data', 'pods' ); ?>
			<?php
			if ( $ui->export ) {
				?>
				, <?php _e( 'or click on an Export to download a full copy of the data', 'pods' ); ?><?php } ?>.</p>
		<?php
	} else {
		$ui->table( $reorder );}
	if ( ! empty( $ui->data ) ) {
		if ( true !== $reorder && ( false !== $ui->pagination_total || false !== $ui->pagination ) ) {
			?>
			<div class="tablenav">
				<div class="tablenav-pages<?php esc_attr_e( ( $ui->limit < $ui->total_found || 1 < $ui->page ) ? '' : ' one-page' ); ?>">
					<?php $ui->pagination(); ?>
					<br class="clear" />
				</div>
			</div>
			<?php
		}
	}

	?>
	</form>
</div>

<?php
/**
 * Allow additional output after the container area of the Pods UI manage screen.
 *
 * @since 2.7.17
 */
do_action( 'pods_ui_manage_after_container' );
?>
</div>
