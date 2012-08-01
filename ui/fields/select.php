<?php
    $attributes = array();
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

    if ( !isset( $options[ 'data' ] ) || empty( $options[ 'data' ] ) )
        $options[ 'data' ] = array();
    elseif ( !is_array( $options[ 'data' ] ) )
        $options[ 'data' ] = implode( ',', $options[ 'data' ] );
?>
<select<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?>>
    <?php
        foreach ( $options[ 'data' ] as $option_value => $option_label ) {
            if ( is_array( $option_label ) && isset( $option_label[ 'label' ] ) )
                $option_label = $option_label[ 'label' ];

            if ( is_array( $option_label ) ) {
    ?>
        <optgroup label="<?php echo esc_attr( $option_value ); ?>">
            <?php
                foreach ( $option_label as $sub_option_value => $sub_option_label ) {
                    if ( is_array( $sub_option_label ) ) {
                        if ( isset( $sub_option_label[ 'label' ] ) )
                            $sub_option_label = $sub_option_label[ 'label' ];
                        else
                            $sub_option_label = $sub_option_value;
                    }

                    $sub_option_label = (string) $sub_option_label;

                    $selected = '';

                    if ( $sub_option_value == $value || ( is_array( $value ) && in_array( $sub_option_value, $value ) ) )
                        $selected = ' SELECTED';

                    if ( is_array( $sub_option_label ) ) {
            ?>
                <option<?php PodsForm::attributes( $sub_option_label, $name, PodsForm::$field_type . '_option', $options ); ?>><?php echo esc_html( $sub_option_label ); ?></option>
            <?php
                    }
                    else {
            ?>
                <option value="<?php echo esc_attr( $sub_option_value ); ?>"<?php echo $selected; ?>><?php echo esc_html( $sub_option_label ); ?></option>
            <?php
                    }
                }
            ?>
        </optgroup>
    <?php
            }
            else {
                $option_label = (string) $option_label;

                $selected = '';

                if ( $option_value == $value || ( is_array( $value ) && in_array( $option_value, $value ) ) )
                    $selected = ' SELECTED';

                if ( is_array( $option_value ) ) {
    ?>
        <option<?php PodsForm::attributes( $option_value, $name, PodsForm::$field_type . '_option', $options ); ?>><?php echo esc_html( $option_label ); ?></option>
    <?php
                }
                else {
    ?>
        <option value="<?php echo esc_attr( $option_value ); ?>"<?php echo $selected; ?>><?php echo esc_html( $option_label ); ?></option>
    <?php
                }
            }
        }
    ?>
</select>