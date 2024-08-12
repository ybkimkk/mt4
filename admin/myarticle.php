<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('myarticle/' . $Clause . '.php');

