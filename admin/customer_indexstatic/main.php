<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_positionsModel.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_buyModel.class.php');

//-----------------------------------------

$list = array();
$calclevel=10;

$start= date('Y-m-d', time() - 86400 * date('w'));
$end=date('Y-m-d');
if(_request('time_start')){
	$start=_request('time_start');
} 
$time_start = $start;
 
if(_request('time_end')){
	$end = _request('time_end');
} 
$time_end = $end;
$symbollist = array();
if(_request('qtype')){
	$symbollist = $DB->getField("select symbol from t_symbol where `type` = '" . _request('qtype') . "'",true);
	if(empty($symbollist)){
		$symbollist = array('0');
	}
}

//如果输入mt4账号 这里优先级最大
if(_request("loginid")){
	$memberlogin = $DB->getDRow("select member_id as id from t_member_mtlogin where loginid = '" . _request("loginid") . "' and status = 1");
	if ($memberlogin) {
		$_REQUEST['memberid']=$memberlogin['id'];
	}else{
		$_REQUEST['memberid']=-1;
	} 
}
_request('time_end',$end);
$calclevel = 100;
if(_request('memberid')){//优先ID查询
	$map['parent_id']=_request('memberid');
}else if(_request('qname')){//昵称查询
	$qname=urldecode(_request('qname'));
	$pmember = $DB->getDRow("select * from t_member where server_id = '{$DRAdmin['server_id']}' and status = '1' and (nickname = '".$qname."' or email = '".$qname."' or chineseName ='".$qname."')");
	if($pmember)//根据昵称查询
		$map['parent_id']=$pmember['id'];
}

if($map['parent_id']){
	//检测是否有权查看
	$parentid = $DRAdmin['id'];
	_check_member_scope($parentid, $map['parent_id']); //检验是否下级
}else{
	//非管理员，只能看到自己和伞下
	if($DRAdmin['_dataRange'] <= 1){
		$map['parent_id'] = $DRAdmin['id'];
	}else{
		$map['parent_id'] = 'admin';
	}
}

if($_GET['GROUP_NAME']){
	$group = $_GET['GROUP_NAME'];
}

custonmer_indexstatic_staticunder($map['parent_id'],$start,$end,0,$symbollist,true,$group);

$sumtotal = custonmer_indexstatic_totaltradedata($list);
	   
