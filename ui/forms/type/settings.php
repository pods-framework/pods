<?php
/**
 * @var array         $fields
 * @var Pods          $pod
 * @var mixed         $id
 * @var string        $field_prefix
 * @var string        $field_row_classes
 * @var callable|null $value_callback
 * @var callable|null $pre_callback
 * @var callable|null $post_callback
 * @var string        $th_scope
 * @var PodsUI        $obj
 * @var array         $groups
 */

// @todo Abstract this logic better! START
$more_than_one_group = 1 < count( $groups );

$rendered_fields = [];

foreach ( $groups as $group ) {
	if ( empty( $group['fields'] ) ) {
		continue;
	}

	$fields = [];

	foreach ( $group['fields'] as $field ) {
		if ( isset( $rendered_fields[ $field['name'] ] ) ) {
			continue;
		}

		$fields[ $field['name'] ] = $field;

		$rendered_fields[ $field['name'] ] = true;
	}
	// @todo Abstract this logic better! END
	?>
	<div id="pods-settings-group-<?php echo esc_attr( sanitize_title( $group['label'] ) ); ?>">
		<?php if ( $more_than_one_group ) : ?>
			<h2><?php echo esc_html( $group['label'] ); ?></h2>
		<?php endif; ?>

		<table class="form-table pods-metabox">
			<?php
			$field_prefix = 'pods_field_';
			$id           = $pod->id();

			pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );
			?>
		</table>
	</div>
<?php } ?>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr( $obj->label['edit'] ); ?>">
	<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
</p>
