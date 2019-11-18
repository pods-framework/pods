/**
 * Internal dependencies
 */
import { percentage } from '@moderntribe/common/utils/number';

describe( 'percentage', () => {
	test( 'with default values', () => {
		expect( percentage() ).toBe( 0 );
	} );

	test( 'with non numbers', () => {
		expect( () => percentage( 'modern', 'tribe' ) ).toThrow();
	} );

	test( 'limits outside 100 percent', () => {
		expect( percentage( 120, 100 ) ).toBe( 120 );
		expect( percentage( 1220, 100 ) ).toBe( 1220 );
		expect( percentage( 101, 100 ) ).toBe( 101 );
		expect( percentage( 100, 100 ) ).toBe( 100 );
	} );

	test( 'common cases', () => {
		expect( percentage( 10, 100 ) ).toBe( 10 );
		expect( percentage( 17, 100 ) ).toBe( 17 );
		expect( percentage( 20, 1000 ) ).toBe( 2 );
		expect( percentage( 155, 1000 ) ).toBe( 15.5 );
		expect( percentage( 999, 1000 ) ).toBe( 99.9 );
		expect( percentage( 1000, 1000 ) ).toBe( 100 );
	} );

	test( 'negative percentages', () => {
		expect( percentage( -10, 100 ) ).toBe( -10 );
		expect( percentage( -80, 100 ) ).toBe( -80 );
		expect( percentage( -200, 100 ) ).toBe( -200 );
	} );
} );
