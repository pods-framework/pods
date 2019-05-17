import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';
import {
	getOption,
	getOptionItemValue,
	getOptionValue,
} from '../selectors';

describe( 'options selectors', () => {
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
