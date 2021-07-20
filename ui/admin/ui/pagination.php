<?php
/**
 * @var $ui PodsUI
 */
if ( 1 < $total_pages ) {
	$first_link = esc_url( $request_uri . ( $append ? '&' : '?' ) . $ui->num_prefix . 'pg' . $ui->num . '=1' );
	$prev_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . $ui->num_prefix . 'pg' . $ui->num . '=' . max( $ui->page - 1, 1 ) );
	$next_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . $ui->num_prefix . 'pg' . $ui->num . '=' . min( $ui->page + 1, $total_pages ) );
	$last_link  = esc_url( $request_uri . ( $append ? '&' : '?' ) . $ui->num_prefix . 'pg' . $ui->num . '=' . $total_pages );

	$classes = '';
	if ( 1 >= $ui->page ) {
		$classes .= ' disabled';
	}
	if ( is_admin() ) {
		$classes .= ' button';
	}
	?>
	<a class="first-page<?php echo esc_attr( $classes ); ?>"
	   title="<?php esc_attr_e( 'Go to the first page', 'pods' ); ?>" href="<?php echo $first_link; ?>">&laquo;</a>
	<a class="prev-page<?php echo esc_attr( $classes ); ?>"
	   title="<?php esc_attr_e( 'Go to the previous page', 'pods' ); ?>" href="<?php echo $prev_link; ?>">&lsaquo;</a>
	<?php
	if ( true == $header ) {
		?>
		<span class="paging-input"><input class="current-page" title="<?php esc_attr_e( 'Current page', 'pods' ); ?>"
										  type="text"
										  name="<?php echo esc_attr( $ui->num_prefix ); ?>pg<?php echo esc_attr( $ui->num ); ?>"
										  value="<?php esc_attr_e( absint( $ui->page ) ); ?>"
										  size="<?php esc_attr_e( strlen( $total_pages ) ); ?>"> <?php _e( 'of', 'pods' ); ?>
						<span class="total-pages"><?php echo absint( $total_pages ); ?></span></span>
		<script type="text/javascript">
			document.addEventListener( 'DOMContentLoaded', function ( event ) {
				var pageInput = jQuery( 'input.current-page' );
				var currentPage = pageInput.val();
				pageInput.closest( 'form' ).submit( function ( e ) {
					if ( ( 1 > jQuery( 'select[name="<?php echo esc_attr( $ui->num_prefix ); ?>action<?php echo esc_attr( $ui->num ); ?>"]' ).length || jQuery( 'select[name="<?php echo esc_attr( $ui->num_prefix ); ?>action<?php echo esc_attr( $ui->num ); ?>"]' ).val() == -1 ) && ( 1 > jQuery( 'select[name="<?php echo esc_attr( $ui->num_prefix ); ?>action_bulk<?php echo esc_attr( $ui->num ); ?>"]' ).length || jQuery( 'select[name="<?php echo esc_attr( $ui->num_prefix ); ?>action_bulk<?php echo esc_attr( $ui->num ); ?>"]' ).val() == -1 ) && pageInput.val() == currentPage ) {
						pageInput.val( '1' );
					}
				} );
			} );
		</script>
		<?php
	} else {
		?>
		<span class="paging-input"><?php echo absint( $ui->page ); ?> <?php _e( 'of', 'pods' ); ?>
						<span class="total-pages"><?php echo number_format_i18n( $total_pages ); ?></span></span>
		<?php
	}//end if
	$classes = '';
	if ( $ui->page >= $total_pages ) {
		$classes .= ' disabled';
	}
	if ( is_admin() ) {
		$classes .= ' button';
	}
	?>
	<a class="next-page<?php echo esc_attr( $classes ); ?>"
	   title="<?php esc_attr_e( 'Go to the next page', 'pods' ); ?>" href="<?php echo $next_link; ?>">&rsaquo;</a>
	<a class="last-page<?php echo esc_attr( $classes ); ?>"
	   title="<?php esc_attr_e( 'Go to the last page', 'pods' ); ?>" href="<?php echo $last_link; ?>">&raquo</a>
	<?php
}//end if
