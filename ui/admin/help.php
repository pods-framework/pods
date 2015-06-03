<?php
/**
 * @package  Pods
 * @category Admin
 */
?>
<div class="wrap pods-admin">
	<form action="" method="post">

		<div id="icon-pods" class="icon32"><br /></div>

		<?php
		$default = 'resources';

		$tabs = array(
			'resources' => __( 'Support Resources', 'pods' ),
			'debug'     => __( 'Debug Information', 'pods' ),
			'send-info' => __( 'Send Information', 'pods' ),
			'github'    => __( 'GitHub Log' )
		);
		?>

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $tabs as $tab => $label ) {
				$class = '';

				if ( $tab == pods_v( 'tab', 'get', $default ) ) {
					$class = ' nav-tab-active';

					$label = 'Pods ' . $label;
				}

				$url = pods_query_arg( array( 'tab' => $tab ), array( 'page' ) );
				?>
				<a href="<?php echo esc_url( $url ); ?>" class="nav-tab<?php echo esc_attr( $class ); ?>">
					<?php echo $label; ?>
				</a>
			<?php
			}
			?>
		</h2>
		<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

		<?php
		$tab = pods_v( 'tab', 'get', $default );
		$tab = sanitize_title( $tab );

		echo pods_view( PODS_DIR . 'ui/admin/help-' . $tab . '.php' );
		?>
	</form>
</div>
