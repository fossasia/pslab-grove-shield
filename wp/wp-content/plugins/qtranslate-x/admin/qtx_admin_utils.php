<?php
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Read or enqueue Java script files listed in $jss.
 * @since 3.3.2
 */
function qtranxf_loadfiles_js($jss, $enqueue_script) {
	$cnt = 0;
	$deps = array();
	foreach($jss as $k => $js){
		if(isset($js['javascript']) && !empty($js['javascript'])){
			echo $js['javascript'];
		}else if(isset($js['src'])){
			$src = $js['src'];
			if($enqueue_script){
				$handle = isset($js['handle']) ? $js['handle'] : (is_string($k) ? $k : 'qtranslate-admin-js-'.(++$cnt) );
				$ver = isset($js['ver']) ? $js['ver'] : QTX_VERSION;
				$url = content_url($src);
				wp_register_script( $handle, $url, $deps, $ver, true);
				wp_enqueue_script( $handle );
				$deps[] = $handle;
			}else{
				$fp = WP_CONTENT_DIR . '/' . $src;
				readfile($fp);
			}
		}
	}
}

function qtranxf_detect_admin_language($url_info) {
	global $q_config;
	$cs=null;
	$lang=null;

	/** @since 3.2.9.9.6
	 * Detect language from $_POST['WPLANG'].
	 */
	if(isset($_POST['WPLANG'])){
		// User is switching the language using "Site Language" field on page /wp-admin/options-general.php
		$wplang = sanitize_text_field($_POST['WPLANG']);
		if(empty($wplang)) $wplang = 'en';
		foreach($q_config['enabled_languages'] as $language){
			if($q_config['locale'][$language] != $wplang) continue;
			$lang = $language;
			break;
		}
		if(!$lang){
			$lang=substr($wplang,0,2);
			$lang=qtranxf_resolveLangCase($lang,$cs);
		}
	}

	if(!$lang && isset($_COOKIE[QTX_COOKIE_NAME_ADMIN])){
		$lang=qtranxf_resolveLangCase($_COOKIE[QTX_COOKIE_NAME_ADMIN],$cs);
		$url_info['lang_cookie_admin'] = $lang;
	}

	if(!$lang){
		$lang = $q_config['default_language'];
	}
	$url_info['doing_front_end'] = false;
	$url_info['lang_admin'] = $lang;
	return $url_info;
}
add_filter('qtranslate_detect_admin_language','qtranxf_detect_admin_language');

/**
 * @return bool true if $a and $b are equal.
 */
function qtranxf_array_compare($a,$b) {
	if( !is_array($a) || !is_array($b) ) return false;
	if(count($a) != count($b)) return false;
	foreach($a as $k => $v){
		if(!isset($b[$k])) return false;
		if(is_array($v)){
			if(!qtranxf_array_compare($v,$b[$k])) return false;
		}else{
			if($b[$k] !== $v) return false;
		}
	}
	return true;
}

function qtranxf_join_texts($texts,$sep) {
	switch($sep){
		//case '<': return qtranxf_join_c($texts);//no longer in use
		case 'byline': return qtranxf_join_byline($texts);
		case '{': return qtranxf_join_s($texts);
		default: return qtranxf_join_b($texts);
	}
}

function qtranxf_convert_to_b($text) {
	$blocks = qtranxf_get_language_blocks($text);
	if( count($blocks) > 1 ){
		foreach($blocks as $key => $b){
			if(empty($b)) unset($blocks[$key]);
		}
	}
	if( count($blocks) <= 1 )
		return $text;

	$text='';
	$lang = false;
	$lang_closed = true;
	foreach($blocks as $block) {
		if(preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
			$lang_closed = false;
			$lang = $matches[1];
			$text .= '[:'.$lang.']';
			continue;
		} elseif(preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
			$lang_closed = false;
			$lang = $matches[1];
			$text .= '[:'.$lang.']';
			continue;
		}
		switch($block){
			case '[:]':
			case '<!--:-->':
				$lang = false;
				break;
			default:
				if( !$lang && !$lang_closed ){
					$text .= '[:]';
					$lang_closed = true;
				}
				$text .= $block;
				break;
		}
	}
	$text .= '[:]';
	return $text;
}

function qtranxf_convert_to_b_no_closing($text) {
	$blocks = qtranxf_get_language_blocks($text);
	if( count($blocks) > 1 ){
		foreach($blocks as $key => $b){
			if(empty($b)) unset($blocks[$key]);
		}
	}
	if( count($blocks) > 1 ){
		$texts = qtranxf_split_blocks($blocks);
		$text = qtranxf_join_b_no_closing($texts);
	}
	return $text;
}

