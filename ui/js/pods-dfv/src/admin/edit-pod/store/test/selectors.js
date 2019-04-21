import { uiConstants } from '../constants';
import {
	getActiveTab,
	getSaveStatus,
	getLabels,
	getLabelValue,
	isSaving,
	getPodName,
} from '../selectors';

describe( 'selectors', () => {

	describe( 'ui', () => {
		describe( 'getActiveTab', () => {
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

		describe( 'getSaveStatus', () => {
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

		describe( 'isSaving', () => {
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
		describe( 'getPodName', () => {
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
	} );

	describe( 'fields', () => {
	} );

	describe( 'labels', () => {
		const state = {
			labels: [
				{ name: 'name1', value: 'value1' },
				{ name: 'name2', value: 'value2' },
				{ name: 'name3', value: 'value3' },
			],
		};

		describe( 'getLabels', () => {
			it( 'Should return the labels array', () => {
				const result = getLabels( state );
				const expected = state.labels;

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getLabelValue', () => {
			it( 'Should return label values', () => {
				state.labels.forEach( ( thisLabel ) => {
					const result = getLabelValue( state, thisLabel.name );
					const expected = thisLabel.value;

					expect( result ).not.toBeUndefined();
					expect( result ).toBe( expected );
				} );
			} );
		} );
	} );
} );
