<?php
class PodsForm {
    /**
     * Generate UI for a Form and it's Fields
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct () {
        add_filter( 'pods_form_ui_label_text', 'wp_kses_post', 9, 1 );
        add_filter( 'pods_form_ui_label_help', 'wp_kses_post', 9, 1 );
        add_filter( 'pods_form_ui_comment_text', 'wp_kses_post', 9, 1 );
        add_filter( 'pods_form_ui_comment_text', 'the_content', 9, 1 );
    }

    /**
     * Output a field's label
     *
     * @since 2.0.0
     */
    public static function label ( $name, $label, $help = '', $options = null ) {
        $name_clean = self::clean( $name );
        $name_more_clean = self::clean( $name, true );

        $label = apply_filters( 'pods_form_ui_label_text', $label, $name, $help, $options );
        $help = apply_filters( 'pods_form_ui_label_help', $help, $name, $label, $options );

        ob_start();

        $type = 'label';
        $attributes = array();
        $attributes[ 'class' ] = 'pods-form-ui-' . $type . ' pods-form-ui-' . $type . '-' . $name_more_clean;
        $attributes[ 'for' ] = 'pods-form-ui-' . $name_clean;
        $attributes = self::merge_attributes( $attributes, $name, $type, $options, false );

        pods_view( PODS_DIR . 'ui/fields/_label.php', compact( $name, $label, $help, $attributes, $options ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_' . $type, $output, $name, $label, $help, $attributes, $options );
    }

    /**
     * Output a Field Comment Paragraph
     */
    public static function comment ( $name, $message = null, $options = null ) {
        $options = (array) $options;
        $name_more_clean = self::clean( $name, true );

        if ( isset( $options[ 'description' ] ) && !empty( $options[ 'description' ] ) )
            $message = $options[ 'description' ];
        elseif ( empty( $message ) )
            return;

        $message = apply_filters( 'pods_form_ui_comment_text', $message, $name, $options );

        ob_start();

        $type = 'comment';
        $attributes = array();
        $attributes[ 'class' ] = 'pods-form-ui-' . $type . ' pods-form-ui-' . $type . '-' . $name_more_clean;
        $attributes = self::merge_attributes( $attributes, $name, $type, $options, false );

        pods_view( PODS_DIR . 'ui/fields/_comment.php', compact( $name, $attributes, $options ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_' . $type, $output, $name, $message, $attributes, $options );
    }

    /**
     * Output a field
     *
     * @since 2.0.0
     */
    public static function field ($name, $value, $type = 'text', $options = null, $pod = null, $id = null) {
        $options = (array) $options;

        ob_start();

        if ( class_exists( "PodsField_{$type}" ) && method_exists( "PodsField_{$type}", 'input' ) ) {
            call_user_func( array( "PodsField_{$type}", 'input' ), $name, $value, $options, $pod, $id );
        }
        else
            do_action('pods_form_ui_field_' . $type, $name, $value, $options, $pod, $id);

        $output = ob_get_clean();

        return apply_filters('pods_form_ui_field_' . $type, $output, $name, $value, $options, $pod, $id);
    }

    /**
     * Output field type 'text'
     *
     * @since 2.0.0
     */
    protected function field_text ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'text';
        $attributes = array();
        $attributes['type'] = 'text';
        if ( is_array( $value ) )
            $value = current( $value );
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
?>
    <input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
    }

    /**
     * Output field type 'textarea'
     *
     * @since 2.0.0
     */
    protected function field_textarea ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'textarea';
        $attributes = array();
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($value) < 1)
            $value = $options['default'];
        $value = apply_filters('pods_form_ui_field_' . $type . '_value', $value, $name, $attributes, $options);
?>
    <textarea<?php self::attributes($attributes, $name, $type, $options); ?>><?php echo esc_html($value); ?></textarea>
<?php
    }

    /**
     * Output field type 'tinymce'
     *
     * @since 2.0.0
     */
    protected function field_tinymce ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'tinymce';
        $attributes = array();
        $attributes['name'] = $name;
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($value) < 1)
            $value = $options['default'];

        $value = apply_filters('pods_form_ui_field_' . $type . '_value', $value, $name, $attributes, $options);
        $settings = null;
        if (isset($options['settings']))
            $settings = $options['settings'];

