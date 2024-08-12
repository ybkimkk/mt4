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
$agentsql = $where . " and  setting.MODEL_TYPE='agent' and setting.BONUS_TYPE=0  and setting.SERVER_ID = " . $DRAdmin['server_id'];
$agentlist = $DB->getDTable("select setting.* from t_sale_setting_new setting where {$agentsql} order by setting.LEVEL,setting.GROUP_NAME asc");
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

//代理-外佣
$outagentsql = $where . " and  setting.MODEL_TYPE='agent' and setting.BONUS_TYPE=1 and setting.SERVER_ID = " . $DRAdmin['server_id'];
$outagentlist = $DB->getDTable("select setting.* from t_sale_setting_new setting where {$outagentsql} order by setting.LEVEL,setting.GROUP_NAME asc");
//替换等级
$step = 0;
foreach ($outagentlist as $key => $value) {
	$step++;
	$exist_alias = false;
	foreach ($ranks as $key1 => $value1) {
		if ($value['LEVEL'] == $value1['rank'] && $value['MODEL_TYPE'] == $value1['model_type']) {
			$outagentlist[$step - 1]['LEVEL_NAME'] = $value1['rank_name'];
			$exist_alias = true;
			break;
		}
	}
	if (!$exist_alias) {
		$outagentlist[$step - 1]['LEVEL_NAME'] = $value['LEVEL'] . ' ' . L('级') . ' ' . L('代理');
	}
}

//直客
$directsql = $where . " and  setting.MODEL_TYPE='direct' and setting.SERVER_ID = " . $DRAdmin['server_id'];
$directlist = $DB->getDTable("select setting.* from t_sale_setting_new setting where {$directsql} order by setting.LEVEL,setting.GROUP_NAME asc");

//员工-内佣
$membersql = $where . " and  setting.MODEL_TYPE='member' and setting.BONUS_TYPE=0  and setting.SERVER_ID = " . $DRAdmin['server_id'];
$memberlist = $DB->getDTable("select setting.* from t_sale_setting_new setting where {$membersql} order by setting.LEVEL,setting.GROUP_NAME asc");
//替换等级
$step = 0;
foreach ($memberlist as $key => $value) {
	$step++;
	$exist_alias = false;
	foreach ($ranks as $key1 => $value1) {
		if ($value['LEVEL'] == $value1['rank'] && $value['MODEL_TYPE'] == $value1['model_type']) {
			$memberlist[$step - 1]['LEVEL_NAME'] = $value1['rank_name'];
			$exist_alias = true;
			break;
		}
	}
	if (!$exist_alias) {
		$memberlist[$step - 1]['LEVEL_NAME'] = $value['LEVEL'] . ' ' . L('级') . ' ' . L('员工');
	}
}

