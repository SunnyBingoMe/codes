<?php 
if(!isset($_DATETIMEPICKER_REQUIRE_IN_HEAD_PHP_)){
	$_DATETIMEPICKER_REQUIRE_IN_HEAD_PHP_ = 1;
	require_once 'jquery/require_in_head.php';
	echo '
	<link rel="stylesheet" type="text/css" href="date_time_picker/jquery-ui-timepicker-addon.css" />
	<script type="text/javascript" src="date_time_picker/jquery-ui-timepicker-addon.js"></script>
	';
}
?>