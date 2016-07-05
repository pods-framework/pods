<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $field_type      string
 * @var $value           array
 * @var $id              string
 */
wp_enqueue_style( 'pods-flex' );
wp_enqueue_script( 'pods-mv-fields' );

$data = (array) pods_v( 'data', $options, array(), null, true );
unset ( $options[ 'data' ] );
$options[ 'item_id' ] = (int) $id;

$model_data = array();

$supports_thumbnails = null;

foreach ( $data as $this_id => $this_title ) {
	$icon = '';
	$edit_link = '';
	$link = '';

	switch ( $options[ 'pick_object' ] ) {
		case 'post_type':
			if ( null === $supports_thumbnails ) {
				$supports_thumbnails = post_type_supports( $options['pick_val'], 'thumbnail' );
			}

			if ( true === $supports_thumbnails ) {
				$thumb = wp_get_attachment_image_src( $this_id, 'thumbnail', true );

				if ( ! empty( $thumb[0] ) ) {
					$icon = $thumb[0];
				}
			}

			$edit_link = get_edit_post_link( $this_id, 'raw' );
			$link = get_permalink( $this_id );
			break;

		case 'taxonomy':
			$edit_link = get_edit_term_link( $this_id, $options['pick_val'] );
			$link = get_term_link( $this_id, $options['pick_val'] );
			break;

		case 'user':
			$icon = get_avatar_url( $this_id, array( 'size' => 150 ) );
			$edit_link = get_edit_user_link( $this_id );
			$link = get_author_posts_url( $this_id );
			break;

		case 'pod':
			$file_name = 'admin.php';
			$query_args = array(
				'page'   => 'pods-manage-' . $options[ 'pick_val' ],
				'action' => 'edit',
				'id'     => $this_id,
			);

			$edit_link = add_query_arg( $query_args, admin_url( $file_name ) );
			// @todo Add $link support
			break;

		// Something unsupported
		default:
			break;
	}

	$model_data[] = array(
		'id'        => $this_id,
		'icon'      => $icon,
		'name'      => $this_title,
		'edit_link' => $edit_link,
		'link'      => $link,
		'selected'  => ( isset( $value[ $this_id ] ) ),
	);
}

$attributes = PodsForm::merge_attributes( array(), $name, $form_field_type, $options );
$attributes = array_map( 'esc_attr', $attributes );
$field_meta = array(
	'field_attributes' => array(
		'id'         => $attributes[ 'id' ],
		'class'      => $attributes[ 'class' ],
		'name'       => $attributes[ 'name' ],
		'name_clean' => $attributes[ 'data-name-clean' ]
	),
	'field_options'    => $options
);

// Set the file name and args based on the content type of the relationship
switch ( $options[ 'pick_object' ] ) {
	case 'post_type':
		$file_name = 'post-new.php';
		$query_args = array(
			'post_type' => $options[ 'pick_val' ],
		);
		break;

	case 'taxonomy':
		$file_name = 'edit-tags.php';
		$query_args = array(
			'taxonomy' => $options[ 'pick_val' ],
		);
		break;

	case 'user':
		$file_name = 'user-new.php';
		$query_args = array();
		break;

	case 'pod':
		$file_name = 'admin.php';
		$query_args = array(
			'page'   => 'pods-manage-' . $options[ 'pick_val' ],
			'action' => 'add'
		);
		break;

	// Something unsupported
	default:
		$file_name = '';
		$query_args = array();
		break;
}

// Add args we always need
$query_args = array_merge(
	$query_args,
	array(
		'pods_modal' => '1', // @todo: Replace string literal with defined constant
	)
);

$field_meta[ 'field_options' ][ 'iframe_src' ] = add_query_arg( $query_args, admin_url( $file_name ) );

// Assemble the URL
$url = add_query_arg( $query_args, admin_url( $file_name ) );

include_once PODS_DIR . 'classes/PodsFieldData.php';
$field_data = new PodsUIFieldData( $field_type, array( 'model_data' => $model_data, 'field_meta' => $field_meta ) );
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ], 'id' => $attributes[ 'id' ] ), $name, $form_field_type, $options ); ?>>
	<?php if ( ! empty( $file_name ) ) { ?>
		<?php $field_data->emit_script(); ?>
	<?php } else { ?>
		<p>This related object does not support Flexible Relationships.</p>
	<?php } ?>
</div>
