import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';
import { uiConstants } from '../constants';

import {
	getActiveTab,
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
