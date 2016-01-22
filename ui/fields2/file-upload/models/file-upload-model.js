/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadModel = Backbone.Model.extend( {
		defaults: {
			'id'  : 0,
			'name': 'undefined',
			'icon': '',
			'link': false
		}
	} );

}( jQuery, pods_ui ) );