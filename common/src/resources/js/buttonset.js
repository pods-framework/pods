var tribe_buttonset = tribe_buttonset || {};

( function( $, obj, _ ) {
	'use strict';

	obj.$body;

	obj.selector = {
		buttonset: '.tribe-buttonset',
		button: '.tribe-button-field',
		input: '.tribe-button-input',
		active: '.tribe-active'
	};

	obj.ready = function( event ) {
		obj.$body = $( 'body' );
		obj.$body.on( 'click.tribe_buttonset', obj.selector.button, obj.click );
		obj.$body.on( 'change.tribe_buttonset', obj.selector.input, obj.change ).find( obj.selector.input ).trigger( 'change' );
	};

	obj.change = function( event ) {
		var $input = $( this ),
			value = $input.val(),
			$group = $input.parents( obj.selector.buttonset ).eq( 0 );

		$group.find( '[data-value="' + value + '"]' ).addClass( obj.selector.active.replace( '.', '' ) );
	};

	obj.click = function( event ) {
		var $button = $( this ),
			$group,
			$input;

		if ( $button.is( '[data-group]' ) ) {
			$group = $( $button.data( 'group' ) );
		} else {
			$group = $button.parents( obj.selector.buttonset );
		}

		var has_group = $group.length > 0,
			input_selector = $group.data( 'input' ) ? $group.data( 'input' ) : obj.selector.input,
			value = $button.data( 'value' ),
			is_multiple = $group.is( '[data-multiple]' );

		if ( has_group && ! is_multiple ) {
			$group.find( obj.selector.button ).removeClass( obj.selector.active.replace( '.', '' ) );
		}

		if ( is_multiple ) {
			$button.toggleClass( obj.selector.active.replace( '.', '' ) );
		} else {
			$button.addClass( obj.selector.active.replace( '.', '' ) );
		}

		// Allows buttons to have specific inputs
		if ( $button.is( '[data-input]' ) ) {
			input_selector = $button.data( 'input' );
		}

		// Tries to find the Input inside of the Button
		$input = $button.find( input_selector );

		// Check for the group and try to find the input there
		if ( has_group && $input.length === 0 ) {
			$input = $group.find( input_selector );
		}

		// If didn't find something yet tries globally
		if ( $input.length === 0 ) {
			$input = $( input_selector );
		}

		if ( $button.is( '[data-value]' ) ) {
			// Apply the value
			$input.val( value );
		}

		if ( 'checkbox' === $input.attr( 'type' ) ) {
			$input.prop( 'checked', $button.is( obj.selector.active ) );
		} else {
			$input.prop( 'disabled', ! $button.is( obj.selector.active ) );
		}

		$input.trigger( 'change' );

		event.preventDefault();
		return false;
	};

	$( document ).ready( obj.ready );
} )( jQuery, tribe_buttonset, window.underscore || window._ );
