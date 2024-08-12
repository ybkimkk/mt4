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
                                    <h4 class="page-title"><?php echo L('交易种类同步') , getCurrMt4ServerName();?></h4>
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
                                                <a href="#nolink" id="sync" class="btn btn-danger mb-2"><?php echo L('同步') , getCurrMt4ServerName() , L('交易品种');?></a>
                                                <a href="#nolink" id="add" class="btn btn-danger mb-2"><?php echo L('添加自定义交易种类');?></a>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="help-block m-b-none"><i class="fa fa-info-circle"></i><?php echo L('同一交易品种，将按照自定义交易种类的返佣标准进行'); ?></span>
                                            <span class="help-block m-b-none"></span>
                                        </div>
										<div>
                                            <span class="help-block m-b-none">
												<i class="fa fa-info-circle"></i>1手倍数颜色表示：
												<span style="color:#0000ff">100</span>、
												<span style="color:#ff0000">10000</span>、
												<span style="color:#ff00ff">其它</span>
											</span>
                                            <span class="help-block m-b-none"></span>
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('编号');?></th>
                                                    <th class="no-sort"><?php echo L('交易种类');?></th>
                                                    <th class="no-sort"><?php echo L('交易品种');?></th>
                                                    <th class="no-sort"><?php echo L('更新时间');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
$list = $DB->getDTable("select symbol.*, type.type_name,svr.mt4_name from t_symbol symbol, t_type type,t_mt4_server svr where symbol.type = type.id and type.server_id = svr.id and svr.id='" . $DRAdmin['server_id'] . "' and svr.status=1 and type.status=1 order by svr.id,type.id desc");

$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id='" . $DRAdmin['server_id'] . "' and a.status=1 order by a.type desc,a.type_name asc");

$symbols = $DB->getDTable("select distinct symbol.symbol from t_symbol symbol, t_type type,t_mt4_server svr where symbol.type = type.id and type.server_id = svr.id and svr.id='" . $DRAdmin['server_id'] . "' and  svr.status=1 and type.status=1 order by svr.id,type.id desc");

