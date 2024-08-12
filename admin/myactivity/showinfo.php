<?php
$LoadCSSArr = array();
require_once('header.php');


$DRInfo = $DB->getDRow("select * from `t_activity_list` where id = '{$Id}' and status = 1 and server_id = '{$DRAdmin['server_id']}' and f_key = ''");

$DRInfo1 = get_lang_otherset_drow('-_activity_-'.$Id,$CurrLangName,$DRAdmin['server_id'],1);
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .control-label {padding-top: 7px;margin-bottom: 0;text-align: right;}
	.form-horizontal .form-control-static {min-height: 34px;padding-top: 7px;padding-bottom: 7px;margin-bottom: 0;}
}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('活动信息') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="post" action="?clause=saveinfo&id=<?php echo $Id;?>">
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static" style="font-weight:bold;"><?php echo $DRInfo1['f_title'];?></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static"><?php echo date('Y-m-d H:i:s',$DRInfo['create_time']);?></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static"><?php echo $DRInfo1['f_val'];?></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static">
                                                
<?php
if(time() > strtotime($DRInfo['start_time']) && time() < strtotime($DRInfo['end_time'])){
	$rs = $DB->getDRow("select * from t_activity_join where f_uid = '" . $DRAdmin['id'] . "' and f_pid = " . $Id);
	if($rs){
		switch($rs['f_status']){
			case '待审核':
				echo '<a class="btn btn-danger btn-red-cz btn-xs" type="button" href="?clause=unjoin&id=' , $Id , '" onclick="return confirm_qx()">' , L('取消报名') , '</a> ';
				break;
			case '审核通过':
				break;
			case '已拒绝':
				if($rs['f_canJoinAgain'] > 0){
					echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=join&id=' , $Id , '" onclick="return confirm_bm()">' , L('重新报名') , '</a> ';
				}
				break;
		}
	}else{
		echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=join&id=' , $Id , '" onclick="return confirm_bm()">' , L('报名') , '</a> ';
	}
}
?>
                                                
                                                	<a href="myactivity.php" type="button" class="btn btn-light"><?php echo L("返回");?></a>
                                                </div></div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                        
                        
                        


                    </div> <!-- container -->


		<?php
        require_once('footer.php');
        ?>
        
        
        
        <script>
			function confirm_bm(){
			  if(confirm("<?php echo L('您确定已经阅读并同意协议报名吗'); ?>?")){
				 return true;
				 }else{
				 return false;
			  }
		   }
		   function confirm_qx(){
			  if(confirm("<?php echo L('您确定要取消报名吗'); ?>?")){
				 return true;
				 }else{
				 return false;
			  }
		   }
        </script>
        
        

    </body>
</html>
