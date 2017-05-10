<?php
    $field = array_merge( $field_settings[ 'field_defaults' ], $field );

    // Migrate pick object when saving
    if ( 'pod' == pods_v_sanitized( 'pick_object', $field ) ) {
        if ( isset( PodsMeta::$post_types[ $field[ 'pick_val' ] ] ) )
            $field[ 'pick_object' ] = 'post_type';
        elseif ( isset( PodsMeta::$taxonomies[ $field[ 'pick_val' ] ] ) )
            $field[ 'pick_object' ] = 'taxonomy';
        elseif ( 'user' == $field[ 'pick_val' ] && !empty( PodsMeta::$user ) ) {
            $field[ 'pick_object' ] = 'user';
            $field[ 'pick_val' ] = '';
        }
        elseif ( 'comment' == $field[ 'pick_val' ] && !empty( PodsMeta::$comment ) ) {
            $field[ 'pick_object' ] = 'comment';
            $field[ 'pick_val' ] = '';
        }
        elseif ( 'media' == $field[ 'pick_val' ] && !empty( PodsMeta::$media ) ) {
            $field[ 'pick_object' ] = 'media';
            $field[ 'pick_val' ] = '';
        }
    }

    $ignored_pick_objects = apply_filters( '', array( 'table' ) );

    if ( !in_array( pods_v_sanitized( 'pick_object', $field ), $ignored_pick_objects ) ) {
        // Set pick object
        $field[ 'pick_object' ] = trim( pods_v_sanitized( 'pick_object', $field ) . '-' . pods_v_sanitized( 'pick_val', $field ), '-' );
    }

    // Unset pick_val for the field to be used above
    if ( isset( $field[ 'pick_val' ] ) )
        unset( $field[ 'pick_val' ] );

    // Remove weight as we're going to allow reordering here
    unset( $field[ 'weight' ] );

    // Remove options, we don't need it in the JSON
    unset( $field[ 'options' ] );
    unset( $field[ 'table_info' ] );

    $data = array(
        'row' => $pods_i
    );
?>
<tr id="row-<?php echo esc_attr( $pods_i ); ?>" class="pods-manage-row pods-field-init pods-field-<?php echo esc_attr( pods_v_sanitized( 'name', $field ) ) . ( '--1' === $pods_i ? ' flexible-row' : ' pods-submittable-fields' ); ?>" valign="top"<?php PodsForm::data( $data ); ?>>
    <th scope="row" class="check-field pods-manage-sort">
        <img src="<?php echo esc_url( PODS_URL ); ?>ui/images/handle.gif" alt="<?php esc_attr_e( 'Move', 'pods' ); ?>" />
    </th>
    <td class="pods-manage-row-label">
        <strong> <a class="pods-manage-row-edit row-label" title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" href="#edit-field">
            <?php echo esc_html( pods_v( 'label', $field ) ); ?>
        </a> <abbr title="required" class="required<?php echo esc_attr( 1 == pods_v( 'required', $field ) ? '' : ' hidden' ); ?>">*</abbr> </strong>

        <?php
        if ( '__1' != pods_v_sanitized( 'id', $field ) ) {
            ?>
            <span class="pods-manage-row-more">
                        [id: <?php echo esc_html( pods_v_sanitized( 'id', $field ) ); ?>]
                    </span>
            <?php
        }
        ?>

        <div class="row-actions">
                    <span class="edit">
                        <a title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" class="pods-manage-row-edit" href="#edit-field"><?php _e( 'Edit', 'pods' ); ?></a> |
                    </span>
                    <span class="duplicate">
                        <a title="<?php esc_attr_e( 'Duplicate this field', 'pods' ); ?>" class="pods-manage-row-duplicate" href="#duplicate-field"><?php _e( 'Duplicate', 'pods' ); ?></a> |
                    </span>
                    <span class="trash pods-manage-row-delete">
                        <a class="submitdelete" title="<?php esc_attr_e( 'Delete this field', 'pods' ); ?>" href="#delete-field"><?php _e( 'Delete', 'pods' ); ?></a>
                    </span>
        </div>
        <div class="pods-manage-row-wrapper" id="pods-manage-field-<?php echo esc_attr( $pods_i ); ?>">
            <input type="hidden" name="field_data_json[<?php echo esc_attr( $pods_i ); ?>]" value="<?php echo esc_attr( ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $field, JSON_UNESCAPED_UNICODE ) : json_encode( $field ) ) ); ?>" class="field_data" />

            <div class="pods-manage-field pods-dependency">
                <input type="hidden" name="field_data[<?php echo esc_attr( $pods_i ); ?>][id]" value="<?php echo esc_attr( pods_v( 'id', $field ) ); ?>" />
            <div>
        </div>
    </td>
    <td class="pods-manage-row-name">
        <a title="Edit this field" class="pods-manage-row-edit row-name" href="#edit-field"><?php echo esc_html( pods_v( 'name', $field ) ); ?></a>
    </td>
    <td class="pods-manage-row-type">
        <?php
            $type = 'Unknown';

            if ( isset( $field_types[ pods_v_sanitized( 'type', $field ) ] ) )
                $type = $field_types[ pods_v_sanitized( 'type', $field ) ][ 'label' ];

            echo esc_html( $type ) . ' <span class="pods-manage-row-more">[type: ' . pods_v_sanitized( 'type', $field ) . ']</span>';

            $pick_object = trim( pods_v_sanitized( 'pick_object', $field ) . '-' . pods_v_sanitized( 'pick_val', $field ), '-' );

            if ( 'pick' == pods_v_sanitized( 'type', $field ) && '' != pods_v_sanitized( 'pick_object', $field, '' ) ) {
                $pick_object_name = null;

                foreach ( $field_settings[ 'pick_object' ] as $object => $object_label ) {
                    if ( null !== $pick_object_name )
                        break;

                    if ( '-- Select --' == $object_label )
                        continue;

                    if ( is_array( $object_label ) ) {
                        foreach ( $object_label as $sub_object => $sub_object_label ) {
                            if ( $pick_object == $sub_object ) {
                                $ies = strlen( $object ) - 3;

                                if ( $ies === strpos( $object, 'ies' ) )
                                    $object = substr( $object, 0, $ies ) . 'y';

                                $object = rtrim( $object, 's' );

                                $pick_object_name = esc_html( $sub_object_label ) . ' <small>(' . esc_html( $object ) . ')</small>';

                                break;
                            }
                        }
                    }
                    elseif ( pods_v_sanitized( 'pick_object', $field ) == $object ) {
                        $pick_object_name = $object_label;

                        break;
                    }
                }

                if ( null === $pick_object_name ) {
                    $pick_object_name = ucwords( str_replace( array( '-', '_' ), ' ', pods_v( 'pick_object', $field ) ) );

                    if ( 0 < strlen( pods_v( 'pick_val', $field ) ) )
                        $pick_object_name = pods_v( 'pick_val', $field ) . ' (' . $pick_object_name . ')';
                }
        ?>
            <br /><span class="pods-manage-field-type-desc">&rsaquo; <?php echo $pick_object_name; ?></span>
        <?php
            }
        ?>
    </td>
</tr>