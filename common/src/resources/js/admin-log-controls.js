var tribe_logger_admin = tribe_logger_admin || {};
var tribe_logger_data  = tribe_logger_data || {};

( function( $, obj ) {
	var working        = false;
	var current_view   = '';
	var current_engine = '';
	var view_changed   = false;
	var $controls      = $( '#tribe-log-controls' );
	var $options       = $controls.find( 'select' );
	var $spinner       = $controls.find( '.working' );
	var $viewer        = $( '#tribe-log-viewer' );
	var $download_link = $( 'a.download_log' );

	/**
	 * Update the log view based on changes to the various selectors.
	 */
	function update() {
		// If an update is already in progress let's wait until that job completes
		if ( working ) {
			return;
		}

		detect_view_change();
		freeze();
		request();
	}

	/**
	 * Communicate any changes back to the server so we can obtain fresh log data
	 * to display to the user.
	 */
	function request() {
		var data = {
			'action':     'tribe_logging_controls',
			'check':      tribe_logger_data.check,
			'log-level':  $( '#log-level' ).find( ':selected' ).attr( 'name' ),
			'log-engine': $( '#log-engine' ).find( ':selected' ).attr( 'name' )
		};

		if ( view_changed ) {
			data['log-view'] = current_view;
		}

		$.ajax( ajaxurl, {
			'method':   'POST',
			'success':  on_success,
			'error':    on_error,
			'dataType': 'json',
			'data':     data
		} );
	}

	/**
	 * Unfreeze the controls (following an update) and refresh on-screen data as needed.
	 *
	 * @param data
	 */
	function on_success( data ) {
		unfreeze();

		if ( $.isArray( data.data.entries ) ) {
			$viewer.html( to_table( data.data.entries ) );
			update_download_link();
		}
	}

	/**
	 * Converts data_array, which is expected to be an array of
	 * arrays, into an HTML table.
	 */
	function to_table( data_array ) {
		var html = '<table>';

		for ( var row in data_array ) {
			html += '<tr>';

			for ( var cell in data_array[row] ) {
				html += '<td>' + data_array[row][cell] + '</td>';
			}

			html += '</tr>';
		}

		return html + '</table>';
	}

	/**
	 * Add, append or update the log download link so it points to the
	 * currently selected log file.
	 */
	function update_download_link() {
		// bail if not in DOM
		if ( 1 > $download_link.length ) {
			return;
		}

		var url = $download_link.attr( 'href' );
		var log = encodeURI( get_current_view() );
		var matches = url.match(/&log=([a-z0-9\-]+)/i);

		// Update or add the log parameter
		if ( $.isArray( matches ) && 2 === matches.length ) {
			url = url.replace( matches[0], '&log=' + log );
		} else if ( url.indexOf( '?' ) ) {
			url = url + '&log=' + log;
		} else {
			url = url + '?log=' + log;
		}

		$download_link.attr( 'href', url );
	}

	/**
	 * If our request back to the server failed, unfreeze the controls so
	 * we can try again.
	 *
	 * @todo in a future iteration we should add substantive handling for this scenario
	 */
	function on_error() {
		unfreeze();
	}

	/**
	 * Freeze/disable the controls and show the spinner.
	 */
	function freeze() {
		working = true;
		$options.prop( 'disabled', true );
		$spinner.removeClass( 'hidden' );
	}

	/**
	 * Unfreeze/enable the controls and hide the spinner.
	 */
	function unfreeze() {
		working = false;
		$options.prop( 'disabled', false );
		$spinner.addClass( 'hidden' );
	}

	/**
	 * Check if a change in controls constituting a change of view has occured.
	 *
	 * This could be because the user changed logging engine or because they picked
	 * a different log to peruse; a change in logging level on the other hand does
	 * not count.
	 */
	function detect_view_change() {
		var new_view = get_current_view();
		var new_engine = get_current_engine();

		if ( new_view !== current_view || new_engine !== current_engine ) {
			view_changed = true;
			current_view = new_view;
			current_engine = new_engine;
		} else {
			view_changed = false;
		}
	}

	function get_current_view() {
		return $( '#log-selector' ).find( ':selected' ).attr( 'name' );
	}

	function get_current_engine() {
		return $( '#log-engine' ).find( ':selected' ).attr( 'name' );
	}

	// Setup
	current_view = get_current_view();
	current_engine = get_current_engine();

	update_download_link();
	$options.change( update );
} )( jQuery, tribe_logger_admin );