function qtranxf_convert_to_c($text) {
	$blocks = qtranxf_get_language_blocks($text);
	if( count($blocks) > 1 ){
		foreach($blocks as $key => $b){
			if(empty($b)) unset($blocks[$key]);
		}
	}
	if( count($blocks) > 1 ){
		$texts = qtranxf_split_blocks($blocks);
		$text = qtranxf_join_c($texts);
	}
	return $text;
}

function qtranxf_convert_to_b_deep($text) {
	if(is_array($text)) {
		foreach($text as $key => $t) {
			$text[$key] = qtranxf_convert_to_b_deep($t);
		}
		return $text;
	}

	if( is_object($text) || $text instanceof __PHP_Incomplete_Class ) {
		foreach(get_object_vars($text) as $key => $t) {
			$text->$key = qtranxf_convert_to_b_deep($t);
		}
		return $text;
	}

	if(!is_string($text) || empty($text))
		return $text;

	return qtranxf_convert_to_b($text);
}

function qtranxf_convert_to_b_no_closing_deep($text) {
	if(is_array($text)) {
		foreach($text as $key => $t) {
			$text[$key] = qtranxf_convert_to_b_no_closing_deep($t);
		}
		return $text;
	}

	if( is_object($text) || $text instanceof __PHP_Incomplete_Class ) {
		foreach(get_object_vars($text) as $key => $t) {
			$text->$key = qtranxf_convert_to_b_no_closing_deep($t);
		}
		return $text;
	}

	if(!is_string($text) || empty($text))
		return $text;

	return qtranxf_convert_to_b_no_closing($text);
}

function qtranxf_convert_database($action){
	global $wpdb;
	$wpdb->show_errors(); @set_time_limit(0);
	qtranxf_convert_database_options($action);
	qtranxf_convert_database_posts($action);
	qtranxf_convert_database_postmeta($action);
	switch($action){
		case 'b_only':
			return __('Database has been converted to square bracket format.', 'qtranslate').'<br/>'.__('Note: custom entries are not touched.', 'qtranslate');
		case 'c_dual':
			return __('Database has been converted to legacy dual-tag format.', 'qtranslate').'<br/>'.__('Note: custom entries are not touched.', 'qtranslate');
		default: return '';
	}
}

function qtranxf_convert_database_options($action){
	global $wpdb;
	$wpdb->show_errors();
	$result = $wpdb->get_results('SELECT option_id, option_value FROM '.$wpdb->options);
	if(!$result) return;
	switch($action){
		case 'b_only':
			foreach($result as $row) {
				if(!qtranxf_isMultilingual($row->option_value)) continue;
				$value = maybe_unserialize($row->option_value);
				$value_converted=qtranxf_convert_to_b_deep($value);
				$value_serialized = maybe_serialize($value_converted);
				if($value_serialized === $row->option_value) continue;
				//Since 3.2-b3: Replaced mysql_real_escape_string with $wpdb->prepare
				$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->options.' set option_value = %s WHERE option_id = %d', $value_serialized, $row->option_id));
			}
			break;
		case 'c_dual':
			foreach($result as $row) {
				if(!qtranxf_isMultilingual($row->option_value)) continue;
				$value = maybe_unserialize($row->option_value);
				$value_converted=qtranxf_convert_to_b_no_closing_deep($value);
				$value_serialized = maybe_serialize($value_converted);
				if($value_serialized === $row->option_value) continue;
				//Since 3.2-b3: Replaced mysql_real_escape_string with $wpdb->prepare
				$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->options.' set option_value = %s WHERE option_id = %d', $value_serialized, $row->option_id));
			}
			break;
		default: break;
	}
}

