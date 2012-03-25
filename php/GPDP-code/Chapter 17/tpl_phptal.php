<?php

require_once 'PHPTAL.php'; 

class RainbowColor {
  public $color;
  public function __construct($color) {
    $this->color = $color;
  }
}

// make a collection of colors
$colors = array();
foreach (array('red', 'orange', 'yellow',
  'green', 'blue', 'indigo', 'violet') as $color) {
  $colors[] = new RainbowColor($color);
}
		  
$tpl = new PHPTAL('rainbow.tal.html');
$tpl->title = 'Colors of the Rainbow';
$tpl->colors = $colors;

try {
    echo $tpl->execute();
}
catch (Exception $e){
    echo $e;
}

