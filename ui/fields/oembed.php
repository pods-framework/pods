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
?>
	<p class="howto">
		<?php _e( 'Preview', 'pods' );?>
	</p>
	<input type="hidden" id="<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_preview_nonce" name="_nonce_pods_oembed" value="<?php echo wp_create_nonce( 'pods_field_embed_preview' ); ?>" />
    <div class="pods-oembed-preview">
        <?php echo PodsForm::field_method( $form_field_type, 'display', $value, $name, $options ); ?>
    </div>
	<script type="text/javascript">
		jQuery( function( $ ){
//https://www.youtube.com/watch?v=bYEE2i3nPOM
			var element_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = $( '#<?php echo esc_js( $css_id ); ?>' ),
				timeout_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = false,
				pods_ajaxurl = ajaxurl + '?pods_ajax=1';

			$(document).on('keyup', '#<?php echo esc_js( $attributes[ 'id' ] ); ?>', function(e){
				var value = $(this).val();
				delay(function(){
					postdata = {
						'action': 'oembed_update_preview',
						'_nonce_pods_oembed': $(this).parent().find('#<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_preview_nonce').val(),
						'pods_field_embed_value': value
					}
                    $.ajax({
                        type : 'POST',
                        url : pods_ajaxurl,
                        cache : false,
                        data : postdata,
                        success : function ( response ) {
                        	console.log(response);
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