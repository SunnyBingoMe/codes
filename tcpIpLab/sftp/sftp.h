#ifndef SFTP_H_INCLUDED
#define SFTP_H_INCLUDED

#include <algorithm> //transform string up/low
#include <arpa/inet.h>//inet_net_ntop...
#include <dirent.h>//list directory
#include <errno.h>
#include <netdb.h>//getaddrinfo...
#include <netinet/in.h>//Standard well-defined IP protocols
#include <signal.h>//handle child process
#include <stdio.h>
#include <string.h>
#include <sys/socket.h>
#include <sys/stat.h>//file size
#include <sys/statvfs.h>//file system information
#include <sys/types.h>//fcn waitpid
#include <sys/wait.h> //fcn waitpid; 3rd parameter of fcn waitpid;
//#include <stdint.h> //intmax
using namespace std;

char aDefaultOpenReadType[] = "rb";
char aDefaultOpenWriteType[] = "wb";
//string sDefaultCurrentPathRemote = "/media/NOW/ET2440_TCPIP/tftp";
int intMax = 2147483647;

///temp test code
int tSizeDefaultFile = 2519;
///temp test code end

#include "frequentFunction.h"
#include "sftpFunctionDeclaration.h"
#include "sftpStruct.h"

int openReadFile(string tsFileAddress)
{
    debug("ok: openReadFile(string).start.");
    struct stat tStat;
    if ( (pAboutReadFile->pReadFile = fopen(tsFileAddress.c_str(), pAboutReadFile->pOpenReadType)) == NULL)
    {
        printf("ERR: openReadFile(string): fopen(), tsFileAddress:%s.\n",tsFileAddress.c_str());
        return 1;
    }
    else
    {
        if(dbugOk)printf("ok: openReadFile(string): fopen(), tsFileAddress:%s.\n",tsFileAddress.c_str());
    }
    stat(tsFileAddress.c_str(), &tStat);
    pAboutReadFile->sizeFile = tStat.st_size;

    debug("ok: openReadFile(string).end.");
    return 0;
}
int openReadFile(string tsPath, string tsFilename)
{
    debug("ok: openReadFile(string, string).start.");
    string tsFileAddress = tsPath + tsFilename;
    return(openReadFile(tsFileAddress));
}
int openReadFile()
{
    debug("ok: openReadFile().start.");
    struct stat tStat;

    if ( (pAboutReadFile->pReadFile = fopen(pSession->sLocalFileAddress.c_str(), pAboutReadFile->pOpenReadType)) == NULL)
    {
        printf("ERR: openReadFile(): fopen(), sLocalFileAddress:%s.\n",pSession->sLocalFileAddress.c_str());
        pAboutReadFile->sizeFile = -1;
        return 1;
    }
    else
    {
        if(dbugOk)printf("ok: openReadFile(): fopen(), sLocalFileAddress:%s.\n",pSession->sLocalFileAddress.c_str());
        stat(pSession->sLocalFileAddress.c_str(), &tStat);
        pAboutReadFile->sizeFile = tStat.st_size;
        if(pAboutReadFile->sizeFile < 0)
        {
            pAboutReadFile->sizeFile = intMax;
        }
    }

    debug("ok: openReadFile().end.");
    return 0;
}
int closeReadFile()
{
    debug("ok: closeReadFile().start.");
    if(pAboutReadFile->pReadFile != NULL)
    {
        fclose(pAboutReadFile->pReadFile);
    }
    else
    {
        say("ok: file not opened.");
    }
    debug("ok: closeReadFile().end.");
    return 0;
}
int openWriteFile()
{
    debug("ok: openWriteFile().start.");
    if ( (pAboutWriteFile->pWriteFile = fopen(pSession->sLocalFileAddress.c_str(), pAboutWriteFile->pOpenWriteType)) == NULL)
    {
        printf("ERR: openWriteFile(): fopen(), sLocalFileAddress:%s.\n",pSession->sLocalFileAddress.c_str());
        return 1;
    }

    debug("ok: openWriteFile().end.");
    return 0;
}
int closeWriteFile()
{
    debug("ok: closeWriteFile().start.");
    if(pAboutWriteFile->pWriteFile != NULL)
    {
        fclose(pAboutWriteFile->pWriteFile);
    }
    else
    {
        say("ok: file not opened.");
    }
    debug("ok: closeWriteFile().end.");
    return 0;
}
int readBinaryDataFromFile()
{
    debug("ok: readBinaryDataFromFile().start.");
    int iFread = 0;
    char readTempChar;

    pAboutReadFile->sizeReadUnit = sizeof(char);
    while( fread(&readTempChar,pAboutReadFile->sizeReadUnit,1, pAboutReadFile->pReadFile), !feof(pAboutReadFile->pReadFile) && !ferror(pAboutReadFile->pReadFile) )
    {
        if(dDbugOk){printf("ok: read %c\n", readTempChar);}
        pAboutReadFile->aReadBuffer[iFread] = readTempChar;
        if(dDbugOk){printf("ok: iFread and aReadBuffer[iFread]:%d,%c.\n",iFread,pAboutReadFile->aReadBuffer[iFread]);}
        iFread ++;
        if(iFread == maxReadOnce)
        {
            break;
        }
    }
    if (feof(pAboutReadFile->pReadFile))
    {
        pPrepareSftpDataPacket->reachEofSend = 1;
        if(dbug){printf("ok: feof(), iFread(last read):%dbytes.\n",iFread);}
    }
    if (ferror(pAboutReadFile->pReadFile))
    {
        printf("ERR: read file.\n");
        return 1;
    }

    pAboutReadFile->sizeReadData = iFread;
    if (dbug){printf("ok: read:%d bytes.\n",pAboutReadFile->sizeReadData);}

    if (dDbugOk){printf("ok: aReadBuffer:%s.\n",pAboutReadFile->aReadBuffer);}

    debug("ok: readBinaryDataFromFile().end.");
    return 0;
}
int writeBinaryDataToFile()
{
    debug("ok: writeBinaryDataToFile().start.");
    int tSizeDataHasWritten;

    pAboutWriteFile->sizeWriteUnit = sizeof(char);
    tSizeDataHasWritten = fwrite(pAboutWriteFile->pWriteBuffer, pAboutWriteFile->sizeWriteUnit, pAboutWriteFile->sizeWriteData, pAboutWriteFile->pWriteFile);
    if(tSizeDataHasWritten != pAboutWriteFile->sizeWriteData)
    {
        printf("ERR: want write: %d bytes, but written: %d bytes.",pAboutWriteFile->sizeWriteData,tSizeDataHasWritten);
        return 1;
    }
    if(dbug){printf("ok: written: %d bytes.\n",tSizeDataHasWritten);}

    debug("ok: writeBinaryDataToFile().end.");
    return 0;
}
void * getBinaryIp(struct sockaddr * tSockaddr)// get sockaddr, IPv4 or IPv6:
{
    if (tSockaddr->sa_family == AF_INET)
    {
        return &(((struct sockaddr_in*)tSockaddr)->sin_addr);
    }

    return &(((struct sockaddr_in6*)tSockaddr)->sin6_addr);
}
int newStruct()
{
    debug("ok: newStruct().start.");

    pSession = new session;
    pAboutReadFile = new aboutReadFile;
    pAboutWriteFile = new aboutWriteFile;

    ///send
    pPrepareSftpCommandPacket = new prepareSftpCommandPacket;
    pPrepareSftpDataPacket = new prepareSftpDataPacket;
    pPrepareTcpLoad = new prepareTcpLoad;
    pPrepareSftpMessagePacket = new prepareSftpMessagePacket;
    ///send end

    pSftpCommandPacket = pPrepareSftpCommandPacket->pOriginalAddress = new sftpCommandPacket;
    pSftpDataPacket = pPrepareSftpDataPacket->pOriginalAddress = new sftpDataPacket;
    pSftpMessagePacket = pPrepareSftpMessagePacket->pOriginalAddress = new sftpMessagePacket;

    pSftpPacket = new sftpPacket;

    ///receive
    pHandleSftpPacket = new handleSftpPacket;
    pHandleSftpDataPacket = new handleSftpDataPacket;
    pHandleSftpCommandPacket = new handleSftpCommandPacket;
    pHandleTcpLoad = new handleTcpLoad;
    ///receive end

    debug("ok: newStruct().end.");
    return 0;
}

