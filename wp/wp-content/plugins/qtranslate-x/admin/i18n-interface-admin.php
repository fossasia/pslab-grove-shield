<?php
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WP_Translator interface for admin side.
 * See also documentation of interface WP_Translator in ./inc/i18n-interface.php
 *
 * @since 3.4
 */
interface WP_Translator_Admin extends WP_Translator
{
/**
 * Get an encoded multilingual value for a term name.
 * 
 * @param (mixed) $term - The term name to be encoded or null. It may be object with property 'name', or array of names or objects.
 * @param (mixed)(optional) $term_default - The default term name to be encoded or null. It may be an array of terms.
 * @param (string)(optional) $taxonomy - Taxonomy name that $term is part of. Currently unused, since all term names assumed to be unique across all taxonomies.
 */
	public function multilingual_term($term, $term_default=null, $taxonomy=null);
}
