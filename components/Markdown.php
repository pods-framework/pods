<?php
/**
 * ID: markdown-syntax
 *
 * Name: Markdown Syntax
 *
 * Description: Integration with Markdown (via Parsedown https://github.com/erusev/parsedown); Adds an option to enable Markdown syntax for Paragraph Text and WYSIWYG fields.
 *
 * Version: 2.0
 *
 * Category: Field Types
 *
 * @package    Pods\Components
 * @subpackage Markdown
 */

add_filter( 'pods_form_ui_field_paragraph_display_value_pre_process', 'pods_markdown_maybe_parse_field_display_value', 10, 4 );
add_filter( 'pods_form_ui_field_wysiwyg_display_value_pre_process', 'pods_markdown_maybe_parse_field_display_value', 10, 4 );

/**
 * Maybe parse Markdown for a field display value.
 *
 * @since 3.1.0
 *
 * @param mixed|null      $value   Current value.
 * @param string          $type    Field type.
 * @param string|null     $name    Field name.
 * @param array|null      $options Field options.
 *
 * @return mixed|null The parsed value if markdown is enabled, otherwise the value as it was originally passed.
 */
function pods_markdown_maybe_parse_field_display_value(
	$value,
	$type,
	$name = null,
	$options = null
) {
	if ( ! class_exists( 'Pods__Prefixed__Parsedown' ) || 1 !== (int) pods_v( $type . '_allow_markdown', $options ) ) {
		return $value;
	}

	$parsedown = new Pods__Prefixed__Parsedown();
	$parsedown->setSafeMode( true );

	return $parsedown->text( $value );
}

add_filter( 'pods_admin_setup_edit_paragraph_additional_field_options', 'pods_markdown_maybe_add_field_display_option', 10, 2 );
add_filter( 'pods_admin_setup_edit_wysiwyg_additional_field_options', 'pods_markdown_maybe_add_field_display_option', 10, 2 );

/**
 * Maybe add field display option for Markdown.
 *
 * @since 3.1.0
 *
 * @param array  $type_options The additional field type options.
 * @param string $type         Field type.
 *
 * @return array The additional field type options.
 */
function pods_markdown_maybe_add_field_display_option(
	$type_options,
	$type
) {
	if ( ! class_exists( 'Pods__Prefixed__Parsedown' ) || ! isset( $type_options['output_options']['boolean_group'] ) ) {
		return $type_options;
	}

	$type_options['output_options']['boolean_group'][ $type . '_allow_markdown' ] = [
		'label'   => __( 'Allow Markdown Syntax', 'pods' ),
		'default' => 0,
		'type'    => 'boolean',
	];

	return $type_options;
}
