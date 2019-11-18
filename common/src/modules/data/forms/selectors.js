/**
 * External dependencies
 */
import { createSelector } from 'reselect';
import { DEFAULT_STATE } from './reducers/form';

export const formSelector = ( state, props ) => state.forms.byId[ props.name ];

export const getFormType = createSelector(
	[ formSelector ],
	( block ) => block ? block.type : DEFAULT_STATE.type
);

export const getFormEdit = createSelector(
	[ formSelector ],
	( block ) => block ? block.edit : DEFAULT_STATE.edit
);

export const getFormCreate = createSelector(
	[ formSelector ],
	( block ) => block ? block.create : DEFAULT_STATE.create,
);

export const getFormSubmit = createSelector(
	[ formSelector ],
	( block ) => block ? block.submit : DEFAULT_STATE.submit
);

export const getFormFields = createSelector(
	[ formSelector ],
	( block ) => block ? block.fields : DEFAULT_STATE.fields,
);

export const getFormSaving = createSelector(
	[ formSelector ],
	( block ) => block ? block.saving : DEFAULT_STATE.saving
);

export const getVolatile = ( state ) => state.forms.volatile;
