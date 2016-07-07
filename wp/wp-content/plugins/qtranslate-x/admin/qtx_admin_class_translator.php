<?php
if ( !defined( 'ABSPATH' ) ) exit;

require_once(QTRANSLATE_DIR.'/admin/i18n-interface-admin.php');

/**
 *
 * @since 3.4
 */
class QTX_Translator_Admin extends QTX_Translator implements WP_Translator_Admin
{
	public static function get_translator(){
		global $q_config;
		if(!isset($q_config['translator'])) $q_config['translator'] = new QTX_Translator_Admin;
		return $q_config['translator'];
	}

	public function __construct() {
		parent::__construct();
		add_filter('multilingual_term', array($this, 'multilingual_term'), 10, 3);
	}

	function multilingual_term($term, $term_default=null, $taxonomy=null) {
		$terms = empty($term_default) ? $term : $term_default;
		return qtranxf_get_terms_joined($terms,$taxonomy);
	}
}
add_filter('wp_translator', 'QTX_Translator_Admin::get_translator');
