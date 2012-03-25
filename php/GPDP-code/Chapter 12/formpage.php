<html>
<head>
<title>Decorator Example</title>
<style type="text/css">
.invalid {color: red; }
.invalid input { background-color: red; color: yellow; }
#myform input { position: absolute; left: 110px; width: 250px;  font-weight: bold;}
</style>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div id="myform">
<?php
error_reporting(E_ALL);
require_once 'widgets.inc.php';

$post =& Post::autoFill();
$form = FormHandler::build($post);
if ($_POST) {
	FormHandler::validate($form, $post);
}

foreach($form as $widget) {
	echo $widget->paint(), "<br>\n";
}

?>
</div>
<input type="submit" value="Submit">
</form>
</body>
</html>