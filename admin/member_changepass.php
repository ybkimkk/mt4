<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();
	
	$uid = $DRAdmin['id'];
	
	$password = FPostStr('oldpassword');
	$newpassword = FPostStr('newpassword');
	$confirm_password = FPostStr('confirm_password');
	
	if ($newpassword != $confirm_password) {
		FJS_AB(L("两次输入密码不一致"));
	}
	$pattern = "/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/";
	if (!preg_match($pattern, $newpassword)) {
		FJS_AB(L("新密码必须是6-12位且包含大小写字母和数字"));
	}
	$info = $DB->getDRow("select * from t_member where id = '{$uid}' and password = '" . md5($password) . "'");
	if ($info) {
		$DB->query("update t_member set password = '" . md5($newpassword) . "' where id = '{$uid}'");

		FJS_AT(L("密码修改成功"),'login.php');
	} else {
		FJS_AB(L("原密码错误，请重新输入"));
	}
}


$LoadCSSArr = array();
require_once('header.php');
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .control-label {padding-top: 7px;margin-bottom: 0;text-align: right;}
	.form-horizontal .form-control-static {min-height: 34px;padding-top: 7px;padding-bottom: 7px;margin-bottom: 0;}
}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('修改密码') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" id="signupForm" method="post" action="?clause=saveinfo">
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8"><div class="form-control-static"><?php echo L('这里仅限修改CRM邮箱登录密码，如果需要修改MT帐号密码请去账户管理或者MT客户端修改');?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("原密码");?>:</label>
                                                <div class="col-sm-8"><input name="oldpassword" id="oldpassword" type="password" class="form-control" value=""></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("新密码");?>:</label>
                                                <div class="col-sm-8"><input name="newpassword" id="newpassword" type="password" class="form-control" value=""></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("确认密码");?>:</label>
                                                <div class="col-sm-8"><input name="confirm_password" id="confirm_password" type="password" class="form-control" value=""></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8"><div class="form-control-static">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("保存");?></button>
                                                </div></div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                        


                    </div> <!-- container -->


		<?php
        require_once('footer.php');
        ?>
        
        
        
        
<script src="/assets/js/validate/jquery.validate.min.js"></script>

<script>
$.validator.setDefaults({highlight: function(e) {
        $(e).closest(".form-group").removeClass("has-success").addClass("has-error")
    }, success: function(e) {
        e.closest(".form-group").removeClass("has-error").addClass("has-success")
    }, errorElement: "span", errorPlacement: function(e, r) {
        e.appendTo(r.is(":radio") || r.is(":checkbox") ? r.parent().parent().parent() : r.parent())
    }, errorClass: "help-block m-b-none", validClass: "help-block m-b-none"}), $().ready(function() {
    var e = "<i class='fa fa-times-circle'></i> ";
    $("#signupForm").validate({
        rules: {  
            newpassword: {
                required: !0, 
                minlength: 6,
                ckps: "<?php echo L('密码必须包含大小写字母和数字'); ?>"
            }, 
            confirm_password: {
                required: !0, 
                minlength: 6, 
                equalTo: "#newpassword"
            }, 
            agree: "required"
        }, 
        messages: {   
            newpassword: {
                required: e + "<?php echo L('请输入您的密码'); ?>", 
                minlength: e + "<?php echo L('密码必须6个字符以上'); ?>",
                ckps: e + "<?php echo L('密码必须包含大小写字母和数字'); ?>"
            }, 
            confirm_password: {
                required: e + "<?php echo L('请再次输入密码'); ?>", 
                minlength: e + "<?php echo L('密码必须6个字符以上'); ?>", 
                equalTo: e + "<?php echo L('两次输入的密码不一致'); ?>"
            }
        }
    });
});

    jQuery.validator.addMethod("ckps", function(value, element) {
        var regPass = /^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/;
        return this.optional(element) || (regPass.test(value));
    }, "<?php echo L('密码必须包含大小写字母和数字'); ?>");
</script>
        
        
        
        

    </body>
</html>
