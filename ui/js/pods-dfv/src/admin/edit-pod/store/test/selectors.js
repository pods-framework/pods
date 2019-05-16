import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';
import {
	getState,
	getOption,
	getOptionItemValue,
	getOptionValue,
	getPodName,
	getPodMetaValue,
	getFields,
} from '../selectors';

describe( 'selectors', () => {

	describe( 'getState()', () => {
		it( 'Should return the full state', () => {
			const state = deepFreeze( {
				foo: {
					'xyzzy': 42,
					'plugh': false
				},
				bar: {
					name: 'bob',
					relationship: 'your uncle'
				},
				baz: [ 0, 1, 2 ]
			} );
			const result = getState( state );

			expect( result ).toBeDefined();
			expect( result ).toEqual( state );
		} );
	} );

	describe( 'options', () => {
		describe( 'getOption()', () => {
			const options = {
				opt1: { name: 'opt1', label: 'Option 1', value: 'val1' },
				opt2: { name: 'opt2', label: 'Option 2', value: 'val2' },
				opt3: { name: 'opt3', label: 'Option 3', value: 'val3' },
			};

			it( 'Should return the specified option', () => {
				const state = deepFreeze(
					paths.OPTIONS.createTree( options )
				);
				const result = getOption( state, 'opt2' );
				const expected = options.opt2;

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getOptionItemValue()', () => {
			const options = {
				opt1: { name: 'opt1', label: 'Option 1', value: 'val1' },
				opt2: { name: 'opt2', label: 'Option 2', value: 'val2' },
				opt3: { name: 'opt3', label: 'Option 3', value: 'val3' },
			};

			it( 'Should return the specified option item value', () => {
				const state = deepFreeze(
					paths.OPTIONS.createTree( options )
				);
				const result = getOptionItemValue( state, 'opt2', 'label' );
				const expected = options.opt2.label;

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getOptionValue()', () => {
			const options = {
				opt1: { name: 'opt1', label: 'Option 1', value: 'val1' },
				opt2: { name: 'opt2', label: 'Option 2', value: 'val2' },
				opt3: { name: 'opt3', label: 'Option 3', value: 'val3' },
			};

			it( 'Should return the specified option value', () => {
				const state = deepFreeze(
					paths.OPTIONS.createTree( options )
				);
				const result = getOptionValue( state, 'opt2' );
				const expected = options.opt2.value;

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );

	describe( 'podMeta', () => {
		describe( 'getPodName()', () => {
			it( 'Should return the Pod name', () => {
				const state = deepFreeze(
					paths.POD_META.createTree( { name: 'plugh' } )
				);
				const result = getPodName( state );
				const expected = paths.POD_NAME.getFrom( state );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getPodMetaValue()', () => {
			it( 'Should return the meta value', () => {
				const key = 'foo';
				const expected = 'bar';
				const state = deepFreeze(
					paths.POD_META.createTree( { [ key ]: expected } )
				);
				const result = getPodMetaValue( state, key );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );

	describe( 'groups', () => {
		describe( 'getGroupList()', () => {

		} );

		describe( 'getGroups()', () => {

		} );

		describe( 'getGroup()', () => {

		} );
	} );

	describe( 'fields', () => {
		describe( 'getFields()', () => {
			it( 'Should return the fields array', () => {
				const fields = [
					{ name: 'field1', label: 'label1' },
					{ name: 'field2', label: 'label2' },
					{ name: 'field3', label: 'label3' },
				];
				const state = deepFreeze(
					paths.FIELDS.createTree( fields )
				);
				const result = getFields( state );
				const expected = paths.FIELDS.getFrom( state );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
