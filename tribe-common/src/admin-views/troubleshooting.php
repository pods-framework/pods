<?php
	// admin notice
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/notice.php';
	// intro
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/introduction.php';
	// detected issues
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/detected-issues.php';
	// first steps
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/first-steps.php';
	// common issues
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/common-issues.php';
	// system information
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/system-information.php';
	// recent template changes
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/recent-template-changes.php';
	// recent logs
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/event-log.php';
	// ea status
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status.php';
	// support cta
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/support-cta.php';
	// footer
	include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/footer-logo.php';
?>

<?php // this is inline jQuery / javascript for extra simplicity */ ?>
<script>
	if ( 
		jQuery( '.tribe-events-admin__issues-found-card .tribe-events-admin__issues-found-title' )
			.hasClass( 'active' ) 
	) {
		jQuery( '.tribe-events-admin__issues-found-card .tribe-events-admin__issues-found-card-title.active' )
			.closest( '.tribe-events-admin__issues-found-card' )
			.find( '.tribe-events-admin__issues-found-description' )
			.show();
	}
	jQuery( '.tribe-events-admin__issues-found-card .tribe-events-admin__issues-found-card-title' )
		.on( 'click', function () {
			var $this = jQuery( this );

			if ( jQuery( this ).hasClass( 'active' ) ) {
				$this
					.removeClass( 'active' )
					.closest( '.tribe-events-admin__issues-found-card' )
					.find( '.tribe-events-admin__issues-found-card-description' )
					.slideUp( 200 );
			} else {
				$this
					.addClass( 'active' )
					.closest( '.tribe-events-admin__issues-found-card' )
					.find( '.tribe-events-admin__issues-found-card-description' )
					.slideDown( 200 );
			}
		} );
</script>
