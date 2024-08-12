<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$groups = $DB->getDTable("select * from t_groups where server_id = '" . $DRAdmin['server_id'] . "'");

$symbolTypes = $DB->getDTable("select * from t_type where server_id = '" . $DRAdmin['server_id'] . "' and status = 1");

$activityList = $DB->getDTable("select * from `t_activity_list` where server_id = '" . $DRAdmin['server_id'] . "' and `status` >= 0");

$osLangArr = array();
$query = $DB->query("select f_type,f_lang,f_title from t_lang_otherset where f_serverId = '{$DRAdmin['server_id']}' and f_type like '-_activity_-%' and f_lang = '" . $CurrLangName . "'");
while($rs = $DB->fetchArray($query)){
	$osLangArr[$rs['f_type']] = $rs['f_title'];
}
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
                                    <h4 class="page-title"><?php echo L('赠金规则设置') , getCurrMt4ServerName();?></h4>
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
                                                <a href="#nolink" id="addcredit" data-target="#myModal" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加赠金规则');?></a>
                                            </div>
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('编号');?></th>
                                                    <th class="no-sort"><?php echo L('规则名称');?></th>
                                                    <th class="no-sort"><?php echo L('达标条件');?></th>
                                                    <th class="no-sort"><?php echo L('达标赠金');?></th>
                                                    <th class="no-sort"><?php echo L('MT分组');?></th>
                                                    <th class="no-sort"><?php echo L('日期');?></th>
                                                    <th class="no-sort"><?php echo L('转余额');?></th>
                                                    <th class="no-sort"><?php echo L('赠金到期扣回');?>(<?php echo L('天');?>)</th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where ServerId = '{$DRAdmin['server_id']}' and `Status` in (0,1)";
	
	$recordCount = intval($DB->getField("select count(*) from `t_credit_setting` {$where}"));
	
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
	$query = $DB->query("select * from `t_credit_setting` {$where} order by f_startTime desc,EndTime desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['Id'] , '</td>';
			echo '<td>' , $rs['Name'] , '</td>';
			echo '<td>';
			if($rs['Type'] == 'BALANCE_FIRST'){
				echo '<span class="badge badge-success">' , L('首次入金') , '</span> ';
				echo number_format($rs['Condition'],2,".",",") , '~' , number_format($rs['f_conditionEnd'],2,".",",") , ' USD';
			}else if($rs['Type'] == 'BALANCE_PER'){
				echo '<span class="badge badge-info">' , L('单笔入金') , '</span> ';
				echo number_format($rs['Condition'],2,".",",") , '~' , number_format($rs['f_conditionEnd'],2,".",",") , ' USD';
			}else if($rs['Type'] == 'REG'){
				echo '<span class="badge badge-danger">' , L('开户') , '</span> ';
			}else{
				echo '<span class="badge badge-info">' , L('!!!ERROR!!!') , '</span> ';
			}
			echo '<br>';
			if($rs['f_activityId'] > 0){
				echo $osLangArr['-_activity_-' . $rs['f_activityId']];
			}
			echo '</td>';
			echo '<td>';
			echo number_format($rs['Result'],2,".",",");
			if($rs['Scale'] == 'Scale'){
				echo ' %';
			}else{
				echo ' USD';
			}
			//echo number_format($rs['total_deposit'],2,".",",") , L('美金');
			echo '</td>';
			echo '<td style="word-break:break-all;">' , str_replace(',','<br>',trim($rs['f_group'],',')) , '</td>';
			echo '<td>';
			if($rs['f_startTime'] > time()){
				echo '<span class="badge badge-info">' , L('未开始') , '</span> ';
			}else{
				if($rs['EndTime'] > time()){
					echo '<span class="badge badge-success">' , L('在有效期内') , '</span> ';
				}else{
					echo '<span class="badge badge-danger">' , L('已过期') , '</span> ';
				}
			}
			echo '<br>';
			echo strlen($rs['f_startTime']) > 0 ? date('Y-m-d H:i:s',$rs['f_startTime']) : L('无');
			echo '<br> ~ <br>';
			echo strlen($rs['EndTime']) > 0 ? date('Y-m-d H:i:s',$rs['EndTime']) : L('无');
			echo '</td>';
			echo '<td>';
			if($rs['f_zye_days'] > 0 && strlen($rs['f_zye_symbol']) > 0 && $rs['f_zye_lot'] > 0){
				echo L('赠金产生后') , $rs['f_zye_days'] , L('天内');
				echo '<br>';
				echo L('产品') , '：' , trim($rs['f_zye_symbol'],',');
				echo '<br>';
				echo $rs['f_zye_lot'] * 1 , L('手交易');
				echo '<br>';
				echo L('持仓至少') , $rs['f_zye_keepTimeSecond'] , L('秒');
			}else{
				echo '-';
			}
			echo '</td>';
			echo '<td>' , $rs['f_overDays'] , '</td>';
			echo '<td>';
			if($rs['Status'] == 1){
				echo '<span class="badge badge-success">' , L('启用') , '</span> ';
			}else{
				echo '<span class="badge badge-danger">' , L('停止') , '</span> ';
			}
			echo '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs modifycredit" val="' , $rs['Id'] , '" type="button" data-toggle="modal" href="#onlink">' , L('修改') , '</a> ';
			if($rs['Status'] == 1){
				echo '<a class="btn btn-warning btn-xs stopcredit" rel="' , $rs['Id'] , '" type="button" data-toggle="modal" href="#onlink">' , L('停止') , '</a> ';
			}else{
				echo '<a class="btn btn-primary btn-xs startcredit" rel="' , $rs['Id'] , '" type="button" data-toggle="modal" href="#onlink">' , L('启用') , '</a> ';
			}
			echo '<a class="btn btn-danger btn-red-cz btn-xs delcredit" type="button" href="#nolink" url="?clause=delinfo&id=' , $rs['Id'] , '">' , L('删除') , '</a> ';
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
                        <h4 class="modal-title"><?php echo L('添加赠金规则'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='addform' name='addform'>
		            	<input type="hidden" name="Id" ID="Id" value="" />
		                <div class="modal-body">
		               	 	<div class="row"> 
		                        <div class="col-md-12"> 
				                    <div class="form-group"><label><?php echo L('规则名称'); ?>：</label> 
										 <input type="text"   name='Name' id="Name" placeholder="" class="form-control"> 
			                        </div>
		                        </div>
		                    </div>    
	                       	<div class="row"> 
		                        <div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('达标类型'); ?>：</label> 
				                 		<select name='Type' id="Type" class='form-control m-b'>
		                                 	<option value='BALANCE_FIRST'><?php echo L('首次入金'); ?></option>
		                                 	<option value='BALANCE_PER'><?php echo L('单笔入金'); ?></option>
                                            <option value='REG'><?php echo L('开户'); ?></option>
		                                 	<!--  <option value='Trade'>交易</option>-->
			                            </select>
				                    </div>
			                    </div>
                                <div class="col-md-6 noreginput"> 
				                 	<div class="form-group"><label><?php echo L('MT分组'); ?>：</label> 
				                 		<select name="GROUP_NAME[]" id="GROUP_NAME" data-placeholder="<?php echo L('请选择分组'); ?>" class="chosen-select" multiple>
                                            <option value="all_group"><?php echo L('全部'); ?></option>
                                             <?php
                                             foreach($groups as $j=>$vo){
                                             ?>
                                            <option value="<?php echo $vo['group']; ?>"><?php echo $vo['group']; ?></option>
                                            <?php
                                             }
                                             ?>
                                        </select>
				                    </div>
			                    </div>
			                </div>
	                       	<div class="row noreginput"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('达标数值'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled"   name='Condition' placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text">~</span>
											</div>
											<input type="text"  style="ime-mode:disabled"   name='f_conditionEnd' placeholder="" class="form-control"> 
										</div>				                 		
				                    </div>
			                    </div>
			                </div>
			                <div class="row"> 
			                 	<div class="col-md-6"> 
				                      <div class="form-group"><label><?php echo L('达标赠金'); ?>：</label>
				                       <select name='Scale' id="Scale" class='form-control m-b'>
		                                 	<option value='Fixed'><?php echo L('固定'); ?>($)</option>
		                                 	<option value='Scale'><?php echo L('比例'); ?>(%)</option>
			                            </select>
				                    </div>
		                       	</div>
		                        <div class="col-md-6"> 
			                     	<div class="form-group"><label><?php echo L('达标赠金'); ?>（<span id="scale_uni">$</span>）：</label>
				                        <input type="text" class="form-control" name="Result" id="Result" placeholder=""> 
				                    </div>
				                 </div>
				                
				             </div>

				            <div class="row"> 
			                 	<div class="col-md-6"> 
				                    <div class="form-group"><label><?php echo L('开始日期'); ?>：</label>
				                       <input placeholder="" name="f_startTime" class="form-control layer-date">
				                    </div>
		                       	</div>
			                 	<div class="col-md-6"> 
				                    <div class="form-group"><label><?php echo L('结束日期'); ?>：</label>
				                       <input placeholder="" name="EndTime" class="form-control layer-date">
				                    </div>
		                       	</div>
				            </div>

	                       	<div class="row"> 
	                        	<div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('赠金到期扣回'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='f_overDays' placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('天'); ?></span>
											</div>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>


	                       	<div class="row" style="background-color:#DEF0D8;padding-top:10px;"> 
	                        	<div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('赠金产生后'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='f_zye_days' placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('天内'); ?></span>
											</div>
										</div>				                 		
				                    </div>
			                    </div>
								<div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('产品'); ?>：</label> 
										<div class="input-group">
                                            <select name="zye_symbol[]" data-placeholder="<?php echo L('请选择'); ?>" class="chosen-select" multiple>
                                            <option value="all_zye_symbol"><?php echo L('全部'); ?></option>
                                             <?php
                                             foreach($symbolTypes as $j=>$vo){
                                             ?>
                                            <option value="<?php echo $vo['type_name']; ?>"><?php echo $vo['type_name']; ?></option>
                                            <?php
                                             }
                                             ?>
                                        </select>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>
	                       	<div class="row" style="background-color:#DEF0D8;"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('合计达到交易量(开仓且平仓)，该赠金转进用户余额，否则到期扣回'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='f_zye_lot' placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('手交易'); ?></span>
											</div>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>
	                       	<div class="row" style="background-color:#DEF0D8;"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('持仓至少'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='f_zye_keepTimeSecond' placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('秒'); ?></span>
											</div>
										</div>
				                    </div>
			                    </div>
			                </div>


	                       	<div class="row noreginput" style="background-color:#F2DEDF;padding-top:10px;"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('报名并且审核通过活动，本规则方才生效'); ?>：</label> 
										<div class="input-group">
											<select name="f_activityId" data-placeholder="<?php echo L('请选择'); ?>">
                                                <option value="0">-=<?php echo L('无关联'); ?>=-</option>
                                                 <?php
                                                 foreach($activityList as $j=>$vo){
                                                 ?>
                                                <option value="<?php echo $vo['id']; ?>"><?php echo $osLangArr['-_activity_-' . $vo['id']]; ?></option>
                                                <?php
                                                 }
                                                 ?>
                                            </select>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>


				              <div class="row" style="padding-top:10px;"> 
			                 	<div class="col-md-12"> 
				                      <div class="form-group"><label><?php echo L('规则描述'); ?>：</label>
				                      <textarea id="Memo" name="Memo" class="form-control" required="" aria-required="true" placeholder="<?php echo L('请输入赠金规则具体描述'); ?>"></textarea>
				                    </div>
		                       	</div>
				             </div>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" id='closeoutadd' data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='addsavecredit' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
	    <!--弹出层-->
	    
	    
	     <div class="modal inmodal" id="modifyModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('赠金规则设置'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingform' name='settingform'>
		            	<input type="hidden" name="Id" id="Id1" value="" />
		                <div class="modal-body">
		               	 	<div class="row"> 
		                        <div class="col-md-12"> 
				                    <div class="form-group"><label><?php echo L('赠金规则名称'); ?>：</label> 
										 <input type="text"    name='Name' id="Name1" placeholder="<?php echo L('请输入赠金规则名称'); ?>" class="form-control"> 
			                        </div>
		                        </div>
		                    </div>    
	                       	<div class="row"> 
		                        <div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('达标类型'); ?>：</label> 
				                 		<select name='Type' id="Type1" class='form-control m-b'>
		                                 	<option value='BALANCE_FIRST'><?php echo L('首次入金'); ?></option>
		                                 	<option value='BALANCE_PER'><?php echo L('单笔入金'); ?></option>
                                            <option value='REG'><?php echo L('开户'); ?></option>
		                                 	<!--  <option value='Trade'>交易</option>-->
			                            </select>
				                    </div>
			                    </div>
                                <div class="col-md-6 noreginput"> 
				                 	<div class="form-group"><label><?php echo L('MT分组'); ?>：</label> 
				                 		<select name="GROUP_NAME[]" id="GROUP_NAME1" data-placeholder="<?php echo L('请选择分组'); ?>" class="chosen-select" multiple>
                                            <option value="all_group"><?php echo L('全部'); ?></option>
                                             <?php
                                             foreach($groups as $j=>$vo){
                                             ?>
                                            <option value="<?php echo $vo['group']; ?>"><?php echo $vo['group']; ?></option>
                                            <?php
                                             }
                                             ?>
                                        </select>
				                    </div>
			                    </div>
		                    </div>    
	                       	<div class="row noreginput"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('达标数值'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='Condition' id="Condition1" placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text">~</span>
											</div>
											<input type="text"  style="ime-mode:disabled" name='f_conditionEnd' id='f_conditionEnd' placeholder="" class="form-control"> 
										</div>
				                    </div>
			                    </div>
			                </div>
			                <div class="row"> 
			                 	<div class="col-md-6"> 
				                      <div class="form-group"><label><?php echo L('达标赠金'); ?>：</label>
				                       <select name='Scale' id="Scale1" class='form-control m-b'>
		                                 	<option value='Fixed'><?php echo L('固定'); ?></option>
		                                 	<option value='Scale'><?php echo L('比例'); ?></option>
			                            </select>
				                    </div>
		                       	</div>
		                        <div class="col-md-6"> 
			                     	<div class="form-group"><label><?php echo L('达标赠金'); ?>（<span id="scale_uni1">$</span>）：</label>
				                        <input type="text" class="form-control" name="Result" id="Result1" placeholder="<?php echo L('请输入达标奖励'); ?>"> 
				                    </div>
				                </div> 
				            </div>

				            <div class="row"> 
			                 	<div class="col-md-6"> 
				                    <div class="form-group"><label><?php echo L('开始日期'); ?>：</label>
				                       <input placeholder="" name="f_startTime" id="f_startTime" class="form-control layer-date">
				                    </div>
		                       	</div>
			                 	<div class="col-md-6"> 
				                    <div class="form-group"><label><?php echo L('结束日期'); ?>：</label>
				                       <input placeholder="" name="EndTime" class="form-control layer-date" id="end_time">
				                    </div>
		                       	</div>
				            </div>
	                       	<div class="row"> 
	                        	<div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('赠金到期扣回'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name="f_overDays" id="f_overDays" placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('天'); ?></span>
											</div>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>
                            
                            
	                       	<div class="row" style="background-color:#DEF0D8;padding-top:10px;"> 
	                        	<div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('赠金产生后'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='f_zye_days' id='f_zye_days' placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('天内'); ?></span>
											</div>
										</div>				                 		
				                    </div>
			                    </div>
								<div class="col-md-6"> 
				                 	<div class="form-group"><label><?php echo L('产品'); ?>：</label> 
										<div class="input-group">
                                            <select name="zye_symbol[]" id="zye_symbol" data-placeholder="<?php echo L('请选择'); ?>" class="chosen-select" multiple>
                                            <option value="all_zye_symbol"><?php echo L('全部'); ?></option>
                                             <?php
                                             foreach($symbolTypes as $j=>$vo){
                                             ?>
                                            <option value="<?php echo $vo['type_name']; ?>"><?php echo $vo['type_name']; ?></option>
                                            <?php
                                             }
                                             ?>
                                        </select>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>
	                       	<div class="row" style="background-color:#DEF0D8;"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('合计达到交易量(开仓且平仓)，该赠金转进用户余额，否则到期扣回'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='f_zye_lot' id='f_zye_lot' placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('手交易'); ?></span>
											</div>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>
	                       	<div class="row" style="background-color:#DEF0D8;"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('持仓至少'); ?>：</label> 
										<div class="input-group">
											<input type="text"  style="ime-mode:disabled" name='f_zye_keepTimeSecond' id="f_zye_keepTimeSecond" placeholder="" class="form-control"> 
											<div class="input-group-prepend">
												<span class="input-group-text"><?php echo L('秒'); ?></span>
											</div>
										</div>
				                    </div>
			                    </div>
			                </div>


	                       	<div class="row noreginput" style="background-color:#F2DEDF;padding-top:10px;"> 
	                        	<div class="col-md-12"> 
				                 	<div class="form-group"><label><?php echo L('报名并且审核通过活动，本规则方才生效'); ?>：</label> 
										<div class="input-group">
											<select name="f_activityId" id="f_activityId" data-placeholder="<?php echo L('请选择'); ?>">
                                                <option value="0">-=<?php echo L('无关联'); ?>=-</option>
                                                 <?php
                                                 foreach($activityList as $j=>$vo){
                                                 ?>
                                                <option value="<?php echo $vo['id']; ?>"><?php echo $osLangArr['-_activity_-' . $vo['id']]; ?></option>
                                                <?php
                                                 }
                                                 ?>
                                            </select>
										</div>				                 		
				                    </div>
			                    </div>
			                </div>
                            
                            
				              <div class="row"> 
			                 	<div class="col-md-12"> 
				                      <div class="form-group"><label><?php echo L('规则描述'); ?>：</label>
				                      <textarea id="Memo1" name="Memo" class="form-control" required="" aria-required="true" placeholder="<?php echo L('请输入赠金规则具体描述'); ?>"></textarea>
				                    </div>
		                       	</div>
				             </div>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" id='closeoutmodify' data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='savecredit' ><?php echo L('确认'); ?></button>
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

        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script>
        
		<script src="/assets/js/datapicker/js/bootstrap-datepicker.min.js"></script>
        <link href="/assets/js/datapicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <?php
        if($CurrLangName == 'zh-cn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.zh-CN.min.js"></script>';
			$DatepickerLangName = 'zh-CN';
		}else if($CurrLangName == 'zh-vn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.vi.min.js"></script>';
			$DatepickerLangName = 'vi';
		}
		?>
        <script>
			$("form .layer-date").datepicker({
				<?php
				if(strlen($DatepickerLangName)){
					echo 'language: "' , $DatepickerLangName , '",';
				}
				?>
				keyboardNavigation: !1,
				forceParse: 1,
				autoclose: !0,
				clearBtn: !0,
				format: 'yyyy-mm-dd'
			});
		</script>
        
        
        
        <script src="/assets/js/select2.min.js"></script> 
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script>
        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>
        <script src="/assets/js/suggest/bootstrap-suggest.min.js"></script>
		<script>
			//去除数组中的空值
			function trimSpace(array){
				 for(var i = 0 ;i<array.length;i++){
					 if(array[i] == "" || typeof(array[i]) == "undefined"){
						  array.splice(i,1);
						  i= i-1;
					 }
				 }
				 return array;
			}
		
			function chose_mult_set_ini(select_, values) {
				var arr = values.split(',');
				var length = arr.length;
				var value = '';
				newarr = trimSpace(arr);
				$(select_).val(newarr);
				$(select_).trigger("chosen:updated");
			}
		
			$(".chosen-select").chosen( {width: "100%"});
        </script>
        
        
        
        
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
		$('#Scale').change(function(){
			if($('#Scale').val() == 'Scale'){
				$('#scale_uni').html('%');
			}else{
				$('#scale_uni').html('$');
			}
		});
		$('#Scale1').change(function(){
			if($('#Scale1').val() == 'Scale'){
				$('#scale_uni1').html('%');
			}else{
				$('#scale_uni1').html('$');
			}
		});

		$(document).on("click","#addsavecredit",function(){
    	 //$("#addsavecredit").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveinfo";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.settingform.reset();
                    $("#closeoutadd").click();
                    setTimeout(function(){
						document.location.reload();
					},1500);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
    	 
		 $(document).on("click","#savecredit",function(){
    	 //$("#savecredit").click(function() {
             $(this).attr('disabled', "disabled");
           	var _this=$(this);
             var form = $(this).closest('form');
             var url = "?clause=saveinfo";

             $.post(url, form.serialize(), function(data) {
                 layer.msg(data.info);
                 if (data.status) {
                     document.settingform.reset();
                     $("#closeoutmodify").click();
                    setTimeout(function(){
						document.location.reload();
					},1500);
                 }
                 _this.removeAttr("disabled");
             }, 'json')
         });
        
		$(document).on("click","#addcredit",function(){
        //$("#addcredit").click(function() {
            document.addform.reset();
           	$('#myModal').modal('toggle'); 
        });
    
        
		$(document).on("click",".modifycredit",function(){
    	//$(".modifycredit").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=getinfo";
			var ID =  $(this).attr('val');
            $.post(url, "id="+ID, function(data) {
                if (data.status) {
                	console.log(data.data);
                	$("#Id1").val(data.data.Id);
                	$("#Name1").val(data.data.Name);
                	$("#Scale1").val(data.data.Scale);
                	$("#Type1").val(data.data.Type);
                    $("#Condition1").val(data.data.Condition);
                    $("#Result1").val(data.data.Result);
                    $("#Memo1").val(data.data.Memo);
                    $("#end_time").val(data.data.end_time);
					$("#f_conditionEnd").val(data.data.f_conditionEnd);
					$("#f_startTime").val(data.data.f_startTime);
					$("#f_overDays").val(data.data.f_overDays);
					
					$("#f_zye_days").val(data.data.f_zye_days);
					$("#f_zye_lot").val(data.data.f_zye_lot);
					$("#f_zye_keepTimeSecond").val(data.data.f_zye_keepTimeSecond);
					$("#f_activityId").val(data.data.f_activityId);
					
					chose_mult_set_ini('#GROUP_NAME1',data.data.f_group);
					
					chose_mult_set_ini('#zye_symbol',data.data.f_zye_symbol);

					$('#Type1').change();
					
                   	$('#modifyModal').modal('toggle');

					$('#Scale1').change();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
		
		$(document).on("click","#closeoutmodify,.close",function(){
        //$("#closeoutmodify,.close").click(function(){
             $("#modifyModal").hide();
        });
		
		$(document).on("click","#closeoutadd,.close",function(){
        //$("#closeoutadd,.close").click(function(){
            $("#myModal").hide();
       });
        
		$(document).on("click",".startcredit",function(){
        //$('.startcredit').click(function () {
        	var ID =  $(this).attr('rel');
		    swal({
		        title: "<?php echo L('您确定要发布这条赠金规则吗'); ?>",
		        showCancelButton: true,
		        confirmButtonColor: "#DD6B55",
		        confirmButtonText: "<?php echo L('发布'); ?>",
		        closeOnConfirm: false,   
		        showLoaderOnConfirm: true,
		    }, function () {
		    	var url = "?clause=saveSettingStatus";
		    	$.post(url, "ID="+ID + "&STATUS=1", function(data) {
	                if(data.status == 0) {
	                	swal("<?php echo L('发布成功'); ?>！",  data.info, "success");
	                	document.location.reload();
	        		}else{
	        			swal("<?php echo L('发布失败'); ?>！", data.info, "warning");
	                	 _this.removeAttr("disabled");
	        		}
		    	}, 'json');
		    });
		});
        
		$(document).on("click",".stopcredit",function(){
        //$('.stopcredit').click(function () {
        	var ID =  $(this).attr('rel');
		    swal({
		        title: "<?php echo L('您确定要停止这条赠金规则吗'); ?>",
		        showCancelButton: true,
		        confirmButtonColor: "#DD6B55",
		        confirmButtonText: "<?php echo L('停止'); ?>",
		        closeOnConfirm: false,   
		        showLoaderOnConfirm: true,
		    }, function () {
		    	var url = "?clause=saveSettingStatus";
		    	$.post(url, "ID="+ID + "&STATUS=0", function(data) {
	                if(data.status == 0) {
	                	swal("<?php echo L('停止成功'); ?>！",  data.info, "success");
	                	document.location.reload();
	        		}else{
	        			swal("<?php echo L('停止失败'); ?>！", data.info, "warning");
	                	 _this.removeAttr("disabled");
	        		}
		    	}, 'json');
		    });
		});
		
		
		
        $(document).on("click",".delcredit",function(){
        //$('.delcredit').click(function () {
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
        
       <script>
	   $('#Type').change(function(){
		   var val = $(this).val();
		   if(val == 'REG'){
				$('#myModal .noreginput').hide();
		   }else{
				$('#myModal .noreginput').show();
		   }
		});
		 $('#Type1').change(function(){
		   var val = $(this).val();
		   if(val == 'REG'){
				$('#modifyModal .noreginput').hide();
		   }else{
				$('#modifyModal .noreginput').show();
		   }
		});
		 
	   </script>
        
        
        
        

    </body>
</html>
