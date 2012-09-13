<span class="pods-pagination-simple">
    <span class="pods-pagination-label"><?php echo $params->label; ?></span>

    <?php
    if ( 1 < $params->page ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-prev"><?php echo $params->prev_label; ?></a>
        <a href="<?php echo pods_var_update( array( $params->page_var => 1 ) ); ?>" class="pods-pagination-number pods-pagination-first">1</a>
        <?php
    }

    if ( 1 < ( $params->page - 100 ) ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 100 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page - 100 ); ?>"><?php echo ( $params->page - 100 ); ?></a>
        <?php
    }

    if ( 1 < ( $params->page - 10 ) ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 10 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page - 10 ); ?>"><?php echo ( $params->page - 10 ); ?></a>
        <?php
    }

    for ( $i = 2; $i > 0; $i-- ) {
        if ( 1 < ( $params->page - $i ) ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - $i ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page - $i ); ?>"><?php echo ( $params->page - $i ); ?></a>
            <?php
        }
    }
    ?>

    <span class="pods-pagination-number pods-pagination-current"><?php echo $params->page; ?></span>

    <?php
    for ( $i = 1; $i < 3; $i++ ) {
        if ( ( $params->page + $i ) < $params->total_pages ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + $i ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page + $i ); ?>"><?php echo ( $params->page + $i ); ?></a>
            <?php
        }
    }

    if ( ( $params->page + 10 ) < $params->total_pages ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 10 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page + 10 ); ?>"><?php echo ( $params->page + 10 ); ?></a>
        <?php
    }

    if ( ( $params->page + 100 ) < $params->total_pages ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 100 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page + 100 ); ?>"><?php echo ( $params->page + 100 ); ?></a>
        <?php
    }

    if ( $params->page < $params->total_pages ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => $params->total_pages ) ); ?>" class="pods-pagination-number pods-pagination-last"><?php echo $params->total_pages; ?></a>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-next"><?php echo $params->next_label; ?></a>
        <?php
    }
    ?>

</span>
