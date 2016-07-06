<?php


// Language files loading
function theme_init(){
	load_theme_textdomain('default', get_template_directory() . '/languages');
}
add_action ('init', 'theme_init');



if ( function_exists('register_sidebar') ) {
  register_sidebar(array(
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<div class="sb-title widgettitle">',
    'after_title' => '</div>',
    'name' => '1'
  ));
    register_sidebar(array(
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<div class="sb-title widgettitle">',
    'after_title' => '</div>',
    'name' => '2'
  ));
    register_sidebar(array(
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<div class="sb-title widgettitle">',
    'after_title' => '</div>',
    'name' => '3'
  ));
    register_sidebar(array(
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '<div class="sb-title widgettitle">',
    'after_title' => '</div>',
    'name' => '4'
  ));
}



// Generates the menu
function greenpark_globalnav() {
	if ( $menu = str_replace( array( "\r", "\n", "\t" ), '', wp_list_pages('title_li=&echo=0&depth=1') ) )
	echo apply_filters( 'globalnav_menu', $menu );
}



// http://sivel.net/2008/10/wp-27-comment-separation/
function list_pings($comment, $args, $depth) {
  $GLOBALS['comment'] = $comment;
  echo "<li id=\"comment-";
  echo comment_ID();
  echo "\" class=\"pings\">";
  echo comment_author_link();
}


// Note: Custom Admin Panel Functions

add_action('admin_menu', 'greenpark2_options');
add_action('wp_head', 'greenpark2_feed');


function greenpark2_feed() {
	$enable = get_option('greenpark2_feed_enable');
}


