var tribe = tribe || {};
tribe.tooltip = tribe.tooltip || {};

( function ( $, obj ) {
	'use strict';

	var $document = $( document );

	/**
	 * Object containing the relevant selectors
	 *
	 * @since 4.9.12
	 *
	 * @return {Object}
	 */
	obj.selectors = {
		tooltip: '.tribe-tooltip',
		active: 'active',
	};

	/**
	 * Setup the live listener to anything that lives inside of the document
	 * that matches the tooltip selector for a click action.
	 *
	 * @since 4.9.12
	 *
	 * @return {void}
	 */
	obj.setup = function () {
		$document.on( 'click', obj.selectors.tooltip, obj.onClick );
	};

	/**
	 * When a tooltip is clicked we setup A11y for the element
	 *
	 * @since 4.9.12
	 *
	 * @return {void}
	 */
	obj.onClick = function () {
		var $tooltip = $( this ).closest( obj.selectors.tooltip );
		var add = ! $tooltip.hasClass( obj.selectors.active );

		$( obj.selectors.tooltip ).each( function () {
			$( this ).removeClass( obj.selectors.active ).attr( 'aria-expanded', false );
		} );

		if ( add ) {
			$( $tooltip ).addClass( obj.selectors.active ).attr( 'aria-expanded', true );
		}
	};

	$document.ready( obj.setup );

} )( jQuery, tribe.tooltip );
