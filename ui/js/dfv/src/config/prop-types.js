import PropTypes from 'prop-types';

// @todo can these be changed to real Booleans on the PHP side?
export const BOOLEAN_STRINGS = PropTypes.oneOf(
	[ '0', '1', 0, 1 ]
);
export const BOOLEAN_ALL_TYPES = PropTypes.oneOf(
	[ '0', '1', 0, 1, true, false ]
);

export const BOOLEAN_ALL_TYPES_OR_EMPTY = PropTypes.oneOf(
	[ '0', '1', 0, 1, true, false, '', null, undefined ]
);

// Handles issue where objects get passed as arrays when empty from PHP.
export const OBJECT_OR_ARRAY = PropTypes.oneOfType( [
	PropTypes.object,
	PropTypes.array,
] );

export const NUMBER_OR_NUMBER_AS_STRING = PropTypes.oneOfType( [
	// @todo custom validator to ensure that the string is a number
	PropTypes.string,
	PropTypes.number,
] );

export const OBJECT_OR_JSON_STRING = PropTypes.oneOfType( [
	PropTypes.object,
	PropTypes.string,
] );

export const PICK_OPTIONS = PropTypes.arrayOf(
	PropTypes.shape( {
		id: PropTypes.oneOfType( [
			PropTypes.string.isRequired,
			PropTypes.arrayOf( PropTypes.shape( {
				name: PropTypes.string.isRequired,
				id: PropTypes.string.isRequired,
			} ) ).isRequired,
		] ),
		icon: PropTypes.string.isRequired,
		name: PropTypes.string.isRequired,
		edit_link: PropTypes.string.isRequired,
		link: PropTypes.string.isRequired,
		selected: PropTypes.bool.isRequired,
	} )
);

export const HTML_ATTR = PropTypes.shape( {
	id: PropTypes.string,
	class: PropTypes.string,
	name: PropTypes.string,
	name_clean: PropTypes.string,
} );

export const AJAX_DATA = PropTypes.shape( {
	ajax: BOOLEAN_ALL_TYPES,
	delay: NUMBER_OR_NUMBER_AS_STRING,
	minimum_input_length: NUMBER_OR_NUMBER_AS_STRING,
	pod: NUMBER_OR_NUMBER_AS_STRING,
	field: NUMBER_OR_NUMBER_AS_STRING,
	id: NUMBER_OR_NUMBER_AS_STRING,
	uri: PropTypes.string,
	_wpnonce: PropTypes.string,
} );

