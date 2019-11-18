/**
 * External dependencies
 */
import { isEmpty, get } from 'lodash';

/**
 * Internal dependencies
 */
import { actions as requestActions } from '@moderntribe/common/store/middlewares/request';

import * as types from './types';
import * as selectors from './selectors';

export const registerForm = ( id, type ) => ( {
	type: types.ADD_FORM,
	payload: {
		id,
		type,
	},
} );

export const clearForm = ( id ) => ( {
	type: types.CLEAR_FORM,
	payload: {
		id,
	},
} );

export const createDraft = ( id, fields ) => ( {
	type: types.CREATE_FORM_DRAFT,
	payload: {
		id,
		fields,
	},
} );

export const editEntry = ( id, fields ) => ( {
	type: types.EDIT_FORM_ENTRY,
	payload: {
		id,
		fields,
	},
} );

export const setSubmit = ( id ) => ( {
	type: types.SUBMIT_FORM,
	payload: {
		id,
	},
} );

export const setSaving = ( id, saving ) => ( {
	type: types.SET_SAVING_FORM,
	payload: {
		id,
		saving,
	},
} );

export const addVolatile = ( id ) => ( {
	type: types.ADD_VOLATILE_ID,
	payload: {
		id,
	},
} );

export const removeVolatile = ( id ) => ( {
	type: types.REMOVE_VOLATILE_ID,
	payload: {
		id,
	},
} );

export const sendForm = ( id, fields = {}, completed ) => ( dispatch, getState ) => {
	const state = getState();
	const props = { name: id };
	const type = selectors.getFormType( state, props );
	const create = selectors.getFormCreate( state, props );
	const details = selectors.getFormFields( state, props );
	const saving = selectors.getFormSaving( state, props );

	if ( saving ) {
		return;
	}

	const path = create
		? `${ type }`
		: `${ type }/${ details.id }`;

	const options = {
		path,
		params: {
			method: create ? 'POST' : 'PUT',
			body: JSON.stringify( fields ),
		},
		actions: {
			start: () => dispatch( setSaving( id, true ) ),
			success: ( { body } ) => {
				const postID = get( body, 'id', '' );

				if ( create && postID ) {
					dispatch( addVolatile( postID ) );
				}
				completed( body );
				dispatch( clearForm( id ) );
				dispatch( setSaving( id, false ) );
			},
			error: () => {
				dispatch( clearForm( id ) );
				dispatch( setSaving( id, false ) );
			},
		},
	};
	dispatch( requestActions.wpRequest( options ) );
};

const deleteEntry = ( dispatch ) => ( path ) => ( { body } ) => {
	const { id, status } = body;

	if ( 'draft' !== status ) {
		dispatch( removeVolatile( id ) );
		return;
	}

	const options = {
		path,
		params: {
			method: 'DELETE',
		},
		actions: {
			success: () => dispatch( removeVolatile( id ) ),
		},
	};
	dispatch( requestActions.wpRequest( options ) );
};

export const maybeRemoveEntry = ( id, details = {} ) => ( dispatch, getState ) => {
	const state = getState();
	const type = selectors.getFormType( state, { name: id } );

	if ( isEmpty( details ) ) {
		return;
	}

	const path = `${ type }/${ details.id }`;
	const options = {
		path,
		actions: {
			success: deleteEntry( dispatch )( path ),
		},
	};
	dispatch( requestActions.wpRequest( options ) );
};
