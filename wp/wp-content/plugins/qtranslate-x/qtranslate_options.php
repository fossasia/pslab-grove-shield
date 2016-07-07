<?php // encoding: utf-8
if ( !defined( 'ABSPATH' ) ) exit;

/* There is no need to edit anything here! */
define('QTX_STRING',	1);
define('QTX_BOOLEAN',	2);
define('QTX_INTEGER',	3);
define('QTX_URL',	4);
define('QTX_LANGUAGE',	5);
define('QTX_ARRAY',	6);
define('QTX_BOOLEAN_SET',	7);
define('QTX_TEXT',	8);//multi-line string

define('QTX_URL_QUERY'  , 1);// query: domain.com?lang=en
define('QTX_URL_PATH'   , 2);// pre path: domain.com/en
define('QTX_URL_DOMAIN' , 3);// pre domain: en.domain.com
define('QTX_URL_DOMAINS', 4);// domain per language

define('QTX_DATE_WP', 0);// default
// strftime usage (backward compability)
define('QTX_STRFTIME_OVERRIDE', 1);
define('QTX_DATE_OVERRIDE', 2);
define('QTX_DATE', 3);// old default
define('QTX_STRFTIME', 4);

define('QTX_FILTER_OPTIONS_ALL', 0);
define('QTX_FILTER_OPTIONS_LIST', 1);
define('QTX_FILTER_OPTIONS_DEFAULT','blogname blogdescription widget_%');

define('QTX_EX_DATE_FORMATS_DEFAULT','\'U\'');

define('QTX_EDITOR_MODE_LSB', 0);//Language Switching Buttons
define('QTX_EDITOR_MODE_RAW', 1);
define('QTX_EDITOR_MODE_SINGLGE', 2);

define('QTX_HIGHLIGHT_MODE_NONE', 0);
define('QTX_HIGHLIGHT_MODE_BORDER_LEFT', 1);
define('QTX_HIGHLIGHT_MODE_BORDER', 2);
define('QTX_HIGHLIGHT_MODE_LEFT_SHADOW', 3);
define('QTX_HIGHLIGHT_MODE_OUTLINE', 4);
define('QTX_HIGHLIGHT_MODE_CUSTOM_CSS', 9);

define('QTX_COOKIE_NAME_FRONT','qtrans_front_language');
define('QTX_COOKIE_NAME_ADMIN','qtrans_admin_language');

define('QTX_IGNORE_FILE_TYPES','gif,jpg,jpeg,png,svg,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js,mp3,mp4,apk');


global $q_config;
global $qtranslate_options;


/**
 * array of default option values
 * other plugins and themes should not use global variables directly, they are subject to change at any time.
 * @since 3.3
 */
function qtranxf_set_default_options(&$ops)
{
	$ops = array();

	//options processed in a standardized way
	$ops['front'] = array();

	$ops['front']['int']=array(
		'url_mode' => QTX_URL_PATH,// sets default url mode
		'use_strftime' => QTX_DATE,// strftime usage (backward compability)
		'filter_options_mode' => QTX_FILTER_OPTIONS_ALL,
		'language_name_case' => 0 //Camel Case
	);

	$ops['front']['bool']=array(
		'detect_browser_language' => true,// enables browser language detection
		'hide_untranslated' => false,// hide pages without content
		'show_displayed_language_prefix' => true,
		'show_alternative_content' => false,
		'hide_default_language' => true,// hide language tag for default language in urls
		'use_secure_cookie' => false,
		'header_css_on' => true,
	);

	//single line options
	$ops['front']['str']=array(
	);

	//multi-line options
	$ops['front']['text']=array(
		'header_css' => 'qtranxf_front_header_css_default',
	);

	$ops['front']['array']=array(
		//'term_name'// uniquely special treatment
		'text_field_filters' => array(),
		'front_config' => array(),
	);

	//options processed in a special way

	// store other default values of specially handled options
	$ops['default_value']=array(
		'default_language' => null,//string
		'enabled_languages' => null,//array
		'qtrans_compatibility' => false,//enables compatibility with former qtrans_* functions
		'disable_client_cookies' => false,//bool
		'flag_location' => null,//string
		'filter_options' => QTX_FILTER_OPTIONS_DEFAULT,//array
		'ignore_file_types' => QTX_IGNORE_FILE_TYPES,//array
		'domains' => null,//array
	);

	//must have function 'qtranxf_default_option_name()' which returns a default value for option 'option_name'.
	$ops['languages']=array(
		'language_name' => 'qtranslate_language_names',
		'locale' => 'qtranslate_locales',
		'locale_html' => 'qtranslate_locales_html',
		'not_available' => 'qtranslate_na_messages',
		'date_format' => 'qtranslate_date_formats',
		'time_format' => 'qtranslate_time_formats',
		'flag' => 'qtranslate_flags',
		//'windows_locale' => null,//this property is not stored
	);

	/**
	 * A chance to add additional options
	*/
	$ops = apply_filters('qtranslate_option_config',$ops);
}

