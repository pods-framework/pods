import deepFreeze from 'deep-freeze';
import { merge } from 'lodash';

import * as paths from '../state-paths';

import {
	getGroupList,
	getGroups,
	getGroup,
	getGroupFields,
} from '../selectors';

const groupList = [ 'group4', 'group3', 'group2', 'group1' ];

const groups = {
	group1: { name: 'group1' },
	group2: { name: 'group2' },
	group3: { name: 'group3' },
	group4: { name: 'group4' },
};
const groupFieldList = {
	group1: [ 'foo3', 'foo2', 'foo1' ],
	group2: [],
	group3: [],
	group4: [],
};
const fields = {
	foo1: { name: 'foo1', label: 'foo label1' },
	foo2: { name: 'foo2', label: 'foo label2' },
	foo3: { name: 'foo3', label: 'foo label3' },
};

const state = deepFreeze( merge(
	paths.GROUP_LIST.createTree( groupList ),
	paths.GROUPS_BY_NAME.createTree( groups ),
	paths.GROUP_FIELD_LIST.createTree( groupFieldList ),
	paths.FIELDS.createTree( fields ),
) );

/**
 *
 */
describe( 'group selectors', () => {
	describe( 'getGroupList()', () => {
		it( 'Should return an ordered array of group names', () => {
			const result = getGroupList( state );

			expect( result ).toEqual( groupList );
		} );
	} );

	describe( 'getGroups()', () => {
		it( 'Should return an ordered array of group objects', () => {
			const expected = [
				{ name: 'group4' },
				{ name: 'group3' },
				{ name: 'group2' },
				{ name: 'group1' },
			];
			const result = getGroups( state );

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'getGroup()', () => {
		it( 'Should return the group object for the given group name', () => {
			const groupName = 'group3';
			const expected = groups[ groupName ];

			const result = getGroup( state, groupName );

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );

		it( 'Should be undefined for an unknown group name', () => {
			expect( getGroup( state, 'xyzzy' ) ).toBeUndefined();
		} );
	} );

	describe( 'getGroupFields()', () => {
		it( 'Should return the ordered field list for the given group name', () => {
			const groupName = 'group1';
			const expected = [
				{ name: 'foo3', label: 'foo label3' },
				{ name: 'foo2', label: 'foo label2' },
				{ name: 'foo1', label: 'foo label1' },
			];
			const result = getGroupFields( state, groupName );

			expect( result ).toEqual( expected );
		} );
	} );
} );
