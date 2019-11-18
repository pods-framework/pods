var tribe_dropdowns = tribe_dropdowns || {};

( function( $, obj, _ ) {
	'use strict';

	obj.selector = {
		dropdown: '.tribe-dropdown',
		created: '.tribe-dropdown-created',
	};

	// Setup a Dependent
	$.fn.tribe_dropdowns = function () {
		obj.dropdown( this );

		return this;
	};

	obj.freefrom_create_search_choice = function( term, data ) {
		var args = this.opts,
			$select = args.$select;

		if (
			term.match( args.regexToken )
			&& (
				! $select.is( '[data-int]' )
				|| (
					$select.is( '[data-int]' )
					&& term.match( /\d+/ )
				)
			)
		) {
			var choice = { id: term, text: term, new: true };
			if ( $select.is( '[data-create-choice-template]' ) ) {
				choice.text = _.template( $select.data( 'createChoiceTemplate' ) )( { term: term } );
			}

			return choice;
		}
	};

	obj.allow_html_markup = function ( m ) {
		return m;
	};

	/**
	 * Better Search ID for Select2, compatible with WordPress ID from WP_Query
	 *
	 * @param  {object|string} e Searched object or the actual ID
	 * @return {string}   ID of the object
	 */
	obj.search_id = function ( e ) {
		var id = undefined;

		if ( 'undefined' !== typeof e.id ){
			id = e.id;
		} else if ( 'undefined' !== typeof e.ID ){
			id = e.ID;
		} else if ( 'undefined' !== typeof e.value ){
			id = e.value;
		}
		return undefined === e ? undefined : id;
	};

	/**
	 * Better way of matching results
	 *
	 * @param  {string} term Which term we are searching for
	 * @param  {string} text Search here
	 * @return {boolean}
	 */
	obj.matcher = function ( term, text ) {
		var $select = this.element,
			args = $select.data( 'dropdown' ),
			result = text.toUpperCase().indexOf( term.toUpperCase() ) !== -1;

		if ( ! result && 'undefined' !== typeof args.tags ){
			var possible = _.where( args.tags, { text: text } );
			if ( args.tags.length > 0  && _.isObject( possible ) ){
				var test_value = obj.search_id( possible[0] );
				result = test_value.toUpperCase().indexOf( term.toUpperCase() ) !== -1;
			}
		}

		return result;
	};

	/**
	 * If the element used as the basis of a dropdown specifies one or more numeric/text
	 * identifiers in its val attribute, then use those to preselect the appropriate options.
	 *
	 * @param {object}   $select
	 * @param {function} make_selection
	 */
	obj.init_selection = function( $select, make_selection ) {
		var is_multiple    = $select.is( '[multiple]' );
		var options        = $select.data( 'dropdown' );
		var current_values = $select.val().split( options.regexSplit );
		var selected_items = [];

		$( current_values ).each( function( index, value ) {
			var search_for   = { id: this, text: this };
			var data = options.ajax ? $select.data( 'options' ) : options.data;
			var located_item = find_item( search_for, data );

			if ( located_item ) {
				selected_items.push( located_item );
			}
		} );

		if ( selected_items.length && is_multiple ) {
			make_selection( selected_items );
		} else if ( selected_items.length ) {
			make_selection( selected_items[ 0 ] );
		}
	};

	/**
	 * Searches array 'haystack' for objects that match 'description'.
	 *
	 * The 'description' object should take the form { id: number, text: string }. The first
	 * object within the haystack that matches one of those two properties will be returned.
	 *
	 * If objects contain an array named 'children', then that array will also be searched.
	 *
	 * @param {Object} description
	 * @param {Array}  haystack
	 *
	 * @return {Object|boolean}
	 */
	function find_item( description, haystack ) {
		if ( ! $.isArray( haystack ) ) {
			return false;
		}

		for ( var index in haystack ) {
			var possible_match = haystack[ index ];

			if ( possible_match.hasOwnProperty( 'id' ) && possible_match.id == description.id ) {
				return possible_match;
			}

			if ( possible_match.hasOwnProperty( 'text' ) && possible_match.text == description.text ) {
				return possible_match;
			}

			if ( possible_match.hasOwnProperty( 'children' ) && $.isArray( possible_match.children ) ) {
				var subsearch = find_item( description, possible_match.children );

				if ( subsearch ) {
					return subsearch;
				}
			}
		}

		return false;
	}

	obj.element = function ( field, args ) {
		var $select = $( field );
		var args = $.extend( {}, args );
		var carryOverData = [
			'depends',
			'condition',
			'conditionNot',
			'condition-not',
			'conditionNotEmpty',
			'condition-not-empty',
			'conditionEmpty',
			'condition-empty',
			'conditionIsNumeric',
			'condition-is-numeric',
			'conditionIsNotNumeric',
			'condition-is-not-numeric',
			'conditionChecked',
			'condition-is-checked'
		];
		var $container;

		// Add a class for dropdown created
		$select.addClass( obj.selector.created.className() );

		// For Reference we save the jQuery element as an Arg
		args.$select = $select;

		// Auto define the Width of the Select2
		args.dropdownAutoWidth = true;

		// CSS for the container
		args.containerCss = {};

		// Only apply visibility when it's a Visible Select2
		if ( $select.is( ':visible' ) ) {
			args.containerCss.display = 'inline-block';
		}

		// CSS for the dropdown
		args.dropdownCss = {};
		args.dropdownCss.width = 'auto';

		// When we have this we replace the default with what's in the param
		if ( $select.is( '[data-dropdown-css-width]' ) ) {
			args.dropdownCss.width = $select.data( 'dropdown-css-width' );

			if ( ! args.dropdownCss.width ) {
				delete args.dropdownCss.width;
			}
		}

		// How do we match the Search
		args.matcher = obj.matcher;

		if ( ! $select.is( 'select' ) ) {
			// Better Method for finding the ID
			args.id = obj.search_id;
		}

		// By default we allow The field to be cleared
		args.allowClear = true;
		if ( $select.is( '[data-prevent-clear]' ) ) {
			args.allowClear = false;
		}

		// Pass the "Searching..." placeholder if specified
		if ( $select.is( '[data-searching-placeholder]' ) ) {
			args.formatSearching = $select.data( 'searching-placeholder' );
		}

		// If we are dealing with a Input Hidden we need to set the Data for it to work
		if ( $select.is( '[data-options]' ) ) {
			args.data = $select.data( 'options' );

			if ( ! $select.is( 'select' ) ) {
				args.initSelection = obj.init_selection;
			}
		}

		// Don't Remove HTML elements or escape elements
		if ( $select.is( '[data-allow-html]' ) ) {
			args.escapeMarkup = obj.allow_html_markup;
		}

		// Prevents the Search box to show
		if ( $select.is( '[data-hide-search]' ) ) {
			args.minimumResultsForSearch = Infinity;
		}

		// Allows freeform entry
		if ( $select.is( '[data-freeform]' ) ) {
			args.createSearchChoice = obj.freefrom_create_search_choice;
		}

		if ( $select.is( '[multiple]' ) ) {
			args.multiple = true;

			// Set the max select items, if defined
			if ( $select.is( '[data-maximum-selection-size]' ) ) {
				args.maximumSelectionSize = $select.data( 'maximum-selection-size' );
			}

			// If you don't have separator, add one (comma)
			if ( ! $select.is( 'data-separator' ) ) {
				$select.data( 'separator', ',' );
			}

			if ( ! _.isArray( $select.data( 'separator' ) ) ) {
				args.tokenSeparators = [ $select.data( 'separator' ) ];
			} else {
				args.tokenSeparators = $select.data( 'separator' );
			}
			args.separator = $select.data( 'separator' );

			// Define the regular Exp based on
			args.regexSeparatorElements = [ '^(' ];
			args.regexSplitElements = [ '(?:' ];
			$.each( args.tokenSeparators, function ( i, token ) {
				args.regexSeparatorElements.push( '[^' + token + ']+' );
				args.regexSplitElements.push( '[' + token + ']' );
			} );
			args.regexSeparatorElements.push( ')$' );
			args.regexSplitElements.push( ')' );

			args.regexSeparatorString = args.regexSeparatorElements.join( '' );
			args.regexSplitString = args.regexSplitElements.join( '' );

			args.regexToken = new RegExp( args.regexSeparatorString, 'ig' );
			args.regexSplit = new RegExp( args.regexSplitString, 'ig' );
		}

		// Select also allows Tags, so we go with that too
		if ( $select.is( '[data-tags]' ) ) {
			args.tags = $select.data( 'tags' );

			args.initSelection = obj.init_selection;

			args.createSearchChoice = function( term, data ) {
				if ( term.match( args.regexToken ) ) {
					return { id: term, text: term };
				}
			};

			if ( 0 === args.tags.length ) {
				args.formatNoMatches = function() {
					return $select.attr( 'placeholder' );
				};
			}
		}

		// When we have a source, we do an AJAX call
		if ( $select.is( '[data-source]' ) ) {
			var source = $select.data( 'source' );

			// For AJAX we reset the data
			args.data = { results: [] };

			// Allows HTML from Select2 AJAX calls
			args.escapeMarkup = obj.allow_html_markup;

			// Format for Parents breadcrumbs
			args.formatResult = function ( item, container, query ) {
				if ( 'undefined' !== typeof item.breadcrumbs ) {
					return $.merge( item.breadcrumbs, [ item.text ] ).join( ' &#187; ' );
				}

				return item.text;
			};

			args.ajax = { // instead of writing the function to execute the request we use Select2's convenient helper
				dataType: 'json',
				type: 'POST',
				url: obj.ajaxurl(),
				results: function ( response, page, query ) { // parse the results into the format expected by Select2.
					if ( ! $.isPlainObject( response ) || 'undefined' === typeof response.success ) {
						console.error( 'We received a malformed Object, could not complete the Select2 Search.' );
						return { results: [] };
					}

					if ( ! $.isPlainObject( response.data ) || 'undefined' === typeof response.data.results ) {
						console.error( 'We received a malformed results array, could not complete the Select2 Search.' );
						return { results: [] };
					}

					if ( ! response.success ) {
						if ( 'string' === $.type( response.data.message ) ) {
							console.error( response.data.message )
						} else {
							console.error( 'The Select2 search failed in some way... Verify the source.' );
						}
						return { results: [] };
					}

					return response.data;
				}
			};

			// By default only send the source
			args.ajax.data = function( search, page ) {
				return {
					action: 'tribe_dropdown',
					source: source,
					search: search,
					page: page,
					args: $select.data( 'source-args' )
				};
			};
		}

		// Save data on Dropdown
		$select.data( 'dropdown', args );

		$container = ( $select.select2( args ) ).select2( 'container' );

		if ( carryOverData.length > 0 ) {
			carryOverData.map( function ( dataKey ) {
				var attr = 'data-' + dataKey,
					val = $select.attr( attr );

				if ( ! val ) {
					return;
				}

				this.attr( attr, val );
			}, $container );
		}
	};

	obj.action_change =  function( event ) {
		var $select = $( this ),
			data = $( this ).data( 'value' );

		if ( ! $select.is( '[multiple]' ) ) {
			return;
		}

		if ( ! $select.is( '[data-source]' ) ) {
			return;
		}

		if ( event.added ){
			if ( _.isArray( data ) ) {
				data.push( event.added );
			} else {
				data = [ event.added ];
			}
		} else {
			if ( _.isArray( data ) ) {
				data = _.without( data, event.removed );
			} else {
				data = [];
			}
		}

		$select.data( 'value', data ).attr( 'data-value', JSON.stringify( data ) );
	};

	obj.ajaxurl = function () {
		if ( 'undefined' !== typeof window.ajaxurl ) {
			return window.ajaxurl;
		}

		if ( 'undefined' !== typeof TEC && 'undefined' !== typeof TEC.ajaxurl ) {
			return TEC.ajaxurl;
		}

		console.error( 'Dropdowns framework cannot properly do an AJAX request without the WordPress `ajaxurl` variable setup.' )
	};

	obj.action_select2_removed = function( event ) {
		var $select = $( this );

		// Remove the Search
		if ( $select.is( '[data-sticky-search]' ) && $select.is( '[data-last-search]' )  ) {
			$select.removeAttr( 'data-last-search' ).removeData( 'lastSearch' );
		}
	};

	/**
	 * When a Group of Items is selected and it has an ID attached
	 * all the child items will be hidden too because of a bug inside of select2 Core Code
	 *
	 * This method will be applied on `select2-loaded` to remove `select2-selected` class
	 * from all items that were not actually selected
	 */
	obj.action_bugfix_group_select = function( event ) {
		var $select = $( this ),
			items = $select.select2( 'data' ),
			$drop = $( '.select2-drop:visible' );

		// Loop on all selected items to see which match our bug
		$drop.find( '.select2-selected' ).each( function(  ) {
			var $item = $( this ),
				item = $item.data( 'select2-data' ),
				remove = true;

			// Dont mess with non-parent items
			if ( 'undefined' === typeof item.children ) {
				return;
			}

			// Loop on Selected items mark them as not to remove class
			$.each( items, function( k, selected ) {
				if ( obj.search_id( item ) == obj.search_id( selected ) ) {
					remove = false;
				}
			} );

			// Actually remove the class
			if ( remove ) {
				$item.removeClass( 'select2-selected' );
			}
		} );
	};

	obj.action_select2_close = function( event ) {
		var $select = $( this ),
			$search = $( '.select2-drop .select2-input.select2-focused' );

		// If we had a value we apply it again
		if ( $select.is( '[data-sticky-search]' ) ) {
			$search.off( 'keyup-change.tribe' );
		}
	};

	obj.action_select2_open = function( event ) {
		var $select = $( this ),
			$search = $( '.select2-drop .select2-input:visible' );

		// If we have a placeholder for search, apply it!
		if ( $select.is( '[data-search-placeholder]' ) ) {
			$search.attr( 'placeholder', $select.data( 'searchPlaceholder' ) );
		}

		// If we had a value we apply it again
		if ( $select.is( '[data-sticky-search]' ) ) {
			$search.on( 'keyup-change.tribe', function() {
				$select.data( 'lastSearch', $( this ).val() ).attr( 'data-last-search', $( this ).val() );
			} );

			if ( $select.is( '[data-last-search]' ) ) {
				$search.val( $select.data( 'lastSearch' ) ).trigger( 'keyup-change' );
			}
		}
	};

	/**
	 * Configure the Drop Down Fields
	 *
	 * @param  {jQuery} $fields All the fields from the page
	 * @param  {array}  args    Allow extending the arguments
	 *
	 * @return {jQuery}         Affected fields
	 */
	obj.dropdown = function( $fields, args ) {
		var $elements = $fields.not( '.select2-offscreen, .select2-container, ' + obj.selector.created.className() );

		if ( 0 === $elements.length ) {
			return $elements;
		}

		// Default args to avoid Undefined
		if ( ! args ) {
			args = {};
		}

		$elements.each( function( index, element ) {
			// Apply element to all given items and pass args
			obj.element( element, args );
		} )
		.on( 'select2-open', obj.action_select2_open )
		.on( 'select2-close', obj.action_select2_close )
		.on( 'select2-removed', obj.action_select2_removed )
		.on( 'select2-loaded', obj.action_bugfix_group_select )
		.on( 'change', obj.action_change );

		// return to be able to chain jQuery calls
		return $elements;
	};

	$( function() {
		$( obj.selector.dropdown ).tribe_dropdowns();
	} );

	// Addresses some problems with Select2 inputs not being initialized when using a browser's "Back" button.
	$( window ).on( 'unload', function() {
		$( obj.selector.dropdown ).tribe_dropdowns();
	});

} )( jQuery, tribe_dropdowns, window.underscore || window._ );
