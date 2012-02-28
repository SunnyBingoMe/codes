
#include <arpa/inet.h>
#include <errno.h>
#include <fcntl.h>
#include <limits.h>
#include <memory.h>
#include <netdb.h>
#include <netinet/in.h>
#include <pthread.h>
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <sys/ioctl.h>
#include <sys/socket.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <sys/types.h>
#include <time.h>
#include <unistd.h>

#define UCH         unsigned char//8
#define US          unsigned short//16
#define UL          unsigned long//32
#define SOCKET      int//32
#define PORT        69
#define MIN_PORT    20070
#define MAX_PORT    20100
#define TIME_OUT    5
#define BL_SIZE     512

#define DEBUG       1


struct packet//data pkt
{
	US      opcode;
	US      block;
	char    buffer;
};

struct err
{
	US   opcode;
	US   errorcode;
	char errormessage[508];
};

struct ack
{
	US opcode;
	union
	{
		US block;
		char buffer[510];
	};
};

struct request
{
	struct timeval tv;
	fd_set      readfds;
	pthread_t   threadId;
	SOCKET      m_socket;
	UCH         attempt;
	char        path[256];
	FILE       *file;
	char       *filename;
	char       *mode;
	char       *alias;
	UL          blksize;
	UL          timeout;
	US          block;
	US          fblock;
	int         bytesReceived;
	int         bytesRead;
	int         bytesSent;
	struct sockaddr_in  client;
	socklen_t           clientsize;
	struct packet      *pkt;
	union
	{
		struct ack acout;
		struct err serverError;
	};
	union
	{
		struct ack datain;
		struct err clientError;
	};
	SOCKET knock;
};

struct data
{
	SOCKET  sock;
	struct  sockaddr_in addr;
	UL      server;
	US      port;
	char    homedir[256];
	char    overwrite;
	UCH     logLevel;
	int     minport;
	int     maxport;
};

UL      timeout = TIME_OUT;
char    tempbuf[256];
char    logbuf[512];
struct  data cfig;

int     ini();
void   *hand_req(struct request *);
void    cle_fre_req(struct request *);
void    errmsg(struct request *, UCH);
void    ck_e2c(int);
struct  ack tmpack;

int ini(char * argv_dir)
{
	socklen_t nRet;

	memset(&cfig, 0, sizeof(cfig));

	sprintf(cfig.homedir, "%s",argv_dir);
	cfig.overwrite = 0;
	cfig.logLevel = 2;
	cfig.minport = MIN_PORT;
	cfig.maxport = MAX_PORT;

/**/cfig.server = inet_addr("127.0.0.1");
	cfig.port = PORT;
	cfig.sock = socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP);
	if (cfig.sock == -1)
    {
	    printf("ERR socket()\n");
        return 0;
    }
    #ifdef DEBUG
    printf("complete create socket()\n");
    #endif

    cfig.addr.sin_family = AF_INET;//ipv4
    cfig.addr.sin_addr.s_addr = cfig.server;
    cfig.addr.sin_port = htons(cfig.port);

    nRet = bind(cfig.sock, (struct sockaddr *)&cfig.addr, sizeof(struct sockaddr_in));
    if (nRet == -1)
	{
	    close(cfig.sock);
	    printf("ERR Failed to bind()\n");
		return 0;
	}
    #ifdef DEBUG
    printf("complete bind()\n");
    #endif

	return 1;
}

