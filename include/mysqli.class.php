<?php
class CMySQLi{
	public $QueryCount = 0;
	private $conn;
	private $transTimes = 0;
	
	public function connnect($con_db_host,$con_db_uid,$con_db_pwd,$con_db_name,$con_db_port = 3306){
		$this->conn = new mysqli($con_db_host,$con_db_uid,$con_db_pwd,$con_db_name,$con_db_port);
		if($this->conn->connect_errno){
			$errHtml = '抱歉，数据库连接失败（' . $this->conn->connect_errno . '）';
			if(CC_MYSQL_DEBUG){
				$errHtml .= '：' . $this->conn->connect_error;
			}
			$this->showError($errHtml);
		}
		$this->conn->set_charset("utf8mb4");
	}

	public function query($sql){
		$query = $this->conn->query($sql);
		
		if($query === false){
			$errHtml = 'SQL语句执行出错了，请联系技术员处理！<br>';
			if(CC_MYSQL_DEBUG){
				$errHtml .= 'SQL：' . $sql . '<br>';
				$errHtml .= '错误信息：' . $this->conn->error;
			}
			$this->showError($errHtml);
		}
		
		$this->QueryCount++;
		
		if(stripos(trim($sql),'insert ') === 0){
			return $this->conn->insert_id;
		}else if(stripos(trim($sql),'delete ') === 0 || stripos(trim($sql),'update ') === 0){
			//1.执行成功，则返回受影响的行的数目，如果最近一次查询失败的话，函数返回 -1
			//2.对于delete,将返回实际删除的行数.
			//3.对于update,如果更新的列值原值和新值一样,如update tables set col1=10 where id=1;id=1该条记录原值就是10的话,则返回0。mysql_affected_rows返回的是实际更新的行数,而不是匹配到的行数。
			return $this->conn->affected_rows;
		}else{
			return $query;
		}
	}
	
	public function getDTable($sql,$arrKey = '') {
		$strLeft6 = strtolower(substr(trim($sql),0,6));
		if($strLeft6 != 'select'){
			$errHtml = '抱歉，只支持select语句';
			$this->showError($errHtml);
		}

		$query = $this->query($sql);
		
		$list = array();
		if($query !== false){
			if($query->num_rows > 0){
				while($dr = $this->fetchArray($query)){
					if(strlen($arrKey)){
						$list[$dr[$arrKey]] = $dr;
					}else{
						$list[] = $dr;
					}
				}
			}
		}
		
		return $list;
	}
	
	public function getDRow($sql,$assoc = 1){
		if(stripos(strtolower($sql),' limit ') === false){
			$sql .= ' limit 1';
		}

		$query = $this->query($sql);
		if($query->num_rows > 0){
			if($assoc){
				$dr = $this->fetchArray($query);
			}else{
				$dr = $query->fetch_row();
			}
		}else{
			$dr = array();
		}
		return $dr;
	}
	
	//单个字段，getAll为空或肯定是false，返回字段的值
	//多个字段，getAll肯定为true，返回以第一个字段为key的多行数组
	public function getField($sql,$getAll = false){
		if($getAll){
			$list = array();
			
			$query = $this->query($sql);
			while($dr = $query->fetch_row()){
				$list[] = $dr[0];
			}
	
			return $list;
		}else{
			$rs = $this->getDRow($sql,0);
			return $rs[0];
		}
	}
	
	//双字段，第二个参数为空，返回以第一个字段为key的多行数组
	public function getField2Arr($sql,$sepa = NULL){
		$cols = array();
		
		$resultSet = $this->getDTable($sql);
		
		if(!empty($resultSet)) {
			$count          =   count($resultSet[0]);
			
			$field          =   array_keys($resultSet[0]);
			$key            =   array_shift($field);
			$key2           =   array_shift($field);
			
			foreach ($resultSet as $result){
				$name   =  $result[$key];
				if(2==$count) {
					$cols[$name] =  $result[$key2];
				}else{
					$cols[$name] =  is_string($sepa)?implode($sepa,$result):$result;
				}
			}
			return $cols;
		}

		return $cols;
	}
	
	public function numRows($query){
		return $query->num_rows;
	}
	
	public function update($dbTable, $arr = array(), $where = ''){
	    $where = trim($where);
	    if(strlen($where) > 0 && strtolower(substr($where,0,6)) != 'where '){
			$where = 'WHERE ' . $where;
		}
		
	    $set = array();
	    foreach ($arr as $key=>$val) {
			$key = $this->escapeStr($key);
			$val = $this->escapeStr($val);
	        $set[] = "`{$key}` = '{$val}'";
	    }
		
	    $sql = 'UPDATE `' . $dbTable . '` SET ' . implode(',', $set) . ' ' . $where;
		$this->query($sql);
		
        return $this->conn->affected_rows;
	}

	public function insert($dbTable, $arr = array()){
	    $set = array();
	    foreach ($arr as $key=>$val) {
			$key = $this->escapeStr($key);
			$val = $this->escapeStr($val);
	        $set[] = "`{$key}` = '{$val}'";
	    }
		
	    $sql = 'INSERT INTO `' . $dbTable . '` SET ' . implode(',', $set);
		$this->query($sql);
		
        return $this->conn->insert_id;
	}

	public function fetchArray($query) {
		return $query->fetch_assoc();
	}
	
	public function moveToFirst($query) {
		$query->data_seek(0);
	}
	
	public function escapeStr($str){
		return $this->conn->real_escape_string($this->stripslashes_($str));
	}
	
