<?php
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ERROR | E_PARSE);
@ini_set("display_errors", "Off");
@date_default_timezone_set('Asia/Shanghai');
@ini_set('Asia/Shanghai');

class CVCode002 {
	private $charset = '0123456789';//随机因子
	private $code;//验证码
	private $codelen = 4;//验证码长度
	private $width = 120;//宽度
	private $height = 50;//高度
	private $img;//图形资源句柄
	private $font;//指定的字体
	private $fontsize = 20;//指定字体大小
	private $fontcolor;//指定字体颜色
	//构造方法初始化
	public function __construct() {
		$width = (int)$_GET['w']; if($width > 0){$this->width = $width;}
		$height = (int)$_GET['h']; if($height > 0){$this->height = $height;}
		$codelen = (int)$_GET['l']; if($codelen > 0){$this->codelen = $codelen;}
		
		$this->font = '000.php';//注意字体路径要写对，否则显示不了图片
	}
	//生成随机码
	private function createCode() {
		$_len = strlen($this->charset)-1;
		for ($i=0;$i<$this->codelen;$i++) {
			$this->code .= $this->charset[mt_rand(0,$_len)];
		}
	}
	//生成背景
	private function createBg() {
		$this->img = imagecreatetruecolor($this->width, $this->height);
		$color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
		imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
	}
	//生成文字
	private function createFont() {
		$_x = $this->width / $this->codelen;
		for ($i=0;$i<$this->codelen;$i++) {
			$this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
		}
	}
	//生成线条、雪花
	private function createLine() {
		//线条
		for ($i=0;$i<3;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
		}
		//雪花
		for ($i=0;$i<5;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
			imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
		}
	}
	//输出
	private function outPut() {
		header('Content-type:image/png');
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

$_vc = new CVCode002();
$_vc->drawImg();	

session_start();	
$_SESSION['VCode'] = $_vc->getVCode();