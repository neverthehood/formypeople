/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.height = '400';
	config.language = 'ru';
	config.uiColor = '#f2f2f2';
	config.coreStyles_bold = { element: 'b', overrides: 'strong' };// Используем B вместо STRONG
	config.forceSimpleAmpersand = true;// & не меняем на &amp;
	config.scayt_autoStartup = false
	config.pasteFromWordNumberedHeadingToList = true;// Нумерованные списки при вставке из ворда
	config.filebrowserBrowseUrl = document.location.protocol+'//'+document.location.host+'/admin/plugins/elfinder/elfinder.php?mode=file';
	config.filebrowserImageBrowseUrl = document.location.protocol+'//'+document.location.host+'/admin/plugins/elfinder/elfinder.php?mode=file';
	config.filebrowserFlashBrowseUrl = document.location.protocol+'//'+document.location.host+'/admin/plugins/elfinder/elfinder.php?mode=file';
	config.font_names = 'Arial;Arial Narrow;Georgia;sans-serif;serif;Tahoma;Times New Roman;Verdana';
	config.removePlugins = 'wsc,scayt,save,newpage,spellchecker,forms,bidi,about';
	config.smiley_path = document.location.protocol+"//"+document.location.host+'/uploaded/_smiles/';
	config.smiley_images = ['s1.gif','s2.gif','s3.gif','s4.gif','s5.gif','s6.gif','s7.gif','s8.gif','s9.gif','s10.gif','s11.gif','s12.gif','s13.gif','s14.gif','s15.gif','s16.gif','s17.gif','s18.gif','s19.gif','s20.gif','s21.gif','s22.gif','s23.gif','s24.gif','s25.gif','s26.gif','s27.gif','s28.gif','s29.gif','s30.gif','s31.gif','s32.gif','s33.gif'];
	config.smiley_descriptions = ['Улыбка','Зубы','С ума сошел?','Отстой','Рассматриваю','Посмотри сюда','Смущаюсь','Бе-Бе-Бе!','Аксакал','Пока-пока!','Класс!','Пиво!','Нервный тик','Биццо апстену','Драки ищешь?','Злой','Очень злой','Помолимся','Фото','Влюблен','Б-у-у-э...','Попробуй попади!','В натуре крутой!','Не пыхай в лицо!','Рыдаю...','Это тебе','Обижаюсь','По-секрету','Ржунимагу!','Поговорим?','Уезжаю','Читай внимательно','Веселый роджер'];
	config.smiley_columns = 11;
	config.contentsCss = [document.location.protocol+'//'+document.location.host+'/style.css'];
	config.fontSize_sizes = '8/8px;10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px/28/28px;30/30px;32/32px;34/34px;36/36px;38/38px;40/40px;42/42px;44/44px;46/46px;48/48px;64/64px;72/72px';
	config.resize_dir = 'both';
	config.toolbarCanCollapse = false;
	config.filebrowserImageWindowWidth = 950;
    config.filebrowserImageWindowHeight = 600;
    config.filebrowserWindowWidth = 950;
    config.filebrowserWindowHeight = 600;
	config.entities = false;
	config.fillEmptyBlocks = true; // Заполнение пустых блоков символом &nbsp;
	config.templates_replaceContent = false;
	config.templates_files = [ document.location.protocol+'//'+document.location.host+'/uploaded/templates/default.js' ];
	config.extraPlugins = 'osem_googlemaps,magicline,insertpre,pagebreak,videodetector,stylesheetparser,placeholder,pbckcode,autocorrect,codemirror';
	config.insertpre_style = 'font:normal 14px Times; background-color:#F8F8F8;border:1px solid #DDD;padding:10px;';
	config.toolbar_ckaxiom = [
			[ 'Codemirror','Source', 'Bold' ,'Italic', 'Underline', 'Strike', 'RemoveFormat'],
			[ 'Find','Replace'], ['Undo','Redo' ], [ 'Cut','Copy'],['NumberedList','BulletedList' ],
			['Paste','PasteText','PasteFromWord'],['Templates'],
			['Link','Unlink','Anchor'],['Image','osem_googlemaps','VideoDetector','Flash','insertPre'],
			['Blockquote','CreateDiv'],['CreatePlaceholder'],['pbckcode'],['AutoCorrect'],
			[ 'Table','HorizontalRule','Smiley','SpecialChar' ],
			[ 'Styles'],['Format'],['Font'],['FontSize'],['TextColor','ShowBlocks'],
			['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ]
			];
	config.toolbar_ckbasic = [
			[ 'Source', 'Bold', 'Italic', 'Underline','RemoveFormat'],
			[ 'Paste','PasteText','PasteFromWord'],
			[ 'Link','Unlink' ],
			['Font'],['FontSize'],['TextColor','ShowBlocks']
		];
	config.toolbar_ckmini = [
			[ 'Source', 'Bold', 'Italic', 'Underline','PasteText','Link','Unlink' ]
		];
	config.codemirror = {
    theme: 'tomorrow-night-bright',// bespin, base16-dark, 3024-night, neat, mdn-like !!!!, eclipse, tomorrow-night-bright
    lineNumbers: true,
    lineWrapping: true,
    matchBrackets: true,
    autoCloseTags: true,
    autoCloseBrackets: true,
    // Whether or not to enable search tools, CTRL+F (Find), CTRL+SHIFT+F (Replace), CTRL+SHIFT+R (Replace All), CTRL+G (Find Next), CTRL+SHIFT+G (Find Previous)
    enableSearchTools: true,
    enableCodeFolding: false,
    enableCodeFormatting: true,
    autoFormatOnStart: true,
    autoFormatOnModeChange: false,
    autoFormatOnUncomment: true,
    // Define the language specific mode 'htmlmixed' for html including (css, xml, javascript), 'application/x-httpd-php' for php mode including html, or 'text/javascript' for using java script only
    mode: 'htmlmixed',
    // Whether or not to show the search Code button on the toolbar
    showSearchButton: true,
    showTrailingSpace: true,
    highlightMatches: true,
    showFormatButton: true,
    showCommentButton: true,
    showUncommentButton: false,
    showAutoCompleteButton: true,
    styleActiveLine: true
	};
};
