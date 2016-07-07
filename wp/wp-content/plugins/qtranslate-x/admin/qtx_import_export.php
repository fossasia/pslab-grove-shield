<?php
if ( !defined( 'ABSPATH' ) ) exit;

/* function qtranxf_get_plugin_name($slug){
	//there must be a better way to take it from file?
	switch($slug) {
		case 'qtranslate': return 'qTranslate';
		case 'mqtranslate': return 'mqTranslate';
		case 'qtranslate-xp': return 'qTranslate Plus';
		case 'ztranslate': return 'zTranslate';
		case 'sitepress-multilingual-cms': return 'WPML';
		default: return apply_filters('qtranslate_get_plugin_name', '', $slug);
	}
}// */

function qtranxf_migrate_options_update($nm_to,$nm_from)
{
	global $wpdb;
	$option_names = $wpdb->get_col("SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE '$nm_to\_%'");
	foreach ($option_names as $name)
	{
		if(strpos($name,'_flag_location')>0) continue;
		$nm = str_replace($nm_to,$nm_from,$name);
		$value=get_option($nm);
		if($value===FALSE) continue;
		update_option($name,$value);
	}
}

function qtranxf_migrate_options_copy($nm_to,$nm_from)
{
	global $wpdb;
	$options = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE `option_name` LIKE '$nm_from\_%'");
	foreach ($options as $option)
	{
		$name = $option->option_name;
		//skip new qTranslate-X specific options
		// It is now easier to list options which need to be copied, instead.
		switch($name){
			case 'qtranslate_flag_location':
			case 'qtranslate_admin_notices':
			case 'qtranslate_domains':
			case 'qtranslate_editor_mode':
			case 'qtranslate_custom_fields':
			case 'qtranslate_custom_field_classes':
			case 'qtranslate_text_field_filters':
			case 'qtranslate_qtrans_compatibility':
			case 'qtranslate_header_css_on':
			case 'qtranslate_header_css':
			case 'qtranslate_filter_options_mode':
			case 'qtranslate_filter_options':
			case 'qtranslate_highlight_mode':
			case 'qtranslate_highlight_mode_custom_css':
			case 'qtranslate_lsb_style':
			case 'qtranslate_lsb_style_wrap_class':
			case 'qtranslate_lsb_style_active_class':
			case 'qtranslate_custom_i18n_config':
			case 'qtranslate_config_files':
			case 'qtranslate_page_configs':
			case 'qtranslate_admin_config':
			case 'qtranslate_front_config':
				continue;
			default: break;
		}
		//if(strpos($name,'_flag_location')>0) continue;
		$value = maybe_unserialize($option->option_value);
		if(strpos($name,'_flag_location')>0) continue;
		$nm = str_replace($nm_from,$nm_to,$name);
		update_option($nm,$value);
	}
	//save enabled languages
	global $q_config, $qtranslate_options;
	foreach($qtranslate_options['languages'] as $nm => $opn){
		$op = str_replace($nm_from,$nm_to,$opn);
		update_option($op,$q_config[$nm]);
	}
}

function qtranxf_migrate_import_mqtranslate(){
	qtranxf_migrate_import('mqTranslate','mqtranslate');
	//qtranxf_migrate_options_update('qtranslate','mqtranslate');
	update_option('qtranslate_qtrans_compatibility', '1');//since 3.1
	$nm = '<span style="color:blue"><strong>mqTranslate</strong></span>';
	qtranxf_add_message(sprintf(__('Option "%s" has also been turned on, as the most common case for importing configuration from %s. You may turn it off manually if your setup does not require it. Refer to %sFAQ%s for more information.', 'qtranslate'), '<span style="color:magenta">'.__('Compatibility Functions', 'qtranslate').'</span>', $nm, '<a href="https://qtranslatexteam.wordpress.com/faq/#CompatibilityFunctions" target="_blank">', '</a>'));
}
//function qtranxf_migrate_export_mqtranslate(){ qtranxf_migrate_options_copy('mqtranslate','qtranslate'); }
function qtranxf_migrate_export_mqtranslate(){ qtranxf_migrate_export('mqTranslate','mqtranslate'); }

