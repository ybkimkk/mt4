function ExeUpload(VarName,chkPermissions)
{
	var href_ = window.location.href;
	var arr_ = href_.split("/plugins/");
	href_ = arr_[0] + "/plugins/";

	var swfu;
    this.Name = VarName;
	this.ChkPermissions = chkPermissions;
    this.UrlHttp = href_;
    this.FileSize = "5 MB";
    this.FileType = "*.jpg;*.gif;*.png;*.bmp";
    this.FileTypeDescription = "Images File";
    this.FileUpLoadCount = 15;
	this.FileSelectType = 0;
	var zSize = 0;
	this.LoadUpComponents = function()
	{       
			var FileString = "";
			if(this.FileSelectType == 1)
			{FileString = SWFUpload.BUTTON_ACTION.SELECT_FILE;}
			else
			{FileString = SWFUpload.BUTTON_ACTION.SELECT_FILES;}
		swfu = new SWFUpload({
			upload_url : this.UrlHttp + "swfUpload/upload_save.php",
            flash_url : this.UrlHttp + "swfUpload/script/swfUpload.swf",
            post_params : {"chkPermissions" : this.ChkPermissions},
            file_size_limit : this.FileSize,
            file_types : this.FileType,
            file_types_description : this.FileTypeDescription,
            file_upload_limit : this.FileUpLoadCount,
			file_dialog_start_handler : this.FileSelect,
			swfupload_loaded_handler : this.LoadingOk,
            file_queued_handler : this.FileJoinSuccess,
            file_queue_error_handler : this.FileJoinError,
            upload_progress_handler : this.FileIsNowUpLoad,
            upload_error_handler : this.FileUpLoadError,
            upload_start_handler : this.FileUpLoadBefore,
            upload_success_handler : this.FileSendSuccess,
            upload_complete_handler : this.FileUpLoadSuccess,
            button_image_url : this.UrlHttp + "swfUpload/images/add.jpg",
            button_placeholder_id : "AddButton",
            button_width : 90,
            button_height : 25,
            button_text : '',
            button_text_style : '',
            button_text_top_padding : 0,
            button_text_left_padding : 0,
            button_action : FileString,
            button_disabled : false,
            button_cursor : SWFUpload.CURSOR.HAND,
			button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE,
            debug : false
		});
	}
	this.LoadingOk = function(){$("FlashLoadingOk").style.visibility = "hidden";}
	this.FileJoinSuccess = function(File)
	{
		zSize += File.size;
		off();
		ExeUpload.prototype.AddFileHtml(File.name,File.id,File.size);
		FillLabel(swfu.getStats().files_queued,zSize);
		offUpload();
		if(swfu.getStats().files_queued >= 15){swfu.setButtonDisabled(true);swfu.setButtonCursor("ARROW");}
	}
	this.FileJoinError = function(File,Code,Message)
    {
        switch(Code)
        {
            case -100 :alert("为了保证上传速度，一次性最多上传15个文件！");break; 
            case -110 :alert("您选择的文件太大！");break; 
            case -120 :alert("您选择的文件是零字节文件，不能上传！");break; 
            default :alert("文件类型不正确！"); 
        }
    }
	ExeUpload.prototype.StartUpLoad = function()
    {
        if(swfu.getStats().files_queued > 0){swfu.setButtonDisabled(true);swfu.setButtonCursor("ARROW");swfu.startUpload();onUpload();}
    }
	this.FileIsNowUpLoad = function(File,Bytes,Total)
	{
		var PercentageTotal = 100 / (Total / Bytes);
        $("Percentage").innerHTML = getLeft(PercentageTotal) + "%";
        var PercentageYotal = (150 / 100) * PercentageTotal;
        $("loadingImages").style.width = PercentageYotal + "px";
        if(PercentageTotal == 100)
            $("loading_" + File.id).innerHTML = "上传成功，正在保存...";
	}
	this.FileUpLoadError = function(File,Code,Message){
		if(Code != -280){$("loading_" + File.id).innerHTML = Message +"("+ Code +")";}
		}
	this.FileUpLoadBefore=function(File)
	{
		$("loading_" + File.id).innerHTML = '<div class="loading"><img src="images/loading.gif" width="0" height="8" id="loadingImages" /></div><div class="bfb" id="Percentage"></div>';
		$("Delete_" + File.id).src = "images/delimgoff.gif";
		$("Delete_" + File.id).className = "delimagesoff";
        $("Delete_" + File.id).title = "文件正在上传...";
	}
	this.FileSendSuccess=function(File,Data)
	{
		if(Data.indexOf("upload/") >= 0)
		{
			$("loading_" + File.id).innerHTML = "<span id='uploadsuccess_" + File.id + "' title='" + Data + "'>文件上传成功！</span>";
			//$("loading_" + File.id).innerHTML = Data;
			$("Delete_" + File.id).title = "此文件已经上传成功！";
		}
		else
		{
			//$("loading_" + File.id).innerHTML = "<span id='uploadsuccess_" + File.id + "' title='" + Data + "'>上传失败！</span>";
			$("loading_" + File.id).innerHTML = "<span id='uploadsuccess_" + File.id + "'>" + Data + "</span>";
			//$("loading_" + File.id).innerHTML = Data;
			$("Delete_" + File.id).title = "此文件上传失败！";
		}
		$("Delete_" + File.id).src = "images/delimg.gif";
		$("Delete_" + File.id).className = "delimages";
		$("Delete_" + File.id).style.cursor = "pointer";
	}
	this.FileUpLoadSuccess=function(File)
	{
		$("label").innerHTML = "剩余上传文件个数"+ swfu.getStats().files_queued +"个，剩余上传大小("+ ByteToKb(ByteToKb(zSize-File.size)) +"M)";
		if(swfu.getStats().files_queued > 0){swfu.startUpload();}
		//if(swfu.getStats().files_queued <= 0){$("label").innerHTML="所有文件上传成功！<a href=\"javascript:window.location.reload();\">继续上传>></a>"}
		//if(swfu.getStats().files_queued <= 0){$("label").innerHTML="所有文件上传成功！<a href=\"#nolink\" onclick=\"AllPicToFCK()\">【将所有图片插入编辑器中】</a> &nbsp; <a href=\"javascript:window.location.reload();\">继续上传其它图片</a>"}
		if(swfu.getStats().files_queued <= 0){$("label").innerHTML="所有文件均已执行上传！"}
	}
	ExeUpload.prototype.alldelFile = function()
	{
		$("div_on").innerHTML = '<div class="Content_title"><ul><li class="W400">文件名</li><li class="W200">状态</li><li class="W49">操作</li></ul></div>';
		while(swfu.getStats().files_queued > 0){swfu.cancelUpload();}
		FillLabel(0, 0);
		on();onUpload();
		swfu.setButtonDisabled(false);
		swfu.setButtonCursor("HAND");
	}
	ExeUpload.prototype.delFile = function(id)
	{
		if($("Delete_" + id).className == "delimages")
		{
			var x = $("FileItem_" + id);
			x.parentNode.removeChild(x);
			zSize -= swfu.getFile(id).size;
			swfu.cancelUpload(id);
			FillLabel(swfu.getStats().files_queued, zSize);
			if(swfu.getStats().files_queued <= 0){on();onUpload();}
			if(swfu.getStats().files_queued < 15){swfu.setButtonDisabled(false);swfu.setButtonCursor("HAND");}
		}
	}
	ExeUpload.prototype.AddFileHtml = function (name,id,size)
	{
		$("div_on").innerHTML +='<div class="Content_Txt" id="FileItem_'+ id +'" onmouseover="this.style.backgroundColor=\'#e8f1ff\'" onmouseout="this.style.backgroundColor=\'\'"><ul><li class="W400">'+ name +' ('+ ByteToKb(size) +'KB)</li><li class="W200" id="loading_'+id+'">等待上传...</li><li class="W49"><img src="images/delimg.gif" class="delimages" title="删除文件" id="Delete_'+ id +'" onclick="ExeUpload.prototype.delFile(\''+ id +'\')" /></li></ul></div>';
		FillLabel();
	}
}
function $(id){return document.getElementById(id);}
function on(){$("div_on").style.display="none";$("div_off").style.display="block";}
function off(){$("div_off").style.display="none";$("div_on").style.display="block";}
function FillLabel(count, size){$("label").innerHTML = "已经添加了"+ count +"张图片(总大小"+ ByteToKb(ByteToKbu(size)) +"M)";}
function ByteToKb(Byte){var temp = 	Byte / 1024;return getLeft(temp);}
function ByteToKbu(Byte){var temp = Byte / 1024;return temp;}
function onk(bo){if(bo.style.cursor == "pointer"){bo.style.backgroundPosition="0px -50px"; }}
function onu(bo)
{
	if(bo.style.cursor == "pointer"){bo.style.backgroundPosition="0px 0px";}
	if(bo.id=="DelButton" && bo.style.cursor == "pointer"){ExeUpload.prototype.alldelFile();}
	if(bo.id=="UpButton" && bo.style.cursor == "pointer"){ExeUpload.prototype.StartUpLoad();}
}
function onUpload()
{
	$("UpButton").style.backgroundPosition="bottom";
	$("UpButton").style.cursor = "default";
	$("DelButton").style.backgroundPosition="bottom";
	$("DelButton").style.cursor = "default";
}
function offUpload()
{
	$("UpButton").style.backgroundPosition="top";
	$("UpButton").style.cursor = "pointer";
	$("DelButton").style.backgroundPosition="top";
	$("DelButton").style.cursor = "pointer";
}
function getLeft(Lens)
{
	var Lxt;
	var LensU = Lens.toString();
	if(LensU.length > 4)
	{
		LensU.lastIndexOf(".")
		Lxt = LensU.substring(0,LensU.lastIndexOf(".")+4);
		return Lxt;
	}
	return LensU;
}

function AllPicToFCK()
{
	var spans = document.getElementsByTagName("SPAN");
	var fileList = "";
	for (var i = 0;i <spans.length ;i++ )
	{
		if(spans[i].id)
		{
			if(spans[i].id.indexOf("uploadsuccess_") >= 0)
			{
				if(fileList != "")
					fileList = fileList + "|";

				fileList = fileList + spans[i].title;
			}
		}
	}
	if(fileList == "")
	{
		alert("抱歉，没有发现上传成功的图片！");
	}
	else
	{
		window.parent.InsertPics(fileList);
	}
}