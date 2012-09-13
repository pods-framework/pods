<span class="pods-pagination-basic">

    <?php
    if ( 1 < $params->page ) {
        if ( 1 === $params->show_first_last ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => 1 ) ); ?>" class="pods-pagination-number pods-pagination-first"><?php echo $params->first_label; ?></a>
            <?php } ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-prev"><?php echo $params->prev_label; ?></a>
        <?php
    }

    if ( $params->page < $params->total_pages ) {
        if ( 1 === $params->show_first_last ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => $params->total_pages ) ); ?>" class="pods-pagination-number pods-pagination-last"><?php echo $params->last_label; ?></a>
            <?php } ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-next"><?php echo $params->next_label; ?></a>
        <?php
    }
    ?>

</span>
