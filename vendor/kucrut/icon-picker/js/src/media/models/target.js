/**
 * wp.media.model.IconPickerTarget
 *
 * A target where the picked icon should be sent to
 *
 * @augments Backbone.Model
 */
var IconPickerTarget = Backbone.Model.extend({
	defaults: {
		type:  '',
		group: 'all',
		icon:  '',
		url:   '',
		sizes: []
	}
});

module.exports = IconPickerTarget;
