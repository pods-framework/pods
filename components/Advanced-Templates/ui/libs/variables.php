<div style="padding: 3px 0 10px;">
    <button type="button" onclick="pat_expandVariables();" class="button" style="float:right;"><i class="icon-resize-full"></i> Expand All</button>
    <button type="button" onclick="pat_contractVariables();" class="button" style="float:right; margin-right: 10px;"><i class="icon-resize-small"></i> Contract All</button>
    <button type="button" onclick="pat_addVariable();" class="button"><i class="icon-plus"></i> Add Attribute</button>
</div>
<div id="variablePane">
<?php

    $types = array(
        'Text Field',
        'Text Box',
        'Dropdown',
        'Checkbox',
        'Radio',
        'Color Picker',
        'File',
        'Page Selector',
        //'Custom',
    );
    //vardump($podTemplate, false);
    if(!empty($podTemplate['variable'])){
        foreach($podTemplate['variable'] as $key=>$var){
            $default = '';
            if(isset($podTemplate['variableDefault'][$key])){
                $default = $podTemplate['variableDefault'][$key];
            }
            $label = ucwords($var);
            if(!empty($podTemplate['label'][$key])){
                $label = $podTemplate['label'][$key];
            }
            $info = '';
            if(!empty($podTemplate['variableInfo'][$key])){
                $info = $podTemplate['variableInfo'][$key];
            }
            $tabgroup = 'General Settings';
            $tabGroupShown = 'block';      
            $tabGroupLabel = 'Group';
            if(!empty($podTemplate['tabgroup'][$key])){
                $tabgroup = $podTemplate['tabgroup'][$key];                
            }
            echo '<div id="'.$key.'" class="attributeItem">';


            echo '<div class="attribute-row-left">';
                echo '<div class="attributeField"><label for="label'.$key.'">Label</label><input class="labelbox" ref="'.$key.'" type="text" value="'.$label.'" id="name'.$key.'" name="data[label]['.$key.']" style="width: 100px;"></div>';                
                echo '<div class="attributeField tiny"><label for="slug'.$key.'">Slug</label><input class="slugbox" ref="'.$key.'" type="text" value="'.$var.'" id="slug'.$key.'" name="data[variable]['.$key.']" style="width: 100px;"></div>';                
                echo '<div class="attributeField"><label for="type'.$key.'">Type</label><select name="data[type]['.$key.']" id="type'.$key.'" style="width: 100px;">';                
                foreach($types as $type){
                    $sel = '';
                    if($podTemplate['type'][$key] == $type){
                        $sel = 'selected="selected"';
                    }
                    echo '<option value="'.$type.'" '.$sel.'>'.$type.'</option>';
                }
                echo '</select></div>';
                $sel = '';
                if(!empty($podTemplate['isMultiple'][$key])){
                    $sel = 'checked="checked"';
                }
                echo '<div class="attributeField extend"><label for="multiple'.$key.'">Mulitple</label><input type="checkbox" class="multi-check" value="1" id="multiple'.$key.'" ref="'.$key.'" name="data[isMultiple]['.$key.']" '.$sel.' /></div>';
                        $showGroup = 'display:none;';
                        if(!empty($podTemplate['isMultiple'][$key])){
                           $showGroup = '';
                           $tabGroupShown = 'none';
                        }
                        echo '<div class="attributeField extend"><span id="group'.$key.'" style="'.$showGroup.'"><label for="select_'.$key.'">Group with</label>';
                        echo '<select id="select_multiple'.$key.'" class="groupSelect" name="data[group]['.$key.']">';
                        if(!empty($podTemplate['isMultiple'][$key])){
                            foreach($podTemplate['isMultiple'] as $mkey=>$mval){
                                $sel = '';
                                if(!empty($podTemplate['isMultiple'][$key])){
                                    if($podTemplate['group'][$key] == $mkey){
                                        $sel = 'selected="selected"';
                                    }
                                }
                                echo '<option ref="'.$key.'" value="'.$mkey.'" '.$sel.'>'.$podTemplate['variable'][$mkey].'</option>';
                                //echo '<option value="'.$mkey.'" '.$sel.'>'.$podTemplate['group'][$mkey].' -- '.$mkey.' -- '.$key.'</option>';

                            }
                        }
                        echo '</select></span>';
                echo '</div>';
                
                if(!empty($podTemplate['group'])){
                    if(in_array($key, $podTemplate['group'])){
                        $tabGroupLabel = 'Group label';
                        $tabGroupShown = 'block';
                        if($tabgroup == 'General Settings'){
                            $tabgroup = $label;
                        }
                    }
                }
                
                echo '<div class="attributeField" id="tabgroup'.$key.'" style="display:'.$tabGroupShown.'"><label for="tabgroupfield'.$key.'">'.$tabGroupLabel.'</label><input class="tabgroupbox" ref="'.$key.'" type="text" value="'.$tabgroup.'" id="tabgroupfield'.$key.'" name="data[tabgroup]['.$key.']" style="width: 100px;"></div>';
            echo '</div>';
            
            echo '<div class="attribute-row-right">';
                echo '<div class="attributeField extend"><label for="info'.$key.'">Info</label><textarea id="info'.$key.'" name="data[variableInfo]['.$key.']">'.htmlspecialchars($info).'</textarea></div>';
                echo '<div class="attributeField"><label for="default'.$key.'">Default</label><textarea id="default'.$key.'" name="data[variableDefault]['.$key.']">'.htmlspecialchars($default).'</textarea></div>';            
            echo '</div>';
            echo '<div class="clear"></div>';
            echo '<div class="attributeFooter">';                
                echo '<a class="removal button button-sml" href="#" onclick="jQuery(this).slideUp(130,function(){jQuery(this).parent().find(\'.confirm\').slideDown(130)}); return false;"><i class="icon-remove"></i> Remove Attribute&nbsp;</a>';
                echo '<a class="confirm button button-sml" href="#" onclick="jQuery(\'#'.$key.'\').slideUp(130, function(){jQuery(this).remove()}); return false;" style="display:none;"><i class="icon-check"></i> Confirm?&nbsp;</a> <a class="confirm button button-sml" href="#" onclick="jQuery(this).parent().find(\'.confirm\').slideUp(130, function(){jQuery(this).parent().find(\'.removal\').slideDown(130)}); return false;" style="display:none;"><i class="icon-share-alt"></i> Cancel&nbsp;</a>';
            echo '</div>';
        echo '</div>';
        }
    }
?>
</div>

