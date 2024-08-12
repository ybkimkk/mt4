<?php
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ERROR | E_PARSE);
@ini_set("display_errors", "Off");
@date_default_timezone_set('Asia/Shanghai');
@ini_set('Asia/Shanghai');

class CVCode001 {
	private $charset = '0123456789';//随机因子
	private $code;//验证码
	private $codelen = 4;//验证码长度
	private $width = 55;//宽度
	private $height = 18;//高度
	private $img;//图形资源句柄
	private $font;//指定的字体
	private $fontsize = 5;//指定字体大小
	private $fontcolor;//指定字体颜色
	//构造方法初始化
	public function __construct() {
		$width = intval($_GET['w']); if($width > 0){$this->width = $width;}
		$height = intval($_GET['h']); if($height > 0){$this->height = $height;}
		$codelen = intval($_GET['l']); if($codelen > 0){$this->codelen = $codelen;}
	}
	//生成随机码
	private function createCode() {
		$max = strlen($this->charset) - 1; 
		mt_srand((double)microtime() * 1000000); 
		for($i = 0; $i < $this->codelen; $i++) { 
			$this->code .= $this->charset[mt_rand(0, $max)]; 
		} 
	}
	//生成背景
	private function createBg() {
		$this->img = imagecreate($this->width, $this->height);
		$bgColor = imagecolorallocate($this->img, 218,218,218);
		imagefill($this->img,$this->width,$this->height,$bgColor);
		//imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
	}
	//生成文字
	private function createFont() {
		$chrWidth = intval($this->width / $this->codelen);
		
		$pointX = intval(((floatval($this->width) / floatval($this->codelen)) - 10) / 2);if($pointX < 4){$pointX = 4;}
	
		$fontColor = imagecolorallocate($this->img, 172,34,50);
		for ($i = 0; $i < strlen($this->code); $i++){ 
			$pointY = intval(($this->height - 18) / 2);if($pointY < 1){$pointY = 1;}else{$pointY = mt_rand(1,($this->height - 18));}
			
			imagestring($this->img, $this->fontsize, $chrWidth * $i + $pointX, $pointY, substr($this->code,$i,1), $fontColor); 
		}
	}
	//生成线条、雪花
	private function createLine() {
		$pointColor = imagecolorallocate($this->img, 0,0,0);
		for($i = 0;$i < 15;$i++){
			imagesetpixel($this->img, rand() % $this->width , rand() % $this->height , $pointColor);
		}
	}
	//输出
	private function outPut() {
		header("Content-type: image/PNG"); 
		imagepng($this->img);
		imagedestroy($this->img);
	}
	//对外生成
	public function drawImg() {
		$this->createBg();
		$this->createCode();
		$this->createLine();
		$this->createFont();
		$this->outPut();
	}
	//获取验证码
	public function getVCode() {
		return strtolower($this->code);
	}
}

$_vc = new CVCode001();
$_vc->drawImg();	

session_start();	
$_SESSION['VCode'] = $_vc->getVCode();