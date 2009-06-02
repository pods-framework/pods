<!--
==================================================
Begin helper area
==================================================
-->
<script type="text/javascript">
jQuery(function() {
    jQuery("#helperArea .editme").click(function() {
        helper_id = jQuery(this).parent("td").parent("tr").attr("id").substr(4);
        var theform = jQuery("#helper_form").html();
        jQuery("#helperArea .hform").html("");
        jQuery("#helperArea .htr").hide();
        jQuery("#helperArea #htr"+helper_id).show();
        jQuery("#helperArea #hform"+helper_id).html(theform);
        loadHelper();
    });

    jQuery("#helperArea .dropme").click(function() {
        helper_id = jQuery(this).parent("td").parent("tr").attr("id").substr(4);
        var theform = jQuery("#helperArea #hform"+helper_id).html();
        dropHelper();
    });

    jQuery("#helperBox").jqm();
});

function addPodHelper(div_id, select_id) {
    var val = jQuery("#"+select_id).val();
    var html = '<div class="helper" id="'+val+'">'+val+' (<a onclick="jQuery(this).parent().remove()">drop</a>)</div>';
    jQuery("#"+div_id).append(html);
}

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
                var helper_data = eval("("+msg+")");
                var phpcode = (null == helper_data.phpcode) ? "" : helper_data.phpcode;
                var helper_type = (null == helper_data.helper_type) ? "display" : helper_data.helper_type;
                jQuery("#helper_content").val(phpcode);
                jQuery("#helper_type").val(helper_type);
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
                var rand = Math.round(Math.random()*9999);
                window.location="?page=pods&"+rand+"#helper";
            }
        }
    });
}

function editHelper() {
    var content = jQuery("#helperArea #hform"+helper_id+" #helper_content").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=edithelper&helper_id="+helper_id+"&phpcode="+encodeURIComponent(content),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                colorFade('helper', helper_id);
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
                    jQuery("#helperArea #htr"+helper_id).remove();
                    jQuery("#helperArea tr#hrow"+helper_id).css("background", "red");
                    jQuery("#helperArea tr#hrow"+helper_id).fadeOut("slow");
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
    <input type="button" class="button-primary" onclick="jQuery('#helperBox').jqmShow()" value="Add new helper" />
    <table id="browseTable" style="width:100%" cellpadding="0" cellspacing="0">
        <tr>
            <th></th>
            <th>Name</th>
            <th>Helper Type</th>
            <th></th>
        </tr>
<?php
if (isset($helpers))
{
    $zebra = '';
    foreach ($helpers as $id => $val)
    {
        $id = $val['id'];
        $name = $val['name'];
        $helper_type = $val['helper_type'];
        $zebra = ('' == $zebra) ? ' class="zebra"' : '';
?>
        <tr id="hrow<?php echo $id; ?>"<?php echo $zebra; ?>>
            <td width="20">
                <div class="btn editme"></div>
            </td>
            <td><?php echo $name; ?></td>
            <td><?php echo $helper_type; ?></td>
            <td width="20"><div class="btn dropme"></div></td>
        </tr>
        <tr id="htr<?php echo $id; ?>" class="htr hidden">
            <td id="hform<?php echo $id; ?>" class="hform" colspan="4"></td>
        </tr>
<?php
    }
}
?>
    </table>

    <div id="helper_form" class="hidden">
        <textarea id="helper_content"></textarea>
        <input type="button" class="button" onclick="editHelper()" value="Save changes" /> or <a href="javascript:;" onclick="jQuery('.htr').hide()">close</a>
    </div>
</div>

