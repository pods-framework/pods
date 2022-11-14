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
	 * @var array
	 */
	protected static $compatible_types = [
		'file' => 'file',
	];

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'File', 'pods' );
	}

}
