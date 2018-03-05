require( './media/manifest' );

( function( $ ) {
	var l10n = wp.media.view.l10n.iconPicker,
		templates = {},
		frame, selectIcon, removeIcon, getFrame, updateField, updatePreview, $field;

	getFrame = function() {
		if ( ! frame ) {
			frame = new wp.media.view.MediaFrame.IconPicker();

			frame.target.on( 'change', updateField );
		}

		return frame;
	};

	updateField = function( model ) {
		_.each( model.get( 'inputs' ), function( $input, key ) {
			$input.val( model.get( key ) );
		});

		model.clear({ silent: true });
		$field.trigger( 'ipf:update' );
	};

	updatePreview = function( e ) {
		var $el     = $( e.currentTarget ),
		    $select = $el.find( 'a.ipf-select' ),
		    $remove = $el.find( 'a.ipf-remove' ),
		    type    = $el.find( 'input.ipf-type' ).val(),
		    icon    = $el.find( 'input.ipf-icon' ).val(),
		    url     = $el.find( 'input.url' ).val(),
		    template;

		if ( type === '' || icon === '' || ! _.has( iconPicker.types, type ) ) {
			$remove.addClass( 'hidden' );
			$select
				.removeClass( 'has-icon' )
				.addClass( 'button' )
				.text( l10n.selectIcon )
				.attr( 'title', '' );

			return;
		}

		if ( templates[ type ]) {
			template = templates[ type ];
		} else {
			template = templates[ type ] = wp.template( 'iconpicker-' + iconPicker.types[ type ].templateId + '-icon' );
		}

		$remove.removeClass( 'hidden' );
		$select
			.attr( 'title', l10n.selectIcon )
			.addClass( 'has-icon' )
			.removeClass( 'button' )
			.html( template({
				type: type,
				icon: icon,
				url:  url
			}) );
	};

	selectIcon = function( e ) {
		var frame = getFrame(),
			model = { inputs: {} };

		e.preventDefault();

		$field   = $( e.currentTarget ).closest( '.ipf' );
		model.id = $field.attr( 'id' );

		// Collect input fields and use them as the model's attributes.
		$field.find( 'input' ).each( function() {
			var $input = $( this ),
			    key    = $input.attr( 'class' ).replace( 'ipf-', '' ),
			    value  = $input.val();

			model[ key ]        = value;
			model.inputs[ key ] = $input;
		});

		frame.target.set( model, { silent: true });
		frame.open();
	};

	removeIcon = function( e ) {
		var $el = $( e.currentTarget ).closest( 'div.ipf' );

		$el.find( 'input' ).val( '' );
		$el.trigger( 'ipf:update' );
	};

	$( document )
		.on( 'click', 'a.ipf-select', selectIcon )
		.on( 'click', 'a.ipf-remove', removeIcon )
		.on( 'ipf:update', 'div.ipf', updatePreview );

	$( 'div.ipf' ).trigger( 'ipf:update' );
}( jQuery ) );
