<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');
?>

<style>
.radio-inline{ margin-right:15px;}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('支付币种') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="#nolink" id="addpay" data-target="#myModal" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加币种');?></a>
                                            </div>
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('编号');?></th>
                                                    <th class="no-sort"><?php echo L('名称');?></th>
                                                    <th class="no-sort"><?php echo L('货币符号');?></th>
                                                    <th class="no-sort"><?php echo L('入金汇率');?></th>
                                                    <th class="no-sort"><?php echo L('出金汇率');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where f_status in (0,1)";
	
	$recordCount = intval($DB->getField("select count(*) from `t_pay_currency` {$where}"));
	
	$page = FGetInt('page');
	$pagersize = 20;
	$pageConfig = array(
		'recordCount'=>$recordCount,
		'pagesize'=>$pagersize,
		'pageCurrIndex'=>$page,
		'pageMainLinks'=>5,
		'tplRecordCount'=>L('_RECORDS_条记录，第_PAGE_/_PAGES_页'),
		'showRecordCount'=>true,
		'showPrevPage'=>true,
		'showNextPage'=>true,
	);
	$cnPager = new CPager($pageConfig);
	$sqlRecordStartIndex = $cnPager->FGetSqlRecordStartIndex();
	$query = $DB->query("select * from `t_pay_currency` {$where} order by f_addTime desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['id'] , '</td>';
			echo '<td>' , $rs['f_title'] , '</td>';
			echo '<td>' , $rs['f_pa'] , '</td>';
			echo '<td>';
			if($rs['f_ers'] == 'fixed'){
				//echo L('固定汇率') , ': ' , $rs['f_fixedER'] * 1;
				echo $rs['f_fixedER'] * 1;
			}else if($rs['f_ers'] == 'auto'){
				echo L('自动读取汇率') , ': ' , $rs['f_symbolsER'];
			}else{
				echo '(ERROR)';
			}
			echo '</td>';
			//echo '<td>' , L('充值或提现该币') , ' = ' , L('美元金额') , ' ' , $rs['f_erAlgo'] , ' ' , L('汇率') , '</td>';
			echo '<td>';
			if($rs['f_ers'] == 'fixed'){
				//echo L('固定汇率') , ': ' , $rs['f_fixedEROut'] * 1;
				echo $rs['f_fixedEROut'] * 1;
			}else if($rs['f_ers'] == 'auto'){
				echo L('自动读取汇率') , ': ' , $rs['f_symbolsER'];
			}else{
				echo '(ERROR)';
			}
			echo '</td>';
			echo '<td>' , $rs['f_status'] == 1 ? '<font color="green">' . L('启用') . '</font>' : '<font color="red">' . L('禁用') . '</font>' , '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs modifypay" val="' , $rs['id'] , '" type="button" data-toggle="modal" href="#onlink">' , L('修改') , '</a> ';
			if($rs['f_status'] == 1){
				echo '<a class="btn btn-danger btn-xs forbidenreopen" rel="' , $rs['id'] , '" type="button" href="#onlink">' , L('禁用') , '</a>';
			}else{
				echo '<a class="btn btn-primary btn-xs forbidenreopen" rel="' , $rs['id'] , '" type="button" href="#onlink">' , L('启用') , '</a>';
			}
			echo '</td>';
			echo '</tr>';
		}
	}
?>
                                            </tbody>
                                        </table>

