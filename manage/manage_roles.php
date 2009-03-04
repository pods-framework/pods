<?php
// Get all available WP roles
$user_roles = get_option($table_prefix . 'user_roles');
?>

<!--
==================================================
Begin role area
==================================================
-->
<script type="text/javascript">
function editRoles() {
    var data = new Array();

    var i = 0;
    jQuery("#roleArea .form").each(function() {
        var theval = "";
        var classname = jQuery(this).attr("class").split(" ");
        jQuery("." + classname[2] + " .active").each(function() {
            theval += jQuery(this).attr("value") + ",";
        });
        theval = theval.substr(0, theval.length - 1);
        data[i] = classname[2] + "=" + encodeURIComponent(theval);
        i++;
    });

    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "action=editroles&auth="+auth+"&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                alert("Success!");
            }
        }
    });
    return false;
}
</script>

<!--
==================================================
Role HTML
==================================================
-->
<div id="roleArea" class="area hidden">
    <div class="tips">Use the Role Manager plugin to add new roles. Admins have total access.</div>
<?php
$all_privs = array(
    array('name' => 'manage_pods', 'label' => 'Manage Pods'),
    array('name' => 'manage_podpages', 'label' => 'Manage PodPages'),
    array('name' => 'manage_helpers', 'label' => 'Manage Helpers'),
    array('name' => 'manage_settings', 'label' => 'Manage Settings'),
    array('name' => 'manage_content', 'label' => 'Manage All Content'),
    array('name' => 'manage_roles', 'label' => 'Manage Roles'),
    array('name' => 'manage_menu', 'label' => 'Manage Menu')
);

foreach ($datatypes as $id => $dtname)
{
    $all_privs[] = array('name' => "pod_$dtname", 'label' => "Access: $dtname");
}

if (isset($user_roles))
{
    foreach ($user_roles as $role => $junk)
    {
        if ('administrator' != $role)
        {
?>
    <div style="float:left; width:32%; margin:0 5px 5px 0">
        <h4><?php echo $role; ?></h4>
        <div class="form pick <?php echo $role; ?>">
<?php
            foreach ($all_privs as $priv)
            {
                $active = '';
                $pods_role = $pods_roles[$role];
                if (isset($pods_role) && false !== array_search($priv['name'], $pods_role))
                {
                    $active = ' active';
                }
?>
            <div class="option<?php echo $active; ?>" value="<?php echo $priv['name']; ?>"><?php echo $priv['label']; ?></div>
<?php
            }
?>
        </div>
    </div>
<?php
        }
    }
}
?>
    <div class="clear"><!--clear--></div>
    <input type="button" class="button" onclick="editRoles()" value="Save roles" />
</div>

