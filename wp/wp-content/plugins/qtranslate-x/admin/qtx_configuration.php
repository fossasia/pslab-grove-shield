<?php
if ( !defined( 'ABSPATH' ) ) exit;

require_once(QTRANSLATE_DIR.'/admin/qtx_admin_options_update.php');
require_once(QTRANSLATE_DIR.'/admin/qtx_import_export.php');

function qtranxf_language_form() {
	global $q_config;

	$language_code = isset($q_config['posted']['language_code']) ? $q_config['posted']['language_code'] : '';
	$original_lang = isset($q_config['posted']['original_lang']) ? $q_config['posted']['original_lang'] : '';

	$lang_props = isset($q_config['posted']['lang_props']) ? $q_config['posted']['lang_props'] : array();

	$language_name = isset($lang_props['language_name']) ? $lang_props['language_name'] : '';
	$language_locale = isset($lang_props['locale']) ? $lang_props['locale'] : '';
	$language_locale_html = isset($lang_props['locale_html']) ? $lang_props['locale_html'] : '';
	$language_date_format = isset($lang_props['date_format']) ? $lang_props['date_format'] : '';
	$language_time_format = isset($lang_props['time_format']) ? $lang_props['time_format'] : '';
	$language_flag = isset($lang_props['flag']) ? $lang_props['flag'] : '';
	$language_na_message = isset($lang_props['not_available']) ? $lang_props['not_available'] : '';
?>
<input type="hidden" name="original_lang" value="<?php echo $original_lang; ?>" />
<div class="form-field">
	<label for="language_code"><?php _e('Language Code', 'qtranslate') ?><br/></label>
	<input name="language_code" id="language_ code" type="text" value="<?php echo $language_code; ?>" size="2" maxlength="2"/>
	<p class="qtranxs_notes"><?php echo __('2-Letter <a href="http://www.w3.org/WAI/ER/IG/ert/iso639.htm#2letter">ISO Language Code</a> for the Language you want to insert. (Example: en)', 'qtranslate').'<br/>'.__('The language code is used in language tags and in URLs. It is case sensitive. Use of lower case for the language code is preferable, but not required. The code may be arbitrary chosen by site owner, although it is preferable to use already commonly accepted code if available. Once a language code is created and entries for this language are made, it is difficult to change it, please make a careful decision.', 'qtranslate') ?></p>
</div>
<div class="form-field">
	<label for="language_flag"><?php _e('Flag', 'qtranslate') ?></label>
	<?php
	$files = array();
	$flag_dir = trailingslashit(WP_CONTENT_DIR).$q_config['flag_location'];
	if($dir_handle = @opendir($flag_dir)) {
		while (false !== ($file = readdir($dir_handle))) {
			if(preg_match("/\.(jpeg|jpg|gif|png|svg)$/i",$file)) {
				$files[] = $file;
			}
		}
		sort($files);
	}
	if(sizeof($files)>0){
	?>
	<select name="language_flag" id="language_flag" onchange="switch_flag(this.value);"  onclick="switch_flag(this.value);" onkeypress="switch_flag(this.value);">
	<?php
		foreach ($files as $file) {
	?>
		<option value="<?php echo $file; ?>" <?php echo ($language_flag==$file)?'selected="selected"':''?>><?php echo $file; ?></option>
	<?php
		}
	?>
	</select>
	<img src="" alt="<?php _e('Flag', 'qtranslate') ?>" id="preview_flag" style="vertical-align:middle; display:none"/>
	<?php
	} else {
		_e('Incorrect Flag Image Path! Please correct it!', 'qtranslate');
	}
	?>
	<p class="qtranxs_notes"><?php _e('Choose the corresponding country flag for language. (Example: gb.png)', 'qtranslate') ?></p>
</div>
<script type="text/javascript">
//<![CDATA[
	function switch_flag(url) {
		document.getElementById('preview_flag').style.display = "inline";
		document.getElementById('preview_flag').src = "<?php echo qtranxf_flag_location() ?>" + url;
	}
	switch_flag(document.getElementById('language_flag').value);
//]]>
</script>
<div class="form-field">
	<label for="language_name"><?php _e('Name', 'qtranslate'); echo ' '; _e('(in native alphabet)', 'qtranslate') ?><br/></label>
	<input name="language_name" id="language_name" type="text" value="<?php echo $language_name; ?>"/>
	<p class="qtranxs_notes"><?php _e('The Name of the language, which will be displayed on the site. (Example: English)', 'qtranslate') ?></p>
</div>
<div class="form-field">
	<label for="language_locale"><?php _e('Locale', 'qtranslate') ?><br/></label>
	<input name="language_locale" id="language_locale" type="text" value="<?php echo $language_locale; ?>" />
	<p class="qtranxs_notes">
	<?php _e('PHP and Wordpress Locale for the language. (Example: en_US)', 'qtranslate') ?><br/>
	<?php _e('You will need to install the .mo file for this language.', 'qtranslate') ?>
	</p>
</div>
<div class="form-field">
	<label for="language_locale_html"><?php _e('Locale at front-end', 'qtranslate') ?><br/></label>
	<input name="language_locale_html" id="language_locale_html" type="text" value="<?php echo $language_locale_html; ?>" />
	<p class="qtranxs_notes">
	<?php printf(__('Locale to be used in browser at front-end to set %s HTML attributes to specify alternative languages on a page. If left empty, then "%s" is used by default.', 'qtranslate'), '"hreflang"', __('Language Code', 'qtranslate')) ?><br/>
	</p>
</div>
<div class="form-field">
	<label for="language_date_format"><?php _e('Date Format', 'qtranslate') ?><br/></label>
	<input name="language_date_format" id="language_date_format" type="text" value="<?php echo $language_date_format; ?>"/>
	<p class="qtranxs_notes"><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> (use %q for day suffix (st,nd,rd,th)) or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %A %B %e%q, %Y)', 'qtranslate') ?></p>
</div>
<div class="form-field">
	<label for="language_time_format"><?php _e('Time Format', 'qtranslate') ?><br/></label>
	<input name="language_time_format" id="language_time_format" type="text" value="<?php echo $language_time_format; ?>"/>
	<p class="qtranxs_notes"><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %I:%M %p)', 'qtranslate') ?></p>
</div>
<div class="form-field">
	<label for="language_na_message"><?php _e('Not Available Message', 'qtranslate') ?><br/></label>
	<input name="language_na_message" id="language_na_message" type="text" value="<?php echo esc_html($language_na_message); ?>"/>
	<p class="qtranxs_notes">
	<?php _e('Message to display if post is not available in the requested language. (Example: Sorry, this entry is only available in %LANG:, : and %.)', 'qtranslate') ?><br/>
	<?php _e('%LANG:&lt;normal_separator&gt;:&lt;last_separator&gt;% generates a list of languages separated by &lt;normal_separator&gt; except for the last one, where &lt;last_separator&gt; will be used instead.', 'qtranslate');
		echo ' ';
		printf(__('The language names substituted into the list of available languages are shown translated in the active language. The nominative form of language names is used as it is fetched from %s may not fit the grammar rules of your language. It is then advisable to include quotes in this message like this "%s". Alternatively you may modify "%s" files in folder "%s" with names that fit your grammar rules. Please, %scontact the development team%s, if you decide to modify "%s" files.', 'qtranslate'), '<a href="http://unicode.org/Public/cldr/latest" title="Unicode Common Locale Data Repository" target="_blank" tabindex="-1">CLDR</a>', 'Sorry, this entry is only available in "%LANG:", ":" and "%".', '.po', '<a href="https://github.com/qTranslate-Team/qtranslate-x/tree/master/lang/language-names" target="_blank" tabindex="-1">/lang/language-names/</a>', '<a href="https://qtranslatexteam.wordpress.com/contact-us/" target="_blank" tabindex="-1">', '</a>', '.po');
	?>
	</p>
