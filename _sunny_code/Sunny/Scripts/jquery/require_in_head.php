<?php 
if(!isset($_JQUERY_REQUIRE_IN_HEAD_PHP_)){
	$_JQUERY_REQUIRE_IN_HEAD_PHP_ = 1;
	echo '
	<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
	<link rel="stylesheet" type="text/css" href="jquery/css/smoothness/jquery-ui-1.8.16.custom.css" />
	';
}
?>