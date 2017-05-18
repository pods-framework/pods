/*global jQuery, _, Backbone, Marionette */
import {PodsDFVInit} from 'pods-dfv/_src/pods-dfv-init'; // jQuery plugin

import * as fields from 'pods-dfv/_src/field-manifest';
import * as models from 'pods-dfv/_src/model-manifest';

const PodsDFV = {
	fields        : fields,
	models        : models,
	fieldInstances: {}
};
export default PodsDFV;

/**
 * This is the workhorse that currently kicks everything off
 */
jQuery.fn.PodsDFVInit = PodsDFVInit;
jQuery( function () {
	jQuery( '.pods-form-ui-field' ).PodsDFVInit( PodsDFV.fieldInstances );
} );