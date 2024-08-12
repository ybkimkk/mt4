<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>文件上传</title>
<script type="text/javascript">
function ChkUpload(){
	if(document.getElementById("uploadFileName").value != ""){
		window.parent.document.getElementById("uploading").style.display = "";
		window.parent.document.getElementById("uploadIframe").style.display = "none";
		return true;
	}else{
		alert("请选择文件再上传！");
		return false;
	}
}
</script>
</head>
<body>
<div style="text-align:center;">
<br>
<form name="uploadFile" enctype="multipart/form-data" method="post" action="uploader_save.php?tgid=<?php echo($_GET['tgid']); ?>" onSubmit="return ChkUpload();">
	<input type="file" name="myUploadFile" id="myUploadFile">
	<input type="submit" name="Submit" value=" 上 传 ">
</form>
</div>
</body>
</html>