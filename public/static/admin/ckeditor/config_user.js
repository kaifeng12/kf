/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' }
	];
	
	config.extraPlugins = 'colorbutton,panelbutton,button,justify';

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
	
	
	//自定义定制属性
	config.width=605;
	config.height=400;
	

	
	
		//文件的上传管理：加载CKfinder
config.filebrowserBrowseUrl =	static+'/admin/ckfinder/ckfinder.html';	
config.filebrowserImageBrowseUrl = static+'/admin/ckfinder/ckfinder.html?Type=Images';	
config.filebrowserFlashBrowseUrl = static+'/admin/ckfinder/ckfinder.html?Type=Flash';	
config.filebrowserUploadUrl = static+'/admin/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';	
config.filebrowserImageUploadUrl = static+'/admin/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';	
config.filebrowserFlashUploadUrl = static+'/admin/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
	



	
};