//function qtranxf_migrate_import_qtranslate_xp(){ qtranxf_migrate_options_update('qtranslate','ppqtranslate'); }
//function qtranxf_migrate_export_qtranslate_xp(){ qtranxf_migrate_options_copy('ppqtranslate','qtranslate'); }
function qtranxf_migrate_import_qtranslate_xp(){ qtranxf_migrate_import('qTranslate Plus','ppqtranslate'); }
function qtranxf_migrate_export_qtranslate_xp(){ qtranxf_migrate_export('qTranslate Plus','ppqtranslate'); }

function qtranxf_migrate_import($plugin_name, $nm_from){
	qtranxf_migrate_options_update('qtranslate', $nm_from);

	$nm = '<span style="color:blue"><strong>'.$plugin_name.'</strong></span>';

	qtranxf_add_message(sprintf(__('Applicable options and taxonomy names from plugin %s have been imported. Note that the multilingual content of posts, pages and other objects has not been altered during this operation. There is no additional operation needed to import content, since its format is compatible with %s.', 'qtranslate'), $nm, 'qTranslate&#8209;X').' '.sprintf(__('It might be a good idea to review %smigration instructions%s, if you have not yet done so.', 'qtranslate'),'<a href="https://qtranslatexteam.wordpress.com/migration/" target="_blank">','</a>'));

	qtranxf_add_message(sprintf(__('%sImportant%s: Before you start making edits to post and pages, please, make sure that both, your front site and admin back-end, work under this configuration. It may help to review "%s" and see if any of conflicting plugins mentioned there are used here. While the current content, coming from %s, is compatible with this plugin, the newly modified posts and pages will be saved with a new square-bracket-only encoding, which has a number of advantages comparing to former %s encoding. However, the new encoding is not straightforwardly compatible with %s and you will need an additional step available under "%s" option if you ever decide to go back to %s. Even with this additional conversion step, the 3rd-party plugins custom-stored data will not be auto-converted, but manual editing will still work. That is why it is advisable to create a test-copy of your site before making any further changes. In case you encounter a problem, please give us a chance to improve %s, send the login information to the test-copy of your site to %s along with a detailed step-by-step description of what is not working, and continue using your main site with %s meanwhile. It would also help, if you share a success story as well, either on %sthe forum%s, or via the same e-mail as mentioned above. Thank you very much for trying %s.', 'qtranslate'), '<span style="color:red">', '</span>', '<a href="https://qtranslatexteam.wordpress.com/known-issues/" target="_blank">'.'Known Issues'.'</a>', $nm, 'qTranslate', $nm, '<a href="https://qtranslatexteam.wordpress.com/option-convert-database/" target="_blank"><span style="color:magenta">'.__('Convert Database', 'qtranslate').'</span></a>', $nm, 'qTranslate&#8209;X', '<a href="mailto:qtranslateteam@gmail.com">qtranslateteam@gmail.com</a>', $nm, '<a href="https://wordpress.org/support/plugin/qtranslate-x">', '</a>', 'qTranslate&#8209;X').'<br/><span style="font-size: smaller">'.__('This is a one-time message, which you will not see again, unless the same import is repeated.', 'qtranslate').'</span>');
}

function qtranxf_migrate_export($plugin_name, $nm_to){
	qtranxf_migrate_options_copy($nm_to, 'qtranslate');

	$nm = '<span style="color:blue"><strong>'.$plugin_name.'</strong></span>';

	qtranxf_add_message(sprintf(__('Applicable options have been exported to plugin %s. If you have done some post or page updates after migrating from %s, then "%s" operation is also required to convert the content to "dual language tag" style in order for plugin %s to function.', 'qtranslate'), $nm, $nm, '<a href="https://qtranslatexteam.wordpress.com/option-convert-database/" target="_blank"><span style="color:magenta">'.__('Convert Database', 'qtranslate').'</span></a>', $nm));
}

