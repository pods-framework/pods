<span class="pods-pagination-basic">

    <?php
        if ( 1 < $params->page ) {
    ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-prev"><?php echo $params->prev_label; ?></a>
    <?php
        }

        if ( $params->page < $params->total_pages ) {
    ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-next"><?php echo $params->next_label; ?></a>
    <?php
        }
    ?>

</span>