function qtranxf_convert_database_posts($action){
	global $wpdb;
	$result = $wpdb->get_results('SELECT ID, post_title, post_content, post_excerpt FROM '.$wpdb->posts);
	if(!$result) return;
	switch($action){
		case 'b_only':
			foreach($result as $row) {
				$title=qtranxf_convert_to_b($row->post_title);
				$content=qtranxf_convert_to_b($row->post_content);
				$excerpt=qtranxf_convert_to_b($row->post_excerpt);
				if( $title==$row->post_title && $content==$row->post_content && $excerpt==$row->post_excerpt ) continue;
				//Since 3.2-b3: Replaced mysql_real_escape_string with $wpdb->prepare
				$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->posts.' set post_content = %s, post_title = %s, post_excerpt = %s WHERE ID = %d',$content, $title, $excerpt, $row->ID));
				//$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = "'.mysql_real_escape_string($content).'", post_title = "'.mysql_real_escape_string($title).'", post_excerpt = "'.mysql_real_escape_string($excerpt).'" WHERE ID='.$row->ID);
			}
			break;
		case 'c_dual':
			foreach($result as $row) {
				$title=qtranxf_convert_to_c($row->post_title);
				$content=qtranxf_convert_to_c($row->post_content);
				$excerpt=qtranxf_convert_to_c($row->post_excerpt);
				if( $title==$row->post_title && $content==$row->post_content && $excerpt==$row->post_excerpt ) continue;
				//Since 3.2-b3: Replaced mysql_real_escape_string with $wpdb->prepare
				$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->posts.' set post_content = %s, post_title = %s, post_excerpt = %s WHERE ID = %d',$content, $title, $excerpt, $row->ID));
				//$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = "'.mysql_real_escape_string($content).'", post_title = "'.mysql_real_escape_string($title).'", post_excerpt = "'.mysql_real_escape_string($excerpt).'" WHERE ID='.$row->ID);
			}
			break;
		default: break;
	}
}

function qtranxf_convert_database_postmeta($action){
	global $wpdb;
	$result = $wpdb->get_results('SELECT meta_id, meta_value FROM '.$wpdb->postmeta);
	if(!$result) return;
	switch($action){
		case 'b_only':
			foreach($result as $row) {
				if(!qtranxf_isMultilingual($row->meta_value)) continue;
				$value = maybe_unserialize($row->meta_value);
				$value_converted=qtranxf_convert_to_b_deep($value);
				$value_serialized = maybe_serialize($value_converted);
				if($value_serialized === $row->meta_value) continue;
				$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->postmeta.' set meta_value = %s WHERE meta_id = %d', $value_serialized, $row->meta_id));
			}
			break;
		case 'c_dual':
			foreach($result as $row) {
				if(!qtranxf_isMultilingual($row->meta_value)) continue;
				$value = maybe_unserialize($row->meta_value);
				$value_converted=qtranxf_convert_to_b_no_closing_deep($value);
				$value_serialized = maybe_serialize($value_converted);
				if($value_serialized === $row->meta_value) continue;
				$wpdb->query($wpdb->prepare('UPDATE '.$wpdb->postmeta.' set meta_value = %s WHERE meta_id = %d', $value_serialized, $row->meta_id));
			}
			break;
		default: break;
	}
}

function qtranxf_mark_default($text) {
	global $q_config;
	$blocks = qtranxf_get_language_blocks($text);
	if( count($blocks) > 1 ) return $text;//already have other languages.
	$content=array();
	foreach($q_config['enabled_languages'] as $language) {
		if($language == $q_config['default_language']) {
			$content[$language] = $text;
		}else{
			$content[$language] = '';
		}
	}
	return qtranxf_join_b($content);
}

function qtranxf_term_name_encoded($name) {
	global $q_config;
	if(isset($q_config['term_name'][$name])) {
		$name = qtranxf_join_b($q_config['term_name'][$name]);
	}
	return $name;
}

function qtranxf_get_term_joined($obj,$taxonomy=null) {
	global $q_config;
	if(is_object($obj)) {
		// object conversion
		if(isset($q_config['term_name'][$obj->name])) {
			//'[:'.$q_config['language'].']'.$obj->name
			$obj->name = qtranxf_join_b($q_config['term_name'][$obj->name]);
			//qtranxf_dbg_log('qtranxf_get_term_joined: object:',$obj);
		}
	}elseif(isset($q_config['term_name'][$obj])) {
		$obj = qtranxf_join_b($q_config['term_name'][$obj]);
		//'[:'.$q_config['language'].']'.$obj.
		//qtranxf_dbg_echo('qtranxf_get_term_joined: string:',$obj,true);//never fired, we probably do not need it
	}
	return $obj;
}

/**
 * @since 3.4.6.8
 * @return string default language name of term $nm in langulage $lang
 * @param string $lang two-letter language code to search for $nm
 * @param string $nm name of term in language $lang
 * @param string $taxonomy
 */
function qtranxf_find_term($lang, $term, $taxonomy=null) {
	global $q_config;
	if($lang != $q_config['default_language']){
		foreach($q_config['term_name'] as $nm => $ts){
			if(empty($ts[$lang])) continue;
			if( $ts[$lang] == $term ) return $nm;
		}
	}
	return $term;
}

