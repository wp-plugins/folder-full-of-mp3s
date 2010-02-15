<?php
/*
Plugin Name: Folder full of MP3s
Plugin URI: http://gelform.com/blog/simple_mp3_player_
Description: Upload MP3's to a folder on your server. Tell the plugin where the folder is. Add the widget to your sidebar and you have a playable list of MP3's. 
Version: 1.0
Author: Corey Maass, Gelform
Author URI: http://gelform.com

This plug in uses the amazing SoundManager2 Javascript - http://www.schillmania.com/projects/soundmanager2/ - refer to that site for styling).

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

add_action("widgets_init", array('Widget_ffomp3s', 'register'));

register_activation_hook( __FILE__, array('Widget_ffomp3s', 'activate'));

register_deactivation_hook( __FILE__, array('Widget_ffomp3s', 'deactivate'));

class Widget_ffomp3s 
{
	function activate(){
		$data = array( 'title' => 'MP3s - click to listen' ,'folder' => '');
		if ( ! get_option('widget_ffomp3s')){
		add_option('widget_ffomp3s' , $data);
		} else {
		update_option('widget_ffomp3s' , $data);
		}
	}

	function deactivate(){
		delete_option('widget_ffomp3s');
	}
	
	function control(){
		$data = get_option('widget_ffomp3s');
		?>
		
		<dl>
		<dt><label for="widget_ffomp3s_title">Title:</label></dt>
		<dd><input name="widget_ffomp3s_title" id="widget_ffomp3s_title"
		type="text" value="<?php echo $data['title']; ?>" />
		</dd>
		<dt><label for="widget_ffomp3s_folder">Folder:</label></dt>
		<dd>/<input name="widget_ffomp3s_folder"
		type="text" value="<?php echo $data['folder']; ?>" />/	
		</dd>
		<dd>
		<label>
		<?php 
			if ( !isset($data['head']) )$data['head'] = 1; 
			if ( empty($data['head']) )$data['head'] = 0; 
		?>
		<input name="widget_ffomp3s_wp_head"
		type="checkbox" value="1" <?php if ( $data['head'] == 1 ) echo ' checked="checked"' ?>" />
		Does your theme call &quot;wp_head&quot;?
		</label>
		<br /><small>(Look in header.php in your theme folder)</small>
		</dd>
		<?php
		if (isset($_POST['widget_ffomp3s_title']))
		{
			$data['title'] = attribute_escape($_POST['widget_ffomp3s_title']);
			
			$folder = $_POST['widget_ffomp3s_folder'];
			// remove slashes
			$folder = (substr( $folder, -1) == '/') ? substr($folder, 0, -1) : $folder;
			$folder = (substr( $folder, 0, 1) == '/') ? substr($folder, 1) : $folder;
			$data['folder'] = attribute_escape($folder);
			
			if ( empty($_POST['widget_ffomp3s_wp_head']) ) 
			{
				$_POST['widget_ffomp3s_wp_head'] = 0;
			}
			$data['head'] = $_POST['widget_ffomp3s_wp_head'];

			update_option('widget_ffomp3s', $data);
		}
	}
	
	function widget($args)
	{
		$data = get_option('widget_ffomp3s');

		echo $args['before_widget'];
		echo $args['before_title'] . ' ' . $data['title'] . ' ' . $args['after_title'];
		
		if ($handle = opendir(ABSPATH . $data['folder'])) 
		{
			// if not, write javascripts inline
			if ( !$data['head'] )
			{
				echo '<script type="text/javascript" src="' . plugins_url( 'soundmanager2.js', __FILE__ ) . '"></script>';
				echo '<script type="text/javascript" src="' . plugins_url( 'inlineplayer.js', __FILE__ ) . '"></script>';
			}

			wp_enqueue_script('soundmanager2');
			wp_enqueue_script('inlineplayer');

			echo '<ul id="mp3s_list" class="sidebar_list">' . "\n";
			while (false !== ($file = readdir($handle))) 
			{
				if ($file == "." || $file == "..") continue;
				if ( strtolower(substr(strrchr($file,"."),1)) != 'mp3' ) continue;
				
				$name = substr(str_replace('_', ' ', $file), 0 , -4);
	
				echo '<li><a href="' . $data['folder'] . '/' . $file . '">' . $name . '</a></li>' . "\n";
			}
			closedir($handle);
			echo '</ul>' . "\n";
		}
		
		echo $args['after_widget'];
	}
	
	function register()
	{
		register_sidebar_widget('Folder full of MP3s', array('Widget_ffomp3s', 'widget'));
		register_widget_control('Folder full of MP3s', array('Widget_ffomp3s', 'control'));
		wp_enqueue_script ('soundmanager2', plugins_url( 'soundmanager2.js', __FILE__ ));
		wp_enqueue_script ('inlineplayer', plugins_url( 'inlineplayer.js', __FILE__ ));

	}
}

?>
