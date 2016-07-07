/* executed for 
 /wp-admin/edit-tags.php (without action=edit)
*/
jQuery(document).ready(
function(){
	var qtx = qTranslateConfig.js.get_qtx();

	var form = document.getElementById('addtag');//AjaxForm
	if(!form) return;

	var h=qtx.addContentHookByIdB('tag-name');
	if(!h) return;

	qtranxj_ce('input', {name: 'qtrans_term_field_name', type: 'hidden', className: 'hidden', value: h.name }, form, true);

	//var default_name = h.contents[qTranslateConfig.default_language];
	var default_name = h.fields[qTranslateConfig.default_language].value;
	qtranxj_ce('input', {name: 'qtrans_term_field_default_name', type: 'hidden', className: 'hidden', value: default_name }, form, true);

	//var theList=document.getElementById('the-list');

	//remove "Quick Edit" links for now
	jQuery('#the-list > tr > td.name span.inline').css('display','none');

	//make page to reload page on submit of a new taxonomy
	var submit_button = document.getElementById('submit');
	if(submit_button){
		submit_button.addEventListener('click',function(){
				setTimeout(function(){window.location.reload();},800);
				//addDisplayHookRows(theList);//does not work, because the updates on theList has not yet propagated
			});
	}

});
