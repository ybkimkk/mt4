<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

if(FGetStr('key') != '8353572'){
	echo 'KEY错误';
	exit;
}
?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>定时器</title>
<script type="text/javascript" src="/assets/js/jquery-1.4.2.min.js"></script>
</head>
<body>
<div id="result"></div>
<script type="text/javascript">
var getCount = 0;
function get_(){
    getCount++;
    
    $('#result').html('定时器：第' + getCount + '次发送消息');
    
    $.ajax({ type : "get", async:true,  url : 'timer.php?key=5798432&rnd=' + Math.random(), dataType : "html",timeout : 30 * 1000,
        success: function(html){
            $('#result').html(html);

            if(html.indexOf('更新') > 0 || html.indexOf('插入') > 0){
                //有发送的，有可能下次还有，快速继续
                setTimeout(get_,1000 * 5);
            }else{					
                //没有需要发送的，间隔5秒后再次来
                setTimeout(get_,1000 * 5);
            }
        },
        error:function(json){
            //出错了
            $('#result').html('出错了，重新载入页面');
            setTimeout(function(){window.location.reload();},1000 * 5);
            //setTimeout(get_,1000 * 5);
        }
    });
}
get_();
</script>
</body>
</html>