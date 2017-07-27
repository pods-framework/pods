;( function ( $ ) {

	/**
	 * Component admin page
	 */
	PodsAdminI18n = {
		init: function() {
			if ( $('#pods_i18n_settings_save').length ) {
				this.initSave();
			}
		},

		initSave: function () {
			$( document ).on( 'click', '#pods_i18n_settings_save #submit', function() {
				$( '#_nonce_i18n' ).appendTo( '.pods-admin form#posts-filter' );

				$( '.pods-admin form#posts-filter' ).prop( 'method', 'post' ).submit();
			} );
		}
	};

	if ( $('#pods_admin_i18n').length ) {
		PodsAdminI18n.init();
	}


	/**
	 * Pod edit page
	 */
	PodsEditI18n = {
		i18nVisible: true,
		selector: '.pods-i18n-input',
		toggleSpeed: 100,

		init: function() {
			this.toggleI18nInputs();
			this.dynamicAddRemoveInputs();
		},

		validateI18nVisibility: function() {
			/**
			 * Check if we're on the fields tab
			 * If we're on a fields tab, check if the first i18n input is visible and change this object's visibility property
			 */
			if ( $('#pods-manage-fields').is(':visible') && $('#pods-manage-fields .pods-manage-list .pods-manage-row-expanded').length ) {
				var first = $('#pods-manage-fields .pods-manage-list .pods-manage-row-expanded' ).first().find( PodsEditI18n.selector ).first();
				if ( first.is(':visible') ) {
					PodsEditI18n.i18nVisible = true;
				} else {
					PodsEditI18n.i18nVisible = false;
				}
			}

		},

		toggleI18nInputs: function() {

			// Toggle i18n inputs for pod options
			// @todo  Enable auto-toggle when opening a field
			$(document).on( 'click', 'button#toggle_i18n', function(e) {
				e.preventDefault();

				PodsEditI18n.validateI18nVisibility();

				if ( PodsEditI18n.i18nVisible ) {

					PodsEditI18n.i18nVisible = false;
					$( PodsEditI18n.selector ).each( function() {
						$( this ).slideUp( PodsEditI18n.toggleSpeed, function() {
							// Fallback for hidden fields
							$( this ).css('display', 'none');
						} );
					} );

				} else {

					PodsEditI18n.i18nVisible = true;
					$( PodsEditI18n.selector ).each( function() {
						$( this ).slideDown( PodsEditI18n.toggleSpeed, function() {
							// Fallback for hidden fields
							$( this ).css('display', 'block');
						} );
					} );

				}
				return false;
			});
		},

		dynamicAddRemoveInputs: function() {

			$(document).on( 'change', '#pods_i18n .pods-enable-disable-language input', function(e) {

				PodsEditI18n.validateI18nVisibility();

				var locale = $(this).parents('.pods-enable-disable-language').attr('data-locale');

				if ( $(this).is(':checked') ) {

					// Get the index for this locale
					var index = 0;
					$('#pods_i18n .pods-enable-disable-language input:checked').each( function() {
						if ( $( this ).parents('.pods-enable-disable-language').attr( 'data-locale' ) == locale ) {
							return false;
						}
						index++;
					});

					$('.pods-i18n-field').each( function() {
						if ( $('.pods-i18n-input-' + locale, this).length ) {
							$('.pods-i18n-input-' + locale, this).slideDown( PodsEditI18n.toggleSpeed, function() {
								$('input', this).removeAttr('disabled');
							});
						} else {
							var name = $(this).parent().children('input').first().attr('name');
							// Place the new input on the right index
							if ( $( '.pods-i18n-input:visible', this ).eq(index).length ) {
								$( '.pods-i18n-input:visible', this ).eq(index).before( PodsEditI18n.i18nInputTemplate( name, locale ) );
							} else {
								$( this ).append( PodsEditI18n.i18nInputTemplate( name, locale ) );
							}
							if ( PodsEditI18n.i18nVisible ) {
								$('.pods-i18n-input-' + locale, this).slideDown( PodsEditI18n.toggleSpeed );
							}
						}
					});

				} else {
					$('.pods-i18n-input-' + locale + ' input').each( function() {
						$(this).parent().slideUp( PodsEditI18n.toggleSpeed, function() {
							$('input', this).attr('disabled', 'disabled');
						});
					});
				}

			});

		},

		i18nInputTemplate: function( name, locale ) {

			var locale_clean = locale.toLowerCase().replace('_', '-');
			var name_clean = name.toLowerCase().replace('_', '-');

			html  = '<div class="pods-i18n-input pods-i18n-input-'+locale+'" data-locale="'+locale+'" style="display: none;">';
			html += '<label class="pods-form-ui-label" for="pods-form-ui-label-'+locale_clean+'"><small><code style="font-size: 1em;">'+locale+'</code></small></label>';
			html += '<input name="'+name+'_'+locale+'" data-name-clean="'+name_clean+'-'+locale_clean+'" id="pods-form-ui-label-'+locale_clean+'" class="pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-'+name_clean+'-'+locale_clean+'" type="text" value="" tabindex="2" maxlength="255">';
			html += '</div>';
			return html;
		}

	};

	if ( $('#post-body').length ) {
		PodsEditI18n.init();
	}

} )( jQuery );
