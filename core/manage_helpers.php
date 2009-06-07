<!--
==================================================
Begin helper area
==================================================
-->
<script type="text/javascript">
jQuery(function() {
    jQuery(".select-helper").change(function() {
        helper_id = jQuery(this).val();
        if ("" == helper_id) {
            jQuery("#helperContent").hide();
            jQuery("#helper_code").val("");
        }
        else {
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
        url: "<?php echo $pods_url; ?>/ajax/load.php",
        data: "auth="+auth+"&helper_id="+helper_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var json = eval("("+msg+")");
                var code = (null == json.phpcode) ? "" : json.phpcode;
                var helper_type = (null == json.helper_type) ? "display" : json.helper_type;
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
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&type=helper&name="+name+"&helper_type="+helper_type,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var id = msg;
                var html = '<option value="'+id+'">'+name+'</option>';
                jQuery(".select-helper").append(html);
                jQuery("#helperBox #new_helper").val("");
                jQuery(".select-helper > option[value="+id+"]").attr("selected", "selected");
                jQuery(".select-helper").change();
                jQuery("#helperBox").jqmHide();
            }
        }
    });
}

function editHelper() {
    var code = jQuery("#helper_code").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=edithelper&helper_id="+helper_id+"&phpcode="+encodeURIComponent(code),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                alert("Success!");
            }
        }
    });
}

function dropHelper() {
    if (confirm("Do you really want to drop this helper?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&helper="+helper_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery(".select-helper > option[value="+helper_id+"]").remove();
                    jQuery(".select-helper").change();
                }
            }
        });
    }
}
</script>

<!--
==================================================
Helper popups
==================================================
-->
<div id="helperBox" class="jqmWindow">
    <input type="text" id="new_helper" style="width:280px" />
    <input type="button" class="button" onclick="addHelper()" value="Add Helper" />
    <select id="helper_type">
        <option value="display">Display (pre-output hook)</option>
        <option value="input">Input (alter input fields)</option>
        <option value="before">Before (pre-save hook)</option>
        <option value="after">After (post-save hook)</option>
    </select>
    <div>Ex: <strong>format_date</strong> or <strong>mp3_player</strong></div>
</div>

<!--
==================================================
Helper HTML
==================================================
-->
<div id="helperArea" class="area hidden">
    <select class="area-select select-helper">
        <option value="">Choose a Helper</option>
<?php
if (isset($helpers))
{
    foreach ($helpers as $key => $helper)
    {
        $helper_id = $helper['id'];
        $helper_name = $helper['name'];
        $helper_type = $helper['helper_type'];
?>
        <option value="<?php echo $helper_id; ?>"><?php echo $helper_name; ?></option>
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
</div>
