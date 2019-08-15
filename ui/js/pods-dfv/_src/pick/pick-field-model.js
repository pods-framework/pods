/*global jQuery, _, Backbone, PodsMn */

export const PickFieldModel = Backbone.Model.extend( {
	defaults: {
		'view_name': 'select',
		'iframe_src': '',
		'pick_format_type': 'single',
		'pick_show_icon': false,
		'pick_show_view_link': false,
		'pick_show_edit_link': false
	}
} );
