<?php
/**
 * @var $ui PodsUI
 */
?>
<div id="pods-ui-posts-filter-popup" class="pods-hidden">
	<form action="" method="get" class="pods-ui-posts-filter-popup">
		<h2><?php _e( 'Advanced Filters', 'pods' ); ?></h2>

		<div class="pods-ui-posts-filters">
			<?php
			$excluded_filters = array(
				$ui->num_prefix . 'search' . $ui->num,
				$ui->num_prefix . 'pg' . $ui->num,
				$ui->num_prefix . 'action' . $ui->num,
				$ui->num_prefix . 'action_bulk' . $ui->num,
				$ui->num_prefix . 'action_bulk_ids' . $ui->num,
				$ui->num_prefix . '_wpnonce' . $ui->num,
			);

			foreach ( $filters as $filter ) {
				$excluded_filters[] = 'filters_relation';
				$excluded_filters[] = 'filters_compare_' . $filter;
				$excluded_filters[] = 'filter_' . $filter . '_start';
				$excluded_filters[] = 'filter_' . $filter . '_end';
				$excluded_filters[] = 'filter_' . $filter;
			}

			$get = $_GET;

			foreach ( $get as $k => $v ) {
				if ( in_array( $k, $excluded_filters ) || strlen( $v ) < 1 ) {
					continue;
				}
				?>
				<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>"/>
				<?php
			}

			$zebra = true;

			foreach ( $filters as $filter ) {
				if ( empty( $filter ) ) {
					continue;
				}

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
				<p class="pods-ui-posts-filter-toggled pods-ui-posts-filter-<?php echo esc_attr( $filter . ( $zebra ? ' clear' : '' ) ); ?>">
					<?php
					if ( in_array( $filter_field['type'], array( 'date', 'datetime', 'time' ) ) ) {
						$start = pods_v( 'filter_' . $filter . '_start', 'get', pods_v( 'filter_default', $filter_field, '', true ), true );
						$end   = pods_v( 'filter_' . $filter . '_end', 'get', pods_v( 'filter_ongoing_default', $filter_field, '', true ), true );

						// override default value
						$filter_field['default_value']                          = '';
						$filter_field[ $filter_field['type'] . '_allow_empty' ] = 1;

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
						?>
						<span
							class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( ( empty( $start ) && empty( $end ) ) ? '' : ' pods-hidden' ); ?>">+</span>
						<span
							class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( ( empty( $start ) && empty( $end ) ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

						<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_start">
							<?php esc_html_e( $filter_field['label'] ); ?>
						</label>

						<span
							class="pods-ui-posts-filter<?php esc_attr_e( ( empty( $start ) && empty( $end ) ) ? ' pods-hidden' : '' ); ?>">
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

									<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>_end">to</label>
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
									?>
							</span>
						<?php
					} elseif ( 'pick' === $filter_field['type'] ) {
						$value = pods_v( 'filter_' . $filter, 'get', '' );

						if ( '' === $value ) {
							$value = pods_v( 'filter_default', $filter_field );
						}

						// override default value
						$filter_field['default_value'] = '';

						$filter_field['pick_format_type']   = 'single';
						$filter_field['pick_format_single'] = 'dropdown';
						$filter_field['pick_allow_add_new'] = 0;

						$filter_field['input_helper'] = pods_v( 'ui_input_helper', pods_v( $filter, $ui->fields['search'] ?: $ui->fields['manage'], array(), true ), '', true );
						$filter_field['input_helper'] = pods_v( 'ui_input_helper', $filter_field, $filter_field['input_helper'], true );

						$options = $filter_field;
						?>
						<span
							class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
						<span
							class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

						<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
							<?php esc_html_e( $filter_field['label'] ); ?>
						</label>

						<span class="pods-ui-posts-filter<?php esc_attr_e( '' === $value ? ' pods-hidden' : '' ); ?>">
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
								?>
							</span>
						<?php
					} elseif ( 'boolean' === $filter_field['type'] ) {
						$value = pods_v( 'filter_' . $filter, 'get', '' );

						if ( '' === $value ) {
							$value = pods_v( 'filter_default', $filter_field );
						}

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
						<span
							class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
						<span
							class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

						<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
							<?php esc_html_e( $filter_field['label'] ); ?>
						</label>

						<span class="pods-ui-posts-filter<?php esc_attr_e( '' === $value ? ' pods-hidden' : '' ); ?>">
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
								?>
							</span>
						<?php
					} else {
						$value = pods_v( 'filter_' . $filter );

						if ( '' === $value ) {
							$value = pods_v( 'filter_default', $filter_field );
						}

						$options = array(
							'input_helper' => pods_v( 'ui_input_helper', pods_v( 'options', pods_v( $filter, $ui->fields['search'], array(), true ), array(), true ), '', true ),
						);

						if ( empty( $options['input_helper'] ) && isset( $filter_field['input_helper'] ) ) {
							$options['input_helper'] = $filter_field['input_helper'];
						}
						?>
						<span
							class="pods-ui-posts-filter-toggle toggle-on<?php esc_attr_e( empty( $value ) ? '' : ' pods-hidden' ); ?>">+</span>
						<span
							class="pods-ui-posts-filter-toggle toggle-off<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>"><?php _e( 'Clear', 'pods' ); ?></span>

						<label for="pods-form-ui-filter-<?php echo esc_attr( $filter ); ?>">
							<?php esc_html_e( $filter_field['label'] ); ?>
						</label>

						<span class="pods-ui-posts-filter<?php esc_attr_e( empty( $value ) ? ' pods-hidden' : '' ); ?>">
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
								?>
							</span>
						<?php
					}//end if
					?>
				</p>
				<?php
				$zebra = empty( $zebra );
			}//end foreach
			?>

			<p class="pods-ui-posts-filter-toggled pods-ui-posts-filter-search<?php echo esc_attr( $zebra ? ' clear' : '' ); ?>">
				<label
					for="<?php echo esc_attr( $ui->num_prefix ); ?>pods-form-ui-search<?php echo esc_attr( $ui->num ); ?>"><?php _e( 'Search Text', 'pods' ); ?></label>
				<?php echo PodsForm::field( $ui->num_prefix . 'search' . $ui->num, pods_v( $ui->num_prefix . 'search' . $ui->num ), 'text' ); ?>
			</p>

			<?php $zebra = empty( $zebra ); ?>
		</div>

		<p class="submit<?php echo esc_attr( $zebra ? ' clear' : '' ); ?>">
			<input type="submit" value="<?php echo esc_attr( $ui->header['search'] ); ?>"
			       class="button button-primary"/>
		</p>
	</form>
</div>

<script type="text/javascript">
	document.addEventListener( 'DOMContentLoaded', function ( event ) {
		jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggle.toggle-on', function ( e ) {
			jQuery( this ).parent().find( '.pods-ui-posts-filter' ).removeClass( 'pods-hidden' );

			jQuery( this ).hide();
			jQuery( this ).parent().find( '.toggle-off' ).show();
		} );

		jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggle.toggle-off', function ( e ) {
			jQuery( this ).parent().find( '.pods-ui-posts-filter' ).addClass( 'pods-hidden' );
			jQuery( this ).parent().find( 'select, input' ).val( '' );

			jQuery( this ).hide();
			jQuery( this ).parent().find( '.toggle-on' ).show();
		} );

		jQuery( document ).on( 'click', '.pods-ui-posts-filter-toggled label', function ( e ) {
			if ( jQuery( this ).parent().find( '.pods-ui-posts-filter' ).hasClass( 'pods-hidden' ) ) {
				jQuery( this ).parent().find( '.pods-ui-posts-filter' ).removeClass( 'pods-hidden' );

				jQuery( this ).parent().find( '.toggle-on' ).hide();
				jQuery( this ).parent().find( '.toggle-off' ).show();
			} else {
				jQuery( this ).parent().find( '.pods-ui-posts-filter' ).addClass( 'pods-hidden' );
				jQuery( this ).parent().find( 'select, input' ).val( '' );

				jQuery( this ).parent().find( '.toggle-on' ).show();
				jQuery( this ).parent().find( '.toggle-off' ).hide();
			}
		} );
	} );
</script>
