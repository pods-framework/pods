<style type="text/css">
    ol.pods_list_widget_form {
        list-style: none;
        padding-left: 0;
        margin-left: 0;
    }

    ol.pods_list_widget_form label {
        display: block;
    }
</style>

<p><em><?php _e('You must specify a Pods Template or create a custom template, using <a href="http://pods.io/docs/build/using-magic-tags/" title="Using Magic Tags" target="_blank">magic tags</a>.', 'pods'); ?></p></em>

<ol class="pods_list_widget_form">
    <li>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"> <?php _e( 'Title', 'pods' ); ?></label>

        <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
    </li>

    <li>
        <?php
            $api = pods_api();
            $all_pods = $api->load_pods( array( 'names' => true ) );
        ?>
        <label for="<?php echo $this->get_field_id( 'pod_type' ); ?>">
            <?php _e( 'Pod', 'pods' ); ?>
        </label>

        <?php if ( 0 < count( $all_pods ) ): ?>
            <select id="<?php $this->get_field_id( 'pod_type' ); ?>" name="<?php echo $this->get_field_name( 'pod_type' ); ?>">
                <?php foreach ( $all_pods as $pod_name => $pod_label ): ?>
                    <?php $selected = ( $pod_name == $pod_type ) ? 'selected' : ''; ?>
                    <option value="<?php echo $pod_name; ?>" <?php echo $selected; ?>>
                        <?php echo esc_html( $pod_label . ' (' . $pod_name . ')' ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <strong class="red"><?php _e( 'None Found', 'pods' ); ?></strong>
        <?php endif; ?>
    </li>

    <?php
        if ( class_exists( 'Pods_Templates' ) ) {
    ?>
        <li>
            <?php
                $all_templates = (array) $api->load_templates( array() );
            ?>
            <label for="<?php echo $this->get_field_id( 'template' ); ?>"> <?php _e( 'Template', 'pods' ); ?> </label>

            <select name="<?php echo $this->get_field_name( 'template' ); ?>" id="<?php echo $this->get_field_id( 'template' ); ?>">
                <option value="">- <?php _e( 'Custom Template', 'pods' ); ?> -</option>
                <?php foreach ( $all_templates as $tpl ): ?>
                    <?php $selected = ( $tpl[ 'name' ] == $template ) ? 'selected' : ''; ?>
                    <option value="<?php echo $tpl[ 'name' ]; ?>" <?php echo $selected; ?>>
                        <?php echo esc_html( $tpl[ 'name' ] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </li>
    <?php
        }
        else {
    ?>
        <li>
            <label for="<?php echo $this->get_field_id( 'template' ); ?>"> <?php _e( 'Template', 'pods' ); ?> </label>

            <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>" value="<?php echo esc_attr( $template ); ?>" />
        </li>
    <?php
        }
    ?>

    <li>
        <label for="<?php echo $this->get_field_id( 'before_content' ); ?>"> <?php _e( 'Before Content', 'pods' ); ?></label>

        <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'before_content' ); ?>" name="<?php echo $this->get_field_name( 'before_content' ); ?>" value="<?php echo esc_attr( $before_content ); ?>" />
    </li>

    <li>
        <label for="<?php echo $this->get_field_id( 'template_custom' ); ?>"> <?php _e( 'Custom Template', 'pods' ); ?></label>

        <textarea name="<?php echo $this->get_field_name( 'template_custom' ); ?>" id="<?php echo $this->get_field_id( 'template_custom' ); ?>" cols="10" rows="10" class="widefat"><?php echo esc_html( $template_custom ); ?></textarea>
    </li>

    <li>
        <label for="<?php echo $this->get_field_id( 'after_content' ); ?>"> <?php _e( 'After Content', 'pods' ); ?></label>

        <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'after_content' ); ?>" name="<?php echo $this->get_field_name( 'after_content' ); ?>" value="<?php echo esc_attr( $after_content ); ?>" />
    </li>

    <li>
        <label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit', 'pods' ); ?></label>

        <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo esc_attr( $limit ); ?>" />
    </li>

    <li>
        <label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By', 'pods' ); ?></label>

        <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>" value="<?php echo esc_attr( $orderby ); ?>" />
    </li>

    <li>
        <label for="<?php echo $this->get_field_id( 'where' ); ?>"><?php _e( 'Where', 'pods' ); ?></label>

        <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'where' ); ?>" name="<?php echo $this->get_field_name( 'where' ); ?>" value="<?php echo esc_attr( $where ); ?>" />
    </li>

    <li>
        <label for="<?php echo $this->get_field_id( 'cache_mode' ); ?>"><?php _e( 'Cache Type', 'pods' ); ?></label>

        <?php
            $cache_modes = array(
                'none' => __( 'Disable Caching', 'pods' ),
                'cache' => __( 'Object Cache', 'pods' ),
                'transient' => __( 'Transient', 'pods' ),
                'site-transient' => __( 'Site Transient', 'pods' )
            );
        ?>
        <select id="<?php echo $this->get_field_id( 'cache_mode' ); ?>" name="<?php echo $this->get_field_name( 'cache_mode' ); ?>">
            <?php foreach ( $cache_modes as $cache_mode_option => $cache_mode_label ): ?>
            <?php $selected = ( $cache_mode_option == $cache_mode ) ? 'selected' : ''; ?>
            <option value="<?php echo $cache_mode_option; ?>" <?php echo $selected; ?>>
                <?php echo esc_html( $cache_mode_label ); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </li>

    <li>
        <label for="<?php $this->get_field_id( 'expires' ); ?>"><?php _e( 'Cache Expiration (in seconds)', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'expires' ); ?>" id="<?php echo $this->get_field_id( 'expires' ); ?>" value="<?php echo esc_attr( $expires ); ?>" />
    </li>
</ol>
