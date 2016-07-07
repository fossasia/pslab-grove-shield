<?php
if ( !defined( 'ABSPATH' ) ) exit;

function qtranxf_version_int() {
	$ver = str_replace('.','',QTX_VERSION);
	while(strlen($ver) < 5) $ver.='0';
	return intval($ver);
}

/**
 * Save language properties from configuration $cfg to database
 * @since 3.3
 */
function qtranxf_save_languages($cfg){
	global $qtranslate_options;
	foreach($qtranslate_options['languages'] as $nm => $opn){
		if(is_array($cfg[$nm])){
			foreach($cfg[$nm] as $k => $v){
				if(empty($v)) unset($cfg[$nm][$k]);
			}
		}
		if(empty($cfg[$nm])) delete_option($opn);
		else update_option($opn,$cfg[$nm]);
	}
	return $cfg;
}

/**
 * since 3.2.9.2
 */
function qtranxf_default_enabled_languages(){
	//$locale = defined('WPLANG') ? WPLANG : get_option('WPLANG','en_US');
	$locale = get_locale();
	if(!$locale) $locale = 'en_US';
	$lang = null;
	$locales = qtranxf_default_locale();
	foreach($locales as $ln => $lo){
		if($lo != $locale) continue;
		$lang = $ln;
		break;
	}
	if(!$lang) $lang = substr($locale,0,2);
	if(!qtranxf_language_predefined($lang)){
		$langs = array();
		$langs['language_name'][$lang] = 'Unknown';
		$langs['flag'][$lang] = 'us.png';
		$langs['locale'][$lang] = $locale;
		$langs['date_format'][$lang] = '%A %B %e%q, %Y';
		$langs['time_format'][$lang] = '%I:%M %p';
		$langs['not_available'][$lang] = 'Sorry, this entry is only available in %LANG:, : and %.';
		qtranxf_save_languages($langs);
	}
	//qtranxf_dbg_log('qtranxf_default_enabled_languages: $lang='.$lang.' $locale:',$locale);
	return array($lang, $lang != 'en' ? 'en' : 'de');
	//return array( 'de', 'en', 'zh' );
}

/**
 * since 3.2.9.2
 */
function qtranxf_default_default_language(){
	global $q_config;
	$enabled_languages = qtranxf_default_enabled_languages();
	$default_language = $enabled_languages[0];
	update_option('qtranslate_enabled_languages',$enabled_languages);
	update_option('qtranslate_default_language',$default_language);
	$q_config['language'] = $q_config['default_language'] = $default_language;
	$q_config['enabled_languages'] = $enabled_languages;
	//qtranxf_updateGettextDatabases(true);
	return $default_language;
}

/**
 * @since 3.3.2
 */
function qtranxf_load_config_files($json_files){
	$content_dir = null;
	$qtransx_dir = null;
	foreach($json_files as $k => $fnm){
		//$fnm = trim($v,'/\\');
		if(file_exists($fnm)) continue;
		$ffnm = null;
		if($fnm[0] == '.' && $fnm[1] == '/'){
			if(!$qtransx_dir) $qtransx_dir = QTRANSLATE_DIR;
			$ffnm = $qtransx_dir.substr($fnm,1);
		}
		if(!file_exists($ffnm)){
			if(!$content_dir) $content_dir = trailingslashit(WP_CONTENT_DIR);
			$ffnm = $content_dir.$fnm;
		}
		if(file_exists($ffnm)){
			$json_files[$k] = $ffnm;
		}else{
			qtranxf_error_log(sprintf(__('Could not find file "%s" listed in option "%s".', 'qtranslate'), '<strong>'.$fnm.'</strong>', '<a href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">'.__('Configuration Files', 'qtranslate').'</a>') . ' ' . __('Please, either put file in place or update the option.', 'qtranslate') . ' ' . sprintf(__('Once the problem is fixed, re-save the configuration by pressing button "%s" on plugin %ssettings page%s.', 'qtranslate'), __('Save Changes', 'qtranslate'), '<a href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">', '</a>'));
			unset($json_files[$k]);
		}
	}

	$cfg_all = array();
	foreach($json_files as $fnm){
		$cfg_json=file_get_contents($fnm);
		//$cfg_json=php_strip_whitespace($fnm);
		if($cfg_json){
			$cfg=json_decode($cfg_json,true);
			if(!empty($cfg) && is_array($cfg)){
				$cfg_all = qtranxf_merge_config($cfg_all,$cfg);
			}else{
				qtranxf_error_log(sprintf(__('Could not parse %s file "%s" listed in option "%s".', 'qtranslate'), 'JSON', '<strong>'.$fnm.'</strong>', '<a href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">'.__('Configuration Files', 'qtranslate').'</a>') . ' ' . __('Please, correct the syntax error in the file.', 'qtranslate') . ' ' . sprintf(__('Once the problem is fixed, re-save the configuration by pressing button "%s" on plugin %ssettings page%s.', 'qtranslate'), __('Save Changes', 'qtranslate'), '<a href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">', '</a>'));
			}
		}else{
			qtranxf_error_log(sprintf(__('Could not load file "%s" listed in option "%s".', 'qtranslate'), '<strong>'.$fnm.'</strong>', '<a href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">'.__('Configuration Files', 'qtranslate').'</a>') . ' ' . __('Please, make sure the file is accessible and readable.', 'qtranslate') . ' ' . sprintf(__('Once the problem is fixed, re-save the configuration by pressing button "%s" on plugin %ssettings page%s.', 'qtranslate'), __('Save Changes', 'qtranslate'), '<a href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">', '</a>'));
		}
	}
	if(!isset($cfg_all['admin-config'])) $cfg_all['admin-config'] = array();
	if(!isset($cfg_all['front-config'])) $cfg_all['front-config'] = array();
	return $cfg_all;
}

/**
 * @since 3.4
 */
function qtranxf_get_option_config_files(){
	$config_files_def = array('./i18n-config.json');
	$config_files = get_option('qtranslate_config_files', $config_files_def);
	if(!is_array($config_files)){
		$config_files = $config_files_def;
		delete_option('qtranslate_config_files');
	}
	//qtranxf_dbg_log('qtranxf_get_option_config_files: $config_files: ', $config_files);
	return $config_files;
}

/**
 * @since 3.4
 */
