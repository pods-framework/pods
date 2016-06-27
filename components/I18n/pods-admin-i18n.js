;( function ( $ ) {

	if ( $('#pods_i18n_settings_save').length ) {
		$(document).on( 'click', '#pods_i18n_settings_save', function() {
			$('#nonce_i18n').appendTo('.pods-admin form#posts-filter');
			$('.pods-admin form#posts-filter').attr( 'method', 'post' ).submit();
		});
	}

	if ( $('.pods-i18n-input').length ) {
		//pods_edit_i18n.init();
		$('.pods-admin .postbox-container #submitdiv').after('<div id="toggle_i18n" class="postbox"><div class="inside"><p><button class="button-secondary">' + pods_admin_i18n_strings.__toggle_translations + '</button></p></div></div>');
		$(document).on( 'click', '.pods-admin .postbox-container #toggle_i18n', function(e) {
			e.preventDefault();
			if ( $('.pods-i18n-input').first().css('display') == 'none' ) {
				$('.pods-i18n-input').each( function() { $(this).show(); } );
			} else {
				$('.pods-i18n-input').each( function() { $(this).hide(); } );
			}
			return false;
		});
	}

	/*pods_edit_i18n = {
		available_languages: {},
		locale_container_selector: '.pods-i18n-input',
		init: function() {
			var locale_container_selector = pods_edit_i18n.locale_container_selector;
			// Hide empty translations
			$( locale_container_selector ).each( function() {

				if ( $(this).attr('data-locale') != '' && typeof pods_edit_i18n.available_languages['__'+$(this).attr('data-locale')] == 'undefined' ) {
					pods_edit_i18n.available_languages[$(this).attr('data-locale')] = $(this).attr('data-locale');
				}

				if ( $('.pods-form-ui-field', this).val() == '' ) {
					$(this).hide();
				}

				if ( $(this).is(':last-child') ) {
					$(this).after( pods_edit_i18n.add_new_link() );
				}
			} );
		},
		add_new_link: function( $languages ) {

			$html = '<div class="pods-i18n-add">';

			$html += '<label></label>';
			$html += '<select class="select-translation">';
			$html += '<option>- '+pods_admin_i18n_strings.__select+' -</option>';
			for ( var lang in pods_edit_i18n.available_languages ) {
				var label = pods_edit_i18n.available_languages[lang];
				if ( typeof pods_admin_i18n_strings[lang] != 'undefined' ) {
					label = pods_admin_i18n_strings[lang];
				}
				$html += '<option value="'+pods_edit_i18n.available_languages[lang]+'">'+label+'</option>';
			}
			$html += '</select>';
			$html += '<a href="" class="add-translation">'+pods_admin_i18n_strings.__add_translation+'</a>';

			$html += '</div>';

			return $html;
		}
	};*/

} )( jQuery );
