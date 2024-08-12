<?php
/*
2015-10-29 更新


*/

class CCategory{
	private $G_f_sort = 0;

	function __construct(){
	
	}
	
	function __destruct(){
		
	}
	
	function run(){
		global $clause;
		global $tid;
		if($tid <= 0){
			echo '类别错误！请联系技术人员检查并处理！';
			exit;
		}
		
		switch($clause){
			case 'add_info':
				$this->menu();
				$this->add_info();
				break;
			case 'save_add_info':
				$this->save_add_info();
				break;
			case 'edit_info':
				$this->menu();
				$this->edit_info();
				break;
			case 'save_edit_info':
				$this->save_edit_info();
				break;
			case 'chk_repair':
				$this->chk_repair();
				FJS_AT('更新成功！',FPrevUrl());
				break;
			case 'del_info':
				$this->del_info();
				break;
			case 'root_sort':
				$this->menu();
				$this->root_sort();
				break;
			case 'save_root_sort':
				$this->save_root_sort();
				break;
			case 'child_sort':
				$this->menu();
				$this->child_sort();
				break;
			case 'save_child_sort':
				$this->save_child_sort();
				break;
			default:
				$this->menu();
				$this->main();
		}
	}
	
	//获取上移或下移目标位置的排序
	function get_UD_target_sort($parentId,$cid,$cidSort,$udNum){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		$targetIndex = -1;
		
		if($udNum > 0){
			$sql = "select f_categoryId,f_sort from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_parentId = {$parentId} and f_sort >= {$cidSort} order by f_sort asc";
		}else{
			$sql = "select f_categoryId,f_sort from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_parentId = {$parentId} and f_sort <= {$cidSort} order by f_sort asc";
		}
		$rs_list = $DB->getDTable($sql);
		$rsCount = count($rs_list);
		for($ci = 0;$ci < $rsCount;$ci++){
			$rs = $rs_list[$ci];
			if($rs['f_categoryId'] == $cid){
				$targetIndex = $ci + $udNum;
				break;
			}
		}
		if($targetIndex < 0 || $targetIndex >= $rsCount){
			$targetIndex = -1;
		}else{
			$targetIndex = $rs_list[$targetIndex]['f_sort'];
		}
		return $targetIndex;
	}
	
	function save_child_sort(){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		global $cid;
		
		$upCount = FPostInt('upCount');
		$downCount = FPostInt('downCount');
		if($upCount <= 0 && $downCount <= 0){
			FJS_AB('抱歉，请选择排序方向（向上或向下）');
		}
		if($upCount > 0 && $downCount > 0){
			FJS_AB('抱歉，排序方向（向上或向下）同时只能选择一项');
		}
		
		$editRs = $DB->getDRow("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_categoryId = {$cid}");
		if(!$editRs){
			FJS_AB('抱歉，未找到您要排序的数据');
		}else{
			$f_sort = $editRs['f_sort'];
			$f_parentId = $editRs['f_parentId'];
		}
		
		if($upCount > 0){
			$relatedOrder = $this->get_UD_target_sort($f_parentId,$cid,$f_sort,0 - $upCount);
			if($relatedOrder < 0){
				FJS_AB('抱歉，排序出错了！');
			}
			
			$DB->query("update `{$config['config']['dbTable']}` set f_sort = f_sort + 999999 where f_typeId = {$tid} and f_sort >= {$relatedOrder}");
			$DB->query("update `{$config['config']['dbTable']}` set f_sort = {$relatedOrder} where f_typeId = {$tid} and f_categoryId = {$cid}");
		}
		else if($downCount > 0){
			$relatedOrder = $this->get_UD_target_sort($f_parentId,$cid,$f_sort,$downCount);
			if($relatedOrder < 0){
				FJS_AB('抱歉，排序出错了！');
			}

			$DB->query("update `{$config['config']['dbTable']}` set f_sort = f_sort + 999999 where f_typeId = {$tid} and f_sort >= {$relatedOrder}");
			$DB->query("update `{$config['config']['dbTable']}` set f_sort = {$relatedOrder} where f_typeId = {$tid} and f_categoryId = {$cid}");
		}
		
		//执行修正
		$this->G_f_sort = 0;
		$this->chk_repair();
		
		global $pageParms;
		if($pageParms){
			FJS_AT('移动成功！',"?{$pageParms}&clause=child_sort");
		}else{
			FJS_AT('移动成功！',"?tid={$tid}&clause=child_sort");
		}
	}
	
	function get_UD_count($query,$parentId, $sort, $lr){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		
		$temp = 0;
		if($DB->num_rows($query) > 0){
			//遍历tid所有的分类
			while($rs = $DB->fetch_array($query)){
				//"Select COUNT(id) FROM `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_parentId = {$parentId} AND f_sort < {$sort}"
				//"Select COUNT(id) FROM `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_parentId = {$parentId} AND f_sort > {$sort}"
				//如果父id相同
				if($rs['f_parentId'] == $parentId){
					//小于
					if($lr == '<'){
						if($rs['f_sort'] < $sort){
							$temp++;
						}
					}else if($lr == '>'){
						//大于
						if($rs['f_sort'] > $sort){
							$temp++;
						}
					}//end if
				}//end if
			}//end while
			//恢复指针到第一条记录
			$DB->move_first($query);
		}//end if
		return $temp;
	}
	
