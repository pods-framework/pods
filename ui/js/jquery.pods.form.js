( function( $ ) {

	var $el,
		field_types = {

			// DB fields
			db: function() {

				$el.on( 'change.PodsForm', function() {
					var newval = $el.val()
						.toLowerCase()
						.replace( /([- ])/g, '_' )
						.replace( /([^0-9a-z_])/g, '' )
						.replace( /(_){2,}/g, '_' )
						.replace( /_$/, '' );

					$el.val( newval );
				} );

				return this;

			},

			// Slug fields
			slug: function() {

				$el.on( 'change.PodsForm', function() {
					var newval = $el.val()
						.toLowerCase()
						.replace( /([_ ])/g, '-' )
						.replace( /([`~!@#$%^&*()_|+=?;:'",.<>\{\}\[\]\\\/])/g, '' )
						.replace( /(\-){2,}/g, '-' )
						.replace( /\-$/, '' );

					$el.val( newval );
				} );

				return this;

			},

			// Attachment fields @deprecated as of WP 3.5+
			attachment: function( args ) {
				options = {
					max_files: 1
				};

				$.extend( options, args );

				// init sortable
				$( 'ul.pods-files', $el ) .sortable( {
					containment : 'parent',
					axis : 'y',
					scrollSensitivity : 40,
					tolerance : 'pointer',
					opacity : 0.6
				} );

				// hook delete links
				$el.on( 'click.PodsForm', 'li.pods-file-delete', function() {
					var $this = $( this ),
						file = $this.parent().parent();

					file.slideUp( function() {

						// check to see if this was the only entry
						if ( file.parent().children().length == 1 ) { // 1 because we haven't removed our target yet
							file.parent().hide();
						}

						// remove the entry
						$this.remove();

					} );
				} );

				// hook the add link
				$el.on( 'click.PodsForm', 'a.pods-file-add', function( e ) {
					e.preventDefault();

					var $this = $( this ),
						href = $this.prop( 'href' ),
						width = $( window ).width(),
						windowH = $( window ).height(),
						windowW = ( 720 < width ) ? 720 : width;

					if ( !href ) {
						return;
					}

					href = href.replace( /&width=[0-9]+/g, '' )
						.replace( /&height=[0-9]+/g, '' );

					$this.prop( 'href', href + '&width=' + ( windowW - 80 ) + '&height=' + ( windowH - 85 ) );

					file_context = $this.parent().find( 'ul.pods-files' );

					file_thickbox_modder = setInterval( function() {

							// @todo Merge pods_attachments
							if ( file_context ) {
								pods_attachments( $el.prop( 'id' ), options.max_files );
							}

						},
						500
					);

					// @todo JS l18n
					tb_show( 'Attach a file', e.target.href, false );

					return false;

				} );
			},

			// CLEditor
			cleditor: function() {
				$el.cleditor( {
					width : $el.outerWidth()
				} );
			},

			// Color
			color: function() {

			},

			// Currency
			currency: function() {

			},

			//  Date
			date: function() {

			},

			// Time
			time: function() {

			},

			// E-mail
			email: function() {

			},

			// Farbtastic
			farbtastic: function() {

			},

			// Loop
			loop: function() {

			},

			// Media
			media: function() {

			},

			// Number
			number: function() {

			},

			// Phone
			Phone: function() {

			},

			// Plupload
			plupload: function() {

			},

			// Slider
			slider: function() {

			},

			// Textarea
			textarea: function() {

			},

			// TinyMCE
			tinymce: function() {

			},

			// Website
			website: function() {

			},

			// Remove all functionality for a field
			destroy: function() {

				if ( field_types[ 'destroy_' + field_type ] ) {
					field_types[ 'destroy_' + field_type ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
				}

				$el.off( '.PodsForm' );

				return this;

			}

		};

	$.fn.PodsForm = function( field_type ) {

		$el = $( this );

        if ( field_types[ field_type ] ) {
            return this.each( function() {

				return field_types[ field_type ].apply( this, Array.prototype.slice.call( arguments, 1 ) );

			} );
        }
        else {
            return $.error( 'Field Type method ' + field_type + ' does not exist on jQuery.PodsForm' );
        }

	}

} );