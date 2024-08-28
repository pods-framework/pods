<?php

namespace Pods\Integrations\Query_Monitor\Outputters;

use QM_Collector;
use QM_Output_Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Constants
 *
 * @since 3.2.7
 */
class Constants extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );

		add_filter( 'qm/output/menus', [ $this, 'admin_menu' ], 999 );
	}

	public function name(): string {
		return __( 'Pods', 'pods' );
	}

	public function output(): void {
		$data = $this->collector->get_data();

		$this->before_tabular_output();
		?>
		<thead>
		<tr>
			<th><?php esc_html_e( 'Constant Name', 'pods' ); ?></th>
			<th><?php esc_html_e( 'Constant Value', 'pods' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( ! empty( $data['constants'] ) ) {
			foreach ( $data['constants'] as $key => $value ) {
				?>
				<tr>
					<th><?php echo esc_html( $key ); ?></th>
					<td><?php echo esc_html( $value ); ?></td>
				</tr>
				<?php
			}
		} else {
			?>
			<tr>
				<td colspan="2" style="text-align:center !important;"><em>none</em></td>
			</tr>
			<?php
		}
		?>
		</tbody>
		<tfoot>
		<tr class="qm-items-shown qm-hide">
			<td><?php esc_html_e( 'Constant Name', 'pods' ); ?></td>
			<td><?php esc_html_e( 'Constant Value', 'pods' ); ?></td>
		</tr>
		</tfoot>
		<?php
		$this->after_tabular_output();
	}
}
