<?php

/**
 * Pods__Object__Page class.
 *
 * @since 2.8
 */
class Pods__Object__Page extends Pods__Object {

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
