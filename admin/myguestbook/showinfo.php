<?php
$LoadCSSArr = array();
require_once('header.php');


$DRInfo = $DB->getDRow("select a.*,b.nickname,b.phone,b.email from (select * from `t_message_board` where id = '{$Id}' and m_id = '{$DRAdmin['id']}') a left join `t_member` b on a.m_id = b.id");
$DRAnswer = $DB->getDRow("select * from `t_message_board_answer` where `mb_id` = '{$Id}'");
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
                                    <h4 class="page-title"><?php echo L('留言详情') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="post">
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("标题");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['f_title'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("类型");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo L($GuestbookTypeArr[$DRInfo['type']]);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("内容");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRInfo['content'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("时间");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo date('Y-m-d H:i:s',$DRInfo['addtime']);?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label"><?php echo L("回复");?>:</label>
                                                <div class="col-sm-10"><div class="form-control-static"><?php echo $DRAnswer['content'];?></div></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-10"><div class="form-control-static">
                                                	<button onclick="window.history.back();" type="button" class="btn btn-light"><?php echo L("返回");?></button>
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

    </body>
</html>
