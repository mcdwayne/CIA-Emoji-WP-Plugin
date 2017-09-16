<?php

/*
 *
 *	Plugin Name: CIA Emoji TIme
 *	Plugin URI: 
 *	Description: A sidebar widget that displays 
 *	Version: 1.0
 *	Author: mcdwayne
 *	Author URI: http://mcdwayne.com/
 *	GitHub Plugin URI: https://github.com/mcdwayne/CIA-Emoji-WP-Plugin
 *
 *
 * This is a fork of https://wordpress.org/plugins/fun-facts/
 * This is the first time I have done this, hacking someone else's code and released it in the wild. 
 * We are all standing on the shoulders of giants and I am very thankful to http://www.joeswebtools.com/wordpress-plugins/fun-facts/ for making this very handy and functional code.  
 * If I am violating any rules, sorry.  Let me know at mcdwayne.com 
 * I am not making any $ on this, this was for fun and learning.ˇ
 *
 *
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 *
 */





/*
 *
 *	cia_emoji_shortcode_handler
 *
 */

function cia_emoji_shortcode_handler($atts, $content = nul) {

	global $wpdb;

	// Create the table name
	$table_name = $wpdb->prefix . 'cia_emoji';

	// Get a CIA Emoji
	$ciaemoji_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
	$ciaemoji_random = rand(0, $ciaemoji_count - 1);
	$ciaemoji_record = $wpdb->get_results("SELECT * FROM $table_name WHERE id={$ciaemoji_random}");
	foreach($ciaemoji_record as $ciaemojis_current) {
		$ciaemoji_text = $ciaemojis_current->cia_emoji;
	}

	// Create the content
	$content = '<table width="250" style="border-width: thin thin thin thin; border-style: solid solid solid solid;">';
	$content .= '<thead><tr><th><center><font face="arial" size="+1"><b>CIA Emoji Time</b></center></font></th></tr></thead>';
	$content .= '<tbody><tr><td>';

	$content .= '<div style="text-align: justify;">' . $ciaemoji_text . '</div>';

	$content .= '</td></tr></tbody>';
	$content .= '</table>';

	return $content;
}

add_shortcode('cia_emoji', 'cia_emoji_shortcode_handler');





/*
 *
 *	WP_Widget_cia_emoji
 *
 */

class WP_Widget_cia_emoji extends WP_Widget {

	function WP_Widget_cia_emoji() {

		parent::WP_Widget(false, $name = 'CIA Emoji');
	}

