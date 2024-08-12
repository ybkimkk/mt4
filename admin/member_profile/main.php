<?php
$LoadCSSArr = array();
require_once('header.php');

//print_r($DRAdmin);exit;

$uid = $DRAdmin['id'];
$server_id = $DRAdmin['server_id'];
if (!$DRAdmin['avatar']) {
	$url = C('cz_REGISTER_URL') . "?fromuser=" . $DRAdmin['id'];
	$DRAdmin['avatar'] = showqrcode($url, 8, '/upload/member/qrcode/' . md5($DRAdmin['id'] . $DRAdmin['email']) . '.png');
	$DB->query("update t_member set `avatar` = '{$DRAdmin['avatar']}' where id = '{$uid}'");
}

if ($DRAdmin['recomment_groups']) {
	$recomment_groups = explode(',', $DRAdmin['recomment_groups']);
	foreach ($recomment_groups as $key => $value) {
		$url = C('cz_REGISTER_URL') . "?fromuser=" . $DRAdmin['id'] . "&IA=" . md5($value);
		$data_qrcode[$key]['remark'] = $DB->getField("select group_remark from t_groups where `group` = '{$value}' and server_id = '{$DRAdmin['server_id']}'");
		$data_qrcode[$key]['link'] = $url;
		$data_qrcode[$key]['avatar'] = showqrcode($url, 8, '/upload/member/qrcode/' . md5($DRAdmin['id'] . $DRAdmin['email'] . $value) . '.png');
	}
}

if (!empty($DRAdmin['identityOpposite'])) {
	$attach = $DB->getDRow("select * from t_attach where id = '{$DRAdmin['identityOpposite']}'");
	$imgpath = str_replace(".", "", $attach['savepath']) . $attach['savename'];
}
if (!empty($DRAdmin['identityBack'])) {
	$attach = $DB->getDRow("select * from t_attach where id = '{$DRAdmin['identityBack']}'");
	$imgpath1 = str_replace(".", "", $attach['savepath']) . $attach['savename'];
}
if (!empty($DRAdmin['bankCard'])) {
	$attach2 = $DB->getDRow("select * from t_attach where id = '{$DRAdmin['bankCard']}'");
	$imgpath2 = str_replace(".", "", $attach2['savepath']) . $attach2['savename'];
}
if (!empty($DRAdmin['addressProof'])) {
	$attach3 = $DB->getDRow("select * from t_attach where id = '{$DRAdmin['addressProof']}'");
	$imgpath3 = str_replace(".", "", $attach3['savepath']) . $attach3['savename'];
}

$isopencode = $DB->getField("select configvalue from t_config_server where configname = 'REGEX_FILEDS' and server_id = '{$DRAdmin['server_id']}'");
$isopencode = explode(",", $isopencode);