/*
 * @since 3.4.6.8
 * @return string default language name of term $nm in langulage $lang
 * @param string $lang two-letter language code to search for $nm
 * @param string $nm name of term in language $lang
 * @param string $taxonomy
 *
function qtranxf_find_term_like($lang, $s, $taxonomy=null) {
	global $q_config;
	if($lang != $q_config['default_language']){
		foreach($q_config['term_name'] as $nm => $ts){
			if(empty($ts[$lang])) continue;
			if(function_exists('mb_stripos'))
				$p = stripos($ts[$lang],$s);
			else
				$p = stripos($ts[$lang],$s);
			if( $p !== false) return $nm;
		}
	}
	return $s;
} */

function qtranxf_get_terms_joined($terms, $taxonomy=null, $args=null) {
	global $q_config;
	if(is_array($terms)){
		// handle arrays recursively
		foreach($terms as $key => $term) {
			$terms[$key] = qtranxf_get_terms_joined($term,$taxonomy);
		}
	}else{
		$terms = qtranxf_get_term_joined($terms,$taxonomy);
	}
	return $terms;
}

function qtranxf_useAdminTermLibJoin($obj, $taxonomies=null, $args=null) {
	global $pagenow;
	//qtranxf_dbg_echo('qtranxf_useAdminTermLibJoin: $pagenow='.$pagenow);
	//qtranxf_dbg_echo('qtranxf_useAdminTermLibJoin: $obj:',$obj);
	//qtranxf_dbg_echo('qtranxf_useAdminTermLibJoin: $taxonomies:',$taxonomies);
	//qtranxf_dbg_echo('qtranxf_useAdminTermLibJoin: $args:',$args);
	switch($pagenow){
		case 'nav-menus.php':
		case 'edit-tags.php':
		case 'term.php':
		case 'edit.php':
			return qtranxf_get_terms_joined($obj);
		default: return qtranxf_useTermLib($obj);
	}
}
add_filter('get_term', 'qtranxf_useAdminTermLibJoin', 5, 2);
add_filter('get_terms', 'qtranxf_useAdminTermLibJoin', 5, 3);

/*
 * @since 3.4.6.8
 */
function qtranxf_admin_term_name($value, $term_id, $taxonomy = null, $context = null){
	global $pagenow;
	if( !empty($context) && $pagenow == 'edit.php' )
	switch($context){
		case 'display': return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
	}
	return $value;
}
add_filter('term_name', 'qtranxf_admin_term_name', 5, 4);//used in function sanitize_term_field

//does someone use it?
function qtranxf_useAdminTermLib($obj) {
	//qtranxf_dbg_echo('qtranxf_useAdminTermLib: $obj: ',$obj,true);
	if ($script_name==='/wp-admin/term.php' || ($script_name==='/wp-admin/edit-tags.php' &&
		strstr($_SERVER['QUERY_STRING'], 'action=edit' )!==FALSE))
	{
		return $obj;
	}
	else
	{
		return qtranxf_useTermLib($obj);
	}
}
//add_filter('get_term', 'qtranxf_useAdminTermLib',0);
//add_filter('get_terms', 'qtranxf_useAdminTermLib',0);


function qtranxf_updateTermLibrary() {
	global $q_config;
	if(!isset($_POST['action'])) return;
	switch($_POST['action']) {
		case 'editedtag':
		case 'addtag':
		case 'editedcat':
		case 'addcat':
		case 'add-cat':
		case 'add-tag':
		case 'add-link-cat':
			if(isset($_POST['qtrans_term_'.$q_config['default_language']]) && $_POST['qtrans_term_'.$q_config['default_language']]!='') {
				$default = htmlspecialchars(qtranxf_stripSlashesIfNecessary($_POST['qtrans_term_'.$q_config['default_language']]), ENT_NOQUOTES);
				if(!isset($q_config['term_name'][$default]) || !is_array($q_config['term_name'][$default])) $q_config['term_name'][$default] = array();
				foreach($q_config['enabled_languages'] as $lang) {
					$_POST['qtrans_term_'.$lang] = qtranxf_stripSlashesIfNecessary($_POST['qtrans_term_'.$lang]);
					if($_POST['qtrans_term_'.$lang]!='') {
						$q_config['term_name'][$default][$lang] = htmlspecialchars($_POST['qtrans_term_'.$lang], ENT_NOQUOTES);
					} else {
						$q_config['term_name'][$default][$lang] = $default;
					}
				}
				update_option('qtranslate_term_name',$q_config['term_name']);
			}
		break;
	}
}

function qtranxf_stripSlashesIfNecessary($str) {
	/**
	 * @since 3.2.9.8.4 WordPress now always supplies slashed data
	 */
	//if(1==get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	//}
	return $str;
}

