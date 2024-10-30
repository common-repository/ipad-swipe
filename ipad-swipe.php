<?php
/*
Plugin Name: iPad Swipe Plugin
Plugin URI: http://www.sanisoft.com/blog/2011/01/11/wordpress-plugin-ipad-swipe/
Description: iPad Swipe Plugin to swipe posts on iPad.
Version: 1.2
Author: Sumit Meshram <sumit@sanisoft.com>
Author URI: http://www.sanisoft.com/blog/author/sumitmeshram/
*/

/*  Copyright 2009  Sumit Meshram  (email : sumit@sanisoft.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Call to the function to initialize swp settings.
$swp_settings = swp_initialize_and_get_settings();


// This hook will now handle swiping posts in ipones.
add_action('wp_enqueue_scripts', 'swp_enqueue_scripts'); // Plugin hook for adding JS and CSS files required for this plugin
add_action('admin_menu', 'swp_menu');
add_filter('the_content', 'swp_content');


/**
 * Function to initialize default swipe posts settings.
 *
 * @return void
 */
function swp_initialize_and_get_settings() {
	$defaults = array(
		'message' => "Welcome iPad user, you can swipe right and left to go back and forth.",
		'message_count' => 5,
    'apply_to' => 'ipad',  
		);

	add_option('swp_settings', $defaults, 'Options for iPad Swipe.');
	return get_option('swp_settings');	
}//end swp_initialize_and_get_settings()


/**
 * Function to register the management page
 *
 * @return void
 */ 
function swp_menu() {
  add_options_page('iPad Swipe', 'iPad Swipe', 'manage_options', 'swipe-posts-settings', 'swp_options');
}//end swp_menu()


/**
 * Function to manage admin settings.
 * 
 * @return void 
 */
function swp_options() {
    global $swp_settings;
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }
	
	  if (!empty($_POST))
	  {
		  check_admin_referer('swipe-posts-settings');
		  $swp_settings['message'] = stripslashes($_POST['swp_message']);
		  $swp_settings['message_count'] = stripslashes($_POST['swp_message_count']);
  		$swp_settings['apply_to'] = stripslashes($_POST['swp_apply_to']);
		  update_option('swp_settings', $swp_settings);
	  }
    $checked = '';
    if (isset($swp_settings['credit']) && $swp_settings['credit']) {
        $checked = 'CHECKED';
    }
    $ipad = $all = '';
    if ('all' == $swp_settings['apply_to']) {
      $all = 'CHECKED';
    } else {
      $ipad = 'CHECKED';
    }
    
?>
    <div class="wrap">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2>iPad Swipe - By <a href="http://www.sanisoft.com" target="_blank">SANIsoft</a></h2>
        <form method="post" action="">
            <?php wp_nonce_field('swipe-posts-settings') ?>
            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <th scope="row"><label for="swp_message">Message</label></th>
                  <td><input type="text" id="swp_message" name="swp_message" value="<?php print $swp_settings['message']; ?>" size=50></td>
                </tr>
                <tr valign="top">
                  <th scope="row"><label for="swp_message_count">Show message for</label></th>
                  <td><input type="text" id="swp_message_count" name="swp_message_count" value="<?php print $swp_settings['message_count']; ?>" size="3" /> <label>times.</label></td>
                </tr>

                <tr valign="top">
                  <th scope="row"><label for="swp_apply_to">Apply to</label></th>
                  <td>
                    <label><input type="radio" id="swp_apply_to" name="swp_apply_to" value="ipad" <?php print $ipad; ?>/>iPad</label><br />
                    <label><input type="radio" id="swp_apply_to" name="swp_apply_to" value="all" <?php print $all; ?>/>All</label>  
                  </td>
                </tr>
              </tbody>  
            </table>
            <p class="submit">
              <input type="submit" value="Save Changes" class="button-primary" name="Submit">
            </p>
        </form>
    </div>
<?php
}


/**
 * Add our JS and CSS files
 * 
 * @return void
 */
