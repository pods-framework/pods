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
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"> <?php _e( 'Title', 'pods' ); ?></label>

        <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
    </li>

    <li>
        <label for="<?php $this->get_field_id( 'view' ); ?>"><?php _e( 'File to include', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'view' ); ?>" id="<?php echo $this->get_field_id( 'view' ); ?>" value="<?php echo esc_attr( $view ); ?>" />
    </li>

    <li>
        <label for="<?php echo $this->get_field_id( 'cache_type' ); ?>"><?php _e( 'Cache Type', 'pods' ); ?></label>

        <?php
            $cache_types = array(
                'none' => __( 'Disable Caching', 'pods' ),
                'cache' => __( 'Object Cache', 'pods' ),
                'transient' => __( 'Transient', 'pods' ),
                'site-transient' => __( 'Site Transient', 'pods' )
            );
        ?>
        <select id="<?php echo $this->get_field_id( 'cache_type' ); ?>" name="<?php echo $this->get_field_name( 'cache_type' ); ?>">
            <?php foreach ( $cache_types as $cache_type_option => $cache_type_label ): ?>
            <?php $selected = ( $cache_type_option == $cache_type ) ? 'selected' : ''; ?>
            <option value="<?php echo $cache_type_option; ?>" <?php echo $selected; ?>>
                <?php echo esc_html( $cache_type_label ); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </li>

    <li>
        <label for="<?php $this->get_field_id( 'expires' ); ?>"><?php _e( 'Cache Expiration (in seconds)', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'expires' ); ?>" id="<?php echo $this->get_field_id( 'expires' ); ?>" value="<?php echo esc_attr( $expires ); ?>" />
    </li>
</ol>
