<?php
if ( !defined( 'ABSPATH' ) ) exit;

require_once(QTRANSLATE_DIR.'/admin/qtx_admin_options.php');
require_once(QTRANSLATE_DIR.'/admin/qtx_import_export.php');

function qtranxf_editConfig(){
	global $q_config;
	if(!qtranxf_verify_nonce('qtranslate-x_configuration_form')) return;
	// init some needed variables
	if(!isset($q_config['url_info']['errors'])) $q_config['url_info']['errors'] = array();
	if(!isset($q_config['url_info']['warnings'])) $q_config['url_info']['warnings'] = array();
	if(!isset($q_config['url_info']['messages'])) $q_config['url_info']['messages'] = array();

	$errors = &$q_config['url_info']['errors'];
	//$warnings = &$q_config['url_info']['warnings'];
	$messages = &$q_config['url_info']['messages'];

	$q_config['posted'] = array();
	$q_config['posted']['lang_props'] = array();
	$q_config['posted']['language_code'] = '';
	$q_config['posted']['original_lang'] = '';

	$language_code = &$q_config['posted']['language_code'];
	$lang_props = &$q_config['posted']['lang_props'];
	$original_lang = &$q_config['posted']['original_lang'];

	// check for action
	if(isset($_POST['qtranslate_reset']) && isset($_POST['qtranslate_reset2'])) {
		$messages[] = __('qTranslate has been reset.', 'qtranslate');
	} elseif(isset($_POST['default_language'])) {

		qtranxf_updateSettings();

		//execute actions
		qtranxf_executeOnUpdate();
	}

	if(isset($_POST['original_lang'])) {
		// validate form input
		$original_lang = sanitize_text_field($_POST['original_lang']);
		$lang = sanitize_text_field($_POST['language_code']);
		if($_POST['language_na_message']=='') $errors[] = __('The Language must have a Not-Available Message!', 'qtranslate');
		if(strlen($_POST['language_locale'])<2) $errors[] = __('The Language must have a Locale!', 'qtranslate');
		if($_POST['language_name']=='') $errors[] = __('The Language must have a name!', 'qtranslate');
		if(strlen($lang)!=2) $errors[] = __('Language Code has to be 2 characters long!', 'qtranslate');
		$langs=array(); qtranxf_load_languages($langs);
		$language_names = $langs['language_name'];
		if(empty($errors)){
			if(empty($original_lang)) {
				// new language
				if(isset($language_names[$lang])) {
					$errors[] = __('There is already a language with the same Language Code!', 'qtranslate');
				} 
			}else{
				// language update
				if($lang!=$original_lang&&isset($language_names[$lang])) {
					$errors[] = __('There is already a language with the same Language Code!', 'qtranslate');
				} else {
					if($lang!=$original_lang){
						// remove old language
						qtranxf_unsetLanguage($langs,$original_lang);
						qtranxf_unsetLanguage($q_config,$original_lang);
						// if was enabled, set modified one to enabled too
						foreach($q_config['enabled_languages'] as $k => $lng) {
							if($lng != $original_lang) continue;
							$q_config['enabled_languages'][$k] = $lng;
							break;
						}
					}
					if($original_lang==$q_config['default_language']){
						// was default, so set modified the default
						$q_config['default_language'] = $lang;
					}
					if($q_config['language'] == $original_lang){
						qtranxf_setLanguageAdmin($lang);
					}
				}
			}
		}

		$lang_props['language_name'] = sanitize_text_field($_POST['language_name']);
		$lang_props['flag'] = sanitize_text_field($_POST['language_flag']);
		$lang_props['locale'] = sanitize_text_field($_POST['language_locale']);
		$lang_props['locale_html'] = sanitize_text_field($_POST['language_locale_html']);
		$lang_props['date_format'] = sanitize_text_field(stripslashes($_POST['language_date_format']));
		$lang_props['time_format'] = sanitize_text_field(stripslashes($_POST['language_time_format']));
		$lang_props['not_available'] = wp_kses_post(stripslashes($_POST['language_na_message']));//allow valid HTML
		if(empty($errors)) {
			// everything is fine, insert language
			foreach($lang_props as $k => $v){
				$q_config[$k][$lang] = $v;
			}
			qtranxf_copyLanguage($langs, $q_config, $lang);
			qtranxf_save_languages($langs);
			qtranxf_enableLanguage($lang);
			//qtranxf_update_config_header_css();

			$original_lang = $lang;
			$s = 'Custom Language Properties Used';
			$b = 'I use the following language properties for '.$lang.':'.PHP_EOL .PHP_EOL;
			foreach($lang_props as $k => $v){
				$b .= $k.': '.$v.PHP_EOL;
			}
			$b .= PHP_EOL .'which should probably be used as a default preset on the plugin.'.PHP_EOL;
			$b .= PHP_EOL .'Thank you very much!'.PHP_EOL;
			$u = 'qtranslateteam@gmail.com?subject='.rawurlencode($s).'&body='.rawurlencode($b);
			$messages[] = sprintf(__('The new language properties have been saved. If you think these properties should be the preset default, please %ssend email%s to the development team.', 'qtranslate'),'<a href="mailto:'.$u.'"><strong>','</strong></a>');
		}
		if(!empty($errors)||isset($_GET['edit'])) {
			// get old values in the form
			$language_code = $lang;
		}else{
			//reset form for new language
			$lang_props = array();
			$original_lang = '';
		}
	}
	elseif(isset($_GET['convert'])){
		// update language tags
		global $wpdb;
		$wpdb->show_errors(); @set_time_limit(0);
		$cnt = 0;
		//this will not work correctly if set of languages is different
		foreach($q_config['enabled_languages'] as $lang) {
			$cnt +=
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[lang_'.$lang.']","[:'.$lang.']"),  post_content = REPLACE(post_content, "[lang_'.$lang.']","[:'.$lang.']")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[/lang_'.$lang.']","[:]"),  post_content = REPLACE(post_content, "[/lang_'.$lang.']","[:]")');
		}
		if($cnt > 0){
			$messages[] = sprintf(__('%d database entries have been converted.', 'qtranslate'), $cnt);
		}else{
			$messages[] = __('No database entry has been affected while processing the conversion request.', 'qtranslate');
		}
	}
	elseif(isset($_GET['markdefault'])){
		// update language tags
		global $wpdb;
		$wpdb->show_errors(); @set_time_limit(0);
		$result = $wpdb->get_results('SELECT ID, post_content, post_title, post_excerpt, post_type FROM '.$wpdb->posts.' WHERE post_status = \'publish\' AND  (post_type = \'post\' OR post_type = \'page\') AND NOT (post_content LIKE \'%<!--:-->%\' OR post_title LIKE \'%<!--:-->%\' OR post_content LIKE \'%![:!]%\' ESCAPE \'!\' OR post_title LIKE \'%![:!]%\' ESCAPE \'!\')');
		if(is_array($result)){
			$cnt_page = 0;
			$cnt_post = 0;
			foreach($result as $post) {
				$title=qtranxf_mark_default($post->post_title);
				$content=qtranxf_mark_default($post->post_content);
				$excerpt=qtranxf_mark_default($post->post_excerpt);
				if( $title==$post->post_title && $content==$post->post_content && $excerpt==$post->post_excerpt ) continue;
				switch($post->post_type){
					case 'post': ++$cnt_post; break;
					case 'page': ++$cnt_page; break;
				}
				//qtranxf_dbg_log('markdefault:'. PHP_EOL .'title old: '.$post->post_title. PHP_EOL .'title new: '.$title. PHP_EOL .'content old: '.$post->post_content. PHP_EOL .'content new: '.$content); continue;
				$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->posts.' set post_content = %s, post_title = %s, post_excerpt = %s WHERE ID = %d', $content, $title, $excerpt, $post->ID));
			}

			if($cnt_page > 0) $messages[] = sprintf(__('%d pages have been processed to set the default language.', 'qtranslate'), $cnt_page);
			else $messages[] = __('No initially untranslated pages found to set the default language', 'qtranslate');

			if($cnt_post > 0) $messages[] = sprintf(__('%d posts have been processed to set the default language.', 'qtranslate'), $cnt_post);
			else $messages[] = __('No initially untranslated posts found to set the default language.', 'qtranslate');

			$messages[] = sprintf(__('Post types other than "post" or "page", as well as unpublished entries, will have to be adjusted manually as needed, since there is no common way to automate setting the default language otherwise. It can be done with a custom script though. You may request a %spaid support%s for this.', 'qtranslate'), '<a href="https://qtranslatexteam.wordpress.com/contact-us/">', '</a>');
		}
	}
	elseif(isset($_GET['edit'])){
		$lang = sanitize_text_field($_GET['edit']);
		$lang = preg_replace('/[^a-z]/i', '', $lang);
		if(strlen($lang) != 2){
			$errors[] = __('Language Code has to be 2 characters long!', 'qtranslate');
		}
		$original_lang = $lang;
		$language_code = $lang;
		//$langs = $q_config;
		$langs = array(); qtranxf_languages_configured($langs);
		$lang_props['language_name'] = isset($langs['language_name'][$lang])?$langs['language_name'][$lang]:'';
		$lang_props['locale'] = isset($langs['locale'][$lang])?$langs['locale'][$lang]:'';
		$lang_props['locale_html'] = isset($langs['locale_html'][$lang])?$langs['locale_html'][$lang]:'';
		$lang_props['date_format'] = isset($langs['date_format'][$lang])?$langs['date_format'][$lang]:'';
		$lang_props['time_format'] = isset($langs['time_format'][$lang])?$langs['time_format'][$lang]:'';
		$lang_props['not_available'] = isset($langs['not_available'][$lang])?$langs['not_available'][$lang]:'';
		$lang_props['flag'] = isset($langs['flag'][$lang])?$langs['flag'][$lang]:'';
	}
	elseif(isset($_GET['delete'])){
		$lang = sanitize_text_field($_GET['delete']);
		// validate delete (protect code)
		//if($q_config['default_language']==$lang) $errors[] = 'Cannot delete Default Language!';
		//if(!isset($q_config['language_name'][$lang])||strtolower($lang)=='code') $errors[] = __('No such language!', 'qtranslate');
		//if(empty($errors)) {
		//	// everything seems fine, delete language
		$err = qtranxf_deleteLanguage($lang);
		if(!empty($err)) $errors[] = $err;
		//}
	}
	elseif(isset($_GET['enable'])){
		$lang = sanitize_text_field($_GET['enable']);
		// enable validate
		if(!qtranxf_enableLanguage($lang)) {
			$errors[] = __('Language is already enabled or invalid!', 'qtranslate');
		}
	}
	elseif(isset($_GET['disable'])){
		$lang = sanitize_text_field($_GET['disable']);
		// enable validate
		if($lang==$q_config['default_language'])
			$errors[] = __('Cannot disable Default Language!', 'qtranslate');
		if(!qtranxf_isEnabled($lang))
			if(!isset($q_config['language_name'][$lang]))
				$errors[] = __('No such language!', 'qtranslate');
		// everything seems fine, disable language
		if(empty($errors) && !qtranxf_disableLanguage($lang)) {
			$errors[] = __('Language is already disabled!', 'qtranslate');
		}
	}
	elseif(isset($_GET['moveup'])){
		$lang = sanitize_text_field($_GET['moveup']);
		$languages = qtranxf_getSortedLanguages();
		$msg = __('No such language!', 'qtranslate');
		foreach($languages as $key => $language) {
			if($language!=$lang) continue;
			if($key==0) {
				$msg = __('Language is already first!', 'qtranslate');
				break;
			}
			$languages[$key] = $languages[$key-1];
			$languages[$key-1] = $language;
			$q_config['enabled_languages'] = $languages;
			$msg = __('New order saved.', 'qtranslate');
			qtranxf_update_config_header_css();
			break;
		}
		$messages[] = $msg;
	}
	elseif(isset($_GET['movedown'])){
		$lang = sanitize_text_field($_GET['movedown']);
		$languages = qtranxf_getSortedLanguages();
		$msg = __('No such language!', 'qtranslate');
		foreach($languages as $key => $language) {
			if($language!=$lang) continue;
			if($key==sizeof($languages)-1) {
				$msg = __('Language is already last!', 'qtranslate');
				break;
			}
			$languages[$key] = $languages[$key+1];
			$languages[$key+1] = $language;
			$q_config['enabled_languages'] = $languages;
			$msg = __('New order saved.', 'qtranslate');
			qtranxf_update_config_header_css();
			break;
		}
		$messages[] = $msg;
	}

	do_action('qtranslate_editConfig');

	$everything_fine = ((isset($_POST['submit'])||isset($_GET['delete'])||isset($_GET['enable'])||isset($_GET['disable'])||isset($_GET['moveup'])||isset($_GET['movedown']))&&empty($errors));
	if($everything_fine) {
		// settings might have changed, so save
		qtranxf_saveConfig();
		if(empty($messages)) {
			$messages[] = __('Options saved.', 'qtranslate');
		}
	}

	if($q_config['auto_update_mo']) {
		if(!is_dir(WP_LANG_DIR) || !$ll = @fopen(trailingslashit(WP_LANG_DIR).'qtranslate.test','a')) {
			$errors[] = sprintf(__('Could not write to "%s", Gettext Databases could not be downloaded!', 'qtranslate'), WP_LANG_DIR);
		} else {
			@fclose($ll);
			@unlink(trailingslashit(WP_LANG_DIR).'qtranslate.test');
		}
	}
}

