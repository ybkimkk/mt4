<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');

$email = urldecode(FGetStr('email'));
$mtserver = FGetInt('mtserver');
$pkey = FGetStr('key');

$sendmail = $DB->getDRow("select * from t_mail_log where user_email = '{$email}' and status = 1 order by id desc");
$info = $DB->getDRow("select * from t_member where email = '{$email}' and status = 1 and server_id = '{$mtserver}'");
if ($info['active_code'] != $pkey || time() - $sendmail['create_time'] > 3600 || !$info) {
	FJS_AT(L("该重置密码链接已失效，请重新操作"),'/');
}

if($Clause == 'passwordinsert'){
	$password = FPostStr('password');
	$regpassword = FPostStr('regpassword');
	
	if ($password != $regpassword) {
		FJS_AB(L("两次密码不相同"));
	}
	
	$pattern = "/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/";
	if (!preg_match($pattern, $password)) {
		FJS_AB(L("密码必须是6-12位且包含大小写字母和数字"));
	}
	
	$res = $DB->query("update t_member set password = '" . md5($password) . "',update_time = '" . time() . "' where id = '" . $info['id'] . "'");
	if ($res) {
		FJS_AT(L('密码重置成功'),'login.php');
	} else {
		FJS_AB(L("密码重置失败，请刷新页面重试"));
	}
}
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo L('密码重置') , ' - ' , $mt4server['mt4_name'] , ' - ' , $webConfig['f_title'];?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="<?php echo $webConfig['f_description'];?>" name="description" />
        <meta content="Coderthemes" name="author" />
        <link rel="shortcut icon" href="/favicon.ico">
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/cz.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="authentication-bg">

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5">
                        <div class="card">
                            <!-- Logo-->
                            <div class="card-header pt-3 pb-3 text-center bg-primary bg-primary-rlf-cz">
                                <a href="#nolink">
                                    <span><img src="<?php echo C('WEB_LOGO_FILE');?>" alt="" height="50"></span>
                                </a>
                            </div>

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <h4 class="text-dark-50 text-center mt-0 font-weight-bold"><?php echo L('密码重置');?></h4>
                                    <p class="text-muted mb-4"><?php
									$ci = 0;
                                    foreach($LangNameList['list'] as $key=>$val){
										if($ci > 0){
											echo '&nbsp; | &nbsp;';
										}
										echo '<a href="set_lang.php?lang=' , $key , '">' , $val['title'] , '</a>';
										$ci++;
									}
									?></p>
                                </div>
                                
                            <form class="form-horizontal m-t" action="?clause=passwordinsert&email=<?php echo urlencode($email);?>&mtserver=<?php echo $mtserver;?>&key=<?php echo $pkey;?>" method="post" id="signupForm" autocomplete="off">
								<div class="form-group">
									<label class="control-label"><?php echo L('新密码');?><font color=red>*</font>：</label>
									<input value="" id="password" name="password" class="form-control" placeholder="<?php echo L('请输入您的新密码');?>" type="password">
								</div>
								<div class="form-group">
									<label class="control-label"><?php echo L('确认密码');?><font color=red>*</font>：</label>
									<input value="" id="regpassword" name="regpassword" class="form-control" type="password" placeholder="<?php echo L('确认密码');?>">
								</div>
                                <div class="form-group mb-0 text-center">
                                    <button class="btn btn-primary" type="submit" onclick="return checkpassword();"><?php echo L('确定');?></button>
                                </div>
							</form>

                                
                                
                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->
						<!--
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p class="text-muted">Already have account? <a href="login.php" class="text-muted ml-1"><b>Log In</b></a></p>
                            </div>
                        </div>
                        -->
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

		<!--
        <footer class="footer footer-alt">&nbsp;</footer>
        -->

        <!-- App js -->
        <script src="/assets/js/app.min.js"></script>
        
        <script src="/assets/js/ajaxupload.3.5.js"></script>
        <script src="/assets/js/layer/layer.js"></script>        
        
        <script src="/assets/js/validate/jquery.validate.min.js"></script>
        
        
 		<script>
			function checkpassword() {
				if($("#password").val()==""){
					alert(<?php echo L("请输入您的新密码");?>);
					return false;
				}
				if($("#regpassword").val()=="")
				{
					alert(<?php echo L("请重复输入密码");?>);
					return false;
				}
				if($("#password").val()!=$("#regpassword").val())
				{
					alert(<?php echo L("两次密码输入不一致，请重新输入");?>);
					$("#password").focus();
					$("#regpassword").focus();
					return false;
				}
				return true;
			}   
		</script>  

        
        
    </body>
</html>
