<?php
	$attributes = array();
	$attributes[ 'type' ] = 'text';
	$attributes[ 'value' ] = $value;
	$attributes[ 'tabindex' ] = 2;
	$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

	if ( pods_var( 'readonly', $options, false ) ) {
		$attributes[ 'readonly' ] = 'READONLY';

		$attributes[ 'class' ] .= ' pods-form-ui-read-only';
	}

	$show_preview = (int) pods_v( $form_field_type . '_show_preview', $options, 0 );
?>
	<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<?php 
if ( 1 == $show_preview ) {
	$oembed_width = ( isset( $options['oembed_width'] ) ) ? (int) $options['oembed_width'] : 0;
	$oembed_height = ( isset( $options['oembed_height'] ) ) ? (int) $options['oembed_height'] : 0;
?>
	<p class="howto">
		<?php _e( 'Preview', 'pods' );?>
	</p>
	<input type="hidden" id="<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_preview_nonce" name="_nonce_pods_oembed" value="<?php echo wp_create_nonce( 'pods_field_oembed_preview' ); ?>" />
	<div id="<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_preview" class="pods-oembed-preview">
		<?php echo PodsForm::field_method( $form_field_type, 'display', $value, $name, $options ); ?>
	</div>
	<script type="text/javascript">
		jQuery( function( $ ){
			var element_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = $( '#<?php echo esc_js( $css_id ); ?>' ),
				timeout_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = false,
				pods_ajaxurl = ajaxurl + '?pods_ajax=1';

			$(document).on('keyup', '#<?php echo esc_js( $attributes[ 'id' ] ); ?>', function(e){
				var value = $(this).val();
				var name = '<?php echo $name; ?>';
				var options = {
					oembed_width: '<?php echo $oembed_width; ?>',
					oembed_height: '<?php echo $oembed_height; ?>',
				};
				var nonce = $(this).parent().find('#<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_preview_nonce').val()
				delay(function(){
					postdata = {
						'action': 'oembed_update_preview',
						'_nonce_pods_oembed': nonce,
						'pods_field_oembed_value': value,
						'pods_field_oembed_name': name,
						'pods_field_oembed_options': options
					}
					$.ajax({
						type : 'POST',
						url : pods_ajaxurl,
						cache : false,
						data : postdata,
						success : function ( response ) {
							if ( response.success ) {
								$('#<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_preview').html( response.data );
							} else {
								$('#<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_preview').html( '' );
							}
						}
					});
				}, 500);
			});

			var delay = (function(){
				var timer = 0;
				return function(callback, ms){
					clearTimeout (timer);
					timer = setTimeout(callback, ms);
				};
			})();

		});
	</script>
<?php
}
PodsForm::regex( $form_field_type, $options );