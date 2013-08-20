<?php
$options[ 'data' ] = (array) pods_var_raw( 'data', $options, array(), null, true );

$data_count = count( $options[ 'data' ] );

if ( 0 < $data_count ) {

    if ( 1 == pods_var( 'grouped', $options, 0, null, true ) ) {
        ?>
<div class="pods-pick-values pods-pick-checkbox">
    <ul>
<?php
    }

    $counter = 1;
    $primary_name = $name;
    $primary_id = 'pods-form-ui-' . PodsForm::clean( $name );

    foreach ( $options[ 'data' ] as $val => $label ) {
        if ( is_array( $label ) ) {
            if ( isset( $label[ 'label' ] ) )
                $label = $label[ 'label' ];
            else
                $label = $val;
        }

        $attributes = array();

        $attributes[ 'type' ] = 'checkbox';
        $attributes[ 'tabindex' ] = 2;

        if ( ( !is_array( $value ) && (string) $val === (string) $value ) || ( is_array( $value ) && ( in_array( $val, $value ) || in_array( (string) $val, $value ) ) ) )
            $attributes[ 'checked' ] = 'CHECKED';

        $attributes[ 'value' ] = $val;

        if ( 1 < $data_count && false === strpos( $primary_name, '[]' ) )
            $name = $primary_name . '[' . ( $counter - 1 ) . ']';

        $attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

        $indent = '';

        $indent_count = substr_count( $label, '&nbsp;&nbsp;&nbsp;' );

        if ( 0 < $indent_count ) {
            $label = str_replace( '&nbsp;&nbsp;&nbsp;', '', $label );

            $indent = ' style="margin-left:' . ( 18 * $indent_count ) . 'px;"';
        }

        if ( 1 < $data_count && false === strpos( $primary_name, '[]' ) )
            $attributes[ 'class' ] .= ' pods-dependent-multi';

        if ( strlen( $label ) < 1 )
            $attributes[ 'class' ] .= ' pods-form-ui-no-label';

        if ( pods_var( 'readonly', $options, false ) ) {
            $attributes[ 'readonly' ] = 'READONLY';

            $attributes[ 'class' ] .= ' pods-form-ui-read-only';
        }

        if ( 1 < $data_count )
            $attributes[ 'id' ] = $primary_id . $counter;

        if ( 1 == pods_var( 'grouped', $options, 0, null, true ) ) {
            ?>
        <li>
<?php
        }
        ?>
        <div class="pods-field pods-boolean"<?php echo $indent; ?>>
            <input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
            <?php
            if ( 0 < strlen( $label ) ) {
                $help = pods_var_raw( 'help', $options );

                if ( 1 == pods_var( 'grouped', $options, 0, null, true ) || empty( $help ) )
                    $help = '';

                echo PodsForm::label( $attributes[ 'id' ], $label, $help );
            }
            ?>
        </div>
        <?php

        if ( 1 == pods_var( 'grouped', $options, 0, null, true ) ) {
            ?>
        </li>
<?php
        }

        $counter++;
    }

    if ( 1 == pods_var( 'grouped', $options, 0, null, true ) ) {
        ?>
    </ul>
</div>
<?php
    }
}