function qtranxf_updateTermLibraryJoin() {
	global $q_config;
	if(!isset($_POST['action'])) return;
	$action=$_POST['action'];
	if(!isset($_POST['qtrans_term_field_name'])) return;
	$field=$_POST['qtrans_term_field_name'];
	$default_name_original=$_POST['qtrans_term_field_default_name'];
	//qtranxf_dbg_log('$_POST:',$_POST);
	$field_value = qtranxf_stripSlashesIfNecessary($_POST[$field]);
	//qtranxf_dbg_log('$field_value='.$field_value);
	$names=qtranxf_split($field_value);
	//qtranxf_dbg_log('names=',$names);
	$default_name=htmlspecialchars($names[$q_config['default_language']], ENT_NOQUOTES);
	$_POST[$field]=$default_name;
	if(empty($default_name))
		return;//will generate error later from WP
	foreach($names as $lang => $name){
		$q_config['term_name'][$default_name_original][$lang] = htmlspecialchars($name, ENT_NOQUOTES);
	}
	if($default_name_original != $default_name){
		$q_config['term_name'][$default_name]=$q_config['term_name'][$default_name_original];
		unset($q_config['term_name'][$default_name_original]);
	}
	update_option('qtranslate_term_name',$q_config['term_name']);
}

function qtranxf_updateTranslations($type) {
	global $q_config;
	if(!isset($_POST[$type])) return;
}

/*
function qtranxf_edit_terms($term_id, $taxonomy){
	//qtranxf_dbg_log('qtranxf_edit_terms: $name='.$name);
}
add_action('edit_terms','qtranxf_edit_terms');

//function qtranxf_gettext($translated_text, $text, $domain) {
function qtranxf_gettext($translated_text) {
	//same as qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage
	$blocks = qtranxf_get_language_blocks($translated_text);
	if(count($blocks)<=1)//no language is encoded in the $text, the most frequent case
		return $translated_text;
	global $q_config;
	//qtranxf_dbg_log('qtranxf_gettext: $translated_text=',$translated_text,true);
	return $translated_text;
	//return qtranxf_use_block($q_config['language'], $blocks);
	//return qtranxf_use($q_config['language'], $translated_text, false);
}

//function qtranxf_gettext_with_context($translated_text, $text, $context, $domain) {
function qtranxf_gettext_with_context($translated_text) {
	return qtranxf_gettext($translated_text);
}
add_filter('gettext', 'qtranxf_gettext',0);
add_filter('gettext_with_context', 'qtranxf_gettext_with_context',0);
*/

function qtranxf_getLanguageEdit() {
	global $q_config;
	return isset($_COOKIE['qtrans_edit_language']) ? $_COOKIE['qtrans_edit_language'] : $q_config['language'];
}

/*
function qtranxf_language_columns($columns) {
	return array(
		'code' => _x('Code', 'Two-letter Language Code meant.', 'qtranslate'),
		'flag' => __('Flag', 'qtranslate'),
		'name' => __('Name', 'qtranslate'),
		'status' => __('Action', 'qtranslate'),
		'status2' => __('Edit', 'qtranslate'),
		'status3' => __('Stored', 'qtranslate')
	);
}
add_filter('manage_language_columns', 'qtranxf_language_columns');
*/

function qtranxf_languageColumnHeader($columns){
	$new_columns = array();
	if(isset($columns['cb'])) $new_columns['cb'] = '';
	if(isset($columns['title'])) $new_columns['title'] = '';
	if(isset($columns['author'])) $new_columns['author'] = '';
	if(isset($columns['categories'])) $new_columns['categories'] = '';
	if(isset($columns['tags'])) $new_columns['tags'] = '';
	$new_columns['language'] = __('Languages', 'qtranslate');
	return array_merge($new_columns, $columns);
}

function qtranxf_languageColumn($column) {
	global $q_config, $post;
	if ($column == 'language') {
		$missing_languages = null;
		$available_languages = qtranxf_getAvailableLanguages($post->post_content);
		if($available_languages === FALSE){
			echo _x('Languages are not set', 'Appears in the column "Languages" on post listing pages, when content has no language tags yet.', 'qtranslate');
		}else{
			$missing_languages = array_diff($q_config['enabled_languages'], $available_languages);
			$available_languages_name = array();
			$language_names = null;
			foreach($available_languages as $language) {
				if(isset($q_config['language_name'][$language])){
					$language_name = $q_config['language_name'][$language];
				}else{
					if(!$language_names) $language_names = qtranxf_default_language_name();
					$language_name = isset($language_names[$language]) ? $language_names[$language] : __('Unknown Language', 'qtranslate');
					$language_name .= ' ('.__('Not enabled', 'qtranslate').')';
				}
				$available_languages_name[] = $language_name;
			}
			$available_languages_names = join(', ', $available_languages_name);
			echo apply_filters('qtranslate_available_languages_names',$available_languages_names);
		}
		do_action('qtranslate_languageColumn', $available_languages, $missing_languages);
	}
	return $column;
}

