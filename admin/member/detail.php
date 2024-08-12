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
		FJS_AB(L("您没有权限查看"));
	}
}

//图片
if (!empty($DRInfo['identityOpposite'])) {
	$attach = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['identityOpposite']}'");
	$imgpath = str_replace(".", "", $attach['savepath']) . $attach['savename'];
}
if (!empty($DRInfo['identityBack'])) {
	$attach = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['identityBack']}'");
	$imgpath4 = str_replace(".", "", $attach['savepath']) . $attach['savename'];
}
if (!empty($DRInfo['bankCard'])) {
	$attach2 = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['bankCard']}'");
	$imgpath2 = str_replace(".", "", $attach2['savepath']) . $attach2['savename'];
}
if (!empty($DRInfo['addressProof'])) {
	$attach3 = $DB->getDRow("select * from `t_attach` where id = '{$DRInfo['addressProof']}'");
	$imgpath3 = str_replace(".", "", $attach3['savepath']) . $attach3['savename'];
}
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
                                    <h4 class="page-title"><?php echo L('详情') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="post" action="">
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("昵称");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['nickname'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("手机");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['phone'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("邮箱");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['email'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("国籍");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo L($DRInfo['nationality']);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("称谓");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['appellation'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("中文名");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['chineseName'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("出生国家");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['birthCountry'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("出生日期");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['birthDate'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("居住国家");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['livingState'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("城市");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['city'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("住宅地址");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['residentialAddress'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("居住年限");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['residenceTime'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("住宅电话");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['residentialTelephone'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("证件名称");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['realname'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("证件号码");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['identity'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("身份证正面");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><a href="<?php echo $imgpath;?>" target="_blank"><img alt="" src="<?php echo $imgpath;?>" style="width: 200px;height: 200px;"></a></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("身份证反面");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><a href="<?php echo $imgpath4;?>" target="_blank"><img alt="" src="<?php echo $imgpath4;?>" style="width: 200px;height: 200px;"></a></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("开户银行支行名称");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['bankName'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("银行开户姓名");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['accountName'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("银行账号");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['accountNum'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("银行卡照片");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><a href="<?php echo $imgpath2;?>" target="_blank"><img alt="" src="<?php echo $imgpath2;?>" style="width: 200px;height: 200px;"></a></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("地址证明照片");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><a href="<?php echo $imgpath3;?>" target="_blank"><img alt="" src="<?php echo $imgpath3;?>" style="width: 200px;height: 200px;"></a></div></div>
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
