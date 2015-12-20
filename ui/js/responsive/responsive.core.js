/*  ==|== Responsive =============================================================
 Author: James South
 twitter : http://twitter.com/James_M_South
 github : https://github.com/ResponsiveBP/Responsive
 Copyright (c),  James South.
 Licensed under the MIT License.
 ============================================================================== */

/*! Responsive v4.1.1 | MIT License | responsivebp.com */

/*
 * Responsive Core
 */

/*global jQuery*/
/*jshint forin:false, expr:true*/
(function ( $, w, d ) {

	"use strict";

	$.pseudoUnique = function ( length ) {
		/// <summary>Returns a pseudo unique alpha-numeric string of the given length.</summary>
		/// <param name="length" type="Number">The length of the string to return. Defaults to 8.</param>
		/// <returns type="String">The pseudo unique alpha-numeric string.</returns>

		var len = length || 8,
			text = "",
			possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",
			max = possible.length;

		if ( len > max ) {
			len = max;
		}

		for ( var i = 0; i < len; i += 1 ) {
			text += possible.charAt( Math.floor( Math.random() * max ) );
		}

		return text;
	};

	$.support.rtl = (function () {
		/// <summary>Returns a value indicating whether the current page is setup for right-to-left languages.</summary>
		/// <returns type="Boolean">
		///      True if right-to-left language support is set up; otherwise false.
		///</returns>

		return $( "html[dir=rtl]" ).length ? true : false;
	}());

	$.support.currentGrid = (function () {
		/// <summary>Returns a value indicating what grid range the current browser width is within.</summary>
		/// <returns type="Object">
		///   An object containing two properties.
		///   &#10;    1: grid - The current applied grid; either xxs, xs, s, m, or l.
		///   &#10;    2: index - The index of the current grid in the range.
		///   &#10;    3: range - The available grid range.
		///</returns>
		return function () {
			var $div = $( "<div/>" ).addClass( "grid-state-indicator" ).prependTo( "body" );

			// These numbers match values in the css
			var grids = [ "xxs", "xs", "s", "m", "l" ],
				key = parseInt( $div.width(), 10 );

			$div.remove();

			return {
				grid : grids[ key ],
				index: key,
				range: grids
			};
		};
	}());

	$.support.scrollbarWidth = (function () {
		/// <summary>Returns a value indicating the width of the browser scrollbar.</summary>
		/// <returns type="Number">The width in pixels.</returns>
		return function () {

			var width = 0;
			if ( d.body.clientWidth < w.innerWidth ) {

				var $div = $( "<div/>" ).addClass( "scrollbar-measure" ).prependTo( "body" );
				width = $div[ 0 ].offsetWidth - $div[ 0 ].clientWidth;

				$div.remove();
			}

			return width;
		};
	}());

	$.toggleBodyLock = function () {
		/// <summary>
		/// Toggles a locked state on the body which toggles both scrollbar visibility and padding on the body.
		/// </summary>

		var $html = $( "html" ),
			$body = $( "body" ),
			bodyPad;

		// Remove.
		if ( $html.attr( "data-lock" ) !== undefined ) {

			bodyPad = $body.data( "bodyPad" );
			$body.css( "padding-right", bodyPad || "" )
				.removeData( "bodyPad" );

			$html.removeAttr( "data-lock" )
				.trigger( $.Event( "unlock.r.bodylock" ) );
			return;
		}

		// Add
		bodyPad = parseInt( $body.css( "padding-right" ) || 0 );
		var scrollWidth = $.support.scrollbarWidth();

		if ( scrollWidth ) {
			$body.css( "padding-right", bodyPad + scrollWidth );

			if ( bodyPad ) {
				$body.data( "bodyPad", bodyPad );
			}

			$html.attr( "data-lock", "" )
				.trigger( $.Event( "lock.r.bodylock", { padding: bodyPad + scrollWidth } ) );
		}
	};

	$.support.transition = (function () {
		/// <summary>Returns a value indicating whether the browser supports CSS transitions.</summary>
		/// <returns type="Boolean">True if the current browser supports css transitions.</returns>

		var transitionEnd = function () {
			/// <summary>Gets transition end event for the current browser.</summary>
			/// <returns type="Object">The transition end event for the current browser.</returns>

			var div = d.createElement( "div" ),
				transEndEventNames = {
					"transition"      : "transitionend",
					"WebkitTransition": "webkitTransitionEnd",
					"MozTransition"   : "transitionend",
					"OTransition"     : "oTransitionEnd otransitionend"
				};

			var names = Object.keys( transEndEventNames ),
				len = names.length;

			for ( var i = 0; i < len; i++ ) {
				if ( div.style[ names[ i ] ] !== undefined ) {
					return { end: transEndEventNames[ names[ i ] ] };
				}
			}

			return false;
		};

		return transitionEnd();

	}());

	$.fn.redraw = function () {
		/// <summary>Forces the browser to redraw by measuring the given target.</summary>
		/// <returns type="jQuery">The jQuery object for chaining.</returns>
		var redraw;
		return this.each( function () {
			redraw = this.offsetWidth;
		} );
	};

	(function () {
		var getDuration = function ( $element ) {
			var rtransition = /\d+(.\d+)?/;
			return (rtransition.test( $element.css( "transition-duration" ) ) ? $element.css( "transition-duration" ).match( rtransition )[ 0 ] : 0) * 1000;
		};

		$.fn.ensureTransitionEnd = function ( duration ) {
			/// <summary>
			/// Ensures that the transition end callback is triggered.
			/// http://blog.alexmaccaw.com/css-transitions
			///</summary>

			if ( !$.support.transition ) {
				return this;
			}

			var called = false,
				$this = $( this ),
				callback = function () {
					if ( !called ) {
						$this.trigger( $.support.transition.end );
					}
				};

			if ( !duration ) {
				duration = getDuration( $this );
			}

			$this.one( $.support.transition.end, function () {
				called = true;
			} );
			w.setTimeout( callback, duration );
			return this;
		};

		$.fn.onTransitionEnd = function ( callback ) {
			/// <summary>Performs the given callback at the end of a css transition.</summary>
			/// <param name="callback" type="Function">The function to call on transition end.</param>
			/// <returns type="jQuery">The jQuery object for chaining.</returns>

			var supportTransition = $.support.transition;
			return this.each( function () {

				if ( !$.isFunction( callback ) ) {
					return;
				}

				var $this = $( this ),
					duration = getDuration( $this ),
					error = duration / 10,
					start = new Date(),
					args = arguments;

				$this.redraw();
				supportTransition ? $this.one( supportTransition.end, function () {
					// Prevent events firing too early.
					var end = new Date();
					if ( end.getMilliseconds() - start.getMilliseconds() <= error ) {
						w.setTimeout( function () {
							callback.apply( this, args );
						}.bind( this ), duration );
						return;
					}

					callback.apply( this, args );

				} ) : callback.apply( this, args );
			} );
		};
	}());

	$.support.touchEvents = (function () {
		return ("ontouchstart" in w) || (w.DocumentTouch && d instanceof w.DocumentTouch);
	}());

	$.support.pointerEvents = (function () {
		return (w.PointerEvent || w.MSPointerEvent);
	}());

	(function () {
		var supportTouch = $.support.touchEvents,
			supportPointer = $.support.pointerEvents;

		var pointerStart = [ "pointerdown", "MSPointerDown" ],
			pointerMove = [ "pointermove", "MSPointerMove" ],
			pointerEnd = [ "pointerup", "pointerout", "pointercancel", "pointerleave",
				"MSPointerUp", "MSPointerOut", "MSPointerCancel", "MSPointerLeave" ];

		var touchStart = "touchstart",
			touchMove = "touchmove",
			touchEnd = [ "touchend", "touchleave", "touchcancel" ];

		var mouseStart = "mousedown",
			mouseMove = "mousemove",
			mouseEnd = [ "mouseup", "mouseleave" ];

		var getEvents = function ( ns ) {
			var estart,
				emove,
				eend;

			// Keep the events separate since support could be crazy.
			if ( supportTouch ) {
				estart = touchStart + ns;
				emove = touchMove + ns;
				eend = (touchEnd.join( ns + " " )) + ns;
			}
			else if ( supportPointer ) {
				estart = (pointerStart.join( ns + " " )) + ns;
				emove = (pointerMove.join( ns + " " )) + ns;
				eend = (pointerEnd.join( ns + " " )) + ns;

			}
			else {
				estart = mouseStart + ns;
				emove = mouseMove + ns;
				eend = (mouseEnd.join( ns + " " )) + ns;
			}

			return {
				start: estart,
				move : emove,
				end  : eend
			};
		};

		var addSwipe = function ( $elem, handler ) {
			/// <summary>Adds swiping functionality to the given element.</summary>
			/// <param name="$elem" type="Object">
			///      The jQuery object representing the given node(s).
			/// </param>
			/// <returns type="jQuery">The jQuery object for chaining.</returns>

			var ns = handler.namespace ? "." + handler.namespace : "",
				eswipestart = "swipestart",
				eswipemove = "swipemove",
				eswipeend = "swipeend",
				etouch = getEvents( ns );

			// Set the touchAction variable for move.
			var touchAction = handler.data && handler.data.touchAction || "none",
				sensitivity = handler.data && handler.data.sensitivity || 5;

			if ( supportPointer ) {
				// Enable extended touch events on supported browsers before any touch events.
				$elem.css( {
					"-ms-touch-action": "" + touchAction + "",
					"touch-action"    : "" + touchAction + ""
				} );
			}

			return $elem.each( function () {
				var $this = $( this );

				var start = {},
					delta = {},
					onMove = function ( event ) {

						// Normalize the variables.
						var isMouse = event.type === "mousemove",
							isPointer = event.type !== "touchmove" && !isMouse,
							original = event.originalEvent,
							moveEvent;

						// Only left click allowed.
						if ( isMouse && event.which !== 1 ) {
							return;
						}

						// One touch allowed.
						if ( original.touches && original.touches.length > 1 ) {
							return;
						}

						// Ensure swiping with one touch and not pinching.
						if ( event.scale && event.scale !== 1 ) {
							return;
						}

						var dx = (isMouse ? original.pageX : isPointer ? original.clientX : original.touches[ 0 ].pageX) - start.x,
							dy = (isMouse ? original.pageY : isPointer ? original.clientY : original.touches[ 0 ].pageY) - start.y;

						var doSwipe,
							percentX = Math.abs( parseFloat( (dx / $this.width()) * 100 ) ) || 100,
							percentY = Math.abs( parseFloat( (dy / $this.height()) * 100 ) ) || 100;

						// Work out whether to do a scroll based on the sensitivity limit.
						switch ( touchAction ) {
							case "pan-x":
								if ( Math.abs( dy ) > Math.abs( dx ) ) {
									event.preventDefault();
								}
								doSwipe = Math.abs( dy ) > Math.abs( dx ) && Math.abs( dy ) > sensitivity && percentY < 100;
								break;
							case "pan-y":
								if ( Math.abs( dx ) > Math.abs( dy ) ) {
									event.preventDefault();
								}
								doSwipe = Math.abs( dx ) > Math.abs( dy ) && Math.abs( dx ) > sensitivity && percentX < 100;
								break;
							default:
								event.preventDefault();
								doSwipe = Math.abs( dy ) > sensitivity || Math.abs( dx ) > sensitivity && percentX < 100 && percentY < 100;
								break;
						}

						event.stopPropagation();

						if ( !doSwipe ) {
							return;
						}

						moveEvent = $.Event( eswipemove, {
							delta: {
								x: dx,
								y: dy
							}
						} );
						$this.trigger( moveEvent );

						if ( moveEvent.isDefaultPrevented() ) {
							return;
						}

						// Measure change in x and y.
						delta = {
							x: dx,
							y: dy
						};
					},
					onEnd = function () {

						// Measure duration
						var duration = +new Date() - start.time,
							endEvent;

						// Determine if slide attempt triggers slide.
						if ( Math.abs( delta.x ) > 1 || Math.abs( delta.y ) > 1 ) {

							// Set the direction and return it.
							var horizontal = delta.x < 0 ? "left" : "right",
								vertical = delta.y < 0 ? "up" : "down",
								direction = Math.abs( delta.x ) > Math.abs( delta.y ) ? horizontal : vertical;

							endEvent = $.Event( eswipeend, {
								delta    : delta,
								direction: direction,
								duration : duration
							} );

							$this.trigger( endEvent );
						}

						// Disable the touch events till next time.
						$this.off( etouch.move ).off( etouch.end );
					};

				$this.off( etouch.start ).on( etouch.start, function ( event ) {

					// Normalize the variables.
					var isMouse = event.type === "mousedown",
						isPointer = event.type !== "touchstart" && !isMouse,
						original = event.originalEvent;

					if ( (isPointer || isMouse) && $( event.target ).is( "img" ) ) {
						event.preventDefault();
					}

					event.stopPropagation();

					// Measure start values.
					start = {
						// Get initial touch coordinates.
						x: isMouse ? original.pageX : isPointer ? original.clientX : original.touches[ 0 ].pageX,
						y: isMouse ? original.pageY : isPointer ? original.clientY : original.touches[ 0 ].pageY,

						// Store time to determine touch duration.
						time: +new Date()
					};

					var startEvent = $.Event( eswipestart, { start: start } );

					$this.trigger( startEvent );

					if ( startEvent.isDefaultPrevented() ) {
						return;
					}

					// Reset delta and end measurements.
					delta = { x: 0, y: 0 };

					// Attach touchmove and touchend listeners.
					$this.on( etouch.move, onMove )
						.on( etouch.end, onEnd );
				} );
			} );
		};

		var removeSwipe = function ( $elem, handler ) {
			/// <summary>Removes swiping functionality from the given element.</summary>

			var ns = handler.namespace ? "." + handler.namespace : "",
				etouch = getEvents( ns );

			return $elem.each( function () {

				// Disable extended touch events on ie.
				// Unbind events.
				$( this ).css( { "-ms-touch-action": "", "touch-action": "" } )
					.off( etouch.start ).off( etouch.move ).off( etouch.end );
			} );
		};

		// Create special events so we can use on/off.
		$.event.special.swipe = {
			add   : function ( handler ) {
				addSwipe( $( this ), handler );
			},
			remove: function ( handler ) {
				removeSwipe( $( this ), handler );
			}
		};
	}());

	$.extend( $.expr[ ":" ], {
		attrStart: function ( el, i, props ) {
			/// <summary>Custom selector extension to allow attribute starts with selection.</summary>
			/// <param name="el" type="DOM">The element to test against.</param>
			/// <param name="i" type="Number">The index of the element in the stack.</param>
			/// <param name="props" type="Object">Metadata for the element.</param>
			/// <returns type="Boolean">True if the element is a match; otherwise, false.</returns>
			var hasAttribute = false;

			$.each( el.attributes, function () {
				if ( this.name.indexOf( props[ 3 ] ) === 0 ) {
					hasAttribute = true;
					return false;  // Exit the iteration.
				}
				return true;
			} );

			return hasAttribute;
		}
	} );

	$.getDataOptions = function ( $elem, filter ) {
		/// <summary>Creates an object containing options populated from an elements data attributes.</summary>
		/// <param name="$elem" type="jQuery">The object representing the DOM element.</param>
		/// <param name="filter" type="String">The prefix with filter to identify the data attribute.</param>
		/// <returns type="Object">The extended object.</returns>
		var options = {};
		$.each( $elem.data(), function ( key, val ) {
			if ( key.indexOf( filter ) === 0 && key.length > filter.length ) {

				// Build a key with the correct format.
				var length = filter.length,
					newKey = key.charAt( length ).toLowerCase() + key.substring( length + 1 );

				options[ newKey ] = val;
			}
		} );

		return Object.keys( options ).length ? options : $elem.data();
	};

	$.debounce = function ( func, wait, immediate ) {
		/// <summary>
		/// Returns a function, that, as long as it continues to be invoked, will not
		/// be triggered. The function will be called after it stops being called for
		/// N milliseconds. If `immediate` is passed, trigger the function on the
		/// leading edge, instead of the trailing.
		///</summary>
		/// <param name="func" type="Function">
		///      The function to debounce.
		/// </param>
		/// <param name="wait" type="Number">
		///      The number of milliseconds to delay.
		/// </param>
		/// <param name="wait" type="immediate">
		///      Specify execution on the leading edge of the timeout.
		/// </param>
		/// <returns type="jQuery">The function.</returns>
		var timeout;
		return function () {
			var context = this, args = arguments;
			w.clearTimeout( timeout );
			timeout = w.setTimeout( function () {
				timeout = null;
				if ( !immediate ) {
					func.apply( context, args );
				}
			}, wait );
			if ( immediate && !timeout ) {
				func.apply( context, args );
			}
		};
	};

	(function ( old ) {
		/// <summary>Override the core html method in the jQuery object to fire a domchanged event whenever it is called.</summary>
		/// <param name="old" type="Function">
		///      The jQuery function being overridden.
		/// </param>
		/// <returns type="jQuery">The jQuery object for chaining.</returns>

		var echanged = $.Event( "domchanged" ),
			$d = $( d );

		$.fn.html = function () {
			// Execute the original html() method using the augmented arguments collection.
			var result = old.apply( this, arguments );

			if ( arguments.length ) {
				$d.trigger( echanged );
			}

			return result;

		};
	})( $.fn.html );
}( jQuery, window, document ));