/**
 * External Dependencies
 */
import PropTypes from 'prop-types';

//
// ─── VENDOR ─────────────────────────────────────────────────────────────────────
//

export const ReactSelectOption = PropTypes.shape( {
	label: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
} );

export const ReactSelectOptions = PropTypes.arrayOf( ReactSelectOption );
