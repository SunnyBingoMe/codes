#include "/media/NOW/ET2440_TCPIP/sftp/sftp.h"

string sDefaultLocalFilename = "rfc_cl.txt";
string sDefaultLocalFileAddress = "/media/NOW/ET2440_TCPIP/sftp/rfc_cl.txt";
string sDefaultCurrentPath = "/media/NOW/ET2440_TCPIP/sftp/";

int initialize(int argc, char * argv[])
{
    debug("ok: initialize().start.");

    newStruct();

    pSession->cClientOrServer = 'c';
    pSession->sCurrentPath = sDefaultCurrentPath;
    pSession->sCurrentPathForClient = "";
    pSession->readyForSubcommand = 0;

    //pSession->requestQueueMax = 10;
    pSession->pRemotePort = (char*)"5570";//sftp 69 is not usable

    pSession->sLocalFileAddress = sDefaultLocalFileAddress;
    pAboutReadFile->pOpenReadType = aDefaultOpenReadType;
    pAboutWriteFile->pOpenWriteType = aDefaultOpenWriteType;

///temp codes for test receive file

///temp codes for test receive file end

    ///deal With Arguments
	if (argc != 2)
	{
	    printf("OBS! no server addr, use localhost as default.\n\n");
        pSession->pRemoteHost = (char*)"127.0.0.1";
	}
	else
	{
        pSession->pRemoteHost = argv[1];
        if(dbug){printf("ok: pRemoteHost is %s:%s.\n",pSession->pRemoteHost,pSession->pRemotePort);}
	}

    debug("ok: initialize().end.");
    return 0;
}

int getaddrinfoLocal()
{
    int getaddrinfoStatus;

	memset(&pSession->getaddrinfoSourceLinkList, 0, sizeof pSession->getaddrinfoSourceLinkList);
	pSession->getaddrinfoSourceLinkList.ai_family = AF_UNSPEC;
	pSession->getaddrinfoSourceLinkList.ai_socktype = SOCK_STREAM;
    //pSession->getaddrinfoSourceLinkList.ai_flags = AI_PASSIVE; //use my IP //only when listening

	if ((getaddrinfoStatus = getaddrinfo(pSession->pRemoteHost,
                                        pSession->pRemotePort,
                                        &pSession->getaddrinfoSourceLinkList,
                                        &pSession->pGetaddrinfoResultLinkList
                                        )
        ) != 0 )
	{
		fprintf(stderr, "getaddrinfo: %s\n", gai_strerror(getaddrinfoStatus));
		return 1;
	}

    debug("ok: getaddrinfoLocal().");
    return 0;
}
int tryCreateAndConnect()
{
    debug("ok: tryCreateAndConnect().start.");
    struct addrinfo *piTempAddrinfo;

	for(piTempAddrinfo = pSession->pGetaddrinfoResultLinkList; piTempAddrinfo != NULL; piTempAddrinfo = piTempAddrinfo->ai_next)
	{
		if ((pSession->localSocket = socket(piTempAddrinfo->ai_family,
                                            piTempAddrinfo->ai_socktype,
                                            piTempAddrinfo->ai_protocol
                                            )
            ) == -1)
        {
			perror("ERR: tryCreateAndConnect(): socket().");
			continue;
		}
		else
            debug("ok: tryCreateAndConnect(): socket().");

		if (connect(pSession->localSocket,
                    piTempAddrinfo->ai_addr,
                    piTempAddrinfo->ai_addrlen
                    ) == -1)
		{
			close(pSession->localSocket);
			perror("ERR: tryCreateAndConnect(): connect.");
			continue;
		}
        else
            debug("ok: tryCreateAndConnect(): connect().");

		break;
	}

	if (piTempAddrinfo == NULL)
	{
		fprintf(stderr, "ERR: tryCreateAndConnect(): failed to connect, p == NULL.\n");
		return 2;
	}

	inet_ntop(piTempAddrinfo->ai_family,
              getBinaryIp((struct sockaddr *)piTempAddrinfo->ai_addr),
			  pSession->aIpRemote,
			  sizeof pSession->aIpRemote);
    if(dbug){printf("ok: tryCreateAndConnect(): connected to %s\n", pSession->aIpRemote);}

    debug("ok: tryCreateAndConnect().end.");
    return 0;
}


