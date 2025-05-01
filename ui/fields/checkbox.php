<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$options['data'] = (array) pods_v( 'data', $options, [] );

$data_count = count( $options['data'] );

if ( 0 < $data_count ) {

	if ( 1 === (int) pods_v( 'grouped', $options ) ) {
		?>
		<div class="pods-pick-values pods-pick-checkbox">
		<ul>
		<?php
	}

	$counter      = 1;
	$primary_name = $name;
	$primary_id   = 'pods-form-ui-' . PodsForm::clean( $name );

	foreach ( $options['data'] as $val => $label ) {
		if ( is_array( $label ) ) {
			if ( isset( $label['label'] ) ) {
				$label = $label['label'];
			} else {
				$label = $val;
			}
		}

		$attributes = array();

		$attributes['type']     = 'checkbox';
		$attributes['tabindex'] = 2;

		if ( ( ! is_array( $value ) && (string) $val === (string) $value ) || ( is_array( $value ) && ( in_array( $val, $value ) || in_array( (string) $val, $value ) ) ) ) {
			$attributes['checked'] = 'CHECKED';
		}

		$attributes['value'] = $val;

		if ( 1 < $data_count && false === strpos( $primary_name, '[]' ) ) {
			$name = $primary_name . '[' . ( $counter - 1 ) . ']';
		}

		$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

		$indent = '';

		$indent_count = substr_count( $label, '&nbsp;&nbsp;&nbsp;' );

		if ( 0 < $indent_count ) {
			$label = str_replace( '&nbsp;&nbsp;&nbsp;', '', $label );

			$indent = ' style="margin-left:' . ( 18 * $indent_count ) . 'px;"';
		}

		if ( 1 < $data_count && false === strpos( $primary_name, '[]' ) ) {
			$attributes['class'] .= ' pods-dependent-multi';
		}

		if ( strlen( $label ) < 1 ) {
			$attributes['class'] .= ' pods-form-ui-no-label';
		}

		if ( (bool) pods_v( 'readonly', $options, false ) ) {
			$attributes['readonly'] = 'READONLY';
			$attributes['disabled'] = 'DISABLED';

			$attributes['class'] .= ' pods-form-ui-read-only';
		}

		if ( 1 < $data_count ) {
			$attributes['id'] = $primary_id . $counter;
		}

		if ( 1 === (int) pods_v( 'grouped', $options ) ) {
			?>
			<li>
			<?php
		}
		?>
		<div class="pods-field pods-boolean"<?php echo $indent; ?>>
			<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
			<?php
			if ( isset( $attributes['readonly'] ) && isset( $attributes['checked'] ) && 'CHECKED' === $attributes['checked'] ) {
				?>
				<input type="hidden" name="<?php echo esc_attr( pods_js_name( $name ) ); ?>" value="<?php echo esc_attr( $attributes['value'] ); ?>" />
				<?php
			}

			if ( 0 < strlen( $label ) ) {
				$help = pods_v( 'help', $options );

				if ( 1 === (int) pods_v( 'grouped', $options ) || empty( $help ) ) {
					$help = '';
				}

				echo PodsForm::label( $attributes['id'], $label, $help );
			}
			?>
		</div>
		<?php

		if ( 1 === (int) pods_v( 'grouped', $options ) ) {
			?>
			</li>
			<?php
		}

		$counter ++;
	}//end foreach

	if ( 1 === (int) pods_v( 'grouped', $options ) ) {
		?>
		</ul>
		</div>
		<?php
	}
}//end if
