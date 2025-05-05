/**
 * Internal dependencies
 */
import prepareApiFetchData from '../prepareApiFetchData';

describe( 'prepareApiFetchData', () => {
	test( 'returns non-object data as-is', () => {
		expect( prepareApiFetchData( 'string value' ) ).toEqual( 'string value' );
		expect( prepareApiFetchData( 123 ) ).toEqual( 123 );
		expect( prepareApiFetchData( null ) ).toEqual( null );
	} );

	test( 'converts boolean values to 1/0 in the main object', () => {
		const data = {
			name: 'Test Name',
			active: true,
			published: false,
			count: 42,
		};

		const result = prepareApiFetchData( data );

		expect( result ).toEqual( {
			name: 'Test Name',
			active: 1,
			published: 0,
			count: 42,
		} );
	} );

	test( 'converts undefined values to empty strings in the main object', () => {
		const data = {
			name: 'Test Name',
			description: undefined,
			count: 42,
		};

		const result = prepareApiFetchData( data );

		expect( result ).toEqual( {
			name: 'Test Name',
			description: '',
			count: 42,
		} );
	} );

	test( 'converts boolean values to 1/0 in the args object', () => {
		const data = {
			name: 'Test Name',
			args: {
				required: true,
				disabled: false,
				max_length: 100,
			},
		};

		const result = prepareApiFetchData( data );

		expect( result ).toEqual( {
			name: 'Test Name',
			args: {
				required: 1,
				disabled: 0,
				max_length: 100,
			},
		} );
	} );

	test( 'converts undefined values to empty strings in the args object', () => {
		const data = {
			name: 'Test Name',
			args: {
				placeholder: undefined,
				default: 'Default value',
			},
		};

		const result = prepareApiFetchData( data );

		expect( result ).toEqual( {
			name: 'Test Name',
			args: {
				placeholder: '',
				default: 'Default value',
			},
		} );
	} );

	test( 'handles nested data structures correctly', () => {
		const data = {
			name: 'Test Name',
			active: true,
			args: {
				required: true,
				default_value: undefined,
				options: {
					visible: false,
					advanced: undefined,
				},
			},
		};

		const result = prepareApiFetchData( data );

		// Only processes boolean values in the main object and args object
		expect( result ).toEqual( {
			name: 'Test Name',
			active: 1,
			args: {
				required: 1,
				default_value: '',
				options: {
					// Not processed since it's nested beyond args
					visible: false,
					advanced: undefined,
				},
			},
		} );
	} );

	test( 'handles empty args object', () => {
		const data = {
			name: 'Test Name',
			args: {},
		};

		const result = prepareApiFetchData( data );

		expect( result ).toEqual( {
			name: 'Test Name',
			args: {},
		} );
	} );

	test( 'preserves numeric values', () => {
		const data = {
			integer: 42,
			float: 3.14,
			zero: 0,
			stringNumber: '123',
			args: {
				min: 0,
				max: 100,
			},
		};

		const result = prepareApiFetchData( data );

		expect( result ).toEqual( {
			integer: 42,
			float: 3.14,
			zero: 0,
			stringNumber: '123',
			args: {
				min: 0,
				max: 100,
			},
		} );
	} );
} );
