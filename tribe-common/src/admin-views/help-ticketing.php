<?php

use \Tribe\Admin\Help_Page;

$faqs               = tribe( Tribe__Admin__Help_Page::class )->get_ticketing_faqs();
$extensions         = tribe( Tribe__Admin__Help_Page::class )->get_ticketing_extensions();
$ticketing_products = tribe( Tribe__Admin__Help_Page::class )->get_ticketing_products();

?>
<div id="tribe-ticketing">
	<img
		class="tribe-events-admin-header__right-image"
		src="<?php echo esc_url( tribe_resource_url( 'images/help/help-ticketing-header.png', false, null, $main ) ); ?>"
	/>
	<p class="tribe-events-admin-products-description">
		<?php esc_html_e( 'Get help for these products and learn more about products you don\'t have.', 'tribe-common' ); ?>
	</p>

	<?php // list of products ?>
	<div class="tribe-events-admin-products tribe-events-admin-2col-grid">
	<?php //requires valid links for all the products
		foreach ( $ticketing_products as $slug ) : ?>
			<div class="tribe-events-admin-products-card">
				<img
					class="tribe-events-admin-products-card__icon"
					src="<?php echo esc_url( tribe_resource_url( $products[ $slug ]['logo'], false, null, $main ) ); ?>"
					alt="<?php esc_attr_e( 'logo icon', 'tribe-common' ); ?>"
				/>
				<div class="tribe-events-admin-products-card__group">
					<h4 class="tribe-events-admin-products-card__group-title">
						<?php echo esc_html( $products[ $slug ]['title'] ); ?>
					</h4>
					<div class="tribe-events-admin-products-card__group-description">
						<?php echo esc_html( $products[ $slug ]['description-help'] ); ?>
					</div>
				</div>
				<?php 
					$plugin_path_url = WP_PLUGIN_DIR . '/' . $products[ $slug ]['plugin-dir'] . '/' . $products[ $slug ]['main-file'];
					$plugin_exists = file_exists( $plugin_path_url );

					// checks if plugin is installed and activated
					if ( $products[ $slug ]['is_installed'] ) { ?>
						<button class="tribe-events-admin-products-card__button tribe-events-admin-products-card__button--active">
							<?php esc_html_e( 'Active', 'tribe-common' ); ?>
						</button> 
						<?php
					}
					// displays different message for Promoter
					elseif ( ! $products[ $slug ]['is_installed'] && 'Promoter' === $products[ $slug ]['title'] ) { ?>
						<a href="<?php echo esc_url( Tribe__Settings::instance()->get_url( [ 'tab' => 'licenses', 'post_type' => 'tribe_events' ] ) ); ?>" class="tribe-events-admin-products-card__button">
							<?php esc_html_e( 'Add license key', 'tribe-common' ); ?>
						</a>
						<?php
					}
					// checks if plugin is installed but not activated
					elseif ( ! $products[ $slug ]['is_installed'] && $plugin_exists ) { ?>
						<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="tribe-events-admin-products-card__button">
							<?php esc_html_e( 'Activate', 'tribe-common' ); ?>
						</a>
						<?php
					}
					// adds a learn more link if plugin is neither activated nor installed
					else { ?>
						<a href="<?php echo $products[ $slug ]['link'] ?>" target="_blank" rel="noopener noreferrer" class="tribe-events-admin-products-card__button">
							<?php esc_html_e( 'Learn More', 'tribe-common' ); ?>
						</a>
						<?php
					}
				?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="tribe-events-admin-section-header">
		<h3>
			<?php esc_html_e( 'Start Here', 'tribe-common' ); ?>
		</h3>
		
		<a href="https://evnt.is/1aq9" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Visit Knowledgebase', 'tribe-common' ); ?>
		</a>
	</div>

	<div class="tribe-events-admin-kb tribe-events-admin-3col-grid">
		<div class="tribe-events-admin-kb-card">
			<img
				class="tribe-events-admin-kb-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/help/help-start-guide-tickets.png', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'book with The Events ticketing logo', 'tribe-common' ); ?>"
			/>
			<h4 class="tribe-events-admin-kb-card__title">
				<?php esc_html_e( 'Getting Started Guides', 'tribe-common' ); ?>
			</h4>
			<ul class="tribe-events-admin-kb-card__links">
				<li>
					<a href="https://evnt.is/1apn" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Event Tickets', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1apo" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Calendar & Ticket Shortcodes', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1app" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Promoter', 'tribe-common' ); ?>
					</a>
				</li>
			</ul>
		</div>

		<div class="tribe-events-admin-kb-card">
			<img
				class="tribe-events-admin-kb-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/help/customizing.png', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'book with Event Tickets logo', 'tribe-common' ); ?>"
			/>
			<h4 class="tribe-events-admin-kb-card__title">
				<?php esc_html_e( 'Creating Tickets & RSVPs', 'tribe-common' ); ?>
			</h4>
			<ul class="tribe-events-admin-kb-card__links">
				<li>
					<a href="https://evnt.is/1apq" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Creating Tickets', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1apr" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Creating RSVPs', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1aps" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Configuring Paypal for Tickets', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1apt" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Shortcodes', 'tribe-common' ); ?>
					</a>
					<?php esc_html_e( '(Event Tickets Plus)', 'tribe-common' ); ?>
				</li>
			</ul>
		</div>

		<div class="tribe-events-admin-kb-card">
			<img
				class="tribe-events-admin-kb-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/help/common-issues.png', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'book with The Events ticketing logo', 'tribe-common' ); ?>"
			/>
			<h4 class="tribe-events-admin-kb-card__title">
				<?php esc_html_e( 'Plugin Maintenance', 'tribe-common' ); ?>
			</h4>
			<ul class="tribe-events-admin-kb-card__links">
				<li>
					<a href="https://evnt.is/1apu" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Testing for Conflicts', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1apv" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Release notes', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1apw" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Integrations', 'tribe-common' ); ?>
					</a>
				</li>
				<li>
					<a href="https://evnt.is/1apx" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Automatic Updates', 'tribe-common' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>

	<?php // faq section ?>
	<div class="tribe-events-admin-section-header">
		<h3>
			<?php esc_html_e( 'FAQs', 'tribe-common' ); ?>
		</h3>
		
		<a href="https://evnt.is/1av3#faqs" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'All FAQs', 'tribe-common' ); ?>
		</a>
	</div>

	<div class="tribe-events-admin-faq tribe-events-admin-4col-grid">
		<?php foreach ( $faqs as $faq ) : ?>
			<div class="tribe-events-admin-faq-card">
				<div class="tribe-events-admin-faq-card__icon">
					<img
						src="<?php echo esc_url( tribe_resource_url( 'images/icons/faq.png', false, null, $main ) ); ?>"
						alt="<?php esc_attr_e( 'lightbulb icon', 'tribe-common' ); ?>"
					/>
				</div>
				<div class="tribe-events-admin-faq-card__content">
					<div class="tribe-events-admin-faq__question">
						<a href="<?php echo esc_url( $faq['link'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $faq['question'] ); ?>	
						</a>
					</div>
					<div class="tribe-events-admin-faq__answer">
						<?php echo esc_html( $faq['answer'] ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php // extensions section ?>
	<div class="tribe-events-admin-section-header">
		<h3>
			<?php esc_html_e( 'Free extensions', 'tribe-common' ); ?>
		</h3>
		
		<a href="https://evnt.is/1aqa" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'All Extensions', 'tribe-common' ); ?>
		</a>
	</div>

	<p class="tribe-events-admin-extensions-title">
		<?php esc_html_e( 'Small, lightweight WordPress plugins that add new capabilities to our core plugins. Support is not offered for extensions; however they provide enhanced functionality and bonus features.', 'tribe-common' ); ?>
	</p>

	<div class="tribe-events-admin-extensions tribe-events-admin-4col-grid">
		<?php foreach ( $extensions as $extension ) : ?>
			<div class="tribe-events-admin-extensions-card">
				<h4 class="tribe-events-admin-extensions-card__title">
					<a href="<?php echo esc_url( $extension['link'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $extension['title'] ); ?>
					</a>
				</h4>
				<div class="tribe-events-admin-extensions-card__description">
					<?php echo esc_html( $extension['description'] ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>