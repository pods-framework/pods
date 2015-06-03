<?php
/**
 * @package  Pods
 * @category Admin
 */
?>
<style type="text/css">
	ol.pods_list_widget_form {
		list-style: none;
		padding-left: 0;
		margin-left: 0;
	}

	ol.pods_list_widget_form label {
		display: block;
	}
</style>

<p>
	<em><?php _e( 'You must specify a Pods Template or create a custom template, using <a href="http://pods.io/docs/build/using-magic-tags/" title="Using Magic Tags" target="_blank">magic tags</a>.', 'pods' ); ?>
</p></em>

<ol class="pods_list_widget_form">
<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"> <?php _e( 'Title', 'pods' ); ?></label>

	<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" />
</li>

<li>
	<?php
	$api = pods_api();
	$all_pods = $api->load_pods( array( 'names' => true ) );
	?>
	<label for="<?php echo esc_attr( $this->get_field_id( 'pod_type' ) ); ?>">
		<?php _e( 'Pod', 'pods' ); ?>
	</label>

	<?php if ( 0 < count( $all_pods ) ): ?>
		<select id="<?php echo esc_attr( $this->get_field_id( 'pod_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'pod_type' ) ); ?>">
			<?php foreach ( $all_pods as $pod_name => $pod_label ): ?>
				<option value="<?php echo esc_attr( $pod_name ); ?>"<?php selected( $pod_name, $pod_type ); ?>>
					<?php echo esc_html( $pod_label . ' (' . $pod_name . ')' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	<?php else: ?>
		<strong class="red"><?php _e( 'None Found', 'pods' ); ?></strong>
	<?php endif; ?>
</li>

<?php
if ( class_exists( 'Pods_Templates' ) ) {
	?>
	<li>
		<?php
		$all_templates = (array) $api->load_templates( array() );
		?>
		<label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>"> <?php _e( 'Template', 'pods' ); ?> </label>

		<select name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>">
			<option value="">- <?php _e( 'Custom Template', 'pods' ); ?> -</option>
			<?php foreach ( $all_templates as $tpl ): ?>
				<option value="<?php echo esc_attr( $tpl[ 'name' ] ); ?>"<?php echo selected( $tpl[ 'name' ], $template ); ?>>
					<?php echo esc_html( $tpl[ 'name' ] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</li>
<?php
} else {
	?>
	<li>
		<label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>"> <?php _e( 'Template', 'pods' ); ?> </label>

		<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>" value="<?php echo esc_attr( $template ); ?>" />
	</li>
<?php
}
?>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'before_content' ) ); ?>"> <?php _e( 'Before Content', 'pods' ); ?></label>

	<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'before_content' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'before_content' ) ); ?>" value="<?php echo esc_attr( $before_content ); ?>" />
</li>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'template_custom' ) ); ?>"> <?php _e( 'Custom Template', 'pods' ); ?></label>

	<textarea name="<?php echo esc_attr( $this->get_field_name( 'template_custom' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'template_custom' ) ); ?>" cols="10" rows="10" class="widefat"><?php echo esc_html( $template_custom ); ?></textarea>
</li>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'after_content' ) ); ?>"> <?php _e( 'After Content', 'pods' ); ?></label>

	<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'after_content' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_content' ) ); ?>" value="<?php echo esc_attr( $after_content ); ?>" />
</li>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php _e( 'Limit', 'pods' ); ?></label>

	<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" value="<?php echo esc_attr( $limit ); ?>" />
</li>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php _e( 'Order By', 'pods' ); ?></label>

	<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" value="<?php echo esc_attr( $orderby ); ?>" />
</li>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'where' ) ); ?>"><?php _e( 'Where', 'pods' ); ?></label>

	<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'where' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'where' ) ); ?>" value="<?php echo esc_attr( $where ); ?>" />
