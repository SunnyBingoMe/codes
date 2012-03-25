<?php
require_once 'phplib/smarty/Smarty.class.php';

$tpl =& new Smarty;
$tpl->assign(array(
  'title' => 'Colors of the Rainbow'
   ,'colors' => array('red', 'orange', 'yellow',
  	'green', 'blue', 'indigo', 'violet')
  ));
$tpl->display('rainbow.tpl');
