<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){	
	$DRInfo = $DB->getDRow("select * from `t_mail_group` where id = '{$Id}'");
	if(!$DRInfo){
		FJS_AB(L('未找到该数据或权限不足'));
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
										echo L('新增群发配置');
									}else{
										echo L('编辑邮件模板');
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
                                        <form class="form-horizontal" action="?clause=savesendgroupemail&id=<?php echo $Id;?>" method="post" target="iframe_qpost">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("收件邮箱");?>：</label>
                                                <div class="col-sm-8">
                                                	<textarea name="emaillist"<?php if($Id > 0){echo 'disabled';}?> class="form-control" placeholder="<?php echo L('不填写邮件系统会给所有的非管理员会员发送邮件。多个邮箱请使用英文逗号隔开'); ?>"><?php echo $DRInfo['emaillist'];?></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮件标题");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="title" type="text" class="form-control" value="<?php echo $DRInfo['title'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("发送邮箱");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="send_email" type="text" class="form-control" value="<?php echo $DRInfo['send_email'] ? $DRInfo['send_email'] : C("EMAIL");?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮箱密码");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="email_pass" type="text" class="form-control" value="<?php echo $DRInfo['email_pass'] ? $DRInfo['email_pass'] : C("EMAIL_PASS");?>" placeholder="<?php echo L('请确保邮箱密码的正确是否'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮箱端口");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="email_port" type="text" class="form-control" value="<?php echo $DRInfo['email_port'] ? $DRInfo['email_port'] : C("EMAIL_PORT");?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮箱SMTP地址");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="email_smtp" type="text" class="form-control" value="<?php echo $DRInfo['email_smtp'] ? $DRInfo['email_smtp'] : C("EMAIL_HOST");?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("发件人昵称");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="email_nickname" type="text" class="form-control" value="<?php echo $DRInfo['email_nickname'] ? $DRInfo['email_nickname'] : C("EMAIL_HONER");?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮件内容");?>：</label>
                                                <div class="col-sm-8">
                                                	<?php
                                                    FCreateCkeditor('content',$DRInfo['content'],array('lang'=>'zh-cn','toolbar'=>'Default','height'=>'450'));
													?>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("提交");?></button>
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

    </body>
</html>
