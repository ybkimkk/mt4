<?php
$LoadCSSArr = array();
require_once('header.php');

$roleDR = $DB->getDRow("select * from `t_role` where id = '{$Id}'");

/*
$a = 'a:11:{i:0;s:1:"a";i:1;s:1:"b";i:2;s:2:"b1";i:3;s:2:"b2";i:4;s:1:"c";i:5;s:2:"c1";i:6;s:2:"c2";i:7;s:1:"d";i:8;s:2:"d2";i:9;s:4:"d201";s:4:"more";a:2:{s:2:"c1";a:2:{i:0;s:12:"添加客户";i:1;s:18:"查看客户详情";}s:2:"c2";a:1:{i:0;s:8:"MT开户";}}}';
$a = unserialize($a);
print_r($a);exit;
*/
?>

<style>
.form-horizontal label{ font-weight:normal;}
.form-horizontal .form-group > label{ font-weight:bold; margin-right:15px;}
.form-horizontal .checkbox-inline{ margin-right:15px;}
@media screen and (min-width:768px) {
	.form-horizontal .col-sm-2 {padding-top: 7px;margin-bottom: 0;text-align: right;}
}
</style>

					<div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php 
									echo L('角色授权');
									echo getCurrMt4ServerName();
									?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" id="commentForm" action="?clause=saveAccess&id=<?php echo $Id;?>" method="post" target="iframe_qpost">

