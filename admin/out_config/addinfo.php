<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){	
	$DRInfo = $DB->getDRow("select * from `t_out_config` where id = '{$Id}'");
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
										echo L('新增提现设置');
									}else{
										echo L('修改提现设置');
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
                                        <form class="form-horizontal" action="?clause=saveinfo&id=<?php echo $Id;?>" method="post" target="iframe_qpost">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("Key");?>：</label>
                                                <div class="col-sm-8">
                                                    <select name="PayCode" class="form-control">
                                                        <option value="">-=<?php echo L('选择'); ?>=-</option>
                                                        <?php
														$DirPath = $_SERVER['DOCUMENT_ROOT'] . '/out/';
														foreach(scandir($DirPath) as $afile){
															if($afile == '.' || $afile == '..'){
																continue;
															}
															if(is_dir($DirPath.$afile)){
																echo '<option value="' , $afile , '"' , $DRInfo['PayCode'] == $afile ? ' selected' : '' , '>' , $afile , '</option>';
															}
														}
                                                        ?>
                                                    </select>
                                                    <!--<input name="PayCode" type="text" class="form-control" value="<?php echo $DRInfo['PayCode'];?>">-->
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("名称");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="PayName" type="text" class="form-control" value="<?php echo $DRInfo['PayName'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("币种");?>：</label>
                                                <div class="col-sm-8">
                                                    <select name="f_currencyId" class="form-control">
                                                        <option value="">-=<?php echo L('选择'); ?>=-</option>
                                                        <?php
                                                        $symbols = $DB->getDTable("select * from t_pay_currency where f_status in (0,1) order by id desc");
                                                        foreach($symbols as $key=>$val){
                                                            echo '<option value="' , $val['id'] , '"' , $val['id'] == $DRInfo['f_currencyId'] ? ' selected' : '' , '>' , $val['f_title'] , ' (' , $val['f_pa'] , ')</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("手续费");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="f_fee" type="text" class="form-control" value="<?php echo $DRInfo['f_fee'];?>" placeholder="1%填写0.01，类推">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("类型");?>：</label>
                                                <div class="col-sm-8">
                                                    <div class="form-check-inline mt-sm-1">
                                                        <div class="radio radio-info radio-inline mr-sm-2">
                                                            <input id="PayIsOnline0" value="0" name="PayIsOnline" type="radio"<?php echo $DRInfo['PayIsOnline'] <= 0 ? ' checked="checked"' : '';?>>
                                                            <label for="PayIsOnline0"><?php echo L("线下转账");?></label>
                                                        </div>
                                                        <div class="radio radio-info radio-inline">
                                                            <input id="PayIsOnline1" value="1" name="PayIsOnline" type="radio"<?php echo $DRInfo['PayIsOnline'] > 0 ? ' checked="checked"' : '';?>>
                                                            <label for="PayIsOnline1"><?php echo L("线上");?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("商户号");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="PayKey" type="text" class="form-control" value="<?php echo $DRInfo['PayKey'] ;?>" placeholder="<?php echo L('线上类型才需要填写');?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("密钥");?>：</label>
                                                <div class="col-sm-8">
                                                	<textarea class="form-control" name="PaySignKey" placeholder="<?php echo L('线上类型才需要填写');?>"><?php echo $DRInfo['PaySignKey'] ;?></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("网关地址");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="submit_gateway" type="text" class="form-control" value="<?php echo $DRInfo['submit_gateway'] ;?>" placeholder="<?php echo L('线上类型才需要填写');?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("排序");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="sort" type="text" class="form-control" value="<?php echo strlen($DRInfo['sort']) > 0 ? $DRInfo['sort'] : 999 ;?>">
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