function greenpark2() {
	
	if(isset($_POST['submitted']) and $_POST['submitted'] == 'yes') :
		update_option("greenpark2_sidebar_about_title", stripslashes($_POST['sidebar_about_title']));
		update_option("greenpark2_sidebar_about_content", stripslashes($_POST['sidebar_about_content']));
		update_option("greenpark2_feed_uri", stripslashes($_POST['feed_uri']));
		update_option("greenpark2_about_site", stripslashes($_POST['about_site']));
		update_option("google_analytics", stripslashes($_POST['google_analytics']));
		update_option("google_adsense_bottom", stripslashes($_POST['google_adsense_bottom']));
		update_option("google_adsense_sidebar", stripslashes($_POST['google_adsense_sidebar']));
		
		if(isset($_POST['feed_enable']) and $_POST['feed_enable'] == 'yes') :
			update_option("greenpark2_feed_enable", "yes");
		else :
			update_option("greenpark2_feed_enable", "no");
		endif;
		
		if(isset($_POST['sidebar_about_title']) and $_POST['sidebar_about_title'] == '') {
			update_option("greenpark2_sidebar_about_title", "About");
		}
		
		if(isset($_POST['sidebar_about_content']) and $_POST['sidebar_about_content'] == '') {
			update_option("greenpark2_sidebar_about_content", "Change this text in the admin section of WordPress");
		}
		
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>Your settings have been saved.</strong></p></div>";
	endif; 
	
	if(get_option('greenpark2_sidebar_about_title') == '') {
		update_option("greenpark2_sidebar_about_title", "About");
	}
	
	if(get_option('greenpark2_sidebar_about_content') == '') {
		update_option("greenpark2_sidebar_about_content", "Change this text in the admin section of WordPress");
	}
	
	$data = array(
		'feed' => array(
			'uri' => get_option('greenpark2_feed_uri'),
			'enable' => get_option('greenpark2_feed_enable')
		),
		'sidebar' => array(
			'about_title' => get_option('greenpark2_sidebar_about_title'),
			'about_content' => get_option('greenpark2_sidebar_about_content')
		),
		'aside' => get_option('greenpark2_aside_cat'),
		'about' => get_option('greenpark2_about_site')
	);
?>

<!-- Cordobo Green Park 2 settings -->
<div class="wrap">	
	<h2>Cordobo Green Park 2 Settings</h2>

<div class="settings_container" style="width: 100%; margin-right: -200px; float: left;">
	<div style="margin-right: 200px;">
	<form method="post" name="update_form" target="_self">


    <h3 id="greenpark2_sidebar">Sidebar</h3>
		<p>Sidebar box &nbsp; <a href="#greenpark2_sidebar_doc">( ? )</a></p>
		<table class="form-table">
			<tr>
				<th>
					Title:
				</th>
				<td>
					<input type="text" name="sidebar_about_title" value="<?php echo $data['sidebar']['about_title']; ?>" size="35" />
				</td>
			</tr>
			<tr>
				<th>
					Content:
				</th>
				<td>
					<textarea name="sidebar_about_content" rows="10" style="width: 95%;"><?php echo $data['sidebar']['about_content']; ?></textarea>
				</td>
			</tr>
		</table>
		<br />


    <h3 id="greenpark2_feedburner">Feedburner</h3>
		<p>Feedburner information</p>
		<table class="form-table">
			<tr>
				<th>
					Feed URI:
				</th>
				<td>
					http://feeds.feedburner.com/<input type="text" name="feed_uri" value="<?php echo $data['feed']['uri']; ?>" size="30" />
          <br />Check to enable feedburner <input type="checkbox" name="feed_enable" <?php echo ($data['feed']['enable'] == 'yes' ? 'checked="checked"' : ''); ?> value="yes" /> 
				</td>
			</tr>
		</table>	
		<br />
		

    <h3 id="greenpark2_admanager">Ad Manager</h3>
		<p>Code for Google Adsense.</p>
		<table class="form-table">
			<tr>
				<th>
					Google Adsense:
          <br />(Bottom of Post)
				</th>
				<td>
					<textarea name="google_adsense_bottom" style="width: 95%;" rows="10" /><?php echo get_option('google_adsense_bottom'); ?></textarea>
					<br />Paste your Google Adsense Code for the bottom of each post.
					<br /><strong>Size of 468x60 Recommended.</strong>
				</td>
			</tr>
		</table>
		<br />
		

    <h3 id="greenpark2_misc">Misc</h3>
		<p>Google Analytics.</p>
		<table class="form-table">
			<tr>
				<th>
					Google Analytics:
				</th>
				<td>
					<textarea name="google_analytics" style="width: 95%;" rows="10" /><?php echo get_option('google_analytics'); ?></textarea>
					<br />Paste your Google Analytics code here. It will appear at the end of each page.
				</td>
			</tr>
		</table>

    <p class="submit" id="jump_submit">
			<input name="submitted" type="hidden" value="yes" />
			<input type="submit" name="Submit" value="Save Changes" />
		</p>
	</form>
	<br /><br /><br /><br />
	
	<h2>Cordobo Green Park 2 Documentation</h2>
	
	<h3 id="greenpark2_about_doc">About your new theme</h3>
	<p>Thank you for using the Green Park 2 theme, a free premium wordpress theme by German webdesigner <a href="http://cordobo.com/about/">Andreas Jacob</a>.</p>
  <p>Cordobo Green Park 2 is a <strong>simple &amp; elegant light-weight</strong> theme for Wordpress with a <strong>clean typography</strong>, built with <strong>seo and page-rendering optimizations</strong> in mind. Green Park 2 has been rebuild from scratch and supports Wordpress 2.7 and up. The theme is released as &quot;ALPHA&quot;, to let you know I’m still adding features and improvements.</p>
	<p>If you need any support or want some tips, please visit <a href="http://cordobo.com/green-park-2/">Cordobo Green Park 2 project page</a></p>
	

	<h3 id="greenpark2_logo_doc">Logo Setup</h3>
	<p>
  You can easily replace the "text logo" with your image.
  Open the file "styles.css" in the themes folder
  <ul>
  <li>Find the text<br />
    <code>Start EXAMPLE CODE for an image logo</code> (line 224)</li>
  
  <li>Delete <code>/*</code> before<br />
    <code>#logo,</code> (line 225)</li>
  
  <li>Delete <code>*/</code> (line 230) after<br />
    <code>.description</code> (line 229)</li>
  
  <li>Find <code>logo.png</code> (line 228) and replace it with the name of your logo.</li>
  
  <li>Change the height and width to fit your logo (line 226)<br />
    <code>#logo, #logo a { display: block; height: 19px; width: 87px; }</code></li>
  
  <li>Find the text<br />
    <code>Start EXAMPLE CODE for a text logo</code> (line 234)</li>
  
  <li>Add <code>/*</code> before<br />
    <code>#branding</code> (line 235)</li>
  
  <li>Add <code>*/</code> (line 239) after<br />
    <code>#logo, .description { color: #868F98; float: left; margin: 17px 0 0 10px; }</code> (line 238)</li>
  
  <li>Save your changes and upload the file style.css to your themes folder.</li>
  </ul>
	</p>
	

	<h3 id="greenpark2_sidebar_doc">Sidebar</h3>
	<p>
	The &quot;Sidebar Box&quot; can be used for pretty anything. Personally, I use it as an &quot;About section&quot; to tell my readers a little bit about myself, but generally it's completely up to you: put your google adsense code in it, describe your website, add your photo&hellip;
	</p>
	

	<h3 id="greenpark2_tutorials_doc">Tutorials</h3>
	<p>
	List of tutorials based on this theme.
	</p>
	<p>
	<ul>
		<li><a href="http://cordobo.com/1119-provide-visual-feedback-css/">Provide visual feedback using CSS</a> &mdash; an introduction to the themes usage of CSS3</li>
	</ul>
	</p>
	

	<h3 id="greenpark2_licence_doc">Licence</h3>
	<p>
	Released under the <a target="_blank" href="http://www.gnu.org/licenses/gpl.html">GPL License</a> (<a target="_blank" href="http://en.wikipedia.org/wiki/GNU_General_Public_License">What is the GPL</a>?)
  </p>
	<p>
  Free to download, free to use, free to customize. Basically you can do whatever you want as long as you credit me with a link.
	</p>
	
	</div>
	</div>
	
			<div style="position: fixed; right: 20px; width: 170px; background:#F1F1F1; float: right; border: 1px solid #E3E3E3; -moz-border-radius: 6px; padding: 0 10px 10px;">
		<h3 id="bordertitle">Navigation</h3>
		
		<h4>Settings</h4>
		<ul style="list-style-type: none; padding-left: 10px;">
			<li><a href="#greenpark2_sidebar">Sidebar</a></li>
			<li><a href="#greenpark2_feedburner">FeedBurner</a></li>
			<li><a href="#greenpark2_admanager">Ad Manager</a></li>
			<li><a href="#greenpark2_misc">Misc</a></li>
		</ul>
		
		<h4>Documentation</h4>
		<ul style="list-style-type: none; padding-left: 10px;">
			<li><a href="#greenpark2_about_doc">About this Theme</a></li>
			<li><a href="#greenpark2_logo_doc">Logo setup</a></li>
			<li><a href="#greenpark2_sidebar_doc">Sidebar</a></li>
			<li><a href="#greenpark2_tutorials_doc">Tutorials</a></li>
			<li><a href="#greenpark2_license_doc">License</a></li>
		</ul>
		
		<br/>
		<small>&uarr; <a href="#wpwrap">Top</a> | <a href="#jump_submit">Goto &quot;Save&quot;</a></small>
		
	</div>

	<div class="clear"></div>
	
</div>
<?php
}

