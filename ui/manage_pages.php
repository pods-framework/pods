<?php
// Get all pages
$result = pod_query("SELECT id, uri FROM @wp_pod_pages ORDER BY uri");
while ($row = mysql_fetch_assoc($result)) {
    $pages[$row['id']] = $row['uri'];
}
?>
<!-- Begin page area -->
<script type="text/javascript">
jQuery(function() {
    jQuery(".select-page").change(function() {
        page_id = jQuery(this).val();
        if ("" == page_id) {
            jQuery("#pageArea .stickynote").show();
            jQuery("#pageContent").hide();
            jQuery("#page_code").val("");
            jQuery("#page_precode").val("");
        }
        else {
            jQuery("#pageArea .stickynote").hide();
            jQuery("#pageContent").show();
            loadPage();
        }
    });
    jQuery(".select-page").change();
    jQuery("#pageBox").jqm();
});

function loadPage() {
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=load_page&_wpnonce=<?php echo wp_create_nonce('pods-load_page'); ?>&id="+page_id,
        success: function(msg) {
            if (!is_error(msg)) {
                var json = eval('('+msg+')');
                var title = (null == json.title) ? "" : json.title;
                var code = (null == json.phpcode) ? "" : json.phpcode;
                var precode = (null == json.precode) ? "" : json.precode;
                var template = (null == json.page_template) ? "" : json.page_template;
                jQuery("#page_code").val(code);
                jQuery("#page_precode").val(precode);
                jQuery("#page_title").val(title);
                jQuery("#page_template").val(template);
            }
        }
    });
}

function addPage() {
    var uri = jQuery("#new_page").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_page&_wpnonce=<?php echo wp_create_nonce('pods-save_page'); ?>&uri="+uri,
        success: function(msg) {
            if (!is_error(msg)) {
                var id = msg;
                var html = '<option value="'+id+'">'+uri+'</option>';
                jQuery(".select-page").append(html);
                jQuery("#pageBox #new_page").val("");
                jQuery(".select-page > option[value='"+id+"']").attr("selected", "selected");
                jQuery(".select-page").change();
                jQuery("#pageBox").jqmHide();
            }
        }
    });
}

function editPage() {
    var code = jQuery("#page_code").val();
    var precode = jQuery("#page_precode").val();
    var title = jQuery("#page_title").val();
    var template = jQuery("#page_template").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_page&_wpnonce=<?php echo wp_create_nonce('pods-save_page'); ?>&id="+page_id+"&page_title="+encodeURIComponent(title)+"&page_template="+encodeURIComponent(template)+"&phpcode="+encodeURIComponent(code)+"&precode="+encodeURIComponent(precode),
        success: function(msg) {
            if (!is_error(msg)) {
                alert("Success!");
            }
        }
    });
}

function dropPage() {
    if (confirm("Do you really want to drop this page?")) {
        jQuery.ajax({
            type: "post",
            url: api_url,
            data: "action=drop_page&_wpnonce=<?php echo wp_create_nonce('pods-drop_page'); ?>&id="+page_id,
            success: function(msg) {
                if (!is_error(msg)) {
                    jQuery(".select-page > option[value='"+page_id+"']").remove();
                    jQuery(".select-page").change();
                }
            }
        });
    }
}
</script>

<!-- Page popups -->

<div id="pageBox" class="jqmWindow">
    <input type="text" id="new_page" style="width:280px" maxlength="128" />
    <input type="button" class="button" onclick="addPage()" value="Add Page" />
    <div>Ex: <strong>events</strong> or <strong>events/*</strong></div>
</div>

<!-- Page HTML -->

<select class="area-select select-page">
    <option value="">-- Choose a Page --</option>
<?php
if (isset($pages)) {
    foreach ($pages as $key => $val) {
?>
    <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
<?php
    }
}
?>
</select>
<input type="button" class="button-primary" onclick="jQuery('#pageBox').jqmShow()" value="Add new page" />
<div id="pageContent">
    <textarea id="page_code"></textarea><br />
    Precode (optional):
    <textarea id="page_precode"></textarea><br />
    Page Title (optional):<br />
    <input id="page_title" type="text" maxlength="128" />
    <select id="page_template">
        <option value="">-- Page Template --</option>
<?php
$page_templates = apply_filters('pods_page_templates', get_page_templates());
if (!in_array('page.php', $page_templates) && locate_template(array('page.php', false))) {
    $page_templates['Page (WP Default)'] = 'page.php';
    ksort($page_templates);
}
foreach ($page_templates as $template => $file) {
?>
        <option value="<?php echo $file; ?>"><?php echo $template; ?></option>
<?php
}
?>
    </select><br />
    <input type="button" class="button" onclick="editPage()" value="Save changes" /> or
    <a href="javascript:;" onclick="dropPage()">drop page</a>
</div>

<div class="stickynote">
    <div><strong>Pod Pages are similar to WordPress pages, but also support PHP and wildcard URLs.</strong></div>
    <div style="margin-top:10px">To handle the URL of http://yoursite.com/history/, you'd create a Pod Page named <strong>history</strong></div>
    <div style="margin-top:10px">A single wildcard Pod Page can handle multiple URLs. For example, the Pod Page <strong>history/*</strong> will be used for any URL beginning with http://yoursite.com/history/ like http://yoursite.com/history/ancient-egypt/</div>
</div>