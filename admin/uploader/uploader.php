<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>文件上传</title>
</head>
<body>
<div style="display:none; height:100%; line-height:100%; background-color:#EAF4FF;" id="uploading">
	<marquee behavior="alternate">文件上传中，请稍候..</marquee>
</div>
<div id="uploadIframe">
	<iframe src="uploader_sel.php?tgid=<?php echo $_GET['tgid']; ?>" width="100%" height="100%" frameborder="0" scrolling="no" noresize></iframe>
</div>
</body>
</html>
