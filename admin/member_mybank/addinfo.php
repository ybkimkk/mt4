<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){	
	$DRInfo = $DB->getDRow("select * from `t_bankcode` where id = '{$Id}' and member_id = '{$DRAdmin['id']}'");
	if(!$DRInfo){
		FJS_AB(L("查询数据失败"));
	}
	
	if (!empty($DRInfo['bankCard'])) {
		$attach2 = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['bankCard']}'");
		$imgpath2 = str_replace(".", "", $attach2['savepath']) . $attach2['savename'];
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
										echo L('新增银行卡');
									}else{
										echo L('修改银行卡信息');
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
                                        <form class="form-horizontal" action="?clause=saveinfo&id=<?php echo $Id;?>" method="post" target="iframe_qpost" id="signupForm">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("开户行");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="bankName" type="text" class="form-control" value="<?php echo $DRInfo['bankName'];?>" placeholder="<?php echo L('请输入您的银行名称'); ?>">
                                                    <span class="help-block m-b-none"><i class="fa fa-info-circle"></i><a style="color:red"><?php echo L('请务必按照《XX银行XX省XX市XX支行》格式填写'); ?></a></span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("开户姓名");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="accountName" type="text" class="form-control" value="<?php echo $DRInfo['accountName'];?>" placeholder="<?php echo L('请输入您的银行开户姓名'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><font color="red">*</font><?php echo L("银行账号");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="accountNum" type="text" class="form-control" value="<?php echo $DRInfo['accountNum'];?>" placeholder="<?php echo L('请输入您的银行账号'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行国际代码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="swiftCode" type="text" class="form-control" value="<?php echo $DRInfo['swiftCode'];?>" placeholder="<?php echo L('请输入您的银行国际代码'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("银行卡照片");?>：</label>
                                                <div class="col-sm-8">
                                                    <a id="btnUp1" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
			                        				<div id="imglist1" class="mt-sm-1"><?php if(strlen($imgpath2)){echo '<a href="' , $imgpath2 , '" target="_blank"><img alt="" src="' , $imgpath2 , '" style="width: 120px;height: 90px;"></a>';} ?></div>
                                                    <input name="bankCard" id="bankCard" type="hidden" class="form-control" value="<?php echo $DRInfo['bankCard'];?>">
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
						}
					}
				});

			});
		</script>
        

        
        

    </body>
</html>
