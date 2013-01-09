<span class="pods-pagination-paginate <?php echo $params->class ?>">
    <?php
        $args = array(
            'base' => $params->base,
            'format' => $params->format,
            'total' => $params->total,
            'current' => $params->page,
            'end_size' => $params->end_size,
            'mid_size' => $params->mid_size,
            'prev_next' => $params->prev_next,
            'prev_text' => $params->prev_text,
            'next_text' => $params->next_text,
        );

        echo paginate_links( $args );
    ?>
</span>