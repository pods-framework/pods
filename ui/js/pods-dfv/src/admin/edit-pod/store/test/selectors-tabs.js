import deepFreeze from 'deep-freeze';
import { merge } from 'lodash';

import * as paths from '../state-paths';
import {
	getTabList,
	getTab,
	getTabs,
	getTabOptions,
} from '../selectors';

const testTabs = {
	foo: { name: 'foo', titleText: 'Foo' },
	bar: { name: 'bar', titleText: 'Bar' },
};
const options = {
	'foo-option1': { name: 'foo-option1', value: 'foo1 value' },
	'foo-option2': { name: 'foo-option2', value: 'foo2 value' },
	'bar-option1': { name: 'bar-option1', value: 'bar1 value' },
	'bar-option2': { name: 'bar-option2', value: 'bar2 value' },
};
const tabOptionsList = {
	foo: [ 'foo-option2', 'foo-option1' ],
	bar: [ 'bar-option2', 'bar-option1' ],
};

describe( 'tabs selectors', () => {
	describe( 'getTabList()', () => {
		it( 'Should return the ordered tab list', () => {
			const orderedList = [ 'foo', 'bar', 'baz' ];
			const state = deepFreeze(
				paths.TAB_LIST.createTree( orderedList )
			);
			const result = getTabList( state );

			expect( result ).toBeDefined();
			expect( result ).toEqual( orderedList );
		} );
	} );

	describe( 'getTab()', () => {
		const targetTab = 'bar';
		const state = deepFreeze(
			paths.TABS_BY_NAME.createTree( testTabs )
		);

		it( 'Should return the specified tab', () => {
			const result = getTab( state, targetTab );
			const expected = testTabs[ targetTab ];

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );

		it( 'Should return undefined for a non-existent tab name', () => {
			const result = getTab( state, 'not a tab' );
			expect( result ).toBeUndefined();
		} );
	} );

	describe( 'getTabs()', () => {
		it( 'Should return the ordered tabs', () => {
			const state = deepFreeze( merge(
				paths.TABS_BY_NAME.createTree( testTabs ),
				paths.TAB_LIST.createTree( [ 'bar', 'foo' ] )
			) );
			const result = getTabs( state );
			const expected = [ testTabs.bar, testTabs.foo ];

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'getTabOptions()', () => {
		it( 'Should get the ordered options for the specified tab', () => {
			const state = deepFreeze( merge(
				paths.TABS_BY_NAME.createTree( testTabs ),
				paths.OPTIONS.createTree( options ),
				paths.TAB_OPTIONS_LIST.createTree( tabOptionsList ),
			) );

			const result = getTabOptions( state, 'bar' );
			const expected = [
				{ name: 'bar-option2', value: 'bar2 value' },
				{ name: 'bar-option1', value: 'bar1 value' },
			];

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );
	} );
} );