/* pre-Domain Endings - for future use
	$cfg['pre_domain'] = array();
	$cfg['pre_domain']['de'] = 'de';
	$cfg['pre_domain']['en'] = 'en';
	$cfg['pre_domain']['zh'] = 'zh';
	$cfg['pre_domain']['ru'] = 'ru';
	$cfg['pre_domain']['fi'] = 'fs';
	$cfg['pre_domain']['fr'] = 'fr';
	$cfg['pre_domain']['nl'] = 'nl';
	$cfg['pre_domain']['sv'] = 'sv';
	$cfg['pre_domain']['it'] = 'it';
	$cfg['pre_domain']['ro'] = 'ro';
	$cfg['pre_domain']['hu'] = 'hu';
	$cfg['pre_domain']['ja'] = 'ja';
	$cfg['pre_domain']['es'] = 'es';
	$cfg['pre_domain']['vi'] = 'vi';
	$cfg['pre_domain']['ar'] = 'ar';
	$cfg['pre_domain']['pt'] = 'pt';
	$cfg['pre_domain']['pb'] = 'pb';
	$cfg['pre_domain']['pl'] = 'pl';
	$cfg['pre_domain']['gl'] = 'gl';
	$cfg['pre_domain']['tr'] = 'tr';
*/

/**
 * Names for languages in the corresponding language, add more if needed
 * @since 3.3
 */
function qtranxf_default_language_name()
{
	//Native Name
	$nnm = array();
	$nnm['de'] = 'Deutsch';
	$nnm['en'] = 'English';
	$nnm['zh'] = '中文';// 简体中文
	$nnm['ru'] = 'Русский';
	$nnm['fi'] = 'suomi';
	$nnm['fr'] = 'Français';
	$nnm['nl'] = 'Nederlands';
	$nnm['sv'] = 'Svenska';
	$nnm['it'] = 'Italiano';
	$nnm['ro'] = 'Română';
	$nnm['hu'] = 'Magyar';
	$nnm['ja'] = '日本語';
	$nnm['es'] = 'Español';
	$nnm['vi'] = 'Tiếng Việt';
	$nnm['ar'] = 'العربية';
	$nnm['pt'] = 'Português';
	$nnm['pb'] = 'Português do Brasil';
	$nnm['pl'] = 'Polski';
	$nnm['gl'] = 'galego';
	$nnm['tr'] = 'Turkish';
	$nnm['et'] = 'Eesti';
	$nnm['hr'] = 'Hrvatski';
	$nnm['eu'] = 'Euskera';
	$nnm['el'] = 'Ελληνικά';
	$nnm['ua'] = 'Українська';
	$nnm['cy'] = 'Cymraeg';// Oct 22 2015
	$nnm['ca'] = 'Català';//Nov 6 2015
	$nnm['sk'] = 'Slovenčina';//Nov 12 2015
	$nnm['lt'] = 'Lietuvių';//May 3 2016
	//$nnm['tw'] = '繁體中文';
	return $nnm;
}

/**
 * Locales for languages
 * @since 3.3
 */
