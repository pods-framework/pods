<?php
/**
 * Name: Migrate: Pod Page and Pod Template PHP into File-based templates
 *
 * Menu Name: Migrate PHP Templates
 *
 * Description: Pod Pages and Pod Templates will be migrated into their corresponding file-based locations in the theme. This will overwrite existing files and this is one-way. After migrating the files it will clear the PHP code from the DB content. <a href="https://docs.pods.io/displaying-pods/pod-page-template-hierarchy-for-themes/">More information about Pod Page template hierarchy</a> | <a href="https://docs.pods.io/displaying-pods/pod-template-hierarchy-for-themes/">More information about Pod Template hierarchy</a>
 *
 * Category: Migration
 *
 * Version: 1.0
 *
 * Plugin: pods-migrate-php/pods-migrate-php.php
 *
 * @package    Pods\Components
 * @subpackage Migrate-PHP
 */

use Pods\Whatsit;
use Pods\Whatsit\Page;
use Pods\Whatsit\Template;

if ( class_exists( 'Pods_Migrate_PHP' ) ) {
	return;
}

/**
 * Class Pods_Migrate_PHP
 */
class Pods_Migrate_PHP extends PodsComponent {

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// Nothing to do here.
	}

	/**
	 * Enqueue styles
	 *
	 * @since 2.0.0
	 */
	public function admin_assets() {
		wp_enqueue_style( 'pods-wizard' );
	}

	/**
	 * Show the Admin
	 *
	 * @param $options
	 * @param $component
	 */
	public function admin( $options, $component ) {
		$method = 'migrate';

		$pod_templates = [];
		$pod_pages     = [];

		$api = pods_api();

		if ( class_exists( 'Pods_Templates' ) ) {
			$pod_templates = array_filter(
				$api->load_templates(),
				static function( $object ) {
					if ( ! $object instanceof Whatsit ) {
						return false;
					}

					return ! empty( $object->get_id() ) && false !== strpos( $object->get_description(), '<?' );
				}
			);
		}

		if ( class_exists( 'Pods_Pages' ) ) {
			$pod_pages = array_filter(
				$api->load_pages(),
				static function( $object ) {
					if ( ! $object instanceof Whatsit ) {
						return false;
					}

					return (
						! empty( $object->get_id() )
						&& (
							false !== strpos( $object->get_description(), '<?' )
							|| false !== strpos( (string) $object->get_arg( 'precode' ), '<?' )
						)
					);
				}
			);
		}

		// ajax_migrate
		pods_view( __DIR__ . '/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Handle the Migration AJAX
	 *
	 * @param $params
	 */
	public function ajax_migrate( $params ) {
		WP_Filesystem();

		$cleanup = 1 === (int) pods_v( 'cleanup', $params, 0 );

		$pod_templates = [];
		$pod_pages     = [];

		if ( isset( $params->templates ) && ! empty( $params->templates ) ) {
			foreach ( $params->templates as $object_id => $checked ) {
				if ( true === (boolean) $checked ) {
					$pod_templates[] = $object_id;
				}
			}
		}

		if ( isset( $params->pages ) && ! empty( $params->pages ) ) {
			foreach ( $params->pages as $object_id => $checked ) {
				if ( true === (boolean) $checked ) {
					$pod_pages[] = $object_id;
				}
			}
		}

		$pod_templates_file_paths = [];
		$pod_pages_file_paths     = [];

		foreach ( $pod_templates as $object_id ) {
			$pod_templates_file_paths[] = $this->migrate_template( $object_id, $cleanup );
		}

		foreach ( $pod_pages as $object_id ) {
			$pod_pages_file_paths[] = $this->migrate_page( $object_id, $cleanup );
		}

		$content = '<div class="pods-wizard-content">' . "\n";

		$content .= '<p>' . esc_html__( 'Migration Complete! The following paths were saved:', 'pods' ) . '</p>' . "\n";

		if ( ! empty( $pod_templates_file_paths ) ) {
			$content .= '<h4>' . esc_html__( 'Pod Templates saved', 'pods' ) . '</h4>' . "\n";
			$content .= '<ul class="normal">' . "\n";

			foreach ( $pod_templates_file_paths as $file_path ) {
				$content .= '<li>' . esc_html( $file_path ) . '</li>' . "\n";
			}

			$content .= '</ul>' . "\n";
		}

		if ( ! empty( $pod_pages_file_paths ) ) {
			$content .= '<h4>' . esc_html__( 'Pod Pages saved', 'pods' ) . '</h4>' . "\n";
			$content .= '<ul class="normal">' . "\n";

			foreach ( $pod_pages_file_paths as $file_path ) {
				$content .= '<li>' . esc_html( $file_path ) . '</li>' . "\n";
			}

			$content .= '</ul>' . "\n";
		}

		if ( $cleanup ) {
			$content .= '<p>' . esc_html__( 'The Pod Page(s) and/or Pod Template(s) were cleaned up and will now load directly from the theme files.', 'pods' ) . '</p>' . "\n";
		} else {
			$content .= '<p>' . esc_html__( 'The Pod Page(s) and/or Pod Template(s) were not modified. You will need to empty the content on them before they will load from the theme files.', 'pods' ) . '</p>' . "\n";
		}

		return $content;
	}

	private function setup_file_path( $file_path ) {
		/**
		 * @var $wp_filesystem WP_Filesystem_Base
		 */
		global $wp_filesystem;

		if ( ! $wp_filesystem->is_dir( dirname( $file_path ) ) ) {
			$pods_path = trailingslashit( get_stylesheet_directory() ) . 'pods';

			if ( ! $wp_filesystem->is_dir( $pods_path ) && ! $wp_filesystem->mkdir( $pods_path, FS_CHMOD_DIR ) ) {
				// translators: %s is the directory path.
				pods_error( sprintf( esc_html__( 'Unable to create the directory: %s', 'pods' ), $pods_path ) );
			}

			$grandparent_path = dirname( dirname( $file_path ) );

			if ( $pods_path !== $grandparent_path && ! $wp_filesystem->is_dir( $grandparent_path ) && ! $wp_filesystem->mkdir( $grandparent_path, FS_CHMOD_DIR ) ) {
				// translators: %s is the directory path.
				pods_error( sprintf( esc_html__( 'Unable to create the directory: %s', 'pods' ), $grandparent_path ) );
			}

			if ( ! $wp_filesystem->mkdir( dirname( $file_path ), FS_CHMOD_DIR ) ) {
				// translators: %s is the directory path.
				pods_error( sprintf( esc_html__( 'Unable to create the directory: %s', 'pods' ), $file_path ) );
			}
		} elseif ( ! $wp_filesystem->is_writable( dirname( $file_path ) ) ) {
			// translators: %s is the directory path.
			pods_error( sprintf( esc_html__( 'Unable to write to the directory: %s', 'pods' ), $file_path ) );
		}
	}

	private function migrate_template( $object_id, bool $cleanup ) {
		/**
		 * @var $wp_filesystem WP_Filesystem_Base
		 */
		global $wp_filesystem;

		$api = pods_api();

		/** @var Template $object */
		$object = $api->load_template( [ 'id' => $object_id ] );

		if ( ! $object ) {
			// translators: %s is the object ID.
			pods_error( sprintf( esc_html__( 'Unable to find the Pod Template by ID: %s', 'pods' ), $object_id ) );
		}

		$files = Pods_Templates::get_templates_for_pod_template( $object );

		if ( count( $files ) < 2 ) {
			// translators: %s is the file paths found.
			pods_error( sprintf( esc_html__( 'Unable to detect the file path: %s', 'pods' ), json_encode( $files, JSON_PRETTY_PRINT ) ) );
		}

		$file_path = trailingslashit( get_stylesheet_directory() ) . array_shift( $files );

		$this->setup_file_path( $file_path );

		$contents = <<<PHPTEMPLATE
<?php
/**
 * Pod Template: {$object->get_label()}
 *
 * @var Pods \$obj
 */
?>

{$object->get_description()}
PHPTEMPLATE;

		if ( ! $wp_filesystem->put_contents( $file_path, $contents, FS_CHMOD_FILE ) ) {
			// translators: %s is the file path.
			pods_error( sprintf( esc_html__( 'Unable to write to the file: %s', 'pods' ), $file_path ) );
		}

		if ( $cleanup ) {
			$api->save_template( [
				'id'   => $object->get_id(),
				'code' => '',
			] );
		}

		return str_replace( ABSPATH, '', $file_path );
	}

	private function migrate_page( $object_id, bool $cleanup ) {
		/**
		 * @var $wp_filesystem WP_Filesystem_Base
		 */
		global $wp_filesystem;

		$api = pods_api();

		/** @var Page $object */
		$object = $api->load_page( [ 'id' => $object_id ] );

		if ( ! $object ) {
			// translators: %s is the object ID.
			pods_error( sprintf( esc_html__( 'Unable to find the Pod Page by ID: %s', 'pods' ), $object_id ) );
		}

		$files = Pods_Pages::get_templates_for_pod_page( $object );

		if ( count( $files ) < 2 ) {
			// translators: %s is the file paths found.
			pods_error( sprintf( esc_html__( 'Unable to detect the file path: %s', 'pods' ), json_encode( $files, JSON_PRETTY_PRINT ) ) );
		}

		$file_path = trailingslashit( get_stylesheet_directory() ) . array_shift( $files );

		$this->setup_file_path( $file_path );

		$precode = (string) $object->get_arg( 'precode' );

		if ( false !== strpos( $precode, '<?' ) && false === strpos( $precode, '?>' ) ) {
			$precode .= "\n?>";
		}

		$precode_template = '';

		if ( ! empty( $precode ) ) {
			$precode_template = <<<PHPTEMPLATE

<?php
/*
 * Precode goes below.
 */
?>
{$precode}

PHPTEMPLATE;
		}

		$contents = <<<PHPTEMPLATE
<?php
/**
 * Pod Page Template: {$object->get_label()}
 *
 * @var Pods \$pods
 */

{$precode_template}

?>
{$object->get_description()}
PHPTEMPLATE;

		if ( ! $wp_filesystem->put_contents( $file_path, $contents, FS_CHMOD_FILE ) ) {
			// translators: %s is the file path.
			pods_error( sprintf( esc_html__( 'Unable to write to the file: %s', 'pods' ), $file_path ) );
		}

		if ( $cleanup ) {
			$api->save_page( [
				'id'      => $object->get_id(),
				'name'    => $object->get_label(),
				'code'    => '',
				'precode' => '',
			] );
		}

		return str_replace( ABSPATH, '', $file_path );
	}

}
