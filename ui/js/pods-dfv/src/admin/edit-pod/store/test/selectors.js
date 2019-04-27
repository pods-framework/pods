import deepFreeze from 'deep-freeze';
import { merge } from 'lodash';

import * as paths from '../state-paths';
import { uiConstants } from '../constants';
import {
	getState,
	getOption,
	getActiveTab,
	getOrderedTabList,
	getTab,
	getTabs,
	getTabOptions,
	getSaveStatus,
	isSaving,
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

	describe( 'ui', () => {
		describe( 'tabs/options', () => {
			describe( 'getOption()', () => {
				const options = {
					option1: { name: 'option1', value: 'option1 value' },
					option2: { name: 'option2', value: 'option2 value' },
					option3: { name: 'option3', value: 'option3 value' },
				};

				it( 'Should return the specified option', () => {
					const state = deepFreeze(
						paths.createObjectIn( paths.OPTIONS, options )
					);
					const result = getOption( state, 'option2' );
					const expected = options.option2;

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'getActiveTab()', () => {
				const { tabNames } = uiConstants;

				it( 'Should return the active tab', () => {
					const state = deepFreeze(
						paths.createObjectIn( paths.ACTIVE_TAB, tabNames.LABELS )
					);
					const result = getActiveTab( state );
					const expected = paths.get( state, paths.UI ).activeTab;

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'getTabList()', () => {
				it( 'Should return the ordered tab list', () => {
					const orderedList = [ 'foo', 'bar', 'baz' ];
					const state = deepFreeze(
						paths.createObjectIn( paths.ORDERED_TAB_LIST, orderedList )
					);
					const result = getOrderedTabList( state );

					expect( result ).toBeDefined();
					expect( result ).toEqual( orderedList );
				} );
			} );

			const testTabs = {
				foo: {
					name: 'foo',
					titleText: 'Foo',
					options: [ 'foo-option1', 'foo-option1' ]
				},
				bar: {
					name: 'bar',
					titleText: 'Bar',
					options: [ 'bar-option1', 'bar-option2' ]
				}
			} ;

			describe( 'getTab()', () => {
				const state = deepFreeze(
					paths.createObjectIn( paths.TAB_BY_NAME, testTabs )
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
					const state = deepFreeze(
						paths.createObjectIn( paths.TABS, {
							byName: testTabs,
							orderedList: [ 'bar', 'foo' ]
						} )
					);
					const result = getTabs( state );
					const expected = [ testTabs.bar, testTabs.foo ];

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'getTabOptions()', () => {
				it( 'Should get the ordered options for the specified tab', () => {
					const testTabs = {
						foo: {
							name: 'foo',
							titleText: 'Foo',
							optionList: [ 'foo-option2', 'foo-option1' ]
						},
						bar: {
							name: 'bar',
							titleText: 'Bar',
							optionList: [ 'bar-option2', 'bar-option1' ]
						}
					} ;
					const options = {
						'foo-option1': { name: 'foo-option1', value: 'foo1 value' },
						'foo-option2': { name: 'foo-option2', value: 'foo2 value' },
						'bar-option1': { name: 'bar-option1', value: 'bar1 value' },
						'bar-option2': { name: 'bar-option2', value: 'bar2 value' },
					};
					const state = deepFreeze( merge(
						paths.createObjectIn( paths.TAB_BY_NAME, testTabs ),
						paths.createObjectIn( paths.OPTIONS, options )
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

		describe( 'getSaveStatus()', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return the save status', () => {
				const saveStatus = saveStatuses.SAVE_SUCCESS;
				const state = deepFreeze(
					paths.createObjectIn( paths.SAVE_STATUS, saveStatus )
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
					paths.createObjectIn( paths.SAVE_STATUS, saveStatuses.SAVING )
				);
				expect( isSaving( state ) ).toBe( true );
			} );

			it( 'Should return false when not saving', () => {
				const state = deepFreeze(
					paths.createObjectIn( paths.SAVE_STATUS, saveStatuses.SAVE_SUCCESS )
				);
				expect( isSaving( state ) ).toBe( false );
			} );
		} );
	} );

	describe( 'podMeta', () => {
		describe( 'getPodName()', () => {
			it( 'Should return the Pod name', () => {
				const state = deepFreeze(
					paths.createObjectIn( paths.POD_META, { name: 'plugh' } )
				);
				const result = getPodName( state );
				const expected = paths.get( state, paths.POD_META ).name;

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getPodMetaValue()', () => {
			it( 'Should return the meta value', () => {
				const key = 'foo';
				const expected = 'bar';
				const state = deepFreeze(
					paths.createObjectIn( paths.POD_META, { [ key ]: expected } )
				);
				const result = getPodMetaValue( state, key );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
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
					paths.createObjectIn( paths.FIELDS, fields )
				);
				const result = getFields( state );
				const expected = paths.get( state, paths.FIELDS );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