        $media_bar = false;
        if (!(defined('PODS_DISABLE_FILE_UPLOAD') && true === PODS_DISABLE_FILE_UPLOAD)
                && !(defined('PODS_UPLOAD_REQUIRE_LOGIN') && is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in())
                && !(defined('PODS_UPLOAD_REQUIRE_LOGIN') && !is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_UPLOAD_REQUIRE_LOGIN)))) {
            $media_bar = true;
        }

        if (function_exists('wp_editor')) {
            if (!isset($settings['media_button']))
                $settings['media_button'] = $media_bar;
            wp_editor($value, $attributes['id'], $settings);
        }
        else {
            global $wp_editor;
            require_once PODS_DIR . "/deprecated/wp-editor/wp-editor.php";
            echo $wp_editor->editor($value, $attributes['id'], $settings, $media_bar);
        }
    }

    /**
     * Output field type 'number'
     *
     * @since 2.0.0
     */
    protected function field_number ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'number';
        $decimals = 0;
        $decimal_point = '.';
        $thousands_sep = '';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        if (isset($options['decimals']))
            $decimals = (int) $options['decimals'];
        if (isset($options['decimal_point']))
            $decimal_point = $options['decimal_point'];
        if ($decimals < 1)
            $decimal_point = '';
        if (isset($options['thousands_sep']))
            $thousands_sep = $options['thousands_sep'];
        $attributes['value'] = number_format((float) $attributes['value'], $decimals, $decimal_point, $thousands_sep);
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
    if (!wp_script_is('jquery', 'queue') && !wp_script_is('jquery', 'to_do') && !wp_script_is('jquery', 'done'))
        wp_print_scripts('jquery');
?>
    <script>
        jQuery(function($){
            $('input#<?php echo $attributes['id']; ?>').keyup(function() {
                if (!/^[0-9<?php echo implode('\\', array_filter(array($decimal_point, $thousands_sep))); ?>]$/.test($(this).val())) {
                    var newval = $(this).val().replace(/[^0-9<?php echo implode('\\', array_filter(array($decimal_point, $thousands_sep))); ?>]/g, '');
                    $(this).val(newval);
                }
            });
            $('input#<?php echo $attributes['id']; ?>').blur(function() {
                $(this).keyup();
            });
        });
    </script>
<?php
    }

    /**
     * Output field type 'password'
     *
     * @since 2.0.0
     */
    protected function field_password ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'password';
        $attributes = array();
        $attributes['type'] = 'password';
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
    }

    /**
     * Output field type 'db'
     *
     * Used for field names and other places where only [a-z0-9_] is accepted
     *
     * @since 2.0.0
     */
    protected function field_db ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'slug';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['value'] = self::clean($value, false, true);
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
?>
    <input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
    if (!wp_script_is('jquery', 'queue') && !wp_script_is('jquery', 'to_do') && !wp_script_is('jquery', 'done'))
        wp_print_scripts('jquery');
?>
    <script>
        jQuery(function($){
            $('input#<?php echo $attributes['id']; ?>').change(function() {
                var newval = $(this).val().toLowerCase().replace(/([- ])/g, '_').replace(/([^0-9a-z_])/g, '').replace(/(_){2,}/g, '_');
                $(this).val(newval);
            });
        });
    </script>
<?php
    }

    /**
     * Output field type 'slug'
     *
     * @since 2.0.0
     */
    protected function field_slug ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'slug';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
?>
    <input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
    if (!wp_script_is('jquery', 'queue') && !wp_script_is('jquery', 'to_do') && !wp_script_is('jquery', 'done'))
        wp_print_scripts('jquery');
?>
    <script>
        jQuery(function($){
            $('input#<?php echo $attributes['id']; ?>').change(function() {
                var newval = $(this).val().toLowerCase().replace(/([_ ])/g, '-').replace(/([^0-9a-z-])/g, '').replace(/(-){2,}/g, '-');
                $(this).val(newval);
            });
        });
    </script>
<?php
    }

    /**
     * Output field type 'pick'
     *
     * @since 2.0.0
     */
    protected function field_pick ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'pick';
        $attributes = array();
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($value) < 1)
            $value = $options['default'];
        $value = apply_filters('pods_form_ui_field_' . $type . '_value', $value, $name, $attributes, $options);
        if (!isset($options['data']) || empty($options['data']))
            $options['data'] = array();
        elseif (!is_array($options['data']))
            $options['data'] = implode(',', $options['data']);
?>
    <select<?php self::attributes($attributes, $name, $type, $options); ?>>
