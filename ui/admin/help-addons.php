<?php
/**
 * @var array $addons
 */
?>

<table class="pods-admin-help-info widefat striped fixed">
	<thead>
		<tr>
			<th></th>
			<th><?php esc_html_e( 'Plugin', 'pods' ); ?></th>
			<th><?php esc_html_e( 'Links', 'pods' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $addons as $addon ) : ?>
			<?php pods_view( PODS_DIR . 'ui/admin/help-addons-row.php', compact( array_keys( get_defined_vars() ) ) ); ?>
		<?php endforeach; ?>
	</tbody>
</table>
