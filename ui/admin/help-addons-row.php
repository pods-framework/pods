<?php
/**
 * @var array $addon
 */

$first_link = null;

foreach ( $addon['links'] as $link ) {
	if ( __( 'Download', 'pods' ) === $link['label'] ) {
		continue;
	}

	$first_link = $link['url'];
	break;
}

$first_host = pods_host_from_url( $first_link );
?>

<tr>
	<td>
		<a href="<?php echo esc_url( $first_link ); ?>" target="_blank" rel="noopener noreferrer"
			title="<?php echo esc_attr( sprintf( __( 'View Plugin on %s', 'pods' ), $first_host ) ); /* translators: %s is the domain host. */ ?>">
			<img width="50" height="50" src="<?php echo esc_url( $addon['icon'] ); ?>"
				class="attachment-thumbnail size-thumbnail" alt="<?php echo esc_attr( $addon['label'] ); ?>" loading="lazy" />
		</a>
	</td>
	<td>
		<a href="<?php echo esc_url( $first_link ); ?>" target="_blank" rel="noopener noreferrer"
			title="<?php echo esc_attr( sprintf( __( 'View Plugin on %s', 'pods' ), $first_host ) ); /* translators: %s is the domain host. */ ?>">
			<?php echo esc_html( $addon['label'] ); ?>
		</a>

		<?php if ( ! empty( $addon['description'] ) ) : ?>
			<p><?php echo esc_html( $addon['description'] ); ?></p>
		<?php endif; ?>
	</td>
	<td>
		<?php
		$addon_links = [];

		foreach ( $addon['links'] as $link ) {
			if ( empty( $link['title'] ) ) {
				$link['title'] = $link['label'];
			}

			$addon_links[] = sprintf(
				'<a href="%1$s" title="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a>',
				esc_url( $link['url'] ),
				esc_attr( $link['title'] ),
				esc_html( $link['label'] )
			);
		}

		echo implode( ' | ', $addon_links );
		?>
	</td>
</tr>
