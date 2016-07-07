<?php
if ( !defined( 'ABSPATH' ) ) exit;

if(WP_DEBUG){
	require_once(QTRANSLATE_DIR.'/inc/qtx_dbg.php');
}else{
	if(!function_exists('qtranxf_dbg_log')){
		function qtranxf_dbg_log($msg,$var=null,$bt=false,$exit=false){}
		function qtranxf_dbg_echo($msg,$var=null,$bt=false,$exit=false){}
		function qtranxf_dbg_log_if($condition,$msg,$var=null,$bt=false,$exit=false){}
		function qtranxf_dbg_echo_if($condition,$msg,$var=null,$bt=false,$exit=false){}
	}
	//assert_options(ASSERT_ACTIVE,false);
	//assert_options(ASSERT_WARNING,false);
	//assert_options(ASSERT_QUIET_EVAL,true);
}

/**
 * @since 3.3.1
 */
function qtranxf_error_log($msg) {
	qtranxf_add_error($msg);
	error_log('qTranslate-X: '.strip_tags($msg));
}

/**
 * @since 3.3.7
 */
function qtranxf_add_error($msg) { qtranxf_add_admin_notice($msg,'errors'); }
function qtranxf_add_warning($msg) { qtranxf_add_admin_notice($msg,'warnings'); }
function qtranxf_add_message($msg) { qtranxf_add_admin_notice($msg,'messages'); }

/**
 * @since 3.3.8.4
 */
function qtranxf_add_admin_notice($msg, $kind) {
	global $q_config;
	if(isset($q_config['url_info'][$kind])){
		if(!in_array($msg,$q_config['url_info'][$kind]))
			$q_config['url_info'][$kind][] = $msg;
	}else{
		if(!isset($q_config['url_info'])) $q_config['url_info'] = array();
		$q_config['url_info'][$kind] = array($msg);
	}
}

/**
 * Default domain translation for strings already translated by WordPress.
 * Use of this function prevents xgettext, poedit and other translating parsers from including the string that does not need translation.
 */
function qtranxf_translate_wp($s) { return __($s); }

/**
 * Looks up a translation in domain 'qtranslate', and if it is not there, uses the default WordPress domain to translate.
 * @since 3.4.5.5
*/
function qtranxf_translate($s)
{
	$t = get_translations_for_domain( 'qtranslate' );
	if(isset($t->entries[$s]) && !empty($t->entries[$s]->translations)){
		return $t->entries[$s]->translations[0];
	}
	return qtranxf_translate_wp($s);
}

/**
 * @since 3.3.8.8
 */
function qtranxf_plugin_basename(){
	static $s;
	if(!$s){
		$s = plugin_basename(wp_normalize_path(QTRANSLATE_FILE));
	}
	return $s;
}

/**
 * @since 3.3.2
 */
function qtranxf_plugin_dirname(){
	static $s;
	if(!$s){
		$b = qtranxf_plugin_basename();
		$s = dirname($b);
	}
	return $s;
}

/**
 * Return path to plugin folder relative to WP_CONTENT_DIR. Works for plugin paths only.
 * No trailing slash in the return string.
 * It may return absolute path to plugin folder in case content and plugin directories are on different devices.
 * $plugin is path to plugin file, like the one coming from __FILE__.
 * @since 3.4.5
*/
function qtranxf_dir_from_wp_content($plugin){
	global $wp_plugin_paths;
	$plugin_realpath = wp_normalize_path( dirname( realpath( $plugin ) ) );
	$d = $plugin_realpath;
	foreach ( $wp_plugin_paths as $dir => $realdir ) {
		if ( $plugin_realpath != $realdir ) continue;
		$d = $dir;
		break;
	}
	$c = trailingslashit(wp_normalize_path(WP_CONTENT_DIR));
	$d_len = strlen($d);
	$c_len = strlen($c);
	$i = 0;
	while($i < $d_len && $i < $c_len && $d[$i] == $c[$i]) ++$i;
	if($i == $c_len) return substr($d,$c_len);
	if($i == 0) return $d;//return absolute path then
	$c = substr($c,$i);
	$d = substr($d,$i);
	for($i = substr_count($c,'/'); --$i >= 0;) $d = '../'.$d;
	return $d;
}

/**
 * Return path to QTX plugin folder relative to WP_CONTENT_DIR.
 * Uses qtranxf_dir_from_wp_content
 * @since 3.4
 * @since 3.4.5 modified for multisite.
 */
