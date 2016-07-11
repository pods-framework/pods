// JavaScript Document

jQuery(document).ready(function($){

	var podsLink = {
		wpLinkDefaults: null,
		activeLinkPicker: null,
		init: function() {
			
			// Open wpLink modal
			$(document).on('click', '.pods-field .podsLinkPopup', function (e) {
				// Store current field pointer
				podsLink.activeLinkPicker = $(this).parents('.pods-link-options');
				$('body').addClass('modal-open-pods-field-link');

				var url_elem = podsLink.activeLinkPicker.find('.linkPickerUrl');
				var text_elem = podsLink.activeLinkPicker.find('.linkPickerText');
				var target_elem = podsLink.activeLinkPicker.find('.linkPickerTarget');

				wpLink.setDefaultValues = function() {
					$('#wp-link-wrap #wp-link-url').val( url_elem.val() );
					$('#wp-link-wrap #wp-link-text').val( text_elem.val() );
					if ( target_elem.is(':checked') ) {
						$('#wp-link-wrap #wp-link-target').prop('checked', true);
					} else {
						$('#wp-link-wrap #wp-link-target').prop('checked', false);
					}
				};

		        // save any existing default initialization
		        podsLink.wpLinkDefaults = wpLink.setDefaultValues;

				// Open modal in the dummy textarea
				wpLink.open( 'pods-link-editor-hidden' );

				return false;
			});
			
			// Save changes in the selected field
			$(document).on('click', '#wp-link-submit', function(event) {

				// Is the wpLink modal open?
				if ($('body').hasClass('modal-open-pods-field-link')) {
					//get the href attribute and add to a textfield, or use as you see fit
					//the links attributes (href, target) are stored in an object, which can be access via  wpLink.getAttrs()
					var linkAtts = wpLink.getAttrs();
					if (linkAtts.href != '') {
						podsLink.activeLinkPicker.find('.linkPickerUrl').val(linkAtts.href);
					}
					//get the target attribute
					if (linkAtts.target == '_blank') {
						podsLink.activeLinkPicker.find('.linkPickerTarget').prop('checked', true);
					} else {
						podsLink.activeLinkPicker.find('.linkPickerTarget').prop('checked', false);
					}
					//get the text attribute
					var linkText = $('#wp-link-wrap #wp-link-text').val();
					if (linkText != '') {
						podsLink.activeLinkPicker.find('.linkPickerText').val(linkText);
					}
				}

				// Reset wpLink modal
				podsLink.resetLink();

				//trap any events
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();
				return false;
			});
			
			// Close modal without any changes
			$(document).on('click', '#wp-link-cancel, #wp-link-close', function(event) {

				// Reset wpLink modal
				podsLink.resetLink();

				//trap any events
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();
				return false;
			});	

		},
	    resetLink: function() {
	    	$('body').removeClass('modal-open-pods-field-link');
	        wpLink.textarea = $('body'); // to close the link dialogue, it is again expecting an wp_editor instance, so you need to give it something to set focus back to. In this case, I'm using body, but the textfield with the URL would be fine
	        wpLink.close();// close the dialogue

	        // restore wplink default initialization
	        wpLink.setDefaultValues = podsLink.wpLinkDefaults;
	    }
	};

	// Validate that we have the right resourses
	if ( typeof wpLink !== 'undefined' && $('#wp-link-wrap').length ) {
		$('.pods-field .link-existing-content').show();
		podsLink.init();
	}
});

