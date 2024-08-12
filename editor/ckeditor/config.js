/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.language = 'zh-cn';

	//config.uiColor = '#AADC6E';
	config.allowedContent = true;

	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	
	config.font_names = "新宋体/新宋体;黑体/黑体;仿宋/仿宋_GB2312;楷体/楷体_GB2312;隶书/隶书;幼圆/幼圆;微软雅黑/微软雅黑;" + config.font_names;

	config.templates_replaceContent = 0;

	config.smiley_columns = 15;
	config.smiley_images = ['001.gif','002.gif','003.gif','004.gif','005.gif','006.gif','007.gif','008.gif','009.gif','010.gif','011.gif','012.gif','013.gif','014.gif','015.gif','016.gif','017.gif','018.gif','019.gif','020.gif','021.gif','022.gif','023.gif','024.gif','025.gif','026.gif','027.gif','028.gif','029.gif','030.gif','031.gif','032.gif','033.gif','034.gif','035.gif','036.gif','037.gif','038.gif','039.gif','040.gif','041.gif','042.gif','043.gif','044.gif','045.gif','046.gif','047.gif','048.gif','049.gif','050.gif','051.gif','052.gif','053.gif','054.gif','055.gif','056.gif','057.gif','058.gif','059.gif','060.gif','061.gif','062.gif','063.gif','064.gif','065.gif','066.gif','067.gif','068.gif','069.gif','070.gif','071.gif','072.gif','073.gif','074.gif','075.gif','076.gif','077.gif','078.gif','079.gif','080.gif'];
	config.smiley_descriptions = [];

	var webCode = "php";
	config.filebrowserUploadUrl = '/editor/ckeditor/upload/' + webCode + '/upload_save.' + webCode + '?ut=file';
	config.filebrowserImageUploadUrl = '/editor/ckeditor/upload/' + webCode + '/upload_save.' + webCode + '?ut=image';
	config.filebrowserFlashUploadUrl = '/editor/ckeditor/upload/' + webCode + '/upload_save.' + webCode + '?ut=flash';

	config.extraPlugins = 'flvPlayer,swfUpload';
	//config.extraPlugins = 'flvPlayer';

	config.toolbar = 'Default';
 	config.toolbar_Full =
	[
		['Source','-','NewPage','Preview','-','Templates'],
		['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'Scayt'],
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
		'/',
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['BidiLtr', 'BidiRtl'],
		['Link','Unlink','Anchor'],
		['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'],
		'/',
		['Styles','Format','Font','FontSize'],
		['TextColor','BGColor'],
		['Maximize', 'ShowBlocks','-','About']
	];
	config.toolbar_Default =
	[
		['NewPage','Templates','SelectAll','-', 'ShowBlocks'],
		['Cut','Copy','Paste','PasteText','PasteFromWord'],
		['Undo','Redo','-','Find','Replace','-','RemoveFormat'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Anchor'],
		'/',
		['Font','FontSize'],
		['TextColor','BGColor','-','Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['Image','swfUpload','Flash','flvPlayer','Table','CreateDiv','Iframe','Smiley','HorizontalRule'],
		['Source','-','Maximize']
	];
	config.toolbar_Basic =
	[
		['Font','FontSize'],
		['TextColor','BGColor','-','Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['Smiley']
	];
	config.toolbar_BasicAdmin =
	[
		['Font','FontSize'],
		['TextColor','BGColor','-','Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['Link','Unlink','Anchor'],
		['Image','Flash','Table','Iframe','Smiley','HorizontalRule'],
		['Source']
	];

};