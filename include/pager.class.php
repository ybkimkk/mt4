<?php
/*
//精简版：
$recordCount = intval($DB->getField("select count(id) from `t_member` {$where}"));
$pagersize = 5;
$pageConfig = array(
	'recordCount'=>$recordCount,
	'pagesize'=>$pagersize,
	'pageCurrIndex'=>FGetInt('page'),
	'showPrevPage'=>false,
	'showNextPage'=>false,
);
$cnPager = new CPager($pageConfig);
$sqlRecordStartIndex = $cnPager->FGetSqlRecordStartIndex();
$query = $DB->getDTable("select * from `t_member` {$where} LIMIT {$sqlRecordStartIndex},{$pagersize}");
//...
echo $cnPager->FGetPageList();

//常用版：
$recordCount = intval($DB->getField("select count(id) from `t_member` {$where}"));
$pagersize = 10;
$pageConfig = array(
	'recordCount'=>$recordCount,
	'pagesize'=>$pagersize,
	'pageCurrIndex'=>FGetInt('page'),
);
$cnPager = new CPager($pageConfig);
$sqlRecordStartIndex = $cnPager->FGetSqlRecordStartIndex();
$query = $DB->getDTable("select * from `t_member` {$where} LIMIT {$sqlRecordStartIndex},{$pagersize}");
//...
echo $cnPager->FGetPageList();

//完整版：
$recordCount = intval($DB->getField("select count(id) from `t_member` {$where}"));
$pagersize = 10;
$pageConfig = array(
	'recordCount'=>$recordCount,
	'pagesize'=>$pagersize,
	'pageCurrIndex'=>FGetInt('page'),
	'showRecordCount'=>true,
	'showSetPagesize'=>true,
	'showJump'=>true,
);
$cnPager = new CPager($pageConfig);
$sqlRecordStartIndex = $cnPager->FGetSqlRecordStartIndex();
$query = $DB->getDTable("select * from `t_member` {$where} LIMIT {$sqlRecordStartIndex},{$pagersize}");
//...
echo $cnPager->FGetPageList();
*/

class CPager{
	//分页的参数配置
	private $config = array(
		'pagesize'=>10,
		'pageMainLinks'=>10,
		
		'recordCount'=>0,
		'pageCurrIndex'=>1,
		'pageMax'=>0,
		'pageNextIndex'=>0,
		'pageKey'=>'page',
		
		'useTplUrl'=>false,
		'tplUrl'=>'/demo/news-{page}.html',
		
		'isAjax'=>0,
		'ajaxJsFun'=>'FLoadPage({page})',
		
		'skin'=>'WJX-pager-blue1',

		'showRecordCount'=>false,
		'tplRecordCount'=>'_RECORDS_条记录，第_PAGE_/_PAGES_页',
		
		'showPrevPage'=>true,
		'tplPrevPage'=>'上一页',
		'showNextPage'=>true,
		'tplNextPage'=>'下一页',
		
		'showSetPagesize'=>false,
		'tplSetPagesize'=>array('auto'=>'（自动）','sp'=>'条/页'),
		
		'showJump'=>false,
		'tplJump'=>array('gotoS'=>'到第','gotoE'=>'页','submit'=>'确定'),
		
		'language'=>'zh-cn',
	);

	public function __construct($config){
		if($config['language'] == 'en'){
			$this->config['tplRecordCount'] = '{recordCount} records';
			$this->config['tplPrevPage'] = 'previous';
			$this->config['tplNextPage'] = 'next';
			$this->config['tplSetPagesize'] = array('auto'=>'(auto)','sp'=>'items/page');
			$this->config['tplJump'] = array('gotoS'=>'Go to page','gotoE'=>'','submit'=>'Submit');
		}
		
		$this->FInitConfig($config);
	}

	public function __destruct(){
		unset($this->config);
	}
	
	private function FInitConfig($config){
		//初始化配置
		if(isset($config) && is_array($config) && count($config)>0){
			foreach($config as $key=>$val){
				$this->config[$key] = $val;
			}
		}
		
		//修正分页数据，防止传递进来错误的数据
		$this->FCC();
	}
	