function greenpark2_options() { // Adds to menu
	add_menu_page('greenpark2 Settings', __('Green Park 2 Settings', 'default'), 'edit_themes', __FILE__, 'greenpark2');
}


/*
   Please leave the credits. Thanks!
 */
function greenpark2_footer() { ?>

<div id="footer" class="clearfix">
<p class="alignright">
  <a href="#home" class="top-link"><?php _e('Back to Top', 'default'); ?></a>
</p>

<p>
	&copy; <?php echo date("Y"); ?> <?php bloginfo('name'); ?>
  &middot; <?php _e('Proudly powered by', 'default'); ?>
  <a href="http://wordpress.org/" title="<?php _e('Blogsoftware by Wordpress', 'default'); ?>">WordPress</a>
	<span class="amp">&amp;</span>
  <a href="http://cordobo.com/green-park-2/" title="Cordobo Green Park 2 Beta 5">Green Park 2</a>
  <?php _e('by', 'default'); ?>
  <a href="http://cordobo.com/" title="Webdesign by Cordobo">Cordobo</a>.
</p>

<p class="signet">
  <?php _e('Valid XHTML 1.0 Transitional | Valid CSS 3', 'default'); ?>
  <br /><br />
	<img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo-cgp2.png" alt="Cordobo Green Park 2 logo" title="Cordobo Green Park 2" width="75" height="12" />
</p>

</div>

<?php
}
  
  add_action('wp_footer', 'greenpark2_footer');

?>
