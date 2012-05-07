
############################## Sunny Debug BinSun#gmail.com ###################
$sayOk = 0; $debug = 1; $debug2 = 1; $debugOk = 0; $debug2Ok = 0;
sub say		{foreach (@_){print "$_\n";}}	sub nl{say "";}
sub sayOk	{if($sayOk){say @_;}}			sub endl{print "\n";}
sub debug	{if($debug){say @_;}}			sub debugOk	{if($debugOk){say @_;}}
sub debug2 {if($debug2){say @_;}}			sub debug2Ok{if($debug2Ok){say @_;}}
###############################################################################

# Perl trim function to remove whitespace from the start and end of the string
sub trim($)
{
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}
# Left trim function to remove leading whitespace
sub ltrim($)
{
	my $string = shift;
	$string =~ s/^\s+//;
	return $string;
}
# Right trim function to remove trailing whitespace
sub rtrim($)
{
	my $string = shift;
	$string =~ s/\s+$//;
	return $string;
}
use Socket;
sub dns()
{
	my $server = gethostbyname($_[0]) or die "ERR: gethostbyname()";
	return $server = inet_ntoa($server);
}
sub rdns()
{
	my $name = gethostbyaddr(inet_aton($_[0]), AF_INET) or die "ERR: Cannot resolve ip: $_[0].\n";
	return $name;
}

