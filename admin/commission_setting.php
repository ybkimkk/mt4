<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

//代理奖励：推荐层级=5级，交易种类=x，MT分组=y，意思是：获得佣金奖励的客户必须是代理，并且是5级代理，佣金提供者在其伞下，提供奖金的客户的订单交易种类为x、属于y分组
//直客奖励：推荐层级=5级，交易种类=x，MT分组=y，意思是：获得佣金奖励的客户必须是直客，本身是在佣金提供者之上的第5层（佣金提供者的推荐人为第1层），提供奖金的客户的订单交易种类为x、属于y分组

if($Clause == 'savesettingstatus'){
	admin_action_log();

	$Id = FPostInt('ID');
	$STATUS = FPostStr('STATUS');
	$data = $DB->getDRow("select * from t_sale_setting where ID = '{$Id}'");
	if ($data) {
		if ($data['STATUS'] == $STATUS) {
			ajaxReturn($data, L('操作出错'), 1); //已经被修改过
		} else {
			$result = $DB->query("update t_sale_setting set `STATUS` = '{$STATUS}' where ID = '{$Id}'");
			ajaxReturn($data, L('操作成功'), 0);
		}
	}
	ajaxReturn($data, L('数据不存在'), 2);
	exit;
}else if($Clause == 'viewsetting'){
	$Id = FPostInt('ID');
	$data = $DB->getDRow("select * from t_sale_setting where ID = '{$Id}'");
	ajaxReturn($data, '', 1);
}else if($Clause == 'deletesetting'){
	admin_action_log();

	$Id = FPostInt('ID');
	$data = $DB->getDRow("select * from t_sale_setting where ID = '{$Id}'");
	if ($data) {
		if ($data['STATUS'] == '4') {//删除
			ajaxReturn($data, L('操作出错'), 1); //已经被修改过
		} else {
			$result = $DB->query("update t_sale_setting set `STATUS` = '4' where ID = '{$Id}'");
			ajaxReturn($data, L('操作成功'), 0);
		}
	}
	ajaxReturn($data, L('数据不存在'), 2);
}else if($Clause == 'savesetting'){
	admin_action_log();

	$data = array();
	$data['ACCOUNT'] = FPostStr('ACCOUNT');
	$data['FIXED'] = FPostStr('FIXED');
	$data['SCALE'] = floatval(FPostStr('SCALE'));

	$data['SYMBOL_TYPE'] = implode(',',$_POST['SYMBOL_TYPE']);
	if ($data['SYMBOL_TYPE'] == 'all_symbol') {
		$arr_symbol = $DB->getField("select type_name from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1", true);
	}

	$data['LEVEL'] = FPostInt('LEVEL');

	$data['GROUP_NAME'] = implode(',',$_POST['GROUP_NAME']);
	if ($data['GROUP_NAME'] == 'all_group') {
		$arr_group = $DB->getField("select `GROUP` from t_groups where server_id = '" . $DRAdmin['server_id'] . "'", true);
	}
	$data['MODEL_TYPE'] = FPostStr('MODEL_TYPE');
	$data['COMM_TYPE'] = FPostStr('COMM_TYPE');
	$data['NEXT_COMM_TYPE'] = FPostStr('NEXT_COMM_TYPE');
	$data['NEXT_COMM_VALUE'] = FPostStr('NEXT_COMM_VALUE');

	$data['MODEL_TYPE'] = FPostStr('MODEL_TYPE');
	$data['EQUAL_SCALE'] = FPostStr('EQUAL_SCALE');
	$data['UP_SCALE'] = FPostStr('UP_SCALE');
	$data['SERVER_ID'] = $DRAdmin['server_id'];
	$data["COMMISSION_PATTERN"] = FPostStr('COMMISSION_PATTERN');
	$enter = $data['FIXED'] + 0;
	if ((!is_float($data['FIXED']) && !is_numeric($data['FIXED'])) || $enter < 0) {
		this_error(L('请设置直接客户返佣标准，不能为负或者非浮点数字类型'));
	}
	if ($data['LEVEL'] <= 0) {
		if($data['MODEL_TYPE'] != 'direct'){
			this_error(L('请选择代理商级别'));
		}
	}
	if (!$data['SYMBOL_TYPE']) {
		this_error(L('请选择交易种类') . $data['SYMBOL_TYPE']);
	}
	if (!$data['GROUP_NAME']) {
		this_error(L('请选择MT分组'));
	}
	if (FPostStr('MODEL_TYPE') == 'direct') {
		if (FPostStr('NEXT_COMM_TYPE') == 'SCALE') {
			this_error(L('直接客户的间接客户提成，仅支持比例，不支持其他方式'));
		}
	}
	//todo 如果账号非空 需要验证等级
	//----------------------
	if (!empty($arr_symbol)) {
		$symbol_array = $arr_symbol;
	} else {
		$symbol_array = $_POST['SYMBOL_TYPE'];
	}

	if (!empty($arr_group)) {
		$group_array = $arr_group;
	} else {
		$group_array = $_POST['GROUP_NAME'];
	}

	foreach ($symbol_array as $symbolkey => $symbolval) {
		foreach ($group_array as $groupkey => $groupval) {
			$symboltype = $DB->getDRow("select * from t_type where type_name = '{$symbolval}' and status = 1 and server_id = '{$DRAdmin['server_id']}'"); //查询交易种类
			if (!$symboltype) {
				this_error(L('当前交易种类不存在') . "，" . $symboltype['type_name']);
			}
			$map['SYMBOL_TYPE'] = array('like', '%' . $symbolval . ',%');
			$map['LEVEL'] = FPostStr('LEVEL');
			$map['ACCOUNT'] = FPostStr('ACCOUNT');
			$map["STATUS"] = array("in", "1,3");
			$map['GROUP_NAME'] = array('like', '%' . $groupval . ',%');
			$map["MODEL_TYPE"] = FPostStr('MODEL_TYPE');
			$map["COMMISSION_PATTERN"] = FPostStr('COMMISSION_PATTERN');
			$map["SERVER_ID"] = $DRAdmin['server_id'];
			
			$mapStr = cz_where_to_str($map);
			$result = $DB->getDTable("select * from t_sale_setting {$mapStr}"); //查询上级配置是否存在
			if ($map["COMMISSION_PATTERN"] == 1)
				$exist = checkOuterExist($result, $symbolval, $groupval);
			elseif ($map["COMMISSION_PATTERN"] == 2) {
				if (checkRemind()) {
					this_error("代理模式中平级/越级返佣已存在，不能继续设置平级推荐奖励，如确实需要启用，请修改之前配置为0！");
				}
				if (checkRemindExist($result, $symbolval, $groupval)) {
					this_error("当前层级的推荐返佣已存在，不能继续设置！");
				}
			} else
				$exist = checkInnerExist($result, $symbolval, $groupval);
		}
	}

	if ($exist) {
		$model = "";
		if (FPostStr('MODEL_TYPE') == 'direct') {
			$model = L('直客');
		} elseif (FPostStr('MODEL_TYPE') == 'agent') {
			$model = L('代理');
		} elseif (FPostStr('MODEL_TYPE') == 'member') {
			$model = L('员工');
		}
		$pattern = "";
		if (FPostStr('COMMISSION_PATTERN') == 0) {
			$pattern = "内佣";
		} else if (FPostStr('COMMISSION_PATTERN') == 1) {
			$pattern = "外佣";
		} else {
			$pattern = "平级推荐";
		}
		this_error("交易种类'" . $symbolval . "'和分组'" . $groupval . "'的'" . $map['LEVEL'] . "'级" . $model . "的" . (FPostStr('COMMISSION_PATTERN') == 0 ? "内佣" : "外佣") . "返佣已存在！如果已经禁用，请启用即可！");
	}
	if (FPostStr('ID')) {
		$whereDB['ID'] = FPostStr('ID');
		$db = $DB->getDRow("select * from t_sale_setting where ID = '{$whereDB['ID']}'");
		if ($db != null) {
			$result = $DB->query("update t_sale_setting set `STATUS` = 2,UPDATE_TIME = '" . time() . "' where ID = '{$whereDB['ID']}'");
		}
	}
	//新增
	if ($arr_symbol) {
		$data['SYMBOL_TYPE'] = implode(',', $arr_symbol) . ',';
	} else {
		$data['SYMBOL_TYPE'] = $data['SYMBOL_TYPE'] . ',';
	}

	if ($arr_group) {
		$data['GROUP_NAME'] = implode(',', $arr_group) . ',';
	} else {
		$data['GROUP_NAME'] = $data['GROUP_NAME'] . ',';
	}
	$data['CREATE_TIME'] = time();
	$data['UPDATE_TIME'] = time();
	$data['STATUS'] = FPostStr('STATUS');
	$result = $DB->insert("t_sale_setting",$data);
	if ($result > 0) {
		this_success(L('操作成功'));
	} else {
		this_error(L('操作失败'));
	}
}

