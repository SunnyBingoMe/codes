#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <netdb.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <time.h>

#define PORT "7"

#define MAXDATASIZE 100

//#define DEBUG

void *get_in_addr(struct sockaddr *sa)
{
	#ifdef DEBUG
        printf("in get_in_addr\n");
    #endif

	if (sa->sa_family == AF_INET)
	{
        #ifdef DEBUG
        printf("    sa->sa_family == AF_INET{it's ipv4}\n");
        #endif
		return &(((struct sockaddr_in*)sa)->sin_addr);
	}

	#ifdef DEBUG
    printf("    sa->sa_family != AF_INET{it's ipv6}\n");
    #endif
	return &(((struct sockaddr_in6*)sa)->sin6_addr);
}

int main(int argc, char *argv[])
{
	int sockfd, numbytes;
	char buf[MAXDATASIZE];
	struct addrinfo hints, *servinfo, *p;
	int rv;
	char s[INET6_ADDRSTRLEN];
	unsigned int * p_intbuf;

	//send
    time_t rawtime;
	char * p_msg;
	unsigned int * p_intmsg;
    int counter;



	#ifdef DEBUG
    printf("complete ini\n");
    #endif

	if (argc != 2)
	{
	    printf("OBS! no server addr, use local as default.\n\n");
        argv[1]="127.0.0.1";

	}
	#ifdef DEBUG
	printf("argv[1] is %s\n",argv[1]);
    #endif

	memset(&hints, 0, sizeof hints);
	hints.ai_family = AF_UNSPEC;
	hints.ai_socktype = SOCK_STREAM;

	if ((rv = getaddrinfo(argv[1], PORT, &hints, &servinfo)) != 0)
	{
		fprintf(stderr, "getaddrinfo: %s\n", gai_strerror(rv));
		return 1;
	}
	#ifdef DEBUG
	else
        printf("complete getaddrinfo()\n");
    #endif

	for(p = servinfo; p != NULL; p = p->ai_next)
	{
		if ((sockfd = socket(p->ai_family, p->ai_socktype,
				p->ai_protocol)) == -1)
        {
			perror("client: socket");
			continue;
		}
		#ifdef DEBUG
		else
            printf("complete socket() \n");
        #endif

		if (connect(sockfd, p->ai_addr, p->ai_addrlen) == -1)
		{
			close(sockfd);
			perror("client: connect");
			continue;
		}
        #ifdef DEBUG
        else
            printf("complete connect()\n");
        #endif

		break;
	}

	if (p == NULL)
	{
		fprintf(stderr, "client: failed to connect, p == NULL\n");
		return 2;
	}
    #ifdef DEBUG
    else
        printf("complete p != NULL\n");
    #endif

	inet_ntop(p->ai_family,
              get_in_addr((struct sockaddr *)p->ai_addr),
			  s,
			  sizeof s);
	#ifdef DEBUG
    printf("complete inet_ntop()\n");
	printf("client: connecting to %s\n", s);
    #endif

	freeaddrinfo(servinfo);
	#ifdef DEBUG
    printf("complete freeaddrinfo()\n");
    #endif


    srand (time(NULL));
    counter = (rand() % 10)/2 + 1;
    for (;counter>0;counter--)
    {    //send & recv
        sleep(1);
        time ( &rawtime );
        p_msg = (char *) &rawtime;
        p_intmsg=(unsigned int *)p_msg;

        //send start,
        if ((send(sockfd,
                  p_msg,
                  sizeof(p_msg),
                  0
                 )
            )
            == -1
           )
            perror("send");
        #ifdef DEBUG
        printf("complete client send()\n");
        printf("complete client send(): %u\n",*((unsigned int *)p_msg));
        printf("complete client send(): %u\n",rawtime);
        printf("complete client send(): %u\n",*p_intmsg);
        #endif
        printf("SEND: %s\n",ctime(p_msg));
        //send end.

        //recv start,
        if ((numbytes = recv(sockfd,
                            buf,
                            MAXDATASIZE-1,
                            0
                            )
            )
            == -1
        )
        {
            perror("recv");
            exit(1);
        }
        #ifdef DEBUG
        else
            printf("complete recv()\n");
        #endif

        buf[numbytes] = '\0';
        p_intbuf=(unsigned int *)buf;
        #ifdef DEBUG
        printf("complete client received %dbytes: %u\n",numbytes,*((unsigned int *)buf));
        printf("complete client received %dbytes: %u\n",numbytes,*p_intbuf);
        #endif

        printf("RECV: %s\n\n",ctime (p_intbuf));
        #ifdef DEBUG
        printf("complete ctime()\n");
        #endif
        //recv end.
    }//for (;counter>0;counter--){    //send & recv

	close(sockfd);
	#ifdef DEBUG
    printf("complete client close()\n");
    #endif

    return 0;
}

