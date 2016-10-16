/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/fields-mv/_src/pick/views/select2-view.html';
import {PodsFieldListView, PodsFieldView} from '~/ui/fields-mv/_src/core/pods-field-views';

/**
 *
 */
export const Select2View = PodsFieldView.extend( {
	template: _.template( template ),
} );
