/**
 * Internal dependencies
 */
import { sendValue } from '@moderntribe/common/utils/input';

describe( 'Tests for input.js', () => {
	const event = {
		target: {
			value: 'Sample',
		},
	};

	test( 'Callback being executed', () => {
		const mockCallback = jest.fn();
		sendValue( mockCallback )( event );
		expect( mockCallback ).toHaveBeenCalled();
		expect( mockCallback ).toHaveBeenCalledTimes( 1 );
		expect( mockCallback ).toHaveBeenCalledWith( 'Sample' );
	} );
} );