int sendTheRequestFile()
{
    debug("\nok: sendTheRequestFile().start.");

    openReadFile();

    pPrepareSftpDataPacket->reachEofSend = 0;
    pSession->fileTimer = clock();

    while(1) //transfer all data of the file
    {
        packSftpDataPacket();
        packTcpPacket();

        if(pPrepareSftpDataPacket->reachEofSend)
        {
            debug("ok: sendTheRequestFile() reach eof.");
            break;
        }

        say("");
    }

    closeReadFile();
    pPrepareSftpDataPacket->reachEofSend = 0;

    pSession->fileTimer = clock() - pSession->fileTimer;
    pSession->fileTimer = pSession->fileTimer/CLOCKS_PER_SEC;
    if(pSession->fileTimer == 0)
    {
        pSession->fileTimer = 1;
    }
    printf("ok: send file speed: %i bytes/s.\n",pAboutReadFile->sizeFile/(pSession->fileTimer) );

    debug("\nok: sendTheRequestFile().end.\n");
    return 0;
}
int receiveTheRequestFile()
{
    debug("ok: receiveTheRequestFile().start.");

    if(openWriteFile())
    {
        say("ERR: receiveTheRequestFile():openWriteFile().");
        return 1;
    }

    pAboutWriteFile->sizePassedFile = 0;
    pHandleSftpDataPacket->reachEofReceive = 0;//?
    pSession->fileTimer = clock();

    debug("\nok: \"while\": receive and write.start.\n");
    while(1) ///the "while" of: receive all the data of remote file and write to local file
    {
        unpackTcpPacket();
        unpackSftpPacket();

        if(pHandleSftpDataPacket->reachEofReceive)//?
        {
            debug("ok: the \"while\" of: receive: reachEofReceive.");
            break;
        }

        debug("");
    }
    debug("\nok: \"while\": receive and write.end.\n");

    closeWriteFile();

    pHandleSftpDataPacket->reachEofReceive = 0;//?

    pSession->fileTimer = clock() - pSession->fileTimer;
    pSession->fileTimer = pSession->fileTimer/CLOCKS_PER_SEC;
    if(pSession->fileTimer == 0)
    {
        pSession->fileTimer = 1;
    }
    printf("ok: receive file speed: %i bytes/s.\n",pAboutWriteFile->sizeFile/pSession->fileTimer );
    pAboutWriteFile->sizeFile = 0;

    debug("ok: receiveTheRequestFile().end.");
    return 0;
}

