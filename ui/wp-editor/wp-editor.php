<?php
// TinyMCE Editor API for WordPress
// Developed by Andrew Ozz (azaozz) for WordPress 3.3 (or a future release)

// Usage:
// global $wp_editor;
// $wp_editor->editor($content, $editor_id, $settings, $media_buttons);
if ( !class_exists('WP_Pre_33_Editor') ) :
class WP_Pre_33_Editor {

	var $editor_ids = array();
	var $settings = array();
	var $editor_loaded;
	var $media_buttons;

	function __construct() {
		add_filter( 'tiny_mce_before_init', array($this, 'loaded_test') );
	}

	function wp_default_editor() {
		return wp_default_editor();
	}

	function editor( $content, $editor_id, $settings = array(), $media_buttons = true ) {
        if (function_exists('wp_editor')) {
            $settings['media_buttons'] = $media_buttons;
            wp_editor($content, $editor_id, $settings);
            return;
        }

		$this->editor_ids[] = $editor_id;

		$set = wp_parse_args( $settings,  array(
			'wpautop' => true, // use wpautop?
			'wp_buttons_css' => PODS_URL . '/ui/wp-editor/editor-buttons.css', // styles for both visual and HTML editors buttons
			'editor_class' => 'wp-editor-area',
			'upload_link_title' => 'Upload and insert images or other media',
			'media_buttons_context' => '',
			'textarea_rows' => get_option('default_post_edit_rows', 10)
		) );

		if ( !$set['wpautop'] )
			$set['apply_source_formatting'] = true;

		$rows = (int) $set['textarea_rows'];
		$can_richedit = user_can_richedit();
		$id = $editor_id;
		$default_editor = is_admin() ? wp_default_editor() : 'tinymce';
        $default_editor = 'tinymce'; // default editor not working yet in code, so it's turned off for now
		$toolbar = '';

		if ( !current_user_can( 'upload_files' ) )
			$media_buttons = false;

		$this->media_buttons = $media_buttons;
		echo '<div id="wp-' . $id . '-wrap" class="wp-editor-wrap">';

		if ( !is_admin() && !empty($set['wp_buttons_css']) )
			echo '<style type="text/css" scoped="scoped"> @import url("' . $set['wp_buttons_css'] . '"); ' . "</style>\n";

		if ( $can_richedit || $media_buttons ) {
			$toolbar .= '<div id="wp-' . $id . '-editor-tools" class="wp-editor-tools">';

			if ( $can_richedit ) {
				$html_class = $tmce_class = '';
				if ( 'html' == $default_editor ) {
					add_filter('the_editor_content', 'wp_htmledit_pre');
					$html_class = 'active ';
				} else {
					add_filter('the_editor_content', 'wp_richedit_pre');
					$tmce_class = 'active ';
				}

				$toolbar .= '<a id="' . $id . '-html" class="' . $html_class . 'hide-if-no-js wp-switch-editor" onclick="wpEditor.s(this);return false;">' . __('HTML') . "</a>\n";
				$toolbar .= '<a id="' . $id . '-tmce" class="' . $tmce_class . 'hide-if-no-js wp-switch-editor" onclick="wpEditor.s(this);return false;">' . __('Visual') . "</a>\n";
			}

			if ( $media_buttons ) {
				global $post_ID;

				$href = add_query_arg( array('post_id' => (int) $post_ID, 'TB_iframe' => true), admin_url('media-upload.php') );
				$title = $set['upload_link_title'];
				$button = "<a href='" . esc_url( $href ) . "' id='$id-add_media' class='thickbox' title='$title'><img src='" . esc_url( admin_url('images/media-button-image.gif') ) . "' alt='$title' onclick='return false;' /></a>";

				$toolbar .= '<div id="wp-' . $id . '-media-buttons" class="hide-if-no-js wp-media-buttons">';
				$toolbar .= $set['media_buttons_context'] . apply_filters('wp_upload_buttons', $button, $id);
				$toolbar .= "</div>\n";
			}

			$toolbar .=  "</div>\n";
		}

		$the_editor = apply_filters('the_editor', '<div id="wp-' . $id . '-editor-container" class="wp-editor-container"><textarea class="' . $set['editor_class'] . '" rows="' . $rows . '" cols="40" name="' . $id . '" id="' . $id . '">%s</textarea></div>');
		$the_editor_content = apply_filters('the_editor_content', $content);

		echo apply_filters( 'the_editor_toolbar', $toolbar );
		printf($the_editor, $the_editor_content);
		echo "\n</div>\n\n";

		if ( is_admin() )
			add_action( 'admin_print_footer_scripts', array($this, 'editor_js'), 50 );
		else
			add_action( 'wp_print_footer_scripts', array($this, 'editor_js') );

		unset( $set['wp_buttons_css'], $set['editor_class'], $set['upload_link_title'], $set['media_buttons_context'], $set['textarea_rows'] );
		$this->settings[$editor_id] = $set;
	}