function qtranxf_fetch_file_selection($dir,$suffix='.css'){
	//qtranxf_dbg_log('qtranxf_fetch_file_selection: dir:',$dir);
	$files = array();
	$dir_handle = @opendir($dir);
	if(!$dir_handle) return false;
	while (false !== ($file = readdir($dir_handle))) {
		if(!qtranxf_endsWith($file,$suffix)) continue;
		$nm = basename($file, $suffix);
		if(!$nm) continue;
		$nm = str_replace('_',' ',$nm);
		if(qtranxf_endsWith($nm,'.min')){
			$nm = substr($nm,-4);
			$files[$nm] = $file;
		}elseif(!isset($files[$nm])){
			$files[$nm] = $file;
		}
	}
	ksort($files);
	//qtranxf_dbg_log('qtranxf_fetch_file_selection: files:',$files);
	return $files;
}

/*
 * former qtranxf_fixAdminBar($wp_admin_bar)
 */
function qtranxf_before_admin_bar_render() {
	global $wp_admin_bar, $q_config;
	if(!isset($wp_admin_bar)) return;
	$nodes = $wp_admin_bar->get_nodes();
	//qtranxf_dbg_log('qtranxf_before_admin_bar_render: $nodes:', $nodes);
	if(!isset($nodes)) return;//sometimes $nodes is NULL
	$lang = $q_config['language'];
	foreach($nodes as $node) {
		//$nd = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($node);
		$nd = qtranxf_use($lang,$node);
		$wp_admin_bar->add_node($nd);
	}
	//qtranxf_dbg_log('qtranxf_before_admin_bar_render: $wp_admin_bar:', $wp_admin_bar);
}

//function qtranxf_after_admin_bar_render() {
//	global $wp_admin_bar;
//}

function qtranxf_admin_list_cats($text) {
	global $pagenow;
	//qtranxf_dbg_echo('qtranxf_admin_list_cats: $text',$text);
	switch($pagenow){
		case 'edit-tags.php':
		case 'term.php':
			//replace [:] with <:>
			$blocks = qtranxf_get_language_blocks($text);
			if(count($blocks)<=1) return $text;
			$texts = qtranxf_split_blocks($blocks);
			//$text = qtranxf_join_c($texts);
			$text = qtranxf_join_b($texts);//with closing tag
			return $text;
		default: return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($text);
	}
}
add_filter('list_cats', 'qtranxf_admin_list_cats',0);

function qtranxf_admin_dropdown_cats($text) {
	global $pagenow;
	//qtranxf_dbg_echo('qtranxf_admin_list_cats: $text',$text);
	switch($pagenow){
		case 'edit-tags.php':
		case 'term.php':
			return $text;
		default: return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($text);
	}
}
add_filter('wp_dropdown_cats', 'qtranxf_admin_dropdown_cats',0);

function qtranxf_admin_category_description($text) {
	global $pagenow;
	switch($pagenow){
		case 'term.php':
		case 'edit-tags.php':
			return $text;
		default: return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($text);
	}
}
add_filter('category_description', 'qtranxf_admin_category_description',0);

function qtranxf_admin_the_title($title) {
	global $pagenow;
	//todo this filter should not be used in admin area at all?
	if(defined('DOING_AJAX') && DOING_AJAX)//nav-menus.php#752
		return $title;
	global $pagenow;
	switch($pagenow){
		//case 'term.php':
		//case 'edit-tags.php':
		case 'nav-menus.php':
			return $title;
		default: return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
	}
}
add_filter('the_title', 'qtranxf_admin_the_title', 0);//WP: fires for display purposes only

//filter added in qtranslate_hooks.php
if(!function_exists('qtranxf_trim_words')){
function qtranxf_trim_words( $text, $num_words, $more, $original_text ) {
	global $q_config;
	//qtranxf_dbg_log('qtranxf_trim_words: $text: ',$text);
	//qtranxf_dbg_log('qtranxf_trim_words: $original_text: ',$original_text);
	$blocks = qtranxf_get_language_blocks($original_text);
	//qtranxf_dbg_log('qtranxf_trim_words: $blocks: ',$blocks);
	if ( count($blocks) <= 1 )
		return $text;
	$lang = $q_config['language'];
	$texts = qtranxf_split_blocks($blocks);
	foreach($texts as $key => $txt){
		$texts[$key] = wp_trim_words($txt, $num_words, $more);
	}
	return qtranxf_join_b($texts);//has to be 'b', because 'c' gets stripped in /wp-admin/includes/nav-menu.php:182: esc_html( $item->description )
}
}

