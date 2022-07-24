<?php

namespace Pods\Whatsit\Storage;

/**
 * File class.
 *
 * @since TBD
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