	function loaded_test($r) {
		$this->editor_loaded = true;
		return $r;
	}

	function disable_fullscreen($init) {
		$plugins = preg_split('/[ ,]+/', $init['plugins']);
		$plugins = array_diff( $plugins, array('wpfullscreen') );
        $plugins[] = 'fullscreen';
        $plugins = array_unique($plugins);
		$init['plugins'] = implode($plugins, ',');

		//$init['theme_advanced_buttons1'] = str_replace( ',fullscreen', '', $init['theme_advanced_buttons1'] );

		return $init;
	}

	function editor_js() {
		global $wp_db_version;

		$short_load = false;

		if ( count($this->editor_ids) > 1 ) {
			$ids = $this->editor_ids;
			$id = array_shift($ids);
		} else {
			$id = $this->editor_ids[0];
		}

		if ( !$this->editor_loaded ) {
			if ( !function_exists('wp_tiny_mce') )
				include_once( ABSPATH . 'wp-admin/includes/post.php' );

			if ( !function_exists('submit_button') )
				include_once( ABSPATH . 'wp-admin/includes/template.php' );

            add_filter( 'tiny_mce_before_init', array($this, 'disable_fullscreen') );
            echo '<style type="text/css">.wp-dialog,.alternate{background-color:#F9F9F9;}</style>';

			if ( function_exists('wp_tiny_mce_preload_dialogs') ) {
				add_action( 'wp_footer', 'wp_tiny_mce_preload_dialogs', 30 );
				add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );
			}

			$set = $this->settings[$id];

			$set['elements'] = $id;
			$set['mode'] = 'exact';
			$set['editor_selector'] = null;

			wp_tiny_mce(false, $set);
			wp_print_scripts( array('quicktags', 'editor') );

			if ( !empty($ids) )
				$short_load = $ids;

			$first_load = $id;
			$this->editor_loaded = true;
		} else {
			$short_load = $this->editor_ids;
		}

/* js source

var wpEditor = {
	wpautop : {
		<?php echo $id.':';echo $this->settings[$id]['wpautop'] ? 'true' : 'false'; ? >
	},

	s : function(a) {
		var t = this, aid = a.id, l = aid.length, id = aid.substr(0, l - 5), mode = aid.substr(l - 4),
			I = t.I, ed = tinyMCE.get(id), qttb = 'qt_'+id+'_qtags', dom = tinymce.DOM;

		if ( 'tmce' == mode ) {
			if ( t.wpautop[id] )
				I(id).value = switchEditors.wpautop( I(id).value );

			if ( ed )
				ed.show();
			else
				tinyMCE.execCommand("mceAddControl", false, id);

			dom.hide(qttb);
			dom.addClass(id+'-tmce', 'active');
			dom.removeClass(id+'-html', 'active');

		} else if ( 'html' == mode ) {
			if ( ed ) {
				if ( t.wpautop[id] === false ) {
					switchEditors['old_wp_Nop'] = switchEditors._wp_Nop;
					switchEditors._wp_Nop = function(c){ return c; };
					ed.hide();
					switchEditors._wp_Nop = switchEditors['old_wp_Nop'];
				} else {
					ed.hide();
				}
			}
			dom.show(qttb);
			dom.addClass(id+'-html', 'active');
			dom.removeClass(id+'-tmce', 'active');
		}
	},

	I : function(id) {
		return document.getElementById(id);
	},

	ed_init : function(id, settings) {
		var t = this, set = {};

		settings = settings || {};

		if ( 'undefined' != typeof(tinymce) && !tinyMCE.get(id) ) {

			t.wpautop[id] = settings['wpautop'] ? true : false;

			tinymce.extend( set, tinyMCEPreInit.mceInit, settings );
			set.mode = 'exact';
			set.elements = id;

			tinyMCE.init(set);
		}
	},

	qt_init : function(id, settings) {
		var disabled, name;

		id = id || 'content';
		settings = settings || {};
		disabled = settings.disabled;
		name = 'qt_'+id;

		if ( typeof(QTags) != 'undefined' )
			window[name] = new QTags(name, id, 'wp-'+id+'-editor-container', disabled);

		if ( typeof(tinymce) != 'undefined' )
			tinymce.DOM.hide('qt_'+id+'_qtags');
	}
}

*/
?>

<script type="text/javascript">
<?php

