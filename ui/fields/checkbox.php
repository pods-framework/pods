<?php
    if ( 1 == $options[ 'grouped' ] ) {
?>
<div class="pods-pick-values pods-pick-checkbox">
    <ul>
<?php
    }

    $counter = 1;
    foreach ( $options[ 'data' ] as $val => $label ) {
        $attributes = array();
        $attributes[ 'type' ] = 'checkbox';
        $attributes[ 'checked' ] = ( $val == $value ) ? 'CHECKED' : null;
        $attributes[ 'value' ] = $val;
        if ( 1 < count( $options[ 'data' ] ) && false === strpos( '[]', $name ) )
            $name .= '[]';
        $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$type, $options );
        if ( 1 < count( $options[ 'data' ] ) )
            $attributes[ 'id' ] .= $counter;

        if ( 1 == $options[ 'grouped' ] ) {
?>
        <li>
<?php
        }
?>
            <div class="pods-field pods-boolean">
                <input<?php PodsForm::attributes( $attributes, $name, PodsForm::$type, $options ); ?> />
<?php
        if ( 0 < strlen( $label ) ) {
            $help = '';
            if ( 0 == $options[ 'grouped' ] && isset( $options[ 'help' ] ) && 0 < strlen( $options[ 'help' ] ) )
                $help = $options[ 'help' ];

            PodsForm::label( $attributes[ 'id' ], $label, $help );
        }
?>
            </div>
<?php

        if ( 1 == $options[ 'grouped' ] ) {
?>
        </li>
<?php
        }

        $counter++;
    }

    if ( 1 == $options[ 'grouped' ] ) {
?>
    </ul>
</div>
<?php
    }