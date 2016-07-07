<?php
if ( !defined( 'ABSPATH' ) ) exit;

define('QTX_WIDGET_CSS',
'.qtranxs_widget ul { margin: 0; }
.qtranxs_widget ul li
{
display: inline; /* horizontal list, use "list-item" or other appropriate value for vertical list */
list-style-type: none; /* use "initial" or other to enable bullets */
margin: 0 5px 0 0; /* adjust spacing between items */
opacity: 0.5;
-o-transition: 1s ease opacity;
-moz-transition: 1s ease opacity;
-webkit-transition: 1s ease opacity;
transition: 1s ease opacity;
}
/* .qtranxs_widget ul li span { margin: 0 5px 0 0; } */ /* other way to control spacing */
.qtranxs_widget ul li.active { opacity: 0.8; }
.qtranxs_widget ul li:hover { opacity: 1; }
.qtranxs_widget img { box-shadow: none; vertical-align: middle; display: initial; }
.qtranxs_flag { height:12px; width:18px; display:block; }
.qtranxs_flag_and_text { padding-left:20px; }
.qtranxs_flag span { display:none; }
');

//define('QTX_WIDGET_CUSTOM_FORMAT','%f<span>%n</span>');

/* qTranslate-X Widget */

class qTranslateXWidget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'qtranxs_widget', 'description' => __('Allows your visitors to choose a Language.', 'qtranslate') );
		parent::__construct('qtranslate', __('qTranslate Language Chooser', 'qtranslate'), $widget_ops);
	}

	function widget($args, $instance) {
		extract($args);
		//qtranxf_dbg_log('widget: $this: ',$this);
		//qtranxf_dbg_log('widget: $instance: ',$instance);
		if(!isset($instance['widget-css-off'])){
			echo '<style type="text/css">'.PHP_EOL;
			echo empty($instance['widget-css']) ? QTX_WIDGET_CSS : $instance['widget-css'];
			echo '</style>'.PHP_EOL;
		}
		echo $before_widget;
		if(empty($instance['hide-title'])) {
			$title = $instance['title'];
			if(empty($title))
				$title=__('Language', 'qtranslate');
			if(empty($instance['hide-title-colon']))
				$title .= ':';
			$title=apply_filters('qtranslate_widget_title',$title,$this);
			echo $before_title . $title . $after_title;
		}
		qtranxf_generateLanguageSelectCode($instance,$this->id);
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		//qtranxf_dbg_log('update: $new_instance: ',$new_instance);
		//qtranxf_dbg_log('update: $old_instance: ',$old_instance);
		$instance['title'] = $new_instance['title'];

		if(isset($new_instance['hide-title'])) $instance['hide-title'] = true;
		else unset($instance['hide-title']);

		if(isset($new_instance['hide-title-colon'])) $instance['hide-title-colon'] = true;
		else unset($instance['hide-title-colon']);

		$instance['type'] = $new_instance['type'];

		if(!empty($new_instance['format'])) $instance['format'] = $new_instance['format'];
		else unset($instance['format']);

		if(isset($new_instance['widget-css-on'])) unset($instance['widget-css-off']);
		else $instance['widget-css-off'] = true;

		if($new_instance['widget-css'] == QTX_WIDGET_CSS) unset($instance['widget-css']);
		else $instance['widget-css'] = $new_instance['widget-css'];

		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'type' => 'text', 'format' => '', 'widget-css' => QTX_WIDGET_CSS ) );
		$title = $instance['title'];
		$hide_title = isset($instance['hide-title']) && $instance['hide-title'] !== false;
		$hide_title_colon = isset($instance['hide-title-colon']);
		$type = $instance['type'];
		$format = $instance['format'];
		$widget_css_on = !isset($instance['widget-css-off']);
		$widget_css = $instance['widget-css'];
		if(empty($widget_css)) $widget_css=QTX_WIDGET_CSS;
