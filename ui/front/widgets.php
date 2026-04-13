<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

echo wp_kses_post( $before_widget );

if ( ! empty( $title ) ) {
	echo wp_kses_post( $before_title . $title . $after_title );
}

if ( ! empty( $before_content ) ) {
	echo wp_kses_post( $before_content );
}

echo pods_shortcode( $args, ( isset( $content ) ? $content : null ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( ! empty( $after_content ) ) {
	echo wp_kses_post( $after_content );
}

echo wp_kses_post( $after_widget );