export const FIELD_PROP_TYPE = {
	// Used in multiple fields
	admin_only: BOOLEAN_ALL_TYPES,
	attributes: OBJECT_OR_ARRAY,
	class: PropTypes.string,
	data: PropTypes.any,
	conditional_logic: PropTypes.shape( {
		action: PropTypes.string,
		logic: PropTypes.string,
		rules: PropTypes.arrayOf( PropTypes.shape( {
			field: PropTypes.string,
			compare: PropTypes.string,
			value: PropTypes.any,
		} ) ),
	} ),
	default: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	default_value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	default_value_param: PropTypes.string,
	dependency: PropTypes.bool,
	description: PropTypes.string,
	description_param: PropTypes.string,
	description_param_default: PropTypes.string,
	developer_mode: PropTypes.bool,
	disable_dfv: BOOLEAN_ALL_TYPES,
	display_filter: PropTypes.string,
	display_filter_args: PropTypes.arrayOf( PropTypes.string ),
	editor_options: PropTypes.oneOfType( [
		PropTypes.string, // @todo is this an error message, or a back-end bug?
		PropTypes.object,
	] ),
	enable_conditional_logic: BOOLEAN_ALL_TYPES,
	field_type: PropTypes.string,
	group: NUMBER_OR_NUMBER_AS_STRING,
	fields: PropTypes.arrayOf(
		NUMBER_OR_NUMBER_AS_STRING
	),
	groups: PropTypes.arrayOf(
		NUMBER_OR_NUMBER_AS_STRING
	),
	group_id: NUMBER_OR_NUMBER_AS_STRING,
	grouped: PropTypes.number,
	help: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	help_param: PropTypes.string,
	help_param_default: PropTypes.string,
	hidden: BOOLEAN_ALL_TYPES,
	htmlAttr: HTML_ATTR,
	fieldEmbed: PropTypes.bool,
	id: NUMBER_OR_NUMBER_AS_STRING.isRequired,
	iframe_src: PropTypes.string,
	iframe_title_add: PropTypes.string,
	iframe_title_edit: PropTypes.string,
	label: PropTypes.string.isRequired,
	label_param: PropTypes.string,
	label_param_default: PropTypes.string,
	name: PropTypes.string.isRequired,
	object_type: PropTypes.string,
	old_name: PropTypes.string,
	options: PropTypes.oneOfType( [
		PICK_OPTIONS,
		PropTypes.object,
	] ),
	parent: NUMBER_OR_NUMBER_AS_STRING,
	placeholder: PropTypes.string,
	placeholder_param: PropTypes.string,
	placeholder_param_default: PropTypes.string,
	post_status: PropTypes.string,
	read_only: BOOLEAN_ALL_TYPES,
	rest_pick_response: PropTypes.string,
	rest_pick_depth: NUMBER_OR_NUMBER_AS_STRING,
	rest_read: BOOLEAN_ALL_TYPES,
	rest_write: BOOLEAN_ALL_TYPES,
	restrict_capability: BOOLEAN_ALL_TYPES,
	restrict_role: BOOLEAN_ALL_TYPES,
	required: BOOLEAN_ALL_TYPES,
	roles_allowed: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	sister_id: NUMBER_OR_NUMBER_AS_STRING,
	slug_placeholder: PropTypes.string,
	slug_separator: PropTypes.string,
	slug_fallback: PropTypes.string,
	object_storage_type: PropTypes.string,
	type: PropTypes.string.isRequired,
	unique: PropTypes.string,
	repeatable_add_new_label: PropTypes.string,
	repeatable_reorder: BOOLEAN_ALL_TYPES,
	repeatable_limit: NUMBER_OR_NUMBER_AS_STRING,
	weight: PropTypes.number,
	_locale: PropTypes.string,

	// Avatar fields
	avatar_add_button: PropTypes.string,
	avatar_allowed_extensions: PropTypes.string,
	avatar_attachment_tab: PropTypes.string,
	avatar_edit_title: PropTypes.string,
	avatar_field_template: PropTypes.string,
	avatar_format_type: PropTypes.string,
	avatar_limit: NUMBER_OR_NUMBER_AS_STRING,
	avatar_linked: BOOLEAN_ALL_TYPES,
	avatar_modal_add_button: PropTypes.string,
	avatar_modal_title: PropTypes.string,
	avatar_restrict_filesize: PropTypes.string,
	avatar_show_edit_link: BOOLEAN_ALL_TYPES,
	avatar_type: PropTypes.string,
	avatar_uploader: PropTypes.string,
	avatar_upload_dir: PropTypes.string,
	avatar_upload_dir_custom: PropTypes.string,
	avatar_wp_gallery_columns: PropTypes.string,
	avatar_wp_gallery_link: PropTypes.string,
	avatar_wp_gallery_output: BOOLEAN_ALL_TYPES,
	avatar_wp_gallery_random_sort: BOOLEAN_ALL_TYPES,
	avatar_wp_gallery_size: PropTypes.string,

	// Boolean fields
	boolean_format_type: PropTypes.string,
	boolean_no_label: PropTypes.string,
	boolean_yes_label: PropTypes.string,

	// Boolean Group fields
	boolean_group: PropTypes.arrayOf(
		PropTypes.shape( {
			default: BOOLEAN_ALL_TYPES,
			dependency: PropTypes.bool,
			help: PropTypes.oneOfType( [ PropTypes.string, PropTypes.arrayOf( PropTypes.string ) ] ),
			label: PropTypes.string,
			name: PropTypes.string,
			type: PropTypes.string,
		} ),
	),

	// Code fields
	code_allow_shortcode: PropTypes.string,
	code_max_length: NUMBER_OR_NUMBER_AS_STRING,

	// Color fields
	color_select_label: PropTypes.string,
	color_clear_label: PropTypes.string,

	// Conditional Logic fields
	conditional_logic_affected_field_name: PropTypes.string,

	// Currency fields
	currency_decimal_handling: PropTypes.string,
	currency_decimals: NUMBER_OR_NUMBER_AS_STRING,
	currency_format: PropTypes.string,
	currency_format_placement: PropTypes.string,
	currency_format_sign: PropTypes.string,
	currency_format_type: PropTypes.string,
	currency_html5: BOOLEAN_ALL_TYPES,
	currency_max: NUMBER_OR_NUMBER_AS_STRING,
	currency_max_length: NUMBER_OR_NUMBER_AS_STRING,
	currency_min: NUMBER_OR_NUMBER_AS_STRING,
	currency_placeholder: PropTypes.string,
	currency_step: NUMBER_OR_NUMBER_AS_STRING,

	// Date fields
	date_allow_empty: BOOLEAN_ALL_TYPES,
	date_format: PropTypes.string,
	date_format_custom: PropTypes.string,
	date_format_custom_js: PropTypes.string,
	date_format_moment_js: PropTypes.string,
	date_html5: BOOLEAN_ALL_TYPES,
	date_type: PropTypes.string,
	date_year_range_custom: PropTypes.string,

	// Date/Time fields
	datetime_allow_empty: BOOLEAN_ALL_TYPES,
	datetime_date_format_moment_js: PropTypes.string,
	datetime_format: PropTypes.string,
	datetime_format_custom: PropTypes.string,
	datetime_format_custom_js: PropTypes.string,
	datetime_html5: BOOLEAN_ALL_TYPES,
	datetime_time_format: PropTypes.string,
	datetime_time_format_24: PropTypes.string,
	datetime_time_format_custom: PropTypes.string,
	datetime_time_format_custom_js: PropTypes.string,
	datetime_time_format_moment_js: PropTypes.string,
	datetime_time_type: PropTypes.string,
	datetime_type: PropTypes.string,
	datetime_year_range_custom: PropTypes.string,

	// Email field
	email_html5: BOOLEAN_ALL_TYPES,
	email_max_length: NUMBER_OR_NUMBER_AS_STRING,
	email_placeholder: PropTypes.string,

	// File field
	file_add_button: PropTypes.string,
	file_allowed_extensions: PropTypes.string,
	file_attachment_tab: PropTypes.string,
	file_edit_title: PropTypes.string,
	file_field_template: PropTypes.string,
	file_format_type: PropTypes.string,
	file_limit: NUMBER_OR_NUMBER_AS_STRING,
	file_linked: BOOLEAN_ALL_TYPES,
	file_modal_add_button: PropTypes.string,
	file_modal_title: PropTypes.string,
	file_restrict_filesize: PropTypes.string,
	file_show_edit_link: BOOLEAN_ALL_TYPES,
	file_type: PropTypes.string,
	file_uploader: PropTypes.string,
	file_upload_dir: PropTypes.string,
	file_upload_dir_custom: PropTypes.string,
	file_wp_gallery_columns: PropTypes.any,
	file_wp_gallery_link: PropTypes.any,
	file_wp_gallery_output: BOOLEAN_ALL_TYPES,
	file_wp_gallery_random_sort: BOOLEAN_ALL_TYPES,
	file_wp_gallery_size: PropTypes.any,
	plupload_init: PropTypes.object,
	limit_extensions: PropTypes.string,
	limit_types: PropTypes.string,

	// Heading field
	heading_tag: PropTypes.string,

	// HTML field
	html_content: PropTypes.string,
	html_content_param: PropTypes.string,
	html_content_param_default: PropTypes.string,
	html_wpautop: BOOLEAN_ALL_TYPES,
	html_no_label: BOOLEAN_ALL_TYPES,

	// Number field
	number_decimals: NUMBER_OR_NUMBER_AS_STRING,
	number_format: PropTypes.oneOf( [
		'i18n',
		'9.999,99',
		'9,999.99',
		"9'999.99",
		'9 999,99',
		'9999.99',
		'9999,99',
	] ),
	number_format_soft: BOOLEAN_ALL_TYPES,
	number_format_type: PropTypes.string,
	number_html5: BOOLEAN_ALL_TYPES,
	number_max: NUMBER_OR_NUMBER_AS_STRING,
	number_max_length: NUMBER_OR_NUMBER_AS_STRING,
	number_min: NUMBER_OR_NUMBER_AS_STRING,
	number_placeholder: PropTypes.string,
	number_step: NUMBER_OR_NUMBER_AS_STRING,

	// Oembed field
	oembed_enable_providers: PropTypes.object,
	oembed_enabled_providers_amazoncn: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_amazoncom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_amazoncomau: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_amazoncouk: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_amazonin: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_animotocom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_cloudupcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_crowdsignalcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_dailymotioncom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_facebookcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_flickrcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_imgurcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_instagramcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_issuucom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_kickstartercom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_meetupcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_mixcloudcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_redditcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_reverbnationcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_screencastcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_scribdcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_slidesharenet: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_smugmugcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_someecardscom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_soundcloudcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_speakerdeckcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_spotifycom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_tedcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_tiktokcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_tumblrcom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_twittercom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_vimeocom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_wordpresscom: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_wordpresstv: BOOLEAN_ALL_TYPES,
	oembed_enabled_providers_youtubecom: BOOLEAN_ALL_TYPES,
	oembed_height: NUMBER_OR_NUMBER_AS_STRING,
	oembed_restrict_providers: BOOLEAN_ALL_TYPES,
	oembed_show_preview: BOOLEAN_ALL_TYPES,
	oembed_width: NUMBER_OR_NUMBER_AS_STRING,

	// Paragraph
	paragraph_allow_html: BOOLEAN_ALL_TYPES,
	paragraph_allow_shortcode: BOOLEAN_ALL_TYPES,
	paragraph_allowed_html_tags: PropTypes.string,
	paragraph_convert_chars: BOOLEAN_ALL_TYPES,
	paragraph_max_length: NUMBER_OR_NUMBER_AS_STRING,
	paragraph_oembed: BOOLEAN_ALL_TYPES,
	paragraph_placeholder: PropTypes.string,
	paragraph_wpautop: BOOLEAN_ALL_TYPES,
	paragraph_wptexturize: BOOLEAN_ALL_TYPES,

	// Password
	password_max_length: NUMBER_OR_NUMBER_AS_STRING,
	password_placeholder: PropTypes.string,

	// Phone field
	phone_enable_phone_extension: PropTypes.string,
	phone_format: PropTypes.string,
	phone_html5: BOOLEAN_ALL_TYPES,
	phone_max_length: NUMBER_OR_NUMBER_AS_STRING,
	phone_options: PropTypes.object,
	phone_placeholder: PropTypes.string,

	// Pick field
	default_icon: PropTypes.string,
	fieldItemData: PropTypes.oneOfType(
		[
			PropTypes.arrayOf(
				PropTypes.oneOfType(
					[
						PropTypes.string,
						PropTypes.shape( {
							id: PropTypes.string,
							icon: PropTypes.string,
							name: PropTypes.string,
							edit_link: PropTypes.string,
							link: PropTypes.string,
							selected: BOOLEAN_ALL_TYPES,
						} ),
					],
				),
			),
			PropTypes.object,
		]
	),
	pick_ajax: BOOLEAN_ALL_TYPES,
	pick_allow_add_new: BOOLEAN_ALL_TYPES,
	pick_add_new_label: PropTypes.string,
	pick_custom: PropTypes.string,
	pick_display: PropTypes.string,
	pick_display_format_multi: PropTypes.string,
	pick_display_format_separator: PropTypes.string,
	pick_format_multi: PropTypes.oneOf( [
		'autocomplete',
		'checkbox',
		'list',
		'multiselect',
	] ),
	pick_format_single: PropTypes.oneOf( [
		'autocomplete',
		'checkbox',
		'dropdown',
		'list',
		'radio',
	] ),
	pick_format_type: PropTypes.oneOf( [
		'single',
		'multi',
	] ),
	pick_groupby: PropTypes.string,
	pick_limit: NUMBER_OR_NUMBER_AS_STRING,
	pick_object: PropTypes.string,
	pick_orderby: PropTypes.string,
	pick_placeholder: PropTypes.string,
	pick_post_status: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	pick_select_text: PropTypes.string,
	pick_show_edit_link: BOOLEAN_ALL_TYPES,
	pick_show_icon: BOOLEAN_ALL_TYPES,
	pick_show_select_text: BOOLEAN_ALL_TYPES,
	pick_show_view_link: BOOLEAN_ALL_TYPES,
	pick_table: PropTypes.string,
	pick_table_id: PropTypes.string,
	pick_table_index: PropTypes.string,
	pick_taggable: BOOLEAN_ALL_TYPES,
	pick_user_role: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	pick_val: PropTypes.string,
	pick_where: PropTypes.string,
	table_info: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	view_name: PropTypes.string,
	ajax_data: AJAX_DATA,
	select2_overrides: PropTypes.any,
	supports_thumbnails: BOOLEAN_ALL_TYPES,
	optgroup: PropTypes.any,

	// Text field
	text_allow_html: BOOLEAN_ALL_TYPES,
	text_allow_shortcode: BOOLEAN_ALL_TYPES,
	text_allowed_html_tags: PropTypes.string,
	text_max_length: NUMBER_OR_NUMBER_AS_STRING,
	text_placeholder: PropTypes.string,

	// Time	field
	time_allow_empty: BOOLEAN_ALL_TYPES,
	time_format: PropTypes.string,
	time_format_24: PropTypes.string,
	time_format_custom: PropTypes.string,
	time_format_custom_js: PropTypes.string,
	time_format_moment_js: PropTypes.string,
	time_html5: BOOLEAN_ALL_TYPES,
	time_type: PropTypes.string,

	// Website field
	website_allow_port: BOOLEAN_ALL_TYPES,
	website_clickable: BOOLEAN_ALL_TYPES,
	website_format: PropTypes.string,
	website_new_window: BOOLEAN_ALL_TYPES,
	website_max_length: NUMBER_OR_NUMBER_AS_STRING,
	website_html5: BOOLEAN_ALL_TYPES,
	website_placeholder: PropTypes.string,

	// Wysiwyg field
	wysiwyg_allow_shortcode: BOOLEAN_ALL_TYPES,
	wysiwyg_allowed_html_tags: PropTypes.string,
	wysiwyg_convert_chars: BOOLEAN_ALL_TYPES,
	wysiwyg_editor: PropTypes.string,
	wysiwyg_editor_height: NUMBER_OR_NUMBER_AS_STRING,
	wysiwyg_media_buttons: BOOLEAN_ALL_TYPES,
	wysiwyg_default_editor: PropTypes.string,
	wysiwyg_oembed: BOOLEAN_ALL_TYPES,
	wysiwyg_wpautop: BOOLEAN_ALL_TYPES,
	wysiwyg_wptexturize: BOOLEAN_ALL_TYPES,
};