function qtranxf_set_field_jquery(&$f){
	if(isset($f['jquery'])) return false;
	if(isset($f['class'])){
		$jq = '.'.$f['class'];
		unset($f['class']);
	}else{
		$jq = '';
	}
	if(isset($f['tag'])){
		$jq = $f['tag'].$jq;
		unset($f['tag']);
	}
	if(isset($f['name'])){
		$jq .= '[name="'.$f['name'].'"]';
		unset($f['name']);
	}
	if(empty($jq)) return false;
	$f['jquery'] = $jq;
	return true;
}

/**
 * @since 3.4
 */
function qtranxf_standardize_config_fields($fields){
	foreach($fields as $k => $f ){
		if(!is_array($f)) continue;
		if(isset($f['id'])){
			$id = $f['id']; unset($f['id']);
			$fields[$id] = $f;
			if($id !== $k) unset($fields[$k]);
		}else if(qtranxf_set_field_jquery($f)){
			$fields[$k] = $f;
		}
	}
	return $fields;
}

/**
 * @since 3.4
 */
function qtranxf_standardize_config_anchor( &$anchor ){
	if(is_string($anchor)){
		switch($anchor){
			case '':
			case 'post':
			case 'postexcerpt': return null; //do not allow these, to offset obsolete configurations
			default: $id = $anchor; break;
		}
		$anchor = array();
		$anchor['where'] = 'before';
	}else if(isset($anchor['id'])){
		$id = $anchor['id'];
		unset($anchor['id']);
	}else{
		return false;
	}
	return $id;
}

/**
 * @since 3.4
 */
function qtranxf_standardize_front_config($cfg_front){
	//remove filters with empty priorities
	foreach($cfg_front as $k => $cfg){
		if(!isset($cfg['filters'])) continue;
		if(!empty($cfg['filters']['text'])){
			foreach($cfg['filters']['text'] as $nm => $pr){
				if($pr === '') unset($cfg_front[$k]['filters']['text'][$nm]);
			}
		}
		if(!empty($cfg['filters']['url'])){
			foreach($cfg['filters']['url'] as $nm => $pr){
				if($pr === '') unset($cfg_front[$k]['filters']['url'][$nm]);
			}
		}
		if(!empty($cfg['filters']['term'])){
			foreach($cfg['filters']['term'] as $nm => $pr){
				if($pr === '') unset($cfg_front[$k]['filters']['term'][$nm]);
			}
		}
	}
	return $cfg_front;
}

/**
 * @since 3.4
 */
function qtranxf_standardize_admin_config($configs){
	foreach($configs as $k => $config ){
		if(!is_array($config)) continue;
		if($k === 'forms'){
			foreach($config as $form_id => $frm ){
				if(isset($frm['form']['id'])){
					$id = $frm['form']['id']; unset($frm['form']['id']);
					if(empty($frm['form'])) unset($frm['form']);
					$configs['forms'][$id] = $frm;
					if($id !== $form_id) unset($configs['forms'][$form_id]);
					$form_id = $id;
				}
				if(isset($frm['fields'])) $configs['forms'][$form_id]['fields'] = qtranxf_standardize_config_fields($frm['fields']);
			}
		}else if($k === 'anchors'){
			if(empty($config)){
				unset($configs['anchors']);
			}else{
				foreach($configs['anchors'] as $k => $anchor){
					$id = qtranxf_standardize_config_anchor($anchor);
					if(is_null($id)){
						unset($configs['anchors'][$k]);
					}else if(is_string($id)){
						$configs['anchors'][$id] = $anchor;
						if($id !== $k) unset($configs['anchors'][$k]);
					}
				}
				if(empty($configs['anchors'])) unset($configs['anchors']);
			}
		}else{
			$configs[$k] = qtranxf_standardize_admin_config($config);//recursive call
		}
	}
	return $configs;
}

/**
 * @since 3.4
 */
function qtranxf_standardize_i18n_config($configs){
	if(isset($configs['admin-config']))
		$configs['admin-config'] = qtranxf_standardize_admin_config($configs['admin-config']);
	if(isset($configs['front-config']))
		$configs['front-config'] = qtranxf_standardize_front_config($configs['front-config']);
	return $configs;
}

/**
 * @since 3.4
 */
function qtranxf_load_config_all($json_files, $custom_config){
	global $q_config;
	$nerr = isset($q_config['url_info']['errors']) ? count($q_config['url_info']['errors']) : 0;
	$cfg = qtranxf_load_config_files($json_files);
	$cfg = qtranxf_merge_config($cfg, $custom_config);
	$cfg = qtranxf_standardize_i18n_config($cfg);
	// store the errors permanently until an admin fixes them,
	// otherwise admin may not realise that not all configurations are loaded.
	if(!empty($q_config['url_info']['errors']) && $nerr != count($q_config['url_info']['errors'])){//new errors occurred
		$errs = array_slice($q_config['url_info']['errors'], $nerr);
		update_option('qtranslate_config_errors', $errs);
	}else{
		delete_option('qtranslate_config_errors');
	}
	return $cfg;
}

/**
 * @since 3.4
 */
function qtranxf_update_config_options($config_files, $changed = true){
	//qtranxf_dbg_log('qtranxf_update_config_options: $config_files: ', $config_files);
	if($changed){
		update_option('qtranslate_config_files',$config_files);
		qtranxf_update_option_admin_notices_id('config-files-changed');//notify admin
	}
	$custom_config = get_option('qtranslate_custom_i18n_config', array());
	$cfg = qtranxf_load_config_all($config_files, $custom_config);
	update_option('qtranslate_admin_config', $cfg['admin-config']);
	update_option('qtranslate_front_config', $cfg['front-config']);
}

/**
 * @since 3.4
 */
function qtranxf_search_config_files_theme($theme=null, $found=null){
	if(!$theme) $theme = wp_get_theme();
	else if(is_string($theme)) $theme = wp_get_theme($theme);
	if(!$found) $found = array();
	$fn = $theme->theme_root.'/'.$theme->stylesheet.'/i18n-config.json';
	if(file_exists($fn)) $found[] = $fn;
	else{
		$fn = WP_PLUGIN_DIR.'/'.qtranxf_plugin_dirname().'/i18n-config/themes/'.$theme->stylesheet.'/i18n-config.json';
		if(file_exists($fn)) $found[] = $fn;
	}
	$parent_theme = $theme->parent();
	if(!empty($parent_theme))
		return qtranxf_search_config_files_theme($parent_theme,$found);//recursive call
	return $found;
}

/**
 * @since 3.4
 */