function qtranxf_resetConfig(){
	global $qtranslate_options;

	if(!current_user_can('manage_options')) return;

	if(isset($_POST['qtranslate_reset_admin_notices'])){
		delete_option('qtranslate_admin_notices');
		qtranxf_add_message(__('Admin notices have been reset. You will see all applicable notices on admin pages and may dismiss them again.', 'qtranslate'));
	}

	if( !isset($_POST['qtranslate_reset']) || !isset($_POST['qtranslate_reset2']) )
		return;

	// reset all settings
	foreach($qtranslate_options['front'] as $ops){ foreach($ops as $nm => $def){ delete_option('qtranslate_'.$nm); } }
	foreach($qtranslate_options['admin'] as $ops){ foreach($ops as $nm => $def){ delete_option('qtranslate_'.$nm); } }
	foreach($qtranslate_options['default_value'] as $nm => $def){ delete_option('qtranslate_'.$nm); }
	foreach($qtranslate_options['languages'] as $nm => $opn){ delete_option($opn); }

	// internal private options not loaded by default
	delete_option('qtranslate_next_update_mo');
	delete_option('qtranslate_next_thanks');

	// obsolete options
	delete_option('qtranslate_custom_pages');
	delete_option('qtranslate_plugin_js_composer_off');
	delete_option('qtranslate_widget_css');
	delete_option('qtranslate_version');
	delete_option('qtranslate_disable_header_css');

	if(isset($_POST['qtranslate_reset3'])) {
		delete_option('qtranslate_term_name');
		if(isset($_POST['qtranslate_reset4'])){//not implemented yet
			delete_option('qtranslate_version_previous');
			//and delete translations in posts
		}
	}
	remove_filter('locale', 'qtranxf_localeForCurrentLanguage',99);
	qtranxf_reloadConfig();
	add_filter('locale', 'qtranxf_localeForCurrentLanguage',99);
}
add_action('qtranslate_saveConfig','qtranxf_resetConfig',20);

