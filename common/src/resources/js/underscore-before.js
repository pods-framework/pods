(function(){

	/**
	 * Function that search if an object has all the specified methods and all those methods are functions.
	 *
	 * @since 4.7.7
	 *
	 * @param {Object} obj The object where all the methods might be stored
	 * @param {Array} methods An array with the name of all the methods to be tested
	 * @return {boolean} True if has all the methods false otherwise.
	 */
	function search_for_methods( obj, methods ) {

		// Object is not defined or does not exist on window nothing to do.
		if ( ! obj || window[ obj ] ) {
			return false;
		}

		var search = methods.filter( function( name ) {
			// Test if the method is part of Obj and if is a function.
			return obj[ name ] && 'function' === typeof obj[ name ];
		});
		return methods.length === search.length;
	}

	/**
	 * Function to compare if the variable _ is from lodash by testing some of us unique methods.
	 *
	 * @since 4.7.7
	 *
	 * @return {boolean} True if the global _ is from lodash.
	 */
	function is_lodash() {
		return search_for_methods( window._, [ 'get', 'set', 'at', 'cloneDeep', 'some', 'every' ] );
	}

	window._lodash_tmp = false;
	// If current _ is from lodash Store it in a temp variable before underscore is loaded
	if ( '_' in window && is_lodash() ) {
		window._lodash_tmp = _;
	}
})();
