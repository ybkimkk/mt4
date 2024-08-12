CKEDITOR.dialog.add('flvPlayer',function(editor){
	var escape = function(value){return　value;};
	return {
	title:editor.lang.flvPlayer.pluginTitle,
	resizable:CKEDITOR.DIALOG_RESIZE_BOTH,
		minWidth:400,
		minHeight:150,
		contents:[{
		id: 'info',  
		label: editor.lang.flvPlayer.generalTab,
		accessKey: 'P',
		elements:[
					{
						type: 'hbox',
						widths : [ '100%'],
						children:[{id: 'src',type: 'text',label: editor.lang.flvPlayer.url}]
					},
					{
					type: 'hbox',
					widths : [ '33%', '34%', '33%' ],
					children:[
							  {type:　'text',label:　editor.lang.flvPlayer.width,id:　'vWidth','default':　'400',style:　'width:80px'},
							  {type:　'text',label:　editor.lang.flvPlayer.height,id:　'vHeight','default':　'300',style:　'width:80px'},
							  {type:　'select',label:　editor.lang.flvPlayer.autostart,id:　'autostart','default':　'false',items:　[[editor.lang.flvPlayer.autostart_true,　'true'],　[editor.lang.flvPlayer.autostart_false,　'false']]}
							 ]
					}
		]
		}],
		onOk:　function(){
			vWidth = this.getValueOf('info','vWidth');
			vHeight = this.getValueOf('info','vHeight');
			autostart = this.getValueOf('info','autostart');
			mysrc = this.getValueOf('info','src');
			html = escape(mysrc);
			var iHtml = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'+ vWidth +'" height="'+ vHeight +'">';
			iHtml += '<param name="movie" value="/editor/ckeditor/plugins/flvPlayer/jwplayer.swf">';
			iHtml += '<param name="quality" value="high">';
			iHtml += '<param name="menu" value="false">';
			iHtml += '<param name="autostart" value="'+ autostart +'">';
			iHtml += '<param name="allowFullScreen" value="true">';
			iHtml += '<param name="FlashVars" value="file='+html+'">';
			iHtml += '<embed src="/editor/ckeditor/plugins/flvPlayer/jwplayer.swf?file='+html+'" autostart="'+ autostart +'" allowFullScreen="true" menu="false" quality="high" width="'+ vWidth +'" height="'+ vHeight +'" type="application/x-shockwave-flash"></embed>';
			iHtml += '</object>';
			editor.insertHtml(iHtml);
		},
		onLoad:　function(){}
	};
});