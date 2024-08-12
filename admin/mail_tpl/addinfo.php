<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){	
	$DRInfo = $DB->getDRow("select * from `t_mail_template` where id = '{$Id}'");
	if(!$DRInfo){
		FJS_AB(L('未找到该数据或权限不足'));
	}
	
	$osLangArr = get_lang_otherset_arr($DRInfo['name'],0);
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
										echo L('新增模板');
									}else{
										echo L('编辑邮件模板');
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
                                        	<input name="type" type="hidden" value="1">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("模板名称");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="name" type="text" class="form-control" value="<?php echo $DRInfo['name'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("发送类型");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" name="sendtype">
                                                        <option value="0"<?php echo $DRInfo['sendtype'] == 0 ? ' selected' : '';?>><?php echo L('邮件');?></option>
                                                         <option value="1"<?php echo $DRInfo['sendtype'] == 1 ? ' selected' : '';?>><?php echo L('短信');?></option>
                                                    </select>
                                                </div>
                                            </div>                 
                                            <!--                           
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮件类型");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" name="type">
                                                        <option value="0"<?php echo $DRInfo['type'] == 0 ? ' selected' : '';?>><?php echo L('纯文本邮件');?></option>
                                                        <option value="1"<?php echo $DRInfo['type'] == 1 ? ' selected' : '';?>><?php echo L('HTML邮件');?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            -->
                                            <?php
											foreach($LangNameList['list'] as $keyL=>$valL){
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2" style="background-color:<?php echo $valL['color'];?>"><?php echo L("主题");?>-<?php echo $valL['title'];?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="title-<?php echo $keyL;?>" type="text" class="form-control" value="<?php echo $osLangArr[$keyL]['f_title'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2" style="background-color:<?php echo $valL['color'];?>"><?php echo L("模板内容");?>-<?php echo $valL['title'];?>：</label>
                                                <div class="col-sm-8">
                                                	<?php
                                                    FCreateCkeditor('content-' . $keyL,$osLangArr[$keyL]['f_val'],array('lang'=>'zh-cn','toolbar'=>'Default','height'=>'450'));
													?>
                                                </div>
                                            </div>
                                            <?php
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("模板变量");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><textarea class="form-control" name="var"><?php echo $DRInfo['var'];?></textarea></div></div>
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
