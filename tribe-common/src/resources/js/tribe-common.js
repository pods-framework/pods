// Run some magic to allow a better handling of class names for jQuery.hasClass type of methods
String.prototype.className = function () {
	// Prevent Non Strings to be included
	if (
		(
			'string' !== typeof this
			&& ! this instanceof String
		)
		|| 'function' !== typeof this.replace
	) {
		return this;
	}

	return this.replace( '.', '' );
};

// Add a method to convert ID/Classes into JS easy/safe variable
String.prototype.varName = function () {
	// Prevent Non Strings to be included
	if (
		(
			'string' !== typeof this
			&& ! this instanceof String
		)
		|| 'function' !== typeof this.replace
	) {
		return this;
	}

	return this.replace( '-', '_' );
};

/**
 * Creates a global Tribe Variable where we should start to store all the things
 * @type {object}
 */
var tribe = tribe || {};