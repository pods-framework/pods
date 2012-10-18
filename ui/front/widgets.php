<?php
    echo $before_widget;

    if ( !empty( $title ) )
        echo $before_title . $title . $after_title;

    echo pods_shortcode( $args, ( isset( $content ) ? $content : null ) );

    echo $after_widget;