function qtranxf_plugin_dirname_from_wp_content(){
	static $s;
	if(!$s){
		//qtranxf_dbg_log('__FILE__: ', __FILE__);//links are resolved
		//qtranxf_dbg_log('wp_normalize_path(__FILE__): ', wp_normalize_path(__FILE__));//links are resolved, same as __FILE__
		//qtranxf_dbg_log('plugin_dir_path: ', plugin_dir_path( __FILE__ ));//links are resolved, with trailing slash
		//qtranxf_dbg_log('plugin_basename: ', plugin_basename( __FILE__ ));//no links resolved
		//qtranxf_dbg_log('WP_CONTENT_DIR: ', WP_CONTENT_DIR);//no links resolved
		//qtranxf_dbg_log('wp_content_dir(): ', wp_content_dir());//no links resolved
		//qtranxf_dbg_log('WP_PLUGIN_DIR: ', WP_PLUGIN_DIR);//no links resolved
		//qtranxf_dbg_log('WP_MU_PLUGIN_DIR: ', WPMU_PLUGIN_DIR);//no links resolved
		//qtranxf_dbg_log('plugin_dir_url: ', plugin_dir_url( __FILE__ ));//no links, naturally
		//qtranxf_dbg_log('content_url: ', content_url());//no links either
		$s = qtranxf_dir_from_wp_content(QTRANSLATE_FILE);
	}
	return $s;
}

function qtranxf_parseURL($url) {
	//this is not the same as native parse_url and so it is in use
	//it should also work quicker than native parse_url, so we should keep it?
	//preg_match('!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!',$url,$out);
	preg_match('!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:?#]+)?(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!',$url,$out);
	//qtranxf_dbg_log('qtranxf_parseURL('.$url.'): out:',$out);
	//new code since 3.2.8 - performance improvement
	$result = array();
	if(!empty($out[1])) $result['scheme'] = $out[1];
	if(!empty($out[2])) $result['user'] = $out[2];
	if(!empty($out[3])) $result['pass'] = $out[3];
	if(!empty($out[4])) $result['host'] = $out[4];
	if(!empty($out[6])) $result['path'] = $out[6];
	if(!empty($out[7])) $result['query'] = $out[7];
	if(!empty($out[8])) $result['fragment'] = $out[8];
	/*
	//new code since 3.2-b2, older version produces warnings in the debugger
	$result = @array(
		'scheme' => isset($out[1]) ? $out[1] : '',
		'user' => isset($out[2]) ? $out[2] : '',
		'pass' => isset($out[3]) ? $out[3] : '',
		'host' => isset($out[4]) ? $out[4] : '',
		'path' => isset($out[6]) ? $out[6] : '',
		'query' => isset($out[7]) ? $out[7] : '',
		'fragment' => isset($out[8]) ? $out[8] : ''
		);
	*/
	if(!empty($out[5])) $result['host'] .= ':'.$out[5];
/*
	//this older version produce warnings in the debugger
	$result = @array(
		"scheme" => $out[1],
		"host" => $out[4].(($out[5]=='')?'':':'.$out[5]),
		"user" => $out[2],
		"pass" => $out[3],
		"path" => $out[6],
		"query" => $out[7],
		"fragment" => $out[8]
		);
*/
/* not the same as above for relative url without host like 'path/1/2/3'
	$result = parse_url($url) + array(
		'scheme' => '',
		'host' => '',
		'user' => '',
		'pass' => '',
		'path' => '',
		'query' => '',
		'fragment' => ''
	);
	isset($result['port']) and $result['host'] .= ':'. $result['port'];
*/
	return $result;
}

/**
 * @since 3.2.8
 */
function qtranxf_buildURL($urlinfo,$homeinfo) {
	//qtranxf_dbg_log('qtranxf_buildURL: $urlinfo:',$urlinfo);
	//qtranxf_dbg_log('qtranxf_buildURL: $homeinfo:',$homeinfo);
	if(empty($urlinfo['host'])){//relative path stays relative
		$url = '';
	}else{
		$url = (empty($urlinfo['scheme']) ? $homeinfo['scheme'] : $urlinfo['scheme']).'://';
		if(!empty($urlinfo['user'])){
			$url .= $urlinfo['user'];
			if(!empty($urlinfo['pass'])) $url .= ':'.$urlinfo['pass'];
			$url .= '@';
		}elseif(!empty($homeinfo['user'])){
			$url .= $homeinfo['user'];
			if(!empty($homeinfo['pass'])) $url .= ':'.$homeinfo['pass'];
			$url .= '@';
		}
		$url .= empty($urlinfo['host']) ? $homeinfo['host'] : $urlinfo['host'];
	}
	if(!empty($urlinfo['path-base'])) $url .= $urlinfo['path-base'];
	if(!empty($urlinfo['wp-path'])) $url .= $urlinfo['wp-path'];
	if(!empty($urlinfo['query'])) $url .= '?'.$urlinfo['query'];
	if(!empty($urlinfo['fragment'])) $url .= '#'.$urlinfo['fragment'];
	//qtranxf_dbg_log('qtranxf_buildURL: $url:',$url);
	return $url;
}

/**
 * @since 3.2.8 Copies the data needed for qtranxf_buildURL and qtranxf_url_set_language
 */
