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
                                    <h4 class="page-title"><?php echo L('分组同步') , getCurrMt4ServerName();?></h4>
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
                                                <a href="#nolink" id="sync" class="btn btn-danger mb-2"><?php echo L('同步') , getCurrMt4ServerName() , L('分组');?></a>
                                            </div>
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('类型');?></th>
                                                    <th class="no-sort"><?php echo L('分组名称');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
$DTInfo = $DB->getDTable("select groups.*,svr.mt4_name from t_groups groups,t_mt4_server svr where groups.server_id = svr.id and svr.id='" . $DRAdmin['server_id'] . "' and svr.status=1 order by svr.id desc");
$defaultgroup = $DB->getDRow("select * from t_groups groups,t_mt4_server svr where groups.server_id = svr.id and svr.id='" . $DRAdmin['server_id'] . "' and svr.status=1 and groups.status=0");
?>
    <tr>
        <td><B>A Book</B></td>
        <td>
        <?php
		$cii = 0;
        foreach($DTInfo as $key=>$val){
			if($val['type'] == 'A'){
				if($cii > 0){
					if($DRAdmin['ver'] == 5){
						echo '<br>';
					}else{
						echo ',';
					}
				}
				
				echo $val['group'];
				
				$cii++;
			}
		}
		?>
       </td>
       <td class="center">
        <a class="btn btn-primary btn-xs modifytype" type="button" rel="A"  data-toggle="modal"  href="#" ><?php echo L('修改');?></a>
       </td>
    </tr>   
    <tr>
        <td><B>B Book</B></td>
        <td  width="75%" style="word-wrap:break-word;word-break:break-all;">
        <?php
		$cii = 0;
        foreach($DTInfo as $key=>$val){
			if($val['type'] == 'B'){
				if($cii > 0){
					if($DRAdmin['ver'] == 5){
						echo '<br>';
					}else{
						echo ',';
					}
				}
				
				echo $val['group'];
				
				$cii++;
			}
		}
		?>
       </td>
       <td class="center">
        <!-- <span class="btn  btn-xs" type="button" rel="A"    href="#" ><a href="{:U('Commission/transfer_list')}"><?php echo L('MT默认类型'); ?>(<?php echo L('点击添加修改组备注'); ?>)</a></span> -->
            <a class="btn btn-primary btn-xs grname" type="button" rel="{$vo.group}"  data-toggle="modal"  href="#" ><?php echo L('MT默认类型');?>(<?php echo L('点击添加修改组备注');?>)</a>
       </td>
    </tr> 
    <tr>
        <td><B><?php echo L('默认开户组');?></B></td>
        <td>
        <?php
		$dfLangOtherSet = get_lang_otherset_arr('默认开户MT组');
		foreach($LangNameList['list'] as $keyL=>$valL){
			if($dfLangOtherSet[$keyL]){
				echo '<b>（' , $valL['title'] , '）</b> ' , $dfLangOtherSet[$keyL]['f_val'];
			}else{
				echo '<b>（' , $valL['title'] , '）</b> ' , '<span style="color:#ff0000">' , L('暂无设置') , '</span>';
			}
			echo '<br>';
		}
		?>
       </td>
       <td class="center">
        <a class="btn btn-primary btn-xs modifydefaultgroup" type="button" rel="<?php echo $defaultgroup['group'];?>"  data-toggle="modal"  href="#" ><?php echo L('修改');?></a>
       </td>
    </tr>   

                                            </tbody>
                                        </table>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->











    <!--弹出层-->
	    <div class="modal inmodal" id="groupModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('MT组分类'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingform' name='settingform'>
		            	<input type="hidden" name="type" ID="type" value="" />
		                <div class="modal-body">
		                  	 <div class="row"> 
		                  	 <div class="col-md-12">    
		                	 	<div class="form-group">
			                	 	 <label><?php echo L('分组种类'); ?>：</label>
				                	 <p class="form-control-static" id="typetext">B BOOK</p>
			                	</div>
	                    	 </div>
		                    <div class="col-md-12"> 
		                        <div class="form-group"><label><?php echo L('MT组'); ?>：</label> 
		                            <select name='groups[]' id="groups" data-placeholder="<?php echo L('请选择MT组'); ?>" class='chosen-select'  multiple  >
	                                 	<?php
                                        foreach($DTInfo as $key=>$val){
											echo '<option value="' , $val['group'] , '">' , $val['group'] , '</option>';
										}
										?>
		                            </select>
		                        </div>
		                        <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('A Book权限将由主标统管'); ?></span>
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
  
  	<!--弹出层-->
	    <div class="modal inmodal" id="group_remark" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('添加修改组备注'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                <form id='groupform' name='groupform'>
		                <div class="modal-body">
		                  	<div class="row"> 
                            	<?php
								foreach($DTInfo as $key=>$val){
									echo '<div class="col-md-12">
											<div class="form-group">
												<label>' , $val['group'] , '：</label>
												<input name="remark_' , $val['id'] , '" type="text" value="' , $val['group_remark'] , '" class="form-control forwordnumber" placeholder="' , L('请输入备注') , '">
											</div>
										</div>';
								}
								?>
	                        </div>
	                    </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='savetGroupRemark' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
  <!--弹出层-->
  
   <!--弹出层-->
	    <div class="modal inmodal" id="openaccountModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('默认开户组'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='defaultform' name='defaultform'>
		                <div class="modal-body">
	                        <div class="row"> 
                            <?php
                            foreach($LangNameList['list'] as $keyL=>$valL){
							?>
		                    <div class="col-md-12"> 
		                        <div class="form-group"><label><?php echo $valL['title']; ?>：</label> 
		                            <select name='defaultgroup-<?php echo $keyL; ?>' id="defaultgroup-<?php echo $keyL; ?>" data-placeholder="<?php echo L('请选择默认开户MT组'); ?>" class='form-control m-b' >
	                                 	<?php
                                        foreach($DTInfo as $key=>$val){
											echo '<option value="' , $val['group'] , '"';
											if($dfLangOtherSet[$keyL]){
												if($dfLangOtherSet[$keyL]['f_val'] == $val['group']){
													echo ' selected="selected"';
												}
											}
											echo '>' , $val['group'] , '</option>';
										}
										?>
		                            </select>
		                        </div>
	                        </div>
                            <?php
							}
							?>
	                        </div>
	                	</div>
		                <div class="modal-footer">
		                    <input type='hidden' name='inlogin' value=''/>
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='saveDefaultGroup' ><?php echo L('确认'); ?></button>
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
	$(document).on("click","#sync",function(){
	//$("#sync").click(function() {
            $(this).attr('disabled', "disabled");
            $(this).text("<?php echo L('正在同步中'); ?>...");
          	var _this=$(this);
            var url = "?clause=syncGroups";

            $.post(url, "", function(data) {
                alert(data.info);
                _this.removeAttr("disabled");
                _this.text("<?php echo L('同步完成'); ?>");
                document.location.reload();
            }, 'json')
        });
	
	$(".chosen-select").chosen( {width: "100%"});
	
	$(document).on("click",".modifytype",function(){
	//$(".modifytype").click(function() {
        $(this).attr('disabled', "disabled");
      	var _this=$(this);
        var form = $(this).closest('form');
        var url = "?clause=viewType";
		var type =  $(this).attr('rel');
        $.post(url, "type="+type, function(data) {
            if (data.status) {
            	if(type=='A')
            		$("#grouptitle").text("A BOOK <?php echo L('分组编辑'); ?>");
            	else
               		$("#grouptitle").text("B BOOK <?php echo L('分组编辑'); ?>");
            	$("#type").val(type);
            	$("#typetext").text(type+" Book");
            	if ($(".chosen-select").hasClass('chzn-done'))
                    $(".chosen-select").chosen('destroy');
                $(".chosen-select").chosen( {width: "100%"});
                chose_mult_set_ini('#groups',data.data.groups);
               	$('#groupModal').modal('toggle');
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
	 
	 
		$(document).on("click","#savetype",function(){
	    //$("#savetype").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveType";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status == 1) {
                    document.settingform.reset();
                    $(".closeout").click();
					
					setTimeout(function(){
						document.location.reload();
					},1000);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
	    
	    $(document).on("click",".modifydefaultgroup",function(){
	    //$(".modifydefaultgroup").click(function() {
	        $(this).attr('disabled', "disabled");
	      	var _this=$(this);
	      	var group =  $(this).attr('rel');
	        var form = $(this).closest('form');
           	$("#defaultgroup").val(group);
            $('#openaccountModal').modal('toggle');
	    });
	    
		$(document).on("click",".grname",function(){
	    //$(".grname").click(function() {
	        $(this).attr('disabled', "disabled");
            $('#group_remark').modal('toggle');
	    });

		$(document).on("click","#savetGroupRemark",function(){
	    //$("#savetGroupRemark").click(function(){
            var form = $(this).closest('form');
            var url = "?clause=upremark";
            $.post(url, form.serialize(), function (data) {
            	if(data.status == 1){
            		alert("<?php echo L('修改成功'); ?>！");
                    document.location.reload();
            	}
            },'json')
	    });

		$(document).on("click","#saveDefaultGroup",function(){
	    //$("#saveDefaultGroup").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveDefaultGroup";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                   // document.defaultform.reset();
                    $(".closeout").click();
					
					setTimeout(function(){
						document.location.reload();
					},1000);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
    </script>
        
        
        
        
        
        

    </body>
</html>
