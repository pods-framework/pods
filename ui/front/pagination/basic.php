<span class="pods-pagination-simple">
    <span class="pods-pagination-label"><?php echo $label; ?></span>

    <?php
        if ( 1 < $page ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => 1 ) ); ?>" class="pods-pagination-number pods-pagination-first">1</a>
    <?php
        }

        if ( 1 < ( $page - 100 ) ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => ( $page - 100 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $page - 100 ); ?>"><?php echo ( $page - 100 ); ?></a>
    <?php
        }

        if ( 1 < ( $page - 10 ) ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => ( $page - 10 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $page - 10 ); ?>"><?php echo ( $page - 10 ); ?></a>
    <?php
        }

        for ( $i = 2; $i > 0; $i-- ) {
            if ( 1 < ( $page - $i ) ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => ( $page - $i ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $page - $i ); ?>"><?php echo ( $page - $i ); ?></a>
    <?php
            }
        }
    ?>

    <span class="pods-pagination-number pods-pagination-current"><?php echo $page; ?></span>

    <?php
        for ( $i = 1; $i < 3; $i++ ) {
            if ( ( $page + $i ) < $total_pages ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => ( $page + $i ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $page + $i ); ?>"><?php echo ( $page + $i ); ?></a>
    <?php
            }
        }

        if ( ( $page + 10 ) < $total_pages ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => ( $page + 10 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $page + 10 ); ?>"><?php echo ( $page + 10 ); ?></a>
    <?php
        }

        if ( ( $page + 100 ) < $total_pages ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => ( $page + 100 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $page + 100 ); ?>"><?php echo ( $page + 100 ); ?></a>
    <?php
        }

        if ( $page < $total_pages ) {
    ?>
        <a href="<?php echo pods_var_update( array( 'pg' => $total_pages ) ); ?>" class="pods-pagination-number pods-pagination-last"><?php echo $total_pages; ?></a>
    <?php
        }
    ?>

</span>