function checkInnerExist($list, $symbolval, $gropval) {
	$exist = false;
	foreach ($list as $k => $val) {
		if ($val['ID'] != FPostStr('ID')) {
			if ((strpos("," . $val['SYMBOL_TYPE'], "," . $symbolval . ',') !== false) && (strpos("," . $val['GROUP_NAME'], "," . $gropval . ',') !== false)) {
				$exist = true;
				break;
			}
		}
	}
	return $exist;
}

function checkRemindExist($list, $symbolval, $gropval) {
	$exist = false;
	foreach ($list as $k => $val) {
		if ($val['ID'] != FPostStr('ID')) {
			if ((strpos("," . $val['SYMBOL_TYPE'], "," . $symbolval . ',') !== false) && (strpos("," . $val['GROUP_NAME'], "," . $gropval . ',') !== false)) {
				$exist = true;
				break;
			}
		}
	}
	return $exist;
}

//检测之前是否存在平级和越级奖励，不能同时和平级推荐共存
function checkRemind() {
	global $DB;
	
	unset($map);
	$map["STATUS"] = 1;
	$map["MODEL_TYPE"] = "agent";
	$map["SERVER_ID"] = $DRAdmin['server_id'];
	$map['_string'] = ' (EQUAL_SCALE>0 or UP_SCALE>0)';
	
	$mapStr = cz_where_to_str($map);
	$result = $DB->getDTable("select * from t_sale_setting {$mapStr}"); //查询上级配置是否存在
	if (count($result) > 0)
		return true;
	return false;
}


function checkOuterExist($list, $symbolval, $gropval) {
	$exist = false;
	foreach ($list as $k => $val) {
		if ($val['ID'] != FPostStr('ID')) {
			if ((strpos("," . $val['SYMBOL_TYPE'], "," . $symbolval . ',') !== false) && (strpos("," . $val['GROUP_NAME'], "," . $gropval . ',') !== false) && $val['COMM_TYPE'] == FPostStr('COMM_TYPE')) {
				$exist = true;
				break;
			}
		}
	}
	return $exist;
}


$LoadCSSArr = array();
require_once('header.php');






//-------------------------------------------

$where = 'setting.status in (1,3) and  setting.SERVER_ID =' . $DRAdmin['server_id'] . '';
if ($_REQUEST['Q_SYMBOL_TYPE']) {
	$where = $where . " and  CONCAT(',',setting.SYMBOL_TYPE) like '%," . $_REQUEST['Q_SYMBOL_TYPE'] . ",%'";
}
if ($_REQUEST['Q_GROUP_NAME']) {
	$where = $where . " and  CONCAT(',',setting.GROUP_NAME) like '%," . $_REQUEST['Q_GROUP_NAME'] . ",%'";
}
$agentsql = $where . " and  setting.MODEL_TYPE='agent' and setting.COMMISSION_PATTERN=0  and setting.SERVER_ID = " . $DRAdmin['server_id'];
//查询返佣设置
$list = $DB->getDTable("select setting.* from t_sale_setting setting where {$agentsql} order by setting.LEVEL,setting.GROUP_NAME asc");
$ranks = $DB->getDTable("select * from t_ib_rank where server_id = '{$DRAdmin['server_id']}' and status = 1 order by rank asc");

$step = 0;
foreach ($list as $key => $value) {
	$step++;
	$exist_alias = false;
	foreach ($ranks as $key1 => $value1) {
		if ($value['LEVEL'] == $value1['rank'] && $value['MODEL_TYPE'] == $value1['model_type']) {
			$list[$step - 1]['LEVEL_NAME'] = $value1['rank_name'];
			$exist_alias = true;
			break;
		}
	}
	if (!$exist_alias) {
		$list[$step - 1]['LEVEL_NAME'] = $value['LEVEL'] . ' ' . L('级') . ' ' . L('代理');
	}
}

$outagentsql = $where . " and  setting.MODEL_TYPE='agent' and setting.COMMISSION_PATTERN=1 and setting.SERVER_ID = " . $DRAdmin['server_id'];
//外佣
$outlist = $DB->getDTable("select setting.* from t_sale_setting setting where {$outagentsql} order by setting.LEVEL,setting.GROUP_NAME asc");
$step = 0;
foreach ($outlist as $key => $value) {
	$step++;
	$exist_alias = false;
	foreach ($this->ranks as $key1 => $value1) {
		if ($value['LEVEL'] == $value1['rank'] && $value['MODEL_TYPE'] == $value1['model_type']) {
			$outlist[$step - 1]['LEVEL_NAME'] = $value1['rank_name'];
			$exist_alias = true;
			break;
		}
	}
	if (!$exist_alias) {
		$outlist[$step - 1]['LEVEL_NAME'] = $value['LEVEL'] . ' ' . L('级') . ' ' . L('代理');
	}
}

//代理推荐返佣
$remindagentsql = $where . " and  setting.MODEL_TYPE='agent'  and setting.COMMISSION_PATTERN=2  and setting.SERVER_ID = " . $DRAdmin['server_id'];

$remindlist = $DB->getDTable("select setting.* from t_sale_setting setting where {$remindagentsql} order by setting.LEVEL,setting.GROUP_NAME asc");

