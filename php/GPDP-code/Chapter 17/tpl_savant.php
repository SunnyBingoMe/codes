<?php 
error_reporting(E_ALL);
restore_error_handler();

require_once 'Savant2.php';

$tpl =& new Savant2();
$tpl->assign('title', 'Colors of the Rainbow');
$tpl->assign('colors', array('red', 'orange', 'yellow',
	'green', 'blue', 'indigo', 'violet'));

$tpl->display('rainbow.tpl.php');
