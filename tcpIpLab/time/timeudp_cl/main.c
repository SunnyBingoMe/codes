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

#define SERVERPORT "37"
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
    unsigned int * p_intbuf;
    char s[INET6_ADDRSTRLEN];

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
    printf("complete : sent %d bytes to %s\n\n", numbytes, argv[1]);
    #endif

    #ifdef DEBUG
    printf("complete client: waiting packet ...\n\n");
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

    p_intbuf=(unsigned int *)buf;


    #ifdef DEBUG
    printf("complete client: got packet from %s\n",
            inet_ntop(their_addr.ss_family,
                    get_in_addr((struct sockaddr *)&their_addr),
                    s,
                    sizeof s
                    )
          );
    printf("complete client: packet is %d bytes long\n", numbytes);
    printf("complete client: packet contains \"%u\"\n", *p_intbuf);
    printf("complete client: packet contains \"%u\"\n", *((unsigned int *)buf));
    #endif

    printf("Now:%s\n",ctime (p_intbuf));

    freeaddrinfo(servinfo);
    close(sockfd);
	#ifdef DEBUG
        printf("complete close()\n");
    #endif

    return 0;
}