</div>
<?php
}

function qtranxf_admin_section_start($nm) {
	echo '<div id="tab-'.$nm.'" class="hidden">'.PHP_EOL;
}

function qtranxf_admin_section_end($nm, $button_name=null, $button_class='button-primary') {
	if(!$button_name) $button_name = __('Save Changes', 'qtranslate');
	echo '<p class="submit"><input type="submit" name="submit"';
	if($button_class) echo ' class="'.$button_class.'"';
	echo ' value="'.$button_name.'" /></p>';
	echo '</div>'.PHP_EOL; //'<!-- id="tab-'.$nm.'" -->';
}

/**
 * Set Admin Sections Names
 */
function qtranxf_get_admin_sections() {
	//$q_config['admin_sections'] = array();
	//$admin_sections = &$q_config['admin_sections'];
	$admin_sections = array();
	$admin_sections['general'] = __('General', 'qtranslate');//General Settings
	$admin_sections['advanced'] = __('Advanced', 'qtranslate');//Advanced Settings

	$custom_sections = apply_filters('qtranslate_admin_sections', array());
	foreach($custom_sections as $k => $v) $admin_sections[$k] = $v;

	$admin_sections['integration'] = __('Integration', 'qtranslate');//Custom Integration
	$admin_sections['import'] = __('Import', 'qtranslate').'/'.__('Export', 'qtranslate');
	$admin_sections['languages'] = __('Languages', 'qtranslate');//always last section
	return $admin_sections;
}