function swp_enqueue_scripts() {
    global $swp_settings;

    // Include js and css files if user agent is iPod, only on home page and post detail page.
    if (_swp_check_user_agent() && (is_single() || is_home())) { 
        wp_enqueue_style( 'ipad_swipe', plugins_url( 'css/ipad-swipe.css', __FILE__ ));
        wp_enqueue_script( 'zepto', plugins_url( 'js/zepto.min.js', __FILE__ ));
        wp_enqueue_script( 'floating', plugins_url( 'js/floating.min.js', __FILE__ ));

        if (is_single()) {
            // Get urls of previous and next posts.
            $prevURL = get_permalink(get_previous_post(false)->ID);
            $nextURL = get_permalink(get_next_post(false)->ID);

            // Check if current url is same as prev or next post urls.
            if ($prevURL == get_permalink()) {
                unset($prevURL);
            }
            if ($nextURL == get_permalink()) {
                unset($nextURL);
            }
        } elseif (is_home()) {
            global $wp_query;
            // Get the total number of pages and page number.
            $max_num_pages = $wp_query->max_num_pages;
            $pageNumber = (get_query_var('paged')) ? get_query_var('paged') : 1;
            
            // Get the previous and next page urls.
            if ($pageNumber > 1) {
                $prevURL = get_previous_posts_page_link();
            }
            if ($pageNumber < $max_num_pages) { 
                $nextURL = get_next_posts_page_link();
            }
        }
        $message = $swp_settings['message'];
        $message_count = $swp_settings['message_count'];

?>

         <script type="text/javascript">
          // <![CDATA[
                <?php if(isset($nextURL) && $nextURL) { ?>
                    var nextURL = "<?php print $nextURL; ?>";
                <?php } ?>  
                <?php if(isset($prevURL) && $prevURL) { ?>
                    var prevURL = "<?php print $prevURL; ?>";
                <?php } ?>  
                var swp_message = "<?php print $message; ?>";
                var swp_msg_count = <?php print $message_count; ?>;
                var swp_loading_img = "<?php print plugins_url( 'images/ajax-loader.gif', __FILE__ ); ?>";
          // ]]>
          </script>

<?php  
    // Include ipad_swipe js file
    wp_enqueue_script( 'ipad_swipe', plugins_url( 'js/ipad_swipe.js', __FILE__ ));
    }
} //End swp_enqueue_scripts


/**
 * Function to check current user agent
 *
 * @return boolean true or false
 */
function _swp_check_user_agent() {
    global $swp_settings;
    $supported_agents = array('iPad', 'iPod', 'iPhone');
    if ('ipad' == $swp_settings['apply_to'] && strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false) {
        return true;
    } else if('all' == $swp_settings['apply_to']) {
        foreach ($supported_agents as $agent) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], $agent) !== false) {
                return true;
            }
        }
    }
    return false;
}//end _check_user_agent()


/**
 * Function to modify the next/previous url for the multi page posts.
 *
 * @return void   
 */
function swp_content($content = '') {
  if (is_single()) { 
    global $multipage, $numpages, $page;

    if ($multipage) {
      if ($page < $numpages) {
        $nextURL = get_permalink(). '&page='. ($page + 1);
      }
      if ($page > 1) {
        $prevURL = get_permalink(). '&page='. ($page - 1);
      }
?>
    <script type="text/javascript">
          // <![CDATA[
              <?php if(isset($nextURL) && $nextURL) { ?>
                  if(typeof nextURL != "undefined") {
                    nextURL = "<?php print $nextURL; ?>";
                  } else {
                    var nextURL = "<?php print $nextURL; ?>";
                  }                
              <?php } ?>  
              <?php if(isset($prevURL) && $prevURL) { ?>
                  if(typeof prevURL != "undefined") {  
                    prevURL = "<?php print $prevURL; ?>";
                  } else {
                    var prevURL = "<?php print $prevURL; ?>";
                  }  
              <?php } ?>  
          // ]]>
          </script>
<?php      
    }
  }
  return $content;
}//end swp_content()
