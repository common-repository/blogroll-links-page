<?php
/*
Plugin Name: Blogroll Page
Plugin URI: http://www.websitehostingiq.net/blogroll-page-plugin/
Description: Outputs your Blogroll links to a Page or Post. Add the text <code>&lt;!--blogroll-page--&gt;</code> to a Page or Post and it will output your Blogroll links. Pretty simple stuff really. Don't use this version on WordPress 2.3 or later!
Author: Dominic Foster
Version: 1.0
Author URI: http://www.websitehostingiq.com/
*/


/*
Links Page is a Wordpress Plugin that will create a list of blogroll links to a Post or Page on your Wordpress Blog.
Copyright (C) 2007 Dominic Foster

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//To replace the <!--blogroll-page--> with the blogroll links
function bp_text($text) {
	global $wpdb, $table_prefix;

	//Only perform plugin functionality if post/page text has <!--rs sitemap-->
	if (preg_match("|<!--blogroll-page-->|", $text)) {

	    	if (get_option('blogroll_page_new_window') == 'yes')
    		{
		      $target = 'target="_blank"';
		} else {
		      $target = '';
		}


		// Get parent categories only
		$sql = "SELECT cat_name, cat_id FROM " . $table_prefix ."categories where link_count > 0 AND category_parent = 0 ORDER BY cat_name ASC";
		$categories = $wpdb->get_results($sql);
		foreach ($categories as $cat) {
			$links .= '<H2>' . $cat->cat_name . '</H2><UL>';
			$lsql = "SELECT l.link_url, l.link_name, l.link_description FROM " . $table_prefix . "links as l, " . $table_prefix . "link2cat as lc WHERE lc.link_id = l.link_id and lc.category_id = " . $cat->cat_id . " ORDER BY l.link_name ASC";

			$alllinks = $wpdb->get_results($lsql);

			foreach($alllinks as $link) {
				$url = $link->link_url;
				$name = $link->link_name;
				$description = (strlen($link->link_description) > 0) ? '<br>' . $link->link_description : '';

				$links .= '<li><a href=' . $url . ' ' . $target . '>' . $name . '</a>' . $description;
			} 

		$links .= '</UL>'; 
		} // end category loop

		if (get_option('blogroll_page_link') != 'yes')
		{
			$links .= '<a href="http://www.websitehostingiq.net/" ' . $target . '>Blogroll Links Plugin</a>';
		}

		$text = preg_replace("|<!--blogroll-page-->|", $links, $text);

	}

	return $text;

} //end bp_text()

//admin menu
function blogroll_page_admin() {
	if (function_exists('add_options_page')) {
		add_options_page('blogroll-page', 'Blogroll Page', 1, basename(__FILE__), 'blogroll_page_admin_panel');
  }
}

function blogroll_page_admin_panel() {

	//Add options if first time running
	add_option('blogroll_page_link', 'no', 'Blogroll Page - disable link');
	add_option('blogroll_page_new_window', 'no', 'Blogroll Page - open link in new window');

	if (isset($_POST['info_update'])) {
		//update settings
		if($_POST['disable'] == 'on') { $disable = 'yes'; } else { $disable = 'no'; }
		if($_POST['newwindow'] == 'on') { $new = 'yes'; } else { $new = 'no'; }

		update_option('blogroll_page_link', $disable);
		update_option('blogroll_page_new_window', $new);
	} else {
		//load settings from database
		$disable = get_option('blogroll_page_link');
		$new = get_option('blogroll_page_new_window');
	}

	?>

	<div class=wrap>
		<form method="post">

			<h2>Blogroll Page Plugin Options</h2>

			<fieldset name="set1">
				<h3>Disable Link to Plugin Page:</h3>

				<p>
            It would be nice to get a link to the Blogroll Plugin Download page. But if you don't want to, I'll understand :(<br /><br />
					<label>
            <input type="checkbox" name="disable" <?php checked('yes', $disable); ?> class="tog"/>
						Don't show link to Plugin Download Page.
					</label>
				</p>

				<h3>Open Link in New Window:</h3>

				<p>
					<label>
            <input type="checkbox" name="newwindow" <?php checked('yes', $new); ?> class="tog"/>
						Open link in new window.
					</label>
				</p>

			</fieldset>

			<div class="submit">

				<input type="submit" name="info_update" value="Update Options" />

			</div>

		</form>
	</div><?php
}


//hooks
add_filter('the_content', 'bp_text', 2);
add_action('admin_menu', 'blogroll_page_admin');

?>
