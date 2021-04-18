var tribe_plugin_notices = tribe_plugin_notices || {};

/**
 * Appends license key notifications inline within the plugin table.
 *
 * This is done via JS because the options for achieving the same things
 * server-side are currently limited.
 */
(function( $, my ) {
	'use strict';

	my.init = function() {
		for ( var plugin_file in tribe_plugin_notices ) {
			if ( ! tribe_plugin_notices.hasOwnProperty( plugin_file ) ) {
				continue;
			}

			var $row = $( tribe_plugin_notices[ plugin_file ].message_row_html );
			var $active_plugin_row = $( 'tr[data-plugin="' + plugin_file + '"].active' );

			// Add the .update class to the plugin row and append our new row with the update message
			$active_plugin_row.addClass( 'update' ).after( $row );
		}
	};

	$( function() {
		if ( 'object' === typeof tribe_plugin_notices ) {
			my.init();
		}
	});
})( jQuery, tribe_plugin_notices );