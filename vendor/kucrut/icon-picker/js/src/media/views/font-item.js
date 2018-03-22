var Attachment = wp.media.view.Attachment.Library,
	IconPickerFontItem;

/**
 * wp.media.view.IconPickerFontItem
 */
IconPickerFontItem = Attachment.extend({
	className: 'attachment iconpicker-item',

	initialize: function() {
		this.template = wp.media.template( 'iconpicker-' + this.options.baseType + '-item' );
		Attachment.prototype.initialize.apply( this, arguments );
	},

	render: function() {
		var options = _.defaults( this.model.toJSON(), {
			baseType: this.options.baseType,
			type:     this.options.type
		});

		this.views.detach();
		this.$el.html( this.template( options ) );
		this.updateSelect();
		this.views.render();

		return this;
	}
});

module.exports = IconPickerFontItem;
