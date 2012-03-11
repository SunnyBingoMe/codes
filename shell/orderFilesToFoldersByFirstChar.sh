#!/bin/bash
# modified from \Dropbox\NetSecurity\assignments\2.2 Q _NS_ET2437.pdf
# author: BinSun@mail.com

timeStart=`date`
thisScriptFilename=`basename $0`

	dbug=0
	dbugOk=0
	vbose=0
	function debug(){
		if [ $dbug == 1 ] ; then
			echo $1
		fi
	}
	function debugOk(){
		if [ $dbugOk == 1 ] ; then
			echo $1
		fi
	}
	function verbose(){
		if [ $vbose == 1 ]; then
			echo $1
		fi
	}


	#char types
	function echoBold(){
		echo $2 -e "\033[1m$1\033[0m"
		tput sgr0
	}
	#colored char (with bold, with bg). 47: white bg 
	function echoRedAndWhite(){
		echo $2 -e '\E[47;31m'"\033[1m$1\033[0m" #31: red char
		tput sgr0
	}
	function echoGreenAndWhite(){
		echo $2 -e '\E[47;32m'"\033[1m$1\033[0m" # 32: green char
		tput sgr0
	}
	#colored char (with bold)
	function echoRed(){
		echo $2 -e "\E[33;31m""\033[1m$1\033[0m" # 31: red char
		tput sgr0
	}
	function echoGreen(){
		echo $2 -e "\E[33;32m""\033[1m$1\033[0m" # 32: green char
		tput sgr0
	}
	function echoYellow(){
		echo $2 -e "\E[33;33m""\033[1m$1\033[0m" # 33: yellow char
		tput sgr0
	}
	function echoBlue(){
		echo $2 -e "\E[33;34m""\033[1m$1\033[0m" # 34: blue char
		tput sgr0
	}


function applicationReport(){
	echoYellow "Application Report:"
	if [ -s "$tmpFile" ];then 
		echo "Total number of files moved for each filetype:" |tee -a "$txtFile"
		echo -e "    No. Filetype" | tee -a "$txtFile"
		cat "$tmpFile" | sort | uniq -c |tee -a "$txtFile"
		echo -e "Total number of archives processed:\n      $archiveFileNr" |tee -a "$txtFile"
	else 
		echo "Nothing to do"|tee -a "$txtFile"
	fi 
	rm -f "$tmpFile"
	timeStop=`date`
	echo "Started at: $timeStart." |tee -a "$txtFile"
	echo "Stopped at: $timeStop." |tee -a "$txtFile"
	echo "" |tee -a "$txtFile"
}
function signalInterrupt(){
	echoRed "\nInterrupted by user (SIGINT)." -e
	applicationReport
	exit 0
}
trap "signalInterrupt" 2 #SIGINT


debugOk "\$PWD is '$PWD'."
debugOk "\$0 is \"$0\"." # /usr/bin/orderFiles.sh
debugOk "\`basename \$0\` is \"`basename $0`\"."

