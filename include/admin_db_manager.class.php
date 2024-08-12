<?php
/*
2015-10-31 更新


*/

class CAdminDBManager{
	function __construct(){

	}
	
	function __destruct(){
		
	}
	
	function run(){
		global $clause;
		
		switch($clause){
			case 'del_info':
				$this->del_info();
				break;
			case 'save_page_sort':
				$this->save_page_sort();
				break;
			case 'batcf_bool_info':
				$this->batcf_bool_info();
				break;
			case 'batcf_del_info':
				$this->batcf_del_info();
				break;
			case 'update_bool':
				$this->update_bool();
				break;
			case 'update_field':
				$this->update_field();
				break;
			case 'save_add_info':
				$this->save_add_info();
				break;
			case 'add_info':
				$this->menu();
				$this->add_info();
				break;
			case 'save_edit_info':
				$this->save_edit_info();
				break;
			case 'edit_info':
				$this->menu();
				$this->edit_info();
				break;
			case 'bat_update_save_list':
				$this->bat_update_save_list();
				break;
			default:
				$this->menu();
				$this->main();
				break;
		}
	}
	
	function bat_update_save_list(){
		global $DB;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		
		$batUpdateIds = $_POST['batUpdateIds'];
		$batUpdateInputNames = FPostStr('batUpdateInputNames');
		
		if(!is_array($batUpdateIds)){
			FJS_AB('抱歉，没有数据需要更新(E001)！');
		}
		if(count($batUpdateIds) <= 0){
			FJS_AB('抱歉，没有数据需要更新(E002)！');
		}
		if(strlen($batUpdateInputNames) <= 0){
			FJS_AB('抱歉，需要更新的字段出错(E003)！');
		}
		
		$fieldArr = explode('|',$batUpdateInputNames);
		
		$dbTable = $config['config']['dbTable'];
		
		foreach($batUpdateIds as $id){
			$sql = "update `{$dbTable}` set ";
			for($ci = 0;$ci < count($fieldArr);$ci++){
				$field = $fieldArr[$ci];
				$val = FPostStr($field . '_' . $id);
				
				if($ci > 0){
					$sql .= ',';
				}
				
				$sql .= "`{$field}` = '{$val}'";
			}
			$sql .= " where id = '{$id}'";
			$DB->query($sql);
		}

		FJS_AT('批量更新成功！',FPrevUrl());
	}

	function menu(){
		global $pageParms;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		
		if(count($config['menu']) <= 0){
			return;
		}
		
		echo '<table width="98%" border="0" align="center" cellpadding="2" cellspacing="1" class="borderTable">';
		echo '<tr>';
		echo '<td class="title">' , $config['menu']['title'] , '</td>';
		echo '</tr>';
		
		$links = $config['menu']['links'];
		if($links){
			echo '<tr>';
			echo '<td class="tac"><div>';
			foreach($links as $key=>$val){
				if($val['shtml']){
					echo $val['shtml'];
				}
				if($val['url']){
					echo '<a href="' , $this->replace_url_parms_GP($val['url']) , '"';
					if($val['target']){
						echo ' target="' , $val['target'] , '"';
					}
					echo '>' , $key , '</a>';
				}
				if($val['ehtml']){
					echo $val['ehtml'];
				}
			}
			echo '</div></td>';
			echo '</tr>';
		}
		
		//search
		$search = $config['menu']['search'];
		if($search){
			echo '<tr>';
			echo '<td class="tac">';
						
			echo '<form action="?" method="get" name="searchForm" id="searchForm" onSubmit="return SubmitAllBtnDis();">';
			echo FParmsToHiddenInput($pageParms);
			
			foreach($search as $arr){
				if(strlen($arr['shtml']) > 0){
					echo $arr['shtml'];
				}
				
				$val = FGetStr($arr['name']);
				if(strlen($val) <= 0){
					$val = $arr['value'];
				}
				
				switch($arr['type']){
					case 'select':
						echo '<select name="' , $arr['name'] , '" id="' , $arr['name'] , '">';
						if($arr['options']){
							echo $arr['options'];
						}else if($arr['assoDbTable']){
							echo $arr['defaultOption'];
							FGetDbTableSelectOptions($arr['assoDbTable']['sql'],$arr['assoDbTable']['vField'],$arr['assoDbTable']['tField']);
						}
						echo '</select>';
						if(strlen($val) > 0){
							echo '<script type="text/javascript">';
							echo '$("#' , $arr['name'] , '").val("' , $val , '");';
							echo '</script>';
						}
						break;
					case 'text':
						echo '<input type="' , $arr['type'] , '" name="' , $arr['name'] , '" id="' , $arr['name'] , '"';
						echo ' value="' , $val , '"';
						echo ' placeholder="' , $arr['placeholder'] , '"';
						if(strlen($arr['onfocus']) > 0){
							echo ' onfocus="' , $arr['onfocus'] , '"';
						}
						echo ' />';
						break;
					case 'submit':
						echo '<input type="' , $arr['type'] , '" name="' , $arr['name'] , '" id="' , $arr['name'] , '"';
						echo ' value="' , $val , '"';
						echo ' />';
						break;
				}
				
				if(strlen($arr['ehtml']) > 0){
					echo $arr['ehtml'];
				}
			}
			
			echo '</form>';
			
			echo '</td>';
			echo '</tr>';
		}
		
		echo '</table>';
		echo '<br />';
	}
	