function qtranxf_normalize_config_files($found){
	$nc = strlen(WP_CONTENT_DIR);
	$plugin_dir = WP_PLUGIN_DIR.'/'.qtranxf_plugin_dirname();
	$np = strlen($plugin_dir);
	foreach($found as $k => $fn){
		if(substr($fn,0,$np) === $plugin_dir){
			$found[$k] = '.'.substr($fn,$np);
		}else if(substr($fn,0,$nc) === WP_CONTENT_DIR){
			$found[$k] = substr($fn,$nc+1);
		}
	}
	return $found;
}

/**
 * @since 3.4
 */
function qtranxf_find_plugin_by_foder($fld,$plugins){
	foreach( $plugins as $plugin ){
		$dir = dirname($plugin);
		$bnm = basename($dir);
		if($fld == $bnm) return $plugin;
	}
}

/**
 * @since 3.4
 */
function qtranxf_search_config_files(){
	$found = qtranxf_search_config_files_theme();
	$plugins = wp_get_active_and_valid_plugins();
	$plugin_bnm = qtranxf_plugin_dirname();
	$plugin_dir = WP_PLUGIN_DIR.'/'.$plugin_bnm;
	//qtranxf_dbg_log('qtranxf_search_config_files: $plugin_dir: ', $plugin_dir);
	foreach( $plugins as $plugin ){
		$dir = dirname($plugin);
		$bnm = basename($dir);
		//qtranxf_dbg_log('$dir='.$dir.'; $bnm: ',$bnm);
		if(strpos($bnm,'qtranslate-x') === 0) continue;
		if($bnm == $plugin_bnm) continue;
		$fn = $dir.'/i18n-config.json';
		if(!file_exists($fn)){
			$fn = $plugin_dir.'/i18n-config/plugins/'.$bnm.'/i18n-config.json';
			if(!file_exists($fn)) continue;
			if(qtranxf_find_plugin_by_foder($bnm.'-qtranslate-x',$plugins)) continue;
		}
		$found[] = $fn;
	}
	return qtranxf_normalize_config_files($found);
}

/**
 * Inserts new entry at the second position, for now.
 * Later we may need to preserve order somehow.
 * @since 3.4
 */
function qtranxf_add_config_file($config_files, $fn){
	$a = array_slice($config_files,0,1);
	$a[] = $fn;
	foreach(array_slice($config_files,1) as $f){
		if(!is_string($f)) continue;
		$a[] = $f;
	}
	return $a;
}

/**
 * @since 3.4
 */
function qtranxf_add_config_files(&$config_files, $found){
	$changed = false;
	foreach($found as $fn){
		$i = array_search($fn,$config_files);
		if($i !== FALSE) continue;
		$config_files = qtranxf_add_config_file($config_files, $fn);
		$changed = true;
	}
	return $changed;
}

function qtranxf_del_config_files(&$config_files, $found){
	$changed = false;
	foreach($found as $fn){
		$i = array_search($fn,$config_files);
		if($i === FALSE) continue;
		unset($config_files[$i]);
		$changed = true;
	}
	return $changed;
}

/**
 * @since 3.4
 */
function qtranxf_update_config_files(){
	$config_files = qtranxf_get_option_config_files();
	$found = qtranxf_search_config_files();
	$changed = qtranxf_add_config_files($config_files, $found);
	//qtranxf_dbg_log('qtranxf_update_config_files: $config_files: ',$config_files);
	qtranxf_update_config_options($config_files,$changed);
}

function qtranxf_find_plugin_file($fp){
	$fp = '/' . $fp;
	$fn = WP_PLUGIN_DIR . $fp;
	while(!file_exists($fn)){
		$fn = WPMU_PLUGIN_DIR . $fp;
		if(file_exists($fn)) break;
		$fn = WP_CONTENT_DIR . '/plugins' . $fp;
		if(file_exists($fn)) break;
		$fn = WP_CONTENT_DIR . '/mu-plugins' . $fp;
		if(file_exists($fn)) break;
		return;
	}
	$found = array($fn);
	$found = qtranxf_normalize_config_files($found);
	return $found[0];
}

function qtranxf_on_switch_theme($new_name, $new_theme){
	$config_files = qtranxf_get_option_config_files();
	$changed = false;

	$old_theme_stylesheet = get_option( 'theme_switched');
	$found = qtranxf_search_config_files_theme($old_theme_stylesheet);
	$found = qtranxf_normalize_config_files($found);
	if(qtranxf_del_config_files($config_files, $found)) $changed = true;

	$found = qtranxf_search_config_files_theme($new_theme);
	$found = qtranxf_normalize_config_files($found);
	if(qtranxf_add_config_files($config_files, $found)) $changed = true;

	if(!$changed) return;
	qtranxf_update_config_options($config_files);
}
add_action('switch_theme', 'qtranxf_on_switch_theme', 10, 2);

function qtranxf_find_plugin_config_files(&$fn_bnm, &$fn_qtx, $bnm){
	$plugins = wp_get_active_and_valid_plugins();
	$fn_bnm = null;
	if(!qtranxf_find_plugin_by_foder($bnm.'-qtranslate-x',$plugins)){
		$fn_bnm = qtranxf_find_plugin_file($bnm . '/i18n-config.json');
		while(!$fn_bnm){
			$fn_bnm = qtranxf_plugin_dirname().'/i18n-config/plugins/'.$bnm.'/i18n-config.json';
			$fn_bnm = qtranxf_find_plugin_file($fn_bnm);
			if($fn_bnm) break;
			$fn_bnm = qtranxf_plugin_dirname().'/i18n-config/themes/'.$bnm.'/i18n-config.json';
			$fn_bnm = qtranxf_find_plugin_file($fn_bnm);
			break;
		}
	}
	$fn_qtx = null;
	while(qtranxf_endsWith($bnm,'-qtranslate-x')){
		$bnm_qtx = substr($bnm,0,-13);
		$plugins = wp_get_active_and_valid_plugins();
		$fn_qtx = qtranxf_plugin_dirname().'/i18n-config/plugins/'.$bnm_qtx.'/i18n-config.json';
		$fn_qtx = qtranxf_find_plugin_file($fn_qtx);
		if($fn_qtx) break;
		$fn_qtx = qtranxf_plugin_dirname().'/i18n-config/themes/'.$bnm_qtx.'/i18n-config.json';
		$fn_qtx = qtranxf_find_plugin_file($fn_qtx);
		break;
	}
	return $fn_bnm || $fn_qtx;
}

function qtranxf_adjust_config_files($fn_add, $fn_del){
	$config_files = qtranxf_get_option_config_files();
	if($fn_add){
		if(in_array($fn_add,$config_files)) $fn_add = false;
		else $config_files = qtranxf_add_config_file($config_files, $fn_add);
	}
	if($fn_del){
		$i = array_search($fn_del,$config_files);
		if($i === FALSE) $fn_del = false;
		else unset($config_files[$i]);
	}
	if(!$fn_add && !$fn_del) return;
	qtranxf_update_config_options($config_files);
}

