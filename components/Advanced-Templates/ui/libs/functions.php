<?php

/**
 * File: Advanced Templates - Utility Functions
 *
 * Author: David Cramer
 * Author URI: http://www.digilab.co.za
 * 
 */


function pat_configOption($ID, $Name, $Type, $Title, $Config, $caption = false, $inputTags = '') {
    
    $Return = '';
    
    switch ($Type) {
        case 'hidden':
            $Val = '';
            if (!empty($Config[$Name])) {
                $Val = $Config[$Name];
            }
            $Return .= '<input type="hidden" name="data[' . $Name . ']" id="' . $ID . '" value="' . $Val . '" />';
            break;
        case 'textfield':
            $Val = '';
            if (!empty($Config[$Name])) {
                $Val = $Config[$Name];
            }
            $Return .= '<label>'.$Title . '</label> <input type="text" name="data[' . $Name . ']" id="' . $ID . '" value="' . $Val . '" '.$inputTags.' />';
            break;
        case 'textarea':
            $Val = '';
            if (!empty($Config[$Name])) {
                $Val = $Config[$Name];
            }
            $Return .= '<label class="confOption-textarea">'.$Title . '</label><textarea class="confOption-textarea" name="data[' . $Name . ']" id="' . $ID . '" cols="30" rows="5">' . htmlentities($Val) . '</textarea><br class="clear" />';
            break;
        case 'radio':
            $parts = explode('|', $Title);
            $options = explode(',', $parts[1]);
            $Return .= '<label class="multiLable">'.$parts[0]. '</label>';
            $index = 1;
            foreach ($options as $option) {
                $sel = '';
                if (!empty($Config[$Name])) {
                    if ($Config[$Name] == $index) {
                        $sel = 'checked="checked"';
                    }
                }else{
                    if(strpos($option, '*') !== false){
                        $sel = 'checked="checked"';
                    }
                    
                }
                if (empty($Config)) {
                    if ($index === 1) {
                        $sel = 'checked="checked"';
                    }
                }
                $option = str_replace('*', '', $option);
                $Return .= '<div class="toggleConfigOption"> <input type="radio" name="data[' . $Name . ']" id="' . $ID . '_' . $index . '" value="' . $index . '" ' . $sel . '/> <label for="' . $ID . '_' . $index . '" style="width:auto;">' . $option . '</label></div>';
                $index++;
            }
            break;
        case 'checkbox':
            $sel = '';
            if (!empty($Config[$Name])) {
                $sel = 'checked="checked"';
            }

            $Return .= '<input type="checkbox" name="data[' . $Name . ']" id="' . $ID . '" value="1" '.$sel.' /><label for="' . $ID . '" style="margin-left: 10px; width: 570px;">'.$Title.'</label> ';
            break;
    }
    $captionLine = '';
    if(!empty($caption)){
        $captionLine = '<div class="pat_captionLine description">'.$caption.'</div>';
    }
    return '<div class="pat_configOption '.$Type.'" id="config_'.$ID.'">' . $Return . $captionLine.'</div>';
}


?>