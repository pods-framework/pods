/*global jQuery, _, Backbone, Mn */
import { podsFieldsInit } from './pods-fields-init'; // jQuery plugin
import * as fieldClasses from './pods-fields-manifest'; // All fields
import * as Queue from './file-upload/views/file-upload-queue'; // @todo: just here for testing the file upload queue

const PodsUI = {
	fieldClasses: fieldClasses,
	fields      : {},
	Queue       : Queue
};
export default PodsUI;

/**
 * This is the workhorse that currently kicks everything off
 */
jQuery.fn.podsFieldsInit = podsFieldsInit;
jQuery( function () {
	jQuery( '.pods-form-ui-field' ).podsFieldsInit( PodsUI.fields );
} );