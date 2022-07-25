<?php
/**
 * An extension of the base collection implementation to handle posts.
 *
 * @since 4.9.5
 */

use Tribe\Traits\With_Post_Attribute_Detection;

/**
 * Class Tribe__Utils__Post_Collection
 *
 * @since 4.9.5
 */
class Tribe__Utils__Post_Collection extends Tribe__Utils__Collection {
	use With_Post_Attribute_Detection;

	/**
	 * A list of the taxonomies supported by the post types in the collection.
	 *
	 * @since 4.12.6
	 *
	 * @var array<string>
	 */
	protected $taxonomies;

	/**
	 * Tribe__Utils__Post_Collection constructor.
	 *
	 * Overrides the base constructor to ensure all elements in the collection are, in fact, posts.
	 * Elements that do not resolve to a post are discarded.
	 *
	 * @param array $items
	 */
	public function __construct( array $items ) {
		parent::__construct( array_filter( array_map( 'get_post', $items ) ) );
	}

	/**
	 * Plucks fields from the posts in the collection creating a map using a field value as key and one
	 * or more fields as values.
	 *
	 * Note: the method does not make any check on the uniqueness of the fields used as keys, e.g. this will
	 * probably not return what intended: `$collection->pluck_combine( 'post_status', 'post_title' );`.
	 * If there's a chance of the key fields not being unique, then use `#` as key field to simply return an
	 * array of plucked values.
	 *
	 * @since 4.12.6
	 *
	 * @param string                            $key_field    The field to key the return map by, or `#` to use
	 *                                                        progressive integers to key the return value. Use fields
	 *                                                        as keys only when their uniqueness is sure.
	 * @param string|array<string>|array<array> $value_fields Either a single field name to populate the values with;
	 *                                                        a list of fields, each plucked with default settings;
	 *                                                        a map of fields to fetch, each defining a `single` and
	 *                                                        `args` key to define the pluck `$single` and `$args`
	 *                                                        parameters where applicable.
	 *                                                        Additionally an `as` parameter can be specified to alias
	 *                                                        the field in the results.
	 *                                                        If the only requirement is to alias fields, just use a
	 *                                                        flat map like `[ <orig_key_1> => <alias_1>, ... ]`.
	 *
	 * @return array<int|string,string|array> A list of plucked fields or a map of plucked fields keyed by the
	 *                                        specified field.
	 */
	public function pluck_combine( $key_field = '#', $value_fields = 'post_title' ) {
		$value_req_is_array = is_array( $value_fields );
		$value_fields       = (array) $value_fields;
		$rows               = [];
		$field_names        = [];
		$field_index        = 0;
		foreach ( $value_fields as $k => $field ) {
			if ( is_string( $k ) && is_string( $field ) ) {
				$single     = true;
				$args       = [];
				$field_name = $field;
				$pluck      = $k;
			} else {
				list( $as, $single, $args ) = $this->parse_field_args( $field );
				$field      = is_array( $field ) ? $k : $field;
				$field_name = null === $as ? $field : $as;
				$pluck      = $field;
			}
			$field_names[ $field_index ] = $field_name;
			$rows[ $field_name ]         = $this->pluck( $pluck, $single, $args );
			$field_index ++;
		}
		$values = [];

		// Build a list with only numeric keys and string values.
		$fields_list = array_replace(
			array_filter(
				array_filter( $value_fields, 'is_string' ),
				'is_numeric',
				ARRAY_FILTER_USE_KEY
			),
			$field_names
		);

		for ( $i = 0, $count = count( $this->items ); $i < $count; $i ++ ) {
			$values[ $i ] = array_combine( $fields_list, array_column( $rows, $i ) );
		}

		if ( ! $value_req_is_array ) {
			$values = array_column( $values, reset( $fields_list ) );
		}

		// If the key field is `#` then use a progressive number as key, else use the specified field.
		$keys = '#' === $key_field
			? range( 0, count( $this->items ) - 1 )
			: $this->pluck( $key_field, true );

		return array_combine( $keys, $values );
	}

	/**
	 * Parses a single field request to extract the `$single` and `$args` parameters from it.
	 *
	 * @since 4.12.6
	 *
	 * @param string|array<string,string|array> $field The field name or the field arguments map.
	 *
	 * @return array<string,string,array> The `$as`, `$single` and `$args` parameters extracted from the field.
	 */
	protected function parse_field_args( $field ) {
		$field = (array) $field;

		$as     = isset( $field['as'] )
			? (string) $field['as']
			: null;
		$single = isset( $field['single'] )
			? (bool) $field['single']
			: true;
		$args   = isset( $field['args'] )
			? (array) $field['args']
			: null;

		return [ $as, $single, $args ];
	}

