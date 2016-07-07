/*
	Copyright 2014  qTranslate Team  (email : qTranslateTeam@gmail.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
/**
 * Search for 'Designed as interface for other plugin integration' in comments to functions
 * to find out which functions are safe to use in the 3rd-party integration.
 * Avoid accessing internal variables directly, as they are subject to be re-designed at any time.
 * Single global variable 'qTranslateConfig' is an entry point to the interface.
 * - qTranslateConfig.qtx - is a shorthand reference to the only global object of type 'qTranslateX'.
 * - qTranslateConfig.js - is a place where custom Java script functions are stored, if needed.
 * Read Integration Guide, https://qtranslatexteam.wordpress.com/integration/, for more information.
*/
/*
// debugging tools, do not check in
var cc=0;
function c(v){ ++cc; console.log('== '+cc+': '+v); }
function ct(v){ c(v); console.trace(); }
function co(t,o){ ++cc; console.log('== '+cc+': '+t+'%o',o); }
*/

/**
 * since 3.2.7
 */
qtranxj_get_split_blocks = function(text) {
	var split_regex = /(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})/gi; // @since 3.3.6 swirly brackets
	return text.xsplit(split_regex);
}

/**
 * since 3.2.7
 */
qtranxj_split = function(text) {
	var blocks = qtranxj_get_split_blocks(text);
	return qtranxj_split_blocks(blocks);
}

/**
 * since 3.1-b1 - closing tag [:]
 */
qtranxj_split_blocks = function(blocks) {
	var result = new Object;
	//for(var i=0; i<qTranslateConfig.enabled_languages.length; ++i)
	for(var lang in qTranslateConfig.language_config) {
		//var lang=qTranslateConfig.enabled_languages[i];
		result[lang] = '';
	}
	//if(!qtranxj_isArray(blocks))//since 3.2.7
	if(!blocks || !blocks.length)
		return result;
	if(blocks.length==1){ //no language separator found, enter it to all languages
		var b=blocks[0];
		//for(var j=0; j<qTranslateConfig.enabled_languages.length; ++j){
		for(var lang in qTranslateConfig.language_config){
			//var lang=qTranslateConfig.enabled_languages[j];
			result[lang] += b;
		}
		return result;
	}
	var clang_regex=/<!--:([a-z]{2})-->/gi;
	var blang_regex=/\[:([a-z]{2})\]/gi;
	var slang_regex=/\{:([a-z]{2})\}/gi; // @since 3.3.6 swirly brackets
	var lang = false;
	var matches;
	for(var i = 0;i<blocks.length;++i){
		var b=blocks[i];
		if(!b.length) continue;
		matches = clang_regex.exec(b); clang_regex.lastIndex=0;
		if(matches!=null){
			lang = matches[1];
			continue;
		}
		matches = blang_regex.exec(b); blang_regex.lastIndex=0;
		if(matches!=null){
			lang = matches[1];
			continue;
		}
		matches = slang_regex.exec(b); slang_regex.lastIndex=0;
		if(matches!=null){
			lang = matches[1];
			continue;
		}
		if( b == '<!--:-->' || b == '[:]' || b == '{:}' ){
			lang = false;
			continue;
		}
		if(lang){
			if(!result[lang]) result[lang] = b;
			else result[lang] += b;
			lang = false;
		}else{ //keep neutral text
			for(var key in result){
				result[key] += b;
			}
		}
	}
	return result;
}

function qtranxj_get_cookie(cname) {
	var nm = cname + "=";
	var ca = document.cookie.split(';');
	//c('ca='+ca);
	for(var i=0; i<ca.length; ++i){
		var s = ca[i];
		var sa = s.split('=');
		if(sa[0].trim()!=cname) continue;
		if(ca.length<2) continue;
		return sa[1].trim();
	}
	return '';
}

String.prototype.xsplit = function(_regEx){
	// Most browsers can do this properly, so let them work, they'll do it faster
	if ('a~b'.split(/(~)/).length === 3){ return this.split(_regEx); }

	if (!_regEx.global)
	{ _regEx = new RegExp(_regEx.source, 'g' + (_regEx.ignoreCase ? 'i' : '')); }

	// IE (and any other browser that can't capture the delimiter)
	// will, unfortunately, have to be slowed down
	var start = 0, arr=[];
	var result;
	while((result = _regEx.exec(this)) != null){
		arr.push(this.slice(start, result.index));
		if(result.length > 1) arr.push(result[1]);
		start = _regEx.lastIndex;
	}
	if(start < this.length) arr.push(this.slice(start));
	if(start == this.length) arr.push(''); //delim at the end
	return arr;
};

//Since 3.2.7 removed: function qtranxj_isArray(obj){ return obj.constructor.toString().indexOf('Array') >= 0; }

function qtranxj_ce(tagName, props, pNode, isFirst) {
	var el= document.createElement(tagName);
	if (props) {
		for(prop in props) {
			//try
			{
				el[prop]=props[prop];
			}
			//catch(err)
			{
				//Handle errors here
			}
		}
	}
	if (pNode) {
		if (isFirst && pNode.firstChild) {
			pNode.insertBefore(el, pNode.firstChild);
		}
		else {
			pNode.appendChild(el);
		}
	}
	return el;
}