/* function qtranxf_migrate_plugin($plugin){
	$var=$plugin.'-migration';
	if(!isset($_POST[$var])) return;
	$action = $_POST[$var];
	if($action=='none') return;
	$f='qtranxf_migrate_'.$_POST[$var].'_'.str_replace('-','_',$plugin);
	$f();
	if( $action == 'export' ) return;
	//if( $plugin == 'mqtranslate' )//since 3.2-b2: moved to qtranxf_migrate_import_mqtranslate
	//	update_option('qtranslate_qtrans_compatibility', '1');
	qtranxf_reloadConfig();
}
*/

function qtranxf_migrate_plugins(){
	if(!current_user_can('manage_options')) return;
	//qtranxf_migrate_plugin('mqtranslate');
	//qtranxf_migrate_plugin('qtranslate-xp');
	////qtranxf_migrate_plugin('ztranslate');//ok same db
	////do_action('qtranslate_migrate_plugins');
	foreach($_POST as $key => $value){
		if(!is_string($value)) continue;
		if($value == 'none') continue;
		if(!qtranxf_endsWith($key,'-migration')) continue;
		$plugin = substr($key,0,-strlen('-migration'));
		$f = 'qtranxf_migrate_'.$value.'_'.str_replace('-','_',$plugin);
		if(!function_exists($f)) continue;
		$f();
		if($value == 'import'){
			qtranxf_reloadConfig();
		//}elseif($value == 'export'){
		}
	}
}
add_action('qtranslate_saveConfig','qtranxf_migrate_plugins',30);

function qtranxf_add_row_migrate($nm,$plugin,$args=null) {
	$plugin_file = qtranxf_find_plugin_file($plugin);
	if(!$plugin_file) return;
	//$pd = get_plugin_data( $plugin_file.'/mqtranslate.php', false, true );
	//qtranxf_dbg_log('qtranxf_add_row_migrate: $pd:',$pd);
	$href = isset($args['href']) ? $args['href'] : 'https://wordpress.org/plugins/'.$plugin;
?>
<tr valign="top" id="qtranslate-<?php echo $plugin; ?>">
	<th scope="row"><?php _e('Plugin') ?> <a href="<?php echo $href; ?>/" target="_blank"><?php echo $nm; ?></a></th>
	<td>
<?php
	if(!empty($args['compatible'])){
		_e('There is no need to migrate any setting, the database schema is compatible with this plugin.', 'qtranslate');
	}else if(!empty($args['text'])){
		echo $args['text'];
	}else{
?>
		<label for="<?php echo $plugin; ?>_no_migration"><input type="radio" name="<?php echo $plugin; ?>-migration" id="<?php echo $plugin; ?>_no_migration" value="none" checked /> <?php _e('Do not migrate any setting', 'qtranslate') ?></label>
		<br/>
		<label for="<?php echo $plugin; ?>_import_migration"><input type="radio" name="<?php echo $plugin; ?>-migration" id="<?php echo $plugin; ?>_import_migration" value="import" /> <?php echo __('Import settings from ', 'qtranslate').$nm; ?></label>
<?php if(empty($args['no_export'])){ ?>
		<br/>
		<label for="<?php echo $plugin; ?>_export_migration"><input type="radio" name="<?php echo $plugin; ?>-migration" id="<?php echo $plugin; ?>_export_migration" value="export" /> <?php echo __('Export settings to ', 'qtranslate').$nm; ?></label>
<?php }
	}
	if(!empty($args['note'])){
		echo '<p class="qtranxs_notes">'.$args['note'].'</p>';
	}
?>
	</td>
</tr>
<?php
}

