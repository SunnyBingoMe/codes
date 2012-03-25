<?php
error_reporting(E_ALL);

require_once 'wact/framework/common.inc.php';
require_once WACT_ROOT.'template/template.inc.php';
require_once WACT_ROOT.'datasource/dictionary.inc.php';
require_once WACT_ROOT.'iterator/arraydataset.inc.php';

// simulate tabular data
$rainbow = array();
foreach (array('red', 'orange', 'yellow',
	'green', 'blue', 'indigo', 'violet') as $color) {
	  $rainbow[] = array('color' => $color);
}

$ds =& new DictionaryDataSource;
$ds->set('title', 'Colors of the Rainbow');
$ds->set('colors', new ArrayDataSet($rainbow));

$tpl =& new Template('/rainbow.html');
$tpl->registerDataSource($ds);
$tpl->display();
