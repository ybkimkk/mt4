<?php
/*
2015-10-24 更新


*/

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
		$width = (int)$_GET['w']; if($width > 0){$this->width = $width;}
		$height = (int)$_GET['h']; if($height > 0){$this->height = $height;}
		$codelen = (int)$_GET['l']; if($codelen > 0){$this->codelen = $codelen;}
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
		$chrWidth = (int)($this->width / $this->codelen);
		$fontColor = imagecolorallocate($this->img, 172,34,50);
		for ($i = 0; $i < strlen($this->code); $i++){ 
			imagestring($this->img, $this->fontsize, $chrWidth * $i + 4, 1, substr($this->code,$i,1), $fontColor); 
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

class CVCode002 {
	private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';//随机因子
	private $code;//验证码
	private $codelen = 4;//验证码长度
	private $width = 80;//宽度
	private $height = 30;//高度
	private $img;//图形资源句柄
	private $font;//指定的字体
	private $fontsize = 20;//指定字体大小
	private $fontcolor;//指定字体颜色
	//构造方法初始化
	public function __construct() {
		$width = (int)$_GET['w']; if($width > 0){$this->width = $width;}
		$height = (int)$_GET['h']; if($height > 0){$this->height = $height;}
		$codelen = (int)$_GET['l']; if($codelen > 0){$this->codelen = $codelen;}
		
		$this->font = dirname(__FILE__).'/font/elephant.ttf';//注意字体路径要写对，否则显示不了图片
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
		for ($i=0;$i<6;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
		}
		//雪花
		for ($i=0;$i<15;$i++) {
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