/**
 * The same as core wp_htmledit_pre in /wp-includes/formatting.php,
 * but with last argument of htmlspecialchars $double_encode off,
 * which makes it to survive multiple applications from other plugins,
 * for example, "PS Disable Auto Formatting" (https://wordpress.org/plugins/ps-disable-auto-formatting/)
 * cited on support thread https://wordpress.org/support/topic/incompatibility-with-ps-disable-auto-formatting.
 * @since 2.9.8.9
*/
if(!function_exists('qtranxf_htmledit_pre')){
function qtranxf_htmledit_pre($output) {
	if ( !empty($output) )
		$output = htmlspecialchars($output, ENT_NOQUOTES, get_option( 'blog_charset' ), false ); // convert only < > &
	return apply_filters( 'htmledit_pre', $output );
}
}

function qtranxf_the_editor($editor_div)
{
	// remove wpautop, which causes unmatched <p> on combined language strings
	if('html' != wp_default_editor()) {
		remove_filter('the_editor_content', 'wp_richedit_pre');
		add_filter('the_editor_content', 'qtranxf_htmledit_pre', 99);
	}
	return $editor_div;
}

/* @since 3.3.8.7 use filter 'admin_title' instead
function qtranxf_filter_options_general($value){
	global $q_config;
	global $pagenow;
	switch($pagenow){
		case 'options-general.php':
		case 'customize.php'://there is more work to do for this case
			return $value;
		default: break;
	}
	$lang = $q_config['language'];
	return qtranxf_use_language($lang,$value,false,false);
}
add_filter('option_blogname', 'qtranxf_filter_options_general');
add_filter('option_blogdescription', 'qtranxf_filter_options_general');
*/

function qtranxf_updateGettextDatabases($force = false, $only_for_language = '') {
	require_once(QTRANSLATE_DIR.'/admin/qtx_update_gettext_db.php');
	return qtranxf_updateGettextDatabasesEx($force, $only_for_language);
}

/* this did not work, need more investigation
function qtranxf_enable_blog_title_filters($name)
{
	add_filter('option_blogname', 'qtranxf_filter_options_general');
	add_filter('option_blogdescription', 'qtranxf_filter_options_general');
}
add_action( 'get_header', 'qtranxf_enable_blog_title_filters' );

function qtranxf_disable_blog_title_filters($name)
{
	remove_filter('option_blogname', 'qtranxf_filter_options_general');
	remove_filter('option_blogdescription', 'qtranxf_filter_options_general');
}
add_action( 'wp_head', 'qtranxf_disable_blog_title_filters' );
*/

function qtranxf_add_conf_filters(){
	global $q_config;
	switch($q_config['editor_mode']){
		case QTX_EDITOR_MODE_SINGLGE:
		case QTX_EDITOR_MODE_RAW:
			add_filter('gettext', 'qtranxf_gettext',0);
			add_filter('gettext_with_context', 'qtranxf_gettext_with_context',0);
			add_filter('ngettext', 'qtranxf_ngettext',0);
		break;
		case QTX_EDITOR_MODE_LSB:
		default:
			//applied in /wp-includes/class-wp-editor.php
			add_filter('the_editor', 'qtranxf_the_editor');
		break;
	}
}

function qtranxf_del_conf_filters(){
	global $q_config;
	remove_filter('gettext', 'qtranxf_gettext',0);
	remove_filter('gettext_with_context', 'qtranxf_gettext_with_context',0);
	remove_filter('ngettext', 'qtranxf_ngettext',0);
	remove_filter('the_editor', 'qtranxf_the_editor');
}

/**
 * Get the currently selected admin color scheme (to be used for generated CSS)
 * @return array
 */
function qtranxf_get_user_admin_color() {
	global $_wp_admin_css_colors;
	$user_id = get_current_user_id();
	$user_admin_color = get_user_meta( $user_id, 'admin_color', true );
	if(!$user_admin_color){ //ajax calls do not have user authenticated?
		$user_admin_color = 'fresh';
	}
	return $_wp_admin_css_colors[$user_admin_color]->colors;
}