	if ( !is_admin() )
		echo 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
?>

var wpEditor={wpautop:{<?php echo $id.':';echo $this->settings[$id]['wpautop'] ? 'true' : 'false'; ?>},s:function(i){var k=this,d=i.id,e=d.length,c=d.substr(0,e-5),g=d.substr(e-4),j=k.I,h=tinyMCE.get(c),b="qt_"+c+"_qtags",f=tinymce.DOM;if("tmce"==g){if(k.wpautop[c]){j(c).value=switchEditors.wpautop(j(c).value)}if(h){h.show()}else{tinyMCE.execCommand("mceAddControl",false,c)}f.hide(b);f.addClass(c+"-tmce","active");f.removeClass(c+"-html","active")}else{if("html"==g){if(h){if(k.wpautop[c]===false){switchEditors.old_wp_Nop=switchEditors._wp_Nop;switchEditors._wp_Nop=function(a){return a};h.hide();switchEditors._wp_Nop=switchEditors.old_wp_Nop}else{h.hide()}}f.show(b);f.addClass(c+"-html","active");f.removeClass(c+"-tmce","active")}}},I:function(a){return document.getElementById(a)},ed_init:function(d,b){var a=this,c={};b=b||{};if("undefined"!=typeof(tinymce)&&!tinyMCE.get(d)){a.wpautop[d]=b.wpautop?true:false;tinymce.extend(c,tinyMCEPreInit.mceInit,b);c.mode="exact";c.elements=d;tinyMCE.init(c)}},qt_init:function(d,c){var b,a;d=d||"content";c=c||{};b=c.disabled;a="qt_"+d;if(typeof(QTags)!="undefined"){window[a]=new QTags(a,d,"wp-"+d+"-editor-container",b)}if(typeof(tinymce)!="undefined"){tinymce.DOM.hide("qt_"+d+"_qtags")}}};
<?php
		if ( !empty($first_load) )
			echo 'wpEditor.qt_init("' . $first_load . '", ' . json_encode($this->settings[$id]) . ");\n";

		if ( !empty($short_load) ) {
			foreach ( $short_load as $id ) {
				$jsn = json_encode($this->settings[$id]);
				echo 'wpEditor.ed_init("' . $id . '", ' . $jsn . ");\n" . 'wpEditor.qt_init("' . $id . '", ' . $jsn . ");\n";
			}
		} ?>

</script>
<?php

		if ( !empty($first_load) && $this->media_buttons ) {
			if ( !is_admin() )
				wp_register_script( 'media-upload', admin_url('js/media-upload.js'), array( 'thickbox' ), '20110113' );

			wp_print_scripts('media-upload');
			wp_print_styles('thickbox');
		}
	}
}
global $wp_editor;
$wp_editor = new WP_Pre_33_Editor;
endif; // WP_Editor