opcode=''
noOrderFiles=0
archiveFileNr=0
directory=.
vbose=0
unpack=0
if $(test $# -gt 0);then 
	debugOk "$# greater than 0"
	i=1
	for tmp in $@; do 
		parameterList[$i]=$tmp
		i=`expr $i + 1`
	done 
	for tParameterIndex in $(seq 1 $#) ; do
		tParameter=${parameterList[$tParameterIndex]}
		debugOk "tParameter is '$tParameter'."
		if [ $tParameter == -c ] || [ $tParameter == -d ] ; then 
			tParameterIndexNext=`expr $tParameterIndex + 1`
			debugOk "tParameterIndexNext=$tParameterIndexNext"
			tParameterNext=${parameterList[$tParameterIndexNext]}
			debugOk "tParameterNext=$tParameterNext."
			parameterList[`expr $tParameterIndex+1`]=$(printf "%s%s" $tParameter $tParameterNext)
			continue
		fi 
		opcode=${tParameter:0:2}
		if [ $opcode == -d ] ; then
			directory=${tParameter#-d}
			directory=${directory%/}
			if [ ! -d $directory ];then
				echoRedAndWhite "ERR: directory '$directory' does not exit."
				exit 1
			fi 
		elif [ $opcode == -c ] ; then
			noOrderFiles=1
			touch "$directory/${tParameter#-c}" && echo "File '$directory/${tParameter#-c}' created."
		elif [ $opcode == -v ] ; then
			vbose=1
		elif [ $opcode == -z ] ; then
			unpack=1
		fi
	done
fi 


if [ $noOrderFiles == 0 ] ; then
	echoYellow "Target directory is set to: '$directory/'."
	txtFile="$directory/.order_files_report.txt"
	tmpFile="$directory/.order_files_report.tmp"
	echo -n "" > "$txtFile"
	echo -n "" > "$tmpFile"
	for tItemName in "$directory"/* ; do
		#sleep 1
		debug "in for, tItemName=$tItemName."
		tBasename=${tItemName##*/}
		debugOk "tBasename=$tBasename"
		if [ "$thisScriptFilename" == "$tBasename" ] ; then #skip this script itself
			debugOk "find myself, nothing will do to myself."
			continue
		fi

		if [ -f "$tItemName" ] ; then
			tItemBasename=${tItemName##*/}
			tFileSuffix=${tItemBasename:0:1}
			debug "suffix is: $tFileSuffix"
			if [ $vbose == 1 ]; then
				echoGreen "$tItemName" -n
				echo " is file."
			fi 
			if [ "`basename "$tFileSuffix"`" == "$tBasename" ];then 
				continue 
			fi
			if [ ! -d "$directory/$tFileSuffix" ] && [ -n "$tFileSuffix" ]; then
				mkdir "$directory/$tFileSuffix" && verbose "Folder '$tFileSuffix' created."
			fi
			if [ "$tFileSuffix" == 'tgz' ] || [ "$tFileSuffix" == 'tar' ]; then 
				archiveFileNr=`expr $archiveFileNr + 1`
			fi 
			mv "$tItemName" "$directory/$tFileSuffix" && \
				verbose "File '$tItemName' has been moved into folder '$tFileSuffix'." && \
				echo "$tFileSuffix" >> "$tmpFile"
		elif [ -d "$tItemName" ] ; then
			if [ $vbose == 1 ];then 
				echoBold "$tItemName" -n
				echo " is dir."
			fi 
		else 
			echo "not file, not folder."
		fi
	done
	if [ -d "$directory/tgz" ];then 
		verbose "Entering '$directory/tgz'."
		cd "$directory/tgz"
		debugOk "pwd: '$PWD'."
		for tTgzFile in "$PWD"/* ; do
			if [ -f "$tTgzFile" ];then
				if [ $unpack == 1 ];then
					if [ ! -d "${tTgzFile%.tgz}" ];then
						mkdir "${tTgzFile%.tgz}"
					fi
					if [ $vbose == 0 ];then
						tar -zxf "$tTgzFile" -C "${tTgzFile%.tgz}"
					else 
						tar -zxvf "$tTgzFile" -C "${tTgzFile%.tgz}"
					fi 
				fi 
			fi 
		done 
		verbose "Leaving '$directory/tgz'."
		cd - >> /dev/null
		debugOk "pwd: '$PWD'."
	fi 
	if [ -d "$directory/tar" ];then 
		verbose "Entering '$directory/tar'."
		cd "$directory/tar"
		debugOk "pwd: '$PWD'."
		for tTarFile in "$PWD"/* ; do
			if [ -f "$tTarFile" ];then
				if [ $unpack == 1 ];then 
					if [ ! -d "${tTarFile%.tar}" ];then
						mkdir "${tTarFile%.tar}"
					fi
					if [ $vbose == 0 ];then
						tar -xf "$tTarFile" -C "${tTarFile%.tar}"
					else 
						tar -xvf "$tTarFile" -C "${tTarFile%.tar}"
					fi 
				fi 
			fi 
		done 
		verbose "Leaving '$directory/tar'."
		cd - >> /dev/null
		debugOk "pwd: '$PWD'."
	fi 
	cd "$directory"
	if [ ! -d 'archive' ] && [ $archiveFileNr -gt 0 ]; then 
		mkdir 'archive'
	fi 
	if [ -d tgz ] && [ $archiveFileNr -gt 0 ];then
		cp -rf tgz/* archive && rm -rf tgz
	fi 
	if [ -d tar ] && [ $archiveFileNr -gt 0 ];then 
		cp -rf tar/* archive && rm -rf tar
	fi 
	echoBlue "Finished."
	applicationReport
fi 
exit 0

