import deepFreeze from 'deep-freeze';
import { merge } from 'lodash';

import * as paths from '../state-paths';
import { uiConstants } from '../constants';

import {
	getActiveTab,
	getTabList,
	getTab,
	getTabs,
	getTabOptions,
	getSaveStatus,
	isSaving,
} from '../selectors';

describe( 'ui selectors', () => {
	describe( 'tabs', () => {
		describe( 'getActiveTab()', () => {
			const { tabNames } = uiConstants;

			it( 'Should return the active tab', () => {
				const state = deepFreeze(
					paths.ACTIVE_TAB.createTree( tabNames.LABELS )
				);
				const result = getActiveTab( state );
				const expected = paths.ACTIVE_TAB.getFrom( state );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

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

		// These are being reused below
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

		describe( 'getTab()', () => {
			const state = deepFreeze(
				paths.TABS_BY_NAME.createTree( testTabs )
			);

			it( 'Should return the specified tab', () => {
				const result = getTab( state, 'bar' );
				const expected = testTabs.bar;

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

	describe( 'save status', () => {
		describe( 'getSaveStatus()', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return the save status', () => {
				const saveStatus = saveStatuses.SAVE_SUCCESS;
				const state = deepFreeze(
					paths.SAVE_STATUS.createTree( saveStatus )
				);
				const result = getSaveStatus( state );

				expect( result ).toBeDefined();
				expect( result ).toEqual( saveStatus );
			} );
		} );

		describe( 'isSaving()', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return true when saving', () => {
				const state = deepFreeze(
					paths.SAVE_STATUS.createTree( saveStatuses.SAVING )
				);
				expect( isSaving( state ) ).toBe( true );
			} );

			it( 'Should return false when not saving', () => {
				const state = deepFreeze(
					paths.SAVE_STATUS.createTree( saveStatuses.SAVE_SUCCESS )
				);
				expect( isSaving( state ) ).toBe( false );
			} );
		} );
	} );
} );