function qtranxf_update_option( $nm, $default_value=null ) {
	global $q_config;
	if( !isset($q_config[$nm]) || ( !is_integer($q_config[$nm]) && empty($q_config[$nm]) ) ){
		delete_option('qtranslate_'.$nm);
		return;
	}
	if(!is_null($default_value)){
		if(is_string($default_value)){
			if(function_exists($default_value)){
				$default_value = call_user_func($default_value);
			}elseif(is_array($q_config[$nm])){
				$default_value = preg_split('/[\s,]+/',$default_value,null,PREG_SPLIT_NO_EMPTY);
			}
		}
		if( $default_value===$q_config[$nm] ){
			delete_option('qtranslate_'.$nm);
			return;
		}
	}
	update_option('qtranslate_'.$nm, $q_config[$nm]);
}

function qtranxf_update_option_bool( $nm, $default_value=null ) {
	global $q_config, $qtranslate_options;
	if( !isset($q_config[$nm]) ){
		delete_option('qtranslate_'.$nm);
		return;
	}
	if(is_null($default_value)){
		if(isset($qtranslate_options['default_value'][$nm])){
			$default_value = $qtranslate_options['default_value'][$nm];
		}elseif(isset($qtranslate_options['front']['bool'][$nm])){
			$default_value = $qtranslate_options['front']['bool'][$nm];
		}
	}
	if( !is_null($default_value) && $default_value === $q_config[$nm] ){
		delete_option('qtranslate_'.$nm);
	}else{
		update_option('qtranslate_'.$nm, $q_config[$nm]?'1':'0');
	}
}

