#ifndef DEBUGANDALWAYS_H_INCLUDED
#define DEBUGANDALWAYS_H_INCLUDED

#include <iostream>
using namespace std;

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

#include <sys/timeb.h>
#include <time.h>

/*
 when debug
int sOk = 0;
int dbug = 1;
int dbugOk = 0;
int dDbug = 1;
int dDbugOk = 0;

 when complete:
int sOk = 1;
int dbug = 0;
int dbugOk = 0;
int dDbug = 0;
int dDbugOk = 0;
*/
int sOk = 1;
int dbug = 0;
int dbugOk = 0;
int dDbug = 0;
int dDbugOk = 0;

void say (const char * string)
{
    printf("%s\n",string);
}
void sayOk (const char * string)
{
    if(sOk)
    {
        say (string);
    }
}
void debug (const char * string)
{
    if(dbug)
    {
        say (string);
    }
}
void debugOk (const char * string)
{
    if(dbugOk)
    {
        say (string);
    }
}
void dDebug (const char * string)
{
    if(dDbug)
    {
        say (string);
    }
}
void dDebugOk (const char * string)
{
    if(dDbugOk)
    {
        say (string);
    }
}

char aTextTimeStamp[] = "2010-00-00_00:00:00.000_0";
char * timeStamp()
{
    struct timeb tTimeb;
    struct tm tTm;
    ftime(&tTimeb);
    localtime_r( &tTimeb.time, &tTm );
    sprintf(aTextTimeStamp,
            "%04d-%02d-%02d_%02d:%02d:%02d.%03d_%d",
            tTm.tm_year + 1900,
            tTm.tm_mon + 1,
            tTm.tm_mday,
            tTm.tm_hour,
            tTm.tm_min,
            tTm.tm_sec,
            tTimeb.millitm,
            tTm.tm_wday//What day is it.
            );
    aTextTimeStamp[23] = '\0';//don't need "What day is it (today)".
    return aTextTimeStamp;
/*
    char * stringTimeStamp;
    time_t rawtime;
    time ( &rawtime );
    stringTimeStamp = ctime( (time_t*) &rawtime);
    stringTimeStamp[24] = '\0';
    return stringTimeStamp;
*/
}
string timeStampString()
{
    string tString;
    tString = timeStamp();
    return tString;
}
string time_tToString(time_t tTime_t)
{
    string tString;
    tString = ctime( (time_t*) &tTime_t);
    tString.erase(tString.end()-1);
    return tString;
}

///decimal To String
#include <iostream>
#include <string>
#include <sstream>// stringstream
string decimalToString(int tInt)
{
    stringstream tStringstream;
    string tString;

    tStringstream << tInt;
    tStringstream >> tString;

    return tString;
}
///decimal To String end

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


#endif // DEBUGANDALWAYS_H_INCLUDED