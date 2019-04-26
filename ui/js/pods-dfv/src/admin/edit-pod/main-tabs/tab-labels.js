/* eslint-disable react/prop-types */
import React from 'react';
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';

const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;
const { sprintf } = wp.i18n;

export const TabLabels = compose( [
	withSelect( ( select ) => {
	} ),
	withDispatch( ( dispatch ) => {
	} )
] )
( ( props ) => {
	return ( '[Labels]' );
	/*
	return (
		<div id='pods-labels' className='pods-manage-field'>
			{props.labels.map( thisLabel => (
				<div key={thisLabel.name} className="pods-field-option">
					<label
						className={`pods-form-ui-label pods-form-ui-label-${thisLabel.name}`}
						htmlFor={`pods-form-ui-${thisLabel.name}`}>
						{sprintf( thisLabel.label, props.getLabelValue( thisLabel.label_param ) || thisLabel.param_default )}
					</label>
					<div className="pods-form-ui-field pods-dfv-field">
						<div className="pods-dfv-container">
							<input
								type="text"
								name={thisLabel.name}
								id={`pods-form-ui-${thisLabel.name}`}
								className="pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-label"
								maxLength={thisLabel.text_max_length}
								value={props.getLabelValue( thisLabel.name )}
								onChange={ ( e ) => props.setLabelValue( thisLabel.name, e.target.value )}
							/>
						</div>
					</div>
				</div>
			) )}
		</div>
	);
	 */
} );
