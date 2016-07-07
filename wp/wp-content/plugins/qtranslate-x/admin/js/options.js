/* executed for
 /wp-admin/options-general.php?page=qtranslate-x
*/
jQuery(document).ready(
function($){
	var getcookie = function(cname)
	{
		var nm = cname + "=";
		var ca = document.cookie.split(';');
		for(var i=0; i<ca.length; i++) {
			var ce = ca[i];
			var p = ce.indexOf(nm);
			if (p >= 0) return ce.substring(p+nm.length,ce.length);
		}
		return '';
	}
	var setFormAction = function(hash){
		var f = jQuery('#qtranxs-configuration-form');
		var a = f.attr('action');
		a = a.replace(/(#.*|$)/,hash);
		f.attr('action',a);
	}
	var switchTabTo = function(anchor,hash) {
		// active tab
		anchor.parent().children().removeClass('nav-tab-active');
		anchor.addClass('nav-tab-active');
		// active tab content
		var tabcontents = $('.tabs-content');
		tabcontents.children().addClass('hidden');
		var tab_id = hash.replace('#', '#tab-');
		tabcontents.find('div' + tab_id).removeClass('hidden');
		setFormAction(hash);
		document.cookie='qtrans_admin_section='+hash;
	}
	var onHashChange = function(hash_default) {
		var tabs = $('.nav-tab-wrapper');
		if(!tabs || !tabs.length) return;
		var hash = window.location.hash;
		if (!hash){
			hash = getcookie('qtrans_admin_section');
			if (!hash){
				if(!hash_default) return;
				hash = hash_default;
			}
		}
		var anchor = tabs.find('a[href="' + hash + '"]');
		while(!anchor || !anchor.length){
			if(window.location.hash){
				hash = getcookie('qtrans_admin_section');
				if(hash){
					anchor = tabs.find('a[href="' + hash + '"]');
					if(anchor && anchor.length) break;
				}
			}
			if(!hash_default) return;
			hash = hash_default;
			anchor = tabs.find('a[href="' + hash + '"]');
			if(anchor && anchor.length) break;
			return;
		}
		switchTabTo(anchor,hash);
	}
	$(window).bind('hashchange', function(e){ onHashChange(); });
	onHashChange('#general');
});

/* // Unnecessary as Show/Hide is obsolete
function qtranxj_getcookie(cname)
{
	var nm = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var ce = ca[i];
		var p = ce.indexOf(nm);
		if (p >= 0) return ce.substring(p+nm.length,ce.length);
	}
	return '';
}
function qtranxj_delcookie(cname)
{
	var date = new Date();
	date.setTime(date.getTime()-(24*60*60*1000));
	document.cookie=cname+'=; expires='+date.toGMTString();
}
function qtranxj_readShowHideCookie(id) {
	var e=document.getElementById(id);
	if(!e) return;
	if(qtranxj_getcookie(id)){
		e.style.display='block';
	}else{
		e.style.display='none';
	}
}
function qtranxj_toggleShowHide(id) {
	var e = document.getElementById(id);
	if (e.style.display == 'block'){
		qtranxj_delcookie(id);
		e.style.display = 'none';
	}else{
		document.cookie=id+'=1';
		e.style.display='block';
	}
	return false;
}
*/
