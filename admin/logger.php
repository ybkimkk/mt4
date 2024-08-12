<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('logger/' . $Clause . '.php');

