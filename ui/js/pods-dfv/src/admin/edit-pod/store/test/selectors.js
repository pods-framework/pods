import { uiConstants } from '../constants';
import {
	getActiveTab,
	getSaveStatus,
	getLabels,
	getLabelValue,
	isSaving,
	getPodName
} from '../selectors';

describe( 'selectors', () => {

	describe( 'ui', () => {
		describe( 'getActiveTab', () => {
			const { tabNames } = uiConstants;

			it( 'Should return the active tab', () => {
				const state = {
					ui: { activeTab: tabNames.LABELS }
				};
				expect( getActiveTab( state ) ).toEqual( state.ui.activeTab );
			} );
		} );

		describe( 'getSaveStatus', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should return the save status', () => {
				const state = {
					ui: { saveStatus: saveStatuses.SAVE_SUCCESS }
				};
				expect( getSaveStatus( state ) ).toEqual( state.ui.saveStatus );
			} );
		} );

		describe( 'isSaving', () => {
			const { saveStatuses } = uiConstants;

			it ( 'Should return true when saving', () => {
				const state = {
					ui: { saveStatus: saveStatuses.SAVING }
				};
				expect( isSaving( state ) ).toBe( true );
			} );

			it ( 'Should return false when not saving', () => {
				const state = {
					ui: { saveStatus: saveStatuses.SAVE_SUCCESS }
				};
				expect( isSaving( state ) ).toBe( false );
			} );
		} );
	} );

	describe( 'podMeta', () => {
		describe( 'getPodName', () => {
			it( 'Should return the Pod name', () => {
				const state = {
					podMeta: { podName: 'plugh' }
				};
				expect( getPodName( state ) ).toEqual( state.podMeta.podName );
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
			]
		};
		describe( 'getLabels', () => {
			it ( 'Should return the labels', () => {
				expect( getLabels( state ) ).toEqual( state.labels );
			} );
		} );

		describe( 'getLabelValue', () => {
			it ( 'Should get the label value', () => {
				state.labels.forEach( ( thisLabel ) => {
					expect( getLabelValue( state, thisLabel.name ) ).toBe( thisLabel.value );
				} );
			} );
		} );
	} );
} );
