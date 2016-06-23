// JavaScript Document

jQuery(document).ready(function($){
	
	// Variable to store the active link picker field
	var activeLinkPicker;
	
	// Open wpLink modal
	$('body').on('click', '.pods-field .podsLinkPopup', function (e) {
		activeLinkPicker = $(this).parents('.link-options');
		$('body').addClass('modal-open-pods-field-link');
		wpActiveEditor = true;
		wpLink.open('podsLinkPopupDummyTextarea'); // Open modal in the dummy textarea
		$('#wp-link #wp-link-url').val(activeLinkPicker.find('.linkPickerUrl').val());
		$('#wp-link #wp-link-text').val(activeLinkPicker.find('.linkPickerText').val());
		if (activeLinkPicker.find('.linkPickerTarget').is(':checked')) {
			$('#wp-link #wp-link-target').prop('checked', true);
		}
		return false;
	});
	
	// Save changes in the selected field
	$('body').on('click', '#wp-link-submit', function(event) {
		if ($('body').hasClass('modal-open-pods-field-link')) {
			//get the href attribute and add to a textfield, or use as you see fit
			//the links attributes (href, target) are stored in an object, which can be access via  wpLink.getAttrs()
			var linkAtts = wpLink.getAttrs();
			if (linkAtts.href != '') {
				activeLinkPicker.find('.linkPickerUrl').val(linkAtts.href);
			}
			//get the target attribute
			if (linkAtts.target == '_blank') {
				activeLinkPicker.find('.linkPickerTarget').prop('checked', true);
			} else {
				activeLinkPicker.find('.linkPickerTarget').prop('checked', false);
			}
			//get the text attribute
			var linkText = $('#wp-link #wp-link-text').val();
			if (linkText != '') {
				activeLinkPicker.find('.linkPickerText').val(linkText);
			}
		}
		$('body').removeClass('modal-open-pods-field-link');
		wpLink.textarea = $('body'); //to close the link dialogue, it is again expecting an wp_editor instance, so you need to give it something to set focus back to. In this case, I'm using body, but the textfield with the URL would be fine
		wpLink.close();//close the dialogue
		//trap any events
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		event.stopPropagation();
		return false;
	});
	
	// Close modal without any changes
	$('body').on('click', '#wp-link-cancel, #wp-link-close', function(event) {
		$('body').removeClass('modal-open-pods-field-link');
		wpLink.textarea = $('body');
		wpLink.close();
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		event.stopPropagation();
		return false;
	});	
	
});

