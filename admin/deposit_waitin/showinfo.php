<?php
$LoadCSSArr = array();
require_once('header.php');


$DRInfo = $DB->getDRow("select * from `t_inmoney` where id = '{$Id}' and status >= 0");

if ($DRAdmin['_dataRange'] <= 1) {
	$idarr = getunderCustomerIds($DRAdmin['id']);
	if (!in_array($DRInfo['member_id'], $idarr) && $DRAdmin['id'] != $DRInfo['member_id']) {
		FJS_AB(L("数据查询失败"));
	}
}

$member = $DB->getDRow("select * from t_member where id = '{$DRInfo['member_id']}'");

if(strlen($DRInfo['certificate'])){
	$attach = $DB->getDTable("select * from t_attach where id in ({$DRInfo['certificate']})");
}else{
	$attach = array();
}

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
                                    <h4 class="page-title"><?php echo L('入金详情') , getCurrMt4ServerName();?></h4>
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
                                                <label class="col-sm-2 control-label"><?php echo L("入金MT帐号");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['mtid'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("姓名");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $member['nickname'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("入金金额");?>（USD）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['number'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("入金金额");?>（<?php if($DRInfo['type'] == 'nganluong'){echo 'VDN';}else{echo 'RMB';} ?>）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['price'];?></div></div>
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
                                                <label class="col-sm-2 control-label"><?php echo L("状态");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo getinstatus($DRInfo['status']);
												echo '<br>' , $DRInfo['content'];
												?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("处理时间");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo strlen($DRInfo['visit_time']) ? date('Y-m-d H:i:s',$DRInfo['visit_time']) : '';?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("付款方式");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo getpaytype($DRInfo['type']); ?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("汇款凭证");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php
												foreach($attach as $key=>$val){
													echo '<a target="_blank" href="' , trim($val["savepath"],".") , $val["savename"] , '"><img src="' , trim($val["savepath"],".") , $val["savename"] , '" width="100"></a>';
												}
												if(strlen(trim($DRInfo['serialno'])) > 0){
													echo '<br>' , $DRInfo['serialno'];
												}
												?></div></div>
                                            </div>
                                            <?php
                                            if($DRInfo['type'] == 'gaozhipay'){
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("付款银行代码");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['bankcode']; ?></div></div>
                                            </div>
                                            <?php
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("商户订单");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['payno']; ?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("支付订单");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['serialno']; ?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("支付状态");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo getpaystatus($DRInfo['paystatus']); ?></div></div>
                                            </div>
                                            <?php
                                            if($DRInfo['status'] == 9 && $DRInfo['content'] == '手动审核'){
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("审核人");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $Audit['nickname']; ?></div></div>
                                            </div>
                                            <?php
											}
                                            if($DRInfo['status'] == 1){
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("驳回人");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $Audit['nickname']; ?></div></div>
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
