(function( $, _ ) {
	'use strict';
	// Configure on Document ready for the default trigger
	$( document ).ready( function() {
		$( '.tribe-bumpdown-trigger' ).bumpdown();
	} );

	$.fn.bumpdown = function() {
		var $document = $( document ),
			selectors = {
				// A template for the ID if we don't have one already
				ID: 'tribe-bumpdown-',
				data_trigger: function( ID ) {
					return '[data-trigger="' + ID + '"]';
				},
				bumpdown: '.tribe-bumpdown',
				content: '.tribe-bumpdown-content',
				trigger: '.tribe-bumpdown-trigger',
				hover_trigger: '.tribe-bumpdown-trigger:not(.tribe-bumpdown-nohover)',
				close: '.tribe-bumpdown-close',
				permanent: '.tribe-bumpdown-permanent',
				active: '.tribe-bumpdown-active'
			},
			methods = {
				open: function( $bumpdown ) {
					var data = $bumpdown.data( 'bumpdown' ),
						width_rule = data.$trigger.data( 'width-rule' );

					if ( $bumpdown.is( ':visible' ) ) {
						return;
					}

					// Adds a Class to signal it's active
					data.$trigger.addClass( selectors.active.replace( '.', '' ) );

					var $content = $bumpdown.find( selectors.content );

					if ( 'string' === typeof width_rule && 'all-triggers' === width_rule ) {
						var min_width = 600;
						var trigger_position = 0;
						$( selectors.trigger ).each( function() {
							var $el = $( this );

							// only attempt to align items with a width rule
							if ( ! $el.data( 'width-rule' ) ) {
								return;
							}

							var position = $el.position();

							if ( position.left > trigger_position ) {
								trigger_position = position.left;
							}
						} );

						if ( trigger_position ) {
							trigger_position = trigger_position > min_width ? trigger_position : min_width;

							$content.css( 'max-width', trigger_position + 'px' );
						}
					}

					$content.prepend( '<a class="tribe-bumpdown-close" title="Close"><i class="dashicons dashicons-no"></i></a>' );
					$content.prepend( '<span class="tribe-bumpdown-arrow"></span>' );
					methods.arrow( $bumpdown );

					$bumpdown.data( 'preventClose', true );
					$bumpdown.slideDown( 'fast', function() {
						$bumpdown.data( 'preventClose', false );
					} );
				},
				close: function( $bumpdown ) {
					var data = $bumpdown.data( 'bumpdown' );

					if ( ! $bumpdown.is( ':visible' ) || $bumpdown.data( 'preventClose' ) ) {
						return;
					}

					// When we close we reset the flag about hoverintent
					$( this ).removeData( 'is_hoverintent_queued' );

					$bumpdown.find( '.tribe-bumpdown-close, .tribe-bumpdown-arrow' ).remove();
					$bumpdown.not( '.tribe-bumpdown-trigger' ).slideUp( 'fast' );

					data.$trigger.removeClass( selectors.active.replace( '.', '' ) );
				},
				arrow: function( $bumpdown ) {
					var data = $bumpdown.data( 'bumpdown' ),
						arrow;

					arrow = Math.ceil( data.$trigger.position().left - ( 'block' === data.type ? data.$parent.offset().left : 0 ) );

					data.$bumpdown.find( '.tribe-bumpdown-arrow' ).css( 'left', arrow );
				}
			};

		$( window ).on( {
			'resize.bumpdown': function() {
				$document.find( selectors.active ).each( function() {
					methods.arrow( $( this ) );
				} );
			}
		} );

		$document
			// Use hoverIntent to make sure we are not opening Bumpdown on a fast hover
			.hoverIntent( {
				over: function() {
					var data = $( this ).data( 'bumpdown' );

					// Flags that it's open
					data.$trigger.data( 'is_hoverintent_queued', false );

					// Actually opens
					data.$bumpdown.trigger( 'open.bumpdown' );
				},
				out: function() {}, // Prevents Notice on JS
				selector: selectors.hover_trigger,
				interval: 200
			} )

			// Setup Events on Trigger
			.on( {
				mouseenter: function() {
					if ( $( this ).data( 'is_hoverintent_queued' ) === undefined ) {
						// Flags that hoverIntent will take care of the
						$( this ).data( 'is_hoverintent_queued', true );
					}
				},
				click: function( e ) {
					var data = $( this ).data( 'bumpdown' );
					e.preventDefault();
					e.stopPropagation();

					if ( data.$bumpdown.is( ':visible' ) ) {
						// Makes sure we are not dealing with the first enter of the mouse
						if ( data.$trigger.data( 'is_hoverintent_queued' ) ) {
							// On double click it will close, kinda like forcing the closing
							return data.$trigger.data( 'is_hoverintent_queued', false );
						}

						data.$bumpdown.trigger( 'close.bumpdown' );
					} else {
						data.$bumpdown.trigger( 'open.bumpdown' );
					}
				},
				'open.bumpdown': function() { methods.open( $( this ) ); },
				'close.bumpdown': function() { methods.close( $( this ) ); }
			}, selectors.trigger )

			// Setup Events on Trigger
			.on( {
				click: function( e ) {
					var data = $( this ).parents( selectors.bumpdown ).first().data( 'bumpdown' );

					e.preventDefault();
					e.stopPropagation();

					if ( 'undefined' === typeof data ) {
						return;
					}

					if ( 'undefined' === typeof data.$bumpdown ) {
						return;
					}

					data.$bumpdown.trigger( 'close.bumpdown' );
				},
			}, selectors.close )

			// Triggers closing when clicking on the document
			.on( 'click', function( e ) {
				var $target = $( e.target ),
					is_bumpdown = $target.is( selectors.bumpdown ) || 0 !== $target.parents( selectors.bumpdown ).length;

				if ( is_bumpdown ) {
					return;
				}

				$( selectors.trigger ).not( selectors.permanent ).trigger( 'close.bumpdown' );
			} )

			// Creates actions on the actual bumpdown
			.on( {
				'open.bumpdown': function() { methods.open( $( this ) ); },
				'close.bumpdown': function() { methods.close( $( this ) ); }
			}, selectors.bumpdown );

		// Configure all the fields
		return this.each( function() {
			var data = {
					// Store the jQuery Elements
					$trigger: $( this ),
					$parent: null,
					$bumpdown: null,

					// Store other Variables
					ID: null,
					html: null,
					type: 'block',

					// Flags
					is_permanent: false
				};

			// We need a ID for this Bumpdown
			data.ID = data.$trigger.attr( 'id' );

			// If we currently don't have the ID, set it up
			if ( ! data.ID ) {
				data.ID = _.uniqueId( selectors.ID );

				// Apply the given ID to
				data.$trigger.attr( 'id', data.ID );
			}

			// We fetch from `[data-bumpdown]` attr the possible HTML for this Bumpdown
			data.html = data.$trigger.attr( 'data-bumpdown' );
			data.html = '<div class="tribe-bumpdown-content">' + data.html + '</div>';

			// We fetch from `[data-bumpdown-class]` attr the possible class(es) for this Bumpdown
			data.class = data.$trigger.attr( 'data-bumpdown-class' );

			// Flags about if this bumpdown is permanent, meaning it only closes when clicking on the close button or the trigger
			data.is_permanent = data.$trigger.is( selectors.permanent );

			// Fetch the first Block-Level parent
			data.$parent = data.$trigger.parents().filter( function() {
				return -1 < $.inArray( $( this ).css( 'display' ), [ 'block', 'table', 'table-cell', 'table-row' ] );
			}).first();

			if ( ! data.html ) {
				data.$bumpdown = $( selectors.data_trigger( data.ID ) );
				data.type = 'block';
			} else {
				data.type = data.$parent.is( 'td, tr, td, table' ) ? 'table' : 'block';

				if ( 'table' === data.type ) {
					data.$bumpdown = $( '<td>' ).attr( { colspan: 2 } ).addClass( 'tribe-bumpdown-cell' ).html( data.html );
					var classes = data.class ? 'tribe-bumpdown-row ' + data.class : 'tribe-bumpdown-row',
						$row = $( '<tr>' ).append( data.$bumpdown ).addClass( classes );

					data.$parent = data.$trigger.parents( 'tr' ).first();

					data.$parent.after( $row );
				} else {
					data.$bumpdown = $( '<div>' ).addClass( 'tribe-bumpdown-block' ).html( data.html );
					data.$trigger.after( data.$bumpdown );
				}
			}

			// Setup data on trigger
			data.$trigger
				.data( 'bumpdown', data )

				// Mark this as the trigger
				.addClass( selectors.trigger.replace( '.', '' ) );


			// Setup data on actual bumpdown
			data.$bumpdown
				.data( 'bumpdown', data )

				// Mark it as the Bumpdown
				.addClass( selectors.bumpdown.replace( '.', '' ) );

			// support our dependency library
			if ( data.$trigger.data( 'depends' ) ) {
				var field_ids = data.$trigger.data( 'depends' );
				$( document ).on( 'change', field_ids, function() {
					methods.close( data.$bumpdown );
				} );
			}
		});
	};
}( jQuery, window.underscore || window._ ) );
