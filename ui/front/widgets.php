<?php
    echo $before_widget;

    if ( !empty( $title ) )
        echo $before_title . $title . $after_title;

    if ( !empty( $before_content ) )
        echo $before_content;

    echo pods_shortcode( $args, ( isset( $content ) ? $content : null ) );

    if ( !empty( $after_content ) )
        echo $after_content;

    echo $after_widget;