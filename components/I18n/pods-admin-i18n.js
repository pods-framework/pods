;(function ( $ ) {

	/**
	 * Component admin page
	 */
	PodsAdminI18n = {
		init : function () {
			if ( $( '#pods_i18n_settings_save' ).length ) {
				this.initSave();
			}
		},

		initSave : function () {
			$( document ).on( 'click', '#pods_i18n_settings_save #submit', function () {
				$( '#_nonce_i18n' ).appendTo( '.pods-admin form#posts-filter' );

				$( '.pods-admin form#posts-filter' ).prop( 'method', 'post' ).submit();
			} );
		}
	};

	if ( $( '#pods_admin_i18n' ).length ) {
		PodsAdminI18n.init();
	}

})( jQuery );
