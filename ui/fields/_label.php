<label<?php self::attributes( $attributes, $name, 'label' ); ?>>
    <?php
    echo $label;
    if ( 0 < strlen( $help ) && 'help' != $help )
        pods_help( $help );
    ?>
</label>