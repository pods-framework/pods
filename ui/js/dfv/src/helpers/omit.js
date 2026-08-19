/**
 * Returns a copy of an object without the given keys.
 *
 * @param {Object}   obj  Source object.
 * @param {string[]} keys Keys to omit.
 * @return {Object} New object without the omitted keys.
 */
const omit = ( obj, keys ) => {
	return Object.fromEntries(
		Object.entries( obj ).filter( ( [ key ] ) => ! keys.includes( key ) )
	);
};

export default omit;
