<?php
if ( !defined( 'ABSPATH' ) ) exit;

require_once(QTRANSLATE_DIR.'/inc/i18n-interface.php');
require_once(QTRANSLATE_DIR.'/qtranslate_options.php');
require_once(QTRANSLATE_DIR.'/qtranslate_utils.php');
require_once(QTRANSLATE_DIR.'/qtranslate_core.php');

require_once(QTRANSLATE_DIR.'/qtranslate_widget.php');

/**
 * Implementation of WP_Translator interface.
 * For a function documentation look up definition of WP_Translator.
 * @since 3.4
*/
class QTX_Translator implements WP_Translator
{
	public static function get_translator(){
		global $q_config;
		if(!isset($q_config['translator'])) $q_config['translator'] = new QTX_Translator;
		return $q_config['translator'];
	}

	public function __construct() {
		add_filter('translate_text', array($this, 'translate_text'), 10, 3);
		add_filter('translate_term', array($this, 'translate_term'), 10, 3);
		add_filter('translate_url', array($this, 'translate_url'), 10, 2);
		//add_filter('translate_date', 'qtranxf_', 10, 2);
		//add_filter('translate_time', 'qtranxf_', 10, 2);
	}

	public function translate_text($text, $lang=null, $flags=0) {
		global $q_config;
		if(!$lang) $lang = $q_config['language'];
		$show_available = $flags & TRANSLATE_SHOW_AVALABLE;
		$show_empty = $flags & TRANSLATE_SHOW_EMPTY;
		return qtranxf_use($lang, $text, $show_available, $show_empty);
	}

	public function translate_term($term, $lang=null, $taxonomy=null) {
		global $q_config;
		if(!$lang) $lang = $q_config['language'];
		return qtranxf_use_term($lang, $term, $taxonomy);
	}

	public function translate_url($url, $lang=null) {
		global $q_config;
		if($lang){
			$showLanguage = true;
		}else{
			$lang = $q_config['language'];
			$showLanguage = !$q_config['hide_default_language'] || $lang != $q_config['default_language'];
		}
		return qtranxf_get_url_for_language($url, $lang, $showLanguage);
	}
}
