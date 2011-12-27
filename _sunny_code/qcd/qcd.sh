qcd ()
{
	#echo -e "\n\n i am in qcd.sh \n\n";
    
	mkdir -p /.qcd/tmp 
	[ -x /.qcd/tmp/qcd_tmp ] || touch /.qcd/tmp/qcd_tmp 
	chmod 700 /.qcd/tmp
	QD=/.qcd/tmp/qcd_tmp
	export QD
	/usr/local/sbin/qcd "$@"
	[ ! `cat $QD` ] || cd "`cat $QD`"
	rm -f "$QD"
    unset QD;
}
