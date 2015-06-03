<?php
/**
 * @package  Pods
 * @category Display
 */
?>
<form method="get" class="pods-form-filters pods-form-filters-<?php echo esc_attr( $pod->pod ); ?>" action="<?php echo esc_attr( $action ); ?>">
	<input type="hidden" name="type" value="<?php echo esc_attr( $pod->pod ); ?>" />

	<?php
	foreach ( $fields as $name => $field ) {
		if ( 'pick' == $field[ 'type' ] && 'pick-custom' != $field[ 'pick_object' ] && ! empty( $field[ 'pick_object' ] ) ) {
			$field[ 'pick_format_type' ] = 'single';
			$field[ 'pick_format_single' ] = 'dropdown';
			$field[ 'pick_select_text' ] = '-- ' . $field[ 'label' ] . ' --';

			$filter = pods_v( 'filter_' . $name, 'get', '' );

			echo Pods_Form::field( 'filter_' . $name, $filter, 'pick', $field, $pod->pod, $pod->id() );
		}
	}
	?>

	<input type="text" class="pods-form-filters-search" name="<?php echo esc_attr( $pod->search_var ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $search ); ?>" />

	<input type="submit" class="pods-form-filters-submit" value="<?php echo esc_attr( $label ); ?>" />
</form>
