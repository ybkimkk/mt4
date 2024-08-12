<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

//exit;
//require_once('ServiceAction.class.php');
require_once('ServiceActionNew.class.php');

$sa = new ServiceAction();
$sa->calcCommission();

