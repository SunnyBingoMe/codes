package sunny_function;
use strict;
use warnings;
use Exporter;
use POSIX qw/strftime/;

our @ISA = qw (Exporter);
our @EXPORT = qw (
	$say
	say
	$sayOk
	sayOk
	nl
	$debug
	debug
	$debugOk
	debugOk
	$debug2
	debug2
	$debug2Ok
	debug2Ok
	trim
	ltrim
	rtrim
	dns
	rdns
	timestampRfc
);
our @EXPORT_OK = qw ();

sub say
{
	my $timestampYmd = timestampRfcYmd();
	my $timestampRfc = timestampRfc();
	system ("echo '[$timestampRfc]' >> /home/ats/thesis/$timestampYmd.log");
	foreach(@_){
		my $tString = $_."\n";
		print $tString;
		system("echo '$tString' >>/home/ats/thesis/$timestampYmd.log");
	}
}
##################### Sunny Debug BinSun#gmail.com ###################
$sayOk = 0; $debug = 1; $debug2 = 1; $debugOk = 0; $debug2Ok = 0;
#sub say		{foreach (@_){print "$_\n";}}
sub nl{say "";}
sub sayOk	{if($sayOk){say @_;}}			sub endl{print "\n";}
sub debug	{if($debug){say @_;}}			sub debugOk	{if($debugOk){say @_;}}
sub debug2 {if($debug2){say @_;}}			sub debug2Ok{if($debug2Ok){say @_;}}
######################################################################

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

#use POSIX qw/strftime/;
sub timestampRfc {
	return strftime('%Y-%m-%d %H:%M:%S',localtime($_[0]?$_[0]:time())); 
}
sub timestampRfcYmd
{
	return strftime('%Y-%m-%d',localtime($_[0]?$_[0]:time())); 
}
sub timestampUnderline
{
	return strftime('%Y%m%d_%H%M%S',localtime($_[0]?$_[0]:time())); 
}

