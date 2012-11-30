<span class="pods-pagination-simple <?php echo $params->class ?>">
    <?php

    if ( 1 < $params->page ) {
        if ( 1 === $params->first_last ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => 1 ) ); ?>" class="pods-pagination-number pods-pagination-first <?php echo $params->link_class ?>"><?php echo $params->first_text; ?></a>
            <?php } ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-prev <?php echo $params->link_class ?>"><?php echo $params->prev_text; ?></a>
        <?php
    }

    if ( $params->page < $params->total ) {
        ?>

        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-next <?php echo $params->link_class ?>"><?php echo $params->next_text; ?></a>
        <?php if ( 1 === $params->first_last ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => $params->total ) ); ?>" class="pods-pagination-number pods-pagination-last <?php echo $params->link_class ?>"><?php echo $params->last_text; ?></a>
            <?php } ?>
        <?php
    }
    ?>

</span>
