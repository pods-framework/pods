/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import * as proptypes from '@moderntribe/common/utils/proptypes';

describe( 'Tests for proptypes utils', () => {
	describe( 'createChainableValidator', () => {
		it( 'should not return an error when prop is undefined and is not required', () => {
			const props = {};
			const chainedValidator = jest.fn( proptypes.createChainableValidator( noop ) );
			chainedValidator( props, 'time', 'component' );

			expect( chainedValidator ).toHaveReturned();
			expect( chainedValidator ).toHaveReturnedWith( null );
		} );

		it( 'should return an error when prop is undefined and is required', () => {
			const props = {};
			const chainedValidator = jest.fn( proptypes.createChainableValidator( noop ).isRequired );
			chainedValidator( props, 'time', 'component' );

			expect( chainedValidator ).toHaveReturned();
			expect( chainedValidator ).toHaveReturnedWith( Error( 'The prop `time` is marked as required in `component`, but its value is `undefined`.' ) );
		} );

		it( 'should return an error when prop is null and is required', () => {
			const props = {
				time: null,
			};
			const chainedValidator = jest.fn( proptypes.createChainableValidator( noop ).isRequired );
			chainedValidator( props, 'time', 'component' );

			expect( chainedValidator ).toHaveReturned();
			expect( chainedValidator ).toHaveReturnedWith( Error( 'The prop `time` is marked as required in `component`, but its value is `null`.' ) );
		} );

		it( 'should call the validator when prop is provided, not undefined or null, and is not required', () => {
			const props = {
				time: '15:34',
			};
			const validator = jest.fn( noop );
			const chainedValidator = jest.fn( proptypes.createChainableValidator( validator ) );
			chainedValidator( props, 'time', 'component' );

			expect( validator ).toHaveBeenCalled();
			expect( validator ).toHaveBeenCalledTimes( 1 );
		} );

		it( 'should call the validator when prop is provided, not undefined or null, and is required', () => {
			const props = {
				time: '15:34',
			};
			const validator = jest.fn( noop );
			const chainedValidator = jest.fn( proptypes.createChainableValidator( validator ).isRequired );
			chainedValidator( props, 'time', 'component' );

			expect( validator ).toHaveBeenCalled();
			expect( validator ).toHaveBeenCalledTimes( 1 );
		} );
	} );

	describe( 'timeRegex', () => {
		it( 'should return true when provided proper time formatted string', () => {
			expect( proptypes.timeRegex.test( '00:00' ) ).toEqual( true );
			expect( proptypes.timeRegex.test( '23:59' ) ).toEqual( true );
			expect( proptypes.timeRegex.test( '12:42' ) ).toEqual( true );
			expect( proptypes.timeRegex.test( '03:01' ) ).toEqual( true );
			expect( proptypes.timeRegex.test( '19:47' ) ).toEqual( true );
			expect( proptypes.timeRegex.test( '05:56' ) ).toEqual( true );
			expect( proptypes.timeRegex.test( '14:11' ) ).toEqual( true );
		} );

		it( 'should return false when not provided proper time formatted string', () => {
			expect( proptypes.timeRegex.test( 'random string' ) ).toEqual( false );
			expect( proptypes.timeRegex.test( '-00:00' ) ).toEqual( false );
			expect( proptypes.timeRegex.test( '24:00' ) ).toEqual( false );
			expect( proptypes.timeRegex.test( '00:60' ) ).toEqual( false );
			expect( proptypes.timeRegex.test( '24:60' ) ).toEqual( false );
			expect( proptypes.timeRegex.test( '75:93' ) ).toEqual( false );
			expect( proptypes.timeRegex.test( '90:90' ) ).toEqual( false );
		} );
	} );

	describe( 'timeFormat', () => {
		it( 'should not return an error when provided proper time formatted string', () => {
			const props = {
				time: '15:34',
			};
			const timeFormat = jest.fn( () => proptypes.timeFormat( props, 'time', 'component' ) );
			timeFormat();

			expect( timeFormat ).toHaveReturned();
			expect( timeFormat ).toHaveReturnedWith( null );
		} );

		it( 'should return an error when not provided a string', () => {
			const props = {
				time: true,
			};
			const timeFormat = jest.fn( () => proptypes.timeFormat( props, 'time', 'component' ) );
			timeFormat();

			expect( timeFormat ).toHaveReturned();
			expect( timeFormat ).toHaveReturnedWith( Error( 'Invalid prop `time` of type `boolean` supplied to `component`, expected `string`.' ) );
		} );

		it( 'should return an error when not provided proper time format', () => {
			const props = {
				time: 'random string',
			};
			const timeFormat = jest.fn( () => proptypes.timeFormat( props, 'time', 'component' ) );
			timeFormat();

			expect( timeFormat ).toHaveReturned();
			expect( timeFormat ).toHaveReturnedWith( Error( 'Invalid prop `time` format supplied to `component`, expected `hh:mm`.' ) );
		} );
	} );

	describe( 'nullType', () => {
		test( 'valid prop types', () => {
			const props = {
				name: null,
			}
			const format = jest.fn( () => proptypes.nullType( props, 'name', 'Test Type' ) );
			format();
			expect( format ).toHaveReturned();
			expect( format ).toHaveReturnedWith( undefined );
		} );

		test( 'invalid prop types', () => {
			const props = {
				name: 'Modern Tribe',
			}
			const format = jest.fn( () => proptypes.nullType( props, 'name', 'Test Type' ) );
			format();
			expect( format ).toHaveReturned();
			expect( format ).toHaveReturnedWith( Error( 'Invalid prop: `name` supplied to `Test Type`, expect null.') );
		} );
	} );
} );