	function widget($args, $instance) {

		global $wpdb;

		// Create the table name
		$table_name = $wpdb->prefix . 'cia_emoji';

		// Get a fun fact
		$ciaemoji_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
		$ciaemoji_random = rand(0, $ciaemoji_count - 1);
		$ciaemoji_record = $wpdb->get_results("SELECT * FROM $table_name WHERE id={$ciaemoji_random}");
		foreach($ciaemoji_record as $ciaemojis_current) {
			$ciaemoji_text = $ciaemojis_current->cia_emoji;
		}

		extract($args);

	//	$option_title = apply_filters('widget_title', empty($instance['title']) ? 'CIA Emoji' : $instance['title']);

		// Create the widget
		echo $before_widget;
		echo $before_title . $option_title . $after_title;

		echo '<div style="text-align: justify;">' . $ciaemoji_text . '</div>';
		
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {

		return $new_instance;
	}

	function form($instance) {

		$instance = wp_parse_args((array)$instance, array('title' => 'CIA Emoji'));
		$option_title = strip_tags($instance['title']);

		echo '<p>';
		echo 	'<label for="' . $this->get_field_id('title') . '">Title:</label>';
		echo 	'<input class="widefat" type="text" value="' . $option_title . '" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" />';
		echo '</p>';
	}
}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_cia_emoji");'));





/*
 *
 *	cia_emoji_page
 *
 */

function cia_emoji_page() {

	global $wpdb;

	// Create the table name
	$table_name = $wpdb->prefix . 'cia_emoji';

	// Update data
	if(isset($_POST['update'])) {

		// Truncate table
		$results = $wpdb->query("TRUNCATE TABLE $table_name");

		// Update
		if($ciaemojis_list = strip_tags(stripslashes($_POST['ciaemojislist']))) {

			$ciaemojis_array = explode("\n", $ciaemojis_list);
			sort($ciaemojis_array);

			foreach($ciaemojis_array as $ciaemojis_current) {
				$ciaemojis_current = trim($ciaemojis_current);
				if(!empty($ciaemojis_current)) {
					if(NULL == $wpdb->get_var("SELECT cia_emoji FROM $table_name WHERE cia_emoji='" . $wpdb->escape($ciaemojis_current) . "'")) {
						$wpdb->query("INSERT INTO $table_name(cia_emoji) VALUES('" . $wpdb->escape($ciaemojis_current) . "')");
					}
				}
			}
		}
	}

	// Restore defaults
	if(isset($_POST['default'])) {

		// Truncate table
		$results = $wpdb->query("TRUNCATE TABLE $table_name");

		// Get the emoji
		$ciaemojis_array = file(dirname(__FILE__) . '/cia-emoji.dat');
		sort($ciaemojis_array);

		// Import the emoji facts
		foreach($ciaemojis_array as $ciaemojis_current) {
			$ciaemojis_current = trim($ciaemojis_current);
			if(!empty($ciaemojis_current)) {
				if(strncmp($ciaemojis_current, '//', 2)) {
					if(NULL == $wpdb->get_var("SELECT cia_emoji FROM $table_name WHERE cia_emoji='" . $wpdb->escape($ciaemojis_current) . "'")) {
						$wpdb->query("INSERT INTO $table_name(cia_emoji) VALUES('" . $wpdb->escape($ciaemojis_current) . "')");
					}
				}
			}
		}
	}

	// Page wrapper start
	echo '<div class="wrap">';

	// Title
	screen_icon();
	echo '<h2>CIA Emoji</h2>';

	// Options
	echo	'<div id="poststuff" class="ui-sortable">';
	echo		'<div class="postbox opened">';
	echo			'<h3>Options</h3>';
	echo			'<div class="inside">';
	echo				'<form method="post">';
	echo					'<table class="form-table">';
	echo						'<tr>';
	echo							'<th scope="row" valign="top">';
	echo								'<b>CIA Emoji List</b>';
	echo							'</th>';
	echo							'<td>';
	echo								'<textarea name="ciaemojislist" rows="15" cols="80" wrap="off" style="overflow: auto;">';
											$record = $wpdb->get_results("SELECT * FROM $table_name");
											foreach($record as $record) {
												echo $record->cia_emoji . "\r\n";
											}
	echo								'</textarea><br />';
	echo								'Only one emoji per line, not html code.';
	echo							'</td>';
	echo						'</tr>';
	echo						'<tr>';
	echo							'<td colspan="2">';
	echo								'<input type="submit" class="button-primary"  name="update" value="Save Changes" />';
	echo								'&nbsp;&nbsp;&nbsp;';	
	echo								'<input type="submit" class="button-primary"  name="default" value="Restore defaults" />';
	echo							'</td>';
	echo						'</tr>';
	echo					'</table>';
	echo				'</form>';
	echo			'</div>';
	echo		'</div>';
	echo	'</div>';

	echo '</div>';

}





/*
 *
 *	cia_emoji_add_menu
 *
 */

function cia_emoji_add_menu() {

	// Add the menu page
	add_submenu_page('options-general.php', 'CIA Emoji', 'CIA Emoji', 10, __FILE__, 'cia_emoji_page');
}

add_action('admin_menu', 'cia_emoji_add_menu');





/*
 *
 *	cia_emoji_activate
 *
 */

function cia_emoji_activate() {

	global $wpdb;

	// Create the table name
	$table_name = $wpdb->prefix . 'cia_emoji';

	// Create the table if it doesn't already exist
	$results = $wpdb->query("CREATE TABLE IF NOT EXISTS $table_name(id INT(11) NOT NULL AUTO_INCREMENT, cia_emoji VARCHAR(2048) DEFAULT NULL, PRIMARY KEY (id), KEY cia_emoji (cia_emoji));");

	// Get the CIA Emoji
	$ciaemojis_array = file(dirname(__FILE__) . '/cia_emoji.dat');
	sort($ciaemojis_array);

	// Import the CIA Emoji
	foreach($ciaemojis_array as $ciaemojis_current) {
		$ciaemojis_current = trim($ciaemojis_current);
		if(!empty($ciaemojis_current)) {
			if(strncmp($ciaemojis_current, '//', 2)) {
				if(NULL == $wpdb->get_var("SELECT cia_emoji FROM $table_name WHERE cia_emoji='" . $wpdb->escape($ciaemojis_current) . "'")) {
					$wpdb->query("INSERT INTO $table_name(cia_emoji) VALUES('" . $wpdb->escape($ciaemojis_current) . "')");
				}
			}
		}
	}
}

register_activation_hook(__FILE__, 'cia_emoji_activate');
