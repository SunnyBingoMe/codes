use threads;

## don't buffer output
my $old_fh = select socketSession;
$| = 1;
select $old_fh;


my $thr = threads->create(\&sub1);   # Spawn the thread
print "xxx";
$thr->detach();   # Now we officially don't care any more
print "yyyy";
sleep(5);        # Let thread run for awhile
sub sub1 {
	$a = 0;
	while (1) {
		$a++;
		print("\$a is $a\n");
		sleep(1);
	}
}
