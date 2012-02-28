#ifndef SFTPSTRUCT_H_INCLUDED
#define SFTPSTRUCT_H_INCLUDED

#define tcpLoadMax 1000
#define maxReadOnce tcpLoadMax
#define blockDataMax tcpLoadMax
struct session
{
    char cClientOrServer;

    char * pRemoteHost;//name or ip //server don't need
    char * pRemotePort;
    char * pLocalPort;
    int requestQueueMax;//clinet don't need
    int reusable;//clinet don't need ?
    struct addrinfo getaddrinfoSourceLinkList;
    struct addrinfo * pGetaddrinfoResultLinkList;
    struct addrinfo * pFirstValidGetaddrinfoSourceLinkListSend;//server don't need
    //struct addrinfo * pFirstValidGetaddrinfoSourceLinkListReceive;

    int localListener;//client don't need
    int localSocket;// listen on localListener, new connection on localSocket

    //string sCurrentPathRemote;
    string sCurrentPath;
    string sCurrentPathForClient;
    //char * pRemoteFileAddress;//server don't need
    string sLocalFileAddress;
    //char cReadOrWrite;
    //char * pMode;

    int userLogedIn;//only server
    int tryTimeMax;//only server
    int sessionClosed;
    int readyForSubcommand;
    struct sockaddr_storage remoteAddressInformation;
    socklen_t sizeRemoteAddressInformation;//unsigned int
    char aIpRemote[INET6_ADDRSTRLEN];
    const char * pTextIpRemote;

    clock_t commandTimer;
    clock_t fileTimer;
};
struct session * pSession;

///read and write file
struct aboutReadFile
{
    //string sFileAddress;
    char * pOpenReadType;
    char aReadBuffer[maxReadOnce];
    int sizeReadUnit;
    int sizeReadData;
    FILE * pReadFile;
    int sizeFile;
    int sizePassedFile;
};
struct aboutReadFile * pAboutReadFile;
struct aboutWriteFile
{
    //string sFileAddress;
    char * pOpenWriteType;
    char * pWriteBuffer;
    int sizeWriteUnit;
    int sizeWriteData;
    FILE * pWriteFile;
    int sizeFile;
    int sizePassedFile;
    int sizeFreeDiskByte()
    {
        struct statvfs tFileSystemInformation;
        if((statvfs(pSession->sCurrentPath.c_str(), &tFileSystemInformation)) < 0 )
        {
            printf("ERR: failed to stat %s:\n", pSession->sCurrentPath.c_str());
            return -1;
        }
        else
        {
            if(dbug)
            {
                printf("ok: disk %s: \n", pSession->sCurrentPath.c_str());
                printf("\tblock size: %u\n", (unsigned int)tFileSystemInformation.f_bsize);
                printf("\ttotal no blocks: %u\n", (unsigned int)tFileSystemInformation.f_blocks);
                printf("\tfree blocks: %u\n", (unsigned int)tFileSystemInformation.f_bfree);
            }
            if(intMax/(int)tFileSystemInformation.f_bsize <= (int)tFileSystemInformation.f_bfree)
            {
                return intMax;
            }
            else
            {
                return (unsigned int)tFileSystemInformation.f_bsize * (unsigned int)tFileSystemInformation.f_bfree;
            }
        }
    }

};
struct aboutWriteFile * pAboutWriteFile;
///read and write file end

///pack and unpack
    ///send
struct prepareSftpMessagePacket
{
    struct sftpMessagePacket * pOriginalAddress;
    string sLoad;
};
struct prepareSftpMessagePacket * pPrepareSftpMessagePacket;

struct prepareSftpCommandPacket
{
    struct sftpCommandPacket * pOriginalAddress;
    string sCommand;
    string sCmd;
    string sArgs;
};
struct prepareSftpCommandPacket * pPrepareSftpCommandPacket;

struct prepareSftpDataPacket
{
    struct sftpDataPacket * pOriginalAddress;
    int sizeBlockDataToSend;
    int reachEofSend;
    int sizeTotalData;
    int sizePassedData;
};
struct prepareSftpDataPacket * pPrepareSftpDataPacket;

struct prepareTcpLoad
{
    char * pTcpLoadToSend;
    int sizeTcpLoadToSend;
    int sizeTcpLoadHasSend;//for debug
};
struct prepareTcpLoad * pPrepareTcpLoad;
    ///send end

struct sftpPacket
{
	char aBlockData[tcpLoadMax];
};
struct sftpPacket * pSftpPacket;
struct sftpMessagePacket
{
	char aErrorMessage[blockDataMax];
};
struct sftpMessagePacket * pSftpMessagePacket;
struct sftpDataPacket
{
	char aBlockData[blockDataMax];
};
struct sftpDataPacket * pSftpDataPacket;
struct sftpCommandPacket
{
    char aCommand[tcpLoadMax];
};
struct sftpCommandPacket * pSftpCommandPacket;

    ///receive
struct handleTcpLoad
{
    char aTcpLoadReceiveBuffer[tcpLoadMax];//void * pTcpLoadHasReceive;
    int sizeTcpLoadHasReceive;
    int isErrorPacket()
    {
        if(aTcpLoadReceiveBuffer[0] == '-')
        {
            debug("ok: isErrorPacket(): is error packet.");
            return 1;
        }

        debug("ok: isErrorPacket(): isn't error packet.");
        return 0;
    }

};
struct handleTcpLoad * pHandleTcpLoad;
struct handleSftpPacket
{
    int sizeSftpPacket;
    string sExpectedPacketType;
};
struct handleSftpPacket * pHandleSftpPacket;
struct handleSftpCommandPacket
{
    int sizeSftpCommandPacket;
    string sCommand;
    string sCmd;
    string sArgs;
    string sArgFirst;
    string sArgSecond;
};
struct handleSftpCommandPacket * pHandleSftpCommandPacket;
struct handleSftpDataPacket
{
    char * pBlockDataReceive;
    int sizeBlockDataReceive;
    int reachEofReceive;
};
struct handleSftpDataPacket * pHandleSftpDataPacket;
    ///receive end

///pack and unpack end


#endif // SFTPSTRUCT_H_INCLUDED
