#include "/media/NOW/ET2440_TCPIP/sftp/sftp.h"

string sDefaultLocalFilename = "rfc_sr.txt";
string sDefaultLocalFileAddress = "/media/NOW/ET2440_TCPIP/sftp/rfc_sr.txt";
string sDefaultCurrentPath = "/media/NOW/ET2440_TCPIP/sftp/";

///temp test code
//int tSizeDefaultFile = 2519; //only client
///temp test code end


void handleDeadChildProcess()
{
	while(waitpid(-1, NULL, WNOHANG) > 0);
    printf("\nok: one child-connection finished.\n");
    printf("\nok: waiting for connections...\n");
}
int prepareToHandleDeadChildProcess()//The code that's there is responsible for reaping zombie processes that appear as the fork()ed child processes exit. If you make lots of zombies and don't reap them, your system administrator will become agitated.
{
    struct sigaction tSigaction;

	tSigaction.sa_handler = (void(*)(int))handleDeadChildProcess; //reap all dead processes
	sigemptyset(&tSigaction.sa_mask);
	tSigaction.sa_flags = SA_RESTART;
	if (sigaction(SIGCHLD,
                  &tSigaction,/*struct sigaction sa*/
                  NULL
                 ) == -1)
    {
		perror("ERR: sigaction");
		exit(1);
	}

    debug("ok: prepareToHandleDeadChildProcess().");
    return 0;
}

int initialize()
{
    debug("ok: initialize().start.");

    newStruct();

    pSession->cClientOrServer = 's';
    pSession->sCurrentPath = sDefaultCurrentPath;
    pSession->sCurrentPathForClient = "";
    pSession->readyForSubcommand = 0;

    pSession->requestQueueMax = 10;
    pSession->pLocalPort = (char*)"5570";//sftp 69 is not usable

    pSession->sLocalFileAddress = sDefaultLocalFileAddress;
    pAboutReadFile->pOpenReadType = aDefaultOpenReadType;
    pAboutWriteFile->pOpenWriteType = aDefaultOpenWriteType;

    debug("ok: initialize().end.");
    return 0;
}

int getaddrinfoLocal()
{
    debug("ok: getaddrinfoLocal().start.");
    int getaddrinfoStatus;

    memset(&pSession->getaddrinfoSourceLinkList, 0, sizeof pSession->getaddrinfoSourceLinkList);
    pSession->getaddrinfoSourceLinkList.ai_family = AF_UNSPEC; // set to AF_INET to force IPv4
    pSession->getaddrinfoSourceLinkList.ai_socktype = SOCK_STREAM;
    pSession->getaddrinfoSourceLinkList.ai_flags = AI_PASSIVE; // use my IP
    pSession->reusable = 1;

	if ((getaddrinfoStatus = getaddrinfo(NULL,
                                        pSession->pLocalPort,
                                        &pSession->getaddrinfoSourceLinkList,
                                        &pSession->pGetaddrinfoResultLinkList
                                        )
        ) != 0)
	{
		fprintf(stderr, "ERR: getaddrinfo: %s\n", gai_strerror(getaddrinfoStatus));
		return 1;
	}

    debug("ok: getaddrinfo().end.");
    return 0;
}
int tryCreateSocketReusableAndBind()
{
    debug("ok: tryCreateSocketReusableAndBind().start.");

    struct addrinfo *piTempAddrinfo;
    piTempAddrinfo = pSession->pGetaddrinfoResultLinkList;

    if (piTempAddrinfo == NULL)
	{
		fprintf(stderr, "ERR:  pGetaddrinfoResultLinkList is NULL, cannot try to creat and bind.\n");
		return 2;
	}

    for( ; piTempAddrinfo != NULL; piTempAddrinfo = piTempAddrinfo->ai_next)
	{
	    ///creat
		if ((pSession->localListener = socket(piTempAddrinfo->ai_family,
                                            piTempAddrinfo->ai_socktype,
                                            piTempAddrinfo->ai_protocol
                                            )
            ) == -1)
        {
			perror("ERR: server: socket");
			continue;
		}
		debug("ok: socket().");

        ///set to reusable
		if (setsockopt(pSession->localListener,  //socket descriptor
                       SOL_SOCKET,     //Socket Option Level: set will happen at the  level of SOCKET
                       SO_REUSEADDR,   //The socket option for which the value is to be set
                       &pSession->reusable,//A pointer to the buffer in which the value for the requested option is specified. //int reusable = 1; /*#define SOL_SOCKET 1; #define SO_REUSEADDR	2*/
                       sizeof(int)
                       )== -1)
        {//reusable, in case of unexpected exit
			perror("ERR: setsockopt, couldn't set to reusable.");
			exit(1);
		}
        debug("ok: setsockopt, reusable. ");

        ///bind
		if (bind(pSession->localListener,
                piTempAddrinfo->ai_addr,
                piTempAddrinfo->ai_addrlen
                ) == -1)
		{
			close(pSession->localListener);
			perror("ERR: server: bind");
			continue;
		}
        debug("ok: bind.");

        ///all done
		break;
	}

    debug("ok: tryCreateSocketReusableAndBind().end.");
    return 0;
}
int tryListen()
{
    debug("ok: tryListen().start.");

	if (listen(pSession->localListener,
                pSession->requestQueueMax
                ) == -1)
	{
		perror("ERR: listen");
		exit(1);
	}

    debug("ok: tryListen().end.");
    return 0;
}
int tryAccept()
{
    pSession->sizeRemoteAddressInformation = sizeof pSession->remoteAddressInformation;
    pSession->localSocket = accept(pSession->localListener,
                                    (struct sockaddr *)&pSession->remoteAddressInformation,
                                    &pSession->sizeRemoteAddressInformation
                                    );

    if (pSession->localSocket == -1)
    {
        perror("ERR: accept");
        return 1;
    }
    else
    {
        debug("ok: accept().");
    }

    //inet_ntop: convert IPv4 or IPv6 addresses from binary to text string form
    inet_ntop(pSession->remoteAddressInformation.ss_family,
              getBinaryIp( (struct sockaddr *) &pSession->remoteAddressInformation ),
              pSession->aIpRemote,
              sizeof pSession->aIpRemote
             );
    printf("ok: got connection from: %s, %s.\n\n", pSession->aIpRemote,timeStamp());//char ipRemote[INET6_ADDRSTRLEN];

    return 0;
}

