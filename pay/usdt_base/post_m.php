<?php
	if($clause =='pay_success'){	
    echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>' , GLang('USDT转账') , '</title>
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width">
<meta name="viewport" media="(device-height: 568px)" content="initial-scale=1.0,user-scalable=no,maximum-scale=1">
<style>
body,html{padding:0;margin:0}
body{font-size:16px;color:#323232;}

.bankpics{}
.bankpics .bankpic{float:left;width:320px; height:60px; margin-bottom:40px;}
.bankpics .bankpic input{vertical-align:middle}
.bankpics .bankpic img{vertical-align:middle;height:60px;border:1px solid #d7d5d5;}

.btn1 {
    width: 100%;
    height: 60px;
	line-height:60px;
    font-size: 22px;
    color: #fff;
    border: 0;
    background-color: #0581CE;
    outline: none;
	margin-bottom:20px;
	display:block;
	text-align:center;
	text-decoration:none;
	border-radius:10px;
	box-shadow: 3px 3px 3px #aaaaaa;
}
.btn2 {
    padding:0 10px;
    height: 30px;
	line-height:30px;
    font-size: 18px;
    color: #fff;
    border: 0;
    background-color: #0581CE;
    outline: none;
}
.titlebg{ background-color: #0581CE;background-image:url(/pay/ibank/selfpay_base/images/bg1.jpg);background-size:100% 100%;
font-size:18px; color:#ffffff;height:35px; line-height:35px;padding-left:15px;
}
</style>
</head>
<body>
<form action="/pay/usdt_base/post.php?clause=save_pay_info&oid=' . $DRInfo['payno'] . '" method="post" id="test_form" name="test_form">
<div style="max-width:800px; margin:0 auto; text-align:left;padding:0 5px;">

    <div style="text-align:right;">
		<a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=zh-cn"><img src="/pay/usdt_base/images/cn.png" alt="" width="40"></a>
        <a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=en-us"><img src="/pay/usdt_base/images/en.png" alt="" width="40" style="margin:0 20px;"></a>
        <a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=zh-vn"><img src="/pay/usdt_base/images/vn.png" alt="" width="40"></a>
		<a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=id"><img src="/pay/usdt_base/images/id.png" alt="" width="40" style="margin:0 0 0 20px;"></a>
    </div>

	<div style=" padding:5px 0 15px 0;"><span style="font-size:20px;">' , GLang('USDT转账') , '</span> (' , GLang('请确定收款信息及订单信息后提交') , ')</div>

    <div style="border-radius:15px;">
        <div class="titlebg">' , GLang('提示') , '</div>
        <div style="padding:0;">
            <div style="font-size:16px;line-height:30px;margin-top:5px;">
				<div style="font-size:20px;line-height:45px;text-align:center;">
					<img src="/pay/usdt_base/images/success.png" width="80"><br>
					' , GLang('您的支付信息已经提交，请等待审核') , '
				</div>
            </div>
        </div>
    </div>
</div>

    <script type="text/javascript" src="/pay/usdt_base/js/layer/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="/pay/usdt_base/js/layer/layer.js"></script>

</body></html>';
		  	
    }else if($clause =='pay_timer'){	
    echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>' , GLang('USDT转账') , '</title>
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width">
<meta name="viewport" media="(device-height: 568px)" content="initial-scale=1.0,user-scalable=no,maximum-scale=1">
<style>
body,html{padding:0;margin:0}
body{font-size:16px;color:#323232;}

.bankpics{}
.bankpics .bankpic{float:left;width:320px; height:60px; margin-bottom:40px;}
.bankpics .bankpic input{vertical-align:middle}
.bankpics .bankpic img{vertical-align:middle;height:60px;border:1px solid #d7d5d5;}

.btn1 {
    width: 100%;
    height: 60px;
	line-height:60px;
    font-size: 22px;
    color: #fff;
    border: 0;
    background-color: #0581CE;
    outline: none;
	margin-bottom:20px;
	display:block;
	text-align:center;
	text-decoration:none;
	border-radius:10px;
	box-shadow: 3px 3px 3px #aaaaaa;
}
.btn2 {
    padding:0 10px;
    height: 30px;
	line-height:30px;
    font-size: 18px;
    color: #fff;
    border: 0;
    background-color: #0581CE;
    outline: none;
}
.titlebg{ background-color: #0581CE;background-image:url(/pay/ibank/selfpay_base/images/bg1.jpg);background-size:100% 100%;
font-size:18px; color:#ffffff;height:35px; line-height:35px;padding-left:15px;
}
</style>
</head>
<body>
<form action="/pay/usdt_base/post.php?clause=save_pay_info&oid=' . $DRInfo['payno'] . '" method="post" id="test_form" name="test_form">
<div style="max-width:800px; margin:0 auto; text-align:left;padding:0 5px;">

    <div style="text-align:right;">
		<a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=zh-cn"><img src="/pay/usdt_base/images/cn.png" alt="" width="40"></a>
        <a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=en-us"><img src="/pay/usdt_base/images/en.png" alt="" width="40" style="margin:0 20px;"></a>
        <a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=zh-vn"><img src="/pay/usdt_base/images/vn.png" alt="" width="40"></a>
		<a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=id"><img src="/pay/usdt_base/images/id.png" alt="" width="40" style="margin:0 0 0 20px;"></a>
    </div>

	<div style=" padding:5px 0 15px 0;"><span style="font-size:20px;">' , GLang('USDT转账') , '</span> (' , GLang('请确定收款信息及订单信息后提交') , ')</div>

    <div style="border-radius:15px;">
        <div class="titlebg">' , GLang('订单信息') , '</div>
        <div style="padding:0;">
            <div style="font-size:16px;line-height:30px;margin-top:5px;">
								<!--' , GLang('特约商户') , ': '.$DRPay['PayKey'].'<br>-->
								' , GLang('金额') , ': '.($DRInfo['number'] * 1).'<br>
								' , GLang('您的实际转账金额') , ': '. ($DRInfo['number'] + $fee).'<br>
								' , GLang('订单号') , ': '.$DRInfo['payno'].'<br>
								' , GLang('交易时间') , ': '.date('Y-m-d H:i:s',$DRInfo['create_time']).'<br>
								<!--' , GLang('收款户名') , ': '.$DRPay['PayKey'].'<br>
								' , GLang('银行名称') , ': '.$rsInMer['f_uid2'].'<br>
								' , GLang('银行支行') , ': '.$rsInMer['f_key1'].'<br>
								' , GLang('收款账号') , ': '.$rsInMer['f_key2'].'<br>-->
            </div>
            <div style="font-size:14px;line-height:28px;margin-top:10px;">
                <div style="font-size:14px;font-weight:bold;">' , GLang('温馨提示') , '：</div>
                ' , GLang('_温馨提示内容') , '
            </div>
        </div>
    </div>

    <div>
		<div class="titlebg" style="margin-top:20px;">' , GLang('支付信息') , '</div>
		<div style="font-size:16px;line-height:30px;margin-top:10px;">
			<img src="'.$DRPay['f_pic1'].'" width="100%"><br>
			<div>' , GLang('金额') , ': <span style="font-size:22px;color:#8B0001;">'.($DRInfo['number'] * 1).'</span></div>
			<div>' , GLang('您的实际转账金额') , ': <span style="font-size:28px;color:#8B0001;">'.($DRInfo['number'] + $fee).'</span>（';
			$feeStr = GLang('转账会有{0} USDT的手续费，请注意实际到账数量。比如：你实际支付{1}美金，那你需要转{2}美金');
			$feeStr = str_replace('{0}',($feePre * 1000) . '‰',$feeStr);
			$feeStr = str_replace('{1}',1000,$feeStr);
			$feeStr = str_replace('{2}',1000 + $feePre * 1000,$feeStr);
			echo $feeStr;
			echo '）</div>
			<div>' , GLang('收款地址') , ': '.$DRPay['PayKey'].'</div>
			
    <script type="text/javascript" src="/pay/usdt_base/js/clipboard.min.js"></script>
    <script>
        var clipboard = new ClipboardJS("#copybtn");
        clipboard.on("success", function(e){
			e.clearSelection();
            document.getElementById("copyCode").innerHTML = "' , GLang('复制成功') , '";
			setTimeout(function(){document.getElementById("copyCode").innerHTML = "";},600);
        });
        clipboard.on("error", function(e){
            alert("' , GLang('复制失败，请手工选择后复制') , '");
        });
    </script>

			&nbsp; <button type="button" class="btn2" id="copybtn" data-clipboard-text="'.$DRPay['PayKey'].'">' , GLang('①复制') , '</button>
			<span style="color:#ff0000" id="copyCode"></span>
			
			</div>





<div id="sfz_zm_div" style="margin-top:20px;"><img id="ceshi1"></div>
<input type="hidden" id="sfz_zm" value="">
<div><span style="color:#ff0000">*</span> ' , GLang('上传转账截图') , ':
	<span style="display:none;"><input type="file" id="sc_zm">
	<textarea name="imgdata_1" id="imgdata_1"></textarea>
	</span>
	&nbsp; <button type="button" class="btn2" id="copybtn" onclick="Selfile(\'sc_zm\',1);" style="background-color:#cc0000">' , GLang('②点击选择我的截图') , '</button>
</div>
<div style="margin-top:20px;"><span style="color:#ff0000"></span> ' , GLang('USDT交易ID') , ':
	<input name="poid" id="poid" placeholder="" type="text" style="width:98%;height:35px;line-height:35px;border:1px solid #a9a9a9;">
	' , GLang('交易ID不是必须填写，但是填写交易ID可以加快审核速度。') , '
</div>
<script>
var isupload = false;
function Selfile(inputid,n) {
	if (isupload != false) {
		message("其他文件正在上传...请稍后");
	} else {	
		$("#" + inputid).click();
	}
}
window.onload=function(){
	var ck=1;
	var eleFile = document.querySelector("#sc_zm");
	
	var reader = new FileReader(),
	img = new Image();

	var file = null;

	var canvas = document.createElement("canvas");
	var context = canvas.getContext("2d");

	function picbig(this_){
		var originWidth = this_.width;
		var originHeight = this_.height;
		var maxWidth = 800, maxHeight = 600;

		var targetWidth = originWidth, targetHeight = originHeight;
		if (originWidth > maxWidth || originHeight > maxHeight) {
			if (originWidth / originHeight > maxWidth / maxHeight) {
				targetWidth = maxWidth;
				targetHeight = Math.round(maxWidth * (originHeight / originWidth));
			} else {
				targetHeight = maxHeight;
				targetWidth = Math.round(maxHeight * (originWidth / originHeight));
			}
		}

		canvas.width = targetWidth;
		canvas.height = targetHeight;
		context.clearRect(0, 0, targetWidth, targetHeight);
		context.drawImage(img, 0, 0, targetWidth, targetHeight);
		var type = "image/jpeg";
		var dataurl = canvas.toDataURL(type);

		$("#imgdata_"+ck).html(dataurl);
	}

	img.onload = function () {
	
		var originWidth = this.width;
		var originHeight = this.height;
		var maxWidth = 240, maxHeight = 150;

		var targetWidth = originWidth, targetHeight = originHeight;
		if (originWidth > maxWidth || originHeight > maxHeight) {
			if (originWidth / originHeight > maxWidth / maxHeight) {
				targetWidth = maxWidth;
				targetHeight = Math.round(maxWidth * (originHeight / originWidth));
			} else {
				targetHeight = maxHeight;
				targetWidth = Math.round(maxHeight * (originWidth / originHeight));
			}
		}

		canvas.width = targetWidth;
		canvas.height = targetHeight;
		context.clearRect(0, 0, targetWidth, targetHeight);
		context.drawImage(img, 0, 0, targetWidth, targetHeight);
		var type = "image/jpeg";
		var dataurl = canvas.toDataURL(type);
		$("#ceshi"+ck).attr("src",dataurl);

		picbig(this);		

		$(".mark").hide();


	};



	reader.onload = function(e) {
		img.src = e.target.result;
	};
	eleFile.addEventListener("change", function (event) {
		file = event.target.files[0];
		ck=1;
		if (file.type.indexOf("image") == 0) {
		$(".mark").show();
			reader.readAsDataURL(file);
		}
	});
}
</script>



			<button id="confirmsubmit" type="submit" class="btn1" style="margin:25px 0;">' , GLang('③转账完毕，提交审核') , '</button>
			<div style="color:#666666;font-size:16px;line-height:30px;padding-bottom:20px;">
				' , GLang('倒计时') , ': <span id="timedownspan" style="font-size:18px;color:#8B0001;">??' , GLang('分') , ' ??' , GLang('秒') , '</span><br>
				' , GLang('请在倒计时结束前完成汇款动作，汇款时请务必在附言栏或备注栏内填写识别码。') , '
			</div>
		</div>
    </div>

</div>

<script>
var endTimeHidden = "' . date('Y-m-d H:i:s',$DRInfo['create_time'] + 180 * 60) . '";
var endTimeHiddenArr = endTimeHidden.split(" ");
var endTimeHiddenArrL = endTimeHiddenArr[0].split("-");
var endTimeHiddenArrR = endTimeHiddenArr[1].split(":");
var EndTime = new Date(endTimeHiddenArrL[0], parseInt(endTimeHiddenArrL[1]) - 1, endTimeHiddenArrL[2], endTimeHiddenArrR[0], endTimeHiddenArrR[1], endTimeHiddenArrR[2]);
function GetRTime() {
    var NowTime = new Date();
    var nMS = EndTime.getTime() - NowTime.getTime();
    var nM = Math.floor(nMS / (1000 * 60));
    var nS = Math.floor(nMS / 1000) % 60;
	
	nS = ("0" + nS).slice(-2);

    if (nMS >= 0) {
        document.getElementById("timedownspan").innerHTML = nM + "' , GLang('分') , ' " + nS + "' , GLang('秒') , '";
		
		setTimeout("GetRTime()", 1000);
    }else {
        document.getElementById("timedownspan").innerHTML = "' , GLang('已超时') , '";
    }
}
GetRTime();
</script>

    <script type="text/javascript" src="/pay/usdt_base/js/layer/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="/pay/usdt_base/js/layer/layer.js"></script>
    <script type="text/javascript">
		/*
        var event = document.getElementById("item2");
        var line = document.getElementById("progress-line-new");
        event.setAttribute("class", "num-item item2 num-item-select");
        event.setAttribute("className", "num-item item2 num-item-select");
        line.style.width = 66 + "%";
        var event2 = document.getElementById("afterPay");
        event2.style.display = "block";
        $("#toPayLink").click(function() {      
            window.open("'.$url.'");
        });
        $(".openPicture").click(function () {
            $(".picture-popup-back").show();
        });
        $("#closePicture").click(function () {
            $(".picture-popup-back").hide();
        });
        var payView = document.getElementById("payCodeView");// 去支付中间整块信息
        var toPay = document.getElementById("toPay");// 去支付按钮
        var seeBtn = document.getElementById("seeState");// 查看支付状态按钮
        var iconSuccess = document.getElementById("iconSuccess");// 支付成功区块
        var iconFail = document.getElementById("iconFail");// 支付失败区块
        payView.style.display = toPay.style.display = "block";
        seeBtn.style.display = iconSuccess.style.display = iconFail.style.display = "none";
		*/
    </script>
	</form>
</body></html>';
		  	
    }else{
    	echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>' , GLang('USDT转账') , '</title>
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width">
<meta name="viewport" media="(device-height: 568px)" content="initial-scale=1.0,user-scalable=no,maximum-scale=1">
<style>
body,html{padding:0;margin:0}
body,div,td,p,span{font-size:14px;color:#323232;}

.bankpics{}
.bankpics .bankpic{float:left;width:320px; height:60px; margin-bottom:20px;}
.bankpics .bankpic input{vertical-align:middle}
.bankpics .bankpic img{vertical-align:middle;height:60px;border:1px solid #d7d5d5;}

.btn1 {
    width: 100%;
    height: 60px;
	line-height:60px;
    font-size: 22px;
    color: #fff;
    border: 0;
    background-color: #0581CE;
    outline: none;
	margin-bottom:20px;
	border-radius:10px;
	box-shadow: 3px 3px 3px #aaaaaa;
}
.btn2 {
    padding:0 10px;
    height: 30px;
	line-height:30px;
    font-size: 18px;
    color: #fff;
    border: 0;
    background-color: #0581CE;
    outline: none;
}
.titlebg{ background-color: #0581CE;background-image:url(/pay/ibank/selfpay_base/images/bg1.jpg);background-size:100% 100%;
font-size:18px; color:#ffffff;height:35px; line-height:35px;padding-left:15px;
}
</style>
</head>
<body>
<div style="max-width:800px; margin:0 auto; text-align:left;padding:0 5px;">
    <div style="text-align:right;">
		<a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=zh-cn"><img src="/pay/usdt_base/images/cn.png" alt="" width="40"></a>
        <a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=en-us"><img src="/pay/usdt_base/images/en.png" alt="" width="40" style="margin:0 20px;"></a>
        <a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=zh-vn"><img src="/pay/usdt_base/images/vn.png" alt="" width="40"></a>
		<a href="' , CC_ADMIN_ROOT_FOLDER , 'set_lang.php?lang=id"><img src="/pay/usdt_base/images/id.png" alt="" width="40" style="margin:0 0 0 20px;"></a>
    </div>

	<div style=" padding:5px 0 15px 0;"><span style="font-size:20px;">' , GLang('USDT转账') , '</span> (' , GLang('请确定收款信息及订单信息后提交') , ')</div>

    <div style="border-radius:15px;">
        <div class="titlebg">' , GLang('订单信息') , '</div>
        <div style="padding:0;">
            <div style="font-size:16px;line-height:30px;margin-top:5px;">
                    <!--' , GLang('特约商户') , ': '.$DRPay['PayKey'].'<br>-->
                    ' , GLang('金额') , ': '. ($DRInfo['number'] * 1).'<br>
					' , GLang('您的实际转账金额') , ': '. ($DRInfo['number'] + $fee).'<br>
                    ' , GLang('订单号') , ': '. $DRInfo['payno'].'<br>
                    ' , GLang('交易时间') , ': '.date('Y-m-d H:i:s',$DRInfo['create_time']).'<br>
            </div>
            <div style="font-size:14px;line-height:28px;margin-top:10px;">
                <div style="font-size:14px;font-weight:bold;">' , GLang('温馨提示') , '：</div>
                ' , GLang('_温馨提示内容') , '
            </div>
        </div>
    </div>

	<form action="/pay/usdt_base/post.php?clause=pay_timer&oid=' . $DRInfo['payno'] . '" method="post" id="test_form" name="test_form">
	<div class="titlebg" style="margin-top:20px;">' , GLang('支付信息') , '</div>


        	<img src="'.$DRPay['f_pic1'].'" width="100%"><br>
			' , GLang('收款地址') , '：'.$DRPay['PayKey'].'

    <script type="text/javascript" src="/pay/usdt_base/js/clipboard.min.js"></script>
    <script>
        var clipboard = new ClipboardJS("#copybtn");
        clipboard.on("success", function(e){
			e.clearSelection();
            document.getElementById("copyCode").innerHTML = "' , GLang('复制成功') , '";
			setTimeout(function(){document.getElementById("copyCode").innerHTML = "";},600);
        });
        clipboard.on("error", function(e){
            alert("' , GLang('复制失败，请手工选择后复制') , '");
        });
    </script>

			&nbsp; <button type="button" class="btn2" id="copybtn" data-clipboard-text="'.$DRPay['PayKey'].'">' , GLang('复制') , '</button>
			<span style="color:#ff0000" id="copyCode"></span>



    <div style="clear:both"></div>
    <div style="font-size:16px;height:50px; line-height:50px;">' , GLang('汇款条款') , '：</div>
    <div style="padding:10px; line-height:30px;border-radius:10px; border:1px solid #eceef0;">
        ' , GLang('_汇款条款内容') , '
    </div>
    <div style="padding:15px 0;"><input name="isread" id="isread" type="checkbox" value="1"><label for="isread">' , GLang('我已阅读并同意以上使用条款') , '</label></div>
    <button type="button" class="btn1" style="margin-top:20px;" onClick="tonext()">' , GLang('下一步') , '</button>
	</form>

</div>


	    <script type="text/javascript" src="/pay/usdt_base/js/layer/jquery-1.7.2.min.js"></script>
	    <script type="text/javascript" src="/pay/usdt_base/js/layer/layer.js"></script>
	    <script type="text/javascript">
	        function tonext() {
	            var outName = $("#names").val();
	            var bank = $(".bankpic input:checked").length;
				var isread = $("#isread").is(":checked");
	            if(outName == ""){
	                //layer.msg("' , GLang('请输入汇款户名') , '",{icon: 2});
	               // return false;
	            }
	            if(bank <= 0){
	               // layer.msg("' , GLang('请选择银行') , '",{icon: 2});
	              //  return false;
	            }
				if(!isread){
					layer.msg("' , GLang('请阅读并同意使用条款') , '",{icon: 2});
	                return false;
				}
	            $("#test_form").submit();
	        }
	    </script>
	</body>
	</html>';
    }

