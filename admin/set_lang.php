<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once('conn.php');

$lang = FGetStr('lang');
if(!in_array($lang,array_keys($LangNameList['list']))){
	$lang = $LangNameList['default'];
}

setcookie('lang',$lang,time() + 60 * 60 * 24 * 365,'/');

FRedirect(FPrevUrl());