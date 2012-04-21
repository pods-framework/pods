<?php
if(!defined('ABSPATH')) { wp_die('No direct access is allowed'); }
/**
 * PodMeta: Ties Pods into various core WordPress functionality
 * @package WordPress,Pods Framework
 * @since 2.1 or {@internal Unknown}}
 *
 * ... More information if needed.
 */
 
if(!function_exists('PodMeta')) {
	
	class PodMeta {
		
		public $fields;
		public $object;
		public $taxonomies;
		
		function __construct() {
			
			$api = pods_api();
			$this->taxonomies = (array) $api->load_pods(array('orderby' => '`weight`, `name`', 'type' => 'taxonomy'));	
			$this->post_types = (array) $api->load_pods(array('orderby' => '`weight`, `name`', 'type' => 'post_type'));
			
			$uri = parse_url($_SERVER['REQUEST_URI']);
			$page = end(explode('/',$uri['path']));
			if('edit-tags.php' == $page) {
				$this->taxonomy_meta_setup();
			} elseif('post.php' == $page OR 'edit-post.php' == $page) {		
				add_action('add_meta_boxes', array($this,'add_meta_boxes'));
	      add_action('save_post',array($this,'save_post'));
	      //user fields
	      add_action('show_user_profile',array($this,'profile_fields'));
	      add_action('edit_user_profile',array($this,'profile_fields'));
	    } elseif('profile.php' == $page OR 'users.php' == $page) { 
	    	$this->user_setup(); 
	    }
		}

		/**
	     * Build fields for user profile
	     * package WordPress, Pods Framework
	     * @params 
	     * @todo 
	     */
		
		function profile_fields() {
			global $wpdb;
			echo 'test';
			$data = array();
			$data['title'] = 'Extra profile information';
			$data['fields'] = $wpdb->get_results($wpdb->prepare("SELECT f.*,p.name as datatype FROM {$wpdb->prefix}pods_fields f LEFT JOIN {$wpdb->prefix}pods p ON p.id = f.pod_id WHERE p.type = 'user' ORDER BY f.weight ASC"));
			//set up field inputs
			for($i = 0; $i < count($data['fields']);$i++) {
				ob_start(); 
				$this->show_input($data['fields'][$i],get_post_meta($_GET['user_id'],$data['fields'][$i]->name, true));
				$data['fields'][$i]['output'] = ob_get_contents();
				ob_end_clean();
			}
			pods_view('admin/user-profile', $data); 
		}
		
		/**
	     * Build meta boxes for custom fields
	     * package WordPress, Pods Framework
	     * @params 
	     * @todo 
	     */
	   	function add_meta_boxes() {
	   		global $wpdb,$post;
	   		$pod_fields = array(); 
	   		wp_enqueue_style('mb-style',PODS_URL.'/ui/css/meta-boxes.css');
	   		//query pod fields for post_type
	   		$select = $wpdb->prepare("SELECT p.name as pod_name, f.* FROM {$wpdb->prefix}pods p INNER JOIN {$wpdb->prefix}pods_fields AS f ON f.pod_id = p.id WHERE p.type = 'post_type' AND p.name = %s ORDER BY f.weight ASC",$post->post_type);
	   		$pts = $wpdb->get_results($select);
	   		if(!empty($pts)) {
		   		foreach($pts as $pt) {
		   			$pod_fields[$pt->pod_name][$pt->weight] = $pt;
		   		}
		   		if(!empty($pod_fields)) {
					$post_type = get_post_type_object($post->post_type);
					add_meta_box($post->post_type.'-pods-fields', __($post_type->labels->singular_name,'pods'), array($this,'mb_callback'), $post->post_type, 'normal', 'high', $pod_fields );  
				}
			}
	   	}
	   	
	   	function taxonomy_meta_setup() {
	   		foreach($this->taxonomies as $taxonomy) {
		   		add_action($taxonomy['name'] .'_edit_form_fields',array($this, 'tax_fields'));
					add_action($taxonomy['name'] .'_add_form_fields',array($this, 'tax_fields'));
					add_action('edited_'.$taxonomy['name'] ,array($this,'tax_save'));
					add_action('created_'.$taxonomy['name'],array($this,'tax_save'));
	   		}	   		
	   	}
	   	
	   	function tax_fields() {
	   		?>
	   		 <tr class="form-field">
				 <th scope="row" valign="top">Custom Field</th>
				 <td>
		   		<input type="text" name="field_name" id="field_id" value="Test value" style="width:97%" />
					<p class="DMB_metabox_description">This is a sample field for proof of concept</p>
				</td>
				</tr>
			<?php
	   	}
	   	
	   	
	   	function tax_save() {
	   		//do something on save
	   	}
	   	
	   	function user_setup() {
				add_action( 'show_user_profile', array($this,'user_fields'));
				add_action( 'edit_user_profile', array($this,'user_fields'));
				add_action( 'personal_options_update', array($this,'user_save'));
				add_action( 'edit_user_profile_update',  array($this,'user_save'));
	   	}
	   	
	   	function user_fields() {
	   		?>
	   		<table class="form-table">
	   			<tbody>
	   				<tr>
	   					<th><label>Custom Field</label></th>
	   					<td><input type="text" name="" value="testing..."</td>
	   					<p class="description">This is a sample field for proof of concept</p>
	   				</tr>
	   			</tbody>
	   		</table>
	   		<?php
	   	}
	   	
	   	function user_save() {
	   		//save the fields here
	   	}
	   	
	   	function mb_callback($post,$args) {
	   		
	   		$fields = $args['args'][$post->post_type];
	   		foreach($fields as $field) {
	   			
	   			$options = (array) json_decode($field->options);
	   			
	   			$input_field = array(
	   				'id' 	=> $field->name,
	   				'type'	=> $field->type,
	   				'desc' 	=> $options['description'],
	   				'std'	=> @$options['default'],
	   			); 
	   			
	   			if($field->type == 'pick') {
	   				$obj = explode('-',$field->pick_object); 
	   				switch($obj[0]) {
	   					case 'taxonomy':
	   						if('single' == $options['pick_type']) {
	   							$input_field['type'] = 'taxonomy-single';
	   							$input_field['taxonomy'] = $obj[1];
	   						}
	   					break;
	   				}
	   			}
	   			
	   			echo '<input type="hidden" name="wp_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';			
	   			echo '<table class="form-table DMB_metabox">';
	   			echo '<tr class="'.$field->type.'">';
	   			echo '<th style="width:18%"><label for="', $field->name.'">'.$field->label.'</label></th>';
	   			echo '<td>';
	   			$this->show_input($input_field,get_post_meta($post->ID,$field->name,true),$post);
	   			echo '</td>';
	   			echo '</tr>';
	   			echo '</table>';
	   		}
	   	}
	   	
	   	function save_post($post_id) {
	   		global $post,$wpdb;
	   		//look for the nonce
			if ( ! isset( $_POST['wp_meta_box_nonce'] ) || !wp_verify_nonce($_POST['wp_meta_box_nonce'], basename(__FILE__))) {
				return $post_id;
			}
			
			// check autosave
			if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
				return $post_id;
			}
	
			// check permissions
			if ( 'page' == $_POST['post_type'] ) {
				if ( !current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
	   		
	   		//get fields
	   		$select = $wpdb->prepare("SELECT p.name as pod_name, f.* FROM {$wpdb->prefix}pods p INNER JOIN {$wpdb->prefix}pods_fields AS f ON f.pod_id = p.id WHERE p.type = 'post_type' AND p.name = %s ORDER BY f.weight ASC",$post->post_type);
	   		$pts = $wpdb->get_results($select);
	   		if(!empty($pts)) {
	   			foreach($pts as $field) {
	   				if($field->type != 'pick') {
	   					update_post_meta($post_id,$field->name, $_POST[$field->name]);
	   				} elseif($field->type == 'pick' AND strstr($field->pick_object,'taxonomy') ) {
	   					$taxonomy = str_replace('taxonomy-','',$field->pick_object);
	   					wp_set_object_terms($post_id,intval($_POST[$field->name]),$taxonomy);
	   				}
	   			}
	   		}
	   		
	   		return $post_id;
	   	}
	   	
	   	function show_input($field,$meta,$post) {
			switch ( $field['type'] ) {
					case 'text':
						echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" style="width:97%" />',
							'<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'text_small':
						echo '<input class="DMB_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'text_medium':
						echo '<input class="DMB_text_medium" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'text_date':
						echo '<input class="DMB_text_small DMB_datepicker" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'text_money':
						echo '$ <input class="DMB_text_money" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'textarea':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
							'<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'textarea_small':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
							'<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'select':
						echo '<select name="', $field['id'], '" id="', $field['id'], '">';
						foreach ($field['options'] as $option) {
							echo '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
						}
						echo '</select>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'radio_inline':
						echo '<div class="DMB_radio_inline">';
						foreach ($field['options'] as $option) {
							echo '<div class="DMB_radio_inline_option"><input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'], '</div>';
						}
						echo '</div>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'radio':
						foreach ($field['options'] as $option) {
							echo '<p><input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'].'</p>';
						}
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'checkbox':
						echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
						echo '<span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'multicheck':
						echo '<ul>';
						foreach ( $field['options'] as $value => $name ) {
							// Append `[]` to the name to get multiple values
							// Use in_array() to check whether the current option should be checked
							echo '<li><input type="checkbox" name="', $field['id'], '[]" id="', $field['id'], '" value="', $value, '"', in_array( $value, $meta ) ? ' checked="checked"' : '', ' /><label>', $name, '</label></li>';
						}
						echo '</ul>';
						echo '<span class="DMB_metabox_description">', $field['desc'], '</span>';					
						break;		
					case 'title':
						echo '<h5 class="DMB_metabox_title">', $field['name'], '</h5>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'paragraph':
						echo '<div id="poststuff" class="meta_mce">';
						echo '<div class="customEditor"><textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="7" style="width:97%">', $meta ? $meta : '', '</textarea></div>';
	                    echo '</div>';
				        echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
					break;
	/*
					case 'wysiwyg':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" class="theEditor" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';	
						break;
	*/
					case 'file_list':
						if($field['mode'] == 'all' || !isset($field['mode'])) {
							echo '<input id="upload_file" type="text" size="36" name="', $field['id'], '" value="" />';
							echo '<input class="upload_button button" type="button" value="Upload File" />';
							echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
								$args = array(
										'post_type' => 'attachment',
										'numberposts' => null,
										'post_status' => null,
										'post_parent' => $post->ID
									);
									$attachments = get_posts($args);
									if ($attachments) {
										echo '<ul class="attach_list">';
										foreach ($attachments as $attachment) {
											echo '<li>'.wp_get_attachment_link($attachment->ID, 'thumbnail', 0, 0, 'Download');
											echo '<span>';
											echo apply_filters('the_title', '&nbsp;'.$attachment->post_title);
											echo '</span>';
											echo ' / <span><a href="" id="remove-attach" rel="'.$attachment->ID.'">Remove</a></li>';
										}
										echo '</ul>';
									}
						} elseif($field['mode'] == 'only') { 
								echo '<input id="upload_file" type="text" size="36" name="', $field['id'], '" value="" />';
								echo '<input class="upload_button button" type="button" value="Upload File" />';
								echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
							$files = get_post_meta($post->ID,$field['id'],false);
							if(!empty($files)) {
										echo '<ul class="attach_list">';
									if(is_array($files)) {
											foreach ($files as $file) {
												echo '<li>'.wp_get_attachment_link($file, 'thumbnail', 0, 0, 'Download');
												echo '<span>';
												echo apply_filters('the_title', '&nbsp;'.get_the_title($file));
												echo '</span></li>';	
											}
									} else {
												echo '<li>'.wp_get_attachment_link($files, 'thumbnail', 0, 0, 'Download');
												echo '<span>';
												echo apply_filters('the_title', '&nbsp;'.get_the_title($file));
												echo '</span></li>';	
									}
								}
							}
							echo '<div id="', $field['id'], '_status" class="DMB_upload_status">';	
							echo '</div>';
							break;
					case 'file':
						echo '<input id="upload_file" type="text" size="45" class="', $field['id'], '" name="', $field['id'], '" value="', $meta, '" />';
						echo '<input class="upload_button button" type="button" value="Upload File" />';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						echo '<div id="', $field['id'], '_status" class="DMB_upload_status">';	
							if ( $meta != '' ) { 
								$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $meta );
								if ( $check_image ) {
									echo '<div class="img_status">';
									echo '<a href="#" class="remove_file_button" rel="', $field['id'], '">Remove Image</a><br>';
									echo '<img src="', $meta, '" alt="" />';
									echo '</div>';
								} else {
									$parts = explode( "/", $meta );
									for( $i = 0; $i < sizeof( $parts ); ++$i ) {
										$title = $parts[$i];
									} 
									echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta, '" target="_blank" rel="external">Download</a> / <a href="# class="remove_file_button" rel="', $field['id'], '">Remove</a>)';
								}	
							}
						echo '</div>'; 
					break;
					case 'pick':
					case 'taxonomy-single':
					$vals = wp_get_object_terms($post->ID,$field['taxonomy'],array('fields'=>'ids'));
						wp_dropdown_categories(array(
						'name' => $field['id'], 
						'id'=> $field['taxonomy'], 
						'hide_empty'=> 0,
						'show_count'=>0,
						'selected' =>($vals)?$vals[0]:'',
						'taxonomy' => $field['taxonomy'])
						); 
					echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
					break;
					
					case 'taxonomy-text':
					$vals = wp_get_object_terms($post->ID,$field['taxonomy'],array('fields'=>'all'));
					echo '<input class="DMB_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="'.@$vals[0]->name.'" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
					break;
					
					} // switch
		}
			
	}		
}
?>