	private function FCC(){
		$this->config['recordCount'] = intval($this->config['recordCount']);
		$this->config['pagesize'] = intval($this->config['pagesize']);
		$this->config['pageCurrIndex'] = intval($this->config['pageCurrIndex']);
		
		//修正每页数量
		if($this->config['pagesize'] <= 0){
			$this->config['pagesize'] = 10;
		}
	
		//计算最大页数
		$this->config['pageMax'] = ceil($this->config['recordCount'] / $this->config['pagesize']);
		
		//修正当前页
		if($this->config['pageCurrIndex'] > $this->config['pageMax']){
			$this->config['pageCurrIndex'] = $this->config['pageMax'];
		}
		if($this->config['pageCurrIndex'] <= 0){
			$this->config['pageCurrIndex'] = 1;
		}
		
		//下一页页码
		$this->config['pageNextIndex'] = $this->config['pageCurrIndex'] >= $this->config['pageMax'] ? $this->config['pageMax'] : ($this->config['pageCurrIndex'] + 1);
	}
	
	public function FGetSqlRecordStartIndex(){
		$temp = $this->config['pagesize'] * ($this->config['pageCurrIndex'] - 1);
		if($temp < 0){
			$temp = 0;
		}
		return $temp;
	}
	
	private function FGetTplUrl(){
		if($this->config['useTplUrl']){
			$url = $this->config['tplUrl'];
		}else{
			$url = $_SERVER['REQUEST_URI'];
			if(stripos($url,'?')){
				$pattern = '/' . $this->config['pageKey'] . '=([-0-9])*/';
				if(preg_match($pattern,$url)){
					//$url = preg_replace_callback($pattern, function($r){return $this->config['pageKey'] . '={page}';}, $url);
					$url = preg_replace($pattern, $this->config['pageKey'] . '={page}', $url);
				}else{
					$url .= '&' . $this->config['pageKey'] . '={page}';
				}
			}else{
				$url .= '?' . $this->config['pageKey'] . '={page}';
			}
			if(stripos($url,'?&')){
				$url = str_replace('?&','?',$url);
			}
		}
		return $url;
	}
	
	private function FCreateSpan($num){
		return '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>' . $num . '</em></span>';
	}
	
	private function FCreateA($url,$pageIndex,$title,$disabled = false){
		$temp = '';
		if($this->config['isAjax']){
			$temp .= '<a';
			$temp .= ' href="javascript:void(0);"';
			if(strlen($disabled)){
				$temp .= $disabled;
			}else{
				$temp .= ' onclick="' . str_ireplace('{page}',$pageIndex,$this->config['ajaxJsFun']) . '"';
			}
			$temp .= '>' . $title . '</a>';
		}else{
			$temp .= '<li class="paginate_button page-item';
			if($pageIndex == $this->config['pageCurrIndex']){
				$temp .= ' active';
			}else if($disabled){
				$temp .= ' disabled';
			}
			$temp .= '"><a href="';
			if($disabled){
				$temp .= '#nolink';
			}else{
				$temp .= str_replace('{page}',$pageIndex,$url);
			}
			$temp .= '" aria-controls="basic-datatable" data-dt-idx="' . $pageIndex . '" tabindex="0" class="page-link">' . $title . '</a></li>';
		}
		return $temp;
	}


