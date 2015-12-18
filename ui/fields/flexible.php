<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $value           mixed
 */
$pick_object = pods_v( 'pick_object', $options );
$pick_val    = pods_v( 'pick_val', $options );

if ( empty( $pick_object ) || ( in_array( $pick_object, array( 'post_type', 'taxonomy', 'pod' ) ) && empty( $pick_val ) ) ) {
	return;
}

$pick_limit = 1;

// @todo: Pick limit not yet supported in the UI, it's either single or multiple
if ( 'multi' == pods_var( $form_field_type . '_format_type', $options, 'single' ) ) {
	$pick_limit = (int) pods_var( $form_field_type . '_limit', $options, 0 );
}

$url_new      = '';
$url_new_args = array();

$url_edit      = '';
$url_edit_args = array();

// Set the file name and args based on the content type of the relationship
switch ( $pick_object ) {
	case 'post_type':
		$url_new      = 'post-new.php';
		$url_new_args = array(
			'post_type' => $pick_val,
		);

		$url_edit      = 'post.php';
		$url_edit_args = array(
			'action' => 'edit',
			'post'   => '',
		);
		break;

	case 'taxonomy':
		$url_new      = 'edit-tags.php';
		$url_new_args = array(
			'taxonomy' => $pick_val,
		);

		$url_edit      = 'edit-tags.php';
		$url_edit_args = array(
			'taxonomy' => $pick_val,
			'action'   => 'edit',
			'tag_ID'   => '',
		);
		break;

	case 'user':
		$url_new      = 'user-new.php';
		$url_new_args = array();

		$url_edit      = 'user-edit.php';
		$url_edit_args = array(
			'user_id' => '',
		);
		break;

	case 'comment':
		$url_edit      = 'comment.php';
		$url_edit_args = array(
			'action' => 'editcomment',
			'c'      => '',
		);
		break;

	case 'pod':
		$url_new       = 'admin.php';
		$url_edit_args = array(
			'page'   => 'pods-manage-' . $pick_val,
			'action' => 'add',
		);

		$url_edit      = 'admin.php';
		$url_edit_args = array(
			'page'   => 'pods-manage-' . $pick_val,
			'action' => 'edit',
			'id'     => '',
		);
		break;

	default:
		return;
}

wp_enqueue_script( 'pods-handlebars' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );

/**
 * @var $field_pick PodsField_Pick
 */
$field_pick = PodsForm::field_loader( 'pick' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

$css_id = $attributes['id'];

// Add args we always need
$url_new_args = array_merge( $url_new_args, array(
	'pods_modal' => '1',    // @todo: Replace string literal with defined constant
	'TB_iframe'  => 'true', // @todo: Remove when we use new modal script
	'width'      => '753',  // @todo: This needs to be responsive with new modal, make sure it is
	'height'     => '798',  // @todo: This needs to be responsive with new modal, make sure it is
) );

// Assemble the URL
$url = add_query_arg( $url_new_args, admin_url( $url_new ) );
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes['class'], 'id' => $attributes['id'] ), $name, $form_field_type, $options ); ?>>
	<ul class="pods-flexible-list pods-flexible-input"><?php // no extra space in ul or CSS:empty won't work
		foreach ( $value as $val ) {
			// @todo figure out what to do here
			//echo $field_pick->markup( $attributes, $pick_limit, $title_editable, $attachment[ 'ID' ], $attachment[ 'post_title' ] );
		}
		?></ul>

	<a href="<?php echo esc_url( $url ); ?>"
		id="<?php echo esc_attr( $css_id ); ?>-add"
		class="button pods-flexible-add pods-related-edit"<?php // @todo Remove .pods-related-edit ?>
		data-pod-id="<?php echo esc_attr( $field['pod_id'] ); ?>"
		data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
		data-item-id="<?php echo esc_attr( $id ); ?>">
		<?php echo esc_html( pods_v( $form_field_type . '_add_button', $options, __( 'Add New', 'pods' ) ) ); ?>
	</a>
</div>

<script type="text/x-handlebars" id="<?php echo esc_attr( $css_id ); ?>-handlebars">
	<?php echo $field_pick->markup( $attributes, $pick_limit, $title_editable, null, null, null, $linked ); ?>
</script>