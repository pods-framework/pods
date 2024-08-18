<?php

namespace Pods\Integrations\Query_Monitor\Outputters;

use QM_Collector;
use QM_Output_Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug
 *
 * @since TBD
 */
class Debug extends QM_Output_Html {

	/**
	 * {@inheritDoc}
	 */
	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );

		add_filter( 'qm/output/menus', [ $this, 'admin_menu' ], 999 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function name(): string {
		return __( 'Pods Debug Log', 'pods' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function output(): void {
		$data = $this->collector->get_data();

		$this->before_tabular_output();

		$functions = array_unique( array_column( $data['debug_data'], 'function', 'function' ) );
		$contexts  = array_unique( array_column( $data['debug_data'], 'context', 'context' ) );

		sort( $functions );
		sort( $contexts );

		$debug_log_types  = [
			'yes' => __( 'Log is JSON', 'pods' ),
			'no'  => __( 'Log is not JSON', 'pods' ),
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
				<?php echo $this->build_filter( 'debug-log-is-json', $debug_log_types, __( 'Debug Log', 'pods' ) ); ?>
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

				$has_toggle = $has_json || $is_long;

				$row_attr = [
					'data-qm-function'          => $debug['function'],
					'data-qm-context'           => $debug['context'],
					'data-qm-debug-log-is-json' => $has_json ? 'yes' : 'no',
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
						<code><?php echo esc_html( $debug['context'] ); ?></code>
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
				</tr>
				<?php
			}
		} else {
			?>
			<tr>
				<td colspan="4" style="text-align:center !important;"><em>none</em></td>
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
				<td colspan="4">
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
}