<style>
.WJX-table-permissions {margin-bottom:10px;border:3px solid #E3E4E5; text-align:left;}
	.WJX-table-permissions .menu {height:28px;padding:3px 0px 3px 8px;font-weight:bold;background-color:#E3E4E5;}
	.WJX-table-permissions .item { width:240px; padding-left:8px;background-color:#F3F4F5; height:28px; line-height:28px;}
	.WJX-table-permissions .item label{ margin:0;}
	.WJX-table-permissions .tline .item { border-top:2px solid #ffffff;}
	.WJX-table-permissions .tline .subList { border-top:2px solid #F3F4F5;}
	.WJX-table-permissions .sub {height:25px;width:300px;float:left;padding:3px 0px 3px 10px;display:block;overflow:hidden;background-color:#F9FAFB;}
	.WJX-aes-table .layui-form-item .layui-input-block .WJX-table-permissions input{ height:auto;}
</style>
<script>
function WJXPIS_Menu(id) {
    var p_menu_checked = $("#menu_" + id).is(":checked");
    if (p_menu_checked) {
        $("input[id^='item_" + id + "_']").FN_CheckboxSelect('on');
        $("input[id^='sub_" + id + "_']").FN_CheckboxSelect('on');
    }
    else {
        $("input[id^='item_" + id + "_']").FN_CheckboxSelect('off');
        $("input[id^='sub_" + id + "_']").FN_CheckboxSelect('off');
    }
}
function WJXPIS_Item(menuId, itemId) {
    var p_item_checked = $("#item_" + menuId + "_" + itemId).is(":checked");
    if (p_item_checked) {
        $("input[id^='sub_" + menuId + "_" + itemId + "_']").FN_CheckboxSelect('on');
        WJXPIS_ChkMenu(menuId);
    }else {
        $("input[id^='sub_" + menuId + "_" + itemId + "_']").FN_CheckboxSelect('off');
        WJXPIS_ChkMenu(menuId);
    }
}
function WJXPIS_Sub(menuId, itemId, subId) {
    WJXPIS_ChkItem(menuId, itemId); 
    WJXPIS_ChkMenu(menuId);
}
function WJXPIS_ChkItem(menuId, itemId) {
    var haveChked = false;
    $("input[id^='sub_" + menuId + "_" + itemId + "_']").each(function() {
        if (this.checked == true) { haveChked = true; return; }
    });
    if (haveChked == true) {
        $("#item_" + menuId + "_" + itemId).prop("checked", true);
    }
    else {
        $("#item_" + menuId + "_" + itemId).prop("checked", false);
    }
}
function WJXPIS_ChkMenu(menuId) {
    var haveChked = false;
    $("input[id^='item_" + menuId + "_']").each(function() {
        if (this.checked == true) { haveChked = true; return; }
    });
    if (haveChked == false) {
        $("input[id^='sub_" + menuId + "_']").each(function() {
            if (this.checked == true) { haveChked = true; return; }
        });
    }
    if (haveChked == true) {
        $("#menu_" + menuId).prop("checked", true);
    }else {
        $("#menu_" + menuId).prop("checked", false);
    }
}
</script>
<?php
		$myaccess = unserialize($roleDR['f_access']);
		$tsArr = array();
		
		$chrIndex = 65;
		$ci = 0;
		foreach($CZMenu as $key1=>$xml_menu){
			//menu 的 title
			$menuTitle = L($xml_menu['title']);
			$menuId = $key1;
			if($xml_menu['macc']){
				$tsArr[$key1] = $xml_menu;
				$tsArr[$key1]['chrIndex'] = $chrIndex;
				$chrIndex++;
			}
			
			//若权限含menu的id，说明有该菜单的管理权限
			if(@in_array($menuId,$myaccess)){
				$checked = ' checked="checked"';
			}else{
				$checked = '';
			}
			
			//串接html
			echo '<div class="WJX-table-permissions">';
			echo '<div class="menu">';
			echo '<input lay-ignore type="checkbox" name="menu_' . $ci . '" id="menu_' . $ci . '" value="' . $menuId . '"' . $checked . ' onClick="WJXPIS_Menu(' . $ci . ');">';
			echo '<label for="menu_' . $ci . '"> ';
			echo $menuTitle;
			if($xml_menu['macc']){
				echo '<span style="color:#ff0000">(' . chr($chrIndex-1) . ')</span>';
			}
			echo '</label>';
			echo '</div>';
				
			$cj = 0;
			//取menu-->item
			if($xml_menu['sub']){
				foreach($xml_menu['sub'] as $key2=>$xml_menu_item){
					//item 的 title
					$itemTitle = L($xml_menu_item['title']);
					$itemId = $key2;
					if($xml_menu_item['macc']){
						$tsArr[$key2] = $xml_menu_item;
						$tsArr[$key2]['chrIndex'] = $chrIndex;
						$chrIndex++;
					}
		
					//若权限含item的id，说明有该栏目的管理权限
					if(@in_array($itemId,$myaccess)){
						$checked = ' checked="checked"';
					}else{
						$checked = '';
					}
					
					//串接html
					if($cj > 0){
						$tableClass = ' class="tline"';
					}else{
						$tableClass = '';
					}
					echo '<table width="100%" border="0" cellspacing="0" cellpadding="0"' . $tableClass . '>';
					echo '<tr>';
					echo '<td class="item">';
					echo '<input lay-ignore type="checkbox" name="item_' . $ci . '_' . $cj . '" id="item_' . $ci . '_' . $cj . '" value="' . $itemId . '"' . $checked . ' onClick="WJXPIS_Item(' . $ci . ',' . $cj . ');">';
					echo '<label for="item_' . $ci . '_' . $cj . '"> ';
					echo $itemTitle;
					if($xml_menu_item['macc']){
						echo '<span style="color:#ff0000">(' . chr($chrIndex-1) . ')</span>';
					}
					echo '</label>';
					echo '</td>';
					echo '<td class="subList">';
		
					$ck = 0;
					//取menu-->item-->sub
					if($xml_menu_item['sub']){
						foreach($xml_menu_item['sub'] as $key3=>$xml_menu_item_sub){
							//sub 的 title
							$subTitle = L($xml_menu_item_sub['title']);
							$subId = $key3;
							if($xml_menu_item_sub['macc']){
								$tsArr[$key3] = $xml_menu_item_sub;
								$tsArr[$key3]['chrIndex'] = $chrIndex;
								$chrIndex++;
							}
		
							//若权限含sub的title，说明有该栏目的管理权限
							if(@in_array($subId,$myaccess)){
								$checked = ' checked="checked"';
							}else{
								$checked = '';
							}
							
							//串接html
							echo '<span class="sub">';
							echo '<input lay-ignore type="checkbox" name="sub_' . $ci . '_' . $cj . '_' . $ck . '" id="sub_' . $ci . '_' . $cj . '_' . $ck . '" value="' . $subId . '"' . $checked . ' onClick="WJXPIS_Sub(' . $ci . ',' . $cj . ',' . $ck . ');">';
							echo '<label for="sub_' . $ci . '_' . $cj . '_' . $ck . '"> ' . $subTitle;
							if($xml_menu_item_sub['macc']){
								echo '<span style="color:#ff0000">(' . chr($chrIndex-1) . ')</span>';
							}
							echo '</label>';
							echo '</span>';
							
							$ck++;
						}//end foreach menu-->item-->sub
					}
	
					echo '</td></tr></table>';
					
					$cj++;
				}//end foreach menu-->item
			}
			
			echo '<div class=""clear""></div></div>';
			
			$ci++;
		}//end foreach menu
		
		
		//----------------------------------
		
		if($tsArr){
			if($myaccess['more']){
				$checked = ' checked="checked"';
			}else{
				$checked = '';
			}
			
			//串接html
			echo '<div class="WJX-table-permissions">';
			echo '<div class="menu">';
			echo '<input lay-ignore type="checkbox" name="moremenu_' . $ci . '" id="menu_' . $ci . '" value="more"' . $checked . ' onClick="WJXPIS_Menu(' . $ci . ');">';
			echo '<label for="menu_' . $ci . '" style="color:#ff0000"> (' . L('特殊权限') . ')</label>';
			echo '</div>';
				
			$cj = 0;
			//取menu-->item
			foreach($tsArr as $key2=>$xml_menu_item){
				//item 的 title
				$itemTitle = L($xml_menu_item['title']);
				$itemId = $key2;
	
				//若权限含item的id，说明有该栏目的管理权限
				if(@array_key_exists($itemId,$myaccess['more'])){
					$checked = ' checked="checked"';
				}else{
					$checked = '';
				}
				
				//串接html
				if($cj > 0){
					$tableClass = ' class="tline"';
				}else{
					$tableClass = '';
				}
				echo '<table width="100%" border="0" cellspacing="0" cellpadding="0"' . $tableClass . '>';
				echo '<tr>';
				echo '<td class="item">';
				echo '<input lay-ignore type="checkbox" name="moreitem_' . $ci . '_' . $cj . '" id="item_' . $ci . '_' . $cj . '" value="' . $itemId . '"' . $checked . ' onClick="WJXPIS_Item(' . $ci . ',' . $cj . ');">';
				echo '<label for="item_' . $ci . '_' . $cj . '"> ' . $itemTitle;
				echo '<span style="color:#ff0000">(' . chr($xml_menu_item['chrIndex']) . ')</span>';
				echo '</label>';
				echo '</td>';
				echo '<td class="subList">';
	
				$ck = 0;
				//取menu-->item-->sub
				foreach($xml_menu_item['macc'] as $key3=>$xml_menu_item_sub){
					//sub 的 title
					$subTitle = L($xml_menu_item_sub);
					$subId = $key3;

					//若权限含sub的title，说明有该栏目的管理权限
					if(@in_array($xml_menu_item_sub,$myaccess['more'][$itemId])){
						$checked = ' checked="checked"';
					}else{
						$checked = '';
					}
					
					//串接html
					echo '<span class="sub">';
					echo '<input lay-ignore type="checkbox" name="moresub_' . $ci . '_' . $cj . '_' . $ck . '" id="sub_' . $ci . '_' . $cj . '_' . $ck . '" value="' . $xml_menu_item_sub . '"' . $checked . ' onClick="WJXPIS_Sub(' . $ci . ',' . $cj . ',' . $ck . ');">';
					echo '<label for="sub_' . $ci . '_' . $cj . '_' . $ck . '"> ' . $subTitle . '</label>';
					echo '</span>';
					
					$ck++;
				}//end foreach menu-->item-->sub

				echo '</td></tr></table>';
				
				$cj++;
			}//end foreach menu-->item
			
			echo '<div class=""clear""></div></div>';
		}
		
		//----------------------------------
	
		//串接html
		echo '<div class="WJX-table-permissions">';
		echo '<div class="menu">';
		echo '<label style="color:#ff0000"> (' . L('其它') . ')</label>';
		echo '</div>';

		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
		echo '<tr>';
		echo '<td class="item">';
		echo '<label> ' . L('数据范围') . '</label>';
		echo '</td>';
		echo '<td class="subList">';

			echo '<span class="sub">';
			echo '<input lay-ignore type="radio" name="f_dataRange" id="f_dataRange_0" value="0"' . ($roleDR['f_dataRange'] == 0 ? ' checked="checked"' : '') . '>';
			echo '<label for="f_dataRange_0"> ' , L('自己') , '</label>';
			echo '</span>';
			
			echo '<span class="sub">';
			echo '<input lay-ignore type="radio" name="f_dataRange" id="f_dataRange_1" value="1"' . ($roleDR['f_dataRange'] == 1 ? ' checked="checked"' : '') . '>';
			echo '<label for="f_dataRange_1"> ' , L('伞下') , '</label>';
			echo '</span>';
			
			echo '<span class="sub">';
			echo '<input lay-ignore type="radio" name="f_dataRange" id="f_dataRange_2" value="2"' . ($roleDR['f_dataRange'] == 2 ? ' checked="checked"' : '') . '>';
			echo '<label for="f_dataRange_2"> ' , L('本MT服务') , '</label>';
			echo '</span>';

		echo '</td></tr>';
		echo '</table>';
				
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tline">';
		echo '<tr>';
		echo '<td class="item">';
		echo '<label> ' . L('客户列表') . '-' . L('授权') . '</label>';
		echo '</td>';
		echo '<td class="subList">';
		$query1 = $DB->query("select * from `t_role` where status = 1 order by id asc");
		while($rs1 = $DB->fetchArray($query1)){
			echo '<span class="sub">';
			echo '<input lay-ignore type="checkbox" name="actrole[]" id="actrole_' , $rs1['id'] , '" value="' , $rs1['id'] , '"' . (@in_array($rs1['id'],$myaccess['actrole']) ? ' checked="checked"' : '') . '>';
			echo '<label for="actrole_' , $rs1['id'] , '"> ' , L($rs1['name']) , '</label>';
			echo '</span>';
		}
		echo '</td></tr>';
		echo '</table>';
		
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tline">';
		echo '<tr>';
		echo '<td class="item">';
		echo '<label> ' . L('其它') . '</label>';
		echo '</td>';
		echo '<td class="subList">';

			echo '<span class="sub">';
			echo '<input lay-ignore type="checkbox" name="other[]" id="other_0" value="查看客户上级归属信息"' . (@in_array('查看客户上级归属信息',$myaccess['other']) ? ' checked="checked"' : '') . '>';
			echo '<label for="other_0"> ' , L('查看客户上级归属信息') , '</label>';
			echo '</span>';

		echo '</td></tr>';
		echo '</table>';
		
		echo '<div class=""clear""></div></div>';
?>

                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("提交");?></button>
                                                    <button onclick="window.history.back()" type="button" class="btn btn-light"><?php echo L("返回");?></button>
                                                </div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>

					</div>






		<?php
        require_once('footer.php');
        ?>
        
<script type="text/javascript">
$.fn.FN_CheckboxSelect = function(type) {
    var t = type || "on";
    this.each(function() {
        switch (t) {
        case "on":
            this.checked = true;
            break;
        case "off":
            this.checked = false;
            break;
        case "toggle":
            this.checked = !this.checked;
            break;
        }
    });
}
</script>

    </body>
</html>