function qtranxf_on_activate_plugin($plugin, $network_wide = false){
	//qtranxf_dbg_log('qtranxf_on_activate_plugin: $plugin: ',$plugin);
	$bnm = dirname($plugin);
	$qtx = qtranxf_plugin_dirname();
	if($bnm == $qtx) return;
	$fn_add = null; $fn_del = null;
	if(!qtranxf_find_plugin_config_files($fn_add, $fn_del, $bnm)) return;
	qtranxf_adjust_config_files($fn_add, $fn_del);
}
add_action( 'activate_plugin', 'qtranxf_on_activate_plugin' );

function qtranxf_on_deactivate_plugin($plugin, $network_deactivating = false){
	//qtranxf_dbg_log('qtranxf_on_deactivate_plugin: $plugin: ',$plugin);
	$bnm = dirname($plugin);
	$qtx = qtranxf_plugin_dirname();
	if($bnm == $qtx){
		if($bnm == 'qtranslate-x'){//not testing version
			$ver_cur = qtranxf_version_int();
			update_option('qtranslate_version_previous',$ver_cur);
		}
		return;
	}
	$fn_add = null; $fn_del = null;
	if(!qtranxf_find_plugin_config_files($fn_del, $fn_add, $bnm)) return;
	qtranxf_adjust_config_files($fn_add, $fn_del);
}
add_action( 'deactivate_plugin', 'qtranxf_on_deactivate_plugin' );

function qtranxf_clear_debug_log(){
	//clear file debug-qtranslate.log
	$f=WP_CONTENT_DIR.'/debug-qtranslate.log';
	if(file_exists($f)){
		if(WP_DEBUG){
			$fh = fopen($f, "a+");
			ftruncate($fh,0);
			fclose($fh);
		}else{
			unlink($f);
		}
	}
}


function qtranxf_activation_hook(){
	qtranxf_clear_debug_log();
	//qtranxf_dbg_log('qtranxf_activation_hook: ', __FILE__);
	if(version_compare(PHP_VERSION, '5.2.0') < 0){
		// Deactivate ourself
		$plugin_dir = qtranxf_plugin_dirname();
		$lang_dir = $plugin_dir.'/lang';
		load_plugin_textdomain('qtranslate', false, $lang_dir);
		$msg = sprintf(__('Plugin %s requires PHP version %s at least. This server instance runs PHP version %s. A PHP version %s or higher is recommended. The plugin has not been activated.', 'qtranslate'), '<a href="https://wordpress.org/plugins/qtranslate-x/" style="color:blue" target="_blank">qTranslate&#8209;X</a>', '5.2.0', PHP_VERSION, '5.4.0');
		deactivate_plugins($plugin_dir.'/qtranslate.php');
		wp_die( $msg );
	}

	require_once(QTRANSLATE_DIR.'/admin/qtx_admin_options.php');
	require_once(QTRANSLATE_DIR.'/admin/qtx_import_export.php');

	// Check if other qTranslate forks are activated.
	if ( is_plugin_active( 'mqtranslate/mqtranslate.php' ) )
		qtranxf_admin_notice_deactivate_plugin('mqTranslate', 'mqtranslate/mqtranslate.php');

	if ( is_plugin_active( 'qtranslate/qtranslate.php' ) ){
		update_option('qtranslate_qtrans_compatibility', '1');
		qtranxf_admin_notice_deactivate_plugin('qTranslate', 'qtranslate/qtranslate.php');
	}

	if ( is_plugin_active( 'qtranslate-xp/ppqtranslate.php' ) )
		qtranxf_admin_notice_deactivate_plugin('qTranslate Plus', 'qtranslate-xp/ppqtranslate.php');

	if ( is_plugin_active( 'ztranslate/ztranslate.php' ) )
		qtranxf_admin_notice_deactivate_plugin('zTranslate', 'ztranslate/ztranslate.php');

	$ts = time();
	$next_thanks = get_option('qtranslate_next_thanks');
	$check_qtranslate_forks = $next_thanks === false;
	if($next_thanks !== false && $next_thanks < $ts+7*24*60*60){
		$next_thanks = $ts + rand(10,20)*24*60*60;
		update_option('qtranslate_next_thanks', $next_thanks);
	}
	$messages = qtranxf_update_admin_notice('next_thanks');

	$default_language = get_option('qtranslate_default_language');
	$ver_cur = qtranxf_version_int();
	$first_install = $default_language===false;
	if($first_install){
		qtranxf_default_default_language();
		update_option('qtranslate_version_previous', $ver_cur);
		$check_qtranslate_forks = true;
		if(isset($messages['initial-install'])){
			$messages = qtranxf_update_option_admin_notices($messages,'initial-install');
		}
	}else{
		$ver_prv = get_option('qtranslate_version_previous');
		if(!$ver_prv) update_option('qtranslate_version_previous', 29000);

		if(!isset($messages['initial-install'])){
			$messages = qtranxf_update_option_admin_notices($messages,'initial-install');
		}
	}

	$vers = get_option('qtranslate_versions', array());
	if(!isset($vers[$ver_cur])) $vers[$ver_cur] = $ts;
	$vers['l'] = $ts;
	update_option('qtranslate_versions',$vers);

	// @since 3.3.7
	if($check_qtranslate_forks){ // possibly first install after a fork
		if( get_option('qtranslate_qtrans_compatibility') === false ){
			//to prevent most of fatal errors on upgrade
			if ( file_exists(WP_PLUGIN_DIR.'/qtranslate/qtranslate.php')
				|| file_exists(WP_PLUGIN_DIR.'/mqtranslate/mqtranslate.php')
				|| file_exists(WP_PLUGIN_DIR.'/ztranslate/ztranslate.php')
				|| file_exists(WP_PLUGIN_DIR.'/qtranslate-xp/ppqtranslate.php')
			) update_option('qtranslate_qtrans_compatibility', '1');
		}
	}

	/**
	 * A chance to execute activation actions specifically for this plugin.
	 * @since 3.4
	*/
	do_action('qtranslate_activation_hook');

	qtranxf_update_config_files();
}

