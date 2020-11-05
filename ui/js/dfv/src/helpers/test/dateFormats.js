import {
	convertPHPDateFormatToMomentFormat,
	convertjQueryUIDateFormatToMomentFormat,
	convertjQueryUITimeFormatToMomentFormat,
	convertPodsDateFormatToMomentFormat,
} from '../dateFormats';

describe( 'convertPHPDateFormatToMomentFormat', () => {
	it( 'converts date and time formats', () => {
		expect( convertPHPDateFormatToMomentFormat( 'm/d/Y' ) ).toEqual( 'MM/DD/YYYY' );

		expect( convertPHPDateFormatToMomentFormat( 'm-d-Y' ) ).toEqual( 'MM-DD-YYYY' );

		expect( convertPHPDateFormatToMomentFormat( 'm.d.Y' ) ).toEqual( 'MM.DD.YYYY' );

		expect( convertPHPDateFormatToMomentFormat( 'Y/m/d' ) ).toEqual( 'YYYY/MM/DD' );

		expect( convertPHPDateFormatToMomentFormat( 'Y-m-d' ) ).toEqual( 'YYYY-MM-DD' );

		expect( convertPHPDateFormatToMomentFormat( 'Y.m.d' ) ).toEqual( 'YYYY.MM.DD' );

		expect( convertPHPDateFormatToMomentFormat( 'F j, Y' ) ).toEqual( 'MMMM D, YYYY' );

		expect( convertPHPDateFormatToMomentFormat( 'F jS, Y' ) ).toEqual( 'MMMM Do, YYYY' );

		expect( convertPHPDateFormatToMomentFormat( 'c' ) ).toEqual( 'YYYY-MM-DD[T]HH:mm:ssZ' );

		expect( convertPHPDateFormatToMomentFormat( 'd-m-Y' ) ).toEqual( 'DD-MM-YYYY' );
	} );

	it( 'handles invalid formats', () => {
		expect( convertPHPDateFormatToMomentFormat( null ) ).toEqual( '' );

		expect( convertPHPDateFormatToMomentFormat( '' ) ).toEqual( '' );
	} );
} );

describe( 'convertjQueryUIDateFormatToMomentFormat', () => {
	it( 'converts date and time formats', () => {
		expect( convertjQueryUIDateFormatToMomentFormat( 'mm/dd/yy' ) ).toEqual( 'MM/DD/YYYY' );

		expect( convertjQueryUIDateFormatToMomentFormat( 'mm-dd-yy' ) ).toEqual( 'MM-DD-YYYY' );

		expect( convertjQueryUIDateFormatToMomentFormat( 'mm.dd.yy' ) ).toEqual( 'MM.DD.YYYY' );

		expect( convertjQueryUIDateFormatToMomentFormat( 'yy/mm/dd' ) ).toEqual( 'YYYY/MM/DD' );

		expect( convertjQueryUIDateFormatToMomentFormat( 'yy-mm-dd' ) ).toEqual( 'YYYY-MM-DD' );

		expect( convertjQueryUIDateFormatToMomentFormat( 'yy.mm.dd' ) ).toEqual( 'YYYY.MM.DD' );

		expect( convertjQueryUIDateFormatToMomentFormat( 'MM d, yy' ) ).toEqual( 'MMMM D, YYYY' );
	} );

	it( 'handles invalid formats', () => {
		expect( convertjQueryUIDateFormatToMomentFormat( null ) ).toEqual( '' );

		expect( convertjQueryUIDateFormatToMomentFormat( '' ) ).toEqual( '' );
	} );
} );

describe( 'convertjQueryUITimeFormatToMomentFormat', () => {
	it( 'converts date and time formats', () => {
		expect( convertjQueryUITimeFormatToMomentFormat( 'hh:mm tt' ) ).toEqual( 'hh:MM tt' );
	} );

	it( 'handles invalid formats', () => {
		expect( convertjQueryUITimeFormatToMomentFormat( null ) ).toEqual( '' );

		expect( convertjQueryUITimeFormatToMomentFormat( '' ) ).toEqual( '' );
	} );
} );

describe( 'convertPodsDateFormatToMomentFormat', () => {
	it( 'converts the predetermined formats', () => {
		expect( convertPodsDateFormatToMomentFormat( 'mdy' ) ).toEqual( 'MM/DD/YYYY' );

		expect( convertPodsDateFormatToMomentFormat( 'mdy_dash' ) ).toEqual( 'MM-DD-YYYY' );

		expect( convertPodsDateFormatToMomentFormat( 'mdy_dot' ) ).toEqual( 'MM.DD.YYYY' );

		expect( convertPodsDateFormatToMomentFormat( 'ymd_slash' ) ).toEqual( 'YYYY/MM/DD' );

		expect( convertPodsDateFormatToMomentFormat( 'ymd_dash' ) ).toEqual( 'YYYY-MM-DD' );

		expect( convertPodsDateFormatToMomentFormat( 'ymd_dot' ) ).toEqual( 'YYYY.MM.DD' );

		expect( convertPodsDateFormatToMomentFormat( 'fjy' ) ).toEqual( 'MMMM D, YYYY' );

		expect( convertPodsDateFormatToMomentFormat( 'fjsy' ) ).toEqual( 'MMMM Do, YYYY' );

		expect( convertPodsDateFormatToMomentFormat( 'c' ) ).toEqual( 'YYYY-MM-DD[T]HH:mm:ssZ' );

		expect( convertPodsDateFormatToMomentFormat( 'h_mm_A' ) ).toEqual( 'h:mm A' );

		expect( convertPodsDateFormatToMomentFormat( 'h_mm_ss_A' ) ).toEqual( 'h:mm:ss A' );

		expect( convertPodsDateFormatToMomentFormat( 'hh_mm_A' ) ).toEqual( 'hh:mm A' );

		expect( convertPodsDateFormatToMomentFormat( 'hh_mm_ss_A' ) ).toEqual( 'hh:mm:ss A' );

		expect( convertPodsDateFormatToMomentFormat( 'h_mma' ) ).toEqual( 'h:mma' );

		expect( convertPodsDateFormatToMomentFormat( 'hh_mma' ) ).toEqual( 'hh:mma' );

		expect( convertPodsDateFormatToMomentFormat( 'h_mm' ) ).toEqual( 'h:mm' );

		expect( convertPodsDateFormatToMomentFormat( 'h_mm_ss' ) ).toEqual( 'h:mm:ss' );

		expect( convertPodsDateFormatToMomentFormat( 'hh_mm' ) ).toEqual( 'hh:mm' );

		expect( convertPodsDateFormatToMomentFormat( 'hh_mm_ss' ) ).toEqual( 'hh:mm:ss' );

		expect( convertPodsDateFormatToMomentFormat( 'hh_mm', true ) ).toEqual( 'HH:mm' );

		expect( convertPodsDateFormatToMomentFormat( 'hh_mm_ss', true ) ).toEqual( 'HH:mm:ss' );
	} );

	it( 'handles invalid formats', () => {
		expect( convertPodsDateFormatToMomentFormat( null ) ).toEqual( '' );

		expect( convertPodsDateFormatToMomentFormat( '' ) ).toEqual( '' );

		expect( convertPodsDateFormatToMomentFormat( 'invalid' ) ).toEqual( '' );
	} );
} );
