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

		initSave: function() {
			$(document).on( 'click', '#pods_i18n_settings_save #submit', function() {
				$('#nonce_i18n').appendTo('.pods-admin form#posts-filter');
				$('.pods-admin form#posts-filter').attr( 'method', 'post' ).submit();
			});
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
		},

		toggleI18nInputs: function() {

			// Toggle i18n inputs for pod options
			$(document).on( 'click', 'button#toggle_i18n', function(e) {
				e.preventDefault();

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
		}

	};

	if ( $('#post-body').length ) {
		PodsEditI18n.init();
	}

} )( jQuery );