?>
<p><label for="<?php echo $this->get_field_id('title') ?>"><?php _e('Title:', 'qtranslate') ?> <input class="widefat" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" type="text" value="<?php echo esc_attr($title) ?>" /></label></p>
<p><label for="<?php echo $this->get_field_id('hide-title') ?>"><?php _e('Hide Title:', 'qtranslate') ?> <input type="checkbox" id="<?php echo $this->get_field_id('hide-title') ?>" name="<?php echo $this->get_field_name('hide-title') ?>" <?php checked($hide_title) ?>/></label></p>
<p><label for="<?php echo $this->get_field_id('hide-title-colon') ?>"><?php _e('Hide Title Colon:', 'qtranslate') ?> <input type="checkbox" id="<?php echo $this->get_field_id('hide-title-colon') ?>" name="<?php echo $this->get_field_name('hide-title-colon') ?>" <?php checked($hide_title_colon) ?>/></label></p>
<p><?php _e('Display:', 'qtranslate') ?></p>
<p><label for="<?php echo $this->get_field_id('type') ?>-text"><input type="radio" name="<?php echo $this->get_field_name('type') ?>" id="<?php echo $this->get_field_id('type') ?>-text" value="text"<?php checked($type=='text') ?>/> <?php _e('Text only', 'qtranslate') ?></label></p>
<p><label for="<?php echo $this->get_field_id('type') ?>-image"><input type="radio" name="<?php echo $this->get_field_name('type') ?>" id="<?php echo $this->get_field_id('type') ?>-image" value="image"<?php checked($type=='image') ?>/> <?php _e('Image only', 'qtranslate') ?></label></p>
<p><label for="<?php echo $this->get_field_id('type') ?>-both"><input type="radio" name="<?php echo $this->get_field_name('type') ?>" id="<?php echo $this->get_field_id('type') ?>-both" value="both"<?php checked($type=='both') ?>/> <?php _e('Text and Image', 'qtranslate') ?></label></p>
<p><label for="<?php echo $this->get_field_id('type') ?>-dropdown"><input type="radio" name="<?php echo $this->get_field_name('type') ?>" id="<?php echo $this->get_field_id('type') ?>-dropdown" value="dropdown"<?php checked($type=='dropdown') ?>/> <?php _e('Dropdown Box', 'qtranslate') ?></label></p>
<p><label for="<?php echo $this->get_field_id('type') ?>-custom"><input type="radio" name="<?php echo $this->get_field_name('type') ?>" id="<?php echo $this->get_field_id('type') ?>-custom" value="custom"<?php checked($type=='custom') ?>/> <?php _e('Custom list item format:', 'qtranslate') ?></label></p><p>
<label for="<?php echo $this->get_field_id('format') ?>-format"><input type="text" class="widefat" name="<?php echo $this->get_field_name('format') ?>" id="<?php echo $this->get_field_id('format') ?>-format" value="<?php echo esc_html($format) ?>" /></label>
<br ><?php
//printf(__('Accepted format arguments:%s - Flag Image HTML, "%s"%s - Flag Image URL%s - Language Native Name%s - Language Name in Active Language%s - Language 2-Letter Code%s', 'qtranslate'), '<ul><li>%f', '&lt;img ... /&gt;', '</li><li>%s', '</li><li>%n', '</li><li>%a', '</li><li>%c', '</ul>')
	echo __('Accepted format arguments:','qtranslate').'<ul>'.PHP_EOL;
	echo '<li>%f - '.__('Flag Image HTML','qtranslate').'</li>'.PHP_EOL;
	echo '<li>%s - '.__('Flag Image URL','qtranslate').'</li>'.PHP_EOL;
	echo '<li>%n - '.__('Language Native Name','qtranslate').'</li>'.PHP_EOL;
	echo '<li>%a - '.__('Language Name in Active Language','qtranslate').'</li>'.PHP_EOL;
	echo '<li>%c - '.__('Language 2-Letter Code','qtranslate').'</li>'.PHP_EOL;
	echo '</ul>';
