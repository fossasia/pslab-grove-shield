<?php
if ( !defined( 'ABSPATH' ) ) exit;

add_filter('wp_translator', 'QTX_Translator::get_translator');

function qtranxf_get_front_page_config() {
	static $page_configs;//cache
	if($page_configs) return $page_configs;

	global $q_config;
	$url_path = $q_config['url_info']['wp-path'];
	$url_query = isset($q_config['url_info']['query']) ? $q_config['url_info']['query'] : '';

	$front_config = $q_config['front_config'];
	/**
	 * Customize the front configuration for all pages.
	 * @param (array) $front_config token 'front-config' of the configuration.
	 */
	$front_config = apply_filters('i18n_front_config', $front_config);
	//qtranxf_dbg_log('qtranxf_get_front_page_config: $front_config: ', json_encode($front_config,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

	$page_configs = qtranxf_parse_page_config($front_config, $url_path, $url_query);
	/*
	 * Customize the $page_config for this front request.
	 * @param (array) $page_config 'front_config', filtered for the current page.
	 * @param (string) $url_path URL path without 'Site Address (URL)'.
	 * @param (string) $url_query query part of URL without '?', sanitized version of $_SERVER['QUERY_STRING'].
	 * @param (string) $post_type type of post serving on the current page, or null if not applicable.
	 */
	//$page_config = apply_filters('i18n_front_page_config', $page_config, $url_path, $url_query, $post_type);
	//qtranxf_dbg_log('qtranxf_get_front_page_config: $url_path='.$url_path.'; $url_query='.$url_query.'; $post_type='.$post_type.'; $page_config: ', json_encode($page_config,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
	//if(isset($q_config['i18n-log-dir'])) qtranxf_write_config_log($page_configs[''], '', $url_path, $url_query);
	return $page_configs;
}

/**
 * Response to action 'init', which runs after user is authenticated
 * @since 3.3.2
 */
//function qtranxf_front_init(){
//	global $q_config;
//}
//add_action('init','qtranxf_front_init');

function qtranxf_wp_head(){
	global $q_config;

	if( $q_config['header_css_on'] ){
		echo '<style type="text/css">' . PHP_EOL .$q_config['header_css'].'</style>'. PHP_EOL;
	}
	do_action('qtranslate_head_add_css');//not really needed?

	// skip the rest if 404
	if(is_404()) return;

	// set links to translations of current page
	foreach($q_config['enabled_languages'] as $lang) {
		if(!empty($q_config['locale_html'][$lang])){
			$hreflang = $q_config['locale_html'][$lang];
		}else{
			$hreflang = $lang;
		}
		//if($language != qtranxf_getLanguage())//standard requires them all
		echo '<link hreflang="'.$hreflang.'" href="'.qtranxf_convertURL('',$lang,false,true).'" rel="alternate" />'.PHP_EOL;
	}
	//https://support.google.com/webmasters/answer/189077
	echo '<link hreflang="x-default" href="'.qtranxf_convertURL('',$q_config['default_language']).'" rel="alternate" />'.PHP_EOL;

	//qtranxf_add_css();// since 3.2.5 no longer needed
}
add_action('wp_head', 'qtranxf_wp_head');

/**
 * Moved line '<meta name="generator"' to a separate action.
 * Developers may use code
 *   remove_action('wp_head','qtranxf_wp_head_meta_generator');
 * to remove this line from the header.
 * @since 3.4.5.4
*/
function qtranxf_wp_head_meta_generator(){
	echo '<meta name="generator" content="qTranslate-X '.QTX_VERSION.'" />'.PHP_EOL;
}
add_action('wp_head', 'qtranxf_wp_head_meta_generator');