function qtranxf_copy_url_info($urlinfo) {
	$r = array();
	if(isset($urlinfo['scheme'])) $r['scheme'] = $urlinfo['scheme'];
	if(isset($urlinfo['user'])) $r['user'] = $urlinfo['user'];
	if(isset($urlinfo['pass'])) $r['pass'] = $urlinfo['pass'];
	if(isset($urlinfo['host'])) $r['host'] = $urlinfo['host'];
	if(isset($urlinfo['path-base'])) $r['path-base'] = $urlinfo['path-base'];
	if(isset($urlinfo['path-base-length'])) $r['path-base-length'] = $urlinfo['path-base-length'];
	if(isset($urlinfo['wp-path'])) $r['wp-path'] = $urlinfo['wp-path'];
	if(isset($urlinfo['query'])) $r['query'] = $urlinfo['query'];
	if(isset($urlinfo['fragment'])) $r['fragment'] = $urlinfo['fragment'];
	if(isset($urlinfo['query_amp'])) $r['query_amp'] = $urlinfo['query_amp'];
	return $r;
}

function qtranxf_get_address_info($url) {
	$info = qtranxf_parseURL( $url );
	if(isset($info['path'])){
		$info['path-length'] = strlen($info['path']);
	}else{
		$info['path'] = '';
		$info['path-length'] = 0;
	}
	return $info;
}

function qtranxf_get_home_info() {
	static $home_info;
	if(!$home_info){
		//$url = defined('WP_HOME') ? WP_HOME : get_option('home');
		$url = get_option('home');//WP does take care of WP_HOME
		$home_info = qtranxf_get_address_info($url);
	}
	return $home_info;
}

function qtranxf_get_site_info() {
	static $site_info;
	if(!$site_info){
		//$url = defined('WP_SITEURL') ? WP_SITEURL : get_option('siteurl');
		$url = get_option('siteurl');//WP does take care of WP_SITEURL
		$site_info = qtranxf_get_address_info($url);
	}
	return $site_info;
}

function qtranxf_get_url_info($url){
	$urlinfo = qtranxf_parseURL($url);
	qtranxf_complete_url_info($urlinfo);
	qtranxf_complete_url_info_path($urlinfo);
	return $urlinfo;
}

function qtranxf_complete_url_info(&$urlinfo){
	if(!isset($urlinfo['path'])) $urlinfo['path'] = '';
	$path = &$urlinfo['path'];
	$home_info = qtranxf_get_home_info();
	$site_info = qtranxf_get_site_info();
	$home_path = $home_info['path'];
	$site_path = $site_info['path'];
	$home_path_len = $home_info['path-length'];
	$site_path_len = $site_info['path-length'];
	if($home_path_len > $site_path_len){
		if(qtranxf_startsWith($path,$home_path)){
			$urlinfo['path-base'] = $home_path;
			$urlinfo['path-base-length'] = $home_path_len;
			$urlinfo['doing_front_end'] = true;
		}elseif(qtranxf_startsWith($path,$site_path)){
			$urlinfo['path-base'] = $site_path;
			$urlinfo['path-base-length'] = $site_path_len;
			$urlinfo['doing_front_end'] = false;
		}
	}elseif($home_path_len < $site_path_len){
		if(qtranxf_startsWith($path,$site_path)){
			$urlinfo['path-base'] = $site_path;
			$urlinfo['path-base-length'] = $site_path_len;
			$urlinfo['doing_front_end'] = false;
		}elseif(qtranxf_startsWith($path,$home_path)){
			$urlinfo['path-base'] = $home_path;
			$urlinfo['path-base-length'] = $home_path_len;
			$urlinfo['doing_front_end'] = true;
		}
	}elseif($home_path != $site_path){
		if(qtranxf_startsWith($path,$home_path)){
			$urlinfo['path-base'] = $home_path;
			$urlinfo['path-base-length'] = $home_path_len;
			$urlinfo['doing_front_end'] = true;
		}elseif(qtranxf_startsWith($path,$site_path)){
			$urlinfo['path-base'] = $site_path;
			$urlinfo['path-base-length'] = $site_path_len;
			$urlinfo['doing_front_end'] = false;
		}
	}else{//$home_path == $site_path
		if(qtranxf_startsWith($path,$home_path)){
			$urlinfo['path-base'] = $home_path;
			$urlinfo['path-base-length'] = $home_path_len;
		}
	}
}

/**
 * @since 3.2.8
 */
function qtranxf_complete_url_info_path(&$urlinfo){
	if(isset($urlinfo['path-base'])){
		if( empty($urlinfo['path-base']) ){
			$urlinfo['wp-path'] = $urlinfo['path'];
		}elseif( !empty($urlinfo['path']) && qtranxf_startsWith($urlinfo['path'],$urlinfo['path-base']) ){
			//qtranxf_dbg_log('qtranxf_complete_url_info_path: urlinfo: ',$urlinfo);
			if(isset($urlinfo['path'][$urlinfo['path-base-length']])){
				if($urlinfo['path'][$urlinfo['path-base-length']] == '/'){
					$urlinfo['wp-path'] = substr($urlinfo['path'],$urlinfo['path-base-length']);
				}
			}else{
				$urlinfo['wp-path'] = '';
			}
		}
	}
	//$urlinfo['wp-path'] is not set, means url does not belong to this WP installation
}

