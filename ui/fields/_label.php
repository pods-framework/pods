<label<?php PodsForm::attributes( $attributes, $name, 'label' ); ?>>
    <?php
        echo $label;

        if ( 0 == pods_var( 'grouped', $options, 0, null, true ) && !empty( $help ) && 'help' != $help )
            pods_help( $help );
    ?>
</label>