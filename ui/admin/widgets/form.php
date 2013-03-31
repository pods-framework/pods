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

    <li>
        <label for="<?php $this->get_field_id( 'slug' ); ?>"><?php _e( 'ID or Slug', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'slug' ); ?>" id="<?php echo $this->get_field_id( 'slug' ); ?>" value="<?php echo esc_attr( $slug ); ?>" />
    </li>

    <li>
        <label for="<?php $this->get_field_id( 'fields' ); ?>"><?php _e( 'Fields (comma-separated)', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'fields' ); ?>" id="<?php echo $this->get_field_id( 'fields' ); ?>" value="<?php echo esc_attr( $fields ); ?>" />
    </li>

    <li>
        <label for="<?php $this->get_field_id( 'label' ); ?>"><?php _e( 'Submit Label', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'label' ); ?>" id="<?php echo $this->get_field_id( 'label' ); ?>" value="<?php echo esc_attr( $label ); ?>" />
    </li>

    <li>
        <label for="<?php $this->get_field_id( 'thank_you' ); ?>"><?php _e( 'Thank You URL upon submission', 'pods' ); ?></label>

        <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'thank_you' ); ?>" id="<?php echo $this->get_field_id( 'thank_you' ); ?>" value="<?php echo esc_attr( $thank_you ); ?>" />
    </li>
</ol>