/**
 * Simplified version of WP's add_query_arg
 * @since 3.2.8
 */
function qtranxf_add_query_arg(&$query, $key_value){
	if(empty($query)) $query = $key_value;
	else $query .= '&'.$key_value;
}

/**
 * Simplified version of WP's remove_query_arg
 * @since 3.2.8
 */
function qtranxf_del_query_arg(&$query, $key){
	//$key_value;
	$match;
	while(preg_match('/(&|&amp;|&#038;|^)('.$key.'=[^&]+)(&|&amp;|&#038;|$)/i',$query,$match)){
		//$key_value = $match[2];
		$p = strpos($query,$match[2]);
		$n = strlen($match[2]);
		if(!empty($match[1])) { $l = strlen($match[1]); $p -= $l; $n += $l; }
		elseif(!empty($match[3])) { $l = strlen($match[3]); $n += $l; }
		//qtranxf_dbg_log('qtranxf_del_query_arg: query: '.$query.'; p='.$p.'; n=',$n);
		$query = substr_replace($query,'',$p,$n);
		//qtranxf_dbg_log('qtranxf_del_query_arg: query: ',$query);
	}
	//return $key_value;
}

/*
 * @since 2.3.8 simplified version of esc_url
*/
function qtranxf_sanitize_url($url)
{
	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);
	$strip = array('%0d', '%0a', '%0D', '%0A');
	$count;
	do{ $url = str_replace( $strip, '', $url, $count ); } while($count);
	return $url;
}

function qtranxf_insertDropDownElement($language, $url, $id){
	global $q_config;
	$html ="
		var sb = document.getElementById('qtranxs_select_".$id."');
		var o = document.createElement('option');
		var l = document.createTextNode('".$q_config['language_name'][$language]."');
		";
	if($q_config['language']==$language)
		$html .= "o.selected = 'selected';";
		$html .= "
		o.value = '".addslashes(htmlspecialchars_decode($url, ENT_NOQUOTES))."';
		o.appendChild(l);
		sb.appendChild(o);
		";
	return $html;
}

function qtranxf_get_domain_language($host){
	global $q_config;
	//todo should have hash host->lang
	//foreach($q_config['enabled_languages'] as $lang){
	//	if(!isset($q_config['domains'][$lang])) continue;
	//	if($q_config['domains'][$lang] != $host) continue;
	//	return $lang;
	//}
	foreach($q_config['domains'] as $lang => $h){
		if($h == $host) return $lang;
	}
}

function qtranxf_external_host_ex($host,$homeinfo){
	global $q_config;
	//$homehost = qtranxf_get_home_info()['host'];
	switch($q_config['url_mode']){
		case QTX_URL_QUERY:
		case QTX_URL_PATH: return $homeinfo['host'] != $host;
		case QTX_URL_DOMAIN: return !qtranxf_endsWith($host,$homeinfo['host']);
		case QTX_URL_DOMAINS:
			foreach($q_config['domains'] as $lang => $h){
				if($h == $host) return false;
			}
			if($homeinfo['host'] == $host) return false;
		default: return true;
	}
}

function qtranxf_external_host($host){
	$homeinfo=qtranxf_get_home_info();
	return qtranxf_external_host_ex($host,$homeinfo);
}

function qtranxf_isMultilingual($str){
	return preg_match('/<!--:[a-z]{2}-->|\[:[a-z]{2}\]|\{:[a-z]{2}\}/im',$str);
}

function qtranxf_is_multilingual_deep($value){
	if(is_string($value)){
		return qtranxf_isMultilingual($value);
	}else if(is_array($value)){
		foreach($value as $k => $v){
			if(qtranxf_is_multilingual_deep($v))//recursive call
				return true;
		}
	}else if(is_object($value) || $value instanceof __PHP_Incomplete_Class){
		foreach(get_object_vars($value) as $k => $v) {
			if(qtranxf_is_multilingual_deep($v))//recursive call
				return true;
		}
	}
	return false;
}

function qtranxf_getLanguage() {
	global $q_config;
	return $q_config['language'];
}

function qtranxf_getLanguageDefault() {
	global $q_config;
	return $q_config['default_language'];
}

/**
 * @since 3.4.5.4 - return language name in native language, former qtranxf_getLanguageName.
*/
function qtranxf_getLanguageNameNative($lang = ''){
	global $q_config;
	if(empty($lang)) $lang = $q_config['language'];
	return $q_config['language_name'][$lang];
}

