<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){	
	$DRInfo = $DB->getDRow("select * from `t_article` where id = '{$Id}' and server_id = '{$DRAdmin['server_id']}'");
	if(!$DRInfo){
		FJS_AB(L('未找到该数据或权限不足'));
	}
	
	$osLangArr = get_lang_otherset_arr('-_news_-' . $DRInfo['id'],$DRAdmin['server_id']);
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
										echo L('新增通知信息');
									}else{
										echo L('修改通知信息');
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
                                                <label class="col-sm-2">key：</label>
                                                <div class="col-sm-8 mt-1">
                                                	<select name="f_key">
                                                    	<option value=""><?php echo L('通知信息');?></option>
                                                        <option value="reg_prov"<?php if($DRInfo['f_key'] == 'reg_prov'){echo ' selected';};?>><?php echo L('《开户协议》');?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php
											foreach($LangNameList['list'] as $keyL=>$valL){
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2" style="background-color:<?php echo $valL['color'];?>"><?php echo L("信息标题");?>-<?php echo $valL['title'];?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="title-<?php echo $keyL;?>" type="text" class="form-control" value="<?php echo $osLangArr[$keyL]['f_title'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2" style="background-color:<?php echo $valL['color'];?>"><?php echo L("信息内容");?>-<?php echo $valL['title'];?>：</label>
                                                <div class="col-sm-8">
                                                	<?php
                                                    FCreateCkeditor('content-' . $keyL,$osLangArr[$keyL]['f_val'],array('lang'=>'zh-cn','toolbar'=>'Default','height'=>'450'));
													?>
                                                </div>
                                            </div>
                                            <?php
											}
											?>
                                            <!--
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("信息类型");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" name="cid">
                                                        <option value="1"<?php echo $DRInfo['cid'] == 1 ? ' selected' : '';?>><?php echo L('系统通知');?></option>
                                                         <option value="2"<?php echo $DRInfo['cid'] == 2 ? ' selected' : '';?>><?php echo L('滚动通知');?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            -->
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("发布时间");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="create_time" type="text" class="form-control" value="<?php echo $DRInfo['create_time'] ? date('Y-m-d H:i:s',$DRInfo['create_time']) : date('Y-m-d H:i:s');?>" placeholder="<?php echo L('格式参考') , ': ' , date('Y-m-d H:i:s');?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("显示顺序");?>：</label>
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
