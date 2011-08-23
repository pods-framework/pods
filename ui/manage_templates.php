<?php
// Get all pages
$result = pod_query("SELECT id, name FROM @wp_pod_templates ORDER BY name");
while ($row = mysql_fetch_assoc($result)) {
    $templates[$row['id']] = $row['name'];
}
?>
<!-- Begin template area -->
<script type="text/javascript">
jQuery(function() {
    jQuery(".select-template").change(function() {
        template_id = jQuery(this).val();
        if ("" == template_id) {
            jQuery("#templateArea .stickynote").show();
            jQuery("#templateContent").hide();
            jQuery("#template_code").val("");
        }
        else {
            jQuery("#templateArea .stickynote").hide();
            jQuery("#templateContent").show();
            loadTemplate();
        }
    });
    jQuery(".select-template").change();
    jQuery("#templateBox").jqm();
});

function loadTemplate() {
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=load_template&_wpnonce=<?php echo wp_create_nonce('pods-load_template'); ?>&id="+template_id,
        success: function(msg) {
            if (!is_error(msg)) {
                var json = eval("("+msg+")");
                var code = (null == json.code) ? "" : json.code;
                jQuery("#template_code").val(code);
            }
        }
    });
}

function addTemplate() {
    var name = jQuery("#new_template").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_template&_wpnonce=<?php echo wp_create_nonce('pods-save_template'); ?>&name="+name,
        success: function(msg) {
            if (!is_error(msg)) {
                var id = msg;
                var html = '<option value="'+id+'">'+name+'</option>';
                jQuery(".select-template").append(html);
                jQuery("#templateBox #new_template").val("");
                jQuery(".select-template > option[value='"+id+"']").attr("selected", "selected");
                jQuery(".select-template").change();
                jQuery("#templateBox").jqmHide();
            }
        }
    });
}

function editTemplate() {
    var code = jQuery("#template_code").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_template&_wpnonce=<?php echo wp_create_nonce('pods-save_template'); ?>&id="+template_id+"&code="+encodeURIComponent(code),
        success: function(msg) {
            if (!is_error(msg)) {
                alert("Success!");
            }
        }
    });
}

function dropTemplate() {
    if (confirm("Do you really want to drop this template?")) {
        jQuery.ajax({
            type: "post",
            url: api_url,
            data: "action=drop_template&_wpnonce=<?php echo wp_create_nonce('pods-drop_template'); ?>&id="+template_id,
            success: function(msg) {
                if (!is_error(msg)) {
                    jQuery(".select-template > option[value='"+template_id+"']").remove();
                    jQuery(".select-template").change();
                }
            }
        });
    }
}
</script>

<!-- Template popups -->

<div id="templateBox" class="jqmWindow">
    <input type="text" id="new_template" style="width:280px" maxlength="255" />
    <input type="button" class="button" onclick="addTemplate()" value="Add Template" />
    <div>Ex: <strong>event_list</strong> or <strong>gallery_photo_detail</strong></div>
</div>

<!-- Template HTML -->

<select class="area-select select-template">
    <option value="">-- Choose a Template --</option>
<?php
if (isset($templates)) {
    foreach ($templates as $key => $val) {
?>
    <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
<?php
    }
}
?>
</select>
<input type="button" class="button-primary" onclick="jQuery('#templateBox').jqmShow()" value="Add new template" />
<div id="templateContent">
    <textarea id="template_code"></textarea><br />
    <input type="button" class="button" onclick="editTemplate()" value="Save changes" /> or
    <a href="javascript:;" onclick="dropTemplate()">drop template</a>
</div>

<div class="stickynote">
    <div><strong>Templates are re-usable chunks of display code.</strong> They are called from Pod Pages using showTemplate().</div>
    <div style="margin-top:10px">Templates support all the usual code (HTML, Javascript, PHP), and you can pull column values from Pod items using <strong>Magic Tags</strong>: {@column_name}</div>
</div>