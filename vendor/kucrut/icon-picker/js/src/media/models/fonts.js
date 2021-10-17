/**
 * wp.media.model.IconPickerFonts
 */
var IconPickerFonts = Backbone.Collection.extend({
	constructor: function() {
		Backbone.Collection.prototype.constructor.apply( this, arguments );

		this.items = new Backbone.Collection( this.models );
		this.props = new Backbone.Model({
			group:  'all',
			search: ''
		});

		this.props.on( 'change', this.refresh, this );
	},

	/**
	 * Refresh library when props is changed
	 *
	 * @param {Backbone.Model} props
	 */
	refresh: function( props ) {
		let items = _.clone( this.items.models );

		_.each( props.toJSON(), ( value, filter ) => {
			const method = this.filters[ filter ];

			if ( method ) {
				items = items.filter( item => {
					return method( item, value );
				});
			}
		});

		this.reset( items );
	},

	filters: {
		/**
		 * @static
		 *
		 * @param {Backbone.Model} item  Item model.
		 * @param {string}         group Group ID.
		 *
		 * @returns {Boolean}
		 */
		group: function( item, group ) {
			return (  group === 'all' || item.get( 'group' ) === group || item.get( 'group' ) === '' );
		},

		/**
		 * @static
		 *
		 * @param {Backbone.Model} item Item model.
		 * @param {string}         term Search term.
		 *
		 * @returns {Boolean}
		 */
		search: function( item, term ) {
			let result;

			if ( term === '' ) {
				result = true;
			} else {
				result = _.any([ 'id', 'name' ], attribute => {
					const value = item.get( attribute );

					return value && value.search( term ) >= 0;
				}, term );
			}

			return result;
		}
	}
});

module.exports = IconPickerFonts;