export const FIELD_PROP_TYPE_SHAPE = PropTypes.shape( FIELD_PROP_TYPE );

export const GROUP_PROP_TYPE_SHAPE = PropTypes.shape( {
	description: PropTypes.string,
	fields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ),
	id: NUMBER_OR_NUMBER_AS_STRING.isRequired,
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	object_type: PropTypes.string,
	parent: NUMBER_OR_NUMBER_AS_STRING.isRequired,
	object_storage_type: PropTypes.string,
	weight: PropTypes.number,
	_locale: PropTypes.string,
} );

/**
 * Components will extend this shape, but the base will guarantee
 * the minimum props to render a Field Component (used by FieldWrapper).
 */
export const FIELD_COMPONENT_BASE_PROPS = {
	/**
	 * Function to add additional validation rules, beyond the
	 * FieldWrapper defaults.
	 */
	addValidationRules: PropTypes.func.isRequired,

	/**
	 * Field config.
	 */
	fieldConfig: FIELD_PROP_TYPE_SHAPE.isRequired,

	/**
	 * Function to update the field's value on change.
	 */
	setValue: PropTypes.func.isRequired,

	/**
	 * Used to notify the FieldWrapper that an onBlur event has
	 * occurred, for validating purposes.
	 */
	setHasBlurred: PropTypes.func.isRequired,

	/**
	 * Default type for `value` is a string, components may want to
	 * override this with another specific type.
	 */
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
		PropTypes.array,
	] ),
};
