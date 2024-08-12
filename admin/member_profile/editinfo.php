<?php
$LoadCSSArr = array();
require_once('header.php');

//图片
if (!empty($DRAdmin['identityOpposite'])) {
	$attach = $DB->getDRow("select * from `t_attach` where id = '{$DRAdmin['identityOpposite']}'");
	$imgpath = str_replace(".", "", $attach['savepath']) . $attach['savename'];
}
if (!empty($DRAdmin['identityBack'])) {
	$attach = $DB->getDRow("select * from `t_attach` where id = '{$DRAdmin['identityBack']}'");
	$imgpath4 = str_replace(".", "", $attach['savepath']) . $attach['savename'];
}
if (!empty($DRAdmin['bankCard'])) {
	$attach2 = $DB->getDRow("select * from `t_attach` where id = '{$DRAdmin['bankCard']}'");
	$imgpath2 = str_replace(".", "", $attach2['savepath']) . $attach2['savename'];
}
if (!empty($DRAdmin['addressProof'])) {
	$attach3 = $DB->getDRow("select * from `t_attach` where id = '{$DRAdmin['addressProof']}'");
	$imgpath3 = str_replace(".", "", $attach3['savepath']) . $attach3['savename'];
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
									echo L('修改资料');
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
                                        <form class="form-horizontal" action="?clause=saveeditinfo&id=<?php echo $Id;?>" method="post" target="iframe_qpost" id="signupForm" autocomplete="off">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮箱");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
                                                    <?php echo $DRAdmin['email'];?>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("头像");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp5" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('上传图片'); ?></a>
                                                	<span><?php echo L('请上传200*200像素 JPG PNG图片'); ?></span>
                                                    <div id="imglist5" class="mt-sm-1"><img src="<?php echo $DRAdmin['headimg']; ?>" style="width: 100px;height: 100px;"></div>
                                                    <input id="headimg" name="headimg" value="<?php echo $DRAdmin['headimg']; ?>" type="hidden">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("英文名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="nickname" type="text" class="form-control" value="<?php echo $DRAdmin['nickname'];?>" placeholder="<?php echo L('请输入您的英文名或者姓名拼音'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("手机");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
                                                    <?php echo $DRAdmin['phone'];?>
                                                    <a href="?clause=editphone"><?php echo L('修改手机'); ?></a>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("中文名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="chineseName" type="text" class="form-control" value="<?php echo $DRAdmin['chineseName'];?>" placeholder="<?php echo L('请输入您的中文名'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("国籍");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control m-b" id="nationality" name="nationality"><?php write_country_option();?></select>
                                                    <script>document.getElementById('nationality').value = '越南';</script>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出生日期");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="birthDate" id="birthDate" type="text" class="form-control layer-date" value="<?php echo $DRAdmin['birthDate'];?>" placeholder="<?php echo L('请选择您的生日'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("居住地");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control m-b" id="livingState" name="livingState"><?php write_country_option();?></select>
                                                    <script>document.getElementById('livingState').value = '越南';</script>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("省份");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="province" type="text" class="form-control" value="<?php echo $DRAdmin['province'];?>" placeholder="<?php echo L('请输入您所在的省份'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("城市");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="city" type="text" class="form-control" value="<?php echo $DRAdmin['city'];?>" placeholder="<?php echo L('请输入您所在的城市'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("地址");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="residentialAddress" type="text" class="form-control" value="<?php echo $DRAdmin['residentialAddress'];?>" placeholder="<?php echo L('请输入您所居住的地址'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("居住年限");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="residenceTime" type="text" class="form-control" value="<?php echo $DRAdmin['residenceTime'];?>" placeholder="<?php echo L('请输入您的居住年限'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("住宅电话");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="residentialTelephone" type="text" class="form-control" value="<?php echo $DRAdmin['residentialTelephone'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("开户支行名称");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="bankName" type="text" class="form-control" value="<?php echo $DRAdmin['bankName'];?>" placeholder="<?php echo L('请输入您的银行名称'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行开户姓名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="accountName" type="text" class="form-control" value="<?php echo $DRAdmin['accountName'];?>" placeholder="<?php echo L('请输入您的银行开户姓名'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行账号");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="accountNum" type="text" class="form-control" value="<?php echo $DRAdmin['accountNum'];?>" placeholder="<?php echo L('请输入您的银行账号'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行卡照片");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp1" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist1"><?php if(strlen($imgpath2)){echo '<a href="' , $imgpath2 , '" target="_blank"><img alt="" src="' , $imgpath2 , '" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="bankCard" id="bankCard" type="hidden" class="form-control" value="<?php echo $DRAdmin['bankCard'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行国际代码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="swiftCode" type="text" class="form-control" value="<?php echo $DRAdmin['swiftCode'];?>" placeholder="<?php echo L('请输入您的银行国际代码'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("真实姓名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="realname" type="text" class="form-control" value="<?php echo $DRAdmin['realname'];?>" placeholder="<?php echo L('请输入您证件的真实姓名'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("证件号码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="identity" type="text" class="form-control" value="<?php echo $DRAdmin['identity'];?>" placeholder="<?php echo L('请输入您的证件号码'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("身份证正面照");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp2" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist2"><?php if(strlen($imgpath)){echo '<a href="' , $imgpath , '" target="_blank"><img alt="" src="' , $imgpath , '" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="identityOpposite" id="identityOpposite" type="hidden" class="form-control" value="<?php echo $DRAdmin['identityOpposite'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("身份证反面照");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp3" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist3"><?php if(strlen($imgpath4)){echo '<a href="' , $imgpath4 , '" target="_blank"><img alt="" src="' , $imgpath4 , '" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="identityBack" id="identityBack" type="hidden" class="form-control" value="<?php echo $DRAdmin['identityBack'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("地址证明");?>：</label>
                                                <div class="col-sm-8">
                                                	<a id="btnUp4" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist4"><?php if(strlen($imgpath3)){echo '<a href="' , $imgpath3 , '" target="_blank"><img alt="" src="' , $imgpath3 , '" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="addressProof" id="addressProof" type="hidden" class="form-control" value="<?php echo $DRAdmin['addressProof'];?>">
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
			
			
			
			
			
			
			

			$(function() {
				var button = $('#btnUp5'),
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
							$('#imglist5').html('<a href="' + (savepath + savename) + '" target="_blank"><img src="' + (savepath + savename) + '" style="width:120px;hight:90px;"></a>');
							$("#headimg").val(savepath + savename);
							
							//alert(info.info);
						}
					}
				});

			});
			
			
			
			
			
			
 
 
		</script>
        

        
        

    </body>
</html>
