( function ( $ ) {

	/**
	 * Close the modal on form submit, if one is open
	 */
	var pods_modal_submit = function( e ) {

		// @todo: good chance we're in a race condition in some cases
		// This will trigger the unload event for the modal, which in turn
		// will make an ajax request for updated markup.  We have no
		// guarantee that what we're saving from the modal will complete
		// before our ajax update in all cases.  What we need are events on
		// completion we could attach to.

		var $modal_target = parent.window.jQuery( '.pods-modal.showing-modal' );
		if ( $modal_target.length > 0 ) {
			$modal_target.modal( 'hide' );
		}
	};

	$( '#addtag #submit' ).on( 'click', pods_modal_submit );
	$( 'form#post, form#createuser' ).on( 'submit', pods_modal_submit );
	$( document ).on( 'pods_submit_success', pods_modal_submit );

	/**
	 * Modal display and ajax updates
	 */
		// @todo: hard-coded class selector
	$( document ).on( 'click', '.pods-related-edit', function( e ) {

		var $add_new_button = $( this );

		var pod_id = $add_new_button.data( 'pod-id' );
		var field_id = $add_new_button.data( 'field-id' );
		var item_id = $add_new_button.data( 'item-id' );

		e.preventDefault();

		// Add a class and setup a handler function for when the popup unloads
		$add_new_button.addClass( 'showing-modal' ).on( 'hidden.r.modal', function () {
			$( this ).removeClass( 'showing-modal' );

			var data = {
				'action'  : 'pods_relationship_popup', // @todo: hardcoded constant
				'pod_id'  : pod_id,
				'field_id': field_id,
				'item_id' : item_id
			};

			// @todo: check failure as well?
			$.post( ajaxurl, data, response_success( $add_new_button ) );
		} );

		/**
		 * Return the callback function that handles a successful response from the ajax post call
		 *
		 * @returns {Function}
		 * @param $add_new_button
		 */
		var response_success = function( $add_new_button ) {

			// We return a function to be used as the callback, this allows us to expose the target element as a passed param
			return function( response ) {

				// Update the DOM (this might ideally be a method the view handles in the UI)
				// @todo: hardcoded constant in the selector
				var $field_container = $add_new_button.parents( '.podsform-field-container' );
				$field_container.html( response );

				// Current implementation replaces the button, re-bind for modal
				var $modal_target = $field_container.find( '.pods-modal' );
				if ( $modal_target.length > 0 ) {
					$modal_target.modal( $.getDataOptions( $modal_target, 'modal' ) );
				}
			};
		};

	} );

} )( jQuery );
