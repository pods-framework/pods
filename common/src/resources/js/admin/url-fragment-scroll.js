tribe.urlFragmentScroll = tribe.urlFragmentScroll || {};

( function ( $, obj ) {
	'use strict';

	var $document = $( document );

	/**
	 * Sets up listeners and callbacks to handle navigation to page #elements
	 * gracefully and in a way that doesn't result in the admin toolbar obscuring
	 * the target.
	 *
	 * @since 4.5.6
	 * @since 4.9.12   Moved from tribe-common.js to a more specific file
	 *
	 * @return {void}
	 */
	obj.setup = function () {
		obj.navigateToFragment( window.location.href );

		$document.on( 'click', '.tribe_events_page_tribe-common', obj.onClick );
	};

	/**
	 * If it looks like the user has navigated to a specific anchor within the page
	 * then trigger our scroll position adjustment.
	 *
	 * @since 4.5.6
	 * @since 4.9.12   Moved from tribe-common.js to a more specific file and renamed to `onClick`
	 *
	 * @param {Event} event Event in which this was triggered on.
	 *
	 * @return {void}
	 */
	obj.onClick = function( event ) {
		var link = $( event.target ).attr( 'href' );

		// If we couldn't determine the URL, bail
		if ( 'undefined' === typeof link ) {
			return;
		}

		obj.navigateToFragment( link );
	}

	/**
	 * Will atempt to navigate to a given Fragment based on a URL.
	 *
	 * @since  4.9.12
	 *
	 * @param  {String} link Which link target we are trying to navigate to.
	 *
	 * @return {void}
	 */
	obj.navigateToFragment = function( link ) {
		var fragment = obj.getUrlFragment( link );

		// No ID/fragment in the URL? Bail
		if ( ! fragment ) {
			return;
		}

		obj.adjustScrollPosition( fragment );
	};

	/**
	 * Adjust the scroll/viewport offset if necessary to stop the admin toolbar
	 * from obscuring the target element.
	 *
	 * @since 4.5.6
	 * @since 4.9.12   Moved from tribe-common.js to a more specific file
	 *
	 * @param {String} id
	 *
	 * @return {void}
	 */
	obj.adjustScrollPosition = function( id ) {
		// No toolbar, no problem
		if ( ! $( '#wpadminbar' ).length ) {
			return;
		}

		var elementPosition = $( '#' + id ).position();

		// Bail if the element doesn't actually exist
		if ( ! elementPosition ) {
			return;
		}

		// A fractional delay is needed to ensure our adjustment sticks
		setTimeout( function() {
			window.scroll( window.scrollX, elementPosition.top );
		} );
	};

	/**
	 * Attempts to extract the "#fragment" string from a URL and returns it
	 * (will be empty if not set).
	 *
	 * @since 4.5.6
	 *
	 * @param {String} url
	 *
	 * @returns {String}
	 */
	obj.getUrlFragment = function( url ) {
		var fragment = url.match( /#([a-z0-9_-]+)$/i )

		if ( null === fragment ) {
			return '';
		}

		// Return the first captured group
		return fragment[1];
	};

	$document.ready( obj.setup );

} )( jQuery, tribe.urlFragmentScroll );