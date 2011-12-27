<?php
print <<<HTMLBLOCK
<html>
<head><title>Menu</title></head>
<body bgcolor="#fffed9">
<h1>Dinner</h1>
<ul>
  <li> Beef Chow-Fun
  <li> Sauteed Pea Shoots
  <li> Soy Sauce Noodles
  </ul>
</body>
</html>
HTMLBLOCK

?>
<?php
include 'sunny_function.php';
?>
<?php 
printf("%-60s....", "asldfhjalshdfla");
brn();
?>
<?php 
echo 'MD5:' . crypt('admin2', '$1$Sunny_Cr$') . "\n";
brn();
?>
<html>
<form action="overview.php" method="POST">
<!-- <input type="checkbox" name="thisName" value="thisValue" />-->
<input type="submit" value="submit It" />
</form> 
</html>
<?php

$newTrainLineName = "TestNewLineName";
$initialCharacter = strtolower(substr($newTrainLineName,0,1));
$newTrainLineName = preg_replace('/^(\w)(\w+)/',$initialCharacter.'${2}',$newTrainLineName);

brn();

$string = 'April 15, 2003';
$pattern = '/(\w+) (\d+), (\d+)/i';
$replacement = '${1}1,$3';
echo preg_replace($pattern, $replacement, $string);
?>
<?php 
session_start();

$_SESSION['count'] = $_SESSION['count'] + 1;

print "You've looked at this page " . $_SESSION['count'] . ' times.';
?>