function qtranxf_wp_get_nav_menu_items( $items, $menu, $args ){
	global $q_config;
	$language = $q_config['language'];
	$itemid = 0;
	$menu_order = 0;
	$itemsremoved = array();
	$qtransmenus = array();
	//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: count(items)='.count($items).'; args: ', $args);//,true);
	//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: count(items)=',count($items));
	foreach($items as $key => $item)
	{
		if(isset($item->item_lang)) continue;
		if(isset($itemsremoved[$item->menu_item_parent])){
			$itemsremoved[$item->ID] = $item;
			unset($items[$key]);//remove a child of removed item
			continue;
		}
		$item->item_lang = $language;
		//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: $item->url: ',$item->url);
		//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: $item: ',$item);
		//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: item: '.$item->title.'; p='.$item->menu_item_parent.'; ID='.$item->ID);
		$qtransLangSw = isset( $item->url ) && stristr( $item->url, 'qtransLangSw' ) !== FALSE;
		if(!$qtransLangSw){
			$item_title = $item->title;
			if(!empty($item_title)){
				if(empty($item->post_title) && !qtranxf_isMultilingual($item_title)){
					//$item does not have custom menu title, then it fetches information from post_title, but it is already translated with ShowEmpty=false, which gives either valid translation or possibly something like "(English) English Text". We need to translate again and to skip menu item if translation does not exist.
					switch($item->type){
						case 'post_type':
							$p = get_post($item->object_id);
							if($p){
								$post_title_ml = isset($p->post_title_ml) ? $p->post_title_ml : $p->post_title;
								$item_title=qtranxf_use_language($language, $post_title_ml, false, true);
							}
						break;
						case 'taxonomy':
							//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: taxonomy: $item: ',$item);
							$term = wp_cache_get( $item->object_id, $item->object );
							if($term){
								//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: $term: ',$term);
								if(isset($q_config['term_name'][$term->name][$language])){
									$item_title = $q_config['term_name'][$term->name][$language];
								}else{
									$item_title = '';
								}
								if(!empty($term->description)){
									$item->description = $term->description;
								}
							}
						break;
					}
				}else{
					//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: $item->title && $item->post_title: $item: ',$item);
					$item_title=qtranxf_use_language($language, $item_title, false, true);
				}
			}
			//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: $item_title: ',$item_title);
			if(empty($item_title)){
				//qtranxf_dbg_log('removed item: '.$item->title.'; p='.$item->menu_item_parent);
				$itemsremoved[$item->ID] = $item;
				unset($items[$key]);//remove menu item with empty title for this language
				continue;
			}
			$item->title = $item_title;
			if($item->object == 'custom' && !empty($item->url)){
				if(strpos($item->url,'setlang=no')===FALSE){
					$item->url = qtranxf_convertURL($item->url,$language);
				}else{
					$item->url = remove_query_arg('setlang',$item->url);
				}
				$i = strpos($item->url,'#?lang=');
				if($i !== FALSE){
					$lang = substr($item->url,$i+7,2);
					$item->url = qtranxf_convertURL('', $lang, false, true);
					$item->item_lang = $lang;
				}
			}
		}

		$item->post_content=qtranxf_use_language($language, $item->post_content, false, true);
		$item->post_title=qtranxf_use_language($language, $item->post_title, false, true);
		$item->post_excerpt=qtranxf_use_language($language, $item->post_excerpt, false, true);
		$item->description=qtranxf_use_language($language, $item->description, false, true);
		if(isset($item->attr_title)) $item->attr_title=qtranxf_use_language($language, $item->attr_title, false, true);

		if($itemid<$item->ID) $itemid=$item->ID;
		if($menu_order<$item->menu_order) $menu_order=$item->menu_order;
		if(!$qtransLangSw) continue;
		$qtransmenus[$key] = $item;
	}

	//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: $itemsremoved: ',$itemsremoved);
	if( !empty($itemsremoved) ) qtranxf_remove_detached_children($items,$itemsremoved);

	if(!empty($qtransmenus)){
		foreach($qtransmenus as $key => $item){
			$nlang = count($items);
			qtranxf_add_language_menu_item( $items, $menu_order, $itemid, $key, $language );
			$nlang = count($items) - $nlang;
			$menu->count += $nlang;
			$menu_order += $nlang;
		}
	}
	//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: done: $items: ',$items);
	return $items;
}
add_filter( 'wp_get_nav_menu_items',  'qtranxf_wp_get_nav_menu_items', 20, 3 );

function qtranxf_add_language_menu_item(&$items, &$menu_order, &$itemid, $key, $language ) {
	global $q_config;
	//qtranxf_dbg_log('qtranxf_add_language_menu_item: $key: ',$key);
	$item = $items[$key];
	$flag_location = qtranxf_flag_location();
	$altlang = null;
	$url = '';
	//options
	$type='LM';//[LM|AL]
	$title='Language';//[none|Language|Current]
	$current=true;//[shown|hidden]
	$flags=true;//[none|all|items]
	$lang_names=true;//names=[shown|hidden]
	$colon=true;//[shown|hidden]
	$topflag=true;

	$p=strpos($item->url,'?');
	if($p!==FALSE){
		$qs=substr($item->url,$p+1);
		$qs=str_replace('#','',$qs);
		$pars=array(); parse_str($qs,$pars);
		if(isset($pars['type']) && stripos($pars['type'],'AL')!==FALSE ) $type='AL';
		if(isset($pars['flags'])){
			$flags=(stripos($pars['flags'],'no')===FALSE);
			if($flags) $topflag=(stripos($pars['flags'],'items')===FALSE);
			else $topflag=false;
		}
		if(isset($pars['names'])){
			$lang_names = (stripos($pars['names'],'hid')===FALSE);
		}
		if(isset($pars['title'])){
			$title=$pars['title'];
			if(stripos($pars['title'],'no')!==FALSE) $title='';
			if(!$topflag && empty($title)) $title='Language';
		}
		if(isset($pars['colon'])){
			$colon = (stripos($pars['colon'],'hid')===FALSE);
		}
		if(isset($pars['current'])){
			$current=(stripos($pars['current'],'hid')===FALSE);
		}
		if( !$lang_names && !$flags ){
			$flags = true;
		}
	}
	if($type=='AL'){
		foreach($q_config['enabled_languages'] as $lang){
			if($lang==$language) continue;
			$toplang=$lang;
			$altlang=$lang;
			break;
		}
		$item->title=empty($title)?'':$q_config['language_name'][$toplang];
		$item->url=qtranxf_convertURL($url, $altlang, false, true);
	}else{
		$toplang=$language;
		if(empty($title)){
			$item->title='';
		}elseif(stripos($title,'Current')!==FALSE){
			$item->title=$q_config['language_name'][$toplang];
		}else{
			$blocks = qtranxf_get_language_blocks($item->title);
			if(count($blocks)<=1){//no customization is done
				$item->title = qtranxf_translate('Language');
			}else{
				$item->title = qtranxf_use_block($language, $blocks);
			}
		}
		$item->url='#';//qtranxf_convertURL($url, $language, false, true);
	}
	if($topflag){
		if(!empty($item->title)){
			if($colon) $item->title.=_x(':', 'Colon after a title. For example, in top item of Language Menu.', 'qtranslate');
			$item->title.='&nbsp;';
		}
		$item->title.='<img src="'.$flag_location.$q_config['flag'][$toplang].'" alt="'.$q_config['language_name'][$toplang].'" />';//.' '.__('Flag', 'qtranslate')
	}
	if(empty($item->attr_title))
		$item->attr_title = $q_config['language_name'][$toplang];
	//$item->classes[] = 'qtranxs_flag_'.$language;
	$item->classes[] = 'qtranxs-lang-menu';
	$item->classes[] = 'qtranxs-lang-menu-'.$toplang;
	//qtranxf_dbg_log('qtranxf_wp_get_nav_menu_items: top $item: ',$item);
	$qtransmenu = $item;

	//find children in case this function was already applied (customize.php on menu change)
	foreach($items as $k => $item)
	{
		if($item->menu_item_parent != $qtransmenu->ID ) continue;
		unset($items[$k]);
	}

	foreach($q_config['enabled_languages'] as $lang)
	{
		if($type=='AL'){
			if($lang==$language) continue;
			if($lang==$altlang ) continue;
		}elseif(!$current){
			if($lang==$language) continue;
		}
		$item=new WP_Post((object)array('ID' => ++$itemid));

		//add properties required for nav_menu_item, whose absense causes class-wp-customize-setting.php to throw Exception in function __construct
		//$item->db_id=$item->ID;
		//$item->url='';//gets assigned later
		$item->target = '';
		$item->description = '';
		$item->xfn = '';

		//set properties for language menu item
		$item->menu_item_parent=$qtransmenu->ID;
		$item->menu_order=++$menu_order;
		$item->post_type='nav_menu_item';
		$item->object='custom';
		$item->object_id=$qtransmenu->object_id;
		$item->type='custom';
		$item->type_label='Custom';
		$item->title='';
		if($flags){
			$item->title='<img src="'.$flag_location.$q_config['flag'][$lang].'" alt="'.$q_config['language_name'][$lang].'" />';
		}
		if($lang_names){
			if($flags) $item->title .= '&nbsp;';
			$item->title .= $q_config['language_name'][$lang];
		}
		$item->post_title=$item->title;
		$item->post_name='language-menuitem-'.$lang;
		//if($lang!=$language){ menu url should not be empty
			$item->url=qtranxf_convertURL($url, $lang, false, true);
			$item->url=esc_url($item->url);//not sure if this is needed
		//}
		$item->attr_title = $q_config['language_name'][$lang];
		$item->classes=array();
		//$item->classes[] = 'qtranxs_flag_'.$lang;
		$item->classes[] = 'qtranxs-lang-menu-item';
		$item->classes[] = 'qtranxs-lang-menu-item-'.$lang;
		//qtx specific properties
		$item->item_lang = $lang;//to store the language assigned
		$items[]=$item;
		//qtranxf_dbg_log('qtranxf_add_language_menu_item: language menu $item',$item);
	}
}