/**
 * @since 3.4
*/
function qtranxf_deactivation_hook(){
	//qtranxf_dbg_log('qtranxf_deactivation_hook: ', __FILE__);
	$vers = get_option('qtranslate_versions', array());
	$ts = time();
	$t=0;
	if(isset($vers['l'])){ $t=$ts-$vers['l']; }
	if($t > 0){
		if(!isset($vers['t'])) $vers['t'] = 0;
		$vers['t'] += $t;
	}
	$vers['p'] = $ts;
	update_option('qtranslate_versions',$vers);

	/**
	 * A chance to execute deactivation actions specifically for this plugin.
	 */
	do_action('qtranslate_deactivation_hook');
}

function qtranxf_admin_notice_config_files_changed(){
	$messages = get_option('qtranslate_admin_notices');
	if(!isset($messages['config-files-changed'])) return;
	qtranxf_admin_notice_dismiss_script();
	$url = admin_url('options-general.php?page=qtranslate-x#integration');
	echo '<div class="update-nag notice is-dismissible" id="qtranxs-config-files-changed"><p>';
	printf(__('Option "%s" for plugin %s has been auto-adjusted after recent changes in the site configuration. It might be a good idea to %sreview the changes%s in the list of configuration files.', 'qtranslate'), '<a href="'.$url.'">'.__('Configuration Files', 'qtranslate').'</a>', '<a href="https://wordpress.org/plugins/qtranslate-x/" style="color:blue" target="_blank">qTranslate&#8209;X</a>', '<a href="'.$url.'">', '</a>');
	echo '<br/></p><p>';
	echo '<a class="button" href="'.$url.'">';
	printf(__('Review Option "%s"', 'qtranslate'), __('Configuration Files', 'qtranslate'));
	echo '</a>&nbsp;&nbsp;&nbsp;<a class="button" href="https://qtranslatexteam.wordpress.com/integration/" target="_blank">';
	echo __('Read Integration Guide', 'qtranslate');
	echo '</a>&nbsp;&nbsp;&nbsp;<a class="button" href="javascript:qtranxj_dismiss_admin_notice(\'config-files-changed\');">'.__('I have already done it, dismiss this message.', 'qtranslate');
	echo '</a></p></div>';
}
add_action('admin_notices', 'qtranxf_admin_notice_config_files_changed');

function qtranxf_admin_notice_first_install(){
	$messages = get_option('qtranslate_admin_notices');
	if(isset($messages['initial-install'])) return;
	qtranxf_admin_notice_dismiss_script();
	echo '<div class="updated notice is-dismissible" id="qtranxs-initial-install"><p style="font-size: larger;">';// text-align: center;
	printf(__('Are you new to plugin %s?', 'qtranslate'), '<a href="https://wordpress.org/plugins/qtranslate-x/" style="color:blue" target="_blank">qTranslate&#8209;X</a>');
	echo '<br/>';
	echo '</p><p><a class="button" href="https://qtranslatexteam.wordpress.com/startup-guide/" target="_blank">';
	echo __('Read Startup Guide', 'qtranslate');
	echo '</a>&nbsp;&nbsp;&nbsp;<a class="button" href="javascript:qtranxj_dismiss_admin_notice(\'initial-install\');">'.__('I have already done it, dismiss this message.', 'qtranslate');
	echo '</a></p></div>';
}
add_action('admin_notices', 'qtranxf_admin_notice_first_install');

function qtranxf_admin_notice_deactivate_plugin($nm, $plugin){
	deactivate_plugins($plugin,true);
	$d=dirname($plugin);
	$link='<a href="https://wordpress.org/plugins/'.$d.'/" target="_blank">'.$nm.'</a>';
	$qtxnm='qTranslate&#8209;X';
	$qtxlink='<a href="https://wordpress.org/plugins/qtranslate-x/" target="_blank">'.$qtxnm.'</a>';
	$imported = false;
	$f='qtranxf_migrate_import_'.str_replace('-','_',dirname($plugin));
	if(function_exists($f)){
		global $wpdb;
		$options = $wpdb->get_col("SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE 'qtranslate_%'");
		if(empty($options)){
			$f();
			$imported = true;
		}
	}
	$s = '</p><p>'.sprintf(__('It might be a good idea to review %smigration instructions%s, if you have not yet done so.', 'qtranslate'),'<a href="https://qtranslatexteam.wordpress.com/migration/" target="_blank">','</a>').'</p><p><a class="button" href="">';
	$msg=sprintf(__('Activation of plugin %s deactivated plugin %s since they cannot run simultaneously.', 'qtranslate'), $qtxlink, $link).' ';
	if($imported){
		$msg.=sprintf(__('The compatible settings from %s have been imported to %s. Further tuning, import, export and reset of options can be done at Settings/Languages configuration page, once %s is running.%sContinue%s', 'qtranslate'), $nm, $qtxnm, $qtxnm, $s, '</a>');
	}else{
		$msg.=sprintf(__('You may import/export compatible settings from %s to %s on Settings/Languages configuration page, once %s is running.%sContinue%s', 'qtranslate'), $nm, $qtxnm, $qtxnm, $s, '</a>');
	}
	//$nonce=wp_create_nonce('deactivate-plugin_'.$plugin);
	//$msg=sprintf(__('Plugin %s cannot run concurrently with %s, please %sdeactivate %s%s. You may import compatible settings from %s to %s on Settings/Languages configuration page, once %s is running.','qtranslate'),$qtxlink,$link,'<a href="'.admin_url('plugins.php?action=deactivate&plugin='.encode($plugin).'&plugin_status=all&paged=1&s&_wpnonce='.$nonce.'">',$nm,'</a>',$nm,$qtxnm,$qtxnm);
	//$msg=sprintf(__('Activation of plugin %s deactivated plugin %s since they cannot run simultaneously. You may import compatible settings from %s to %s on Settings/%sLanguages%s configuration page, once %s is running.%sContinue%s','qtranslate'),$qtxlink,$link,$nm,$qtxnm,'<a href="'.admin_url('/options-general.php?page=qtranslate-x').'">','</a>',$qtxnm,'</p><p><a  class="button" href="">','</a>');
	wp_die('<p>'.$msg.'</p>');
}

function qtranxf_admin_notices_version(){
	$ver_cur = qtranxf_version_int();
	$ver_prv = get_option('qtranslate_version_previous',$ver_cur);
	if($ver_cur == $ver_prv) return;

	if($ver_prv < 33000 && $ver_cur >= 32980) qtranxf_admin_notices_new_options(array(__('Highlight Style', 'qtranslate'),__('LSB Style', 'qtranslate')),'3.3','https://qtranslatexteam.wordpress.com/2015/03/30/release-notes-3-3');

	if($ver_prv < 34000 && $ver_cur >= 32980) qtranxf_admin_notices_new_options(array('<a href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">'.__('Configuration Files', 'qtranslate').'</a>'),'3.4','https://qtranslatexteam.wordpress.com/2015/05/15/release-notes-3-4/');
}
add_action('admin_notices', 'qtranxf_admin_notices_version');

