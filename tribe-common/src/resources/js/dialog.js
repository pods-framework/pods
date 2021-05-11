var tribe = tribe || {};
tribe.dialogs = tribe.dialogs || {};

( function( $, obj ) {
	'use strict';

	var $document = $( document );
	obj.dialogs = obj.dialogs || [];
	obj.events = obj.events || {};

	/**
	 * Get the dialog name.
	 *
	 * @since 4.11.3
	 *
	 * @param {obj} dialog The dialog object
	 *
	 * @return {string} the dialog name.
	 */
	obj.getDialogName = function( dialog ) {
		return 'dialog_obj_' + dialog.id;
	};

	/**
	 * Initialize tribe dialogs.
	 *
	 * @since 4.11.3
	 *
	 * @return {void}
	 */
	obj.init = function() {
		obj.dialogs.forEach( function( dialog ) {
			var objName      = obj.getDialogName( dialog );
			var a11yInstance = new window.A11yDialog( {
				appendTarget: dialog.appendTarget,
				bodyLock: dialog.bodyLock,
				closeButtonAriaLabel: dialog.closeButtonAriaLabel,
				closeButtonClasses: dialog.closeButtonClasses,
				contentClasses: dialog.contentClasses,
				effect: dialog.effect,
				effectEasing: dialog.effectEasing,
				effectSpeed: dialog.effectSpeed,
				overlayClasses: dialog.overlayClasses,
				overlayClickCloses: dialog.overlayClickCloses,
				trigger: dialog.trigger,
				wrapperClasses: dialog.wrapperClasses,
			} );

			window[ objName ] = a11yInstance;
			dialog.a11yInstance = a11yInstance;

			window[ objName ].on( 'show', function( dialogEl, event ) {
				if ( event ) {
					event.preventDefault();
					event.stopPropagation();
				}

				$( obj.events ).trigger( dialog.showEvent, [ dialogEl, event ] );
			} );

			window[ objName ].on( 'hide', function ( dialogEl, event ) {
				if ( event ) {
					event.preventDefault();
					event.stopPropagation();
				}

				$( obj.events ).trigger( dialog.closeEvent, [ dialogEl, event ] );
			} );
		} );
	};

	$( obj.init );

} )( jQuery, tribe.dialogs );