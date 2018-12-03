<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Page class.
 *
 * @since 2.8
 */
class Page extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public function get_arg( $arg, $default = null ) {
		$old_mapping = array(
			'name' => 'label',
			'slug' => 'name',
			'code' => 'description',
		);

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