	function main(){
		global $DB;
		global $pageParms;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		
		//遍历search，把地址栏参数加上search字段
		//如果页面指字了whereSearch，这里不再自动串接
		if(strlen($config['list']['config']['whereSearch']) > 0){
			$hasWhereSearch = true;
			$whereSearch = $config['list']['config']['whereSearch'];
		}else{
			$hasWhereSearch = false;
			$whereSearch = '';	
		}
		
		foreach($_GET as $key=>$val){
			if(substr($key,0,7) == 'search_'){
				$pageParms .= '&';
				$pageParms .= $key;
				$pageParms .= '=';
				$pageParms .= urlencode($val);
				
				//需要串接
				if($hasWhereSearch == false){
					switch(substr($key,0,11)){
						case 'search_tis_':
						case 'search_tie_':
							//array('type'=>'text','name'=>'search_tis_f_addTime','placeholder'=>'下单时间-开始','ehtml'=>' ','onfocus'=>'WdatePicker({dateFmt:\'yyyy-M-d H:mm:ss\'})'),
							//array('type'=>'text','name'=>'search_tie_f_addTime','placeholder'=>'下单时间-结束','ehtml'=>' ','onfocus'=>'WdatePicker({dateFmt:\'yyyy-M-d H:mm:ss\'})'),
							//key = val
							$sKey = substr($key,11);
							$sVal = $val;//时间
							
							if(strlen($sVal) <= 0){
								break;
							}

							if(strlen($whereSearch) > 0){
								$whereSearch .= ' and ';
							}
							if(substr($key,0,11) == 'search_tis_'){
								$whereSearch .= "timestampdiff(second,'" . $sVal . "',`" . $sKey . "`) >= 0 ";
							}else{
								$whereSearch .= "timestampdiff(second,`" . $sKey . "`,'" . $sVal . "') >= 0 ";
							}
							break;
						case 'search_kve_':
							//key = val
							$sKey = substr($key,11);
							$sVal = $val;
							
							if(strlen($sVal) <= 0){
								break;
							}
							if($sVal == '_NULL_'){
								$sVal = '';
							}
							
							if(strlen($whereSearch) > 0){
								$whereSearch .= ' and ';
							}
							$whereSearch .= "`" . $sKey . "` = '" . $sVal . "'";
							break;
						case 'search_ves_':
							//val = (key1=val1&key2=val2)
							
							if(strlen($val) <= 0){
								break;
							}
							
							$searchTempArr = explode('&',$val);
							for($searchCi = 0;$searchCi < count($searchTempArr);$searchCi++){
								$searchTempLineArr = explode('=',$searchTempArr[$searchCi]);
								$sKey = $searchTempLineArr[0];
								$sVal = $searchTempLineArr[1];
								
								if(strlen($whereSearch) > 0){
									$whereSearch .= ' and ';
								}
								$whereSearch .= "`" . $sKey . "` = '" . $sVal . "'";
							}
							break;
						case 'search_key_':
							//key = search_key_? = this.value = like_f_xxx || equa_f_xxx
							//val = search_val_? = this.value
							$sLE = substr($val,0,5);
							$sKey = substr($val,5);
							$sVal = FGetStr('search_val_' . substr($key,11));
							
							if(strlen($sKey) <= 0 | strlen($sVal) <= 0){
								break;
							}
							
							if(strlen($whereSearch) > 0){
								$whereSearch .= ' and ';
							}
							if($sLE == 'like_'){
								$whereSearch .= "`" . $sKey . "` like '%" . $sVal . "%'";
							}else if($sLE == 'equa_'){
								$whereSearch .= "`" . $sKey . "` = '" . $sVal . "'";
							}
							break;
					}
				}
			}
		}
		
		//echo $whereSearch;
		
		//其它表字段串接
		//array('type'=>'otfv','field'=>'categoryTitle','sql'=>"select f_title from `t_category` where f_typeId = a.f_typeId and f_categoryId = a.f_categoryId"),
		$sqlOtfvFields = '';
		foreach($config['list']['columns'] as $key1=>$val1){
			for($ci = 0;$ci < count($val1);$ci++){
				//key=>val的，使用[index]无法获得
				$arrLine = $val1[$ci];
				if(is_array($arrLine)){
					switch($arrLine['type'])
					{
						case 'otfv':
							//需要特殊处理
							//other table field value
							
							$sqlOtfvFields .= ',(';
							$sqlOtfvFields .= $arrLine['sql'];
							$sqlOtfvFields .= ' LIMIT 1) as ';
							$sqlOtfvFields .= $arrLine['field'];
							break;
					}//end switch
				}//end if
			}//end for
		}//end foreach

		$tdCount = count($config['list']['columns']);
		
		//表格头
		echo '<form action="" method="post" name="mainForm" id="mainForm" onSubmit="return SubmitAllBtnDis();">';
		echo '<table width="98%" border="0" align="center" cellpadding="2" cellspacing="1" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="' , $tdCount , '" class="title">' , $config['list']['config']['title'] , '</td>';
		echo '</tr>';
		
		//子标题
		echo '<tr>';
		//子array里的width为sTitle用
		//height、class为td用
		foreach($config['list']['columns'] as $key=>$val){
			echo '<th';
			if($val['width']){
				echo ' width="' , $val['width'] , '"';
			}
			if($val['title']){
				echo ' title="' , $val['title'] , '"';
			}
			echo ' class="sTitle">';
			echo $key;
			echo '</th>';
		}
		echo '</tr>';
		
		//当前是第几行
		$rsRowIndex = 0;
		//是否存在批量排序
		$isortTdIndex = 0;
		//是否是批量更新的第1个input，以及batUpdate的input列表
		$batUpdateInputIndex = 0;
		$batUpdateInputNames = '';
		
		//where
		$where = trim($config['list']['config']['where']);
		if(strlen($where) > 0){
			if(strtolower(substr($where,0,6)) != 'where '){
				$where = 'where ' . $where;
			}
			
			if(strlen($whereSearch) > 0){
				$where .= ' and ' . $whereSearch;
			}
		}else{
			if(strlen($whereSearch) > 0){
				$where = 'where ' . $whereSearch;
			}
		}
		//加上搜索项 searcf_f_xxx

		//sql语句及查询
		$dbTableId = $config['config']['dbTableId'];
		if(strlen($dbTableId) <= 0){
			$dbTableId = 'id';
		}
		$recordCount = $DB->counter($config['config']['dbTable'],$dbTableId,$where);
		$pagerSize = (int)$config['list']['config']['pagerSize'];
		if($pagerSize <= 0){
			$pagerSize = 15;
		}
		$pager_config = array(
			"record_count"=>$recordCount,
			"pager_size"=>$pagerSize,
			"pager_index"=>(int)$_GET["page"],
			"show_front_btn"=>false,
			"show_last_btn"=>false,
			"skin"=>'blue'
		);
		$pager = new CPager2($pager_config);
		$sqlRecordStartIndex = $pager->getSqlRecordStartIndex();
		unset($query);
		if($recordCount > 0){
			$sql = "select * from `{$config['config']['dbTable']}` a {$where} order by {$config['list']['config']['orderBy']} LIMIT {$sqlRecordStartIndex},{$pagerSize}";
			$sql = "select *{$sqlOtfvFields} from ({$sql}) a";
			$query = $DB->query($sql);
		}
		if($recordCount > 0){
			while($rs = $DB->fetch_array($query)){
				$rsRowIndex++;
				
				echo '<tr>';
				
				//遍历td
				$cj = 0;
				$batUpdateInputIndex = 0;
				foreach($config['list']['columns'] as $key1=>$val1){
					$cj++;
					
					//属性
					echo '<td';
					if($val1['height']){
						echo ' height="' , $val1['height'] , '"';
					}
					if($val1['class']){
						echo ' class="' , $val1['class'] , '"';
					}
					if($val1['valign']){
						echo ' valign="' , $val1['valign'] , '"';
					}
					echo '>';
					
					//遍历td里的数据，可能有多个字段数据显示在同一个td里
					for($ci = 0;$ci < count($val1);$ci++){
						//key=>val的，使用[index]无法获得
						$arrLine = $val1[$ci];
						if(is_array($arrLine)){
							//如果有检测内容是否大于0，view check length
							if($arrLine['vcl']){
								if(strlen($rs[$arrLine['field']]) <= 0){
									continue;
								}
							}
							
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
								case 'article':
									echo $this->replace_url_parms_rs($arrLine['content'],$rs);
									break;
								case 'eval':
									eval($arrLine['php']);
									break;
								case 'otfv':
									//other table field value
									echo $rs[$arrLine['field']];
									break;
								case 'isort':
									//批量排序
									echo '<input name="sort_' , $rs['id'] , '" type="text" class="input1" maxlength="5" value="' , $rs['f_sort'] , '" />';
									$isortTdIndex = $cj;
									break;
								case 'batUpdateSelect':
									//批量更新：下拉选择
									$batUpdateInputIndex++;
									
									//如果是每行的第1个input，则增加id项
									if($batUpdateInputIndex == 1){
										echo '<input name="batUpdateIds[]" id="batUpdateIds_' , $rs['id'] , '" type="hidden" value="' , $rs['id'] , '" />';
									}
									
									echo '<select name="' , $arrLine['field'] , '_' , $rs['id'] , '" id="' , $arrLine['field'] , '_' , $rs['id'] , '">';
									if($arrLine['options']){
										echo $arrLine['options'];
									}else if($arrLine['assoDbTable']){
										echo $arrLine['defaultOption'];
										FGetDbTableSelectOptions($arrLine['assoDbTable']['sql'],$arrLine['assoDbTable']['vField'],$arrLine['assoDbTable']['tField']);
									}
									echo '</select>';

									echo '<script type="text/javascript">';
									echo '$("#' , $arrLine['field'] , '_' , $rs['id'] , '").val("' , $rs[$arrLine['field']] , '");';
									echo '</script>';

									break;
								case 'batUpdateRadio':
									//批量更新：单选按钮
									$batUpdateInputIndex++;
									
									//如果是每行的第1个input，则增加id项
									if($batUpdateInputIndex == 1){
										echo '<input name="batUpdateIds[]" id="batUpdateIds_' , $rs['id'] , '" type="hidden" value="' , $rs['id'] , '" />';
									}
									
									//累加inputNames
									if($rsRowIndex == 1){
										if(stripos('|' . $batUpdateInputNames . '|','|' . $arrLine['field'] . '|') === false){
											if(strlen($batUpdateInputNames) > 0){
												$batUpdateInputNames .= '|';
											}
										
											$batUpdateInputNames .= $arrLine['field'];
										}
									}
									
									//input项
									$batUpdateRadioCi = 0;
									foreach($arrLine['list'] as $key2=>$val2){
										$batUpdateRadioCi++;
										echo '<input name="' , $arrLine['field'] , '_' , $rs['id'] , '" id="' , $arrLine['field'] , '_' , $rs['id'] , '_' , $batUpdateRadioCi , '" type="radio"';
										echo ' value="' , $key2 , '"';
										if($rs[$arrLine['field']] == $key2){
											echo ' checked="checked"';
										}
										echo ' />';
										echo '<label for="' , $arrLine['field'] , '_' , $rs['id'] , '_' , $batUpdateRadioCi , '">' , $val2 , '</label> &nbsp; ';
									}
									break;
								case 'batUpdateText':
									//批量更新：文本输入框
									$batUpdateInputIndex++;
									
									//如果是每行的第1个input，则增加id项
									if($batUpdateInputIndex == 1){
										echo '<input name="batUpdateIds[]" id="batUpdateIds_' , $rs['id'] , '" type="hidden" value="' , $rs['id'] , '" />';
									}
									
									//累加inputNames
									if($rsRowIndex == 1){
										if(stripos('|' . $batUpdateInputNames . '|','|' . $arrLine['field'] . '|') === false){
											if(strlen($batUpdateInputNames) > 0){
												$batUpdateInputNames .= '|';
											}
										
											$batUpdateInputNames .= $arrLine['field'];
										}
									}
									
									//input项
									echo '<input name="' , $arrLine['field'] , '_' , $rs['id'] , '" id="' , $arrLine['field'] , '_' , $rs['id'] , '" type="text" value="';
									if(strlen($arrLine['valEval']) > 0){
										eval($arrLine['valEval']);
									}else{
										echo $rs[$arrLine['field']];
									}
									echo '"';
									if($arrLine['class']){
										echo ' class="' , $arrLine['class'] , '"';
									}
									if($arrLine['style']){
										echo ' style="' , $arrLine['style'] , '"';
									}
									if($arrLine['title']){
										echo ' title="' , $arrLine['title'] , '"';
									}
									if($arrLine['ondblclick']){
										echo ' ondblclick="' , $arrLine['ondblclick'] , '"';
									}
									if($arrLine['autocomplete']){
										echo ' autocomplete="' , $arrLine['autocomplete'] , '"';
									}
									if($arrLine['maxlength']){
										echo ' maxlength="' , $arrLine['maxlength'] , '"';
									}
									echo ' />';
									break;
								case 'img':
									if($rs[$arrLine['field']]){
										echo '<a id="img_bg_' , $rs['id'] , '" href="' , $rs[$arrLine['field']] , '" target="_blank"><img src="' , $rs[$arrLine['field']] , '"';
										if($arrLine['width']){
											echo ' width="' , $arrLine['width'] , '"';
										}
										if($arrLine['height']){
											echo ' height="' , $arrLine['height'] , '"';
										}
										if($arrLine['class']){
											echo ' class="' , $arrLine['class'] , '"';
										}
										if($arrLine['style']){
											echo ' style="' , $arrLine['style'] , '"';
										}
										echo ' /></a>';
									}else{
										echo '-';
									}
									if(strlen($arrLine['bgColor']) > 0){
										if(strlen($rs[$arrLine['bgColor']]) > 0){
											echo '<script type="text/javascript">';
											echo '$("#img_bg_' , $rs['id'] , '").parent().css("background-color","' , $rs[$arrLine['bgColor']] , '");';
											echo '</script>';
										}
									}
									break;
								case 'bool':
									if($rs[$arrLine['field']]){
										if($arrLine['qupdate']){
											echo '<a href="?clause=update_bool&field=' , $arrLine['field'] , '&state=0&id=' , $rs['id'] , '">'; 
										}
										echo $arrLine['ttpl'];
										if($arrLine['qupdate']){
											echo '</a>'; 
										}
									}else{
										if($arrLine['qupdate']){
											echo '<a href="?clause=update_bool&field=' , $arrLine['field'] , '&state=1&id=' , $rs['id'] , '">'; 
										}
										echo $arrLine['ftpl'];
										if($arrLine['qupdate']){
											echo '</a>'; 
										}
									}
									break;
								case 'switch':
									//怎么可能有url这个属性key？作用是？暂时不管，以后可能要删除掉
									if($arrLine['url']){
										echo '<a href="';
										echo $this->replace_url_parms_rs($arrLine['url'],$rs);
										echo '"';
										if($arrLine['target']){
											echo ' target="' , $arrLine['target'] , '"';
										}
										echo '>';
									}
									
									//case是个数组，case的key是否field的值，判断case的key是否存在
									if(array_key_exists($rs[$arrLine['field']],$arrLine['case'])){
										//存在，则显示其值
										$switchVal = $arrLine['case'][$rs[$arrLine['field']]];
										if(strpos($switchVal,'{') !== false){
											$switchVal = $this->replace_url_parms_rs($switchVal,$rs);
										}
										
										echo $switchVal;
									}else if(array_key_exists('*default*',$arrLine['case'])){
										//不存在，看是否有默认值，有的话，显示默认值
										$switchVal = $arrLine['case']['*default*'];
										if(strpos($switchVal,'{') !== false){
											$switchVal = $this->replace_url_parms_rs($switchVal,$rs);
										}
										
										echo $switchVal;
									}
									
									if($arrLine['url']){
										echo '</a>';
									}
									break;
								case 'action':
									switch($arrLine['action']){
										case 'edit':
											echo '<a href="?clause=edit_info&id=' , $rs['id'] , '&' , $pageParms , '">' , $arrLine['text'] , '</a>';
											break;
										case 'delete':
											echo '<a href="#nolink" onClick="HintAndTurn(\'确定要删除吗？数据将不可恢复！\',\'?clause=del_info&id=' , $rs['id'] , '&' , $pageParms , '\');">' , $arrLine['text'] , '</a>';
											break;
										case 'url':
											$url = $this->replace_url_parms_rs($arrLine['url'],$rs);
											echo '<a href="' , $url , '&' , $pageParms , '">' , $arrLine['text'] , '</a>';
											break;
									}
									break;
								case 'checkbox':
									echo '<input name="' , $arrLine['name'] , '" id="' , $arrLine['name'] , '_' , $rs['id'] , '" type="checkbox" value="' , $rs[$arrLine['field']] , '" />';
									if($arrLine['text']){
										echo '<label for="' , $arrLine['name'] , '_' , $rs['id'] , '">' , $arrLine['text'] , '</label>';
									}
									break;
								default:
									//field
									
									//如果有eval，则执行
									if($arrLine['eval']){
										eval($arrLine['eval']);
										continue;
									}
									
									if($arrLine['url']){
										echo '<a href="';
										echo $this->replace_url_parms_rs($arrLine['url'],$rs);
										echo '"';
										if($arrLine['target']){
											echo ' target="' , $arrLine['target'] , '"';
										}
										echo '>';
									}
	
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
									if(strlen($rs[$arrLine['field']]) > 0){
										echo $rs[$arrLine['field']];
									}else if($arrLine['noe']){
										//这个字段是什么？好像没有用过。。。
										echo $arrLine['noe'];
									}
									if($arrLine['color'] || $arrLine['bgColor']){
										echo '</span>';
									}
									
									if($arrLine['url']){
										echo '</a>';
									}
	
									break;
							}//end switch
							
							if($arrLine['ehtml']){
								echo $arrLine['ehtml'];
							}
						}//end if
					}//end for
					
					echo '</td>';
				}//end foreach
				
				echo '</tr>';
			}
		}else{
			echo '<tr>';
			echo '<td class="tac" colspan="' , $tdCount , '"><div>未找到相关数据</div></td>';
			echo '</tr>';
		}
		