foreach($types as $key=>$rs){
?>
<tr id="tr_<?php echo $rs['id'];?>">
    <td><?php echo $rs['id'];?></td>
    <td>
    <?php
    if($rs['type'] == 1){
		echo '<span class="badge badge-primary">' , L('自定义') , '</span> ';
	}else{
		echo '<span class="badge badge-info">MT</span> ';
	}
	echo $rs['type_name'];
	//echo '(' , $rs['mt4_name'] , ')';
	?>
    </td>
    <td width="50%" style="word-wrap:break-word;word-break:break-all;max-width:50%;">
    <?php
	$cii = 0;
    foreach($list as $key=>$val){
		if($val['type'] == $rs['id'] && $val['server_id'] == $rs['server_id']){
			if($cii > 0){
				echo ',';
			}
			
			echo '<span style="color:';
			switch($val['f_lotB']){
				case 100:
					echo '#0000ff';
					break;
				case 10000:
					echo '#ff0000';
					break;
				default:
					echo '#ff00ff';
					break;
			}
			echo '">';
			echo $val['symbol'];
			echo '</span>';
			
			$cii++;
		}
	}
	?>
    </td>
    <td><?php echo date('Y-m-d H:i:s',$rs['update_time']);?></td>
    <td>
    <?php
    if($rs['type'] == '1'){
		echo '<a class="btn btn-primary btn-xs modifytype" type="button" rel="' , $rs['id'] , '" data-toggle="modal" href="#nolink" >' , L('修改') , '</a> ';
		echo '<a class="btn btn-danger btn-red-cz btn-xs deltype" type="button" url="?clause=delinfo&id=' , $rs['id'] , '" data-toggle="modal"  href="#nolink" >' , L('删除') , '</a> ';
	}else{
		echo '<span class="badge badge-info">' , L('MT交易种类') , '</span> ';
		//echo '<a class="btn btn-primary btn-xs stusybol" type="button" href="?clause=editsysinfo&id=' , $rs['id'] , '">' , L('修改标准货币对') , '</a>';
		echo '<a class="btn btn-primary btn-xs stusybol" type="button" href="?clause=editlotb&id=' , $rs['id'] , '">' , L('设置1手倍数') , '</a>';
	}
	?>
    </td>
</tr> 
<?php
}
?>

                                            </tbody>
                                        </table>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->






     <!--弹出层-->
	    <div class="modal inmodal" id="symbolModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('自定义交易种类'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingform' name='settingform'>
		            	<input type="hidden" name="id" ID="id" value="" />
		                <div class="modal-body">
		                  	<div class="row"> 
		                  	<div class="col-md-12">    
			                	<label><?php echo L('交易种类'); ?>：</label>
			                	<div class="input-group m-b">
			                        <input type="text" class="form-control" name="type_name" id="type_name" placeholder="<?php echo L('请输入自定义交易种类名称'); ?>">
			                    </div>
	                    	 </div>
		                    <div class="col-md-12"> 
		                        <div class="form-group"><label><?php echo L('交易品种'); ?>：</label> 
		                            <select name='symbols[]' id="symbols" data-placeholder="<?php echo L('请选择交易品种'); ?>" class='chosen-select'  multiple  >
	                                 	<?php
                                        foreach($symbols as $key=>$val){
											echo '<option value="' , $val['symbol'] , '">' , $val['symbol'] , '</option>';
										}
										?>
		                            </select>
		                        </div>
		                        <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('该交易品种集合的交易订单，将按照自定义种类的返佣进行'); ?></span>
	                        </div>
                        </div>
	                	</div>
		                <div class="modal-footer">
		                    <input type='hidden' name='inlogin' value=''/>
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='savetype' ><?php echo L('确认'); ?></button>
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
        
        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>  

        
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
	$(".chosen-select").chosen( {width: "100%"});
	
	$(document).on("click","#sync",function(){
	//$("#sync").click(function() {
            $(this).attr('disabled', "disabled");
            $(this).text("<?php echo L('正在同步中');?>...");
          	var _this=$(this);
            var url = "?clause=syncSymbol";

            $.post(url, "", function(data) {
                layer.msg(data.info);
                //_this.removeAttr("disabled");
                _this.text("<?php echo L('同步完成');?>");
                setTimeout(function() {
                	 document.location.reload();
                  }, 1000);
               
            }, 'json')
        });
	
	
		$(document).on("click",".modifytype",function(){
    	//$(".modifytype").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=viewSymbolType";
			var ID =  $(this).attr('rel');
            $.post(url, "id="+ID, function(data) {
                if (data.status) {
                	$("#id").val(data.data.id);
                	$("#type_name").val(data.data.type_name);
                	if ($(".chosen-select").hasClass('chzn-done'))
                        $(".chosen-select").chosen('destroy');
                    $(".chosen-select").chosen( {width: "100%"});
                    chose_mult_set_ini('#symbols',data.data.symbols);
                   	$('#symbolModal').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
    	
    	 function chose_mult_set_ini(select, values) {
             var arr = values.split(',');
             var value = '';
             $(select).val(arr);
             $(select).trigger("chosen:updated");
             //$(select).trigger("chosen:updated");  
         }
    	
    	$(document).on("click","#add",function(){
    	//$("#add").click(function() {
           	$('#symbolModal').modal('toggle'); 
        });
        
		$(document).on("click","#savetype",function(){
         //$("#savetype").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveSymbolType";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.settingform.reset();
                    $(".closeout").click();
					
					setTimeout(function(){
						document.location.reload();
					},1000);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
         
		 $(document).on("click",".deltype",function(){
         //$(".deltype").click(function() {
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
         
         $('#qtype').val('{$Think.request.qtype}');
    </script>
        
        
        
        
        
        

    </body>
</html>
