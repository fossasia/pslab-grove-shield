/* executed for
 /wp-admin/post.php
 /wp-admin/post-new.php
*/
jQuery(document).ready(
function($){
	var qtx = qTranslateConfig.js.get_qtx();
	//co('post.php: qtx: ',qtx);

	// Slug
	var convertURL = function(url,lang)
	{
		switch (qTranslateConfig.url_mode.toString())
		{
		case '1':
			if (url.search){
				url.search += '&lang='+lang;
			}else{
				url.search = '?lang='+lang;
			}
			break;
		case '2':
			//if( !qTranslateConfig.hide_default_language || qTranslateConfig.default_language != lang){
			var homepath=qTranslateConfig.home_url_path;
			var p = url.pathname;
			if(p[0] != '/') p = '/'+p;//to deal with IE imperfection: http://stackoverflow.com/questions/956233/javascript-pathname-ie-quirk
			var i=p.indexOf(homepath);
			if(i >= 0)
				url.pathname=qTranslateConfig.homeinfo_path+lang+p.substring(i+homepath.length-1);
			//}
			break;
		case '3':
			url.host = lang+'.'+url.host;
			break;
		case '4':
			url.host = qTranslateConfig.domains[lang];
			break;
		}
	}

	var btnViewPostA;//a node of 'View Page/Post' link.
	var origUrl, langUrl, origUrlQ;
	var slugSamplePermalink;//'sample-permalink' node
	var origSamplePermalink;
	var view_link;
	var permalink_query_field;
	var setSlugLanguage=function(lang)
	{
		if(!btnViewPostA){
			var btnViewPost=document.getElementById('view-post-btn');
			if (!btnViewPost || !btnViewPost.children.length) return;
			btnViewPostA=btnViewPost.children[0];
			if(btnViewPostA.tagName != 'A') return;
			origUrl=btnViewPostA.href;
			langUrl=qtranxj_ce('a',{});
			origUrlQ = origUrl.search(/\?/) > 0;
		}

		langUrl.href=origUrl;
		convertURL(langUrl,lang);
		btnViewPostA.href=langUrl.href;

		var btnPreviewAction=document.getElementById('preview-action');
		if (btnPreviewAction && btnPreviewAction.children.length)
		{
			btnPreviewAction.children[0].href = langUrl.href;
		}

		if(qTranslateConfig.url_mode!=1){// !QTX_URL_QUERY
			if(!slugSamplePermalink){
				var slugEl=document.getElementById('sample-permalink');
				if (slugEl && slugEl.offsetHeight > 0 && slugEl.childNodes.length){
					slugSamplePermalink=slugEl.childNodes[0];//span
					origSamplePermalink=slugSamplePermalink.nodeValue;
					//var slugEdit=document.getElementById('editable-post-name');
				}
			}
			if(slugSamplePermalink){
				langUrl.href=origSamplePermalink;
				convertURL(langUrl,lang);
				slugSamplePermalink.nodeValue=langUrl.href;
			}
		}else{// QTX_URL_QUERY
			if(!permalink_query_field){
				$('#sample-permalink').append('<span id="sample-permalink-lang-query"></span>');
				permalink_query_field = $('#sample-permalink-lang-query');
			}
			if(permalink_query_field){
				permalink_query_field.text( (origUrl.search(/\?/) < 0 ? '/?lang=' : '&lang=')+lang );
			}
		}

		if(!view_link) view_link = document.getElementById('wp-admin-bar-view');
		if(view_link && view_link.children.length){
			view_link.children[0].href = btnViewPostA.href;
		}
	}

	//handle prompt text of empty field 'title', not important
	var field_title = jQuery('#title');
	var title_label = jQuery('#title-prompt-text');
	var hide_title_prompt_text=function(lang)
	{
		var value = field_title.attr('value');
		//co('hide_title_prompt_text: title.value: ',value);
		if(value){
			title_label.addClass('screen-reader-text');
		}else{
			title_label.removeClass('screen-reader-text');
		}
		//jQuery('#title-prompt-text').remove();//ok
		//this.delLanguageSwitchAfterListener(hide_title_prompt_text);//ok
	}

	qtx.addCustomContentHooks();//handles values of option 'Custom Fields'
	setSlugLanguage(qtx.getActiveLanguage());

	qtx.addLanguageSwitchAfterListener(setSlugLanguage);

	if(title_label && field_title){
		qtx.addLanguageSwitchAfterListener(hide_title_prompt_text);
	}
});
