function setModalMiddle(modal){
	$(modal).css('display','block');
	var modalTop = (($(modal).height() - $(modal).children().eq(0).height() - 120) / 2) + 'px';
	$(modal).css('display','none').css('top',modalTop);
}

function WJX_Alert(str,jsCode){
	alert(str);
	jsCode();
	/*
	//该弹框方式无法响应空格键或回车键以确定
	layer.alert(str, function(index){
		jsCode();
	});
	*/
}

function init_findinfo(){
	$(document).on("click",".findinfo",function(){
	//});
	//$(".findinfo").click(function () {
		var id = $(this).attr('val');
		var phone = $(this).attr('phone');
		var email = $(this).attr('email');
		if (phone) {			
			$(this).closest("td,.form-control-static").find(".lookphone").text(phone);
		}
		if (email) {			
			$(this).closest("td,.form-control-static").find(".lookemail").text(email);
		}
	});
}

function CInt(str) {
    var temp = parseInt(str);
    if (isNaN(temp)) temp = 0;
    return temp;
}

function CDouble(str) {
    var temp = parseFloat(str);
    if (isNaN(temp)) temp = 0;
    return temp;
}

function FormatNumericPoint2(val) {
    var temp = Math.round(parseFloat(val) * 100) / 100;
    if (temp.toString().indexOf(".") < 0) {
        temp = temp.toString() + ".00";
    }
    return temp;
}

function CDecimal(num, Len) {
    var temp = Math.pow(10, Len);
    return Math.round(num * temp) / temp
}

function go_download(){
	var url = window.location.href;
	url = url.replace(location.hash,'');		
	url = url.replace('?isdownload=1','');
	url = url.replace('&isdownload=1','');
	if(url.toLowerCase().indexOf('.php?') > 0){
		url = url + '&isdownload=1';
	}else{
		url = url + '?isdownload=1';
	}

	if(lang_go_download_isSearch == '1'){
		$('#iframe_qpost').attr('src',url);
		
		layer.load(1, {
		  shade: [0.3,'#000']
		});
	}else{
		layer.confirm(lang_go_download_confirm, {btn: [lang_go_download_ok, lang_go_download_cancel],icon: 3, title:lang_go_download_tips}, function(index, layero){
			$('#iframe_qpost').attr('src',url);
			
			layer.load(1, {
			  shade: [0.3,'#000']
			});
			
			layer.close(index);
		}, function(index){
			layer.close(index);
		});
	}
}

