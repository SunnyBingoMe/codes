#!/bin/bash

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

debugOk "\$PWD is '$PWD'."
debugOk "\$0 is \"$0\"." # /usr/bin/orderFiles.sh
debugOk "\`basename \$0\` is \"`basename $0`\"."
