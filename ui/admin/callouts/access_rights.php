<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @var bool $force_callouts Whether to force the callouts.
 */

$callout = 'access_rights';

$doc_link = 'https://docs.pods.io/displaying-pods/access-rights-in-pods/';

$campaign_args = [
	'utm_source'   => 'pods_plugin_callout',
	'utm_medium'   => 'link',
	'utm_campaign' => $callout,
];

$doc_link = add_query_arg( $campaign_args, $doc_link );
?>

<div class="pods-admin_friends-callout_container pods-admin_friends-callout_container-horizontal">
	<div class="pods-admin_friends-callout_content-container">
		<h3>
			<?php esc_html_e( 'Pods 3.1: New Access Rights Feature', 'pods' ); ?>
		</h3>

		<p class="pods-admin_friends-callout_text">
			🔒&nbsp;
			<?php
				esc_html_e( 'We built a new Access Rights feature to help you better secure your site and give you more control over who sees your content.', 'pods' );
			?>

			<a href="<?php echo esc_url( $doc_link ); ?>"
			   target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Read the documentation for more information', 'pods' ); ?> &raquo;
			</a>
		</p>

		<p class="pods-admin_friends-callout_text">
			⚠️&nbsp;
			<?php
				esc_html_e( 'Some of your dynamically embedded content and forms may no longer be visible to everyone. People may see notices in those places depending on the access rights of their individual WP user account.', 'pods' );
			?>
		</p>

		<p>
			<strong><?php esc_html_e( 'Your next required step:', 'pods' ); ?></strong>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pods-access-rights-review' ) ); ?>"
			   class="button button-primary">
				<?php esc_html_e( 'Review and confirm your access rights', 'pods' ); ?>
			</a>
		</p>

		<hr />

		<p class="pods-admin_friends-callout_text">
			<strong>
				<?php
					esc_html_e( 'Access Right checks include:', 'pods' );
				?>
			</strong>
		</p>

		<p class="pods-admin_friends-callout_text">
			<ul class="pods-ul-normal">
				<li>
					<?php
						esc_html_e( 'Control Dynamic Features in Pods Settings', 'pods' );
					?>
				</li>
				<li>
					<?php
						esc_html_e( 'Manage Access Rights notices in Pods Settings and when editing individual Pods', 'pods' );
					?>
				</li>
				<li>
					<?php
						esc_html_e( 'Preview various capabilities needed for any content type in WordPress', 'pods' );
					?>
				</li>
				<li>
					<?php
						esc_html_e( 'You can always further customize the capabilities that Pods references through your own custom PHP code', 'pods' );
					?>
				</li>
			</ul>
		</p>

		<div class="pods-admin_friends-callout_button-group-padded">
			<a href="<?php echo esc_url( $doc_link ); ?>"
				target="_blank" rel="noopener noreferrer"
				class="pods-admin_friends-callout_button">
				<?php esc_html_e( 'Learn more', 'pods' ); ?> &raquo;
			</a>
		</div>
	</div>
</div>
