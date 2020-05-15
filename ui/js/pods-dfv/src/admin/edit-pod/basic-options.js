/* eslint-disable react/prop-types */
import React from 'react';
import { __ } from '@wordpress/i18n';

export const PodsDFVEditFieldBasicOptions = () => {
	// Todo: this is all just copy/paste hardcoded as placeholders, not wired up, would be dynamically assembled
	return (
		<div className="pods-manage-field">
			<div className="pods-tab-group">
				<div id="pods-basic-options" className="pods-tab pods-basic-options">

					<div className="pods-field-option">
						<label
							className="pods-form-ui-label pods-form-ui-label-field-data-label"
							htmlFor="pods-form-ui-field-data-1-label">
							{ __( 'Label', 'pods' ) }
						</label>
						<div className="pods-form-ui-field pods-dfv-field">
							<div className="pods-dfv-container">
								<input
									type="text"
									name="field_data[1][label]"
									id="pods-form-ui-field-data-1-label"
									className="pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-field-data-label pods-validate pods-validate-required pods-validate-error"
									data-name-clean="field-data-label"
									placeholder=""
									maxLength="255"
									value=""
								/>
							</div>
						</div>
					</div>

					<div className="pods-field-option">
						<label
							className="pods-form-ui-label pods-form-ui-label-field-data-name"
							htmlFor="pods-form-ui-field-data-1-name">
							{ __( 'Name', 'pods' ) }
						</label>
						<input
							name="field_data[1][name]"
							data-name-clean="field-data-name"
							id="pods-form-ui-field-data-1-name"
							className="pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-field-data-name pods-validate pods-validate-required pods-slugged-lower"
							type="text"
							value=""
							// eslint-disable-next-line jsx-a11y/tabindex-no-positive
							tabIndex="2"
							maxLength="50"
						/>
					</div>

					<div className="pods-field-option">
						<label
							className="pods-form-ui-label pods-form-ui-label-field-data-label"
							htmlFor="pods-form-ui-field-data-1-description">
							{ __( 'Description', 'pods' ) }
						</label>
						<div className="pods-form-ui-field pods-dfv-field">
							<div className="pods-dfv-container">
								<input
									type="text"
									name="field_data[1][description]"
									id="pods-form-ui-field-data-1-description"
									className="pods-form-ui-field pods-form-ui-field-type-text pods-form-ui-field-name-field-data-label pods-validate pods-validate-required pods-validate-error"
									data-name-clean="field-data-description"
									placeholder=""
									maxLength="255"
									value=""
								/>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	);
};
