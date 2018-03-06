<?php
$depends_on = false;

foreach ( $field_options as $field_name => $field_option ) {
	if ( false !== strpos( $field_name, 'helper' ) && ! class_exists( 'Pods_Helpers' ) ) {
		continue;
	} elseif ( $field_option['developer_mode'] && ! pods_developer() ) {
		continue;
	}

	$field_option = (array) $field_option;

	$dep_options = PodsForm::dependencies( $field_option, ( ! isset( $pods_tab_form ) ? 'field-data-' : '' ) );
	$dep_classes = $dep_options['classes'];
	$dep_data    = $dep_options['data'];

	if ( ( ! empty( $depends_on ) || ! empty( $dep_classes ) ) && $depends_on != $dep_classes ) {
		if ( ! empty( $depends_on ) ) {
			?>
			</div>
			<?php
		}
		if ( ! empty( $dep_classes ) ) {
			?>
			<div class="pods-field-option-container <?php echo esc_attr( $dep_classes ); ?>" <?php PodsForm::data( $dep_data ); ?>>
			<?php
		}
	}

	if ( ! is_array( $field_option['group'] ) ) {
		$row_name = $field_name;

		if ( ! isset( $pods_tab_form ) ) {
			$row_name = 'field_data[' . $pods_i . '][' . $field_name . ']';
		}

		$value = $field_option['default'];

		if ( isset( $field_option['value'] ) && 0 < strlen( $field_option['value'] ) ) {
			$value = $field_option['value'];
		} else {
			$value = pods_v( $field_name, $field, $value );
		}

		if ( in_array( $field_option['type'], PodsForm::file_field_types(), true ) ) {
			if ( is_array( $value ) && ! isset( $value['id'] ) ) {
				foreach ( $value as $k => $v ) {
					if ( isset( $v['id'] ) ) {
						$value[ $k ] = $v['id'];
					}
				}
			}
		}
		?>
		<div class="pods-field-option">
			<?php echo PodsForm::row( $row_name, $value, $field_option['type'], $field_option ); ?>
		</div>
		<?php
	} else {
		?>
		<div class="pods-field-option-group">
			<p class="pods-field-option-group-label">
				<?php echo $field_option['label']; ?>
			</p>

			<div class="pods-pick-values pods-pick-checkbox">
				<ul>
					<?php
					foreach ( $field_option['group'] as $field_group_name => $field_group_option ) {
						$field_group_option = (array) $field_group_option;

						if ( 'boolean' !== $field_group_option['type'] ) {
							continue;
						}

						$field_group_option['boolean_yes_label'] = $field_group_option['label'];

						$group_dep_options = PodsForm::dependencies( $field_group_option, ( ! isset( $pods_tab_form ) ? 'field-data-' : '' ) );
						$group_dep_classes = $group_dep_options['classes'];
						$group_dep_data    = $group_dep_options['data'];

						$row_name = $field_group_name;

						if ( ! isset( $pods_tab_form ) ) {
							$row_name = 'field_data[' . $pods_i . '][' . $field_group_name . ']';
						}

						$value = $field_group_option['default'];

						if ( isset( $field_group_option['value'] ) && 0 < strlen( $field_group_option['value'] ) ) {
							$value = $field_group_option['value'];
						} else {
							$value = pods_v( $field_group_name, $field, $value );
						}

						?>
						<li class="<?php echo esc_attr( $group_dep_classes ); ?>" <?php PodsForm::data( $group_dep_data ); ?>>
							<?php echo PodsForm::field( $row_name, $value, $field_group_option['type'], $field_group_option ); ?>
						</li>
						<?php
					}//end foreach
					?>
				</ul>
			</div>
		</div>
		<?php
	}//end if

	if ( false !== $depends_on || ! empty( $dep_classes ) ) {
		$depends_on = $dep_classes;
	}
}//end foreach

if ( ! empty( $depends_on ) ) {
	?>
	</div>
	<?php
}
