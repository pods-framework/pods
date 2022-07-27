<?php

namespace Pods\Whatsit\Storage;

/**
 * File class.
 *
 * @since 2.9.0
 */
class File extends Collection {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'file';

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'File', 'pods' );
	}

}
