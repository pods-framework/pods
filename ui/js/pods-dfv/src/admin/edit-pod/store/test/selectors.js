import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';

import {
	getState,
	getFields,
	getPodName,
	getPodOptionValue,
	getActiveTab,
	getSaveStatus,
} from '../selectors';

import { uiConstants } from '../constants';

test( 'getState()', () => {
	it( 'Should return the full state', () => {
		const state = deepFreeze( {
			foo: {
				xyzzy: 42,
				plugh: false,
			},
			bar: {
				name: 'bob',
				relationship: 'your uncle',
			},
			baz: [ 0, 1, 2 ],
		} );
		const result = getState( state );

		expect( result ).toBeDefined();
		expect( result ).toEqual( state );
	} );
} );

describe( 'Pod options selectors', () => {
	test( 'getPodName()', () => {
		it( 'Should return the Pod name', () => {
			const state = deepFreeze(
				paths.CURRENT_POD.createTree( { name: 'plugh' } )
			);

			const result = getPodName( state );

			expect( result ).toEqual( 'plugh' );
		} );
	} );

	test( 'getFields()', () => {
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

	test( 'getPodOptionValue()', () => {
		it( 'Should return the option', () => {
			const key = 'foo';
			const expected = 'bar';
			const state = deepFreeze(
				paths.getPodOptionValue.createTree( { [ key ]: expected } )
			);

			const result = getPodOptionValue( state, key );

			expect( result ).toEqual( 'bar' );
		} );
	} );
} );

describe( 'UI selectors', () => {
	test( 'tabs', () => {
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