int packSftpCommandPacket()
{
    debug("ok: packSftpCommandPacket().start.");
    pSftpCommandPacket = pPrepareSftpCommandPacket->pOriginalAddress;
    memset(pSftpCommandPacket,'\0',sizeof(sftpCommandPacket));

    pSession->commandTimer = clock();

    if(pPrepareSftpCommandPacket->sCommand.length() < 4 || pPrepareSftpCommandPacket->sCommand.length() > tcpLoadMax)
    {
        say("ERR: the length of the command is too short or too long.");
        pPrepareSftpCommandPacket->sCmd.clear();
        pPrepareTcpLoad->sizeTcpLoadToSend = 0;
        return 1;
    }
    char tCmd[5];
    pPrepareSftpCommandPacket->sCommand.copy(tCmd,4,0);//return int: 4
    tCmd[4] = '\0';
    pPrepareSftpCommandPacket->sCmd = tCmd;
    pPrepareSftpCommandPacket->sCmd = toUpString(pPrepareSftpCommandPacket->sCmd);
    if(dbug)printf("ok: cmd: %s.\n",pPrepareSftpCommandPacket->sCmd.c_str());
    pPrepareSftpCommandPacket->sCommand.replace(0,4,pPrepareSftpCommandPacket->sCmd);
    if(pPrepareSftpCommandPacket->sCommand.length() > 5)
    {
        pPrepareSftpCommandPacket->sArgs = (char*)pPrepareSftpCommandPacket->sCommand.c_str() + 5;
        if(dbug)printf("ok: args: %s.\n",pPrepareSftpCommandPacket->sArgs.c_str());
    }
    else
    {
        pPrepareSftpCommandPacket->sArgs = "";
        debug("ok: no argument.");
    }

    memmove(pSftpCommandPacket->aCommand,pPrepareSftpCommandPacket->sCommand.c_str(),pPrepareSftpCommandPacket->sCommand.size());
    pSftpCommandPacket->aCommand[pPrepareSftpCommandPacket->sCommand.size()] = '\0';

    pPrepareTcpLoad->pTcpLoadToSend = pSftpCommandPacket->aCommand;
    pPrepareTcpLoad->sizeTcpLoadToSend = pPrepareSftpCommandPacket->sCommand.size() + 1;


    debug("ok: packSftpCommandPacket().end.");
    return 0;
}
int packSftpMessagePacket(string tsMessageContent)
{
    debug("ok: packSftpMessagePacket().start");
    pSftpMessagePacket = pPrepareSftpMessagePacket->pOriginalAddress;
    memset(pSftpMessagePacket,'\0',sizeof(sftpMessagePacket));

    tsMessageContent.copy(pSftpMessagePacket->aErrorMessage,tsMessageContent.size());
    pSftpMessagePacket->aErrorMessage[tsMessageContent.size()] = '\0';

    pPrepareTcpLoad->pTcpLoadToSend = (char*)pSftpMessagePacket;
    pPrepareTcpLoad->sizeTcpLoadToSend = tsMessageContent.size()+1;

    debug("ok: packSftpMessagePacket().end.");
    return 0;
}
int packSftpDataPacket()
{
    debug("ok: packSftpDataPacket().");
    pSftpDataPacket = pPrepareSftpDataPacket->pOriginalAddress;
    memset(pSftpDataPacket,'\0',sizeof(sftpDataPacket));

    readBinaryDataFromFile();

    pPrepareTcpLoad->pTcpLoadToSend = pAboutReadFile->aReadBuffer;
    pPrepareTcpLoad->sizeTcpLoadToSend = pAboutReadFile->sizeReadData;

    debug("ok: packSftpDataPacket().");
    return 0;
}
int packTcpPacket()
{
    if ((send (pSession->localSocket,
                pPrepareTcpLoad->pTcpLoadToSend,
                pPrepareTcpLoad->sizeTcpLoadToSend,
                0
                )
        ) == -1)
    {
        perror("ERR: packTcpPacket(): send()");
    }
    else if(pPrepareTcpLoad->sizeTcpLoadToSend != 0 && dbug)
    {
        printf("ok: packTcpPacket(): sent %d bytes, %s.\n",pPrepareTcpLoad->sizeTcpLoadToSend,timeStamp());
    }
    else if(pPrepareTcpLoad->sizeTcpLoadToSend == 0 && dbug)
    {
        debug("ok: packTcpPacket(). sent none.");
    }

    return 0;
}

