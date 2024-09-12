<?php
/*
//$AdminFloder = $_COOKIE['adminFloder'];
//if(strlen($AdminFloder) <= 0){
	$AdminFloder = substr($_SERVER['REQUEST_URI'],1);
	$AdminFloder = substr($AdminFloder,0,stripos($AdminFloder,'/'));
	$AdminFloder = '/' . $AdminFloder . '/';
//	setcookie('adminFloder',$AdminFloder,time() + 60 * 60 * 24 * 365,'/');
//}
define('CC_ADMIN_ROOT_FOLDER',$AdminFloder);*/

//-------------------------------------------------------------------------------------
$LangNameList = array(
    'list' => array(
        'en-us' => array('title' => 'English', 'color' => '#EEEEFF'),
        'zh-vn' => array('title' => 'Tiếng Việt', 'color' => '#FFFFF0'),
        'zh-cn' => array('title' => '中文', 'color' => '#FFF2F2'),
        'id' => array('title' => 'Indonesia', 'color' => '#F0FFF0'),
        'tc' => array('title' => '繁體中文', 'color' => '#F0FFF0'),
        'korean' => array('title' => '한국어', 'color' => '#F0FFF0'),
        'japanese' => array('title' => '日本語', 'color' => '#F0FFF0'),
        'arabic' => array('title' => 'بالعربية', 'color' => '#F0FFF0'),
    ),
    'default' => 'en-us',
);

//项目前置设置
if ($ConfigItemPre && $ConfigItemPre['lang_default']) {
    $LangNameList['default'] = $ConfigItemPre['lang_default'];
}

if ($ConfigItemPre && $ConfigItemPre['lang_unset']) {
    foreach ($ConfigItemPre['lang_unset'] as $key_ => $val_) {
        unset($LangNameList['list'][$val_]);
    }
}

$CurrLangName = $_COOKIE['lang'];
if (strlen($CurrLangName) <= 0) {
    $CurrLangName = $LangNameList['default'];
}

//echo $CurrLangName;

$_lang = array();
require_once($_SERVER['DOCUMENT_ROOT'] . '/lang/' . $CurrLangName . '.php');

//-------------------------------------------------------------------------------------