<?php
if ( !defined( 'ABSPATH' ) ) exit;

if(!defined('QTRANS_INIT')){
	define('QTRANS_INIT',true);
}
if(!function_exists('qtrans_convertURL')){
	function qtrans_convertURL($url='', $lang='', $forceadmin = false, $showDefaultLanguage = false){
		return qtranxf_convertURL($url, $lang, $forceadmin, $showDefaultLanguage);
	}
}
if(!function_exists('qtrans_generateLanguageSelectCode')){
	function qtrans_generateLanguageSelectCode($style='', $id=''){ return qtranxf_generateLanguageSelectCode($style,$id); }
}

/**
	Some 3rd-party plugins (for example "Google XML Sitemaps v3 for qTranslate") use this function and expect an array in return.
*/
if(!function_exists('qtrans_getAvailableLanguages')){
	function qtrans_getAvailableLanguages($text){
		$langs = qtranxf_getAvailableLanguages($text);
		if(is_array($langs)) return $langs;
		if(empty($text)) return array();
		global $q_config;
		return array($q_config['default_language']);
	}
}

if(!function_exists('qtrans_getLanguage')){
	function qtrans_getLanguage(){ return qtranxf_getLanguage(); }
}
if(!function_exists('qtrans_getLanguageName')){
	function qtrans_getLanguageName($lang = ''){ return qtranxf_getLanguageNameNative($lang); }
}
if(!function_exists('qtrans_getSortedLanguages')){
	function qtrans_getSortedLanguages($reverse = false){ return qtranxf_getSortedLanguages($reverse); }
}
if(!function_exists('qtrans_join')){
	function qtrans_join($texts) {
		if(!is_array($texts)) $texts = qtranxf_split($texts);
		return qtranxf_join_b($texts);
	}
}
if(!function_exists('qtrans_split')){
	function qtrans_split($text, $quicktags = true){ return qtranxf_split($text); }
}
if(!function_exists('qtrans_use')){
	function qtrans_use($lang, $text, $show_available=false){
		return qtranxf_use($lang, $text, $show_available);
	}
}
if (!function_exists('qtrans_useCurrentLanguageIfNotFoundShowAvailable')){
	function qtrans_useCurrentLanguageIfNotFoundShowAvailable($content){
		return qtranxf_useCurrentLanguageIfNotFoundShowAvailable($content);
	}
}
if (!function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')){
	function qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content){
		return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
	}
}
if (!function_exists('qtrans_useDefaultLanguage')){
	function qtrans_useDefaultLanguage($content){
		return qtranxf_useDefaultLanguage($content);
	}
}
if(!function_exists('qtrans_useTermLib')){
	function qtrans_useTermLib($obj){ return qtranxf_useTermLib($obj); }
}