void *hand_req(struct request * req)
{
    #ifdef DEBUG
    printf("complete , beginning of process req().\n");
    #endif

	req->blksize = BL_SIZE;//max
	req->timeout = ++timeout / 2;
	US      comport;
	struct  sockaddr_in service;
	char   *temp;
	int     i;

    //socket(), bind(), connect() to cl.
	req->m_socket = socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP);
	if (req->m_socket == -1)
	{
		req->serverError.opcode = htons(5);
		req->serverError.errorcode = htons(0);
		strcpy(req->serverError.errormessage, "ERR thread socket()");
		req->bytesSent = sendto(req->knock, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0, (struct sockaddr*) & (req->client), req->clientsize);
		errmsg(req, 1);
		ck_e2c(req->bytesSent);
		free(req);
		req=NULL;
		pthread_exit(NULL);
	}
    #ifdef DEBUG
    else
        printf("complete thread socket().\n");
    #endif

	for (comport = cfig.minport;1;comport++)
	{
		service.sin_family = AF_INET;
		service.sin_addr.s_addr = cfig.server;
		service.sin_port = htons(comport);

		if (comport > cfig.maxport)
		{
			req->serverError.opcode = htons(5);
			req->serverError.errorcode = htons(0);
			strcpy(req->serverError.errormessage, "ERR No port is free");
			req->bytesSent = sendto(req->knock, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0, (struct sockaddr*) & (req->client), req->clientsize);
			errmsg(req, 1);
            ck_e2c(req->bytesSent);
			cle_fre_req(req);
			pthread_exit(NULL);
		}
		else if (bind(req->m_socket, (struct sockaddr *) &service, sizeof(service)) == -1)
		{
            #ifdef DEBUG
            printf("ERR thread bind(), this port is not free, try next.\n");
            #endif
			continue;
		}
		else
		{
            #ifdef DEBUG
            printf("complete thread bind(), port is free.\n");
            #endif
			break;
		}
	}

	if (connect(req->m_socket, (struct sockaddr*)&(req->client), req->clientsize) == -1)
	{
		req->serverError.opcode = htons(5);
		req->serverError.errorcode = htons(0);
		strcpy(req->serverError.errormessage, "ERR thread connect()");
		req->bytesSent = sendto(req->knock, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0, (struct sockaddr*) & (req->client), req->clientsize);
		errmsg(req, 1);
		ck_e2c(req->bytesSent);
        cle_fre_req(req);
		pthread_exit(NULL);
	}
    #ifdef DEBUG
    else
        printf("complete thread connect().\n");
    #endif

    //hint: opcode, name_str, '0', mode_str, '0'.
	temp = (char*) & (req->datain);
	temp += 2;
	req->filename = temp;
	temp += strlen(temp) + 1;//skip file name
	req->mode = temp;
	temp += strlen(temp) + 1;//skip mode name
	req->alias = req->filename;

    //deal with filename, mode.
	for (i = 0; i < strlen(req->alias); i++)
	{
		if (req->alias[i] == '\\')
			req->alias[i] = '/';
	}
	if (strstr(req->alias, "../"))//upper is violated
	{
		req->serverError.opcode = htons(5);
		req->serverError.errorcode = htons(2);
		strcpy(req->serverError.errormessage, "ERR access \"../\"");
		req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
		errmsg(req, 1);
        ck_e2c(req->bytesSent);
		cle_fre_req(req);
		pthread_exit(NULL);
	}
	#ifdef DEBUG
	else
        printf("complete access\n");
    #endif

	if (req->alias[0] == '/')
		req->alias++;
	strcpy(req->path, cfig.homedir);
	strcat(req->path, req->alias);

	req->fblock = 0;
	if (ntohs(req->datain.opcode) == 1)//RRQ
	{
		errno = 0;//no err, so far
		if (!strcasecmp(req->mode, "netascii") || !strcasecmp(req->mode, "mail"))//mode is netasc or mail
			req->file = fopen(req->path, "r");//text, read only
		else
			req->file = fopen(req->path, "rb");//binary, rd only
		if (errno || !req->file)
		{
			req->serverError.opcode = htons(5);
			req->serverError.errorcode = htons(1);
			strcpy(req->serverError.errormessage, "ERR fopen()");
			req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
			errmsg(req, 1);
            ck_e2c(req->bytesSent);
			cle_fre_req(req);
			pthread_exit(NULL);
		}
        #ifdef DEBUG
        else
            printf("complete open file, read only.\n");
        #endif
	}
	else//WRQ
	{
		if (!cfig.overwrite)//if overwrite == 0
		{
			req->file = fopen(req->path, "rb");
			if (req->file)//if could open the file, it exists
			{
				req->serverError.opcode = htons(5);
				req->serverError.errorcode = htons(6);
				strcpy(req->serverError.errormessage, "ERR file exists");
				errmsg(req, 1);
				req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
                ck_e2c(req->bytesSent);
				cle_fre_req(req);
				pthread_exit(NULL);
			}
		}
        #ifdef DEBUG
        else
            printf("complete : file is not exist.\n");
        #endif

        // to open the to be written file
		errno = 0;
		if (!strcasecmp(req->mode, "netascii") || !strcasecmp(req->mode, "mail"))
			req->file = fopen(req->path, "w");
		else
			req->file = fopen(req->path, "wb");
		if (errno || !req->file)
		{
			req->serverError.opcode = htons(5);
			req->serverError.errorcode = htons(2);
			strcpy(req->serverError.errormessage, "ERR invalid path or access");
			errmsg(req, 1);
			req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
            ck_e2c(req->bytesSent);
			cle_fre_req(req);
			pthread_exit(NULL);
		}
        #ifdef DEBUG
        else
            printf("complete open file, write.\n");
        #endif
	}

	if (ntohs(req->datain.opcode) == 1)  //process opcode==RRQ
	{
		errno = 0;
		req->pkt = (struct packet *)malloc(req->blksize + 4);
		if (errno || !req->pkt)
		{
			printf("ERR thread process RRQ malloc()");
			cle_fre_req(req);
			pthread_exit(NULL);
		}
        #ifdef DEBUG
        else
            printf("complete thread process RRQ malloc().\n");
        #endif

		if (ftell(req->file))
			fseek(req->file, 0, SEEK_SET);//for txt file, SEEK_SET: Beginning of file

        // send 1st data
		errno = 0;
		req->block = 1;
		req->pkt->opcode = htons(3);
		req->pkt->block = htons(req->block);
		req->bytesRead = fread(&req->pkt->buffer, 1, req->blksize, req->file);//req->blksize = 512;//max
		if (errno)
		{
			req->serverError.opcode = htons(5);
			req->serverError.errorcode = htons(0);
			sprintf(req->serverError.errormessage, "%s", strerror(errno));
			errmsg(req, 1);
			req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
            ck_e2c(req->bytesSent);
			cle_fre_req(req);
			pthread_exit(NULL);
		}
        #ifdef DEBUG
        else
            printf("complete thread process RRQ fread().\n");
        #endif

		req->bytesSent = send(req->m_socket, (const char*)req->pkt, req->bytesRead + 4, 0);
		if (errno)
		{
			sprintf(req->serverError.errormessage, "ERR thread pro rrq send()");
			errmsg(req, 1);
			cle_fre_req(req);
			pthread_exit(NULL);
	    }
        #ifdef DEBUG
        else
            printf("complete thread process RRQ send(), block==%hd.\n",req->fblock);
        #endif
        req->fblock++;
		if (req->bytesRead != req->blksize)//EOF
		{
			fclose(req->file);
			req->file = 0;
        #ifdef DEBUG
        printf("complete thread process RRQ send(), EOF.\n");
        #endif
		}

		while (1)
		{
			FD_ZERO(&req->readfds);
			req->tv.tv_sec = req->timeout;
			req->tv.tv_usec = 0;
			FD_SET(req->m_socket, &req->readfds);
			select(req->m_socket + 1, &req->readfds, NULL, NULL, &req->tv);
			if (FD_ISSET(req->m_socket, &req->readfds))
			{
			    #ifdef DEBUG
			    printf("complete : there is a response\n");
			    #endif
				errno = 0;
				req->bytesReceived = recv(req->m_socket, (char*) & (req->datain), sizeof(struct ack), 0);
				if (errno)
				{
					sprintf(req->serverError.errormessage, "ERR thread process RRQ send=>recv().");
					errmsg(req, 1);
					cle_fre_req(req);
					pthread_exit(NULL);
				}
                #ifdef DEBUG
                else
                    printf("complete thread process RRQ send=>recv().\n");
                #endif
			}
			else
				req->bytesReceived = 0;  //no client response available.

			if (req->bytesReceived >= 4)
			{
				if (ntohs(req->datain.opcode) == 4)
				{
					if (ntohs(req->datain.block) == req->block)
					{
                        req->block++;
						req->attempt = 0;
		                if (req->file)//if the file is not closed, continue send data
		                {
		                	errno = 0;
		                    req->pkt->opcode = htons(3);
		                    req->pkt->block = htons(req->block);
		                    req->bytesRead = fread(&req->pkt->buffer, 1, req->blksize, req->file);
		                    if (errno)
		                    {
			                    req->serverError.opcode = htons(5);
			                    req->serverError.errorcode = htons(0);
			                    sprintf(req->serverError.errormessage, "%s", strerror(errno));
			                    errmsg(req, 1);
			                    req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
                                ck_e2c(req->bytesSent);
			                    cle_fre_req(req);
			                    pthread_exit(NULL);
		                    }
                            #ifdef DEBUG
                            else
                                printf("complete thread process RRQ, not eof, fread()\n");
                            #endif
		                    req->bytesSent = send(req->m_socket, (const char*)req->pkt, req->bytesRead + 4, 0);
                            req->fblock++;
		                    if (errno)
		                    {
			                    sprintf(req->serverError.errormessage, "ERR thread process RRQ, not eof, send()");
			                    errmsg(req, 1);
			                    cle_fre_req(req);
			                    pthread_exit(NULL);
	                        }
                            #ifdef DEBUG
                            else
                                printf("complete thread process RRQ, not eof, send()\n");
                                //then,if not eof it will go to while(1) ,that is "continue"
                            #endif
		                    if (req->bytesRead != req->blksize)//EOF
		                    {
			                    fclose(req->file);
			                    req->file = 0;
		                    }
		                }//if (req->file)//if the file is not closed, continue send data
		                else// EOF
		                {
                            #ifdef DEBUG
                            printf("complete thread process RRQ send(), EOF.\n");
                            #else
		                    printf("Client is %s:%u %s, %u blocks\n", inet_ntoa(req->client.sin_addr), ntohs(req->client.sin_port), req->path, req->fblock);
				            #endif
				            req->attempt = 255;
				            break;//while (1)
		                }//if (req->file)//if the file is not closed, continue send data
					}//if (ntohs(req->datain.block) == req->block)
					else
						continue;//while (1)
				}//if (ntohs(req->datain.opcode) == 4)
				else if (ntohs(req->datain.opcode) == 5)
				{
					sprintf(req->serverError.errormessage, "cl is %s:%u, ERR_code %i of cl %s", inet_ntoa(req->client.sin_addr), ntohs(req->client.sin_port), ntohs(req->clientError.errorcode), req->clientError.errormessage);
					errmsg(req, 1);
					cle_fre_req(req);
					pthread_exit(NULL);
				}//if (ntohs(req->datain.opcode) == 4),else if (ntohs(req->datain.opcode) == 5)
				else
				{
					req->serverError.opcode = htons(5);
					req->serverError.errorcode = htons(0);
					strcpy(req->serverError.errormessage, "ERR opcode");
					errmsg(req, 1);
					req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
                    ck_e2c(req->bytesSent);
					cle_fre_req(req);
					pthread_exit(NULL);
				}//if (ntohs(req->datain.opcode) == 4),else if (ntohs(req->datain.opcode) == 5),else
			}//if (req->bytesReceived >= 4)
			else if (req->attempt >= 3)
			{
				req->serverError.opcode = htons(5);
				req->serverError.errorcode = htons(0);
                strcpy(req->serverError.errormessage, "ERR Timeout");
                errmsg(req, 1);
				req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
                ck_e2c(req->bytesSent);
				cle_fre_req(req);
				pthread_exit(NULL);
			}//if (req->bytesReceived >= 4)
			else
			{
				//resend previous frame.
				errno = 0;
				req->bytesSent = send(req->m_socket, (const char*)req->pkt, req->bytesSent, 0);
				req->attempt++;

				if (errno)
				{
					sprintf(req->serverError.errormessage, "ERR resend send()");
					errmsg(req, 1);
					cle_fre_req(req);
					pthread_exit(NULL);
				}
            }//if (req->bytesReceived >= 4)
		}//while (1)
	}//if (ntohs(req->datain.opcode) == 1)  //process opcode==RRQ

	else if (ntohs(req->datain.opcode) == 2)  //process opcode==WRQ
	{
		//return ACK of wrq
		req->block = 0;
		req->acout.opcode = htons(4);
		req->acout.block = htons(req->block);
		errno = 0;
		req->bytesSent = send(req->m_socket, (const char*) & (req->acout), 4, 0);
		if (errno)
	    {
		    sprintf(req->serverError.errormessage, "ERR ack of wrq");
		    errmsg(req, 1);
		    cle_fre_req(req);
		    pthread_exit(NULL);
	    }
		errno = 0;
		req->pkt = (struct packet *)malloc(req->blksize + 4);
		if (errno || !req->pkt)
		{
			printf("ERR malloc()\n");
            req->attempt = 255;
			cle_fre_req(req);
			pthread_exit(NULL);
		}

		while (1)
		{
			FD_ZERO(&req->readfds);
			req->tv.tv_sec = req->timeout;
			req->tv.tv_usec = 0;
			FD_SET(req->m_socket, &req->readfds);
			select(req->m_socket + 1, &req->readfds, NULL, NULL, &req->tv);

			if (FD_ISSET(req->m_socket, &req->readfds))
			{
				errno = 0;
				req->bytesReceived = recv(req->m_socket, (char*)req->pkt, req->blksize + 4, 0);
				if (errno)
				{
					sprintf(req->serverError.errormessage, "ERR wrq, isset, recv()");
					errmsg(req, 1);
					cle_fre_req(req);
					pthread_exit(NULL);
				}
			}
			else
				req->bytesReceived = 0;  //no client response available.

			if (req->bytesReceived >= 4)
			{
				if (ntohs(req->pkt->opcode) == 3)//opcode == data
				{
					if (ntohs(req->pkt->block) == req->block + 1)//block is right
					{
						req->acout.opcode = htons(4);
						req->acout.block = htons(req->block + 1);
                        req->block++;
						errno = 0;
						req->bytesSent = send(req->m_socket, (const char*)&req->acout, 4, 0);

						if (errno )
						{
							sprintf(req->serverError.errormessage, "ERR wrq, while, send()");
							errmsg(req, 1);
							cle_fre_req(req);
							pthread_exit(NULL);
						}

						if (req->file && req->bytesReceived > 4)
						{
							errno = 0;
							fwrite(&req->pkt->buffer, req->bytesReceived - 4, 1, req->file);
							if (errno)
							{
								req->serverError.opcode = htons(5);
								req->serverError.errorcode = htons(3);
								strcpy(req->serverError.errormessage, "ERR fwrite()");
								errmsg(req, 1);
								req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
                                ck_e2c(req->bytesSent);
								cle_fre_req(req);
								pthread_exit(NULL);
							}
							else
							{
                                req->fblock++;
								req->attempt = 0;
							}
						}

						if (req->bytesReceived - 4 < req->blksize)
						{
							fclose(req->file);
							req->file = 0;
							printf("cl %s:%u %s, %u blocks recv()ed\n", inet_ntoa(req->client.sin_addr), ntohs(req->client.sin_port), req->path, req->fblock);
							break;
						}
					}
					else
						continue;
				}
				else if (ntohs(req->pkt->opcode) == 5)
				{
					printf("ERR_code %i at cl, %s\n", ntohs(req->clientError.errorcode), req->clientError.errormessage);
					cle_fre_req(req);
					pthread_exit(NULL);
				}
				else
				{
					req->serverError.opcode = htons(5);
					req->serverError.errorcode = htons(4);
					strcpy(req->serverError.errormessage, "ERR opcode");
					errmsg(req, 1);
					req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
                    ck_e2c(req->bytesSent);
					cle_fre_req(req);
					pthread_exit(NULL);
				}
			}
			else if (req->attempt >= 3)
			{
				req->serverError.opcode = htons(5);
				req->serverError.errorcode = htons(0);
				strcpy(req->serverError.errormessage, "ERR time out");
				errmsg(req, 1);
				req->bytesSent = send(req->m_socket, (const char*) & (req->serverError), strlen(req->serverError.errormessage) + 5, 0);
                ck_e2c(req->bytesSent);
				cle_fre_req(req);
				pthread_exit(NULL);
			}
			else
			{
				errno = 0;
				send(req->m_socket, (const char*)&req->acout, req->bytesSent, 0);
				req->attempt++;

				if (errno )
				{
					sprintf(req->serverError.errormessage, "ERR  send()");
					errmsg(req, 1);
					cle_fre_req(req);
					pthread_exit(NULL);
				}
			}
		}
	}
	cle_fre_req(req);
	pthread_exit(NULL);

}