int unpackTcpPacket()
{
    debug("ok: unpackTcpPacket().start.");
    if(pSession->cClientOrServer == 's' && pHandleSftpPacket->sExpectedPacketType == "commandPacket")
    {
        say("ok: waiting command...");
    }
    memset(pHandleTcpLoad->aTcpLoadReceiveBuffer,'\0',tcpLoadMax);
    if((pHandleTcpLoad->sizeTcpLoadHasReceive = recv(pSession->localSocket,
                                                    pHandleTcpLoad->aTcpLoadReceiveBuffer,
                                                    tcpLoadMax,//tcpLoadReceiveMax - 1,
                                                    0
                                                    )
        ) == -1 ) // -1: ERR; 0: close; +:count
    {
        perror("ERR: tryReceive():recv()");
        exit(1);
    }
    if (pHandleTcpLoad->sizeTcpLoadHasReceive != -1 && dbug)// receive() is on-line
    {
        pSession->pTextIpRemote = inet_ntop(pSession->remoteAddressInformation.ss_family,
                                            getBinaryIp((struct sockaddr *)&pSession->remoteAddressInformation),
                                            pSession->aIpRemote,
                                            sizeof pSession->aIpRemote
                                            );
        printf("ok: received tcpload %d bytes from %s, %s.\n",pHandleTcpLoad->sizeTcpLoadHasReceive,pSession->aIpRemote,timeStamp());
        if(pHandleTcpLoad->sizeTcpLoadHasReceive <= 20)
        {
            printf("ok: received tcpload content:%s.\n",pHandleTcpLoad->aTcpLoadReceiveBuffer);
        }
    }
    if (pHandleTcpLoad->sizeTcpLoadHasReceive ==  0)// socket closed by remote
    {

        if(pSession->cClientOrServer == 'c')
        {
            say("ERR: socket closed by remote, client will exit.\n");
            exit(1);
        }
        else
        {
            printf("ok: socket closed by remote.\n");
            pSession->sessionClosed = 1;
        }
        return 0;
    }

    debug("ok: unpackTcpPacket().end.");
    return 0;
}
int unpackSftpPacket()
{
    debug("ok: unpackSftpPacket().start.");
    pSftpPacket = (sftpPacket*)pHandleTcpLoad->aTcpLoadReceiveBuffer;
    pHandleSftpPacket->sizeSftpPacket = pHandleTcpLoad->sizeTcpLoadHasReceive;

    if(!pHandleSftpPacket->sExpectedPacketType.compare("commandPacket"))
    {
        unpackSftpCommandPacket();
    }
    else if(!pHandleSftpPacket->sExpectedPacketType.compare("dataPacket"))
    {
        unpackSftpDataPacket();
    }
    else if(!pHandleSftpPacket->sExpectedPacketType.compare("messagePacket"))
    {
        unpackSftpMessagePacket();
    }
    else if(!pHandleSftpPacket->sExpectedPacketType.compare("sizeFilePacket"))
    {
        unpackSftpSizeFilePacket();
    }
    else
    {
        say("ERR: unknown packet type.");
        return 1;
    }

    debug("ok: unpackSftpPacket().end.");
    return 0;
}
int unpackSftpMessagePacket()
{
    debug("ok: unpackSftpMessagePacket().start.");
    pSftpMessagePacket = (sftpMessagePacket*)pSftpPacket;

    printf("\n%s\n\n",pSftpMessagePacket->aErrorMessage);

    debug("ok: unpackSftpMessagePacket().end.");
    return 0;
}
int unpackSftpDataPacket()
{
    debug("ok: unpackSftpDataPacket().start.");
    pSftpDataPacket = (sftpDataPacket*)pHandleTcpLoad->aTcpLoadReceiveBuffer;

    pHandleSftpDataPacket->pBlockDataReceive = pHandleTcpLoad->aTcpLoadReceiveBuffer;
    pHandleSftpDataPacket->sizeBlockDataReceive = pHandleTcpLoad->sizeTcpLoadHasReceive;

    pAboutWriteFile->pWriteBuffer = pHandleSftpDataPacket->pBlockDataReceive;
    pAboutWriteFile->sizeWriteData = pHandleSftpDataPacket->sizeBlockDataReceive;
    writeBinaryDataToFile();

    pAboutWriteFile->sizePassedFile += pAboutWriteFile->sizeWriteData;
    if(pAboutWriteFile->sizePassedFile >= pAboutWriteFile->sizeFile)
    {

        debug("ok: reachEofReceive.");
        pHandleSftpDataPacket->reachEofReceive = 1;
    }

    debug("ok: unpackSftpDataPacket().end.");
    return 0;
}
int unpackSftpCommandPacket()//only server
{
    debug("ok: unpackSftpCommandPacket().start.");
    pSftpCommandPacket = (sftpCommandPacket*)pHandleTcpLoad->aTcpLoadReceiveBuffer;
    pHandleSftpCommandPacket->sizeSftpCommandPacket = pHandleTcpLoad->sizeTcpLoadHasReceive;
    if(pHandleSftpCommandPacket->sizeSftpCommandPacket < 4)
    {
        printf("\nERR: got too short command: %s.\n\n",pHandleSftpCommandPacket->sCommand.c_str());
    }
    else
    {
        pHandleSftpCommandPacket->sCommand = pSftpCommandPacket->aCommand;
        printf("\nok: got command: %s.\n\n",pHandleSftpCommandPacket->sCommand.c_str());

        char tCmd[5];
        memmove(tCmd, pSftpCommandPacket, 4);
        tCmd[4] = '\0';
        pHandleSftpCommandPacket->sCmd = tCmd;
        pHandleSftpCommandPacket->sCmd = toUpString(pHandleSftpCommandPacket->sCmd);
        if(dbug)printf("ok: cmd: %s.\n",pHandleSftpCommandPacket->sCmd.c_str());
    }
    if(pHandleSftpCommandPacket->sizeSftpCommandPacket > 5)
    {
        pHandleSftpCommandPacket->sArgs = (char*)pSftpCommandPacket + 5;
        if(dbug)printf("ok: args: %s.\n",pHandleSftpCommandPacket->sArgs.c_str());
    }
    else
    {
        pHandleSftpCommandPacket->sArgs = "";
        pHandleSftpCommandPacket->sArgFirst = "";
        pHandleSftpCommandPacket->sArgSecond = "";
        debug("ok: no argument.");
    }

    debug("ok: unpackSftpCommandPacket().end.");
    return 0;
}
int unpackSftpSizeFilePacket()//only client
{
    debug("ok: unpackSftpSizeFilePacket().start.");
    pSftpPacket->aBlockData[pHandleSftpPacket->sizeSftpPacket] = '\0';
    pAboutWriteFile->sizeFile = atoi((char*)pSftpPacket);

    printf("ok: file size: %d bytes.\n",pAboutWriteFile->sizeFile);
    int tInt;
    if(pAboutWriteFile->sizeFile >= (tInt = pAboutWriteFile->sizeFreeDiskByte()) )
    {
        if(dbug)printf("ok: disk free size >= %d bytes.\n",tInt);
        say("ERR: disk full.");
        if(pSession->cClientOrServer == 'c')
        {
            packSftpMessagePacket("STOP");
        }
        else if(pSession->cClientOrServer == 's')//useless
        {
            packSftpMessagePacket("-Not enough room, dont send it.");
        }
        packTcpPacket();
        return 1;
    }
    else
    {
        if(dbug)printf("ok: disk free size > %d bytes.\n",tInt);
        pSession->readyForSubcommand = 1;
        say("\nok: disk space is ok, please typein \"SEND\" to start file transfer.\n");
        return 0;
    }
}

#endif // SFTP_H_INCLUDED
