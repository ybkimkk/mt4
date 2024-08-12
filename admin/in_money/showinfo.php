<?php
$LoadCSSArr = array();
require_once('header_code.php');


$DRInfo = $DB->getDRow("select * from `t_inmoney` where id = '{$Id}' and status >= 0");

if($DRAdmin['_dataRange'] <= 1) {
	if($DRAdmin['id'] != $DRInfo['member_id']){
		$idarr = D("Member")->getunderCustomerIds($DRAdmin['id']);
		if (!in_array($DRInfo['member_id'], $idarr)) {
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>L("数据查询失败"),
				'btnStr'=>L('返回'),
				'url'=>FPrevUrl(),
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
	}
}

$DRMember = $DB->getDRow("select * from `t_member` where id = '{$DRInfo['member_id']}'");

if($DRInfo['adminid'] > 0){
	$DRAudit = $DB->getDRow("select * from `t_member` where id = '{$DRInfo['adminid']}'");
}else{
	$DRAudit = array();
}
?>

<style>
.col-12{padding:0;}
.card{box-shadow:none;-webkit-box-shadow:none;}
body{ background-color:#ffffff;}

@media screen and (min-width:768px) {
	.form-horizontal .control-label {padding-top: 7px;margin-bottom: 0;text-align: right;}
	.form-horizontal .form-control-static {min-height: 34px;padding-top: 7px;padding-bottom: 7px;margin-bottom: 0;}
}
</style>

<div class="container-fluid">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal">
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("入金MT帐号");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['mtid'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("姓名");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRMember['nickname'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("入金金额");?>（USD）:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['number'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("入金金额");?>（<?php echo $DRInfo['f_currencyTitle'];?>）:</label>
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
													echo ' ' , L($DRInfo['content']);
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
                                            <?php
                                            if($DRInfo['type'] == 'remit'){
												if(strlen(trim($DRInfo['certificate'])) > 0){
													$DTAttach = $DB->getDTable("select * from `t_attach` where id in ({$DRInfo['certificate']})");
												}else{
													$DTAttach = array();
												}
												?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"><?php echo L("汇款凭证");?>:</label>
                                                    <div class="col-sm-10"><div class="form-control-static"><?php
														foreach($DTAttach as $key=>$val){
															$path_ = trim($val["savepath"],".") . $val["savename"];
															echo '<a target="_blank" href="' , $path_ , '"><img src="' , $path_ , '" width="100"></a>';
														}
													?></div></div>
                                                </div>
												<?php
											}else if($DRInfo['type'] == 'gaozhipay'){
												?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"><?php echo L("付款银行代码");?>:</label>
                                                    <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['bankcode']; ?></div></div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"><?php echo L("商户订单");?>:</label>
                                                    <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['payno']; ?></div></div>
                                                </div>
												<?php
											}
											?>
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
                                                    <div class="col-sm-10"><div class="form-control-static"><?php echo $DRAudit['nickname']; ?></div></div>
                                                </div>
												<?php
											}
											if($DRInfo['status'] == 1){
												?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"><?php echo L("驳回人");?>:</label>
                                                    <div class="col-sm-10"><div class="form-control-static"><?php echo $DRAudit['nickname']; ?></div></div>
                                                </div>
												<?php
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-10"><div class="form-control-static"><button onclick="window.parent.layer.closeAll();" type="button" class="btn btn-light"><?php echo L("返回");?></button></div></div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>

</div>






</div>
<script src="/assets/js/app.min.js"></script>
</body>
</html>
