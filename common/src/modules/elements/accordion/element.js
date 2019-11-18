/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import Row from './row/template';
import './style.pcss';

const Accordion = ( {
	className,
	containerAttrs,
	rows,
} ) => (
	rows.length
	? (
		<div
			aria-multiselectable="true"
			className={ classNames(
				'tribe-editor__accordion',
				className,
			) }
			role="tablist"
			{ ...containerAttrs }
		>
			{ rows.map( ( row, index ) => (
				<Row key={ index } { ...row } />
			) ) }
		</div>
	)
	: null
);

Accordion.defaultProps = {
	containerAttrs: {},
	rows: [],
};

Accordion.propTypes = {
	className: PropTypes.string,
	containerAttrs: PropTypes.object,
	rows: PropTypes.arrayOf( PropTypes.shape( {
		accordionId: PropTypes.string.isRequired,
		content: PropTypes.node,
		contentClassName: PropTypes.string,
		header: PropTypes.node,
		headerClassName: PropTypes.string,
		onClick: PropTypes.func,
		onClose: PropTypes.func,
		onOpen: PropTypes.func,
	} ).isRequired ).isRequired,
};

export default Accordion;