function qtranxf_default_locale()
{
	// see locale -a for available locales
	$loc = array();
	$loc['de'] = 'de_DE';
	$loc['en'] = 'en_US';
	$loc['zh'] = 'zh_CN';
	$loc['ru'] = 'ru_RU';
	$loc['fi'] = 'fi';//changed from fi_FI on Nov 10 2015 to match WordPress locale
	$loc['fr'] = 'fr_FR';
	$loc['nl'] = 'nl_NL';
	$loc['sv'] = 'sv_SE';
	$loc['it'] = 'it_IT';
	$loc['ro'] = 'ro_RO';
	$loc['hu'] = 'hu_HU';
	$loc['ja'] = 'ja';
	$loc['es'] = 'es_ES';
	$loc['vi'] = 'vi';
	$loc['ar'] = 'ar';
	$loc['pt'] = 'pt_PT';
	$loc['pb'] = 'pt_BR';
	$loc['pl'] = 'pl_PL';
	$loc['gl'] = 'gl_ES';
	$loc['tr'] = 'tr_TR';
	$loc['et'] = 'et';//changed from et_EE on Nov 10 2015 to match WordPress locale
	$loc['hr'] = 'hr';//changed from hr_HR on Nov 10 2015 to match WordPress locale
	$loc['eu'] = 'eu';//changed from eu_ES on Nov 10 2015 to match WordPress locale
	$loc['el'] = 'el';//corrected from el_GR on Nov 10 2015 http://qtranslate-x.com/support/index.php?topic=27
	$loc['ua'] = 'uk';
	$loc['cy'] = 'cy';// not 'cy_GB'
	$loc['ca'] = 'ca';
	$loc['sk'] = 'sk_SK';
	$loc['lt'] = 'lt_LT';
	//$loc['tw'] = 'zh_TW';
	return $loc;
}

/**
 * HTML locales for languages
 * @since 3.4
 */
function qtranxf_default_locale_html(){
	//HTML locales for languages are not provided by default
	$cfg = array();
	return $cfg;
}

/**
 * Language not available messages
 * @since 3.3
 */
function qtranxf_default_not_available()
{
	// %LANG:<normal_separator>:<last_separator>% generates a list of languages separated by <normal_separator> except for the last one, where <last_separator> will be used instead.
	//Not Available Message
	$nam = array();
	//Sorry, this entry is only available in "%LANG:, :" and "%".
	$nam['de'] = 'Leider ist der Eintrag nur auf %LANG:, : und % verfügbar.';//ok
	$nam['en'] = 'Sorry, this entry is only available in %LANG:, : and %.';//ok
	$nam['zh'] = '对不起，此内容只适用于%LANG:，:和%。';
	$nam['ru'] = 'Извините, этот техт доступен только в &ldquo;%LANG:&rdquo;, &ldquo;:&rdquo; и &ldquo;%&rdquo;.';//ok
	//$nam['fi'] = 'Anteeksi, mutta tämä kirjoitus on saatavana ainoastaan näillä kielillä: %LANG:, : ja %.';
	$nam['fi'] = 'Tämä teksti on valitettavasti saatavilla vain kielillä: %LANG:, : ja %.';//Jyrki Vanamo, Oct 20 2015, 3.4.6.5
	$nam['fr'] = 'Désolé, cet article est seulement disponible en %LANG:, : et %.';
	$nam['nl'] = 'Onze verontschuldigingen, dit bericht is alleen beschikbaar in %LANG:, : en %.';
	$nam['sv'] = 'Tyvärr är denna artikel enbart tillgänglig på %LANG:, : och %.';
	$nam['it'] = 'Ci spiace, ma questo articolo è disponibile soltanto in %LANG:, : e %.';
	$nam['ro'] = 'Din păcate acest articol este disponibil doar în %LANG:, : și %.';
	$nam['hu'] = 'Sajnos ennek a bejegyzésnek csak %LANG:, : és % nyelvű változata van.';
	$nam['ja'] = '申し訳ありません、このコンテンツはただ今　%LANG:、 :と %　のみです。';
	$nam['es'] = 'Disculpa, pero esta entrada está disponible sólo en %LANG:, : y %.';
	$nam['vi'] = 'Rất tiếc, mục này chỉ tồn tại ở %LANG:, : và %.';
	$nam['ar'] = 'عفوا، هذه المدخلة موجودة فقط في %LANG:, : و %.';
	$nam['pt'] = 'Desculpe, este conteúdo só está disponível em %LANG:, : e %.';
	$nam['pb'] = 'Desculpe-nos, mas este texto está apenas disponível em %LANG:, : y %.';
	$nam['pl'] = 'Przepraszamy, ten wpis jest dostępny tylko w języku %LANG:, : i %.';
	$nam['gl'] = 'Sentímolo moito, ista entrada atopase unicamente en %LANG;,: e %.';
	$nam['tr'] = 'Sorry, this entry is only available in %LANG:, : and %.';
	$nam['et'] = 'Vabandame, see kanne on saadaval ainult %LANG : ja %.';
	$nam['hr'] = 'Žao nam je, ne postoji prijevod na raspolaganju za ovaj proizvod još %LANG:, : i %.';
	$nam['eu'] = 'Sentitzen dugu, baina sarrera hau %LANG-z:, : eta % bakarrik dago.';
	$nam['el'] = 'Συγγνώμη,αυτή η εγγραφή είναι διαθέσιμη μόνο στα %LANG:, : και %.';
	$nam['ua'] = 'Вибачте цей текст доступний тільки в &ldquo;%LANG:&rdquo;, &ldquo;: і &ldquo;%&rdquo;.';//ok
	$nam['cy'] = 'Mae&#8217;n ddrwg gen i, mae\'r cofnod hwn dim ond ar gael mewn %LANG:, : a %.';//ok
	$nam['ca'] = 'Ho sentim, aquesta entrada es troba disponible únicament en %LANG:, : i %.';//ok
	$nam['sk'] = 'Ľutujeme, táto stránka je dostupná len v %LANG:, : a %.';//ok
	$nam['lt'] = 'Atsiprašome, šis puslapis galimas tik %LANG:, : ir %.';
	//$nam['tw'] = '对不起，此内容只适用于%LANG:，:和%。';
	return $nam;
}

