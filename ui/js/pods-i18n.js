
PodsI18n = {
	__: function( str ) {
		return this.translate_string( str );
	},
	_e: function( str ) {
		print( this.translate_string( str ) );
	},
	translate_string: function( str ) {
		if ( typeof pods_localized_strings != 'undefined' ) {

			/**
			 * Converts string into reference object variable
			 * Uses the same logic as PHP to create the same references
			 * 
			 * 1. Remove capitals
			 * 2. Remove all punctuation etc.
			 * 3. Trim
			 * 4. Convert whitespaces to underscores
			 */
			var ref = '__' + str.toLowerCase().replace(/[^a-z ]+/g, ' ').trim().replace(/\s{2,}/g, "_").replace(/ /g,'_');
			
			if ( typeof pods_localized_strings[ ref ] != 'undefined' ) {
				return pods_localized_strings[ ref ];
			} else if ( pods_localized_strings.debug == true ) {
				console.log( 'Pods__: String not found "' + str + '" (reference used: "' + ref + '")');
			}
		}
		return str;
	}
}
