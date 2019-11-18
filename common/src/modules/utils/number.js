/**
 * Calculate the percentage of two numbers
 *
 * @param {number} value Initial value from where to take the percentage
 * @param {number} total Total value to get the percentage relative to this value
 * @returns {number} total percentage value
 */
export const percentage = ( value = 0, total = 0 ) => {
	if ( total === 0 ) {
		return 0;
	}

	const result = Number.parseFloat( ( value / total ) * 100 );

	if ( isNaN( result ) ) {
		throw new RangeError(
			`Make sure ${value} and ${total} are valid numbers, operation result in NaN value`
		);
	}

	return result;
};
