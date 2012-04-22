<?php
class PodsForm {
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
     * Output a field's label
     *
     * @since 2.0.0
     */
    public static function label ($name, $label, $help = '') {
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);

        $label = apply_filters('pods_form_ui_label_text', $label, $name, $help);
        $help = apply_filters('pods_form_ui_label_help', $help, $name, $label);

        ob_start();

        $attributes = array();
        $attributes['class'] = 'pods-form-ui-label-' . $name_more_clean;
        $attributes['for'] = 'pods-form-ui-' . $name_clean;
?>
    <label<?php self::attributes($attributes, $name, 'label'); ?>>
        <?php
            echo wp_kses_post($label);
            if (0 < strlen($help) && 'help' != $help)
                pods_help($help);
        ?>
    </label>
<?php
        $output = ob_get_clean();

        return apply_filters('pods_form_ui_label', $output, $name, $label, $help);
    }

    /**
     * Output a field
     *
     * @since 2.0.0
     */
    public static function field ($name, $value, $type = 'text', $options = null) {
        $options = (array) $options;

        ob_start();

        if (method_exists(get_called_class(), "field_{$type}")) {
            $field = "field_{$type}";
            call_user_func(array(get_called_class(), $field), $name, $value, $options);
        }
        elseif (method_exists(__CLASS__, "field_{$type}")) {
            $field = "field_{$type}";
            call_user_func(array(__CLASS__, $field), $name, $value, $options);
        }
        else
            do_action('pods_form_ui_field_' . $type, $name, $value, $options);

        $output = ob_get_clean();

        return apply_filters('pods_form_ui_field', $output, $name, $value, $type, $options);
    }

    /**
     * Output field type 'text'
     *
     * @since 2.0.0
     */
    protected function field_text ($name, $value = null, $options = null) {
        $options = (array) $options;
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'text';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $options);
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
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'textarea';
        $attributes = array();
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes = self::merge_attributes($attributes, $options);
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
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'tinymce';
        $attributes = array();
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes = self::merge_attributes($attributes, $options);
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
        $options = (array) $options;
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'number';
        $decimals = 0;
        $decimal_point = '.';
        $thousands_sep = '';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $options);
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
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'password';
        $attributes = array();
        $attributes['type'] = 'password';
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $options);
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
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'slug';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes['value'] = self::clean($value, false, true);
        $attributes = self::merge_attributes($attributes, $options);
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
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'slug';
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $options);
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
     * Output field type 'boolean'
     *
     * @since 2.0.0
     */
    protected function field_boolean ($name, $value = null, $options = null) {
        $options = (array) $options;
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'boolean';
        $attributes = array();
        $attributes['type'] = 'checkbox';
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes['value'] = 1;
        $attributes['checked'] = (1 == $value || true === $value) ? 'CHECKED' : null;
        $attributes = self::merge_attributes($attributes, $options);
        if (isset($options['default']) && strlen($attributes['value']) < 1)
            $attributes['value'] = $options['default'];
        $attributes['value'] = apply_filters('pods_form_ui_field_' . $type . '_value', $attributes['value'], $name, $attributes, $options);
?>
    <input<?php self::attributes($attributes, $name, $type, $options); ?> />
<?php
    }

    /**
     * Output field type 'pick'
     *
     * @since 2.0.0
     */
    protected function field_pick ($name, $value = null, $options = null) {
        $options = (array) $options;
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'pick';
        $attributes = array();
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes = self::merge_attributes($attributes, $options);
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
        $name_clean = self::clean($name);
        $name_more_clean = self::clean($name, true);
        $type = 'pick_checkbox';
        $attributes = array();
        $attributes['type'] = 'checkbox';
        $attributes['name'] = $name;
        $attributes['data-name-clean'] = $name_more_clean;
        $attributes['id'] = 'pods-form-ui-' . $name_clean;
        $attributes['class'] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
        $attributes['value'] = $value;
        $attributes = self::merge_attributes($attributes, $options);
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
    protected function merge_attributes ($attributes, $options) {
        $options = (array) $options;
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
        return $attributes;
    }

    /**
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