void cle_fre_req(struct request* req)
{
    printf("tid[%d] cle_fre_req executed.\n", (int)req->threadId);
/*	if (req->file)
	{
		fclose(req->file);
		req->file = 0;
    }
	if (req->m_socket >= 0)
	{
		close(req->m_socket);
		req->m_socket = 0;
    }
	if (req->pkt)
	{
		free(req->pkt);
		req->pkt = 0;
    }
*/
    memset(req,0,sizeof req);
	free(req);
	req = NULL;
}

void errmsg(struct request *req, UCH messLevel)
{
    if (!req->serverError.errormessage[0])
        printf(req->serverError.errormessage, strerror(errno));
    if (req->path[0])
        printf("cl is %s:%u %s, %s\n", inet_ntoa(req->client.sin_addr), ntohs(req->client.sin_port), req->path, req->serverError.errormessage);
    else
        printf("cl is %s:%u, %s\n", inet_ntoa(req->client.sin_addr), ntohs(req->client.sin_port), req->serverError.errormessage);
}

void ck_e2c(int i)//check err to client
{
    if(i>0)
    {
        printf("sent err msg to client.\n");
    }
    else
    {
        printf("ERR fail to send err msg to client.\n");
    }
}


int main(int argc, char *argv[])
{
    if( argc != 2)
    {
        printf("complete no arguement, will use default(/home/). please use a dir(homedir) as arguement.\n");
        argv[1]="/home/";
    }
    #ifdef DEBUG
    printf("complete in main(),\n");
    #endif

    if(argv[1][strlen(argv[1]) - 1] != '/')
    strcat(argv[1],"/");

	struct timeval tv;
	fd_set readfds;
	int fdsReady;
	struct request * req;

    //ini
	if (ini(argv[1]) == 0)
	{
        #ifdef DEBUG
        printf("ERR ini() == 0\n");
        #endif
        exit(1);
    }
    #ifdef DEBUG
    printf("complete ini() != 0\n");
    printf("complete server: waiting requests...\n");
    #else
    printf("server: waiting requests...\n");
    #endif

    while (1)
    {
        #ifdef DEBUG
        printf("complete in while(1)\n");
        #endif
        FD_ZERO(&readfds);
        FD_SET(cfig.sock, &readfds);
        tv.tv_sec  = 2;
        tv.tv_usec = 0;

        errno = 0;
        fdsReady = select(cfig.sock + 1, &readfds, NULL, NULL, &tv);
        if(fdsReady<=0)
        {
            printf("ERR select() times out\n");
            continue;
        }
        #ifdef DEBUG
        else
        {
            printf("complete select()\n");
        }
        #endif

        if (FD_ISSET(cfig.sock, &readfds))//confirm fd is really ready
        {
            req = (struct request *)malloc(sizeof(struct request));
            if (!req)
            {
                printf("ERR malloc()\n");
                continue;//try to malloc() again
            }
            #ifdef DEBUG
            else
                printf("complete malloc()\n");
            #endif

            req->clientsize = sizeof(req->client);
            errno = 0;
            req->bytesReceived = recvfrom(cfig.sock, (char*)&(req->datain), sizeof(req->datain), 0, (struct sockaddr *)&(req->client), &req->clientsize);
            req->knock = cfig.sock;

            if (!errno)//if recvfrom ok, errno will still ==0, and !error==true
            {
                #ifdef DEBUG
                printf("complete recvfrom(), %dbytes.\n",req->bytesReceived);
                #endif
                if (req->bytesReceived > 0 && (ntohs(req->datain.opcode) == 1 || ntohs(req->datain.opcode) == 2))//RRQ or WRQ
                {
                    #ifdef DEBUG
                    printf("complete , this is RRQ or WRQ.\n");
                    #endif
                    pthread_create(&req->threadId, NULL, hand_req, req);
                    if (!req->threadId)//if req->threadId==0
                    {
                        req->serverError.opcode = htons(5);
                        req->serverError.errorcode = htons(0);
                        strcpy(req->serverError.errormessage, "ERR pthread_create");
                        errmsg(req, 2);
                        req->bytesSent = sendto(req->knock, (const char*)&(req->serverError), strlen(req->serverError.errormessage) + 5, 0, (struct sockaddr*)&(req->client), req->clientsize);
                        ck_e2c(req->bytesSent);
                        free(req);
                        req=NULL;
                    }
                    #ifdef DEBUG
                    else
                    {
                        printf("complete pthread_create()");
                    }
                    #endif
                }
                else if (req->bytesReceived > 0 && (htons(req->datain.opcode) == 5))//opcode==err
                {
                    sprintf(req->serverError.errormessage, "ERR_code %i, cl, %s", ntohs(req->clientError.errorcode), req->clientError.errormessage);
                    errmsg(req, 2);
                    free(req);
                    req=NULL;
                }
                else
                {
                    req->serverError.opcode = htons(5);
                    req->serverError.errorcode = htons(4);
                    sprintf(req->serverError.errormessage, "ERR opcode %i", ntohs(req->datain.opcode));
                    errmsg(req, 2);
                    req->bytesSent = sendto(req->knock, (const char*)&(req->serverError), strlen(req->serverError.errormessage) + 5, 0, (struct sockaddr*)&(req->client), req->clientsize);
                    if(req->bytesSent>0)
                    {
                        printf("succeed to send err msg to client\n");
                    }
                    else
                    {
                        printf("failed to send err msg to client\n");
                    }
                    free(req);
                    req=NULL;
                }
            }
            else
            {
                sprintf(req->serverError.errormessage, "Err recvfrom()");
                errmsg(req, 1);
                free(req);
                req=NULL;
            }
        }
        else
        {
            printf("ERR fd is not really ready\n");
        }
    }//while(1)
    return(0);
}

