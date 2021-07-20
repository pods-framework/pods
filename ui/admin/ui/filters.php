<?php
/**
 * @var $ui PodsUI
 */
wp_enqueue_script( 'thickbox' );
wp_enqueue_style( 'thickbox' );

$filters = $ui->filters;

foreach ( $filters as $k => $filter ) {
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

	if ( isset( $filter_field ) && in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
		if ( '' == pods_v( 'filter_' . $filter . '_start', 'get', '', true ) && '' == pods_v( 'filter_' . $filter . '_end', 'get', '', true ) ) {
			unset( $filters[ $k ] );
		}
	} elseif ( '' === pods_v( 'filter_' . $filter, 'get', '' ) ) {
		unset( $filters[ $k ] );
	}
}

$filtered = false;

if ( ! empty( $filters ) ) {
	$filtered = true;
}
?>
	<div class="pods-ui-filter-bar">
		<div class="pods-ui-filter-bar-primary">
			<?php
			if ( ! empty( $ui->views ) ) {
				?>
				<ul class="subsubsub">
					<li class="pods-ui-filter-view-label">
						<strong><?php echo wp_kses_post( $ui->heading['views'] ); ?></strong></li>

					<?php
					foreach ( $ui->views as $view => $label ) {
						if ( false === strpos( $label, '<a' ) ) {
							$link = pods_query_arg(
									array(
											$ui->num_prefix . 'view' . $ui->num => $view,
											$ui->num_prefix . 'pg' . $ui->num   => '',
									), self::$allowed, $ui->exclusion()
							);

							if ( $ui->view == $view ) {
								$label = '<a href="' . esc_url( $link ) . '" class="current">' . esc_html( $label ) . '</a>';
							} else {
								$label = '<a href="' . esc_url( $link ) . '">' . esc_html( $label ) . '</a>';
							}
						} else {
							$label = wp_kses_post( $label );
						}
						?>
						<li class="<?php echo esc_attr( $view ); ?>">
							<?php
							/* Escaped above to support links */
							echo $label;
							?>
						</li>
						<?php
					}//end foreach
					?>
				</ul>
				<?php
			}//end if
			?>

			<?php
			if ( false !== $ui->search && false !== $ui->searchable ) {
				?>
				<p class="search-box">
					<?php
					if ( $filtered || '' != pods_v( $ui->num_prefix . 'search' . $ui->num, 'get', '', true ) ) {
						$clear_filters = array(
								$ui->num_prefix . 'search' . $ui->num => false,
						);

						foreach ( $ui->filters as $filter ) {
							$clear_filters[ 'filter_' . $filter . '_start' ] = false;
							$clear_filters[ 'filter_' . $filter . '_end' ]   = false;
							$clear_filters[ 'filter_' . $filter ]            = false;
						}
						?>
						<a href="
							<?php
						echo esc_url(
								pods_query_arg(
										$clear_filters, array(
										$ui->num_prefix . 'orderby' . $ui->num,
										$ui->num_prefix . 'orderby_dir' . $ui->num,
										$ui->num_prefix . 'limit' . $ui->num,
										'page',
								), $ui->exclusion()
								)
						);
						?>
							" class="pods-ui-filter-reset">[<?php _e( 'Reset', 'pods' ); ?>]</a>
						<?php
					}//end if

					if ( false !== pods_do_hook( 'ui', 'filters_show_search', [ true ], $ui ) ) {
						?>
						&nbsp;&nbsp;
						<label class="screen-reader-text"
							   for="<?php echo esc_attr( $ui->num_prefix ); ?>page-search<?php echo esc_attr( $ui->num ); ?>-input"><?php _e( 'Search', 'pods' ); ?>
							:</label>
						<?php echo PodsForm::field( $ui->num_prefix . 'search' . $ui->num, $ui->search, 'text', array( 'attributes' => array( 'id' => 'page-search' . $ui->num . '-input' ) ) ); ?>
						<?php
					} else {
						echo PodsForm::field( $ui->num_prefix . 'search' . $ui->num, '', 'hidden' );
					}
					?>

					<?php echo PodsForm::submit_button( $ui->header['search'], 'button', false, false, array( 'id' => $ui->num_prefix . 'search' . $ui->num . '-submit' ) ); ?>
				</p>
				<?php
			}//end if
			?>
		</div>

		<?php
		if ( ! empty( $ui->filters ) ) {
			?>
			<div class="pods-ui-filter-bar-secondary">
				<ul class="subsubsub">
					<?php
					if ( ! $filtered ) {
						?>
						<li class="pods-ui-filter-bar-add-filter">
							<a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox"
							   title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
								<?php _e( 'Advanced Filters', 'pods' ); ?>
							</a>
						</li>
						<?php
					} else {
						?>
						<li class="pods-ui-filter-bar-add-filter">
							<a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox"
							   title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
								+ <?php _e( 'Add Filter', 'pods' ); ?>
							</a>
						</li>
						<?php
					}

					foreach ( $filters as $filter ) {
						$value = pods_v( 'filter_' . $filter );

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

						$data_filter = 'filter_' . $filter;

						$start       = '';
						$end         = '';
						$value_label = '';

						if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
							$start = pods_v( 'filter_' . $filter . '_start', 'get', '', true );
							$end   = pods_v( 'filter_' . $filter . '_end', 'get', '', true );

							if ( ! empty( $start ) && ! in_array(
											$start, array(
													'0000-00-00',
													'0000-00-00 00:00:00',
													'00:00:00',
											)
									) ) {
								$start = PodsForm::field_method( $filter_field['type'], 'convert_date', $start, 'n/j/Y' );
							}

							if ( ! empty( $end ) && ! in_array(
											$end, array(
													'0000-00-00',
													'0000-00-00 00:00:00',
													'00:00:00',
											)
									) ) {
								$end = PodsForm::field_method( $filter_field['type'], 'convert_date', $end, 'n/j/Y' );
							}

							$value = trim( $start . ' - ' . $end, ' -' );

							$data_filter = 'filter_' . $filter . '_start';
						} elseif ( 'pick' === $filter_field['type'] ) {
							$value_label = trim( PodsForm::field_method( 'pick', 'value_to_label', $filter, $value, $filter_field, $ui->pod->pod_data, null ) );
						} elseif ( 'boolean' === $filter_field['type'] ) {
							$yesno_options = array(
									'1' => pods_v( 'boolean_yes_label', $filter_field, __( 'Yes', 'pods' ), true ),
									'0' => pods_v( 'boolean_no_label', $filter_field, __( 'No', 'pods' ), true ),
							);

							if ( isset( $yesno_options[ (string) $value ] ) ) {
								$value_label = $yesno_options[ (string) $value ];
							}
						}//end if

						if ( strlen( $value_label ) < 1 ) {
							$value_label = $value;
						}
						?>
						<li class="pods-ui-filter-bar-filter" data-filter="<?php echo esc_attr( $data_filter ); ?>">
							<a href="#TB_inline?width=640&inlineId=pods-ui-posts-filter-popup" class="thickbox"
							   title="<?php esc_attr_e( 'Advanced Filters', 'pods' ); ?>">
								<strong><?php esc_html_e( $filter_field['label'] ); ?>:</strong>
								<?php esc_html_e( $value_label ); ?>
							</a>

							<a href="#remove-filter" class="remove-filter"
							   title="<?php esc_attr_e( 'Remove Filter', 'pods' ); ?>">x</a>

							<?php
							if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
								echo PodsForm::field( 'filter_' . $filter . '_start', $start, 'hidden' );
								echo PodsForm::field( 'filter_' . $filter . '_end', $end, 'hidden' );
							} else {
								echo PodsForm::field( $data_filter, $value, 'hidden' );
							}
							?>
						</li>
						<?php
					}//end foreach
					?>
				</ul>
			</div>
			<?php
		}//end if
		?>
	</div>

	<script type="text/javascript">
		document.addEventListener( 'DOMContentLoaded', function ( event ) {
			jQuery( '.pods-ui-filter-bar-secondary' ).on( 'click', '.remove-filter', function ( e ) {
				jQuery( '.pods-ui-filter-popup #' + jQuery( this ).parent().data( 'filter' ) ).remove();

				jQuery( this ).parent().find( 'input' ).each( function () {
					jQuery( this ).remove();
				} );

				jQuery( 'form#posts-filter [name="<?php echo esc_attr( $ui->num_prefix ); ?>pg<?php echo esc_attr( $ui->num ); ?>"]' ).prop( 'disabled', true );
				jQuery( 'form#posts-filter [name="<?php echo esc_attr( $ui->num_prefix ); ?>action<?php echo esc_attr( $ui->num ); ?>"]' ).prop( 'disabled', true );
				jQuery( 'form#posts-filter [name="<?php echo esc_attr( $ui->num_prefix ); ?>action_bulk<?php echo esc_attr( $ui->num ); ?>"]' ).prop( 'disabled', true );
				jQuery( 'form#posts-filter [name="<?php echo esc_attr( $ui->num_prefix ); ?>_wpnonce<?php echo esc_attr( $ui->num ); ?>"]' ).prop( 'disabled', true );

				jQuery( 'form#posts-filter' ).submit();

				e.preventDefault();
			} );
		} );
	</script>
<?php
