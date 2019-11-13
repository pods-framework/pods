<?php
$field = array_merge( $field_settings['field_defaults'], $field );

$pick_object = trim( pods_v_sanitized( 'pick_object', $field ) . '-' . pods_v_sanitized( 'pick_val', $field ), '-' );
?>
<tr id="row-<?php echo esc_attr( $pods_i ); ?>" class="pods-manage-row pods-field-new pods-field-<?php echo esc_attr( pods_v_sanitized( 'name', $field ) ) . ( '--1' === $pods_i ? ' flexible-row' : ' pods-submittable-fields' ); ?>" valign="top" data-row="<?php echo esc_attr( $pods_i ); ?>">
	<th scope="row" class="check-field pods-manage-sort">
		<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/handle.gif" alt="<?php esc_attr_e( 'Move', 'pods' ); ?>" />
	</th>
	<td class="pods-manage-row-label">
		<strong>
			<a class="pods-manage-row-edit row-label" title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" href="#edit-field">
				<?php _e( 'New Field', 'pods' ); ?>
			</a> <abbr title="required" class="required hidden">*</abbr> </strong>

		<div class="row-actions">
			<span class="edit">
				<a title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" class="pods-manage-row-edit" href="#edit-field"><?php _e( 'Edit', 'pods' ); ?></a> |
			</span> <span class="duplicate">
				<a title="<?php esc_attr_e( 'Duplicate this field', 'pods' ); ?>" class="pods-manage-row-duplicate" href="#duplicate-field"><?php _e( 'Duplicate', 'pods' ); ?></a> |
			</span> <span class="trash pods-manage-row-delete">
				<a class="submitdelete" title="<?php esc_attr_e( 'Delete this field', 'pods' ); ?>" href="#delete-field"><?php _e( 'Delete', 'pods' ); ?></a>
			</span>
		</div>
		<div class="pods-manage-row-wrapper" id="pods-manage-field-<?php echo esc_attr( $pods_i ); ?>">
			<input type="hidden" name="field_data_json[<?php echo esc_attr( $pods_i ); ?>]" value="" class="field_data" />

			<div class="pods-manage-field pods-dependency">
				<div class="pods-tabbed">
					<ul class="pods-tabs">
						<?php
						$default = 'basic';

						foreach ( $field_tabs as $tab => $label ) {
							if ( ! in_array(
								$tab, array(
									'basic',
									'additional-field',
									'advanced',
								), true
							) && ( ! isset( $field_tab_options[ $tab ] ) || empty( $field_tab_options[ $tab ] ) ) ) {
								continue;
							}

							$class         = '';
							$extra_classes = '';

							$tab = sanitize_title( $tab );

							if ( $tab === $default ) {
								$class = ' selected';
							}

							if ( 'additional-field' === $tab ) {
								$extra_classes = ' pods-excludes-on pods-excludes-on-field-data-type pods-excludes-on-field-data-type-' . implode( ' pods-excludes-on-field-data-type-', $no_additional );
							}
							?>
							<li class="pods-tab<?php echo esc_attr( $extra_classes ); ?>">
								<a href="#pods-<?php echo esc_attr( $tab ); ?>-options-<?php echo esc_attr( $pods_i ); ?>" class="pods-tab-link<?php echo esc_attr( $class ); ?>">
									<?php echo $label; ?>
								</a>
							</li>
							<?php
						}//end foreach
						?>
					</ul>

					<div class="pods-tab-group">
						<div id="pods-basic-options-<?php echo esc_attr( $pods_i ); ?>" class="pods-tab pods-basic-options">
							<div class="pods-field-option">
								<?php echo PodsForm::label( 'field_data[' . $pods_i . '][label]', __( 'Label', 'pods' ), __( 'help', 'pods' ) ); ?>
								<?php echo PodsForm::field( 'field_data[' . $pods_i . '][label]', pods_v( 'label', $field, '' ), 'text', array( 'class' => 'pods-validate pods-validate-required' ) ); ?>
							</div>
							<div class="pods-field-option">
								<?php echo PodsForm::label( 'field_data[' . $pods_i . '][name]', __( 'Name', 'pods' ), __( 'You will use this name to programatically reference this field throughout WordPress', 'pods' ) ); ?>
								<?php
								echo PodsForm::field(
									'field_data[' . $pods_i . '][name]', pods_v( 'name', $field, '' ), 'db', array(
										'attributes' => array(
											'maxlength'      => 50,
											'data-sluggable' => 'field_data[' . $pods_i . '][label]',
										),
										'class'      => 'pods-validate pods-validate-required pods-slugged-lower pods-slugged-sanitize-title',
									)
								);
								?>
							</div>
							<div class="pods-field-option">
								<?php echo PodsForm::label( 'field_data[' . $pods_i . '][description]', __( 'Description', 'pods' ), __( 'help', 'pods' ) ); ?>
								<?php echo PodsForm::field( 'field_data[' . $pods_i . '][description]', pods_v( 'description', $field, '' ), 'text' ); ?>
							</div>
							<div class="pods-field-option">
								<?php echo PodsForm::label( 'field_data[' . $pods_i . '][type]', __( 'Field Type', 'pods' ), __( 'help', 'pods' ) ); ?>
								<?php
								echo PodsForm::field(
									'field_data[' . $pods_i . '][type]', pods_v( 'type', $field, '' ), 'pick', array(
										'data'  => pods_v( 'field_types_select', $field_settings ),
										'class' => 'pods-dependent-toggle',
									)
								);
								?>
							</div>
							<div class="pods-field-option-container pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-pick">
								<div class="pods-field-option">
									<?php echo PodsForm::label( 'field_data[' . $pods_i . '][pick_object]', __( 'Related To', 'pods' ), __( 'help', 'pods' ) ); ?>
									<?php
									echo PodsForm::field(
										'field_data[' . $pods_i . '][pick_object]', $pick_object, 'pick', array(
											'required' => true,
											'data'     => pods_v( 'pick_object', $field_settings ),
											'class'    => 'pods-dependent-toggle',
										)
									);
									?>
								</div>
								<div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-custom-simple">
									<?php echo PodsForm::label( 'field_data[' . $pods_i . '][pick_custom]', __( 'Custom Defined Options', 'pods' ), __( 'One option per line, use <em>value|Label</em> for separate values and labels', 'pods' ) ); ?>
									<?php echo PodsForm::field( 'field_data[' . $pods_i . '][pick_custom]', pods_v( 'pick_custom', $field, '' ), 'paragraph' ); ?>
								</div>
								<div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-table">
									<?php echo PodsForm::label( 'field_data[' . $pods_i . '][pick_table]', __( 'Related Table', 'pods' ), __( 'help', 'pods' ) ); ?>
									<?php
									echo PodsForm::field(
										'field_data[' . $pods_i . '][pick_table]', pods_v( 'pick_table', $field, '' ), 'pick', array(
											'required' => true,
											'data'     => pods_v( 'pick_table', $field_settings ),
										)
									);
									?>
								</div>
								<div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-<?php echo esc_attr( str_replace( '_', '-', implode( ' pods-depends-on-field-data-pick-object-', $bidirectional_objects ) ) ); ?>" data-dependency-trigger="pods_sister_field">
									<?php echo PodsForm::label( 'field_data[' . $pods_i . '][sister_id]', __( 'Bi-directional Field', 'pods' ), __( 'Bi-directional fields will update their related field for any item you select. This feature is only available for two relationships between two Pods.<br /><br />For example, when you update a Parent pod item to relate to a Child item, when you go to edit that Child item you will see the Parent pod item selected.', 'pods' ) ); ?>

									<div class="pods-sister-field">
										<?php echo PodsForm::field( 'field_data[' . $pods_i . '][sister_id]', pods_v( 'sister_id', $field, '' ), 'text' ); ?>
									</div>
								</div>
							</div>
							<div class="pods-field-option-group">
								<p class="pods-field-option-group-label">
									<?php _e( 'Options', 'pods' ); ?>
								</p>

								<?php
								$required_option = PodsForm::field( 'field_data[' . $pods_i . '][required]', pods_v( 'required', $field, 0 ), 'boolean', array(
									'class'             => 'pods-dependent-toggle',
									'boolean_yes_label' => __( 'Required', 'pods' ),
									'help'              => __( 'help', 'pods' )
								) );
								if ( 'table' == $pod[ 'storage' ] ) {
									?>
									<div class="pods-pick-values pods-pick-checkbox">
										<ul>
											<li>
												<?php echo $required_option; ?>
											</li>
											<li class="pods-excludes-on pods-excludes-on-field-data-type pods-excludes-on-field-data-type-pick pods-excludes-on-field-data-type-file pods-excludes-on-field-data-type-boolean pods-excludes-on-field-data-type-date pods-excludes-on-field-data-type-datetime pods-excludes-on-field-data-type-time">
												<?php echo PodsForm::field( 'field_data[' . $pods_i . '][unique]', pods_v( 'unique', $field, 0 ), 'boolean', array(
													'class'             => 'pods-dependent-toggle',
													'boolean_yes_label' => __( 'Unique', 'pods' ),
													'help'              => __( 'help', 'pods' )
												) ); ?>
											</li>
										</ul>
									</div>
									<?php
								} else {
									echo $required_option;
								}
								?>
							</div>
						</div>

						<?php
						foreach ( $field_tabs as $tab => $tab_label ) {
							$tab = sanitize_title( $tab );

							if ( 'basic' === $tab || ! isset( $field_tab_options[ $tab ] ) || empty( $field_tab_options[ $tab ] ) ) {
								continue;
							}
							?>
							<div id="pods-<?php echo esc_attr( $tab ); ?>-options-<?php echo esc_attr( $pods_i ); ?>" class="pods-tab pods-<?php echo esc_attr( $tab ); ?>-options">
								<?php
								$field_tab_fields = $field_tab_options[ $tab ];

								if ( 'additional-field' === $tab ) {
									foreach ( $field_tab_fields as $field_type => $field_type_fields ) {
										$first_field = current( $field_type_fields );
										?>
										<div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-<?php echo esc_attr( sanitize_title( $field_type ) ); ?>">
											<?php
											if ( ! isset( $first_field[ 'name' ] ) && ! isset( $first_field[ 'label' ] ) ) {
												foreach ( $field_type_fields as $group => $group_fields ) {
													?>
													<h4><?php echo $group; ?></h4>
													<?php
													$field_options = PodsForm::fields_setup( $group_fields );

													include PODS_DIR . 'ui/admin/field-option.php';
												}
											} else {
												$field_options = PodsForm::fields_setup( $field_type_fields );

												include PODS_DIR . 'ui/admin/field-option.php';
											}
											?>
										</div>
										<?php
									}//end foreach
								} else {
									$first_field = current( $field_tab_fields );

									if ( ! isset( $first_field[ 'name' ] ) && ! isset( $first_field[ 'label' ] ) ) {
										foreach ( $field_tab_fields as $group => $group_fields ) {
											?>
											<h4><?php echo $group; ?></h4>
											<?php
											$field_options = PodsForm::fields_setup( $group_fields );

											include PODS_DIR . 'ui/admin/field-option.php';
										}
									} else {
										$field_options = PodsForm::fields_setup( $field_tab_fields );

										include PODS_DIR . 'ui/admin/field-option.php';
									}
								}//end if
								?>
							</div>
							<?php
						}//end foreach
						?>
					</div>

					<div class="pods-manage-row-actions submitbox">
						<div class="pods-manage-row-delete">
							<a class="submitdelete deletion" href="#delete-field"><?php _e( 'Delete Field', 'pods' ); ?></a>
						</div>
						<p class="pods-manage-row-save">
							<a class="pods-manage-row-cancel" href="#cancel-edit-field"><?php _e( 'Cancel', 'pods' ); ?></a> &nbsp;&nbsp;
							<a href="#save-field" class="button-primary pods-button-update"><?php _e( 'Update Field', 'pods' ); ?></a><a href="#save-field" class="button-primary pods-button-add"><?php _e( 'Save Field', 'pods' ); ?></a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</td>
	<td class="pods-manage-row-name">
		<a title="Edit this field" class="pods-manage-row-edit row-name" href="#edit-field"><?php echo esc_html( pods_v( 'name', $field ) ); ?></a>
	</td>
	<td class="pods-manage-row-type">
		<?php
		$type = 'Unknown';

		if ( isset( $field_types[ pods_v_sanitized( 'type', $field ) ] ) ) {
			$type = $field_types[ pods_v_sanitized( 'type', $field ) ][ 'label' ];
		}

		echo esc_html( $type ) . ' <span class="pods-manage-row-more">[type: ' . pods_v_sanitized( 'type', $field ) . ']</span>';

		if ( 'pick' == pods_v_sanitized( 'type', $field ) && '' != pods_v_sanitized( 'pick_object', $field, '' ) ) {
			$pick_object_name = null;

			foreach ( $field_settings[ 'pick_object' ] as $object => $object_label ) {
				if ( null !== $pick_object_name ) {
					break;
				}

				if ( '-- Select --' === $object_label ) {
					continue;
				}

				if ( is_array( $object_label ) ) {
					foreach ( $object_label as $sub_object => $sub_object_label ) {
						if ( $pick_object === $sub_object ) {
							$object = rtrim( $object, 's' );

							if ( false !== strpos( $object, 'ies' ) ) {
								$object = str_replace( 'ies', 'y', $object );
							}

							$pick_object_name = esc_html( $sub_object_label ) . ' <small>(' . esc_html( $object ) . ')</small>';

							break;
						}
					}
				} elseif ( pods_v_sanitized( 'pick_object', $field ) === $object ) {
					$pick_object_name = $object_label;

					break;
				}
			}//end foreach

			if ( null === $pick_object_name ) {
				$pick_object_name = ucwords( str_replace( array(
					'-',
					'_'
				), ' ', pods_v( 'pick_object', $field ) ) );

				if ( 0 < strlen( pods_v( 'pick_val', $field ) ) ) {
					$pick_object_name = pods_v( 'pick_val', $field ) . ' (' . $pick_object_name . ')';
				}
			}
			?>
			<br /><span class="pods-manage-field-type-desc">&rsaquo; <?php echo $pick_object_name; ?></span>
			<?php
		}//end if
		?>
	</td>
</tr>
