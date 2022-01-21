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

		<table class="pods-admin-help-info widefat striped fixed">
			<thead>
				<tr>
					<th></th>
					<th><?php esc_html_e( 'Plugin', 'pods' ); ?></th>
					<th><?php esc_html_e( 'Links', 'pods' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<a href="https://wordpress.org/plugins/pods/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							<img width="50" height="50" src="https://ps.w.org/pods/assets/icon-256x256.png" class="attachment-thumbnail size-thumbnail" alt="Pods Framework" loading="lazy" >
						</a>
					</td>
					<td>
						<a href="https://wordpress.org/plugins/pods/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							Pods Framework
						</a>
					</td>
					<td>
						<a href="https://downloads.wordpress.org/plugin/pods.zip" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Download' ); ?>
						</a> |
						<a href="https://wordpress.org/support/plugin/pods/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Support Forums', 'pods' ); ?>">
							<?php esc_html_e( 'Support', 'pods' ); ?>
						</a> |
						<a href="https://docs.pods.io/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Documentation', 'pods' ); ?>">
							<?php esc_html_e( 'Docs', 'pods' ); ?>
						</a> |
						<a href="https://github.com/pods-framework/pods" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'GitHub', 'pods' ); ?>
						</a>
					</td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Official Free Add-Ons', 'pods' ); ?></h2>
		<table class="pods-admin-help-info widefat">
			<thead>
				<tr>
					<th></th>
					<th><?php esc_html_e( 'Plugin', 'pods' ); ?></th>
					<th><?php esc_html_e( 'Links', 'pods' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<a href="https://wordpress.org/plugins/pods-beaver-builder-themer-add-on/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							<img width="50" height="50" src="https://ps.w.org/pods-beaver-builder-themer-add-on/assets/icon-256x256.png" class="attachment-thumbnail size-thumbnail" alt="Pods Beaver Themer Add-On" loading="lazy" >
						</a>
					</td>
					<td>
						<a href="https://wordpress.org/plugins/pods-beaver-builder-themer-add-on/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							Pods Beaver Themer Add-On
						</a>
					</td>
					<td>
						<a href="https://downloads.wordpress.org/plugin/pods-beaver-builder-themer-add-on.zip" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Download' ); ?>
						</a> |
						<a href="https://wordpress.org/support/plugin/pods-beaver-builder-themer-add-on/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Support Forums', 'pods' ); ?>">
							<?php esc_html_e( 'Support', 'pods' ); ?>
						</a> |
						<a href="https://docs.pods.io/plugins/pods-beaver-themer-add-on/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Documentation', 'pods' ); ?>">
							<?php esc_html_e( 'Docs', 'pods' ); ?>
						</a> |
						<a href="https://github.com/pods-framework/pods-beaver-builder-themer-add-on" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'GitHub', 'pods' ); ?>
						</a>
					</td>
				</tr>

				<tr>
					<td>
						<a href="https://wordpress.org/plugins/pods-gravity-forms/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>'">
							<img width="50" height="50" src="https://ps.w.org/pods-gravity-forms/assets/icon-256x256.png" class="attachment-thumbnail size-thumbnail" alt="Pods Gravity Forms Add-on" loading="lazy" >
						</a>
					</td>
					<td>
						<a href="https://wordpress.org/plugins/pods-gravity-forms/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							Pods Gravity Forms Add-on
						</a>
					</td>
					<td>
						<a href="https://downloads.wordpress.org/plugin/pods-gravity-forms.zip" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Download' ); ?>
						</a> |
						<a href="https://wordpress.org/support/plugin/pods-gravity-forms/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Support Forums', 'pods' ); ?>">
							<?php esc_html_e( 'Support', 'pods' ); ?>
						</a> |
						<a href="https://docs.pods.io/plugins/pods-gravity-forms-add-on/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Documentation', 'pods' ); ?>">
							<?php esc_html_e( 'Docs', 'pods' ); ?>
						</a> |
						<a href="https://github.com/pods-framework/pods-gravity-forms" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'GitHub', 'pods' ); ?>
						</a>
					</td>
				</tr>

				<tr>
					<td>
						<a href="https://wordpress.org/plugins/pods-alternative-cache/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							<img width="50" height="50" src="https://ps.w.org/pods-alternative-cache/assets/icon-256x256.png" class="attachment-thumbnail size-thumbnail" alt="Pods Alternative Cache" loading="lazy" >
						</a>
					</td>
					<td>
						<a href="https://wordpress.org/plugins/pods-alternative-cache/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							Pods Alternative Cache
						</a>
					</td>
					<td>
						<a href="https://downloads.wordpress.org/plugin/pods-alternative-cache.zip" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Download' ); ?>
						</a> |
						<a href="https://wordpress.org/support/plugin/pods-alternative-cache/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Support Forums', 'pods' ); ?>">
							<?php esc_html_e( 'Support', 'pods' ); ?>
						</a> |
						<a href="https://docs.pods.io/plugins/pods-alternative-cache/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Documentation', 'pods' ); ?>">
							<?php esc_html_e( 'Docs', 'pods' ); ?>
						</a> |
						<a href="https://github.com/pods-framework/pods-alternative-cache" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'GitHub', 'pods' ); ?>
						</a>
					</td>
				</tr>

				<tr>
					<td>
						<a href="https://wordpress.org/plugins/pods-seo/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							<img width="50" height="50" src="https://ps.w.org/pods-seo/assets/icon-256x256.png" class="attachment-thumbnail size-thumbnail" alt="Pods SEO" loading="lazy" >
						</a>
					</td>
					<td>
						<a href="https://wordpress.org/plugins/pods-seo/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							Pods SEO
						</a>
					</td>
					<td>
						<a href="https://downloads.wordpress.org/plugin/pods-seo.zip" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Download' ); ?>
						</a> |
						<a href="https://wordpress.org/support/plugin/pods-seo/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Support Forums', 'pods' ); ?>">
							<?php esc_html_e( 'Support', 'pods' ); ?>
						</a> |
						<a href="https://github.com/pods-framework/pods-seo" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'GitHub', 'pods' ); ?>
						</a>
					</td>
				</tr>

				<tr>
					<td>
						<a href="https://wordpress.org/plugins/pods-ajax-views/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							<img width="50" height="50" src="https://ps.w.org/pods-ajax-views/assets/icon-256x256.png" class="attachment-thumbnail size-thumbnail" alt="Pods AJAX Views" loading="lazy" >
						</a>
					</td>
					<td>
						<a href="https://wordpress.org/plugins/pods-ajax-views/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'View plugin on WordPress.org', 'pods' ); ?>">
							Pods AJAX Views
						</a>
					</td>
					<td>
						<a href="https://downloads.wordpress.org/plugin/pods-ajax-views.zip" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Download' ); ?>
						</a> |
						<a href="https://wordpress.org/support/plugin/pods-ajax-views/" target="_blank" rel="noopener noreferrer"
							title="<?php esc_attr_e( 'Support Forums', 'pods' ); ?>">
							<?php esc_html_e( 'Support', 'pods' ); ?>
						</a> |
						<a href="https://github.com/pods-framework/pods-ajax-views" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'GitHub', 'pods' ); ?>
						</a>
					</td>
				</tr>
			</tbody>
		</table>

		<div class="note">
			<h3>Are you looking for help with Pods Pro by SKCDEV?</h3>
			<p>Pods Pro by SKCDEV is separate from Pods. You can get support by logging into the
				<a href="https://pods-pro.skc.dev/" target="_blank" rel="noopener noreferrer">Pods Pro by SKCDEV site</a>
				and going to your Dashboard area.
			</p>
		</div>
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