function qtranxf_remove_detached_children(&$items,&$itemsremoved)
{
	do{
		$more=false;
		foreach($items as $key => $item){
			if($item->menu_item_parent==0) continue;
			if(!isset($itemsremoved[$item->menu_item_parent])) continue;
			$itemsremoved[$item->ID] = $item;
			unset($items[$key]);
			$more=true;
			//qtranxf_dbg_log('qtranxf_remove_detached_children: removed: $key='.$key.'; $item: ',$item);
		}
	}while($more);
}

/*
function qtranxf_wp_setup_nav_menu_item($menu_item) {
	global $q_config;
	if($menu_item->title==='test'){
		//echo "qtranxf_wp_setup_nav_menu_item: '$text'<br/>\n";
		//qtranxf_dbg_echo('menu_item:',$menu_item,true);
		//qtranxf_dbg_echo('menu_item->title:'.$menu_item->title);
		//$menu_item->title='test';//is in use
		//$menu_item->post_title='';//not in use in menu
		//$menu_item->title='';
		//unset($menu_item);
	}
	//return $menu_item;
	return qtranxf_use($q_config['language'], $menu_item, false, true);
	//return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($menu_item);
}
add_filter('wp_setup_nav_menu_item', 'qtranxf_wp_setup_nav_menu_item');
*/

/**
 * @since 3.3.8.9
 * @param (mixed) $value to translate, which may be array, object or string
 *                and may have serialized parts with embedded multilingual values.
 */
function qtranxf_translate_deep($value,$lang){
	if(is_string($value)){
		if(!qtranxf_isMultilingual($value))
			return $value; //most frequent case
		if(is_serialized( $value )){
			$value = unserialize($value);
			$value = qtranxf_translate_deep($value,$lang);//recursive call
			return serialize($value);
		}
		$lang_value =  qtranxf_use_language($lang,$value);
		return $lang_value;
	}else if(is_array($value)){
		foreach($value as $k => $v){
			$value[$k] = qtranxf_translate_deep($v,$lang);
		}
	}else if(is_object($value) || $value instanceof __PHP_Incomplete_Class){
		foreach(get_object_vars($value) as $k => $v) {
			if(!isset($value->$k)) continue;
			$value->$k = qtranxf_translate_deep($v,$lang);
		}
	}
	return $value;
}

/**
 * @since 3.3.8.9
 * Used to filter option values
 */
function qtranxf_translate_option($value, $lang=null){
	global $q_config;
	if(!$lang) $lang = $q_config['language'];
	return qtranxf_translate_deep($value,$lang);
}

/*
function qtranxf_split_languages_option($value, $nm, $lang){
	$value_cached = wp_cache_get($nm, 'qtranxc_options');
	if(isset($value_cached[0])) return $value_cached[0];//no translation needed
	//global $q_config; if(!$lang) $lang = $q_config['language'];
	if(isset($value_cached[$lang])) return $value_cached[$lang];

	if(qtranxf_is_multilingual_deep($value)){
		$s = is_serialized( $value );
		if($s) $value = unserialize($value);
		$values = qtranxf_split_languages($value);
		if($s) foreach($values as $lng => $v){
			$values[$lng] = serialize($v);
		}
		wp_cache_add($nm, $values, 'qtranxc_options');
		return $values[$lang];
	}else{
		wp_cache_add($nm, array($value), 'qtranxc_options');
		return $value;
	}
}// */

