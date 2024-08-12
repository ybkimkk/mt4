<?php
$LoadCSSArr = array();
require_once('header.php');

$DRInfo = $DB->getDRow("select * from t_credit_record where Id = '{$Id}'");

if ($DRAdmin['_dataRange'] >= 2) {
	$parent_id = 'admin';
} else {
	$parent_id = $DRAdmin['id'];
}
$idarr = getunderCustomerIds($parent_id);
if (!in_array($DRInfo['MemberId'], $idarr) && $DRAdmin['id'] != $DRInfo['MemberId']) {
	FJS_AB(L("数据查询失败"));
}
_check_member_scope($parent_id, $DRInfo['MemberId']);
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
                                    <h4 class="page-title"><?php echo L('赠金详情') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="post">
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("MT账号");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['MtLogin'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("入金规则");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php
													echo L('注册赠金');
												?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("达标奖励");?>（USD）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['Result'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("申请时间");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo date('Y-m-d H:i:s',$DRInfo['CreateTime']);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("MT订单");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php
													if(strlen($DRInfo['Ticket'])){
														echo 'Ticket: ', $DRInfo['Ticket'];
													}else{
														echo L('无');
													}
												?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("状态");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php
													if($DRInfo['Status'] == '0'){
														echo '<span class="label label-info">' , L('未处理') , '</span>';
													}else if($DRInfo['Status'] == '1'){
														echo '<span class="label label-success">' , L('已处理') , '</span>';
													}else if($DRInfo['Status'] == '-1'){
														echo '<span class="label label-success">' , L('已驳回') , '</span>';
													}
												?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("理由");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['Memo'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("审核时间");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php
													if($DRInfo['CheckTime'] != ''){
														echo date('Y-m-d H:i:s',$DRInfo['CheckTime']);
													}
												?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-10"><div class="form-control-static">
                                                	<button onclick="window.history.back();" type="button" class="btn btn-light"><?php echo L("返回");?></button>
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

    </body>
</html>
