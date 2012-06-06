<?php
    if ( isset( $options[ 'grouped' ] ) && 1 == $options[ 'grouped' ] ) {
?>
<div class="pods-pick-values pods-pick-radio">
    <ul>
<?php
    }

    $counter = 1;
    foreach ( $options[ 'data' ] as $val => $label ) {
        $attributes = array();
        $attributes[ 'type' ] = 'radio';
        $attributes[ 'checked' ] = ( $val == $value ) ? 'CHECKED' : null;
        $attributes[ 'value' ] = $val;
        $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$type, $options );
        if ( 1 < count( $options[ 'data' ] ) )
            $attributes[ 'id' ] .= $counter;

        if ( isset( $options[ 'grouped' ] ) && 1 == $options[ 'grouped' ] ) {
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
            if ( isset( $options[ 'help' ] ) && 0 < strlen( $options[ 'help' ] ) )
                $help = $options[ 'help' ];

            parent::label( $attributes[ 'id' ], $label, $help );
        }
?>
            </div>
<?php

        if ( isset( $options[ 'grouped' ] ) && 1 == $options[ 'grouped' ] ) {
?>
        </li>
<?php
        }

        $counter++;
    }

    if ( isset( $options[ 'grouped' ] ) &&  1 == $options[ 'grouped' ] ) {
?>
    </ul>
</div>
<?php
    }