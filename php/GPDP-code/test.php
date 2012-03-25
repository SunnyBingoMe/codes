<?php

$count = 5; // "outer" count = 5

function get_count()
{
    static $count = 0; // "inner" count = 0 only the first run
    $count2 = 3;
	return $count2++; // "inner" count + 1
}

echo $count; // "outer" count is still 5 
++$count; // "outer" count is now 6 (but you never echoed it)

echo get_count(); // "inner" count is now + 1 = 1 (0 before the echo)
echo get_count(); // "inner" count is now + 1 = 2 (1 before the echo)
echo get_count(); // "inner" count is now + 1 = 3 (2 before the echo)
