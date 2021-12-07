<style type="text/css">
	ol.pods_field_widget_form {
		list-style:   none;
		padding-left: 0;
		margin-left:  0;
	}

	ol.pods_field_widget_form label {
		display: block;
	}
</style>

<ol class="pods_field_widget_form">
	<li>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"> <?php _e( 'Title', 'pods' ); ?></label>

		<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" />
	</li>

	<li>
		<?php
		$api      = pods_api();
		$all_pods = $api->load_pods( array( 'names' => true ) );
		?>
		<label for="<?php echo esc_attr( $this->get_field_id( 'pod_type' ) ); ?>">
			<?php _e( 'Pod', 'pods' ); ?>
		</label>

		<?php if ( 0 < count( $all_pods ) ) : ?>
			<select id="<?php echo esc_attr( $this->get_field_id( 'pod_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'pod_type' ) ); ?>">
				<?php foreach ( $all_pods as $pod_name => $pod_label ) : ?>
					<option value="<?php echo esc_attr( $pod_name ); ?>" <?php selected( $pod_name, $pod_type ); ?>>
						<?php echo esc_html( $pod_label . ' (' . $pod_name . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php else : ?>
			<strong class="red"><?php _e( 'None Found', 'pods' ); ?></strong>
		<?php endif; ?>
	</li>

	<li>
		<label for="<?php echo esc_attr( $this->get_field_id( 'slug' ) ); ?>">
			<?php _e( 'Slug or ID', 'pods' ); ?>
		</label>

		<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'slug' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'slug' ) ); ?>" value="<?php echo esc_attr( $slug ); ?>" />
	</li>

    <li>
        <label for="<?php echo esc_attr( $this->get_field_id( 'use_current' ) ); ?>">
            <?php _e( 'Use current post (singular) or term (term archive)', 'pods' ); ?>
        </label>

        <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'use_current' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'use_current' ) ); ?>" value="1"<?php checked( $use_current, '1' ); ?> />
    </li>

	<li>
		<label for="<?php echo esc_attr( $this->get_field_id( 'field' ) ); ?>"><?php _e( 'Field', 'pods' ); ?></label>

		<input class="widefat" type="text" name="<?php echo esc_attr( $this->get_field_name( 'field' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'field' ) ); ?>" value="<?php echo esc_attr( $field ); ?>" />
	</li>
</ol>
