<?php
$LoadCSSArr = array();
require_once('header.php');

$data = $DRAdmin;

$mt4server = $DB->getDTable("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
if (C("OPEN_MT4_FILEDS") == 'chineseName' && $data['chineseName']) {
	$data['nickname'] = $data['chineseName'];
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
									echo L('申请MT账户');
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
                                                <label class="col-sm-2"><?php echo L("选择MT服务器");?>：</label>
                                                <div class="col-sm-8">
                                                    <select name='mt4_server_id' id="mt4_server_id" class='form-control'>
                                                        <?php
                                                        foreach($mt4server as $key=>$vo){
															echo '<option value="' , $vo['id'] , '">' , $vo['mt4_name'];
															if($vo['real'] == 0){
																echo '（' , L("模拟仓") , '）';
															}else{
																echo '（' , L("真实仓") , '）';
															}
															echo '</option>';
														}
														?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("用户名称");?>：</label>
                                                <div class="col-sm-8">
                                                    <input id="name" name="name" type="text"  readonly="readonly" class="form-control" value="<?php echo $data['nickname'];?>">
                                                </div>
                                            </div>
                                            <?php
                                            if(C('MODIFY_LEVERAGE')=='1'){
												 if(C('LEVERAGES'))
													$strarr = explode(",",C('LEVERAGES'));
												else
													$strarr = array(C('DEFAULT_LEVER'));
												?>
												<div class="form-group row">
                                                    <label class="col-sm-2"><?php echo L("杠杆");?>：</label>
                                                    <div class="col-sm-8">
                                                        <select name='leverage' id="leverage" class='form-control'>
                                                            <?php
                                                            for($i=0;$i<count($strarr);$i++){
                                                                echo '<option value="' , $strarr[$i] , '"';
																if($vo['leverage'] == $strarr[$i]){
																	echo ' selected';
																}
																echo '>1:';
																echo $strarr[$i];
																echo '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
												<?php
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("邮箱");?>：</label>
                                                <div class="col-sm-8">
                                                    <input id="email" name="email" type="text"  readonly="readonly" class="form-control" value="<?php echo $data['email'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("手机");?>：</label>
                                                <div class="col-sm-8">
                                                    <input id="phone" name="phone" type="text"  readonly="readonly" class="form-control" value="<?php echo $data['phone'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("国籍");?>：</label>
                                                <div class="col-sm-8">
                                                    <input id="country" name="country" type="text"  readonly="readonly" class="form-control" value="<?php echo L($data['nationality']);?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("省");?>：</label>
                                                <div class="col-sm-8">
                                                    <input id="province" name="province" type="text"  readonly="readonly" class="form-control" value="<?php echo $data['province'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("城市");?>：</label>
                                                <div class="col-sm-8">
                                                    <input id="city" name="city" type="text"  readonly="readonly" class="form-control" value="<?php echo $data['city'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("地点");?>：</label>
                                                <div class="col-sm-8">
                                                    <input id="address" name="address" type="text"  readonly="readonly" class="form-control" value="<?php echo $data['address'];?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="button" id="formsumit" class="btn btn-primary"><?php echo L("确认并提交开户申请");?></button>
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
        
        
        
        
<script>
	$(document).on("click","#formsumit",function(){
	//$("#formsumit").click(function() {
		$(this).attr('disabled', "disabled");
		$(this).text("<?php echo L('申请提交中');?>...");
		var _this=$(this);
		var url = "?clause=saveapply";
		var form = $(this).closest('form');
		 $.post(url, form.serialize(), function(data) {
				alert(data.info);
				if (data.status) {
				   window.location.href="?";
				   $(".close").click();
				}else{
					 _this.removeAttr("disabled");
					 _this.text("<?php echo L('确认并提交开户申请');?>");
				}
			}, 'json');
	});
	
	$("#leverage").val('<?php echo C('DEFAULT_LEVER');?>');
</script>
        
        
        

    </body>
</html>
