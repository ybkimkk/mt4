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
                                    <h4 class="page-title"><?php echo L('客户等级设置') , getCurrMt4ServerName();?></h4>
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
                                                <a href="#nolink" id="addrank" data-target="#myModal" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加等级');?></a>
                                            </div>
                                            <!--
                                            <div class="col-sm-8">
                                                <div class="text-sm-right">
                                                    <button type="button" class="btn btn-success mb-2 mr-1"><i class="mdi mdi-settings"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 mr-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div>-->
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('编号');?></th>
                                                    <th class="no-sort"><?php echo L('等级名称');?></th>
                                                    <th class="no-sort"><?php echo L('所处等级');?></th>
                                                    <th class="no-sort"><?php echo L('入金量');?></th>
                                                    <th class="no-sort"><?php echo L('客户量') , '（' , L('组') , '）';?></th>
                                                    <th class="no-sort"><?php echo L('交易量');?></th>
                                                    <th class="no-sort"><?php echo L('时间');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$DTArr = array();
	
	$where = "where server_id = '{$DRAdmin['server_id']}' and `status` = 1";
	
	$recordCount = intval($DB->getField("select count(*) from `t_ib_rank` {$where}"));
	
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
	$query = $DB->query("select * from `t_ib_rank` {$where} order by rank asc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			$DTArr[] = $rs;
		}
		
        $step = 0;
        foreach ($DTArr as $key => $value){
            $step++;
            foreach ($DTArr as $key1 => $value1){
                if($value['customer_level'] == 0){
                    $DTArr[$step-1]['customer_level_name'] = L("直接客户");
                    break;
                }elseif($value['customer_level'] == $value1['rank']){
                    $DTArr[$step-1]['customer_level_name'] = $value1['rank_name'];
                    break;
                }
            }
        }
		
		foreach($DTArr as $key=>$rs){
			echo '<tr>';
			echo '<td>' , $rs['id'] , '</td>';
			echo '<td>';
			if($rs['model_type'] == 'member'){
				echo '<span class="badge badge-info">' , L('员工') , '</span> ';
			}else{
				echo '<span class="badge badge-info">' , L('代理') , '</span> ';
			}
			echo $rs['rank_name'];
			echo '</td>';
			echo '<td>' , L($rs['rank'] . '级') , $rs['model_type'] == 'agent' ? L('代理') : '' , '</font></td>';
			echo '<td>';
			if($rs['contain_self'] == 1){
				echo '<span class="badge badge-info">' , L('包括自己') , '</span> ';
			}else{
				echo '<span class="badge badge-success">' , L('仅团队') , '</span> ';
			}
			echo number_format($rs['total_deposit'],2,".",",") , L('美金');
			echo '</td>';
			echo '<td>' , L('满足') , $rs['total_customer'] , L('组') , ' ' , $rs['customer_level_name'] , '</td>';
			echo '<td>' , number_format($rs['total_volume'],2,".",",") , $rs['volume_unit'] == 'USD' ? L('美金') : L('手') , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['update_time']) , '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs modifyrank" val="' , $rs['id'] , '" type="button" data-toggle="modal" href="#onlink">' , L('修改') , '</a> ';
			echo '<a class="btn btn-danger btn-red-cz btn-xs delrank" type="button" href="#nolink" url="?clause=delinfo&id=' , $rs['id'] , '">' , L('删除') , '</a> ';
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






         <!--弹出层-->
	    <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('等级设置'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingform' name='settingform'>
		            	<input type="hidden" name="id" ID="id" value="" />
		            	<input type="hidden" name="scale" ID="scale" value="1" />
		                <div class="modal-body">
		               	 	<div class="row"> 
		                        <div class="col-md-12"> 
				                    <div class="form-group"><label><?php echo L('等级名称'); ?></label> 
										 <input type="text" name='rank_name' id="rank_name" placeholder="<?php echo L('请输入客户等级'); ?>" class="form-control"> 
			                        </div>
		                        </div>
		                    </div>    
	                       	<div class="row"> 
		                        <div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('等级类型'); ?></label> 
				                 		<select name='model_type' id="model_type" class='form-control m-b'>
		                                 	<option value='agent'><?php echo L('代理'); ?></option>
		                                 	<option value='member'><?php echo L('员工'); ?></option>
			                            </select>
				                    </div>
			                    </div>
	                        	<div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('等级值'); ?></label> 
				                 		<input type="text"  style="ime-mode:disabled" name='rank' id="rank" placeholder="<?php echo L('请输入等级值,值越小等级越大'); ?>" class="form-control"> 
				                    </div>
			                    </div>
			                </div>
			                <div class="row"> 
		                        <div class="col-md-6"> 
			                     	<div class="form-group"><label><?php echo L('入金量'); ?>（$）：</label>
				                        <input type="text" class="form-control" name="total_deposit" id="total_deposit" placeholder="<?php echo L('请输入达标入金量'); ?>"> 
				                    </div>
				                 </div>
				                 <div class="col-md-6"> 
				                      <div class="form-group"><label><?php echo L('包括自己'); ?></label>
				                       <select name='contain_self' id="contain_self" class='form-control m-b'>
		                                 	<option value='0'><?php echo L('仅团队的入金'); ?></option>
		                                 	<option value='1'><?php echo L('包括自己的入金'); ?></option>
			                            </select>
				                    </div>
		                       	</div>
				             </div>
			                 <div class="row"> 
		                        <div class="col-md-6"> 
				                      <div class="form-group"><label><?php echo L('交易量'); ?></label>
				                        <input type="text" class="form-control" name="total_volume" id="total_volume" placeholder="<?php echo L('请输入达标交易量'); ?>">
				                    </div>
		                       	</div>
		                       	<div class="col-md-6"> 
				                      <div class="form-group"><label><?php echo L('单位'); ?></label>
				                       <select name='volume_unit' id="volume_unit" class='form-control m-b'>
		                                 	<option value='USD'><?php echo L('美金'); ?></option>
		                                 	<option value='VOLUME'><?php echo L('手'); ?></option>
			                            </select>
				                    </div>
		                       	</div>
			                </div>
			                <div class="row"> 
		                        <div class="col-md-6">
				                	<div class="form-group"><label><?php echo L('推荐客户量'); ?>（<?php echo L('组'); ?>）：</label>
				                        <input type="text" class="form-control" name="total_customer" id="total_customer" placeholder="<?php echo L('请输入达标客户量'); ?>">
				                    </div>
			                    </div>
		                        <div class="col-md-6">
				                	<div class="form-group"><label><?php echo L('推荐客户等级'); ?></label>
				                	 	<select name='customer_level' id="customer_level" class='form-control m-b'>
			                            </select>
				                    </div>
			                    </div>
			                </div>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='saverank' ><?php echo L('确认'); ?></button>
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
		$(document).on("click","#saverank",function(){
    	 //$("#saverank").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveinfo";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status > 0) {
					document.settingform.reset();
					$(".closeout").click();
					setTimeout(function(){
						document.location.reload();
					},1500);
                }else{
                	_this.removeAttr("disabled");
				}
            }, 'json')
        });
        
		$(document).on("click","#addrank",function(){
        //$("#addrank").click(function() {
            document.settingform.reset();
           	$('#myModal').modal('toggle'); 
        });
        
        var json = '<?php echo json_encode($DTArr); ?>';
        $('#rank').change(function(){
        	var rank =  $(this).val();
        	changelevel(rank);
        });
        
        function changelevel(rank){
    		var json_obj = eval('(' + json + ')');
    		$('#customer_level').empty();
    		model_type = $('#model_type').val();
    		for(var o in json_obj){ 
    			if(json_obj[o].rank > rank&&model_type==json_obj[o].model_type){
    				$("#customer_level").append("<option value='"+json_obj[o].rank+"'>"+json_obj[o].rank_name+"</option>"); 
    				console.log(json_obj[o]);
    			}
    		}
    		if(model_type == 'agent')
    			$("#customer_level").append("<option value='0'><?php echo L('直接客户'); ?></option>"); 
    	}
        
		$(document).on("click",".modifyrank",function(){
    	//$(".modifyrank").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=getinfo";
			var ID =  $(this).attr('val');
            $.post(url, "id="+ID, function(data) {
                if (data.status) {
                	$("#id").val(data.data.id);
                	$("#rank_name").val(data.data.rank_name);
                	$("#rank").val(data.data.rank);
                	$("#total_deposit").val(data.data.total_deposit);
                    $("#total_customer").val(data.data.total_customer);
                    $("#total_volume").val(data.data.total_volume);
                    $("#volume_unit").val(data.data.volume_unit);
                    $("#customer_level").val(data.data.customer_level);
                    $("#contain_self").val(data.data.contain_self);
                 	$("#scale").val(data.data.scale);	
                 	$("#model_type").val(data.data.model_type);	
                 	changelevel(data.data.rank);
                   	$('#myModal').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

		$(document).on("click",".closeout,.close",function(){
        //$(".closeout,.close").click(function(){
             $("#myModal").hide();
        });
        
		$(document).on("click",".delrank",function(){
        //$('.delrank').click(function () {
			var _this = $(this);
			layer.confirm('<?php echo L('您确定要删除这条信息吗');?>?', {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
				_this.attr('disabled', 'disabled');
				$.post(_this.attr('url'), function (data) {
					if (data.status == 1) {
						layer.msg('<?php echo L('删除成功');?>');
						setTimeout(function () {
							document.location.reload();
						}, 700);
					} else if (data.status == 0) {
						layer.msg('<?php echo L('删除失败');?>');
					}
					
					_this.attr('disabled', false);
					layer.close(index);
				}, 'json');
			}, function(index){
				layer.close(index);
			});
		});
    </script>
        
        
        
        
        
        

    </body>
</html>
