 <?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$DRInfo = $DB->getDRow("select a.*,b.Name,b.Scale,b.Condition,b.Result SetResult,b.Type from (select * from t_credit_record where Id = '{$Id}') a left join t_credit_setting b on a.CreditId=b.Id");

if ($DRAdmin['_dataRange'] >= 2) {
	$parent_id = 'admin';
} else {
	$parent_id = $DRAdmin['id'];
	
	$idarr = getunderCustomerIds($parent_id);
	if (!in_array($DRInfo['MemberId'], $idarr) && $DRAdmin['id'] != $DRInfo['MemberId']) {
		FJS_AB(L("数据查询失败"));
	}
	_check_member_scope($parent_id, $DRInfo['MemberId']);
}
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('转余额') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
										<div>
											<?php
											$rs = $DB->getDRow("select * from t_credit_setting where id = " . $DRInfo['CreditId']);
											if($rs['f_zye_days'] > 0 && strlen($rs['f_zye_symbol']) > 0 && $rs['f_zye_lot'] > 0){
												echo L('赠金产生后') , $rs['f_zye_days'] , L('天内');
												echo ' &nbsp; &nbsp; ';
												echo L('产品') , '：' , trim($rs['f_zye_symbol'],',');
												echo ' &nbsp; &nbsp; ';
												echo $rs['f_zye_lot'] * 1 , L('手交易');
												echo ' &nbsp; &nbsp; ';
												echo L('持仓至少') , $rs['f_zye_keepTimeSecond'] , L('秒');
											}else{
												echo '-';
											}
											?>
										</div>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('TICKET');?></th>
                                                    <th class="no-sort"><?php echo L('产品');?></th>
													<th class="no-sort"><?php echo L('计算');?></th>
                                                    <th class="no-sort"><?php echo L('手数');?></th>
                                                    <th class="no-sort"><?php echo L('OPEN_TIME');?></th>
                                                    <th class="no-sort"><?php echo L('CLOSE_TIME');?></th>
                                                    <th class="no-sort"><?php echo L('持仓秒数');?></th>
                                                    <th class="no-sort"><?php echo L('计入下一个赠金活动');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$zye_lot_info = unserialize($DRInfo['f_zye_lot_info']);
	if($zye_lot_info){
		if($zye_lot_info['tickets']){
			$tickets = array_keys($zye_lot_info['tickets']);
			$tickets = implode(',',$tickets);
		}else{
			$tickets = '0';
		}		
		
		$sum = 0;
		$sum1 = 0;
		$query = $DB->query("select a.*,b.f_again from (select * from {$zye_lot_info['db_name']}.mt4_trades where TICKET in (" . $tickets . ")) a left join t_credit_record_tickets b on a.TICKET = b.f_ticket and b.f_recordId = '{$Id}' order by a.CLOSE_TIME asc");
		while($rs = $DB->fetchArray($query)){
			$bs = $zye_lot_info['tickets'][$rs['TICKET']];
			
			echo '<tr>';
			echo '<td>' , $rs['TICKET'] , '</td>';
			echo '<td>' , $rs['SYMBOL'] , '</td>';
			echo '<td>' , $rs['VOLUME'] , ' / ' , $bs , '</td>';
			echo '<td>' , $sum1 = $rs['VOLUME'] / $bs , '</td>';
			echo '<td>' , $rs['OPEN_TIME'] , '</td>';
			echo '<td>' , $rs['CLOSE_TIME'] , '</td>';
			echo '<td>' , strtotime($rs['CLOSE_TIME']) - strtotime($rs['OPEN_TIME']) , '</td>';
			echo '<td>' , $rs['f_again'] ? L('是') : L('否') , '</td>';
			echo '</tr>';

			$sum += $sum1;
		}

		echo '<tr>';
		echo '<td>&nbsp;</td>';
		echo '<td>&nbsp;</td>';
		echo '<td>&nbsp;</td>';
		echo '<td>' , $sum , '</td>';
		echo '<td>&nbsp;</td>';
		echo '<td>&nbsp;</td>';
		echo '<td>&nbsp;</td>';
		echo '<td>&nbsp;</td>';
		echo '</tr>';
	}
?>
                                            </tbody>
                                        </table>
<?php
if($DRInfo['f_zye_endTime'] > 0 && $DRInfo['f_zye_endBackTime'] <= 0 && $DRInfo['f_zye_endTimeChkEd'] > 0 && $DRInfo['f_zye_endTimeChkState'] > 0){
	echo '<a href="#nolink" onclick="sh_click()" class="btn btn-success btn-sm">' , L('审核') , '</a> ';
	echo '<a href="?clause=unzye&id=' , $Id , '&prevUrl=' , FPrevUrl() , '" onclick="return confirm(\'' , L('确定驳回吗') , '？\')" class="btn btn-primary btn-sm">' , L('驳回') , '</a> ';
}
echo '<button onclick="window.history.back();" type="button" class="btn btn-light btn-sm">返回</button >';
?>


                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->
                    
  
  


        
            <!--修改密码弹出层-->
	    <div class="modal inmodal" id="myModal_sh" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('审核');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
		                <div class="modal-body">
                           <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('审核通过'); ?></span>
                           <br>
	                       <label><?php echo L('交易数据'); ?>：</label>
                              <div class="input-group m-b">
                                <div class="radio radio-info radio-inline">
                                    <input type="radio" id="jysj1" value="1" name="jysj">
                                    <label for="jysj1"><?php echo L('计入下一个赠金活动里'); ?></label>
                                </div>
                                <div class="radio radio-inline">
                                    &nbsp; &nbsp; &nbsp; 
                                    <input type="radio" id="jysj2" value="0" name="jysj" checked="checked">
                                    <label for="jysj2"> <?php echo L('不计入'); ?> </label>
                                </div>
                              </div>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" onclick="save_sh()"><?php echo L('确认'); ?></button>
		                </div>
	            </div>
	        </div>
	    </div>
        
        


    <!-- modal 导入中-->
    <div class="modal fade" id="myModal_loading">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title">Loading</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center"><img src="/assets/js/ThinkBox/img/tips_loading.gif"></div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->






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
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script> 
        

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
	var FPrevUrl = '<?php echo FPrevUrl();?>';
	var infoId = <?php echo $Id;?>;
	   function sh_click(){
			$('#myModal_sh').modal('toggle');
		}
		function save_sh(){
			var jysj = $('#jysj1').is(':checked');
			if(jysj){
				jysj = 1;
			}else{
				jysj = 0;
			}
			
			var url = '?clause=savezye&id=' + infoId + '&jysj=' + jysj + '&prevUrl=' + FPrevUrl;
			window.location.href = url;
			
			$('#myModal_sh').modal('toggle');
			$('#myModal_loading').modal('toggle');
		}
</script>
        
        
        
        

    </body>
</html>
