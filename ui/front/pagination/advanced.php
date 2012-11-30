<span class="pods-pagination-advanced <?php echo $params->class ?>">
    <?php if ( 1 === $params->show_label ) { ?>
    <span class="pods-pagination-label"><?php echo $params->label; ?></span>
    <?php
}

    if ( 1 < $params->page ) {
        ?>
        <?php if ( $params->first_last ) { ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => 1 ) ); ?>" class="pods-pagination-number pods-pagination-first <?php echo $params->link_class ?>"><?php echo $params->first_text; ?></a>
            <?php } ?>
        <?php if ( $params->prev_next ) { ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-prev <?php echo $params->link_class ?>"><?php echo $params->prev_text; ?></a>
            <?php } ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => 1 ) ); ?>" class="pods-pagination-number pods-pagination-first <?php echo $params->link_class ?>">1</a>
        <?php
    }

    if ( 1 < ( $params->page - 100 ) ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 100 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page - 100 ); ?> <?php echo $params->link_class ?>"><?php echo ( $params->page - 100 ); ?></a>
        <?php
    }

    if ( 1 < ( $params->page - 10 ) ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - 10 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page - 10 ); ?> <?php echo $params->link_class ?>"><?php echo ( $params->page - 10 ); ?></a>
        <?php
    }

    for ( $i = $params->mid_size; $i > 0; $i-- ) {
        if ( 1 < ( $params->page - $i ) ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page - $i ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page - $i ); ?> <?php echo $params->link_class ?>"><?php echo ( $params->page - $i ); ?></a>
            <?php
        }
    }
    ?>

    <span class="pods-pagination-number pods-pagination-current <?php echo $params->link_class ?>"><?php echo $params->page; ?></span>

    <?php
    for ( $i = 1; $i <= $params->mid_size; $i++ ) {
        if ( ( $params->page + $i ) < $params->total ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + $i ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page + $i ); ?> <?php echo $params->link_class ?>"><?php echo ( $params->page + $i ); ?></a>
            <?php
        }
    }

    if ( ( $params->page + 10 ) < $params->total ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 10 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page + 10 ); ?> <?php echo $params->link_class ?>"><?php echo ( $params->page + 10 ); ?></a>
        <?php
    }

    if ( ( $params->page + 100 ) < $params->total ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 100 ) ) ); ?>" class="pods-pagination-number pods-pagination-<?php echo ( $params->page + 100 ); ?> <?php  echo $params->link_class ?>"><?php echo ( $params->page + 100 ); ?></a>
        <?php
    }

    if ( $params->page < $params->total ) {
        ?>
        <a href="<?php echo pods_var_update( array( $params->page_var => $params->total ) ); ?>" class="pods-pagination-number pods-pagination-last <?php echo $params->link_class ?>"><?php echo $params->total; ?></a>
        <?php
        if ($params->prev_next ) {
            ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => ( $params->page + 1 ) ) ); ?>" class="pods-pagination-number pods-pagination-next <?php echo $params->link_class ?>"><?php echo $params->next_text; ?></a>
            <?php } ?>
        <?php if ($params->first_last ) { ?>
            <a href="<?php echo pods_var_update( array( $params->page_var => $params->total ) ); ?>" class="pods-pagination-number pods-pagination-last <?php echo $params->link_class ?>"><?php echo $params->last_text; ?></a>
            <?php } ?>
        <?php
    }
    ?>

</span>
