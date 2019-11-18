<?php
/**
 * The default template for a Tabbed View.
 *
 * @var Tribe__Tabbed_View $view
 */

/** @var Tribe__Tabbed_View__Tab[] $tabs */
$tabs = $view->get_visibles();
?>

<?php if ( count( $tabs ) > 1 ) : ?>
    <div class="tabbed-view-wrap wrap">
		<?php if ( $view->get_label() ) : ?>
			<h1><?php echo esc_html( $view->get_label() ); ?></h1>
		<?php endif; ?>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab ): ?>
				<a id="<?php echo esc_attr( $tab->get_slug() ); ?>"
				   class="nav-tab<?php echo( $tab->is_active() ? ' nav-tab-active' : '' ); ?>"
				   href="<?php echo esc_url( $tab->get_url() ); ?>"><?php echo esc_html( $tab->get_label() ); ?>
				</a>
			<?php endforeach; ?>
		</h2>
    </div>
<?php else: ?>
    <h1><?php esc_html_e( reset( $tabs )->get_label() ); ?></h1>
<?php endif; ?>