/**
 * Date Configuration
 * @since 3.3
 */
function qtranxf_default_date_format()
{
	$dtf = array();
	$dtf['en'] = '%A %B %e%q, %Y';
	$dtf['de'] = '%A, \d\e\r %e. %B %Y';
	$dtf['zh'] = '%x %A';
	$dtf['ru'] = '%A %B %e%q, %Y';
	//$dtf['fi'] = '%e.&m.%C';
	$dtf['fi'] = '%d.%m.%Y';//Jyrki Vanamo, Oct 20 2015, 3.4.6.5
	$dtf['fr'] = '%A %e %B %Y';
	$dtf['nl'] = '%d/%m/%y';
	$dtf['sv'] = '%Y-%m-%d';
	$dtf['it'] = '%e %B %Y';
	$dtf['ro'] = '%A, %e %B %Y';
	$dtf['hu'] = '%Y %B %e, %A';
	$dtf['ja'] = '%Y年%m月%d日';
	$dtf['es'] = '%d \d\e %B \d\e %Y';
	$dtf['vi'] = '%d/%m/%Y';
	$dtf['ar'] = '%d/%m/%Y';
	$dtf['pt'] = '%A, %e \d\e %B \d\e %Y';
	$dtf['pb'] = '%d \d\e %B \d\e %Y';
	$dtf['pl'] = '%d/%m/%y';
	$dtf['gl'] = '%d \d\e %B \d\e %Y';
	$dtf['tr'] = '%A %B %e%q, %Y';
	$dtf['et'] = '%A %B %e%q, %Y';
	$dtf['hr'] = '%d/%m/%Y';
	$dtf['eu'] = '%Y %B %e, %A';
	$dtf['el'] = '%d/%m/%y';
	$dtf['ua'] = '%A %B %e%q, %Y';
	$dtf['cy'] = '%A %B %e%q, %Y';//not verified
	$dtf['ca'] = 'j F, Y';
	$dtf['sk'] = 'j.F Y';
	$dtf['lt'] = '%Y.%m.%d';
	//$dtf['tw'] = '%x %A';
	return $dtf;
}

/**
 * Time Configuration
 * @since 3.3
 */
