<?php

namespace Pods\Integrations\Query_Monitor\Outputters;

use QM_Collector;
use QM_Collectors;
use QM_Output_Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug
 *
 * @since 3.2.7
 */
class Debug extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );

		add_filter( 'qm/output/menus', [ $this, 'admin_menu' ], 999 );
		add_filter( 'qm/output/panel_menus', [ $this, 'panel_menu' ], 20 );
	}

	public function name(): string {
		return __( 'Pods Debug Log', 'pods' );
	}

	public function output(): void {
		$data = $this->collector->get_data();

		$this->before_tabular_output();

		$functions = array_unique( array_column( $data['debug_data'], 'function', 'function' ) );
		$contexts  = array_unique( array_column( $data['debug_data'], 'context', 'context' ) );

		sort( $functions );
		sort( $contexts );

		$debug_log_types  = [
			'is-json'  => __( 'Log is JSON', 'pods' ),
			'not-json' => __( 'Log is not JSON', 'pods' ),
			'is-sql'   => __( 'Log is SQL query', 'pods' ),
			'not-sql'  => __( 'Log is not SQL query', 'pods' ),
		];
		?>
		<thead>
		<tr>
			<th scope="col" class="qm-nowrap qm-filterable-column">
				<?php echo $this->build_filter( 'function', $functions, __( 'Function/Method', 'pods' ) ); ?>
			</th>
			<th scope="col" class="qm-nowrap">
				<?php esc_html_e( 'Line Number', 'pods' ); ?>
			</th>
			<th scope="col" class="qm-nowrap qm-filterable-column">
				<?php echo $this->build_filter( 'context', $contexts, __( 'Context', 'pods' ) ); ?>
			</th>
			<th scope='col' class='qm-filterable-column'>
				<?php echo $this->build_filter( 'debug-log-type', $debug_log_types, __( 'Debug Log', 'pods' ) ); ?>
			</th>
			<th scope='col'>
				<?php esc_html_e( 'Trace', 'pods' ); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( ! empty( $data['debug_data'] ) ) {
			foreach ( $data['debug_data'] as $debug ) {
				$debug_output = $debug['debug'];

				$has_json = false;
				$is_long  = false;

				if ( ! is_scalar( $debug_output ) ) {
					$debug_output = json_encode( $debug_output, JSON_PRETTY_PRINT );

					$has_json = true;
				} else {
					$debug_output = (string) $debug_output;
					$debug_output = preg_replace( '/\n\n/', "\n", $debug_output );
					$debug_output = preg_replace( '/[\t ]+\n/', ' ', $debug_output );
					$debug_output = ltrim( $debug_output, "\n" );
					$debug_output = rtrim( $debug_output );

					if ( 100 < strlen( $debug_output ) ) {
						$is_long = true;
					}
				}

				$context = $debug['context'];

				$sql_contexts = [
					'sql-select',
					'sql-query',
				];

				$has_toggle = $has_json || $is_long;

				$log_type = [];

				if ( $has_json ) {
					$log_type[] = 'is-json';
				} else {
					$log_type[] = 'not-json';
				}

				if ( in_array( $context, $sql_contexts, true ) ) {
					$log_type[] = 'is-sql';
				} else {
					$log_type[] = 'not-sql';
				}

				$row_attr = [
					'data-qm-function'       => $debug['function'],
					'data-qm-context'        => $debug['context'],
					'data-qm-debug-log-type' => implode( ' ', $log_type ),
				];

				$attr = '';

				foreach ( $row_attr as $a => $v ) {
					$attr .= ' ' . $a . '="' . esc_attr( $v ) . '"';
				}
				?>
				<tr<?php echo $attr; ?>>
					<td class="qm-nowrap qm-ltr">
						<code><?php echo esc_html( $debug['function'] ); ?></code>
					</td>
					<td class="qm-nowrap qm-ltr">
						<?php echo esc_html( $debug['line'] ); ?>
					</td>
					<td class="qm-nowrap qm-ltr">
						<code><?php echo esc_html( $context ); ?></code>
					</td>
					<td class="qm-wrap qm-ltr<?php echo $has_toggle ? ' qm-has-toggle' : ''; ?>">
						<?php
						if ( $has_toggle ) {
							$excerpt = trim( $debug_output );
							$excerpt = preg_replace( '/\t+/', ' ', $excerpt );
							$excerpt = preg_replace( '/\n+/', ' ', $excerpt );
							$excerpt = preg_replace( '/ +/', ' ', $excerpt );
							$excerpt = substr( $excerpt, 0, 100 );

							echo self::build_toggler();
							echo '<div class="qm-inverse-toggled"><pre class="qm-pre-wrap"><code>';
							echo esc_html( $excerpt ) . '&nbsp;&hellip;';
							echo '</code></pre></div>';
							echo '<div class="qm-toggled"><pre class="qm-pre-wrap"><code>';
							echo esc_html( $debug_output );
							echo '</code></pre></div>';
						} else {
							echo '<pre class="qm-pre-wrap"><code>';
							echo esc_html( $debug_output );
							echo '</code></pre>';
						}
						?>
					</td>
					<td class="qm-row-caller qm-row-stack qm-nowrap qm-ltr qm-has-toggle">
						<?php
						$stack = [];

						$filtered_trace = $debug['trace']->get_filtered_trace();
						array_shift( $filtered_trace );

						foreach ( $filtered_trace as $frame ) {
							$stack[] = self::output_filename( $frame['display'], $frame['calling_file'], $frame['calling_line'] );
						}

						echo self::build_toggler();
						echo '<div class="qm-inverse-toggled"><ol>';
						echo '<li>' . reset( $stack ) . '&nbsp;&hellip;</li>';
						echo '</ol></div>';
						echo '<div class="qm-toggled"><ol>';
						echo '<li>' . implode( '</li><li>', $stack ) . '</li>';
						echo '</ol></div>';
						?>
					</td>
				</tr>
				<?php
			}
		} else {
			?>
			<tr>
				<td colspan="5" style="text-align:center !important;"><em>none</em></td>
			</tr>
			<?php
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<?php
				$total_debug_logs = count( $data['debug_data'] );
				?>
				<td colspan="5">
					<?php
					echo sprintf(
						'%s %s',
						number_format_i18n( $total_debug_logs ),
						_n( 'log', 'logs', $total_debug_logs, 'pods' )
					);
					?>
				</td>
			</tr>
		</tfoot>
		<?php
		$this->after_tabular_output();
	}

	/**
	 * Register the panel menu for this outputter.
	 *
	 * @since 3.2.7
	 *
	 * @param array<string, mixed[]> $menus The panel menus.
	 *
	 * @return array<string, mixed[]> The updated panel menus.
	 */
	public function panel_menu( array $menus ) {
		$id = $this->collector->id();

		if ( isset( $menus[ $id ] ) ) {
			/** @var \Pods\Integrations\Query_Monitor\Collectors\Constants|null $constants */
			$constants = QM_Collectors::get( 'pods-constants' );

			if ( $constants ) {
				$menus[ $constants->id() ]['children'][] = $menus[ $id ];
			}

			unset( $menus[ $id ] );
		}

		return $menus;
	}
}
