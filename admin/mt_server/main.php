<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$truenum = intval($DB->getField("select count(*) as count1 from `t_mt4_server` where `status` = '1' and `real` = '1'"));
$simunum = intval($DB->getField("select count(*) as count1 from `t_mt4_server` where `status` = '1' and `real` = '0'"));
$defaulnum = intval($DB->getField("select count(*) as count1 from `t_mt4_server` where `status` = '1' and `real` = '1' and `default_open_svr` = 1"));

$truemt4nums = C('OPEN_TRUE_NUMBER');
$simumt4nums = C('OPEN_SIMU_NUMBER');
if(!$truemt4nums) $truemt4nums = 1;
if(!$simumt4nums) $simumt4nums = 1;
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
                                    <h4 class="page-title"><?php echo L('MT服务器设置') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row mb-2">
                                            <div class="col-sm-12">
                                            	<?php
													if($truenum < $truemt4nums){
													?>
													<a href="#nolink" data-toggle="modal" ref="1" data-target="#myModal" class="btn btn-danger mb-2 addmtserver"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加MT服务器') , '(' , L('真实仓') , ')';?></a>
													<?php
													}
													if($simunum < $simumt4nums){
													?>
													<a href="#nolink" data-toggle="modal" ref="0" data-target="#myModal" class="btn btn-danger mb-2 addmtserver"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加MT服务器') , '(' , L('模拟仓') , ')';?></a>
													<?php
													}
												?>
                                            </div>
                                        </div>

                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('服务器名称');?></th>
                                                    <th class="no-sort"><?php echo L('服务器地址');?></th>
                                                    <th class="no-sort">Manager <?php echo L('账号');?></th>
                                                    <th class="no-sort"><?php echo L('服务器时区');?></th>
                                                    <th class="no-sort"><?php echo L('开户号段');?></th>
                                                    <th class="no-sort"><?php echo L('设置时间');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where `status` = 1";
	if($DRAdmin['manage_server'] && $DRAdmin['query_server']){
		$_ids = $DRAdmin['manage_server'] . ',' . $DRAdmin['query_server'];
	}else{
		$_ids = $DRAdmin['manage_server'] . '' . $DRAdmin['query_server'];
	}
	if($_ids){
		$where .= " and id in ({$_ids})";
	}
	
	$recordCount = intval($DB->getField("select count(*) from `t_mt4_server` {$where}"));
	
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
	$query = $DB->query("select * from `t_mt4_server` {$where} order by id asc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>';
			if($rs['ver'] == 5){
				echo '<span class="badge badge-info">MT5</span> ';
			}else{
				echo '<span class="badge badge-success">MT4</span> ';
			}
			echo $rs['mt4_name'];
			if($rs['real'] == 0){
				echo '(' , L("模拟仓") , ') ';
			}else{
				echo '(' , L("真实仓") , ') ';
			}
			if($rs['default_open_svr'] == 1){
				echo '<span class="badge badge-success">' , L("注册默认") , '</span>';
			}
			echo '</td>';
			echo '<td>' , $rs['mt4_server'] , '</td>';
			echo '<td>' , $rs['mt4_manager'] , '</td>';
			echo '<td>GMT ' , $rs['time_zone'] , ':00</td>';
			echo '<td>' , $rs['start_number'] , '-' , $rs['end_number'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['update_time']) , '</td>';
			echo '<td>';
			//print_r($AccessList);
			echo '<a class="btn btn-primary btn-xs modifymt4" val="' , $rs['id'] , '" type="button" data-toggle="modal" href="#onlink">' , L('修改') , '</a> ';
			echo '<a class="btn btn-danger btn-red-cz btn-xs delmt4" type="button" href="#nolink" url="?clause=delinfo&id=' , $rs['id'] , '">' , L('删除') , '</a> ';
			//echo '<a href="?clause=openmt&id=' , $rs['id'] , '" class="btn btn-primary btn-xs">' , L("会员类型开户号段设置") , '</a> ';
			echo '<a class="btn btn-light btn-xs openaccount" type="button" rel="' , $rs['id'] , '" href="#nolink">' , $rs['real'] > 0 ? L("真实仓注册地址") : L("模拟仓注册地址") , '</a> ';
			echo '<a class="btn btn-light btn-xs openagent" type="button" rel="' , $rs['id'] , '" href="#nolink">' , L("代理注册地址") , '</a>';
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






	    <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('MT服务器设置'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingform' name='settingform'>
		            	<input type="hidden" name="id" ID="id" value="" />
		                <div class="modal-body">
			                <div class="row"> 
	                       	 	<div class="col-md-6"> 
	                                <div class="form-group"> 
	                                 <label><?php echo L("服务器名称"); ?></label> 
									 <input type="text"  style="ime-mode:disabled" name='mt4_name' id="mt4_name" placeholder="<?php echo L('请输入mt服务器名称'); ?>" class="form-control"> 
	                       			</div>
	                       		</div>
	                       	 	<div class="col-md-6"> 
				                    <div class="form-group"><label><?php echo L("类型"); ?>：</label>
				                       <select class="form-control m-b" id="real" name="real" >
				                 			<option value="0"><?php echo L("模拟仓"); ?></option>
				                 			<option value="1"><?php echo L("真实仓"); ?></option>
				                 		</select>
				                    </div>
			                 	</div>
	                       	</div>
                       	 	<div class="row"> 
	                       		<div class="col-md-12"> 
		                       		<div class="form-group"><label>MT<?php echo L("服务器地址"); ?>：</label> 
				                 		<input type="text"  style="ime-mode:disabled"   name='mt4_server' id="mt4_server" placeholder="<?php echo L('请输入MT服务器地址,格式'); ?>：122.9.21.2:443" class="form-control"> 
				                    </div>
	                       		</div>
	                       	</div>
	                       	
	                       	 <div class="row"> 
	                       	 	<div class="col-md-6"> 
	                                <div class="form-group"> 
	                                	<div class="form-group"><label>Manager<?php echo L('账号'); ?>：</label>
				                        	<input type="text" class="form-control" name="mt4_manager" id="mt4_manager" placeholder="<?php echo L('MT中Manager账号'); ?>"> 
				                    	 	<input style="display:none">
			                     		</div>
	                       			</div>
	                       		</div>
	                       		<div class="col-md-6"> 
	                                <div class="form-group"> 
	                                	<div class="form-group"><label>Manager<?php echo L('密码'); ?>：</label>
					                        <input type="password" class="form-control" name="mt4_password" id="mt4_password" placeholder="<?php echo L('Manager密码'); ?>">
					                    </div>
	                       			</div>
	                       		</div>
	                       	</div>
			                    
		                   
		                    <div class="row"> 
	                     	 	<div class="col-md-6">
			                     	<div class="form-group"><label><?php echo L('号码段最小值'); ?>：</label>
				                        <input type="number" class="form-control" name="start_number" id="start_number" placeholder="<?php echo L('开户号段最小值'); ?>"> 
			                     	</div>
		                     	</div>
		                    	<div class="col-md-6"> 
			                     	<div class="form-group"><label><?php echo L('号码段最大值'); ?>：</label>
				                        <input type="number" class="form-control" name="end_number" id="end_number" placeholder="<?php echo L('开户号段最大值'); ?>"> 
			                     	</div>
				                </div>
			                </div>
			                
			                <div class="row"> 
	                       		<div class="col-md-6"> 
		                       		<div class="form-group"><label><?php echo L('服务器类型'); ?>：</label> 
		                       			<select class="form-control m-b" id="ver" name="ver" >
				                 			<option value="4">MT4</option>
				                 			<option value="5">MT5</option>
				                 		</select>
				                    </div>
	                       		</div>
                                   <div class="col-md-6"> 
		                       		<div class="form-group"><label><?php echo L('默认开户服务器'); ?>：</label> 
		                       			<select class="form-control m-b" id="default_open_svr" name="default_open_svr" >
				                 			<option value="1"><?php echo L('是'); ?></option>
				                 			<option value="0"><?php echo L('否'); ?></option>
				                 		</select>
				                    </div>
	                       		</div>       
                                      
	                       	</div>
	                       	
	                       	<div class="row"> 
	                       		<div class="col-md-6"> 
									<div class="form-group"><label><?php echo L('MT订单库'); ?>：</label>
				                        <input type="text" class="form-control" name="db_name" id="db_name" placeholder="<?php echo L('MT记录同步库'); ?>"> 
			                     	</div>
			                    </div>
			                    
		                    	<div class="col-md-6">    
				                    <div class="form-group"><label><?php echo L('服务器时区'); ?>：</label>
				                    <select class="form-control m-b" id="time_zone" name="time_zone" >
				                       		<option value="-12">GMT-12:00</option>
				                 			<option value="-11">GMT-11:00</option>
				                 			<option value="-10">GMT-10:00</option>
				                 			<option value="-9">GMT-09:00</option>
				                 			<option value="-8">GMT-08:00</option>
				                 			<option value="-7">GMT-07:00</option>
				                 			<option value="-6">GMT-06:00</option>
				                 			<option value="-5">GMT-05:00</option>
				                 			<option value="-4">GMT-04:00</option>
				                 			<option value="-3">GMT-03:00</option>
				                 			<option value="-2">GMT-02:00</option>
				                 			<option value="-1">GMT-01:00</option>
				                 			<option value="0">GMT+00:00</option>
				                 			<option value="1">GMT+01:00</option>
				                 			<option value="2">GMT+02:00</option>
				                 			<option value="3">GMT+03:00</option>
				                 			<option value="4">GMT+04:00</option>
				                 			<option value="5">GMT+05:00</option>
				                 			<option value="6">GMT+06:00</option>
				                 			<option value="7">GMT+07:00</option>
				                 			<option value="8">GMT+08:00</option>
				                 			<option value="9">GMT+09:00</option>
				                 			<option value="10">GMT+10:00</option>
				                 			<option value="11">GMT+11:00</option>
				                 			<option value="12">GMT+12:00</option>
				                 		</select>
				                    </div>
			                   	</div>
		                    </div>
		               </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='savemt4' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
	    <!--弹出层-->
	    
    
	      <!--弹出层-->
	    <div class="modal inmodal" id="openModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('开户地址'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
		                <div class="modal-body">
		             <?php echo L('开户地址'); ?>：<span id="address"></span>
		             <br/>
		              <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('请复制该链接到网站用于注册开户使用'); ?>.</span>
		                </div>
		                
		                <div class="modal-footer">
		               
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('取消'); ?></button>
		                    <button type="button" class="btn btn-primary" id='open_url' ><?php echo L('打开预览'); ?></button>
		                </div>
	            </div>
	        </div>
	    </div>









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
		$(document).on("click","#savemt4",function(){
    	 //$("#savemt4").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveinfo";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.settingform.reset();
                    $(".closeout").click();
					
                    setTimeout(function(){
						document.location.reload();
					},1500);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
    	 
		 $(document).on("click",".openaccount",function(){
    	 //$(".openaccount").click(function() {
             $(this).attr('disabled', "disabled");
           	 var _this=$(this);
  			 var id =  $(this).attr('rel');
  			 $("#address").text("<?php 
			 if(FIsHttps()){
				 echo 'https://';
			 }else{
				 echo 'http://';
			 }
			 echo $_SERVER['HTTP_HOST'];
			 echo CC_ADMIN_ROOT_FOLDER;
			 echo 'reg.php?mt=';
			 ?>" + id);
             $('#openModal').modal('toggle');
             _this.removeAttr("disabled");
          });
		  
		  $(document).on("click",".openagent",function(){
          //$(".openagent").click(function() {
             $(this).attr('disabled', "disabled");
             var _this=$(this);
			 var id =  $(this).attr('rel');
			 $("#address").text("<?php 
			 if(FIsHttps()){
				 echo 'https://';
			 }else{
				 echo 'http://';
			 }
			 echo $_SERVER['HTTP_HOST'];
			 echo CC_ADMIN_ROOT_FOLDER;
			 echo 'reg.php?usertype=agent&mt=';
			 ?>" + id);
             $('#openModal').modal('toggle');
             _this.removeAttr("disabled");
          });
		  
		  $(document).on("click","#open_url",function(){
    	 //$("#open_url").click(function() {
  			window.open( $("#address").text());
          });
    	 
		 $(document).on("click",".addmtserver",function(){
		 //$('.addmtserver').click(function(){
			 $("#time_zone").removeAttr("disabled");
			 $("#db_name").removeAttr("disabled");
			 document.settingform.reset();
		 });
    	 
		 $(document).on("click",".addmt4manage",function(){
    	 //$(".addmt4manage").click(function() {
           // $(this).attr('disabled', "disabled");
           	var _this=$(this);
 			var real =  $(this).attr('ref');
            $("#real").val(real);
            $("#db_name").removeAttr('disabled');
            if(<?php echo $defaulnum;?> > 1){
            	$("#default_open_svr").val(0);
            }
            $('#myModal').modal('toggle');
         });
        
		$(document).on("click",".modifymt4",function(){
    	//$(".modifymt4").click(function() {
          //  $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=getinfo";
			var ID =  $(this).attr('val');
            $.post(url, "id="+ID, function(data) {
                if (data.status) {
                	$("#id").val(data.data.id);
                	$("#mt4_name").val(data.data.mt4_name);
                	$("#mt4_manager").val(data.data.mt4_manager);
                	$("#mt4_server").val(data.data.mt4_server);
                    //$("#mt4_password").val(data.data.mt4_password);
                    $("#real").val(data.data.real);
                    if(data.data.time_zone!=""){
                    	$("#time_zone").val(data.data.time_zone);
                    	//$("#time_zone").attr("disabled","disabled")
                    }else{
                    	$("#time_zone").removeAttr("disabled");
                    	$("#time_zone").val("");
                    } 
                    $("#db_name").val(data.data.db_name);
                    if(data.data.db_name!=""){
                    	$("#db_name").attr('disabled','disabled');
					}else{
						$("#db_name").removeAttr("disabled");
					}
                    $("#start_number").val(data.data.start_number);
                	$("#end_number").val(data.data.end_number);	
                	if(data.data.ver=="")
                		data.data.ver = 4;
                	$("#ver").val(data.data.ver);	
                	$("#default_open_svr").val(data.data.default_open_svr);	
                        $("#default_inmoney").val(data.data.default_inmoney);	
                        if(data.data.real=='0'){
                            $("#default_inmoney").closest("div").show();
                        }else{
                            $("#default_inmoney").closest("div").hide();
                        }
                   	$('#myModal').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

		$(document).on("click",".closeout,.close",function(){
        //$(".closeout,.close").click(function(){
             $("#myModal").hide();
        });
        

        /*$('.syncdata').click(function () {
        	var ID =  $(this).attr('rel');
		    swal({
		        title: "您确定要同步MT信息吗",
		        showCancelButton: true,
		        confirmButtonColor: "#DD6B55",
		        confirmButtonText: "将同步最新的信息到CRM中",
		        closeOnConfirm: false,   
		        showLoaderOnConfirm: true,
		    }, function () {
		    	var url = "{:U('Mt4Server/restartSyncSvr')}";
		    	$.post(url, "id="+ID, function(data) {
		    		swal("同步信息！", data.info, "success");
		    	}, 'json');
        	});
        });*/
		
        $(document).on("click",".delmt4",function(){
        //$('.delmt4').click(function () {
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
