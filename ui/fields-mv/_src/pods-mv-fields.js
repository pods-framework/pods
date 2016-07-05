/*global jQuery, _, Backbone, Mn */
import { podsFieldsInit } from './pods-mv-fields-init'; // jQuery plugin
import * as fieldClasses from './pods-mv-fields-manifest'; // All fields

const PodsMVFields = {
	fieldClasses: fieldClasses,
	fields      : {}
};
export default PodsMVFields;

/**
 * This is the workhorse that currently kicks everything off
 */
jQuery.fn.podsFieldsInit = podsFieldsInit;
jQuery( function () {
	jQuery( '.pods-form-ui-field' ).podsFieldsInit( PodsMVFields.fields );
} );