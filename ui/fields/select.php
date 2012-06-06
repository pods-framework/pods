<?php
    $attributes = array();
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$type, $options );
    if ( !isset( $options[ 'data' ] ) || empty( $options[ 'data' ] ) )
        $options[ 'data' ] = array();
    elseif ( !is_array( $options[ 'data' ] ) )
        $options[ 'data' ] = implode( ',', $options[ 'data' ] );
?>
<select<?php PodsForm::attributes( $attributes, $name, PodsForm::$type, $options ); ?>>
    <?php
        foreach ( $options[ 'data' ] as $option_value => $option_label ) {
            if ( is_array( $option_label ) ) {
    ?>
        <optgroup label="<?php echo esc_attr( $option_value ); ?>">
            <?php
                foreach ( $option_label as $sub_option_value => $sub_option_label ) {
                    $sub_option_label = (string) $sub_option_label;
                    if ( is_array( $sub_option_label ) ) {
            ?>
                <option<?php PodsForm::attributes( $sub_option_label, $name, PodsForm::$type . '_option', $options ); ?>><?php echo esc_html( $sub_option_label ); ?></option>
            <?php
                    }
                    else {
            ?>
                <option value="<?php echo esc_attr( $sub_option_value ); ?>"<?php echo ( $value === $sub_option_value ? ' SELECTED' : '' ); ?>><?php echo esc_html( $sub_option_label ); ?></option>
            <?php
                    }
                }
            ?>
        </optgroup>
    <?php
            }
            else {
                $option_label = (string) $option_label;
                if ( is_array( $option_value ) ) {
    ?>
        <option<?php PodsForm::attributes( $option_value, $name, PodsForm::$type . '_option', $options ); ?>><?php echo esc_html( $option_label ); ?></option>
    <?php
                }
                else {
    ?>
        <option value="<?php echo esc_attr( $option_value ); ?>"<?php echo ( $value === $option_value ? ' SELECTED' : '' ); ?>><?php echo esc_html( $option_label ); ?></option>
    <?php
                }
            }
        }
    ?>
</select>