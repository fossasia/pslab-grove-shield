<?php

define('TRANSLATE_SHOW_DEFAULT', 1);
define('TRANSLATE_SHOW_AVALABLE', 2);
define('TRANSLATE_SHOW_EMPTY', 4);

/**
 * Interface WP_Translator
 *
 * Designed as interface for other plugin integration. The documentation is available at
 * https://qtranslatexteam.wordpress.com/integration/
 *
 * It is recommended to only use the functions listed here when developing a 3rd-party integration.
 * It is not recommended to access global variables directly.
 *
 * Each method declared here is connected to a filter with the same name.
 * For example, to call 'translate_text', one may use the following line of code:
 *
 *   $text = apply_filters('translate_text', $text, $lang, $flags);
 *
 * where arguments $lang and $flags may be omitted.
 *
 * If a translating plugin is not loaded, the variable $text will not be altered, otherwise it may get translated, if applicable. This is a safe and easy way to integrate your plugin or theme with a translating plugin.
 *
 * Alternatively, you may get an instance of this object with a call
 *
 *   $translator = apply_filters('wp_translator', null);
 *
 * Use test 'if($translator)' to determine if a translating plugin was loaded and to fork your code accordingly. However, it only makes sense to do, if your plugin requires presence of a translating plugin, otherwise apply_filters method of calling the interface functions is easier to employ.
 *
 * Below is the list of all available filter calls, printed here for the sake of convenience for a developer to copy and paste.
 *
 * Available at both, front- and admin-side:
 *
 *   $text = apply_filters('translate_text', $text, $lang=null, $flags=0);
 *   $term = apply_filters('translate_term', $term, $lang=null, $taxonomy=null);
 *   $url  = apply_filters('translate_url', $url, $lang=null);
 *
 * Available at admin-side only (see ./admin/i18n-interface-admin.php for function documentations):
 *
 *   $term = apply_filters('multilingual_term', $term, $term_default=null, $taxonomy=null);
 *
 * @since 3.4
 */
interface WP_Translator
{
/**
 * Get WP_Translator global object.
 */
	public static function get_translator();

/**
 * Get translated value from a multilingual string.
 *
 * @param (mixed) $text - a string, an array or an object possibly containing multilingual values.
 * @param (string)(optional) $lang - a two-letter language code of the language to be extracted from $text. If omitted or null, then the currently active language is assumed.
 * @param (int)(optional) $flags - what to return if text for language $lang is not available. Possible choices are
 *     TRANSLATE_SHOW_DEFAULT - show the value for default language (default)
 *     TRANSLATE_SHOW_AVALABLE - return a list of available languages with language-encoded links to the current page.
 *     TRANSLATE_SHOW_EMPTY - return empty string.
 */
	public function translate_text($text, $lang=null, $flags=0);

/**
 * Get translated value for a term name.
 *
 * @param (mixed) $term - The term name to be translated. It may be an array of terms.
 * @param (string)(optional) $lang - A two-letter language code of the language to translate $term to. If omitted or null, then the currently active language is assumed.
 * @param (string)(optional) $taxonomy - Taxonomy name that $term is part of. Currently unused, since all term names assumed to be unique across all taxonomies.
 */
	public function translate_term($term, $lang=null, $taxonomy=null);

/**
 * Get language-encoded value for a URL.
 * 
 * @param (mixed) $url - The URL to be encoded. It may be an array of URLs.
 * @param (string)(optional) $lang - A two-letter language code of the language to encode $url with. If omitted or null, then the currently active language is assumed.
 */
	public function translate_url($url, $lang=null);

}
