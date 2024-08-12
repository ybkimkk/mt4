/*
 Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.addTemplates("default",{imagesPath:CKEDITOR.getUrl(CKEDITOR.plugins.getPath("templates")+"templates/images/"),
templates:[
	{
		title:"人才招聘(JOB)",
		image:"template1.gif",
		description:"每次增加一个人才招聘的表格，多个职位请添加多次即可",
		html:'<table width="100%" border="0" cellpadding="3" cellspacing="1" class="borderTable_job"><tr><th>职业名称</th><td>&nbsp;</td><th>学历要求</th><td>&nbsp;</td><th>性别要求</th><td>不限</td></tr><tr><th>年龄要求</th><td>20~40</td><th>薪金待遇</th><td>面议</td><th>语言要求</th><td>汉语</td></tr><tr><th>招聘人数</th><td>数名</td><th>工作地点</th><td>&nbsp;</td><th>有效期至</th><td>长期有效</td></tr><tr><th>详细要求</th><td colspan="5">&nbsp;</td></tr><tr><th>应聘方式</th><td colspan="5">请将简历发送至邮箱：()，合则约见</td></tr></table><br /><br />'
	}
	,
	{
		title:"Strange Template",
		image:"template2.gif",
		description:"A template that defines two colums, each one with a title, and some text.",
		html:'<table cellspacing="0" cellpadding="0" style="width:100%" border="0"><tr><td style="width:50%"><h3>Title 1</h3></td><td></td><td style="width:50%"><h3>Title 2</h3></td></tr><tr><td>Text 1</td><td></td><td>Text 2</td></tr></table><p>More text goes here.</p>'
	}
	,
	{
		title:"Text and Table",
		image:"template3.gif",
		description:"A title with some text and a table.",
		html:'<div style="width: 80%"><h3>Title goes here</h3><table style="width:150px;float: right" cellspacing="0" cellpadding="0" border="1"><caption style="border:solid 1px black"><strong>Table title</strong></caption><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table><p>Type the text here</p></div>'
	}
]});