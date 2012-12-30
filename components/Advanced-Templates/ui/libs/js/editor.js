/* Setup Editors */

var mustache = function(stream, state) {
    var ch;
    
    /* pods field names highlighting */
    if(fields.length > 0){
        CodeMirror.xmlHints['{'] = [''].concat(fields.concat(magics));
        for(f=0;f<fields.length;f++){
            if (stream.match("{"+fields[f]+"}")) {
                return "magic-at";
            }
        };
    }else{
        CodeMirror.xmlHints['{'] = magics;
    }
    
    /* Built in highlighting for the template engine */
    if (stream.match("[once]") || stream.match("[/once]") || stream.match("[else]") || stream.match("[/if]") || stream.match("[loop]") || stream.match("[/loop]")) {
        return "command";
    }
    if (stream.match("{_")) {
        while ((ch = stream.next()) != null)
            if (ch == "_" && stream.next() == "}") break;
        stream.eat("}");        
        return "internal";
    }
    
    if (stream.match("[if")) {                  
        while ((ch = stream.next()) != null)
            if (ch == "]" && stream.next() != "]") break;
        stream.eat("]");
        return "command";
    }
    if (stream.match("{&")) {
        while ((ch = stream.next()) != null)
            if (ch == "}") break;
        stream.eat("}");
        return "include";
    }
    if (stream.match("[[")) {
        while ((ch = stream.next()) != null)
            if (ch == "]" && stream.next() == "]") break;
        stream.eat("]");
        return "include";
    }
    while (stream.next() != null && 
        !stream.match("{", false) && 
        !stream.match("[[", false) && 
        !stream.match("[once]", false) && 
        !stream.match("[/once]", false) && 
        !stream.match("[loop]", false) && 
        !stream.match("[/loop]", false) && 
        !stream.match("[if", false) && 
        !stream.match("[else]", false) && 
        !stream.match("[/if]", false) ) {}
    return null;
};

var phpeditor = CodeMirror.fromTextArea(document.getElementById("code-php"), {
    lineNumbers: true,
    matchBrackets: true,
    mode: "text/x-php",
    indentUnit: 4,
    indentWithTabs: true,
    enterMode: "keep",
    tabMode: "shift",
    lineWrapping: true,
    onBlur: function(){
        phpeditor.save();
    }
});
        
CodeMirror.defineMode("cssCode", function(config) {
    return CodeMirror.multiplexingMode(
    CodeMirror.getMode(config, "text/css"),
    {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
        mode: CodeMirror.getMode(config, "text/x-php"),
        delimStyle: "phptag"}
);
});
CodeMirror.defineMode("cssMustache", function(config, parserConfig) {
    var mustacheOverlay = {
        token: mustache
    };
    return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "cssCode"), mustacheOverlay);
});            
var csseditor = CodeMirror.fromTextArea(document.getElementById("code-css"), {
    lineNumbers: true,
    matchBrackets: true,
    mode: "cssMustache",
    indentUnit: 4,
    indentWithTabs: true,
    enterMode: "keep",
    tabMode: "shift",
    lineWrapping: true,
    onBlur: function(){
        csseditor.save();
    },
    extraKeys: {
        "'{'": function(cm) { CodeMirror.xmlHint(cm, '{'); }
    }
});
            
CodeMirror.defineMode("mustache", function(config, parserConfig) {
    var mustacheOverlay = {
        token: mustache
    };
    return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "application/x-httpd-php"), mustacheOverlay);
});
var htmleditor = CodeMirror.fromTextArea(document.getElementById("code-html"), {
    lineNumbers: true,
    matchBrackets: true,
    mode: "mustache",
    indentUnit: 4,
    indentWithTabs: true,
    enterMode: "keep",
    tabMode: "shift",
    lineWrapping: true,
    onBlur: function(){
        htmleditor.save();
    },
    extraKeys: {
        "'{'": function(cm) { CodeMirror.xmlHint(cm, '{'); }
    }    
});
            