$directsql = $where . " and  setting.MODEL_TYPE='direct' and setting.SERVER_ID = " . $DRAdmin['server_id'];
$directlist = $DB->getDTable("select setting.* from t_sale_setting setting where {$directsql} order by setting.LEVEL,setting.GROUP_NAME asc");

$membersql = $where . " and  setting.MODEL_TYPE='member' and setting.SERVER_ID = " . $DRAdmin['server_id'];
$memberlist = $DB->getDTable("select setting.* from t_sale_setting setting where {$membersql} order by setting.LEVEL,setting.GROUP_NAME asc");

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
		$memberlist[$step - 1]['LEVEL_NAME'] = L($value['LEVEL'] . '级员工');
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
															echo '<option value="' , $val['id'] , '">' , $val['type_name'] , '</option>';
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
														echo '<a href="#nolink" onclick="addsetting_click(this)" class="btn btn-danger"><i class="md md-add"></i>' , L('添加设置') , '</a> ';
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
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                        
<div class="ibox-content">
    <span class="help-block m-b-none"></span>
    <ul class="nav nav-tabs mb-sm-2">
        <li class="nav-item" id="agentli"><a class="nav-link active" data-toggle="tab" href="#tab-1" aria-expanded="false"><?php echo L('代理奖励'); ?></a></li>
        <li class="nav-item" id="directli"><a class="nav-link" data-toggle="tab" href="#tab-2" aria-expanded="true"><?php echo L('直客奖励'); ?></a></li>
        <li class="nav-item" id="memberli"><a class="nav-link" data-toggle="tab" href="#tab-3" aria-expanded="true"><?php echo L('员工奖励'); ?></a></li>                         
    </ul>
    <div class="tab-content">
    <div id="tab-1" class="tab-pane active">
        <ul class="nav nav-tabs mb-sm-2">
            <li class="nav-item" id="subagent1"></i><a class="nav-link active" data-toggle="tab" href="#subtab-1" aria-expanded="false"> <?php echo L('内佣'); ?></a></li>
            <li class="nav-item" id="subagent2"><a class="nav-link" data-toggle="tab" href="#subtab-2" aria-expanded="true"><?php echo L('外佣'); ?></a></li>
            <li class="nav-item" id="subagent3"><a class="nav-link" data-toggle="tab" href="#subtab-3" aria-expanded="true"><?php echo L('平级推荐'); ?></a></li>
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
                <tbody id="tablebody">
                <?php
                foreach($list as $j=>$vo){
				?>
                  <tr class="gradeX" id="tr_<?php echo $vo['ID']; ?>" style="word-break:break-word;">
                        <td><?php echo $j;?></td>
                        <td>
                            <?php
                            if($vo['ACCOUNT'] != '0'){
                                echo L('仅限');
								echo $vo['LEVEL'];
								echo L('级代理下');
								echo $vo['ACCOUNT'];
								echo L('账户');
							}else{
                                echo $vo['LEVEL_NAME'];
                            }
                            ?>                                
                        </td>
                        <td style="word-break:break-all;">
                        <?php
                        if($vo['SYMBOL'] == 'ALL'){
							echo L('所有货币对');
						}else{
							echo $vo['SYMBOL_TYPE'];
						}
						?>
                        </td>
                        <td style="word-wrap : break-word"><?php echo $vo['GROUP_NAME']; ?></td>
                        <td>
                        <?php
						echo L('直接客户') , '：';
                        if($vo['COMM_TYPE'] == '' || $vo['COMM_TYPE'] == 'FIXED'){
                     		echo '$' , $vo['FIXED'] , '/' , L('手');
                        }else if($vo['COMM_TYPE'] == 'SCALE'){
                       		echo L('交易量') , '*' , $vo['FIXED'] , '%';
                        }else if($vo['COMM_TYPE'] == 'POINT'){
                        	echo $vo['FIXED'] , '/pip/' , L('手');
                        }else if($vo['COMM_TYPE'] == 'WIN'){
                       		echo L('盈利额') , '*' , $vo['FIXED'] , '%';
						}
                        echo '<br/>';
						
						echo L('间接客户') , '：';
						if($vo['NEXT_COMM_TYPE'] == '' || $vo['NEXT_COMM_TYPE'] == 'DIFFER'){
							if($vo['COMM_TYPE'] == '' || $vo['COMM_TYPE'] == 'FIXED'){
								echo '$';
							}else if($vo['COMM_TYPE'] == 'SCALE'){
								echo '%';
							}else if($vo['COMM_TYPE'] == 'POINT'){
								echo 'pip $';
							}else if($vo['COMM_TYPE'] == 'WIN'){
								echo '%';
							}
							echo $vo['FIXED'] , '-' , L('下级标准');
						}else if($vo['NEXT_COMM_TYPE'] == 'FIXED'){
                        	echo '$' , $vo['NEXT_COMM_VALUE'] , '/' , L('手');
                        }else if($vo['NEXT_COMM_TYPE'] == 'SCALE'){
                        	echo L('返佣总额') , '*' , $vo['NEXT_COMM_VALUE'] , '%';
                        }else if($vo['NEXT_COMM_TYPE'] == 'POINT'){
                            echo '$' , $vo['NEXT_COMM_VALUE'] , '/pip/' , L('手');
                        }else if($vo['NEXT_COMM_TYPE'] == 'WIN'){
							echo L('盈利额') , '*' , $vo['NEXT_COMM_VALUE'] , '%';
						}
                        echo '<br/>';
						echo L('平级奖励') , '：' , L('团队返佣额') , '*' , $vo['EQUAL_SCALE'] , '%<br/>';
                        echo L('越级奖励') , '：' , L('团队返佣额') , '*' , $vo['UP_SCALE'] , '%';
                        echo '</td>';
                        echo '<td>' , date('Y-m-d H:i:s',$vo['CREATE_TIME']) , '</td>';
                        echo '<td id="state_' , $vo['ID'] , '">';
                        if($vo['STATUS'] == 1){
                            echo L('启用');
                        }else{
                            echo L('禁用');
                        }
                        echo '</td>';
                        echo '<td class="center">';
                       	echo '<a class="btn btn-primary btn-xs modifysetting" val="' , $vo['ID'] , '" type="button" data-toggle="modal" href="#nolink">' , L('修改') , '</a> ';
                       	echo '<a class="btn btn-danger btn-red-cz btn-xs delete" type="button" rel="' , $vo['ID'] , '" href="#nolink">' , L('删除') , '</a> ';
						if($vo['STATUS'] == '1'){
							echo '<a class="btn btn-danger btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('禁用') , '</a> ';
						}else{
							echo '<a class="btn btn-primary btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('启用') , '</a>';
						}
						?>
                        </td>
                    </tr>   
                <?php
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
                        <th><?php echo L('外佣标准'); ?></th>
                        <th><?php echo L('设置时间'); ?></th>
                        <th><?php echo L('状态'); ?></th>
                        <th><?php echo L('操作'); ?></th>
                    </tr>
                </thead>
                <tbody id="subtable">
                <?php
                foreach($outlist as $j=>$vo){
				?>
                  <tr class="gradeX" id="tr_<?php echo $vo['ID']; ?>" style="word-break:break-word;">
                        <td><?php echo $j+1;?></td>
                        <td>
                            <?php
                            if($vo['ACCOUNT'] != '0'){
                                echo L('仅限');
								echo $vo['LEVEL'];
								echo L('级代理下');
								echo $vo['ACCOUNT'];
								echo L('账户');
							}else{
                                echo $vo['LEVEL_NAME'];
                            }
                            ?>
                        </td>
                        <td style="word-break:break-all;">
                        <?php
                        if($vo['SYMBOL'] == 'ALL'){
							echo L('所有货币对');
						}else{
							echo $vo['SYMBOL_TYPE'];
						}
						?>
                        </td>
                        <td style="word-wrap : break-word"><?php echo $vo['GROUP_NAME']; ?></td>
                        <td>
                        <?php
						echo L('直接客户') , '：';
                        if($vo['COMM_TYPE'] == '' || $vo['COMM_TYPE'] == 'FIXED'){
                     		echo '$' , $vo['FIXED'] , '/' , L('手');
                        }else if($vo['COMM_TYPE'] == 'SCALE'){
                       		echo L('交易量') , '*' , $vo['FIXED'] , '%';
                        }else if($vo['COMM_TYPE'] == 'POINT'){
                        	echo $vo['FIXED'] , '/pip/' , L('手');
                        }else if($vo['COMM_TYPE'] == 'WIN'){
                       		echo L('盈利额') , '*' , $vo['FIXED'] , '%';
						}
						?>
                        </td>
                        <td><?php echo date('Y-m-d H:i:s',$vo['CREATE_TIME']);?></td>
                        <td id="state_<?php echo $vo['ID']; ?>" >
                        <?php
                        if($vo['STATUS'] == 1){
                            echo L('启用');
                        }else{
                            echo L('禁用');
                        }
						?>
                        </td>
                        <td class="center">
                        <?php
                       	echo '<a class="btn btn-primary btn-xs modifysetting" val="' , $vo['ID'] , '" type="button" data-toggle="modal" href="#nolink">' , L('修改') , '</a> ';
                       	echo '<a class="btn btn-danger btn-red-cz btn-xs delete" type="button" rel="' , $vo['ID'] , '" href="#nolink">' , L('删除') , '</a> ';
						if($vo['STATUS'] == '1'){
							echo '<a class="btn btn-danger btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('禁用') , '</a> ';
						}else{
							echo '<a class="btn btn-primary btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('启用') , '</a>';
						}
						?>
                        </td>
                    </tr>   
                <?php
				}
				?>
                </tbody>
            </table>
        </div>
        <div id="subtab-3" class="tab-pane">
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
                <tbody id="tablebody">
                <?php
                foreach($remindlist as $j=>$vo){
				?>
                  <tr class="gradeX" id="tr_<?php echo $vo['ID']; ?>" style="word-break:break-word;">
                        <td><?php echo $j+1;?></td>
                        <td>
                        	<?php
                            if($vo['ACCOUNT'] != '0'){
                                echo L('仅限');
								echo $vo['LEVEL'];
								echo L('级代理下');
								echo $vo['ACCOUNT'];
								echo L('账户');
							}else{
                                echo $vo['LEVEL'] , L('级');
                            }
                            ?>   
                        </td>
                        <td style="word-break:break-all;">
                        <?php
                        if($vo['SYMBOL'] == 'ALL'){
							echo L('所有货币对');
						}else{
							echo $vo['SYMBOL_TYPE'];
						}
						?>
                        </td>
                        <td style="word-wrap : break-word"><?php echo $vo['GROUP_NAME']; ?></td>
                        <td>
                        $<?php echo $vo['FIXED']; ?>/<?php echo L('手'); ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i:s',$vo['CREATE_TIME']);?></td>
                        <td id="state_<?php echo $vo['ID']; ?>" >
                        <?php
                        if($vo['STATUS'] == 1){
                            echo L('启用');
                        }else{
                            echo L('禁用');
                        }
						?>
                        </td>
                        <td class="center">
                        <?php
						echo '<a class="btn btn-primary btn-xs modifysetting" val="' , $vo['ID'] , '" type="button" data-toggle="modal" href="#nolink">' , L('修改') , '</a> ';
						echo '<a class="btn btn-danger btn-red-cz btn-xs delete" type="button" rel="' , $vo['ID'] , '" href="#nolink">' , L('删除') , '</a> ';
						if($vo['STATUS'] == '1'){
							echo '<a class="btn btn-danger btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('禁用') , '</a> ';
						}else{
							echo '<a class="btn btn-primary btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('启用') , '</a>';
						}
						?>
                        </td>
                    </tr>   
                <?php
				}
				?>
            </table>
        </div>
    </div>
    </div>
    <div id="tab-2" class="tab-pane">
        <table class="table table-hover  table-bordered  " >
            <thead>
                <tr>
                    <th>No.</th>
                    <th><?php echo L('推荐层级'); ?></th>
                    <th width="20%"><?php echo L('交易种类'); ?></th>
                    <th width="20%"><?php echo L('MT分组'); ?></th>
                    <th><?php echo L('直接客户'); ?></th>
                    <th><?php echo L('设置时间'); ?></th>
                    <th><?php echo L('状态'); ?></th>
                    <th><?php echo L('操作'); ?></th>
                </tr>
            </thead>
            <tbody id="tablebody">
            <?php
			foreach($directlist as $j=>$vo){
			?>
              <tr class="gradeX" id="tr_<?php echo $vo['ID']; ?>" style="word-break:break-word;">
                    <td><?php echo $j+1;?></td>
                     <td>
                     	<?php
						if($vo['ACCOUNT'] != '0'){
							echo L('仅限');
							echo $vo['LEVEL'];
							echo L('级代理下');
							echo $vo['ACCOUNT'];
							echo L('账户');
						}else{
                            if($vo['MODEL_TYPE'] == 'direct'){
								echo $vo['LEVEL'] . ' ' . L('级');
                            }else{
                            	echo $vo['LEVEL'] . ' ' . L('级') . ' ' . L('代理');
                            }
						}
						?> 
                    </td>
                    <td style="word-break:break-all;">
                    <?php
					if($vo['SYMBOL'] == 'ALL'){
						echo L('所有货币对');
					}else{
						echo $vo['SYMBOL_TYPE'];
					}
					?>
                    </td>
                    <td style="word-wrap : break-word"><?php echo $vo['GROUP_NAME']; ?></td>
                    
                    <td style="word-break:break-all;"> $<?php echo $vo['FIXED']; ?>/<?php echo L('手'); ?></td>
                    <td><?php echo date('Y-m-d H:i:s',$vo['CREATE_TIME']);?></td>
                    <td id="state_<?php echo $vo['ID']; ?>" >
                    <?php
					if($vo['STATUS'] == 1){
						echo L('启用');
					}else{
						echo L('禁用');
					}
					?>
                    </td>
                    <td class="center">
                    <?php
                       	echo '<a class="btn btn-primary btn-xs modifysetting" val="' , $vo['ID'] , '" type="button" data-toggle="modal" href="#nolink">' , L('修改') , '</a> ';
                       	echo '<a class="btn btn-danger btn-red-cz btn-xs delete" type="button" rel="' , $vo['ID'] , '" href="#nolink">' , L('删除') , '</a> ';
						if($vo['STATUS'] == '1'){
							echo '<a class="btn btn-danger btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('禁用') , '</a> ';
						}else{
							echo '<a class="btn btn-primary btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('启用') , '</a>';
						}
						?>
                    </td>
                </tr>   
            <?php
			}
			?>
            </tbody>
        </table>
     </div>
    <div id="tab-3" class="tab-pane ">
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
        <tbody id="tablebody">
        <?php
		foreach($memberlist as $j=>$vo){
		?>
          <tr class="gradeX" id="tr_<?php echo $vo['ID']; ?>" style="word-break:break-word;">
                <td><?php echo $j+1;?></td>
                <td>
                	<?php
					if($vo['ACCOUNT'] != '0'){
						echo L('仅限');
						echo $vo['LEVEL'];
						echo L('级代理下');
						echo $vo['ACCOUNT'];
						echo L('账户');
					}else{
						echo $vo['LEVEL_NAME'];
					}
					?>  
                </td>
                <td style="word-break:break-all;">
                <?php
				if($vo['SYMBOL'] == 'ALL'){
					echo L('所有货币对');
				}else{
					echo $vo['SYMBOL_TYPE'];
				}
				?>
                </td>
                <td style="word-wrap : break-word"><?php echo $vo['GROUP_NAME']; ?></td>
                <td>
                <?php
				echo L('直接客户') , '：';
				if($vo['COMM_TYPE'] == '' || $vo['COMM_TYPE'] == 'FIXED'){
					echo '$' , $vo['FIXED'] , '/' , L('手');
				}else if($vo['COMM_TYPE'] == 'SCALE'){
					echo L('交易量') , '*' , $vo['FIXED'] , '%';
				}else if($vo['COMM_TYPE'] == 'POINT'){
					echo $vo['FIXED'] , '/pip/' , L('手');
				}else if($vo['COMM_TYPE'] == 'WIN'){
					echo L('盈利额') , '*' , $vo['FIXED'] , '%';
				}
				echo '<br/>';
				
				echo L('间接客户') , '：';
				if($vo['NEXT_COMM_TYPE'] == '' || $vo['NEXT_COMM_TYPE'] == 'DIFFER'){
					if($vo['COMM_TYPE'] == '' || $vo['COMM_TYPE'] == 'FIXED'){
						echo '$';
					}else if($vo['COMM_TYPE'] == 'SCALE'){
						echo '%';
					}else if($vo['COMM_TYPE'] == 'POINT'){
						echo $vo['FIXED'] , 'pt';
					}else if($vo['COMM_TYPE'] == 'WIN'){
						echo '%';
					}
					echo $vo['FIXED'] , '-' , L('下级标准');
				}else if($vo['NEXT_COMM_TYPE'] == 'FIXED'){
					echo '$' , $vo['NEXT_COMM_VALUE'] , '/' , L('手');
				}else if($vo['NEXT_COMM_TYPE'] == 'SCALE'){
					echo L('返佣总额') , '*' , $vo['NEXT_COMM_VALUE'] , '%';
				}else if($vo['NEXT_COMM_TYPE'] == 'POINT'){
					echo '$' , $vo['NEXT_COMM_VALUE'] , '/pip/' , L('手');
				}else if($vo['NEXT_COMM_TYPE'] == 'WIN'){
					echo L('盈利额') , '*' , $vo['NEXT_COMM_VALUE'] , '%';
				}
				echo '<br/>';
				echo L('平级奖励') , '：' , L('团队返佣额') , '*' , $vo['EQUAL_SCALE'] , '%<br/>';
				echo L('越级奖励') , '：' , L('团队返佣额') , '*' , $vo['UP_SCALE'] , '%';
				?>
                </td>
                <td><?php echo date('Y-m-d H:i:s',$vo['CREATE_TIME']);?></td>
                <td id="state_<?php echo $vo['ID']; ?>" >
                <?php
				if($vo['STATUS'] == 1){
					echo L('启用');
				}else{
					echo L('禁用');
				}
				?>
                </td>
                <td class="center">
                <?php
				echo '<a class="btn btn-primary btn-xs modifysetting" val="' , $vo['ID'] , '" type="button" data-toggle="modal" href="#nolink">' , L('修改') , '</a> ';
				echo '<a class="btn btn-danger btn-red-cz btn-xs delete" type="button" rel="' , $vo['ID'] , '" href="#nolink">' , L('删除') , '</a> ';
				if($vo['STATUS'] == '1'){
					echo '<a class="btn btn-danger btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('禁用') , '</a> ';
				}else{
					echo '<a class="btn btn-primary btn-xs forbidenreopen " rel="' , $vo['ID'] , '" type="button" href="#nolink">' , L('启用') , '</a>';
				}
				?>
                </td>
            </tr>   
        <?php
		}
		?>
    </table>
    </div>
    </div>
</div>
                    
                    
                    
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                        

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div> 


                        
                        <!-- end row-->


                    </div> <!-- container -->








        <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('佣金设置'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                     <form  id='settingform' name='settingform'>
                        <input type="hidden" name="STATUS" value="1" />
                        <input type="hidden" name="ID" ID="ID" value="" />
                        <div class="modal-body">
                            <div class="row"> 
                                <div class="col-md-6"> 
                                     <div class="form-group"><label><?php echo L('返佣模式'); ?>：</label> 
                                        <select name='MODEL_TYPE' id="MODEL_TYPE" class='form-control m-b'>
                                            <option value='agent'><?php echo L('代理奖励'); ?></option>
                                            <option value='direct'><?php echo L('直客奖励'); ?></option>
                                            <option value='member'><?php echo L('员工奖励'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6"> 
                                    <div class="form-group"><label><?php echo L('等级/层级'); ?>：</label> 
                                        <select class="form-control m-b" id="LEVEL" name="LEVEL" >
                                            <?php
                                            for($i=1;$i<=C('MAX_LEVEL');$i++){
												echo '<option value="' , $i , '">' , L($i.'级') , '</option>';
											}
                                            ?> 
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                             <div class="row" id="PATTERN_DIV"> 
                            
                                <div class="col-md-12"> 
                                    <div class="form-group"><label><?php echo L('返佣模式'); ?>：</label> 
                                        <select name='COMMISSION_PATTERN' id="COMMISSION_PATTERN"  data-placeholder="<?php echo L('请选择返佣模式'); ?>"  class='form-control m-b' >
                                            <option value='0'><?php echo L('内佣'); ?>(<?php echo L('平台统一的返佣标准'); ?>)</option>
                                            <option value='1'><?php echo L('外佣'); ?>(<?php echo L('特殊组加点/加佣额外返佣标准'); ?>)</option>
                                            <option value='2'><?php echo L('平级推荐'); ?>(<?php echo L('推荐上级代理级别小于等于推荐的代理返佣标准'); ?>)</option>
                                        </select>
                                    </div>
                               </div>
                            
                            </div>
                           
                            
                            
                            <div class="row"> 
                            
                                <div class="col-md-12"> 
                                    <div class="form-group"><label><?php echo L('交易种类'); ?>：</label> 
                                        <select name='SYMBOL_TYPE[]' id="SYMBOL_TYPE"  data-placeholder="<?php echo L('请选择种类'); ?>" class='chosen-select'  multiple >
                                            <option value='all_symbol'><?php echo L('全部'); ?></option>
                                            <?php
											foreach($types as $j=>$vo){
											?>
                                                 <option value='<?php echo $vo['type_name']; ?>'><?php echo $vo['type_name']; ?></option>
                                            <?php
											}
											?>
                                        </select>
                                    </div>
                               </div>
                               <div class="col-md-12"> 
                                    <div class="form-group"><label><?php echo L('MT分组'); ?>：</label> 
                                        <select name='GROUP_NAME[]' id="GROUP_NAME" data-placeholder="<?php echo L('请选择分组'); ?>" class='chosen-select'  multiple  >
                                            <option value='all_group'><?php echo L('全部'); ?></option>
                                             <?php
											 foreach($groups as $j=>$vo){
											 ?>
                                            <option value='<?php echo $vo['group']; ?>'><?php echo $vo['group']; ?></option>
                                            <?php
											 }
											 ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <!-- <div class="col-md-6"> 
                                    <div class="form-group"><label>直接客户返佣方式：</label> 
                                        <p class="form-control-static"> $，每手固定金额</p>
                                 
                                        <input type="hidden" name="COMM_TYPE" value="FIXED"></input>
                                        <select class="form-control m-b" id="COMM_TYPE" name="COMM_TYPE" >
                                             <option value="FIXED">$，每手固定金额</option>
                                             <option value="POINT">pt，点值</option>
                                             <option value="SCALE">%，百分比</option>
                                        </select>
                                    </div>
                                </div> -->
                                <div class="col-md-6"> 
                                     <div class="form-group"><label><?php echo L('直接客户返佣标准'); ?>：</label> 
                                        <select name='COMM_TYPE' id="COMM_TYPE" class='form-control m-b'>
                                                <option value='FIXED'>$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                <option value='SCALE'>%，<?php echo L('交易量百分比'); ?></option>
                                                <option value='POINT'>pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                <option value='WIN'>%，<?php echo L('盈利百分比'); ?></option>
                                        </select>
                                    </div>
                                </div>
                               <div class="col-md-6">    
                                    <label><?php echo L('数值'); ?>：</label>
                                    <div class="input-group m-b"><div class="input-group-prepend"><span class="input-group-text" id="FIXED_ADDON">$</span></div>
                                        <input type="text" class="form-control" name="FIXED" id="FIXED" placeholder="<?php echo L('请输入返佣标准数值'); ?>">
                                    </div>
                                 </div>
                            </div>
                            
                            <div style="display: none;">
                            <label><?php echo L('特殊账户'); ?>：</label>
                            <div class="input-group m-b"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user"></i></span></div>
                                <input type="text" class="form-control" name="ACCOUNT" id="ACCOUNT"> 
                            </div>
                            </div>
                            <div class="row" id="agent_div"> 
                                <div class="col-md-6"> 
                                    <div class="form-group"><label><?php echo L('间接客户返佣标准'); ?>：</label> 
                                        <select class="form-control m-b" id="NEXT_COMM_TYPE" name="NEXT_COMM_TYPE" >
                                             <option value="DIFFER">-，<?php echo L('上级标准'); ?>-<?php echo L('下级标准'); ?></option>
                                             <option value="FIXED">$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                             <option value="SCALE">%，<?php echo L('下级返佣总额百分比'); ?></option>
                                             <option value="POINT">pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                             <option value='WIN'>%，<?php echo L('盈利百分比'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" id="NEXT_COMM_VALUE_DIV" style="display:none"> 
                                    <label><?php echo L('数值'); ?>：</label>
                                    <div class="input-group m-b"><div class="input-group-prepend"><span class="input-group-text" id="NEXT_COMM_VALUE_ADDON">$</span></div>
                                        <input type="text" class="form-control" name="NEXT_COMM_VALUE" id="NEXT_COMM_VALUE" placeholder="<?php echo L('请输入间接客户的返佣标准'); ?>"> 
                                    </div>
                                </div>
                            </div>
                      
                             <div class="row" id="equal_scale_div"> 
                                <div class="col-md-6"> 
                                     <div class="form-group"><label><?php echo L('平级奖励'); ?>：</label> 
                                       <div class="input-group m-b"><div class="input-group-prepend"><span class="input-group-text" id="FIXED_ADDO">%</span></div>
                                        <input type="text" class="form-control" name="EQUAL_SCALE" id="EQUAL_SCALE" value="0" placeholder="<?php echo L('获取团队返佣总金额的比例'); ?>">
                                        </div>
                                    </div>
                                    <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('平级奖励'); ?>：<?php echo L('当接收返佣客户等级<=下级客户等级，获取下级团队内佣总额的百分比'); ?>。</span>
                                </div>
                                <div class="col-md-6"> 
                                     <div class="form-group"><label><?php echo L('越级奖励'); ?>：</label> 
                                       <div class="input-group m-b"><div class="input-group-prepend"><span class="input-group-text" id="FIXED_ADD1">%</span></div>
                                        <input type="text" class="form-control" name="UP_SCALE" id="UP_SCALE" value="0" placeholder="<?php echo L('获取团队返佣总金额的比例'); ?>">
                                        </div>
                                    </div>
                                    <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('越级奖励'); ?>，<?php echo L('当接收返佣客户等级>下级客户等级，获取下级团队内佣总额的百分比'); ?>。</span>
                                </div>
                                 
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type='hidden' name='inlogin' value=''/>
                            <button type="button" class="btn btn-white" id='closeout' data-dismiss="modal"><?php echo L('关闭'); ?></button>
                            <button type="button" class="btn btn-primary" id='savesetting' ><?php echo L('确认'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--弹出层-->
        
        
        <!--
        <div class="modal inmodal" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('是否确认删除'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                        <div class="modal-body">
                         <?php echo L('确定要删除改记录吗'); ?>？
                        </div>
                        <div class="modal-footer">
                            <input type='hidden' name='inlogin' value=''/>
                            <button type="button" class="btn btn-white" id='closeout' data-dismiss="modal"><?php echo L('取消'); ?></button>
                            <button type="button" class="btn btn-primary" id='savesetting' ><?php echo L('确认'); ?></button>
                        </div>
                </div>
            </div>
        </div>
        -->




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
         
		 $(document).on("click","#savesetting",function(){
         //$("#savesetting").click(function() {
            $(this).attr('disabled', "disabled");
            var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveSetting";
            document.cookie="modeltype="+$("#MODEL_TYPE").val();
            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.settingform.reset();
                    $("#closeout").click();
					setTimeout(function(){document.location.reload();},800);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
         function addsetting_click(this_) {
            document.settingform.reset();
            if(modeltype)
                $("#MODEL_TYPE").val(modeltype);
            if(modeltype=='direct'){
                $('#agent_div').hide();
            }else{
                $('#agent_div').show();
            }
            if ($(".chosen-select").hasClass('chzn-done'))
                $(".chosen-select").chosen('destroy');
            $(".chosen-select").chosen( {width: "100%"});
            chose_mult_set_ini('#GROUP_NAME','');
            chose_mult_set_ini('#SYMBOL_TYPE','');
            $("#EQUAL_SCALE").val('0');
            $("#UP_SCALE").val('0');
			
			$("#MODEL_TYPE").change();
            
            $('#myModal').modal('toggle'); 
        };

        $("#SYMBOL_TYPE").change(function(){
           var symbol = $("#SYMBOL_TYPE  option:selected").val();
            if(symbol=='all_symbol'){
                chose_mult_set_ini('#SYMBOL_TYPE','all_symbol');
            }
        })

        $("#GROUP_NAME").change(function(){
           var group = $("#GROUP_NAME  option:selected").val();
            if(group=='all_group'){
                chose_mult_set_ini('#GROUP_NAME','all_group');
            }
        })
        
		$(document).on("click",".modifysetting",function(){
        //$(".modifysetting").click(function() {
            $(this).attr('disabled', "disabled");
            var _this=$(this);
            var form = $(this).closest('form');
            document.settingform.reset();
            var url = "?clause=viewSetting";
            var ID =  $(this).attr('val');
            $.post(url, "ID="+ID, function(data) {
                if (data.status) {
                    $("#ID").val(data.data.ID);
                    $("#MODEL_TYPE").val(data.data.MODEL_TYPE);
                    if(data.data.MODEL_TYPE=='direct'){
                        $('#agent_div').hide();
                    }else{
                        $('#agent_div').show();
                    }
                    $("#LEVEL").val(data.data.LEVEL);
                    $("#ACCOUNT").val(data.data.ACCOUNT);
                    $("#FIXED").val(data.data.FIXED);
                    $("#SCALE").val(data.data.SCALE);
                    $("#COMMISSION_PATTERN").val(data.data.COMMISSION_PATTERN);
                    changePattern(data.data.COMMISSION_PATTERN)
                    var COMM_TYPE  = data.data.COMM_TYPE;
                    if(!COMM_TYPE) COMM_TYPE='FIXED';
                    var NEXT_COMM_TYPE  = data.data.NEXT_COMM_TYPE;
                    if(!NEXT_COMM_TYPE) NEXT_COMM_TYPE='DIFFER';
                    
                    changeComm(COMM_TYPE,'COMM_TYPE','FIXED',data.data.FIXED);
                    changeComm(NEXT_COMM_TYPE,'NEXT_COMM_TYPE','NEXT_COMM_VALUE',data.data.NEXT_COMM_VALUE);
                    if ($(".chosen-select").hasClass('chzn-done'))
                        $(".chosen-select").chosen('destroy');
                    $(".chosen-select").chosen( {width: "100%"});
                    //console.log(data.data.SYMBOL_TYPE);
                    chose_mult_set_ini('#SYMBOL_TYPE',data.data.SYMBOL_TYPE);
                    chose_mult_set_ini('#GROUP_NAME',data.data.GROUP_NAME);
                    
                    $("#EQUAL_SCALE").val(data.data.EQUAL_SCALE);
                    $("#UP_SCALE").val(data.data.UP_SCALE);
                    
                    $('#myModal').modal('toggle');
					
					$("#MODEL_TYPE").change();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
		$(document).on("click","#closeout,.close",function(){
        //$("#closeout,.close").click(function(){
             $("#myModal").hide();
        });
        
        //去除数组中的空值
        function trimSpace(array){
             for(var i = 0 ;i<array.length;i++)
             {
                 if(array[i] == "" || typeof(array[i]) == "undefined")
                 {
                      array.splice(i,1);
                      i= i-1;
                      
                 }
             }
             return array;
        }
 
        
        function chose_mult_set_ini(select, values) {
			//console.log(values);
			
            var arr = values.split(',');
            var length = arr.length;
            var value = '';
            //console.log(arr);
            newarr = trimSpace(arr);
            //console.log(newarr);
            $(select).val(newarr);
            $(select).trigger("chosen:updated");
            //$(select).trigger("chosen:updated");  
        }
        
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
        
        
        $("#NEXT_COMM_TYPE").change(function(){
            val = $(this).val();
            changeComm(val,'NEXT_COMM_TYPE','NEXT_COMM_VALUE','0');
        });
        
        function changeComm(commtype,typeid,valid,val){
            if(commtype=='DIFFER'){
                $('#'+valid+"_ADDON").text('$');
                $('#'+valid+"_DIV").hide();
            }else if(commtype=='SCALE'){
                $('#'+valid+"_ADDON").text('%');
                $('#'+valid+"_DIV").show();
            }else if(commtype=='FIXED'){
                $('#'+valid+'_ADDON').text('$');
                $('#'+valid+"_DIV").show();
            }else if(commtype=='POINT'){
                $('#'+valid+"_ADDON").text('$');
                $('#'+valid+"_DIV").show();
            }else if(commtype=='WIN'){
                $('#'+valid+"_ADDON").text('%');
                $('#'+valid+"_DIV").show();
			}
            $('#'+typeid).val(commtype);
            $('#'+valid).val(val);
        }
        
        $("#COMM_TYPE").change(function(){
            val = $(this).val();
            changeComm(val,'COMM_TYPE','FIXED','0');
        });
        

        $("#MODEL_TYPE").change(function(){
            var val = $(this).val();
            document.cookie="modeltype="+val;
            if(val=='direct'){
                $('#agent_div,#equal_scale_div,#PATTERN_DIV').hide();
                $('#EQUAL_SCALE').val("0");
                $("#UP_SCALE").val("0");
				
				//直客有0级=自己拿佣
				var czLv0Option = $("#LEVEL").find("option[value='0']");
				if(czLv0Option.length == 0){
					$("#LEVEL").prepend("<option value='0'><?php echo L('0级');?></option>");
				}
            }else{
                $('#agent_div,#equal_scale_div,#PATTERN_DIV').show();
                if($('#COMMISSION_PATTERN').val()=='1'){
                	  $('#equal_scale_div,#agent_div').hide();
                }
                
				//非直客，必须从1级开始
				$("#LEVEL option[value='0']").remove();
            } 
        });
        
        $("#COMMISSION_PATTERN").change(function(){
            var val = $(this).val();
            changePattern(val);
        });
        
        function changePattern(val){
        	if(val=='1'){ 
                $('#equal_scale_div,#agent_div').hide();
                $('#EQUAL_SCALE').val("0");
                $("#UP_SCALE").val("0");
                removeCommOption(0,1,0);
                removeNextOption(1,0,1,0);
            }else if(val=="0"){
                $('#equal_scale_div,#agent_div').show();
                addCommOption(1,1,1);
                addNextOption(1,1,1,1);
            }else{
            	$('#equal_scale_div,#agent_div').hide();
            	 $('#EQUAL_SCALE').val("0");
                 $("#UP_SCALE").val("0");
            	 removeCommOption(0,1,0);
                 removeNextOption(1,0,1,0);
            }
        }
        
        
    $('#Q_SYMBOL_TYPE').val('<?php echo $_REQUEST['Q_SYMBOL_TYPE']; ?>');
    $('#Q_GROUP_NAME').val('<?php echo $_REQUEST['Q_GROUP_NAME']; ?>');
    $('#Q_MODEL_TYPE').val('<?php echo $_REQUEST['Q_MODEL_TYPE']; ?>');
    
    function getCookie(name){  
        var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));  
        if(arr != null){  
         return unescape(arr[2]);   
        }else{  
         return null;  
        }  
    }  
    
    var modeltype= getCookie('modeltype');
    if(modeltype&&modeltype=='direct'){
        $('#agentli > a').attr('class','nav-link');
        $('#directli > a').attr('class','nav-link active');
		$('#memberli > a').attr('class','nav-link');
		
        $('#tab-1').attr('class','tab-pane');
        $('#tab-2').attr('class','tab-pane active');
		$('#tab-3').attr('class','tab-pane');
		
        $('#agent_div,#equal_scale_div,#PATTERN_DIV').hide();
    }
    
    function removeCommOption(fixed,scale,point){
    	if(fixed==1){
	        var tgtOptFixed=$("#COMM_TYPE").find("option[value*='FIXED']");
	        if(tgtOptFixed.length>0)
	            $("#COMM_TYPE option[value='FIXED']").remove();
	    }
    	if(scale==1){
	        var tgtOptScale=$("#COMM_TYPE").find("option[value*='SCALE']");
	        if(tgtOptScale.length>0)
	            $("#COMM_TYPE option[value='SCALE']").remove();
    	}
    	if(point==1){
	        var tgtOptPoint=$("#COMM_TYPE").find("option[value*='POINT']");
	        if(tgtOptPoint.length>0)
	            $("#COMM_TYPE option[value='POINT']").remove();
    	}
    }
    
    function addCommOption(scale,fixed,point){
    	if(fixed==1){
    		var tgtOpt=$("#COMM_TYPE").find("option[value*='FIXED']");
            if(tgtOpt.length==0)
            	 $("#COMM_TYPE").append("<option value='FIXED'>$，<?php echo L('每手固定金额'); ?></option>");
    	}
    	if(point==1){
	        var tgtOptFixed=$("#COMM_TYPE").find("option[value*='POINT']");
	        if(tgtOptFixed.length==0)
	            $("#COMM_TYPE").append("<option value='POINT'>$，<?php echo L('点值/手/美金'); ?></option>");
    	}
        if(scale==1){
        	 var tgtOptScale=$("#COMM_TYPE").find("option[value*='SCALE']");
	        if(tgtOptScale.length==0)
	            $("#COMM_TYPE").append("<option value='SCALE'>%，<?php echo L('交易百分比'); ?></option>");
        }
    }
    
    function removeNextOption(differ,fixed,scale,point){
    	if(differ==1){
	        var tgtOpt=$("#NEXT_COMM_TYPE").find("option[value*='DIFFER']");
	        if(tgtOpt.length>0)
	            $("#NEXT_COMM_TYPE option[value='DIFFER']").remove(); 
    	}
    	if(fixed==1){
	        var tgtOptFixed=$("#NEXT_COMM_TYPE").find("option[value*='FIXED']");
	        if(tgtOptFixed.length>0)
	            $("#NEXT_COMM_TYPE option[value='FIXED']").remove();
    	}
    	if(scale==1){
	        var tgtOptScale=$("#NEXT_COMM_TYPE").find("option[value*='SCALE']");
	        if(tgtOptScale.length>0)
	            $("#NEXT_COMM_TYPE option[value='SCALE']").remove();
    	}
    	if(point==1){
	        var tgtOptPoint=$("#NEXT_COMM_TYPE").find("option[value*='POINT']");
	        if(tgtOptPoint.length>0)
	            $("#NEXT_COMM_TYPE option[value='POINT']").remove();
    	}
    }
    
    function addNextOption(differ,scale,fixed,point){
    	if(differ==1){
	        var tgtOpt=$("#NEXT_COMM_TYPE").find("option[value*='DIFFER']");
	        if(tgtOpt.length==0)
	            $("#NEXT_COMM_TYPE").append("<option value='DIFFER'>Diff，<?php echo L('上级返佣标准'); ?>-<?php echo L('下级返佣标准'); ?></option>");
    	}
        if(fixed==1){
	        var tgtOptFixed=$("#NEXT_COMM_TYPE").find("option[value*='FIXED']");
	        if(tgtOptFixed.length==0)
	            $("#NEXT_COMM_TYPE").append("<option value='FIXED'>$，<?php echo L('每手固定金额'); ?></option>");
        }
        if(scale==1){
	        var tgtOptScale=$("#NEXT_COMM_TYPE").find("option[value*='SCALE']");
	        if(tgtOptScale.length==0)
	            $("#NEXT_COMM_TYPE").append("<option value='SCALE'>%，<?php echo L('下级返佣总额百分比'); ?></option>");
        }
        if(point==1){
	        var tgtOptPoint=$("#NEXT_COMM_TYPE").find("option[value*='POINT']");
	        if(tgtOptPoint.length==0)
	            $("#NEXT_COMM_TYPE").append("<option value='POINT'>pip， <?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>");
        }
    }
	var current_tab_val = modeltype;


	$(document).on("click","#downBtns",function(){
    //$("#downBtns").click(function(){

        var value = $("#commentForm").serialize();
        //var modeltype = $('#Q_MODEL_TYPE').val('<?php echo $_REQUEST['Q_MODEL_TYPE']; ?>');
        
         if(!current_tab_val)
        	 current_tab_val = "agent";
         console.log("type:"+current_tab_val);
        document.location.href="{:U('Commission/down_setting')}?act=down&type="+current_tab_val+"&"+value;
    });

	  $(document).on("click","#agentli",function(){
	  //$("#agentli").click(function(){
		  current_tab_val = "agent";
		  
	  })
	
		$(document).on("click","#directli",function(){
	   //$("#directli").click(function(){
		  current_tab_val = "direct";
		  
	  })

		$(document).on("click","#memberli",function(){
	  //$("#memberli").click(function(){
		  current_tab_val = "member";
		  
	  })
     
    </script>

<script>
    $("#Q_GROUP_NAME").bsSuggest({
		//allowNoKeyword: false, //是否允许无关键字时请求数据。为 false 则无输入时不执行过滤请求
		multiWord: true, //以分隔符号分割的多关键字支持
		separator: ",", //多关键字支持时的分隔符，默认为空格
		getDataMethod: "url", //获取数据的方式，总是从 URL 获取
		//idField: "id",
		keyField: "group",
		url: "/Commission/queryByGroup?Q_GROUP_NAME=" ,
		/*优先从url ajax 请求 json 帮助数据，注意最后一个参数为关键字请求参数*/
	}).on('onDataRequestSuccess', function(e, result) {
		console.log('onDataRequestSuccess: ', result);
	}).on('onSetSelectValue', function(e, keyword, data) {
		console.log('onSetSelectValue: ', keyword, data);
	}).on('onUnsetSelectValue', function() {
		console.log("onUnsetSelectValue");
	});
</script>
        
        

    </body>
</html>
