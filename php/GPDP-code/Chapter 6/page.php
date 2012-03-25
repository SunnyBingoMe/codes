<?php

require_once 'classes.inc.php';
define('SELF', 'http://www.example.com/path/to/page.php');

$page =& new PageDirector(new Session, new Response);
$page->run();
