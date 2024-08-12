<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchNickname = FGetStr('searchNickname');
$SearchEmail = FGetStr('searchEmail');
$SearchModule = FGetStr('searchModule');
$SearchPost = FGetStr('searchPost');
$SearchSTime = FGetStr('searchSTime');
$SearchETime = FGetStr('searchETime');if(strlen($SearchSTime) && strlen($SearchETime) && $SearchETime < $SearchSTime){$temp_ = $SearchETime;$SearchETime = $SearchSTime;$SearchSTime = $temp_;}
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('操作日志列表') , getCurrMt4ServerName();?></h4>
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
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('会员昵称');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchNickname;?>" name="searchNickname">
                                                </div>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('会员邮箱');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchEmail;?>" name="searchEmail">
                                                </div>
                                                <?php
												$pMenuOptions_ = $DB->getDTable("select * from t_menu where urlNew <> ''");
												$pMenuArr_ = array();
												?>
                                            	<div class="form-group mr-sm-2" style="min-width:120px;">
                                                	<?php echo L('操作模块');?>：
                                                    <select name="searchModule" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														foreach($pMenuOptions_ as $key=>$val){
															$pMenuArr_[$val['urlNew']] = L($val['title']);
															echo '<option value="' , $val['urlNew'] , '"';
															if($val['urlNew'] == $SearchModule){
																echo ' selected="selected"';
															}
															echo '>' , L($val['title']) , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label">POST：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchPost;?>" name="searchPost">
                                                </div>
                                					<!--
                                                    <label class="col-sm-1 control-label"><?php echo L('操作模块');?>：</label>
                                                    <div class="col-sm-2">
                                                        <select name="searchModule" class="form-control">
                                                        <option value=''><?php echo L('全部');?>  </option>
                                                          <volist name='modules' id='vo'>
                                                            <option value='{$vo.id}'>{:L($vo['title'])}  </option>
                                                          </volist>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <select name='action' id="action" class='form-control'>
                                                        <php>if($action_name){</php>
                                                            <option selected="selected">{$action_name}</option>
                                                        <php>}else{</php>
                                                            <option value=''><?php echo L('全部');?>  </option>
                                                         <php>}</php>
                                                        </select>
                                                    </div>
                                                    -->                                
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('操作时间');?>：</label>
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="searchSTime" value="<?php echo $SearchSTime;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="searchETime" value="<?php echo $SearchETime;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary"><?php echo L('搜索');?></button>
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
                                                    <th class="no-sort"><?php echo L('操作人');?></th>
                                                    <th class="no-sort"><?php echo L('操作路径');?></th>
                                                    <th class="no-sort"><?php echo L('操作标题');?></th>
                                                    <th class="no-sort">POST</th>
                                                    <th class="no-sort"><?php echo L('操作IP');?></th>
                                                    <th class="no-sort"><?php echo L('操作时间');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php



        /*$t = C("DB_PREFIX");
        $count = M("member")->join(" a  inner join {$t}logger b on a.id=b.user_id")->where($map)->count();
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $list = M("member")->join(" a  inner join {$t}logger b on a.id=b.user_id")->where($map)->limit($p->firstRow . ',' . $p->listRows)->field("a.nickname,email,b.*")->order('b.id desc')->select();

        $this->list = $list;
        $this->module_node = M("Node")->where(array('pid' => 0))->getField('name,title');
        $modules = M("Node")->where(array('pid' => 0))->select();
        $action_node = array();
        foreach ($modules as $key => $val) {
            $action_node[$val['name']] = M("Node")->where(array('pid' => $val['id']))->getField('name,title');
        }*/







	$where = "where server_id = '{$DRAdmin['server_id']}'";
	/*if ($module) {
		$node_mod = M("Node")->where('id=' . $module)->field('name')->find();
		$map['b.module_name'] = $node_mod['name'];
	}
	if ($action) {
		$node_act = M("Node")->where('id=' . $action)->field('name,title')->find();
		$map['b.action_name'] = $node_act['name'];
		$this->assign('action_name', $node_act['title']);
	}*/
	if (strlen($SearchSTime)) {
		$where .= " and create_time >= '{$SearchSTime} 00:00:00'";
	}
	if (strlen($SearchETime)) {
		$where .= " and create_time <= '{$SearchETime} 23:59:59'";
	}
	if (strlen($SearchNickname) > 0 || strlen($SearchEmail) > 0) {
		$where1 = '';
		if (strlen($SearchNickname) > 0) {
			$where1 = "where `nickname` like '%{$SearchNickname}%'";
		}
		if (strlen($SearchEmail) > 0) {
			if(strlen($where1) <= 0){
				$where1 = "where `email` = '{$SearchEmail}'";
			}else{
				$where1 .= " and `email` = '{$SearchEmail}'";
			}
		}
		
		$where .= " and user_id in (select id from `t_member` {$where1})";
	}
	if (strlen($SearchModule)) {
		$where .= " and url = '/admin/{$SearchModule}'";
	}
	if (strlen($SearchPost)) {
		$where .= " and postdata like '%{$SearchPost}%'";
	}
	
	$recordCount = intval($DB->getField("select count(*) from `t_logger` {$where}"));
	
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
	$query = $DB->query("select a.*,b.nickname,b.email from (select * from `t_logger` {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join `t_member` b on a.user_id = b.id");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , L('英文名') , ': ' , $rs['nickname'] , '<br>' , L('邮箱') , ': ' , $rs['email'] , '</td>';
			echo '<td>' , $rs['module_name'] , '<br>' , $pMenuArr_[$rs['module_name']] , '</td>';
			echo '<td>' , $rs['action_name'] , '</td>';
			echo '<td>' , L('GET参数') , ': ' , wb_substr($rs['getdata'],20) , '<br>' , L('POST参数') , ': <br><textarea readonly style="width:350px;height:80px;">' , $rs['postdata'] , '</textarea></td>';
			echo '<td>' , $rs['ip'] , '</td>';
			echo '<td>' , $rs['create_time'] , '</td>';
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

    </body>
</html>