function qtranxf_conf() {
	global $q_config, $qtranslate_options, $wpdb;
	//qtranxf_dbg_log('qtranxf_conf: REQUEST_TIME_FLOAT: ', $_SERVER['REQUEST_TIME_FLOAT']);
	//qtranxf_dbg_log('qtranxf_conf: POST: ',$_POST);
	//qtranxf_dbg_log('qtranxf_conf: GET: ',$_GET);

	// do redirection for dashboard
	if(isset($_GET['godashboard'])) {
		echo '<h2>'.__('Switching Language', 'qtranslate').'</h2>'.sprintf(__('Switching language to %1$s... If the Dashboard isn\'t loading, use this <a href="%2$s" title="Dashboard">link</a>.','qtranslate'),$q_config['language_name'][qtranxf_getLanguage()],admin_url()).'<script type="text/javascript">document.location="'.admin_url().'";</script>';
		exit();
	}

	// don't accidentally delete/enable/disable twice
	//$clean_uri = preg_replace("/&(delete|enable|disable|convert|markdefault|moveup|movedown)=[^&#]*/i","",$_SERVER['REQUEST_URI']);
	$clean_uri = $q_config['url_info']['qtranslate-settings-url'];
	$clean_uri = apply_filters('qtranslate_clean_uri', $clean_uri);

	$pluginurl = plugin_dir_url( QTRANSLATE_FILE );
	$nonce_action = 'qtranslate-x_configuration_form';
	if ( ! qtranxf_verify_nonce( $nonce_action ) ) return;

	// Allow to prepare loading additional features
	do_action('qtranslate_configuration_pre', $clean_uri);

	// Generate XHTML
?>
<div class="wrap">
<?php if(isset($_GET['edit'])) { ?>
<h2><?php _e('Edit Language', 'qtranslate') ?></h2>
<form action="" method="post" id="qtranxs-edit-language">
<?php
	wp_nonce_field($nonce_action);
	qtranxf_language_form()
?>
<p class="submit"><input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes &raquo;', 'qtranslate') ?>" /></p>
</form>
<p class="qtranxs_notes"><a href="<?php echo admin_url('options-general.php?page=qtranslate-x#languages') ?>"><?php _e('back to configuration page', 'qtranslate') ?></a></p>
<?php
	} else {
?>
<h2><?php _e('Language Management (qTranslate Configuration)', 'qtranslate') ?></h2>
<p class="qtranxs_heading" style="font-size: small"><?php printf(__('For help on how to configure qTranslate correctly, take a look at the <a href="%1$s">qTranslate FAQ</a> and the <a href="%2$s">Support Forum</a>.', 'qtranslate')
	, 'https://qtranslatexteam.wordpress.com/faq/'
//, 'https://wordpress.org/plugins/qtranslate-x/faq/'
	, 'http://qtranslate-x.com/support/'
//, 'https://wordpress.org/support/plugin/qtranslate-x'
	);
	echo '<br><small><em>';
	printf(__('Plugin development is supported through your %sdonations to the development team%s.','qtranslate'),'<a href="https://qtranslatexteam.wordpress.com/donations/" target="_blank">', '</a>') ?></em></small></p>
<?php if(isset($_GET['config_inspector'])) {

	$admin_config = $q_config['admin_config'];
	$admin_config = apply_filters('qtranslate_load_admin_page_config', $admin_config);
	$admin_config = apply_filters('i18n_admin_config', $admin_config);

	$front_config = $q_config['front_config'];
	$front_config = apply_filters('i18n_front_config', $front_config);

	$configs = array();
	$configs['vendor'] = 'combined effective configuration';
	$configs['admin-config'] = $admin_config;
	$configs['front-config'] = $front_config;

	$configs = qtranxf_standardize_i18n_config($configs);
?>
<p class="qtranxs_notes"><a href="<?php echo admin_url('options-general.php?page=qtranslate-x#integration') ?>"><?php _e('back to configuration page', 'qtranslate') ?></a></p>
<h3 class="heading"><?php _e('Configuration Inspector', 'qtranslate') ?></h3>
<p class="qtranxs_explanation">
<?php printf(__('Review a combined JSON-encoded configuration as loaded from options %s and %s, as well as from the theme and other plugins via filters %s and %s.', 'qtranslate'), '"'.__('Configuration Files', 'qtranslate').'"', '"'.__('Custom Configuration', 'qtranslate').'"', '"i18n_admin_config"', '"i18n_front_config"');
echo ' '; printf(__('Please, read %sIntegration Guide%s for more information.', 'qtranslate'), '<a href="https://qtranslatexteam.wordpress.com/integration/" target="_blank">', '</a>'); ?></p>
<p class="qtranxs_explanation"><textarea class="widefat" rows="30"><?php echo qtranxf_json_encode($configs); ?></textarea></p>
<p class="qtranxs_notes"><?php printf(__('Note to developers: ensure that front-end filter %s is also active on admin side, otherwise the changes it makes will not show up here. Having this filter active on admin side does not affect admin pages functionality, except this field.', 'qtranslate'), '"i18n_front_config"') ?></p>
<p class="qtranxs_notes"><a href="<?php echo admin_url('options-general.php?page=qtranslate-x#integration') ?>"><?php _e('back to configuration page', 'qtranslate') ?></a></p>
<?php }else{
	// Set Navigation Tabs
	$admin_sections = qtranxf_get_admin_sections();
	echo '<h2 class="nav-tab-wrapper">'.PHP_EOL;
	foreach( $admin_sections as $slug => $name ){
		echo '<a class="nav-tab" href="#'.$slug.'" title="'.sprintf(__('Click to switch to %s', 'qtranslate'), $name).'">'.$name.'</a>'.PHP_EOL;
	}
	echo '</h2>'.PHP_EOL;
?>
	<form id="qtranxs-configuration-form" action="<?php echo $clean_uri;?>" method="post">
	<?php wp_nonce_field($nonce_action); // Prevent CSRF ?>
	<div class="tabs-content"><?php //<!-- tabs-container --> ?>
	<?php qtranxf_admin_section_start('general');
		$permalink_is_query = qtranxf_is_permalink_structure_query();
		//qtranxf_dbg_echo('$permalink_is_query: ',$permalink_is_query);
		$url_mode = $q_config['url_mode'];
	?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Default Language / Order', 'qtranslate') ?></th>
				<td><p class="qtranxs_explanation"><?php echo __('Every multilingual field is expected to have a meaningful content in the "Default Language". Usually, it is the language of your site before it became multilingual.', 'qtranslate');
				echo ' '; echo __('Order of languages defines in which order they are listed, when languages need to be listed, otherwise it is not important.', 'qtranslate');
				?></p>
					<fieldset id="qtranxs-languages-menu">
					<legend class="hidden"><?php _e('Default Language', 'qtranslate') ?></legend>
					<table id="qtranxs-enabled-languages">
				<?php
					$flag_location = qtranxf_flag_location();
					foreach ( qtranxf_getSortedLanguages() as $key => $language ) {
						echo '<tr>';
						echo '<td><label title="' . $q_config['language_name'][$language] . '"><input type="radio" name="default_language" value="' . $language . '"';
						checked($language,$q_config['default_language']);
						echo ' />';
						echo ' <a href="'.add_query_arg('moveup', $language, $clean_uri).'"><img src="'.$pluginurl.'img/arrowup.png" alt="up" /></a>';
						echo ' <a href="'.add_query_arg('movedown', $language, $clean_uri).'"><img src="'.$pluginurl.'img/arrowdown.png" alt="down" /></a>';
						echo ' <img src="' . $flag_location.$q_config['flag'][$language] . '" alt="' . $q_config['language_name'][$language] . '" /> ';
						echo ' '.$q_config['language_name'][$language];
						echo '</label></td>';
						echo '<td>[:'.$language.']</td><td><a href="'. $clean_uri . '&edit='.$language.'">' . __('Edit', 'qtranslate') . '</a></td><td><a href="'. $clean_uri . '&disable='.$language.'">' . __('Disable', 'qtranslate') . '</a></td>';
						echo '</tr>'.PHP_EOL;
					}
				?>
					</table>
					<p class="qtranxs_notes"><?php
						$url = get_bloginfo('url');
						$url = qtranxf_convertURL($url, $q_config['default_language'], true);
						printf(__('Choose the default language of your blog. This is the language which will be shown on %s. You can also change the order the languages by clicking on the arrows above.', 'qtranslate'), $url) ?></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('URL Modification Mode', 'qtranslate') ?></th>
				<td>
					<fieldset><legend class="hidden"><?php _e('URL Modification Mode', 'qtranslate') ?></legend>
						<label title="Query Mode"><input type="radio" name="url_mode" value="<?php echo QTX_URL_QUERY; ?>" <?php checked($url_mode,QTX_URL_QUERY) ?> /> <?php echo __('Use Query Mode (?lang=en)', 'qtranslate').'. '.__('Most SEO unfriendly, not recommended.', 'qtranslate') ?></label><br/>
					<?php /*
							if($permalink_is_query) {
								echo '<br/>'.PHP_EOL;
								printf(__('No other URL Modification Modes are available if permalink structure is set to "Default" on configuration page %sPermalink Setting%s. It is SEO advantageous to use some other permalink mode, which will enable more URL Modification Modes here as well.', 'qtranslate'),'<a href="'.admin_url('options-permalink.php').'">', '</a>');
								echo PHP_EOL.'<br/><br/>'.PHP_EOL;
							}else{ */ ?>
						<label title="Pre-Path Mode"><input type="radio" name="url_mode" value="<?php echo QTX_URL_PATH; ?>" <?php checked($url_mode,QTX_URL_PATH); disabled($permalink_is_query) ?> /> <?php echo __('Use Pre-Path Mode (Default, puts /en/ in front of URL)', 'qtranslate').'. '.__('SEO friendly.', 'qtranslate') ?></label><br/>
						<label title="Pre-Domain Mode"><input type="radio" name="url_mode" value="<?php echo QTX_URL_DOMAIN; ?>" <?php checked($url_mode,QTX_URL_DOMAIN) ?> /> <?php echo __('Use Pre-Domain Mode (uses http://en.yoursite.com)', 'qtranslate').'. '.__('You will need to configure DNS sub-domains on your site.', 'qtranslate') ?></label><br/>
					<?php /*
						<p class="qtranxs_notes"><?php _e('Pre-Path and Pre-Domain mode will only work with mod_rewrite/pretty permalinks. Additional Configuration is needed for Pre-Domain mode or Per-Domain mode.', 'qtranslate') ?></p><br/><br/>
							} */
					?>
						<label for="hide_default_language"><input type="checkbox" name="hide_default_language" id="hide_default_language" value="1"<?php checked($q_config['hide_default_language']) ?>/> <?php _e('Hide URL language information for default language.', 'qtranslate') ?></label>
						<p class="qtranxs_notes"><?php _e('This is only applicable to Pre-Path and Pre-Domain mode.', 'qtranslate') ?></p>
					<?php
						//if(!$permalink_is_query) {
							do_action('qtranslate_url_mode_choices',$permalink_is_query);
					?>
						<label title="Per-Domain Mode"><input type="radio" name="url_mode" value="<?php echo QTX_URL_DOMAINS; ?>" <?php checked($url_mode,QTX_URL_DOMAINS) ?> /> <?php echo __('Use Per-Domain mode: specify separate user-defined domain for each language.', 'qtranslate') ?></label>
					<?php //} ?>
					</fieldset>
				</td>
			</tr>
	<?php
		if($url_mode==QTX_URL_DOMAINS){
			$homeinfo = qtranxf_get_home_info();
			$home_host = $homeinfo['host']; //parse_url(get_option('home'),PHP_URL_HOST);
			foreach($q_config['enabled_languages'] as $lang){
				$id='language_domain_'.$lang;
				$domain = isset($q_config['domains'][$lang]) ? $q_config['domains'][$lang] : $lang.'.'.$home_host;
				echo '<tr><td style="text-align: right">'.__('Domain for', 'qtranslate').' <a href="'.$clean_uri.'&edit='.$lang.'">'.$q_config['language_name'][$lang].'</a>&nbsp;('.$lang.'):</td><td><input type="text" name="'.$id.'" id="'.$id.'" value="'.$domain.'" style="width:100%"/></td></tr>'.PHP_EOL;
			}
		}
	?>
			<tr valign="top">
				<th scope="row"><?php _e('Untranslated Content', 'qtranslate') ?></th>
				<td>
					<p class="qtranxs_explanation"><?php printf(__('The choices below define how to handle untranslated content at front-end of the site. A content of a page or a post is considered untranslated if the main text (%s) is empty for a given language, regardless of other fields like title, excerpt, etc. All three options are independent of each other.', 'qtranslate'), 'post_content') ?></p>
					<br/>
					<label for="hide_untranslated"><input type="checkbox" name="hide_untranslated" id="hide_untranslated" value="1"<?php checked($q_config['hide_untranslated']) ?>/> <?php _e('Hide Content which is not available for the selected language.', 'qtranslate') ?></label>
					<br/>
					<p class="qtranxs_notes"><?php _e('When checked, posts will be hidden if the content is not available for the selected language. If unchecked, a message will appear showing all the languages the content is available in.', 'qtranslate') ?>
					<?php _e('The message about available languages for the content of a post or a page may also appear if a single post display with an untranslated content if viewed directly.', 'qtranslate') ?>
					<?php printf(__('This function will not work correctly if you installed %s on a blog with existing entries. In this case you will need to take a look at option "%s" under "%s" section.', 'qtranslate'), 'qTranslate', __('Convert Database','qtranslate'), __('Import', 'qtranslate').'/'.__('Export', 'qtranslate')) ?></p>
					<br/>
					<label for="show_displayed_language_prefix"><input type="checkbox" name="show_displayed_language_prefix" id="show_displayed_language_prefix" value="1"<?php checked($q_config['show_displayed_language_prefix']) ?>/> <?php _e('Show displayed language prefix when content is not available for the selected language.', 'qtranslate') ?></label>
					<br/>
					<p class="qtranxs_notes"><?php _e('This is relevant to all fields other than the main content of posts and pages. Such untranslated fields are always shown in an alternative available language, and will be prefixed with the language name in parentheses, if this option is on.', 'qtranslate') ?></p>
					<br/>
					<label for="show_alternative_content"><input type="checkbox" name="show_alternative_content" id="show_alternative_content" value="1"<?php checked($q_config['show_alternative_content']) ?>/> <?php _e('Show content in an alternative language when translation is not available for the selected language.', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php printf(__('When a page or a post with an untranslated content is viewed, a message with a list of other available languages is displayed, in which languages are ordered as defined by option "%s". If this option is on, then the content of the first available language will also be shown, instead of the expected language, for the sake of user convenience.', 'qtranslate'), __('Default Language / Order', 'qtranslate')) ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Language Names', 'qtranslate') ?></th>
				<td>
					<label for="camel_case"><input type="checkbox" name="camel_case" id="camel_case" value="1"<?php checked(!isset($q_config['language_name_case']) || !$q_config['language_name_case']) ?>/> <?php _e('Show language names in "Camel Case".', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php printf(__('Define how to display translated language names, whenever languages need to be listed, for example, in "%s" statement.', 'qtranslate'), __('Not Available Message', 'qtranslate')) ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Detect Browser Language', 'qtranslate') ?></th>
				<td>
					<label for="detect_browser_language"><input type="checkbox" name="detect_browser_language" id="detect_browser_language" value="1"<?php checked($q_config['detect_browser_language']) ?>/> <?php _e('Detect the language of the browser and redirect accordingly.', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php _e('When the frontpage is visited via bookmark/external link/type-in, the visitor will be forwarded to the correct URL for the language specified by his browser.', 'qtranslate') ?></p>
				</td>
			</tr>
		</table>
	<?php qtranxf_admin_section_end('general') ?>
	<?php qtranxf_admin_section_start('advanced') ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Post Types', 'qtranslate') ?></th>
				<td>
					<label for="post_types"><?php _e('Post types enabled for translation:', 'qtranslate') ?></label><p>
					<?php
						$post_types = get_post_types();
						foreach ( $post_types as $post_type ) {
							if(!qtranxf_post_type_optional($post_type)) continue;
							$post_type_off = isset($q_config['post_type_excluded']) && in_array($post_type,$q_config['post_type_excluded']);
					?>
					<span style="margin-right: 12pt"><input type="hidden" name="post_types_all[<?php echo $post_type ?>]" value="<?php echo $post_type_off ? '0':'1' ?>"><input type="checkbox" name="post_types[<?php echo $post_type ?>]" id="post_type_<?php echo $post_type ?>" value="1"<?php checked(!$post_type_off) ?> />&nbsp;<?php echo $post_type ?></span>
					<?php
						}
					?>
					</p><p class="qtranxs_notes"><?php _e('If a post type unchecked, no fields in a post of that type are treated as translatable on editing pages. However, the manual raw multilingual entries with language tags may still get translated in a usual way at front-end.', 'qtranslate') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Flag Image Path', 'qtranslate') ?></th>
				<td>
					<?php echo trailingslashit(content_url()) ?><input type="text" name="flag_location" id="flag_location" value="<?php echo $q_config['flag_location']; ?>" style="width:100%"/>
					<p class="qtranxs_notes"><?php printf(__('Path to the flag images under wp-content, with trailing slash. (Default: %s, clear the value above to reset it to the default)', 'qtranslate'), qtranxf_flag_location_default()) ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Ignore Links', 'qtranslate') ?></th>
				<td>
					<input type="text" name="ignore_file_types" id="ignore_file_types" value="<?php echo implode(',',array_diff($q_config['ignore_file_types'],explode(',',QTX_IGNORE_FILE_TYPES))) ?>" style="width:100%"/>
					<p class="qtranxs_notes"><?php printf(__('Don\'t convert links to files of the given file types. (Always included: %s)', 'qtranslate'),implode(', ',explode(',',QTX_IGNORE_FILE_TYPES))) ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Head inline CSS', 'qtranslate') ?></th>
				<td>
					<label for="header_css_on"><input type="checkbox" name="header_css_on" id="header_css_on" value="1"<?php checked($q_config['header_css_on']) ?> />&nbsp;<?php _e('CSS code added by plugin in the head of front-end pages:', 'qtranslate') ?></label>
					<br />
					<textarea id="header_css" name="header_css" style="width:100%"><?php echo esc_textarea($q_config['header_css']) ?></textarea>
					<p class="qtranxs_notes"><?php echo __('To reset to default, clear the text.', 'qtranslate').' '.__('To disable this inline CSS, clear the check box.', 'qtranslate') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Cookie Settings', 'qtranslate') ?></th>
				<td>
					<label for="disable_client_cookies"><input type="checkbox" name="disable_client_cookies" id="disable_client_cookies" value="1"<?php checked($q_config['disable_client_cookies']); disabled( $url_mode==QTX_URL_DOMAIN || $url_mode==QTX_URL_DOMAINS) ?> /> <?php printf(__('Disable language client cookie "%s" (not recommended).', 'qtranslate'),QTX_COOKIE_NAME_FRONT) ?></label>
					<p class="qtranxs_notes"><?php echo sprintf(__('Language cookie is auto-disabled for "%s" "Pre-Domain" and "Per-Domain", as language is always unambiguously defined by a url in those modes.','qtranslate'), __('URL Modification Mode', 'qtranslate')).' '.sprintf(__('Otherwise, use this option with a caution, for simple enough sites only. If checked, the user choice of browsing language will not be saved between sessions and some AJAX calls may deliver unexpected language, as well as some undesired language switching during browsing may occur under certain themes (%sRead More%s).', 'qtranslate'),'<a href="https://qtranslatexteam.wordpress.com/2015/02/26/browser-redirection-based-on-language/" target="_blank">','</a>') ?></p>
					<br />
					<label for="use_secure_cookie"><input type="checkbox" name="use_secure_cookie" id="use_secure_cookie" value="1"<?php checked($q_config['use_secure_cookie']) ?> /><?php printf(__('Make %s cookies available only through HTTPS connections.', 'qtranslate'),'qTranslate&#8209;X') ?></label>
					<p class="qtranxs_notes"><?php _e("Don't check this if you don't know what you're doing!", 'qtranslate') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Update Gettext Databases', 'qtranslate') ?></th>
				<td>
					<label for="auto_update_mo"><input type="checkbox" name="auto_update_mo" id="auto_update_mo" value="1"<?php checked($q_config['auto_update_mo']) ?>/> <?php _e('Automatically check for .mo-Database Updates of installed languages.', 'qtranslate') ?></label>
					<br/>
					<label for="update_mo_now"><input type="checkbox" name="update_mo_now" id="update_mo_now" value="1" /> <?php _e('Update Gettext databases now.', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php _e('qTranslate will query the Wordpress Localisation Repository every week and download the latest Gettext Databases (.mo Files).', 'qtranslate') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Date / Time Conversion', 'qtranslate') ?></th>
				<td>
					<label><input type="radio" name="use_strftime" value="<?php echo QTX_DATE; ?>" <?php checked($q_config['use_strftime'],QTX_DATE) ?>/> <?php _e('Use emulated date function.', 'qtranslate') ?></label><br/>
					<label><input type="radio" name="use_strftime" value="<?php echo QTX_DATE_OVERRIDE; ?>" <?php checked($q_config['use_strftime'],QTX_DATE_OVERRIDE) ?>/> <?php _e('Use emulated date function and replace formats with the predefined formats for each language.', 'qtranslate') ?></label><br/>
					<label><input type="radio" name="use_strftime" value="<?php echo QTX_STRFTIME; ?>" <?php checked($q_config['use_strftime'],QTX_STRFTIME) ?>/> <?php _e('Use strftime instead of date.', 'qtranslate') ?></label><br/>
					<label><input type="radio" name="use_strftime" value="<?php echo QTX_STRFTIME_OVERRIDE; ?>" <?php checked($q_config['use_strftime'],QTX_STRFTIME_OVERRIDE) ?>/> <?php _e('Use strftime instead of date and replace formats with the predefined formats for each language.', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php _e('Depending on the mode selected, additional customizations of the theme may be needed.', 'qtranslate') ?></p>
					<?php /*
					<br/><br/>
					<label><?php _e('If one of the above options "... replace formats with the predefined formats for each language" is in use, then exclude the following formats from being overridden:', 'qtranslate') ?></label><br/>
					<input type="text" name="ex_date_formats" id="qtranxs_ex_date_formats" value="<?php echo isset($q_config['ex_date_formats']) ? implode(' ',$q_config['ex_date_formats']) : QTX_EX_DATE_FORMATS_DEFAULT; ?>" style="width:100%"><br/>
					*/ ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Translation of options', 'qtranslate') ?></th>
				<td>
					<label for="filter_options_mode_all"><input type="radio" name="filter_options_mode" id="filter_options_mode_all" value=<?php echo '"'.QTX_FILTER_OPTIONS_ALL.'"'; checked($q_config['filter_options_mode'],QTX_FILTER_OPTIONS_ALL) ?> /> <?php _e('Filter all WordPress options for translation at front-end. It may hurt performance of the site, but ensures that all options are translated.', 'qtranslate') ?> <?php _e('Starting from version 3.2.5, only options with multilingual content get filtered, which should help on performance issues.', 'qtranslate') ?></label>
					<br />
					<label for="filter_options_mode_list"><input type="radio" name="filter_options_mode" id="filter_options_mode_list" value=<?php echo '"'.QTX_FILTER_OPTIONS_LIST.'"'; checked($q_config['filter_options_mode'],QTX_FILTER_OPTIONS_LIST) ?> /> <?php _e('Translate only options listed below (for experts only):', 'qtranslate') ?> </label>
					<br />
					<input type="text" name="filter_options" id="qtranxs_filter_options" value="<?php echo isset($q_config['filter_options']) ? implode(' ',$q_config['filter_options']) : QTX_FILTER_OPTIONS_DEFAULT; ?>" style="width:100%">
					<p class="qtranxs_notes"><?php printf(__('By default, all options are filtered to be translated at front-end for the sake of simplicity of configuration. However, for a developed site, this may cause a considerable performance degradation. Normally, there are very few options, which actually need a translation. You may simply list them above to minimize the performance impact, while still getting translations needed. Options names must match the field "%s" of table "%s" of WordPress database. A minimum common set of option, normally needed a translation, is already entered in the list above as a default example. Option names in the list may contain wildcard with symbol "%s".', 'qtranslate'), 'option_name', 'options', '%') ?></p>
				</td>
			</tr>
			<tr valign="top" id="option_editor_mode">
				<th scope="row"><?php _e('Editor Mode', 'qtranslate') ?></th>
				<td>
					<label for="qtranxs_editor_mode_lsb"><input type="radio" name="editor_mode" id="qtranxs_editor_mode_lsb" value="<?php echo QTX_EDITOR_MODE_LSB; ?>"<?php checked($q_config['editor_mode'], QTX_EDITOR_MODE_LSB) ?>/>&nbsp;<?php _e('Use Language Switching Buttons (LSB).', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php echo __('This is the default mode.', 'qtranslate').' '.__('Pages with translatable fields have Language Switching Buttons, which control what language is being edited, while admin language stays the same.', 'qtranslate') ?></p><br/>
					<label for="qtranxs_editor_mode_raw"><input type="radio" name="editor_mode" id="qtranxs_editor_mode_raw" value="<?php echo QTX_EDITOR_MODE_RAW; ?>"<?php checked($q_config['editor_mode'], QTX_EDITOR_MODE_RAW) ?>/>&nbsp;<?php _e('Editor Raw Mode', 'qtranslate') ?>. <?php _e('Do not use Language Switching Buttons to edit multi-language text entries.', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php _e('Some people prefer to edit the raw entries containing all languages together separated by language defining tags, as they are stored in database.', 'qtranslate') ?></p><br/>
					<label for="qtranxs_editor_mode_single"><input type="radio" name="editor_mode" id="qtranxs_editor_mode_single" value="<?php echo QTX_EDITOR_MODE_SINGLGE; ?>"<?php checked($q_config['editor_mode'], QTX_EDITOR_MODE_SINGLGE) ?>/>&nbsp;<?php echo __('Single Language Mode.', 'qtranslate').' '.__('The language edited is the same as admin language.', 'qtranslate') ?></label>
					<p class="qtranxs_notes"><?php echo __('Edit language cannot be switched without page re-loading. Try this mode, if some of the advanced translatable fields do not properly respond to the Language Switching Buttons due to incompatibility with a plugin, which severely alters the default WP behaviour. This mode is the most compatible with other themes and plugins.', 'qtranslate').' '.__('One may find convenient to use the default Editor Mode, while remembering not to switch edit languages on custom advanced translatable fields, where LSB do not work.', 'qtranslate') ?></p>
				</td>
			</tr>
			<?php
				$options=qtranxf_fetch_file_selection(QTRANSLATE_DIR.'/admin/css/opLSBStyle');
				if($options){
			?>
			<tr valign="top" id="option_lsb_style">
				<th scope="row"><?php _e('LSB Style', 'qtranslate') ?></th>
				<td>
					<fieldset>
						<legend class="hidden"><?php _e('LSB Style', 'qtranslate') ?></legend>
						<label><?php printf(__('Choose CSS style for how Language Switching Buttons are rendered:', 'qtranslate')) ?></label>
						<br/><?php printf(__('LSB %s-wrap classes:', 'qtranslate'), 'ul') ?>&nbsp;<input type="text" name="lsb_style_wrap_class" id="lsb_style_wrap_class" value="<?php echo $q_config['lsb_style_wrap_class']; ?>" size="50" >
						<br/><?php _e('Active button class:', 'qtranslate') ?>&nbsp;<input type="text" name="lsb_style_active_class" id="lsb_style_active_class" value="<?php echo $q_config['lsb_style_active_class']; ?>" size="40" >
						<p class="qtranxs_notes"><?php _e('The above is reset to an appropriate default, if the below is changed.', 'qtranslate') ?></p>
						<br/><?php _e('CSS set:', 'qtranslate') ?>&nbsp;<select name="lsb_style" id="lsb_style"><?php
							foreach($options as $nm => $val){
								echo '<option value="'.$val.'"'.selected($val,$q_config['lsb_style']).'>'.$nm.'</option>';
							}
							echo '<option value="custom"'.selected('custom',$q_config['lsb_style']).'>'.__('Use custom CSS', 'qtranslate').'</option>';
						?></select>
						<p class="qtranxs_notes"><?php printf(__('Choice "%s" disables this option and allows one to use its own custom CSS provided by other means.', 'qtranslate'),__('Use custom CSS', 'qtranslate')) ?></p>
					</fieldset>
				</td>
			</tr>
			<?php
				}
			?>
			<tr valign="top" id="option_highlight_mode">
				<?php
				$highlight_mode = $q_config['highlight_mode'];
				// reset default custom CSS when the field is empty, or when the "custom" option is not checked
				if(empty($q_config['highlight_mode_custom_css']) || $highlight_mode != QTX_HIGHLIGHT_MODE_CUSTOM_CSS) {
					$highlight_mode_custom_css = qtranxf_get_admin_highlight_css($highlight_mode);
				} else {
					$highlight_mode_custom_css = $q_config['highlight_mode_custom_css'];
				}
				?>
				<th scope="row"><?php _e('Highlight Style', 'qtranslate') ?></th>
				<td>
					<p class="qtranxs_explanation"><?php _e('When there are many integrated or customized translatable fields, it may become confusing to know which field has multilingual value. The highlighting of translatable fields may come handy then:', 'qtranslate') ?></p>
					<fieldset>
						<legend class="hidden"><?php _e('Highlight Style', 'qtranslate') ?></legend>
						<label title="<?php _e('Do not highlight the translatable fields.', 'qtranslate') ?>">
							<input type="radio" name="highlight_mode" value="<?php echo QTX_HIGHLIGHT_MODE_NONE; ?>" <?php checked($highlight_mode, QTX_HIGHLIGHT_MODE_NONE) ?> />
							<?php _e('Do not highlight the translatable fields.', 'qtranslate') ?>
						</label><br/>
						<label title="<?php _e('Show a line on the left border of translatable fields.', 'qtranslate') ?>">
							<input type="radio" name="highlight_mode" value="<?php echo QTX_HIGHLIGHT_MODE_BORDER_LEFT; ?>" <?php checked($highlight_mode, QTX_HIGHLIGHT_MODE_BORDER_LEFT) ?> />
							<?php _e('Show a line on the left border of translatable fields.', 'qtranslate') ?>
						</label><br/>
						<label title="<?php _e('Draw a border around translatable fields.', 'qtranslate') ?>">
							<input type="radio" name="highlight_mode" value="<?php echo QTX_HIGHLIGHT_MODE_BORDER; ?>" <?php checked($highlight_mode, QTX_HIGHLIGHT_MODE_BORDER) ?> />
							<?php _e('Draw a border around translatable fields.', 'qtranslate') ?>
						</label><br/>
						<label title="<?php _e('Show a shadow on the left of translatable fields.', 'qtranslate') ?>">
							<input type="radio" name="highlight_mode" value="<?php echo QTX_HIGHLIGHT_MODE_LEFT_SHADOW; ?>" <?php checked($highlight_mode, QTX_HIGHLIGHT_MODE_LEFT_SHADOW) ?> />
							<?php _e('Show a shadow on the left of translatable fields.', 'qtranslate') ?>
						</label><br/>
						<label title="<?php _e('Outline border around translatable fields.', 'qtranslate') ?>">
							<input type="radio" name="highlight_mode" value="<?php echo QTX_HIGHLIGHT_MODE_OUTLINE; ?>" <?php checked($highlight_mode, QTX_HIGHLIGHT_MODE_OUTLINE) ?> />
							<?php _e('Outline border around translatable fields.', 'qtranslate') ?>
						</label><br/>
						<label title="<?php _e('Use custom CSS', 'qtranslate') ?>">
							<input type="radio" name="highlight_mode" value="<?php echo QTX_HIGHLIGHT_MODE_CUSTOM_CSS; ?>" <?php checked($highlight_mode, QTX_HIGHLIGHT_MODE_CUSTOM_CSS) ?>/>
							<?php echo __('Use custom CSS', 'qtranslate').':' ?>
						</label><br/>
					</fieldset><br />
					<textarea id="highlight_mode_custom_css" name="highlight_mode_custom_css" style="width:100%"><?php echo esc_textarea($highlight_mode_custom_css) ?></textarea>
					<p class="qtranxs_notes"><?php echo __('To reset to default, clear the text.', 'qtranslate').' '; printf(__('The color in use is taken from your profile option %s, the third color.', 'qtranslate'), '"<a href="'.admin_url('/profile.php').'">'.qtranxf_translate_wp('Admin Color Scheme').'</a>"') ?></p>
				</td>
			</tr>
<?php /*
			<tr valign="top">
				<th scope="row"><?php _e('Debugging Information', 'qtranslate') ?></th>
				<td>
					<p class="qtranxs_explanation"><?php printf(__('If you encounter any problems and you are unable to solve them yourself, you can visit the <a href="%s">Support Forum</a>. Posting the following Content will help other detect any misconfigurations.', 'qtranslate'), 'https://wordpress.org/support/plugin/qtranslate-x') ?></p>
					<textarea readonly="readonly" id="qtranxs_debug"><?php
						$q_config_copy = $q_config;
						// remove information to keep data anonymous and other not needed things
						unset($q_config_copy['url_info']);
						unset($q_config_copy['js']);
						unset($q_config_copy['windows_locale']);
						//unset($q_config_copy['pre_domain']);
						unset($q_config_copy['term_name']);
						echo htmlspecialchars(print_r($q_config_copy, true));
					?></textarea>
				</td>
			</tr>
*/ ?>
		</table>
	<?php qtranxf_admin_section_end('advanced') ?>
	<?php qtranxf_admin_section_start('integration') ?>
		<table class="form-table">
			<tr valign="top">
				<td scope="row" colspan="2"><p class="heading"><?php printf(__('If your theme or some plugins are not fully integrated with %s, suggest their authors to review the %sIntegration Guide%s. In many cases they would only need to create a simple text file in order to be fully compatible with %s. Alternatively, you may create such a file for them and for yourselves.', 'qtranslate'), 'qTranslate&#8209;X', '<a href="https://qtranslatexteam.wordpress.com/integration/" target="_blank">', '</a>', 'qTranslate&#8209;X');
				echo ' '; printf(__('Read %sIntegration Guide%s for more information on how to customize the configuration of %s.', 'qtranslate'), '<a href="https://qtranslatexteam.wordpress.com/integration/" target="_blank">', '</a>', 'qTranslate&#8209;X'); ?></p></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Configuration Files', 'qtranslate') ?></th>
				<td><label for="qtranxs_config_files" class="qtranxs_explanation"><?php printf(__('List of configuration files. Unless prefixed with "%s", paths are relative to %s variable: %s. Absolute paths are also acceptable.', 'qtranslate'), './', 'WP_CONTENT_DIR', trailingslashit(WP_CONTENT_DIR)) ?></label>
				<br/><textarea name="json_config_files" id="qtranxs_config_files" rows="4" style="width:100%"><?php if(isset($_POST['json_config_files'])) echo sanitize_text_field(stripslashes($_POST['json_config_files'])); else echo implode(PHP_EOL,$q_config['config_files']); ?></textarea>
				<p class="qtranxs_notes"><?php printf(__('The list gets auto-updated on a 3rd-party integrated plugin activation/deactivation. You may also add your own custom files for your theme or plugins. File "%s" is the default configuration loaded from this plugin folder. It is not recommended to modify any configuration file from other authors, but you may alter any configuration item through your own custom file appended to the end of this list.', 'qtranslate'), './i18n-config.json');
				echo ' '; printf(__('Use "%s" to review the resulting combined configuration from all "%s" and this option.', 'qtranslate'), '<a href="'.admin_url('options-general.php?page=qtranslate-x&config_inspector=show').'">'.__('Configuration Inspector', 'qtranslate').'</a>', __('Custom Configuration', 'qtranslate'));
				echo ' '; printf(__('Please, read %sIntegration Guide%s for more information.', 'qtranslate'), '<a href="https://qtranslatexteam.wordpress.com/integration/" target="_blank">', '</a>');
				echo ' '.__('To reset to default, clear the text.', 'qtranslate') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Custom Configuration', 'qtranslate') ?></th>
				<td><label for="qtranxs_json_custom_i18n_config" class="qtranxs_explanation"><?php printf(__('Additional custom JSON-encoded configuration of %s for all admin pages. It is processed after all files from option "%s" are loaded, providing opportunity to add or to override configuration tokens as necessary.', 'qtranslate'), 'qTranslate&#8209;X', __('Configuration Files', 'qtranslate')); ?></label>
				<br/><textarea name="json_custom_i18n_config" id="qtranxs_json_custom_i18n_config" rows="4" style="width:100%"><?php if(isset($_POST['json_custom_i18n_config'])) echo sanitize_text_field(stripslashes($_POST['json_custom_i18n_config'])); else if(!empty($q_config['custom_i18n_config'])) echo qtranxf_json_encode($q_config['custom_i18n_config']) ?></textarea>
				<p class="qtranxs_notes"><?php printf(__('It would make no difference, if the content of this field is stored in a file, which name is listed last in option "%s". Therefore, this field only provides flexibility for the sake of convenience.', 'qtranslate'), __('Configuration Files', 'qtranslate'));
				echo ' '; printf(__('Please, read %sIntegration Guide%s for more information.', 'qtranslate'), '<a href="https://qtranslatexteam.wordpress.com/integration/" target="_blank">', '</a>');
				echo ' '; printf(__('Use "%s" to review the resulting combined configuration from all "%s" and this option.', 'qtranslate'), '<a href="'.admin_url('options-general.php?page=qtranslate-x&config_inspector=show').'">'.__('Configuration Inspector', 'qtranslate').'</a>', __('Configuration Files', 'qtranslate'));
				?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Custom Fields', 'qtranslate') ?></th>
				<td><p class="qtranxs_explanation">
					<?php printf(__('Enter "%s" or "%s" attribute of text fields from your theme, which you wish to translate. This applies to post, page and media editors (%s). To lookup "%s" or "%s", right-click on the field in the post or the page editor and choose "%s". Look for an attribute of the field named "%s" or "%s". Enter it below, as many as you need, space- or comma-separated. After saving configuration, these fields will start responding to the language switching buttons, and you can enter different text for each language. The input fields of type %s will be parsed using %s syntax, while single line text fields will use %s syntax. If you need to override this behaviour, prepend prefix %s or %s to the name of the field to specify which syntax to use. For more information, read %sFAQ%s.', 'qtranslate'),'id','class','/wp-admin/post*','id','class',_x('Inspect Element','browser option','qtranslate'),'id','class','\'textarea\'',esc_html('<!--:-->'),'[:]','\'<\'','\'[\'','<a href="https://qtranslatexteam.wordpress.com/faq/">','</a>') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: right">id</th>
				<td><label for="qtranxs_custom_fields" class="qtranxs_explanation">
					<input type="text" name="custom_fields" id="qtranxs_custom_fields" value="<?php echo implode(' ',$q_config['custom_fields']) ?>" style="width:100%"></label>
					<p class="qtranxs_notes"><?php _e('The value of "id" attribute is normally unique within one page, otherwise the first field found, having an id specified, is picked up.', 'qtranslate') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: right">class</th>
				<td><label for="qtranxs_custom_field_classes" class="qtranxs_explanation">
					<input type="text" name="custom_field_classes" id="qtranxs_custom_field_classes" value="<?php echo implode(' ',$q_config['custom_field_classes']) ?>" style="width:100%"></label>
					<p class="qtranxs_notes"><?php printf(__('All the fields of specified classes will respond to Language Switching Buttons. Be careful not to include a class, which would affect language-neutral fields. If you cannot uniquely identify a field needed neither by %s, nor by %s attribute, report the issue on %sSupport Forum%s', 'qtranslate'),'"id"', '"class"', '<a href="https://wordpress.org/support/plugin/qtranslate-x">','</a>') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Custom Filters', 'qtranslate') ?></th>
				<td><label for="qtranxs_text_field_filters" class="qtranxs_explanation">
					<input type="text" name="text_field_filters" id="qtranxs_text_field_filters" value="<?php echo implode(' ',$q_config['text_field_filters']) ?>" style="width:100%"></label>
					<p class="qtranxs_notes"><?php printf(__('Names of filters (which are enabled on theme or other plugins via %s function) to add translation to. For more information, read %sFAQ%s.', 'qtranslate'),'apply_filters()','<a href="https://qtranslatexteam.wordpress.com/faq/#CustomFields">','</a>') ?></p>
				</td>
			</tr>
			<?php /* ?>
			<tr valign="top">
				<th scope="row"><?php _e('Custom Admin Pages', 'qtranslate') ?></th>
				<td><label for="qtranxs_custom_pages" class="qtranxs_explanation"><input type="text" name="custom_pages" id="qtranxs_custom_pages" value="<?php echo implode(' ',$q_config['custom_pages']) ?>" style="width:100%"></label>
					<p class="qtranxs_notes"><?php printf(__('List the custom admin page paths for which you wish Language Switching Buttons to show up. The Buttons will then control fields configured in "Custom Fields" section. You may only include part of the full URL after %s, including a distinctive query string if needed. As many as desired pages can be listed space/comma separated. For more information, read %sFAQ%s.', 'qtranslate'),'/wp-admin/','<a href="https://qtranslatexteam.wordpress.com/faq/">','</a>') ?></p>
				</td>
			</tr>
			<?php */ ?>
			<tr valign="top">
				<th scope="row"><?php _e('Compatibility Functions', 'qtranslate') ?></th>
				<td>
					<label for="qtranxs_qtrans_compatibility"><input type="checkbox" name="qtrans_compatibility" id="qtranxs_qtrans_compatibility" value="1"<?php checked($q_config['qtrans_compatibility']) ?>/>&nbsp;<?php printf(__('Enable function name compatibility (%s).', 'qtranslate'), 'qtrans_convertURL, qtrans_getAvailableLanguages, qtrans_generateLanguageSelectCode, qtrans_getLanguage, qtrans_getLanguageName, qtrans_getSortedLanguages, qtrans_join, qtrans_split, qtrans_use, qtrans_useCurrentLanguageIfNotFoundShowAvailable, qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage, qtrans_useDefaultLanguage, qtrans_useTermLib') ?></label><br/>
					<p class="qtranxs_notes"><?php printf(__('Some plugins and themes use direct calls to the functions listed, which are defined in former %s plugin and some of its forks. Turning this flag on will enable those function to exists, which will make the dependent plugins and themes to work. WordPress policy prohibits to define functions with the same names as in other plugins, since it generates user-unfriendly fatal errors, when two conflicting plugins are activated simultaneously. Before turning this option on, you have to make sure that there are no other plugins active, which define those functions.', 'qtranslate'), '<a href="https://wordpress.org/plugins/qtranslate/" target="_blank">qTranslate</a>') ?></p>
				</td>
			</tr>
		</table>
	<?php qtranxf_admin_section_end('integration');
		// Allow to load additional services
		do_action('qtranslate_configuration', $clean_uri);
	?>
	</div><?php //<!-- /tabs-container --> ?>
	</form>
<?php }?>
</div><!-- /wrap -->
<div class="wrap">

<div class="tabs-content">
<?php qtranxf_admin_section_start('languages') ?>
<?php
class QTX_LanguageList extends WP_List_Table
{
	private $_clean_uri;
	private $_language_names;

	public function __construct($language_names,$clean_uri) {
		parent::__construct(array('screen' => 'language'));
		$this->_language_names = $language_names;
		$this->_clean_uri = $clean_uri;
	}

	public function get_columns() {
		return array(
			'code' => _x('Code', 'Two-letter Language Code meant.', 'qtranslate'),
			'flag' => __('Flag', 'qtranslate'),
			'name' => __('Name', 'qtranslate'),
			'action' => __('Action', 'qtranslate'),
			'edit' => __('Edit', 'qtranslate'),
			'stored' => __('Stored', 'qtranslate')
		);
	}

	/* public function get_sortable_columns() {
		return array(
			'code' => array('code',false),
			'name' => array('name',true) //true means it's already sorted
		);
	}*/

	protected function column_default( $item, $column_name ) { return $item[$column_name]; }
	protected function get_default_primary_column_name() { return 'name'; }
	protected function display_tablenav($which){}
	protected function get_table_classes() { return array( 'widefat', 'qtranxs-language-list' ); }

	public function prepare_items() {
		global $q_config;
		$flags = qtranxf_language_configured('flag');
		$languages_stored = get_option('qtranslate_language_names',array());
		$languages_predef = qtranxf_default_language_name();
		$flag_location_url = qtranxf_flag_location();
		$flag_location_dir = trailingslashit(WP_CONTENT_DIR).$q_config['flag_location'];
		$flag_location_url_def = content_url(qtranxf_flag_location_default());
		//trailingslashit(content_url()).'/plugins/'.basename(dirname(QTRANSLATE_FILE)).'/flags/';
		$clean_uri = $this->_clean_uri;
		$data = array();
		foreach($this->_language_names as $lang => $language){ if($lang=='code') continue;
			$flag = $flags[$lang];
			if(file_exists($flag_location_dir.$flag)){
				$flag_url = $flag_location_url.$flag;
			}else{
				$flag_url = $flag_location_url_def.$flag;
			}
			$data[] = array(
				'code' => $lang,
				'flag' => '<img src="'.$flag_url.'" alt="'.sprintf(__('%s Flag', 'qtranslate'), $language).'">',
				'name' => $language,
				'action' => in_array($lang,$q_config['enabled_languages']) ? ($q_config['default_language']==$lang ? __('Default', 'qtranslate') : '<a class="edit" href="'.$clean_uri.'&disable='.$lang.'#languages">'.__('Disable', 'qtranslate').'</a>') : '<a class="edit" href="'.$clean_uri.'&enable='.$lang.'#languages">'.__('Enable', 'qtranslate').'</a>',
				'edit' => '<a class="edit" href="'.$clean_uri.'&edit='.$lang.'">'.__('Edit', 'qtranslate').'</a>',
				'stored' => !isset($languages_stored[$lang]) ? __('Pre-Defined', 'qtranslate') : '<a class="delete" href="'.$clean_uri.'&delete='.$lang.'#languages">'.(isset($languages_predef[$lang]) ? __('Reset', 'qtranslate') : __('Delete', 'qtranslate')). '</a>'
			);
		}
		$this->items = $data;
	}
}
?>
<div id="col-container">

<div id="col-right">
<div class="col-wrap">
<h3><?php _e('List of Configured Languages','qtranslate') ?></h3>
<p class="qtranxs_notes"><?php
	$language_names = qtranxf_language_configured('language_name');
	printf(__('Only enabled languages are loaded at front-end, while all %d configured languages are listed here.','qtranslate'),count($language_names));
	echo ' '; _e('The table below contains both pre-defined and manually added or modified languages.','qtranslate');
	echo ' '; printf(__('You may %s or %s a language, or %s manually added language, or %s previous modifications of a pre-defined language.', 'qtranslate'), '"'.__('Enable', 'qtranslate').'"', '"'.__('Disable', 'qtranslate').'"', '"'.__('Delete', 'qtranslate').'"', '"'.__('Reset', 'qtranslate').'"');
	echo ' '; printf(__('Click %s to modify language properties.', 'qtranslate'), '"'.__('Edit', 'qtranslate').'"');
?></p>
<?php
	$tbl = new QTX_LanguageList($language_names,$clean_uri);
	$tbl->prepare_items();
	$tbl->display();
/* ?>
<table class="widefat">
	<thead><tr><?php $tbl->print_column_headers(true) ?></tr></thead>
	<tbody id="the-list" class="qtranxs-language-list" class="list:cat">
<?php
	$flags = qtranxf_language_configured('flag');
	$languages_stored = get_option('qtranslate_language_names',array());
	$languages_predef = qtranxf_default_language_name();
	$flag_location_url = qtranxf_flag_location();
	$flag_location_dir = trailingslashit(WP_CONTENT_DIR).$q_config['flag_location'];
	$flag_location_url_def = content_url(qtranxf_flag_location_default());
	//trailingslashit(content_url()).'/plugins/'.basename(dirname(QTRANSLATE_FILE)).'/flags/';
	foreach($language_names as $lang => $language){ if($lang=='code') continue;
		$flag = $flags[$lang];
		if(file_exists($flag_location_dir.$flag)){
			$flag_url = $flag_location_url.$flag;
		}else{
			$flag_url = $flag_location_url_def.$flag;
		}
?>
	<tr>
		<td><?php echo $lang; ?></td>
		<td><img src="<?php echo $flag_url; ?>" alt="<?php echo sprintf(__('%s Flag', 'qtranslate'), $language) ?>"></td>
		<td><?php echo $language; ?></td>
		<td><?php if(in_array($lang,$q_config['enabled_languages'])) { if($q_config['default_language']==$lang){ _e('Default', 'qtranslate'); } else{ ?><a class="edit" href="<?php echo $clean_uri; ?>&disable=<?php echo $lang; ?>#languages"><?php _e('Disable', 'qtranslate') ?></a><?php } } else { ?><a class="edit" href="<?php echo $clean_uri; ?>&enable=<?php echo $lang; ?>#languages"><?php _e('Enable', 'qtranslate') ?></a><?php } ?></td>
		<td><a class="edit" href="<?php echo $clean_uri; ?>&edit=<?php echo $lang; ?>"><?php _e('Edit', 'qtranslate') ?></a></td>
		<td><?php if(!isset($languages_stored[$lang])){ _e('Pre-Defined', 'qtranslate'); } else { ?><a class="delete" href="<?php echo $clean_uri; ?>&delete=<?php echo $lang; ?>#languages"><?php if(isset($languages_predef[$lang])) _e('Reset', 'qtranslate'); else _e('Delete', 'qtranslate') ?></a><?php } ?></td>
	</tr>
<?php } ?>
	</tbody>
	<tfoot><tr><?php $tbl->print_column_headers(false) ?></tr></tfoot>
</table>
<?php */ ?>
<p class="qtranxs_notes"><?php _e('Enabling a language will cause qTranslate to update the Gettext-Database for the language, which can take a while depending on your server\'s connection speed.', 'qtranslate') ?></p>
</div>
</div>

<div id="col-left">
<div class="col-wrap">
<div class="form-wrap">
<h3><?php _e('Add Language', 'qtranslate') ?></h3>
<form action="<?php echo $clean_uri ?>" name="addlang" id="addlang" method="post" class="add:the-list: validate">
<?php
	wp_nonce_field($nonce_action); // Prevent CSRF
	qtranxf_language_form();
	qtranxf_admin_section_end('languages',__('Add Language &raquo;', 'qtranslate'));
?>
</form></div></div></div></div></div>
<?php } ?>
</div>
<?php
}