function qtranxf_default_time_format()
{
	$tmf = array();
	$tmf['en'] = '%I:%M %p';
	$tmf['de'] = '%H:%M';
	$tmf['zh'] = '%I:%M%p';
	$tmf['ru'] = '%H:%M';
	$tmf['fi'] = '%H:%M';
	$tmf['fr'] = '%H:%M';
	$tmf['nl'] = '%H:%M';
	$tmf['sv'] = '%H:%M';
	$tmf['it'] = '%H:%M';
	$tmf['ro'] = '%H:%M';
	$tmf['hu'] = '%H:%M';
	$tmf['ja'] = '%H:%M';
	$tmf['es'] = '%H:%M hrs.';
	$tmf['vi'] = '%H:%M';
	$tmf['ar'] = '%H:%M';
	$tmf['pt'] = '%H:%M';
	$tmf['pb'] = '%H:%M hrs.';
	$tmf['pl'] = '%H:%M';
	$tmf['gl'] = '%H:%M hrs.';
	$tmf['tr'] = '%H:%M';
	$tmf['et'] = '%H:%M';
	$tmf['hr'] = '%H:%M';
	$tmf['eu'] = '%H:%M';
	$tmf['el'] = '%H:%M';
	$tmf['ua'] = '%H:%M';
	$tmf['cy'] = '%I:%M %p';//not verified
	$tmf['ca'] = 'G:i';
	$tmf['sk'] = 'G:i';
	$tmf['lt'] = '%H:%M';
	//$tmf['tw'] = '%I:%M%p';
	return $tmf;
}

/**
 * Flag images configuration
 * Look in /flags/ directory for a huge list of flags for usage
 * @since 3.3
 */
function qtranxf_default_flag()
{
	$flg = array();
	$flg['en'] = 'gb.png';
	$flg['de'] = 'de.png';
	$flg['zh'] = 'cn.png';
	$flg['ru'] = 'ru.png';
	$flg['fi'] = 'fi.png';
	$flg['fr'] = 'fr.png';
	$flg['nl'] = 'nl.png';
	$flg['sv'] = 'se.png';
	$flg['it'] = 'it.png';
	$flg['ro'] = 'ro.png';
	$flg['hu'] = 'hu.png';
	$flg['ja'] = 'jp.png';
	$flg['es'] = 'es.png';
	$flg['vi'] = 'vn.png';
	$flg['ar'] = 'arle.png';
	$flg['pt'] = 'pt.png';
	$flg['pb'] = 'br.png';
	$flg['pl'] = 'pl.png';
	$flg['gl'] = 'galego.png';
	$flg['tr'] = 'tr.png';
	$flg['et'] = 'ee.png';
	$flg['hr'] = 'hr.png';
	$flg['eu'] = 'eu_ES.png';
	$flg['el'] = 'gr.png';
	$flg['ua'] = 'ua.png';
	$flg['cy'] = 'cy_GB.png';
	$flg['ca'] = 'catala.png';
	$flg['sk'] = 'sk.png';
	$flg['lt'] = 'lt.png';
	//$flg['tw'] = 'tw.png';
	return $flg;
}

/**
 * Full country names as locales for Windows systems
 * @since 3.3
 */
