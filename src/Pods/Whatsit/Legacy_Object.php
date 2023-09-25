<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Legacy Object class.
 *
 * @since 2.8.0
 */
class Legacy_Object extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	public function get_clean_args() {
		$args = parent::get_clean_args();

		$old_mapping = [
			'name' => 'label',
			'slug' => 'name',
			'code' => 'description',
		];

		$new_args = [];

		foreach ( $old_mapping as $old_arg => $new_arg ) {
			$new_args[ $old_arg ] = '';

			if ( isset( $args[ $new_arg ] ) ) {
				$new_args[ $old_arg ] = $args[ $new_arg ];

				unset( $args[ $new_arg ] );
			}
		}

		return array_merge( $new_args, $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_arg( $arg, $default = null, $strict = false, $raw = false ) {
		if ( $raw ) {
			return parent::get_arg( $arg, $default, $strict, $raw );
		}

		$old_mapping = [
			'name' => 'label',
			'slug' => 'name',
			'code' => 'description',
		];

		if ( isset( $old_mapping[ $arg ] ) ) {
			$arg = $old_mapping[ $arg ];
		}

		return parent::get_arg( $arg, $default );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name() {
		// Map get_name() to the real name, so we have a way to still get to it intentionally.
		if ( isset( $this->args['name'] ) ) {
			return $this->args['name'];
		}

		return null;
	}

}
