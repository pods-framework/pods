<style type="text/css">
    ol.pods_form_widget_form {
        list-style: none;
        padding-left: 0;
        margin-left: 0;
    }

    ol.pods_form_widget_form label {
        display: block;
    }
</style>

<ol class="pods_form_widget_form">
    <li>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"> <?php _e( 'Title', 'pods' ); ?></label>

        <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" />
    </li>

    <li>
        <label for="<?php echo esc_attr( $this->get_field_id( 'view' ) ); ?>"><?php _e( 'File to include', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo esc_attr( $this->get_field_name( 'view' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'view' ) ); ?>" value="<?php echo esc_attr( $view ); ?>" />
    </li>

    <li>
        <label for="<?php echo esc_attr( $this->get_field_id( 'cache_mode' ) ); ?>"><?php _e( 'Cache Type', 'pods' ); ?></label>

        <?php
            $cache_modes = array(
                'none' => __( 'Disable Caching', 'pods' ),
                'cache' => __( 'Object Cache', 'pods' ),
                'transient' => __( 'Transient', 'pods' ),
                'site-transient' => __( 'Site Transient', 'pods' )
            );
        ?>
        <select id="<?php echo esc_attr( $this->get_field_id( 'cache_mode' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cache_mode' ) ); ?>">
            <?php foreach ( $cache_modes as $cache_mode_option => $cache_mode_label ): ?>
            <?php $selected = ( $cache_mode_option == $cache_mode ) ? 'selected' : ''; ?>
            <option value="<?php echo esc_attr( $cache_mode_option ); ?>"<?php selected( $cache_mode_option, $cache_mode ); ?>>
                <?php echo esc_html( $cache_mode_label ); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </li>

    <li>
        <label for="<?php echo esc_attr( $this->get_field_id( 'expires' ) ); ?>"><?php _e( 'Cache Expiration (in seconds)', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo esc_attr( $this->get_field_name( 'expires' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'expires' ) ); ?>" value="<?php echo esc_attr( $expires ); ?>" />
    </li>
</ol>