/**
 * Filter all options for language tags
 */
function qtranxf_filter_options(){
	global $q_config, $wpdb;
	$where;
	switch($q_config['filter_options_mode']){
		case QTX_FILTER_OPTIONS_ALL:
			$where=' WHERE autoload=\'yes\' AND (option_value LIKE \'%![:__!]%\' ESCAPE \'!\' OR option_value LIKE \'%{:__}%\' OR option_value LIKE \'%<!--:__-->%\')';
			//$alloptions = wp_load_alloptions();
			//foreach($alloptions as $option => $value) {
			//	add_filter('option_'.$option, 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
			//} return;
			break;
		case QTX_FILTER_OPTIONS_LIST:
			if(empty($q_config['filter_options'])) return;
			$where = ' WHERE FALSE';
			foreach($q_config['filter_options'] as $nm){
				$where .= ' OR option_name LIKE "'.$nm.'"';
			}
			break;
		default: return;
	}
	$result = $wpdb->get_results('SELECT option_name FROM '.$wpdb->options.$where);
	if(!$result) return;
	foreach($result as $row) {
		$option = $row->option_name;
		//qtranxf_dbg_log('add_filter: option_'.$option);
		add_filter('option_'.$option,'qtranxf_translate_option',5);
		//$option_cache = wp_cache_get( $option, 'options' );
		//if($option_cache){
		//	$option_cache = qtranxf_translate_option($option_cache);
		//	//qtranxf_dbg_log('qtranxf_filter_options: $option_cache: ',$option_cache);
		//	wp_cache_replace( $option, $option_cache, 'options' );
		//}
	}
}
qtranxf_filter_options();

/**
 * @since 3.4.6.5
*/
function qtranxf_translate_post($post,$lang) {
	foreach(get_object_vars($post) as $key => $txt) {
		switch($key){//the quickest way to proceed
			//known to skip
			case 'ID'://int
			case 'post_author':
			case 'post_date':
			case 'post_date_gmt':
			case 'post_status':
			case 'comment_status':
			case 'ping_status':
			case 'post_password':
			case 'post_name': //slug!
			case 'to_ping':
			case 'pinged':
			case 'post_modified':
			case 'post_modified_gmt':
			case 'post_parent': //int
			case 'guid':
			case 'menu_order': //int
			case 'post_type':
			case 'post_mime_type':
			case 'comment_count':
			case 'filter':
				continue;
			//known to translate
			case 'post_content': $post->$key = qtranxf_use_language($lang, $txt, true); break;
			case 'post_title':
			case 'post_excerpt':
			case 'post_content_filtered'://not sure how this is in use
			{
				$blocks = qtranxf_get_language_blocks($txt);
				if(count($blocks)>1){//value is multilingual
					$key_ml = $key.'_ml';
					$post->$key_ml = $txt;
					$langs = array();
					$content = qtranxf_split_blocks($blocks,$langs);
					$post->$key = qtranxf_use_content($lang, $content, $langs, false);
					//$post->$key = qtranxf_use_block($lang, $blocks, false);
					$key_langs = $key.'_langs';
					$post->$key_langs = $langs;
				}
			} break;
			//other maybe, if it is a string, most likely it never comes here
			default:
				$post->$key = qtranxf_use($lang, $txt, false);
		}
	}
}

function qtranxf_postsFilter($posts,&$query) {//WP_Query
	global $q_config;
	//qtranxf_dbg_log('qtranxf_postsFilter: $posts: ',$posts);
	//$post->post_content = qtranxf_useCurrentLanguageIfNotFoundShowAvailable($post->post_content);
	//$posts = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($posts);
	if(!is_array($posts)) return $posts;
	//qtranxf_dbg_log('qtranxf_postsFilter: $post_type: ',$query->query_vars['post_type']);
	//qtranxf_dbg_log('qtranxf_postsFilter: query_vars: ',$query->query_vars);
	switch($query->query_vars['post_type']){
		case 'nav_menu_item': return $posts;//will translate later in qtranxf_wp_get_nav_menu_items: to be able to filter empty labels.
		default: break;
	}
	$lang = $q_config['language'];
	foreach($posts as $post) {//post is an object derived from WP_Post
		//if($post->filter == 'raw') continue;//@since 3.4.5 - makes 'get_the_exerpts' to return raw, breaks "more" tags in 'the_content', etc.
		//qtranxf_dbg_log('qtranxf_postsFilter: ID='.$post->ID.'; post_type='.$post->post_type.'; $post->filter: ',$post->filter);
		qtranxf_translate_post($post,$lang);
	}
	return $posts;
}
add_filter('the_posts', 'qtranxf_postsFilter', 5, 2);

/** allow all filters within WP_Query - many other add_filters may not be needed now? */
function qtranxf_pre_get_posts( &$query ) {//WP_Query
	//qtranxf_dbg_log('qtranxf_pre_get_posts: $query: ',$query);
	//'post_type'
	if(isset($query->query_vars['post_type'])){
			switch($query->query_vars['post_type']){
				case 'nav_menu_item': return;
				default: break;
			}
	}
	$query->query_vars['suppress_filters'] = false;
}
add_action( 'pre_get_posts', 'qtranxf_pre_get_posts', 99 );

/**
 * since 3.1-b3 new query to pass empty content and content without closing tags (sliders, galleries and other special kind of posts that never get translated)
 */
function qtranxf_where_clause_translated_posts($lang,$table_posts) {
	$post_content = $table_posts.'.post_content';
	return  "($post_content='' OR $post_content LIKE '%![:$lang!]%' ESCAPE '!' OR $post_content LIKE '%<!--:$lang-->%' OR ($post_content NOT LIKE '%![:!]%' ESCAPE '!' AND $post_content NOT LIKE '%<!--:-->%'))";
}

