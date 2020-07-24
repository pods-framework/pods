import * as PropTypes from 'prop-types';

// @todo can these be changed to real Booleans on the PHP side?
const BOOLEAN_STRINGS = [ '0', '1', 0, 1 ];
const BOOLEAN_ALL_TYPES = [ '0', '1', 0, 1, true, false ];

export const FIELD_PROP_TYPE_SHAPE = PropTypes.exact( {
	admin_only: PropTypes.oneOf( BOOLEAN_STRINGS ),
	attributes: PropTypes.array,
	boolean_yes_label: PropTypes.string,
	class: PropTypes.string,
	data: PropTypes.object,
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
	'depends-on': PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.array,
	] ),
	dependency: PropTypes.bool,
	description: PropTypes.string,
	developer_mode: PropTypes.bool,
	'excludes-on': PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.array,
	] ),
	field_type: PropTypes.string,
	group: PropTypes.string.isRequired,
	grouped: PropTypes.number,
	help: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	hidden: PropTypes.oneOf( BOOLEAN_STRINGS ),
	// @todo this should maybe just be number
	id: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ).isRequired,
	label: PropTypes.string.isRequired,
	label_param: PropTypes.string,
	name: PropTypes.string.isRequired,
	number_decimals: PropTypes.number,
	number_format: PropTypes.string,
	number_format_soft: PropTypes.number,
	object_type: PropTypes.string.isRequired,
	old_name: PropTypes.string,
	param_default: PropTypes.string,
	// @todo this should maybe just be number
	parent: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ).isRequired,
	pick_allow_add_new: PropTypes.string,
	pick_object: PropTypes.string,
	pick_format: PropTypes.string,
	pick_display_format_multi: PropTypes.string,
	pick_display_format_separator: PropTypes.string,
	pick_format_single: PropTypes.string,
	pick_format_multi: PropTypes.string,
	pick_format_type: PropTypes.string,
	pick_limit: PropTypes.string,
	pick_show_edit_link: PropTypes.string,
	pick_show_icon: PropTypes.string,
	pick_show_view_link: PropTypes.string,
	pick_taggable: PropTypes.string,
	// @todo does position actually belong here?
	position: PropTypes.number,
	// @todo this seems like it shouldn't be here
	post_status: PropTypes.string,
	read_only: PropTypes.oneOf( BOOLEAN_STRINGS ),
	rest_read: PropTypes.oneOf( BOOLEAN_STRINGS ),
	rest_write: PropTypes.oneOf( BOOLEAN_STRINGS ),
	rest_pick_response: PropTypes.string,
	rest_pick_depth: PropTypes.string,
	restrict_capability: PropTypes.oneOf( BOOLEAN_STRINGS ),
	restrict_role: PropTypes.oneOf( BOOLEAN_STRINGS ),
	required: PropTypes.oneOf( BOOLEAN_ALL_TYPES ),
	// @todo this should be unserialized to an object?
	roles_allowed: PropTypes.string,
	storage_type: PropTypes.string,
	text_allow_html: PropTypes.oneOf( BOOLEAN_STRINGS ),
	text_allow_shortcode: PropTypes.oneOf( BOOLEAN_STRINGS ),
	text_max_length: PropTypes.oneOfType( [
		PropTypes.number,
		PropTypes.string,
	] ),
	text_allowed_html_tags: PropTypes.string,
	text_repeatable: PropTypes.oneOf( BOOLEAN_STRINGS ),
	type: PropTypes.string.isRequired,
	website_format: PropTypes.string,
	website_allow_port: PropTypes.string,
	website_clickable: PropTypes.string,
	website_new_window: PropTypes.string,
	website_max_length: PropTypes.string,
	website_html5: PropTypes.string,
	weight: PropTypes.number,
	'wildcard-on': PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.array,
	] ),
	_locale: PropTypes.string,
} );

export const GROUP_PROP_TYPE_SHAPE = PropTypes.shape( {
	description: PropTypes.string,
	fields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ),
	// @todo this should maybe just be number
	id: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	object_type: PropTypes.string,
	// @todo this should maybe just be number
	parent: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
	storage_type: PropTypes.string,
	weight: PropTypes.number,
	_locale: PropTypes.string,
} );