/**
 * @since 3.4.5.4 - return language name in active language, if available, otherwise the name in native language.
*/
function qtranxf_getLanguageName($lang = ''){
	global $q_config, $l10n;
	if(empty($lang)) return $q_config['language_name'][$q_config['language']];
	if(isset($q_config['language-names'][$lang])) return $q_config['language-names'][$lang];
	if(!isset($l10n['language-names'])){//is not loaded by default, since this place should not be hit frequently
		$locale = $q_config['locale'][$q_config['language']];
		if(!load_textdomain( 'language-names', QTRANSLATE_DIR . '/lang/language-names/language-'.$locale.'.mo' )){
			if($locale[2] == '_'){
				$locale = substr($locale,0,2);
				load_textdomain( 'language-names', QTRANSLATE_DIR . '/lang/language-names/language-'.$locale.'.mo' );
			}
		}
	}
	$translations = get_translations_for_domain('language-names');
	$locale = $q_config['locale'][$lang];
	while(!isset($translations->entries[$locale])){
		if($locale[2] == '_'){
			$locale = substr($locale,0,2);
			if(isset($translations->entries[$locale])) break;
		}
		return $q_config['language-names'][$lang] = $q_config['language_name'][$lang];
	}
	$n = $translations->entries[$locale]->translations[0];
	if(empty($q_config['language_name_case'])){//Camel Case by default
		if(function_exists('mb_convert_case')){// module 'mbstring' may not be installed by default: https://wordpress.org/support/topic/qtranslate_utilsphp-on-line-504
			$n = mb_convert_case($n,MB_CASE_TITLE);
		}else{
			$msg = 'qTranslate-X: Enable PHP module "mbstring" to get names of languages printed in "Camel Case" or disable option \'Show language names in "Camel Case"\' on admin page '.admin_url('options-general.php?page=qtranslate-x#general').'. You may find more information at http://php.net/manual/en/mbstring.installation.php, or search for PHP installation options on control panel of your server provider.';
			error_log($msg);
		}
	}
	return $q_config['language-names'][$lang] = $n;
}

function qtranxf_isEnabled($lang) {
	global $q_config;
	return isset($q_config['locale'][$lang]);//only available languages are loaded, this will work quiker
	//return in_array($lang, $q_config['enabled_languages']);
}

/**
 * @since 3.2.8 - change code to improve performance
 */
function qtranxf_startsWith($s, $n) {
	$l = strlen($n);
	if($l>strlen($s)) return false;
	for($i=0;$i<$l;++$i){
		if($s[$i] != $n[$i])
			return false;
	}
	//if($n == substr($s,0,strlen($n))) return true;
	return true;
}

/**
 * @since 3.2.8
 * $s - string to test
 * $n - needle to search
 */
function qtranxf_endsWith($s, $n) {
	$l = strlen($n);
	$b = strlen($s) - $l;
	if($b < 0) return false;
	for($i=0;$i<$l;++$i){
		if($s[$b+$i] != $n[$i])
			return false;
	}
	return true;
}

function qtranxf_getAvailableLanguages($text) {
	global $q_config;
	$blocks = qtranxf_get_language_blocks($text);
	if(count($blocks) <= 1)
		return FALSE;// no languages set
	$result = array();
	$content = qtranxf_split_languages($blocks);
	foreach($content as $language => $lang_text) {
		$lang_text = trim($lang_text);
		if(!empty($lang_text)) $result[] = $language;
	}
	if(sizeof($result)==0) {
		// add default language to keep default URL
		$result[] = $q_config['language'];
	}
	return $result;
}

function qtranxf_isAvailableIn($post_id, $lang='') {
	global $q_config;
	if(empty($lang)) $lang = $q_config['default_language'];
	global $wpdb;
	$post_content = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM $wpdb->posts WHERE ID = %d", $post_id ) );
	//qtranxf_dbg_log('qtranxf_isAvailableIn: $post_content: ', $post_content);
	if(empty($post_content)) return false;
	$languages = qtranxf_getAvailableLanguages($post_content);
	if($languages===FALSE) return $lang == $q_config['default_language'];
	return in_array($lang,$languages);
}

function qtranxf_convertDateFormatToStrftimeFormat($format) {
	$mappings = array(
		'd' => '%d',
		'D' => '%a',
		'j' => '%E',
		'l' => '%A',
		'N' => '%u',
		'S' => '%q',
		'w' => '%f',
		'z' => '%F',
		'W' => '%V',
		'F' => '%B',
		'm' => '%m',
		'M' => '%b',
		'n' => '%i',
		't' => '%J',
		'L' => '%k',
		'o' => '%G',
		'Y' => '%Y',
		'y' => '%y',
		'a' => '%P',
		'A' => '%p',
		'B' => '%K',
		'g' => '%l',
		'G' => '%L',
		'h' => '%I',
		'H' => '%H',
		'i' => '%M',
		's' => '%S',
		'u' => '%N',
		'e' => '%Q',
		'I' => '%o',
		'O' => '%O',
		'P' => '%s',
		'T' => '%v',
		'Z' => '%1',
		'c' => '%2',
		'r' => '%3',
		'U' => '%4'
	);
	
	$date_parameters = array();
	$strftime_parameters = array();
	$date_parameters[] = '#%#'; 			$strftime_parameters[] = '%';
	foreach($mappings as $df => $sf) {
		$date_parameters[] = '#(([^%\\\\])'.$df.'|^'.$df.')#';	$strftime_parameters[] = '${2}'.$sf;
	}
	// convert everything
	$format = preg_replace($date_parameters, $strftime_parameters, $format);
	// remove single backslashes from dates
	$format = preg_replace('#\\\\([^\\\\]{1})#','${1}',$format);
	// remove double backslashes from dates
	$format = preg_replace('#\\\\\\\\#','\\\\',$format);
	return $format;
}