	public function FGetPageList(){
		$html = '';
		
		if($this->config['recordCount'] <= 0){
			return $html;
		}

		$url = $this->FGetTplUrl();
		
		//添加开头的div
		$html .= '<div class="row">';
		
		//共多少条
		if($this->config['showRecordCount']){
			$temp_ = $this->config['tplRecordCount'];
			$temp_ = str_ireplace('_RECORDS_',$this->config['recordCount'],$temp_);
			$temp_ = str_ireplace('_PAGE_',$this->config['pageCurrIndex'],$temp_);
			$temp_ = str_ireplace('_PAGES_',$this->config['pageMax'],$temp_);
			
			$html .= '<div class="col-sm-12 col-md-5"><div class="dataTables_info" id="basic-datatable_info" role="status" aria-live="polite">' . $temp_ . '</div></div></span>';
		}
		
		$html .= '<div class="col-sm-12 col-md-7">';
        $html .= '<div class="dataTables_paginate paging_simple_numbers" id="basic-datatable_paginate">';
        $html .= '<ul class="pagination pagination-rounded">';
		
		//上一页、第1页
		if($this->config['showPrevPage']){
			//如果当前页码为1，则 上一页、第1页 不可点击
			
			//上一页
			if($this->config['pageCurrIndex'] <= 1){
				$html .= '<li class="paginate_button page-item previous disabled" id="basic-datatable_previous"><a href="#nolink" aria-controls="basic-datatable" data-dt-idx="0" tabindex="0" class="page-link"><i class="mdi mdi-chevron-left"></i></a></li>';
			}else{
				$html .= '<li class="paginate_button page-item previous" id="basic-datatable_previous"><a href="' . str_replace('{page}',$this->config['pageCurrIndex'] - 1,$url) . '" aria-controls="basic-datatable" data-dt-idx="0" tabindex="0" class="page-link"><i class="mdi mdi-chevron-left"></i></a></li>';
			}
		}
		
		//第一页
		if($this->config['pageCurrIndex'] <= 1){
			$html .= $this->FCreateA($url,1,1,true);
		}else{
			$html .= $this->FCreateA($url,1,1,false);
		}

		//当前显示页码的起始 1~10 1  11~20 11
		$pageMainStartIndex = (ceil($this->config['pageCurrIndex'] / $this->config['pageMainLinks']) - 1) * $this->config['pageMainLinks'] + 1;
		//当前显示页码的结束
		$pageMainEndIndex = $pageMainStartIndex + $this->config['pageMainLinks'] - 1;
		if($pageMainEndIndex > $this->config['pageMax']){
			$pageMainEndIndex = $this->config['pageMax'];
		}

		//前省略号
		if($pageMainStartIndex > 1){
			$html .= $this->FCreateA($url,$pageMainStartIndex - 1,'…',false);
		}

		//主体页码部分
		for($ci = $pageMainStartIndex;$ci <= $pageMainEndIndex;$ci++){
			//第一页已经在之前固定显示了，所以跳过第1页
			if($ci == 1){
				continue;
			}
			
			//页码
			if($ci == $this->config['pageCurrIndex']){
				$html .= $this->FCreateA($url,$ci,$ci,true);
			}else{
				$html .= $this->FCreateA($url,$ci,$ci,false);
			}
		}

		//后省略号及最后1页
		if($pageMainEndIndex < $this->config['pageMax']){
			//结束页码如果与最后1页差1，则不显示省略号
			if($this->config['pageMax'] - $pageMainEndIndex > 1){
				$html .= $this->FCreateA($url,$pageMainEndIndex + 1,'…',false);
			}
			
			//最后1页
			$html .= $this->FCreateA($url,$this->config['pageMax'],$this->config['pageMax'],false);
		}

		//下一页
		if($this->config['showNextPage']){
			if($this->config['pageCurrIndex'] >= $this->config['pageMax']){
				$html .= '<li class="paginate_button page-item next disabled" id="basic-datatable_next"><a href="#nolink" aria-controls="basic-datatable" data-dt-idx="' . $this->config['pageMax'] . '" tabindex="0" class="page-link"><i class="mdi mdi-chevron-right"></i></a></li>';
			}else{
				$html .= '<li class="paginate_button page-item next" id="basic-datatable_next"><a href="' . str_replace('{page}',$this->config['pageNextIndex'],$url) . '" aria-controls="basic-datatable" data-dt-idx="' . $this->config['pageMax'] . '" tabindex="0" class="page-link"><i class="mdi mdi-chevron-right"></i></a></li>';
			}
		}
		
		//每页显示多少条
		if($this->config['showSetPagesize']){
			$adminPagesize = intval($_COOKIE['admin_pagesize']);
			$adminFloder = $_COOKIE['adminFloder'];
			
			$html .= '<span class="layui-laypage-limits">';
			$html .= '<select onchange="window.location.href=\'' . $adminFloder . 'api/set_pagesize.php?pagesize=\' + this.value;">';
			$html .= '<option value="0">' . $this->config['tplSetPagesize']['auto'] . '</option>';
			for($ci = 10;$ci <= 100;$ci += 10){
				$html .= '<option value="' . $ci . '"' . ($adminPagesize == $ci ? ' selected' : '') . '>' . $ci . ' ' . $this->config['tplSetPagesize']['sp'] . '</option>';
			}
			$html .= '</select>';
			$html .= '</span>';
		}

		//添加跳转的html
		if($this->config['showJump']){
			$html .= '<span class="layui-laypage-skip">' . $this->config['tplJump']['gotoS'] . '<input onkeydown="if(event.keyCode==13){window.location.href=\'' . $url . '\'.replace(\'{page}\',$(this).val());}" type="text" min="1" value="' . $this->config['pageCurrIndex'] . '" class="layui-input">' . $this->config['tplJump']['gotoE'] . '<button onclick="window.location.href=\'' . $url . '\'.replace(\'{page}\',$(this).prev().val());" type="button" class="layui-laypage-btn">' . $this->config['tplJump']['submit'] . '</button></span>';
		}
		
		$html .= '</ul>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

}