<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('出入金管理') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('MT帐号');?></th>
                                                    <th class="no-sort"><?php echo L('MT名称');?></th>
                                                    <th class="no-sort"><?php echo L('姓名');?></th>
                                                    <th class="no-sort"><?php echo L('邮箱');?></th>
                                                    <th class="no-sort"><?php echo L('余额');?></th>
                                                    <th class="no-sort"><?php echo L('净值');?></th>
                                                    <th class="no-sort"><?php echo L('保证金');?></th>
                                                    <th class="no-sort"><?php echo L('可用预付款');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
        $map = "where b.status = 1";
        $map .= " and a.status >= 0";
        $map .= " and a.member_id = '{$DRAdmin['id']}'";
        $map .= " and a.mtserver = '{$DRAdmin['server_id']}'";
		
        //$count = D('MemberMtlogin')->join(' a inner  join ' . $t . 'member b on a.member_id=b.id')->where($map)->count();

        $list = $DB->getDTable("select a.*,b.nickname,b.phone,b.email from t_member_mtlogin  a inner  join t_member b on a.member_id=b.id {$map} order by a.create_time desc");
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_users where `LOGIN` = '{$list[$i]['loginid']}'");
                if ($user) {
                    $list[$i]['group'] = $user['GROUP'];
                    $list[$i]['amount'] = $user['BALANCE'];
                    $list[$i]['EQUITY'] = $user['EQUITY'];
                    $list[$i]['MTNAME'] = $user['NAME'];
                    $list[$i]['MARGIN_FREE'] = $user['MARGIN_FREE'];
                    $list[$i]['MARGIN'] = $user['MARGIN'];
                } else {
                    $list[$i]['group'] = L("未知");
                    $list[$i]['amount'] = L("未知");
                    $list[$i]['EQUITY'] = L("未知");
                    $list[$i]['MTNAME'] = L("未知");
                }
            }
        }

        $pays = $DB->getDTable("select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' order by sort asc");

		//出入金汇率
        //$exchangerate = M('ConfigServer')->where(array('configname' => 'EXCHANGERATE', 'server_id' => $DRAdmin['server_id']))->field('configvalue as value')->find();
        //$this->assign('exchangerate', $exchangerate);
		
        //$exchangerateout = M('ConfigServer')->where(array('configname' => 'EXCHANGERATE_OUT', 'server_id' => $DRAdmin['server_id']))->field('configvalue as value')->find();
        //$this->assign('exchangerateout', $exchangerateout);
		
        $member = $DRAdmin;
		
        //会员银行卡信息
        $bank = $DRAdmin;
        if ($bank && $bank[0]['accountNum']) {
            $bank_a = array();
            $bank_a[0]['id'] = $bank[0]['id'];
            $bank_a[0]['bankCard'] = $bank[0]['bankCard'];
            $bank_a[0]['bankName'] = $bank[0]['bankName'];
            $bank_a[0]['swiftCode'] = $bank[0]['swiftCode'];
            $bank_a[0]['accountName'] = $bank[0]['accountName'];
            $bank_a[0]['accountNum'] = $bank[0]['accountNum'];
        }
        $bank_b = $DB->getDTable("select * from t_bankcode where server_id = '{$DRAdmin['server_id']}' and member_id = '{$DRAdmin['id']}' and status = 1");		
        $bank_name = array_merge($bank_a ? $bank_a : array(), $bank_b ? $bank_b : array());
        $count_num_bank = count($bank_name);


	if(count($list) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		foreach($list as $key=>$rs){
			echo '<tr>';
			echo '<td>' , $rs['loginid'] , '</td>';
			echo '<td>' , $rs['MTNAME'] , '</td>';
			echo '<td>' , $rs['nickname'] , '</td>';
			echo '<td>' , $rs['email'] , '</td>';
			echo '<td>' , round($rs['amount'],2) , '</td>';
			echo '<td>' , round($rs['EQUITY'],2) , '</td>';
			echo '<td>' , round($rs['MARGIN'],2) , '</td>';
			echo '<td>' , round($rs['MARGIN_FREE'],2) , '</td>';
			echo '<td>';
			//echo '<a  href="in_money.php?clause=addinfo" target="_blank" class="btn btn-primary btn-xs">' , L("入金") , '</a> ';
			//echo '<button type="button" val="' , $rs['loginid'] , '" data-toggle="modal"  data-target="#myModal" class="btn btn-primary btn-xs outmoney">' , L("出金") , '</button> ';
			echo '<a  href="?clause=addinfo&login=' , $rs['loginid'] , '" class="btn btn-primary btn-xs">' , L("出金") , '</a> ';
			//echo '<a type="button" val="' , $rs['loginid'] , '" href="?clause=outmoney&loginid=' , $rs['loginid'] , '" class="btn btn-primary btn-xs ountmoneylog">' , L("出金记录") , '</a> ';
			//echo '<a type="button" val="' , $rs['loginid'] , '" href="?clause=addmoney&loginid=' , $rs['loginid'] , '" class="btn btn-primary btn-xs inmoneylog">' , L("入金记录") , '</a>';
			echo '</td>';		
			echo '</tr>';
		}
	}
	
	//print_r($AccessList);exit;
?>
                                            </tbody>
                                        </table>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->



                    </div> <!-- container -->


		<?php
        require_once('footer.php');
        ?>

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

    </body>
</html>