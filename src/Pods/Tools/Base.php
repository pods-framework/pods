<?php

namespace Pods\Tools;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

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
	 * The list of conflicts with other plugins detected while running the tool.
	 *
	 * @since 3.4.0
	 *
	 * @var string[]
	 */
	protected $conflicts = [];

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
	 * Determine whether another plugin was detected to be conflicting with the tool.
	 *
	 * @since 3.4.0
	 *
	 * @return bool Whether another plugin was detected to be conflicting with the tool.
	 */
	public function has_conflicts() {
		return ! empty( $this->conflicts );
	}

	/**
	 * Get the conflicts with other plugins detected while running the tool.
	 *
	 * @since 3.4.0
	 *
	 * @return string[] The conflicts with other plugins detected while running the tool.
	 */
	public function get_conflicts() {
		return $this->conflicts;
	}

	/**
	 * Get the results for a tool that was stopped because another plugin was conflicting with it.
	 *
	 * @since 3.4.0
	 *
	 * @param string      $tool_heading The tool heading text.
	 * @param array       $results      The results from the work that completed before the tool was stopped.
	 * @param null|string $mode         The tool mode.
	 *
	 * @return array The results with information about why the tool was stopped.
	 */
	protected function get_conflict_results( $tool_heading, array $results, $mode = null ) {
		$conflict_heading = __( 'Stopped: another plugin is conflicting with Pods', 'pods' );

		$results = array_merge(
			[
				$conflict_heading => array_merge(
					[
						__( 'This tool was stopped before making any further changes because the configurations returned by Pods did not match the configurations found with a direct database query.', 'pods' ),
						__( 'That normally means another plugin (or theme) is filtering post queries, meta queries, or caching in a way that would make this tool repair the wrong things and break your configuration.', 'pods' ),
						__( 'To fix this: deactivate your other plugins, then flush the Pods cache from Pods Admin > Settings > Tools (or run "wp pods tools flush-cache"), and clear any caching plugin and persistent object cache. Flushing the Pods cache matters even after the other plugin is gone, because Pods stores what it read for up to a week.', 'pods' ),
						__( 'Then run this tool again. If the problem continues, please report the details below to the Pods support team.', 'pods' ),
					],
					$this->conflicts
				),
			],
			$results
		);

		$results['message_html'] = $this->get_message_html( $tool_heading, $results, $mode );
		$results['conflicts']    = $this->conflicts;

		return $results;
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

		$has_errors    = ! empty( $this->errors );
		$has_conflicts = ! empty( $this->conflicts );

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
			if ( $has_conflicts ) {
				WP_CLI::error( __( 'This tool was stopped because another plugin is conflicting with the queries Pods relies on', 'pods' ) );
			}

			if ( $has_errors ) {
				WP_CLI::error( __( 'This tool was unable to complete', 'pods' ) );
			}

			return '';
		}

		return wpautop( implode( "\n\n", $messages ) );
	}

}