$children=array();
foreach($list as $key=>$val){
	if($val['parent_ids']){
		$children= array_merge($children, explode(',', $val['parent_ids']));
	}
}
$children= implode(',',$children);
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('我的客户报表') , getCurrMt4ServerName();?></h4>
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
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get" autocomplete="off">
                                            	<div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('账户信息');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('qname');?>" name="qname" placeholder="<?php echo L('请输入用户的昵称、姓名、邮箱'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	MT <?php echo L('账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('loginid');?>" name="loginid" placeholder="MT <?php echo L('账号'); ?>">
                                                </div>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易种类');?>：
                                                    <select name="qtype" id="qtype" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=".$DRAdmin['server_id']." and a.status=1");
														foreach($types as $key=>$val){
															echo '<option value="' , $val['id'] , '"' , _request('qtype') == $val['id'] ? ' selected' : '' , '>' , $val['type_name'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('查询时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="time_start" value="<?php echo $time_start;?>" placeholder="<?php echo L('查询开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="time_end" value="<?php echo $time_end;?>" placeholder="<?php echo L('查询结束日期');?>">
                                                    </div>
                                                </div>                                                
                                            	<?php
												$groupsList = $DB->getDTable("select * from t_groups where server_id = {$DRAdmin['server_id']}");
                                                if(count($groupsList) && $DRAdmin['_dataRange'] >= 2){
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:120px;">
                                                    <select data-placeholder="<?php echo L('请选择分组');?>" name="GROUP_NAME[]" class="chosen-select" multiple>
                                                        <option value="all_group"><?php echo L('全部');?></option>
                                                        <?php
														foreach($groupsList as $key=>$val){
															echo '<option value="' , $val['group'] , '" hassubinfo="true"';
															if(in_array($val['group'],$_GET['GROUP_NAME'])){
																echo ' selected';
															}
															echo '>' , $val['group'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <?php
												}
												?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<div class="custom-control custom-checkbox">
                                                        <input type="checkbox"<?php if($_GET['reject'] == 1){echo ' checked';} ?> class="custom-control-input" name="reject" id="reject" value="1"><label class="custom-control-label" for="reject"><?php echo L('剔除组');?></label>
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('剔除MT账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $_GET['T_LOGIN'];?>" name="T_LOGIN" placeholder="<?php echo L('多个MT帐号请用英文逗号隔开'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <div>
                                                    	<button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索'); ?></button>
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
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('客户等级');?></th>
                                                    <th class="no-sort"><?php echo L('客户情况');?></th>
                                                    <th class="no-sort"><?php echo L('交易笔数') , '/' , L('手数');?></th>
                                                    <th class="no-sort"><?php echo L('总入金') , '/' , L('笔数');?></th>
                                                    <th class="no-sort"><?php echo L('总出金') , '/' , L('笔数');?></th>
                                                    <th class="no-sort"><?php echo L('点差');?></th>
                                                    <th class="no-sort"><?php echo L('盈亏') , '/' , L('返佣');?></th>
                                                    <th class="no-sort"><?php echo L('未平仓');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		/*$recordCount = intval($count);
		
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
		$cnPager = new CPager($pageConfig);*/
//print_r($list);exit;
		if(count($list) <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
		}else{
			foreach($list as $key=>$rs){
				if($key == 0){
					echo '<tr style="color:green;font-weight:bold">';
					echo '<td><font color="green">' , $rs['nickname'] , '</font></td>';
					echo '<td>' , L('客户数') , '： <b>' , $rs['num'] , '</b>' , L('个') , '<br/>';
					echo 'MT' , L('账号') , '：  <b>' , $rs['mt4num'] , '</b>' , L('个') , '</td>';
					echo '<td>' , L('笔数') , '：';
					//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
					echo '<b>' , $rs['totalcount'] , '</b>';
					//</a>
					echo L('笔');
					echo '<br/>' , L('交易') , '：';
					//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
					echo '<b>' , round($rs['totalvolume']/100,3) , '</b>';
					//</a>
					echo L('手');
					echo '</td>';
					echo '<td>';
					echo L('入金额') , '：';
					//<a href="{:U('report/history_trade',array('report_type'=>1,'CMD'=>6,'PROFIT_s'=>'0.01','BALANCE'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
					echo '<b>' , round($rs['depoistamount'],3) , '</b>';
					//</a>
					echo '<br/>' , L('入金数') , '：<b>' , $rs['depoistcount'] , L('笔') , '</b>';
					echo '</td>';
					echo '<td>';
					echo L('出金额') , '：';
					//<a href="{:U('report/history_trade',array('report_type'=>1,'CMD'=>6,'PROFIT_e'=>'-0.01','BALANCE'=>-1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
					echo '<b>' , round($rs['withdrawamount'],3) , '</b>';
					//</a>
					echo '<br/>' , L('出金数') , '：<b>' , $rs['withdrawcount'] , L('笔') , '</b>';
					echo '</td>';
                    echo '<td>$' , round($rs['spreadProfitCount'],2) , '</td>';
                    echo '<td>';
     	 				if($rs['profitamount'] < 0){
     	 					echo '<font color="red">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
							echo '<b>$ ' , round($rs['profitamount'],3) , '</b>';
							//</a>
							echo '</font>';
						}else{
     	 					echo '<font color="green">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
							echo '<b>$ ' , round($rs['profitamount'],3) , '</b>';
							//</a>
							echo '</font>';
						}
     	 				echo '<br/>';
     	 				if($rs['commissionbanalce'] < 0){
     	 					echo '<font color="red">' , L('返佣') , '：<b>$ ' , round($rs['commissionbanalce'],3) , '</b> </font>';
						}else{
     	 					echo '<font color="green">' , L('返佣') , '：<b>$ ' , round($rs['commissionbanalce'],3) , '</b> </font>';
						}
                    echo '</td>';
                    echo '<td>';
     	 				if($rs['uncloseamount'] < 0){
     	 					echo '<font color="red">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>2,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
							echo '<b>$ ' , round($rs['uncloseamount'],3) , '</b>';
							//</a>
							echo '</font>';
						}else{
     	 					echo '<font color="green">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>2,'username'=>$rs['email'],'datascope'=>'my','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
							echo '<b>$ ' , round($rs['uncloseamount'],3) , '</b>';
							//</a>
							echo '</font>';
						}
     	 				echo '<br/>' , L('笔数') , '：<b>' , $rs['unclosecount'] , '</b> <br/>' , L('持仓') , '：<b>' , $rs['unclosevolume']/100 , '</b>' , L('手');
                    echo '</td>';
                    echo '</tr>';

                    echo '<tr style="font-weight:bold;">';
                    echo '<td><b>' , L('下级汇总') , '</b></td>';
                    echo '<td>';
                    echo L('客户数') , '：<b>' , $sumtotal['totalmembers'] , '</b><br/>';
                    echo 'MT ' , L('账户') , '：<b>' , $sumtotal['totalmts'] , '</b>';
                    echo '</td>';
                    echo '<td>';
                    echo L('笔数') , '：';
						//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$list[0]['email'],'datascope'=>'nextall','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
                    echo '<b>' , $sumtotal['totalcounts'] , '</b>';
						//</a>';
                    echo '<br/>';
                    echo L('交易') , '：';
						//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$list[0]['email'],'datascope'=>'nextall','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
                    echo '<b>' , round($sumtotal['totalvolumes']/100,3) , L('手') , '</b>';
						//</a>
                    echo '</td>';
                    echo '<td>';
                    echo L('入金额') , '：';
						//<a href="{:U('report/history_trade',array('report_type'=>1,'CMD'=>6,'PROFIT_s'=>'0.01','BALANCE'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$list[0]['email'],'datascope'=>'nextall','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
                    echo '<b>$ ' , round($sumtotal['totalInBalance'],3) , '</b>';
						//</a>
                    echo '<br/>';
                    echo L('入金数') , '：<b>' , $sumtotal['totalInbalanceCount'] , L('笔') , '</b>';
                    echo '</td>';
                    echo '<td>';
                    echo L('出金额') , '：';
						//<a href="{:U('report/history_trade',array('report_type'=>1,'CMD'=>6,'PROFIT_e'=>'-0.01','BALANCE'=>-1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'username'=>$list[0]['email'],'datascope'=>'nextall','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
                    echo '<b>$ ' , round($sumtotal['totalOutBalance'],3) , '</b>';
						//</a>
                    echo '<br/>';
                    echo L('出金数') , '：<b>' , $sumtotal['totalOutbalanceCount'] , L('笔') , '</b>';
                    echo '</td>';
                    echo '<td>$' , round($sumtotal['spreadProfitCount'],2) , '</td>';
                    echo '<td>';
                    if($sumtotal['totalProfit'] < 0){
						echo '<font color="red">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'username'=>$list[0]['email'],'datascope'=>'nextall','CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
						echo '<b>$ ' , round($sumtotal['totalProfit'],3) , '</b>';
							//</a>
						echo '</font>';
					}else{
						echo '<font color="green">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'username'=>$list[0]['email'],'datascope'=>'nextall','CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
						echo '<b>$ ' , round($sumtotal['totalProfit'],3) , '</b>';
							//</a>
						echo '</font>';
					}
                    echo '<br/>';
                    echo '<font color="green">' , L('返佣') , '：<b>$ ' , round($sumtotal['totalCommbanalce'],3) , '</b> </font>';
                    echo '</td>';
                    echo '<td>';
            		if($sumtotal['totalUncloseamount'] < 0){
						echo '<font color="red">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>2,'username'=>$list[0]['email'],'datascope'=>'nextall','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
						echo '<b>$ ' , round($sumtotal['totalUncloseamount'],3) , '</b>';
							//</a>
						echo '</font>';
					}else{
						echo '<font color="green">' , L('盈亏') , '：';
							//<a href="{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>2,'username'=>$list[0]['email'],'datascope'=>'nextall','qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}">
						echo '<b>$ ' , round($sumtotal['totalUncloseamount'],3) , '</b>';
							//</a>
						echo '</font>';
					}	 				 
                    echo '<br/>';
                    echo L('笔数') , '：<b>' , $sumtotal['totalUnclosecount'] , '</b><br/>';
                    echo L('持仓') , '：<b>' , $sumtotal['totalUnclosevolume']/100 , '</b>';
                    echo '</td>';
                    echo '</tr>';
				}else{
                    echo '<tr >';
                    echo '<td>' , L('Level'), ' ' , $key , '</td>';
                    echo '<td>' , L('客户数') , '：';
						//<a href="javascript:query_detail('{:U('/Customer/index',array('qtype'=>$_REQUEST['qtype']))}','{$rs['parent_ids']}')">
                    echo '<b>' , $rs['num'] , '</b>';
						//</a>
                    echo L('个') , '<br/>';
                    echo 'MT ' , L('账号') , '：';
						// <a href="javascript:query_detail('{:U('/Customer/index',array('qtype'=>$_REQUEST['qtype']))}','{$rs['parent_ids']}')">
                    echo '<b>' , $rs['mt4num'] , '</b>';
						 //</a>
                    echo L('个') , '</td>';
                    echo '<td data-value="' , round($rs['totalvolume'],2) , '">' , L('笔数') , '：';
						//<a href="javascript:query_detail('{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'username'=>$rs['email'],'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
                    echo '<b>' , $rs['totalcount'] , '</b>';
						//</a>
                    echo L('笔') , '<br/>' , L('交易') , '：';
						//<a href="javascript:query_detail('{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'username'=>$rs['email'],'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
                    echo '<b>' , round($rs['totalvolume']/100,3) , '</b>';
						//</a>
                    echo L('手') , '</td>';
                    echo '<td data-value="' , round($rs['depoistamount'],2) , '">' , L('入金额') , '：';
						//<a href="javascript:query_detail('{:U('report/history_trade/',array('report_type'=>1,'CMD'=>6,'PROFIT_s'=>'0.01','BALANCE'=>1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
                    echo '<b>' , round($rs['depoistamount'],3) , '</b>';
						//</a>
                    echo '<br/>' , L('入金数') , '：<b>' , $rs['depoistcount'] , '</b> </td>';
                    echo '<td data-value="' , round($rs['withdrawamount'],2) , '">' , L('出金额') , '：';
						//<a href="javascript:query_detail('{:U('report/history_trade/',array('report_type'=>1,'CMD'=>6,'PROFIT_e'=>'-0.01','BALANCE'=>-1,'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
                    echo '<b>' , round($rs['withdrawamount'],3) , '</b>';
						//</a>
                    echo '<br/>' , L('出金数') , '：<b>' , $rs['withdrawcount'] , '</b> </td>';
                    echo '<td>$' , round($rs['spreadProfitCount'],2) , '</td>';
                    echo '<td data-value="' , round($rs['profitamount'],2) , '">';
     	 			if($rs['profitamount'] < 0){
						echo '<font color="red">' , L('盈亏') , '：';
							//<a href="javascript:query_detail('{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'username'=>$rs['email'],'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
						echo '<b>$ ' , round($rs['profitamount'],3) , '</b>';
							//</a>
						echo '</font>';
					}else{
						echo '<font color="green">' , L('盈亏') , '：';
							//<a href="javascript:query_detail('{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>1,'username'=>$rs['email'],'CLOSE_TIME_s'=>$time_start,'CLOSE_TIME_e'=>$time_end,'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
						echo '<b>$ ' , round($rs['profitamount'],3) , '</b>';
							//</a>
						echo '</font>';
					}
                    echo '<br/>';
                    echo '<font color="green">' , L('返佣') , '：<b>$ ' , round($rs['commissionbanalce'],3) , '</b> </font>';
                    echo '</td>';
                    echo '<td data-value="' , round($rs['uncloseamount'],2) , '">';
     	 			if($rs['uncloseamount'] < 0){
						echo '<font color="red">' , L('盈亏') , '：';
							//<a href="javascript:query_detail('{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>2,'username'=>$rs['email'],'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
						echo '<b>$ ' , round($rs['uncloseamount'],3) , '</b>';
							//</a>
						echo '</font>';
					}else{
						echo '<font color="green">' , L('盈亏') , '：';
							//<a href="javascript:query_detail('{:U('report/history_trade',array('CMD'=>'0,1','closetime'=>2,'username'=>$rs['email'],'qtype'=>$_GET['qtype'],'reject'=>$_GET['reject'],'GROUP_NAME'=>$_GET['GROUP_NAME'],'T_LOGIN'=>$_GET['T_LOGIN']))}','{$rs['parent_ids']}')">
						echo '<b>$ ' , round($rs['uncloseamount'],3) , '</b>';
							//</a>
						echo '</font>';
					}
                    echo '<br/>' , L('笔数') , '：<b>' , $rs['unclosecount'] , '</b> <br/>' , L('持仓') , '：<b>' , $rs['unclosevolume']/100 , '</b>' , L('手');
                    echo '</td>';
                    echo '</tr>';
				}
			}
		}
?>
                                            </tbody>
                                        </table>

<?php
//echo $cnPager->FGetPageList();
?>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->
                    
                 
                    


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
			$("#commentForm .layer-date").datepicker({
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

        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>   
        <script>
			$(".chosen-select").chosen( {width: "100%"});
        </script>
        
    </body>
</html>
