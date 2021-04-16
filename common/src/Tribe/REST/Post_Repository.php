<?php
class Tribe__REST__Post_Repository {

	/**
	 * Returns the data representing an image object.
	 *
	 * @since 4.7.19
	 *
	 * @param int $image_id
	 *
	 * @return array|false An array of image information or `false` on failure.
	 */
	protected function get_image_data( $image_id ) {
		$full_url = get_attachment_link( $image_id );
		$file     = get_attached_file( $image_id );

		$data = array(
			'url'       => $full_url,
			'id'        => $image_id,
			'extension' => pathinfo( $file, PATHINFO_EXTENSION ),
		);

		$metadata = wp_get_attachment_metadata( $image_id );

		if (
			false !== $metadata
			&& isset( $metadata['image_meta'], $metadata['file'], $metadata['sizes'] )
		) {
			unset( $metadata['image_meta'], $metadata['file'] );

			foreach ( $metadata['sizes'] as $size => &$meta ) {
				$size_image_src = wp_get_attachment_image_src( $image_id, $size );
				$meta['url']    = ! empty( $size_image_src[0] ) ? $size_image_src[0] : '';
				unset( $meta['file'] );
			}
			unset( $meta );

			$data = array_filter( array_merge( $data, $metadata ) );
		}

		return $data;
	}

	/**
	 * @param string $date A date string in a format `strtotime` can parse.
	 *
	 * @return array An array of date details for the end date; each entry will be
	 *               empty if the date is empty.
	 */
	protected function get_date_details( $date ) {
		if ( empty( $date ) ) {
			return array(
				'year'    => '',
				'month'   => '',
				'day'     => '',
				'hour'    => '',
				'minutes' => '',
				'seconds' => '',
			);
		}

		$time = strtotime( $date );

		return array(
			'year'    => date( 'Y', $time ),
			'month'   => date( 'm', $time ),
			'day'     => date( 'd', $time ),
			'hour'    => date( 'H', $time ),
			'minutes' => date( 'i', $time ),
			'seconds' => date( 's', $time ),
		);
	}

	/**
	 * Returns a localized and formatted list of cost values in ASC order.
	 *
	 * @since 4.7.19
	 *
	 * @param array $cost_couples An array of cost couples in the [ <pretty name> => <number value> ] format.
	 *
	 * @return array
	 */
	protected function format_and_sort_cost_couples( array $cost_couples = array() ) {
		global $wp_locale;

		$cost_values = array();
		foreach ( $cost_couples as $key => $value ) {
			$value = str_replace( array(
				$wp_locale->number_format['decimal_point'],
				$wp_locale->number_format['thousands_sep'],
			), array( '.', '' ), '' . $value );
			if ( is_numeric( $value ) ) {
				$cost_values[] = $value;
			} else {
				$cost_values[] = $key;
			}
		}

		sort( $cost_values, SORT_NUMERIC );

		return $cost_values;
	}
}
