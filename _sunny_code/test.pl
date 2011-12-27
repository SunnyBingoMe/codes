#!/usr/bin/perl

### debug begin
$sayOk = 1; $debug = 0; $dDebug = 0; $debugOk = 0; $dDebugOk = 0;
sub say		{for (my $i = 0; $i <= $#_; $i++){print "$_[$i]\n";}}
sub sayOk	{if($sayOk){say @_;}}	sub endl{print "\n";}
sub debug	{if($debug){say @_;}}	sub debugOk	{if($debugOk){say @_;}}
sub dDebug	{if($dDebug){say @_;}}	sub dDebugOk{if($dDebugOk){say @_;}}
### debug end

print "Please input the end number of array:\n";

chomp($endNr = <STDIN>);
for ($i = 0; $i <= $endNr; $i++)
{
	$array[$i] = $i;
}

foreach $i (@array)
{
	say "$i";
}

#print "$endNr\n";
exit;