function qtranxf_default_windows_locale()
{
	//English Name
	$enm = array();
	$enm['aa'] = "Afar";
	$enm['ab'] = "Abkhazian";
	$enm['ae'] = "Avestan";
	$enm['af'] = "Afrikaans";
	$enm['am'] = "Amharic";
	$enm['ar'] = "Arabic";
	$enm['as'] = "Assamese";
	$enm['ay'] = "Aymara";
	$enm['az'] = "Azerbaijani";
	$enm['ba'] = "Bashkir";
	$enm['be'] = "Belarusian";
	$enm['bg'] = "Bulgarian";
	$enm['bh'] = "Bihari";
	$enm['bi'] = "Bislama";
	$enm['bn'] = "Bengali";
	$enm['bo'] = "Tibetan";
	$enm['br'] = "Breton";
	$enm['bs'] = "Bosnian";
	$enm['ca'] = "Catalan";
	$enm['ce'] = "Chechen";
	$enm['ch'] = "Chamorro";
	$enm['co'] = "Corsican";
	$enm['cs'] = "Czech";
	$enm['cu'] = "Church Slavic";
	$enm['cv'] = "Chuvash";
	$enm['cy'] = "Welsh";
	$enm['da'] = "Danish";
	$enm['de'] = "German";
	$enm['dz'] = "Dzongkha";
	$enm['el'] = "Greek";
	$enm['en'] = "English";
	$enm['eo'] = "Esperanto";
	$enm['es'] = "Spanish";
	$enm['et'] = "Estonian";
	$enm['eu'] = "Basque";
	$enm['fa'] = "Persian";
	$enm['fi'] = "Finnish";
	$enm['fj'] = "Fijian";
	$enm['fo'] = "Faeroese";
	$enm['fr'] = "French";
	$enm['fy'] = "Frisian";
	$enm['ga'] = "Irish";
	$enm['gd'] = "Gaelic (Scots)";
	$enm['gl'] = "Gallegan";
	$enm['gn'] = "Guarani";
	$enm['gu'] = "Gujarati";
	$enm['gv'] = "Manx";
	$enm['ha'] = "Hausa";
	$enm['he'] = "Hebrew";
	$enm['hi'] = "Hindi";
	$enm['ho'] = "Hiri Motu";
	$enm['hr'] = "Croatian";
	$enm['hu'] = "Hungarian";
	$enm['hy'] = "Armenian";
	$enm['hz'] = "Herero";
	$enm['ia'] = "Interlingua";
	$enm['id'] = "Indonesian";
	$enm['ie'] = "Interlingue";
	$enm['ik'] = "Inupiaq";
	$enm['is'] = "Icelandic";
	$enm['it'] = "Italian";
	$enm['iu'] = "Inuktitut";
	$enm['ja'] = "Japanese";
	$enm['jw'] = "Javanese";
	$enm['ka'] = "Georgian";
	$enm['ki'] = "Kikuyu";
	$enm['kj'] = "Kuanyama";
	$enm['kk'] = "Kazakh";
	$enm['kl'] = "Kalaallisut";
	$enm['km'] = "Khmer";
	$enm['kn'] = "Kannada";
	$enm['ko'] = "Korean";
	$enm['ks'] = "Kashmiri";
	$enm['ku'] = "Kurdish";
	$enm['kv'] = "Komi";
	$enm['kw'] = "Cornish";
	$enm['ky'] = "Kirghiz";
	$enm['la'] = "Latin";
	$enm['lb'] = "Letzeburgesch";
	$enm['ln'] = "Lingala";
	$enm['lo'] = "Lao";
	$enm['lt'] = "Lithuanian";
	$enm['lv'] = "Latvian";
	$enm['mg'] = "Malagasy";
	$enm['mh'] = "Marshall";
	$enm['mi'] = "Maori";
	$enm['mk'] = "Macedonian";
	$enm['ml'] = "Malayalam";
	$enm['mn'] = "Mongolian";
	$enm['mo'] = "Moldavian";
	$enm['mr'] = "Marathi";
	$enm['ms'] = "Malay";
	$enm['mt'] = "Maltese";
	$enm['my'] = "Burmese";
	$enm['na'] = "Nauru";
	$enm['nb'] = "Norwegian Bokmal";
	$enm['nd'] = "Ndebele, North";
	$enm['ne'] = "Nepali";
	$enm['ng'] = "Ndonga";
	$enm['nl'] = "Dutch";
	$enm['nn'] = "Norwegian Nynorsk";
	$enm['no'] = "Norwegian";
	$enm['nr'] = "Ndebele, South";
	$enm['nv'] = "Navajo";
	$enm['ny'] = "Chichewa; Nyanja";
	$enm['oc'] = "Occitan (post 1500)";
	$enm['om'] = "Oromo";
	$enm['or'] = "Oriya";
	$enm['os'] = "Ossetian; Ossetic";
	$enm['pa'] = "Panjabi";
	$enm['pi'] = "Pali";
	$enm['pl'] = "Polish";
	$enm['ps'] = "Pushto";
	$enm['pt'] = "Portuguese";
	$enm['pb'] = "Brazilian Portuguese";
	$enm['qu'] = "Quechua";
	$enm['rm'] = "Rhaeto-Romance";
	$enm['rn'] = "Rundi";
	$enm['ro'] = "Romanian";
	$enm['ru'] = "Russian";
	$enm['rw'] = "Kinyarwanda";
	$enm['sa'] = "Sanskrit";
	$enm['sc'] = "Sardinian";
	$enm['sd'] = "Sindhi";
	$enm['se'] = "Sami";
	$enm['sg'] = "Sango";
	$enm['si'] = "Sinhalese";
	$enm['sk'] = "Slovak";
	$enm['sl'] = "Slovenian";
	$enm['sm'] = "Samoan";
	$enm['sn'] = "Shona";
	$enm['so'] = "Somali";
	$enm['sq'] = "Albanian";
	$enm['sr'] = "Serbian";
	$enm['ss'] = "Swati";
	$enm['st'] = "Sotho";
	$enm['su'] = "Sundanese";
	$enm['sv'] = "Swedish";
	$enm['sw'] = "Swahili";
	$enm['ta'] = "Tamil";
	$enm['te'] = "Telugu";
	$enm['tg'] = "Tajik";
	$enm['th'] = "Thai";
	$enm['ti'] = "Tigrinya";
	$enm['tk'] = "Turkmen";
	$enm['tl'] = "Tagalog";
	$enm['tn'] = "Tswana";
	$enm['to'] = "Tonga";
	$enm['tr'] = "Turkish";
	$enm['ts'] = "Tsonga";
	$enm['tt'] = "Tatar";
	$enm['tw'] = "Twi";
	$enm['ug'] = "Uighur";
	$enm['uk'] = "Ukrainian";
	$enm['ur'] = "Urdu";
	$enm['uz'] = "Uzbek";
	$enm['vi'] = "Vietnamese";
	$enm['vo'] = "Volapuk";
	$enm['wo'] = "Wolof";
	$enm['xh'] = "Xhosa";
	$enm['yi'] = "Yiddish";
	$enm['yo'] = "Yoruba";
	$enm['za'] = "Zhuang";
	$enm['zh'] = "Chinese";
	$enm['zu'] = "Zulu";
	return $enm;
}