function qtranxf_excludePages($pages) {
	static $exclude = 0;
	if(!is_array($exclude)){
		global $wpdb;
		$lang = qtranxf_getLanguage();
		$where = qtranxf_where_clause_translated_posts($lang,$wpdb->posts);
		$query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND NOT ".$where;
		$hide_pages = $wpdb->get_results($query);
		$exclude = array();
		foreach($hide_pages as $page) {
			$exclude[] = $page->ID;
		}
	}
	return array_merge($exclude, $pages);
}

/**
 * @since 3.3.7
 * applied in /wp-includes/link-template.php on line
 *
 * $where = apply_filters( "get_{$adjacent}_post_where", $wpdb->prepare( "WHERE p.post_date $op %s AND p.post_type = %s $where", $current_post_date, $post->post_type ), $in_same_term, $excluded_terms );
 *
 */
function qtranxf_excludeUntranslatedAdjacentPosts($where) {
	$lang = qtranxf_getLanguage();
	$where .= ' AND '.qtranxf_where_clause_translated_posts($lang,'p');
	//qtranxf_dbg_log('qtranxf_excludeUntranslatedAdjacentPosts: $where: ', $where);
	return $where;
}

function qtranxf_excludeUntranslatedPosts($where,&$query) {//WP_Query
	//qtranxf_dbg_log('qtranxf_excludeUntranslatedPosts: post_type: ',$query->query_vars['post_type']);
	switch($query->query_vars['post_type']){
		//known not to filter
		case 'nav_menu_item':
			return $where;
		//known to filter
		case '':
		case 'any':
		case 'page':
		case 'post':
		default: break;
	}
	//qtranxf_dbg_log('qtranxf_excludeUntranslatedPosts: post_type is empty: $query: ',$query, true);
	//qtranxf_dbg_log('qtranxf_excludeUntranslatedPosts: $where: ',$where);
	//qtranxf_dbg_log('qtranxf_excludeUntranslatedPosts: is_singular(): ',is_singular());
	$single_post_query=$query->is_singular();//since 3.1 instead of top is_singular()
	while(!$single_post_query){
		$single_post_query = preg_match('/ID\s*=\s*[\'"]*(\d+)[\'"]*/i',$where,$matches)==1;
		if($single_post_query) break;
		$single_post_query = preg_match('/post_name\s*=\s*[^\s]+/i',$where,$matches)==1;
		break;
	}
	//qtranxf_dbg_log('qtranxf_excludeUntranslatedPosts: $single_post_query: ',$single_post_query);
	if(!$single_post_query){
		global $wpdb;
		$lang = qtranxf_getLanguage();
		$where .= ' AND '.qtranxf_where_clause_translated_posts($lang,$wpdb->posts);
	}
	return $where;
}

function qtranxf_excludeUntranslatedPostComments($clauses, &$q/*WP_Comment_Query*/) {
	global $wpdb;

	//qtranxf_dbg_log('qtranxf_excludeUntranslatedPostComments: $clauses: ',$clauses);
	//if(!isset($clauses['join'])) return $clauses;
	//if(strpos($clauses['join'],$wpdb->posts) === FALSE) return $clauses;

	if( !isset($clauses['join']) || empty($clauses['join']) ){
		$clauses['join'] = "JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID";
	}elseif(strpos($clauses['join'],$wpdb->posts) === FALSE){
		//do not break some more complex JOIN if it ever happens
		return $clauses;
	}
	//qtranxf_dbg_log('qtranxf_excludeUntranslatedPostComments: $wpdb->posts found');

	$single_post_query=is_singular();
	if($single_post_query && isset($clauses['where'])){
		$single_post_query = preg_match('/comment_post_ID\s*=\s*[\'"]*(\d+)[\'"]*/i',$clauses['where'])==1;
	}
	if(!$single_post_query){
		$lang = qtranxf_getLanguage();
		$clauses['where'] .= ' AND '.qtranxf_where_clause_translated_posts($lang,$wpdb->posts);
	}
	return $clauses;
}

/*
//todo in response to https://github.com/qTranslate-Team/qtranslate-x/issues/17
function qtranxf_add_query_language(&$query) //WP_Comment_Query &$this
{
	global $q_config;
	//$query->query_vars[QTX_COOKIE_NAME_FRONT] = qtranxf_getLanguage();//this does not help, since they cut additional query_vars before generating cache key
	$lang = $q_config['language'];//qtranxf_getLanguage();
	//$query->query_vars['meta_query'] = array( 'key' => 'qtranxf_language_' . $lang, 'compare' => 'NOT EXISTS' );//this cannot be right?
}
if($q_config['hide_untranslated']){
	add_action( 'pre_get_comments', 'qtranxf_add_query_language' );
}
*/

//function qtranxf_get_attachment_image_attributes($attr, $attachment, $size)
function qtranxf_get_attachment_image_attributes($attr, $attachment=null, $size=null)
{
	global $q_config;
	$lang = $q_config['language'];
	//qtranxf_dbg_echo('qtranxf_get_attachment_image_attributes: $attachment:',$attachment);
	if(isset($attr['alt'])){
		$attr['alt']=qtranxf_use_language($lang,$attr['alt'],false,false);
	}
	//foreach( $attr as $name => $value ){
		//qtranxf_dbg_echo('qtranxf_get_attachment_image_attributes: $name='.$name.'; value='.$value);
		//if($name!=='alt') continue;
		//$attr[$name]=qtranxf_use_language($lang,$value,false,false);
		////$attr[$name]=qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
	//}
	return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'qtranxf_get_attachment_image_attributes',5,3);

