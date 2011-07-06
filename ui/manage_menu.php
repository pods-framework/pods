<?php
if (!wp_script_is('pods-ui', 'queue') && !wp_script_is('pods-ui', 'to_do') && !wp_script_is('pods-ui', 'done'))
    wp_print_scripts('pods-ui');
?>
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/ui/style.css" />
<script type="text/javascript">
var api_url = "<?php echo PODS_URL; ?>/ui/ajax/api.php";
var menu_id;
var add_or_edit;

jQuery(function() {
    jQuery(".btn").live("click", function() {
        menu_id = jQuery(this).parent("div").attr("mid");
        var classname = jQuery(this).attr("class").substr(4);

        if ("addnew" == classname) {
            resetForm();
            if ("1" == menu_id) {
                jQuery("#menu_edit").attr("disabled", "disabled");
                jQuery("#menuBox").jqmShow();
            }
            else {
                jQuery("#menu_edit").removeAttr("disabled");
                loadMenu();
            }
        }
        else if ("dropme" == classname) {
            dropMenu();
        }
    });
    jQuery("#menuBox").jqm();
});

function resetForm() {
    add_or_edit = "add";
    jQuery("#menu_id").val("");
    jQuery("#menu_uri").val("");
    jQuery("#menu_title").val("");
    jQuery("#menu_add").attr("checked", "checked");
}

function addOrEditMenu() {
    if ("add" == add_or_edit) {
        addMenu();
    }
    else {
        editMenu();
    }
}

function loadMenu() {
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=load_menu_item&id="+menu_id,
        success: function(msg) {
            if (!is_error(msg)) {
                var json = eval('('+msg+')');
                var uri = (null == json.uri) ? "" : json.uri;
                var title = (null == json.title) ? "" : json.title;
                jQuery("#menu_id").val(menu_id);
                jQuery("#menu_uri").val(uri);
                jQuery("#menu_title").val(title);
                jQuery("#menuBox").jqmShow();
            }
        }
    });
}

function addMenu() {
    var parent_menu_id = menu_id;
    var menu_uri = jQuery("#menu_uri").val();
    var menu_title = jQuery("#menu_title").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_menu_item&parent_menu_id="+parent_menu_id+"&menu_uri="+menu_uri+"&menu_title="+encodeURIComponent(menu_title),
        success: function(msg) {
            if (!is_error(msg)) {
                var menu_id = msg;
                var html = '<div class="menu-item" mid="'+menu_id+'"><div class="btn addnew"></div><div class="btn dropme"></div><span class="menu-title">'+menu_title+'</span></div>';
                jQuery("div.menu-item[mid='"+parent_menu_id+"']").append(html);
                jQuery("#menuBox").jqmHide();
            }
        }
    });
}

function editMenu() {
    var menu_uri = jQuery("#menu_uri").val();
    var menu_title = jQuery("#menu_title").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_menu_item&id="+menu_id+"&menu_uri="+menu_uri+"&menu_title="+encodeURIComponent(menu_title),
        success: function(msg) {
            if (!is_error(msg)) {
                jQuery("div.menu-item[mid='"+menu_id+"'] > span.menu-title").html(menu_title);
                jQuery("#menuBox").jqmHide();
            }
        }
    });
}

function dropMenu() {
    if (confirm("Do you really want to drop this menu and all its children?")) {
        jQuery.ajax({
            type: "post",
            url: api_url,
            data: "action=drop_menu_item&id="+menu_id,
            success: function(msg) {
                if (!is_error(msg)) {
                    jQuery("div.menu-item[mid='"+menu_id+"']").remove();
                }
            }
        });
    }
}
</script>

<div class="wrap pods_admin">
    <h2>Menu Editor</h2>
    <div class="updated">
        <p>This feature has been deprecated. Please use <strong>Appearance > Menus</strong> in WordPress 3.0.</p>
    </div>
<?php
$last_depth = -1;
if ($menu = build_nav_array('<root>', 0)) {
    foreach ($menu as $key => $val) {
        $id = $val['id'];
        $uri = $val['uri'];
        $title = esc_html($val['title']);
        $depth = $val['depth'];
        $diff = ($depth - $last_depth);
        $last_depth = $depth;

        if (0 > $diff) {
            for ($i = $diff; $i <= 0; $i++) {
                echo '</div>';
            }
        }
        elseif (0 == $diff) {
            echo '</div>';
        }

        $show_dropme = (1 == $id) ? '' : "<div class='btn dropme'></div>";
        echo "<div class='menu-item' mid='$id'><div class='btn addnew'></div>$show_dropme<span class='menu-title'>$title</span>";
    }

    for ($i = 0; $i <= $depth; $i++) {
        echo '</div>';
    }
}
?>
    <!-- Begin popups -->
    <div id="menuBox" class="jqmWindow">
        <input type="hidden" id="add_or_edit" value="" />

        <div class="leftside">Link/URI</div>
        <div class="rightside">
            <input type="text" id="menu_uri" value="" />
            <input type="hidden" id="menu_id" value="" />
        </div>

        <div class="leftside">Title</div>
        <div class="rightside">
            <input type="text" id="menu_title" value="" />
        </div>

        <div align="center">
            <input type="radio" id="menu_add" name="addedit" value="add" onclick="add_or_edit='add'"> Add child &nbsp; &nbsp;
            <input type="radio" id="menu_edit" name="addedit" value="edit" onclick="add_or_edit='edit'"> Edit current &nbsp; &nbsp;
            <input type="button" class="button" onclick="addOrEditMenu()" value="Save item" />
        </div>

        <div class="clear"><!--clear--></div>
    </div>
</div>