	private function stripslashes_($html){
		if(get_magic_quotes_gpc()){
			if(is_array($html)){
				foreach ($html as $k => $v){
					$html[$k] = $this->stripslashes_($v);
				}
			} else {
				$html = stripslashes($html);
			}
		}
		return $html;
	}
	
	private function showError($html){
		echo '<div style="border:5px solid #ffffff;">';
		echo '<div style="border:3px dashed #f0f0f0;padding:10px 15px;background-color:#ffffff; line-height:180%;">';
		echo $html;
		echo '</div>';
		echo '</div>';
		exit;
	}
	
    public function startTrans() {
        if ($this->transTimes <= 0) {
            $this->conn->autocommit(false);
        }
        $this->transTimes++;
        return ;
    }

    public function commit() {
        if ($this->transTimes > 0) {
            $result = $this->conn->commit();
            $this->conn->autocommit(true);
            $this->transTimes = 0;
            if(!$result){
                return false;
            }
        }
        return true;
    }

    public function rollback() {
        if ($this->transTimes > 0) {
            $result = $this->conn->rollback();
			$this->conn->autocommit(true);
            $this->transTimes = 0;
            if(!$result){
                return false;
            }
        }
        return true;
    }

	
	//-----------------------------------
	
	public function createWhere($where) {
		$this->where($where);
		return $this->parseWhere($this->options['where']);
	}
	
    public function setActTable($tableName){
        $this->options['table'] = $tableName;
		return $this;
    }
	
	protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN');
	protected $options = array();
	
	
	public function save($arr) {
		$sql = "update `" . $this->options['table'] . "`";
		$ci = 0;
		foreach($arr as $key=>$val){
			if($ci == 0){
				$sql .= " set `{$key}` = '" . $this->escapeString($val) . "'";
			}else{
				$sql .= ",`{$key}` = '" . $this->escapeString($val) . "'";
			}
			$ci++;
		}
		$sql .= $this->parseWhere($this->options['where']);
		
		$this->query($sql);
    }

	
    public function escapeString($str) {
        return addslashes($str);
    }
	
    public function where($where,$parse=null){
        if(!is_null($parse) && is_string($where)) {
            if(!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map(array($this,'escapeString'),$parse);
            $where =   vsprintf($where,$parse);
        }elseif(is_object($where)){
            $where  =   get_object_vars($where);
        }
		
		$this->options['where'] = $where;

        return $this;
    }
	
    /**
     * where分析
     * @access protected
     * @param mixed $where
     * @return string
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        }else{ // 使用数组或者对象条件表达式
            if(isset($where['_logic'])) {
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate    =   ' '.strtoupper($where['_logic']).' ';
                unset($where['_logic']);
            }else{
                // 默认进行 AND 运算
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                $whereStr .= '( ';
                if(0===strpos($key,'_')) {
                    // 解析特殊条件表达式
                    $whereStr   .= $this->parseThinkWhere($key,$val);
                }else{
                    // 查询字段的安全过滤
                    if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
                        exit('_EXPRESS_ERROR_:'.$key);
                    }
                    // 多条件支持
                    $multi  = is_array($val) &&  isset($val['_multi']);
                    $key    = trim($key);
                    if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array =  explode('|',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' OR ',$str);
                    }elseif(strpos($key,'&')){
                        $array =  explode('&',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' AND ',$str);
                    }else{
                        $whereStr .= $this->parseWhereItem($this->parseKey($key),$val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }
    /**
     * 特殊条件分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    protected function parseThinkWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr = substr($this->parseWhere($val),6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array   =  array();
                foreach ($where as $field=>$data)
                    $array[] = $this->parseKey($field).' = '.$this->parseValue($data);
                $whereStr   = implode($op,$array);
                break;
        }
        return $whereStr;
    }
    /**
     * 字段名分析
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        return $key;
    }
    // where子单元分析
    protected function parseWhereItem($key,$val) {
        $whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i',$val[0])) { // 比较运算
                    $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                }elseif(preg_match('/^(NOTLIKE|LIKE)$/i',$val[0])){// 模糊查找
                    if(is_array($val[1])) {
                        $likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
                        $likeStr    =   $this->comparison[strtolower($val[0])];
                        $like       =   array();
                        foreach ($val[1] as $item){
                            $like[] = $key.' '.$likeStr.' '.$this->parseValue($item);
                        }
                        $whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';
                    }else{
                        $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                    }
                }elseif('exp'==strtolower($val[0])){ // 使用表达式
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                }elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                             $val[1] =  explode(',',$val[1]);
                        }
                        $zone      =   implode(',',$this->parseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
                }else{
                    throw_exception(L('_EXPRESS_ERROR_').':'.$val[0]);
                }
            }else {
                $count = count($val);
                if(in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))) {
                    $rule   = strtoupper(trim($val[$count-1]));
                    $count  = $count -1;
                }else{
                    $rule   = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    }else{
                        $op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
                        $whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr,0,-4);
            }
        }else {
            //对字符串类型字段采用模糊匹配
            if(C('DB_LIKE_FIELDS') && preg_match('/('.C('DB_LIKE_FIELDS').')/i',$key)) {
                $val  =  '%'.$val.'%';
                $whereStr .= $key.' LIKE '.$this->parseValue($val);
            }else {
                $whereStr .= $key.' = '.$this->parseValue($val);
            }
        }
        return $whereStr;
    }
    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value =  '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }
	
	
	
	
}



