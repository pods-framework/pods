import deepFreeze from 'deep-freeze';
import { uiConstants } from '../constants';
import {
	getState,
	getActiveTab,
	getOrderedTabList,
	getTabs,
	getTab,
	getOrderedTabs,
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
		describe( 'tabs', () => {
			describe( 'getActiveTab()', () => {
				const { tabNames } = uiConstants;

				it( 'Should return the active tab', () => {
					const state = deepFreeze( {
						ui: { activeTab: tabNames.LABELS },
					} );
					const result = getActiveTab( state );
					const expected = state.ui.activeTab;

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'getTabList()', () => {
				it( 'Should return the ordered tab list', () => {
					const orderedList = [ 'foo', 'bar', 'baz' ];
					const state = deepFreeze( {
						ui: {
							tabs: { orderedList: orderedList }
						}
					} );
					const result = getOrderedTabList( state );

					expect( result ).toBeDefined();
					expect( result ).toEqual( orderedList );
				} );
			} );

			const testTabs = {
				foo: {
					name: 'foo',
					titleText: 'Foo',
					options: []
				},
				bar: {
					name: 'bar',
					titleText: 'Bar',
					options: []
				}
			} ;


			describe( 'getTabs()', () => {
				it( 'Should return all tab entities', () => {
					const state = deepFreeze( {
						ui: { tabs: { byName: testTabs } }
					} );
					const result = getTabs( state );
					const expected = testTabs;

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'getTab()', () => {
				const state = deepFreeze( {
					ui: { tabs: { byName: testTabs } }
				} );

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

			describe( 'getOrderedTabs()', () => {
				const state = deepFreeze( {
					ui: {
						tabs: {
							byName: testTabs,
							orderedList: [ 'bar', 'foo' ]
						}
					}
				} );
				const result = getOrderedTabs( state );
				const expected = [ testTabs.bar, testTabs.foo ];

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getSaveStatus()', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return the save status', () => {
				const saveStatus = saveStatuses.SAVE_SUCCESS;
				const state = deepFreeze( {
					ui: { saveStatus: saveStatus },
				} );
				const result = getSaveStatus( state );

				expect( result ).toBeDefined();
				expect( result ).toEqual( saveStatus );
			} );
		} );

		describe( 'isSaving()', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return true when saving', () => {
				const state = deepFreeze( {
					ui: { saveStatus: saveStatuses.SAVING },
				} );
				expect( isSaving( state ) ).toBe( true );
			} );

			it( 'Should return false when not saving', () => {
				const state = deepFreeze( {
					ui: { saveStatus: saveStatuses.SAVE_SUCCESS },
				} );
				expect( isSaving( state ) ).toBe( false );
			} );
		} );
	} );

	describe( 'podMeta', () => {
		describe( 'getPodName()', () => {
			it( 'Should return the Pod name', () => {
				const state = deepFreeze( {
					podMeta: { name: 'plugh' },
				} );
				const result = getPodName( state );
				const expected = state.podMeta.name;

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getPodMetaValue()', () => {
			it( 'Should return the meta value', () => {
				const key = 'foo';
				const expected = 'bar';
				const state = deepFreeze( { podMeta: { [ key ]: expected } } );
				const result = getPodMetaValue( state, key );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );

	describe( 'fields', () => {
		const state = deepFreeze( {
			fields: [
				{ name: 'field1', label: 'label1' },
				{ name: 'field2', label: 'label2' },
				{ name: 'field3', label: 'label3' },
			]
		} );
		describe( 'getFields()', () => {
			it( 'Should return the fields array', () => {
				const result = getFields( state );
				const expected = state.fields;

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