		//排序
		if($isortTdIndex > 0){
			echo '<tr>';
			for($ci = 1;$ci <= $tdCount;$ci++){
				if($ci == $isortTdIndex){
					echo '<td class="tac"><input onclick="$(\'#mainForm\').attr(\'action\',\'?clause=save_page_sort&' , $pageParms , '\').submit();" type="button" name="form_button_" value="更新排序" class="bttn" /></td>';
				}else{
					echo '<td>&nbsp;</td>';
				}
			}
			echo '</tr>';
		}
		
		//多项选择的批操作
		if($recordCount > 0){
			$batch = $config['list']['config']['batch'];
			if(count($batch) > 0){
				echo '<tr>';
				echo '<td colspan="' , $tdCount , '"';
				if(strlen($batch['class']) > 0){
					echo ' class="' , $batch['class'] , '"';
				}
				echo '>';
	
				if(strlen($batch['cbName']) > 0){
					echo '<a href="#nolink" onclick="CheckBox_On(\'' , $batch['cbName'] , '\')">全选</a>&#12288;';
					echo '<a href="#nolink" onclick="CheckBox_Off(\'' , $batch['cbName'] , '\')">取消</a>&#12288;';
					echo '<a href="#nolink" onclick="CheckBox_Toggle(\'' , $batch['cbName'] , '\')">反选</a>&#12288;';
				}
				
				echo '<input name="batUpdateInputNames" id="batUpdateInputNames" value="' , $batUpdateInputNames , '" type="hidden" />';
	
				foreach($batch as $key=>$val){
					if(is_array($val)){
						if($val['shtml']){
							echo $val['shtml'];
						}
						
						echo '<input name="form_button_" value="' , $key , '" class="bttn"';
						if($val['onclick']){
							echo ' onclick="' , $val['onclick'] , '"';
						}
						echo ' type="button" />';
						
						if($val['ehtml']){
							echo $val['ehtml'];
						}
					}
				}
				
				echo '</td>';
				echo '</tr>';
			}
		}
		
