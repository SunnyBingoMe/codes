/*
 * sunny_function
 *
 *  Created on: 2011-6-19
 *      Author: BinSun@mail.com
 */

#ifndef DEBUGANDALWAYS_H_INCLUDED
#define DEBUGANDALWAYS_H_INCLUDED

#include <stdio.h>
#include <stdlib.h>
#include <string.h>//memset
#include <unistd.h>
#include <ctype.h>//toupper()//

//**************************************************debug begin
/*   when debuging
#define sOk 0
#define dbug 1
#define dbugOk 0
#define dDbug 1
#define dDbugOk 0
/*   when finished
#define sOk 1
#define dbug 0
#define dbugOk 0
#define dDbug 0
#define dDbugOk 0
*/
#define sOk 0
#define dbug 1
#define dbugOk 0
#define dDbug 1
#define dDbugOk 0

#if sOk == 1
	#define sayOk say
#else
	#define sayOk 1||
#endif
#if dbug == 1
	#define debug say
#else
	#define debug 1||
#endif
#if dbugOk == 1
	#define debugOk say
#else
	#define debugOk 1||
#endif
#if dDbug == 1
	#define dDebug say
#else
	#define dDebug 1||
#endif
#if dDbugOk == 1
	#define dDebugOk say
#else
	#define dDebugOk 1||
#endif
//#include <libioP.h>
#include <stdarg.h>
#include <stdio.h>
int say (const char *format, ...){
	//printf("%s:%d", __FILE__, __LINE__);
	va_list argumentList;
	int done;
	va_start (argumentList, format);
	done = vfprintf (stdout, format, argumentList);
	va_end (argumentList);
	printf("\n");
	return done;
}
//**************************************************debug end

//**************************************************timestamp begin
#include <sys/timeb.h>
#include <time.h>
char gaTextTimeStamp[] = "2010-00-00_00:00:00.000_0";//25 chars use 26 memory
char *timeStamp(){
    struct timeb tp;
    struct tm ltime;
    ftime(&tp);
    localtime_r(&tp.time,&ltime);
    sprintf(gaTextTimeStamp,
            "%04d-%02d-%02d_%02d:%02d:%02d.%03d_%d",
            ltime.tm_year+1900,
            ltime.tm_mon+1,
            ltime.tm_mday,
            ltime.tm_hour,
            ltime.tm_min,
            ltime.tm_sec,
            tp.millitm,
            ltime.tm_wday//What day is it.
            );
    gaTextTimeStamp[23] = '\0';//don't need "What day is it (today)".
    return gaTextTimeStamp;
}
char *cTimeStamp(){
	char * tgaTextTimeStamp;
    time_t rawtime;
    time ( &rawtime );
    tgaTextTimeStamp = ctime( (time_t*) &rawtime);
    memmove(gaTextTimeStamp, tgaTextTimeStamp, 25);
    gaTextTimeStamp[24] = '\0';
    return gaTextTimeStamp;
}
//**************************************************timestamp end

//**************************************************string begin
unsigned int crc(unsigned char *ptr, unsigned char len) {
	unsigned char i;
	unsigned int crcValue=0;
	while(len--!=0) {
		for(i=0x80; i!=0; i/=2) {
			if((crcValue&0x8000)!=0) { /* 余式CRC 乘以2 再求CRC */
				crcValue*=2;
				crcValue^=0x1021;
			}
			else {
				crcValue*=2;
			}
			if((*ptr&i)!=0){
				crcValue^=0x1021; /* 再加上本位的CRC */
			}
		}
		ptr++;
	}
	return(crcValue);
}
unsigned int stringCrc(unsigned char * ptr){
	return crc(ptr, strlen(ptr));
}

/*
#include <sstream>// stringstream
string decimalToString(int tInt)
{
    stringstream tStringstream;
    string tString;

    tStringstream << tInt;
    tStringstream >> tString;

    return tString;
}

string toLowString(string tString)
{
    transform(tString.begin(), tString.end(), tString.begin(), ::tolower);
    return tString;
}
string toUpString(string tString)
{
    transform(tString.begin(), tString.end(), tString.begin(), ::toupper);
    return tString;
}

char* toLowString(char* pString)
{
    string tString = pString;
    transform(tString.begin(), tString.end(), tString.begin(), ::tolower);
    memmove(pString,tString.c_str(),tString.length());
    return pString;
}
char* toUpString(char* pString)
{
    string tString = pString;
    transform(tString.begin(), tString.end(), tString.begin(), ::toupper);
    memmove(pString,tString.c_str(),tString.length());
    return pString;
}
*/
int toLowString(char* pStringPartDestination,char* pStringPartSource,size_t tSize)
{
    size_t i;
    for(i = 0; i<tSize; i++)
    {
        pStringPartDestination[i] = toupper(pStringPartSource[i]);
    }
    return 0;
}
int toUpString(char* pStringPartDestination,char* pStringPartSource,size_t tSize)
{
    size_t i;
    for(i = 0; i<tSize; i++)
    {
        pStringPartDestination[i] = tolower(pStringPartSource[i]);
    }
    return 0;
}
/*
int toLowString(char* pStringPart,size_t tSize)
{
    toLowString(pStringPart,pStringPart,tSize);
    return 0;
}
int toUpString(char* pStringPart,size_t tSize)
{
    toUpString(pStringPart,pStringPart,tSize);
    return 0;
}
*/
//**************************************************string end
#endif // DEBUGANDALWAYS_H_INCLUDED
