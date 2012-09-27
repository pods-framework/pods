<?php
$options[ 'data' ] = (array) pods_var_raw( 'data', $options, array(), null, true );

if ( 1 == pods_var( 'grouped', $options ) ) {
    ?>
<div class="pods-pick-values pods-pick-radio">
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

    $attributes[ 'type' ] = 'radio';

    $attributes[ 'checked' ] = null;
    $attributes[ 'tabindex' ] = 2;

    if ( $val == $value || ( is_array( $value ) && in_array( $val, $value ) ) )
        $attributes[ 'checked' ] = 'CHECKED';

    $attributes[ 'value' ] = $val;

    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

    if ( 1 < count( $options[ 'data' ] ) )
        $attributes[ 'id' ] = $primary_id . $counter;

    if ( 1 == pods_var( 'grouped', $options ) ) {
        ?>
        <li>
<?php
    }
    ?>
    <div class="pods-field pods-boolean">
        <input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
        <?php
        if ( 0 < strlen( $label ) ) {
            $help = '';

            if ( 0 == pods_var( 'grouped', $options ) && 0 < strlen( pods_var_raw( 'help', $options ) ) )
                $help = pods_var_raw( 'help', $options );

            echo PodsForm::label( $attributes[ 'id' ], $label, $help );
        }
        ?>
    </div>
    <?php

    if ( 1 == pods_var( 'grouped', $options ) ) {
        ?>
        </li>
<?php
    }

    $counter++;
}

if ( 1 == pods_var( 'grouped', $options ) ) {
    ?>
    </ul>
</div>
<?php
}