	function child_sort(){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="2" class="title">下级排序</td>';
		echo '</tr>';
		
		$query_ud = $DB->query("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} order by f_rootId asc,f_sort asc");
		
		$query = $DB->query("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} order by f_rootId asc,f_sort asc");
		if($DB->num_rows($query) > 0){
			while($rs = $DB->fetch_array($query)){
				echo '<tr>';
				echo '<td>';
				for($ci = 1;$ci <= $rs['f_depth'];$ci++){
					echo '&#12288;';
				}
				if($rs['f_childCount'] > 0){
					echo '╋ ';
					echo ' <b>' . $rs['f_title'] . '</b>(' . $rs['f_childCount'] . ')';
				}else{
					echo '├ ';
					echo $rs['f_title'];
				}
				echo '</td>';
				echo '<td>';
				if($rs['f_parentId'] > 0){
					
					echo '<form action="?clause=save_child_sort&tid=' . $tid . '&cid=' . $rs['f_categoryId'] . '" method="post">';
					
					$upCount = $this->get_UD_count($query_ud,$rs['f_parentId'], $rs['f_sort'], '<');
					$downCount = $this->get_UD_count($query_ud,$rs['f_parentId'], $rs['f_sort'], '>');
					if($upCount > 0){
						echo '<select name="upCount">';
						echo '<option value="0">↑向上移动</option>';
						for($ci = 1;$ci <= $upCount;$ci++){
							echo '<option value="' . $ci . '">' . $ci . '</option>';
						}
						echo '</Select> &nbsp; ';
					}
					if($downCount > 0){
						echo '<select name="downCount">';
						echo '<option value="0">↓向下移动</option>';
						for($ci = 1;$ci <= $downCount;$ci++){
							echo '<option value="' . $ci . '">' . $ci . '</option>';
						}
						echo '</Select> &nbsp; ';
					}
					if($upCount > 0 || $downCount > 0){
						echo '<input type="submit" name="send_btn_" value="修改" class="bttn" />';
					}
					
					echo '</form>';
				}else{
					echo '&nbsp;';
				}
				echo '</td>';
				echo '</tr>';
			}
		}else{
			echo '<tr>
					<td class="tac" colspan="100">未找到相关数据</td>
				  </tr>';
		}
		
		echo '</table>';
	}
	
	function save_root_sort(){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		global $cid;
		
		$f_rootId = FPostInt('f_rootId');
		if($f_rootId <= 0){
			FJS_AB('序号必须大于0');
		}
		
		//检查看有没有变更
		$O_f_rootId = 0;
		$rs = $DB->getDRow("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_categoryId = {$cid}");
		if(!$rs){
			FJS_AB('未找到您要编辑的数据！');
		}else{
			if($rs['f_rootId'] == $f_rootId){
				FJS_AB('您未做变更，不必修改！');
			}
			
			$O_f_rootId = $rs['f_rootId'];
		}
		
		$targetRootId = 0;
		//检查看，有没有别的分类用了该序号
		$rs = $DB->getDRow("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_rootId = {$f_rootId} and f_categoryId <> {$cid}");
		if($rs){
			//如果有，那么将别的序号变更
			//尝试10次
			for($ci = 1;$ci <= 10;$ci++){
				$targetRootId = $rs['f_rootId'] * 10 * $ci;
				if($targetRootId > 2000000000 || $targetRootId <= 0){
					$targetRootId = mt_rand(1,2000000000);
				}
				
				if($DB->counter($config['config']['dbTable'],'id',"f_typeId = {$tid} and f_rootId = {$targetRootId} and f_categoryId <> {$cid}") <= 0){
					break;
				}else{
					$targetRootId = 0;
				}
			}
			
			if($targetRootId == 0){
				FJS_AB('系统发生错误，请重新提交！');
			}
		}
		
		$tips = '修改成功！';
		
		//将别的分类先更新
		if($targetRootId > 0){
			$DB->query("update `{$config['config']['dbTable']}` set f_rootId = {$targetRootId} where f_typeId = {$tid} and f_rootId = {$f_rootId}");
			
			$tips = '修改成功！但您输入的序号与其它数据的序号有冲突，程序自动修改了有冲突的数据序号！';
		}
		
		//更新
		$DB->query("update `{$config['config']['dbTable']}` set f_rootId = {$f_rootId} where f_typeId = {$tid} and f_rootId = {$O_f_rootId}");
		
		//修正
		$this->G_f_sort = 0;
		$this->chk_repair();
		
		global $pageParms;
		if($pageParms){
			FJS_AT($tips,"?{$pageParms}&clause=root_sort");
		}else{
			FJS_AT($tips,"?tid={$tid}&clause=root_sort");
		}
	}
	
