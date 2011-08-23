<!-- Begin helper area -->
<script type="text/javascript">
var helper_id = '';
var helper_name = '';
jQuery(function() {
    jQuery(".select-helper").change(function() {
        helper_id = jQuery(this).val();
        if ("" == helper_id) {
            jQuery("#helperArea .stickynote").show();
            jQuery("#helperContent").hide();
            jQuery("#helper_code").val("");
        }
        else {
            jQuery("#helperArea .stickynote").hide();
            jQuery("#helperContent").show();
            loadHelper();
        }
    });
    jQuery(".select-helper").change();
    jQuery("#helperBox").jqm();
});

function loadHelper() {
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=load_helper&_wpnonce=<?php echo wp_create_nonce('pods-load_helper'); ?>&id="+helper_id,
        success: function(msg) {
            if (!is_error(msg)) {
                var json = eval('('+msg+')');
                var code = (null == json.phpcode) ? "" : json.phpcode;
                var helper_type = (null == json.helper_type) ? "display" : json.helper_type;
                helper_id = json.id;
                helper_name = json.name;
                jQuery("#helper_code").val(code);
            }
        }
    });
}

function addHelper() {
    var name = jQuery("#new_helper").val();
    var helper_type = jQuery("#helper_type").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_helper&_wpnonce=<?php echo wp_create_nonce('pods-save_helper'); ?>&name="+name+"&helper_type="+helper_type,
        success: function(msg) {
            if (!is_error(msg)) {
                var id = msg;
                var html = '<option value="'+id+'">'+name+' ('+helper_type+')</option>';
                jQuery(".select-helper").append(html);
                jQuery("#helperBox #new_helper").val("");
                jQuery(".select-helper > option[value='"+id+"']").attr("selected", "selected");
                jQuery(".select-helper").change();
                jQuery("#helperBox").jqmHide();
<?php
if (pods_access('manage_pods')) {
?>
                var helper_select_html = '<option value="'+name+'">'+name+'</option>';
                if ('pre_save' == helper_type) {
                    jQuery("#pre_save_helpers").append(helper_select_html);
                }
                else if ('pre_drop' == helper_type) {
                    jQuery("#pre_drop_helpers").append(helper_select_html);
                }
                else if ('post_save' == helper_type) {
                    jQuery("#post_save_helpers").append(helper_select_html);
                }
                else if ('post_drop' == helper_type) {
                    jQuery("#post_drop_helpers").append(helper_select_html);
                }
                else if ('input' == helper_type) {
                    jQuery("#column_input_helper").append(helper_select_html);
                }
<?php
}
?>
            }
        }
    });
}

function editHelper() {
    var code = jQuery("#helper_code").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_helper&_wpnonce=<?php echo wp_create_nonce('pods-save_helper'); ?>&id="+helper_id+"&phpcode="+encodeURIComponent(code),
        success: function(msg) {
            if (!is_error(msg)) {
                alert("Success!");
            }
        }
    });
}

function dropHelper() {
    if (confirm("Do you really want to drop this helper?")) {
        jQuery.ajax({
            type: "post",
            url: api_url,
            data: "action=drop_helper&_wpnonce=<?php echo wp_create_nonce('pods-drop_helper'); ?>&id="+helper_id,
            success: function(msg) {
                if (!is_error(msg)) {
                    jQuery(".select-helper > option[value='"+helper_id+"']").remove();
                    jQuery(".select-helper").change();
<?php
if (pods_access('manage_pods')) {
?>
                    jQuery("div.helper#"+helper_name).remove();
                    jQuery("#pre_save_helpers option[value='"+helper_name+"']").remove();
                    jQuery("#pre_drop_helpers option[value='"+helper_name+"']").remove();
                    jQuery("#post_save_helpers option[value='"+helper_name+"']").remove();
                    jQuery("#post_drop_helpers option[value='"+helper_name+"']").remove();
<?php
}
?>
                }
            }
        });
    }
}
</script>

<!-- Helper popups -->

<div id="helperBox" class="jqmWindow">
    <input type="text" id="new_helper" style="width:280px" maxlength="255" />
    <input type="button" class="button" onclick="addHelper()" value="Add Helper" />
    <select id="helper_type">
        <option value="display">Display (pre-output)</option>
        <option value="input">Input (alter input fields)</option>
        <option value="pre_save">Pre-save</option>
        <option value="pre_drop">Pre-drop</option>
        <option value="post_save">Post-save</option>
        <option value="post_drop">Post-drop</option>
    </select>
    <div>Ex: <strong>format_date</strong> or <strong>mp3_player</strong></div>
</div>

<!-- Helper HTML -->

<select class="area-select select-helper">
    <option value="">-- Choose a Helper --</option>
<?php
if (isset($helpers)) {
    foreach ($helpers as $key => $helper) {
        $helper_id = $helper['id'];
        $helper_name = $helper['name'];
        $helper_type = $helper['helper_type'];
?>
    <option value="<?php echo $helper_id; ?>"><?php echo $helper_name; ?> (<?php echo $helper_type; ?>)</option>
<?php
    }
}
?>
</select>
<input type="button" class="button-primary" onclick="jQuery('#helperBox').jqmShow()" value="Add new helper" />
<div id="helperContent">
    <textarea id="helper_code"></textarea><br />
    <input type="button" class="button" onclick="editHelper()" value="Save changes" /> or
    <a href="javascript:;" onclick="dropHelper()">drop helper</a>
</div>

<div class="stickynote">
    <div><strong>Display Helpers</strong> allow you to format a column's value before it gets displayed. They can be invoked within Pod Templates (using Magic Tags), within Pod Pages (using the pod_helper function), or at the column-level.</div>
    <div style="margin-top:10px"><strong>Pre-save Helpers</strong> run right before Pod data is saved to the database. They are invoked at the Pod-level.</div>
    <div style="margin-top:10px"><strong>Post-save Helpers</strong> run right after Pod data is successfully saved to the database. They are invoked at the Pod-level.</div>
    <div style="margin-top:10px"><strong>Input Helpers</strong> allow you to override the appearance and functionality of an input form field. They are invoked at the column-level.</div>
</div>