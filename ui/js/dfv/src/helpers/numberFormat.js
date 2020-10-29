const splitThousands = ( number ) => ( decimalPoint, thousandsPoint ) => {
	const splitNum = number.toString().split( decimalPoint );
	splitNum[ 0 ] = splitNum[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, thousandsPoint );
	return splitNum.join( decimalPoint );
};

const isBigNumber = ( number ) => number.toString().includes( 'e' );

const isBigFloat = ( number ) => number.toString().includes( '-' );

const calcTrailing = ( dec, len ) => Number( dec ) + 2 - len;

const handleBigFloats = ( number, decimals ) => {
	if ( ! decimals ) {
		return '0';
	}

	const [ numbers, dec ] = number.toString().replace( '.', '' ).split( 'e-' );
	const trailingZeros = calcTrailing( dec, numbers.length );
	const res = `${ '0.'.padEnd( trailingZeros + 2, '0' ) }${ numbers }`;

	return decimals ? res.substring( 0, 2 ) + res.substring( 2, decimals + 2 ) : res;
};

const handleBigNumbers = ( number, decimals, decimalPoint, thousandsPoint ) => {
	if ( isBigFloat( number ) ) {
		return handleBigFloats( number, decimals );
	}

	// eslint-disable-next-line no-undef
	return splitThousands( BigInt( number ) )( decimalPoint, thousandsPoint );
};

function handleFiniteNumbers( number, decimals, decimalPoint, thousandsPoint ) {
	if ( ! isFinite( number ) ) {
		throw new TypeError( 'number is not finite number' );
	}

	if ( 'auto' === decimals ) {
		const len = number.toString().split( '.' ).length;
		decimals = len > 1 ? len : 0;
	}

	return splitThousands(
		parseFloat( number ).toFixed( decimals ).replace( '.', decimalPoint )
	)( decimalPoint, thousandsPoint );
}

// Equivalent to php's number_format, borrowed from
// https://gist.github.com/VassilisPallas/d73632e9de4794b7dd10b7408f7948e8
const numberFormat = (
	number,
	decimals = 0,
	decimalPoint = '.',
	thousandsPoint = ','
) => {
	if ( number === null || typeof number !== 'number' ) {
		throw new TypeError( 'number is not valid' );
	}

	if ( isBigNumber( number ) ) {
		return handleBigNumbers( number, decimals, decimalPoint, thousandsPoint );
	}

	return handleFiniteNumbers( number, decimals, decimalPoint, thousandsPoint );
};

export default numberFormat;