function qtranxf_meta_box_LSB()
{
	/*
	global $q_config;
	$flag_location=qtranxf_flag_location();
	$lsb = '<ul class="'.$q_config['lsb_style_wrap_class'].' qtranxs-meta-box-lsb">';
	foreach($q_config['enabled_languages'] as $lang){
		$lsb .= '<li lang="'.$lang.'" class="qtranxs-lang-switch" onclick="qTranslateConfig.qtx.switchActiveLanguage"><img src="'.$flag_location.$q_config['flag'][$lang].'"><span>'.$q_config['language_name'][$lang].'</span></li>';
	}
	$lsb .= '</ul>';
	echo $lsb;
	*/
	printf(__('This is a set of "%s" from %s. Click any blank space between the buttons and drag it to a place where you would need it the most. Click the handle at the top-right corner of this widget to hide this message.', 'qtranslate'), __('Language Switching Buttons','qtranslate'), '<a href="https://wordpress.org/plugins/qtranslate-x/" target="_blank">qTranslate&#8209;X</a>');
}

function qtranxf_add_meta_box_LSB($post_type, $post)
{
	global $q_config, $pagenow;
	if( $q_config['editor_mode'] != QTX_EDITOR_MODE_LSB) return;
	switch($pagenow){
		case 'post-new.php':
		case 'post.php': break;
		default: return;
	}
	if(empty($post_type)) if(isset($post->post_type)) $post_type = $post->post_type; else return;
	//qtranxf_dbg_log('qtranxf_add_meta_box_LSB: $post_type: ', $post_type);//, true);
	$page_config = qtranxf_get_admin_page_config_post_type($post_type);
	if(empty($page_config)) return;
	add_meta_box( 'qtranxs-meta-box-lsb', __('Language', 'qtranslate'), 'qtranxf_meta_box_LSB', $post_type, 'normal', 'low');
}
add_action( 'add_meta_boxes', 'qtranxf_add_meta_box_LSB', 10, 2 );

/**
 * @since 3.3
 * @return true if post type is listed in option 'Post Types'.
 */
function qtranxf_post_type_optional($post_type) {
	switch($post_type){
		case 'revision':
		case 'nav_menu_item':
			return false; //no option for this type
		default: return true;
	}
}

function qtranxf_json_encode($o){
	if(version_compare(PHP_VERSION, '5.4.0') >= 0)
		return json_encode($o,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	return json_encode($o);
}

/**
 * @since 3.4
 * return reference to $page_config['forms'][$nm]['fields']
 */
function qtranxf_config_add_form( &$page_config, $nm){
	if(!isset($page_config['forms'][$nm])) $page_config['forms'][$nm] = array('fields' => array());
	else if(!isset($page_config['forms'][$nm]['fields'])) $page_config['forms'][$nm]['fields'] = array();
}

/**
 * @since 3.4.5
 * check the WP Nonce - OK if POST is empty
 * @link https://codex.wordpress.org/Function_Reference/wp_nonce_field#Examples
 * @param  string $nonce_name  Name specified when generating the nonce
 * @param  string $nonce_field Form input name for the nonce
 * @return boolean             True if the nonce is ok
 */
function qtranxf_verify_nonce($nonce_name, $nonce_field = '_wpnonce') {
	return empty( $_POST ) || check_admin_referer( $nonce_name, $nonce_field );
}

/**
 * @since 3.4.6.5
 */
function qtranxf_decode_name_value_pair(&$a,$nam,$val) {
	if(preg_match( '#([^\[]*)\[([^\]]+)\](.*)#', $nam, $matches )) {
		$n = $matches[1];
		$k = $matches[2];
		$s = $matches[3];
		if(is_numeric($n)) $n = (int)$n;
		if(is_numeric($k)) $k = (int)$k;
		if(empty($a[$n])) $a[$n] = array();
		if(empty($s)){
			$a[$n][$k] = $val;
		}else{
			qtranxf_decode_name_value_pair($a[$n],$k.$s,$val);//recursive call
		}
	}else{
		$a[$nam] = $val;
	}
}

/**
 * @since 3.4.6.5
 */
function qtranxf_decode_name_value($data) {
	$a = array();
	foreach ( $data as $nv ) {
		qtranxf_decode_name_value_pair($a,$nv->name,wp_slash($nv->value));
/*
		if ( preg_match( '#(.*)\[(\w+)\]#', $nv->name, $matches ) ) {
			$nm = $matches[1];
			if ( empty( $a[ $nm ] ) ) {
				$a[ $nm ] = array();
			}
			$key = $matches[2];
			if ( is_numeric( $key ) ) {
				$key = (int) $key;
			}
			$a[ $nm ][ $key ] = wp_slash( $nv->value );
		} else {
			$a[ $nv->name ] = wp_slash( $nv->value );
		}
*/
	}
	return $a;
}

add_filter('manage_posts_columns', 'qtranxf_languageColumnHeader');
add_filter('manage_posts_custom_column', 'qtranxf_languageColumn');
add_filter('manage_pages_columns', 'qtranxf_languageColumnHeader');
add_filter('manage_pages_custom_column', 'qtranxf_languageColumn');
