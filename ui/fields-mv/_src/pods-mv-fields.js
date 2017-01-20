/*global jQuery, _, Backbone, Marionette */
import {podsMVFieldsInit} from '~/ui/fields-mv/_src/pods-mv-fields-init'; // jQuery plugin

import * as fields from '~/ui/fields-mv/_src/field-manifest';
import * as models from '~/ui/fields-mv/_src/model-manifest';

const PodsMVFields = {
	fields        : fields,
	models        : models,
	fieldInstances: {}
};
export default PodsMVFields;

/**
 * This is the workhorse that currently kicks everything off
 */
jQuery.fn.podsMVFieldsInit = podsMVFieldsInit;
jQuery( function () {
	jQuery( '.pods-form-ui-field' ).podsMVFieldsInit( PodsMVFields.fieldInstances );
} );