	function root_sort(){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="2" class="title">一级排序</td>';
		echo '</tr>';
		
		$query = $DB->query("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_parentId = 0 order by f_rootId asc,f_sort asc");
		if($DB->num_rows($query) > 0){
			while($rs = $DB->fetch_array($query)){
				echo '<tr>';
				echo '<td width="l30">';
				echo $rs['f_title'];
				echo '</td>';
				echo '<td>';
				echo '<form action="?clause=save_root_sort&tid=' . $tid . '&cid=' . $rs['f_categoryId'] . '" method="post">';
				echo '<input class="input1" name="f_rootId" type="text" value="' . $rs['f_rootId'] . '" />';
				echo ' <input class="bttn" name="btn_submit_" type="submit" value="修改" />';
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
		}else{
			echo '<tr>
					<td class="tac" colspan="100">未找到相关数据</td>
				  </tr>';
		}
		
		echo '</table>';
	}
	
	function del_info(){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		global $cid;
		
		$rs = $DB->getDRow("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_categoryId = {$cid}");
		if(!$rs){
			FJS_AB('抱歉，未找到您要删除的数据！');
		}else{
			if($rs['f_childCount'] > 0){
				FJS_AB('抱歉，您要删除的数据含下级数据，请先删除其下级数据！');
			}
		}
		
		$DB->query("delete from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_categoryId = {$cid}");
		
		//修正
		$this->G_f_sort = 0;
		$this->chk_repair();
		
		global $pageParms;
		if($pageParms){
			FJS_AT('删除成功！',"?{$pageParms}");
		}else{
			FJS_AT('删除成功！',"?tid={$tid}");
		}
	}
	
	function save_edit_info(){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		global $cid;
		
		//分类名称
		$f_title = FPostStr('f_title');
		if($f_title == ''){
			FJS_AB('请填写名称！');
		}
		
		//编辑的分类
		$editRs = $DB->getDRow("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_categoryId = {$cid}");
		if(!$editRs){
			FJS_AB('抱歉，未找到您要编辑的数据！');
		}
	
		//检测父id归属
		$f_parentId = FPostInt('f_parentId');
		if($editRs['f_parentId'] == $f_parentId){
			//未编辑父ID
		}else{
			//不可以属于自己或自己的下级
			if(stripos(',' . $editRs['f_childIds'] . ',',',' . $f_parentId . ',') !== false){
				FJS_AB('抱歉，数据归属不可以是 本数据自身 或 本数据的下级数据！');
			}
			
			//深度、rootId都不管了，在修正里会修正
		}
		
		//写入数据库
		$sqlFVArr = array(
			'f_title'=>$f_title,
			'f_parentId'=>$f_parentId,
			'f_updateTime'=>FNow(),
		);
		//遍历post的数据，增加其它字段
		foreach($_POST as $key=>$val) {
			if(substr($key,0,2) == 'f_'){
				//不存在的数据才加入
				if(stripos(',id,f_typeId,f_categoryId,f_title,f_rootId,f_depth,f_parentId,f_childCount,f_childIds,f_sort,f_addTime,',',' . $key . ',') === false){
					if(substr($key,0,7) == 'f_html_'){
						//如果是ckeditor里过来的，非html标签中的'"会被ckeditor转义为&quot;形式
						//这里的转义'"转为\'\"，是过滤从textarea中过来的数据
						$sqlFVArr[$key] = FAddslashes($val);
					}else{
						$sqlFVArr[$key] = FSHtmlEncode($val);
					}
				}
			}
		}
		//正式更新
		$affectId = $DB->update($config['config']['dbTable'],$sqlFVArr,"f_typeId = {$tid} and f_categoryId = {$cid}");
		
		//执行修正
		if($editRs['f_parentId'] != $f_parentId){
			//编辑了父ID，才需要修正，否则是保持原结构的
			$this->G_f_sort = 0;
			$this->chk_repair();
		}
		
		global $pageParms;
		if($pageParms){
			FJS_AT('修改成功！',"?{$pageParms}");
		}else{
			FJS_AT('修改成功！',"?tid={$tid}");
		}
	}
	
	function edit_info(){
		$this->operate_record('edit');
	}
	
	function main(){
		global $DB;
		global $pageParms;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		$tdCount = count($config['list']['columns']);
		
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="' . $tdCount . '" class="title">' . $config['list']['config']['title'] . '</td>';
		echo '</tr>';
		
		//子标题
		echo '<tr>';
		//子array里的width为sTitle用
		//height、class为td用
		foreach($config['list']['columns'] as $key=>$val){
			echo '<th';
			if($val['width']){
				echo ' width="' . $val['width'] . '"';
			}
			echo ' class="sTitle">';
			echo $key;
			echo '</th>';
		}
		echo '</tr>';
		
		$query = $DB->query("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} order by f_rootId asc,f_sort asc");
		if($DB->num_rows($query) > 0){
			while($rs = $DB->fetch_array($query)){
				echo '<tr>';
				
				//遍历td
				$cj = 0;
				foreach($config['list']['columns'] as $key1=>$val1){
					$cj++;
					
					//属性
					echo '<td';
					if($val1['height'])
						echo ' height="' . $val1['height'] . '"';
					if($val1['class'])
						echo ' class="' . $val1['class'] . '"';
					echo '>';
					echo '<div>';
					
					//遍历td里的数据，可能有多行数据显示在同一个td里
					for($ci = 0;$ci < count($val1);$ci++){
						//key=>val的，使用[index]无法获得
						$arrLine = $val1[$ci];
						
						if(is_array($arrLine)){
							//如果有if语句 view if
							if($arrLine['vif']){
								eval('$vif_result = ' . $arrLine['vif'] . ';');

								if(!$vif_result){
									continue;
								}
							}
							
							if($arrLine['shtml']){
								echo $arrLine['shtml'];
							}

							switch($arrLine['type']){
								case 'bgColor':
									echo '<span class="bgColor';
									if($arrLine['class']){
										echo ' ' , $arrLine['class'];
									}
									echo '"';
									echo ' style="background-color:' , $rs[$arrLine['field']] , ';';
									if($arrLine['style']){
										echo $arrLine['style'];
									}
									echo '"';
									echo '>' , $rs[$arrLine['field']] , '</span>';
									break;
								case 'otfv':
									//other table field value
									//用到时再启用，暂无用
									//echo $rs[$arrLine['field']];
									break;
								case 'isort':
									//不支持
									break;
								case 'url':
									//检测是否限级
									if($arrLine['chkDepth']){
										if($rs['f_depth'] >= $config['config']['depth']){
											echo '-';
											break;
										}
									}
									
									echo '<a href="';
									echo $this->replace_url_1parms_rs($arrLine['url'],$rs);
									echo '"';
									if($arrLine['target']){
										echo ' target="' . $arrLine['target'] . '"';
									}
									echo '>';
									echo $arrLine['text'];
									echo '</a>';
									break;
								case 'img':
									echo '<a href="' . $rs[$arrLine['field']] . '" target="_blank"><img src="' . $rs[$arrLine['field']] . '"';
									if($arrLine['width']){
										echo ' width="' . $arrLine['width'] . '"';
									}
									if($arrLine['height']){
										echo ' height="' . $arrLine['height'] . '"';
									}
									if($arrLine['class']){
										echo ' class="' . $arrLine['class'] . '"';
									}
									if($arrLine['style']){
										echo ' style="' . $arrLine['style'] . '"';
									}
									echo ' /></a>';
									break;
								case 'bool':
									if($rs[$arrLine['field']]){
										if($arrLine['qupdate']){
											echo '<a href="?clause=update_bool&field=' . $arrLine['field'] . '&state=0&id=' . $rs['id'] . '">'; 
										}
										echo $arrLine['ttpl'];
										if($arrLine['qupdate']){
											echo '</a>'; 
										}
									}else{
										if($arrLine['qupdate']){
											echo '<a href="?clause=update_bool&field=' . $arrLine['field'] . '&state=1&id=' . $rs['id'] . '">'; 
										}
										echo $arrLine['ftpl'];
										if($arrLine['qupdate']){
											echo '</a>'; 
										}
									}
									break;
								case 'switch':
									if($arrLine['url']){
										echo '<a href="';
										echo $this->replace_url_1parms_rs($arrLine['url'],$rs);
										echo '"';
										if($arrLine['target']){
											echo ' target="' . $arrLine['target'] . '"';
										}
										echo '>';
									}
									
									//case是个数组，case的key是否field的值，判断case的key是否存在
									if(array_key_exists($rs[$arrLine['field']],$arrLine['case'])){
										//存在，则显示其值
										echo $arrLine['case'][$rs[$arrLine['field']]];
									}else if(array_key_exists('*default*',$arrLine['case'])){
										//不存在，看是否有默认值，有的话，显示默认值
										echo $arrLine['case']['*default*'];
									}
									
									if($arrLine['url']){
										echo '</a>';
									}
									break;
								case 'action':
									switch($arrLine['action']){
										case 'edit':
											echo '<a href="?clause=edit_info&cid=' . $rs['f_categoryId'] . '&' . $pageParms . '">' . $arrLine['text'] . '</a>';
											break;
										case 'delete':
											if($rs['f_childCount'] > 0){
												echo '<a href="#nolink" onclick="HintAndTurn(\'该数据含有下级数据，请先删除其下级数据！\',\'#nolink\')"><font color="#ff0000">' . $arrLine['text'] . '</font></a>';
											}else{
												echo '<a href="#nolink" onclick="HintAndTurn(\'确定要删除本数据吗？删除后，数据无法再恢复！\',\'?clause=del_info&cid=' . $rs['f_categoryId'] . '&' . $pageParms . '\')">' . $arrLine['text'] . '</a>';
											}
											break;
										case 'url':
										
											break;
									}
									break;
								case 'checkbox':
									echo '<input name="' . $arrLine['name'] . '" id="' . $arrLine['name'] . '_' . $rs['id'] . '" type="checkbox" value="' . $rs[$arrLine['field']] . '" />';
									if($arrLine['text']){
										echo '<label for="' . $arrLine['name'] . '_' . $rs['id'] . '">' . $arrLine['text'] . '</label>';
									}
									break;
								default:
									//field
									if($arrLine['field'] == 'f_title'){//特殊处理
										for($cj = 1;$cj <= $rs['f_depth'];$cj++){
											echo '&#12288;';
										}
										if($rs['f_childCount'] > 0){
											echo '╋ ';
											if($arrLine['color'] || $arrLine['bgColor']){
												echo '<span style="display:inline-block; padding:0 5px;';
												if(strlen($rs[$arrLine['color']]) > 0){
													echo 'color:' , $rs[$arrLine['color']] , ';';
												}
												if(strlen($rs[$arrLine['bgColor']]) > 0){
													echo 'background-color:' , $rs[$arrLine['bgColor']] , ';';
												}
												echo '">';
											}
											echo ' <b>' . $rs['f_title'] . '</b>(' . $rs['f_childCount'] . ')';
											if($arrLine['color'] || $arrLine['bgColor']){
												echo '</span>';
											}
										}else{
											echo '├ ';
											if($arrLine['color'] || $arrLine['bgColor']){
												echo '<span style=" display:inline-block; padding:0 5px;';
												if(strlen($rs[$arrLine['color']]) > 0){
													echo 'color:' , $rs[$arrLine['color']] , ';';
												}
												if(strlen($rs[$arrLine['bgColor']]) > 0){
													echo 'background-color:' , $rs[$arrLine['bgColor']] , ';';
												}
												echo '">';
											}
											echo $rs['f_title'];
											if($arrLine['color'] || $arrLine['bgColor']){
												echo '</span>';
											}
										}
									}else{
										if($arrLine['url']){
											echo '<a href="';
											echo $this->replace_url_1parms_rs($arrLine['url'],$rs);
											echo '"';
											if($arrLine['target']){
												echo ' target="' . $arrLine['target'] . '"';
											}
											echo '>';
										}
		
										if(strlen($rs[$arrLine['field']]) > 0){
											echo $rs[$arrLine['field']];
										}else if($arrLine['noe']){
											echo $arrLine['noe'];
										}
										
										if($arrLine['url']){
											echo '</a>';
										}
									}
									break;
							}//end switch
							
							if($arrLine['ehtml']){
								echo $arrLine['ehtml'];
							}
						}//end if
					}//end for
					
					echo '</div>';
					echo '</td>';
				}//end foreach
				
				echo '</tr>';
			}
		}else{
			echo '<tr>';
			echo '<td class="tac" colspan="' . $tdCount . '"><div>未找到相关数据</div></td>';
			echo '</tr>';
		}
		
		echo '</table>';
	}

	function save_add_info(){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		//分类名称
		$f_title = FPostStr('f_title');
		if($f_title == ''){
			FJS_AB('请填写名称！');
		}
	
		//父id、深度、同根id
		$f_parentId = FPostInt('f_parentId');
		$f_depth = 0;
		$f_rootId = 0;
		if($f_parentId > 0){
			$rs = $DB->getDRow("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_categoryId = {$f_parentId}");
			if(!$rs){
				FJS_AB('抱歉，未找到您选择的上级数据！');
			}else{
				$f_depth = (int)$rs['f_depth'] + 1;
				$f_rootId = $rs['f_rootId'];
			}
		}else{
			$rs = $DB->getDRow("select max(f_rootId) as maxRootId from `{$config['config']['dbTable']}` where f_typeId = {$tid}");
			if(!$rs){
				$f_rootId = 1;
			}else{
				$f_rootId = (int)$rs['maxRootId'];
				if($f_rootId <= 0){
					$f_rootId = 1;
				}else{
					$f_rootId += 1;
				}
			}
		}
		
		//创建分类id
		$rs = $DB->getDRow("select max(f_categoryId) as maxCid from `{$config['config']['dbTable']}` where f_typeId = {$tid}");
		if(!$rs){
			$f_categoryId = 1;
		}else{
			$f_categoryId = (int)$rs['maxCid'];
			if($f_categoryId <= 0){
				$f_categoryId = 1;
			}else{
				$f_categoryId += 1;
			}
		}
		
		//写入数据库
		$sqlFVArr = array(
			'f_typeId'=>$tid,
			'f_categoryId'=>$f_categoryId,
			'f_title'=>$f_title,
			'f_rootId'=>$f_rootId,
			'f_depth'=>$f_depth,
			'f_parentId'=>$f_parentId,
			'f_childCount'=>0,
			'f_childIds'=>$f_categoryId,
			'f_sort'=>999999,
			'f_addTime'=>FNow(),
		);
		//遍历post的数据，增加其它字段
		foreach($_POST as $key=>$val) {
			if(substr($key,0,2) == 'f_'){
				//不存在的数据才加入
				if(stripos(',id,f_typeId,f_categoryId,f_title,f_rootId,f_depth,f_parentId,f_childCount,f_childIds,f_sort,f_addTime,',',' . $key . ',') === false){
					if(substr($key,0,7) == 'f_html_'){
						//如果是ckeditor里过来的，非html标签中的'"会被ckeditor转义为&quot;形式
						//这里的转义'"转为\'\"，是过滤从textarea中过来的数据
						$sqlFVArr[$key] = FAddslashes($val);
					}else{
						$sqlFVArr[$key] = FSHtmlEncode($val);
					}
				}
			}
		}
		//正式写入
		$affectId = $DB->insert($config['config']['dbTable'],$sqlFVArr);
		
		//执行修正
		$this->G_f_sort = 0;
		$this->chk_repair();
		
		global $pageParms;
		if($pageParms){
			FJS_AT('添加成功！',"?{$pageParms}");
		}else{
			FJS_AT('添加成功！',"?tid={$tid}");
		}
	}
	
	function child_count($pid){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		$count = $DB->counter($config['config']['dbTable'],'id',"f_typeId = {$tid} and f_parentId = {$pid}");
		
		return $count;
	}
	
	function child_append($pid){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		$list = array();
		$query = $DB->query("select *,(select count(id) from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_parentId = a.f_categoryId) as childCount from `{$config['config']['dbTable']}` a where f_typeId = {$tid} and f_parentId = {$pid} order by f_rootId asc,f_sort asc");
		if($DB->num_rows($query) > 0){
			while($rs = $DB->fetch_array($query)){
				$list[] = $rs['f_categoryId'];
				
				//有子分类才递归
				if((int)$rs['childCount'] > 0){
					$list[] = $this->child_append($rs['f_categoryId']);
				}
			}
		}
		
		return implode(',',$list);
	}
	
	//根据f_parentId递归检查并修正本tid分类的：
	//1、f_rootId
	//2、f_depth
	//3、f_parentIds
	//4、f_childCount
	//5、f_childIds
	//6、f_sort
	function chk_repair($f_parentId = 0,$f_rootId = 0,$f_depth = 0,$f_parentIds = ''){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		$query = $DB->query("select *,(select count(id) from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_parentId = a.f_categoryId) as childCount from `{$config['config']['dbTable']}` a where f_typeId = {$tid} and f_parentId = {$f_parentId} order by f_rootId asc,f_sort asc");
		if($DB->num_rows($query) > 0){
			while($rs = $DB->fetch_array($query)){
				if($f_parentId == 0){
					$f_rootId = $rs['f_rootId'];
					$f_depth = 0;
					$f_parentIds = '';
				}
				
				//无子分类，不递归
				if((int)$rs['childCount'] <= 0){
					$f_childCount = 0;
					$f_childIds = $rs['f_categoryId'];
				}else{
					//即时统计
					$f_childCount = (int)$rs['childCount'];//这个方法也可以：$this->child_count($rs['f_categoryId']);
					$f_childIds = $rs['f_categoryId'] . ',' . $this->child_append($rs['f_categoryId']);
				}
				
				//排序
				$this->G_f_sort++;
				
				//更新
				$sqlFVArr = array(
					'f_rootId'=>$f_rootId,
					'f_depth'=>$f_depth,
					'f_parentIds'=>$f_parentIds,
					'f_childCount'=>$f_childCount,
					'f_childIds'=>$f_childIds,
					'f_sort'=>$this->G_f_sort,
					'f_updateTime'=>FNow(),
				);
				$affectId = $DB->update($config['config']['dbTable'],$sqlFVArr,"id = {$rs['id']}");

				//递归，统计本类的子类
				if((int)$rs['childCount'] > 0){
					//更新给下级分类使用
					//parentId
					$N_f_parentIds = $f_parentIds;
					if($N_f_parentIds != ''){
						$N_f_parentIds .= ',';
					}
					$N_f_parentIds .= $rs['f_categoryId'];
					//depth
					$N_f_depth = $f_depth + 1;

					$this->chk_repair($rs['f_categoryId'],$f_rootId,$N_f_depth,$N_f_parentIds);
				}
			}
		}
		
		//echo '|' . $DB->queryCounter . '|';
	}
	
	function get_select_options($selId,$depth){
		global $DB;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		$list = array();
		$query = $DB->query("select * from `{$config['config']['dbTable']}` where f_typeId = {$tid} and f_depth < {$depth} order by f_rootId asc,f_sort asc");
		if($DB->num_rows($query) > 0){
			while($rs = $DB->fetch_array($query)){
				$list[] = '<option value="' . $rs['f_categoryId'] . '"';
				if($selId == $rs['f_categoryId']){
					$list[] = ' selected="selected"';
				}
				$list[] = '>';
				for($ci = 1;$ci <= $rs['f_depth'];$ci++){
					$list[] = '&#12288;';
				}
				if($rs['f_childCount'] > 0){
					$list[] = '╋ ';
				}else{
					$list[] = '├ ';
				}
				$list[] = $rs['f_title'] . '</option>';
			}
		}
		
		return implode($list);
	}
	
	function add_info(){
		$this->operate_record('add');
	}
	
	//$operateType = add / edit
	function operate_record($operateType){
		global $DB;
		global $pageParms;
		global $admin_category_config; $config = $admin_category_config;
		
		global $tid;
		global $cid;
		global $pid;
		
		//读取数据的条件，仅用于edit
		$where = '';
		$whereParms = '';
		if($operateType == 'edit'){
			$where = trim($config['record'][$operateType]['config']['where']);
			//加防更新出错，任何情况下，必须有where，否则更新将是批量的、出错就麻烦了
			if(strlen($where) <= 0){
				FJS_AB('where条件出错，请检查！');
			}
			if(strtolower(substr($where,0,6)) != 'where '){
				$where = 'where ' . $where;
			}

			$rs = $DB->getDRow("select * from `{$config['config']['dbTable']}` {$where}");
			if(!$rs){
				FJS_AB('抱歉，未找到您指定的数据！');
			}
			
			$whereParms = trim($config['record'][$operateType]['config']['whereParms']);
			if(!$whereParms){
				$whereParms = $this->create_parms_from_where($where);
			}
		}
		
		//表单头
		echo '<form action="?clause=save_' , $operateType , '_info&' , $pageParms , '&' , $whereParms , '" method="post" name="myForm" id="myForm" onSubmit="return SubmitAllBtnDis();">';
		
		//隐藏框
		$arr = $config['record'][$operateType]['config']['hidden'];
		foreach($arr as $key=>$val){
			echo '<input name="' . $key . '" id="' . $key . '" type="hidden" value="';
			//添加模式下、并且key的前两个字符=f_才从数据库中读取
			//substr($key,0,2) == 'f_'
			if($operateType == 'edit' && array_key_exists($key,$rs)){
				echo $rs[$key];
			}else{
				//echo $this->replace_hidden_val($val);
				echo $val;
			}
			echo '" />';
		}
		
		//左、右列的css样式
		$leftClass = $config['record'][$operateType]['config']['class']['left'];
		$rightClass = $config['record'][$operateType]['config']['class']['right'];
		$leftStyle = $config['record'][$operateType]['config']['style']['left'];
		$rightStyle = $config['record'][$operateType]['config']['style']['right'];
		//构建左单元格html
		$leftTd = '<td';
		if($leftClass){
			$leftTd .= ' class="' . $leftClass . '"';
		}
		if($leftStyle){
			$leftTd .= ' style="' . $leftStyle . '"';
		}
		$leftTd .= '>';
		//构建右单元格html
		$rightTd = '<td';
		if($rightClass){
			$rightTd .= ' class="' . $rightClass . '"';
		}
		if($rightStyle){
			$rightTd .= ' style="' . $rightStyle . '"';
		}
		$rightTd .= '>';
		
		//title
		$title = $config['record'][$operateType]['config']['title'];
		
		//table头
		echo '<table width="98%" border="0" align="center" cellpadding="2" cellspacing="1" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="2" class="title">';
		echo $title;
		echo '</td>';
		echo '</tr>';
		
		//inputs
		$inputs = $config['record'][$operateType]['inputs'];
		foreach($inputs as $key=>$val){
			echo '<tr>';
			
			echo $leftTd;
			echo $key;
			echo '</td>';
			
			echo $rightTd;
			//遍历右侧单元格里的元素
			$valCount = count($val);
			for($ci = 0;$ci < $valCount;$ci++){
				//取得数组
				$arr = $val[$ci];
				//遍历类型
				switch($arr['type']){
					case 'color':
						echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '" type="text"';
						
						$colorVal = '';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								$colorVal = $rsO[$arr['field']];
							}else{
								$colorVal = $rs[$arr['field']];
							}
						}else if(strlen($arr['value']) > 0){
							$colorVal = $arr['value'];
						}
						
						echo ' value="' , $colorVal , '"';
						
						if($arr['maxlength']){
							echo ' maxlength="' , $arr['maxlength'] , '"';
						}
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						echo ' />';
						
						echo '<script type="text/javascript">';
						if(strlen($colorVal) > 0){
							if($arr['for'] == 'text'){
								echo '$("#' , $arr['field'] , '").css("color","' , $colorVal , '");';
							}else{
								echo '$("#' , $arr['field'] , '").css("background-color","' , $colorVal , '");';
							}
						}
						echo '$(function(){';
						echo '$("#' , $arr['field'] , '").colorpicker({';
						echo 'fillcolor:true,';
						echo 'success:function(o,color){';
						if($arr['for'] == 'text'){
							echo '$(o).css("color",color);';
						}else{
							echo '$(o).css("background-color",color);';
						}
						echo '}';
						echo ',reset:function(o,color){$(o).val("");$(o).css("background-color","inherit");}';
						echo '});';
						echo '});';
						echo '</script>';

						break;
					case 'imgShow':
						echo '<img';
						//遍历属性
						foreach($arr as $key1=>$val1){
							switch($key1){
								case 'type':
									//跳过
									break;
								case 'field':
									//field字段则从数据库中读取
									echo ' src="' . $rs[$val1] . '"';
									break;
								default:
									//其它字段直接赋值
									echo ' ' . $key1 . '="' . $val1 . '"';
									break;
							}
						}
						echo ' />';
						break;
					case 'article':
						echo $arr['content'];
						break;
					case 'imgUpload':
						echo '<input name="' . $arr['field'] . '" id="' . $arr['field'] . '" type="text"';
						if($operateType == 'edit'){
							echo ' value="' . $rs[$arr['field']] . '" ';
						}else if(strlen($arr['value']) > 0){
							echo ' value="' . $arr['value'] . '" ';
						}
						if($arr['maxlength']){
							echo ' maxlength="' . $arr['maxlength'] . '"';
						}
						if($arr['class']){
							echo ' class="' . $arr['class'] . '"';
						}
						echo ' />';
						if($arr['required']){
							echo ' <span class="mustInput">*</span>';
						}
						if($arr['ehtml']){
							echo ' ' . $arr['ehtml'];
						}
						echo ' <a onclick="ShowUpLoadFile(\'' . $arr['field'] . '\');" href="#nolink" style="color:#ff0000;">[点击上传]</a>';
						//echo '[<a style="cursor:pointer;" onClick="AdminPicAutoSmall(\'f_picBig\',\'f_picSmall\');"><font color="#ff0000">自动缩小</font></a>] ';
						echo '<script type="text/javascript">$("#' . $arr['field'] . '").FN_CluetipInputPic();</script>';
						break;
					case 'text':
					case 'password':
						if($arr['shtml']){
							echo ' ' . $arr['shtml'];
						}
						echo '<input name="' . $arr['field'] . '" id="' . $arr['field'] . '" type="' . $arr['type'] . '"';
						if($operateType == 'edit'){
							echo ' value="' . $rs[$arr['field']] . '" ';
						}else if(strlen($arr['value']) > 0){
							echo ' value="' . $arr['value'] . '" ';
						}
						if($arr['maxlength']){
							echo ' maxlength="' . $arr['maxlength'] . '"';
						}
						if($arr['class']){
							echo ' class="' . $arr['class'] . '"';
						}
						echo ' />';
						if($arr['required']){
							echo ' <span class="mustInput">*</span>';
						}
						if($arr['ehtml']){
							echo ' ' . $arr['ehtml'];
						}
						break;
					case 'radio':
						$cj = 0;
						foreach($arr['list'] as $key1=>$val1){
							$cj++;
							echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '_' , $cj , '" type="' , $arr['type'] , '"';
							echo ' value="' , $key1 , '"';
							if($operateType == 'edit'){
								if($rs[$arr['field']] == $key1){
									echo ' checked="checked"';
								}
							}else if(strlen($arr['value']) > 0){
								if($arr['value'] == $key1){
									echo ' checked="checked"';
								}
							}
							echo ' />';
							echo '<label for="' , $arr['field'] , '_' , $cj , '">' , $val1 , '</label> &nbsp; ';
						}
						if($arr['required']){
							echo ' <span class="mustInput">*</span>';
						}
						if($arr['ehtml']){
							echo ' ' , $arr['ehtml'];
						}
						break;
					case 'textarea':
						echo '<textarea name="' . $arr['field'] . '" id="' . $arr['field'] . '"';
						if($arr['class']){
							echo ' class="' . $arr['class'] . '"';
						}
						echo '>';
						if($operateType == 'edit'){
							echo $rs[$arr['field']];
						}else if(strlen($arr['value']) > 0){
							echo $arr['value'];
						}
						echo '</textarea>';
						if($arr['required']){
							echo ' <span class="mustInput">*</span>';
						}
						if($arr['ehtml']){
							echo ' ' . $arr['ehtml'];
						}
						break;
					case 'ckeditor':
						if($operateType == 'edit'){
							FCreateCkeditor($arr['field'],$rs[$arr['field']],$arr['ckc']);
						}else if(strlen($arr['value']) > 0){
							FCreateCkeditor($arr['field'],$arr['value'],$arr['ckc']);
						}else{
							FCreateCkeditor($arr['field'],'',$arr['ckc']);
						}
						if($arr['required']){
							echo ' <span class="mustInput">*</span>';
						}
						if($arr['ehtml']){
							echo ' ' . $arr['ehtml'];
						}
						break;
					case 'cid':
						echo '<select name="' . $arr['field'] . '" id="' . $arr['field'] . '">';
						echo '<option value="0">-=作为一级分类=-</option>';
						if($operateType == 'edit'){
							echo $this->get_select_options($rs[$arr['field']],$config['config']['depth']);
						}else{
							echo $this->get_select_options($pid,$config['config']['depth']);
						}
						echo '</select>';
						if($arr['required']){
							echo ' <span class="mustInput">*</span>';
						}
						if($arr['ehtml']){
							echo ' ' . $arr['ehtml'];
						}
						break;
				}
			}
			echo '</td>';
			
			echo '</tr>';
		}
		
