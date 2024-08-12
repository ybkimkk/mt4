CKEDITOR.dialog.add("swfUpload", function(editor){
	var timestamp = Math.round(new Date().getTime()/1000);
	var ckeditorPage = '/editor/ckeditor/plugins/swfUpload/index.php?rnd=' + timestamp;
	return {
		title: editor.lang.swfUpload.pluginTitle,
		minWidth: 680,
		minHeight: 450,
		contents:
			[
				{
					id: "upload",
					label: "上传图片",
					padding: 0,
					elements: [
						{
							type: "html",
							html: "<iframe name='swfUploadPage' id='swfUploadPage' width='680' height='450' src='" + ckeditorPage + "' scrolling='no' frameborder='0' allowtransparency='true'></iframe>",
							style: "width:680px;height:450px;padding:0;"
						}
					]
				}
			],
		onLoad:function(){
			$("#swfUploadPage").parent().css('height','450px');
		},
		onShow:function(){},
		onCancel:function(){},
		onHide:function(){
			//$('#swfUploadPage').attr('src', $('#swfUploadPage').attr('src'));
			//document.getElementById('swfUploadPage').contentDocument.location.reload();
			document.getElementById('swfUploadPage').contentWindow.location.reload();
		},
		onOk : function(){
			var iframePage = document.getElementById("swfUploadPage").contentWindow;
			var spans = iframePage.document.getElementsByTagName("SPAN");
			var fileList = "";
			for (var i = 0;i <spans.length ;i++ )
			{
				if(spans[i].id)
				{
					if(spans[i].id.indexOf("uploadsuccess_") >= 0)
					{
						if(fileList != "")
						{
							fileList = fileList + "|";
						}
						fileList = fileList + spans[i].title;
					}
				}
			}
		
			if(fileList == "")
			{
				alert("抱歉，没有发现上传成功的图片！");
				return false;
			}
			else
			{
				var arr = fileList.split("|");
				var picsHTML = "<br />";
				for(var i = 0;i < arr.length;i++)
				{
					picsHTML += "<img src='" + arr[i] + "'><br />";
				}
				
				editor.insertHtml(picsHTML);
			}
		},
		resizable: CKEDITOR.DIALOG_RESIZE_HEIGHT
	}
});