//员工-外佣
$outmembersql = $where . " and  setting.MODEL_TYPE='member' and setting.BONUS_TYPE=1 and setting.SERVER_ID = " . $DRAdmin['server_id'];
$outmemberlist = $DB->getDTable("select setting.* from t_sale_setting_new setting where {$outmembersql} order by setting.LEVEL,setting.GROUP_NAME asc");
//替换等级
$step = 0;
foreach ($outmemberlist as $key => $value) {
	$step++;
	$exist_alias = false;
	foreach ($ranks as $key1 => $value1) {
		if ($value['LEVEL'] == $value1['rank'] && $value['MODEL_TYPE'] == $value1['model_type']) {
			$outmemberlist[$step - 1]['LEVEL_NAME'] = $value1['rank_name'];
			$exist_alias = true;
			break;
		}
	}
	if (!$exist_alias) {
		$outmemberlist[$step - 1]['LEVEL_NAME'] = $value['LEVEL'] . ' ' . L('级') . ' ' . L('员工');
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
function str_cal_type($calType,$calNum){
	$str = '';
	switch($calType){
		case 'FIXED':
			$str = '$' . ($calNum * 1) . '/' . L('手');
			break;
		case 'SCALE':
			$str = L('交易量') . '*' . ($calNum * 1) . '%';
			break;
		case 'POINT':
			$str = ($calNum * 1) . '/pip/' . L('手');
			break;
		case 'WIN':
			$str = L('盈利额') . '*' . ($calNum * 1) . '%';
			break;
		case 'group_win':
			$str = L('团队返佣额') . '*' . ($calNum * 1) . '%';
			break;
		default:
			$str = '<span style="color:#ff0000">ERROR</span>';
			break;
	}
	return $str;
}

function write_bonus_type_0($j,$vo){
	echo '<tr class="gradeX" id="tr_' , $vo['ID'] , '" style="word-break:break-word;">';
	echo '<td>' , $j + 1 , '</td>';
	echo '<td>';
		if($vo['ACCOUNT'] != '0'){
			echo L('仅限');
			echo $vo['LEVEL'];
			echo L('级');
			echo L('代理');
			echo '，';
			echo L('账户');
			echo '：';
			echo $vo['ACCOUNT'];
		}else{
			echo $vo['LEVEL_NAME'];
		}                               
	echo '</td>';
	echo '<td style="word-break:break-all;">' , trim($vo['SYMBOL_TYPE'],',') , '</td>';
	echo '<td style="word-break:break-all;">' , trim($vo['GROUP_NAME'],',') , '</td>';
	echo '<td>';
		if($vo['CAL_NUM_ZK'] > 0)
		{
			echo L('直接客户') , '：';
			echo str_cal_type($vo['CAL_TYPE_ZK'],$vo['CAL_NUM_ZK']);
			echo '<br/>';
		}
		if($vo['CAL_NUM_JC'] > 0)
		{
			echo L('级差') , '：';
			echo str_cal_type($vo['CAL_TYPE_JC'],$vo['CAL_NUM_JC']);
			echo '<br/>';
		}
		$isJOrG = 0;
		if($vo['CAL_NUM_JJ_2'] > 0 || $vo['CAL_NUM_JJ_1'] > 0 || $vo['CAL_NUM_JJ_0'] > 0)
		{
			echo L('间接客户') , '：';
			echo '<br>';
			if($vo['CAL_NUM_JJ_2'] > 0){
				echo '&#12288;&#12288;' , '（' , '&gt; ' , L("下级") , '）' , str_cal_type($vo['CAL_TYPE_JJ_2'],$vo['CAL_NUM_JJ_2']) , '<br>';
			}
			if($vo['CAL_NUM_JJ_1'] > 0){
				echo '&#12288;&#12288;' , '（' , '= ' , L("下级") , '）' , str_cal_type($vo['CAL_TYPE_JJ_1'],$vo['CAL_NUM_JJ_1']) , '<br>';
			}
			if($vo['CAL_NUM_JJ_0'] > 0){
				echo '&#12288;&#12288;' , '（' , '&lt; ' , L("下级") , '）' , str_cal_type($vo['CAL_TYPE_JJ_0'],$vo['CAL_NUM_JJ_0']) , '<br>';
			}
			
			$isJOrG++;
		}
		if($vo['CAL_NUM_GROUP_2'] > 0 || $vo['CAL_NUM_GROUP_1'] > 0 || $vo['CAL_NUM_GROUP_0'] > 0)
		{
			echo L('团队返佣') , '：';
			echo '<br>';
			if($vo['CAL_NUM_GROUP_2'] > 0){
				echo '&#12288;&#12288;' , '（' , '&gt; ' , L("下级") , '）' , str_cal_type('group_win',$vo['CAL_NUM_GROUP_2']) , '<br>';
			}
			if($vo['CAL_NUM_GROUP_1'] > 0){
				echo '&#12288;&#12288;' , '（' , '= ' , L("下级") , '）' , str_cal_type('group_win',$vo['CAL_NUM_GROUP_1']) , '<br>';
			}
			if($vo['CAL_NUM_GROUP_0'] > 0){
				echo '&#12288;&#12288;' , '（' , '&lt; ' , L("下级") , '）' , str_cal_type('group_win',$vo['CAL_NUM_GROUP_0']) , '<br>';
			}
			
			$isJOrG++;
		}
		if($isJOrG > 0){
			//echo str_jg_cal_type($vo['MODEL_JG_CAL_TYPE']);
		}
	echo '</td>';
	echo '<td>' , $vo['CREATE_TIME'] , '</td>';
	echo '<td id="state_' , $vo['ID'] , '">';
		if($vo['STATUS'] == 1){
			echo L('启用');
		}else{
			echo L('禁用');
		}
	echo '</td>';
	echo '<td class="center">';
		echo '<a class="btn btn-primary btn-xs modifysetting" type="button" href="?clause=addinfo&id=' , $vo['ID'] , '">' , L('修改') , '</a> ';
		echo '<a class="btn btn-danger btn-red-cz btn-xs delete" type="button" rel="' , $vo['ID'] , '" href="#nolink">' , L('删除') , '</a> ';
		if($vo['STATUS'] == '1'){
			echo '<a class="btn btn-danger btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('禁用') , '</a> ';
		}else{
			echo '<a class="btn btn-primary btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('启用') , '</a>';
		}
	echo '</td>';
	echo '</tr>';
}

function write_bonus_type_1($j,$vo){
	echo '<tr class="gradeX" id="tr_' , $vo['ID'] , '" style="word-break:break-word;">';
	echo '<td>' , $j + 1 , '</td>';
	echo '<td>';
		if($vo['ACCOUNT'] != '0'){
			echo L('仅限');
			echo $vo['LEVEL'];
			echo L('级');
			echo L('代理');
			echo '，';
			echo L('账户');
			echo '：';
			echo $vo['ACCOUNT'];
		}else{
			echo $vo['LEVEL_NAME'];
		}                               
	echo '</td>';
	echo '<td style="word-break:break-all;">' , trim($vo['SYMBOL_TYPE'],',') , '</td>';
	echo '<td style="word-break:break-all;">' , trim($vo['GROUP_NAME'],',') , '</td>';
	echo '<td>';
		if($vo['CAL_NUM_ZK'] > 0)
		{
			echo L('直接客户') , '：';
			echo str_cal_type($vo['CAL_TYPE_ZK'],$vo['CAL_NUM_ZK']);
			echo '<br/>';
		}
	echo '</td>';
	echo '<td>' , $vo['CREATE_TIME'] , '</td>';
	echo '<td id="state_' , $vo['ID'] , '">';
		if($vo['STATUS'] == 1){
			echo L('启用');
		}else{
			echo L('禁用');
		}
	echo '</td>';
	echo '<td class="center">';
		echo '<a class="btn btn-primary btn-xs modifysetting" type="button" href="?clause=addinfo&id=' , $vo['ID'] , '">' , L('修改') , '</a> ';
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
    <ul class="nav nav-tabs mb-sm-2">
        <li class="nav-item" id="agentli"><a class="nav-link active" data-toggle="tab" href="#tab-1" aria-expanded="false"><?php echo L('代理奖励'); ?></a></li>
        <li class="nav-item" id="directli"><a class="nav-link" data-toggle="tab" href="#tab-2" aria-expanded="true"><?php echo L('直客奖励'); ?></a></li>
        <li class="nav-item" id="memberli"><a class="nav-link" data-toggle="tab" href="#tab-3" aria-expanded="true"><?php echo L('员工奖励'); ?></a></li>                         
    </ul>
    <div class="tab-content">
    <div id="tab-1" class="tab-pane active agentlitab">
        <ul class="nav nav-tabs mb-sm-2">
            <li class="nav-item" id="subagent1"><a class="nav-link active" data-toggle="tab" href="#subtab-1" aria-expanded="false"> <?php echo L('内佣'); ?></a></li>
            <li class="nav-item" id="subagent2"><a class="nav-link" data-toggle="tab" href="#subtab-2" aria-expanded="true"><?php echo L('外佣'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div id="subtab-1" class="tab-pane active">
                <table class="table table-hover  table-bordered  " >
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th><?php echo L('等级'); ?></th>
                            <th width="20%"><?php echo L('交易种类'); ?></th>
                            <th width="20%"><?php echo L('MT分组'); ?></th>
                            <th><?php echo L('返佣标准'); ?></th>
                            <th><?php echo L('设置时间'); ?></th>
                            <th><?php echo L('状态'); ?></th>
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
            <div id="subtab-2" class="tab-pane">
                <table class="table table-hover  table-bordered  " >
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th><?php echo L('等级'); ?></th>
                            <th width="20%"><?php echo L('交易种类'); ?></th>
                            <th width="20%"><?php echo L('MT分组'); ?></th>
                            <th><?php echo L('返佣标准'); ?></th>
                            <th><?php echo L('设置时间'); ?></th>
                            <th><?php echo L('状态'); ?></th>
                            <th><?php echo L('操作'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($outagentlist as $j=>$vo){
                        write_bonus_type_1($j,$vo);
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="tab-2" class="tab-pane directlitab">
        <table class="table table-hover  table-bordered  " >
            <thead>
                <tr>
                    <th>No.</th>
                    <th><?php echo L('推荐层级'); ?></th>
                    <th width="20%"><?php echo L('交易种类'); ?></th>
                    <th width="20%"><?php echo L('MT分组'); ?></th>
                    <th><?php echo L('返佣标准'); ?></th>
                    <th><?php echo L('设置时间'); ?></th>
                    <th><?php echo L('状态'); ?></th>
                    <th><?php echo L('操作'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
			foreach($directlist as $j=>$vo){
				echo '<tr class="gradeX" id="tr_' , $vo['ID'] , '" style="word-break:break-word;">';
				echo '<td>' , $j + 1 , '</td>';
				echo '<td>';
					if($vo['ACCOUNT'] != '0'){
						echo L('仅限');
						echo $vo['LEVEL'];
						echo L('级');
						echo L('代理');
						echo '，';
						echo L('账户');
						echo '：';
						echo $vo['ACCOUNT'];
					}else{
					   echo $vo['LEVEL'] . ' ' . L('层');
					}
				echo '</td>';
				echo '<td style="word-break:break-all;">' , trim($vo['SYMBOL_TYPE'],',') , '</td>';
				echo '<td style="word-break:break-all;">' , trim($vo['GROUP_NAME'],',') , '</td>';
				echo '<td>';
					if($vo['CAL_NUM_ZK'] > 0)
					{
						echo L('直接客户') , '：';
						echo str_cal_type($vo['CAL_TYPE_ZK'],$vo['CAL_NUM_ZK']);
						echo '<br/>';
					}
				echo '</td>';
				echo '<td>' , $vo['CREATE_TIME'] , '</td>';
				echo '<td id="state_' , $vo['ID'] , '">';
					if($vo['STATUS'] == 1){
						echo L('启用');
					}else{
						echo L('禁用');
					}
				echo '</td>';
				echo '<td class="center">';
					echo '<a class="btn btn-primary btn-xs modifysetting" type="button" href="?clause=addinfo&id=' , $vo['ID'] , '">' , L('修改') , '</a> ';
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
            </tbody>
        </table>
     </div>
    <div id="tab-3" class="tab-pane memberlitab">
        <ul class="nav nav-tabs mb-sm-2">
            <li class="nav-item" id="submember1"><a class="nav-link active" data-toggle="tab" href="#submembertab-1" aria-expanded="false"> <?php echo L('内佣'); ?></a></li>
            <li class="nav-item" id="submember2"><a class="nav-link" data-toggle="tab" href="#submembertab-2" aria-expanded="true"><?php echo L('外佣'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div id="submembertab-1" class="tab-pane active">
                <table class="table table-hover  table-bordered  " >
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th><?php echo L('等级'); ?></th>
                            <th width="20%"><?php echo L('交易种类'); ?></th>
                            <th width="20%"><?php echo L('MT分组'); ?></th>
                            <th><?php echo L('返佣标准'); ?></th>
                            <th><?php echo L('设置时间'); ?></th>
                            <th><?php echo L('状态'); ?></th>
                            <th><?php echo L('操作'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($memberlist as $j=>$vo){
                        write_bonus_type_0($j,$vo);
                    }
                    ?>
                </table>
            </div>
            <div id="submembertab-2" class="tab-pane">
                <table class="table table-hover  table-bordered  " >
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th><?php echo L('等级'); ?></th>
                            <th width="20%"><?php echo L('交易种类'); ?></th>
                            <th width="20%"><?php echo L('MT分组'); ?></th>
                            <th><?php echo L('返佣标准'); ?></th>
                            <th><?php echo L('设置时间'); ?></th>
                            <th><?php echo L('状态'); ?></th>
                            <th><?php echo L('操作'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($outmemberlist as $j=>$vo){
                        write_bonus_type_1($j,$vo);
                    }
                    ?>
                    </tbody>
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
