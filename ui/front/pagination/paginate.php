<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>

<?php if ( $wrap_pagination ) : ?>
<p class="pods-pagination">
<?php endif; ?>

	<span class="pods-pagination-paginate <?php echo esc_attr( $params->class ); ?>">
		<?php
		$args = [
			'base'      => $params->base,
			'format'    => $params->format,
			'total'     => $params->total,
			'current'   => $params->page,
			'end_size'  => $params->end_size,
			'mid_size'  => $params->mid_size,
			'prev_next' => $params->prev_next,
			'prev_text' => $params->prev_text,
			'next_text' => $params->next_text,
		];

		echo paginate_links( $args );
		?>
	</span>

<?php if ( $wrap_pagination ) : ?>
</p>
<?php endif; ?>
