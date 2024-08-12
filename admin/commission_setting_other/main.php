<?php
$LoadCSSArr = array();
require_once('header.php');


//-------------------------------------------

$where = 'setting.status in (1,3) and  setting.SERVER_ID =' . $DRAdmin['server_id'] . '';
if ($_REQUEST['Q_SYMBOL_TYPE']) {
	$where = $where . " and setting.SYMBOL_TYPE like '%," . $_REQUEST['Q_SYMBOL_TYPE'] . ",%'";
}
if ($_REQUEST['Q_GROUP_NAME']) {
	$where = $where . " and setting.GROUP_NAME like '%," . str_replace('\\','\\\\\\\\',implode('',$_REQUEST['Q_GROUP_NAME'])) . ",%'";
}

//echo $where;exit;

//代理-内佣
$agentsql = $where . " and  setting.MODEL_TYPE='agent' and setting.SERVER_ID = " . $DRAdmin['server_id'];
$agentlist = $DB->getDTable("select setting.* from t_sale_setting_other setting where {$agentsql} order by setting.LEVEL,setting.GROUP_NAME asc");
//替换等级
$ranks = $DB->getDTable("select * from t_ib_rank where server_id = '{$DRAdmin['server_id']}' and status = 1 order by rank asc");
$step = 0;
foreach ($agentlist as $key => $value) {
	$step++;
	$exist_alias = false;
	foreach ($ranks as $key1 => $value1) {
		if ($value['LEVEL'] == $value1['rank'] && $value['MODEL_TYPE'] == $value1['model_type']) {
			$agentlist[$step - 1]['LEVEL_NAME'] = $value1['rank_name'];
			$exist_alias = true;
			break;
		}
	}
	if (!$exist_alias) {
		$agentlist[$step - 1]['LEVEL_NAME'] = $value['LEVEL'] . ' ' . L('级') . ' ' . L('代理');
	}
}


