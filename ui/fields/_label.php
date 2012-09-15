<label<?php PodsForm::attributes( $attributes, $name, 'label' ); ?>>
    <?php
        echo $label;

        if ( 0 == $options[ 'grouped' ] && !empty( $help ) && 'help' != $help )
            pods_help( $help );
    ?>
</label>