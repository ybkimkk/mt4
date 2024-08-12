<?php
$LoggedAdminId = intval($_COOKIE['admin']['id']);
$LoggedAdminPassword = $_COOKIE['admin']['password'];

//test
if(stripos($_SERVER['HTTP_HOST'],'127.0.0.1') !== false){
	$LoggedAdminId = 1;
	$LoggedAdminPassword = 'babd4a84418fb04015ac773f7459727c';
}

$DRAdmin = array();
if($LoggedAdminId > 0 && strlen($LoggedAdminPassword) > 0){
	$DRAdmin = $DB->getDRow("select * from `t_member` where id = '" . $LoggedAdminId . "' and password = '" . $DB->escapeStr($LoggedAdminPassword) . "'");
	if($DRAdmin){
		//按原逻辑修正：
		$DRTemp_ = $DB->getDRow("select * from `t_mt4_server` where id = '" . $DRAdmin['server_id'] . "' and `status` = 1");
		$DRAdmin['serverid'] = $DRTemp_['id'];
		$DRAdmin['mt4name']  = $DRTemp_['mt4_name'];
		$DRAdmin['timezone'] = $DRTemp_['time_zone'];	
		$DRAdmin['ver']      = $DRTemp_['ver'];
		$DRAdmin['mt4dbname'] = $DRTemp_['db_name'];
		
		$GLOBALS['deposit_mt4dbname'] = $DRTemp_['db_name'];
		$GLOBALS['deposit_mt4dbver'] = $DRTemp_['ver'];
		
		$DTTemp_ = $DB->getDTable("select id,server_id from `t_member` where `email` = '" . $DRAdmin['email'] . "' and `status` = 1");
		$DRAdmin['svr_ids'] = '';
		$ci = 0;
		foreach ($DTTemp_ as $svrkey => $svrval) {
			if ($ci > 0) {
				$DRAdmin['svr_ids'] .= ',';
			}
			$DRAdmin['svr_ids'] .= $svrval['server_id'];
			
			$ci++;
		}
		
		//读取数据库中的配置
		//查询私有配置
		$serverConfigArr = $DB->getField2Arr("select configname,configvalue from `t_config_server` where `server_id` = '{$DRAdmin['server_id']}'");
		$czUConfig = array();
		foreach ($configArr as $k => $v) {
			if($v['sid'] > 0 && NULL !== $serverConfigArr[$v['name']]) {
				$configArr[$k]['value'] = $serverConfigArr[$v['name']];
			}
			$czUConfig[$v['name']] = $configArr[$k]['value'];
		}
		
		//合并页面上的配置
		$webConfig = array_merge($webConfig,$czUConfig);
		
		//-------------------------------
		
		$roleDR = $DB->getDRow("select * from t_role where id = '" . $DRAdmin['f_roleId'] . "' and status = 1");
		if(!$roleDR){
			$DRAdmin['_access'] = array();
		}else{
			@$DRAdmin['_access'] = unserialize($roleDR['f_access']);
		}
		if(!$DRAdmin['_access']){
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>L("权限有误，请联系管理员处理" . ' (' . $DRAdmin['f_roleId'] . ')'),
				'btnStr'=>L('返回'),
				'url'=>FPrevUrl(),
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
		$DRAdmin['_dataRange'] = $roleDR['f_dataRange'];
		
		//-------------------------------处理后台菜单，一并处理具体权限
		$LeftMenuHtml = '';
		
		require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'left_menu_xml.php');
						
		$currPage = strtolower(FGetCurrUrl(8));
		if(stripos($currPage,'.') === false){
			$currPage = 'index.php';
		}
		
		$pageAccess = array();
		
		foreach($CZMenu as $key1=>$xml_menu){
			//权限
			if(@!in_array($key1,$DRAdmin['_access'])){
				continue;
			}
			
			$onMenuClassName = '';
			if(!$xml_menu['sub']){
				$pageAccess[] = strtolower($xml_menu['url']);
				if($xml_menu['murl']){
					foreach($xml_menu['murl'] as $key_=>$val_){
						$pageAccess[] = strtolower($val_);
					}
				}
				if($xml_menu['macc']){
					foreach($xml_menu['macc'] as $key_=>$val_){
						if(@!in_array($val_,$DRAdmin['_access']['more'][$key1])){
							continue;
						}
						$pageAccess[] = strtolower($xml_menu['url']) . $val_;
					}
				}
				
				if($currPage == strtolower($xml_menu['url'])){
					$onMenuClassName = ' czLMOn';
				}
				if(strlen($onMenuClassName) <= 0){
					if($xml_menu['murl']){
						foreach($xml_menu['murl'] as $key_=>$val_){
							if($currPage == strtolower($val_)){
								$onMenuClassName = ' czLMOn';
								break;
							}
						}
					}
				}
			}
			
			//一级
			$LeftMenuHtml .= '<li class="side-nav-item' . $onMenuClassName . '">';
				$LeftMenuHtml .= '<a href="' . ($xml_menu['sub'] ? 'javascript: void(0);' : $xml_menu['url']) . '" class="side-nav-link"' . ($xml_menu['target'] ? ' target="' . $xml_menu['target'] . '"' : '') . '>';
				$LeftMenuHtml .= '<span>';
					$LeftMenuHtml .= '<i class="' . $xml_menu['ico'] . '"></i>';
					$LeftMenuHtml .= '<span> ' . L($xml_menu['title']) . ' </span>';
					if($xml_menu['sub']){
						$LeftMenuHtml .= '<span class="menu-arrow"></span>';
					}
				$LeftMenuHtml .= '</span>';
				$LeftMenuHtml .= '</a>';
					
			//取menu-->item
			if($xml_menu['sub']){
				$LeftMenuHtml .= '<ul class="side-nav-second-level collapse" aria-expanded="false">';
				
				foreach($xml_menu['sub'] as $key2=>$xml_menu_item){
					//权限
					if(@!in_array($key2,$DRAdmin['_access'])){
						continue;
					}
					
					if($xml_menu_item['sub']){
						//二级
						$LeftMenuHtml .= '<li class="side-nav-item">';
							$LeftMenuHtml .= '<a href="javascript: void(0);" aria-expanded="false">';
							$LeftMenuHtml .= '<span>';
								$LeftMenuHtml .= L($xml_menu_item['title']);
								$LeftMenuHtml .= '<span class="menu-arrow"></span>';
							$LeftMenuHtml .= '</span>';
							$LeftMenuHtml .= '</a>';
							//三级
							$LeftMenuHtml .= '<ul class="side-nav-third-level" aria-expanded="false">';
							foreach($xml_menu_item['sub'] as $key3=>$xml_menu_item_sub){
								//权限
								if(@!in_array($key3,$DRAdmin['_access'])){
									continue;
								}

								$onMenuClassName = '';
								
								$pageAccess[] = strtolower($xml_menu_item_sub['url']);
								if($xml_menu_item_sub['murl']){
									foreach($xml_menu_item_sub['murl'] as $key_=>$val_){
										$pageAccess[] = strtolower($val_);
									}
								}
								if($xml_menu_item_sub['macc']){
									foreach($xml_menu_item_sub['macc'] as $key_=>$val_){
										if(@!in_array($val_,$DRAdmin['_access']['more'][$key3])){
											continue;
										}
										$pageAccess[] = strtolower($xml_menu_item_sub['url']) . $val_;
									}
								}
								
								if($currPage == strtolower($xml_menu_item_sub['url'])){
									$onMenuClassName = 'czLMOn';
								}
								if(strlen($onMenuClassName) <= 0){
									if($xml_menu_item_sub['murl']){
										foreach($xml_menu_item_sub['murl'] as $key_=>$val_){
											if($currPage == strtolower($val_)){
												$onMenuClassName = 'czLMOn';
												break;
											}
										}
									}
								}
								
								$LeftMenuHtml .= '<li class="' . $onMenuClassName . '"><a href="' . $xml_menu_item_sub['url'] . '"' . ($xml_menu_item_sub['target'] ? ' target="' . $xml_menu_item_sub['target'] . '"' : '') . '><span>' . L($xml_menu_item_sub['title']) . '</span></a></li>';
							}
							$LeftMenuHtml .= '</ul>';
						$LeftMenuHtml .= '</li>';
					}else{
						//二级
						$onMenuClassName = '';
						
						$pageAccess[] = strtolower($xml_menu_item['url']);
						if($xml_menu_item['murl']){
							foreach($xml_menu_item['murl'] as $key_=>$val_){
								$pageAccess[] = strtolower($val_);
							}
						}
						if($xml_menu_item['macc']){
							foreach($xml_menu_item['macc'] as $key_=>$val_){
								if(@!in_array($val_,$DRAdmin['_access']['more'][$key2])){
									continue;
								}
								$pageAccess[] = strtolower($xml_menu_item['url']) . $val_;
							}
						}
						
						if($currPage == strtolower($xml_menu_item['url'])){
							$onMenuClassName = 'czLMOn';
						}
						if(strlen($onMenuClassName) <= 0){
							if($xml_menu_item['murl']){
								foreach($xml_menu_item['murl'] as $key_=>$val_){
									if($currPage == strtolower($val_)){
										$onMenuClassName = 'czLMOn';
										break;
									}
								}
							}
						}
						
						$LeftMenuHtml .= '<li class="' . $onMenuClassName . '"><a href="' . $xml_menu_item['url'] . '"' . ($xml_menu_item['target'] ? ' target="' . $xml_menu_item['target'] . '"' : '') . '><span>' . L($xml_menu_item['title']) . '</span></a></li>';
					}
				}//end foreach menu-->item
				
				$LeftMenuHtml .= '</ul>';
			}
			
			$LeftMenuHtml .= '</li>';
		}//end foreach menu
		
		$DRAdmin['_pageAccess'] = $pageAccess;
	}
}
if($DRAdmin){
	$DTMt4Server = $DB->getDTable("select * from `t_mt4_server` where `status` = 1",'id');
}