	/**
	 * Plucks a post field, a taxonomy or a custom field from the collection.
	 *
	 * @since 4.12.6
	 *
	 * @param string $key      The name of the field to pluck; the method will try to detect the type of field
	 *                         from its name. If any issues might arise due to fields of different types with the
	 *                         same name, then use the `pluck_<type>` methods directly.
	 * @param bool   $single   Whether to pluck a single taxonomy term or custom fields or an array of all the taxonomy
	 *                         terms or custom fields for each post.
	 * @param array  $args     A list of n optional arguments that will be passed down to the `pluck_<type>` methods.
	 *                         Currently only the the `pluck_taxonomy` will support one more argument to define the
	 *                         query arguments for the term query.
	 *
	 * @return array<string>|array<array> Either an array of plucked fields when plucking post fields or single
	 *                                    custom fields or taxonomy terms, or an array of arrays, each one a list
	 *                                    of all the taxonomy terms or custom fields entries for each post.
	 */
	public function pluck( $key, $single = true, array $args = null ) {
		$type = $this->detect_field_type( $key );

		switch ( $type ) {
			case 'post_field':
				return $this->pluck_field( $key );
				break;
			case 'taxonomy':
				return $this->pluck_taxonomy( $key, $single, $args );
				break;
			default:
				return $this->pluck_meta( $key, $single );
				break;
		}
	}

	/**
	 * Detects the type of a post field from its name.
	 *
	 * @since 4.12.6
	 *
	 * @param string $key The name of the field to check.
	 *
	 * @return string The type of field detected for the key, either `post_field`, `taxonomy` or `custom_field`.
	 */
	protected function detect_field_type( $key ) {
		if ( $this->is_a_post_field( $key ) ) {
			return 'post_field';
		}

		// Init taxonomies as late as possible and only once.
		$this->init_taxonomies();

		if ( $this->is_a_taxonomy( $key ) ) {
			return 'taxonomy';
		}

		return 'custom_field';
	}

	/**
	 * Initialize the post collection taxonomies by filling up the `$taxonomies` property.
	 *
	 * Note the collection will use the first post in the collection to fill the taxonomies array,
	 * this assumes the collection is homogeneous in its post types.
	 *
	 * @since 4.12.6
	 */
	protected function init_taxonomies() {
		if ( ! empty( $this->taxonomies ) ) {
			// Already set up, return.
			return;
		}

		if ( empty( $this->items ) ) {
			// We cannot detect taxonomies from an empty list of items.
			$this->taxonomies = [];

			return;
		}

		// Use the first post to detect the taxonomies.
		$this->taxonomies = get_object_taxonomies( reset( $this->items ), 'names' );
	}

	/**
	 * Plucks a post field from all posts in the collection.
	 *
	 * Note: there is no check on the name of the plucked post field: if a non-existing post field is requested, then
	 * the method will return an empty array.
	 *
	 * @since 4.12.6
	 *
	 * @param string $field The name of the post field to pluck.
	 *
	 * @return array<string> A list of the plucked post fields from each item in the collection.
	 */
	public function pluck_field( $field ) {
		return wp_list_pluck( $this->items, $field );
	}

	/**
	 * Plucks taxonomy terms assigned to the posts in the collection.
	 *
	 * Note: there is no check on the taxonomy being an existing one or not; that responsibility
	 * is on the user code.
	 *
	 * @since 4.12.6
	 *
	 * @param string                     $taxonomy The name of the post taxonomy to pluck terms for.
	 * @param bool                       $single   Whether to return only the first results or all of them.
	 * @param array<string,string|array> $args     A set of arguments as supported by the `WP_Term_Query::__construct`
	 *                                             method.
	 *
	 * @return array<mixed>|array<array> Either an array of the requested results if `$single` is `true`
	 *                                   or an array of arrays if `$single` is `false`.
	 */
	public function pluck_taxonomy( $taxonomy, $single = true, array $args = null ) {
		$plucked = [];
		$args    = null === $args ? [ 'fields' => 'names' ] : $args;

		foreach ( $this as $item ) {
			$terms     = wp_get_object_terms( $item->ID, $taxonomy, $args );
			$plucked[] = $single ? reset( $terms ) : $terms;
		}

		return $plucked;
	}

	/**
	 * Plucks a meta key for all elements in the collection.
	 *
	 * Elements that are not posts or do not have the meta set will have an
	 * empty string value.
	 *
	 * @since 4.9.5
	 *
	 * @param string $meta_key The meta key to pluck.
	 * @param bool   $single   Whether to fetch the meta key as single or not.
	 *
	 * @return array An array of meta values for each item in the collection; items that
	 *               do not have the meta set or that are not posts, will have an empty
	 *               string value.
	 */
	public function pluck_meta( $meta_key, $single = true ) {
		$plucked = [];

		foreach ( $this as $item ) {
			$plucked[] = get_post_meta( $item->ID, $meta_key, $single );
		}

		return $plucked;
	}
}
