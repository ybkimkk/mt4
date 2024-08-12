<?php
$LoadCSSArr = array();
require_once('header.php');

$DRInfo = $DB->getDRow("select * from `t_mt4_server` where id = '{$Id}' and `status` = 1");
if(!$DRInfo){
	FJS_AB(L('未找到该数据或权限不足'));
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
									echo L('开户号段设置');
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
                                        <form class="form-horizontal" action="?clause=saveopenmt&id=<?php echo $Id;?>" method="post" target="iframe_qpost">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("直客");?>：</label>
                                                <div class="input-daterange input-group col-sm-8">
                                                    <input type="text" class="form-control" name="direct_start_number" value="<?php echo $DRInfo['direct_start_number'];?>" placeholder="<?php echo L('最小值');?>">
                                                    <div class="input-group-prepend">
                                                      <div class="input-group-text"><?php echo L('到');?></div>
                                                    </div>
                                                    <input type="text" class="form-control" name="direct_end_number" value="<?php echo $DRInfo['direct_end_number'];?>" placeholder="<?php echo L('最大值');?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("代理");?>：</label>
                                                <div class="input-daterange input-group col-sm-8">
                                                    <input type="text" class="form-control" name="agent_start_number" value="<?php echo $DRInfo['agent_start_number'];?>" placeholder="<?php echo L('最小值');?>">
                                                    <div class="input-group-prepend">
                                                      <div class="input-group-text"><?php echo L('到');?></div>
                                                    </div>
                                                    <input type="text" class="form-control" name="agent_end_number" value="<?php echo $DRInfo['agent_end_number'];?>" placeholder="<?php echo L('最大值');?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("员工");?>：</label>
                                                <div class="input-daterange input-group col-sm-8">
                                                    <input type="text" class="form-control" name="member_start_number" value="<?php echo $DRInfo['member_start_number'];?>" placeholder="<?php echo L('最小值');?>">
                                                    <div class="input-group-prepend">
                                                      <div class="input-group-text"><?php echo L('到');?></div>
                                                    </div>
                                                    <input type="text" class="form-control" name="member_end_number" value="<?php echo $DRInfo['member_end_number'];?>" placeholder="<?php echo L('最大值');?>">
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