/*
function qtranxf_get_attachment_link( $link, $id=null, $size=null, $permalink=null, $icon=null, $text=null )
{
	global $q_config;
	$lang = $q_config['language'];
	return qtranxf_use_language($lang,$link,false,true);
}
add_filter( 'wp_get_attachment_link', 'qtranxf_get_attachment_link', 5, 6);
*/

function qtranxf_home_url($url, $path, $orig_scheme, $blog_id)
{
	global $q_config;
	$lang = $q_config['language'];
	//qtranxf_dbg_log('qtranxf_home_url: url='.$url.'; path='.$path.'; orig_scheme='.$orig_scheme);
	$url = qtranxf_get_url_for_language($url, $lang, !$q_config['hide_default_language'] || $lang != $q_config['default_language']);
	//qtranxf_dbg_log('qtranxf_home_url: url='.$url.'; lang='.$lang);
	return $url;
}

function qtranxf_esc_html($text) {
	//qtranxf_dbg_echo('qtranxf_esc_html:text=',$text,true);
	//return qtranxf_useDefaultLanguage($text);//this does not make sense, does it? - original code
	//return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($text);
	/**
	 * since 3.1-b1
	 * used to return qtranxf_useDefaultLanguage($text)
	*/
	return qtranxf_useCurrentLanguageIfNotFoundShowEmpty($text);
}
add_filter('esc_html', 'qtranxf_esc_html', 0);

if(!function_exists('qtranxf_trim_words')){
//filter added in qtranslate_hooks.php
function qtranxf_trim_words( $text, $num_words, $more, $original_text ) {
	global $q_config;
	$blocks = qtranxf_get_language_blocks($original_text);
	if ( count($blocks) <= 1 )
		return $text;
	$lang = $q_config['language'];
	$text = qtranxf_use_block($lang, $blocks, true, false);
	return wp_trim_words($text, $num_words, $more);
}
}

/**
 * @since 3.2.9.9.6
 * Delete translated post_meta cache for all languages.
 * Cache may have a few languages, if it is persistent.
 */
function qtranxf_cache_delete_metadata($meta_type, $object_id){//, $meta_key) {
	global $q_config;
	//maybe optimized to only replace the meta_key needed ?
	foreach($q_config['enabled_languages'] as $lang){
		$cache_key_lang = $meta_type . '_meta' . $lang;
		wp_cache_delete($object_id, $cache_key_lang);
	}
}

/**
 * @since 3.2.3 translation of meta data
 * @since 3.4.6.4 improved caching algorithm
 */