<?php
        foreach( $options['data'] as $option_value => $option_label ) {
            if (is_array($option_label)) {
?>
        <optgroup label="<?php echo esc_attr($option_value); ?>">
<?php
                foreach ($option_label as $sub_option_value => $sub_option_label) {
                    $sub_option_label = (string) $sub_option_label;
                    if (is_array($sub_option_label)) {
?>
            <option<?php self::attributes($sub_option_label, $name, $type . '_option', $options); ?>><?php echo esc_html($sub_option_label); ?></option>
<?php
                    }
                    else {
?>
            <option value="<?php echo esc_attr($sub_option_value); ?>"<?php echo ($value === $sub_option_value ? ' SELECTED' : ''); ?>><?php echo esc_html($sub_option_label); ?></option>
<?php
                    }
                }
?>
        </optgroup>
<?php
            }
            else {
                $option_label = (string) $option_label;
                if (is_array($option_value)) {
?>
        <option<?php self::attributes($option_value, $name, $type . '_option', $options); ?>><?php echo esc_html($option_label); ?></option>
<?php
                }
                else {
?>
        <option value="<?php echo esc_attr($option_value); ?>"<?php echo ($value === $option_value ? ' SELECTED' : ''); ?>><?php echo esc_html($option_label); ?></option>
<?php
                }
            }
        }
?>
    </select>
<?php
    }

    /**
     * Output field type 'pick_checkbox'
     *
     * @since 2.0.0
     */
    protected function field_pick_checkbox ($name, $value = null, $options = null) {
        $options = (array) $options;
        $type = 'pick_checkbox';
        $attributes = array();
        $attributes['type'] = 'checkbox';
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $name, $type, $options);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        if (isset($options['data']))
            $attributes['data'] = $options['data'];
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
    }

	/**
	 * Output a hidden field
	 */
	protected function field_hidden($name, $value = null, $options = null) {
		$type = 'hidden';
		$attributes = array();
		$attributes['type'] = $type;
		$attributes['value'] = $value;
		$attributes = self::merge_attributes($attributes, $name, $type, $options);
?>
	<input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
	}

    /**
     * Output a field's attributes
     *
     * @since 2.0.0
     */
    public static function attributes ($attributes, $name = null, $type = null, $options = null) {
        $attributes = (array) apply_filters('pods_form_ui_field_' . $type . '_attributes', $attributes, $name, $options);
        foreach ($attributes as $attribute => $value) {
            if (null === $value)
                continue;
            echo ' ' . esc_attr((string) $attribute) . '="' . esc_attr((string) $value) . '"';
        }
    }

    /**
     * Merge attributes and handle classes
     *
     * @since 2.0.0
     */
    protected function merge_attributes ($attributes, $name = null, $type = null, $options = null ) {
        $options = (array) $options;
        if ( !in_array( $type, array( 'label', 'comment' ) ) ) {
            $name_clean = self::clean( $name );
            $name_more_clean = self::clean( $name, true );
            $_attributes = array();
            $_attributes[ 'name' ] = $name;
            $_attributes[ 'data-name-clean' ] = $name_more_clean;
            $_attributes[ 'id' ] = 'pods-form-ui-' . $name_clean;
            $_attributes[ 'class' ] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
            $attributes = array_merge( $_attributes, (array) $attributes );
        }
        if (isset($options['attributes']) && is_array($options['attributes']) && !empty($options['attributes'])) {
            $attributes = array_merge($attributes, $options['attributes']);
        }
        if (isset($options['class'])) {
            if (is_array($options['class']))
                $options['class'] = implode(' ', $options['class']);
            $options['class'] = (string) $options['class'];
            if (isset($attributes['class']))
                $attributes['class'] = $attributes['class'] . ' ' . $options['class'];
            else
                $attributes['class'] = $options['class'];
        }
        $attributes = (array) apply_filters( 'pods_form_ui_field_' . $type . '_merge_attributes', $attributes, $name, $options );
        return $attributes;
    }

    /*
     * Clean a value for use in class / id
     *
     * @since 2.0.0
     */
    public static function clean ($input, $noarray = false, $db_field = false) {
        $input = str_replace(array('--1', '__1'), '00000', $input);
        if (false !== $noarray)
            $input = preg_replace('/\[\d*\]/', '-', $input);
        $output = str_replace(array('[', ']'), '-', strtolower($input));
        $output = preg_replace('/([^a-z0-9-_])/', '', $output);
        $output = trim(str_replace(array('__', '_', '--'), '-', $output), '-');
        $output = str_replace('00000', '--1', $output);
        if (false !== $db_field)
            $output = str_replace('-', '_', $output);
        return $output;
    }
}
