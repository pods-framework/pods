<div style="padding: 3px 0 10px;">
    <button type="button" onclick="pat_addAsset();" class="button"><?php echo __( 'Add Asset', 'pods'); ?></button>
</div>
<div id="assetPane">
    <?php
    if(!empty($podTemplate['assetLabel'])){
     foreach($podTemplate['assetLabel'] as $assetKey=>$Label){
        echo '<div id="'.$assetKey.'" class="attributeItem assetItem">';
            echo '<label for="lable_'.$assetKey.'">'.__( 'Label', 'pods').': </label>';
                echo '<input type="text" value="'.$Label.'" name="data[assetLabel]['.$assetKey.']" style="width:70px;margin-right:20px" id="lable_'.$assetKey.'" class="assetlabel">';
            echo '<label for="upload_'.$assetKey.'">'.__( 'File', 'pods').': </label>';
                echo '<input type="text" value="'.$podTemplate['assetURL'][$assetKey].'" name="data[assetURL]['.$assetKey.']" class="fileURL" style="width:270px;" id="upload_'.$assetKey.'">';
            echo '<input type="button" value="'.__( 'Browse &amp; Upload', 'pods').'" id="button_'.$assetKey.'" class="button button-sml upload_file">';
            echo ' <a onclick="jQuery(\'#'.$assetKey.'\').remove(); return false;" href="#">'.__( 'Remove', 'pods').'</a>';
        echo '</div>';

     }
    }
    ?>
</div>