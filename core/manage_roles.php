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
    jQuery("#roleTable .all_roles").each(function() {
        var theval = "";
        var the_role = jQuery(this).html();
        jQuery("#roleTable input."+the_role+":checked").each(function() {
            var the_priv = jQuery(this).parent().parent().attr("id");
            theval += the_priv + ",";
        });
        theval = theval.slice(0, -1);
        data[i] = the_role + "=" + encodeURIComponent(theval);
        i++;
    });

    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/edit.php",
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
    <table id="roleTable" cellpadding="0" cellspacing="0">
        <tr>
            <td><!--privilege--></td>
<?php
$all_privs = array(
    array('name' => 'manage_pods', 'label' => 'Manage Pods'),
    array('name' => 'manage_templates', 'label' => 'Manage Templates'),
    array('name' => 'manage_pod_pages', 'label' => 'Manage Pod Pages'),
    array('name' => 'manage_helpers', 'label' => 'Manage Helpers'),
    array('name' => 'manage_roles', 'label' => 'Manage Roles'),
    array('name' => 'manage_settings', 'label' => 'Manage Settings'),
    array('name' => 'manage_content', 'label' => 'Manage All Content'),
    array('name' => 'manage_packages', 'label' => 'Manage Packages'),
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
            <td class="all_roles"><?php echo $role; ?></td>
<?php
        }
    }
}
?>
        </tr>
<?php
foreach ($all_privs as $priv)
{
    $zebra = ('zebra' == $zebra) ? '' : 'zebra';
?>
        <tr id="<?php echo $priv['name']; ?>" class="<?php echo $zebra; ?>">
            <td><?php echo $priv['name']; ?></td>
<?php
    if (isset($user_roles))
    {
        foreach ($user_roles as $role => $junk)
        {
            if ('administrator' != $role)
            {
                $checked = '';
                $pods_role = $pods_roles[$role];
                if (isset($pods_role) && false !== array_search($priv['name'], $pods_role))
                {
                    $checked = ' checked';
                }
?>
            <td><input type="checkbox" class="<?php echo $role; ?>"<?php echo $checked; ?> /></td>
<?php
            }
        }
    }
?>
        </tr>
<?php
}
?>
    </table>
    <input type="button" class="button" onclick="editRoles()" value="Save roles" />
</div>
