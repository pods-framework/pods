<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/style.css?r=<?php echo rand(1000, 9999); ?>" />
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/jqmodal.js"></script>
<script type="text/javascript">
var menu_id;
var add_or_edit;

jQuery(function() {
    jQuery(".btn").live("click", function() {
        menu_id = jQuery(this).parent("div").attr("mid");
        var classname = jQuery(this).attr("class").substr(4);

        if ("addnew" == classname) {
            resetForm();
            if ("1" == menu_id) {
                jQuery("#menu_edit").attr("disabled", true);
                jQuery("#menuBox").jqmShow();
            }
            else {
                jQuery("#menu_edit").attr("disabled", false);
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
    jQuery("#menu_add").attr("checked", true);
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
        url: "<?php echo PODS_URL; ?>/ajax/api.php",
        data: "action=load_menu&id="+menu_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var menu_data = eval("("+msg+")");
                var uri = (null == menu_data.uri) ? "" : menu_data.uri;
                var title = (null == menu_data.title) ? "" : menu_data.title;
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
        url: "<?php echo PODS_URL; ?>/ajax/api.php",
        data: "action=save_menu&parent_menu_id="+parent_menu_id+"&menu_uri="+menu_uri+"&menu_title="+encodeURIComponent(menu_title),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var menu_id = msg;
                var html = '<div class="menu-item" mid="'+menu_id+'"><div class="btn addnew"></div><div class="btn dropme"></div><span class="menu-title">'+menu_title+'</span></div>';
                jQuery("div.menu-item[mid="+parent_menu_id+"]").append(html);
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
        url: "<?php echo PODS_URL; ?>/ajax/api.php",
        data: "action=save_menu&id="+menu_id+"&menu_uri="+menu_uri+"&menu_title="+encodeURIComponent(menu_title),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("div.menu-item[mid="+menu_id+"] > span.menu-title").html(menu_title);
                jQuery("#menuBox").jqmHide();
            }
        }
    });
}

function dropMenu() {
    if (confirm("Do you really want to drop this menu and all its children?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo PODS_URL; ?>/ajax/api.php",
            data: "action=drop_menu&id="+menu_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("div.menu-item[mid="+menu_id+"]").remove();
                }
            }
        });
    }
}
</script>

<!--
==================================================
Begin popups
==================================================
-->
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

<!--
==================================================
Begin edit area
==================================================
-->
<div id="menuArea" class="area">
    <h2 class="title" id="editTitle">Menu Editor</h2>
    <div class="tips">Define your site's menu structure, and display it using pods_navigation($uri, $max_depth)</div>
<?php
$last_depth = -1;
if ($menu = build_nav_array('/', 0))
{
    foreach ($menu as $key => $val)
    {
        $id = $val['id'];
        $uri = $val['uri'];
        $title = $val['title'];
        $depth = $val['depth'];
        $diff = ($depth - $last_depth);
        $last_depth = $depth;

        if (0 > $diff)
        {
            for ($i = $diff; $i <= 0; $i++)
            {
                echo '</div>';
            }
        }
        elseif (0 == $diff)
        {
            echo '</div>';
        }

        $show_dropme = (1 == $id) ? '' : "<div class='btn dropme'></div>";
        echo "<div class='menu-item' mid='$id'><div class='btn addnew'></div>$show_dropme<span class='menu-title'>$title</span>";
    }

    for ($i = 0; $i <= $depth; $i++)
    {
        echo '</div>';
    }
}
?>
</div>