function qtranxf_translate_metadata($meta_type, $original_value, $object_id, $meta_key = '', $single = false){
	global $q_config;
	static $meta_cache_unserialized = array();
	if(!isset($q_config['url_info'])){
		//qtranxf_dbg_log('qtranxf_filter_postmeta: too early: $object_id='.$object_id.'; $meta_key',$meta_key,true);
		return $original_value;
	}
	//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_key=',$meta_key);

	//$meta_type = 'post';
	$lang = $q_config['language'];
	$cache_key = $meta_type . '_meta';
	$cache_key_lang = $cache_key . $lang;

	$meta_cache_wp = wp_cache_get($object_id, $cache_key);
	if($meta_cache_wp){
		//if there is wp cache, then we check if there is qtx cache
		$meta_cache = wp_cache_get( $object_id, $cache_key_lang );
	}else{
		//reset qtx cache, since it would not be valid in the absence of wp cache
		qtranxf_cache_delete_metadata($meta_type, $object_id);
		$meta_cache = null;
	}

	if(!isset($meta_cache_unserialized[$meta_type])) $meta_cache_unserialized[$meta_type] = array();
	if(!isset($meta_cache_unserialized[$meta_type][$object_id])) $meta_cache_unserialized[$meta_type][$object_id] = array();
	$meta_unserialized = &$meta_cache_unserialized[$meta_type][$object_id];

	if( !$meta_cache ){
		if ( $meta_cache_wp ) {
			$meta_cache = $meta_cache_wp;
		}else{
			$meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
			$meta_cache = $meta_cache[$object_id];
		}
		$meta_unserialized = array();//clear this cache if we are re-doing meta_cache
		//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_cache before:',$meta_cache);
		foreach($meta_cache as $mkey => $mval){
			$meta_unserialized[$mkey] = array();
			if(strpos($mkey,'_url') !== false){
				switch($mkey){
					case '_menu_item_url': break; // function qtranxf_wp_get_nav_menu_items takes care of this later
					default:
						foreach($mval as $k => $v){
							$s = is_serialized($v);
							if($s) $v = unserialize($v);
							$v = qtranxf_convertURLs($v,$lang);
							$meta_unserialized[$mkey][$k] = $v;
							if($s) $v = serialize($v);
							$meta_cache[$mkey][$k] = $v;
						}
					break;
				}
			}else{
				foreach($mval as $k => $v){
					if(!qtranxf_isMultilingual($v)) continue;
					$s = is_serialized($v);
					if($s) $v = unserialize($v);
					$v = qtranxf_use($lang, $v, false, false);
					$meta_unserialized[$mkey][$k] = $v;
					if($s) $v = serialize($v);
					$meta_cache[$mkey][$k] = $v;
				}
			}
		}
		//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_cache  after:',$meta_cache);
		wp_cache_set( $object_id, $meta_cache, $cache_key_lang );
	}

	if(!$meta_key){
		if($single){
	/**
	  @since 3.2.9.9.7
	  The code executed after a call to this filter in /wp-includes/meta.php,
	  in function get_metadata, is apparently designed having non-empty $meta_key in mind:

	  	if ( $single && is_array( $check ) ){
	  		return $check[0];
	  	}else
	  		return $check;

	  Following the logic of the code "if ( !$meta_key ) return $meta_cache;",
		a few lines below in the same function, the code above rather have to be:

	  	if ( $meta_key && $single && is_array( $check ) ){
	  		return $check[0];
	  	}else
	  		return $check;

	  WP assumes that, if $meta_key is empty, then $single must be 'false', but developers sometimes put 'true' anyway, as it is ignored in the original function. The line below offsets this imperfection.
	  If WP ever fixes that place, this block of code can be removed.
	 */
			return array($meta_cache);
		}
		return $meta_cache;
	}

	if(isset($meta_cache[$meta_key])){
		//cache unserialized values, just for the sake of performance.
		$meta_key_unserialized = &$meta_unserialized[$meta_key];
		if($single){
			if(!isset($meta_key_unserialized[0])) $meta_key_unserialized[0] = maybe_unserialize($meta_cache[$meta_key][0]);
		}else{
			foreach($meta_cache[$meta_key] as $k => $v){
				if(!isset($meta_key_unserialized[$k])) $meta_key_unserialized[$k] = maybe_unserialize($meta_cache[$meta_key][$k]);
			}
		}
		return $meta_key_unserialized;
	}

	if ($single)
		return '';
	else
		return array();
}
/* // code before 3.4.6.4
function qtranxf_translate_metadata($meta_type, $original_value, $object_id, $meta_key = '', $single = false){
	global $q_config;
	if(!isset($q_config['url_info'])){
		//qtranxf_dbg_log('qtranxf_filter_postmeta: too early: $object_id='.$object_id.'; $meta_key',$meta_key,true);
		return $original_value;
	}
	//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_key=',$meta_key);

	//$meta_type = 'post';
	$lang = $q_config['language'];
	$cache_key = $meta_type . '_meta';
	$cache_key_lang = $cache_key . $lang;

	$meta_cache_wp = wp_cache_get($object_id, $cache_key);
	if($meta_cache_wp){
		//if there is wp cache, then we check if there is qtx cache
		$meta_cache = wp_cache_get( $object_id, $cache_key_lang );
	}else{
		//reset qtx cache, since it would not be valid in the absence of wp cache
		qtranxf_cache_delete_metadata($meta_type, $object_id);
		$meta_cache = null;
	}
	if( !$meta_cache ){
		if ( $meta_cache_wp ) {
			$meta_cache = $meta_cache_wp;
		}else{
			$meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
			$meta_cache = $meta_cache[$object_id];
		}

		//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_cache before:',$meta_cache);
		foreach($meta_cache as $mkey => $mval){
			if(strpos($mkey,'_url') !== false){
				$val = array_map('maybe_unserialize', $mval);
				switch($mkey){
					case '_menu_item_url': break; // function qtranxf_wp_get_nav_menu_items takes care of this later
					default:
						//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_cache['.$mkey.'] url before:',$val);
						$val = qtranxf_convertURLs($val,$lang);
						//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_cache['.$mkey.'] url  after:',$val);
					break;
				}
			}else{
				$val = array();
				foreach($mval as $k => $v){
					$ml = qtranxf_isMultilingual($v);
					$v = maybe_unserialize($v);
					if($ml) $v = qtranxf_use($lang, $v, false, false);
					$val[$k] = $v;
				}
			}
			$meta_cache[$mkey] = $val;
		}
		//qtranxf_dbg_log('qtranxf_filter_postmeta: $object_id='.$object_id.'; $meta_cache  after:',$meta_cache);

		wp_cache_set( $object_id, $meta_cache, $cache_key_lang );
	}

	if(!$meta_key){
		if($single){
	/**
	  @since 3.2.9.9.7
	  The code executed after a call to this filter in /wp-includes/meta.php,
	  in function get_metadata, is apparently designed having non-empty $meta_key in mind:

	  	if ( $single && is_array( $check ) ){
	  		return $check[0];
	  	}else
	  		return $check;

	  Following the logic of the code "if ( !$meta_key ) return $meta_cache;",
		a few lines below in the same function, the code above rather have to be:

	  	if ( $meta_key && $single && is_array( $check ) ){
	  		return $check[0];
	  	}else
	  		return $check;

	  The line below offsets this imperfection.
	  If WP ever fixes that place, this block of code will have to be removed.
	 * /
			return array($meta_cache);
		}
		return $meta_cache;
	}

	if(isset($meta_cache[$meta_key]))
		return $meta_cache[$meta_key];

	if ($single)
		return '';
	else
		return array();
}
*/

/**
 * @since 3.2.3 translation of postmeta
 */
function qtranxf_filter_postmeta($original_value, $object_id, $meta_key = '', $single = false){
	return qtranxf_translate_metadata('post', $original_value, $object_id, $meta_key, $single);
}
add_filter('get_post_metadata', 'qtranxf_filter_postmeta', 5, 4);

/**
 * @since 3.2.9.9.6
 * Delete translated post_meta cache for all languages on cache update.
 * Cache may have a few languages, if it is persistent.
 */
function qtranxf_updated_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
	qtranxf_cache_delete_metadata('post', $object_id);
}
add_action('updated_postmeta', 'qtranxf_updated_postmeta', 5, 4);

/**
 * @since 3.4 translation of usermeta
 */
function qtranxf_filter_usermeta($original_value, $object_id, $meta_key = '', $single = false){
	return qtranxf_translate_metadata('user', $original_value, $object_id, $meta_key, $single);
}
add_filter('get_user_metadata', 'qtranxf_filter_usermeta', 5, 4);

/**
 * @since 3.4
 * Delete translated user_meta cache for all languages on cache update.
 * Cache may have a few languages, if it is persistent.
 */