<?php
echo $cnPager->FGetPageList();
?>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->






         <!--新增弹出层-->
	    <div class="modal inmodal" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('支付币种'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form id='addform' name='addform'>
                     	<input type="hidden" id="Id" name="Id" value="">
                        <input type="hidden" name="f_ers" value="fixed">
                        <input type="hidden" name="f_erAlgo" value="×">
		                <div class="modal-body">
		                	<label><?php echo L('名称'); ?>：</label>
		                	<div class="input-group mb-2">
		                        <input type="text" class="form-control" name="f_title" id="f_title">
		                    </div>
		                	<label><?php echo L('货币符号'); ?>：</label>
		                	<div class="input-group mb-2">
		                        <input type="text" class="form-control" name="f_pa" id="f_pa">
		                    </div>
                            <!--
                            <label><?php echo L('汇率来源'); ?>：</label>
                            <div class="input-group mb-2">
                                <div class="radio radio-info radio-inline">
                                    <input type="radio" id="f_ers_fixed" value="fixed" name="f_ers" checked class="setlang">
                                    <label for="f_ers_fixed"> <?php echo L('固定汇率'); ?> </label>
                                </div>
                                <div class="radio radio-inline">
                                    <input type="radio" id="f_ers_auto" value="auto" name="f_ers" class="setlang" >
                                    <label for="f_ers_auto"> <?php echo L('自动读取汇率'); ?></label>
                                </div>
                            </div>
                            -->
                            <label><?php echo L('入金汇率'); ?>：</label>
		                	<div class="input-group mb-2">
		                        <input type="text" class="form-control" name="f_fixedER" id="f_fixedER">
		                    </div>
                            <label><?php echo L('出金汇率'); ?>：</label>
		                	<div class="input-group mb-2">
		                        <input type="text" class="form-control" name="f_fixedEROut" id="f_fixedEROut">
		                    </div>
                            <!--
                            <label><?php echo L('自动读取汇率'); ?>：</label>
		                	<div class="input-group mb-2">
                            	<select name="f_symbolsER" id="f_symbolsER" class="form-control">
                                	<option value="">-=<?php echo L('选择'); ?>=-</option>
									<?php
									/*if($DRAdmin['ver'] == 5){
										$symbols = $DB->getDTable("select * from " . $DRAdmin['mt4dbname'] . ".mt5_prices where Symbol like '%USD%' order by Symbol asc");
										foreach($symbols as $key=>$val){
											echo '<option value="' , $val['Symbol'] , '">' , $val['Symbol'] , ' 【BID ' , number_format($val['BidLast'],5,'.','') , '】【ASK ' , number_format($val['AskLast'],5,'.','') , '】</option>';
										}
									}else{
										$symbols = $DB->getDTable("select * from " . $DRAdmin['mt4dbname'] . ".mt4_prices where SYMBOL like '%USD%' order by SYMBOL asc");
										foreach($symbols as $key=>$val){
											echo '<option value="' , $val['SYMBOL'] , '">' , $val['SYMBOL'] , ' 【BID ' , number_format($val['BID'],5,'.','') , '】【ASK ' , number_format($val['ASK'],5,'.','') , '】</option>';
										}
									}*/
                                    ?>
                                </select>
		                    </div>
                            -->
                            <!--
                            <label><?php echo L('汇率算法'); ?>：</label>
		                	<div class="input-group mb-2">
		                        <select name="f_erAlgo" id="f_erAlgo" class="form-control">
                                	<option value="×"><?php echo L('充值或提现该币'); ?> = <?php echo L('美元金额'); ?> × <?php echo L('汇率'); ?></option>
                                    <option value="÷"><?php echo L('充值或提现该币'); ?> = <?php echo L('美元金额'); ?> ÷ <?php echo L('汇率'); ?></option>
                                </select>
		                    </div>
                            -->
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='addpaybtn' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
	    <!--弹出层-->
   


		<?php
        require_once('footer.php');
        ?>

        <!-- third party js -->
        <script src="/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="/assets/js/vendor/buttons.print.min.js"></script>
        <script src="/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="/assets/js/vendor/dataTables.select.min.js"></script>
        <!-- third party js ends -->
        
        <script src="/assets/js/layer/layer.js"></script>
        
        <script>
			$(document).ready(function() {
				"use strict";

				$("#basic-datatable").DataTable({
					paging:false,//是否允许表格分页
					info:false,//控制是否显示表格左下角的信息
					lengthChange: false,//是否允许用户改变表格每页显示的记录数
					searching: false,//是否允许Datatables开启本地搜索
					ordering: false,//是否允许Datatables开启排序
					aoColumnDefs: [{ 
						bSortable: false, 
						aTargets: ["no-sort"] 
					}]
				});

			});
        </script>
        
        
        
        
        
        
    <script>
    	 $("#savepay").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveinfo";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.modifyform.reset();
                    $("#closeoutmodify").click();
                    setTimeout(function(){
                         document.location.reload();
                    },1000);
                   
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
		$(document).on("click",".closeout",function(){
      	//$(".closeout").click(function(){
             $("#addModal").hide();
        });

		$(document).on("click","#addpay",function(){
       	//$("#addpay").click(function() {
            document.addform.reset();
           	$('#addModal').modal('toggle'); 
        });
        
		$(document).on("click","#addpaybtn",function(){
    	//$("#addpaybtn").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveinfo";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.addform.reset();
                    $(".closeout").click();
                    setTimeout(function(){
                         document.location.reload();
                    },1000);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
        
        $(document).on("click",".modifypay",function(){
    	//$(".modifypay").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=getinfo";
			var ID =  $(this).attr('val');
            $.post(url, "id="+ID, function(data) {
                if (data.status) {
                	$("#Id").val(data.data.id);
                	$("#f_title").val(data.data.f_title);
					$("#f_pa").val(data.data.f_pa);
                	//$("#f_ers_" + data.data.f_ers).click();
                	$("#f_fixedER").val(data.data.f_fixedER);
					$("#f_fixedEROut").val(data.data.f_fixedEROut);
					$("#f_symbolsER").val(data.data.f_symbolsER);
					$("#f_erAlgo").val(data.data.f_erAlgo);

                   	$('#addModal').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
		$(document).on("click","#closeoutmodify",function(){
        //$("#closeoutmodify").click(function(){
             $("#addModal").hide();
        });
        
		$(document).on("click",".forbidenreopen",function(){
		//$('.forbidenreopen').click(function () {
        	var ID =  $(this).attr('rel');
        	var txt = $(this).text();
        	var _this=$(this);
        	var status = "1";
        	var nextText = "",className="";
                var txtbtn=txt;
        	if(txt=="<?php echo L('禁用'); ?>"){
        		status='0';
        		nextText="<?php echo L('启用'); ?>";
        		className="btn-primary";
                txts=txt+"<?php echo L('后将无法支付'); ?>，"+"<?php echo L('请谨慎操作'); ?>！";
        	}else{
        		status='1';
        		nextText="<?php echo L('禁用'); ?>";
        		className="btn-danger";
                txts="<?php echo L('请谨慎操作'); ?>";
        	}
			
			layer.confirm("<?php echo L('您确定要'); ?>"+txtbtn+"<?php echo L('这条信息吗'); ?>", {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
				var url = "?clause=updateStatus";
				$.post(url, "id="+ID+"&status="+status, function(data) {
					if (data.status == 1) {
						layer.msg('<?php echo L('更新成功'); ?>');
						setTimeout(function () {
							document.location.reload();
						}, 700);
					} else if (data.status == 0) {
						layer.msg('<?php echo L('更新失败');?>');
					}

					layer.close(index);
					
				}, 'json');
			}, function(index){
				layer.close(index);
			});	

		});
    </script>
        
        
        
        
        
        

    </body>
</html>
