<?php
// Get all available WP roles
global $table_prefix, $pods_roles;
if (empty($pods_roles) && !is_array($pods_roles)) {
    $pods_roles = @unserialize(get_option('pods_roles'));
    if (!is_array($pods_roles))
        $pods_roles = array();
}
$user_roles = get_option($table_prefix . 'user_roles');
?>
<!-- Begin role area -->
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
        url: api_url,
        data: "action=save_roles&_wpnonce=<?php echo wp_create_nonce('pods-save_roles'); ?>&"+data.join("&"),
        success: function(msg) {
            if (!is_error(msg)) {
                alert("Success!");
            }
        }
    });
    return false;
}
</script>

<!-- Role HTML -->

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
    array('name' => 'manage_packages', 'label' => 'Manage Packages')
);

if (isset($datatypes) && is_array($datatypes)) {
    foreach ($datatypes as $id => $dtname) {
        $all_privs[] = array('name' => "pod_$dtname", 'label' => "Access: $dtname");
    }
}

if (isset($user_roles) && is_array($user_roles)) {
    foreach ($user_roles as $role => $junk) {
        if ('administrator' != $role) {
?>
        <td class="all_roles"><?php echo $role; ?></td>
<?php
        }
    }
}
?>
        </tr>
<?php
foreach ($all_privs as $priv) {
    $zebra = empty($zebra) ? 'zebra' : '';
?>
    <tr id="<?php echo $priv['name']; ?>" class="<?php echo $zebra; ?>">
        <td><?php echo $priv['name']; ?></td>
<?php
    if (is_array($user_roles)) {
        foreach ($user_roles as $role => $junk) {
            if ('administrator' != $role) {
                $checked = '';
                if (isset($pods_roles[$role]) && false !== array_search($priv['name'], $pods_roles[$role])) {
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