?>
<small><?php printf(__('For example, format "%s" would do the same as the choice "%s".', 'qtranslate'), esc_html('%f<span>%n</span>'), __('Text and Image', 'qtranslate')) ?>&nbsp;
<?php _e('An appropriate custom CSS is expected to be provided in this case.', 'qtranslate') ?></small>
</p>
<p><label for="<?php echo $this->get_field_id('widget-css') ?>"><input type="checkbox" id="<?php echo $this->get_field_id('widget-css-on') ?>" name="<?php echo $this->get_field_name('widget-css-on') ?>" <?php checked($widget_css_on) ?>/><?php echo __('Widget CSS:', 'qtranslate') ?></label><br/><textarea class="widefat" rows="6" name="<?php echo $this->get_field_name('widget-css') ?>" id="<?php echo $this->get_field_id('widget-css') ?>"><?php echo esc_attr($widget_css) ?></textarea><br/><small><?php echo __('To reset to default, clear the text.', 'qtranslate').' '.__('To disable this inline CSS, clear the check box.', 'qtranslate').' '.sprintf(__('Other common CSS block for flag classes "%s" is loaded in the head of HTML and can be controlled with option "%s".', 'qtranslate'), 'qtranxs_flag_xx', __('Head inline CSS','qtranslate')) ?></small></p>
<?php
/*
<p><label for="<?php echo $this->get_field_id('type') ?>-short"><input type="radio" name="<?php echo $this->get_field_name('type') ?>" id="<?php echo $this->get_field_id('type') ?>-short" value="short"<?php echo ($type=='short')?' checked="checked"':'' ?>/> <?php _e('2-Letter Language Code', 'qtranslate') ?></label></p>
*/
	}
}