		//表格尾
		echo '</table>';
		echo '</form>';
		if($recordCount > 0){
			echo '<div class="br"></div>';
			echo '<div class="pager-c">' , $pager->pager() , '</div>';
		}
	}
	
	function add_info(){
		$this->operate_record('add');
	}
	
	function save_add_info(){
		$this->save_operate_record('add');
	}
	
	function edit_info(){
		$this->operate_record('edit');
	}
	
	function save_edit_info(){
		$this->save_operate_record('edit');
	}
	
	//$operateType = add / edit
	function operate_record($operateType){
		global $DB;
		global $pageParms;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		
		//读取数据的条件，仅用于edit
		$where = '';
		$whereO = '';
		$rsONotFind = true;
		$whereParms = '';
		if($operateType == 'edit'){
			//主表
			$where = trim($config['record'][$operateType]['config']['where']);
			//加防更新出错，任何情况下，必须有where，否则更新将是批量的、出错就麻烦了
			if(strlen($where) <= 0){
				FJS_AB('where条件出错，请检查！');
			}
			if(strtolower(substr($where,0,6)) != 'where '){
				$where = 'where ' . $where;
			}

			$dbTable = $config['config']['dbTable'];
			$rs = $DB->getDRow("select * from `{$dbTable}` {$where}");
			if(!$rs){
				$ifeof = $config['record'][$operateType]['config']['ifeof'];
				if(is_array($ifeof)){
					if(count($ifeof) > 0){
						//foreach($ifeof as $key=>$val){}
						$DB->insert($dbTable,$ifeof);
						$rs = $DB->getDRow("select * from `{$dbTable}` {$where}");
					}
				}
			}
			if(!$rs){
				FJS_AB('抱歉，未找到您指定的数据！');
			}
			
			//附属表
			if($config['config']['dbTableO']){
				$whereO = trim($config['record'][$operateType]['config']['whereO']);
				if(strlen($whereO) <= 0){
					FJS_AB('附属表where条件出错，请检查！');
				}
				if(strtolower(substr($whereO,0,6)) != 'where '){
					$whereO = 'where ' . $whereO;
				}
				
				$rsO = $DB->getDRow("select * from `{$config['config']['dbTableO']}` {$where}");
				if($rsO){
					//FJS_AB('抱歉，未找到您指定的附表数据！');
					$rsONotFind = false;
				}
			}
			
			$whereParms = trim($config['record'][$operateType]['config']['whereParms']);
			if(!$whereParms){
				$whereParms = $this->create_parms_from_where($where);
			}
		}
		
		//表单头
		echo '<form action="?clause=save_' , $operateType , '_info&' , $pageParms , '&' , $whereParms , '" method="post" name="myForm" id="myForm" onSubmit="return SubmitAllBtnDis();">';
		if($config['config']['dbTableO']){
			if($rsONotFind){
				echo '<input name="rsONotFind" id="rsONotFind" value="1" type="hidden" />';
			}
			echo '<input name="rsOPid" id="rsOPid" value="' , $rs['id'] , '" type="hidden" />';
		}
		
		//隐藏框
		$arr = $config['record'][$operateType]['config']['hidden'];
		foreach($arr as $key=>$val){
			echo '<input name="' , $key , '" id="' , $key , '" type="hidden" value="';
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
			$valCount = count($val);
			
			//如果有trvif语句 tr view if，那么判断第一项是否有trvif，可能该行不必显示！
			if($valCount > 0){
				if(array_key_exists('trvif',$val[0])){
					eval('$vif_result = ' . $val[0]['trvif'] . ';');
					if(!$vif_result){
						continue;
					}
				}
			}
			
			echo '<tr>';
			echo $leftTd;
			echo $key;
			echo '</td>';
			
			echo $rightTd;
			//遍历右侧单元格里的元素
			for($ci = 0;$ci < $valCount;$ci++){
				//取得数组
				$arr = $val[$ci];
				
				//如果有if语句 view if
				if($arr['vif']){
					eval('$vif_result = ' . $arr['vif'] . ';');

					if(!$vif_result){
						continue;
					}
				}
				
				//前面要显示的内容
				if(strlen($arr['shtml']) > 0){
					echo $arr['shtml'];
				}

				//遍历类型
				switch($arr['type']){
					case 'eval':
						eval($arr['php']);
						break;
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
								case 'trvif':
								case 'vif':
									//跳过
									break;
								case 'field':
									//field字段则从数据库中读取
									if(strtolower(substr($val1,0,4)) == 'f_o_'){
										echo ' src="' , $rsO[$val1] , '"';
									}else{
										echo ' src="' , $rs[$val1] , '"';
									}
									break;
								default:
									//其它字段直接赋值
									echo ' ' , $key1 , '="' , $val1 , '"';
									break;
							}
						}
						echo ' />';
						break;
					case 'article':
						if(strlen($arr['content']) > 0){
							echo $arr['content'];
						}else if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo $rsO[$arr['field']];
							}else{
								echo $rs[$arr['field']];
							}
						}
						break;
					case 'imgUpload':
					case 'imgUploadB':
					case 'imgUploadS':
						echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '" type="text"';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo ' value="' , $rsO[$arr['field']] , '" ';
							}else{
								echo ' value="' , $rs[$arr['field']] , '" ';
							}
						}else if(strlen($arr['value']) > 0){
							echo ' value="' , $arr['value'] , '" ';
						}
						if($arr['maxlength']){
							echo ' maxlength="' , $arr['maxlength'] , '"';
						}
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						echo ' />';

						//若未指定是大图，那默认都是小图
						echo ' <a onclick="ShowUpLoadFile(\'' , $arr['field'] , '\',\'' , $arr['soid'] , '\',\'' , ($arr['type'] == 'imgUploadB' ? 'big' : ($arr['type'] == 'imgUploadS' ? 'small' : '')) , '\');" href="#nolink" style="color:#ff0000;">[点击上传]</a>';
						if($arr['type'] == 'imgUploadS'){
							echo ' <a onClick="UpLoadPicAutoSmall(\'' , $arr['boid'] , '\',\'' , $arr['field'] , '\',false);" href="#nolink" style="color:#0000ff;">[自动缩小]</a>';
						}
						echo '<script type="text/javascript">$("#' , $arr['field'] , '").FN_CluetipInputPic();</script>';
						break;
					case 'fileUpload':
						echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '" type="text"';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo ' value="' , $rsO[$arr['field']] , '" ';
							}else{
								echo ' value="' , $rs[$arr['field']] , '" ';
							}
						}else if(strlen($arr['value']) > 0){
							echo ' value="' , $arr['value'] , '" ';
						}
						if($arr['maxlength']){
							echo ' maxlength="' , $arr['maxlength'] , '"';
						}
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						echo ' />';

						echo ' <a onclick="ShowUpLoadFile(\'' , $arr['field'] , '\',\'\',\'\');" href="#nolink" style="color:#ff0000;">[点击上传]</a>';
						break;
					case 'text':
					case 'password':
						echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '" type="' , $arr['type'] , '"';
						
						//md5不显示值
						if($arr['isMD5'] == 1){
							echo ' value=""';
						}else{
							if($operateType == 'edit'){
								if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
									echo ' value="' , $rsO[$arr['field']] , '"';
								}else{
									echo ' value="' , $rs[$arr['field']] , '"';
								}
							}else if(strlen($arr['value']) > 0){
								echo ' value="' , $arr['value'] , '"';
							}
						}
						
						if($arr['maxlength']){
							echo ' maxlength="' , $arr['maxlength'] , '"';
						}
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						echo ' />';

						break;
					case 'datetime':
						echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '" type="text"';
						
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								$datetimeVal = $rsO[$arr['field']];
							}else{
								$datetimeVal = $rs[$arr['field']];
							}
						}else if(strlen($arr['value']) > 0){
							$datetimeVal = $arr['value'];
						}
						if($datetimeVal == '0000-00-00 00:00:00'){
							$datetimeVal = '';
						}
						echo ' value="' , $datetimeVal , '"';
						
						if($arr['maxlength']){
							echo ' maxlength="' , $arr['maxlength'] , '"';
						}
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						if($arr['onfocus']){
							//,'onfocus'=>'WdatePicker({dateFmt:\'yyyy-M-d\'})'
							//dateFmt:'H:mm:ss'
							echo ' onfocus="' , $arr['onfocus'] , '"';
						}
						echo ' />';

						break;
					case 'radio':
						$cj = 0;
						foreach($arr['list'] as $key1=>$val1){
							$cj++;
							echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '_' , $cj , '" type="' , $arr['type'] , '"';
							echo ' value="' , $key1 , '"';
							if($operateType == 'edit'){
								if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
									if($rsO[$arr['field']] == $key1){
										echo ' checked="checked"';
									}
								}else{
									if($rs[$arr['field']] == $key1){
										echo ' checked="checked"';
									}
								}
							}else if(strlen($arr['value']) > 0){
								if($arr['value'] == $key1){
									echo ' checked="checked"';
								}
							}
							echo ' />';
							echo '<label for="' , $arr['field'] , '_' , $cj , '">' , $val1 , '</label> &nbsp; ';
						}

						break;
					case 'baiduMap':
					case 'googleMap':
					case 'aMap':
						echo '<input name="' , $arr['field'] , '" id="' , $arr['field'] , '" type="' , $arr['type'] , '"';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo ' value="' , $rsO[$arr['field']] , '" ';
							}else{
								echo ' value="' , $rs[$arr['field']] , '" ';
							}
						}else if(strlen($arr['value']) > 0){
							echo ' value="' , $arr['value'] , '" ';
						}
						if($arr['maxlength']){
							echo ' maxlength="' , $arr['maxlength'] , '"';
						}
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						echo ' onclick="MapSelector(\'' , $arr['field'] , '\',\'' , $arr['type'] , '\',\'' , $arr['default'] , '\',this.value)"';
						echo ' />';

						break;
					case 'textarea':
						echo '<textarea name="' , $arr['field'] , '" id="' , $arr['field'] , '"';
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						echo '>';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo $rsO[$arr['field']];
							}else{
								echo $rs[$arr['field']];
							}
						}else if(strlen($arr['value']) > 0){
							echo $arr['value'];
						}
						echo '</textarea>';

						break;
					case 'qTextarea':
						echo '<textarea name="' , $arr['field'] , '" id="' , $arr['field'] , '"';
						if($arr['class']){
							echo ' class="' , $arr['class'] , '"';
						}
						echo '>';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo $rsO[$arr['field']];
							}else{
								echo $rs[$arr['field']];
							}
						}else if(strlen($arr['value']) > 0){
							echo $arr['value'];
						}
						echo '</textarea>';

						//list q...
						break;
					case 'ckeditor':
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								FCreateCkeditor($arr['field'],$rsO[$arr['field']],$arr['ckc']);
							}else{
								FCreateCkeditor($arr['field'],$rs[$arr['field']],$arr['ckc']);
							}
						}else if(strlen($arr['value']) > 0){
							FCreateCkeditor($arr['field'],$arr['value'],$arr['ckc']);
						}else{
							FCreateCkeditor($arr['field'],'',$arr['ckc']);
						}

						break;
					case 'cid':
						echo '<select name="' , $arr['field'] , '" id="' , $arr['field'] , '">';
						echo '<option value="0">-=请选择=-</option>';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo FGetCategorySelectOptions($rsO[$arr['field']],$arr['tid'],$arr['depth'],$arr['assoDbTable']['dbTable'],$arr['assoDbTable']['valueType'],$arr['assoDbTable']['attrFields']);
							}else{
								echo FGetCategorySelectOptions($rs[$arr['field']],$arr['tid'],$arr['depth'],$arr['assoDbTable']['dbTable'],$arr['assoDbTable']['valueType'],$arr['assoDbTable']['attrFields']);
							}
						}else{
							echo FGetCategorySelectOptions(intval($arr['value']),$arr['tid'],$arr['depth'],$arr['assoDbTable']['dbTable'],$arr['assoDbTable']['valueType'],$arr['assoDbTable']['attrFields']);
						}
						echo '</select>';

						break;
					case 'cTitle':
						echo '<select name="' , $arr['field'] , '" id="' , $arr['field'] , '">';
						echo '<option value="">-=请选择=-</option>';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								echo FGetCategorySelectOptions($rsO[$arr['field']],$arr['tid'],$arr['depth'],$arr['assoDbTable']['dbTable'],$arr['assoDbTable']['valueType'],$arr['assoDbTable']['attrFields']);
							}else{
								echo FGetCategorySelectOptions($rs[$arr['field']],$arr['tid'],$arr['depth'],$arr['assoDbTable']['dbTable'],$arr['assoDbTable']['valueType'],$arr['assoDbTable']['attrFields']);
							}
						}else{
							echo FGetCategorySelectOptions($arr['value'],$arr['tid'],$arr['depth'],$arr['assoDbTable']['dbTable'],$arr['assoDbTable']['valueType'],$arr['assoDbTable']['attrFields']);
						}
						echo '</select>';

						break;
					case 'select':
						echo '<select name="' , $arr['field'] , '" id="' , $arr['field'] , '">';
						echo '<option value="">-=请选择=-</option>';
						
						$assoVal = '';
						if($operateType == 'edit'){
							if(strtolower(substr($arr['field'],0,4)) == 'f_o_'){
								$assoVal = $rsO[$arr['field']];
							}else{
								$assoVal = $rs[$arr['field']];
							}
						}else{
							$assoVal = $arr['value'];
						}
						
						if($arr['options']){
							echo $arr['options'];
						}else if($arr['list']){
							
						}else{
							FGetDbTableSelectOptions($arr['assoDbTable']['sql'],$arr['assoDbTable']['vField'],$arr['assoDbTable']['tField'],$assoVal);
						}
						
						echo '</select>';
						
						if(strlen($assoVal) > 0){
							echo '<script type="text/javascript">';
							echo '$("#' , $arr['field'] , '").val("' , $assoVal , '");';
							echo '</script>';
						}

						break;
					case 'area':
						$tempRnd = mt_rand(10000,99999);
						echo '<span id="ajaxAreaId_' , $tempRnd , '"></span>';
						break;
				}//end switch
				
				//后面要显示的内容
				if($arr['required']){
					echo ' <span class="mustInput">*</span>';
				}
				if($arr['ehtml']){
					echo ' ' , $arr['ehtml'];
				}
				
			}//end for
			echo '</td>';
			
			echo '</tr>';
		}
		
		//按钮
		$button = $config['record'][$operateType]['config']['button'];
		if(is_array($button)){
			echo '<tr>';
			echo '<td colspan="2" align="center">';
			//遍历
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
		}
		
		echo '</table>';
		echo '</form>';
	}
	
	//$operateType = add / edit
	function save_operate_record($operateType = 'edit'){
		global $DB;
		global $pageParms;
		global $admin_db_manager_config; $config = $admin_db_manager_config;

		//要更新数据的表
		$dbTable = $config['config']['dbTable'];
		//附属表
		$dbTableO = $config['config']['dbTableO'];
		
		//要更新的数据数组
		$sqlFVArr = array();
		//附属表
		$sqlFVArrO = array();
		
		//遍历post的数据
		//必填未开发：required_inputName = 必填，例：<input name="required_f_title" type="hidden" value="请填写标题" />
		foreach($_POST as $key=>$val) {
			if(substr($key,0,2) == 'f_'){
				if(substr($key,0,4) == 'f_o_'){
					//附属表
					if(substr($key,0,9) == 'f_o_html_'){
						//如果是ckeditor里过来的，非html标签中的'"会被ckeditor转义为&quot;形式
						//这里的转义'"转为\'\"，是过滤从textarea中过来的数据
						$sqlFVArrO[$key] = FAddslashes($val);
					}else{
						$sqlFVArrO[$key] = FSHtmlEncode($val);
					}
				}else{
					if(substr($key,0,7) == 'f_html_'){
						//如果是ckeditor里过来的，非html标签中的'"会被ckeditor转义为&quot;形式
						//这里的转义'"转为\'\"，是过滤从textarea中过来的数据
						$sqlFVArr[$key] = FAddslashes($val);
					}else{
						$sqlFVArr[$key] = FSHtmlEncode($val);
						
						//处理md5
						if($key == 'f_password'){
							if(strlen($sqlFVArr[$key]) <= 0){
								//没填写，置空
								unset($sqlFVArr[$key]);
							}else{
								$sqlFVArr[$key] = md5($sqlFVArr[$key]);
							}
						}
					}
				}
			}
		}
		
		//保存后返回哪里
		$saveBack = $config['record'][$operateType]['config']['saveBack'];
		$saveBack = $this->replace_url_parms_GP($saveBack);
		
		//写入数据库
		if($operateType == 'edit'){
			//更新数据的条件
			$where = trim($config['record'][$operateType]['config']['where']);
			//加防更新出错，任何情况下，必须有where，否则更新将是批量的、出错就麻烦了
			if(strlen($where) <= 0){
				FJS_AB('where条件出错，请检查！');
			}
			if(strtolower(substr($where,0,6)) != 'where '){
				$where = 'where ' . $where;
			}
			
			//echo $dbTable;
			//print_r($sqlFVArr);
			//echo $where;
			//exit;
			
			$affectId = $DB->update($dbTable,$sqlFVArr,$where);
			
			//如果有附属表
			if($dbTableO){
				$whereO = trim($config['record'][$operateType]['config']['whereO']);
				if(strlen($whereO) <= 0){
					FJS_AB('附属表where条件出错，请检查！');
				}
				if(strtolower(substr($whereO,0,6)) != 'where '){
					$whereO = 'where ' . $whereO;
				}
				
				//如果在附表中没有数据，插入新数据
				//这个不准，当两个人同时管理一条数据、先后提交数据时，会出现问题
				/*
				if(FPostInt('rsONotFind')){
					$sqlFVArrO['id'] = FPostInt('rsOPid');
					$affectId = $DB->insert($dbTableO,$sqlFVArrO);
				}else{
					$affectId = $DB->update($dbTableO,$sqlFVArrO,$whereO);
				}
				*/
				//修正为：
				$sqlFVArrO['f_updateRndKey'] = FRndStr(20);
				$affectId = $DB->update($dbTableO,$sqlFVArrO,$whereO);
				if($affectId <= 0){
					$sqlFVArrO['id'] = FPostInt('rsOPid');
					if($sqlFVArrO['id'] <= 0){
						$sqlFVArrO['id'] = FGetInt('id');
					}
					if($sqlFVArrO['id'] <= 0){
						$sqlFVArrO['id'] = FPostInt('id');
					}
					if($sqlFVArrO['id'] <= 0){
						FJS_AB('附属表插入数据时发生错误：ID为0，请检查！');
					}
					
					$affectId = $DB->insert($dbTableO,$sqlFVArrO);
				}
			}
			
			//
		}else{
			$affectId = $DB->insert($dbTable,$sqlFVArr);

			//如果有附属表，更新附属表数据
			if($dbTableO){
				$sqlFVArrO['id'] = $affectId;
				$affectId = $DB->insert($dbTableO,$sqlFVArrO);
			}
			
			//
		}
		
		global $F_More_Fun;
		if(strlen($F_More_Fun) > 0){
			if(function_exists($F_More_Fun)){
				$F_More_Fun();
			}
		}
		
		FJS_AT('数据保存成功！',$saveBack);
	}
	
	function del_info(){
		global $DB;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		global $id;

		$DB->query("delete from `{$config['config']['dbTable']}` where id = {$id}");
		
		//删除附属表，关联必须是id
		if(strlen($config['config']['dbTableO']) > 0){
			$DB->query("delete from `{$config['config']['dbTableO']}` where id = {$id}");
		}
		
		FJS_AT('删除成功！',FPrevUrl());
		//FRedirect(FPrevUrl());
	}
	
	function update_field(){
		global $DB;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		global $id;

		$field = FGetStr('field');
		$val = FGetStr('val');

		$DB->query("update `{$config['config']['dbTable']}` set `{$field}` = '{$val}' where id = {$id}");
		
		FRedirect(FPrevUrl());
	}
	
	function update_bool(){
		global $DB;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		global $id;

		$field = FGetStr('field');
		$state = FGetInt('state');

		$DB->query("update `{$config['config']['dbTable']}` set {$field} = {$state} where id = {$id}");
		
		FRedirect(FPrevUrl());
	}
	
	//批量更新状态，只认 checkbox的name=ids[]
	//并且checkbox的value必须=id
	function batcf_bool_info(){
		global $DB;
		global $admin_db_manager_config; $config = $admin_db_manager_config;

		$ids = $_POST['ids'];
		if(!is_array($ids)){
			FJS_AB('请选中要更新的数据！');
		}
		$delIds = '';
		for($ci = 0;$ci < count($ids);$ci++){
			if($delIds !== ''){
				$delIds .= ',';
			}
			$delIds .= $ids[$ci];
		}
		
		$field = FGetStr('field');
		$state = FGetStr('state');

		$DB->query("update `{$config['config']['dbTable']}` set {$field} = '{$state}' where id in ({$delIds})");
		
		FJS_AT('更新成功！',FPrevUrl());
		//FRedirect(FPrevUrl());
	}
	
	//批量删除，只认 checkbox的name=ids[]
	//并且checkbox的value必须=id
	function batcf_del_info(){
		global $DB;
		global $admin_db_manager_config; $config = $admin_db_manager_config;

		$ids = $_POST['ids'];
		if(!is_array($ids)){
			FJS_AB('请选中要删除的数据！');
		}
		$delIds = '';
		for($ci = 0;$ci < count($ids);$ci++){
			if($delIds !== ''){
				$delIds .= ',';
			}
			$delIds .= $ids[$ci];
		}

		$DB->query("delete from `{$config['config']['dbTable']}` where id in ({$delIds})");
		
		//删除附属表，关联必须是id
		if(strlen($config['config']['dbTableO']) > 0){
			$DB->query("delete from `{$config['config']['dbTableO']}` where id in ({$delIds})");
		}
		
		FJS_AT('选中的数据已经删除！',FPrevUrl());
		//FRedirect(FPrevUrl());
	}
	
	//批量排序页面中的序号
	function save_page_sort(){
		global $DB;
		global $admin_db_manager_config; $config = $admin_db_manager_config;
		
		$sql = "UPDATE `{$config['config']['dbTable']}` SET f_sort = CASE id ";
		$ids = '';
		$ci = 0;
		foreach($_POST as $key=>$val){
			if(substr($key,0,5) == 'sort_'){
				$ci++;
				
				$id = substr($key,5);
				$val = (int)$val;
				$sql .= sprintf("WHEN %d THEN %d ", $id, $val);
				
				if($ids !== ''){
					$ids .= ',';
				}
				$ids .= $id;
			}
		}
		$sql .= "END WHERE id IN ($ids)"; 
		
		if($ci <= 0){
			FJS_AB('抱歉，没有需要排序的数据！');
		}

		$DB->query($sql);
		
		FJS_AT('排序成功！',FPrevUrl());
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
	function replace_url_parms_rs($url,$rs){
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