/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	config.height = '400';
	config.coreStyles_bold = { element: 'b', overrides: 'strong' };// Используем B вместо STRONG
	config.forceSimpleAmpersand = true;// & не меняем на &amp;
	config.filebrowserBrowseUrl = document.location.protocol+'//'+document.location.host+'/_jscript/elfinder/elfinder.php?mode=file';
	config.filebrowserImageBrowseUrl = document.location.protocol+'//'+document.location.host+'/_jscript/elfinder/elfinder.php?mode=file';
	config.filebrowserFlashBrowseUrl = document.location.protocol+'//'+document.location.host+'/_jscript/elfinder/elfinder.php?mode=file';
	config.contentsCss = [document.location.protocol+'//'+document.location.host+'/style.css'];
	config.filebrowserImageWindowWidth = 950;
    config.filebrowserImageWindowHeight = 600;
    config.filebrowserWindowWidth = 950;
    config.filebrowserWindowHeight = 600;
	config.entities = false;
	config.fillEmptyBlocks = true; // Заполнение пустых блоков символом &nbsp;
	config.templates_replaceContent = false;
	
	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbar_Axi = [
		[ 'Bold' ,'Italic', 'Underline', 'Strike', 'RemoveFormat'],
		['Undo','Redo' ],['NumberedList','BulletedList' ],
		['Paste'],
		['Link','Unlink','Anchor'],['Image'],
		['Blockquote'],
		['Table','HorizontalRule','SpecialChar' ],
		['Format'],['TextColor','ShowBlocks']
	];
	config.toolbar = 'Axi';

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	// config.removeButtons = 'Underline,Subscript,Superscript';
	
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
    showCommentButton: false,
    showUncommentButton: false,
    showAutoCompleteButton: false,
    styleActiveLine: true
	};

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	//config.removeDialogTabs = 'image:advanced;link:advanced';
};
