// JavaScript Document

jQuery(document).ready(function($){
	
	// Variable to store the active link picker field
	var pods_active_link_picker;

	var pods_link_picker = {

		init: function() {
			
			// Open wpLink modal
			$('body').on('click', '.pods-field .podsLinkPopup', function (e) {
				pods_active_link_picker = $(this).parents('.pods-link-options');
				$('body').addClass('modal-open-pods-field-link');

				var url_elem = pods_active_link_picker.find('.linkPickerUrl');
				var text_elem = pods_active_link_picker.find('.linkPickerText');
				var target_elem = pods_active_link_picker.find('.linkPickerTarget');

		        // save any existing default initialization
		        wplink_defaults = wpLink.setDefaultValues;

				wpLink.setDefaultValues = function() {
					$('#wp-link-wrap #wp-link-url').val( url_elem.val() );
					$('#wp-link-wrap #wp-link-text').val( text_elem.val() );
					if ( target_elem.is(':checked') ) {
						$('#wp-link-wrap #wp-link-target').prop('checked', true);
					} else {
						$('#wp-link-wrap #wp-link-target').prop('checked', false);
					}
				};

				// Open modal in the dummy textarea
				wpLink.open( 'pods-link-editor-hidden' );

				return false;
			});
			
			// Save changes in the selected field
			$('body').on('click', '#wp-link-submit', function(event) {
				if ($('body').hasClass('modal-open-pods-field-link')) {
					//get the href attribute and add to a textfield, or use as you see fit
					//the links attributes (href, target) are stored in an object, which can be access via  wpLink.getAttrs()
					var linkAtts = wpLink.getAttrs();
					if (linkAtts.href != '') {
						pods_active_link_picker.find('.linkPickerUrl').val(linkAtts.href);
					}
					//get the target attribute
					if (linkAtts.target == '_blank') {
						pods_active_link_picker.find('.linkPickerTarget').prop('checked', true);
					} else {
						pods_active_link_picker.find('.linkPickerTarget').prop('checked', false);
					}
					//get the text attribute
					var linkText = $('#wp-link-wrap #wp-link-text').val();
					if (linkText != '') {
						pods_active_link_picker.find('.linkPickerText').val(linkText);
					}
				}
				$('body').removeClass('modal-open-pods-field-link');
				pods_link_picker.reset_wplink();
				//trap any events
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();

				return false;
			});
			
			// Close modal without any changes
			$('body').on('click', '#wp-link-cancel, #wp-link-close', function(event) {

				pods_link_picker.reset_wplink();
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();

				return false;
			});	

		},
	    reset_wplink: function() {
	    	$('body').removeClass('modal-open-pods-field-link');
	        wpLink.textarea = $('body'); // to close the link dialogue, it is again expecting an wp_editor instance, so you need to give it something to set focus back to. In this case, I'm using body, but the textfield with the URL would be fine
	        wpLink.close();// close the dialogue

	        // restore wplink default initialization
	        wpLink.setDefaultValues = wplink_defaults;
	    }
	};

	// Validate that we have the right resourses
	if ( typeof wpLink != 'undefined' && $('#wp-link-wrap').length ) {
		pods_link_picker.init();
	} else {
		$('.pods-field .podsLinkPopup').hide();
	}
});

