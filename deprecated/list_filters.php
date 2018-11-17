<form method="get" class="filterbox filterbox_<?php echo esc_attr( $this->pod ); ?>" action="<?php echo esc_attr( $action ); ?>">
	<?php
	if ( ! empty( $filters ) ) {
		if ( ! is_array( $filters ) ) {
			$filters = explode( ',', $filters );
		}
		foreach ( $filters as $field_name ) {
			$field = $this->api->load_column(
				array(
					'name' => $field_name,
					'pod'  => $this->pod,
				)
			);
			if ( empty( $field ) ) {
				continue;
			}
			if ( 'pick' === $field['type'] && ! empty( $field['pick_object'] ) ) {
				$pick_object = $field['pick_object'];
				$pick_val    = $field['pick_val'];
				if ( 'pod' === $pick_object ) {
					$pick_pod    = $this->api->load_pod( array( 'name' => $pick_val ) );
					$pick_object = $pick_pod['type'];
					$pick_val    = $pick_pod['object'];
				}
				$pick_table     = '';
				$pick_join      = '';
				$pick_where     = '';
				$pick_column_id = 'id';
				switch ( $pick_object ) {
					case 'pod':
						$pick_table     = "@wp_pods_{$pick_val}";
						$pick_column_id = 'id';
						break;
					case 'post_type':
						$pick_table     = '@wp_posts';
						$pick_column_id = 'ID';
						$pick_where     = "t.`post_type` = '{$pick_val}'";
						break;
					case 'taxonomy':
						$pick_table     = '@wp_terms';
						$pick_column_id = 'term_id';
						$pick_join      = '`@wp_term_taxonomy` AS tx ON tx.`term_id` = t.`term_id';
						$pick_where     = "tx.`taxonomy` = '{$pick_val}' AND tx.`taxonomy` IS NOT NULL";
						break;
					case 'user':
						$pick_table     = '@wp_users';
						$pick_column_id = 'ID';
						break;
					case 'comment':
						$pick_table     = '@wp_comments';
						$pick_column_id = 'comment_ID';
						$pick_where     = "t.`comment_type` = '{$pick_val}'";
						break;
					case 'table':
						$pick_table     = "{$pick_val}";
						$pick_column_id = 'id';
						break;
				}//end switch
				$pick_params = array(
					'selected_ids' => $selected_ids,
					'table'        => $pick_table,
					'column'       => $pick_column_id,
					'column_name'  => $field_name,
					'join'         => $pick_join,
					'orderby'      => $field['options']['pick_orderby'],
					'where'        => $pick_where,
				);
				$field_data  = $this->get_dropdown_values( $pick_params );
				$field_label = ucwords( str_replace( '_', ' ', $field_name ) );
				if ( 0 < strlen( $row['label'] ) ) {
					$field_label = $row['label'];
				}
				?>
				<select name="<?php echo esc_attr( $field_name ); ?>" id="filter_<?php echo esc_attr( $field_name ); ?>" class="filter <?php echo esc_attr( $field_name ); ?>">
					<option value="">-- <?php echo esc_attr( $field_label ); ?> --</option>
					<?php
					foreach ( $field_data as $val ) {
						$active = ( empty( $val['active'] ) ) ? '' : ' selected';
						$value  = $val['id'];
						if ( 'text' === $this->search_mode ) {
							$value = $val['name'];
						}
						?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php echo $active; ?>><?php echo esc_html( $val['name'] ); ?></option>
						<?php
					}
					?>
				</select>
				<?php
			}//end if
		}//end foreach
	}//end if
	// Display the search box and submit button
	$search = empty( $_GET[ $this->search_var ] ) ? '' : pods_unslash( $_GET[ $this->search_var ] );
	if ( false !== $show_textbox ) {
		?>
		<input type="text" class="pod_search" name="<?php echo esc_attr( $this->search_var ); ?>" value="<?php echo esc_attr( $search ); ?>" />
		<?php
	} else {
		?>
		<input type="hidden" name="<?php echo esc_attr( $this->search_var ); ?>" value="1" />
		<input type="hidden" name="<?php echo esc_attr( $this->search_var ); ?>_min" value="1" />
		<?php
	}
	?>
	<input type="submit" class="pod_submit" value="<?php echo esc_attr( $label ); ?>" />
</form>
