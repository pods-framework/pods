<?php
/**
 * @var $ui PodsUI
 */
if ( true === $reorder && ! in_array( 'reorder', $ui->actions_disabled ) && false !== $ui->reorder['on'] ) {

?>
<style type="text/css">
	table.widefat.fixed tbody.reorderable tr {
		height: 50px;
	}

	.dragme {
		background:          url(<?php echo esc_url( PODS_URL ); ?>/ui/images/handle.gif) no-repeat;
		background-position: 8px 8px;
		cursor:              pointer;
	}

	.dragme strong {
		margin-left: 30px;
	}
</style>
<form action="
			<?php
echo esc_url(
	pods_query_arg(
		array(
			$ui->num_prefix . 'action' . $ui->num => 'reorder',
			$ui->num_prefix . 'do' . $ui->num     => 'save',
			'page'                => pods_v( 'page' ),
		), $ui::$allowed, $ui->exclusion()
	)
);
?>
		" method="post" class="admin_ui_reorder_form">
	<?php
		}//end if
			$table_fields = $ui->fields['manage'];
		if ( true === $reorder && ! in_array( 'reorder', $ui->actions_disabled ) && false !== $ui->reorder['on'] ) {
			$table_fields = $ui->fields['reorder'];
		}
		if ( false === $table_fields || empty( $table_fields ) ) {
			return $ui->error( __( '<strong>Error:</strong> Invalid Configuration - Missing "fields" definition.', 'pods' ) );
		}
			?>
	<table class="widefat page fixed wp-list-table" cellspacing="0"<?php echo ( 1 == $reorder && $ui->reorder ) ? ' id="admin_ui_reorder"' : ''; ?>>
		<thead>
		<tr>
			<?php
			if ( ! empty( $ui->actions_bulk ) ) {
				?>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" />
				</th>
				<?php
			}

			$name_field = false;
			$fields     = array();
			if ( ! empty( $table_fields ) ) {
				foreach ( $table_fields as $field => $attributes ) {
					if ( false === $attributes['display'] ) {
						continue;
					}
					if ( false === $name_field ) {
						$id = 'title';
					} else {
						$id = '';
					}
					if ( 'other' === $attributes['type'] ) {
						$id = '';
					}
					if ( in_array( $attributes['type'], array( 'date', 'datetime', 'time' ) ) ) {
						$id = 'date';
					}
					if ( false === $name_field && 'title' === $id ) {
						$name_field = true;
					}
					$fields[ $field ]             = $attributes;
					$fields[ $field ]['field_id'] = $id;
					$dir                          = 'DESC';
					$current_sort                 = ' asc';
					if ( isset( $ui->orderby['default'] ) && $field == $ui->orderby['default'] ) {
						if ( 'DESC' === $ui->orderby_dir ) {
							$dir          = 'ASC';
							$current_sort = ' desc';
						}
					}

					$att_id = '';
					if ( ! empty( $id ) ) {
						$att_id = ' id="' . esc_attr( $id ) . '"';
					}

					$width = '';

					$column_classes = array(
						'manage-column',
						'column-' . $id,
					);

					// Merge with the classes taken from the UI call
					if ( ! empty( $attributes['classes'] ) && is_array( $attributes['classes'] ) ) {
						$column_classes = array_merge( $column_classes, $attributes['classes'] );
					}
					if ( $id == 'title' ) {
						$column_classes[] = 'column-primary';
					}

					if ( isset( $attributes['width'] ) && ! empty( $attributes['width'] ) ) {
						$width = ' style="width: ' . esc_attr( $attributes['width'] ) . '"';
					}

					if ( $ui->is_field_sortable( $attributes ) ) {
						$column_classes[] = 'sortable' . $current_sort;
						?>
						<th scope="col"<?php echo $att_id; ?> class="<?php esc_attr_e( implode( ' ', $column_classes ) ); ?>"<?php echo $width; ?>>
							<a href="
									<?php
							echo esc_url_raw(
								pods_query_arg(
									array(
										$ui->num_prefix . 'orderby' . $ui->num => $field,
										$ui->num_prefix . 'orderby_dir' . $ui->num => $dir,
									), array(
									$ui->num_prefix . 'limit' . $ui->num,
									$ui->num_prefix . 'search' . $ui->num,
									$ui->num_prefix . 'pg' . $ui->num,
									'page',
								), $ui->exclusion()
								)
							);
							?>
									">
								<span><?php esc_html_e( $attributes['label'] ); ?></span>
								<span class="sorting-indicator"></span> </a>
						</th>
						<?php
					} else {
						?>
						<th scope="col"<?php echo $att_id; ?> class="<?php esc_attr_e( implode( ' ', $column_classes ) ); ?>"<?php echo $width; ?>><?php esc_html_e( $attributes['label'] ); ?></th>
						<?php
					}//end if
				}//end foreach
			}//end if
			?>
		</tr>
		</thead>
		<?php
		if ( 6 < $ui->total_found ) {
			?>
			<tfoot>
			<tr>
				<?php
				if ( ! empty( $ui->actions_bulk ) ) {
					?>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
					<?php
				}

				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field => $attributes ) {
						$dir = 'ASC';
						if ( $field == $ui->orderby ) {
							$current_sort = 'desc';
							if ( 'ASC' === $ui->orderby_dir ) {
								$dir          = 'DESC';
								$current_sort = 'asc';
							}
						}

						$width = '';

						if ( isset( $attributes['width'] ) && ! empty( $attributes['width'] ) ) {
							$width = ' style="width: ' . esc_attr( $attributes['width'] ) . '"';
						}

						if ( $ui->is_field_sortable( $attributes ) ) {
							?>
							<th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?> sortable <?php echo esc_attr( $current_sort ); ?>"<?php echo $width; ?>>
								<a href="
										<?php
								echo esc_url_raw(
									pods_query_arg(
										array(
											$ui->num_prefix . 'orderby' . $ui->num     => $field,
											$ui->num_prefix . 'orderby_dir' . $ui->num => $dir,
										), array(
										$ui->num_prefix . 'limit' . $ui->num,
										$ui->num_prefix . 'search' . $ui->num,
										$ui->num_prefix . 'pg' . $ui->num,
										'page',
									), $ui->exclusion()
									)
								);
								?>
										"><span><?php esc_html_e( $attributes['label'] ); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<?php
						} else {
							?>
							<th scope="col" class="manage-column column-<?php echo esc_attr( $id ); ?>"<?php echo $width; ?>><?php esc_html_e( $attributes['label'] ); ?></th>
							<?php
						}//end if
					}//end foreach
				}//end if
				?>
			</tr>
			</tfoot>
			<?php
		}//end if
		?>
		<tbody id="the-list"<?php echo ( true === $reorder && ! in_array( 'reorder', $ui->actions_disabled ) && false !== $ui->reorder['on'] ) ? ' class="reorderable"' : ''; ?>>
		<?php
		if ( ! empty( $ui->data ) && is_array( $ui->data ) ) {
			$counter = 0;

			while ( $row = $ui->get_row( $counter, 'table' ) ) {
				if ( is_object( $row ) ) {
					$row = get_object_vars( (object) $row );
				}

				$toggle_class = '';

				$field_id = '';

				if ( ! empty( $row[ $ui->sql['field_id'] ] ) ) {
					$field_id = $row[ $ui->sql['field_id'] ];
				}

				if ( is_array( $ui->actions_custom ) && isset( $ui->actions_custom['toggle'] ) ) {
					$toggle_class = ' pods-toggled-on';

					if ( ! isset( $row['toggle'] ) || empty( $row['toggle'] ) ) {
						$toggle_class = ' pods-toggled-off';
					}
				}
				?>
				<tr id="item-<?php echo esc_attr( $field_id ); ?>" class="iedit<?php echo esc_attr( $toggle_class ); ?>">
					<?php
					if ( ! empty( $ui->actions_bulk ) ) {
						?>
						<th scope="row" class="check-column">
							<input type="checkbox" name="<?php echo esc_attr( $ui->num_prefix ); ?>action_bulk_ids<?php echo esc_attr( $ui->num ); ?>[]" value="<?php echo esc_attr( $field_id ); ?>">
						</th>
						<?php
					}
					// Boolean for the first field to output after the check-column
					// will be set to false at the end of the first loop
					$first_field = true;
					foreach ( $fields as $field => $attributes ) {
						if ( false === $attributes['display'] ) {
							continue;
						}

						if ( ! isset( $row[ $field ] ) ) {
							$row[ $field ] = $ui->get_field( $field );
						}

						$row_value = $row[ $field ];

						if ( ! empty( $attributes['custom_display'] ) ) {
							if ( is_callable( $attributes['custom_display'] ) ) {
								$row_value = call_user_func_array(
									$attributes['custom_display'], array(
										$row,
										&$ui,
										$row_value,
										$field,
										$attributes,
									)
								);
							}
						} else {
							ob_start();

							$field_value = PodsForm::field_method( $attributes['type'], 'ui', $ui->id, $row_value, $field, $attributes, $fields, $ui->pod );

							$field_output = trim( (string) ob_get_clean() );

							if ( false === $field_value ) {
								$row_value = '';
							} elseif ( 0 < strlen( trim( (string) $field_value ) ) ) {
								$row_value = trim( (string) $field_value );
							} elseif ( 0 < strlen( $field_output ) ) {
								$row_value = $field_output;
							}
						}//end if

						if ( false !== $attributes['custom_relate'] ) {
							global $wpdb;
							$table = $attributes['custom_relate'];
							$on    = $ui->sql['field_id'];
							$is    = $field_id;
							$what  = array( 'name' );
							if ( is_array( $table ) ) {
								if ( isset( $table['on'] ) ) {
									$on = pods_sanitize( $table['on'] );
								}
								if ( isset( $table['is'] ) && isset( $row[ $table['is'] ] ) ) {
									$is = pods_sanitize( $row[ $table['is'] ] );
								}
								if ( isset( $table['what'] ) ) {
									$what = array();
									if ( is_array( $table['what'] ) ) {
										foreach ( $table['what'] as $wha ) {
											$what[] = pods_sanitize( $wha );
										}
									} else {
										$what[] = pods_sanitize( $table['what'] );
									}
								}
								if ( isset( $table['table'] ) ) {
									$table = $table['table'];
								}
							}//end if
							$table = pods_sanitize( $table );
							$wha   = implode( ',', $what );
							$sql   = "SELECT {$wha} FROM {$table} WHERE `{$on}`='{$is}'";
							$value = @current( $wpdb->get_results( $sql, ARRAY_A ) );
							if ( ! empty( $value ) ) {
								$val = array();
								foreach ( $what as $wha ) {
									if ( isset( $value[ $wha ] ) ) {
										$val[] = $value[ $wha ];
									}
								}
								if ( ! empty( $val ) ) {
									$row_value = implode( ' ', $val );
								}
							}
						}//end if

						$css_classes = array(
							'pods-ui-col-field-' . sanitize_title( $field ),
						);

						// Merge with the classes taken from the UI call
						if ( ! empty( $attributes['classes'] ) && is_array( $attributes['classes'] ) ) {
							$css_classes = array_merge( $css_classes, $attributes['classes'] );
						}

						if ( $attributes['css_values'] ) {
							$css_field_value = $row[ $field ];

							if ( is_object( $css_field_value ) ) {
								$css_field_value = get_object_vars( $css_field_value );
							}

							if ( is_array( $css_field_value ) ) {
								foreach ( $css_field_value as $css_field_val ) {
									if ( is_object( $css_field_val ) ) {
										$css_field_val = get_object_vars( $css_field_val );
									}

									if ( is_array( $css_field_val ) ) {
										foreach ( $css_field_val as $css_field_v ) {
											if ( is_object( $css_field_v ) ) {
												$css_field_v = get_object_vars( $css_field_v );
											}

											$css_classes[] = 'pods-ui-css-value-' . sanitize_title(
													str_replace(
														array(
															"\n",
															"\r",
														), ' ', strip_tags( (string) $css_field_v )
													)
												);
										}
									} else {
										$css_classes[] = ' pods-ui-css-value-' . sanitize_title(
												str_replace(
													array(
														"\n",
														"\r",
													), ' ', strip_tags( (string) $css_field_val )
												)
											);
									}//end if
								}//end foreach
							} else {
								$css_classes[] = ' pods-ui-css-value-' . sanitize_title(
										str_replace(
											array(
												"\n",
												"\r",
											), ' ', strip_tags( (string) $css_field_value )
										)
									);
							}//end if
						}//end if

						if ( is_object( $ui->pod ) ) {
							$row_value = $ui->do_hook( $ui->pod->pod . '_field_value', $row_value, $field, $attributes, $row );
						}

						$row_value = $ui->do_hook( 'field_value', $row_value, $field, $attributes, $row );

						if ( ! empty( $attributes['custom_display_formatted'] ) && is_callable( $attributes['custom_display_formatted'] ) ) {
							$row_value = call_user_func_array(
								$attributes['custom_display_formatted'], array(
									$row,
									&$ui,
									$row_value,
									$field,
									$attributes,
								)
							);
						}

						if ( 'title' === $attributes['field_id'] ) {
							$default_action = $ui->do_hook( 'default_action', 'edit', $row );

							if ( $first_field ) {
								$css_classes[] = 'column-primary';
							}

							if ( is_admin() ) {
								$css_classes[] = 'post-title';
								$css_classes[] = 'page-title';
							}

							$css_classes[] = 'column-title';

							if ( 'raw' !== $attributes['type'] ) {
								// Deal with unexpected array values.
								if ( is_array( $row_value ) ) {
									if ( empty( $row_value ) ) {
										$row_value = '';
									} else {
										$row_value = pods_serial_comma( $row_value, $attributes );
									}
								}

								$row_value = wp_kses_post( $row_value );
							}

							/**
							 * Allow filtering the display value used for a field in the table column.
							 *
							 * @since 2.7.29
							 *
							 * @param string $row_value  The display value for the field.
							 * @param string $field      The field name.
							 * @param array  $attributes The field attributes.
							 * @param array  $row        The other values for the row.
							 * @param PodsUI $obj        The PodsUI object.
							 */
							$row_value = apply_filters( 'pods_ui_field_display_value', $row_value, $field, $attributes, $row, $ui );

							if ( ! in_array( 'edit', $ui->actions_disabled ) && ! in_array( 'edit', $ui->actions_hidden ) && ( false === $reorder || in_array( 'reorder', $ui->actions_disabled ) || false === $ui->reorder['on'] ) && 'edit' === $default_action ) {
								$link = pods_query_arg(
									array(
										$ui->num_prefix . 'action' . $ui->num => 'edit',
										$ui->num_prefix . 'id' . $ui->num     => $field_id,
									), $ui::$allowed, $ui->exclusion()
								);

								if ( ! empty( $ui->action_links['edit'] ) ) {
									$link = $ui->do_template( $ui->action_links['edit'], $row );
								}
								?>
								<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>">
								<strong><a class="row-title" href="<?php echo esc_url_raw( $link ); ?>" title="<?php esc_attr_e( 'Edit this item', 'pods' ); ?>">
										<?php
										/* Escaped above for non-HTML types */
										echo $row_value;
										?>
									</a></strong>
								<?php
							} elseif ( ! in_array( 'view', $ui->actions_disabled ) && ! in_array( 'view', $ui->actions_hidden ) && ( false === $reorder || in_array( 'reorder', $ui->actions_disabled ) || false === $ui->reorder['on'] ) && 'view' === $default_action ) {
								$link = pods_query_arg(
									array(
										$ui->num_prefix . 'action' . $ui->num => 'view',
										$ui->num_prefix . 'id' . $ui->num     => $field_id,
									), $ui::$allowed, $ui->exclusion()
								);

								if ( ! empty( $ui->action_links['view'] ) ) {
									$link = $ui->do_template( $ui->action_links['view'], $row );
								}
								?>
								<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>">
								<strong><a class="row-title" href="<?php echo esc_url_raw( $link ); ?>" title="<?php esc_attr_e( 'View this item', 'pods' ); ?>">
										<?php
										/* Escaped above for non-HTML types */
										echo $row_value;
										?>
									</a></strong>
								<?php
							} else {
								if ( 1 == $reorder && $ui->reorder ) {
									$css_classes[] = 'dragme';
								}
								?>
								<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>">
								<strong>
									<?php
									/* Escaped above for non-HTML types */
									echo $row_value;
									?>
								</strong>
								<?php
							}//end if

							if ( true !== $reorder || in_array( 'reorder', $ui->actions_disabled ) || false === $ui->reorder['on'] ) {
								$toggle = false;

								$actions = $ui->get_actions( $row );
								$actions = $ui->do_hook( 'row_actions', $actions, $field_id );

								if ( ! empty( $actions ) ) {
									?>
									<div class="row-actions<?php echo esc_attr( $toggle ? ' row-actions-toggle' : '' ); ?>">
										<?php
										$ui->callback( 'actions_start', $row, $actions );

										echo implode( ' | ', $actions );

										$ui->callback( 'actions_end', $row, $actions );
										?>
									</div>
									<?php
								}
							} else {
								?>
								<input type="hidden" name="order[]" value="<?php echo esc_attr( $field_id ); ?>" />
								<?php
							}//end if

							if ( ! in_array( 'toggle_details', $ui->actions_disabled, true ) ) {
								?>
								<button type="button" class="toggle-row">
									<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
								</button>
							<?php } ?>
							</td>
							<?php
						} elseif ( 'date' === $attributes['type'] ) {
							if ( $first_field ) {
								$css_classes[] = 'column-primary';
							}
							$css_classes[] = 'date';
							$css_classes[] = 'column-date';
							?>
							<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>" data-colname="<?php echo esc_attr( $attributes['label'] ); ?>">
								<abbr title="<?php echo esc_attr( $row_value ); ?>"><?php echo wp_kses_post( $row_value ); ?></abbr>
								<?php if ( $first_field && ! in_array( 'toggle_details', $ui->actions_disabled, true ) ) { ?>
									<button type="button" class="toggle-row">
										<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
									</button>
								<?php } ?>
							</td>
							<?php
						} else {
							if ( $first_field ) {
								$css_classes[] = 'column-primary';
							}

							$css_classes[] = 'author';

							if ( 'raw' !== $attributes['type'] ) {
								// Deal with unexpected array values.
								if ( is_array( $row_value ) ) {
									if ( empty( $row_value ) ) {
										$row_value = '';
									} else {
										$row_value = pods_serial_comma( $row_value, $attributes );
									}
								}

								$row_value = wp_kses_post( $row_value );
							}
							?>
							<td class="<?php esc_attr_e( implode( ' ', $css_classes ) ); ?>" data-colname="<?php echo esc_attr( $attributes['label'] ); ?>">
										<span>
										<?php
										/* Escaped above for non-HTML types */
										echo $row_value;
										?>
											</span>
								<?php if ( $first_field && ! in_array( 'toggle_details', $ui->actions_disabled, true ) ) { ?>
									<button type="button" class="toggle-row">
										<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'pods' ); ?></span>
									</button>
								<?php } ?>
							</td>
							<?php
						}//end if
						$first_field = false;
					}//end foreach
					?>
				</tr>
				<?php
			}//end while
		}//end if
		?>
		</tbody>
	</table>
	<?php
	if ( true === $reorder && ! in_array( 'reorder', $ui->actions_disabled ) && false !== $ui->reorder['on'] ) {

	?>
</form>
<?php
}
?>
<script type="text/javascript">
	document.addEventListener( 'DOMContentLoaded', function( event ) {
		jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
		<?php
		if ( true === $reorder && ! in_array( 'reorder', $ui->actions_disabled ) && false !== $ui->reorder['on'] ) {
		?>
		jQuery( ".reorderable" ).sortable( {axis : "y", handle : ".dragme"} );
		jQuery( ".reorderable" ).bind( 'sortupdate', function ( event, ui ) {
			jQuery( 'table.widefat tbody tr' ).removeClass( 'alternate' );
			jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
		} );
		<?php
		}
		?>
	} );
</script>