$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1");
$groups = $DB->getDTable("select * from t_groups where server_id = '" . $DRAdmin['server_id'] . "'");
?>
                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('佣金设置') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title"><?php echo L('搜索');?></h4>

                                        <div>
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get">
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易种类');?>：
                                                    <select name="Q_SYMBOL_TYPE" id="Q_SYMBOL_TYPE" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														foreach($types as $key=>$val){
															echo '<option value="' , $val['type_name'] , '">' , $val['type_name'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2" style="min-width:120px;">
                                                    <select data-placeholder="<?php echo L('请选择分组');?>" name="Q_GROUP_NAME[]" class="chosen-select" multiple>
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														foreach($groups as $key=>$val){
															echo '<option value="' , $val['group'] , '" hassubinfo="true"';
															if(in_array($val['group'],$_GET['Q_GROUP_NAME'])){
																echo ' selected';
															}
															echo '>' , $val['group'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<div>
                                                        <button type="submit" class="btn btn-primary"><?php echo L('搜索');?></button>
                                                        <?php
														echo '<a href="?clause=addinfo" class="btn btn-danger"><i class="md md-add"></i>' , L('添加设置') , '</a> ';
														//echo '<a href="#nolink"  id="downBtns" class="btn btn-primary">' , L('下载记录') , '</a>';
														?>
                                                    </div>
                                                </div>
                                            </form>
                                        </div> <!-- end row -->

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div> 
                        <!-- end row-->
                        
                        

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        
                                        
                                        
                                        
                                        
<?php
function write_bonus_type_0($j,$vo){
	global $DRAdmin;
	$mt4_tcdatee = strtotime($vo['TC_DATE_E']) - 3600 * floatval(8 - $DRAdmin['timezone']);

	echo '<tr class="gradeX" id="tr_' , $vo['ID'] , '" style="word-break:break-word;">';
	echo '<td>' , $vo['ID'] , '</td>';
	echo '<td>';
		if($vo['LEVEL'] <= 0){
			echo L('代理');
		}else{
			echo $vo['LEVEL_NAME'];
		}
	echo '</td>';
	echo '<td style="word-break:break-all;">' , $vo['f_title'] , '<br>' , trim($vo['SYMBOL_TYPE'],',') , '</td>';
	echo '<td style="word-break:break-all;">' , str_replace(',','<br>',trim($vo['GROUP_NAME'],',')) , '</td>';
	echo '<td style="word-break:break-all;">';
	if($vo['GROUP_TYPE'] == 1){
		echo L('直接下级');
	}else if($vo['GROUP_TYPE'] == 2){
		echo L('伞下');
	}
	echo '</td>';
	echo '<td>';
	echo date('Y-m-d',strtotime($vo['TC_DATE_S'])) , ' ~ ' , date('Y-m-d',strtotime($vo['TC_DATE_E'])) , '<br>';
	echo L('达到手数') , ': ' , $vo['LIMIT_MIN_SS'] * 1 , '<br>';
	echo cvd_str_cal_type($vo['CAL_TYPE_AGENT'],$vo['CAL_NUM_AGENT']);
	echo '</td>';
	echo '<td>';
		if($vo['f_isJs'] == 1){
			echo L('已结算') , '<br>';
			echo $vo['f_jsTime'] , '<br>';
			echo $vo['f_jsAbout'];
		}else{
			echo L('待结算');
		}
	echo '</td>';
	echo '<td id="state_' , $vo['ID'] , '">';
		if($vo['STATUS'] == 1){
			echo L('启用');
		}else{
			echo L('禁用');
		}
	echo '</td>';
	echo '<td class="center">';
		//if($mt4_tcdatee > time()){
		if($vo['f_isJs'] <= 0){
			echo '<a class="btn btn-primary btn-xs modifysetting" type="button" href="?clause=addinfo&id=' , $vo['ID'] , '">' , L('修改') , '</a> ';
		}
		echo '<a class="btn btn-danger btn-red-cz btn-xs delete" type="button" rel="' , $vo['ID'] , '" href="#nolink">' , L('删除') , '</a> ';
		if($vo['STATUS'] == '1'){
			echo '<a class="btn btn-danger btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('禁用') , '</a> ';
		}else{
			echo '<a class="btn btn-primary btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('启用') , '</a>';
		}
	echo '</td>';
	echo '</tr>';
}
?>                                 
                                        
                                        
                                        
                                        
<div class="ibox-content">
    <span class="help-block m-b-none"></span>
    <div class="tab-content">
    <div id="tab-1" class="tab-pane active agentlitab">
        <div class="tab-content">
            <div id="subtab-1" class="tab-pane active">
                <table class="table table-hover  table-bordered  " >
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th><?php echo L('等级'); ?></th>
                            <th width="20%"><?php echo L('交易种类'); ?></th>
                            <th width="20%"><?php echo L('MT分组'); ?></th>
                            <th><?php echo L('统计团队'); ?></th>
							<th><?php echo L('平仓时间'); ?> / <?php echo L('达到手数'); ?> / <?php echo L('返佣标准'); ?></th>
                            <th><?php echo L('状态'); ?></th>
							<th><?php echo L('结算'); ?></th>
                            <th><?php echo L('操作'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($agentlist as $j=>$vo){
                        write_bonus_type_0($j,$vo);
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>


    </div>
</div>
                    
                    
                    
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                        

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div> 


                        
                        <!-- end row-->


                    </div> <!-- container -->





		<?php
        require_once('footer.php');
        ?>
        
        <script src="/assets/js/layer/layer.js"></script>
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/select2.min.js"></script> 
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script>
        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>
        <script src="/assets/js/suggest/bootstrap-suggest.min.js"></script>
        
        
        
    <script>        
         $(".chosen-select").chosen( {width: "100%"});
        
		$(document).on("click",".delete",function(){
        //$('.delete').click(function () {
            var ID =  $(this).attr('rel');
            swal({
                title: "<?php echo L('您确定要删除这条信息吗'); ?>",
                text: "<?php echo L('删除后将无法恢复，请谨慎操作'); ?>！",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "<?php echo L('删除'); ?>",
                closeOnConfirm: false,   
                showLoaderOnConfirm: true,
            }, function () {
                var url = "?clause=deleteSetting";
                $.post(url, "ID="+ID, function(data) {
                    if(data.status==0) {
                        swal("<?php echo L('删除成功'); ?>！", "<?php echo L('您已经删除了这条信息'); ?>。", "success");
                         $("#tr_"+ID).remove();
                    }else if(data.status==1){
                        swal("<?php echo L('删除失败'); ?>！", "<?php echo L('当前记录早被删除'); ?>。", "warning");
                        $("#tr_"+ID).remove();
                    }else if(data.status==2){
                        swal("<?php echo L('删除失败'); ?>！", "<?php echo L('当前记录不存在'); ?>。", "warning");
                         $("#tr_"+ID).remove();
                    }else if(data.status==3){
                        swal("<?php echo L('删除失败'); ?>！", "<?php echo L('删除出错'); ?>。", "warning");
                         _this.removeAttr("disabled");
                    }
                }, 'json');
            });
        });
        
		$(document).on("click",".forbidenreopen",function(){
        //$('.forbidenreopen').click(function () {
            var ID =  $(this).attr('rel');
            var txt = $(this).text();
            var _this=$(this);
            var status = "1";
            var nextText = "",className="";
            if(txt=="<?php echo L('禁用'); ?>"){
                status='3';
                nextText="<?php echo L('启用'); ?>";
                className="btn-primary";
            }else{
                status='1';
                nextText="<?php echo L('禁用'); ?>";
                className="btn-danger";
            }
            swal({
                title: "<?php echo L('您确定要'); ?>"+txt+"<?php echo L('这条信息吗'); ?>",
                text: txt+"<?php echo L('后将无法返佣'); ?>，<?php echo L('请谨慎操作'); ?>！",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: txt,
                closeOnConfirm: false,   
                showLoaderOnConfirm: true,
            }, function () {
                var url = "?clause=saveSettingStatus";
                $.post(url, "ID="+ID+"&STATUS="+status, function(data) {
                    if(data.status==0) {
                        swal(txt+"<?php echo L('成功'); ?>！", "<?php echo L('您已经'); ?>"+txt+"<?php echo L('了这条信息'); ?>。", "success");
                        _this.text(nextText);
                        _this.attr("class","btn "+className+" btn-xs forbidenreopen");
                         $("#state_"+ID).text(txt);
                    }else if(data.status==1){
                        swal(txt+"<?php echo L('失败'); ?>！", "<?php echo L('当前记录早被'); ?>"+txt+"。", "warning");
                        _this.text(nextText);
                         $("#state_"+ID).text(txt);
                        _this.attr("class","btn "+className+" btn-xs forbidenreopen");
                    }else if(data.status==2){
                        swal(txt+"<?php echo L('失败'); ?>！", "<?php echo L('当前记录不存在'); ?>。", "warning");
                         $("#tr_"+ID).remove();
                    }else if(data.status==3){
                        swal(txt+"<?php echo L('失败'); ?>！", txt+"<?php echo L('出错'); ?>。", "warning");
                         _this.removeAttr("disabled");
                    }
                    
                }, 'json');
            });
        });

                
    $('#Q_SYMBOL_TYPE').val('<?php echo $_REQUEST['Q_SYMBOL_TYPE']; ?>');
    $('#Q_GROUP_NAME').val('<?php echo $_REQUEST['Q_GROUP_NAME']; ?>');
    
	//------------------
    
    var modeltype = '<?php echo FGetStr('modeltype');?>';
	var bonustype = <?php echo FGetInt('bonustype') + 1;?>;
    if(modeltype != ''){
        $('#' + modeltype + 'li > a').click();
		
		if(modeltype != 'direct'){
			$('#sub' + modeltype + bonustype + ' > a').click();
		}
    }
    </script>
     
        

    </body>
</html>
