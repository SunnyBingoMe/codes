#!/bin/sh 
filePath=`readlink -f $0`;
f=`dirname $filePath`;

#qcd install 
#written by xiewei 2004 

setup_content=/etc/qcd
history_dir=$setup_content/history_dir
bin_content=/usr/local/sbin
prof_content=/etc/profile.d

setup()
{
    #check 
    if [ -r $history_dir ]      #���$history_dir �Ե�ǰ�û��ɶ�
    then	
 	echo -n "You have installed qcd , overwrite it(y\Y or q\Q or n\N)? "
 	while read choice
 	do
	    if [ "$choice" = "y"  -o  "$choice" = "Y" ] #���ǰ�װ
	    then
	        break
	    fi	

	    if [ "$choice" = "q"  -o  "$choice" = "Q" ]  #ʲôҲ�����˳�
	    then
	        echo "Nothing to do!"
	        exit 1
	    fi
               
	    if [ "$choice" = "n"  -o  "$choice" = "N" ]  #��װ��Ĭ�ϵ�λ��
	    then
	        cp -f $f/qcd $bin_content/
		cp -f $f/qcd.sh $prof_content/
		echo "install qcd OK, but do not overwrite it!"
	        echo "version is v11.0618"
	        exit 1
	    fi	

	    echo -n "You have installed qcd, overwrite it(y\Y or q\Q or n\N)? " 
	done
    fi
    
    if [ -r $setup_content ] ; then
		:;
    else
		mkdir $setup_content;
    fi
    
    cp -f $f/qcd $bin_content/
    cp -f $f/history_dir $setup_content/
    cp -f $f/qcd.sh $prof_content/
}

delete()
{
    [ -r $history_dir ] || [ -r $bin_content ] || \
    ! echo "Your computer has not qcd!" || ! echo "Nothing to do!"
    
    echo -n "Are you sure to delete qcd(y\Y or q\Q)? "
    while read choice
    do
        if [ "$choice" = "y"  -o  "$choice" = "Y" ]  
        then
            break
        fi	
	
	if [ "$choice" = "q"  -o  "$choice" = "Q" ]  
	then
	    echo "Nothing to do!"
	    exit 1
	fi
	
	echo -n "Are you sure to delete qcd(y\Y or q\Q)? "
    done

    rm -rf $setup_content
    rm -f  $bin_content/qcd
    rm -f  $prof_content/qcd.sh
}

usage()
{
    echo "<install> install qcd on your computer."
    echo "<install del> delete qcd from your computer."
    exit 1
}

echo "Qcd Install Software"
echo "Written By XieWei 2004"
echo "Editted by Sunny v11.0618" 

if [ $# -eq 0 ]    #�������Ĳ�������Ϊ0�
then
    setup
    echo "install qcd OK!"
    echo "version is v11.0618"
    exit 1
fi    

if [ $# -gt 1 ]    #�������Ĳ�����������1
then
    usage
fi 

case $1 in        #�������һ��������del
del)
    delete
    echo "have delete qcd OK!"
    ;;
*)
    usage    #��ʾ��Ϣ
    ;;
esac

