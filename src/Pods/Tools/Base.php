<?php

namespace Pods\Tools;

use PodsAPI;
use WP_CLI;

/**
 * Base tool functionality.
 *
 * @since 2.9.10
 */
class Base {

	/**
	 * @var PodsAPI
	 */
	protected $api;

	/**
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Setup the tool.
	 *
	 * @since 2.9.10
	 */
	protected function setup() {
		if ( ! $this->api ) {
			$this->api = pods_api();
		}
	}

	/**
	 * Get the message HTML from the results.
	 *
	 * @since 2.9.10
	 *
	 * @param string      $tool_heading The tool heading text.
	 * @param array       $results      The tool results.
	 * @param null|string $mode         The tool mode.
	 *
	 * @return string The message HTML.
	 */
	protected function get_message_html( $tool_heading, array $results, $mode = null ) {
		$using_cli = defined( 'WP_CLI' );

		$messages = [];

		if ( $tool_heading ) {
			if ( $using_cli ) {
				WP_CLI::line( '=== ' . $tool_heading . ' ===' );
			} else {
				$messages[] = sprintf(
					'<h3>%s</h3>',
					$tool_heading
				);
			}
		}

		if ( 'preview' === $mode ) {
			$results = array_merge(
				[
					__( 'Preview Mode Active', 'pods' ) => __( 'These results did not add or change anything in the database.', 'pods' ),
				],
				$results
			);
		}

		$has_errors = ! empty( $this->errors );

		$errors_heading = __( 'Errors', 'pods' );

		if ( $has_errors ) {
			$results[ $errors_heading ] = $this->errors;
		}

		foreach ( $results as $heading => $result_set ) {
			if ( ! is_array( $result_set ) ) {
				$result_set = (array) $result_set;
			}

			if ( empty( $result_set ) ) {
				// Don't output anything if in upgrade mode.
				if ( 'upgrade' === $mode ) {
					continue;
				}

				$result_set[] = __( 'No actions were needed.', 'pods' );
			}

			if ( $using_cli ) {
				if ( $errors_heading === $heading ) {
					WP_CLI::warning( $heading . '...' );

					foreach ( $result_set as $result ) {
						WP_CLI::warning( '- ' . $result );
					}
				} else {
					WP_CLI::line( $heading . '...' );

					foreach ( $result_set as $result ) {
						WP_CLI::line( '- ' . $result );
					}
				}
			} else {
				$messages[] = sprintf(
					'<h4>%1$s</h4><ul class="ul-disc"><li>%2$s</li></ul>',
					esc_html( $heading ),
					implode( '</li><li>', array_map( 'esc_html', $result_set ) )
				);
			}
		}

		$total_messages = count( $messages );

		$total_check = $tool_heading ? 1 : 0;

		if ( $total_messages <= $total_check ) {
			if ( $using_cli ) {
				WP_CLI::warning( __( 'No actions were needed.', 'pods' ) );
			} else {
				// Don't output anything if in upgrade mode.
				if ( 'upgrade' === $mode ) {
					return '';
				}

				$messages[] = esc_html__( 'No actions were needed.', 'pods' );
			}
		}

		if ( $using_cli ) {
			if ( $has_errors ) {
				WP_CLI::error( __( 'This tool was unable to complete', 'pods' ) );
			}

			return '';
		}

		return wpautop( implode( "\n\n", $messages ) );
	}

}
