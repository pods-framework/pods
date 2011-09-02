<?php
class PodsFormUI {
    /**
     * Generate UI for a Form and it's Fields
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    private function __construct () {
        return false;
    }

    /**
     * Output a field
     *
     * @since 2.0.0
     */
    public static function field ($name, $value, $type = 'text', $options = null) {
        ob_start();
        if (method_exists(get_called_class(), "field_{$type}")) {
            $field = 'field_' . $type;
            get_called_class()::$field($name, $value, $options);
        }
        elseif (method_exists(__CLASS__, "field_{$type}")) {
            $field = 'field_' . $type;
            __CLASS__::$field($name, $value, $options);
        }
        else
            do_action('pods_form_ui_field_' . $type, $name, $value, $options);
        return apply_filters('pods_form_ui_field', ob_get_clean(), $name, $value, $type, $options);
    }

    /**
     * Output a field's attributes
     *
     * @since 2.0.0
     */
    public static function attributes ($attributes) {
        foreach ($attributes as $attribute => $value) {
            if (null === $value)
                continue;
            echo ' ' . esc_attr($attribute) . '="' . esc_attr($value) . '"';
        }
    }

    /**
     * Output field type 'text'
     *
     * @since 2.0.0
     */
    protected function field_text ($name, $value = null, $options = null) {
        $type = 'text';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['id'] = 'pods_form_ui_' . $name;
        $attributes['class'] = 'pods_form_ui_field_' . $type;
        $attributes['value'] = $value;
        if (isset($options['attributes']))
            $attributes = array_merge($attributes, $options['attributes']);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
?>
    <input<?php self::attributes($attributes); ?> />
<?php
    }

    /**
     * Output field type 'textarea'
     *
     * @since 2.0.0
     */
    protected function field_textarea ($name, $value = null, $options = null) {
        $type = 'textarea';
        $attributes = array();
        $attributes['id'] = 'pods_form_ui_' . $name;
        $attributes['class'] = 'pods_form_ui_field_' . $type;
        if (isset($options['attributes']))
            $attributes = array_merge($attributes, $options['attributes']);
        if (isset($options['default']) && strlen($value) < 1)
            $value = $options['default'];
        $value = apply_filters('pods_form_ui_field_' . $type . '_value', $value, $name, $attributes, $options);
?>
    <textarea<?php self::attributes($attributes); ?>><?php echo esc_html($value); ?></textarea>
<?php
    }

    /**
     * Output field type 'tinymce'
     *
     * @since 2.0.0
     */
    protected function field_tinymce ($name, $value = null, $options = null) {

        $type = 'tinymce';
        $attributes = array();
        $attributes['id'] = 'pods_form_ui_' . $name;
        if (isset($options['attributes']))
            $attributes = array_merge($attributes, $options['attributes']);
        if (isset($options['default']) && strlen($value) < 1)
            $value = $options['default'];
        $value = apply_filters('pods_form_ui_field_' . $type . '_value', $value, $name, $attributes, $options);
        $settings = null;
        if (isset($options['settings']))
            $settings = $options['settings'];
        require_once PODS_DIR . "/ui/wp-editor/wp-editor.php";
        global $wp_editor;
        $wp_editor->editor($value, $attributes['id'], $settings);
    }

    /**
     * Output field type 'number'
     *
     * @since 2.0.0
     */
    protected function field_number ($name, $value = null, $options = null) {
        $type = 'number';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['id'] = 'pods_form_ui_' . $name;
        $attributes['class'] = 'pods_form_ui_field_' . $type;
        $attributes['value'] = $value;
        if (isset($options['attributes']))
            $attributes = array_merge($attributes, $options['attributes']);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        $attributes['value'] = number_format_i18n($attributes['value'], 0);
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes); ?> />
<?php
    }

    /**
     * Output field type 'decimal'
     *
     * @since 2.0.0
     */
    protected function field_decimal ($name, $value = null, $options = null) {
        $type = 'number';
        $decimals = 2;
        if (isset($options['decimals']))
            $decimals = $options['decimals'];
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['id'] = 'pods_form_ui_' . $name;
        $attributes['class'] = 'pods_form_ui_field_' . $type;
        $attributes['value'] = $value;
        if (isset($options['attributes']))
            $attributes = array_merge($attributes, $options['attributes']);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        $attributes['value'] = number_format_i18n($attributes['value'], $decimals);
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes); ?> />
<?php
    }

    /**
     * Output field type 'password'
     *
     * @since 2.0.0
     */
    protected function field_password ($name, $value = null, $options = null) {
        $type = 'password';
        $attributes = array();
        $attributes['type'] = 'password';
        $attributes['id'] = 'pods_form_ui_' . $name;
        $attributes['class'] = 'pods_form_ui_field_' . $type;
        $attributes['value'] = $value;
        if (isset($options['attributes']))
            $attributes = array_merge($attributes, $options['attributes']);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes); ?> />
<?php
    }

    /**
     * Output field type 'boolean'
     *
     * @since 2.0.0
     */
    protected function field_boolean ($name, $value = null, $options = null) {
        $type = 'boolean';
        $attributes = array();
        $attributes['type'] = 'checkbox';
        $attributes['id'] = 'pods_form_ui_' . $name;
        $attributes['class'] = 'pods_form_ui_field_' . $type;
        $attributes['value'] = 1;
        $attributes['checked'] = (1 == $value || true === $value) ? 'CHECKED' : null;
        if (isset($options['attributes']))
            $attributes = array_merge($attributes, $options['attributes']);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes); ?> />
<?php
    }
}