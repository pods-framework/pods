<?php if ( ! empty( $atts['pod'] ) ) : ?>
	<p><?php esc_html_e( 'Magic tags can be used in your template code to reference field values from the pod.', 'pods' ); ?></p>
	<p><strong><?php esc_html_e( 'Example', 'pods' ); ?>:</strong> <code>{@field_name}</code></p>
	<p>
		<a href="https://docs.pods.io/displaying-pods/magic-tags/" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'Read more about magic tags', 'pods' ); ?></a>
		 | <a href="https://docs.pods.io/displaying-pods/template-tags/" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'Read more about template tags like [if], [else], [each], and more', 'pods' ); ?></a>
	</p>
	<?php
	$fields = pq_loadpod( $atts['pod'] );
	if ( ! empty( $fields ) ) :
		?>
		<div id="pods-magic-tag-list" class="pods-magic-tag-list">
			<dl>
				<dt><?php esc_html_e( 'Available magic tags', 'pods' ); ?></dt>
				<dd><em><?php esc_html_e( 'You can click to copy any tag', 'pods' ); ?></em></dd>
				<?php
				foreach ( $fields as $field ) :
					?>
					<dd class="pods-magic-tag-option" data-tag="<?php echo esc_attr( $field ); ?>" title="<?php esc_attr_e( 'Field', 'pods' ); ?>: <?php echo esc_attr( $field ); ?>">
						<span><?php echo esc_html( $field ); ?></span>
					</dd>
				<?php
				endforeach;
				?>
			</dl>
		</div>
	<?php
	endif;
	?>
<?php else : ?>
	<p><?php esc_html_e( 'Selecting a Pod will allow you to see the list of available magic tags that can be used in your template code.', 'pods' ); ?></p>
	<p><strong><?php esc_html_e( 'Example', 'pods' ); ?>: <code>{@field_name}</code></p>
<?php
endif;