function qtranxf_convertFormat($format, $default_format) {
	global $q_config;
	// if one of special language-neutral formats are requested, don't replace it
	switch($format){
		case 'Z':
		case 'c':
		case 'r':
		case 'U':
			return qtranxf_convertDateFormatToStrftimeFormat($format); 
		default: break;
	}
	// check for multilang formats
	$format = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($format);
	$default_format = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($default_format);
	switch($q_config['use_strftime']) {
		case QTX_DATE:
			if($format=='') $format = $default_format;
			return qtranxf_convertDateFormatToStrftimeFormat($format);
		case QTX_DATE_OVERRIDE:
			return qtranxf_convertDateFormatToStrftimeFormat($default_format);
		case QTX_STRFTIME:
			return $format;
		case QTX_STRFTIME_OVERRIDE:
		default:
			return $default_format;
	}
}

function qtranxf_convertDateFormat($format) {
	global $q_config;
	if(isset($q_config['date_format'][$q_config['language']])) {
		$default_format = $q_config['date_format'][$q_config['language']];
	} elseif(isset($q_config['date_format'][$q_config['default_language']])) {
		$default_format = $q_config['date_format'][$q_config['default_language']];
	} else {
		$default_format = '';
	}
	return qtranxf_convertFormat($format, $default_format);
}

function qtranxf_convertTimeFormat($format) {
	global $q_config;
	if(isset($q_config['time_format'][$q_config['language']])) {
		$default_format = $q_config['time_format'][$q_config['language']];
	} elseif(isset($q_config['time_format'][$q_config['default_language']])) {
		$default_format = $q_config['time_format'][$q_config['default_language']];
	} else {
		$default_format = '';
	}
	return qtranxf_convertFormat($format, $default_format);
}

function qtranxf_formatCommentDateTime($format) {
	global $comment;
	return qtranxf_strftime(qtranxf_convertFormat($format, $format), mysql2date('U',$comment->comment_date), '');
}

function qtranxf_formatPostDateTime($format) {
	global $post;
	return qtranxf_strftime(qtranxf_convertFormat($format, $format), mysql2date('U',$post->post_date), '');
}

function qtranxf_formatPostModifiedDateTime($format) {
	global $post;
	return qtranxf_strftime(qtranxf_convertFormat($format, $format), mysql2date('U',$post->post_modified), '');
}

//not in use
//function qtranxf_realURL($url = '') {
//	global $q_config;
//	return $q_config['url_info']['original_url'];
//}

function qtranxf_getSortedLanguages($reverse = false) {
	global $q_config;
	$languages = $q_config['enabled_languages'];
	ksort($languages);
	// fix broken order
	$clean_languages = array();
	foreach($languages as $lang) {
		$clean_languages[] = $lang;
	}
	if($reverse) krsort($clean_languages);
	return $clean_languages;
}

function qtranxf_can_redirect() {
	return !defined('WP_ADMIN') && !defined('DOING_AJAX') && !defined('WP_CLI') && !defined('DOING_CRON') && empty($_POST)
	//'REDIRECT_*' needs more testing
	//&& !isset($_SERVER['REDIRECT_URL'])
	&& (!isset($_SERVER['REDIRECT_STATUS']) || $_SERVER['REDIRECT_STATUS']=='200')
	;
}

/**
 * @since 3.4
 */
function qtranxf_post_type(){
	global $post, $post_type;
	if($post_type){
		//qtranxf_dbg_log('qtranxf_post_type: global $post_type=',$post_type);
		return $post_type;
	}
	if($post && isset($post->post_type)){
		$post_type = $post->post_type;
		//qtranxf_dbg_log('qtranxf_post_type: $post->post_type=',$post_type);
		return $post_type;
	}
	if(isset($_REQUEST['post_type'])){
		$post_type = $_REQUEST['post_type'];
		//qtranxf_dbg_log('qtranxf_post_type: REQUEST[post_type]=',$post_type);
		return $post_type;
	}
	//qtranxf_dbg_log('qtranxf_post_type: null $post_type=',$post_type);
	return null;
}

/**
 * Test $cfg['pages'] against $url_path and $url_query ($_SERVER['QUERY_STRING'])
 * @since 3.4
 */
function qtranxf_match_page($cfg, $url_path, $url_query, $d){
	if(!isset($cfg['pages']))
		return true;
	foreach($cfg['pages'] as $page => $query){
		if( preg_match($d.$page.$d,$url_path) !== 1 ) continue;
		//qtranxf_dbg_log('qtranxf_match_page: preg_match('.$d.$query.$d.', '.$url_query.')');
		if( empty($query) || preg_match($d.$query.$d,$url_query) === 1 )
			return true;
	}
	return false;
}

