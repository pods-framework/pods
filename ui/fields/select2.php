<?php
$attributes = array();
$attributes['type'] = 'hidden';
$attributes['value'] = $value;
$attributes['data-field-type'] = 'select2';
$attributes = PodsForm::merge_attributes($attributes, $name, PodsForm::$field_type, $options);
?>
<input<?php PodsForm::attributes($attributes, $name, PodsForm::$field_type, $options); ?> />

<script type="text/javascript">
jQuery(function($) {
    $('#<?php echo $attributes['id']; ?>').select2({
        placeholder: 'Start Typing...',
        minimumInputLength: 1,
        ajax: {
            url: pods_ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: function(term, page) {
                return {
                    _wpnonce: nonce,
                    action: 'pods_admin',
                    method: 'select2_ajax',
                    query: term
                };
            }
        }
    });
});
</script>