/**
 * saves entire configuration
 */
function qtranxf_saveConfig() {
	global $q_config, $qtranslate_options;

	qtranxf_update_option('default_language');
	qtranxf_update_option('enabled_languages');

	foreach($qtranslate_options['front']['int'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}

	foreach($qtranslate_options['front']['bool'] as $nm => $def){
		qtranxf_update_option_bool($nm,$def);
	}
	qtranxf_update_option_bool('qtrans_compatibility');
	qtranxf_update_option_bool('disable_client_cookies');

	foreach($qtranslate_options['front']['str'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}

	foreach($qtranslate_options['front']['text'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}

	foreach($qtranslate_options['front']['array'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}
	qtranxf_update_option('domains');

	update_option('qtranslate_ignore_file_types', implode(',',$q_config['ignore_file_types']));

	qtranxf_update_option('flag_location',qtranxf_flag_location_default());

	//if($q_config['filter_options_mode'] == QTX_FILTER_OPTIONS_LIST)
	qtranxf_update_option('filter_options',explode(' ',QTX_FILTER_OPTIONS_DEFAULT));

	//$qtranslate_options['languages'] are updated in a special way: look for _GET['edit'], $_GET['delete'], $_GET['enable'], $_GET['disable']

	qtranxf_update_option('term_name');//uniquely special case


	//save admin options

	foreach($qtranslate_options['admin']['int'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}

	foreach($qtranslate_options['admin']['bool'] as $nm => $def){
		qtranxf_update_option_bool($nm,$def);
	}

	foreach($qtranslate_options['admin']['str'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}

	foreach($qtranslate_options['admin']['text'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}

	foreach($qtranslate_options['admin']['array'] as $nm => $def){
		qtranxf_update_option($nm,$def);
	}

	do_action('qtranslate_saveConfig');
}

function qtranxf_reloadConfig() {
	global $q_config;
	$url_info = isset($q_config['url_info']) ? $q_config['url_info'] : null;
	//qtranxf_dbg_log('qtranxf_reloadConfig: $url_info: ',$url_info);
	qtranxf_del_conf_filters();
	qtranxf_loadConfig();
	qtranxf_admin_loadConfig();
	if($url_info){
		$q_config['url_info'] = $url_info;
		if(isset($q_config['url_info']['language'])){
			$q_config['language'] = $q_config['url_info']['language'];
		}
		if(!qtranxf_isEnabled($q_config['language'])){
			$q_config['language'] = $q_config['default_language'];
		}
		//qtranxf_dbg_log('qtranxf_reloadConfig: $q_config[language]: ',$q_config['language']);
	}
	qtranxf_load_option_qtrans_compatibility();
}

function qtranxf_updateSetting($var, $type = QTX_STRING, $def = null) {
	global $q_config, $qtranslate_options;
	if(!isset($_POST['submit'])) return false;
	if(!isset($_POST[$var]) && $type != QTX_BOOLEAN) return false;

	if(is_null($def) && isset($qtranslate_options['default_value'][$var])){
		$def = $qtranslate_options['default_value'][$var];
	}
	if(is_string($def) && function_exists($def)){
		$def = call_user_func($def);
	}
	switch($type) {
		case QTX_URL:
		case QTX_LANGUAGE:
		case QTX_STRING:
			$val = sanitize_text_field($_POST[$var]);
			if($type == QTX_URL) $val = trailingslashit($val);
			else if($type == QTX_LANGUAGE && !qtranxf_isEnabled($val)) return false;
			if(isset($q_config[$var])){
				if($q_config[$var] === $val) return false;
			}elseif(!is_null($def)){
				if(empty($val) || $def === $val) return false;
			}
			if(empty($val) && $def) $val = $def;
			$q_config[$var] = $val;
			qtranxf_update_option($var, $def);
			return true;
		case QTX_TEXT:
			$val = $_POST[$var];
			//standardize multi-line string
			$lns = preg_split('/\r?\n\r?/',$val);
			foreach($lns as $key => $ln){
				$lns[$key] = sanitize_text_field($ln);
			}
			$val = implode(PHP_EOL,$lns);
			//qtranxf_dbg_log('qtranxf_updateSetting:QTX_TEXT: $_POST[$var]:'.PHP_EOL, $_POST[$var]);
			//qtranxf_dbg_log('qtranxf_updateSetting:QTX_TEXT: $val:'.PHP_EOL, $val);
			if(isset($q_config[$var])){
				if($q_config[$var] === $val) return false;
			}elseif(!is_null($def)){
				if(empty($val) || $def === $val) return false;
			}
			if(empty($val) && $def) $val = $def;
			$q_config[$var] = $val;
			qtranxf_update_option($var, $def);
			return true;
		case QTX_ARRAY:
			$val = $_POST[$var];
			if(!is_array($_POST[$var])){
				$val = sanitize_text_field($val);
				$val = preg_split('/[\s,]+/',$val,null,PREG_SPLIT_NO_EMPTY);
			}
			if(empty($val) && !is_null($def)){
				if(is_string($def)){
					$val = preg_split('/[\s,]+/',$def,null,PREG_SPLIT_NO_EMPTY);
				}else if(is_array($def)){
					$val = $def;
				}
			}
			if( isset($q_config[$var]) && qtranxf_array_compare($q_config[$var],$val) ) return false;
			$q_config[$var] = $val;
			qtranxf_update_option($var, $def);
			return true;
		case QTX_BOOLEAN:
			if( isset($_POST[$var]) && $_POST[$var]==1 ) {
				if($q_config[$var]) return false;
				$q_config[$var] = true;
			} else {
				if(!$q_config[$var]) return false;
				$q_config[$var] = false;
			}
			qtranxf_update_option_bool($var, $def);
			return true;
		case QTX_INTEGER:
			$val = sanitize_text_field($_POST[$var]);
			$val = intval($val);
			if($q_config[$var] == $val) return false;
			$q_config[$var] = $val;
			qtranxf_update_option($var, $def);
			return true;
	}
	return false;
}

/**
 * Updates 'admin_config' and 'front_config' from *.json files listed in option 'config_files', and option 'custom_i18n_config'.
 * @since 3.3.1
 */
function qtranxf_update_i18n_config(){
	global $q_config;
	if(!isset($q_config['config_files'])){
		global $qtranslate_options;
		qtranxf_admin_set_default_options($qtranslate_options);
		qtranxf_load_option_array('config_files', $qtranslate_options['admin']['array']['config_files']);
		qtranxf_load_option_array('custom_i18n_config', $qtranslate_options['admin']['array']['custom_i18n_config']);
	}
	$json_files = $q_config['config_files'];
	$custom_i18n_config = $q_config['custom_i18n_config'];
	$cfg = qtranxf_load_config_all($json_files,$custom_i18n_config);
	if($q_config['admin_config'] !== $cfg['admin-config']){
		$q_config['admin_config'] = $cfg['admin-config'];
		qtranxf_update_option('admin_config');
	}
	if($q_config['front_config'] !== $cfg['front-config']){
		$q_config['front_config'] = $cfg['front-config'];
		qtranxf_update_option('front_config');
	}
}

function qtranxf_updateSettingFlagLocation($nm) {
	global $q_config;
	if(!isset($_POST['submit'])) return false;
	if(!isset($_POST[$nm])) return false;
	$flag_location=untrailingslashit(sanitize_text_field($_POST[$nm]));
	if(empty($flag_location)) $flag_location = qtranxf_flag_location_default();
	$flag_location = trailingslashit($flag_location);
	if(!file_exists(trailingslashit(WP_CONTENT_DIR).$flag_location))
		return null;
	if($flag_location != $q_config[$nm]){
		$q_config[$nm]=$flag_location;
		if($flag_location == qtranxf_flag_location_default())
			delete_option('qtranslate_'.$nm);
		else
			update_option( 'qtranslate_'.$nm, $flag_location );
	}
	return true;
}

function qtranxf_updateSettingIgnoreFileTypes($nm) {
	global $q_config;
	if(!isset($_POST['submit'])) return false;
	if(!isset($_POST[$nm])) return false;
	$posted=preg_split('/[\s,]+/',strtolower(sanitize_text_field($_POST[$nm])),null,PREG_SPLIT_NO_EMPTY);
	$val=explode(',',QTX_IGNORE_FILE_TYPES);
	if(is_array($posted)){
		foreach($posted as $v){
			if(empty($v)) continue;
			if(in_array($v,$val)) continue;
			$val[]=$v;
		}
	}
	if( qtranxf_array_compare($q_config[$nm],$val) ) return false;
	$q_config[$nm] = $val;
	update_option('qtranslate_'.$nm, implode(',',$val));
	return true;
}

function qtranxf_parse_post_type_excluded() {
	if(!isset($_POST['submit'])) return false;
	if(!isset($_POST['post_types_all'])) return false;
	if(!is_array($_POST['post_types_all'])) return false;
	$post_type_excluded = array();
	foreach($_POST['post_types_all'] as $post_type => $v){
		if(isset($_POST['post_types'][$post_type])) continue;
		$post_type_excluded[] = $post_type;
	}
	unset($_POST['post_types']);
	unset($_POST['post_types_all']);
	$_POST['post_type_excluded'] = $post_type_excluded;
	//qtranxf_dbg_log('qtranxf_parse_post_type_excluded: $_POST[post_type_excluded]: ',$_POST['post_type_excluded']);
}

function qtranxf_updateSettings(){
	global $qtranslate_options, $q_config;

	$errors = &$q_config['url_info']['errors'];

	// update front settings

	/**
	 * Opportunity to prepare special custom settings update on sub-plugins
	 */
	do_action('qtranslate_update_settings_pre');

	// special cases handling for front options

	qtranxf_updateSetting('default_language', QTX_LANGUAGE);
	//enabled_languages are not changed at this place

	qtranxf_updateSettingFlagLocation('flag_location');
	qtranxf_updateSettingIgnoreFileTypes('ignore_file_types');

	$_POST['language_name_case'] = isset($_POST['camel_case']) ? '0' : '1';

	// special cases handling for front options - end

	foreach($qtranslate_options['front']['int'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_INTEGER, $def);
	}

	foreach($qtranslate_options['front']['bool'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_BOOLEAN, $def);
	}
	qtranxf_updateSetting('qtrans_compatibility', QTX_BOOLEAN);

	foreach($qtranslate_options['front']['str'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_STRING, $def);
	}

	foreach($qtranslate_options['front']['text'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_TEXT, $def);
	}

	foreach($qtranslate_options['front']['array'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_ARRAY, $def);
	}
	qtranxf_updateSetting('filter_options', QTX_ARRAY);

	switch($q_config['url_mode']){
		case QTX_URL_DOMAIN:
		case QTX_URL_DOMAINS: $q_config['disable_client_cookies'] = true; break;
		case QTX_URL_QUERY:
		case QTX_URL_PATH:
		default: qtranxf_updateSetting('disable_client_cookies', QTX_BOOLEAN); break;
	}

	$domains = isset($q_config['domains']) ? $q_config['domains'] : array();
	foreach($q_config['enabled_languages'] as $lang){
		$id='language_domain_'.$lang;
		if(!isset($_POST[$id])) continue;
		$domain = preg_replace('#^/*#','',untrailingslashit(trim($_POST[$id])));
		//qtranxf_dbg_log('qtranxf_updateSettings: domain['.$lang.']: ',$domain);
		$domains[$lang] = $domain;
	}
	if( !empty($domains) && (!isset($q_config['domains']) || !qtranxf_array_compare($q_config['domains'],$domains)) ){
		$q_config['domains'] = $domains;
		qtranxf_update_option('domains');
	}

	// update admin settings

	//special cases handling for admin options

	if(isset($_POST['json_config_files'])){
		//verify that files are loadable
		$json_config_files_post = sanitize_text_field(stripslashes($_POST['json_config_files']));
		$json_files = preg_split('/[\s,]+/',$json_config_files_post,null,PREG_SPLIT_NO_EMPTY);
		if(empty($json_files)){
			$_POST['config_files'] = array();
			unset($_POST['json_config_files']);
		}else{
			$nerr = isset($q_config['url_info']['errors']) ? count($q_config['url_info']['errors']) : 0;
			$cfg = qtranxf_load_config_files($json_files);
			if(!empty($q_config['url_info']['errors']) && $nerr != count($q_config['url_info']['errors'])){//new errors occurred
				$_POST['json_config_files'] = implode(PHP_EOL,$json_files);
				remove_action('admin_notices', 'qtranxf_admin_notices_errors');
				if($json_files == $q_config['config_files']){
					//option is not changed, apparently something happened to files, then make the error permanent
					update_option('qtranslate_config_errors',array_slice($q_config['url_info']['errors'],$nerr));
				}
			}else{
				$_POST['config_files'] = implode(PHP_EOL,$json_files);
				unset($_POST['json_config_files']);
				delete_option('qtranslate_config_errors');
			}
		}
	}

	if(isset($_POST['json_custom_i18n_config'])){
		//verify that JSON string can be parsed
		$cfg_json = sanitize_text_field(stripslashes($_POST['json_custom_i18n_config']));
		if(empty($cfg_json)){
			$_POST['custom_i18n_config'] = array();
		}else{
			$cfg = json_decode($cfg_json,true);
			if($cfg){
				$_POST['custom_i18n_config'] = $cfg;
				unset($_POST['json_custom_i18n_config']);
			}else{
				$_POST['json_custom_i18n_config'] = stripslashes($_POST['json_custom_i18n_config']);
				$errors[] = sprintf(__('Cannot parse JSON code in the field "%s".', 'qtranslate'), __('Custom Configuration', 'qtranslate'));
			}
		}
	}

	if($_POST['highlight_mode'] != QTX_HIGHLIGHT_MODE_CUSTOM_CSS){
		$_POST['highlight_mode_custom_css'] = '';
	}
	if($_POST['lsb_style'] != $q_config['lsb_style']){
		$_POST['lsb_style_wrap_class'] = '';
		$_POST['lsb_style_active_class'] = '';
	}

	qtranxf_parse_post_type_excluded();

	//special cases handling for admin options - end

	do_action('qtranslate_update_settings_admin');

	foreach($qtranslate_options['admin']['int'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_INTEGER, $def);
	}

	foreach($qtranslate_options['admin']['bool'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_BOOLEAN, $def);
	}

	foreach($qtranslate_options['admin']['str'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_STRING, $def);
	}

	foreach($qtranslate_options['admin']['text'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_TEXT, $def);
	}

	foreach($qtranslate_options['admin']['array'] as $nm => $def){
		qtranxf_updateSetting($nm, QTX_ARRAY, $def);
	}

	if(empty($_POST['json_config_files']))//only update if config files parsed successfully
		qtranxf_update_i18n_config();

	$q_config['i18n-cache'] = array();//clear i18n-config cache

	/**
	 * Opportunity to update special custom settings on sub-plugins
	 */
	do_action('qtranslate_update_settings');
}

function qtranxf_executeOnUpdate() {
	global $q_config;
	$messages = &$q_config['url_info']['messages'];

	if ( isset( $_POST['update_mo_now'] ) && $_POST['update_mo_now'] == '1' ) {
		$result = qtranxf_updateGettextDatabases( true );
		if( $result === 0 ) {
			$messages[] = __( 'Gettext databases updated.', 'qtranslate' );
		}
	}

	// ==== import/export msg was here

	if(isset($_POST['convert_database'])){
		$msg = qtranxf_convert_database($_POST['convert_database']);
		if($msg) $messages[] = $msg;
	}
}

//function qtranxf_updateLanguage() {
//}

/**
 * Allow 3rd-party to include additional code here
 */
do_action('qtranslate_admin_options_update.php');
