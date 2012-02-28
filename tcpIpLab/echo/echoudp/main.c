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

#define MYPORT "7"    // the port users will be connecting to
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

int main(void)
{
    int sockfd;
    struct addrinfo hints, *servinfo, *p;
    int rv;
    int numbytes;
    struct sockaddr_storage their_addr;
    char buf[MAXBUFLEN];
    socklen_t addr_len;
    char s[INET6_ADDRSTRLEN];
   	char * p_msg;
    int strl;


    memset(&hints, 0, sizeof hints);
    hints.ai_family = AF_UNSPEC; // set to AF_INET to force IPv4
    hints.ai_socktype = SOCK_DGRAM;
    hints.ai_flags = AI_PASSIVE; // use my IP

    if ((rv = getaddrinfo(NULL, MYPORT, &hints, &servinfo)) != 0)
    {
        fprintf(stderr, "getaddrinfo: %s\n", gai_strerror(rv));
        return 1;
    }

    for(p = servinfo; p != NULL; p = p->ai_next)
    {
        if ((sockfd = socket(p->ai_family,
                            p->ai_socktype,
                            p->ai_protocol
                            )
            ) == -1
           )
        {
            perror("server: socket");
            continue;
        }
        if (bind(sockfd,
                p->ai_addr,
                p->ai_addrlen
                ) == -1
            )
        {
            close(sockfd);
            perror("server: bind");
            continue;
        }
        break;
    }

    if (p == NULL)
    {
        fprintf(stderr, "server: failed to bind socket, p == NULL\n");
        return 2;
    }

    freeaddrinfo(servinfo);


    while(1)
    {
        printf("server: waiting request ...\n\n");

        addr_len = sizeof their_addr;

        //recv
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
        printf("SERVER: got request from %s\n",
                inet_ntop(their_addr.ss_family,
                          get_in_addr((struct sockaddr *)&their_addr),
                          s,
                          sizeof s
                          )
              );
        buf[numbytes] = '\0';
        printf("RECV: %dbytes, %s.\n", numbytes,buf);

        //send
        p_msg = buf;
        if ((numbytes = sendto(sockfd,
                               p_msg,
                               strl=strlen(p_msg),
                               0,
                               (struct sockaddr *)&their_addr,
                               sizeof (struct sockaddr)
                              )
             ) == -1
            )
        {
            perror("talker: sendto");
            exit(1);
        }

        #ifdef DEBUG
        printf("complete strlen is %dbytes.\n",strl);
        printf("complete sent to %s.\n",
               inet_ntop(their_addr.ss_family,
                         get_in_addr((struct sockaddr *)&their_addr),
                         s,
                         sizeof s
                        )
              );
        #endif
        printf("SEND: %dbytes, %s.\n",numbytes,p_msg);

    }//while()

    return 0;
}
