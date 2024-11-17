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
	 */
	public function admin_assets() {
		wp_enqueue_style( 'pods-wizard' );
	}

	/**
	 * Get the list of objects that need migration.
	 *
	 * @return array{pod_templates:Template[],pod_pages:Page[]} The list of objects that need migration.
	 */
	public function get_objects_that_need_migration(): array {
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

		// Rekey the objects by ID.
		$pod_templates = array_combine( array_map( static function( $object ) {
			return $object->get_id();
		}, $pod_templates ), $pod_templates );

		$pod_pages = array_combine( array_map( static function( $object ) {
			return $object->get_id();
		}, $pod_pages ), $pod_pages );

		return compact( 'pod_templates', 'pod_pages' );
	}

	/**
	 * Show the Admin
	 *
	 * @param $options
	 * @param $component
	 */
	public function admin( $options, $component ) {
		$method = 'migrate';

		[
			'pod_templates' => $pod_templates,
			'pod_pages'     => $pod_pages,
		] = $this->get_objects_that_need_migration();

		$has_objects_to_migrate = ! empty( $pod_templates ) || ! empty( $pod_pages );

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

		[
			'pod_templates' => $pod_templates_available_to_migrate,
			'pod_pages'     => $pod_pages_available_to_migrate,
		] = $this->get_objects_that_need_migration();

		$objects_can_be_migrated = ! empty( $pod_templates_available_to_migrate ) || ! empty( $pod_pages_available_to_migrate );

		$cleanup = 1 === (int) pods_v( 'cleanup', $params, 0 );

		$pod_templates = [];
		$pod_pages     = [];

		$pod_templates_selected = (array) pods_v( 'templates', $params, [] );
		$pod_templates_selected = array_filter( $pod_templates_selected );

		$pod_pages_selected = (array) pods_v( 'pages', $params, [] );
		$pod_pages_selected = array_filter( $pod_pages_selected );

		$has_objects_to_migrate = ! empty( $pod_templates_selected ) || ! empty( $pod_pages_selected );

		foreach ( $pod_templates_selected as $object_id => $checked ) {
			if ( true === (boolean) $checked && isset( $pod_templates_available_to_migrate[ (int) $object_id ] ) ) {
				$pod_templates[] = $object_id;
			}
		}

		foreach ( $pod_pages_selected as $object_id => $checked ) {
			if ( true === (boolean) $checked && isset( $pod_pages_available_to_migrate[ (int) $object_id ] ) ) {
				$pod_pages[] = $object_id;
			}
		}

		$pod_templates_file_paths = [];
		$pod_pages_file_paths     = [];

		$migrated = false;

		foreach ( $pod_templates as $object_id ) {
			$migrated = true;

			$pod_templates_file_paths[] = $this->migrate_template( $object_id, $cleanup );
		}

		foreach ( $pod_pages as $object_id ) {
			$migrated = true;

			$pod_pages_file_paths[] = $this->migrate_page( $object_id, $cleanup );
		}

		$content = '<div class="pods-wizard-content">' . "\n";

		if ( ! $has_objects_to_migrate ) {
			$content .= '<p>' . esc_html__( 'No Pod Templates or Pod Pages were selected.', 'pods' ) . '</p>' . "\n";
		} elseif ( ! $objects_can_be_migrated ) {
			$content .= '<p>' . esc_html__( 'The selected Pod Templates or Pod Pages are not available for migration. They no longer contain PHP.', 'pods' ) . '</p>' . "\n";
		} elseif ( ! $migrated ) {
			$content .= '<p>' . esc_html__( 'The selected Pod Templates or Pod Pages were not successfully migrated.', 'pods' ) . '</p>' . "\n";
		} else {
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

		$template_code = $object->get_description();

		$extra_headers = '';

		if ( false !== strpos( $template_code, '{@' ) ) {
			$extra_headers = <<<PHPTEMPLATE
 * Magic Tags: Enabled
PHPTEMPLATE;

		}

		$contents = <<<PHPTEMPLATE
<?php
/**
 * Pod Template: {$object->get_label()}{$extra_headers}
 *
 * @var Pods \$obj
 */
?>

{$template_code}
PHPTEMPLATE;

		if ( ! $wp_filesystem->put_contents( $file_path, $contents, FS_CHMOD_FILE ) ) {
			// translators: %s is the file path.
			pods_error( sprintf( esc_html__( 'Unable to write to the file: %s', 'pods' ), $file_path ) );
		}

		if ( $cleanup ) {
			$api->save_template( [
				'id'   => $object->get_id(),
				'name'   => $object->get_label(),
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

		$files             = Pods_Pages::get_templates_for_pod_page( $object );
		$files_for_content = Pods_Pages::get_templates_for_pod_page_content( $object );

		if ( count( $files ) < 2 ) {
			// translators: %s is the file paths found.
			pods_error( sprintf( esc_html__( 'Unable to detect the file path: %s', 'pods' ), json_encode( $files, JSON_PRETTY_PRINT ) ) );
		}

		$file_path             = trailingslashit( get_stylesheet_directory() ) . array_shift( $files );
		$file_path_for_content = trailingslashit( get_stylesheet_directory() ) . array_shift( $files_for_content );

		$this->setup_file_path( $file_path );

		$precode       = (string) $object->get_arg( 'precode' );
		$page_template = (string) $object->get_arg( 'page_template' );

		if ( false !== strpos( $precode, '<?' ) && false === strpos( $precode, '?>' ) ) {
			$precode .= "\n?>";
		}

		$precode_template = '';

		if ( ! empty( $precode ) ) {
			$precode_template = "\n" . <<<PHPTEMPLATE
/*
 * Precode goes below.
 */
?>
{$precode}
PHPTEMPLATE . "\n";
		}

		$template_code = trim( $object->get_description() );

		$has_page_template = ! empty( $page_template );

		$precode_has_end_tag = false !== strpos( $precode, '?>' );

		if ( false === strpos( $template_code, '<?' ) ) {
			$template_code = "?>\n" . $template_code . ( ! $has_page_template ? '' : "\n<?php" );
		} elseif ( ( ! $has_page_template || ! $precode_has_end_tag ) && 0 === strpos( $template_code, '<?php' ) ) {
			$template_code = substr( $template_code, strlen( '<?php' ) );
		} elseif ( ( ! $has_page_template || ! $precode_has_end_tag ) && 0 === strpos( $template_code, '<?' ) ) {
			$template_code = substr( $template_code, strlen( '<?' ) );
		}

		$extra_headers = '';
		$extra_notes   = '';

		if ( ! $has_page_template ) {
			$start_tag = '';

			if ( $precode_has_end_tag ) {
				$start_tag = "\n<?php\n";
			}

			$template_code = $start_tag . <<<PHPTEMPLATE
get_header();

// Pod Page content goes here.
{$template_code}

get_sidebar();
get_footer();
PHPTEMPLATE;
		} else {
			// Set the code and save it for the content path.
			$this->setup_file_path( $file_path_for_content );

			if ( '_custom' !== $page_template && 'blank' !== $page_template ) {
				$extra_notes .= "\n" . <<<PHPTEMPLATE
 *
 * @see {$page_template} for the template where this will get called from.
PHPTEMPLATE;
			}

			// Set the file path we will write to as the one for the content specific template.
			$file_path = $file_path_for_content;
			$extra_notes .= "\n" . <<<PHPTEMPLATE
 *
 * This template is only used for pods_content() calls.
PHPTEMPLATE;
		}

		if ( false !== strpos( $template_code, '{@' ) ) {
			$extra_headers = "\n" . <<<PHPTEMPLATE
 * Magic Tags: Enabled
PHPTEMPLATE;
		}

		$contents = <<<PHPTEMPLATE
<?php
/**
 * Pod Page URI: {$object->get_label()}{$extra_headers}{$extra_notes}
 *
 * @var Pods \$pods
 */

{$precode_template}

{$template_code}
PHPTEMPLATE;

		// Clean up the PHP tags that open and close too often.
		$contents = preg_replace( '/\?>\s*<\?php(\s*)/Umi', '$1', $contents );
		$contents = preg_replace( '/\?>\s*<\?(\s*)/Umi', '$1', $contents );
		$contents = preg_replace( '/\n{3,}/', "\n\n", $contents );

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
