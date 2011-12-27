###########################  Author : BinSun@mail.com ##############################
$sayOk = 0; $debug = 1; $dDebug = 1; $debugOk = 0; $dDebugOk = 0;
sub say		{foreach (@_){print "$_\n";}}
sub sayOk	{if($sayOk){say @_;}}	sub endl{print "\n";}
sub debug	{if($debug){say @_;}}	sub debugOk	{if($debugOk){say @_;}}
sub dDebug	{if($dDebug){say @_;}}	sub dDebugOk{if($dDebugOk){say @_;}}
####################################################################################