</li>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'cache_mode' ) ); ?>"><?php _e( 'Cache Type', 'pods' ); ?></label>

	<?php
	$cache_modes = array(
		'none'           => __( 'Disable Caching', 'pods' ),
		'cache'          => __( 'Object Cache', 'pods' ),
		'transient'      => __( 'Transient', 'pods' ),
		'site-transient' => __( 'Site Transient', 'pods' )
	);
	?>
	<select id="<?php echo esc_attr( $this->get_field_id( 'cache_mode' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cache_mode' ) ); ?>">
		<?php foreach ( $cache_modes as $cache_mode_value => $cache_mode_label ): ?>
			<option value="<?php echo esc_attr( $cache_mode_value ); ?>"<?php selected( $cache_mode_value, $cache_mode ); ?>>
				<?php echo esc_html( $cache_mode_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</li>

<li>
	<label for="<?php echo esc_attr( $this->get_field_id( 'expires' ) ); ?>"><?php _e( 'Cache Expiration (in seconds)', 'pods' ); ?></label>

	<input class="widefat" type="text" name="<?php echo esc_attr( $this->get_field_name( 'expires' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'expires' ) ); ?>" value="<?php echo esc_attr( $expires ); ?>" />
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'filters' ); ?>"><?php _e( 'Filters', 'pods' ); ?></label>

	<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'filters' ); ?>" id="<?php echo $this->get_field_id( 'filters' ); ?>" value="<?php echo esc_attr( $filters ); ?>" />
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'filters_location' ); ?>"><?php _e( 'Filters Location', 'pods' ); ?></label>

	<?php
	$locations = array(
		'before' => __( 'Before (default)', 'pods' ),
		'after'  => __( 'After', 'pods' ),
		'both'   => __( 'Both', 'pods' )
	);
	?>
	<select id="<?php echo $this->get_field_id( 'filters_location' ); ?>" name="<?php echo $this->get_field_name( 'filters_location' ); ?>">
		<?php foreach ( $locations as $location_value => $location_label ): ?>
			<option value="<?php echo $location_value; ?>"<?php selected( $location_value, $filters_location ); ?>>
				<?php echo esc_html( $location_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'filters_label' ); ?>"><?php _e( 'Filters Label', 'pods' ); ?></label>

	<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'filters_label' ); ?>" id="<?php echo $this->get_field_id( 'filters_label' ); ?>" value="<?php echo esc_attr( $filters_label ); ?>" />
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'pagination' ); ?>"><?php _e( 'Show Pagination links', 'pods' ); ?></label>

	<input type="checkbox" name="<?php echo $this->get_field_name( 'pagination' ); ?>" id="<?php echo $this->get_field_id( 'pagination' ); ?>" value="1" <?php selected( 1, $pagination ); ?> />
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'pagination_label' ); ?>"><?php _e( 'Pagination Label', 'pods' ); ?></label>

	<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'pagination_label' ); ?>" id="<?php echo $this->get_field_id( 'pagination_label' ); ?>" value="<?php echo esc_attr( $pagination_label ); ?>" />
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'pagination_location' ); ?>"><?php _e( 'Pagination Location', 'pods' ); ?></label>

	<?php
	$locations = array(
		'before' => __( 'Before', 'pods' ),
		'after'  => __( 'After (default)', 'pods' ),
		'both'   => __( 'Both', 'pods' )
	);
	?>
	<select id="<?php echo $this->get_field_id( 'pagination_location' ); ?>" name="<?php echo $this->get_field_name( 'pagination_location' ); ?>">
		<?php foreach ( $locations as $location_value => $location_label ): ?>
			<option value="<?php echo $location_value; ?>"<?php selected( $location_value, $pagination_location ); ?>>
				<?php echo esc_html( $location_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'pagination_type' ); ?>"><?php _e( 'Pagination Type', 'pods' ); ?></label>

	<?php
	$pagination_types = array(
		'advanced' => __( 'Advanced (default)', 'pods' ),
		'simple'   => __( 'Simple', 'pods' ),
		'paginate' => __( 'Paginate (paginate_links plain type)', 'pods' ),
		'list'     => __( 'List (paginate_links list type)', 'pods' )
	);
	?>
	<select id="<?php echo $this->get_field_id( 'pagination_type' ); ?>" name="<?php echo $this->get_field_name( 'pagination_type' ); ?>">
		<?php foreach ( $pagination_types as $pagination_type_value => $pagination_type_label ): ?>
			<option value="<?php echo $pagination_type_value; ?>"<?php selected( $pagination_type_value, $pagination_type ); ?>>
				<?php echo esc_html( $pagination_type_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'before' ); ?>"><?php _e( 'Before Text', 'pods' ); ?></label>

	<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'before' ); ?>" id="<?php echo $this->get_field_id( 'before' ); ?>" value="<?php echo esc_attr( $before ); ?>" />
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'after' ); ?>"><?php _e( 'After Text', 'pods' ); ?></label>

	<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'after' ); ?>" id="<?php echo $this->get_field_id( 'after' ); ?>" value="<?php echo esc_attr( $after ); ?>" />
</li>

<li>
	<label for="<?php echo $this->get_field_id( 'shortcodes' ); ?>"><?php _e( 'Enable Shortcodes in output', 'pods' ); ?></label>

	<input type="checkbox" name="<?php echo $this->get_field_name( 'shortcodes' ); ?>" id="<?php echo $this->get_field_id( 'shortcodes' ); ?>" value="1" <?php selected( 1, $shortcodes ); ?> />
</li>
</ol>
