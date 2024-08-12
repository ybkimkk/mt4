<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){	
	$DRInfo = $DB->getDRow("select * from `t_member` where id = '{$Id}'");
	if(!$DRInfo){
		FJS_AB(L("查询数据失败"));
	}
	
	if ($DRAdmin['_dataRange'] <= 1) {
		$idarr = getunderCustomerIds($DRAdmin['id']);
		$idarr = array_merge(array($DRAdmin['id']), $idarr);
		if (!in_array($DRInfo['id'], $idarr)) {
			FJS_AB(L("您没有权限编辑"));
		}
	}
	
	//图片
	if (!empty($DRInfo['identityOpposite'])) {
		$attach = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['identityOpposite']}'");
		$imgpath = str_replace(".", "", $attach['savepath']) . $attach['savename'];
	}
	if (!empty($DRInfo['identityBack'])) {
		$attach = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['identityBack']}'");
		$imgpath4 = str_replace(".", "", $attach['savepath']) . $attach['savename'];
	}
	if (!empty($DRInfo['bankCard'])) {
		$attach2 = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['bankCard']}'");
		$imgpath2 = str_replace(".", "", $attach2['savepath']) . $attach2['savename'];
	}
	if (!empty($DRInfo['addressProof'])) {
		$attach3 = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['addressProof']}'");
		$imgpath3 = str_replace(".", "", $attach3['savepath']) . $attach3['savename'];
	}
}
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .col-sm-2 {padding-top: 7px;margin-bottom: 0;text-align: right;}
}
</style>

					<div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php 
									if($Id <= 0){
										echo L('新增用户');
									}else{
										echo L('修改用户');
									}
									echo getCurrMt4ServerName();
									?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" action="?clause=saveinfo&id=<?php echo $Id;?>" method="post" target="iframe_qpost" id="signupForm" autocomplete="off">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("邮箱");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="email" type="text" class="form-control" value="<?php echo $DRInfo['email'];?>" placeholder="<?php echo L('请输入您的邮箱,格式'); ?>：32233232@qq.com">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("英文名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="nickname" type="text" class="form-control" value="<?php echo $DRInfo['nickname'];?>" placeholder="<?php echo L('请输入您的英文名或者姓名拼音'); ?>">
                                                </div>
                                            </div>
                                            <?php
                                            if($Id <= 0){
											?>
											<div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("密码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="password" type="password" class="form-control" value="" placeholder="<?php echo L('密码必须是6-12位且包含大小写字母和数字'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("确认密码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="confirm" type="password" class="form-control" value="" placeholder="<?php echo L('请再次输入您的密码'); ?>">
                                                </div>
                                            </div>
                                            <?php
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("手机");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="phone" type="text" class="form-control" value="<?php echo $DRInfo['phone'];?>" placeholder="<?php echo L('请输入您的手机'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("中文名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="chineseName" type="text" class="form-control" value="<?php echo $DRInfo['chineseName'];?>" placeholder="<?php echo L('请输入您的中文名'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("国籍");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control m-b" id="nationality" name="nationality"><?php write_country_option();?></select>
                                                    <script>document.getElementById('nationality').value = '<?php echo $DRInfo['nationality'];?>';</script>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出生日期");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="birthDate" id="birthDate" type="text" class="form-control layer-date" value="<?php echo $DRInfo['birthDate'];?>" placeholder="<?php echo L('请选择您的生日'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("居住地");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control m-b" id="livingState" name="livingState"><?php write_country_option();?></select>
                                                    <script>document.getElementById('livingState').value = '<?php echo $DRInfo['livingState'];?>';</script>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("省份");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="province" type="text" class="form-control" value="<?php echo $DRInfo['province'];?>" placeholder="<?php echo L('请输入您所在的省份'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("城市");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="city" type="text" class="form-control" value="<?php echo $DRInfo['city'];?>" placeholder="<?php echo L('请输入您所在的城市'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("地址");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="residentialAddress" type="text" class="form-control" value="<?php echo $DRInfo['residentialAddress'];?>" placeholder="<?php echo L('请输入您所居住的地址'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("居住年限");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="residenceTime" type="text" class="form-control" value="<?php echo $DRInfo['residenceTime'];?>" placeholder="<?php echo L('请输入您的居住年限'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("住宅电话");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="residentialTelephone" type="text" class="form-control" value="<?php echo $DRInfo['residentialTelephone'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("开户支行名称");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="bankName" type="text" class="form-control" value="<?php echo $DRInfo['bankName'];?>" placeholder="<?php echo L('请输入您的银行名称'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行开户姓名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="accountName" type="text" class="form-control" value="<?php echo $DRInfo['accountName'];?>" placeholder="<?php echo L('请输入您的银行开户姓名'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行账号");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="accountNum" type="text" class="form-control" value="<?php echo $DRInfo['accountNum'];?>" placeholder="<?php echo L('请输入您的银行账号'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行卡照片");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp1" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist1"><?php if(strlen($imgpath2)){echo '<a href="' , $imgpath2 , '" target="_blank"><img alt="" src="' , $imgpath2 , '" id="img1" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="bankCard" id="bankCard" type="hidden" class="form-control" value="<?php echo $DRInfo['bankCard'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行国际代码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="swiftCode" type="text" class="form-control" value="<?php echo $DRInfo['swiftCode'];?>" placeholder="<?php echo L('请输入您的银行国际代码'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("真实姓名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="realname" type="text" class="form-control" value="<?php echo $DRInfo['realname'];?>" placeholder="<?php echo L('请输入您证件的真实姓名'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("证件号码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="identity" type="text" class="form-control" value="<?php echo $DRInfo['identity'];?>" placeholder="<?php echo L('请输入您的证件号码'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("身份证正面照");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp2" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist2"><?php if(strlen($imgpath)){echo '<a href="' , $imgpath , '" target="_blank"><img alt="" src="' , $imgpath , '" id="img1" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="identityOpposite" id="identityOpposite" type="hidden" class="form-control" value="<?php echo $DRInfo['identityOpposite'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("身份证反面照");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp3" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist3"><?php if(strlen($imgpath4)){echo '<a href="' , $imgpath4 , '" target="_blank"><img alt="" src="' , $imgpath4 , '" id="img1" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="identityBack" id="identityBack" type="hidden" class="form-control" value="<?php echo $DRInfo['identityBack'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("地址证明");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp4" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist4"><?php if(strlen($imgpath3)){echo '<a href="' , $imgpath3 , '" target="_blank"><img alt="" src="' , $imgpath3 , '" id="img1" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="addressProof" id="addressProof" type="hidden" class="form-control" value="<?php echo $DRInfo['addressProof'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("确认提交");?></button>
                                                    <button onclick="window.history.back()" type="button" class="btn btn-light"><?php echo L("返回");?></button>
                                                </div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>

					</div>






		<?php
        require_once('footer.php');
        ?>
        
        <script src="/assets/js/ajaxupload.3.5.js"></script>
        <script src="/assets/js/layer/layer.js"></script>
        <script src="/assets/js/validate/jquery.validate.min.js"></script>
        
        
		<script src="/assets/js/datapicker/js/bootstrap-datepicker.min.js"></script>
        <link href="/assets/js/datapicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <?php
        if($CurrLangName == 'zh-cn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.zh-CN.min.js"></script>';
			$DatepickerLangName = 'zh-CN';
		}else if($CurrLangName == 'zh-vn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.vi.min.js"></script>';
			$DatepickerLangName = 'vi';
		}
		?>
        <script>
			$(".layer-date").datepicker({
				<?php
				if(strlen($DatepickerLangName)){
					echo 'language: "' , $DatepickerLangName , '",';
				}
				?>
				keyboardNavigation: !1,
				forceParse: 1,
				autoclose: !0,
				clearBtn: !0,
				format: 'yyyy-mm-dd'
			});
		</script>


		<script>
			/*$.validator.setDefaults({
				highlight: function(e) {
					$(e).closest(".form-group").removeClass("has-success").addClass("has-error")
				},
				success: function(e) {
					e.closest(".form-group").removeClass("has-error").addClass("has-success")
				},
				errorElement: "span",
				errorPlacement: function(e, r) {
					e.appendTo(r.is(":radio") || r.is(":checkbox") ? r.parent().parent().parent() : r.parent())
				},
				errorClass: "help-block m-b-none",
				validClass: "help-block m-b-none"
			}), $().ready(function() {
				var e = "<i class='fa fa-times-circle'></i> ";
				$("#signupForm").validate({
					rules: {
						email: {
							required: !0,
							email: !0
						},
						nickname: {
							required: !0,
							english: "{:L('只能输入字母')}",
							minlength : 3,
							maxlength : 20
						},
						phone: {
							required: !0,
							isNum: "{:L('只能输入数字')}"
						},
						password: {
							required:!0,
							minlength: 6,
							maxlength: 12,
							ckps: "{:L('密码必须包含大小写字母和数字')}"
						},
						confirm: {
							required:!0,
							minlength: 6,
							maxlength: 12,
							equalTo: "#password"
						},
						chineseName: {
							minlength: 2,
							maxlength: 50,
							isChar: "{:L('请输入规范的中文名称')}！"
						},
						identity: {
							isIdCardNo: "{:L('请输入正确的身份证号码')}！"
						},
					},
					messages: {
						email: e + "{:L('请输入您的E-mail')}",
						nickname: {
							required: e + "{:L('请输入您的昵称')}",
							minlength : e + "{:L('最少3个字符')}",
							maxlength : e + "{:L('最多20个字符')}",
							english : e + "{:L('只能输入字母')}"
						},
						phone: {
							required: e + "{:L('请输入您的手机号')}",
							isNum: e + "{:L('只能输入数字')}！"
						},
						password: {
							required:e+"{:L('请输入密码')}",
							minlength: e + "{:L('密码必须6个字符以上')}",
							ckps: e + "{:L('密码必须包含大小写字母和数字')}！"
						},
						confirm: {
							required:e+"{:L('请输入确认密码')}",
							minlength: e + "{:L('密码必须6个字符以上')}",
							equalTo: e + "{:L('两次输入的密码不一致')}"
						},
						chineseName: {
							minlength : e + "{:L('最少2个字符')}",
							maxlength : e + "{:L('最多50个字符')}",
							isChar : e + "{:L('请输入规范的中文名称')}！"
						},
						identity: {
							isIdCardNo : e + "{:L('请输入正确的身份证号码')}！"
						}
					}
				}) 
			});
			jQuery.validator.addMethod("ckps", function(value, element) {
		        var regPass = /^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/;
		        return this.optional(element) || (regPass.test(value));
		    }, "{:L('密码必须包含大小写字母和数字')}");

			jQuery.validator.addMethod("english", function(value, element) {
			    var regEname = /^([a-zA-Z\" "]+)$/;
			    return this.optional(element) || (regEname.test(value));
			}, "{:L('只能输入字母')}");

			jQuery.validator.addMethod("isChar", function(value, element) {
				var regName = /[^\u4e00-\u9fa5\" "]/g;
				return this.optional(element) || !regName.test( value );  
			}, "{:L('请输入规范的中文名称')}");

			jQuery.validator.addMethod("isNum", function(value, element) {
			    var regNum = /[^\d^\" "+]/g;
			    return this.optional(element) || (!regNum.test(value));
			}, "{:L('只能输入数字')}！");

			jQuery.validator.addMethod("isIdCardNo", function(value, element) { 
			    var regcard = /^([A-Za-z0-9]+)$/;
			    return this.optional(element) || (regcard.test(value));
			}, "{:L('请输入正确的身份证号码')}！");
			 
			//验证帐号
			var loginurl = "{:C('LOGIN_URL')}";
			var form = $('#form');
			*/

			$(function() {
				var button = $('#btnUp2'),
					interval,intervalCi = 0;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|JPG|JPEG|PNG|png)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择jpg,png格式的文件');?>!', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							intervalCi++;
							if(intervalCi <= 3){
								button.text(button.text() + '......'.substr(0,intervalCi));
							} else {
								intervalCi = 0;
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
						window.clearInterval(interval);
						this.enable();
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L("上传完成");?>');
							$('#imglist2').html('<a href="' + (savepath + savename) + '" target="_blank"><img src="' + (savepath + savename) + '" style="width:120px;hight:90px;"></a>');
							$("#identityOpposite").val(info.data[0].id);
							
							//alert(info.info);
						}
					}
				});

			});

			$(function() {
				var button = $('#btnUp3'),
					interval,intervalCi = 0;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|png|JPG|JPEG|PNG)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择jpg,png格式的文件');?>!', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							intervalCi++;
							if(intervalCi <= 3){
								button.text(button.text() + '......'.substr(0,intervalCi));
							} else {
								intervalCi = 0;
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
						window.clearInterval(interval);
						this.enable();
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L("上传完成");?>');
							$('#imglist3').html('<a href="' + (savepath + savename) + '" target="_blank"><img src="' + (savepath + savename) + '" style="width:120px;hight:90px;"></a>');
							$("#identityBack").val(info.data[0].id);
							
							//alert(info.info);
						}
					}
				});

			});

			$(function() {
				var button = $('#btnUp1'),
					interval,intervalCi = 0;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|png|JPG|JPEG|PNG)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择jpg,png格式的文件');?>!', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							intervalCi++;
							if(intervalCi <= 3){
								button.text(button.text() + '......'.substr(0,intervalCi));
							} else {
								intervalCi = 0;
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
						window.clearInterval(interval);
						this.enable();
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;
							
							button.text('<?php echo L("上传完成");?>');
							$('#imglist1').html('<a href="' + (savepath + savename) + '" target="_blank"><img src="' + (savepath + savename) + '" style="width:120px;hight:90px;"></a>');
							$("#bankCard").val(info.data[0].id);
							
							//alert(info.info);
						}
					}
				});

			});
 
 


			$(function() {
				var button = $('#btnUp4'),
					interval,intervalCi = 0;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|png|JPG|JPEG|PNG)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择jpg,png格式的文件');?>!', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							intervalCi++;
							if(intervalCi <= 3){
								button.text(button.text() + '......'.substr(0,intervalCi));
							} else {
								intervalCi = 0;
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
						window.clearInterval(interval);
						this.enable();
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;
							
							button.text('<?php echo L("上传完成");?>');
							$('#imglist4').html('<a href="' + (savepath + savename) + '" target="_blank"><img src="' + (savepath + savename) + '" style="width:120px;hight:90px;"></a>');
							$("#addressProof").val(info.data[0].id);
							
							//alert(info.info);
						}
					}
				});

			});
 
 
		</script>
        

        
        

    </body>
</html>
