<div id="settingsPane" class="config"><?php

//Get pods
$api = pods_api();
$_pods = $api->load_pods();

// push Pods to javascript array for colour coding.

echo "\n<script type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "var fields = [";
;
$podFields = array();
foreach($_pods as $pod){
    if((int)$podTemplate['pod'] === (int)$pod['id']){
        
        if(!empty($pod['options']['supports_title']))
            $podFields[] = "'@title'";

        if(!empty($pod['options']['supports_editor']))
            $podFields[] = "'@content'";
        
        foreach($pod['fields'] as $podField=>$fiedSet){
            $podFields[] = "'@".$podField."'";
        }
    }
}
echo implode(',', $podFields);
echo "];\n";
echo "var magics = [";
$podMagics = array(
    "'_id_'",
    "'&id'",
    "'&user_login'",
    "'&user_nicename'",
    "'&display_name'"
);
echo implode(',', $podMagics);
echo "];\n/* ]]> */\n";
echo "</script>";

$cats = array();
if(!empty($podTemplates)){
    foreach($podTemplates as $el){
        $cat = strtolower($el['category']);
        $cats[$cat] = '"'.$cat.'"';
    }
}


echo pat_configOption('ID', 'ID', 'hidden', 'ID', $podTemplate);
echo pat_configOption('name', 'name', 'textfield', 'Template Name', $podTemplate);
echo pat_configOption('description', 'description', 'textarea', 'Template Description', $podTemplate);
//echo pat_configOption('category', 'category', 'textfield', 'Category', $podTemplate, false, 'autocomplete="off"');
echo pat_configOption('slug', 'slug', 'textfield', 'Slug / Shortcode', $podTemplate);

echo '<div class="pat_configOption podselect" id="config_pod">';
echo '<label>Pod</label>';
    echo '<select name="data[pod]" id="pod" />';
    echo '<option value="">Select Pod</option>';
    foreach($_pods as $pod){
        $sel = '';
        if(isset($podTemplate['pod'])){
            if((int)$podTemplate['pod'] === (int)$pod['id'])
                $sel = 'selected="selected"';
        }
        echo '<option value="'.$pod['id'].'" '.$sel.'>'.$pod['name'].'</option>';
    }
    echo '</select>';
echo '</div>';

?>
</div>
<script type="text/javascript">
    
    jQuery('#settingsPane').on('change', '#pod', function(){
        var pod = jQuery('#pod').val();
        var postdata = {
            action : 'pods_admin_components',
            component: '<?php echo $component; ?>',
            method : '<?php echo $method; ?>',
            process  : 'swap_pod',
            _wpnonce : '<?php echo wp_create_nonce( 'pods-component-' . $component . '-' . $method ); ?>',
            pod : this.value
        };

        jQuery.ajax( {
            type : 'POST',
            url : ajaxurl + '?pods_ajax=1',
            dataType : 'json',
            cache : false,
            data : postdata,
            success : function ( d ) {
                fields = d.data;
            },
            error : function () {
                fields = [];
            }
        } );  
    });
    
function pat_reloadHelp(eid){

    var debugmode = '';

        jQuery('#saveIndicator').fadeIn(200);


        var postdata = {
            action   : 'pods_admin_components',
            component: '<?php echo $component; ?>',
            method   : '<?php echo $method; ?>',
            process  : 'apply_changes',
            _wpnonce : '<?php echo wp_create_nonce( 'pods-component-' . $component . '-' . $method ); ?>',
            data     : jQuery('#editor-form').serialize()
        };

        jQuery.ajax( {
            type : 'POST',
            url : ajaxurl + '?pods_ajax=1',
            dataType : 'json',
            cache : false,
            data : postdata,
            success : function ( d ) {
                var newtitle = d.title;
                var id = jQuery('#ID');
                if(id.val().length <= 0){
                    id.val(d.id);
                }
                jQuery('#templateTitle').html(newtitle);
                jQuery('#saveIndicator').fadeOut(200);
            },
            error : function () {
                fields = [];
            }
        } );

};    
   
</script>    