<?php
/**
 * @package  Pods
 * @category Admin
 */
?>
<style type="text/css">
	#pods-send-info-preview {
		border: 1px solid #DDD;
		background: #fff;
		height: 300px;
		overflow: scroll;
	}

	#pods-send-info-form {
		display: inline;
		width: 64%;
		float: left;
		margin-right: 2%;
	}

	#pods-send-info-info {
		display: inline;
		width: 32%;
		float: left;
	}

	div#pods-send-info-form div {
		padding: 12px 0;
	}

</style>

<div id="pods-send-info-form">
	<div id="pods-send-info-form">
		<h3><?php _e( 'This is the exact information that what will be sent to our support staff:', 'pods' ); ?></h3>

		<div id="pods-send-info-preview" style="">
			<?php echo pods_admin()->send_info(); ?>
		</div>

		<h3><?php _e( 'Send Your information', 'pods' ); ?></h3>

		<form action="" method="post">
			<?php
			$pods_form = pods_form();
			echo $pods_form::field( '_wpnonce', wp_create_nonce( 'my-nonce-name' ), 'hidden' );

			echo '<div>';
			echo $pods_form::label( 'name', __( 'Your Name (Required)', 'pods' ) ) . '<br />';
			echo $pods_form::field( 'name', '' );
			echo '</div>';

			echo '<div>';
			echo $pods_form::label( 'issue-link', __( 'Link to Pods Forum Post or GitHub Issue (Required)', 'pods' ) ) . '<br />';
			echo $pods_form::field( 'issue-link', '' );
			echo '</div>';

			echo $pods_form::submit_button( 'Send' );
			?>

		</form>

	</div>
	<div id="pods-send-info-info">
		<p><?php _e( 'You can use this form to send your debug information and Pods configuration information to Pods support.', 'pods' ) ?></p>

		<p><?php _e( 'Before sending this information please create a new post in our forums or if this relates to a bug report create a new issue in our GitHub repository. The form on this page requires that you provide a link to the issue or post.', 'pods' ) ?></p>

		<p><?php _e( 'This information will be kept private and viewed only by members of the Pods team.', 'pods' ) ?></p>
	</div>

</div>

<?php
if ( isset( $_POST[ 'submit' ] ) ) {
	$sent = false;
	$nonce = $_REQUEST[ '_wpnonce' ];

	if ( empty( $_POST ) || ! wp_verify_nonce( $nonce, 'my-nonce-name' ) ) {
		printf( '<div id="message" class="error"><p>%s</p></div>', __( 'Error sending Email. Your nonce could not be verified.', 'pods' ) );
		exit;
	} else {
		$message = '';
		$message_parts = array(
			'name' => pods_v_sanitized( 'name', 'post', false, true ),
			'link' => pods_v_sanitized( 'issue-link', 'post', false, true ),
			'info' => pods_admin()->send_info(),
		);

		$fail = false;
		foreach ( $message_parts as $key => $part ) {
			if ( $part === false || $part !== '' ) {
				$fail = true;
				break;
			} else {
				$message .= '<p>' . $key . '<p><br />';
				$message .= $part;
				$message .= '<hr />';
			}

		}

		if ( $message !== '' && ! $fail ) {
			$headers = '';
			$current_user = get_currentuserinfo();
			if ( is_object( $current_user ) ) {
				$headers = array(
					'From: ' . $current_user->display_name . ' <' . $current_user->user_email . '>',
					'Reply-To: ' . $current_user->user_email,
				);
			}
			$sent = wp_mail( 'Josh@Pods.io', 'Pods Send System Info', print_r( $message, true ), $headers );
		}

		if ( $sent && ! $fail ) {
			printf( '<div id="message" class="updated"><p>%s</p></div>', __( 'Error sending Email.', 'pods' ) );
		} else {
			printf( '<div id="message" class="error"><p>%s</p></div>', __( 'Error sending Email.', 'pods' ) );
		}

	}

}