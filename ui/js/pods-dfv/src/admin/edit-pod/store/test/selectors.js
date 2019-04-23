import { uiConstants } from '../constants';
import {
	getState,
	getTabs,
	getActiveTab,
	getSaveStatus,
	getLabels,
	getFields,
	getLabelValue,
	isSaving,
	getPodName,
	getPodMetaValue,
} from '../selectors';

describe( 'selectors', () => {

	describe( 'getState()', () => {
		it( 'Should return the full state', () => {
			const state = {
				foo: {
					'xyzzy': 42,
					'plugh': false
				},
				bar: {
					name: 'bob',
					relationship: 'your uncle'
				},
				baz: [ 0, 1, 2 ]
			};
			const result = getState( state );
			expect( result ).toEqual( state );
		} );
	} );

	describe( 'ui', () => {
		describe( 'getTabs', () => {
			it( 'Should return all tabs', () => {
				const state = {
					ui: {
						tabs: [
							{ foo: {} },
							{ bar: {} },
							{ baz: {} }
						]
					}
				};
				const result = getTabs( state );
				const expected = state.ui.tabs;

				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getActiveTab()', () => {
			const { tabNames } = uiConstants;

			it( 'Should return the active tab', () => {
				const state = {
					ui: { activeTab: tabNames.LABELS },
				};
				const result = getActiveTab( state );
				const expected = state.ui.activeTab;

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getSaveStatus()', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return the save status', () => {
				const state = {
					ui: { saveStatus: saveStatuses.SAVE_SUCCESS },
				};
				const result = getSaveStatus( state );
				const expected = state.ui.saveStatus;

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'isSaving()', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return true when saving', () => {
				const state = {
					ui: { saveStatus: saveStatuses.SAVING },
				};
				expect( isSaving( state ) ).toBe( true );
			} );

			it( 'Should return false when not saving', () => {
				const state = {
					ui: { saveStatus: saveStatuses.SAVE_SUCCESS },
				};
				expect( isSaving( state ) ).toBe( false );
			} );
		} );
	} );

	describe( 'podMeta', () => {
		describe( 'getPodName()', () => {
			it( 'Should return the Pod name', () => {
				const state = {
					podMeta: { name: 'plugh' },
				};
				const result = getPodName( state );
				const expected = state.podMeta.name;

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getPodMetaValue()', () => {
			it( 'Should return the meta value', () => {
				const key = 'foo';
				const expected = 'bar';
				const state = { podMeta: { [ key ]: expected } };
				const result = getPodMetaValue( state, key );

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );

	describe( 'fields', () => {
		const state = {
			fields: [
				{ name: 'field1', label: 'label1' },
				{ name: 'field2', label: 'label2' },
				{ name: 'field3', label: 'label3' },
			]
		};
		describe( 'getFields()', () => {
			it( 'Should return the fields array', () => {
				const result = getFields( state );
				const expected = state.fields;

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );

	describe( 'labels', () => {
		const state = {
			labels: [
				{ name: 'name1', value: 'value1' },
				{ name: 'name2', value: 'value2' },
				{ name: 'name3', value: 'value3' },
			],
		};

		describe( 'getLabels()', () => {
			it( 'Should return the labels array', () => {
				const result = getLabels( state );
				const expected = state.labels;

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getLabelValue()', () => {
			it( 'Should return existing label values', () => {
				state.labels.forEach( ( thisLabel ) => {
					const result = getLabelValue( state, thisLabel.name );
					const expected = thisLabel.value;

					expect( result ).not.toBeUndefined();
					expect( result ).toBe( expected );
				} );
			} );

			it( 'Should return null for a non-existent label', () => {
				const result = getLabelValue( state, 'xyzzy' );
				const expected = null;

				expect( result ).toBeDefined();
				expect( result ).toBe( expected );
			} );
		} );
	} );
} );
