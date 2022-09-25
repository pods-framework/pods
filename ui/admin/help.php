<div class="wrap pods-admin pods-admin--flex">
	<div class="pods-admin-container pods-admin__content-container">
		<div id="icon-pods" class="icon32"><br /></div>
		<h2><?php _e( 'Get help with Pods', 'pods' ); ?></h2>
		<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

		<p><?php _e( 'There are many resources available to help you learn to use Pods <a href="https://pods.io/" target="_blank" rel="noopener noreferrer">on our website</a>', 'pods'); ?>.</p>
		<ul class="ul-disc">
			<li><?php _e('To learn more about using Pods, see our <a href="https://docs.pods.io/" target="_blank" rel="noopener noreferrer">documentation</a> and subscribe to our <a href="https://www.youtube.com/user/podsframework/" target="_blank" rel="noopener noreferrer">YouTube Channel</a>', 'pods'); ?>.

			<li><?php _e( 'To get help with a specific issue, you can ask in our <a href="https://wordpress.org/support/plugin/pods" target="_blank" rel="noopener noreferrer">support forums</a>, or you can join our <a href="https://support.pods.io/chat/" target="_blank" rel="noopener noreferrer">Live Community Slack Chat</a>', 'pods'); ?>.

			<li><?php _e('To report <strong>bugs or request features</strong>, go to our <a href="https://github.com/pods-framework/pods/issues?sort=updated&direction=desc&state=open" target="_blank" rel="noopener noreferrer">GitHub</a>.', 'pods' ); ?></li>

			<li><?php _e( 'Pods is open source, so you can get into the code and submit your own fixes or features. We would love to help you contribute on our project over on our <a href="https://github.com/pods-framework/pods/blob/main/docs/CONTRIBUTING.md" target="_blank" rel="noopener noreferrer">GitHub</a>', 'pods'); ?>.</li>

			<li><?php
				echo sprintf(
					// translators: %1$s: The opening tag for the link; %2$s: The ending tag for the link.
					esc_html__( 'Check out the complete list of %1$sFree and Premium add-ons%2$s to explore great features and integrations.', 'pods'),
					'<a href="https://pods.io/plugins/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
			?></li>
		</ul>

		<hr />

		<style>
			.pods-admin-help-info {
				max-width: 800px;
			}
			.pods-admin-help-info td {
				vertical-align: middle;
			}

			.pods-admin-help-info thead tr th:nth-child(1),
			.pods-admin-help-info tbody tr td:nth-child(1) {
				width: 50px;
			}
			.pods-admin-help-info thead tr th:nth-child(2),
			.pods-admin-help-info tbody tr td:nth-child(2) {
				width: 300px;
			}
		</style>

		<?php
		$addons = [
			[
				'label' => 'Pods Framework',
				'icon' => 'https://ps.w.org/pods/assets/icon-256x256.png',
				'links' => [
					[
						'label' => __( 'Download', 'pods' ),
						'url' => 'https://downloads.wordpress.org/plugin/pods.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url' => 'https://wordpress.org/plugins/pods/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url' => 'https://wordpress.org/support/plugin/pods/',
					],
					[
						'label' => __( 'Docs', 'pods' ),
						'title' => __( 'Documentation', 'pods' ),
						'url' => 'https://docs.pods.io/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url' => 'https://github.com/pods-framework/pods',
					],
				],
			],
		];

		pods_view( PODS_DIR . 'ui/admin/help-addons.php', compact( array_keys( get_defined_vars() ) ) );
		?>

		<h2><?php esc_html_e( 'Official Free Add-Ons', 'pods' ); ?></h2>

		<?php
		$addons = [
			[
				'label'       => 'Pods Beaver Themer Add-On',
				'description' => __( 'Integrates Pods with Beaver Themer', 'pods' ),
				'icon'        => 'https://ps.w.org/pods-beaver-builder-themer-add-on/assets/icon-256x256.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://downloads.wordpress.org/plugin/pods-beaver-builder-themer-add-on.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://wordpress.org/plugins/pods-beaver-builder-themer-add-on/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url'   => 'https://wordpress.org/support/plugin/pods-beaver-builder-themer-add-on/',
					],
					[
						'label' => __( 'Docs', 'pods' ),
						'title' => __( 'Documentation', 'pods' ),
						'url'   => 'https://docs.pods.io/plugins/pods-beaver-themer-add-on/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url'   => 'https://github.com/pods-framework/pods-beaver-builder-themer-add-on',
					],
				],
			],
			[
				'label'       => 'Pods Gravity Forms Add-On',
				'description' => __( 'Integrates Pods with Gravity Forms', 'pods' ),
				'icon'        => 'https://ps.w.org/pods-gravity-forms/assets/icon-256x256.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://downloads.wordpress.org/plugin/pods-gravity-forms.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://wordpress.org/plugins/pods-gravity-forms/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url'   => 'https://wordpress.org/support/plugin/pods-gravity-forms/',
					],
					[
						'label' => __( 'Docs', 'pods' ),
						'title' => __( 'Documentation', 'pods' ),
						'url'   => 'https://docs.pods.io/plugins/pods-gravity-forms-add-on/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url'   => 'https://github.com/pods-framework/pods-gravity-forms',
					],
				],
			],
			[
				'label'       => 'Pods Alternative Cache Add-On',
				'description' => __( 'Speed up Pods on servers with limited object caching capabilities', 'pods' ),
				'icon'        => 'https://ps.w.org/pods-alternative-cache/assets/icon-256x256.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://downloads.wordpress.org/plugin/pods-alternative-cache.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://wordpress.org/plugins/pods-alternative-cache/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url'   => 'https://wordpress.org/support/plugin/pods-alternative-cache/',
					],
					[
						'label' => __( 'Docs', 'pods' ),
						'title' => __( 'Documentation', 'pods' ),
						'url'   => 'https://docs.pods.io/plugins/pods-alternative-cache/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url'   => 'https://github.com/pods-framework/pods-alternative-cache',
					],
				],
			],
			[
				'label'       => 'Pods SEO Add-On',
				'description' => __( 'Integrates Pods Advanced Content Types with Yoast SEO', 'pods' ),
				'icon'        => 'https://ps.w.org/pods-seo/assets/icon-256x256.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://downloads.wordpress.org/plugin/pods-seo.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://wordpress.org/plugins/pods-seo/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url'   => 'https://wordpress.org/support/plugin/pods-seo/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url'   => 'https://github.com/pods-framework/pods-seo',
					],
				],
			],
			[
				'label'       => 'Pods AJAX Views Add-On',
				'description' => __( 'Adds new functions you can use to output template parts that load via AJAX after other page elements', 'pods' ),
				'icon'        => 'https://ps.w.org/pods-ajax-views/assets/icon-256x256.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://downloads.wordpress.org/plugin/pods-ajax-views.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://wordpress.org/plugins/pods-ajax-views/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url'   => 'https://wordpress.org/support/plugin/pods-ajax-views/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url'   => 'https://github.com/pods-framework/pods-ajax-views',
					],
				],
			],
		];

		pods_view( PODS_DIR . 'ui/admin/help-addons.php', compact( array_keys( get_defined_vars() ) ) );
		?>

		<h2><?php esc_html_e( 'Third-party Free Add-Ons', 'pods' ); ?></h2>

		<?php
		$addons = [
			[
				'label'       => 'Paid Memberships Pro - Pods Add-On',
				'description' => __( 'Integrates Pods with Paid Memberships Pro', 'pods' ),
				'icon'        => 'https://ps.w.org/pmpro-pods/assets/icon-256x256.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://downloads.wordpress.org/plugin/pmpro-pods.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://wordpress.org/plugins/pmpro-pods/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url'   => 'https://wordpress.org/support/plugin/pmpro-pods/',
					],
					[
						'label' => __( 'Docs', 'pods' ),
						'title' => __( 'Documentation', 'pods' ),
						'url'   => 'https://www.paidmembershipspro.com/add-ons/pods-integration/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url'   => 'https://github.com/strangerstudios/pmpro-pods',
					],
				],
			],
			[
				'label'       => 'Panda Pods Repeater Field Add-On',
				'description' => __( '(Advanced setup required) Add groups of fields that repeat and are stored in their own custom database table', 'pods' ),
				'icon'        => 'https://ps.w.org/panda-pods-repeater-field/assets/icon-128x128.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://downloads.wordpress.org/plugin/panda-pods-repeater-field.zip',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://wordpress.org/plugins/panda-pods-repeater-field/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'title' => __( 'Support Forums', 'pods' ),
						'url'   => 'https://wordpress.org/support/plugin/panda-pods-repeater-field/',
					],
					[
						'label' => __( 'GitHub', 'pods' ),
						'url'   => 'https://github.com/coding-panda/panda-pods-repeater-field',
					],
				],
			],
		];

		pods_view( PODS_DIR . 'ui/admin/help-addons.php', compact( array_keys( get_defined_vars() ) ) );
		?>

		<h2><?php esc_html_e( 'Pods Pro by SKCDEV Premium Add-Ons', 'pods' ); ?></h2>

		<p><?php esc_html_e( 'These add-ons were developed by the Lead Developer of the Pods Framework project as a way to fund development of unique features and integrations that take Pods further.', 'pods' ); ?></p>

		<?php
		$addons = [
			[
				'label'       => 'List Tables Add-On',
				'description' => __( 'A new block and shortcode to list/filter content from Pods in a table format', 'pods' ),
				'icon'        => 'https://pods-pro.skc.dev/wp-content/uploads/edd/2021/05/List-Tables-150x150.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/downloads/',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/downloads/list-tables/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/get-support/',
					],
				],
			],
			[
				'label'       => 'Page Builder Toolkit Add-On',
				'description' => __( 'Integrates Pods with Beaver Builder, Beaver Themer, Divi Theme, Elementor, GenerateBlocks, and Oxygen Builder', 'pods' ),
				'icon'        => 'https://pods-pro.skc.dev/wp-content/uploads/edd/2020/10/page-builder-toolkit@4x-150x150.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/downloads/',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/downloads/page-builder-toolkit/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/get-support/',
					],
				],
			],
			[
				'label'       => 'Advanced Relationships Storage Add-On',
				'description' => __( 'Advanced options for relationship storage', 'pods' ),
				'icon'        => 'https://pods-pro.skc.dev/wp-content/uploads/edd/2020/10/advanced-relationship-storage@4x-150x150.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/downloads/',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/downloads/advanced-relationship-storage/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/get-support/',
					],
				],
			],
			[
				'label'       => 'TablePress Integration Add-On',
				'description' => __( 'Integrates Pods with TablePress', 'pods' ),
				'icon'        => 'https://pods-pro.skc.dev/wp-content/uploads/edd/2020/10/tablepress-integration@4x-150x150.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/downloads/',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/downloads/tablepress-integration/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/get-support/',
					],
				],
			],
			[
				'label'       => 'Advanced Permalinks Add-On',
				'description' => __( 'Advanced permalink structures and taxonomy landing pages', 'pods' ),
				'icon'        => 'https://pods-pro.skc.dev/wp-content/uploads/edd/2020/10/advanced-permalinks@4x-150x150.png',
				'links'       => [
					[
						'label' => __( 'Download', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/downloads/',
					],
					[
						'label' => __( 'Learn More', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/downloads/advanced-permalinks/',
					],
					[
						'label' => __( 'Support', 'pods' ),
						'url'   => 'https://pods-pro.skc.dev/dashboard/get-support/',
					],
				],
			],
		];

		pods_view( PODS_DIR . 'ui/admin/help-addons.php', compact( array_keys( get_defined_vars() ) ) );
		?>
	</div>

	<?php
	/**
	 * Allow additional output after the container area of the Pods help screen.
	 *
	 * @since 2.7.17
	 */
	do_action( 'pods_admin_after_help' );
	?>

</div>
