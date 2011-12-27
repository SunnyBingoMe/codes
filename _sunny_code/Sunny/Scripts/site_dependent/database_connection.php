<?php 
if (isset($getDatabaseConnectionFilePath)){
	$databaseConnectionFilePath = __FILE__;
}else{
	if (!isset ($_DATABASE_CONNECTION_PHP_)){
		$_DATABASE_CONNECTION_PHP_ = 1;
		
		$databaseHostName = '127.0.0.1';//hellosweden.db.6009281.hostedresource.com
		$databaseName = "mobile_lab";
		$userName = "root";
		$passWord = "workteam";
		
		
		$session = mysql_connect($databaseHostName,$userName,$passWord) or die("ERR: mysql_connect():".mysql_error());
		
		$query = "USE $databaseName ";
		mysql_query($query,$session) or die("ERR: db_connect USE: ".mysql_error());
		
		
		// names
		$toDoTable = "lab2";
		$toDoTableColumnNameList = array(
			"id",//0
			"name",//1
			"text",//2
			"time",//3
			"priority"//4
		);
	}
}
?>