function qtranxf_updated_usermeta( $meta_id, $object_id, $meta_key, $meta_value ) {
	qtranxf_cache_delete_metadata('user', $object_id);
}
add_action('updated_usermeta', 'qtranxf_updated_usermeta', 5, 4);

function qtranxf_checkCanonical($redirect_url, $requested_url) {
	global $q_config;
	//if(!qtranxf_can_redirect()) return $redirect_url;// WP already check this
	$lang = $q_config['language'];
	// fix canonical conflicts with language urls
	$redirect_url_lang = qtranxf_convertURL($redirect_url,$lang);
	//$requested_url_lang = qtranxf_convertURL($requested_url,$lang);
	//qtranxf_dbg_log('qtranxf_checkCanonical: requested vs redirect vs redirect_lang:' . PHP_EOL . $requested_url . PHP_EOL . $redirect_url. PHP_EOL . $redirect_url_lang);
	//qtranxf_dbg_log('qtranxf_checkCanonical: redirect vs requested:' . PHP_EOL . $redirect_url . PHP_EOL . $requested_url. PHP_EOL . 'redirect_lang vs requested_lang:' . PHP_EOL . $redirect_url_lang . PHP_EOL . $requested_url_lang, 'novar');//. PHP_EOL . '$q_config[url_info]: ', $q_config['url_info']);
	//if(qtranxf_convertURL($redirect_url)==qtranxf_convertURL($requested_url))
	//if($redirect_url_lang==$requested_url_lang) return false; //WP calls this only if $redirect_url != $requested_url, we only need to make sure to return language encoded url
	return $redirect_url_lang;
}
add_filter('redirect_canonical', 'qtranxf_checkCanonical', 10, 2);

/**
 * @since 3.2.8 moved here from _hooks.php
 */
function qtranxf_convertBlogInfoURL($url, $what) {
	global $q_config;
	switch($what){
		case 'stylesheet_url':
		case 'template_url':
		case 'template_directory':
		case 'stylesheet_directory':
			return $url;
		default: return qtranxf_convertURL($url);
	}
}

/**
 * @since 3.3.1
 * Moved here from qtranslate_hooks.php and modified.
*/
function qtranxf_pagenum_link($url) {
	$url_fixed = preg_replace('#\?lang=[a-z]{2}/#i', '/', $url); //kind of ugly fix for function get_pagenum_link in /wp-includes/link-template.php. Maybe we should cancel filter 'bloginfo_url' instead?
	return qtranxf_convertURL($url_fixed);
}
add_filter('get_pagenum_link', 'qtranxf_pagenum_link');

/**
 * @since 3.3.7
 */
function qtranxf_add_front_filters(){
	global $q_config;

	if($q_config['hide_untranslated']){
		add_filter('wp_list_pages_excludes', 'qtranxf_excludePages');//moved here from _hooks.php since 3.2.8
		add_filter('posts_where_request', 'qtranxf_excludeUntranslatedPosts',10,2);
		add_filter('comments_clauses','qtranxf_excludeUntranslatedPostComments',10,2);
		add_filter('get_previous_post_where', 'qtranxf_excludeUntranslatedAdjacentPosts');
		add_filter('get_next_post_where', 'qtranxf_excludeUntranslatedAdjacentPosts');
	}

	foreach($q_config['text_field_filters'] as $nm){
		add_filter($nm, 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage');
	}

	$page_configs = qtranxf_get_front_page_config();
	//qtranxf_dbg_log('$page_configs: ', $page_configs);
	if(!empty($page_configs['']['filters'])){
		qtranxf_add_filters($page_configs['']['filters']);
	}

	if($q_config['url_mode'] != QTX_URL_QUERY){
		/* WP uses line like 'trailingslashit( get_bloginfo( 'url' ) )' in /wp-includes/link-template.php, for example, which obviously breaks the further processing in QTX_URL_QUERY mode.
		*/
		add_filter('bloginfo_url', 'qtranxf_convertBlogInfoURL',10,2);
		add_filter('home_url', 'qtranxf_home_url', 0, 4);
	}

	// Hooks (execution time critical filters)
	add_filter('gettext', 'qtranxf_gettext',0);
	add_filter('gettext_with_context', 'qtranxf_gettext_with_context',0);
	add_filter('ngettext', 'qtranxf_ngettext',0);
}
qtranxf_add_front_filters();

//qtranxf_optionFilter();
//add_filter('wp_head', 'qtranxf_add_css');

/* //moved to i18n-config.json
// Compability with Default Widgets
add_filter('widget_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('widget_text', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', 0);//WP: fires for display purposes only
add_filter('category_description', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('list_cats', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_dropdown_cats', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('term_name', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_comment_author', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_author', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('tml_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);

/ **
 * @since 3.2
 * wp-includes\category-template.php:1230 calls:
 * $description = get_term_field( 'description', $term, $taxonomy );
 *
 * which calls wp-includes\taxonomy.php:1503
 * return sanitize_term_field($field, $term->$field, $term->term_id, $taxonomy, $context);
 *
 * which calls wp-includes\taxonomy.php:2276:
 * apply_filters( "term_{$field}", $value, $term_id, $taxonomy, $context );
* /
add_filter('term_description', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);

// translate terms
add_filter('cat_row', 'qtranxf_useTermLib',0);
add_filter('cat_rows', 'qtranxf_useTermLib',0);
add_filter('wp_get_object_terms', 'qtranxf_useTermLib',0);
add_filter('single_tag_title', 'qtranxf_useTermLib',0);
add_filter('single_cat_title', 'qtranxf_useTermLib',0);
add_filter('the_category', 'qtranxf_useTermLib',0);
add_filter('get_term', 'qtranxf_useTermLib',0);
add_filter('get_terms', 'qtranxf_useTermLib',0);
add_filter('get_category', 'qtranxf_useTermLib',0);
// */
