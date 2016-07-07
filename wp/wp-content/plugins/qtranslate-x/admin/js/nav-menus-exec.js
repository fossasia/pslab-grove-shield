/* executed for
 /wp-admin/nav-menus.php
*/
jQuery(document).ready(
function(){
	var qtx = qTranslateConfig.js.get_qtx();

	var addMenuItemHooks=function(li)
	{
		qtx.addContentHooksByClass('edit-menu-item-title',li);
		qtx.addContentHooksByClass('edit-menu-item-attr-title',li);
		qtx.addContentHooksByClass('[edit-menu-item-description',li);//must use '[:]' separator style

		qtx.addDisplayHooksByClass('menu-item-title',li);
		//qtx.addDisplayHooksByClass('item-title',li);
		qtx.addDisplayHooksByTagInClass('link-to-original','A',li);
	}

	var onAddMenuItem = function(menuMarkup){
		var rx = /id="menu-item-(\d+)"/gi;
		while((matches = rx.exec(menuMarkup))){
			var id = 'menu-item-'+matches[1];
			var li = document.getElementById(id);
			if(li) addMenuItemHooks(li);
		}
	}

	if(wpNavMenu){
		var wp_addMenuItemToBottom = wpNavMenu.addMenuItemToBottom;
		if( typeof wp_addMenuItemToBottom == 'function'){
			wpNavMenu.addMenuItemToBottom = function( menuMarkup, req ) {
				wp_addMenuItemToBottom( menuMarkup, req );
				onAddMenuItem(menuMarkup);
			};
		}
		if( typeof wp_addMenuItemToTop == 'function'){
			wpNavMenu.addMenuItemToTop = function( menuMarkup ) {
				wp_addMenuItemToTop( menuMarkup );
				onAddMenuItem(menuMarkup);
			};
		}
	}

	var onLanguageSwitchAfter = function(lang){
		if(wpNavMenu){
			if( typeof wpNavMenu.refreshKeyboardAccessibility == 'function'){
				wpNavMenu.refreshKeyboardAccessibility();
			}
			if( typeof wpNavMenu.refreshAdvancedAccessibility == 'function'){
				wpNavMenu.refreshAdvancedAccessibility();
			}
		}
	}
	onLanguageSwitchAfter();

	qtx.addLanguageSwitchAfterListener(onLanguageSwitchAfter);
});