CodeMirror.defineMode("jsCode", function(config) {
    return CodeMirror.multiplexingMode(
    CodeMirror.getMode(config, "text/javascript"),
    {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
        mode: CodeMirror.getMode(config, "text/x-php"),
        delimStyle: "phptag"}
);
});
CodeMirror.defineMode("jsMustache", function(config, parserConfig) {
    var mustacheOverlay = {
        token: mustache
    };
    return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "jsCode"), mustacheOverlay);
});            
var jseditor = CodeMirror.fromTextArea(document.getElementById("code-js"), {
    lineNumbers: true,
    matchBrackets: true,
    mode: "jsMustache",
    indentUnit: 4,
    indentWithTabs: true,
    enterMode: "keep",
    tabMode: "shift",
    lineWrapping: true,
    onBlur: function(){
        jseditor.save();
    },
    extraKeys: {
        "'{'": function(cm) { CodeMirror.xmlHint(cm, '{'); }
    }
});
            
/* Setup Navigation Tabs */
jQuery('.navigation-tabs li:not(.fbutton) a').click(function(e){
    e.preventDefault();
    var alltabs = jQuery('.navigation-tabs li');
    var clicked = jQuery(this);
    alltabs.removeClass('active');
    clicked.parent().addClass('active');
    var panel = jQuery(clicked.attr('href'));
    jQuery('.editor-tab').hide();
    panel.show();
    panel.find('textarea').focus();
    phpeditor.refresh();
    csseditor.refresh();
    htmleditor.refresh();
    jseditor.refresh();
})
/* Apply podTemplate Changes & Reload Help */
jQuery('#element-apply').click(function(e){
    pat_reloadHelp();
});
            
/* Utility Functions */
function randomUUID() {
    var s = [], itoh = '0123456789ABCDEF';
    for (var i = 0; i <6; i++) s[i] = Math.floor(Math.random()*0x10);
    return s.join('');
}
function pat_togglehelp(){

    if(jQuery('.editor-pane').css('right') == '0px'){
        jQuery('.editor-pane').css({right: '50%'});
        jQuery('#setShowHelp').val('1');
    }else{
        jQuery('.editor-pane').css({right: 0});
        jQuery('#setShowHelp').val('0');
    }
    jQuery('.help-pane').toggle();
    jQuery('#help-toggle').toggleClass('active');
    phpeditor.refresh();
    csseditor.refresh();
    htmleditor.refresh();
    jseditor.refresh();

}
function pat_addAsset(){
    var rowID = randomUUID();
    jQuery('#assetPane').append('<div class="attributeItem assetItem" id="'+rowID+'"><label for="lable_'+rowID+'">Label: </label><input type="text" id="lable_'+rowID+'" style="width:70px;margin-right:20px" class="assetlabel" name="data[assetLabel]['+rowID+']" value="" /><label for="upload_'+rowID+'">File: </label><input id="upload_'+rowID+'" type="text" style="width:270px;" class="fileURL" name="data[assetURL]['+rowID+']" value="" /><input class="button button-sml upload_file" id="button_'+rowID+'" type="button" value="Browse & Upload" /> <a href="#" onclick="jQuery(\'#'+rowID+'\').remove(); return false;">Remove</a>');
}


jQuery(document).ready(function() {

    jQuery('.upload_file').live('click', function() {
     formfield = jQuery(this).parent().find('.fileURL');
     tb_show('', 'media-upload.php?type=file&amp;post_id=0&amp;TB_iframe=true');

        window.send_to_editor = function(html) {
         linkurl = jQuery(html).attr('href');
         jQuery(formfield).val(linkurl);
         tb_remove();
        }

     return false;
    });

});
/* ready calls */
jQuery(document).ready(function(){
    jQuery('#help-toggle').click(pat_togglehelp);
    jQuery( "#variablePane" ).sortable();
    jQuery( "#jslibraryPane" ).sortable();
    jQuery( "#assetPane" ).sortable();
                
    /* Bind ctr+s & cmd+s for saving*/
                
    jQuery(window).keypress(function(event) {
        if (!(event.which == 115 && event.metaKey) && !(event.which == 19)) return true;
        event.preventDefault();
        htmleditor.save();
        csseditor.save();
        phpeditor.save();
        jseditor.save();
        pat_reloadHelp();
        return false;
    });
 
                
});