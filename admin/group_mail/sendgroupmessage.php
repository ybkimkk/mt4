<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){	
	$DRInfo = $DB->getDRow("select * from `t_message_group` where id = '{$Id}'");
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
										echo L('编辑群发配置');
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
                                        <form class="form-horizontal" action="?clause=savesendgroupmessage&id=<?php echo $Id;?>" method="post" target="iframe_qpost">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("接收号码");?>：</label>
                                                <div class="col-sm-8">
                                                	<textarea name="messagelist"<?php if($Id > 0){echo 'disabled';}?> class="form-control" placeholder="<?php echo L('不填写短信系统会给所有的非管理员会员发送短信。多个号码请使用英文逗号隔开'); ?>"><?php echo $DRInfo['messagelist'];?></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("短信内容");?>：</label>
                                                <div class="col-sm-8">
                                                	<textarea name="content" class="form-control"><?php echo $DRInfo['content'];?></textarea>
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