		//按钮
		echo '<tr>';
		echo '<td colspan="2" align="center">';
		//遍历
		$button = $config['record'][$operateType]['config']['button'];
		foreach($button as $key=>$val){
			switch($key){
				case 'submit':
					echo '<input type="submit" name="button_submit_" value="';
					echo $val;
					echo '" class="bttn" />';
					break;
				case 'back':
					echo '<input type="button" name="button_back_" value="';
					echo $val;
					echo '" class="bttn" onclick="history.back();" />';
					break;
			}
		}
		echo '</td>';
		echo '</tr>';
		
		echo '</table>';
		echo '</form>';
	}
	
	function menu(){
		global $pageParms;
		global $admin_category_config; $config = $admin_category_config;
		global $tid;
		
		if(count($config['menu']) <= 0){
			return;
		}
		
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td class="title">' . $config['menu']['title'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="tac"><div>';
		
		$links = $config['menu']['links'];
		foreach($links as $key=>$val){
			if($val['shtml']){
				echo $val['shtml'];
			}
			if($val['url']){
				echo '<a href="' . $this->replace_url_parms_GP($val['url']) . '"';
				if($val['target']){
					echo ' target="' . $val['target'] . '"';
				}
				echo '>' . $key . '</a>';
			}
			if($val['ehtml']){
				echo $val['ehtml'];
			}
		}
		//echo ' || ';
		//echo '<a href="?tid=' . $tid . '&clause=create_html" style="color:#ff0000;">生成静态(重要)</a>';
		echo '</div></td>';
		echo '</tr>';
		echo '</table>';
		echo '<br />';
	}
	
	/*
	function replace_hidden_val($val){
		$temp = $val;
		switch($val){
			case '{now}':
				$temp = date("Y-m-d H:i:s");
				break;
		}
		return $temp;
	}
	*/

	//正则取出{...}
	//然后从get和post中获取相关的数据并替换
	//替换完成后，将不再存在{}
	function replace_url_parms_GP($url){
		global $pageParms;
		
		$temp = $url;
		$temp = str_replace('{pageParms}',$pageParms,$temp);
		
		preg_match_all("/{(.+?)}/i", $temp, $matches);
		//$matches[0] 将包含与整个模式匹配的文本，
		//$matches[1] 将包含与第一个捕获的括号中的子模式所匹配的文本，以此类推
		foreach($matches[0] as $matche){
			$key = substr($matche,1);
			$key = substr($key,0,strlen($key) - 1);
			$val = '';
			if($key){
				$val = FGetStr($key);
				if(!$val){
					$val = FPostStr($key);
				}
			}
			$temp = str_replace($matche,$val,$temp);
		}
		
		return $temp;
	}
	
	//从rs取得数据替换url里的参数
	//只替换rs存在的。如果替换后，还存在{}最后调用replace_url_parms_GP处理
	function replace_url_1parms_rs($url,$rs){
		global $pageParms;
		
		$temp = $url;
		$temp = str_replace('{pageParms}',$pageParms,$temp);
		
		preg_match_all("/{(.+?)}/i", $temp, $matches);
		//$matches[0] 将包含与整个模式匹配的文本，
		//$matches[1] 将包含与第一个捕获的括号中的子模式所匹配的文本，以此类推
		foreach($matches[0] as $matche){
			$key = substr($matche,1);
			$key = substr($key,0,strlen($key) - 1);
			$val = '';
			if($key){
				if(array_key_exists($key,$rs)){
					$val = $rs[$key];
					
					$temp = str_replace($matche,$val,$temp);
				}
			}
		}
		
		//如果还存在{}，则从get 或 post里获取来替换
		if(stripos($temp,'{') !== false){
			$temp = $this->replace_url_parms_GP($temp);
		}
		
		return $temp;
	}
	
	//从rs取得数据替换url里的参数
	//只替换rs存在的。如果替换后，还存在{}最后调用replace_url_parms_GP处理
	function create_parms_from_where($where){
		//只能处理：
		//where a = 1 and b = 'c'
		//无法处理其它情况（含括号、or等）
		$temp = $where;
		if(stripos($temp,' or ') !== false)return;
		if(stripos($temp,'(') !== false)return;
		if(stripos($temp,'<>') !== false)return;
		
		if(substr($temp,0,6) == 'where '){
			$temp = substr($temp,6);
		}

		$temp = str_replace(' and ','&',$temp);
		$temp = str_replace("'",'',$temp);
		$temp = str_replace(' ','',$temp);
			
		$arr = explode('&',$temp);
				
		$parms = '';
		for($ci = 0;$ci < count($arr);$ci++){
			$kv = explode('=',$arr[$ci]);
			
			if(count($kv) == 2){
				if($parms){
					$parms .= '&';
				}
				
				$parms .= $kv[0];
				$parms .= '=';
				$parms .= $kv[1];
			}
		}
		
		$temp = $parms;
		//if($temp){
		//	$temp = '&' . $temp;
		//}

		return $temp;
	}
	
}