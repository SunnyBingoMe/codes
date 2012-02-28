#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <netdb.h>

#define SERVERPORT "13"
#define MYPORT "59590"
#define MAXBUFLEN 100

//#define DEBUG

void *get_in_addr(struct sockaddr *sa)
{
    if (sa->sa_family == AF_INET)
    {
        return &(((struct sockaddr_in*)sa)->sin_addr);
    }

    return &(((struct sockaddr_in6*)sa)->sin6_addr);
}

int main(int argc, char *argv[])
{
    int sockfd;
    struct addrinfo hints, *servinfo, *p, *pp;
    int rv;
    int numbytes;

    socklen_t addr_len;
    struct sockaddr_storage their_addr;
    char buf[MAXBUFLEN];
    char s[INET6_ADDRSTRLEN];

	char date_cl[]="00 aaa",year_cl[]="2010",time_cl[]="00,00,00 (24H)",zone[]="1234567890";
	int i;

	#ifdef DEBUG
    printf("complete ini\n");
    #endif

    if (argc != 2)
    {
	    printf("OBS! no server addr, use default.\n");
        argv[1]="127.0.0.1";
    }
    argv[2]="this is the default ANY massage.";

    memset(&hints, 0, sizeof hints);
    hints.ai_family = AF_UNSPEC;
    hints.ai_socktype = SOCK_DGRAM;

    if ((rv = getaddrinfo(argv[1],
                          SERVERPORT,
                          &hints,
                          &servinfo
                          )
        ) != 0
       )
    {
        fprintf(stderr, "getaddrinfo: %s\n", gai_strerror(rv));
        return 1;
    }
	#ifdef DEBUG
	else
        printf("complete getaddrinfo()_SERVERPORT\n");
    #endif

    for(p = servinfo; p != NULL; p = p->ai_next)
    {
        if ((sockfd = socket(p->ai_family,
                            p->ai_socktype,
                            p->ai_protocol
                            )
            ) == -1
           )
        {
            perror("talker: socket");
            continue;
        }
        break;
    }
    if (p == NULL)
    {
        fprintf(stderr, "talker: failed to socket()\n");
        return 2;
    }
	#ifdef DEBUG
	else
        printf("complete socket()\n");
    #endif


    if ((rv = getaddrinfo(argv[1],
                          MYPORT,
                          &hints,
                          &servinfo
                          )
        ) != 0
       )
    {
        fprintf(stderr, "getaddrinfo: %s\n", gai_strerror(rv));
        return 1;
    }
	#ifdef DEBUG
	else
        printf("complete getaddrinfo()_MYPORT\n");
    #endif

    for(pp = servinfo; pp != NULL; pp = pp->ai_next)
    {
        if (bind(sockfd,
                pp->ai_addr,
                pp->ai_addrlen
                ) == -1
            )
        {
            perror("server: bind");
            continue;
        }
        break;
    }
    if (pp == NULL)
    {
        fprintf(stderr, "talker: failed to bind socket\n");
        return 2;
    }
    #ifdef DEBUG
    else
        printf("complete bind()\n");
    #endif

    if ((numbytes = sendto(sockfd,
                           argv[2],
                           strlen(argv[2]),
                           0,
                           p->ai_addr,
                           p->ai_addrlen
                          )
        ) == -1
       )
    {
        perror("talker: sendto");
        exit(1);
    }
	#ifdef DEBUG
    printf("talker: sent %d bytes to %s\n\n", numbytes, argv[1]);
    #endif

    #ifdef DEBUG
    printf("server: waiting packet ...\n\n");
    #endif

    addr_len = sizeof their_addr;
    if ((numbytes = recvfrom(sockfd,
                            buf,
                            MAXBUFLEN-1,
                            0,
                            (struct sockaddr *)&their_addr,
                            &addr_len
                            )
         ) == -1
        )
    {
        perror("recvfrom");
        exit(1);
    }
    buf[numbytes] = '\0';

    #ifdef DEBUG
    printf("server: got packet from %s\n",
            inet_ntop(their_addr.ss_family,
                    get_in_addr((struct sockaddr *)&their_addr),
                    s,
                    sizeof s
                    )
          );
    printf("server: packet is %d bytes long\n", numbytes);
    printf("server: packet contains \"%s\"\n", buf);
    #endif


    //date_cl
    for (i=0;i<6;i++)
        date_cl[i]=buf[i];

    //year_cl
    year_cl[2]=buf[7];
    year_cl[3]=buf[8];

    //time_cl
    for (i=0;i<8;i++)
        time_cl[i]=buf[i+10];

    //zone
    for (i=0;i<(numbytes-19);i++)
    {
        zone[i]=buf[i+19];
    }
    zone[i]='\0';

    printf("Today is %s, %s.\nNow is %s.\nTime zone is %s.\n",date_cl,year_cl,time_cl,zone);


    freeaddrinfo(servinfo);
    close(sockfd);
	#ifdef DEBUG
        printf("complete close()\n");
    #endif

    return 0;
}