$invent_code = $DB->getField("select loginid from t_member_mtlogin where member_id = '{$DRAdmin['id']}' and mtserver = '{$DRAdmin['server_id']}' and status = 1 and mt_type = 0");
if (!$invent_code) {
	$invent_code = $DB->getField("select loginid from t_member_mtlogin where member_id = '{$DRAdmin['id']}' and mtserver = '{$DRAdmin['server_id']}' and status = 1");
}
?>


                    <!-- Start Content-->
                    <div class="container-fluid">
                    
                    
                    

    <div class="row mt-sm-3">
        <div class="col-sm-12">
            <!-- Profile -->
            <div class="card bg-default">
                <div class="card-body profile-user-box">

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="media">
                                <span class="float-left m-2 mr-4"><img src="<?php echo $DRAdmin['headimg']?>" style="height: 100px;" alt="" class="rounded-circle img-thumbnail"></span>
                                <div class="media-body mt-4">
                                    <h4 class="mt-1 mb-1"><?php echo $DRAdmin['nickname']?></h4>
                                    <p class="font-13"><?php echo $DRAdmin['email']?></p>
                                </div> <!-- end media-body-->
                            </div>
                        </div> <!-- end col-->

                        <div class="col-sm-4">
                            <div class="text-center mt-sm-0 mt-3 text-sm-right">
                                <button type="button" class="btn btn-danger" onclick="window.location.href='?clause=editinfo'">
                                    <i class="mdi mdi-account-edit mr-1"></i> <?php echo L('修改资料');?>
                                </button>
                            </div>
                        </div> <!-- end col-->
                    </div> <!-- end row -->

                </div> <!-- end card-body/ profile-user-box-->
            </div><!--end profile/ card -->
        </div> <!-- end col-->
    </div>
    <!-- end row -->


    <div class="row">
        <div class="col-md-<?php if($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0){echo '12';}else{echo '8';}?>">
            <!-- Personal-Information -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mt-0 mb-3"><?php echo L('用户资料');?></h4>
                    <hr/>

                    <div class="text-left">
                        <p class="text-muted"><strong><?php echo L('昵称');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['nickname'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('中文名');?> :</strong><span class="ml-2"><?php echo $DRAdmin['chineseName'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('手机');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['phone'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('邮箱');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['email'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('国籍');?> :</strong> <span class="ml-2"><?php echo L($DRAdmin['nationality']);?></span></p>
                        <p class="text-muted"><strong><?php echo L('地址');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['residentialAddress'];?></span></p>
                        <!--<p class="text-muted"><strong><?php echo L('角色');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['role'];?></span></p>-->
                        <p class="text-muted"><strong><?php echo L('开户支行');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['bankName'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('银行开户姓名');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['accountName'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('银行账号');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['accountNum'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('银行国际代码');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['swiftCode'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('银行卡照片');?> :</strong> <span class="ml-2"><div><img src="<?php echo $imgpath2;?>" style="width: 120px;height: 80px;"></div></span></p>
                        <p class="text-muted"><strong><?php echo L('真实姓名');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['realname'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('证件号');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['identity'];?></span></p>
                        <p class="text-muted"><strong><?php echo L('身份证正面照');?> :</strong> <span class="ml-2"><div><img src="<?php echo $imgpath;?>" style="width: 120px;height: 80px;"></div></span></p>
                        <p class="text-muted"><strong><?php echo L('身份证反面照');?> :</strong> <span class="ml-2"><div><img src="<?php echo $imgpath1;?>" style="width: 120px;height: 80px;"></div></span></p>
                        <p class="text-muted"><strong><?php echo L('地址证明');?> :</strong> <span class="ml-2"><div><img src="<?php echo $imgpath3;?>" style="width: 120px;height: 80px;"></div></span></p>
                        <!--<p class="text-muted"><strong><?php echo L('备注');?> :</strong> <span class="ml-2"><?php echo $DRAdmin['remark'];?></span></p>-->
                    </div>
                </div>
            </div>
            <!-- Personal-Information -->
        </div> <!-- end col-->

		<?php if($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0){ 
							
		}else{ ?>
        <div class="col-md-4">

            <!-- Chart-->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3"><?php echo L('我的推广信息');?></h4>
                    <hr/>
                    <div>
                        <form class="form-horizontal" action="#" method="post" novalidate="novalidate">
                            <?php if(strlen($DRAdmin['recomment_groups']) <= 0){ ?>
                                    <div class="form-group">
                                        <label class="control-label"><?php echo L('开户链接'); ?></label>
                                        <p class="form-control-static"><span id='text'><?php echo C('cz_REGISTER_URL'); ?>?fromuser=<?php echo $DRAdmin['id'];?></span></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?php echo L('推广二维码'); ?></label>
                                        <p class="form-control-static"><span id='text'><img src="<?php echo trim($DRAdmin['avatar'],'.'); ?>" style="width:250px;height:250px;"></span></p>
                                    </div>
                                    <?php
                                        if(in_array('register_invent_codes',$isopencode)){
                                    ?>
                                    <div class="form-group">
                                        <label class="control-label"><?php echo L('推广码'); ?></label>
                                    	<p class="form-control-static"><span id='text'><?php echo $invent_code; ?></span></p>
                                    </div>
                                    <?php
                                    }
									?>
                            <?php }else{ ?>
                                <volist name='data_qrcode' id='vo'>
                                    <div class="form-group">
                                        <label class="control-label"><?php echo L('开户链接'); ?></label>
                                        <p class="form-control-static"><span id='text'>{$vo.link}&nbsp;&nbsp;</span>
                                        <if condition = "$vo['remark']['group_remark'] neq ''">
                                            ({$vo['remark']})
                                        </if>
                                        </p>                                    
                                        <label class="control-label"><?php echo L('推广二维码'); ?></label>
                                        <p class="form-control-static"><span id='text'><img src="<?php echo trim($vo['avatar'],'.'); ?>" style="width: 200px;height: 200px;"></span></p>
                                    </div>
                                </volist>
                            <?php } ?>
                        </form>
                    </div>        
                </div>
            </div>
            <!-- End Chart-->

        </div>
        <!-- end col -->
        <?php } ?>

    </div>
    <!-- end row -->



                    </div> <!-- container -->


		<?php
        require_once('footer.php');
        ?>
        
        
        

    </body>
</html>
