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
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'pods' ); ?></label>
        <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_html( $title ); ?>">
    </li>
    <li>
        <?php
        $api = pods_api();
        $all_pods = $api->load_pods();
        ?>
        <label for="<?php echo $this->get_field_id( 'pod_type' ); ?>"><?php _e( 'Pod Type', 'pods' ); ?></label>
        <?php if ( 0 < count( $all_pods ) ): ?>
        <select id="<?php echo $this->get_field_id( 'pod_type' ); ?>" name="<?php echo $this->get_field_name( 'pod_type' ); ?>">
            <?php foreach ( $all_pods as $pod ): ?>
            <?php $selected = ( $pod[ 'name' ] == $pod_type ) ? 'selected' : ''; ?>
            <option value="<?php echo $pod[ 'name' ]; ?>" <?php echo $selected; ?>>
                <?php echo esc_html( $pod[ 'label' ] ); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>

        <?php endif; ?>
    </li>
</ol>