/*
function qtranxf_admin_notice_deactivated($plugin)
{
	$plugin_file=WP_CONTENT_DIR.'/plugins/'.$plugin;
	$plugin_data=get_plugin_data( plugin_file, false, true );
echo "qtranxf_admin_notice_deactivated: $plugin";
var_dump($plugin_data);
	if(!$plugin_data) return;
	$nm='<a href="https://wordpress.org/plugins/'.dirname($plugin).'/">'.$plugin_data['Name'].'</a>';
	echo printf(__('Plugin qTranslate&#8209;X deactivated plugin %s since they cannot run simultaneously. You may import compatible settings from %s to qTranslate&#8209;X on Settings/"<a href="%s">Languages</a>" configuration page.','qtranslate'),$nm,$nm,admin_url('options-general.php?page=qtranslate-x'));
}

function qtranxf_admin_notices($nm)
{
	//if($_SERVER['REQUEST_METHOD']!='GET') return;
	if(isset($_REQUEST['qtx_dismiss'])){
		update_option('qtranslate_admin_notices',array());
		return;
	}
	$admin_notices=get_option('qtranslate_admin_notices',array());
	if(empty($admin_notices)) return;
	//echo '<div class="updated notice is-dismissible">';
	echo '<div class="update-nag notice is-dismissible">';
	echo '<div style="float: right"><a href="?qtx_dismiss"><small>dismiss</small></a></div>';
	foreach($admin_notices as $key=>$notice){
		echo '<p>';
		switch($key){
			case 'mqtranslate/mqtranslate.php':
			case 'qtranslate/qtranslate.php':
			case 'qtranslate-xp/ppqtranslate.php':
			case 'ztranslate/ztranslate.php':
				qtranxf_admin_notice_deactivated($key);
				break;
			default: echo $notice; break;
		}
		echo '</p>';
	}
	echo '</div>';
}

function qtranxf_check_qtranslate_other()
{
	// Check if other qTranslate forks are active.
	$plugins=array();
	if(is_plugin_active('mqtranslate/mqtranslate.php')) $plugins[]='qtranslate-xp/ppqtranslate.php';
	if(is_plugin_active('qtranslate/qtranslate.php')) $plugins[]='qtranslate/qtranslate.php';
	if(is_plugin_active('qtranslate-xp/ppqtranslate.php')) $plugins[]='qtranslate-xp/ppqtranslate.php';
	if(is_plugin_active('ztranslate/ztranslate.php')) $plugins[]='ztranslate/ztranslate.php';
	if(empty($plugins)) return;
	$admin_notices=get_option('qtranslate_admin_notices',array());
	$t=time();
	foreach($plugins as $plugin){
		$admin_notices[$plugin]=$t;
	}
	deactivate_plugins($plugins,true);
	add_action('admin_notices', 'qtranxf_admin_notices');
}
//muplugins_loaded plugins_loaded
//add_action('admin_init', 'qtranxf_check_qtranslate_other', 0);
*/

function qtranxf_admin_notice_plugin_conflict($title,$plugin){
	if(!is_plugin_active($plugin)) return;
	$me=qtranxf_get_plugin_link();
	$link='<a href="https://wordpress.org/plugins/'.dirname($plugin).'/" style="color:magenta" target="_blank">'.$title.'</a>';
	echo '<div class="error notice is-dismissible"><p style="font-size: larger">';
	printf(__('%sError:%s plugin %s cannot run concurrently with plugin %s. You may import and export compatible settings between %s and %s on Settings/<a href="%s">Languages</a> configuration page. Then you have to deactivate one of the plugins to continue.','qtranslate'),'<span style="color:red"><strong>','</strong></span>',$me,$link,'qTranslate&#8209;X',$title,admin_url('options-general.php?page=qtranslate-x'), 'qtranslate');
	echo ' ';
	printf(__('It might be a good idea to review %smigration instructions%s, if you have not yet done so.', 'qtranslate'),'<a href="https://qtranslatexteam.wordpress.com/migration/" target="_blank">','</a>');

	$nonce=wp_create_nonce('deactivate-plugin_'.$plugin);
	echo '</p><p> &nbsp; &nbsp; &nbsp; &nbsp;<a class="button" href="'.admin_url('plugins.php?action=deactivate&plugin='.urlencode($plugin).'&plugin_status=all&paged=1&s&_wpnonce='.$nonce).'"><strong>'.sprintf(__('Deactivate %s', 'qtranslate'), '<span style="color:magenta">'.$title.'</span>').'</strong></a>';
	$nonce=wp_create_nonce('deactivate-plugin_qtranslate-x/qtranslate.php');
	echo ' &nbsp; &nbsp; &nbsp; &nbsp;<a class="button" href="'.admin_url('plugins.php?action=deactivate&plugin='.urlencode('qtranslate-x/qtranslate.php').'&plugin_status=all&paged=1&s&_wpnonce='.$nonce).'"><strong>'.sprintf(__('Deactivate %s', 'qtranslate'), '<span style="color:blue">qTranslate&#8209;X</span>').'</strong></a>';
	echo '</p></div>';
}

function qtranxf_admin_notices_plugin_conflicts(){
	qtranxf_admin_notice_plugin_conflict('qTranslate','qtranslate/qtranslate.php');
	qtranxf_admin_notice_plugin_conflict('mqTranslate','mqtranslate/mqtranslate.php');
	qtranxf_admin_notice_plugin_conflict('qTranslate Plus','qtranslate-xp/ppqtranslate.php');
	qtranxf_admin_notice_plugin_conflict('zTranslate','ztranslate/ztranslate.php');
	do_action('qtranslate_admin_notices_plugin_conflicts');
}
add_action('admin_notices', 'qtranxf_admin_notices_plugin_conflicts');

function qtranxf_get_plugin_link(){
	return '<a href="https://wordpress.org/plugins/qtranslate-x/" style="color:blue" target="_blank">qTranslate&#8209;X</a>';
}

