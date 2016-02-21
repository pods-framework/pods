/*global jQuery, _, Backbone, Mn */
import { podsFieldsInit } from './pods-fields-init';

// @todo: just here for testing the file upload queue
import * as Queue from './file-upload/views/file-upload-queue';
import * as fieldClasses from './pods-ui-field-manifest';

import { PodsFieldModel } from './core/pods-field-model';
import { FileUploadCollection } from './file-upload/models/file-upload-model';
import { RelationshipCollection } from './pick/models/relationship-model';

const app = {
	fieldClasses: fieldClasses,
	fields      : {},
	Queue       : Queue
};
export default app;

/**
 * This is the workhorse that currently kicks everything off
 */
jQuery.fn.podsFieldsInit = podsFieldsInit;
jQuery( function () {
	jQuery( '.pods-form-ui-field' ).podsFieldsInit( app.fields );
} );