function qtranxf_admin_section_import_export($request_uri)
{
	//echo '<div class="tabs-content">';
	qtranxf_admin_section_start('import');
?>
	<table class="form-table">
		<tr valign="top" id="qtranslate-convert-database">
			<th scope="row"><?php _e('Convert Database', 'qtranslate') ?></th>
			<td>
				<?php printf(__('If you are updating from qTranslate 1.x or Polyglot, <a href="%s">click here</a> to convert posts to the new language tag format.', 'qtranslate'), $request_uri.'&convert=true#import') ?>
				<?php printf(__('If you have installed qTranslate for the first time on a Wordpress with existing posts, you can either go through all your posts manually and save them in the correct language or <a href="%s">click here</a> to mark all existing posts as written in the default language.', 'qtranslate'), $request_uri.'&markdefault=true#import') ?>
				<?php _e('Both processes are <b>irreversible</b>! Be sure to make a full database backup before clicking one of the links.', 'qtranslate') ?><br/><br/>
				<label for="qtranxs_convert_database_none"><input type="radio" name="convert_database" id="qtranxs_convert_database_none" value="none" checked />&nbsp;<?php _e('Do not convert database', 'qtranslate') ?></label><br/><br/>
				<label for="qtranxs_convert_database_to_b_only"><input type="radio" name="convert_database" id="qtranxs_convert_database_to_b_only" value="b_only" />&nbsp;<?php echo __('Convert database to the "square bracket only" style.', 'qtranslate') ?></label><br/>
				<small><?php printf(__('The square bracket language tag %s only will be used as opposite to dual-tag (%s and %s) %s legacy database format. All string options and standard post and page fields will be uniformly encoded like %s.','qtranslate'),'[:]',esc_html('<!--:-->'),'[:]','qTranslate','"[:en]English[:de]Deutsch[:]"') ?></small><br/><br/>
				<label for="qtranxs_convert_database_to_c_dual"><input type="radio" name="convert_database" id="qtranxs_convert_database_to_c_dual" value="c_dual" />&nbsp;<?php echo __('Convert database back to the legacy "dual language tag" style.', 'qtranslate') ?></label><br/>
				<small><?php _e('Note, that only string options and standard post and page fields are affected.','qtranslate') ?></small>
			</td>
		</tr>
		<?php qtranxf_add_row_migrate('qTranslate','qtranslate', array('compatible' => true)) ?>
		<?php qtranxf_add_row_migrate('mqTranslate','mqtranslate') ?>
		<?php qtranxf_add_row_migrate('qTranslate Plus','qtranslate-xp') ?>
		<?php qtranxf_add_row_migrate('zTranslate','ztranslate', array('compatible' => true)) ?>
		<?php qtranxf_add_row_migrate('WPML Multilingual CMS','sitepress-multilingual-cms', array('href' => 'https://wpml.org', 'text' => sprintf(__('Use plugin %s to import data.', 'qtranslate'), '<a href="https://wordpress.org/plugins/w2q-wpml-to-qtranslate/" target="_blank">W2Q: WPML to qTranslate</a>'))) ?>
		<?php do_action('qtranslate_add_row_migrate') ?>
		<tr valign="top">
			<th scope="row"><?php _e('Reset qTranslate', 'qtranslate') ?></th>
			<td>
				<label for="qtranslate_reset"><input type="checkbox" name="qtranslate_reset" id="qtranslate_reset" value="1"/> <?php _e('Check this box and click Save Changes to reset all qTranslate settings.', 'qtranslate') ?></label>
				<br/>
				<label for="qtranslate_reset2"><input type="checkbox" name="qtranslate_reset2" id="qtranslate_reset2" value="1"/> <?php _e('Yes, I really want to reset qTranslate.', 'qtranslate') ?></label>
				<br/>
				<label for="qtranslate_reset3"><input type="checkbox" name="qtranslate_reset3" id="qtranslate_reset3" value="1"/> <?php _e('Also delete Translations for Categories/Tags/Link Categories.', 'qtranslate') ?></label>
				<br/>
				<small><?php _e('If something isn\'t working correctly, you can always try to reset all qTranslate settings. A Reset won\'t delete any posts but will remove all settings (including all languages added).', 'qtranslate') ?></small>
				<br/>
				<label for="qtranslate_reset_admin_notices"><input type="checkbox" name="qtranslate_reset_admin_notices" id="qtranslate_reset_admin_notices" value="1"/> <?php _e('Reset admin notices.', 'qtranslate') ?></label>
				<br/>
				<small><?php _e('All previously dismissed admin notices related to this plugin will show up again on next refresh of admin pages.', 'qtranslate') ?></small>
			</td>
		</tr>
	</table>
<?php
	qtranxf_admin_section_end('import');
	//echo '</div>';
}
add_action('qtranslate_configuration', 'qtranxf_admin_section_import_export', 9);