function qtranxf_admin_notice_plugin_integration($plugin,$integr_title,$integr_plugin){
	if(!is_plugin_active($plugin)) return 0;
	if(is_plugin_active($integr_plugin)) return 0;

	$integr_bnm = dirname($integr_plugin);//
	$messages = get_option('qtranslate_admin_notices');
	if(isset($messages['integration-'.$integr_bnm])) return 0;

	$plugin_file = qtranxf_find_plugin_file($plugin);
	if(!$plugin_file) return 0;
	$pd = get_plugin_data( WP_CONTENT_DIR .'/'. $plugin_file, false, true );
	$pluginName = $pd['Name'];
	$pluginURI = $pd['PluginURI'];

	$me=qtranxf_get_plugin_link();
	$plugin_link='<a href="'.$pluginURI.'/" style="color:blue" target="_blank">'.$pluginName.'</a>';
	$integr_link='<a href="https://wordpress.org/plugins/'.$integr_bnm.'/" style="color:magenta" target="_blank">'.$integr_title.'</a>';

	echo '<div class="update-nag notice is-dismissible" id="qtranxs-integration-'.$integr_bnm.'"><p style="font-size: larger">';
	printf(__('Plugin %s may be integrated with multilingual plugin %s with a help of plugin %s.','qtranslate'),$plugin_link,$me,$integr_link);
	echo ' ';
	echo __('Please, press an appropriate button below.','qtranslate');

	$integr_file = qtranxf_find_plugin_file($integr_plugin);
	if($integr_file){
		echo '</p><p> &nbsp; &nbsp; &nbsp; &nbsp;<a class="button" href="'.esc_url( wp_nonce_url( admin_url('plugins.php?action=activate&plugin='.urlencode($integr_plugin)), 'activate-plugin_'.$integr_plugin)).'"><strong>'.sprintf(__('Activate plugin %s', 'qtranslate'), '<span style="color:magenta">'.$integr_title.'</span>').'</strong></a>';
	}else{
		echo '</p><p> &nbsp; &nbsp; &nbsp; &nbsp;<a class="button" href="'.esc_url( wp_nonce_url( admin_url('update.php?action=install-plugin&plugin='.urlencode($integr_bnm)), 'install-plugin_'.$integr_bnm)).'"><strong>'.sprintf(__('Install plugin %s', 'qtranslate'), '<span style="color:magenta">'.$integr_title.'</span>').'</strong></a>';
	}
	echo '&nbsp;&nbsp;&nbsp;<a class="button" href="javascript:qtranxj_dismiss_admin_notice(\'integration-'.$integr_bnm.'\');">'.__('I am aware of that, dismiss this message.', 'qtranslate');
	echo '</a></p></div>';
	return 1;
}

function qtranxf_admin_notice_dismiss_script(){//($response_action=null)
	static $admin_notice_dismiss_script = false;
	if($admin_notice_dismiss_script) return;
	$admin_notice_dismiss_script = true;
?>
<script type="text/javascript">
	function qtranxj_dismiss_admin_notice(id) {
		jQuery('#qtranxs-'+id).css('display','none');
		jQuery.post(ajaxurl, { action: 'qtranslate_admin_notice', notice_id: id }
	<?php /*
		if($response_action){
			//,function(response) { eval(response); }
			echo ', function(response) { '.$response_action.' }';
		}
	*/ ?>
		);
	}
</script>
<?php
}

function qtranxf_admin_notices_plugin_integration(){
	global $pagenow;
	if($pagenow == 'update.php') return;
	$cnt = 0;

	$cnt += qtranxf_admin_notice_plugin_integration('advanced-custom-fields/acf.php', 'ACF qTranslate', 'acf-qtranslate/acf-qtranslate.php');

	$cnt += qtranxf_admin_notice_plugin_integration('all-in-one-seo-pack/all_in_one_seo_pack.php', 'All in One SEO Pack & qTranslate&#8209;X', 'all-in-one-seo-pack-qtranslate-x/qaioseop.php');

	//$cnt += qtranxf_admin_notice_plugin_integration('events-made-easy/events-manager.php', 'Events Made Easy & qTranslate&#8209;X', 'events-made-easy-qtranslate-x/events-made-easy-qtranslate-x.php');

	$cnt += qtranxf_admin_notice_plugin_integration('gravity-forms-addons/gravity-forms-addons.php', 'qTranslate support for GravityForms', 'qtranslate-support-for-gravityforms/qtranslate-support-for-gravityforms.php');

	$cnt += qtranxf_admin_notice_plugin_integration('woocommerce/woocommerce.php', 'WooCommerce & qTranslate&#8209;X', 'woocommerce-qtranslate-x/woocommerce-qtranslate-x.php');

	$cnt += qtranxf_admin_notice_plugin_integration('wordpress-seo/wp-seo.php', 'Integration: Yoast SEO & qTranslate&#8209;X', 'wp-seo-qtranslate-x/wordpress-seo-qtranslate-x.php');

	$cnt += qtranxf_admin_notice_plugin_integration('js_composer/js_composer.php', 'WPBakery Visual Composer & qTranslate&#8209;X', 'js-composer-qtranslate-x/js-composer-qtranslate-x.php');

	if($cnt>0){
		qtranxf_admin_notice_dismiss_script();
	}
}
add_action('admin_notices', 'qtranxf_admin_notices_plugin_integration');


function qtranxf_admin_notices_next_thanks(){
	$messages = get_option('qtranslate_admin_notices');
	if(isset($messages['next_thanks'])) return;
	qtranxf_admin_notice_dismiss_script();
	//qtranxj_dismiss_admin_notice('next_thanks');
	//document.location.href = 'https://qtranslatexteam.wordpress.com/donations/';
?>
<script type="text/javascript">
	function qtranxj_dismiss_admin_notice_next_thanks() {
		jQuery('#qtranxs-next_thanks').css('display','none');
		jQuery.post(ajaxurl, { action: 'qtranslate_admin_notice', notice_id: 'next_thanks' }
		, function(response) { document.location.href = 'https://qtranslatexteam.wordpress.com/donations/'; }
		);
	}
</script>
<?php
	$tnx=sprintf(__('Thank you for using %s plugin!', 'qtranslate'), 'qTranslate&#8209;X');
	echo '<div class="updated notice is-dismissible" id="qtranxs-next_thanks"><table><tr><td style="width: 0%"><img src="'.plugins_url('admin/img/qtxlogo.png',QTRANSLATE_FILE).'" title="'.$tnx.'" alt="'.$tnx.'"></td><td style="width: 100%">';
	echo '<p>';// style="" text-align: center; font-size: larger;
	printf(__('Thank you for using %s plugin!', 'qtranslate'), '<a href="https://wordpress.org/plugins/qtranslate-x/" style="color:blue" target="_blank">qTranslate&#8209;X</a>');
	//echo '</p><p>';
	echo '<br/>';
	echo __('Our team would greatly appreciate any feedback:', 'qtranslate');
	echo '<ul style="list-style: square; list-style-position: inside;"><li>';
	printf(__('%sUse Support Forum%s to ask a question.', 'qtranslate'), '<a href="https://wordpress.org/support/plugin/qtranslate-x" target="_blank">', '</a>');
	echo '</li><li>';
	printf(__('%sVisit%s %s website.', 'qtranslate'), '<a href="https://qtranslatexteam.wordpress.com/" target="_blank">', '</a>', '"<a href="https://qtranslatexteam.wordpress.com/about/" target="_blank">qTranslate-X explained</a>"');
	echo '</li><li>';
	printf(__('%sShare a new idea%s with our community.', 'qtranslate'), '<a href="https://qtranslatexteam.wordpress.com/contact-us/"  target="_blank">', '</a>');
	echo '</li><li>';
	printf(__('%sReview the plugin%s at WordPress site.', 'qtranslate'), '<a href="https://wordpress.org/support/view/plugin-reviews/qtranslate-x?rate=5#postform" target="_blank">', '</a>');
	echo '</ul>';
	echo '</p><p>&nbsp;';
	echo '<a class="button" href="javascript:qtranxj_dismiss_admin_notice_next_thanks();">'.__('Thank me again in a few months!', 'qtranslate').'</a>';
	echo '</p>';
	echo '</td></tr></table></div>';
}
//add_action('admin_notices', 'qtranxf_admin_notices_next_thanks');


