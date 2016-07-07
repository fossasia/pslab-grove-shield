/* executed for 
 /wp-admin/edit-tags.php?action=edit
*/
jQuery(document).ready(
function(){
	var qtx = qTranslateConfig.js.get_qtx();

	var form = document.getElementById('edittag');
	if(!form) return false;

	var h=qtx.addContentHookByIdB('name');
	if(!h) return false;

	qtranxj_ce('input', {name: 'qtrans_term_field_name', type: 'hidden', className: 'hidden', value: h.name }, form, true);

	//var default_name=h.contents[qTranslateConfig.default_language];
	var default_name=h.fields[qTranslateConfig.default_language].value;
	qtranxj_ce('input', {name: 'qtrans_term_field_default_name', type: 'hidden', className: 'hidden', value: default_name }, form, true);
});