int main(int argc, char *argv[])
{
    string tsString;

    initialize(argc, argv);

    getaddrinfoLocal();
    tryCreateAndConnect();
	freeaddrinfo(pSession->pGetaddrinfoResultLinkList);
    debug("ok: freeaddrinfo().");

    unpackTcpPacket();
    pHandleSftpPacket->sExpectedPacketType = "messagePacket";
    unpackSftpPacket();

    while(1)//each loop for each command
    {
        printf("\nok: please command:");
        getline(cin,pPrepareSftpCommandPacket->sCommand);
        packSftpCommandPacket();

        ///switch cmd
        if     (!pPrepareSftpCommandPacket->sCmd.compare("USER"))
        {
            debug("ok: switch cmd: USER.start.");
            packTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "messagePacket";
            unpackTcpPacket();
            while(pHandleTcpLoad->aTcpLoadReceiveBuffer[0] != '!')
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                printf("ok: please type in what the server need:");///typein PATH to change current path
                getline(cin,tsString);
                packSftpMessagePacket(tsString);
                packTcpPacket();
                tsString = "";
                unpackTcpPacket();
            }
            pHandleSftpPacket->sExpectedPacketType = "messagePacket";
            unpackSftpPacket();
            say("ok: loged in.");
            debug("ok: switch cmd: USER.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("SEND") && pSession->readyForSubcommand)
        {
            debug("ok: switch cmd: SEND.start.");
            printf("ok: please type in local filename:");///typein PATH to change current path
            getline(cin,tsString);
            pSession->sLocalFileAddress = pSession->sCurrentPath + tsString;
            packTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "dataPacket";
            receiveTheRequestFile();
            tsString = "";
            pSession->readyForSubcommand = 0;
            debug("ok: switch cmd: SEND.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("STOP") && pSession->readyForSubcommand)
        {
            debug("ok: switch cmd: STOP.start.");
            packTcpPacket();

            pSession->readyForSubcommand = 0;
            debug("ok: switch cmd: SEND.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("STOR"))
        {
            debug("ok: switch cmd: STOR.start.");

            printf("ok: please type in local filename:");///typein PATH to change current path
            getline(cin,tsString);
            pSession->sLocalFileAddress = pSession->sCurrentPath + tsString;
            openReadFile();
            closeReadFile();
            if(pAboutReadFile->sizeFile == -1)
            {
                say("ERR: fail to open file.");
                continue;
            }

            packTcpPacket();

            pHandleSftpPacket->sExpectedPacketType = "messagePacket";

            unpackTcpPacket();
            if(pHandleTcpLoad->isErrorPacket())
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                continue;
            }
            else
            {
                unpackSftpPacket();
                pPrepareSftpCommandPacket->sCommand = "SIZE " + decimalToString(pAboutReadFile->sizeFile);
                packSftpCommandPacket();
                packTcpPacket();

                pHandleSftpPacket->sExpectedPacketType = "messagePacket";

                unpackTcpPacket();
                if(pHandleTcpLoad->isErrorPacket())///switch the sent "SIZE" : + => send file. - => continue to get next command
                {
                    pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                    unpackSftpPacket();
                    continue;
                }
                else
                {
                    unpackSftpPacket();

                    sendTheRequestFile();

                    pHandleSftpPacket->sExpectedPacketType = "messagePacket";

                    unpackTcpPacket();
                    if(pHandleTcpLoad->isErrorPacket())
                    {
                        pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                        unpackSftpPacket();
                        continue;
                    }
                    else
                    {
                        unpackSftpPacket();
                    }
                }
            }
            debug("ok: switch cmd: STOR.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("RETR"))
        {
            debug("ok: switch cmd: RETR.start.");
            packTcpPacket();

            unpackTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "sizeFilePacket";

            if(pHandleTcpLoad->isErrorPacket())
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                continue;
            }
            else
            {
                unpackSftpPacket();
            }
            debug("ok: switch cmd: RETR.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("LIST"))
        {
            debug("ok: switch cmd: LIST.start.");
            packTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "messagePacket";
            unpackTcpPacket();
            if(pHandleTcpLoad->isErrorPacket())
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                continue;
            }
            else
            {
                unpackSftpPacket();
            }
            debug("ok: switch cmd: LIST.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("KILL"))
        {
            debug("ok: switch cmd: KILL.start.");
            packTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "messagePacket";
            unpackTcpPacket();
            if(pHandleTcpLoad->isErrorPacket())
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                continue;
            }
            else
            {
                unpackSftpPacket();
            }
            debug("ok: switch cmd: KILL.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("NAME"))
        {
            debug("ok: switch cmd: NAME.start.");
            printf("ok: please type in new filename:");
            getline(cin,tsString);

            packTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "messagePacket";
            unpackTcpPacket();
            if(pHandleTcpLoad->isErrorPacket())
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                continue;
            }
            else
            {
                unpackSftpPacket();
                packSftpMessagePacket("TOBE "+tsString);
                packTcpPacket();
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackTcpPacket();
                if(pHandleTcpLoad->isErrorPacket())
                {
                    pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                    unpackSftpPacket();
                    continue;
                }
                else
                {
                    unpackSftpPacket();
                }
            }
            debug("ok: switch cmd: NAME.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("CDIR"))
        {
            debug("ok: switch cmd: CDIR.start.");
            packTcpPacket();
            pHandleSftpPacket->sExpectedPacketType = "messagePacket";
            unpackTcpPacket();
            while(pHandleTcpLoad->aTcpLoadReceiveBuffer[0] == '+')
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                printf("ok: please type in what the server need:");///typein PATH to change current path
                getline(cin,tsString);
                packSftpMessagePacket(tsString);
                packTcpPacket();
                tsString = "";
                unpackTcpPacket();
            }
            if(pHandleTcpLoad->aTcpLoadReceiveBuffer[0] == '!')
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                if(pPrepareSftpCommandPacket->sArgs[0] == '/')
                {
                    pPrepareSftpCommandPacket->sArgs.erase(0,1);
                }
                if(pPrepareSftpCommandPacket->sArgs[pPrepareSftpCommandPacket->sArgs.size()-1] != '/')
                {
                    pPrepareSftpCommandPacket->sArgs += "/";
                }
                pSession->sCurrentPathForClient = pPrepareSftpCommandPacket->sArgs;
                cout<<"ok: sCurrentPathForClient:/"<<pSession->sCurrentPathForClient<<endl;
            }
            else//(pHandleTcpLoad->isErrorPacket()) // '-'
            {
                pHandleSftpPacket->sExpectedPacketType = "messagePacket";
                unpackSftpPacket();
                continue;
            }
            debug("ok: switch cmd: CDIR.end.");
        }
        else if(!pPrepareSftpCommandPacket->sCmd.compare("DONE"))
        {
            debug("ok: switch cmd: DONE.start.");
            packTcpPacket();
            debug("ok: switch cmd: DONE.end.");
            break;
        }
        else
        {
            say("ERR: unexpected command.");
            continue;
        }

        printf("\nok: finished command: \"%s\".%s.\n\n",pSftpCommandPacket->aCommand,timeStamp());
        pSession->commandTimer = clock() - pSession->commandTimer;
        printf("ok: handeling this command costs: %i s.\n",pSession->commandTimer/CLOCKS_PER_SEC);
    }
    printf("\nok: finished command: \"DONE\".\n\n");
    pSession->commandTimer = clock() - pSession->commandTimer;
    printf("ok: handeling this command costs: %i s.\n",pSession->commandTimer/CLOCKS_PER_SEC);
    close(pSession->localSocket);
    debug("ok: close(localSocket).");

    return 0;
}
