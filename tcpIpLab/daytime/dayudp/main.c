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
#include <time.h>

#define MYPORT "13"
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

char temp[5];//5 = num digits in 32676
char ans[5];
char * int2str(int num)
{
    int i=0,j=0,k=0;
    int ones=0;

    if(num==0)
    {
        ans[0]='0';
        return (char *)ans;
    };

    while (num!=0)
    {
        ones=num%10;//get current ones digit
        temp[i]=(char)(ones+48);//48=(int)'0';
        num=num/10;//remove current ones digit
        i++;//length of number
    }
    for(j=i-1;j>=0;j--)
    {
        ans[k]=temp[j]; //reorder string correctly
        k++;
    }
    ans[i]='\0'; //add null char for end of string
    return (char *)ans;
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

   	char * p_msg,p_ans;
    time_t rawtime;
    struct tm * p_timeinfo;
	char msg[23];
    char * mon[]={"   ",
                  "JAN",
                  "FEB",
                  "MAR",
                  "APR",
                  "MAY",
                  "JUN",
                  "JUL",
                  "OUG",
                  "SEP",
                  "OCT",
                  "NOV",
                  "DEC"
                  };


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
        fprintf(stderr, "server: failed to bind socket\n");
        return 2;
    }

    freeaddrinfo(servinfo);


    while(1)
    {
        printf("server: waiting request ...\n\n");

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

        printf("server: got request from %s\n",
                inet_ntop(their_addr.ss_family,
                          get_in_addr((struct sockaddr *)&their_addr),
                          s,
                          sizeof s
                          )
              );

        #ifdef DEBUG
        printf("server: packet is %d bytes long\n", numbytes);
        buf[numbytes] = '\0';
        printf("server: packet contains \"%s\"\n", buf);
        #endif



        time ( &rawtime );
        p_timeinfo = localtime ( &rawtime );
        #ifdef DEBUG
            printf("complete child p_timeinfo\n");
        #endif

        //day
        p_ans=int2str(p_timeinfo->tm_mday);
        if(p_timeinfo->tm_mday<10)
        {
            msg[0]='0';
            msg[1]=ans[0];
        }
        else
        {
            msg[0]=ans[0];
            msg[1]=ans[1];
        }

        //month
        msg[3]=mon[p_timeinfo->tm_mon][0];
        msg[4]=mon[p_timeinfo->tm_mon][1];
        msg[5]=mon[p_timeinfo->tm_mon][2];

        //year
        p_ans=int2str(p_timeinfo->tm_year);
        if(temp[1]<10)
        {
            msg[7]='0';
            msg[8]=temp[0];
        }
        else
        {
            msg[7]=temp[1];
            msg[8]=temp[0];
        }

        //hour
        p_ans=int2str(p_timeinfo->tm_hour);
        if(p_timeinfo->tm_hour<10)
        {
            msg[10]='0';
            msg[11]=ans[0];
        }
        else
        {
            msg[10]=ans[0];
            msg[11]=ans[1];
        }

        //minute
        p_ans=int2str(p_timeinfo->tm_min);
        if(p_timeinfo->tm_min<10)
        {
            msg[13]='0';
            msg[14]=ans[0];
        }
        else
        {
            msg[13]=ans[0];
            msg[14]=ans[1];
        }

        //second
        p_ans=int2str(p_timeinfo->tm_sec);
        if(p_timeinfo->tm_sec<10)
        {
            msg[16]='0';
            msg[17]=ans[0];
        }
        else
        {
            msg[16]=ans[0];
            msg[17]=ans[1];
        }

        //charactistics
        msg[2]=msg[6]=msg[9]=msg[18]=' ';//spaces
        msg[12]=msg[15]=':';//solons
        msg[19]='C';
        msg[20]='E';
        msg[21]='S';
        msg[22]='T';
        //Central European Summer Time

        p_msg = msg;
        if ((numbytes = sendto(sockfd,
                               p_msg,
                               strlen(p_msg),
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
        printf("talker: sent %d bytes to %s, '%s'\n",
               numbytes,
               inet_ntop(their_addr.ss_family,
                         get_in_addr((struct sockaddr *)&their_addr),
                         s,
                         sizeof s
                        ),
               p_msg
              );
        #endif
    }//while()
    return 0;
}
