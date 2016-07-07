<?php
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * return 'true', if no update needed,
 * or 'false', if update is impossible to do,
 * or 0, if all languages were updated successfully,
 * or positive integer number of errors occurred on languages update.
 */
function qtranxf_updateGettextDatabasesEx($force = false, $only_for_language = '') {
	global $q_config;

	if($only_for_language && !qtranxf_isEnabled($only_for_language)){
		return false;
	}

	if(!is_dir(WP_LANG_DIR)) {
		if(!@mkdir(WP_LANG_DIR))
			return false;
	}

	$next_update = get_option('qtranslate_next_update_mo');
	if(time() < $next_update && !$force) return true;
	update_option('qtranslate_next_update_mo', time() + 7*24*60*60);

	require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	include( ABSPATH . WPINC . '/version.php' ); // include an unmodified $wp_version
	$result = translations_api( 'core', array( 'version' => $wp_version ));

	if ( is_wp_error( $result ) ){
		qtranxf_add_warning(__( 'Gettext databases <strong>not</strong> updated:', 'qtranslate' ) . ' ' . $result->get_error_message());
		return false;
	}

	set_time_limit(300);

	$langs = empty($only_for_language) ? $q_config['enabled_languages'] : array($only_for_language);
	$locales = $q_config['locale'];
	$errcnt = 0;
	foreach ( $result['translations'] as $translation ) {
		$locale = $translation['language'];
		$lang = null;
		foreach($langs as $lng) {
			if(!isset($locales[$lng])){
				$locales = qtranxf_language_configured('locale');
				if(!isset($locales[$lng])) continue;
			}
			if($locales[$lng] != $locale) continue;
			$lang = $lng;
			break;
		}
		if(!$lang) continue;

		$translation = (object) $translation;
		$skin              = new Automatic_Upgrader_Skin;
		$upgrader          = new Language_Pack_Upgrader( $skin );
		$translation->type = 'core';
		$result            = $upgrader->upgrade( $translation, array( 'clear_update_cache' => false ));

		if ( is_wp_error( $result ) ){
			qtranxf_add_warning(sprintf(__( 'Failed to update gettext database for "%s": %s', 'qtranslate' ), $q_config['language_name'][$lang], $result->get_error_message()));
			++$errcnt;
		}
	}
	return $errcnt;
}
