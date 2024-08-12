<?php
$LoadCSSArr = array();
require_once('header.php');

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
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .col-sm-2 {padding-top: 7px;margin-bottom: 0;text-align: right;}

	.form-horizontal .control-label {padding-top: 7px;margin-bottom: 0;text-align: right;}
	.form-horizontal .form-control-static {min-height: 34px;padding-top: 7px;padding-bottom: 7px;margin-bottom: 0;}
}
</style>

					<div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php 
									echo L('重置CRM邮箱登录密码');
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
                                        <form class="form-horizontal" action="?clause=savepwd&id=<?php echo $Id;?>" method="post" target="iframe_qpost" id="signupForm">
                                        	<div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("提示");?>：</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo L('重置MT账号密码请去账户管理操作');?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮箱");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="email" disabled="disabled" type="text" class="form-control" value="<?php echo $DRInfo['email'];?>">
                                                </div>
                                            </div>
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
		</script>
        

        
        

    </body>
</html>
