<?php
$LoadCSSArr = array();
require_once('header.php');

$mt4server = $DB->getDTable("select * from `t_mt4_server` where `status` = 1");

$alias = explode('@', C('EMAIL'));
$alias = $alias[1];
?>

<style>
.radio-inline{ margin-right:15px;}

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
                                    <h4 class="page-title"><?php echo L('同步客户') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


                                        <form class="form-horizontal" method="post" target="iframe_qpost" action="?clause=init_sync">								 
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("服务器ID"); ?>：</label>                               		
                                                <div class="col-sm-6">
                                                    <select name="serverid" id="serverid" class='form-control m-b'>
                                                    <?php
                                                    foreach($mt4server as $key=>$val){
														echo '<option value="' , $val['id'] , '">';
														echo $val['mt4_name'];
														echo '（';
														if($val['real'] == 0){
															echo L("模拟仓");
														}else{
															echo L("真实仓");
														}
														echo '）';
														echo '</option>';
													}
													?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("同步选项"); ?>：</label>     
                                                <div class="col-sm-6">
                                                    <div class="form-control-static"><label><input type="checkbox" name="cover" id='cover_input' onchange="display()" value="1"><?php echo L("无邮箱无手机虚拟同步"); ?></label></div>
                                                </div>
                                            </div>
                                                
                                            <div class="form-group row" id="alias_div" style="display:none">
                                                <label class="col-sm-2 control-label"><?php echo L("邮箱后缀"); ?>：</label>
                                                <div class="col-sm-6">
                                                    <input type="text" placeholder="<?php echo L("邮箱后缀"); ?>" value="<?php echo $alias;?>" name="alias" class="form-control">
                                                </div>
                                            </div>													
                                                
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-6">
                                                    <button class="btn btn-primary" type="button" id="sync"><?php echo L("确认同步"); ?></button>
                                                </div>
                                            </div>
                                        </form>
                    

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->









		<?php
        require_once('footer.php');
        ?>
        
        <script src="/assets/js/layer/layer.js"></script>

        
    <script>
		$(document).on("click","#sync",function(){
		//$('#sync').click(function() {
			var _this = $(this);
			var form = $(this).closest('form');
			layer.confirm('<?php echo L('您确定要同步数据吗');?>?', {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
				_this.attr('disabled', 'disabled');
				_this.text("<?php echo L('正在同步中');?>...");
				
				form.submit();
				
				layer.close(index);
			}, function(index){
				_this.removeAttr("disabled");
				_this.text("<?php echo L("确认同步"); ?>");
				
				layer.close(index);
			});
		});	
		
		
		$("#serverid").val('<?php echo $DRAdmin['server_id'];?>')
		
		function display(){
			var bischecked=$('#cover_input').is(':checked');
			if(bischecked){
				$("#alias_div").show();
			}else{
				$("#alias_div").hide();
			}
		}
    </script>
        
        
        
        
        
        

    </body>
</html>
