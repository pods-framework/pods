tribe.helpPage = tribe.helpPage || {};

( function ( $, obj ) {
	'use strict';

	obj.selectors = {
		copyButton: '.system-info-copy-btn',
		optInMsg: '.tribe-sysinfo-optin-msg',
		autoInfoOptIn: '#tribe_auto_sysinfo_opt_in',
	};

	obj.setup = function () {
		obj.setupSystemInfo();
		obj.setupCopyButton();
	};

	/**
	 * Initialize system info opt in copy
	 */
	obj.setupCopyButton = function () {
		if ( 'undefined' === typeof tribe_system_info ) {
			return;
		}

		var clipboard = new Clipboard( obj.selectors.copyButton );
		var button_icon = '<span class="dashicons dashicons-clipboard license-btn"></span>';
		var button_text = tribe_system_info.clipboard_btn_text;

		//Prevent Button From Doing Anything Else
		$( '.system-info-copy-btn' ).click( function ( e ) {
			e.preventDefault();
		} );

		clipboard.on( 'success', function ( event ) {
			event.clearSelection();
			event.trigger.innerHTML = button_icon + '<span class="optin-success">' + tribe_system_info.clipboard_copied_text + '<span>';
			window.setTimeout( function () {
				event.trigger.innerHTML = button_icon + button_text;
			}, 5000 );
		} );

		clipboard.on( 'error', function ( event ) {
			event.trigger.innerHTML = button_icon + '<span class="optin-fail">' + tribe_system_info.clipboard_fail_text + '<span>';
			window.setTimeout( function () {
				event.trigger.innerHTML = button_icon + button_text;
			}, 5000 );
		} );

	};

	/**
	 * Initialize system info opt in
	 */
	obj.setupSystemInfo = function () {
		if ( 'undefined' === typeof tribe_system_info ) {
			return;
		}

		this.$system_info_opt_in     = $( obj.selectors.autoInfoOptIn );
		this.$system_info_opt_in_msg = $( obj.selectors.optInMsg );

		this.$system_info_opt_in.change( function () {
			if ( this.checked ) {
				obj.doAjaxRequest( 'generate' );
			} else {
				obj.doAjaxRequest( 'remove' );
			}
		} );

	};

	obj.doAjaxRequest = function ( generate ) {
		var request = {
			'action'       : 'tribe_toggle_sysinfo_optin',
			'confirm'      : tribe_system_info.sysinfo_optin_nonce,
			'generate_key' : generate
		};

		// Send our request
		$.post(
			ajaxurl,
			request,
			function ( results ) {
				if ( results.success ) {
					obj.$system_info_opt_in_msg.html( "<p class=\'optin-success\'>" + results.data + "</p>" );
				} else {
					obj.$system_info_opt_in_msg.html( "<p class=\'optin-fail\'>" + results.data.message + " Code:" + results.data.code + " Status:" + results.data.data.status + "</p>" );
					$( "#tribe_auto_sysinfo_opt_in" ).prop( "checked", false );
				}
			}
		);

	};

	$( document ).ready( obj.setup )

} )( jQuery, tribe.helpPage );