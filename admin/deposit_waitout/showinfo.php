<?php
$LoadCSSArr = array();
require_once('header.php');


$DRInfo = $DB->getDRow("select * from t_outmoney where id = '{$Id}' and status >= -1");

if ($DRAdmin['_dataRange'] >= 2) {
	$parent_id = 'admin';
} else {
	$parent_id = $DRAdmin['id'];
}
$idarr = getunderCustomerIds($parent_id);
if (!in_array($DRInfo['member_id'], $idarr) && $DRAdmin['id'] != $DRInfo['member_id']) {
	FJS_AB(L("数据查询失败"));
}
_check_member_scope($parent_id, $DRInfo['member_id']);

$member = $DB->getDRow("select * from t_member where id = '{$DRInfo['member_id']}'");
$Audit = $DB->getDRow("select * from t_member where id = '{$DRInfo['adminid']}'");
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
                                    <h4 class="page-title"><?php echo L('出金详情') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="post" action="?clause=saveinfo&id=<?php echo $Id;?>">
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("MT账号");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['mtid'];?></div></div>
                                            </div>
                                            <?php
                                            if($DRInfo['forwordmtlogin'] <= 0){
												?>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("转账方式");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo getouttype($DRInfo['type'],$DRInfo['forwordmtlogin']);?></div></div>
												</div>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("银行名称");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['bankname'];?></div></div>
												</div>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("账户名称");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['forwordname'];?></div></div>
												</div>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("银行账号");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['bankaccount'];?></div></div>
												</div>
												<?php
											}else{
												if ($DRAdmin['ver'] == 5) {
													$result = $DB->getDRow("select `Name` as `NAME` from " . $DRAdmin['mt4dbname'] . ".mt5_users where Login = '{$DRInfo['forwordmtlogin']}'");
												}else{
													$result = $DB->getDRow("select `NAME` from " . $DRAdmin['mt4dbname'] . ".mt4_users where LOGIN = '{$DRInfo['forwordmtlogin']}'");
												}
												?>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("转账方式");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo getouttype($DRInfo['type'],$DRInfo['forwordmtlogin']);?></div></div>
												</div>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("MT转入账户");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['forwordmtlogin'];?></div></div>
												</div>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("转入MT帐户名称");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo $result['NAME'];?></div></div>
												</div>
                                            	<?php
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("证件信息");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php
													if($member['realname'] != '' && $member['identity'] == ''){
														echo $member['realname'];
													}
													if($member['realname'] == '' && $member['identity'] != ''){
														echo $member['identity'];
													}
													if($member['realname'] != '' && $member['identity'] != ''){
														echo $member['realname'] , '/' , $member['identity'];
													}
												?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("手机号");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $member['phone'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("出金金额");?>（USD）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['number'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("出金金额");?>（<?php echo $DRInfo['f_pa'];?>）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo round($DRInfo['amount'],2);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("手续费");?>（USD）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo round($DRInfo['fee'],2);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("手续费");?>（<?php echo $DRInfo['f_pa'];?>）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo round($DRInfo['fee']*$DRInfo['exchange'],2);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("汇率");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['exchange'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("申请时间");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo date('Y-m-d H:i:s',$DRInfo['create_time']);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("处理时间");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo strlen($DRInfo['reply_time']) && $DRInfo['reply_time'] != '0' ? date('Y-m-d H:i:s',$DRInfo['reply_time']) : '';?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("状态");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo getoutstatus($DRInfo['status']) , ' ' , L($DRInfo['content']);?></div></div>
                                            </div>
                                            <?php
                                            if($DRInfo['status'] == 9){
												?>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("审核人");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo $Audit['nickname'];?></div></div>
												</div>
												<?php
											}
											if($DRInfo['status'] == 1){
												?>
												<div class="form-group row">
													<label class="col-sm-2 control-label"><?php echo L("驳回人");?>:</label>
													<div class="col-sm-10"><div class="form-control-static"><?php echo $Audit['nickname'];?></div></div>
												</div>
												<?php
											}
											?>
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
