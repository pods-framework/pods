<?php
/**
 * Defines the locations the `Tribe__Context` class should look up.
 *
 * The location definitions are moved here to avoid burdening the `Tribe__Context` class with a long array definition
 * that would be loaded upfront every time the `Tribe__Context` class file is loaded. Since locations will be required
 * only when the Context is built moving them here is a small optimization.
 * This file is meant to be included by the `Tribe__Context::populate_locations` method.
 *
 * @since 4.9.11
 */
return [
	'post_id' => [
		'read' => [
			Tribe__Context::FUNC => static function () {
				return get_the_ID();
			}
		],
	],
	'permalink_structure' => [
		'read' => [
			Tribe__Context::OPTION => [ 'permalink_structure' ],
		],
	],
	'plain_permalink' => [
		'read' => [
			Tribe__Context::LOCATION_FUNC => [
				'permalink_structure',
				static function( $struct ){
					return empty( $struct );
				},
			],
		],
	],
	'posts_per_page' => [
		'read'  => [
			Tribe__Context::REQUEST_VAR  => 'posts_per_page',
			Tribe__Context::OPTION       => 'posts_per_page',
			Tribe__Context::TRIBE_OPTION => [ 'posts_per_page', 'postsPerPage' ],
		],
		'write' => [
			Tribe__Context::REQUEST_VAR => 'posts_per_page',
		],
	],
	'is_main_query'  => [
		'read'  => [
			Tribe__Context::FUNC => static function () {
				global $wp_query;

				return $wp_query->is_main_query();
			},
		],
		'write' => [
			Tribe__Context::FUNC => static function () {
				global $wp_query, $wp_the_query;
				$wp_the_query = $wp_query;
			},
		],
	],
	'paged'          => [
		'read'  => [
			Tribe__Context::REQUEST_VAR => [ 'paged', 'page' ],
			Tribe__Context::QUERY_VAR   => [ 'paged', 'page' ],
		],
		'write' => [
			Tribe__Context::REQUEST_VAR => 'paged',
			Tribe__Context::QUERY_VAR   => 'paged',
		],
	],
	'page'           => [
		'read'  => [
			Tribe__Context::REQUEST_VAR => [ 'page', 'paged' ],
			Tribe__Context::QUERY_VAR   => [ 'page', 'paged' ],
		],
		'write' => [
			Tribe__Context::REQUEST_VAR => 'page',
			Tribe__Context::QUERY_VAR   => 'page',
		],
	],
	'name'           => [
		'read'  => [
			Tribe__Context::REQUEST_VAR => [ 'name', 'post_name' ],
			Tribe__Context::WP_PARSED   => [ 'name', 'post_name' ],
			Tribe__Context::QUERY_VAR   => [ 'name', 'post_name' ],
		],
		'write' => [
			Tribe__Context::REQUEST_VAR => [ 'name', 'post_name' ],
			Tribe__Context::QUERY_VAR   => [ 'name', 'post_name' ],
		],
	],
	'post_type' => [
		'read' => [
			Tribe__Context::FUNC        => static function() {
				$post_type_objs = get_post_types(
					[
						'public' => true,
						'_builtin' => false,
					],
					'objects'
				);

				foreach( $post_type_objs as $post_type ) {
					if ( empty( $post_type->query_var ) ) {
						continue;
					}

					$url_value = tribe_get_request_var( $post_type->query_var, false );
					if ( empty( $url_value ) ) {
						continue;
					}

					return $post_type->name;
				}

				return Tribe__Context::NOT_FOUND;
			},
			Tribe__Context::QUERY_PROP  => 'post_type',
			Tribe__Context::QUERY_VAR   => 'post_type',
			Tribe__Context::REQUEST_VAR => 'post_type',
		],
	],
	'single' => [
		'read' => [ Tribe__Context::QUERY_METHOD => 'is_single' ]
	],
	'taxonomy' => [
		'read' => [
			Tribe__Context::QUERY_PROP  => [ 'taxonomy' ],
			Tribe__Context::QUERY_VAR   => [ 'taxonomy' ],
			Tribe__Context::REQUEST_VAR => [ 'taxonomy' ],
		],
	],
	'post_tag' => [
		'read' => [
			Tribe__Context::QUERY_PROP  => [ 'post_tag', 'tag' ],
			Tribe__Context::QUERY_VAR   => [ 'post_tag', 'tag' ],
			Tribe__Context::REQUEST_VAR => [ 'post_tag', 'tag' ],
		],
	],
	'bulk_edit' => [
		'read' => [
			Tribe__Context::REQUEST_VAR => [ 'bulk_edit' ],
		],
	],
	'inline_save' => [
		'read' => [
			Tribe__Context::FUNC => [
				static function () {
					return tribe_get_request_var( 'action', false ) === 'inline-save'
						? true
						: Tribe__Context::NOT_FOUND;
				}
			],
		],
	],
];