int dealWithAConnection()
{
    debug("ok: childProcess().start.");
    close(pSession->localListener); // child doesn't need the listener
    debug("ok: childProcess(). close(localListener).");

    string tString;
    int tTryTime = 0;
    pSession->sessionClosed = 0;
    pSession->userLogedIn = 0;
    pSession->sCurrentPathForClient = "";
    pSession->tryTimeMax = 3;

    packSftpMessagePacket("+Sunny SFTP Service. Please send user-ID. Test ID: lab123.");
    packTcpPacket();

    while(1)//each loop for each command
    {
        pHandleSftpPacket->sExpectedPacketType = "commandPacket";

        while(pSession->userLogedIn == 0)
        {
            debug("ok: checking user-ID.");
            tTryTime ++;

            unpackTcpPacket();// may set pSession->sessionClosed = 1
            if(pSession->sessionClosed)
            {
                return 1;
            }
            unpackSftpPacket();

            if(!pHandleSftpCommandPacket->sCmd.compare("USER"))
            {
                if(!pHandleSftpCommandPacket->sArgs.compare("lab123"))
                {
                    pSession->userLogedIn = 1;
                    cout<<endl<<"ok: " + pHandleSftpCommandPacket->sArgs +" logged in."<<endl<<endl;
                    packSftpMessagePacket("!" + pHandleSftpCommandPacket->sArgs +" logged in.");
                    packTcpPacket();
                    break;
                }
                else
                {
                    cout<<"ok: Invalid user-id, need try again."<<endl;
                    if(tTryTime >= pSession->tryTimeMax)
                    {
                        cout<<"ERR: Illegal access. A user tried " + decimalToString(tTryTime) + " times. Session will close."<<endl;
                        pSession->sessionClosed = 1;
                        close(pSession->localSocket);
                        debug("ok: child: close(localSocket).");
                        exit(1);
                    }
                    packSftpMessagePacket("-Invalid user-id, you have " + decimalToString(pSession->tryTimeMax-tTryTime) + " time(s) left to try.");
                    packTcpPacket();
                }
            }
            else
            {
                cout<<"ok: Illegal command, need \"USER ID\"."<<endl;
                if(tTryTime >= pSession->tryTimeMax)
                {
                    cout<<"ERR: Illegal access. A user tried " + decimalToString(tTryTime) + " times. Session will close."<<endl;
                    pSession->sessionClosed = 1;
                    close(pSession->localSocket);
                    debug("ok: child: close(localSocket).");
                    exit(1);
                }
                packSftpMessagePacket("-Illegal command, please send \"USER ID\", you have " + decimalToString(pSession->tryTimeMax-tTryTime) + " time(s) left to try.");
                packTcpPacket();
            }
        }

        unpackTcpPacket();
        pHandleSftpPacket->sExpectedPacketType = "commandPacket";
        unpackSftpPacket();

        pSession->commandTimer = clock();

        if(!pHandleSftpCommandPacket->sCmd.compare("SEND") && pSession->readyForSubcommand)
        {
            debug("ok: switch cmd: SEND.start.");
            sendTheRequestFile();
            pSession->readyForSubcommand = 0;
            debug("ok: switch cmd: SEND.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("STOP") && pSession->readyForSubcommand)
        {
            debug("ok: switch cmd: STOP.start.");
            pSession->readyForSubcommand = 0;
            debug("ok: switch cmd: STOP.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("STOR"))
        {
            debug("ok: switch cmd: STOR.start.");
            if(pHandleSftpCommandPacket->sArgs.size() < 5)
            {
                printf("ERR: unexpected command:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: server received unexpected command");
                packTcpPacket();
                continue;
            }

            pHandleSftpCommandPacket->sArgFirst = pHandleSftpCommandPacket->sArgs.substr(0,3);
            pHandleSftpCommandPacket->sArgFirst = toUpString(pHandleSftpCommandPacket->sArgFirst);
            pHandleSftpCommandPacket->sArgSecond = pHandleSftpCommandPacket->sArgs.substr(4);

            if(!pHandleSftpCommandPacket->sArgFirst.compare("NEW"))
            {
                pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                openReadFile();
                closeReadFile();
                if(pAboutReadFile->sizeFile != -1)//file exists
                {
                    pHandleSftpCommandPacket->sArgSecond = (timeStampString() + "_" + pHandleSftpCommandPacket->sArgSecond);
                    if(dbug)printf("ok: new filename:%s.\n",pHandleSftpCommandPacket->sArgSecond.c_str());
                    pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;

                    packSftpMessagePacket("+File exists, will create new generation of file.");
                    packTcpPacket();

                    pSession->readyForSubcommand = 1;
                }
                else//file not exists
                {
                    pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                    packSftpMessagePacket("+File does not exist, will create new file.");
                    packTcpPacket();

                    pSession->readyForSubcommand = 1;
                }
            }
            else if(!pHandleSftpCommandPacket->sArgFirst.compare("OLD"))
            {
                pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                openReadFile();
                closeReadFile();
                if(pAboutReadFile->sizeFile != -1)//file exists
                {
                    pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                    packSftpMessagePacket("+Will write over old file.");
                    packTcpPacket();

                    pSession->readyForSubcommand = 1;
                }
                else//file not exists
                {
                    pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                    packSftpMessagePacket("+Will create new file.");
                    packTcpPacket();

                    pSession->readyForSubcommand = 1;
                }
            }
            else if(!pHandleSftpCommandPacket->sArgFirst.compare("APP"))
            {
                pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                openReadFile();
                closeReadFile();
                if(pAboutReadFile->sizeFile != -1)//file exists
                {
                    pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                    packSftpMessagePacket("+Will append to file.");
                    packTcpPacket();

                    char aChar[] = "ab";
                    pAboutWriteFile->pOpenWriteType = aChar;//after transfer, set back to default //pAboutWriteFile->pOpenWriteType = aDefaultOpenWriteType
                    pSession->readyForSubcommand = 1;
                }
                else//file not exists
                {
                    pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
                    packSftpMessagePacket("+Will create file.");
                    packTcpPacket();

                    pSession->readyForSubcommand = 1;
                }
            }
            else
            {
                printf("ERR: unexpected command:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: server received unexpected command.");
                packTcpPacket();
                continue;
            }
            debug("ok: switch cmd: STOR.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("SIZE") && pSession->readyForSubcommand)
        {
            debug("ok: switch cmd: SIZE.start.");
            pAboutWriteFile->sizeFile = atoi(pHandleSftpCommandPacket->sArgs.c_str());
            printf("ok: file size: %d bytes.\n",pAboutWriteFile->sizeFile);
            int tInt;
            if(pAboutWriteFile->sizeFile >= (tInt = pAboutWriteFile->sizeFreeDiskByte()) )
            {
                if(dbug)printf("ok: disk free size >= %d bytes.\n",tInt);
                say("ERR: disk full.");
                packSftpMessagePacket("-Not enough room, dont send it.");
                packTcpPacket();
                continue;
            }
            else//disk space is ok
            {
                if(dbug)printf("ok: disk free size > %d bytes.\n",tInt);
                packSftpMessagePacket("+ok, waiting for file.");
                packTcpPacket();
                say("ok: ready to receive file.");

                pHandleSftpPacket->sExpectedPacketType = "dataPacket";

                if(receiveTheRequestFile())
                {
                    packSftpMessagePacket("-Couldn't save.");
                    packTcpPacket();
                    say("ERR: couldn't save.");
                    continue;
                }
                else
                {
                    packSftpMessagePacket("+Saved:"+pSession->sLocalFileAddress.substr(pSession->sCurrentPath.size()));
                    packTcpPacket();
                    cout<<"ok: saved:" + pSession->sLocalFileAddress<<endl;
                }
            }//if //else//disk space is ok
            pSession->readyForSubcommand = 0;
            pAboutWriteFile->pOpenWriteType = aDefaultOpenWriteType;//"wb"
            debug("ok: switch cmd: SIZE.end.");
        }//else if "SIZE"
        else if(!pHandleSftpCommandPacket->sCmd.compare("RETR"))
        {
            debug("ok: switch cmd: RETR.start.");
            pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgs;
            if(dbug)cout<<"ok: sFileAddress:"<<pSession->sLocalFileAddress<<endl;

            if(openReadFile())
            {
                printf("ERR: file not exists:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: file not exists.");
                packTcpPacket();
                continue;
            }
            closeReadFile();
            tString = decimalToString(pAboutReadFile->sizeFile);

            packSftpMessagePacket(tString);
            packTcpPacket();
            pSession->readyForSubcommand = 1;
            debug("ok: switch cmd: RETR.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("NAME"))
        {
            debug("ok: switch cmd: NAME.start.");
            pSession->sLocalFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgs;

            if(openReadFile())
            {
                printf("ERR: file not exists:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: file not exists.");
                packTcpPacket();
                continue;
            }
            closeReadFile();
            packSftpMessagePacket("+File exists.");
            packTcpPacket();

            string tsOldName = pHandleSftpCommandPacket->sArgs;
            unpackTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "commandPacket";
            unpackSftpPacket();
            if(pHandleSftpCommandPacket->sCmd.compare("TOBE"))//if not "TOBE"
            {
                printf("ERR: unexpected command following \"NAME\":%s.",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: server received unexpected command following \"NAME\".");
                packTcpPacket();
                continue;
            }

            if(!openReadFile(pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgs))
            {
                printf("ERR: new filename exists:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: new filename exists.");
                packTcpPacket();

                closeReadFile();
                continue;
            }

            if(rename(  (pSession->sCurrentPath + pSession->sCurrentPathForClient + tsOldName).c_str(),
                        (pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgs).c_str()
                     ))
            {
                printf("ERR: rename error, file may under using:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: rename error, file may under using.");
                packTcpPacket();
                continue;
            }
            packSftpMessagePacket("+" + tsOldName +" renamed to " + pHandleSftpCommandPacket->sArgs);
            packTcpPacket();
            debug("ok: switch cmd: NAME.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("KILL"))
        {
            debug("ok: switch cmd: KILL.start.");
            if (remove((pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgs).c_str()))
            {
                printf("ERR: unexpected filename:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: server received unexpected filename.");
                packTcpPacket();
                continue;
            }
            cout<<"ok: file:" + pHandleSftpCommandPacket->sArgs + " deleted."<<endl;
            packSftpMessagePacket("+" + pHandleSftpCommandPacket->sArgs + " deleted.");
            packTcpPacket();
            debug("ok: switch cmd: KILL.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("LIST"))
        {
            debug("ok: switch cmd: LIST.start.");
            if(pHandleSftpCommandPacket->sArgs.size() < 1)
            {
                printf("ERR: unexpected command:%s.\n",pHandleSftpCommandPacket->sCommand.c_str());
                packSftpMessagePacket("-ERR: server received unexpected command");
                packTcpPacket();
                continue;
            }
            pHandleSftpCommandPacket->sArgFirst = pHandleSftpCommandPacket->sArgs.substr(0,1);
            pHandleSftpCommandPacket->sArgFirst = toUpString(pHandleSftpCommandPacket->sArgFirst);
            if(pHandleSftpCommandPacket->sArgs.size() > 2)
            {
                if(pHandleSftpCommandPacket->sArgs[0] == '/')
                {
                    pHandleSftpCommandPacket->sArgs.erase(0,1);
                }
                pHandleSftpCommandPacket->sArgSecond = pHandleSftpCommandPacket->sArgs.substr(2);
            }
            else
            {
                pHandleSftpCommandPacket->sArgSecond = "";
            }

            struct dirent * tpDirent = NULL;
            DIR * tpDirectory = NULL;
            string tsDirectoryToList = pSession->sCurrentPath + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond;
            string tsList = "";
            struct stat tStat;
            string tsFileAddress;
            string tsLine = "";

            tpDirectory = opendir(tsDirectoryToList.c_str());
            if( tpDirectory == NULL)
            {
                printf("ERR: unexpected directory to list:%s.\n",tsDirectoryToList.c_str());
                packSftpMessagePacket("-ERR: unexpected directory to list.");
                packTcpPacket();
                continue;
            }
            if(dbug)cout<<"ok: directory to list:"<<tsDirectoryToList<<endl;
            if(!pHandleSftpCommandPacket->sArgFirst.compare("F"))
            {
                tsList = "+" + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond + "\n";
                while((tpDirent = readdir(tpDirectory)))
                {
                    if(tsList.size() + 15 >= tcpLoadMax || tsList.size() + strlen(tpDirent->d_name) >= tcpLoadMax - 5 )
                    {
                        tsList.append("...etc...");
                        tsList.append("\n");
                        break;
                    }
                    tsList.append(tpDirent->d_name);
                    tsList.append("\n");
                }
                closedir(tpDirectory);
                if(dbug)cout<<"ok: list:"<<endl<<tsList<<endl;
                packSftpMessagePacket(tsList);
                packTcpPacket();

                tsList = "";
                tsDirectoryToList = pSession->sCurrentPath + pSession->sCurrentPathForClient;
                pHandleSftpCommandPacket->sArgFirst = "";
                pHandleSftpCommandPacket->sArgSecond = "";
            }
            else if(!pHandleSftpCommandPacket->sArgFirst.compare("V"))
            {
                tsList = "+" + pSession->sCurrentPathForClient + pHandleSftpCommandPacket->sArgSecond + "\n";
                while((tpDirent = readdir(tpDirectory)))
                {
                    tsLine = "";
                    tsLine += tpDirent->d_name;

                    tsFileAddress = pSession->sCurrentPath + pSession->sCurrentPathForClient + tpDirent->d_name;
                    stat(tsFileAddress.c_str(), &tStat);
                    if(S_ISDIR(tStat.st_mode))//if folder
                    {
                        tsLine += "\t<LIST>...\n";
                    }
                    else
                    {
                        tsLine += "\t";
                        tsLine += decimalToString(tStat.st_size);
                        tsLine += "\t";
                        tsLine += time_tToString(tStat.st_mtime);
                        tsLine += "\n";
                    }
                    if(tsList.size() + 15 >= tcpLoadMax || tsList.size() + tsLine.size() >= tcpLoadMax - 5)
                    {
                        tsList.append("...etc...");
                        tsList.append("\n");
                        break;
                    }
                    else
                    {
                        tsList += tsLine;
                    }
                }
                closedir(tpDirectory);
                if(dbug)cout<<"ok: list:"<<endl<<tsList<<endl;
                packSftpMessagePacket(tsList);
                packTcpPacket();

                tsList = "";
                tsDirectoryToList = pSession->sCurrentPath + pSession->sCurrentPathForClient;
                pHandleSftpCommandPacket->sArgFirst = "";
                pHandleSftpCommandPacket->sArgSecond = "";
            }
            else
            {
                printf("ERR: unexpected LIST argument:%s.\n",pHandleSftpCommandPacket->sArgs.c_str());
                packSftpMessagePacket("-ERR: server received unexpected LIST argument.");
                packTcpPacket();

                tsList = "";
                tsDirectoryToList = pSession->sCurrentPath + pSession->sCurrentPathForClient;
                pHandleSftpCommandPacket->sArgFirst = "";
                pHandleSftpCommandPacket->sArgSecond = "";
                continue;
            }
            debug("ok: switch cmd: LIST.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("CDIR"))
        {
            debug("ok: switch cmd: CDIR.start.");
            if(pHandleSftpCommandPacket->sArgs[0] == '/')
            {
                pHandleSftpCommandPacket->sArgs.erase(0,1);
            }
            if(pHandleSftpCommandPacket->sArgs.size() > 0)
            {
                if(pHandleSftpCommandPacket->sArgs[pHandleSftpCommandPacket->sArgs.size()-1] != '/')
                {
                    pHandleSftpCommandPacket->sArgs += "/";
                }
            }

            DIR * tpDirectoryCdir = NULL;
            string tsDirectoryToCdir = pSession->sCurrentPath + pHandleSftpCommandPacket->sArgs;
            tpDirectoryCdir = opendir(tsDirectoryToCdir.c_str());
            if( tpDirectoryCdir == NULL)
            {
                printf("ERR: unexpected real directory to change to:%s.\n",tsDirectoryToCdir.c_str());
                packSftpMessagePacket("-ERR: unexpected directory to chang to.");
                packTcpPacket();
                continue;
            }
            if(dbug)cout<<"ok: real directory to change to:"<<tsDirectoryToCdir<<endl;
            packSftpMessagePacket("!Changed working dir to /"+pHandleSftpCommandPacket->sArgs);
            packTcpPacket();
            pSession->sCurrentPathForClient = pHandleSftpCommandPacket->sArgs;
            cout<<"ok: sCurrentPathForClient:/"<<pSession->sCurrentPathForClient<<endl;
            debug("ok: switch cmd: CDIR.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("TYPE"))
        {
            debug("ok: switch cmd: TYPE.start.");
            packSftpMessagePacket("-This server is binary mode only.");
            packTcpPacket();
            debug("ok: switch cmd: TYPE.end.");
        }
        else if(!pHandleSftpCommandPacket->sCmd.compare("DONE"))
        {
            debug("ok: switch cmd: DONE.start.");
            packSftpMessagePacket("+.");///need change
            packTcpPacket();
            debug("ok: switch cmd: DONE.end.");
            break;
        }
        else
        {
            printf("ERR: unexpected command:%s.",pHandleSftpCommandPacket->sArgs.c_str());
            packSftpMessagePacket("-ERR: server received unexpected command");
            packTcpPacket();
            continue;
        }

        printf("\nok: finished command: \"%s\".%s.\n\n",pHandleSftpCommandPacket->sCommand.c_str(),timeStamp());
        pSession->commandTimer = clock() - pSession->commandTimer;
        printf("ok: handeling this command costs: %i s.\n",pSession->commandTimer/CLOCKS_PER_SEC);
    }//while(1)//each loop for each command

    printf("\nok: finished command: \"DONE\".\n");
    pSession->commandTimer = clock() - pSession->commandTimer;
    printf("ok: handeling this command costs: %i s.\n",pSession->commandTimer/CLOCKS_PER_SEC);
    pSession->sessionClosed = 0;

    close(pSession->localSocket);
    debug("ok: child: close(localSocket).");

    exit(0);
}

int main(void)
{
    initialize();

    getaddrinfoLocal();
    tryCreateSocketReusableAndBind();
	freeaddrinfo(pSession->pGetaddrinfoResultLinkList); // all done with this structure
    debug("ok: freeaddrinfo().");

    tryListen();

    prepareToHandleDeadChildProcess();

	say("\nok: waiting for connections...\n");

	while(1) //each loop for each connection
	{
        if ( tryAccept() == 1 ) //if ERR: accept
        {
            continue;
        }

		if (fork()==0) //success, fork() return 0 to child and return child's PID to parent.
		{
            dealWithAConnection();
		}
        else //if in parent instead of child process
        {
            close(pSession->localSocket);
            debug("ok: parent: close(localSocket), parent doesn't need.");
        }
	}

	return 0;
}//int main(void)