function qtranxf_language_predefined($lang)
{
	$language_names = qtranxf_default_language_name();
	return isset($language_names[$lang]);
}

function qtranxf_language_configured($prop,$opn=null)
{
	global $qtranslate_options;
	$val = call_user_func('qtranxf_default_'.$prop);
	if(!$opn){
		if(isset($qtranslate_options['languages'][$prop])){
			$opn = $qtranslate_options['languages'][$prop];
		}else{
			$opn = 'qtranslate_'.$prop;
		}
	}
	$opt = get_option($opn,array());
	if($opt){
		$val = array_merge($val,$opt);
	}
	return $val;
}

/**
 * Fill merged array of stored and pre-defined language properties
 * @since 3.3
 */
function qtranxf_languages_configured(&$cfg)
{
	global $qtranslate_options;
	foreach($qtranslate_options['languages'] as $nm => $opn){
		$cfg[$nm] = qtranxf_language_configured($nm,$opn);
	}
	//$cfg['windows_locale'] = qtranxf_language_configured('windows_locale');
	return $cfg;
}

/**
 * Load enabled languages properties from  database
 * @since 3.3
 */
function qtranxf_load_languages_enabled()
{
	global $q_config, $qtranslate_options;
	foreach($qtranslate_options['languages'] as $nm => $opn){
		$f = 'qtranxf_default_'.$nm;
		qtranxf_load_option_func($nm,$opn,$f);
		$val = array();
		$def = null;
		foreach($q_config['enabled_languages'] as $lang){
			if(isset($q_config[$nm][$lang])){
				$val[$lang] = $q_config[$nm][$lang];
			}else{
				if(is_null($def) && function_exists($f)) $def = call_user_func($f);
				$val[$lang] = isset($def[$lang]) ? $def[$lang] : '';
			}
		}
		$q_config[$nm] = $val;
	}
	//$locales = qtranxf_default_windows_locale();
	//foreach($q_config['enabled_languages'] as $lang){
	//	$q_config['windows_locale'][$lang] = $locales[$lang];
	//}
}