/**
 * @since 3.4
 */
function qtranxf_match_post_type($cfg_post_type, $post_type){

	if(is_string($cfg_post_type))
		return preg_match($cfg_post_type, $post_type) === 1;

	if(isset($cfg_post_type['exclude'])){
		if( preg_match($cfg_post_type['exclude'], $post_type) === 1 ){
			//$exclude = apply_filters('i18n_page_match_exclude_post_type', true, $cfg, $url_path, $url_query, $post_type);
			//if($exclude){// means not to provide any configuration for this post type on this page.
			return null;
			//}
		}
	}

	return true;
}

/**
 * @since 3.3.2
 */
function qtranxf_merge_config($cfg_all, $cfg){
	//return array_merge_recursive($cfg_all,$cfg);
	foreach($cfg as $k => $v){
		if(is_array($v) && isset($cfg_all[$k])){
			$cfg_all[$k] = qtranxf_merge_config($cfg_all[$k], $v);
		}else{
			$cfg_all[$k] = $v;
		}
	}
	return $cfg_all;
}

/**
 * filters i18n configurations for the current page
 */
function qtranxf_parse_page_config($config, $url_path, $url_query) {
	global $q_config;

	//$q_config['i18n-log-dir'] = WP_CONTENT_DIR.'/i18n-config'; //qtranxf_dbg
	if(isset($q_config['i18n-log-dir'])){
		if(!file_exists($q_config['i18n-log-dir'])) if(!mkdir($q_config['i18n-log-dir'])) unset($q_config['i18n-log-dir']);
		if(isset($q_config['i18n-log-dir'])) qtranxf_write_config_log($config, 'all-pages');
	}

	//qtranxf_dbg_log('qtranxf_parse_page_config: $url_path: "'.$url_path.'"; $url_query: "'.$url_query.'"');
	//qtranxf_dbg_log('qtranxf_parse_page_config: $config: ', $config);
	$page_configs = array();
	foreach($config as $pgkey => $pgcfg){
		$d = isset($pgcfg['preg_delimiter']) ? $pgcfg['preg_delimiter'] : '!';
		$matched = qtranxf_match_page($pgcfg, $url_path, $url_query, $d);
		//qtranxf_dbg_log('qtranxf_parse_page_config: $pgcfg: ', $pgcfg);
		//qtranxf_dbg_log('qtranxf_parse_page_config: $matched: ', $matched);
		if($matched === false) continue;

		$post_type_key = '';
		if(isset($pgcfg['post_type'])){
			if(is_string($pgcfg['post_type'])){
				$post_type_key = $d.$pgcfg['post_type'].$d;
				unset($pgcfg['post_type']);
			}else{
				$post_type_key = serialize($pgcfg['post_type']);
				foreach($pgcfg['post_type'] as $k => $item){
					$pgcfg['post_type'][$k] = $d.$item.$d;
				}
			}
		}
		if(!isset($page_configs[$post_type_key])) $page_configs[$post_type_key] = array();
		$page_config = &$page_configs[$post_type_key];

		foreach($pgcfg as $key => $cfg){
			if(empty($cfg)) continue;
			if( $key === 'anchors' ){
				//Anchor elements are defined by id only.
				//Merge unique id values only:
				foreach($cfg as $k => $anchor){
					$id = qtranxf_standardize_config_anchor($anchor);
					if(is_null($id)) continue;
					if(!is_string($id)) $id = $k;
					if( !isset($page_config['anchors']) ) $page_config['anchors'] = array();
					$page_config['anchors'][$id] = $anchor;
				}
			}else
			if( $key === 'forms' ){
				if( !isset($page_config['forms']) ) $page_config['forms'] = array();
				foreach($cfg as $form_id => $pgcfg_form){
					if(!isset($pgcfg_form['fields'])) continue;
					// convert obsolete format for 'fields'
					foreach($pgcfg_form['fields'] as $k => $f){
						if(!isset($f['id'])) continue;
						$id = $f['id'];
						unset($f['id']);
						$pgcfg_form['fields'][$id] = $f;
						if($id !== $k) unset($pgcfg_form['fields'][$k]);
					}
					//figure out obsolete id of form/collection
					if(is_string($form_id)){
						$id = $form_id;
					}else if(isset($pgcfg_form['form']['id'])){
						$id = $pgcfg_form['form']['id'];
						unset($pgcfg_form['form']['id']);
						if(empty($pgcfg_form['form'])) unset($pgcfg_form['form']);
					}else{
						$id = '';
					}
					if(!isset($page_config['forms'][$id])) $page_config['forms'][$id] = $pgcfg_form;
					else $page_config['forms'][$id] = qtranxf_merge_config($page_config['forms'][$id],$pgcfg_form);
				}
			}else{
				if( !isset($page_config[$key]) ) $page_config[$key] = $cfg;
				else $page_config[$key] = qtranxf_merge_config($page_config[$key],$cfg);
			}
		}
	}

	//qtranxf_dbg_log('qtranxf_parse_page_config: $page_configs: ', $page_configs);
	foreach($page_configs as $post_type_key => &$page_config){
		//if(!empty($post_type_key))
		//qtranxf_dbg_log('qtranxf_parse_page_config: $post_type_key="'.$post_type_key.'"; page_config: ', $page_config);
		if(!empty($page_config)){
			//clean up 'fields'
			if(!empty($page_config['forms']))
			foreach($page_config['forms'] as $form_id => $frm){
				if(!isset($frm['fields'])) continue;
				foreach($frm['fields'] as $k => $f){
					if(qtranxf_set_field_jquery($f)){
						$page_config['forms'][$form_id]['fields'][$k] = $f;
					}
				}
			}
			foreach($page_config as $k => $cfg){
				if(empty($cfg)) unset($page_config[$k]);
			}
		}
		if(empty($page_config)) unset($page_configs[$post_type_key]);
	}

	if(isset($q_config['i18n-log-dir'])) qtranxf_write_config_log($page_configs, 'by-post-type', $url_path, $url_query);
	return $page_configs;
}

