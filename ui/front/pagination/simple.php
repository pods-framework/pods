<span class="pods-pagination-simple <?php echo esc_attr( $params->class ); ?>">
    <?php

    if ( 1 < $params->page ) {
        if ( 1 === $params->first_last ) {
            ?>
            <a href="<?php echo esc_url( pods_query_arg( array( $params->page_var => 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-first <?php echo esc_attr( $params->link_class ); ?>"><?php echo $params->first_text; ?></a>
            <?php } ?>
        <a href="<?php echo esc_url( pods_query_arg( array( $params->page_var => ( $params->page - 1 ) ) ) ); ?>" class="pods-pagination-number pods-pagination-prev <?php echo esc_attr( $params->link_class ); ?>"><?php echo $params->prev_text; ?></a>
        <?php
    }

    if ( $params->page < $params->total ) {
        ?>

        <a href="<?php echo esc_url( pods_query_arg( array( $params->page_var => ( $params->page + 1 ) ) ) ); ?>" class="pods-pagination-number pods-pagination-next <?php echo esc_attr( $params->link_class ); ?>"><?php echo $params->next_text; ?></a>
        <?php if ( 1 === $params->first_last ) {
            ?>
            <a href="<?php echo esc_url( pods_query_arg( array( $params->page_var => $params->total ) ) ); ?>" class="pods-pagination-number pods-pagination-last <?php echo esc_attr( $params->link_class ); ?>"><?php echo $params->last_text; ?></a>
            <?php } ?>
        <?php
    }
    ?>

</span>
