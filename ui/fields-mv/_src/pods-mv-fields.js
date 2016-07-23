/*global jQuery, _, Backbone, Marionette */
import { podsMVFieldsInit } from '~/ui/fields-mv/_src/pods-mv-fields-init'; // jQuery plugin
import * as fieldClasses from '~/ui/fields-mv/_src/pods-mv-fields-manifest'; // All fields

const PodsMVFields = {
	fieldClasses: fieldClasses,
	fields      : {}
};
export default PodsMVFields;

/**
 * This is the workhorse that currently kicks everything off
 */
jQuery.fn.podsMVFieldsInit = podsMVFieldsInit;
jQuery( function () {
	jQuery( '.pods-form-ui-field' ).podsMVFieldsInit( PodsMVFields.fields );
} );