function qtranxf_write_config_log($config, $sfx='', $url_path=null, $url_query=null, $post_type=null){
	global $q_config;
	if(empty($q_config['i18n-log-dir'])) return;
	if(!is_null($url_path) && empty($url_path)){
		if(defined('WP_ADMIN')){
			global $pagenow;
			$url_path = $pagenow;
		}else{
			$url_path = $q_config['url_info']['wp-path'];
		}
	}
	if(!is_null($url_query) && empty($url_query)){
		$url_query = isset($q_config['url_info']['query']) ? $q_config['url_info']['query'] : '';
	}
	$nm = '';
	if(!empty($url_path)) $nm = preg_replace('![/?&=#\.]+!', '-', trim($url_path,'/'));
	if(!empty($url_query)) $nm .= '-'.preg_replace('![/?&=#\.]+!', '-', $url_query);
	if(empty($nm) && !is_null($url_path)) $nm = 'fronthome';
	if(!empty($sfx)){ if(!empty($nm)) $nm .= '-'; $nm .= $sfx; }

	$fnm = $q_config['i18n-log-dir'].'/i18n-config-'.$nm.'.json';
	if(empty($config)){
		if(file_exists($fnm)) unlink($fnm);
		return;
	}
	$fh = fopen($fnm, 'w');
	if($fh){
		if(!empty($url_path)) fwrite($fh, 'url_path: "'.$url_path.'"'.PHP_EOL);
		if(!empty($url_query)) fwrite($fh, 'url_query: "'.$url_query.'"'.PHP_EOL);
		if(!empty($post_type)) fwrite($fh, 'post_type: "'.$post_type.'"'.PHP_EOL);
		$title = 'config';
		if(!empty($sfx)) $title .= '-'.$sfx;
		fwrite($fh, $title.': '.PHP_EOL .json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);
		fclose($fh);
	}
}

/**
 * @since 3.4
 */
function qtranxf_add_filters($filters){
	global $q_config;
	//qtranxf_dbg_log('qtranxf_add_filters: $filters: ', $filters);
	if(!empty($filters['text'])){
		//qtranxf_dbg_log('$filters[text]: ', $filters['text']);
		foreach($filters['text'] as $nm => $pr){
			if($pr === '') continue;
			//qtranxf_dbg_log('$filters[text]['.$nm.']: ', $pr);
			add_filter($nm, 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', $pr);
		}
	}
	if(!empty($filters['url'])){
		//qtranxf_dbg_log('$filters[url]: ',$filters['url']);
		foreach($filters['url'] as $nm => $pr){
			if($pr === '') continue;
			//qtranxf_dbg_log('$filters[url]['.$nm.']: ', $pr);
			add_filter($nm, 'qtranxf_convertURL', $pr);
		}
	}
	if(!empty($filters['term'])){
		//qtranxf_dbg_log('$filters[term]: ',$filters['term']);
		foreach($filters['term'] as $nm => $pr){
			if($pr === '') continue;
			//qtranxf_dbg_log('$filters[term]['.$nm.']: ', $pr);
			add_filter($nm, 'qtranxf_useTermLib', $pr);
		}
	}
}

/**
 * @since 3.4
 */
function qtranxf_html_locale($locale){
	return str_replace('_','-',$locale);
}

function qtranxf_match_language_locale($locale){
	global $q_config;
	foreach($q_config['enabled_languages'] as $lang) {
		if(qtranxf_html_locale($q_config['locale'][$lang]) == $locale) return $lang;
		if($q_config['locale'][$lang] == $locale) return $lang;
		if(!empty($q_config['locale_html'][$lang]) && $q_config['locale_html'][$lang] == $locale) return $lang;
	}
	$locale_code = substr($locale,0,2);
	foreach($q_config['enabled_languages'] as $lang) {
		if( $locale_code == $lang ) return $lang;
	}
}