/**
 * Language Select Code for non-Widget users
 * @args is a hash array of options, which accepts the following keys:
 *   ‘type’ – one of the values: ‘text’, ‘image’, ‘both’, ‘dropdown’ and ‘custom’, which match the choices on widget admin page.
 *   ‘format’ – needs to be provided if ‘type’ is ‘custom’. Read help text to this option on widget admin page.
 *   ‘id’ – id of widget, which is used as a distinctive string to create CSS entities.
 * @since 3.4.5 type of argument is changed, compatibility with old way is preserved.
*/
function qtranxf_generateLanguageSelectCode($args = array(), $id='') {
	global $q_config;
	if(is_string($args)) $type = $args;
	elseif(is_bool($args)&&$args) $type='image';
	elseif(is_array($args)){
		if(!empty($args['type'])) $type = $args['type'];
		if(empty($id) && !empty($args['id'])) $id = $args['id'];
	}
	if(empty($type)) $type='text';
	else switch($type){
		case 'text':
		case 'image':
		case 'both':
		case 'short':
		case 'css_only':
		case 'custom':
		case 'dropdown': break;
		default: $type='text';
	}
	if(empty($id)) $id = 'qtranslate';
	$id .= '-chooser';
	if(is_404()) $url = get_option('home'); else $url = '';
	$flag_location=qtranxf_flag_location();
	echo PHP_EOL.'<ul class="language-chooser language-chooser-'.$type.' qtranxs_language_chooser" id="'.$id.'">'.PHP_EOL;
	switch($type) {
		case 'image':
		case 'text':
		case 'css_only':
		case 'dropdown': {
			foreach(qtranxf_getSortedLanguages() as $language) {
				$alt = $q_config['language_name'][$language].' ('.$language.')';
				$classes = array('lang-'.$language);
				if($language == $q_config['language']) $classes[] = 'active';
				echo '<li class="'. implode(' ', $classes) .'"><a href="'.qtranxf_convertURL($url, $language, false, true).'"';
				// set hreflang
				echo ' hreflang="'.$language.'"';
				echo ' title="'.$alt.'"';
				if($type=='image')
					echo ' class="qtranxs_image qtranxs_image_'.$language.'"';
				//	echo ' class="qtranxs_flag qtranxs_flag_'.$language.'"';
				elseif($type=='text')
					echo ' class="qtranxs_text qtranxs_text_'.$language.'"';
				elseif($type=='css_only')// to be removed
					echo ' class="qtranxs_css qtranxs_css_'.$language.'"';
				echo '>';
				if($type=='image') echo '<img src="'.$flag_location.$q_config['flag'][$language].'" alt="'.$alt.'" />';
				echo '<span';
				if($type=='image' || $type=='css_only') echo ' style="display:none"';
				echo '>'.$q_config['language_name'][$language].'</span>';
				echo '</a></li>'.PHP_EOL;
			}
			//echo '</ul><div class="qtranxs_widget_end"></div>'.PHP_EOL;
			if($type=='dropdown') {
				echo '<script type="text/javascript">'.PHP_EOL.'// <![CDATA['.PHP_EOL;
				echo "var lc = document.getElementById('".$id."');".PHP_EOL;
				echo "var s = document.createElement('select');".PHP_EOL;
				echo "s.id = 'qtranxs_select_".$id."';".PHP_EOL;
				echo "lc.parentNode.insertBefore(s,lc);".PHP_EOL;
				// create dropdown fields for each language
				foreach(qtranxf_getSortedLanguages() as $language) {
					echo qtranxf_insertDropDownElement($language, qtranxf_convertURL($url, $language, false, true), $id);
				}
				// hide html language chooser text
				echo "s.onchange = function() { document.location.href = this.value;}".PHP_EOL;
				echo "lc.style.display='none';".PHP_EOL;
				echo '// ]]>'.PHP_EOL.'</script>'.PHP_EOL;
			}
		} break;
		case 'both':{
			foreach(qtranxf_getSortedLanguages() as $language) {
				$alt = $q_config['language_name'][$language].' ('.$language.')';
				echo '<li';
				if($language == $q_config['language'])
					echo ' class="active"';
				echo '><a href="'.qtranxf_convertURL($url, $language, false, true).'"';
				echo ' class="qtranxs_flag_'.$language.' qtranxs_flag_and_text" title="'.$alt.'">';
				//echo '<img src="'.$flag_location.$q_config['flag'][$language].'"></img>';
				echo '<span>'.$q_config['language_name'][$language].'</span></a></li>'.PHP_EOL;
			}
		} break;
		case 'short': {// undocumented, to be removed
			foreach(qtranxf_getSortedLanguages() as $language) {
				$alt = $q_config['language_name'][$language].' ('.$language.')';
				echo '<li';
				if($language == $q_config['language'])
					echo ' class="active"';
				echo '><a href="'.qtranxf_convertURL($url, $language, false, true).'"';
				echo ' class="qtranxs_short_'.$language.' qtranxs_short" title="'.$alt.'">';
				echo '<span>'.$language.'</span></a></li>'.PHP_EOL;
			}
		} break;
		case 'custom': {
			$format = isset($args['format']) ? $args['format'] : '';
			foreach(qtranxf_getSortedLanguages() as $language) {
				$alt = $q_config['language_name'][$language].' ('.$language.')';
				$s = $flag_location.$q_config['flag'][$language];
				$n = $q_config['language_name'][$language];
				$content = $format;
				$content = str_replace('%f', '<img src="'.$s.'" alt="'.$alt.'" />', $content);
				$content = str_replace('%s', $s, $content);
				$content = str_replace('%n', $n, $content);
				if(strpos($content,'%a')!==FALSE){
					$a = qtranxf_getLanguageName($language);//this is an expensive function, do not call without necessity.
					$content = str_replace('%a', $a==$n ? '' : $a, $content);
				}
				$content = str_replace('%c', $language, $content);
				$classes = array('language-chooser-item', 'language-chooser-item-'.$language);
				if($language == $q_config['language']) $classes[] = 'active';
				echo '<li class="'.implode(' ', $classes).'"><a href="' . qtranxf_convertURL($url, $language, false, true) . '" title="'.$alt.'">' . $content . '</a></li>'.PHP_EOL;
			}
		} break;
	}
	echo '</ul><div class="qtranxs_widget_end"></div>'.PHP_EOL;
}

function qtranxf_widget_init() {
	register_widget('qTranslateXWidget');
	do_action('qtranslate_widget_init');
}
