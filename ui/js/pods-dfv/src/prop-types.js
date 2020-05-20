import * as PropTypes from 'prop-types';

// @todo can these be changed to real Booleans on the PHP side?
const BOOLEAN_STRINGS = [ '0', '1' ];

export const FIELD_PROP_TYPE_SHAPE = PropTypes.exact( {
	admin_only: PropTypes.oneOf( BOOLEAN_STRINGS ),
	boolean_yes_label: PropTypes.string,
	datetime_type: PropTypes.string,
	datetime_format: PropTypes.string,
	datetime_time_type: PropTypes.string,
	datetime_time_format: PropTypes.string,
	datetime_time_format_24: PropTypes.string,
	datetime_allow_empty: PropTypes.oneOf( BOOLEAN_STRINGS ),
	datetime_html5: PropTypes.oneOf( BOOLEAN_STRINGS ),
	default: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	'depends-on': PropTypes.object,
	description: PropTypes.string,
	group: PropTypes.string.isRequired,
	help: PropTypes.string,
	hidden: PropTypes.oneOf( BOOLEAN_STRINGS ),
	// @todo this should maybe just be number
	id: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ).isRequired,
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	object_type: PropTypes.string.isRequired,
	// @todo this should maybe just be number
	parent: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ).isRequired,
	// @todo does position actually belong here?
	position: PropTypes.number,
	read_only: PropTypes.oneOf( BOOLEAN_STRINGS ),
	rest_read: PropTypes.oneOf( BOOLEAN_STRINGS ),
	rest_write: PropTypes.oneOf( BOOLEAN_STRINGS ),
	rest_pick_response: PropTypes.string,
	rest_pick_depth: PropTypes.string,
	restrict_capability: PropTypes.oneOf( BOOLEAN_STRINGS ),
	restrict_role: PropTypes.oneOf( BOOLEAN_STRINGS ),
	required: PropTypes.oneOf( BOOLEAN_STRINGS ).isRequired,
	// @todo this should be unserialized to an object?
	roles_allowed: PropTypes.string,
	storage_type: PropTypes.string.isRequired,
	text_allow_html: PropTypes.oneOf( BOOLEAN_STRINGS ),
	text_allow_shortcode: PropTypes.oneOf( BOOLEAN_STRINGS ),
	// @todo can this be an integer from the PHP side?
	text_max_length: PropTypes.string,
	text_allowed_html_tags: PropTypes.string,
	type: PropTypes.string.isRequired,
	weight: PropTypes.number,
} );