var qTranslateX=function(pg) {
	var qtx = this;
	qTranslateConfig.qtx = this;

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * return array keyed by two-letter language code. Example of usage:
	 * var langs = getLanguages();
	 * for(var lang_code in langs){
	 *  var lang_conf = langs[lang_code];
	 *  // variables available:
	 *  //lang_conf.name
	 *  //lang_conf.flag
	 *  //lang_conf.locale
	 *  // and may be more properties later
	 * }
	 */
	this.getLanguages=function(){ return qTranslateConfig.language_config; }

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * return URL to folder with flag images.
	 */
	this.getFlagLocation=function(){ return qTranslateConfig.flag_location; }

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * return true if 'lang' is in the hash of enabled languages.
	 * This function maybe needed, as function qtranxj_split may return languages,
	 * which are not enabled, in case they were previously enabled and had some data.
	 * Such data is preserved and re-saved until user deletes it manually.
	 */
	this.isLanguageEnabled=function(lang){ return !!qTranslateConfig.language_config[lang]; }

	var setLangCookie=function(lang) { document.cookie='qtrans_edit_language='+lang; }

	qTranslateConfig.activeLanguage;
	if(qTranslateConfig.LSB){
		qTranslateConfig.activeLanguage = qtranxj_get_cookie('qtrans_edit_language');
		if(!qTranslateConfig.activeLanguage || !this.isLanguageEnabled(qTranslateConfig.activeLanguage)){
			qTranslateConfig.activeLanguage = qTranslateConfig.language;
			if(this.isLanguageEnabled(qTranslateConfig.activeLanguage)){
				setLangCookie(qTranslateConfig.activeLanguage);
			}else{//no languages are enabled
				qTranslateConfig.LSB = false;
			}
		}
	}else{
		qTranslateConfig.activeLanguage = qTranslateConfig.language;
		setLangCookie(qTranslateConfig.activeLanguage);
	}

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3
	 */
	this.getActiveLanguage = function() { return qTranslateConfig.activeLanguage; }
	//this.getActiveLanguageName = function() { return qTranslateConfig.language_name[qTranslateConfig.activeLanguage]; }

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3
	 */
	this.getLanguages = function() { return qTranslateConfig.language_config; }

	var contentHooks={};
	var contentHookId = 0;

	var updateFusedValueH=function(id,value) {
		var h = contentHooks[id];
		var text = value.trim();
		//c('updateFusedValueH['+id+'] lang='+h.lang+'; text:'+text);
		h.fields[h.lang].value = text;
	}

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3.4
	 */
	this.hasContentHook=function(id){ return contentHooks[id]; }

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3.2
	 */
	this.addContentHook=function(inpField,encode,field_name) {
		//co('addContentHook: inpField:',inpField);
		//co('addContentHook: encode:',encode);
		//co('addContentHook: field_name:',field_name);
		if( !inpField ) return false;
		switch(inpField.tagName){
			case 'TEXTAREA': break;
			case 'INPUT':
				//co('addContentHook: inpField.type=',inpField.type);
				//reject the types which cannot be multilingual
				switch (inpField.type) {
					case 'button':
					case 'checkbox':
					case 'password':
					case 'radio':
					case 'submit': return false;
				}
				//if(inpField.type.match(/(button|checkbox|password|radio|submit)/)) return false;
				break;
			default: return false;
		}
		if(!field_name){
			if( !inpField.name ) return false;
			field_name = inpField.name;
		}
		//if( typeof inpField.value !== 'string' ) return false;
		if(inpField.id){
			if(contentHooks[inpField.id]){
				if(jQuery.contains(document,inpField))
					return contentHooks[inpField.id];
				//otherwise some Java script already removed previously hooked element
				qtx.removeContentHook(inpField);
			}
		} else if (!contentHooks[field_name]) {
			inpField.id = field_name;
		} else {
			var idx = 0;
			do {
				++idx;
				inpField.id = field_name + idx;
			} while (contentHooks[field_name]);
			//jQuery(inpField).uniqueId();//does not work
			//jQuery(inpField).each(function (i,e) { e.uniqueId(); });//does not work
		}
		//co('addContentHook: id=',inpField.id);
		var h = contentHooks[inpField.id]={};
		//h.id = inpField.id;
		h.name = field_name;
		h.contentField=inpField;
		//c('addContentHook: inpField.value='+inpField.value);
		h.lang = qTranslateConfig.activeLanguage;
		var contents = qtranxj_split(inpField.value);//keep neutral text from older times, just in case.
		                        //inpField.tagName
		inpField.value = contents[h.lang];
		var qtx_prefix;
		if(encode){
			switch(encode){
				case 'slug': qtx_prefix = 'qtranslate-slugs['; break;
				case 'term': qtx_prefix = 'qtranslate-terms['; break;
				default: qtx_prefix = 'qtranslate-fields['; break;
			}
		}else{
			//if(inpField.tagName==='TEXTAREA')
			//	encode='<';
			//else
			encode = '[';//since 3.1 we get rid of <--:--> encoding
			qtx_prefix = 'qtranslate-fields[';
		}

		var bfnm, sfnm, p = h.name.indexOf('[');
		if(p<0){
			bfnm = qtx_prefix + h.name+']';
		}else{
			bfnm = qtx_prefix + h.name.substring(0,p)+']';
			if(h.name.lastIndexOf('[]') < 0){
				bfnm += h.name.substring(p);
			}else{
				var len = h.name.length-2;
				if(len > p) bfnm += h.name.substring(p,len);
				sfnm = '[]';
			}
		}
		h.fields={};
		for(var lang in contents){
			var text = contents[lang];
			var fnm = bfnm+'['+lang+']';
			if(sfnm) fnm += sfnm;
			var f = qtranxj_ce('input', {name: fnm, type: 'hidden', className: 'hidden', value: text});
			h.fields[lang] = f;
			inpField.parentNode.insertBefore(f,inpField);
		}
		
		// since 3.2.9.8 - h.contents -> h.fields
		// since 3.3.8.7 - slug & term
		switch(encode){
			case 'slug':
			case 'term':
				h.sepfield = qtranxj_ce('input', {name: bfnm+'[qtranslate-original-value]', type: 'hidden', className: 'hidden', value: contents[qTranslateConfig.default_language] }); break;
			default: h.sepfield = qtranxj_ce('input', {name: bfnm+'[qtranslate-separator]', type: 'hidden', className: 'hidden', value: encode }); break;
		}
		inpField.parentNode.insertBefore(h.sepfield,inpField);
		h.encode=encode;

		/**
		 * Highlighting the translatable fields
		 * @since 3.2-b3
		*/
		inpField.className += ' qtranxs-translatable';

		/*
		if(window.tinyMCE){
			//c('addContentHook: window.tinyMCE: tinyMCE.editors.length='+tinyMCE.editors.length);
			//tinyMCE.editors are not yet set up at this point.
			for(var i=0; i<tinyMCE.editors.length; ++i){
				var ed=tinyMCE.editors[i];
				if(ed.id != inpField.id) continue;
				//c('addContentHook: updateTinyMCE: ed.id='+ed.id);//never fired yet
				h.mce=ed;
				//updateTinyMCE(ed,text);
				updateTinyMCE(h);
			}
		}
		*/
		return h;
	}
	this.addContentHookC=function(inpField) { return qtx.addContentHook(inpField,'['); }//'<'
	this.addContentHookB=function(inpField) { return qtx.addContentHook(inpField,'['); }

	this.addContentHookById=function(id,sep,nm) { return qtx.addContentHook(document.getElementById(id),sep,nm); }
	this.addContentHookByIdName=function(nm) {
		var sep;
		//if(nm.indexOf('<')==0 || nm.indexOf('[')==0){
		switch(nm[0]){
			case '<':
			case '[':
				sep=nm.substring(0,1);
				nm=nm.substring(1);
				break;
			default: break;
		}
		return qtx.addContentHookById(nm,sep);
	}
	this.addContentHookByIdC=function(id) { return qtx.addContentHookById(id,'['); }//'<'
	this.addContentHookByIdB=function(id) { return qtx.addContentHookById(id,'['); }

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.1-b2
	*/
	this.addContentHooks=function(fields,sep,field_name) {
		for(var i=0; i<fields.length; ++i){
			var field = fields[i];
			qtx.addContentHook(field,sep,field_name);
		}
	}

	var addContentHooksByClassName=function(nm,container,sep) {
		if(!container) container=document;
		var fields=container.getElementsByClassName(nm);
		qtx.addContentHooks(fields,sep);
	}

	this.addContentHooksByClass=function(nm,container) {
		var sep;
		if(nm.indexOf('<')==0 || nm.indexOf('[')==0){
			sep=nm.substring(0,1);
			nm=nm.substring(1);
		}
		addContentHooksByClassName(nm,container,sep);
	}

	/** 
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3.2
	 */
	this.addContentHooksByTagInClass=function(nm,tag,container) {
		var elems=container.getElementsByClassName(nm);
		for(var i=0; i<elems.length; ++i){
			var elem=elems[i];
			var items=elem.getElementsByTagName(tag);
			qtx.addContentHooks(items);
		}
	}

	var removeContentHookH=function(h) {
		if(!h) return false;
		if(h.sepfield) jQuery(h.sepfield).remove();
		for(var lang in h.fields){
			jQuery(h.fields[lang]).remove();
		}
		delete contentHooks[h.contentField.id];
		return true;
	};

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3
	 */
	this.removeContentHook=function(inpField) {
		if( !inpField ) return false;
		if( !inpField.id ) return false;
		if( !contentHooks[inpField.id] ) return false;
		var h=contentHooks[inpField.id];
		removeContentHookH(h);
		/* @since 3.2.9.8 - h.contents -> h.fields
		inpField.onblur = function(){};
		inpField.name=inpField.name.replace(/^edit-/,'');
		inpField.value=h.mlContentField.value;
		jQuery(h.mlContentField).remove();
		*/
		jQuery(inpField).removeClass('qtranxs-translatable');
		return true;
	};

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * Re-create a hook, after a piece of HTML is dynamically replaced with a custom Java script.
	 */
	this.refreshContentHook=function(inpField) {
		if( !inpField ) return false;
		if( !inpField.id ) return false;
		var h = contentHooks[inpField.id];
		if( h ) removeContentHookH(h);
		return qtx.addContentHook(inpField);
	}

	/**
	 * @since 3.2.7
	 */
	var displayHookNodes=[];
	var addDisplayHookNode = function (nd) {
		if(!nd.nodeValue) return 0;
		var blocks = qtranxj_get_split_blocks(nd.nodeValue);
		if( !blocks || !blocks.length || blocks.length == 1 ) return 0;
		//co('addDisplayHookNode: nd: ',nd);
		//co('addDisplayHookNode: blocks: ',blocks);
		var h={};
		h.nd=nd;
		//co('addDisplayHookNode: nd=',nd);
		//c('addDisplayHookNode: nodeValue: "'+nd.nodeValue+'"');
		//c('addDisplayHookNode: content='+content);
		h.contents = qtranxj_split_blocks(blocks);
		nd.nodeValue=h.contents[qTranslateConfig.activeLanguage];
		displayHookNodes.push(h);
		return 1;
	}

	/**
	 * @since 3.2.7
	 */
	var displayHookAttrs=[];
	var addDisplayHookAttr = function (nd) {
		if(!nd.value) return 0;
		var blocks = qtranxj_get_split_blocks(nd.value);
		if( !blocks || !blocks.length || blocks.length == 1 ) return 0;
		//co('addDisplayHookAttr: nd: ',nd);
		var h={};
		h.nd=nd;
		h.contents = qtranxj_split_blocks(blocks);
		nd.value=h.contents[qTranslateConfig.activeLanguage];
		displayHookAttrs.push(h);
		return 1;
	}

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.2.7 switched to use of nodeValue instead of innerHTML.
	 */
	this.addDisplayHook = function (elem) {
		//co('addDisplayHook: this: ',this);
		if(!elem || !elem.tagName) return 0;
		switch(elem.tagName){
			case 'TEXTAREA': return 0;
			case 'INPUT':
				switch(elem.type){
					case 'submit': if(elem.value) return addDisplayHookAttr(elem);
					default: return 0;
				}
			default: break;
		}
		//co('addDisplayHook: elem: ',elem);
		var cnt = 0;
		if(elem.childNodes && elem.childNodes.length){
			for(var i = 0; i < elem.childNodes.length; ++i){
				var nd = elem.childNodes[i];
				switch(nd.nodeType){//http://www.w3.org/TR/REC-DOM-Level-1/level-one-core.html#ID-1950641247
					case 1://ELEMENT_NODE
						cnt += qtx.addDisplayHook(nd);//recursive call
						break;
					case 2://ATTRIBUTE_NODE
					case 3://TEXT_NODE
						cnt += addDisplayHookNode(nd);
						break;
					default: break;
				}
			}
		}
		return cnt;
	}

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.0
	 */
	this.addDisplayHookById=function(id) { return qtx.addDisplayHook(document.getElementById(id)); }

	var updateTinyMCE = function (h) {
		text = h.contentField.value;
		//co('updateTinyMCE: window.switchEditors: ',window.switchEditors);
		//c('updateTinyMCE: text:'+text);
		if(h.wpautop && window.switchEditors){
			//text = window.switchEditors.pre_wpautop( text );
			text = window.switchEditors.wpautop(text);
			//c('updateTinyMCE:wpautop:'+text);
		}
		h.mce.setContent(text,{format: 'html'});
	}

	var onTabSwitch = function (lang) {
		//var qtx = this;
		setLangCookie(lang);
		for(var i = displayHookNodes.length; --i >= 0; ){
			var h=displayHookNodes[i];
			if(h.nd.parentNode){
				h.nd.nodeValue = h.contents[lang];//IE gets upset here if node was removed
			}else{
				displayHookNodes.splice(i,1);//node was removed by some other function
			}
		}
		for(var i = displayHookAttrs.length; --i >= 0;){
			var h=displayHookAttrs[i];
			if(h.nd.parentNode){
				h.nd.value = h.contents[lang];
			}else{
				displayHookAttrs.splice(i,1);//node was removed by some other function
			}
		}
		for(var key in contentHooks){
			var h=contentHooks[key];
			var mce = h.mce && !h.mce.hidden;
			if(mce){
				h.mce.save({format: 'html'});
			}
			h.fields[h.lang].value = h.contentField.value;
			h.lang = lang;
			var value = h.fields[h.lang].value;
			if(h.contentField.placeholder && value != ''){//since 3.2.7
				h.contentField.placeholder='';
			}
			h.contentField.value = value;
			if(mce){
				updateTinyMCE(h);
			}
		}
	}

/*
	onTabSwitchCustom=function()
	{
		//co('onTabSwitch: this',this);
		//co('onTabSwitch: qtx',qTranslateConfig.qtx);
		pg.onTabSwitch(this.lang,qTranslateConfig.qtx);
	}
*/

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.0
	 */
	this.addDisplayHooks = function (elems) {
		//c('addDisplayHooks: elems.length='+elems.length);
		for(var i=0; i<elems.length; ++i){
			var e=elems[i];
			//co('addDisplayHooks: e=',e);
			//co('addDisplayHooks: e.tagName=',e.tagName);
			qtx.addDisplayHook(e);
		}
	}

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3
	 */
	this.addDisplayHooksByClass = function (nm, container) {
		//co('addDisplayHooksByClass: container:',container);
		var elems=container.getElementsByClassName(nm);
		//co('addDisplayHooksByClass: elems('+nm+'):',elems);
		//co('addDisplayHooksByClass: elems.length=',elems.length);
		qtx.addDisplayHooks(elems);
	}

	/**
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 *
	 * @since 3.3
	 */
	this.addDisplayHooksByTagInClass = function (nm, tag, container) {
		var elems=container.getElementsByClassName(nm);
		//c('addDisplayHooksByClass: elems.length='+elems.length);
		for(var i=0; i<elems.length; ++i){
			var elem=elems[i];
			var items=elem.getElementsByTagName(tag);
			qtx.addDisplayHooks(items);
		}
	}


	/**
	 * adds custom hooks from configuration
	 * @since 3.1-b2 - renamed to addCustomContentHooks, since addContentHooks used in qTranslateConfig.js
	 * @since 3.0 - addContentHooks
	*/
	this.addCustomContentHooks = function () {
		//c('qTranslateConfig.custom_fields.length='+qTranslateConfig.custom_fields.length);
		for(var i=0; i<qTranslateConfig.custom_fields.length; ++i){
			var nm=qTranslateConfig.custom_fields[i];
			qtx.addContentHookByIdName(nm);
		}
		for(var i=0; i<qTranslateConfig.custom_field_classes.length; ++i){
			var nm=qTranslateConfig.custom_field_classes[i];
			qtx.addContentHooksByClass(nm);
		}
		if(qTranslateConfig.LSB)
			setTinyMceInit();
	}

	/**
	 * adds translatable hooks for fields marked with classes
	 * i18n-multilingual
	 * i18n-multilingual-curly
	 * i18n-multilingual-term
	 * i18n-multilingual-slug
	 * i18n-multilingual-display
	 * @since 3.4
	*/
	var addMultilingualHooks = function ($) {
		$('.i18n-multilingual').each(function(i,e){ qtx.addContentHook(e,'['); });
		$('.i18n-multilingual-curly').each(function(i,e){ qtx.addContentHook(e,'{'); });
		$('.i18n-multilingual-term').each(function(i,e){ qtx.addContentHook(e,'term'); });
		$('.i18n-multilingual-slug').each(function(i,e){ qtx.addContentHook(e,'slug'); });
		$('.i18n-multilingual-display').each(function(i,e){ qtx.addDisplayHook(e); });
	}

	/**
	 * Parses page configuration, loaded in qtranxf_get_admin_page_config_post_type.
	 * @since 3.1-b2
	*/
	var addPageHooks = function (page_config_forms) {
		for(var form_id in page_config_forms){
			var frm = page_config_forms[form_id];
			var form;
			if(frm.form){
				if(frm.form.id){
					form = document.getElementById(frm.form.id);
				}else if(frm.form.jquery){
					form = $(frm.form.jquery);
				}else if(frm.form.name){
					var elms = document.getElementsByName(frm.form.name);
					if(elms && elms.length){
						form = elms[0];
					//}else{
					//	alert('qTranslate-X misconfiguraton: form with name "'+frm.form.name+'" is not found.');
					}
				}
			}else{
				form = document.getElementById(form_id);
			}
			if(!form){
				form = getWrapForm();
				if(!form) form = document;
			}
			//co('form=',form);
			//c('frm.fields.length='+frm.fields.length);
			for(var handle in frm.fields){
				var fld = frm.fields[handle];
				//co('fld['+handle+']: ',fld);
				//c('encode='+fld.encode);
				//c('id='+fld.id);
				//c('class='+fld.class);
				var containers=[];
				if(fld.container_id){
					var container = document.getElementById(fld.container_id);
					if(container) containers.push(container);
				}else if(fld.container_jquery){
					containers = $(fld.container_jquery);
				}else if(fld.container_class){
					containers = document.getElementsByClassName(fld.container_class);
				}else{// if(form){
					containers.push(form);
				}
				var sep = fld.encode;
				switch( sep ){
					case 'none': continue;
					case 'display':
						if(fld.jquery){
							for(var i=0; i < containers.length; ++i){
								var container = containers[i];
								//co('addPageHooks:display: container: ',container);
								//$(container).find(fld.jquery).each(function(i,e){qtx.addDisplayHook(e);});//also ok
								var fields = jQuery(container).find(fld.jquery);
								//co('addPageHooks:display: jquery='+fld.jquery+': fields.length=',fields.length);
								qtx.addDisplayHooks(fields);
							}
						}else{
							var id = fld.id ? fld.id : handle;
							//co('addPageHooks:display: id=',id);
							qtx.addDisplayHook(document.getElementById(id));
						}
						break;
					case '['://b - bracket
					case '<'://c - comment
					case '{'://s - swirly/curly bracket
					case 'byline':
					default:
						if(fld.jquery){
							for(var i=0; i < containers.length; ++i){
								var container = containers[i];
								//jQuery(container).find(fld.jquery).each(function(i,e){qtx.addContentHook(e,sep);});//also works
								//co('addPageHooks:content: jquery='+fld.jquery+': container=',container);
								var fields = jQuery(container).find(fld.jquery);
								//co('addPageHooks:content: jquery='+fld.jquery+': fields.length=',fields.length);
								qtx.addContentHooks(fields,sep,fld.name);
							}
						}else{
							var id = fld.id ? fld.id : handle;
							//co('addPageHooks:content: id=',id);
							qtx.addContentHookById(id,sep,fld.name);
						}
						break;
				}
			}
		}
	}

	var addContentHooksTinyMCE = function () {
		function setEditorHooks(ed) {
			var id = ed.id;
			if (!id) return;
			var h=contentHooks[id];
			if(!h) return;
			if(h.mce){
				//already initialized
				return;
			}
			h.mce=ed;

			/**
			 * Highlighting the translatable fields
			 * @since 3.2-b3
			*/
			ed.getContainer().className += ' qtranxs-translatable';
			ed.getElement().className += ' qtranxs-translatable';

			var updateTinyMCEonInit = h.updateTinyMCEonInit;
			if(updateTinyMCEonInit == null){// 'tmce-active' or 'html-active' was not provided on the wrapper.
				var text_e = ed.getContent({format: 'html'}).replace(/\s+/g,'');
				var text_h = h.contentField.value.replace(/\s+/g,'');
				/**
				 * @since 3.2.9.8 - this is an ugly trick.
				 * Before this version, it was working relying on properly timed synchronisation of the page loading process,
				 * which did not work correctly in some browsers like IE or MAC OS, for example.
				 * Now, function setTinyMceInit is called after HTML loaded, before TinyMCE initialization, and it always set
				 * tinyMCEPreInit.mceInit, which causes to call this function, setEditorHooks, on TinyMCE initialization of each editor.
				 * However, function setEditorHooks gets invoked in two ways:
				 *
				 * 1. On page load, when Visual mode is initially on.
				 *      In this case we need to apply updateTinyMCE, which possibly applies wpautop.
				 *      Without q-X, WP applies wpautop in this case in php code in /wp-includes/class-wp-editor.php,
				 *      function 'editor', line "add_filter('the_editor_content', 'wp_richedit_pre');".
				 *      q-X disables this call in 'function qtranxf_the_editor',
				 *      since wpautop does not work correctly on multilingual values, and there is no filter to adjust its behaviour.
				 *      So, here we have to apply back wpautop to single-language value, which is achieved
				 *      with a call to updateTinyMCE(h) below.
				 *
				 * 2. When user switches to Visual mode for the first time from a page, which was initially loaded in Text mode.
				 *      In this case, wpautop gets applied internally inside TinyMCE, and we do not need to call updateTinyMCE(h) below.
				 *
				 * We could not figure out a good way to distinct within this function which way it was called,
				 * except this tricky comparison on the next line.
				 *
				 * If somebody finds out a better way, please let us know at qtranslateteam@gmail.com.
				*/
				updateTinyMCEonInit = text_e != text_h;
			}
			if(updateTinyMCEonInit){
				updateTinyMCE(h);
			}
			return h;
		}

		/** Sets hooks on HTML-loaded TinyMCE editors via tinyMCEPreInit.mceInit. */
		setTinyMceInit = function () {
			//co('setTinyMceInit: this: ', this);
			if (!window.tinyMCE) return;
			for(var key in contentHooks){
				var h=contentHooks[key];
				if(h.contentField.tagName!=='TEXTAREA') continue;
				if(h.mce) continue;
				if(h.mceInit) continue;
				if(!tinyMCEPreInit.mceInit[key]) continue;
				h.mceInit=tinyMCEPreInit.mceInit[key];
				if(h.mceInit.wpautop){
					h.wpautop = h.mceInit.wpautop;
					var wrappers = tinymce.DOM.select( '#wp-' + key + '-wrap' );
					if(wrappers && wrappers.length){
						h.wrapper = wrappers[0];
						if(h.wrapper){
							if(tinymce.DOM.hasClass( h.wrapper, 'tmce-active')) h.updateTinyMCEonInit = true;
							if(tinymce.DOM.hasClass( h.wrapper, 'html-active')) h.updateTinyMCEonInit = false;
							//otherwise h.updateTinyMCEonInit stays undetermined
						}
					}
				}else{
					h.updateTinyMCEonInit = false;
				}
				tinyMCEPreInit.mceInit[key].init_instance_callback = function(ed){ setEditorHooks(ed); }
				//co('setTinyMceInit: id=', key);
			}
		}
		setTinyMceInit();

		/** Adds more TinyMCE editors, which may have been initialized dynamically. */
		loadTinyMceHooks = function () {
			if (!window.tinyMCE) return;
			if (!tinyMCE.editors) return;
			for(var i=0; i<tinyMCE.editors.length; ++i){
				var ed=tinyMCE.editors[i];
				setEditorHooks(ed);
			}
		}
		window.addEventListener('load', loadTinyMceHooks);
	}

	if(!qTranslateConfig.onTabSwitchFunctions) qTranslateConfig.onTabSwitchFunctions=[];
	if(!qTranslateConfig.onTabSwitchFunctionsSave) qTranslateConfig.onTabSwitchFunctionsSave=[];
	if(!qTranslateConfig.onTabSwitchFunctionsLoad) qTranslateConfig.onTabSwitchFunctionsLoad=[];

	this.addLanguageSwitchListener=function(func){ qTranslateConfig.onTabSwitchFunctions.push(func); }

	/**
	 * @since 3.2.9.8.6
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * The function passed will be called when user presses one of the Language Switching Buttons
	 * before the content of all fields hooked is replaced with an appropriate language.
	 * Two arguments are supplied:
	 * - two-letter language code of currently active language from which the edit language is being switched.
	 * - the language code to which the edit language is being switched.
	 * The value of "this" is set to the only global instance of qTranslateX object.
	 */
	this.addLanguageSwitchBeforeListener=function(func){ qTranslateConfig.onTabSwitchFunctionsSave.push(func); }

	/**
	 * @since 3.3.2
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * Delete handler previously added by function addLanguageSwitchBeforeListener.
	 */
	this.delLanguageSwitchBeforeListener=function(func){ 
		for(var i=0; i < qTranslateConfig.onTabSwitchFunctionsSave.length; ++i){
			var f = qTranslateConfig.onTabSwitchFunctionsSave[i];
			if(f != func) continue;
			qTranslateConfig.onTabSwitchFunctionsSave.splice(i,1);
			return;
		}
	}

	/**
	 * @since 3.2.9.8.6
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * The function passed will be called when user presses one of the Language Switching Buttons
	 * after the content of all fields hooked is replaced with an appropriate language.
	 * Two arguments are supplied:
	 * - two-letter language code of active language to which the edit language is already switched.
	 * - the language code from which the edit language is being switched.
	 * The value of "this" is set to the only global instance of qTranslateX object.
	 */
	this.addLanguageSwitchAfterListener=function(func){ qTranslateConfig.onTabSwitchFunctionsLoad.push(func); }

	/**
	 * @since 3.3.2
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * Delete handler previously added by function addLanguageSwitchAfterListener.
	 */
	this.delLanguageSwitchAfterListener=function(func){ 
		for(var i=0; i < qTranslateConfig.onTabSwitchFunctionsLoad.length; ++i){
			var f = qTranslateConfig.onTabSwitchFunctionsLoad[i];
			if(f != func) continue;
			qTranslateConfig.onTabSwitchFunctionsLoad.splice(i,1);
			return;
		}
	}

	/**
	 * @since 3.2.9.8.9
	 * Designed as interface for other plugin integration. The documentation is available at
	 * https://qtranslatexteam.wordpress.com/integration/
	 * 
	 */
	this.enableLanguageSwitchingButtons=function(on){
		var display = on ? 'block' : 'none';
		for(var lang in qTranslateConfig.tabSwitches){
			var tabSwitches = qTranslateConfig.tabSwitches[lang];
			for(var i=0; i < tabSwitches.length; ++i){
				var tabSwitch = tabSwitches[i];
				var tabSwitchParent = tabSwitches[i].parentElement;
				tabSwitchParent.style.display = display;
				break;
			}
			break;
		}
	}

	var getWrapForm=function(){
		var wraps = document.getElementsByClassName('wrap');
		for(var i=0; i < wraps.length; ++i){
			var w = wraps[i];
			var forms = w.getElementsByTagName('form');
			if(forms.length) return forms[0];
		}
		var forms = document.getElementsByTagName('form');
		if(forms.length === 1)
			return forms[0];
		for(var i=0; i < forms.length; ++i){
			var f = forms[i];
			wraps = f.getElementsByClassName('wrap');
			if(wraps.length) return f;
		}
		return null;
	}

	var getFormWrap=function(){
		var forms = document.getElementsByTagName('form');
		for(var i=0; i < forms.length; ++i){
			var f = forms[i];
			var wraps = f.getElementsByClassName('wrap');
			if(wraps.length) return wraps[0];
		}
		var wraps = document.getElementsByClassName('wrap');
		for(var i=0; i < wraps.length; ++i){
			var w = wraps[i];
			forms = w.getElementsByTagName('form');
			if(forms.length) return w;
		}
		return null;
	}

	if( typeof(pg.addContentHooks) == "function")
		pg.addContentHooks(this);

	if( qTranslateConfig.page_config && qTranslateConfig.page_config.forms)
		addPageHooks(qTranslateConfig.page_config.forms);

	addMultilingualHooks(jQuery);

	//co('displayHookNodes.length=',displayHookNodes.length);
	//co('displayHookNodes: ',displayHookNodes);
	//co('displayHookAttrs.length=',displayHookAttrs.length);
	//co('contentHooks: ',contentHooks);
	if(!displayHookNodes.length && !displayHookAttrs.length){
		var ok = false;
		for(var key in contentHooks){ ok = true; break; }
		if(!ok){
			return;
		}
	}

	/**
	 * former switchTab
	 * @since 3.3.2
	 */
	this.switchActiveLanguage = function () {
		//co('switchActiveLanguage: this=',this);
		var tabSwitch = this;
		var lang = tabSwitch.lang;
		if (!lang){
			alert('qTranslate-X: This should not have happened: Please, report this incident to the developers: !lang');
			return;
		}
		if ( qTranslateConfig.activeLanguage === lang ){
			return;
		}
		if (qTranslateConfig.activeLanguage) {
			var ok2switch = true;
			var onTabSwitchFunctionsSave = qTranslateConfig.onTabSwitchFunctionsSave;
			for (var i = 0; i < onTabSwitchFunctionsSave.length; ++i) {
				var ok = onTabSwitchFunctionsSave[i].call(qTranslateConfig.qtx,qTranslateConfig.activeLanguage,lang);
				if(ok === false) ok2switch = false;
			}
			if(!ok2switch)
				return;//cancel button switch, if one of onTabSwitchFunctionsSave returned 'false'.
			var tabSwitches = qTranslateConfig.tabSwitches[qTranslateConfig.activeLanguage];
			for(var i=0; i < tabSwitches.length; ++i){
				tabSwitches[i].classList.remove(qTranslateConfig.lsb_style_active_class);
				//tabSwitches[i].classList.remove('active');
				//tabSwitches[i].classList.remove('wp-ui-highlight');
			}
			//tabSwitches[qTranslateConfig.activeLanguage].classList.remove('active');
		}
		var langFrom = qTranslateConfig.activeLanguage;
		qTranslateConfig.activeLanguage=lang;
		{
			var tabSwitches = qTranslateConfig.tabSwitches[qTranslateConfig.activeLanguage];
			for(var i=0; i < tabSwitches.length; ++i){
				tabSwitches[i].classList.add(qTranslateConfig.lsb_style_active_class);
				//tabSwitches[i].classList.add('active');
				//tabSwitches[i].classList.add('wp-ui-highlight');
			}
		}
		var onTabSwitchFunctions = qTranslateConfig.onTabSwitchFunctions;
		for (var i = 0; i < onTabSwitchFunctions.length; ++i) {
			onTabSwitchFunctions[i].call(qTranslateConfig.qtx,lang,langFrom);
		}
		var onTabSwitchFunctionsLoad = qTranslateConfig.onTabSwitchFunctionsLoad;
		for (var i = 0; i < onTabSwitchFunctionsLoad.length; ++i) {
			onTabSwitchFunctionsLoad[i].call(qTranslateConfig.qtx,lang,langFrom);
		}
	}

	/**
	 * former switchTab
	 * @since 3.3.2
	 */
	var createSetOfLSB = function () {
		var langSwitchWrap=qtranxj_ce('ul', {className: qTranslateConfig.lsb_style_wrap_class});
		var langs=qTranslateConfig.language_config;
		if(!qTranslateConfig.tabSwitches) qTranslateConfig.tabSwitches={};
		for (var lang in langs) {
			var lang_conf = langs[lang];
			var flag_location=qTranslateConfig.flag_location;
			var tabSwitch=qtranxj_ce ('li', {lang: lang, className: 'qtranxs-lang-switch', onclick: qTranslateConfig.qtx.switchActiveLanguage }, langSwitchWrap );
			qtranxj_ce('img', {src: flag_location+lang_conf.flag}, tabSwitch);
			qtranxj_ce('span', {innerHTML: lang_conf.name}, tabSwitch);
			if ( qTranslateConfig.activeLanguage == lang )
				tabSwitch.classList.add(qTranslateConfig.lsb_style_active_class);
			if(!qTranslateConfig.tabSwitches[lang]) qTranslateConfig.tabSwitches[lang] = [];
			qTranslateConfig.tabSwitches[lang].push(tabSwitch);
		}
		return langSwitchWrap;
	}

	var setupMetaBoxLSB = function($){
		var mb = document.getElementById('qtranxs-meta-box-lsb');
		if(!mb) return;
		var inside_elems = mb.getElementsByClassName('inside');
		if(!inside_elems.length) return;//consistency check in case WP did some changes
		mb.className += ' closed';
		$(mb).find('.hndle').remove();//original h3 element is replaced with span below
		var sp = document.createElement('span');
		mb.insertBefore(sp, inside_elems[0]);
		sp.className = 'hndle ui-sortable-handle';
		var langSwitchWrap = createSetOfLSB();
		sp.appendChild(langSwitchWrap);
		$(function($){$('#qtranxs-meta-box-lsb .hndle').unbind('click.postboxes');});
	}

	//co('qTranslateConfig.LSB=',qTranslateConfig.LSB);
	if(qTranslateConfig.LSB){
		//additional initialization
		addContentHooksTinyMCE();
		setupMetaBoxLSB(jQuery);

		//create sets of LSB
		var anchors=[];
		if(qTranslateConfig.page_config && qTranslateConfig.page_config.anchors){
			for(var id in qTranslateConfig.page_config.anchors){
				var anchor = qTranslateConfig.page_config.anchors[id];
				//co('anchor: ', anchor);
				var f = document.getElementById(id);
				if (f) {
					anchors.push({ f: f, where: anchor.where });
				} else if (anchor.jquery) {
					var list = jQuery(anchor.jquery);
					for (var i = 0; i < list.length; ++i) {
						var f = list[i];
						anchors.push({ f: f, where: anchor.where });
					}
				}
			}
		}
		//co('anchors: ', anchors);
		if(!anchors.length){
			var f=pg.langSwitchWrapAnchor;
			if(!f){
				f = getWrapForm();
			}
			if(f) anchors.push({ f:f, where: 'before'});
		}
		for(var i=0; i < anchors.length; ++i){
			var anchor = anchors[i];
			//co('anchor['+i+']: ', anchor);
			if( !anchor.where || anchor.where.indexOf('before') >= 0 ){
				//var langSwitchWrap=qtranxj_ce('ul', {className: qTranslateConfig.lsb_style_wrap_class});
				//var languageSwitch = new qtranxj_LanguageSwitch(langSwitchWrap);
				var langSwitchWrap = createSetOfLSB();
				anchor.f.parentNode.insertBefore( langSwitchWrap, anchor.f );
			}
			if( anchor.where && anchor.where.indexOf('after') >= 0 ){
				//var langSwitchWrap=qtranxj_ce('ul', {className: qTranslateConfig.lsb_style_wrap_class});
				//var languageSwitch = new qtranxj_LanguageSwitch(langSwitchWrap);
				var langSwitchWrap = createSetOfLSB();
				anchor.f.parentNode.insertBefore( langSwitchWrap, anchor.f.nextSibling );
			}
			if( anchor.where && anchor.where.indexOf('first') >= 0 ){
				var langSwitchWrap = createSetOfLSB();
				anchor.f.insertBefore( langSwitchWrap, anchor.f.firstChild );
			}
			if( anchor.where && anchor.where.indexOf('last') >= 0 ){
				var langSwitchWrap = createSetOfLSB();
				anchor.f.insertBefore( langSwitchWrap, null );
			}
		}

		/**
		 * @since 3.2.4 Synchronization of multiple sets of Language Switching Buttons
		 */
		this.addLanguageSwitchListener(onTabSwitch);
		if(pg.onTabSwitch){
			this.addLanguageSwitchListener(pg.onTabSwitch);
		}
	}
}

/**
 * Designed as interface for other plugin integration. The documentation is available at
 * https://qtranslatexteam.wordpress.com/integration/
 *
 * qTranslateX instance is saved in global variable qTranslateConfig.qtx,
 * which can be used by theme or plugins to dynamically change content hooks.
 * @since 3.4
 */
qTranslateConfig.js.get_qtx = function(){
	//co('get_qtx: qtx: ', qTranslateConfig.qtx);
	if(!qTranslateConfig.qtx) new qTranslateX(qTranslateConfig.js);
	return qTranslateConfig.qtx;
}
jQuery(document).ready(qTranslateConfig.js.get_qtx);
