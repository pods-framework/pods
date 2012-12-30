<?php

    $Title = '<span><strong id="templateTitle">Untitled Template</strong></span>';
    $podTemplate = array();
    if(!empty($_GET['edit'])){
        // load the stuffs
        $post = get_post($_GET['edit']);        
        $podTemplate = get_post_meta($_GET['edit'],'_pods_adv_template', true);
        $Title = '<span><strong id="templateTitle">'.$post->post_title.'</strong> - '.$post->post_name.'</span>';
        $podTemplate['ID'] = $post->ID;
    }else{
        $podTemplate['ID'] = false;
        $podTemplate['slug'] = uniqid('pt');
    }
    if(!isset($podTemplate['_showhelp__'])){
        $podTemplate['_showhelp__'] = 0;
    }    
?>
    <form action="admin.php?page=pods-component-advanced-templates" method="post" id="editor-form">
    <?php wp_nonce_field('pat-edit-template'); ?>
        <div class="header-nav">
            <div class="pat-logo-icon" style="background: url('<?php echo PODS_URL . 'ui/images/icon32.png'; ?>') center center no-repeat;"></div>
            <ul class="editor-section-tabs navigation-tabs">
                <li><a href="#php">PHP</a></li>
                <li><a href="#css">CSS</a></li>
                <li><a href="#html">HTML</a></li>
                <li><a href="#js">JS</a></li>
                <li class="divider-vertical"></li>
                <li><?php echo $Title; ?></li>
                <li class="divider-vertical"></li>
                <li class="fbutton"><button id="element-apply" type="button" class="button"><?php echo __( 'Apply', 'pods'); ?></button></li>
                <li class="fbutton"><button type="submit" class="button"><?php echo __( 'Save', 'pods'); ?></button></li>
                <li class="divider-vertical"></li><?php
                /*
                <li class="fbutton"><button id="help-toggle" type="button" class="button <?php if(!empty($podTemplate['_showhelp__'])){ echo 'active'; } ?>">Revisions</button></li>
                <li class="divider-vertical"></li> */
                ?>
                <li class="fbutton"><button id="help-toggle" type="button" class="button <?php if(!empty($podTemplate['_showhelp__'])){ echo 'active'; } ?>"><?php echo __( 'Help', 'pods'); ?></button></li>
                <li class="divider-vertical"></li>
                <li><span id="saveIndicator"><progress>Saving</progress></span></li>
            </ul>
        </div>
        <div class="side-controls">
            <ul class="element-config-tabs navigation-tabs">
                <li class="active"><a class="control-settings-icon" href="#config" title="Settings"><span>Settings</span></a></li>                
                <li><a class="control-libraries-icon" href="#libraries" title="Libraries"><span>Libraries</span></a></li>
                <li><a class="control-assets-icon" href="#assets" title="Assets"><span>Assets</span></a></li>
            </ul>
        </div>
        <div class="editor-pane" style="<?php if(empty($podTemplate['_showhelp__'])){ echo 'right:0;'; }; ?>">            
            <div id="config" class="editor-tab active editor-setting editor-config">
                <div class="editor-tab-content">
                    <h3><?php echo __( 'Template Config', 'pods'); ?> <small>Settings and template display options</small></h3>
                    <?php include PODS_DIR . 'components/Advanced-Templates/ui/libs/settings.php'; ?>
                </div>
            </div>
            <div id="libraries" class="editor-tab editor-setting editor-libraries">
                <div class="editor-tab-content">
                    <h3><?php echo __( 'Libraries', 'pods'); ?> <small>Scripts and styles to be included in the header</small></h3>                    
                    <?php include PODS_DIR . 'components/Advanced-Templates/ui/libs/libraries.php'; ?>
                </div>
            </div>
            <div id="assets" class="editor-tab editor-setting editor-assets">
                <div class="editor-tab-content">
                    <h3><?php echo __( 'Assets', 'pods'); ?> <small>Additional files and scripts to be used by your template.</small></h3>                    
                    <?php include PODS_DIR . 'components/Advanced-Templates/ui/libs/assets.php'; ?>
                </div>
            </div>
            <div id="php" class="editor-tab editor-code editor-php">
                <label for="code-php">PHP</label>
                <textarea id="code-php" name="data[phpCode]"><?php if(!empty($podTemplate['phpCode'])){ echo htmlspecialchars($podTemplate['phpCode']); } ;?></textarea>
            </div>
            <div id="css" class="editor-tab editor-code editor-css">
                <label for="code-css">CSS</label>
                <textarea id="code-css" name="data[cssCode]"><?php if(!empty($podTemplate['cssCode'])){ echo $podTemplate['cssCode']; } ;?></textarea>
            </div>
            <div id="html" class="editor-tab editor-code editor-html">
                <label for="code-html">HTML</label>
                <textarea id="code-html" name="data[htmlCode]"><?php if(!empty($podTemplate['htmlCode'])){ echo htmlspecialchars($podTemplate['htmlCode']); } ;?></textarea>
            </div>
            <div id="js" class="editor-tab editor-code editor-js">
                <label for="code-js">JavaScript</label>
                <textarea id="code-js" name="data[javascriptCode]"><?php if(!empty($podTemplate['javascriptCode'])){ echo $podTemplate['javascriptCode']; } ;?></textarea>
            </div>            
        </div>
        <?php
        /*
        <div class="editor-revisions">
            <div class="editor-tab-content">
                <h3>Revisions</h3>
            </div>            
        </div>
         */
        ?>
        <div class="help-pane editor-help" style="<?php if(empty($podTemplate['_showhelp__'])){ echo 'display:none;'; }; ?>">
            <label>Help</label>
            <div class="help-wrapper">
                <h3>Help Docs for Building Templates</h3>
                <h4>Magic Tags</h4>
                <p>The editors support color highlighting and code hints for magic tags and some new tags.</p>
                <p><span class="cm-magic-at">{@fieldname}</span> : standard tags</p>
                <p><span class="cm-internal">{_id_}</span> : instance id</p>
                <p><span class="cm-include">{&user_field}</span> : field from the current user</p>
                <p><span class="cm-command">[if]</span> : start an if</p>
                <p><span class="cm-command">[else]</span> : start an else</p>
                <p><span class="cm-command">[/if]</span> : end an if</p>
                <p><span class="cm-include">[[slug]]</span> : slug of an asset</p>
                <h4>Looping</h4>
                By default, the whole template is looped per record found.
                You can specify the loop by wrapping the looped code in [loop][/loop] tags. Any code before and after will not be looped per record.
                You should keep your {@fieldname} tags within the loop.
                <p><span class="cm-command">[loop]</span> : starts the loop</p>
                <p><span class="cm-command">[/loop]</span> : ends the loop</p>                
            </div>
        </div>
    </form>
