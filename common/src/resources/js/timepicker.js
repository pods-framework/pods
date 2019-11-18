/**
 * @todo Move this to use the new `tribe` object, tribe.timepicker
 * @type {object}
 */
var tribe_timepickers = tribe_timepickers || {};

( function ( $, obj ) {
	'use strict';

	obj.selector = {
		container  : '.tribe-datetime-block',
		timepicker : '.tribe-timepicker',
		all_day    : '#allDayCheckbox',
		timezone   : '.tribe-field-timezone',
		input      : 'select, input'
	};

	obj.timepicker = {
		opts: {
			forceRoundTime: false,
			step: 30,
		}
	};

	obj.timezone = {
		link: _.template( '<a href="#" class="tribe-change-timezone"><%= label %> <%= timezone %></a>' )
	}

	obj.$ = {};

	obj.container = function( k, container ) {
		var $container  = $( container );
		var $allDay    = $container.find( obj.selector.all_day );
		var $timepicker = $container.find( obj.selector.timepicker );
		var $timezone   = $container.find( obj.selector.timezone ).not( obj.selector.input );
		var $input      = $container.find( obj.selector.timezone ).filter( obj.selector.input );

		// Create the Link
		var $timezoneLink = $( obj.timezone.link( { label: $input.data( 'timezoneLabel' ), timezone: $input.data( 'timezoneValue' ) } ) );

		// Toggle Timepickers on All Day change
		$allDay.on( 'change', function() {
			if ( true === $allDay.prop( 'checked' ) ) {
				$timepicker.hide();
			} else {
				$timepicker.show();
			}
		} ).trigger( 'change' );

		obj.setup_timepickers( $timepicker );

		// Attach a Click action the Timezone Link
		$timezoneLink.on( 'click', function( e ) {
			$timezone = $container.find( obj.selector.timezone ).filter( '.select2-container' );
			e.preventDefault();

			$timezoneLink.hide();
			$timezone.show();
		} );

		// Append the Link to the Timezone
		$input.before( $timezoneLink );
	};

	obj.init = function() {
		obj.$.containers = $( obj.selector.container );
		obj.$.containers.each( obj.container );
	};

	/**
	 * Initializes timepickers
	 */
	obj.setup_timepickers = function( $timepickers ) {
		// Setup all Timepickers
		$timepickers.each( function() {
			var $item = $( this );
			var opts  = $.extend( {}, obj.timepicker.opts );

			if ( $item.data( 'format' ) ) {
				opts.timeFormat = $item.data( 'format' );
			}

			// By default the step is 15
			if ( $item.data( 'step' ) ) {
				opts.step = $item.data( 'step' );
			}

			// Passing anything but 0 or 'false' will make it round to the nearest step
			var round = $item.data( 'round' );
			if (
				round &&
				0 != round &&
				'false' !== round
			) {
				opts.forceRoundTime = true;
			}

			if ( 'undefined' !== typeof $.fn.tribeTimepicker ) {
				$item.tribeTimepicker( opts ).trigger( 'change' );
			} else {
				// @deprecated 4.6.1
				$item.timepicker( opts ).trigger( 'change' );
			}
		} );
	};

	$( document ).ready( obj.init );
} ( jQuery, tribe_timepickers ) );