function qtranxf_admin_notices_survey_request(){
	$messages = get_option('qtranslate_admin_notices');
	if(isset($messages['survey-translation-service'])) return;
	qtranxf_admin_notice_dismiss_script();
	echo '<div class="updated notice is-dismissible" id="qtranxs-survey-translation-service"><p style="font-size: larger;">';// text-align: center;
	printf(__('Thank you for using %s plugin!', 'qtranslate'), '<a href="https://wordpress.org/plugins/qtranslate-x/" style="color:blue" target="_blank">qTranslate&#8209;X</a>');
	echo '<br/>';
	printf(__('Please, help us to make a decision on "%s" feature, press the button below.', 'qtranslate'), __('Translation Service', 'qtranslate'));
	echo '</p><p><a class="button" href="http://www.marius-siroen.com/qTranslate-X/TranslateServices/" target="_blank">';
	printf(__('Survey on "%s" feature', 'qtranslate'), __('Translation Service', 'qtranslate'));
	echo '</a>&nbsp;&nbsp;&nbsp;<a class="button" href="javascript:qtranxj_dismiss_admin_notice(\'survey-translation-service\');">'.__('I have already done it, dismiss this message.', 'qtranslate');
	echo '</a></p></div>';
}
add_action('admin_notices', 'qtranxf_admin_notices_survey_request');


function qtranxf_admin_notices_errors(){
	//qtranxf_dbg_log('14.qtranxf_admin_notices_errors:');
	$msgs = get_option('qtranslate_config_errors');
	if(!is_array($msgs)) return;
	foreach($msgs as $key => $msg){
		echo '<div class="error notice is-dismissible" id="qtranxs_config_error_'.$key.'"><p><a href="'.admin_url('options-general.php?page=qtranslate-x').'" style="color:magenta">qTranslate&#8209;X</a>:&nbsp;<strong><span style="color: red;">'.__('Error', 'qtranslate').'</span></strong>:&nbsp;'.$msg.'</p></div>';
	}
}
add_action('admin_notices', 'qtranxf_admin_notices_errors');

function qtranxf_update_option_admin_notices($messages, $id, $toggle=true){
	if(!is_array($messages)) $messages = array();
	if($toggle && isset($messages[$id])) unset($messages[$id]);
	else $messages[$id] = time();
	update_option('qtranslate_admin_notices',$messages);
	return $messages;
}

function qtranxf_update_option_admin_notices_id($id){
	$messages = get_option('qtranslate_admin_notices',array());
	return qtranxf_update_option_admin_notices($messages, $id, false);
}

function qtranxf_update_admin_notice($id){
	$messages = get_option('qtranslate_admin_notices',array());
	return qtranxf_update_option_admin_notices($messages,$id);
}

function qtranxf_ajax_qtranslate_admin_notice(){
	if(!isset($_POST['notice_id'])) return;
	$id = sanitize_text_field($_POST['notice_id']);
	qtranxf_update_admin_notice($id);
	//echo "jQuery('#qtranxs_+$id').css('display','none');"; die();
}
add_action('wp_ajax_qtranslate_admin_notice', 'qtranxf_ajax_qtranslate_admin_notice');

function qtranxf_admin_notices_new_options($nms,$ver,$url){
	$messages = get_option('qtranslate_admin_notices');
	$id='new-options-ver-'.str_replace('.','',$ver);
	if(isset($messages[$id])) return;
	$me=qtranxf_get_plugin_link();
	qtranxf_admin_notice_dismiss_script();
	echo '<div class="update-nag notice is-dismissible" id="qtranxs-'.$id.'">';// style="font-size: larger"
	//echo __('One time message:', 'qtranslate'); echo ' ';
	if(!empty($nms)){
		$opns = '';
		foreach($nms as $nm){
			if(!empty($opns)) $opns .= ', ';
			$opns .= '"'.__($nm, 'qtranslate').'"';
		}
		echo '<p>';
		printf(__('The latest version of plugin %s has a number of new options, for example, %s, which may change the look of some pages. Please, review the help text of new options on %sconfiguration page%s.','qtranslate'), $me, $opns, '<a href="'.admin_url('options-general.php?page=qtranslate-x').'">','</a>');
		echo '</p>';
	}
	if(!empty($url)){
		echo '<p>';
		printf(__('It is recommended to review %sRelease Notes%s for this new version of %s before making any further changes.','qtranslate'), '<a href="'.$url.'" target="_blank">','</a>',$me);
		echo '</p>';
	}
	echo '<p>&nbsp;&nbsp;&nbsp;<a class="button" href="javascript:qtranxj_dismiss_admin_notice(\''.$id.'\');">'.__('I have already done it, dismiss this message.', 'qtranslate');
	echo '</a></p></div>';
}

/** register activation/deactivation hooks */
function qtranxf_register_activation_hooks(){
	$qtx_plugin_basename = qtranxf_plugin_basename();
	register_activation_hook($qtx_plugin_basename, 'qtranxf_activation_hook');
	register_deactivation_hook($qtx_plugin_basename, 'qtranxf_deactivation_hook');
}
