import React from 'react';
import Select from 'react-select';
import PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';

import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const ListSelect = ( {
	htmlAttributes,
	name,
	value,
	options,
	setValue,
	placeholder,
	isMulti,
	limit,
	// showIcon,
	showViewLink,
	showEditLink,
} ) => {
	// Always have an array of values for the list, even if
	// we were just passed a single object.
	let arrayOfValues = [];

	if ( value ) {
		arrayOfValues = isMulti ? value : [ value ];
	}

	return (
		<>
			<div className="pods-ui-list-autocomplete">
				<Select
					name={ htmlAttributes.name || name }
					options={ options }
					value={ value }
					placeholder={ placeholder }
					isMulti={ isMulti }
					controlShouldRenderValue={ false }
					onChange={ ( newOption ) => {
						if ( isMulti ) {
							setValue( newOption.map( ( selection ) => selection.value ) );
						} else {
							setValue( newOption.value );
						}
					} }
				/>
			</div>

			<div className="pods-pick-values">
				{ !! arrayOfValues.length && (
					<ul className="pods-dfv-list pods-relationship ui-sortable">
						{ arrayOfValues.map( ( valueItem, index ) => {
							const itemName = isMulti ? `name[${ index }]` : name;
							const itemId = isMulti ? `name[${ index }]` : name;

							return (
								<li
									className="pods-dfv-list-item pods-relationship"
									key={ `${ name }-${ index }` }
								>
									<input
										name={ itemName }
										id={ itemId }
										type="hidden"
										value={ valueItem.value }
									/>

									<ul className="pods-dfv-list-meta relationship-item">
										{ ( 1 !== limit ) && (
											<li className="pods-dfv-list-col pods-dfv-list-handle">
												<span>{ __( 'Reorder', 'pods' ) }</span>
											</li>
										) }

										{ /*
										@todo icon logic, see:
										https://github.com/pods-framework/pods/blob/main/ui/js/pods-dfv/_src/pick/views/list-item.html#L16-L32
										*/ }

										<li className="pods-dfv-list-col pods-dfv-list-name">
											{ valueItem.label }
										</li>

										<li className="pods-dfv-list-col pods-dfv-list-remove">
											<a
												href="#remove"
												title={ __( 'Deselect', 'pods' ) }
												onClick={ () => {
													if ( isMulti ) {
														setValue(
															value
																.filter( ( item ) => item.value !== valueItem.value )
																.map( ( item ) => item.value )
														);
													} else {
														setValue( undefined );
													}
												} }
											>
												{ __( 'Deselect', 'pods' ) }
											</a>
										</li>

										{ showViewLink && (
											<li className="pods-dfv-list-col pods-dfv-list-link">
												{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
												<a
													href="#" // @todo
													title={ __( 'View', 'pods' ) }
													target="_blank"
												>
													{ __( 'View', 'pods' ) }
												</a>
											</li>
										) }

										{ showEditLink && (
											<li className="pods-dfv-list-col pods-dfv-list-edit">
												{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
												<a
													href="#" // @todo
													title={ __( 'Edit', 'pods' ) }
													target="_blank"
												>
													{ __( 'Edit', 'pods' ) }
												</a>
											</li>
										) }
									</ul>
								</li>
							);
						} ) }
					</ul>
				) }
			</div>
		</>
	);
};

ListSelect.propTypes = {
	htmlAttributes: PropTypes.shape( {
		id: PropTypes.string,
		class: PropTypes.string,
		name: PropTypes.string,
	} ),
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.shape( {
			label: PropTypes.string.isRequired,
			value: PropTypes.string.isRequired,
		} ),
		PropTypes.arrayOf(
			PropTypes.shape( {
				label: PropTypes.string.isRequired,
				value: PropTypes.string.isRequired,
			} )
		),
	] ),
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
	placeholder: PropTypes.string.isRequired,
	isMulti: PropTypes.bool.isRequired,
	limit: PropTypes.number.isRequired,
	showIcon: PropTypes.bool.isRequired,
	showViewLink: PropTypes.bool.isRequired,
	showEditLink: PropTypes.bool.isRequired,
};

export default ListSelect;
