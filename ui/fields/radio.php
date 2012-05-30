<?php
    $counter = 1;
    foreach ( $options[ 'data' ] as $val => $label ) {
        $attributes = array();
        $attributes[ 'type' ] = 'radio';
        $attributes[ 'checked' ] = ( $val == $value ) ? 'CHECKED' : null;
        $attributes[ 'value' ] = $val;
        $attributes = self::merge_attributes( $attributes, $name, self::$type, $options );
        if ( 1 < count( $options[ 'data' ] ) )
            $attributes[ 'id' ] .= $counter;
?>
<input<?php PodsForm::attributes( $attributes, $name, $type, $options ); ?> />
<?php
        if ( 0 < strlen( $label ) ) {
            $help = '';
            if ( isset( $options[ 'help' ] ) && 0 < strlen( $options[ 'help' ] ) )
                $help = $options[ 'help' ];

            parent::label( $attributes[ 'id' ], $label, $help );
        }
        $counter++;
    }