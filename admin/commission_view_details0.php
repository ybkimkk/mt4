<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if ($Clause == 'saveuser') {

}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('commission_view_details0/' . $Clause . '.php');

