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

export const PICK_OPTIONS = PropTypes.arrayOf(
	PropTypes.shape( {
		label: PropTypes.string.isRequired,
		value: PropTypes.oneOfType( [
			PropTypes.string.isRequired,
			PropTypes.arrayOf(
				PropTypes.shape( {
					label: PropTypes.string.isRequired,
					value: PropTypes.string.isRequired,
				} )
			),
		] ),
	} )
);

export const HTML_ATTR = PropTypes.shape( {
	id: PropTypes.string,
	class: PropTypes.string,
	name: PropTypes.string,
	name_clean: PropTypes.string,
} );

export const FIELD_PROP_TYPE = {
	// Used in multiple fields
	admin_only: BOOLEAN_STRINGS,
	attributes: OBJECT_OR_ARRAY,
	class: PropTypes.string,
	data: PropTypes.oneOfType( [
		PICK_OPTIONS,
		PropTypes.object,
	] ),
	default: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	'depends-on': OBJECT_OR_ARRAY,
	'depends-on-any': OBJECT_OR_ARRAY,
	dependency: PropTypes.bool,
	description: PropTypes.string,
	developer_mode: PropTypes.bool,
	disable_dfv: BOOLEAN_ALL_TYPES,
	display_filter: PropTypes.string,
	display_filter_args: PropTypes.arrayOf( PropTypes.string ),
	editor_options: PropTypes.oneOfType( [
		PropTypes.string, // @todo is this an error message, or a back-end bug?
		PropTypes.object,
	] ),
	'excludes-on': OBJECT_OR_ARRAY,
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
	hidden: BOOLEAN_STRINGS,
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
	post_status: PropTypes.string,
	read_only: BOOLEAN_STRINGS,
	rest_pick_response: PropTypes.string,
	rest_pick_depth: PropTypes.string,
	rest_read: BOOLEAN_STRINGS,
	rest_write: BOOLEAN_STRINGS,
	restrict_capability: BOOLEAN_STRINGS,
	restrict_role: BOOLEAN_STRINGS,
	required: BOOLEAN_ALL_TYPES,
	roles_allowed: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	sister_id: NUMBER_OR_NUMBER_AS_STRING,
	slug_placeholder: PropTypes.string,
	slug_separator: PropTypes.string,
	slug_fallback: PropTypes.string,
	storage_type: PropTypes.string,
	type: PropTypes.string.isRequired,
	unique: PropTypes.string,
	weight: PropTypes.number,
	'wildcard-on': OBJECT_OR_ARRAY,
	_locale: PropTypes.string,

	// Avatar fields
	avatar_add_button: PropTypes.string,
	avatar_allowed_extensions: PropTypes.string,
	avatar_attachment_tab: PropTypes.string,
	avatar_edit_title: PropTypes.string,
	avatar_field_template: PropTypes.string,
	avatar_format_type: PropTypes.string,
	avatar_limit: NUMBER_OR_NUMBER_AS_STRING,
	avatar_linked: PropTypes.string,
	avatar_modal_add_button: PropTypes.string,
	avatar_modal_title: PropTypes.string,
	avatar_restrict_filesize: PropTypes.string,
	avatar_show_edit_link: PropTypes.string,
	avatar_type: PropTypes.string,
	avatar_uploader: PropTypes.string,
	avatar_upload_dir: PropTypes.string,
	avatar_upload_dir_custom: PropTypes.string,
	avatar_wp_gallery_columns: PropTypes.string,
	avatar_wp_gallery_link: PropTypes.string,
	avatar_wp_gallery_output: PropTypes.string,
	avatar_wp_gallery_random_sort: PropTypes.string,
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
	code_repeatable: BOOLEAN_ALL_TYPES,

	// Color fields
	color_repeatable: BOOLEAN_ALL_TYPES,

	// Currency fields
	currency_decimal_handling: PropTypes.string,
	currency_decimals: PropTypes.string,
	currency_format: PropTypes.string,
	currency_format_placement: PropTypes.string,
	currency_format_sign: PropTypes.string,
	currency_format_type: PropTypes.string,
	currency_html5: BOOLEAN_ALL_TYPES,
	currency_max: PropTypes.string,
	currency_max_length: NUMBER_OR_NUMBER_AS_STRING,
	currency_min: PropTypes.string,
	currency_placeholder: PropTypes.string,
	currency_repeatable: BOOLEAN_ALL_TYPES,
	currency_step: PropTypes.string,

	// Date fields
	date_allow_empty: PropTypes.string,
	date_format: PropTypes.string,
	date_format_custom: PropTypes.string,
	date_format_custom_js: PropTypes.string,
	date_html5: BOOLEAN_ALL_TYPES,
	date_repeatable: BOOLEAN_ALL_TYPES,
	date_type: PropTypes.string,
	date_year_range_custom: PropTypes.string,

	// Date/Time fields
	datetime_allow_empty: BOOLEAN_STRINGS,
	datetime_format: PropTypes.string,
	datetime_format_custom: PropTypes.string,
	datetime_format_custom_js: PropTypes.string,
	datetime_html5: BOOLEAN_STRINGS,
	datetime_repeatable: BOOLEAN_ALL_TYPES,
	datetime_time_format: PropTypes.string,
	datetime_time_format_24: PropTypes.string,
	datetime_time_format_custom: PropTypes.string,
	datetime_time_format_custom_js: PropTypes.string,
	datetime_time_type: PropTypes.string,
	datetime_type: PropTypes.string,
	datetime_year_range_custom: PropTypes.string,

	// Email field
	email_html5: BOOLEAN_ALL_TYPES,
	email_max_length: NUMBER_OR_NUMBER_AS_STRING,
	email_placeholder: PropTypes.string,
	email_repeatable: BOOLEAN_ALL_TYPES,

	// File field
	file_add_button: PropTypes.string,
	file_allowed_extensions: PropTypes.string,
	file_attachment_tab: PropTypes.string,
	file_edit_title: PropTypes.string,
	file_field_template: PropTypes.string,
	file_format_type: PropTypes.string,
	file_limit: PropTypes.oneOfType(
		[
			PropTypes.string,
			PropTypes.number,
		]
	),
	file_linked: PropTypes.string,
	file_modal_add_button: PropTypes.string,
	file_modal_title: PropTypes.string,
	file_restrict_filesize: PropTypes.string,
	file_show_edit_link: PropTypes.string,
	file_type: PropTypes.string,
	file_uploader: PropTypes.string,
	file_upload_dir: PropTypes.string,
	file_upload_dir_custom: PropTypes.string,
	file_wp_gallery_columns: PropTypes.string,
	file_wp_gallery_link: PropTypes.string,
	file_wp_gallery_output: PropTypes.string,
	file_wp_gallery_random_sort: PropTypes.string,
	file_wp_gallery_size: PropTypes.string,
	plupload_init: PropTypes.object,
	limit_extensions: PropTypes.string,
	limit_types: PropTypes.string,

	// HTML field
	html_content: PropTypes.string,
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
	number_max: PropTypes.string,
	number_max_length: NUMBER_OR_NUMBER_AS_STRING,
	number_min: PropTypes.string,
	number_placeholder: PropTypes.string,
	number_repeatable: BOOLEAN_ALL_TYPES,
	number_step: PropTypes.string,

	// Oembed field
	oembed_enable_providers: PropTypes.object,
	oembed_enabled_providers_amazoncn: PropTypes.string,
	oembed_enabled_providers_amazoncom: PropTypes.string,
	oembed_enabled_providers_amazoncomau: PropTypes.string,
	oembed_enabled_providers_amazoncouk: PropTypes.string,
	oembed_enabled_providers_amazonin: PropTypes.string,
	oembed_enabled_providers_animotocom: PropTypes.string,
	oembed_enabled_providers_cloudupcom: PropTypes.string,
	oembed_enabled_providers_crowdsignalcom: PropTypes.string,
	oembed_enabled_providers_dailymotioncom: PropTypes.string,
	oembed_enabled_providers_facebookcom: PropTypes.string,
	oembed_enabled_providers_flickrcom: PropTypes.string,
	oembed_enabled_providers_imgurcom: PropTypes.string,
	oembed_enabled_providers_instagramcom: PropTypes.string,
	oembed_enabled_providers_issuucom: PropTypes.string,
	oembed_enabled_providers_kickstartercom: PropTypes.string,
	oembed_enabled_providers_meetupcom: PropTypes.string,
	oembed_enabled_providers_mixcloudcom: PropTypes.string,
	oembed_enabled_providers_redditcom: PropTypes.string,
	oembed_enabled_providers_reverbnationcom: PropTypes.string,
	oembed_enabled_providers_screencastcom: PropTypes.string,
	oembed_enabled_providers_scribdcom: PropTypes.string,
	oembed_enabled_providers_slidesharenet: PropTypes.string,
	oembed_enabled_providers_smugmugcom: PropTypes.string,
	oembed_enabled_providers_someecardscom: PropTypes.string,
	oembed_enabled_providers_soundcloudcom: PropTypes.string,
	oembed_enabled_providers_speakerdeckcom: PropTypes.string,
	oembed_enabled_providers_spotifycom: PropTypes.string,
	oembed_enabled_providers_tedcom: PropTypes.string,
	oembed_enabled_providers_tiktokcom: PropTypes.string,
	oembed_enabled_providers_tumblrcom: PropTypes.string,
	oembed_enabled_providers_twittercom: PropTypes.string,
	oembed_enabled_providers_vimeocom: PropTypes.string,
	oembed_enabled_providers_wordpresscom: PropTypes.string,
	oembed_enabled_providers_wordpresstv: PropTypes.string,
	oembed_enabled_providers_youtubecom: PropTypes.string,
	oembed_height: PropTypes.string,
	oembed_repeatable: BOOLEAN_ALL_TYPES,
	oembed_restrict_providers: PropTypes.string,
	oembed_show_preview: PropTypes.string,
	oembed_width: PropTypes.string,

	// Paragraph
	paragraph_allow_html: PropTypes.string,
	paragraph_allow_shortcode: PropTypes.string,
	paragraph_allowed_html_tags: PropTypes.string,
	paragraph_convert_chars: PropTypes.string,
	paragraph_max_length: NUMBER_OR_NUMBER_AS_STRING,
	paragraph_oembed: PropTypes.string,
	paragraph_placeholder: PropTypes.string,
	paragraph_repeatable: BOOLEAN_ALL_TYPES,
	paragraph_wpautop: PropTypes.string,
	paragraph_wptexturize: PropTypes.string,

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
	phone_repeatable: BOOLEAN_ALL_TYPES,

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
	pick_post_status: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	pick_select_text: PropTypes.string,
	pick_show_edit_link: BOOLEAN_ALL_TYPES,
	pick_show_icon: NUMBER_OR_NUMBER_AS_STRING,
	pick_show_view_link: NUMBER_OR_NUMBER_AS_STRING,
	pick_table: PropTypes.string,
	pick_table_id: PropTypes.string,
	pick_table_index: PropTypes.string,
	pick_taggable: NUMBER_OR_NUMBER_AS_STRING,
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
	ajax_data: PropTypes.shape( {
		ajax: BOOLEAN_ALL_TYPES,
		delay: NUMBER_OR_NUMBER_AS_STRING,
		minimum_input_length: NUMBER_OR_NUMBER_AS_STRING,
		pod: NUMBER_OR_NUMBER_AS_STRING,
		field: NUMBER_OR_NUMBER_AS_STRING,
		id: NUMBER_OR_NUMBER_AS_STRING,
		uri: PropTypes.string,
		_wpnonce: PropTypes.string,
	} ),
	select2_overrides: PropTypes.any,
	supports_thumbnails: PropTypes.any,
	optgroup: PropTypes.any,

	// Text field
	text_allow_html: BOOLEAN_STRINGS,
	text_allow_shortcode: BOOLEAN_STRINGS,
	text_allowed_html_tags: PropTypes.string,
	text_max_length: NUMBER_OR_NUMBER_AS_STRING,
	text_placeholder: PropTypes.string,
	text_repeatable: BOOLEAN_STRINGS,

	// Time	field
	time_allow_empty: BOOLEAN_STRINGS,
	time_format: PropTypes.string,
	time_format_24: PropTypes.string,
	time_format_custom: PropTypes.string,
	time_format_custom_js: PropTypes.string,
	time_html5: BOOLEAN_ALL_TYPES,
	time_repeatable: BOOLEAN_ALL_TYPES,
	time_type: PropTypes.string,

	// Website field
	website_allow_port: PropTypes.string,
	website_clickable: PropTypes.string,
	website_format: PropTypes.string,
	website_new_window: PropTypes.string,
	website_max_length: NUMBER_OR_NUMBER_AS_STRING,
	website_html5: BOOLEAN_ALL_TYPES,
	website_placeholder: PropTypes.string,
	website_repeatable: BOOLEAN_ALL_TYPES,

	// Wysiwyg field
	wysiwyg_allow_shortcode: PropTypes.string,
	wysiwyg_allowed_html_tags: PropTypes.string,
	wysiwyg_convert_chars: PropTypes.string,
	wysiwyg_editor: PropTypes.string,
	wysiwyg_editor_height: NUMBER_OR_NUMBER_AS_STRING,
	wysiwyg_media_buttons: PropTypes.string,
	wysiwyg_oembed: PropTypes.string,
	wysiwyg_repeatable: BOOLEAN_ALL_TYPES,
	wysiwyg_wpautop: PropTypes.string,
	wysiwyg_wptexturize: BOOLEAN_ALL_TYPES,
};

export const FIELD_PROP_TYPE_SHAPE = PropTypes.exact( FIELD_PROP_TYPE );

export const GROUP_PROP_TYPE_SHAPE = PropTypes.shape( {
	description: PropTypes.string,
	fields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ),
	id: NUMBER_OR_NUMBER_AS_STRING.isRequired,
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	object_type: PropTypes.string,
	parent: NUMBER_OR_NUMBER_AS_STRING.isRequired,
	storage_type: PropTypes.string,
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
